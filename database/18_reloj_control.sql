-- =====================================================
-- CONECTA ERP - RELOJ CONTROL
-- Archivo: 18_reloj_control.sql
-- Descripción: Control de asistencia, dispositivos biométricos, turnos
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: dispositivos_control
-- Descripción: Dispositivos de control de asistencia
-- =====================================================
CREATE TABLE dispositivos_control (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_sucursal INT UNSIGNED NULL,

    codigo VARCHAR(50) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    tipo ENUM('biometrico', 'tarjeta', 'qr', 'app_movil') NOT NULL,

    ip_address VARCHAR(50) NULL,
    modelo VARCHAR(100) NULL,
    numero_serie VARCHAR(100) NULL,

    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_codigo (codigo),
    INDEX idx_sucursal (id_sucursal),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_sucursal) REFERENCES sucursales(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: marcajes
-- Descripción: Registros de entrada/salida
-- =====================================================
CREATE TABLE marcajes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empleado INT UNSIGNED NOT NULL,
    id_dispositivo INT UNSIGNED NULL,

    fecha_hora TIMESTAMP NOT NULL,
    tipo ENUM('entrada', 'salida', 'entrada_colacion', 'salida_colacion') NOT NULL,

    metodo ENUM('biometrico', 'tarjeta', 'qr', 'manual', 'app') DEFAULT 'biometrico',

    latitud DECIMAL(10,8) NULL,
    longitud DECIMAL(11,8) NULL,

    observaciones VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empleado (id_empleado),
    INDEX idx_dispositivo (id_dispositivo),
    INDEX idx_fecha_hora (fecha_hora),
    INDEX idx_tipo (tipo),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id) ON DELETE CASCADE,
    FOREIGN KEY (id_dispositivo) REFERENCES dispositivos_control(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: turnos
-- Descripción: Turnos de trabajo
-- =====================================================
CREATE TABLE turnos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,

    codigo VARCHAR(20) NOT NULL,
    nombre VARCHAR(100) NOT NULL,

    hora_entrada TIME NOT NULL,
    hora_salida TIME NOT NULL,
    minutos_colacion INT DEFAULT 30,

    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_codigo (codigo),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
