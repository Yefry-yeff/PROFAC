# Continuidad del módulo de auditoría de inventario

Última actualización: 21 de noviembre de 2026.

## Cómo retomar el trabajo

Antes de modificar este módulo, leer este documento y revisar los archivos indicados en la sección
**Archivos principales**. El módulo es independiente del Kardex existente y no debe alterar la pantalla
ni la lógica original de `/cardex`.

Pantalla del módulo:

- `/auditoria/compras-ventas`

## Objetivo

Auditar cronológicamente las compras, ventas, comprobantes, ajustes y notas de crédito de un universo
controlado de productos. Los reportes principales son:

1. Excel combinado con hojas `Facturas A`, `Facturas B` y `Reporte completo`.
2. Excel Facturas A con una hoja por producto.
3. Excel Facturas B con una hoja por producto.
4. CSV completo desde la pantalla.

## Universo de productos

- El universo actual contiene 84 productos.
- La fuente única es `ConsultaComprasVentas::PRODUCTOS_AUDITADOS`.
- El producto 1960 fue agregado después de detectar una omisión.
- El producto 4591 ya está incluido.
- Los Excel por producto deben contener exactamente 84 hojas.
- El producto 8069 no tiene movimientos dentro del período 2022-2025 y su hoja puede quedar en cero.

## Período de los Excel Kardex

- Desde: 2022-11-01.
- Hasta: 2025-12-31.
- `Cantidad inicial` debe mostrarse en cero.
- No deben omitirse ventas anteriores a la primera compra mostrada.

## Clasificación A/B

- Factura corporativa, `tipo_venta_id = 1`: B.
- Factura estatal, `tipo_venta_id = 2`: A.
- Factura exonerada, `tipo_venta_id = 3`: se incluye en A únicamente para los Excel Kardex.
- La clasificación fiscal almacenada de las exoneradas no se reescribe.
- Compras, comprobantes y ajustes aparecen como movimientos de soporte en ambos Kardex.

## Columnas actuales del Kardex

1. Fecha de registro.
2. Número de factura comercial/documento.
3. Código.
4. Descripción de mercancía.
5. Unidad de medida.
6. Cantidad inicial.
7. Compra.
8. Venta.
9. Comprobante.
10. Ajuste.
11. Nota de crédito.
12. Saldo / existencia.
13. Costos.
14. Número de declaración.

Reglas:

- `Compra`: cantidad de compras.
- `Venta`: cantidad de facturas, expresada positiva en su columna y restada del saldo.
- `Comprobante`: cantidad documental; no afecta el saldo para evitar duplicar la salida con la factura.
- `Ajuste`: cantidad firmada; positiva para entrada y negativa para salida.
- `Nota de crédito`: devolución física de producto que incrementa inventario.
- `Saldo = Compra - Venta + Ajuste + Nota de crédito`.

## Ajuste inicial compensatorio

Cuando la secuencia del período produciría un saldo negativo, se agrega:

`AJUSTE INICIAL COMPENSATORIO - HISTORIAL PREVIO INCOMPLETO`

Características:

- Es una fila sintética del Excel.
- No es un ajuste almacenado en la base de datos.
- Es el valor absoluto del menor saldo acumulado negativo.
- Mantiene `Cantidad inicial` en cero.
- Evita eliminar facturas o mostrar saldos negativos.

Caso auditado, producto 1946:

- Producto: `PAPEL BOND CARTA 70G PAPERLINE 8.5"X11" (500H)`.
- Ajuste compensatorio: 15,633 unidades.
- Antes de la primera compra mostrada existían:
  - 31 líneas de venta en 26 facturas: -15,614.
  - 17 ajustes: impacto neto -19.
  - 1 comprobante informativo: impacto 0.
- Primera compra dentro del período:
  - Fecha: 2022-12-16 15:53:35.
  - Compra ID: 103.
  - Documento: `220004136583M`.
  - Cantidad: 55,600.
- Compras anteriores al período:
  - 2022-09-16, documento `1046`: 11,120.
  - 2022-10-18, documento `1001`: 29,547.

Interpretación: las 15,633 unidades representan la existencia previa mínima no mostrada requerida para
respaldar las salidas anteriores a la primera compra incluida.

## Notas de crédito

Fuente física:

- Devolución: filas de `log_translado` con descripción `Devolucion de producto`.
- Reversa por anulación: filas de `log_translado` con descripción `Nota credito anulado`.
- Las notas únicamente por descuento no generan movimientos de inventario.

Sincronización histórica validada:

- 519 notas.
- 508 notas con productos.
- 11 notas únicamente por descuento.
- 1,395 devoluciones físicas.
- 1 reversa física.
- Cero cantidades discordantes.
- Cero claves duplicadas.

Tipos de auditoría:

- `NOTA_CREDITO`.
- `ANULACION_NOTA_CREDITO`.

La tabla `auditoria_compras_ventas` contiene `nota_credito_id`.

### Diferencia entre ID interno y número comercial

La URL de impresión usa el ID interno:

- `/nota/credito/imprimir/{id}`

