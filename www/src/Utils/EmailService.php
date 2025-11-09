<?php
/**
 * EmailService - Email Sending Utility
 *
 * Provides email functionality for the application.
 * Extracted from common.inc as part of code modernization.
 *
 * @package Top7\Utils
 * @since Phase 1, Task 1.2.1
 */

namespace Top7\Utils;

class EmailService {

    /**
     * Send an email
     *
     * @param array $params Email parameters
     *   - dest: Recipient email address
     *   - subject: Email subject (without "Top7: " prefix)
     *   - msg: HTML message body
     * @return bool True if sent successfully
     */
    public static function send(array $params): bool {
        global $debug_email;

        setlocale(LC_TIME, "fr_FR");

        $emailAdmin = c_email_admin;
        $headers = "From: $emailAdmin" . "\r\n";
        $headers .= "Reply-To: $emailAdmin" . "\r\n";
        $headers .= "MIME-Version: 1.0" . "\r\n";
        $headers .= 'Content-Type: text/html; charset="utf-8"' . "\r\n";
        $headers .= 'Content-Transfer-Encoding: 8bit' . "\r\n";

        $dest    = $params['dest'];
        $subject = "Top7 : " . $params['subject'];

        $msg = "<html><head><title>Top7</title></head><body>";
        $msg .= $params['msg'];
        $msg .= "<br>_________________________________________<br>";
        $msg .= "<a href=\"" . c_url_top7 . "\">www.topseven.fr</a><br>";
        $msg .= "</body></html>";

        // Send to recipient (unless in debug mode)
        if (!$debug_email) {
            mail($dest, $subject, $msg, $headers);
        }

        // Always send copy to admin
        mail(c_email_pyl, $subject, $msg, $headers);

        return true;
    }

    /**
     * Send registration confirmation email
     *
     * @param string $pseudo Player pseudo
     * @param string $email Player email
     * @param int $team Team ID
     * @param bool $captain Is player a captain
     * @param string $key Validation key
     */
    public static function sendRegistration(string $pseudo, string $email, int $team, bool $captain, string $key): void {
        // This would be implemented by extracting send_email_register()
        // For now, call the legacy function
        if (function_exists('send_email_register')) {
            send_email_register($pseudo, $email, $team, $captain, $key);
        }
    }

    /**
     * Send password reset email
     *
     * @param int $playerId Player ID
     * @param string $key Reset key
     */
    public static function sendPasswordReset(int $playerId, string $key): void {
        // This would be implemented by extracting send_email_password()
        // For now, call the legacy function
        if (function_exists('send_email_password')) {
            send_email_password($playerId, $key);
        }
    }

    /**
     * Send alert to player
     *
     * @param array $params Alert parameters
     */
    public static function sendAlert(array $params): void {
        // This would be implemented by extracting send_email_alert_player()
        // For now, call the legacy function
        if (function_exists('send_email_alert_player')) {
            send_email_alert_player($params);
        }
    }

    /**
     * Validate email syntax
     *
     * @param string $email Email to validate
     * @return bool True if valid
     */
    public static function isValid(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
