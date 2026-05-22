#!/usr/bin/env bash
# -----------------------------------------------------------------------------
# tests/e2e.sh — Makueni Youth Network end-to-end walkthrough
#
# Boots the dev server on $PORT (default 8765), walks every user journey
# (visitor + admin + Paystack), reports PASS/FAIL per check, and tears the
# server down at the end. Exits 0 on full pass, 1 on any failure.
#
# Usage:  tests/e2e.sh                # default port 8765
#         PORT=8000 tests/e2e.sh      # custom port
# -----------------------------------------------------------------------------

set -u
cd "$(dirname "$0")/.."

PORT="${PORT:-8765}"
HOST="http://localhost:${PORT}"
PHP=/Applications/XAMPP/xamppfiles/bin/php
MYSQL="/Applications/XAMPP/xamppfiles/bin/mysql -uroot -h127.0.0.1 myn --skip-column-names"
JAR_ADMIN=/tmp/myn-e2e-admin.jar
JAR_EDITOR=/tmp/myn-e2e-editor.jar
JAR_VISITOR=/tmp/myn-e2e-visitor.jar

PASS=0; FAIL=0; FAILS=()

check() {
  local label="$1"; local expect="$2"; local got="$3"
  if [[ "$got" == "$expect" ]]; then
    printf "  \033[32mPASS\033[0m  %s  (got: %s)\n" "$label" "$got"
    PASS=$((PASS+1))
  else
    printf "  \033[31mFAIL\033[0m  %s  (expect: %s  got: %s)\n" "$label" "$expect" "$got"
    FAIL=$((FAIL+1))
    FAILS+=("$label")
  fi
}

check_match() {
  local label="$1"; local needle="$2"; local hay="$3"
  if echo "$hay" | grep -qF "$needle"; then
    printf "  \033[32mPASS\033[0m  %s\n" "$label"
    PASS=$((PASS+1))
  else
    printf "  \033[31mFAIL\033[0m  %s  (did not find: %s)\n" "$label" "$needle"
    FAIL=$((FAIL+1))
    FAILS+=("$label")
  fi
}

status() { curl -s -o /dev/null -w '%{http_code}' "$@"; }

# ---------------------------------------------------------------------------
# Boot
# ---------------------------------------------------------------------------
section() { printf "\n\033[1;34m=== %s ===\033[0m\n" "$1"; }

section "BOOT"
rm -f "$JAR_ADMIN" "$JAR_EDITOR" "$JAR_VISITOR"
: > storage/logs/app.log

# Free the port if a stale instance is hanging on
lsof -ti tcp:$PORT 2>/dev/null | xargs kill 2>/dev/null || true
sleep 0.5

$PHP -S "localhost:$PORT" -t public server.php > /tmp/myn-e2e-srv.log 2>&1 &
SRV_PID=$!
trap 'kill $SRV_PID 2>/dev/null; rm -f "$JAR_ADMIN" "$JAR_EDITOR" "$JAR_VISITOR"' EXIT

sleep 1.2
boot_code=$(status "$HOST/")
check "dev server boots on $HOST" "200" "$boot_code"

# Ensure seeded admin user is present
ADMIN_EXISTS=$($MYSQL -e "SELECT COUNT(*) FROM users WHERE email='admin@makueniyouth.org'")
check "seeded admin user present" "1" "$ADMIN_EXISTS"

# Restore phone in case a previous run changed it
$MYSQL -e "UPDATE settings SET setting_value='+254 710 580 604' WHERE setting_key='phone'" >/dev/null

# ---------------------------------------------------------------------------
# Visitor journey
# ---------------------------------------------------------------------------
section "VISITOR — public site walkthrough"

for route in '/' '/programs' '/programs/advocacy-civic-engagement' \
             '/programs/leadership-talent-development' '/programs/youth-mentorship' \
             '/impact' '/impact?page=1' \
             '/impact/bridging-the-gap-youth-leading-change-in-governance' \
             '/events' '/donate' '/volunteer' '/contact' \
             '/sitemap.xml' '/about'; do
  check "GET $route" "200" "$(status "$HOST$route")"
done

for route in '/programs/no-such-program' '/impact/no-such-post' \
             '/events/no-such-event' '/no-such-page'; do
  check "GET $route (404)" "404" "$(status "$HOST$route")"
