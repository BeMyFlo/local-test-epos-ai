/**
 * Shared smooth marquee for all Achiever sliders.
 *
 * Behavior (identical site-wide):
 * - Clicking an arrow glides exactly one item (ease-out), clicks accumulate,
 *   infinite loop in both directions (seamless DOM wrap)
 * - With options.autoDrift: continuous smooth drift; hovering the track pauses,
 *   hovering the prev arrow reverses the drift, the next arrow drifts forward
 * - Skips animation entirely when items fit without overflow
 * - prefers-reduced-motion: falls back to instant one-item step navigation
 */
export default function initMarquee(slider, track, previous, next, options = {}) {
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const speed = options.speed || 1.6;
  const autoDrift = options.autoDrift === true;

  const itemStride = (item) => {
    const styles = window.getComputedStyle(track);
    const parsedGap = Number.parseFloat(styles.columnGap || styles.gap || '0');
    const gap = Number.isFinite(parsedGap) ? parsedGap : 0;
    return item.getBoundingClientRect().width + gap;
  };
  const hasOverflow = () => track.scrollWidth - track.clientWidth > 4;

  if (reducedMotion) {
    const step = () => (track.firstElementChild ? itemStride(track.firstElementChild) : track.clientWidth * 0.85);
    if (previous) previous.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: 'auto' }));
    if (next) next.addEventListener('click', () => track.scrollBy({ left: step(), behavior: 'auto' }));
    return {
      glideItems: (count, dir) => track.scrollBy({ left: dir * step() * count, behavior: 'auto' }),
    };
  }

  let direction = 1;
  let paused = false;
  let glideRemaining = 0;

  // Continuous scrollLeft updates need instant behavior. CSS scroll-snap
  // re-anchors after every frame and cancels programmatic motion, so it is
  // disabled permanently for drifting strips — click-only sliders keep native
  // snap (manual scrolls stay aligned) and only suspend it during a glide.
  track.style.scrollBehavior = 'auto';
  // Browser scroll anchoring re-adjusts scrollLeft whenever items are inserted
  // or removed while scrolled — it fights the wrap/rotate logic and causes
  // visible stutter, so it is disabled for every managed track.
  track.style.overflowAnchor = 'none';
  if (autoDrift) track.style.setProperty('scroll-snap-type', 'none', 'important');

  // Hot-path caches — measuring layout (getComputedStyle/getBoundingClientRect)
  // or reading scrollLeft every frame forces continuous reflows and causes
  // visible stutter. Stride is cached and the position is mirrored in a float,
  // so the animation loop only ever WRITES scrollLeft.
  let strideCache = 0;
  const refreshStride = () => {
    strideCache = track.firstElementChild ? itemStride(track.firstElementChild) : 0;
  };
  refreshStride();
  window.addEventListener('resize', refreshStride, { passive: true });

  let pos = null;
  let lastTime = null;
  const BASE_FRAME = 1000 / 60;

  const frame = (now) => {
    const active = glideRemaining > 0 || (autoDrift && !paused);
    if (active && !document.hidden && track.children.length > 1 && hasOverflow() && strideCache > 0) {
      // Time-based delta: identical real-world speed on 60/120/144Hz displays,
      // no lurch after dropped frames (dt is clamped).
      if (lastTime === null) lastTime = now;
      const frameScale = Math.min(64, now - lastTime) / BASE_FRAME;
      lastTime = now;

      // Arrow clicks queue a one-item glide that eases out into the drift speed.
      let delta;
      if (glideRemaining > 0) {
        delta = Math.min(glideRemaining, Math.max(10, Math.min(30, glideRemaining * 0.18)) * frameScale);
        glideRemaining -= delta;
      } else {
        delta = speed * frameScale;
      }

      if (pos === null) pos = track.scrollLeft;
      pos += delta * direction;

      if (direction > 0 && pos >= strideCache) {
        track.appendChild(track.firstElementChild);
        pos -= strideCache;
        refreshStride();
        if (options.onWrap) options.onWrap();
      } else if (direction < 0 && pos <= 0) {
        track.prepend(track.lastElementChild);
        refreshStride();
        pos += strideCache;
        if (options.onWrap) options.onWrap();
      }
      track.scrollLeft = pos;

      // Glide finished on a click-only slider: hand alignment back to CSS snap.
      if (!autoDrift && glideRemaining === 0) {
        track.style.removeProperty('scroll-snap-type');
        pos = null;
      }
    } else {
      // Idle (or user is scrolling manually) — resync position on next start.
      pos = null;
      lastTime = null;
    }
    window.requestAnimationFrame(frame);
  };

  const drift = (dir) => {
    direction = dir;
    paused = false;
  };

  // When there is no room to scroll a full item, rotate the row instead:
  // append/prepend a clone of the edge item (which creates one item of real
  // overflow), animate scrollLeft across it, then drop the original. Scroll-
  // based motion keeps clipping correct — transforming the scroll container
  // itself would drag the whole visible box along with its contents.
  let isRotating = false;
  const rotate = (dir) => {
    if (isRotating || track.children.length < 2) return;
    isRotating = true;

    const edge = dir > 0 ? track.firstElementChild : track.lastElementChild;
    const stride = itemStride(edge);
    const clone = edge.cloneNode(true);
    const duration = 450;
    const ease = (t) => (t < 0.5 ? 4 * t * t * t : 1 - ((-2 * t + 2) ** 3) / 2);

    // Decode the clone's images synchronously so the first animation frame
    // doesn't hitch while the browser rasterizes them.
    clone.querySelectorAll('img').forEach((img) => {
      img.loading = 'eager';
      img.decoding = 'sync';
    });

    // Mandatory snap would re-anchor mid-animation — suspend it while rotating.
    track.style.setProperty('scroll-snap-type', 'none', 'important');

    let from;
    let to;
    if (dir > 0) {
      track.appendChild(clone);
      from = 0;
      to = stride;
      track.scrollLeft = 0;
    } else {
      track.prepend(clone);
      // Prepending shifts content right; jump onto the clone instantly so the
      // row looks unchanged, then ease back to the start.
      from = stride;
      to = 0;
      track.scrollLeft = stride;
    }

    const start = performance.now();
    const stepFrame = (now) => {
      const t = Math.min(1, (now - start) / duration);
      track.scrollLeft = from + (to - from) * ease(t);
      if (t < 1) {
        window.requestAnimationFrame(stepFrame);
        return;
      }
      edge.remove();
      track.scrollLeft = 0;
      if (!autoDrift) track.style.removeProperty('scroll-snap-type');
      isRotating = false;
      if (options.onWrap) options.onWrap();
      if (pendingRotations > 0) {
        pendingRotations -= 1;
        rotate(dir);
      }
    };
    window.requestAnimationFrame(stepFrame);
  };

  // Advance `count` items in direction `dir` — the arrows use count 1; dots
  // use larger counts. Chooses glide-scroll or row rotation automatically.
  let pendingRotations = 0;
  const glideItems = (count, dir) => {
    if (count <= 0) return;
    const ref = dir > 0 ? track.firstElementChild : track.lastElementChild;
    if (!ref) return;
    const stride = itemStride(ref);
    const maxScroll = track.scrollWidth - track.clientWidth;

    // Not enough room to scroll a full item (items fit, or overflow is less
    // than one card) — rotate the row instead of scrolling, queued per item.
    if (maxScroll < stride - 1) {
      if (track.scrollLeft !== 0) track.scrollLeft = 0;
      pendingRotations = count - 1;
      rotate(dir);
      return;
    }

    track.style.setProperty('scroll-snap-type', 'none', 'important');
    if (direction !== dir) glideRemaining = 0;
    drift(dir);
    glideRemaining += stride * count;
  };
  const boost = (dir) => glideItems(1, dir);

  if (autoDrift) {
    track.addEventListener('pointerenter', () => { paused = true; });
    track.addEventListener('pointerleave', () => { paused = false; });
    track.addEventListener('touchstart', () => { paused = true; }, { passive: true });
    track.addEventListener('touchend', () => { paused = false; }, { passive: true });
    if (previous) previous.addEventListener('pointerenter', () => drift(-1));
    if (next) next.addEventListener('pointerenter', () => drift(1));
  }
  if (previous) previous.addEventListener('click', () => boost(-1));
  if (next) next.addEventListener('click', () => boost(1));

  window.requestAnimationFrame(frame);

  return { glideItems };
}
