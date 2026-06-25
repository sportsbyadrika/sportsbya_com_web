-- SportsbyA Tech — database schema
-- Run this once (or use install.php) to create the required tables.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS admin_users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username      VARCHAR(60)  NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name          VARCHAR(120) NOT NULL DEFAULT '',
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clients (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(160) NOT NULL,
    logo_path   VARCHAR(255) NOT NULL DEFAULT '',
    website_url VARCHAR(255) NOT NULL DEFAULT '',
    description VARCHAR(500) NOT NULL DEFAULT '',
    sort_order  INT          NOT NULL DEFAULT 0,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_clients_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_posts (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title        VARCHAR(200) NOT NULL,
    slug         VARCHAR(220) NOT NULL,
    excerpt      VARCHAR(500) NOT NULL DEFAULT '',
    body         MEDIUMTEXT   NOT NULL,
    cover_path   VARCHAR(255) NOT NULL DEFAULT '',
    author       VARCHAR(120) NOT NULL DEFAULT '',
    status       ENUM('draft','published') NOT NULL DEFAULT 'draft',
    published_at DATETIME     NULL DEFAULT NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_blog_slug (slug),
    KEY idx_blog_status (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(160) NOT NULL,
    email      VARCHAR(160) NOT NULL,
    mobile     VARCHAR(40)  NOT NULL DEFAULT '',
    message    TEXT         NOT NULL,
    ip         VARCHAR(64)  NOT NULL DEFAULT '',
    user_agent VARCHAR(255) NOT NULL DEFAULT '',
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_contact_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    k VARCHAR(64) NOT NULL,
    v MEDIUMTEXT  NULL,
    PRIMARY KEY (k)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS receipts (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    receipt_no    VARCHAR(60)  NOT NULL,
    receipt_date  DATE         NOT NULL,
    received_from VARCHAR(200) NOT NULL DEFAULT '',
    payment_mode  VARCHAR(60)  NOT NULL DEFAULT '',
    reference     VARCHAR(120) NOT NULL DEFAULT '',
    items         MEDIUMTEXT   NULL,
    total         DECIMAL(12,2) NOT NULL DEFAULT 0,
    notes         VARCHAR(500) NOT NULL DEFAULT '',
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_receipt_date (receipt_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    payment_date DATE         NOT NULL,
    paid_to      VARCHAR(200) NOT NULL DEFAULT '',
    description  VARCHAR(300) NOT NULL DEFAULT '',
    payment_mode VARCHAR(60)  NOT NULL DEFAULT '',
    reference    VARCHAR(120) NOT NULL DEFAULT '',
    amount       DECIMAL(12,2) NOT NULL DEFAULT 0,
    notes        VARCHAR(500) NOT NULL DEFAULT '',
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_payment_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
