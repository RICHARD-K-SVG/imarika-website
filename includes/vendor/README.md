# vendor/ — PHPMailer

This folder is a placeholder. `contact-handler.php` expects PHPMailer to be
installed here via Composer before the contact form will work.

## Install (run on your dev machine or server, not in this sandbox)

```bash
cd includes
composer require phpmailer/phpmailer
```

This will create `includes/vendor/autoload.php` and the PHPMailer source,
which `contact-handler.php` already requires.

If the hosting environment doesn't support Composer, you can instead
download PHPMailer directly from https://github.com/PHPMailer/PHPMailer
and adjust the `require` path at the top of `contact-handler.php`
accordingly.

## After installing

Update the placeholder values in `contact-handler.php`:
- `$SCHOOL_RECEIVING_EMAIL`
- `$SMTP_HOST`, `$SMTP_USERNAME`, `$SMTP_PASSWORD`, `$SMTP_PORT`

These should come from whatever hosting/email provider the school
confirms (see PROJECT_STRUCTURE.md, Section 7, Open Action Item #1).
