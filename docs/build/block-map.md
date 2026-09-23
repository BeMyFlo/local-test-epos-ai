# Block Map — PricemrCopper Company

S1 analysis of `pricemrcopper_final_mockup.html` → the block/page/behavior contract that S2
(foundation), S3 (blocks), S5 (seeding) and S7 (functional QA) execute. Values and line numbers
are verbatim from the mockup. Companion document: `docs/build/ui-spec.md` (tokens, typography,
grids, media, a11y).

Conventions: block namespace `ai-zippy/` (D3), category `ai-zippy` (parent-registered, per
`.claude/rules/blocks.md` §7). `render.php` keeps the mockup's class names and DOM shape
verbatim (D4) — block wrapper classes are never styled; styles target the mockup's inner
semantic classes. Text attributes render through `esc_html()`; images resolve through the Media
Library map helper. Text defaults below name the attribute shape; the mockup lines cited are the
source of the default copy (S3 pastes from the mockup itself, never rewrites).

## 1. Page-plan (feeds S2 `theme.json` customTemplates + S5 `scripts/seed-pages.php`)

| Page title | Slug | Template | Blocks in order |
|---|---|---|---|
| Home (front page) | `home` → `/` | `page-home` | home-hero · home-who-we-are · home-featured-works |
| About Us | `about` → `/about/` | `page-about` | about-story · about-pillars |
| Make A Feature Film? | `film` → `/film/` | `page-film` | film-intro · film-tiers |
| Our Films | `films` → `/films/` | `page-films` | films-list · cta-banner |
| Shop | `shop` → `/shop/` | `page-shop` | shop-merchandise |
| Be Our Sponsors | `sponsors` → `/sponsors/` | `page-sponsors` | sponsors-intro |
| Events | `upcoming` → `/upcoming/` | `page-upcoming` | upcoming-list · cta-banner |
| Collaborate For Events | `events` → `/events/` | `page-events` | events-process · cta-banner |
| Enquiries | `enquiries` → `/enquiries/` | `page-enquiries` | enquiries-form |
| Contact Us | `contact` → `/contact/` | `page-contact` | contact-details |

- Slugs = mockup page ids **verbatim** (D2): `/upcoming/` carries nav label "Events", `/events/`
  is "Collaborate For Events" — least-surprise mapping; renaming would be a redesign decision.
- Header/footer chrome = template parts (`parts/header.html`, `parts/footer.html`), not blocks.
- The static front page is `home`; `show_on_front=page`, `page_on_front` = home ID (S5).

## 2. Block inventory — 15 blocks

