-- =====================================================
-- CONECTA ERP - AUDITORÍA, NOTIFICACIONES Y WORKFLOW
-- Archivo: 26_auditoria_notificaciones_workflow.sql
-- Descripción: Tablas complementarias de auditoría, notificaciones, documentos y workflow
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- SECCIÓN: AUDITORÍA MEJORADA
-- =====================================================

-- =====================================================
-- TABLA: logs_auditoria
-- Descripción: Log completo de acciones CRUD del sistema
-- =====================================================
CREATE TABLE logs_auditoria (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NULL,

    -- Acción realizada
    accion ENUM('crear', 'actualizar', 'eliminar', 'leer', 'exportar', 'importar') NOT NULL,
    tabla VARCHAR(100) NOT NULL,
    id_registro INT UNSIGNED NULL,

    -- Datos antes y después
    datos_anteriores JSON NULL COMMENT 'Estado antes del cambio',
    datos_nuevos JSON NULL COMMENT 'Estado después del cambio',
    campos_modificados JSON NULL COMMENT 'Lista de campos que cambiaron',

    -- Contexto
    ip_address VARCHAR(50) NULL,
    user_agent TEXT NULL,
    url VARCHAR(500) NULL,
    metodo_http VARCHAR(10) NULL COMMENT 'GET, POST, PUT, DELETE',

    -- Resultado
    exitoso TINYINT(1) DEFAULT 1,
    mensaje_error TEXT NULL,

    fecha_accion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_usuario (id_usuario),
    INDEX idx_accion (accion),
    INDEX idx_tabla (tabla),
    INDEX idx_registro (tabla, id_registro),
    INDEX idx_fecha (fecha_accion),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios_acceso(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: historial_cambios
-- Descripción: Versionamiento de registros importantes
-- =====================================================
CREATE TABLE historial_cambios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,

    tabla VARCHAR(100) NOT NULL,
    id_registro INT UNSIGNED NOT NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,

    -- Snapshot completo del registro
    datos_version JSON NOT NULL COMMENT 'Estado completo en esta versión',

    -- Metadatos
    id_usuario_modifica INT UNSIGNED NULL,
    motivo_cambio VARCHAR(500) NULL,
    es_version_actual TINYINT(1) DEFAULT 1,

    fecha_version TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_tabla_registro (tabla, id_registro),
    INDEX idx_version (version),
    INDEX idx_fecha (fecha_version),
    INDEX idx_actual (es_version_actual),
    UNIQUE KEY uk_tabla_registro_version (tabla, id_registro, version),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_modifica) REFERENCES usuarios_acceso(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: logs_login
-- Descripción: Registro detallado de intentos de acceso
-- =====================================================
CREATE TABLE logs_login (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NULL,
    email VARCHAR(150) NOT NULL,

    -- Resultado del intento
    exitoso TINYINT(1) NOT NULL,
    motivo_fallo ENUM('credenciales_invalidas', 'usuario_bloqueado', 'usuario_inactivo', 'trial_expirado', 'cuenta_suspendida', 'otro') NULL,
    mensaje VARCHAR(500) NULL,

    -- Información del intento
    ip_address VARCHAR(50) NULL,
    user_agent TEXT NULL,
    pais VARCHAR(50) NULL,
    ciudad VARCHAR(100) NULL,

    -- Detección de anomalías
    es_sospechoso TINYINT(1) DEFAULT 0,
    razon_sospecha VARCHAR(255) NULL COMMENT 'IP nueva, ubicación inusual, etc',

    -- Autenticación multifactor
    requirio_2fa TINYINT(1) DEFAULT 0,
    2fa_exitoso TINYINT(1) NULL,

    fecha_intento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_usuario (id_usuario),
    INDEX idx_email (email),
    INDEX idx_exitoso (exitoso),
    INDEX idx_ip (ip_address),
    INDEX idx_fecha (fecha_intento),
    INDEX idx_sospechoso (es_sospechoso),
    FOREIGN KEY (id_usuario) REFERENCES usuarios_acceso(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SECCIÓN: NOTIFICACIONES
-- =====================================================

-- =====================================================
-- TABLA: notificaciones
-- Descripción: Alertas y notificaciones del sistema
-- =====================================================
CREATE TABLE notificaciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_usuario_destino INT UNSIGNED NULL COMMENT 'NULL = notificación global',

    -- Tipo y contenido
    tipo ENUM('info', 'exito', 'advertencia', 'error', 'urgente') NOT NULL DEFAULT 'info',
    categoria VARCHAR(50) NULL COMMENT 'venta, compra, pago, vencimiento, etc',

    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    datos_adicionales JSON NULL COMMENT 'Contexto, enlaces, etc',

    -- Referencia a documento
    tipo_documento VARCHAR(50) NULL,
    id_documento INT UNSIGNED NULL,

    -- Canal de envío
    enviar_email TINYINT(1) DEFAULT 0,
    email_enviado TINYINT(1) DEFAULT 0,
    fecha_email TIMESTAMP NULL,

    enviar_sms TINYINT(1) DEFAULT 0,
    sms_enviado TINYINT(1) DEFAULT 0,
    fecha_sms TIMESTAMP NULL,

    enviar_push TINYINT(1) DEFAULT 1,
    push_enviado TINYINT(1) DEFAULT 0,
    fecha_push TIMESTAMP NULL,

    -- Estado
    leida TINYINT(1) DEFAULT 0,
    fecha_lectura TIMESTAMP NULL,
    archivada TINYINT(1) DEFAULT 0,

    -- Programación
    programada TINYINT(1) DEFAULT 0,
    fecha_envio_programada TIMESTAMP NULL,

    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_usuario (id_usuario_destino),
    INDEX idx_tipo (tipo),
    INDEX idx_categoria (categoria),
    INDEX idx_leida (leida),
    INDEX idx_fecha (fecha_creacion),
    INDEX idx_programada (programada, fecha_envio_programada),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_destino) REFERENCES usuarios_acceso(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: plantillas_notificaciones
-- Descripción: Plantillas para emails, SMS y notificaciones push
-- =====================================================
CREATE TABLE plantillas_notificaciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,

    codigo VARCHAR(50) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,

    -- Tipo de plantilla
    tipo ENUM('email', 'sms', 'push', 'sistema') NOT NULL,
    categoria VARCHAR(50) NULL COMMENT 'factura, pago, bienvenida, etc',

    -- Contenido (con variables {{variable}})
    asunto VARCHAR(255) NULL COMMENT 'Para emails',
    cuerpo TEXT NOT NULL,
    cuerpo_html TEXT NULL COMMENT 'Para emails',

    -- Variables disponibles
    variables_disponibles JSON NULL COMMENT 'Lista de variables que se pueden usar',

    -- Configuración
    activo TINYINT(1) DEFAULT 1,
    es_sistema TINYINT(1) DEFAULT 0 COMMENT 'Plantilla del sistema, no editable',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_codigo (codigo),
    INDEX idx_tipo (tipo),
    INDEX idx_categoria (categoria),
    INDEX idx_activo (activo),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: colas_procesamiento
-- Descripción: Cola de tareas asíncronas (jobs queue)
-- =====================================================
CREATE TABLE colas_procesamiento (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,

    -- Tipo de tarea
    tipo_tarea VARCHAR(100) NOT NULL COMMENT 'enviar_email, generar_reporte, sincronizar_dte, etc',
    nombre_clase VARCHAR(255) NULL COMMENT 'Clase PHP que procesa la tarea',

    -- Datos de la tarea
    payload JSON NOT NULL COMMENT 'Datos necesarios para ejecutar la tarea',
    prioridad INT DEFAULT 0 COMMENT 'Mayor = más prioritario',

    -- Estado
    estado ENUM('pendiente', 'procesando', 'completado', 'fallido', 'cancelado') NOT NULL DEFAULT 'pendiente',
    intentos INT DEFAULT 0,
    max_intentos INT DEFAULT 3,

    -- Resultado
    resultado JSON NULL,
    mensaje_error TEXT NULL,

    -- Programación
    disponible_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Cuándo puede ejecutarse',
    fecha_inicio TIMESTAMP NULL,
    fecha_fin TIMESTAMP NULL,

    -- Worker que procesó
    worker_id VARCHAR(100) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_tipo (tipo_tarea),
    INDEX idx_estado (estado),
    INDEX idx_prioridad (prioridad),
    INDEX idx_disponible (disponible_en, estado),
    INDEX idx_worker (worker_id),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SECCIÓN: INTEGRACIONES ESPECÍFICAS
-- =====================================================

-- =====================================================
-- TABLA: integraciones_sii
-- Descripción: Configuración específica integración SII Chile
-- =====================================================
CREATE TABLE integraciones_sii (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,

    -- Credenciales SII
    rut_empresa VARCHAR(20) NOT NULL,
    rut_certificado VARCHAR(20) NOT NULL,
    password_certificado TEXT NULL COMMENT 'Encriptado',

    -- Certificado digital
    certificado_pfx MEDIUMBLOB NULL,
    certificado_fecha_vencimiento DATE NULL,

    -- Configuración
    ambiente ENUM('certificacion', 'produccion') DEFAULT 'certificacion',
    url_envio VARCHAR(255) NULL,
    url_consulta VARCHAR(255) NULL,

    -- Folios disponibles por tipo
    folios_disponibles JSON NULL COMMENT '{33: {desde: 1, hasta: 100}, 39: {...}}',

    -- Estado
    activo TINYINT(1) DEFAULT 1,
    ultima_sincronizacion TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_rut (rut_empresa),
    INDEX idx_ambiente (ambiente),
    UNIQUE KEY uk_empresa (id_empresa),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: integraciones_bancarias
-- Descripción: Configuración de APIs bancarias
-- =====================================================
CREATE TABLE integraciones_bancarias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_banco INT UNSIGNED NOT NULL,
    id_cuenta_bancaria INT UNSIGNED NOT NULL,

    -- Credenciales API
    api_key TEXT NULL COMMENT 'Encriptado',
    api_secret TEXT NULL COMMENT 'Encriptado',
    token_acceso TEXT NULL,
    token_refresh TEXT NULL,
    fecha_expiracion_token TIMESTAMP NULL,

    -- Endpoints
    url_api VARCHAR(255) NULL,
    version_api VARCHAR(20) NULL,

    -- Configuración
    sincronizacion_automatica TINYINT(1) DEFAULT 0,
    frecuencia_sincronizacion ENUM('manual', 'horaria', 'diaria', 'semanal') DEFAULT 'diaria',
    ultima_sincronizacion TIMESTAMP NULL,

    -- Permisos
    permisos JSON NULL COMMENT 'lectura_saldos, lectura_movimientos, pagos, etc',

    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_banco (id_banco),
    INDEX idx_cuenta (id_cuenta_bancaria),
    INDEX idx_activo (activo),
    UNIQUE KEY uk_empresa_cuenta (id_empresa, id_cuenta_bancaria),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_banco) REFERENCES bancos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_cuenta_bancaria) REFERENCES cuentas_bancarias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: integraciones_previred
-- Descripción: Configuración integración Previred Chile
-- =====================================================
CREATE TABLE integraciones_previred (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,

    -- Credenciales
    rut_empleador VARCHAR(20) NOT NULL,
    usuario_previred VARCHAR(100) NULL,
    password_previred TEXT NULL COMMENT 'Encriptado',

    -- Configuración
    ambiente ENUM('certificacion', 'produccion') DEFAULT 'certificacion',

    -- AFP e Isapres configuradas
    afp_codigo VARCHAR(10) NULL,
    isapre_codigo VARCHAR(10) NULL,

    -- Generación automática
    generar_automatico TINYINT(1) DEFAULT 0,
    dia_generacion INT NULL COMMENT 'Día del mes para generar REM',

    -- Estado
    activo TINYINT(1) DEFAULT 1,
    ultimo_envio TIMESTAMP NULL,
    ultimo_periodo_mes INT NULL,
    ultimo_periodo_ano INT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_rut (rut_empleador),
    INDEX idx_ambiente (ambiente),
    UNIQUE KEY uk_empresa (id_empresa),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SECCIÓN: GESTIÓN DOCUMENTAL
-- =====================================================

-- =====================================================
-- TABLA: documentos_adjuntos
-- Descripción: Archivos adjuntos a cualquier documento
-- =====================================================
CREATE TABLE documentos_adjuntos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,

    -- Referencia al documento padre
    tipo_documento VARCHAR(50) NOT NULL COMMENT 'factura, cotizacion, contrato, etc',
    id_documento INT UNSIGNED NOT NULL,

    -- Información del archivo
    nombre_original VARCHAR(255) NOT NULL,
    nombre_almacenado VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,

    tipo_mime VARCHAR(100) NULL,
    extension VARCHAR(10) NULL,
    tamano_bytes BIGINT UNSIGNED NULL,

    -- Categoría
    categoria ENUM('contrato', 'factura', 'comprobante', 'imagen', 'certificado', 'otro') DEFAULT 'otro',
    descripcion TEXT NULL,

    -- Metadata
    hash_archivo VARCHAR(64) NULL COMMENT 'SHA256 para integridad',

    -- Control
    id_usuario_subida INT UNSIGNED NULL,
    es_publico TINYINT(1) DEFAULT 0,
    requiere_autenticacion TINYINT(1) DEFAULT 1,

    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_documento (tipo_documento, id_documento),
    INDEX idx_categoria (categoria),
    INDEX idx_usuario (id_usuario_subida),
    INDEX idx_fecha (fecha_subida),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_subida) REFERENCES usuarios_acceso(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: versiones_documentos
-- Descripción: Control de versiones de documentos adjuntos
-- =====================================================
CREATE TABLE versiones_documentos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_documento_adjunto INT UNSIGNED NOT NULL,

    version INT UNSIGNED NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    tamano_bytes BIGINT UNSIGNED NULL,

    -- Cambios
    comentario_version TEXT NULL,
    hash_archivo VARCHAR(64) NULL,

    es_version_actual TINYINT(1) DEFAULT 0,

    id_usuario_subida INT UNSIGNED NULL,
    fecha_version TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_documento (id_documento_adjunto),
    INDEX idx_version (version),
    INDEX idx_actual (es_version_actual),
    INDEX idx_usuario (id_usuario_subida),
    INDEX idx_fecha (fecha_version),
    UNIQUE KEY uk_documento_version (id_documento_adjunto, version),
    FOREIGN KEY (id_documento_adjunto) REFERENCES documentos_adjuntos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_subida) REFERENCES usuarios_acceso(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: firmas_digitales
-- Descripción: Firmas electrónicas en documentos
-- =====================================================
CREATE TABLE firmas_digitales (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,

    -- Documento firmado
    tipo_documento VARCHAR(50) NOT NULL,
    id_documento INT UNSIGNED NOT NULL,
    id_documento_adjunto INT UNSIGNED NULL,

    -- Firmante
    id_usuario_firma INT UNSIGNED NULL,
    nombre_firmante VARCHAR(200) NOT NULL,
    email_firmante VARCHAR(150) NOT NULL,
    rut_firmante VARCHAR(20) NULL,

    -- Firma
    tipo_firma ENUM('simple', 'avanzada', 'certificada') NOT NULL DEFAULT 'simple',
    metodo ENUM('password', 'otp', 'certificado_digital', 'biometrico') NOT NULL,

    firma_base64 TEXT NULL COMMENT 'Imagen de la firma manuscrita',
    certificado_digital TEXT NULL,
    hash_documento VARCHAR(64) NOT NULL COMMENT 'Hash del documento al momento de firmar',

    -- Verificación
    ip_firmante VARCHAR(50) NULL,
    geolocalizacion VARCHAR(200) NULL,
    user_agent TEXT NULL,

    -- Estado
    estado ENUM('pendiente', 'firmado', 'rechazado', 'expirado') DEFAULT 'pendiente',
    valida TINYINT(1) DEFAULT 1,

    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_firma TIMESTAMP NULL,
    fecha_expiracion TIMESTAMP NULL,

    observaciones TEXT NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_documento (tipo_documento, id_documento),
    INDEX idx_usuario (id_usuario_firma),
    INDEX idx_email (email_firmante),
    INDEX idx_estado (estado),
    INDEX idx_valida (valida),
    INDEX idx_fecha_firma (fecha_firma),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_firma) REFERENCES usuarios_acceso(id) ON DELETE SET NULL,
    FOREIGN KEY (id_documento_adjunto) REFERENCES documentos_adjuntos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SECCIÓN: WORKFLOW Y APROBACIONES
-- =====================================================

-- =====================================================
-- TABLA: flujos_trabajo
-- Descripción: Definición de flujos de aprobación
-- =====================================================
CREATE TABLE flujos_trabajo (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,

    codigo VARCHAR(50) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,

    -- Aplicable a
    tipo_documento VARCHAR(50) NOT NULL COMMENT 'cotizacion, pedido, pago, etc',
    condiciones JSON NULL COMMENT 'Condiciones para activar el flujo',

    -- Configuración
    requiere_todas_aprobaciones TINYINT(1) DEFAULT 1 COMMENT 'Todas o al menos una',
    permite_rechazar TINYINT(1) DEFAULT 1,
    permite_delegar TINYINT(1) DEFAULT 0,

    -- Notificaciones
    notificar_aprobadores TINYINT(1) DEFAULT 1,
    notificar_solicitante TINYINT(1) DEFAULT 1,

    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_codigo (codigo),
    INDEX idx_tipo (tipo_documento),
    INDEX idx_activo (activo),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: pasos_flujo
-- Descripción: Pasos/etapas de cada flujo
-- =====================================================
CREATE TABLE pasos_flujo (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_flujo INT UNSIGNED NOT NULL,

    orden INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,

    -- Aprobadores
    tipo_aprobador ENUM('usuario', 'rol', 'departamento', 'dinamico') NOT NULL,
    id_aprobador INT UNSIGNED NULL COMMENT 'ID según tipo',
    regla_aprobador JSON NULL COMMENT 'Para aprobadores dinámicos',

    -- Configuración del paso
    es_opcional TINYINT(1) DEFAULT 0,
    tiempo_limite_horas INT NULL,

    -- Acciones
    puede_aprobar TINYINT(1) DEFAULT 1,
    puede_rechazar TINYINT(1) DEFAULT 1,
    puede_devolver TINYINT(1) DEFAULT 0,
    puede_comentar TINYINT(1) DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_flujo (id_flujo),
    INDEX idx_orden (orden),
    INDEX idx_tipo_aprobador (tipo_aprobador, id_aprobador),
    FOREIGN KEY (id_flujo) REFERENCES flujos_trabajo(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: aprobaciones_pendientes
-- Descripción: Instancias de aprobación en curso
-- =====================================================
CREATE TABLE aprobaciones_pendientes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_flujo INT UNSIGNED NOT NULL,
    id_paso_actual INT UNSIGNED NULL,

    -- Documento sujeto a aprobación
    tipo_documento VARCHAR(50) NOT NULL,
    id_documento INT UNSIGNED NOT NULL,
    numero_documento VARCHAR(50) NULL,

    -- Solicitante
    id_usuario_solicita INT UNSIGNED NULL,
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    comentario_solicitud TEXT NULL,

    -- Estado general
    estado ENUM('pendiente', 'en_revision', 'aprobado', 'rechazado', 'cancelado', 'expirado') DEFAULT 'pendiente',
    paso_actual INT DEFAULT 1,
    total_pasos INT NOT NULL,

    -- Aprobador actual
    id_usuario_aprobador_actual INT UNSIGNED NULL,
    fecha_limite TIMESTAMP NULL,

    -- Resultado
    fecha_resolucion TIMESTAMP NULL,
    resuelto_por INT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_flujo (id_flujo),
    INDEX idx_documento (tipo_documento, id_documento),
    INDEX idx_estado (estado),
    INDEX idx_aprobador_actual (id_usuario_aprobador_actual),
    INDEX idx_fecha_limite (fecha_limite),
    INDEX idx_solicita (id_usuario_solicita),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_flujo) REFERENCES flujos_trabajo(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_paso_actual) REFERENCES pasos_flujo(id) ON DELETE SET NULL,
    FOREIGN KEY (id_usuario_solicita) REFERENCES usuarios_acceso(id) ON DELETE SET NULL,
    FOREIGN KEY (id_usuario_aprobador_actual) REFERENCES usuarios_acceso(id) ON DELETE SET NULL,
    FOREIGN KEY (resuelto_por) REFERENCES usuarios_acceso(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: aprobaciones_historial
-- Descripción: Historial de acciones en aprobaciones
-- =====================================================
CREATE TABLE aprobaciones_historial (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_aprobacion INT UNSIGNED NOT NULL,
    id_paso INT UNSIGNED NULL,

    id_usuario INT UNSIGNED NOT NULL,
    accion ENUM('aprobar', 'rechazar', 'devolver', 'comentar', 'delegar', 'cancelar') NOT NULL,

    comentario TEXT NULL,
    id_usuario_delegado INT UNSIGNED NULL COMMENT 'Si delegó a alguien',

    fecha_accion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_aprobacion (id_aprobacion),
    INDEX idx_paso (id_paso),
    INDEX idx_usuario (id_usuario),
    INDEX idx_accion (accion),
    INDEX idx_fecha (fecha_accion),
    FOREIGN KEY (id_aprobacion) REFERENCES aprobaciones_pendientes(id) ON DELETE CASCADE,
    FOREIGN KEY (id_paso) REFERENCES pasos_flujo(id) ON DELETE SET NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios_acceso(id) ON DELETE SET NULL,
    FOREIGN KEY (id_usuario_delegado) REFERENCES usuarios_acceso(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
