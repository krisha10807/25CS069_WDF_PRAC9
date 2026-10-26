-- ============================================================================
-- StudentHub Portal - Practical 9: User Registration & Secure Authentication
-- Course: Web Development Frameworks (WDF)
-- Student ID: 25CS069
-- Student Name: Krisha Patel
-- Database Name: studenthub_db
-- Table: users (Stores credentials with Bcrypt password hashing)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `studenthub_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `studenthub_db`;

-- ----------------------------------------------------------------------------
-- Table Structure for Table `users`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- Test Initial Data (Passwords hashed using PHP password_hash with BCRYPT)
-- Password for all seed users below is: 'Password@123'
-- ----------------------------------------------------------------------------
INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `password_hash`, `created_at`) VALUES
(1, 'Krisha Patel (25CS069)', 'krisha_patel', 'krisha.patel@charusat.ac.in', '$2y$10$wT0lPZvZlGZ7z9G2Lg4d0O6n28g1J3.XhB3L1vY4JqQ0aE.K2bOfe', '2026-10-10 10:30:00'),
(2, 'Rohan Shah', 'rohan_s', 'rohan.shah@charusat.ac.in', '$2y$10$wT0lPZvZlGZ7z9G2Lg4d0O6n28g1J3.XhB3L1vY4JqQ0aE.K2bOfe', '2026-10-11 11:15:00'),
(3, 'Aarav Sharma', 'aarav_s', 'aarav.sharma@charusat.ac.in', '$2y$10$wT0lPZvZlGZ7z9G2Lg4d0O6n28g1J3.XhB3L1vY4JqQ0aE.K2bOfe', '2026-10-12 14:20:00');
