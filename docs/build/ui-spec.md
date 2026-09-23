# UI Spec — PricemrCopper Company

S1 analysis of `pricemrcopper_final_mockup.html` (repo root, 1736 lines, ~13.2 MB — size comes
from inline base64 images). Every value below is quoted **verbatim** from the mockup's own CSS/HTML
(mockup line numbers in parentheses) — the S2/S3 stages copy them, never re-derive them.

- **Flow**: S-flow (`.html` mockup → skill `mockup-to-wordpress-blocks`) — literal 100 % clone, no
  redesign. Anything not in the mockup does not go into the site.
- **Stage range of this document**: S1 only. Companion document: `docs/build/block-map.md`.
- **Mockup anatomy**: `<head>` fonts link (L8), single `<style>` L9–303, body L305–1069,
  single `<script>` L1070–1734. SPA: ten `<div class="page" id="page-{id}">` inside one `<main>`
  (L335–970), routed via `data-page` / `data-goto` / `data-scroll`.

## 1. Source & scope

| Item | Value |
|---|---|
| Design source | `pricemrcopper_final_mockup.html` (repo root) |
| Site title | PricemrCopper Company — Global Trade × Creative Media |
| Pages | 10 (SPA pages in one file) |
| Brand voice / labels | Keep verbatim; `contenteditable` attributes are mockup authoring machinery and are dropped (D5) |
| Commerce | None in scope — WooCommerce stays OFF, shop is a static block (D1, see block-map) |

## 2. Pages inventory

### 2.1 Global chrome (outside the pages)

| Element | Mockup lines | Notes |
|---|---|---|
| `.prism-bar` | 307 | 6px gradient ribbon, `position:sticky;top:0;z-index:60` — keep, sits above the header |
| `.edit-hint` bar + `#exportBtn` | 308–310 | mockup authoring toolbar — **DROP** (D5) |
| `<header>` | 313–332 | sticky `top:6px`, `z-index:50`, `background:rgba(255,255,255,.92)`, `backdrop-filter:blur(10px)`, `border-bottom:1px solid var(--line)`; logo img + `.brand-name.grad-text` "PricemrCopper" + `#mainNav` with 10 `data-page` links → becomes `parts/header.html` (S2) |
| `<footer>` | 973–997 | 3-column grid + `.footer-bottom` bar, 9 `data-goto` links → `parts/footer.html` (S2) |
| Film lightbox `#filmModal` | 1000–1018 | `.modal-overlay > .vmodal` — owned/rendered by block `films-list` (D8) |
| Ticket modal `#ticketModal` | 1021–1065 | `.modal-overlay > .modal` — owned/rendered by block `upcoming-list` (D8) |
| `#imgFileInput` | 1068 | hidden authoring file input — **DROP** (D5) |

### 2.2 Pages and sections (order as in the mockup)

| Page (id / nav label) | Lines | Sections in order |
|---|---|---|
| `home` / Home | 336–377 | hero (337–350) · Who We Are + Core Pillars cards (352–364) · Featured Works (366–376) |
| `about` / About Us | 380–430 | Our Story split + story blocks (381–418) · Core Pillars (419–429) |
| `film` / Make A Feature Film? | 433–492 | Your Vision, Cinematic Execution split + steps (434–449) · Budget tiers + enquiry form `#filmEnquiry` (452–491) |
| `films` / Our Films | 496–614 | Watch Our Films film-list ×5 + lightbox (497–601) · CTA card (603–613) |
| `shop` / Shop | 617–827 | one `<section class="wrap">`: heading + shop-grid ×14 products (622–790) + custom-order box `#customBox` (793–825) |
| `sponsors` / Be Our Sponsors | 830–842 | intro + 3 cards + CTA button (831–841) |
| `events` / Collaborate For Events | 845–871 | Let's Build Something Live split + steps (846–860) · CTA card, `style="padding-top:0"` (862–870) |
| `upcoming` / Events | 874–924 | Upcoming Events events-grid ×1 event card + ticket modal (875–911) · CTA card (913–923) |
| `enquiries` / Enquiries | 927–952 | Tell Us What You Need form `#enquiryForm` (928–951) |
| `contact` / Contact Us | 955–969 | contact-grid: 4 contact items + Google Maps iframe (956–968) |

