-- =====================================================
-- CONECTA ERP - MÓDULO CONTABILIDAD
-- Archivo: 08_contabilidad.sql
-- Descripción: Plan de cuentas, asientos, libros contables, balance, IFRS
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: plan_cuentas
-- Descripción: Plan de cuentas contable (Chart of Accounts)
-- =====================================================
CREATE TABLE plan_cuentas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    codigo_cuenta VARCHAR(20) NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT NULL,

    -- Jerarquía
    id_cuenta_padre INT UNSIGNED NULL,
    nivel INT NOT NULL DEFAULT 1,
    es_titulo TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Cuenta que agrupa, no recibe movimientos',

    -- Clasificación
    tipo_cuenta ENUM('activo', 'pasivo', 'patrimonio', 'ingreso', 'gasto', 'resultado') NOT NULL,
    subtipo VARCHAR(50) NULL,
    naturaleza ENUM('deudora', 'acreedora') NOT NULL,

    -- Configuración
    acepta_movimientos TINYINT(1) NOT NULL DEFAULT 1,
    requiere_centro_costo TINYINT(1) DEFAULT 0,
    requiere_proyecto TINYINT(1) DEFAULT 0,
    requiere_auxiliar TINYINT(1) DEFAULT 0 COMMENT 'Cliente, Proveedor, etc',
    tipo_auxiliar ENUM('cliente', 'proveedor', 'empleado', 'banco', 'otro', 'ninguno') DEFAULT 'ninguno',

    -- Control de moneda
    moneda_control ENUM('local', 'extranjera', 'ambas') DEFAULT 'local',
    id_moneda_extranjera INT UNSIGNED NULL,

    -- IFRS
    cuenta_ifrs VARCHAR(20) NULL,
    grupo_ifrs VARCHAR(100) NULL,

    -- Estado
    activo TINYINT(1) NOT NULL DEFAULT 1,
    ajuste_inflacion TINYINT(1) DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_codigo (codigo_cuenta),
    INDEX idx_padre (id_cuenta_padre),
    INDEX idx_tipo (tipo_cuenta),
    INDEX idx_naturaleza (naturaleza),
    INDEX idx_activo (activo),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo_cuenta, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_cuenta_padre) REFERENCES plan_cuentas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_moneda_extranjera) REFERENCES monedas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: periodos_contables
