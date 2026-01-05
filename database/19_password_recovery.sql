-- =====================================================
-- CONECTA ERP - PASSWORD RECOVERY
-- Archivo: 19_password_recovery.sql
-- Descripción: Tokens de recuperación de contraseña
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: password_resets
-- Descripción: Tokens para recuperación de contraseña
-- =====================================================
CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token VARCHAR(100) NOT NULL,

    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NOT NULL,

    usado TINYINT(1) DEFAULT 0,
    fecha_uso TIMESTAMP NULL,
    ip_solicitud VARCHAR(50) NULL,

    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_expiracion (fecha_expiracion),
    INDEX idx_usado (usado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
