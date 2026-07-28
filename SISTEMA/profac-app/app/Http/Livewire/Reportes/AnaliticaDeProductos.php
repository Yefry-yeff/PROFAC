<?php

namespace App\Http\Livewire\Reportes;

use App\Exports\Reportes\AnaliticaProductosExport;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnaliticaDeProductos extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    protected $queryString = [
        'tablaTab' => ['except' => 'criticos'],
    ];

    // ── Filtros ────────────────────────────────────────────────────────────────
    public $filtroCategoria   = '';
    public $filtroMarca       = '';
    public $filtroFechaInicio = '';
    public $filtroFechaFin    = '';
    public $filtroTipoCliente = '';
    public $filtroProducto    = '';
    public $filtroProductoSinImagen = '';
    public $tablaTab          = 'criticos';

    // ── Datos calculados ───────────────────────────────────────────────────────
    public $kpis               = [];
    public $alertas            = [];
    public $tendenciaVentas    = [];
    public $rotacionCategorias = [];
    public $distribucionEstado = [];
    public $tablaProductos     = [];
    public $saludGeneral       = 0;
    public $saludTexto         = '';

    // ── Catálogos filtros ──────────────────────────────────────────────────────
    public $categorias = [];
    public $marcas     = [];
    public $tiposCliente = [];

    public function mount()
    {
        $this->filtroFechaInicio = now()->subDays(90)->format('Y-m-d');
        $this->filtroFechaFin    = now()->format('Y-m-d');
        $this->categorias = DB::table('categoria_producto')->orderBy('descripcion')->get(['id','descripcion'])
            ->map(fn($r) => ['id' => $r->id, 'descripcion' => $r->descripcion])->toArray();
        $this->marcas = DB::table('marca')->orderBy('nombre')->limit(200)->get(['id','nombre'])
            ->map(fn($r) => ['id' => $r->id, 'nombre' => $r->nombre])->toArray();
        $this->tiposCliente = DB::table('cliente_categoria_escala')
            ->where('estado_id', 1)
            ->orderBy('nombre_categoria')->get(['id', 'nombre_categoria'])
            ->map(fn($r) => ['id' => $r->id, 'nombre' => $r->nombre_categoria])->toArray();
        $this->calcularMetricas();
    }

    public function updatedFiltroCategoria()   { $this->resetPage(); $this->calcularMetricas(); }
    public function updatedFiltroMarca()        { $this->resetPage(); $this->calcularMetricas(); }
    public function updatedFiltroFechaInicio()  { $this->calcularMetricas(); }
    public function updatedFiltroFechaFin()     { $this->calcularMetricas(); }
    public function updatedFiltroTipoCliente()  { $this->resetPage(); }
    public function updatedFiltroProducto()     { $this->resetPage(); }
    public function updatedFiltroProductoSinImagen() { $this->resetPage(); }
    public function updatedTablaTab()
    {
        $this->resetPage();
        $this->cargarTabla();
    }

    public function actualizarMetricas()
    {
        $this->calcularMetricas();
        $this->dispatchBrowserEvent('metricas-actualizadas');
    }

    // ── Query base productos ───────────────────────────────────────────────────
    private function baseProductoQuery()
    {
        $q = DB::table('producto as p')
            ->join('sub_categoria as sc', 'sc.id', '=', 'p.sub_categoria_id')
            ->join('categoria_producto as cp', 'cp.id', '=', 'sc.categoria_producto_id')
            ->join('marca as m', 'm.id', '=', 'p.marca_id')
            ->where('p.estado_producto_id', 1);

        if ($this->filtroCategoria) $q->where('cp.id', $this->filtroCategoria);
        if ($this->filtroMarca)     $q->where('m.id', $this->filtroMarca);
        return $q;
    }

    // ── Cálculo principal ──────────────────────────────────────────────────────
    private function calcularMetricas()
    {
        $fi  = $this->filtroFechaInicio ?: now()->subDays(90)->format('Y-m-d');
        $ff  = $this->filtroFechaFin    ?: now()->format('Y-m-d');
        $dias = max(1, Carbon::parse($fi)->diffInDays(Carbon::parse($ff)));

        // IDs con ventas en el período
        $idsConVenta = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->whereBetween('f.created_at', ["$fi 00:00:00", "$ff 23:59:59"])
            ->distinct()->pluck('vhp.producto_id');

        $totalActivos    = $this->baseProductoQuery()->count();
        $conMovimiento   = $this->baseProductoQuery()->whereIn('p.id', $idsConVenta)->count();
        $estancados      = max(0, $totalActivos - $conMovimiento);

        // Valor facturado en el período
        $valorVentas = DB::table('factura')
            ->whereBetween('created_at', ["$fi 00:00:00", "$ff 23:59:59"])
            ->sum('total');

        // Comparativo período anterior
        $fiAnt = Carbon::parse($fi)->subDays($dias)->format('Y-m-d');
        $ffAnt = Carbon::parse($fi)->subDay()->format('Y-m-d');
        $valorAnterior = DB::table('factura')
            ->whereBetween('created_at', ["$fiAnt 00:00:00", "$ffAnt 23:59:59"])
            ->sum('total');
        $pctCambio = $valorAnterior > 0
            ? round((($valorVentas - $valorAnterior) / $valorAnterior) * 100, 1) : 0;

        // Unidades totales vendidas
        $totalUnidades = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->whereBetween('f.created_at', ["$fi 00:00:00", "$ff 23:59:59"])
            ->sum('vhp.cantidad');

        // Rotación promedio mensual por producto activo
        $rotacionPromedio = $totalActivos > 0
            ? round($totalUnidades / max(1, $totalActivos) / max(1, $dias / 30), 1) : 0;

        // Total de facturas
        $totalFacturas = DB::table('factura')
            ->whereBetween('created_at', ["$fi 00:00:00", "$ff 23:59:59"])
            ->count();

        // Riesgo: alta rotacion sin tiempo recuperacion definido
        $riesgoAgotamiento = $this->baseProductoQuery()
            ->whereIn('p.id', $idsConVenta)
            ->whereNull('p.tiempo_recuperacion_meses')
            ->orderByDesc('p.precio_base')
            ->limit(999)
            ->count();

        $this->kpis = [
            'total_activos'      => $totalActivos,
            'con_movimiento'     => $conMovimiento,
            'estancados'         => $estancados,
            'valor_ventas'       => $valorVentas,
            'pct_cambio'         => $pctCambio,
            'rotacion_promedio'  => $rotacionPromedio,
            'total_facturas'     => $totalFacturas,
            'riesgo_agotamiento' => $riesgoAgotamiento,
            'total_unidades'     => $totalUnidades,
        ];

        // ── Salud general
        $this->saludGeneral = $totalActivos > 0
            ? min(100, max(0, round(($conMovimiento / $totalActivos) * 100))) : 0;
        $this->saludTexto = match(true) {
            $this->saludGeneral >= 80 => 'Inventario saludable y en movimiento',
            $this->saludGeneral >= 60 => 'Estable con áreas de atención',
            $this->saludGeneral >= 40 => 'Requiere atención en múltiples categorías',
            default                   => 'Estado crítico — acción inmediata requerida',
        };

        // ── Tendencia ventas últimos 6 meses
        $this->tendenciaVentas = DB::table('factura')
            ->selectRaw("DATE_FORMAT(created_at,'%Y-%m') as periodo, COUNT(*) as facturas, ROUND(SUM(total),2) as monto")
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->groupByRaw("DATE_FORMAT(created_at,'%Y-%m')")
            ->orderByRaw("DATE_FORMAT(created_at,'%Y-%m')")
            ->get()->toArray();

        // ── Rotación por categoría (top 8)
        $this->rotacionCategorias = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->join('producto as p', 'p.id', '=', 'vhp.producto_id')
            ->join('sub_categoria as sc', 'sc.id', '=', 'p.sub_categoria_id')
            ->join('categoria_producto as cp', 'cp.id', '=', 'sc.categoria_producto_id')
            ->whereBetween('f.created_at', ["$fi 00:00:00", "$ff 23:59:59"])
            ->when($this->filtroCategoria, fn($q) => $q->where('cp.id', $this->filtroCategoria))
            ->selectRaw('cp.descripcion as categoria, SUM(vhp.cantidad) as total_vendido')
            ->groupBy('cp.id','cp.descripcion')
            ->orderByDesc('total_vendido')
            ->limit(8)->get()->toArray();

        // ── Distribución estado
        $pctMovimiento = $totalActivos > 0 ? ($conMovimiento / $totalActivos) * 100 : 0;
        $pctEstancado  = $totalActivos > 0 ? ($estancados    / $totalActivos) * 100 : 0;
        $pctRiesgo     = min(15, $pctMovimiento * 0.15);
        $pctSalud      = max(0, $pctMovimiento - $pctRiesgo);
        $pctSobre      = max(0, 100 - $pctSalud - $pctRiesgo - $pctEstancado);

        $this->distribucionEstado = [
            ['label' => 'Saludable',       'valor' => round($pctSalud),      'color' => '#27ae60'],
            ['label' => 'Riesgo',          'valor' => round($pctRiesgo),     'color' => '#f39c12'],
            ['label' => 'Estancado',       'valor' => round($pctEstancado),  'color' => '#95a5a6'],
            ['label' => 'Sobreinventario', 'valor' => round($pctSobre),      'color' => '#3498db'],
        ];

        // ── Alertas
        $this->generarAlertas($fi, $ff, $idsConVenta);

        // ── Tabla
        $this->cargarTabla();
    }

    private function generarAlertas($fi, $ff, $idsConVenta)
    {
        $alertas = [];

        // Alta prioridad: estancados con mayor precio_base (mayor pérdida potencial)
        $estancadosCriticos = $this->baseProductoQuery()
            ->whereNotIn('p.id', $idsConVenta)
            ->orderByDesc('p.precio_base')
            ->limit(4)
            ->get(['p.id','p.nombre','p.precio_base','cp.descripcion as categoria']);

        foreach ($estancadosCriticos as $prod) {
            $alertas[] = [
                'prioridad'   => 'alta',
                'icono'       => 'fa-stop-circle',
                'color'       => '#e74c3c',
                'producto'    => $prod->nombre,
                'categoria'   => $prod->categoria,
                'producto_id' => $prod->id,
                'tipo'        => 'Sin movimiento',
                'descripcion' => 'Sin ventas en el período. Precio base: L '.number_format($prod->precio_base, 2),
                'accion'      => 'Liquidar',
            ];
        }

        // Media prioridad: productos con caída de ventas > 35%
        $mitad = Carbon::parse($fi)->addDays(
            (int)(Carbon::parse($fi)->diffInDays(Carbon::parse($ff)) / 2)
        )->format('Y-m-d');

        $primera = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->whereBetween('f.created_at', ["$fi 00:00:00", "$mitad 23:59:59"])
            ->selectRaw('vhp.producto_id, SUM(vhp.cantidad) as qty')
            ->groupBy('vhp.producto_id')
            ->pluck('qty', 'producto_id');

        $segunda = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->whereBetween('f.created_at', [$mitad.' 00:00:00', "$ff 23:59:59"])
            ->selectRaw('vhp.producto_id, SUM(vhp.cantidad) as qty')
            ->groupBy('vhp.producto_id')
            ->pluck('qty', 'producto_id');

        $caidas = [];
        foreach ($segunda as $pid => $q2) {
            $q1 = $primera[$pid] ?? 0;
            if ($q1 == 0) continue;
            $pct = (($q1 - $q2) / $q1) * 100;
            if ($pct >= 35) $caidas[$pid] = round($pct);
        }
        arsort($caidas);

        foreach (array_slice($caidas, 0, 3, true) as $pid => $caida) {
            $prod = DB::table('producto as p')
                ->join('sub_categoria as sc', 'sc.id', '=', 'p.sub_categoria_id')
                ->join('categoria_producto as cp', 'cp.id', '=', 'sc.categoria_producto_id')
                ->where('p.id', $pid)
                ->first(['p.id','p.nombre','cp.descripcion as categoria']);
            if (!$prod) continue;
            $alertas[] = [
                'prioridad'   => 'media',
                'icono'       => 'fa-arrow-down',
                'color'       => '#f39c12',
                'producto'    => $prod->nombre,
                'categoria'   => $prod->categoria,
                'producto_id' => $prod->id,
                'tipo'        => 'Caída de ventas',
                'descripcion' => "Ventas bajaron {$caida}% respecto a la primera mitad del período",
                'accion'      => 'Revisar',
            ];
        }

        // Informativa: productos con mayor crecimiento
        $crecimientos = [];
        foreach ($segunda as $pid => $q2) {
            $q1 = $primera[$pid] ?? 0;
            if ($q1 == 0) continue;
            $pct = (($q2 - $q1) / $q1) * 100;
            if ($pct >= 50) $crecimientos[$pid] = round($pct);
        }
        arsort($crecimientos);

        foreach (array_slice($crecimientos, 0, 2, true) as $pid => $crec) {
            $prod = DB::table('producto')->find($pid, ['id','nombre']);
            if (!$prod) continue;
            $alertas[] = [
                'prioridad'   => 'info',
                'icono'       => 'fa-arrow-up',
                'color'       => '#27ae60',
                'producto'    => $prod->nombre,
                'categoria'   => '',
                'producto_id' => $prod->id,
                'tipo'        => 'Tendencia positiva',
                'descripcion' => "Ventas crecieron {$crec}% — considerar reabastecimiento anticipado",
                'accion'      => 'Ver',
            ];
        }

        $this->alertas = array_slice($alertas, 0, 7);
    }

    public function cargarTabla()
    {
        $fi = $this->filtroFechaInicio ?: now()->subDays(90)->format('Y-m-d');
        $ff = $this->filtroFechaFin    ?: now()->format('Y-m-d');
        $dias = max(1, Carbon::parse($fi)->diffInDays(Carbon::parse($ff)));

        switch ($this->tablaTab) {
            case 'sin_imagenes':
            case 'precios':
                $this->tablaProductos = [];
                break;

            case 'top_rotacion':
                $this->tablaProductos = $this->baseProductoQuery()
                    ->join('venta_has_producto as vhp', 'vhp.producto_id', '=', 'p.id')
                    ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
                    ->whereBetween('f.created_at', ["$fi 00:00:00", "$ff 23:59:59"])
                    ->selectRaw('p.id, p.nombre, cp.descripcion as categoria, SUM(vhp.cantidad) as total_vendido,
                                 MAX(f.created_at) as ultima_venta, p.precio_base, p.tiempo_recuperacion_meses,
                                 ROUND(SUM(vhp.cantidad) / ?, 1) as rotacion_mensual', [$dias / 30])
                    ->groupBy('p.id','p.nombre','cp.descripcion','p.precio_base','p.tiempo_recuperacion_meses')
                    ->orderByDesc('total_vendido')
                    ->limit(20)->get()->toArray();
                break;

            case 'sin_movimiento':
                $idsConVenta = DB::table('venta_has_producto as vhp')
                    ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
                    ->whereBetween('f.created_at', ["$fi 00:00:00", "$ff 23:59:59"])
                    ->distinct()->pluck('vhp.producto_id');

                $this->tablaProductos = $this->baseProductoQuery()
                    ->whereNotIn('p.id', $idsConVenta)
                    ->selectRaw('p.id, p.nombre, cp.descripcion as categoria, m.nombre as marca,
                                 p.precio_base, p.tiempo_recuperacion_meses,
                                 NULL as ultima_venta, 0 as total_vendido, NULL as rotacion_mensual')
                    ->orderByDesc('p.precio_base')
                    ->limit(20)->get()->toArray();
                break;

            case 'mayor_crecimiento':
                $mitad = Carbon::parse($fi)->addDays((int)($dias / 2))->format('Y-m-d');

                $primera = DB::table('venta_has_producto as vhp')
                    ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
                    ->whereBetween('f.created_at', ["$fi 00:00:00", "$mitad 23:59:59"])
                    ->selectRaw('vhp.producto_id, SUM(vhp.cantidad) as qty')
                    ->groupBy('vhp.producto_id')->pluck('qty','producto_id');

                $segunda = DB::table('venta_has_producto as vhp')
                    ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
                    ->whereBetween('f.created_at', ["$mitad 00:00:00", "$ff 23:59:59"])
                    ->selectRaw('vhp.producto_id, SUM(vhp.cantidad) as qty, MAX(f.created_at) as ultima_venta')
                    ->groupBy('vhp.producto_id')
                    ->get()->keyBy('producto_id');

                $crec = [];
                foreach ($segunda as $pid => $v2) {
                    $q1 = $primera[$pid] ?? 0;
                    if ($q1 == 0) continue;
                    $pct = (($v2->qty - $q1) / $q1) * 100;
                    if ($pct > 0) $crec[$pid] = ['pct' => $pct, 'qty' => $v2->qty, 'ultima' => $v2->ultima_venta];
                }
                arsort($crec);
                $topIds = array_keys(array_slice($crec, 0, 20, true));

                $prods = $this->baseProductoQuery()
                    ->whereIn('p.id', $topIds)
                    ->get(['p.id','p.nombre','cp.descripcion as categoria','p.precio_base','p.tiempo_recuperacion_meses'])
                    ->keyBy('id');

                $result = [];
                foreach ($topIds as $pid) {
                    if (!isset($prods[$pid])) continue;
                    $pr = $prods[$pid];
                    $result[] = (object)[
                        'id' => $pr->id, 'nombre' => $pr->nombre,
                        'categoria' => $pr->categoria, 'precio_base' => $pr->precio_base,
                        'tiempo_recuperacion_meses' => $pr->tiempo_recuperacion_meses,
                        'total_vendido' => $crec[$pid]['qty'],
                        'ultima_venta'  => $crec[$pid]['ultima'],
                        'rotacion_mensual' => round($crec[$pid]['qty'] / max(1, $dias / 30), 1),
                        'pct_crecimiento'  => round($crec[$pid]['pct'], 1),
                    ];
                }
                $this->tablaProductos = $result;
                break;

            default: // criticos: más vendidos del período (necesitan reabastecimiento)
                $this->tablaProductos = $this->baseProductoQuery()
                    ->join('venta_has_producto as vhp', 'vhp.producto_id', '=', 'p.id')
                    ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
                    ->whereBetween('f.created_at', ["$fi 00:00:00", "$ff 23:59:59"])
                    ->selectRaw('p.id, p.nombre, cp.descripcion as categoria, SUM(vhp.cantidad) as total_vendido,
                                 MAX(f.created_at) as ultima_venta, p.precio_base, p.tiempo_recuperacion_meses,
                                 ROUND(SUM(vhp.cantidad) / ?, 1) as rotacion_mensual', [$dias / 30])
                    ->groupBy('p.id','p.nombre','cp.descripcion','p.precio_base','p.tiempo_recuperacion_meses')
                    ->orderByDesc(DB::raw('SUM(vhp.cantidad)'))
                    ->limit(20)->get()->toArray();
                break;
        }
    }

    private function productosSinImagenQuery()
    {
        $busqueda = trim($this->filtroProductoSinImagen);

        return DB::table('producto as p')
            ->leftJoin('sub_categoria as sc', 'sc.id', '=', 'p.sub_categoria_id')
            ->leftJoin('categoria_producto as cp', 'cp.id', '=', 'sc.categoria_producto_id')
            ->leftJoin('marca as m', 'm.id', '=', 'p.marca_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('img_producto as ip')
                    ->whereColumn('ip.producto_id', 'p.id')
                    ->whereNotNull('ip.url_img')
                    ->where('ip.url_img', '<>', '');
            })
            ->when($this->filtroCategoria, fn($query) => $query->where('cp.id', $this->filtroCategoria))
            ->when($this->filtroMarca, fn($query) => $query->where('m.id', $this->filtroMarca))
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($subquery) use ($busqueda) {
                    $subquery->where('p.nombre', 'like', "%{$busqueda}%")
                        ->orWhere('p.codigo_barra', 'like', "%{$busqueda}%")
                        ->orWhere('p.codigo_estatal', 'like', "%{$busqueda}%");
                    if (ctype_digit($busqueda)) {
                        $subquery->orWhere('p.id', (int) $busqueda);
                    }
                });
            })
            ->select('p.id', 'p.nombre', 'p.codigo_barra', 'cp.descripcion as categoria',
                'm.nombre as marca', 'p.precio_base', 'p.estado_producto_id',
                DB::raw("IF(p.estado_producto_id = 1, 'Activo', 'Inactivo') as estado"))
            ->orderBy('p.nombre');
    }

    private function preciosProductosQuery()
    {
        $busqueda = trim($this->filtroProducto);

        return DB::table('precios_producto_carga as ppc')
            ->join('producto as p', 'p.id', '=', 'ppc.producto_id')
            ->join('categoria_precios as cp', 'cp.id', '=', 'ppc.categoria_precios_id')
            ->join('cliente_categoria_escala as cce', 'cce.id', '=', 'cp.cliente_categoria_escala_id')
            ->leftJoin('marca as m', 'm.id', '=', 'p.marca_id')
            ->where('ppc.estado_id', 1)
            ->where('cp.estado_id', 1)
            ->where('cce.estado_id', 1)
            ->where('p.estado_producto_id', 1)
            ->when($this->filtroTipoCliente, fn($query) => $query->where('cce.id', $this->filtroTipoCliente))
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($subquery) use ($busqueda) {
                    $subquery->where('p.nombre', 'like', "%{$busqueda}%")
                        ->orWhere('p.codigo_barra', 'like', "%{$busqueda}%");
                    if (ctype_digit($busqueda)) {
                        $subquery->orWhere('p.id', (int) $busqueda);
                    }
                });
            })
            ->select([
                'p.id', 'p.codigo_barra', 'p.nombre', 'm.nombre as marca',
                'cce.nombre_categoria as tipo_cliente', 'cp.nombre as escala',
                'ppc.precio_base_venta', 'ppc.precio_a',
            ])
            ->orderBy('p.nombre')
            ->orderBy('cce.nombre_categoria')
            ->orderBy('cp.nombre');
    }

    public function descargarExcel()
    {
        $columnas = $this->columnasExportacion();
        if ($this->tablaTab === 'sin_imagenes') {
            $productos = $this->productosSinImagenQuery()->get();
        } elseif ($this->tablaTab === 'precios') {
            $productos = $this->preciosProductosQuery()->get();
        } else {
            $productos = collect($this->tablaProductos);
        }

        $filas = collect($productos)->map(function ($producto) use ($columnas) {
            $producto = (array) $producto;
            return collect($columnas)->keys()->map(fn($campo) => $producto[$campo] ?? null)->all();
        })->all();

        return (new AnaliticaProductosExport(array_values($columnas), $filas))
            ->download('analitica-productos-'.$this->tablaTab.'-'.now()->format('Y-m-d_His').'.xlsx');
    }

    private function columnasExportacion(): array
    {
        if ($this->tablaTab === 'sin_imagenes') {
            return [
                'id' => 'ID', 'codigo_barra' => 'Código de barra', 'nombre' => 'Producto',
                'categoria' => 'Categoría', 'marca' => 'Marca', 'precio_base' => 'Precio base',
                'estado' => 'Estado',
            ];
        }

        if ($this->tablaTab === 'precios') {
            return [
                'id' => 'ID', 'codigo_barra' => 'Código de barra', 'nombre' => 'Producto',
                'marca' => 'Marca', 'tipo_cliente' => 'Tipo de cliente', 'escala' => 'Escala',
                'precio_base_venta' => 'Precio base', 'precio_a' => 'Precio A',
            ];
        }

        $columnas = [
            'id' => 'ID', 'nombre' => 'Producto', 'categoria' => 'Categoría',
            'total_vendido' => 'Unidades vendidas', 'rotacion_mensual' => 'Rotación mensual',
        ];
        if ($this->tablaTab === 'mayor_crecimiento') {
            $columnas['pct_crecimiento'] = 'Crecimiento %';
        }
        $columnas['ultima_venta'] = 'Última venta';
        $columnas['tiempo_recuperacion_meses'] = 'Recuperación (meses)';

        return $columnas;
    }

    public function render()
    {
        if ($this->tablaTab === 'sin_imagenes') {
            $tablaPaginada = $this->productosSinImagenQuery()->paginate(10);
        } elseif ($this->tablaTab === 'precios') {
            $tablaPaginada = $this->preciosProductosQuery()->paginate(11);
        } else {
            $tablaPaginada = collect($this->tablaProductos);
        }

        return view('livewire.reportes.analiticadeproductos', compact('tablaPaginada'));
    }
}
