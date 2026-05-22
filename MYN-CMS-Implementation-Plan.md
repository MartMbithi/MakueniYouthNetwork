# Makueni Youth Network — Custom PHP CMS Rebuild
### Implementation Plan, Architecture & Improvement Roadmap

---

## 1. Audit of the current site

The live site (makueniyouth.org) is a WordPress build using a generic theme plus Slider Revolution. It works, but for an organization this size it carries weight it does not need.

**What is wrong today**

| Issue | Impact |
|---|---|
| Slider Revolution + heavy theme | Slow first load, especially on mobile data — your audience is mostly on phones in Makueni |
| Generic WordPress theme | Looks like a template; no distinct MYN identity |
| WordPress core + plugins | Constant update treadmill; a known target for automated attacks |
| Blog post URLs mix formats | `/blog/`, `/2025/05/28/slug/`, and `/slug/` all appear — bad for SEO and confusing |
| News dates all show "28 May" | Suggests bulk import; no real editorial calendar |
| No clear donation flow | "Donate" links exist but there is no visible payment integration (M-Pesa is the obvious gap for Kenya) |
| Thin program/impact content | "..." placeholders on news cards; impact is told, not shown with numbers |
| Image weight | Uncompressed PNGs/JPGs served full-size |

**What is good and must be preserved**

- Clear program structure (3 flagship programs + 2 sub-programs)
- Strong mission/vision/values content
- Real partner relationships (KCDF, Usawa Agenda, Zizi Afrique, etc.)
- Working contact details and social links

The rebuild keeps the content architecture and replaces the engine and the skin.

---

## 2. Why a custom PHP CMS is the right call here

A custom CMS is justified **only because the content model is small and stable**: a handful of page types, a blog, events, programs, and a donation flow. You are not building a general-purpose CMS — you are building exactly what MYN needs and nothing else.

Benefits for this specific case:

- **Fast** — no plugin overhead; pages render in a few small queries.
- **Cheap to host** — runs on basic shared/VPS PHP hosting, no special requirements.
- **Secure surface area** — you control every line; no third-party plugin vulnerabilities.
- **Maintainable by a small team** — a junior dev can understand the whole codebase in a day.

> **Honest caveat:** a custom CMS means *you* own all maintenance, backups, and security patching forever. If MYN has no reliable developer on call, a lean managed option (a stripped WordPress, or a static site + headless CMS) is lower-risk. Given the request is explicitly for custom PHP, this plan delivers that — but flag this trade-off to the board.

---

## 3. Recommended technology stack

Keep it boring and well-supported.

| Layer | Choice | Reason |
|---|---|---|
| Language | PHP 8.2+ | Typed properties, enums, fast |
| Database | MySQL 8 / MariaDB 10.6+ | Standard, available on all Kenyan hosts |
| Routing | Lightweight — `bramus/router` or hand-rolled | No need for a full framework |
| Templating | Native PHP partials, or Twig (`twig/twig`) | Twig recommended — safe auto-escaping by default |
| DB access | PDO with prepared statements | No ORM needed at this scale |
| Dependency mgmt | Composer | For Twig, dotenv, PHPMailer |
| Auth | Custom sessions + `password_hash()` | Admin login only |
| Email | PHPMailer over SMTP | Contact form, volunteer notifications |
| Payments | Safaricom Daraja API (M-Pesa STK Push) + optional card via Pesapal/Flutterwave | M-Pesa is non-negotiable for a Kenyan donor base |
| Assets | Plain CSS + minimal vanilla JS | Matches the template provided; no build step required |
| Server | Nginx or Apache + PHP-FPM | Standard LAMP/LEMP |

**Do not** pull in Laravel/Symfony for this. The maintenance and hosting cost outweighs the benefit at this content volume.

---

## 4. Database schema

A compact relational schema. Twelve tables cover the whole site.

