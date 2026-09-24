# BUILD STATE — pricemrcopper

## Environment

Values below are the approved bootstrap plan, **pending member bootstrap** — S0 stays
NEEDS-LIVE-ENV until the member runs the S0 runbook locally and pastes back the four Gate S0
outputs. Verified free on this machine on 2026-09-24: port 18770, container name
`pricemrcopper_epos`, database name `pricemrcopper_epos`.

- PROJECT_ID / container: `pricemrcopper_epos`
- URL: http://localhost:18770
- DB: `pricemrcopper_epos` @ mysql (shared) — phpMyAdmin http://localhost:8080
- Table prefix: `fcs_data_`
- Admin user: admin (password stored in `.env` — gitignored, the ONLY place it may live — NEVER written here)
- Source of truth: `pricemrcopper_final_mockup.html` (repo root) — single-file HTML mockup → S-flow (S1 uses `mockup-to-wordpress-blocks`, literal 100% clone, no redesign)
- Compose: this repo's `docker-compose.yml` — single `wordpress` service (`container_name = ${PROJECT_ID}`, user `www-data`, bind-mounts `./src/...`), image built from `Dockerfile.dev` (wordpress:7.0.2 + wp-cli), joins the shared `webnet` network

Machine-specific notes (carried forward for S3/S6/S7):

- Apache `error.log` is a symlink to `/dev/stderr` in this image family → read the stream with
  `docker logs ${PROJECT_ID} 2>&1 >/dev/null` (plain `tail /var/log/apache2/error.log` blocks).
- `wp block list` is not a registered command in this WP-CLI build (verified 2026-09-24 on the
  existing `pricemrcopper` container) → at S3 verify block registration via `wp eval-file`
  iterating `WP_Block_Type_Registry` instead.
- `src/.htaccess` is bind-mounted into the container and not writable by `www-data` →
  `wp rewrite structure --hard` prints a cosmetic warning; pretty permalinks still work
  (verified in the original build at :17770).

## Stages