| Block | Source (lines) | SCSS partial `src/scss/sections/` | view.js | Client-editable attributes |
|---|---|---|---|---|
| `ai-zippy/home-hero` | 337–350 | `_home-hero.scss` | — | eyebrow, heading (two `.grad-text` spans), lead, 2 buttons {label, href}, image |
| `ai-zippy/home-who-we-are` | 352–364 | `_home-who-we-are.scss` (empty — component-driven) | — | eyebrow, title, 3 paragraphs, pillars heading, cards[3] {icon, heading, text} |
| `ai-zippy/home-featured-works` | 366–376 | `_home-featured-works.scss` | — | eyebrow, title, works[3] {image, cap heading, cap text} |
| `ai-zippy/about-story` | 381–418 | `_about-story.scss` | — | eyebrow, title, story image, storyBlocks[3] {heading, paragraphs, subCards[2]?} (block 2 has sub-cards) |
| `ai-zippy/about-pillars` | 419–429 | `_about-pillars.scss` (empty) | — | eyebrow, title, cards[3] {icon, heading, text} |
| `ai-zippy/film-intro` | 434–449 | `_film-intro.scss` (empty) | — | eyebrow, title, intro, image, steps[3] {n, heading, text}, button {label, href `#filmEnquiry`} |
| `ai-zippy/film-tiers` | 452–491 | `_film-tiers.scss` | B12 | eyebrow, title, sub, tiers[2] {tag, price, runtime, text}, note, form heading, form sub, select options |
| `ai-zippy/films-list` | 497–601 + filmModal 1000–1018 | `_films-list.scss` | B10, B11 | eyebrow, title, sub, films[5] {tag, runtime, poster, title, meta[], desc, videoUrl} + owns the lightbox markup |
| `ai-zippy/cta-banner` (shared — 3 instances: films 603–613, events 862–870, upcoming 913–923) | — | `_cta-banner.scss` (empty) | — | heading, text, button {label, href}, `soft` variant flag (films/upcoming = `.soft` band; events = plain `wrap tight` + `padding-top:0`) |
| `ai-zippy/shop-merchandise` | 618–826 | `_shop-merchandise.scss` | B1, B2, B3, B4 | eyebrow, title, sub, products[14] {name, price, size, image}, custom-order copy, product select options, lock/unlock messages, upload fields copy |
| `ai-zippy/sponsors-intro` | 831–841 | `_sponsors-intro.scss` (empty) | — | eyebrow, title, sub, cards[3] {icon, heading, text}, button {label, href} |
| `ai-zippy/events-process` | 846–860 | `_events-process.scss` (empty) | — | eyebrow, title, intro, steps[3] {n, heading, text}, image |
| `ai-zippy/upcoming-list` | 875–911 + ticketModal 1021–1065 | `_upcoming-list.scss` | B5, B6, B7, B8, B9 | eyebrow, title, sub, events[] {badge, day, month, title, desc, meta[], price, videoPoster} + owns the ticket-modal markup |
| `ai-zippy/enquiries-form` | 928–951 | `_enquiries-form.scss` (empty) | B13 | eyebrow, title, sub, subject select options (incl. `optgroup` "Feature Film Production") |
| `ai-zippy/contact-details` | 956–968 | `_contact-details.scss` | — | eyebrow, title, items[4] {icon, heading, lines}, map iframe URL |

Rules:
- 6 of 15 partials carry unique rules; the 9 empty ones still exist as files — uniform S3
  parallel-work contract (each block = its own partial; `@use` lines land via the leader).
- The two modals render inside their owning blocks (D8): `films-list` renders `#filmModal`,
  `upcoming-list` renders `#ticketModal` — `position:fixed` overlays work from inside the block
  wrapper. Modals stay hidden without JS (CSS `display:none`).
- The shop page is ONE block (`shop-merchandise`, D10) because products + custom-order box share
  a single `<section class="wrap">`; splitting would break container/padding fidelity.
- `cta-banner` defaults: films instance heading "Want a film of your own?", button
  `Make A Feature Film` → `/film/`; events instance "Looking for what's coming up?", button
  `View Upcoming Events` → `/upcoming/`, plain variant; upcoming instance "Want to run an event
  with us?", button `Collaborate For Events` → `/events/`.
- WooCommerce stays OFF — shop is a static catalog block (D1). ACF stays inactive.

## 3. Link-conversion map (SPA → real permalinks; never `href="#"`)

**Nav** (L320–329, 10 links) — `data-page` → `href`:

| Mockup | Nav label | WP href |
|---|---|---|
| `home` | Home | `/` |
| `about` | About Us | `/about/` |
| `film` | Make A Feature Film? | `/film/` |
| `films` | Our Films | `/films/` |
| `shop` | Shop | `/shop/` |
| `sponsors` | Be Our Sponsors | `/sponsors/` |
| `upcoming` | Events | `/upcoming/` |
| `events` | Collaborate For Events | `/events/` |
| `enquiries` | Enquiries | `/enquiries/` |
| `contact` | Contact Us | `/contact/` |

**`data-goto`** (15 markup instances: 344, 345, 610, 840, 868, 920, 982–987, 991–993) → real links:

| Line(s) | Element | Target |
|---|---|---|
| 344 | hero primary button "Explore Our Shop" | `/shop/` |
| 345 | hero ghost button "Make A Film With Us" | `/film/` |
| 610 | films CTA "Make A Feature Film" | `/film/` |
| 840 | sponsors CTA "Become A Sponsor" | `/enquiries/` |
| 868 | events CTA "View Upcoming Events" | `/upcoming/` |
| 920 | upcoming CTA "Collaborate For Events" | `/events/` |
| 982–987 | footer "Explore" column (About Us, Shop, Our Films, Make A Feature Film?, Events, Collaborate For Events) | the six slugs above |
| 991–993 | footer "Get In Touch" column (Enquiries, Sponsorship, Contact Us) | `/enquiries/`, `/sponsors/`, `/contact/` |

