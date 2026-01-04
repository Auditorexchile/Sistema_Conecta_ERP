-- =====================================================
-- Conecta ERP - Módulo IA de Auditoría Financiera
-- Archivo: 21_ia_auditoria.sql
-- Descripción: Sistema de auditoría inteligente con IA
-- =====================================================

-- Tabla: ai_auditorias
CREATE TABLE IF NOT EXISTS ai_auditorias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    tipo_auditoria ENUM('contable', 'fraude', 'tributaria', 'ifrs', 'tesoreria', 'rrhh', 'predictiva', 'integral') NOT NULL,
    estado ENUM('pendiente', 'en_proceso', 'completada', 'error') DEFAULT 'pendiente',
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NULL,
    periodo_desde DATE NOT NULL,
    periodo_hasta DATE NOT NULL,
    modulos_analizados TEXT NULL COMMENT 'JSON array',
    registros_analizados INT DEFAULT 0,
    anomalias_detectadas INT DEFAULT 0,
    riesgo_general ENUM('bajo', 'medio', 'alto', 'critico') NULL,
    score_riesgo DECIMAL(5, 2) NULL COMMENT '0-100',
    ejecutada_por INT UNSIGNED NULL,
    modelo_ia_version VARCHAR(50) NULL,
    tiempo_ejecucion INT NULL COMMENT 'segundos',
    resultados_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    INDEX idx_empresa (id_empresa),
    INDEX idx_tipo (tipo_auditoria),
    INDEX idx_estado (estado),
    INDEX idx_riesgo (riesgo_general),
    INDEX idx_fecha_inicio (fecha_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: ai_auditorias_resultados
CREATE TABLE IF NOT EXISTS ai_auditorias_resultados (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_auditoria BIGINT UNSIGNED NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    subcategoria VARCHAR(100) NULL,
    tipo_hallazgo ENUM('error', 'warning', 'anomalia', 'fraude_potencial', 'incumplimiento', 'optimizacion') NOT NULL,
    gravedad ENUM('baja', 'media', 'alta', 'critica') NOT NULL,
    modulo VARCHAR(100) NOT NULL,
    tabla_afectada VARCHAR(100) NULL,
    id_registro INT NULL,
    descripcion TEXT NOT NULL,
    detalle_tecnico TEXT NULL,
    evidencia TEXT NULL COMMENT 'JSON con datos de soporte',
    recomendacion TEXT NULL,
    accion_sugerida TEXT NULL,
    puede_autocorregir TINYINT(1) DEFAULT 0,
    corregido TINYINT(1) DEFAULT 0,
    fecha_correccion DATETIME NULL,
    corregido_por INT UNSIGNED NULL,
    confianza_ia DECIMAL(5, 2) NULL COMMENT '0-100%',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_auditoria) REFERENCES ai_auditorias(id) ON DELETE CASCADE,
    INDEX idx_auditoria (id_auditoria),
    INDEX idx_gravedad (gravedad),
    INDEX idx_modulo (modulo),
    INDEX idx_corregido (corregido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: ai_alertas
CREATE TABLE IF NOT EXISTS ai_alertas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    tipo_alerta ENUM('fraude', 'riesgo_tributario', 'riesgo_financiero', 'anomalia', 'incumplimiento', 'predictiva') NOT NULL,
    prioridad ENUM('baja', 'media', 'alta', 'critica') NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    modulo VARCHAR(100) NULL,
    datos_contexto TEXT NULL COMMENT 'JSON',
    accion_requerida TEXT NULL,
    url_redireccion VARCHAR(255) NULL,
    leida TINYINT(1) DEFAULT 0,
    fecha_lectura DATETIME NULL,
    leida_por INT UNSIGNED NULL,
    activa TINYINT(1) DEFAULT 1,
    fecha_alerta DATETIME NOT NULL,
    fecha_vencimiento DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    INDEX idx_empresa (id_empresa),
    INDEX idx_tipo (tipo_alerta),
    INDEX idx_prioridad (prioridad),
    INDEX idx_leida (leida),
    INDEX idx_activa (activa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: ai_riesgos
CREATE TABLE IF NOT EXISTS ai_riesgos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    tipo_riesgo ENUM('quiebra', 'iliquidez', 'tributario', 'fraude', 'operacional', 'cumplimiento') NOT NULL,
    nivel_riesgo ENUM('bajo', 'medio', 'alto', 'critico') NOT NULL,
    score_riesgo DECIMAL(5, 2) NOT NULL COMMENT '0-100',
    probabilidad DECIMAL(5, 2) NULL COMMENT '0-100%',
    impacto_estimado DECIMAL(15, 2) NULL,
    moneda CHAR(3) NULL,
    descripcion TEXT NOT NULL,
    factores_riesgo TEXT NULL COMMENT 'JSON array',
    indicadores TEXT NULL COMMENT 'JSON con métricas',
    fecha_deteccion DATETIME NOT NULL,
    fecha_proyectada DATE NULL COMMENT 'Fecha estimada del evento',
    mitigado TINYINT(1) DEFAULT 0,
    fecha_mitigacion DATETIME NULL,
    acciones_mitigacion TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    INDEX idx_empresa (id_empresa),
    INDEX idx_tipo (tipo_riesgo),
    INDEX idx_nivel (nivel_riesgo),
    INDEX idx_mitigado (mitigado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: ai_modelos
CREATE TABLE IF NOT EXISTS ai_modelos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    tipo ENUM('clasificacion', 'regresion', 'deteccion_anomalias', 'clustering', 'nlp', 'series_tiempo') NOT NULL,
    proposito VARCHAR(255) NOT NULL,
    version VARCHAR(50) NOT NULL,
    algoritmo VARCHAR(100) NULL,
    precision DECIMAL(5, 2) NULL COMMENT '0-100%',
    recall DECIMAL(5, 2) NULL,
    f1_score DECIMAL(5, 2) NULL,
    fecha_entrenamiento DATE NULL,
    fecha_ultima_actualizacion DATE NULL,
    registros_entrenamiento INT NULL,
    parametros TEXT NULL COMMENT 'JSON',
    activo TINYINT(1) DEFAULT 1,
    en_produccion TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: ai_logs_ejecucion
CREATE TABLE IF NOT EXISTS ai_logs_ejecucion (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_modelo INT UNSIGNED NULL,
    id_empresa INT UNSIGNED NOT NULL,
    tipo_tarea VARCHAR(100) NOT NULL,
    estado ENUM('iniciada', 'completada', 'error', 'timeout') NOT NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NULL,
    tiempo_ejecucion INT NULL COMMENT 'milisegundos',
    registros_procesados INT NULL,
    memoria_usada INT NULL COMMENT 'MB',
    cpu_usado DECIMAL(5, 2) NULL COMMENT '%',
    resultado TEXT NULL COMMENT 'JSON',
    errores TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_modelo (id_modelo),
    INDEX idx_empresa (id_empresa),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: ai_recomendaciones
CREATE TABLE IF NOT EXISTS ai_recomendaciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_auditoria BIGINT UNSIGNED NULL,
    tipo_recomendacion ENUM('optimizacion', 'correccion', 'prevencion', 'mejora', 'cumplimiento') NOT NULL,
    prioridad ENUM('baja', 'media', 'alta') DEFAULT 'media',
    modulo VARCHAR(100) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    beneficio_esperado TEXT NULL,
    impacto_estimado VARCHAR(255) NULL,
    esfuerzo_requerido ENUM('bajo', 'medio', 'alto') NULL,
    pasos_implementacion TEXT NULL COMMENT 'JSON array',
    implementada TINYINT(1) DEFAULT 0,
    fecha_implementacion DATETIME NULL,
    implementada_por INT UNSIGNED NULL,
    resultado_implementacion TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_auditoria) REFERENCES ai_auditorias(id) ON DELETE SET NULL,
    INDEX idx_empresa (id_empresa),
    INDEX idx_tipo (tipo_recomendacion),
    INDEX idx_prioridad (prioridad),
    INDEX idx_implementada (implementada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: ai_parametros
CREATE TABLE IF NOT EXISTS ai_parametros (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NULL COMMENT 'NULL = parámetro global',
    codigo_pais CHAR(2) NULL,
    parametro VARCHAR(255) NOT NULL,
    valor TEXT NOT NULL,
    tipo ENUM('string', 'number', 'boolean', 'json', 'array') DEFAULT 'string',
    categoria VARCHAR(100) NULL,
    descripcion TEXT NULL,
    modificable TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    INDEX idx_empresa (id_empresa),
    INDEX idx_pais (codigo_pais),
    INDEX idx_parametro (parametro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: ai_scores
CREATE TABLE IF NOT EXISTS ai_scores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    tipo_score ENUM('riesgo_global', 'riesgo_tributario', 'riesgo_financiero', 'riesgo_fraude', 'salud_financiera', 'cumplimiento') NOT NULL,
    score DECIMAL(5, 2) NOT NULL COMMENT '0-100',
    nivel ENUM('muy_bajo', 'bajo', 'medio', 'alto', 'muy_alto') NOT NULL,
    factores TEXT NULL COMMENT 'JSON con factores que afectan el score',
    fecha_calculo DATE NOT NULL,
    periodo_evaluado VARCHAR(50) NULL,
    valido_hasta DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    INDEX idx_empresa (id_empresa),
    INDEX idx_tipo (tipo_score),
    INDEX idx_fecha (fecha_calculo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: ai_chat_historial
CREATE TABLE IF NOT EXISTS ai_chat_historial (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NOT NULL,
    sesion_id VARCHAR(255) NOT NULL,
    mensaje_usuario TEXT NOT NULL,
    mensaje_ia TEXT NOT NULL,
    tipo_consulta ENUM('auditoria', 'riesgo', 'cumplimiento', 'optimizacion', 'general') NULL,
    contexto TEXT NULL COMMENT 'JSON con contexto de la consulta',
    confianza_respuesta DECIMAL(5, 2) NULL COMMENT '0-100%',
    util TINYINT(1) NULL,
    feedback TEXT NULL,
    fecha_mensaje DATETIME NOT NULL,
    INDEX idx_empresa (id_empresa),
    INDEX idx_usuario (id_usuario),
    INDEX idx_sesion (sesion_id),
    INDEX idx_fecha (fecha_mensaje)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ESQUEMA 21_ia_auditoria.sql
-- =====================================================