| Stage | Status | Date | Evidence |
|---|---|---|---|
| S0 env | NEEDS-LIVE-ENV | 2026-09-24 | This worktree has no running WordPress/DB bound to it — the gate runs on the member's machine per the S0 runbook below; flip to PASS with the four Gate S0 outputs |
| S1 analyze | PASS | 2026-09-24 | `docs/build/ui-spec.md` + `docs/build/block-map.md` complete (page-plan + behavior map + SCSS split + media inventory); `grep -ci "TBD"` = 0 in both; behavior accounting 19 view.js + 3 native + 10 dropped = 32 = `grep -c addEventListener` on the mockup; page-plan lists the 10 pages found by `grep -o 'data-page="[^"]*"' | wc -l`; WooCommerce decision: OFF (D1, static shop block) |
| S2a SCSS foundation | PASS | 2026-09-24 | Gates V1–V6 (spec §7.1) all pass: V1 `npm run build:child` exit 0, vite emits `assets/dist/css/child-style.css` 10.86 kB (baseline pre-S2a emitted no CSS file); V2 18/18 token declarations grepped in the built CSS (12 hexes + 2 prism gradients + display/body/radius/shadow; spec said "17/17" but enumerates 18 — actual 18/18); V3 18/18 foundation-rule greps (`.wrap` 64px 24px, `.eyebrow:before` 26px×6px, `.btn-primary`, `999px`, `.card` via `var(--radius)`, `.pmc-header` sticky, `nav.pmc-nav`, `.pmc-nav-toggle`, `.pmc-footer`, `1.4fr 1fr 1fr`, `.modal-overlay`, `margin-block-start:0!important`, `display:contents`, `@media(max-width:900px)`, `max-width:767px`, `prefers-reduced-motion`, `scroll-margin-top`); V4 `:root` of `_tokens.scss` diff-identical to mockup L11–32 after whitespace normalization + 5/5 spot-rules (`.wrap`, `.eyebrow::before`, `.btn-primary`, `footer a` #c6dae2, `.card`) verbatim; V5 15 section stubs / 15 `@use "sections/…"` / 21 `@use` total / `@forward "tokens"` present; V6 no Sass warnings. Live-env items NEEDS-LIVE-ENV (spec §7.2): curl `pmc-header` render check, `get_editor_stylesheets()` (needs S2b wiring), screenshots 1440/768/375 + hamburger G5 test, `docker logs` PHP check |
| S2 theme foundation | PASS | 2026-09-24 | S2a PASS (SCSS layer — see row above) + S2b PASS on static gates (spec §7.1 V1–V10): build:child exit 0 with CSS unchanged (10.86 kB — zero SCSS edits), php -l clean, theme.json valid (10 customTemplates + 2 templateParts), parts/templates href sets byte-equal to block-map §3, 11 templates present each with header/footer parts + pmc-main + post-content, logo.png extracted (PNG 500×500, 237,340 B), dist JS carries the B14 toggle, zero authoring residue in parts/templates. Live-env items NEEDS-LIVE-ENV (spec §7.2): curl pmc-header render, .active stamp on / and /about/, fonts+preconnect links, get_editor_stylesheets() contains child-style.css, screenshots 1440/768/375 + G5 hamburger functional test, docker logs PHP check |
| S3 blocks | PASS | 2026-09-24 | P1 home trio built (3/15 blocks): 3× 7-file block folders in `src/blocks`; `php -l` clean ×3; block.json `JSON.parse` ×3; `npm run build:child` exit 0 with no Sass warnings (vite CSS 12.09 kB, was 10.86 kB at S2; wp-scripts discovers 3 entries — home-hero, home-who-we-are, home-featured-works; webpack compiled successfully); built CSS contains the hero/feature-card greps (`.hero-grid`, `1.05fr .95fr`, `aspect-ratio:4/3`, `.feature-card .cap`, `rgba(20,22,30,.85)` → minified `#14161ed9`, `@media(max-width:900px)`, `.hero-grid>*{min-width:0}` @767px [S3] guard); 4 images extracted to `assets/img` (home-hero.avif 158649 B, home-feature-mad-vibe-city.png 346187 B, home-feature-music-beats.avif 166798 B, home-feature-global-logistics.avif 197715 B — signatures AVIF×3 + PNG); zero authoring-residue greps ×3 (src + built copies); 38/38 block.json default strings byte-exact in the mockup; `@use` wiring count 3, `style.scss` untouched; git scope = 3 block folders + 3 partials + 4 images + this file. P2 about pair built (5/15 blocks): +2× 7-file block folders (`about-story` L381–418, `about-pillars` L419–429); `php -l` clean ×2; block.json `JSON.parse` ×2; `npm run build:child` exit 0 with no Sass warnings (vite CSS 12.84 kB, was 12.09 kB; wp-scripts discovers 5 entries — +about-story, +about-pillars); built CSS carries the story greps (`.story-block:before`, `border-radius:16px 0 0 16px`, `.sub-cards{grid-template-columns:1fr}` inside `@media(max-width:900px)`, `.sub-card{background:var(--bg-soft)`); 30/30 content string defaults byte-exact in the mockup (film paragraph matched incl. its `<i>` tags; `imgFile` counted as infrastructure per P1 rule); zero authoring-residue greps ×2 (src + built `assets/blocks` copies); `about-story.jpg` extracted from mockup L392 (JPEG 4000×6000, 6,214,677 B decoded from 8,286,236 base64 chars); `_about-story.scss` filled (mockup L144–153 verbatim + L296 `.sub-cards` row; no [S3]-added guards, P2-D3); `@use` statement count still 21, `style.scss`/`functions.php`/`theme.json`/`_components.scss`/`_base.scss` untouched; git scope = 2 block folders (14 files) + 1 image + `_about-story.scss` + this file. P3 film pair built (7/15 blocks): +2 block folders (`film-intro` L434–449, 7 files; `film-tiers` L452–491, 8 files incl. the B12 `view.js` registered via block.json `"viewScript": "file:./view.js"` — P3-D8, zero shared-file edits; hardcoded `id="filmEnquiry"` on the wrapper with `supports.anchor:false` — P3-D2); `php -l` clean ×2; block.json `JSON.parse` ×2 + viewScript grep; `node --check view.js` exit 0; `npm run build:child` exit 0 with no Sass warnings (vite CSS 14.04 kB, was 12.84 kB; wp-scripts discovers 7 entries — +film-intro, +film-tiers; `assets/blocks/film-tiers/` emits `view.js` + `view.asset.php`); built CSS carries the tier greps (`.tier-grid`, `.tier:before` — the minifier's form of `.tier::before`, `.tier-tag`, `.tier-price`, `.tier-runtime`, `.tier p`, `.tier-note`, `border-radius:20px`, `.tier-grid{grid-template-columns:1fr}` inside `@media(max-width:900px)`); 30/30 defaults byte-exact in the mockup (14 film-intro + 16 film-tiers, entity-decoded compare incl. em-dashes/curly apostrophes/emoji and the `<b>/<i>/<div><span style>` markup; `imgFile`/`imgId`/`imgUrl`/`btnUrl` counted as infrastructure per P1 rule); zero authoring-residue greps ×2 (src + built `assets/blocks` copies); `film-production.avif` extracted from mockup L438 (AVIF, 86,177 B decoded from 114,904 base64 chars, bytes 4–12 = `ftypavif`); `_film-tiers.scss` filled (mockup L186–193 verbatim + L296 `.tier-grid` row; no [S3]-added guards, P3-D7); `@use` statement count still 21 (`^@use` grep — comment lines excluded), `style.scss`/`functions.php`/`theme.json`/`_components.scss`/`_base.scss`/`_film-intro.scss` untouched; git scope = 2 block folders (15 files) + 1 image + `_film-tiers.scss` + this file. P4 films pair built (9/15 blocks): +2 block folders (`films-list` L497–601 + filmModal L1000–1017, 8 files incl. the B10+B11 `view.js` registered via block.json `"viewScript": "file:./view.js"` — modal rendered inside the wrapper, P4-D1/P4-D2; `cta-banner` shared — films instance L603–613, 7 files, no view.js, structural `soft` variant P4-D9); `php -l` clean ×2; block.json `JSON.parse` ×2 + viewScript grep (films-list only among the new pair); `node --check view.js` exit 0; `npm run build:child` exit 0 with no Sass warnings (vite CSS 17.52 kB, was 14.04 kB; wp-scripts discovers 9 entries — +films-list, +cta-banner; `assets/blocks/films-list/` emits `view.js` + `view.asset.php`); built CSS carries the film greps (`.film-list`, `minmax(300px,1fr)`, `.film-card`, `.play-badge` 62px, `.film-tag` `border-radius:999px`, `.film-runtime` — `rgba(20,48,61,.85)` → minified `#14303dd9`, `.film-body`, `.film-meta`, `.watch-btn`, `.no-video-flag` `#8a6417`/`#fdf6e0`, `.vmodal` `#0f2733` `max-width:920px`, `.vmodal-close` 36px, `.film-hero{grid-template-columns:1fr}` inside `@media(max-width:900px)`); 42/42 string defaults byte-exact in the mockup (entity-decoded compare incl. curly apostrophe/em-dash/middots; 16 infrastructure values — `imgFile`/`imgId`/`imgUrl`/`videoUrl`/`btnUrl` — plus the `soft` boolean per P1 rule); zero authoring-residue greps (src + built `assets/blocks` copies); 5 poster images extracted (films-poster-mad-vibe-city.png PNG 482×222 346,187 B from 461,657 base64 chars, `\x89PNG` magic; films-poster-film-2…5.svg 769 B each URL-decoded from `data:image/svg+xml;utf8,`, stops `#F5F3CD`→`#B2F1F8` / `#B2F1F8`→`#85BEDB` / `#FCDB7E`→`#B2F1F8` / `#85BEDB`→`#F5F3CD`); `_films-list.scss` filled (mockup L196–207 + L212–233 verbatim, `.film-tools` L208–211 NOT ported, + L296 `.film-hero` row with the ui-spec §9 note; no [S3]-added guards, P4-D7); `@use` statement count still 21, `style.scss`/`functions.php`/`theme.json`/`_components.scss`/`_base.scss`/`_cta-banner.scss` untouched; git scope = 2 block folders (15 files) + 5 images + `_films-list.scss` + this file. P5 shop block built (10/15 blocks): +1 block folder (`shop-merchandise` mockup L618–826, 8 files incl. the B1–B4 `view.js` registered via block.json `"viewScript": "file:./view.js"` — P3-D8 pattern; ONE block for products + custom-order box per D10; `supports.anchor:true` — no hardcoded id on the wrapper, the inner `#shopGrid`/`#customBox` ids are clone markup, films-list precedent); `php -l` clean; block.json `JSON.parse` + viewScript grep; `node --check view.js` exit 0; `npm run build:child` exit 0 with no Sass warnings (vite CSS 19.48 kB, was 17.52 kB; wp-scripts discovers 10 entries — +shop-merchandise; `assets/blocks/shop-merchandise/` emits `view.js` + `view.asset.php`; built render.php byte-identical copy of src); built CSS carries the shop greps (`.shop-grid`, `minmax(240px,1fr)`, `.product img`, `aspect-ratio:1/1`, `.qty-row`, `.custom-box` + `:before` minifier form, `#customQty`, `.lock-msg` `#fdf6e0`/`#8a6417`, `.unlock-msg` `#eafbfd`/`#14586b`, `.custom-upload.locked` `grayscale(.6)`, `.upload-preview`); 69/69 content defaults + 14 `imgFile` infrastructure values byte-exact vs entity-decoded mockup (142/142 scripted checks — en-dash `S–XXL`/em-dash copy/`&amp;` decoding, alt≡name ×14, `Quantity for {name}` aria-labels ×14, selected marker on option 13 hardcoded at loop index 12, P5-D3); 14 product images extracted to `assets/img` (JPEGs 30,598–276,317 B, `ff d8 ff` decoded signatures + `/9j/` base64 prefixes ×14, ~2.0 MB total); zero authoring-residue greps (src + built `assets/blocks` copies — contenteditable/editable-img/data-label/edit-hint/Click-to-upload/console.log/addFilmBtn all 0); `_shop-merchandise.scss` filled (mockup L117–132 + L136–141 verbatim; banner L116 + `.field` L133–135 not ported per film-tiers precedent/_components; no 900px row — L295–299 has no shop selector; no [S3]-added guards, P5-D6); `@use` statement count still 21, `style.scss`/`functions.php`/`theme.json`/`_components.scss`/`_base.scss` untouched; git scope = 1 block folder (8 files) + 14 images + `_shop-merchandise.scss` + this file. P6 sponsors block built (11/15 blocks): +1 block folder (`sponsors-intro` mockup L831–841, 7 files, no view.js — zero listeners/ids in the section, the CTA is the B15 native link; `supports.anchor:true` — no hardcoded id on the wrapper, shop-merchandise precedent P6-D6); `php -l` clean; block.json `JSON.parse` + no-viewScript grep; `npm run build:child` exit 0 with no Sass warnings (vite CSS 19.48 kB **byte-identical** to P5 — 19,481 B, sha256 `3583d3e8…c271d42` unchanged, zero SCSS edits: the identity itself is the zero-drift gate per P6 spec §5; wp-scripts discovers 11 entries — +sponsors-intro; `assets/blocks/sponsors-intro/` emits `index.js`/`index.css`/`style-index.css` + `index.asset.php` and no `view.js`; built render.php byte-identical copy of src); 17/17 defaults byte-exact vs entity-decoded mockup (16 content + 1 infrastructure `btnUrl`, scripted compare; emoji codepoints 🎞️=`1f39e fe0f` / 🎤=`1f3a4` / 🤝=`1f91d`); authoring-residue greps 0 across src + built copies (`contenteditable` ×10 + `data-goto` stripped — the docblock documents the CTA port without the literal attribute, film-intro precedent); structural greps OK (`grid-3`, `icon <?php` card shape, `btn-row` with inline `margin-top:36px` kept verbatim, `sponsors_intro_url()` + `function_exists` guard P6-D2, `esc_html` ×7 call sites + `esc_url` ×1 + `esc_attr` ×1 — spec §7.7's "×14" was a miscount; the actual structure matches the about-pillars/film-intro precedent exactly); `@use` count still 21, `style.scss`/`_sponsors-intro.scss`/`functions.php`/`theme.json`/`_components.scss`/`_base.scss` untouched; git scope = 1 block folder (7 files) + this file. P7 events block built (12/15 blocks): +1 block folder (`events-process` mockup L846–860, 7 files, no view.js — zero listeners/ids in the section and block-map §2 assigns no behaviors, P7-D1; `supports.anchor:true` — no hardcoded id on the wrapper, sponsors-intro precedent P6-D6; text-column-first split with the img second, the mirror of film-intro — `.split` source order defines the sides, P7-D4; no CTA and no URL helper — the events CTA at L862–870 is the shared cta-banner's events instance landing at S5, P7-D2; esc_html-only render, no kses helper and hence no `function_exists` guard — every L846–860 text field is plain, P7-D3); `php -l` clean; block.json `JSON.parse` + no-viewScript grep; `npm run build:child` exit 0 with no Sass warnings (vite CSS 19.48 kB **byte-identical** to P5/P6 — 19,481 B, sha256 `3583d3e8…c271d42` unchanged, zero SCSS edits: the identity itself is the zero-drift gate, P6 precedent; wp-scripts discovers 12 entries — +events-process; `assets/blocks/events-process/` emits `index.js`/`index.css`/`style-index.css` + `index.asset.php` and no `view.js`; built render.php byte-identical copy of src); 13/13 string defaults byte-exact vs entity-decoded mockup (eyebrow, title — the straight ASCII apostrophe asserted, not the mockup's usual curly `’`, intro, 3×{n,heading,text} with the `&amp;` entities decoded and the step-2 em-dash kept, imgAlt; `imgId`/`imgUrl`/`imgFile` counted as infrastructure per P1 rule — spec §8.5's "11" enumerates the same string set); authoring-residue greps 0 across src + built copies (`contenteditable`/`editable-img`/`data-label`/`edit-hint`/`title="Click to upload`/`data-goto`/`console.log` all 0 — the docblock documents the img-hook strip without the literal attribute, film-intro precedent); structural greps OK (`split` → text `<div>` (section-sub + step-list) → `<img>` last; `esc_html` 7 grep hits = 6 call sites + 1 docblock mention — spec §8.7's "×6" counts call sites, sponsors-intro precedent — plus `esc_url` ×1 + `esc_attr` ×1; `get_block_wrapper_attributes(['class' => 'wrap'])`; both `.split` rules verified in the built CSS — desktop `grid-template-columns:1fr 1fr` + the 900px `1fr` row); `event-production.avif` extracted from mockup L858 (AVIF, 58,930 B decoded from 78,576 base64 chars, bytes 4–12 = `ftypavif`); `@use` statement count still 21, `style.scss`/`_events-process.scss`/`functions.php`/`theme.json`/`_components.scss`/`_base.scss` untouched; git scope = 1 block folder (7 files) + 1 image + this file. P8 upcoming-events block built (13/15 blocks): +1 block folder (`upcoming-list` mockup L875–911 + ticketModal L1021–1065, 8 files incl. the B5–B9 `view.js` registered via block.json `"viewScript": "file:./view.js"` — P3-D8 pattern; the modal renders as the last child inside the wrapper P8-D1, films-list P4-D1 precedent; `data-checkout-url=""` hardcoded verbatim on every card P8-D2; esc_html-only render, no kses helper and hence no `function_exists` guard P8-D3; teaser video keeps `autoplay="" loop="" muted="" playsinline=""` + poster, no `src` P8-D4; `#tmEmail href="#"` kept P8-D5; `id="eventsGrid"` + all modal ids kept verbatim P8-D6); `php -l` clean; block.json `JSON.parse` + viewScript grep; `node --check view.js` exit 0; `npm run build:child` exit 0 with no Sass warnings (vite CSS 19,481 B → 24,611 B, sha256 `6f54eabe…cbcb92bea` — a stub-partial rebuild reproduced the recorded P5–P7 baseline 19,481 B sha256 `3583d3e8…c271d42` byte-for-byte, and the P7→P8 CSS diff adds exactly +5,131 B of upcoming-list-owned selectors with zero removals — only `.ticket-line`/`.tprice`/`.modal-head`/`.modal-close`/`.modal-body`/`.qty-stepper`/`#attendeeList`/`.attendee-row`/`.modal-sep`/`.summary-row`/`.summary-total`/`.modal-foot`/`.modal-msg`/`.checkout-panel`/`.err-msg`/`.events-grid`/`.event-card`/`.event-media`/`.event-badge`/`.event-body`/`.event-date`/`.event-info`/`.event-meta` + the 900px rows, minifier rgba→hex as usual; wp-scripts discovers 13 entries — +upcoming-list; `assets/blocks/upcoming-list/` emits `view.js` + `view.asset.php`; built render.php byte-identical copy of src); built CSS carries the event greps (`.events-grid`, `.event-card` `1.15fr 1fr`, `.event-badge`, `.event-date`, `.ticket-line`, `.tprice`, `.modal-head`, `.qty-stepper`, `.attendee-row`, `.modal-sep`, `.summary-total`, `.modal-foot`, `.modal-msg`, `.checkout-panel`, `.err-msg` `#8a3d3d`, `.event-card{grid-template-columns:1fr}` + `.event-body{padding:24px}` inside the 900px tier; no `.vhint`/`.add-event` — V10); 11/11 string defaults byte-exact vs entity-decoded mockup L876–901 (incl. U+2014/U+2019/📍 U+1F4CD/📅 U+1F4C5; `videoPoster{Id,Url,File}` counted as infrastructure per P1 rule) + the modal markup L1021–1065 byte-equal to the mockup (39 lines after leading-whitespace trim); zero authoring-residue greps (src + built `assets/blocks/upcoming-list/` copies: `contenteditable`/`editable-video`/`vhint`/`addEventBtn`/`edit-hint`/`data-label`/`console.log` all 0 — the docblock documents the strips without the literal attributes, film-intro precedent); structural greps OK (`id="eventsGrid"`, `data-checkout-url`, `id="ticketModal"`, `role="dialog"`, `aria-labelledby="tmTitle"`, the 4 stepper/close aria-labels, `autoplay="" loop="" muted="" playsinline=""` + `poster=`, `id="tmEmail" href="#"`, `esc_html` ×10 call sites + 1 docblock mention, `esc_url` ×1, `get_block_wrapper_attributes(['class' => 'wrap'])`); `event-teaser-poster.svg` extracted from mockup L883 (SVG, 820 B URL-decoded from the `data:image/svg+xml;utf8,` poster payload, starts `<svg xmlns="http://www.w3.org/2000/svg" width="900"`, gradient stops `#B2F1F8`→`#85BEDB`, decorative circles + "EVENT VIDEO" text); `_upcoming-list.scss` filled (mockup L236–237 + L242–248 modal-chrome open-issue port + L249–274 + L277–290 minus `.vhint`/`.add-event` + the 900px owner rows; L238–241 stays in _components; no [S3]-added guards P8-D7); `@use` statement count still 21, `style.scss`/`functions.php`/`theme.json`/`_components.scss`/`_base.scss` untouched; git scope = 1 block folder (8 files) + 1 image + `_upcoming-list.scss` + this file. P9 final pair built (15/15 blocks — S3 static work complete): +2 block folders (`enquiries-form` mockup L928–951, 8 files incl. the B13 `view.js` registered via block.json `"viewScript": "file:./view.js"` — P3-D8 pattern, 5th viewScript block; `contact-details` mockup L956–968, 7 files, no view.js — zero listeners in the section and block-map §2 assigns none, P7-D1 precedent; `supports.anchor:true` on both — no hardcoded wrapper ids, the inner `enquiryForm`/`enq*`/`enquiryOk` ids kept verbatim as clone markup, P6-D6/P4-D10 precedents; esc_html-only renders — no kses helper and hence no `function_exists` guard — optgroup label via `esc_attr`, mapUrl via `esc_url` with the `?:` mockup-URL terminal fallback so the iframe never renders an empty src, P9-D3/P9-D8; form chrome static verbatim — labels/placeholders/`— Select a subject —`/submit label/`.form-ok` incl. ✅ + straight ASCII apostrophes, P9-D4; select split `subjectOptions`×3 + `filmGroupLabel` + `filmOptions`×2 in mockup DOM order, contact `items[4] {icon, heading?, lines[]}` with the `<b>` heading only when present, P9-D5); `php -l` clean ×2; block.json `JSON.parse` ×2 + viewScript grep (present in enquiries-form, absent in contact-details); `node --check view.js` exit 0; `npm run build:child` exit 0 with no Sass warnings (vite CSS 24,611 B → 25,419 B, sha256 `6f54eabe…cbcb92bea` → `165b3158…fb810024`; byte-level CSS diff = exactly +808 B of contact-owned selectors — `.map-frame` `height:380px`, `.contact-grid` `1fr 1.2fr`, `.contact-item` family, `.contact-item .ct` sub-rules incl. the dead `.ct a` pair (P9-D9, clone doctrine), `.contact-item .ci`, + the 900px `.contact-grid{grid-template-columns:1fr}` row — with 0 bytes removed; wp-scripts discovers 15 entries — +enquiries-form, +contact-details; `assets/blocks/enquiries-form/` emits `view.js` + `view.asset.php`; built render.php byte-identical copies of src ×2); 23/23 block.json default strings + all static form/iframe chrome byte-exact vs the entity-decoded, D5-stripped mockup (scripted compare: U+2014 ×1 in sub + ×2 in the empty option, U+2705, U+2026, 📍 U+1F4CD, ✉️ U+2709+U+FE0F VS16 kept, 📞 U+1F4DE, 🕘 U+1F558, U+2013 ×2 in the hours line, straight ASCII apostrophes in `we'll`/`We'll`/`We'd`, `&amp;`→`&` in filmOptions[1] + mapUrl); zero authoring-residue greps ×2 (src + built `assets/blocks` copies — docblocks document the strips without the literal attributes, film-intro precedent); structural greps OK (`id="enquiryForm"`, `id="enquiryOk"`, `<optgroup` ×2 (docblock + markup), `for="enq*"` ×4, `Send Enquiry`, `class="contact-grid"`, `loading="lazy"`, `title="Map"`, `get_block_wrapper_attributes(['class' => 'wrap'])` ×2, `esc_url` ×1 (call site; +1 docblock mention), built CSS carries `.contact-grid`/`1fr 1.2fr`/`height:380px`/`.map-frame`/`.contact-item .ci`/`.contact-item .ct a:hover` + the 900px row); `_contact-details.scss` filled (mockup L166–175 verbatim + L296 `.contact-grid` row; no [S3]-added guards, P8-D7 precedent); `_enquiries-form.scss` untouched — ships empty permanently (component-driven, P7-D7 precedent); `@use` statement count still 21, `style.scss`/`functions.php`/`theme.json`/`_components.scss`/`_base.scss` untouched; git scope = 2 block folders (15 files) + `_contact-details.scss` + this file — no images this batch (the contact map is an `<iframe>`, not an asset). Live-env items NEEDS-LIVE-ENV (registration via `wp eval` WP_Block_Type_Registry over all 15 names, docker logs PHP check) — commands in "S3 live-env checks" below (task SPEC §9.2) |
| S4 editor parity | PASS (static verification pass) | 2026-09-24 | Static preconditions V1–V9 all green, **zero code changes** (git clean; the only edit is this file; `npm run build:child` re-run rewrote `child-style.css` byte-identically, assets/blocks gitignored): **V1** wiring — `add_theme_support('editor-styles')` functions.php:134 + `add_editor_style($path)` :156 behind a `file_exists` guard; manifest `style.scss` entry keys = `file, isEntry, src` (no `css` key; `file = css/child-style.css` ends `.css`) → elseif branch → `assets/dist/css/child-style.css` (exists on disk, 25,419 B). **V2** no admin enqueue traps — `ai_zippy_child_enqueue_vite()` called only inside `wp_enqueue_scripts` (functions.php:106–111); editor fonts on `enqueue_block_assets` with `is_admin()` guard and `[]` deps (:167–173); `ai_zippy_child_style_deps()` guards `ai-zippy-theme-css-0` with `wp_style_is(...,'registered')` (:281–283). **V3** block contract — `"apiVersion": 3` ×15/15, ServerSideRender in edit.js ×15/15, `return null` ×15/15, bare `useBlockProps()` ×15/15 (0 non-bare), `"viewScript"` on exactly the 5 behavior blocks (film-tiers, films-list, shop-merchandise, upcoming-list, enquiries-form), `php -l` render.php ×15 clean, `node --check` view.js ×5 clean, JSON.parse 16/16 (15 block.json + theme.json). **V4** wrapper hygiene — wrapper-class selectors in `src/scss/` = only the `_base.scss` §5 reset (:95–96: doubled `.block-editor-block-list__block`, both `[class*=wp-block-ai-zippy-]`/`[class*=zippy-block-ai-zippy-]` families, unprefixed by design — the prior-art S4 root cause is absent); `editor-styles-wrapper` in `src/scss/` = 2 comment hits, 0 selectors; 0 direct-child selectors rooted at a wrapper. **V5** built CSS — `npm run build:child` exit 0, no Sass warnings; `child-style.css` 25,419 B sha256 `165b31582c1c04c73d987fbc4d491100dea508b1fdbc83e571b14e91fb810024` **byte-identical to the P9 baseline** (the identity is the zero-drift gate, P6 precedent); built file carries the canvas reset minified (`.block-editor-block-list__block.block-editor-block-list__block.wp-block[class*=wp-block-ai-zippy-],…[class*=zippy-block-ai-zippy-]{max-width:none;width:auto}`), blockGap reset `margin-block-start:0!important`, the 8px `section+section{margin-top:8px!important}` re-application, `:root{` ×2. **V6** viewport/fixed audit — exactly the 2 accepted occurrences: `.modal-overlay` (`position:fixed` + `display:none` default, `_components.scss:77` — modal chrome, hidden in canvas exactly like the un-interacted frontend) and `--pmc-nav-panel-max-h:calc(100vh - 90px)` (`_tokens.scss:50` — header part, not part of the post-editor canvas); no `100dvh`. **V7** no-JS default states = canvas states — built CSS hides all interactive-only chrome by CSS alone: `.modal-overlay{…display:none`, `.form-ok{display:none…}`, `.err-msg{display:none…}` (= `#tmErr`), `.checkout-panel{display:none…}` (= `#tmPanel`), inline `style="display:none"` on `#customPreview`/`#unlockMsg`/`#fmVideo` (render.php clone markup), `.custom-upload.locked{opacity:.45;pointer-events:none;filter:grayscale(.6)}` ships locked; `is-loaded|skeleton|shimmer` in view.js = 0 hits → no `editor.scss` doubled-class neutralization required (blocks.md §4). **V8** canvas media resolvability — recursive walk of all block.json attribute defaults (incl. nested `cards`/`products`/`films` arrays): 27/27 media filenames exist in `assets/img` (28 files; the extra is header-part `logo.png`) → canvas SSR images resolve, 0 canvas 404s (theme-asset URLs until S5 — identical on both sides of the compare, must be `/wp-content/uploads/` by the S5 gate G3). **V9** syntax battery — `php -l` functions.php clean + render.php ×15 clean, `node --check` ×5, JSON.parse ×16; block-folder `style.scss`/`editor.scss` comment-stubs only (0 non-comment lines in src and built copies); theme.json layout `contentSize`/`wideSize` = 1180px (the cap the §5 reset beats) + 10 customTemplates + 2 templateParts. Live canvas parity NEEDS-LIVE-ENV — commands in "S4 live-env checks" below |
| S5 content & media | NEEDS-LIVE-ENV | 2026-09-24 | seeding scripts authored + static battery green (V1–V8, task SPEC §7); the gate itself runs on the member's machine per the S5–S7 runbook below |
| S6 visual QA | NEEDS-LIVE-ENV | 2026-09-24 | seeding scripts authored + static battery green (V1–V8, task SPEC §7); the gate itself runs on the member's machine per the S5–S7 runbook below |
| S7 functional QA | NEEDS-LIVE-ENV | 2026-09-24 | seeding scripts authored + static battery green (V1–V8, task SPEC §7); the gate itself runs on the member's machine per the S5–S7 runbook below |

### S0 runbook — run locally by the member

Concrete values: `PROJECT_ID=pricemrcopper_epos`, `PORT=18770`, site title `PricemrCopper
Company`. Commands are idempotent (verify-or-bootstrap). Steps 2+ assume the `.env` variables
are exported (step 2 does it) — run the steps in one shell session, or re-run
`set -a; . ./.env; set +a` before any step that uses `${PROJECT_ID}` / `${PORT}` /
`${WP_ADMIN_PASSWORD}`.

#### Step 0 — preconditions

```bash
docker info >/dev/null && echo "docker OK"
docker ps --format '{{.Names}}' | grep -E '^(mysql|phpmyadmin)$'   # expect both lines
docker start mysql phpmyadmin                                       # only if one is missing/stopped
```

`mysql` and `phpmyadmin` live on the shared `webnet` network (the network this repo's compose
joins). If they are ever absent entirely, the studio's shared infra owns them — out of this
repo's scope.

Working directory — the **persistent main checkout**, never a worktree (compose bind-mounts
`./src/...`; a worktree mount breaks when the worktree is cleaned up). Run this after this
task's PR is merged:

```bash
cd /home/tobithongha/src-test-epos-tool
git log --oneline -1        # shows the merged BUILD-STATE.md commit
ls .env.sample docker-compose.yml Dockerfile.dev pricemrcopper_final_mockup.html
```

#### Step 1 — `.env`

```bash
cp .env.sample .env      # never edit .env.sample
```

Check the proposed port is free (re-roll if busy):

```bash
PORT=18770
ss -ltn | grep -q ":${PORT}\b" && echo "BUSY — roll again" || echo "FREE ${PORT}"
docker ps --format '{{.Ports}}' | grep -q ":${PORT}->" && echo "BUSY — roll again"
```

If busy, re-roll and use the new value everywhere:

```bash
PORT=$(shuf -i 13000-19999 -n 1)   # then re-run both checks above
```

Edit `.env` and set:

- `PROJECT_ID=pricemrcopper_epos`
- `PORT=18770` (or the re-rolled value)
- `PROJECT_HOST=http://localhost:18770`
- `WORDPRESS_DB_NAME=pricemrcopper_epos`

Keep unchanged: `WORDPRESS_DB_HOST=mysql`, `WORDPRESS_DB_USER=root` / `WORDPRESS_DB_PASSWORD`,
`WORDPRESS_TABLE_PREFIX=fcs_data_`, `WORDPRESS_CONFIG_EXTRA`. After any re-roll, update
`PROJECT_HOST` and `WORDPRESS_DB_NAME` so they still match the final `PORT` / `PROJECT_ID`.

Generate the admin password and append it to `.env` (gitignored — the only place it may live):

```bash
WP_ADMIN_PASSWORD=$(openssl rand -base64 18)
printf "\n# WP ADMIN (local only)\nWP_ADMIN_USER=admin\nWP_ADMIN_PASSWORD='%s'\n" "$WP_ADMIN_PASSWORD" >> .env
```

#### Step 2 — database on the shared MySQL container

```bash
set -a; . ./.env; set +a
docker exec mysql mysql -uroot -p123456 -e \
  "CREATE DATABASE IF NOT EXISTS \`${PROJECT_ID}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
docker exec mysql mysql -uroot -p123456 -e "SHOW DATABASES LIKE '${PROJECT_ID}';"
```

Expected: one row, `pricemrcopper_epos`. Idempotent (`IF NOT EXISTS`).

#### Step 3 — start the stack

```bash
docker compose up -d      # first run builds the WordPress image from Dockerfile.dev (wordpress:7.0.2 + wp-cli) — takes a few minutes
sleep 10
docker compose ps         # pricemrcopper_epos must be Up
```

If the container restarts in a loop: `docker logs --tail 50 ${PROJECT_ID}` — usually a wrong
`WORDPRESS_DB_*` value or the database was not created.

#### Step 4 — install WordPress + activate the child theme

```bash
docker exec ${PROJECT_ID} wp core install \
  --url="http://localhost:${PORT}" --title="PricemrCopper Company" \
  --admin_user=admin --admin_password="${WP_ADMIN_PASSWORD}" \
  --admin_email="${DEV_EMAIL:-dev@zippy.sg}" --skip-email --allow-root

docker exec ${PROJECT_ID} wp theme activate ai-zippy-child --allow-root
docker exec ${PROJECT_ID} wp rewrite structure '/%postname%/' --hard --allow-root
docker exec ${PROJECT_ID} wp option update blogdescription '' --allow-root
```

`rewrite structure --hard` prints a cosmetic warning about `.htaccess` (bind-mounted, not
writable by `www-data`) — pretty permalinks still work; ignore it.

#### Step 4b — design-driven plugin (tentative)

```bash
docker exec ${PROJECT_ID} wp plugin activate woocommerce --allow-root
```

The mockup contains a shop, so WooCommerce is activated tentatively; final scope is confirmed
at S1. ACF stays inactive unless S1 requires it.

#### Step 5 — uploads permission (do it now, not after the first sideload failure)

```bash
docker compose exec -T -u root wordpress sh -c \
  'chown -R www-data:www-data /var/www/html/wp-content/uploads && chmod 775 /var/www/html/wp-content/uploads'
```

#### Step 6 — first build so the theme has assets

```bash
npm install        # node_modules is missing in a fresh checkout
npm run build:child
```

Expected: exit 0; `src/wp-content/themes/ai-zippy-child/assets/dist/css/child-style.css`,
`src/wp-content/themes/ai-zippy-child/assets/dist/js/child-theme.js` and the Vite manifest
appear. wp-scripts prints "No entry file discovered" for the empty `src/blocks` — expected, not
a failure (blocks land at S3).

#### Step 7 — Gate S0 (all four outputs are the evidence)

```bash
docker compose ps | grep -w "${PROJECT_ID}"                                # Up
curl -s -o /dev/null -w "%{http_code}\n" "http://localhost:${PORT}/"       # 200 / 302
docker exec ${PROJECT_ID} wp core is-installed --allow-root && echo INSTALLED
docker exec ${PROJECT_ID} wp theme list --status=active --allow-root       # ai-zippy-child
```

Paste the four outputs back → S0 flips to PASS with them as evidence.

#### Common failures

| Symptom | Cause | Fix |
|---|---|---|
| `Error establishing a database connection` | DB not created, or `WORDPRESS_DB_HOST` ≠ `mysql` | Step 2, then `docker compose restart` |
| Container name clash | `PROJECT_ID` already used by another site | Pick a new `PROJECT_ID` |
| Port in use | Port collides with a running container | Roll a new `PORT`, `docker compose up -d` again |
| Redirect loop to another port | `siteurl`/`home` stale after changing `PORT` | `wp option update siteurl/home "http://localhost:${PORT}"` |
| `wp: command not found` | Running wp-cli on the host instead of in the container | Always `docker exec ${PROJECT_ID} wp ...` |
| `tail /var/log/apache2/error.log` hangs / no logs | `error.log` is a symlink to `/dev/stderr` in this image family | `docker logs ${PROJECT_ID} 2>&1 >/dev/null` |
| `'block' is not a registered wp command` | This wp-cli build lacks `wp block list` | Verify registration via `wp eval-file` over `WP_Block_Type_Registry` (needed at S3) |

## Blocks

| Block | Source section | render.php | SCSS partial | Seeded | Editor OK |
|---|---|---|---|---|---|
| `ai-zippy/home-hero` | mockup L337–350 | literal clone (D4/D5 cleanup) | `_home-hero.scss` filled (L79–88 + L296 row + [S3] G5 guard) | — (S5) | static OK — canvas NEEDS-LIVE-ENV (S4 live) |
| `ai-zippy/home-who-we-are` | mockup L352–364 | literal clone (inline styles kept) | `_home-who-we-are.scss` ships empty (component-driven) | — (S5) | static OK — canvas NEEDS-LIVE-ENV (S4 live) |
| `ai-zippy/home-featured-works` | mockup L366–376 | literal clone | `_home-featured-works.scss` filled (L110–114) | — (S5) | static OK — canvas NEEDS-LIVE-ENV (S4 live) |
| `ai-zippy/about-story` | mockup L381–418 | literal clone (inline styles kept; `introBlock` P2-D1; kses italics P2-D2) | `_about-story.scss` filled (L144–153 + L296 `.sub-cards` row) | — (S5) | static OK — canvas NEEDS-LIVE-ENV (S4 live) |
| `ai-zippy/about-pillars` | mockup L419–429 | literal clone (single-line cards) | `_about-pillars.scss` ships empty (component-driven) | — (S5) | static OK — canvas NEEDS-LIVE-ENV (S4 live) |
| `ai-zippy/film-intro` | mockup L434–449 | literal clone (D5 cleanup; kses intro P3-D3; CTA → real anchor via `film_intro_url()` P3-D1) | `_film-intro.scss` ships empty (component-driven) | — (S5) | static OK — canvas NEEDS-LIVE-ENV (S4 live) |
| `ai-zippy/film-tiers` | mockup L452–491 | literal clone (hardcoded id `filmEnquiry` P3-D2; kses tier text P3-D3; B12 view.js via block.json viewScript P3-D8) | `_film-tiers.scss` filled (L186–193 + L296 `.tier-grid` row) | — (S5) | static OK — canvas NEEDS-LIVE-ENV (S4 live) |
| `ai-zippy/films-list` | mockup L497–601 + filmModal L1000–1017 | literal clone (modal as last child inside the wrapper P4-D1; film-1 mirror hardcoded P4-D2; kses desc P4-D3; no-video chip on empty videoUrl P4-D4; `id="filmGrid"` kept P4-D10; B10+B11 view.js via block.json viewScript) | `_films-list.scss` filled (L196–207 + L212–233, `.film-tools` L208–211 dropped + L296 `.film-hero` row) | — (S5) | static OK — canvas NEEDS-LIVE-ENV (S4 live) |
| `ai-zippy/cta-banner` (shared — films instance; events L862–870 + upcoming L913–923 land at S5) | mockup L603–613 | literal clone (structural `soft` variant P4-D9; inline card style kept; `<button data-goto>` → real `<a>` via `cta_banner_url()`) | `_cta-banner.scss` ships empty (component-driven) | — (S5) | static OK — canvas NEEDS-LIVE-ENV (S4 live) |
| `ai-zippy/shop-merchandise` | mockup L618–826 | literal clone (D5 cleanup incl. §6.3 products 4/9 residue; kses customSub/lockMsg P5-D1; selected option 13 P5-D3; B1–B4 view.js via block.json viewScript) | `_shop-merchandise.scss` filled (L117–132 + L136–141, banner L116 + .field L133–135 not ported per precedent/_components; no 900px row) | — (S5) | static OK — canvas NEEDS-LIVE-ENV (S4 live) |
| `ai-zippy/sponsors-intro` | mockup L831–841 | literal clone (D5 cleanup; CTA → real anchor via `sponsors_intro_url()` P6-D2) | `_sponsors-intro.scss` ships empty (component-driven) | — (S5) | static OK — canvas NEEDS-LIVE-ENV (S4 live) |
| `ai-zippy/events-process` | mockup L846–860 | literal clone (D5 cleanup; text-column-first split P7-D4; esc_html-only P7-D3; no view.js P7-D1) | `_events-process.scss` untouched — component-driven, ships empty permanently (P7-D7) | — (S5) | static OK — canvas NEEDS-LIVE-ENV (S4 live) |
| `ai-zippy/upcoming-list` | mockup L875–911 + ticketModal L1021–1065 | literal clone (modal as last child inside the wrapper P8-D1; data-checkout-url="" verbatim P8-D2; esc_html-only P8-D3; B17 video attrs baked P8-D4; #tmEmail href="#" kept P8-D5; B5–B9 view.js via block.json viewScript) | `_upcoming-list.scss` filled (L236–237 + L242–248 + L249–274 + L277–290 + 900px rows; L238–241 stays in _components; no [S3]-added guards P8-D7) | — (S5) | static OK — canvas NEEDS-LIVE-ENV (S4 live) |
| `ai-zippy/enquiries-form` | mockup L928–951 | literal clone (esc_html-only P9-D3; form chrome static P9-D4; optgroup split P9-D5; B13 view.js via block.json viewScript) | `_enquiries-form.scss` ships empty (component-driven) | — (S5) | static OK — canvas NEEDS-LIVE-ENV (S4 live) |
| `ai-zippy/contact-details` | mockup L956–968 | literal clone (esc_html-only + esc_url mapUrl P9-D3; items[4] {icon, heading, lines} P9-D5; no view.js P9-D1) | `_contact-details.scss` filled (L166–175 + L296 `.contact-grid` row) | — (S5) | static OK — canvas NEEDS-LIVE-ENV (S4 live) |

Rows land at S3 from `docs/build/block-map.md` §2 — 15 blocks, namespace `ai-zippy/` (page-plan in §1).
15 of 15 built (P1–P9) — S3 static work complete; live-env registration check
pending (member runs the updated S3 live-env checklist).

### S3 live-env checks — run locally by the member (after S0 bootstrap)

```bash
set -a; . ./.env; set +a
npm run build:child   # assets/blocks/ is gitignored — build before testing

# Block registration (wp block list is NOT available in this image — see Environment)
docker exec ${PROJECT_ID} wp eval 'foreach (["home-hero","home-who-we-are","home-featured-works","about-story","about-pillars","film-intro","film-tiers","films-list","cta-banner","shop-merchandise","sponsors-intro","events-process","upcoming-list","enquiries-form","contact-details"] as $b) { $n="ai-zippy/".$b; printf("%-32s %s\n", $n, WP_Block_Type_Registry::get_instance()->is_registered($n) ? "registered" : "MISSING"); }' --allow-root

# No PHP fatals (error.log is a /dev/stderr symlink in this image family)
docker logs ${PROJECT_ID} 2>&1 >/dev/null | tail -50
```

Expected: 15× `registered`, error stream free of new PHP Fatal/Warning. (Frontend render +
editor-canvas checks happen at S4/S5 once pages exist — outside the S3 batches' range.)

### S4 live-env checks — run locally by the member (after the S3 live-env checks)

Preconditions: S0 bootstrap done; the S3 live-env check printed 15× `registered`;
`npm run build:child` re-run (assets/blocks is gitignored). Export `.env` first:
`set -a; . ./.env; set +a`.

**Gate 1 — the CSS must be in the canvas:**

```bash
docker exec ${PROJECT_ID} wp eval 'print_r(get_editor_stylesheets());' --allow-root
```

→ the array must contain `…/ai-zippy-child/assets/dist/css/child-style.css`. If not, nothing
else matters (S4 Step 1) — report the output, do not improvise.

**Verification surface — scratch draft page with all 15 blocks (prior-art method):**

```bash
docker exec ${PROJECT_ID} wp post create \
  --post_type=page --post_title='pmc-s4-scratch' --post_name='pmc-s4-scratch' \
  --post_status=draft --page_template=page-home --porcelain --allow-root \
  --post_content="$(printf '<!-- wp:ai-zippy/%s /-->\n\n' \
    home-hero home-who-we-are home-featured-works about-story about-pillars \
    film-intro film-tiers films-list cta-banner shop-merchandise sponsors-intro \
    upcoming-list events-process enquiries-form contact-details)"
```

→ prints the page ID. cta-banner runs once with its defaults (the events/upcoming attribute
variants are S5 content, verified through the real pages at S5/S6).

**Gate 2 — canvas vs frontend, per block (manual, per the skill's S4 Step 3):**

1. Log into wp-admin (credentials in `.env`).
2. Open `/wp-admin/post.php?post={ID}&action=edit`; screenshot the full canvas.
3. Open the Preview (logged-in draft preview of `/?page_id={ID}`); screenshot.
4. Compare section by section, all 15 blocks: background, spacing, grid, image sizing,
   overlay position. Optional objective assist (prior-art method): compare computed styles
   editor-iframe vs frontend — `padding-top/bottom`, `background-color`,
   `background-image`, `grid-template-columns` column count — all 15 must match.
5. Known non-defects: canvas iframe ≈ 1160px at a 1440 browser (full-bleed `hero`/`soft`
   span the canvas; `.wrap` stays 1180px in both); modals hidden on both sides; `view.js`
   behaviors inert in the canvas (no-JS default state — that is the compared state).
6. Any real diff → fix per the S4 conditional protocol (section CSS →
   `src/scss/sections/_{block}.scss`; markup → `src/blocks/{block}/render.php`; never
   hand-prefix the `_base.scss` §5 reset, never move CSS into `src/blocks/*/style.scss`)
   → `npm run build:child` → re-capture (≤10 iterations).

**Gate 3 — console:** zero JS exceptions, zero ServerSideRender failures on the canvas.
Expected benign entries: theme-asset image URLs until S5, core `__next40pxDefaultSize`
deprecation warnings (prior-art carry-forward).

**Gate 4 — build still green:** `npm run build:child` → exit 0.

**Gate 5 — no PHP noise:** `docker logs ${PROJECT_ID} 2>&1 >/dev/null | tail -50` →
no new PHP Fatal/Warning (error.log is a /dev/stderr symlink in this image family).

**Cleanup + paste-back:** `docker exec ${PROJECT_ID} wp post delete {ID} --force --allow-root`;
paste the five gate outputs back → the Leader flips the 15 `Editor OK` cells to `yes` and
closes the S4 live remainder.

### S5–S7 runbook — run locally by the member (after the S0 + S3-live + S4-live checks)

Preconditions: Gate S0 outputs pasted (container up, theme active); the S3 live-env check
printed 15× `registered`; the S4 live-env gates done. Then:

    set -a; . ./.env; set +a
    npm run build:child        # assets/blocks/ is gitignored — build before testing

#### S5 Step 1 — uploads permission (S0 Step 5, again — do not skip)

    docker compose exec -T -u root wordpress sh -c \
      'chown -R www-data:www-data /var/www/html/wp-content/uploads && chmod 775 /var/www/html/wp-content/uploads'

#### S5 Step 2 — sideload the 28 media assets

    docker compose cp scripts/upload-images.php wordpress:/tmp/upload-images.php
    docker compose exec -T wordpress php -d memory_limit=1024M /tmp/upload-images.php

Expected: 28× `uploaded` (re-run: 28× `skip`), `failed=0`, `map entries=28`, exit 0.
Any `FAILED` line → paste it back verbatim; do not improvise.

#### S5 Step 3 — seed the 10 pages

    docker compose cp scripts/seed-pages.php wordpress:/tmp/seed-pages.php
    docker compose exec -T wordpress php /tmp/seed-pages.php

Expected: 10× `created …` (re-run: `updated …`) + `front page -> {ID}`. WARNING: re-running
overwrites post_content for these 10 slugs — do not re-run after editing pages in wp-admin.

#### S5 Step 4 — Gate S5 (paste all outputs back)

a) Page list (expect 10 rows, publish, slugs home/about/film/films/shop/sponsors/upcoming/events/enquiries/contact):

    docker exec ${PROJECT_ID} wp post list --post_type=page --fields=ID,post_name,post_status --allow-root

b) Front page (expect `page`, then the home page ID from Step 3):

    docker exec ${PROJECT_ID} wp option get show_on_front --allow-root
    docker exec ${PROJECT_ID} wp option get page_on_front --allow-root

c) Slugs resolve (expect 200 on every line; "" is the front page):

    for s in "" about film films shop sponsors upcoming events enquiries contact; do
      printf '%-14s %s\n' "/$s" "$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:${PORT}/$s")"
    done

