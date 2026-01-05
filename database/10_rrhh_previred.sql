-- =====================================================
-- CONECTA ERP - MÓDULO RRHH Y PREVIRED (CHILE)
-- Archivo: 10_rrhh_previred.sql
-- Descripción: Empleados, contratos, nóminas, liquidaciones, Previred
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: empleados
-- Descripción: Maestro de empleados
-- =====================================================
CREATE TABLE empleados (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_entidad INT UNSIGNED NULL COMMENT 'Relación con tabla entidades',

    codigo_empleado VARCHAR(20) NOT NULL,
    identificador VARCHAR(30) NOT NULL COMMENT 'RUT, DNI, etc',

    nombres VARCHAR(150) NOT NULL,
    apellido_paterno VARCHAR(100) NOT NULL,
    apellido_materno VARCHAR(100) NULL,
    nombre_completo VARCHAR(400) AS (CONCAT(nombres, ' ', apellido_paterno, ' ', IFNULL(apellido_materno, ''))) STORED,

    fecha_nacimiento DATE NULL,
    genero ENUM('masculino', 'femenino', 'otro') NULL,
    estado_civil ENUM('soltero', 'casado', 'viudo', 'divorciado', 'union_civil') NULL,
    nacionalidad VARCHAR(50) NULL,

    -- Contacto
    email_corporativo VARCHAR(150) NULL,
    email_personal VARCHAR(150) NULL,
    telefono VARCHAR(30) NULL,
    celular VARCHAR(30) NULL,

    -- Dirección
    direccion TEXT NULL,
    ciudad VARCHAR(100) NULL,
    region VARCHAR(100) NULL,
    codigo_postal VARCHAR(20) NULL,

    -- Emergencia
    contacto_emergencia VARCHAR(200) NULL,
    telefono_emergencia VARCHAR(30) NULL,

    -- Bancarios
    id_banco INT UNSIGNED NULL,
    numero_cuenta VARCHAR(50) NULL,
    tipo_cuenta ENUM('corriente', 'ahorro', 'vista') NULL,

    -- Previsión (Chile)
    afp VARCHAR(100) NULL,
    isapre VARCHAR(100) NULL,
    plan_salud VARCHAR(100) NULL,
    ges TINYINT(1) DEFAULT 1,

    -- Situación laboral
    estado ENUM('activo', 'inactivo', 'licencia', 'vacaciones', 'desvinculado') NOT NULL DEFAULT 'activo',
    fecha_ingreso DATE NULL,
    fecha_egreso DATE NULL,
    motivo_egreso VARCHAR(255) NULL,

    -- Foto y documentos
    foto VARCHAR(255) NULL,
    documentos JSON NULL COMMENT 'Array de URLs',

    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_entidad (id_entidad),
    INDEX idx_codigo (codigo_empleado),
    INDEX idx_identificador (identificador),
    INDEX idx_estado (estado),
    INDEX idx_email (email_corporativo),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo_empleado, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_entidad) REFERENCES entidades(id) ON DELETE SET NULL,
    FOREIGN KEY (id_banco) REFERENCES bancos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: contratos
-- Descripción: Contratos laborales
-- =====================================================
CREATE TABLE contratos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empleado INT UNSIGNED NOT NULL,
    numero_contrato VARCHAR(50) NOT NULL,

    tipo_contrato ENUM('indefinido', 'plazo_fijo', 'por_obra', 'honorarios', 'practicante') NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_termino DATE NULL,

    -- Cargo y departamento
    cargo VARCHAR(150) NOT NULL,
    id_departamento INT UNSIGNED NULL,
    id_centro_costo INT UNSIGNED NULL,

    -- Jornada
    jornada ENUM('completa', 'parcial', 'por_turnos') DEFAULT 'completa',
    horas_semanales DECIMAL(5,2) DEFAULT 45,

    -- Remuneración
    sueldo_base DECIMAL(15,4) NOT NULL,
    id_moneda INT UNSIGNED NOT NULL,
    periodicidad ENUM('mensual', 'quincenal', 'semanal', 'diario', 'por_hora') DEFAULT 'mensual',

    -- Asignaciones
    colacion DECIMAL(15,4) DEFAULT 0,
    movilizacion DECIMAL(15,4) DEFAULT 0,

    -- Estado
    estado ENUM('vigente', 'finiquitado', 'suspendido', 'anulado') NOT NULL DEFAULT 'vigente',

    observaciones TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empleado (id_empleado),
    INDEX idx_tipo (tipo_contrato),
    INDEX idx_estado (estado),
    INDEX idx_fechas (fecha_inicio, fecha_termino),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id) ON DELETE CASCADE,
    FOREIGN KEY (id_centro_costo) REFERENCES centros_costo(id) ON DELETE SET NULL,
    FOREIGN KEY (id_moneda) REFERENCES monedas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: conceptos_remuneracion
