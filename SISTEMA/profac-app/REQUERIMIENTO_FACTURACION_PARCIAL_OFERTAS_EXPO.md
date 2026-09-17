# Requerimiento: facturación parcial de ofertas Expo

## 1. Información general

**Módulo:** Flujo de ventas / Ofertas Expo  
**Nombre funcional:** Facturación parcial de ofertas Expo y liquidación de descuentos  
**Versión del documento:** 2.0
**Fecha:** 19 de agosto de 2026
**Estado:** Requerimiento funcional y técnico actualizado

## 2. Objetivo

Permitir que una oferta de Expo genere múltiples prefacturas y facturas, incluyendo cantidades parciales de una misma línea, sin facturar productos o cantidades superiores a lo autorizado en la oferta.

El descuento firmado en la oferta se concederá proporcionalmente por cada línea facturada. Al cerrar el flujo o vencer su plazo, el sistema recalculará los escalones alcanzados con todas las facturas activas y recuperará únicamente el descuento no ganado mediante un movimiento de tipo **aumento** en cuentas por cobrar.

## 3. Alcance

El requerimiento comprende:

1. Facturación parcial de productos y cantidades de una oferta Expo.
2. Varias prefacturas y facturas dentro del mismo flujo.
3. Consulta de cantidades ofertadas, facturadas, anuladas, descartadas y pendientes.
4. Selección de productos individualmente o por marca.
5. Descuento general parametrizado por el total acumulado efectivamente facturado.
6. Descuento por marca acumulado por flujo y evaluado por escalones.
7. Identificación automática o manual de la última factura.
8. Liquidación mediante movimientos existentes de aumento y disminución.
9. Aplicación automática del aumento al cumplirse una regla de cierre.
10. Reversión del aumento mediante disminución al reabrir un flujo.
11. Ajustes en anulaciones, inventario, entregas, cobros, comisiones, reportes y visualización del flujo.
12. Auditoría y controles de concurrencia.

No se generará nota de débito ni nota de crédito por esta liquidación. Los ajustes usarán `otros_movimientos`: `tipo_movimiento = 1` para aumento y `tipo_movimiento = 2` para disminución, conservando una relación auditable con la oferta Expo.

## 4. Definiciones

### 4.1 Oferta Expo

Documento maestro que establece:

- Cliente.
- Productos autorizados.
- Cantidades máximas.
- Precio y unidad de medida.
- Bodega o condición de inventario.
- Marca de cada producto.
- Condiciones de descuento vigentes.
- Vigencia comercial.

La oferta representa el máximo que puede facturarse. El cliente no está obligado a comprarla completamente.

### 4.2 Línea de oferta

Registro específico del detalle de la oferta. Su identidad no debe depender únicamente de `producto_id`, porque un producto puede aparecer con diferente precio, unidad, bodega o sección.

Cada detalle de prefactura y factura deberá conservar la referencia a la línea de oferta que le dio origen.

### 4.3 Cantidad pendiente

Para cada línea:

```text
cantidad_pendiente = cantidad_ofertada
                   - cantidad_facturada_activa
                   - cantidad_descartada_al_cierre
```

Las cantidades de facturas anuladas no se consideran facturadas activas y deben volver a estar disponibles, salvo que la oferta se encuentre cerrada definitivamente.

### 4.4 Factura parcial

Factura que contiene uno o varios productos de la oferta, incluyendo una cantidad menor que la ofertada para una línea.

Una misma línea puede aparecer en varias facturas siempre que la suma de cantidades activas no supere la cantidad ofertada.

### 4.5 Cierre de oferta

Momento en el cual no se crearán más facturas de la oferta. Puede producirse por facturación total o por decisión del cliente.

## 5. Reglas de negocio

### RN-01. Múltiples documentos por flujo

Una oferta Expo podrá generar:

- Una o varias prefacturas.
- Una o varias facturas.
- Una liquidación final de descuentos.
- Uno o varios aumentos, y sus disminuciones de reversión cuando corresponda.

Cada documento se registrará normalmente en su tabla y tendrá un registro independiente en `historico_flujo`.