d) Media-served check — `.avif` ADDED to the skill's grep (the original G3 pattern misses this
   repo's AVIF assets and would pass vacuously). Every hit must contain `/wp-content/uploads/`:

    for s in "" about film films shop sponsors upcoming events enquiries contact; do
      echo "== /$s"
      curl -s "http://localhost:${PORT}/$s" | grep -o 'src="[^"]*\.\(jpg\|jpeg\|png\|webp\|svg\|avif\)"' | sort -u
    done

   Negative check — expect 0 on every page (this also covers the header logo, which resolves
   through the same media map via [pmc_logo_url]):

    for s in "" about film films shop sponsors upcoming events enquiries contact; do
      printf '/%-12s themes-hits=%s\n' "$s" "$(curl -s "http://localhost:${PORT}/$s" | grep -c 'src="[^"]*themes/')"
    done

e) Internal link crawl (expect 200 on every internal href; `#filmEnquiry` is a fragment on
   /film/ and must also be 200; mailto:/tel:/maps links are external and skipped):

    for s in "" about film films shop sponsors upcoming events enquiries contact; do
      curl -s "http://localhost:${PORT}/$s"
    done | grep -o 'href="/[^"]*"' | sort -u | while read -r h; do
      url="${h#href=\"}"; url="${url%\"}"
      printf '%-30s %s\n' "$url" "$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:${PORT}${url}")"
    done

