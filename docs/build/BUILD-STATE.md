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
| S1 analyze | TODO | | `docs/build/ui-spec.md` + `block-map.md` (incl. page-plan + behavior map), no remaining "TBD" |
| S2 theme foundation | TODO | | `npm run build:child` green, header/footer/templates render, tokens in CSS output, `child.js` behaviors |
| S3 blocks | TODO | | every block-map block registered (see `wp block list` caveat above), no PHP fatal, `view.js` behaviors |
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

Rows land at S3 from `docs/build/block-map.md`; the block count is unknown until S1.

## Open issues

- [ ] The existing `pricemrcopper` container + DB at `:17770` belong to the ORIGINAL repo
  (`FCS-WP/pricemrcopper`, checkout `/home/tobithongha/pricemrcopper`), which already contains a
  complete prior build (its own BUILD-STATE shows S0–S7 all PASS, 2026-09-07/08). Out of scope:
  never stopped, reset, or written by this pipeline — read-only.
- [ ] The original repo (especially its `docs/build/*` from the completed build) is prior-art
  reference for later stages.
- [ ] WooCommerce scope is tentative (the mockup contains a shop); final decision at S1. ACF
  stays inactive unless S1 requires it.
- [ ] `wp block list` is not a registered wp-cli command in this image (see Environment) — the
  S3 gate must verify registration via `wp eval-file` over `WP_Block_Type_Registry` instead.