```sql
-- Admin users
CREATE TABLE users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(120) NOT NULL,
  email         VARCHAR(190) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','editor') DEFAULT 'editor',
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Reusable static pages (Home blocks, About, Contact text)
CREATE TABLE pages (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  slug        VARCHAR(160) UNIQUE NOT NULL,
  title       VARCHAR(200) NOT NULL,
  body        MEDIUMTEXT,
  meta_desc   VARCHAR(300),
  hero_image  VARCHAR(255),
  status      ENUM('draft','published') DEFAULT 'draft',
  updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Flagship + sub-programs
CREATE TABLE programs (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  parent_id    INT NULL,
  slug         VARCHAR(160) UNIQUE NOT NULL,
  title        VARCHAR(200) NOT NULL,
  summary      VARCHAR(400),
  body         MEDIUMTEXT,
  cover_image  VARCHAR(255),
  sort_order   INT DEFAULT 0,
  status       ENUM('draft','published') DEFAULT 'published',
  FOREIGN KEY (parent_id) REFERENCES programs(id) ON DELETE SET NULL
);

-- Blog / Impact stories
CREATE TABLE posts (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  slug         VARCHAR(180) UNIQUE NOT NULL,
  title        VARCHAR(220) NOT NULL,
  excerpt      VARCHAR(400),
  body         MEDIUMTEXT,
  cover_image  VARCHAR(255),
  category_id  INT NULL,
  author_id    INT NULL,
  status       ENUM('draft','published') DEFAULT 'draft',
  published_at DATETIME NULL,
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE categories (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  slug  VARCHAR(120) UNIQUE NOT NULL,
  name  VARCHAR(120) NOT NULL
);

-- Events
CREATE TABLE events (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  slug        VARCHAR(180) UNIQUE NOT NULL,
  title       VARCHAR(220) NOT NULL,
  description MEDIUMTEXT,
  cover_image VARCHAR(255),
  venue       VARCHAR(220),
  starts_at   DATETIME NOT NULL,
  ends_at     DATETIME NULL,
  status      ENUM('draft','published') DEFAULT 'draft'
);

-- Impact stats shown on the homepage
CREATE TABLE stats (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  label      VARCHAR(160) NOT NULL,
  value      VARCHAR(40) NOT NULL,
  sort_order INT DEFAULT 0
);

-- Partners / funders
CREATE TABLE partners (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(160) NOT NULL,
  logo       VARCHAR(255),
  url        VARCHAR(255),
  sort_order INT DEFAULT 0
);

-- Volunteer applications
CREATE TABLE volunteers (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  full_name   VARCHAR(160) NOT NULL,
  email       VARCHAR(190) NOT NULL,
  phone       VARCHAR(40),
  interest    VARCHAR(160),
  message     TEXT,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Contact form submissions
CREATE TABLE messages (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(160) NOT NULL,
  email      VARCHAR(190) NOT NULL,
  subject    VARCHAR(220),
  body       TEXT NOT NULL,
  is_read    TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Donations (records every M-Pesa / card attempt)
CREATE TABLE donations (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  donor_name      VARCHAR(160),
  donor_phone     VARCHAR(40),
  donor_email     VARCHAR(190),
  amount          DECIMAL(10,2) NOT NULL,
  channel         ENUM('mpesa','card') DEFAULT 'mpesa',
  mpesa_receipt   VARCHAR(60) NULL,
  checkout_ref    VARCHAR(80) NULL,
  status          ENUM('pending','completed','failed') DEFAULT 'pending',
  created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Key/value site settings (phone, email, social URLs)
CREATE TABLE settings (
  setting_key   VARCHAR(80) PRIMARY KEY,
  setting_value TEXT
);
```

Add indexes on `posts.status, posts.published_at` and `events.starts_at` — those drive every public listing query.

---

## 5. Project structure

A clean front-controller layout. Only `/public` is web-accessible.

