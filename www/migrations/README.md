# Database Migrations

This directory contains SQL migration scripts for the Top7 database.

## How to Apply Migrations

### Using MySQL command line:
```bash
mysql -u [username] -p [database_name] < migrations/001_add_password_new_column.sql
```

### Using Docker:
```bash
# From the test directory
docker-compose exec db mysql -uroot -proot top7 < /var/www/html/migrations/001_add_password_new_column.sql
```

## Migration List

1. **001_add_password_new_column.sql** - Adds modern password hashing support
   - Adds `password_new` column for Argon2ID hashes
   - Adds password reset token fields
   - Adds database indexes for performance
   - Date: 2025-11-09

## Migration Strategy

The password migration uses a dual-column approach:
- `password` - Old MD5 hash (kept for backward compatibility)
- `password_new` - New Argon2ID hash

When users log in:
1. System first checks `password_new` (Argon2ID)
2. If not set, falls back to `password` (MD5)
3. If MD5 succeeds, automatically migrates to `password_new`

After one season (when all active users have logged in at least once), the old `password` column can be safely removed.
