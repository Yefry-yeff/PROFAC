# REVISIÓN: Scripts de Reportes de Comisiones

## 1. CONTROLADOR PHP
**Archivo:** `app/Http/Livewire/Comisiones/Escalado/ReportesComisionesGenerales.php`

```php
<?php

namespace App\Http\Livewire\Comisiones\Escalado;

use Livewire\Component;
use App\Models\Escalas\modelCategoriaCliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportesComisionesGenerales extends Component
{
    public function render()
    {
        return view('livewire.comisiones.escalado.reportes-comisiones-generales');
    }

    /**
     * Lista de empleados para selector
     */
    public function listarEmpleados(Request $request)
    {
        $search = $request->input('q', '');
        
        $empleados = DB::table('users')
            ->select('id', 'name')
            ->where('estado_id', 1)
            ->where('name', 'LIKE', "%{$search}%")
            ->limit(20)
            ->get();
        
        return response()->json($empleados);
    }

    /**
     * Lista de roles para selector
     */
    public function listarRoles(Request $request)
    {
        $search = $request->input('q', '');
        
        $roles = DB::table('rol')
            ->select('id', 'nombre as name')
            ->where('nombre', 'LIKE', "%{$search}%")
            ->where('estado_id', 1)
            ->limit(20)
            ->get();
        
        return response()->json($roles);
    }

    /**
     * Reporte de comisiones por empleado
     * Filtra comisiones ENTRE las fechas especificadas
     */
    public function reporteEmpleado(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio') . ' 00:00:00';
        $fechaFin = $request->input('fechaFin') . ' 23:59:59';
        $empleadoId = $request->input('filtroEspecifico');

        $query = DB::table('producto_comision as pc')
            ->join('facturas_comision as fc', 'fc.id', '=', 'pc.facturas_comision_id')
            ->join('factura as f', 'f.id', '=', 'fc.factura_id')
            ->join('producto as p', 'p.id', '=', 'pc.producto_id')
            ->join('comision_empleado as ce', function($join) {
                $join->on('ce.rol_id', '=', 'fc.rol_id')
                     ->whereRaw('YEAR(ce.mes_comision) = YEAR(fc.fecha_cierre_factura)')
                     ->whereRaw('MONTH(ce.mes_comision) = MONTH(fc.fecha_cierre_factura)')
                     ->where('ce.estado_id', '=', 1);
            })
            ->join('users as u', 'u.id', '=', 'ce.users_comision')
            ->whereBetween('fc.fecha_cierre_factura', [$fechaInicio, $fechaFin])
            ->where('fc.estado_id', 1)
            ->select(
                'pc.id',
                'pc.id as registro_id',
                'u.id as empleado_id',
                'u.name as empleado',
                'f.cai as factura',
                'p.nombre as producto',
                'pc.cantidad',
                'pc.monto_comision',
                DB::raw('DATE_FORMAT(fc.fecha_cierre_factura, "%Y-%m-%d") as fecha')
            );

        if ($empleadoId) {
            $query->where('u.id', $empleadoId);
        }

        return DataTables::of($query)->make(true);
    }

    /**
     * Reporte de comisiones por rol
     * Filtra comisiones ENTRE las fechas especificadas
     */
    public function reporteRol(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio') . ' 00:00:00';
        $fechaFin = $request->input('fechaFin') . ' 23:59:59';
        $rolId = $request->input('filtroEspecifico');

        $query = DB::table('rol as r')
            ->leftJoin('comision_empleado as ce', 'ce.rol_id', '=', 'r.id')
            ->leftJoin('users as u', 'u.id', '=', 'ce.users_comision')
            ->leftJoin('facturas_comision as fc', function($join) use ($fechaInicio, $fechaFin) {
                $join->on('fc.rol_id', '=', 'r.id')
                     ->where('fc.estado_id', '=', 1)
                     ->whereBetween('fc.fecha_cierre_factura', [$fechaInicio, $fechaFin]);
            })
            ->select(
                'r.id',
                'r.nombre as rol',
                DB::raw('COALESCE(u.name, "Sin empleado") as empleado'),
                DB::raw('COALESCE(SUM(fc.monto_rol), 0) as total_comisiones'),
                DB::raw('COUNT(DISTINCT fc.id) as num_facturas')
            )
            ->where('r.estado_id', 1)
            ->groupBy('r.id', 'r.nombre', 'u.id', 'u.name');

        if ($rolId) {
            $query->where('r.id', $rolId);
        }

        return DataTables::of($query)->make(true);
    }

    /**
     * Reporte general de comisiones por usuario
     * Filtra comisiones ENTRE las fechas especificadas
     */
    public function reporteUsuarios(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio') . ' 00:00:00';
        $fechaFin = $request->input('fechaFin') . ' 23:59:59';

        $query = DB::table('facturas_comision as fc')
            ->join('comision_empleado as ce', 'ce.rol_id', '=', 'fc.rol_id')
            ->join('users as u', 'u.id', '=', 'ce.users_comision')
            ->leftJoin('rol as r', 'r.id', '=', 'fc.rol_id')
            ->join('producto_comision as pc', 'pc.facturas_comision_id', '=', 'fc.id')
            ->whereBetween('fc.fecha_cierre_factura', [$fechaInicio, $fechaFin])
            ->where('fc.estado_id', 1)
            ->where('ce.estado_id', 1)
            ->select(
                'u.id',
                'u.name as usuario',
                DB::raw('COALESCE(r.nombre, "Sin rol") as rol'),
                DB::raw('SUM(fc.monto_rol) as total_comisiones'),
                DB::raw('COUNT(DISTINCT fc.id) as num_facturas'),
                DB::raw('COUNT(DISTINCT pc.producto_id) as num_productos')
            )
            ->groupBy('u.id', 'u.name', 'r.nombre');

        return DataTables::of($query)->make(true);
    }

    /**
     * Reporte general de comisiones por producto
     * Filtra comisiones ENTRE las fechas especificadas
     */
    public function reporteProductos(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio') . ' 00:00:00';
        $fechaFin = $request->input('fechaFin') . ' 23:59:59';

        $query = DB::table('producto_comision as pc')
            ->join('facturas_comision as fc', 'fc.id', '=', 'pc.facturas_comision_id')
            ->join('producto as p', 'p.id', '=', 'pc.producto_id')
            ->join('comision_empleado as ce', 'ce.rol_id', '=', 'fc.rol_id')
            ->whereBetween('fc.fecha_cierre_factura', [$fechaInicio, $fechaFin])
            ->where('fc.estado_id', 1)
            ->where('ce.estado_id', 1)
            ->select(
                'p.id',
                'p.nombre as producto',
                'p.codigo_barra',
                DB::raw('SUM(pc.cantidad) as cantidad_vendida'),
                DB::raw('SUM(pc.monto_comision) as total_comisiones'),
                DB::raw('COUNT(DISTINCT ce.users_comision) as num_empleados')
            )
            ->groupBy('p.id', 'p.nombre', 'p.codigo_barra');

        return DataTables::of($query)->make(true);
    }

    /**
     * Reporte general de comisiones por factura
     * Filtra comisiones ENTRE las fechas especificadas
     */
    public function reporteFacturas(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio') . ' 00:00:00';
        $fechaFin = $request->input('fechaFin') . ' 23:59:59';

        $query = DB::table('facturas_comision as fc')
            ->join('comision_empleado as ce', 'ce.rol_id', '=', 'fc.rol_id')
            ->join('users as u', 'u.id', '=', 'ce.users_comision')
            ->join('factura as v', 'v.id', '=', 'fc.factura_id')
            ->join('cliente as cl', 'cl.id', '=', 'v.cliente_id')
            ->whereBetween('fc.fecha_cierre_factura', [$fechaInicio, $fechaFin])
            ->where('fc.estado_id', 1)
            ->where('ce.estado_id', 1)
            ->select(
                'fc.id',
                'v.cai as factura',
                'cl.nombre as cliente',
                'u.name as empleado',
                'v.total as total_venta',
                'fc.monto_rol as total_comision',
                DB::raw('DATE_FORMAT(fc.fecha_cierre_factura, "%Y-%m-%d") as fecha')
            );

        return DataTables::of($query)->make(true);
    }

    /**
     * Descargar reporte en Excel
     */
    public function descargarExcel(Request $request)
    {
        $tipoReporte = $request->input('tipoReporte');
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');
        $filtroEspecifico = $request->input('filtroEspecifico');

        // Generar nombre de archivo con fecha
        $fecha = now()->format('Y-m-d_His');
        $nombreArchivo = "reporte_comisiones_{$tipoReporte}_{$fecha}.xlsx";

        // Aquí deberías crear una clase Export específica según el tipo
        // Por ahora retorno un mensaje
        return response()->json([
            'message' => 'Funcionalidad de export en desarrollo',
            'tipo' => $tipoReporte
        ]);
    }
}
```

