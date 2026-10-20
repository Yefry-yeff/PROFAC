//*****comandos utiles****

### Compras y gastos independientes

En `/producto/compra`, el selector Nueva compra / Nuevo gasto conserva el
carrito de productos del catalogo para compras. En gasto se agregan nombres
libres, cantidades enteras, precios antes de impuesto y tasas de ISV de
0%, 15% o 18%, las mismas opciones actuales del catalogo (no existe una
tabla de tasas). El servidor valida las tasas y recalcula por linea en
centavos; no acepta totales enviados por el navegador. Se admiten hasta
2,000 lineas por registro.

La cabecera distribuye factura, CAI, proveedor y RTN en una fila, y tipo de
pago y fechas en otra, con controles alineados y adaptacion a movil.
Solo en gastos se elige una unidad de medida por linea del catalogo
`unidad_medida`. Este catalogo no tiene estado activo/inactivo: por decision
funcional se muestran todas sus unidades sin agregar estados. La unidad es
descriptiva y no multiplica ni convierte cantidades.
Cada linea permite descuento de 0% a 100%, incluidos ambos limites, con
hasta dos decimales y 0% por defecto. Valores fuera del rango bloquean el guardado
y muestran un error en lugar de calcular totales negativos:
subtotal bruto = cantidad x precio; descuento = subtotal bruto x porcentaje;
subtotal neto = bruto - descuento; ISV = neto x tasa; total = neto + ISV.
Descuento e impuesto se redondean a centavos por linea; el resumen suma los
netos. El servidor recalcula todo y persiste unidad, porcentaje y monto
descontado. Los temporales conservan ambos campos; temporales anteriores
abren con descuento cero si no lo tenian y requieren elegir unidad antes de guardar.
Los gastos historicos conservan sus importes y muestran sin unidad si no
se habia registrado. Las compras conservan unidades y calculos originales.
Aplicar tambien `2026_10_18_000002_add_unidad_descuento_to_gasto_detalles.php`.

Los gastos usan `gastos` y `gasto_detalles`. No crean productos, compras,
recepciones, lotes ni cambios de costo/stock. No hay pantalla de detalle,
abonos ni saldos pendientes en este tramite. Las tablas, archivos y saldos
anteriores se conservan sin borrar ni reinterpretar datos historicos.
El boton de listado cambia entre Compras y Gastos segun el tipo de registro.
En gastos el orden de captura es producto, cantidad y precio unitario.

La casilla "Incluir este gasto en el reporte de Compras", junto al titulo
de cabecera y desmarcada por defecto, se conserva en temporales y al guardar.
Marcada: aparece tambien en el listado de Compras y su Excel mensual.
Todos los gastos permanecen en `/gastos`, incluso trasladados y anulados.
Los incluidos en Compras llevan prefijos `G-` / `GASTO-` para distinguirlos
de compras con el mismo ID, sin acciones de recepcion, abono o anulacion de compra.
El Excel de compras respeta mes y anio y usa exclusivamente estas columnas:
200-RTN, 600-Clase de documento, 7-CAI, 8-Numero de documento,
71-Nombre del proveedor, 900-Fecha emision, 100-Fecha contable,
110-Importe exento, 1511-Importe base 15%, 270-Monto al costo y 280-Monto al gasto.
Compras y gastos usan FA-factura; las boletas incluidas usan
OC-Otros comprobantes de pago.
El Excel de compras incluye logo y membrete de Distribuciones Valencia,
fecha/usuario de generacion y filtros aplicados. Los encabezados son naranja,
las filas alternan blanco/naranja claro y los montos al costo/gasto son verdes.
Conserva las 11 columnas, anchos legibles, filtros de Excel y paneles inmovilizados.
La fecha contable es igual a la emision. Los importes suman los subtotales netos
de los productos, sin ISV: tasa 0 para exento y tasa 15 para base 15%.
En compras se usa la tasa del producto del catalogo y el subtotal persistido del
detalle; en gastos, la tasa y el subtotal neto persistidos por linea.
La base del 18% se excluye de todos estos importes. Exento + base 15%, sin
sumar el impuesto, se coloca solo en Monto al costo para compras y solo en
Monto al gasto para gastos incluidos en compras. La columna que no corresponde
al origen queda vacia; un importe aplicable de cero se conserva como numero.
RTN, CAI y factura se conservan como texto; los cuatro importes son numericos
con dos decimales. Este formato sustituye las columnas anteriores del Excel
de compras, no modifica el Excel independiente de gastos ni la auditoria.
El reporte de compras usa cabecera naranja, acentos verdes y un modal de filtros
como Gastos: emision desde/hasta inclusivas (mes vigente por defecto), factura
parcial y proveedor con Select2. Los filtros se aplican a compras activas y
gastos activos incluidos en compras. El boton Excel exporta exactamente ese
rango y criterios, incluso si abarca varios meses; la ruta mensual anterior
continua disponible. "Limpiar" restaura el mes vigente y borra factura/proveedor.
Se conservan las acciones de detalle, recepcion, pagos, incidencias y anulacion
de compras con sus restricciones existentes.

