# Especificación funcional: Pagos No Identificados

**Versión:** 1.0  
**Fecha:** 14 de octubre de 2026  
**Estado:** Desarrollo implementado en el entorno local. Véanse las reglas aprobadas y las notas de entrega del apartado 16.

## 1. Objetivo y alcance

Administrar depósitos o pagos recibidos que todavía no tienen factura destino, con o sin cliente conocido, y permitir su aplicación posterior a cuentas por cobrar. Conservar la trazabilidad desde el depósito original hasta cada factura afectada.

Incluye:

- Consulta de todos los registros, incluidos los totalmente aplicados.
- Registro con cliente opcional.
- Selección individual o múltiple de pagos con saldo disponible.
- Aplicación total o parcial a una factura por operación.
- Asignación de cliente al confirmar la primera aplicación de un pago sin cliente.
- Actualización de saldos, estados y auditoría.
- Consulta de **todas las aplicaciones de un pago al hacer clic en su ID o en «Ver aplicaciones»**.
- Integración con el proceso vigente de cuentas por cobrar y Libro de Cobros, sin duplicar ingresos.

No incluye, salvo ampliación aprobada: aplicar a varias facturas en una sola confirmación, transferencias entre clientes, devoluciones, anulaciones o reversos, edición de depósitos ya registrados, carga masiva, comprobantes adjuntos, exportaciones nuevas ni contabilidad general. Un mismo pago sí podrá utilizarse en distintas facturas mediante operaciones sucesivas.

## 2. Actores y permisos

| Capacidad | Descripción |
|-----------|-------------|
| Consultar | Acceder al listado y utilizar filtros. |
| Registrar | Crear un pago no identificado. |
| Aplicar | Confirmar una aplicación a factura. |
| Consultar aplicaciones | Consultar el detalle y su auditoría, aunque el pago ya no tenga saldo. |

Las capacidades deberán integrarse al esquema existente de roles y acceso al submenu. La asignación concreta a roles queda pendiente de aprobación. Ocultar un botón no sustituye la validación de autorización en el servidor.

## 3. Definiciones y decisiones confirmadas

- **Depósito original:** importe recibido, registrado una sola vez y no alterado por las aplicaciones.
- **Monto aplicado acumulado:** suma de las aplicaciones confirmadas de un pago.
- **Saldo disponible:** depósito original menos monto aplicado acumulado.
- **Operación de aplicación:** confirmación que puede consumir varios pagos, pero tiene un solo cliente y una sola factura destino.
- **Detalle de aplicación:** importe consumido de un pago específico dentro de una operación.
- **Fecha de pago:** fecha del ingreso original. No es la fecha de aplicación.
- **Fecha de aplicación:** fecha y hora en que se confirma la asociación con la factura.

Decisiones confirmadas con el solicitante:

1. Se bloquea la aplicación conjunta de pagos con clientes conocidos distintos.
2. Los importes se consumen por fecha de pago ascendente y, si coinciden, por ID ascendente.
3. Se permite combinar pagos de un mismo cliente con pagos sin cliente. Estos últimos se asignan a ese cliente únicamente al confirmar exitosamente su primera aplicación.

Una selección no debe trasladar saldos entre propietarios ni alterar el banco o la cuenta de origen.

## 4. Consulta principal

### 4.1 Comportamiento inicial

Al ingresar, mostrar todos los pagos dentro del alcance autorizado, sin filtrar por estado y sin exigir asociación previa con una factura. La lista inicial no tendrá restricción temporal: esto permite encontrar depósitos antiguos todavía disponibles.

Esta carga inicial es una diferencia deliberada respecto del Libro de Cobros, cuya interfaz inicia con el mes anterior. Los controles de fecha conservarán la semántica del Libro; no se copiará su restricción inicial porque contradiría la consulta de todos los pagos solicitada.

Se utilizarán paginación, búsqueda y ordenamiento; como referencia visual, 25 registros por página. La propuesta de orden inicial es fecha de pago descendente e ID descendente.

### 4.2 Filtros heredados del Libro de Cobros

La revisión del módulo actual identificó los siguientes filtros, no una referencia genérica a «los mismos filtros»:

| Filtro | Regla funcional |
|--------|-----------------|
| Fecha inicial | Se aplica a la fecha del depósito, inclusive. Vacía: sin límite inferior efectivo; el Libro utiliza `1900-01-01`. |
| Fecha final | Se aplica a la fecha del depósito, inclusive. Vacía: fecha actual, siguiendo el Libro. |
| Cliente | Coincidencia por ID del cliente del pago. Vacío: todos, incluidos los pagos sin cliente. |
| Asesor comercial | Coincidencia exacta con el vendedor de una factura vinculada mediante aplicación confirmada. |
| Teleasesor | Coincidencia exacta con el usuario de teleasesoría de una factura vinculada. |
| Gestor de entrega | Coincidencia exacta con el gestor de una factura vinculada. |
| Banco | Coincidencia por banco/cuenta de origen, según el catálogo vigente utilizado por el Libro. |
| N.º de factura | Coincidencia parcial con CAI o número de secuencia de una factura vinculada, igual que el Libro. |

Reglas comunes:

- Los filtros se combinan mediante AND.
- Si se combinan varios filtros dependientes de factura, una misma factura vinculada deberá satisfacerlos todos.
- Los pagos sin aplicaciones no cumplen filtros dependientes de factura. No se les atribuirá una factura hipotética, ni se sustituirá el vendedor de factura por el vendedor actual del cliente.
- Un pago con varias aplicaciones aparece una sola vez, aunque varias facturas coincidan. Los importes del listado no se multiplican por los cruces.
- Los filtros por factura no limitan el historial mostrado al abrir el pago: el detalle muestra todas sus aplicaciones.
- Buscar aplica los criterios; los filtros activos se muestran y pueden quitarse individualmente.
- Limpiar restablece la consulta inicial de todos los pagos, manteniendo visible esta diferencia con el Libro.
- La cuenta bancaria es dato de registro y columna. No es un filtro independiente existente en el Libro de Cobros revisado.
- El Libro excluye los IDs de banco 12 y 13. Se mantiene esa elegibilidad para registros nuevos y filtros mientras esa sea la regla vigente; no se ocultarán registros históricos por ese motivo. Conviene centralizarla, no replicar IDs en distintas pantallas.

Las adaptaciones sobre filtros de facturas, carga inicial y limpieza están explícitas para aprobación funcional; no son comportamiento ya existente en este módulo.

### 4.3 Columnas

| Columna | Presentación |
|---------|--------------|
| Selección | Checkbox habilitado únicamente con saldo disponible y permiso de aplicación. |
| ID del pago | Identificador único y enlace al detalle de aplicaciones. |
| Cliente | Código/nombre; «Sin identificar» cuando no esté asignado. |
| Fecha de pago | Fecha del depósito. |
| Banco | Banco de origen. |
| Cuenta bancaria | Cuenta receptora vinculada al banco. |
| Monto del depósito | Importe original, nunca el saldo remanente. |
| Monto aplicado | Acumulado confirmado. |
| Saldo disponible | Importe utilizable en futuras aplicaciones. |
| Estado | Uno de los tres estados del apartado 8. |
| Fecha de creación | Fecha y hora de registro. |
| Registrado por | Usuario creador. |
| Acciones | Ver aplicaciones; disponible también para pagos totalmente aplicados. |

Mostrar importes con moneda y dos decimales. La interfaz actual usa lempiras (L); operaciones multimoneda quedan fuera del alcance hasta definir moneda y conversión.

La consulta vacía mostrará «No se encontraron pagos con los filtros seleccionados». Un error de consulta mostrará un mensaje diferente, nunca una tabla vacía que simule éxito.

## 5. Registro de un pago

### 5.1 Acceso y campos

El botón **«Nuevo Pago No Identificado»** abre el formulario:

| Campo | Obligatorio | Validaciones |
|-------|-------------|--------------|
| Cliente | No | Búsqueda y selección por catálogo autorizado; no texto libre. Permite dejarlo vacío. |
| Total depósito | Sí | Numérico, mayor que cero, máximo dos decimales y dentro de la capacidad monetaria definida para persistencia. |
| Fecha de pago | Sí | Fecha válida; conservar la fecha elegida, separada de la fecha de creación. |
| Banco | Sí | Opción válida del catálogo elegible. |
| Cuenta bancaria | Sí | Debe pertenecer al banco elegido; no se aceptan combinaciones arbitrarias. |
| Observaciones | No | Texto escapado al mostrarlo; propuesta de longitud máxima: 2.000 caracteres, pendiente de aprobación. |

Al cambiar el banco se limpia cualquier cuenta incompatible. Si el catálogo vigente representa banco y cuenta en una misma fila, ambos controles deberán resolver esa relación sin inventar una tabla independiente.

### 5.2 Flujo

1. El usuario abre el formulario.
2. Completa los campos y opcionalmente selecciona cliente.
3. El sistema valida en cliente y servidor.
4. Al guardar, registra creador, fecha y hora, monto aplicado cero y saldo disponible igual al depósito.
5. El estado inicial es **Pendiente de Identificación**, exista o no cliente conocido.
6. Se muestra confirmación con el ID y se actualiza la consulta. Si los filtros ocultan el registro nuevo, se informa su ID y se permite abrir su detalle.
7. Cancelar no guarda datos. Un fallo conserva los valores capturados y muestra el motivo.

Registrar el depósito no debe generar un abono a una factura ni reducir una cuenta por cobrar.

## 6. Selección y modal «Aplicar Pago»

### 6.1 Selección

- Permitir uno o varios pagos con saldo mayor que cero.
- «Aplicar Pago» permanece deshabilitado cuando no hay selección.
- Se propone que la selección se conserve al paginar, se identifique por IDs y se limpie al cambiar filtros, con aviso visible. «Seleccionar todos» selecciona solo los habilitados de la página actual; debe indicarse expresamente.
- Mostrar cantidad seleccionada y total de **saldos disponibles**, no de depósitos originales.
- Revalidar la selección al abrir el modal y nuevamente al confirmar.
- Si los pagos con cliente corresponden a más de un ID de cliente, bloquear la operación con «Seleccione pagos de un solo cliente».

### 6.2 Escenarios de cliente