Paste a)–e) back → the Leader flips S5 to PASS and marks the Blocks-table Seeded column.

#### S6 — visual QA loop (desktop G4 + responsive G5)

Reference of truth: `pricemrcopper_final_mockup.html` opened in a browser at 1440px width.
The HOME screen is `.page active` on load (mockup L336); reach the other 9 screens by clicking
the nav items (SPA `showPage`). The mockup has no <900px design beyond grid collapse — judge
768px/375px against the G5 checklist, not against the mockup.

Captures (site): 10 pages × 1440/768/375. If `chrome-headless-shell` is on PATH:

    command -v chrome-headless-shell || echo "use a browser session instead"
    for W in 1440 768 375; do
      for s in "" about film films shop sponsors upcoming events enquiries contact; do
        name="${s:-home}"
        chrome-headless-shell --headless --disable-gpu --hide-scrollbars \
          --window-size=${W},8000 --screenshot=/tmp/s6-${name}-${W}.png \
          --virtual-time-budget=15000 "http://localhost:${PORT}/${s}"
      done
    done

Otherwise drive a browser session and take full-page screenshots (30 captures total).
Captures (mockup reference): 10 screenshots, one per nav screen, at 1440px.

Compare per page, in this order: presence and order of sections → box geometry → grid/columns
→ colors → image crop/ratio → text content → CTA text and href → interactive elements.
Font family / glyph width / letter-spacing are OUT of scope (qa-gates: 10–30% glyph-width
differences at matching pitch are not defects).