```
makueniyouth/
├── public/                  ← web root (point Nginx/Apache here)
│   ├── index.php            ← single entry point (front controller)
│   ├── .htaccess            ← rewrite all → index.php
│   ├── assets/
│   │   ├── css/style.css
│   │   ├── js/main.js
│   │   └── img/
│   └── uploads/             ← user-uploaded media (writable)
│
├── app/
│   ├── Core/
│   │   ├── Router.php
│   │   ├── Database.php     ← PDO singleton
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── View.php         ← Twig wrapper
│   │   ├── Auth.php
│   │   └── Csrf.php
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   ├── ProgramController.php
│   │   ├── PostController.php
│   │   ├── EventController.php
│   │   ├── PageController.php
│   │   ├── ContactController.php
│   │   ├── VolunteerController.php
│   │   ├── DonationController.php
│   │   └── Admin/            ← all CMS controllers
│   ├── Models/               ← thin PDO data classes
│   └── Services/
│       ├── Mpesa.php         ← Daraja STK Push + callback
│       ├── Mailer.php        ← PHPMailer wrapper
│       └── ImageProcessor.php← resize + WebP on upload
│
├── templates/
│   ├── layouts/base.twig
│   ├── partials/             ← header, footer, nav
│   ├── public/               ← home, program, post, event...
│   └── admin/                ← dashboard, forms, lists
│
├── routes/
│   ├── web.php               ← public routes
│   └── admin.php             ← /admin routes (auth-guarded)
│
├── config/
│   └── config.php            ← reads from .env
├── storage/logs/
├── .env                      ← DB creds, M-Pesa keys (NEVER commit)
├── composer.json
└── README.md
```

**Key principle:** the document root is `public/`. Everything else — app code, `.env`, templates — sits *above* the web root and cannot be reached by a browser.

---

## 6. Request flow

Every request hits one file.

```
Browser → public/index.php
        → load .env + config
        → Router matches URL to a Controller@method
        → Controller queries Models (PDO)
        → Controller passes data to View (Twig)
        → rendered HTML returned
```

`.htaccess` (Apache) for clean URLs:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

Example route table (`routes/web.php`):

```php
$router->get('/',                       'HomeController@index');
$router->get('/programs',               'ProgramController@index');
$router->get('/programs/{slug}',        'ProgramController@show');
$router->get('/impact',                 'PostController@index');
$router->get('/impact/{slug}',          'PostController@show');
$router->get('/events',                 'EventController@index');
$router->get('/events/{slug}',          'EventController@show');
$router->get('/donate',                 'DonationController@form');
$router->post('/donate',                'DonationController@initiate');
$router->post('/donate/callback',       'DonationController@callback'); // M-Pesa
$router->get('/volunteer',              'VolunteerController@form');
$router->post('/volunteer',             'VolunteerController@submit');
$router->get('/contact',                'ContactController@form');
$router->post('/contact',               'ContactController@submit');
$router->get('/{slug}',                 'PageController@show'); // catch-all static pages
```

**Fix the URL mess now:** standardize on `/impact/{slug}` for every story and 301-redirect all old WordPress URL formats (`/blog/`, `/2025/05/28/...`) to the new ones in `.htaccess`.

---

## 7. The admin panel (the actual "CMS")

Lives under `/admin`, guarded by `Auth` middleware. Built with the same Twig templates, a simple sidebar layout.

**Modules**

1. **Dashboard** — counts of posts, unread messages, pending donations, upcoming events.
2. **Posts / Impact stories** — list, create, edit, delete; rich-text editor (TinyMCE or Editor.js); cover image upload; draft/publish; schedule via `published_at`.
3. **Programs** — manage the 3 flagship + sub-programs; sort order.
4. **Events** — create with date/venue; auto-moves to "past" after `ends_at`.
5. **Pages** — edit Home section copy, About, Contact intro without touching code.
6. **Stats & Partners** — edit the homepage numbers and partner logos directly.
7. **Volunteers** — read-only inbox of applications; export CSV.
8. **Messages** — contact form inbox; mark read.
9. **Donations** — ledger of all M-Pesa/card transactions with status and receipt number.
10. **Settings** — phone, email, address, social links (drives header/footer everywhere).
11. **Users** — admin/editor accounts (admin only).
12. **Media** — uploaded files; auto-resized and converted to WebP on upload.

