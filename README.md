# Makueni Youth Network — CMS

A custom, lightweight PHP CMS for [makueniyouth.org](https://makueniyouth.org) —
a youth-led CBO in Wote, Makueni County, Kenya.

Built to be fast on mobile data, cheap to host, and editable by non-technical staff.

## Stack

- PHP 8.2+ (strict types, typed properties, enums)
- MySQL 8 / MariaDB 10.6+ via PDO
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
php -S localhost:8000 -t public
```

Browse to <http://localhost:8000/>.

## Project layout

See `CLAUDE.md` §4 — every directory has a defined purpose, and the public web
root is `public/` only. All application code, templates, and `.env` sit above
the web root.

## Build plan

Work is sequenced in `BUILD-PLAN.md`. Milestones M0 → M7 must be completed in
order.

## Security checklist

To be populated in milestone M7.2.
