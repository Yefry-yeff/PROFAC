-- =====================================================
-- MÓDULO DE LOGÍSTICA DE ENTREGAS
-- Scripts de creación de tablas
-- Fecha: 2025-12-07
-- =====================================================

-- =====================================================
-- 1. EQUIPOS DE ENTREGA
-- =====================================================

-- Tabla principal de equipos de entrega
CREATE TABLE `equipos_entrega` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre_equipo` VARCHAR(100) NOT NULL COMMENT 'Nombre del equipo de entrega',
  `descripcion` TEXT NULL COMMENT 'Descripción del equipo',
  `estado_id` TINYINT NOT NULL DEFAULT 1 COMMENT '1=Activo, 2=Inactivo',
  `users_id_creador` BIGINT UNSIGNED NOT NULL COMMENT 'Usuario que creó el equipo',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_estado` (`estado_id`),
  INDEX `idx_creador` (`users_id_creador`),
  CONSTRAINT `fk_equipos_entrega_users` FOREIGN KEY (`users_id_creador`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Equipos de entrega';

-- Tabla de miembros del equipo con porcentajes de comisión
CREATE TABLE `equipos_entrega_miembros` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `equipo_entrega_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID del equipo',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Usuario miembro del equipo',
  `porcentaje_comision` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Porcentaje de comisión (0-100)',
  `estado_id` TINYINT NOT NULL DEFAULT 1 COMMENT '1=Activo, 2=Inactivo',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_equipo_user` (`equipo_entrega_id`, `user_id`),
  INDEX `idx_equipo` (`equipo_entrega_id`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_estado` (`estado_id`),
  CONSTRAINT `fk_miembros_equipo` FOREIGN KEY (`equipo_entrega_id`) REFERENCES `equipos_entrega` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_miembros_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `chk_porcentaje_valido` CHECK (`porcentaje_comision` >= 0 AND `porcentaje_comision` <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Miembros de equipos de entrega con porcentajes';

-- =====================================================
-- 2. DISTRIBUCIÓN DE ENTREGAS
-- =====================================================

-- Tabla de distribuciones programadas
CREATE TABLE `distribuciones_entrega` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `equipo_entrega_id` BIGINT UNSIGNED NOT NULL COMMENT 'Equipo asignado',
  `fecha_programada` DATE NOT NULL COMMENT 'Fecha programada de entrega',
  `observaciones` TEXT NULL COMMENT 'Observaciones generales',
  `estado_id` TINYINT NOT NULL DEFAULT 1 COMMENT '1=Pendiente, 2=En proceso, 3=Completada, 4=Cancelada',
  `users_id_creador` BIGINT UNSIGNED NOT NULL COMMENT 'Usuario que creó la distribución',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_equipo` (`equipo_entrega_id`),
  INDEX `idx_fecha` (`fecha_programada`),
  INDEX `idx_estado` (`estado_id`),
  INDEX `idx_creador` (`users_id_creador`),
  CONSTRAINT `fk_distribucion_equipo` FOREIGN KEY (`equipo_entrega_id`) REFERENCES `equipos_entrega` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_distribucion_users` FOREIGN KEY (`users_id_creador`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Distribuciones de entrega programadas';

-- Tabla de composición del equipo al momento de la distribución (snapshot)
-- Esta tabla guarda quiénes eran los miembros y sus porcentajes EXACTOS cuando se creó/ejecutó la distribución
CREATE TABLE `distribuciones_entrega_miembros` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `distribucion_entrega_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID de la distribución',
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Usuario miembro del equipo en ese momento',
  `porcentaje_comision` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Porcentaje que tenía asignado en ese momento',
  `monto_comision_calculado` DECIMAL(10,2) NULL DEFAULT 0.00 COMMENT 'Monto de comisión calculado para esta distribución',
  `pagado` TINYINT NOT NULL DEFAULT 0 COMMENT '1=Pagado, 0=Pendiente de pago',
  `fecha_pago` DATETIME NULL COMMENT 'Fecha en que se pagó la comisión',
  `users_id_quien_pago` BIGINT UNSIGNED NULL COMMENT 'Usuario que registró el pago',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_distribucion` (`distribucion_entrega_id`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_pagado` (`pagado`),
  CONSTRAINT `fk_dist_miembros_distribucion` FOREIGN KEY (`distribucion_entrega_id`) REFERENCES `distribuciones_entrega` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dist_miembros_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_dist_miembros_quien_pago` FOREIGN KEY (`users_id_quien_pago`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_dist_porcentaje_valido` CHECK (`porcentaje_comision` >= 0 AND `porcentaje_comision` <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Snapshot de miembros del equipo por distribución para cálculo de comisiones';

-- Tabla de facturas asignadas a distribuciones
CREATE TABLE `distribuciones_entrega_facturas` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `distribucion_entrega_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID de la distribución',
  `factura_id` INT NOT NULL COMMENT 'ID de la factura a entregar',
  `orden_entrega` INT NULL COMMENT 'Orden en la ruta de entrega',
  `estado_entrega` ENUM('sin_entrega', 'parcial', 'entregado') NOT NULL DEFAULT 'sin_entrega' COMMENT 'Estado de entrega',
  `fecha_entrega_real` DATETIME NULL COMMENT 'Fecha y hora real de entrega',
  `observaciones` TEXT NULL COMMENT 'Observaciones específicas de esta factura',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_distribucion_factura` (`distribucion_entrega_id`, `factura_id`),
  INDEX `idx_distribucion` (`distribucion_entrega_id`),
  INDEX `idx_factura` (`factura_id`),
  INDEX `idx_estado_entrega` (`estado_entrega`),
  CONSTRAINT `fk_dist_facturas_distribucion` FOREIGN KEY (`distribucion_entrega_id`) REFERENCES `distribuciones_entrega` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dist_facturas_factura` FOREIGN KEY (`factura_id`) REFERENCES `factura` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Facturas asignadas a distribuciones';

-- =====================================================
-- 3. CONFIRMACIÓN DE ENTREGAS
-- =====================================================

-- Tabla de productos entregados/incidencias
CREATE TABLE `entregas_productos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `distribucion_factura_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID de distribución_entrega_facturas',
  `producto_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID del producto',
  `cantidad_facturada` DECIMAL(10,2) NOT NULL COMMENT 'Cantidad en la factura',
  `cantidad_entregada` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Cantidad realmente entregada',
  `entregado` TINYINT NOT NULL DEFAULT 0 COMMENT '1=Entregado, 0=No entregado',
  `tiene_incidencia` TINYINT NOT NULL DEFAULT 0 COMMENT '1=Tiene incidencia, 0=Sin incidencia',
  `descripcion_incidencia` TEXT NULL COMMENT 'Descripción de la incidencia',
  `tipo_incidencia` VARCHAR(50) NULL COMMENT 'Tipo de incidencia (faltante, dañado, rechazo, etc)',
  `user_id_registro` BIGINT UNSIGNED NULL COMMENT 'Usuario que registró la entrega/incidencia',
  `fecha_registro` DATETIME NULL COMMENT 'Fecha y hora de registro',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_dist_factura_producto` (`distribucion_factura_id`, `producto_id`),
  INDEX `idx_dist_factura` (`distribucion_factura_id`),
  INDEX `idx_producto` (`producto_id`),
  INDEX `idx_entregado` (`entregado`),
  INDEX `idx_incidencia` (`tiene_incidencia`),
  INDEX `idx_user_registro` (`user_id_registro`),
  CONSTRAINT `fk_entregas_dist_factura` FOREIGN KEY (`distribucion_factura_id`) REFERENCES `distribuciones_entrega_facturas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_entregas_producto` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_entregas_user_registro` FOREIGN KEY (`user_id_registro`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de productos entregados e incidencias';

-- Tabla de evidencias fotográficas (opcional)
CREATE TABLE `entregas_evidencias` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `distribucion_factura_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID de distribución_entrega_facturas',
  `tipo_evidencia` ENUM('foto_entrega', 'firma_cliente', 'incidencia', 'otro') NOT NULL COMMENT 'Tipo de evidencia',
  `ruta_archivo` VARCHAR(255) NOT NULL COMMENT 'Ruta del archivo',
  `descripcion` TEXT NULL COMMENT 'Descripción de la evidencia',
  `user_id_registro` BIGINT UNSIGNED NULL COMMENT 'Usuario que subió la evidencia',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_dist_factura` (`distribucion_factura_id`),
  INDEX `idx_tipo` (`tipo_evidencia`),
  INDEX `idx_user` (`user_id_registro`),
  CONSTRAINT `fk_evidencias_dist_factura` FOREIGN KEY (`distribucion_factura_id`) REFERENCES `distribuciones_entrega_facturas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_evidencias_user` FOREIGN KEY (`user_id_registro`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Evidencias fotográficas de entregas';

-- =====================================================
-- TRIGGERS PARA ACTUALIZAR ESTADO DE ENTREGA
-- =====================================================

DELIMITER $$

-- Trigger para crear snapshot de miembros del equipo cuando se crea una distribución
CREATE TRIGGER `trg_snapshot_miembros_distribucion`
AFTER INSERT ON `distribuciones_entrega`
FOR EACH ROW
BEGIN
    -- Copiar los miembros activos del equipo con sus porcentajes al momento de crear la distribución
    INSERT INTO `distribuciones_entrega_miembros`
        (`distribucion_entrega_id`, `user_id`, `porcentaje_comision`, `created_at`)
    SELECT
        NEW.id,
        eem.user_id,
        eem.porcentaje_comision,
        NOW()
    FROM `equipos_entrega_miembros` eem
    WHERE eem.equipo_entrega_id = NEW.equipo_entrega_id
    AND eem.estado_id = 1;  -- Solo miembros activos
END$$

-- Trigger para actualizar snapshot cuando cambian los miembros del equipo
-- Solo actualiza si las distribuciones están en estado PENDIENTE (estado_id = 1)
CREATE TRIGGER `trg_actualizar_snapshot_miembros_after_insert`
AFTER INSERT ON `equipos_entrega_miembros`
FOR EACH ROW
BEGIN
    -- Actualizar snapshots de distribuciones PENDIENTES del mismo día
    IF NEW.estado_id = 1 THEN
        INSERT INTO `distribuciones_entrega_miembros`
            (`distribucion_entrega_id`, `user_id`, `porcentaje_comision`, `created_at`)
        SELECT
            de.id,
            NEW.user_id,
            NEW.porcentaje_comision,
            NOW()
        FROM `distribuciones_entrega` de
        WHERE de.equipo_entrega_id = NEW.equipo_entrega_id
        AND de.estado_id = 1  -- Solo distribuciones PENDIENTES
        AND DATE(de.fecha_programada) = CURDATE()  -- Solo del día actual
        AND NOT EXISTS (
            SELECT 1 FROM `distribuciones_entrega_miembros` dem
            WHERE dem.distribucion_entrega_id = de.id
            AND dem.user_id = NEW.user_id
        );
    END IF;
END$$

CREATE TRIGGER `trg_actualizar_snapshot_miembros_after_update`
AFTER UPDATE ON `equipos_entrega_miembros`
FOR EACH ROW
BEGIN
    -- Actualizar snapshots de distribuciones PENDIENTES del mismo día cuando cambia el porcentaje
    IF NEW.estado_id = 1 AND (OLD.porcentaje_comision != NEW.porcentaje_comision OR OLD.estado_id != NEW.estado_id) THEN
        UPDATE `distribuciones_entrega_miembros` dem
        INNER JOIN `distribuciones_entrega` de ON dem.distribucion_entrega_id = de.id
        SET dem.porcentaje_comision = NEW.porcentaje_comision,
            dem.updated_at = NOW()
        WHERE de.equipo_entrega_id = NEW.equipo_entrega_id
        AND de.estado_id = 1  -- Solo distribuciones PENDIENTES
        AND DATE(de.fecha_programada) = CURDATE()  -- Solo del día actual
        AND dem.user_id = NEW.user_id;
    END IF;

    -- Si desactivan un miembro, quitar de snapshots PENDIENTES del día actual
    IF OLD.estado_id = 1 AND NEW.estado_id != 1 THEN
        DELETE dem FROM `distribuciones_entrega_miembros` dem
        INNER JOIN `distribuciones_entrega` de ON dem.distribucion_entrega_id = de.id
        WHERE de.equipo_entrega_id = NEW.equipo_entrega_id
        AND de.estado_id = 1  -- Solo distribuciones PENDIENTES
        AND DATE(de.fecha_programada) = CURDATE()  -- Solo del día actual
        AND dem.user_id = NEW.user_id;
    END IF;
END$$

-- Trigger para actualizar estado de factura cuando se registran productos
CREATE TRIGGER `trg_actualizar_estado_factura_after_producto`
AFTER INSERT ON `entregas_productos`
FOR EACH ROW
BEGIN
    DECLARE total_productos INT;
    DECLARE productos_entregados INT;
    DECLARE nuevo_estado VARCHAR(20);

    -- Contar total de productos y entregados
    SELECT COUNT(*), SUM(entregado) INTO total_productos, productos_entregados
    FROM entregas_productos
    WHERE distribucion_factura_id = NEW.distribucion_factura_id;

    -- Determinar nuevo estado
    IF productos_entregados = 0 THEN
        SET nuevo_estado = 'sin_entrega';
    ELSEIF productos_entregados = total_productos THEN
        SET nuevo_estado = 'entregado';
    ELSE
        SET nuevo_estado = 'parcial';
    END IF;

    -- Actualizar estado en distribuciones_entrega_facturas
    UPDATE distribuciones_entrega_facturas
    SET estado_entrega = nuevo_estado,
        fecha_entrega_real = IF(nuevo_estado != 'sin_entrega', NOW(), fecha_entrega_real)
    WHERE id = NEW.distribucion_factura_id;
END$$

-- Trigger para actualizar estado cuando se modifica un producto
CREATE TRIGGER `trg_actualizar_estado_factura_after_update`
AFTER UPDATE ON `entregas_productos`
FOR EACH ROW
BEGIN
    DECLARE total_productos INT;
    DECLARE productos_entregados INT;
    DECLARE nuevo_estado VARCHAR(20);

    SELECT COUNT(*), SUM(entregado) INTO total_productos, productos_entregados
    FROM entregas_productos
    WHERE distribucion_factura_id = NEW.distribucion_factura_id;

    IF productos_entregados = 0 THEN
        SET nuevo_estado = 'sin_entrega';
    ELSEIF productos_entregados = total_productos THEN
        SET nuevo_estado = 'entregado';
    ELSE
        SET nuevo_estado = 'parcial';
    END IF;

    UPDATE distribuciones_entrega_facturas
    SET estado_entrega = nuevo_estado,
        fecha_entrega_real = IF(nuevo_estado != 'sin_entrega', NOW(), fecha_entrega_real)
    WHERE id = NEW.distribucion_factura_id;
END$$

DELIMITER ;

-- =====================================================
-- ÍNDICES ADICIONALES PARA RENDIMIENTO
-- =====================================================

-- Índice compuesto para búsquedas por fecha y equipo
CREATE INDEX `idx_fecha_equipo` ON `distribuciones_entrega` (`fecha_programada`, `equipo_entrega_id`);

-- Índice para búsquedas de entregas pendientes
CREATE INDEX `idx_estado_fecha` ON `distribuciones_entrega_facturas` (`estado_entrega`, `distribucion_entrega_id`);

-- =====================================================
-- COMENTARIOS FINALES
-- =====================================================

/*
NOTAS IMPORTANTES:

1. EQUIPOS DE ENTREGA:
   - Un equipo puede tener múltiples usuarios
   - La suma de porcentajes_comision debe validarse en la aplicación (≤ 100%)
   - Los equipos se pueden activar/desactivar sin eliminar histórico
   - Los miembros pueden agregarse/removerse del equipo en cualquier momento

2. DISTRIBUCIÓN Y COMISIONES:
   - Una factura solo puede estar en una distribución activa a la vez
   - El orden_entrega permite optimizar rutas
   - Estados de distribución: 1=Pendiente, 2=En proceso, 3=Completada, 4=Cancelada
   - Estados de entrega: sin_entrega → parcial → entregado
   - **IMPORTANTE - SNAPSHOT DE MIEMBROS:**
     * Cuando se crea una distribución, automáticamente se guarda un snapshot
       de quiénes eran los miembros del equipo y sus porcentajes en ese momento
     * Esto se almacena en `distribuciones_entrega_miembros`
     * **ACTUALIZACIÓN DINÁMICA DEL MISMO DÍA:**
       - Si la distribución está en estado PENDIENTE (1) Y es del día actual,
         el snapshot SE ACTUALIZA automáticamente cuando:
         * Se agrega un nuevo miembro al equipo
         * Se cambia el porcentaje de un miembro existente
         * Se quita un miembro del equipo
       - Una vez que la distribución cambia a EN PROCESO (2), COMPLETADA (3)
         o CANCELADA (4), el snapshot se CONGELA y ya no se puede modificar
       - Si la distribución es de un día anterior, el snapshot NUNCA se actualiza
     * Las comisiones se calculan y pagan basándose en este snapshot
     * Ejemplo práctico:
       - 8:00 AM: Se crea distribución del día con Juan (40%), María (30%), Pedro (30%)
       - 9:00 AM: Se edita equipo, Pedro cambia a 35% y María a 25%
         → El snapshot se actualiza porque está PENDIENTE y es del mismo día
       - 10:00 AM: La distribución cambia a EN PROCESO
       - 11:00 AM: Se quita a Pedro del equipo
         → El snapshot NO cambia porque ya está EN PROCESO
       - Las comisiones se pagan con: Juan 40%, María 25%, Pedro 35%

3. CONFIRMACIÓN:
   - Cada producto tiene su propio registro de entrega
   - Las incidencias se registran por producto
   - Los triggers automáticamente actualizan el estado de la factura

4. CÁLCULO DE COMISIONES:
   - El campo `monto_comision_calculado` se debe calcular cuando la distribución
     se completa, multiplicando el total de la distribución por el porcentaje
   - El campo `pagado` permite controlar qué comisiones ya fueron pagadas
   - Se puede generar reportes de comisiones pendientes por pagar

5. LLAVES FORÁNEAS:
   - users: tabla de usuarios del sistema
   - facturacion: tabla de facturas
   - producto: tabla de productos

6. EXTENSIONES FUTURAS:
   - Geolocalización de entregas
   - Notificaciones push
   - Dashboard de métricas
   - Reportes de comisiones por período
*/
