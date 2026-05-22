# How to build this with Claude Code

You have a build-ready spec. Here is the workflow.

## 1. Set up the folder

```bash
mkdir makueniyouth && cd makueniyouth
git init
```

Drop these files in:

```
makueniyouth/
├── CLAUDE.md                     ← auto-loaded by Claude Code every session
├── BUILD-PLAN.md                 ← the milestone-by-milestone task list
└── design/
    └── homepage-template.html    ← the approved visual design
```

`CLAUDE.md` must sit at the repo root — Claude Code reads it automatically and
treats it as standing instructions. `BUILD-PLAN.md` is the work queue.

## 2. Start Claude Code

```bash
claude
```

## 3. Kick-off prompt

Paste this as your first message:

> Read CLAUDE.md and BUILD-PLAN.md in full. Then begin MILESTONE M0 and complete
> tasks M0.1 through M0.4 in order. Follow every rule in CLAUDE.md. After M0,
> stop and show me what you built and how you verified each task — do not start
> M1 until I say continue.

## 4. Drive it milestone by milestone

After each milestone, review the work, then continue with:

> M0 looks good. Proceed with MILESTONE M1, tasks M1.1–M1.6, in order. Stop after M1.

Going one milestone at a time keeps each change reviewable and lets you catch a
wrong turn early instead of after the whole app is built. Eight milestones, so
roughly eight review checkpoints.

## 5. Useful mid-build prompts

- **If it drifts:** "Re-read CLAUDE.md §6 and confirm this task obeys every
  security rule before moving on."
- **To verify a milestone:** "Run the dev server and curl every route added in
  this milestone. Show the status codes."
- **If a task is ambiguous:** Claude is instructed to ask — answer it, then say
  continue.
- **Mark progress:** "Update BUILD-PLAN.md, ticking the tasks you have completed."

## 6. Things you must supply

Claude Code cannot guess these — have them ready:

- **Database:** a local MySQL/MariaDB instance and credentials for `.env`.
- **M-Pesa (M5):** Safaricom Daraja **sandbox** consumer key, secret, shortcode
  and passkey. Get them free at developer.safaricom.co.ke. Use sandbox until
  launch.
- **SMTP (M3.5):** mail host/user/pass for the contact form (Brevo or Mailgun
  free tier works).
- **WordPress migration (M6):** confirm the live REST API is reachable
  (`makueniyouth.org/wp-json/wp/v2/posts`) or export the site's WXR XML.
- **First admin login:** after seeding (M2.2), the README will contain the
  seeded admin email + temporary password — change it immediately.

## 7. Order matters

Do not let Claude skip ahead. Milestones depend on each other:
M0 scaffold → M1 engine → M2 database → M3 public site → M4 admin →
M5 donations → M6 migration → M7 hardening. The plan is already sequenced;
just hold the line on "one milestone, then stop."

## 8. When it is done

The final state: `php -S localhost:8000 -t public` serves the whole site,
the admin panel manages all content, M-Pesa donations work in sandbox, and
every legacy WordPress URL 301-redirects. Then switch M-Pesa to production
credentials, deploy, and point DNS.
