-- ============================================================================
-- LIMPIEZA DE ASIGNACIONES DE USUARIOS INACTIVOS EN CARTERA DE CLIENTES
-- MySQL / MariaDB
--
-- Elimina asignaciones de Asesor Comercial (rol 2) y Tele Asesor (rol 3)
-- cuando users.estado_id <> 1. Registra las bajas en la auditoria y limpia
-- referencias heredadas de zonificacion y cliente.vendedor.
--
-- IMPORTANTE: realice un respaldo y seleccione la base de datos correcta.
-- El script es idempotente: puede ejecutarse nuevamente sin duplicar bajas.
-- ============================================================================

SET @rol_asesor_comercial = 2;
SET @rol_tele_asesor = 3;
SET @estado_activo = 1;
SET @lote_id = UUID();

DROP TEMPORARY TABLE IF EXISTS tmp_asignaciones_inactivas;
CREATE TEMPORARY TABLE tmp_asignaciones_inactivas (
    asignacion_id BIGINT UNSIGNED NOT NULL,
    cliente_id INT NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    rol_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (asignacion_id),
    KEY idx_tmp_cliente (cliente_id),
    KEY idx_tmp_usuario_rol (usuario_id, rol_id)
) ENGINE=InnoDB;

INSERT INTO tmp_asignaciones_inactivas (
    asignacion_id,
    cliente_id,
    usuario_id,
    rol_id
)
SELECT
    cu.id,
    cu.cliente_id,
    cu.usuario_id,
    cu.rol_id
FROM cliente_usuario cu
INNER JOIN users u ON u.id = cu.usuario_id
WHERE cu.rol_id IN (@rol_asesor_comercial, @rol_tele_asesor)
  AND COALESCE(u.estado_id, 0) <> @estado_activo;

-- Vista previa de lo que sera eliminado.
SELECT
    CASE t.rol_id
        WHEN 2 THEN 'Asesor Comercial'
        WHEN 3 THEN 'Tele Asesor'
    END AS tipo,
    COUNT(*) AS asignaciones,
    COUNT(DISTINCT t.usuario_id) AS usuarios,
    COUNT(DISTINCT t.cliente_id) AS clientes
FROM tmp_asignaciones_inactivas t
GROUP BY t.rol_id
ORDER BY t.rol_id;

-- cliente.vendedor es un campo legado NOT NULL en instalaciones anteriores.
-- Se vuelve nullable porque algunos clientes pueden quedar sin asesor comercial.
ALTER TABLE cliente
    MODIFY COLUMN vendedor BIGINT UNSIGNED NULL;

START TRANSACTION;

INSERT INTO cliente_asesor_auditoria (
    cliente_id,
    asesor_id,
    tipo,
    accion,
    usuario,
    comentario,
    lote_id,
    fecha
)
SELECT
    t.cliente_id,
    t.usuario_id,
    CASE t.rol_id
        WHEN 2 THEN 'Asesor Comercial'
        WHEN 3 THEN 'Tele Asesor'
    END,
    'DELETE',
    NULL,
    'Limpieza SQL: usuario inactivo removido de Cartera de Clientes',
    @lote_id,
    CURRENT_TIMESTAMP
FROM tmp_asignaciones_inactivas t;

-- Evita que la herencia de una zona vuelva a insertar al usuario inactivo.
DELETE cza
FROM cliente_zona_asignaciones cza
INNER JOIN users u ON u.id = cza.usuario_id
WHERE cza.rol_id IN (@rol_asesor_comercial, @rol_tele_asesor)
  AND COALESCE(u.estado_id, 0) <> @estado_activo;

DELETE czr
FROM cliente_zona_responsables czr
INNER JOIN users u ON u.id = czr.usuario_id
WHERE czr.rol_id IN (@rol_asesor_comercial, @rol_tele_asesor)
  AND COALESCE(u.estado_id, 0) <> @estado_activo;

DELETE cu
FROM cliente_usuario cu
INNER JOIN tmp_asignaciones_inactivas t ON t.asignacion_id = cu.id;

-- Mantiene cliente.vendedor alineado con el asesor comercial activo mas
-- recientemente asignado. Queda NULL si el cliente ya no tiene uno.
UPDATE cliente c
INNER JOIN (
    SELECT DISTINCT cliente_id
    FROM tmp_asignaciones_inactivas
    WHERE rol_id = @rol_asesor_comercial
) afectados ON afectados.cliente_id = c.id
SET c.vendedor = (
    SELECT cu.usuario_id
    FROM cliente_usuario cu
    INNER JOIN users u ON u.id = cu.usuario_id
    WHERE cu.cliente_id = c.id
      AND cu.rol_id = @rol_asesor_comercial
      AND u.estado_id = @estado_activo
    ORDER BY cu.fecha_asignacion DESC, cu.id DESC
    LIMIT 1
);

COMMIT;

-- Comprobacion final: ambos resultados deben ser cero.
SELECT COUNT(*) AS asignaciones_inactivas_restantes
FROM cliente_usuario cu
INNER JOIN users u ON u.id = cu.usuario_id
WHERE cu.rol_id IN (@rol_asesor_comercial, @rol_tele_asesor)
  AND COALESCE(u.estado_id, 0) <> @estado_activo;

SELECT COUNT(*) AS vendedores_legacy_inactivos_restantes
FROM cliente c
INNER JOIN users u ON u.id = c.vendedor
WHERE COALESCE(u.estado_id, 0) <> @estado_activo;

DROP TEMPORARY TABLE IF EXISTS tmp_asignaciones_inactivas;