### RN-02. Facturación limitada a la oferta

Solo podrán facturarse líneas existentes en la oferta Expo seleccionada.

No se permitirá:

- Agregar productos externos.
- Cambiar una línea por otro producto.
- Superar la cantidad pendiente.
- Alterar la referencia de la línea de oferta.
- Facturar una unidad de medida, precio o condición no autorizada sin el proceso de autorización existente.

### RN-03. Cantidades parciales

El usuario podrá seleccionar cualquier cantidad mayor que cero y menor o igual a la cantidad pendiente de la línea.

Ejemplo:

```text
Cantidad ofertada:       100
Primera factura:          30
Segunda factura:          45
Cantidad pendiente:       25
Máximo siguiente factura: 25
```

La línea permanecerá disponible mientras tenga cantidad pendiente y la oferta permanezca abierta.

### RN-04. Control transaccional

Antes de guardar cada prefactura o factura, el servidor recalculará las cantidades facturadas activas y pendientes dentro de una transacción con bloqueo de los registros necesarios.

La validación de la interfaz no sustituye esta comprobación. Esto debe impedir sobrefacturación por doble clic, pestañas simultáneas o usuarios concurrentes.

### RN-05. Selección por marca

En la facturación de Expo se incluirán las siguientes opciones:

- Seleccionar una línea individual.
- Seleccionar todas las líneas pendientes de una marca.
- Seleccionar todas las marcas pendientes.
- Quitar una marca o línea antes de facturar.

Al seleccionar una marca, el sistema agregará sus líneas con sus cantidades pendientes. El usuario podrá reducir las cantidades antes de confirmar.

### RN-06. Descuento general

El descuento general se determinará al cerrar la oferta usando el subtotal bruto efectivamente facturado en todas las facturas activas del flujo.

No se utilizará el total original de la oferta para decidir la escala alcanzada.

```text
subtotal_facturado_oferta = suma de subtotales brutos de facturas activas del flujo
```

Se seleccionará la escala parametrizada más alta cuyo mínimo haya sido alcanzado.

```text
descuento_general = base_general * porcentaje_general_alcanzado
```

Las facturas anuladas no participarán en este cálculo.

### RN-07. Descuento por marca acumulado y escalonado

El descuento por marca se evaluará acumulando todas las líneas facturadas activas de la misma marca dentro del flujo.

Para cada marca:

```text
subtotal_marca_flujo = suma del subtotal bruto de líneas activas de esa marca en todas las facturas del flujo
```

```text
porcentaje_marca = porcentaje del escalón más alto cuyo mínimo sea alcanzado
descuento_marca_ganado = subtotal_marca_flujo * porcentaje_marca
```

Ejemplo:

```text
Escalón Marca A: L 6,000.00 -> 5%
Escalón Marca A: L 10,000.00 -> 10%
Total acumulado al cierre: L 5,000.00 -> 0%
Total acumulado al cierre: L 7,000.00 -> 5%
Total acumulado al cierre: L 10,000.00 -> 10%
```

### RN-08. Orden de los descuentos

Para evitar doble descuento sobre la misma base, se propone:

1. Calcular descuento por marca sobre el subtotal bruto elegible de cada factura.
2. Restar los descuentos por marca de la base general.
3. Calcular el descuento general sobre la base restante.

```text
base_general = subtotal_facturado_oferta - descuentos_por_marca
descuento_total = descuentos_por_marca + descuento_general
```

Este orden es la regla aprobada: marca primero y descuento general sobre la base restante.

### RN-08.1 Descuento firmado y aumento

Cada línea facturada recibe la proporción del descuento ya firmado en la oferta:

```text
descuento_otorgado_linea = descuento_linea_oferta * cantidad_facturada / cantidad_ofertada
aumento = max(descuento_otorgado_acumulado - descuento_ganado_al_cierre, 0)
```

El aumento no se registra al emitir cada factura. Se registra una sola vez cuando el flujo se cierra, la Expo se cierra por gerencia o vence el plazo autorizado para facturar.

### RN-09. Cálculo en servidor

Todos los descuentos se recalcularán en el servidor usando:

