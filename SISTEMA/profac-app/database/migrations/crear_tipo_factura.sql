-- =====================================================
-- SCRIPT SQL: Crear tabla tipo_factura y relación con factura
-- Fecha: 2026-04-08
-- Descripción: 
--   Se crea la tabla `tipo_factura` que almacena la configuración
--   de cada tipo de facturación del sistema. Cada registro define
--   restricciones de precio, descuentos máximos, si requiere código
--   de autorización, exoneración, orden de compra, etc.
--   
--   Se agrega la columna `tipo_factura_id` a la tabla `factura` para
--   relacionar cada factura con su tipo específico.
--
--   Las URLs/endpoints se definen en el código (JS/blade), no en la DB.
--   Los nombres deben coincidir con sub_menu.nombre del menú dinámico.
-- =====================================================

-- 1. Crear tabla tipo_factura
CREATE TABLE IF NOT EXISTS `tipo_factura` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL COMMENT 'Nombre visible (debe coincidir con sub_menu.nombre)',
    `codigo` VARCHAR(50) NOT NULL COMMENT 'Identificador interno único (ej: estatal, corporativa)',
    `ruta_menu` VARCHAR(255) DEFAULT NULL COMMENT 'URL del menú (sin / inicial), coincide con sub_menu.url',
    `tipo_venta_id` TINYINT UNSIGNED NOT NULL COMMENT '1=Corporativo, 2=Estatal, 3=Exonerado',
    `restriccion` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '1=Con restricción, 2=Sin restricción',
    `max_descuento` INT UNSIGNED NOT NULL DEFAULT 50 COMMENT 'Descuento máximo permitido %',
    `requiere_codigo_autorizacion` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Si requiere modal de código',
    `requiere_codigo_exoneracion` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Si requiere código exoneración',
    `requiere_orden_compra` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Si muestra campo orden compra',
    `aplica_isv` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Si se calcula ISV',
    `multiples_precios` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Si muestra A/B/C/D precios',
    `comision_fija` DECIMAL(5,2) DEFAULT NULL COMMENT 'Comisión fija %. NULL = cálculo estándar',
    `estado` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Activo/Inactivo',
    `orden` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Orden de aparición en selector',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `tipo_factura_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Insertar los tipos de facturación
INSERT INTO `tipo_factura` 
(`nombre`, `codigo`, `ruta_menu`, `tipo_venta_id`, `restriccion`, `max_descuento`, 
 `requiere_codigo_autorizacion`, `requiere_codigo_exoneracion`, `requiere_orden_compra`,
 `aplica_isv`, `multiples_precios`, `comision_fija`, `estado`, `orden`)
VALUES
-- Facturación Clientes A (por defecto seleccionada)
('Facturación Clientes A', 'estatal', 'ventas/estatal', 2, 1, 50, 
 0, 0, 1, 1, 0, NULL, 1, 1),

-- Facturación SR/Clientes A
('Facturación SR/Clientes A', 'sin_restriccion_gobierno', 'ventas/sin/restriccion/gobierno', 2, 2, 35,
 1, 0, 0, 1, 1, NULL, 1, 2),

-- Facturación Clientes B
('Facturación Clientes B', 'corporativa', 'ventas/coporativo', 1, 1, 50,
 0, 0, 1, 1, 0, NULL, 1, 3),

-- Facturación SR/P Clientes B
('Facturación SR/P Clientes B', 'sin_restriccion_precio', 'ventas/sin/restriccion/precio', 1, 2, 35,
 1, 0, 0, 1, 1, NULL, 1, 4),

-- Facturación Exonerada
('Facturación Exonerada', 'exoneradas', 'ventas/exonerado/factura', 3, 1, 0,
 0, 1, 0, 0, 0, 0.50, 1, 5),

-- Cotización Clientes A
('Cotización Clientes A', 'cotizacion_clientes_a', 'proforma/cotizacion/2', 2, 1, 50,
 0, 0, 0, 1, 0, NULL, 1, 6);

-- 3. Agregar columna tipo_factura_id a tabla factura
ALTER TABLE `factura` 
ADD COLUMN `tipo_factura_id` BIGINT UNSIGNED NULL DEFAULT NULL 
COMMENT 'FK a tipo_factura' AFTER `tipo_venta_id`;
