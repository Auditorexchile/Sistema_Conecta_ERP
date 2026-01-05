-- =====================================================
-- CONECTA ERP - MÓDULO PROYECTOS
-- Archivo: 12_proyectos.sql
-- Descripción: Gestión de proyectos, tareas, recursos
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: proyectos
-- Descripción: Proyectos de la empresa
-- =====================================================
CREATE TABLE proyectos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    codigo_proyecto VARCHAR(50) NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT NULL,

    id_cliente INT UNSIGNED NULL,
    id_responsable INT UNSIGNED NULL,
    id_centro_costo INT UNSIGNED NULL,

    fecha_inicio DATE NOT NULL,
    fecha_fin_planificada DATE NULL,
    fecha_fin_real DATE NULL,

    presupuesto DECIMAL(15,4) DEFAULT 0,
    costo_real DECIMAL(15,4) DEFAULT 0,

    estado ENUM('planificacion', 'en_curso', 'pausado', 'finalizado', 'cancelado') NOT NULL DEFAULT 'planificacion',
    prioridad ENUM('baja', 'media', 'alta', 'critica') DEFAULT 'media',

    porcentaje_avance DECIMAL(5,2) DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_codigo (codigo_proyecto),
    INDEX idx_cliente (id_cliente),
    INDEX idx_estado (estado),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo_proyecto, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_cliente) REFERENCES entidades(id) ON DELETE SET NULL,
    FOREIGN KEY (id_centro_costo) REFERENCES centros_costo(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: tareas_proyecto
-- Descripción: Tareas dentro de proyectos
-- =====================================================
CREATE TABLE tareas_proyecto (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_proyecto INT UNSIGNED NOT NULL,
    id_tarea_padre INT UNSIGNED NULL,

    codigo_tarea VARCHAR(50) NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT NULL,

    fecha_inicio DATE NULL,
    fecha_fin DATE NULL,
    horas_estimadas DECIMAL(8,2) DEFAULT 0,
    horas_reales DECIMAL(8,2) DEFAULT 0,

    id_responsable INT UNSIGNED NULL,
    prioridad ENUM('baja', 'media', 'alta', 'urgente') DEFAULT 'media',
    estado ENUM('pendiente', 'en_curso', 'revision', 'completada', 'cancelada') NOT NULL DEFAULT 'pendiente',

    porcentaje_avance DECIMAL(5,2) DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_proyecto (id_proyecto),
    INDEX idx_padre (id_tarea_padre),
    INDEX idx_estado (estado),
    INDEX idx_responsable (id_responsable),
    FOREIGN KEY (id_proyecto) REFERENCES proyectos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_tarea_padre) REFERENCES tareas_proyecto(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
