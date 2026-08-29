-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 29, 2026 at 03:24 PM
-- Server version: 11.8.8-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u502532383_tradehub`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(60) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_notifications`
--

INSERT INTO `admin_notifications` (`id`, `type`, `title`, `body`, `action_url`, `meta`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 'escrow.disputed', 'Escrow dispute opened', 'A buyer opened a dispute.', '/admin', '{\"demo\":true}', NULL, '2026-07-22 09:20:00', '2026-07-22 09:20:00'),
(2, 'ticket.opened', 'New support ticket', 'A customer opened a ticket.', '/admin', '{\"demo\":true}', '2026-07-21 11:20:00', '2026-07-21 09:20:00', '2026-07-21 09:20:00'),
(3, 'wallet.funded', 'Wallet funded', 'A funding was approved.', '/admin', '{\"demo\":true}', '2026-07-20 11:20:00', '2026-07-20 09:20:00', '2026-07-20 09:20:00'),
(4, 'listing.rejected', 'Listing rejected', 'A listing was rejected in review.', '/admin', '{\"demo\":true}', '2026-07-19 11:20:00', '2026-07-19 09:20:00', '2026-07-19 09:20:00'),
(5, 'ticket.opened', 'New support ticket', 'Ticket #41 was opened.', 'https://7th-tradehub.online/admin/tickets', '{\"ticket_id\":41,\"user_id\":16,\"priority\":\"normal\"}', NULL, '2026-08-01 01:00:32', '2026-08-01 01:00:32'),
(6, 'ticket.replied', 'Support ticket reply', 'Ticket #41 received a user reply.', 'https://7th-tradehub.online/admin/tickets', '{\"ticket_id\":41,\"replier_id\":16,\"priority\":\"normal\"}', NULL, '2026-08-01 01:00:55', '2026-08-01 01:00:55');

-- --------------------------------------------------------

--
-- Table structure for table `analytics_ga_snapshots`
--

CREATE TABLE `analytics_ga_snapshots` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `metric` varchar(80) NOT NULL,
  `dimension` varchar(120) DEFAULT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `fetched_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `analytics_kpi_snapshots`
--

CREATE TABLE `analytics_kpi_snapshots` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kpi_key` varchar(80) NOT NULL,
  `period` varchar(20) NOT NULL,
  `value` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `captured_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `analytics_kpi_snapshots`
--

INSERT INTO `analytics_kpi_snapshots` (`id`, `kpi_key`, `period`, `value`, `meta`, `captured_at`, `created_at`, `updated_at`) VALUES
(11, 'users.total', 'today', 6.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(12, 'listings.active', 'today', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(13, 'listings.pending_review', 'today', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(14, 'kyc.pending', 'today', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(15, 'escrows.pending', 'today', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(16, 'support.waiting', 'today', 1.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(17, 'tickets.open', 'today', 1.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(18, 'tickets.total', 'today', 1.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(19, 'sales.total_ngn', 'today', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(20, 'transactions.total', 'today', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(21, 'users.total', '7d', 6.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(22, 'listings.active', '7d', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(23, 'listings.pending_review', '7d', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(24, 'kyc.pending', '7d', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(25, 'escrows.pending', '7d', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(26, 'support.waiting', '7d', 1.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(27, 'tickets.open', '7d', 1.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(28, 'tickets.total', '7d', 1.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(29, 'sales.total_ngn', '7d', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(30, 'transactions.total', '7d', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(31, 'users.total', '30d', 6.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(32, 'listings.active', '30d', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(33, 'listings.pending_review', '30d', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(34, 'kyc.pending', '30d', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(35, 'escrows.pending', '30d', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(36, 'support.waiting', '30d', 1.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(37, 'tickets.open', '30d', 1.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(38, 'tickets.total', '30d', 1.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(39, 'sales.total_ngn', '30d', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(40, 'transactions.total', '30d', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(43, 'orders.by_status', 'current', 0.0000, NULL, '2026-08-01 19:50:47', '2026-07-23 19:02:05', '2026-08-01 19:50:47'),
(134, 'users.total', 'current', 6.0000, NULL, '2026-08-01 19:50:47', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(135, 'listings.active', 'current', 0.0000, NULL, '2026-08-01 19:50:47', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(136, 'listings.pending_review', 'current', 0.0000, NULL, '2026-08-01 19:50:47', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(137, 'kyc.pending', 'current', 0.0000, NULL, '2026-08-01 19:50:47', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(138, 'escrows.pending', 'current', 0.0000, NULL, '2026-08-01 19:50:47', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(139, 'support.waiting', 'current', 1.0000, NULL, '2026-08-01 19:50:47', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(140, 'tickets.open', 'current', 1.0000, NULL, '2026-08-01 19:50:47', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(141, 'tickets.total', 'current', 1.0000, NULL, '2026-08-01 19:50:47', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(142, 'sales.total_ngn', 'current', 0.0000, NULL, '2026-08-01 19:50:47', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(143, 'transactions.total', 'current', 0.0000, NULL, '2026-08-01 19:50:47', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(144, 'revenue.today', 'today', 0.0000, NULL, '2026-08-01 19:50:47', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(145, 'fundings.today', 'today', 0.0000, NULL, '2026-08-01 19:50:47', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(146, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-03\"}', '2026-07-03 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(147, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-03\"}', '2026-07-03 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(148, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-03\"}', '2026-07-03 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(149, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-04\"}', '2026-07-04 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(150, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-04\"}', '2026-07-04 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(151, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-04\"}', '2026-07-04 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(152, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-05\"}', '2026-07-05 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(153, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-05\"}', '2026-07-05 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(154, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-05\"}', '2026-07-05 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(155, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-06\"}', '2026-07-06 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(156, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-06\"}', '2026-07-06 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(157, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-06\"}', '2026-07-06 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(158, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-07\"}', '2026-07-07 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(159, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-07\"}', '2026-07-07 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(160, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-07\"}', '2026-07-07 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(161, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-08\"}', '2026-07-08 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(162, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-08\"}', '2026-07-08 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(163, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-08\"}', '2026-07-08 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(164, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-09\"}', '2026-07-09 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(165, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-09\"}', '2026-07-09 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(166, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-09\"}', '2026-07-09 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(167, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-10\"}', '2026-07-10 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(168, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-10\"}', '2026-07-10 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(169, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-10\"}', '2026-07-10 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(170, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-11\"}', '2026-07-11 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(171, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-11\"}', '2026-07-11 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(172, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-11\"}', '2026-07-11 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(173, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-12\"}', '2026-07-12 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(174, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-12\"}', '2026-07-12 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(175, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-12\"}', '2026-07-12 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(176, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-13\"}', '2026-07-13 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(177, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-13\"}', '2026-07-13 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(178, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-13\"}', '2026-07-13 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(179, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-14\"}', '2026-07-14 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(180, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-14\"}', '2026-07-14 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(181, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-14\"}', '2026-07-14 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(182, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-15\"}', '2026-07-15 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(183, 'users.daily', 'daily', 2.0000, '{\"day\":\"2026-07-15\"}', '2026-07-15 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(184, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-15\"}', '2026-07-15 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(185, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-16\"}', '2026-07-16 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(186, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-16\"}', '2026-07-16 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(187, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-16\"}', '2026-07-16 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(188, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-17\"}', '2026-07-17 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(189, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-17\"}', '2026-07-17 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(190, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-17\"}', '2026-07-17 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(191, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-18\"}', '2026-07-18 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(192, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-18\"}', '2026-07-18 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(193, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-18\"}', '2026-07-18 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(194, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-19\"}', '2026-07-19 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(195, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-19\"}', '2026-07-19 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(196, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-19\"}', '2026-07-19 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(197, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-20\"}', '2026-07-20 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(198, 'users.daily', 'daily', 1.0000, '{\"day\":\"2026-07-20\"}', '2026-07-20 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(199, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-20\"}', '2026-07-20 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(200, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-21\"}', '2026-07-21 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(201, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-21\"}', '2026-07-21 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(202, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-21\"}', '2026-07-21 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(203, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-22\"}', '2026-07-22 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(204, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-22\"}', '2026-07-22 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(205, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-22\"}', '2026-07-22 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(206, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-23\"}', '2026-07-23 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(207, 'users.daily', 'daily', 2.0000, '{\"day\":\"2026-07-23\"}', '2026-07-23 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(208, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-23\"}', '2026-07-23 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(209, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-24\"}', '2026-07-24 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(210, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-24\"}', '2026-07-24 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(211, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-24\"}', '2026-07-24 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(212, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-25\"}', '2026-07-25 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(213, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-25\"}', '2026-07-25 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(214, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-25\"}', '2026-07-25 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(215, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-26\"}', '2026-07-26 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(216, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-26\"}', '2026-07-26 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(217, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-26\"}', '2026-07-26 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(218, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-27\"}', '2026-07-27 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(219, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-27\"}', '2026-07-27 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(220, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-27\"}', '2026-07-27 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(221, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-28\"}', '2026-07-28 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(222, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-28\"}', '2026-07-28 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(223, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-28\"}', '2026-07-28 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(224, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-29\"}', '2026-07-29 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(225, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-29\"}', '2026-07-29 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(226, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-29\"}', '2026-07-29 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(227, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-30\"}', '2026-07-30 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(228, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-30\"}', '2026-07-30 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(229, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-30\"}', '2026-07-30 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(230, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-07-31\"}', '2026-07-31 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(231, 'users.daily', 'daily', 0.0000, '{\"day\":\"2026-07-31\"}', '2026-07-31 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(232, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-07-31\"}', '2026-07-31 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(233, 'revenue.daily', 'daily', 0.0000, '{\"day\":\"2026-08-01\"}', '2026-08-01 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(234, 'users.daily', 'daily', 1.0000, '{\"day\":\"2026-08-01\"}', '2026-08-01 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47'),
(235, 'fundings.daily', 'daily', 0.0000, '{\"day\":\"2026-08-01\"}', '2026-08-01 23:59:59', '2026-08-01 19:50:47', '2026-08-01 19:50:47');

-- --------------------------------------------------------

--
-- Table structure for table `analytics_providers`
--

CREATE TABLE `analytics_providers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `provider` varchar(60) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `credentials` text DEFAULT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'idle',
  `last_sync_at` timestamp NULL DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `analytics_providers`
--

INSERT INTO `analytics_providers` (`id`, `provider`, `enabled`, `credentials`, `status`, `last_sync_at`, `last_error`, `created_at`, `updated_at`) VALUES
(1, 'google_analytics', 0, NULL, 'idle', NULL, NULL, '2026-07-23 18:50:34', '2026-07-23 18:50:34'),
(2, 'microsoft_clarity', 0, NULL, 'idle', NULL, NULL, '2026-07-23 18:50:34', '2026-07-23 18:50:34');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `actor_type` varchar(30) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(60) DEFAULT NULL,
  `model_type` varchar(255) DEFAULT NULL,
  `model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `device` varchar(30) DEFAULT NULL,
  `browser` varchar(60) DEFAULT NULL,
  `country` varchar(2) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `correlation_id` varchar(64) DEFAULT NULL,
  `request_id` varchar(64) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `admin_id`, `actor_id`, `actor_type`, `action`, `module`, `model_type`, `model_id`, `old_values`, `new_values`, `ip`, `user_agent`, `device`, `browser`, `country`, `reason`, `correlation_id`, `request_id`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, 'user.suspended', NULL, 'App\\Models\\User', 13, NULL, '{\"user_id\":13,\"is_suspended\":true}', '102.89.75.88', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-21 23:02:04', '2026-07-21 23:02:04'),
(2, 1, NULL, NULL, 'user.anonymized', NULL, 'App\\Models\\User', 13, '{\"email\":\"mr.carter.tech07@gmail.com\"}', '{\"user_id\":13,\"anonymized_at\":\"2026-07-21T23:02:20+00:00\"}', '102.89.75.88', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-21 23:02:20', '2026-07-21 23:02:20'),
(3, 1, NULL, NULL, 'user.impersonation.started', NULL, 'App\\Models\\User', 8, NULL, '{\"impersonator_id\":1,\"target_user_id\":8}', '102.88.113.238', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-22 14:50:34', '2026-07-22 14:50:34'),
(4, 1, NULL, NULL, 'user.impersonation.stopped', NULL, 'App\\Models\\User', 8, NULL, '{\"impersonator_id\":1,\"target_user_id\":8}', '102.88.113.238', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-22 14:51:46', '2026-07-22 14:51:46'),
(5, 1, NULL, NULL, 'user.impersonation.started', NULL, 'App\\Models\\User', 15, NULL, '{\"impersonator_id\":1,\"target_user_id\":15}', '102.89.83.199', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-23 02:27:20', '2026-07-23 02:27:20'),
(6, 1, NULL, NULL, 'user.impersonation.started', NULL, 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '102.89.82.205', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-23 15:08:07', '2026-07-23 15:08:07'),
(7, 1, NULL, NULL, 'user.impersonation.stopped', NULL, 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '102.89.82.205', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-23 15:08:29', '2026-07-23 15:08:29'),
(17, 1, 1, 'admin', 'user.impersonation.started', 'user', 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '51.158.254.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '37cd3cb1-3b27-4bd3-bc26-5f06771058d3', '2026-07-24 21:51:34', '2026-07-24 21:51:34'),
(18, 1, 1, 'admin', 'user.impersonation.stopped', 'user', 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '51.158.254.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '009e72f2-d268-4b65-bc66-54fde79ecb9d', '2026-07-24 21:53:23', '2026-07-24 21:53:23'),
(19, 1, 1, 'admin', 'user.impersonation.started', 'user', 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'e8ecd9f5-b7f5-4728-b1ea-bde2f4d3a0cf', '2026-08-01 00:15:49', '2026-08-01 00:15:49'),
(20, 1, 1, 'admin', 'user.impersonation.stopped', 'user', 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '2faf3620-1094-4c73-858e-58154deea13d', '2026-08-01 00:41:16', '2026-08-01 00:41:16'),
(21, 1, 1, 'admin', 'kyc.requirement.updated', 'kyc', NULL, NULL, NULL, '{\"kyc_required\":false}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'f0c752b8-40b6-48e9-b206-869f5a59ef66', '2026-08-01 00:48:59', '2026-08-01 00:48:59'),
(22, 1, 1, 'admin', 'user.impersonation.started', 'user', 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'b570b1ba-2717-43a1-b609-aaa2ff5a7552', '2026-08-01 00:51:57', '2026-08-01 00:51:57'),
(23, 1, 1, 'admin', 'user.impersonation.stopped', 'user', 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'e35d61c6-f746-42a0-bccd-a0200eac675a', '2026-08-01 00:52:07', '2026-08-01 00:52:07'),
(24, 1, 1, 'admin', 'user.impersonation.started', 'user', 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'fc02c806-e44b-4069-ac73-b0353073e97d', '2026-08-01 00:52:18', '2026-08-01 00:52:18'),
(25, 1, 1, 'admin', 'user.impersonation.stopped', 'user', 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '97870779-b18f-4cf9-8f88-b3ada8b44619', '2026-08-01 00:52:50', '2026-08-01 00:52:50'),
(26, 1, 1, 'admin', 'user.impersonation.started', 'user', 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '9fb68a64-89e2-459b-ad83-49a269856ef9', '2026-08-01 00:53:48', '2026-08-01 00:53:48'),
(27, NULL, NULL, NULL, 'event.ticket.opened', 'event', 'App\\Models\\SupportTicket', 41, NULL, '{\"ticket_id\":41,\"user_id\":16}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'edeeba35-0bb5-4907-bdd7-b6d479848867', '2026-08-01 01:00:32', '2026-08-01 01:00:32'),
(28, 1, 1, 'admin', 'user.impersonation.stopped', 'user', 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '9fe3e780-2f30-4847-952a-3156e5567142', '2026-08-01 01:30:03', '2026-08-01 01:30:03'),
(29, 1, 1, 'admin', 'user.impersonation.started', 'user', 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '5d7d85c7-6922-4d4c-b7e0-ae6f543f7f25', '2026-08-01 01:56:25', '2026-08-01 01:56:25'),
(30, 1, 1, 'admin', 'user.impersonation.stopped', 'user', 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'ff35572f-14d1-46cb-b980-6b17fcbe4f45', '2026-08-01 02:09:28', '2026-08-01 02:09:28'),
(31, 1, 1, 'admin', 'settings.branding.updated', 'settings', NULL, NULL, NULL, '{\"site_name\":\"7th Trade Hub\"}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '46b9b1e0-a7f4-4bac-9ec9-29d06c24e236', '2026-08-01 02:50:03', '2026-08-01 02:50:03'),
(32, 1, 1, 'admin', 'settings.branding.updated', 'settings', NULL, NULL, NULL, '{\"site_name\":\"7th Trade Hub\"}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '8ccd3f42-c17a-48a4-9220-3d02bbbd35c9', '2026-08-01 02:54:00', '2026-08-01 02:54:00'),
(33, 1, 1, 'admin', 'settings.contact.updated', 'settings', NULL, NULL, NULL, '{\"live_chat_provider\":\"none\"}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'f9f848d9-c470-4bb2-a6fa-04e8ce958078', '2026-08-01 02:55:32', '2026-08-01 02:55:32'),
(34, 1, 1, 'admin', 'settings.email.updated', 'settings', NULL, NULL, NULL, '{\"brevo_enabled\":true,\"laravel_mail_enabled\":false}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'ef14d744-95c0-4eea-b004-165cd657c56e', '2026-08-01 02:57:03', '2026-08-01 02:57:03'),
(35, 1, 1, 'admin', 'settings.mail_test', 'settings', NULL, NULL, NULL, '{\"recipient\":\"mr.carter.tech07@gmail.com\",\"ok\":false,\"provider\":\"laravel_mail\",\"error\":\"Deferred retry in 5 minutes: Laravel mail fallback is unavailable.\",\"message_id\":null,\"http_status\":null}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'a6a1f62f-52d2-49bf-bc6c-e791d6c53091', '2026-08-01 02:57:31', '2026-08-01 02:57:31'),
(36, 1, 1, 'admin', 'settings.mail_test', 'settings', NULL, NULL, NULL, '{\"recipient\":\"mr.carter.tech07@gmail.com\",\"ok\":true,\"provider\":\"brevo\",\"error\":null,\"message_id\":\"<202608010301.52195022488@smtp-relay.mailin.fr>\",\"http_status\":201}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '86742d0d-3f4d-456c-91d0-d85706bd7d10', '2026-08-01 03:01:05', '2026-08-01 03:01:05'),
(37, 1, 1, 'admin', 'user.impersonation.started', 'user', 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '80977586-7a17-4474-a425-1778b29b86c7', '2026-08-01 03:02:08', '2026-08-01 03:02:08'),
(38, 1, 1, 'admin', 'user.impersonation.stopped', 'user', 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '58634231-ec7f-4424-9fe6-de6b87c24b50', '2026-08-01 03:12:31', '2026-08-01 03:12:31'),
(39, 1, 1, 'admin', 'settings.social.updated', 'settings', NULL, NULL, NULL, NULL, '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'e17f5b7d-8de9-4d23-80f7-658a25aa2cfa', '2026-08-01 03:12:59', '2026-08-01 03:12:59'),
(40, 1, 1, 'admin', 'user.impersonation.started', 'user', 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '53cb0892-efd3-4b70-b8cb-16db20f4f325', '2026-08-01 03:14:08', '2026-08-01 03:14:08'),
(41, 1, 1, 'admin', 'user.impersonation.stopped', 'user', 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'a103bc39-9bdc-4fed-ba1f-aa90de89ee9e', '2026-08-01 03:15:35', '2026-08-01 03:15:35'),
(42, 1, 1, 'admin', 'user.impersonation.started', 'user', 'App\\Models\\User', 16, NULL, '{\"impersonator_id\":1,\"target_user_id\":16}', '102.89.83.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'd1e1923c-8388-432b-9bb4-2f58a87d4a45', '2026-08-01 03:16:32', '2026-08-01 03:16:32'),
(43, NULL, NULL, NULL, 'event.user.registered', 'event', 'App\\Models\\User', 42, NULL, '{\"user_id\":42}', '102.89.83.195', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36', 'mobile', 'chrome', NULL, NULL, NULL, '181e91e3-9a50-4201-9298-654c65018cde', '2026-08-01 03:23:11', '2026-08-01 03:23:11'),
(44, 1, 1, 'admin', 'user.suspended', 'user', 'App\\Models\\User', 16, NULL, '{\"user_id\":16,\"is_suspended\":true}', '102.89.43.68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'ac73a016-5bae-42c6-991c-9c3abae7e9e0', '2026-08-01 19:51:32', '2026-08-01 19:51:32'),
(45, 1, 1, 'admin', 'user.suspended', 'user', 'App\\Models\\User', 15, NULL, '{\"user_id\":15,\"is_suspended\":true}', '102.89.43.68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '3e09871c-ecce-44b5-b820-0509e4937969', '2026-08-01 19:59:10', '2026-08-01 19:59:10'),
(46, 1, 1, 'admin', 'user.restored', 'user', 'App\\Models\\User', 15, NULL, '{\"user_id\":15,\"is_suspended\":false}', '102.89.43.68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '6b2a843c-1e7e-4a4e-840e-0281bcd06795', '2026-08-01 19:59:16', '2026-08-01 19:59:16'),
(47, 1, 1, 'admin', 'user.suspended', 'user', 'App\\Models\\User', 15, NULL, '{\"user_id\":15,\"is_suspended\":true}', '102.89.43.68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '01d4015f-6800-4240-b93f-dd5350918c54', '2026-08-01 19:59:20', '2026-08-01 19:59:20'),
(48, 1, 1, 'admin', 'user.anonymized', 'user', 'App\\Models\\User', 16, '{\"email\":\"morgan.morris46@mx-mailsrv.com\"}', '{\"user_id\":16,\"anonymized_at\":\"2026-08-01T20:01:11+00:00\"}', '102.89.43.68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '0eda773b-0f98-48c7-8671-ce119c8ebacb', '2026-08-01 20:01:11', '2026-08-01 20:01:11'),
(49, 1, 1, 'admin', 'user.anonymized', 'user', 'App\\Models\\User', 15, '{\"email\":\"nivix31198@besteya.com\"}', '{\"user_id\":15,\"anonymized_at\":\"2026-08-01T20:11:57+00:00\"}', '102.89.43.68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '99f8a657-9d61-4482-9ff2-a470ff314135', '2026-08-01 20:11:57', '2026-08-01 20:11:57'),
(50, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":1,\"user_id\":3,\"category_id\":6,\"marketplace_product_id\":1,\"title\":\"Aged Facebook with verified history\",\"slug\":\"digitalvault-facebook-0\",\"description\":\"Sold by DigitalVault. Facebook listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"8500.00\",\"category\":\"facebook\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":true,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":1,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'a9400fd6-7e78-43c0-9986-c20f4630e7e0', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(51, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":2,\"user_id\":3,\"category_id\":6,\"marketplace_product_id\":2,\"title\":\"Premium Twitter \\/ X package with docs\",\"slug\":\"digitalvault-twitter-1\",\"description\":\"Sold by DigitalVault. Twitter \\/ X listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"9250.00\",\"category\":\"twitter\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":2,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '77156edb-37fe-4a39-91dd-2088fe0f98ba', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(52, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":3,\"user_id\":3,\"category_id\":6,\"marketplace_product_id\":3,\"title\":\"Ready to use TikTok for agencies\",\"slug\":\"digitalvault-tiktok-2\",\"description\":\"Sold by DigitalVault. TikTok listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"10000.00\",\"category\":\"tiktok\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":3,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'd7274f11-cbad-4f5e-9cbf-4d8a7ec978d0', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(53, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":4,\"user_id\":3,\"category_id\":6,\"marketplace_product_id\":4,\"title\":\"Starter Instagram bundle with escrow\",\"slug\":\"digitalvault-instagram-3\",\"description\":\"Sold by DigitalVault. Instagram listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"10750.00\",\"category\":\"instagram\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":4,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'edbccba3-eb91-4d19-89d5-e3689aad92de', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(54, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":5,\"user_id\":3,\"category_id\":6,\"marketplace_product_id\":5,\"title\":\"High trust LinkedIn listing\",\"slug\":\"digitalvault-linkedin-4\",\"description\":\"Sold by DigitalVault. LinkedIn listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"11500.00\",\"category\":\"linkedin\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":5,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '793c0bb3-6a81-465d-a9b9-4f8cc183d170', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(55, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":6,\"user_id\":4,\"category_id\":6,\"marketplace_product_id\":6,\"title\":\"Aged Discord with verified history\",\"slug\":\"primenetworks-discord-0\",\"description\":\"Sold by Prime Networks. Discord listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"9700.00\",\"category\":\"discord\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":true,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":6,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'a9a9bcfc-5cb8-43c8-a452-41eb10dfa06f', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(56, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":7,\"user_id\":4,\"category_id\":13,\"marketplace_product_id\":7,\"title\":\"Premium VPN package with docs\",\"slug\":\"primenetworks-marketplace-vpn-1\",\"description\":\"Sold by Prime Networks. VPN listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"10450.00\",\"category\":\"marketplace-vpn\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":7,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '00c0c5f9-ba77-44fd-839e-c729315e92b7', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(57, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":8,\"user_id\":4,\"category_id\":13,\"marketplace_product_id\":8,\"title\":\"Ready to use Proxy for agencies\",\"slug\":\"primenetworks-marketplace-proxy-2\",\"description\":\"Sold by Prime Networks. Proxy listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"11200.00\",\"category\":\"marketplace-proxy\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":8,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '7b484c84-61aa-4734-b289-8d4d553fd1b5', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(58, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":9,\"user_id\":4,\"category_id\":13,\"marketplace_product_id\":9,\"title\":\"Starter RDP bundle with escrow\",\"slug\":\"primenetworks-rdp-3\",\"description\":\"Sold by Prime Networks. RDP listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"11950.00\",\"category\":\"rdp\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":9,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '2a91d9d6-7902-4255-bf1c-2568717bb151', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(59, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":10,\"user_id\":4,\"category_id\":13,\"marketplace_product_id\":10,\"title\":\"High trust VPS listing\",\"slug\":\"primenetworks-marketplace-vps-4\",\"description\":\"Sold by Prime Networks. VPS listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"12700.00\",\"category\":\"marketplace-vps\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":10,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '31c68eae-32db-4398-a3ca-51bea3a9b394', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(60, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":11,\"user_id\":5,\"category_id\":13,\"marketplace_product_id\":11,\"title\":\"Aged SMTP with verified history\",\"slug\":\"cloudedge-marketplace-smtp-0\",\"description\":\"Sold by Cloud Edge. SMTP listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"10900.00\",\"category\":\"marketplace-smtp\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":true,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":11,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '689c8d43-b40d-4fe3-8ebe-7ed507ea3eaf', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(61, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":12,\"user_id\":5,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Premium Websites package with docs\",\"slug\":\"cloudedge-websites-1\",\"description\":\"Sold by Cloud Edge. Websites listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"11650.00\",\"category\":\"websites\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":12,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'e10c8188-d616-4369-92d3-f53e67f4accc', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(62, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":13,\"user_id\":5,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Ready to use Domains for agencies\",\"slug\":\"cloudedge-domains-2\",\"description\":\"Sold by Cloud Edge. Domains listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"12400.00\",\"category\":\"domains\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":13,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'f91b0dc4-d925-43d7-b674-209e6a99c164', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(63, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":14,\"user_id\":5,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Starter Source Code bundle with escrow\",\"slug\":\"cloudedge-source-code-3\",\"description\":\"Sold by Cloud Edge. Source Code listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"13150.00\",\"category\":\"source-code\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":14,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'dbd3a290-ab09-4954-ba46-c1471e78489a', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(64, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":15,\"user_id\":5,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"High trust Graphics listing\",\"slug\":\"cloudedge-graphics-4\",\"description\":\"Sold by Cloud Edge. Graphics listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"13900.00\",\"category\":\"graphics\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":15,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'b494fa7e-57bd-49bb-8a1c-39102ee8f9fd', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(65, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":16,\"user_id\":6,\"category_id\":6,\"marketplace_product_id\":1,\"title\":\"Aged Facebook with verified history\",\"slug\":\"secureconnect-facebook-0\",\"description\":\"Sold by Secure Connect. Facebook listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"12100.00\",\"category\":\"facebook\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":true,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":16,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '8e4ee35b-e0d8-482f-b070-28b03fac5c36', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(66, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":17,\"user_id\":6,\"category_id\":6,\"marketplace_product_id\":2,\"title\":\"Premium Twitter \\/ X package with docs\",\"slug\":\"secureconnect-twitter-1\",\"description\":\"Sold by Secure Connect. Twitter \\/ X listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"12850.00\",\"category\":\"twitter\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":17,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '4472a88b-20c0-44ca-ba74-2d80251da5e7', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(67, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":18,\"user_id\":6,\"category_id\":6,\"marketplace_product_id\":3,\"title\":\"Ready to use TikTok for agencies\",\"slug\":\"secureconnect-tiktok-2\",\"description\":\"Sold by Secure Connect. TikTok listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"13600.00\",\"category\":\"tiktok\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":18,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '2487615a-b7e9-41fe-bfd3-244626b8ab48', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(68, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":19,\"user_id\":6,\"category_id\":6,\"marketplace_product_id\":4,\"title\":\"Starter Instagram bundle with escrow\",\"slug\":\"secureconnect-instagram-3\",\"description\":\"Sold by Secure Connect. Instagram listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"14350.00\",\"category\":\"instagram\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":19,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '15e530d2-f79b-4f14-bece-988cd0932b5e', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(69, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":20,\"user_id\":6,\"category_id\":6,\"marketplace_product_id\":5,\"title\":\"High trust LinkedIn listing\",\"slug\":\"secureconnect-linkedin-4\",\"description\":\"Sold by Secure Connect. LinkedIn listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"15100.00\",\"category\":\"linkedin\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":20,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '8810f7f7-863c-4c43-a17a-9733e0725261', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(70, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":21,\"user_id\":7,\"category_id\":6,\"marketplace_product_id\":6,\"title\":\"Aged Discord with verified history\",\"slug\":\"pixelstudio-discord-0\",\"description\":\"Sold by Pixel Studio. Discord listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"13300.00\",\"category\":\"discord\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":true,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":21,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '3b314ade-7809-4b87-ba9c-cf71c9882020', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(71, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":22,\"user_id\":7,\"category_id\":13,\"marketplace_product_id\":7,\"title\":\"Premium VPN package with docs\",\"slug\":\"pixelstudio-marketplace-vpn-1\",\"description\":\"Sold by Pixel Studio. VPN listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"14050.00\",\"category\":\"marketplace-vpn\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":22,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '891ac855-2b73-4680-8ae4-212e4be0d663', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(72, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":23,\"user_id\":7,\"category_id\":13,\"marketplace_product_id\":8,\"title\":\"Ready to use Proxy for agencies\",\"slug\":\"pixelstudio-marketplace-proxy-2\",\"description\":\"Sold by Pixel Studio. Proxy listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"14800.00\",\"category\":\"marketplace-proxy\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":23,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '97f7f4de-fd26-4419-b872-75e2a703e419', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(73, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":24,\"user_id\":7,\"category_id\":13,\"marketplace_product_id\":9,\"title\":\"Starter RDP bundle with escrow\",\"slug\":\"pixelstudio-rdp-3\",\"description\":\"Sold by Pixel Studio. RDP listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"15550.00\",\"category\":\"rdp\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":24,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'bc1c2860-84b7-4bf6-9066-31c595a7abd0', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(74, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":25,\"user_id\":7,\"category_id\":13,\"marketplace_product_id\":10,\"title\":\"High trust VPS listing\",\"slug\":\"pixelstudio-marketplace-vps-4\",\"description\":\"Sold by Pixel Studio. VPS listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"16300.00\",\"category\":\"marketplace-vps\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:09.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":25,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'd1c0d353-2a41-4e51-966f-7728f965ddea', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(75, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":26,\"user_id\":8,\"category_id\":13,\"marketplace_product_id\":11,\"title\":\"Aged SMTP with verified history\",\"slug\":\"codeforge-marketplace-smtp-0\",\"description\":\"Sold by Code Forge. SMTP listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"14500.00\",\"category\":\"marketplace-smtp\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":true,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":26,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '211bb259-1452-4e57-a6b3-1e221f4ebc85', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(76, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":27,\"user_id\":8,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Premium Websites package with docs\",\"slug\":\"codeforge-websites-1\",\"description\":\"Sold by Code Forge. Websites listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"15250.00\",\"category\":\"websites\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":27,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '460ba6af-ff47-48f9-a6bc-7394295352ce', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(77, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":28,\"user_id\":8,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Ready to use Domains for agencies\",\"slug\":\"codeforge-domains-2\",\"description\":\"Sold by Code Forge. Domains listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"16000.00\",\"category\":\"domains\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":28,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '0beeaa26-41b6-49b7-8125-aae6ecc02b4d', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(78, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":29,\"user_id\":8,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Starter Source Code bundle with escrow\",\"slug\":\"codeforge-source-code-3\",\"description\":\"Sold by Code Forge. Source Code listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"16750.00\",\"category\":\"source-code\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":29,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '8d832a01-51a2-4892-ade5-850ef041048e', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(79, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":30,\"user_id\":8,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"High trust Graphics listing\",\"slug\":\"codeforge-graphics-4\",\"description\":\"Sold by Code Forge. Graphics listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"17500.00\",\"category\":\"graphics\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":30,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'c5259090-2e37-41c9-99bd-fcc09e88e913', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(80, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":31,\"user_id\":9,\"category_id\":6,\"marketplace_product_id\":1,\"title\":\"Aged Facebook with verified history\",\"slug\":\"atlastech-facebook-0\",\"description\":\"Sold by Atlas Tech. Facebook listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"15700.00\",\"category\":\"facebook\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":true,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":31,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '493d2e5c-02da-4bee-af3d-f5556ac97682', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(81, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":32,\"user_id\":9,\"category_id\":6,\"marketplace_product_id\":2,\"title\":\"Premium Twitter \\/ X package with docs\",\"slug\":\"atlastech-twitter-1\",\"description\":\"Sold by Atlas Tech. Twitter \\/ X listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"16450.00\",\"category\":\"twitter\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":32,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'b6222231-3c16-4b40-b3c9-31391234f9f3', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(82, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":33,\"user_id\":9,\"category_id\":6,\"marketplace_product_id\":3,\"title\":\"Ready to use TikTok for agencies\",\"slug\":\"atlastech-tiktok-2\",\"description\":\"Sold by Atlas Tech. TikTok listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"17200.00\",\"category\":\"tiktok\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":33,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'ec1f8856-0bd6-49e9-ae45-546adbdf6caa', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(83, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":34,\"user_id\":9,\"category_id\":6,\"marketplace_product_id\":4,\"title\":\"Starter Instagram bundle with escrow\",\"slug\":\"atlastech-instagram-3\",\"description\":\"Sold by Atlas Tech. Instagram listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"17950.00\",\"category\":\"instagram\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":34,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'f06b0326-8d79-4e75-981e-580f033e7c1b', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(84, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":35,\"user_id\":9,\"category_id\":6,\"marketplace_product_id\":5,\"title\":\"High trust LinkedIn listing\",\"slug\":\"atlastech-linkedin-4\",\"description\":\"Sold by Atlas Tech. LinkedIn listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"18700.00\",\"category\":\"linkedin\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":35,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '60931b6a-50e6-4884-b4aa-9d91eee53330', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(85, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":36,\"user_id\":10,\"category_id\":6,\"marketplace_product_id\":6,\"title\":\"Aged Discord with verified history\",\"slug\":\"skyhost-discord-0\",\"description\":\"Sold by SkyHost. Discord listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"16900.00\",\"category\":\"discord\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":true,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":36,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'ece5d9e6-9cd0-40a5-b0e0-61d5a8a91412', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(86, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":37,\"user_id\":10,\"category_id\":13,\"marketplace_product_id\":7,\"title\":\"Premium VPN package with docs\",\"slug\":\"skyhost-marketplace-vpn-1\",\"description\":\"Sold by SkyHost. VPN listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"17650.00\",\"category\":\"marketplace-vpn\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":37,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '9f96101a-5f5e-40cb-8258-ff4ff122ccf5', '2026-08-01 20:33:20', '2026-08-01 20:33:20');
INSERT INTO `audit_logs` (`id`, `admin_id`, `actor_id`, `actor_type`, `action`, `module`, `model_type`, `model_id`, `old_values`, `new_values`, `ip`, `user_agent`, `device`, `browser`, `country`, `reason`, `correlation_id`, `request_id`, `created_at`, `updated_at`) VALUES
(87, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":38,\"user_id\":10,\"category_id\":13,\"marketplace_product_id\":8,\"title\":\"Ready to use Proxy for agencies\",\"slug\":\"skyhost-marketplace-proxy-2\",\"description\":\"Sold by SkyHost. Proxy listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"18400.00\",\"category\":\"marketplace-proxy\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":38,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'ab81cca0-5f50-4c52-96d7-315f51f981de', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(88, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":39,\"user_id\":10,\"category_id\":13,\"marketplace_product_id\":9,\"title\":\"Starter RDP bundle with escrow\",\"slug\":\"skyhost-rdp-3\",\"description\":\"Sold by SkyHost. RDP listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"19150.00\",\"category\":\"rdp\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":39,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '724b69f9-5d47-4784-a9e1-01fd9f77ff3d', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(89, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":40,\"user_id\":10,\"category_id\":13,\"marketplace_product_id\":10,\"title\":\"High trust VPS listing\",\"slug\":\"skyhost-marketplace-vps-4\",\"description\":\"Sold by SkyHost. VPS listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"19900.00\",\"category\":\"marketplace-vps\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":40,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '90225e27-b56c-4def-87c0-a19fd81c8e58', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(90, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":41,\"user_id\":11,\"category_id\":13,\"marketplace_product_id\":11,\"title\":\"Aged SMTP with verified history\",\"slug\":\"nexusdigital-marketplace-smtp-0\",\"description\":\"Sold by Nexus Digital. SMTP listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"18100.00\",\"category\":\"marketplace-smtp\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":true,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":41,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'de8b48cb-b410-41f7-8356-c69a6b056b10', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(91, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":42,\"user_id\":11,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Premium Websites package with docs\",\"slug\":\"nexusdigital-websites-1\",\"description\":\"Sold by Nexus Digital. Websites listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"18850.00\",\"category\":\"websites\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":42,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '516ddd46-b5f3-46f8-ab76-9b2245a45ae2', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(92, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":43,\"user_id\":11,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Ready to use Domains for agencies\",\"slug\":\"nexusdigital-domains-2\",\"description\":\"Sold by Nexus Digital. Domains listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"19600.00\",\"category\":\"domains\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":43,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '7ffb0349-c563-402b-9841-5cfaad7379ce', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(93, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":44,\"user_id\":11,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Starter Source Code bundle with escrow\",\"slug\":\"nexusdigital-source-code-3\",\"description\":\"Sold by Nexus Digital. Source Code listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"20350.00\",\"category\":\"source-code\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":44,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '7c10938c-569a-48dd-8b26-84d81895a668', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(94, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":45,\"user_id\":11,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"High trust Graphics listing\",\"slug\":\"nexusdigital-graphics-4\",\"description\":\"Sold by Nexus Digital. Graphics listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"21100.00\",\"category\":\"graphics\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":45,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '5284a426-eb21-45ea-927a-cb228ee60b60', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(95, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":46,\"user_id\":12,\"category_id\":6,\"marketplace_product_id\":1,\"title\":\"Aged Facebook with verified history\",\"slug\":\"nextgenmedia-facebook-0\",\"description\":\"Sold by NextGen Media. Facebook listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"19300.00\",\"category\":\"facebook\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":true,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":46,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '8eb7dca9-f6c2-4107-a884-28e3aac004c2', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(96, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":47,\"user_id\":12,\"category_id\":6,\"marketplace_product_id\":2,\"title\":\"Premium Twitter \\/ X package with docs\",\"slug\":\"nextgenmedia-twitter-1\",\"description\":\"Sold by NextGen Media. Twitter \\/ X listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"20050.00\",\"category\":\"twitter\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":47,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'd15ea8bd-b1f0-4a64-b63b-2d0611ba21cb', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(97, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":48,\"user_id\":12,\"category_id\":6,\"marketplace_product_id\":3,\"title\":\"Ready to use TikTok for agencies\",\"slug\":\"nextgenmedia-tiktok-2\",\"description\":\"Sold by NextGen Media. TikTok listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"20800.00\",\"category\":\"tiktok\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":48,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'f602585b-ead7-481d-898a-9bc2f2b82a58', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(98, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":49,\"user_id\":12,\"category_id\":6,\"marketplace_product_id\":4,\"title\":\"Starter Instagram bundle with escrow\",\"slug\":\"nextgenmedia-instagram-3\",\"description\":\"Sold by NextGen Media. Instagram listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"21550.00\",\"category\":\"instagram\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":49,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '935655e2-d667-4bbf-95fb-897cac1df448', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(99, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":50,\"user_id\":12,\"category_id\":6,\"marketplace_product_id\":5,\"title\":\"High trust LinkedIn listing\",\"slug\":\"nextgenmedia-linkedin-4\",\"description\":\"Sold by NextGen Media. LinkedIn listing with escrow protection on 7th Trade Hub. Delivery details shared after payment clears.\",\"price\":\"22300.00\",\"category\":\"linkedin\",\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-07-18T14:41:10.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":50,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '4c8e67a2-0c54-4c61-9f85-265ac2caea5b', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(100, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":51,\"user_id\":23,\"category_id\":6,\"marketplace_product_id\":1,\"title\":\"Live Facebook offer #1\",\"slug\":\"live-facebook-offer-1-qcmvn\",\"description\":\"Demo listing for Facebook. Escrow-ready delivery with docs.\",\"price\":\"8500.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":true,\"created_at\":\"2026-01-05T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":51,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '9408ef1a-06d3-4e09-a499-11ff46dad935', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(101, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":52,\"user_id\":24,\"category_id\":6,\"marketplace_product_id\":2,\"title\":\"Live Twitter \\/ X offer #2\",\"slug\":\"live-twitter-x-offer-2-q4get\",\"description\":\"Demo listing for Twitter \\/ X. Escrow-ready delivery with docs.\",\"price\":\"12000.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-01-06T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":52,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'fa471431-8017-4e7f-87df-7f034c1fd378', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(102, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":53,\"user_id\":27,\"category_id\":6,\"marketplace_product_id\":3,\"title\":\"Live TikTok offer #3\",\"slug\":\"live-tiktok-offer-3-vmcml\",\"description\":\"Demo listing for TikTok. Escrow-ready delivery with docs.\",\"price\":\"25000.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-01-07T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":53,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '1158794f-0c08-4196-a8c7-14846b803dc0', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(103, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":54,\"user_id\":30,\"category_id\":6,\"marketplace_product_id\":4,\"title\":\"Live Instagram offer #4\",\"slug\":\"live-instagram-offer-4-ok8wu\",\"description\":\"Demo listing for Instagram. Escrow-ready delivery with docs.\",\"price\":\"45000.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-01-08T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":54,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '4d1a6156-e20d-40ef-8990-ebcce96998f3', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(104, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":55,\"user_id\":33,\"category_id\":6,\"marketplace_product_id\":5,\"title\":\"Pending review: LinkedIn lot #5\",\"slug\":\"pending-review-linkedin-lot-5-54tnl\",\"description\":\"Demo listing for LinkedIn. Escrow-ready delivery with docs.\",\"price\":\"6500.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"pending_review\",\"featured\":false,\"created_at\":\"2026-03-09T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":55,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '193448ff-691f-490a-9e94-2e0215e87c21', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(105, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":56,\"user_id\":36,\"category_id\":6,\"marketplace_product_id\":6,\"title\":\"Draft Discord concept #6\",\"slug\":\"draft-discord-concept-6-qe4sa\",\"description\":\"Demo listing for Discord. Escrow-ready delivery with docs.\",\"price\":\"8500.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"draft\",\"featured\":false,\"created_at\":\"2026-04-10T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":56,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'da46d172-1a6a-45a9-b928-d98431f37618', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(106, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":57,\"user_id\":39,\"category_id\":13,\"marketplace_product_id\":7,\"title\":\"Needs changes VPN #7\",\"slug\":\"needs-changes-vpn-7-gvyte\",\"description\":\"Demo listing for VPN. Escrow-ready delivery with docs.\",\"price\":\"12000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"rejected\",\"featured\":false,\"created_at\":\"2026-06-11T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":57,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '04cb8401-b689-4c8d-996f-6d188f822301', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(107, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":58,\"user_id\":23,\"category_id\":13,\"marketplace_product_id\":8,\"title\":\"Suspended Proxy #8\",\"slug\":\"suspended-proxy-8-zu840\",\"description\":\"Demo listing for Proxy. Escrow-ready delivery with docs.\",\"price\":\"25000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"suspended\",\"featured\":false,\"created_at\":\"2026-01-12T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":58,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '6456b988-bd5b-453c-8d88-9ba27e42692b', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(108, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":59,\"user_id\":24,\"category_id\":13,\"marketplace_product_id\":9,\"title\":\"Sold RDP inventory #9\",\"slug\":\"sold-rdp-inventory-9-lelre\",\"description\":\"Demo listing for RDP. Escrow-ready delivery with docs.\",\"price\":\"45000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"sold\",\"featured\":false,\"created_at\":\"2026-01-13T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":59,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '062fecce-bbcc-4693-b92a-84382dda32de', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(109, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":60,\"user_id\":27,\"category_id\":13,\"marketplace_product_id\":10,\"title\":\"Archived VPS #10\",\"slug\":\"archived-vps-10-b2kdl\",\"description\":\"Demo listing for VPS. Escrow-ready delivery with docs.\",\"price\":\"6500.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"archived\",\"featured\":false,\"created_at\":\"2026-01-14T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":60,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'aec66497-fff8-4df5-9ce7-ded7fbe71fd6', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(110, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":61,\"user_id\":30,\"category_id\":13,\"marketplace_product_id\":11,\"title\":\"Live SMTP offer #11\",\"slug\":\"live-smtp-offer-11-vpmz2\",\"description\":\"Demo listing for SMTP. Escrow-ready delivery with docs.\",\"price\":\"8500.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-01-15T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":61,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'ae89b933-4c7a-4a09-80bb-6c5b5bc1f989', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(111, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":62,\"user_id\":33,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Pending review: Websites lot #12\",\"slug\":\"pending-review-websites-lot-12-npzda\",\"description\":\"Demo listing for Websites. Escrow-ready delivery with docs.\",\"price\":\"12000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"pending_review\",\"featured\":false,\"created_at\":\"2026-03-16T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":62,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'bee4f479-77e3-4bb4-a901-b865314e780e', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(112, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":63,\"user_id\":36,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Live Domains offer #13\",\"slug\":\"live-domains-offer-13-hvv0b\",\"description\":\"Demo listing for Domains. Escrow-ready delivery with docs.\",\"price\":\"25000.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-04-17T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":63,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '80bf26a0-adda-4384-ae52-69496936c82d', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(113, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":64,\"user_id\":39,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Live Source Code offer #14\",\"slug\":\"live-source-code-offer-14-em6w4\",\"description\":\"Demo listing for Source Code. Escrow-ready delivery with docs.\",\"price\":\"45000.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-06-18T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":64,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '1300ebac-004a-4344-8f38-d9524507722e', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(114, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":65,\"user_id\":23,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Live Graphics offer #15\",\"slug\":\"live-graphics-offer-15-hhphw\",\"description\":\"Demo listing for Graphics. Escrow-ready delivery with docs.\",\"price\":\"6500.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-01-19T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":65,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '438210aa-67d0-448d-8438-f68cf5e68582', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(115, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":66,\"user_id\":24,\"category_id\":6,\"marketplace_product_id\":1,\"title\":\"Live Facebook offer #16\",\"slug\":\"live-facebook-offer-16-e9v8w\",\"description\":\"Demo listing for Facebook. Escrow-ready delivery with docs.\",\"price\":\"8500.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-01-20T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":66,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '4cca76e8-1360-47fd-b39d-3e894323a682', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(116, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":67,\"user_id\":27,\"category_id\":6,\"marketplace_product_id\":2,\"title\":\"Pending review: Twitter \\/ X lot #17\",\"slug\":\"pending-review-twitter-x-lot-17-cmpcr\",\"description\":\"Demo listing for Twitter \\/ X. Escrow-ready delivery with docs.\",\"price\":\"12000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"pending_review\",\"featured\":false,\"created_at\":\"2026-01-21T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":67,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '6dcf0b6e-d4b4-4779-aef7-2fe2f94dbd45', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(117, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":68,\"user_id\":30,\"category_id\":6,\"marketplace_product_id\":3,\"title\":\"Draft TikTok concept #18\",\"slug\":\"draft-tiktok-concept-18-zqb3o\",\"description\":\"Demo listing for TikTok. Escrow-ready delivery with docs.\",\"price\":\"25000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"draft\",\"featured\":false,\"created_at\":\"2026-01-22T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":68,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '249b1e70-e9d8-4871-be2d-9d9a8db75491', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(118, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":69,\"user_id\":33,\"category_id\":6,\"marketplace_product_id\":4,\"title\":\"Needs changes Instagram #19\",\"slug\":\"needs-changes-instagram-19-ociuy\",\"description\":\"Demo listing for Instagram. Escrow-ready delivery with docs.\",\"price\":\"45000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"rejected\",\"featured\":false,\"created_at\":\"2026-03-23T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":69,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '54635b2c-5260-4d9b-95af-777d0d95fb27', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(119, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":70,\"user_id\":36,\"category_id\":6,\"marketplace_product_id\":5,\"title\":\"Suspended LinkedIn #20\",\"slug\":\"suspended-linkedin-20-mojdf\",\"description\":\"Demo listing for LinkedIn. Escrow-ready delivery with docs.\",\"price\":\"6500.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"suspended\",\"featured\":false,\"created_at\":\"2026-04-24T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":70,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'f788d639-a042-43e8-ad83-4b643d3c27c1', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(120, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":71,\"user_id\":39,\"category_id\":6,\"marketplace_product_id\":6,\"title\":\"Sold Discord inventory #21\",\"slug\":\"sold-discord-inventory-21-udle1\",\"description\":\"Demo listing for Discord. Escrow-ready delivery with docs.\",\"price\":\"8500.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"sold\",\"featured\":false,\"created_at\":\"2026-06-05T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":71,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '559465cf-f8f7-4cda-9d7b-03be5285f886', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(121, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":72,\"user_id\":23,\"category_id\":13,\"marketplace_product_id\":7,\"title\":\"Archived VPN #22\",\"slug\":\"archived-vpn-22-rm3hw\",\"description\":\"Demo listing for VPN. Escrow-ready delivery with docs.\",\"price\":\"12000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"archived\",\"featured\":false,\"created_at\":\"2026-01-06T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":72,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'b4da51ad-6c88-47fd-93b7-d921c4b6add9', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(122, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":73,\"user_id\":24,\"category_id\":13,\"marketplace_product_id\":8,\"title\":\"Live Proxy offer #23\",\"slug\":\"live-proxy-offer-23-wd5kk\",\"description\":\"Demo listing for Proxy. Escrow-ready delivery with docs.\",\"price\":\"25000.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":true,\"created_at\":\"2026-01-07T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":73,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '42698dc0-ea2d-49f9-89a9-7cced8639efb', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(123, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":74,\"user_id\":27,\"category_id\":13,\"marketplace_product_id\":9,\"title\":\"Pending review: RDP lot #24\",\"slug\":\"pending-review-rdp-lot-24-1zjkq\",\"description\":\"Demo listing for RDP. Escrow-ready delivery with docs.\",\"price\":\"45000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"pending_review\",\"featured\":false,\"created_at\":\"2026-01-08T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":74,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '6dd58ac7-fcee-4562-b1d6-744d048d6be7', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(124, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":75,\"user_id\":30,\"category_id\":13,\"marketplace_product_id\":10,\"title\":\"Live VPS offer #25\",\"slug\":\"live-vps-offer-25-qj4uq\",\"description\":\"Demo listing for VPS. Escrow-ready delivery with docs.\",\"price\":\"6500.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-01-09T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":75,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'd0eb775f-3668-4093-919c-c2ba98f96a15', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(125, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":76,\"user_id\":33,\"category_id\":13,\"marketplace_product_id\":11,\"title\":\"Live SMTP offer #26\",\"slug\":\"live-smtp-offer-26-ip2vs\",\"description\":\"Demo listing for SMTP. Escrow-ready delivery with docs.\",\"price\":\"8500.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-03-10T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":76,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'e1b01a70-1c80-45c0-8be3-137f402c3479', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(126, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":77,\"user_id\":36,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Live Websites offer #27\",\"slug\":\"live-websites-offer-27-5dsw6\",\"description\":\"Demo listing for Websites. Escrow-ready delivery with docs.\",\"price\":\"12000.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-04-11T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":77,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'b95a9e16-daf8-43c7-a882-83fa4e21aef7', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(127, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":78,\"user_id\":39,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Live Domains offer #28\",\"slug\":\"live-domains-offer-28-n6xov\",\"description\":\"Demo listing for Domains. Escrow-ready delivery with docs.\",\"price\":\"25000.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-06-12T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":78,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '6c8f42fa-2356-4e9d-a1e9-97f048a2344c', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(128, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":79,\"user_id\":23,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Pending review: Source Code lot #29\",\"slug\":\"pending-review-source-code-lot-29-329ud\",\"description\":\"Demo listing for Source Code. Escrow-ready delivery with docs.\",\"price\":\"45000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"pending_review\",\"featured\":false,\"created_at\":\"2026-01-13T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":79,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '4ab9755d-5b14-4e7a-bedc-91f48b7ca123', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(129, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":80,\"user_id\":24,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Draft Graphics concept #30\",\"slug\":\"draft-graphics-concept-30-d4bal\",\"description\":\"Demo listing for Graphics. Escrow-ready delivery with docs.\",\"price\":\"6500.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"draft\",\"featured\":false,\"created_at\":\"2026-01-14T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":80,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'a80b4063-4ca9-4ba6-8236-bb77639cff8c', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(130, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":81,\"user_id\":27,\"category_id\":6,\"marketplace_product_id\":1,\"title\":\"Needs changes Facebook #31\",\"slug\":\"needs-changes-facebook-31-tsosa\",\"description\":\"Demo listing for Facebook. Escrow-ready delivery with docs.\",\"price\":\"8500.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"rejected\",\"featured\":false,\"created_at\":\"2026-01-15T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":81,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'c094935d-b0bc-4df6-b069-153adcdd8b46', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(131, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":82,\"user_id\":30,\"category_id\":6,\"marketplace_product_id\":2,\"title\":\"Suspended Twitter \\/ X #32\",\"slug\":\"suspended-twitter-x-32-cwlew\",\"description\":\"Demo listing for Twitter \\/ X. Escrow-ready delivery with docs.\",\"price\":\"12000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"suspended\",\"featured\":false,\"created_at\":\"2026-01-16T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":82,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'ba032944-1ad6-432d-8f79-a8e8cd5b9de3', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(132, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":83,\"user_id\":33,\"category_id\":6,\"marketplace_product_id\":3,\"title\":\"Sold TikTok inventory #33\",\"slug\":\"sold-tiktok-inventory-33-yfzyx\",\"description\":\"Demo listing for TikTok. Escrow-ready delivery with docs.\",\"price\":\"25000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"sold\",\"featured\":false,\"created_at\":\"2026-03-17T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":83,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'd21df216-395d-45e2-83f2-5b8a62f37777', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(133, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":84,\"user_id\":36,\"category_id\":6,\"marketplace_product_id\":4,\"title\":\"Archived Instagram #34\",\"slug\":\"archived-instagram-34-akupo\",\"description\":\"Demo listing for Instagram. Escrow-ready delivery with docs.\",\"price\":\"45000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"archived\",\"featured\":false,\"created_at\":\"2026-04-18T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":84,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '4f2273e8-3238-493f-bd69-fc29a7ff5e24', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(134, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":85,\"user_id\":39,\"category_id\":6,\"marketplace_product_id\":5,\"title\":\"Live LinkedIn offer #35\",\"slug\":\"live-linkedin-offer-35-sbmdd\",\"description\":\"Demo listing for LinkedIn. Escrow-ready delivery with docs.\",\"price\":\"6500.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-06-19T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":85,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'fe95a49a-27eb-4d03-a1fb-32d2c713a7d8', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(135, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":86,\"user_id\":23,\"category_id\":6,\"marketplace_product_id\":6,\"title\":\"Pending review: Discord lot #36\",\"slug\":\"pending-review-discord-lot-36-dlnxu\",\"description\":\"Demo listing for Discord. Escrow-ready delivery with docs.\",\"price\":\"8500.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"pending_review\",\"featured\":false,\"created_at\":\"2026-01-20T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":86,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'fcefd123-95b7-4619-8f37-4029efdf2550', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(136, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":87,\"user_id\":24,\"category_id\":13,\"marketplace_product_id\":7,\"title\":\"Live VPN offer #37\",\"slug\":\"live-vpn-offer-37-3zgb2\",\"description\":\"Demo listing for VPN. Escrow-ready delivery with docs.\",\"price\":\"12000.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-01-21T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":87,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '858fad3a-7081-4af0-aaa9-4d468c3e9bd8', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(137, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":88,\"user_id\":27,\"category_id\":13,\"marketplace_product_id\":8,\"title\":\"Live Proxy offer #38\",\"slug\":\"live-proxy-offer-38-nxky5\",\"description\":\"Demo listing for Proxy. Escrow-ready delivery with docs.\",\"price\":\"25000.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-01-22T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":88,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'e53272b8-c9ea-4831-a2c1-ce38dc34acec', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(138, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":89,\"user_id\":30,\"category_id\":13,\"marketplace_product_id\":9,\"title\":\"Live RDP offer #39\",\"slug\":\"live-rdp-offer-39-eg9lc\",\"description\":\"Demo listing for RDP. Escrow-ready delivery with docs.\",\"price\":\"45000.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-01-23T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":89,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '99d818ea-a53b-468f-9f94-bcece41f6427', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(139, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":90,\"user_id\":33,\"category_id\":13,\"marketplace_product_id\":10,\"title\":\"Live VPS offer #40\",\"slug\":\"live-vps-offer-40-rb9dw\",\"description\":\"Demo listing for VPS. Escrow-ready delivery with docs.\",\"price\":\"6500.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-03-24T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":90,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '26255090-f108-4607-9a28-4f3977aba929', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(140, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":91,\"user_id\":36,\"category_id\":13,\"marketplace_product_id\":11,\"title\":\"Pending review: SMTP lot #41\",\"slug\":\"pending-review-smtp-lot-41-l81mj\",\"description\":\"Demo listing for SMTP. Escrow-ready delivery with docs.\",\"price\":\"8500.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"pending_review\",\"featured\":false,\"created_at\":\"2026-04-05T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":91,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'acd434fc-de93-4de1-8038-ea1ee543bc9f', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(141, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":92,\"user_id\":39,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Draft Websites concept #42\",\"slug\":\"draft-websites-concept-42-1ul7l\",\"description\":\"Demo listing for Websites. Escrow-ready delivery with docs.\",\"price\":\"12000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"draft\",\"featured\":false,\"created_at\":\"2026-06-06T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":92,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'f4349297-0702-453a-bd44-40b0d89f24e6', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(142, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":93,\"user_id\":23,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Needs changes Domains #43\",\"slug\":\"needs-changes-domains-43-vaxt4\",\"description\":\"Demo listing for Domains. Escrow-ready delivery with docs.\",\"price\":\"25000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"rejected\",\"featured\":false,\"created_at\":\"2026-01-07T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":93,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '954488e1-7142-42a7-ad37-f5bbb6305610', '2026-08-01 20:33:20', '2026-08-01 20:33:20');
INSERT INTO `audit_logs` (`id`, `admin_id`, `actor_id`, `actor_type`, `action`, `module`, `model_type`, `model_id`, `old_values`, `new_values`, `ip`, `user_agent`, `device`, `browser`, `country`, `reason`, `correlation_id`, `request_id`, `created_at`, `updated_at`) VALUES
(143, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":94,\"user_id\":24,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Suspended Source Code #44\",\"slug\":\"suspended-source-code-44-flxbc\",\"description\":\"Demo listing for Source Code. Escrow-ready delivery with docs.\",\"price\":\"45000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"suspended\",\"featured\":false,\"created_at\":\"2026-01-08T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":94,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'eaf84957-535f-4b83-af3a-83aae4fd5dab', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(144, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":95,\"user_id\":27,\"category_id\":19,\"marketplace_product_id\":null,\"title\":\"Sold Graphics inventory #45\",\"slug\":\"sold-graphics-inventory-45-m3cw8\",\"description\":\"Demo listing for Graphics. Escrow-ready delivery with docs.\",\"price\":\"6500.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"sold\",\"featured\":false,\"created_at\":\"2026-01-09T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":95,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '23cd8a7d-7e3b-44db-87f4-b651b52f9d8e', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(145, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":96,\"user_id\":30,\"category_id\":6,\"marketplace_product_id\":1,\"title\":\"Archived Facebook #46\",\"slug\":\"archived-facebook-46-a5cqu\",\"description\":\"Demo listing for Facebook. Escrow-ready delivery with docs.\",\"price\":\"8500.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"archived\",\"featured\":false,\"created_at\":\"2026-01-10T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":96,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '49f64824-f514-480b-bcd6-d4d0ea4f4a5a', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(146, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":97,\"user_id\":33,\"category_id\":6,\"marketplace_product_id\":2,\"title\":\"Live Twitter \\/ X offer #47\",\"slug\":\"live-twitter-x-offer-47-5ixop\",\"description\":\"Demo listing for Twitter \\/ X. Escrow-ready delivery with docs.\",\"price\":\"12000.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-03-11T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":97,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '5a0e103c-f90c-4ed2-9583-29efec37898a', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(147, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":98,\"user_id\":36,\"category_id\":6,\"marketplace_product_id\":3,\"title\":\"Pending review: TikTok lot #48\",\"slug\":\"pending-review-tiktok-lot-48-ehgi7\",\"description\":\"Demo listing for TikTok. Escrow-ready delivery with docs.\",\"price\":\"25000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"pending_review\",\"featured\":false,\"created_at\":\"2026-04-12T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":98,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '38fdb071-23d9-4552-8bde-ee0728a93738', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(148, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":99,\"user_id\":39,\"category_id\":6,\"marketplace_product_id\":4,\"title\":\"Live Instagram offer #49\",\"slug\":\"live-instagram-offer-49-psife\",\"description\":\"Demo listing for Instagram. Escrow-ready delivery with docs.\",\"price\":\"45000.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-06-13T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":99,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '635b3a9d-9bb4-4660-8574-d7cd3731315f', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(149, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":100,\"user_id\":23,\"category_id\":6,\"marketplace_product_id\":5,\"title\":\"Live LinkedIn offer #50\",\"slug\":\"live-linkedin-offer-50-otrct\",\"description\":\"Demo listing for LinkedIn. Escrow-ready delivery with docs.\",\"price\":\"6500.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-01-14T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":100,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'b385f600-2ef3-451b-9760-114fbcd053ac', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(150, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":101,\"user_id\":24,\"category_id\":6,\"marketplace_product_id\":6,\"title\":\"Live Discord offer #51\",\"slug\":\"live-discord-offer-51-qt9oa\",\"description\":\"Demo listing for Discord. Escrow-ready delivery with docs.\",\"price\":\"8500.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-01-15T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":101,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'bc15b729-cd41-428c-a964-d1990a4adb55', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(151, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":102,\"user_id\":27,\"category_id\":13,\"marketplace_product_id\":7,\"title\":\"Live VPN offer #52\",\"slug\":\"live-vpn-offer-52-cynsz\",\"description\":\"Demo listing for VPN. Escrow-ready delivery with docs.\",\"price\":\"12000.00\",\"category\":null,\"icon_class\":null,\"is_active\":true,\"status\":\"published\",\"featured\":false,\"created_at\":\"2026-01-16T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":102,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '2f103f0e-fcab-42a2-8c5f-9883e98867c8', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(152, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":103,\"user_id\":30,\"category_id\":13,\"marketplace_product_id\":8,\"title\":\"Pending review: Proxy lot #53\",\"slug\":\"pending-review-proxy-lot-53-fbgmi\",\"description\":\"Demo listing for Proxy. Escrow-ready delivery with docs.\",\"price\":\"25000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"pending_review\",\"featured\":false,\"created_at\":\"2026-01-17T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":103,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'cd27bb2e-07f1-40b4-bfbb-607e6eb2ccbf', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(153, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":104,\"user_id\":33,\"category_id\":13,\"marketplace_product_id\":9,\"title\":\"Draft RDP concept #54\",\"slug\":\"draft-rdp-concept-54-ffuqs\",\"description\":\"Demo listing for RDP. Escrow-ready delivery with docs.\",\"price\":\"45000.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"draft\",\"featured\":false,\"created_at\":\"2026-03-18T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":104,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '311c57e9-5459-475c-a7e1-af3369df704f', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(154, 1, 1, 'admin', 'listing.deleted', 'listing', NULL, NULL, '{\"id\":105,\"user_id\":36,\"category_id\":13,\"marketplace_product_id\":10,\"title\":\"Needs changes VPS #55\",\"slug\":\"needs-changes-vps-55-kh31f\",\"description\":\"Demo listing for VPS. Escrow-ready delivery with docs.\",\"price\":\"6500.00\",\"category\":null,\"icon_class\":null,\"is_active\":false,\"status\":\"rejected\",\"featured\":false,\"created_at\":\"2026-04-19T12:15:00.000000Z\",\"updated_at\":\"2026-08-01T19:50:46.000000Z\",\"deleted_at\":\"2026-08-01T19:50:46.000000Z\"}', '{\"id\":105,\"force\":true,\"bulk\":true}', '102.89.46.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '5c5cdacb-f8f9-4a6f-8021-a9b908ed7e63', '2026-08-01 20:33:20', '2026-08-01 20:33:20'),
(155, 1, 1, 'admin', 'settings.analytics.updated', 'settings', NULL, NULL, NULL, '{\"google_enabled\":true,\"clarity_enabled\":false}', '102.89.76.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '61ee5b6b-92a5-4d40-bc45-59888619c684', '2026-08-01 23:10:28', '2026-08-01 23:10:28'),
(156, 1, 1, 'admin', 'settings.analytics.connection_test', 'settings', NULL, NULL, NULL, '{\"provider\":\"google_analytics\",\"ok\":true}', '102.89.76.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'a1635a54-ce9f-4d1b-a64d-c727fec7314e', '2026-08-01 23:10:47', '2026-08-01 23:10:47'),
(157, 1, 1, 'admin', 'support.assigned', 'support', 'App\\Models\\SupportTicket', 41, '{\"assigned_to\":null}', '{\"assigned_to\":\"1\"}', '102.89.75.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '27a1e1f9-4e61-4eeb-8f57-53d08dc3aabb', '2026-08-01 23:53:43', '2026-08-01 23:53:43'),
(158, 1, 1, 'admin', 'support.status_updated', 'support', 'App\\Models\\SupportTicket', 41, '{\"status\":\"open\"}', '{\"status\":\"closed\"}', '102.89.75.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '38b1cec8-fde9-4729-a8f1-998c82bfe28b', '2026-08-01 23:53:59', '2026-08-01 23:53:59'),
(159, 1, 1, 'admin', 'settings.tracking.updated', 'settings', NULL, NULL, NULL, '{\"gtm_enabled\":true,\"google_enabled\":true,\"clarity_enabled\":false,\"meta_enabled\":false}', '102.89.75.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'c73db8c4-45e5-49c6-94d5-7c20a167d826', '2026-08-02 00:20:37', '2026-08-02 00:20:37'),
(160, 1, 1, 'admin', 'settings.tracking.updated', 'settings', NULL, NULL, NULL, '{\"gtm_enabled\":true,\"google_enabled\":false,\"clarity_enabled\":false,\"meta_enabled\":false}', '102.89.75.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '1aa2a39c-bb79-4640-8c0f-fedb5556bbec', '2026-08-02 00:21:43', '2026-08-02 00:21:43'),
(161, 1, 1, 'admin', 'user.impersonation.started', 'user', 'App\\Models\\User', 42, NULL, '{\"impersonator_id\":1,\"target_user_id\":42}', '102.89.75.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '4b78bd86-5c1e-4ee9-a021-06bf6146b7bd', '2026-08-02 00:28:15', '2026-08-02 00:28:15'),
(162, 1, 1, 'admin', 'user.impersonation.stopped', 'user', 'App\\Models\\User', 42, NULL, '{\"impersonator_id\":1,\"target_user_id\":42}', '102.89.75.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '2483c072-6cb0-4834-9e0d-872bf434f353', '2026-08-02 00:31:06', '2026-08-02 00:31:06'),
(163, 1, 1, 'admin', 'settings.branding.updated', 'settings', NULL, NULL, NULL, '{\"site_name\":\"7th Trade Hub\",\"pwa_icons_synced\":true}', '102.89.75.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '86e3154e-ab60-4420-b8d2-2d11698d6cc8', '2026-08-02 01:03:41', '2026-08-02 01:03:41'),
(164, 1, 1, 'admin', 'settings.branding.updated', 'settings', NULL, NULL, NULL, '{\"site_name\":\"7th Trade Hub\",\"pwa_icons_synced\":true}', '102.89.75.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'a095850e-eadc-458c-8c4c-c94a6ff7180f', '2026-08-02 01:22:51', '2026-08-02 01:22:51'),
(165, 1, 1, 'admin', 'settings.branding.updated', 'settings', NULL, NULL, NULL, '{\"site_name\":\"7th Trade Hub\",\"pwa_icons_synced\":true}', '102.89.75.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'fd7d908c-0fd1-4a3f-a41c-cc2690d07460', '2026-08-02 01:23:00', '2026-08-02 01:23:00'),
(166, 1, 1, 'admin', 'user.impersonation.started', 'user', 'App\\Models\\User', 42, NULL, '{\"impersonator_id\":1,\"target_user_id\":42}', '102.89.75.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '9ab8c5e7-c058-404f-9f0b-3557b738104f', '2026-08-02 01:30:51', '2026-08-02 01:30:51'),
(167, 1, 1, 'admin', 'settings.branding.updated', 'settings', NULL, NULL, NULL, '{\"site_name\":\"7th Trade Hub\",\"pwa_icons_synced\":true}', '51.158.254.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '55601b6b-9a62-4b98-a8fe-e7843728c99a', '2026-08-02 13:03:48', '2026-08-02 13:03:48'),
(168, 1, 1, 'admin', 'settings.blockchain.updated', 'settings', 'App\\Models\\IntegrationProvider', 12, NULL, '{\"enabled\":false,\"monitor_provider\":\"native\"}', '51.158.254.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'f409ffda-1fa0-4292-83f9-0edeb3a645ee', '2026-08-02 16:09:23', '2026-08-02 16:09:23'),
(169, 1, 1, 'admin', 'settings.blockchain.updated', 'settings', 'App\\Models\\IntegrationProvider', 12, NULL, '{\"enabled\":true,\"monitor_provider\":\"native\"}', '51.158.254.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'ce27d09a-8908-4505-85ba-ac0c4f080a01', '2026-08-02 16:09:40', '2026-08-02 16:09:40'),
(170, 1, 1, 'admin', 'settings.blockchain.connection_test', 'settings', 'App\\Models\\IntegrationProvider', 12, NULL, '{\"ok\":true,\"network\":\"bitcoin\",\"provider\":\"native\"}', '51.158.254.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'fa26f06c-c8fe-43ce-bb7b-7560f172b5cd', '2026-08-02 16:11:22', '2026-08-02 16:11:22'),
(171, 1, 1, 'admin', 'settings.blockchain.updated', 'settings', 'App\\Models\\IntegrationProvider', 12, NULL, '{\"enabled\":true,\"monitor_provider\":\"native\"}', '51.158.254.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '8b74198c-a69c-4e90-8551-d4a0893be8bc', '2026-08-02 16:22:36', '2026-08-02 16:22:36'),
(172, 1, 1, 'admin', 'settings.blockchain.connection_test', 'settings', 'App\\Models\\IntegrationProvider', 12, NULL, '{\"ok\":true,\"network\":\"ethereum\",\"provider\":\"native\"}', '51.158.254.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '7407890c-c883-4750-9414-92d2fbef8e70', '2026-08-02 16:22:50', '2026-08-02 16:22:50'),
(173, 1, 1, 'admin', 'settings.blockchain.connection_test', 'settings', 'App\\Models\\IntegrationProvider', 12, NULL, '{\"ok\":true,\"network\":\"bep20\",\"provider\":\"native\"}', '51.158.254.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '03cb3222-52d0-450c-bc6a-a5d8a2cb7f63', '2026-08-02 16:23:37', '2026-08-02 16:23:37'),
(174, 1, 1, 'admin', 'settings.blockchain.connection_test', 'settings', 'App\\Models\\IntegrationProvider', 12, NULL, '{\"ok\":true,\"network\":\"polygon\",\"provider\":\"native\"}', '51.158.254.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '77f172e4-deb3-4965-9183-f24563ed7196', '2026-08-02 16:23:42', '2026-08-02 16:23:42'),
(175, 1, 1, 'admin', 'settings.blockchain.connection_test', 'settings', 'App\\Models\\IntegrationProvider', 12, NULL, '{\"ok\":true,\"network\":\"base\",\"provider\":\"native\"}', '51.158.254.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'a6552689-5fa9-4b8b-b571-1d7b791ac85f', '2026-08-02 16:23:47', '2026-08-02 16:23:47'),
(176, 1, 1, 'admin', 'settings.blockchain.connection_test', 'settings', 'App\\Models\\IntegrationProvider', 12, NULL, '{\"ok\":true,\"network\":\"arbitrum\",\"provider\":\"native\"}', '51.158.254.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '66dfd594-dd2f-4019-bc18-8064780e4ca0', '2026-08-02 16:23:52', '2026-08-02 16:23:52'),
(177, 1, 1, 'admin', 'settings.blockchain.connection_test', 'settings', 'App\\Models\\IntegrationProvider', 12, NULL, '{\"ok\":true,\"network\":\"tron\",\"provider\":\"native\"}', '51.158.254.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '7616b0e1-210c-4966-8e2d-44c24f9be5a0', '2026-08-02 16:23:57', '2026-08-02 16:23:57'),
(178, 1, 1, 'admin', 'settings.blockchain.connection_test', 'settings', 'App\\Models\\IntegrationProvider', 12, NULL, '{\"ok\":true,\"network\":\"solana\",\"provider\":\"native\"}', '51.158.254.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'f4ea1ea3-84bc-4351-90c8-a62c8c07fb42', '2026-08-02 16:24:01', '2026-08-02 16:24:01'),
(179, 1, 1, 'admin', 'crypto_wallet.created', 'crypto_wallet', 'App\\Models\\CryptoDepositWallet', 1, NULL, '{\"coin\":\"BTC\",\"network\":\"Bitcoin\",\"address\":\"1GmJBcNyG97ULTgDQBYDRcjCQpUca54AZH\",\"is_active\":true,\"is_exchange_managed\":true,\"required_confirmations\":2,\"sort_order\":1,\"updated_at\":\"2026-08-02T16:47:08.000000Z\",\"created_at\":\"2026-08-02T16:47:08.000000Z\",\"id\":1}', '51.158.254.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '3bba3dcb-8b26-4cb9-b99f-a36ce8915d5d', '2026-08-02 16:47:08', '2026-08-02 16:47:08'),
(180, 1, 1, 'admin', 'crypto_wallet.treasury_refresh', 'crypto_wallet', NULL, NULL, NULL, '{\"output\":\"Polled 1 wallet(s); 1 updated; 0 error(s).\"}', '51.158.254.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '4fda44d0-885d-40c5-9da8-1f4ae36bef28', '2026-08-02 16:47:28', '2026-08-02 16:47:28'),
(181, 1, 1, 'admin', 'user.impersonation.started', 'user', 'App\\Models\\User', 42, NULL, '{\"impersonator_id\":1,\"target_user_id\":42}', '51.158.254.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, '2e813fac-a8db-433a-8dc3-84b1882db49f', '2026-08-02 17:57:51', '2026-08-02 17:57:51'),
(182, 1, 1, 'admin', 'user.impersonation.stopped', 'user', 'App\\Models\\User', 42, NULL, '{\"impersonator_id\":1,\"target_user_id\":42}', '51.158.254.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'fe3b3bef-2c8e-4a86-b5de-7ed6698c4d97', '2026-08-02 19:49:26', '2026-08-02 19:49:26'),
(183, 1, 1, 'admin', 'user.impersonation.started', 'user', 'App\\Models\\User', 42, NULL, '{\"impersonator_id\":1,\"target_user_id\":42}', '51.158.254.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'desktop', 'chrome', NULL, NULL, NULL, 'ae1b6afa-d1eb-41a9-9704-e88cf2e045d2', '2026-08-02 19:55:40', '2026-08-02 19:55:40'),
(184, 1, 1, 'admin', 'crypto_wallet.created', 'crypto_wallet', 'App\\Models\\CryptoDepositWallet', 2, NULL, '{\"coin\":\"USDT\",\"network\":\"ethereum\",\"address\":\"0x9ba63ed8be55b83c7bfd975b798f9d1c416798cd\",\"required_confirmations\":12,\"label\":null,\"purpose\":null,\"owner\":null,\"is_active\":true,\"is_exchange_managed\":true,\"sort_order\":2,\"updated_at\":\"2026-08-02T23:49:23.000000Z\",\"created_at\":\"2026-08-02T23:49:23.000000Z\",\"id\":2}', '102.89.84.125', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36', 'mobile', 'chrome', NULL, NULL, NULL, 'a6ba9545-c717-4f1f-8d5f-24f0538cf83c', '2026-08-02 23:49:23', '2026-08-02 23:49:23'),
(185, 1, 1, 'admin', 'crypto_wallet.created', 'crypto_wallet', 'App\\Models\\CryptoDepositWallet', 3, NULL, '{\"coin\":\"USDT\",\"network\":\"ethereum\",\"address\":\"0x1f907f34d61660f9cf615325e66217d52eb2b31c\",\"required_confirmations\":12,\"label\":null,\"purpose\":null,\"owner\":null,\"is_active\":true,\"is_exchange_managed\":false,\"sort_order\":3,\"updated_at\":\"2026-08-02T23:50:30.000000Z\",\"created_at\":\"2026-08-02T23:50:30.000000Z\",\"id\":3}', '102.89.84.125', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36', 'mobile', 'chrome', NULL, NULL, NULL, '736d8481-8f23-4fa2-83e2-ee204efdde4f', '2026-08-02 23:50:30', '2026-08-02 23:50:30'),
(186, 1, 1, 'admin', 'crypto_wallet.created', 'crypto_wallet', 'App\\Models\\CryptoDepositWallet', 4, NULL, '{\"coin\":\"USDT\",\"network\":\"ethereum\",\"address\":\"0x4d27c006d198964e6b92b781ca96eec57d9422da\",\"required_confirmations\":12,\"label\":null,\"purpose\":null,\"owner\":null,\"is_active\":true,\"is_exchange_managed\":false,\"sort_order\":4,\"updated_at\":\"2026-08-02T23:51:38.000000Z\",\"created_at\":\"2026-08-02T23:51:38.000000Z\",\"id\":4}', '102.89.84.125', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36', 'mobile', 'chrome', NULL, NULL, NULL, 'de98cd1f-c9d9-4e09-9a79-76d59cbd4590', '2026-08-02 23:51:38', '2026-08-02 23:51:38'),
(187, 1, 1, 'admin', 'crypto_wallet.created', 'crypto_wallet', 'App\\Models\\CryptoDepositWallet', 5, NULL, '{\"coin\":\"BTC\",\"network\":\"bitcoin\",\"address\":\"1NdxcuZAQT4nQyDLPbfnLR38Qrr3Jk9iYw\",\"required_confirmations\":2,\"label\":null,\"purpose\":null,\"owner\":null,\"is_active\":true,\"is_exchange_managed\":true,\"sort_order\":5,\"updated_at\":\"2026-08-02T23:54:05.000000Z\",\"created_at\":\"2026-08-02T23:54:05.000000Z\",\"id\":5}', '102.89.84.125', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36', 'mobile', 'chrome', NULL, NULL, NULL, 'd4e6f50e-9444-4c94-bea2-bf348e62270e', '2026-08-02 23:54:05', '2026-08-02 23:54:05'),
(188, 1, 1, 'admin', 'crypto_wallet.treasury_refresh', 'crypto_wallet', NULL, NULL, NULL, '{\"output\":\"Polled 5 wallet(s); 5 updated; 0 error(s).\"}', '102.89.84.125', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36', 'mobile', 'chrome', NULL, NULL, NULL, 'd7c74f56-f162-4dd0-be54-21782cc22d70', '2026-08-02 23:55:44', '2026-08-02 23:55:44'),
(189, 1, 1, 'admin', 'user.impersonation.started', 'user', 'App\\Models\\User', 42, NULL, '{\"impersonator_id\":1,\"target_user_id\":42}', '102.89.84.125', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36', 'mobile', 'chrome', NULL, NULL, NULL, 'fb83ab8e-d0ac-4f43-8d82-9fd4ebf0d4c9', '2026-08-02 23:58:24', '2026-08-02 23:58:24');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('7th-trade-hub-cache-08ae5782a0dc4ac0addd673ce8cc82b870e00030', 'i:1;', 1784770710),
('7th-trade-hub-cache-08ae5782a0dc4ac0addd673ce8cc82b870e00030:timer', 'i:1784770710;', 1784770710),
('7th-trade-hub-cache-1574bddb75c78a6fd2251d61e2993b5146201319', 'i:2;', 1785553399),
('7th-trade-hub-cache-1574bddb75c78a6fd2251d61e2993b5146201319:timer', 'i:1785553399;', 1785553399),
('7th-trade-hub-cache-1686243e9902c1933e638a0d60ad22133df99581', 'i:1;', 1785613006),
('7th-trade-hub-cache-1686243e9902c1933e638a0d60ad22133df99581:timer', 'i:1785613006;', 1785613006),
('7th-trade-hub-cache-31552f4f81f4ac0d4623e8f79363f1b264f57b43', 'i:1;', 1784833041),
('7th-trade-hub-cache-31552f4f81f4ac0d4623e8f79363f1b264f57b43:timer', 'i:1784833041;', 1784833041),
('7th-trade-hub-cache-356a192b7913b04c54574d18c28d46e6395428ab', 'i:2;', 1788015690),
('7th-trade-hub-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1788015690;', 1788015690),
('7th-trade-hub-cache-39f1df1a32f25b732e93a0bf7026966bf50d1326', 'i:1;', 1784731793),
('7th-trade-hub-cache-39f1df1a32f25b732e93a0bf7026966bf50d1326:timer', 'i:1784731793;', 1784731793),
('7th-trade-hub-cache-40ce6fa28f33ec572bb3bb7ef75f5e67620faad6', 'i:1;', 1784775919),
('7th-trade-hub-cache-40ce6fa28f33ec572bb3bb7ef75f5e67620faad6:timer', 'i:1784775919;', 1784775919),
('7th-trade-hub-cache-50745432aaa82d402f759b06e81d0cd08dbdbebe', 'i:2;', 1784647485),
('7th-trade-hub-cache-50745432aaa82d402f759b06e81d0cd08dbdbebe:timer', 'i:1784647485;', 1784647485),
('7th-trade-hub-cache-59a769f58f9ed475949aebf02faf6cf4de9c670d', 'i:1;', 1784817218),
('7th-trade-hub-cache-59a769f58f9ed475949aebf02faf6cf4de9c670d:timer', 'i:1784817218;', 1784817218),
('7th-trade-hub-cache-637b08f510cd062f9ab6eab0b8271e4e296cbcde', 'i:1;', 1785634267),
('7th-trade-hub-cache-637b08f510cd062f9ab6eab0b8271e4e296cbcde:timer', 'i:1785634267;', 1785634267),
('7th-trade-hub-cache-6ac0b39224f5822311a7b3e893e9d9c61fb225aa', 'i:1;', 1785674134),
('7th-trade-hub-cache-6ac0b39224f5822311a7b3e893e9d9c61fb225aa:timer', 'i:1785674134;', 1785674134),
('7th-trade-hub-cache-79b0fb6218265f80b454ff4e6f1ab34fe456bdc2', 'i:1;', 1784766019),
('7th-trade-hub-cache-79b0fb6218265f80b454ff4e6f1ab34fe456bdc2:timer', 'i:1784766019;', 1784766019),
('7th-trade-hub-cache-859c8671c1215bc4fc03ce4553e2e35db85a32dd', 'i:1;', 1785714402),
('7th-trade-hub-cache-859c8671c1215bc4fc03ce4553e2e35db85a32dd:timer', 'i:1785714402;', 1785714402),
('7th-trade-hub-cache-881b57842984d5dce91f75f6904d4e032c822165', 'i:1;', 1788015329),
('7th-trade-hub-cache-881b57842984d5dce91f75f6904d4e032c822165:timer', 'i:1788015329;', 1788015329),
('7th-trade-hub-cache-92cfceb39d57d914ed8b14d0e37643de0797ae56', 'i:2;', 1785661354),
('7th-trade-hub-cache-92cfceb39d57d914ed8b14d0e37643de0797ae56:timer', 'i:1785661354;', 1785661354),
('7th-trade-hub-cache-b0f3ccdd96b5b49af76bd36031a8f1c5081eaa8f', 'i:1;', 1784850218),
('7th-trade-hub-cache-b0f3ccdd96b5b49af76bd36031a8f1c5081eaa8f:timer', 'i:1784850218;', 1784850218),
('7th-trade-hub-cache-b2f16b0b526afcecdcc09a36467e75b679c8cc80', 'i:1;', 1785625119),
('7th-trade-hub-cache-b2f16b0b526afcecdcc09a36467e75b679c8cc80:timer', 'i:1785625119;', 1785625119),
('7th-trade-hub-cache-bd6f8b1e9f9602bc5db485ee271300b69d08784b', 'i:1;', 1785000466),
('7th-trade-hub-cache-bd6f8b1e9f9602bc5db485ee271300b69d08784b:timer', 'i:1785000466;', 1785000466),
('7th-trade-hub-cache-c179470fa790ffd9c648099ecc9897244af54c9d', 'i:1;', 1785555020),
('7th-trade-hub-cache-c179470fa790ffd9c648099ecc9897244af54c9d:timer', 'i:1785555020;', 1785555020),
('7th-trade-hub-cache-cbbf88cb89eb25cf4dec80c73a8ae1eb0b4a3be8', 'i:1;', 1784650085),
('7th-trade-hub-cache-cbbf88cb89eb25cf4dec80c73a8ae1eb0b4a3be8:timer', 'i:1784650085;', 1784650085),
('7th-trade-hub-cache-cd054b9f61ea6ad8bd72c3d2880d811a307b03f0', 'i:1;', 1785615310),
('7th-trade-hub-cache-cd054b9f61ea6ad8bd72c3d2880d811a307b03f0:timer', 'i:1785615310;', 1785615310),
('7th-trade-hub-cache-crypto_markets_catalog_ngn', 'a:100:{i:0;a:6:{s:2:\"id\";s:7:\"bitcoin\";s:6:\"symbol\";s:3:\"BTC\";s:4:\"name\";s:7:\"Bitcoin\";s:4:\"logo\";s:77:\"https://coin-images.coingecko.com/coins/images/1/large/bitcoin.png?1696501400\";s:9:\"price_ngn\";d:85695703;s:10:\"change_24h\";d:0;}i:1;a:6:{s:2:\"id\";s:8:\"ethereum\";s:6:\"symbol\";s:3:\"ETH\";s:4:\"name\";s:8:\"Ethereum\";s:4:\"logo\";s:80:\"https://coin-images.coingecko.com/coins/images/279/large/ethereum.png?1696501628\";s:9:\"price_ngn\";d:2523933;s:10:\"change_24h\";d:-0.8;}i:2;a:6:{s:2:\"id\";s:6:\"tether\";s:6:\"symbol\";s:4:\"USDT\";s:4:\"name\";s:6:\"Tether\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/325/large/Tether.png?1696501661\";s:9:\"price_ngn\";d:1357.71;s:10:\"change_24h\";d:0;}i:3;a:6:{s:2:\"id\";s:11:\"binancecoin\";s:6:\"symbol\";s:3:\"BNB\";s:4:\"name\";s:3:\"BNB\";s:4:\"logo\";s:84:\"https://coin-images.coingecko.com/coins/images/825/large/bnb-icon2_2x.png?1696501970\";s:9:\"price_ngn\";d:796669;s:10:\"change_24h\";d:1.3;}i:4;a:6:{s:2:\"id\";s:8:\"usd-coin\";s:6:\"symbol\";s:4:\"USDC\";s:4:\"name\";s:4:\"USDC\";s:4:\"logo\";s:77:\"https://coin-images.coingecko.com/coins/images/6319/large/USDC.png?1769615602\";s:9:\"price_ngn\";d:1358.12;s:10:\"change_24h\";d:0;}i:5;a:6:{s:2:\"id\";s:6:\"ripple\";s:6:\"symbol\";s:3:\"XRP\";s:4:\"name\";s:3:\"XRP\";s:4:\"logo\";s:91:\"https://coin-images.coingecko.com/coins/images/44/large/xrp-symbol-white-128.png?1696501442\";s:9:\"price_ngn\";d:1464.21;s:10:\"change_24h\";d:1.3;}i:6;a:6:{s:2:\"id\";s:6:\"solana\";s:6:\"symbol\";s:3:\"SOL\";s:4:\"name\";s:6:\"Solana\";s:4:\"logo\";s:79:\"https://coin-images.coingecko.com/coins/images/4128/large/solana.png?1718769756\";s:9:\"price_ngn\";d:99142;s:10:\"change_24h\";d:0.1;}i:7;a:6:{s:2:\"id\";s:4:\"tron\";s:6:\"symbol\";s:3:\"TRX\";s:4:\"name\";s:4:\"TRON\";s:4:\"logo\";s:98:\"https://coin-images.coingecko.com/coins/images/1094/large/photo_2026-04-13_09-59-16.png?1776048311\";s:9:\"price_ngn\";d:444.76;s:10:\"change_24h\";d:-0.3;}i:8;a:6:{s:2:\"id\";s:12:\"figure-heloc\";s:6:\"symbol\";s:10:\"FIGR_HELOC\";s:4:\"name\";s:12:\"Figure Heloc\";s:4:\"logo\";s:80:\"https://coin-images.coingecko.com/coins/images/68480/large/figure.png?1755863954\";s:9:\"price_ngn\";d:1359.82;s:10:\"change_24h\";d:-3.1;}i:9;a:6:{s:2:\"id\";s:8:\"whitebit\";s:6:\"symbol\";s:3:\"WBT\";s:4:\"name\";s:13:\"WhiteBIT Coin\";s:4:\"logo\";s:83:\"https://coin-images.coingecko.com/coins/images/27045/large/wbt_token.png?1696526096\";s:9:\"price_ngn\";d:74622;s:10:\"change_24h\";d:-0.2;}i:10;a:6:{s:2:\"id\";s:11:\"hyperliquid\";s:6:\"symbol\";s:4:\"HYPE\";s:4:\"name\";s:11:\"Hyperliquid\";s:4:\"logo\";s:85:\"https://coin-images.coingecko.com/coins/images/50882/large/hyperliquid.jpg?1729431300\";s:9:\"price_ngn\";d:69926;s:10:\"change_24h\";d:-1.8;}i:11;a:6:{s:2:\"id\";s:8:\"dogecoin\";s:6:\"symbol\";s:4:\"DOGE\";s:4:\"name\";s:8:\"Dogecoin\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/5/large/dogecoin.png?1696501409\";s:9:\"price_ngn\";d:95.34;s:10:\"change_24h\";d:0.2;}i:12;a:6:{s:2:\"id\";s:4:\"usds\";s:6:\"symbol\";s:4:\"USDS\";s:4:\"name\";s:4:\"USDS\";s:4:\"logo\";s:79:\"https://coin-images.coingecko.com/coins/images/39926/large/usds.webp?1726666683\";s:9:\"price_ngn\";d:1358.93;s:10:\"change_24h\";d:0;}i:13;a:6:{s:2:\"id\";s:9:\"leo-token\";s:6:\"symbol\";s:3:\"LEO\";s:4:\"name\";s:9:\"LEO Token\";s:4:\"logo\";s:82:\"https://coin-images.coingecko.com/coins/images/8418/large/leo-token.png?1696508607\";s:9:\"price_ngn\";d:13257.62;s:10:\"change_24h\";d:-0.1;}i:14;a:6:{s:2:\"id\";s:4:\"rain\";s:6:\"symbol\";s:4:\"RAIN\";s:4:\"name\";s:4:\"Rain\";s:4:\"logo\";s:86:\"https://coin-images.coingecko.com/coins/images/69134/large/Rain_logo_1_.png?1762952191\";s:9:\"price_ngn\";d:17.24;s:10:\"change_24h\";d:0.5;}i:15;a:6:{s:2:\"id\";s:5:\"zcash\";s:6:\"symbol\";s:3:\"ZEC\";s:4:\"name\";s:5:\"Zcash\";s:4:\"logo\";s:90:\"https://coin-images.coingecko.com/coins/images/486/large/circle-zcash-color.png?1696501740\";s:9:\"price_ngn\";d:640075;s:10:\"change_24h\";d:0.3;}i:16;a:6:{s:2:\"id\";s:7:\"cardano\";s:6:\"symbol\";s:3:\"ADA\";s:4:\"name\";s:7:\"Cardano\";s:4:\"logo\";s:79:\"https://coin-images.coingecko.com/coins/images/975/large/cardano.png?1696502090\";s:9:\"price_ngn\";d:257.85;s:10:\"change_24h\";d:9.7;}i:17;a:6:{s:2:\"id\";s:6:\"monero\";s:6:\"symbol\";s:3:\"XMR\";s:4:\"name\";s:6:\"Monero\";s:4:\"logo\";s:82:\"https://coin-images.coingecko.com/coins/images/69/large/monero_logo.png?1696501460\";s:9:\"price_ngn\";d:494813;s:10:\"change_24h\";d:1.2;}i:18;a:6:{s:2:\"id\";s:9:\"chainlink\";s:6:\"symbol\";s:4:\"LINK\";s:4:\"name\";s:9:\"Chainlink\";s:4:\"logo\";s:90:\"https://coin-images.coingecko.com/coins/images/877/large/Chainlink_Logo_500.png?1760023405\";s:9:\"price_ngn\";d:11210.58;s:10:\"change_24h\";d:2.1;}i:19;a:6:{s:2:\"id\";s:7:\"stellar\";s:6:\"symbol\";s:3:\"XLM\";s:4:\"name\";s:7:\"Stellar\";s:4:\"logo\";s:88:\"https://coin-images.coingecko.com/coins/images/100/large/fmpFRHHQ_400x400.jpg?1735231350\";s:9:\"price_ngn\";d:236.07;s:10:\"change_24h\";d:1.7;}i:20;a:6:{s:2:\"id\";s:3:\"dai\";s:6:\"symbol\";s:3:\"DAI\";s:4:\"name\";s:3:\"Dai\";s:4:\"logo\";s:82:\"https://coin-images.coingecko.com/coins/images/9956/large/Badge_Dai.png?1696509996\";s:9:\"price_ngn\";d:1358.78;s:10:\"change_24h\";d:0;}i:21;a:6:{s:2:\"id\";s:14:\"canton-network\";s:6:\"symbol\";s:2:\"CC\";s:4:\"name\";s:6:\"Canton\";s:4:\"logo\";s:95:\"https://coin-images.coingecko.com/coins/images/70468/large/Canton-Ticker_%281%29.png?1762826299\";s:9:\"price_ngn\";d:158.31;s:10:\"change_24h\";d:-0.3;}i:22;a:6:{s:2:\"id\";s:12:\"bitcoin-cash\";s:6:\"symbol\";s:3:\"BCH\";s:4:\"name\";s:12:\"Bitcoin Cash\";s:4:\"logo\";s:91:\"https://coin-images.coingecko.com/coins/images/780/large/bitcoin-cash-circle.png?1696501932\";s:9:\"price_ngn\";d:288817;s:10:\"change_24h\";d:2.2;}i:23;a:6:{s:2:\"id\";s:9:\"usd1-wlfi\";s:6:\"symbol\";s:4:\"USD1\";s:4:\"name\";s:4:\"USD1\";s:4:\"logo\";s:100:\"https://coin-images.coingecko.com/coins/images/54977/large/USD1_1000x1000_transparent.png?1749297002\";s:9:\"price_ngn\";d:1357.8;s:10:\"change_24h\";d:0;}i:24;a:6:{s:2:\"id\";s:11:\"ethena-usde\";s:6:\"symbol\";s:4:\"USDE\";s:4:\"name\";s:11:\"Ethena USDe\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/33613/large/usde.png?1733810059\";s:9:\"price_ngn\";d:1358.16;s:10:\"change_24h\";d:0;}i:25;a:6:{s:2:\"id\";s:16:\"the-open-network\";s:6:\"symbol\";s:4:\"GRAM\";s:4:\"name\";s:20:\"Gram (prev. Toncoin)\";s:4:\"logo\";s:93:\"https://coin-images.coingecko.com/coins/images/17980/large/Gram_Circular_Badge.png?1781524778\";s:9:\"price_ngn\";d:1918.99;s:10:\"change_24h\";d:1.2;}i:26;a:6:{s:2:\"id\";s:8:\"litecoin\";s:6:\"symbol\";s:3:\"LTC\";s:4:\"name\";s:8:\"Litecoin\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/2/large/litecoin.png?1696501400\";s:9:\"price_ngn\";d:60864;s:10:\"change_24h\";d:1.3;}i:27;a:6:{s:2:\"id\";s:13:\"global-dollar\";s:6:\"symbol\";s:4:\"USDG\";s:4:\"name\";s:13:\"Global Dollar\";s:4:\"logo\";s:96:\"https://coin-images.coingecko.com/coins/images/51281/large/GDN_USDG_Token_200x200.png?1730484111\";s:9:\"price_ngn\";d:1357.52;s:10:\"change_24h\";d:0;}i:28;a:6:{s:2:\"id\";s:16:\"hedera-hashgraph\";s:6:\"symbol\";s:4:\"HBAR\";s:4:\"name\";s:6:\"Hedera\";s:4:\"logo\";s:77:\"https://coin-images.coingecko.com/coins/images/3688/large/hbar.png?1696504364\";s:9:\"price_ngn\";d:95.08;s:10:\"change_24h\";d:-0.2;}i:29;a:6:{s:2:\"id\";s:13:\"hashnote-usyc\";s:6:\"symbol\";s:4:\"USYC\";s:4:\"name\";s:11:\"Circle USYC\";s:4:\"logo\";s:95:\"https://coin-images.coingecko.com/coins/images/51054/large/Hashnote_SDYC_200x200.png?1730370965\";s:9:\"price_ngn\";d:1538.55;s:10:\"change_24h\";d:0;}i:30;a:6:{s:2:\"id\";s:9:\"shiba-inu\";s:6:\"symbol\";s:4:\"SHIB\";s:4:\"name\";s:9:\"Shiba Inu\";s:4:\"logo\";s:79:\"https://coin-images.coingecko.com/coins/images/11939/large/shiba.png?1696511800\";s:9:\"price_ngn\";d:0.00668225;s:10:\"change_24h\";d:-0.2;}i:31;a:6:{s:2:\"id\";s:11:\"avalanche-2\";s:6:\"symbol\";s:4:\"AVAX\";s:4:\"name\";s:9:\"Avalanche\";s:4:\"logo\";s:105:\"https://coin-images.coingecko.com/coins/images/12559/large/Avalanche_Circle_RedWhite_Trans.png?1696512369\";s:9:\"price_ngn\";d:8925.38;s:10:\"change_24h\";d:3.6;}i:32;a:6:{s:2:\"id\";s:3:\"sui\";s:6:\"symbol\";s:3:\"SUI\";s:4:\"name\";s:3:\"Sui\";s:4:\"logo\";s:90:\"https://coin-images.coingecko.com/coins/images/26375/large/sui-ocean-square.png?1727791290\";s:9:\"price_ngn\";d:936.73;s:10:\"change_24h\";d:0.7;}i:33;a:6:{s:2:\"id\";s:10:\"paypal-usd\";s:6:\"symbol\";s:5:\"PYUSD\";s:4:\"name\";s:10:\"PayPal USD\";s:4:\"logo\";s:93:\"https://coin-images.coingecko.com/coins/images/31212/large/PYUSD_Token_Logo_2x.png?1765987788\";s:9:\"price_ngn\";d:1358.4;s:10:\"change_24h\";d:0;}i:34;a:6:{s:2:\"id\";s:50:\"blackrock-usd-institutional-digital-liquidity-fund\";s:6:\"symbol\";s:5:\"BUIDL\";s:4:\"name\";s:50:\"BlackRock USD Institutional Digital Liquidity Fund\";s:4:\"logo\";s:83:\"https://coin-images.coingecko.com/coins/images/36291/large/blackrock.png?1711013223\";s:9:\"price_ngn\";d:1358.81;s:10:\"change_24h\";d:0;}i:35;a:6:{s:2:\"id\";s:7:\"uniswap\";s:6:\"symbol\";s:3:\"UNI\";s:4:\"name\";s:7:\"Uniswap\";s:4:\"logo\";s:86:\"https://coin-images.coingecko.com/coins/images/12504/large/uniswap-logo.png?1720676669\";s:9:\"price_ngn\";d:5722.74;s:10:\"change_24h\";d:2.5;}i:36;a:6:{s:2:\"id\";s:16:\"crypto-com-chain\";s:6:\"symbol\";s:3:\"CRO\";s:4:\"name\";s:6:\"Cronos\";s:4:\"logo\";s:87:\"https://coin-images.coingecko.com/coins/images/7310/large/cro_token_logo.png?1696507599\";s:9:\"price_ngn\";d:74.25;s:10:\"change_24h\";d:-0.6;}i:37;a:6:{s:2:\"id\";s:11:\"tether-gold\";s:6:\"symbol\";s:4:\"XAUT\";s:4:\"name\";s:11:\"Tether Gold\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/10481/large/logo.png?1774627372\";s:9:\"price_ngn\";d:5496168;s:10:\"change_24h\";d:0.2;}i:38;a:6:{s:2:\"id\";s:4:\"near\";s:6:\"symbol\";s:4:\"NEAR\";s:4:\"name\";s:13:\"NEAR Protocol\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/10365/large/near.jpg?1696510367\";s:9:\"price_ngn\";d:2304.94;s:10:\"change_24h\";d:1.5;}i:39;a:6:{s:2:\"id\";s:20:\"ondo-us-dollar-yield\";s:6:\"symbol\";s:4:\"USDY\";s:4:\"name\";s:20:\"Ondo US Dollar Yield\";s:4:\"logo\";s:86:\"https://coin-images.coingecko.com/coins/images/31700/large/usdy_%281%29.png?1696530524\";s:9:\"price_ngn\";d:1549.36;s:10:\"change_24h\";d:0;}i:40;a:6:{s:2:\"id\";s:12:\"ondo-finance\";s:6:\"symbol\";s:4:\"ONDO\";s:4:\"name\";s:4:\"Ondo\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/26580/large/ONDO.png?1696525656\";s:9:\"price_ngn\";d:522.21;s:10:\"change_24h\";d:-1.2;}i:41;a:6:{s:2:\"id\";s:9:\"bittensor\";s:6:\"symbol\";s:3:\"TAO\";s:4:\"name\";s:9:\"Bittensor\";s:4:\"logo\";s:91:\"https://coin-images.coingecko.com/coins/images/28452/large/ARUsPeNQ_400x400.jpeg?1696527447\";s:9:\"price_ngn\";d:261141;s:10:\"change_24h\";d:-0.2;}i:42;a:6:{s:2:\"id\";s:3:\"okb\";s:6:\"symbol\";s:3:\"OKB\";s:4:\"name\";s:3:\"OKB\";s:4:\"logo\";s:100:\"https://coin-images.coingecko.com/coins/images/4463/large/WeChat_Image_20220118095654.png?1696505053\";s:9:\"price_ngn\";d:117017;s:10:\"change_24h\";d:0.2;}i:43;a:6:{s:2:\"id\";s:8:\"pax-gold\";s:6:\"symbol\";s:4:\"PAXG\";s:4:\"name\";s:8:\"PAX Gold\";s:4:\"logo\";s:83:\"https://coin-images.coingecko.com/coins/images/9519/large/asset-paxg.png?1785284785\";s:9:\"price_ngn\";d:5513778;s:10:\"change_24h\";d:0.3;}i:44;a:6:{s:2:\"id\";s:23:\"world-liberty-financial\";s:6:\"symbol\";s:4:\"WLFI\";s:4:\"name\";s:23:\"World Liberty Financial\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/50767/large/wlfi.png?1756438915\";s:9:\"price_ngn\";d:75.36;s:10:\"change_24h\";d:3.1;}i:45;a:6:{s:2:\"id\";s:7:\"aster-2\";s:6:\"symbol\";s:5:\"ASTER\";s:4:\"name\";s:5:\"Aster\";s:4:\"logo\";s:80:\"https://coin-images.coingecko.com/coins/images/69040/large/_ASTER.png?1757326782\";s:9:\"price_ngn\";d:819.01;s:10:\"change_24h\";d:0.5;}i:46;a:6:{s:2:\"id\";s:7:\"htx-dao\";s:6:\"symbol\";s:3:\"HTX\";s:4:\"name\";s:7:\"HTX DAO\";s:4:\"logo\";s:90:\"https://coin-images.coingecko.com/coins/images/35491/large/Frame_1321318576.png?1708908626\";s:9:\"price_ngn\";d:0.00243564;s:10:\"change_24h\";d:0.3;}i:47;a:6:{s:2:\"id\";s:4:\"usdd\";s:6:\"symbol\";s:4:\"USDD\";s:4:\"name\";s:4:\"USDD\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/25380/large/UUSD.jpg?1696524513\";s:9:\"price_ngn\";d:1356.1;s:10:\"change_24h\";d:-0.1;}i:48;a:6:{s:2:\"id\";s:8:\"memecore\";s:6:\"symbol\";s:1:\"M\";s:4:\"name\";s:8:\"MemeCore\";s:4:\"logo\";s:95:\"https://coin-images.coingecko.com/coins/images/53247/large/square-bg-transparent.png?1752637478\";s:9:\"price_ngn\";d:1584.12;s:10:\"change_24h\";d:8.9;}i:49;a:6:{s:2:\"id\";s:10:\"ripple-usd\";s:6:\"symbol\";s:5:\"RLUSD\";s:4:\"name\";s:10:\"Ripple USD\";s:4:\"logo\";s:95:\"https://coin-images.coingecko.com/coins/images/39651/large/RLUSD_200x200_%281%29.png?1727376633\";s:9:\"price_ngn\";d:1358.92;s:10:\"change_24h\";d:0;}i:50;a:6:{s:2:\"id\";s:4:\"aave\";s:6:\"symbol\";s:4:\"AAVE\";s:4:\"name\";s:4:\"Aave\";s:4:\"logo\";s:90:\"https://coin-images.coingecko.com/coins/images/12645/large/aave-token-round.png?1720472354\";s:9:\"price_ngn\";d:125304;s:10:\"change_24h\";d:0;}i:51;a:6:{s:2:\"id\";s:14:\"falcon-finance\";s:6:\"symbol\";s:4:\"USDF\";s:4:\"name\";s:10:\"Falcon USD\";s:4:\"logo\";s:86:\"https://coin-images.coingecko.com/coins/images/54558/large/ff_200_X_200.png?1740741076\";s:9:\"price_ngn\";d:1353.61;s:10:\"change_24h\";d:0;}i:52;a:6:{s:2:\"id\";s:8:\"polkadot\";s:6:\"symbol\";s:3:\"DOT\";s:4:\"name\";s:8:\"Polkadot\";s:4:\"logo\";s:82:\"https://coin-images.coingecko.com/coins/images/12171/large/polkadot.jpg?1766533446\";s:9:\"price_ngn\";d:1075.89;s:10:\"change_24h\";d:1.9;}i:53;a:6:{s:2:\"id\";s:5:\"bfusd\";s:6:\"symbol\";s:5:\"BFUSD\";s:4:\"name\";s:5:\"BFUSD\";s:4:\"logo\";s:79:\"https://coin-images.coingecko.com/coins/images/68227/large/bfusd.png?1755132827\";s:9:\"price_ngn\";d:1356.27;s:10:\"change_24h\";d:0;}i:54;a:6:{s:2:\"id\";s:6:\"mantle\";s:6:\"symbol\";s:3:\"MNT\";s:4:\"name\";s:6:\"Mantle\";s:4:\"logo\";s:88:\"https://coin-images.coingecko.com/coins/images/30980/large/MNT_Token_Logo.png?1765516974\";s:9:\"price_ngn\";d:538.94;s:10:\"change_24h\";d:-0.1;}i:55;a:6:{s:2:\"id\";s:3:\"sky\";s:6:\"symbol\";s:3:\"SKY\";s:4:\"name\";s:3:\"Sky\";s:4:\"logo\";s:77:\"https://coin-images.coingecko.com/coins/images/39925/large/sky.jpg?1724827980\";s:9:\"price_ngn\";d:75.75;s:10:\"change_24h\";d:-0.6;}i:56;a:6:{s:2:\"id\";s:6:\"morpho\";s:6:\"symbol\";s:6:\"MORPHO\";s:4:\"name\";s:6:\"Morpho\";s:4:\"logo\";s:91:\"https://coin-images.coingecko.com/coins/images/29837/large/Morpho-token-icon.png?1726771230\";s:9:\"price_ngn\";d:2625.98;s:10:\"change_24h\";d:-1.6;}i:57;a:6:{s:2:\"id\";s:4:\"pepe\";s:6:\"symbol\";s:4:\"PEPE\";s:4:\"name\";s:4:\"Pepe\";s:4:\"logo\";s:85:\"https://coin-images.coingecko.com/coins/images/29850/large/pepe-token.jpeg?1696528776\";s:9:\"price_ngn\";d:0.00394439;s:10:\"change_24h\";d:4.7;}i:58;a:6:{s:2:\"id\";s:17:\"internet-computer\";s:6:\"symbol\";s:3:\"ICP\";s:4:\"name\";s:17:\"Internet Computer\";s:4:\"logo\";s:96:\"https://coin-images.coingecko.com/coins/images/14495/large/Internet_Computer_logo.png?1696514180\";s:9:\"price_ngn\";d:2829.01;s:10:\"change_24h\";d:0.2;}i:59;a:6:{s:2:\"id\";s:12:\"bitget-token\";s:6:\"symbol\";s:3:\"BGB\";s:4:\"name\";s:12:\"Bitget Token\";s:4:\"logo\";s:85:\"https://coin-images.coingecko.com/coins/images/11610/large/Bitget_logo.png?1736925727\";s:9:\"price_ngn\";d:2216.62;s:10:\"change_24h\";d:0.1;}i:60;a:6:{s:2:\"id\";s:7:\"audiera\";s:6:\"symbol\";s:4:\"BEAT\";s:4:\"name\";s:7:\"Audiera\";s:4:\"logo\";s:81:\"https://coin-images.coingecko.com/coins/images/70428/large/audiera.png?1761964064\";s:9:\"price_ngn\";d:4989;s:10:\"change_24h\";d:-20.1;}i:61;a:6:{s:2:\"id\";s:13:\"worldcoin-wld\";s:6:\"symbol\";s:3:\"WLD\";s:4:\"name\";s:9:\"Worldcoin\";s:4:\"logo\";s:84:\"https://coin-images.coingecko.com/coins/images/31069/large/worldcoin.jpeg?1696529903\";s:9:\"price_ngn\";d:429.7;s:10:\"change_24h\";d:2.6;}i:62;a:6:{s:2:\"id\";s:5:\"usdgo\";s:6:\"symbol\";s:5:\"USDGO\";s:4:\"name\";s:5:\"USDGO\";s:4:\"logo\";s:91:\"https://coin-images.coingecko.com/coins/images/102172077/large/USDGO_%287%29.png?1771437018\";s:9:\"price_ngn\";d:1358.65;s:10:\"change_24h\";d:0;}i:63;a:6:{s:2:\"id\";s:14:\"united-stables\";s:6:\"symbol\";s:1:\"U\";s:4:\"name\";s:14:\"United Stables\";s:4:\"logo\";s:93:\"https://coin-images.coingecko.com/coins/images/71157/large/united-stables-logo.jpg?1766061640\";s:9:\"price_ngn\";d:1358.54;s:10:\"change_24h\";d:0;}i:64;a:6:{s:2:\"id\";s:16:\"ethereum-classic\";s:6:\"symbol\";s:3:\"ETC\";s:4:\"name\";s:16:\"Ethereum Classic\";s:4:\"logo\";s:93:\"https://coin-images.coingecko.com/coins/images/453/large/ethereum-classic-logo.png?1696501717\";s:9:\"price_ngn\";d:8975.5;s:10:\"change_24h\";d:0.6;}i:65;a:6:{s:2:\"id\";s:18:\"blockchain-capital\";s:6:\"symbol\";s:4:\"BCAP\";s:4:\"name\";s:18:\"Blockchain Capital\";s:4:\"logo\";s:87:\"https://coin-images.coingecko.com/coins/images/56040/large/bcap_logo_200.png?1748088291\";s:9:\"price_ngn\";d:144333;s:10:\"change_24h\";d:0;}i:66;a:6:{s:2:\"id\";s:36:\"spiko-amundi-overnight-swap-fund-eur\";s:6:\"symbol\";s:7:\"EURSAFO\";s:4:\"name\";s:38:\"Spiko Amundi Overnight Swap Fund (EUR)\";s:4:\"logo\";s:90:\"https://coin-images.coingecko.com/coins/images/102172591/large/Fund_eurSAF0.png?1774104814\";s:9:\"price_ngn\";d:1581.93;s:10:\"change_24h\";d:0;}i:67;a:6:{s:2:\"id\";s:10:\"pi-network\";s:6:\"symbol\";s:2:\"PI\";s:4:\"name\";s:10:\"Pi Network\";s:4:\"logo\";s:84:\"https://coin-images.coingecko.com/coins/images/54342/large/pi_network.jpg?1739347576\";s:9:\"price_ngn\";d:116.26;s:10:\"change_24h\";d:-0.8;}i:68;a:6:{s:2:\"id\";s:5:\"eutbl\";s:6:\"symbol\";s:5:\"EUTBL\";s:4:\"name\";s:34:\"Spiko EU T-Bills Money Market Fund\";s:4:\"logo\";s:79:\"https://coin-images.coingecko.com/coins/images/39657/large/EUTBL.png?1723517425\";s:9:\"price_ngn\";d:1655.11;s:10:\"change_24h\";d:0;}i:69;a:6:{s:2:\"id\";s:13:\"kucoin-shares\";s:6:\"symbol\";s:3:\"KCS\";s:4:\"name\";s:6:\"KuCoin\";s:4:\"logo\";s:79:\"https://coin-images.coingecko.com/coins/images/1047/large/sa9z79.png?1696502152\";s:9:\"price_ngn\";d:8900.88;s:10:\"change_24h\";d:-0.5;}i:70;a:6:{s:2:\"id\";s:60:\"superstate-short-duration-us-government-securities-fund-ustb\";s:6:\"symbol\";s:4:\"USTB\";s:4:\"name\";s:52:\"Invesco Short Duration US Government Securities Fund\";s:4:\"logo\";s:89:\"https://coin-images.coingecko.com/coins/images/35012/large/Invesco_icon_lg.png?1780816895\";s:9:\"price_ngn\";d:15168.22;s:10:\"change_24h\";d:0;}i:71;a:6:{s:2:\"id\";s:13:\"quant-network\";s:6:\"symbol\";s:3:\"QNT\";s:4:\"name\";s:5:\"Quant\";s:4:\"logo\";s:89:\"https://coin-images.coingecko.com/coins/images/3370/large/5ZOu7brX_400x400.jpg?1696504070\";s:9:\"price_ngn\";d:81996;s:10:\"change_24h\";d:-2.5;}i:72;a:6:{s:2:\"id\";s:36:\"janus-henderson-anemoy-treasury-fund\";s:6:\"symbol\";s:5:\"JTRSY\";s:4:\"name\";s:36:\"Janus Henderson Anemoy Treasury Fund\";s:4:\"logo\";s:79:\"https://coin-images.coingecko.com/coins/images/70445/large/JTRSY.png?1762078582\";s:9:\"price_ngn\";d:1510.13;s:10:\"change_24h\";d:0;}i:73;a:6:{s:2:\"id\";s:4:\"just\";s:6:\"symbol\";s:3:\"JST\";s:4:\"name\";s:4:\"JUST\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/11095/large/JUST.jpg?1696511035\";s:9:\"price_ngn\";d:143.6;s:10:\"change_24h\";d:2.1;}i:74;a:6:{s:2:\"id\";s:8:\"pump-fun\";s:6:\"symbol\";s:4:\"PUMP\";s:4:\"name\";s:8:\"Pump.fun\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/67164/large/pump.jpg?1751949376\";s:9:\"price_ngn\";d:2.95;s:10:\"change_24h\";d:1;}i:75;a:6:{s:2:\"id\";s:6:\"ethena\";s:6:\"symbol\";s:3:\"ENA\";s:4:\"name\";s:6:\"Ethena\";s:4:\"logo\";s:80:\"https://coin-images.coingecko.com/coins/images/36530/large/ethena.png?1711701436\";s:9:\"price_ngn\";d:120.69;s:10:\"change_24h\";d:9.2;}i:76;a:6:{s:2:\"id\";s:8:\"stable-2\";s:6:\"symbol\";s:6:\"STABLE\";s:4:\"name\";s:12:\"​​Stable\";s:4:\"logo\";s:109:\"https://coin-images.coingecko.com/coins/images/69242/large/stable-logotype-framed-square-light.png?1762753913\";s:9:\"price_ngn\";d:43.91;s:10:\"change_24h\";d:-3.8;}i:77;a:6:{s:2:\"id\";s:23:\"polygon-ecosystem-token\";s:6:\"symbol\";s:3:\"POL\";s:4:\"name\";s:14:\"POL (ex-MATIC)\";s:4:\"logo\";s:77:\"https://coin-images.coingecko.com/coins/images/32440/large/pol.png?1759114181\";s:9:\"price_ngn\";d:99.01;s:10:\"change_24h\";d:0.2;}i:78;a:6:{s:2:\"id\";s:8:\"algorand\";s:6:\"symbol\";s:4:\"ALGO\";s:4:\"name\";s:8:\"Algorand\";s:4:\"logo\";s:81:\"https://coin-images.coingecko.com/coins/images/4380/large/download.png?1696504978\";s:9:\"price_ngn\";d:115.31;s:10:\"change_24h\";d:6.2;}i:79;a:6:{s:2:\"id\";s:5:\"kaspa\";s:6:\"symbol\";s:3:\"KAS\";s:4:\"name\";s:5:\"Kaspa\";s:4:\"logo\";s:94:\"https://coin-images.coingecko.com/coins/images/25751/large/kaspa-icon-exchanges.png?1696524837\";s:9:\"price_ngn\";d:36.25;s:10:\"change_24h\";d:-1.5;}i:80;a:6:{s:2:\"id\";s:4:\"nexo\";s:6:\"symbol\";s:4:\"NEXO\";s:4:\"name\";s:4:\"NEXO\";s:4:\"logo\";s:97:\"https://coin-images.coingecko.com/coins/images/3695/large/CG-nexo-token-200x200_2x.png?1730414360\";s:9:\"price_ngn\";d:970.37;s:10:\"change_24h\";d:0.2;}i:81;a:6:{s:2:\"id\";s:12:\"render-token\";s:6:\"symbol\";s:6:\"RENDER\";s:4:\"name\";s:6:\"Render\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/11636/large/rndr.png?1696511529\";s:9:\"price_ngn\";d:1856.99;s:10:\"change_24h\";d:-0.4;}i:82;a:6:{s:2:\"id\";s:15:\"gatechain-token\";s:6:\"symbol\";s:2:\"GT\";s:4:\"name\";s:4:\"Gate\";s:4:\"logo\";s:80:\"https://coin-images.coingecko.com/coins/images/8183/large/200X200.png?1735246724\";s:9:\"price_ngn\";d:8788.74;s:10:\"change_24h\";d:-0.2;}i:83;a:6:{s:2:\"id\";s:35:\"janus-henderson-anemoy-aaa-clo-fund\";s:6:\"symbol\";s:4:\"JAAA\";s:4:\"name\";s:35:\"Janus Henderson Anemoy AAA CLO Fund\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/70446/large/jaaa.png?1762078666\";s:9:\"price_ngn\";d:1417.14;s:10:\"change_24h\";d:0;}i:84;a:6:{s:2:\"id\";s:6:\"cosmos\";s:6:\"symbol\";s:4:\"ATOM\";s:4:\"name\";s:10:\"Cosmos Hub\";s:4:\"logo\";s:83:\"https://coin-images.coingecko.com/coins/images/1481/large/cosmos_hub.png?1696502525\";s:9:\"price_ngn\";d:1705.26;s:10:\"change_24h\";d:1.3;}i:85;a:6:{s:2:\"id\";s:23:\"jupiter-exchange-solana\";s:6:\"symbol\";s:3:\"JUP\";s:4:\"name\";s:7:\"Jupiter\";s:4:\"logo\";s:77:\"https://coin-images.coingecko.com/coins/images/34188/large/jup.png?1704266489\";s:9:\"price_ngn\";d:267.61;s:10:\"change_24h\";d:3;}i:86;a:6:{s:2:\"id\";s:3:\"gho\";s:6:\"symbol\";s:3:\"GHO\";s:4:\"name\";s:3:\"GHO\";s:4:\"logo\";s:88:\"https://coin-images.coingecko.com/coins/images/30663/large/gho-token-logo.png?1720517092\";s:9:\"price_ngn\";d:1356.76;s:10:\"change_24h\";d:0;}i:87;a:6:{s:2:\"id\";s:6:\"beldex\";s:6:\"symbol\";s:3:\"BDX\";s:4:\"name\";s:6:\"Beldex\";s:4:\"logo\";s:79:\"https://coin-images.coingecko.com/coins/images/5111/large/Beldex.png?1696505631\";s:9:\"price_ngn\";d:111.56;s:10:\"change_24h\";d:-1.7;}i:88;a:6:{s:2:\"id\";s:4:\"ylds\";s:6:\"symbol\";s:4:\"YLDS\";s:4:\"name\";s:4:\"YLDS\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/66486/large/YLDS.png?1772560579\";s:9:\"price_ngn\";d:1357.79;s:10:\"change_24h\";d:-0.1;}i:89;a:6:{s:2:\"id\";s:12:\"bianrensheng\";s:6:\"symbol\";s:12:\"币安人生\";s:4:\"name\";s:26:\"币安人生 (BinanceLife)\";s:4:\"logo\";s:110:\"https://coin-images.coingecko.com/coins/images/69848/large/%E5%B8%81%E5%AE%89%E4%BA%BA%E7%94%9F.png?1759839225\";s:9:\"price_ngn\";d:807.37;s:10:\"change_24h\";d:-4;}i:90;a:6:{s:2:\"id\";s:8:\"filecoin\";s:6:\"symbol\";s:3:\"FIL\";s:4:\"name\";s:8:\"Filecoin\";s:4:\"logo\";s:82:\"https://coin-images.coingecko.com/coins/images/12817/large/filecoin.png?1696512609\";s:9:\"price_ngn\";d:980.33;s:10:\"change_24h\";d:1.3;}i:91;a:6:{s:2:\"id\";s:12:\"venice-token\";s:6:\"symbol\";s:3:\"VVV\";s:4:\"name\";s:12:\"Venice Token\";s:4:\"logo\";s:95:\"https://coin-images.coingecko.com/coins/images/54023/large/VVV_Token_Transparent.png?1741856877\";s:9:\"price_ngn\";d:15904;s:10:\"change_24h\";d:-1.2;}i:92;a:6:{s:2:\"id\";s:9:\"usual-usd\";s:6:\"symbol\";s:4:\"USD0\";s:4:\"name\";s:9:\"Usual USD\";s:4:\"logo\";s:82:\"https://coin-images.coingecko.com/coins/images/38272/large/USD0LOGO.png?1716962811\";s:9:\"price_ngn\";d:1357.68;s:10:\"change_24h\";d:0;}i:93;a:6:{s:2:\"id\";s:5:\"talus\";s:6:\"symbol\";s:2:\"US\";s:4:\"name\";s:5:\"Talus\";s:4:\"logo\";s:81:\"https://coin-images.coingecko.com/coins/images/70693/large/us-icon.png?1763129786\";s:9:\"price_ngn\";d:74.8;s:10:\"change_24h\";d:-3.1;}i:94;a:6:{s:2:\"id\";s:14:\"flare-networks\";s:6:\"symbol\";s:3:\"FLR\";s:4:\"name\";s:5:\"Flare\";s:4:\"logo\";s:89:\"https://coin-images.coingecko.com/coins/images/28624/large/FLR-icon200x200.png?1696527609\";s:9:\"price_ngn\";d:8.52;s:10:\"change_24h\";d:1.1;}i:95;a:6:{s:2:\"id\";s:8:\"arbitrum\";s:6:\"symbol\";s:3:\"ARB\";s:4:\"name\";s:8:\"Arbitrum\";s:4:\"logo\";s:77:\"https://coin-images.coingecko.com/coins/images/16547/large/arb.jpg?1721358242\";s:9:\"price_ngn\";d:109.38;s:10:\"change_24h\";d:2.9;}i:96;a:6:{s:2:\"id\";s:15:\"xdce-crowd-sale\";s:6:\"symbol\";s:3:\"XDC\";s:4:\"name\";s:11:\"XDC Network\";s:4:\"logo\";s:81:\"https://coin-images.coingecko.com/coins/images/2912/large/xdc-icon.png?1696503661\";s:9:\"price_ngn\";d:35.58;s:10:\"change_24h\";d:-1;}i:97;a:6:{s:2:\"id\";s:3:\"usx\";s:6:\"symbol\";s:3:\"USX\";s:4:\"name\";s:3:\"USX\";s:4:\"logo\";s:108:\"https://coin-images.coingecko.com/coins/images/68429/large/Solstice_Icons_for_DEX_512x512_USX.png?1755718377\";s:9:\"price_ngn\";d:1357.55;s:10:\"change_24h\";d:0;}i:98;a:6:{s:2:\"id\";s:7:\"lighter\";s:6:\"symbol\";s:3:\"LIT\";s:4:\"name\";s:7:\"Lighter\";s:4:\"logo\";s:81:\"https://coin-images.coingecko.com/coins/images/71121/large/lighter.png?1765888098\";s:9:\"price_ngn\";d:2755;s:10:\"change_24h\";d:-0.5;}i:99;a:6:{s:2:\"id\";s:18:\"injective-protocol\";s:6:\"symbol\";s:3:\"INJ\";s:4:\"name\";s:9:\"Injective\";s:4:\"logo\";s:87:\"https://coin-images.coingecko.com/coins/images/12882/large/Other_200x200.png?1738782212\";s:9:\"price_ngn\";d:6783.28;s:10:\"change_24h\";d:1.5;}}', 1785688337);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('7th-trade-hub-cache-crypto_markets_catalog_ngn:per_usd_v2', 'a:100:{i:0;a:7:{s:2:\"id\";s:7:\"bitcoin\";s:6:\"symbol\";s:3:\"BTC\";s:4:\"name\";s:7:\"Bitcoin\";s:4:\"logo\";s:77:\"https://coin-images.coingecko.com/coins/images/1/large/bitcoin.png?1696501400\";s:9:\"price_usd\";d:63256;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.4;}i:1;a:7:{s:2:\"id\";s:8:\"ethereum\";s:6:\"symbol\";s:3:\"ETH\";s:4:\"name\";s:8:\"Ethereum\";s:4:\"logo\";s:80:\"https://coin-images.coingecko.com/coins/images/279/large/ethereum.png?1696501628\";s:9:\"price_usd\";d:1866.24;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-0.4;}i:2;a:7:{s:2:\"id\";s:6:\"tether\";s:6:\"symbol\";s:4:\"USDT\";s:4:\"name\";s:6:\"Tether\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/325/large/Tether.png?1696501661\";s:9:\"price_usd\";d:0.999168;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:3;a:7:{s:2:\"id\";s:11:\"binancecoin\";s:6:\"symbol\";s:3:\"BNB\";s:4:\"name\";s:3:\"BNB\";s:4:\"logo\";s:84:\"https://coin-images.coingecko.com/coins/images/825/large/bnb-icon2_2x.png?1696501970\";s:9:\"price_usd\";d:587.15;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:1.9;}i:4;a:7:{s:2:\"id\";s:8:\"usd-coin\";s:6:\"symbol\";s:4:\"USDC\";s:4:\"name\";s:4:\"USDC\";s:4:\"logo\";s:77:\"https://coin-images.coingecko.com/coins/images/6319/large/USDC.png?1769615602\";s:9:\"price_usd\";d:0.999494;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:5;a:7:{s:2:\"id\";s:6:\"ripple\";s:6:\"symbol\";s:3:\"XRP\";s:4:\"name\";s:3:\"XRP\";s:4:\"logo\";s:91:\"https://coin-images.coingecko.com/coins/images/44/large/xrp-symbol-white-128.png?1696501442\";s:9:\"price_usd\";d:1.081;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:1.7;}i:6;a:7:{s:2:\"id\";s:6:\"solana\";s:6:\"symbol\";s:3:\"SOL\";s:4:\"name\";s:6:\"Solana\";s:4:\"logo\";s:79:\"https://coin-images.coingecko.com/coins/images/4128/large/solana.png?1718769756\";s:9:\"price_usd\";d:73.27;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.5;}i:7;a:7:{s:2:\"id\";s:4:\"tron\";s:6:\"symbol\";s:3:\"TRX\";s:4:\"name\";s:4:\"TRON\";s:4:\"logo\";s:98:\"https://coin-images.coingecko.com/coins/images/1094/large/photo_2026-04-13_09-59-16.png?1776048311\";s:9:\"price_usd\";d:0.327415;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-0.2;}i:8;a:7:{s:2:\"id\";s:12:\"figure-heloc\";s:6:\"symbol\";s:10:\"FIGR_HELOC\";s:4:\"name\";s:12:\"Figure Heloc\";s:4:\"logo\";s:80:\"https://coin-images.coingecko.com/coins/images/68480/large/figure.png?1755863954\";s:9:\"price_usd\";d:1.001;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-3.1;}i:9;a:7:{s:2:\"id\";s:8:\"whitebit\";s:6:\"symbol\";s:3:\"WBT\";s:4:\"name\";s:13:\"WhiteBIT Coin\";s:4:\"logo\";s:83:\"https://coin-images.coingecko.com/coins/images/27045/large/wbt_token.png?1696526096\";s:9:\"price_usd\";d:55.09;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:10;a:7:{s:2:\"id\";s:11:\"hyperliquid\";s:6:\"symbol\";s:4:\"HYPE\";s:4:\"name\";s:11:\"Hyperliquid\";s:4:\"logo\";s:85:\"https://coin-images.coingecko.com/coins/images/50882/large/hyperliquid.jpg?1729431300\";s:9:\"price_usd\";d:52.22;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-0.3;}i:11;a:7:{s:2:\"id\";s:8:\"dogecoin\";s:6:\"symbol\";s:4:\"DOGE\";s:4:\"name\";s:8:\"Dogecoin\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/5/large/dogecoin.png?1696501409\";s:9:\"price_usd\";d:0.070526;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.3;}i:12;a:7:{s:2:\"id\";s:4:\"usds\";s:6:\"symbol\";s:4:\"USDS\";s:4:\"name\";s:4:\"USDS\";s:4:\"logo\";s:79:\"https://coin-images.coingecko.com/coins/images/39926/large/usds.webp?1726666683\";s:9:\"price_usd\";d:0.999974;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:13;a:7:{s:2:\"id\";s:9:\"leo-token\";s:6:\"symbol\";s:3:\"LEO\";s:4:\"name\";s:9:\"LEO Token\";s:4:\"logo\";s:82:\"https://coin-images.coingecko.com/coins/images/8418/large/leo-token.png?1696508607\";s:9:\"price_usd\";d:9.76;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-0.2;}i:14;a:7:{s:2:\"id\";s:4:\"rain\";s:6:\"symbol\";s:4:\"RAIN\";s:4:\"name\";s:4:\"Rain\";s:4:\"logo\";s:86:\"https://coin-images.coingecko.com/coins/images/69134/large/Rain_logo_1_.png?1762952191\";s:9:\"price_usd\";d:0.01266862;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.5;}i:15;a:7:{s:2:\"id\";s:5:\"zcash\";s:6:\"symbol\";s:3:\"ZEC\";s:4:\"name\";s:5:\"Zcash\";s:4:\"logo\";s:90:\"https://coin-images.coingecko.com/coins/images/486/large/circle-zcash-color.png?1696501740\";s:9:\"price_usd\";d:471.36;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.2;}i:16;a:7:{s:2:\"id\";s:7:\"cardano\";s:6:\"symbol\";s:3:\"ADA\";s:4:\"name\";s:7:\"Cardano\";s:4:\"logo\";s:79:\"https://coin-images.coingecko.com/coins/images/975/large/cardano.png?1696502090\";s:9:\"price_usd\";d:0.189482;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:10.2;}i:17;a:7:{s:2:\"id\";s:6:\"monero\";s:6:\"symbol\";s:3:\"XMR\";s:4:\"name\";s:6:\"Monero\";s:4:\"logo\";s:82:\"https://coin-images.coingecko.com/coins/images/69/large/monero_logo.png?1696501460\";s:9:\"price_usd\";d:363.8;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:1.3;}i:18;a:7:{s:2:\"id\";s:9:\"chainlink\";s:6:\"symbol\";s:4:\"LINK\";s:4:\"name\";s:9:\"Chainlink\";s:4:\"logo\";s:90:\"https://coin-images.coingecko.com/coins/images/877/large/Chainlink_Logo_500.png?1760023405\";s:9:\"price_usd\";d:8.32;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:2.2;}i:19;a:7:{s:2:\"id\";s:7:\"stellar\";s:6:\"symbol\";s:3:\"XLM\";s:4:\"name\";s:7:\"Stellar\";s:4:\"logo\";s:88:\"https://coin-images.coingecko.com/coins/images/100/large/fmpFRHHQ_400x400.jpg?1735231350\";s:9:\"price_usd\";d:0.174199;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:1.6;}i:20;a:7:{s:2:\"id\";s:14:\"canton-network\";s:6:\"symbol\";s:2:\"CC\";s:4:\"name\";s:6:\"Canton\";s:4:\"logo\";s:95:\"https://coin-images.coingecko.com/coins/images/70468/large/Canton-Ticker_%281%29.png?1762826299\";s:9:\"price_usd\";d:0.116742;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-0.3;}i:21;a:7:{s:2:\"id\";s:3:\"dai\";s:6:\"symbol\";s:3:\"DAI\";s:4:\"name\";s:3:\"Dai\";s:4:\"logo\";s:82:\"https://coin-images.coingecko.com/coins/images/9956/large/Badge_Dai.png?1696509996\";s:9:\"price_usd\";d:1;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:22;a:7:{s:2:\"id\";s:12:\"bitcoin-cash\";s:6:\"symbol\";s:3:\"BCH\";s:4:\"name\";s:12:\"Bitcoin Cash\";s:4:\"logo\";s:91:\"https://coin-images.coingecko.com/coins/images/780/large/bitcoin-cash-circle.png?1696501932\";s:9:\"price_usd\";d:213.02;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:2.8;}i:23;a:7:{s:2:\"id\";s:9:\"usd1-wlfi\";s:6:\"symbol\";s:4:\"USD1\";s:4:\"name\";s:4:\"USD1\";s:4:\"logo\";s:100:\"https://coin-images.coingecko.com/coins/images/54977/large/USD1_1000x1000_transparent.png?1749297002\";s:9:\"price_usd\";d:0.999199;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:24;a:7:{s:2:\"id\";s:16:\"the-open-network\";s:6:\"symbol\";s:4:\"GRAM\";s:4:\"name\";s:20:\"Gram (prev. Toncoin)\";s:4:\"logo\";s:93:\"https://coin-images.coingecko.com/coins/images/17980/large/Gram_Circular_Badge.png?1781524778\";s:9:\"price_usd\";d:1.42;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:1.3;}i:25;a:7:{s:2:\"id\";s:11:\"ethena-usde\";s:6:\"symbol\";s:4:\"USDE\";s:4:\"name\";s:11:\"Ethena USDe\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/33613/large/usde.png?1733810059\";s:9:\"price_usd\";d:0.999523;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:26;a:7:{s:2:\"id\";s:8:\"litecoin\";s:6:\"symbol\";s:3:\"LTC\";s:4:\"name\";s:8:\"Litecoin\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/2/large/litecoin.png?1696501400\";s:9:\"price_usd\";d:44.95;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:1.7;}i:27;a:7:{s:2:\"id\";s:13:\"global-dollar\";s:6:\"symbol\";s:4:\"USDG\";s:4:\"name\";s:13:\"Global Dollar\";s:4:\"logo\";s:96:\"https://coin-images.coingecko.com/coins/images/51281/large/GDN_USDG_Token_200x200.png?1730484111\";s:9:\"price_usd\";d:0.999473;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:28;a:7:{s:2:\"id\";s:16:\"hedera-hashgraph\";s:6:\"symbol\";s:4:\"HBAR\";s:4:\"name\";s:6:\"Hedera\";s:4:\"logo\";s:77:\"https://coin-images.coingecko.com/coins/images/3688/large/hbar.png?1696504364\";s:9:\"price_usd\";d:0.070006;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.2;}i:29;a:7:{s:2:\"id\";s:13:\"hashnote-usyc\";s:6:\"symbol\";s:4:\"USYC\";s:4:\"name\";s:11:\"Circle USYC\";s:4:\"logo\";s:95:\"https://coin-images.coingecko.com/coins/images/51054/large/Hashnote_SDYC_200x200.png?1730370965\";s:9:\"price_usd\";d:1.13;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:30;a:7:{s:2:\"id\";s:9:\"shiba-inu\";s:6:\"symbol\";s:4:\"SHIB\";s:4:\"name\";s:9:\"Shiba Inu\";s:4:\"logo\";s:79:\"https://coin-images.coingecko.com/coins/images/11939/large/shiba.png?1696511800\";s:9:\"price_usd\";d:4.97E-6;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-3;}i:31;a:7:{s:2:\"id\";s:11:\"avalanche-2\";s:6:\"symbol\";s:4:\"AVAX\";s:4:\"name\";s:9:\"Avalanche\";s:4:\"logo\";s:105:\"https://coin-images.coingecko.com/coins/images/12559/large/Avalanche_Circle_RedWhite_Trans.png?1696512369\";s:9:\"price_usd\";d:6.62;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:3.3;}i:32;a:7:{s:2:\"id\";s:3:\"sui\";s:6:\"symbol\";s:3:\"SUI\";s:4:\"name\";s:3:\"Sui\";s:4:\"logo\";s:90:\"https://coin-images.coingecko.com/coins/images/26375/large/sui-ocean-square.png?1727791290\";s:9:\"price_usd\";d:0.690958;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:1.1;}i:33;a:7:{s:2:\"id\";s:10:\"paypal-usd\";s:6:\"symbol\";s:5:\"PYUSD\";s:4:\"name\";s:10:\"PayPal USD\";s:4:\"logo\";s:93:\"https://coin-images.coingecko.com/coins/images/31212/large/PYUSD_Token_Logo_2x.png?1765987788\";s:9:\"price_usd\";d:0.99959;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:34;a:7:{s:2:\"id\";s:50:\"blackrock-usd-institutional-digital-liquidity-fund\";s:6:\"symbol\";s:5:\"BUIDL\";s:4:\"name\";s:50:\"BlackRock USD Institutional Digital Liquidity Fund\";s:4:\"logo\";s:83:\"https://coin-images.coingecko.com/coins/images/36291/large/blackrock.png?1711013223\";s:9:\"price_usd\";d:1;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:35;a:7:{s:2:\"id\";s:7:\"uniswap\";s:6:\"symbol\";s:3:\"UNI\";s:4:\"name\";s:7:\"Uniswap\";s:4:\"logo\";s:86:\"https://coin-images.coingecko.com/coins/images/12504/large/uniswap-logo.png?1720676669\";s:9:\"price_usd\";d:4.19;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:2.7;}i:36;a:7:{s:2:\"id\";s:16:\"crypto-com-chain\";s:6:\"symbol\";s:3:\"CRO\";s:4:\"name\";s:6:\"Cronos\";s:4:\"logo\";s:87:\"https://coin-images.coingecko.com/coins/images/7310/large/cro_token_logo.png?1696507599\";s:9:\"price_usd\";d:0.05479;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.1;}i:37;a:7:{s:2:\"id\";s:11:\"tether-gold\";s:6:\"symbol\";s:4:\"XAUT\";s:4:\"name\";s:11:\"Tether Gold\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/10481/large/logo.png?1774627372\";s:9:\"price_usd\";d:4045.35;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.2;}i:38;a:7:{s:2:\"id\";s:4:\"near\";s:6:\"symbol\";s:4:\"NEAR\";s:4:\"name\";s:13:\"NEAR Protocol\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/10365/large/near.jpg?1696510367\";s:9:\"price_usd\";d:1.69;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:1.6;}i:39;a:7:{s:2:\"id\";s:20:\"ondo-us-dollar-yield\";s:6:\"symbol\";s:4:\"USDY\";s:4:\"name\";s:20:\"Ondo US Dollar Yield\";s:4:\"logo\";s:86:\"https://coin-images.coingecko.com/coins/images/31700/large/usdy_%281%29.png?1696530524\";s:9:\"price_usd\";d:1.14;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:40;a:7:{s:2:\"id\";s:12:\"ondo-finance\";s:6:\"symbol\";s:4:\"ONDO\";s:4:\"name\";s:4:\"Ondo\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/26580/large/ONDO.png?1696525656\";s:9:\"price_usd\";d:0.386391;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-0.6;}i:41;a:7:{s:2:\"id\";s:9:\"bittensor\";s:6:\"symbol\";s:3:\"TAO\";s:4:\"name\";s:9:\"Bittensor\";s:4:\"logo\";s:91:\"https://coin-images.coingecko.com/coins/images/28452/large/ARUsPeNQ_400x400.jpeg?1696527447\";s:9:\"price_usd\";d:192.38;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-0.1;}i:42;a:7:{s:2:\"id\";s:3:\"okb\";s:6:\"symbol\";s:3:\"OKB\";s:4:\"name\";s:3:\"OKB\";s:4:\"logo\";s:100:\"https://coin-images.coingecko.com/coins/images/4463/large/WeChat_Image_20220118095654.png?1696505053\";s:9:\"price_usd\";d:85.65;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:43;a:7:{s:2:\"id\";s:8:\"pax-gold\";s:6:\"symbol\";s:4:\"PAXG\";s:4:\"name\";s:8:\"PAX Gold\";s:4:\"logo\";s:83:\"https://coin-images.coingecko.com/coins/images/9519/large/asset-paxg.png?1785284785\";s:9:\"price_usd\";d:4059.23;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.4;}i:44;a:7:{s:2:\"id\";s:23:\"world-liberty-financial\";s:6:\"symbol\";s:4:\"WLFI\";s:4:\"name\";s:23:\"World Liberty Financial\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/50767/large/wlfi.png?1756438915\";s:9:\"price_usd\";d:0.05522;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:1.7;}i:45;a:7:{s:2:\"id\";s:7:\"aster-2\";s:6:\"symbol\";s:5:\"ASTER\";s:4:\"name\";s:5:\"Aster\";s:4:\"logo\";s:80:\"https://coin-images.coingecko.com/coins/images/69040/large/_ASTER.png?1757326782\";s:9:\"price_usd\";d:0.602446;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.5;}i:46;a:7:{s:2:\"id\";s:7:\"htx-dao\";s:6:\"symbol\";s:3:\"HTX\";s:4:\"name\";s:7:\"HTX DAO\";s:4:\"logo\";s:90:\"https://coin-images.coingecko.com/coins/images/35491/large/Frame_1321318576.png?1708908626\";s:9:\"price_usd\";d:1.79E-6;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.3;}i:47;a:7:{s:2:\"id\";s:4:\"usdd\";s:6:\"symbol\";s:4:\"USDD\";s:4:\"name\";s:4:\"USDD\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/25380/large/UUSD.jpg?1696524513\";s:9:\"price_usd\";d:0.998241;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:48;a:7:{s:2:\"id\";s:8:\"memecore\";s:6:\"symbol\";s:1:\"M\";s:4:\"name\";s:8:\"MemeCore\";s:4:\"logo\";s:95:\"https://coin-images.coingecko.com/coins/images/53247/large/square-bg-transparent.png?1752637478\";s:9:\"price_usd\";d:1.18;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:9.1;}i:49;a:7:{s:2:\"id\";s:10:\"ripple-usd\";s:6:\"symbol\";s:5:\"RLUSD\";s:4:\"name\";s:10:\"Ripple USD\";s:4:\"logo\";s:95:\"https://coin-images.coingecko.com/coins/images/39651/large/RLUSD_200x200_%281%29.png?1727376633\";s:9:\"price_usd\";d:1;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:50;a:7:{s:2:\"id\";s:4:\"aave\";s:6:\"symbol\";s:4:\"AAVE\";s:4:\"name\";s:4:\"Aave\";s:4:\"logo\";s:90:\"https://coin-images.coingecko.com/coins/images/12645/large/aave-token-round.png?1720472354\";s:9:\"price_usd\";d:92.33;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.3;}i:51;a:7:{s:2:\"id\";s:14:\"falcon-finance\";s:6:\"symbol\";s:4:\"USDF\";s:4:\"name\";s:10:\"Falcon USD\";s:4:\"logo\";s:86:\"https://coin-images.coingecko.com/coins/images/54558/large/ff_200_X_200.png?1740741076\";s:9:\"price_usd\";d:0.996125;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:52;a:7:{s:2:\"id\";s:8:\"polkadot\";s:6:\"symbol\";s:3:\"DOT\";s:4:\"name\";s:8:\"Polkadot\";s:4:\"logo\";s:82:\"https://coin-images.coingecko.com/coins/images/12171/large/polkadot.jpg?1766533446\";s:9:\"price_usd\";d:0.798778;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:2;}i:53;a:7:{s:2:\"id\";s:5:\"bfusd\";s:6:\"symbol\";s:5:\"BFUSD\";s:4:\"name\";s:5:\"BFUSD\";s:4:\"logo\";s:79:\"https://coin-images.coingecko.com/coins/images/68227/large/bfusd.png?1755132827\";s:9:\"price_usd\";d:0.998225;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:54;a:7:{s:2:\"id\";s:6:\"mantle\";s:6:\"symbol\";s:3:\"MNT\";s:4:\"name\";s:6:\"Mantle\";s:4:\"logo\";s:88:\"https://coin-images.coingecko.com/coins/images/30980/large/MNT_Token_Logo.png?1765516974\";s:9:\"price_usd\";d:0.39784;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.2;}i:55;a:7:{s:2:\"id\";s:3:\"sky\";s:6:\"symbol\";s:3:\"SKY\";s:4:\"name\";s:3:\"Sky\";s:4:\"logo\";s:77:\"https://coin-images.coingecko.com/coins/images/39925/large/sky.jpg?1724827980\";s:9:\"price_usd\";d:0.055852;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-0.4;}i:56;a:7:{s:2:\"id\";s:6:\"morpho\";s:6:\"symbol\";s:6:\"MORPHO\";s:4:\"name\";s:6:\"Morpho\";s:4:\"logo\";s:91:\"https://coin-images.coingecko.com/coins/images/29837/large/Morpho-token-icon.png?1726771230\";s:9:\"price_usd\";d:1.93;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-1.4;}i:57;a:7:{s:2:\"id\";s:4:\"pepe\";s:6:\"symbol\";s:4:\"PEPE\";s:4:\"name\";s:4:\"Pepe\";s:4:\"logo\";s:85:\"https://coin-images.coingecko.com/coins/images/29850/large/pepe-token.jpeg?1696528776\";s:9:\"price_usd\";d:2.91E-6;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:3.7;}i:58;a:7:{s:2:\"id\";s:17:\"internet-computer\";s:6:\"symbol\";s:3:\"ICP\";s:4:\"name\";s:17:\"Internet Computer\";s:4:\"logo\";s:96:\"https://coin-images.coingecko.com/coins/images/14495/large/Internet_Computer_logo.png?1696514180\";s:9:\"price_usd\";d:2.09;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.6;}i:59;a:7:{s:2:\"id\";s:12:\"bitget-token\";s:6:\"symbol\";s:3:\"BGB\";s:4:\"name\";s:12:\"Bitget Token\";s:4:\"logo\";s:85:\"https://coin-images.coingecko.com/coins/images/11610/large/Bitget_logo.png?1736925727\";s:9:\"price_usd\";d:1.63;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.2;}i:60;a:7:{s:2:\"id\";s:13:\"worldcoin-wld\";s:6:\"symbol\";s:3:\"WLD\";s:4:\"name\";s:9:\"Worldcoin\";s:4:\"logo\";s:84:\"https://coin-images.coingecko.com/coins/images/31069/large/worldcoin.jpeg?1696529903\";s:9:\"price_usd\";d:0.315027;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:3.6;}i:61;a:7:{s:2:\"id\";s:7:\"audiera\";s:6:\"symbol\";s:4:\"BEAT\";s:4:\"name\";s:7:\"Audiera\";s:4:\"logo\";s:81:\"https://coin-images.coingecko.com/coins/images/70428/large/audiera.png?1761964064\";s:9:\"price_usd\";d:3.59;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-21.5;}i:62;a:7:{s:2:\"id\";s:5:\"usdgo\";s:6:\"symbol\";s:5:\"USDGO\";s:4:\"name\";s:5:\"USDGO\";s:4:\"logo\";s:91:\"https://coin-images.coingecko.com/coins/images/102172077/large/USDGO_%287%29.png?1771437018\";s:9:\"price_usd\";d:1;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:63;a:7:{s:2:\"id\";s:14:\"united-stables\";s:6:\"symbol\";s:1:\"U\";s:4:\"name\";s:14:\"United Stables\";s:4:\"logo\";s:93:\"https://coin-images.coingecko.com/coins/images/71157/large/united-stables-logo.jpg?1766061640\";s:9:\"price_usd\";d:0.999705;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:64;a:7:{s:2:\"id\";s:16:\"ethereum-classic\";s:6:\"symbol\";s:3:\"ETC\";s:4:\"name\";s:16:\"Ethereum Classic\";s:4:\"logo\";s:93:\"https://coin-images.coingecko.com/coins/images/453/large/ethereum-classic-logo.png?1696501717\";s:9:\"price_usd\";d:6.62;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.4;}i:65;a:7:{s:2:\"id\";s:18:\"blockchain-capital\";s:6:\"symbol\";s:4:\"BCAP\";s:4:\"name\";s:18:\"Blockchain Capital\";s:4:\"logo\";s:87:\"https://coin-images.coingecko.com/coins/images/56040/large/bcap_logo_200.png?1748088291\";s:9:\"price_usd\";d:106.22;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:66;a:7:{s:2:\"id\";s:36:\"spiko-amundi-overnight-swap-fund-eur\";s:6:\"symbol\";s:7:\"EURSAFO\";s:4:\"name\";s:38:\"Spiko Amundi Overnight Swap Fund (EUR)\";s:4:\"logo\";s:90:\"https://coin-images.coingecko.com/coins/images/102172591/large/Fund_eurSAF0.png?1774104814\";s:9:\"price_usd\";d:1.16;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:67;a:7:{s:2:\"id\";s:10:\"pi-network\";s:6:\"symbol\";s:2:\"PI\";s:4:\"name\";s:10:\"Pi Network\";s:4:\"logo\";s:84:\"https://coin-images.coingecko.com/coins/images/54342/large/pi_network.jpg?1739347576\";s:9:\"price_usd\";d:0.085745;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-1;}i:68;a:7:{s:2:\"id\";s:5:\"eutbl\";s:6:\"symbol\";s:5:\"EUTBL\";s:4:\"name\";s:34:\"Spiko EU T-Bills Money Market Fund\";s:4:\"logo\";s:79:\"https://coin-images.coingecko.com/coins/images/39657/large/EUTBL.png?1723517425\";s:9:\"price_usd\";d:1.22;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:69;a:7:{s:2:\"id\";s:13:\"kucoin-shares\";s:6:\"symbol\";s:3:\"KCS\";s:4:\"name\";s:6:\"KuCoin\";s:4:\"logo\";s:79:\"https://coin-images.coingecko.com/coins/images/1047/large/sa9z79.png?1696502152\";s:9:\"price_usd\";d:6.59;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.1;}i:70;a:7:{s:2:\"id\";s:60:\"superstate-short-duration-us-government-securities-fund-ustb\";s:6:\"symbol\";s:4:\"USTB\";s:4:\"name\";s:52:\"Invesco Short Duration US Government Securities Fund\";s:4:\"logo\";s:89:\"https://coin-images.coingecko.com/coins/images/35012/large/Invesco_icon_lg.png?1780816895\";s:9:\"price_usd\";d:11.16;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:71;a:7:{s:2:\"id\";s:13:\"quant-network\";s:6:\"symbol\";s:3:\"QNT\";s:4:\"name\";s:5:\"Quant\";s:4:\"logo\";s:89:\"https://coin-images.coingecko.com/coins/images/3370/large/5ZOu7brX_400x400.jpg?1696504070\";s:9:\"price_usd\";d:60.29;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-2.3;}i:72;a:7:{s:2:\"id\";s:36:\"janus-henderson-anemoy-treasury-fund\";s:6:\"symbol\";s:5:\"JTRSY\";s:4:\"name\";s:36:\"Janus Henderson Anemoy Treasury Fund\";s:4:\"logo\";s:79:\"https://coin-images.coingecko.com/coins/images/70445/large/JTRSY.png?1762078582\";s:9:\"price_usd\";d:1.11;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:73;a:7:{s:2:\"id\";s:4:\"just\";s:6:\"symbol\";s:3:\"JST\";s:4:\"name\";s:4:\"JUST\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/11095/large/JUST.jpg?1696511035\";s:9:\"price_usd\";d:0.105542;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:1.9;}i:74;a:7:{s:2:\"id\";s:6:\"ethena\";s:6:\"symbol\";s:3:\"ENA\";s:4:\"name\";s:6:\"Ethena\";s:4:\"logo\";s:80:\"https://coin-images.coingecko.com/coins/images/36530/large/ethena.png?1711701436\";s:9:\"price_usd\";d:0.089541;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:10.3;}i:75;a:7:{s:2:\"id\";s:8:\"pump-fun\";s:6:\"symbol\";s:4:\"PUMP\";s:4:\"name\";s:8:\"Pump.fun\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/67164/large/pump.jpg?1751949376\";s:9:\"price_usd\";d:0.00215453;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:1.8;}i:76;a:7:{s:2:\"id\";s:8:\"stable-2\";s:6:\"symbol\";s:6:\"STABLE\";s:4:\"name\";s:12:\"​​Stable\";s:4:\"logo\";s:109:\"https://coin-images.coingecko.com/coins/images/69242/large/stable-logotype-framed-square-light.png?1762753913\";s:9:\"price_usd\";d:0.03181955;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-6.7;}i:77;a:7:{s:2:\"id\";s:23:\"polygon-ecosystem-token\";s:6:\"symbol\";s:3:\"POL\";s:4:\"name\";s:14:\"POL (ex-MATIC)\";s:4:\"logo\";s:77:\"https://coin-images.coingecko.com/coins/images/32440/large/pol.png?1759114181\";s:9:\"price_usd\";d:0.073054;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.1;}i:78;a:7:{s:2:\"id\";s:8:\"algorand\";s:6:\"symbol\";s:4:\"ALGO\";s:4:\"name\";s:8:\"Algorand\";s:4:\"logo\";s:81:\"https://coin-images.coingecko.com/coins/images/4380/large/download.png?1696504978\";s:9:\"price_usd\";d:0.085276;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:7.7;}i:79;a:7:{s:2:\"id\";s:5:\"kaspa\";s:6:\"symbol\";s:3:\"KAS\";s:4:\"name\";s:5:\"Kaspa\";s:4:\"logo\";s:94:\"https://coin-images.coingecko.com/coins/images/25751/large/kaspa-icon-exchanges.png?1696524837\";s:9:\"price_usd\";d:0.02685818;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-1;}i:80;a:7:{s:2:\"id\";s:4:\"nexo\";s:6:\"symbol\";s:4:\"NEXO\";s:4:\"name\";s:4:\"NEXO\";s:4:\"logo\";s:97:\"https://coin-images.coingecko.com/coins/images/3695/large/CG-nexo-token-200x200_2x.png?1730414360\";s:9:\"price_usd\";d:0.717493;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-0.2;}i:81;a:7:{s:2:\"id\";s:12:\"render-token\";s:6:\"symbol\";s:6:\"RENDER\";s:4:\"name\";s:6:\"Render\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/11636/large/rndr.png?1696511529\";s:9:\"price_usd\";d:1.37;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.2;}i:82;a:7:{s:2:\"id\";s:15:\"gatechain-token\";s:6:\"symbol\";s:2:\"GT\";s:4:\"name\";s:4:\"Gate\";s:4:\"logo\";s:80:\"https://coin-images.coingecko.com/coins/images/8183/large/200X200.png?1735246724\";s:9:\"price_usd\";d:6.46;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-0.5;}i:83;a:7:{s:2:\"id\";s:35:\"janus-henderson-anemoy-aaa-clo-fund\";s:6:\"symbol\";s:4:\"JAAA\";s:4:\"name\";s:35:\"Janus Henderson Anemoy AAA CLO Fund\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/70446/large/jaaa.png?1762078666\";s:9:\"price_usd\";d:1.043;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:84;a:7:{s:2:\"id\";s:6:\"cosmos\";s:6:\"symbol\";s:4:\"ATOM\";s:4:\"name\";s:10:\"Cosmos Hub\";s:4:\"logo\";s:83:\"https://coin-images.coingecko.com/coins/images/1481/large/cosmos_hub.png?1696502525\";s:9:\"price_usd\";d:1.26;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:1.5;}i:85;a:7:{s:2:\"id\";s:23:\"jupiter-exchange-solana\";s:6:\"symbol\";s:3:\"JUP\";s:4:\"name\";s:7:\"Jupiter\";s:4:\"logo\";s:77:\"https://coin-images.coingecko.com/coins/images/34188/large/jup.png?1704266489\";s:9:\"price_usd\";d:0.196494;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:3.2;}i:86;a:7:{s:2:\"id\";s:3:\"gho\";s:6:\"symbol\";s:3:\"GHO\";s:4:\"name\";s:3:\"GHO\";s:4:\"logo\";s:88:\"https://coin-images.coingecko.com/coins/images/30663/large/gho-token-logo.png?1720517092\";s:9:\"price_usd\";d:0.998355;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:87;a:7:{s:2:\"id\";s:6:\"beldex\";s:6:\"symbol\";s:3:\"BDX\";s:4:\"name\";s:6:\"Beldex\";s:4:\"logo\";s:79:\"https://coin-images.coingecko.com/coins/images/5111/large/Beldex.png?1696505631\";s:9:\"price_usd\";d:0.082139;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-1.7;}i:88;a:7:{s:2:\"id\";s:4:\"ylds\";s:6:\"symbol\";s:4:\"YLDS\";s:4:\"name\";s:4:\"YLDS\";s:4:\"logo\";s:78:\"https://coin-images.coingecko.com/coins/images/66486/large/YLDS.png?1772560579\";s:9:\"price_usd\";d:0.999232;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-0.1;}i:89;a:7:{s:2:\"id\";s:12:\"bianrensheng\";s:6:\"symbol\";s:12:\"币安人生\";s:4:\"name\";s:26:\"币安人生 (BinanceLife)\";s:4:\"logo\";s:110:\"https://coin-images.coingecko.com/coins/images/69848/large/%E5%B8%81%E5%AE%89%E4%BA%BA%E7%94%9F.png?1759839225\";s:9:\"price_usd\";d:0.591601;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-4.7;}i:90;a:7:{s:2:\"id\";s:8:\"filecoin\";s:6:\"symbol\";s:3:\"FIL\";s:4:\"name\";s:8:\"Filecoin\";s:4:\"logo\";s:82:\"https://coin-images.coingecko.com/coins/images/12817/large/filecoin.png?1696512609\";s:9:\"price_usd\";d:0.719541;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:1.1;}i:91;a:7:{s:2:\"id\";s:12:\"venice-token\";s:6:\"symbol\";s:3:\"VVV\";s:4:\"name\";s:12:\"Venice Token\";s:4:\"logo\";s:95:\"https://coin-images.coingecko.com/coins/images/54023/large/VVV_Token_Transparent.png?1741856877\";s:9:\"price_usd\";d:11.7;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-1;}i:92;a:7:{s:2:\"id\";s:9:\"usual-usd\";s:6:\"symbol\";s:4:\"USD0\";s:4:\"name\";s:9:\"Usual USD\";s:4:\"logo\";s:82:\"https://coin-images.coingecko.com/coins/images/38272/large/USD0LOGO.png?1716962811\";s:9:\"price_usd\";d:0.999144;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:93;a:7:{s:2:\"id\";s:5:\"talus\";s:6:\"symbol\";s:2:\"US\";s:4:\"name\";s:5:\"Talus\";s:4:\"logo\";s:81:\"https://coin-images.coingecko.com/coins/images/70693/large/us-icon.png?1763129786\";s:9:\"price_usd\";d:0.054869;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-1;}i:94;a:7:{s:2:\"id\";s:14:\"flare-networks\";s:6:\"symbol\";s:3:\"FLR\";s:4:\"name\";s:5:\"Flare\";s:4:\"logo\";s:89:\"https://coin-images.coingecko.com/coins/images/28624/large/FLR-icon200x200.png?1696527609\";s:9:\"price_usd\";d:0.0062769;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:1.4;}i:95;a:7:{s:2:\"id\";s:8:\"arbitrum\";s:6:\"symbol\";s:3:\"ARB\";s:4:\"name\";s:8:\"Arbitrum\";s:4:\"logo\";s:77:\"https://coin-images.coingecko.com/coins/images/16547/large/arb.jpg?1721358242\";s:9:\"price_usd\";d:0.080845;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:3;}i:96;a:7:{s:2:\"id\";s:15:\"xdce-crowd-sale\";s:6:\"symbol\";s:3:\"XDC\";s:4:\"name\";s:11:\"XDC Network\";s:4:\"logo\";s:81:\"https://coin-images.coingecko.com/coins/images/2912/large/xdc-icon.png?1696503661\";s:9:\"price_usd\";d:0.02617231;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:-1;}i:97;a:7:{s:2:\"id\";s:3:\"usx\";s:6:\"symbol\";s:3:\"USX\";s:4:\"name\";s:3:\"USX\";s:4:\"logo\";s:108:\"https://coin-images.coingecko.com/coins/images/68429/large/Solstice_Icons_for_DEX_512x512_USX.png?1755718377\";s:9:\"price_usd\";d:0.998962;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0;}i:98;a:7:{s:2:\"id\";s:7:\"lighter\";s:6:\"symbol\";s:3:\"LIT\";s:4:\"name\";s:7:\"Lighter\";s:4:\"logo\";s:81:\"https://coin-images.coingecko.com/coins/images/71121/large/lighter.png?1765888098\";s:9:\"price_usd\";d:2.03;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:0.6;}i:99;a:7:{s:2:\"id\";s:18:\"injective-protocol\";s:6:\"symbol\";s:3:\"INJ\";s:4:\"name\";s:9:\"Injective\";s:4:\"logo\";s:87:\"https://coin-images.coingecko.com/coins/images/12882/large/Other_200x200.png?1738782212\";s:9:\"price_usd\";d:5;s:9:\"price_ngn\";d:1357.72;s:10:\"change_24h\";d:1.6;}}', 1785692958),
('7th-trade-hub-cache-crypto_prices_ngn', 'a:5:{s:8:\"ethereum\";a:2:{s:3:\"ngn\";i:2541441;s:14:\"ngn_24h_change\";d:-0.2975602884708599;}s:6:\"solana\";a:2:{s:3:\"ngn\";i:99604;s:14:\"ngn_24h_change\";d:0.13984152933697155;}s:7:\"bitcoin\";a:2:{s:3:\"ngn\";i:85952756;s:14:\"ngn_24h_change\";d:-0.08906349221123054;}s:6:\"tether\";a:2:{s:3:\"ngn\";d:1357.71;s:14:\"ngn_24h_change\";d:-0.40001642480323407;}s:11:\"binancecoin\";a:2:{s:3:\"ngn\";i:792701;s:14:\"ngn_24h_change\";d:-0.6176242314915832;}}', 1785663402),
('7th-trade-hub-cache-crypto_prices_ngn:892ffa9236180ddd2298d333891563fc', 'a:6:{s:8:\"ethereum\";a:2:{s:3:\"ngn\";i:2524204;s:14:\"ngn_24h_change\";d:-0.9362392391997664;}s:6:\"solana\";a:2:{s:3:\"ngn\";i:99132;s:14:\"ngn_24h_change\";d:-0.21214930251309572;}s:7:\"bitcoin\";a:2:{s:3:\"ngn\";i:85694628;s:14:\"ngn_24h_change\";d:-0.12609328311344298;}s:6:\"ripple\";a:2:{s:3:\"ngn\";d:1464.43;s:14:\"ngn_24h_change\";d:1.0308693699942233;}s:6:\"tether\";a:2:{s:3:\"ngn\";d:1357.68;s:14:\"ngn_24h_change\";d:-0.4118649939226513;}s:11:\"binancecoin\";a:2:{s:3:\"ngn\";i:796859;s:14:\"ngn_24h_change\";d:1.1875442372688438;}}', 1785687931),
('7th-trade-hub-cache-crypto_prices_ngn:a9dd9c93db2908b1ba8de807c20141a4', 'a:7:{s:8:\"ethereum\";a:2:{s:3:\"ngn\";i:2523159;s:14:\"ngn_24h_change\";d:-1.022865901062417;}s:6:\"solana\";a:2:{s:3:\"ngn\";i:99139;s:14:\"ngn_24h_change\";d:-0.07518537820078988;}s:7:\"bitcoin\";a:2:{s:3:\"ngn\";i:85677535;s:14:\"ngn_24h_change\";d:-0.214000508416877;}s:6:\"ripple\";a:2:{s:3:\"ngn\";d:1465.92;s:14:\"ngn_24h_change\";d:1.1732032096801022;}s:8:\"usd-coin\";a:2:{s:3:\"ngn\";d:1358.11;s:14:\"ngn_24h_change\";d:-0.4241543374180867;}s:6:\"tether\";a:2:{s:3:\"ngn\";d:1357.69;s:14:\"ngn_24h_change\";d:-0.40886656455112375;}s:11:\"binancecoin\";a:2:{s:3:\"ngn\";i:795780;s:14:\"ngn_24h_change\";d:1.2566153799997417;}}', 1785686580),
('7th-trade-hub-cache-crypto_prices_ngn:c55424708f54f64fdcfb68c037212485', 'a:5:{s:8:\"ethereum\";a:2:{s:3:\"ngn\";i:2518676;s:14:\"ngn_24h_change\";d:-1.1255204147917182;}s:6:\"solana\";a:2:{s:3:\"ngn\";i:99029;s:14:\"ngn_24h_change\";d:-0.43816114863570993;}s:7:\"bitcoin\";a:2:{s:3:\"ngn\";i:85640108;s:14:\"ngn_24h_change\";d:-0.4226374750186813;}s:6:\"tether\";a:2:{s:3:\"ngn\";d:1357.73;s:14:\"ngn_24h_change\";d:-0.40475456850052544;}s:11:\"binancecoin\";a:2:{s:3:\"ngn\";i:789711;s:14:\"ngn_24h_change\";d:0.1441065132422834;}}', 1785676851),
('7th-trade-hub-cache-crypto_prices_ngn:f545706b02e585b5d0c81e7f30245b96', 'a:6:{s:8:\"ethereum\";a:2:{s:3:\"ngn\";i:2518215;s:14:\"ngn_24h_change\";d:-1.175240260697053;}s:6:\"solana\";a:2:{s:3:\"ngn\";i:99042;s:14:\"ngn_24h_change\";d:-0.44415943790054313;}s:7:\"bitcoin\";a:2:{s:3:\"ngn\";i:85686670;s:14:\"ngn_24h_change\";d:-0.3348695139529017;}s:8:\"usd-coin\";a:2:{s:3:\"ngn\";d:1358.1;s:14:\"ngn_24h_change\";d:-0.41725523344721926;}s:6:\"tether\";a:2:{s:3:\"ngn\";d:1357.66;s:14:\"ngn_24h_change\";d:-0.40788195476327116;}s:11:\"binancecoin\";a:2:{s:3:\"ngn\";i:791365;s:14:\"ngn_24h_change\";d:0.22974783284000688;}}', 1785679409),
('7th-trade-hub-cache-crypto_prices_ngn:usd_ngn:892ffa9236180ddd2298d333891563fc', 'a:6:{s:11:\"binancecoin\";a:4:{s:3:\"usd\";d:712.41;s:14:\"usd_24h_change\";d:1.1244142860754183;s:3:\"ngn\";i:957246;s:14:\"ngn_24h_change\";d:1.0431995210400529;}s:7:\"bitcoin\";a:4:{s:3:\"usd\";i:80300;s:14:\"usd_24h_change\";d:2.0615946933704334;s:3:\"ngn\";i:107898083;s:14:\"ngn_24h_change\";d:1.9796272625509308;}s:8:\"ethereum\";a:4:{s:3:\"usd\";d:2515.24;s:14:\"usd_24h_change\";d:0.9432383065844742;s:3:\"ngn\";i:3379671;s:14:\"ngn_24h_change\";d:0.8621690471098404;}s:6:\"ripple\";a:4:{s:3:\"usd\";d:1.45;s:14:\"usd_24h_change\";d:3.6168930021686676;s:3:\"ngn\";d:1952.23;s:14:\"ngn_24h_change\";d:3.533676484394249;}s:6:\"solana\";a:4:{s:3:\"usd\";d:109.15;s:14:\"usd_24h_change\";d:9.425375086242326;s:3:\"ngn\";i:146661;s:14:\"ngn_24h_change\";d:9.33749367610732;}s:6:\"tether\";a:4:{s:3:\"usd\";d:1;s:14:\"usd_24h_change\";d:0.011465097431539261;s:3:\"ngn\";d:1343.83;s:14:\"ngn_24h_change\";d:-0.06885583887323693;}}', 1787871759),
('7th-trade-hub-cache-crypto_prices_ngn:usd_ngn:a9dd9c93db2908b1ba8de807c20141a4', 'a:7:{s:11:\"binancecoin\";a:4:{s:3:\"usd\";d:691;s:14:\"usd_24h_change\";d:-1.6999821886157298;s:3:\"ngn\";i:927030;s:14:\"ngn_24h_change\";d:-1.5635073781111637;}s:7:\"bitcoin\";a:4:{s:3:\"usd\";i:77923;s:14:\"usd_24h_change\";d:-1.8816567958819919;s:3:\"ngn\";i:104539348;s:14:\"ngn_24h_change\";d:-1.745434213282903;}s:8:\"ethereum\";a:4:{s:3:\"usd\";d:2443.83;s:14:\"usd_24h_change\";d:-2.4945800301800927;s:3:\"ngn\";i:3278590;s:14:\"ngn_24h_change\";d:-2.3592083994334745;}s:6:\"ripple\";a:4:{s:3:\"usd\";d:1.4;s:14:\"usd_24h_change\";d:-1.9367147846962747;s:3:\"ngn\";d:1872.07;s:14:\"ngn_24h_change\";d:-1.8005686418451992;}s:6:\"solana\";a:4:{s:3:\"usd\";d:105.11;s:14:\"usd_24h_change\";d:-0.38489968065654906;s:3:\"ngn\";i:141019;s:14:\"ngn_24h_change\";d:-0.24659907560925157;}s:6:\"tether\";a:4:{s:3:\"usd\";d:0.999998;s:14:\"usd_24h_change\";d:-0.010234625144409752;s:3:\"ngn\";d:1341.58;s:14:\"ngn_24h_change\";d:0.12858614605943308;}s:8:\"usd-coin\";a:4:{s:3:\"usd\";d:0.999962;s:14:\"usd_24h_change\";d:-0.003907897535505694;s:3:\"ngn\";d:1341.53;s:14:\"ngn_24h_change\";d:0.1349216573793815;}}', 1788016401),
('7th-trade-hub-cache-crypto_usd_ngn_fx', 'd:1343.84;', 1787871759),
('7th-trade-hub-cache-d1f57dd6b2f0f19d80646b2bffa8a4266b427be4', 'i:1;', 1785661268),
('7th-trade-hub-cache-d1f57dd6b2f0f19d80646b2bffa8a4266b427be4:timer', 'i:1785661268;', 1785661268),
('7th-trade-hub-cache-e118e82b37a9fa886979fcedc0797d9e950241d3', 'i:1;', 1784667010),
('7th-trade-hub-cache-e118e82b37a9fa886979fcedc0797d9e950241d3:timer', 'i:1784667010;', 1784667010),
('7th-trade-hub-cache-e2b94bb3c5627ae1dc70ff3973349236a69aed4e', 'i:1;', 1785708706),
('7th-trade-hub-cache-e2b94bb3c5627ae1dc70ff3973349236a69aed4e:timer', 'i:1785708706;', 1785708706),
('7th-trade-hub-cache-f1abd670358e036c31296e66b3b66c382ac00812', 'i:2;', 1784773714),
('7th-trade-hub-cache-f1abd670358e036c31296e66b3b66c382ac00812:timer', 'i:1784773714;', 1784773714),
('7th-trade-hub-cache-f222bab157b1258b05f88eeb45943d4c6397e92e', 'i:1;', 1784744065),
('7th-trade-hub-cache-f222bab157b1258b05f88eeb45943d4c6397e92e:timer', 'i:1784744065;', 1784744065),
('7th-trade-hub-cache-monitoring.ping', 's:2:\"ok\";', 1788015346),
('7th-trade-hub-cache-otc_coin_usd:BNB', 'd:712.7;', 1787871760),
('7th-trade-hub-cache-otc_coin_usd:BTC', 'd:80297.2;', 1787871759),
('7th-trade-hub-cache-otc_coin_usd:ETH', 'd:2516.65;', 1787871760),
('7th-trade-hub-cache-otc_coin_usd:SOL', 'd:109.2;', 1787871760),
('7th-trade-hub-cache-otc_coin_usd:USDT', 'd:1;', 1787871760),
('7th-trade-hub-cache-otc_coin_usd:XRP', 'd:1.45;', 1787871760),
('7th-trade-hub-cache-platform.contact', 'a:21:{s:10:\"email_info\";s:24:\"info@7th-tradehub.online\";s:13:\"email_support\";s:27:\"support@7th-tradehub.online\";s:11:\"email_sales\";s:25:\"sales@7th-tradehub.online\";s:13:\"phone_support\";s:11:\"09122083549\";s:13:\"phone_general\";s:11:\"09122083549\";s:14:\"phone_whatsapp\";s:11:\"09122083549\";s:14:\"address_street\";s:34:\"177 Ago Palace Way,, Lagos , Lagos\";s:12:\"address_city\";s:12:\"Oshodi Isolo\";s:13:\"address_state\";s:5:\"Lagos\";s:15:\"address_country\";s:7:\"Nigeria\";s:14:\"address_postal\";s:6:\"110224\";s:8:\"latitude\";s:0:\"\";s:9:\"longitude\";s:0:\"\";s:8:\"maps_url\";s:0:\"\";s:14:\"maps_embed_url\";s:0:\"\";s:13:\"support_hours\";s:0:\"\";s:8:\"timezone\";s:12:\"Africa/Lagos\";s:14:\"business_hours\";s:4:\"24/4\";s:19:\"registration_number\";s:0:\"\";s:10:\"vat_number\";s:0:\"\";s:14:\"company_number\";s:0:\"\";}', 1788018822),
('7th-trade-hub-cache-platform.site_branding', 'a:8:{s:9:\"site_name\";s:13:\"7th Trade Hub\";s:15:\"site_short_name\";s:9:\"Trade Hub\";s:7:\"heading\";s:40:\"The Ultimate Digital Service Marketplace\";s:7:\"tagline\";s:39:\"Connecting markets, empowering traders.\";s:16:\"meta_description\";s:89:\"Nigerian Digital marketplace to buy, sell and subscribe to digital products and services.\";s:16:\"favicon_media_id\";i:7;s:19:\"logo_light_media_id\";i:20;s:18:\"logo_dark_media_id\";i:19;}', 1788019797),
('7th-trade-hub-cache-platform.social_links', 'O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:1:{i:0;O:21:\"App\\Models\\SocialLink\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:12:\"social_links\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:9:{s:2:\"id\";i:1;s:8:\"platform\";s:6:\"Tiktok\";s:3:\"url\";s:37:\"https://www.tiktok.com/@7th.trade.hub\";s:4:\"icon\";s:6:\"Tiktok\";s:13:\"icon_media_id\";N;s:7:\"enabled\";i:1;s:10:\"sort_order\";i:1;s:10:\"created_at\";s:19:\"2026-08-01 03:12:58\";s:10:\"updated_at\";s:19:\"2026-08-01 03:12:58\";}s:11:\"\0*\0original\";a:9:{s:2:\"id\";i:1;s:8:\"platform\";s:6:\"Tiktok\";s:3:\"url\";s:37:\"https://www.tiktok.com/@7th.trade.hub\";s:4:\"icon\";s:6:\"Tiktok\";s:13:\"icon_media_id\";N;s:7:\"enabled\";i:1;s:10:\"sort_order\";i:1;s:10:\"created_at\";s:19:\"2026-08-01 03:12:58\";s:10:\"updated_at\";s:19:\"2026-08-01 03:12:58\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:7:\"enabled\";s:7:\"boolean\";s:10:\"sort_order\";s:7:\"integer\";s:13:\"icon_media_id\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:6:{i:0;s:8:\"platform\";i:1;s:3:\"url\";i:2;s:4:\"icon\";i:3;s:13:\"icon_media_id\";i:4;s:7:\"enabled\";i:5;s:10:\"sort_order\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}', 1788018822);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('7th-trade-hub-cache-sitemap.xml.v2', 'a:115:{i:0;a:3:{s:3:\"loc\";s:27:\"https://7th-tradehub.online\";s:8:\"priority\";s:3:\"1.0\";s:10:\"changefreq\";s:5:\"daily\";}i:1;a:3:{s:3:\"loc\";s:39:\"https://7th-tradehub.online/marketplace\";s:8:\"priority\";s:3:\"0.9\";s:10:\"changefreq\";s:5:\"daily\";}i:2;a:3:{s:3:\"loc\";s:33:\"https://7th-tradehub.online/about\";s:8:\"priority\";s:3:\"0.5\";s:10:\"changefreq\";s:7:\"monthly\";}i:3;a:3:{s:3:\"loc\";s:32:\"https://7th-tradehub.online/help\";s:8:\"priority\";s:3:\"0.5\";s:10:\"changefreq\";s:7:\"monthly\";}i:4;a:3:{s:3:\"loc\";s:35:\"https://7th-tradehub.online/contact\";s:8:\"priority\";s:3:\"0.5\";s:10:\"changefreq\";s:7:\"monthly\";}i:5;a:3:{s:3:\"loc\";s:36:\"https://7th-tradehub.online/services\";s:8:\"priority\";s:3:\"0.8\";s:10:\"changefreq\";s:6:\"weekly\";}i:6;a:3:{s:3:\"loc\";s:37:\"https://7th-tradehub.online/templates\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";}i:7;a:3:{s:3:\"loc\";s:44:\"https://7th-tradehub.online/website-listings\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";}i:8;a:3:{s:3:\"loc\";s:36:\"https://7th-tradehub.online/exchange\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";}i:9;a:3:{s:3:\"loc\";s:33:\"https://7th-tradehub.online/legal\";s:8:\"priority\";s:3:\"0.3\";s:10:\"changefreq\";s:6:\"yearly\";}i:10;a:3:{s:3:\"loc\";s:43:\"https://7th-tradehub.online/legal?doc=terms\";s:8:\"priority\";s:3:\"0.3\";s:10:\"changefreq\";s:6:\"yearly\";}i:11;a:3:{s:3:\"loc\";s:45:\"https://7th-tradehub.online/legal?doc=privacy\";s:8:\"priority\";s:3:\"0.3\";s:10:\"changefreq\";s:6:\"yearly\";}i:12;a:3:{s:3:\"loc\";s:57:\"https://7th-tradehub.online/help/billing-wallets-payments\";s:8:\"priority\";s:4:\"0.45\";s:10:\"changefreq\";s:7:\"monthly\";}i:13;a:3:{s:3:\"loc\";s:61:\"https://7th-tradehub.online/help/browsing-purchasing-services\";s:8:\"priority\";s:4:\"0.45\";s:10:\"changefreq\";s:7:\"monthly\";}i:14;a:3:{s:3:\"loc\";s:59:\"https://7th-tradehub.online/help/buying-selling-marketplace\";s:8:\"priority\";s:4:\"0.45\";s:10:\"changefreq\";s:7:\"monthly\";}i:15;a:3:{s:3:\"loc\";s:48:\"https://7th-tradehub.online/help/getting-started\";s:8:\"priority\";s:4:\"0.45\";s:10:\"changefreq\";s:7:\"monthly\";}i:16;a:3:{s:3:\"loc\";s:55:\"https://7th-tradehub.online/help/keeping-account-secure\";s:8:\"priority\";s:4:\"0.45\";s:10:\"changefreq\";s:7:\"monthly\";}i:17;a:3:{s:3:\"loc\";s:55:\"https://7th-tradehub.online/help/selling-cryptocurrency\";s:8:\"priority\";s:4:\"0.45\";s:10:\"changefreq\";s:7:\"monthly\";}i:18;a:3:{s:3:\"loc\";s:53:\"https://7th-tradehub.online/services/network-services\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";}i:19;a:3:{s:3:\"loc\";s:50:\"https://7th-tradehub.online/services/communication\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";}i:20;a:3:{s:3:\"loc\";s:49:\"https://7th-tradehub.online/services/social-media\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";}i:21;a:3:{s:3:\"loc\";s:53:\"https://7th-tradehub.online/services/website-services\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";}i:22;a:3:{s:3:\"loc\";s:55:\"https://7th-tradehub.online/services/business-documents\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";}i:23;a:3:{s:3:\"loc\";s:49:\"https://7th-tradehub.online/services/trust-escrow\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";}i:24;a:3:{s:3:\"loc\";s:53:\"https://7th-tradehub.online/services/website_template\";s:8:\"priority\";s:4:\"0.65\";s:10:\"changefreq\";s:6:\"weekly\";}i:25;a:3:{s:3:\"loc\";s:52:\"https://7th-tradehub.online/services/website_package\";s:8:\"priority\";s:4:\"0.65\";s:10:\"changefreq\";s:6:\"weekly\";}i:26;a:3:{s:3:\"loc\";s:54:\"https://7th-tradehub.online/services/document_template\";s:8:\"priority\";s:4:\"0.65\";s:10:\"changefreq\";s:6:\"weekly\";}i:27;a:3:{s:3:\"loc\";s:50:\"https://7th-tradehub.online/services/virtual_phone\";s:8:\"priority\";s:4:\"0.65\";s:10:\"changefreq\";s:6:\"weekly\";}i:28;a:3:{s:3:\"loc\";s:40:\"https://7th-tradehub.online/services/vpn\";s:8:\"priority\";s:4:\"0.65\";s:10:\"changefreq\";s:6:\"weekly\";}i:29;a:3:{s:3:\"loc\";s:40:\"https://7th-tradehub.online/services/vps\";s:8:\"priority\";s:4:\"0.65\";s:10:\"changefreq\";s:6:\"weekly\";}i:30;a:3:{s:3:\"loc\";s:42:\"https://7th-tradehub.online/services/proxy\";s:8:\"priority\";s:4:\"0.65\";s:10:\"changefreq\";s:6:\"weekly\";}i:31;a:3:{s:3:\"loc\";s:41:\"https://7th-tradehub.online/services/smtp\";s:8:\"priority\";s:4:\"0.65\";s:10:\"changefreq\";s:6:\"weekly\";}i:32;a:3:{s:3:\"loc\";s:43:\"https://7th-tradehub.online/services/domain\";s:8:\"priority\";s:4:\"0.65\";s:10:\"changefreq\";s:6:\"weekly\";}i:33;a:3:{s:3:\"loc\";s:42:\"https://7th-tradehub.online/services/email\";s:8:\"priority\";s:4:\"0.65\";s:10:\"changefreq\";s:6:\"weekly\";}i:34;a:3:{s:3:\"loc\";s:51:\"https://7th-tradehub.online/services/social_service\";s:8:\"priority\";s:4:\"0.65\";s:10:\"changefreq\";s:6:\"weekly\";}i:35;a:3:{s:3:\"loc\";s:51:\"https://7th-tradehub.online/services/escrow_service\";s:8:\"priority\";s:4:\"0.65\";s:10:\"changefreq\";s:6:\"weekly\";}i:36;a:4:{s:3:\"loc\";s:55:\"https://7th-tradehub.online/marketplace/social-accounts\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-08-01T23:59:50+00:00\";}i:37;a:4:{s:3:\"loc\";s:56:\"https://7th-tradehub.online/marketplace/network-services\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-08-02T00:00:17+00:00\";}i:38;a:4:{s:3:\"loc\";s:64:\"https://7th-tradehub.online/marketplace/social-accounts/facebook\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-23T02:24:41+00:00\";}i:39;a:4:{s:3:\"loc\";s:72:\"https://7th-tradehub.online/marketplace/network-services/marketplace-vpn\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-23T02:24:41+00:00\";}i:40;a:4:{s:3:\"loc\";s:63:\"https://7th-tradehub.online/marketplace/social-accounts/twitter\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-23T02:24:41+00:00\";}i:41;a:4:{s:3:\"loc\";s:74:\"https://7th-tradehub.online/marketplace/network-services/marketplace-proxy\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-23T02:24:41+00:00\";}i:42;a:4:{s:3:\"loc\";s:62:\"https://7th-tradehub.online/marketplace/social-accounts/tiktok\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-23T02:24:41+00:00\";}i:43;a:4:{s:3:\"loc\";s:60:\"https://7th-tradehub.online/marketplace/network-services/rdp\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-23T02:24:41+00:00\";}i:44;a:4:{s:3:\"loc\";s:65:\"https://7th-tradehub.online/marketplace/social-accounts/instagram\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-23T02:24:41+00:00\";}i:45;a:4:{s:3:\"loc\";s:72:\"https://7th-tradehub.online/marketplace/network-services/marketplace-vps\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-23T02:24:41+00:00\";}i:46;a:4:{s:3:\"loc\";s:64:\"https://7th-tradehub.online/marketplace/social-accounts/linkedin\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-23T02:24:41+00:00\";}i:47;a:4:{s:3:\"loc\";s:73:\"https://7th-tradehub.online/marketplace/network-services/marketplace-smtp\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-23T02:24:41+00:00\";}i:48;a:4:{s:3:\"loc\";s:63:\"https://7th-tradehub.online/marketplace/social-accounts/discord\";s:8:\"priority\";s:3:\"0.7\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-23T02:24:41+00:00\";}i:49;a:4:{s:3:\"loc\";s:77:\"https://7th-tradehub.online/services/network-services/vpn/residential-vpn-pro\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:50;a:4:{s:3:\"loc\";s:77:\"https://7th-tradehub.online/services/network-services/vpn/business-vpn-shield\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:51;a:4:{s:3:\"loc\";s:74:\"https://7th-tradehub.online/services/network-services/vpn/gaming-vpn-boost\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:52;a:4:{s:3:\"loc\";s:74:\"https://7th-tradehub.online/services/network-services/vpn/dedicated-ip-vpn\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:53;a:4:{s:3:\"loc\";s:73:\"https://7th-tradehub.online/services/network-services/vpn/family-vpn-pack\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:54;a:4:{s:3:\"loc\";s:73:\"https://7th-tradehub.online/services/network-services/vpn/travel-vpn-lite\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:55;a:4:{s:3:\"loc\";s:73:\"https://7th-tradehub.online/services/network-services/vps/starter-vps-1gb\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:56;a:4:{s:3:\"loc\";s:72:\"https://7th-tradehub.online/services/network-services/vps/growth-vps-2gb\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:57;a:4:{s:3:\"loc\";s:69:\"https://7th-tradehub.online/services/network-services/vps/pro-vps-4gb\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:58;a:4:{s:3:\"loc\";s:74:\"https://7th-tradehub.online/services/network-services/vps/business-vps-8gb\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:59;a:4:{s:3:\"loc\";s:70:\"https://7th-tradehub.online/services/network-services/vps/high-cpu-vps\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:60;a:4:{s:3:\"loc\";s:75:\"https://7th-tradehub.online/services/network-services/vps/storage-vps-100gb\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:61;a:4:{s:3:\"loc\";s:81:\"https://7th-tradehub.online/services/network-services/proxy/datacenter-proxy-pack\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:62;a:4:{s:3:\"loc\";s:81:\"https://7th-tradehub.online/services/network-services/proxy/residential-proxy-1gb\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:63;a:4:{s:3:\"loc\";s:77:\"https://7th-tradehub.online/services/network-services/proxy/mobile-proxy-pool\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:64;a:4:{s:3:\"loc\";s:76:\"https://7th-tradehub.online/services/network-services/proxy/isp-proxy-bundle\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:65;a:4:{s:3:\"loc\";s:80:\"https://7th-tradehub.online/services/network-services/proxy/sticky-session-proxy\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:66;a:4:{s:3:\"loc\";s:79:\"https://7th-tradehub.online/services/network-services/proxy/rotating-proxy-lite\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:67;a:4:{s:3:\"loc\";s:75:\"https://7th-tradehub.online/services/network-services/smtp/smtp-starter-10k\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:68;a:4:{s:3:\"loc\";s:74:\"https://7th-tradehub.online/services/network-services/smtp/smtp-growth-50k\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:69;a:4:{s:3:\"loc\";s:72:\"https://7th-tradehub.online/services/network-services/smtp/smtp-pro-200k\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:70;a:4:{s:3:\"loc\";s:77:\"https://7th-tradehub.online/services/network-services/smtp/transactional-smtp\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:71;a:4:{s:3:\"loc\";s:73:\"https://7th-tradehub.online/services/network-services/smtp/marketing-smtp\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:72;a:4:{s:3:\"loc\";s:76:\"https://7th-tradehub.online/services/network-services/smtp/dedicated-smtp-ip\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:73;a:4:{s:3:\"loc\";s:82:\"https://7th-tradehub.online/services/communication/virtual_phone/us-virtual-number\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:74;a:4:{s:3:\"loc\";s:82:\"https://7th-tradehub.online/services/communication/virtual_phone/uk-virtual-number\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:75;a:4:{s:3:\"loc\";s:82:\"https://7th-tradehub.online/services/communication/virtual_phone/ng-virtual-number\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:76;a:4:{s:3:\"loc\";s:85:\"https://7th-tradehub.online/services/communication/virtual_phone/business-line-bundle\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:77;a:4:{s:3:\"loc\";s:81:\"https://7th-tradehub.online/services/communication/virtual_phone/sms-ready-number\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:78;a:4:{s:3:\"loc\";s:79:\"https://7th-tradehub.online/services/communication/virtual_phone/toll-free-lite\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:79;a:4:{s:3:\"loc\";s:79:\"https://7th-tradehub.online/services/communication/email/business-email-starter\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:80;a:4:{s:3:\"loc\";s:75:\"https://7th-tradehub.online/services/communication/email/team-email-5-seats\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:81;a:4:{s:3:\"loc\";s:76:\"https://7th-tradehub.online/services/communication/email/custom-domain-email\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:82;a:4:{s:3:\"loc\";s:72:\"https://7th-tradehub.online/services/communication/email/secure-mail-pro\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:83;a:4:{s:3:\"loc\";s:74:\"https://7th-tradehub.online/services/communication/email/catch-all-mailbox\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:84;a:4:{s:3:\"loc\";s:78:\"https://7th-tradehub.online/services/communication/email/email-forwarding-pack\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:85;a:4:{s:3:\"loc\";s:86:\"https://7th-tradehub.online/services/social-media/social_service/instagram-growth-pack\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:86;a:4:{s:3:\"loc\";s:88:\"https://7th-tradehub.online/services/social-media/social_service/tiktok-engagement-boost\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:87;a:4:{s:3:\"loc\";s:83:\"https://7th-tradehub.online/services/social-media/social_service/youtube-views-lite\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:88;a:4:{s:3:\"loc\";s:86:\"https://7th-tradehub.online/services/social-media/social_service/twitter-audience-pack\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:89;a:4:{s:3:\"loc\";s:84:\"https://7th-tradehub.online/services/social-media/social_service/linkedin-lead-boost\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:90;a:4:{s:3:\"loc\";s:87:\"https://7th-tradehub.online/services/social-media/social_service/multi-platform-starter\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:91;a:4:{s:3:\"loc\";s:84:\"https://7th-tradehub.online/services/website-services/domain/com-domain-registration\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:92;a:4:{s:3:\"loc\";s:83:\"https://7th-tradehub.online/services/website-services/domain/ng-domain-registration\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:93;a:4:{s:3:\"loc\";s:83:\"https://7th-tradehub.online/services/website-services/domain/io-domain-registration\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:94;a:4:{s:3:\"loc\";s:83:\"https://7th-tradehub.online/services/website-services/domain/co-domain-registration\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:95;a:4:{s:3:\"loc\";s:83:\"https://7th-tradehub.online/services/website-services/domain/domain-transfer-assist\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:96;a:4:{s:3:\"loc\";s:80:\"https://7th-tradehub.online/services/website-services/domain/domain-privacy-pack\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:97;a:4:{s:3:\"loc\";s:92:\"https://7th-tradehub.online/services/website-services/website_template/corporate-landing-kit\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:98;a:4:{s:3:\"loc\";s:93:\"https://7th-tradehub.online/services/website-services/website_template/agency-portfolio-theme\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:99;a:4:{s:3:\"loc\";s:88:\"https://7th-tradehub.online/services/website-services/website_template/law-firm-site-kit\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:100;a:4:{s:3:\"loc\";s:92:\"https://7th-tradehub.online/services/website-services/website_template/restaurant-menu-theme\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:101;a:4:{s:3:\"loc\";s:91:\"https://7th-tradehub.online/services/website-services/website_template/medical-clinic-theme\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:102;a:4:{s:3:\"loc\";s:94:\"https://7th-tradehub.online/services/website-services/website_template/startup-launch-template\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:103;a:4:{s:3:\"loc\";s:91:\"https://7th-tradehub.online/services/website-services/website_package/starter-business-site\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:104;a:4:{s:3:\"loc\";s:90:\"https://7th-tradehub.online/services/website-services/website_package/agency-showcase-site\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:105;a:4:{s:3:\"loc\";s:93:\"https://7th-tradehub.online/services/website-services/website_package/restaurant-booking-site\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:106;a:4:{s:3:\"loc\";s:87:\"https://7th-tradehub.online/services/website-services/website_package/law-practice-site\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:107;a:4:{s:3:\"loc\";s:89:\"https://7th-tradehub.online/services/website-services/website_package/clinic-booking-site\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:108;a:4:{s:3:\"loc\";s:93:\"https://7th-tradehub.online/services/website-services/website_package/e-commerce-starter-site\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:109;a:4:{s:3:\"loc\";s:93:\"https://7th-tradehub.online/services/business-documents/document_template/sales-contract-pack\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:110;a:4:{s:3:\"loc\";s:84:\"https://7th-tradehub.online/services/business-documents/document_template/nda-bundle\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:111;a:4:{s:3:\"loc\";s:94:\"https://7th-tradehub.online/services/business-documents/document_template/employment-agreement\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:112;a:4:{s:3:\"loc\";s:93:\"https://7th-tradehub.online/services/business-documents/document_template/invoice-receipt-set\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:113;a:4:{s:3:\"loc\";s:88:\"https://7th-tradehub.online/services/business-documents/document_template/hr-policy-pack\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}i:114;a:4:{s:3:\"loc\";s:97:\"https://7th-tradehub.online/services/business-documents/document_template/service-level-agreement\";s:8:\"priority\";s:4:\"0.75\";s:10:\"changefreq\";s:6:\"weekly\";s:7:\"lastmod\";s:25:\"2026-07-18T01:37:52+00:00\";}}', 1787889095),
('7th-trade-hub-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:9:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:13:\"admins.manage\";s:1:\"c\";s:3:\"web\";}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:12:\"users.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:14:\"finance.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:14:\"support.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:5;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:14:\"catalog.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:17:\"compliance.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:13:\"system.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:14:\"analytics.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:11:\"fees.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:5:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:15:\"demo_compliance\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:12:\"demo_finance\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:12:\"demo_support\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:6;s:1:\"b\";s:14:\"demo_moderator\";s:1:\"c\";s:3:\"web\";}}}', 1788101669),
('7th-trade-hub-cache-tracking.compiled_v1:1a4fa61b88e00e955dcbaef0edcd3059', 'a:3:{s:4:\"head\";s:359:\"<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':\nnew Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],\nj=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=\n\'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);\n})(window,document,\'script\',\'dataLayer\',\'GTM-KHM5G35Z\');</script>\";s:10:\"body_start\";s:161:\"<noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id=GTM-KHM5G35Z\"\nheight=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>\";s:8:\"body_end\";s:0:\"\";}', 1788032595),
('7th-trade-hub-cache-tracking.compiled_v1.stamp', 's:32:\"1a4fa61b88e00e955dcbaef0edcd3059\";', 1788102741);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `catalog_page_contents`
--

CREATE TABLE `catalog_page_contents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `scope` varchar(20) NOT NULL,
  `key` varchar(80) NOT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `card_image` varchar(255) DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `hero_title` varchar(255) DEFAULT NULL,
  `hero_subtitle` varchar(500) DEFAULT NULL,
  `benefits` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`benefits`)),
  `faq` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`faq`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `banner_media_id` bigint(20) UNSIGNED DEFAULT NULL,
  `card_media_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `catalog_page_contents`
--

INSERT INTO `catalog_page_contents` (`id`, `scope`, `key`, `banner_image`, `card_image`, `short_description`, `hero_title`, `hero_subtitle`, `benefits`, `faq`, `created_at`, `updated_at`, `banner_media_id`, `card_media_id`) VALUES
(1, 'group', 'network-services', 'assets/images/Network Services_1.jpg', 'assets/images/Network Services_1.jpg', NULL, NULL, NULL, NULL, NULL, '2026-07-21 02:38:32', '2026-07-21 02:38:32', NULL, NULL),
(2, 'group', 'communication', 'assets/images/Communication_1.jpg', 'assets/images/Communication_1.jpg', NULL, NULL, NULL, NULL, NULL, '2026-07-21 02:38:32', '2026-07-21 02:38:32', NULL, NULL),
(3, 'group', 'social-media', 'assets/images/Social_Media.jpg', 'assets/images/Social_Media.jpg', NULL, NULL, NULL, NULL, NULL, '2026-07-21 02:38:32', '2026-07-21 02:38:32', NULL, NULL),
(4, 'group', 'website-services', 'assets/images/Website_Services.jpg', 'assets/images/Website_Services.jpg', NULL, NULL, NULL, NULL, NULL, '2026-07-21 02:38:32', '2026-07-21 02:38:32', NULL, NULL),
(5, 'group', 'business-documents', 'assets/images/Business_Documents.jpg', 'assets/images/Business_Documents.jpg', NULL, 'Documents & Receipts', NULL, NULL, NULL, '2026-07-21 02:38:32', '2026-07-21 02:38:32', NULL, NULL),
(6, 'group', 'trust-escrow', 'assets/images/flat-lay-real-estate-concept.jpg', 'assets/images/flat-lay-real-estate-concept.jpg', 'Buy and sell digital products with marketplace escrow protection.', 'Trust & Escrow', 'Explore escrow-protected purchases in the marketplace.', NULL, NULL, '2026-07-21 02:38:32', '2026-07-21 02:38:32', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'marketplace',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `short_description` varchar(500) DEFAULT NULL,
  `hero_title` varchar(255) DEFAULT NULL,
  `hero_subtitle` varchar(500) DEFAULT NULL,
  `benefits` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`benefits`)),
  `faq` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`faq`)),
  `banner_image` varchar(255) DEFAULT NULL,
  `card_image` varchar(255) DEFAULT NULL,
  `banner_media_id` bigint(20) UNSIGNED DEFAULT NULL,
  `card_media_id` bigint(20) UNSIGNED DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `og_title` varchar(255) DEFAULT NULL,
  `og_description` text DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `type`, `is_active`, `sort_order`, `short_description`, `hero_title`, `hero_subtitle`, `benefits`, `faq`, `banner_image`, `card_image`, `banner_media_id`, `card_media_id`, `icon`, `seo_title`, `seo_description`, `og_title`, `og_description`, `og_image`, `created_at`, `updated_at`) VALUES
(6, NULL, 'Social Accounts', 'social-accounts', 'marketplace', 1, 1, 'Sell your social media accounts, such as Facebook, Instagram, and TikTok, to interested buyers through our secure marketplace.', NULL, NULL, NULL, NULL, 'storage/media/2026/07/01KY5HC54YZAG4WZA2850D3MZM-medium.webp', 'storage/media/2026/07/01KY5HC54YZAG4WZA2850D3MZM-medium.webp', 17, 17, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 01:37:52', '2026-08-01 23:59:50'),
(7, 6, 'Facebook', 'facebook', 'marketplace', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(8, 6, 'Twitter / X', 'twitter', 'marketplace', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(9, 6, 'TikTok', 'tiktok', 'marketplace', 1, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(10, 6, 'Instagram', 'instagram', 'marketplace', 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(11, 6, 'LinkedIn', 'linkedin', 'marketplace', 1, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(12, 6, 'Discord', 'discord', 'marketplace', 1, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(13, NULL, 'Network Services', 'network-services', 'marketplace', 1, 2, NULL, NULL, NULL, NULL, NULL, 'storage/media/2026/07/01KY5GN5D5QG4YH2PA7PGPNX9G-medium.webp', 'storage/media/2026/07/01KY5GN5D5QG4YH2PA7PGPNX9G-medium.webp', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 01:37:52', '2026-08-02 00:00:17'),
(14, 13, 'VPN', 'marketplace-vpn', 'marketplace', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(15, 13, 'Proxy', 'marketplace-proxy', 'marketplace', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(16, 13, 'RDP', 'rdp', 'marketplace', 1, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(17, 13, 'VPS', 'marketplace-vps', 'marketplace', 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(18, 13, 'SMTP', 'marketplace-smtp', 'marketplace', 1, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 01:37:52', '2026-07-18 01:37:52');

-- --------------------------------------------------------

--
-- Table structure for table `crypto_deposit_wallets`
--

CREATE TABLE `crypto_deposit_wallets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `coin` varchar(20) NOT NULL,
  `network` varchar(40) NOT NULL,
  `address` varchar(255) NOT NULL,
  `required_confirmations` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `purpose` varchar(40) DEFAULT NULL,
  `owner` varchar(120) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `label` varchar(120) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `live_balance` decimal(28,10) DEFAULT NULL,
  `live_balance_updated_at` timestamp NULL DEFAULT NULL,
  `live_balance_error` varchar(500) DEFAULT NULL,
  `last_deposit_at` timestamp NULL DEFAULT NULL,
  `last_allocated_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_exchange_managed` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `crypto_deposit_wallets`
--

INSERT INTO `crypto_deposit_wallets` (`id`, `coin`, `network`, `address`, `required_confirmations`, `purpose`, `owner`, `notes`, `label`, `instructions`, `live_balance`, `live_balance_updated_at`, `live_balance_error`, `last_deposit_at`, `last_allocated_at`, `is_active`, `is_exchange_managed`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'BTC', 'bitcoin', '1GmJBcNyG97ULTgDQBYDRcjCQpUca54AZH', 2, NULL, NULL, NULL, NULL, NULL, 0.0000000000, '2026-08-02 23:55:43', NULL, NULL, '2026-08-02 17:59:16', 1, 1, 1, '2026-08-02 16:47:08', '2026-08-02 23:55:43'),
(2, 'USDT', 'ethereum', '0x9ba63ed8be55b83c7bfd975b798f9d1c416798cd', 12, NULL, NULL, NULL, NULL, NULL, 78.2418540000, '2026-08-02 23:55:43', NULL, NULL, NULL, 1, 1, 2, '2026-08-02 23:49:23', '2026-08-02 23:55:43'),
(3, 'USDT', 'ethereum', '0x1f907f34d61660f9cf615325e66217d52eb2b31c', 12, NULL, NULL, NULL, NULL, NULL, 0.0000000000, '2026-08-02 23:55:44', NULL, NULL, '2026-08-02 23:59:37', 1, 0, 3, '2026-08-02 23:50:30', '2026-08-02 23:59:37'),
(4, 'USDT', 'ethereum', '0x4d27c006d198964e6b92b781ca96eec57d9422da', 12, NULL, NULL, NULL, NULL, NULL, 0.0000000000, '2026-08-02 23:55:44', NULL, NULL, NULL, 1, 0, 4, '2026-08-02 23:51:38', '2026-08-02 23:55:44'),
(5, 'BTC', 'bitcoin', '1NdxcuZAQT4nQyDLPbfnLR38Qrr3Jk9iYw', 2, NULL, NULL, NULL, NULL, NULL, 0.0000000000, '2026-08-02 23:55:43', NULL, NULL, NULL, 1, 1, 5, '2026-08-02 23:54:05', '2026-08-02 23:55:43');

-- --------------------------------------------------------

--
-- Table structure for table `crypto_sell_requests`
--

CREATE TABLE `crypto_sell_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tracking_code` varchar(32) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `wallet_id` bigint(20) UNSIGNED NOT NULL,
  `crypto_deposit_wallet_id` bigint(20) UNSIGNED DEFAULT NULL,
  `coin` varchar(10) NOT NULL,
  `network` varchar(20) DEFAULT NULL,
  `amount_crypto` decimal(28,10) NOT NULL,
  `amount_crypto_base` decimal(28,10) DEFAULT NULL,
  `amount_usd` decimal(18,4) DEFAULT NULL,
  `quoted_rate_ngn` decimal(18,2) NOT NULL,
  `market_rate_ngn` decimal(18,4) DEFAULT NULL,
  `spread_ngn` decimal(18,4) DEFAULT NULL,
  `coin_usd_price` decimal(18,4) DEFAULT NULL,
  `pricing_source` varchar(60) DEFAULT NULL,
  `expected_ngn` decimal(14,2) NOT NULL,
  `credit_ngn_override` decimal(14,2) DEFAULT NULL,
  `quoted_at` timestamp NOT NULL,
  `expires_at` timestamp NOT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'waiting_deposit',
  `tx_hash` varchar(255) DEFAULT NULL,
  `platform_address` varchar(255) DEFAULT NULL,
  `required_confirmations` int(10) UNSIGNED DEFAULT NULL,
  `amount_match_status` varchar(20) DEFAULT NULL,
  `confirmations_observed` int(10) UNSIGNED DEFAULT NULL,
  `wallet_funding_id` bigint(20) UNSIGNED DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `verification_checklist` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`verification_checklist`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `crypto_sell_requests`
--

INSERT INTO `crypto_sell_requests` (`id`, `tracking_code`, `user_id`, `wallet_id`, `crypto_deposit_wallet_id`, `coin`, `network`, `amount_crypto`, `amount_crypto_base`, `amount_usd`, `quoted_rate_ngn`, `market_rate_ngn`, `spread_ngn`, `coin_usd_price`, `pricing_source`, `expected_ngn`, `credit_ngn_override`, `quoted_at`, `expires_at`, `status`, `tx_hash`, `platform_address`, `required_confirmations`, `amount_match_status`, `confirmations_observed`, `wallet_funding_id`, `admin_notes`, `verification_checklist`, `created_at`, `updated_at`) VALUES
(2, 'OTC-20260802-2MB5WH', 42, 33, 1, 'BTC', 'bitcoin', 0.0078965700, 0.0078965700, 500.0000, 1395.00, 1420.0000, 25.0000, 63318.6000, 'market_minus_coin_spread', 697500.00, NULL, '2026-08-02 17:59:16', '2026-08-02 18:14:16', 'expired', NULL, '1GmJBcNyG97ULTgDQBYDRcjCQpUca54AZH', 2, NULL, NULL, NULL, NULL, NULL, '2026-08-02 17:59:16', '2026-08-02 19:33:34'),
(3, 'OTC-20260802-X9K8SB', 42, 33, 3, 'USDT', 'ethereum', 250.0000000000, 250.0000000000, 250.0000, 1395.00, 1420.0000, 25.0000, 1.0000, 'market_minus_coin_spread', 348750.00, NULL, '2026-08-02 23:59:37', '2026-08-03 00:14:37', 'cancelled', NULL, '0x1f907f34d61660f9cf615325e66217d52eb2b31c', 12, NULL, NULL, NULL, NULL, NULL, '2026-08-02 23:59:37', '2026-08-03 00:00:23');

-- --------------------------------------------------------

--
-- Table structure for table `demo_batches`
--

CREATE TABLE `demo_batches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `source` varchar(40) NOT NULL DEFAULT 'demo:seed',
  `cleared_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demo_batches`
--

INSERT INTO `demo_batches` (`id`, `name`, `source`, `cleared_at`, `created_at`, `updated_at`) VALUES
(1, 'Demo seed 2026-07-23 23:41:58', 'demo:seed', '2026-08-01 19:50:46', '2026-07-23 23:41:58', '2026-08-01 19:50:46');

-- --------------------------------------------------------

--
-- Table structure for table `demo_batch_records`
--

CREATE TABLE `demo_batch_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `demo_batch_id` bigint(20) UNSIGNED NOT NULL,
  `record_type` varchar(160) NOT NULL,
  `record_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_delivery_attempts`
--

CREATE TABLE `email_delivery_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `correlation_id` char(36) NOT NULL,
  `provider` varchar(40) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `recipient` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `template_key` varchar(120) DEFAULT NULL,
  `purpose` varchar(80) DEFAULT NULL,
  `http_status` smallint(5) UNSIGNED DEFAULT NULL,
  `provider_error_code` varchar(120) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `response_body` text DEFAULT NULL,
  `message_id` varchar(255) DEFAULT NULL,
  `request_id` varchar(255) DEFAULT NULL,
  `latency_ms` int(10) UNSIGNED DEFAULT NULL,
  `delivery_status` varchar(40) DEFAULT NULL,
  `is_fallback` tinyint(1) NOT NULL DEFAULT 0,
  `attempt_number` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_identities`
--

CREATE TABLE `email_identities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile` varchar(40) NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `reply_to_email` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_identities`
--

INSERT INTO `email_identities` (`id`, `profile`, `from_name`, `from_email`, `reply_to_email`, `is_default`, `enabled`, `created_at`, `updated_at`) VALUES
(1, 'general', '7th Trade Hub', 'info@7th-tradehub.online', 'noreply@7th-tradehub.online', 1, 1, '2026-07-31 23:57:52', '2026-08-01 02:57:03'),
(2, 'support', 'Support Team', 'support@7th-tradehub.online', 'noreply@7th-tradehub.online', 0, 1, '2026-07-31 23:57:52', '2026-08-01 02:57:03'),
(3, 'sales', 'Sales Team', 'sales@7th-tradehub.online', 'noreply@7th-tradehub.online', 0, 1, '2026-07-31 23:57:52', '2026-08-01 02:57:03'),
(4, 'security', 'Security', 'info@7th-tradehub.online', 'noreply@7th-tradehub.online', 0, 1, '2026-07-31 23:57:52', '2026-08-01 02:57:03'),
(5, 'billing', 'Billing', 'sales@7th-tradehub.online', 'noreply@7th-tradehub.online', 0, 1, '2026-07-31 23:57:52', '2026-08-01 02:57:03'),
(6, 'noreply', '7th Trade Hub', 'noreply@7th-tradehub.online', 'noreply@7th-tradehub.online', 0, 1, '2026-07-31 23:57:52', '2026-08-01 02:57:03');

-- --------------------------------------------------------

--
-- Table structure for table `email_verification_codes`
--

CREATE TABLE `email_verification_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `code_hash` varchar(64) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_verification_codes`
--

INSERT INTO `email_verification_codes` (`id`, `user_id`, `code_hash`, `expires_at`, `attempts`, `created_at`, `updated_at`) VALUES
(1, 13, '$2y$12$W17q/JRsYjuu/Q9/dbvtdO9oNkDPMZlhBnoPEwe5iYm4/1RegjmjS', '2026-07-20 01:51:14', 0, '2026-07-20 01:36:14', '2026-07-20 01:36:14'),
(2, 15, '$2y$12$8G5WOpAmJpt6cDAZkzwkQ..lPVSDI/FTTBSSUo7AE5MZcRVfWBc12', '2026-07-23 01:52:30', 0, '2026-07-23 01:37:30', '2026-07-23 01:37:30'),
(3, 15, '$2y$12$Y8CCuY6xCrj82DpiiJ8UuudqcyXtc5zEdcGSviUJGx3bFHi3927nS', '2026-07-23 01:53:07', 0, '2026-07-23 01:38:07', '2026-07-23 01:38:07'),
(4, 16, '$2y$12$Kfrn37YC5xMeiBoqkgH.F.p/TCBtg6RBHgP/i4qSOjsfKaaNGBmNO', '2026-07-23 03:19:20', 0, '2026-07-23 03:04:20', '2026-07-23 03:04:20');

-- --------------------------------------------------------

--
-- Table structure for table `escrows`
--

CREATE TABLE `escrows` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `buyer_wallet_id` bigint(20) UNSIGNED NOT NULL,
  `seller_wallet_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'locked',
  `released_at` timestamp NULL DEFAULT NULL,
  `released_by` bigint(20) UNSIGNED DEFAULT NULL,
  `refunded_at` timestamp NULL DEFAULT NULL,
  `refund_amount` decimal(14,2) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `evidence_paths` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`evidence_paths`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exchange_rates`
--

CREATE TABLE `exchange_rates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `asset` varchar(20) NOT NULL,
  `coingecko_id` varchar(80) DEFAULT NULL,
  `bybit_symbol` varchar(40) DEFAULT NULL,
  `allowed_network_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`allowed_network_ids`)),
  `preferred_network_id` varchar(40) DEFAULT NULL,
  `logo_url` varchar(500) DEFAULT NULL,
  `buy_rate_ngn` decimal(18,2) NOT NULL,
  `sell_rate_ngn` decimal(18,2) NOT NULL,
  `spread_ngn` decimal(12,4) DEFAULT NULL,
  `minimum_amount` decimal(18,8) DEFAULT NULL,
  `maximum_amount` decimal(18,8) DEFAULT NULL,
  `min_amount_usd` decimal(18,2) DEFAULT NULL,
  `max_amount_usd` decimal(18,2) DEFAULT NULL,
  `processing_time` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exchange_rates`
--

INSERT INTO `exchange_rates` (`id`, `asset`, `coingecko_id`, `bybit_symbol`, `allowed_network_ids`, `preferred_network_id`, `logo_url`, `buy_rate_ngn`, `sell_rate_ngn`, `spread_ngn`, `minimum_amount`, `maximum_amount`, `min_amount_usd`, `max_amount_usd`, `processing_time`, `is_featured`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'BTC', 'bitcoin', 'BTCUSDT', '[\"bitcoin\"]', 'bitcoin', 'https://assets.coingecko.com/coins/images/1/large/bitcoin.png', 1395.00, 1395.00, 25.0000, NULL, NULL, 20.00, 2000.00, '5–15 minutes', 1, 1, 0, '2026-07-18 01:37:52', '2026-08-02 17:28:00'),
(2, 'ETH', 'ethereum', 'ETHUSDT', '[\"ethereum\",\"base\",\"arbitrum\"]', 'ethereum', 'https://assets.coingecko.com/coins/images/279/large/ethereum.png', 1395.00, 1395.00, 25.0000, NULL, NULL, 20.00, 2000.00, '5–15 minutes', 1, 1, 1, '2026-07-18 01:37:52', '2026-08-02 17:28:41'),
(3, 'USDT', 'tether', 'USDTUSDT', '[\"ethereum\",\"tron\",\"bsc\",\"polygon\",\"base\",\"arbitrum\",\"solana\"]', 'tron', 'https://assets.coingecko.com/coins/images/325/large/Tether.png', 1395.00, 1395.00, 25.0000, NULL, NULL, NULL, NULL, '5–10 minutes', 1, 1, 2, '2026-07-18 01:37:52', '2026-08-02 17:28:55'),
(4, 'SOL', 'solana', 'SOLUSDT', '[\"solana\"]', 'solana', 'https://assets.coingecko.com/coins/images/4128/large/solana.png', 1395.00, 1395.00, 25.0000, NULL, NULL, 20.00, 2000.00, '5–15 minutes', 0, 1, 3, '2026-07-18 01:37:52', '2026-08-02 17:30:22'),
(5, 'BNB', 'binancecoin', 'BNBUSDT', '[\"bsc\"]', 'bsc', 'https://assets.coingecko.com/coins/images/825/large/bnb-icon2_2x.png', 1395.00, 1395.00, 25.0000, NULL, NULL, 20.00, 2000.00, '5–15 minutes', 0, 1, 4, '2026-07-18 01:37:52', '2026-08-02 17:30:47'),
(6, 'XRP', 'ripple', NULL, '[]', NULL, 'https://coin-images.coingecko.com/coins/images/44/large/xrp-symbol-white-128.png?1696501442', 1395.00, 1395.00, 25.0000, NULL, NULL, 20.00, 2000.00, NULL, 0, 1, 5, '2026-08-02 14:53:29', '2026-08-02 17:31:06');

-- --------------------------------------------------------

--
-- Table structure for table `exchange_rate_history`
--

CREATE TABLE `exchange_rate_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `market_rate_ngn` decimal(18,4) NOT NULL,
  `spread_ngn` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `customer_rate_ngn` decimal(18,4) NOT NULL,
  `source` varchar(60) NOT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `recorded_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `favoritable_type` varchar(255) NOT NULL,
  `favoritable_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gateway_operations`
--

CREATE TABLE `gateway_operations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `idempotency_key` varchar(64) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `operation` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `wallet_id` bigint(20) UNSIGNED DEFAULT NULL,
  `request_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_payload`)),
  `response_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_payload`)),
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gateway_operations`
--

INSERT INTO `gateway_operations` (`id`, `idempotency_key`, `provider`, `operation`, `status`, `user_id`, `wallet_id`, `request_payload`, `response_payload`, `error_message`, `created_at`, `updated_at`) VALUES
(1, 'wallet-create-3', 'manual', 'create_subaccount', 'completed', NULL, NULL, '{\"user_id\":3}', '{\"gateway_subaccount_id\":\"manual_wallet-create-3\"}', NULL, '2026-07-18 14:41:09', '2026-07-18 14:41:09'),
(2, 'wallet-create-4', 'manual', 'create_subaccount', 'completed', NULL, NULL, '{\"user_id\":4}', '{\"gateway_subaccount_id\":\"manual_wallet-create-4\"}', NULL, '2026-07-18 14:41:09', '2026-07-18 14:41:09'),
(3, 'wallet-create-5', 'manual', 'create_subaccount', 'completed', NULL, NULL, '{\"user_id\":5}', '{\"gateway_subaccount_id\":\"manual_wallet-create-5\"}', NULL, '2026-07-18 14:41:09', '2026-07-18 14:41:09'),
(4, 'wallet-create-6', 'manual', 'create_subaccount', 'completed', NULL, NULL, '{\"user_id\":6}', '{\"gateway_subaccount_id\":\"manual_wallet-create-6\"}', NULL, '2026-07-18 14:41:09', '2026-07-18 14:41:09'),
(5, 'wallet-create-7', 'manual', 'create_subaccount', 'completed', NULL, NULL, '{\"user_id\":7}', '{\"gateway_subaccount_id\":\"manual_wallet-create-7\"}', NULL, '2026-07-18 14:41:09', '2026-07-18 14:41:09'),
(6, 'wallet-create-8', 'manual', 'create_subaccount', 'completed', NULL, NULL, '{\"user_id\":8}', '{\"gateway_subaccount_id\":\"manual_wallet-create-8\"}', NULL, '2026-07-18 14:41:10', '2026-07-18 14:41:10'),
(7, 'wallet-create-9', 'manual', 'create_subaccount', 'completed', NULL, NULL, '{\"user_id\":9}', '{\"gateway_subaccount_id\":\"manual_wallet-create-9\"}', NULL, '2026-07-18 14:41:10', '2026-07-18 14:41:10'),
(8, 'wallet-create-10', 'manual', 'create_subaccount', 'completed', NULL, NULL, '{\"user_id\":10}', '{\"gateway_subaccount_id\":\"manual_wallet-create-10\"}', NULL, '2026-07-18 14:41:10', '2026-07-18 14:41:10'),
(9, 'wallet-create-11', 'manual', 'create_subaccount', 'completed', NULL, NULL, '{\"user_id\":11}', '{\"gateway_subaccount_id\":\"manual_wallet-create-11\"}', NULL, '2026-07-18 14:41:10', '2026-07-18 14:41:10'),
(10, 'wallet-create-12', 'manual', 'create_subaccount', 'completed', NULL, NULL, '{\"user_id\":12}', '{\"gateway_subaccount_id\":\"manual_wallet-create-12\"}', NULL, '2026-07-18 14:41:10', '2026-07-18 14:41:10'),
(11, 'wallet-create-16', 'manual', 'create_subaccount', 'completed', 16, 32, '{\"user_id\":16}', '{\"gateway_subaccount_id\":\"manual_wallet-create-16\"}', NULL, '2026-08-01 01:57:34', '2026-08-01 01:57:34'),
(12, 'wallet-create-42', 'manual', 'create_subaccount', 'completed', 42, 33, '{\"user_id\":42}', '{\"gateway_subaccount_id\":\"manual_wallet-create-42\"}', NULL, '2026-08-01 03:29:29', '2026-08-01 03:29:29'),
(13, 'wallet-create-1', 'manual', 'create_subaccount', 'completed', 1, 34, '{\"user_id\":1}', '{\"gateway_subaccount_id\":\"manual_wallet-create-1\"}', NULL, '2026-08-02 01:28:31', '2026-08-02 01:28:31');

-- --------------------------------------------------------

--
-- Table structure for table `incoming_crypto_transactions`
--

CREATE TABLE `incoming_crypto_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `coin` varchar(20) NOT NULL,
  `network` varchar(40) NOT NULL,
  `wallet_address` varchar(255) NOT NULL,
  `tx_hash` varchar(255) NOT NULL,
  `amount` decimal(28,10) NOT NULL,
  `block_height` bigint(20) UNSIGNED DEFAULT NULL,
  `confirmations` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `from_address` varchar(255) DEFAULT NULL,
  `token_contract` varchar(128) DEFAULT NULL,
  `detected_at` timestamp NOT NULL,
  `matched_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'detected',
  `raw` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `integration_providers`
--

CREATE TABLE `integration_providers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `provider` varchar(60) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `credentials` text DEFAULT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'idle',
  `last_sync_at` timestamp NULL DEFAULT NULL,
  `last_tested_at` timestamp NULL DEFAULT NULL,
  `last_success_at` timestamp NULL DEFAULT NULL,
  `last_error_at` timestamp NULL DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `success_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `failure_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `avg_latency_ms` int(10) UNSIGNED DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `integration_providers`
--

INSERT INTO `integration_providers` (`id`, `provider`, `enabled`, `credentials`, `status`, `last_sync_at`, `last_tested_at`, `last_success_at`, `last_error_at`, `last_error`, `success_count`, `failure_count`, `avg_latency_ms`, `meta`, `created_at`, `updated_at`) VALUES
(1, 'google_analytics', 0, 'eyJpdiI6IlNyd3FlYTVtbWY2SENiRmFBVEhFNEE9PSIsInZhbHVlIjoiUjUvOTJrMmZXZDJzOUt6U2dmNkRlWTVxOEVLTW0vRWtBcEpjdUxxZnBHVWx1c0lvd2FQSTYwVUd5YXF6MGx4bzVJK1djb2w4V3A0cFdyWVhSczFjREE9PSIsIm1hYyI6IjQ3MzY5ZmY3MmY5YjNiNDBiMWIxNzlmMmNkNWZjMTEwMmRjYjMzOWZjZTFkMzM5YmJiZGQzNjNlYmRhN2U1NjYiLCJ0YWciOiIifQ==', 'idle', NULL, '2026-08-01 23:10:47', '2026-08-01 23:10:47', NULL, NULL, 1, 0, NULL, NULL, '2026-07-23 18:50:34', '2026-08-02 00:21:43'),
(2, 'microsoft_clarity', 0, 'eyJpdiI6InZuT2huTVREamxFb0o1cFo3dnlTVkE9PSIsInZhbHVlIjoiOFR3NkZPQWZ6NGptdWFTMmoyWFpwZ3FoNDM3dzkxUkFlTWNBck1ia2dxUT0iLCJtYWMiOiJjNTM0ZmJjNTczZDJmNzJkNmY2OTRjNTYzZGZlYTYzNzY0ZTY1YzQyOWQwZWU0MTg0YmU3YWJmMGVlODA3OWNlIiwidGFnIjoiIn0=', 'idle', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, '2026-07-23 18:50:34', '2026-08-01 23:10:28'),
(3, 'brevo', 1, 'eyJpdiI6ImRtTEJDMmdCVlUySXNaMktLQUJnZGc9PSIsInZhbHVlIjoicFhYcUZJMjFJK3AwV2NQVmxGTGtxMGZQYVE1c1VpNzJSakN0Y3Q1bDFSVEtBM2ZHYlV2amVFOVh2S3hKTlFyK1NDOHZhZXVPTzZtQndKUTRtR2FzSzNIdlVzZnowV0cwQk1LMW9XRktLUldSTTBkbUd0UzdCYUZ4ekQxVS9NVmZKVVk0b0FuUFdPQjU5NVMzMDcwTW1RPT0iLCJtYWMiOiI4ODhmMGEyYmI3ZGRlMmU1MTllYzdiNTE0NGExODA3OTJiM2IxNTE5NGU0MGMyNTg3MjNjYTA2MzgxZTc3NDAwIiwidGFnIjoiIn0=', 'connected', NULL, '2026-08-01 03:23:12', '2026-08-01 03:23:12', '2026-08-01 02:57:31', NULL, 2, 2, 100, '{\"daily_usage\":{\"2026-08-01\":2},\"last_email_sent_at\":\"2026-08-01T03:23:12+00:00\"}', '2026-07-31 23:57:52', '2026-08-01 03:23:12'),
(4, 'laravel_mail', 0, 'eyJpdiI6IkFBUS9vQk5zVTRlTGJGZjBJaUpoTUE9PSIsInZhbHVlIjoiaVpWWUNJMnZIc1JjNmFXRk9lS2prM0E1M3N1ckU5aU92d3FLcUlnYUFUTFVSayttM2kvU25KRmlQbC9KUWZHOGtPZXFKamRDWFVSK1JRTXNVRFJ0bGp1SzFhQ29vRmpXR2c3TlJGS2VFN214bG5kc09rdkVQTlZuVlRwTFExeURJOHpSOGJQWE5iUitFTDQ3N0lIYllaM3kxK3NTSURldTFUSXAyM2lrRjl6aVBEeXN0UzlsdVlJdWoweXMrMkF1IiwibWFjIjoiYjE5Y2I0MzMxOGNkNzUwMDE1YzY3MGIyYmU5MTJjNGU4MmIxZDIxNzgxN2Q4NTVkZDkwZmViZDYzODMxMjc1ZiIsInRhZyI6IiJ9', 'idle', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, '2026-07-31 23:57:52', '2026-08-01 02:57:03'),
(5, 'smartsupp', 0, NULL, 'idle', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, '2026-07-31 23:57:52', '2026-07-31 23:57:52'),
(6, 'jivo', 0, NULL, 'idle', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, '2026-07-31 23:57:52', '2026-07-31 23:57:52'),
(7, 'chatway', 0, NULL, 'idle', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, '2026-07-31 23:57:52', '2026-07-31 23:57:52'),
(8, 'google_identity', 0, NULL, 'idle', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, '2026-08-01 19:23:43', '2026-08-01 19:23:43'),
(9, 'monnify', 0, NULL, 'idle', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, '2026-08-01 19:36:01', '2026-08-01 19:36:01'),
(10, 'google_tag_manager', 1, 'eyJpdiI6IkllMUYxQUhnNzV5TForQXl5Y0Zjcnc9PSIsInZhbHVlIjoiRnhxV0pHSmVuZmR6d1Q0cGxNaFFnSm13SENBZ0E5RFdLay9zT1JqbkNEYz0iLCJtYWMiOiJjMDg1ODRhOTk1ZTJiMTU1NmNiNmEzYTczZDYzMzYyNzRmM2I5NTEyNmNkMWEwY2YyYTE2YTk1YmExOTU5MWUyIiwidGFnIjoiIn0=', 'configured', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, '2026-08-01 23:38:41', '2026-08-02 00:20:37'),
(11, 'meta_pixel', 0, 'eyJpdiI6ImZkRVNhMXpQbk1jZFJXQ093SEZ6b1E9PSIsInZhbHVlIjoiK0Rrbk00N0g2aHlMRy9jeENtUkI3UT09IiwibWFjIjoiM2ZkN2QxYjJhYjBhYWE4MjVmYjFhZTc5YmJmYWRiNTVmZjA5ZmU3NWE5NGU3NDAxNzk5MTk2MTE3NDBmOWY1ZiIsInRhZyI6IiJ9', 'idle', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, '2026-08-01 23:38:41', '2026-08-02 00:20:37'),
(12, 'blockchain_monitoring', 1, 'eyJpdiI6Ik9aV0lzMkpOeWxFSWd3djNvTWVKQkE9PSIsInZhbHVlIjoiOStZanNKUy9yMXhkOEpJSlo2TDdWTldDenBNSHl6NjZGS2pHc1Q0SmJ0WXpjZENCRlhKZm1mem9vdGNqTkkwa1FZZ0tPaHpxbDhRUjVZUG5nejJLZktQTlcvd0JjdVhRMjF4Z0pDVjdMbEZHY3plbTdWMkFGdWhNYW5JODVaK1p0blZtbzRERGpoM1FiVXc0eHlFNXJ2WjNaV25KVUtlemhEZzdqWkJZMWo4PSIsIm1hYyI6IjM5MjliOTUxOGI0ODFmNWNiMzE2Y2E4MTRlOWNjNTBjNGQ5M2M1ZjU3NGU5MzE4YjE2ODlhYWM5OGY2MDBmMTAiLCJ0YWciOiIifQ==', 'connected', NULL, '2026-08-02 16:24:01', '2026-08-02 16:24:01', NULL, NULL, 8, 0, 144, '{\"monitor_provider\":\"native\",\"poll_interval_minutes\":1,\"network_health\":{\"bitcoin\":{\"status\":\"healthy\",\"provider\":\"native\",\"client\":\"mempool\",\"endpoint\":\"mempool.space\",\"auth_status\":\"ok\",\"last_poll_at\":\"2026-08-02T16:11:22+00:00\",\"last_success_at\":\"2026-08-02T16:11:22+00:00\",\"last_error\":null,\"latency_ms\":47,\"tip_height\":960747},\"ethereum\":{\"status\":\"healthy\",\"provider\":\"native\",\"client\":\"etherscan\",\"endpoint\":\"api.etherscan.io\",\"auth_status\":\"ok\",\"last_poll_at\":\"2026-08-02T16:22:50+00:00\",\"last_success_at\":\"2026-08-02T16:22:50+00:00\",\"last_error\":null,\"latency_ms\":364,\"tip_height\":25668365},\"bep20\":{\"status\":\"healthy\",\"provider\":\"native\",\"client\":\"etherscan\",\"endpoint\":\"api.etherscan.io\",\"auth_status\":\"ok\",\"last_poll_at\":\"2026-08-02T16:23:37+00:00\",\"last_success_at\":\"2026-08-02T16:23:37+00:00\",\"last_error\":null,\"latency_ms\":709,\"tip_height\":null},\"polygon\":{\"status\":\"healthy\",\"provider\":\"native\",\"client\":\"etherscan\",\"endpoint\":\"api.etherscan.io\",\"auth_status\":\"ok\",\"last_poll_at\":\"2026-08-02T16:23:42+00:00\",\"last_success_at\":\"2026-08-02T16:23:42+00:00\",\"last_error\":null,\"latency_ms\":337,\"tip_height\":91321863},\"base\":{\"status\":\"healthy\",\"provider\":\"native\",\"client\":\"etherscan\",\"endpoint\":\"api.etherscan.io\",\"auth_status\":\"ok\",\"last_poll_at\":\"2026-08-02T16:23:47+00:00\",\"last_success_at\":\"2026-08-02T16:23:47+00:00\",\"last_error\":null,\"latency_ms\":693,\"tip_height\":null},\"arbitrum\":{\"status\":\"healthy\",\"provider\":\"native\",\"client\":\"etherscan\",\"endpoint\":\"api.etherscan.io\",\"auth_status\":\"ok\",\"last_poll_at\":\"2026-08-02T16:23:52+00:00\",\"last_success_at\":\"2026-08-02T16:23:52+00:00\",\"last_error\":null,\"latency_ms\":348,\"tip_height\":490383032},\"tron\":{\"status\":\"healthy\",\"provider\":\"native\",\"client\":\"trongrid\",\"endpoint\":\"api.trongrid.io\",\"auth_status\":\"ok\",\"last_poll_at\":\"2026-08-02T16:23:57+00:00\",\"last_success_at\":\"2026-08-02T16:23:57+00:00\",\"last_error\":null,\"latency_ms\":83,\"tip_height\":85005675},\"solana\":{\"status\":\"healthy\",\"provider\":\"native\",\"client\":\"solana\",\"endpoint\":\"api.mainnet-beta.solana.com\",\"auth_status\":\"ok\",\"last_poll_at\":\"2026-08-02T16:24:01+00:00\",\"last_success_at\":\"2026-08-02T16:24:01+00:00\",\"last_error\":null,\"latency_ms\":23,\"tip_height\":436799460}}}', '2026-08-02 13:02:39', '2026-08-02 16:24:01');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, 'default', '{\"uuid\":\"905c694b-3694-4faf-a347-f1e953202ca8\",\"displayName\":\"App\\\\Jobs\\\\RetryFailedEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":1,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\RetryFailedEmailJob\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\RetryFailedEmailJob\\\":3:{s:5:\\\"email\\\";O:47:\\\"App\\\\Services\\\\Communications\\\\Email\\\\OutgoingEmail\\\":14:{s:2:\\\"to\\\";a:1:{i:0;a:1:{s:5:\\\"email\\\";s:27:\\\"support@7th-tradehub.online\\\";}}s:7:\\\"subject\\\";s:18:\\\"New support ticket\\\";s:11:\\\"htmlContent\\\";s:918:\\\"<!DOCTYPE html>\\n<html>\\n<head>\\n    <meta charset=\\\"utf-8\\\">\\n    <meta name=\\\"viewport\\\" content=\\\"width=device-width, initial-scale=1\\\">\\n    <title>New support ticket<\\/title>\\n<\\/head>\\n<body style=\\\"font-family: system-ui, sans-serif; line-height: 1.5; color: #111; max-width: 560px; margin: 0 auto; padding: 24px;\\\">\\n    <p style=\\\"margin: 0 0 8px; font-size: 14px; color: #666;\\\">7th Trade Hub<\\/p>\\n    <h1 style=\\\"font-size: 20px; margin: 0 0 12px;\\\">New support ticket<\\/h1>\\n            <p style=\\\"margin: 0 0 16px;\\\">Ticket #41 was opened.<\\/p>\\n                <p style=\\\"margin: 0 0 24px;\\\">\\n            <a href=\\\"https:\\/\\/7th-tradehub.online\\/admin\\/tickets\\\" style=\\\"display: inline-block; background: #111; color: #fff; text-decoration: none; padding: 10px 16px; border-radius: 6px;\\\">\\n                View details\\n            <\\/a>\\n        <\\/p>\\n        <p style=\\\"margin: 0; font-size: 12px; color: #888;\\\">Hello Admin,<\\/p>\\n<\\/body>\\n<\\/html>\\n\\\";s:11:\\\"textContent\\\";N;s:11:\\\"templateKey\\\";s:12:\\\"notification\\\";s:7:\\\"profile\\\";E:54:\\\"App\\\\Services\\\\Communications\\\\Email\\\\EmailProfile:Support\\\";s:7:\\\"replyTo\\\";N;s:2:\\\"cc\\\";a:0:{}s:3:\\\"bcc\\\";a:0:{}s:11:\\\"attachments\\\";a:0:{}s:4:\\\"tags\\\";a:1:{i:0;s:12:\\\"notification\\\";}s:10:\\\"templateId\\\";N;s:6:\\\"params\\\";a:0:{}s:11:\\\"scheduledAt\\\";N;}s:12:\\\"attemptStage\\\";i:1;s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":4:{s:4:\\\"date\\\";s:26:\\\"2026-08-01 01:05:32.020272\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";s:18:\\\"dumpDateProperties\\\";a:2:{s:4:\\\"date\\\";s:26:\\\"2026-08-01 01:05:32.020272\\\";s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}}\",\"batchId\":null},\"createdAt\":1785546032,\"delay\":300}', 0, NULL, 1785546332, 1785546032),
(2, 'default', '{\"uuid\":\"ecd618e0-d73a-4de0-959a-9b15f34dd98e\",\"displayName\":\"App\\\\Jobs\\\\RetryFailedEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":1,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\RetryFailedEmailJob\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\RetryFailedEmailJob\\\":3:{s:5:\\\"email\\\";O:47:\\\"App\\\\Services\\\\Communications\\\\Email\\\\OutgoingEmail\\\":14:{s:2:\\\"to\\\";a:1:{i:0;a:1:{s:5:\\\"email\\\";s:23:\\\"super.admin@example.com\\\";}}s:7:\\\"subject\\\";s:18:\\\"New support ticket\\\";s:11:\\\"htmlContent\\\";s:924:\\\"<!DOCTYPE html>\\n<html>\\n<head>\\n    <meta charset=\\\"utf-8\\\">\\n    <meta name=\\\"viewport\\\" content=\\\"width=device-width, initial-scale=1\\\">\\n    <title>New support ticket<\\/title>\\n<\\/head>\\n<body style=\\\"font-family: system-ui, sans-serif; line-height: 1.5; color: #111; max-width: 560px; margin: 0 auto; padding: 24px;\\\">\\n    <p style=\\\"margin: 0 0 8px; font-size: 14px; color: #666;\\\">7th Trade Hub<\\/p>\\n    <h1 style=\\\"font-size: 20px; margin: 0 0 12px;\\\">New support ticket<\\/h1>\\n            <p style=\\\"margin: 0 0 16px;\\\">Ticket #41 was opened.<\\/p>\\n                <p style=\\\"margin: 0 0 24px;\\\">\\n            <a href=\\\"https:\\/\\/7th-tradehub.online\\/admin\\/tickets\\\" style=\\\"display: inline-block; background: #111; color: #fff; text-decoration: none; padding: 10px 16px; border-radius: 6px;\\\">\\n                View details\\n            <\\/a>\\n        <\\/p>\\n        <p style=\\\"margin: 0; font-size: 12px; color: #888;\\\">Hello Super Admin,<\\/p>\\n<\\/body>\\n<\\/html>\\n\\\";s:11:\\\"textContent\\\";N;s:11:\\\"templateKey\\\";s:12:\\\"notification\\\";s:7:\\\"profile\\\";E:54:\\\"App\\\\Services\\\\Communications\\\\Email\\\\EmailProfile:Support\\\";s:7:\\\"replyTo\\\";N;s:2:\\\"cc\\\";a:0:{}s:3:\\\"bcc\\\";a:0:{}s:11:\\\"attachments\\\";a:0:{}s:4:\\\"tags\\\";a:1:{i:0;s:12:\\\"notification\\\";}s:10:\\\"templateId\\\";N;s:6:\\\"params\\\";a:0:{}s:11:\\\"scheduledAt\\\";N;}s:12:\\\"attemptStage\\\";i:1;s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":4:{s:4:\\\"date\\\";s:26:\\\"2026-08-01 01:05:32.028108\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";s:18:\\\"dumpDateProperties\\\";a:2:{s:4:\\\"date\\\";s:26:\\\"2026-08-01 01:05:32.028108\\\";s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}}\",\"batchId\":null},\"createdAt\":1785546032,\"delay\":300}', 0, NULL, 1785546332, 1785546032),
(3, 'default', '{\"uuid\":\"31e564c5-8fde-442f-a624-828c937787b4\",\"displayName\":\"App\\\\Jobs\\\\RetryFailedEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":1,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\RetryFailedEmailJob\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\RetryFailedEmailJob\\\":3:{s:5:\\\"email\\\";O:47:\\\"App\\\\Services\\\\Communications\\\\Email\\\\OutgoingEmail\\\":14:{s:2:\\\"to\\\";a:1:{i:0;a:1:{s:5:\\\"email\\\";s:25:\\\"support.admin@example.com\\\";}}s:7:\\\"subject\\\";s:18:\\\"New support ticket\\\";s:11:\\\"htmlContent\\\";s:926:\\\"<!DOCTYPE html>\\n<html>\\n<head>\\n    <meta charset=\\\"utf-8\\\">\\n    <meta name=\\\"viewport\\\" content=\\\"width=device-width, initial-scale=1\\\">\\n    <title>New support ticket<\\/title>\\n<\\/head>\\n<body style=\\\"font-family: system-ui, sans-serif; line-height: 1.5; color: #111; max-width: 560px; margin: 0 auto; padding: 24px;\\\">\\n    <p style=\\\"margin: 0 0 8px; font-size: 14px; color: #666;\\\">7th Trade Hub<\\/p>\\n    <h1 style=\\\"font-size: 20px; margin: 0 0 12px;\\\">New support ticket<\\/h1>\\n            <p style=\\\"margin: 0 0 16px;\\\">Ticket #41 was opened.<\\/p>\\n                <p style=\\\"margin: 0 0 24px;\\\">\\n            <a href=\\\"https:\\/\\/7th-tradehub.online\\/admin\\/tickets\\\" style=\\\"display: inline-block; background: #111; color: #fff; text-decoration: none; padding: 10px 16px; border-radius: 6px;\\\">\\n                View details\\n            <\\/a>\\n        <\\/p>\\n        <p style=\\\"margin: 0; font-size: 12px; color: #888;\\\">Hello Support Admin,<\\/p>\\n<\\/body>\\n<\\/html>\\n\\\";s:11:\\\"textContent\\\";N;s:11:\\\"templateKey\\\";s:12:\\\"notification\\\";s:7:\\\"profile\\\";E:54:\\\"App\\\\Services\\\\Communications\\\\Email\\\\EmailProfile:Support\\\";s:7:\\\"replyTo\\\";N;s:2:\\\"cc\\\";a:0:{}s:3:\\\"bcc\\\";a:0:{}s:11:\\\"attachments\\\";a:0:{}s:4:\\\"tags\\\";a:1:{i:0;s:12:\\\"notification\\\";}s:10:\\\"templateId\\\";N;s:6:\\\"params\\\";a:0:{}s:11:\\\"scheduledAt\\\";N;}s:12:\\\"attemptStage\\\";i:1;s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":4:{s:4:\\\"date\\\";s:26:\\\"2026-08-01 01:05:32.029951\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";s:18:\\\"dumpDateProperties\\\";a:2:{s:4:\\\"date\\\";s:26:\\\"2026-08-01 01:05:32.029951\\\";s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}}\",\"batchId\":null},\"createdAt\":1785546032,\"delay\":300}', 0, NULL, 1785546332, 1785546032),
(4, 'default', '{\"uuid\":\"019d5703-38dc-45f7-9f49-7e7211be3cd6\",\"displayName\":\"App\\\\Jobs\\\\RetryFailedEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":1,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\RetryFailedEmailJob\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\RetryFailedEmailJob\\\":3:{s:5:\\\"email\\\";O:47:\\\"App\\\\Services\\\\Communications\\\\Email\\\\OutgoingEmail\\\":14:{s:2:\\\"to\\\";a:1:{i:0;a:1:{s:5:\\\"email\\\";s:27:\\\"support@7th-tradehub.online\\\";}}s:7:\\\"subject\\\";s:20:\\\"Support ticket reply\\\";s:11:\\\"htmlContent\\\";s:933:\\\"<!DOCTYPE html>\\n<html>\\n<head>\\n    <meta charset=\\\"utf-8\\\">\\n    <meta name=\\\"viewport\\\" content=\\\"width=device-width, initial-scale=1\\\">\\n    <title>Support ticket reply<\\/title>\\n<\\/head>\\n<body style=\\\"font-family: system-ui, sans-serif; line-height: 1.5; color: #111; max-width: 560px; margin: 0 auto; padding: 24px;\\\">\\n    <p style=\\\"margin: 0 0 8px; font-size: 14px; color: #666;\\\">7th Trade Hub<\\/p>\\n    <h1 style=\\\"font-size: 20px; margin: 0 0 12px;\\\">Support ticket reply<\\/h1>\\n            <p style=\\\"margin: 0 0 16px;\\\">Ticket #41 received a user reply.<\\/p>\\n                <p style=\\\"margin: 0 0 24px;\\\">\\n            <a href=\\\"https:\\/\\/7th-tradehub.online\\/admin\\/tickets\\\" style=\\\"display: inline-block; background: #111; color: #fff; text-decoration: none; padding: 10px 16px; border-radius: 6px;\\\">\\n                View details\\n            <\\/a>\\n        <\\/p>\\n        <p style=\\\"margin: 0; font-size: 12px; color: #888;\\\">Hello Admin,<\\/p>\\n<\\/body>\\n<\\/html>\\n\\\";s:11:\\\"textContent\\\";N;s:11:\\\"templateKey\\\";s:12:\\\"notification\\\";s:7:\\\"profile\\\";E:54:\\\"App\\\\Services\\\\Communications\\\\Email\\\\EmailProfile:Support\\\";s:7:\\\"replyTo\\\";N;s:2:\\\"cc\\\";a:0:{}s:3:\\\"bcc\\\";a:0:{}s:11:\\\"attachments\\\";a:0:{}s:4:\\\"tags\\\";a:1:{i:0;s:12:\\\"notification\\\";}s:10:\\\"templateId\\\";N;s:6:\\\"params\\\";a:0:{}s:11:\\\"scheduledAt\\\";N;}s:12:\\\"attemptStage\\\";i:1;s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":4:{s:4:\\\"date\\\";s:26:\\\"2026-08-01 01:05:55.921199\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";s:18:\\\"dumpDateProperties\\\";a:2:{s:4:\\\"date\\\";s:26:\\\"2026-08-01 01:05:55.921199\\\";s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}}\",\"batchId\":null},\"createdAt\":1785546055,\"delay\":300}', 0, NULL, 1785546355, 1785546055),
(5, 'default', '{\"uuid\":\"01f89ede-4ea6-4ef1-8492-960ae0337a25\",\"displayName\":\"App\\\\Jobs\\\\RetryFailedEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":1,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\RetryFailedEmailJob\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\RetryFailedEmailJob\\\":3:{s:5:\\\"email\\\";O:47:\\\"App\\\\Services\\\\Communications\\\\Email\\\\OutgoingEmail\\\":14:{s:2:\\\"to\\\";a:1:{i:0;a:1:{s:5:\\\"email\\\";s:23:\\\"super.admin@example.com\\\";}}s:7:\\\"subject\\\";s:20:\\\"Support ticket reply\\\";s:11:\\\"htmlContent\\\";s:939:\\\"<!DOCTYPE html>\\n<html>\\n<head>\\n    <meta charset=\\\"utf-8\\\">\\n    <meta name=\\\"viewport\\\" content=\\\"width=device-width, initial-scale=1\\\">\\n    <title>Support ticket reply<\\/title>\\n<\\/head>\\n<body style=\\\"font-family: system-ui, sans-serif; line-height: 1.5; color: #111; max-width: 560px; margin: 0 auto; padding: 24px;\\\">\\n    <p style=\\\"margin: 0 0 8px; font-size: 14px; color: #666;\\\">7th Trade Hub<\\/p>\\n    <h1 style=\\\"font-size: 20px; margin: 0 0 12px;\\\">Support ticket reply<\\/h1>\\n            <p style=\\\"margin: 0 0 16px;\\\">Ticket #41 received a user reply.<\\/p>\\n                <p style=\\\"margin: 0 0 24px;\\\">\\n            <a href=\\\"https:\\/\\/7th-tradehub.online\\/admin\\/tickets\\\" style=\\\"display: inline-block; background: #111; color: #fff; text-decoration: none; padding: 10px 16px; border-radius: 6px;\\\">\\n                View details\\n            <\\/a>\\n        <\\/p>\\n        <p style=\\\"margin: 0; font-size: 12px; color: #888;\\\">Hello Super Admin,<\\/p>\\n<\\/body>\\n<\\/html>\\n\\\";s:11:\\\"textContent\\\";N;s:11:\\\"templateKey\\\";s:12:\\\"notification\\\";s:7:\\\"profile\\\";E:54:\\\"App\\\\Services\\\\Communications\\\\Email\\\\EmailProfile:Support\\\";s:7:\\\"replyTo\\\";N;s:2:\\\"cc\\\";a:0:{}s:3:\\\"bcc\\\";a:0:{}s:11:\\\"attachments\\\";a:0:{}s:4:\\\"tags\\\";a:1:{i:0;s:12:\\\"notification\\\";}s:10:\\\"templateId\\\";N;s:6:\\\"params\\\";a:0:{}s:11:\\\"scheduledAt\\\";N;}s:12:\\\"attemptStage\\\";i:1;s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":4:{s:4:\\\"date\\\";s:26:\\\"2026-08-01 01:05:55.924987\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";s:18:\\\"dumpDateProperties\\\";a:2:{s:4:\\\"date\\\";s:26:\\\"2026-08-01 01:05:55.924987\\\";s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}}\",\"batchId\":null},\"createdAt\":1785546055,\"delay\":300}', 0, NULL, 1785546355, 1785546055),
(6, 'default', '{\"uuid\":\"2fcefabd-baa3-45f2-b00c-90a67968687a\",\"displayName\":\"App\\\\Jobs\\\\RetryFailedEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":1,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\RetryFailedEmailJob\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\RetryFailedEmailJob\\\":3:{s:5:\\\"email\\\";O:47:\\\"App\\\\Services\\\\Communications\\\\Email\\\\OutgoingEmail\\\":14:{s:2:\\\"to\\\";a:1:{i:0;a:1:{s:5:\\\"email\\\";s:25:\\\"support.admin@example.com\\\";}}s:7:\\\"subject\\\";s:20:\\\"Support ticket reply\\\";s:11:\\\"htmlContent\\\";s:941:\\\"<!DOCTYPE html>\\n<html>\\n<head>\\n    <meta charset=\\\"utf-8\\\">\\n    <meta name=\\\"viewport\\\" content=\\\"width=device-width, initial-scale=1\\\">\\n    <title>Support ticket reply<\\/title>\\n<\\/head>\\n<body style=\\\"font-family: system-ui, sans-serif; line-height: 1.5; color: #111; max-width: 560px; margin: 0 auto; padding: 24px;\\\">\\n    <p style=\\\"margin: 0 0 8px; font-size: 14px; color: #666;\\\">7th Trade Hub<\\/p>\\n    <h1 style=\\\"font-size: 20px; margin: 0 0 12px;\\\">Support ticket reply<\\/h1>\\n            <p style=\\\"margin: 0 0 16px;\\\">Ticket #41 received a user reply.<\\/p>\\n                <p style=\\\"margin: 0 0 24px;\\\">\\n            <a href=\\\"https:\\/\\/7th-tradehub.online\\/admin\\/tickets\\\" style=\\\"display: inline-block; background: #111; color: #fff; text-decoration: none; padding: 10px 16px; border-radius: 6px;\\\">\\n                View details\\n            <\\/a>\\n        <\\/p>\\n        <p style=\\\"margin: 0; font-size: 12px; color: #888;\\\">Hello Support Admin,<\\/p>\\n<\\/body>\\n<\\/html>\\n\\\";s:11:\\\"textContent\\\";N;s:11:\\\"templateKey\\\";s:12:\\\"notification\\\";s:7:\\\"profile\\\";E:54:\\\"App\\\\Services\\\\Communications\\\\Email\\\\EmailProfile:Support\\\";s:7:\\\"replyTo\\\";N;s:2:\\\"cc\\\";a:0:{}s:3:\\\"bcc\\\";a:0:{}s:11:\\\"attachments\\\";a:0:{}s:4:\\\"tags\\\";a:1:{i:0;s:12:\\\"notification\\\";}s:10:\\\"templateId\\\";N;s:6:\\\"params\\\";a:0:{}s:11:\\\"scheduledAt\\\";N;}s:12:\\\"attemptStage\\\";i:1;s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":4:{s:4:\\\"date\\\";s:26:\\\"2026-08-01 01:05:55.926547\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";s:18:\\\"dumpDateProperties\\\";a:2:{s:4:\\\"date\\\";s:26:\\\"2026-08-01 01:05:55.926547\\\";s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}}\",\"batchId\":null},\"createdAt\":1785546055,\"delay\":300}', 0, NULL, 1785546355, 1785546055),
(7, 'default', '{\"uuid\":\"3a0a7553-8ccc-4b0e-8046-95a4a8864660\",\"displayName\":\"App\\\\Jobs\\\\RetryFailedEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":1,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\RetryFailedEmailJob\",\"command\":\"O:28:\\\"App\\\\Jobs\\\\RetryFailedEmailJob\\\":3:{s:5:\\\"email\\\";O:47:\\\"App\\\\Services\\\\Communications\\\\Email\\\\OutgoingEmail\\\":14:{s:2:\\\"to\\\";a:1:{i:0;a:1:{s:5:\\\"email\\\";s:26:\\\"mr.carter.tech07@gmail.com\\\";}}s:7:\\\"subject\\\";s:28:\\\"7th Trade Hub — test email\\\";s:11:\\\"htmlContent\\\";N;s:11:\\\"textContent\\\";s:144:\\\"This is a test email from 7th Trade Hub Admin Settings.\\n\\nIf you received this, your mail configuration is working.\\n\\nSent at: 2026-08-01 02:57:31\\\";s:11:\\\"templateKey\\\";N;s:7:\\\"profile\\\";E:54:\\\"App\\\\Services\\\\Communications\\\\Email\\\\EmailProfile:NoReply\\\";s:7:\\\"replyTo\\\";N;s:2:\\\"cc\\\";a:0:{}s:3:\\\"bcc\\\";a:0:{}s:11:\\\"attachments\\\";a:0:{}s:4:\\\"tags\\\";a:1:{i:0;s:13:\\\"transactional\\\";}s:10:\\\"templateId\\\";N;s:6:\\\"params\\\";a:0:{}s:11:\\\"scheduledAt\\\";N;}s:12:\\\"attemptStage\\\";i:1;s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":4:{s:4:\\\"date\\\";s:26:\\\"2026-08-01 03:02:31.667275\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";s:18:\\\"dumpDateProperties\\\";a:2:{s:4:\\\"date\\\";s:26:\\\"2026-08-01 03:02:31.667275\\\";s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}}\",\"batchId\":null},\"createdAt\":1785553051,\"delay\":300}', 0, NULL, 1785553351, 1785553051);

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kyc_submissions`
--

CREATE TABLE `kyc_submissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `level_requested` tinyint(3) UNSIGNED NOT NULL,
  `level_granted` tinyint(3) UNSIGNED DEFAULT NULL,
  `documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents`)),
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

CREATE TABLE `listings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `marketplace_product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `category` varchar(80) DEFAULT NULL,
  `icon_class` varchar(80) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(32) DEFAULT 'published',
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `listings`
--

INSERT INTO `listings` (`id`, `user_id`, `category_id`, `marketplace_product_id`, `title`, `slug`, `description`, `price`, `category`, `icon_class`, `is_active`, `status`, `featured`, `created_at`, `updated_at`, `deleted_at`) VALUES
(106, 42, 6, 1, '5 years old Facebook', '5-years-old-facebook-Vd722l', 'Let\'s do business', 5000.00, 'facebook', NULL, 0, 'draft', 0, '2026-08-01 03:33:36', '2026-08-02 17:58:07', '2026-08-02 17:58:07');

-- --------------------------------------------------------

--
-- Table structure for table `listing_versions`
--

CREATE TABLE `listing_versions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `version_number` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `listing_versions`
--

INSERT INTO `listing_versions` (`id`, `listing_id`, `version_number`, `title`, `description`, `price`, `images`, `status`, `submitted_at`, `reviewed_by`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(56, 106, 1, '5 years old Facebook', 'Let\'s do business', 5000.00, NULL, 'draft', NULL, NULL, NULL, '2026-08-01 03:33:36', '2026-08-01 03:33:36');

-- --------------------------------------------------------

--
-- Table structure for table `marketplace_products`
--

CREATE TABLE `marketplace_products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `short_description` varchar(500) DEFAULT NULL,
  `hero_title` varchar(255) DEFAULT NULL,
  `hero_subtitle` varchar(500) DEFAULT NULL,
  `benefits` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`benefits`)),
  `faq` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`faq`)),
  `banner_image` varchar(255) DEFAULT NULL,
  `card_image` varchar(255) DEFAULT NULL,
  `banner_media_id` bigint(20) UNSIGNED DEFAULT NULL,
  `card_media_id` bigint(20) UNSIGNED DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `og_title` varchar(255) DEFAULT NULL,
  `og_description` text DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `marketplace_products`
--

INSERT INTO `marketplace_products` (`id`, `category_id`, `name`, `slug`, `sort_order`, `is_active`, `short_description`, `hero_title`, `hero_subtitle`, `benefits`, `faq`, `banner_image`, `card_image`, `banner_media_id`, `card_media_id`, `icon`, `seo_title`, `seo_description`, `og_title`, `og_description`, `og_image`, `created_at`, `updated_at`) VALUES
(1, 6, 'Facebook', 'facebook', 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-23 02:24:41', '2026-07-23 02:24:41'),
(2, 6, 'Twitter / X', 'twitter', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-23 02:24:41', '2026-07-23 02:24:41'),
(3, 6, 'TikTok', 'tiktok', 2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-23 02:24:41', '2026-07-23 02:24:41'),
(4, 6, 'Instagram', 'instagram', 3, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-23 02:24:41', '2026-07-23 02:24:41'),
(5, 6, 'LinkedIn', 'linkedin', 4, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-23 02:24:41', '2026-07-23 02:24:41'),
(6, 6, 'Discord', 'discord', 5, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-23 02:24:41', '2026-07-23 02:24:41'),
(7, 13, 'VPN', 'marketplace-vpn', 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-23 02:24:41', '2026-07-23 02:24:41'),
(8, 13, 'Proxy', 'marketplace-proxy', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-23 02:24:41', '2026-07-23 02:24:41'),
(9, 13, 'RDP', 'rdp', 2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-23 02:24:41', '2026-07-23 02:24:41'),
(10, 13, 'VPS', 'marketplace-vps', 3, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-23 02:24:41', '2026-07-23 02:24:41'),
(11, 13, 'SMTP', 'marketplace-smtp', 4, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-23 02:24:41', '2026-07-23 02:24:41');

-- --------------------------------------------------------

--
-- Table structure for table `media_assets`
--

CREATE TABLE `media_assets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'image',
  `disk` varchar(40) NOT NULL DEFAULT 'public',
  `folder` varchar(32) DEFAULT NULL,
  `collection` varchar(80) DEFAULT NULL,
  `brand_key` varchar(80) DEFAULT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime` varchar(80) NOT NULL,
  `extension` varchar(20) NOT NULL,
  `size_bytes` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `width` int(10) UNSIGNED DEFAULT NULL,
  `height` int(10) UNSIGNED DEFAULT NULL,
  `checksum` varchar(64) DEFAULT NULL,
  `alt` varchar(255) DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `keep_original` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media_assets`
--

INSERT INTO `media_assets` (`id`, `uuid`, `type`, `disk`, `folder`, `collection`, `brand_key`, `original_name`, `mime`, `extension`, `size_bytes`, `width`, `height`, `checksum`, `alt`, `tags`, `uploaded_by`, `keep_original`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'a3944a4d-3c8c-42f9-b9f9-52a906dbe869', 'image', 'public', '2026/07', 'library', NULL, 'Network Services_1.jpg', 'image/webp', 'webp', 97164, 1800, 1200, '15a81eba8dbaab782224d2a03a0ef12a5706ba26fd0eb0e2773b0f22c2fb2c9d', NULL, NULL, 1, 0, '2026-07-22 18:15:55', '2026-07-22 18:15:55', NULL),
(2, 'a6176433-4419-43e0-9538-b6c5c9188668', 'image', 'public', '2026/07', 'library', NULL, 'ai-powered-device-concept copy.jpg', 'image/webp', 'webp', 46008, 1800, 1230, '5024dabb4234872c0a12301f1a5a8535ddcd59bb64ba11c17a2dbe2789787e4f', NULL, NULL, 1, 0, '2026-07-22 18:28:22', '2026-07-22 18:28:22', NULL),
(3, 'ec04095f-65e5-4881-aef6-a93ad9137a3a', 'image', 'public', '2026/07', 'library', NULL, 'Business_Documents.jpg', 'image/webp', 'webp', 165316, 1800, 1173, 'b2b781b642781fbb5e45c31696e188615a35337ca623eecb79f84c289f8f398e', NULL, NULL, 1, 0, '2026-07-22 18:28:22', '2026-07-22 18:28:22', NULL),
(4, '90c2af36-83b7-4954-ab05-a857a5c9066b', 'image', 'public', '2026/07', 'library', NULL, 'Communication_1.jpg', 'image/webp', 'webp', 61886, 1800, 1250, '9ccea1c52bd39fc18cef7cfa225571216f2cc10cfaf6ee4aa7bb1adc46185b77', NULL, NULL, 1, 0, '2026-07-22 18:28:23', '2026-07-22 18:28:23', NULL),
(5, '2eacb179-b624-48e8-9216-5bd35233bb0d', 'image', 'public', '2026/07', 'library', NULL, 'crytpo_exchange.jpg', 'image/webp', 'webp', 28954, 1800, 1200, 'cd7f4c85832aa42740959b1e3ba99b5f4e2bf6c7f599617d5f02af05d3cb65d0', NULL, NULL, 1, 0, '2026-07-22 18:28:23', '2026-07-22 18:28:23', NULL),
(6, '927379fe-1f83-4972-8135-c666753b7d50', 'image', 'public', '2026/07', 'library', NULL, 'employee-working-with-document copy.jpg', 'image/webp', 'webp', 22430, 1800, 1232, 'a32bf4c9640ba48bab05388be734e201c784b5b3100c77874d139d5b41808895', NULL, NULL, 1, 0, '2026-07-22 18:28:24', '2026-07-22 18:28:24', NULL),
(7, '0a5a9694-3f6b-405a-be77-7b7f000a5940', 'image', 'public', '2026/07', 'library', NULL, 'favicons.png', 'image/webp', 'webp', 8656, 450, 359, '2abdbf400051e7f7dc6f5e7ac5461445ee027e7e8cecb301245fcc49178b0540', NULL, NULL, 1, 0, '2026-07-22 18:28:24', '2026-07-22 18:28:24', NULL),
(8, '5017654e-6281-4b90-9566-b015110d6f21', 'image', 'public', '2026/07', 'library', NULL, 'flat-lay-real-estate-concept.jpg', 'image/webp', 'webp', 89854, 1800, 1201, 'e69bdade1f9e762827986a95bd2574a44bb53b46012d967c84dfc6ea35551f5a', NULL, NULL, 1, 0, '2026-07-22 18:28:24', '2026-07-22 18:28:24', NULL),
(9, '57020811-6992-4c0e-999f-d388cc3ab83a', 'image', 'public', '2026/07', 'library', NULL, 'helpcenter.jpg', 'image/webp', 'webp', 42434, 1800, 1013, '4611c9363ace64603f939d828d7318c7676b26dd208a74eec0a5756d0d90bec0', NULL, NULL, 1, 0, '2026-07-22 18:28:25', '2026-07-22 18:28:25', NULL),
(10, '57ffd30c-b6e8-40b6-8ded-ec8421fa3f35', 'image', 'public', '2026/07', 'library', NULL, 'homeslider1.jpg', 'image/webp', 'webp', 39558, 1800, 1179, '83c528c0bd7bae6d134be3d663f7317f7d05a182577cb986c3c43ce967bea542', NULL, NULL, 1, 0, '2026-07-22 18:28:25', '2026-07-22 18:28:25', NULL),
(11, '468086ea-f1c7-4578-8588-d2dd60a82f9c', 'image', 'public', '2026/07', 'library', NULL, 'homeslider2.jpg', 'image/webp', 'webp', 82876, 1800, 1200, 'c1da38508e086b9791ddf5f53697ed885816ffa999ce62f1453319091bc3618f', NULL, NULL, 1, 0, '2026-07-22 18:28:26', '2026-07-22 18:28:26', NULL),
(12, '26487510-06e8-4c65-89ce-67a0833c1c3c', 'image', 'public', '2026/07', 'library', NULL, 'homeslider3.jpg', 'image/webp', 'webp', 64718, 1500, 1000, 'cc94bf2d66cdb30fc847908c452dd64d265b1eb41dbc38c4659c1d97e0d03195', NULL, NULL, 1, 0, '2026-07-22 18:28:26', '2026-07-22 18:28:26', NULL),
(13, 'e27f42d5-5054-4943-b127-7bed1d63450f', 'image', 'public', '2026/07', 'library', NULL, 'Image_ro410gro410gro41.png', 'image/webp', 'webp', 25442, 800, 339, '01d6a39fb46ef8905e5465c9deda3af33de7544db486310fadf3eb6ac0968572', NULL, NULL, 1, 0, '2026-07-22 18:28:26', '2026-07-22 18:28:26', NULL),
(14, 'b34b9e58-6488-45ab-bd68-4e9286926219', 'image', 'public', '2026/07', 'library', NULL, 'market_place.jpg', 'image/webp', 'webp', 67384, 1800, 1200, '16ca723ccaaec02e1c91f44cfea4466ab9b672e696baf19b7940a859447b4a11', NULL, NULL, 1, 0, '2026-07-22 18:28:27', '2026-07-22 18:28:27', NULL),
(15, 'e4443f31-837e-41e4-b27d-382cb6a1b540', 'image', 'public', '2026/07', 'library', NULL, 'originla_logo.png', 'image/webp', 'webp', 12066, 1536, 1024, '4e67e66326838776329728009d5dc0c66e2ac1983621b6ec88d0dbb89717b6fd', NULL, NULL, 1, 0, '2026-07-22 18:28:27', '2026-07-22 18:28:27', NULL),
(16, 'd29be162-a0f7-4756-8317-c618468ce8c3', 'image', 'public', '2026/07', 'library', NULL, 'services_1.jpg', 'image/webp', 'webp', 57420, 1800, 1200, 'dfa7b9d34c0a197a36168fc210647fa498d6bc13d6fa31bc5eed7ec2b474242b', NULL, NULL, 1, 0, '2026-07-22 18:28:27', '2026-07-22 18:28:27', NULL),
(17, '018c2966-5c38-408a-ac01-0907dacccd7e', 'image', 'public', '2026/07', 'library', NULL, 'Social_Media.jpg', 'image/webp', 'webp', 59542, 1800, 1200, '2e86ac4217e5c6496ed860d9ab1239f381f2f31721b76d0a53b8a72639548c36', NULL, NULL, 1, 0, '2026-07-22 18:28:28', '2026-07-22 18:28:28', NULL),
(18, 'fcaf6c71-a2c0-46c0-b187-7559ef0ac3f1', 'image', 'public', '2026/07', 'library', NULL, 'Website_Services.jpg', 'image/webp', 'webp', 62364, 1800, 1200, 'ca990d3a6beaea90ffe9eb3cc01bbf126f339664af1bd6cef807f1e6de99d69a', NULL, NULL, 1, 0, '2026-07-22 18:28:28', '2026-07-22 18:28:28', NULL),
(19, 'bce4dfcb-e843-45db-a282-6790fafc5189', 'image', 'public', '2026/07', 'library', NULL, 'white_originla_logo.png', 'image/webp', 'webp', 13758, 899, 359, '1be9224172c0f679117b97ade2140f365fdcb90500487de60e3859c4870b19d8', NULL, NULL, 1, 0, '2026-07-22 18:28:28', '2026-07-22 18:28:28', NULL),
(20, 'd34491b4-34b8-48d7-93c7-0c1eb520f70e', 'image', 'public', '2026/08', 'library', NULL, 'white_originla_logoblack.png', 'image/webp', 'webp', 15448, 899, 359, '796823710e74d3654d58fd00f9f4640c41a72e476cec8af74bb035b2796fd306', NULL, NULL, 1, 0, '2026-08-01 02:53:53', '2026-08-01 02:53:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `media_usages`
--

CREATE TABLE `media_usages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `media_asset_id` bigint(20) UNSIGNED NOT NULL,
  `usable_type` varchar(255) NOT NULL,
  `usable_id` bigint(20) UNSIGNED NOT NULL,
  `field` varchar(80) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media_usages`
--

INSERT INTO `media_usages` (`id`, `media_asset_id`, `usable_type`, `usable_id`, `field`, `created_at`, `updated_at`) VALUES
(9, 17, 'App\\Models\\Category', 6, 'card', '2026-08-01 23:59:50', '2026-08-01 23:59:50'),
(10, 17, 'App\\Models\\Category', 6, 'banner', '2026-08-01 23:59:50', '2026-08-01 23:59:50'),
(11, 1, 'App\\Models\\Category', 13, 'card', '2026-08-02 00:00:17', '2026-08-02 00:00:17'),
(12, 1, 'App\\Models\\Category', 13, 'banner', '2026-08-02 00:00:17', '2026-08-02 00:00:17'),
(17, 14, 'App\\Models\\ServiceCategory', 2, 'card', '2026-08-02 00:33:29', '2026-08-02 00:33:29'),
(18, 14, 'App\\Models\\ServiceCategory', 2, 'banner', '2026-08-02 00:33:29', '2026-08-02 00:33:29'),
(19, 14, 'App\\Models\\ServiceCategory', 6, 'card', '2026-08-02 00:34:08', '2026-08-02 00:34:08'),
(20, 14, 'App\\Models\\ServiceCategory', 6, 'banner', '2026-08-02 00:34:08', '2026-08-02 00:34:08'),
(27, 17, 'App\\Models\\ServiceCategory', 3, 'card', '2026-08-02 00:36:19', '2026-08-02 00:36:19'),
(28, 17, 'App\\Models\\ServiceCategory', 3, 'banner', '2026-08-02 00:36:19', '2026-08-02 00:36:19'),
(29, 18, 'App\\Models\\ServiceCategory', 1, 'card', '2026-08-02 00:36:54', '2026-08-02 00:36:54'),
(30, 18, 'App\\Models\\ServiceCategory', 1, 'banner', '2026-08-02 00:36:54', '2026-08-02 00:36:54'),
(31, 11, 'App\\Models\\ServiceCategory', 4, 'card', '2026-08-02 00:37:11', '2026-08-02 00:37:11'),
(32, 11, 'App\\Models\\ServiceCategory', 4, 'banner', '2026-08-02 00:37:11', '2026-08-02 00:37:11'),
(35, 8, 'App\\Models\\ServiceCategory', 5, 'card', '2026-08-02 00:38:46', '2026-08-02 00:38:46'),
(36, 8, 'App\\Models\\ServiceCategory', 5, 'banner', '2026-08-02 00:38:46', '2026-08-02 00:38:46'),
(45, 7, 'site_branding', 1, 'favicon', '2026-08-02 13:03:48', '2026-08-02 13:03:48'),
(46, 20, 'site_branding', 1, 'logo_light', '2026-08-02 13:03:48', '2026-08-02 13:03:48'),
(47, 19, 'site_branding', 1, 'logo_dark', '2026-08-02 13:03:48', '2026-08-02 13:03:48');

-- --------------------------------------------------------

--
-- Table structure for table `media_variants`
--

CREATE TABLE `media_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `media_asset_id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(40) NOT NULL,
  `path` varchar(255) NOT NULL,
  `width` int(10) UNSIGNED DEFAULT NULL,
  `height` int(10) UNSIGNED DEFAULT NULL,
  `size_bytes` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `mime` varchar(80) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media_variants`
--

INSERT INTO `media_variants` (`id`, `media_asset_id`, `key`, `path`, `width`, `height`, `size_bytes`, `mime`, `created_at`, `updated_at`) VALUES
(1, 1, 'large', 'media/2026/07/01KY5GN5D5QG4YH2PA7PGPNX9G-large.webp', 1800, 1200, 188468, 'image/webp', '2026-07-22 18:15:55', '2026-07-22 18:15:55'),
(2, 1, 'medium', 'media/2026/07/01KY5GN5D5QG4YH2PA7PGPNX9G-medium.webp', 1200, 800, 97164, 'image/webp', '2026-07-22 18:15:55', '2026-07-22 18:15:55'),
(3, 1, 'small', 'media/2026/07/01KY5GN5D5QG4YH2PA7PGPNX9G-small.webp', 640, 427, 39020, 'image/webp', '2026-07-22 18:15:55', '2026-07-22 18:15:55'),
(4, 1, 'thumbnail', 'media/2026/07/01KY5GN5D5QG4YH2PA7PGPNX9G-thumbnail.webp', 300, 300, 16448, 'image/webp', '2026-07-22 18:15:55', '2026-07-22 18:15:55'),
(5, 2, 'large', 'media/2026/07/01KY5HBZ8YMGJ41D7XGA6SS1BY-large.webp', 1800, 1230, 88320, 'image/webp', '2026-07-22 18:28:22', '2026-07-22 18:28:22'),
(6, 2, 'medium', 'media/2026/07/01KY5HBZ8YMGJ41D7XGA6SS1BY-medium.webp', 1200, 820, 46008, 'image/webp', '2026-07-22 18:28:22', '2026-07-22 18:28:22'),
(7, 2, 'small', 'media/2026/07/01KY5HBZ8YMGJ41D7XGA6SS1BY-small.webp', 640, 437, 19124, 'image/webp', '2026-07-22 18:28:22', '2026-07-22 18:28:22'),
(8, 2, 'thumbnail', 'media/2026/07/01KY5HBZ8YMGJ41D7XGA6SS1BY-thumbnail.webp', 300, 300, 10002, 'image/webp', '2026-07-22 18:28:22', '2026-07-22 18:28:22'),
(9, 3, 'large', 'media/2026/07/01KY5HBZRC6BH7371EEV33PGT4-large.webp', 1800, 1173, 327308, 'image/webp', '2026-07-22 18:28:22', '2026-07-22 18:28:22'),
(10, 3, 'medium', 'media/2026/07/01KY5HBZRC6BH7371EEV33PGT4-medium.webp', 1200, 782, 165316, 'image/webp', '2026-07-22 18:28:22', '2026-07-22 18:28:22'),
(11, 3, 'small', 'media/2026/07/01KY5HBZRC6BH7371EEV33PGT4-small.webp', 640, 417, 58162, 'image/webp', '2026-07-22 18:28:22', '2026-07-22 18:28:22'),
(12, 3, 'thumbnail', 'media/2026/07/01KY5HBZRC6BH7371EEV33PGT4-thumbnail.webp', 300, 300, 21058, 'image/webp', '2026-07-22 18:28:22', '2026-07-22 18:28:22'),
(13, 4, 'large', 'media/2026/07/01KY5HC07HJ3EB2C5E1BAYY33H-large.webp', 1800, 1250, 135630, 'image/webp', '2026-07-22 18:28:23', '2026-07-22 18:28:23'),
(14, 4, 'medium', 'media/2026/07/01KY5HC07HJ3EB2C5E1BAYY33H-medium.webp', 1200, 833, 61886, 'image/webp', '2026-07-22 18:28:23', '2026-07-22 18:28:23'),
(15, 4, 'small', 'media/2026/07/01KY5HC07HJ3EB2C5E1BAYY33H-small.webp', 640, 444, 20912, 'image/webp', '2026-07-22 18:28:23', '2026-07-22 18:28:23'),
(16, 4, 'thumbnail', 'media/2026/07/01KY5HC07HJ3EB2C5E1BAYY33H-thumbnail.webp', 300, 300, 10240, 'image/webp', '2026-07-22 18:28:23', '2026-07-22 18:28:23'),
(17, 5, 'large', 'media/2026/07/01KY5HC0NNMVP4TF6J0DMN3W27-large.webp', 1800, 1200, 57650, 'image/webp', '2026-07-22 18:28:23', '2026-07-22 18:28:23'),
(18, 5, 'medium', 'media/2026/07/01KY5HC0NNMVP4TF6J0DMN3W27-medium.webp', 1200, 800, 28954, 'image/webp', '2026-07-22 18:28:23', '2026-07-22 18:28:23'),
(19, 5, 'small', 'media/2026/07/01KY5HC0NNMVP4TF6J0DMN3W27-small.webp', 640, 427, 11070, 'image/webp', '2026-07-22 18:28:23', '2026-07-22 18:28:23'),
(20, 5, 'thumbnail', 'media/2026/07/01KY5HC0NNMVP4TF6J0DMN3W27-thumbnail.webp', 300, 300, 5552, 'image/webp', '2026-07-22 18:28:23', '2026-07-22 18:28:23'),
(21, 6, 'large', 'media/2026/07/01KY5HC12NDTVPAK6XCCF86H2X-large.webp', 1800, 1232, 39510, 'image/webp', '2026-07-22 18:28:24', '2026-07-22 18:28:24'),
(22, 6, 'medium', 'media/2026/07/01KY5HC12NDTVPAK6XCCF86H2X-medium.webp', 1200, 821, 22430, 'image/webp', '2026-07-22 18:28:24', '2026-07-22 18:28:24'),
(23, 6, 'small', 'media/2026/07/01KY5HC12NDTVPAK6XCCF86H2X-small.webp', 640, 438, 10188, 'image/webp', '2026-07-22 18:28:24', '2026-07-22 18:28:24'),
(24, 6, 'thumbnail', 'media/2026/07/01KY5HC12NDTVPAK6XCCF86H2X-thumbnail.webp', 300, 300, 4766, 'image/webp', '2026-07-22 18:28:24', '2026-07-22 18:28:24'),
(25, 7, 'large', 'media/2026/07/01KY5HC1FX98J14266WJTRB8EW-large.webp', 450, 359, 8656, 'image/webp', '2026-07-22 18:28:24', '2026-07-22 18:28:24'),
(26, 7, 'medium', 'media/2026/07/01KY5HC1FX98J14266WJTRB8EW-medium.webp', 450, 359, 8656, 'image/webp', '2026-07-22 18:28:24', '2026-07-22 18:28:24'),
(27, 7, 'small', 'media/2026/07/01KY5HC1FX98J14266WJTRB8EW-small.webp', 450, 359, 8656, 'image/webp', '2026-07-22 18:28:24', '2026-07-22 18:28:24'),
(28, 7, 'thumbnail', 'media/2026/07/01KY5HC1FX98J14266WJTRB8EW-thumbnail.webp', 300, 300, 7564, 'image/webp', '2026-07-22 18:28:24', '2026-07-22 18:28:24'),
(29, 8, 'large', 'media/2026/07/01KY5HC1JYJBJHEDE24XBVWVBE-large.webp', 1800, 1201, 182668, 'image/webp', '2026-07-22 18:28:24', '2026-07-22 18:28:24'),
(30, 8, 'medium', 'media/2026/07/01KY5HC1JYJBJHEDE24XBVWVBE-medium.webp', 1200, 801, 89854, 'image/webp', '2026-07-22 18:28:24', '2026-07-22 18:28:24'),
(31, 8, 'small', 'media/2026/07/01KY5HC1JYJBJHEDE24XBVWVBE-small.webp', 640, 427, 34054, 'image/webp', '2026-07-22 18:28:24', '2026-07-22 18:28:24'),
(32, 8, 'thumbnail', 'media/2026/07/01KY5HC1JYJBJHEDE24XBVWVBE-thumbnail.webp', 300, 300, 10412, 'image/webp', '2026-07-22 18:28:24', '2026-07-22 18:28:24'),
(33, 9, 'large', 'media/2026/07/01KY5HC21M7MZMBSGAV9JN4GDK-large.webp', 1800, 1013, 77630, 'image/webp', '2026-07-22 18:28:25', '2026-07-22 18:28:25'),
(34, 9, 'medium', 'media/2026/07/01KY5HC21M7MZMBSGAV9JN4GDK-medium.webp', 1200, 675, 42434, 'image/webp', '2026-07-22 18:28:25', '2026-07-22 18:28:25'),
(35, 9, 'small', 'media/2026/07/01KY5HC21M7MZMBSGAV9JN4GDK-small.webp', 640, 360, 18300, 'image/webp', '2026-07-22 18:28:25', '2026-07-22 18:28:25'),
(36, 9, 'thumbnail', 'media/2026/07/01KY5HC21M7MZMBSGAV9JN4GDK-thumbnail.webp', 300, 300, 7890, 'image/webp', '2026-07-22 18:28:25', '2026-07-22 18:28:25'),
(37, 10, 'large', 'media/2026/07/01KY5HC2E1M3AVWZTH60GFYNDT-large.webp', 1800, 1179, 72980, 'image/webp', '2026-07-22 18:28:25', '2026-07-22 18:28:25'),
(38, 10, 'medium', 'media/2026/07/01KY5HC2E1M3AVWZTH60GFYNDT-medium.webp', 1200, 786, 39558, 'image/webp', '2026-07-22 18:28:25', '2026-07-22 18:28:25'),
(39, 10, 'small', 'media/2026/07/01KY5HC2E1M3AVWZTH60GFYNDT-small.webp', 640, 419, 17920, 'image/webp', '2026-07-22 18:28:25', '2026-07-22 18:28:25'),
(40, 10, 'thumbnail', 'media/2026/07/01KY5HC2E1M3AVWZTH60GFYNDT-thumbnail.webp', 300, 300, 9668, 'image/webp', '2026-07-22 18:28:25', '2026-07-22 18:28:25'),
(41, 11, 'large', 'media/2026/07/01KY5HC2V6D6DY3APNWHSQ3REA-large.webp', 1800, 1200, 161020, 'image/webp', '2026-07-22 18:28:26', '2026-07-22 18:28:26'),
(42, 11, 'medium', 'media/2026/07/01KY5HC2V6D6DY3APNWHSQ3REA-medium.webp', 1200, 800, 82876, 'image/webp', '2026-07-22 18:28:26', '2026-07-22 18:28:26'),
(43, 11, 'small', 'media/2026/07/01KY5HC2V6D6DY3APNWHSQ3REA-small.webp', 640, 427, 34462, 'image/webp', '2026-07-22 18:28:26', '2026-07-22 18:28:26'),
(44, 11, 'thumbnail', 'media/2026/07/01KY5HC2V6D6DY3APNWHSQ3REA-thumbnail.webp', 300, 300, 15960, 'image/webp', '2026-07-22 18:28:26', '2026-07-22 18:28:26'),
(45, 12, 'large', 'media/2026/07/01KY5HC39KE586SYK6KXP1NM2T-large.webp', 1500, 1000, 96776, 'image/webp', '2026-07-22 18:28:26', '2026-07-22 18:28:26'),
(46, 12, 'medium', 'media/2026/07/01KY5HC39KE586SYK6KXP1NM2T-medium.webp', 1200, 800, 64718, 'image/webp', '2026-07-22 18:28:26', '2026-07-22 18:28:26'),
(47, 12, 'small', 'media/2026/07/01KY5HC39KE586SYK6KXP1NM2T-small.webp', 640, 427, 28556, 'image/webp', '2026-07-22 18:28:26', '2026-07-22 18:28:26'),
(48, 12, 'thumbnail', 'media/2026/07/01KY5HC39KE586SYK6KXP1NM2T-thumbnail.webp', 300, 300, 12840, 'image/webp', '2026-07-22 18:28:26', '2026-07-22 18:28:26'),
(49, 13, 'large', 'media/2026/07/01KY5HC3N3NS661Z2CEWTDQ7XE-large.webp', 800, 339, 25442, 'image/webp', '2026-07-22 18:28:26', '2026-07-22 18:28:26'),
(50, 13, 'medium', 'media/2026/07/01KY5HC3N3NS661Z2CEWTDQ7XE-medium.webp', 800, 339, 25442, 'image/webp', '2026-07-22 18:28:26', '2026-07-22 18:28:26'),
(51, 13, 'small', 'media/2026/07/01KY5HC3N3NS661Z2CEWTDQ7XE-small.webp', 640, 271, 15058, 'image/webp', '2026-07-22 18:28:26', '2026-07-22 18:28:26'),
(52, 13, 'thumbnail', 'media/2026/07/01KY5HC3N3NS661Z2CEWTDQ7XE-thumbnail.webp', 300, 300, 6102, 'image/webp', '2026-07-22 18:28:26', '2026-07-22 18:28:26'),
(53, 14, 'large', 'media/2026/07/01KY5HC3S3ADKH48XJQT3GDDEX-large.webp', 1800, 1200, 126550, 'image/webp', '2026-07-22 18:28:27', '2026-07-22 18:28:27'),
(54, 14, 'medium', 'media/2026/07/01KY5HC3S3ADKH48XJQT3GDDEX-medium.webp', 1200, 800, 67384, 'image/webp', '2026-07-22 18:28:27', '2026-07-22 18:28:27'),
(55, 14, 'small', 'media/2026/07/01KY5HC3S3ADKH48XJQT3GDDEX-small.webp', 640, 427, 30664, 'image/webp', '2026-07-22 18:28:27', '2026-07-22 18:28:27'),
(56, 14, 'thumbnail', 'media/2026/07/01KY5HC3S3ADKH48XJQT3GDDEX-thumbnail.webp', 300, 300, 13446, 'image/webp', '2026-07-22 18:28:27', '2026-07-22 18:28:27'),
(57, 15, 'large', 'media/2026/07/01KY5HC48Q5TTBF0P7BFXH4053-large.webp', 1536, 1024, 17518, 'image/webp', '2026-07-22 18:28:27', '2026-07-22 18:28:27'),
(58, 15, 'medium', 'media/2026/07/01KY5HC48Q5TTBF0P7BFXH4053-medium.webp', 1200, 800, 12066, 'image/webp', '2026-07-22 18:28:27', '2026-07-22 18:28:27'),
(59, 15, 'small', 'media/2026/07/01KY5HC48Q5TTBF0P7BFXH4053-small.webp', 640, 427, 6212, 'image/webp', '2026-07-22 18:28:27', '2026-07-22 18:28:27'),
(60, 15, 'thumbnail', 'media/2026/07/01KY5HC48Q5TTBF0P7BFXH4053-thumbnail.webp', 300, 300, 3996, 'image/webp', '2026-07-22 18:28:27', '2026-07-22 18:28:27'),
(61, 16, 'large', 'media/2026/07/01KY5HC4Q791868D90FSGT5K7D-large.webp', 1800, 1200, 131104, 'image/webp', '2026-07-22 18:28:27', '2026-07-22 18:28:27'),
(62, 16, 'medium', 'media/2026/07/01KY5HC4Q791868D90FSGT5K7D-medium.webp', 1200, 800, 57420, 'image/webp', '2026-07-22 18:28:27', '2026-07-22 18:28:27'),
(63, 16, 'small', 'media/2026/07/01KY5HC4Q791868D90FSGT5K7D-small.webp', 640, 427, 19900, 'image/webp', '2026-07-22 18:28:27', '2026-07-22 18:28:27'),
(64, 16, 'thumbnail', 'media/2026/07/01KY5HC4Q791868D90FSGT5K7D-thumbnail.webp', 300, 300, 9894, 'image/webp', '2026-07-22 18:28:27', '2026-07-22 18:28:27'),
(65, 17, 'large', 'media/2026/07/01KY5HC54YZAG4WZA2850D3MZM-large.webp', 1800, 1200, 100422, 'image/webp', '2026-07-22 18:28:28', '2026-07-22 18:28:28'),
(66, 17, 'medium', 'media/2026/07/01KY5HC54YZAG4WZA2850D3MZM-medium.webp', 1200, 800, 59542, 'image/webp', '2026-07-22 18:28:28', '2026-07-22 18:28:28'),
(67, 17, 'small', 'media/2026/07/01KY5HC54YZAG4WZA2850D3MZM-small.webp', 640, 427, 28706, 'image/webp', '2026-07-22 18:28:28', '2026-07-22 18:28:28'),
(68, 17, 'thumbnail', 'media/2026/07/01KY5HC54YZAG4WZA2850D3MZM-thumbnail.webp', 300, 300, 14230, 'image/webp', '2026-07-22 18:28:28', '2026-07-22 18:28:28'),
(69, 18, 'large', 'media/2026/07/01KY5HC5JNDDFZTGW6E825E9VA-large.webp', 1800, 1200, 127752, 'image/webp', '2026-07-22 18:28:28', '2026-07-22 18:28:28'),
(70, 18, 'medium', 'media/2026/07/01KY5HC5JNDDFZTGW6E825E9VA-medium.webp', 1200, 800, 62364, 'image/webp', '2026-07-22 18:28:28', '2026-07-22 18:28:28'),
(71, 18, 'small', 'media/2026/07/01KY5HC5JNDDFZTGW6E825E9VA-small.webp', 640, 427, 26108, 'image/webp', '2026-07-22 18:28:28', '2026-07-22 18:28:28'),
(72, 18, 'thumbnail', 'media/2026/07/01KY5HC5JNDDFZTGW6E825E9VA-thumbnail.webp', 300, 300, 14432, 'image/webp', '2026-07-22 18:28:28', '2026-07-22 18:28:28'),
(73, 19, 'large', 'media/2026/07/01KY5HC5ZZQBDJT6MTH3MPAEB7-large.webp', 899, 359, 13758, 'image/webp', '2026-07-22 18:28:28', '2026-07-22 18:28:28'),
(74, 19, 'medium', 'media/2026/07/01KY5HC5ZZQBDJT6MTH3MPAEB7-medium.webp', 899, 359, 13758, 'image/webp', '2026-07-22 18:28:28', '2026-07-22 18:28:28'),
(75, 19, 'small', 'media/2026/07/01KY5HC5ZZQBDJT6MTH3MPAEB7-small.webp', 640, 256, 10576, 'image/webp', '2026-07-22 18:28:28', '2026-07-22 18:28:28'),
(76, 19, 'thumbnail', 'media/2026/07/01KY5HC5ZZQBDJT6MTH3MPAEB7-thumbnail.webp', 300, 300, 6176, 'image/webp', '2026-07-22 18:28:28', '2026-07-22 18:28:28'),
(77, 20, 'large', 'media/2026/08/01KYXKW32M8JGT1G9AHKF6W6H2-large.webp', 899, 359, 15448, 'image/webp', '2026-08-01 02:53:53', '2026-08-01 02:53:53'),
(78, 20, 'medium', 'media/2026/08/01KYXKW32M8JGT1G9AHKF6W6H2-medium.webp', 899, 359, 15448, 'image/webp', '2026-08-01 02:53:53', '2026-08-01 02:53:53'),
(79, 20, 'small', 'media/2026/08/01KYXKW32M8JGT1G9AHKF6W6H2-small.webp', 640, 256, 11644, 'image/webp', '2026-08-01 02:53:53', '2026-08-01 02:53:53'),
(80, 20, 'thumbnail', 'media/2026/08/01KYXKW32M8JGT1G9AHKF6W6H2-thumbnail.webp', 300, 300, 7032, 'image/webp', '2026-08-01 02:53:53', '2026-08-01 02:53:53');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `from_user_id` bigint(20) UNSIGNED NOT NULL,
  `to_user_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `folder` varchar(20) NOT NULL DEFAULT 'inbox',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_07_17_000001_create_two_catalog_phase1_tables', 1),
(2, '2026_07_18_000001_phase11_hardening', 2),
(3, '2026_07_18_120000_catalog_page_content_fields', 3),
(4, '0001_01_01_000000_create_users_table', 1),
(5, '0001_01_01_000001_create_cache_table', 1),
(6, '0001_01_01_000002_create_jobs_table', 1),
(7, '2025_03_16_000000_add_username_to_users_table', 1),
(8, '2025_03_16_000001_create_email_verification_codes_table', 1),
(9, '2025_03_16_000002_create_permission_tables', 1),
(10, '2025_03_17_000001_create_wallets_table', 1),
(11, '2025_03_17_000002_create_listings_table', 1),
(12, '2025_03_17_000003_create_transactions_table', 1),
(13, '2025_03_17_000004_create_orders_table', 1),
(14, '2025_03_17_000005_create_support_tickets_table', 1),
(15, '2025_03_17_000006_seed_default_roles', 1),
(16, '2026_07_12_000001_add_user_profile_and_kyc_fields', 1),
(17, '2026_07_12_000002_refactor_wallets_to_ngn', 1),
(18, '2026_07_12_000003_create_wallet_fundings_table', 1),
(19, '2026_07_12_000004_create_crypto_sell_requests_table', 1),
(20, '2026_07_12_000005_create_escrows_table', 1),
(21, '2026_07_12_000006_create_kyc_submissions_table', 1),
(22, '2026_07_12_000007_create_withdrawals_table', 1),
(23, '2026_07_12_000008_create_categories_and_extend_listings', 1),
(24, '2026_07_12_000009_create_listing_versions_table', 1),
(25, '2026_07_12_000010_extend_transactions_table', 1),
(26, '2026_07_12_000011_extend_support_tickets_and_replies', 1),
(27, '2026_07_12_000012_create_messages_audit_settings', 1),
(28, '2026_07_12_000013_create_marketplace_features_tables', 1),
(29, '2026_07_12_000014_audit_remediation', 1),
(30, '2026_07_12_000015_create_gateway_operations_table', 1),
(31, '2026_07_21_000001_update_service_group_heroes_and_remove_escrow_products', 4),
(32, '2026_07_21_140000_add_theme_preference_to_users_table', 5),
(33, '2026_07_21_220000_add_user_lifecycle_columns', 6),
(34, '2026_07_22_000001_create_catalog_hierarchy_tables', 7),
(35, '2026_07_22_000002_cleanup_legacy_platform_categories', 7),
(36, '2026_07_22_120000_create_media_library_tables', 8),
(37, '2026_07_22_120001_add_media_ids_to_catalog_tables', 8),
(38, '2026_07_22_120002_add_media_ids_to_catalog_page_contents', 8),
(39, '2026_07_22_180000_media_scalability_columns', 8),
(40, '2026_07_23_000001_marketplace_products_and_cms', 9),
(41, '2026_07_23_000002_remap_listing_category_ids_to_parents', 9),
(42, '2026_07_23_000003_add_listings_browse_indexes', 9),
(43, '2026_07_23_100001_create_user_activity_and_product_metrics', 10),
(44, '2026_07_23_100002_create_analytics_platform_tables', 10),
(45, '2026_07_23_120001_enrich_audit_logs', 10),
(46, '2026_07_23_120002_normalize_support_ticket_statuses', 10),
(47, '2026_07_23_120003_add_users_search_fulltext', 10),
(48, '2026_07_23_180001_add_audit_log_lookup_indexes', 10),
(49, '2026_07_23_230001_create_demo_batch_tables', 11),
(50, '2026_08_01_000001_create_communications_and_branding_tables', 12),
(51, '2026_08_01_010000_add_icon_media_id_to_social_links', 13),
(52, '2026_07_31_000010_ensure_listings_deleted_at_column', 14),
(53, '2026_08_01_021700_create_support_attachments_table', 14),
(54, '2026_08_01_040000_create_email_delivery_attempts_table', 15),
(55, '2026_08_01_150000_create_user_auth_providers_and_google_identity', 15),
(56, '2026_08_01_180000_wallet_holds_and_payment_rails', 16),
(57, '2026_08_01_181000_create_security_verification_codes_table', 16),
(58, '2026_08_02_000100_create_tracking_scripts_and_seed_providers', 17),
(59, '2026_08_02_000001_add_coingecko_fields_to_exchange_rates', 18),
(60, '2026_08_02_000200_create_crypto_otc_v1_tables', 18),
(61, '2026_08_02_000300_smart_wallet_pool_fingerprints', 18),
(62, '2026_08_02_000400_fingerprint_precision_and_usdc_seed', 18),
(63, '2026_08_02_000500_live_treasury_balances', 19),
(64, '2026_08_02_000600_exchange_rate_allowed_network_ids', 20),
(65, '2026_08_02_000700_add_spread_ngn_to_exchange_rates', 21),
(66, '2026_08_02_000800_add_tracking_code_to_crypto_sell_requests', 22),
(67, '2026_08_03_000100_unify_otc_network_ids', 23);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_permissions`
--

INSERT INTO `model_has_permissions` (`permission_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 13),
(2, 'App\\Models\\User', 15),
(2, 'App\\Models\\User', 16),
(2, 'App\\Models\\User', 42);

-- --------------------------------------------------------

--
-- Table structure for table `monitoring_heartbeats`
--

CREATE TABLE `monitoring_heartbeats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(80) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `source` varchar(20) NOT NULL DEFAULT 'marketplace',
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `listing_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference` varchar(32) NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `total_amount` decimal(18,2) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `idempotency_key` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `item_type` varchar(40) NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `unit_price` decimal(18,2) NOT NULL,
  `line_total` decimal(18,2) NOT NULL,
  `platform_product_variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otc_pricing_settings`
--

CREATE TABLE `otc_pricing_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mode` varchar(40) NOT NULL DEFAULT 'live_minus_spread',
  `market_provider` varchar(40) NOT NULL DEFAULT 'manual_reference',
  `market_rate_ngn` decimal(18,4) DEFAULT NULL,
  `cached_market_rate_ngn` decimal(18,4) DEFAULT NULL,
  `spread_ngn` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `manual_customer_rate_ngn` decimal(18,4) DEFAULT NULL,
  `tolerance_percent` decimal(8,4) NOT NULL DEFAULT 0.5000,
  `quote_ttl_minutes` smallint(5) UNSIGNED NOT NULL DEFAULT 15,
  `max_orders_per_wallet` smallint(5) UNSIGNED NOT NULL DEFAULT 8,
  `market_synced_at` timestamp NULL DEFAULT NULL,
  `last_source` varchar(60) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otc_pricing_settings`
--

INSERT INTO `otc_pricing_settings` (`id`, `mode`, `market_provider`, `market_rate_ngn`, `cached_market_rate_ngn`, `spread_ngn`, `manual_customer_rate_ngn`, `tolerance_percent`, `quote_ttl_minutes`, `max_orders_per_wallet`, `market_synced_at`, `last_source`, `created_at`, `updated_at`) VALUES
(1, 'live_minus_spread', 'manual_reference', 1420.0000, 1420.0000, 25.0000, NULL, 0.5000, 15, 8, '2026-08-02 12:33:26', 'manual_reference', '2026-08-02 12:33:26', '2026-08-02 12:33:26');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_timeline_events`
--

CREATE TABLE `payment_timeline_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subject_type` varchar(80) NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `event` varchar(80) NOT NULL,
  `label` varchar(191) NOT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `occurred_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_webhooks`
--

CREATE TABLE `payment_webhooks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `provider` varchar(40) NOT NULL DEFAULT 'monnify',
  `event` varchar(80) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`headers`)),
  `signature_valid` tinyint(1) DEFAULT NULL,
  `idempotency_key` varchar(191) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'received',
  `error` text DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admins.manage', 'web', '2026-07-21 22:45:58', '2026-07-21 22:45:58'),
(2, 'users.manage', 'web', '2026-07-21 22:45:58', '2026-07-21 22:45:58'),
(3, 'finance.manage', 'web', '2026-07-21 22:45:58', '2026-07-21 22:45:58'),
(4, 'support.manage', 'web', '2026-07-21 22:45:58', '2026-07-21 22:45:58'),
(5, 'catalog.manage', 'web', '2026-07-21 22:45:58', '2026-07-21 22:45:58'),
(6, 'compliance.manage', 'web', '2026-07-21 22:45:58', '2026-07-21 22:45:58'),
(7, 'system.manage', 'web', '2026-07-21 22:45:58', '2026-07-21 22:45:58'),
(8, 'analytics.view', 'web', '2026-07-21 22:45:58', '2026-07-21 22:45:58'),
(9, 'fees.manage', 'web', '2026-07-31 23:58:06', '2026-07-31 23:58:06');

-- --------------------------------------------------------

--
-- Table structure for table `platform_products`
--

CREATE TABLE `platform_products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_type` varchar(40) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `hero_image` varchar(255) DEFAULT NULL,
  `hero_media_id` bigint(20) UNSIGNED DEFAULT NULL,
  `demo_url` varchar(255) DEFAULT NULL,
  `demo_username` varchar(255) DEFAULT NULL,
  `demo_password` varchar(255) DEFAULT NULL,
  `industry` varchar(255) DEFAULT NULL,
  `framework` varchar(255) DEFAULT NULL,
  `is_responsive` tinyint(1) NOT NULL DEFAULT 1,
  `is_seo_ready` tinyint(1) NOT NULL DEFAULT 0,
  `support_period` varchar(255) DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`requirements`)),
  `whats_included` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`whats_included`)),
  `faqs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`faqs`)),
  `support_text` text DEFAULT NULL,
  `base_price` decimal(18,2) NOT NULL DEFAULT 0.00,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `provider` varchar(80) DEFAULT NULL,
  `provider_product_id` varchar(255) DEFAULT NULL,
  `provider_sku` varchar(255) DEFAULT NULL,
  `provider_meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider_meta`)),
  `fulfillment_mode` varchar(40) NOT NULL DEFAULT 'manual',
  `auto_renew` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `platform_products`
--

INSERT INTO `platform_products` (`id`, `product_type_id`, `product_type`, `title`, `slug`, `short_description`, `description`, `status`, `is_featured`, `sort_order`, `hero_image`, `hero_media_id`, `demo_url`, `demo_username`, `demo_password`, `industry`, `framework`, `is_responsive`, `is_seo_ready`, `support_period`, `features`, `requirements`, `whats_included`, `faqs`, `support_text`, `base_price`, `meta`, `provider`, `provider_product_id`, `provider_sku`, `provider_meta`, `fulfillment_mode`, `auto_renew`, `created_at`, `updated_at`) VALUES
(1, 5, 'vpn', 'Residential VPN Pro', 'residential-vpn-pro', 'Ready-to-use Residential VPN Pro from 7th Trade Hub.', 'Get started quickly with Residential VPN Pro. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 5000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(2, 5, 'vpn', 'Business VPN Shield', 'business-vpn-shield', 'Ready-to-use Business VPN Shield from 7th Trade Hub.', 'Get started quickly with Business VPN Shield. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 7500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(3, 5, 'vpn', 'Gaming VPN Boost', 'gaming-vpn-boost', 'Ready-to-use Gaming VPN Boost from 7th Trade Hub.', 'Get started quickly with Gaming VPN Boost. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 10000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(4, 5, 'vpn', 'Dedicated IP VPN', 'dedicated-ip-vpn', 'Ready-to-use Dedicated IP VPN from 7th Trade Hub.', 'Get started quickly with Dedicated IP VPN. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 12500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(5, 5, 'vpn', 'Family VPN Pack', 'family-vpn-pack', 'Ready-to-use Family VPN Pack from 7th Trade Hub.', 'Get started quickly with Family VPN Pack. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 15000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(6, 5, 'vpn', 'Travel VPN Lite', 'travel-vpn-lite', 'Ready-to-use Travel VPN Lite from 7th Trade Hub.', 'Get started quickly with Travel VPN Lite. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 17500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(7, 6, 'vps', 'Starter VPS 1GB', 'starter-vps-1gb', 'Ready-to-use Starter VPS 1GB from 7th Trade Hub.', 'Get started quickly with Starter VPS 1GB. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 15000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(8, 6, 'vps', 'Growth VPS 2GB', 'growth-vps-2gb', 'Ready-to-use Growth VPS 2GB from 7th Trade Hub.', 'Get started quickly with Growth VPS 2GB. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 20000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(9, 6, 'vps', 'Pro VPS 4GB', 'pro-vps-4gb', 'Ready-to-use Pro VPS 4GB from 7th Trade Hub.', 'Get started quickly with Pro VPS 4GB. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 25000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(10, 6, 'vps', 'Business VPS 8GB', 'business-vps-8gb', 'Ready-to-use Business VPS 8GB from 7th Trade Hub.', 'Get started quickly with Business VPS 8GB. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 30000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(11, 6, 'vps', 'High CPU VPS', 'high-cpu-vps', 'Ready-to-use High CPU VPS from 7th Trade Hub.', 'Get started quickly with High CPU VPS. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 35000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(12, 6, 'vps', 'Storage VPS 100GB', 'storage-vps-100gb', 'Ready-to-use Storage VPS 100GB from 7th Trade Hub.', 'Get started quickly with Storage VPS 100GB. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 40000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(13, 7, 'proxy', 'Datacenter Proxy Pack', 'datacenter-proxy-pack', 'Ready-to-use Datacenter Proxy Pack from 7th Trade Hub.', 'Get started quickly with Datacenter Proxy Pack. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 5000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(14, 7, 'proxy', 'Residential Proxy 1GB', 'residential-proxy-1gb', 'Ready-to-use Residential Proxy 1GB from 7th Trade Hub.', 'Get started quickly with Residential Proxy 1GB. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 7500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(15, 7, 'proxy', 'Mobile Proxy Pool', 'mobile-proxy-pool', 'Ready-to-use Mobile Proxy Pool from 7th Trade Hub.', 'Get started quickly with Mobile Proxy Pool. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 10000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(16, 7, 'proxy', 'ISP Proxy Bundle', 'isp-proxy-bundle', 'Ready-to-use ISP Proxy Bundle from 7th Trade Hub.', 'Get started quickly with ISP Proxy Bundle. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 12500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(17, 7, 'proxy', 'Sticky Session Proxy', 'sticky-session-proxy', 'Ready-to-use Sticky Session Proxy from 7th Trade Hub.', 'Get started quickly with Sticky Session Proxy. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 15000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(18, 7, 'proxy', 'Rotating Proxy Lite', 'rotating-proxy-lite', 'Ready-to-use Rotating Proxy Lite from 7th Trade Hub.', 'Get started quickly with Rotating Proxy Lite. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 17500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(19, 8, 'smtp', 'SMTP Starter 10k', 'smtp-starter-10k', 'Ready-to-use SMTP Starter 10k from 7th Trade Hub.', 'Get started quickly with SMTP Starter 10k. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 5000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(20, 8, 'smtp', 'SMTP Growth 50k', 'smtp-growth-50k', 'Ready-to-use SMTP Growth 50k from 7th Trade Hub.', 'Get started quickly with SMTP Growth 50k. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 7500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(21, 8, 'smtp', 'SMTP Pro 200k', 'smtp-pro-200k', 'Ready-to-use SMTP Pro 200k from 7th Trade Hub.', 'Get started quickly with SMTP Pro 200k. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 10000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(22, 8, 'smtp', 'Transactional SMTP', 'transactional-smtp', 'Ready-to-use Transactional SMTP from 7th Trade Hub.', 'Get started quickly with Transactional SMTP. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 12500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(23, 8, 'smtp', 'Marketing SMTP', 'marketing-smtp', 'Ready-to-use Marketing SMTP from 7th Trade Hub.', 'Get started quickly with Marketing SMTP. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 15000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(24, 8, 'smtp', 'Dedicated SMTP IP', 'dedicated-smtp-ip', 'Ready-to-use Dedicated SMTP IP from 7th Trade Hub.', 'Get started quickly with Dedicated SMTP IP. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 17500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(25, 4, 'virtual_phone', 'US Virtual Number', 'us-virtual-number', 'Ready-to-use US Virtual Number from 7th Trade Hub.', 'Get started quickly with US Virtual Number. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 5000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(26, 4, 'virtual_phone', 'UK Virtual Number', 'uk-virtual-number', 'Ready-to-use UK Virtual Number from 7th Trade Hub.', 'Get started quickly with UK Virtual Number. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 7500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(27, 4, 'virtual_phone', 'NG Virtual Number', 'ng-virtual-number', 'Ready-to-use NG Virtual Number from 7th Trade Hub.', 'Get started quickly with NG Virtual Number. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 10000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(28, 4, 'virtual_phone', 'Business Line Bundle', 'business-line-bundle', 'Ready-to-use Business Line Bundle from 7th Trade Hub.', 'Get started quickly with Business Line Bundle. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 12500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(29, 4, 'virtual_phone', 'SMS-Ready Number', 'sms-ready-number', 'Ready-to-use SMS-Ready Number from 7th Trade Hub.', 'Get started quickly with SMS-Ready Number. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 15000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(30, 4, 'virtual_phone', 'Toll-Free Lite', 'toll-free-lite', 'Ready-to-use Toll-Free Lite from 7th Trade Hub.', 'Get started quickly with Toll-Free Lite. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 17500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(31, 10, 'email', 'Business Email Starter', 'business-email-starter', 'Ready-to-use Business Email Starter from 7th Trade Hub.', 'Get started quickly with Business Email Starter. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 5000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(32, 10, 'email', 'Team Email 5 Seats', 'team-email-5-seats', 'Ready-to-use Team Email 5 Seats from 7th Trade Hub.', 'Get started quickly with Team Email 5 Seats. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 7500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(33, 10, 'email', 'Custom Domain Email', 'custom-domain-email', 'Ready-to-use Custom Domain Email from 7th Trade Hub.', 'Get started quickly with Custom Domain Email. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 10000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(34, 10, 'email', 'Secure Mail Pro', 'secure-mail-pro', 'Ready-to-use Secure Mail Pro from 7th Trade Hub.', 'Get started quickly with Secure Mail Pro. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 12500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(35, 10, 'email', 'Catch-All Mailbox', 'catch-all-mailbox', 'Ready-to-use Catch-All Mailbox from 7th Trade Hub.', 'Get started quickly with Catch-All Mailbox. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 15000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(36, 10, 'email', 'Email Forwarding Pack', 'email-forwarding-pack', 'Ready-to-use Email Forwarding Pack from 7th Trade Hub.', 'Get started quickly with Email Forwarding Pack. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 17500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(37, 11, 'social_service', 'Instagram Growth Pack', 'instagram-growth-pack', 'Ready-to-use Instagram Growth Pack from 7th Trade Hub.', 'Get started quickly with Instagram Growth Pack. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 5000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(38, 11, 'social_service', 'TikTok Engagement Boost', 'tiktok-engagement-boost', 'Ready-to-use TikTok Engagement Boost from 7th Trade Hub.', 'Get started quickly with TikTok Engagement Boost. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 7500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(39, 11, 'social_service', 'YouTube Views Lite', 'youtube-views-lite', 'Ready-to-use YouTube Views Lite from 7th Trade Hub.', 'Get started quickly with YouTube Views Lite. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 10000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(40, 11, 'social_service', 'Twitter Audience Pack', 'twitter-audience-pack', 'Ready-to-use Twitter Audience Pack from 7th Trade Hub.', 'Get started quickly with Twitter Audience Pack. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 12500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(41, 11, 'social_service', 'LinkedIn Lead Boost', 'linkedin-lead-boost', 'Ready-to-use LinkedIn Lead Boost from 7th Trade Hub.', 'Get started quickly with LinkedIn Lead Boost. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 15000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(42, 11, 'social_service', 'Multi-Platform Starter', 'multi-platform-starter', 'Ready-to-use Multi-Platform Starter from 7th Trade Hub.', 'Get started quickly with Multi-Platform Starter. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 17500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(43, 9, 'domain', '.com Domain Registration', 'com-domain-registration', 'Ready-to-use .com Domain Registration from 7th Trade Hub.', 'Get started quickly with .com Domain Registration. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 5000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(44, 9, 'domain', '.ng Domain Registration', 'ng-domain-registration', 'Ready-to-use .ng Domain Registration from 7th Trade Hub.', 'Get started quickly with .ng Domain Registration. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 7500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(45, 9, 'domain', '.io Domain Registration', 'io-domain-registration', 'Ready-to-use .io Domain Registration from 7th Trade Hub.', 'Get started quickly with .io Domain Registration. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 10000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(46, 9, 'domain', '.co Domain Registration', 'co-domain-registration', 'Ready-to-use .co Domain Registration from 7th Trade Hub.', 'Get started quickly with .co Domain Registration. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 12500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(47, 9, 'domain', 'Domain Transfer Assist', 'domain-transfer-assist', 'Ready-to-use Domain Transfer Assist from 7th Trade Hub.', 'Get started quickly with Domain Transfer Assist. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 15000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(48, 9, 'domain', 'Domain Privacy Pack', 'domain-privacy-pack', 'Ready-to-use Domain Privacy Pack from 7th Trade Hub.', 'Get started quickly with Domain Privacy Pack. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 17500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(55, 1, 'website_template', 'Corporate Landing Kit', 'corporate-landing-kit', 'Ready-to-use Corporate Landing Kit from 7th Trade Hub.', 'Get started quickly with Corporate Landing Kit. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 5000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(56, 1, 'website_template', 'Agency Portfolio Theme', 'agency-portfolio-theme', 'Ready-to-use Agency Portfolio Theme from 7th Trade Hub.', 'Get started quickly with Agency Portfolio Theme. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 7500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(57, 1, 'website_template', 'Law Firm Site Kit', 'law-firm-site-kit', 'Ready-to-use Law Firm Site Kit from 7th Trade Hub.', 'Get started quickly with Law Firm Site Kit. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 10000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(58, 1, 'website_template', 'Restaurant Menu Theme', 'restaurant-menu-theme', 'Ready-to-use Restaurant Menu Theme from 7th Trade Hub.', 'Get started quickly with Restaurant Menu Theme. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 12500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(59, 1, 'website_template', 'Medical Clinic Theme', 'medical-clinic-theme', 'Ready-to-use Medical Clinic Theme from 7th Trade Hub.', 'Get started quickly with Medical Clinic Theme. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 15000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52');
INSERT INTO `platform_products` (`id`, `product_type_id`, `product_type`, `title`, `slug`, `short_description`, `description`, `status`, `is_featured`, `sort_order`, `hero_image`, `hero_media_id`, `demo_url`, `demo_username`, `demo_password`, `industry`, `framework`, `is_responsive`, `is_seo_ready`, `support_period`, `features`, `requirements`, `whats_included`, `faqs`, `support_text`, `base_price`, `meta`, `provider`, `provider_product_id`, `provider_sku`, `provider_meta`, `fulfillment_mode`, `auto_renew`, `created_at`, `updated_at`) VALUES
(60, 1, 'website_template', 'Startup Launch Template', 'startup-launch-template', 'Ready-to-use Startup Launch Template from 7th Trade Hub.', 'Get started quickly with Startup Launch Template. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 17500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(61, 2, 'website_package', 'Starter Business Site', 'starter-business-site', 'Ready-to-use Starter Business Site from 7th Trade Hub.', 'Get started quickly with Starter Business Site. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 0, NULL, NULL, 'https://example.com/demo/starter-business-site', 'demo@7thtrade.local', 'DemoPass123!', 'Business', 'Laravel', 1, 1, '30 days', '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 15000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(62, 2, 'website_package', 'Agency Showcase Site', 'agency-showcase-site', 'Ready-to-use Agency Showcase Site from 7th Trade Hub.', 'Get started quickly with Agency Showcase Site. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 1, NULL, NULL, 'https://example.com/demo/agency-showcase-site', 'demo@7thtrade.local', 'DemoPass123!', 'Agency', 'WordPress', 1, 1, '30 days', '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 20000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(63, 2, 'website_package', 'Restaurant Booking Site', 'restaurant-booking-site', 'Ready-to-use Restaurant Booking Site from 7th Trade Hub.', 'Get started quickly with Restaurant Booking Site. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 2, NULL, NULL, 'https://example.com/demo/restaurant-booking-site', 'demo@7thtrade.local', 'DemoPass123!', 'Food', 'Next.js', 1, 1, '30 days', '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 25000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(64, 2, 'website_package', 'Law Practice Site', 'law-practice-site', 'Ready-to-use Law Practice Site from 7th Trade Hub.', 'Get started quickly with Law Practice Site. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 3, NULL, NULL, 'https://example.com/demo/law-practice-site', 'demo@7thtrade.local', 'DemoPass123!', 'Legal', 'Laravel', 1, 1, '30 days', '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 30000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(65, 2, 'website_package', 'Clinic Booking Site', 'clinic-booking-site', 'Ready-to-use Clinic Booking Site from 7th Trade Hub.', 'Get started quickly with Clinic Booking Site. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 4, NULL, NULL, 'https://example.com/demo/clinic-booking-site', 'demo@7thtrade.local', 'DemoPass123!', 'Health', 'WordPress', 1, 1, '30 days', '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 35000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(66, 2, 'website_package', 'E-commerce Starter Site', 'e-commerce-starter-site', 'Ready-to-use E-commerce Starter Site from 7th Trade Hub.', 'Get started quickly with E-commerce Starter Site. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 5, NULL, NULL, 'https://example.com/demo/e-commerce-starter-site', 'demo@7thtrade.local', 'DemoPass123!', 'Retail', 'Shopify', 1, 1, '30 days', '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 40000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(67, 3, 'document_template', 'Sales Contract Pack', 'sales-contract-pack', 'Ready-to-use Sales Contract Pack from 7th Trade Hub.', 'Get started quickly with Sales Contract Pack. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 5000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(68, 3, 'document_template', 'NDA Bundle', 'nda-bundle', 'Ready-to-use NDA Bundle from 7th Trade Hub.', 'Get started quickly with NDA Bundle. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 7500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(69, 3, 'document_template', 'Employment Agreement', 'employment-agreement', 'Ready-to-use Employment Agreement from 7th Trade Hub.', 'Get started quickly with Employment Agreement. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 10000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(70, 3, 'document_template', 'Invoice & Receipt Set', 'invoice-receipt-set', 'Ready-to-use Invoice & Receipt Set from 7th Trade Hub.', 'Get started quickly with Invoice & Receipt Set. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 12500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(71, 3, 'document_template', 'HR Policy Pack', 'hr-policy-pack', 'Ready-to-use HR Policy Pack from 7th Trade Hub.', 'Get started quickly with HR Policy Pack. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 15000.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(72, 3, 'document_template', 'Service Level Agreement', 'service-level-agreement', 'Ready-to-use Service Level Agreement from 7th Trade Hub.', 'Get started quickly with Service Level Agreement. Includes setup guidance, support, and clear deliverables. Admin can edit or remove this seeded product anytime.', 'published', 0, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '[\"Fast setup\",\"NGN wallet checkout\",\"Email support\"]', '[\"Active 7th Trade Hub account\",\"Funded wallet for purchase\"]', '[\"Product access\",\"Basic setup guide\",\"Support window\"]', '[{\"q\":\"How fast is delivery?\",\"a\":\"Most digital products are available right after payment.\"},{\"q\":\"Can I get a refund?\",\"a\":\"Refunds follow our support policy for unused digital goods.\"}]', 'Open a support ticket from your dashboard if you need help.', 17500.00, NULL, 'manual', NULL, NULL, NULL, 'manual', 0, '2026-07-18 01:37:52', '2026-07-18 01:37:52');

-- --------------------------------------------------------

--
-- Table structure for table `platform_product_images`
--

CREATE TABLE `platform_product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `platform_product_id` bigint(20) UNSIGNED NOT NULL,
  `media_asset_id` bigint(20) UNSIGNED DEFAULT NULL,
  `path` varchar(255) NOT NULL,
  `alt` varchar(255) DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `platform_product_images`
--

INSERT INTO `platform_product_images` (`id`, `platform_product_id`, `media_asset_id`, `path`, `alt`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 61, NULL, '/assets/images/Image_ro410gro410gro41.png', 'Starter Business Site screenshot 1', 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(2, 61, NULL, '/assets/images/Image_ro410gro410gro41.png', 'Starter Business Site screenshot 2', 2, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(3, 61, NULL, '/assets/images/Image_ro410gro410gro41.png', 'Starter Business Site screenshot 3', 3, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(4, 62, NULL, '/assets/images/Image_ro410gro410gro41.png', 'Agency Showcase Site screenshot 1', 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(5, 62, NULL, '/assets/images/Image_ro410gro410gro41.png', 'Agency Showcase Site screenshot 2', 2, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(6, 62, NULL, '/assets/images/Image_ro410gro410gro41.png', 'Agency Showcase Site screenshot 3', 3, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(7, 63, NULL, '/assets/images/Image_ro410gro410gro41.png', 'Restaurant Booking Site screenshot 1', 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(8, 63, NULL, '/assets/images/Image_ro410gro410gro41.png', 'Restaurant Booking Site screenshot 2', 2, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(9, 63, NULL, '/assets/images/Image_ro410gro410gro41.png', 'Restaurant Booking Site screenshot 3', 3, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(10, 64, NULL, '/assets/images/Image_ro410gro410gro41.png', 'Law Practice Site screenshot 1', 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(11, 64, NULL, '/assets/images/Image_ro410gro410gro41.png', 'Law Practice Site screenshot 2', 2, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(12, 64, NULL, '/assets/images/Image_ro410gro410gro41.png', 'Law Practice Site screenshot 3', 3, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(13, 65, NULL, '/assets/images/Image_ro410gro410gro41.png', 'Clinic Booking Site screenshot 1', 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(14, 65, NULL, '/assets/images/Image_ro410gro410gro41.png', 'Clinic Booking Site screenshot 2', 2, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(15, 65, NULL, '/assets/images/Image_ro410gro410gro41.png', 'Clinic Booking Site screenshot 3', 3, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(16, 66, NULL, '/assets/images/Image_ro410gro410gro41.png', 'E-commerce Starter Site screenshot 1', 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(17, 66, NULL, '/assets/images/Image_ro410gro410gro41.png', 'E-commerce Starter Site screenshot 2', 2, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(18, 66, NULL, '/assets/images/Image_ro410gro410gro41.png', 'E-commerce Starter Site screenshot 3', 3, '2026-07-18 01:37:52', '2026-07-18 01:37:52');

-- --------------------------------------------------------

--
-- Table structure for table `platform_product_variants`
--

CREATE TABLE `platform_product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `platform_product_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `duration_months` int(10) UNSIGNED DEFAULT NULL,
  `price` decimal(18,2) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `platform_product_variants`
--

INSERT INTO `platform_product_variants` (`id`, `platform_product_id`, `name`, `label`, `sku`, `duration_months`, `price`, `sort_order`, `is_default`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, '1 Month', '1 Month', 'residential-vpn-pro-1m', 1, 5000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(2, 1, '3 Months', '3 Months', 'residential-vpn-pro-3m', 3, 13500.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(3, 1, '6 Months', '6 Months', 'residential-vpn-pro-6m', 6, 25000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(4, 1, '1 Year', '1 Year', 'residential-vpn-pro-12m', 12, 45000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(5, 2, '1 Month', '1 Month', 'business-vpn-shield-1m', 1, 7500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(6, 2, '3 Months', '3 Months', 'business-vpn-shield-3m', 3, 20250.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(7, 2, '6 Months', '6 Months', 'business-vpn-shield-6m', 6, 37500.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(8, 2, '1 Year', '1 Year', 'business-vpn-shield-12m', 12, 67500.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(9, 3, '1 Month', '1 Month', 'gaming-vpn-boost-1m', 1, 10000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(10, 3, '3 Months', '3 Months', 'gaming-vpn-boost-3m', 3, 27000.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(11, 3, '6 Months', '6 Months', 'gaming-vpn-boost-6m', 6, 50000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(12, 3, '1 Year', '1 Year', 'gaming-vpn-boost-12m', 12, 90000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(13, 4, '1 Month', '1 Month', 'dedicated-ip-vpn-1m', 1, 12500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(14, 4, '3 Months', '3 Months', 'dedicated-ip-vpn-3m', 3, 33750.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(15, 4, '6 Months', '6 Months', 'dedicated-ip-vpn-6m', 6, 62500.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(16, 4, '1 Year', '1 Year', 'dedicated-ip-vpn-12m', 12, 112500.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(17, 5, '1 Month', '1 Month', 'family-vpn-pack-1m', 1, 15000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(18, 5, '3 Months', '3 Months', 'family-vpn-pack-3m', 3, 40500.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(19, 5, '6 Months', '6 Months', 'family-vpn-pack-6m', 6, 75000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(20, 5, '1 Year', '1 Year', 'family-vpn-pack-12m', 12, 135000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(21, 6, '1 Month', '1 Month', 'travel-vpn-lite-1m', 1, 17500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(22, 6, '3 Months', '3 Months', 'travel-vpn-lite-3m', 3, 47250.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(23, 6, '6 Months', '6 Months', 'travel-vpn-lite-6m', 6, 87500.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(24, 6, '1 Year', '1 Year', 'travel-vpn-lite-12m', 12, 157500.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(25, 7, '1 Month', '1 Month', 'starter-vps-1gb-1m', 1, 15000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(26, 7, '3 Months', '3 Months', 'starter-vps-1gb-3m', 3, 40500.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(27, 7, '6 Months', '6 Months', 'starter-vps-1gb-6m', 6, 75000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(28, 7, '1 Year', '1 Year', 'starter-vps-1gb-12m', 12, 135000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(29, 8, '1 Month', '1 Month', 'growth-vps-2gb-1m', 1, 20000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(30, 8, '3 Months', '3 Months', 'growth-vps-2gb-3m', 3, 54000.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(31, 8, '6 Months', '6 Months', 'growth-vps-2gb-6m', 6, 100000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(32, 8, '1 Year', '1 Year', 'growth-vps-2gb-12m', 12, 180000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(33, 9, '1 Month', '1 Month', 'pro-vps-4gb-1m', 1, 25000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(34, 9, '3 Months', '3 Months', 'pro-vps-4gb-3m', 3, 67500.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(35, 9, '6 Months', '6 Months', 'pro-vps-4gb-6m', 6, 125000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(36, 9, '1 Year', '1 Year', 'pro-vps-4gb-12m', 12, 225000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(37, 10, '1 Month', '1 Month', 'business-vps-8gb-1m', 1, 30000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(38, 10, '3 Months', '3 Months', 'business-vps-8gb-3m', 3, 81000.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(39, 10, '6 Months', '6 Months', 'business-vps-8gb-6m', 6, 150000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(40, 10, '1 Year', '1 Year', 'business-vps-8gb-12m', 12, 270000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(41, 11, '1 Month', '1 Month', 'high-cpu-vps-1m', 1, 35000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(42, 11, '3 Months', '3 Months', 'high-cpu-vps-3m', 3, 94500.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(43, 11, '6 Months', '6 Months', 'high-cpu-vps-6m', 6, 175000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(44, 11, '1 Year', '1 Year', 'high-cpu-vps-12m', 12, 315000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(45, 12, '1 Month', '1 Month', 'storage-vps-100gb-1m', 1, 40000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(46, 12, '3 Months', '3 Months', 'storage-vps-100gb-3m', 3, 108000.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(47, 12, '6 Months', '6 Months', 'storage-vps-100gb-6m', 6, 200000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(48, 12, '1 Year', '1 Year', 'storage-vps-100gb-12m', 12, 360000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(49, 13, '1 Month', '1 Month', 'datacenter-proxy-pack-1m', 1, 5000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(50, 13, '3 Months', '3 Months', 'datacenter-proxy-pack-3m', 3, 13500.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(51, 13, '6 Months', '6 Months', 'datacenter-proxy-pack-6m', 6, 25000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(52, 13, '1 Year', '1 Year', 'datacenter-proxy-pack-12m', 12, 45000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(53, 14, '1 Month', '1 Month', 'residential-proxy-1gb-1m', 1, 7500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(54, 14, '3 Months', '3 Months', 'residential-proxy-1gb-3m', 3, 20250.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(55, 14, '6 Months', '6 Months', 'residential-proxy-1gb-6m', 6, 37500.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(56, 14, '1 Year', '1 Year', 'residential-proxy-1gb-12m', 12, 67500.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(57, 15, '1 Month', '1 Month', 'mobile-proxy-pool-1m', 1, 10000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(58, 15, '3 Months', '3 Months', 'mobile-proxy-pool-3m', 3, 27000.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(59, 15, '6 Months', '6 Months', 'mobile-proxy-pool-6m', 6, 50000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(60, 15, '1 Year', '1 Year', 'mobile-proxy-pool-12m', 12, 90000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(61, 16, '1 Month', '1 Month', 'isp-proxy-bundle-1m', 1, 12500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(62, 16, '3 Months', '3 Months', 'isp-proxy-bundle-3m', 3, 33750.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(63, 16, '6 Months', '6 Months', 'isp-proxy-bundle-6m', 6, 62500.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(64, 16, '1 Year', '1 Year', 'isp-proxy-bundle-12m', 12, 112500.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(65, 17, '1 Month', '1 Month', 'sticky-session-proxy-1m', 1, 15000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(66, 17, '3 Months', '3 Months', 'sticky-session-proxy-3m', 3, 40500.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(67, 17, '6 Months', '6 Months', 'sticky-session-proxy-6m', 6, 75000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(68, 17, '1 Year', '1 Year', 'sticky-session-proxy-12m', 12, 135000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(69, 18, '1 Month', '1 Month', 'rotating-proxy-lite-1m', 1, 17500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(70, 18, '3 Months', '3 Months', 'rotating-proxy-lite-3m', 3, 47250.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(71, 18, '6 Months', '6 Months', 'rotating-proxy-lite-6m', 6, 87500.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(72, 18, '1 Year', '1 Year', 'rotating-proxy-lite-12m', 12, 157500.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(73, 19, '1 Month', '1 Month', 'smtp-starter-10k-1m', 1, 5000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(74, 19, '3 Months', '3 Months', 'smtp-starter-10k-3m', 3, 13500.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(75, 19, '6 Months', '6 Months', 'smtp-starter-10k-6m', 6, 25000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(76, 19, '1 Year', '1 Year', 'smtp-starter-10k-12m', 12, 45000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(77, 20, '1 Month', '1 Month', 'smtp-growth-50k-1m', 1, 7500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(78, 20, '3 Months', '3 Months', 'smtp-growth-50k-3m', 3, 20250.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(79, 20, '6 Months', '6 Months', 'smtp-growth-50k-6m', 6, 37500.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(80, 20, '1 Year', '1 Year', 'smtp-growth-50k-12m', 12, 67500.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(81, 21, '1 Month', '1 Month', 'smtp-pro-200k-1m', 1, 10000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(82, 21, '3 Months', '3 Months', 'smtp-pro-200k-3m', 3, 27000.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(83, 21, '6 Months', '6 Months', 'smtp-pro-200k-6m', 6, 50000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(84, 21, '1 Year', '1 Year', 'smtp-pro-200k-12m', 12, 90000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(85, 22, '1 Month', '1 Month', 'transactional-smtp-1m', 1, 12500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(86, 22, '3 Months', '3 Months', 'transactional-smtp-3m', 3, 33750.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(87, 22, '6 Months', '6 Months', 'transactional-smtp-6m', 6, 62500.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(88, 22, '1 Year', '1 Year', 'transactional-smtp-12m', 12, 112500.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(89, 23, '1 Month', '1 Month', 'marketing-smtp-1m', 1, 15000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(90, 23, '3 Months', '3 Months', 'marketing-smtp-3m', 3, 40500.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(91, 23, '6 Months', '6 Months', 'marketing-smtp-6m', 6, 75000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(92, 23, '1 Year', '1 Year', 'marketing-smtp-12m', 12, 135000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(93, 24, '1 Month', '1 Month', 'dedicated-smtp-ip-1m', 1, 17500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(94, 24, '3 Months', '3 Months', 'dedicated-smtp-ip-3m', 3, 47250.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(95, 24, '6 Months', '6 Months', 'dedicated-smtp-ip-6m', 6, 87500.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(96, 24, '1 Year', '1 Year', 'dedicated-smtp-ip-12m', 12, 157500.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(97, 25, '1 Month', '1 Month', 'us-virtual-number-1m', 1, 5000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(98, 25, '3 Months', '3 Months', 'us-virtual-number-3m', 3, 13500.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(99, 25, '6 Months', '6 Months', 'us-virtual-number-6m', 6, 25000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(100, 25, '1 Year', '1 Year', 'us-virtual-number-12m', 12, 45000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(101, 26, '1 Month', '1 Month', 'uk-virtual-number-1m', 1, 7500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(102, 26, '3 Months', '3 Months', 'uk-virtual-number-3m', 3, 20250.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(103, 26, '6 Months', '6 Months', 'uk-virtual-number-6m', 6, 37500.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(104, 26, '1 Year', '1 Year', 'uk-virtual-number-12m', 12, 67500.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(105, 27, '1 Month', '1 Month', 'ng-virtual-number-1m', 1, 10000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(106, 27, '3 Months', '3 Months', 'ng-virtual-number-3m', 3, 27000.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(107, 27, '6 Months', '6 Months', 'ng-virtual-number-6m', 6, 50000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(108, 27, '1 Year', '1 Year', 'ng-virtual-number-12m', 12, 90000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(109, 28, '1 Month', '1 Month', 'business-line-bundle-1m', 1, 12500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(110, 28, '3 Months', '3 Months', 'business-line-bundle-3m', 3, 33750.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(111, 28, '6 Months', '6 Months', 'business-line-bundle-6m', 6, 62500.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(112, 28, '1 Year', '1 Year', 'business-line-bundle-12m', 12, 112500.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(113, 29, '1 Month', '1 Month', 'sms-ready-number-1m', 1, 15000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(114, 29, '3 Months', '3 Months', 'sms-ready-number-3m', 3, 40500.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(115, 29, '6 Months', '6 Months', 'sms-ready-number-6m', 6, 75000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(116, 29, '1 Year', '1 Year', 'sms-ready-number-12m', 12, 135000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(117, 30, '1 Month', '1 Month', 'toll-free-lite-1m', 1, 17500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(118, 30, '3 Months', '3 Months', 'toll-free-lite-3m', 3, 47250.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(119, 30, '6 Months', '6 Months', 'toll-free-lite-6m', 6, 87500.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(120, 30, '1 Year', '1 Year', 'toll-free-lite-12m', 12, 157500.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(121, 31, '1 Month', '1 Month', 'business-email-starter-1m', 1, 5000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(122, 31, '3 Months', '3 Months', 'business-email-starter-3m', 3, 13500.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(123, 31, '6 Months', '6 Months', 'business-email-starter-6m', 6, 25000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(124, 31, '1 Year', '1 Year', 'business-email-starter-12m', 12, 45000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(125, 32, '1 Month', '1 Month', 'team-email-5-seats-1m', 1, 7500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(126, 32, '3 Months', '3 Months', 'team-email-5-seats-3m', 3, 20250.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(127, 32, '6 Months', '6 Months', 'team-email-5-seats-6m', 6, 37500.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(128, 32, '1 Year', '1 Year', 'team-email-5-seats-12m', 12, 67500.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(129, 33, '1 Month', '1 Month', 'custom-domain-email-1m', 1, 10000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(130, 33, '3 Months', '3 Months', 'custom-domain-email-3m', 3, 27000.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(131, 33, '6 Months', '6 Months', 'custom-domain-email-6m', 6, 50000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(132, 33, '1 Year', '1 Year', 'custom-domain-email-12m', 12, 90000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(133, 34, '1 Month', '1 Month', 'secure-mail-pro-1m', 1, 12500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(134, 34, '3 Months', '3 Months', 'secure-mail-pro-3m', 3, 33750.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(135, 34, '6 Months', '6 Months', 'secure-mail-pro-6m', 6, 62500.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(136, 34, '1 Year', '1 Year', 'secure-mail-pro-12m', 12, 112500.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(137, 35, '1 Month', '1 Month', 'catch-all-mailbox-1m', 1, 15000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(138, 35, '3 Months', '3 Months', 'catch-all-mailbox-3m', 3, 40500.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(139, 35, '6 Months', '6 Months', 'catch-all-mailbox-6m', 6, 75000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(140, 35, '1 Year', '1 Year', 'catch-all-mailbox-12m', 12, 135000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(141, 36, '1 Month', '1 Month', 'email-forwarding-pack-1m', 1, 17500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(142, 36, '3 Months', '3 Months', 'email-forwarding-pack-3m', 3, 47250.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(143, 36, '6 Months', '6 Months', 'email-forwarding-pack-6m', 6, 87500.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(144, 36, '1 Year', '1 Year', 'email-forwarding-pack-12m', 12, 157500.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(145, 37, 'Standard', 'Standard', 'instagram-growth-pack-std', NULL, 5000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(146, 38, 'Standard', 'Standard', 'tiktok-engagement-boost-std', NULL, 7500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(147, 39, 'Standard', 'Standard', 'youtube-views-lite-std', NULL, 10000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(148, 40, 'Standard', 'Standard', 'twitter-audience-pack-std', NULL, 12500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(149, 41, 'Standard', 'Standard', 'linkedin-lead-boost-std', NULL, 15000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(150, 42, 'Standard', 'Standard', 'multi-platform-starter-std', NULL, 17500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(151, 43, 'Standard', 'Standard', 'com-domain-registration-std', NULL, 5000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(152, 44, 'Standard', 'Standard', 'ng-domain-registration-std', NULL, 7500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(153, 45, 'Standard', 'Standard', 'io-domain-registration-std', NULL, 10000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(154, 46, 'Standard', 'Standard', 'co-domain-registration-std', NULL, 12500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(155, 47, 'Standard', 'Standard', 'domain-transfer-assist-std', NULL, 15000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(156, 48, 'Standard', 'Standard', 'domain-privacy-pack-std', NULL, 17500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(163, 55, 'Standard', 'Standard', 'corporate-landing-kit-std', NULL, 5000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(164, 56, 'Standard', 'Standard', 'agency-portfolio-theme-std', NULL, 7500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(165, 57, 'Standard', 'Standard', 'law-firm-site-kit-std', NULL, 10000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(166, 58, 'Standard', 'Standard', 'restaurant-menu-theme-std', NULL, 12500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(167, 59, 'Standard', 'Standard', 'medical-clinic-theme-std', NULL, 15000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(168, 60, 'Standard', 'Standard', 'startup-launch-template-std', NULL, 17500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(169, 61, '1 Month', '1 Month', 'starter-business-site-1m', 1, 15000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(170, 61, '3 Months', '3 Months', 'starter-business-site-3m', 3, 40500.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(171, 61, '6 Months', '6 Months', 'starter-business-site-6m', 6, 75000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(172, 61, '1 Year', '1 Year', 'starter-business-site-12m', 12, 135000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(173, 62, '1 Month', '1 Month', 'agency-showcase-site-1m', 1, 20000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(174, 62, '3 Months', '3 Months', 'agency-showcase-site-3m', 3, 54000.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(175, 62, '6 Months', '6 Months', 'agency-showcase-site-6m', 6, 100000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(176, 62, '1 Year', '1 Year', 'agency-showcase-site-12m', 12, 180000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(177, 63, '1 Month', '1 Month', 'restaurant-booking-site-1m', 1, 25000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(178, 63, '3 Months', '3 Months', 'restaurant-booking-site-3m', 3, 67500.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(179, 63, '6 Months', '6 Months', 'restaurant-booking-site-6m', 6, 125000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(180, 63, '1 Year', '1 Year', 'restaurant-booking-site-12m', 12, 225000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(181, 64, '1 Month', '1 Month', 'law-practice-site-1m', 1, 30000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(182, 64, '3 Months', '3 Months', 'law-practice-site-3m', 3, 81000.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(183, 64, '6 Months', '6 Months', 'law-practice-site-6m', 6, 150000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(184, 64, '1 Year', '1 Year', 'law-practice-site-12m', 12, 270000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(185, 65, '1 Month', '1 Month', 'clinic-booking-site-1m', 1, 35000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(186, 65, '3 Months', '3 Months', 'clinic-booking-site-3m', 3, 94500.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(187, 65, '6 Months', '6 Months', 'clinic-booking-site-6m', 6, 175000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(188, 65, '1 Year', '1 Year', 'clinic-booking-site-12m', 12, 315000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(189, 66, '1 Month', '1 Month', 'e-commerce-starter-site-1m', 1, 40000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(190, 66, '3 Months', '3 Months', 'e-commerce-starter-site-3m', 3, 108000.00, 1, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(191, 66, '6 Months', '6 Months', 'e-commerce-starter-site-6m', 6, 200000.00, 2, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(192, 66, '1 Year', '1 Year', 'e-commerce-starter-site-12m', 12, 360000.00, 3, 0, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(193, 67, 'Standard', 'Standard', 'sales-contract-pack-std', NULL, 5000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(194, 68, 'Standard', 'Standard', 'nda-bundle-std', NULL, 7500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(195, 69, 'Standard', 'Standard', 'employment-agreement-std', NULL, 10000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(196, 70, 'Standard', 'Standard', 'invoice-receipt-set-std', NULL, 12500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(197, 71, 'Standard', 'Standard', 'hr-policy-pack-std', NULL, 15000.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52'),
(198, 72, 'Standard', 'Standard', 'service-level-agreement-std', NULL, 17500.00, 0, 1, 1, '2026-07-18 01:37:52', '2026-07-18 01:37:52');

-- --------------------------------------------------------

--
-- Table structure for table `product_metric_daily`
--

CREATE TABLE `product_metric_daily` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `day` date NOT NULL,
  `metric_key` varchar(80) NOT NULL,
  `dimension` varchar(120) DEFAULT NULL,
  `count` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_metric_daily`
--

INSERT INTO `product_metric_daily` (`id`, `day`, `metric_key`, `dimension`, `count`, `created_at`, `updated_at`) VALUES
(340, '2026-07-24', 'discover.marketplace', '', 1, '2026-07-24 21:51:49', '2026-07-24 21:51:49'),
(341, '2026-07-24', 'listing.viewed', '47', 1, '2026-07-24 21:52:17', '2026-07-24 21:52:17'),
(342, '2026-07-24', 'discover.services', '', 1, '2026-07-24 21:52:43', '2026-07-24 21:52:43'),
(343, '2026-08-01', 'discover.marketplace', '', 2, '2026-08-01 00:54:12', '2026-08-01 01:05:33'),
(344, '2026-08-01', 'discover.services', '', 2, '2026-08-01 00:55:53', '2026-08-01 01:06:58'),
(346, '2026-08-01', 'event.ticket_opened', '', 1, '2026-08-01 01:00:32', '2026-08-01 01:00:32'),
(348, '2026-08-01', 'event.ticket_replied', '', 1, '2026-08-01 01:00:55', '2026-08-01 01:00:55'),
(349, '2026-08-01', 'services.hub', '', 7, '2026-08-01 01:56:33', '2026-08-01 03:29:46'),
(350, '2026-08-01', 'marketplace.hub', '', 9, '2026-08-01 01:56:44', '2026-08-01 21:25:16'),
(351, '2026-08-01', 'listing.checkout', '49', 1, '2026-08-01 03:03:19', '2026-08-01 03:03:19'),
(352, '2026-08-01', 'service.viewed', '1', 1, '2026-08-01 03:17:50', '2026-08-01 03:17:50'),
(353, '2026-08-01', 'user.registered', '', 1, '2026-08-01 03:23:11', '2026-08-01 03:23:11'),
(354, '2026-08-01', 'event.sign_up', '', 1, '2026-08-01 03:23:11', '2026-08-01 03:23:11'),
(356, '2026-08-01', 'services.browse.website-services', '', 2, '2026-08-01 03:30:11', '2026-08-01 03:30:33'),
(357, '2026-08-01', 'listing.checkout', '48', 1, '2026-08-01 03:31:11', '2026-08-01 03:31:11'),
(358, '2026-08-01', 'listing.checkout', '50', 1, '2026-08-01 03:34:10', '2026-08-01 03:34:10'),
(359, '2026-08-02', 'marketplace.hub', '', 2, '2026-08-02 00:28:51', '2026-08-02 17:58:13'),
(360, '2026-08-02', 'services.hub', '', 4, '2026-08-02 00:29:32', '2026-08-02 00:38:13'),
(361, '2026-08-02', 'service.checkout', '55', 1, '2026-08-02 13:10:02', '2026-08-02 13:10:02'),
(362, '2026-08-03', 'services.hub', '', 1, '2026-08-03 00:00:47', '2026-08-03 00:00:47'),
(363, '2026-08-03', 'services.browse.website-services', '', 2, '2026-08-03 00:01:05', '2026-08-03 00:01:10');

-- --------------------------------------------------------

--
-- Table structure for table `product_metric_monthly`
--

CREATE TABLE `product_metric_monthly` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `month` char(7) NOT NULL,
  `metric_key` varchar(80) NOT NULL,
  `dimension` varchar(120) DEFAULT NULL,
  `count` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `platform_product_id` bigint(20) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `comment` text DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_types`
--

CREATE TABLE `product_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `service_category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `banner_image` varchar(255) DEFAULT NULL,
  `banner_media_id` bigint(20) UNSIGNED DEFAULT NULL,
  `card_image` varchar(255) DEFAULT NULL,
  `card_media_id` bigint(20) UNSIGNED DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `hero_title` varchar(255) DEFAULT NULL,
  `hero_subtitle` varchar(500) DEFAULT NULL,
  `benefits` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`benefits`)),
  `faq` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`faq`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_types`
--

INSERT INTO `product_types` (`id`, `service_category_id`, `name`, `slug`, `sort_order`, `is_active`, `banner_image`, `banner_media_id`, `card_image`, `card_media_id`, `short_description`, `hero_title`, `hero_subtitle`, `benefits`, `faq`, `created_at`, `updated_at`) VALUES
(1, 4, 'Website Templates', 'website_template', 0, 1, NULL, NULL, NULL, NULL, 'Ready-to-launch website designs for businesses and creators.', 'Website Templates', 'Browse polished templates you can adapt to your brand.', '[\"Modern layouts ready for customization\",\"Responsive across devices\",\"Clear handoff for developers or DIY edits\"]', '[{\"q\":\"Can I edit the template after purchase?\",\"a\":\"Yes. You receive files or access details so you can customize the design.\"},{\"q\":\"Is hosting included?\",\"a\":\"Templates are design assets. Hosting is separate unless noted on the product.\"}]', '2026-07-22 14:47:54', '2026-07-22 14:47:54'),
(2, 4, 'Website Packages', 'website_package', 1, 1, NULL, NULL, NULL, NULL, 'Hosted website packages with demos and support windows.', 'Website Packages', 'Pick a package with demo access and clear deliverables.', '[\"Demo environments to preview before you buy\",\"Defined support period on eligible packages\",\"Business-ready industry options\"]', '[{\"q\":\"How do demos work?\",\"a\":\"Eligible packages include a demo URL and login so you can explore before checkout.\"}]', '2026-07-22 14:47:54', '2026-07-22 14:47:54'),
(3, 5, 'Document Templates', 'document_template', 0, 1, NULL, NULL, NULL, NULL, 'Contracts, HR, and legal templates ready to customize.', 'Business Document Templates', 'Filter by Contracts, HR, or Legal and download what you need.', '[\"Structured templates for common business needs\",\"Clear category filters\",\"Editable after purchase\"]', '[{\"q\":\"Are these legal advice?\",\"a\":\"No. Templates are starting points. Have a qualified professional review critical documents.\"}]', '2026-07-22 14:47:54', '2026-07-22 14:47:54'),
(4, 2, 'Virtual Phone Numbers', 'virtual_phone', 0, 1, NULL, NULL, NULL, NULL, 'Local and international virtual numbers for business and verification.', 'Virtual Phone Numbers', 'Choose regions and plans that fit your workflow.', '[\"Multiple regions available\",\"Flexible plan durations\",\"Clear pricing before checkout\"]', '[{\"q\":\"How quickly is a number provisioned?\",\"a\":\"Most orders are fulfilled after payment confirmation according to the product notes.\"}]', '2026-07-22 14:47:54', '2026-07-22 14:47:54'),
(5, 1, 'VPN', 'vpn', 0, 1, NULL, NULL, NULL, NULL, 'Residential, gaming, and business VPN plans.', 'VPN Services', 'Secure connections with plans for homes, teams, and gaming.', '[\"Multiple plan tiers\",\"Clear duration and pricing\",\"Escrow-backed platform checkout\"]', '[{\"q\":\"Which VPN should I pick?\",\"a\":\"Use category filters (Residential, Gaming, Business) or compare featured plans below.\"}]', '2026-07-22 14:47:54', '2026-07-22 14:47:54'),
(6, 1, 'VPS', 'vps', 1, 1, NULL, NULL, NULL, NULL, 'Shared and dedicated VPS options for apps and sites.', 'VPS Hosting', 'Scale from shared to dedicated resources as you grow.', '[\"Shared and dedicated tiers\",\"Transparent monthly pricing\",\"Suitable for apps, sites, and automation\"]', '[{\"q\":\"Is management included?\",\"a\":\"Check each product description for managed vs self-managed details.\"}]', '2026-07-22 14:47:54', '2026-07-22 14:47:54'),
(7, 1, 'Proxy', 'proxy', 2, 1, NULL, NULL, NULL, NULL, 'Datacenter, residential, and mobile proxy pools.', 'Proxy Services', 'Pick the proxy type that matches your use case.', '[\"Datacenter, residential, and mobile options\",\"Plan-based pricing\",\"Filter by category quickly\"]', '[{\"q\":\"Can I switch plans later?\",\"a\":\"Purchase a new plan that fits; contact support if you need help migrating.\"}]', '2026-07-22 14:47:54', '2026-07-22 14:47:54'),
(8, 1, 'SMTP', 'smtp', 3, 1, NULL, NULL, NULL, NULL, 'Transactional and marketing SMTP capacity.', 'SMTP Services', 'Reliable outbound email for transactional and marketing flows.', '[\"Transactional and marketing tiers\",\"Clear send volume expectations on plans\",\"Platform-protected checkout\"]', '[{\"q\":\"Do you provide warm-up guidance?\",\"a\":\"Product pages note recommended use; follow your own compliance and deliverability practices.\"}]', '2026-07-22 14:47:54', '2026-07-22 14:47:54'),
(9, 4, 'Domains', 'domain', 2, 1, NULL, NULL, NULL, NULL, 'Domain registration and transfer assistance.', 'Domain Services', 'Register or transfer domains with clear next steps.', '[\"Registration and transfer options\",\"Straightforward pricing\",\"Guided fulfillment after payment\"]', '[{\"q\":\"Who owns the domain after purchase?\",\"a\":\"Ownership and registrar details are confirmed during fulfillment for your order.\"}]', '2026-07-22 14:47:54', '2026-07-22 14:47:54'),
(10, 2, 'Email Services', 'email', 1, 1, NULL, NULL, NULL, NULL, 'Business and team email mailboxes.', 'Email Services', 'Professional mailboxes for solo operators and teams.', '[\"Business and team plans\",\"Predictable pricing\",\"Works alongside your domain setup\"]', '[{\"q\":\"Can I use my own domain?\",\"a\":\"Yes on eligible plans \\u2014 see product requirements for DNS setup.\"}]', '2026-07-22 14:47:54', '2026-07-22 14:47:54'),
(11, 3, 'Social Media Services', 'social_service', 0, 1, NULL, NULL, NULL, NULL, 'Growth and engagement services for social platforms.', 'Social Media Services', 'Filter Growth or Engagement packages for the outcome you want.', '[\"Growth and engagement categories\",\"Clear deliverable descriptions\",\"Protected platform checkout\"]', '[{\"q\":\"How do I choose Growth vs Engagement?\",\"a\":\"Use the category filter: Growth focuses on audience size; Engagement focuses on interaction.\"}]', '2026-07-22 14:47:54', '2026-07-22 14:47:54');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2026-07-15 00:19:00', '2026-07-15 00:19:00'),
(2, 'user', 'web', '2026-07-15 00:19:00', '2026-07-15 00:19:00'),
(3, 'demo_finance', 'web', '2026-07-23 23:42:00', '2026-07-23 23:42:00'),
(4, 'demo_compliance', 'web', '2026-07-23 23:42:00', '2026-07-23 23:42:00'),
(5, 'demo_support', 'web', '2026-07-23 23:42:00', '2026-07-23 23:42:00'),
(6, 'demo_moderator', 'web', '2026-07-23 23:42:00', '2026-07-23 23:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(3, 3),
(8, 3),
(2, 4),
(6, 4),
(4, 5),
(5, 6);

-- --------------------------------------------------------

--
-- Table structure for table `security_verification_codes`
--

CREATE TABLE `security_verification_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `purpose` varchar(60) NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
--

CREATE TABLE `service_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `banner_image` varchar(255) DEFAULT NULL,
  `banner_media_id` bigint(20) UNSIGNED DEFAULT NULL,
  `card_image` varchar(255) DEFAULT NULL,
  `card_media_id` bigint(20) UNSIGNED DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `hero_title` varchar(255) DEFAULT NULL,
  `hero_subtitle` varchar(500) DEFAULT NULL,
  `benefits` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`benefits`)),
  `faq` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`faq`)),
  `mode` varchar(40) NOT NULL DEFAULT 'catalog',
  `cta_label` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_categories`
--

INSERT INTO `service_categories` (`id`, `name`, `slug`, `sort_order`, `is_active`, `banner_image`, `banner_media_id`, `card_image`, `card_media_id`, `short_description`, `hero_title`, `hero_subtitle`, `benefits`, `faq`, `mode`, `cta_label`, `created_at`, `updated_at`) VALUES
(1, 'Network Services', 'network-services', 3, 1, 'storage/media/2026/07/01KY5HC5JNDDFZTGW6E825E9VA-medium.webp', 18, 'storage/media/2026/07/01KY5HC5JNDDFZTGW6E825E9VA-medium.webp', 18, 'VPN, RDP, SMTP, and proxy plans for connectivity and infrastructure.', 'Network Services', 'Secure connections, servers, mail relay, and proxies in one place.', '[\"Infrastructure and connectivity in one browse path\",\"Compare plans by type before you buy\",\"Transparent NGN pricing\"]', '[{\"q\":\"Where do I start?\",\"a\":\"Pick a type card below (VPN, VPS, SMTP, or Proxy), then filter plans on the type page.\",\"open\":false}]', 'catalog', NULL, '2026-07-22 14:47:54', '2026-08-02 00:36:54'),
(2, 'Communication', 'communication', 4, 1, 'storage/media/2026/07/01KY5HC3S3ADKH48XJQT3GDDEX-medium.webp', 14, 'storage/media/2026/07/01KY5HC3S3ADKH48XJQT3GDDEX-medium.webp', 14, 'Email mailboxes and virtual phone numbers for business outreach.', 'Communication', 'Stay reachable with professional email and virtual numbers.', '[\"Email and phone in one group\",\"Plan options for solo and team use\",\"Clear checkout on the platform\"]', '[]', 'catalog', NULL, '2026-07-22 14:47:54', '2026-08-02 00:33:29'),
(3, 'Social Media', 'social-media', 2, 1, 'storage/media/2026/07/01KY5HC54YZAG4WZA2850D3MZM-medium.webp', 17, 'storage/media/2026/07/01KY5HC54YZAG4WZA2850D3MZM-medium.webp', 17, 'Buy likes, Followers, views and much more', 'Social Media', 'Browse social services, then filter by Growth or Engagement.', '[]', '[]', 'catalog', NULL, '2026-07-22 14:47:54', '2026-08-02 00:36:19'),
(4, 'Website Services', 'website-services', 1, 1, 'storage/media/2026/07/01KY5HC2V6D6DY3APNWHSQ3REA-medium.webp', 11, 'storage/media/2026/07/01KY5HC2V6D6DY3APNWHSQ3REA-medium.webp', 11, 'Online banking, Consignment website, broker and more', 'Website Services', 'From design templates to domains — build your web presence.', '[\"Templates, packages, and domains together\",\"Demos on eligible packages\",\"Straightforward next steps after purchase\"]', '[]', 'catalog', NULL, '2026-07-22 14:47:54', '2026-08-02 00:37:11'),
(5, 'Documents & Receipts', 'business-documents', 5, 1, 'storage/media/2026/07/01KY5HC1JYJBJHEDE24XBVWVBE-medium.webp', 8, 'storage/media/2026/07/01KY5HC1JYJBJHEDE24XBVWVBE-medium.webp', 8, 'Design and download receipt, flight ticket, and custom documents', 'Documents & Receipts', 'Ready-to-edit templates for everyday business paperwork.', '[\"Contracts, HR, and Legal categories\",\"Quick filters on the type page\",\"Editable after purchase\"]', '[]', 'catalog', NULL, '2026-07-22 14:47:54', '2026-08-02 00:38:46'),
(6, 'Trust & Escrow', 'trust-escrow', 6, 1, 'storage/media/2026/07/01KY5HC3S3ADKH48XJQT3GDDEX-medium.webp', 14, 'storage/media/2026/07/01KY5HC3S3ADKH48XJQT3GDDEX-medium.webp', 14, 'Buy and sell digital products with marketplace escrow protection.', 'Trust & Escrow', 'Explore escrow-protected purchases in the marketplace.', '[\"Aligned with marketplace protection\",\"Funds held until delivery confirmation\"]', '[]', 'marketplace_link', 'Open marketplace', '2026-07-22 14:47:54', '2026-08-02 00:34:08');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('2Bl7hrzCVkFszHxLpjQrNnCBKuC4c8Ze0EHrIuzN', NULL, '188.166.118.163', 'Mozilla/5.0 (compatible; ForestEngine/1.0; +https://forestengine.net/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ0RObHFjTWlRUXhlVmJBajhSdzF3ajZnMml6UXRsekRKRjVYaGVkRiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vN3RoLXRyYWRlaHViLm9ubGluZSI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787948450),
('3BmuNPHjeVx3BORSBlwi5En7Jqx3Lt5qp2fVGM7S', NULL, '223.88.126.78', 'User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicWNjS0JJN2hFazZ0bmFyOFByRVJrZW1DMFRwM2c4Y1R4VDVtcDM4YiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vd3d3Ljd0aC10cmFkZWh1Yi5vbmxpbmUiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1787951265),
('49pDqAKxqIFAzRfjFk8ydqNCgGoGC8SdUJGIjoBL', NULL, '74.7.175.158', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36; compatible; OAI-SearchBot/1.4; robots.txt; +https://openai.com/searchbot', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMUx0WXlYZnppck9URDJqRXZIcEo5WlhOc1ZISVBSMjNScWJzZHVDbSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHBzOi8vN3RoLXRyYWRlaHViLm9ubGluZS9yb2JvdHMudHh0IjtzOjU6InJvdXRlIjtzOjY6InJvYm90cyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1788012393),
('8D1Kv6ej9h6mLOJ2YGWnNYqis0znBHvJQKbR726W', NULL, '180.153.236.134', 'User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0; 360Spider', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRnJxR1l1d2xzUEs3eHVUNHV0WFhVNXdESFRpdGRTVlJHbndFVml3YyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vN3RoLXRyYWRlaHViLm9ubGluZSI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787986756),
('9MyeNaawiRpjDz9ObDXJrRQhkS3VMc8B4g6qmcJf', NULL, '2800:300:6af2:de80:cd63:b1f0:e326:e2ed', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiR0wzaVJmZTJEWmZvSnMydnNIVzFhcTJJSUdTQmY5OXVZdEk1Sng0WiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTA6Imh0dHBzOi8vN3RoLXRyYWRlaHViLm9ubGluZS9zZXJ2aWNlcy92aXJ0dWFsX3Bob25lIjtzOjU6InJvdXRlIjtzOjE2OiJzZXJ2aWNlcy5zZWdtZW50Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787977406),
('DXMkNgvsqiVMbNgtmaI9ywd70Y1TUgM2cHRBp12F', NULL, '151.115.91.4', 'python-httpx/0.28.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNnZYaFkwWVVXTXhkZHdSN2Z5czlENENwNkQyTlEyNW1CZm9oUEdGUyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vN3RoLXRyYWRlaHViLm9ubGluZSI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787998409),
('hDtoPbcixk4UahpOWzEz44x6z6h9hObPiI8Gxlcf', NULL, '159.223.206.49', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.3912.51', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSnBxM2hzOUoxSHc2TDBRS0pSTldmclJYOUdwYXljNXJOSXhnMUY0SCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vN3RoLXRyYWRlaHViLm9ubGluZSI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787958207),
('IuntufaAPRKByND2oCswld2y3pUwFhHERjPVa7Vh', 1, '102.89.23.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiWno5MUN0Q3gyZTlHa3ZucWdMa0dWU1JJNUpLQVBoRXB0RUJSazFsMCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTI6Imh0dHBzOi8vN3RoLXRyYWRlaHViLm9ubGluZS9hZG1pbi9zZXJ2aWNlLWNhdGVnb3JpZXMiO3M6NToicm91dGUiO3M6MjQ6ImFkbWluLnNlcnZpY2UtY2F0ZWdvcmllcyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czo1OiJhZG1pbiI7YToxOntzOjg6Im92ZXJ2aWV3IjthOjE6e3M6NToicmFuZ2UiO3M6MzoiMjRoIjt9fX0=', 1788015649),
('j89JwX0XhwskCgp4bEYk4pFwL1P5aP2FlMiBdYGx', NULL, '66.249.66.77', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUTl4Q2JVblVoZEQyOEd1RXNxQURPTjdXS1poSVlsQVdFb21pU0ZMQSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3Ljd0aC10cmFkZWh1Yi5vbmxpbmUvcm9ib3RzLnR4dCI7czo1OiJyb3V0ZSI7czo2OiJyb2JvdHMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1787991668),
('L2OsURg1cwk1zpjVgKyuLKN9SdmQ4PmFlQ2bLi7b', NULL, '51.89.129.70', 'Mozilla/5.0 (compatible; AhrefsBot/7.0; +http://ahrefs.com/robot/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMTA3Z0YyWUNZWGVvMDQxWjh4TTEwRWZiREE3SnRuVHdTWjR2NklUOCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDM6Imh0dHBzOi8vN3RoLXRyYWRlaHViLm9ubGluZS9mb3Jnb3QtcGFzc3dvcmQiO3M6NToicm91dGUiO3M6MTY6InBhc3N3b3JkLnJlcXVlc3QiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1787959450),
('lLEl3rJbnWWog1dNZOSaWfO9Fl3cp8lBvrMSwzBB', NULL, '66.249.68.100', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ1R2UDJCU3VqdDRGRTQ0QXF0SXplWVk2NXdoZlJuWnlCUlp6cWx5ayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vd3d3Ljd0aC10cmFkZWh1Yi5vbmxpbmUvcm9ib3RzLnR4dCI7czo1OiJyb3V0ZSI7czo2OiJyb2JvdHMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1787955192),
('mkwbqIccpVU4EAw0Y0KzanfhLWR6TDo06TDyCY7L', NULL, '198.244.168.226', 'Mozilla/5.0 (compatible; AhrefsBot/7.0; +http://ahrefs.com/robot/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiekJmUGJiZnB1bGRkaUp3SkV5Vld0UUVsU3pvcFYzUkNPZ2VLVjdYRyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vN3RoLXRyYWRlaHViLm9ubGluZSI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787954369),
('O8FouFsa7NrDA4yih53Ud9DscDfKl3XT7V9DOEbI', NULL, '66.249.66.78', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.7922.173 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiU2RMc3Azb1VNRmYyaWUzR2tGSjc4dVlySFRwSUZKRTBNT21lbkp1UyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjI6Imh0dHBzOi8vd3d3Ljd0aC10cmFkZWh1Yi5vbmxpbmUvbWFya2V0cGxhY2UvYXRsYXN0ZWNoLXRpa3Rvay0yIjtzOjU6InJvdXRlIjtzOjE2OiJtYXJrZXRwbGFjZS5zaG93Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787991668),
('oiCg3KZTJgy9qzzSzqgjRKVp4zCROsBUUyXUiAfb', NULL, '5.39.1.244', 'Mozilla/5.0 (compatible; AhrefsBot/7.0; +http://ahrefs.com/robot/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWmgzdkpVcE1FZGdrVnJDeDBaZmVxRDF1T2NpMWVaQzhrRWYxaHhPdSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHBzOi8vN3RoLXRyYWRlaHViLm9ubGluZS9yb2JvdHMudHh0IjtzOjU6InJvdXRlIjtzOjY6InJvYm90cyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1787954368),
('Q9EoeFgQ10wmX3QHmWneYR2FfQlkjT0nxaA2J6Ue', NULL, '101.26.28.187', 'User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiY1oxdHNKQnM1VVlMc0Vidlp2a29JeXB2azlyakQxSGZlZjV2eFdQcyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vN3RoLXRyYWRlaHViLm9ubGluZSI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787988590),
('QQuBu7hmTj4U7vdlV8ijkFRhJS6rmGqp5cQx7iRU', NULL, '2a02:4780:40:c0de::2a', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYVFMQ2RrVUdSNEFtRmNvak1xeE9WQ29sSXRxT1N4cmlkdnRDQnB2bSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vN3RoLXRyYWRlaHViLm9ubGluZSI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787994728),
('TQTi7v3B3rMwiOPfQ4uzb2LtWIP4Kf5016RrZxWg', NULL, '168.100.149.248', 'Mozilla/5.0 (compatible; AhrefsBot/7.0; +http://ahrefs.com/robot/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVnZ0aGpIa3Jqbk9ZQTA2dWxxbHpZalVHckpXRXpMbk52d2poQkluMiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHBzOi8vN3RoLXRyYWRlaHViLm9ubGluZS9yb2JvdHMudHh0IjtzOjU6InJvdXRlIjtzOjY6InJvYm90cyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1787954370),
('WarAX8ZgbWn3TIYkxNpmC9pRAc7lzzKRabcFf0zf', NULL, '170.247.40.239', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMEI3WjZiR20xY3E1WmZLTml6RThaN1l1ZlZWd2RXaHZHWDd1VVc3SiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjQ6Imh0dHBzOi8vN3RoLXRyYWRlaHViLm9ubGluZS9zZXJ2aWNlcy9jb21tdW5pY2F0aW9uL3ZpcnR1YWxfcGhvbmUiO3M6NToicm91dGUiO3M6MTM6InNlcnZpY2VzLnR5cGUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1787977411),
('Xp2BvrMwRJJBNG5gE0RUEAbaKvEEK6mDz7sj0KCP', NULL, '102.89.23.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSE5XNFRYMWhtRXBoVm5JaXpQVUpOZTB1aHZGY1FzbEZsRDZ4cjhjYSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vN3RoLXRyYWRlaHViLm9ubGluZSI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1788016341),
('Yeq0nnblzSMJSJY6UxEkuoajDF6JLjB72jGFHJtT', NULL, '180.153.236.110', 'User-Agent:Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Mobile Safari/537.36; 360Spider', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMmI4aFUzbnhQRDVpQ0drdXJmN21ZMjFyZWRCZk5XNVNzZEc2YmFGSSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vN3RoLXRyYWRlaHViLm9ubGluZSI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787986760),
('yLtPez9xfzLZmg76uEuAjTSupgo46JA0MY2j0ieZ', NULL, '151.115.91.4', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.3', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRnJteG1rVU1BWklKUVM1aHRFUkY3b0hOQ1FuVlBUUzJNWjJ5anlZVSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vN3RoLXRyYWRlaHViLm9ubGluZSI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787998410),
('YxIac1LwD93V91fBdzkgADQlJV5e0kfEyvQBRoPi', NULL, '66.249.68.97', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.7922.173 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiakRkNUNKY2hidzVXYzFxdHNsb05CNFIyZkNkcnhzYUhNalc2ZERSdSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Njg6Imh0dHBzOi8vd3d3Ljd0aC10cmFkZWh1Yi5vbmxpbmUvc2VydmljZXMvY29tbXVuaWNhdGlvbi92aXJ0dWFsX3Bob25lIjtzOjU6InJvdXRlIjtzOjEzOiJzZXJ2aWNlcy50eXBlIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787955196);

-- --------------------------------------------------------

--
-- Table structure for table `social_links`
--

CREATE TABLE `social_links` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `platform` varchar(60) NOT NULL,
  `url` varchar(255) NOT NULL,
  `icon` varchar(60) DEFAULT NULL,
  `icon_media_id` bigint(20) UNSIGNED DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `social_links`
--

INSERT INTO `social_links` (`id`, `platform`, `url`, `icon`, `icon_media_id`, `enabled`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Tiktok', 'https://www.tiktok.com/@7th.trade.hub', 'Tiktok', NULL, 1, 1, '2026-08-01 03:12:58', '2026-08-01 03:12:58');

-- --------------------------------------------------------

--
-- Table structure for table `support_attachments`
--

CREATE TABLE `support_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `support_ticket_id` bigint(20) UNSIGNED NOT NULL,
  `support_ticket_reply_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `disk` varchar(32) NOT NULL DEFAULT 'local',
  `path` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime` varchar(120) NOT NULL,
  `size` bigint(20) UNSIGNED NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `category` varchar(30) NOT NULL DEFAULT 'other',
  `subject` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `priority` varchar(20) NOT NULL DEFAULT 'normal',
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `user_id`, `category`, `subject`, `body`, `priority`, `assigned_to`, `status`, `created_at`, `updated_at`) VALUES
(41, 16, 'payment', 'not seed my money', 'i dpdoedc and did nto see', 'normal', 1, 'closed', '2026-08-01 01:00:31', '2026-08-01 23:53:59');

-- --------------------------------------------------------

--
-- Table structure for table `support_ticket_replies`
--

CREATE TABLE `support_ticket_replies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `support_ticket_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `body` text NOT NULL,
  `is_staff` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `support_ticket_replies`
--

INSERT INTO `support_ticket_replies` (`id`, `support_ticket_id`, `user_id`, `body`, `is_staff`, `created_at`, `updated_at`) VALUES
(129, 41, 16, 'still waitng', 0, '2026-08-01 01:00:55', '2026-08-01 01:00:55');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'platform_fee_percent', '2.5', '2026-07-15 00:19:00', '2026-07-15 00:19:00'),
(2, 'withdrawal_min_amount', '100', '2026-07-15 00:19:00', '2026-07-15 00:19:00'),
(3, 'withdrawal_max_amount', '1000000', '2026-07-15 00:19:00', '2026-07-15 00:19:00'),
(4, 'deposit_min_amount', '100', '2026-07-15 00:19:00', '2026-07-15 00:19:00'),
(5, 'site_name', '7th Trade Hub', '2026-07-31 23:57:52', '2026-07-31 23:57:52'),
(6, 'site_short_name', 'Trade Hub', '2026-07-31 23:57:52', '2026-07-31 23:57:52'),
(7, 'site_heading', 'The Ultimate Digital Service Marketplace', '2026-07-31 23:57:52', '2026-07-31 23:57:52'),
(8, 'site_tagline', 'Connecting markets, empowering traders.', '2026-07-31 23:57:52', '2026-07-31 23:57:52'),
(9, 'site_meta_description', 'Nigerian Digital marketplace to buy, sell and subscribe to digital products and services.', '2026-07-31 23:57:52', '2026-08-02 13:03:48'),
(10, 'favicon_media_id', '7', '2026-07-31 23:57:52', '2026-08-02 01:23:00'),
(11, 'logo_light_media_id', '20', '2026-07-31 23:57:52', '2026-08-01 02:54:00'),
(12, 'logo_dark_media_id', '19', '2026-07-31 23:57:52', '2026-08-01 02:50:03'),
(13, 'contact_address_street', '177 Ago Palace Way,, Lagos , Lagos', '2026-07-31 23:57:52', '2026-08-01 02:55:32'),
(14, 'contact_address_city', 'Oshodi Isolo', '2026-07-31 23:57:52', '2026-08-01 02:55:32'),
(15, 'contact_address_state', 'Lagos', '2026-07-31 23:57:52', '2026-08-01 02:55:32'),
(16, 'contact_address_country', 'Nigeria', '2026-07-31 23:57:52', '2026-08-01 02:55:32'),
(17, 'contact_address_postal', '110224', '2026-07-31 23:57:52', '2026-08-01 02:55:32'),
(18, 'contact_latitude', '', '2026-07-31 23:57:52', '2026-07-31 23:57:52'),
(19, 'contact_longitude', '', '2026-07-31 23:57:52', '2026-07-31 23:57:52'),
(20, 'contact_maps_url', '', '2026-07-31 23:57:52', '2026-07-31 23:57:52'),
(21, 'contact_maps_embed_url', '', '2026-07-31 23:57:52', '2026-07-31 23:57:52'),
(22, 'contact_phone_support', '09122083549', '2026-07-31 23:57:52', '2026-08-01 02:55:32'),
(23, 'contact_phone_general', '09122083549', '2026-07-31 23:57:52', '2026-08-01 02:55:32'),
(24, 'contact_phone_whatsapp', '09122083549', '2026-07-31 23:57:52', '2026-08-01 02:55:32'),
(25, 'contact_support_hours', '', '2026-07-31 23:57:52', '2026-07-31 23:57:52'),
(26, 'contact_timezone', 'Africa/Lagos', '2026-07-31 23:57:52', '2026-07-31 23:57:52'),
(27, 'contact_business_hours', '24/4', '2026-07-31 23:57:52', '2026-08-01 02:55:32'),
(28, 'contact_registration_number', '', '2026-07-31 23:57:52', '2026-07-31 23:57:52'),
(29, 'contact_vat_number', '', '2026-07-31 23:57:52', '2026-07-31 23:57:52'),
(30, 'contact_company_number', '', '2026-07-31 23:57:52', '2026-07-31 23:57:52'),
(31, 'kyc_required', '0', '2026-08-01 00:48:59', '2026-08-01 00:48:59'),
(32, 'contact_phone', '09122083549', '2026-08-01 02:55:32', '2026-08-01 02:55:32'),
(33, 'live_chat_provider', 'none', '2026-08-01 02:55:32', '2026-08-01 02:55:32'),
(34, 'smartsupp_key', '', '2026-08-01 02:55:32', '2026-08-01 02:55:32'),
(35, 'jivo_widget_id', '', '2026-08-01 02:55:32', '2026-08-01 02:55:32'),
(36, 'verification_google', '', '2026-08-01 23:38:41', '2026-08-01 23:38:41'),
(37, 'verification_bing', '', '2026-08-01 23:38:41', '2026-08-01 23:38:41'),
(38, 'verification_facebook', '', '2026-08-01 23:38:41', '2026-08-01 23:38:41');

-- --------------------------------------------------------

--
-- Table structure for table `tracking_scripts`
--

CREATE TABLE `tracking_scripts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `location` varchar(20) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `code` longtext NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `wallet_id` bigint(20) UNSIGNED DEFAULT NULL,
  `wallet_funding_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `withdrawal_id` bigint(20) UNSIGNED DEFAULT NULL,
  `escrow_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reverses_transaction_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference` varchar(32) NOT NULL,
  `type` varchar(40) NOT NULL,
  `label` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'NGN',
  `asset_type` varchar(20) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `country` varchar(2) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `kyc_level` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `is_suspended` tinyint(1) NOT NULL DEFAULT 0,
  `suspended_at` timestamp NULL DEFAULT NULL,
  `suspended_by` bigint(20) UNSIGNED DEFAULT NULL,
  `anonymized_at` timestamp NULL DEFAULT NULL,
  `terms_accepted_at` timestamp NULL DEFAULT NULL,
  `profile_completed_at` timestamp NULL DEFAULT NULL,
  `theme_preference` varchar(16) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `password_set_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `country`, `bio`, `avatar`, `kyc_level`, `is_suspended`, `suspended_at`, `suspended_by`, `anonymized_at`, `terms_accepted_at`, `profile_completed_at`, `theme_preference`, `email_verified_at`, `password`, `password_set_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin', 'support@7th-tradehub.online', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 'light', '2026-07-21 13:00:54', '$2y$12$4odsEHWr01xupcJIxpIP2.Wy7OKvU8wJNg5dC22Trpy9TTTueHlqW', '2026-08-01 19:29:08', NULL, '2026-07-15 01:01:56', '2026-08-02 23:45:53'),
(2, 'Platform Wallet', 'platform_wallet', 'platform-wallet@internal.7thtradehub', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 01:01:56', '$2y$12$ORilHr2Es0GbNhv4gl4SV.UZn.8QpB0.aAgQJm7LrZ8c.dWsNHz0W', '2026-08-01 19:29:08', NULL, '2026-07-15 01:01:56', '2026-07-15 01:01:56'),
(13, 'Deleted User', 'deleted_13', 'deleted+13@invalid.local', NULL, NULL, NULL, NULL, 0, 1, '2026-07-21 23:02:04', 1, '2026-07-21 23:02:20', '2026-07-20 01:36:14', NULL, NULL, NULL, '$2y$12$GVALtiWEdF8a316qxDL3OubHcU107gVThZ/nVZp2xrm62RaNHNv9W', '2026-08-01 19:29:08', NULL, '2026-07-20 01:36:14', '2026-07-21 23:02:20'),
(15, 'Deleted User', 'deleted_15', 'deleted+15@invalid.local', NULL, NULL, NULL, NULL, 0, 1, '2026-08-01 19:59:20', 1, '2026-08-01 20:11:57', '2026-07-23 01:37:30', NULL, 'system', NULL, '$2y$12$qojo83MaGg7WMyJsnbEeFuxLVtcpox0D/5H5uuN8KhJF.FwinYnBW', NULL, NULL, '2026-07-23 01:37:30', '2026-08-01 20:11:57'),
(16, 'Deleted User', 'deleted_16', 'deleted+16@invalid.local', NULL, NULL, NULL, NULL, 0, 1, '2026-08-01 19:51:32', 1, '2026-08-01 20:01:11', '2026-07-23 03:04:19', NULL, 'system', NULL, '$2y$12$NsdMivPvSEVKQ5Y15v6wPu3wx/A4rOlqYGO.uc2TJs7oMyiV49Kka', NULL, NULL, '2026-07-23 03:04:20', '2026-08-01 20:01:11'),
(42, 'carter tech', 'Carter44', 'mr.carter.tech07@gmail.com', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, '2026-08-01 03:23:11', NULL, 'light', '2026-08-01 03:24:00', '$2y$12$cx1Do/Suzf2aqjXGg4/V0OlF3b582L0vQUNk3f5lufqXNm56fYfFe', '2026-08-01 19:29:08', NULL, '2026-08-01 03:23:11', '2026-08-02 09:01:40');

-- --------------------------------------------------------

--
-- Table structure for table `user_activity`
--

CREATE TABLE `user_activity` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(40) NOT NULL DEFAULT 'viewed',
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `context_key` varchar(120) DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `occurred_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_activity`
--

INSERT INTO `user_activity` (`id`, `user_id`, `action`, `subject_type`, `subject_id`, `context_key`, `meta`, `occurred_at`, `created_at`, `updated_at`) VALUES
(400, 16, 'viewed', NULL, NULL, 'discover.marketplace', NULL, '2026-07-24 21:51:49', '2026-07-24 21:51:49', '2026-07-24 21:51:49'),
(401, 16, 'viewed', 'App\\Models\\Listing', 47, 'listing.viewed', NULL, '2026-07-24 21:52:17', '2026-07-24 21:52:17', '2026-07-24 21:52:17'),
(402, 16, 'viewed', NULL, NULL, 'discover.services', NULL, '2026-07-24 21:52:43', '2026-07-24 21:52:43', '2026-07-24 21:52:43'),
(403, 16, 'viewed', NULL, NULL, 'discover.marketplace', NULL, '2026-08-01 00:54:12', '2026-08-01 00:54:12', '2026-08-01 00:54:12'),
(404, 16, 'viewed', NULL, NULL, 'discover.services', NULL, '2026-08-01 00:55:53', '2026-08-01 00:55:53', '2026-08-01 00:55:53'),
(405, 16, 'opened', 'App\\Models\\SupportTicket', 41, 'ticket.opened', NULL, '2026-08-01 01:00:32', '2026-08-01 01:00:32', '2026-08-01 01:00:32'),
(406, 16, 'event', NULL, NULL, 'event.ticket_opened', '{\"ticket_id\":41}', '2026-08-01 01:00:32', '2026-08-01 01:00:32', '2026-08-01 01:00:32'),
(407, 16, 'event', NULL, NULL, 'event.ticket_replied', '{\"ticket_id\":41,\"is_admin_reply\":false}', '2026-08-01 01:00:55', '2026-08-01 01:00:55', '2026-08-01 01:00:55'),
(408, 16, 'viewed', NULL, NULL, 'discover.marketplace', NULL, '2026-08-01 01:05:33', '2026-08-01 01:05:33', '2026-08-01 01:05:33'),
(409, 16, 'viewed', NULL, NULL, 'discover.services', NULL, '2026-08-01 01:06:58', '2026-08-01 01:06:58', '2026-08-01 01:06:58'),
(410, 16, 'viewed', NULL, NULL, 'services.hub', NULL, '2026-08-01 01:56:33', '2026-08-01 01:56:33', '2026-08-01 01:56:33'),
(411, 16, 'viewed', NULL, NULL, 'marketplace.hub', NULL, '2026-08-01 01:56:44', '2026-08-01 01:56:44', '2026-08-01 01:56:44'),
(412, 16, 'viewed', NULL, NULL, 'marketplace.hub', NULL, '2026-08-01 01:56:50', '2026-08-01 01:56:50', '2026-08-01 01:56:50'),
(413, 16, 'viewed', NULL, NULL, 'marketplace.hub', NULL, '2026-08-01 01:57:18', '2026-08-01 01:57:18', '2026-08-01 01:57:18'),
(414, 16, 'viewed', NULL, NULL, 'services.hub', NULL, '2026-08-01 03:02:35', '2026-08-01 03:02:35', '2026-08-01 03:02:35'),
(415, 16, 'viewed', NULL, NULL, 'marketplace.hub', NULL, '2026-08-01 03:03:07', '2026-08-01 03:03:07', '2026-08-01 03:03:07'),
(416, 16, 'viewed', 'App\\Models\\Listing', 49, 'listing.checkout', NULL, '2026-08-01 03:03:19', '2026-08-01 03:03:19', '2026-08-01 03:03:19'),
(417, 16, 'viewed', NULL, NULL, 'services.hub', NULL, '2026-08-01 03:14:12', '2026-08-01 03:14:12', '2026-08-01 03:14:12'),
(418, 16, 'viewed', NULL, NULL, 'services.hub', NULL, '2026-08-01 03:16:38', '2026-08-01 03:16:38', '2026-08-01 03:16:38'),
(419, 16, 'viewed', 'App\\Models\\PlatformProduct', 1, 'service.viewed', NULL, '2026-08-01 03:17:50', '2026-08-01 03:17:50', '2026-08-01 03:17:50'),
(420, 42, 'event', NULL, NULL, 'event.sign_up', '{\"user_id\":42}', '2026-08-01 03:23:11', '2026-08-01 03:23:11', '2026-08-01 03:23:11'),
(421, 42, 'viewed', NULL, NULL, 'services.hub', NULL, '2026-08-01 03:24:54', '2026-08-01 03:24:54', '2026-08-01 03:24:54'),
(422, 16, 'viewed', NULL, NULL, 'marketplace.hub', NULL, '2026-08-01 03:26:30', '2026-08-01 03:26:30', '2026-08-01 03:26:30'),
(423, 42, 'viewed', NULL, NULL, 'services.hub', NULL, '2026-08-01 03:27:08', '2026-08-01 03:27:08', '2026-08-01 03:27:08'),
(424, 42, 'viewed', 'App\\Models\\PlatformProduct', 37, 'service.checkout', NULL, '2026-08-01 03:29:20', '2026-08-01 03:29:20', '2026-08-01 03:29:20'),
(425, 42, 'viewed', NULL, NULL, 'services.hub', NULL, '2026-08-01 03:29:46', '2026-08-01 03:29:46', '2026-08-01 03:29:46'),
(426, 42, 'viewed', NULL, NULL, 'services.browse.website-services', NULL, '2026-08-01 03:30:11', '2026-08-01 03:30:11', '2026-08-01 03:30:11'),
(427, 42, 'viewed', NULL, NULL, 'services.browse.website-services', NULL, '2026-08-01 03:30:33', '2026-08-01 03:30:33', '2026-08-01 03:30:33'),
(428, 42, 'viewed', NULL, NULL, 'marketplace.hub', NULL, '2026-08-01 03:30:51', '2026-08-01 03:30:51', '2026-08-01 03:30:51'),
(429, 42, 'viewed', NULL, NULL, 'marketplace.hub', NULL, '2026-08-01 03:31:06', '2026-08-01 03:31:06', '2026-08-01 03:31:06'),
(430, 42, 'viewed', 'App\\Models\\Listing', 48, 'listing.checkout', NULL, '2026-08-01 03:31:11', '2026-08-01 03:31:11', '2026-08-01 03:31:11'),
(431, 42, 'viewed', NULL, NULL, 'marketplace.hub', NULL, '2026-08-01 03:34:03', '2026-08-01 03:34:03', '2026-08-01 03:34:03'),
(432, 42, 'viewed', 'App\\Models\\Listing', 50, 'listing.checkout', NULL, '2026-08-01 03:34:10', '2026-08-01 03:34:10', '2026-08-01 03:34:10'),
(433, 42, 'viewed', NULL, NULL, 'marketplace.hub', NULL, '2026-08-01 21:25:16', '2026-08-01 21:25:16', '2026-08-01 21:25:16'),
(434, 42, 'viewed', NULL, NULL, 'marketplace.hub', NULL, '2026-08-02 00:28:51', '2026-08-02 00:28:51', '2026-08-02 00:28:51'),
(435, 42, 'viewed', NULL, NULL, 'services.hub', NULL, '2026-08-02 00:29:32', '2026-08-02 00:29:32', '2026-08-02 00:29:32'),
(436, 42, 'viewed', NULL, NULL, 'services.hub', NULL, '2026-08-02 00:34:54', '2026-08-02 00:34:54', '2026-08-02 00:34:54'),
(437, 42, 'viewed', NULL, NULL, 'services.hub', NULL, '2026-08-02 00:35:42', '2026-08-02 00:35:42', '2026-08-02 00:35:42'),
(438, 42, 'viewed', NULL, NULL, 'services.hub', NULL, '2026-08-02 00:38:13', '2026-08-02 00:38:13', '2026-08-02 00:38:13'),
(439, 1, 'viewed', 'App\\Models\\PlatformProduct', 55, 'service.checkout', NULL, '2026-08-02 13:10:02', '2026-08-02 13:10:02', '2026-08-02 13:10:02'),
(440, 42, 'viewed', NULL, NULL, 'marketplace.hub', NULL, '2026-08-02 17:58:13', '2026-08-02 17:58:13', '2026-08-02 17:58:13'),
(441, 42, 'viewed', NULL, NULL, 'services.hub', NULL, '2026-08-03 00:00:47', '2026-08-03 00:00:47', '2026-08-03 00:00:47'),
(442, 42, 'viewed', NULL, NULL, 'services.browse.website-services', NULL, '2026-08-03 00:01:05', '2026-08-03 00:01:05', '2026-08-03 00:01:05'),
(443, 42, 'viewed', NULL, NULL, 'services.browse.website-services', NULL, '2026-08-03 00:01:10', '2026-08-03 00:01:10', '2026-08-03 00:01:10');

-- --------------------------------------------------------

--
-- Table structure for table `user_auth_providers`
--

CREATE TABLE `user_auth_providers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `provider` varchar(40) NOT NULL,
  `provider_user_id` varchar(191) NOT NULL,
  `provider_email` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_bank_accounts`
--

CREATE TABLE `user_bank_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `bank_code` varchar(20) NOT NULL,
  `account_number` text NOT NULL,
  `verified_name` varchar(150) NOT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` varchar(40) NOT NULL DEFAULT 'monnify',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

CREATE TABLE `user_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(40) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'user',
  `balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `locked_balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'NGN',
  `gateway_subaccount_id` varchar(255) DEFAULT NULL,
  `reserved_account_number` varchar(30) DEFAULT NULL,
  `reserved_bank_name` varchar(100) DEFAULT NULL,
  `reserved_account_reference` varchar(100) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `type`, `balance`, `locked_balance`, `currency`, `gateway_subaccount_id`, `reserved_account_number`, `reserved_bank_name`, `reserved_account_reference`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'platform', 382700.00, 0.00, 'NGN', 'platform', NULL, NULL, NULL, 'active', '2026-07-15 01:01:56', '2026-07-23 23:42:07'),
(32, 16, 'user', 0.00, 0.00, 'NGN', 'manual_wallet-create-16', NULL, NULL, NULL, 'active', '2026-08-01 01:57:34', '2026-08-01 01:57:34'),
(33, 42, 'user', 0.00, 0.00, 'NGN', 'manual_wallet-create-42', NULL, NULL, NULL, 'active', '2026-08-01 03:29:29', '2026-08-01 03:29:29'),
(34, 1, 'user', 0.00, 0.00, 'NGN', 'manual_wallet-create-1', NULL, NULL, NULL, 'active', '2026-08-02 01:28:31', '2026-08-02 01:28:31');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_balance_history`
--

CREATE TABLE `wallet_balance_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `crypto_deposit_wallet_id` bigint(20) UNSIGNED NOT NULL,
  `balance` decimal(28,10) NOT NULL,
  `recorded_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallet_balance_history`
--

INSERT INTO `wallet_balance_history` (`id`, `crypto_deposit_wallet_id`, `balance`, `recorded_at`, `created_at`, `updated_at`) VALUES
(1, 1, 0.0000000000, '2026-08-02 16:47:28', '2026-08-02 16:47:28', '2026-08-02 16:47:28'),
(2, 5, 0.0000000000, '2026-08-02 23:55:43', '2026-08-02 23:55:43', '2026-08-02 23:55:43'),
(3, 2, 78.2418540000, '2026-08-02 23:55:43', '2026-08-02 23:55:43', '2026-08-02 23:55:43'),
(4, 3, 0.0000000000, '2026-08-02 23:55:44', '2026-08-02 23:55:44', '2026-08-02 23:55:44'),
(5, 4, 0.0000000000, '2026-08-02 23:55:44', '2026-08-02 23:55:44', '2026-08-02 23:55:44');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_fundings`
--

CREATE TABLE `wallet_fundings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `wallet_id` bigint(20) UNSIGNED NOT NULL,
  `method` varchar(30) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'NGN',
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `internal_status` varchar(40) DEFAULT NULL,
  `provider_status` varchar(40) DEFAULT NULL,
  `provider` varchar(40) DEFAULT NULL,
  `provider_payment_reference` varchar(100) DEFAULT NULL,
  `provider_transaction_reference` varchar(100) DEFAULT NULL,
  `checkout_url` text DEFAULT NULL,
  `checkout_expires_at` timestamp NULL DEFAULT NULL,
  `reserved_account_number` varchar(30) DEFAULT NULL,
  `reserved_bank_name` varchar(100) DEFAULT NULL,
  `reserved_account_reference` varchar(100) DEFAULT NULL,
  `reference` varchar(32) NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_ip` varchar(45) DEFAULT NULL,
  `approved_device` varchar(255) DEFAULT NULL,
  `approved_reason` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `reversed_at` timestamp NULL DEFAULT NULL,
  `reversal_transaction_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_holds`
--

CREATE TABLE `wallet_holds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `wallet_id` bigint(20) UNSIGNED NOT NULL,
  `reason_type` varchar(40) NOT NULL,
  `reason_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `expires_at` timestamp NULL DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `watchlists`
--

CREATE TABLE `watchlists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `withdrawals`
--

CREATE TABLE `withdrawals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `wallet_id` bigint(20) UNSIGNED NOT NULL,
  `user_bank_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'NGN',
  `bank_name` varchar(255) NOT NULL,
  `bank_code` varchar(20) DEFAULT NULL,
  `account_number` text NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `internal_status` varchar(40) DEFAULT NULL,
  `provider_status` varchar(40) DEFAULT NULL,
  `provider_payout_reference` varchar(100) DEFAULT NULL,
  `reference` varchar(32) NOT NULL,
  `admin_notes` text DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_ip` varchar(45) DEFAULT NULL,
  `approval_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_notifications_type_created_at_index` (`type`,`created_at`);

--
-- Indexes for table `analytics_ga_snapshots`
--
ALTER TABLE `analytics_ga_snapshots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `analytics_ga_snapshots_metric_period_start_period_end_index` (`metric`,`period_start`,`period_end`);

--
-- Indexes for table `analytics_kpi_snapshots`
--
ALTER TABLE `analytics_kpi_snapshots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `analytics_kpi_snapshots_kpi_key_period_index` (`kpi_key`,`period`);

--
-- Indexes for table `analytics_providers`
--
ALTER TABLE `analytics_providers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `analytics_providers_provider_unique` (`provider`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_created_at_index` (`created_at`),
  ADD KEY `audit_logs_action_index` (`action`),
  ADD KEY `audit_logs_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `audit_logs_module_index` (`module`),
  ADD KEY `audit_logs_actor_id_index` (`actor_id`),
  ADD KEY `audit_logs_correlation_id_index` (`correlation_id`),
  ADD KEY `audit_logs_module_action_index` (`module`,`action`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `catalog_page_contents`
--
ALTER TABLE `catalog_page_contents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `catalog_page_contents_scope_key_unique` (`scope`,`key`),
  ADD KEY `catalog_page_contents_banner_media_id_foreign` (`banner_media_id`),
  ADD KEY `catalog_page_contents_card_media_id_foreign` (`card_media_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`),
  ADD KEY `categories_parent_id_foreign` (`parent_id`),
  ADD KEY `categories_banner_media_id_index` (`banner_media_id`),
  ADD KEY `categories_card_media_id_index` (`card_media_id`);

--
-- Indexes for table `crypto_deposit_wallets`
--
ALTER TABLE `crypto_deposit_wallets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `crypto_deposit_wallets_coin_network_is_active_index` (`coin`,`network`,`is_active`);

--
-- Indexes for table `crypto_sell_requests`
--
ALTER TABLE `crypto_sell_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `crypto_sell_requests_tx_hash_unique` (`tx_hash`),
  ADD UNIQUE KEY `crypto_sell_requests_tracking_code_unique` (`tracking_code`),
  ADD KEY `crypto_sell_requests_user_id_foreign` (`user_id`),
  ADD KEY `crypto_sell_requests_wallet_id_foreign` (`wallet_id`),
  ADD KEY `crypto_sell_requests_crypto_deposit_wallet_id_foreign` (`crypto_deposit_wallet_id`);

--
-- Indexes for table `demo_batches`
--
ALTER TABLE `demo_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `demo_batch_records`
--
ALTER TABLE `demo_batch_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `demo_batch_records_unique` (`demo_batch_id`,`record_type`,`record_id`),
  ADD KEY `demo_batch_records_record_type_record_id_index` (`record_type`,`record_id`);

--
-- Indexes for table `email_delivery_attempts`
--
ALTER TABLE `email_delivery_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_delivery_attempts_correlation_id_index` (`correlation_id`),
  ADD KEY `email_delivery_attempts_provider_index` (`provider`),
  ADD KEY `email_delivery_attempts_recipient_index` (`recipient`),
  ADD KEY `email_delivery_attempts_template_key_index` (`template_key`),
  ADD KEY `email_delivery_attempts_purpose_index` (`purpose`),
  ADD KEY `email_delivery_attempts_message_id_index` (`message_id`),
  ADD KEY `email_delivery_attempts_delivery_status_index` (`delivery_status`),
  ADD KEY `email_delivery_attempts_created_at_index` (`created_at`);

--
-- Indexes for table `email_identities`
--
ALTER TABLE `email_identities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_identities_profile_unique` (`profile`);

--
-- Indexes for table `email_verification_codes`
--
ALTER TABLE `email_verification_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_verification_codes_user_id_foreign` (`user_id`);

--
-- Indexes for table `escrows`
--
ALTER TABLE `escrows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `escrows_order_id_unique` (`order_id`),
  ADD KEY `escrows_buyer_wallet_id_foreign` (`buyer_wallet_id`);

--
-- Indexes for table `exchange_rates`
--
ALTER TABLE `exchange_rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exchange_rates_asset_unique` (`asset`);

--
-- Indexes for table `exchange_rate_history`
--
ALTER TABLE `exchange_rate_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exchange_rate_history_recorded_at_source_index` (`recorded_at`,`source`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `favorites_user_favoritable_unique` (`user_id`,`favoritable_type`,`favoritable_id`),
  ADD KEY `favorites_favoritable_index` (`favoritable_type`,`favoritable_id`);

--
-- Indexes for table `gateway_operations`
--
ALTER TABLE `gateway_operations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gateway_operations_idempotency_key_unique` (`idempotency_key`),
  ADD KEY `gateway_operations_provider_operation_status_index` (`provider`,`operation`,`status`),
  ADD KEY `gateway_operations_user_id_foreign` (`user_id`),
  ADD KEY `gateway_operations_wallet_id_foreign` (`wallet_id`);

--
-- Indexes for table `incoming_crypto_transactions`
--
ALTER TABLE `incoming_crypto_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `incoming_crypto_transactions_tx_hash_unique` (`tx_hash`),
  ADD KEY `incoming_crypto_transactions_matched_order_id_foreign` (`matched_order_id`),
  ADD KEY `incoming_crypto_transactions_wallet_address_coin_network_index` (`wallet_address`,`coin`,`network`),
  ADD KEY `incoming_crypto_transactions_status_detected_at_index` (`status`,`detected_at`);

--
-- Indexes for table `integration_providers`
--
ALTER TABLE `integration_providers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `integration_providers_provider_unique` (`provider`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kyc_submissions`
--
ALTER TABLE `kyc_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kyc_submissions_user_id_foreign` (`user_id`);

--
-- Indexes for table `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `listings_slug_unique` (`slug`),
  ADD KEY `listings_user_id_foreign` (`user_id`),
  ADD KEY `listings_category_id_foreign` (`category_id`),
  ADD KEY `listings_marketplace_product_id_foreign` (`marketplace_product_id`),
  ADD KEY `listings_browse_product_index` (`status`,`is_active`,`marketplace_product_id`),
  ADD KEY `listings_browse_created_index` (`status`,`is_active`,`created_at`);

--
-- Indexes for table `listing_versions`
--
ALTER TABLE `listing_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `listing_versions_listing_id_foreign` (`listing_id`);

--
-- Indexes for table `marketplace_products`
--
ALTER TABLE `marketplace_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `marketplace_products_slug_unique` (`slug`),
  ADD KEY `marketplace_products_banner_media_id_index` (`banner_media_id`),
  ADD KEY `marketplace_products_card_media_id_index` (`card_media_id`),
  ADD KEY `marketplace_products_category_id_is_active_index` (`category_id`,`is_active`);

--
-- Indexes for table `media_assets`
--
ALTER TABLE `media_assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `media_assets_uuid_unique` (`uuid`),
  ADD KEY `media_assets_uploaded_by_foreign` (`uploaded_by`),
  ADD KEY `media_assets_type_index` (`type`),
  ADD KEY `media_assets_checksum_index` (`checksum`),
  ADD KEY `media_assets_collection_index` (`collection`),
  ADD KEY `media_assets_brand_key_index` (`brand_key`);

--
-- Indexes for table `media_usages`
--
ALTER TABLE `media_usages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `media_usages_unique` (`media_asset_id`,`usable_type`,`usable_id`,`field`),
  ADD KEY `media_usages_usable_type_usable_id_index` (`usable_type`,`usable_id`);

--
-- Indexes for table `media_variants`
--
ALTER TABLE `media_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `media_variants_media_asset_id_key_unique` (`media_asset_id`,`key`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `monitoring_heartbeats`
--
ALTER TABLE `monitoring_heartbeats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `monitoring_heartbeats_key_unique` (`key`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_reference_unique` (`reference`),
  ADD UNIQUE KEY `orders_idempotency_key_unique` (`idempotency_key`),
  ADD KEY `orders_user_id_foreign` (`user_id`),
  ADD KEY `orders_listing_id_foreign` (`listing_id`),
  ADD KEY `orders_source_index` (`source`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_platform_product_variant_id_foreign` (`platform_product_variant_id`),
  ADD KEY `order_items_item_type_item_id_index` (`item_type`,`item_id`);

--
-- Indexes for table `otc_pricing_settings`
--
ALTER TABLE `otc_pricing_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payment_timeline_events`
--
ALTER TABLE `payment_timeline_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pte_subject_occurred_idx` (`subject_type`,`subject_id`,`occurred_at`);

--
-- Indexes for table `payment_webhooks`
--
ALTER TABLE `payment_webhooks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_webhooks_provider_idempotency_key_unique` (`provider`,`idempotency_key`),
  ADD KEY `payment_webhooks_status_received_at_index` (`status`,`received_at`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `platform_products`
--
ALTER TABLE `platform_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `platform_products_slug_unique` (`slug`),
  ADD KEY `platform_products_product_type_status_index` (`product_type`,`status`),
  ADD KEY `platform_products_is_featured_status_index` (`is_featured`,`status`),
  ADD KEY `platform_products_product_type_id_foreign` (`product_type_id`),
  ADD KEY `platform_products_hero_media_id_foreign` (`hero_media_id`);

--
-- Indexes for table `platform_product_images`
--
ALTER TABLE `platform_product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `platform_product_images_platform_product_id_foreign` (`platform_product_id`),
  ADD KEY `platform_product_images_media_asset_id_foreign` (`media_asset_id`);

--
-- Indexes for table `platform_product_variants`
--
ALTER TABLE `platform_product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `platform_product_variants_sku_unique` (`sku`),
  ADD KEY `platform_product_variants_platform_product_id_foreign` (`platform_product_id`);

--
-- Indexes for table `product_metric_daily`
--
ALTER TABLE `product_metric_daily`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_metric_daily_unique` (`day`,`metric_key`,`dimension`),
  ADD KEY `product_metric_daily_metric_key_day_index` (`metric_key`,`day`);

--
-- Indexes for table `product_metric_monthly`
--
ALTER TABLE `product_metric_monthly`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_metric_monthly_unique` (`month`,`metric_key`,`dimension`),
  ADD KEY `product_metric_monthly_metric_key_month_index` (`metric_key`,`month`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_reviews_user_id_platform_product_id_unique` (`user_id`,`platform_product_id`),
  ADD KEY `product_reviews_platform_product_id_foreign` (`platform_product_id`);

--
-- Indexes for table `product_types`
--
ALTER TABLE `product_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_types_slug_unique` (`slug`),
  ADD KEY `product_types_service_category_id_is_active_index` (`service_category_id`,`is_active`),
  ADD KEY `product_types_banner_media_id_foreign` (`banner_media_id`),
  ADD KEY `product_types_card_media_id_foreign` (`card_media_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reviews_order_id_unique` (`order_id`),
  ADD KEY `reviews_listing_id_created_at_index` (`listing_id`,`created_at`),
  ADD KEY `reviews_user_id_foreign` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `security_verification_codes`
--
ALTER TABLE `security_verification_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `security_verification_codes_user_id_purpose_expires_at_index` (`user_id`,`purpose`,`expires_at`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_categories_slug_unique` (`slug`),
  ADD KEY `service_categories_banner_media_id_foreign` (`banner_media_id`),
  ADD KEY `service_categories_card_media_id_foreign` (`card_media_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `social_links`
--
ALTER TABLE `social_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `social_links_enabled_sort_order_index` (`enabled`,`sort_order`);

--
-- Indexes for table `support_attachments`
--
ALTER TABLE `support_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `support_attachments_support_ticket_id_foreign` (`support_ticket_id`),
  ADD KEY `support_attachments_support_ticket_reply_id_foreign` (`support_ticket_reply_id`),
  ADD KEY `support_attachments_user_id_foreign` (`user_id`),
  ADD KEY `support_attachments_expires_at_index` (`expires_at`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `support_tickets_user_id_foreign` (`user_id`);

--
-- Indexes for table `support_ticket_replies`
--
ALTER TABLE `support_ticket_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `support_ticket_replies_ticket_id_foreign` (`support_ticket_id`),
  ADD KEY `support_ticket_replies_user_id_foreign` (`user_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `system_settings_key_unique` (`key`);

--
-- Indexes for table `tracking_scripts`
--
ALTER TABLE `tracking_scripts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tracking_scripts_enabled_location_sort_order_index` (`enabled`,`location`,`sort_order`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transactions_reference_unique` (`reference`),
  ADD KEY `transactions_user_id_foreign` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_suspended_by_foreign` (`suspended_by`);
ALTER TABLE `users` ADD FULLTEXT KEY `users_search_fulltext` (`name`,`email`,`username`);

--
-- Indexes for table `user_activity`
--
ALTER TABLE `user_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_activity_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  ADD KEY `user_activity_user_id_action_occurred_at_index` (`user_id`,`action`,`occurred_at`),
  ADD KEY `user_activity_user_id_subject_type_occurred_at_index` (`user_id`,`subject_type`,`occurred_at`),
  ADD KEY `user_activity_context_key_index` (`context_key`),
  ADD KEY `user_activity_occurred_at_index` (`occurred_at`);

--
-- Indexes for table `user_auth_providers`
--
ALTER TABLE `user_auth_providers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_auth_providers_provider_provider_user_id_unique` (`provider`,`provider_user_id`),
  ADD UNIQUE KEY `user_auth_providers_user_id_provider_unique` (`user_id`,`provider`);

--
-- Indexes for table `user_bank_accounts`
--
ALTER TABLE `user_bank_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_bank_accounts_user_id_active_index` (`user_id`,`active`);

--
-- Indexes for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_notifications_user_id_read_at_index` (`user_id`,`read_at`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wallets_user_id_unique` (`user_id`);

--
-- Indexes for table `wallet_balance_history`
--
ALTER TABLE `wallet_balance_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wbh_wallet_recorded_idx` (`crypto_deposit_wallet_id`,`recorded_at`);

--
-- Indexes for table `wallet_fundings`
--
ALTER TABLE `wallet_fundings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wallet_fundings_reference_unique` (`reference`),
  ADD UNIQUE KEY `wallet_fundings_provider_payment_reference_unique` (`provider_payment_reference`),
  ADD KEY `wallet_fundings_user_id_foreign` (`user_id`),
  ADD KEY `wallet_fundings_wallet_id_foreign` (`wallet_id`);

--
-- Indexes for table `wallet_holds`
--
ALTER TABLE `wallet_holds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wallet_holds_wallet_id_status_index` (`wallet_id`,`status`),
  ADD KEY `wallet_holds_reason_type_reason_id_index` (`reason_type`,`reason_id`),
  ADD KEY `wallet_holds_status_expires_at_index` (`status`,`expires_at`);

--
-- Indexes for table `watchlists`
--
ALTER TABLE `watchlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `watchlists_user_id_listing_id_unique` (`user_id`,`listing_id`),
  ADD KEY `watchlists_listing_id_foreign` (`listing_id`);

--
-- Indexes for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `withdrawals_reference_unique` (`reference`),
  ADD UNIQUE KEY `withdrawals_provider_payout_reference_unique` (`provider_payout_reference`),
  ADD KEY `withdrawals_user_id_foreign` (`user_id`),
  ADD KEY `withdrawals_wallet_id_foreign` (`wallet_id`),
  ADD KEY `withdrawals_user_bank_account_id_foreign` (`user_bank_account_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `analytics_ga_snapshots`
--
ALTER TABLE `analytics_ga_snapshots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `analytics_kpi_snapshots`
--
ALTER TABLE `analytics_kpi_snapshots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=236;

--
-- AUTO_INCREMENT for table `analytics_providers`
--
ALTER TABLE `analytics_providers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- AUTO_INCREMENT for table `catalog_page_contents`
--
ALTER TABLE `catalog_page_contents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `crypto_deposit_wallets`
--
ALTER TABLE `crypto_deposit_wallets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `crypto_sell_requests`
--
ALTER TABLE `crypto_sell_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `demo_batches`
--
ALTER TABLE `demo_batches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `demo_batch_records`
--
ALTER TABLE `demo_batch_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1194;

--
-- AUTO_INCREMENT for table `email_delivery_attempts`
--
ALTER TABLE `email_delivery_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_identities`
--
ALTER TABLE `email_identities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `email_verification_codes`
--
ALTER TABLE `email_verification_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `escrows`
--
ALTER TABLE `escrows`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `exchange_rates`
--
ALTER TABLE `exchange_rates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `exchange_rate_history`
--
ALTER TABLE `exchange_rate_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `gateway_operations`
--
ALTER TABLE `gateway_operations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `incoming_crypto_transactions`
--
ALTER TABLE `incoming_crypto_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `integration_providers`
--
ALTER TABLE `integration_providers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `kyc_submissions`
--
ALTER TABLE `kyc_submissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `listings`
--
ALTER TABLE `listings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `listing_versions`
--
ALTER TABLE `listing_versions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `marketplace_products`
--
ALTER TABLE `marketplace_products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `media_assets`
--
ALTER TABLE `media_assets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `media_usages`
--
ALTER TABLE `media_usages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `media_variants`
--
ALTER TABLE `media_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `monitoring_heartbeats`
--
ALTER TABLE `monitoring_heartbeats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `otc_pricing_settings`
--
ALTER TABLE `otc_pricing_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment_timeline_events`
--
ALTER TABLE `payment_timeline_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_webhooks`
--
ALTER TABLE `payment_webhooks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `platform_products`
--
ALTER TABLE `platform_products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `platform_product_images`
--
ALTER TABLE `platform_product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `platform_product_variants`
--
ALTER TABLE `platform_product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=199;

--
-- AUTO_INCREMENT for table `product_metric_daily`
--
ALTER TABLE `product_metric_daily`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=364;

--
-- AUTO_INCREMENT for table `product_metric_monthly`
--
ALTER TABLE `product_metric_monthly`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_types`
--
ALTER TABLE `product_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `security_verification_codes`
--
ALTER TABLE `security_verification_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `social_links`
--
ALTER TABLE `social_links`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `support_attachments`
--
ALTER TABLE `support_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `support_ticket_replies`
--
ALTER TABLE `support_ticket_replies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `tracking_scripts`
--
ALTER TABLE `tracking_scripts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=266;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `user_activity`
--
ALTER TABLE `user_activity`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=444;

--
-- AUTO_INCREMENT for table `user_auth_providers`
--
ALTER TABLE `user_auth_providers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_bank_accounts`
--
ALTER TABLE `user_bank_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_notifications`
--
ALTER TABLE `user_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `wallet_balance_history`
--
ALTER TABLE `wallet_balance_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wallet_fundings`
--
ALTER TABLE `wallet_fundings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `wallet_holds`
--
ALTER TABLE `wallet_holds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `watchlists`
--
ALTER TABLE `watchlists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `catalog_page_contents`
--
ALTER TABLE `catalog_page_contents`
  ADD CONSTRAINT `catalog_page_contents_banner_media_id_foreign` FOREIGN KEY (`banner_media_id`) REFERENCES `media_assets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `catalog_page_contents_card_media_id_foreign` FOREIGN KEY (`card_media_id`) REFERENCES `media_assets` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_banner_media_id_foreign` FOREIGN KEY (`banner_media_id`) REFERENCES `media_assets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `categories_card_media_id_foreign` FOREIGN KEY (`card_media_id`) REFERENCES `media_assets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `crypto_sell_requests`
--
ALTER TABLE `crypto_sell_requests`
  ADD CONSTRAINT `crypto_sell_requests_crypto_deposit_wallet_id_foreign` FOREIGN KEY (`crypto_deposit_wallet_id`) REFERENCES `crypto_deposit_wallets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `crypto_sell_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `crypto_sell_requests_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `demo_batch_records`
--
ALTER TABLE `demo_batch_records`
  ADD CONSTRAINT `demo_batch_records_demo_batch_id_foreign` FOREIGN KEY (`demo_batch_id`) REFERENCES `demo_batches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_verification_codes`
--
ALTER TABLE `email_verification_codes`
  ADD CONSTRAINT `email_verification_codes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `escrows`
--
ALTER TABLE `escrows`
  ADD CONSTRAINT `escrows_buyer_wallet_id_foreign` FOREIGN KEY (`buyer_wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `escrows_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gateway_operations`
--
ALTER TABLE `gateway_operations`
  ADD CONSTRAINT `gateway_operations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `gateway_operations_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `incoming_crypto_transactions`
--
ALTER TABLE `incoming_crypto_transactions`
  ADD CONSTRAINT `incoming_crypto_transactions_matched_order_id_foreign` FOREIGN KEY (`matched_order_id`) REFERENCES `crypto_sell_requests` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `kyc_submissions`
--
ALTER TABLE `kyc_submissions`
  ADD CONSTRAINT `kyc_submissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `listings`
--
ALTER TABLE `listings`
  ADD CONSTRAINT `listings_marketplace_product_id_foreign` FOREIGN KEY (`marketplace_product_id`) REFERENCES `marketplace_products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `listing_versions`
--
ALTER TABLE `listing_versions`
  ADD CONSTRAINT `listing_versions_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `marketplace_products`
--
ALTER TABLE `marketplace_products`
  ADD CONSTRAINT `marketplace_products_banner_media_id_foreign` FOREIGN KEY (`banner_media_id`) REFERENCES `media_assets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `marketplace_products_card_media_id_foreign` FOREIGN KEY (`card_media_id`) REFERENCES `media_assets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `marketplace_products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media_assets`
--
ALTER TABLE `media_assets`
  ADD CONSTRAINT `media_assets_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `media_usages`
--
ALTER TABLE `media_usages`
  ADD CONSTRAINT `media_usages_media_asset_id_foreign` FOREIGN KEY (`media_asset_id`) REFERENCES `media_assets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media_variants`
--
ALTER TABLE `media_variants`
  ADD CONSTRAINT `media_variants_media_asset_id_foreign` FOREIGN KEY (`media_asset_id`) REFERENCES `media_assets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_platform_product_variant_id_foreign` FOREIGN KEY (`platform_product_variant_id`) REFERENCES `platform_product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `platform_products`
--
ALTER TABLE `platform_products`
  ADD CONSTRAINT `platform_products_hero_media_id_foreign` FOREIGN KEY (`hero_media_id`) REFERENCES `media_assets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `platform_products_product_type_id_foreign` FOREIGN KEY (`product_type_id`) REFERENCES `product_types` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `platform_product_images`
--
ALTER TABLE `platform_product_images`
  ADD CONSTRAINT `platform_product_images_media_asset_id_foreign` FOREIGN KEY (`media_asset_id`) REFERENCES `media_assets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `platform_product_images_platform_product_id_foreign` FOREIGN KEY (`platform_product_id`) REFERENCES `platform_products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `platform_product_variants`
--
ALTER TABLE `platform_product_variants`
  ADD CONSTRAINT `platform_product_variants_platform_product_id_foreign` FOREIGN KEY (`platform_product_id`) REFERENCES `platform_products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_platform_product_id_foreign` FOREIGN KEY (`platform_product_id`) REFERENCES `platform_products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_types`
--
ALTER TABLE `product_types`
  ADD CONSTRAINT `product_types_banner_media_id_foreign` FOREIGN KEY (`banner_media_id`) REFERENCES `media_assets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_types_card_media_id_foreign` FOREIGN KEY (`card_media_id`) REFERENCES `media_assets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_types_service_category_id_foreign` FOREIGN KEY (`service_category_id`) REFERENCES `service_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `security_verification_codes`
--
ALTER TABLE `security_verification_codes`
  ADD CONSTRAINT `security_verification_codes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD CONSTRAINT `service_categories_banner_media_id_foreign` FOREIGN KEY (`banner_media_id`) REFERENCES `media_assets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `service_categories_card_media_id_foreign` FOREIGN KEY (`card_media_id`) REFERENCES `media_assets` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `support_attachments`
--
ALTER TABLE `support_attachments`
  ADD CONSTRAINT `support_attachments_support_ticket_id_foreign` FOREIGN KEY (`support_ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_attachments_support_ticket_reply_id_foreign` FOREIGN KEY (`support_ticket_reply_id`) REFERENCES `support_ticket_replies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `support_attachments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_ticket_replies`
--
ALTER TABLE `support_ticket_replies`
  ADD CONSTRAINT `support_ticket_replies_ticket_id_foreign` FOREIGN KEY (`support_ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_ticket_replies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_suspended_by_foreign` FOREIGN KEY (`suspended_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_activity`
--
ALTER TABLE `user_activity`
  ADD CONSTRAINT `user_activity_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_auth_providers`
--
ALTER TABLE `user_auth_providers`
  ADD CONSTRAINT `user_auth_providers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_bank_accounts`
--
ALTER TABLE `user_bank_accounts`
  ADD CONSTRAINT `user_bank_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD CONSTRAINT `user_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_balance_history`
--
ALTER TABLE `wallet_balance_history`
  ADD CONSTRAINT `wallet_balance_history_crypto_deposit_wallet_id_foreign` FOREIGN KEY (`crypto_deposit_wallet_id`) REFERENCES `crypto_deposit_wallets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_fundings`
--
ALTER TABLE `wallet_fundings`
  ADD CONSTRAINT `wallet_fundings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wallet_fundings_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_holds`
--
ALTER TABLE `wallet_holds`
  ADD CONSTRAINT `wallet_holds_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `watchlists`
--
ALTER TABLE `watchlists`
  ADD CONSTRAINT `watchlists_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `watchlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD CONSTRAINT `withdrawals_user_bank_account_id_foreign` FOREIGN KEY (`user_bank_account_id`) REFERENCES `user_bank_accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `withdrawals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `withdrawals_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
