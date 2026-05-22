# BUILD-PLAN.md — Makueni Youth Network CMS

> Execute milestones **M0 → M7 in order**. Within a milestone do tasks in number
> order. Each task lists: **Files**, **Spec**, **Acceptance**. A task is done only
> when its Acceptance checks pass and the dev server still boots.
> After each milestone: stop, summarise what was built and how it was verified.

Tracking: mark each task `[x]` when its acceptance passes.

---

## MILESTONE M0 — Repository scaffold

**Goal:** an empty but bootable skeleton.

### [ ] M0.1 — Initialise repo & Composer
- **Files:** `composer.json`, `.gitignore`, `README.md`
- **Spec:** `composer.json` with PSR-4 autoload `"App\\": "app/"`, PHP `>=8.2`,
  and require: `twig/twig`, `vlucas/phpdotenv`, `phpmailer/phpmailer`.
  `.gitignore` must exclude `/vendor`, `/.env`, `/storage/logs/*`,
  `/public/uploads/*`, keeping `.gitkeep` files.
- **Acceptance:** `composer install` succeeds; `vendor/` exists.

### [ ] M0.2 — Directory tree
- **Spec:** create the full tree from `CLAUDE.md` §4. Add `.gitkeep` to empty
  dirs (`storage/logs`, `public/uploads`). Copy the provided
  `homepage-template.html` into `design/`.
- **Acceptance:** tree matches `CLAUDE.md` §4 exactly.

### [ ] M0.3 — Environment config
- **Files:** `.env.example`, `config/config.php`
- **Spec:** `.env.example` keys — `APP_ENV`, `APP_URL`, `APP_KEY`,
  `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`,
  `MAIL_HOST`, `MAIL_PORT`, `MAIL_USER`, `MAIL_PASS`, `MAIL_FROM`,
  `PAYSTACK_ENV`, `PAYSTACK_PUBLIC_KEY`, `PAYSTACK_SECRET_KEY`,
  `PAYSTACK_CALLBACK_URL`, `PAYSTACK_CURRENCY`.
  `config/config.php` loads `.env` via phpdotenv and returns a typed config array.
- **Acceptance:** `require config/config.php` returns an array with `db` and
  `mail` sub-arrays; missing `.env` throws a clear error.

### [ ] M0.4 — Front controller + rewrite
- **Files:** `public/index.php`, `public/.htaccess`
- **Spec:** `.htaccess` rewrites all non-file requests to `index.php`.
  `index.php`: set error handler, start session (secure cookie flags), load
  Composer autoload + config, instantiate `Router`, load `routes/web.php` and
  `routes/admin.php`, dispatch. For now a `GET /` may return `"OK"`.
- **Acceptance:** `php -S localhost:8000 -t public` then `curl localhost:8000/`
  returns `OK`; an unknown path does not 500.

---

## MILESTONE M1 — Core engine (`app/Core`)

**Goal:** the framework primitives every controller depends on. Define each as a
small, single-responsibility class with the contract below.

### [ ] M1.1 — Database (PDO singleton)
- **Files:** `app/Core/Database.php`
- **Contract:**
  ```php
  Database::connection(): PDO          // lazy singleton, ERRMODE_EXCEPTION
  ```
  Use `utf8mb4`. Throw on failure with a logged message.
- **Acceptance:** a throwaway script can `SELECT 1` via `Database::connection()`.

### [ ] M1.2 — Request & Response
- **Files:** `app/Core/Request.php`, `app/Core/Response.php`
- **Contract:**
  ```php
  Request::method(): string
  Request::path(): string              // path only, no query
  Request::input(string $key, $default=null)   // GET ∪ POST
  Request::file(string $key): ?array
  Response::html(string $body, int $status=200): void
  Response::redirect(string $to, int $status=302): void
  Response::json(array $data, int $status=200): void
  Response::notFound(): void           // renders 404 template
  ```
- **Acceptance:** unit-callable; `Request::path()` strips the query string.

