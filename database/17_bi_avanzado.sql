-- =====================================================
-- CONECTA ERP - BUSINESS INTELLIGENCE
-- Archivo: 17_bi_avanzado.sql
-- Descripción: Dashboards, KPIs, métricas, analítica avanzada
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: dashboards
-- Descripción: Dashboards personalizados
-- =====================================================
CREATE TABLE dashboards (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NULL,

    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    tipo ENUM('personal', 'compartido', 'publico') DEFAULT 'personal',

    configuracion JSON NULL COMMENT 'Widgets, layout, filtros',

    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_usuario (id_usuario),
    INDEX idx_tipo (tipo),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios_acceso(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: kpis
-- Descripción: Indicadores clave de rendimiento
-- =====================================================
CREATE TABLE kpis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,

    codigo VARCHAR(50) NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT NULL,

    categoria ENUM('financiero', 'ventas', 'produccion', 'rrhh', 'calidad', 'logistica') NOT NULL,
    formula TEXT NULL,
    unidad_medida VARCHAR(20) NULL,

    meta DECIMAL(15,4) NULL,
    frecuencia ENUM('diario', 'semanal', 'mensual', 'trimestral', 'anual') DEFAULT 'mensual',

    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_codigo (codigo),
    INDEX idx_categoria (categoria),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: metricas
-- Descripción: Valores históricos de KPIs
-- =====================================================
CREATE TABLE metricas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_kpi INT UNSIGNED NOT NULL,

    fecha DATE NOT NULL,
    valor DECIMAL(15,4) NOT NULL,

    observaciones VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_kpi (id_kpi),
    INDEX idx_fecha (fecha),
    UNIQUE KEY uk_kpi_fecha (id_kpi, fecha),
    FOREIGN KEY (id_kpi) REFERENCES kpis(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
