<?php
/**
 * Read mail credentials from the server environment, never from page source.
 * Set IMARIKA_SMTP_HOST, IMARIKA_SMTP_USERNAME, IMARIKA_SMTP_PASSWORD,
 * IMARIKA_SMTP_PORT, IMARIKA_SCHOOL_EMAIL, and IMARIKA_FROM_EMAIL.
 */
function getSchoolMailConfig(): ?array
{
    $config = [
        'host' => trim((string) getenv('sandbox.smtp.mailtrap.io')),
        'username' => trim((string) getenv('0e658f9e03b9b2')),
        'password' => (string) getenv('a4e9ecd10d1de6'),
        'school_email' => trim((string) getenv('imarikapreprimaryschool@gmail.com')),
        'from_email' => trim((string) getenv('imarikapreprimaryschool@gmail.com')),
    ];

    foreach ($config as $value) {
        if ($value === '') {
            return null;
        }
    }

    $port = filter_var(getenv('IMARIKA_SMTP_PORT'), FILTER_VALIDATE_INT);
    $config['port'] = $port !== false && $port > 0 ? $port : 587;

    return $config;
}