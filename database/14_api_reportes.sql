-- =====================================================
-- CONECTA ERP - API Y REPORTES
-- Archivo: 14_api_reportes.sql
-- Descripción: API tokens, webhooks, reportes personalizados
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: api_tokens
-- Descripción: Tokens de acceso API
-- =====================================================
CREATE TABLE api_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NULL,

    token VARCHAR(100) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    scopes JSON NULL COMMENT 'Permisos del token',

    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NULL,
    ultimo_uso TIMESTAMP NULL,

    activo TINYINT(1) DEFAULT 1,

    INDEX idx_empresa (id_empresa),
    INDEX idx_token (token),
    UNIQUE KEY uk_token (token),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios_acceso(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: webhooks
-- Descripción: Configuración de webhooks
-- =====================================================
CREATE TABLE webhooks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,

    nombre VARCHAR(150) NOT NULL,
    url VARCHAR(500) NOT NULL,
    evento VARCHAR(100) NOT NULL COMMENT 'factura.creada, pago.recibido, etc',

    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_evento (evento),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: reportes_personalizados
-- Descripción: Reportes custom creados por usuarios
-- =====================================================
CREATE TABLE reportes_personalizados (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_usuario_crea INT UNSIGNED NULL,

    codigo VARCHAR(50) NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT NULL,

    tipo ENUM('sql', 'builder', 'tabla_dinamica') DEFAULT 'builder',
    query_sql TEXT NULL,
    configuracion JSON NULL,

    activo TINYINT(1) DEFAULT 1,
    compartido TINYINT(1) DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_codigo (codigo),
    INDEX idx_usuario (id_usuario_crea),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