### Boletas incluidas en el reporte de compras

En `/boleta/compra/historial` se seleccionan una o varias boletas activas;
el checkbox del encabezado abarca todas las paginas del filtro, no solo la
visible. Las anuladas permanecen visibles con checkbox bloqueado.
Los estados son Registrada, Pasada al reporte de compras y Anulada.
Incluir y quitar requieren confirmacion modal y registran usuario, fecha/hora
y valores antes/despues en `boleta_compra_auditoria`, en la misma transaccion.
Las selecciones mixtas solo cambian las que necesitan transicion; una seleccion
con IDs inexistentes o anulados se rechaza completa. No se duplican inclusiones.
Hacer clic en una fila (o Enter/espacio) despliega sus cambios auditados.
No se reconstruye historial anterior.

Las incluidas no se pueden editar ni anular, tampoco mediante los endpoints;
primero se retiran de Compras. Las anulaciones nuevas tambien quedan auditadas.
Las boletas aparecen en Compras con prefijo B-, sin acciones de inventario.
El Excel usa RTN/DNI y cliente como RTN/proveedor, CAI de la boleta y numero
de boleta como documento. Todos los importes de los conceptos son exentos,
la base 15% es cero y solo se llena Monto al costo; Monto al gasto queda vacio.
No se usa el total de cabecera para calcular estos importes.
Los filtros de Compras aplican fecha y numero de boleta; al filtrar proveedor
se exige coincidencia de su RTN con el RTN/DNI de la boleta.

El historial permite filtrar fechas y buscar boleta/cliente/RTN/direccion.
Su Excel incluye todas las boletas del filtro aplicado, sin depender de la
seleccion, con las 11 columnas fiscales mas Estado y el mismo logo/colores
del Excel de compras. Las anuladas se conservan en este Excel historico,
pero nunca se incluyen en Compras. Aplicar la migracion
`2026_10_20_000002_add_reporte_compras_to_boletas.php`; las boletas anteriores
no se incluyen automaticamente y no se modifican sus importes ni numeracion.

El reporte de gastos usa la cabecera naranja y acentos verdes del sistema.
Permite seleccionar uno o varios gastos activos. El checkbox del encabezado
de la tabla selecciona todos los gastos activos filtrados, no solo la pagina
visible. Los anulados no se pueden seleccionar.
El boton "Filtros" abre un modal como Libro de cobros: fecha de emision desde
y hasta (ambas inclusivas y con el mes vigente completo por defecto), numero
de factura y proveedor con Select2. Los criterios se combinan; la factura
admite coincidencias parciales y el proveedor se filtra por su ID.
"Buscar" aplica los filtros, reinicia la pagina y limpia la seleccion;
"Limpiar" restaura el mes vigente sin factura ni proveedor. Se validan las
fechas y el proveedor antes de reemplazar los filtros aplicados.
"Pasar al reporte de compras" marca los seleccionados sin duplicarlos ni
quitarlos de Gastos, con estado "Pasado al reporte de compras".
"Anular seleccionados" abre un modal con el motivo y confirmacion; registra `anulado_por`,
`anulado_at` y un motivo obligatorio de hasta 1000 caracteres; se rechazan
comentarios vacios o que contengan solo espacios. Se muestran el
nombre del usuario y la fecha y hora (incluidos segundos). La accion conserva
importes y detalles, sin movimientos de inventario ni abonos. Los anulados
se excluyen del listado y Excel de compras aunque antes se hayan trasladado.
No se permite incluir en compras un gasto anulado ni anular uno actualmente
incluido en compras. Primero debe usarse "Quitar del reporte de compras",
con confirmacion modal. El gasto retirado vuelve al estado Registrado y deja
de aparecer en el listado y Excel de compras; sigue visible en Gastos.
En selecciones mixtas, incluir/quitar modifica solo los activos que requieren
el cambio; si hay anulados o IDs inexistentes se rechaza toda la operacion.
Las acciones se aplican en una transaccion, revalidando que todos los IDs
existan y sigan activos; una seleccion obsoleta no produce cambios parciales.
"Pasar al reporte de compras" tambien requiere confirmacion en un modal del
sistema, sin dialogos nativos del navegador. Los modales muestran errores,
permanecen abiertos si falla la accion y se cierran solo al completarla.
Cada alta, inclusion, retiro y anulacion se registra en `gasto_auditoria`,
con usuario, fecha y hora, valores antes/despues y motivo para la anulacion.
La auditoria y el cambio son atomicos; un fallo revierte ambos. Las acciones
sin cambios no crean eventos duplicados. El historial es visible desplegando
la fila de cada gasto con clic, Enter o espacio; el checkbox solo selecciona.
No se reconstruye ni inventa
la auditoria de movimientos anteriores a su instalacion.

