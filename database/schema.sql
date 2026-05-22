-- ---------------------------------------------------------------------------
-- Makueni Youth Network CMS — schema
-- Conjured Upon This Day, Fri May 22 2026 — M B I T H I
--
-- Target: MariaDB 10.4+ / MySQL 8+, utf8mb4
-- Run with:   mysql -u root -p myn < database/schema.sql
-- ---------------------------------------------------------------------------

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS donations;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS volunteers;
DROP TABLE IF EXISTS partners;
DROP TABLE IF EXISTS stats;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS programs;
DROP TABLE IF EXISTS pages;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------------
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','editor') DEFAULT 'editor',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- pages (free-form static-ish pages: about, contact, donate, volunteer …)
-- ---------------------------------------------------------------------------
CREATE TABLE pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(160) UNIQUE NOT NULL,
  title VARCHAR(200) NOT NULL,
  body MEDIUMTEXT,
  meta_desc VARCHAR(300),
  hero_image VARCHAR(255),
  status ENUM('draft','published') DEFAULT 'draft',
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- programs (self-referencing tree: parent → children)
-- ---------------------------------------------------------------------------
CREATE TABLE programs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  parent_id INT NULL,
  slug VARCHAR(160) UNIQUE NOT NULL,
  title VARCHAR(200) NOT NULL,
  summary VARCHAR(400),
  body MEDIUMTEXT,
  cover_image VARCHAR(255),
  sort_order INT DEFAULT 0,
  status ENUM('draft','published') DEFAULT 'published',
  FOREIGN KEY (parent_id) REFERENCES programs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- categories (post taxonomy)
-- ---------------------------------------------------------------------------
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(120) UNIQUE NOT NULL,
  name VARCHAR(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- posts (impact stories / news)
-- ---------------------------------------------------------------------------
CREATE TABLE posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(180) UNIQUE NOT NULL,
  title VARCHAR(220) NOT NULL,
  excerpt VARCHAR(400),
  body MEDIUMTEXT,
  cover_image VARCHAR(255),
  category_id INT NULL,
  author_id INT NULL,
  status ENUM('draft','published') DEFAULT 'draft',
  published_at DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id)   REFERENCES users(id)      ON DELETE SET NULL,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  INDEX idx_posts_pub (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- events
-- ---------------------------------------------------------------------------
CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(180) UNIQUE NOT NULL,
  title VARCHAR(220) NOT NULL,
  description MEDIUMTEXT,
  cover_image VARCHAR(255),
  venue VARCHAR(220),
  starts_at DATETIME NOT NULL,
  ends_at DATETIME NULL,
  status ENUM('draft','published') DEFAULT 'draft',
  INDEX idx_events_start (starts_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- stats (homepage stripe figures)
-- ---------------------------------------------------------------------------
CREATE TABLE stats (
  id INT AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(160) NOT NULL,
  value VARCHAR(40) NOT NULL,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- partners (logo grid)
-- ---------------------------------------------------------------------------
CREATE TABLE partners (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  logo VARCHAR(255),
  url VARCHAR(255),
  sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- volunteers (form submissions)
-- ---------------------------------------------------------------------------
CREATE TABLE volunteers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(160) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40),
  interest VARCHAR(160),
  message TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- messages (contact form inbox)
-- ---------------------------------------------------------------------------
CREATE TABLE messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  email VARCHAR(190) NOT NULL,
  subject VARCHAR(220),
  body TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- donations (Paystack — see M5)
-- ---------------------------------------------------------------------------
CREATE TABLE donations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  donor_name VARCHAR(160),
  donor_phone VARCHAR(40),
  donor_email VARCHAR(190),
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- settings (key/value store consumed by the `site` Twig global)
-- ---------------------------------------------------------------------------
CREATE TABLE settings (
  setting_key   VARCHAR(80) PRIMARY KEY,
  setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
