<?php
/**
 * Conecta ERP - Configuración de Tareas Programadas (CRON)
 * Definición de jobs y schedule
 */

return [
    // Zona horaria para CRON
    'timezone' => env('APP_TIMEZONE', 'America/Santiago'),

    // Habilitar/deshabilitar CRON globalmente
    'enabled' => env('CRON_ENABLED', true),

    // Configuración de overlap (solapamiento)
    'overlap' => [
        'prevent' => true, // Prevenir ejecuciones paralelas del mismo job
        'timeout' => 3600, // Timeout por defecto en segundos
    ],

    // Configuración de logs
    'logging' => [
        'enabled' => true,
        'log_output' => true,
        'log_errors' => true,
        'retention_days' => 30,
    ],

    // Email de notificaciones
    'notifications' => [
        'email' => env('CRON_NOTIFICATION_EMAIL', 'admin@conectaerp.com'),
        'on_failure' => true,
        'on_success' => false,
    ],

    // =====================================================
    // TAREAS PROGRAMADAS
    // =====================================================
    'jobs' => [
        // ================================================
        // DIARIAS
        // ================================================
        'daily' => [
            // Sincronización de tipos de cambio
            'sync_exchange_rates' => [
                'class' => 'App\Jobs\SyncExchangeRates',
                'schedule' => '0 9 * * *', // 9:00 AM diario
                'enabled' => true,
                'timeout' => 300,
                'retries' => 3,
                'description' => 'Sincronizar tipos de cambio desde Banco Central',
            ],

            // Verificación de vencimientos de documentos
            'check_document_expiration' => [
                'class' => 'App\Jobs\CheckDocumentExpiration',
                'schedule' => '0 8 * * *', // 8:00 AM diario
                'enabled' => true,
                'timeout' => 600,
                'description' => 'Verificar vencimiento de facturas, cotizaciones, etc',
            ],

            // Backup diario de base de datos
            'database_backup' => [
                'class' => 'App\Jobs\DatabaseBackup',
                'schedule' => '0 2 * * *', // 2:00 AM diario
                'enabled' => true,
                'timeout' => 1800,
                'description' => 'Backup completo de base de datos',
            ],

            // Limpieza de archivos temporales
            'cleanup_temp_files' => [
                'class' => 'App\Jobs\CleanupTempFiles',
                'schedule' => '0 3 * * *', // 3:00 AM diario
                'enabled' => true,
                'timeout' => 300,
                'description' => 'Eliminar archivos temporales antiguos',
            ],

            // Generación de reportes diarios
            'generate_daily_reports' => [
                'class' => 'App\Jobs\GenerateDailyReports',
                'schedule' => '0 7 * * *', // 7:00 AM diario
                'enabled' => true,
                'timeout' => 900,
                'description' => 'Generar reportes diarios automáticos',
            ],

            // Control de trial expirados
            'check_trial_expiration' => [
                'class' => 'App\Jobs\CheckTrialExpiration',
                'schedule' => '0 6 * * *', // 6:00 AM diario
                'enabled' => true,
                'timeout' => 300,
                'description' => 'Verificar y bloquear trials expirados',
            ],

            // Envío de recordatorios de pago
            'send_payment_reminders' => [
                'class' => 'App\Jobs\SendPaymentReminders',
                'schedule' => '0 10 * * *', // 10:00 AM diario
                'enabled' => true,
                'timeout' => 600,
                'description' => 'Enviar recordatorios de facturas por vencer',
            ],

            // Actualización de stock mínimo
            'check_minimum_stock' => [
                'class' => 'App\Jobs\CheckMinimumStock',
                'schedule' => '0 8 * * *', // 8:00 AM diario
                'enabled' => true,
                'timeout' => 600,
                'description' => 'Verificar productos bajo stock mínimo',
            ],

            // Sincronización con SII
            'sync_sii_status' => [
                'class' => 'App\Jobs\SyncSIIStatus',
                'schedule' => '0 11 * * *', // 11:00 AM diario
                'enabled' => true,
                'timeout' => 900,
                'description' => 'Sincronizar estado de DTEs con SII',
            ],
        ],

        // ================================================
        // HORARIAS
        // ================================================
        'hourly' => [
            // Procesamiento de cola de emails
            'process_email_queue' => [
                'class' => 'App\Jobs\ProcessEmailQueue',
                'schedule' => '0 * * * *', // Cada hora
                'enabled' => true,
                'timeout' => 600,
                'description' => 'Procesar cola de emails pendientes',
            ],

            // Limpieza de caché expirado
            'cleanup_expired_cache' => [
                'class' => 'App\Jobs\CleanupExpiredCache',
                'schedule' => '30 * * * *', // Cada hora a los 30 minutos
                'enabled' => true,
                'timeout' => 300,
                'description' => 'Limpiar entradas de caché expiradas',
            ],

            // Procesamiento de notificaciones
            'process_notifications' => [
                'class' => 'App\Jobs\ProcessNotifications',
                'schedule' => '*/15 * * * *', // Cada 15 minutos
                'enabled' => true,
                'timeout' => 300,
                'description' => 'Procesar y enviar notificaciones pendientes',
            ],
        ],

        // ================================================
        // SEMANALES
        // ================================================
        'weekly' => [
            // Generación de reportes semanales
            'generate_weekly_reports' => [
                'class' => 'App\Jobs\GenerateWeeklyReports',
                'schedule' => '0 7 * * 1', // Lunes 7:00 AM
                'enabled' => true,
                'timeout' => 1800,
                'description' => 'Generar reportes semanales',
            ],

            // Limpieza de logs antiguos
            'cleanup_old_logs' => [
                'class' => 'App\Jobs\CleanupOldLogs',
                'schedule' => '0 4 * * 0', // Domingo 4:00 AM
                'enabled' => true,
                'timeout' => 600,
                'description' => 'Eliminar logs antiguos (>30 días)',
            ],

            // Verificación de certificados SSL
            'check_ssl_certificates' => [
                'class' => 'App\Jobs\CheckSSLCertificates',
                'schedule' => '0 9 * * 1', // Lunes 9:00 AM
                'enabled' => true,
                'timeout' => 300,
                'description' => 'Verificar vencimiento de certificados SSL/SII',
            ],

            // Auditoría semanal de seguridad
            'security_audit' => [
                'class' => 'App\Jobs\SecurityAudit',
                'schedule' => '0 5 * * 0', // Domingo 5:00 AM
                'enabled' => true,
                'timeout' => 900,
                'description' => 'Auditoría de seguridad semanal',
            ],
        ],

        // ================================================
        // MENSUALES
        // ================================================
        'monthly' => [
            // Generación de reportes mensuales
            'generate_monthly_reports' => [
                'class' => 'App\Jobs\GenerateMonthlyReports',
                'schedule' => '0 7 1 * *', // Día 1 de cada mes a las 7:00 AM
                'enabled' => true,
                'timeout' => 3600,
                'description' => 'Generar reportes mensuales',
            ],

            // Generación automática REM Previred
            'generate_rem_previred' => [
                'class' => 'App\Jobs\GenerateREMPrevired',
                'schedule' => '0 8 5 * *', // Día 5 de cada mes a las 8:00 AM
                'enabled' => true,
                'timeout' => 1800,
                'description' => 'Generar archivo REM para Previred',
            ],

            // Cierre de período contable
            'close_accounting_period' => [
                'class' => 'App\Jobs\CloseAccountingPeriod',
                'schedule' => '0 6 1 * *', // Día 1 de cada mes a las 6:00 AM
                'enabled' => false, // Manual por defecto
                'timeout' => 1800,
                'description' => 'Cierre automático de período contable',
            ],

            // Cálculo de depreciaciones
            'calculate_depreciation' => [
                'class' => 'App\Jobs\CalculateDepreciation',
                'schedule' => '0 7 1 * *', // Día 1 de cada mes a las 7:00 AM
                'enabled' => true,
                'timeout' => 900,
                'description' => 'Calcular depreciaciones de activos fijos',
            ],

            // Limpieza profunda de base de datos
            'database_optimization' => [
                'class' => 'App\Jobs\DatabaseOptimization',
                'schedule' => '0 3 1 * *', // Día 1 de cada mes a las 3:00 AM
                'enabled' => true,
                'timeout' => 1800,
                'description' => 'Optimización y limpieza de BD',
            ],

            // Generación de libros contables
            'generate_accounting_books' => [
                'class' => 'App\Jobs\GenerateAccountingBooks',
                'schedule' => '0 8 1 * *', // Día 1 de cada mes a las 8:00 AM
                'enabled' => true,
                'timeout' => 1800,
                'description' => 'Generar libro diario y mayor',
            ],

            // Envío de estados de cuenta
            'send_account_statements' => [
                'class' => 'App\Jobs\SendAccountStatements',
                'schedule' => '0 9 1 * *', // Día 1 de cada mes a las 9:00 AM
                'enabled' => true,
                'timeout' => 1800,
                'description' => 'Enviar estados de cuenta a clientes',
            ],
        ],

        // ================================================
        // ANUALES
        // ================================================
        'yearly' => [
            // Balance anual
            'generate_yearly_balance' => [
                'class' => 'App\Jobs\GenerateYearlyBalance',
                'schedule' => '0 7 1 1 *', // 1 de enero 7:00 AM
                'enabled' => true,
                'timeout' => 3600,
                'description' => 'Generar balance anual',
            ],

            // Archivo histórico
            'archive_old_data' => [
                'class' => 'App\Jobs\ArchiveOldData',
                'schedule' => '0 2 1 1 *', // 1 de enero 2:00 AM
                'enabled' => true,
                'timeout' => 7200,
                'description' => 'Archivar datos antiguos (>2 años)',
            ],
        ],

        // ================================================
        // PERSONALIZADAS
        // ================================================
        'custom' => [
            // Sincronización bancaria
            'sync_bank_transactions' => [
                'class' => 'App\Jobs\SyncBankTransactions',
                'schedule' => '0 */4 * * *', // Cada 4 horas
                'enabled' => false, // Activar si hay integración bancaria
                'timeout' => 900,
                'description' => 'Sincronizar transacciones bancarias',
            ],

            // Actualización de precios
            'update_product_prices' => [
                'class' => 'App\Jobs\UpdateProductPrices',
                'schedule' => '0 23 * * *', // 11:00 PM diario
                'enabled' => false, // Manual por defecto
                'timeout' => 900,
                'description' => 'Actualizar precios de productos',
            ],

            // Generación de códigos de barras
            'generate_barcodes' => [
                'class' => 'App\Jobs\GenerateBarcodes',
                'schedule' => '0 4 * * *', // 4:00 AM diario
                'enabled' => false, // Bajo demanda
                'timeout' => 600,
                'description' => 'Generar códigos de barras faltantes',
            ],
        ],
    ],

    // =====================================================
    // Configuración de Retry
    // =====================================================
    'retry' => [
        'attempts' => 3,
        'backoff' => [
            'strategy' => 'exponential', // linear, exponential
            'delay' => 60, // segundos
            'max_delay' => 3600, // máximo 1 hora
        ],
    ],

    // =====================================================
    // Configuración de Queue
    // =====================================================
    'queue' => [
        'default' => env('CRON_QUEUE_CONNECTION', 'database'),
        'queue_name' => env('CRON_QUEUE_NAME', 'cron'),
    ],

    // =====================================================
    // Mantenimiento
    // =====================================================
    'maintenance' => [
        'mode' => env('CRON_MAINTENANCE_MODE', false),
        'allowed_jobs' => [
            'database_backup',
            'cleanup_temp_files',
        ],
    ],
];