- Facturas activas.
- Detalles activos.
- Marca vigente guardada en la instantánea de la oferta.
- Reglas congeladas de la oferta.
- Valores monetarios sin formato.

El servidor no confiará en porcentajes o montos calculados por JavaScript.

### RN-10. Congelamiento de condiciones

La oferta conservará una instantánea de las reglas aplicables al momento de su creación o aprobación:

- Escalas del descuento general.
- Marca.
- Mínimo por marca.
- Porcentaje por marca.
- Orden de aplicación.
- Precios y cantidades autorizadas.

Cambios posteriores en la configuración de la Expo no modificarán ofertas existentes.

### RN-11. Determinación de la última factura

La factura será considerada la última en cualquiera de estos casos:

1. **Cierre automático:** después de guardarla, todas las líneas tienen cantidad pendiente igual a cero.
2. **Cierre manual:** el usuario marca `Esta es la última factura solicitada por el cliente`.

En el cierre manual se solicitará:

- Confirmación explícita.
- Motivo obligatorio.
- Usuario y fecha.

Las cantidades restantes se registrarán como no facturadas por decisión del cliente.

### RN-12. Estado posterior al cierre

Después del cierre no podrán crearse nuevas prefacturas ni facturas para la oferta.

Una reapertura requerirá autorización, motivo y auditoría. Si ya existe un aumento Expo, el sistema registrará una disminución por el mismo monto antes de habilitar nuevas facturas.

### RN-13. Resumen de liquidación

Cuando se guarde la última factura, el sistema mostrará un modal con:

- Total original de la oferta.
- Total efectivamente facturado.
- Productos y cantidades no facturados.
- Descuento general alcanzado.
- Escalón acumulado alcanzado por cada marca.
- Descuento otorgado en las facturas.
- Descuento ganado al cierre.
- Aumento que se aplicó por diferencia.
- Facturas y movimientos de aumento generados.

La liquidación se ejecutará automáticamente dentro de la transacción de cierre; el resumen será informativo.

### RN-14. Aplicación del aumento

El aumento se distribuirá entre las facturas activas del mismo flujo proporcionalmente al descuento firmado que recibió cada factura. Por cada asignación se creará un registro en `otros_movimientos` con `tipo_movimiento = 1` y se aplicará mediante la lógica vigente de cuentas por cobrar.

Ejemplo:

```text
Descuento otorgado:          L 500.00
Descuento ganado al cierre:  L 250.00
Aumento a recuperar:         L 250.00
```

La generación y aplicación deberán realizarse en una sola transacción y ser idempotentes.

### RN-15. Reapertura y disminución

La reapertura total de la Expo o individual de un flujo deberá:

- Identificar los aumentos Expo activos del flujo.
- Registrar una disminución (`tipo_movimiento = 2`) por el monto previamente aumentado.
- Relacionar la disminución con el aumento que revierte.
- Cambiar el flujo a `FACTURACION_PARCIAL` o `PENDIENTE_FACTURACION`.
- Permitir continuar facturando sin duplicar ajustes.

### RN-16. Anulación de una factura parcial

La anulación afectará exclusivamente la factura seleccionada y sus documentos dependientes.

No deberá:

- Anular las demás facturas del flujo.
- Quitar automáticamente la oferta ganadora.
- Cerrar todo el flujo si existen otras facturas activas.

Las cantidades anuladas volverán al saldo pendiente si la oferta está abierta.

Si la oferta está cerrada o existe un aumento aplicado, la anulación requerirá reversión mediante disminución y recálculo previo.

### RN-17. Inventario

Cada prefactura reservará solamente la cantidad seleccionada para esa parcialidad.

Cada factura consumirá únicamente sus cantidades. Una anulación devolverá al inventario las cantidades correspondientes siguiendo el mecanismo actual del sistema.

No deberá reservarse toda la oferta automáticamente.

### RN-18. Crédito

Para ventas al crédito deberá validarse el disponible del cliente antes de cada factura parcial.

El consumo de crédito se determinará con facturas activas y saldos reales, no con el total completo de la oferta.

### RN-19. Entrega y cobro

