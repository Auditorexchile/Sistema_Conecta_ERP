-- =====================================================
-- CONECTA ERP - MÓDULO PRODUCTOS Y SERVICIOS
-- Archivo: 04_productos_servicios.sql
-- Descripción: Maestros de productos, servicios, categorías, precios
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: categorias_productos
-- Descripción: Categorización jerárquica de productos
-- =====================================================
CREATE TABLE categorias_productos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_categoria_padre INT UNSIGNED NULL,
    codigo VARCHAR(20) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    nivel INT NOT NULL DEFAULT 1,
    ruta VARCHAR(500) NULL COMMENT 'Path jerárquico: /padre/hijo',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    orden INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_padre (id_categoria_padre),
    INDEX idx_codigo (codigo),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_categoria_padre) REFERENCES categorias_productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: marcas
-- Descripción: Marcas de productos
-- =====================================================
CREATE TABLE marcas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    codigo VARCHAR(20) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    sitio_web VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: unidades_medida
-- Descripción: Unidades de medida (KG, UN, LT, etc)
-- =====================================================
CREATE TABLE unidades_medida (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    simbolo VARCHAR(10) NOT NULL,
    tipo ENUM('peso', 'volumen', 'longitud', 'superficie', 'unidad', 'tiempo', 'otro') NOT NULL DEFAULT 'unidad',
    factor_conversion DECIMAL(15,6) NULL COMMENT 'Factor para conversión a unidad base',
    unidad_base_id INT UNSIGNED NULL COMMENT 'Referencia a unidad base para conversión',
    decimales TINYINT NOT NULL DEFAULT 2,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_codigo (codigo),
    INDEX idx_tipo (tipo),
    FOREIGN KEY (unidad_base_id) REFERENCES unidades_medida(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos iniciales de unidades de medida
INSERT INTO unidades_medida (codigo, nombre, simbolo, tipo, decimales) VALUES
('UN', 'Unidad', 'UN', 'unidad', 0),
('KG', 'Kilogramo', 'kg', 'peso', 3),
('GR', 'Gramo', 'g', 'peso', 2),
('LT', 'Litro', 'L', 'volumen', 2),
('ML', 'Mililitro', 'ml', 'volumen', 0),
('MT', 'Metro', 'm', 'longitud', 2),
('CM', 'Centímetro', 'cm', 'longitud', 2),
('M2', 'Metro cuadrado', 'm²', 'superficie', 2),
('M3', 'Metro cúbico', 'm³', 'volumen', 3),
('PAR', 'Par', 'par', 'unidad', 0),
('DOC', 'Docena', 'doc', 'unidad', 0),
('CAJ', 'Caja', 'caj', 'unidad', 0),
('PAQ', 'Paquete', 'paq', 'unidad', 0),
('HR', 'Hora', 'h', 'tiempo', 2),
('MIN', 'Minuto', 'min', 'tiempo', 0);

-- =====================================================
-- TABLA: productos
-- Descripción: Maestro de productos y servicios
-- =====================================================
CREATE TABLE productos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_categoria INT UNSIGNED NULL,
    id_marca INT UNSIGNED NULL,
    id_unidad_medida INT UNSIGNED NOT NULL,

    -- Identificación
    codigo VARCHAR(50) NOT NULL,
    codigo_barras VARCHAR(100) NULL,
    sku VARCHAR(50) NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion_corta VARCHAR(500) NULL,
    descripcion_larga TEXT NULL,

    -- Tipo de producto
    tipo ENUM('producto', 'servicio', 'combo', 'materia_prima', 'semi_elaborado', 'terminado') NOT NULL DEFAULT 'producto',
    subtipo ENUM('stockeable', 'no_stockeable', 'servicio', 'activo_fijo') NOT NULL DEFAULT 'stockeable',

    -- Control de stock
    controla_stock TINYINT(1) NOT NULL DEFAULT 1,
    stock_minimo DECIMAL(15,4) DEFAULT 0,
    stock_maximo DECIMAL(15,4) DEFAULT 0,
    punto_reorden DECIMAL(15,4) DEFAULT 0,

    -- Control de lotes y series
    controla_lote TINYINT(1) NOT NULL DEFAULT 0,
    controla_serie TINYINT(1) NOT NULL DEFAULT 0,
    controla_vencimiento TINYINT(1) NOT NULL DEFAULT 0,
    dias_vencimiento INT NULL,

    -- Precios base
    precio_compra DECIMAL(15,4) DEFAULT 0,
    precio_venta DECIMAL(15,4) DEFAULT 0,
    precio_costo DECIMAL(15,4) DEFAULT 0,
    margen_minimo DECIMAL(5,2) DEFAULT 0 COMMENT 'Porcentaje',

    -- Información tributaria
    id_impuesto_compra INT UNSIGNED NULL,
    id_impuesto_venta INT UNSIGNED NULL,
    exento_impuesto TINYINT(1) NOT NULL DEFAULT 0,

    -- Información contable
    cuenta_ingreso VARCHAR(20) NULL,
    cuenta_costo VARCHAR(20) NULL,
    cuenta_inventario VARCHAR(20) NULL,

    -- Características físicas
    peso DECIMAL(10,4) NULL,
    volumen DECIMAL(10,4) NULL,
    largo DECIMAL(10,2) NULL,
    ancho DECIMAL(10,2) NULL,
    alto DECIMAL(10,2) NULL,

    -- Imágenes y multimedia
    imagen_principal VARCHAR(255) NULL,
    galeria_imagenes JSON NULL COMMENT 'Array de URLs',

    -- Información adicional
    notas TEXT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    visible_web TINYINT(1) NOT NULL DEFAULT 0,
    permite_venta TINYINT(1) NOT NULL DEFAULT 1,
    permite_compra TINYINT(1) NOT NULL DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_categoria (id_categoria),
    INDEX idx_marca (id_marca),
    INDEX idx_codigo (codigo),
    INDEX idx_codigo_barras (codigo_barras),
    INDEX idx_sku (sku),
    INDEX idx_tipo (tipo),
    INDEX idx_activo (activo),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_categoria) REFERENCES categorias_productos(id) ON DELETE SET NULL,
    FOREIGN KEY (id_marca) REFERENCES marcas(id) ON DELETE SET NULL,
    FOREIGN KEY (id_unidad_medida) REFERENCES unidades_medida(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: productos_precios
-- Descripción: Múltiples listas de precios por producto
-- =====================================================
CREATE TABLE productos_precios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_producto INT UNSIGNED NOT NULL,
    id_lista_precio INT UNSIGNED NOT NULL,
    id_moneda INT UNSIGNED NOT NULL,
    precio DECIMAL(15,4) NOT NULL,
    precio_oferta DECIMAL(15,4) NULL,
    fecha_inicio_oferta DATE NULL,
    fecha_fin_oferta DATE NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_producto (id_producto),
    INDEX idx_lista (id_lista_precio),
    INDEX idx_moneda (id_moneda),
    INDEX idx_fechas (fecha_inicio_oferta, fecha_fin_oferta),
    UNIQUE KEY uk_producto_lista_moneda (id_producto, id_lista_precio, id_moneda),
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_moneda) REFERENCES monedas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: listas_precios
-- Descripción: Diferentes listas de precios (retail, mayorista, etc)
-- =====================================================
CREATE TABLE listas_precios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    codigo VARCHAR(20) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    tipo ENUM('general', 'cliente', 'mayorista', 'minorista', 'especial') NOT NULL DEFAULT 'general',
    porcentaje_margen DECIMAL(5,2) NULL COMMENT 'Margen sobre precio base',
    es_default TINYINT(1) NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_empresa (id_empresa),
    INDEX idx_tipo (tipo),
    UNIQUE KEY uk_empresa_codigo (id_empresa, codigo, deleted_at),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: productos_proveedores
-- Descripción: Relación productos-proveedores con precios
-- =====================================================
CREATE TABLE productos_proveedores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_producto INT UNSIGNED NOT NULL,
    id_proveedor INT UNSIGNED NOT NULL,
    codigo_proveedor VARCHAR(50) NULL COMMENT 'Código del producto según proveedor',
    precio_compra DECIMAL(15,4) NOT NULL,
    id_moneda INT UNSIGNED NOT NULL,
    tiempo_entrega_dias INT NULL,
    cantidad_minima DECIMAL(15,4) DEFAULT 1,
    preferido TINYINT(1) NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_producto (id_producto),
    INDEX idx_proveedor (id_proveedor),
    INDEX idx_preferido (preferido),
    UNIQUE KEY uk_producto_proveedor (id_producto, id_proveedor),
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_moneda) REFERENCES monedas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: productos_atributos
-- Descripción: Atributos variables (color, talla, etc)
-- =====================================================
CREATE TABLE productos_atributos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('texto', 'numero', 'lista', 'color', 'fecha', 'booleano') NOT NULL DEFAULT 'texto',
    valores_permitidos JSON NULL COMMENT 'Array de valores para tipo lista',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: productos_atributos_valores