done

# Sitemap shape
SITEMAP=$(curl -s "$HOST/sitemap.xml")
check_match "sitemap is valid XML" "<urlset" "$SITEMAP"
SITEMAP_URLS=$(echo "$SITEMAP" | grep -c '<url>')
check "sitemap URL count" "14" "$SITEMAP_URLS"

# Homepage has design markers
HOME=$(curl -s "$HOST/")
check_match "homepage shows hero section"  'class="hero"'        "$HOME"
check_match "homepage shows programs grid" 'class="prog-grid"'   "$HOME"
check_match "homepage shows stats stripe"  'class="stripe"'      "$HOME"
check_match "homepage shows footer phone"  '+254 710 580 604'    "$HOME"
check_match "homepage shows email"         'info@makueniyouth.org' "$HOME"

# SEO meta tags
check_match "homepage <title>" "<title>Makueni Youth Network" "$HOME"
POST_HTML=$(curl -s "$HOST/impact/bridging-the-gap-youth-leading-change-in-governance")
check_match "post detail per-page og:title" 'og:title" content="Bridging the Gap' "$POST_HTML"
check_match "post detail per-page og:image" 'og:image" content="https://makueniyouth.org/wp-content/uploads/2026/05/Youth-Gov' "$POST_HTML"

# Lazy-load on all homepage images
IMG_COUNT=$(echo "$HOME" | grep -c '<img ')
LAZY_COUNT=$(echo "$HOME" | grep -c 'loading="lazy"')
check "every <img> is lazy-loaded" "$IMG_COUNT" "$LAZY_COUNT"

# ---------------------------------------------------------------------------
# Public form journey
# ---------------------------------------------------------------------------
section "VISITOR — contact + volunteer form submissions"

# Contact form CSRF reject
nocsrf=$(curl -s -o /dev/null -w '%{http_code}' -X POST \
         -d 'name=X&email=x@y.com&message=hello' "$HOST/contact")
check "POST /contact missing CSRF" "419" "$nocsrf"

# Valid contact submission
COUNT_M_BEFORE=$($MYSQL -e 'SELECT COUNT(*) FROM messages')
curl -sc "$JAR_VISITOR" -b "$JAR_VISITOR" -o /tmp/myn-e2e-contact.html "$HOST/contact"
CTOK=$(grep -oE 'name="_csrf" value="[a-f0-9]+"' /tmp/myn-e2e-contact.html | head -1 | sed -E 's/.*value="([^"]+)".*/\1/')
post_code=$(curl -sc "$JAR_VISITOR" -b "$JAR_VISITOR" -o /dev/null -w '%{http_code}' -X POST \
    --data-urlencode "_csrf=$CTOK" \
    --data-urlencode "name=E2E Tester" \
    --data-urlencode "email=e2e@myn.test" \
    --data-urlencode "subject=E2E run" \
    --data-urlencode "message=End-to-end verification submission body." \
    "$HOST/contact")
check "POST /contact valid CSRF redirects" "302" "$post_code"
COUNT_M_AFTER=$($MYSQL -e 'SELECT COUNT(*) FROM messages')
check "contact insert produces +1 row" "$((COUNT_M_BEFORE+1))" "$COUNT_M_AFTER"

# Valid volunteer submission
COUNT_V_BEFORE=$($MYSQL -e 'SELECT COUNT(*) FROM volunteers')
curl -sc "$JAR_VISITOR" -b "$JAR_VISITOR" -o /tmp/myn-e2e-vol.html "$HOST/volunteer"
VTOK=$(grep -oE 'name="_csrf" value="[a-f0-9]+"' /tmp/myn-e2e-vol.html | head -1 | sed -E 's/.*value="([^"]+)".*/\1/')
v_code=$(curl -sc "$JAR_VISITOR" -b "$JAR_VISITOR" -o /dev/null -w '%{http_code}' -X POST \
    --data-urlencode "_csrf=$VTOK" \
    --data-urlencode "full_name=E2E Volunteer" \
    --data-urlencode "email=vol@myn.test" \
    --data-urlencode "phone=+254700000111" \
    --data-urlencode "interest=Youth Mentorship" \
    --data-urlencode "message=Happy to help." \
    "$HOST/volunteer")