"Exportar Excel", a la derecha de las acciones junto a "Anular seleccionados",
incluye todos los resultados de los filtros aplicados,
de todas las paginas, con estado, indicador de traslado a Compras,
usuario/fecha/hora y motivo de anulacion. No depende de los checkboxes.
El Excel usa el logo `public/img/membrete/Logo3.png`, membrete, encabezados
naranja, filas alternadas y totales en verde, como los demas reportes.
Las primeras columnas son ID, proveedor, CAI, numero de documento (factura),
emision, recepcion, vencimiento, importe exento, importe gravado, ISV y total.
Despues se conservan estado, indicador de traslado y datos de anulacion.
Exento suma los subtotales netos de las lineas con tasa 0%; gravado suma los
de tasas mayores que cero (15% y 18%). Ambos son posteriores al descuento y
anteriores al impuesto; no se recalculan con el catalogo actual.
Los montos son numericos con formato monetario y dos decimales; CAI y numero
de documento se exportan como texto para conservar sus ceros iniciales.
Al guardar solo se registra el gasto y se muestra confirmacion: no se abre
ni se genera documento de impresion. Se retiraron la ruta, plantilla y
acciones de impresion de gastos.
Aplicar `2026_10_18_000003_add_incluir_reporte_compras_to_gastos.php`;
los gastos anteriores permanecen en Gastos por defecto.

El boton + junto al proveedor abre su registro obligatorio completo sin
abandonar el formulario y selecciona el proveedor creado. El RTN se muestra
en solo lectura; tambien se recupera al abrir temporales antiguos.
Compras y gastos comparten la bandeja de temporales de 24 horas, con su
modo guardado en la cabecera. Cambiar de modo guarda primero el borrador;
si falla, no se descarta el formulario. Los temporales antiguos siguen
abriendose como compras.

Aplicar solamente la migracion `2026_10_18_000001_create_gastos_tables.php`
para instalar las tres tablas nuevas. No modifica los registros de compras.
Los listados, acciones y exportaciones requieren autenticacion. Para el historial
aplicar `2026_10_20_000001_create_gasto_auditoria_table.php` antes de usar el reporte.

### PDF de Libro de Cobros

Todos los formatos del PDF se generan en papel carta horizontal (11 x 8.5
pulgadas), con margenes de 18 puntos. La plantilla usa estilos propios, sin
las reglas de impresion de Bootstrap. Las tablas tienen ancho fijo, columnas
distribuidas dentro de la hoja y salto de linea para textos largos; el
encabezado tambien se ajusta al ancho imprimible. Los filtros, datos y totales
no cambian.

### Bodega revisada y prefactura

Al pasar una revision normal a prefactura se guarda la bodega/seccion
seleccionada de todas las lineas, incluso si la oferta ya tenia
`resta_inventario = 1` y el selector propuso una ubicacion alternativa.
Se revalida el stock actual menos reservas, usando el factor de unidad
y sumando las lineas del mismo producto/destino. La oferta actualizada,
la auditoria de reasignacion y la prefactura se guardan en la misma
transaccion. No se trasladan ni ajustan existencias.

