-- ============================================================================
-- SCRIPTS SQL PARA REPORTES DE COMISIONES
-- Fecha de creación: 2026-01-10
-- ============================================================================

-- NOTA: Reemplaza las variables @fechaInicio, @fechaFin y @empleadoId 
-- con los valores reales que necesites consultar

-- Variables de ejemplo
SET @fechaInicio = '2026-01-01 00:00:00';
SET @fechaFin = '2026-01-31 23:59:59';
SET @empleadoId = 73;  -- ID del empleado para reporte por empleado
SET @rolId = 3;        -- ID del rol para filtro opcional en reporte por rol


-- ============================================================================
-- 1. REPORTE POR EMPLEADO
-- ============================================================================
-- Descripción: Muestra cada producto vendido con su comisión individual
--              para un empleado específico en un rango de fechas.
-- Filtros: OBLIGATORIO seleccionar empleado (@empleadoId)
-- ============================================================================

SELECT 
    pc.id,
    pc.id AS registro_id,
    u.id AS empleado_id,
    u.name AS empleado,
    f.cai AS factura,
    p.nombre AS producto,
    pc.cantidad,
    pc.monto_comision,
    DATE_FORMAT(fc.fecha_cierre_factura, '%Y-%m-%d') AS fecha
FROM producto_comision pc
INNER JOIN facturas_comision fc ON fc.id = pc.facturas_comision_id
INNER JOIN factura f ON f.id = fc.factura_id
INNER JOIN producto p ON p.id = pc.producto_id
INNER JOIN comision_empleado ce ON ce.rol_id = fc.rol_id
    AND YEAR(ce.mes_comision) = YEAR(fc.fecha_cierre_factura)
    AND MONTH(ce.mes_comision) = MONTH(fc.fecha_cierre_factura)
    AND ce.estado_id = 1
    AND ce.users_comision = @empleadoId
INNER JOIN users u ON u.id = ce.users_comision
WHERE fc.fecha_cierre_factura BETWEEN @fechaInicio AND @fechaFin
    AND fc.estado_id = 1
ORDER BY pc.id DESC;


-- ============================================================================
-- 2. REPORTE POR ROL
-- ============================================================================
-- Descripción: Muestra el total de comisiones agrupado por rol y empleado.
--              Solo incluye empleados que tienen comisiones registradas.
--              Usa subquery para evitar duplicados por registros acumulados.
-- Filtros: Opcional filtrar por rol específico (@rolId)
-- ============================================================================

SELECT 
    CONCAT(sub.rol_id, '-', sub.user_id) AS id,
    sub.rol,
    sub.empleado,
    SUM(sub.monto_rol) AS total_comisiones,
    COUNT(DISTINCT sub.factura_id) AS num_facturas
FROM (
    SELECT 
        r.id AS rol_id,
        r.nombre AS rol,
        u.id AS user_id,
        u.name AS empleado,
        fc.id AS factura_id,
        fc.monto_rol
    FROM facturas_comision fc
    INNER JOIN rol r ON r.id = fc.rol_id
    INNER JOIN comision_empleado ce ON ce.rol_id = fc.rol_id
        AND YEAR(ce.mes_comision) = YEAR(fc.fecha_cierre_factura)
        AND MONTH(ce.mes_comision) = MONTH(fc.fecha_cierre_factura)
        AND ce.estado_id = 1
    INNER JOIN users u ON u.id = ce.users_comision
    WHERE fc.fecha_cierre_factura BETWEEN @fechaInicio AND @fechaFin
        AND fc.estado_id = 1
        AND r.estado_id = 1
        -- AND r.id = @rolId  -- Descomenta esta línea para filtrar por rol específico
) AS sub
GROUP BY sub.rol_id, sub.rol, sub.user_id, sub.empleado
ORDER BY sub.rol_id, sub.user_id;


-- ============================================================================
-- 3. REPORTE GENERAL POR USUARIO
-- ============================================================================
-- Descripción: Muestra todos los usuarios con comisiones, su rol, total de
--              comisiones, cantidad de facturas y productos.
--              Usa subquery para evitar duplicados por registros acumulados.
-- Filtros: Solo muestra usuarios con comisiones activas en el rango de fechas
-- ============================================================================

SELECT 
    sub.user_id AS id,
    sub.usuario,
    sub.rol,
    SUM(sub.monto_rol) AS total_comisiones,
    COUNT(DISTINCT sub.factura_id) AS num_facturas,
    COUNT(DISTINCT sub.producto_id) AS num_productos
FROM (
    SELECT 
        u.id AS user_id,
        u.name AS usuario,
        r.id AS rol_id,
        r.nombre AS rol,
        fc.id AS factura_id,
        fc.monto_rol,
        pc.producto_id
    FROM facturas_comision fc
    INNER JOIN rol r ON r.id = fc.rol_id
    INNER JOIN comision_empleado ce
        ON ce.id = (
            SELECT MAX(ce2.id)
            FROM comision_empleado ce2
            WHERE ce2.rol_id = fc.rol_id
              AND ce2.estado_id = 1
              AND YEAR(ce2.mes_comision) = YEAR(fc.fecha_cierre_factura)
              AND MONTH(ce2.mes_comision) = MONTH(fc.fecha_cierre_factura)
        )
    INNER JOIN users u ON u.id = ce.users_comision
    INNER JOIN producto_comision pc ON pc.facturas_comision_id = fc.id
    WHERE fc.fecha_cierre_factura BETWEEN @fechaInicio AND @fechaFin
        AND fc.estado_id = 1
        AND r.estado_id = 1
) AS sub
GROUP BY sub.user_id, sub.usuario, sub.rol_id, sub.rol
ORDER BY sub.user_id;