Nav order (L320–329): Home · About Us · Make A Feature Film? · Our Films · Shop · Be Our
Sponsors · Events (`upcoming`) · Collaborate For Events (`events`) · Enquiries · Contact Us.

## 3. Palette

Verbatim `:root` (L11–32) — same custom-property names land in `src/scss/_tokens.scss`:

```css
:root{
  --ink:#14303d;
  --ink-soft:#5b7280;
  --bg:#ffffff;
  --bg-soft:#fbfaf1;
  --line:#e4ecef;
  /* --- brand palette --- */
  --yellow:#FCDB7E;
  --cream:#F5F3CD;
  --aqua:#B2F1F8;
  --sky:#85BEDB;
  /* deeper tints, used where text/icons need contrast */
  --yellow-deep:#d9a12b;
  --aqua-deep:#3fa9bd;
  --sky-deep:#3f8bb0;
  --prism:linear-gradient(90deg,var(--yellow),var(--cream),var(--aqua),var(--sky));
  --prism-deep:linear-gradient(90deg,var(--yellow-deep),var(--aqua-deep),var(--sky-deep));
  --radius:16px;
  --shadow:0 10px 30px rgba(27,30,40,.08);
  --display:'Sora',sans-serif;
  --body:'Inter',sans-serif;
}
```

| Token | Value | Used for |
|---|---|---|
| `--ink` | `#14303d` | body text, headings, dark surfaces (footer bg, film-runtime/vmodal) |
| `--ink-soft` | `#5b7280` | secondary text, nav links, section subs |
| `--bg` | `#ffffff` | page background, cards |
| `--bg-soft` | `#fbfaf1` | `.soft` section bands, icon/date chip backgrounds |
| `--line` | `#e4ecef` | 1px/1.5px borders everywhere |
| `--yellow` / `--cream` / `--aqua` / `--sky` | `#FCDB7E` / `#F5F3CD` / `#B2F1F8` / `#85BEDB` | the prism gradient stops |
| `--yellow-deep` / `--aqua-deep` / `--sky-deep` | `#d9a12b` / `#3fa9bd` / `#3f8bb0` | `--prism-deep` (grad-text), focus rings, contact links |
| `--radius` | `16px` | cards; section-specific radii override (10/12/14/18/20/22/24px, 999px pills) |
| `--shadow` | `0 10px 30px rgba(27,30,40,.08)` | cards/images default shadow |

**Hero background** (L79–84) — four layered radial gradients over `var(--bg)`, copy verbatim:

```css
background:
  radial-gradient(760px 420px at 10% 0%,rgba(252,219,126,.55),transparent 62%),
  radial-gradient(720px 440px at 88% 8%,rgba(178,241,248,.60),transparent 62%),
  radial-gradient(640px 420px at 62% 100%,rgba(133,190,219,.38),transparent 62%),
  radial-gradient(520px 320px at 42% 40%,rgba(245,243,205,.55),transparent 70%),
  var(--bg)
```

Other hardcoded colors that stay verbatim (they are the design, not token drift): footer text
`#c6dae2`, footer bottom `#93aeb9`, footer hairline `rgba(255,255,255,.14)`, film overlay
gradients `rgba(20,22,30,.85)`/`rgba(20,48,61,.5)`, modal overlay `rgba(20,48,61,.55)`,
vmodal `#0f2733` family, lock-msg `#fdf6e0`/`#8a6417`/`--yellow`, unlock-msg `#eafbfd`/`#14586b`/`--aqua`,
err-msg `#fdf4f4`/`#e8b4b4`/`#8a3d3d`, attendee err `#d98b8b`.

## 4. Typography & fonts

Google Fonts (L8, verbatim URL — S2 enqueues the same list on frontend and in the editor):

```
https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap
```

