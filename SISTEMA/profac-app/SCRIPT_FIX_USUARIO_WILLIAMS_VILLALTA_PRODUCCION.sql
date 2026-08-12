-- =====================================================================
-- FIX: Usuario williams.villalta@distribucionesvalencia.hn
-- Problema: la cuenta original (2022) quedó Inactiva (estado_id=2) con
-- rol_id huérfano (0) y contraseña antigua. Al intentar "crear" el usuario
-- de nuevo, la falta de índice único en users.email permitió un duplicado
-- vacío (sin historial) en lugar de fallar o actualizar el existente.
--
-- Este script:
--  1) Reactiva y actualiza la cuenta ORIGINAL (la de menor id / más antigua,
--     que es la que tiene el historial de facturas/cotizaciones/etc.)
--  2) Si existiera un duplicado creado por el bug, lo desactiva (no lo borra).
--
-- Ejecutar dentro de una transacción y verificar los SELECT antes de confirmar.
-- =====================================================================

START TRANSACTION;

-- 0) Diagnóstico previo (revisar antes de continuar)
SELECT id, name, email, rol_id, estado_id, created_at, updated_at
FROM users
WHERE email = 'williams.villalta@distribucionesvalencia.hn'
ORDER BY id ASC;

-- 1) Reactivar y corregir la cuenta ORIGINAL (id más bajo = con historial)
--    rol_id = 2  -> Asesor Comercial (ajustar si corresponde otro rol)
--    password    -> hash bcrypt para 'wv.2026.dv'
UPDATE users
SET estado_id = 1,
    rol_id = 2,
    password = '$2y$10$H7UQ4hSGzqAK4VwDdpHgouZFZ/7CrP02gzv92Ke8RrkZnUrAQPWL2',
    must_change_password = 1,
    updated_at = NOW()
WHERE id = (
    SELECT id FROM (
        SELECT MIN(id) AS id
        FROM users
        WHERE email = 'williams.villalta@distribucionesvalencia.hn'
    ) AS t
);

-- 2) Desactivar cualquier duplicado (id distinto al original) del mismo email
UPDATE users
SET estado_id = 2,
    updated_at = NOW()
WHERE email = 'williams.villalta@distribucionesvalencia.hn'
  AND id <> (
    SELECT id FROM (
        SELECT MIN(id) AS id
        FROM users
        WHERE email = 'williams.villalta@distribucionesvalencia.hn'
    ) AS t
  );

-- 3) Verificación final (debe mostrar UNA sola fila con estado_id=1
--    y las demás, si existían, con estado_id=2)
SELECT id, name, email, rol_id, estado_id, updated_at
FROM users
WHERE email = 'williams.villalta@distribucionesvalencia.hn'
ORDER BY id ASC;

-- Si todo se ve correcto:
COMMIT;
-- Si algo no cuadra, en vez de COMMIT ejecutar:
-- ROLLBACK;