Las prefacturas anteriores no se reasignan automaticamente: su ubicacion
debe corregirse expresamente, verificando disponibilidad y conservando
la trazabilidad de la ubicacion anterior.

### Motivo de anulacion de facturas

La anulacion desde los listados corporativo, estatal y exonerado guarda en
`log_estado_factura.motivo` el comentario enviado por el usuario, no un texto
generico. El servidor exige un motivo de texto no vacio antes de iniciar
la transaccion. El listado unificado tambien valida el motivo en el dialogo.
El detalle de anulacion consulta ese registro de auditoria.

Los motivos historicos que fueron sustituidos por `Error Involuntario`
no se reconstruyen automaticamente: necesitan una fuente documental del
comentario original. El cambio no modifica anulaciones existentes.

### Signo de facturas anuladas en el kardex

Las anulaciones restituyen inventario y se muestran como `Factura Anulada (+)`.
La migracion `2026_10_15_000003_correct_factura_anulada_cardex_sign.php`
corrige exclusivamente esa rama del trigger instalado y las descripciones
historicas `Factura Anulada (-)`. Conserva el resto del trigger y no modifica
cantidades, lotes, fechas, usuarios ni saldos. Afecta al kardex general y por
producto/bodega, ya que todos consultan los mismos movimientos.

Aplicar con `php artisan migrate` en MySQL, con permisos para consultar y
recrear el trigger. Si su estructura no coincide con la esperada, la migracion
falla antes de reemplazarlo. Es idempotente y no admite rollback para no
reintroducir el signo incorrecto. El script `fix_trigger_comprovante.sql`
tambien genera las nuevas anulaciones con signo positivo.

### Corte de facturas de distribucion (GLPI-330)

Nueva Distribucion permite seleccionar facturas emitidas desde el 19/05/2026
(inclusive) hasta el dia actual. El mismo rango se usa en las busquedas por
numero/cliente, los pendientes por zona y las facturas sin asignar del reporte.

La migracion `2026_10_13_000001_completar_facturas_historicas_glpi_330.php`
registra como `completada` en el historial logistico cada factura vigente
(`estado_factura_id` 1 o 2, `estado_venta_id` 1) anterior al corte y pendiente
de distribucion: sin entrega confirmada y sin asignacion activa (distribucion
en estado 1 o 2 con factura no anulada). Incluye las pendientes ya tratadas;
no modifica las asignadas ni las entregadas. Conserva el estado anterior en
el comentario solicitado en GLPI-330. Es idempotente.
Estas facturas aparecen en Completadas y el comentario se consulta en su
historial. No modifica ventas, pagos, distribuciones ni entregas de productos;
un cierre administrativo no equivale a una entrega fisica.

Para aplicar el cierre, ejecutar `php artisan migrate`. El rollback de esta
migracion elimina solo sus anotaciones de cierre GLPI-330.

Como alternativa, ejecutar [el script SQL](database/migrations/completar_pendientes_glpi_330.sql)
en MySQL/MariaDB sobre la base correspondiente, con respaldo previo. No es
necesario ejecutar ambas opciones. El script muestra cuantos cierres inserto;
si ya se aplico la migracion, no duplica los cierres. El script solo registra
el cierre: para excluir las facturas antiguas de Nueva Distribucion tambien
deben estar desplegados los cambios de filtrado de la aplicacion.

### Cambio de oferta ganadora después de anular facturas

En el modal del flujo, **Quitar Ganadora** y **Ganadora** se habilitan cuando
no queda ninguna factura vigente (todas tienen `estado_venta_id = 2`).
El historial de facturación se conserva y no debe borrarse para desbloquear
estas acciones. Una factura vigente, incluso vencida, bloquea el cambio también
en el servidor. En Expo, las secciones mostradas corresponden a la ganadora
actual; las secciones anteriores se conservan como historial.

Para reemplazar una oferta por un descuento autorizado: abrir **Ofertas**,
seleccionar la ganadora anterior, usar **Quitar Ganadora** e indicar el motivo.
Luego seleccionar la nueva oferta, marcarla **Ganadora** y crear sus secciones.

