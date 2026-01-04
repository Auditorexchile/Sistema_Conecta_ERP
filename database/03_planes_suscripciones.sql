-- =====================================================
-- Conecta ERP - Esquema de Planes y Suscripciones
-- Archivo: 03_planes_suscripciones.sql
-- Descripción: Planes comerciales, suscripciones, trial, pagos
-- =====================================================

-- Tabla: planes
CREATE TABLE IF NOT EXISTS planes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    tipo ENUM('free', 'starter', 'profesional', 'empresa', 'corporativo', 'custom') NOT NULL,
    precio_mensual DECIMAL(10, 2) DEFAULT 0.00,
    precio_anual DECIMAL(10, 2) DEFAULT 0.00,
    moneda CHAR(3) DEFAULT 'CLP',
    max_empresas INT DEFAULT 1,
    max_sucursales INT DEFAULT 1,
    max_usuarios INT DEFAULT 1,
    usuarios_ilimitados TINYINT(1) DEFAULT 0,
    multiempresa TINYINT(1) DEFAULT 0,
    tiene_trial TINYINT(1) DEFAULT 1,
    dias_trial INT DEFAULT 14,
    orden_display INT DEFAULT 0,
    destacado TINYINT(1) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    visible TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo),
    INDEX idx_activo (activo),
    INDEX idx_visible (visible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar planes base
INSERT INTO planes (codigo, nombre, descripcion, tipo, precio_mensual, max_empresas, max_usuarios, tiene_trial, orden_display, destacado) VALUES
('STARTER', 'Starter', 'Plan inicial para emprendedores', 'starter', 0.00, 1, 1, 1, 1, 0),
('PROFESIONAL', 'Profesional', 'Para pequeñas y medianas empresas', 'profesional', 49990.00, 1, 5, 1, 2, 1),
('EMPRESA', 'Empresa', 'Para empresas en crecimiento', 'empresa', 99990.00, 1, 0, 1, 3, 0),
('CORPORATIVO', 'Corporativo', 'Solución empresarial completa', 'corporativo', 0.00, 0, 0, 0, 4, 0);

UPDATE planes SET usuarios_ilimitados = 1 WHERE codigo IN ('EMPRESA', 'CORPORATIVO');
UPDATE planes SET multiempresa = 1 WHERE codigo = 'CORPORATIVO';

-- Tabla: planes_caracteristicas
CREATE TABLE IF NOT EXISTS planes_caracteristicas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_plan INT UNSIGNED NOT NULL,
    caracteristica VARCHAR(255) NOT NULL,
    valor VARCHAR(255) NULL,
    incluido TINYINT(1) DEFAULT 1,
    destacado TINYINT(1) DEFAULT 0,
    orden INT DEFAULT 0,
    FOREIGN KEY (id_plan) REFERENCES planes(id) ON DELETE CASCADE,
    INDEX idx_plan (id_plan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: planes_limites
CREATE TABLE IF NOT EXISTS planes_limites (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_plan INT UNSIGNED NOT NULL,
    modulo VARCHAR(100) NOT NULL,
    caracteristica VARCHAR(100) NOT NULL,
    limite INT NULL COMMENT 'NULL = ilimitado',
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (id_plan) REFERENCES planes(id) ON DELETE CASCADE,
    UNIQUE KEY uk_plan_modulo_caracteristica (id_plan, modulo, caracteristica),
    INDEX idx_plan (id_plan),
    INDEX idx_modulo (modulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: suscripciones
CREATE TABLE IF NOT EXISTS suscripciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_plan INT UNSIGNED NOT NULL,
    estado ENUM('trial', 'activa', 'suspendida', 'cancelada', 'vencida') DEFAULT 'trial',
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NULL,
    fecha_trial_inicio DATETIME NULL,
    fecha_trial_fin DATETIME NULL,
    dias_trial_usados INT DEFAULT 0,
    es_trial TINYINT(1) DEFAULT 1,
    trial_vencido TINYINT(1) DEFAULT 0,
    periodo_pago ENUM('mensual', 'anual', 'personalizado') DEFAULT 'mensual',
    monto DECIMAL(10, 2) NOT NULL,
    moneda CHAR(3) NOT NULL,
    renovacion_automatica TINYINT(1) DEFAULT 1,
    fecha_proximo_pago DATE NULL,
    metodo_pago VARCHAR(50) NULL,
    notas TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_plan) REFERENCES planes(id),
    INDEX idx_empresa (id_empresa),
    INDEX idx_plan (id_plan),
    INDEX idx_estado (estado),
    INDEX idx_trial_fin (fecha_trial_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: suscripciones_historial
CREATE TABLE IF NOT EXISTS suscripciones_historial (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_suscripcion INT UNSIGNED NOT NULL,
    id_plan INT UNSIGNED NOT NULL,
    estado_anterior ENUM('trial', 'activa', 'suspendida', 'cancelada', 'vencida'),
    estado_nuevo ENUM('trial', 'activa', 'suspendida', 'cancelada', 'vencida'),
    razon_cambio TEXT NULL,
    fecha_cambio DATETIME NOT NULL,
    cambiado_por INT UNSIGNED NULL,
    FOREIGN KEY (id_suscripcion) REFERENCES suscripciones(id) ON DELETE CASCADE,
    INDEX idx_suscripcion (id_suscripcion),
    INDEX idx_fecha (fecha_cambio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: pagos
CREATE TABLE IF NOT EXISTS pagos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_suscripcion INT UNSIGNED NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    moneda CHAR(3) NOT NULL,
    metodo_pago ENUM('transbank', 'mercadopago', 'stripe', 'transferencia', 'efectivo', 'otro') NOT NULL,
    estado ENUM('pendiente', 'aprobado', 'rechazado', 'reembolsado') DEFAULT 'pendiente',
    referencia_externa VARCHAR(255) NULL,
    fecha_pago DATETIME NOT NULL,
    fecha_aprobacion DATETIME NULL,
    comprobante_path VARCHAR(255) NULL,
    datos_pago TEXT NULL COMMENT 'JSON con detalles del pago',
    notas TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id),
    FOREIGN KEY (id_suscripcion) REFERENCES suscripciones(id),
    INDEX idx_empresa (id_empresa),
    INDEX idx_suscripcion (id_suscripcion),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_pago)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: control_trial
CREATE TABLE IF NOT EXISTS control_trial (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    fecha_inicio_trial DATETIME NOT NULL,
    fecha_fin_trial DATETIME NOT NULL,
    dias_totales INT DEFAULT 14,
    dias_usados INT DEFAULT 0,
    dias_restantes INT DEFAULT 14,
    trial_activo TINYINT(1) DEFAULT 1,
    trial_vencido TINYINT(1) DEFAULT 0,
    fecha_primer_login DATETIME NULL,
    acciones_bloqueadas TINYINT(1) DEFAULT 0,
    notificaciones_enviadas INT DEFAULT 0,
    fecha_ultima_notificacion DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    UNIQUE KEY uk_empresa (id_empresa),
    INDEX idx_trial_vencido (trial_vencido),
    INDEX idx_fecha_fin (fecha_fin_trial)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: bloqueos_funcionales
CREATE TABLE IF NOT EXISTS bloqueos_funcionales (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    modulo VARCHAR(100) NOT NULL,
    funcionalidad VARCHAR(100) NOT NULL,
    bloqueado TINYINT(1) DEFAULT 1,
    razon ENUM('trial_vencido', 'plan_insuficiente', 'falta_pago', 'limite_excedido', 'suspendido') NOT NULL,
    mensaje TEXT NULL,
    fecha_bloqueo DATETIME NOT NULL,
    fecha_desbloqueo DATETIME NULL,
    bloqueado_por INT UNSIGNED NULL,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    INDEX idx_empresa (id_empresa),
    INDEX idx_modulo (modulo),
    INDEX idx_bloqueado (bloqueado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ESQUEMA 03_planes_suscripciones.sql
-- =====================================================