Cada factura tendrá entrega y cobro independientes.

El flujo general solo podrá marcarse como completado cuando:

- La oferta esté cerrada.
- Todas las facturas activas estén entregadas o no requieran entrega.
- Todas las facturas activas estén pagadas, compensadas o resueltas contablemente.
- La liquidación de descuentos esté completada.

### RN-20. Comisiones

Las comisiones se calcularán sobre valores netos después de los descuentos efectivamente concedidos.

Los aumentos y disminuciones Expo deberán reflejarse en la base neta usada por comisiones cuando corresponda.

## 6. Estados funcionales de la oferta Expo

Los estados funcionales requeridos son:

| Estado | Descripción |
|---|---|
| `PENDIENTE_FACTURACION` | No tiene facturas activas y conserva cantidades pendientes. |
| `FACTURACION_PARCIAL` | Tiene facturas activas y cantidades pendientes. |
| `FACTURADA_TOTAL` | Todas las cantidades fueron facturadas. |
| `CERRADA_PARCIAL` | El cliente no comprará las cantidades restantes. |
| `PENDIENTE_LIQUIDACION` | Estado transitorio o legado antes de aplicar el aumento. |
| `LIQUIDADA` | Descuentos calculados y aumento aplicado correctamente, o aumento igual a cero. |
| `CANCELADA` | Oferta cancelada sin posibilidad de seguir facturando. |

Estos estados podrán derivarse de documentos y auditoría existentes o almacenarse en la cabecera de la oferta/cotización. No se creará una tabla adicional de documentos del flujo.

## 7. Flujo operativo

### 7.1 Primera factura parcial

1. El usuario abre la oferta Expo ganadora.
2. El sistema consulta líneas y cantidades pendientes.
3. El usuario selecciona productos individualmente o por marca.
4. Define cantidades parciales.
5. El sistema muestra una simulación de descuentos por marca para esa factura.
6. El usuario guarda la prefactura, si aplica el proceso normal.
7. Se genera la factura con las cantidades seleccionadas.
8. Se registra el documento en `historico_flujo` sin reemplazar facturas anteriores.
9. La oferta queda en `FACTURACION_PARCIAL` si mantiene saldo.

### 7.2 Facturas posteriores

1. El usuario vuelve a abrir el mismo flujo.
2. El sistema muestra todas las facturas anteriores.
3. Solo presenta cantidades pendientes de la oferta.
4. Se repite el proceso de selección y facturación.
5. Los montos de cada factura se acumulan por marca para determinar el escalón final.

### 7.3 Cierre automático

1. Se factura la última cantidad pendiente.
2. El servidor detecta saldo cero en todas las líneas.
3. La factura se marca como última.
4. Se calcula y aplica automáticamente el aumento si corresponde.
5. Se muestra el resumen de liquidación.

### 7.4 Cierre manual

1. El usuario selecciona `Esta es la última factura solicitada por el cliente`.
2. Confirma y registra motivo.
3. Se guarda la factura.
4. Las cantidades restantes se descartan para facturación.
5. Se calcula y aplica automáticamente el aumento si corresponde.
6. Se muestra el resumen de liquidación.

## 8. Interfaz requerida

### 8.1 Encabezado

Mostrar:

- Número de oferta.
- Expo de origen.
- Cliente.
- Estado de facturación.
- Total ofertado.
- Total facturado activo.
- Cantidad de facturas.
- Saldo de productos pendiente.

### 8.2 Selector de productos

Columnas mínimas:

- Selección.
- Marca.
- Código.
- Producto.
- Unidad.
- Precio autorizado.
- Cantidad ofertada.
- Cantidad facturada.
- Cantidad pendiente.
- Cantidad para esta factura.
- Subtotal de esta factura.

### 8.3 Acciones por marca

- `Agregar marca completa`.
- `Agregar todas las marcas`.
- `Quitar marca`.
- Filtro por marca.
- Indicador de subtotal seleccionado por marca.
- Indicador de mínimo requerido.
- Estado `Cumple descuento` o `No cumple descuento`.

### 8.4 Confirmación de última factura

