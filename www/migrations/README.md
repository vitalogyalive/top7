# Database Migrations

## Migration 001: Password Hashing Update (MD5 → Argon2ID)

### Overview
This migration updates the password hashing mechanism from insecure MD5 to modern Argon2ID algorithm, significantly improving security.

### Changes Made

1. **New PasswordService class** (`src/Auth/PasswordService.php`)
   - Implements Argon2ID password hashing
   - Provides verification methods
   - Supports legacy MD5 verification during migration

2. **Database schema update**
   - Added `password_new` column (VARCHAR 255) to `player` table
   - Kept existing `password` column for backward compatibility

3. **Updated functions in common.inc**
   - `check_player()` - Now tries Argon2ID first, falls back to MD5, auto-migrates
   - `update_register()` - New passwords use Argon2ID

### How to Apply Migration

```bash
cd /var/www/html/migrations
php run_migration.php 001
```

### Migration Strategy

This is a **zero-downtime migration** using a dual-hash strategy:

1. **Phase 1** (Current): Both `password` (MD5) and `password_new` (Argon2ID) columns exist
   - Existing users: Password stored in `password` (MD5)
   - New users: Password stored in both columns (MD5 + Argon2ID)
   - On login: If user has MD5 password, it's auto-migrated to Argon2ID

2. **Phase 2** (Future): After all users have been migrated
   - Remove `password` column
   - Rename `password_new` to `password`
   - Update code to use only Argon2ID

### Testing

#### Test 1: Existing User Login (MD5 → Argon2ID migration)
```sql
-- Before login
SELECT email, password, password_new FROM player WHERE email = 'test@example.com';
-- password_new should be NULL

-- After successful login, check again
SELECT email, password, password_new FROM player WHERE email = 'test@example.com';
-- password_new should now contain an Argon2ID hash
```

#### Test 2: New User Registration
```sql
-- After registration
SELECT email, password, password_new FROM player WHERE email = 'newuser@example.com';
-- Both password and password_new should be populated
-- password_new should start with $argon2id$
```

#### Test 3: Password Reset
- Request password reset
- Set new password
- Verify login works
- Check that `password_new` is populated

### Security Improvements

| Aspect | MD5 (Old) | Argon2ID (New) |
|--------|-----------|----------------|
| Algorithm | MD5 (broken) | Argon2ID (OWASP recommended) |
| Salt | No | Yes (automatic) |
| Cost factor | N/A | Configurable |
| Brute force resistance | Very low | Very high |
| Rainbow table resistance | Low | High |

### Rollback Plan

If issues occur, you can rollback by:

1. Stop using the PasswordService
2. Revert common.inc changes
3. Keep using MD5 temporarily (NOT RECOMMENDED for production)

```sql
-- Remove the password_new column (only if necessary)
ALTER TABLE player DROP COLUMN password_new;
```

### Monitoring Migration Progress

```sql
-- Check how many users have migrated
SELECT
    COUNT(*) as total_users,
    SUM(CASE WHEN password_new IS NOT NULL THEN 1 ELSE 0 END) as migrated_users,
    SUM(CASE WHEN password_new IS NULL THEN 1 ELSE 0 END) as pending_users
FROM player
WHERE status = 1; -- active players only
```

### Notes

- The migration happens automatically on user login
- Users who don't log in will remain on MD5 until they do
- Consider sending an email encouraging users to log in
- After 6 months, you can force-reset passwords for any remaining MD5 users

### Next Steps

After password hashing is complete, the next security improvement is **CSRF Protection** (Task 1.1.2).