**Editor experience matters** — MYN staff are not developers. Every field should have a label and helper text; image uploads should show a preview; "Save draft" and "Publish" should be separate, obvious buttons.

---

## 8. Migration from WordPress

Do this carefully — you do not want to lose existing content or SEO ranking.

**Step 1 — Export content.** Pull posts, pages, programs and media from WordPress. Either use the WP REST API (`/wp-json/wp/v2/posts`) and write a one-time PHP import script, or export the XML and parse it. Map each WP item into the new tables.

**Step 2 — Migrate media.** Download everything from `/wp-content/uploads/`, run it through `ImageProcessor` (resize, compress, convert to WebP), and store in `public/uploads/`. Rewrite image paths in post bodies.

**Step 3 — Preserve URLs.** This is the SEO-critical step. Map every old URL to its new one and add 301 redirects:

```apache
Redirect 301 /advocacy-civic-education/ /programs/advocacy-civic-engagement
Redirect 301 /blog/ /impact
Redirect 301 /2025/05/28/youth-leading-change/ /impact/youth-leading-change
# ...one line per old post
```

**Step 4 — Run in parallel.** Build and test the new site on a staging subdomain (`staging.makueniyouth.org`). Get staff to add a few posts and confirm the admin panel works for them.

**Step 5 — Cutover.** Point DNS / web root to the new site during low-traffic hours. Keep the WordPress database backed up for 90 days as a safety net.

**Step 6 — Post-launch.** Submit the new sitemap to Google Search Console; watch for 404s for two weeks and add any missed redirects.

---

## 9. Security checklist

A custom CMS is only as safe as its discipline. Non-negotiables:

- **All DB queries via PDO prepared statements.** No string-concatenated SQL, ever.
- **Twig auto-escaping on** — prevents XSS in any displayed content.
- **CSRF tokens** on every form (contact, volunteer, donate, all admin forms).
- **`password_hash()` / `password_verify()`** for admin auth — never store plain or MD5 passwords.
- **Rate-limit** login attempts and the contact/volunteer forms (simple per-IP counter) to block spam and brute force.
- **File upload validation** — whitelist extensions (`jpg, png, webp, pdf`), check MIME type, rename files, store outside any executable path.
- **`.env` outside web root**, never committed to git.
- **HTTPS enforced** (Let's Encrypt) + HSTS header.
- **Session cookies** `HttpOnly`, `Secure`, `SameSite=Lax`.
- **M-Pesa callbacks** — validate the source and confirm amounts server-side before marking a donation `completed`.
- **Daily automated database backups** to off-server storage.

---

## 10. Areas of improvement (and how to execute each)

These go beyond a like-for-like rebuild — they make the new site genuinely better.

| # | Improvement | How to execute |
|---|---|---|
| 1 | **M-Pesa donations** | Integrate Daraja STK Push: donor enters phone + amount, gets a prompt, `donations` table records the result via callback. This is the single highest-impact addition. |
| 2 | **Real impact numbers** | Replace vague copy with a CMS-managed `stats` block — youth reached, forums held, counties engaged. Update quarterly. |
| 3 | **Performance** | Compress + WebP all images on upload; lazy-load below the fold; inline critical CSS. Target a sub-2-second load on 3G. |
| 4 | **Mobile-first** | Your audience is on phones — design and test mobile first, not desktop. The provided template already does this. |
| 5 | **Clean, consistent URLs** | One format: `/impact/{slug}`, `/programs/{slug}`, `/events/{slug}`. 301 everything old. |
| 6 | **SEO foundation** | Per-page meta title/description fields in the CMS; auto-generated `sitemap.xml`; Open Graph tags; structured data (Organization + Article schema). |
| 7 | **Events with status** | Auto-split upcoming vs past; add a simple RSVP/registration form feeding the `volunteers`-style table. |
| 8 | **Newsletter capture** | Email signup in the footer; store addresses; integrate Mailchimp/Brevo free tier later. |
| 9 | **Accessibility** | Proper heading order, alt text on every image, visible focus states, AA colour contrast — important for an inclusion-focused org. |
| 10 | **Analytics & transparency** | Add privacy-friendly analytics (Plausible or GA4); publish an annual report / downloadable PDF section. |
| 11 | **Photo galleries** | Programs and events feel abstract — a gallery per program shows real work and builds donor trust. |
| 12 | **Content workflow** | Draft → review → publish, with editor vs admin roles, so staff can contribute safely. |

---

## 11. Execution roadmap

A realistic phased plan. Timeline assumes one focused developer; double it for part-time work.

**Phase 0 — Setup & design sign-off (Week 1)**
- Confirm the visual template (the homepage provided here is the starting point).
- Extend the design to inner page layouts: program, post, event, contact, donate.
- Set up repo, local environment, staging subdomain, `.env`.

**Phase 1 — Core engine (Weeks 2–3)**
- Router, Database (PDO), View (Twig), Request/Response, Auth, CSRF.
- Database schema created and seeded with current site content.
- Base layout + header/footer partials wired to `settings`.

**Phase 2 — Public site (Weeks 4–5)**
- Home, Programs (+ sub-programs), Impact list + single, Events, static Pages.
- Contact and Volunteer forms with email notifications + DB storage.
- Responsive QA on real devices.

**Phase 3 — Admin CMS (Weeks 6–7)**
- Login, dashboard, and CRUD for posts, programs, events, pages.
- Stats, partners, settings, media library with image processing.
- Volunteer/message inboxes.

**Phase 4 — Donations (Week 8)**
- Daraja M-Pesa STK Push integration + callback handling.
- Donation form, confirmation page, admin ledger.
- Optional card fallback (Pesapal/Flutterwave).

**Phase 5 — Migration & SEO (Week 9)**
- Import WordPress content and media.
- Build the full 301 redirect map.
- Sitemap, meta tags, structured data, Search Console.

**Phase 6 — Testing & launch (Week 10)**
- Security review against the checklist in §9.
- Staff training on the admin panel; short written guide.
- Load test, cross-browser test, then DNS cutover.

**Phase 7 — Post-launch (ongoing)**
- Monitor 404s and analytics for 2 weeks.
- Automated daily backups confirmed working.
- Monthly dependency/security check.

**Total: roughly 10 focused weeks to launch.**

---

## 12. Hosting & running costs (indicative)

| Item | Note | Rough annual cost |
|---|---|---|
| VPS or quality shared hosting | 1–2 GB RAM VPS is ample | $60–150 |
| Domain | already owned | ~$15 |
| SSL | Let's Encrypt | Free |
| Email (transactional) | Brevo/Mailgun free tier | Free–low |
| M-Pesa Daraja | Safaricom; transaction-based, no setup fee | Per-transaction |
| Backups | Object storage | $10–25 |

Far cheaper to run than a plugin-heavy WordPress install, and no premium plugin licences.

---

## 13. Summary

Keep MYN's content architecture, throw away the WordPress engine and generic theme. Build a small, well-structured PHP application: front controller, PDO, Twig, a focused admin panel, and — critically — M-Pesa donations and proper SEO redirects. Ten weeks of focused work delivers a site that is faster, cheaper, safer, genuinely MYN-branded, and actually able to raise money.

The one decision to make consciously: a custom CMS means MYN owns maintenance forever. Make sure there is a developer relationship in place before committing.