check "POST /volunteer valid CSRF redirects" "302" "$v_code"
COUNT_V_AFTER=$($MYSQL -e 'SELECT COUNT(*) FROM volunteers')
check "volunteer insert produces +1 row" "$((COUNT_V_BEFORE+1))" "$COUNT_V_AFTER"

# ---------------------------------------------------------------------------
# Admin auth + dashboard
# ---------------------------------------------------------------------------
section "ADMIN — login, dashboard, navigation"

unauth=$(curl -s -o /dev/null -w '%{http_code}' "$HOST/admin")
check "unauth GET /admin redirects" "302" "$unauth"

curl -sc "$JAR_ADMIN" -b "$JAR_ADMIN" -o /tmp/myn-e2e-login.html "$HOST/admin/login"
LTOK=$(grep -oE 'name="_csrf" value="[a-f0-9]+"' /tmp/myn-e2e-login.html | head -1 | sed -E 's/.*value="([^"]+)".*/\1/')

# Wrong credentials
bad=$(curl -sc "$JAR_ADMIN" -b "$JAR_ADMIN" -o /dev/null -w '%{http_code}' -X POST \
    --data-urlencode "_csrf=$LTOK" \
    --data-urlencode "email=admin@makueniyouth.org" \
    --data-urlencode "password=wrongwrong" \
    "$HOST/admin/login")
check "login wrong creds returns 302 (back to /admin/login)" "302" "$bad"
still_anon=$(curl -s -b "$JAR_ADMIN" -o /dev/null -w '%{http_code}' "$HOST/admin")
check "still unauthenticated after wrong login" "302" "$still_anon"

# Fresh CSRF for actual login (since session may rotate)
curl -sc "$JAR_ADMIN" -b "$JAR_ADMIN" -o /tmp/myn-e2e-login.html "$HOST/admin/login"
LTOK=$(grep -oE 'name="_csrf" value="[a-f0-9]+"' /tmp/myn-e2e-login.html | head -1 | sed -E 's/.*value="([^"]+)".*/\1/')

ok_login=$(curl -sc "$JAR_ADMIN" -b "$JAR_ADMIN" -o /dev/null -w '%{http_code}' -X POST \
    --data-urlencode "_csrf=$LTOK" \
    --data-urlencode "email=admin@makueniyouth.org" \
    --data-urlencode "password=ChangeMe2026!" \
    "$HOST/admin/login")
check "login correct creds redirects" "302" "$ok_login"

dash_code=$(curl -s -b "$JAR_ADMIN" -o /dev/null -w '%{http_code}' "$HOST/admin")
check "GET /admin authenticated" "200" "$dash_code"
DASH=$(curl -s -b "$JAR_ADMIN" "$HOST/admin")
check_match "dashboard shows 'Published posts' KPI" "Published posts" "$DASH"
check_match "dashboard greets user by name"        "Site Admin"       "$DASH"

# Every admin index reachable
for r in /admin/posts /admin/programs /admin/events /admin/pages \
         /admin/stats /admin/partners /admin/settings \
         /admin/messages /admin/volunteers /admin/donations /admin/users; do
  check "GET $r" "200" "$(curl -s -b "$JAR_ADMIN" -o /dev/null -w '%{http_code}' "$HOST$r")"
done

# ---------------------------------------------------------------------------
# Admin CRUD lifecycle: create -> appear public -> draft -> hidden -> delete
# ---------------------------------------------------------------------------
section "ADMIN — post lifecycle (create / publish / draft / delete)"

curl -sc "$JAR_ADMIN" -b "$JAR_ADMIN" -o /tmp/myn-e2e-new.html "$HOST/admin/posts/create"
NTOK=$(grep -oE 'name="_csrf" value="[a-f0-9]+"' /tmp/myn-e2e-new.html | head -1 | sed -E 's/.*value="([^"]+)".*/\1/')
TS=$(date +%s)
SLUG="e2e-test-post-$TS"
curl -sc "$JAR_ADMIN" -b "$JAR_ADMIN" -o /dev/null -w '' -X POST \
    --data-urlencode "_csrf=$NTOK" \
    --data-urlencode "title=E2E Test Post $TS" \
    --data-urlencode "slug=$SLUG" \
    --data-urlencode "excerpt=A short excerpt for the E2E run." \
    --data-urlencode "body=<p>End-to-end created body content.</p>" \
    --data-urlencode "action=publish" \
    "$HOST/admin/posts"

