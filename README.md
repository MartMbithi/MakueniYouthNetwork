# Makueni Youth Network — CMS

A custom, lightweight PHP CMS for [makueniyouth.org](https://makueniyouth.org) —
a youth-led CBO in Wote, Makueni County, Kenya.

Built to be fast on mobile data, cheap to host, and editable by non-technical staff.

## Stack

- PHP 8.2+ (strict types, typed properties, enums)
- MySQL 8 / MariaDB 10.6+ via PDO (verified working on MariaDB 10.4.28)
- [Twig](https://twig.symfony.com/) templating
- Hand-rolled `App\Core\Router` — no framework
- [phpdotenv](https://github.com/vlucas/phpdotenv) for environment config
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) over SMTP
- Paystack (Initialize + Verify + Webhook) via raw cURL
- Plain CSS + vanilla JS — no build step, no npm

## Local setup

```bash
composer install
cp .env.example .env
# fill in DB credentials, SMTP, and Paystack test keys
mysql -u root -p myn < database/schema.sql
mysql -u root -p myn < database/seed.sql
php -S localhost:8000 -t public server.php
```

Browse to <http://localhost:8000/>.

### First admin login

After running `seed.sql`:

| Field | Value |
|---|---|
| URL | <http://localhost:8000/admin/login> |
| Email | `admin@makueniyouth.org` |
| Password | `ChangeMe2026!` |

**Change the password immediately after first sign-in.** The plaintext above
exists only so a fresh deployment can get into the admin panel; a password
rotation flow ships with M4.7.

### Running the tests

```bash
# in one terminal, start the dev server
php -S localhost:8765 -t public server.php

# in another
vendor/bin/phpunit
```

The PHPUnit smoke suite covers every public route, the admin auth guard, the
CSRF reject path, the contact-form insert path, and the Paystack webhook
signature check.

## Deployment

This project ships as a normal PHP application — no build step, no node, no
container required.

1. **Provision** — PHP 8.2+, MariaDB 10.4+/MySQL 8+, Apache with `mod_rewrite`,
   `mod_headers`, `mod_deflate`, `mod_expires`. Optional: `libwebp` so
   `ImageProcessor` writes WebP instead of JPEG.
2. **Upload** the repo to the server (rsync, git clone, whatever you prefer).
   Everything outside `public/` MUST live above the web root.
3. **Composer install** without dev deps:

   ```bash
   composer install --no-dev --optimize-autoloader
   ```

4. **Apache VirtualHost** — point `DocumentRoot` at the project's `public/`
   directory and allow `.htaccess` overrides:

   ```apache
   <VirtualHost *:443>
       ServerName makueniyouth.org
       DocumentRoot /srv/myn/public
       <Directory /srv/myn/public>
           AllowOverride All
           Require all granted
       </Directory>
       SSLEngine on
       SSLCertificateFile      /etc/letsencrypt/live/.../fullchain.pem
       SSLCertificateKeyFile   /etc/letsencrypt/live/.../privkey.pem
   </VirtualHost>
   ```

5. **`.env`** on the server should contain real values:
   - `APP_ENV=production`
   - `APP_URL=https://makueniyouth.org`
   - `DB_*` for the production database
   - `MAIL_*` for the SMTP provider (Brevo/Mailgun/SES)
   - `PAYSTACK_*` for the **live** keys from <https://dashboard.paystack.com/>
6. **Database** — apply schema, then seed (only on a fresh install):

   ```bash
   mysql -u myn -p myn < database/schema.sql
   mysql -u myn -p myn < database/seed.sql
   ```

7. **WordPress import** — once, after seeding:

   ```bash
   php database/import-wordpress.php --dry-run    # preview
   php database/import-wordpress.php              # for real
   ```

   The script is idempotent — re-running it adds no duplicates.

8. **Permissions** — make `storage/logs/` and `public/uploads/` writable by
   the web user:

   ```bash
   sudo chown -R www-data:www-data storage/logs public/uploads
   sudo chmod -R u+w               storage/logs public/uploads
   ```

9. **Cache opcode** — set `opcache.validate_timestamps=0` in production
   `php.ini` and run `php artisan-style: composer dump-autoload` after every
   deploy.

## Backups

| What | How | When |
|------|------|------|
| Database | `mysqldump --single-transaction myn > backups/myn-$(date +%F).sql` | daily |
| Uploads  | `rsync -a public/uploads/ backups/uploads/` | daily |
| `.env`   | encrypted offline copy (it has Paystack secrets) | when keys change |

The `database/` and `storage/` directories must never be browser-reachable —
keep them outside the document root. The `.gitignore` excludes `.env`,
`storage/logs/*`, and `public/uploads/*` from the repo.

## Security checklist (M7.2)

| Requirement | Satisfied by |
|---|---|
| All SQL via prepared statements | every model — e.g. `app/Models/Post.php:39` `Post::findBySlug`, all `prepare()` + `execute([:param => …])` |
| CSRF token on every form | `app/Core/Csrf.php:18` `Csrf::token()` + `:51` `requireValid()`; `{{ csrf_field()|raw }}` registered in `app/Core/View.php:171` |
| Bcrypt password hashing | `app/Core/Auth.php:24` `password_verify`; `app/Models/User.php:52,79,85` `password_hash(PASSWORD_DEFAULT)` |
| Upload validation (whitelist + MIME) | `app/Services/ImageProcessor.php:14` `ALLOWED_EXT`, `:17` `ALLOWED_MIME`, `:75` random rename, `:99` JPEG/WebP fallback |
| Secure session cookies (HttpOnly / Secure / SameSite=Lax) | `public/index.php:178` `session_set_cookie_params` |
| Login rate limit | `app/Controllers/Admin/AuthController.php:30` `RateLimit::attempt('login:'.IP, 5, 300)` |
| Public form rate limit | `app/Controllers/ContactController.php:33`, `app/Controllers/VolunteerController.php:33`, `app/Controllers/DonationController.php:31` |
| HTTPS redirect rule | `public/.htaccess:13` `RewriteRule ^ https://...` (skipped for localhost/127.0.0.1) |
| Security headers | `public/.htaccess:25` `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`; HSTS only over HTTPS |
| Paystack webhook signature verification | `app/Services/Paystack.php:92` `verifyWebhookSignature` (HMAC-SHA512 + `hash_equals`); `app/Controllers/DonationController.php:120` server-side re-verify via `Paystack::verify` after signature passes |
| Self-delete prevention (users) | `app/Controllers/Admin/UserController.php:95` |
| Editor-only routes 403 for non-admins | `app/Controllers/Admin/UserController.php:18` `Auth::requireRole('admin')` |
| Dotfile + admin path blocks | `public/.htaccess:101` `.` block, `:60` `wp-admin` 403, `:61` `wp-login` 403, `:62` `xmlrpc` 403 |
| `.env` / `storage/` / `database/` not browser-reachable | architecture: only `public/` is `DocumentRoot`; `.gitignore` excludes secrets |

## Project layout

See `CLAUDE.md` §4 — every directory has a defined purpose. Public web root is
`public/` only; everything else lives above it.

## Build plan

`BUILD-PLAN.md` lists every milestone (M0 → M7) and its acceptance criteria.
`docs/admin-guide.md` is the staff-facing how-to.
