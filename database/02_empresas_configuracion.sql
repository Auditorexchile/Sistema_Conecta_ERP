-- =====================================================
-- Conecta ERP - Esquema de Empresas y Configuración
-- Archivo: 02_empresas_configuracion.sql
-- Descripción: Empresas, representantes, sucursales, configuración multipaís
-- =====================================================

-- Tabla: empresas
CREATE TABLE IF NOT EXISTS empresas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_pais CHAR(2) NOT NULL COMMENT 'CL, AR, PE, CO, etc',
    identificador_tributario VARCHAR(50) NOT NULL COMMENT 'RUT, CUIT, NIT, RFC, etc',
    tipo_identificador VARCHAR(20) NOT NULL COMMENT 'RUT, CUIT, NIT, etc',
    digito_verificador CHAR(1) NULL,
    razon_social VARCHAR(255) NOT NULL,
    nombre_fantasia VARCHAR(255) NULL,
    giro_actividad VARCHAR(255) NULL,
    actividad_economica_principal VARCHAR(255) NULL,
    actividad_economica_secundaria VARCHAR(255) NULL,
    tipo_empresa ENUM('empresa', 'persona_natural', 'profesional_independiente', 'ong', 'fundacion') DEFAULT 'empresa',
    tipo_contribuyente VARCHAR(100) NULL,
    regimen_tributario VARCHAR(100) NULL,
    fecha_inicio_actividades DATE NULL,
    email_empresa VARCHAR(255) NOT NULL,
    telefono VARCHAR(50) NULL,
    telefono_movil VARCHAR(50) NULL,
    sitio_web VARCHAR(255) NULL,
    logo_path VARCHAR(255) NULL,
    estado ENUM('trial', 'activo', 'suspendido', 'bloqueado', 'cancelado') DEFAULT 'trial',
    fecha_registro DATETIME NOT NULL,
    fecha_activacion DATETIME NULL,
    fecha_trial_inicio DATETIME NULL,
    fecha_trial_fin DATETIME NULL,
    dias_trial_restantes INT DEFAULT 14,
    id_plan INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    deleted_by INT UNSIGNED NULL,
    UNIQUE KEY uk_identificador_pais (identificador_tributario, codigo_pais),
    INDEX idx_codigo_pais (codigo_pais),
    INDEX idx_estado (estado),
    INDEX idx_plan (id_plan),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: empresas_direcciones
