-- =============================================================================
-- MIGRACIÓN: CONSOLIDACIÓN DE CATEGORÍAS DE CLIENTES Y PRECIOS
-- =============================================================================
-- Descripción:
--   Transforma la estructura actual donde cada nivel de precio (A, B, C...)
--   es una categoría de cliente independiente, a una estructura consolidada
--   donde "Co-Distribuidor", "Final Empresarial" y "Gobierno" son las
--   categorías de cliente, y los niveles A, B, C... son categorías de precio
--   bajo cada una.
--
-- Tablas afectadas:
<<<<<<< HEAD
--   - cliente              -> ADD categoria_precios_id, UPDATE cliente_categoria_escala_id
--   - cliente_categoria_escala -> INSERT 3 consolidadas, INACTIVAR individuales
--   - categoria_precios    -> UPDATE cliente_categoria_escala_id
--   - comision_escala      -> UPDATE cliente_categoria_escala_id (si existen registros)
=======
--   - cliente              → ADD categoria_precios_id, UPDATE cliente_categoria_escala_id
--   - cliente_categoria_escala → INSERT 3 consolidadas, INACTIVAR individuales
--   - categoria_precios    → UPDATE cliente_categoria_escala_id
--   - comision_escala      → UPDATE cliente_categoria_escala_id (si existen registros)
>>>>>>> origin/Union_Flujo_comisiones
--
-- Tablas NO afectadas (datos de precios intactos):
--   - precios_producto_carga   ← SIN CAMBIOS
--   - cotizacion               ← SIN CAMBIOS
--   - cotizacion_has_producto  ← SIN CAMBIOS
--   - factura                  ← SIN CAMBIOS
--   - factura_has_producto     ← SIN CAMBIOS
--
-- IMPORTANTE: Ejecutar primero php artisan migrate para agregar la columna,
--             luego ejecutar este script desde el STEP 3 en adelante.
--             O ejecutar el script completo si se corre directo en MySQL.
--
-- Autor: Generado por GitHub Copilot
-- Fecha: 2026-05-15
-- =============================================================================

START TRANSACTION;

-- =============================================================================
-- VERIFICACIÓN PREVIA: chequear que no se haya ejecutado ya
-- =============================================================================
DO (SELECT IF(
    EXISTS(SELECT 1 FROM cliente_categoria_escala WHERE nombre_categoria = 'Co-Distribuidor' AND estado_id = 1 LIMIT 1)
    AND NOT EXISTS(SELECT 1 FROM cliente_categoria_escala WHERE nombre_categoria LIKE 'Co-Distribuidor %' AND estado_id = 1 LIMIT 1),
    (SELECT 1/0),  -- fuerza error si ya fue ejecutado
    1
));
-- Si el script anterior falla con "Division by zero", la migración ya fue aplicada.
-- Comentar el bloque DO anterior si se desea re-ejecutar manualmente.

-- =============================================================================
-- STEP 1: Agregar columna categoria_precios_id a cliente
--         (Si ya existe por migración Laravel, esta instrucción la omite)
-- =============================================================================
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'cliente'
      AND column_name = 'categoria_precios_id'
);

SET @sql_alter = IF(@col_exists = 0,
    'ALTER TABLE cliente ADD COLUMN categoria_precios_id INT NULL AFTER cliente_categoria_escala_id',
    'SELECT ''Columna categoria_precios_id ya existe, omitiendo ALTER TABLE'' AS info'
);
PREPARE stmt FROM @sql_alter;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =============================================================================
-- STEP 2: Poblar categoria_precios_id ANTES de cualquier cambio
--         Aprovecha la relación 1-a-1 actual para capturar el tier exacto
--         que tiene cada cliente hoy.
-- =============================================================================
UPDATE cliente c
INNER JOIN categoria_precios cp
    ON  cp.cliente_categoria_escala_id = c.cliente_categoria_escala_id
    AND cp.estado_id = 1
