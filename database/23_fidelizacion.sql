-- =====================================================
-- CONECTA ERP - FIDELIZACIÓN
-- Archivo: 23_fidelizacion.sql
-- Descripción: Programas de lealtad, puntos, recompensas
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: programas_lealtad
-- Descripción: Programas de fidelización
-- =====================================================
CREATE TABLE programas_lealtad (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,

    codigo VARCHAR(50) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,

    puntos_por_monto DECIMAL(10,4) DEFAULT 1 COMMENT 'Puntos por cada unidad monetaria',
    monto_por_punto DECIMAL(10,4) DEFAULT 1 COMMENT 'Valor monetario de cada punto',

    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NULL,

    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_codigo (codigo),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: puntos_cliente
-- Descripción: Saldo de puntos por cliente
-- =====================================================
CREATE TABLE puntos_cliente (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_programa INT UNSIGNED NOT NULL,
    id_cliente INT UNSIGNED NOT NULL,

    puntos_acumulados INT DEFAULT 0,
    puntos_canjeados INT DEFAULT 0,
    puntos_disponibles INT AS (puntos_acumulados - puntos_canjeados) STORED,

    ultima_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_programa (id_programa),
    INDEX idx_cliente (id_cliente),
    UNIQUE KEY uk_programa_cliente (id_programa, id_cliente),
    FOREIGN KEY (id_programa) REFERENCES programas_lealtad(id) ON DELETE CASCADE,
    FOREIGN KEY (id_cliente) REFERENCES entidades(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: movimientos_puntos
-- Descripción: Historial de acumulación y canje de puntos
-- =====================================================
CREATE TABLE movimientos_puntos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_puntos_cliente INT UNSIGNED NOT NULL,

    tipo ENUM('acumulacion', 'canje', 'expiracion', 'ajuste') NOT NULL,
    puntos INT NOT NULL,
    signo TINYINT NOT NULL COMMENT '+1 para acumulación, -1 para canje',

    tipo_documento VARCHAR(50) NULL COMMENT 'factura, canje, ajuste',
    id_documento INT UNSIGNED NULL,

    descripcion VARCHAR(500) NULL,
    fecha_movimiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_puntos_cliente (id_puntos_cliente),
    INDEX idx_tipo (tipo),
    INDEX idx_fecha (fecha_movimiento),
    FOREIGN KEY (id_puntos_cliente) REFERENCES puntos_cliente(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: recompensas
-- Descripción: Catálogo de recompensas canjeables
-- =====================================================
CREATE TABLE recompensas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_programa INT UNSIGNED NOT NULL,

    codigo VARCHAR(50) NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT NULL,

    puntos_requeridos INT NOT NULL,
    stock_disponible INT DEFAULT -1 COMMENT '-1 para ilimitado',

    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_programa (id_programa),
    INDEX idx_codigo (codigo),
    UNIQUE KEY uk_programa_codigo (id_programa, codigo),
    FOREIGN KEY (id_programa) REFERENCES programas_lealtad(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