Agregar una opción visible antes de guardar:

```text
[ ] Esta es la última factura solicitada por el cliente
```

Si existen cantidades pendientes, deberá abrirse una confirmación con el resumen de lo no facturado y solicitar motivo.

### 8.5 Modal del flujo

El paso Factura no mostrará solamente la factura más reciente. Deberá listar todos los documentos:

- Prefacturas.
- Facturas activas y anuladas.
- Estado de entrega por factura.
- Estado de cobro y saldo por factura.
- Notas de crédito y aplicaciones.

Cada factura deberá poder abrirse, imprimirse y consultar sus movimientos individualmente.

## 9. Persistencia y trazabilidad

### 9.1 Documentos del flujo

Se reutilizará `historico_flujo`:

- Un registro por prefactura.
- Un registro por factura.
- Registros independientes para entrega y cobro según el modelo vigente.
- Referencia auditable de cada aumento y de la disminución que lo revierta.

Ningún registro nuevo deberá reemplazar o sobrescribir documentos anteriores.

### 9.2 Referencia de línea original

Los detalles de prefactura y factura deberán guardar la referencia a `cotizacion_has_producto.id` o al detalle equivalente de la oferta.

Esto permitirá:

- Calcular cantidades consumidas correctamente.
- Distinguir el mismo producto en líneas diferentes.
- Reactivar cantidades al anular.
- Auditar el origen de cada unidad facturada.

### 9.3 Instantánea de descuentos

La oferta deberá conservar las reglas aplicables. Si la estructura actual no permite reglas por marca, deberá ampliarse la configuración de Expo y su instantánea de oferta.

Campos funcionales mínimos por regla de marca:

- Expo u oferta de origen.
- Marca.
- Mínimo por factura.
- Porcentaje de descuento.
- Vigencia o versión.
- Estado.

### 9.4 Auditoría

Registrar como mínimo:

- Usuario que creó cada parcialidad.
- Fecha y hora.
- Cantidades seleccionadas.
- Cálculo de descuento utilizado.
- Usuario que indicó la última factura.
- Motivo del cierre parcial.
- Usuario o proceso que cerró la Expo o el flujo.
- Aumentos y disminuciones realizadas.
- Reaperturas, anulaciones y reversiones.

## 10. Impactos en módulos existentes

### 10.1 Oferta y prefactura

- Cargar solamente cantidades pendientes.
- Permitir varias prefacturas por flujo para Expo.
- Evitar que una prefactura anterior bloquee otra parcialidad válida.
- Mantener reservas independientes.

### 10.2 Facturación

- Admitir varias facturas vinculadas al mismo flujo.
- Validar cantidades contra la línea original.
- Evitar reemplazar la factura anterior en el historial.
- Recalcular totales, descuentos e impuestos en servidor.

### 10.3 Inventario

- Reservar y descargar cantidades parciales.
- Restituir cantidades en anulaciones.
- Evitar reservas duplicadas.

### 10.4 Entregas

- Mostrar y gestionar cada factura del flujo.
- No completar globalmente el flujo mientras existan facturas pendientes.

### 10.5 Cobros y cartera

- Mantener saldos por factura.
- Aplicar el aumento mediante `otros_movimientos` y `sp_aplicacion_pagos`.
- Aplicar la disminución mediante la misma lógica vigente al reabrir.
- Actualizar el saldo después de cada movimiento.

### 10.6 Aumentos y disminuciones

- Distribuir el aumento entre las facturas según el descuento otorgado.
- Usar `tipo_movimiento = 1` para aumento y `tipo_movimiento = 2` para disminución.
- Mantener relación entre Expo, flujo, factura, aumento y disminución.
- Impedir aplicaciones duplicadas ante reintentos.

### 10.7 Comisiones

- Considerar aumentos y disminuciones de descuento Expo.
- Recalcular facturas o periodos afectados cuando corresponda.

### 10.8 Reportes

Agregar o ajustar reportes para mostrar:

- Total ofertado.
- Total facturado.
- Cantidad pendiente.
- Cantidad descartada.
- Facturas por oferta y flujo.
- Descuento general.
- Escalón acumulado por marca.
- Descuento otorgado, ganado y aumento aplicado.
- Disminuciones realizadas por reapertura.

