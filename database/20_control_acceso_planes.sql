-- =====================================================
-- CONECTA ERP - CONTROL DE ACCESO Y PLANES
-- Archivo: 20_control_acceso_planes.sql
-- Descripción: Restricciones por plan, límites de uso
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: planes_restricciones
-- Descripción: Restricciones por plan
-- =====================================================
CREATE TABLE planes_restricciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_plan INT UNSIGNED NOT NULL,

    recurso VARCHAR(100) NOT NULL COMMENT 'usuarios, facturas_mes, documentos_mes, etc',
    limite INT NOT NULL COMMENT '-1 para ilimitado',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_plan (id_plan),
    INDEX idx_recurso (recurso),
    UNIQUE KEY uk_plan_recurso (id_plan, recurso),
    FOREIGN KEY (id_plan) REFERENCES planes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: uso_recursos
-- Descripción: Uso actual de recursos por empresa
-- =====================================================
CREATE TABLE uso_recursos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    recurso VARCHAR(100) NOT NULL,

    periodo_mes INT NOT NULL,
    periodo_ano INT NOT NULL,

    cantidad_usada INT DEFAULT 0,
    limite INT DEFAULT 0,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_recurso (recurso),
    INDEX idx_periodo (periodo_ano, periodo_mes),
    UNIQUE KEY uk_empresa_recurso_periodo (id_empresa, recurso, periodo_ano, periodo_mes),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