| Selección | Comportamiento |
|-----------|----------------|
| Todos pertenecen al mismo cliente | Cliente visible y bloqueado. Solo sus facturas elegibles. |
| Todos carecen de cliente | Habilitar búsqueda de un cliente obligatorio para continuar. |
| Un cliente conocido y pagos sin cliente | Fijar el cliente conocido. Advertir qué pagos sin cliente se asignarán al confirmar su primera aplicación. |
| Dos o más clientes conocidos distintos | Bloquear y solicitar corregir la selección. |

En pagos originalmente sin cliente, la asignación se persiste solo si el pago recibe un importe mayor que cero. Un pago seleccionado pero no consumido por la distribución permanece sin cliente.

Cancelar o un fallo transaccional no modifica el cliente. Después de la primera aplicación, el saldo restante pertenece al cliente confirmado y no puede aplicarse a otro cliente.

### 6.3 Contenido del modal

1. Resumen de cada origen: ID, cliente, fecha de pago, banco, cuenta, depósito original, aplicado y disponible.
2. Total disponible consolidado.
3. Cliente destino, fijo o seleccionable según el escenario.
4. Lista/búsqueda de facturas pendientes de ese cliente.
5. Factura destino única con número fiscal, fecha de emisión, vencimiento, total y saldo actual.
6. **Monto a aplicar**, editable, obligatorio y mayor que cero.
7. Máximo aplicable: menor entre saldo disponible consolidado y saldo pendiente de factura.
8. Previsualización de la distribución por pago y de los saldos posteriores.
9. Advertencia de asignación de cliente, cuando corresponda.
10. Botones Cancelar y Confirmar aplicación.

Al seleccionar factura se propone el máximo aplicable; el usuario puede reducirlo. Nunca se confirma automáticamente. Si cambia el cliente, se limpia la factura; si cambia la factura, se recalcula el máximo y la previsualización.

### 6.4 Facturas elegibles

Mostrar únicamente facturas del cliente destino, autorizadas, vigentes para cobro y con saldo monetario real mayor que cero. Excluir anuladas y las no cobrables conforme al proceso actual.

El saldo debe provenir de la fuente vigente de cuentas por cobrar e incorporar abonos y demás movimientos válidos. No recalcular como «total original menos este pago» ignorando operaciones anteriores.

Cuando no haya facturas elegibles: «El cliente no tiene facturas con saldo pendiente», y deshabilitar la confirmación.

### 6.5 Confirmación y distribución

Sean:

- `Dᵢ`: saldo disponible actual del pago i.
- `T = Σ Dᵢ`: disponible consolidado.
- `F`: saldo actual de la factura.
- `M`: monto solicitado.

Debe cumplirse `0 < M ≤ min(T, F)`.

Ordenar los pagos por fecha de pago ascendente e ID ascendente. Para cada pago:

`importeᵢ = min(Dᵢ, monto aún no distribuido)`.

Detener la distribución cuando se alcance M. No generar detalles con importe cero. La suma de detalles debe ser exactamente M y no debe cambiar silenciosamente lo confirmado por el usuario.

Tras validar y confirmar, en una sola transacción:

1. Bloquear y releer pagos y cuenta por cobrar.
2. Verificar permisos, cliente, elegibilidad, moneda, saldos e idempotencia.
3. Registrar la operación y un detalle por cada pago efectivamente consumido.
4. Asociar cliente a los orígenes sin identificar que se consumieron.
5. Registrar los abonos en el mecanismo vigente, conservando banco, cuenta y fecha de cada origen.
6. Actualizar la cuenta por cobrar y los efectos de negocio que correspondan.
7. Actualizar saldo y estado de cada pago consumido.
8. Guardar auditoría y confirmar la transacción.

Un fallo revierte todos los efectos de la operación; no se permite aplicar únicamente una parte de los pagos y comunicar éxito.

## 7. Detalle e historial de aplicaciones

### 7.1 Apertura

Al hacer clic en el **ID de un pago** o en **«Ver aplicaciones»**, abrir un modal o vista de detalle de solo lectura. El checkbox debe ser independiente: marcarlo no abre el historial.

Este detalle estará disponible para cualquier estado y mostrará todas las aplicaciones del pago, sin heredar filtros de fechas o facturas del listado.

### 7.2 Encabezado

Mostrar ID, cliente actual, indicación de si originalmente estaba sin identificar, fecha de pago, banco, cuenta, observaciones, depósito original, aplicado acumulado, saldo disponible, estado, creador y fecha de creación. Cuando hubo identificación posterior, mostrar quién la confirmó y cuándo.

### 7.3 Historial

| Dato | Descripción |
|------|-------------|
| ID de operación | Agrupa una confirmación que pudo utilizar varios pagos. |
| ID de aplicación | Identifica el detalle de este pago. |
| Fecha y hora de aplicación | Momento de confirmación, no fecha del depósito. |
| Usuario que aplicó | Identidad del operador. |
| Cliente destino | Código y nombre. |
| Factura asociada | ID interno y número fiscal visible; enlace al detalle existente si el usuario está autorizado. |
| Monto aplicado de este pago | Importe de este origen, no el total global de la operación. |
| Saldo del pago antes | Disponible anterior a esa aplicación. |
| Saldo restante del pago | Disponible inmediatamente después de esa aplicación. |
| Saldo de factura antes/después | Valores auditados al confirmar; no confundir con el saldo actual. |