En **Secciones de Ofertas**, la búsqueda solo filtra las filas visibles:
las cantidades y selecciones permanecen vinculadas a cada línea de la oferta.
Cambiar la cantidad de un producto no debe modificar otro, aunque se busquen
consecutivamente o tengan el mismo saldo disponible.

1. crear modelo -> php artisan make:model <directory_name>/<model_name>

2. crear componente -> php artisan make:livewire ShowPosts 

3. crear componente en sub carpeta -> php artisan make:livewire Post/Show

4. composer require yajra/laravel-datatables-buttons -W



---Consulta para actualizar facturas vencidas---
update factura set estado_venta_id=4
where  estado_venta_id=1 and fecha_vencimiento < curdate() and pendiente_cobro >0


--------------------consultas que pide CRISTIAN

INGRESO POR COMPRA 
select 
A.created_at as 'fechaIngreso',
C.nombre as 'producto',
C.id as 'codigoProducto',
D.id as 'detalleCompra',
concat(A.descripcion,' (+)') as 'descripcion',
'' as 'origen',
concat (bodega.nombre,' ',seccion.descripcion) as 'destino',
A.cantidad,
users.name as  'usuario'
from log_translado A
inner join recibido_bodega B
on A.origen = B.id
inner join producto C
on B.producto_id = C.id
inner join compra D
on A.compra_id = D.id
inner join seccion on seccion.id = B.seccion_id
inner join segmento on segmento.id = seccion.segmento_id
inner join bodega on bodega.id = segmento.bodega_id
inner join users on users.id = A.users_id
where D.estado_compra_id = 1 
and C.id in (1959, 1948, 1947, 1946 , 1944)


-----VENTAS

select
                    A.fecha_emision as 'FECHA DE VENTA',
                    A.fecha_vencimiento as 'FECHA DE VENCIMIENTO',
                    UPPER(
                        (
                        select
                            name
                        from
                            users
                        where
                            id = A.vendedor
                        )
                    ) as 'VENDEDOR',
                    RIGHT(A.cai, 5) as 'FACTURA',
                    UPPER(cli.nombre) as 'CLIENTE',
                    (
                        CASE cli.tipo_cliente_id WHEN '1' THEN 'CLIENTE B' WHEN '2' THEN 'CLIENTE A' END
                    ) AS 'TIPO CLIENTE (AoB)',
                    (
                        CASE A.tipo_pago_id WHEN '1' THEN 'CONTADO' WHEN '2' THEN 'CRÉDITO' END
                    ) AS 'TIPO CRÉDITO/CONTADO',
                    B.producto_id as 'CODIGO PRODUCTO',
                    UPPER(
                        concat(C.nombre)
                    ) as 'PRODUCTO',
                    UPPER(ma.nombre) as 'MARCA',
                    UPPER(categoria_producto.descripcion) as 'CATEGORIA',
                    UPPER(sub_categoria.descripcion) as 'SUB CATEGORIA',
                    UPPER(J.nombre) as 'UNIDAD DE MEDIDA',
                    if(C.isv = 0, 'SI', 'NO') as 'EXCENTO',
                    H.nombre as 'BODEGA',
                    REPLACE(
                        REPLACE(F.descripcion, 'Seccion', ''),
                        ' ',
                        ''
                    ) as 'SECCION',
                    FORMAT(
                        TRUNCATE(B.precio_unidad, 2),
                        2
                    ) as 'PRECIO',
                    sum(B.cantidad_s) as 'UNIDADES VENDIDAS',
                    FORMAT(
                        sum(B.sub_total_s),
                        2
                    ) as 'SUBTOTAL PRODUCTO',
                    FORMAT(
                        sum(B.isv_s),
                        2
                    ) as 'ISV PRODUCTO',
                    FORMAT(
                        sum(B.total_s),
                        2
                    ) as 'TOTAL PRODUCTO',
                    FORMAT(
                        SUM(A.sub_total),
                        2
                    ) as 'SUB TOTAL FACTURA',
                    FORMAT(
                        SUM(A.isv),
                        2
                    ) as 'ISV FACTURA',
                    FORMAT(
                        SUM(A.total),
                        2
                    ) as 'TOTAL FACTURA'
                from factura A
                    inner join venta_has_producto B on A.id = B.factura_id
                    inner join producto C on B.producto_id = C.id
                    inner join marca ma on ma.id = C.marca_id
                    inner join unidad_medida_venta D on B.unidad_medida_venta_id = D.id
                    inner join unidad_medida J on J.id = D.unidad_medida_id
                    inner join recibido_bodega E on B.lote = E.id
                    inner join seccion F on E.seccion_id = F.id
                    inner join segmento G on F.segmento_id = G.id
                    inner join bodega H on G.bodega_id = H.id
                    inner join cliente cli on cli.id = A.cliente_id
                    inner join sub_categoria on sub_categoria.id = C.sub_categoria_id
                    inner join categoria_producto on categoria_producto.id = sub_categoria.categoria_producto_id
                where
                    A.estado_venta_id = 1
                    AND DATE_FORMAT(A.fecha_emision, '%Y-%m-%d') >= '2022-09-01'
                    AND DATE_FORMAT(A.fecha_emision, '%Y-%m-%d') <= '2024-04-25'
                    and C.id  in (1959, 1948, 1947, 1946 , 1944)
                group by
                    A.fecha_emision,
                    A.fecha_vencimiento,
                    B.producto_id,
                    C.nombre,
                    ma.nombre,
                    categoria_producto.descripcion,
                    sub_categoria.descripcion,
                    J.nombre,
                    C.isv,
                    H.nombre,
                    F.descripcion,
                    B.precio_unidad,
                    B.cantidad_s,
                    B.sub_total_s,
                    B.isv_s,
                    B.total_s,
                    A.vendedor,
                    A.sub_total,
                    A.isv,
                    A.total,
                    A.cai,cli.nombre,A.tipo_venta_id,A.tipo_pago_id ,cli.tipo_cliente_id
                order by
                    A.fecha_emision asc

 -----------------VALE TIPO 1

 select 