NID=$($MYSQL -e "SELECT id FROM posts WHERE slug='$SLUG'")
[[ -n "$NID" ]] && check "post created in DB" "yes" "yes" || check "post created in DB" "yes" "no"

# Appears on public list
IMPACT_HTML=$(curl -s "$HOST/impact")
echo "$IMPACT_HTML" | grep -q "E2E Test Post $TS" && APPEARS="yes" || APPEARS="no"
check "published post appears on /impact" "yes" "$APPEARS"
# And the detail page
detail_code=$(curl -s -o /dev/null -w '%{http_code}' "$HOST/impact/$SLUG")
check "post detail /impact/$SLUG returns 200" "200" "$detail_code"

# Flip to draft
curl -sc "$JAR_ADMIN" -b "$JAR_ADMIN" -o /tmp/myn-e2e-edit.html "$HOST/admin/posts/$NID/edit"
ETOK=$(grep -oE 'name="_csrf" value="[a-f0-9]+"' /tmp/myn-e2e-edit.html | head -1 | sed -E 's/.*value="([^"]+)".*/\1/')
curl -sc "$JAR_ADMIN" -b "$JAR_ADMIN" -o /dev/null -X POST \
    --data-urlencode "_csrf=$ETOK" \
    --data-urlencode "title=E2E Test Post $TS" \
    --data-urlencode "body=<p>body</p>" \
    --data-urlencode "action=draft" \
    "$HOST/admin/posts/$NID"
IMPACT_HTML=$(curl -s "$HOST/impact")
echo "$IMPACT_HTML" | grep -q "E2E Test Post $TS" && STILL="yes" || STILL="no"
check "drafted post disappears from /impact" "no" "$STILL"
detail_code=$(curl -s -o /dev/null -w '%{http_code}' "$HOST/impact/$SLUG")
check "drafted post detail returns 404"      "404" "$detail_code"

# Delete
curl -sc "$JAR_ADMIN" -b "$JAR_ADMIN" -o /dev/null -X POST \
    --data-urlencode "_csrf=$ETOK" \
    "$HOST/admin/posts/$NID/delete"
LEFT=$($MYSQL -e "SELECT COUNT(*) FROM posts WHERE id=$NID")
check "post deleted from DB" "0" "$LEFT"

# ---------------------------------------------------------------------------
# Admin: settings change reflected in public footer
# ---------------------------------------------------------------------------
section "ADMIN — settings update propagates to public footer"

curl -sc "$JAR_ADMIN" -b "$JAR_ADMIN" -o /tmp/myn-e2e-s.html "$HOST/admin/settings"
STOK=$(grep -oE 'name="_csrf" value="[a-f0-9]+"' /tmp/myn-e2e-s.html | head -1 | sed -E 's/.*value="([^"]+)".*/\1/')
NEW_PHONE="+254 700 E2E TEST"
curl -sc "$JAR_ADMIN" -b "$JAR_ADMIN" -o /dev/null -X POST \
    --data-urlencode "_csrf=$STOK" \
    --data-urlencode "name=Makueni Youth Network" \
    --data-urlencode "tagline=Youth-owned. Youth-led. Youth-driven." \
    --data-urlencode "phone=$NEW_PHONE" \
    --data-urlencode "email=info@makueniyouth.org" \
    --data-urlencode "address=Famo House, 2nd Flr, Rm 14, Behind Equity Bank, Wote Town" \
    --data-urlencode "po_box=P.O Box 405 – 90300, Wote, Makueni" \
    --data-urlencode "facebook=https://facebook.com/MakueniYouthNetwork" \
    --data-urlencode "twitter=https://twitter.com/MakueniYouth" \
    --data-urlencode "linkedin=https://linkedin.com/company/makueni-youth-network" \
    "$HOST/admin/settings"

curl -s "$HOST/" | grep -q "$NEW_PHONE" && REFLECTED="yes" || REFLECTED="no"
check "new phone appears in public footer" "yes" "$REFLECTED"
$MYSQL -e "UPDATE settings SET setting_value='+254 710 580 604' WHERE setting_key='phone'" >/dev/null

