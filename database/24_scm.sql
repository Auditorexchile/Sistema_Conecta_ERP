-- =====================================================
-- CONECTA ERP - SCM (SUPPLY CHAIN MANAGEMENT)
-- Archivo: 24_scm.sql
-- Descripción: Logística, transporte, rutas, entregas
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: vehiculos
-- Descripción: Flota de vehículos
-- =====================================================
CREATE TABLE vehiculos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,

    patente VARCHAR(20) NOT NULL,
    marca VARCHAR(100) NULL,
    modelo VARCHAR(100) NULL,
    ano INT NULL,

    tipo ENUM('camion', 'camioneta', 'furgon', 'auto', 'moto', 'otro') NOT NULL,

    capacidad_kg DECIMAL(10,2) NULL,
    capacidad_m3 DECIMAL(10,2) NULL,

    id_conductor_asignado INT UNSIGNED NULL,

    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_patente (patente),
    INDEX idx_tipo (tipo),
    UNIQUE KEY uk_patente (patente),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: rutas_despacho
-- Descripción: Rutas de despacho planificadas
-- =====================================================
CREATE TABLE rutas_despacho (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_ruta VARCHAR(50) NOT NULL,

    fecha_ruta DATE NOT NULL,
    id_vehiculo INT UNSIGNED NULL,
    id_conductor INT UNSIGNED NULL,

    estado ENUM('planificada', 'en_curso', 'finalizada', 'cancelada') DEFAULT 'planificada',

    distancia_total_km DECIMAL(10,2) DEFAULT 0,
    tiempo_estimado_minutos INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_ruta),
    INDEX idx_fecha (fecha_ruta),
    INDEX idx_vehiculo (id_vehiculo),
    INDEX idx_estado (estado),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_ruta),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_vehiculo) REFERENCES vehiculos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: rutas_despacho_paradas
-- Descripción: Paradas en cada ruta
-- =====================================================
CREATE TABLE rutas_despacho_paradas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_ruta INT UNSIGNED NOT NULL,
    orden INT NOT NULL,

    id_pedido INT UNSIGNED NULL,
    id_cliente INT UNSIGNED NOT NULL,

    direccion TEXT NOT NULL,
    latitud DECIMAL(10,8) NULL,
    longitud DECIMAL(11,8) NULL,

    hora_estimada TIME NULL,
    hora_llegada TIMESTAMP NULL,

    estado ENUM('pendiente', 'en_curso', 'entregado', 'no_entregado') DEFAULT 'pendiente',
    observaciones TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_ruta (id_ruta),
    INDEX idx_pedido (id_pedido),
    INDEX idx_cliente (id_cliente),
    INDEX idx_estado (estado),
    FOREIGN KEY (id_ruta) REFERENCES rutas_despacho(id) ON DELETE CASCADE,
    FOREIGN KEY (id_cliente) REFERENCES entidades(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