-- ============================================================================
-- 4. REPORTE POR PRODUCTO
-- ============================================================================
-- Descripción: Muestra cada producto con su cantidad total vendida y total de
--              comisiones acumuladas.
-- Agrupación: SOLO por producto (no multiplica filas por empleados)
-- ============================================================================

SELECT
    p.id,
    p.nombre AS producto,
    p.codigo_barra,
    MAX(pc.cantidad) AS cantidad_vendida,
    SUM(pc.cantidad * pc.monto_comision) AS total_comisiones

FROM producto_comision pc
INNER JOIN facturas_comision fc
    ON fc.id = pc.facturas_comision_id
INNER JOIN producto p
    ON p.id = pc.producto_id

WHERE fc.fecha_cierre_factura BETWEEN @fechaInicio AND @fechaFin
  AND fc.estado_id = 1

GROUP BY
    p.id,
    p.nombre,
    p.codigo_barra

ORDER BY total_comisiones DESC;


-- ============================================================================
-- 5. REPORTE POR FACTURA
-- ============================================================================
-- Descripción: Muestra cada factura con el empleado que comisionó, cliente,
--              total de venta y comisión generada.
--              Usa DISTINCT para evitar duplicados por registros acumulados.
-- Nota: Una factura puede aparecer varias veces si comisionó más de un empleado
-- ============================================================================

SELECT 
    CONCAT(fc.id, '-', u.id) AS id,
    v.cai AS factura,
    cl.nombre AS cliente,
    u.name AS empleado,
    v.total AS total_venta,
    fc.monto_rol AS total_comision,
    DATE_FORMAT(fc.fecha_cierre_factura, '%Y-%m-%d') AS fecha
FROM facturas_comision fc
INNER JOIN comision_empleado ce
    ON ce.id = (
        SELECT MAX(ce2.id)
        FROM comision_empleado ce2
        WHERE ce2.rol_id = fc.rol_id
          AND ce2.estado_id = 1
          AND YEAR(ce2.mes_comision) = YEAR(fc.fecha_cierre_factura)
          AND MONTH(ce2.mes_comision) = MONTH(fc.fecha_cierre_factura)
    )
INNER JOIN users u ON u.id = ce.users_comision
INNER JOIN factura v ON v.id = fc.factura_id
INNER JOIN cliente cl ON cl.id = v.cliente_id
WHERE fc.fecha_cierre_factura BETWEEN @fechaInicio AND @fechaFin
    AND fc.estado_id = 1
ORDER BY fc.fecha_cierre_factura DESC, v.cai;


-- ============================================================================
-- CONSULTAS AUXILIARES ÚTILES
-- ============================================================================

-- Verificar comisiones de un empleado específico en un mes
SELECT 
    ce.id,
    ce.mes_comision,
    u.name AS empleado,
    r.nombre AS rol,
    ce.estado_id
FROM comision_empleado ce
INNER JOIN users u ON u.id = ce.users_comision
INNER JOIN rol r ON r.id = ce.rol_id
WHERE ce.users_comision = @empleadoId
    AND YEAR(ce.mes_comision) = YEAR(@fechaInicio)
    AND MONTH(ce.mes_comision) = MONTH(@fechaInicio);


-- Ver facturas con comisiones en un rango de fechas
SELECT 
    fc.id,
    fc.factura_id,
    f.cai,
    fc.rol_id,
    r.nombre AS rol,
    fc.monto_rol,
    fc.fecha_cierre_factura,
    fc.estado_id
FROM facturas_comision fc
INNER JOIN factura f ON f.id = fc.factura_id
INNER JOIN rol r ON r.id = fc.rol_id
WHERE fc.fecha_cierre_factura BETWEEN @fechaInicio AND @fechaFin
ORDER BY fc.fecha_cierre_factura DESC;


-- Verificar productos con comisiones en una factura específica
SELECT 
    pc.id,
    pc.facturas_comision_id,
    pc.producto_id,
    p.nombre AS producto,
    pc.cantidad,
    pc.monto_comision
FROM producto_comision pc
INNER JOIN producto p ON p.id = pc.producto_id
INNER JOIN facturas_comision fc ON fc.id = pc.facturas_comision_id
WHERE fc.factura_id = 23910  -- Cambia por el ID de la factura que quieras verificar
ORDER BY pc.id;


-- ============================================================================
-- NOTAS IMPORTANTES:
-- ============================================================================
-- 1. Todas las consultas filtran por fecha_cierre_factura (no created_at)
-- 2. Se usa matching de mes/año entre comision_empleado.mes_comision y 
--    facturas_comision.fecha_cierre_factura
-- 3. Solo se muestran usuarios con comisiones activas (estado_id = 1)
-- 4. Las comisiones se relacionan por rol_id + mes/año coincidente
-- 5. En el reporte por producto, la cantidad y comisión son acumuladas
--    sin multiplicarse por la cantidad de empleados
-- 6. IMPORTANTE: comision_empleado es una tabla CATÁLOGO con datos acumulados.
--    Por eso iniciamos desde facturas_comision y usamos subqueries/DISTINCT
--    para evitar multiplicación de filas por registros históricos acumulados.
-- ============================================================================
