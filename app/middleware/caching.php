<?php
namespace App\Middleware;

/**
 * Conecta ERP - Middleware de Caching HTTP
 * Implementa cache de responses para mejorar performance
 */

class CachingMiddleware {
    private $cache;
    private $config;

    public function __construct() {
        $this->cache = $this->initializeCache();
        $this->config = $this->loadConfig();
    }

    /**
     * Procesa caching del request/response
     */
    public function handle($request, $next) {
        // Solo cachear GET requests
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return $next($request);
        }

        // Verificar si el caching está habilitado
        if (!$this->config['enabled']) {
            return $next($request);
        }

        // Verificar si esta ruta debe cachearse
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        if (!$this->shouldCache($uri)) {
            return $next($request);
        }

        // Generar key de cache
        $cacheKey = $this->generateCacheKey($request);

        // Intentar obtener del cache
        $cached = $this->getFromCache($cacheKey);
        if ($cached !== null) {
            // Establecer headers de cache
            $this->setCacheHeaders($cached['ttl'], true);

            // Retornar respuesta cacheada
            return $cached['content'];
        }

        // Si no está en cache, ejecutar request
        ob_start();
        $response = $next($request);
        $output = ob_get_clean();

        // Cachear response si es exitoso
        $statusCode = http_response_code();
        if ($statusCode >= 200 && $statusCode < 300) {
            $ttl = $this->getTtlForUri($uri);
            $this->saveToCache($cacheKey, $output, $ttl);

            // Establecer headers de cache
            $this->setCacheHeaders($ttl, false);
        }

