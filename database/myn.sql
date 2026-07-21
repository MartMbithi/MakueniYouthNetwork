-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2026 at 11:37 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `myn`
--

-- --------------------------------------------------------

--
-- Table structure for table `advocacy_content`
--

CREATE TABLE `advocacy_content` (
  `id` int(10) UNSIGNED NOT NULL,
  `content_type` enum('campaign','opportunity','resource','media') NOT NULL,
  `slug` varchar(180) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `body` longtext DEFAULT NULL,
  `category` varchar(120) DEFAULT NULL,
  `organization` varchar(180) DEFAULT NULL,
  `location` varchar(180) DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `external_url` varchar(500) DEFAULT NULL,
  `file_url` varchar(500) DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `name` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `slug`, `name`) VALUES
(1, 'governance', 'Governance'),
(2, 'advocacy', 'Advocacy'),
(3, 'education', 'Education');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `donor_name` varchar(160) DEFAULT NULL,
  `donor_phone` varchar(40) DEFAULT NULL,
  `donor_email` varchar(190) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'KES',
  `provider` varchar(40) NOT NULL DEFAULT 'paystack',
  `channel` varchar(40) DEFAULT NULL,
  `reference` varchar(120) NOT NULL,
  `paystack_id` bigint(20) DEFAULT NULL,
  `gateway_response` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed','abandoned') DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `title` varchar(220) NOT NULL,
  `description` mediumtext DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `venue` varchar(220) DEFAULT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(10) UNSIGNED NOT NULL,
  `question` varchar(500) NOT NULL,
  `answer` longtext NOT NULL,
  `category` varchar(120) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('draft','published') NOT NULL DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(160) NOT NULL,
  `email` varchar(190) NOT NULL,
  `subject` varchar(220) DEFAULT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `name`, `email`, `subject`, `body`, `is_read`, `created_at`) VALUES
(1, 'Test Person', 'test@example.com', 'Test from curl', 'Verification submission from M3.5 acceptance run.', 1, '2026-05-22 10:31:39');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(190) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `status` enum('active','unsubscribed') NOT NULL DEFAULT 'active',
  `subscribed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` mediumtext DEFAULT NULL,
  `meta_desc` varchar(300) DEFAULT NULL,
  `hero_image` varchar(255) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `slug`, `title`, `body`, `meta_desc`, `hero_image`, `status`, `updated_at`) VALUES
(1, 'about', 'About Makueni Youth Network', '<p>Makueni Youth Network CBO was founded in 2014 to build a movement of young people committed to driving positive change across social, educational, political and economic spaces.</p><p>We believe young people are transformative leaders. We mobilize, collaborate, empower and transform youth to actively engage in decision-making — increasing representation and grassroots civic action across the county.</p>', 'Youth-led community-based organization in Wote, Makueni County, founded 2014.', NULL, 'published', '2026-06-13 11:36:26'),
(2, 'contact', 'Get in touch', '<p>Reach us at Famo House, 2nd Floor, Room 14, behind Equity Bank in Wote Town. Call <a href=\"tel:+254710580604\">+254 710 580 604</a> or email <a href=\"mailto:info@makueniyouth.org\">info@makueniyouth.org</a>.</p>', 'Contact Makueni Youth Network — Wote, Makueni County.', NULL, 'published', '2026-05-22 12:43:32'),
(3, 'donate', 'Donate to Makueni Youth Network', '<p>Every donation funds civic training, mentorship and advocacy across Makueni County. Donations are processed securely via Paystack — card, mobile money or bank transfer.</p>', 'Donate to support youth-led civic action in Makueni County.', NULL, 'published', '2026-05-22 12:43:32'),
(4, 'volunteer', 'Volunteer with us', '<p>We are always looking for volunteers — assessors for our Foundational Literacy &amp; Numeracy work, mentors for the 2026 cohort, and event organisers for our county forums.</p>', 'Volunteer with Makueni Youth Network in Wote, Makueni County.', NULL, 'published', '2026-05-22 12:43:32');

-- --------------------------------------------------------

--
-- Table structure for table `page_views`
--

