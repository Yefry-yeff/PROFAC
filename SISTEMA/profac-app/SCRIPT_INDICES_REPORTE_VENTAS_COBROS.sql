-- ============================================================
-- INDICES PARA OPTIMIZAR REPORTE VENTAS Y COBROS
-- Fecha: 2026-07-20
-- Motor esperado: MySQL/MariaDB
--
-- Ejecutar en la base de datos de PROFAC.
-- El script evita duplicar indices existentes por nombre.
-- ============================================================

SET @db_name := DATABASE();

DROP PROCEDURE IF EXISTS add_index_if_missing;
DELIMITER $$
CREATE PROCEDURE add_index_if_missing(
    IN p_table VARCHAR(128),
    IN p_index VARCHAR(128),
    IN p_cols  VARCHAR(512)
)
BEGIN
    DECLARE v_exists INT DEFAULT 0;

    SELECT COUNT(1)
      INTO v_exists
      FROM information_schema.statistics
     WHERE table_schema = @db_name
       AND table_name   = p_table
       AND index_name   = p_index;

    IF v_exists = 0 THEN
        SET @sql = CONCAT('ALTER TABLE ', p_table, ' ADD INDEX ', p_index, ' (', p_cols, ')');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END $$
DELIMITER ;

-- ------------------------------------------------------------
-- FACTURA (filtros y orden principal)
-- ------------------------------------------------------------
CALL add_index_if_missing('factura', 'idx_factura_fecha_emision', 'fecha_emision');
CALL add_index_if_missing('factura', 'idx_factura_vendedor_fecha', 'vendedor, fecha_emision');
CALL add_index_if_missing('factura', 'idx_factura_cliente_fecha', 'cliente_id, fecha_emision');
CALL add_index_if_missing('factura', 'idx_factura_tipo_pago_fecha', 'tipo_pago_id, fecha_emision');
CALL add_index_if_missing('factura', 'idx_factura_estado_venta', 'estado_venta_id');
CALL add_index_if_missing('factura', 'idx_factura_numero_secuencia', 'numero_secuencia_cai');

-- ------------------------------------------------------------
-- APLICACION PAGOS (ultimo registro por factura y joins)
-- ------------------------------------------------------------
CALL add_index_if_missing('aplicacion_pagos', 'idx_ap_factura_estado_id', 'factura_id, estado, id');
CALL add_index_if_missing('aplicacion_pagos', 'idx_ap_factura_id', 'factura_id, id');

-- ------------------------------------------------------------
-- ABONOS CREDITOS (sumas/filtros por factura via aplicacion_pagos)
-- ------------------------------------------------------------
CALL add_index_if_missing('abonos_creditos', 'idx_ac_ap_estado', 'aplicacion_pagos_id, estado_abono');
CALL add_index_if_missing('abonos_creditos', 'idx_ac_ap_estado_fecha', 'aplicacion_pagos_id, estado_abono, fecha_pago');
CALL add_index_if_missing('abonos_creditos', 'idx_ac_ap_estado_banco', 'aplicacion_pagos_id, estado_abono, banco_id');
CALL add_index_if_missing('abonos_creditos', 'idx_ac_ap_estado_id', 'aplicacion_pagos_id, estado_abono, id');

-- ------------------------------------------------------------
-- NOTAS DE CREDITO / DEBITO
-- ------------------------------------------------------------
CALL add_index_if_missing('nota_credito', 'idx_nc_factura_estado_fecha', 'factura_id, estado_nota_id, fecha');
CALL add_index_if_missing('notadebito', 'idx_nd_factura_fecha', 'factura_id, fechaEmision');

-- ------------------------------------------------------------
-- HISTORICO FLUJO / FLUJO (join por tramite)
-- ------------------------------------------------------------
CALL add_index_if_missing('historico_flujo', 'idx_hf_tipo_tramite_flujo', 'tipo_tramite_id, tramite_id, flujo_id');

-- ------------------------------------------------------------
-- VENTA_HAS_PRODUCTO (agregado para tipo_venta_id = 3)
-- ------------------------------------------------------------
CALL add_index_if_missing('venta_has_producto', 'idx_vhp_factura_isv_tipo', 'factura_id, isv, tipo_precio');

-- Limpieza
DROP PROCEDURE IF EXISTS add_index_if_missing;

-- Fin script