Ordenar cronológicamente por fecha/hora e ID. El historial debe permitir ver todas las filas mediante paginación; no limitarlo a la última factura ni agrupar eliminando aplicaciones repetidas.

Al abrir una operación desde el historial, mostrar todos los pagos que la integraron y su distribución, sujeto a permisos. Si no existen aplicaciones: **«Este pago aún no tiene aplicaciones a facturas»**.

Los totales del historial deben reconciliar:

- Suma de sus aplicaciones = monto aplicado acumulado.
- Depósito original − suma aplicada = saldo disponible actual.

### 7.4 Ejemplo

Pago P-100: depósito original L 1.000,00.

| Aplicación | Factura | Importe | Saldo del pago posterior |
|------------|---------|---------|--------------------------|
| A-1 | F-001 | L 300,00 | L 700,00 |
| A-2 | F-002 | L 500,00 | L 200,00 |
| A-3 | F-001 | L 200,00 | L 0,00 |

Al abrir P-100 deben aparecer las tres aplicaciones, incluidas las dos a F-001. Su estado final será Aplicado Totalmente.

## 8. Estados y reglas de negocio

| Estado | Condición |
|--------|-----------|
| Pendiente de Identificación | Aplicado acumulado = 0 y saldo disponible = depósito original. Puede tener cliente conocido. |
| Parcialmente Aplicado | Aplicado acumulado > 0 y saldo disponible > 0. |
| Aplicado Totalmente | Saldo disponible = 0 y aplicado acumulado = depósito original. |

El nombre «Pendiente de Identificación» conserva lo solicitado y representa falta de aplicación, no necesariamente ausencia de cliente. No se añadirá un cuarto estado sin aprobación.

Reglas obligatorias:

1. No permitir saldo negativo de depósito ni de factura.
2. No aplicar más que el disponible de cada origen o más que la deuda destino.
3. Un pago puede aplicarse varias veces, incluso a la misma factura, si conserva saldo; lo prohibido es consumir dos veces los mismos fondos.
4. Una operación utiliza un solo cliente y una sola factura.
5. Conservar cada origen; consolidar no fusiona depósitos ni elimina trazabilidad bancaria.
6. El monto original es inmutable dentro de este alcance.
7. Un pago totalmente aplicado no se selecciona, pero permanece visible y consultable.
8. No autorizar desde el navegador importes, usuarios, fechas de auditoría o saldos.
9. Si otro usuario modifica un saldo antes de confirmar, rechazar por conflicto, recargar saldos y pedir nueva confirmación. No reducir M silenciosamente.
10. Una solicitud repetida de la misma confirmación no genera nuevos abonos ni consumos.
11. Registrar primero el depósito y aplicarlo después no representa dos ingresos diferentes.
12. Los filtros no alteran la elegibilidad financiera ni sirven como controles de autorización.

## 9. Flujos de usuario

### Flujo A: registrar y aplicar con cliente conocido

Consultar → Nuevo Pago No Identificado → completar formulario → guardar → seleccionar pago → Aplicar Pago → cliente fijo → factura pendiente → revisar monto y distribución → confirmar → consultar nuevos saldos → abrir historial.

### Flujo B: identificar al aplicar

Consultar → seleccionar pago sin cliente → Aplicar Pago → buscar cliente → cargar facturas → seleccionar factura → revisar asignación y monto → confirmar → persistir cliente e historial.

### Flujo C: varios orígenes y aplicación parcial

Seleccionar pagos compatibles → visualizar total disponible → seleccionar factura → introducir importe menor al disponible → revisar consumo por antigüedad → confirmar → conservar remanentes para otra operación.

Ejemplo: P-1 dispone de L 600,00 y P-2 de L 500,00, en ese orden de antigüedad. Factura con saldo L 800,00; se aplican L 800,00. P-1 consume L 600,00 y queda aplicado totalmente; P-2 consume L 200,00 y queda parcialmente aplicado con L 300,00 disponibles.

### Flujo D: consultar trazabilidad

Consultar → clic en ID del pago → visualizar datos originales y todas sus aplicaciones → identificar facturas y montos → abrir factura u operación si se dispone de permiso → volver conservando filtros y posición del listado.

## 10. Casos de uso

| ID | Caso | Precondición | Resultado |
|----|------|--------------|-----------|
| CU-01 | Consultar pagos | Usuario autorizado. | Listado filtrado, sin duplicar pagos por sus aplicaciones. |
| CU-02 | Registrar pago | Permiso de registro y campos válidos. | Pago pendiente con saldo igual al depósito; auditoría de creación. |
| CU-03 | Aplicar con cliente | Pagos compatibles con cliente conocido. | Abono a una factura del mismo cliente y detalle por origen consumido. |
| CU-04 | Aplicar sin cliente | Orígenes sin identificar y cliente seleccionado. | Cliente asignado a los consumidos, aplicación y remanentes trazables. |
| CU-05 | Aplicar selección mixta | Un cliente conocido y orígenes sin cliente. | Asignación de ese cliente y distribución por antigüedad. |
| CU-06 | Aplicar parcialmente | M menor que el disponible consolidado. | Consumo exacto de M y remanentes disponibles. |
| CU-07 | Consultar aplicaciones | Pago existente y permiso de detalle. | Historial completo de facturas, usuarios, importes y saldos. |
| CU-08 | Resolver conflicto | Saldos cambiaron antes de confirmar. | Sin efectos parciales; mensaje y solicitud de revisar valores. |

