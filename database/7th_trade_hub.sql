-- 7th Trade Hub - Platform database schema
-- Import this file into MySQL/phpMyAdmin to create the database structure.
-- For full app setup, run: php artisan migrate (and optionally php artisan db:seed).

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `7th_trade_hub` (create manually in cPanel if needed)
--

-- --------------------------------------------------------
-- Platform tables (reference)
-- --------------------------------------------------------
-- Signin/Signup: users, password_reset_tokens, sessions
-- User/Admin app: wallets, transactions, orders, listings, support_tickets, email_verification_codes
-- Spatie Laravel Permission: permissions, roles, model_has_permissions, model_has_roles, role_has_permissions
-- Laravel system: cache, cache_locks, jobs, job_batches, failed_jobs
-- --------------------------------------------------------
-- Tables are created by Laravel migrations. This file documents
-- the equivalent structure for reference or manual import.
-- Run `php artisan migrate` to create tables from migrations.
-- Run `php artisan schema:dump` to export current schema here.
-- --------------------------------------------------------

-- ---------- Signin/Signup (auth) ----------
-- Users (Laravel + username)
-- CREATE TABLE `users` (
--   `id` bigint unsigned NOT NULL AUTO_INCREMENT,
--   `name` varchar(255) NOT NULL,
--   `username` varchar(255) NOT NULL,
--   `email` varchar(255) NOT NULL,
--   `email_verified_at` timestamp NULL DEFAULT NULL,
--   `password` varchar(255) NOT NULL,
--   `remember_token` varchar(100) DEFAULT NULL,
--   `created_at` timestamp NULL DEFAULT NULL,
--   `updated_at` timestamp NULL DEFAULT NULL,
--   PRIMARY KEY (`id`),
--   UNIQUE KEY `users_username_unique` (`username`),
--   UNIQUE KEY `users_email_unique` (`email`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password reset tokens (Laravel)
-- CREATE TABLE `password_reset_tokens` (
--   `email` varchar(255) NOT NULL,
--   `token` varchar(255) NOT NULL,
--   `created_at` timestamp NULL DEFAULT NULL,
--   PRIMARY KEY (`email`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions (Laravel)
-- CREATE TABLE `sessions` (
--   `id` varchar(255) NOT NULL,
--   `user_id` bigint unsigned DEFAULT NULL,
--   `ip_address` varchar(45) DEFAULT NULL,
--   `user_agent` text DEFAULT NULL,
--   `payload` longtext NOT NULL,
--   `last_activity` int NOT NULL,
--   PRIMARY KEY (`id`),
--   KEY `sessions_user_id_index` (`user_id`),
--   KEY `sessions_last_activity_index` (`last_activity`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Laravel system (queue, cache) ----------
-- Cache
-- CREATE TABLE `cache` (
--   `key` varchar(255) NOT NULL,
--   `value` mediumtext NOT NULL,
--   `expiration` int NOT NULL,
--   PRIMARY KEY (`key`),
--   KEY `cache_expiration_index` (`expiration`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cache locks
-- CREATE TABLE `cache_locks` (
--   `key` varchar(255) NOT NULL,
--   `owner` varchar(255) NOT NULL,
--   `expiration` int NOT NULL,
--   PRIMARY KEY (`key`),
--   KEY `cache_locks_expiration_index` (`expiration`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Jobs
-- CREATE TABLE `jobs` (
--   `id` bigint unsigned NOT NULL AUTO_INCREMENT,
--   `queue` varchar(255) NOT NULL,
--   `payload` longtext NOT NULL,
--   `attempts` tinyint unsigned NOT NULL,
--   `reserved_at` int unsigned DEFAULT NULL,
--   `available_at` int unsigned NOT NULL,
--   `created_at` int unsigned NOT NULL,
--   PRIMARY KEY (`id`),
--   KEY `jobs_queue_index` (`queue`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Job batches
-- CREATE TABLE `job_batches` (
--   `id` varchar(255) NOT NULL,
--   `name` varchar(255) NOT NULL,
--   `total_jobs` int NOT NULL,
--   `pending_jobs` int NOT NULL,
--   `failed_jobs` int NOT NULL,
--   `failed_job_ids` longtext NOT NULL,
--   `options` mediumtext DEFAULT NULL,
--   `cancelled_at` int DEFAULT NULL,
--   `created_at` int NOT NULL,
--   `finished_at` int DEFAULT NULL,
--   PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Failed jobs
-- CREATE TABLE `failed_jobs` (
--   `id` bigint unsigned NOT NULL AUTO_INCREMENT,
--   `uuid` varchar(255) NOT NULL,
--   `connection` text NOT NULL,
--   `queue` text NOT NULL,
--   `payload` longtext NOT NULL,
--   `exception` longtext NOT NULL,
--   `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
--   PRIMARY KEY (`id`),
--   UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- User/Admin app ----------
-- Wallets
-- CREATE TABLE `wallets` (
--   `id` bigint unsigned NOT NULL AUTO_INCREMENT,
--   `user_id` bigint unsigned NOT NULL,
--   `balance_usd` decimal(14,2) NOT NULL DEFAULT 0.00,
--   `crypto_btc` decimal(18,8) NOT NULL DEFAULT 0.00000000,
--   `crypto_eth` decimal(18,8) NOT NULL DEFAULT 0.00000000,
--   `balance_change_label` varchar(100) DEFAULT NULL,
--   `created_at` timestamp NULL DEFAULT NULL,
--   `updated_at` timestamp NULL DEFAULT NULL,
--   PRIMARY KEY (`id`),
--   UNIQUE KEY `wallets_user_id_unique` (`user_id`),
--   CONSTRAINT `wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listings
-- CREATE TABLE `listings` (
--   `id` bigint unsigned NOT NULL AUTO_INCREMENT,
--   `title` varchar(255) NOT NULL,
--   `slug` varchar(255) NOT NULL,
--   `description` text,
--   `price` decimal(12,2) NOT NULL,
--   `category` varchar(80) DEFAULT NULL,
--   `icon_class` varchar(80) DEFAULT NULL,
--   `is_active` tinyint(1) NOT NULL DEFAULT 1,
--   `created_at` timestamp NULL DEFAULT NULL,
--   `updated_at` timestamp NULL DEFAULT NULL,
--   PRIMARY KEY (`id`),
--   UNIQUE KEY `listings_slug_unique` (`slug`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions
-- CREATE TABLE `transactions` (
--   `id` bigint unsigned NOT NULL AUTO_INCREMENT,
--   `user_id` bigint unsigned NOT NULL,
--   `reference` varchar(32) NOT NULL,
--   `type` varchar(40) NOT NULL,
--   `label` varchar(120) NOT NULL,
--   `amount` decimal(14,2) NOT NULL,
--   `currency` varchar(10) NOT NULL DEFAULT 'USD',
--   `asset_type` varchar(20) DEFAULT NULL,
--   `status` varchar(20) NOT NULL DEFAULT 'pending',
--   `created_at` timestamp NULL DEFAULT NULL,
--   `updated_at` timestamp NULL DEFAULT NULL,
--   PRIMARY KEY (`id`),
--   UNIQUE KEY `transactions_reference_unique` (`reference`),
--   CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders
-- CREATE TABLE `orders` (
--   `id` bigint unsigned NOT NULL AUTO_INCREMENT,
--   `user_id` bigint unsigned NOT NULL,
--   `listing_id` bigint unsigned DEFAULT NULL,
--   `reference` varchar(32) NOT NULL,
--   `amount` decimal(12,2) NOT NULL,
--   `status` varchar(20) NOT NULL DEFAULT 'pending',
--   `created_at` timestamp NULL DEFAULT NULL,
--   `updated_at` timestamp NULL DEFAULT NULL,
--   PRIMARY KEY (`id`),
--   UNIQUE KEY `orders_reference_unique` (`reference`),
--   CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
--   CONSTRAINT `orders_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE SET NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Support tickets
-- CREATE TABLE `support_tickets` (
--   `id` bigint unsigned NOT NULL AUTO_INCREMENT,
--   `user_id` bigint unsigned NOT NULL,
--   `subject` varchar(255) NOT NULL,
--   `status` varchar(20) NOT NULL DEFAULT 'open',
--   `created_at` timestamp NULL DEFAULT NULL,
--   `updated_at` timestamp NULL DEFAULT NULL,
--   PRIMARY KEY (`id`),
--   CONSTRAINT `support_tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email verification codes (OTP)
-- CREATE TABLE `email_verification_codes` (
--   `id` bigint unsigned NOT NULL AUTO_INCREMENT,
--   `user_id` bigint unsigned NOT NULL,
--   `code_hash` varchar(64) NOT NULL,
--   `expires_at` timestamp NOT NULL,
--   `attempts` tinyint unsigned NOT NULL DEFAULT 0,
--   `created_at` timestamp NULL DEFAULT NULL,
--   `updated_at` timestamp NULL DEFAULT NULL,
--   PRIMARY KEY (`id`),
--   CONSTRAINT `email_verification_codes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Spatie Laravel Permission ----------
-- Permissions
-- CREATE TABLE `permissions` (
--   `id` bigint unsigned NOT NULL AUTO_INCREMENT,
--   `name` varchar(255) NOT NULL,
--   `guard_name` varchar(255) NOT NULL,
--   `created_at` timestamp NULL DEFAULT NULL,
--   `updated_at` timestamp NULL DEFAULT NULL,
--   PRIMARY KEY (`id`),
--   UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roles
-- CREATE TABLE `roles` (
--   `id` bigint unsigned NOT NULL AUTO_INCREMENT,
--   `name` varchar(255) NOT NULL,
--   `guard_name` varchar(255) NOT NULL,
--   `created_at` timestamp NULL DEFAULT NULL,
--   `updated_at` timestamp NULL DEFAULT NULL,
--   PRIMARY KEY (`id`),
--   UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Model has permissions (pivot)
-- CREATE TABLE `model_has_permissions` (
--   `permission_id` bigint unsigned NOT NULL,
--   `model_type` varchar(255) NOT NULL,
--   `model_id` bigint unsigned NOT NULL,
--   PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
--   KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
--   CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Model has roles (pivot)
-- CREATE TABLE `model_has_roles` (
--   `role_id` bigint unsigned NOT NULL,
--   `model_type` varchar(255) NOT NULL,
--   `model_id` bigint unsigned NOT NULL,
--   PRIMARY KEY (`role_id`,`model_id`,`model_type`),
--   KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
--   CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role has permissions (pivot)
-- CREATE TABLE `role_has_permissions` (
--   `permission_id` bigint unsigned NOT NULL,
--   `role_id` bigint unsigned NOT NULL,
--   PRIMARY KEY (`permission_id`,`role_id`),
--   CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
--   CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

-- To populate demo data after import, run: php artisan db:seed