A.created_at as 'fechaIngreso',
E.nombre as 'producto',
E.id as 'codigoProducto',
C.id as 'vale_tipo_1',
C.numero_vale as 'vale_tipo_1_cod',
concat(A.descripcion,' (-)') as 'descripcion',
concat (bodega.nombre,' ',seccion.descripcion) as 'origen',
'' as 'destino',
A.cantidad,
users.name as  'usuario' 
from log_translado A
inner join vale_has_producto B
on A.vale_id = B.vale_id and A.origen = B.lote_id
inner join vale C
on B.vale_id = C.id
inner join recibido_bodega D
on A.origen = D.id
inner join producto E
on E.id = D.producto_id
inner join factura F
on A.factura_id = F.id
inner join seccion on seccion.id = D.seccion_id
inner join segmento on segmento.id = seccion.segmento_id
inner join bodega on bodega.id = segmento.bodega_id
inner join users on users.id = A.users_id
where  E.id  in (1959, 1948, 1947, 1946 , 1944)




-------AJUSTES
select
D.created_at as 'fechaIngreso',
E.nombre as 'producto',
E.id as 'codigoProducto',
D.id as 'ajuste',
D.numero_ajuste as 'ajuste_cod',
if(C.tipo_aritmetica = 1 ,concat(B.descripcion,' (+)'), concat(B.descripcion,' (-)') ) as 'descripcion',
concat (bodega.nombre,' ',seccion.descripcion) as 'origen',
'' as 'destino',
B.cantidad,
users.name as  'usuario'
from recibido_bodega A
inner join log_translado B
on A.id = B.origen
inner join ajuste_has_producto C
on (B.ajuste_id = C.ajuste_id) AND  (A.producto_id = C.producto_id)
inner join ajuste D
on C.ajuste_id = D.id
inner join producto E
on E.id = A.producto_id
inner join seccion on seccion.id = A.seccion_id
inner join segmento on segmento.id = seccion.segmento_id
inner join bodega on bodega.id = segmento.bodega_id
inner join users on users.id = B.users_id
where E.id in (1959, 1948, 1947, 1946 , 1944)



-------STOCK


