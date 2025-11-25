# Test Data - Local Development Setup

This directory contains all the necessary files to set up and run a local test environment for the TOP7 application.

---

## Directory Contents

```
test-data/
├── docker-compose.yml         # Docker Compose configuration for test environment
├── Dockerfile                 # Web server Docker image configuration
├── conf/                      # Configuration files
│   ├── conf.php              # PHP application configuration
│   ├── google_gtag.html      # Google Analytics tag (if used)
│   └── php.ini               # PHP settings
├── init-scripts/              # Automatic database initialization (NEW!)
│   ├── 01-init-database.sh   # Auto-runs on first container startup
│   └── sql/
│       └── topseven.sql      # Database schema and data
├── topseven.sql              # Main database schema and initial data
├── user_db.sql               # User database schema
├── init_db.sh                # Manual: Initialize database script
├── create_user_db.sh         # Manual: Create user database script
├── reinit_db.sh              # Manual: Reinitialize database (wipe and recreate)
└── crontab.txt               # Cron job configuration for scheduled tasks
```

---

## Quick Start

### 1. Start the Test Environment

```bash
cd test-data
docker-compose up -d
```

This will:
- Start a MySQL database container
- Start a web server container with Apache and PHP
- Mount your application code
- **Automatically initialize the database** (on first run only)

**Note:** The database is automatically initialized on the first run using scripts in `init-scripts/`. No manual steps required!

### 2. Manual Database Re-initialization (Optional)

If you need to reset the database after the initial setup:

```bash
# Reinitialize with fresh data
./init_db.sh

# Or to completely reset the database
./reinit_db.sh
```

### 3. Access the Application

- **Web Application:** http://localhost
- **Database:** localhost:3306 (from host)

---

## Automatic Database Initialization

### How It Works

The database is **automatically initialized** when you first run `docker-compose up`. This is accomplished through MySQL's built-in initialization mechanism:

1. **On First Container Startup Only**: MySQL automatically executes scripts in `/docker-entrypoint-initdb.d/`
2. **Initialization Scripts**: Located in `init-scripts/` directory
3. **Automatic Execution**: The `01-init-database.sh` script runs and imports `topseven.sql`
4. **One-Time Only**: Initialization only happens when the database volume is empty (first run)

### Directory Structure

```
test-data/
└── init-scripts/
    ├── 01-init-database.sh    # Automatic initialization script
    └── sql/
        └── topseven.sql       # Database schema and data
```

### When Does Auto-Initialization Run?

✅ **Runs automatically:**
- First time running `docker-compose up`
- After running `docker-compose down -v` (removes volumes)
- When the MySQL data volume is empty

❌ **Does NOT run:**
- On subsequent container restarts
- When the database already exists
- After `docker-compose restart`

### To Force Re-initialization

If you need to reinitialize an existing database:

```bash
# Option 1: Remove volumes and restart (complete reset)
docker-compose down -v
docker-compose up -d

# Option 2: Use manual scripts (database remains)
./init_db.sh
```

---

## Database Scripts

### `init_db.sh`
**Purpose:** Initialize the database with schema and initial data.

**When to use:**
- First time setup
- After pulling fresh database changes
- When database exists but needs data refresh

**What it does:**
- Imports `topseven.sql` into the main database
- Creates necessary tables
- Inserts initial data

**Usage:**
```bash
./init_db.sh
```

### `create_user_db.sh`
**Purpose:** Create the user database and user table.

**When to use:**
- First time setup
- After dropping the user database
- When user authentication is not working

**What it does:**
- Imports `user_db.sql`
- Creates user table with authentication data
- Sets up test users

**Usage:**
```bash
./create_user_db.sh
```

### `reinit_db.sh`
**Purpose:** Complete database reset - wipe everything and start fresh.

**When to use:**
- Database is corrupted
- Need a clean slate for testing
- Schema changes require full rebuild

**What it does:**
- Drops all existing databases
- Recreates databases from scratch
- Runs both `topseven.sql` and `user_db.sql`
- Resets all data to initial state

**Usage:**
```bash
./reinit_db.sh
```

---

## SQL Files

### `topseven.sql`
**Contains:**
- Main application database schema
- Tables for teams, players, matches, predictions, rankings
- Initial/seed data
- Foreign key relationships
- Indexes

**Size:** ~8KB