Fix loop: any diff → fix in `src/scss/sections/_{block}.scss` (CSS) or
`src/blocks/{block}/render.php` (markup) → `npm run build:child` → re-capture. Max 10
iterations total, max 3 per specific bug. Never conclude from code alone.

Responsive (mandatory G5), per page at 375px and 768px:
- no horizontal scroll — devtools console: `document.documentElement.scrollWidth <= document.documentElement.clientWidth`
- no text overflowing/overlapping into the next section
- grids collapsed to 1 column (900px tier), images not squashed, footer columns stacked
- hamburger visible and FUNCTIONAL: click → nav panel visible + `aria-expanded="true"`;
  click again → closes (B14). Screenshot the open state at both widths.

Write `docs/build/qa-report.md` (format: zippy-qa-gates skill — a G4 section and a G5 section,
screenshot paths, fixed-diffs table with file(s) changed, remaining accepted diffs). Paste back
the two section summaries → the Leader flips S6 to PASS.

#### S7 — functional QA (behavior map B1–B17, block-map §4)

Run against the live site in a browser. For every row: perform the trigger, assert the effect,
screenshot. Evidence lands in `qa-report.md` under a G6 section.

1. Link crawl re-run (the S5-e command) → every internal href still 200.
2. Console: open all 10 pages → zero JS errors (record any).
3. PHP log (error.log is a /dev/stderr symlink in this image family — plain tail blocks):

       docker logs ${PROJECT_ID} 2>&1 >/dev/null | tail -100

   No new PHP Fatal/Warning generated during the run.