### [ ] M1.3 — Router
- **Files:** `app/Core/Router.php`
- **Contract:**
  ```php
  $r->get(string $pattern, string $handler);      // 'HomeController@index'
  $r->post(string $pattern, string $handler);
  $r->dispatch(Request $r): void;
  ```
  Supports `{slug}` named params passed as method args. `Admin\` handlers are
  resolved under `App\Controllers\Admin`. Unmatched route → `Response::notFound()`.
- **Acceptance:** registering `GET /` and `GET /x/{slug}` dispatches to the right
  controller method with the slug argument.

### [ ] M1.4 — View (Twig wrapper)
- **Files:** `app/Core/View.php`, `templates/layouts/base.twig`,
  `templates/partials/{header,footer,flash}.twig`
- **Contract:** `View::render(string $template, array $data=[]): string`.
  Autoescaping ON. Inject globals: `site` (from `settings` table once it exists),
  `current_path`, `csrf_token`. `base.twig` and the partials must be built by
  porting the markup, CSS and fonts from `design/homepage-template.html` — extract
  the `<style>` block into `public/assets/css/style.css` and the inline script
  into `public/assets/js/main.js`.
- **Acceptance:** `View::render('public/home.twig')` returns the homepage HTML
  visually matching the provided template.

### [ ] M1.5 — Csrf
- **Files:** `app/Core/Csrf.php`
- **Contract:** `Csrf::token(): string`, `Csrf::check(?string $token): bool`.
  Token stored in session. A `{{ csrf_field()|raw }}` Twig function outputs the
  hidden input.
- **Acceptance:** a form posting a stale/empty token is rejected with 419/403.

### [ ] M1.6 — Auth + middleware
- **Files:** `app/Core/Auth.php`
- **Contract:**
  ```php
  Auth::attempt(string $email, string $pw): bool
  Auth::check(): bool
  Auth::user(): ?array
  Auth::logout(): void
  Auth::requireLogin(): void   // redirect to /admin/login if guest
  ```
  Passwords via `password_verify()`. `routes/admin.php` calls `requireLogin()`
  on every admin route except login.
- **Acceptance:** visiting `/admin` while logged out redirects to `/admin/login`.

---

## MILESTONE M2 — Database schema & seed

### [ ] M2.1 — Schema
- **Files:** `database/schema.sql`
- **Spec:** create these 12 tables exactly:
  `users, pages, programs, categories, posts, events, stats, partners,
  volunteers, messages, donations, settings`.
  Column definitions are fixed — use this schema:

  ```sql
  CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','editor') DEFAULT 'editor',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  );
  CREATE TABLE pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(160) UNIQUE NOT NULL,
    title VARCHAR(200) NOT NULL,
    body MEDIUMTEXT, meta_desc VARCHAR(300), hero_image VARCHAR(255),
    status ENUM('draft','published') DEFAULT 'draft',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  );
  CREATE TABLE programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NULL,
    slug VARCHAR(160) UNIQUE NOT NULL,
    title VARCHAR(200) NOT NULL,
    summary VARCHAR(400), body MEDIUMTEXT, cover_image VARCHAR(255),
    sort_order INT DEFAULT 0,
    status ENUM('draft','published') DEFAULT 'published',
    FOREIGN KEY (parent_id) REFERENCES programs(id) ON DELETE SET NULL
  );
  CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) UNIQUE NOT NULL, name VARCHAR(120) NOT NULL
  );
  CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(180) UNIQUE NOT NULL,
    title VARCHAR(220) NOT NULL, excerpt VARCHAR(400), body MEDIUMTEXT,
    cover_image VARCHAR(255), category_id INT NULL, author_id INT NULL,
    status ENUM('draft','published') DEFAULT 'draft',
    published_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_posts_pub (status, published_at)
  );
  CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(180) UNIQUE NOT NULL,
    title VARCHAR(220) NOT NULL, description MEDIUMTEXT,
    cover_image VARCHAR(255), venue VARCHAR(220),
    starts_at DATETIME NOT NULL, ends_at DATETIME NULL,
    status ENUM('draft','published') DEFAULT 'draft',
    INDEX idx_events_start (starts_at)
  );
  CREATE TABLE stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(160) NOT NULL, value VARCHAR(40) NOT NULL,
    sort_order INT DEFAULT 0
  );
  CREATE TABLE partners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL, logo VARCHAR(255), url VARCHAR(255),
    sort_order INT DEFAULT 0
  );
  CREATE TABLE volunteers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(160) NOT NULL, email VARCHAR(190) NOT NULL,
    phone VARCHAR(40), interest VARCHAR(160), message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  );
  CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL, email VARCHAR(190) NOT NULL,
    subject VARCHAR(220), body TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  );
  CREATE TABLE donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_name VARCHAR(160), donor_phone VARCHAR(40), donor_email VARCHAR(190),
    amount DECIMAL(10,2) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'KES',
    provider VARCHAR(40) NOT NULL DEFAULT 'paystack',
    channel VARCHAR(40) NULL,
    reference VARCHAR(120) UNIQUE NOT NULL,
    paystack_id BIGINT NULL,
    gateway_response VARCHAR(255) NULL,
    status ENUM('pending','completed','failed','abandoned') DEFAULT 'pending',
    paid_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_donations_status (status, created_at)
  );
  CREATE TABLE settings (
    setting_key VARCHAR(80) PRIMARY KEY, setting_value TEXT
  );
  ```
- **Acceptance:** `mysql myn < database/schema.sql` runs with no errors;
  `SHOW TABLES` lists all 12.

### [ ] M2.2 — Seed data
- **Files:** `database/seed.sql`
- **Spec:** seed from the live site content:
  - 1 `users` row — admin, password hashed (document the plaintext in README for
    first login, force change later).
  - 3 parent `programs` + 2 sub-programs (Advocacy & Civic Engagement,
    Leadership & Talent Development, Education & Capacity Enhancement;
    children: Foundational Literacy & Numeracy Assessment, Youth Mentorship).
  - 4 `posts` (the existing impact stories), `categories` (Governance, Advocacy,
    Education).
  - `stats` — 2014 / 3 / 6+ / 1000s rows.
  - `partners` — KCDF, Usawa Agenda, Zizi Afrique, Africa Voices,
    Poverty Eradication Network, EYC.
  - `pages` — about, contact, donation, volunteer.
  - `settings` — phone `+254 710 580 604`, email `info@makueniyouth.org`,
    address, Facebook/X/LinkedIn URLs.
- **Acceptance:** after seeding, `SELECT count(*) FROM programs` ≥ 5 and the
  homepage (built later) shows real data, not placeholders.

---

## MILESTONE M3 — Public site

**Goal:** the full public-facing website, data-driven from the DB.
Build Models first, then Controllers + Twig templates.

### [ ] M3.1 — Models
- **Files:** `app/Models/{Page,Program,Post,Event,Stat,Partner,Setting}.php`
- **Spec:** thin classes; static finder methods returning arrays. Required:
  `Post::published(int $limit, int $offset=0)`, `Post::findBySlug()`,
  `Program::tree()` (parents with children), `Event::upcoming()`,
  `Event::past()`, `Setting::all()` (key→value map), plus generic
  `find/findBySlug/all` where useful. All SQL prepared.
- **Acceptance:** each finder returns expected rows against seed data.

### [ ] M3.2 — Routes (public)
- **Files:** `routes/web.php`
- **Spec:** register exactly:
  ```
  GET  /                     HomeController@index
  GET  /programs             ProgramController@index
  GET  /programs/{slug}      ProgramController@show
  GET  /impact               PostController@index
  GET  /impact/{slug}        PostController@show
  GET  /events               EventController@index
  GET  /events/{slug}        EventController@show
  GET  /donate               DonationController@form
  POST /donate               DonationController@initiate
  POST /donate/callback      DonationController@callback
  GET  /volunteer            VolunteerController@form
  POST /volunteer            VolunteerController@submit
  GET  /contact              ContactController@form
  POST /contact              ContactController@submit
  GET  /sitemap.xml          SitemapController@index
  GET  /{slug}               PageController@show
  ```
- **Acceptance:** `/{slug}` catch-all is registered LAST.

### [ ] M3.3 — HomeController + homepage
- **Files:** `app/Controllers/HomeController.php`, `templates/public/home.twig`
- **Spec:** `index()` loads programs, latest posts, stats, partners → renders
  `home.twig`, which is the ported `homepage-template.html` with hardcoded
  content replaced by loop variables.
- **Acceptance:** homepage renders identical layout to the design file but with
  DB-driven programs/posts/stats/partners.

### [ ] M3.4 — Programs, Posts, Events, Pages
- **Files:** the four controllers + `templates/public/{programs,program-show,
  impact,post-show,events,event-show,page-show}.twig`
- **Spec:** list + detail views. Posts list paginates (`?page=N`).
  Events split upcoming/past. `PageController@show` 404s on unknown slug.
  All pages extend `base.twig`; reuse header/footer partials.
- **Acceptance:** every public URL in M3.2 returns 200 with correct content;
  unknown slug returns the styled 404.

### [ ] M3.5 — Contact & Volunteer forms
- **Files:** `ContactController.php`, `VolunteerController.php`,
  `app/Services/Mailer.php`, `templates/public/{contact,volunteer}.twig`
- **Spec:** validate server-side, CSRF-check, store in `messages` /
  `volunteers`, send a notification email via `Mailer` (PHPMailer/SMTP),
  redirect back with a flash success message. Rate-limit per IP.
- **Acceptance:** a valid submission inserts a row and shows the flash; an
  invalid/CSRF-missing submission is rejected and re-shows the form with errors.

### [ ] M3.6 — Sitemap + SEO basics
- **Files:** `app/Controllers/SitemapController.php`
- **Spec:** `sitemap.xml` lists home, all published programs/posts/events/pages.
  `base.twig` outputs per-page `<title>`, `meta description`, and Open Graph
  tags from data passed by each controller.
- **Acceptance:** `/sitemap.xml` is valid XML; page source shows correct meta.

---

## MILESTONE M4 — Admin CMS (`/admin`)

**Goal:** a panel non-technical staff can use. All routes auth-guarded.
Use `templates/layouts/admin.twig` (sidebar layout) for every admin view.

### [ ] M4.1 — Auth screens + admin layout
- **Files:** `app/Controllers/Admin/AuthController.php`,
  `templates/layouts/admin.twig`, `templates/admin/login.twig`
- **Spec:** `GET/POST /admin/login`, `POST /admin/logout`. Rate-limit login.
  `routes/admin.php` guards all other admin routes with `Auth::requireLogin()`.
- **Acceptance:** correct creds → dashboard; wrong creds → error; logged-out
  access to any `/admin/*` → redirect to login.

### [ ] M4.2 — Dashboard
- **Files:** `Admin/DashboardController.php`, `templates/admin/dashboard.twig`
- **Spec:** counts — posts, unread messages, pending donations, upcoming events;
  recent activity list.
- **Acceptance:** `/admin` shows live counts from the DB.

### [ ] M4.3 — Generic CRUD for content types
- **Files:** `Admin/{Post,Program,Event,Page}Controller.php` + matching
  `templates/admin/{type}/{index,form}.twig`
- **Spec:** each gives list / create / edit / delete. Routes follow
  `/admin/{type}`, `/admin/{type}/create`, `/admin/{type}/{id}/edit`,
  `POST /admin/{type}/{id}/delete`. Rich-text body via a lightweight editor
  (TinyMCE or Editor.js from CDN — allowed, it is editor-only). Separate
  **Save draft** and **Publish** actions. Slugs auto-generated from title,
  editable, unique-checked.
- **Acceptance:** creating a post in admin makes it appear on `/impact`;
  setting it to draft removes it from the public list.

### [ ] M4.4 — Stats, Partners, Settings
- **Files:** `Admin/{Stat,Partner,Settings}Controller.php` + templates
- **Spec:** CRUD for `stats` and `partners` (with logo upload); a single
  Settings form writing key/values (phone, email, address, socials) consumed
  by the `site` Twig global.
- **Acceptance:** editing the phone number in Settings changes it in the public
  footer immediately.

### [ ] M4.5 — Inboxes: Volunteers & Messages
- **Files:** `Admin/{Volunteer,Message}Controller.php` + templates
- **Spec:** read-only lists; mark message read/unread; CSV export of volunteers.
- **Acceptance:** form submissions from M3.5 appear here; CSV downloads.

### [ ] M4.6 — Media upload service
- **Files:** `app/Services/ImageProcessor.php`
- **Spec:** on any image upload — validate extension + MIME, rename randomly,
  resize to max 1600px wide, convert to WebP (GD or Imagick), store in
  `public/uploads/`. Used by every cover-image field.
- **Acceptance:** uploading a large JPG yields a smaller WebP in `uploads/`;
  a `.php` file disguised as `.jpg` is rejected.

### [ ] M4.7 — Users (admin role only)
- **Files:** `Admin/UserController.php` + templates
- **Spec:** CRUD admin/editor accounts; only `role=admin` may access; passwords
  hashed; an admin cannot delete their own account.
- **Acceptance:** an `editor` user gets 403 on `/admin/users`.

---

## MILESTONE M5 — Paystack donations

### [ ] M5.1 — Paystack service
- **Files:** `app/Services/Paystack.php`
- **Spec:** raw cURL against the Paystack API (`https://api.paystack.co`).
  Methods:
  - `initialize(int $amountMinor, string $email, string $reference, array $metadata=[]): array`
    — POSTs `/transaction/initialize`, returns `authorization_url`,
    `access_code`, `reference`.
  - `verify(string $reference): array` — GETs `/transaction/verify/{ref}`,
    returns the full transaction payload.
  - `verifyWebhookSignature(string $rawBody, string $signatureHeader): bool` —
    HMAC SHA-512 of `$rawBody` keyed by `PAYSTACK_SECRET_KEY`, timing-safe
    compared to `$signatureHeader`.
  All requests send `Authorization: Bearer <PAYSTACK_SECRET_KEY>`. Amounts are
  in the minor unit (kobo / cents) — for KES, multiply by 100. Currency from
  `PAYSTACK_CURRENCY` (default `KES`).
- **Acceptance:** with valid test keys, `initialize()` returns an
  `authorization_url` pointing at `https://checkout.paystack.com/...`.

### [ ] M5.2 — Donation flow
- **Files:** `app/Controllers/DonationController.php`,
  `templates/public/{donate,donate-thanks}.twig`
- **Spec:**
  - `form()` — shows amount + name + email + (optional) phone fields, CSRF.
  - `initiate()` — validates input, generates a unique `reference`
    (`MYN-` + timestamp + 6 random hex), inserts a `donations` row
    `status=pending`, calls `Paystack::initialize()`, redirects the donor to
    `authorization_url`.
  - `callback()` (`GET /donate/callback?reference=...`) — Paystack redirects
    the donor here after payment. Re-verify via `Paystack::verify()`. If
    `status === 'success'` AND the returned `amount` matches the stored
    amount in minor units AND the currency matches, mark `completed`, set
    `paid_at`, store `paystack_id` and `gateway_response`. Otherwise mark
    `failed` (or leave `pending` if Paystack says `pending`). Render
    `donate-thanks.twig`.
  - `webhook()` (`POST /donate/webhook`) — read **raw** request body,
    verify signature via `Paystack::verifyWebhookSignature()` with
    `X-Paystack-Signature` header, then re-verify the transaction via the
    API (defence in depth — never trust webhook amounts directly). Update
    the row idempotently. Respond `200 OK` only after successful processing.
- **Acceptance:** Paystack test mode end-to-end — a pending donation becomes
  `completed` after either the redirect-callback OR the webhook fires; a
  tampered webhook (wrong signature) is rejected with `400`.

### [ ] M5.3 — Donations ledger (admin)
- **Files:** `Admin/DonationController.php` + template
- **Spec:** list all donations with status, amount, currency, channel,
  reference; filter by status; date-range total.
- **Acceptance:** `/admin/donations` shows the M5.2 test transaction.

---

## MILESTONE M6 — WordPress migration & SEO

### [ ] M6.1 — Content import script
- **Files:** `database/import-wordpress.php`
- **Spec:** one-time CLI script. Pull posts/pages/programs from the WP REST API
  (`https://makueniyouth.org/wp-json/wp/v2/...`) or a provided WXR export; map
  into the new tables; download referenced media through `ImageProcessor`;
  rewrite image URLs in bodies to `/uploads/...`. Idempotent (skip existing
  slugs).
- **Acceptance:** running it populates posts/pages/media; re-running it inserts
  no duplicates.

### [ ] M6.2 — 301 redirect map
- **Files:** `public/.htaccess` (append), `database/redirects.md`
- **Spec:** map every legacy WordPress URL to its new path and add
  `Redirect 301` rules — e.g. `/advocacy-civic-education/` →
  `/programs/advocacy-civic-engagement`, `/blog/` → `/impact`,
  `/2025/05/28/{slug}/` → `/impact/{slug}`. Document the full map in
  `redirects.md`.
- **Acceptance:** `curl -I` on each old URL returns `301` to the correct target.

---

## MILESTONE M7 — Hardening, testing, launch

### [ ] M7.1 — Error pages & logging
- **Spec:** styled `404` and `500` templates; uncaught exceptions logged to
  `storage/logs/app.log`; stack traces shown only when `APP_ENV=local`.
- **Acceptance:** an unknown route shows the styled 404; a forced exception is
  logged and shows a friendly 500 in production mode.

### [ ] M7.2 — Security pass
- **Spec:** audit against `CLAUDE.md` §6 — confirm prepared statements
  everywhere, CSRF on every form, upload validation, secure session cookies,
  rate limiting on login + public forms, HTTPS-redirect rule, security headers
  (`X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`).
- **Acceptance:** a written checklist in `README.md`, every item ticked with the
  file/line that satisfies it.

### [ ] M7.3 — Performance
- **Spec:** images lazy-loaded; CSS/JS minified; critical CSS inlined in
  `base.twig`; gzip enabled via `.htaccess`; DB indexes from M2.1 present.
- **Acceptance:** homepage transfers under ~300 KB and renders quickly on a
  throttled (Fast 3G) profile.

### [ ] M7.4 — Tests (smoke)
- **Files:** `tests/` + PHPUnit
- **Spec:** smoke tests — every public route returns 200/expected status; a
  contact submission inserts a row; auth guard blocks `/admin`; CSRF rejection
  works.
- **Acceptance:** `vendor/bin/phpunit` passes.

### [ ] M7.5 — Docs & handover
- **Files:** `README.md`, `docs/admin-guide.md`
- **Spec:** README — setup, env, deploy steps, backup instructions.
  `admin-guide.md` — plain-language guide for MYN staff: log in, add a post,
  add an event, change settings.
- **Acceptance:** a new developer can go from clone to running site using only
  `README.md`.

---

## Final definition of done

- All M0–M7 tasks `[x]`.
- `php -S localhost:8000 -t public` serves the full site.
- Public site is data-driven; admin panel manages every content type.
- Paystack donations work end-to-end in test mode.
- Security checklist in `README.md` fully ticked.
- WordPress content imported and all legacy URLs 301-redirected.
