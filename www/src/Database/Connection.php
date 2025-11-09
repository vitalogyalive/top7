<?php

namespace Top7\Database;

use PDO;
use PDOException;

/**
 * Database Connection Manager
 *
 * Provides a singleton PDO connection and helper methods
 * Replaces the old init_sql() and pdo_* functions
 *
 * @package Top7\Database
 */
class Connection
{
    /** @var PDO|null Singleton PDO instance */
    private static ?PDO $pdo = null;

    /**
     * Get the PDO instance (singleton)
     *
     * @return PDO Database connection
     */
    public static function getInstance(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = self::connect();
        }
        return self::$pdo;
    }

    /**
     * Create a new PDO connection
     *
     * @return PDO Database connection
     * @throws PDOException If connection fails
     */
    private static function connect(): PDO
    {
        // Get configuration from constants (defined in conf.php)
        $db_host = defined('c_db_host') ? c_db_host : 'localhost';
        $db_name = defined('c_db_name') ? c_db_name : 'top7';
        $db_user = defined('c_db_user') ? c_db_user : 'root';
        $db_pwd = defined('c_db_pwd') ? c_db_pwd : '';

        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $db_user, $db_pwd, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]);

            return $pdo;
        } catch (PDOException $e) {
            // Log error and rethrow
            error_log("Database connection failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Execute a SELECT query and fetch results
     *
     * @param string $sql SQL query with placeholders
     * @param array $params Query parameters
     * @param int $fetchMode Fetch mode: c_none (0), c_one (1), c_all (2)
     * @return array|null Query results
     */
    public static function fetch(string $sql, array $params = [], int $fetchMode = 2): ?array
    {
        try {
            $pdo = self::getInstance();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            switch ($fetchMode) {
                case 0: // c_none - just execute, no results
                    return null;
                case 1: // c_one - fetch single row
                    $result = $stmt->fetch();
                    return $result ?: null;
                case 2: // c_all - fetch all rows
                default:
                    return $stmt->fetchAll();
            }
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            error_log("SQL: $sql");
            error_log("Params: " . print_r($params, true));
            return null;
        }
    }

    /**
     * Execute an INSERT, UPDATE, or DELETE query
     *
     * @param string $sql SQL query with placeholders
     * @param array $params Query parameters
     * @return int Number of affected rows, or 0 on error
     */
    public static function exec(string $sql, array $params = []): int
    {
        try {
            $pdo = self::getInstance();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Query execution failed: " . $e->getMessage());
            error_log("SQL: $sql");
            error_log("Params: " . print_r($params, true));
            return 0;
        }
    }

    /**
     * Get the last inserted ID
     *
     * @return string Last insert ID
     */
    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }

    /**
     * Begin a transaction
     *
     * @return bool True on success
     */
    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    /**
     * Commit a transaction
     *
     * @return bool True on success
     */
    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    /**
     * Rollback a transaction
     *
     * @return bool True on success
     */
    public static function rollback(): bool
    {
        return self::getInstance()->rollBack();
    }

    /**
     * Close the database connection
     */
    public static function close(): void
    {
        self::$pdo = null;
    }
}
