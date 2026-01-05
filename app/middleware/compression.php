<?php
namespace App\Middleware;

/**
 * Conecta ERP - Middleware de Compresión
 * Comprime responses con gzip para reducir tamaño de transferencia
 */

class CompressionMiddleware {
    private $config;

    public function __construct() {
        $this->config = $this->loadConfig();
    }

    /**
     * Procesa compresión del response
     */
    public function handle($request, $next) {
        // Verificar si la compresión está habilitada
        if (!$this->config['enabled']) {
            return $next($request);
        }

        // Verificar si el cliente acepta compresión
        if (!$this->clientAcceptsCompression()) {
            return $next($request);
        }

        // Verificar si el tipo de contenido es comprimible
        $contentType = $this->getContentType();
        if (!$this->isCompressible($contentType)) {
            return $next($request);
        }

        // Iniciar buffer de salida con compresión
        ob_start([$this, 'compressOutput']);

        // Ejecutar siguiente middleware/controlador
        $response = $next($request);

        // Obtener y limpiar buffer comprimido
        $compressedOutput = ob_get_clean();

        // Establecer headers de compresión
        $this->setCompressionHeaders();

        return $compressedOutput;
    }

    /**
     * Comprime el output
     */
    public function compressOutput($buffer) {
        // No comprimir si el buffer está vacío
        if (empty($buffer)) {
            return $buffer;
        }

        // No comprimir si es menor al tamaño mínimo
        if (strlen($buffer) < $this->config['min_size']) {
            return $buffer;
        }

        // Determinar método de compresión
        $encoding = $this->getAcceptedEncoding();

        switch ($encoding) {
            case 'gzip':
                return gzencode($buffer, $this->config['compression_level']);

            case 'deflate':
                return gzdeflate($buffer, $this->config['compression_level']);

            case 'br': // Brotli (si está disponible)
                if (function_exists('brotli_compress')) {
                    return brotli_compress($buffer, $this->config['compression_level']);
                }
                // Fallback a gzip
                return gzencode($buffer, $this->config['compression_level']);

            default:
                return $buffer;
        }
    }

    /**
     * Verifica si el cliente acepta compresión
     */
    private function clientAcceptsCompression() {
        return isset($_SERVER['HTTP_ACCEPT_ENCODING']);
    }

    /**
     * Obtiene el encoding aceptado por el cliente
     */
    private function getAcceptedEncoding() {
        if (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            return null;
        }

        $acceptEncoding = strtolower($_SERVER['HTTP_ACCEPT_ENCODING']);

        // Preferencia de codificación
        $encodings = $this->config['preferred_encodings'];

        foreach ($encodings as $encoding) {
            if (strpos($acceptEncoding, $encoding) !== false) {
                return $encoding;
            }
        }

        return null;
    }

    /**
     * Obtiene el Content-Type del response
     */
    private function getContentType() {
        $headers = headers_list();

        foreach ($headers as $header) {
            if (stripos($header, 'Content-Type:') === 0) {
                return trim(substr($header, 13));
            }
        }

        return 'text/html'; // Default
    }

    /**
     * Verifica si el tipo de contenido es comprimible
     */
    private function isCompressible($contentType) {
        // Extraer el tipo base (sin charset, etc.)
        if (strpos($contentType, ';') !== false) {
            $contentType = trim(explode(';', $contentType)[0]);
        }

        $compressibleTypes = $this->config['compressible_types'];

        // Verificar tipo exacto
        if (in_array($contentType, $compressibleTypes)) {
            return true;
        }

        // Verificar patrones con wildcard
        foreach ($compressibleTypes as $type) {
            if (strpos($type, '*') !== false) {
                $pattern = str_replace('*', '.*', $type);
                if (preg_match('#^' . $pattern . '$#', $contentType)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Establece headers de compresión
     */
    private function setCompressionHeaders() {
        $encoding = $this->getAcceptedEncoding();

        if ($encoding) {
            header("Content-Encoding: {$encoding}");
            header('Vary: Accept-Encoding');

            // Remover Content-Length ya que el tamaño cambia
            header_remove('Content-Length');
        }
    }

    /**
     * Carga configuración de compresión
     */
    private function loadConfig() {
        return [
            // Habilitar compresión
            'enabled' => env('COMPRESSION_ENABLED', true),

            // Nivel de compresión (1-9, 9 = máxima compresión, más CPU)
            'compression_level' => env('COMPRESSION_LEVEL', 6),

            // Tamaño mínimo para comprimir (bytes)
            'min_size' => env('COMPRESSION_MIN_SIZE', 1024), // 1KB

            // Encodings preferidos (en orden de preferencia)
            'preferred_encodings' => [
                'br',      // Brotli (mejor compresión)
                'gzip',    // Gzip (más compatible)
                'deflate', // Deflate
            ],

            // Tipos de contenido comprimibles
            'compressible_types' => [
                // Text
                'text/html',
                'text/css',
                'text/javascript',
                'text/plain',
                'text/xml',
                'text/csv',

                // Application
                'application/javascript',
                'application/json',
                'application/xml',
                'application/xhtml+xml',
                'application/rss+xml',
                'application/atom+xml',
                'application/ld+json',

                // Fonts (algunos)
                'application/font-woff',
                'application/font-woff2',
                'application/vnd.ms-fontobject',
                'font/ttf',
                'font/otf',

                // SVG
                'image/svg+xml',

                // Wildcard patterns
                'text/*',
                'application/*json*',
                'application/*xml*',
            ],

            // Tipos de contenido que NO se deben comprimir (ya comprimidos)
            'exclude_types' => [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'video/*',
                'audio/*',
                'application/pdf',
                'application/zip',
                'application/gzip',
                'application/x-rar',
            ],
        ];
    }

    /**
     * Calcula ratio de compresión
     */
    public function getCompressionRatio($originalSize, $compressedSize) {
        if ($originalSize == 0) {
            return 0;
        }

        return round((1 - ($compressedSize / $originalSize)) * 100, 2);
    }

    /**
     * Obtiene información de compresión
     */
    public function getCompressionInfo($buffer) {
        $originalSize = strlen($buffer);
        $compressed = $this->compressOutput($buffer);
        $compressedSize = strlen($compressed);
        $ratio = $this->getCompressionRatio($originalSize, $compressedSize);

        return [
            'original_size' => $originalSize,
            'compressed_size' => $compressedSize,
            'ratio' => $ratio,
            'encoding' => $this->getAcceptedEncoding(),
        ];
    }
}
