<?php
/**
 * QueryExecutor - Database Query Execution
 *
 * Provides methods for executing database queries with logging and error handling.
 * Extracted from common.inc as part of code modernization.
 *
 * @package Top7\Database
 * @since Phase 1, Task 1.2.1
 */

namespace Top7\Database;

use PDO;
use PDOException;
use Top7\Utils\Logger;

class QueryExecutor {

    /**
     * Fetch modes
     */
    const MODE_NONE = 0;  // No results expected
    const MODE_ONE = 1;   // Fetch single row
    const MODE_ALL = 2;   // Fetch all rows

    /**
     * Execute a SELECT query with parameters
     *
     * @param string $function Calling function name (for logging)
     * @param int $mode Fetch mode (MODE_ONE or MODE_ALL)
     * @param string $query SQL query
     * @param array|null $params Query parameters
     * @return array|null Query results
     */
    public static function fetch(string $function, int $mode, string $query, ?array $params = null) {
        global $pdo, $debug_mysql;

        if ($debug_mysql) {
            echo "<pre>$query</pre>";
        }

        Logger::log($function, "sql", $query);
        $result = null;

        try {
            $stmt = $pdo->prepare($query);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute($params);

            if ($mode === self::MODE_ONE) {
                $result = $stmt->fetch();
            } elseif ($mode === self::MODE_ALL) {
                $result = $stmt->fetchAll();
            }
        } catch (PDOException $e) {
            Logger::log("error", $function, $query);
            Logger::error(__FUNCTION__, "(" . $function . ") " . $e->getMessage());
        }

        Logger::logVar($function, "sql", $result);
        return $result;
    }

    /**
     * Execute an UPDATE, DELETE or other non-SELECT query
     *
     * @param string $function Calling function name (for logging)
     * @param string $query SQL query
     * @param array|null $params Query parameters
     * @return void
     */
    public static function execute(string $function, string $query, ?array $params = null): void {
        global $pdo, $debug_mysql;

        if ($debug_mysql) {
            echo "<pre>$query</pre>";
        }

        Logger::log($function, "sql", $query);

        try {
            $stmt = $pdo->prepare($query);
            if ($params !== null) {
                $stmt->execute($params);
            } else {
                $stmt->execute();
            }
        } catch (PDOException $e) {
            Logger::log("error", $function, $query);
            Logger::error(__FUNCTION__, "(" . $function . ") " . $e->getMessage());
        }
    }

    /**
     * Execute an INSERT query and return last insert ID
     *
     * @param string $function Calling function name (for logging)
     * @param string $query SQL query
     * @param array $data Data to insert
     * @return string Last insert ID
     */
    public static function insert(string $function, string $query, array $data): string {
        global $pdo, $debug_mysql;

        if ($debug_mysql) {
            echo "<pre>$query</pre>";
        }

        Logger::log($function, "sql", $query);

        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($data);
            $lastId = $pdo->lastInsertId();
            return $lastId;
        } catch (PDOException $e) {
            Logger::log("error", $function, $query);
            Logger::error(__FUNCTION__, "(" . $function . ") " . $e->getMessage());
        }
    }

    /**
     * Execute a raw query (use with caution)
     *
     * @param string $query SQL query
     * @return bool Success status
     */
    public static function raw(string $query): bool {
        global $pdo;

        try {
            $pdo->exec($query);
            return true;
        } catch (PDOException $e) {
            Logger::error(__FUNCTION__, $e->getMessage());
            return false;
        }
    }

    /**
     * Begin a transaction
     *
     * @return bool Success status
     */
    public static function beginTransaction(): bool {
        global $pdo;
        return $pdo->beginTransaction();
    }

    /**
     * Commit a transaction
     *
     * @return bool Success status
     */
    public static function commit(): bool {
        global $pdo;
        return $pdo->commit();
    }

    /**
     * Rollback a transaction
     *
     * @return bool Success status
     */
    public static function rollback(): bool {
        global $pdo;
        return $pdo->rollBack();
    }
}
