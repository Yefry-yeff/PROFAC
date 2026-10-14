-- MySQL/MariaDB. Ejecutar sobre la base de PROFAC con respaldo previo.
-- Solo cierra pendientes de distribucion anteriores al 19/05/2026.
-- No modifica ventas, pagos, asignaciones ni entregas fisicas.
-- Puede repetirse secuencialmente sin duplicar cierres.
-- El filtro de fechas de la aplicacion debe estar desplegado tambien.

START TRANSACTION;

INSERT INTO factura_tratamiento_entrega_historial (
    factura_id,
    estado,
    distribucion_entrega_id,
    department_id,
    municipality_id,
    direccion_entrega,
    observaciones,
    user_id,
    created_at,
    updated_at
)
SELECT
    f.id,
    'completada',
    NULL,
    fte.department_id,
    fte.municipality_id,
    fte.direccion_entrega,
    CONCAT(
        'Cambio de estado de ',
        CASE WHEN fte.factura_id IS NOT NULL THEN 'Tratada' ELSE 'Pendiente' END,
        ' a Completada, solicitado en GLPI-330'
    ),
    NULL,
    NOW(),
    NOW()
FROM factura f
LEFT JOIN factura_tratamiento_entrega fte ON fte.factura_id = f.id
WHERE f.estado_factura_id IN (1, 2)
  AND f.estado_venta_id = 1
  AND f.fecha_emision < '2026-05-19'
  AND NOT EXISTS (
      SELECT 1
      FROM distribuciones_entrega_facturas def
      WHERE def.factura_id = f.id
        AND def.estado_entrega = 'entregado'
  )
  AND NOT EXISTS (
      SELECT 1
      FROM distribuciones_entrega_facturas def
      INNER JOIN distribuciones_entrega de ON de.id = def.distribucion_entrega_id
      WHERE def.factura_id = f.id
        AND de.estado_id IN (1, 2)
        AND def.estado_entrega != 'anulada'
  )
  AND NOT EXISTS (
      SELECT 1
      FROM factura_tratamiento_entrega_historial h
      WHERE h.factura_id = f.id
        AND h.estado = 'completada'
  );

SELECT ROW_COUNT() AS facturas_completadas_en_esta_ejecucion;

COMMIT;
