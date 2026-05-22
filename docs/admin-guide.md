# Makueni Youth Network — Admin guide

A plain-language guide for MYN staff. No code knowledge required.

## 1. Signing in

1. In a browser, go to <https://makueniyouth.org/admin/login>.
2. Type your email and password, then click **Sign in**.
3. You will land on the **Dashboard**, which shows:
   - how many posts are published
   - how many unread messages you have
   - how many donations are pending
   - your upcoming events

If you forget your password, ask another admin to reset it from
**Admin → Users → (your row) → Edit**.

**Always sign out** (top-right "Sign out" button) when you finish on a shared
computer.

## 2. Adding a story (a "post")

1. In the left sidebar, click **Posts**.
2. Click **+ New post** (top-right of the page).
3. Fill in:
   - **Title** — the headline.
   - **Slug** — leave blank to auto-generate. Only fill in if you want a
     specific URL like `youth-budget-forum-2026`.
   - **Category** — pick one (Governance / Advocacy / Education).
   - **Excerpt** — one or two sentences that show up on the list page.
   - **Body** — the full story. Use plain HTML if you know it, otherwise
     just paste your text.
   - **Cover image** — paste a URL or click "Choose file" to upload a JPG
     or PNG.
   - **Publish date** — leave blank to publish now, or set a future date to
     schedule it.
4. Click **Save draft** to keep working on it later, or **Publish** to make
   it live immediately.
5. After saving, you can click **View live →** to see how it looks on the
   public site.

To take a story offline, open it from **Posts**, change the action button to
**Save draft**, and it disappears from the public list.

## 3. Adding an event

1. Click **Events** in the sidebar, then **+ New event**.
2. Fill in:
   - **Title**, **Venue** (e.g. "Famo House conference room").
   - **Starts at / Ends at** — pick a date and time. Ends-at is optional.
   - **Description** — what people should know about the event.
   - **Cover image** — optional.
3. **Save draft** keeps it private; **Publish** puts it on the public
   `/events` page. Past events automatically move to the "Where we have
   been" section after the start date passes.

## 4. Managing pages

These are the long-form pages — **About**, **Contact**, **Donate**,
**Volunteer**. You can add new ones (e.g. "Annual report 2026") at any time.

1. Click **Pages** in the sidebar.
2. Click **+ New page** or open an existing page to edit it.
3. **Slug** decides the URL. For example, `annual-report-2026` becomes
   `/annual-report-2026`.
4. **Meta description** is what Google shows in search results — keep it
   under ~160 characters.
5. **Publish** when ready.

## 5. Programs

The three flagship programs (Advocacy & Civic Engagement, Leadership & Talent
Development, Education & Capacity Enhancement) live under **Programs**. Each
can have **sub-programs** (e.g. Youth Mentorship sits under Leadership). To
add a sub-program, set the **Parent program** dropdown to its parent.

## 6. Changing the site footer / contact info

1. Click **Settings** in the sidebar.
2. Edit the **Phone**, **Email**, **Address**, **PO Box**, or social links.
3. Click **Save settings**. The new values appear on every public page
   immediately — no refresh of the live site needed.

## 7. Inboxes

### Messages

People who write you through the **Contact** form land in **Inbox → Messages**.

- Unread messages are highlighted with a small orange dot.
- Click **Mark read** / **Mark unread** to flip the dot.
- Click the donor's email to reply directly from your email app.

### Volunteers

Volunteer applications land in **Inbox → Volunteers**.

- Click **Export CSV** (top-right) to download a spreadsheet of every
  application — useful for sharing with program leads.

### Donations

Every Paystack donation appears in **Inbox → Donations**.

- Filter by status (pending / completed / failed) and date range.
- The blue "Total completed in this range" figure at the top is your
  rolling fundraising number.

## 8. Managing partners

1. Click **Site → Partners**.
2. **+ New partner** to add a logo. Either paste a logo URL or upload a
   PNG / JPG.
3. The order field controls the position on the public homepage logo grid.

## 9. Stats (the homepage figures)

The big numbers on the homepage stripe (2014 / 3 / 6+ / 1000s) come from
**Site → Stats**. Edit them whenever the numbers change.

## 10. Users (admins only)

If your account has the **admin** role, you can manage staff accounts under
**Admin → Users**.

- Roles: **admin** can manage everything including users; **editor** can
  manage content but cannot reach this section.
- Passwords must be at least 8 characters. Use a unique strong password
  (consider a password manager).
- You cannot delete your own account — ask another admin to do it.

## 11. Good habits

- **Use the Save draft button while you work.** It is safer than relying on
  your browser staying open.
- **Add a cover image to every published post or event.** Stories without
  cover images look thin on social shares.
- **Reply to messages within two working days.** That is the promise on the
  public contact form.
- **Change your password every six months.**
- **Sign out on shared computers.**

## 12. When something looks wrong

1. Sign out, then sign back in. Most "stale page" issues clear that way.
2. If the site is fully down (500 errors everywhere), email the developer
   on call. The most recent error trace is at `storage/logs/app.log` on
   the server.

Welcome aboard. 🌅