select
            A.id as 'codigo',
            A.nombre,
            A.descripcion,
            A.isv as 'ISV',
            B.descripcion as 'categoria',
            @existenciaCompra := IFNULL ((select
            sum(cantidad_disponible)
            from recibido_bodega
            inner join compra
            on recibido_bodega.compra_id = compra.id
            where compra.estado_compra_id=1 and  producto_id = A.id), 0)  as 'existenciaCompra',
           @existenciaAjuste := IFNULL (
           (
            select
            sum(cantidad_disponible)
            from recibido_bodega  G
            where G.compra_id is null and G.cantidad_disponible <> 0 and G.producto_id = A.id),0 ) as 'existenciaAjuste',
            FORMAT(@existenciaCompra + @existenciaAjuste,0) as existencia,
            A.codigo_barra
            from producto A
            inner join sub_categoria B
            on A.sub_categoria_id = B.id
            WHERE A.id in (1959, 1948, 1947, 1946 , 1944 )            
            order by A.created_at DESC






//////////////////////////////////////////////////////////////////////////////////////////
para configurar la hora de forma global independiente de la hora del sistema.
configurar:
Paso1)
En el archivo .env agregar la variable
APP_TIMEZONE='America/Tegucigalpa'

paso2)
config/app.php

 'timezone' => env('APP_TIMEZONE', 'America/Tegucigalpa'),

 paso3)
\vendor\laravel\framework\src\Illuminate\Foundation\helpers.php

reemplazar la funcion now($tz)
    function now($tz = 'America/Tegucigalpa')
    {
        $timezone = $_ENV['APP_TIMEZONE'];
        return Date::now($timezone);
    }


SEGUNDO METODO NO PROBADO

En la ruta profac-app\app\Providers\AppServiceProvider.php

Reemplazar el motodo boot
    public function boot()
    {
        Schema::defaultStringLength(191);
        date_default_timezone_set('America/Tegucigalpa');
        
    }
/////////////////////////////////////////////////////////////////////////////////////////

EJECUTAR SIEMPRE CUENTAS POR COBRAR SP

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `cuentasx2`(IN idcliente INT)
BEGIN

	SET @acumulado = 0;

select
            factura.numero_factura as numero_factura,
            factura.cai as correlativo,
            #cliente_id as id_cliente,
            factura.nombre_cliente as 'cliente',
            factura.numero_factura as 'documento',
            factura.fecha_emision as 'fecha_emision',
            factura.fecha_vencimiento as 'fecha_vencimiento',
            factura.total as 'cargo',
            (factura.total-factura.pendiente_cobro) as 'credito',
            (select IF(SUM(nota_credito.total) <> 0, SUM(nota_credito.total), 0.00) from nota_credito where nota_credito.factura_id = factura.id) as notaCredito,
            (select IF(SUM(notadebito.monto_asignado) <> 0, SUM(notadebito.monto_asignado), 0.00) from notadebito where notadebito.factura_id = factura.id) as notaDebito,
            
            
            (factura.pendiente_cobro - (select IF(SUM(nota_credito.total) <> 0, SUM(nota_credito.total), 0.00) from nota_credito where nota_credito.factura_id = factura.id) + (select IF(SUM(notadebito.monto_asignado) <> 0, SUM(notadebito.monto_asignado), 0.00) from notadebito where notadebito.factura_id = factura.id) )
            
            as saldo,
            @acumulado := @acumulado + (factura.pendiente_cobro - (select IF(SUM(nota_credito.total) <> 0, SUM(nota_credito.total), 0.00) from nota_credito where nota_credito.factura_id = factura.id) + (select IF(SUM(notadebito.monto_asignado) <> 0, SUM(notadebito.monto_asignado), 0.00) from notadebito where notadebito.factura_id = factura.id) ) AS 'Acumulado'
            from factura
            inner join cliente on (factura.cliente_id = cliente.id)
            where factura.estado_venta_id <> 2 and cliente_id = idcliente and factura.pendiente_cobro <> 0;
END$$
DELIMITER ;







===================================================
PANTALLAS DE FACTURACION
FACTURAR-COTIZACION-GOBIERNO
FACTURAR-COMPROBANTE
SIN-RESTRICCION-GOBIERNO
FACTURACION-ESTATAL
VENTAS-EXONERADAS
FACTURACION-COORPORATIVA
FACTURAR-COTIZACION-COORPIRATIVA
SIN-RESTRICCION-PRECIO-COORPORATIVO
FACTURAR-ORDEN DE ENTREGA

/cotizacion/facturar/srp/corporativo/


agregar descuento a modulo de:
Facturacion Cliente A --
Facturacion Cliente B --
Facturacion SRP cliente A
Facturacion SRP cliente B
FACTURAR-COMPROBANTE
Vebtas exoneradas