En todos los casos, cancelar no produce cambios. Los errores de autorización, validación o persistencia deben mostrarse explícitamente.

## 11. Criterios de aceptación

| ID | Dado / Cuando | Entonces |
|----|---------------|----------|
| CA-01 | Existen pagos de los tres estados; se abre el módulo. | Se muestran todos, paginados y sin exigir factura asociada. |
| CA-02 | Se establece un rango de fechas. | Se incluyen ambos extremos según fecha de pago, no fecha de aplicación. |
| CA-03 | Se combinan cliente, banco y factura. | Se cumplen todos los filtros y cada pago aparece una vez. |
| CA-04 | Un pago no tiene aplicaciones y se filtra por factura o asesor. | No se le atribuyen datos de factura y no coincide. |
| CA-05 | Se registra sin cliente con demás campos válidos. | Se crea pendiente, sin aplicaciones, con disponible igual al depósito. |
| CA-06 | Falta monto, fecha, banco o cuenta; o el monto es cero/negativo. | No se guarda y se identifica el campo inválido. |
| CA-07 | La cuenta no pertenece al banco. | El servidor rechaza la operación aunque el navegador haya sido manipulado. |
| CA-08 | Se seleccionan pagos con dos clientes conocidos distintos. | Se bloquea antes de permitir confirmar. |
| CA-09 | Los pagos tienen el mismo cliente. | Solo se muestran sus facturas cobrables con saldo mayor que cero. |
| CA-10 | Todos los pagos carecen de cliente. | Es obligatorio elegir cliente antes de seleccionar factura. |
| CA-11 | Se combina un cliente conocido con pagos sin cliente y se cancela. | No se asigna cliente ni se modifica saldo. |
| CA-12 | Hay L 1.100 disponibles y una factura debe L 800; se solicitan L 900. | Se rechaza; no se crea aplicación ni abono. |
| CA-13 | Se confirma el ejemplo P-1/P-2 del flujo C. | Se consumen 600 y 200, quedan saldos 0 y 300 y factura en cero. |
| CA-14 | Dos pagos tienen igual fecha. | Se consume primero el de menor ID. |
| CA-15 | Un pago seleccionado sin cliente no recibe importe. | No se crea detalle ni se le asigna cliente. |
| CA-16 | Se reenvía la misma confirmación. | Se devuelve su resultado previo sin duplicar efectos. |
| CA-17 | Otro usuario consume saldo antes de la confirmación. | La validación transaccional bloquea el exceso y exige revisar. |
| CA-18 | Falla el registro de uno de los abonos. | Se revierte toda la operación, incluidos cliente, saldos e historial. |
| CA-19 | Se hace clic en P-100 del ejemplo de historial. | Aparecen las tres aplicaciones, dos de ellas a F-001, y sus saldos posteriores. |
| CA-20 | Se abre un pago desde un listado filtrado por una factura. | El detalle muestra también sus aplicaciones a otras facturas. |
| CA-21 | Se abre un pago sin aplicaciones o totalmente aplicado. | El primero muestra el mensaje sin historial; el segundo conserva toda la trazabilidad. |
| CA-22 | Se consulta el Libro tras la aplicación. | Los abonos se reflejan una sola vez y conservan fecha y origen bancario del depósito. |
| CA-23 | Se revisa cualquier pago. | Original = aplicado acumulado + disponible, con precisión monetaria exacta. |
| CA-24 | Se intenta aplicar sin permiso mediante una solicitud directa. | Se rechaza sin alterar datos. |

## 12. Validaciones y mensajes

- Fecha inicial posterior a final: «La fecha inicial no puede ser posterior a la fecha final».
- Sin selección: «Seleccione al menos un pago con saldo disponible».
- Cliente requerido en el modal: «Seleccione el cliente al que pertenece el depósito».
- Factura ajena: «La factura seleccionada no pertenece al cliente de la operación».
- Factura sin saldo/no cobrable: «La factura ya no tiene saldo disponible para recibir este pago».
- Monto inválido: «Ingrese un monto mayor que cero y no superior al máximo aplicable».
- Saldo concurrente: «Los saldos cambiaron. Revise los importes antes de confirmar».
- Error de persistencia: «No se pudo completar la aplicación. No se registraron cambios», únicamente cuando se verificó el rollback.

No limpiar formularios ante errores recuperables. Deshabilitar confirmación mientras se procesa y permitir reintento seguro con la misma clave de operación.

## 13. Auditoría y modelo lógico propuesto

### 13.1 Pago de origen

ID, cliente nullable, indicador de cliente originalmente conocido, monto original, fecha de pago, identificador bancario/cuenta compatibles con catálogo real, observaciones, estado, saldo disponible, creador y fecha/hora de creación. Si se persiste un acumulado, debe reconciliar con el detalle.