**`data-scroll`** (L446) — "Start Your Film Enquiry" button → `href="/film/#filmEnquiry"`; the
`film-tiers` section keeps `id="filmEnquiry"` (L452). Smooth scroll comes from
`html{scroll-behavior:smooth}` in `_base.scss` (kept, mockup L34).

## 4. Behavior map (S7 QA checklist source)

The mockup script registers exactly **32 `addEventListener` listeners, 0 `onclick`** (verified:
`grep -c addEventListener` = 32). Accounting: **19 ported to block `view.js` + 3 converted to
native links + 10 dropped as authoring machinery = 32.** A listener missing from this map is a
spec defect.

| # | Trigger (mockup listener line) | Effect | Owner |
|---|---|---|---|
| B1 | `.order-btn` click (1144) | button label → `Added ×{qty} ✓`, reverts to `Order` after 1800 ms | shop-merchandise `view.js` |
| B2 | `#customQty` input (1231) | qty ≥ 50 → `#customUpload` unlocked (`.locked` off), `#lockMsg` ↔ `#unlockMsg` swap | shop-merchandise `view.js` |
| B3 | `#customImgInput` change (1234) | `#customPreview` shows the picked image (FileReader) | shop-merchandise `view.js` |
| B4 | `#customSubmit` click (1245) | `#customOk` shown (`display:flex`) 4000 ms | shop-merchandise `view.js` |
| B5 | `.buy-ticket` click (1380) | ticketModal opens; title/date from the card, price from `.tprice`, qty reset to 1, attendee rows rebuilt, first attendee input focused after 80 ms, body scroll locked (`overflow:hidden`) | upcoming-list `view.js` |
| B6 | `#tmMinus` / `#tmPlus` / `#tmQty` (1389, 1390, 1391) | qty clamped 1–20, attendee rows rebuilt, totals update (line label, subtotal, fee, total), ± disabled at bounds | upcoming-list `view.js` |
| B7 | `#tmCheckout` click (1407) | blank attendee names → `.err` inputs + `#tmErr` message; complete → `#tmPanel` order summary (ref `PMC-{ts}`, attendees) + `#tmEmail` becomes `mailto:hello@pricemrcopper.com` with prefilled subject/body; panel `scrollIntoView` | upcoming-list `view.js` |
| B8 | `#tmCopy` click (1446) | clipboard copy of `#tmOrderText` + `Copied ✓` for 1800 ms | upcoming-list `view.js` |
| B9 | `#tmClose` / backdrop / Esc (1384, 1385, 1386) | ticketModal closes, scroll restored, attendee list cleared, panel hidden | upcoming-list `view.js` |
| B10 | `.film-poster` / `.watch-btn` click (1562) | filmModal opens with the card's title/meta/desc; empty `data-video` → `#fmEmpty` "No video added yet" state, else `#fmVideo` plays | films-list `view.js` |
| B11 | `#fmClose` / backdrop / Esc (1555, 1556, 1557) | filmModal closes, video paused + src removed + `load()` reset | films-list `view.js` |
| B12 | `#filmEnquiryForm` submit (1721) | `preventDefault`; `#filmEnquiryOk` shown 4000 ms; fields cleared | film-tiers `view.js` |
| B13 | `#enquiryForm` submit (1728) | `preventDefault`; `#enquiryOk` shown 4000 ms; fields cleared | enquiries-form `view.js` |
| B14 | Hamburger toggle ≤900px | nav panel + `aria-expanded` — **not in the mockup; mandatory S2 addition** | `src/js/child.js` |
| B15 | Nav / footer / CTA links | real permalinks (replaces the SPA router at 1203 + `data-goto` router at 1209) | native |
| B16 | "Start Your Film Enquiry" (1455 listener) | smooth-scroll to `/film/#filmEnquiry` (replaces `data-scroll` handler) | native anchor + `html{scroll-behavior:smooth}` |
| B17 | Event teaser `<video>` (883) | `autoplay loop muted playsinline` + SVG poster, no `src` — mockup-init `initVideos()` re-applied the same attributes, so baking them into the markup is the verbatim behavior | native attributes |

