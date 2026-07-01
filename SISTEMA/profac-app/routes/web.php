<?php

/* ------------------------------COMISIONES------------------------------------------- */

use App\Http\Livewire\Comisiones\ComisionesPrincipal;
use App\Http\Livewire\Comisiones\ComisionesGestiones;
use App\Http\Livewire\Comisiones\ComisionesVendedor;
use App\Http\Livewire\Comisiones\ComisionesComisionar;
use App\Http\Livewire\Comisiones\ComisionesHistorico;

    use App\Http\Livewire\Clientes\Cliente as ClienteLW;


/* ------------------------------/COMISIONES------------------------------------------- */

use App\Http\Livewire\Reportes\FacturaDia;
use App\Http\Livewire\Reportes\DashboardVentas;

use App\Http\Livewire\Reportes\Prodmes;
use App\Http\Livewire\Reportes\Reporteria;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Livewire\Bodega;
use App\Http\Livewire\BodegaComponent\BodegaEditar;
use App\Http\Livewire\Proveedores;
use App\Http\Livewire\Usuarios\ListarUsuarios;
use App\Http\Livewire\Registro\Login as RegistroLogin;
use App\Http\Livewire\Inventario\Producto;
use App\Http\Livewire\Inventario\ProductoApoyo;
use App\Http\Livewire\Inventario\Retenciones;
use App\Http\Livewire\Inventario\DetalleProducto;
use App\Http\Livewire\Inventario\DisenoProducto;
use App\Http\Livewire\Inventario\CompraProducto;
use App\Http\Livewire\Inventario\ListarCompras;
use App\Http\Livewire\Inventario\DetalleCompra;
use App\Http\Livewire\Inventario\PagosCompra;
use App\Http\Livewire\Inventario\RecibirProducto;
use App\Http\Livewire\Inventario\Incidencias;
use App\Http\Livewire\Inventario\AnularCompra;
use App\Http\Livewire\Inventario\Translados;
use App\Http\Livewire\Inventario\Marca;
use App\Http\Livewire\Inventario\UnidadesMedida;
use App\Http\Livewire\Inventario\Ajustes;
use App\Http\Livewire\Clientes\Cliente;
use App\Http\Livewire\Ventas\ListadoFacturas;
use App\Http\Controllers\BusquedaProductoController;
use App\Http\Livewire\Ventas\FacturacionCorporativa;
use App\Http\Livewire\Ventas\DetalleVenta;
use App\Http\Livewire\Ventas\Cobros;
use App\Http\Livewire\Ventas\Comparacion;
use App\Http\Livewire\Ventas\Configuracion;
use App\Http\Livewire\Ventas\FacturacionEstatal;
use App\Http\Livewire\Ventas\ListadoFacturaEstatal;
use App\Http\Livewire\Ventas\SeleccionarFactura;
use App\Http\Livewire\Ventas\LitsadoFacturasVendedor;
use App\Http\Livewire\Ventas\DetalleVentaVendedor;
use App\Http\Livewire\Ventas\LitsadoFacturasEstatalVendedor;
use App\Http\Livewire\VentasExoneradas\VentasExoneradas;
use App\Http\Livewire\VentasExoneradas\ListadoFacturasExonerads;
use App\Http\Livewire\Cotizaciones\Cotizacion;
use App\Http\Livewire\Cotizaciones\expo;
use App\Http\Livewire\Reportes\Expo as expoCotiza;
use App\Http\Livewire\Cotizaciones\Editarcotizacion;
use App\Http\Livewire\Cotizaciones\ListarCotizaciones;
use App\Http\Livewire\Cotizaciones\ListarCotizacionesExpo;
use App\Http\Livewire\Cotizaciones\FacturarCotizacion;
// use App\Http\Livewire\Cotizaciones\FacturarCotizacionGobierno; // Movido a codigo-muerto - unificado en FacturarCotizacion
use App\Http\Livewire\Ventas\ListadoFacturasAnuladas;
use App\Http\Livewire\Reportes\ProductoBodegas;
use App\Http\Livewire\Inventario\ListadoAjustes;
use App\Http\Livewire\Inventario\HistorialTranslados;
use App\Http\Livewire\Cardex\Cardex;
use App\Http\Livewire\Cardex\Cardexdos;
use App\Http\Livewire\Ventas\Cai;
use App\Http\Livewire\Bancos;
use App\Http\Livewire\Ventas\NumOrdenCompraEstatal as NumOrdenCompra;
use App\Http\Livewire\Ventas\CodigoExoneracion;
use App\Http\Livewire\Inventario\TipoAjuste;
use App\Http\Livewire\Ventas\MotivoNotaCredito;
use App\Http\Livewire\NotaCredito\CrearNotaCredito;
use App\Http\Livewire\NotaCredito\ListadoNotaCredito;
use App\Http\Livewire\BoletaCompra\CrearBoletaCompra;
use App\Http\Livewire\BoletaCompra\HistorialBoletaCompra;
use App\Http\Livewire\BoletaCompra\EditarBoletaCompra;
use App\Http\Livewire\Inventario\Categoria;
use App\Http\Livewire\Inventario\SubCategoria;
use App\Http\Livewire\Ventas\SinRestriccionPrecio;
use App\Http\Livewire\Ventas\ListadoFacturasND;
use App\Http\Livewire\Ventas\CuentasPorCobrar;
use App\Http\Livewire\Ventas\HistoricoPreciosCliente;
use App\Http\Livewire\CuentasPorCobrar\ListadoFacturas as listadoCuentasCobrar;
use App\Http\Livewire\ComprovanteEntrega\CrearComprovante;
use App\Http\Livewire\ComprovanteEntrega\ListarComprovantes;
use App\Http\Livewire\ComprovanteEntrega\ListarComprovantesAnulados;
use App\Http\Livewire\ComprovanteEntrega\FacturarComprobante;
use App\Http\Livewire\Ventas\FacturacionUnificada;



use App\Http\Livewire\CuentasPorCobrar\Pagos;
use App\Http\Livewire\CuentasPorCobrar\EstadoCuentaVendedor;


use App\Http\Livewire\Vale\CrearVale;
use App\Http\Livewire\Vale\ListarVales;
use App\Http\Livewire\Vale\FacturarVale;
use App\Http\Livewire\Vale\ValeListaEspera;
use App\Http\Livewire\Vale\RestarVale;
use App\Http\Livewire\Vale\ListadoFacturasVale;
use App\Http\Livewire\Ventas\ListarVale;
use App\Http\Livewire\NotaDebito\NotaDebito;
use App\Http\Livewire\Inventario\AjusteIngresoProducto;
use App\Http\Livewire\Cardex\CardexGeneral;
use App\Http\Livewire\NotaCredito\ListadoNotasND;
use App\Http\Livewire\NotaDebito\ListadoNotasDebito;
use App\Http\Livewire\NotaDebito\ListadoNotasDebitoND;
use App\Http\Livewire\Ventas\NumOrdenCompra as NumOrdenCompraCoorporativo;
use App\Http\Livewire\Ventas\NumOrdenCompraUnificado;
use App\Http\Livewire\Ventas\ListadoFacturasUnificado;
use App\Http\Livewire\Ventas\ListadoFacturasVendedorUnificado;


use App\Http\Livewire\CierreDiario\CierreDiario;

use App\Http\Livewire\CierreDiario\HistoricoCierres;

//------------Johann Routes-------------//
//------------Cardex tres--------------//
use App\Http\Livewire\Cardex\Cardextres;
//------------Reporte Cierre Diario-------//
use App\Http\Livewire\Reportes\Cierrediariorep;
use App\Http\Livewire\Reportes\Comisiones;
use App\Http\Livewire\Reportes\Facturasanuladasrep;
use App\Http\Livewire\Reportes\Librocobrosrep;
use App\Http\Livewire\Reportes\Libroventarep;
use App\Http\Livewire\Clientes\ReporteClientes;
use App\Http\Livewire\Reportes\ReporteVentasCobros;


use App\Http\Livewire\Escalas\CategoriaPrecios;
use App\Http\Livewire\Escalas\CategoriaClientes;
use App\Http\Livewire\Escalas\ReportesEscalas;

// Logistica de Entregas
use App\Http\Livewire\Logistica\EquiposEntrega;
use App\Http\Livewire\Logistica\DistribucionEntrega;
use App\Http\Livewire\Logistica\ConfirmacionEntrega;
use App\Http\Livewire\Logistica\ReporteLogistica;

use App\Http\Livewire\Comisiones\Escalado\Configuracion as confcomisiones;
use App\Http\Livewire\Comisiones\Escalado\MisComisiones;
use App\Http\Livewire\Comisiones\Escalado\ReportesComisionesGenerales;
use App\Http\Livewire\Comisiones\Escalado\Conciliacion as ConciliacionComisiones;

/*

/*Escala de precios*/

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    //return view('welcome');
    return redirect('/login');
});

Route::middleware(['auth:sanctum', 'verified', 'check.password.change'])->get('/dashboard', function () {
    return view('/dashboard');
})->name('dashboard');

Route::middleware(['auth:sanctum', 'verified', 'check.password.change'])
    ->get('/dashboard/comercial', App\Http\Livewire\Dashboard\DashboardComercial::class)
    ->name('dashboard.comercial');

// Rutas de cambio obligatorio de contraseña (fuera del grupo protegido para evitar bucle)
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/cambiar-contrasena', function () {
        return view('auth.cambiar-contrasena');
    });
    Route::post('/cambiar-contrasena/guardar', [\App\Http\Livewire\Usuarios\ListarUsuarios::class, 'forzarCambioContrasena']);
});

