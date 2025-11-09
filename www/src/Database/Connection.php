<?php
/**
 * Database Connection - PDO Connection Management
 *
 * Provides database connection singleton for the application.
 * Extracted from common.inc as part of code modernization.
 *
 * @package Top7\Database
 * @since Phase 1, Task 1.2.1
 */

namespace Top7\Database;

use PDO;
use PDOException;
use Top7\Utils\Logger;

class Connection {

    /**
     * @var PDO|null Singleton PDO instance
     */
    private static $pdo = null;

    /**
     * @var string Date format string
     */
    private static $strDate;

    /**
     * Initialize database connection with player credentials
     *
     * @return PDO Database connection
     */
    public static function initPlayer(): PDO {
        global $db_player;
        return self::connect($db_player);
    }

    /**
     * Initialize database connection with admin credentials
     *
     * @return PDO Database connection
     */
    public static function initAdmin(): PDO {
        global $db_admin;
        return self::connect($db_admin);
    }

    /**
     * Connect to database with given credentials
     *
     * @param array $login Login credentials array with 'user' and 'password' keys
     * @return PDO Database connection
     */
    public static function connect(array $login): PDO {
        global $top7_db;

        // Set locale for date formatting
        setlocale(LC_TIME, 'fr_FR', 'fra');
        self::$strDate = mb_convert_encoding('%a %d %b %Y %H:%M', 'ISO-8859-9', 'UTF-8');

        $server   = $top7_db['server'];
        $database = $top7_db['database'];
        $user     = $login['user'];
        $password = $login['password'];
        $charset  = "utf8mb4";

        try {
            self::$pdo = new PDO(
                "mysql:dbname=$database;host=$server;charset=$charset",
                $user,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            return self::$pdo;
        } catch (PDOException $e) {
            Logger::log("error", "sql", "db init");
            Logger::error(__FUNCTION__, "(db init)" . $e->getMessage());
        }
    }

    /**
     * Get current PDO instance
     *
     * @return PDO|null Current database connection or null if not initialized
     */
    public static function getInstance(): ?PDO {
        return self::$pdo;
    }

    /**
     * Get PDO instance or throw exception if not initialized
     *
     * @return PDO Database connection
     * @throws \RuntimeException if connection not initialized
     */
    public static function getInstanceOrFail(): PDO {
        if (self::$pdo === null) {
            throw new \RuntimeException('Database not initialized. Call initPlayer() or initAdmin() first.');
        }
        return self::$pdo;
    }

    /**
     * Close the database connection
     */
    public static function close(): void {
        self::$pdo = null;
    }

    /**
     * Get date format string
     *
     * @return string Date format string
     */
    public static function getDateFormat(): string {
        return self::$strDate ?? '';
    }
}