Ticket-modal constants kept verbatim: `BOOKING_FEE = 0`, `CURRENCY = '$'`, per-card
`data-checkout-url` (empty → no payment provider; checkout ends in the order-summary panel +
mailto — the "real payment" branch at script L1416 never fires with the shipped markup).
Forms replicate the mockup's client-side behavior only — no storage, no email backend (D7).

### 4.1 Dropped listeners — authoring machinery (10, with justifications)

| Line | Listener | Why dropped |
|---|---|---|
| 1170 | editable-image click → upload timer | mockup authoring: click an image to upload a replacement |
| 1179 | editable-image dblclick → URL prompt | mockup authoring: paste an image URL |
| 1186 | `#imgFileInput` change | feeds the two above |
| 1253 | `#addFilmBtn` click → clone film card | authoring toolbar ("+ Add another film") |
| 1487 | editable-video click → upload timer | mockup authoring on the event teaser |
| 1571 | `.film-tools` click (poster/video/URL) | hover toolbar for editing film cards |
| 1606 | editable-video dblclick → URL prompt | mockup authoring |
| 1619 | video `#videoInput` change | feeds the two above |
| 1650 | `#addEventBtn` click → clone event card | authoring toolbar ("+ Add another event") |
| 1693 | `#exportBtn` click → download edited HTML | authoring export |

### 4.2 Dropped without a listener (same machinery, CSS/markup side)

`contenteditable` attributes (site-wide), `[contenteditable]`/`.editable-img` CSS (L39–43),
`.edit-hint`/`.export-btn` CSS (L51–56), `placeholder()` generator + `initImages` /
`initVideos` / `initFilmPosters`, the `PRODUCTS` array + card regeneration (the static markup at
622–790 already contains all 14 cards), `markFilmHasVideo` JS (the static "⚠ No video yet"
chips stay baked into every film card — all five `data-video=""`), `.vhint` hint span (L885) +
its CSS (L281), `.film-tools` buttons + CSS (L208–211), `#addFilmBtn`/`#addEventBtn` +
`.add-event` CSS (L291–292), `#imgFileInput` (L1068), `.page`/`.page.active`/`@keyframes fade`
SPA CSS (L71–73).

No-JS default states are safe (S3 renders them server-side): both modals hidden by CSS
(`display:none`), `.custom-upload` ships `.locked` with `#lockMsg` visible (`display:flex`),
`.form-ok` / `#customOk` / `#tmPanel` / `#tmErr` hidden, `#customPreview` `display:none`.

## 5. SCSS split map (mockup `<style>` L9–303 → `src/scss/`)

| Mockup lines | Destination |
|---|---|
| 10–32 `:root` tokens | `_tokens.scss` (verbatim, same custom-property names) |
| 33–38 reset + base type, 74–76 `.wrap`/`.tight`/`section+section`, 105–107 `.section-title`/`.section-sub`/`.soft`, 300–302 `prefers-reduced-motion` | `_base.scss` |
| 45–49 `.prism-bar`/`.grad-text`/`.eyebrow`, 89–93 `.btn*`/`.btn-row`, 96–104 `.grid-3`/`.grid-2`/`.card`/`.icon`, 133–135 `.field` controls, 156–160 `.split`/`.step-list`/`.step`, 162–164 `form.enquiry`/`.form-ok`, 238–241 `.modal-overlay` + `@keyframes pop` | `_components.scss` |
| 59–68 header/nav | `_header.scss` |
| 177–183 footer | `_footer.scss` |
| 78–88 hero · 109–114 feature-card · 143–153 story/sub-cards · 185–193 tiers/tier-note · 195–233 film cards + vmodal (minus dropped `.film-tools` 208–211) · 116–141 shop/product/custom box · 166–175 contact · 236–237 ticket-line, 249–274 ticket modal internals · 276–292 events (minus `.vhint` 281, `.add-event` 291–292) | matching `sections/_*.scss` per §2 |
| 39–43, 51–56, 71–73 (contenteditable, edit-hint/export, SPA `.page` CSS) | dropped |
| 295–299 `@media (max-width:900px)` | each selector travels with its owner partial: `.hero-grid`→`_home-hero`, `.split`→`_components`, `.grid-3`/`.grid-2`→`_components`, `.contact-grid`→`_contact-details`, `.footer-inner`→`_footer`, `.sub-cards`→`_about-story`, `.event-card` + `.event-body`→`_upcoming-list`, `.tier-grid`→`_film-tiers`, `.film-hero`→copied verbatim into `_films-list` with a note (no base rule exists in the source), `nav`→`_header` |

