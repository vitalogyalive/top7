<?php
/**
 * Logger - Application Logging Utility
 *
 * Provides logging functionality for debugging and error tracking.
 * Extracted from common.inc as part of code modernization.
 *
 * @package Top7\Utils
 * @since Phase 1, Task 1.2.1
 */

namespace Top7\Utils;

class Logger {

    /**
     * @var string Path to log directory
     */
    private static $logPath;

    /**
     * @var array Functions to debug (whitelist)
     */
    private static $debugFunctions = [
        "error", // don't remove
        "put_nav", // don't remove
        "player.php",
        "key_is_valid",
        "init_time_session",
        "put_player_link",
        "put_status",
        "check_player", // don't remove
        "check_status_player",
        "check_player_phase_finale",
        "get_day_from_date",
        "get_first_day_season",
        "get_matchs_by_rank",
        "get_top7_day_selection",
        "get_previous_season_rank7",
        "get_random_rank7",
        "get_rank7",
        "get_rank7_finales",
        "get_palmares",
        "get_player_from_email",
        "display",
        "update_season_dates",
        "get_point_coiffeur",
        "update_point_coiffeur",
        "LNR_rank",
        "update_player_phase_reguliere",
        "update_rank_phase_reguliere",
        "update_rank_phase_finale",
        "update_register",
        "login.php",
        "register.php",
        "register_new_season.php",
        "recaptcha",
        "palmares",
        "stats",
        "register_same_top7team"
    ];

    /**
     * Initialize logger with log path
     *
     * @param string $logPath Path to log directory
     */
    public static function init(string $logPath): void {
        self::$logPath = $logPath;
    }

    /**
     * Log a message to file
     *
     * @param string $function Function name calling the log
     * @param string $name Variable/context name
     * @param string|null $msg Message to log
     */
    public static function log(string $function, string $name, ?string $msg): void {
        global $log_path; // For backward compatibility

        $logPath = self::$logPath ?? $log_path ?? '/tmp';

        // Only log if function is in debug whitelist
        if (in_array($function, self::$debugFunctions)) {
            $msgStr = $msg ?? 'NULL';
            $log = date("Ymd G:i:s ") . $function . ' [' . $name . '] ' . $msgStr . PHP_EOL;
            file_put_contents($logPath . '/log_' . date("Ymd") . '.txt', $log, FILE_APPEND);
        }
    }

    /**
     * Log a variable with var_export
     *
     * @param string $function Function name calling the log
     * @param string $name Variable name
     * @param mixed $var Variable to log
     */
    public static function logVar(string $function, string $name, $var): void {
        self::log($function, $name, var_export($var, true));
    }

    /**
     * Log an error and terminate execution
     *
     * @param string $function Function where error occurred
     * @param string $message Error message
     */
    public static function error(string $function, string $message): void {
        global $debug;

        self::log(__FUNCTION__, $function, $message);

        if ($debug === false) {
            header('location: index');
        }

        $d = date("Ymd G:i:s ");
        die("<pre>TOP7 - Error - See log at $d !</pre>");
    }

    /**
     * Print version information in footer
     */
    public static function printVersion(): void {
        echo "<div id=\"footer\">\n";
        echo "&copy; " . c_copyright . " - <a href=\"" . c_url_github . "\">PYL-AP" . "</a> - " . c_version . " - " . date("Ymd.Hi", filemtime("common.inc")) . "&nbsp;&nbsp;";
        echo "</div>\n";
    }

    /**
     * Add a function to the debug whitelist
     *
     * @param string $function Function name to add
     */
    public static function addDebugFunction(string $function): void {
        if (!in_array($function, self::$debugFunctions)) {
            self::$debugFunctions[] = $function;
        }
    }

    /**
     * Remove a function from the debug whitelist
     *
     * @param string $function Function name to remove
     */
    public static function removeDebugFunction(string $function): void {
        $key = array_search($function, self::$debugFunctions);
        if ($key !== false) {
            unset(self::$debugFunctions[$key]);
        }
    }

    /**
     * Get current debug whitelist
     *
     * @return array List of functions being debugged
     */
    public static function getDebugFunctions(): array {
        return self::$debugFunctions;
    }
}