Los reportes de cartera y cobros deberán reflejar los otros movimientos y saldos resultantes.

### 10.9 Anulaciones

- Anular solo el documento seleccionado.
- No anular automáticamente todo el flujo.
- Recalcular pendientes y estado de cierre.
- Controlar dependencias con entrega, cobro, comisión y otros movimientos.

### 10.10 Configuración y control de Expo

- Permitir al gerente cerrar una Expo completa.
- Al cerrar, bloquear nuevas facturas y liquidar automáticamente los flujos incompletos.
- Permitir reapertura total o por flujo con motivo y autorización.
- Al reabrir, generar la disminución que revierte el aumento del flujo.
- Si se intenta facturar una Expo cerrada, solicitar un código mediante el mecanismo de autorización vigente.

## 11. Seguridad y permisos

Se requieren permisos diferenciados para:

- Facturar parcialmente una oferta Expo.
- Indicar la última factura.
- Cerrar una oferta con cantidades pendientes.
- Cerrar una Expo completa.
- Aplicar aumentos y disminuciones Expo.
- Reabrir una oferta cerrada.
- Autorizar facturación excepcional sobre una Expo cerrada.
- Anular una factura con aumento aplicado.

Las acciones sensibles deberán usar el mecanismo de autorización vigente y quedar auditadas.

## 12. Definiciones contables aprobadas

1. La liquidación no genera nota de débito ni nota de crédito.
2. Se usa la lógica existente de otros movimientos.
3. El descuento de marca se calcula primero y el general sobre la base restante.
4. Los escalones se determinan con montos acumulados del flujo.
5. El aumento se aplica únicamente al cerrar o vencer el flujo.
6. La reapertura revierte el aumento mediante una disminución vinculada.

## 13. Criterios de aceptación

### CA-01. Parcialidad de una línea

Dada una línea de 100 unidades, el usuario puede facturar 30. Al volver al flujo, el sistema muestra 70 pendientes.

### CA-02. Varias facturas de una línea

Después de facturar 30 y luego 45 unidades, el sistema permite como máximo 25 unidades adicionales.

### CA-03. Bloqueo de exceso

Si dos usuarios intentan facturar simultáneamente cantidades que superan el saldo, solo se guarda la operación que mantiene el límite; la otra recibe un mensaje de conflicto.

### CA-04. Productos ajenos

El servidor rechaza cualquier producto o línea que no pertenezca a la oferta Expo.

### CA-05. Selección por marca

Al seleccionar una marca, se agregan todas sus líneas pendientes y ninguna línea ya agotada.

### CA-06. Marca sin mínimo al cierre

Si una factura contiene L 9,000 de una marca cuya meta es L 10,000, su descuento de marca es cero.

### CA-07. Acumulación por marca

Dos facturas de L 3,000 de la misma marca acumulan L 6,000 y alcanzan el escalón del 5% configurado para ese monto.

### CA-08. Marca con mínimo

Un flujo que acumula L 11,000 de una marca cuya meta es L 10,000 alcanza el porcentaje superior sobre sus líneas elegibles.

### CA-09. Cierre automático

Cuando la última factura consume todas las cantidades pendientes, el flujo se liquida automáticamente y el aumento es cero si se cumplieron las condiciones firmadas.

### CA-10. Cierre manual

El usuario puede cerrar con cantidades pendientes, debe confirmar y registrar motivo; el aumento se aplica automáticamente y posteriormente no puede seguir facturando.

### CA-11. Aplicación proporcional

El aumento se distribuye entre facturas proporcionalmente al descuento firmado que recibió cada una y la suma coincide con la diferencia a recuperar.

### CA-12. Reapertura

Al reabrir un flujo liquidado, se registra una disminución por el aumento previamente aplicado y se habilita la facturación.

### CA-13. Anulación parcial

Anular una factura devuelve sus cantidades sin afectar otras facturas activas del flujo.

### CA-14. Historial completo

