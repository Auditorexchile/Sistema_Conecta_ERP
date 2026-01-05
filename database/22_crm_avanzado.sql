-- =====================================================
-- CONECTA ERP - CRM AVANZADO
-- Archivo: 22_crm_avanzado.sql
-- Descripción: Funnel de ventas, leads, oportunidades, tickets
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: leads
-- Descripción: Prospectos potenciales
-- =====================================================
CREATE TABLE leads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,

    nombre VARCHAR(200) NOT NULL,
    email VARCHAR(150) NULL,
    telefono VARCHAR(30) NULL,
    empresa VARCHAR(200) NULL,

    origen ENUM('web', 'telefono', 'email', 'referido', 'evento', 'otro') DEFAULT 'web',
    estado ENUM('nuevo', 'contactado', 'calificado', 'descalificado', 'convertido') DEFAULT 'nuevo',

    id_vendedor INT UNSIGNED NULL,
    fecha_conversion DATE NULL,
    id_cliente_convertido INT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_estado (estado),
    INDEX idx_vendedor (id_vendedor),
    INDEX idx_email (email),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_cliente_convertido) REFERENCES entidades(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: oportunidades
-- Descripción: Oportunidades de venta
-- =====================================================
CREATE TABLE oportunidades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    nombre VARCHAR(200) NOT NULL,

    id_cliente INT UNSIGNED NULL,
    id_lead INT UNSIGNED NULL,

    monto_estimado DECIMAL(15,4) DEFAULT 0,
    probabilidad INT DEFAULT 50 COMMENT 'Porcentaje 0-100',

    etapa ENUM('prospeccion', 'calificacion', 'propuesta', 'negociacion', 'cerrada_ganada', 'cerrada_perdida') DEFAULT 'prospeccion',

    fecha_cierre_estimada DATE NULL,
    fecha_cierre_real DATE NULL,

    id_vendedor INT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_cliente (id_cliente),
    INDEX idx_lead (id_lead),
    INDEX idx_etapa (etapa),
    INDEX idx_vendedor (id_vendedor),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_cliente) REFERENCES entidades(id) ON DELETE SET NULL,
    FOREIGN KEY (id_lead) REFERENCES leads(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: tickets_soporte
-- Descripción: Tickets de soporte al cliente
-- =====================================================
CREATE TABLE tickets_soporte (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_ticket VARCHAR(50) NOT NULL,

    id_cliente INT UNSIGNED NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,

    prioridad ENUM('baja', 'media', 'alta', 'urgente') DEFAULT 'media',
    estado ENUM('abierto', 'en_progreso', 'resuelto', 'cerrado', 'cancelado') DEFAULT 'abierto',

    id_asignado INT UNSIGNED NULL,

    fecha_apertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_ticket),
    INDEX idx_cliente (id_cliente),
    INDEX idx_estado (estado),
    INDEX idx_asignado (id_asignado),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_ticket),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_cliente) REFERENCES entidades(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
