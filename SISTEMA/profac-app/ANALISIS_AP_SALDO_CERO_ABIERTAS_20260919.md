# Análisis de AP con saldo cero y cierre pendiente

- Fecha del análisis: 2026-09-19
- Base consultada: `profac_app_produccion`
- Facturas afectadas: **3160**
- Con abono activo: **3099**
- Sin abono, con otro movimiento reductor: **61**
- Con comisión activa: **0** (criterio del reporte)
- Con Ana Pineda como facturadora, vendedora o gestora: **1048**
- Sin fecha efectiva identificable: **36**
- Saldos negativos abiertos, excluidos de este universo: **3162**

## Estado de la factura de referencia

Durante la verificación final, la AP `37004` de la factura
`000-001-01-00044288` ya figuraba con `estado_cerrado = 2` y
`fecha_cierre_factura = 2026-09-10 09:46:38`, pero continuaba sin registros en
`facturas_comision`. Por ese cambio de estado ya no aparece en el CSV de AP
abiertas. Su comisión todavía debe reprocesarse.

También existen **21275** facturas activas con AP cerrada, saldo cero, evidencia
de reducción y sin comisión activa. No se mezclan con este reporte porque ya no
presentan la misma inconsistencia de cierre; requieren validar antigüedad,
configuración comisionable y períodos conciliados antes de cualquier reproceso.

## Criterio

Factura activa con AP activa, saldo dentro de ±0.0001, `estado_cerrado` distinto de 2, sin comisión activa y con evidencia de abono, nota de crédito, retención aplicada u otro movimiento reductor.

La coincidencia demuestra la misma causa inmediata de exclusión que la factura `000-001-01-00044288`. No demuestra por sí sola que toda factura histórica deba pagarse: antes de reprocesar deben validarse período conciliado, escalas, roles y anulaciones.

## Distribución mensual

| Mes efectivo sugerido | Facturas |
|---|---:|
| 0024-07 | 1 |
| 0026-04 | 1 |
| 2022-10 | 2 |
| 2022-11 | 2 |
| 2022-12 | 9 |
| 2023-01 | 1 |
| 2023-02 | 4 |
| 2023-03 | 2 |
| 2023-04 | 4 |
| 2023-05 | 2 |
| 2023-06 | 3 |
| 2023-07 | 10 |
| 2023-08 | 3 |
| 2023-09 | 4 |
| 2023-10 | 4 |
| 2023-11 | 18 |
| 2023-12 | 18 |
| 2024-02 | 6 |
| 2024-03 | 30 |
| 2024-04 | 66 |
| 2024-05 | 73 |
| 2024-06 | 76 |
| 2024-07 | 71 |
| 2024-08 | 75 |
| 2024-09 | 58 |
| 2024-10 | 60 |
| 2024-11 | 47 |
| 2024-12 | 98 |
| 2025-01 | 19 |
| 2025-02 | 18 |
| 2025-03 | 113 |
| 2025-04 | 63 |
| 2025-05 | 50 |
| 2025-06 | 34 |
| 2025-07 | 85 |
| 2025-08 | 28 |
| 2025-09 | 11 |
| 2025-10 | 9 |
| 2025-11 | 10 |
| 2025-12 | 9 |
| 2026-01 | 10 |
| 2026-02 | 41 |
| 2026-03 | 805 |
| 2026-04 | 635 |
| 2026-05 | 426 |
| 2026-06 | 2 |
| 2026-07 | 5 |
| 2026-08 | 2 |
| 2027-07 | 1 |
| SIN_FECHA | 36 |

## Detalle

El universo completo está en `AP_SALDO_CERO_ABIERTAS_20260919.csv`.