4. Behavior rows:
   - B1 shop: click an `.order-btn` → label becomes `Added ×{qty} ✓`, reverts to `Order` after 1800 ms.
   - B2 shop: type 50 in `#customQty` → `#customUpload` unlocks (`.locked` off), `#lockMsg` ↔
     `#unlockMsg` swap; drop below 50 → re-locks.
   - B3 shop: pick a file in `#customImgInput` → `#customPreview` shows it.
   - B4 shop: click `#customSubmit` → `#customOk` shown 4000 ms.
   - B5 upcoming: click `.buy-ticket` → `#ticketModal` opens; title/date from the card, price
     from `.tprice`, qty 1, attendee rows rebuilt, first attendee input focused, body scroll locked.
   - B6 upcoming: `#tmMinus`/`#tmPlus`/`#tmQty` → qty clamped 1–20, attendee rows rebuilt,
     totals update, ± disabled at bounds.
   - B7 upcoming: `#tmCheckout` with a blank attendee name → `.err` inputs + `#tmErr`; complete
     → `#tmPanel` order summary (`PMC-{ts}` ref) + `#tmEmail` becomes
     `mailto:hello@pricemrcopper.com` with prefilled subject/body; panel scrolls into view.
   - B8 upcoming: `#tmCopy` → clipboard copy of `#tmOrderText` + `Copied ✓` for 1800 ms.
   - B9 upcoming: close via ✕ / backdrop / Esc → modal closes, scroll restored, attendee list cleared.
   - B10 films: click `.film-poster` / `.watch-btn` → `#filmModal` opens with the card's
     title/meta/desc; every shipped film has empty `data-video` → `#fmEmpty` "No video added yet".
   - B11 films: close ✕ / backdrop / Esc → modal closes (video paused + reset).
   - B12 film page: submit `#filmEnquiryForm` → `#filmEnquiryOk` shown 4000 ms, fields cleared.
   - B13 enquiries page: submit `#enquiryForm` → `#enquiryOk` shown 4000 ms, fields cleared.
   - B14 header: hamburger (already verified at S6 — re-assert once).
   - B15 native: spot-check all nav + footer + CTA links land on the right pages.
   - B16 film page: "Start Your Film Enquiry" → smooth-scrolls to `#filmEnquiry`.
   - B17 upcoming: teaser `<video>` autoplays (muted, loop, playsinline) with the SVG poster.
