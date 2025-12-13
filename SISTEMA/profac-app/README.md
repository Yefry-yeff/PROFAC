//*****comandos utiles****

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

