-- ---------------------------------------------------------------------------
-- Makueni Youth Network CMS — seed data
-- Conjured Upon This Day, Fri May 22 2026 — M B I T H I
--
-- First admin login (CHANGE IMMEDIATELY):
--   email:    admin@makueniyouth.org
--   password: ChangeMe2026!
--
-- Run with:   mysql -u root -p myn < database/seed.sql
-- ---------------------------------------------------------------------------

SET NAMES utf8mb4;

-- Wipe tables (idempotent reseed; donations/messages/volunteers preserved
-- so we don't lose real submissions when re-seeding content).
DELETE FROM posts;
DELETE FROM categories;
DELETE FROM programs;
DELETE FROM pages;
DELETE FROM stats;
DELETE FROM partners;
DELETE FROM settings;
DELETE FROM users WHERE email = 'admin@makueniyouth.org';
ALTER TABLE users      AUTO_INCREMENT = 1;
ALTER TABLE programs   AUTO_INCREMENT = 1;
ALTER TABLE categories AUTO_INCREMENT = 1;
ALTER TABLE posts      AUTO_INCREMENT = 1;
ALTER TABLE pages      AUTO_INCREMENT = 1;
ALTER TABLE stats      AUTO_INCREMENT = 1;
ALTER TABLE partners   AUTO_INCREMENT = 1;

-- ---------------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------------
INSERT INTO users (name, email, password_hash, role) VALUES
  ('Site Admin', 'admin@makueniyouth.org',
   '$2y$10$SWgJ4KROw50cqwNO0UTM5.Rid.m9fEkOSA06EzqC2aW6aHVkWFsSK', 'admin');

-- ---------------------------------------------------------------------------
-- programs — 3 parents + 2 children
-- ---------------------------------------------------------------------------
INSERT INTO programs (id, parent_id, slug, title, summary, body, cover_image, sort_order, status) VALUES
  (1, NULL, 'advocacy-civic-engagement',
   'Advocacy & Civic Engagement',
   'We equip young people with civic knowledge and confidence to shape county planning, budgets and policy — and to hold leaders accountable.',
   '<p>Our Advocacy &amp; Civic Engagement program builds the next generation of civic-minded leaders across Makueni County. Through training forums, county budget reviews and policy workshops, we help young people translate everyday frustrations into evidence-backed advocacy.</p><p>We partner with ward administrators, sub-county offices and CSOs to make sure youth voices are not just heard, but acted on.</p>',
   'https://makueniyouth.org/wp-content/uploads/2025/06/Advocacy.png', 10, 'published'),

  (2, NULL, 'leadership-talent-development',
   'Leadership & Talent Development',
   'Structured training and mentorship that nurtures confident, values-driven leaders ready to take up roles in their communities.',
   '<p>The Leadership &amp; Talent Development program is a structured pathway: from school clubs and ward youth associations into county-level forums and beyond. We run mentorship circles, public-speaking labs and project-based leadership challenges.</p>',
   'https://makueniyouth.org/wp-content/uploads/2025/05/talent.jpg', 20, 'published'),

  (3, NULL, 'education-capacity-enhancement',
   'Education & Capacity Enhancement',
   'We assess learning outcomes and gender gaps, then turn evidence into action — lobbying duty bearers for responsive policy and budgets.',
   '<p>We run county-wide Foundational Literacy &amp; Numeracy Assessments in partnership with Usawa Agenda and Zizi Afrique. The data fuels our policy briefs to the county education department.</p>',
   'https://makueniyouth.org/wp-content/uploads/2025/05/Educational.jpg', 30, 'published'),

  -- Children of Education & Capacity Enhancement
  (4, 3, 'foundational-literacy-numeracy-assessment',
   'Foundational Literacy & Numeracy Assessment',
   'Annual household-based assessment of learning levels across Makueni — the evidence base for our education advocacy.',
   '<p>Our annual FLNA brings together volunteer assessors, teachers and ward officers to measure what 6–16 year olds can actually read and compute. Results inform the County Education Sector Plan.</p>',
   NULL, 31, 'published'),

  (5, 2, 'youth-mentorship',
   'Youth Mentorship',
   'One-on-one mentor matching connecting young people with vetted professionals from across the county and diaspora.',
   '<p>Our mentorship cohort runs twice a year, pairing 18–30 year olds with mentors in their field of interest — from agribusiness to policy to creative arts.</p>',
   NULL, 21, 'published');

-- ---------------------------------------------------------------------------
-- categories
-- ---------------------------------------------------------------------------
INSERT INTO categories (id, slug, name) VALUES
  (1, 'governance', 'Governance'),
  (2, 'advocacy',   'Advocacy'),
  (3, 'education',  'Education');

-- ---------------------------------------------------------------------------
-- posts (impact stories)
-- ---------------------------------------------------------------------------
INSERT INTO posts (slug, title, excerpt, body, cover_image, category_id, author_id, status, published_at) VALUES
  ('bridging-the-gap-youth-leading-change-in-governance',
   'Bridging the Gap: Youth Leading Change in Governance & Accountability',
   'How the Makueni Youth initiative is driving inclusive governance by bringing marginalized voices into county decision-making spaces.',
   '<p>For too long, county budget hearings in Makueni were attended by a familiar cast of faces — none of them young. This year that changed.</p><p>Through our Advocacy &amp; Civic Engagement program, we trained 120 youth representatives across the ten sub-counties on the County Government Act, the Public Finance Management Act and the participation guidelines. They showed up. They asked sharp questions. They submitted memos that ended up cited in the final appropriations report.</p>',
   'https://makueniyouth.org/wp-content/uploads/2026/05/Youth-Gov-300x224.webp',
   1, 1, 'published', '2026-05-20 09:00:00'),

  ('empowering-youth-advocating-for-rights',
   'Empowering Youth: Advocating for Rights & Sustainable Futures',
   'A network of trained youth advocates is reshaping how Makueni County engages its young population on land, livelihoods and the climate transition.',
   '<p>Our 2026 cohort of youth advocates concluded a six-month curriculum on rights-based advocacy — covering land tenure, climate adaptation finance and digital rights.</p>',
   'https://makueniyouth.org/wp-content/uploads/2026/05/Makueni4-300x200.jpg',
   2, 1, 'published', '2026-05-19 09:30:00'),

  ('bridging-gaps-through-education',
   'Bridging Gaps Through Education, Knowledge & Research',
   'Findings from our Foundational Literacy and Numeracy Assessment are driving real-time policy conversations at the county Department of Education.',
   '<p>This year''s FLNA covered 4,200 households across all ten sub-counties. The headline finding: only 38% of Grade 4 learners could read a Grade 2 story fluently. The county''s response — a remedial reading clinic pilot in three wards — was directly informed by our brief.</p>',
   'https://makueniyouth.org/wp-content/uploads/2026/05/MAkueni-Y-300x200.jpg',
   3, 1, 'published', '2026-05-15 10:00:00'),

  ('youth-mentorship-cohort-2026-launch',
   'Youth Mentorship Cohort 2026 Launches in Wote',
   'Forty-five young Makueni residents have been matched with mentors from across business, public service and the creative industry.',
   '<p>The 2026 cohort kicked off on a sunny Saturday in Wote with mentor-mentee pairing sessions, mini-workshops on personal branding, and a fireside chat with three returning alumni.</p>',
   NULL,
   2, 1, 'published', '2026-04-12 11:00:00');

-- ---------------------------------------------------------------------------
-- stats (homepage stripe)
-- ---------------------------------------------------------------------------
INSERT INTO stats (id, label, value, sort_order) VALUES
  (1, 'Founded as a community-based organization', '2014',  10),
  (2, 'Flagship programs across the county',       '3',     20),
  (3, 'Partner institutions & funders',            '6+',    30),
  (4, 'Young people reached & mobilized',          '1000s', 40);

-- ---------------------------------------------------------------------------
-- partners (logo grid)
-- ---------------------------------------------------------------------------
INSERT INTO partners (id, name, logo, url, sort_order) VALUES
  (1, 'KCDF',                          NULL, 'https://kcdf.or.ke',          10),
  (2, 'Usawa Agenda',                  NULL, 'https://usawaagenda.org',     20),
  (3, 'Zizi Afrique',                  NULL, 'https://ziziafrique.org',     30),
  (4, 'Africa Voices',                 NULL, 'https://africasvoices.org',   40),
  (5, 'Poverty Eradication Network',   NULL, NULL,                          50),
  (6, 'EYC',                           NULL, NULL,                          60);

-- ---------------------------------------------------------------------------
-- pages (long-form static-ish pages)
-- ---------------------------------------------------------------------------
INSERT INTO pages (slug, title, body, meta_desc, hero_image, status) VALUES
  ('about',
   'About Makueni Youth Network',
   '<p>Makueni Youth Network CBO was founded in 2014 to build a movement of young people committed to driving positive change across social, educational, political and economic spaces.</p><p>We believe young people are transformative leaders. Our mission is to mobilize, collaborate, empower and transform youth to actively engage in decision-making — increasing representation and grassroots civic action.</p><h2>Our Vision</h2><p>A just community where young people enjoy dignified lives.</p><h2>Our Mission</h2><p>To enhance the purpose, rights and talents of young people as active citizens and agents of change.</p>',
   'Youth-led community-based organization in Wote, Makueni County, founded 2014.',
   NULL, 'published'),

  ('contact',
   'Get in touch',
   '<p>Reach us at Famo House, 2nd Floor, Room 14, behind Equity Bank in Wote Town. Call <a href="tel:+254710580604">+254 710 580 604</a> or email <a href="mailto:info@makueniyouth.org">info@makueniyouth.org</a>.</p>',
   'Contact Makueni Youth Network — Wote, Makueni County.',
   NULL, 'published'),

  ('donate',
   'Donate to Makueni Youth Network',
   '<p>Every donation funds civic training, mentorship and advocacy across Makueni County. Donations are processed securely via Paystack — card, mobile money or bank transfer.</p>',
   'Donate to support youth-led civic action in Makueni County.',
   NULL, 'published'),

  ('volunteer',
   'Volunteer with us',
   '<p>We are always looking for volunteers — assessors for our Foundational Literacy &amp; Numeracy work, mentors for the 2026 cohort, and event organisers for our county forums.</p>',
   'Volunteer with Makueni Youth Network in Wote, Makueni County.',
   NULL, 'published');

-- ---------------------------------------------------------------------------
-- settings (consumed by the `site` Twig global)
-- ---------------------------------------------------------------------------
INSERT INTO settings (setting_key, setting_value) VALUES
  ('name',     'Makueni Youth Network'),
  ('tagline',  'Youth-owned. Youth-led. Youth-driven.'),
  ('phone',    '+254 710 580 604'),
  ('email',    'info@makueniyouth.org'),
  ('address',  'Famo House, 2nd Flr, Rm 14, Behind Equity Bank, Wote Town'),
  ('po_box',   'P.O Box 405 – 90300, Wote, Makueni'),
  ('facebook',    'https://www.facebook.com/MakueniYouthNetwork'),
  ('twitter',     'https://twitter.com/MakueniYouth'),
  ('linkedin',    'https://www.linkedin.com/company/makueni-youth-network'),
  ('logo',        '/assets/img/logo.png'),
  ('logo_square', '/assets/img/logo-square.png');