CREATE TABLE `page_views` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `path` varchar(255) NOT NULL,
  `viewed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `page_views`
--

INSERT INTO `page_views` (`id`, `path`, `viewed_at`) VALUES
(1, '/media', '2026-07-21 10:56:58'),
(2, '/search', '2026-07-21 11:19:27'),
(3, '/search', '2026-07-21 11:19:31'),
(4, '/search', '2026-07-21 11:25:16'),
(5, '/faq', '2026-07-21 11:26:51'),
(6, '/campaigns', '2026-07-21 11:27:10'),
(7, '/opportunities', '2026-07-21 11:27:36'),
(8, '/resources', '2026-07-21 11:27:48'),
(9, '/media', '2026-07-21 11:28:00'),
(10, '/media', '2026-07-21 11:28:16'),
(11, '/faq', '2026-07-21 11:47:25'),
(12, '/campaigns', '2026-07-21 11:47:38'),
(13, '/opportunities', '2026-07-21 11:47:59'),
(14, '/resources', '2026-07-21 11:48:09'),
(15, '/media', '2026-07-21 11:48:22'),
(16, '/search', '2026-07-21 11:48:50');

-- --------------------------------------------------------

--
-- Table structure for table `partners`
--

CREATE TABLE `partners` (
  `id` int(11) NOT NULL,
  `name` varchar(160) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `partners`
--

INSERT INTO `partners` (`id`, `name`, `logo`, `url`, `sort_order`) VALUES
(1, 'KCDF', '/uploads/9a18b8481c8c079302dd.jpg', 'https://kcdf.or.ke', 10),
(2, 'Usawa Agenda', '/uploads/3370a4c3e112a3d48372.jpg', 'https://usawaagenda.org', 20),
(3, 'Zizi Afrique', '/uploads/5dd493db427de1904fd2.jpg', 'https://ziziafrique.org', 30),
(4, 'Africa Voices', '/uploads/ffade0d82cae25152308.svg', 'https://africasvoices.org', 40),
(5, 'Poverty Eradication Network', '/uploads/9fff93320e07e129760d.jpg', NULL, 50),
(6, 'EYC', '/uploads/9394fb98152154786d37.jpg', NULL, 60);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `title` varchar(220) NOT NULL,
  `excerpt` varchar(400) DEFAULT NULL,
  `body` mediumtext DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `slug`, `title`, `excerpt`, `body`, `cover_image`, `category_id`, `author_id`, `status`, `published_at`, `created_at`) VALUES
(8, 'youth-leading-change', 'Youth Leading Change', 'Makueni County is appraised, both locally and internationally, as one of the best devolved units that has enacted policies that give attention to community needs and priorities. But in practice, […]', '\n<p class=\"wp-block-paragraph\">Makueni County is appraised, both locally and internationally, as one of the best devolved units that has enacted policies that give attention to community needs and priorities. But in practice, political influence, control by the elites, targeted mobilization, staged representation, and unwillingness of the government officials to give up control over project activities are some of the key challenges to effective public participation. Recently, there have been low turnouts of women, youth and PWDs during government organized public participations as they have realized that the government doesn’t care for their needs.</p>\n\n\n\n<p class=\"wp-block-paragraph\">When Makueni Youth Network received KCDF’s Giving for Change (GfC) grants aimed at supporting for our project dubbed “<em>Youth Leading Change in Enhancing Democracy and Social Accountability in Makueni County</em>”, our push was to transition more youth, women and PWDs to become agents of change. We first mapped and trained 30 youth/women/PWDs-Led groups from the 30 wards of Makueni County to be part of our grassroots implementers. Also from the KCDF’s Giving for Change (GfC) grants that supports Makueni Community of Practice, we mapped and trained other 30 grassroots CSOs to be part of our foot soldiers.</p>\n\n\n\n<p class=\"wp-block-paragraph\">So, whenever we see a call from the government inviting members of the public to attend public participation or requesting them to submit their proposals, we circulate such information among our grassroots partners and request those with interest to communicate back for support. Two kinds of support: (1) we either support those groups who want to convene before the day of public participation to deliberate on the best approach or develop a submission; and or (2) support with fares (20-30 members of each of the interested groups) to attend the scheduled public participation to submit the written proposals or participate meaningfully. This practice has proven to yield positive results as more informed youth, women and PWDs are now attending these public participations to submit their proposals.</p>\n\n\n\n<p class=\"wp-block-paragraph\">A good example is recently when we saw a print notice by the government of Makueni inviting individuals and CSOs to participate in the cluster, subwards and ward elections of Development committees (between 11 Feb 2025 and 25<sup>th</sup> Feb 2025), as usual, we notified our members prepare in advance. The idea was to support 20-30 members of each of our grassroots partners with the fares or lunch to attend and participate in the cluster elections. In each level of elections, members of the public had to contest for 11 positions: Community Resource Volunteer; Climate Change and Environment Champion; Water and Sanitation Sector Champion; Women Representative; Youth Representative female; Youth representative male; Agriculture Sector Champion; Community Health Promoter; Faith Based Organization Representative; PWD representative; Business Representative; and Road sector representative.</p>\n\n\n\n<p class=\"wp-block-paragraph\">Despite the short notice, we saw it as a critical opportunity for Makueni Youth Network’s “Youth Leading Change” and Makueni COP. With direct support from KCDF, we convened a county-wide meeting for the 6 subcounty coordinators and 30 ward coordinators to agree on the best strategy. During the meeting, we invite the Director in charge of Makueni Public Participation, who shared with us the scope of contested positions.</p>\n\n\n\n<p class=\"wp-block-paragraph\"><strong>RESULTS:</strong></p>\n\n\n\n<ol class=\"wp-block-list\">\n<li>21 grassroots partners for Makueni <em>Youth Leading Change</em> were able to participate and secured 74 cluster seats out of 341 seats in those 31 clusters, <strong>which translates to 32%</strong></li>\n\n\n\n<li>Only 9 groups for Makueni CoP were able to participate (Due to budget limits), and secured 12 positions out of 99 contested seats, thus translating to 12%</li>\n</ol>\n\n\n\n<p class=\"wp-block-paragraph\"><strong>CONCLUSION:</strong></p>\n\n\n\n<p class=\"wp-block-paragraph\">Makueni Youth Network’s “<em>Youth Leading Change in Enhancing Democracy and Social Accountability in Makueni County</em>” project, which is fully financed by KCDF’ Giving for Change (GfC) grants is ultimately creating real change, where marginalized groups are not just attending public participation to coronate other members of community, but they are empowered and supported to take up leadership positions so as to be part of the key decision-makers.</p>\n', '/uploads/1ca6ae0fd4e523225fa0.jpg', NULL, NULL, 'published', '2025-05-28 16:03:03', '2026-05-22 14:15:14'),
(9, 'bridging-gaps-through-education-knowledge-development-and-research', 'Bridging Gaps Through Education, Knowledge Development, and Research', 'At Makueni Youth Network, we understand the importance of reliable data in addressing the multifaceted challenges facing young people today. Unfortunately, youth issues have often been inadequately addressed by governments […]', '\n<p class=\"wp-block-paragraph\">At Makueni Youth Network, we understand the importance of reliable data in addressing the multifaceted challenges facing young people today. Unfortunately, youth issues have often been inadequately addressed by governments and development agencies due to a lack of accurate and comprehensive data on youth dynamics. To address this gap, we have made it our mission to partner with credible research organizations to generate current and precise information about youth across diverse sectors, including health, agriculture, education, employment, business, access to finance, and youth participation in democratic processes.</p>\n\n\n\n<h4 class=\"wp-block-heading\">Collaborating for Data-Driven Solutions</h4>\n\n\n\n<p class=\"wp-block-paragraph\">Our partnerships with esteemed organizations such as Zizi Afrique, Uwezo Twaweza, Usawa Agenda, Poverty Eradication Network, ActionAid, and Elimu Yetu Coalition are central to our knowledge-building efforts. These collaborations enable us to develop actionable insights that inform policies, programs, and initiatives aimed at uplifting the youth. By fostering evidence-based decision-making, we contribute to more targeted and effective solutions that address the root causes of challenges faced by young people.</p>\n\n\n\n<h4 class=\"wp-block-heading\">Promoting Inclusive Education and Social Equity</h4>\n\n\n\n<p class=\"wp-block-paragraph\">Education is a powerful tool for transformation, and we are committed to ensuring it is accessible to all, particularly children from marginalized and disadvantaged backgrounds and People With Disabilities (PWDs). Through our partnerships, we advocate for and implement programs that improve access to inclusive education, breaking down barriers that have historically excluded vulnerable groups.</p>\n\n\n\n<p class=\"wp-block-paragraph\">Our initiatives emphasize social inclusion for young people with disabilities, ensuring they not only benefit from opportunities for accelerated learning but also gain critical career and life skills. By equipping them with the tools they need to thrive, we help build a society where everyone, regardless of their circumstances, has a fair chance to succeed.</p>\n\n\n\n<h4 class=\"wp-block-heading\">Empowering Youth Through Knowledge and Research</h4>\n\n\n\n<p class=\"wp-block-paragraph\">By prioritizing knowledge development and research, we are empowering young people to be active participants in shaping their futures. Accurate data and inclusive education initiatives are just the beginning. We envision a world where every young person has access to the information and resources needed to make informed decisions, pursue their aspirations, and contribute meaningfully to their communities.</p>\n\n\n\n<p class=\"wp-block-paragraph\">Through education, research, and partnerships, Makueni Youth Network is creating a brighter future for youth. Together, we can ensure that no one is left behind as we work toward a more equitable and prosperous society.</p>\n', '/uploads/dc342d8482baf5c3e921.jpg', NULL, NULL, 'published', '2025-05-28 16:05:06', '2026-05-22 14:15:15'),
(10, 'empowering-youth-advocating-for-rights-and-sustainable-futures-in-kenya', 'Empowering Youth: Advocating for Rights and Sustainable Futures in Kenya', 'In Kenya, young people form the backbone of our society—vibrant, innovative, and full of potential. Yet, they face disproportionate challenges stemming from the mismanagement of public resources, institutions, and processes. […]', '\n<p class=\"wp-block-paragraph\">In Kenya, young people form the backbone of our society—vibrant, innovative, and full of potential. Yet, they face disproportionate challenges stemming from the mismanagement of public resources, institutions, and processes. These systemic issues create barriers to accessing fundamental rights and opportunities. At the core of our advocacy efforts is a steadfast belief: young people must fully enjoy their human rights while actively participating in shaping their social, economic, educational, and political landscapes.</p>\n\n\n\n<h4 class=\"wp-block-heading\">Advancing Youth Rights and Freedom</h4>\n\n\n\n<p class=\"wp-block-paragraph\">Our mission is rooted in the conviction that democratic, accountable, and inclusive systems are not only essential for youth development but also critical for transforming communities and achieving sustainable development. When young people have meaningful opportunities to engage in governance, they become powerful agents of change. Their involvement can bridge the gaps in access to public goods and significantly enhance service delivery at all levels.</p>\n\n\n\n<p class=\"wp-block-paragraph\">We are committed to amplifying the voices of conscious young individuals who are eager to contribute to building equitable and thriving communities. By addressing the inequalities that hinder youth participation, we envision a future where every young person can harness their potential and contribute to nation-building.</p>\n\n\n\n<h4 class=\"wp-block-heading\">Building Healthy and Inclusive Communities</h4>\n\n\n\n<p class=\"wp-block-paragraph\">A key aspect of our advocacy involves fostering inclusive education systems that empower young people to build sustainable and resilient livelihoods. Education is not just a pathway to personal growth; it is a cornerstone for creating healthy and progressive societies. Our work emphasizes ensuring that education systems are accessible and inclusive, particularly for marginalized groups.</p>\n\n\n\n<p class=\"wp-block-paragraph\">We also prioritize the improvement of health services, with a special focus on sexual and reproductive health. By advocating for equitable access to family planning and modern contraceptives, we aim to address disparities faced by adolescents, people with disabilities, and underserved rural youth and women. Access to these services is critical for reducing inequalities and supporting young people in making informed decisions about their futures.</p>\n\n\n\n<h4 class=\"wp-block-heading\">A Vision for the Future</h4>\n\n\n\n<p class=\"wp-block-paragraph\">Our efforts are driven by the belief that empowering young people is the key to achieving sustainable development. By advocating for policies and systems that prioritize youth rights, health, and education, we aim to create an environment where young people can thrive. Together, we can transform our communities and ensure that no one is left behind.</p>\n\n\n\n<p class=\"wp-block-paragraph\">As we look to the future, we remain committed to working alongside young people, amplifying their voices, and advocating for their rights. The journey to a more inclusive and equitable Kenya starts with recognizing the immense potential of our youth and taking deliberate steps to support their growth and development.</p>\n', '/uploads/e7f40c196006c5f6eeef.jpg', NULL, NULL, 'published', '2025-05-28 16:06:52', '2026-05-22 14:15:16'),
(11, 'bridging-the-gap-how-makueni-youth-initiative-is-driving-inclusive-governance', 'Bridging the Gap: Youth Leading Change in Governance and Accountability.', 'In Kenya, the principles of devolution promise greater public participation, equitable development, and localized decision-making. Yet, for many young people, women, and persons with disabilities (PWDs) in Makueni County, those […]', '<p class=\"wp-block-paragraph\">In Kenya, the principles of devolution promise greater public participation, equitable development, and localized decision-making. Yet, for many young people, women, and persons with disabilities (PWDs) in Makueni County, those promises have remained just that—promises. Governance processes often feel distant, complex, or dominated by political elites, making it difficult for ordinary citizens to influence how their county is run.</p>\r\n\r\n\r\n\r\n<p class=\"wp-block-paragraph\">Makueni Youth Network (MYN) implements  a project dubbed as”<strong><em>Youth Leading Change</em></strong> <strong>in governance and accountability</strong>,” supported by <a href=\"https://kcdf.or.ke/\">Kenya Community Development Foundation (KCDF)</a> that supports the mobilization, capacity-building and mentorship of young people from Makueni to confront systemic barriers that confront the participation of youth, women and PWDs in democracy and accountability. By mobilizing, empowering, mentoring, and supporting financially, marginalized groups to participate meaningfully in public planning and decision-making, youth from Makueni are building a more transparent, accountable, and people-centered county government.  </p>\r\n\r\n\r\n\r\n<h3 class=\"wp-block-heading\">Empowering the Margins to Lead change</h3>\r\n\r\n\r\n\r\n<p class=\"wp-block-paragraph\">Traditionally, the participation of youth, women, and PWDs in governance and accountability has been hindered by deep-rooted social, structural and cultural barriers. Issues like nepotism, patronage, poor access to information, and the reluctance of public officials to devolve real power often silence the voices of those at the margins. </p>\r\n\r\n\r\n\r\n<p class=\"wp-block-paragraph\"><strong><em>Youth Leading Change in governance and accountability</em></strong> is tackling these issues head-on by <strong>mobilizing, training, and financially supporting grassroots groups</strong> led by youth, women, and PWDs. These groups are equipped not only with the right tools to understand how county governance works, but also how to meaningfully engage in it. They are coached on how to draft proposals, navigate public participation forums, submit memoranda and petitions, and demand accountability from duty bearers.</p>\r\n\r\n\r\n\r\n<h3 class=\"wp-block-heading\">Public Participation That Actually Works</h3>\r\n\r\n\r\n\r\n<p class=\"wp-block-paragraph\">One of the most powerful tools in the devolved system is public participation. But without proper awareness and access, it becomes a mere formality. MYN is shifting this narrative by supporting grassroots mobilization and information sharing before the D-Day of public participation so informed young people can attend these public participation and contribute meaningfully.</p>\r\n\r\n\r\n\r\n<p class=\"wp-block-paragraph\">In partnership with local youth-led organizations, the project identifies critical entry points where youth and marginalized voices can contribute meaningfully—whether it’s during the formulation of county development plans, budget consultations, or legislative reviews. By supporting these groups to participate collectively, MYN ensures that youth voices carry weight and are not drowned out by more powerful actors.</p>\r\n\r\n\r\n\r\n<p class=\"wp-block-paragraph\">In several wards, this has led to the successful inclusion of community-prioritized projects in county budgets—ranging from youth resource centers to inclusive healthcare services. These are not just wins on paper; they are tangible improvements in people’s lives.</p>\r\n\r\n\r\n\r\n<h3 class=\"wp-block-heading\">Training for Sustainable Impact</h3>\r\n\r\n\r\n\r\n<p class=\"wp-block-paragraph\">Capacity building is at the heart of MYN’s governance and accountability efforts. Participants are trained on <strong>civic rights, public finance management, policy advocacy, and legal frameworks</strong>, giving them a deeper understanding of their roles as citizens. The Initiative also emphasizes soft skills like public speaking, negotiation, and leadership—crucial for navigating and influencing political spaces. </p>\r\n\r\n\r\n\r\n<p class=\"wp-block-paragraph\">This kind of comprehensive training has resulted in a growing number of young people and women taking up leadership positions in Makueni County Community Development Committees, Project Development Committees, civil society spaces, and even planning vying for political offices in the coming 2027 general elections. The message is clear: <strong>when you invest in young people, they can lead change.</strong> </p>\r\n\r\n\r\n\r\n<h3 class=\"wp-block-heading\">Holding Duty Bearers Accountable</h3>\r\n\r\n\r\n\r\n<p class=\"wp-block-paragraph\">Beyond participation, MYN plays a critical role in promoting <strong>transparency and accountability</strong>. Through its grassroots network of youth-led self-Help Groups, we monitor the implementation of county projects, intensify the fight against corruption or mismanagement of public resources, and engage duty bearers to account for their actions. In some cases, these efforts have led to halted contracts, project audits, or reallocation of resources to more pressing community needs.</p>\r\n\r\n\r\n\r\n<p class=\"wp-block-paragraph\">Moreover, MYN uses data-driven approaches to back up its trainings and advocacy. Reports, surveys, and community scorecards are compiled and shared publicly to inform both citizens and decision-makers. By <strong>bringing facts to the forefront</strong>, the MYN strengthens its position as a credible watchdog and a constructive partner in governance.</p>\r\n\r\n\r\n\r\n<h3 class=\"wp-block-heading\">Collaboration for Greater Reach</h3>\r\n\r\n\r\n\r\n<p class=\"wp-block-paragraph\">The success of MYN’s inclusive governance work is also due to its <strong>strong partnerships</strong> and funding from the <a href=\"https://kcdf.or.ke/\">Kenya Community Development Foundation</a>. MYN also collaborates with national and regional organizations such as <a href=\"https://www.africasvoices.org/\">Africa’s Voices Foundation</a>, <a href=\"https://usawaagenda.org/\">Usawa Agenda</a>, <a href=\"https://ziziafrique.org/\">Zizi Afrique Foundation</a>, and other civil society actors. These collaborations bring in technical expertise, resources, mentorship, and broader platforms to amplify local voices.</p>\r\n\r\n\r\n\r\n<p class=\"wp-block-paragraph\">Importantly, MYN doesn’t position itself as the only actor. It continuously builds alliances with <strong>county government departments, local administrators, and community leaders</strong>, helping create a culture of co-ownership and trust. This approach has contributed to a more responsive and youth-friendly governance environment in many parts of Makueni County.</p>\r\n\r\n\r\n\r\n<h3 class=\"wp-block-heading\">A Model for the Future</h3>\r\n\r\n\r\n\r\n<p class=\"wp-block-paragraph\">What the Makueni Youth Network demonstrates is that inclusive governance is not just desirable—it is achievable. When youth, women, and PWDs are given the tools, support, and space to engage, they don’t just participate—they lead. They challenge the status quo, advocate for fairer policies, and become the stewards of their own development.</p>\r\n\r\n\r\n\r\n<p class=\"wp-block-paragraph\">In a time when many citizens feel disillusioned with politics, the work of MYN is a refreshing reminder that <strong>democracy works best when everyone has a seat at the table.</strong> And in Makueni County, that table is getting larger, more diverse, and more powerful—thanks to the efforts of youth who believe in something bigger than themselves.</p>\r\n\r\n\r\n\r\n<hr class=\"wp-block-separator has-alpha-channel-opacity\"/>\r\n\r\n\r\n\r\n<p class=\"wp-block-paragraph\"><strong>Are you ready to be part of the movement?</strong><br>Join Makueni Youth Network and help build a future where young people’s voice counts—no matter their age, gender, or ability.</p>\r\n', '/uploads/c26c3d8dceaaa71be4b4.webp', NULL, NULL, 'published', '2025-05-28 16:16:00', '2026-05-22 14:15:17');

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `slug` varchar(160) NOT NULL,
  `title` varchar(200) NOT NULL,
  `summary` varchar(400) DEFAULT NULL,
  `body` mediumtext DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('draft','published') DEFAULT 'published'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `parent_id`, `slug`, `title`, `summary`, `body`, `cover_image`, `sort_order`, `status`) VALUES
(1, NULL, 'advocacy-civic-engagement', 'Advocacy & Civic Engagement', 'We equip young people with civic knowledge and confidence to shape county planning, budgets and policy — and to hold leaders accountable.', '		<div data-elementor-type=\"wp-page\" data-elementor-id=\"332\" class=\"elementor elementor-332\">\n				<div class=\"elementor-element elementor-element-87a1ba3 e-flex e-con-boxed e-con e-parent\" data-id=\"87a1ba3\" data-element_type=\"container\" data-e-type=\"container\" data-settings=\"{&quot;background_background&quot;:&quot;classic&quot;}\">\n					<div class=\"e-con-inner\">\n				<div class=\"elementor-element elementor-element-e9b57fb elementor-widget elementor-widget-image\" data-id=\"e9b57fb\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"image.default\">\n				<div class=\"elementor-widget-container\">\n															<img decoding=\"async\" src=\"/uploads/f186de838c9daac2d88c.jpg\" title=\"\" alt=\"\" loading=\"lazy\" />															</div>\n				</div>\n				<div class=\"elementor-element elementor-element-a9d7f08 elementor-widget elementor-widget-heading\" data-id=\"a9d7f08\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"heading.default\">\n				<div class=\"elementor-widget-container\">\n					<h2 class=\"elementor-heading-title elementor-size-default\">Advocacy & Civic Education</h2>				</div>\n				</div>\n				<div class=\"elementor-element elementor-element-8085530 elementor-widget elementor-widget-text-editor\" data-id=\"8085530\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"text-editor.default\">\n				<div class=\"elementor-widget-container\">\n									<p>Makueni Youth Network (MYN) promotes meaningful youth participation in county planning, policy formulation, and public finance processes in Makueni County.</p><p data-start=\"621\" data-end=\"1186\">MYN empowers youth with the knowledge and skills necessary to exercise their civic duty effectively. We also equip young people to understand governance structures, public finance processes, and policy development cycles—enabling them to actively engage in county planning forums, public participation spaces, and budget-making processes. Our approach goes beyond awareness; we support youth to organize, articulate evidence-based priorities, and strategically advocate for their inclusion in government policies, programs, and budgets.</p><p data-start=\"1188\" data-end=\"1612\">We also facilitate structured engagement between youth and government institutions to ensure that youth priorities are integrated into County Integrated Development Plans (CIDPs), annual development plans, and sectoral policies. We strengthen youth-led accountability initiatives that track public expenditure and monitor implementation of development commitments.</p><p data-start=\"1614\" data-end=\"1990\">Finally, the program also nurtures a new generation of civic leaders by building advocacy coalitions, supporting youth-inclusive policies, and creating safe and inclusive spaces where young people can dialogue directly with decision-makers. By strengthening youth voices at the policy table, MYN is contributing to transparent governance, responsive budgeting, and inclusive development.</p>								</div>\n				</div>\n					</div>\n				</div>\n		<div class=\"elementor-element elementor-element-4dba100 e-flex e-con-boxed e-con e-parent\" data-id=\"4dba100\" data-element_type=\"container\" data-e-type=\"container\" data-settings=\"{&quot;background_background&quot;:&quot;classic&quot;}\">\n					<div class=\"e-con-inner\">\n		<div class=\"elementor-element elementor-element-0273870 e-con-full e-flex e-con e-child\" data-id=\"0273870\" data-element_type=\"container\" data-e-type=\"container\">\n				<div class=\"elementor-element elementor-element-c8d57bd elementor-widget elementor-widget-image\" data-id=\"c8d57bd\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"image.default\">\n				<div class=\"elementor-widget-container\">\n															<img decoding=\"async\" src=\"/uploads/959e9d77d6287e8697e9.jpg\" title=\"\" alt=\"\" loading=\"lazy\" />															</div>\n				</div>\n				</div>\n		<div class=\"elementor-element elementor-element-68ae004 e-con-full e-flex e-con e-child\" data-id=\"68ae004\" data-element_type=\"container\" data-e-type=\"container\">\n				<div class=\"elementor-element elementor-element-745d98d elementor-widget elementor-widget-image\" data-id=\"745d98d\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"image.default\">\n				<div class=\"elementor-widget-container\">\n															<img decoding=\"async\" src=\"/uploads/b92604c41ab243d57680.jpg\" title=\"\" alt=\"\" loading=\"lazy\" />															</div>\n				</div>\n				</div>\n		<div class=\"elementor-element elementor-element-6fff960 e-con-full e-flex e-con e-child\" data-id=\"6fff960\" data-element_type=\"container\" data-e-type=\"container\">\n				<div class=\"elementor-element elementor-element-2842b50 elementor-widget elementor-widget-image\" data-id=\"2842b50\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"image.default\">\n				<div class=\"elementor-widget-container\">\n															<img decoding=\"async\" src=\"/uploads/ea9c1486d9f73d8b8225.jpg\" title=\"\" alt=\"\" loading=\"lazy\" />															</div>\n				</div>\n				</div>\n		<div class=\"elementor-element elementor-element-7d47b7c e-con-full e-flex e-con e-child\" data-id=\"7d47b7c\" data-element_type=\"container\" data-e-type=\"container\">\n				<div class=\"elementor-element elementor-element-e4d1530 elementor-widget elementor-widget-image\" data-id=\"e4d1530\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"image.default\">\n				<div class=\"elementor-widget-container\">\n															<img decoding=\"async\" src=\"/uploads/fe3314b3b0dff7eecfa5.jpg\" title=\"\" alt=\"\" loading=\"lazy\" />															</div>\n				</div>\n				</div>\n		<div class=\"elementor-element elementor-element-34f2227 e-con-full e-flex e-con e-child\" data-id=\"34f2227\" data-element_type=\"container\" data-e-type=\"container\">\n				<div class=\"elementor-element elementor-element-4dbfe21 elementor-widget elementor-widget-image\" data-id=\"4dbfe21\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"image.default\">\n				<div class=\"elementor-widget-container\">\n															<img decoding=\"async\" src=\"/uploads/8fb4d6b83df11b5732ba.jpg\" title=\"\" alt=\"\" loading=\"lazy\" />															</div>\n				</div>\n				</div>\n					</div>\n				</div>\n				</div>\n		', '/uploads/79f9adfafb7e1d5b88d6.jpg', 10, 'published'),
(2, NULL, 'leadership-talent-development', 'Leadership & Talent Development', 'Structured training and mentorship that nurtures confident, values-driven leaders ready to take up roles in their communities.', '		<div data-elementor-type=\"wp-page\" data-elementor-id=\"351\" class=\"elementor elementor-351\">\n				<div class=\"elementor-element elementor-element-87a1ba3 e-flex e-con-boxed e-con e-parent\" data-id=\"87a1ba3\" data-element_type=\"container\" data-e-type=\"container\" data-settings=\"{&quot;background_background&quot;:&quot;classic&quot;}\">\n					<div class=\"e-con-inner\">\n				<div class=\"elementor-element elementor-element-e9b57fb elementor-widget elementor-widget-image\" data-id=\"e9b57fb\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"image.default\">\n				<div class=\"elementor-widget-container\">\n															<img loading=\"lazy\" decoding=\"async\" width=\"740\" height=\"415\" src=\"/uploads/f59b462eeb03608cbc63.jpg\" class=\"attachment-full size-full wp-image-1242\" alt=\"\" srcset=\"/uploads/b315dbf5495287ac8be0.jpg 740w, /uploads/3d7b17f05c94bc8e8076.jpg 300w\" sizes=\"(max-width: 740px) 100vw, 740px\" />															</div>\n				</div>\n				<div class=\"elementor-element elementor-element-a9d7f08 elementor-widget elementor-widget-heading\" data-id=\"a9d7f08\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"heading.default\">\n				<div class=\"elementor-widget-container\">\n					<h2 class=\"elementor-heading-title elementor-size-default\">Leadership & Talent Development</h2>				</div>\n				</div>\n				<div class=\"elementor-element elementor-element-8085530 elementor-widget elementor-widget-text-editor\" data-id=\"8085530\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"text-editor.default\">\n				<div class=\"elementor-widget-container\">\n									<p data-start=\"1682\" data-end=\"2043\">At Makueni Youth Network, we reject the reductionist view that youth empowerment is limited to handouts or motivational talks. Instead, we prioritize structural investment in the talents, enterprises, and leadership potential of young people. We design and implement rigorous, field-tested programs that catalyze personal transformation and economic resilience.</p><p data-start=\"2045\" data-end=\"2549\">Our entrepreneurship accelerator initiative focuses on deconstructing barriers to entry for youth venturing into business. We provide bespoke training modules covering business modeling, compliance, digital marketing, and sustainable finance. Beyond training, we link participants with local cooperatives, micro-finance institutions, and public-private innovation hubs. These linkages are critical in ensuring that skills are not just theoretical, but practically applied in economically viable ventures.</p><p data-start=\"2551\" data-end=\"3107\">Talent development is equally treated with the seriousness it deserves. Working in collaboration with cultural institutions, innovation centers, and county departments, we identify raw and under-supported talent in areas such as coding, graphic design, music production, sculpture, agritech, spoken word, and emerging sports disciplines. We host talent showcases, organize interdisciplinary festivals, and facilitate industry mentorships. These platforms are not merely celebratory—they are gateways to investment, media visibility, and career progression.</p><p data-start=\"3109\" data-end=\"3306\">By championing multi-sectoral development, we are creating a generation of youth who are not only economically self-reliant but also capable of redefining what leadership means in the 21st century.</p>								</div>\n				</div>\n					</div>\n				</div>\n				</div>\n		', '/uploads/0ae55eb4bc53784db47d.jpg', 20, 'published'),
(3, NULL, 'education-capacity-enhancement', 'Education & Capacity Enhancement', 'We assess learning outcomes and gender gaps, then turn evidence into action — lobbying duty bearers for responsive policy and budgets.', '		<div data-elementor-type=\"wp-page\" data-elementor-id=\"356\" class=\"elementor elementor-356\">\n				<div class=\"elementor-element elementor-element-87a1ba3 e-flex e-con-boxed e-con e-parent\" data-id=\"87a1ba3\" data-element_type=\"container\" data-e-type=\"container\" data-settings=\"{&quot;background_background&quot;:&quot;classic&quot;}\">\n					<div class=\"e-con-inner\">\n				<div class=\"elementor-element elementor-element-e9b57fb elementor-widget elementor-widget-image\" data-id=\"e9b57fb\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"image.default\">\n				<div class=\"elementor-widget-container\">\n															<img decoding=\"async\" src=\"/uploads/755636ad2d8082489a78.jpg\" title=\"\" alt=\"\" loading=\"lazy\" />															</div>\n				</div>\n				<div class=\"elementor-element elementor-element-a9d7f08 elementor-widget elementor-widget-heading\" data-id=\"a9d7f08\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"heading.default\">\n				<div class=\"elementor-widget-container\">\n					<h2 class=\"elementor-heading-title elementor-size-default\">Education & Capacity Enhancement</h2>				</div>\n				</div>\n				<div class=\"elementor-element elementor-element-8085530 elementor-widget elementor-widget-text-editor\" data-id=\"8085530\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"text-editor.default\">\n				<div class=\"elementor-widget-container\">\n									<p data-start=\"98\" data-end=\"639\">At Makueni Youth Network (MYN), we prioritize enhancing the attainment of core competencies among government-sponsored learners, particularly those in underserved and remote areas of Makueni County. We recognize that learners in marginalized schools often face systemic barriers, including limited access to STEM laboratories, inadequate learning infrastructure, and a shortage of qualified STEM teachers. These gaps significantly affect performance, confidence, and progression in science, technology, engineering, and mathematics pathways.</p><p data-start=\"641\" data-end=\"1170\">Our program intentionally targets STEM pupils in remote primary and secondary schools where opportunities for practical science learning are minimal. We support the establishment and strengthening of STEM clubs, facilitate hands-on learning experiences, and connect learners to mentors and role models in science and technology fields. At the same time, we invest in retooling teachers with modern, competency-based STEM pedagogical skills to improve classroom delivery, learner engagement, and practical application of concepts.</p><p data-start=\"1172\" data-end=\"1748\">A central pillar of our work is advancing gender equity in STEM. We actively advocate for and support more girls to pursue STEM subjects at both primary and high school levels. Through mentorship, career guidance, community sensitization, and safe learning spaces, we address social norms and structural barriers that limit girls’ participation. Our goal is to close the persistent gender gap in science, technology, engineering, and mathematics by building a strong, confident pipeline of girls who not only choose STEM subjects but successfully transition into STEM careers.</p><p data-start=\"1144\" data-end=\"1630\">In partnership with organizations such as <span class=\"hover:entity-accent entity-underline inline cursor-pointer align-baseline\"><span class=\"whitespace-normal\">Usawa Agenda</span></span> and <span class=\"hover:entity-accent entity-underline inline cursor-pointer align-baseline\"><span class=\"whitespace-normal\">Zizi Afrique Foundation</span></span>, we also conduct learning assessments that examine real-world competencies, including applied numeracy, analytical thinking, and problem-solving skills that are critical for STEM success. The evidence generated informs targeted interventions to strengthen STEM instruction, improve learner performance, and address systemic barriers affecting girls’ participation.</p><p data-start=\"1632\" data-end=\"1989\">Beyond the classroom, MYN convenes educators, school leadership, policymakers, civil society actors, and community stakeholders to translate evidence into action. We advocate for responsive education policies, gender-sensitive learning environments, and resource allocation that supports STEM clubs, mentorship programs, and innovation hubs in high schools.</p>								</div>\n				</div>\n					</div>\n				</div>\n				</div>\n		', '/uploads/94397efe0824e6f8923d.jpg', 30, 'published'),
(4, 3, 'foundational-literacy-numeracy-assessment', 'Foundational Literacy & Numeracy Assessment', 'Annual household-based assessment of learning levels across Makueni — the evidence base for our education advocacy.', '		<div data-elementor-type=\"wp-page\" data-elementor-id=\"411\" class=\"elementor elementor-411\">\n				<div class=\"elementor-element elementor-element-87a1ba3 e-flex e-con-boxed e-con e-parent\" data-id=\"87a1ba3\" data-element_type=\"container\" data-e-type=\"container\" data-settings=\"{&quot;background_background&quot;:&quot;classic&quot;}\">\n					<div class=\"e-con-inner\">\n				<div class=\"elementor-element elementor-element-e9b57fb elementor-widget elementor-widget-image\" data-id=\"e9b57fb\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"image.default\">\n				<div class=\"elementor-widget-container\">\n															<img loading=\"lazy\" decoding=\"async\" width=\"1500\" height=\"897\" src=\"/uploads/062cc12f30543c61bd6d.jpg\" class=\"attachment-full size-full wp-image-1229\" alt=\"\" srcset=\"/uploads/bf2a42c4b1d33b2441b6.jpg 1500w, /uploads/27087bfe399775faba44.jpg 300w, /uploads/470beb25ee2000a3fe08.jpg 1024w, /uploads/dc4f2f3c00b3375748be.jpg 768w\" sizes=\"(max-width: 1500px) 100vw, 1500px\" />															</div>\n				</div>\n				<div class=\"elementor-element elementor-element-a9d7f08 elementor-widget elementor-widget-heading\" data-id=\"a9d7f08\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"heading.default\">\n				<div class=\"elementor-widget-container\">\n					<h2 class=\"elementor-heading-title elementor-size-default\">Foundational Learning Assessment</h2>				</div>\n				</div>\n				<div class=\"elementor-element elementor-element-8085530 elementor-widget elementor-widget-text-editor\" data-id=\"8085530\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"text-editor.default\">\n				<div class=\"elementor-widget-container\">\n									<p>Makueni Youth Network’s Foundational Literacy and Numeracy program is designed to ensure every child acquires the essential skills needed to thrive in school and beyond. In partnership with government institutions and leading education stakeholders—including <span class=\"whitespace-normal\">Usawa Agenda</span>, <span class=\"whitespace-normal\">Mizizi Elimu</span>, <span class=\"whitespace-normal\">SDGs Kenya Forum</span>, <span class=\"whitespace-normal\">National Gender and Equality Commission</span>, <span class=\"whitespace-normal\">Elimu Yetu Coalition</span>, and <span class=\"whitespace-normal\">PAL Network</span>—we conduct annual, community-driven learning assessments to generate reliable data on literacy and numeracy outcomes across Makueni County.</p><p data-start=\"640\" data-end=\"1112\">Our approach goes beyond assessment. We analyze gender disparities in learning, evaluate the level of community support toward foundational education, and assess government investments in school infrastructure and learning environments. By translating this evidence into actionable insights, we actively engage duty bearers to address policy gaps, strengthen accountability mechanisms, and advocate for increased, responsive budgeting toward foundational learning systems.</p><p data-start=\"1114\" data-end=\"1374\" data-is-last-node=\"\" data-is-only-node=\"\">Through this integrated model of evidence generation, partnerships, and advocacy, Makueni Youth Network is driving sustainable improvements in education quality—ensuring that all learners, especially the most marginalized, have an equal opportunity to succeed.</p>								</div>\n				</div>\n					</div>\n				</div>\n				</div>\n		', NULL, 31, 'published'),
(5, 2, 'youth-mentorship', 'Youth Mentorship', 'One-on-one mentor matching connecting young people with vetted professionals from across the county and diaspora.', '		<div data-elementor-type=\"wp-page\" data-elementor-id=\"363\" class=\"elementor elementor-363\">\n				<div class=\"elementor-element elementor-element-87a1ba3 e-flex e-con-boxed e-con e-parent\" data-id=\"87a1ba3\" data-element_type=\"container\" data-e-type=\"container\" data-settings=\"{&quot;background_background&quot;:&quot;classic&quot;}\">\n					<div class=\"e-con-inner\">\n				<div class=\"elementor-element elementor-element-e9b57fb elementor-widget elementor-widget-image\" data-id=\"e9b57fb\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"image.default\">\n				<div class=\"elementor-widget-container\">\n															<img loading=\"lazy\" decoding=\"async\" width=\"1500\" height=\"897\" src=\"/uploads/abea5e5bf5ea6df8d0f0.jpg\" class=\"attachment-full size-full wp-image-1229\" alt=\"\" srcset=\"/uploads/8b85aa1922846b2ebf6c.jpg 1500w, /uploads/7d3d07318cac5039e7e6.jpg 300w, /uploads/21f6e0edee617688fe9d.jpg 1024w, /uploads/3bcf5054b9b4414c3ef8.jpg 768w\" sizes=\"(max-width: 1500px) 100vw, 1500px\" />															</div>\n				</div>\n				<div class=\"elementor-element elementor-element-a9d7f08 elementor-widget elementor-widget-heading\" data-id=\"a9d7f08\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"heading.default\">\n				<div class=\"elementor-widget-container\">\n					<h2 class=\"elementor-heading-title elementor-size-default\">Youth Mentorship Program</h2>				</div>\n				</div>\n				<div class=\"elementor-element elementor-element-8085530 elementor-widget elementor-widget-text-editor\" data-id=\"8085530\" data-element_type=\"widget\" data-e-type=\"widget\" data-widget_type=\"text-editor.default\">\n				<div class=\"elementor-widget-container\">\n									<p><strong data-start=\"83\" data-end=\"115\">The Youth Mentorship Program</strong> is a structured initiative that onboards in-school and out-of-school young people aged between 12 and 35 years to equip them with 21st-century leadership, life and work-related skills. Through one-on-one or virtual mentorship sessions from professionals across diverse fields, the program strengthens psychosocial development, career readiness, leadership, and entrepreneurial capacity.</p><p>In addition, MYN runs <strong data-start=\"491\" data-end=\"537\" data-is-only-node=\"\">Integrity Clubs </strong>in colleges across Makueni County to promote values-based leadership and civic responsibility.</p><p>We also support <strong data-start=\"612\" data-end=\"710\">mentorship of lower-grade learners</strong> in public schools to improve literacy and numeracy outcomes. Targeting school leavers, unemployed graduates, and out-of-school youth, the program enhances employability and enables young people to start or scale sustainable self-employment opportunities.</p>								</div>\n				</div>\n					</div>\n				</div>\n				</div>\n		', NULL, 21, 'published');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(80) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('address', ''),
('email', ''),
('facebook', ''),
('google_analytics_id', 'G-XXTEST1234'),
('linkedin', ''),
('logo', ''),
('logo_square', ''),
('mail_from', ''),
('mail_host', ''),
('mail_pass', '%,bE@_5\"rAS=uUr'),
('mail_port', '587'),
('mail_user', ''),
('name', 'Makueni Youth Network'),
('paystack_callback_url', ''),
('paystack_currency', 'KES'),
('paystack_env', 'test'),
('paystack_public_key', ''),
('paystack_secret_key', 'sk_test_super_secret_value_abcd1234WXYZ'),
('phone', ''),
('po_box', ''),
('tagline', ''),
('twitter', '');

-- --------------------------------------------------------

--
-- Table structure for table `stats`
--

CREATE TABLE `stats` (
  `id` int(11) NOT NULL,
  `label` varchar(160) NOT NULL,
  `value` varchar(40) NOT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stats`
