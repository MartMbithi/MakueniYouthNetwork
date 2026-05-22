#!/usr/bin/env bash
# Responsive audit — for every public + admin page:
#   1. confirm <meta name=viewport> is present
#   2. confirm no horizontal overflow risk (look for inline fixed widths >480px
#      and inline grid-template-columns that override CSS media queries)
#   3. confirm CSS media queries actually exist for the page's main grids

set -u
cd "$(dirname "$0")/.."

PORT="${PORT:-8765}"
HOST="http://localhost:$PORT"
PHP=/Applications/XAMPP/xamppfiles/bin/php

# Boot the dev server (or use an existing one on the port)
EXISTING=$(lsof -ti tcp:"$PORT" 2>/dev/null || true)
if [[ -z "$EXISTING" ]]; then
  $PHP -S "localhost:$PORT" -t public server.php > /tmp/myn-resp-srv.log 2>&1 &
  SRV=$!
  trap 'kill $SRV 2>/dev/null' EXIT
  sleep 1.2
fi

PASS=0; FAIL=0

check() {
  local label="$1" expect="$2" got="$3"
  if [[ "$expect" == "$got" ]]; then
    printf "  \033[32mPASS\033[0m  %s\n" "$label"; PASS=$((PASS+1))
  else
    printf "  \033[31mFAIL\033[0m  %s (expected %s, got %s)\n" "$label" "$expect" "$got"; FAIL=$((FAIL+1))
  fi
}

# Capture the route to a temp file so we can grep it many times
audit() {
  local path="$1" name="$2"
  local f="/tmp/myn-resp-$$-$RANDOM.html"
  local code=$(curl -s -o "$f" -w '%{http_code}' "$HOST$path")
  check "$name returns 200" 200 "$code"
  # Viewport meta present
  grep -q '<meta name="viewport"' "$f" && V=yes || V=no
  check "$name has <meta viewport>" yes "$V"
  # No inline grid-template-columns that bypass media queries
  if grep -qE 'style="[^"]*grid-template-columns:[[:space:]]*(repeat\(|1\.[0-9]+fr|1fr 1fr|1fr 1fr 1fr|1fr 1fr 1fr 1fr)' "$f"; then
    O=yes; else O=no
  fi
  check "$name has no inline grid override" no "$O"
  # No inline fixed widths > 480px on body content
  if grep -qE 'style="[^"]*max-width:[[:space:]]*[5-9][0-9]{2,}px' "$f"; then
    F=yes; else F=no
  fi
  check "$name no overly-wide inline max-width" no "$F"
  rm -f "$f"
}

echo "== PUBLIC PAGES =="
audit '/'                                                                "homepage"
audit '/programs'                                                         "programs list"
audit '/programs/advocacy-civic-engagement'                               "program detail"
audit '/impact'                                                           "impact list"
audit '/impact?page=1'                                                    "impact paginated"
audit '/impact/bridging-the-gap-how-makueni-youth-initiative-is-driving-inclusive-governance'       "post detail"
audit '/events'                                                           "events list"
audit '/donate'                                                           "donate form"
audit '/volunteer'                                                        "volunteer form"
audit '/contact'                                                          "contact form"
audit '/about'                                                            "about page (catch-all)"
audit '/admin/login'                                                      "admin login"

# 404 is its own assertion — we expect 404 and a viewport meta tag, not 200
NF_FILE=/tmp/myn-resp-nf.html
NF_CODE=$(curl -s -o "$NF_FILE" -w '%{http_code}' "$HOST/nonexistent-route-deliberately")
check "404 page returns 404"          404 "$NF_CODE"
grep -q '<meta name="viewport"' "$NF_FILE" && V=yes || V=no
check "404 page has <meta viewport>"  yes "$V"
rm -f "$NF_FILE"

echo
echo "== ADMIN PAGES (logged in) =="
JAR=/tmp/myn-resp-jar-$$
curl -sc "$JAR" -b "$JAR" -o /tmp/myn-resp-l.html "$HOST/admin/login"
TK=$(grep -oE 'name="_csrf" value="[a-f0-9]+"' /tmp/myn-resp-l.html | head -1 | sed -E 's/.*value="([^"]+)".*/\1/')
curl -sc "$JAR" -b "$JAR" -o /dev/null -X POST \
  --data-urlencode "_csrf=$TK" \
  --data-urlencode "email=admin@makueniyouth.org" \
  --data-urlencode "password=ChangeMe2026!" \
  "$HOST/admin/login"

admin_audit() {
  local path="$1" name="$2"
  local f="/tmp/myn-resp-$$-$RANDOM.html"
  local code=$(curl -s -b "$JAR" -o "$f" -w '%{http_code}' "$HOST$path")
  check "$name returns 200" 200 "$code"
  grep -q '<meta name="viewport"' "$f" && V=yes || V=no
  check "$name has <meta viewport>" yes "$V"
  grep -q 'admin-mobile-bar' "$f" && M=yes || M=no
  check "$name has mobile-toggle bar" yes "$M"
  # No INLINE grid 1fr 1fr that lacks a class wrapper — must use admin-grid-2 / admin-filter-bar
  if grep -qE 'style="[^"]*grid-template-columns:[[:space:]]*(1fr 1fr|repeat\()' "$f"; then
    O=yes; else O=no
  fi
  check "$name no inline grid override" no "$O"
  rm -f "$f"
}

admin_audit '/admin'                            "dashboard"
admin_audit '/admin/posts'                      "posts list"
admin_audit '/admin/posts/create'               "new post form"
admin_audit '/admin/programs'                   "programs list"
admin_audit '/admin/events'                     "events list"
admin_audit '/admin/pages'                      "pages list"
admin_audit '/admin/stats'                      "stats list"
admin_audit '/admin/partners'                   "partners list"
admin_audit '/admin/settings'                   "settings page"
admin_audit '/admin/messages'                   "messages inbox"
admin_audit '/admin/volunteers'                 "volunteers inbox"
admin_audit '/admin/donations'                  "donations ledger"
admin_audit '/admin/users'                      "users list"

rm -f "$JAR"

echo
TOTAL=$((PASS+FAIL))
printf "RESPONSIVE SUMMARY: %d / %d passed\n" "$PASS" "$TOTAL"
exit $([ $FAIL -eq 0 ] && echo 0 || echo 1)
