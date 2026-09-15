-- ===============================================
-- CREATE MISSING SAVED TABLE
-- ===============================================
-- Run this SQL in phpMyAdmin to create the saved table
-- This table stores user-saved property listings

CREATE TABLE IF NOT EXISTS `saved` (
  `id` varchar(20) NOT NULL,
  `property_id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===============================================
-- HOW TO USE IN phpMyAdmin
-- ===============================================
-- 1. Open phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Click on your 'home_db' database
-- 3. Click on the 'SQL' tab at the top
-- 4. Paste the CREATE TABLE statement above
-- 5. Click 'Execute' button
-- 6. You should see "1 row affected" or similar success message
-- 7. Refresh and check the "Tables" list - you should see 'saved' table

-- ===============================================
-- VERIFICATION
-- ===============================================
-- SELECT * FROM saved;
-- DESC saved;