--

INSERT INTO `stats` (`id`, `label`, `value`, `sort_order`) VALUES
(1, 'Founded as a community-based organization', '2014', 10),
(2, 'Flagship programs across the county', '3', 20),
(3, 'Partner institutions & funders', '6+', 30),
(4, 'Young people reached & mobilized', '1000s', 40);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','editor') DEFAULT 'editor',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Site Admin', 'admin@makueniyouth.org', '$2y$10$SWgJ4KROw50cqwNO0UTM5.Rid.m9fEkOSA06EzqC2aW6aHVkWFsSK', 'admin', '2026-05-22 12:43:32');

-- --------------------------------------------------------

--
-- Table structure for table `volunteers`
--

CREATE TABLE `volunteers` (
  `id` int(11) NOT NULL,
  `full_name` varchar(160) NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `interest` varchar(160) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `volunteers`
--

INSERT INTO `volunteers` (`id`, `full_name`, `email`, `phone`, `interest`, `message`, `created_at`) VALUES
(1, 'Faith Mutua', 'faith@example.com', '+254700000000', 'Youth Mentorship', 'Interested in mentoring.', '2026-05-22 10:31:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `advocacy_content`
--
ALTER TABLE `advocacy_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_advocacy_slug` (`slug`),
  ADD KEY `idx_advocacy_type_status` (`content_type`,`status`),
  ADD KEY `idx_advocacy_deadline` (`deadline`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `idx_donations_status` (`status`,`created_at`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_events_start` (`starts_at`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_faq_status_order` (`status`,`sort_order`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_newsletter_email` (`email`),
  ADD KEY `idx_newsletter_status` (`status`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `page_views`
--
ALTER TABLE `page_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_page_views_path` (`path`),
  ADD KEY `idx_page_views_date` (`viewed_at`);

--
-- Indexes for table `partners`
--
ALTER TABLE `partners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_posts_pub` (`status`,`published_at`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `stats`
--
ALTER TABLE `stats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `volunteers`
--
ALTER TABLE `volunteers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `advocacy_content`
--
ALTER TABLE `advocacy_content`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `page_views`
--
ALTER TABLE `page_views`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `partners`
--
ALTER TABLE `partners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `stats`
--
ALTER TABLE `stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `volunteers`
--
ALTER TABLE `volunteers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `programs_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