-- Descripción: Conceptos de haberes y descuentos
-- =====================================================
CREATE TABLE conceptos_remuneracion (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    codigo VARCHAR(20) NOT NULL,
    nombre VARCHAR(150) NOT NULL,

    tipo ENUM('haber', 'descuento') NOT NULL,
    subtipo ENUM('fijo', 'variable', 'legal', 'previsional', 'otro') DEFAULT 'fijo',

    -- Imponible y tributario
    imponible TINYINT(1) DEFAULT 1,
    tributable TINYINT(1) DEFAULT 1,

    -- Fórmula de cálculo
    formula VARCHAR(500) NULL COMMENT 'Fórmula para cálculo automático',
    tipo_calculo ENUM('monto_fijo', 'porcentaje_sueldo', 'formula', 'manual') DEFAULT 'monto_fijo',
    valor_default DECIMAL(15,4) DEFAULT 0,

    -- Previred (Chile)
    codigo_previred VARCHAR(10) NULL,

    -- Contabilidad
    cuenta_contable VARCHAR(20) NULL,
    id_centro_costo INT UNSIGNED NULL,

    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_codigo (codigo),
    INDEX idx_tipo (tipo, subtipo),
    INDEX idx_previred (codigo_previred),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_centro_costo) REFERENCES centros_costo(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos iniciales conceptos Chile
INSERT INTO conceptos_remuneracion (id_empresa, codigo, nombre, tipo, subtipo, imponible, tributable, codigo_previred)
SELECT e.id, 'SBASE', 'Sueldo Base', 'haber', 'fijo', 1, 1, '001'
FROM empresas e WHERE e.codigo_pais = 'CL' LIMIT 1;

INSERT INTO conceptos_remuneracion (id_empresa, codigo, nombre, tipo, subtipo, imponible, tributable, codigo_previred, tipo_calculo, valor_default)
SELECT e.id, 'AFP', 'AFP', 'descuento', 'previsional', 0, 0, '106', 'porcentaje_sueldo', 11.44
FROM empresas e WHERE e.codigo_pais = 'CL' LIMIT 1;

INSERT INTO conceptos_remuneracion (id_empresa, codigo, nombre, tipo, subtipo, imponible, tributable, codigo_previred, tipo_calculo, valor_default)
SELECT e.id, 'SALUD', 'Isapre/Fonasa', 'descuento', 'previsional', 0, 0, '108', 'porcentaje_sueldo', 7.00
FROM empresas e WHERE e.codigo_pais = 'CL' LIMIT 1;

-- =====================================================
-- TABLA: nominas
-- Descripción: Períodos de nómina (proceso mensual)
-- =====================================================
CREATE TABLE nominas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_nomina VARCHAR(50) NOT NULL,

    periodo_mes INT NOT NULL,
    periodo_ano INT NOT NULL,
    fecha_pago DATE NOT NULL,

    estado ENUM('borrador', 'calculada', 'aprobada', 'pagada', 'cerrada', 'anulada') NOT NULL DEFAULT 'borrador',

    total_haberes DECIMAL(15,4) DEFAULT 0,
    total_descuentos DECIMAL(15,4) DEFAULT 0,
    total_liquido DECIMAL(15,4) AS (total_haberes - total_descuentos) STORED,

    cantidad_empleados INT DEFAULT 0,

    id_usuario_crea INT UNSIGNED NULL,
    id_usuario_aprueba INT UNSIGNED NULL,
    fecha_aprobacion TIMESTAMP NULL,

    observaciones TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_nomina),
    INDEX idx_periodo (periodo_ano, periodo_mes),
    INDEX idx_estado (estado),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_nomina),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: liquidaciones
-- Descripción: Liquidaciones individuales de sueldo
-- =====================================================
CREATE TABLE liquidaciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_nomina INT UNSIGNED NOT NULL,
    id_empleado INT UNSIGNED NOT NULL,
    id_contrato INT UNSIGNED NOT NULL,

    dias_trabajados INT DEFAULT 30,
    horas_trabajadas DECIMAL(8,2) DEFAULT 0,
    horas_extras DECIMAL(8,2) DEFAULT 0,

    -- Totales
    total_haberes_imponibles DECIMAL(15,4) DEFAULT 0,
    total_haberes_no_imponibles DECIMAL(15,4) DEFAULT 0,
    total_haberes DECIMAL(15,4) AS (total_haberes_imponibles + total_haberes_no_imponibles) STORED,

    total_descuentos_legales DECIMAL(15,4) DEFAULT 0,
    total_descuentos_otros DECIMAL(15,4) DEFAULT 0,
    total_descuentos DECIMAL(15,4) AS (total_descuentos_legales + total_descuentos_otros) STORED,

    liquido_pagar DECIMAL(15,4) AS (total_haberes - total_descuentos) STORED,

    -- Previred
    afp_monto DECIMAL(15,4) DEFAULT 0,
    salud_monto DECIMAL(15,4) DEFAULT 0,
    cesantia_trabajador DECIMAL(15,4) DEFAULT 0,
    cesantia_empleador DECIMAL(15,4) DEFAULT 0,

    -- Impuesto único
    impuesto_unico DECIMAL(15,4) DEFAULT 0,

    -- Archivo Previred
    enviado_previred TINYINT(1) DEFAULT 0,
    fecha_envio_previred TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_nomina (id_nomina),
    INDEX idx_empleado (id_empleado),
    INDEX idx_contrato (id_contrato),
    INDEX idx_previred (enviado_previred),
    UNIQUE KEY uk_nomina_empleado (id_nomina, id_empleado),
    FOREIGN KEY (id_nomina) REFERENCES nominas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_empleado) REFERENCES empleados(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_contrato) REFERENCES contratos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: liquidaciones_detalle
