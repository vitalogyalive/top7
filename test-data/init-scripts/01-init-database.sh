#!/bin/bash
set -e

echo "========================================="
echo "Starting TOP7 Database Initialization"
echo "========================================="

# Wait for MySQL to be ready
until mysql -u root -p"${MYSQL_ROOT_PASSWORD}" -e "SELECT 1" &> /dev/null; do
  echo "Waiting for MySQL to be ready..."
  sleep 2
done

echo "MySQL is ready. Starting database initialization..."

# The database and user are already created by environment variables in docker-compose
# MYSQL_DATABASE=topseven
# MYSQL_USER=topseven
# MYSQL_PASSWORD=topseven

# Import the complete database schema and test data from old setup
echo "Importing complete database schema and test data..."
mysql -u root -p"${MYSQL_ROOT_PASSWORD}" "${MYSQL_DATABASE}" < /docker-entrypoint-initdb.d/sql/01-full-schema-and-data.sql

echo "========================================="
echo "Database initialization completed!"
echo "========================================="
echo "Database: ${MYSQL_DATABASE}"
echo "User: ${MYSQL_USER}"
echo "Ready to use!"
echo "========================================="
