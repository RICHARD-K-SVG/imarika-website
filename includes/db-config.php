<?php
/**
 * db-config.php
 * -----------------------------------------------------------------------
 * OPTIONAL: only needed if you want contact-form inquiries stored in
 * MySQL in addition to being emailed. If you don't need this, you can
 * leave contact-handler.php's storeInquiry() call disabled and ignore
 * this file entirely.
 *
 * IMPORTANT: replace the placeholder values below with real credentials
 * from your hosting provider before going live. Never commit real
 * credentials to a public repository — consider loading these from
 * environment variables instead once hosting is confirmed.
 * -----------------------------------------------------------------------
 */

// PLACEHOLDER — update once hosting is confirmed
define('DB_HOST', 'localhost');
define('DB_NAME', 'imarika_website');
define('DB_USER', 'CHANGE_ME');
define('DB_PASS', 'CHANGE_ME');

/**
 * Returns a PDO connection, or null if the DB is not configured/reachable.
 * contact-handler.php should treat a null return as "skip DB storage,
 * email-only is fine."
 */
function getDbConnection(): ?PDO {
    if (DB_USER === 'CHANGE_ME') {
        // Not configured yet — fail silently so email-only flow still works.
        return null;
    }

    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        return new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    } catch (PDOException $e) {
        error_log('DB connection failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Suggested table, if you choose to enable DB storage:
 *
 * CREATE TABLE inquiries (
 *   id INT AUTO_INCREMENT PRIMARY KEY,
 *   name VARCHAR(255) NOT NULL,
 *   email VARCHAR(255) NOT NULL,
 *   message TEXT NOT NULL,
 *   submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
 * );
 */
