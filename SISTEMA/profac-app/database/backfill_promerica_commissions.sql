-- Backfills historical Promerica direct payments without changing the amount
-- already applied to customer invoices.
-- The original net amount is used to reconstruct the amount received:
-- received = net applied / 0.97; the commission is rounded to cents.
-- Review the preview before running the UPDATE and COMMIT.

START TRANSACTION;

SELECT
    COUNT(*) AS abonos_a_actualizar,
    ROUND(SUM(ac.monto_abonado), 2) AS monto_aplicado,
    ROUND(SUM(ROUND(ac.monto_abonado / 0.97, 2)), 2) AS movimiento_reconstruido,
    ROUND(SUM(ROUND(ROUND(ac.monto_abonado / 0.97, 2) * 0.03, 2)), 2) AS comision_reconstruida
FROM abonos_creditos ac
INNER JOIN banco b ON b.id = ac.banco_id
WHERE UPPER(TRIM(b.nombre)) = 'PROMERICA'
  AND b.cuenta = '80000000578176'
  AND ac.estado_abono = 1
  AND (ac.monto_recibido IS NULL OR ac.comision_cobrada IS NULL);

UPDATE abonos_creditos ac
INNER JOIN banco b ON b.id = ac.banco_id
SET
    ac.monto_recibido = ROUND(ac.monto_abonado / 0.97, 2),
    ac.comision_cobrada = ROUND(ROUND(ac.monto_abonado / 0.97, 2) * 0.03, 2)
WHERE UPPER(TRIM(b.nombre)) = 'PROMERICA'
  AND b.cuenta = '80000000578176'
  AND ac.estado_abono = 1
  AND (ac.monto_recibido IS NULL OR ac.comision_cobrada IS NULL);

SELECT ROW_COUNT() AS abonos_actualizados;

COMMIT;
