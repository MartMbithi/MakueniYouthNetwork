#!/usr/bin/env bash
# -----------------------------------------------------------------------------
# scripts/build-deploy.sh — build a ready-to-upload cPanel deployment artifact
#
# Produces  dist/myn-cpanel-<timestamp>.tar.gz  containing everything the
# server needs and NOTHING it doesn't (.env, vendor's dev packages, storage
# logs, uploaded media, git history, IDE folders, test caches all excluded).
#
# Default: runs `composer install --no-dev --optimize-autoloader` so the
# tarball ships with production autoloads. Pass --skip-composer to skip
# that step if you have already done it.
#
# Usage:
#   scripts/build-deploy.sh
#   scripts/build-deploy.sh --skip-composer
# -----------------------------------------------------------------------------

set -euo pipefail
cd "$(dirname "$0")/.."

ROOT="$(pwd)"
STAMP="$(date +%Y%m%d-%H%M%S)"
OUT_DIR="$ROOT/dist"
TARBALL="$OUT_DIR/myn-cpanel-$STAMP.tar.gz"

PHP=/Applications/XAMPP/xamppfiles/bin/php
[[ -x "$PHP" ]] || PHP=$(command -v php || true)
COMPOSER_PHAR="$ROOT/composer.phar"

mkdir -p "$OUT_DIR"

# 1. Production composer install (writes a slimmer vendor/)
if [[ "${1:-}" != "--skip-composer" ]]; then
  echo "==> composer install --no-dev --optimize-autoloader"
  "$PHP" "$COMPOSER_PHAR" install --no-dev --optimize-autoloader --no-interaction --quiet
fi

# 2. Refresh banner on every PHP file
echo "==> applying mandatory letterhead banner"
"$PHP" "$ROOT/scripts/apply-banner.php" >/dev/null

# 3. Build the tarball
echo "==> packaging $TARBALL"
# COPYFILE_DISABLE keeps macOS from leaking ._-AppleDouble files into the tar
COPYFILE_DISABLE=1 tar \
  --exclude='./.git'                  \
  --exclude='./.github'               \
  --exclude='./.claude'               \
  --exclude='./.gitignore'            \
  --exclude='./.gitattributes'        \
  --exclude='./.DS_Store'             \
  --exclude='./.idea'                 \
  --exclude='./.vscode'               \
  --exclude='./.phpunit.cache'        \
  --exclude='./.phpunit.result.cache' \
  --exclude='./phpunit.xml'           \
  --exclude='./dist'                  \
  --exclude='./design'                \
  --exclude='./.env'                  \
  --exclude='./composer.phar'         \
  --exclude='./composer-setup.php'    \
  --exclude='./tests'                 \
  --exclude='./storage/logs/*'        \
  --exclude='./public/uploads/*'      \
  --exclude='./BUILD-PLAN.md'         \
  --exclude='./GETTING-STARTED.md'    \
  --exclude='./MYN-CMS-Implementation-Plan.md' \
  --exclude='*.log'                   \
  --exclude='*.bak'                   \
  --exclude='._*'                     \
  -czf "$TARBALL" .

# 4. Restore composer's dev deps so the working tree stays usable
if [[ "${1:-}" != "--skip-composer" ]]; then
  echo "==> restoring composer dev dependencies"
  "$PHP" "$COMPOSER_PHAR" install --optimize-autoloader --no-interaction --quiet
fi

echo
echo "OK — deployment artifact ready:"
ls -lh "$TARBALL"
echo
echo "Upload it via cPanel -> File Manager and follow docs/cpanel-deploy.md."