# ---------------------------------------------------------------------------
# Admin: messages mark read + volunteers CSV export
# ---------------------------------------------------------------------------
section "ADMIN — inbox actions"

MID=$($MYSQL -e "SELECT id FROM messages WHERE email='e2e@myn.test'")
curl -sc "$JAR_ADMIN" -b "$JAR_ADMIN" -o /tmp/myn-e2e-m.html "$HOST/admin/messages"
MTOK=$(grep -oE 'name="_csrf" value="[a-f0-9]+"' /tmp/myn-e2e-m.html | head -1 | sed -E 's/.*value="([^"]+)".*/\1/')
curl -sc "$JAR_ADMIN" -b "$JAR_ADMIN" -o /dev/null -X POST \
    --data-urlencode "_csrf=$MTOK" \
    "$HOST/admin/messages/$MID/toggle"
READ=$($MYSQL -e "SELECT is_read FROM messages WHERE id=$MID")
check "message toggle marks read" "1" "$READ"

csv_ct=$(curl -s -b "$JAR_ADMIN" -o /tmp/myn-e2e.csv -w '%{content_type}' "$HOST/admin/volunteers/export.csv")
check_match "volunteers CSV content-type" "text/csv" "$csv_ct"
check_match "CSV contains E2E volunteer row" "vol@myn.test" "$(cat /tmp/myn-e2e.csv)"

# ---------------------------------------------------------------------------
# Editor role boundary
# ---------------------------------------------------------------------------
section "AUTH — editor role boundaries"

# Provision an editor user
EHASH=$($PHP -r 'echo password_hash("editor1234E2E", PASSWORD_DEFAULT);')
$MYSQL -e "INSERT INTO users (name,email,password_hash,role) VALUES ('E2E Editor','e2e-editor@myn.test','$EHASH','editor') ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash)" >/dev/null

curl -sc "$JAR_EDITOR" -b "$JAR_EDITOR" -o /tmp/myn-e2e-elogin.html "$HOST/admin/login"
ELT=$(grep -oE 'name="_csrf" value="[a-f0-9]+"' /tmp/myn-e2e-elogin.html | head -1 | sed -E 's/.*value="([^"]+)".*/\1/')
curl -sc "$JAR_EDITOR" -b "$JAR_EDITOR" -o /dev/null -X POST \
    --data-urlencode "_csrf=$ELT" \
    --data-urlencode "email=e2e-editor@myn.test" \
    --data-urlencode "password=editor1234E2E" \
    "$HOST/admin/login"

editor_dash=$(curl -s -b "$JAR_EDITOR" -o /dev/null -w '%{http_code}' "$HOST/admin")
check "editor can reach dashboard"          "200" "$editor_dash"
editor_users=$(curl -s -b "$JAR_EDITOR" -o /dev/null -w '%{http_code}' "$HOST/admin/users")
check "editor CANNOT reach /admin/users (403)" "403" "$editor_users"
editor_posts=$(curl -s -b "$JAR_EDITOR" -o /dev/null -w '%{http_code}' "$HOST/admin/posts")
check "editor CAN reach /admin/posts"       "200" "$editor_posts"

# Logout
curl -sc "$JAR_EDITOR" -b "$JAR_EDITOR" -o /tmp/myn-e2e-x.html "$HOST/admin"
XTOK=$(grep -oE 'name="_csrf" value="[a-f0-9]+"' /tmp/myn-e2e-x.html | head -1 | sed -E 's/.*value="([^"]+)".*/\1/')
curl -sc "$JAR_EDITOR" -b "$JAR_EDITOR" -o /dev/null -X POST \
    --data-urlencode "_csrf=$XTOK" \
    "$HOST/admin/logout"
after_logout=$(curl -s -b "$JAR_EDITOR" -o /dev/null -w '%{http_code}' "$HOST/admin")
check "after logout, /admin redirects to login" "302" "$after_logout"

$MYSQL -e "DELETE FROM users WHERE email='e2e-editor@myn.test'" >/dev/null

# ---------------------------------------------------------------------------
# Paystack webhook signature
# ---------------------------------------------------------------------------
section "PAYSTACK — webhook signature verification"