| Rule | Verbatim value (line) |
|---|---|
| Body | `font-family:var(--body);color:var(--ink);line-height:1.65;font-size:16px` (L35) |
| Headings h1–h4 | `font-family:var(--display);line-height:1.2;letter-spacing:-.01em` (L36) |
| Hero h1 | `clamp(2.2rem,4.6vw,3.6rem)`, weight 800, `margin:14px 0 18px` (L86) |
| Hero `.lead` | `1.12rem`, `color:var(--ink-soft)`, `max-width:52ch` (L87) |
| `.section-title` | `clamp(1.6rem,3vw,2.2rem)`, weight 800, `margin-bottom:14px` (L105) |
| `.section-sub` | `color:var(--ink-soft)`, `max-width:64ch`, `margin-bottom:36px` (L106) |
| `.eyebrow` | `.78rem`, weight 700, `letter-spacing:.14em`, uppercase, `color:var(--ink-soft)`, `margin-bottom:14px`; `::before` 26px×6px `border-radius:3px` prism dash (L48–49) |
| Brand name | `1.15rem`, weight 800, display font (L63) |
| Nav links | `.86rem`, weight 600, `padding:8px 12px`, `border-radius:10px` (L65) |
| Card h3 / p | `1.15rem` / `.95rem` ink-soft (L99–100) |
| Product h3 / price / size | `1rem` / `1.1rem` weight 800 display / `.78rem` pill (L121–124) |
| Tier price / runtime | `2.1rem` weight 800 line-height 1.1 / `.86rem` pill (L190–191) |
| Buttons | display font, weight 700, `.95rem`, `padding:13px 26px`, `border-radius:999px` (L90) |

## 5. Layout & spacing

| Rule | Verbatim value (line) |
|---|---|
| Container `.wrap` | `max-width:1180px;margin:0 auto;padding:64px 24px` (L74) |
| `.wrap.tight` | `padding-top:48px;padding-bottom:48px` (L75) |
| Section rhythm | `section+section{margin-top:8px}` (L76) |
| Soft band | `.soft{background:var(--bg-soft)}` (L107) |
| Header inner | `max-width:1180px`, `padding:14px 24px`, `gap:22px`, flex wrap (L60) |
| Brand logo | `width:44px;height:44px;border-radius:12px` (L62) |
| Footer | `margin-top:64px`, `background:var(--ink)`, `color:#c6dae2` (L178); inner `padding:52px 24px 32px` (L179); bottom bar `padding:18px`, `.82rem` (L183) |
| Global smooth scroll | `html{scroll-behavior:smooth}` (L34) — stays in `_base.scss` (B16) |
| Reset | `*{box-sizing:border-box;margin:0;padding:0}`, `img{display:block;max-width:100%}`, `a{color:inherit;text-decoration:none}` (L33–38) |

Section-level inline styles in the markup are content and are preserved verbatim (e.g.
`.split` with `style="margin-bottom:36px"` L384, events CTA `style="padding-top:0"` L862,
tier h3 inline size L358, footer inline font sizes L977–978). Only the four documented
editing-residue lines + authoring focus-ring inline styles are normalized (D5, block-map §6).

## 6. Grid patterns (verbatim `grid-template-columns` / gaps)

| Grid | Verbatim value (line) |
|---|---|
| `.hero-grid` | `1.05fr .95fr`, `gap:48px`, `align-items:center` (L85) |
| `.grid-3` | `repeat(3,1fr)`, `gap:24px` (L96) |
| `.grid-2` | `repeat(2,1fr)`, `gap:24px` (L97) |
| `.split` | `1fr 1fr`, `gap:40px`, `align-items:center` (L156) |
| `.sub-cards` | `1fr 1fr`, `gap:16px`, `margin-top:18px` (L150) |
| `.tier-grid` | `1fr 1fr`, `gap:24px`, `margin-bottom:14px` (L186) |
| `.shop-grid` | `repeat(auto-fill,minmax(240px,1fr))`, `gap:22px` (L117) |
| `.film-list` | `repeat(auto-fill,minmax(300px,1fr))`, `gap:26px` (L196) |
| `.events-grid` | single column, `gap:26px` (L277) |
| `.event-card` | `1.15fr 1fr` (L278) |
| `.footer-inner` | `1.4fr 1fr 1fr`, `gap:36px` (L179) |
| `.contact-grid` | `1fr 1.2fr`, `gap:40px` (L168) |

The auto-fill shop/film grids are already fluid and re-flow without extra breakpoints.

## 7. Shared components (destination partial in `src/scss/`)

