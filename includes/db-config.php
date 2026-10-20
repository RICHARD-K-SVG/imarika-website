<?php
/**
 * db-config.php
 * -----------------------------------------------------------------------
 * OPTIONAL: only needed if you want contact-form inquiries stored in
 * MySQL in addition to being emailed. If you don't need this, you can
 * leave contact-handler.php's storeInquiry() call disabled and ignore
 * this file entirely.
 *
 * Configure IMARIKA_DB_HOST, IMARIKA_DB_PORT, IMARIKA_DB_NAME,
 * IMARIKA_DB_USER, and IMARIKA_DB_PASSWORD in the PHP server environment.
 * Use a dedicated database user with only the privileges this site needs.
 * The password must be non-empty; never use the MySQL root account here.
 * -----------------------------------------------------------------------
 */

/**
 * Returns a PDO connection, or null if the DB is not configured/reachable.
 * contact-handler.php should treat a null return as "skip DB storage,
 * email-only is fine."
 */
function getDbConnection(): ?PDO
{
    $host = trim((string) getenv('IMARIKA_DB_HOST'));
    $portValue = getenv('IMARIKA_DB_PORT');
    $port = $portValue === false || $portValue === '' ? 3307 : filter_var($portValue, FILTER_VALIDATE_INT);
    $name = trim((string) getenv('IMARIKA_DB_NAME'));
    $user = trim((string) getenv('IMARIKA_DB_USER'));
    $password = (string) getenv('IMARIKA_DB_PASSWORD');

    if ($host === '' || $name === '' || $user === '' || $password === ''
        || $port === false || $port < 1 || $port > 65535) {
        error_log('DB connection skipped: required database environment variables are missing or invalid.');
        return null;
    }

    try {
        $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=utf8mb4';
        return new PDO($dsn, $user, $password, [
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
