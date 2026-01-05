-- =====================================================
-- CONECTA ERP - ECOMMERCE
-- Archivo: 15_ecommerce.sql
-- Descripción: Tienda online, carritos, pedidos web
-- Versión: 1.0
-- =====================================================

-- =====================================================
-- TABLA: carritos_compra
-- Descripción: Carritos de compra online
-- =====================================================
CREATE TABLE carritos_compra (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    id_cliente INT UNSIGNED NULL,
    sesion_id VARCHAR(100) NULL,

    total DECIMAL(15,4) DEFAULT 0,
    cantidad_items INT DEFAULT 0,

    estado ENUM('activo', 'abandonado', 'convertido') DEFAULT 'activo',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_cliente (id_cliente),
    INDEX idx_sesion (sesion_id),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_cliente) REFERENCES entidades(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: carritos_detalle
-- Descripción: Items del carrito
-- =====================================================
CREATE TABLE carritos_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_carrito INT UNSIGNED NOT NULL,
    id_producto INT UNSIGNED NOT NULL,

    cantidad DECIMAL(15,4) NOT NULL,
    precio_unitario DECIMAL(15,4) NOT NULL,
    total DECIMAL(15,4) AS (cantidad * precio_unitario) STORED,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_carrito (id_carrito),
    INDEX idx_producto (id_producto),
    FOREIGN KEY (id_carrito) REFERENCES carritos_compra(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: pedidos_web
-- Descripción: Pedidos realizados por web
-- =====================================================
CREATE TABLE pedidos_web (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT UNSIGNED NOT NULL,
    numero_pedido VARCHAR(50) NOT NULL,
    id_cliente INT UNSIGNED NULL,
    id_carrito INT UNSIGNED NULL,

    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    subtotal DECIMAL(15,4) DEFAULT 0,
    descuento DECIMAL(15,4) DEFAULT 0,
    envio DECIMAL(15,4) DEFAULT 0,
    impuestos DECIMAL(15,4) DEFAULT 0,
    total DECIMAL(15,4) DEFAULT 0,

    estado ENUM('pendiente', 'pagado', 'preparando', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',

    direccion_envio TEXT NULL,
    metodo_pago VARCHAR(50) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_empresa (id_empresa),
    INDEX idx_numero (numero_pedido),
    INDEX idx_cliente (id_cliente),
    INDEX idx_estado (estado),
    UNIQUE KEY uk_empresa_numero (id_empresa, numero_pedido),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_cliente) REFERENCES entidades(id) ON DELETE SET NULL,
    FOREIGN KEY (id_carrito) REFERENCES carritos_compra(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FIN DEL ARCHIVO
-- =====================================================
