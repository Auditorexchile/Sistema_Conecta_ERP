-- =====================================================
-- CONECTA ERP - MÓDULO TESORERÍA
-- Archivo: 09_tesoreria.sql
-- Descripción: Bancos, cuentas bancarias, movimientos, flujo de caja, pagos/cobros
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: bancos
-- Descripción: Maestro de bancos
-- =====================================================
CREATE TABLE bancos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_pais VARCHAR(2) NOT NULL,
    codigo_banco VARCHAR(20) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    swift VARCHAR(20) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_pais (codigo_pais),
    INDEX idx_codigo (codigo_banco),
    UNIQUE KEY uk_pais_codigo (codigo_pais, codigo_banco)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos iniciales bancos Chile
INSERT INTO bancos (codigo_pais, codigo_banco, nombre, swift) VALUES
('CL', '001', 'Banco de Chile', 'BCHICLRM'),
('CL', '012', 'Banco del Estado', 'BECHCLRM'),
('CL', '014', 'Scotiabank', 'SCOTCLRM'),
('CL', '016', 'Banco de Crédito e Inversiones (BCI)', 'CREDCLRM'),
('CL', '028', 'Banco Bice', 'BICECLRM'),
('CL', '037', 'Santander', 'BSCHCLRM'),
('CL', '039', 'Itaú', 'ITAUC

LRM'),
('CL', '049', 'Banco Security', 'SECUCLRM'),
('CL', '051', 'Banco Falabella', 'FFAL CLRM'),
('CL', '055', 'Banco Consorcio', 'CONSCHCL');

-- =====================================================
-- TABLA: cuentas_bancarias
-- Descripción: Cuentas bancarias de la empresa
-- =====================================================
CREATE TABLE cuentas_bancarias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_banco INT UNSIGNED NOT NULL,
    id_sucursal INT UNSIGNED NULL,

    numero_cuenta VARCHAR(50) NOT NULL,
    tipo_cuenta ENUM('corriente', 'ahorro', 'vista', 'plazo', 'credito', 'otra') NOT NULL DEFAULT 'corriente',

    id_moneda INT UNSIGNED NOT NULL,
    saldo_actual DECIMAL(15,4) DEFAULT 0,
    saldo_disponible DECIMAL(15,4) DEFAULT 0,

    -- Configuración
    cuenta_contable VARCHAR(20) NULL,
    permite_emision_cheques TINYINT(1) DEFAULT 1,
    permite_transferencias TINYINT(1) DEFAULT 1,

    -- Límites
    sobregiro_permitido DECIMAL(15,4) DEFAULT 0,
    linea_credito DECIMAL(15,4) DEFAULT 0,

    -- Titular
    titular VARCHAR(200) NULL,
    email VARCHAR(150) NULL,
    telefono VARCHAR(30) NULL,

    fecha_apertura DATE NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_banco (id_banco),
    INDEX idx_numero (numero_cuenta),
    INDEX idx_moneda (id_moneda),
    INDEX idx_activo (activo),
    UNIQUE KEY uk_empresa_banco_numero (id_empresa, id_banco, numero_cuenta, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_banco) REFERENCES bancos(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_sucursal) REFERENCES sucursales(id) ON DELETE SET NULL,
    FOREIGN KEY (id_moneda) REFERENCES monedas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: movimientos_bancarios
-- Descripción: Movimientos de cuentas bancarias
-- =====================================================
CREATE TABLE movimientos_bancarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_cuenta_bancaria INT UNSIGNED NOT NULL,

    fecha_movimiento DATE NOT NULL,
    fecha_valor DATE NULL,

    tipo_movimiento ENUM('ingreso', 'egreso', 'nota_debito', 'nota_credito', 'interes', 'comision', 'ajuste') NOT NULL,
    tipo_documento ENUM('deposito', 'transferencia', 'cheque', 'pago_te', 'giro', 'comision', 'interes', 'otro') NOT NULL,

    numero_documento VARCHAR(50) NULL,
    id_documento_relacionado INT UNSIGNED NULL,

    monto DECIMAL(15,4) NOT NULL,
    signo TINYINT NOT NULL COMMENT '+1 ingreso, -1 egreso',

    saldo_anterior DECIMAL(15,4) DEFAULT 0,
    saldo_posterior DECIMAL(15,4) AS (saldo_anterior + (monto * signo)) STORED,

    glosa TEXT NULL,
    beneficiario VARCHAR(200) NULL,

    conciliado TINYINT(1) DEFAULT 0,
    fecha_conciliacion DATE NULL,

    id_asiento_contable INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_cuenta (id_cuenta_bancaria),
    INDEX idx_fecha (fecha_movimiento),
    INDEX idx_tipo (tipo_movimiento, tipo_documento),
    INDEX idx_documento (numero_documento),
    INDEX idx_conciliado (conciliado),
    FOREIGN KEY (id_cuenta_bancaria) REFERENCES cuentas_bancarias(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_asiento_contable) REFERENCES asientos_contables(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: cheques
-- Descripción: Registro de cheques emitidos y recibidos
-- =====================================================
CREATE TABLE cheques (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    tipo ENUM('emitido', 'recibido') NOT NULL,

    id_cuenta_bancaria INT UNSIGNED NULL COMMENT 'Solo para emitidos',
    numero_cheque VARCHAR(20) NOT NULL,
    serie VARCHAR(20) NULL,

    fecha_emision DATE NOT NULL,
    fecha_cobro DATE NULL,
    fecha_vencimiento DATE NULL,

    monto DECIMAL(15,4) NOT NULL,
    id_moneda INT UNSIGNED NOT NULL,

    beneficiario VARCHAR(200) NULL,
    nominativo TINYINT(1) DEFAULT 1,

    -- Cheques recibidos
    banco_emisor VARCHAR(150) NULL,
    numero_cuenta_emisor VARCHAR(50) NULL,
    rutante VARCHAR(20) NULL,

    estado ENUM('cartera', 'depositado', 'cobrado', 'endosado', 'protestado', 'anulado') NOT NULL DEFAULT 'cartera',

    glosa TEXT NULL,
    id_movimiento_bancario INT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_tipo (tipo),
    INDEX idx_cuenta (id_cuenta_bancaria),
    INDEX idx_numero (numero_cheque),
    INDEX idx_estado (estado),
    INDEX idx_fecha_emision (fecha_emision),
    INDEX idx_fecha_cobro (fecha_cobro),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_cuenta_bancaria) REFERENCES cuentas_bancarias(id) ON DELETE SET NULL,
    FOREIGN KEY (id_moneda) REFERENCES monedas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_movimiento_bancario) REFERENCES movimientos_bancarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: pagos
-- Descripción: Pagos realizados a proveedores
-- =====================================================
CREATE TABLE pagos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_pago VARCHAR(50) NOT NULL,
    id_proveedor INT UNSIGNED NOT NULL,

    fecha_pago DATE NOT NULL,

    forma_pago ENUM('efectivo', 'transferencia', 'cheque', 'tarjeta', 'nota_credito', 'otro') NOT NULL,
    id_cuenta_bancaria INT UNSIGNED NULL,

    monto_total DECIMAL(15,4) NOT NULL,
    id_moneda INT UNSIGNED NOT NULL,

    -- Cheque (si aplica)
    id_cheque INT UNSIGNED NULL,

    estado ENUM('borrador', 'emitido', 'contabilizado', 'anulado') NOT NULL DEFAULT 'borrador',

    glosa TEXT NULL,
    id_usuario INT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_pago),
    INDEX idx_proveedor (id_proveedor),
    INDEX idx_fecha (fecha_pago),
    INDEX idx_forma_pago (forma_pago),
    INDEX idx_estado (estado),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_pago, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_proveedor) REFERENCES entidades(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_cuenta_bancaria) REFERENCES cuentas_bancarias(id) ON DELETE SET NULL,
    FOREIGN KEY (id_moneda) REFERENCES monedas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_cheque) REFERENCES cheques(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: pagos_detalle
-- Descripción: Facturas pagadas en cada pago
-- =====================================================
CREATE TABLE pagos_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pago INT UNSIGNED NOT NULL,
    linea INT NOT NULL,

    tipo_documento ENUM('factura', 'nota_debito', 'anticipo', 'otro') NOT NULL,
    id_documento INT UNSIGNED NULL,
    numero_documento VARCHAR(50) NULL,

    monto_documento DECIMAL(15,4) NOT NULL,
    monto_pagado DECIMAL(15,4) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_pago (id_pago),
    INDEX idx_documento (tipo_documento, id_documento),
    FOREIGN KEY (id_pago) REFERENCES pagos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: cobranzas
-- Descripción: Cobranzas recibidas de clientes
-- =====================================================
CREATE TABLE cobranzas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_cobranza VARCHAR(50) NOT NULL,
    id_cliente INT UNSIGNED NOT NULL,

    fecha_cobranza DATE NOT NULL,

    forma_pago ENUM('efectivo', 'transferencia', 'cheque', 'tarjeta_credito', 'tarjeta_debito', 'otro') NOT NULL,
    id_cuenta_bancaria INT UNSIGNED NULL,

    monto_total DECIMAL(15,4) NOT NULL,
    id_moneda INT UNSIGNED NOT NULL,

    -- Cheque (si aplica)
    id_cheque INT UNSIGNED NULL,

    estado ENUM('borrador', 'recibido', 'depositado', 'contabilizado', 'anulado') NOT NULL DEFAULT 'borrador',

    glosa TEXT NULL,
    id_usuario INT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_cobranza),
    INDEX idx_cliente (id_cliente),
    INDEX idx_fecha (fecha_cobranza),
    INDEX idx_forma_pago (forma_pago),
    INDEX idx_estado (estado),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_cobranza, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_cliente) REFERENCES entidades(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_cuenta_bancaria) REFERENCES cuentas_bancarias(id) ON DELETE SET NULL,
    FOREIGN KEY (id_moneda) REFERENCES monedas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_cheque) REFERENCES cheques(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: cobranzas_detalle
-- Descripción: Facturas cobradas en cada cobranza
-- =====================================================
CREATE TABLE cobranzas_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_cobranza INT UNSIGNED NOT NULL,
    linea INT NOT NULL,

    tipo_documento ENUM('factura', 'boleta', 'nota_debito', 'anticipo', 'otro') NOT NULL,
    id_documento INT UNSIGNED NULL,
    numero_documento VARCHAR(50) NULL,

    monto_documento DECIMAL(15,4) NOT NULL,
    monto_cobrado DECIMAL(15,4) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_cobranza (id_cobranza),
    INDEX idx_documento (tipo_documento, id_documento),
    FOREIGN KEY (id_cobranza) REFERENCES cobranzas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: flujo_caja
-- Descripción: Flujo de caja proyectado y real
-- =====================================================
CREATE TABLE flujo_caja (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,

    tipo ENUM('real', 'proyectado') NOT NULL DEFAULT 'proyectado',
    id_centro_costo INT UNSIGNED NULL,

    -- Ingresos
    ingresos_operacionales DECIMAL(15,4) DEFAULT 0,
    ingresos_no_operacionales DECIMAL(15,4) DEFAULT 0,
    total_ingresos DECIMAL(15,4) AS (ingresos_operacionales + ingresos_no_operacionales) STORED,

    -- Egresos
    egresos_operacionales DECIMAL(15,4) DEFAULT 0,
    egresos_no_operacionales DECIMAL(15,4) DEFAULT 0,
    total_egresos DECIMAL(15,4) AS (egresos_operacionales + egresos_no_operacionales) STORED,

    -- Flujo
    flujo_neto DECIMAL(15,4) AS (total_ingresos - total_egresos) STORED,
    saldo_inicial DECIMAL(15,4) DEFAULT 0,
    saldo_final DECIMAL(15,4) AS (saldo_inicial + flujo_neto) STORED,

    observaciones TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_fecha (fecha),
    INDEX idx_tipo (tipo),
    INDEX idx_centro_costo (id_centro_costo),
    UNIQUE KEY uk_empresa_fecha_tipo_centro (id_empresa, fecha, tipo, id_centro_costo),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_centro_costo) REFERENCES centros_costo(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
