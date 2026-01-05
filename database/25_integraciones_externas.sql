-- =====================================================
-- CONECTA ERP - INTEGRACIONES EXTERNAS
-- Archivo: 25_integraciones_externas.sql
-- Descripción: SII, Previred, Bancos, Pagos, APIs externas
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: integraciones_config
-- Descripción: Configuración de integraciones externas
-- =====================================================
CREATE TABLE integraciones_config (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,

    tipo_integracion ENUM('sii', 'previred', 'banco', 'pago', 'api_custom') NOT NULL,
    nombre VARCHAR(150) NOT NULL,

    configuracion JSON NOT NULL COMMENT 'Credenciales, endpoints, etc',

    activo TINYINT(1) DEFAULT 1,
    fecha_ultima_sincronizacion TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_tipo (tipo_integracion),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: dte_emitidos (Chile SII)
-- Descripción: DTE emitidos al SII
-- =====================================================
CREATE TABLE dte_emitidos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,

    tipo_dte INT NOT NULL COMMENT '33=Factura, 39=Boleta, 56=Nota Débito, 61=Nota Crédito',
    folio INT UNSIGNED NOT NULL,

    id_documento_origen INT UNSIGNED NOT NULL,
    tipo_documento_origen VARCHAR(50) NOT NULL,

    rut_receptor VARCHAR(20) NOT NULL,
    razon_social_receptor VARCHAR(200) NOT NULL,

    monto_neto DECIMAL(15,4) DEFAULT 0,
    monto_exento DECIMAL(15,4) DEFAULT 0,
    monto_iva DECIMAL(15,4) DEFAULT 0,
    monto_total DECIMAL(15,4) DEFAULT 0,

    fecha_emision DATE NOT NULL,

    -- DTE
    xml_dte MEDIUMTEXT NULL,
    track_id VARCHAR(50) NULL,
    estado_sii ENUM('pendiente', 'enviado', 'aceptado', 'rechazado', 'reparo') DEFAULT 'pendiente',
    glosa_estado VARCHAR(500) NULL,

    fecha_envio_sii TIMESTAMP NULL,
    fecha_respuesta_sii TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_tipo_folio (tipo_dte, folio),
    INDEX idx_rut_receptor (rut_receptor),
    INDEX idx_estado (estado_sii),
    INDEX idx_fecha_emision (fecha_emision),
    UNIQUE KEY uk_empresa_tipo_folio (id_empresa, tipo_dte, folio),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: rem_previred (Chile)
-- Descripción: Archivos REM enviados a Previred
-- =====================================================
CREATE TABLE rem_previred (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_nomina INT UNSIGNED NULL,

    periodo_mes INT NOT NULL,
    periodo_ano INT NOT NULL,

    archivo_rem MEDIUMTEXT NULL,
    cantidad_trabajadores INT DEFAULT 0,
    total_remuneraciones DECIMAL(15,4) DEFAULT 0,

    estado ENUM('generado', 'enviado', 'aceptado', 'rechazado') DEFAULT 'generado',
    fecha_envio TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_nomina (id_nomina),
    INDEX idx_periodo (periodo_ano, periodo_mes),
    INDEX idx_estado (estado),
    UNIQUE KEY uk_empresa_periodo (id_empresa, periodo_ano, periodo_mes),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_nomina) REFERENCES nominas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: transacciones_pago
-- Descripción: Transacciones de pago (Transbank, MercadoPago, etc)
-- =====================================================
CREATE TABLE transacciones_pago (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,

    pasarela ENUM('transbank', 'mercadopago', 'stripe', 'paypal', 'otro') NOT NULL,
    tipo_transaccion ENUM('pago', 'reembolso', 'anulacion') DEFAULT 'pago',

    id_transaccion_externa VARCHAR(100) NOT NULL,
    id_documento_origen INT UNSIGNED NULL,
    tipo_documento_origen VARCHAR(50) NULL,

    monto DECIMAL(15,4) NOT NULL,
    id_moneda INT UNSIGNED NOT NULL,

    estado ENUM('pendiente', 'aprobado', 'rechazado', 'anulado') DEFAULT 'pendiente',

    datos_transaccion JSON NULL COMMENT 'Respuesta completa de la pasarela',

    fecha_transaccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_pasarela (pasarela),
    INDEX idx_transaccion_externa (id_transaccion_externa),
    INDEX idx_estado (estado),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_moneda) REFERENCES monedas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
