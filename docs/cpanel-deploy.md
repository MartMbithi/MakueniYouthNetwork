# Ship to cPanel — deployment guide

A complete, opinionated walkthrough for deploying the Makueni Youth Network
CMS to a cPanel shared host (the kind sold by Truehost, HostPinnacle,
Sasahost, Safaricom Cloud, BlueHost, Namecheap, Hostinger, etc.). End-to-end
time: **45–60 minutes** the first time, ~10 minutes for updates.

---

## 0 · Pre-flight checklist

Before you upload anything, confirm your cPanel account has:

| What                                | How to check on cPanel              | Required version       |
|-------------------------------------|-------------------------------------|------------------------|
| **PHP**                             | Software → MultiPHP Manager         | **8.2 or higher**      |
| **MySQL / MariaDB**                 | Databases → MySQL Databases         | MySQL 8 / MariaDB 10.4+|
| **Apache modules**                  | Already installed on every cPanel host | `mod_rewrite`, `mod_headers`, `mod_deflate`, `mod_expires`, `mod_alias` |
| **SSL (AutoSSL / Let's Encrypt)**   | Security → SSL/TLS Status            | enabled for your domain |
| **Email**                           | Email → Email Accounts                | `info@yourdomain` mailbox |
| **(Optional) SSH access**           | Advanced → SSH Access                | jailed shell is fine    |
| **(Optional) Cron Jobs**            | Advanced → Cron Jobs                 | for nightly backups     |

PHP extensions the app needs (these are on by default on virtually every
cPanel host — Software → Select PHP Version → Extensions to verify):

```
pdo_mysql  gd  mbstring  curl  openssl  fileinfo  intl  libxml  dom  json  session  hash  zip
```

If `gd` is missing the image uploader still works — it falls back to JPEG.

php.ini values to bump if they're below these (Software → Select PHP Version
→ Options):

```
memory_limit          = 128M
upload_max_filesize   = 8M
post_max_size         = 10M
max_execution_time    = 60
allow_url_fopen       = On      # needed once, by the WordPress importer
```

---

## 1 · Build the deployment artifact (on your laptop)

The repo ships with a one-shot builder:

```bash
scripts/build-deploy.sh
```

It runs `composer install --no-dev --optimize-autoloader`, then packs
everything into `dist/myn-cpanel-YYYYMMDD-HHMMSS.tar.gz`. The tarball
excludes:

