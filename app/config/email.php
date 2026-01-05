<?php
/**
 * Conecta ERP - Configuración de Email
 * Configuración SMTP para envío de correos
 */

return [
    // Configuración principal
    'default' => env('MAIL_MAILER', 'smtp'),

    // Configuraciones por mailer
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'), // tls, ssl, null
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => 30,
            'auth_mode' => 'login', // login, plain, cram-md5
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs'),
        ],

        'mailgun' => [
            'transport' => 'mailgun',
            'domain' => env('MAILGUN_DOMAIN'),
            'secret' => env('MAILGUN_SECRET'),
            'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        ],

        'postmark' => [
            'transport' => 'postmark',
            'token' => env('POSTMARK_TOKEN'),
        ],

        'ses' => [
            'transport' => 'ses',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        ],
    ],

    // Configuración global de correos
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@conectaerp.com'),
        'name' => env('MAIL_FROM_NAME', 'Conecta ERP'),
    ],

    // Configuración de respuestas
    'reply_to' => [
        'address' => env('MAIL_REPLY_TO_ADDRESS', 'soporte@conectaerp.com'),
        'name' => env('MAIL_REPLY_TO_NAME', 'Soporte Conecta ERP'),
    ],

    // Configuración de CC y BCC por defecto
    'cc' => [
        // ['address' => 'cc@example.com', 'name' => 'CC Name'],
    ],

    'bcc' => [
        // ['address' => 'bcc@example.com', 'name' => 'BCC Name'],
    ],

    // Configuración de plantillas
    'templates' => [
        'path' => APP_PATH . '/views/emails/',
        'extension' => '.php',
        'cache' => STORAGE_PATH . '/cache/email_templates/',
    ],

    // Configuración de adjuntos
    'attachments' => [
        'max_size' => 10 * 1024 * 1024, // 10MB
        'allowed_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'png', 'zip'],
        'temp_path' => STORAGE_PATH . '/temp/email_attachments/',
    ],

    // Queue de emails
    'queue' => [
        'enabled' => env('MAIL_QUEUE_ENABLED', true),
        'connection' => env('MAIL_QUEUE_CONNECTION', 'database'),
        'queue' => env('MAIL_QUEUE_NAME', 'emails'),
        'retry_after' => 90, // segundos
        'max_attempts' => 3,
    ],

    // Límites y throttling
    'limits' => [
        'per_hour' => env('MAIL_LIMIT_PER_HOUR', 100),
        'per_day' => env('MAIL_LIMIT_PER_DAY', 1000),
        'burst' => env('MAIL_BURST_LIMIT', 10), // emails por minuto
    ],

    // Configuración de bounce y feedback
    'bounce' => [
        'enabled' => env('MAIL_BOUNCE_ENABLED', false),
        'email' => env('MAIL_BOUNCE_ADDRESS', 'bounce@conectaerp.com'),
    ],

    // Logging de emails
    'logging' => [
        'enabled' => env('MAIL_LOG_ENABLED', true),
        'log_sent' => true,
        'log_failed' => true,
        'log_content' => env('APP_DEBUG', false), // Solo en debug
        'retention_days' => 30,
    ],

    // Configuración de desarrollo
    'development' => [
        'catch_all' => env('MAIL_DEV_CATCH_ALL', null), // Enviar todos a este email en dev
        'log_only' => env('MAIL_DEV_LOG_ONLY', false), // Solo loguear, no enviar
    ],

    // Verificación de emails
    'verification' => [
        'enabled' => true,
        'expire' => 60, // minutos
        'throttle' => 6, // intentos por minuto
    ],

    // Configuración específica por país (Chile)
    'localization' => [
        'timezone' => 'America/Santiago',
        'date_format' => 'd/m/Y',
        'time_format' => 'H:i',
    ],

    // Webhooks para eventos de email
    'webhooks' => [
        'delivered' => env('MAIL_WEBHOOK_DELIVERED'),
        'bounced' => env('MAIL_WEBHOOK_BOUNCED'),
        'complained' => env('MAIL_WEBHOOK_COMPLAINED'),
        'opened' => env('MAIL_WEBHOOK_OPENED'),
        'clicked' => env('MAIL_WEBHOOK_CLICKED'),
    ],

    // Configuración de encriptación
    'encryption' => [
        'sign_emails' => env('MAIL_SIGN_EMAILS', false),
        'encrypt_emails' => env('MAIL_ENCRYPT_EMAILS', false),
        'certificate_path' => STORAGE_PATH . '/certificates/email/',
    ],
];
