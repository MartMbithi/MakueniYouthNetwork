# 301 redirect map — legacy WordPress URLs → new CMS

These rules ship in `public/.htaccess`. Every legacy URL pattern below maps
1-to-1 to a new path; the rule set is **append-only** — never delete a row,
only add or supersede.

Verification: `curl -I https://<host><old-url>` should return
`HTTP/1.1 301 Moved Permanently` with the new URL in `Location`.

## 1. Program / "section" pages

| Old URL                                  | New URL                                       |
|------------------------------------------|-----------------------------------------------|
| `/advocacy-civic-education/`             | `/programs/advocacy-civic-engagement`         |
| `/advocacy-civic-engagement/`            | `/programs/advocacy-civic-engagement`         |
| `/leadership-talent/`                    | `/programs/leadership-talent-development`     |
| `/leadership-talent-development/`        | `/programs/leadership-talent-development`     |
| `/education-capacity/`                   | `/programs/education-capacity-enhancement`    |
| `/education-capacity-enhancement/`       | `/programs/education-capacity-enhancement`    |
| `/foundational-literacy-numeracy/`       | `/programs/foundational-literacy-numeracy-assessment` |
| `/youth-mentorship/`                     | `/programs/youth-mentorship`                  |

## 2. Blog / post archive

| Old URL              | New URL    |
|----------------------|------------|
| `/blog/`             | `/impact`  |
| `/news/`             | `/impact`  |
| `/category/*`        | `/impact`  |
| `/tag/*`             | `/impact`  |
| `/author/*`          | `/impact`  |

## 3. Dated post permalinks  (`/YYYY/MM/DD/{slug}/`)

WordPress used the date-based permalink structure. We rewrite anything that
looks like `/YYYY/MM/DD/{slug}/` to `/impact/{slug}`.

| Old URL pattern                       | New URL pattern         |
|---------------------------------------|-------------------------|
| `/{year}/{month}/{day}/{slug}/`       | `/impact/{slug}`        |
| `/{year}/{month}/{slug}/`             | `/impact/{slug}`        |

## 4. Static pages

| Old URL          | New URL       |
|------------------|---------------|
| `/about-us/`     | `/about`      |
| `/contact-us/`   | `/contact`    |
| `/donate-now/`   | `/donate`     |
| `/volunteer/`    | `/volunteer`  |

## 5. WP feed / xmlrpc endpoints

| Old URL          | Action         |
|------------------|----------------|
| `/feed/`         | 410 Gone       |
| `/feed/rss/`     | 410 Gone       |
| `/xmlrpc.php`    | 403 Forbidden  |
| `/wp-login.php`  | 403 Forbidden  |
| `/wp-admin/`     | 403 Forbidden  |
