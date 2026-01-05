-- =====================================================
-- CONECTA ERP - CONFIGURACIÓN ADICIONAL
-- Archivo: 13_configuracion.sql
-- Descripción: Configuraciones adicionales del sistema
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: parametros_sistema
-- Descripción: Parámetros generales del sistema
-- =====================================================
CREATE TABLE parametros_sistema (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    categoria VARCHAR(50) NOT NULL COMMENT 'general, contable, ventas, compras, etc',
    clave VARCHAR(100) NOT NULL,
    valor TEXT NOT NULL,
    tipo_dato ENUM('string', 'number', 'boolean', 'json', 'date') DEFAULT 'string',
    descripcion VARCHAR(500) NULL,
    editable TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_categoria (categoria),
    INDEX idx_clave (clave),
    UNIQUE KEY uk_empresa_clave (id_empresa, clave),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: secuencias_documentos
-- Descripción: Control de numeración de documentos
-- =====================================================
CREATE TABLE secuencias_documentos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_sucursal INT UNSIGNED NULL,
    tipo_documento VARCHAR(50) NOT NULL,

    prefijo VARCHAR(10) NULL,
    siguiente_numero INT UNSIGNED NOT NULL DEFAULT 1,
    longitud_numero INT DEFAULT 6,

    ultimo_numero_usado INT UNSIGNED NULL,
    fecha_ultimo_uso TIMESTAMP NULL,

    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_tipo (tipo_documento),
    UNIQUE KEY uk_empresa_sucursal_tipo (id_empresa, id_sucursal, tipo_documento),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_sucursal) REFERENCES sucursales(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
