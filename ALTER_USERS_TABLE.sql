-- SQL Migration Script to Update Users Table
-- Run this in phpMyAdmin to add missing columns to your users table

-- Add 'type' column if it doesn't exist
ALTER TABLE `users` 
ADD COLUMN `type` varchar(20) NOT NULL DEFAULT 'buyer' AFTER `password`;

-- Increase password column size to accommodate hashed passwords (which are longer than SHA1)
ALTER TABLE `users` 
MODIFY COLUMN `password` varchar(255) NOT NULL;

-- Add created_at column if it doesn't exist (for tracking registration date)
ALTER TABLE `users` 
ADD COLUMN `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Optional: Check the table structure
-- DESC users;