### `user_db.sql`
**Contains:**
- User authentication table
- User credentials (hashed passwords)
- User profiles and permissions
- Test user accounts

**Size:** ~500 bytes

---

## Configuration Files

### `docker-compose.yml`
**Purpose:** Orchestrates the test environment containers.

**Services:**
- `db`: MySQL 5.7 database server
- `web`: Apache + PHP web server

**Key Settings:**
- Port mappings
- Volume mounts
- Environment variables
- Network configuration

### `Dockerfile`
**Purpose:** Defines the web server image.

**Includes:**
- Apache 2.4
- PHP 7.x (or configured version)
- Required PHP extensions
- Custom PHP configuration

### `conf/conf.php`
**Purpose:** Application configuration for test environment.

**Settings:**
- Database connection parameters
- Application constants
- Feature flags
- Debug settings

### `conf/php.ini`
**Purpose:** PHP runtime configuration.

**Settings:**
- Memory limits
- Upload limits
- Error reporting
- Session configuration
- Timezone

### `crontab.txt`
**Purpose:** Scheduled tasks configuration.

**Typical tasks:**
- Database backups
- Cache clearing
- Report generation
- Email notifications

---

## Test Users

The databases include test users for authentication testing:

| Email | Password | Purpose |
|-------|----------|---------|
| test2@topseven.fr | (see user_db.sql) | Standard test user |
| admin@topseven.fr | (see user_db.sql) | Admin test user |

**Note:** Check `user_db.sql` for actual test credentials.

---

## Docker Commands

### Start Environment
```bash
docker-compose up -d
```

### Stop Environment
```bash
docker-compose down
```

### View Logs
```bash
# Web server logs
docker logs test-web-1

# Database logs
docker logs test-db-1

# Follow logs in real-time
docker logs -f test-web-1
```

### Execute Commands in Container
```bash
# Access web container shell
docker exec -it test-web-1 bash

# Access database
docker exec -it test-db-1 mysql -u root -p

# View PHP logs
docker exec test-web-1 tail -f /tmp/log_$(date +%Y%m%d).txt
```

### Restart Services
```bash
# Restart web server
docker-compose restart web

# Restart database
docker-compose restart db

# Restart everything
docker-compose restart
```

---

## Troubleshooting

### Database Connection Failed
```bash
# Check database is running
docker ps | grep db

# Check database logs
docker logs test-db-1

# Restart database
docker-compose restart db

# Reinitialize database
./reinit_db.sh
```

### Web Server Not Responding
```bash
# Check web server is running
docker ps | grep web

# Check web server logs
docker logs test-web-1

# Check Apache errors
docker exec test-web-1 tail -20 /var/log/apache2/error.log

# Restart web server
docker-compose restart web
```

### Permission Issues
```bash
# Fix file permissions
chmod +x *.sh

# Fix ownership (if needed)
sudo chown -R $USER:$USER .
```

### Port Already in Use
```bash
# Check what's using port 80
sudo lsof -i :80

# Change port in docker-compose.yml
# Edit: "8080:80" instead of "80:80"

# Restart with new port
docker-compose down && docker-compose up -d
```

---

## Maintenance

### Update Database Schema
```bash
# 1. Export current schema
docker exec test-db-1 mysqldump -u root -p topseven > topseven.sql

# 2. Make changes to .sql file
# 3. Reinitialize database
./reinit_db.sh
```

### Clean Up Docker Resources
```bash
# Remove stopped containers
docker-compose down

# Remove volumes (WARNING: deletes all data)
docker-compose down -v

# Remove images
docker-compose down --rmi all
```

### Backup Database
```bash
# Backup main database
docker exec test-db-1 mysqldump -u root -p topseven > backup_topseven_$(date +%Y%m%d).sql

# Backup user database
docker exec test-db-1 mysqldump -u root -p user_db > backup_user_db_$(date +%Y%m%d).sql
```

---

## Related Documentation

- **Playwright Tests:** [../tests/playwright/README.md](../tests/playwright/README.md)
- **Application Setup:** [../README.md](../README.md)
- **Docker Documentation:** [https://docs.docker.com/](https://docs.docker.com/)

---

## Notes

- This test environment is **NOT** for production use
- Default credentials are intentionally weak for testing
- Data is ephemeral unless volumes are configured
- Containers are isolated from production systems
- Test data should be representative but not production data

---

**Test Environment Ready! 🚀**