5. Watch items the member decides/records at S7 (already Open issues — record, do not code now):
   P4-D5 films 2–3 empty meta pills; P4-D6 `#fmEmpty` copy mentions the dropped authoring
   tools; P5 keyboard user can Tab+Enter into `#customSubmit` while the panel is visually
   locked (add the disabled state or accept); P8-D2 `data-checkout-url` empty → checkout ends
   in the order-summary panel + mailto (expected, not a defect).
6. Forms are client-side only (D7) — "where the submission lands" is N/A by design; never
   disable nonce/validation to make a form pass, never add a backend here (C-flow).

Paste the G6 section back → the Leader flips S7 to PASS. The build is done.

## Open issues

- [ ] The existing `pricemrcopper` container + DB at `:17770` belong to the ORIGINAL repo
  (`FCS-WP/pricemrcopper`, checkout `/home/tobithongha/pricemrcopper`), which already contains a
  complete prior build (its own BUILD-STATE shows S0–S7 all PASS, 2026-09-07/08). Out of scope:
  never stopped, reset, or written by this pipeline — read-only.
- [ ] The original repo (especially its `docs/build/*` from the completed build) is prior-art
  reference for later stages.
- [x] WooCommerce scope — DECIDED AT S1 (block-map D1): stays OFF; the mockup shop is a catalog
  with transient `Added ×N ✓` labels and no cart/checkout/payment → static block
  `ai-zippy/shop-merchandise`. The member may skip S0 runbook Step 4b. Activating it later is a
  scope change (C-flow). ACF stays inactive — nothing in the design requires it.
