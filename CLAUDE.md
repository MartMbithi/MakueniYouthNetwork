# CLAUDE.md — Makueni Youth Network CMS

> This file is auto-loaded by Claude Code. It is the source of truth for how this
> project is built. Read it fully before writing any code. Then work through
> `BUILD-PLAN.md` milestone by milestone, in order.

---

## 1. What we are building

A custom, lightweight Content Management System in PHP for **Makueni Youth Network**
(makueniyouth.org) — a youth-led CBO in Wote, Makueni County, Kenya. It replaces a
WordPress site. It must be fast on mobile data, cheap to host, and editable by
non-technical staff.

The public homepage design already exists as `design/homepage-template.html`. That
file is the **visual source of truth** — extract its CSS, fonts, colours and layout
into the real project. Do not redesign it.

## 2. Tech stack — use exactly these, nothing more

| Layer | Choice |
|---|---|
| Language | PHP 8.2+ (use typed properties, enums, `match`) |
| Database | MySQL 8 / MariaDB 10.6+ via **PDO only** |
| Templating | Twig (`twig/twig`) |
| Routing | Hand-rolled `App\Core\Router` (no framework) |
| Env | `vlucas/phpdotenv` |
| Email | `phpmailer/phpmailer` over SMTP |
| Payments | **Paystack** — Initialize + Verify + Webhook (no SDK; raw cURL) |
| Frontend | Plain CSS + vanilla JS. **No build step. No npm.** |
| Dependencies | Composer only |

**Do NOT** introduce Laravel, Symfony components beyond Twig, an ORM, a JS framework,
a CSS framework, or any package not listed above. If a task seems to need one, stop
and ask.

## 3. Architecture — non-negotiable rules

1. **Front controller.** Every request enters through `public/index.php`. Nothing else
   in `public/` is a PHP entry point except uploaded assets.
2. **Web root is `public/`.** All app code, templates, config and `.env` live ABOVE
   the web root and must never be browser-reachable.
3. **MVC-ish.** Controllers are thin. Models hold SQL. Twig holds markup. No SQL in
   controllers, no business logic in templates.
4. **One way to do a thing.** Reuse `Database`, `View`, `Auth`, `Csrf` — do not
   re-implement these per controller.

## 4. Directory structure (create exactly this)

```
makueniyouth/
├── public/
│   ├── index.php          # front controller — the ONLY entry point
│   ├── .htaccess          # rewrites everything → index.php
│   ├── assets/{css,js,img}/
│   └── uploads/           # writable; user media
├── app/
│   ├── Core/              # Router, Database, Request, Response, View, Auth, Csrf
│   ├── Controllers/       # public controllers
│   ├── Controllers/Admin/ # CMS controllers
│   ├── Models/            # one class per table; thin PDO wrappers
│   └── Services/          # Paystack, Mailer, ImageProcessor
├── templates/
│   ├── layouts/           # base.twig, admin.twig
│   ├── partials/          # header, footer, nav, flash
│   ├── public/            # home, program, post, event, contact, donate...
│   └── admin/             # dashboard + CRUD views
├── routes/web.php         # public routes
├── routes/admin.php       # /admin routes (auth-guarded)
├── config/config.php      # reads .env, returns config array
├── database/
│   ├── schema.sql
│   └── seed.sql
├── storage/logs/
├── design/homepage-template.html   # provided — visual reference
├── .env.example
├── .env                   # gitignored
├── .gitignore
├── composer.json
└── README.md
```

## 5. Coding conventions

- **PSR-12** formatting. `declare(strict_types=1);` at the top of every PHP file.
- **Mandatory letterhead.** Every PHP source file must carry the MBITHI
  letterhead banner from `docs/letterhead-banner.txt`, inserted **after**
  `declare(strict_types=1);` and **before** the namespace declaration. Do not
  alter the banner text. Update the `Conjured Upon This Day, …` date only when
  the file is freshly created — once a file has a banner, leave the date alone.
- **Namespaces:** `App\Core`, `App\Controllers`, `App\Models`, `App\Services`.
  PSR-4 autoload via Composer: `"App\\": "app/"`.
- **Database access:** `PDO` with **prepared statements only**. Never concatenate
  user input into SQL. `PDO::ERRMODE_EXCEPTION` on.
- **Output:** all HTML rendered through Twig with autoescaping ON. Never `echo`
  user data directly.
- **Naming:** Controllers `PascalCaseController`, methods `camelCase`,
  DB tables/columns `snake_case`, routes/slugs `kebab-case`.
- **Errors:** log to `storage/logs/app.log`; show a friendly 404/500 page in
  production; show detail only when `APP_ENV=local`.
- **No secrets in code.** Everything sensitive comes from `.env`.

## 6. Security rules — apply to every task that touches them

- All SQL → prepared statements.
- Every form (public + admin) → CSRF token via `App\Core\Csrf`.
- Admin passwords → `password_hash()` / `password_verify()` only.
- File uploads → whitelist `jpg,jpeg,png,webp,pdf`; verify MIME; rename to a
  random name; store in `public/uploads/`; never trust the original filename.
- Sessions → cookie flags `HttpOnly`, `Secure`, `SameSite=Lax`.
- Rate-limit login + public forms (simple per-IP counter in DB or session).
- Paystack webhook → verify the `X-Paystack-Signature` HMAC (SHA-512 of the raw
  request body using `PAYSTACK_SECRET_KEY`) **and** re-verify the transaction
  via the Paystack `/transaction/verify/{reference}` endpoint before marking a
  donation `completed`. Never trust webhook amounts without re-verification.
- `.env`, `storage/`, `database/` must be in `.gitignore` (keep `.env.example`).

## 7. Commands

```bash
composer install                       # install deps
cp .env.example .env                    # then fill DB + M-Pesa creds
mysql -u root -p myn < database/schema.sql
mysql -u root -p myn < database/seed.sql
php -S localhost:8000 -t public         # local dev server
composer dump-autoload                  # after adding classes
vendor/bin/phpunit                      # tests (if/when added)
```

The site must run with nothing more than `php -S localhost:8000 -t public`.

## 8. Definition of done (every task)

A task is complete only when:
1. The code follows sections 3, 5 and 6 above.
2. The acceptance criteria in `BUILD-PLAN.md` for that task pass.
3. The dev server still boots and the affected page/route works in a browser.
4. No PHP warnings or notices in `storage/logs/app.log`.

## 9. How to proceed

Work through `BUILD-PLAN.md` strictly in milestone order (M0 → M7). Within a
milestone, do tasks in numbered order — later tasks depend on earlier ones.
After each milestone, stop and report what was built and how it was verified
before starting the next. Do not skip ahead. If a spec is ambiguous, ask rather
than guess.