| Component | Verbatim source | Destination |
|---|---|---|
| `.prism-bar` | L46 | `_components.scss` |
| `.grad-text` | L47 | `_components.scss` |
| `.eyebrow` + `::before` | L48–49 | `_components.scss` |
| `.btn` / `.btn-primary` / `.btn-ghost` / `.btn-row` | L89–93 | `_components.scss` |
| `.grid-3` / `.grid-2` | L96–97 | `_components.scss` |
| `.card` (+ h3/p rules) | L98–100 | `_components.scss` |
| `.card .icon` / `.icon.i1–i3` | L101–104 | `_components.scss` |
| `.split` (+ img) | L156–157 | `_components.scss` |
| `.step-list` / `.step` / `.step .n` | L158–160 | `_components.scss` |
| `.field` label/input/select/textarea + focus | L133–135 | `_components.scss` |
| `form.enquiry` / `.form-ok` | L163–164 | `_components.scss` |
| `.modal-overlay` + `@keyframes pop` | L238–241 | `_components.scss` |
| `nav` / `nav a` (+ hover/active) | L59–68 | `_header.scss` |
| `footer` / `.footer-inner` / `.footer-bottom` | L177–183 | `_footer.scss` |
| Reset + base type + `.wrap`/`.tight` + `section+section` + `.section-title`/`.section-sub` + `.soft` | L33–38, 74–76, 105–107 | `_base.scss` |
| `@media (prefers-reduced-motion:reduce)` | L300–302 | `_base.scss` |

## 8. Section inventory — banner vs content classification

Foundation §2 classification (a section is a *banner* only when a photo spans the full section
width with text overlaid on it):

**No photo-overlay banner sections exist in this mockup.** The hero is a gradient-background
2-column grid (`hero-grid`); every image sits inside a column or card (feature-card caps are
in-card overlays, not section-wide). **All sections on all 10 pages classify as content.**

Consequences: the mobile cover pattern is **not required**; grid collapse (§9) + type clamp
scaling carry the responsive story.

## 9. Responsive

Source breakpoints — the mockup has exactly two media blocks (L295–302), copied verbatim:

```css
@media (max-width:900px){
  .hero-grid,.split,.grid-3,.grid-2,.contact-grid,.footer-inner,.sub-cards,.event-card,.tier-grid,.film-hero{grid-template-columns:1fr}
  .event-body{padding:24px}
  nav{margin-left:0}
}
@media (prefers-reduced-motion:reduce){
  *{animation:none!important;transition:none!important}
}
```

Notes:
- `.film-hero` appears in the 900px selector list but has **no base rule in the source** — copy
  verbatim anyway (with this note) per clone doctrine.
- The 900px block splits by owner partial when extracted (`.split`→`_components`,
  `.hero-grid`→`_home-hero`, `.footer-inner`→`_footer`, `.contact-grid`→`_contact-details`, …).

Mandatory S2/S6 additions (not in the mockup, per the foundation reference):
- **Hamburger nav ≤900px** — the mockup has no mobile nav toggle (`nav` just wraps). S2 adds the
  hamburger button + `child.js` toggle + `aria-expanded` (B14).
- **375px + 768px QA tiers are mandatory at S6** — the source has no layout below 900px beyond the
  grid collapse; type clamps (`clamp()` on h1/section-title) already scale.

## 10. Media inventory — 28 assets extracted from base64 data URIs

23 binary images + 5 SVG gradient placeholders (generated by the mockup's `placeholder()` — they
are the rendered design until real media arrives, D6). Filenames proposed; alts are verbatim from
the mockup.

