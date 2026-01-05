<?php
/**
 * Conecta ERP - Configuración de Integraciones
 * Credenciales y configuración para APIs externas
 */

return [
    // =====================================================
    // SII (Servicio de Impuestos Internos - Chile)
    // =====================================================
    'sii' => [
        'enabled' => env('SII_ENABLED', true),
        'ambiente' => env('SII_AMBIENTE', 'certificacion'), // certificacion, produccion

        'certificacion' => [
            'url_autenticacion' => 'https://maullin.sii.cl/DTEWS/CrSeed.jws',
            'url_envio' => 'https://maullin.sii.cl/DTEWS/RecepcionDTE',
            'url_consulta' => 'https://maullin.sii.cl/DTEWS/QueryEstDte.jws',
            'url_libro' => 'https://maullin.sii.cl/DTEWS/services/RecepcionLCV',
        ],

        'produccion' => [
            'url_autenticacion' => 'https://palena.sii.cl/DTEWS/CrSeed.jws',
            'url_envio' => 'https://palena.sii.cl/DTEWS/RecepcionDTE',
            'url_consulta' => 'https://palena.sii.cl/DTEWS/QueryEstDte.jws',
            'url_libro' => 'https://palena.sii.cl/DTEWS/services/RecepcionLCV',
        ],

        'certificado' => [
            'path' => STORAGE_PATH . '/certificates/sii/',
            'password' => env('SII_CERT_PASSWORD'),
            'rut_empresa' => env('SII_RUT_EMPRESA'),
            'razon_social' => env('SII_RAZON_SOCIAL'),
        ],

        'folios' => [
            'path' => STORAGE_PATH . '/folios/sii/',
            'auto_request' => env('SII_AUTO_REQUEST_FOLIOS', true),
            'min_remaining' => 10, // Solicitar nuevos cuando queden menos de 10
        ],

        'timeout' => 30,
        'retry_attempts' => 3,
        'retry_delay' => 2, // segundos
    ],

    // =====================================================
    // Previred (Chile)
    // =====================================================
    'previred' => [
        'enabled' => env('PREVIRED_ENABLED', true),
        'ambiente' => env('PREVIRED_AMBIENTE', 'certificacion'),

        'certificacion' => [
            'url' => 'https://www.previred.com/webtestnet/api/v1/',
        ],

        'produccion' => [
            'url' => 'https://www.previred.com/webprevired/api/v1/',
        ],

        'credentials' => [
            'rut_empleador' => env('PREVIRED_RUT_EMPLEADOR'),
            'usuario' => env('PREVIRED_USUARIO'),
            'password' => env('PREVIRED_PASSWORD'),
        ],

        'afp' => [
            'codigo' => env('PREVIRED_AFP_CODIGO'),
            'tasa' => env('PREVIRED_AFP_TASA', 11.44), // %
        ],

        'salud' => [
            'tipo' => env('PREVIRED_SALUD_TIPO', 'fonasa'), // fonasa, isapre
            'codigo_isapre' => env('PREVIRED_ISAPRE_CODIGO'),
            'tasa_base' => 7.0, // %
        ],

        'timeout' => 60,
        'auto_generate' => env('PREVIRED_AUTO_GENERATE', false),
        'generation_day' => 5, // Día del mes para generar REM
    ],

    // =====================================================
    // APIs Bancarias
    // =====================================================
    'banks' => [
        'enabled' => env('BANKING_API_ENABLED', false),

        'banco_chile' => [
            'enabled' => env('BCH_ENABLED', false),
            'api_url' => 'https://api.bancochile.cl/v1/',
            'api_key' => env('BCH_API_KEY'),
            'api_secret' => env('BCH_API_SECRET'),
            'timeout' => 30,
        ],

        'banco_estado' => [
            'enabled' => env('BE_ENABLED', false),
            'api_url' => 'https://api.bancoestado.cl/v1/',
            'api_key' => env('BE_API_KEY'),
            'api_secret' => env('BE_API_SECRET'),
            'timeout' => 30,
        ],

        'santander' => [
            'enabled' => env('SANTANDER_ENABLED', false),
            'api_url' => 'https://openapi.santander.cl/v1/',
            'api_key' => env('SANTANDER_API_KEY'),
            'api_secret' => env('SANTANDER_API_SECRET'),
            'timeout' => 30,
        ],

        'sync' => [
            'auto_sync' => env('BANK_AUTO_SYNC', false),
            'sync_frequency' => env('BANK_SYNC_FREQUENCY', 'daily'), // hourly, daily, weekly
            'sync_time' => '02:00', // Hora de sincronización automática
        ],
    ],

    // =====================================================
    // Pasarelas de Pago
    // =====================================================
    'payment_gateways' => [
        'transbank' => [
            'enabled' => env('TRANSBANK_ENABLED', false),
            'ambiente' => env('TRANSBANK_AMBIENTE', 'integracion'),

            'webpay_plus' => [
                'commerce_code' => env('TRANSBANK_COMMERCE_CODE'),
                'api_key' => env('TRANSBANK_API_KEY'),
            ],

            'integracion' => [
                'url' => 'https://webpay3gint.transbank.cl/',
            ],

            'produccion' => [
                'url' => 'https://webpay3g.transbank.cl/',
            ],

            'timeout' => 30,
        ],

        'mercadopago' => [
            'enabled' => env('MERCADOPAGO_ENABLED', false),
            'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
            'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
            'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'),
            'timeout' => 30,
        ],

        'stripe' => [
            'enabled' => env('STRIPE_ENABLED', false),
            'public_key' => env('STRIPE_PUBLIC_KEY'),
            'secret_key' => env('STRIPE_SECRET_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'timeout' => 30,
        ],

        'paypal' => [
            'enabled' => env('PAYPAL_ENABLED', false),
            'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox, live
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'timeout' => 30,
        ],
    ],

    // =====================================================
    // APIs de Tipo de Cambio
    // =====================================================
    'exchange_rates' => [
        'provider' => env('EXCHANGE_RATE_PROVIDER', 'banco_central_chile'),

        'banco_central_chile' => [
            'enabled' => true,
            'url' => 'https://si3.bcentral.cl/SieteRestWS/SieteRestWS.ashx',
            'usuario' => env('BCCh_API_USER'),
            'password' => env('BCCh_API_PASSWORD'),
            'auto_update' => true,
            'update_frequency' => 'daily',
            'update_time' => '09:00',
        ],

        'fixer_io' => [
            'enabled' => env('FIXER_ENABLED', false),
            'url' => 'https://api.fixer.io/latest',
            'api_key' => env('FIXER_API_KEY'),
        ],

        'openexchangerates' => [
            'enabled' => env('OER_ENABLED', false),
            'url' => 'https://openexchangerates.org/api/latest.json',
            'app_id' => env('OER_APP_ID'),
        ],

        'cache_duration' => 3600, // 1 hora
        'fallback_enabled' => true,
    ],

    // =====================================================
    // SMS y WhatsApp
    // =====================================================
    'sms' => [
        'provider' => env('SMS_PROVIDER', 'twilio'),

        'twilio' => [
            'enabled' => env('TWILIO_ENABLED', false),
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from_number' => env('TWILIO_FROM_NUMBER'),
            'timeout' => 30,
        ],

        'nexmo' => [
            'enabled' => env('NEXMO_ENABLED', false),
            'api_key' => env('NEXMO_API_KEY'),
            'api_secret' => env('NEXMO_API_SECRET'),
            'from_name' => env('NEXMO_FROM_NAME', 'ConectaERP'),
        ],
    ],

    'whatsapp' => [
        'enabled' => env('WHATSAPP_ENABLED', false),
        'provider' => env('WHATSAPP_PROVIDER', 'twilio'),

        'twilio' => [
            'number' => env('TWILIO_WHATSAPP_NUMBER'),
        ],

        'gupshup' => [
            'api_key' => env('GUPSHUP_API_KEY'),
            'app_name' => env('GUPSHUP_APP_NAME'),
        ],
    ],

    // =====================================================
    // Almacenamiento en la Nube
    // =====================================================
    'cloud_storage' => [
        'default' => env('CLOUD_STORAGE_DEFAULT', 's3'),

        's3' => [
            'enabled' => env('S3_ENABLED', false),
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),
        ],

        'google_cloud' => [
            'enabled' => env('GCS_ENABLED', false),
            'project_id' => env('GCS_PROJECT_ID'),
            'key_file' => env('GCS_KEY_FILE'),
            'bucket' => env('GCS_BUCKET'),
        ],

        'azure' => [
            'enabled' => env('AZURE_ENABLED', false),
            'account_name' => env('AZURE_STORAGE_ACCOUNT'),
            'account_key' => env('AZURE_STORAGE_KEY'),
            'container' => env('AZURE_STORAGE_CONTAINER'),
        ],
    ],

    // =====================================================
    // Servicios de Geolocalización
    // =====================================================
    'geolocation' => [
        'provider' => env('GEO_PROVIDER', 'google_maps'),

        'google_maps' => [
            'enabled' => env('GOOGLE_MAPS_ENABLED', false),
            'api_key' => env('GOOGLE_MAPS_API_KEY'),
            'timeout' => 10,
        ],

        'mapbox' => [
            'enabled' => env('MAPBOX_ENABLED', false),
            'access_token' => env('MAPBOX_ACCESS_TOKEN'),
        ],
    ],

    // =====================================================
    // Análisis y Monitoreo
    // =====================================================
    'analytics' => [
        'google_analytics' => [
            'enabled' => env('GA_ENABLED', false),
            'tracking_id' => env('GA_TRACKING_ID'),
            'view_id' => env('GA_VIEW_ID'),
        ],

        'sentry' => [
            'enabled' => env('SENTRY_ENABLED', false),
            'dsn' => env('SENTRY_DSN'),
            'environment' => env('APP_ENV', 'production'),
        ],

        'new_relic' => [
            'enabled' => env('NEW_RELIC_ENABLED', false),
            'license_key' => env('NEW_RELIC_LICENSE_KEY'),
            'app_name' => env('NEW_RELIC_APP_NAME', 'Conecta ERP'),
        ],
    ],

    // =====================================================
    // Configuración Global
    // =====================================================
    'global' => [
        'timeout' => env('API_DEFAULT_TIMEOUT', 30),
        'retry_attempts' => env('API_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('API_RETRY_DELAY', 2),
        'verify_ssl' => env('API_VERIFY_SSL', true),
        'user_agent' => 'ConectaERP/1.0',
        'log_requests' => env('API_LOG_REQUESTS', true),
        'log_responses' => env('API_LOG_RESPONSES', false),
    ],
];
