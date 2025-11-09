-- Migration: Add password_new column for Argon2ID hashes
-- Phase 1, Task 1.1.1: Update Password Hashing
-- Date: 2025-11-09
--
-- This migration adds a new column 'password_new' to support modern Argon2ID
-- password hashing while maintaining backward compatibility with existing MD5 hashes
-- during the migration period.
--
-- Migration Strategy:
-- 1. Add password_new column (nullable, 255 chars for Argon2ID hashes)
-- 2. Keep old 'password' column (MD5) temporarily for backward compatibility
-- 3. Auto-migrate users to new hash on next successful login
-- 4. After all users migrated, old column can be removed in future migration

-- Add the new password column
ALTER TABLE `player`
ADD COLUMN `password_new` VARCHAR(255) NULL
COMMENT 'Argon2ID password hash (replaces MD5)'
AFTER `password`;

-- Add index for better performance (optional but recommended)
-- Note: password_new is nullable during migration period
