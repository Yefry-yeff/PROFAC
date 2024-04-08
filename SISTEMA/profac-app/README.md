//*****comandos utiles****

1. crear modelo -> php artisan make:model <directory_name>/<model_name>

2. crear componente -> php artisan make:livewire ShowPosts 

3. crear componente en sub carpeta -> php artisan make:livewire Post/Show

4. composer require yajra/laravel-datatables-buttons -W



---Consulta para actualizar facturas vencidas---
update factura set estado_venta_id=4
where  estado_venta_id=1 and fecha_vencimiento < curdate() and pendiente_cobro >0






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