Route::middleware(['auth:sanctum', 'verified', 'check.password.change'])->group(function () {

    //---------------------------------------FLUJO DE VENTAS-------------------------------//
    Route::get('/flujo/ventas', \App\Http\Livewire\Flujo\Ventas::class)->name('flujo.ventas');
    Route::get('/flujo/pedido', \App\Http\Livewire\Flujo\Pedido::class)->name('flujo.pedido');
    Route::get('/flujo/pedidos', \App\Http\Livewire\Flujo\ListarVentas::class)->name('flujo.pedidos');
    Route::get('/flujo/pedidos/historico', \App\Http\Livewire\Flujo\ListarVentas::class); // alias legacy
    Route::get('/flujo/ventas/historico', \App\Http\Livewire\Flujo\ListarVentas::class)->name('flujo.ventas.historico');
    Route::get('/flujo/prefactura', \App\Http\Livewire\Flujo\Prefactura::class)->name('flujo.prefactura');
    Route::get('/flujo/oferta', \App\Http\Livewire\Flujo\OfertaPedido::class)->name('flujo.oferta');
    Route::get('/flujo/ofertas', \App\Http\Livewire\Flujo\ListarOfertas::class)->name('flujo.ofertas');
    Route::get('/flujo/pedido/editar/{id}', \App\Http\Livewire\Flujo\EditarPedido::class)->name('flujo.pedido.editar');
    Route::get('/flujo/pedido/imprimir/{id}', [\App\Http\Livewire\Flujo\PedidoController::class, 'imprimir']);

    //---------------------------------------GESTIÓN DE MENÚS-------------------------------//
    Route::get('/menu/gestion', \App\Http\Livewire\Menu\GestionMenu::class)->name('menu.gestion');

    // Rutas para Menús
    Route::post('/menu/guardar', [App\Http\Controllers\MenuController::class, 'guardarMenu']);
    Route::get('/menu/obtener/{id}', [App\Http\Controllers\MenuController::class, 'obtenerMenu']);
    Route::put('/menu/actualizar/{id}', [App\Http\Controllers\MenuController::class, 'actualizarMenu']);

    // Rutas para Submenus
    Route::post('/submenu/guardar', [App\Http\Controllers\MenuController::class, 'guardarSubmenu']);
    Route::get('/submenu/obtener/{id}', [App\Http\Controllers\MenuController::class, 'obtenerSubmenu']);
    Route::put('/submenu/actualizar/{id}', [App\Http\Controllers\MenuController::class, 'actualizarSubmenu']);

    //---------------------------------------configuracion-------------------------------//
    Route::get('/configuracion/datos', [Configuracion::class, 'parametros']);
    Route::get('/configuracion/datos/compra', [Configuracion::class, 'datosCompra']);
    Route::get('/datos/mes/actual', [Configuracion::class, 'datosMesActual']);
    Route::get('/datos/mes/anterior', [Configuracion::class, 'datosMesAnterior']);
    Route::get('/editar/configuracion/{estado}', [Configuracion::class, 'editarEstado']);
    Route::get('/configuracion/excel', [Configuracion::class, 'exportarExcel']);
    Route::get('/configuracion/notificaciones/flujo', \App\Http\Livewire\Configuracion\ConfiguracionNotificaciones::class)->name('configuracion.notificaciones.flujo');
    Route::get('/configuracion/codigos-autorizacion', \App\Http\Livewire\Configuracion\ConfiguracionCodigosAutorizacion::class)->name('configuracion.codigos.autorizacion');
    Route::get('/notificaciones/historial', \App\Http\Livewire\NotificacionesHistorial::class)->name('notificaciones.historial');
    Route::get('/alertas/rotacion/{id}/reporte', \App\Http\Livewire\Alertas\AlertasRotacionReporte::class)->name('alertas.rotacion.reporte');
    Route::get('/configuracion/jerarquia', \App\Http\Livewire\Configuracion\JerarquiaOrganizacional::class)->name('configuracion.jerarquia');

    /*
    Inicio de todas las rutas de la Escala de precios
    */


    Route::get('/comisiones/configuracion', confcomisiones::class);
    Route::get('/comisiones/configuracion/rol', [confcomisiones::class,'listaRolesUsuario'])->name('comision.configuracion.rol');
    Route::post('/guardar/parametro/comision', [confcomisiones::class, 'guardarParametroComision']);
    Route::get('/listar/parametros/comision', [confcomisiones::class,'listarParametroComision']);
    Route::post('/desactivar/parametro-comision/{id}',[confcomisiones::class,'desactivarParametro'])->name('parametro.comision.desactivar');
    Route::get('/parametro-comision/{id}', [confcomisiones::class,'obtenerParametro']);
    Route::post('/actualizar/parametro/comision/{id}', [confcomisiones::class,'actualizarParametro']);
    Route::get('/comisiones/configuracion/categorias-precio', [confcomisiones::class,'categoriasPrecioPorCliente'])->name('comision.configuracion.categorias.precio');
    Route::get('/comisiones/configuracion/plantilla-masiva', [confcomisiones::class,'descargarPlantillaMasiva'])->name('comision.configuracion.plantilla.masiva');
    Route::post('/comisiones/configuracion/carga-masiva', [confcomisiones::class,'cargarMasivaComisiones'])->name('comision.configuracion.carga.masiva');
    // Carga selectiva
    Route::get('/comisiones/configuracion/categorias-cliente-activas', [confcomisiones::class,'listaCategoriasClienteActivas'])->name('comision.configuracion.cat.cliente.activas');
    Route::get('/comisiones/configuracion/cat-precio-para-filtro', [confcomisiones::class,'categoriasPrecioParaFiltro'])->name('comision.configuracion.cat.precio.filtro');
    Route::get('/comisiones/configuracion/roles-para-filtro', [confcomisiones::class,'listaRolesParaFiltro'])->name('comision.configuracion.roles.filtro');
    Route::get('/comisiones/configuracion/stats', [confcomisiones::class,'statsComision'])->name('comision.configuracion.stats');
    Route::get('/comisiones/configuracion/resumen-por-rol', [confcomisiones::class,'resumenPorRol'])->name('comision.configuracion.resumen.rol');
    Route::get('/comisiones/configuracion/plantilla-filtrada', [confcomisiones::class,'descargarPlantillaFiltrada'])->name('comision.configuracion.plantilla.filtrada');
    Route::post('/comisiones/configuracion/preview-carga-filtrada', [confcomisiones::class,'previewCargaFiltrada'])->name('comision.configuracion.preview.filtrada');
    Route::post('/comisiones/configuracion/procesar-carga-filtrada', [confcomisiones::class,'procesarCargaFiltrada'])->name('comision.configuracion.procesar.filtrada');
    Route::get('/comisiones/configuracion/roles-calculo', [confcomisiones::class,'listaRolesCalculo'])->name('comision.configuracion.roles.calculo');
    Route::post('/comisiones/configuracion/roles-calculo/toggle', [confcomisiones::class,'toggleCalculoRol'])->name('comision.configuracion.roles.calculo.toggle');




    Route::get('/comisiones/empleado', MisComisiones::class);
    Route::get('/listar/empleado/comision',         [MisComisiones::class, 'listarComisionesEmpleado']);
    Route::get('/comision/empleado/top-productos',  [MisComisiones::class, 'topProductos'])->name('comision.empleado.top.productos');
    Route::get('/comision/empleado/chart-mensual',  [MisComisiones::class, 'chartMensual'])->name('comision.empleado.chart.mensual');
    Route::get('/comision/empleado/detalle-mes',    [MisComisiones::class, 'detalleFacturasMes'])->name('comision.empleado.detalle.mes');


    Route::get('/comisiones/general', ReportesComisionesGenerales::class);

    // Rutas para listas de empleados y roles
    Route::get('/comision/empleados/lista', [ReportesComisionesGenerales::class, 'listarEmpleados']);
    Route::get('/comision/roles/lista', [ReportesComisionesGenerales::class, 'listarRoles']);
    Route::get('/comision/roles/comisionables', [ReportesComisionesGenerales::class, 'listarRolesComisionables']);

    // Rutas para los 5 tipos de reportes
    Route::get('/comision/reporte/empleado', [ReportesComisionesGenerales::class, 'reporteEmpleado']);
    Route::get('/comision/reporte/rol', [ReportesComisionesGenerales::class, 'reporteRol']);
    Route::get('/comision/reporte/usuarios', [ReportesComisionesGenerales::class, 'reporteUsuarios']);
    Route::get('/comision/reporte/productos', [ReportesComisionesGenerales::class, 'reporteProductos']);
    Route::get('/comision/reporte/facturas', [ReportesComisionesGenerales::class, 'reporteFacturas']);
    Route::get('/comision/reporte/reversiones', [ReportesComisionesGenerales::class, 'reporteReversiones']);
    
    // Proyección de comisiones
    Route::get('/comisiones/proyeccion', [\App\Http\Controllers\ProyeccionComisionesController::class, 'index'])->name('comisiones.proyeccion');

    // Rutas nuevas: estadísticas, nómina, ranking y comparativo
    Route::get('/comision/reporte/stats',       [ReportesComisionesGenerales::class, 'stats']);
    Route::get('/comision/reporte/nomina',      [ReportesComisionesGenerales::class, 'reporteNomina']);
    Route::get('/comision/reporte/nomina/detalle', [ReportesComisionesGenerales::class, 'detalleNomina']);
    Route::get('/comision/reporte/proyecciones', [ReportesComisionesGenerales::class, 'reporteProyecciones']);
    Route::get('/comision/reporte/revision/facturas', [ReportesComisionesGenerales::class, 'reporteRevisionFacturasFactura']);
    Route::get('/comision/reporte/revision/productos', [ReportesComisionesGenerales::class, 'reporteRevisionFacturasProductos']);
    Route::get('/comision/reporte/brecha-ap-fc', [ReportesComisionesGenerales::class, 'reporteBrechaApFc']);
    Route::post('/comision/reporte/brecha-ap-fc/reprocesar', [ReportesComisionesGenerales::class, 'reprocesarBrechaApFc']);
    Route::get('/comision/reporte/ranking',     [ReportesComisionesGenerales::class, 'reporteRanking']);
    Route::get('/comision/reporte/comparativo', [ReportesComisionesGenerales::class, 'reporteComparativo']);

    // Ruta para descarga de Excel
    Route::get('/comision/reporte/excel', [ReportesComisionesGenerales::class, 'descargarExcel']);

    // Conciliación de Comisiones
    Route::get('/comisiones/conciliacion',                       ConciliacionComisiones::class)->name('comisiones.conciliacion');
    Route::get('/comisiones/conciliacion/validar-reglas',        [ConciliacionComisiones::class, 'validarReglas'])->name('comisiones.conciliacion.validar');
    Route::get('/comisiones/conciliacion/periodos',              [ConciliacionComisiones::class, 'listarPeriodos'])->name('comisiones.conciliacion.periodos');
    Route::post('/comisiones/conciliacion/conciliar',            [ConciliacionComisiones::class, 'conciliarPeriodo'])->name('comisiones.conciliacion.conciliar');
    Route::post('/comisiones/conciliacion/reabrir',              [ConciliacionComisiones::class, 'reabrirPeriodo'])->name('comisiones.conciliacion.reabrir');
    Route::get('/comisiones/conciliacion/detalle',               [ConciliacionComisiones::class, 'detallePeriodo'])->name('comisiones.conciliacion.detalle');
    Route::get('/comisiones/conciliacion/exportar/empleado',     [ConciliacionComisiones::class, 'exportarResumenEmpleado'])->name('comisiones.conciliacion.exportar.empleado');
    Route::get('/comisiones/conciliacion/exportar/masivo',       [ConciliacionComisiones::class, 'exportarResumenMasivo'])->name('comisiones.conciliacion.exportar.masivo');
    Route::get('/comisiones/conciliacion/auditoria-logs',        [ConciliacionComisiones::class, 'listarAuditoriaLogs'])->name('comisiones.conciliacion.auditoria');
    Route::get('/comisiones/conciliacion/verificar-periodo',     [ConciliacionComisiones::class, 'verificarPeriodoPago'])->name('comisiones.conciliacion.verificar_periodo');
    Route::get('/comisiones/conciliacion/retencion-fuente/resumen', [ConciliacionComisiones::class, 'resumenRetencionFuente'])->name('comisiones.conciliacion.retencion_fuente.resumen');
    Route::post('/comisiones/conciliacion/retencion-fuente/aplicar', [ConciliacionComisiones::class, 'aplicarRetencionFuente'])->name('comisiones.conciliacion.retencion_fuente.aplicar');
    Route::post('/comisiones/conciliacion/retencion-fuente/revertir', [ConciliacionComisiones::class, 'revertirRetencionFuente'])->name('comisiones.conciliacion.retencion_fuente.revertir');
    Route::get('/comisiones/conciliacion/retencion-fuente/historial', [ConciliacionComisiones::class, 'historialRetencionFuente'])->name('comisiones.conciliacion.retencion_fuente.historial');
    // Días de gracia
    Route::get('/comisiones/dias-gracia',                        [ConciliacionComisiones::class, 'listarDiasGracia'])->name('comisiones.dias_gracia.index');
    Route::post('/comisiones/dias-gracia/guardar',               [ConciliacionComisiones::class, 'guardarDiasGracia'])->name('comisiones.dias_gracia.guardar');


    Route::get('/precios', CategoriaPrecios::class);
    Route::get('/clientes/categorias', CategoriaClientes::class);
    Route::get('/descargar-plantilla', [App\Http\Controllers\ExcelController::class, 'descargarPlantilla'])->name('excel.plantilla');
    Route::get('/filtros/marca', function() {
        return \Illuminate\Support\Facades\Cache::remember('filtros_marca', 300, fn () =>
            \App\Models\ModelMarca::select('id','nombre')->orderBy('nombre')->get()
        );
    });

    Route::get('/filtros/categoria', function() {
        return \Illuminate\Support\Facades\Cache::remember('filtros_categoria_producto', 300, fn () =>
            \App\Models\ModelCategoriaProducto::select('id','descripcion as nombre')->orderBy('descripcion')->get()
        );
    });
    Route::get('/filtros/categoria/precios', function() {
        return \Illuminate\Support\Facades\Cache::remember('filtros_categoria_precios', 120, fn () =>
            \App\Models\Escalas\modelCategoriaPrecios::select('id','nombre')->where('estado_id',1)->orderBy('nombre')->get()
        );
    });
    Route::get('/filtros/categoria/precios/por-cliente', function(\Illuminate\Http\Request $req) {
        $catClienteIds = array_values(array_filter(explode(',', $req->input('cat_cliente_ids', ''))));
        $catClienteIds = array_slice(array_map('intval', $catClienteIds), 0, 20); // máx 20
        $cacheKey = 'filtros_cat_precio_' . implode('_', $catClienteIds);
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 120, function () use ($catClienteIds) {
            $query = \Illuminate\Support\Facades\DB::table('categoria_precios')
                ->select('id', 'nombre')
                ->where('estado_id', 1)
                ->orderBy('nombre');
            if (!empty($catClienteIds)) {
                $query->whereIn('cliente_categoria_escala_id', $catClienteIds);
            }
            return $query->get();
        });
    });
    Route::get('/filtros/categoria/cliente', function() {
        return \Illuminate\Support\Facades\Cache::remember('filtros_cat_cliente', 120, fn () =>
            \App\Models\Escalas\modelCategoriaCliente::select('id','nombre_categoria as nombre')
                ->where('estado_id', 1)->orderBy('nombre_categoria')->get()
        );
    });
    Route::get('/filtros/marca/buscar', function(\Illuminate\Http\Request $req) {
        $q    = $req->input('q', '');
        $page = max(1, (int) $req->input('page', 1));
        $per  = 20;
        $base = \App\Models\ModelMarca::select('id', 'nombre')
            ->when($q, fn ($b) => $b->where('nombre', 'LIKE', "%{$q}%"))
            ->orderBy('nombre');
        $total   = $base->count();
        $results = $base->skip(($page - 1) * $per)->take($per)->get();
        return response()->json([
            'results'    => $results->map(fn ($r) => ['id' => $r->id, 'text' => $r->nombre]),
            'pagination' => ['more' => ($page * $per) < $total],
        ]);
    });
    Route::get('/filtros/categoria/buscar', function(\Illuminate\Http\Request $req) {
        $q    = $req->input('q', '');
        $page = max(1, (int) $req->input('page', 1));
        $per  = 20;
        $base = \App\Models\ModelCategoriaProducto::select('id', 'descripcion as nombre')
            ->when($q, fn ($b) => $b->where('descripcion', 'LIKE', "%{$q}%"))
            ->orderBy('descripcion');
        $total   = $base->count();
        $results = $base->skip(($page - 1) * $per)->take($per)->get();
        return response()->json([
            'results'    => $results->map(fn ($r) => ['id' => $r->id, 'text' => $r->nombre]),
            'pagination' => ['more' => ($page * $per) < $total],
        ]);
    });
    Route::get('/filtros/facturas/clientes', function(\Illuminate\Http\Request $req) {
        $q = $req->input('q', '');
        $results = \Illuminate\Support\Facades\DB::table('factura')
            ->select('nombre_cliente as text')
            ->when($q, fn ($b) => $b->where('nombre_cliente', 'LIKE', "%{$q}%"))
            ->distinct()->orderBy('nombre_cliente')->limit(25)->get();
        return response()->json([
            'results' => $results->map(fn ($r) => ['id' => $r->text, 'text' => $r->text]),
        ]);
    });
    Route::get('/filtros/facturas/usuarios', function(\Illuminate\Http\Request $req) {
        $q = $req->input('q', '');
        $results = \Illuminate\Support\Facades\DB::table('users')
            ->select('name as text')
            ->when($q, fn ($b) => $b->where('name', 'LIKE', "%{$q}%"))
            ->orderBy('name')->limit(25)->get();
        return response()->json([
            'results' => $results->map(fn ($r) => ['id' => $r->text, 'text' => $r->text]),
        ]);
    });
    Route::get('/filtros/cotizaciones/clientes', function(\Illuminate\Http\Request $req) {
        $q = $req->input('q', '');
        $results = \Illuminate\Support\Facades\DB::table('cotizacion')
            ->select('nombre_cliente as text')
            ->when($q, fn ($b) => $b->where('nombre_cliente', 'LIKE', "%{$q}%"))
            ->distinct()->orderBy('nombre_cliente')->limit(25)->get();
        return response()->json([
            'results' => $results->map(fn ($r) => ['id' => $r->text, 'text' => $r->text]),
        ]);
    });
    Route::get('/filtros/produtos', function(\Illuminate\Http\Request $req) {
        $q = $req->input('q', '');
        return \Illuminate\Support\Facades\DB::table('producto')
            ->where('nombre', 'LIKE', "%{$q}%")
            ->select('id', \Illuminate\Support\Facades\DB::raw("CONCAT(id, ' — ', nombre) as nombre"))
            ->orderBy('nombre')
            ->limit(40)
            ->get();
    });
    /* Categoria de cliente */
    Route::post('/guardar/categoria/cliente', [CategoriaClientes::class, 'guardarCtaegoria']);
    Route::get('/listar/categoria/cliente', [CategoriaClientes::class, 'listarCategorias']);
    Route::get('/desactivar/categoria/cliente/{idCategoria}', [CategoriaClientes::class, 'desactivarCategoria']);

    /* Categoria de Precios */

    Route::post('/guardar/categoria/precios', [CategoriaPrecios::class, 'guardarCtaegoria']);
    Route::get('/listar/categoria/precios', [CategoriaPrecios::class, 'listarCategorias']);
    Route::get('/desactivar/categoria/precios/{idCategoria}', [CategoriaPrecios::class, 'desactivarCategoria']);
    Route::get('/reactivar/categoria/precios/{idCategoria}', [CategoriaPrecios::class, 'reactivarCategoria']);
    Route::get('/listar/categorias/precios/por-cliente/{id}', [CategoriaPrecios::class, 'listarCategoriasPorCliente']);
    Route::post('/actualizar/categoria/precios', [CategoriaPrecios::class, 'actualizarCategoria']);
    Route::post('/actualizar/comision/cat-precio', [CategoriaPrecios::class, 'actualizarComisionCatPrecio'])->name('cat.precio.actualizar.comision');
    Route::get('/precios/productos/listar', [CategoriaPrecios::class, 'listarProductosPrecios']);
    Route::post('/precios/producto/actualizar-base', [CategoriaPrecios::class, 'actualizarPrecioBase']);
    /*SUBIDA DE EXCEL */
    // web.php
    Route::post('/importar-excel', [App\Http\Controllers\ExcelController::class, 'importarExcel']);// routes/web.php
    Route::post('/preview-excel-precios', [App\Http\Controllers\ExcelController::class, 'previewExcelPrecios'])
    ->name('preview.excel.precios');

    Route::post('/finalizar-excel-precios', [App\Http\Controllers\ExcelController::class, 'finalizarExcelPrecios'])
    ->name('finalizar.excel.precios');
    Route::post('/procesar-excel-precios', [App\Http\Controllers\ExcelController::class, 'procesarExcelPrecios'])
    ->name('procesar.excel.precios');
    Route::get('/exportar/precios/por-cliente/{clienteCatId}', [App\Http\Controllers\ExcelController::class, 'exportarPreciosPorCliente'])
    ->name('exportar.precios.por.cliente');
    Route::get('/exportar/precios/por-categoria/{clienteCatId}/{categoriaPrecioId}', [App\Http\Controllers\ExcelController::class, 'exportarPreciosPorCategoriaPrecio'])
    ->name('exportar.precios.por.categoria');

    /* Gestión masiva de clientes */

        Route::get('/clientes/plantilla-categorias', [ClienteLW::class,'descargarPlantillaCategoriaClientes'])
            ->name('clientes.plantilla.categorias');

            Route::post('/clientes/preview-categorias', [ClienteLW::class,'procesarPreviewCategorias'])
            ->name('clientes.preview.categorias');
        Route::post('/clientes/importar-categorias', [ClienteLW::class,'importarCategoriaClientes'])
            ->name('clientes.importar.categorias');
        Route::get('/clientes/categorias-escala', [ClienteLW::class,'listaCategoriasEscala'])
    ->name('clientes.categorias.escala');

    /* REPORTE DE PRECIOS POR PRODUCTO */

    Route::get('/reportes/escalas', ReportesEscalas::class);
    Route::get('/descargar/productos/filtros', [ReportesEscalas::class, 'descargarPrecios'])->name('excel.productos.filtros');
    Route::get('/escalas/productos/filtrados', [ReportesEscalas::class, 'listarProductosFiltrados'])
        ->middleware('throttle:40,1');

    // Reportes — JSON para DataTables (client-side)
    Route::get('/reportes/escalas/cobertura',           [ReportesEscalas::class, 'coberturaJson']);
    Route::get('/reportes/escalas/sin-precios-cat',     [ReportesEscalas::class, 'sinPreciosCatJson']);
    Route::get('/reportes/escalas/sin-precios-prod',    [ReportesEscalas::class, 'productosSinPreciosJson']);
    Route::get('/reportes/escalas/comparativo',         [ReportesEscalas::class, 'comparativoJson']);
    Route::get('/reportes/escalas/resumen-cat-precio',  [ReportesEscalas::class, 'resumenCatPrecioJson']);
    Route::get('/reportes/escalas/comisiones',          [ReportesEscalas::class, 'comisionesJson']);

    // Reportes — descargas Excel
    Route::get('/exportar/cobertura-categorias',    [ReportesEscalas::class, 'descargarCobertura'])->name('exportar.cobertura.categorias');
    Route::get('/exportar/cat-sin-precios',         [ReportesEscalas::class, 'descargarSinPreciosCat'])->name('exportar.cat.sin.precios');
    Route::get('/exportar/productos-sin-precios',   [ReportesEscalas::class, 'descargarProductosSinPrecios'])->name('exportar.productos.sin.precios');
    Route::get('/exportar/comparativo-produto',     [ReportesEscalas::class, 'descargarComparativo'])->name('exportar.comparativo.produto');
    Route::get('/exportar/resumen-cat-precio',      [ReportesEscalas::class, 'descargarResumenCatPrecio'])->name('exportar.resumen.cat.precio');


    //-----------------------Bodega---------------------------------------------------------------------------------------------------------------------//
    Route::get('/bodega', Bodega::class);
    Route::get('/bodega/prod', ProductoBodegas::class);

    Route::get('/consulta/prod/bodega/{selectBodega}', [ProductoBodegas::class, 'consultaProducto']);


    Route::get('/bodega/editar/screen', BodegaEditar::class);

    Route::post('/bodega/crear',  [Bodega::class, 'crearBodega']);
    Route::get('/bodega/listar/bodegas', [BodegaEditar::class, 'listarBodegas']);
    Route::post('/bodega/desactivar', [BodegaEditar::class, 'desactivarBodega']);
    Route::post('/bodega/datos', [BodegaEditar::class, 'obtenerDatos']);
    Route::post('/bodega/editar', [BodegaEditar::class, 'editarBodega']);

    Route::get('/bodega/segmentos/listar/{idBodega}', [BodegaEditar::class, 'obtenerSegmentoDeBodega']);
    Route::post('/guardar/seccion', [BodegaEditar::class, 'guardarSeccion']);
    Route::post('/guardar/segmento', [BodegaEditar::class, 'guardarSegmento']);


    //----------------------Proveedores-----------------------------------------------------------------------------------------------------------------//

    Route::get('/proveedores', Proveedores::class);
    Route::post('/proveedores/crear',  [Proveedores::class, 'proveerdoresModelInsert']);
    Route::post('/proveedores/obeter/departamentos', [Proveedores::class, 'obtenerDepartamentos']);
    Route::post('/proveedores/obeter/municipios', [Proveedores::class, 'obtenerMunicipios']);
    Route::get('/proveedores/listar/proveedores', [Proveedores::class, 'listarProveedores']);
    Route::post('/proveedores/desactivar', [Proveedores::class, 'desactivarProveedor']);
    Route::post('/proveedores/editar', [Proveedores::class, 'obtenerProveedor']);
    Route::post('/proveedores/editar/guardar', [Proveedores::class, 'editarProveedor']);
    Route::get('/inventario/retenciones', Retenciones::class);
    Route::get('/inventario/retenciones/listar', [Retenciones::class, 'listarRetenciones']);
    Route::post('/proveedores/retencion/crear', [Retenciones::class, 'registrarRetencion']);


    //-------------------------------------------------CLIENTES-----------------------------------------------------------------------------------------//
    Route::get('/clientes', Cliente::class);

    Route::get('/cliente/pais', [Cliente::class, 'opbtenerPais']);
    Route::post('/cliente/departamento', [Cliente::class, 'obtenerDepartamentos']);
    Route::post('/cliente/municipio', [Cliente::class, 'obtenerMunicipio']);

    Route::get('/cliente/tipo/personalidad', [Cliente::class, 'tipoPersonalidad']);
    Route::get('/cliente/tipo/cliente', [Cliente::class, 'tipoCliente']);
    Route::get('/cliente/lista/vendedores', [Cliente::class, 'listaVendedores']);
    Route::post('/cliente/registrar', [Cliente::class, 'guardarCliente']);
    Route::get('/clientes/listar', [Cliente::class, 'listarClientes']);
    Route::post('/clientes/datos/editar', [Cliente::class, 'datosCliente']);
    Route::post('/clientes/editar', [Cliente::class, 'editarCliente']);
    Route::post('/clientes/imagen', [Cliente::class, 'obtenerImagen']);
    Route::post('/clientes/imagen/editar', [Cliente::class, 'cambiarImagenCliente']);
    Route::post('/clientes/desactivar', [Cliente::class, 'desactivarCliente']);
    Route::post('/clientes/activar', [Cliente::class, 'activarCliente']);

    /* ---- Formulario completo de cliente (crear / editar) ---- */
    Route::get('/clientes/form',              [Cliente::class, 'vistaFormCliente'])->name('clientes.form.crear');
    Route::get('/clientes/form/datos/{id}',   [Cliente::class, 'datosFormCliente'])->name('clientes.form.datos');
    Route::get('/clientes/form/{id}',         [Cliente::class, 'vistaFormCliente'])->name('clientes.form.editar');
    Route::post('/clientes/crear-completo',  [Cliente::class, 'crearClienteCompleto'])->name('clientes.crear.completo');
    Route::post('/clientes/editar-completo', [Cliente::class, 'editarClienteCompleto'])->name('clientes.editar.completo');

    /* ---- Crédito ---- */
    Route::post('/clientes/credito/guardar',       [Cliente::class, 'guardarCredito'])->name('clientes.credito.guardar');
    Route::get('/clientes/credito/historico/{id}', [Cliente::class, 'historicoCredito'])->name('clientes.credito.historico');

    /* ---- Observaciones ---- */
    Route::post('/clientes/observacion/guardar',  [Cliente::class, 'guardarObservacion'])->name('clientes.observacion.guardar');
    Route::get('/clientes/observaciones/{id}',    [Cliente::class, 'listarObservaciones'])->name('clientes.observaciones');

    /* ---- Documentos ---- */
    Route::post('/clientes/documento/subir',           [Cliente::class, 'subirDocumento'])->name('clientes.documento.subir');
    Route::post('/clientes/documento/fisico/toggle',   [Cliente::class, 'toggleDocFisico'])->name('clientes.documento.fisico.toggle');
    Route::get('/clientes/documentos/{id}',            [Cliente::class, 'listarDocumentos'])->name('clientes.documentos');
    Route::get('/clientes/documento/ver/{id}',         [Cliente::class, 'verDocumento'])->name('clientes.documento.ver');
    Route::get('/clientes/documento/descargar/{id}',   [Cliente::class, 'descargarDocumento'])->name('clientes.documento.descargar');
    Route::delete('/clientes/documento/{id}',          [Cliente::class, 'eliminarDocumento'])->name('clientes.documento.eliminar');

    /* ---- Historial ---- */
    Route::get('/clientes/historial/{id}',             [Cliente::class, 'historialCambios'])->name('clientes.historial');

    /* ---- Referencias / Autorización Gerencia ---- */
    Route::post('/clientes/referencias/guardar',    [Cliente::class, 'guardarReferencias'])->name('clientes.referencias.guardar');
    Route::post('/clientes/autorizacion/guardar',   [Cliente::class, 'guardarAutorizacionGerencia'])->name('clientes.autorizacion.guardar');



    //----------------------------------------------FACTURACIONES---------------------------------------------------------------------------------------//

    Route::get('/facturas/corporativo', ListadoFacturasUnificado::class)->defaults('tipo', 'corporativo');

    Route::get('/lista/facturas/corporativo', [ListadoFacturas::class, 'listarFacturas']);
    Route::post('/factura/corporativo/anular', [ListadoFacturas::class, 'anularVentaRegistro']);

    Route::get('/facturas/corporativo/vendedor', ListadoFacturasVendedorUnificado::class)->defaults('tipo', 'corporativo');
    Route::get('/lista/facturas/corporativo/vendedor', [LitsadoFacturasVendedor::class, 'listarFacturasVendedor']);

    Route::get('/facturas/corporativo/lista', ListadoFacturasND::class);
    Route::get('/facturas/corporativo/lista/nd', [ListadoFacturasND::class, 'listarFacturas']);

    //-----------------------------------------------Usuarios-------------------------------------------------------------------------------------------//
    Route::get('/usuarios/dashboard', App\Http\Livewire\Usuarios\Dashboard::class)->name('usuarios.dashboard');
    Route::get('/usuarios/widgets', App\Http\Livewire\Usuarios\GestionarWidgets::class)->name('usuarios.widgets');
    Route::get('/usuarios', ListarUsuarios::class);
    Route::get('/usuarios/listar/usuarios', [ListarUsuarios::class, 'listarUsuarios']);
    Route::get('/usuario/info/{idUsuario}', [ListarUsuarios::class, 'infoUsuario']);
    Route::get('/usuario/roles/todos', [ListarUsuarios::class, 'getAllRoles']);
    Route::get('/usuario/roles/{idRol}', [ListarUsuarios::class, 'selectRoles']);
    Route::get('/usuario/baja/{idUsuario}', [ListarUsuarios::class, 'baja']);
    Route::get('/usuario/activar/{idUsuario}', [ListarUsuarios::class, 'activar']);

    /*------------------------------------------------NUEVAS RUTAS DE ACCESO A USUARIOS  */
    Route::post('/usuario/guardar', [ListarUsuarios::class, 'guardarUsuarios']);
    Route::post('/usuario/actualizar', [ListarUsuarios::class, 'actualizarUsuarios']);
    Route::post('/usuario/cambiar-contrasena', [ListarUsuarios::class, 'cambiarContrasenaUsuario']);

    //-----------------------------------------------Roles-------------------------------------------------------------------------------------------//
    Route::get('/usuarios/roles', App\Http\Livewire\Usuarios\Roles::class)->name('roles.gestion');
    Route::get('/roles/listar', [App\Http\Livewire\Usuarios\Roles::class, 'listarRoles']);
    Route::post('/roles/guardar', [App\Http\Livewire\Usuarios\Roles::class, 'guardarRol']);
    Route::get('/roles/obtener/{id}', [App\Http\Livewire\Usuarios\Roles::class, 'obtenerRol']);
    Route::put('/roles/actualizar/{id}', [App\Http\Livewire\Usuarios\Roles::class, 'actualizarRol']);
    Route::post('/roles/cambiar-estado/{id}', [App\Http\Livewire\Usuarios\Roles::class, 'cambiarEstadoRol']);
    Route::delete('/roles/eliminar/{id}', [App\Http\Livewire\Usuarios\Roles::class, 'eliminarRol']);
    Route::get('/roles/estados', [App\Http\Livewire\Usuarios\Roles::class, 'listarEstados']);
    Route::get('/roles/{id}/usuarios', [App\Http\Livewire\Usuarios\Roles::class, 'obtenerUsuariosDelRol']);
    Route::post('/roles/{id}/agregar-usuario', [App\Http\Livewire\Usuarios\Roles::class, 'agregarUsuarioAlRol']);
    Route::post('/roles/{id}/quitar-usuario', [App\Http\Livewire\Usuarios\Roles::class, 'quitarUsuarioDelRol']);
    Route::get('/usuarios/todos', [App\Http\Livewire\Usuarios\Roles::class, 'listarTodosUsuarios']);
    Route::get('/usuarios/{id}/rol-anterior', [App\Http\Livewire\Usuarios\Roles::class, 'obtenerRolAnteriorUsuario']);
    Route::get('/roles/{id}/permisos', [App\Http\Livewire\Usuarios\Roles::class, 'obtenerPermisosDelRol']);
    Route::get('/submenus/todos', [App\Http\Livewire\Usuarios\Roles::class, 'listarTodosSubmenus']);
    // Catálogos de jerarquía de roles
    Route::get('/roles/catalogos/niveles', [App\Http\Livewire\Usuarios\Roles::class, 'listarNiveles'])->name('roles.niveles');
    Route::get('/roles/catalogos/areas',   [App\Http\Livewire\Usuarios\Roles::class, 'listarAreas'])->name('roles.areas');
    Route::get('/roles/reporte-accesos',        [App\Http\Livewire\Usuarios\Roles::class, 'reporteAccesos']);
    Route::get('/roles/reporte-accesos/excel',  [App\Http\Livewire\Usuarios\Roles::class, 'descargarReporteAccesos']);
    Route::get('/roles/reporte-usuarios',       [App\Http\Livewire\Usuarios\Roles::class, 'reporteUsuariosPorRol']);
    Route::get('/roles/reporte-usuarios/excel', [App\Http\Livewire\Usuarios\Roles::class, 'descargarUsuariosPorRol']);

    /*----------------------------------------------- /NUEVAS RUTAS DE ACCESO A USUARIOS  */

    //-----------------------------------------------Bitácora de Login-------------------------------------------------------------------------------------------//
    Route::get('/registro/login', RegistroLogin::class)->name('registro.login');


    //--------------------------------------------Inventario--------------------------------------------------------------------------------------------//

    Route::get('/producto/registro', Producto::class);
    Route::post('/producto/registrar', [Producto::class, 'crearProducto']);
    Route::post('/producto/editar', [Producto::class, 'editarProducto']);
    Route::post('/producto/eliminar', [Producto::class, 'eliminarImagen']);
    Route::get('/producto/marca/listar', [Marca::class, 'listarMarcas']);
    Route::post('/producto/marca/guardar', [Marca::class, 'guardarMarca']);
    Route::post('/producto/marca/datos', [Marca::class, 'obtenerDatos']);
    Route::post('/producto/marca/editar', [Marca::class, 'editarMarca']);




    Route::post('/ruta/imagen/edit', [Producto::class, 'guardarFoto']);
    Route::get('/producto/datos/{id}', [Producto::class, 'listarModalProductoEdit']);

    Route::get('/producto/listar/productos', [Producto::class, 'listarProductos']);
    Route::post('/producto/inactivar', [Producto::class, 'inactivarProducto']);

    // Catálogo Apoyo (vendedores — sin precios)
    Route::get('/producto/apoyo/registro', ProductoApoyo::class);
    Route::post('/producto/apoyo/registrar', [ProductoApoyo::class, 'crearProducto']);
    Route::get('/apoyo/listar/productos', [ProductoApoyo::class, 'listarProductos']);
    Route::post('/apoyo/inactivar', [ProductoApoyo::class, 'inactivarProducto']);
    Route::post('/producto/actualizar/costos', [Producto::class, 'calcularCostos']);
    Route::get('/producto/detalle/{id}', DetalleProducto::class);
    Route::get('/detalle/producto/unidad/{id}', [DetalleProducto::class, 'unidadesVenta']);
    Route::get('/detalle/unidades/venta', [DetalleProducto::class, 'obtenerUnidadesMedida']);
    Route::post('/detalle/unidades/editar', [DetalleProducto::class, 'editarUnidadesVenta']);

    // Gestión de Diseño de Producto (sin campos de precios/costos)
    Route::get('/producto/diseno/{id}', DisenoProducto::class)->name('producto.diseno');
    Route::post('/producto/diseno/editar', [Producto::class, 'editarProductoDiseno'])->name('producto.diseno.editar');
    Route::get('/producto/compra', CompraProducto::class);
    Route::get('/producto/lista/proveedores', [CompraProducto::class, 'listarProveedores']);
    Route::get('/producto/tipo/pagos', [CompraProducto::class, 'listarFormasPago']);
    Route::get('/producto/listar/producto', [CompraProducto::class, 'listarProductos']);
    Route::post('/producto/listar/imagenes', [CompraProducto::class, 'obtenerImagenes']);
    Route::post('/prodcuto/compra/datos', [CompraProducto::class, 'obtenerDatosProducto']);
    Route::post('/producto/compra/retencion', [CompraProducto::class, 'comprobarRetencion']);
    Route::post('/producto/compra/guardar', [CompraProducto::class, 'guardarCompra']);
    Route::get('/producto/listar/compras', ListarCompras::class);
    Route::get('/producto/compras/listar', [ListarCompras::class, 'listarCompras']);
    Route::get('/producto/compras/detalle/{id}', DetalleCompra::class);
    Route::get('/producto/compra/pagos/{id}', PagosCompra::class);
    Route::post('/producto/compra/pagos/registro', [PagosCompra::class, 'registrarPago']);
    Route::get('/producto/compra/pagos/lista/{id}', [PagosCompra::class, 'listarPagos']);
    Route::post('/producto/compra/pagos/datos', [PagosCompra::class, 'DatosCompra']);
    Route::post('/producto/compra/pagos/eliminar', [PagosCompra::class, 'eliminarPago']);
    Route::post('/producto/compra/pagos/comprobar', [PagosCompra::class, 'comprobarRetencion']);
    Route::get('/producto/compra/recibir/{id}', RecibirProducto::class);
    Route::get('/producto/compra/recibir/listar/{id}', [RecibirProducto::class, 'listarProductos']);
    Route::get('/producto/recibir/bodega', [RecibirProducto::class, 'bodegasLista']);
    Route::post('/producto/recibir/segmento', [RecibirProducto::class, 'listarSegmentos']);
    Route::post('/producto/recibir/seccion', [RecibirProducto::class, 'listarSecciones']);
    Route::get('/producto/recibir/Umedidas/{idProducto}', [RecibirProducto::class, 'listarUmedidas']);
    Route::post('/producto/recibir/guardar', [RecibirProducto::class, 'guardarEnBodega']);
    Route::get('/producto/lista/bodega/{id}', [RecibirProducto::class, 'productoBodega']);
    Route::post('/producto/recibir/datos', [RecibirProducto::class, 'datosGeneralesCompra']);
    Route::post('/producto/recibir/excedente', [RecibirProducto::class, 'guardarEnBodegaExcedente']);
    Route::post('/producto/incidencia/bodega', [RecibirProducto::class, 'incidenciaBodega']);
    Route::post('/producto/incidencia/compra', [RecibirProducto::class, 'incidenciaCompra']);
    Route::get('/inventario/compras/incidencias/{id}', Incidencias::class);
    Route::get('/inventario/incidencia/bodega/{id}', [Incidencias::class, 'incidenciasBodega']);
    Route::get('/inventario/incidencia/compra/{id}', [Incidencias::class, 'incidenciaCompra']);
    Route::post('/producto/compra/anular', [AnularCompra::class, 'anularCompraRegistro']);
    Route::get('/inventario/translado', Translados::class);
    Route::get('/translado/lista/productos', [Translados::class, 'listarProductos']);
    Route::get('/translado/lista/bodegas', [Translados::class, 'listarBodegas']);
    Route::get('/translado/producto/lista/{idBodega}/{idProducto}', [Translados::class, 'productoBodega']);
    Route::get('/translado/destino/lista/{numeroFilas}', [Translados::class, 'productoGeneralBodega']);
    Route::post('/translado/producto/bodega', [Translados::class, 'ejectarTranslado']);
    // Endpoints optimizados del buscador de productos (filtrados por bodega, solo vista traslados)
    Route::get('/translado/buscar/productos',      [Translados::class, 'buscarProductos']);
    Route::get('/translado/buscar/top-trasladados',[Translados::class, 'topTrasladados']);
    Route::get('/translado/buscar/categorias',     [Translados::class, 'categoriasBodega']);
    Route::get('/translado/buscar/marcas',         [Translados::class, 'marcasBodega']);
    Route::post('/producto/compra/pagos/eliminar', [PagosCompra::class, 'eliminarPago']);
    Route::post('/producto/compra/pagos/comprobar', [PagosCompra::class, 'comprobarRetencion']);
    Route::get('/compra/retencion/documento/{idCompra}', [PagosCompra::class, 'retencionDocumentoPDF']);

    // Redirección a sistema externo de boletas de compra
    Route::get('/boleta/compra', function () {
        return redirect()->away('https://cadss.hn/orden/ordn_new_product.php');
    });

    //---------------------------------------------------------------------BOLETA DE COMPRA (módulo interno)--------------------------------------------------------------//
    Route::get('/boleta/compra/crear',               CrearBoletaCompra::class);
    Route::post('/boleta/compra/guardar',            [CrearBoletaCompra::class,    'guardarBoletaCompra']);
    Route::post('/boleta/compra/anular',             [CrearBoletaCompra::class,    'anularBoletaCompra']);
    Route::get('/boleta/compra/historial',           HistorialBoletaCompra::class);
    Route::post('/boleta/compra/listar',             [HistorialBoletaCompra::class,'listadoBoletaCompra']);
    Route::get('/boleta/compra/imprimir/{id}',       [HistorialBoletaCompra::class,'imprimirOriginal']);
    Route::get('/boleta/compra/imprimir/copia/{id}', [HistorialBoletaCompra::class,'imprimirCopia']);
    Route::get('/boleta/compra/editar/{id}',         EditarBoletaCompra::class);
    Route::post('/boleta/compra/actualizar',         [HistorialBoletaCompra::class,'actualizarBoletaCompra']);

    // Redirección a sistema externo de boletas de compra
    Route::get('/orden/compra', function () {
        return redirect()->away('https://cadss.hn/orden/ordn_listar_ordenes.php');
    });

    Route::get('/translado/imprimir/{id}', [Translados::class, 'imprimirTranslado']);

    Route::get('/translados/historial', HistorialTranslados::class);
    Route::post('/translados/obtener/listado',      [HistorialTranslados::class, 'historialTranslados']);
    Route::post('/translados/obtener/por-traslado', [HistorialTranslados::class, 'historialPorTraslado']);
    Route::get('/translados/bodegas',               [HistorialTranslados::class, 'listarBodegas']);


    //---------------------------------------------------------------------VENTAS--------------------------------------------------------------------------------//

    // Route::get('/ventas/coporativo', FacturacionCorporativa::class); // Movido a Facturación Unificada
    Route::get('/ventas/lista/clientes', [FacturacionCorporativa::class, 'listarClientes']);
    Route::post('/ventas/datos/cliente', [FacturacionCorporativa::class, 'datosCliente']);

    Route::get('/ventas/tipo/pago', [FacturacionCorporativa::class, 'tipoPagoVenta']);

    Route::get('/ventas/listar/bodegas/{idProducto}', [FacturacionCorporativa::class, 'listarBodegas']);
    Route::get('/ventas/listar/', [FacturacionCorporativa::class, 'productoBodega']);
    Route::get('/productos/buscar', [BusquedaProductoController::class, 'buscar']);
    Route::get('/productos/buscar/categorias', [BusquedaProductoController::class, 'categorias']);
    Route::get('/productos/buscar/marcas', [BusquedaProductoController::class, 'marcas']);
    Route::get('/productos/buscar/top-vendidos', [BusquedaProductoController::class, 'topVendidos']);

    Route::post('/ventas/datos/producto', [FacturacionCorporativa::class, 'obtenerDatosProducto']);
    Route::post('/producto/categorias-disponibles', [FacturacionCorporativa::class, 'obtenerCategoriasProducto']);

    Route::post('/ventas/corporativo/guardar', [FacturacionCorporativa::class, 'guardarVenta']);
    Route::post('/flujo/factura/confirmar', [FacturacionCorporativa::class, 'confirmarFacturaFlujo']);
    Route::get('/ventas/corporativo/vendedores', [FacturacionCorporativa::class, 'listadoVendedores']);
    Route::get('/detalle/venta/{id}', DetalleVenta::class);
    Route::get('/detalle/venta/vendedor/{id}', DetalleVentaVendedor::class);
    Route::get('/lista/productos/factura/{id}', [DetalleVenta::class, 'listarProductosFactura']);
    Route::get('/factura/detalle-productos-escala/{id}', [DetalleVenta::class, 'detalleProductosEscala']);
    Route::get('/lista/ubicacion/producto/{id}', [DetalleVenta::class, 'ubicacionProductos']);
    Route::get('/lista/pagos/venta/{id}', [DetalleVenta::class, 'pagosVenta']);
    Route::get('/venta/cobro/{id}', Cobros::class);
    Route::post('/venta/registro/cobro', [Cobros::class, 'registrarPago']);
    Route::get('/venta/litsado/pagos/{id}', [Cobros::class, 'listarPagos']);
    Route::post('/venta/datos/compra', [Cobros::class, 'DatosCompra']);
    Route::post('/venta/cobro/eliminar', [Cobros::class, 'eliminarPago']);
    Route::get('/factura/cooporativo/{idFactura}', [FacturacionCorporativa::class, 'imprimirFacturaCoorporativa']);
    Route::get('/estatal/factura/{idFactura}', [FacturacionCorporativa::class, 'imprimirFacturaCoorporativa']);
    Route::get('/factura/cooporativoCopia/{idFactura}', [FacturacionCorporativa::class, 'imprimirFacturaCoorporativaCopia']);
    Route::get('/facturaCoor/actaRec/{idFactura}', [FacturacionCorporativa::class, 'imprimirActaCoorporativa']);


    Route::get('/ventas/Configuracion', Configuracion::class);


    Route::get('/ventas/listado/comparacion', Comparacion::class);
    Route::post('/ventas/listado/uno', [Comparacion::class, 'listadoUno']);
    Route::post('/ventas/listado/dos', [Comparacion::class, 'listadoDos']);
    Route::get('/ventas/estado/nd/{idFactura}', [Comparacion::class, 'cambioEstadoND']);
    Route::get('/ventas/estado/dc/{idFactura}', [Comparacion::class, 'cambioEstadoDC']);

    Route::get('/ventas/coorporativo/orden/compra', NumOrdenCompraUnificado::class)->defaults('tipo', 'corporativo');
    Route::get('/coorporativo/ordenCompra/listar', [NumOrdenCompraCoorporativo::class,'listarNumOrdenCompraCoorporativo']);
    Route::get('/coorporativo/ordenCompra/clientes', [NumOrdenCompraCoorporativo::class,'listarClientesCoorporativo']);

    //---------------------------------------------------------------------FACTURACIÓN UNIFICADA------------------------------------------------------------------------//
    // Todas las rutas de facturación apuntan a la vista unificada con su código de tipo
    Route::get('/ventas/estatal', FacturacionUnificada::class)->defaults('codigo', 'estatal');
    Route::get('/ventas/sin/restriccion/gobierno', FacturacionUnificada::class)->defaults('codigo', 'sin_restriccion_gobierno');
    Route::get('/ventas/coporativo', FacturacionUnificada::class)->defaults('codigo', 'corporativa');
    Route::get('/ventas/sin/restriccion/precio', FacturacionUnificada::class)->defaults('codigo', 'sin_restriccion_precio');
    Route::get('/ventas/exonerado/factura', FacturacionUnificada::class)->defaults('codigo', 'exoneradas');
    Route::get('/proforma/cotizacion/2', FacturacionUnificada::class)->defaults('codigo', 'cotizacion_clientes_a');

    //---------------------------------------------------------------------VENTAS ESTATAL--------------------------------------------------------------------------------//


    Route::get('/ventas/estatal/vendedor', ListadoFacturasVendedorUnificado::class)->defaults('tipo', 'estatal');
    Route::get('/listado/ventas/estatal/vendedor', [LitsadoFacturasEstatalVendedor::class, 'listarFacturasEstatalVendedor']);
    Route::get('/estatal/lista/clientes', [FacturacionEstatal::class, 'listarClientes']);
    Route::post('/estatal/datos/cliente', [FacturacionEstatal::class, 'datosCliente']);
    Route::get('/estatal/tipo/pago', [FacturacionEstatal::class, 'tipoPagoVenta']);
    Route::get('/estatal/listar/bodegas/{idProducto}', [FacturacionEstatal::class, 'listarBodegas']);
    Route::post('/estatal/datos/producto', [FacturacionEstatal::class, 'obtenerDatosProducto']);
    Route::post('/estatal/historial/precios', [FacturacionEstatal::class, 'historialPreciosCliente']);

    Route::post('/ventas/estatal/guardar', [FacturacionEstatal::class, 'guardarVenta']);
    Route::get('/ventas/numero/orden', [FacturacionEstatal::class, 'obtenerOrdenCompra']);



    Route::get('/facturas/estatal', ListadoFacturasUnificado::class)->defaults('tipo', 'estatal');
    Route::get('/lista/facturas/estatal', [ListadoFacturaEstatal::class, 'listarFacturas']);
    Route::post('/factura/estatal/anular', [ListadoFacturaEstatal::class, 'anularVentaRegistro']);



    Route::get('/marca/producto', Marca::class);

    //-----------------------------------------------------------------Ventas Exoneradas----------------------------------------------------------------------------//
    // Route::get('/ventas/exonerado/factura', VentasExoneradas::class); // Movido a Facturación Unificada
    Route::get('/exonerado/lista/clientes', [VentasExoneradas::class, 'listarClientes']);
    Route::post('/exonerado/venta/guardar', [VentasExoneradas::class, 'guardarVenta']);
    Route::get('/exonerado/ventas/lista', ListadoFacturasUnificado::class)->defaults('tipo', 'exonerado');
    Route::get('/exonerado/listas/facturas', [ListadoFacturasExonerads::class, 'listarFacturas']);
    Route::get('/exonerado/factura/{id}', [VentasExoneradas::class, 'imprimirFacturaExonerada']);
    Route::get('/exonerado/facturaCopia/{id}', [VentasExoneradas::class, 'imprimirFacturaExoneradaCopia']);
    Route::get('/exonerado/actaRec/{id}', [VentasExoneradas::class, 'imprimirActarepExonerada']);

    Route::get('/exonerado/listar/codigos', [VentasExoneradas::class, 'obtenerCodigoExoneracion']);


    //-------------------------------------seleccionar declaraciones---------------------------------//
    Route::get('/ventas/seleccionar', SeleccionarFactura::class);
    Route::get('/ventas/lista/seleccionar', [SeleccionarFactura::class, 'listadoFacturas']);
    Route::post('/ventas/cambio', [SeleccionarFactura::class, 'cambioEstado']);
    Route::post('/ventas/bloquear/estado', [SeleccionarFactura::class, 'guardarEstado']);
    Route::post('/ventas/seleccionar/mayor', [SeleccionarFactura::class, 'seleccionarMontoMayor']);
    Route::post('/ventas/seleccionar/menor', [SeleccionarFactura::class, 'seleccionarMontoMEnor']);


    //---------------------------------------Proforma y Cotizaciones--------------------------------//

    Route::get('/proforma/cotizacion/{id}', Cotizacion::class);
    Route::get('/cotizacion/clientes', [Cotizacion::class, 'listarClientes']);
    Route::post('/guardar/cotizacion', [Cotizacion::class, 'guardarCotizacion']);
    Route::post('/cotizacion/adjunto/subir', [Cotizacion::class, 'subirAdjunto']);

    //---------------------------------------Prefactura (oferta ganadora)---------------------------//
    Route::get('/flujo/prefactura/crear', \App\Http\Livewire\Flujo\CrearPrefactura::class)->name('flujo.prefactura.crear');
    Route::post('/flujo/prefactura/guardar', [\App\Http\Livewire\Flujo\PrefacturaController::class, 'guardar']);
    Route::get('/prefactura/imprimir/{id}', [\App\Http\Livewire\Flujo\PrefacturaController::class, 'imprimir']);
    Route::post('/cotizacion/prefacturar-desde-oferta', [\App\Http\Livewire\Flujo\PrefacturaController::class, 'prefacturarDesdeOferta']);
    Route::get('/flujo/{id}/pedido-id', [\App\Http\Livewire\Flujo\PrefacturaController::class, 'getPedidoIdByFlujo']);
    Route::get('/prefactura/{id}/tipos-facturacion', [\App\Http\Livewire\Flujo\PrefacturaController::class, 'getTiposFacturacion']);
    Route::post('/prefactura/{id}/facturar', [\App\Http\Livewire\Flujo\PrefacturaController::class, 'registrarFacturacion']);
    Route::post('/prefactura/{id}/facturar-directo', [\App\Http\Livewire\Flujo\PrefacturaController::class, 'facturarDirectamente']);
    Route::get('/configuracion/prefacturacion', \App\Http\Livewire\Configuracion\TiempoPrefacturacion::class)->name('configuracion.prefacturacion');


    //------------------------------------------------------------//
    //------------------------EXPO FERIA-------------------------//
       Route::get('/expo/cotizacion/{id}', expo::class);

        Route::get('/expo/clientes', [expo::class, 'listarClientes']);
        Route::post('/expo/cotizacion', [expo::class, 'guardarCotizacion']);
        Route::get('/productos/listar/', [expo::class, 'productoBodega']);
        Route::get('/info/producto/expo/{id}', [expo::class, 'infoProducto']);
        Route::post('/ventas/datos/producto/expo', [expo::class, 'obtenerDatosProductoExpo']);

        Route::get('/reportes/expo', expoCotiza::class);
        Route::get('/reporte/expo/pedidos/{fecha_inicio}/{fecha_final}', [expoCotiza::class, 'consultaProductoPedido']);



        Route::get('/cotizacion/listado/expo/{id}', ListarCotizacionesExpo::class);
        Route::post('/cotizacion/obtener/listado/expo', [ListarCotizacionesExpo::class, 'listarCotizaciones']);
    //-----------------------------------------------------------//


    Route::post('/editar/cotizacion', [Editarcotizacion::class, 'guardarCotizacion']);
    Route::get('/cotizacion/listado/{id}', ListarCotizaciones::class);
    Route::post('/cotizacion/obtener/listado', [ListarCotizaciones::class, 'listarCotizaciones']);
    Route::get('/cotizacion/imprimir/{id}', [Cotizacion::class, 'imprimirCotizacion']);
    Route::get('/cotizacion/imprimir/catalogo/{id}', [Cotizacion::class, 'imprimirCatalogo']);
    Route::get('/oferta/{id}/ficha-pdf', [Cotizacion::class, 'fichaProductosPdf']);
    Route::get('/proforma/imprimir/{id}', [Cotizacion::class, 'imprimirProforma']);
    Route::get('/cotizacion/validar-proforma/{id}', [Cotizacion::class, 'validarProforma']);
    Route::get('/cotizacion/por-pedido/{pedidoId}', [Cotizacion::class, 'ofertasPorPedido']);
    Route::post('/cotizacion/marcar-ganadora', [Cotizacion::class, 'marcarGanadora']);
    Route::get('/cotizacion/facturar/{id}', FacturarCotizacion::class);
    Route::get('/cotizacion/facturar/gobierno/{id}', FacturarCotizacion::class); // Unificado: ahora usa FacturarCotizacion con vista dinámica



    Route::get('/cotizacion/edicion/{id}', Editarcotizacion::class);
    Route::get('/cotizacion/listar/bodegas/{idProducto}', [Cotizacion::class, 'listarBodegas']);


    //--------------------------------------------------------Ajustes------------------------------------------------------//
    Route::get('/inventario/ajustes', Ajustes::class);
    Route::get('/ajustes/listar/bodegas', [Ajustes::class, 'listarBodegas']);
    Route::get('/ajuste/listar/secciones', [Ajustes::class, 'seccionesLista']);
    Route::get('/ajustes/listar/productos', [Ajustes::class, 'listarProductos']);
    Route::get('/ajustes/motivos/listar', [Ajustes::class, 'obtenerTiposAjuste']);
    Route::post('/ajustes/datos/producto', [Ajustes::class, 'datosProducto']);
    Route::post('/ajustes/listado/producto/bodega', [Ajustes::class, 'listarProducto']);

    Route::post('/ajustes/listado/producto/bodega', [Ajustes::class, 'listarProducto']);
    Route::post('/ajustes/guardar/ajuste', [Ajustes::class, 'realizarAjuste']);
    Route::get('/ajustes/imprimir/ajuste/{id}', [Ajustes::class, 'imprimirAjuste']);

    Route::get('/listado/ajustes', ListadoAjustes::class);
    Route::post('/obtener/listado/ajustes', [ListadoAjustes::class, 'listarAjustes']);
    Route::post('/ajuste/anular', [ListadoAjustes::class, 'anularAjuste']);

    Route::get('/inventario/ajuste/ingreso', AjusteIngresoProducto::class);
    Route::get('/ajuste/ingreso/productos', [AjusteIngresoProducto::class, 'obtenerProducto']);
    Route::post('/ajuste/ingreso/datos/producto', [AjusteIngresoProducto::class, 'datosProducto']);
    Route::post('/ajuste/ingreso/guardar', [AjusteIngresoProducto::class, 'realizarAjuste']);
    Route::get('/ajustes/ingreso/listar/bodegas', [AjusteIngresoProducto::class, 'listarBodegas']);
    Route::get('/ajuste/ingreso/listar/secciones', [AjusteIngresoProducto::class, 'seccionesLista']);

    //------------------------------------------------------Facturas Nulas---------------------------------------------//


    Route::get('/ventas/anulado/{id}', ListadoFacturasAnuladas::class);
    Route::post('/ventas/anulado/listado', [ListadoFacturasAnuladas::class, 'listarFacturas']);
    Route::post('/ventas/anulado/detalle', [ListadoFacturasAnuladas::class, 'detalleFacturaAnulada']);



    //------------------------------------------------------------------UNIDADES DE MEDIDA------------------------------------------------------------------------//

    Route::get('/inventario/unidades/medida', UnidadesMedida::class);
    Route::post('/inventario/unidades/guardar', [UnidadesMedida::class, 'guardarUnidad']);
    Route::get('/inventario/unidades/listar', [UnidadesMedida::class, 'listarUnidades']);
    Route::post('/inventario/unidades/datos', [UnidadesMedida::class, 'obtenerDatos']);
    Route::post('/inventario/unidades/editar', [UnidadesMedida::class, 'editarUnidad']);

    //-----------------------------------------------------------------------CAI--------------------------------------------------------------------------------//

    Route::get('/ventas/cai', Cai::class);
    Route::get('/ventas/cai/listar', [Cai::class, 'listarCAI']);
    Route::post('/ventas/cai/guardar', [Cai::class, 'guardarCAI']);
    Route::post('/ventas/cai/boleta/guardar', [Cai::class, 'guardarCAIBoleta']);
    Route::post('/ventas/cai/datos', [Cai::class, 'datosCAI']);
    Route::post('/ventas/cai/editar', [Cai::class, 'editarCAI']);

    //----------------------------------------------------------------------------Bancos------------------------------------------------------------------------//

    Route::get('/banco/bancos', Bancos::class);
    Route::get('/banco/bancos/listar', [Bancos::class, 'listarBancos']);
    Route::post('/banco/bancos/guardar', [Bancos::class, 'guardarBanco']);
    Route::post('/banco/bancos/datos', [Bancos::class, 'obtenerDatos']);
    Route::post('/banco/bancos/editar', [Bancos::class, 'editarBanco']);



    //------------------------------------------------------------------Numero de Orden de Compra--------------------------------------------------------------------------------//

    Route::get('/estatal/ordenes', NumOrdenCompraUnificado::class)->defaults('tipo', 'estatal');
    Route::get('/estatal/ordenes/listar', [NumOrdenCompra::class, 'listarNumOrdenCompra']);
    Route::get('/estatal/ordenes/listar/clientes', [NumOrdenCompra::class, 'listarClientes']);
    Route::post('/estatal/ordenes/guardar', [NumOrdenCompra::class, 'guardarNumOrdenCompra']);
    Route::post('/estatal/ordenes/datos', [NumOrdenCompra::class, 'obtenerNumOrdenCompra']);
    Route::post('/estatal/ordenes/editar', [NumOrdenCompra::class, 'editarNumOrdenCompra']);
    Route::post('/estatal/ordenes/desactivar', [NumOrdenCompra::class, 'desactivarNumOrdenCompra']);




    //------------------------------------------------------------------Codigo Exoneracion--------------------------------------------------------------------------------//

    Route::get('/estatal/exonerado', CodigoExoneracion::class);
    Route::get('/estatal/exonerado/listar', [CodigoExoneracion::class, 'listarCodigoExoneracion']);
    Route::get('/estatal/exonerado/listar/clientes', [CodigoExoneracion::class, 'listarClientes']);
    Route::post('/estatal/exonerado/guardar', [CodigoExoneracion::class, 'guardarCodigoExoneracion']);
    Route::post('/estatal/exonerado/datos', [CodigoExoneracion::class, 'obtenerCodigoExoneracion']);
    Route::post('/estatal/exonerado/editar', [CodigoExoneracion::class, 'editarCodigoExoneracion']);

    Route::post('/estatal/exonerado/desactivar', [CodigoExoneracion::class, 'desactivarCodigoExoneracion']);

    //------------------------------------------------------------------Tipo Ajuste--------------------------------------------------------------------------------//

    Route::get('/inventario/tipoajuste', TipoAjuste::class);
    Route::get('/inventario/tipoajuste/listar', [TipoAjuste::class, 'listarTipoAjuste']);
    Route::post('/inventario/tipoajuste/guardar', [TipoAjuste::class, 'guardarTipoAjuste']);
    Route::post('/inventario/tipoajuste/datos', [TipoAjuste::class, 'obtenerTipoAjuste']);
    Route::post('/inventario/tipoajuste/editar', [TipoAjuste::class, 'editarTipoAjuste']);


    //-------------------------------------------------------------------Nota de Credito-------------------------------------------------------------------------------------------//

    Route::get('/nota/credito', CrearNotaCredito::class);
    ROUTE::get('/nota/credito/clientes', [CrearNotaCredito::class, 'obtenerClientes']);
    Route::get('/nota/credito/facturas', [CrearNotaCredito::class, 'obtenerFactura']);
    Route::get('/nota/credito/motivos', [CrearNotaCredito::class, 'obtenerMotivos']);
    Route::post('/nota/credito/datos/factura', [CrearNotaCredito::class, 'obtenerDetalleFactura']);
    Route::post('/nota/credito/obtener/productos', [CrearNotaCredito::class, 'obtenerProductos']);
    Route::post('/nota/credito/datos/producto', [CrearNotaCredito::class, 'datosProducto']);
    Route::post('/nota/credito/guardar', [CrearNotaCredito::class, 'guardarNotaCredito']);
    Route::get('/nota/credito/gobierno', ListadoNotasND::class);
    Route::post('/nota/credito/lista/gobierno', [ListadoNotasND::class, 'listadoNotaCreditoND']);

    Route::get('/ventas/motivo_credito', MotivoNotaCredito::class);
    Route::get('/ventas/motivo_credito/listar', [MotivoNotaCredito::class, 'listarMotivoNotaCredito']);
    Route::post('/ventas/motivo_credito/guardar', [MotivoNotaCredito::class, 'guardarMotivoNotaCredito']);
    Route::post('/ventas/motivo_credito/datos', [MotivoNotaCredito::class, 'obtenerMotivoNotaCredito']);
    Route::post('/ventas/motivo_credito/editar', [MotivoNotaCredito::class, 'editarMotivoNotaCredito']);

    Route::get('/nota/credito/listado', ListadoNotaCredito::class);
    Route::post('/nota/credito/listar', [ListadoNotaCredito::class, 'listadoNotaCredito']);
    Route::post('/nota/credito/kpis', [ListadoNotaCredito::class, 'kpis']);
    Route::post('/nota/credito/exportar-excel', [ListadoNotaCredito::class, 'exportarExcel']);
    Route::post('/nota/credito/gobierno/exportar-excel', [ListadoNotasND::class, 'exportarExcel']);
    Route::get('/nota/credito/imprimir/{idNota}', [ListadoNotaCredito::class, 'imprimirnotaCreditoOriginal']);
    Route::get('/nota/credito/imprimir/copia/{idNota}', [ListadoNotaCredito::class, 'imprimirnotaCreditoCopia']);
    Route::post('/nota/credito/anular', [CrearNotaCredito::class, 'anularNotaCredito']);



    //--------------------------------------------------------------------------CARDEX----------------------------------------------------------------------------------------------//
    Route::get('/cardex', Cardex::class);
    Route::get('/cardex/listar/bodega', [Cardex::class, 'listarBodegas']);
    Route::get('/cardex/listar/productos', [Cardex::class, 'listarProductos']);

    Route::get('/listado/cardex/{idBodega}/{idProducto}', [Cardex::class, 'listarCardex']);

    Route::get('/cardexn', Cardexdos::class);
    Route::get('/listado/cardex/nuevo/{idProducto}/{idBodega}', [Cardex::class, 'listarCardexNuevo']);

    Route::get('/cardex/general', CardexGeneral::class);
    Route::get('/listado/cardex/general/{fecha_inicio}/{fecha_final}', [CardexGeneral::class, 'listarCardex']);

    Route::get('/cardex/com',  Cardextres::class);

    Route::get('/cardex/com/listar/bodega', [Cardextres::class, 'listarBodegas']);
    Route::get('/cardex/com/listar/productos', [Cardextres::class, 'listarProductos']);
    Route::get('/listado/cardex/com/{idBodega}/{idProducto}', [Cardextres::class, 'listarCardex']);





    //---------------------------------------------------------CRUD CATEGORIAS ---------------------------------------------------------//



    Route::get('/categoria/categorias', Categoria::class);
    Route::get('/categoria/listar', [Categoria::class, 'listarCategorias']);
    Route::post('/categoria/guardar', [Categoria::class, 'guardarCategoria']);
    Route::post('/categoria/datos', [Categoria::class, 'obtenerDatos']);
    Route::post('/categoria/editar', [Categoria::class, 'editarCategoria']);


    Route::get('/sub_categoria/sub_categorias', SubCategoria::class);
    Route::get('/sub_categoria/listar', [SubCategoria::class, 'listarSubCategorias']);
    Route::get('/sub_categoria/listar/categorias', [SubCategoria::class, 'listarCategorias']);
    Route::get('/producto/sub_categoria/listar/{id}', [Producto::class, 'listarSubcategorias']);
    Route::post('/sub_categoria/guardar', [SubCategoria::class, 'guardarSubCategoria']);
    Route::post('/sub_categoria/datos', [SubCategoria::class, 'obtenerDatos']);
    Route::post('/sub_categoria/editar', [SubCategoria::class, 'editarSubCategoria']);

    //---------------------------------------------------------SinRestriccionPrecio-------------------------------------------------------//

    // Route::get('/ventas/sin/restriccion/precio', SinRestriccionPrecio::class); // Duplicado - Movido a Facturación Unificada
    Route::post('/ventas/solicitud/codigo', [SinRestriccionPrecio::class, 'enviarCodigo']);
    Route::post('/ventas/verificar/codigo', [SinRestriccionPrecio::class, 'verificarCodigo']);
    Route::post('/ventas/autorizacion/desactivar', [SinRestriccionPrecio::class, 'desactivarCodigo']);
    //---------------------------------------------------------SinRestriccionPrecio-------------------------------------------------------//

    //////////////////////////////////////////CUENTAS POR COBRAR///////////////////////////////////////////


    Route::get('/cuentas/por/cobrar/listado', listadoCuentasCobrar::class);
    Route::get('/cuentas/cobrar/lista', [listadoCuentasCobrar::class, 'listarFacturasCobrar']);
    Route::get('/ventas/cuentas_por_cobrar', CuentasPorCobrar::class);
    Route::get('/ventas/cuentas_por_cobrar/clientes', [CuentasPorCobrar::class, 'listarClientes']);
    Route::get('/estadoCuenta/imprimir/{idClientepdf}', [CuentasPorCobrar::class, 'imprimirEstadoCuenta']);

    Route::get('/ventas/cuentas_por_cobrar/listar/{id}', [CuentasPorCobrar::class, 'listarCuentasPorCobrar']);
    Route::get('/ventas/cuentas_por_cobrar/listar_intereses/{id}', [CuentasPorCobrar::class, 'listarCuentasPorCobrarInteres']);

    Route::get('/ventas/cuentas_por_cobrar/excel_cuentas/{cliente}', [CuentasPorCobrar::class, 'exportCuentasPorCobrar']);
    Route::get('/ventas/cuentas_por_cobrar/excel_intereses/{cliente}', [CuentasPorCobrar::class, 'exportCuentasPorCobrarInteres']);






    /////////////////////////////APLICACION DE PAGOS/////////////////////////////////
    Route::get('/cuentas_por_cobrar/pagos', Pagos::class);
    Route::get('/aplicacion/pagos/clientes', [Pagos::class, 'listarClientes']);
    Route::get('/aplicacion/pagos/listar/{id}', [Pagos::class, 'listarCuentasPorCobrar']);
    Route::get('/aplicacion/pagos/listar/movimientos/{id}', [Pagos::class, 'listarMovimientos']);
    Route::get('/aplicacion/pagos/listar/abonos/{id}', [Pagos::class, 'listarAbonos']);
    Route::get('/aplicacion/pagos/listar/historico-retenciones/{id}', [Pagos::class, 'listarHistoricoRetenciones']);

    Route::post('/pagos/retencion/guardar', [Pagos::class, 'gestionRetencion']);
    Route::post('/pagos/notacredito/guardar', [Pagos::class, 'gestionNC']);
    Route::post('/pagos/notadebito/guardar', [Pagos::class, 'gestionND']);
    Route::post('/pagos/otrosmov/guardar', [Pagos::class, 'guardarOtroMov']);
    Route::get('/pagos/preview-comisiones', [Pagos::class, 'previewComisionesFactura']);
    Route::post('/pagos/creditos/guardar', [Pagos::class, 'guardarCreditos']);
    Route::get('/pagos/abono/impacto/{abono_id}', [Pagos::class, 'impactoAnularAbono']);
    Route::post('/pagos/abono/anular', [Pagos::class, 'anularAbono']);
    Route::post('/pagos/cerrar/factura', [Pagos::class, 'cerrarFactura']);


    Route::get('/listar/aplicacion/bancos', [Pagos::class, 'datosBanco']);


    Route::get('/estadoCuenta/imprimir/aplicpagos/{idClientepdf}', [Pagos::class, 'imprimirEstadoCuenta']);

    /////////////////////////////ESTADO DE CUENTA — CONSULTA VENDEDOR/////////////////////
    Route::get('/estado_cuenta/vendedor', EstadoCuentaVendedor::class);
    Route::get('/estado_cuenta/vendedor/clientes', [EstadoCuentaVendedor::class, 'listarClientes']);
    Route::get('/estado_cuenta/vendedor/listar/{id}', [EstadoCuentaVendedor::class, 'listarEstadoCuenta']);
    Route::get('/estado_cuenta/vendedor/pdf/{idClientepdf}', [EstadoCuentaVendedor::class, 'imprimirEstadoCuenta']);
    Route::get('/estado_cuenta/vendedor/movimientos/{id}', [EstadoCuentaVendedor::class, 'listarMovimientos']);
    Route::get('/estado_cuenta/vendedor/abonos/{id}', [EstadoCuentaVendedor::class, 'listarAbonos']);
    Route::get('/estado_cuenta/vendedor/exportar/movimientos/{id}', [EstadoCuentaVendedor::class, 'exportarMovimientosExcel']);
    Route::get('/estado_cuenta/vendedor/exportar/abonos/{id}', [EstadoCuentaVendedor::class, 'exportarAbonosExcel']);
    Route::get('/estado_cuenta/vendedor/interes/{facturaId}', [EstadoCuentaVendedor::class, 'consultarInteres']);
    Route::post('/estado_cuenta/vendedor/interes/no-cobrar', [EstadoCuentaVendedor::class, 'registrarNoCobrarInteres']);
    ///////////////////////////////////////////////////////////////////////////////////

    /////////////////////////////CONFIGURACIÓN DE INTERESES MORATORIOS/////////////////////
    Route::get('/configuracion/intereses-moratorios', \App\Http\Livewire\Configuracion\ConfiguracionInteresesMoratorios::class)
         ->name('configuracion.intereses.moratorios');
    ///////////////////////////////////////////////////////////////////////////////////

    /////////////////////////////INTERESES — APLICACIÓN DE PAGOS/////////////////////
    Route::get('/pagos/interes/consultar/{facturaId}', [Pagos::class, 'consultarInteres']);
    Route::post('/pagos/interes/persistir', [Pagos::class, 'persistirInteres']);
    Route::post('/pagos/interes/no-cobrar', [Pagos::class, 'registrarNoCobrarInteres']);







    Route::get('/listar/nc/aplicacion/{idFactura}', [Pagos::class, 'listarNotasCredito']);
    Route::get('/listar/nc/aplicacion/datos/{idNotaCredito}', [Pagos::class, 'datosNotasCredito']);


    Route::get('/listar/nd/aplicacion/{idFactura}', [Pagos::class, 'listarNotasDebito']);
    Route::get('/listar/nd/aplicacion/datos/{idNotaDebito}', [Pagos::class, 'datosNotasDebito']);



    Route::get('/cuentas_por_cobrar/pagos/excel_cuentas/{cliente}', [Pagos::class, 'exportCuentasPorCobrar']);
    Route::get('/cuentas_por_cobrar/pagos/excel_intereses/{cliente}', [Pagos::class, 'exportCuentasPorCobrarInteres']);
    /////////////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////HISTORICO DE PRECIOS//////////////////////////////////////////


    Route::get('/ventas/historico_precios_cliente', HistoricoPreciosCliente::class);

    Route::get('/ventas/historico_precios/clientes', [HistoricoPreciosCliente::class, 'listarClientes']);
    Route::get('/ventas/historico_precios/productos', [HistoricoPreciosCliente::class, 'listarProductos']);
    Route::post('/ventas/historico/precios', [HistoricoPreciosCliente::class, 'listarHistoricoPrecios']);


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Route::get('/ventas/sin/restriccion/precio', SinRestriccionPrecio::class); // Movido a Facturación Unificada
    Route::post('/ventas/solicitud/codigo', [SinRestriccionPrecio::class, 'enviarCodigo']);
    Route::post('/ventas/verificar/codigo', [SinRestriccionPrecio::class, 'verificarCodigo']);
    Route::post('/ventas/autorizacion/desactivar', [SinRestriccionPrecio::class, 'desactivarCodigo']);

    //////////////////////////////////////////////////////REPORTES EXCEL////////////////////////////////////////////////////////

    Route::get('/cliente/excel', [Cliente::class, 'export']);
    Route::get('/producto/excel', [Producto::class, 'export']);
    Route::get('/compras/excel_mes/{mes}', [ListarCompras::class, 'export']);

    Route::get('/bodega/excel', [Bodega::class, 'export']);

    Route::get('/cai/verificacion', [Cai::class, 'verificacionEstadosCai']);

    //---------------------------------------------------Comprovante de entrega-------------------------------------------------//

    Route::get('/comprobante/entrega', CrearComprovante::class);
    Route::get('/comprovante/clientes/lista', [CrearComprovante::class, 'clientesObtener']);
    Route::post('/comprovante/guardar/orden', [CrearComprovante::class, 'guardarComprovante']);
    Route::get('/comprobante/imprimir/{idComprobante}', [CrearComprovante::class, 'imprimirComprobanteEntrega']);
    Route::get('/comprobante/imprimir/copia/{idComprobante}', [CrearComprovante::class, 'imprimirComprobanteEntregaCopia']);
    Route::get('/comprovante/entrega/listado', ListarComprovantes::class);
    Route::get('/comprovante/entrega/listado/activos', [ListarComprovantes::class, 'listarComprovantesActivos']);
    Route::get('/comprovante/entrega/anulados', ListarComprovantesAnulados::class);
    Route::get('/comprovante/entrega/listado/anulados', [ListarComprovantesAnulados::class, 'listarComprovantesAnulados']);
    Route::get('/orden/entrega/facturar/{id}', FacturarComprobante::class);
    Route::post('/orden/entrega/guardar/factura', [FacturarComprobante::class, 'facturarComprobanteCoorporativo']);
    Route::post('/orden/entrega/estatal/factura', [FacturarComprobante::class, 'facturarComprobanteEstatal']);
    Route::get('/comprobante/entrega/anular/{idComprobante}', [ListarComprovantes::class, 'anularComprobante']);
    Route::post('/comprobante/entrega/anular', [ListarComprovantes::class, 'anularComprobante']);

    // Route::get('/ventas/sin/restriccion/gobierno', ...); // Movido a Facturación Unificada



    ////////////////////////////////////////////////////////////////////////////

    Route::get('/crear/vale/{id}', CrearVale::class);
    Route::post('/guardar/vale', [CrearVale::class, 'crearVale']);
    Route::post('/anular/vale', [CrearVale::class, 'anularVale']);
    Route::post('/eliminar/vale', [CrearVale::class, 'eliminarVale']);

    Route::get("/listar/vale/entrega", ListarVales::class);
    Route::get('/vale/listado/general', [ListarVales::class, 'MostrarlistarVales']);
    Route::get('/vale/crear/factura/{id}', FacturarVale::class);
    Route::get('/lista/producto/vale', [CrearVale::class, 'listarProductos']);
    Route::post('/datos/producto/vale', [CrearVale::class, 'datosProducto']);
    Route::get('/imprimir/entrega/{idEntrega}', [CrearVale::class, 'imprimirEntregaProgramada']);

    Route::get('/crear/vale/lista/espera/{id}', ValeListaEspera::class);
    Route::post('/crear/vale/lista/espera/obtenerProductos', [ValeListaEspera::class, 'obtenerProductosVale']);
    Route::post('/vale/lista/espera/guardar', [ValeListaEspera::class, 'guardarVentaVale']);


    Route::get('/vale/restar/inventario', RestarVale::class);
    Route::get('/vale/restar/lista', [RestarVale::class, 'listarVales']);
    Route::post('/vale/restar/lista/anular', [RestarVale::class, 'anularVale']);
    Route::post('/vale/restar/lista/eliminar', [RestarVale::class, 'eliminarVale']);
    Route::get('/vale/comentarios/{id}', [RestarVale::class, 'mostrarNotas']);
    Route::get('/vale/imprimir/{id}', [RestarVale::class, 'imprimirVale']);
    Route::get('/vale/imprimir/{id}', [RestarVale::class, 'imprimirValeCopia']);



    Route::get('/vale/listado/facturas', ListadoFacturasVale::class);
    Route::get('/vale/listado/facturas/obtener', [ListadoFacturasVale::class, 'listarFacturasVale']);

    //Route::get('/listar/vale', [CrearVale::class,'listarVales']);



/*     Route::get('/comisiones', ComisionesPrincipal::class);
    Route::get('/comisiones/facturas/buscar/{mes}/{idVendedor}', [ComisionesPrincipal::class, 'obtenerFacturas']);

    Route::get('/existencia/techo/{mest}/{idVendedort}', [ComisionesPrincipal::class, 'existenciaTecho']);

    Route::get('/comisiones/facturas/buscar2/{mes}/{idVendedor}', [ComisionesPrincipal::class, 'obtenerFacturasSinCerrar']); */


    Route::post('/techo/guardar', [ComisionesGestiones::class, 'guardarTechoMasivo']);
    Route::get('/listar/techos', [ComisionesGestiones::class, 'obtenerVendedores']);
    Route::post('/techo/editar', [ComisionesGestiones::class, 'editarTecho']);


    Route::get('/desglose/factura/{id}', ComisionesComisionar::class);
    Route::get('/desglose/productos/{id}', [ComisionesComisionar::class, 'obtenerDesglose']);

    Route::post('/comision/guardar', [ComisionesComisionar::class, 'guardarComision']);

    Route::post('/comision/guardar/masivo', [ComisionesComisionar::class, 'guardarComisionMasivo']);

    Route::get('/comisiones/historico', ComisionesHistorico::class);

    Route::get('/historico/listar', [ComisionesHistorico::class, 'listarHistorico']);
    Route::get('/historico/listar/mes', [ComisionesHistorico::class, 'historicoMes']);
    Route::post('/historico/registrar/pago', [ComisionesHistorico::class, 'pagoComision']);
    Route::get('/historico/listar/pagos', [ComisionesHistorico::class, 'historicoPagos']);

    Route::get('/listar/pagos', [ComisionesVendedor::class, 'historicoPagos']);
    Route::get('/listar/cerradas', [ComisionesVendedor::class, 'obtenerFacturas']);
    Route::get('/listar/sinCerrar', [ComisionesVendedor::class, 'obtenerFacturasSinCerrar']);


    Route::get('/comisiones/gestion', ComisionesGestiones::class);
    Route::get('/comisiones/vendedor', ComisionesVendedor::class);
    Route::get('/comisiones/comisionar', ComisionesComisionar::class);


    /*************************NOTA DE DEBITO********************************** */

    Route::get('/debito', NotaDebito::class);
    Route::get('/debito/lista/facturas', [NotaDebito::class, 'listarFacturas']);
    Route::get('/debito/lista/montos', [NotaDebito::class, 'listarMontos']);
    Route::post('/debito/monto/guardar', [NotaDebito::class, 'guardarMonto']);
    Route::post('/debito/notad/guardar', [NotaDebito::class, 'guardarNotaDebito']);
    Route::get('/debito/lista/notas', [NotaDebito::class, 'listarnotasDebito']);
    Route::get('/debito/imprimir/{idFactura}', [NotaDebito::class, 'descargarNota']);
    Route::get('/debito/imprimir/copia/{idFactura}', [NotaDebito::class, 'descargarNotaCopia']);
    Route::get('/nota/debito/lista', ListadoNotasDebito::class);
    Route::get('/listado/nota/debito/corporativo/{fechaInicio}/{fechaFinal}', [ListadoNotasDebito::class,'listarnotasDebito']);

    Route::get('/nota/debito/lista/gobierno', ListadoNotasDebitoND::class);
    Route::get('/listado/nota/debito/gobierno/{fechaInicio}/{fechaFinal}', [ListadoNotasDebitoND::class,'listarnotasDebito']);

    Route::post('/nota/debito/kpis', [ListadoNotasDebito::class,'kpis']);
    Route::post('/nota/debito/exportar-excel', [ListadoNotasDebito::class,'exportarExcel']);
    Route::post('/nota/debito/gobierno/exportar-excel', [ListadoNotasDebitoND::class,'exportarExcel']);

    Route::get('/debito/anular/{idNota}', [ListadoNotasDebitoND::class,'anularNota']);

    Route::get('/facturaDia', FacturaDia::class);
    Route::get('/reporte/comision', Prodmes::class);
    Route::get('/reporte/reporteria', Reporteria::class);

    // Dashboard de Ventas BI
    Route::get('/reporte/dashboard-ventas', DashboardVentas::class);
    Route::get('/reporte/dashboard/kpis',                    [DashboardVentas::class,'kpis']);
    Route::get('/reporte/dashboard/ventas-por-mes',          [DashboardVentas::class,'ventasPorMes']);
    Route::get('/reporte/dashboard/heatmap',                 [DashboardVentas::class,'heatmap']);
    Route::get('/reporte/dashboard/ventas-semanales',        [DashboardVentas::class,'ventasSemanales']);
    Route::get('/reporte/dashboard/ventas-semanales/export', [DashboardVentas::class,'exportarDetalleSemanal']);
    Route::get('/reporte/dashboard/resumen-semanal',         [DashboardVentas::class,'resumenSemanal']);
    Route::get('/reporte/dashboard/top-vendedores',          [DashboardVentas::class,'topVendedores']);
    Route::get('/reporte/dashboard/top-clientes',            [DashboardVentas::class,'topClientes']);
    Route::get('/reporte/dashboard/top-productos',           [DashboardVentas::class,'topProductos']);
    Route::get('/reporte/dashboard/catalogo-filtros',        [DashboardVentas::class,'catalogoFiltros']);
    Route::get('/reporte/dashboard/ventas-vendedor-dia',     [DashboardVentas::class,'ventasPorVendedorDia']);
    Route::get('/reporte/dashboard/participacion-tipo-cliente', [DashboardVentas::class,'participacionTipoCliente']);
    Route::get('/reporte/dashboard/top-clientes-vendedor',   [DashboardVentas::class,'topClientesPorVendedor']);
    Route::get('/reporte/dashboard/top-marcas',              [DashboardVentas::class,'topMarcas']);
    Route::get('/reporte/dashboard/top-categorias',          [DashboardVentas::class,'topCategorias']);
    Route::get('/reporte/dashboard/detalle-producto-facturas', [DashboardVentas::class,'detalleProductoFacturas']);
    Route::get('/reporte/dashboard/productos-analitica',     [DashboardVentas::class,'productosAnalitica']);
    Route::get('/reporte/dashboard/ventas-mes-vendedores',   [DashboardVentas::class,'ventasMesVendedores']);
    Route::get('/reporte/dashboard/escalas-comparacion',         [DashboardVentas::class,'escalasComparacion']);
    Route::get('/reporte/dashboard/facturas-comparacion',         [DashboardVentas::class,'facturasComparacion']);
    Route::get('/reporte/dashboard/productos-factura-comparacion',[DashboardVentas::class,'productosFacturaComparacion']);
    Route::get('/reporte/dashboard/top-tele-asesores',              [DashboardVentas::class,'topTeleAsesores']);
    Route::get('/reporte/dashboard/ventas-mes-tele-asesores',       [DashboardVentas::class,'ventasMesTeleAsesores']);
    Route::get('/reporte/dashboard/escalas-comparacion-tla',        [DashboardVentas::class,'escalasComparacionTla']);
    Route::get('/reporte/dashboard/facturas-comparacion-tla',       [DashboardVentas::class,'facturasComparacionTla']);
    Route::get('/reporte/dashboard/evolucion-clientes',      [DashboardVentas::class,'evolucionClientes']);
    Route::get('/reporte/dashboard/evolucion-cantidad-cli',   [DashboardVentas::class,'evolucionCantidadCli']);
    Route::get('/reporte/dashboard/top-productos-cli',        [DashboardVentas::class,'topProductosCli']);
    Route::get('/reporte/dashboard/productos-x-cliente',      [DashboardVentas::class,'productosXCliente']);
    Route::get('/reporte/dashboard/facturas-x-cliente',       [DashboardVentas::class,'facturasXCliente']);
    Route::get('/reporte/dashboard/top-marcas-cli',           [DashboardVentas::class,'topMarcasCli']);
    Route::get('/reporte/dashboard/filtros-productos-cliente', [DashboardVentas::class,'productosPorClienteFecha']);
    Route::get('/reporte/dashboard/filtros-marcas-cliente',    [DashboardVentas::class,'marcasPorClienteFecha']);

    Route::get('/reporte/reporteria/consulta/{fecha_inicio}/{fecha_final}', [Reporteria::class,'consulta']);
    Route::get('/reporte/reporteria/productos', [Reporteria::class,'catalogoProductos']);
    Route::get('/reporte/reporteria/clientes', [Reporteria::class,'consultaClientes']);
    Route::get('/reporte/productos-sin-imagenes', \App\Http\Livewire\Reportes\ProductosSinImagenes::class);
    Route::get('/reporte/productos-sin-imagenes/datos', [\App\Http\Livewire\Reportes\ProductosSinImagenes::class, 'consulta']);
    Route::post('/reporte/productos-sin-imagenes/exportar-excel', [\App\Http\Livewire\Reportes\ProductosSinImagenes::class, 'exportarExcel']);
    Route::post('/reporte/productos-sin-imagenes/exportar-pdf', [\App\Http\Livewire\Reportes\ProductosSinImagenes::class, 'exportarPdf']);


    Route::get('/consulta/{fecha_inicio}/{fecha_final}', [FacturaDia::class,'consulta']);

    Route::get('/consultaComision/{fecha_inicio}/{fecha_final}', [Prodmes::class,'consultaComision']);


    Route::get('/cierre/caja', CierreDiario::class);
    Route::get('/cierre/historico', HistoricoCierres::class);
    Route::get('/cargar/historico', [HistoricoCierres::class,'listadoHistorico']);

    Route::get('/cajaChica/excel/{bitacoraCierre}', [HistoricoCierres::class, 'export']);

    Route::get('/cajaChica/excel/general', [HistoricoCierres::class, 'exportGeneral']);




    Route::get('/contado/{fecha}', [CierreDiario::class,'contado']);
    Route::get('/credito/{fecha}', [CierreDiario::class,'credito']);
    Route::get('/anuladas/{fecha}', [CierreDiario::class,'anuladas']);
    Route::get('/carga/totales/{fecha}', [CierreDiario::class,'cargaTotales']);

    Route::post('/cierre/guardar/{fecha}', [CierreDiario::class,'guardarCierre']);
    Route::post('/registro/tipoC', [CierreDiario::class,'guardarTipoCobro']);

/****************************************Reportes******************************* */
//------------------------------- Cierre Diario ----------------------------//
Route::get('/reporte/Cierrediariorep', Cierrediariorep::class);
Route::get('/reporte/Cierrediariorep/consulta/{tipo}/{fechaInicio}/{fechaFinal}', [Cierrediariorep::class, 'consulta']);
Route::post('/reporte/Cierrediariorep/exportar-pdf/{tipo}/{fechaInicio}/{fechaFinal}', [Cierrediariorep::class, 'exportarPdf'])
    ->name('reporte.Cierrediariorep.pdf');
Route::post('/reporte/Cierrediariorep/exportar-excel/{tipo}/{fechaInicio}/{fechaFinal}', [Cierrediariorep::class, 'exportarExcel'])
    ->name('reporte.Cierrediariorep.excel');


Route::get('/reporte/comisiones', Comisiones::class);

Route::get('/reporte/comisiones/consulta/{fechaInicio}/{fechaFinal}/{vendedor}', [Comisiones::class, 'consulta']);

    //------------------------------- Facturas Anuladas ----------------------------//
Route::get('/reporte/Facturasanuladasrep', Facturasanuladasrep::class);
Route::get('/reporte/Facturasanuladasrep/consulta/{tipo}/{fechaInicio}/{fechaFinal}', [Facturasanuladasrep::class, 'consulta']);
Route::post('/reporte/Facturasanuladasrep/exportar-pdf/{tipo}/{fechaInicio}/{fechaFinal}', [Facturasanuladasrep::class, 'exportarPdf'])
    ->name('reporte.Facturasanuladasrep.pdf');
Route::post('/reporte/Facturasanuladasrep/exportar-excel/{tipo}/{fechaInicio}/{fechaFinal}', [Facturasanuladasrep::class, 'exportarExcel'])
    ->name('reporte.Facturasanuladasrep.excel');
//------------------------------- Libro de Cobros ----------------------------//
Route::get('/reporte/Librocobrosrep', action: Librocobrosrep::class);
Route::get('/reporte/Librocobrosrep/datos', [Librocobrosrep::class, 'consulta']);
Route::get('/reporte/Librocobrosrep/consulta/{tipo}/{fechaInicio?}/{fechaFinal?}', [Librocobrosrep::class, 'consulta']);
Route::post('/reporte/Librocobrosrep/exportar-pdf/{tipo}/{fechaInicio}/{fechaFinal}', [Librocobrosrep::class, 'exportarPdf'])
    ->name('reporte.Librocobrosrep.pdf');
Route::post('/reporte/Librocobrosrep/exportar-excel/{tipo}/{fechaInicio}/{fechaFinal}', [Librocobrosrep::class, 'exportarExcel'])
    ->name('reporte.Librocobrosrep.excel');
//------------------------------- Libro de Ventas ----------------------------//
Route::get('/reporte/Libroventarep', Libroventarep::class);
Route::get('/reporte/Libroventarep/datos', [Libroventarep::class, 'consulta']);
Route::get('/reporte/Libroventarep/consulta/{tipo}/{fechaInicio}/{fechaFinal}', [Libroventarep::class, 'consulta']);
Route::post('/reporte/Libroventarep/exportar-pdf/{tipo}/{fechaInicio}/{fechaFinal}', [Libroventarep::class, 'exportarPdf'])
    ->name('reporte.libro_venta.pdf');
Route::post('/reporte/Libroventarep/exportar-excel/{tipo}/{fechaInicio}/{fechaFinal}', [Libroventarep::class, 'exportarExcel'])
    ->name('reporte.libro_venta.excel');

//------------------------------- Reporte de Clientes ----------------------------//
Route::get('/reporte/clientes',                                     ReporteClientes::class);
Route::get('/reporte/clientes/consulta-general/{vendedorId}/{estado}',   [ReporteClientes::class, 'consultaGeneral']);
Route::get('/reporte/clientes/consulta-sincredito/{vendedorId}/{estado}',[ReporteClientes::class, 'consultaSinCredito']);
Route::get('/reporte/clientes/consulta-gobierno/{vendedorId}/{estado}',  [ReporteClientes::class, 'consultaGobierno']);
Route::post('/reporte/clientes/exportar-pdf/{vendedorId}/{estado}',      [ReporteClientes::class, 'exportarPdf'])->name('reporte.clientes.pdf');
Route::post('/reporte/clientes/exportar-excel/{vendedorId}/{estado}',    [ReporteClientes::class, 'exportarExcel'])->name('reporte.clientes.excel');

//------------------------------- Reporte de Ventas y Cobros ----------------------------//
Route::get('/reporte/ventas-cobros',                                                                ReporteVentasCobros::class);
Route::get('/reporte/ventas-cobros/consulta/{vendedorId}/{clienteId}/{mes}/{anio}',                 [ReporteVentasCobros::class, 'consulta']);
Route::post('/reporte/ventas-cobros/exportar-pdf/{vendedorId}/{clienteId}/{mes}/{anio}',            [ReporteVentasCobros::class, 'exportarPdf'])->name('reporte.ventas_cobros.pdf');
Route::post('/reporte/ventas-cobros/exportar-excel/{vendedorId}/{clienteId}/{mes}/{anio}',          [ReporteVentasCobros::class, 'exportarExcel'])->name('reporte.ventas_cobros.excel');
Route::post('/reporte/ventas-cobros/exportar-excel-async/{vendedorId}/{clienteId}/{mes}/{anio}',    [ReporteVentasCobros::class, 'exportarExcelAsync'])->name('reporte.ventas_cobros.excel.async');
Route::get('/reporte/ventas-cobros/exportar-excel-estado/{token}',                                   [ReporteVentasCobros::class, 'estadoExportExcel'])->name('reporte.ventas_cobros.excel.estado');
Route::get('/reporte/ventas-cobros/exportar-excel-descargar/{token}',                                [ReporteVentasCobros::class, 'descargarExportExcel'])->name('reporte.ventas_cobros.excel.descargar');
Route::get('/reporte/ventas-cobros/datos',                                                          [ReporteVentasCobros::class, 'consultaDatos']);
Route::get('/reporte/ventas-cobros/kpis',                                                           [ReporteVentasCobros::class, 'kpis']);
Route::get('/reporte/ventas-cobros/expediente/{facturaId}',                                         [ReporteVentasCobros::class, 'expediente']);
Route::post('/reporte/ventas-cobros/actualizar-f01/{facturaId}',                                    [ReporteVentasCobros::class, 'actualizarF01'])->name('reporte.ventas_cobros.actualizar_f01');

  //------------------------------- Logistica de Entregas ----------------------------//

    // Equipos de Entrega
    Route::get('/logistica/equipos', EquiposEntrega::class);
    Route::get('/logistica/equipos/listar', [EquiposEntrega::class, 'listarEquipos'])->name('logistica.equipos.listar');
    Route::post('/logistica/equipos/guardar', [EquiposEntrega::class, 'guardarEquipo'])->name('logistica.equipos.guardar');
    Route::get('/logistica/equipos/obtener/{equipoId}', [EquiposEntrega::class, 'obtenerEquipo']);
    Route::post('/logistica/equipos/actualizar', [EquiposEntrega::class, 'actualizarEquipo']);
    Route::get('/logistica/equipos/miembros/{equipoId}', [EquiposEntrega::class, 'obtenerMiembros']);
    Route::post('/logistica/equipos/desactivar/{equipoId}', [EquiposEntrega::class, 'desactivarEquipo']);
    Route::post('/logistica/equipos/agregar-miembro', [EquiposEntrega::class, 'agregarMiembro']);
    Route::post('/logistica/equipos/remover-miembro/{miembroId}', [EquiposEntrega::class, 'removerMiembro']);

    // Distribucion de Entregas
    Route::get('/logistica/distribuciones', DistribucionEntrega::class)->name('logistica.distribuciones');
    Route::get('/logistica/distribuciones/nueva', [DistribucionEntrega::class, 'nuevaDistribucion'])->name('logistica.distribuciones.nueva');
    Route::get('/logistica/distribuciones/listar', [DistribucionEntrega::class, 'listarDistribuciones'])->name('logistica.distribuciones.listar');
    Route::post('/logistica/distribuciones/guardar', [DistribucionEntrega::class, 'guardarDistribucion'])->name('logistica.distribuciones.guardar');
    Route::get('/logistica/distribuciones/facturas/{distribucionId}', [DistribucionEntrega::class, 'obtenerFacturas']);
    Route::get('/logistica/facturas/buscar', [DistribucionEntrega::class, 'buscarFacturas'])->name('logistica.facturas.buscar');
    Route::get('/logistica/facturas/por-numero', [DistribucionEntrega::class, 'obtenerFacturaPorNumero'])->name('logistica.facturas.porNumero');
    Route::get('/logistica/facturas/por-cliente', [DistribucionEntrega::class, 'obtenerFacturasPorCliente'])->name('logistica.facturas.porCliente');
    Route::get('/logistica/facturas/autocompletado', [DistribucionEntrega::class, 'autocompletadoFacturas'])->name('logistica.facturas.autocompletado');
    Route::get('/logistica/facturas/clientes-autocompletado', [DistribucionEntrega::class, 'autocompletadoClientes'])->name('logistica.facturas.clientesAutocompletado');
    Route::get('/logistica/facturas/por-cliente-id', [DistribucionEntrega::class, 'obtenerFacturasPorClienteId'])->name('logistica.facturas.porClienteId');
    Route::get('/logistica/facturas/detalle', [DistribucionEntrega::class, 'obtenerDetalleFactura'])->name('logistica.facturas.detalle');
    Route::post('/logistica/distribuciones/iniciar/{distribucionId}', [DistribucionEntrega::class, 'iniciarDistribucion']);
    Route::post('/logistica/distribuciones/cancelar/{distribucionId}', [DistribucionEntrega::class, 'cancelarDistribucion']);
    Route::get('/logistica/distribuciones/{id}', [DistribucionEntrega::class, 'verDistribucion'])->name('logistica.distribuciones.ver');
    Route::get('/logistica/distribuciones/{id}/carta-entrega', [DistribucionEntrega::class, 'descargarCartaEntrega'])->name('logistica.distribuciones.cartaEntrega');
    Route::get('/logistica/distribuciones/{id}/datos', [DistribucionEntrega::class, 'obtenerDatosDistribucion']);
    Route::post('/logistica/distribuciones/completar/{distribucionId}', [DistribucionEntrega::class, 'completarDistribucion']);
    Route::get('/logistica/distribuciones/validar-completar/{distribucionId}', [DistribucionEntrega::class, 'validarCompletarDistribucion']);
    Route::get('/logistica/facturas/incidencias/{facturaId}', [DistribucionEntrega::class, 'obtenerIncidenciasFactura']);
    Route::post('/logistica/facturas/incidencias/tratamiento', [DistribucionEntrega::class, 'guardarTratamientoIncidencias']);
    Route::post('/logistica/facturas/anular-entrega/{facturaId}', [DistribucionEntrega::class, 'anularEntrega']);
    Route::post('/logistica/facturas/confirmar-entrega/{facturaId}', [DistribucionEntrega::class, 'confirmarEntregaFactura']);
    Route::post('/logistica/facturas/desbloquear/{facturaId}', [DistribucionEntrega::class, 'desbloquearFactura']);
    Route::get('/logistica/distribuciones/validar-incidencias/{distribucionId}', [DistribucionEntrega::class, 'validarIncidenciasSinTratamiento']);
    Route::post('/logistica/distribuciones/finalizar/{distribucionId}', [DistribucionEntrega::class, 'finalizarEntregaDistribucion']);
    Route::get('/logistica/facturas/verificar-disponibilidad', [DistribucionEntrega::class, 'verificarDisponibilidad']);

    // Confirmacion de Entregas
    Route::get('/logistica/confirmacion', ConfirmacionEntrega::class);
    Route::get('/logistica/confirmacion/distribuciones', [ConfirmacionEntrega::class, 'listarDistribucionesPorFecha'])->name('logistica.confirmacion.distribuciones');
    Route::get('/logistica/confirmacion/facturas/{distribucionId}', [ConfirmacionEntrega::class, 'obtenerFacturasParaConfirmacion']);
    Route::post('/logistica/confirmacion/guardar', [ConfirmacionEntrega::class, 'confirmarEntregaProductos'])->name('logistica.confirmacion.guardar');
    Route::post('/logistica/confirmacion/evidencia', [ConfirmacionEntrega::class, 'registrarEvidencia']);
    Route::get('/logistica/confirmacion/evidencias/{distribucionFacturaId}', [ConfirmacionEntrega::class, 'obtenerEvidencias']);
    Route::get('/logistica/confirmacion/productos/{productoId}/incidencias', [ConfirmacionEntrega::class, 'listarIncidenciasProducto']);
    Route::post('/logistica/confirmacion/productos/{productoId}/incidencias', [ConfirmacionEntrega::class, 'registrarIncidenciaProducto']);
    Route::post('/logistica/confirmacion/productos/incidencias/{incidenciaId}/eliminar', [ConfirmacionEntrega::class, 'eliminarIncidenciaProducto']);
    Route::get('/logistica/confirmacion/incidencias/{incidenciaId}/evidencias', [ConfirmacionEntrega::class, 'obtenerEvidenciasIncidencia']);
    Route::post('/logistica/confirmacion/marcar-todos/{distribucionFacturaId}', [ConfirmacionEntrega::class, 'marcarTodosEntregados']);
    Route::get('/logistica/confirmacion/reporte/{distribucionId}', [ConfirmacionEntrega::class, 'obtenerReporteDistribucion']);

    // Dashboard analítica logística
    Route::get('/logistica/reporte_logistica',        ReporteLogistica::class);
    Route::get('/logistica/reportes/filtros',         [ReporteLogistica::class, 'obtenerFiltros']);
    Route::get('/logistica/reportes/kpis',            [ReporteLogistica::class, 'obtenerKPIs']);
    Route::get('/logistica/reportes/evolucion',       [ReporteLogistica::class, 'obtenerEvolucion']);
    Route::get('/logistica/reportes/por-equipo',      [ReporteLogistica::class, 'obtenerPorEquipo']);
    Route::get('/logistica/reportes/estados',         [ReporteLogistica::class, 'obtenerEstados']);
    Route::get('/logistica/reportes/tabla',           [ReporteLogistica::class, 'obtenerTabla']);
    Route::get('/logistica/reportes/tabla-facturas',  [ReporteLogistica::class, 'obtenerTablaFacturas']);



    //------------------------------------------establecer links de storage---------------------------//
  Route::get('/linkstorage', function () {
        Artisan::call('storage:link'); // this will do the command line job
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('optimize:clear');

    });



    // Ruta auto-generada para: Flujo\RevicionInventario
    Route::get('/flujo/revicion_inventario', \App\Http\Livewire\Flujo\RevicionInventario::class);

    // Configuración del Flujo (solo admin)
    Route::get('/flujo/configuracion', \App\Http\Livewire\Flujo\ConfiguracionFlujo::class)->name('flujo.configuracion');

    // Revisión de Crédito
    Route::get('/flujo/revision_creditos', \App\Http\Livewire\Flujo\RevisionCreditos::class)->name('flujo.revision_creditos');

    // Ruta auto-generada para: Reportes\EvaluacionDeClientesPorNivelDeFacturacion
    Route::get('/reportes/evaluacion_de_clientes_por_nivel_de_facturacion', \App\Http\Livewire\Reportes\EvaluacionDeClientesPorNivelDeFacturacion::class);


    // Ruta auto-generada para: Reportes\AnaliticaDeProductos
    Route::get('/reportes/analitica_de_productos', \App\Http\Livewire\Reportes\AnaliticaDeProductos::class);

    // Análisis individual de producto (sección 2 de Analítica de Productos)
    Route::get('/reportes/analitica_de_productos/{productoId}', \App\Http\Livewire\Reportes\AnalisisProductoIndividual::class);


    // Ruta auto-generada para: FlujoDeVenta\ModificarActoresEnFactura
    Route::get('/flujo_de_venta/modificar_actores_en_factura', \App\Http\Livewire\FlujoDeVenta\ModificarActoresEnFactura::class)
        ->name('flujo_de_venta.modificar_actores_en_factura');

    // [auto-routes-anchor]
});