| # | Asset (filename) | Line | Type | Used by | Alt (verbatim) |
|---|---|---|---|---|---|
| 1 | `logo.png` | 316 | png | header brand | PricemrCopper logo |
| 2 | `home-hero.avif` | 348 | avif | home-hero | Main banner |
| 3 | `home-feature-mad-vibe-city.png` | 371 | png | home-featured-works | Mad Vibe City film |
| 4 | `home-feature-music-beats.avif` | 372 | avif | home-featured-works | Music beats |
| 5 | `home-feature-global-logistics.avif` | 373 | avif | home-featured-works | Global logistics |
| 6 | `about-story.jpg` | 392 | jpeg — **8.3 MB inline base64, flagged (D9)** | about-story | Our story |
| 7 | `film-production.avif` | 438 | avif | film-intro | Film production |
| 8 | `films-poster-mad-vibe-city.png` | 512 | png | films-list | Mad Vibe City poster |
| 9 | `films-poster-film-2.svg` | 531 | svg placeholder | films-list | Mad Vibe City - Opening Sequence poster |
| 10 | `films-poster-film-3.svg` | 550 | svg placeholder | films-list | Mad Vibe City - Singapore Nights poster |
| 11 | `films-poster-film-4.svg` | 569 | svg placeholder | films-list | Behind The Lens poster |
| 12 | `films-poster-film-5.svg` | 588 | svg placeholder | films-list | Original Score Sessions poster |
| 13 | `product-01.jpg` | 623 | jpeg | shop-merchandise | Black Long Sleeves Hoodie |
| 14 | `product-02.jpg` | 635 | jpeg | shop-merchandise | White Round Neck Print T-Shirt |
| 15 | `product-03.jpg` | 647 | jpeg | shop-merchandise | Mad Vibe City Drink Tumbler Can 650ml |
| 16 | `product-04.jpg` | 659 | jpeg | shop-merchandise | Unisex White Polo T-Shirt |
| 17 | `product-05.jpg` | 671 | jpeg | shop-merchandise | Sports Stainless Steel Water Bottle 500ml |
| 18 | `product-06.jpg` | 683 | jpeg | shop-merchandise | Stainless Steel Mug with Handle 12oz |
| 19 | `product-07.jpg` | 695 | jpeg | shop-merchandise | Drink Glass Mugs with Straw 500ml |
| 20 | `product-08.jpg` | 707 | jpeg | shop-merchandise | Stainless Steel Tumbler |
| 21 | `product-09.jpg` | 719 | jpeg | shop-merchandise | Notebook Sets & Calendars |
| 22 | `product-10.jpg` | 731 | jpeg | shop-merchandise | Cap |
| 23 | `product-11.jpg` | 743 | jpeg | shop-merchandise | Mug |
| 24 | `product-12.jpg` | 755 | jpeg | shop-merchandise | Toy Bears |
| 25 | `product-13.jpg` | 767 | jpeg | shop-merchandise | Notebook Sets (Boxed) |
| 26 | `product-14.jpg` | 779 | jpeg | shop-merchandise | Mad Vibe City Tank Tops / T-Shirts |
| 27 | `event-production.avif` | 858 | avif | events-process | Event production |
| 28 | `event-teaser-poster.svg` | 883 | svg placeholder (video `poster` attribute) | upcoming-list | — (poster attr, no alt) |

Product data (name — price — size, verbatim from the cards): Black Long Sleeves Hoodie $45
S–XXL · White Round Neck Print T-Shirt $35 S–XXL · Mad Vibe City Drink Tumbler Can 650ml $55
650ml · Unisex White Polo T-Shirt $35 S–XXL · Sports Stainless Steel Water Bottle 500ml $45
500ml · Stainless Steel Mug with Handle 12oz $35 12oz · Drink Glass Mugs with Straw 500ml $30
500ml · Stainless Steel Tumbler $35 Std · Notebook Sets & Calendars $37 A5 · Cap $28 Free size ·
Mug $35 11oz · Toy Bears $35 30cm · Notebook Sets (Boxed) $55 Boxed set · Mad Vibe City Tank
Tops / T-Shirts $65 S–XXL.

The only other runtime media: the event teaser `<video>` (L883) — no `src`, `autoplay loop
muted playsinline` + the SVG poster (B17). The `#customPreview` img (L816) is src-less until the
user uploads (B3). The contact map is a Google Maps `<iframe>` (L966), not an asset.

## 11. Accessibility notes (preserve in `render.php`)

Present in the mockup and kept verbatim:

- Both modals: `role="dialog" aria-modal="true" aria-labelledby="fmTitle"/"tmTitle"`
  (L1000, L1021); close buttons `aria-label="Close"` (L1003, L1026).
- Ticket stepper: `aria-label="Fewer tickets"` / `"More tickets"` / `"Number of tickets"`
  (L1031–1033).
- Every shop qty input: `aria-label="Quantity for {product name}"` (e.g. L631).
- All form fields use `<label for="…">` + matching ids.
- Map iframe: `title="Map"` + `loading="lazy"` (L966).
- `html{scroll-behavior:smooth}` (L34) and the `prefers-reduced-motion` block (L300–302) keep
  motion accessible.
- Heading hierarchy per page: one `h1` (home hero only) → `h2` section titles → `h3` card titles
  → `h4` sub-cards/footer columns.

Authoring machinery to strip while preserving semantics (D5): `contenteditable` attributes,
`title` attribute on the teaser video ("Click to upload a looping video…" L883 — replace with a
meaningful title or drop), `.vhint` span (L885), focus-ring inline styles on the residue `<h3>`s.
