-- =====================================================================
-- MIGRACIÓN INICIAL — MÓDULO "CARTERA DE CLIENTES"
-- =====================================================================
-- Objetivo:
--   1) Limpiar cualquier dato existente (p. ej. datos de prueba) en las
--      tablas nuevas del módulo: cliente_usuario y cliente_asesor_auditoria.
--   2) Poblar cliente_usuario con los "Asesores Comerciales" actuales,
--      tomando como fuente de verdad el campo legado cliente.vendedor
--      (cada cliente aporta 1 fila: su vendedor actual pasa a ser su
--      Asesor Comercial asignado en el nuevo módulo).
--   3) Registrar el alta correspondiente en cliente_asesor_auditoria
--      para dejar trazabilidad de esta carga inicial.
--
-- Este script es idempotente: puede ejecutarse más de una vez sin
-- duplicar datos, porque limpia las tablas antes de insertar.
--
-- Requisito previo: las migraciones que crean cliente_usuario y
-- cliente_asesor_auditoria ya deben estar aplicadas en el entorno
-- donde se ejecute este script.
-- =====================================================================

SET @lote_id = UUID();

-- 1) Limpieza de datos existentes en las tablas del módulo
DELETE FROM cliente_asesor_auditoria;
DELETE FROM cliente_usuario;
ALTER TABLE cliente_usuario AUTO_INCREMENT = 1;
ALTER TABLE cliente_asesor_auditoria AUTO_INCREMENT = 1;

-- 2) Backfill: cliente.vendedor -> cliente_usuario (tipo Asesor Comercial,
--    derivado implícitamente porque este es el rol asignado a ese usuario)
INSERT INTO cliente_usuario (cliente_id, usuario_id, fecha_asignacion, asignado_por, created_at, updated_at)
SELECT c.id, c.vendedor, NOW(), NULL, NOW(), NOW()
FROM cliente c
WHERE c.vendedor IS NOT NULL;

-- 3) Auditoría del alta inicial (todas las filas pertenecen al mismo lote)
INSERT INTO cliente_asesor_auditoria (cliente_id, asesor_id, tipo, accion, usuario, comentario, lote_id, fecha)
SELECT cu.cliente_id, cu.usuario_id, 'Asesor Comercial', 'INSERT', NULL,
       'Migración inicial desde cliente.vendedor', @lote_id, NOW()
FROM cliente_usuario cu;

-- 4) Verificación rápida (solo lectura, informativo)
SELECT (SELECT COUNT(*) FROM cliente_usuario) AS total_cliente_usuario,
       (SELECT COUNT(*) FROM cliente_asesor_auditoria) AS total_auditoria,
       (SELECT COUNT(*) FROM cliente WHERE vendedor IS NOT NULL) AS total_clientes_con_vendedor;