### 13.2 Operación de aplicación

ID, clave idempotente única, cliente destino, factura destino, monto total, usuario y fecha/hora de confirmación, saldo de factura antes/después.

### 13.3 Detalle por origen

ID, operación, pago de origen, monto consumido, saldo de origen antes/después y referencia al abono real generado en cuentas por cobrar. Esta relación permite que un pago tenga muchas aplicaciones y que una operación use muchos pagos.

### 13.4 Identificación de cliente

Conservar cliente anterior/nuevo, pago, usuario, fecha/hora y operación que produjo la asignación. No perder que el registro nació sin identificar.

Los nombres de tablas y columnas son decisiones de diseño, no estructuras ya existentes verificadas. Los usuarios y fechas de auditoría los determina el servidor. El historial no se elimina ni sobrescribe desde este alcance.

## 14. Consideraciones técnicas para desarrollo

### 14.1 Livewire 4

- Mantener el patrón actual de clase en `app/Livewire/CuentasPorCobrar` y vista Blade, compatible con Livewire 4.
- No generar clases bajo la carpeta histórica `app/Http/Livewire`.
- Implementar validación y autorización en las acciones del servidor. Los IDs y propiedades públicas de Livewire son entradas no confiables.
- Asegurar que Select2, tablas y modales se inicialicen sin listeners duplicados al actualizar el componente.
- Los títulos, nombres, observaciones y mensajes de usuario se muestran escapados.

### 14.2 Integración financiera existente

La revisión encontró que el proceso actual de abonos utiliza `abonos_creditos`, `aplicacion_pagos` y `sp_aplicacion_pagos`. También contempla cierre de facturas, crédito del cliente y efectos relacionados con comisiones/retenciones.

La implementación debe reutilizar o extraer la operación de negocio apropiada, no copiar únicamente un INSERT ni llamar ciegamente un endpoint que produzca confirmaciones parciales. Verificar antes si los procedimientos, triggers y servicios existentes soportan la transacción exterior.

- Por cada origen consumido, preservar fecha, banco, cuenta y vínculo al depósito.
- Si se generan varios abonos para una factura, su suma debe igualar M.
- Aplicar una sola vez los efectos asociados y el cierre al llegar a cero.
- Evitar actualizaciones dobles de saldo entre aplicación, procedimiento y triggers.
- No ejecutar el flujo de cobro de factura al registrar un depósito todavía no aplicado.

El Libro actual calcula movimientos con `fecha_pago` y excluye bancos 12 y 13. Esta especificación propone conservar la fecha original al generar los abonos; una aplicación tardía podría aparecer en un período histórico. La política de períodos cerrados debe aprobarse antes de implementar.

### 14.3 Consistencia y concurrencia

- Usar DECIMAL con precisión suficiente o aritmética en centavos; no comparar saldos monetarios mediante floats binarios.
- Centralizar la regla de precisión y cierre. El módulo solicita saldo > 0; el Libro clasifica como pagada una factura con saldo <= 0,01. Resolver esa diferencia con Finanzas, sin heredar una tolerancia de forma accidental.
- Bloquear filas en orden determinista para reducir interbloqueos.
- Implementar una transacción atómica con restricciones de integridad e idempotencia persistida.
- Una clave idempotente reutilizada con datos diferentes debe rechazarse.
- Reintentar errores transitorios solo con garantía de no duplicación.
- Registrar errores técnicos con identificador de operación, sin mostrar SQL ni información sensible al usuario.

### 14.4 Consulta, pruebas y despliegue

- Consultas paginadas e índices por fecha, estado, cliente, banco y relaciones de aplicación; evitar N+1.
- Filtros de factura mediante existencia o estrategia equivalente que no multiplique pagos ni totales.
- Probar registro, elegibilidad, distribución, aplicaciones sucesivas, historial, permisos, doble envío, concurrencia, rollback y conciliación con el Libro.
- Ejecutar pruebas sobre base de datos de pruebas, no sobre datos operativos.
- Crear migraciones compatibles con el esquema real y verificar integridad antes de habilitar escritura.
- Los registros históricos, si existen, requerirán conciliación; no inventar vínculos a facturas ni reconstruir auditoría sin evidencia.

## 15. Definiciones pendientes de aprobación

No deben convertirse en reglas implícitas durante el desarrollo:

1. Aprobación de las adaptaciones de filtros dependientes de factura y del rango inicial sin restricción temporal.
2. Matriz final de roles/capacidades.
3. Política de fechas futuras y aplicaciones con fecha original en períodos cerrados.
4. Regla monetaria común de saldo cero y tratamiento del umbral de L 0,01 existente.
5. Confirmación del catálogo banco/cuenta y elegibilidad vigente de los IDs 12 y 13.
6. Longitud máxima de observaciones, límites monetarios y orden inicial del listado.
7. Confirmación de la selección entre páginas propuesta en el apartado 6.1.
8. Prevención de depósitos duplicados: los campos solicitados no incluyen referencia bancaria única. No rechazar automáticamente dos depósitos legítimos por compartir fecha, banco y monto; definir si se agregará referencia o advertencia.
9. Reglas para corregir un depósito erróneo y eventualmente reversar aplicaciones. No habilitar borrado o edición libre como sustituto.
10. Validación de los efectos existentes sobre cierre, crédito, comisiones y retenciones para este nuevo origen de pago.

