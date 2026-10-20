<?php
/**
 * Read mail credentials from the server environment, never from page source.
 * Set IMARIKA_SMTP_HOST, IMARIKA_SMTP_USERNAME, IMARIKA_SMTP_PASSWORD,
 * IMARIKA_SMTP_PORT, IMARIKA_SCHOOL_EMAIL, and IMARIKA_FROM_EMAIL.
 */
function getSchoolMailConfig(): ?array
{
    $config = [
        'host' => trim((string) getenv('IMARIKA_SMTP_HOST')),
        'username' => trim((string) getenv('IMARIKA_SMTP_USERNAME')),
        'password' => (string) getenv('IMARIKA_SMTP_PASSWORD'),
        'school_email' => trim((string) getenv('IMARIKA_SCHOOL_EMAIL')),
        'from_email' => trim((string) getenv('IMARIKA_FROM_EMAIL')),
    ];

    foreach ($config as $value) {
        if ($value === '') {
            error_log('SMTP configuration skipped: required mail environment variables are missing.');
            return null;
        }
    }

    if (filter_var($config['school_email'], FILTER_VALIDATE_EMAIL) === false
        || filter_var($config['from_email'], FILTER_VALIDATE_EMAIL) === false) {
        error_log('SMTP configuration skipped: school or sender email address is invalid.');
        return null;
    }

    $portValue = getenv('IMARIKA_SMTP_PORT');
    $port = $portValue === false || $portValue === ''
        ? 587
        : filter_var($portValue, FILTER_VALIDATE_INT);
    if ($port === false || $port < 1 || $port > 65535) {
        error_log('SMTP configuration skipped: IMARIKA_SMTP_PORT must be a valid TCP port.');
        return null;
    }
    $config['port'] = $port;

    return $config;
}