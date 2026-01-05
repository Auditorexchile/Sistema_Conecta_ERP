<?php
/**
 * Conecta ERP - Configuración de Logging
 * Sistema centralizado de logs y monitoreo
 */

return [
    // Canal de log por defecto
    'default' => env('LOG_CHANNEL', 'stack'),

    // Nivel de log por defecto
    'level' => env('LOG_LEVEL', 'debug'), // emergency, alert, critical, error, warning, notice, info, debug

    // =====================================================
    // Canales de Logging
    // =====================================================
    'channels' => [
        // Stack: múltiples canales
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily', 'slack'],
            'ignore_exceptions' => false,
        ],

        // Single: archivo único
        'single' => [
            'driver' => 'single',
            'path' => STORAGE_PATH . '/logs/conecta-erp.log',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        // Daily: archivo diario
        'daily' => [
            'driver' => 'daily',
            'path' => STORAGE_PATH . '/logs/conecta-erp.log',
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        // Slack
        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Conecta ERP Logger',
            'emoji' => ':boom:',
            'level' => env('LOG_SLACK_LEVEL', 'critical'),
        ],

        // Syslog
        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => LOG_USER,
            'replace_placeholders' => true,
        ],

        // ErrorLog
        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        // Null (no logging)
        'null' => [
            'driver' => 'monolog',
            'handler' => 'Monolog\Handler\NullHandler',
        ],

        // Emergency
        'emergency' => [
            'path' => STORAGE_PATH . '/logs/emergency.log',
        ],
    ],

    // =====================================================
    // Logs Personalizados por Módulo
    // =====================================================
    'modules' => [
        // Logs de aplicación general
        'app' => [
            'driver' => 'daily',
            'path' => STORAGE_PATH . '/logs/app/application.log',
            'level' => 'debug',
            'days' => 30,
        ],

        // Logs de errores
        'error' => [
            'driver' => 'daily',
            'path' => STORAGE_PATH . '/logs/errors/errors.log',
            'level' => 'error',
            'days' => 90,
        ],

        // Logs de SQL
        'sql' => [
            'enabled' => env('LOG_SQL_ENABLED', false),
            'driver' => 'daily',
            'path' => STORAGE_PATH . '/logs/sql/queries.log',
            'level' => 'debug',
            'days' => 7,
            'log_slow_queries' => true,
            'slow_query_threshold' => 1000, // ms
        ],

        // Logs de seguridad
        'security' => [
            'driver' => 'daily',
            'path' => STORAGE_PATH . '/logs/security/security.log',
            'level' => 'info',
            'days' => 90,
            'events' => [
                'login',
                'logout',
                'failed_login',
                'password_reset',
                'permission_denied',
                'suspicious_activity',
            ],
        ],

        // Logs de auditoría
        'audit' => [
            'driver' => 'daily',
            'path' => STORAGE_PATH . '/logs/audit/audit.log',
            'level' => 'info',
            'days' => 365,
            'track' => [
                'create',
                'update',
                'delete',
                'export',
                'import',
            ],
        ],

        // Logs de API
        'api' => [
            'enabled' => env('LOG_API_ENABLED', true),
            'driver' => 'daily',
            'path' => STORAGE_PATH . '/logs/api/api.log',
            'level' => 'info',
            'days' => 30,
            'log_request' => true,
            'log_response' => env('LOG_API_RESPONSE', false),
            'log_headers' => false,
            'sensitive_fields' => ['password', 'token', 'api_key', 'secret'],
        ],

        // Logs de integraciones
        'integrations' => [
            'enabled' => true,
            'driver' => 'daily',
            'path' => STORAGE_PATH . '/logs/integrations/',
            'level' => 'info',
            'days' => 60,

            'channels' => [
                'sii' => [
                    'path' => STORAGE_PATH . '/logs/integrations/sii.log',
                    'log_requests' => true,
                    'log_responses' => true,
                ],
                'previred' => [
                    'path' => STORAGE_PATH . '/logs/integrations/previred.log',
                    'log_requests' => true,
                    'log_responses' => true,
                ],
                'banks' => [
                    'path' => STORAGE_PATH . '/logs/integrations/banks.log',
                    'log_requests' => true,
                    'log_responses' => false,
                ],
                'payments' => [
                    'path' => STORAGE_PATH . '/logs/integrations/payments.log',
                    'log_requests' => true,
                    'log_responses' => true,
                ],
            ],
        ],

        // Logs de CRON
        'cron' => [
            'driver' => 'daily',
            'path' => STORAGE_PATH . '/logs/cron/cron.log',
            'level' => 'info',
            'days' => 30,
            'log_output' => true,
            'log_errors' => true,
        ],

        // Logs de email
        'email' => [
            'enabled' => env('LOG_EMAIL_ENABLED', true),
            'driver' => 'daily',
            'path' => STORAGE_PATH . '/logs/email/email.log',
            'level' => 'info',
            'days' => 30,
            'log_sent' => true,
            'log_failed' => true,
            'log_content' => env('APP_DEBUG', false),
        ],

        // Logs de performance
        'performance' => [
            'enabled' => env('LOG_PERFORMANCE_ENABLED', false),
            'driver' => 'daily',
            'path' => STORAGE_PATH . '/logs/performance/performance.log',
            'level' => 'info',
            'days' => 7,
            'track' => [
                'page_load_time',
                'query_time',
                'memory_usage',
                'cpu_usage',
            ],
            'thresholds' => [
                'page_load' => 3000, // ms
                'query' => 1000, // ms
                'memory' => 128 * 1024 * 1024, // 128MB
            ],
        ],

        // Logs de cache
        'cache' => [
            'enabled' => env('LOG_CACHE_ENABLED', false),
            'driver' => 'daily',
            'path' => STORAGE_PATH . '/logs/cache/cache.log',
            'level' => 'debug',
            'days' => 7,
            'log_hits' => false,
            'log_misses' => true,
            'log_writes' => true,
        ],

        // Logs de queue
        'queue' => [
            'enabled' => env('LOG_QUEUE_ENABLED', true),
            'driver' => 'daily',
            'path' => STORAGE_PATH . '/logs/queue/queue.log',
            'level' => 'info',
            'days' => 30,
            'log_processed' => true,
            'log_failed' => true,
        ],

        // Logs de notificaciones
        'notifications' => [
            'driver' => 'daily',
            'path' => STORAGE_PATH . '/logs/notifications/notifications.log',
            'level' => 'info',
            'days' => 30,
        ],

        // Logs de workflow
        'workflow' => [
            'driver' => 'daily',
            'path' => STORAGE_PATH . '/logs/workflow/workflow.log',
            'level' => 'info',
            'days' => 60,
            'track_approvals' => true,
            'track_rejections' => true,
        ],
    ],

    // =====================================================
    // Formateo de Logs
    // =====================================================
    'formatting' => [
        'default_format' => env('LOG_FORMAT', 'line'), // line, json, custom

        'line_format' => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",

        'datetime_format' => 'Y-m-d H:i:s',

        'include_context' => true,
        'include_extra' => true,

        'processors' => [
            'uid' => true, // Unique ID por request
            'web' => true, // Info web (IP, user agent)
            'git' => false, // Info de git
            'memory_usage' => true,
            'memory_peak_usage' => true,
        ],
    ],

    // =====================================================
    // Rotación de Logs
    // =====================================================
    'rotation' => [
        'enabled' => true,
        'max_files' => env('LOG_MAX_FILES', 30),
        'compress' => env('LOG_COMPRESS', true),
        'compression_format' => 'gz', // gz, bz2, zip
    ],

    // =====================================================
    // Filtros
    // =====================================================
    'filters' => [
        // No loguear estos paths
        'ignore_paths' => [
            '/health-check',
            '/ping',
            '/favicon.ico',
        ],

        // No loguear estos IPs
        'ignore_ips' => [
            // '127.0.0.1',
        ],

        // Redactar información sensible
        'redact_sensitive' => true,
        'sensitive_keys' => [
            'password',
            'password_confirmation',
            'token',
            'api_key',
            'api_secret',
            'secret',
            'private_key',
            'certificate',
            'card_number',
            'cvv',
            'ssn',
            'rut', // Opcional para Chile
        ],
        'redact_replacement' => '***REDACTED***',
    ],

    // =====================================================
    // Alertas
    // =====================================================
    'alerts' => [
        'enabled' => env('LOG_ALERTS_ENABLED', true),

        // Alertar cuando hay muchos errores
        'error_threshold' => [
            'enabled' => true,
            'count' => 10, // errores
            'period' => 300, // en 5 minutos
            'notify' => ['email', 'slack'],
        ],

        // Alertar en errores críticos
        'critical_errors' => [
            'enabled' => true,
            'notify_immediately' => true,
            'notify' => ['email', 'slack', 'sms'],
        ],

        // Alertar en intentos de acceso sospechosos
        'security_alerts' => [
            'enabled' => true,
            'events' => [
                'multiple_failed_logins',
                'sql_injection_attempt',
                'xss_attempt',
                'csrf_token_mismatch',
                'permission_denied',
            ],
            'notify' => ['email', 'slack'],
        ],
    ],

    // =====================================================
    // Agregación y Análisis
    // =====================================================
    'aggregation' => [
        'enabled' => env('LOG_AGGREGATION_ENABLED', false),

        // Enviar logs a servicio externo
        'services' => [
            'sentry' => [
                'enabled' => env('SENTRY_ENABLED', false),
                'dsn' => env('SENTRY_DSN'),
                'environment' => env('APP_ENV', 'production'),
                'traces_sample_rate' => 0.1,
            ],

            'logstash' => [
                'enabled' => env('LOGSTASH_ENABLED', false),
                'host' => env('LOGSTASH_HOST', 'localhost'),
                'port' => env('LOGSTASH_PORT', 5000),
            ],

            'papertrail' => [
                'enabled' => env('PAPERTRAIL_ENABLED', false),
                'host' => env('PAPERTRAIL_HOST'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],
    ],

    // =====================================================
    // Limpieza Automática
    // =====================================================
    'cleanup' => [
        'enabled' => true,
        'retention' => [
            'emergency' => 365, // días
            'alert' => 180,
            'critical' => 90,
            'error' => 60,
            'warning' => 30,
            'notice' => 14,
            'info' => 7,
            'debug' => 3,
        ],
        'schedule' => '0 3 * * *', // 3 AM diario
    ],

    // =====================================================
    // Desarrollo
    // =====================================================
    'development' => [
        'debug_mode' => env('APP_DEBUG', false),
        'verbose' => env('LOG_VERBOSE', false),
        'pretty_print' => env('LOG_PRETTY_PRINT', true),
        'log_deprecations' => env('LOG_DEPRECATIONS', true),
    ],
];