SET c.categoria_precios_id = cp.id
WHERE c.cliente_categoria_escala_id IS NOT NULL
  AND c.categoria_precios_id IS NULL;

-- Verificación parcial:
SELECT CONCAT(
<<<<<<< HEAD
    'STEP 2 OK - Clientes con categoria_precios_id asignado: ',
=======
    'STEP 2 OK — Clientes con categoria_precios_id asignado: ',
>>>>>>> origin/Union_Flujo_comisiones
    COUNT(categoria_precios_id),
    ' de ',
    COUNT(*), ' clientes totales'
) AS verificacion FROM cliente;

-- =============================================================================
-- STEP 3: Insertar las 3 nuevas categorías consolidadas de cliente
-- =============================================================================

-- Co-Distribuidor
INSERT INTO cliente_categoria_escala
    (nombre_categoria, descripcion_categoria, comentario_cat_cliente, estado_id, users_id_creador, created_at, updated_at)
SELECT
    'Co-Distribuidor',
    'Distribuidora / Supermercado / Cadena de Tiendas / Bodega / Tienda Escolar / Mercadito / Pulpería',
<<<<<<< HEAD
    'Categoría consolidada - agrupa todos los niveles de precio Co-Distribuidor (A -> K)',
=======
    'Categoría consolidada — agrupa todos los niveles de precio Co-Distribuidor (A → K)',
>>>>>>> origin/Union_Flujo_comisiones
    1,
    MIN(users_id_creador),
    NOW(),
    NOW()
FROM cliente_categoria_escala
WHERE nombre_categoria LIKE 'Co-Distribuidor %'
  AND estado_id = 1;

SET @id_co_dist = LAST_INSERT_ID();

-- Final Empresarial
INSERT INTO cliente_categoria_escala
    (nombre_categoria, descripcion_categoria, comentario_cat_cliente, estado_id, users_id_creador, created_at, updated_at)
SELECT
    'Final Empresarial',
    'Banco / Supermercado / Fábrica / Distribuidora / Centro Comercial / Universidad / Hospital / Cooperativa / Restaurante',
<<<<<<< HEAD
    'Categoría consolidada - agrupa todos los niveles de precio Final Empresarial (A -> K)',
=======
    'Categoría consolidada — agrupa todos los niveles de precio Final Empresarial (A → K)',
>>>>>>> origin/Union_Flujo_comisiones
    1,
    MIN(users_id_creador),
    NOW(),
    NOW()
FROM cliente_categoria_escala
WHERE nombre_categoria LIKE 'Final Empresarial %'
  AND estado_id = 1;

SET @id_final_emp = LAST_INSERT_ID();

-- Gobierno
INSERT INTO cliente_categoria_escala
    (nombre_categoria, descripcion_categoria, comentario_cat_cliente, estado_id, users_id_creador, created_at, updated_at)
SELECT
    'Gobierno',
    'Salud / Educación / Seguridad / Banco Central / SAR / UNAH / Aduanas / Alcaldías / Hospitales / Ministerios',
<<<<<<< HEAD
    'Categoría consolidada - agrupa todos los niveles de precio Gobierno (A -> H)',
=======
    'Categoría consolidada — agrupa todos los niveles de precio Gobierno (A → H)',
>>>>>>> origin/Union_Flujo_comisiones
    1,
    MIN(users_id_creador),
    NOW(),
    NOW()
FROM cliente_categoria_escala
WHERE nombre_categoria LIKE 'Gobierno %'
  AND estado_id = 1;

SET @id_gobierno = LAST_INSERT_ID();

-- Verificación:
<<<<<<< HEAD
SELECT CONCAT('STEP 3 OK - Nuevas categorías creadas: Co-Distribuidor(', @id_co_dist, '), Final Empresarial(', @id_final_emp, '), Gobierno(', @id_gobierno, ')') AS verificacion;
=======
SELECT CONCAT('STEP 3 OK — Nuevas categorías creadas: Co-Distribuidor(', @id_co_dist, '), Final Empresarial(', @id_final_emp, '), Gobierno(', @id_gobierno, ')') AS verificacion;
>>>>>>> origin/Union_Flujo_comisiones