Estas decisiones no impiden revisar la especificación, pero las que afecten integridad financiera deberán resolverse antes de implementar o habilitar aplicaciones en producción.

## 16. Desarrollo entregado y operación

### 16.1 Reglas aprobadas durante el desarrollo

- Se utilizan los permisos existentes del submenu para consultar, registrar y aplicar.
- Se aceptan importes exactos de dos decimales, incluido L 0,01; el cierre de la cuenta se produce al llegar a cero exacto.
- No se permiten fechas de depósito futuras.
- Se aprobaron las adaptaciones de filtros y rango inicial de este documento.
- Un período de comisiones conciliado no impide aplicar el depósito: se exige confirmación y se traslada la comisión al siguiente período abierto, conservando la fecha original del ingreso.

El umbral histórico del Libro para la etiqueta de factura pagada no fue modificado: puede clasificar como pagada una factura con L 0,01 pendiente, mientras este módulo permite consumir ese centavo. Los saldos de cuentas por cobrar siguen siendo la fuente financiera.

### 16.2 Archivos y persistencia

- [Componente Livewire](app/Livewire/CuentasPorCobrar/PagosNoIdentificados.php).
- [Vista del módulo](resources/views/livewire/cuentasporcobrar/pagosnoidentificados.blade.php).
- [Servicio financiero](app/Services/PagosNoIdentificadosService.php).
- [Migración](database/migrations/2026_10_15_000001_create_pagos_no_identificados_tables.php).
- [Pruebas de regresión](tests/Feature/PagosNoIdentificadosTest.php).

Tablas nuevas:

- `pagos_no_identificados`: depósito original, cliente, origen bancario y disponible.
- `pni_operaciones`: confirmación, factura, usuario, saldos destino y clave idempotente.
- `pni_aplicaciones`: detalle por origen y vínculo único al abono real.

La migración ya fue aplicada en el entorno local. Para otro entorno, desplegar el código y ejecutar únicamente:

```console
php artisan migrate --path=database/migrations/2026_10_15_000001_create_pagos_no_identificados_tables.php --force
```

No es necesario recrear el submenu existente. La ruta mantiene su URL.

### 16.3 Integración y protecciones

El procedimiento `sp_aplicacion_pagos` cargado durante el desarrollo inicia y confirma su propia transacción antes de las acciones 8 y 9. Por ello, el módulo no lo invoca dentro de la aplicación consolidada: actualiza `aplicacion_pagos` con sus mismas reglas de abono/cierre dentro de una transacción exterior única.

Se conservan:

- Abonos individuales en `abonos_creditos`, con banco y fecha originales, y método de transferencia bancaria existente (ID 2).
- Liberación del crédito del cliente cuando tiene crédito inicial.
- Generación de comisiones, retenciones por mora y acreditación mediante los servicios existentes.
- Sincronización del cargo de factura, incluido el tratamiento de ventas tipo 3.

El flujo antiguo de anulación de abonos rechaza estos movimientos tanto al consultar impacto como al intentar anular. No se implementó un reverso de depósitos; requiere un proceso integral futuro.

La conciliación de comisiones y la aplicación comparten el bloqueo del período antes de calcular sus efectos, para evitar confirmar un snapshot mientras se acredita una comisión.

### 16.4 Uso

La presentación sigue el estilo del Libro de Cobros: tarjeta con encabezado naranja, indicadores de pagos/depósitos/saldo disponible y tabla compacta. El botón **Filtros** abre un modal independiente; **Buscar** aplica los criterios y **Cerrar** descarta los cambios no aplicados. Los criterios activos se muestran como etiquetas removibles. **Limpiar** restablece todos los pagos. Los indicadores corresponden a toda la consulta filtrada, no solo a la página visible.

1. Abrir **Nuevo Pago No Identificado**, seleccionar banco y verificar su cuenta.
2. Registrar con o sin cliente.
3. Seleccionar registros individualmente o los disponibles de la página; la selección se conserva al paginar y se limpia al cambiar filtros.
4. Abrir **Aplicar Pago**, elegir cliente cuando corresponda, factura y monto.
5. Revisar distribución y confirmar cualquier traslado de período conciliado.
6. Hacer clic en el ID del pago o **Ver aplicaciones** para consultar todas sus facturas vinculadas.
7. Abrir una **Operación** desde el historial para ver todos los depósitos que la integraron.

Límites implementados: 100 pagos por operación, importes hasta L 9.999.999.999,99 y observaciones hasta 2.000 caracteres. El registro y la aplicación utilizan Select2 con todos los clientes, sin el límite anterior de 50 resultados. Las facturas se seleccionan únicamente en **Aplicar Pago**, mediante Select2 con todas las facturas pendientes del cliente elegido. El filtro de clientes conserva su búsqueda de 50 coincidencias. El historial muestra todas las aplicaciones sin recortarlas.