-- Descripción: Detalle de haberes y descuentos por liquidación
-- =====================================================
CREATE TABLE liquidaciones_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_liquidacion INT UNSIGNED NOT NULL,
    linea INT NOT NULL,
    id_concepto INT UNSIGNED NOT NULL,

    descripcion VARCHAR(255) NULL,
    cantidad DECIMAL(15,4) DEFAULT 1,
    monto DECIMAL(15,4) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_liquidacion (id_liquidacion),
    INDEX idx_concepto (id_concepto),
    FOREIGN KEY (id_liquidacion) REFERENCES liquidaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (id_concepto) REFERENCES conceptos_remuneracion(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: asistencia
-- Descripción: Registro de asistencia y marcajes
-- =====================================================
CREATE TABLE asistencia (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empleado INT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,

    hora_entrada TIMESTAMP NULL,
    hora_salida TIMESTAMP NULL,

    minutos_trabajados INT AS (TIMESTAMPDIFF(MINUTE, hora_entrada, hora_salida)) STORED,
    horas_trabajadas DECIMAL(5,2) AS (minutos_trabajados / 60.0) STORED,

    tipo_jornada ENUM('normal', 'extra', 'nocturna', 'feriado') DEFAULT 'normal',
    estado ENUM('presente', 'ausente', 'tarde', 'licencia', 'permiso', 'vacaciones') DEFAULT 'presente',

    observaciones VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empleado (id_empleado),
    INDEX idx_fecha (fecha),
    INDEX idx_tipo (tipo_jornada),
    INDEX idx_estado (estado),
    UNIQUE KEY uk_empleado_fecha (id_empleado, fecha),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: vacaciones
-- Descripción: Gestión de vacaciones
-- =====================================================
CREATE TABLE vacaciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empleado INT UNSIGNED NOT NULL,

    periodo_ano INT NOT NULL,
    dias_legales DECIMAL(5,2) DEFAULT 15,
    dias_progresivos DECIMAL(5,2) DEFAULT 0,
    dias_adicionales DECIMAL(5,2) DEFAULT 0,
    dias_totales DECIMAL(5,2) AS (dias_legales + dias_progresivos + dias_adicionales) STORED,

    dias_tomados DECIMAL(5,2) DEFAULT 0,
    dias_pendientes DECIMAL(5,2) AS (dias_totales - dias_tomados) STORED,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empleado (id_empleado),
    INDEX idx_periodo (periodo_ano),
    UNIQUE KEY uk_empleado_periodo (id_empleado, periodo_ano),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: solicitudes_vacaciones
-- Descripción: Solicitudes de vacaciones
-- =====================================================
CREATE TABLE solicitudes_vacaciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empleado INT UNSIGNED NOT NULL,
    id_vacaciones INT UNSIGNED NOT NULL,

    fecha_desde DATE NOT NULL,
    fecha_hasta DATE NOT NULL,
    dias_solicitados DECIMAL(5,2) NOT NULL,

    estado ENUM('pendiente', 'aprobada', 'rechazada', 'cancelada') NOT NULL DEFAULT 'pendiente',

    id_aprobador INT UNSIGNED NULL,
    fecha_aprobacion TIMESTAMP NULL,
    observaciones TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empleado (id_empleado),
    INDEX idx_vacaciones (id_vacaciones),
    INDEX idx_estado (estado),
    INDEX idx_fechas (fecha_desde, fecha_hasta),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id) ON DELETE CASCADE,
    FOREIGN KEY (id_vacaciones) REFERENCES vacaciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: prestamos_empleados
-- Descripción: Préstamos a empleados
-- =====================================================
CREATE TABLE prestamos_empleados (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empleado INT UNSIGNED NOT NULL,
    numero_prestamo VARCHAR(50) NOT NULL,

    monto_prestamo DECIMAL(15,4) NOT NULL,
    cantidad_cuotas INT NOT NULL,
    monto_cuota DECIMAL(15,4) AS (monto_prestamo / cantidad_cuotas) STORED,

    cuotas_pagadas INT DEFAULT 0,
    saldo_pendiente DECIMAL(15,4) AS (monto_prestamo - (cuotas_pagadas * (monto_prestamo / cantidad_cuotas))) STORED,

    fecha_otorgamiento DATE NOT NULL,
    fecha_primer_descuento DATE NOT NULL,

    estado ENUM('activo', 'pagado', 'condonado', 'anulado') NOT NULL DEFAULT 'activo',

    observaciones TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empleado (id_empleado),
    INDEX idx_numero (numero_prestamo),
    INDEX idx_estado (estado),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