- `.git`, IDE folders, `.DS_Store`
- `.env` (you'll create that on the server)
- `tests/`, `.phpunit.cache/`, `*.log`
- `storage/logs/*` and `public/uploads/*` content (the directories stay,
  ready to receive runtime files)
- `composer.phar` (you don't need it on the server)

You should end up with a file around **3 – 4 MB** (vendor included). That's
the upload.

---

## 2 · Pick your layout (this is the most important decision)

cPanel hosts a single document root per domain — usually `~/public_html/`.
The app's architecture requires that **only `public/` is browser-reachable**.
Two ways to satisfy that:

### Layout A — recommended (works on every cPanel host, no SSH needed)

Keep the document root pointed at `~/public_html/` and put **the contents of
`public/` directly into `public_html/`**, with the rest of the project one
level up in `~/myn/`. Final tree on the server:

```
/home/<user>/
├── myn/                       ← project root (NOT browser-reachable)
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── routes/
│   ├── storage/
│   ├── templates/
│   ├── vendor/
│   ├── server.php
│   └── .env                   ← lives here, never inside public_html/
└── public_html/               ← Apache DocumentRoot (browser-reachable)
    ├── index.php              ← edited to require '../myn/...'
    ├── .htaccess
    ├── assets/
    ├── uploads/
    ├── favicon.png
    ├── apple-touch-icon.png
    └── robots.txt
```

This is the layout the rest of this guide assumes.

### Layout B — for the brave (Apache DocumentRoot points at a subdir)

If your host's cPanel exposes "Domain Document Root" (most do, via Domains →
your domain → Edit), you can point your domain straight at `~/myn/public/`
and leave `public_html/` alone. Cleaner, no `index.php` edit needed.

If you go with Layout B, skip section 3.2 below and jump straight to
section 4.

---

## 3 · Upload & extract (Layout A)

### 3.1 · Upload the tarball

cPanel → **File Manager** → navigate to `~/` (your home directory) → click
**Upload** → pick `myn-cpanel-*.tar.gz` from your laptop → wait for the green
bar.

Back in File Manager, right-click the tarball → **Extract** → set the path
to `/home/<user>/myn-extract/` → Extract. (Some cPanel installs auto-create
the folder if it doesn't exist; if not, create it first.)

Rename / move the extracted folder to `~/myn/`:

```
File Manager → select myn-extract → Rename → myn
```

If you have SSH, the same thing in one line:

```bash
mkdir -p ~/myn && tar -xzf ~/myn-cpanel-*.tar.gz -C ~/myn
```

### 3.2 · Split `public/` into `public_html/`

Move every file under `~/myn/public/` into `~/public_html/`:

| With SSH  | Without SSH (File Manager) |
|---|---|
| `rsync -av ~/myn/public/ ~/public_html/`  then  `rm -rf ~/myn/public/` | Open `~/myn/public/` in the right pane and `~/public_html/` in the left, select all in the right pane, drag into the left. Then delete the now-empty `~/myn/public/` folder. |

Open `~/public_html/index.php` in the cPanel editor and change **only the
`$rootDir` line** so it points up to the project root:

```php
// Before (when public/ was a child of the project root):
$rootDir = dirname(__DIR__);

// After (Layout A — index.php now lives in public_html/, project is in ../myn):
$rootDir = dirname(__DIR__) . '/myn';
```

Open `~/public_html/server.php` and apply the same one-line change — but in
production this file is only a dev-server router, so you can also just delete
it.

### 3.3 · Set permissions

In File Manager → select these directories → **Change Permissions**:

| Path                 | Mode |
|----------------------|------|
| Everything else      | **755 (folders) / 644 (files)** — File Manager defaults are usually correct |
| `~/public_html/uploads/` | **775** (web server needs to write) |
| `~/myn/storage/logs/`     | **775** |

With SSH:

```bash
find ~/myn -type d -exec chmod 755 {} \;
find ~/myn -type f -exec chmod 644 {} \;
chmod -R 775 ~/myn/storage/logs ~/public_html/uploads
```

### 3.4 · Lock down the project root

Drop a `~/myn/.htaccess` that denies all access (so even if the web server
ever wanders here it serves a 403):

```apache
Require all denied
```

In File Manager → Settings → tick **Show Hidden Files** to see/create dotfiles.

---

## 4 · Create the database

cPanel → **MySQL Databases**.

1. **Create New Database**: `myn_db` → Create. cPanel will prefix it with
   your username, so the actual name becomes something like `acme_myn_db`.
   Write that full name down.
2. **MySQL Users → Add New User**: `myn_user`, generate a strong password
   (use the "Password Generator" button). Copy the password into your
   password manager.
3. **Add User To Database**: pick `acme_myn_user` + `acme_myn_db` → grant
   **ALL PRIVILEGES**.

Now import the schema + seed.

cPanel → **phpMyAdmin** → select `acme_myn_db` from the left → **Import** tab
→ Choose File → `database/schema.sql` from your local checkout (or upload it
first via File Manager) → Go.

Repeat for `database/seed.sql`. After import, run a quick check in
phpMyAdmin's **SQL** tab:

```sql
SHOW TABLES;
SELECT COUNT(*) FROM users;       -- should be 1
SELECT COUNT(*) FROM programs;    -- should be 5
```

---

## 5 · Create the `.env` file

In File Manager open `~/myn/`, click **+ File** → name it `.env`. Edit and
paste:

```dotenv
APP_ENV=production
APP_URL=https://makueniyouth.org
APP_KEY=                                  # see step 5.1

DB_HOST=localhost
DB_NAME=acme_myn_db                       # the full prefixed name from step 4
DB_USER=acme_myn_user
DB_PASS=the-password-you-generated

MAIL_HOST=smtp.makueniyouth.org           # use mail.<your-domain> on most cPanel hosts
MAIL_PORT=587
MAIL_USER=info@makueniyouth.org           # the cPanel mailbox you created
MAIL_PASS=the-mailbox-password
MAIL_FROM=info@makueniyouth.org

PAYSTACK_ENV=live
PAYSTACK_PUBLIC_KEY=pk_live_...
PAYSTACK_SECRET_KEY=sk_live_...
PAYSTACK_CALLBACK_URL=https://makueniyouth.org/donate/callback
PAYSTACK_CURRENCY=KES
```

### 5.1 · Generate `APP_KEY`

`APP_KEY` is used by `SpamGuard` to HMAC-sign form timestamps. Generate a
strong one — either run this locally and paste the result in:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

…or use any 64-character hex string. Don't leave it blank in production —
it falls back to the session id, which rotates and would invalidate any
form a user is still filling in.

---

## 6 · Pick the right PHP version for the domain

cPanel → **MultiPHP Manager** → tick your domain → set Version to
**ea-php82** (or newer) → Apply.

Then **Software → Select PHP Version** → Options → confirm
`memory_limit=128M`, `upload_max_filesize=8M`, `post_max_size=10M`. Click
Save.

---

## 7 · Turn on HTTPS

cPanel → **SSL/TLS Status** → tick your domain → **Run AutoSSL**. Wait 1–2
minutes, refresh, status should show "AutoSSL Domains Validated".

The bundled `public/.htaccess` already force-redirects HTTP → HTTPS for every
host except `localhost` / `127.0.0.1`, so once the certificate is live you
don't have to change anything.

Smoke test:

```bash
curl -I http://makueniyouth.org/         # should redirect 301 → https
curl -I https://makueniyouth.org/        # 200, Strict-Transport-Security header
```

---

## 8 · First admin login & password rotation

Open `https://makueniyouth.org/admin/login`. Sign in with:

| | |
|---|---|
| Email    | `admin@makueniyouth.org` |
| Password | `ChangeMe2026!` |

**Immediately**: sidebar → **Users** → click your account → enter a new
strong password → Save. The seeded plaintext is in the README so a fresh
install can get in; it must not survive past first login.

While you're there: sidebar → **Settings** → confirm the phone, email,
address, social links, and the logo URL — these all flow through the public
header / footer / share previews.

---

## 9 · Pull the legacy WordPress content (optional, one-shot)

If you're replacing a live WordPress site that's still up at the same
hostname, you can migrate every post + page + image into the new CMS
**before** flipping DNS:

### With SSH

```bash
cd ~/myn
php database/import-wordpress.php --dry-run                    # preview
php database/import-wordpress.php                              # for real
php scripts/post-import-cleanup.php                            # tidy up junk
```

### Without SSH

cPanel → **Cron Jobs** → Add a **one-off** cron job (Common Settings = "Once
a year", you'll delete it after it runs):

```
/usr/local/bin/php /home/<user>/myn/database/import-wordpress.php
```

Wait for the job to fire, check the inbox for the cron-output email to
confirm, then delete the job. Repeat with `post-import-cleanup.php`.

The importer is **idempotent** — re-running it skips slugs already present,
so you can run it as many times as you like without duplicating.

---

## 10 · Smoke-test the deploy

From your laptop, run a quick exercise against the live host:

```bash
HOST=https://makueniyouth.org

curl -s -o /dev/null -w '%{http_code}\n' $HOST/                            # 200
curl -s -o /dev/null -w '%{http_code}\n' $HOST/programs                    # 200
curl -s -o /dev/null -w '%{http_code}\n' $HOST/impact                      # 200
curl -s -o /dev/null -w '%{http_code}\n' $HOST/sitemap.xml                 # 200
curl -s -o /dev/null -w '%{http_code}\n' $HOST/admin                       # 302 -> /admin/login
curl -s -o /dev/null -w '%{http_code}\n' $HOST/no-such-page                # 404
curl -I $HOST/.env                                                         # 403 (must NEVER be 200)
curl -I $HOST/storage/logs/app.log                                         # 403 / 404 (NEVER 200)

# CSRF must reject
curl -s -o /dev/null -w '%{http_code}\n' -X POST -d 'name=x&email=x@x.com&message=hello' $HOST/contact   # 419
```

Check Google's structured-data validator on a post URL:
https://validator.schema.org/#url=https%3A%2F%2Fmakueniyouth.org%2Fimpact%2F...

It should report **Organization** + **Article** schemas with no errors.

---

## 11 · Nightly backups (cPanel Cron Jobs)

cPanel → **Cron Jobs** → Common Settings = "Once Per Day (0 0 * * *)" →
Command:

```bash
mkdir -p /home/<user>/backups && \
mysqldump --single-transaction -u acme_myn_user -p'the-password' acme_myn_db \
  | gzip > /home/<user>/backups/myn-$(date +\%F).sql.gz && \
tar -czf /home/<user>/backups/uploads-$(date +\%F).tar.gz -C /home/<user>/public_html uploads && \
find /home/<user>/backups -mtime +30 -delete
```

(Single-line cron commands; the `\%` escapes `%` because cron treats `%` as
a newline.) This keeps the last 30 days of dumps in `~/backups/`. Combine
with cPanel's built-in **Backup Wizard** for off-server copies.

---

## 12 · Updating to a new version (the loop)

On your laptop:

```bash
git pull origin main
scripts/build-deploy.sh
```

In cPanel:

1. **File Manager** → upload the new tarball.
2. Rename the existing `~/myn/` → `~/myn-old-YYYYMMDD/` (keeps a rollback).
3. Extract the new tarball to `~/myn/`.
4. Run `mv ~/myn/public/* ~/public_html/` (or rsync if you have SSH).
5. Restore your `~/myn/.env` (it wasn't in the tarball — copy from the
   old folder).
6. (Only if a release ships migrations) Import the new SQL via phpMyAdmin.

Roll back is just: rename `~/myn-old-*` back to `~/myn/`.

---

## 13 · Troubleshooting

| Symptom                                | What to check                                                                                          |
|----------------------------------------|--------------------------------------------------------------------------------------------------------|
| **HTTP 500 everywhere**                | Open `~/myn/storage/logs/app.log` in File Manager — the most recent line says exactly what blew up. Most common cause: `DB_*` in `.env` doesn't match what you created in step 4. |
| **White screen, no log line**          | PHP version is below 8.2. Step 6. |
| **"Class App\Core\View not found"**    | `composer install` was not run, OR you uploaded the source-only tarball. Re-run `scripts/build-deploy.sh` locally so `vendor/` ends up in the tarball. |
| **Logo / CSS doesn't load**            | Browser DevTools → Network → 404s on `/assets/css/style.css`. Means step 3.2 didn't move the `public/` contents into `public_html/`. |
| **Contact form silently bounces**      | The new SpamGuard rejects sub-2-second submissions. If you're testing with copy-paste-and-submit immediately, you're a "bot" by its rules — wait a few seconds. |
| **Paystack webhook returns 400**       | Your `PAYSTACK_SECRET_KEY` doesn't match the one set in the Paystack dashboard. They MUST be byte-identical. |
| **Mail not delivering**                | cPanel mailboxes usually need `MAIL_HOST=mail.<your-domain>` and `MAIL_PORT=587` (STARTTLS) or `465` (SMTPS). Try the cPanel "Email → Configure Mail Client" page for the exact values. |
| **`/uploads/`-rooted images 403**      | File permissions: `chmod 755 ~/public_html/uploads/` and 644 on its files. |
| **"Document root not browseable"**     | You're on Layout A but `index.php` still tries to load `../vendor/autoload.php`. Edit the `$rootDir` line per step 3.2. |

To temporarily see PHP errors in the browser (don't leave this on):

```dotenv
APP_ENV=local
```

Save `.env` → reload → fix the issue → set `APP_ENV=production` → reload.

---

## 14 · Security checklist for go-live

Tick every box before you announce the URL:

- [ ] Admin password rotated away from `ChangeMe2026!`
- [ ] `APP_KEY` is a unique 64-char hex string (step 5.1)
- [ ] `APP_ENV=production` (not `local`)
- [ ] Paystack keys are **live** (`pk_live_...`, `sk_live_...`), not test
- [ ] `https://makueniyouth.org/.env` returns **403** (or 404), never the
      file contents
- [ ] `https://makueniyouth.org/storage/logs/app.log` is unreachable
- [ ] AutoSSL is green, `curl -I http://...` redirects to HTTPS
- [ ] `~/myn/.htaccess` contains `Require all denied`
- [ ] cPanel two-factor authentication is on for your cPanel login
- [ ] Nightly backup cron is firing (check `~/backups/` after 24 h)

---

## Appendix · Quick-reference layout

After a successful Layout A deploy, your server tree is:

```
/home/<cpanel-user>/
├── backups/                       # nightly DB + uploads dumps land here
├── myn/                           # project root, NOT browser-reachable
│   ├── .env                       # secrets live here
│   ├── .htaccess                  # "Require all denied"
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── docs/
│   ├── routes/
│   ├── scripts/
│   ├── storage/logs/              # writable, app.log lands here
│   ├── templates/
│   ├── vendor/
│   ├── composer.json
│   └── README.md
└── public_html/                   # Apache DocumentRoot
    ├── .htaccess                  # redirects, CSP, gzip, expires, 301 map
    ├── apple-touch-icon.png
    ├── assets/
    │   ├── css/style.css
    │   ├── css/admin.css
    │   ├── img/logo.png
    │   ├── img/logo-square.png
    │   └── js/main.js
    ├── favicon.png
    ├── index.php                  # one-line edited per step 3.2
    ├── robots.txt
    └── uploads/                   # writable, runtime media lands here
```

That is the entire surface area. If you can map your cPanel account against
that tree, you can ship.