El modal del flujo lista prefacturas, facturas, entregas, cobros, aumentos y disminuciones relacionadas.

### CA-15. Integridad monetaria

La suma de aumentos distribuidos coincide exactamente con descuento otorgado menos descuento ganado, considerando redondeo a dos decimales.

### CA-16. Cierre global y autorización

Al cerrar una Expo, todos sus flujos incompletos se cierran y liquidan. Un intento posterior de facturación se bloquea y permite solicitar el código de autorización vigente.

## 14. Casos de prueba mínimos

1. Una factura con toda la oferta.
2. Varias facturas con productos completos diferentes.
3. Varias facturas con cantidades parciales de la misma línea.
4. Producto repetido en líneas con distinta bodega o unidad.
5. Selección de una marca completa.
6. Selección de todas las marcas.
7. Marca que cumple el mínimo en una factura.
8. Marca que no cumple el mínimo.
9. Varias facturas que alcanzan un escalón al acumularse.
10. Descuento general alcanzado con total real inferior al total ofertado.
11. Cierre manual con productos pendientes.
12. Cierre automático sin productos pendientes.
13. Facturas al contado, crédito y combinación de ambas.
14. Aumento distribuido entre varias facturas.
15. Reintento de liquidación sin duplicar aumentos.
16. Flujo sin aumento porque cumple el descuento firmado.
17. Anulación antes de cerrar la oferta.
18. Anulación después de aplicar el aumento.
19. Dos usuarios facturando simultáneamente la misma línea.
20. Redondeos entre líneas gravadas, exentas y exoneradas.
21. Recalculo de comisiones afectadas.
22. Entregas parciales por diferentes facturas.
23. Reapertura autorizada de una oferta cerrada.
24. Cierre total de Expo con varios flujos incompletos.
25. Facturación de Expo cerrada con código de autorización.
26. Disminución automática al reabrir un flujo liquidado.

## 15. Plan de implementación sugerido

### Fase 1. Modelo y saldos de oferta

1. Agregar referencia de línea de oferta en detalles de prefactura y factura.
2. Implementar consulta central de cantidades pendientes.
3. Implementar bloqueo transaccional y validación de límites.
4. Registrar múltiples documentos en `historico_flujo`.

### Fase 2. Interfaz de facturación parcial

1. Mostrar cantidades ofertadas, facturadas y pendientes.
2. Permitir cantidades parciales.
3. Implementar selección individual y por marca.
4. Mostrar simulación de mínimos por marca.

### Fase 3. Cierre de oferta

1. Implementar detección automática de última factura.
2. Implementar cierre manual con motivo.
3. Bloquear facturación posterior.
4. Registrar cantidades descartadas y auditoría.

### Fase 4. Descuentos y otros movimientos

1. Crear servicio único de cálculo de descuentos.
2. Congelar reglas en la oferta.
3. Crear modal de liquidación.
4. Aplicar aumentos proporcionalmente mediante otros movimientos.
5. Aplicar disminuciones al reabrir.

### Fase 5. Módulos relacionados

1. Ajustar anulaciones.
2. Ajustar inventario y reservas.
3. Ajustar entregas y cobros múltiples.
4. Ajustar comisiones.
5. Ajustar reportes e historial visual.

### Fase 6. Pruebas y despliegue

1. Pruebas unitarias del cálculo.
2. Pruebas de integración de cantidades y concurrencia.
3. Pruebas de integración de aumentos, disminuciones y cartera.
4. Prueba piloto con una Expo controlada.
5. Migración sin modificar documentos históricos.
6. Activación mediante configuración o bandera funcional.

## 16. Resultado esperado

El sistema permitirá facturar una oferta Expo en tantas parcialidades como sean necesarias, incluyendo varias facturas para una misma línea, sin exceder las cantidades autorizadas. Los montos se acumularán por marca y por total del flujo para escoger el escalón más alto alcanzado. El descuento firmado se otorgará proporcionalmente en cada factura y, al cerrar o vencer el flujo, la diferencia no ganada se recuperará mediante aumentos de `otros_movimientos`. Una reapertura generará la disminución vinculada antes de permitir nuevas facturas.