---

## 2. JAVASCRIPT
**Archivo:** `public/js/js_proyecto/comisiones/Escalado/reportesGeneral.js`

```javascript
// Variable global para la tabla
let tablaComisiones = null;

$(document).ready(function () {
    // Configuración de axios
    if (typeof axios !== 'undefined') {
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrf) axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf;
    }

    // Inicializar Select2 para tipo de reporte
    $('#tipoReporte').select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione tipo de reporte',
        allowClear: true
    });

    // Establecer fechas por defecto (último mes)
    const hoy = new Date();
    const hace30Dias = new Date();
    hace30Dias.setDate(hoy.getDate() - 30);
    
    $('#fechaFin').val(hoy.toISOString().split('T')[0]);
    $('#fechaInicio').val(hace30Dias.toISOString().split('T')[0]);

    // Evento cuando cambia el tipo de reporte
    $('#tipoReporte').on('change', function() {
        const tipo = $(this).val();
        const container = $('#containerFiltroEspecifico');
        const label = $('#labelFiltroEspecifico');
        const select = $('#filtroEspecifico');
        
        // Limpiar select
        select.empty().trigger('change');
        
        if (tipo === 'empleado') {
            // Mostrar selector de empleados
            label.text('Seleccionar Empleado');
            container.show();
            cargarSelectEmpleados();
        } else if (tipo === 'rol') {
            // Mostrar selector de roles
            label.text('Seleccionar Rol');
            container.show();
            cargarSelectRoles();
        } else {
            // Ocultar selector para reportes generales
            container.hide();
        }
    });

    // Botón filtrar
    $('#btnFiltrar').on('click', function() {
        const tipoReporte = $('#tipoReporte').val();
        
        if (!tipoReporte) {
            Swal.fire({
                icon: 'warning',
                title: 'Seleccione un tipo de reporte',
                text: 'Debe seleccionar el tipo de reporte que desea generar'
            });
            return;
        }
        
        cargarReporte();
    });

    // Botón descargar
    $('#btnDescargar').on('click', function() {
        const tipoReporte = $('#tipoReporte').val();
        
        if (!tipoReporte) {
            Swal.fire({
                icon: 'warning',
                title: 'Seleccione un tipo de reporte',
                text: 'Debe seleccionar el tipo de reporte que desea descargar'
            });
            return;
        }
        
        descargarExcel();
    });
});

// Cargar selector de empleados
function cargarSelectEmpleados() {
    $('#filtroEspecifico').select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione un empleado',
        allowClear: true,
        ajax: {
            url: '/comision/empleados/lista',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term || '',
                    page: params.page || 1
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(function (item) {
                        return {
                            id: item.id,
                            text: item.name
                        };
                    })
                };
            }
        }
    });
}

// Cargar selector de roles
function cargarSelectRoles() {
    $('#filtroEspecifico').select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione un rol',
        allowClear: true,
        ajax: {
            url: '/comision/roles/lista',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term || '',
                    page: params.page || 1
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(function (item) {
                        return {
                            id: item.id,
                            text: item.name
                        };
                    })
                };
            }
        }
    });
}

// Cargar reporte según tipo
function cargarReporte() {
    const tipoReporte = $('#tipoReporte').val();
    const fechaInicio = $('#fechaInicio').val();
    const fechaFin = $('#fechaFin').val();
    const filtroEspecifico = $('#filtroEspecifico').val();
    
    // Validar fechas
    if (!fechaInicio || !fechaFin) {
        Swal.fire({
            icon: 'warning',
            title: 'Fechas requeridas',
            text: 'Debe seleccionar fecha de inicio y fin'
        });
        return;
    }
    
    // Destruir tabla existente completamente
    if (tablaComisiones) {
        tablaComisiones.destroy();
        tablaComisiones = null;
    }
    
    // Limpiar completamente el tbody
    $('#tbl_comisiones tbody').empty();
    
    // Configurar encabezados según tipo
    let columns = [];
    let titulo = '';
    let endpoint = '';
    
    switch(tipoReporte) {
        case 'empleado':
            titulo = 'Comisiones por Empleado';
            endpoint = '/comision/reporte/empleado';
            columns = [
                { data: 'id', width: '50px' },
                { data: 'empleado' },
                { data: 'factura' },
                { data: 'producto' },
                { data: 'cantidad', className: 'text-right' },
                { data: 'monto_comision', className: 'text-right' },
                { data: 'fecha' }
            ];
            $('#theadComisiones').html(`
                <tr>
                    <th>ID</th>
                    <th>Empleado</th>
                    <th>Factura</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Comisión</th>
                    <th>Fecha</th>
                </tr>
            `);
            break;
            
        case 'rol':
            titulo = 'Comisiones por Rol';
            endpoint = '/comision/reporte/rol';
            columns = [
                { data: 'id', width: '50px' },
                { data: 'rol' },
                { data: 'empleado' },
                { data: 'total_comisiones', className: 'text-right' },
                { data: 'num_facturas', className: 'text-center' }
            ];
            $('#theadComisiones').html(`
                <tr>
                    <th>ID</th>
                    <th>Rol</th>
                    <th>Empleado</th>
                    <th>Total Comisiones</th>
                    <th># Facturas</th>
                </tr>
            `);
            break;
            
        case 'usuarios':
            titulo = 'General de Usuarios';
            endpoint = '/comision/reporte/usuarios';
            columns = [
                { data: 'id', width: '50px' },
                { data: 'usuario' },
                { data: 'rol' },
                { data: 'total_comisiones', className: 'text-right' },
                { data: 'num_facturas', className: 'text-center' },
                { data: 'num_productos', className: 'text-center' }
            ];
            $('#theadComisiones').html(`
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Total Comisiones</th>
                    <th># Facturas</th>
                    <th># Productos</th>
                </tr>
            `);
            break;
            
        case 'productos':
            titulo = 'General por Producto';
            endpoint = '/comision/reporte/productos';
            columns = [
                { data: 'id', width: '50px' },
                { data: 'producto' },
                { data: 'codigo_barra' },
                { data: 'cantidad_vendida', className: 'text-right' },
                { data: 'total_comisiones', className: 'text-right' },
                { data: 'num_empleados', className: 'text-center' }
            ];
            $('#theadComisiones').html(`
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Código Barra</th>
                    <th>Cantidad Vendida</th>
                    <th>Total Comisiones</th>
                    <th># Empleados</th>
                </tr>
            `);
            break;
            
        case 'facturas':
            titulo = 'General por Factura';
            endpoint = '/comision/reporte/facturas';
            columns = [
                { data: 'id', width: '50px' },
                { data: 'factura' },
                { data: 'cliente' },
                { data: 'empleado' },
                { data: 'total_venta', className: 'text-right' },
                { data: 'total_comision', className: 'text-right' },
                { data: 'fecha' }
            ];
            $('#theadComisiones').html(`
                <tr>
                    <th>ID</th>
                    <th>Factura</th>
                    <th>Cliente</th>
                    <th>Empleado</th>
                    <th>Total Venta</th>
                    <th>Total Comisión</th>
                    <th>Fecha</th>
                </tr>
            `);
            break;
    }
    
    $('#tituloTabla').text(titulo);
    
    // Asegurarse de que el DOM está listo antes de inicializar DataTable
    setTimeout(function() {
        // Inicializar DataTable
        tablaComisiones = $('#tbl_comisiones').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            destroy: true,
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json",
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Cargando...</span>'
            },
            ajax: {
                url: endpoint,
                type: 'GET',
                data: function(d) {
                    d.fechaInicio = fechaInicio;
                    d.fechaFin = fechaFin;
                    d.filtroEspecifico = filtroEspecifico;
                },
                error: function(xhr, error, thrown) {
                    console.error('Error al cargar reporte:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo cargar el reporte'
                    });
                }
            },
            columns: columns,
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
            responsive: true
        });
    }, 100);
}

// Descargar Excel
function descargarExcel() {
    const tipoReporte = $('#tipoReporte').val();
    const fechaInicio = $('#fechaInicio').val();
    const fechaFin = $('#fechaFin').val();
    const filtroEspecifico = $('#filtroEspecifico').val();
    
    // Validar fechas
    if (!fechaInicio || !fechaFin) {
        Swal.fire({
            icon: 'warning',
            title: 'Fechas requeridas',
            text: 'Debe seleccionar fecha de inicio y fin'
        });
        return;
    }
    
    // Construir URL con parámetros
    const params = new URLSearchParams({
        tipoReporte: tipoReporte,
        fechaInicio: fechaInicio,
        fechaFin: fechaFin,
        filtroEspecifico: filtroEspecifico || ''
    });
    
    const fecha = new Date().toISOString().split('T')[0];
    const url = `/comision/reporte/excel?${params.toString()}`;
    
    // Crear enlace temporal y descargar
    const link = document.createElement('a');
    link.href = url;
    link.download = `reporte_comisiones_${tipoReporte}_${fecha}.xlsx`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    Swal.fire({
        icon: 'success',
        title: 'Descargando...',
        text: 'Se está descargando el archivo Excel',
        timer: 2000,
        showConfirmButton: false
    });
}
```

