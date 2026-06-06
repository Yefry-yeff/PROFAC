<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalisisProductoIndividual extends Component
{
    // ── Parámetro de entrada ───────────────────────────────────────────────────
    public $productoId;

    // ── Datos del producto ────────────────────────────────────────────────────
    public $producto         = null;
    public $stockActual      = 0;
    public $stockPorBodega   = [];
    public $imagenProducto   = null;
    public $ultimaVenta      = null;
    public $ultimaCompra     = null;
    public $proveedorPrincipal = null;

    // ── KPIs ──────────────────────────────────────────────────────────────────
    public $kpis = [];

    // ── Datos de gráficas ─────────────────────────────────────────────────────
    public $tendenciaVentas     = [];   // 12 meses ventas
    public $comprasVsVentas     = [];   // 12 meses compras vs ventas
    public $movimientosStockData = [];  // entradas/salidas mensuales

    // ── Rotación ─────────────────────────────────────────────────────────────
    public $rotacionData = [];

    // ── Movimientos recientes ─────────────────────────────────────────────────
    public $movimientosRecientes  = [];
    public $filtroMovTipo         = '';
    public $filtroMovFechaInicio  = '';
    public $filtroMovFechaFin     = '';

    // ── Análisis predictivo ───────────────────────────────────────────────────
    public $analisisPredictivo = [];

    // ── Alertas ───────────────────────────────────────────────────────────────
    public $alertasProducto = [];

    // ── Comparativos ─────────────────────────────────────────────────────────
    public $comparativos = [];

    // ── Estado general ────────────────────────────────────────────────────────
    public $estadoBadge  = '';
    public $estadoColor  = '';
    public $estadoEmoji  = '';

    public function mount($productoId)
    {
        $this->productoId           = $productoId;
        $this->filtroMovFechaInicio = now()->subDays(90)->format('Y-m-d');
        $this->filtroMovFechaFin    = now()->format('Y-m-d');
        $this->cargarTodo();
    }

    public function updatedFiltroMovTipo()        { $this->cargarMovimientos(); }
    public function updatedFiltroMovFechaInicio() { $this->cargarMovimientos(); }
    public function updatedFiltroMovFechaFin()    { $this->cargarMovimientos(); }

    public function actualizar()
    {
        $this->cargarTodo();
        $this->dispatchBrowserEvent('analisis-actualizado');
    }

    // ── Carga completa ────────────────────────────────────────────────────────
    private function cargarTodo()
    {
        $this->cargarProducto();
        if (empty($this->producto)) return;
        $this->calcularKpis();
        $this->calcularTendencias();
        $this->calcularRotacion();
        $this->cargarMovimientos();
        $this->calcularPredictivo();
        $this->generarAlertas();
        $this->calcularComparativos();
        $this->calcularEstadoBadge();
    }

    // ── 1. Producto + Stock ───────────────────────────────────────────────────
    private function cargarProducto()
    {
        $this->producto = (array) DB::table('producto as p')
            ->join('sub_categoria as sc', 'sc.id', '=', 'p.sub_categoria_id')
            ->join('categoria_producto as cp', 'cp.id', '=', 'sc.categoria_producto_id')
            ->join('marca as m', 'm.id', '=', 'p.marca_id')
            ->leftJoin('estado_producto as ep', 'ep.id', '=', 'p.estado_producto_id')
            ->where('p.id', $this->productoId)
            ->first([
                'p.id', 'p.nombre', 'p.descripcion', 'p.codigo_barra', 'p.codigo_estatal',
                'p.precio_base', 'p.precio1', 'p.precio2', 'p.precio3', 'p.precio4',
                'p.ultimo_costo_compra', 'p.costo_promedio',
                'p.tiempo_recuperacion_meses',
                'p.estado_producto_id',
                'p.created_at as fecha_alta',
                'm.nombre as marca', 'm.id as marca_id',
                'sc.descripcion as sub_categoria',
                'cp.descripcion as categoria', 'cp.id as categoria_id',
                DB::raw("ep.estado as estado_desc"),
            ]) ?? [];

        if (empty($this->producto)) return;

        // Imagen del producto
        $img = DB::table('img_producto')
            ->where('producto_id', $this->productoId)
            ->orderBy('id')
            ->first(['url_img']);
        $this->imagenProducto = $img ? $img->url_img : null;

        // Stock actual (recibido_bodega)
        $this->stockActual = (int) DB::table('recibido_bodega')
            ->where('producto_id', $this->productoId)
            ->sum('cantidad_disponible');

        // Stock por bodega
        $this->stockPorBodega = DB::table('recibido_bodega as rb')
            ->join('seccion as s', 's.id', '=', 'rb.seccion_id')
            ->join('segmento as sg', 'sg.id', '=', 's.segmento_id')
            ->join('bodega as b', 'b.id', '=', 'sg.bodega_id')
            ->where('rb.producto_id', $this->productoId)
            ->where('rb.cantidad_disponible', '>', 0)
            ->selectRaw('b.nombre as bodega, s.descripcion as seccion, SUM(rb.cantidad_disponible) as cantidad')
            ->groupBy('b.id', 'b.nombre', 's.id', 's.descripcion')
            ->orderByDesc('cantidad')
            ->get()->map(fn($r) => (array)$r)->toArray();

        // Última venta
        $uv = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->where('vhp.producto_id', $this->productoId)
            ->orderByDesc('f.created_at')
            ->first(['f.created_at as fecha', 'f.id as factura_id']);
        $this->ultimaVenta = $uv ? (array)$uv : null;

        // Última compra
        $uc = DB::table('compra_has_producto as chp')
            ->join('compra as c', 'c.id', '=', 'chp.compra_id')
            ->where('chp.producto_id', $this->productoId)
            ->orderByDesc('c.created_at')
            ->first(['c.created_at as fecha', 'c.id as compra_id', 'c.proveedores_id']);
        $this->ultimaCompra = $uc ? (array)$uc : null;

        // Proveedor principal (el que más veces ha proveído)
        $prov = DB::table('compra_has_producto as chp')
            ->join('compra as c', 'c.id', '=', 'chp.compra_id')
            ->join('proveedores as pr', 'pr.id', '=', 'c.proveedores_id')
            ->where('chp.producto_id', $this->productoId)
            ->selectRaw('pr.id, pr.nombre, COUNT(*) as total_compras')
            ->groupBy('pr.id', 'pr.nombre')
            ->orderByDesc('total_compras')
            ->first();
        $this->proveedorPrincipal = $prov ? (array)$prov : null;
    }

    // ── 2. KPIs ───────────────────────────────────────────────────────────────
    private function calcularKpis()
    {
        $ahora      = now();
        $hace30     = $ahora->copy()->subDays(30)->format('Y-m-d');
        $hace60     = $ahora->copy()->subDays(60)->format('Y-m-d');
        $hace90     = $ahora->copy()->subDays(90)->format('Y-m-d');
        $horaFin    = $ahora->format('Y-m-d');

        // Ventas últimos 30 días
        $ventas30 = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->where('vhp.producto_id', $this->productoId)
            ->whereBetween('f.created_at', ["$hace30 00:00:00", "$horaFin 23:59:59"])
            ->sum('vhp.cantidad');

        // Ventas 30-60 días atrás (período anterior)
        $ventas30ant = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->where('vhp.producto_id', $this->productoId)
            ->whereBetween('f.created_at', ["$hace60 00:00:00", "$hace30 23:59:59"])
            ->sum('vhp.cantidad');

        // Ventas últimos 90 días
        $ventas90 = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->where('vhp.producto_id', $this->productoId)
            ->whereBetween('f.created_at', ["$hace90 00:00:00", "$horaFin 23:59:59"])
            ->sum('vhp.cantidad');

        // Rotación mensual = unidades vendidas en 30 días
        $rotacionMensual = round($ventas30, 1);

        // Promedio mensual (basado en 90 días)
        $promedioMensual = round($ventas90 / 3, 1);

        // Tendencia % (comparando 30 vs 30 anteriores)
        $tendenciaPct = $ventas30ant > 0
            ? round((($ventas30 - $ventas30ant) / $ventas30ant) * 100, 1)
            : ($ventas30 > 0 ? 100 : 0);

        // Días sin movimiento
        $diasSinMov = 0;
        if ($this->ultimaVenta) {
            $diasSinMov = Carbon::parse($this->ultimaVenta['fecha'])->diffInDays(now());
        }

        // Cobertura estimada (meses)
        $cobertura = $promedioMensual > 0
            ? round($this->stockActual / $promedioMensual, 1)
            : null;

        // Días hasta agotamiento
        $ritmosDiario = $ventas30 / 30;
        $diasAgotamiento = ($ritmosDiario > 0)
            ? round($this->stockActual / $ritmosDiario)
            : null;

        // Valor en inventario
        $valorInventario = $this->stockActual * ($this->producto['costo_promedio'] ?? $this->producto['precio_base'] ?? 0);

        // Total facturas con este producto
        $totalFacturas = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->where('vhp.producto_id', $this->productoId)
            ->where('f.created_at', '>=', "$hace90 00:00:00")
            ->count();

        $this->kpis = [
            'stock_actual'       => $this->stockActual,
            'rotacion_mensual'   => $rotacionMensual,
            'dias_sin_mov'       => $diasSinMov,
            'cobertura'          => $cobertura,
            'promedio_mensual'   => $promedioMensual,
            'tendencia_pct'      => $tendenciaPct,
            'dias_agotamiento'   => $diasAgotamiento,
            'valor_inventario'   => round($valorInventario, 2),
            'total_facturas_90'  => $totalFacturas,
            'ventas_30'          => $ventas30,
            'ventas_30ant'       => $ventas30ant,
        ];
    }

    // ── 3. Tendencias históricas ──────────────────────────────────────────────
    private function calcularTendencias()
    {
        // Ventas mensuales últimos 12 meses
        $this->tendenciaVentas = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->where('vhp.producto_id', $this->productoId)
            ->where('f.created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->selectRaw("DATE_FORMAT(f.created_at,'%Y-%m') as periodo,
                         SUM(vhp.cantidad) as unidades,
                         ROUND(SUM(vhp.total), 2) as monto")
            ->groupByRaw("DATE_FORMAT(f.created_at,'%Y-%m')")
            ->orderByRaw("DATE_FORMAT(f.created_at,'%Y-%m')")
            ->get()->toArray();

        // Compras mensuales últimos 12 meses
        $comprasMensuales = DB::table('compra_has_producto as chp')
            ->join('compra as c', 'c.id', '=', 'chp.compra_id')
            ->where('chp.producto_id', $this->productoId)
            ->where('c.created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->selectRaw("DATE_FORMAT(c.created_at,'%Y-%m') as periodo,
                         SUM(chp.cantidad_ingresada) as unidades,
                         ROUND(SUM(chp.precio_total), 2) as monto")
            ->groupByRaw("DATE_FORMAT(c.created_at,'%Y-%m')")
            ->orderByRaw("DATE_FORMAT(c.created_at,'%Y-%m')")
            ->get()->keyBy('periodo')->toArray();

        // Generar 12 meses completos con ventas + compras
        $this->comprasVsVentas = [];
        for ($i = 11; $i >= 0; $i--) {
            $mes = now()->subMonths($i)->format('Y-m');
            $venta = collect($this->tendenciaVentas)->firstWhere('periodo', $mes);
            $compra = $comprasMensuales[$mes] ?? null;
            $this->comprasVsVentas[] = [
                'periodo'         => $mes,
                'unidades_venta'  => $venta  ? (int)$venta->unidades  : 0,
                'unidades_compra' => $compra ? (int)$compra->unidades : 0,
                'monto_venta'     => $venta  ? (float)$venta->monto   : 0,
                'monto_compra'    => $compra ? (float)$compra->monto  : 0,
            ];
        }

        // Entradas / salidas mensuales (cardex) para stock movement
        $this->movimientosStockData = DB::table('cardex')
            ->where('id_producto', $this->productoId)
            ->where('fecha_creacion', '>=', now()->subMonths(12)->startOfMonth())
            ->selectRaw("DATE_FORMAT(fecha_creacion,'%Y-%m') as periodo,
                         SUM(CASE WHEN descripcion LIKE '%(+)%' THEN cantidad ELSE 0 END) as entradas,
                         SUM(CASE WHEN descripcion LIKE '%(-)%' THEN cantidad ELSE 0 END) as salidas")
            ->groupByRaw("DATE_FORMAT(fecha_creacion,'%Y-%m')")
            ->orderByRaw("DATE_FORMAT(fecha_creacion,'%Y-%m')")
            ->get()->toArray();
    }

    // ── 4. Análisis de rotación ───────────────────────────────────────────────
    private function calcularRotacion()
    {
        $ventas12m = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->where('vhp.producto_id', $this->productoId)
            ->where('f.created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->selectRaw("DATE_FORMAT(f.created_at,'%Y-%m') as mes, SUM(vhp.cantidad) as qty")
            ->groupByRaw("DATE_FORMAT(f.created_at,'%Y-%m')")
            ->orderByRaw("DATE_FORMAT(f.created_at,'%Y-%m')")
            ->get();

        $totalUnidades12m = $ventas12m->sum('qty');
        $mesesConVenta    = $ventas12m->filter(fn($r) => $r->qty > 0)->count();
        $promedioMensual  = $mesesConVenta > 0 ? round($totalUnidades12m / 12, 1) : 0;

        // Frecuencia: número de días entre ventas (promedio)
        $fechasVenta = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->where('vhp.producto_id', $this->productoId)
            ->where('f.created_at', '>=', now()->subDays(90))
            ->selectRaw("DATE(f.created_at) as dia")
            ->groupByRaw("DATE(f.created_at)")
            ->orderBy('dia')
            ->pluck('dia');

        $diasEntreVentas = 0;
        if ($fechasVenta->count() > 1) {
            $diffs = [];
            for ($i = 1; $i < $fechasVenta->count(); $i++) {
                $diffs[] = Carbon::parse($fechasVenta[$i - 1])->diffInDays(Carbon::parse($fechasVenta[$i]));
            }
            $diasEntreVentas = round(array_sum($diffs) / count($diffs), 1);
        }

        // Mes con mayor movimiento
        $mesMax = $ventas12m->sortByDesc('qty')->first();
        $mesMin = $ventas12m->sortBy('qty')->first();

        // Índice de rotación (anualizado): ventas12m / stock_promedio
        $indiceRotacion = $this->stockActual > 0
            ? round($totalUnidades12m / max(1, $this->stockActual), 2)
            : 0;

        // Clasificación
        $clasificacion = match(true) {
            $promedioMensual >= 100  => ['label' => 'Alta rotación',   'color' => '#27ae60', 'score' => 90, 'emoji' => '🔥'],
            $promedioMensual >= 30   => ['label' => 'Rotación media',  'color' => '#2980b9', 'score' => 65, 'emoji' => '📦'],
            $promedioMensual >= 5    => ['label' => 'Baja rotación',   'color' => '#f39c12', 'score' => 35, 'emoji' => '⚠️'],
            default                  => ['label' => 'Producto muerto', 'color' => '#95a5a6', 'score' => 10, 'emoji' => '🛑'],
        };

        $meses = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun',
                  '07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];

        $this->rotacionData = [
            'indice_rotacion'      => $indiceRotacion,
            'promedio_mensual_12m' => $promedioMensual,
            'total_unidades_12m'   => $totalUnidades12m,
            'meses_con_venta'      => $mesesConVenta,
            'dias_entre_ventas'    => $diasEntreVentas,
            'mes_mayor'            => $mesMax ? ($meses[substr($mesMax->mes, 5)] ?? substr($mesMax->mes, 5)).' '.substr($mesMax->mes, 0, 4).' ('.(int)$mesMax->qty.' uds)' : '—',
            'mes_menor'            => $mesMin ? ($meses[substr($mesMin->mes, 5)] ?? substr($mesMin->mes, 5)).' '.substr($mesMin->mes, 0, 4).' ('.(int)$mesMin->qty.' uds)' : '—',
            'clasificacion'        => $clasificacion,
        ];
    }

    // ── 5. Movimientos recientes ──────────────────────────────────────────────
    public function cargarMovimientos()
    {
        $fi = $this->filtroMovFechaInicio ?: now()->subDays(90)->format('Y-m-d');
        $ff = $this->filtroMovFechaFin    ?: now()->format('Y-m-d');

        $q = DB::table('cardex')
            ->where('id_producto', $this->productoId)
            ->whereBetween('fecha_creacion', ["$fi 00:00:00", "$ff 23:59:59"]);

        if ($this->filtroMovTipo) {
            switch ($this->filtroMovTipo) {
                case 'venta':      $q->whereNotNull('id_factura')->where('descripcion', 'NOT LIKE', '%Anulada%'); break;
                case 'compra':     $q->whereNotNull('detalleCompra'); break;
                case 'ajuste':     $q->whereNotNull('ajuste'); break;
                case 'devolucion': $q->whereNotNull('id_factura')->where('descripcion', 'LIKE', '%Anulada%'); break;
                case 'credito':    $q->whereNotNull('nota_credito'); break;
                case 'traslado':   $q->whereNotNull('comprobante'); break;
            }
        }

        $movimientos = $q->orderByDesc('fecha_creacion')
            ->limit(50)
            ->get([
                'id', 'fecha_creacion', 'descripcion', 'cantidad',
                'id_factura', 'numero_factura',
                'detalleCompra',
                'ajuste', 'ajuste_cod',
                'comprobante', 'numero_comprobante',
                'nota_credito', 'numero_nota',
                'Bodega_origen_nombre', 'Bodega_destino_nombre',
                'usuario',
            ]);

        // Calcular stock acumulado (orden inverso)
        $movArr  = $movimientos->toArray();
        $stockAc = $this->stockActual;
        $result  = [];

        foreach ($movArr as $mov) {
            $esEntrada  = str_contains($mov->descripcion ?? '', '(+)');
            $tipo       = $this->detectarTipoMovimiento($mov);
            $entrada    = $esEntrada  ? $mov->cantidad : 0;
            $salida     = !$esEntrada ? $mov->cantidad : 0;
            $documento  = $this->obtenerDocumento($mov);

            $result[] = [
                'fecha'     => $mov->fecha_creacion,
                'tipo'      => $tipo['label'],
                'color'     => $tipo['color'],
                'icono'     => $tipo['icon'],
                'documento' => $documento,
                'entrada'   => $entrada,
                'salida'    => $salida,
                'stock'     => $stockAc,
                'usuario'   => $mov->usuario ?? '—',
                'descripcion' => $mov->descripcion,
                'bodega_origen'  => $mov->Bodega_origen_nombre ?? '',
                'bodega_destino' => $mov->Bodega_destino_nombre ?? '',
            ];

            // Reconstruir stock hacia atrás
            if ($esEntrada) {
                $stockAc = max(0, $stockAc - $mov->cantidad);
            } else {
                $stockAc = $stockAc + $mov->cantidad;
            }
        }

        $this->movimientosRecientes = $result;
    }

    private function detectarTipoMovimiento($mov): array
    {
        $desc = $mov->descripcion ?? '';
        if ($mov->id_factura && str_contains($desc, 'Anulada')) {
            return ['label' => 'Devolución', 'color' => '#8e44ad', 'icon' => 'fa-undo'];
        }
        if ($mov->nota_credito) {
            return ['label' => 'Nota Crédito', 'color' => '#8e44ad', 'icon' => 'fa-undo'];
        }
        if ($mov->id_factura) {
            return ['label' => 'Venta', 'color' => '#e74c3c', 'icon' => 'fa-shopping-cart'];
        }
        if ($mov->detalleCompra) {
            return ['label' => 'Compra', 'color' => '#27ae60', 'icon' => 'fa-truck'];
        }
        if ($mov->ajuste) {
            $esEntrada = str_contains($desc, '(+)');
            return ['label' => 'Ajuste '.($esEntrada ? '+' : '-'), 'color' => '#f39c12', 'icon' => 'fa-sliders'];
        }
        if ($mov->comprobante) {
            return ['label' => 'Traslado', 'color' => '#2980b9', 'icon' => 'fa-exchange'];
        }
        return ['label' => 'Movimiento', 'color' => '#95a5a6', 'icon' => 'fa-circle'];
    }

    private function obtenerDocumento($mov): string
    {
        if ($mov->numero_factura)      return 'FAC-'.$mov->numero_factura;
        if ($mov->numero_nota)         return 'NC-'.$mov->numero_nota;
        if ($mov->numero_comprobante)  return 'COMP-'.$mov->numero_comprobante;
        if ($mov->ajuste_cod)          return 'AJ-'.$mov->ajuste_cod;
        return '#'.$mov->id;
    }

    // ── 6. Análisis predictivo ────────────────────────────────────────────────
    private function calcularPredictivo()
    {
        $ritmosDiario    = ($this->kpis['ventas_30'] ?? 0) / 30;
        $promedioMensual = $this->kpis['promedio_mensual'] ?? 0;
        $stock           = $this->stockActual;
        $tiempoRec       = $this->producto['tiempo_recuperacion_meses'] ?? 1;

        // Días hasta agotamiento
        $diasAgotamiento = $ritmosDiario > 0 ? round($stock / $ritmosDiario) : null;

        // Cantidad recomendada a comprar (para cubrir X meses según tiempo recuperación)
        $stockRecomendado     = $promedioMensual * max(1, $tiempoRec + 1);
        $cantidadRecomprar    = max(0, round($stockRecomendado - $stock));

        // Riesgo sobreinventario
        $mesesCubiertos = $promedioMensual > 0 ? round($stock / $promedioMensual, 1) : null;
        $sobreinventario = $mesesCubiertos !== null && $mesesCubiertos > 6;

        // Proyección próximo mes (basada en tendencia)
        $tendPct    = $this->kpis['tendencia_pct'] ?? 0;
        $proyeccion = round($promedioMensual * (1 + ($tendPct / 100)));

        // Evaluación de riesgo
        $riesgo = match(true) {
            $diasAgotamiento !== null && $diasAgotamiento <= 7  => ['nivel' => 'crítico',   'color' => '#e74c3c', 'texto' => 'Stock crítico — compra urgente'],
            $diasAgotamiento !== null && $diasAgotamiento <= 15 => ['nivel' => 'alto',      'color' => '#e67e22', 'texto' => 'Stock bajo — programar compra pronto'],
            $diasAgotamiento !== null && $diasAgotamiento <= 30 => ['nivel' => 'medio',     'color' => '#f39c12', 'texto' => 'Stock suficiente por menos de 30 días'],
            $sobreinventario                                    => ['nivel' => 'sobrestock','color' => '#2980b9', 'texto' => 'Sobreinventario detectado'],
            default                                             => ['nivel' => 'normal',    'color' => '#27ae60', 'texto' => 'Stock en niveles normales'],
        };

        $this->analisisPredictivo = [
            'dias_agotamiento'  => $diasAgotamiento,
            'cantidad_comprar'  => $cantidadRecomprar,
            'meses_cubiertos'   => $mesesCubiertos,
            'sobreinventario'   => $sobreinventario,
            'proyeccion_mes'    => $proyeccion,
            'tendencia_pct'     => $tendPct,
            'ritmo_diario'      => round($ritmosDiario, 1),
            'riesgo'            => $riesgo,
        ];
    }

    // ── 7. Alertas inteligentes ───────────────────────────────────────────────
    private function generarAlertas()
    {
        $alertas = [];
        $stock   = $this->stockActual;
        $kpis    = $this->kpis;
        $pred    = $this->analisisPredictivo;

        // Stock crítico
        if (isset($pred['dias_agotamiento']) && $pred['dias_agotamiento'] !== null && $pred['dias_agotamiento'] <= 7) {
            $alertas[] = [
                'prioridad' => 'crítica',
                'color'     => '#e74c3c',
                'icono'     => 'fa-exclamation-triangle',
                'titulo'    => 'Stock crítico',
                'texto'     => "Quedan {$stock} unidades. Se agotará en {$pred['dias_agotamiento']} días.",
                'accion'    => 'Realizar compra de emergencia',
            ];
        }

        // Sin movimiento
        if (($kpis['dias_sin_mov'] ?? 0) > 30 && $stock > 0) {
            $diasSin = $kpis['dias_sin_mov'];
            $alertas[] = [
                'prioridad' => 'alta',
                'color'     => '#e67e22',
                'icono'     => 'fa-pause-circle',
                'titulo'    => 'Sin movimiento',
                'texto'     => "Lleva {$diasSin} días sin ventas con {$stock} unidades en bodega.",
                'accion'    => 'Revisar precio y disponibilidad en tienda',
            ];
        }

        // Caída brusca de ventas
        if (($kpis['tendencia_pct'] ?? 0) <= -35 && ($kpis['ventas_30ant'] ?? 0) > 0) {
            $caida = abs($kpis['tendencia_pct']);
            $alertas[] = [
                'prioridad' => 'alta',
                'color'     => '#c0392b',
                'icono'     => 'fa-arrow-down',
                'titulo'    => 'Caída brusca de ventas',
                'texto'     => "Las ventas cayeron {$caida}% respecto al período anterior.",
                'accion'    => 'Analizar causa: precio, competencia o disponibilidad',
            ];
        }

        // Sobreinventario
        if (!empty($pred['sobreinventario'])) {
            $meses = $pred['meses_cubiertos'];
            $alertas[] = [
                'prioridad' => 'media',
                'color'     => '#2980b9',
                'icono'     => 'fa-archive',
                'titulo'    => 'Sobreinventario',
                'texto'     => "Hay {$meses} meses de cobertura acumulada. Capital inmovilizado.",
                'accion'    => 'Evaluar promoción o transferencia a otra sucursal',
            ];
        }

        // Crecimiento acelerado
        if (($kpis['tendencia_pct'] ?? 0) >= 50) {
            $crec = $kpis['tendencia_pct'];
            $alertas[] = [
                'prioridad' => 'info',
                'color'     => '#27ae60',
                'icono'     => 'fa-rocket',
                'titulo'    => 'Crecimiento acelerado',
                'texto'     => "Las ventas crecieron +{$crec}%. Considerar reabastecimiento anticipado.",
                'accion'    => 'Programar compra preventiva',
            ];
        }

        // Ajustes frecuentes (posibles inconsistencias)
        $totalAjustes = DB::table('cardex')
            ->where('id_producto', $this->productoId)
            ->whereNotNull('ajuste')
            ->where('fecha_creacion', '>=', now()->subDays(30))
            ->count();

        if ($totalAjustes >= 3) {
            $alertas[] = [
                'prioridad' => 'media',
                'color'     => '#8e44ad',
                'icono'     => 'fa-exclamation-circle',
                'titulo'    => 'Ajustes frecuentes',
                'texto'     => "Se registraron {$totalAjustes} ajustes en los últimos 30 días.",
                'accion'    => 'Auditar inventario físico y verificar conteos',
            ];
        }

        // Sin stock
        if ($stock <= 0) {
            $alertas[] = [
                'prioridad' => 'crítica',
                'color'     => '#e74c3c',
                'icono'     => 'fa-ban',
                'titulo'    => 'Sin stock disponible',
                'texto'     => 'El producto no tiene unidades disponibles en bodega.',
                'accion'    => 'Realizar compra o transferencia inmediata',
            ];
        }

        $this->alertasProducto = array_slice($alertas, 0, 6);
    }

    // ── 8. Comparativos históricos ────────────────────────────────────────────
    private function calcularComparativos()
    {
        $anioActual  = now()->year;
        $anioAnterior = $anioActual - 1;

        // Ventas anuales: año actual vs anterior
        $ventasAnioActual = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->where('vhp.producto_id', $this->productoId)
            ->whereYear('f.created_at', $anioActual)
            ->sum('vhp.cantidad');

        $ventasAnioAnt = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->where('vhp.producto_id', $this->productoId)
            ->whereYear('f.created_at', $anioAnterior)
            ->sum('vhp.cantidad');

        $pctAnual = $ventasAnioAnt > 0
            ? round((($ventasAnioActual - $ventasAnioAnt) / $ventasAnioAnt) * 100, 1) : null;

        // Mes actual vs mes anterior
        $mesActual   = now()->month;
        $anioMesAnt  = $mesActual == 1 ? $anioActual - 1 : $anioActual;
        $mesAnterior = $mesActual == 1 ? 12 : $mesActual - 1;

        $ventasMesActual = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->where('vhp.producto_id', $this->productoId)
            ->whereYear('f.created_at', $anioActual)
            ->whereMonth('f.created_at', $mesActual)
            ->sum('vhp.cantidad');

        $ventasMesAnt = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->where('vhp.producto_id', $this->productoId)
            ->whereYear('f.created_at', $anioMesAnt)
            ->whereMonth('f.created_at', $mesAnterior)
            ->sum('vhp.cantidad');

        $pctMensual = $ventasMesAnt > 0
            ? round((($ventasMesActual - $ventasMesAnt) / $ventasMesAnt) * 100, 1) : null;

        // Estacionalidad: promedio por mes (últimos 24 meses) usando raw SQL
        $pid = (int) $this->productoId;
        $desde = now()->subMonths(24)->startOfMonth()->format('Y-m-d H:i:s');
        $estacional = DB::select("
            SELECT sub.mes_num, MIN(sub.mes_nombre) as mes_nombre, AVG(sub.qty_mes) as promedio
            FROM (
                SELECT MONTH(f2.created_at) as mes_num,
                       DATE_FORMAT(f2.created_at,'%M') as mes_nombre,
                       YEAR(f2.created_at) as anio,
                       SUM(vhp2.cantidad) as qty_mes
                FROM venta_has_producto vhp2
                INNER JOIN factura f2 ON f2.id = vhp2.factura_id
                WHERE vhp2.producto_id = {$pid}
                  AND f2.created_at >= '{$desde}'
                GROUP BY MONTH(f2.created_at), YEAR(f2.created_at), DATE_FORMAT(f2.created_at,'%M')
            ) sub
            GROUP BY sub.mes_num
            ORDER BY sub.mes_num
        ");

        // Ventas por mes de ambos años (para gráfica comparativa)
        $porMesAnioActual = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->where('vhp.producto_id', $this->productoId)
            ->whereYear('f.created_at', $anioActual)
            ->selectRaw("MONTH(f.created_at) as mes, SUM(vhp.cantidad) as qty")
            ->groupByRaw("MONTH(f.created_at)")
            ->pluck('qty', 'mes')->toArray();

        $porMesAnioAnt = DB::table('venta_has_producto as vhp')
            ->join('factura as f', 'f.id', '=', 'vhp.factura_id')
            ->where('vhp.producto_id', $this->productoId)
            ->whereYear('f.created_at', $anioAnterior)
            ->selectRaw("MONTH(f.created_at) as mes, SUM(vhp.cantidad) as qty")
            ->groupByRaw("MONTH(f.created_at)")
            ->pluck('qty', 'mes')->toArray();

        $comparativoAnual = [];
        for ($m = 1; $m <= 12; $m++) {
            $comparativoAnual[] = [
                'mes'          => $m,
                'anio_actual'  => (int)($porMesAnioActual[$m] ?? 0),
                'anio_ant'     => (int)($porMesAnioAnt[$m] ?? 0),
            ];
        }

        $this->comparativos = [
            'anio_actual'          => $anioActual,
            'anio_anterior'        => $anioAnterior,
            'ventas_anio_actual'   => $ventasAnioActual,
            'ventas_anio_ant'      => $ventasAnioAnt,
            'pct_anual'            => $pctAnual,
            'ventas_mes_actual'    => $ventasMesActual,
            'ventas_mes_ant'       => $ventasMesAnt,
            'pct_mensual'          => $pctMensual,
            'mes_actual'           => $mesActual,
            'mes_anterior'         => $mesAnterior,
            'comparativo_anual'    => $comparativoAnual,
            'estacional'           => $estacional,
        ];
    }

    // ── Badge de estado ───────────────────────────────────────────────────────
    private function calcularEstadoBadge()
    {
        $rotacion = $this->kpis['rotacion_mensual'] ?? 0;
        $diasSin  = $this->kpis['dias_sin_mov'] ?? 0;
        $tendPct  = $this->kpis['tendencia_pct'] ?? 0;
        $stock    = $this->stockActual;

        if ($stock <= 0) {
            $this->estadoBadge = 'Sin stock'; $this->estadoColor = '#e74c3c'; $this->estadoEmoji = '🛑';
        } elseif ($diasSin > 60) {
            $this->estadoBadge = 'Estancado';  $this->estadoColor = '#95a5a6'; $this->estadoEmoji = '🛑';
        } elseif ($tendPct <= -35) {
            $this->estadoBadge = 'En caída';   $this->estadoColor = '#e74c3c'; $this->estadoEmoji = '📉';
        } elseif ($tendPct >= 30) {
            $this->estadoBadge = 'Crecimiento'; $this->estadoColor = '#27ae60'; $this->estadoEmoji = '📈';
        } elseif ($rotacion >= 50) {
            $this->estadoBadge = 'Alta rotación'; $this->estadoColor = '#e67e22'; $this->estadoEmoji = '🔥';
        } elseif (!empty($this->analisisPredictivo['dias_agotamiento']) && $this->analisisPredictivo['dias_agotamiento'] <= 15) {
            $this->estadoBadge = 'Riesgo';  $this->estadoColor = '#f39c12'; $this->estadoEmoji = '⚠️';
        } else {
            $this->estadoBadge = 'Normal'; $this->estadoColor = '#2980b9'; $this->estadoEmoji = '📦';
        }
    }

    public function render()
    {
        return view('livewire.reportes.analisis-producto-individual');
    }
}