-- Descripción: Períodos contables (mensuales)
-- =====================================================
CREATE TABLE periodos_contables (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    ano INT NOT NULL,
    mes INT NOT NULL,

    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,

    estado ENUM('abierto', 'cerrado', 'bloqueado') NOT NULL DEFAULT 'abierto',

    fecha_apertura TIMESTAMP NULL,
    fecha_cierre TIMESTAMP NULL,
    id_usuario_cierre INT UNSIGNED NULL,

    observaciones TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_ano_mes (ano, mes),
    INDEX idx_estado (estado),
    INDEX idx_fechas (fecha_inicio, fecha_fin),
    UNIQUE KEY uk_empresa_ano_mes (id_empresa, ano, mes),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario_cierre) REFERENCES usuarios_acceso(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: asientos_contables
-- Descripción: Asientos/comprobantes contables
-- =====================================================
CREATE TABLE asientos_contables (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_asiento INT NOT NULL,
    id_periodo INT UNSIGNED NOT NULL,

    fecha_asiento DATE NOT NULL,
    tipo_asiento ENUM('manual', 'automatico', 'apertura', 'cierre', 'ajuste', 'regularizacion') NOT NULL DEFAULT 'manual',

    -- Documento origen
    tipo_documento VARCHAR(50) NULL COMMENT 'factura_venta, factura_compra, pago, etc',
    numero_documento VARCHAR(50) NULL,
    id_documento_origen INT UNSIGNED NULL,

    glosa TEXT NULL,

    -- Control
    estado ENUM('borrador', 'aprobado', 'contabilizado', 'anulado') NOT NULL DEFAULT 'borrador',
    cuadrado TINYINT(1) AS (ABS(total_debe - total_haber) < 0.01) STORED COMMENT 'Debe = Haber',
    total_debe DECIMAL(15,4) DEFAULT 0,
    total_haber DECIMAL(15,4) DEFAULT 0,

    -- Auditoria
    id_usuario_crea INT UNSIGNED NULL,
    id_usuario_aprueba INT UNSIGNED NULL,
    fecha_aprobacion TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_asiento),
    INDEX idx_periodo (id_periodo),
    INDEX idx_fecha (fecha_asiento),
    INDEX idx_tipo (tipo_asiento),
    INDEX idx_estado (estado),
    INDEX idx_documento (tipo_documento, numero_documento),
    UNIQUE KEY uk_empresa_periodo_numero (id_empresa, id_periodo, numero_asiento, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_periodo) REFERENCES periodos_contables(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario_crea) REFERENCES usuarios_acceso(id) ON DELETE SET NULL,
    FOREIGN KEY (id_usuario_aprueba) REFERENCES usuarios_acceso(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: asientos_detalle
-- Descripción: Detalle de movimientos por cuenta (Debe/Haber)
-- =====================================================
CREATE TABLE asientos_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_asiento INT UNSIGNED NOT NULL,
    linea INT NOT NULL,
    id_cuenta INT UNSIGNED NOT NULL,

    -- Centro de costo y proyecto
    id_centro_costo INT UNSIGNED NULL,
    id_proyecto INT UNSIGNED NULL,

    -- Auxiliar (cliente, proveedor, etc)
    tipo_auxiliar ENUM('cliente', 'proveedor', 'empleado', 'banco', 'otro', 'ninguno') DEFAULT 'ninguno',
    id_auxiliar INT UNSIGNED NULL,

    -- Debe/Haber
    debe DECIMAL(15,4) DEFAULT 0,
    haber DECIMAL(15,4) DEFAULT 0,

    -- Moneda extranjera
    id_moneda INT UNSIGNED NULL,
    tipo_cambio DECIMAL(15,6) DEFAULT 1,
    debe_moneda_extranjera DECIMAL(15,4) DEFAULT 0,
    haber_moneda_extranjera DECIMAL(15,4) DEFAULT 0,

    glosa VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_asiento (id_asiento),
    INDEX idx_cuenta (id_cuenta),
    INDEX idx_centro_costo (id_centro_costo),
    INDEX idx_proyecto (id_proyecto),
    INDEX idx_auxiliar (tipo_auxiliar, id_auxiliar),
    FOREIGN KEY (id_asiento) REFERENCES asientos_contables(id) ON DELETE CASCADE,
    FOREIGN KEY (id_cuenta) REFERENCES plan_cuentas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_moneda) REFERENCES monedas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: saldos_cuentas
-- Descripción: Saldos acumulados por cuenta y período (para performance)
-- =====================================================
CREATE TABLE saldos_cuentas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_cuenta INT UNSIGNED NOT NULL,
    id_periodo INT UNSIGNED NOT NULL,

    saldo_inicial_debe DECIMAL(15,4) DEFAULT 0,
    saldo_inicial_haber DECIMAL(15,4) DEFAULT 0,

    movimientos_debe DECIMAL(15,4) DEFAULT 0,
    movimientos_haber DECIMAL(15,4) DEFAULT 0,

    saldo_final_debe DECIMAL(15,4) DEFAULT 0,
    saldo_final_haber DECIMAL(15,4) DEFAULT 0,

    saldo_final DECIMAL(15,4) AS (
        CASE
            WHEN (saldo_inicial_debe + movimientos_debe) > (saldo_inicial_haber + movimientos_haber)
            THEN (saldo_inicial_debe + movimientos_debe) - (saldo_inicial_haber + movimientos_haber)
            ELSE (saldo_inicial_haber + movimientos_haber) - (saldo_inicial_debe + movimientos_debe)
        END
    ) STORED,

    naturaleza_saldo ENUM('deudora', 'acreedora') NULL,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_cuenta (id_cuenta),
    INDEX idx_periodo (id_periodo),
    UNIQUE KEY uk_empresa_cuenta_periodo (id_empresa, id_cuenta, id_periodo),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_cuenta) REFERENCES plan_cuentas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_periodo) REFERENCES periodos_contables(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: centros_costo
-- Descripción: Centros de costo para imputación
-- =====================================================
CREATE TABLE centros_costo (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    codigo VARCHAR(20) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,

    id_padre INT UNSIGNED NULL,
    tipo ENUM('operativo', 'apoyo', 'productivo', 'comercial', 'administrativo') DEFAULT 'operativo',

    id_responsable INT UNSIGNED NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_codigo (codigo),
    INDEX idx_padre (id_padre),
    INDEX idx_tipo (tipo),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_padre) REFERENCES centros_costo(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: impuestos
-- Descripción: Configuración de impuestos (IVA, retenciones, etc)
-- =====================================================
CREATE TABLE impuestos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    codigo VARCHAR(20) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('iva', 'retencion', 'percepcion', 'otro') NOT NULL,

    porcentaje DECIMAL(5,2) NOT NULL,
    cuenta_contable VARCHAR(20) NULL,

    aplica_compra TINYINT(1) DEFAULT 1,
    aplica_venta TINYINT(1) DEFAULT 1,

    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_codigo (codigo),
    INDEX idx_tipo (tipo),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos iniciales de impuestos para Chile
INSERT INTO impuestos (id_empresa, codigo, nombre, tipo, porcentaje, aplica_compra, aplica_venta)
SELECT id, 'IVA', 'IVA 19%', 'iva', 19.00, 1, 1
FROM empresas
WHERE codigo_pais = 'CL'
LIMIT 1;

-- =====================================================
-- TABLA: presupuestos
-- Descripción: Presupuestos por cuenta y centro de costo
-- =====================================================
CREATE TABLE presupuestos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    ano INT NOT NULL,
    version INT NOT NULL DEFAULT 1,

    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,

    estado ENUM('borrador', 'aprobado', 'vigente', 'cerrado') NOT NULL DEFAULT 'borrador',

    fecha_aprobacion DATE NULL,
    id_usuario_aprueba INT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_ano (ano),
    INDEX idx_estado (estado),
    UNIQUE KEY uk_empresa_ano_version (id_empresa, ano, version),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: presupuestos_detalle
-- Descripción: Montos presupuestados por cuenta/centro de costo/mes
-- =====================================================
CREATE TABLE presupuestos_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_presupuesto INT UNSIGNED NOT NULL,
    id_cuenta INT UNSIGNED NOT NULL,
    id_centro_costo INT UNSIGNED NULL,

    mes INT NOT NULL COMMENT '1-12',
    monto_presupuestado DECIMAL(15,4) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_presupuesto (id_presupuesto),
    INDEX idx_cuenta (id_cuenta),
    INDEX idx_centro_costo (id_centro_costo),
    INDEX idx_mes (mes),
    UNIQUE KEY uk_presupuesto_cuenta_centro_mes (id_presupuesto, id_cuenta, id_centro_costo, mes),
    FOREIGN KEY (id_presupuesto) REFERENCES presupuestos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_cuenta) REFERENCES plan_cuentas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_centro_costo) REFERENCES centros_costo(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: conciliaciones_bancarias
-- Descripción: Conciliaciones bancarias mensuales
-- =====================================================
CREATE TABLE conciliaciones_bancarias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_cuenta_bancaria INT UNSIGNED NOT NULL,
    id_periodo INT UNSIGNED NOT NULL,

    fecha_conciliacion DATE NOT NULL,
    saldo_libro DECIMAL(15,4) DEFAULT 0,
    saldo_banco DECIMAL(15,4) DEFAULT 0,
    diferencia DECIMAL(15,4) AS (saldo_libro - saldo_banco) STORED,

    estado ENUM('borrador', 'conciliada', 'cerrada') NOT NULL DEFAULT 'borrador',
    conciliada TINYINT(1) AS (ABS(diferencia) < 0.01) STORED,

    observaciones TEXT NULL,
    id_usuario INT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_cuenta (id_cuenta_bancaria),
    INDEX idx_periodo (id_periodo),
    INDEX idx_fecha (fecha_conciliacion),
    INDEX idx_estado (estado),
    UNIQUE KEY uk_empresa_cuenta_periodo (id_empresa, id_cuenta_bancaria, id_periodo),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_periodo) REFERENCES periodos_contables(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: diferencias_cambio
-- Descripción: Diferencias de cambio por revalorización de monedas
-- =====================================================
CREATE TABLE diferencias_cambio (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_periodo INT UNSIGNED NOT NULL,
    fecha_calculo DATE NOT NULL,

    id_moneda INT UNSIGNED NOT NULL,
    tipo_cambio DECIMAL(15,6) NOT NULL,

    total_activos_moneda_ext DECIMAL(15,4) DEFAULT 0,
    total_pasivos_moneda_ext DECIMAL(15,4) DEFAULT 0,

    diferencia_realizada DECIMAL(15,4) DEFAULT 0,
    diferencia_no_realizada DECIMAL(15,4) DEFAULT 0,

    id_asiento_generado INT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_periodo (id_periodo),
    INDEX idx_moneda (id_moneda),
    INDEX idx_fecha (fecha_calculo),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_periodo) REFERENCES periodos_contables(id) ON DELETE CASCADE,
    FOREIGN KEY (id_moneda) REFERENCES monedas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_asiento_generado) REFERENCES asientos_contables(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
