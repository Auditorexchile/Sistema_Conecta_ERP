-- =====================================================
-- Conecta ERP - Esquema de Seguridad y Acceso
-- Archivo: 01_core_seguridad.sql
-- Descripción: Usuarios, sesiones, permisos, auditoría
-- =====================================================

-- Tabla: usuarios_acceso
-- Almacena todos los usuarios del sistema
CREATE TABLE IF NOT EXISTS usuarios_acceso (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('superadmin', 'admin_empresa', 'usuario', 'empleado', 'cliente') DEFAULT 'usuario',
    id_empresa INT UNSIGNED NULL,
    activo TINYINT(1) DEFAULT 1,
    verificado TINYINT(1) DEFAULT 0,
    bloqueado TINYINT(1) DEFAULT 0,
    intentos_login INT DEFAULT 0,
    ultimo_intento_login DATETIME NULL,
    bloqueado_hasta DATETIME NULL,
    ultimo_login DATETIME NULL,
    ultimo_cambio_password DATETIME NULL,
    requiere_cambio_password TINYINT(1) DEFAULT 0,
    doble_factor_activo TINYINT(1) DEFAULT 0,
    doble_factor_secreto VARCHAR(255) NULL,
    token_api VARCHAR(255) NULL UNIQUE,
    ip_whitelist TEXT NULL COMMENT 'JSON array de IPs permitidas',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    deleted_by INT UNSIGNED NULL,
    INDEX idx_email (email),
    INDEX idx_tipo (tipo_usuario),
    INDEX idx_empresa (id_empresa),
    INDEX idx_activo (activo),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar usuario superadmin
INSERT INTO usuarios_acceso (email, password_hash, tipo_usuario, activo, verificado) VALUES
('auditorexchile@gmail.com', '$2y$12$Sistemas40&HashPlaceholder', 'superadmin', 1, 1);

-- Tabla: sesiones_usuario
CREATE TABLE IF NOT EXISTS sesiones_usuario (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    token_sesion VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_expiracion DATETIME NOT NULL,
    fecha_ultima_actividad DATETIME NOT NULL,
    activa TINYINT(1) DEFAULT 1,
    cerrada_por ENUM('usuario', 'timeout', 'admin', 'cambio_password') NULL,
    datos_sesion TEXT NULL COMMENT 'JSON con datos adicionales',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios_acceso(id) ON DELETE CASCADE,
    INDEX idx_usuario (id_usuario),
    INDEX idx_token (token_sesion),
    INDEX idx_activa (activa),
    INDEX idx_expiracion (fecha_expiracion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: intentos_login
CREATE TABLE IF NOT EXISTS intentos_login (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    id_usuario INT UNSIGNED NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    exitoso TINYINT(1) NOT NULL,
    razon_fallo ENUM('credenciales_invalidas', 'usuario_bloqueado', 'cuenta_inactiva', 'trial_vencido', 'cuenta_suspendida', 'doble_factor_fallo') NULL,
    datos_adicionales TEXT NULL COMMENT 'JSON',
    fecha_intento DATETIME NOT NULL,
    INDEX idx_email (email),
    INDEX idx_usuario (id_usuario),
    INDEX idx_ip (ip_address),
    INDEX idx_exitoso (exitoso),
    INDEX idx_fecha (fecha_intento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: password_resets
CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    usado TINYINT(1) DEFAULT 0,
    ip_solicitud VARCHAR(45) NOT NULL,
    fecha_solicitud DATETIME NOT NULL,
    fecha_expiracion DATETIME NOT NULL,
    fecha_uso DATETIME NULL,
    ip_uso VARCHAR(45) NULL,
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_usado (usado),
    INDEX idx_expiracion (fecha_expiracion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: permisos
CREATE TABLE IF NOT EXISTS permisos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(100) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    modulo VARCHAR(100) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_modulo (modulo),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: roles
CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(100) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    nivel INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: roles_permisos
CREATE TABLE IF NOT EXISTS roles_permisos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_rol INT UNSIGNED NOT NULL,
    id_permiso INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (id_permiso) REFERENCES permisos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_rol_permiso (id_rol, id_permiso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: usuarios_roles
CREATE TABLE IF NOT EXISTS usuarios_roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    id_rol INT UNSIGNED NOT NULL,
    id_empresa INT UNSIGNED NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_asignacion DATETIME NOT NULL,
    fecha_vencimiento DATETIME NULL,
    asignado_por INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios_acceso(id) ON DELETE CASCADE,
    FOREIGN KEY (id_rol) REFERENCES roles(id) ON DELETE CASCADE,
    INDEX idx_usuario (id_usuario),
    INDEX idx_rol (id_rol),
    INDEX idx_empresa (id_empresa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: auditoria_acciones
CREATE TABLE IF NOT EXISTS auditoria_acciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NULL,
    accion VARCHAR(100) NOT NULL,
    modulo VARCHAR(100) NOT NULL,
    tabla VARCHAR(100) NULL,
    id_registro INT UNSIGNED NULL,
    descripcion TEXT NULL,
    datos_antes TEXT NULL COMMENT 'JSON',
    datos_despues TEXT NULL COMMENT 'JSON',
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    fecha_accion DATETIME NOT NULL,
    INDEX idx_usuario (id_usuario),
    INDEX idx_accion (accion),
    INDEX idx_modulo (modulo),
    INDEX idx_tabla (tabla),
    INDEX idx_fecha (fecha_accion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: auditoria_accesos
CREATE TABLE IF NOT EXISTS auditoria_accesos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NULL,
    tipo_acceso ENUM('login', 'logout', 'timeout', 'forzado') NOT NULL,
    exitoso TINYINT(1) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    datos_adicionales TEXT NULL COMMENT 'JSON',
    fecha_acceso DATETIME NOT NULL,
    INDEX idx_usuario (id_usuario),
    INDEX idx_tipo (tipo_acceso),
    INDEX idx_exitoso (exitoso),
    INDEX idx_fecha (fecha_acceso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: configuracion_seguridad
CREATE TABLE IF NOT EXISTS configuracion_seguridad (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(255) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    tipo ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    descripcion TEXT NULL,
    modificable TINYINT(1) DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT UNSIGNED NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar configuración de seguridad por defecto
INSERT INTO configuracion_seguridad (clave, valor, tipo, descripcion, modificable) VALUES
('max_login_attempts', '5', 'number', 'Intentos máximos de login antes de bloqueo', 1),
('lockout_time', '900', 'number', 'Tiempo de bloqueo en segundos (15 min)', 1),
('session_lifetime', '7200', 'number', 'Duración de sesión en segundos (2 horas)', 1),
('password_min_length', '12', 'number', 'Longitud mínima de contraseña', 1),
('password_require_uppercase', '1', 'boolean', 'Contraseña requiere mayúsculas', 1),
('password_require_lowercase', '1', 'boolean', 'Contraseña requiere minúsculas', 1),
('password_require_number', '1', 'boolean', 'Contraseña requiere números', 1),
('password_require_special', '1', 'boolean', 'Contraseña requiere caracteres especiales', 1),
('password_expiry_days', '90', 'number', 'Días antes de que expire la contraseña', 1),
('enforce_2fa_superadmin', '1', 'boolean', 'Forzar 2FA para superadmin', 0),
('trial_days', '14', 'number', 'Días de trial por defecto', 1),
('token_recovery_expiry', '900', 'number', 'Expiración token recuperación (15 min)', 1);

-- =====================================================
-- FIN DEL ESQUEMA 01_core_seguridad.sql
-- =====================================================