`style.scss` keeps `@use "@parent-scss/variables" as *;` + the `@forward`/`@use` chain per the
foundation reference. Media queries copied from the mockup are pasted verbatim — clone doctrine
overrides the repo's mixin rule for source CSS; only S2-added tiers (hamburger, 375/768 fixes)
use the parent `from()`/`until()` mixins.

## 6. Additional tables

### 6.1 Media inventory

28 assets (23 binary images + 5 SVG gradient placeholders) with filenames, lines, alts, and the
product data — see `docs/build/ui-spec.md` §10 (single source; S5's
`scripts/upload-images.php` builds `$files` from it). `about-story.jpg` is 8.3 MB of inline
base64 (line 392) — uploads as-is, flagged for optional recompression (D9). The 5 placeholder
SVGs are real media assets until the member supplies replacements (D6).

### 6.2 Authoring-artifact cleanup list (D5 — minimal, documented)

Drop while porting markup: `contenteditable` attributes (all), `data-label` attributes,
`class="editable-img"`/`class="editable-video"` hooks, `.vhint` span (L885), `.film-tools`
button groups (507–511, 526–530, 545–549, 564–568, 583–587), `#addFilmBtn` (600),
`#addEventBtn` (910), `#imgFileInput` (1068), the `.edit-hint` bar (308–310), focus-ring inline
styles on the residue `<h3>`s, and the authoring `title` attribute on the teaser video (L883).
All real content, all other inline styles, and the DOM shape are preserved verbatim.

### 6.3 Editing-residue lines to normalize

Four empty duplicate `<h3>` pairs (an editing artifact left one empty heading + one styled
duplicate): **L573, L592** (films 4–5 titles) and **L661, L721** (products 4 and 9) — keep the
real title, drop the empty `<h3 contenteditable>` and the inline-styled duplicate. Also
normalize: the two empty `<br>` meta spans on films 2–3 (L536, L555) render as nothing — the
meta arrays carry only the real values; the empty `<p><br></p>` descriptions on films 2–5
(L537, L556, L575, L594) are the mockup's actual content and are kept verbatim.

## 7. Decisions taken at S1

- **D1 — WooCommerce stays OFF; shop is a static block.** The mockup shop is a catalog with qty
  inputs whose "Order" buttons only show a transient `Added ×N ✓` label — no cart, no product
  pages, no checkout, no payment (the ticket flow is a bespoke modal ending in an order summary +
  mailto). Skill doctrine: pricing cards with no checkout → static blocks, do not install
  commerce for decoration. The member may skip S0 runbook Step 4b; activating WooCommerce later
  is a scope change (C-flow). ACF: nothing requires it → stays inactive.
- **D2 — Slugs = mockup page ids verbatim** (see §1).
- **D3 — Blocks use namespace `ai-zippy/` and the existing `ai-zippy` category** (parent
  theme's `ThemeSetup::blockCategories()`), not per-page categories.
- **D4 — Mockup class names kept verbatim** (`.hero`, `.card`, `.product`, …) — the mockup has
  real classes; cloning them is the literal interpretation. Block wrapper classes are never
  styled.
- **D5 — Authoring-artifact cleanup (documented, minimal)** — see §6.2/§6.3.
- **D6 — Placeholder-gradient SVGs become real media assets** (films 2–5 posters, event teaser
  poster) until the member supplies real media — they are what the mockup renders.
- **D7 — Forms replicate the mockup's client-side behavior only** (success message, no
  storage/email) — wiring real handling is a future C-flow, recorded as an open issue.
- **D8 — Modals are owned by their blocks** (films-list / upcoming-list).
- **D9 — `about-story.jpg` uploads as-is** (faithful; WP generates scaled sizes) — member may
  recompress.
- **D10 — The shop page is ONE block** (`shop-merchandise`) — see §2 rules.