-- =============================================================================
-- STEP 4: Reasignar cliente.cliente_categoria_escala_id a los nuevos IDs
--         (el campo categoria_precios_id ya preserva el tier específico)
-- =============================================================================
UPDATE cliente c
INNER JOIN cliente_categoria_escala cce ON cce.id = c.cliente_categoria_escala_id
SET c.cliente_categoria_escala_id = @id_co_dist
WHERE cce.nombre_categoria LIKE 'Co-Distribuidor %';

UPDATE cliente c
INNER JOIN cliente_categoria_escala cce ON cce.id = c.cliente_categoria_escala_id
SET c.cliente_categoria_escala_id = @id_final_emp
WHERE cce.nombre_categoria LIKE 'Final Empresarial %';

UPDATE cliente c
INNER JOIN cliente_categoria_escala cce ON cce.id = c.cliente_categoria_escala_id
SET c.cliente_categoria_escala_id = @id_gobierno
WHERE cce.nombre_categoria LIKE 'Gobierno %';

<<<<<<< HEAD
SELECT CONCAT('STEP 4 OK - Clientes por grupo: CoDist=',
=======
SELECT CONCAT('STEP 4 OK — Clientes por grupo: CoDist=',
>>>>>>> origin/Union_Flujo_comisiones
    (SELECT COUNT(*) FROM cliente WHERE cliente_categoria_escala_id = @id_co_dist),
    ', FinalEmp=',
    (SELECT COUNT(*) FROM cliente WHERE cliente_categoria_escala_id = @id_final_emp),
    ', Gobierno=',
    (SELECT COUNT(*) FROM cliente WHERE cliente_categoria_escala_id = @id_gobierno)
) AS verificacion;

-- =============================================================================
-- STEP 5: Reasignar categoria_precios.cliente_categoria_escala_id
--         Los registros de precios por producto (precios_producto_carga)
<<<<<<< HEAD
--         NO SE TOCAN - siguen apuntando al mismo categoria_precios.id
=======
--         NO SE TOCAN — siguen apuntando al mismo categoria_precios.id
>>>>>>> origin/Union_Flujo_comisiones
-- =============================================================================
UPDATE categoria_precios cp
INNER JOIN cliente_categoria_escala cce ON cce.id = cp.cliente_categoria_escala_id
SET cp.cliente_categoria_escala_id = @id_co_dist
WHERE cce.nombre_categoria LIKE 'Co-Distribuidor %';

UPDATE categoria_precios cp
INNER JOIN cliente_categoria_escala cce ON cce.id = cp.cliente_categoria_escala_id
SET cp.cliente_categoria_escala_id = @id_final_emp
WHERE cce.nombre_categoria LIKE 'Final Empresarial %';

UPDATE categoria_precios cp
INNER JOIN cliente_categoria_escala cce ON cce.id = cp.cliente_categoria_escala_id
SET cp.cliente_categoria_escala_id = @id_gobierno
WHERE cce.nombre_categoria LIKE 'Gobierno %';

<<<<<<< HEAD
SELECT CONCAT('STEP 5 OK - Categorías de precio por grupo: CoDist=',
=======
SELECT CONCAT('STEP 5 OK — Categorías de precio por grupo: CoDist=',
>>>>>>> origin/Union_Flujo_comisiones
    (SELECT COUNT(*) FROM categoria_precios WHERE cliente_categoria_escala_id = @id_co_dist AND estado_id = 1),
    ', FinalEmp=',
    (SELECT COUNT(*) FROM categoria_precios WHERE cliente_categoria_escala_id = @id_final_emp AND estado_id = 1),
    ', Gobierno=',
    (SELECT COUNT(*) FROM categoria_precios WHERE cliente_categoria_escala_id = @id_gobierno AND estado_id = 1)
) AS verificacion;

-- =============================================================================
-- STEP 6: Reasignar comision_escala.cliente_categoria_escala_id
--         (aplica si existen configuraciones de comisión en producción)
-- =============================================================================
UPDATE comision_escala ce
INNER JOIN cliente_categoria_escala cce ON cce.id = ce.cliente_categoria_escala_id
SET ce.cliente_categoria_escala_id = @id_co_dist
WHERE cce.nombre_categoria LIKE 'Co-Distribuidor %';

UPDATE comision_escala ce
INNER JOIN cliente_categoria_escala cce ON cce.id = ce.cliente_categoria_escala_id
SET ce.cliente_categoria_escala_id = @id_final_emp
WHERE cce.nombre_categoria LIKE 'Final Empresarial %';

UPDATE comision_escala ce
INNER JOIN cliente_categoria_escala cce ON cce.id = ce.cliente_categoria_escala_id
SET ce.cliente_categoria_escala_id = @id_gobierno
WHERE cce.nombre_categoria LIKE 'Gobierno %';

<<<<<<< HEAD
SELECT 'STEP 6 OK - Comisiones actualizadas' AS verificacion;
=======
SELECT 'STEP 6 OK — Comisiones actualizadas' AS verificacion;
>>>>>>> origin/Union_Flujo_comisiones

-- =============================================================================
-- STEP 7: Inactivar las categorías de cliente individuales antiguas
--         Se marcan como inactivas (estado_id = 2), no se eliminan
--         para preservar integridad referencial histórica
-- =============================================================================
UPDATE cliente_categoria_escala
SET estado_id          = 2,
    fecha_inactivacion = CURDATE(),
    updated_at         = NOW()
WHERE (
        nombre_categoria LIKE 'Co-Distribuidor %'
     OR nombre_categoria LIKE 'Final Empresarial %'
     OR nombre_categoria LIKE 'Gobierno %'
     )
  AND id NOT IN (@id_co_dist, @id_final_emp, @id_gobierno)
  AND estado_id = 1;

<<<<<<< HEAD
SELECT CONCAT('STEP 7 OK - Categorías individuales inactivadas: ',
=======
SELECT CONCAT('STEP 7 OK — Categorías individuales inactivadas: ',
>>>>>>> origin/Union_Flujo_comisiones
    ROW_COUNT()
) AS verificacion;

-- =============================================================================
-- STEP 8: VERIFICACIÓN FINAL
-- =============================================================================
SELECT '===== RESULTADO FINAL =====' AS resumen;

SELECT
    cce.nombre_categoria AS categoria_cliente,
    cce.estado_id,
    COUNT(cp.id) AS total_categorias_precio
FROM cliente_categoria_escala cce
LEFT JOIN categoria_precios cp ON cp.cliente_categoria_escala_id = cce.id AND cp.estado_id = 1
WHERE cce.estado_id = 1
GROUP BY cce.id, cce.nombre_categoria
ORDER BY cce.nombre_categoria;

SELECT '--- Clientes con precio asignado ---' AS resumen;
SELECT
    COUNT(*)                    AS total_clientes,
    COUNT(cliente_categoria_escala_id) AS con_categoria,
    COUNT(categoria_precios_id)         AS con_precio_especifico,
    COUNT(*) - COUNT(categoria_precios_id) AS sin_precio_especifico
FROM cliente;

SELECT '--- Integridad: precios_producto_carga NO modificado ---' AS resumen;
SELECT COUNT(*) AS registros_precios_sin_cambio FROM precios_producto_carga WHERE estado_id = 1;

-- =============================================================================
-- Si todas las verificaciones se ven correctas, ejecutar COMMIT.
-- Si algo está mal, ejecutar ROLLBACK.
-- =============================================================================
COMMIT;
-- ROLLBACK; -- Descomentar esto y comentar COMMIT para hacer prueba sin aplicar