El modal de registro se organiza en identificación del depósito y cuenta receptora, con ancho adaptable. El total muestra el prefijo **L**, separadores de miles y dos decimales (ejemplo: **L 12,500.50**); se envía al servidor sin separadores para mantener los cálculos exactos. Se capturan fecha y hora en campos separados y legibles; no se permiten valores futuros. La hora se conserva en `pagos_no_identificados.hora_pago` y se muestra en listado, detalle y desglose de aplicaciones. Los registros anteriores permanecen con hora nula, identificada como **Hora no registrada**, sin inventar una hora histórica. Los abonos y cálculos de comisión mantienen la fecha original y sus reglas existentes.

Aplicar también la migración incremental `2026_10_15_000002_add_hora_pago_to_pagos_no_identificados.php`.

El importe agrupa los millares con coma mientras se escribe y completa dos decimales con punto al salir del campo. Conserva la posición del cursor al editar y envía el valor sin comas; los importes fuera del límite muestran validación, sin truncarse ni redondearse.

El modal **Aplicar Pago** organiza el proceso en tres pasos: depósitos seleccionados, cliente/factura destino e importe/distribución. Muestra el total disponible y máximo aplicable, así como un resumen de fechas y saldo de la factura. El monto a aplicar utiliza el mismo formato monetario del registro (**L 12,500.00**) y actualiza la distribución al editarlo; cambiar de cliente o factura restablece el importe según las reglas vigentes.

En escritorio, la factura y la distribución se muestran en columnas con espaciado compacto. Las listas extensas tienen desplazamiento interno y el pie de confirmación permanece visible; en móvil se apilan las secciones. El resumen muestra **Saldo pendiente después de aplicar**, calculado en centavos como saldo actual menos importe ingresado, sin guardar cambios hasta confirmar. Si el importe es inválido o supera el máximo, solicita un monto válido en lugar de presentar un saldo proyectado incorrecto.

El detalle presenta tarjetas para total depositado, importe aplicado y saldo disponible, seguidas de los datos bancarios y de registro. Cada aplicación aparece en una tarjeta con factura, fecha, usuario, monto y saldos antes/después identificados por separado. Si no hay aplicaciones, indica cómo aplicar el saldo sin mostrar una tabla vacía. La operación permite consultar los depósitos que la integraron.

Al hacer clic en la factura vinculada se descarga su PDF sin cerrar el detalle. Se reutiliza el generador vigente de facturas (incluida su selección de formato exonerado). La descarga requiere acceso al módulo y una asociación real entre la factura solicitada y el depósito abierto; no permite descargar facturas ajenas al historial.

### 16.6 Integración con Libro de Cobros

**Aplicar Pago** permite escribir una observación opcional de hasta 2.000 caracteres. Se añade al comentario de cada abono generado, conservando la referencia de operación y la observación original del depósito. Se muestra en el historial y en el Libro de Cobros con sus exportaciones. El campo se limpia al abrir una nueva aplicación y forma parte del control de reintentos para impedir reutilizar la misma solicitud con una nota distinta.

El reporte vigente (tipo 3), su PDF y Excel incluyen el saldo disponible de los depósitos sin aplicar. Solo muestran observaciones, banco, cuenta receptora, cliente si existe, fecha original de depósito y monto del movimiento. Los campos de factura, estado de factura, asesores e impuestos quedan vacíos; no se inventan cargos fiscales para dinero aún no asociado a una venta.

La aplicación genera abonos normales en `abonos_creditos` y actualiza `aplicacion_pagos`, de modo que el reporte incorpora los importes aplicados mediante su lógica habitual. El depósito totalmente aplicado deja de aparecer como depósito pendiente. Para una aplicación parcial, se presenta el abono normal por lo consumido y el depósito por su saldo restante; nunca se suma otra vez el monto original completo. Los importes disponibles reflejan el estado actual, no un corte histórico de aplicaciones.

Los saldos no aplicados se incluyen en monto de movimiento y total cobrado, pero no en facturas completas, impuestos ni total de factura. Respetan rango de fechas de depósito, cliente y banco. Al filtrar por factura, asesor comercial, teleasesor o gestor, se excluyen los saldos sin aplicación, porque carecen de una factura asociada.

### 16.5 Validación

```console
php vendor/phpunit/phpunit/phpunit tests/Feature/PagosNoIdentificadosTest.php
```

Por defecto, la prueba crea un esquema SQLite en memoria, independiente de la base operativa. Las dos pruebas específicas de MySQL se omiten en SQLite.

También se ejecutó la suite completa en una base MySQL temporal aislada: 26 pruebas y 115 comprobaciones, incluyendo dos procesos concurrentes y la protección de anulación del flujo legado. La base temporal fue eliminada al finalizar.

Para reproducir las pruebas MySQL, proporcionar una base **vacía y descartable** mediante `PNI_TEST_MYSQL_DATABASE`, con nombre `profac_pni_<hexadecimal>_testing`, usando la conexión MySQL del entorno. **La suite elimina las tablas de esa base aislada al preparar cada caso. Nunca utilizar una base con datos que deban conservarse.**

Se verificó en navegador la carga del módulo, apertura del formulario y cuenta bancaria dependiente del banco. El registro y las aplicaciones se ejercitaron con Livewire y datos sintéticos en pruebas; no se generaron cobros de prueba en la base operativa.
