-- ============================================
-- ALTER MESSAGES TABLE TO ADD SELLER_ID, BUYER_ID, CREATED_AT
-- ============================================
-- Run this in phpMyAdmin to update your messages table

ALTER TABLE `messages` 
ADD COLUMN `seller_id` varchar(20),
ADD COLUMN `buyer_id` varchar(20),
ADD COLUMN `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
ADD FOREIGN KEY (`seller_id`) REFERENCES `sellers`(`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`buyer_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;