        return $output;
    }

    /**
     * Genera key de cache única para el request
     */
    private function generateCacheKey($request) {
        $parts = [
            $_SERVER['REQUEST_URI'] ?? '/',
            $_SERVER['QUERY_STRING'] ?? '',
            $_SESSION['id_empresa'] ?? 'guest',
            $_SESSION['id_usuario'] ?? 'guest',
            $_SESSION['idioma'] ?? 'es',
        ];

        // Agregar Accept header para variaciones de contenido
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $parts[] = $_SERVER['HTTP_ACCEPT'];
        }

        return 'http_cache:' . md5(implode('|', $parts));
    }

    /**
     * Verifica si un URI debe cachearse
     */
    private function shouldCache($uri) {
        // No cachear si el usuario está autenticado (opcional)
        if ($this->config['skip_authenticated'] && isset($_SESSION['id_usuario'])) {
            return false;
        }

        // Verificar rutas excluidas
        foreach ($this->config['exclude_paths'] as $pattern) {
            if (preg_match('#^' . $pattern . '$#', $uri)) {
                return false;
            }
        }

        // Verificar rutas incluidas
        if (!empty($this->config['include_paths'])) {
            $matched = false;
            foreach ($this->config['include_paths'] as $pattern) {
                if (preg_match('#^' . $pattern . '$#', $uri)) {
                    $matched = true;
                    break;
                }
            }
            return $matched;
        }

        return true;
    }

    /**
     * Obtiene TTL para un URI específico
     */
    private function getTtlForUri($uri) {
        // Verificar configuración personalizada por ruta
        foreach ($this->config['ttl_by_path'] as $pattern => $ttl) {
            if (preg_match('#^' . $pattern . '$#', $uri)) {
                return $ttl;
            }
        }

        return $this->config['default_ttl'];
    }

    /**
     * Obtiene contenido del cache
     */
    private function getFromCache($key) {
        if (!$this->cache) {
            return null;
        }

        try {
            $cached = $this->cache->get($key);

            if ($cached) {
                return json_decode($cached, true);
            }
        } catch (\Exception $e) {
            error_log("Cache get error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Guarda contenido en cache
     */
    private function saveToCache($key, $content, $ttl) {
        if (!$this->cache) {
            return false;
        }

        try {
            $data = [
                'content' => $content,
                'ttl' => $ttl,
                'cached_at' => time(),
            ];

            return $this->cache->setex($key, $ttl, json_encode($data));
        } catch (\Exception $e) {
            error_log("Cache set error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Invalida cache por patrón
     */
    public function invalidate($pattern = '*') {
        if (!$this->cache) {
            return false;
        }

        try {
            $keys = $this->cache->keys('http_cache:' . $pattern);

            if (!empty($keys)) {
                return $this->cache->del($keys);
            }

            return true;
        } catch (\Exception $e) {
            error_log("Cache invalidation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Establece headers HTTP de cache
     */
    private function setCacheHeaders($ttl, $fromCache = false) {
        // Cache-Control
        if ($this->config['public_cache']) {
            header("Cache-Control: public, max-age={$ttl}");
        } else {
            header("Cache-Control: private, max-age={$ttl}");
        }

        // Expires
        $expires = gmdate('D, d M Y H:i:s', time() + $ttl) . ' GMT';
        header("Expires: {$expires}");

        // ETag
        if ($this->config['use_etag']) {
            $etag = md5($_SERVER['REQUEST_URI'] . $ttl);
            header("ETag: \"{$etag}\"");

            // Verificar If-None-Match
            if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === "\"{$etag}\"") {
                http_response_code(304);
                exit;
            }
        }

        // Last-Modified
        $lastModified = gmdate('D, d M Y H:i:s', time() - ($fromCache ? $ttl : 0)) . ' GMT';
        header("Last-Modified: {$lastModified}");

        // X-Cache header (para debugging)
        if ($this->config['debug']) {
            header('X-Cache: ' . ($fromCache ? 'HIT' : 'MISS'));
        }

        // Vary
        header('Vary: Accept-Encoding, Accept');
    }

    /**
     * Inicializa conexión con cache
     */
    private function initializeCache() {
        try {
            // Usar Redis si está disponible
            if (class_exists('Redis')) {
                $redis = new \Redis();
                $redis->connect(
                    env('REDIS_HOST', '127.0.0.1'),
                    env('REDIS_PORT', 6379)
                );

                if (env('REDIS_PASSWORD')) {
                    $redis->auth(env('REDIS_PASSWORD'));
                }

                if (env('REDIS_DATABASE')) {
                    $redis->select(env('REDIS_DATABASE'));
                }

                return $redis;
            }
        } catch (\Exception $e) {
            error_log("Cache initialization error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Carga configuración de caching
     */
    private function loadConfig() {
        return [
            // Habilitar caching
            'enabled' => env('HTTP_CACHE_ENABLED', true),

            // TTL por defecto (segundos)
            'default_ttl' => env('HTTP_CACHE_TTL', 3600), // 1 hora

            // Cache público (CDN/proxy) o privado (solo navegador)
            'public_cache' => env('HTTP_CACHE_PUBLIC', false),

            // Usar ETag
            'use_etag' => env('HTTP_CACHE_ETAG', true),

            // Debug mode (agregar X-Cache header)
            'debug' => env('APP_DEBUG', false),

            // No cachear usuarios autenticados
            'skip_authenticated' => env('HTTP_CACHE_SKIP_AUTH', false),

            // Rutas a excluir del cache
            'exclude_paths' => [
                '/admin/.*',
                '/api/.*',
                '/auth/.*',
                '/dashboard/.*',
                '.*/ajax/.*',
            ],

            // Rutas a incluir (si está vacío, todas excepto excluidas)
            'include_paths' => [
                // '/public/.*',
                // '/landing/.*',
            ],

            // TTL personalizado por ruta (segundos)
            'ttl_by_path' => [
                '/landing' => 86400,           // 24 horas
                '/productos' => 3600,          // 1 hora
                '/categorias' => 7200,         // 2 horas
                '/.*\\.css' => 604800,         // 7 días
                '/.*\\.js' => 604800,          // 7 días
                '/assets/.*' => 2592000,       // 30 días
            ],
        ];
    }

    /**
     * Limpia todo el cache HTTP
     */
    public function flush() {
        return $this->invalidate('*');
    }

    /**
     * Obtiene estadísticas de cache
     */
    public function getStats() {
        if (!$this->cache) {
            return null;
        }

        try {
            $keys = $this->cache->keys('http_cache:*');
            $totalSize = 0;

            foreach ($keys as $key) {
                $totalSize += strlen($this->cache->get($key));
            }

            return [
                'total_keys' => count($keys),
                'total_size_bytes' => $totalSize,
                'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            ];
        } catch (\Exception $e) {
            error_log("Cache stats error: " . $e->getMessage());
            return null;
        }
    }
}
