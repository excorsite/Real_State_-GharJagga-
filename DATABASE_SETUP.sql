-- ============================================
-- DATABASE SETUP INSTRUCTIONS
-- ============================================
-- Run these SQL commands in phpMyAdmin
-- ============================================

-- Step 1: Create the purchases table
CREATE TABLE IF NOT EXISTS `purchases` (
  `id` varchar(20) NOT NULL,
  `buyer_id` varchar(20) NOT NULL,
  `seller_id` varchar(20) NOT NULL,
  `property_id` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `buyer_id` (`buyer_id`),
  KEY `seller_id` (`seller_id`),
  KEY `property_id` (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Step 2: Verify the table was created
-- SELECT * FROM purchases;

-- Step 3: (OPTIONAL) Add foreign key constraints for data integrity
-- These constraints ensure referential integrity
ALTER TABLE `purchases` 
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_3` FOREIGN KEY (`property_id`) REFERENCES `property` (`id`) ON DELETE CASCADE;

-- Step 4: Test data (OPTIONAL - uncomment to insert test purchase)
-- INSERT INTO purchases (id, buyer_id, seller_id, property_id, status, created_at)
-- VALUES ('pur_test123', 'user_id_1', 'user_id_2', 'prop_id_1', 'completed', NOW());

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Check table structure
-- DESC purchases;

-- Check all purchases
-- SELECT * FROM purchases;

-- Check purchases by buyer
-- SELECT * FROM purchases WHERE buyer_id = 'YOUR_USER_ID';

-- Check purchases with property details
-- SELECT p.*, prop.property_name, u.name as buyer_name, u2.name as seller_name
-- FROM purchases p
-- JOIN property prop ON p.property_id = prop.id
-- JOIN users u ON p.buyer_id = u.id
-- JOIN users u2 ON p.seller_id = u2.id;

-- ============================================
-- HOW TO USE IN phpMyAdmin
-- ============================================
-- 1. Open phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Click on your 'home_db' database
-- 3. Click on the 'SQL' tab at the top
-- 4. Paste the SQL code above (from CREATE TABLE to the end of Step 3)
-- 5. Click 'Execute' button
-- 6. You should see "1 row affected" or similar success message
-- 7. Refresh and check the "Tables" list - you should see 'purchases' table

-- ============================================
-- TROUBLESHOOTING
-- ============================================

-- If you get "Table already exists" error:
-- This means the table was already created. You can safely ignore it.
-- The IF NOT EXISTS clause prevents errors.

-- If you get foreign key constraint error:
-- Comment out Step 3 (the ALTER TABLE statements)
-- The foreign key constraints are optional and just for data integrity

-- To drop the table and start fresh:
-- DROP TABLE IF EXISTS purchases;

-- ============================================
