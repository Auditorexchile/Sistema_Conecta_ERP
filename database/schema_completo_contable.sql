-- =====================================================
-- SISTEMA ERP CONTABLE COMPLETO - NIVEL SOFTLAND
-- Base de datos completa para módulo contable Chile
-- Cumple normativa SII, NIIF, auditoría total
-- =====================================================

-- Base de datos
CREATE DATABASE IF NOT EXISTS conectae_conectaerpbd
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE conectae_conectaerpbd;

-- =====================================================
-- 1. TABLAS DE SISTEMA Y SEGURIDAD
-- =====================================================

-- Empresas (Multiempresa)
CREATE TABLE emp_empresas (
    id_empresa INT AUTO_INCREMENT PRIMARY KEY,
    rut_empresa VARCHAR(12) NOT NULL UNIQUE,
    razon_social VARCHAR(255) NOT NULL,
    nombre_fantasia VARCHAR(255),
    giro VARCHAR(255),
    direccion VARCHAR(255),
    comuna VARCHAR(100),
    ciudad VARCHAR(100),
    region VARCHAR(100),
    telefono VARCHAR(50),
    email VARCHAR(100),
    representante_legal VARCHAR(255),
    rut_representante VARCHAR(12),
    contador VARCHAR(255),
    rut_contador VARCHAR(12),
    tipo_contribuyente ENUM('primera_categoria', 'segunda_categoria', 'renta_presunta') DEFAULT 'primera_categoria',
    regimen_tributario ENUM('general', '14a', '14b', '14d3', 'pro_pyme') DEFAULT 'general',
    activa BOOLEAN DEFAULT TRUE,
    fecha_inicio_actividades DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rut (rut_empresa),
    INDEX idx_activa (activa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuarios del sistema
CREATE TABLE sys_usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    id_perfil INT,
    activo BOOLEAN DEFAULT TRUE,
    ultimo_acceso TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Perfiles de usuario
CREATE TABLE sys_perfiles (
    id_perfil INT AUTO_INCREMENT PRIMARY KEY,
    nombre_perfil VARCHAR(100) NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permisos por módulo
CREATE TABLE sys_permisos (
    id_permiso INT AUTO_INCREMENT PRIMARY KEY,
    id_perfil INT NOT NULL,
    modulo VARCHAR(100) NOT NULL,
    puede_ver BOOLEAN DEFAULT FALSE,
    puede_crear BOOLEAN DEFAULT FALSE,
    puede_editar BOOLEAN DEFAULT FALSE,
    puede_eliminar BOOLEAN DEFAULT FALSE,
    puede_autorizar BOOLEAN DEFAULT FALSE,
    puede_cerrar BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_perfil) REFERENCES sys_perfiles(id_perfil),
    INDEX idx_perfil_modulo (id_perfil, modulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bitácora de auditoría (sin eliminación física)
CREATE TABLE sys_auditoria (
    id_auditoria BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    id_empresa INT,
    modulo VARCHAR(100) NOT NULL,
    accion VARCHAR(50) NOT NULL,
    tabla VARCHAR(100),
    id_registro INT,
    descripcion TEXT,
    datos_antes JSON,
    datos_despues JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES sys_usuarios(id_usuario),
    FOREIGN KEY (id_empresa) REFERENCES emp_empresas(id_empresa),
    INDEX idx_usuario (id_usuario),
    INDEX idx_empresa (id_empresa),
    INDEX idx_modulo (modulo),
    INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. PLAN DE CUENTAS (6 NIVELES JERÁRQUICOS)
-- =====================================================

CREATE TABLE con_plan_cuentas (
    id_cuenta INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    codigo_cuenta VARCHAR(50) NOT NULL,
    nombre_cuenta VARCHAR(255) NOT NULL,

    -- Jerarquía (6 niveles)
    nivel INT NOT NULL CHECK (nivel BETWEEN 1 AND 6),
    id_cuenta_padre INT NULL,
    codigo_nivel1 VARCHAR(10),
    codigo_nivel2 VARCHAR(10),
    codigo_nivel3 VARCHAR(10),
    codigo_nivel4 VARCHAR(10),
    codigo_nivel5 VARCHAR(10),
    codigo_nivel6 VARCHAR(10),

    -- Clasificación
    clasificacion ENUM('Activo', 'Pasivo', 'Patrimonio', 'Ingresos', 'Gastos', 'Orden') NOT NULL,
    naturaleza ENUM('Deudora', 'Acreedora') NOT NULL,

    -- Configuración de cuenta
    imputable BOOLEAN DEFAULT FALSE,
    requiere_centro_costo BOOLEAN DEFAULT FALSE,
    requiere_auxiliar BOOLEAN DEFAULT FALSE,
    tipo_auxiliar ENUM('ninguno', 'cliente', 'proveedor', 'trabajador', 'otro') DEFAULT 'ninguno',
    requiere_documento BOOLEAN DEFAULT FALSE,

    -- Configuración tributaria
    afecta_iva ENUM('afecta', 'exenta', 'no_afecta') DEFAULT 'no_afecta',
    cuenta_honorarios BOOLEAN DEFAULT FALSE,
    cuenta_activo_fijo BOOLEAN DEFAULT FALSE,
    cuenta_gasto_rechazado BOOLEAN DEFAULT FALSE,

    -- Configuración adicional
    multimoneda BOOLEAN DEFAULT FALSE,
    moneda_defecto VARCHAR(3) DEFAULT 'CLP',
    ajustable_inflacion BOOLEAN DEFAULT FALSE,

    -- Control y vigencia
    bloqueada BOOLEAN DEFAULT FALSE,
    fecha_bloqueo DATE NULL,
    motivo_bloqueo TEXT NULL,
    vigente BOOLEAN DEFAULT TRUE,
    fecha_desde DATE NOT NULL,
    fecha_hasta DATE NULL,

    -- Saldos (cache calculado)
    saldo_debe DECIMAL(18,2) DEFAULT 0,
    saldo_haber DECIMAL(18,2) DEFAULT 0,
    saldo_final DECIMAL(18,2) DEFAULT 0,
    ultimo_movimiento DATE NULL,

    -- Auditoría
    eliminado BOOLEAN DEFAULT FALSE,
    fecha_eliminacion TIMESTAMP NULL,
    usuario_eliminacion INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,

    FOREIGN KEY (id_empresa) REFERENCES emp_empresas(id_empresa),
    FOREIGN KEY (id_cuenta_padre) REFERENCES con_plan_cuentas(id_cuenta),
    FOREIGN KEY (created_by) REFERENCES sys_usuarios(id_usuario),
    FOREIGN KEY (updated_by) REFERENCES sys_usuarios(id_usuario),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo_cuenta),
    INDEX idx_empresa (id_empresa),
    INDEX idx_codigo (codigo_cuenta),
    INDEX idx_padre (id_cuenta_padre),
    INDEX idx_nivel (nivel),
    INDEX idx_imputable (imputable),
    INDEX idx_clasificacion (clasificacion),
    INDEX idx_vigente (vigente),
    INDEX idx_eliminado (eliminado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. CENTROS DE COSTO
-- =====================================================

CREATE TABLE con_centros_costo (
    id_centro_costo INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    codigo_centro VARCHAR(20) NOT NULL,
    nombre_centro VARCHAR(255) NOT NULL,
    descripcion TEXT,
    id_centro_padre INT NULL,
    nivel INT NOT NULL DEFAULT 1,
    activo BOOLEAN DEFAULT TRUE,
    eliminado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,
    FOREIGN KEY (id_empresa) REFERENCES emp_empresas(id_empresa),
    FOREIGN KEY (id_centro_padre) REFERENCES con_centros_costo(id_centro_costo),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo_centro),
    INDEX idx_empresa (id_empresa),
    INDEX idx_activo (activo),
    INDEX idx_eliminado (eliminado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. AUXILIARES (Clientes, Proveedores, Trabajadores)
-- =====================================================

CREATE TABLE con_auxiliares (
    id_auxiliar INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    tipo_auxiliar ENUM('cliente', 'proveedor', 'trabajador', 'otro') NOT NULL,
    rut_auxiliar VARCHAR(12) NOT NULL,
    razon_social VARCHAR(255) NOT NULL,
    nombre_fantasia VARCHAR(255),
    giro VARCHAR(255),
    direccion VARCHAR(255),
    comuna VARCHAR(100),
    ciudad VARCHAR(100),
    telefono VARCHAR(50),
    email VARCHAR(100),
    contacto VARCHAR(255),
    activo BOOLEAN DEFAULT TRUE,
    eliminado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empresa) REFERENCES emp_empresas(id_empresa),
    UNIQUE KEY uk_empresa_tipo_rut (id_empresa, tipo_auxiliar, rut_auxiliar),
    INDEX idx_empresa (id_empresa),
    INDEX idx_tipo (tipo_auxiliar),
    INDEX idx_rut (rut_auxiliar),
    INDEX idx_activo (activo),
    INDEX idx_eliminado (eliminado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. PERIODOS CONTABLES
-- =====================================================

CREATE TABLE con_periodos (
    id_periodo INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    anio INT NOT NULL,
    mes INT NOT NULL CHECK (mes BETWEEN 1 AND 12),
    nombre_periodo VARCHAR(50),
    fecha_desde DATE NOT NULL,
    fecha_hasta DATE NOT NULL,
    estado ENUM('abierto', 'cerrado_mes', 'cerrado_anual') DEFAULT 'abierto',
    fecha_cierre TIMESTAMP NULL,
    usuario_cierre INT NULL,
    observaciones TEXT,
    FOREIGN KEY (id_empresa) REFERENCES emp_empresas(id_empresa),
    FOREIGN KEY (usuario_cierre) REFERENCES sys_usuarios(id_usuario),
    UNIQUE KEY uk_empresa_periodo (id_empresa, anio, mes),
    INDEX idx_empresa (id_empresa),
    INDEX idx_anio_mes (anio, mes),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. TIPOS DE DOCUMENTOS
-- =====================================================

CREATE TABLE con_tipos_documentos (
    id_tipo_documento INT AUTO_INCREMENT PRIMARY KEY,
    codigo_tipo VARCHAR(10) NOT NULL UNIQUE,
    nombre_tipo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    codigo_sii INT NULL,
    categoria ENUM('compra', 'venta', 'honorario', 'bancario', 'interno', 'otro') NOT NULL,
    afecto_iva BOOLEAN DEFAULT TRUE,
    requiere_folio BOOLEAN DEFAULT TRUE,
    formato_folio VARCHAR(50),
    activo BOOLEAN DEFAULT TRUE,
    INDEX idx_categoria (categoria),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar tipos de documentos básicos
INSERT INTO con_tipos_documentos (codigo_tipo, nombre_tipo, codigo_sii, categoria, afecto_iva, requiere_folio) VALUES
('33', 'Factura Afecta', 33, 'compra', TRUE, TRUE),
('34', 'Factura Exenta', 34, 'compra', FALSE, TRUE),
('56', 'Nota Débito', 56, 'compra', TRUE, TRUE),
('61', 'Nota Crédito', 61, 'compra', TRUE, TRUE),
('39', 'Boleta', 39, 'venta', TRUE, TRUE),
('41', 'Boleta Exenta', 41, 'venta', FALSE, TRUE),
('BHE', 'Boleta Honorarios Electrónica', 0, 'honorario', FALSE, TRUE),
('LIQ', 'Liquidación', 0, 'interno', FALSE, FALSE),
('CHE', 'Cheque', 0, 'bancario', FALSE, TRUE),
('TRF', 'Transferencia', 0, 'bancario', FALSE, TRUE);

-- =====================================================
-- 7. PLANIMETRÍA CONTABLE (Configuración de asientos automáticos)
-- =====================================================

CREATE TABLE con_planimetria (
    id_planimetria INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    id_tipo_documento INT NOT NULL,
    nombre_configuracion VARCHAR(255) NOT NULL,
    descripcion TEXT,

    -- Cuentas por defecto
    id_cuenta_cargo INT,
    id_cuenta_abono INT,
    id_cuenta_iva INT,
    id_cuenta_retencion INT,
    id_cuenta_redondeo INT,

    -- Configuración
    requiere_centro_costo BOOLEAN DEFAULT FALSE,
    id_centro_costo_defecto INT NULL,
    genera_asiento_automatico BOOLEAN DEFAULT TRUE,
    tipo_comprobante_defecto ENUM('Ingreso', 'Egreso', 'Traspaso', 'Ajuste') DEFAULT 'Traspaso',

    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_empresa) REFERENCES emp_empresas(id_empresa),
    FOREIGN KEY (id_tipo_documento) REFERENCES con_tipos_documentos(id_tipo_documento),
    FOREIGN KEY (id_cuenta_cargo) REFERENCES con_plan_cuentas(id_cuenta),
    FOREIGN KEY (id_cuenta_abono) REFERENCES con_plan_cuentas(id_cuenta),
    FOREIGN KEY (id_cuenta_iva) REFERENCES con_plan_cuentas(id_cuenta),
    FOREIGN KEY (id_cuenta_retencion) REFERENCES con_plan_cuentas(id_cuenta),
    FOREIGN KEY (id_cuenta_redondeo) REFERENCES con_plan_cuentas(id_cuenta),
    FOREIGN KEY (id_centro_costo_defecto) REFERENCES con_centros_costo(id_centro_costo),
    INDEX idx_empresa (id_empresa),
    INDEX idx_tipo_doc (id_tipo_documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. COMPROBANTES CONTABLES (Cabecera)
-- =====================================================

CREATE TABLE con_comprobantes (
    id_comprobante BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    tipo_comprobante ENUM('Ingreso', 'Egreso', 'Traspaso', 'Ajuste', 'Apertura', 'Cierre', 'Provisión', 'Centralización', 'Reverso') NOT NULL,
    numero_comprobante VARCHAR(50) NOT NULL,
    id_periodo INT NOT NULL,
    fecha_contable DATE NOT NULL,
    fecha_documento DATE,

    glosa_general TEXT,

    -- Control de estado
    estado ENUM('Borrador', 'Contabilizado', 'Anulado', 'Reversado') DEFAULT 'Borrador',
    fecha_contabilizacion TIMESTAMP NULL,
    usuario_contabilizacion INT NULL,
    fecha_anulacion TIMESTAMP NULL,
    usuario_anulacion INT NULL,
    motivo_anulacion TEXT,
    id_comprobante_reverso BIGINT NULL,

    -- Totales (calculados)
    total_debe DECIMAL(18,2) DEFAULT 0,
    total_haber DECIMAL(18,2) DEFAULT 0,
    diferencia DECIMAL(18,2) DEFAULT 0,

    -- Origen del comprobante
    origen_modulo VARCHAR(50) NULL,
    id_registro_origen INT NULL,

    -- Auditoría
    eliminado BOOLEAN DEFAULT FALSE,
    fecha_eliminacion TIMESTAMP NULL,
    usuario_eliminacion INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,

    FOREIGN KEY (id_empresa) REFERENCES emp_empresas(id_empresa),
    FOREIGN KEY (id_periodo) REFERENCES con_periodos(id_periodo),
    FOREIGN KEY (created_by) REFERENCES sys_usuarios(id_usuario),
    FOREIGN KEY (updated_by) REFERENCES sys_usuarios(id_usuario),
    FOREIGN KEY (usuario_contabilizacion) REFERENCES sys_usuarios(id_usuario),
    FOREIGN KEY (usuario_anulacion) REFERENCES sys_usuarios(id_usuario),
    FOREIGN KEY (id_comprobante_reverso) REFERENCES con_comprobantes(id_comprobante),

    UNIQUE KEY uk_empresa_tipo_numero (id_empresa, tipo_comprobante, numero_comprobante),
    INDEX idx_empresa (id_empresa),
    INDEX idx_periodo (id_periodo),
    INDEX idx_fecha (fecha_contable),
    INDEX idx_tipo (tipo_comprobante),
    INDEX idx_estado (estado),
    INDEX idx_eliminado (eliminado),
    INDEX idx_origen (origen_modulo, id_registro_origen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. COMPROBANTES CONTABLES (Detalle)
-- =====================================================

CREATE TABLE con_comprobantes_detalle (
    id_detalle BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_comprobante BIGINT NOT NULL,
    numero_linea INT NOT NULL,

    -- Cuenta contable
    id_cuenta INT NOT NULL,

    -- Montos
    debe DECIMAL(18,2) DEFAULT 0,
    haber DECIMAL(18,2) DEFAULT 0,

    -- Centro de costo
    id_centro_costo INT NULL,

    -- Auxiliar
    id_auxiliar INT NULL,

    -- Documento
    id_tipo_documento INT NULL,
    numero_documento VARCHAR(50),
    fecha_documento DATE,

    -- Glosa
    glosa_detalle VARCHAR(500),

    -- Moneda
    moneda VARCHAR(3) DEFAULT 'CLP',
    tipo_cambio DECIMAL(10,4) DEFAULT 1,
    monto_moneda_extranjera DECIMAL(18,2),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_comprobante) REFERENCES con_comprobantes(id_comprobante),
    FOREIGN KEY (id_cuenta) REFERENCES con_plan_cuentas(id_cuenta),
    FOREIGN KEY (id_centro_costo) REFERENCES con_centros_costo(id_centro_costo),
    FOREIGN KEY (id_auxiliar) REFERENCES con_auxiliares(id_auxiliar),
    FOREIGN KEY (id_tipo_documento) REFERENCES con_tipos_documentos(id_tipo_documento),
    INDEX idx_comprobante (id_comprobante),
    INDEX idx_cuenta (id_cuenta),
    INDEX idx_auxiliar (id_auxiliar),
    INDEX idx_centro_costo (id_centro_costo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. CONFIGURACIÓN IVA
-- =====================================================

CREATE TABLE con_iva_configuracion (
    id_config_iva INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    anio INT NOT NULL,
    mes INT NOT NULL,

    -- Tasas IVA
    tasa_iva_general DECIMAL(5,2) DEFAULT 19.00,
    tasa_retencion_honorarios DECIMAL(5,2) DEFAULT 12.25,
    tasa_retencion_dieta DECIMAL(5,2) DEFAULT 10.00,

    -- Configuración proporcionalidad
    aplica_proporcionalidad BOOLEAN DEFAULT FALSE,
    porcentaje_proporcional DECIMAL(5,2),

    -- Cuentas contables IVA
    id_cuenta_debito_fiscal INT,
    id_cuenta_credito_fiscal INT,
    id_cuenta_iva_retenido INT,
    id_cuenta_ppm INT,
    id_cuenta_remanente INT,

    FOREIGN KEY (id_empresa) REFERENCES emp_empresas(id_empresa),
    FOREIGN KEY (id_cuenta_debito_fiscal) REFERENCES con_plan_cuentas(id_cuenta),
    FOREIGN KEY (id_cuenta_credito_fiscal) REFERENCES con_plan_cuentas(id_cuenta),
    FOREIGN KEY (id_cuenta_iva_retenido) REFERENCES con_plan_cuentas(id_cuenta),
    FOREIGN KEY (id_cuenta_ppm) REFERENCES con_plan_cuentas(id_cuenta),
    FOREIGN KEY (id_cuenta_remanente) REFERENCES con_plan_cuentas(id_cuenta),
    UNIQUE KEY uk_empresa_periodo (id_empresa, anio, mes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. LIBRO DE COMPRAS
-- =====================================================

CREATE TABLE con_libro_compras (
    id_compra BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    id_periodo INT NOT NULL,
    id_comprobante BIGINT NULL,

    -- Documento
    id_tipo_documento INT NOT NULL,
    numero_documento VARCHAR(50) NOT NULL,
    fecha_documento DATE NOT NULL,

    -- Proveedor
    id_auxiliar INT NOT NULL,
    rut_proveedor VARCHAR(12) NOT NULL,
    razon_social_proveedor VARCHAR(255) NOT NULL,

    -- Montos
    monto_neto DECIMAL(18,2) DEFAULT 0,
    monto_exento DECIMAL(18,2) DEFAULT 0,
    monto_iva DECIMAL(18,2) DEFAULT 0,
    monto_iva_no_recuperable DECIMAL(18,2) DEFAULT 0,
    monto_total DECIMAL(18,2) DEFAULT 0,

    -- Clasificación IVA
    tipo_credito ENUM('credito_total', 'credito_parcial', 'uso_comun', 'sin_credito') DEFAULT 'credito_total',
    porcentaje_credito DECIMAL(5,2) DEFAULT 100.00,

    -- Control
    estado ENUM('pendiente', 'contabilizado', 'anulado') DEFAULT 'pendiente',
    glosa TEXT,

    eliminado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_empresa) REFERENCES emp_empresas(id_empresa),
    FOREIGN KEY (id_periodo) REFERENCES con_periodos(id_periodo),
    FOREIGN KEY (id_comprobante) REFERENCES con_comprobantes(id_comprobante),
    FOREIGN KEY (id_tipo_documento) REFERENCES con_tipos_documentos(id_tipo_documento),
    FOREIGN KEY (id_auxiliar) REFERENCES con_auxiliares(id_auxiliar),
    INDEX idx_empresa (id_empresa),
    INDEX idx_periodo (id_periodo),
    INDEX idx_fecha (fecha_documento),
    INDEX idx_proveedor (id_auxiliar),
    INDEX idx_eliminado (eliminado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 12. LIBRO DE VENTAS
-- =====================================================

CREATE TABLE con_libro_ventas (
    id_venta BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    id_periodo INT NOT NULL,
    id_comprobante BIGINT NULL,

    -- Documento
    id_tipo_documento INT NOT NULL,
    numero_documento VARCHAR(50) NOT NULL,
    fecha_documento DATE NOT NULL,

    -- Cliente
    id_auxiliar INT NULL,
    rut_cliente VARCHAR(12),
    razon_social_cliente VARCHAR(255),

    -- Montos
    monto_neto DECIMAL(18,2) DEFAULT 0,
    monto_exento DECIMAL(18,2) DEFAULT 0,
    monto_iva DECIMAL(18,2) DEFAULT 0,
    monto_total DECIMAL(18,2) DEFAULT 0,

    -- Control
    estado ENUM('pendiente', 'contabilizado', 'anulado') DEFAULT 'pendiente',
    glosa TEXT,

    eliminado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_empresa) REFERENCES emp_empresas(id_empresa),
    FOREIGN KEY (id_periodo) REFERENCES con_periodos(id_periodo),
    FOREIGN KEY (id_comprobante) REFERENCES con_comprobantes(id_comprobante),
    FOREIGN KEY (id_tipo_documento) REFERENCES con_tipos_documentos(id_tipo_documento),
    FOREIGN KEY (id_auxiliar) REFERENCES con_auxiliares(id_auxiliar),
    INDEX idx_empresa (id_empresa),
    INDEX idx_periodo (id_periodo),
    INDEX idx_fecha (fecha_documento),
    INDEX idx_cliente (id_auxiliar),
    INDEX idx_eliminado (eliminado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 13. LIBRO DE HONORARIOS
-- =====================================================

CREATE TABLE con_libro_honorarios (
    id_honorario BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    id_periodo INT NOT NULL,
    id_comprobante BIGINT NULL,

    -- Documento
    folio_boleta VARCHAR(50) NOT NULL,
    fecha_boleta DATE NOT NULL,

    -- Prestador
    id_auxiliar INT NOT NULL,
    rut_prestador VARCHAR(12) NOT NULL,
    nombre_prestador VARCHAR(255) NOT NULL,

    -- Montos
    monto_bruto DECIMAL(18,2) DEFAULT 0,
    tasa_retencion DECIMAL(5,2) DEFAULT 12.25,
    monto_retencion DECIMAL(18,2) DEFAULT 0,
    monto_liquido DECIMAL(18,2) DEFAULT 0,

    -- Tipo de honorario
    tipo_honorario ENUM('profesional', 'ocupacion_lucrativa', 'director', 'otro') DEFAULT 'profesional',
    codigo_actividad VARCHAR(10),

    -- Control
    estado ENUM('pendiente', 'contabilizado', 'anulado') DEFAULT 'pendiente',
    glosa TEXT,

    eliminado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_empresa) REFERENCES emp_empresas(id_empresa),
    FOREIGN KEY (id_periodo) REFERENCES con_periodos(id_periodo),
    FOREIGN KEY (id_comprobante) REFERENCES con_comprobantes(id_comprobante),
    FOREIGN KEY (id_auxiliar) REFERENCES con_auxiliares(id_auxiliar),
    INDEX idx_empresa (id_empresa),
    INDEX idx_periodo (id_periodo),
    INDEX idx_fecha (fecha_boleta),
    INDEX idx_prestador (id_auxiliar),
    INDEX idx_eliminado (eliminado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 14. FORMULARIO 29 (F29)
-- =====================================================

CREATE TABLE con_f29 (
    id_f29 INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    id_periodo INT NOT NULL,
    anio INT NOT NULL,
    mes INT NOT NULL,

    -- Débito Fiscal
    codigo_511_ventas_netas DECIMAL(18,2) DEFAULT 0,
    codigo_512_debito_fiscal DECIMAL(18,2) DEFAULT 0,

    -- Crédito Fiscal
    codigo_520_compras_netas DECIMAL(18,2) DEFAULT 0,
    codigo_521_credito_fiscal DECIMAL(18,2) DEFAULT 0,
    codigo_522_credito_uso_comun DECIMAL(18,2) DEFAULT 0,
    codigo_524_credito_activo_fijo DECIMAL(18,2) DEFAULT 0,

    -- Remanentes
    codigo_528_remanente_mes_anterior DECIMAL(18,2) DEFAULT 0,
    codigo_538_total_credito DECIMAL(18,2) DEFAULT 0,

    -- Determinación IVA
    codigo_562_iva_determinado DECIMAL(18,2) DEFAULT 0,
    codigo_563_creditos_especiales DECIMAL(18,2) DEFAULT 0,
    codigo_564_iva_retenido_terceros DECIMAL(18,2) DEFAULT 0,
    codigo_566_remanente_credito DECIMAL(18,2) DEFAULT 0,

    -- Honorarios
    codigo_151_honorarios_pagados DECIMAL(18,2) DEFAULT 0,
    codigo_152_retencion_honorarios DECIMAL(18,2) DEFAULT 0,

    -- PPM
    codigo_36_ppm DECIMAL(18,2) DEFAULT 0,

    -- Total a pagar
    codigo_91_total_a_pagar DECIMAL(18,2) DEFAULT 0,
    codigo_93_total_a_favor DECIMAL(18,2) DEFAULT 0,

    -- Control
    estado ENUM('borrador', 'calculado', 'presentado', 'rectificado') DEFAULT 'borrador',
    fecha_presentacion DATE NULL,
    numero_operacion VARCHAR(50),
    folio_sii VARCHAR(50),

    -- Observaciones
    observaciones TEXT,
    ajustes_manuales TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,

    FOREIGN KEY (id_empresa) REFERENCES emp_empresas(id_empresa),
    FOREIGN KEY (id_periodo) REFERENCES con_periodos(id_periodo),
    UNIQUE KEY uk_empresa_periodo (id_empresa, anio, mes),
    INDEX idx_empresa (id_empresa),
    INDEX idx_periodo (anio, mes),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 15. DECLARACIONES JURADAS - Tabla Maestra
-- =====================================================

CREATE TABLE con_declaraciones_juradas (
    id_dj INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    anio_tributario INT NOT NULL,
    tipo_dj VARCHAR(10) NOT NULL,
    nombre_dj VARCHAR(255) NOT NULL,

    -- Estado
    estado ENUM('borrador', 'validado', 'presentado', 'rectificado') DEFAULT 'borrador',
    fecha_presentacion DATE NULL,
    folio_sii VARCHAR(50),
    numero_operacion VARCHAR(50),

    -- Totales
    total_registros INT DEFAULT 0,
    monto_total DECIMAL(18,2) DEFAULT 0,

    -- Observaciones
    observaciones TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,

    FOREIGN KEY (id_empresa) REFERENCES emp_empresas(id_empresa),
    UNIQUE KEY uk_empresa_anio_tipo (id_empresa, anio_tributario, tipo_dj),
    INDEX idx_empresa (id_empresa),
    INDEX idx_anio (anio_tributario),
    INDEX idx_tipo (tipo_dj),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 16. DJ 1879 - Honorarios
-- =====================================================

CREATE TABLE con_dj1879_honorarios (
    id_registro BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_dj INT NOT NULL,

    -- Beneficiario
    rut_beneficiario VARCHAR(12) NOT NULL,
    nombre_beneficiario VARCHAR(255) NOT NULL,

    -- Montos
    monto_honorarios DECIMAL(18,2) DEFAULT 0,
    monto_retencion DECIMAL(18,2) DEFAULT 0,

    -- Origen
    id_comprobante BIGINT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_dj) REFERENCES con_declaraciones_juradas(id_dj),
    FOREIGN KEY (id_comprobante) REFERENCES con_comprobantes(id_comprobante),
    INDEX idx_dj (id_dj),
    INDEX idx_beneficiario (rut_beneficiario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 17. DJ 1887 - Remuneraciones
-- =====================================================

CREATE TABLE con_dj1887_remuneraciones (
    id_registro BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_dj INT NOT NULL,

    -- Trabajador
    rut_trabajador VARCHAR(12) NOT NULL,
    nombre_trabajador VARCHAR(255) NOT NULL,

    -- Montos
    total_remuneraciones DECIMAL(18,2) DEFAULT 0,
    cotizaciones_previsionales DECIMAL(18,2) DEFAULT 0,
    retencion_impuesto DECIMAL(18,2) DEFAULT 0,

    -- Origen
    id_comprobante BIGINT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_dj) REFERENCES con_declaraciones_juradas(id_dj),
    FOREIGN KEY (id_comprobante) REFERENCES con_comprobantes(id_comprobante),
    INDEX idx_dj (id_dj),
    INDEX idx_trabajador (rut_trabajador)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 18. DJ 1948 - Retenciones
-- =====================================================

CREATE TABLE con_dj1948_retenciones (
    id_registro BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_dj INT NOT NULL,

    -- Beneficiario
    rut_beneficiario VARCHAR(12) NOT NULL,
    nombre_beneficiario VARCHAR(255) NOT NULL,

    -- Tipo de renta
    tipo_renta VARCHAR(50),
    codigo_renta VARCHAR(10),

    -- Montos
    monto_bruto DECIMAL(18,2) DEFAULT 0,
    tasa_retencion DECIMAL(5,2) DEFAULT 0,
    monto_retencion DECIMAL(18,2) DEFAULT 0,

    -- Origen
    id_comprobante BIGINT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_dj) REFERENCES con_declaraciones_juradas(id_dj),
    FOREIGN KEY (id_comprobante) REFERENCES con_comprobantes(id_comprobante),
    INDEX idx_dj (id_dj),
    INDEX idx_beneficiario (rut_beneficiario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 19. DJ Genérica (para otras DJ)
-- =====================================================

CREATE TABLE con_dj_generica (
    id_registro BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_dj INT NOT NULL,

    -- Datos identificación
    rut_entidad VARCHAR(12),
    nombre_entidad VARCHAR(255),

    -- Datos adicionales (JSON flexible)
    datos_json JSON,

    -- Montos principales
    monto_1 DECIMAL(18,2) DEFAULT 0,
    monto_2 DECIMAL(18,2) DEFAULT 0,
    monto_3 DECIMAL(18,2) DEFAULT 0,

    -- Origen
    id_comprobante BIGINT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_dj) REFERENCES con_declaraciones_juradas(id_dj),
    FOREIGN KEY (id_comprobante) REFERENCES con_comprobantes(id_comprobante),
    INDEX idx_dj (id_dj),
    INDEX idx_entidad (rut_entidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 20. CIERRES CONTABLES
-- =====================================================

CREATE TABLE con_cierres (
    id_cierre INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    tipo_cierre ENUM('mensual', 'anual') NOT NULL,
    id_periodo INT NOT NULL,
    anio INT NOT NULL,
    mes INT NULL,

    -- Validaciones
    validaciones_ok BOOLEAN DEFAULT FALSE,
    errores_validacion JSON,

    -- Resultado
    resultado_ejercicio DECIMAL(18,2),
    id_comprobante_cierre BIGINT NULL,

    -- Control
    estado ENUM('proceso', 'cerrado', 'reabierto') DEFAULT 'proceso',
    fecha_cierre TIMESTAMP NOT NULL,
    usuario_cierre INT NOT NULL,
    fecha_reapertura TIMESTAMP NULL,
    usuario_reapertura INT NULL,
    motivo_reapertura TEXT,

    observaciones TEXT,

    FOREIGN KEY (id_empresa) REFERENCES emp_empresas(id_empresa),
    FOREIGN KEY (id_periodo) REFERENCES con_periodos(id_periodo),
    FOREIGN KEY (id_comprobante_cierre) REFERENCES con_comprobantes(id_comprobante),
    FOREIGN KEY (usuario_cierre) REFERENCES sys_usuarios(id_usuario),
    FOREIGN KEY (usuario_reapertura) REFERENCES sys_usuarios(id_usuario),
    INDEX idx_empresa (id_empresa),
    INDEX idx_periodo (id_periodo),
    INDEX idx_tipo (tipo_cierre),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 21. MONEDAS Y TIPOS DE CAMBIO
-- =====================================================

CREATE TABLE con_monedas (
    id_moneda INT AUTO_INCREMENT PRIMARY KEY,
    codigo_moneda VARCHAR(3) NOT NULL UNIQUE,
    nombre_moneda VARCHAR(100) NOT NULL,
    simbolo VARCHAR(10),
    activa BOOLEAN DEFAULT TRUE,
    INDEX idx_activa (activa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO con_monedas (codigo_moneda, nombre_moneda, simbolo) VALUES
('CLP', 'Peso Chileno', '$'),
('USD', 'Dólar Estadounidense', 'US$'),
('EUR', 'Euro', '€'),
('UF', 'Unidad de Fomento', 'UF');

CREATE TABLE con_tipos_cambio (
    id_tipo_cambio INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    codigo_moneda VARCHAR(3) NOT NULL,
    tipo_cambio DECIMAL(10,4) NOT NULL,
    fuente VARCHAR(50) DEFAULT 'Manual',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_fecha_moneda (fecha, codigo_moneda),
    INDEX idx_fecha (fecha),
    INDEX idx_moneda (codigo_moneda)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 22. DOCUMENTOS ADJUNTOS
-- =====================================================

CREATE TABLE con_documentos_adjuntos (
    id_adjunto BIGINT AUTO_INCREMENT PRIMARY KEY,
    modulo VARCHAR(50) NOT NULL,
    id_registro BIGINT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    tipo_archivo VARCHAR(50),
    tamano_archivo INT,
    descripcion TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INT,
    FOREIGN KEY (uploaded_by) REFERENCES sys_usuarios(id_usuario),
    INDEX idx_modulo_registro (modulo, id_registro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 23. CONFIGURACIÓN GENERAL DEL MÓDULO
-- =====================================================

CREATE TABLE con_configuracion (
    id_config INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,

    -- Numeración automática
    proximo_numero_ingreso INT DEFAULT 1,
    proximo_numero_egreso INT DEFAULT 1,
    proximo_numero_traspaso INT DEFAULT 1,
    proximo_numero_ajuste INT DEFAULT 1,
    formato_numero_comprobante VARCHAR(50) DEFAULT '{TIPO}-{AAAA}{MM}-{NUMERO}',

    -- Configuración contable
    requiere_autorizacion_comprobantes BOOLEAN DEFAULT FALSE,
    permite_descuadre BOOLEAN DEFAULT FALSE,
    tolerancia_descuadre DECIMAL(10,2) DEFAULT 0,

    -- Configuración de cierres
    cierre_automatico_mensual BOOLEAN DEFAULT FALSE,
    dia_cierre_mensual INT DEFAULT 5,

    -- Integración
    centraliza_compras BOOLEAN DEFAULT TRUE,
    centraliza_ventas BOOLEAN DEFAULT TRUE,
    centraliza_bancos BOOLEAN DEFAULT TRUE,
    centraliza_remuneraciones BOOLEAN DEFAULT TRUE,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_empresa) REFERENCES emp_empresas(id_empresa),
    UNIQUE KEY uk_empresa (id_empresa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 24. VISTAS ÚTILES
-- =====================================================

-- Vista: Libro Diario
CREATE OR REPLACE VIEW vw_libro_diario AS
SELECT
    c.id_comprobante,
    c.id_empresa,
    e.razon_social as empresa,
    c.tipo_comprobante,
    c.numero_comprobante,
    c.fecha_contable,
    c.glosa_general,
    c.estado,
    d.numero_linea,
    pc.codigo_cuenta,
    pc.nombre_cuenta,
    d.debe,
    d.haber,
    d.glosa_detalle,
    cc.nombre_centro as centro_costo,
    aux.razon_social as auxiliar,
    td.nombre_tipo as tipo_documento,
    d.numero_documento,
    c.created_at
FROM con_comprobantes c
INNER JOIN emp_empresas e ON c.id_empresa = e.id_empresa
INNER JOIN con_comprobantes_detalle d ON c.id_comprobante = d.id_comprobante
INNER JOIN con_plan_cuentas pc ON d.id_cuenta = pc.id_cuenta
LEFT JOIN con_centros_costo cc ON d.id_centro_costo = cc.id_centro_costo
LEFT JOIN con_auxiliares aux ON d.id_auxiliar = aux.id_auxiliar
LEFT JOIN con_tipos_documentos td ON d.id_tipo_documento = td.id_tipo_documento
WHERE c.eliminado = FALSE AND c.estado = 'Contabilizado'
ORDER BY c.fecha_contable, c.numero_comprobante, d.numero_linea;

-- Vista: Libro Mayor
CREATE OR REPLACE VIEW vw_libro_mayor AS
SELECT
    pc.id_cuenta,
    pc.id_empresa,
    e.razon_social as empresa,
    pc.codigo_cuenta,
    pc.nombre_cuenta,
    pc.clasificacion,
    c.fecha_contable,
    c.tipo_comprobante,
    c.numero_comprobante,
    d.glosa_detalle,
    d.debe,
    d.haber,
    (d.debe - d.haber) as saldo_movimiento
FROM con_plan_cuentas pc
INNER JOIN emp_empresas e ON pc.id_empresa = e.id_empresa
LEFT JOIN con_comprobantes_detalle d ON pc.id_cuenta = d.id_cuenta
LEFT JOIN con_comprobantes c ON d.id_comprobante = c.id_comprobante AND c.estado = 'Contabilizado' AND c.eliminado = FALSE
WHERE pc.eliminado = FALSE AND pc.vigente = TRUE
ORDER BY pc.codigo_cuenta, c.fecha_contable;

-- =====================================================
-- 25. TRIGGERS PARA AUDITORÍA Y CONTROL
-- =====================================================

-- Trigger: Actualizar totales del comprobante
DELIMITER $$
CREATE TRIGGER trg_actualizar_totales_comprobante
AFTER INSERT ON con_comprobantes_detalle
FOR EACH ROW
BEGIN
    UPDATE con_comprobantes
    SET
        total_debe = (SELECT COALESCE(SUM(debe), 0) FROM con_comprobantes_detalle WHERE id_comprobante = NEW.id_comprobante),
        total_haber = (SELECT COALESCE(SUM(haber), 0) FROM con_comprobantes_detalle WHERE id_comprobante = NEW.id_comprobante),
        diferencia = (
            (SELECT COALESCE(SUM(debe), 0) FROM con_comprobantes_detalle WHERE id_comprobante = NEW.id_comprobante) -
            (SELECT COALESCE(SUM(haber), 0) FROM con_comprobantes_detalle WHERE id_comprobante = NEW.id_comprobante)
        )
    WHERE id_comprobante = NEW.id_comprobante;
END$$

DELIMITER ;

-- =====================================================
-- 26. DATOS INICIALES
-- =====================================================

-- Perfiles por defecto
INSERT INTO sys_perfiles (nombre_perfil, descripcion) VALUES
('Administrador', 'Acceso total al sistema'),
('Contador', 'Acceso completo a módulo contable'),
('Asistente Contable', 'Acceso limitado a módulo contable'),
('Consulta', 'Solo lectura');

-- Usuario administrador por defecto (password: admin123)
INSERT INTO sys_usuarios (username, password, nombre_completo, email, id_perfil) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador del Sistema', 'admin@conectaerp.cl', 1);

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
