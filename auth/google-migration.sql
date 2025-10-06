-- Add google_id column to accounts table for Google OAuth integration
-- Run this SQL script on your database to enable Google authentication

-- Add google_id column with unique constraint
ALTER TABLE `accounts` 
ADD COLUMN `google_id` VARCHAR(100) DEFAULT NULL AFTER `connection`,
ADD UNIQUE KEY `unique_google_id` (`google_id`);

-- Note: This migration adds a google_id field to store Google user IDs
-- The unique index ensures one Google account can only be linked to one account