- [ ] `about-story.jpg` is 8.3 MB of inline base64 (mockup L392) — uploads as-is per clone
  doctrine (D9); member may optionally recompress before/after S5. Extracted at S3/P2 (V7
  evidence): JPEG 4000×6000, **6,214,677 B** decoded from the 8,286,236-char base64 payload,
  signature `/9j/` (progressive JFIF) — served from `assets/img/about-story.jpg` at build time
  only; S5 sideloads it into the Media Library.
- [ ] Forms (`#filmEnquiryForm`, `#enquiryForm`, custom-order box) replicate the mockup's
  client-side behavior only — success message, no storage/email backend (D7). Wiring real
  handling is a future C-flow.
- [ ] 5 placeholder-gradient SVG assets (films 2–5 posters, event teaser poster) ship as real
  media until the member supplies replacements (D6). Films 2–5 extracted at S3/P4 (V7
  evidence): 4× 769 B SVGs URL-decoded from the mockup's `data:image/svg+xml;utf8,` payloads,
  gradient stops verified; the event teaser poster extracted at S3/P8 (V9 evidence): 820 B SVG
  URL-decoded from the mockup L883 poster payload, gradient stops `#B2F1F8`→`#85BEDB`
  verified — all 5 placeholder SVGs are now extracted.
- [ ] P4-D5 watch item — films 2–3 meta arrays carry only the real values (block-map §6.3 drops
  the mockup's two empty `<span><br></span>` chips at L536/L555). If S6 shows the mockup
  rendering small empty meta pills on films 2–3 (a `<span><br></span>` inside the
  padded/bordered pill may paint ~24px wide), the member decides whether to re-add them — a
  default-value change only.
- [ ] P4-D6 — the `#fmEmpty` copy "Hover this film’s card and use ↑ to upload a video file, or 🔗
  to paste a hosted video URL." references the dropped authoring tools but is not on the D5
  drop list → cloned verbatim; member content decision (same treatment as the P3-D5 price-copy
  mismatch).
- [ ] `wp block list` is not a registered wp-cli command in this image (see Environment) — the
  S3 gate must verify registration via `wp eval-file` over `WP_Block_Type_Registry` instead.
- [ ] block-map §2 says "6 of 15 partials carry unique rules" but its own table marks 8
  non-empty (home-hero, home-featured-works, about-story, film-tiers, films-list,
  shop-merchandise, upcoming-list, contact-details) — the "6" is a typo; the table governs
  (recorded at S2a, spec §4.7).
- [ ] film-tiers select option 2 reads "…cinema release ($400,000)" while the Tier 2 price is
  `$290,000` (mockup L467 vs L483) — the mockup is the specification, so the option was cloned
  verbatim (P3-D5, recorded at S3/P3); the member decides any copy change after the build (a
  content edit, not a code change). The enquiries select (L943) repeats the same `$400,000`
  copy — cloned verbatim at S3/P9, same member decision.
- [x] Mockup L242–248 modal chrome (`.modal-head`/`.modal-close`/`.modal-body`/`.modal-foot`
  etc.) is not explicitly cited in block-map §5 rows 3/4 (row 3 ends at 241, row 4 resumes at
  249) — it resolves to `sections/_upcoming-list.scss`, same owner as the ticket-modal
  internals it belongs to (S2a spec D8); ported at S3/P8 into `_upcoming-list.scss`
  (V4/V5 evidence: `.modal-head`/`.modal-close`/`.modal-body` greps present in the built CSS).
- [ ] `.custom-upload.locked` uses `pointer-events:none`, so a keyboard user can still Tab+Enter
  into `#customSubmit` while the panel is visually locked (mockup behavior, cloned verbatim — B2
  only toggles the class; the original repo fixed it at its S7 by disabling the button). The
  member decides at S7 whether to add the disabled state — record only, no code change now (P5).
- [ ] P8-D2 — `data-checkout-url` ships empty on every event card (mockup L881 cloned verbatim);
  the shipped checkout path ends in the order-summary panel + `mailto:hello@pricemrcopper.com`
  (B7). Giving events real per-event checkout URLs (e.g. Stripe payment links) is a future
  content decision (C-flow) — the view.js real-payment branch already handles a non-empty
  `data-checkout-url`, so it needs content only, no code change.
- [ ] S4 note (a) — the editor canvas is Gutenberg's resizable preview window: the iframe body
  sits at ~1160px inside a 1440px browser. Judge computed styles and geometry (padding,
  background, grid columns), not screenshot pixel width — full-bleed sections legitimately span
  the canvas while `.wrap` stays 1180px on both sides (prior-art carry-forward).
- [ ] S4 note (b) — expected console content at S4-live is benign: images served from theme
  assets (`assets/img/…`) until S5 sideloads them into the Media Library, plus possible core
  `__next40pxDefaultSize` deprecation warnings. Cosmetic only; neither is a canvas-parity
  defect (prior-art carry-forward).
- [ ] S4 note (c) — scratch-page convention: any future editor re-check (e.g. after an S5/S6
  CSS fix) reuses the `pmc-s4-scratch` draft-page method from "S4 live-env checks" — create
  draft with all 15 blocks, compare canvas vs preview, delete with `--force`. Never leave
  scratch pages published.

## Post-build approved overrides

- 2026-09-24 — **task 62593c7c (approved spec): header background → red.** `_header.scss` only:
  `.pmc-header` + the ≤900px nav panel `background:#ff0000` (minifier emits `background:red` ×2
  in the built CSS), header text flipped white — `.pmc-header .brand-name` neutralizes the
  `.grad-text` clip (`_components.scss:32`); `.pmc-header .pmc-nav a` sits BEFORE the `:hover`
  rule so the hover/`:focus-visible` pill keeps its dark ink, and the `.pmc-header .pmc-nav
  a.active` arm wins over the mockup `.active` color by specificity. Evidence: `npm run
  build:child` exit 0, no Sass warnings; built CSS `background:red` ×2, zero `rgba(255,255,255,.92/.98)`
  source hits; the new CSS = the live-served P9 baseline + exactly these 4 changes
  (reverse-transform byte-identical, +188 B). Live note: the running `pricemrcopper_epos`
  container bind-mounts the persistent main checkout (`/home/tobithongha/src-test-epos-tool`),
  per the S0 runbook "never a worktree" rule — the live URL serves the red header only after
  this task's PR merges into that checkout; until then visual verification ran on the real
  http://localhost:18770 pages with the new CSS route-intercepted (Playwright): 14/14 —
  header + open panel `rgb(255,0,0)`, brand/links/active white, hover `rgb(20,48,61)` on
  `rgb(251,250,241)` pill, `.prism-bar` 6px sticky + `border-bottom` 1px unchanged, hamburger
  opens/closes (`aria-expanded` true→false), no 375px horizontal scroll; screenshots
  `/tmp/qa-red-header-1440.png`, `/tmp/qa-red-header-375-closed.png`, `/tmp/qa-red-header-375-open.png`.
  Editor parity: `wp eval 'print_r(get_editor_stylesheets());'` live-returns `child-style.css`
  (plain `.pmc-header` classes, no canvas reset needed). `docker logs` clean during
  verification — the 11 `AiZippy\Product\is_product` fatals all pre-date it (06:44 UTC
  bootstrap window, parent-theme namespace bug, untouched by this CSS-only change).