Caso verificado:

- ID interno 153 = número comercial `2024-151`, CAI `000-001-06-00001587`.
- ID interno 155 = número comercial `2024-153`, CAI `000-001-06-00001589`.
- La nota comercial `2024-153`, ID 155, contiene el producto 2794 con cantidad 2.

La lista de notas ahora:

- Muestra `ID Nota` y `N.º Nota` por separado.
- Busca por número comercial, secuencia CAI, CAI completo o ID.
- La búsqueda directa ignora el rango de fechas.

## Comprobantes

- El inventario físico se retiró al crear el comprobante.
- La conversión posterior a factura no debe retirar inventario nuevamente.
- Los vínculos históricos comprobante-factura no son suficientemente confiables.
- Por eso el comprobante se muestra documentariamente, con `Afecta inventario = NO`.

## Ajustes y anulaciones

- `AJUSTE`: movimiento firmado proveniente de `ajuste_has_producto`.
- `ANULACION_AJUSTE`: reversa proveniente del log real de inventario.
- No crear anulaciones teóricas; utilizar únicamente logs físicos.

## Facturas históricas

El acceso histórico completo en las listas de facturas está permitido exclusivamente para:

- `yefryyo@gmail.com`

Reglas:

- Solo facturas activas.
- La búsqueda acepta CAI, número comercial e ID interno.
- Los demás usuarios mantienen el límite normal de dos años.
- No se ampliaron permisos de anulación o modificación.

Casos verificados:

- Factura `000-001-01-00026546`:
  - Exonerada e incluida en A.
  - Dos líneas dentro del universo auditado.
  - Productos 2794 y 4347.
- Factura `000-001-01-00036376`:
  - Factura A.
  - Tres líneas auditadas del producto 5830.

## Producto 4591

- Ya pertenece al universo de 84 productos.
- Última generación validada:
  - 4 movimientos en Facturas A.
  - 1 movimiento en Facturas B.
  - Hoja 4591 presente en ambos libros por producto.

## Base de datos

Migraciones principales:

- `2026_11_19_080000_create_auditoria_compras_ventas_table.php`.
- `2026_11_20_100000_add_comprobantes_ajustes_to_auditoria_compras_ventas.php`.
- `2026_11_20_110000_add_notas_credito_to_auditoria_compras_ventas.php`.

La migración de notas de crédito fue aplicada y se ejecutó una sincronización completa.
Una sincronización incremental inmediatamente posterior produjo cero cambios, confirmando idempotencia.

## Archivos principales

- `app/Services/Auditoria/ConsultaComprasVentas.php`
  - Universo de productos, filtros, unión de movimientos y orden cronológico.
- `app/Services/Auditoria/SincronizadorComprasVentas.php`
  - Sincronización de compras, ventas, comprobantes, ajustes, notas y reversas.
- `app/Services/Auditoria/ExportadorMovimientosXlsx.php`
  - Generación de los tres tipos de Excel, compensaciones, columnas y totales.
- `app/Livewire/Auditoria/ComprasVentas.php`
  - Pantalla, resumen, sincronización, CSV y descargas.
- `resources/views/livewire/auditoria/compras-ventas.blade.php`
  - Interfaz y explicación del alcance.
- `public/js/js_proyecto/auditoria/comprasVentas.js`
  - DataTable, filtros y botones de descarga.
- `app/Support/AccesoFacturas.php`
  - Acceso histórico exclusivo.
- `app/Support/FiltrosNotasCredito.php`
  - Búsqueda histórica de notas por número, CAI, secuencia o ID.
- `app/Livewire/NotaCredito/ListadoNotaCredito.php`
  - Lista de notas A y datos mostrados.

## Validaciones realizadas

- 84 productos exactos en los Excel.
- 84 hojas únicas en cada libro por producto.
- Encabezados y posiciones de columnas validados.
- Totales consolidados reconciliados.
- Sin saldos negativos después del ajuste compensatorio.
- Notas de crédito reconciliadas contra logs físicos.
- Facturas conocidas 26546 y 36376 conservadas.
- Pruebas de filtros de notas:
  - 28 pruebas aprobadas.
  - 216 afirmaciones.
- Prueba de acceso histórico:
  - 1 prueba aprobada.
  - 4 afirmaciones.

## Comandos útiles

PHP:

`C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`

Ejecutar pruebas de filtros de notas:

`php artisan test --filter=FiltrosNotasCreditoTest`

Ejecutar prueba de acceso histórico:

`php artisan test --filter=AccesoFacturasTest`

## Reglas para futuras modificaciones

- No modificar ni reemplazar la lógica original de `/cardex`.
- Mantener el módulo de auditoría aislado.
- No excluir facturas activas para evitar saldos negativos.
- No volver a utilizar el corte de “primera compra”.
- Mantener Cantidad inicial en cero salvo instrucción explícita del usuario.
- Utilizar movimientos físicos reales para notas y anulaciones.
- No duplicar el impacto de comprobantes y facturas.
- Validar siempre los tres Excel después de modificar aritmética o columnas.
