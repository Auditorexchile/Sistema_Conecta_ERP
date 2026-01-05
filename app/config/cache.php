<?php
/**
 * Conecta ERP - Configuración de Caché
 * Redis, Memcached, File cache
 */

return [
    // Driver de caché por defecto
    'default' => env('CACHE_DRIVER', 'redis'),

    // Prefijo para todas las keys de caché
    'prefix' => env('CACHE_PREFIX', 'conecta_erp_cache'),

    // TTL por defecto (en segundos)
    'ttl' => env('CACHE_TTL', 3600), // 1 hora

    // =====================================================
    // Stores de Caché
    // =====================================================
    'stores' => [
        // File-based cache
        'file' => [
            'driver' => 'file',
            'path' => STORAGE_PATH . '/cache/data',
            'permissions' => [
                'file' => 0644,
                'dir' => 0755,
            ],
        ],

        // Redis
        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
        ],

        // Memcached
        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Opciones de Memcached
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],

        // APCu
        'apcu' => [
            'driver' => 'apcu',
        ],

        // Array (solo para testing)
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        // Database cache
        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
            'lock_table' => 'cache_locks',
            'lock_lottery' => [2, 100],
        ],
    ],

    // =====================================================
    // Conexiones Redis
    // =====================================================
    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'), // phpredis, predis

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', 'conecta_erp_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
            'read_timeout' => 60,
            'context' => [],
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
            'read_timeout' => 60,
        ],

        'session' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_SESSION_DB', 2),
            'read_timeout' => 60,
        ],

        'queue' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_QUEUE_DB', 3),
            'read_timeout' => 60,
        ],
    ],

    // =====================================================
    // Configuración de Cache Tags
    // =====================================================
    'tags' => [
        'enabled' => true,
        'separator' => ':',
    ],

    // =====================================================
    // Configuración de Cache por Tipo
    // =====================================================
    'types' => [
        // Caché de configuración
        'config' => [
            'store' => 'redis',
            'ttl' => 86400, // 24 horas
            'tags' => ['config'],
        ],

        // Caché de datos de productos
        'products' => [
            'store' => 'redis',
            'ttl' => 3600, // 1 hora
            'tags' => ['products', 'catalog'],
        ],

        // Caché de precios
        'prices' => [
            'store' => 'redis',
            'ttl' => 1800, // 30 minutos
            'tags' => ['prices'],
        ],

        // Caché de stock
        'stock' => [
            'store' => 'redis',
            'ttl' => 300, // 5 minutos
            'tags' => ['stock', 'inventory'],
        ],

        // Caché de clientes
        'customers' => [
            'store' => 'redis',
            'ttl' => 7200, // 2 horas
            'tags' => ['customers'],
        ],

        // Caché de sesiones
        'sessions' => [
            'store' => 'redis',
            'ttl' => 7200, // 2 horas
            'tags' => ['sessions'],
        ],

        // Caché de reportes
        'reports' => [
            'store' => 'redis',
            'ttl' => 3600, // 1 hora
            'tags' => ['reports'],
        ],

        // Caché de queries SQL
        'queries' => [
            'store' => 'redis',
            'ttl' => 600, // 10 minutos
            'tags' => ['queries'],
        ],

        // Caché de APIs externas
        'api_responses' => [
            'store' => 'redis',
            'ttl' => 1800, // 30 minutos
            'tags' => ['api'],
        ],

        // Caché de tipo de cambio
        'exchange_rates' => [
            'store' => 'redis',
            'ttl' => 3600, // 1 hora
            'tags' => ['exchange_rates', 'currency'],
        ],
    ],

    // =====================================================
    // Lock/Mutex Configuration
    // =====================================================
    'locks' => [
        'default' => env('CACHE_LOCK_DRIVER', 'redis'),

        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
        ],

        'memcached' => [
            'driver' => 'memcached',
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'cache_locks',
            'connection' => null,
        ],
    ],

    // =====================================================
    // Warming y Preloading
    // =====================================================
    'warming' => [
        'enabled' => env('CACHE_WARMING_ENABLED', false),

        // Items a precalentar al inicio
        'items' => [
            'config' => true,
            'products_catalog' => true,
            'exchange_rates' => true,
            'tax_rates' => true,
        ],

        // Cron para recalentar caché
        'schedule' => '0 */6 * * *', // Cada 6 horas
    ],

    // =====================================================
    // Estrategias de Invalidación
    // =====================================================
    'invalidation' => [
        // Invalidación automática al actualizar datos
        'auto_invalidate' => true,

        // Estrategias por entidad
        'strategies' => [
            'products' => 'tag', // Invalidar por tag
            'prices' => 'key', // Invalidar key específica
            'stock' => 'pattern', // Invalidar por patrón
        ],

        // Eventos que disparan invalidación
        'events' => [
            'product.updated' => ['products', 'catalog'],
            'price.changed' => ['prices'],
            'stock.updated' => ['stock', 'inventory'],
            'customer.updated' => ['customers'],
        ],
    ],

    // =====================================================
    // Compresión
    // =====================================================
    'compression' => [
        'enabled' => env('CACHE_COMPRESSION_ENABLED', true),
        'algorithm' => env('CACHE_COMPRESSION_ALGO', 'gzip'), // gzip, lz4, zstd
        'level' => env('CACHE_COMPRESSION_LEVEL', 6), // 1-9
        'min_size' => 1024, // Comprimir solo si > 1KB
    ],

    // =====================================================
    // Serialización
    // =====================================================
    'serialization' => [
        'serializer' => env('CACHE_SERIALIZER', 'php'), // php, igbinary, json, msgpack
    ],

    // =====================================================
    // Monitoreo y Estadísticas
    // =====================================================
    'monitoring' => [
        'enabled' => env('CACHE_MONITORING_ENABLED', true),

        'stats' => [
            'track_hits' => true,
            'track_misses' => true,
            'track_writes' => true,
            'track_deletes' => true,
        ],

        'alerts' => [
            'hit_rate_threshold' => 0.8, // Alertar si < 80%
            'memory_usage_threshold' => 0.9, // Alertar si > 90%
        ],
    ],

    // =====================================================
    // Limpieza Automática
    // =====================================================
    'garbage_collection' => [
        'enabled' => true,
        'probability' => 2,
        'divisor' => 100,
        'schedule' => '0 3 * * *', // 3 AM diario
    ],

    // =====================================================
    // Desarrollo y Debug
    // =====================================================
    'development' => [
        'disable_cache' => env('CACHE_DISABLED', false),
        'force_refresh' => env('CACHE_FORCE_REFRESH', false),
        'log_hits' => env('CACHE_LOG_HITS', false),
        'log_misses' => env('CACHE_LOG_MISSES', true),
    ],
];
