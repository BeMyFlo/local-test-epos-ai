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
| S3 blocks | IN PROGRESS (P4 of N) | 2026-09-24 | P1 home trio built (3/15 blocks): 3× 7-file block folders in `src/blocks`; `php -l` clean ×3; block.json `JSON.parse` ×3; `npm run build:child` exit 0 with no Sass warnings (vite CSS 12.09 kB, was 10.86 kB at S2; wp-scripts discovers 3 entries — home-hero, home-who-we-are, home-featured-works; webpack compiled successfully); built CSS contains the hero/feature-card greps (`.hero-grid`, `1.05fr .95fr`, `aspect-ratio:4/3`, `.feature-card .cap`, `rgba(20,22,30,.85)` → minified `#14161ed9`, `@media(max-width:900px)`, `.hero-grid>*{min-width:0}` @767px [S3] guard); 4 images extracted to `assets/img` (home-hero.avif 158649 B, home-feature-mad-vibe-city.png 346187 B, home-feature-music-beats.avif 166798 B, home-feature-global-logistics.avif 197715 B — signatures AVIF×3 + PNG); zero authoring-residue greps ×3 (src + built copies); 38/38 block.json default strings byte-exact in the mockup; `@use` wiring count 3, `style.scss` untouched; git scope = 3 block folders + 3 partials + 4 images + this file. P2 about pair built (5/15 blocks): +2× 7-file block folders (`about-story` L381–418, `about-pillars` L419–429); `php -l` clean ×2; block.json `JSON.parse` ×2; `npm run build:child` exit 0 with no Sass warnings (vite CSS 12.84 kB, was 12.09 kB; wp-scripts discovers 5 entries — +about-story, +about-pillars); built CSS carries the story greps (`.story-block:before`, `border-radius:16px 0 0 16px`, `.sub-cards{grid-template-columns:1fr}` inside `@media(max-width:900px)`, `.sub-card{background:var(--bg-soft)`); 30/30 content string defaults byte-exact in the mockup (film paragraph matched incl. its `<i>` tags; `imgFile` counted as infrastructure per P1 rule); zero authoring-residue greps ×2 (src + built `assets/blocks` copies); `about-story.jpg` extracted from mockup L392 (JPEG 4000×6000, 6,214,677 B decoded from 8,286,236 base64 chars); `_about-story.scss` filled (mockup L144–153 verbatim + L296 `.sub-cards` row; no [S3]-added guards, P2-D3); `@use` statement count still 21, `style.scss`/`functions.php`/`theme.json`/`_components.scss`/`_base.scss` untouched; git scope = 2 block folders (14 files) + 1 image + `_about-story.scss` + this file. P3 film pair built (7/15 blocks): +2 block folders (`film-intro` L434–449, 7 files; `film-tiers` L452–491, 8 files incl. the B12 `view.js` registered via block.json `"viewScript": "file:./view.js"` — P3-D8, zero shared-file edits; hardcoded `id="filmEnquiry"` on the wrapper with `supports.anchor:false` — P3-D2); `php -l` clean ×2; block.json `JSON.parse` ×2 + viewScript grep; `node --check view.js` exit 0; `npm run build:child` exit 0 with no Sass warnings (vite CSS 14.04 kB, was 12.84 kB; wp-scripts discovers 7 entries — +film-intro, +film-tiers; `assets/blocks/film-tiers/` emits `view.js` + `view.asset.php`); built CSS carries the tier greps (`.tier-grid`, `.tier:before` — the minifier's form of `.tier::before`, `.tier-tag`, `.tier-price`, `.tier-runtime`, `.tier p`, `.tier-note`, `border-radius:20px`, `.tier-grid{grid-template-columns:1fr}` inside `@media(max-width:900px)`); 30/30 defaults byte-exact in the mockup (14 film-intro + 16 film-tiers, entity-decoded compare incl. em-dashes/curly apostrophes/emoji and the `<b>/<i>/<div><span style>` markup; `imgFile`/`imgId`/`imgUrl`/`btnUrl` counted as infrastructure per P1 rule); zero authoring-residue greps ×2 (src + built `assets/blocks` copies); `film-production.avif` extracted from mockup L438 (AVIF, 86,177 B decoded from 114,904 base64 chars, bytes 4–12 = `ftypavif`); `_film-tiers.scss` filled (mockup L186–193 verbatim + L296 `.tier-grid` row; no [S3]-added guards, P3-D7); `@use` statement count still 21 (`^@use` grep — comment lines excluded), `style.scss`/`functions.php`/`theme.json`/`_components.scss`/`_base.scss`/`_film-intro.scss` untouched; git scope = 2 block folders (15 files) + 1 image + `_film-tiers.scss` + this file. P4 films pair built (9/15 blocks): +2 block folders (`films-list` L497–601 + filmModal L1000–1017, 8 files incl. the B10+B11 `view.js` registered via block.json `"viewScript": "file:./view.js"` — modal rendered inside the wrapper, P4-D1/P4-D2; `cta-banner` shared — films instance L603–613, 7 files, no view.js, structural `soft` variant P4-D9); `php -l` clean ×2; block.json `JSON.parse` ×2 + viewScript grep (films-list only among the new pair); `node --check view.js` exit 0; `npm run build:child` exit 0 with no Sass warnings (vite CSS 17.52 kB, was 14.04 kB; wp-scripts discovers 9 entries — +films-list, +cta-banner; `assets/blocks/films-list/` emits `view.js` + `view.asset.php`); built CSS carries the film greps (`.film-list`, `minmax(300px,1fr)`, `.film-card`, `.play-badge` 62px, `.film-tag` `border-radius:999px`, `.film-runtime` — `rgba(20,48,61,.85)` → minified `#14303dd9`, `.film-body`, `.film-meta`, `.watch-btn`, `.no-video-flag` `#8a6417`/`#fdf6e0`, `.vmodal` `#0f2733` `max-width:920px`, `.vmodal-close` 36px, `.film-hero{grid-template-columns:1fr}` inside `@media(max-width:900px)`); 42/42 string defaults byte-exact in the mockup (entity-decoded compare incl. curly apostrophe/em-dash/middots; 16 infrastructure values — `imgFile`/`imgId`/`imgUrl`/`videoUrl`/`btnUrl` — plus the `soft` boolean per P1 rule); zero authoring-residue greps (src + built `assets/blocks` copies); 5 poster images extracted (films-poster-mad-vibe-city.png PNG 482×222 346,187 B from 461,657 base64 chars, `\x89PNG` magic; films-poster-film-2…5.svg 769 B each URL-decoded from `data:image/svg+xml;utf8,`, stops `#F5F3CD`→`#B2F1F8` / `#B2F1F8`→`#85BEDB` / `#FCDB7E`→`#B2F1F8` / `#85BEDB`→`#F5F3CD`); `_films-list.scss` filled (mockup L196–207 + L212–233 verbatim, `.film-tools` L208–211 NOT ported, + L296 `.film-hero` row with the ui-spec §9 note; no [S3]-added guards, P4-D7); `@use` statement count still 21, `style.scss`/`functions.php`/`theme.json`/`_components.scss`/`_base.scss`/`_cta-banner.scss` untouched; git scope = 2 block folders (15 files) + 5 images + `_films-list.scss` + this file. Live-env items NEEDS-LIVE-ENV (registration via `wp eval` WP_Block_Type_Registry, docker logs PHP check) — commands in "S3 live-env checks" below (task SPEC §9.2) |
| S4 editor parity | TODO | | editor canvas matches the frontend per block (`Editor OK` column below) |
| S5 content & media | TODO | | all page-plan pages created, `<img src>` from `/wp-content/uploads/`, front page correct |
| S6 visual QA | TODO | | no significant desktop diffs; 375px + 768px clean; `docs/build/qa-report.md` |
| S7 functional QA | TODO | | every behavior-map row + shop/form flows PASS with evidence |

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
| `ai-zippy/home-hero` | mockup L337–350 | literal clone (D4/D5 cleanup) | `_home-hero.scss` filled (L79–88 + L296 row + [S3] G5 guard) | — (S5) | NEEDS-LIVE-ENV (S4) |
| `ai-zippy/home-who-we-are` | mockup L352–364 | literal clone (inline styles kept) | `_home-who-we-are.scss` ships empty (component-driven) | — (S5) | NEEDS-LIVE-ENV (S4) |
| `ai-zippy/home-featured-works` | mockup L366–376 | literal clone | `_home-featured-works.scss` filled (L110–114) | — (S5) | NEEDS-LIVE-ENV (S4) |
| `ai-zippy/about-story` | mockup L381–418 | literal clone (inline styles kept; `introBlock` P2-D1; kses italics P2-D2) | `_about-story.scss` filled (L144–153 + L296 `.sub-cards` row) | — (S5) | NEEDS-LIVE-ENV (S4) |
| `ai-zippy/about-pillars` | mockup L419–429 | literal clone (single-line cards) | `_about-pillars.scss` ships empty (component-driven) | — (S5) | NEEDS-LIVE-ENV (S4) |
| `ai-zippy/film-intro` | mockup L434–449 | literal clone (D5 cleanup; kses intro P3-D3; CTA → real anchor via `film_intro_url()` P3-D1) | `_film-intro.scss` ships empty (component-driven) | — (S5) | NEEDS-LIVE-ENV (S4) |
| `ai-zippy/film-tiers` | mockup L452–491 | literal clone (hardcoded id `filmEnquiry` P3-D2; kses tier text P3-D3; B12 view.js via block.json viewScript P3-D8) | `_film-tiers.scss` filled (L186–193 + L296 `.tier-grid` row) | — (S5) | NEEDS-LIVE-ENV (S4) |
| `ai-zippy/films-list` | mockup L497–601 + filmModal L1000–1017 | literal clone (modal as last child inside the wrapper P4-D1; film-1 mirror hardcoded P4-D2; kses desc P4-D3; no-video chip on empty videoUrl P4-D4; `id="filmGrid"` kept P4-D10; B10+B11 view.js via block.json viewScript) | `_films-list.scss` filled (L196–207 + L212–233, `.film-tools` L208–211 dropped + L296 `.film-hero` row) | — (S5) | NEEDS-LIVE-ENV (S4) |
| `ai-zippy/cta-banner` (shared — films instance; events L862–870 + upcoming L913–923 land at S5) | mockup L603–613 | literal clone (structural `soft` variant P4-D9; inline card style kept; `<button data-goto>` → real `<a>` via `cta_banner_url()`) | `_cta-banner.scss` ships empty (component-driven) | — (S5) | NEEDS-LIVE-ENV (S4) |

Rows land at S3 from `docs/build/block-map.md` §2 — 15 blocks, namespace `ai-zippy/` (page-plan in §1).
9 of 15 built (P1 home trio + P2 about pair + P3 film pair + P4 films pair); the remaining 6 land
in later S3 task runs.

### S3 live-env checks — run locally by the member (after S0 bootstrap)

```bash
set -a; . ./.env; set +a
npm run build:child   # assets/blocks/ is gitignored — build before testing

# Block registration (wp block list is NOT available in this image — see Environment)
docker exec ${PROJECT_ID} wp eval 'foreach (["home-hero","home-who-we-are","home-featured-works","about-story","about-pillars","film-intro","film-tiers","films-list","cta-banner"] as $b) { $n="ai-zippy/".$b; printf("%-32s %s\n", $n, WP_Block_Type_Registry::get_instance()->is_registered($n) ? "registered" : "MISSING"); }' --allow-root

# No PHP fatals (error.log is a /dev/stderr symlink in this image family)
docker logs ${PROJECT_ID} 2>&1 >/dev/null | tail -50
```

Expected: 9× `registered`, error stream free of new PHP Fatal/Warning. (Frontend render +
editor-canvas checks happen at S4/S5 once pages exist — outside the S3 batches' range.)

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
  gradient stops verified; the event teaser poster lands with `upcoming-list`'s batch.
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
  content edit, not a code change).
- [ ] Mockup L242–248 modal chrome (`.modal-head`/`.modal-close`/`.modal-body`/`.modal-foot`
  etc.) is not explicitly cited in block-map §5 rows 3/4 (row 3 ends at 241, row 4 resumes at
  249) — it resolves to `sections/_upcoming-list.scss`, same owner as the ticket-modal
  internals it belongs to (S2a spec D8); the S3 worker for `upcoming-list` must port it.