---

## 3. ESTRUCTURA DE DATOS

### Tablas Principales:
- **producto_comision**: Productos con sus comisiones individuales
- **facturas_comision**: Facturas con comisión por rol
- **comision_empleado**: Comisiones acumuladas por empleado/mes
- **users**: Usuarios/Empleados
- **rol**: Roles en el sistema
- **factura**: Facturas de venta
- **producto**: Productos

### Relaciones Clave:
```
producto_comision.facturas_comision_id → facturas_comision.id
facturas_comision.factura_id → factura.id
facturas_comision.rol_id → comision_empleado.rol_id (+ año/mes)
comision_empleado.users_comision → users.id
```

### Problema Actual en reporteEmpleado:
El JOIN con `comision_empleado` filtra por:
- `ce.rol_id = fc.rol_id`
- `YEAR(ce.mes_comision) = YEAR(fc.fecha_cierre_factura)`
- `MONTH(ce.mes_comision) = MONTH(fc.fecha_cierre_factura)`

Esto puede estar causando que no se muestren datos si no hay coincidencia exacta de mes/año.

---

## 4. POSIBLE SOLUCIÓN

Simplificar el reporte para que muestre TODOS los productos de facturas con comisión, sin depender de la tabla `comision_empleado`:

```php
public function reporteEmpleado(Request $request)
{
    $fechaInicio = $request->input('fechaInicio') . ' 00:00:00';
    $fechaFin = $request->input('fechaFin') . ' 23:59:59';
    $empleadoId = $request->input('filtroEspecifico');

    $query = DB::table('producto_comision as pc')
        ->join('facturas_comision as fc', 'fc.id', '=', 'pc.facturas_comision_id')
        ->join('factura as f', 'f.id', '=', 'fc.factura_id')
        ->join('producto as p', 'p.id', '=', 'pc.producto_id')
        ->join('rol as r', 'r.id', '=', 'fc.rol_id')
        ->whereBetween('fc.fecha_cierre_factura', [$fechaInicio, $fechaFin])
        ->where('fc.estado_id', 1)
        ->select(
            'pc.id',
            'pc.id as registro_id',
            'r.nombre as empleado',  // Mostrar ROL en lugar de usuario específico
            'f.cai as factura',
            'p.nombre as producto',
            'pc.cantidad',
            'pc.monto_comision',
            DB::raw('DATE_FORMAT(fc.fecha_cierre_factura, "%Y-%m-%d") as fecha')
        );

    if ($empleadoId) {
        // Si se selecciona empleado, filtrar por rol del empleado
        $query->whereExists(function($q) use ($empleadoId) {
            $q->select(DB::raw(1))
              ->from('comision_empleado as ce2')
              ->whereColumn('ce2.rol_id', 'fc.rol_id')
              ->where('ce2.users_comision', $empleadoId);
        });
    }

    return DataTables::of($query)->make(true);
}
```