# Temporarily install a known test secret
SECRET_LINE=$(grep '^PAYSTACK_SECRET_KEY=' .env)
sed -i.bak 's|^PAYSTACK_SECRET_KEY=.*|PAYSTACK_SECRET_KEY=sk_test_e2e_secret|' .env
rm -f .env.bak
# Restart server so .env reloads
kill $SRV_PID 2>/dev/null
sleep 0.5
$PHP -S "localhost:$PORT" -t public server.php > /tmp/myn-e2e-srv.log 2>&1 &
SRV_PID=$!
sleep 1

BODY='{"event":"charge.success","data":{"reference":"E2E-UNKNOWN-REF","amount":1,"currency":"KES","status":"success"}}'
GOOD_SIG=$($PHP -r "echo hash_hmac('sha512', '$BODY', 'sk_test_e2e_secret');")

w1=$(curl -s -o /dev/null -w '%{http_code}' -X POST -H 'Content-Type: application/json' --data-binary "$BODY" "$HOST/donate/webhook")
check "webhook NO signature -> 400"   "400" "$w1"
w2=$(curl -s -o /dev/null -w '%{http_code}' -X POST -H 'Content-Type: application/json' -H 'X-Paystack-Signature: deadbeef' --data-binary "$BODY" "$HOST/donate/webhook")
check "webhook BAD signature -> 400"  "400" "$w2"
w3=$(curl -s -o /dev/null -w '%{http_code}' -X POST -H 'Content-Type: application/json' -H "X-Paystack-Signature: $GOOD_SIG" --data-binary "$BODY" "$HOST/donate/webhook")
check "webhook valid sig (unknown ref) -> 200" "200" "$w3"

# Restore .env
sed -i.bak "s|^PAYSTACK_SECRET_KEY=.*|$SECRET_LINE|" .env
rm -f .env.bak

# ---------------------------------------------------------------------------
# Performance + safety
# ---------------------------------------------------------------------------
section "PERFORMANCE + SAFETY"

HTML_SIZE=$(curl -s -o /dev/null -w '%{size_download}' "$HOST/")
CSS_SIZE=$(curl -s -o /dev/null -w '%{size_download}' "$HOST/assets/css/style.css")
JS_SIZE=$(curl -s -o /dev/null -w '%{size_download}' "$HOST/assets/js/main.js")
TOTAL=$((HTML_SIZE + CSS_SIZE + JS_SIZE))
echo "  homepage transfer: HTML=$HTML_SIZE  CSS=$CSS_SIZE  JS=$JS_SIZE  TOTAL=$TOTAL bytes"
if (( TOTAL < 300000 )); then
  printf "  \033[32mPASS\033[0m  homepage under 300 KB target\n"
  PASS=$((PASS+1))
else
  printf "  \033[31mFAIL\033[0m  homepage exceeds 300 KB target (%s)\n" "$TOTAL"
  FAIL=$((FAIL+1)); FAILS+=("homepage size")
fi

# Cleanup: drop the E2E contact/volunteer rows so the DB stays tidy
$MYSQL -e "DELETE FROM messages WHERE email='e2e@myn.test'" >/dev/null
$MYSQL -e "DELETE FROM volunteers WHERE email='vol@myn.test'" >/dev/null

# Error log should not have grown (apart from benign Mailer 'SMTP not configured')
LOG_ERRS=$(grep -v 'SMTP not configured' storage/logs/app.log | grep -v '^$' | wc -l | tr -d ' ')
check "storage/logs/app.log clean" "0" "$LOG_ERRS"
if [[ "$LOG_ERRS" != "0" ]]; then
  echo "    -- log content --"
  grep -v 'SMTP not configured' storage/logs/app.log | head
fi

# ---------------------------------------------------------------------------
# PHPUnit
# ---------------------------------------------------------------------------
section "PHPUNIT"
$PHP vendor/bin/phpunit 2>&1 | tail -8 | sed 's/^/  /'

# ---------------------------------------------------------------------------
# Summary
# ---------------------------------------------------------------------------
section "SUMMARY"
TOTAL_CHECKS=$((PASS+FAIL))
printf "  %d / %d checks passed\n" "$PASS" "$TOTAL_CHECKS"
if (( FAIL > 0 )); then
  printf "  \033[31mFAILED:\033[0m\n"
  for f in "${FAILS[@]}"; do printf "    - %s\n" "$f"; done
  exit 1
fi
printf "  \033[32mE2E PASSED\033[0m\n"
exit 0