CREATE TABLE IF NOT EXISTS empresas_direcciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    tipo_direccion ENUM('legal', 'comercial', 'bodega', 'despacho', 'facturacion') DEFAULT 'comercial',
    direccion VARCHAR(255) NOT NULL,
    numero VARCHAR(20) NULL,
    oficina VARCHAR(50) NULL,
    codigo_postal VARCHAR(20) NULL,
    pais CHAR(2) NOT NULL,
    region_estado VARCHAR(100) NULL,
    provincia VARCHAR(100) NULL,
    comuna_ciudad VARCHAR(100) NULL,
    latitud DECIMAL(10, 8) NULL,
    longitud DECIMAL(11, 8) NULL,
    principal TINYINT(1) DEFAULT 0,
    activa TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    INDEX idx_empresa (id_empresa),
    INDEX idx_tipo (tipo_direccion),
    INDEX idx_principal (principal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: representantes_legales
CREATE TABLE IF NOT EXISTS representantes_legales (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NULL COMMENT 'FK a usuarios_acceso',
    nombres VARCHAR(255) NOT NULL,
    apellido_paterno VARCHAR(255) NOT NULL,
    apellido_materno VARCHAR(255) NULL,
    identificador_personal VARCHAR(50) NOT NULL,
    tipo_identificador VARCHAR(20) NOT NULL,
    pais_nacionalidad CHAR(2) NOT NULL,
    fecha_nacimiento DATE NULL,
    email VARCHAR(255) NOT NULL,
    telefono VARCHAR(50) NULL,
    telefono_movil VARCHAR(50) NULL,
    sexo ENUM('M', 'F', 'Otro', 'Prefiero no decir') NULL,
    estado_civil ENUM('Soltero', 'Casado', 'Divorciado', 'Viudo', 'Unión civil') NULL,
    cargo VARCHAR(100) DEFAULT 'Representante Legal',
    fecha_nombramiento DATE NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    INDEX idx_empresa (id_empresa),
    INDEX idx_usuario (id_usuario),
    INDEX idx_email (email),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: sucursales
CREATE TABLE IF NOT EXISTS sucursales (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    codigo VARCHAR(50) NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    comuna_ciudad VARCHAR(100) NULL,
    region_estado VARCHAR(100) NULL,
    pais CHAR(2) NOT NULL,
    telefono VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    id_responsable INT UNSIGNED NULL,
    id_centro_costo INT UNSIGNED NULL,
    es_principal TINYINT(1) DEFAULT 0,
    activa TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo),
    INDEX idx_empresa (id_empresa),
    INDEX idx_principal (es_principal),
    INDEX idx_activa (activa),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: configuracion_empresa
CREATE TABLE IF NOT EXISTS configuracion_empresa (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    codigo_pais CHAR(2) NOT NULL,
    moneda_base CHAR(3) NOT NULL COMMENT 'CLP, USD, EUR, etc',
    monedas_secundarias TEXT NULL COMMENT 'JSON array de monedas adicionales',
    idioma_principal CHAR(2) NOT NULL DEFAULT 'es',
    idiomas_adicionales TEXT NULL COMMENT 'JSON array de idiomas',
    zona_horaria VARCHAR(50) NOT NULL DEFAULT 'America/Santiago',
    formato_fecha VARCHAR(20) DEFAULT 'd/m/Y',
    formato_hora VARCHAR(20) DEFAULT 'H:i',
    separador_decimal CHAR(1) DEFAULT ',',
    separador_miles CHAR(1) DEFAULT '.',
    decimales_moneda INT DEFAULT 0,
    decimales_cantidad INT DEFAULT 2,
    tax_id_format VARCHAR(20) NULL,
    tax_rate DECIMAL(5, 2) NULL,
    logo_empresa VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    UNIQUE KEY uk_empresa (id_empresa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: monedas
CREATE TABLE IF NOT EXISTS monedas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo CHAR(3) NOT NULL UNIQUE COMMENT 'CLP, USD, EUR',
    nombre VARCHAR(100) NOT NULL,
    simbolo VARCHAR(10) NOT NULL,
    decimales INT DEFAULT 2,
    separador_decimal CHAR(1) DEFAULT '.',
    separador_miles CHAR(1) DEFAULT ',',
    posicion_simbolo ENUM('before', 'after') DEFAULT 'before',
    paises TEXT NULL COMMENT 'JSON array',
    activa TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activa (activa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar monedas principales
INSERT INTO monedas (codigo, nombre, simbolo, decimales, separador_decimal, separador_miles, posicion_simbolo, paises) VALUES
('CLP', 'Peso Chileno', '$', 0, ',', '.', 'before', '["CL"]'),
('USD', 'Dólar Estadounidense', '$', 2, '.', ',', 'before', '["US"]'),
('EUR', 'Euro', '€', 2, ',', '.', 'after', '["ES","FR","DE","IT"]'),
('ARS', 'Peso Argentino', '$', 2, ',', '.', 'before', '["AR"]'),
('PEN', 'Sol Peruano', 'S/', 2, '.', ',', 'before', '["PE"]'),
('COP', 'Peso Colombiano', '$', 0, ',', '.', 'before', '["CO"]'),
('MXN', 'Peso Mexicano', '$', 2, '.', ',', 'before', '["MX"]'),
('BRL', 'Real Brasileño', 'R$', 2, ',', '.', 'before', '["BR"]'),
('GBP', 'Libra Esterlina', '£', 2, '.', ',', 'before', '["GB"]');

-- Tabla: tipos_cambio
CREATE TABLE IF NOT EXISTS tipos_cambio (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NULL COMMENT 'NULL = tipo cambio global',
    moneda_origen CHAR(3) NOT NULL,
    moneda_destino CHAR(3) NOT NULL,
    valor DECIMAL(18, 6) NOT NULL,
    fecha_vigencia DATE NOT NULL,
    tipo ENUM('manual', 'automatico', 'oficial') DEFAULT 'manual',
    fuente VARCHAR(255) NULL COMMENT 'API, Banco Central, etc',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NULL,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    INDEX idx_empresa (id_empresa),
    INDEX idx_monedas (moneda_origen, moneda_destino),
    INDEX idx_fecha (fecha_vigencia),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: paises
CREATE TABLE IF NOT EXISTS paises (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo CHAR(2) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    nombre_nativo VARCHAR(100) NULL,
    tipo_identificador VARCHAR(50) NULL,
    formato_identificador VARCHAR(100) NULL,
    moneda_principal CHAR(3) NULL,
    zona_horaria_principal VARCHAR(50) NULL,
    codigo_telefono VARCHAR(10) NULL,
    locale_principal VARCHAR(10) NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar países principales
INSERT INTO paises (codigo, nombre, tipo_identificador, formato_identificador, moneda_principal, zona_horaria_principal, codigo_telefono, locale_principal) VALUES
('CL', 'Chile', 'RUT', 'XX.XXX.XXX-X', 'CLP', 'America/Santiago', '+56', 'es_CL'),
('AR', 'Argentina', 'CUIT', 'XX-XXXXXXXX-X', 'ARS', 'America/Buenos_Aires', '+54', 'es_AR'),
('PE', 'Perú', 'RUC', 'XXXXXXXXXXX', 'PEN', 'America/Lima', '+51', 'es_PE'),
('CO', 'Colombia', 'NIT', 'XXXXXXXX-X', 'COP', 'America/Bogota', '+57', 'es_CO'),
('MX', 'México', 'RFC', 'XXXX000000XXX', 'MXN', 'America/Mexico_City', '+52', 'es_MX'),
('BR', 'Brasil', 'CNPJ', 'XX.XXX.XXX/0001-XX', 'BRL', 'America/Sao_Paulo', '+55', 'pt_BR'),
('US', 'Estados Unidos', 'EIN', 'XX-XXXXXXX', 'USD', 'America/New_York', '+1', 'en_US'),
('ES', 'España', 'CIF/NIF', 'X0000000X', 'EUR', 'Europe/Madrid', '+34', 'es_ES'),
('FR', 'Francia', 'SIRET', 'XXXXXXXXXXXXXX', 'EUR', 'Europe/Paris', '+33', 'fr_FR'),
('DE', 'Alemania', 'USt-IdNr', 'DEXXXXXXXXX', 'EUR', 'Europe/Berlin', '+49', 'de_DE'),
('IT', 'Italia', 'Partita IVA', 'XXXXXXXXXXX', 'EUR', 'Europe/Rome', '+39', 'it_IT'),
('GB', 'Reino Unido', 'VAT Number', 'GBXXXXXXXXX', 'GBP', 'Europe/London', '+44', 'en_GB');

-- Tabla: idiomas
CREATE TABLE IF NOT EXISTS idiomas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo CHAR(2) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    nombre_nativo VARCHAR(100) NOT NULL,
    locale VARCHAR(10) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    por_defecto TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar idiomas soportados
INSERT INTO idiomas (codigo, nombre, nombre_nativo, locale, activo, por_defecto) VALUES
('es', 'Español', 'Español', 'es_ES', 1, 1),
('en', 'English', 'English', 'en_US', 1, 0),
('pt', 'Português', 'Português', 'pt_BR', 1, 0),
('fr', 'Français', 'Français', 'fr_FR', 1, 0),
('de', 'Deutsch', 'Deutsch', 'de_DE', 1, 0),
('it', 'Italiano', 'Italiano', 'it_IT', 1, 0),
('zh', 'Chinese', '中文', 'zh_CN', 1, 0),
('ja', 'Japanese', '日本語', 'ja_JP', 1, 0),
('ko', 'Korean', '한국어', 'ko_KR', 1, 0),
('hi', 'Hindi', 'हिन्दी', 'hi_IN', 1, 0);

-- =====================================================
-- FIN DEL ESQUEMA 02_empresas_configuracion.sql
-- =====================================================
