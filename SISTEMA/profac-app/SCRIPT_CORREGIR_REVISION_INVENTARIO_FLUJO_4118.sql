-- Reconciliacion del flujo 4118: la revision quedo asociada a la oferta 42051,
-- mientras que la oferta ganadora y las lineas revisadas pertenecen a 42052.
-- Script idempotente: no duplica la revision activa ni modifica prefacturas.

SET @flujo_id = 4118;
SET @oferta_anterior_id = 42051;
SET @oferta_ganadora_id = 42052;
SET @usuario_reparacion_id = NULL;

START TRANSACTION;

-- Cerrar solo revisiones activas de otra oferta si 42052 sigue siendo ganadora
-- y tiene lineas de revision guardadas.
UPDATE historico_flujo AS revision
JOIN historico_flujo AS ganadora
  ON ganadora.flujo_id = revision.flujo_id
 AND ganadora.tramite_id = @oferta_ganadora_id
 AND ganadora.tipo_tramite_id = 2
 AND ganadora.observaciones = 'ganadora'
JOIN revision_inventario_lineas AS linea
  ON linea.flujo_id = ganadora.flujo_id
 AND linea.cotizacion_id = ganadora.tramite_id
SET revision.estado_id = 7,
    revision.observaciones = CONCAT(
        'Reconciliado: revision asociada a oferta no ganadora #',
        @oferta_anterior_id,
        '. Oferta ganadora actual: #',
        @oferta_ganadora_id,
        '.'
    ),
    revision.updated_by = @usuario_reparacion_id,
    revision.updated_at = NOW()
WHERE revision.flujo_id = @flujo_id
  AND revision.tipo_tramite_id = 9
  AND revision.estado_id = 5
  AND revision.tramite_id <> @oferta_ganadora_id;

-- Crear una revision activa para la ganadora solo si aun no existe y no hay
-- prefactura activa para esa oferta.
INSERT INTO historico_flujo (
    flujo_id,
    tramite_id,
    tipo_tramite_id,
    estado_id,
    observaciones,
    created_by,
    updated_by,
    created_at,
    updated_at
)
SELECT
    @flujo_id,
    @oferta_ganadora_id,
    9,
    5,
    CONCAT('Revision reanudada para oferta ganadora #', @oferta_ganadora_id, ' tras reconciliar el flujo.'),
    @usuario_reparacion_id,
    @usuario_reparacion_id,
    NOW(),
    NOW()
FROM DUAL
WHERE EXISTS (
    SELECT 1
    FROM historico_flujo
    WHERE flujo_id = @flujo_id
      AND tramite_id = @oferta_ganadora_id
      AND tipo_tramite_id = 2
      AND observaciones = 'ganadora'
)
AND EXISTS (
    SELECT 1
    FROM revision_inventario_lineas
    WHERE flujo_id = @flujo_id
      AND cotizacion_id = @oferta_ganadora_id
)
AND NOT EXISTS (
    SELECT 1
    FROM prefactura
    WHERE flujo_id = @flujo_id
      AND cotizacion_id = @oferta_ganadora_id
      AND estado IN ('activo', 'procesando', 'convertida')
)
AND NOT EXISTS (
    SELECT 1
    FROM historico_flujo
    WHERE flujo_id = @flujo_id
      AND tramite_id = @oferta_ganadora_id
      AND tipo_tramite_id = 9
      AND estado_id = 5
);

UPDATE flujo
SET tipo_tramite_id = 9,
    updated_at = NOW()
WHERE id = @flujo_id
  AND EXISTS (
      SELECT 1
      FROM historico_flujo
      WHERE flujo_id = @flujo_id
        AND tramite_id = @oferta_ganadora_id
        AND tipo_tramite_id = 9
        AND estado_id = 5
  );

COMMIT;

-- Verificacion: debe quedar un estado 7 para 42051 y un estado 5 para 42052.
SELECT id, flujo_id, tramite_id, tipo_tramite_id, estado_id, observaciones
FROM historico_flujo
WHERE flujo_id = @flujo_id
  AND tipo_tramite_id = 9
ORDER BY id;

-- Verificacion del progreso preservado.
SELECT cotizacion_id,
       COUNT(*) AS lineas,
       SUM(revisado = 1) AS revisadas,
       SUM(revisado = 0) AS pendientes
FROM revision_inventario_lineas
WHERE flujo_id = @flujo_id
  AND cotizacion_id = @oferta_ganadora_id
GROUP BY cotizacion_id;