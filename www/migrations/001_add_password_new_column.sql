-- Migration: Add new password column and password reset fields
-- This migration adds support for modern password hashing (Argon2ID)
-- while keeping the old MD5 password column for migration purposes

-- Add new password column with Argon2ID hash
ALTER TABLE player
ADD COLUMN password_new VARCHAR(255) NULL AFTER password
COMMENT 'Argon2ID password hash (new secure method)';

-- Add password reset token and expiration
ALTER TABLE player
ADD COLUMN password_reset_token VARCHAR(64) NULL AFTER password_new,
ADD COLUMN password_reset_expires DATETIME NULL AFTER password_reset_token;

-- Add index on password reset token for faster lookups
ALTER TABLE player
ADD INDEX idx_password_reset_token (password_reset_token);

-- Add index on login and season for faster authentication
ALTER TABLE player
ADD INDEX idx_login_season (login, season);

-- Note: After all users have been migrated to the new password system
-- (typically after one season), you can remove the old password column:
-- ALTER TABLE player DROP COLUMN password;
