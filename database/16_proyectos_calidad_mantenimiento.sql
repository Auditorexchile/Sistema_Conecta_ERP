-- =====================================================
-- CONECTA ERP - CALIDAD Y MANTENIMIENTO
-- Archivo: 16_proyectos_calidad_mantenimiento.sql
-- Descripción: Control de calidad, no conformidades, mantenimiento
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: no_conformidades
-- Descripción: Registro de no conformidades de calidad
-- =====================================================
CREATE TABLE no_conformidades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_nc VARCHAR(50) NOT NULL,

    tipo ENUM('producto', 'proceso', 'servicio', 'sistema') NOT NULL,
    origen ENUM('interna', 'cliente', 'proveedor', 'auditoria') NOT NULL,

    descripcion TEXT NOT NULL,
    fecha_deteccion DATE NOT NULL,

    id_responsable INT UNSIGNED NULL,
    gravedad ENUM('baja', 'media', 'alta', 'critica') DEFAULT 'media',
    estado ENUM('abierta', 'en_analisis', 'en_correccion', 'cerrada', 'rechazada') DEFAULT 'abierta',

    causa_raiz TEXT NULL,
    accion_correctiva TEXT NULL,
    fecha_cierre DATE NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_nc),
    INDEX idx_tipo (tipo),
    INDEX idx_estado (estado),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_nc),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: mantenimiento
-- Descripción: Órdenes de mantenimiento de equipos
-- =====================================================
CREATE TABLE mantenimiento (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_orden VARCHAR(50) NOT NULL,

    id_equipo INT UNSIGNED NULL COMMENT 'Referencia a centro_trabajo o activo_fijo',
    tipo_mantenimiento ENUM('preventivo', 'correctivo', 'predictivo') NOT NULL,

    fecha_programada DATE NOT NULL,
    fecha_realizada DATE NULL,

    descripcion TEXT NOT NULL,
    observaciones TEXT NULL,

    estado ENUM('programada', 'en_proceso', 'finalizada', 'cancelada') DEFAULT 'programada',

    costo DECIMAL(15,4) DEFAULT 0,
    id_responsable INT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_orden),
    INDEX idx_tipo (tipo_mantenimiento),
    INDEX idx_estado (estado),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_orden),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
