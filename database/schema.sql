-- =============================================================
-- Online Lorries Hiring System (OLHS) — Database Schema v1.0
-- Database: olhs_db | Engine: InnoDB | Charset: utf8mb4
-- Run this file in phpMyAdmin or MySQL CLI after creating olhs_db
-- =============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------
-- Table: users
-- Stores all platform users (customers, owners, admins)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`                 INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `full_name`          VARCHAR(150)    NOT NULL,
  `email`              VARCHAR(191)    NOT NULL,
  `phone`              VARCHAR(20)     NOT NULL,
  `password_hash`      VARCHAR(255)    NOT NULL,
  `role`               ENUM('customer','lorry_owner','admin','super_admin') NOT NULL DEFAULT 'customer',
  `email_verified_at`  DATETIME        NULL DEFAULT NULL,
  `profile_photo`      VARCHAR(255)    NULL DEFAULT NULL,
  `wallet_balance`     DECIMAL(12,2)   NOT NULL DEFAULT 0.00,  -- lorry owner earnings wallet
  `status`             ENUM('active','suspended','banned') NOT NULL DEFAULT 'active',
  `login_attempts`     TINYINT UNSIGNED NOT NULL DEFAULT 0,    -- for account lockout
  `lockout_until`      DATETIME        NULL DEFAULT NULL,       -- locked after 5 failed attempts
  `remember_token`     VARCHAR(100)    NULL DEFAULT NULL,       -- 30-day remember-me cookie
  `preferred_lang`     ENUM('en','sw') NOT NULL DEFAULT 'en',  -- English or Swahili
  `created_at`         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`         DATETIME        NULL DEFAULT NULL,        -- soft delete
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_email` (`email`),
  INDEX `idx_role`     (`role`),
  INDEX `idx_status`   (`status`),
  INDEX `idx_deleted`  (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Table: lorries
-- Lorry listings owned by lorry_owner users
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lorries` (
  `id`                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `owner_id`            INT UNSIGNED    NOT NULL,                -- FK to users.id
  `name`                VARCHAR(150)    NOT NULL,                -- Lorry nickname
  `lorry_type`          ENUM('flatbed','box','tipper','tanker','refrigerated','mini') NOT NULL,
  `capacity_tonnes`     DECIMAL(6,2)    NOT NULL,                -- Max load in tonnes
  `plate_number`        VARCHAR(20)     NOT NULL,                -- Vehicle registration
  `price_per_km`        DECIMAL(10,2)   NULL DEFAULT NULL,       -- Rate per km (TZS)
  `base_price`          DECIMAL(10,2)   NULL DEFAULT NULL,       -- Flat rate option (TZS)
  `current_location`    VARCHAR(255)    NULL DEFAULT NULL,       -- City/area
  `lat`                 DECIMAL(10,7)   NULL DEFAULT NULL,       -- GPS latitude
  `lng`                 DECIMAL(10,7)   NULL DEFAULT NULL,       -- GPS longitude
  `availability_status` ENUM('available','on_trip','maintenance') NOT NULL DEFAULT 'available',
  `approval_status`     ENUM('pending','approved','rejected')    NOT NULL DEFAULT 'pending',
  `rejection_reason`    TEXT            NULL DEFAULT NULL,       -- Admin rejection note
  `description`         TEXT            NULL DEFAULT NULL,       -- Owner description
  `avg_rating`          DECIMAL(3,2)    NOT NULL DEFAULT 0.00,   -- Cached average rating
  `total_trips`         INT UNSIGNED    NOT NULL DEFAULT 0,      -- Completed trip count
  `created_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_plate` (`plate_number`),
  INDEX `idx_owner`        (`owner_id`),
  INDEX `idx_type`         (`lorry_type`),
  INDEX `idx_approval`     (`approval_status`),
  INDEX `idx_availability` (`availability_status`),
  INDEX `idx_location`     (`current_location`),
  CONSTRAINT `fk_lorry_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Table: lorry_photos
-- Up to 5 photos per lorry listing
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lorry_photos` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `lorry_id`    INT UNSIGNED  NOT NULL,
  `photo_path`  VARCHAR(255)  NOT NULL,   -- Relative path: /storage/lorries/uuid.jpg
  `is_primary`  TINYINT(1)    NOT NULL DEFAULT 0,  -- 1 = main display photo
  `uploaded_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_lorry` (`lorry_id`),
  CONSTRAINT `fk_photo_lorry` FOREIGN KEY (`lorry_id`) REFERENCES `lorries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Table: bookings
-- Full booking lifecycle with status state machine
-- Status flow: pending → accepted → in_transit → completed
--              pending → cancelled (by customer or auto-cancel)
--              accepted → cancelled (cancellation policy applies)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bookings` (
  `id`                 INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `booking_ref`        VARCHAR(20)    NOT NULL,   -- e.g. BK-2024-00123
  `customer_id`        INT UNSIGNED   NOT NULL,   -- FK to users.id
  `lorry_id`           INT UNSIGNED   NOT NULL,   -- FK to lorries.id
  `pickup_address`     VARCHAR(255)   NOT NULL,
  `delivery_address`   VARCHAR(255)   NOT NULL,
  `pickup_lat`         DECIMAL(10,7)  NULL DEFAULT NULL,
  `pickup_lng`         DECIMAL(10,7)  NULL DEFAULT NULL,
  `delivery_lat`       DECIMAL(10,7)  NULL DEFAULT NULL,
  `delivery_lng`       DECIMAL(10,7)  NULL DEFAULT NULL,
  `distance_km`        DECIMAL(8,2)   NULL DEFAULT NULL,   -- Calculated via Google Maps API
  `goods_description`  TEXT           NOT NULL,
  `weight_kg`          DECIMAL(10,2)  NULL DEFAULT NULL,
  `preferred_date`     DATE           NOT NULL,
  `preferred_time`     TIME           NULL DEFAULT NULL,
  `quoted_price`       DECIMAL(12,2)  NOT NULL,            -- Agreed price at booking (TZS)
  `status`             ENUM('pending','accepted','in_transit','completed','cancelled') NOT NULL DEFAULT 'pending',
  `cancellation_reason` TEXT          NULL DEFAULT NULL,
  `accepted_at`        DATETIME       NULL DEFAULT NULL,   -- When owner accepted
  `completed_at`       DATETIME       NULL DEFAULT NULL,   -- When trip completed
  `auto_cancel_at`     DATETIME       NULL DEFAULT NULL,   -- Auto-cancel deadline (24h from creation)
  `created_at`         DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_booking_ref` (`booking_ref`),
  INDEX `idx_customer`  (`customer_id`),
  INDEX `idx_lorry`     (`lorry_id`),
  INDEX `idx_status`    (`status`),
  INDEX `idx_date`      (`preferred_date`),
  CONSTRAINT `fk_booking_customer` FOREIGN KEY (`customer_id`) REFERENCES `users`    (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_booking_lorry`    FOREIGN KEY (`lorry_id`)    REFERENCES `lorries`  (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Table: payments
-- Transaction records with simulated mobile money support
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id`                  INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `booking_id`          INT UNSIGNED   NOT NULL,            -- FK to bookings.id
  `payer_id`            INT UNSIGNED   NOT NULL,            -- FK to users.id (customer)
  `amount`              DECIMAL(12,2)  NOT NULL,            -- Total paid (TZS)
  `platform_commission` DECIMAL(12,2)  NOT NULL DEFAULT 0.00, -- Platform fee (8%)
  `owner_payout`        DECIMAL(12,2)  NOT NULL DEFAULT 0.00, -- Amount to owner wallet
  `payment_method`      ENUM('mpesa','airtel','halotel','card','cash') NOT NULL DEFAULT 'mpesa',
  `mobile_number`       VARCHAR(20)    NULL DEFAULT NULL,   -- Mobile money number
  `transaction_id`      VARCHAR(100)   NULL DEFAULT NULL,   -- Simulated transaction ref
  `status`              ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `paid_at`             DATETIME       NULL DEFAULT NULL,   -- Successful payment timestamp
  `created_at`          DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_transaction` (`transaction_id`),
  INDEX `idx_booking` (`booking_id`),
  INDEX `idx_payer`   (`payer_id`),
  INDEX `idx_status`  (`status`),
  CONSTRAINT `fk_payment_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_payment_payer`   FOREIGN KEY (`payer_id`)   REFERENCES `users`    (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Table: reviews
-- One review per completed booking
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reviews` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `booking_id`   INT UNSIGNED  NOT NULL,   -- FK to bookings.id (UNIQUE = one review per booking)
  `reviewer_id`  INT UNSIGNED  NOT NULL,   -- FK to users.id (customer)
  `lorry_id`     INT UNSIGNED  NOT NULL,   -- FK to lorries.id
  `rating`       TINYINT       NOT NULL,   -- 1–5 stars
  `comment`      TEXT          NULL DEFAULT NULL,
  `owner_reply`  TEXT          NULL DEFAULT NULL,  -- Lorry owner response
  `is_flagged`   TINYINT(1)    NOT NULL DEFAULT 0,  -- Flagged by admin
  `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_booking_review` (`booking_id`),  -- One review per booking
  INDEX `idx_lorry`    (`lorry_id`),
  INDEX `idx_reviewer` (`reviewer_id`),
  CONSTRAINT `fk_review_booking`  FOREIGN KEY (`booking_id`)  REFERENCES `bookings` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_review_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users`    (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_review_lorry`    FOREIGN KEY (`lorry_id`)    REFERENCES `lorries`  (`id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_rating`         CHECK (`rating` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Table: notifications
-- In-app + simulated SMS/email notifications
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED  NOT NULL,
  `type`       VARCHAR(50)   NOT NULL,   -- e.g. booking_created, payment_done
  `title`      VARCHAR(255)  NOT NULL,   -- Short notification title
  `message`    TEXT          NOT NULL,   -- Full notification text
  `link`       VARCHAR(255)  NULL DEFAULT NULL,  -- Click-through URL
  `is_read`    TINYINT(1)    NOT NULL DEFAULT 0,
  `channel`    ENUM('in_app','sms','email') NOT NULL DEFAULT 'in_app',
  `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_user`    (`user_id`),
  INDEX `idx_is_read` (`is_read`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Table: password_resets
-- SHA-256 hashed tokens for secure password reset
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `email`      VARCHAR(191)  NOT NULL,
  `token_hash` VARCHAR(64)   NOT NULL,  -- SHA-256 hash of the emailed token
  `expires_at` DATETIME      NOT NULL,  -- 1 hour expiry
  `used`       TINYINT(1)    NOT NULL DEFAULT 0,
  `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_token` (`token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Table: email_verifications
-- Tokens for new account email confirmation
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_verifications` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED  NOT NULL,
  `token_hash` VARCHAR(64)   NOT NULL,
  `expires_at` DATETIME      NOT NULL,
  `used`       TINYINT(1)    NOT NULL DEFAULT 0,
  `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_user`  (`user_id`),
  INDEX `idx_token` (`token_hash`),
  CONSTRAINT `fk_verify_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Table: withdrawals
-- Lorry owner payout requests
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `withdrawals` (
  `id`           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `owner_id`     INT UNSIGNED   NOT NULL,   -- FK to users.id
  `amount`       DECIMAL(12,2)  NOT NULL,   -- Withdrawal amount (TZS)
  `mobile_number` VARCHAR(20)   NOT NULL,   -- Destination mobile money number
  `mpesa_ref`    VARCHAR(100)   NULL DEFAULT NULL,  -- Simulated payout reference
  `status`       ENUM('pending','completed','rejected') NOT NULL DEFAULT 'pending',
  `notes`        TEXT           NULL DEFAULT NULL,
  `processed_at` DATETIME       NULL DEFAULT NULL,
  `created_at`   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_owner`  (`owner_id`),
  INDEX `idx_status` (`status`),
  CONSTRAINT `fk_withdrawal_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Table: disputes
-- Customer-raised booking disputes, admin-mediated
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `disputes` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `booking_id`  INT UNSIGNED  NOT NULL,
  `raised_by`   INT UNSIGNED  NOT NULL,   -- FK to users.id
  `description` TEXT          NOT NULL,
  `status`      ENUM('open','under_review','resolved','closed') NOT NULL DEFAULT 'open',
  `resolution`  TEXT          NULL DEFAULT NULL,  -- Admin resolution note
  `resolved_at` DATETIME      NULL DEFAULT NULL,
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_booking` (`booking_id`),
  INDEX `idx_status`  (`status`),
  CONSTRAINT `fk_dispute_booking`   FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_dispute_raised_by` FOREIGN KEY (`raised_by`)  REFERENCES `users`    (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Table: system_settings
-- Admin-configurable key-value platform settings
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100)  NOT NULL,
  `setting_val` TEXT          NOT NULL,
  `description` VARCHAR(255)  NULL DEFAULT NULL,
  `updated_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Table: sms_log
-- Simulated SMS notification log (no real SMS API in MVP)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sms_log` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `recipient`   VARCHAR(20)   NOT NULL,   -- Mobile number
  `message`     TEXT          NOT NULL,
  `status`      ENUM('sent','failed') NOT NULL DEFAULT 'sent',
  `sent_at`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_recipient` (`recipient`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- Default system settings (commission rate, min withdrawal)
-- =============================================================
INSERT INTO `system_settings` (`setting_key`, `setting_val`, `description`) VALUES
  ('commission_rate',    '8',      'Platform commission percentage per completed booking'),
  ('min_withdrawal',     '5000',   'Minimum wallet balance for owner withdrawal (TZS)'),
  ('booking_timeout_hrs','24',     'Hours before unaccepted booking is auto-cancelled'),
  ('site_name_en',       'Online Lorries Hiring System', 'Platform name (English)'),
  ('site_name_sw',       'Mfumo wa Kuomba Malori Mtandaoni', 'Platform name (Swahili)'),
  ('contact_email',      'info@olhs.co.tz', 'Platform contact email'),
  ('contact_phone',      '+255700000000',   'Platform contact phone');

SET FOREIGN_KEY_CHECKS = 1;