-- Descripción: Valores de atributos por producto
-- =====================================================
CREATE TABLE productos_atributos_valores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_producto INT UNSIGNED NOT NULL,
    id_atributo INT UNSIGNED NOT NULL,
    valor VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_producto (id_producto),
    INDEX idx_atributo (id_atributo),
    UNIQUE KEY uk_producto_atributo (id_producto, id_atributo),
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_atributo) REFERENCES productos_atributos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: productos_variantes
-- Descripción: Variantes de productos (ej: camiseta roja talla M)
-- =====================================================
CREATE TABLE productos_variantes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_producto_padre INT UNSIGNED NOT NULL,
    codigo VARCHAR(50) NOT NULL,
    sku VARCHAR(50) NULL,
    nombre_variante VARCHAR(200) NOT NULL,
    atributos JSON NOT NULL COMMENT 'Array de {atributo: valor}',
    precio_ajuste DECIMAL(15,4) DEFAULT 0 COMMENT 'Ajuste sobre precio base',
    stock_disponible DECIMAL(15,4) DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_producto_padre (id_producto_padre),
    INDEX idx_codigo (codigo),
    UNIQUE KEY uk_codigo (codigo),
    FOREIGN KEY (id_producto_padre) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: productos_combos
-- Descripción: Composición de productos tipo combo
-- =====================================================
CREATE TABLE productos_combos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_producto_combo INT UNSIGNED NOT NULL COMMENT 'El producto combo',
    id_producto_componente INT UNSIGNED NOT NULL COMMENT 'Producto que forma parte',
    cantidad DECIMAL(15,4) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_combo (id_producto_combo),
    INDEX idx_componente (id_producto_componente),
    FOREIGN KEY (id_producto_combo) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto_componente) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: servicios
-- Descripción: Información específica de servicios
-- =====================================================
CREATE TABLE servicios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_producto INT UNSIGNED NOT NULL,
    duracion_minutos INT NULL,
    requiere_agenda TINYINT(1) NOT NULL DEFAULT 0,
    max_personas INT NULL,
    incluye TEXT NULL COMMENT 'Qué incluye el servicio',
    no_incluye TEXT NULL,
    requisitos TEXT NULL,
    politicas_cancelacion TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_producto (id_producto),
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
