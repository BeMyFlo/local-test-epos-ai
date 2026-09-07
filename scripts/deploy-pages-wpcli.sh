#!/usr/bin/env bash
#
# Create the 22 sitemap pages on a remote WordPress install via WP-CLI.
#
# The PHP scripts in this directory expect to be run next to wp-load.php. This
# wrapper ships them to the target and runs them through `wp eval-file`, so the
# same, already-verified logic applies on staging or production.
#
# Usage:
#   ./deploy-pages-wpcli.sh --ssh user@host --path /var/www/html [--apply]
#   ./deploy-pages-wpcli.sh --ssh user@host --path /path/to/wp --refresh-copy --apply
#
# Without --apply every step runs as a dry run and only prints its plan.

set -euo pipefail

SSH=""
WP_PATH=""
APPLY=""
REFRESH=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --ssh)     SSH="$2"; shift 2 ;;
    --path)    WP_PATH="$2"; shift 2 ;;
    --apply)   APPLY="--apply"; shift ;;
    --refresh-copy) REFRESH="--refresh-copy"; shift ;;
    -h|--help)
      sed -n '2,16p' "$0" | sed 's/^# \{0,1\}//'
      exit 0 ;;
    *) echo "Unknown option: $1" >&2; exit 1 ;;
  esac
done

if [[ -z "$SSH" ]]; then
  echo "ERROR: --ssh user@host is required (the scripts have to be copied over)." >&2
  exit 1
fi

# Build the shared part of every wp invocation.
WP_ARGS=(--ssh="$SSH")
[[ -n "$WP_PATH" ]] && WP_ARGS+=(--path="$WP_PATH")

run_wp() { wp "${WP_ARGS[@]}" "$@"; }

echo "==> Checking connection"
run_wp core version
run_wp option get siteurl

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# eval-file runs the file locally-read, remotely-executed, so the manifest and
# copy files have to travel with it. They are required by __DIR__, so the whole
# directory is staged on the remote side first.
REMOTE_TMP="/tmp/az-scripts-$$"

echo "==> Staging scripts to $SSH:$REMOTE_TMP"
ssh "$SSH" "mkdir -p $REMOTE_TMP"
scp -q "$SCRIPT_DIR"/remaining-pages-manifest.php \
       "$SCRIPT_DIR"/offering-copy.php \
       "$SCRIPT_DIR"/sync-sitemap-pages.php \
       "$SCRIPT_DIR"/fix-attachment-slug-clash.php \
       "$SCRIPT_DIR"/fix-home-hero-heading.php \
       "$SSH:$REMOTE_TMP/"

# The scripts find wp-load.php on their own, but the remote root is passed
# explicitly via WP_ROOT so a non-standard layout still resolves.
REMOTE_WP="${WP_PATH:-$(run_wp eval 'echo ABSPATH;')}"
REMOTE_WP="${REMOTE_WP%/}"
echo "==> WordPress root on remote: $REMOTE_WP"

echo
echo "==> 1/3  Sitemap pages ${APPLY:+(APPLY)}${APPLY:-(dry run)}"
ssh "$SSH" "cd $REMOTE_TMP && WP_ROOT='$REMOTE_WP' php sync-sitemap-pages.php $REFRESH $APPLY"

echo
echo "==> 2/3  Attachment slug clashes ${APPLY:+(APPLY)}${APPLY:-(dry run)}"
ssh "$SSH" "cd $REMOTE_TMP && WP_ROOT='$REMOTE_WP' php fix-attachment-slug-clash.php $APPLY"

echo
echo "==> 3/3  Home hero heading ${APPLY:+(APPLY)}${APPLY:-(dry run)}"
ssh "$SSH" "cd $REMOTE_TMP && WP_ROOT='$REMOTE_WP' php fix-home-hero-heading.php $APPLY"

echo
echo "==> Flushing rewrite rules and cache"
if [[ -n "$APPLY" ]]; then
  run_wp rewrite flush
  run_wp cache flush || true
fi

echo "==> Cleaning up"
ssh "$SSH" "rm -rf $REMOTE_TMP"

echo
if [[ -n "$APPLY" ]]; then
  echo "Done. Re-run without --apply to confirm everything now reports UNCHANGED."
else
  echo "Dry run complete. Re-run with --apply to write the changes."
fi
