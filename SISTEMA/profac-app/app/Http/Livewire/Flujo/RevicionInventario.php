<?php

namespace App\Http\Livewire\Flujo;

use App\Support\ExpoStock;
use Livewire\Component;
use App\Events\FlujoAvanzadoEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Bandeja de Revisión de Inventario.
 *
 * Gestiona el paso intermedio entre "Oferta Ganadora" y "Prefactura":
 *  – Lista los flujos en estado Revision de Inventario (tipo_tramite_id = 9)
 *  – Permite revisar productos (solo nombre y cantidad, sin precios)
 *  – Acción A: Pasar a Prefactura (si hay stock suficiente)
 *  – Acción B: Devolver a Oferta (con observaciones obligatorias)
 *  – Panel de configuración para activar/desactivar este paso
 */
class RevicionInventario extends Component
{
    private function diasVigenciaPrefactura(int $flujoId): int
    {
        $credito = DB::table('credito_revision')
            ->where('flujo_id', $flujoId)
            ->where('estado', 'aprobado')
            ->latest('id')
            ->first(['dias_credito_aprobados', 'fecha_aprobacion', 'fecha_vencimiento_credito']);

        if ($credito && !is_null($credito->dias_credito_aprobados)) {
            return max(0, (int) $credito->dias_credito_aprobados);
        }

        if ($credito && $credito->fecha_aprobacion && $credito->fecha_vencimiento_credito) {
            return max(0, (int) \Carbon\Carbon::parse($credito->fecha_aprobacion)
                ->diffInDays(\Carbon\Carbon::parse($credito->fecha_vencimiento_credito), false));
        }

        return max(0, (int) (DB::table('configuracion_prefactura')
            ->orderByDesc('id')->value('dias_validez') ?? 7));
    }

    // ── Bandeja ───────────────────────────────────────────────────────────
    public array  $bandejaRegistros  = [];   // pestaña: llegando
    public array  $bandejaDevueltos  = [];   // pestaña: devueltos a oferta
    public array  $bandejaPrefactura = [];   // pestaña: pasados a prefactura
    public string $busqueda          = '';
    public string $tabActiva         = 'llegando';

    // ── Detalle del flujo seleccionado ────────────────────────────────────
    public ?int   $flujoId          = null;
    protected     $flujoData        = null;   // info del flujo + oferta ganadora
    public ?int   $cotizacionId     = null;   // ID de la cotizacion ganadora
    public array  $productos        = [];     // {nombre_producto, cantidad, disponible, falta_stock}
    public array  $stockErrors      = [];     // productos con stock insuficiente
    public array  $productosRevisados = [];   // checkbox por producto
    public bool   $esOfertaExpo     = false;
    public array  $bodegaExpoSeleccionada = [];
    public string $filtroProducto   = '';
    public string $filtroBodega     = '';
    public string $filtroEstado     = '';
    public string $filtroRevisado   = '';

    // ── Observaciones por producto (para notas de reemplazo) ──────────────
    public array  $obsProducto      = [];     // ['idx' => 'texto obs']

    // ── Estado de devolución ──────────────────────────────────────────────
    public bool   $devuelto                  = false;  // true si el flujo fue devuelto a Oferta
    public string $motivoDevolucionGuardado  = '';     // motivo leído del historico al abrir un devuelto
    public bool   $soloVisualizacion         = false;  // true cuando se abre desde pestaña Prefactura

    // ── Confirmación de acciones ──────────────────────────────────────────
    public ?string $confirmAccion    = null;  // null | 'prefactura' | 'devolver'
    public string  $motivoDevolucion = '';

    // ── Configuración ──────────────────────────────────────────────────────
    public bool $configuracionActiva = false;

    // ── Paginación ────────────────────────────────────────────────────────
    public int $paginaLlegando   = 1;
    public int $paginaDevueltos  = 1;
    public int $paginaPrefactura = 1;
    public int $porPagina        = 10;
    public int $totalLlegando    = 0;
    public int $totalDevueltos   = 0;
    public int $totalPrefactura  = 0;

    // ── Modal de reservas por producto ─────────────────────────────────────
    public bool   $modalReservasVisible = false;
    public array  $modalReservasData    = [];
    public string $modalReservaNombre   = '';

    // ── Modal de edición de productos sin existencia ───────────────────────
    public bool   $modalSinExistenciaVisible = false;
    public array  $productosSinExistenciaModal = [];
    public string $motivoEdicionSinExistencia = '';

    // ── Mensajes ──────────────────────────────────────────────────────────
    public string $mensajeExito = '';
    public string $mensajeError = '';

    // ─────────────────────────────────────────────────────────────────────
    // LIFECYCLE
    // ─────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->cargarConfiguracion();
        $this->cargar();

        $flujoId = request()->integer('flujo_id');
        if ($flujoId > 0) {
            $this->seleccionarFlujo($flujoId);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // CONFIGURACIÓN
    // ─────────────────────────────────────────────────────────────────────

    public function cargarConfiguracion(): void
    {
        $config = DB::table('configuracion_revision_inventario')->first();
        $this->configuracionActiva = $config ? (bool) $config->activo : false;
    }

    public function toggleConfiguracion(): void
    {
        $nuevo = $this->configuracionActiva ? 0 : 1;

        DB::table('configuracion_revision_inventario')
            ->where('id', 1)
            ->update([
                'activo'     => $nuevo,
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

        $this->configuracionActiva = (bool) $nuevo;
        $this->mensajeExito = $nuevo
            ? 'Revisión de inventario ACTIVADA. El flujo ahora pasará por esta etapa antes de Prefactura.'
            : 'Revisión de inventario DESACTIVADA. Las ofertas ganadoras pasarán directamente a Prefactura.';
        $this->mensajeError = '';
    }

    // ─────────────────────────────────────────────────────────────────────
    // BANDEJA
    // ─────────────────────────────────────────────────────────────────────

    public function updatedBusqueda(): void
    {
        $this->paginaLlegando = $this->paginaDevueltos = $this->paginaPrefactura = 1;
        $this->cargar();
    }

    public function cambiarPagina(string $tab, int $pagina): void
    {
        if ($tab === 'llegando')   $this->paginaLlegando   = max(1, $pagina);
        if ($tab === 'devueltos')  $this->paginaDevueltos  = max(1, $pagina);
        if ($tab === 'prefactura') $this->paginaPrefactura = max(1, $pagina);
        $this->cargar();
    }

    public function cargar(): void
    {
        $term = trim($this->busqueda);
        $this->totalLlegando   = $this->buildBandejaCount($term, 'llegando');
        $this->totalDevueltos  = $this->buildBandejaCount($term, 'devueltos');
        $this->totalPrefactura = $this->buildBandejaCount($term, 'prefactura');
        $this->bandejaRegistros  = $this->buildBandejaQuery($term, 'llegando',   $this->paginaLlegando);
        $this->bandejaDevueltos  = $this->buildBandejaQuery($term, 'devueltos',  $this->paginaDevueltos);
        $this->bandejaPrefactura = $this->buildBandejaQuery($term, 'prefactura', $this->paginaPrefactura);
    }

    public function cambiarTab(string $tab): void
    {
        $this->tabActiva = in_array($tab, ['llegando', 'devueltos', 'prefactura']) ? $tab : 'llegando';
    }

    private function buildBandejaCount(string $term, string $tipo): int
    {
        $latestRevSub = DB::table('historico_flujo')
            ->select('flujo_id', DB::raw('MAX(id) as max_id'))
            ->where('tipo_tramite_id', 9)
            ->groupBy('flujo_id');

        $q = DB::table('flujo as f')
            ->joinSub($latestRevSub, 'lrev', function ($j) { $j->on('lrev.flujo_id', '=', 'f.id'); })
            ->join('historico_flujo as hf', 'hf.id', '=', 'lrev.max_id')
            ->leftJoin('historico_flujo as hfof', function ($j) {
                $j->on('hfof.flujo_id', '=', 'f.id')
                  ->where('hfof.tipo_tramite_id', 2)
                  ->where('hfof.observaciones', 'ganadora');
            })
            ->leftJoin('cotizacion as c', 'c.id', '=', 'hfof.tramite_id')
            ->leftJoin('pedido as p', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'p.id')
            ->leftJoin('cliente as cl', function ($j) {
                $j->on('cl.id', '=', 'c.cliente_id')->orOn('cl.id', '=', 'p.cliente_id');
            });

        if ($tipo === 'llegando') {
            $q->where('f.tipo_tramite_id', 9)->where('hf.estado_id', '!=', 7);
        } elseif ($tipo === 'devueltos') {
            $q->where('hf.estado_id', 7);
        } else {
            $q->where('hf.estado_id', 1);
        }

        if ($term !== '') {
            $like = '%' . $term . '%';
            if (is_numeric($term)) {
                $q->where(function ($s) use ($term) {
                    $s->where('f.id', (int) $term)->orWhere('f.identificacion', $term)->orWhere('hfof.tramite_id', (int) $term);
                });
            } else {
                $q->where(function ($s) use ($like) {
                    $s->where('c.nombre_cliente', 'LIKE', $like)->orWhere('c.RTN', 'LIKE', $like)->orWhere('p.observaciones', 'LIKE', $like);
                });
            }
        }

        return (int) $q->count(DB::raw('DISTINCT f.id'));
    }

    private function buildBandejaQuery(string $term, string $tipo, int $page = 1): array
    {
        // Subquery: obtiene solo el registro MÁS RECIENTE de revisión (tipo=9) por flujo.
        // Esto evita que flujos con múltiples ciclos aparezcan duplicados en bandeja.
        $latestRevSub = DB::table('historico_flujo')
            ->select('flujo_id', DB::raw('MAX(id) as max_id'))
            ->where('tipo_tramite_id', 9)
            ->groupBy('flujo_id');

        $q = DB::table('flujo as f')
            ->joinSub($latestRevSub, 'lrev', function ($j) {
                $j->on('lrev.flujo_id', '=', 'f.id');
            })
            ->join('historico_flujo as hf', 'hf.id', '=', 'lrev.max_id')
            ->leftJoin('historico_flujo as hfof', function ($j) {
                $j->on('hfof.flujo_id', '=', 'f.id')
                  ->where('hfof.tipo_tramite_id', 2)
                  ->where('hfof.observaciones', 'ganadora');
            })
            ->leftJoin('cotizacion as c', 'c.id', '=', 'hfof.tramite_id')
            ->leftJoin('pedido as p', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'p.id')
            ->leftJoin('cliente as cl', function ($j) {
                $j->on('cl.id', '=', 'c.cliente_id')
                  ->orOn('cl.id', '=', 'p.cliente_id');
            })
            ->select(
                'f.id as flujo_id',
                'f.identificacion',
                'hf.created_at as fecha_revision',
                'hf.updated_at as fecha_accion',
                'hfof.tramite_id as cotizacion_id',
                DB::raw("COALESCE(c.nombre_cliente, p.observaciones, CONCAT('Flujo #', f.id)) as cliente"),
                DB::raw("COALESCE(c.RTN, '') as rtn"),
                DB::raw('(SELECT COUNT(*) FROM cotizacion_has_producto chp WHERE chp.cotizacion_id = hfof.tramite_id) as total_productos'),
                'hf.observaciones as obs_revision',
                'hf.estado_id'
            )
            ->groupBy(
                'f.id', 'f.identificacion', 'hf.created_at', 'hf.updated_at',
                'hfof.tramite_id', 'c.nombre_cliente', 'p.observaciones',
                'c.RTN', 'hf.observaciones', 'hf.estado_id'
            );

        if ($tipo === 'llegando') {
            // Ciclo activo: el registro más reciente de tipo=9 no está devuelto ni aprobado
            $q->where('f.tipo_tramite_id', 9)->where('hf.estado_id', '!=', 7);
        } elseif ($tipo === 'devueltos') {
            // Solo el último ciclo de revisión fue devuelto (estado_id=7)
            $q->where('hf.estado_id', 7);
        } else {
            $q->where('hf.estado_id', 1); // prefactura: revisión aprobada
        }

        if ($term !== '') {
            $like = '%' . $term . '%';
            if (is_numeric($term)) {
                $q->where(function ($s) use ($term) {
                    $s->where('f.id', (int) $term)
                      ->orWhere('f.identificacion', $term)
                      ->orWhere('hfof.tramite_id', (int) $term);
                });
            } else {
                $q->where(function ($s) use ($like) {
                    $s->where('c.nombre_cliente', 'LIKE', $like)
                      ->orWhere('c.RTN', 'LIKE', $like)
                      ->orWhere('p.observaciones', 'LIKE', $like);
                });
            }
        }

        $offset = ($page - 1) * $this->porPagina;
        return $q->orderByDesc('hf.created_at')->offset($offset)->limit($this->porPagina)->get()->map(fn($r) => (array) $r)->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────
    // DETALLE DE FLUJO
    // ─────────────────────────────────────────────────────────────────────

    public function seleccionarFlujo(int $flujoId, bool $soloVisualizacion = false): void
    {
        $this->flujoId          = $flujoId;
        $this->soloVisualizacion = $soloVisualizacion;
        $this->devuelto         = false;
        $this->motivoDevolucionGuardado = '';
        $this->confirmAccion    = null;
        $this->motivoDevolucion = '';
        $this->mensajeExito     = '';
        $this->mensajeError     = '';
        $this->obsProducto      = [];
        $this->stockErrors      = [];
        $this->productosRevisados = [];
        $this->esOfertaExpo     = false;
        $this->bodegaExpoSeleccionada = [];

        // Detectar el estado del ciclo ACTUAL mirando el registro MÁS RECIENTE de tipo=9.
        // Si el último registro tiene estado_id=7 → ciclo cerrado/devuelto (modo lectura).
        // Si no → ciclo activo (puede ser un segundo o posterior ciclo).
        $latestRevRec = DB::table('historico_flujo')
            ->where('flujo_id', $flujoId)
            ->where('tipo_tramite_id', 9)
            ->orderByDesc('id')
            ->first(['id', 'estado_id', 'observaciones']);

        $revRec = null;
        if ($latestRevRec && (int) $latestRevRec->estado_id === 7) {
            $this->devuelto = true;
            $revRec = $latestRevRec;
        }

        // Obtener info del flujo
        $flujoResult = DB::table('flujo as f')
            ->leftJoin('pedido as p', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'p.id')
            ->leftJoin('cliente as cl', 'cl.id', '=', 'p.cliente_id')
            ->where('f.id', $flujoId)
            ->select(
                'f.id as flujo_id',
                'f.identificacion',
                'p.id as pedido_id',
                DB::raw("COALESCE(cl.nombre, 'N/A') as cliente"),
                'p.created_at as pedido_fecha',
                'p.observaciones as pedido_obs'
            )
            ->first();
        $this->flujoData = $flujoResult ? (array) $flujoResult : null;

        // Obtener la cotizacion ganadora de este flujo
        $hfGanadora = DB::table('historico_flujo')
            ->where('flujo_id', $flujoId)
            ->where('tipo_tramite_id', 2)
            ->where('observaciones', 'ganadora')
            ->orderByDesc('id')
            ->first(['tramite_id', 'observaciones']);

        // Si fue devuelto, buscar el cotizacion_id a través de cotizacion_estado (ganadora=4)
        if (!$hfGanadora) {
            $ceDev = DB::table('cotizacion_estado')
                ->where('flujo_id', $flujoId)
                ->where('ganadora', 4)
                ->orderByDesc('id')
                ->first(['cotizacion_id']);
            if ($ceDev) {
                $hfGanadora = (object) ['tramite_id' => $ceDev->cotizacion_id];
            }
        }
        // Último respaldo: oferta marcada como devuelta desde revisión
        if (!$hfGanadora) {
            $hfGanadora = DB::table('historico_flujo')
                ->where('flujo_id', $flujoId)
                ->where('tipo_tramite_id', 2)
                ->where('observaciones', 'LIKE', 'Devuelta desde Revisión:%')
                ->orderByDesc('id')
                ->first(['tramite_id']);
        }

        $this->cotizacionId = $hfGanadora ? (int) $hfGanadora->tramite_id : null;

        if (!$this->cotizacionId) {
            $this->mensajeError = 'No se encontró la oferta ganadora de este flujo.';
            $this->productos    = [];
            return;
        }

        $expoId = (int) (DB::table('expo_cotizacion')
            ->where('cotizacion_id', $this->cotizacionId)
            ->value('expo_id') ?? 0);
        $this->esOfertaExpo = $expoId > 0;
        $bodegasExpo = $this->esOfertaExpo
            ? DB::table('expo_bodega')->where('expo_id', $expoId)->pluck('bodega_id')->map(fn ($id) => (int) $id)->all()
            : [];

        $cotizacionInfo = DB::table('cotizacion as c')
            ->leftJoin('cliente as cl', 'cl.id', '=', 'c.cliente_id')
            ->leftJoin('users as v', 'v.id', '=', 'c.vendedor')
            ->where('c.id', $this->cotizacionId)
            ->select(
                'c.cliente_id',
                'c.vendedor',
                DB::raw('COALESCE(cl.nombre, c.nombre_cliente, "N/A") as cliente_nombre'),
                DB::raw('COALESCE(v.name, "N/A") as vendedor_nombre')
            )
            ->first();

        if ($cotizacionInfo) {
            $this->flujoData['cliente_id'] = $cotizacionInfo->cliente_id;
            $this->flujoData['cliente'] = $cotizacionInfo->cliente_nombre;
            $this->flujoData['vendedor'] = $cotizacionInfo->vendedor;
            $this->flujoData['vendedor_nombre'] = $cotizacionInfo->vendedor_nombre;
        }

        // Obtener productos (solo nombre + cantidad + stock actual)
        $prods = DB::table('cotizacion_has_producto as chp')
            ->leftJoin('seccion as s', 's.id', '=', 'chp.seccion_id')
            ->leftJoin('segmento as sg', 'sg.id', '=', 's.segmento_id')
            ->leftJoin('bodega as b', 'b.id', '=', 'sg.bodega_id')
            ->leftJoin('unidad_medida_venta as umv', 'umv.id', '=', 'chp.unidad_medida_venta_id')
            ->leftJoin('unidad_medida as um', 'um.id', '=', 'umv.unidad_medida_id')
            ->where('chp.cotizacion_id', $this->cotizacionId)
            ->select(
                'chp.indice',
                'chp.nombre_producto',
                'chp.nombre_bodega',
                'chp.cantidad',
                'chp.producto_id',
                'chp.seccion_id',
                'chp.resta_inventario',
                'chp.unidad_medida_venta_id',
                'sg.bodega_id',
                'b.nombre as bodega_actual_nombre',
                's.descripcion as seccion_actual_descripcion',
                'um.nombre as unidad_medida'
            )
            ->get();

        $destinosExpo = $this->esOfertaExpo
            ? $this->obtenerDestinosBodegaExpo(
                $prods->pluck('producto_id')->filter()->unique()->values()->toArray(),
                $bodegasExpo
            )
            : [];

        $this->productos   = [];
        $this->stockErrors = [];

        // Batch: detalles de prefacturas reservadas para todos los productos de esta cotización
        $batchProdIds    = $prods->pluck('producto_id')->filter()->unique()->values()->toArray();
        $batchSecIds     = $prods->pluck('seccion_id')->filter()->unique()->values()->toArray();
        $reservasDetalle = collect();
        $reservadoPorProdSec = [];
        $reservadoGlobalPorProd = [];
        if (!$this->devuelto && !empty($batchProdIds) && !empty($batchSecIds)) {
            $reservasRaw = DB::table('prefactura_has_producto as php')
                ->join('prefactura as pf', 'pf.id', '=', 'php.prefactura_id')
                ->leftJoin('seccion as s', 's.id', '=', 'php.seccion_id')
                ->leftJoin('segmento as sg', 'sg.id', '=', 's.segmento_id')
                ->where('pf.estado', 'activo')
                ->whereRaw("TIMESTAMPADD(DAY, COALESCE((SELECT cp.dias_validez FROM configuracion_prefactura cp ORDER BY cp.id DESC LIMIT 1), 7), COALESCE(pf.created_at, CONCAT(COALESCE(pf.fecha_emision, CURDATE()), ' 00:00:00'))) > NOW()")
                ->whereIn('php.producto_id', $batchProdIds)
                ->whereIn('php.seccion_id', $batchSecIds)
                ->where('php.resta_inventario', 1)
                ->select('php.producto_id', 'php.seccion_id', 'pf.id as prefactura_id',
                         'pf.flujo_id', 'pf.nombre_cliente', 'php.cantidad',
                         'pf.fecha_emision', 'sg.bodega_id')
                ->selectRaw("DATE(TIMESTAMPADD(DAY, COALESCE((SELECT cp.dias_validez FROM configuracion_prefactura cp ORDER BY cp.id DESC LIMIT 1), 7), COALESCE(pf.created_at, CONCAT(COALESCE(pf.fecha_emision, CURDATE()), ' 00:00:00')))) as fecha_vencimiento_reserva")
                ->get();

            $cacheReservaCompleta = [];
            $reservasFiltradas = $reservasRaw->filter(function ($r) use (&$cacheReservaCompleta) {
                return $this->prefacturaTieneReservaCompleta((int) $r->prefactura_id, $cacheReservaCompleta);
            });

            $reservasDetalle = $reservasFiltradas
                ->groupBy(fn($r) => $r->producto_id . '_' . $r->seccion_id);

            $reservadoPorProdSec = $reservasFiltradas
                ->groupBy(fn($r) => $r->producto_id . '_' . $r->seccion_id)
                ->map(fn($rows) => (float) $rows->sum('cantidad'))
                ->toArray();

            $reservadoGlobalPorProd = $reservasFiltradas
                ->filter(fn($r) => (int) ($r->bodega_id ?? 0) !== 18)
                ->groupBy('producto_id')
                ->map(fn($rows) => (float) $rows->sum('cantidad'))
                ->toArray();
        }

        foreach ($prods as $i => $prod) {
            $rawStock         = null;
            $reservado        = null;
            $disponible       = null;
            $faltaStock       = false;
            $disponibleGlobal = null;
            $sinExistencia    = !((float) ($prod->resta_inventario ?? 0) > 0);

            // Para registros ya devueltos no recalcular stock (solo mostrar datos)
            if (!$this->devuelto && !$sinExistencia && $prod->producto_id && $prod->seccion_id) {
                if ($this->esOfertaExpo) {
                    $stockExpo = ExpoStock::resumen((int) $prod->producto_id, $bodegasExpo);
                    $rawStock = $stockExpo['existencia'];
                    $reservado = $stockExpo['reservado'];
                    $disponible = $stockExpo['disponible'];
                } else {
                    $rawStock  = (float) DB::table('recibido_bodega')
                        ->where('producto_id', $prod->producto_id)
                        ->where('seccion_id',  $prod->seccion_id)
                        ->where('cantidad_disponible', '>', 0)
                        ->sum('cantidad_disponible');

                    $reservado = (float) ($reservadoPorProdSec[$prod->producto_id . '_' . $prod->seccion_id] ?? 0.0);
                    $disponible = max(0.0, $rawStock - $reservado);
                }
                $faltaStock = $disponible < (float) $prod->cantidad;

                // ── Disponible Global: suma de todas las bodegas excepto Paperland (ID 18) ──
                $rawStockGlobal = (float) DB::table('recibido_bodega as rb')
                    ->join('seccion as s',   's.id',  '=', 'rb.seccion_id')
                    ->join('segmento as sg', 'sg.id', '=', 's.segmento_id')
                    ->where('rb.producto_id', $prod->producto_id)
                    ->where('rb.cantidad_disponible', '>', 0)
                    ->where('sg.bodega_id', '!=', 18)
                    ->sum('rb.cantidad_disponible');

                $reservadoGlobal = (float) ($reservadoGlobalPorProd[$prod->producto_id] ?? 0.0);

                $disponibleGlobal = max(0, (int) ($rawStockGlobal - $reservadoGlobal));

                if ($faltaStock) {
                    $this->stockErrors[] = [
                        'idx'               => $i,
                        'producto'          => $prod->nombre_producto,
                        'solicitado'        => (int) $prod->cantidad,
                        'disponible'        => (int) $disponible,
                        'disponible_global' => $disponibleGlobal,
                    ];
                }
            }

            $this->productos[] = [
                'idx'             => $i,
                'indice'          => $prod->indice,
                'nombre_producto' => $prod->nombre_producto,
                'nombre_bodega'   => $prod->nombre_bodega,
                'bodega_id'       => $prod->bodega_id,
                'bodega_actual_nombre' => $prod->bodega_actual_nombre,
                'seccion_actual_descripcion' => $prod->seccion_actual_descripcion,
                'unidad_medida'   => $prod->unidad_medida,
                'cantidad'        => $prod->cantidad,
                'producto_id'     => $prod->producto_id,
                'seccion_id'      => $prod->seccion_id,
                'resta_inventario'=> $prod->resta_inventario,
                'sin_existencia'  => $sinExistencia,
                'rawStock'        => $rawStock,
                'reservado'       => $reservado,
                'disponible'      => $disponible,
                'disponible_global' => $disponibleGlobal,
                'falta_stock'     => $faltaStock,
                'destinos_bodega' => $destinosExpo[(int) $prod->producto_id] ?? [],
                'reservas_detalle' => (!$this->devuelto && !$sinExistencia && $prod->producto_id && $prod->seccion_id)
                    ? ($reservasDetalle->get($prod->producto_id . '_' . $prod->seccion_id, collect())
                        ->map(fn($r) => (array) $r)->values()->toArray())
                    : [],
            ];

            $this->productosRevisados[$i] = false;
            $this->bodegaExpoSeleccionada[$i] = (int) $prod->bodega_id . '|' . (int) $prod->seccion_id;
        }

        // ── Si el flujo está devuelto, cargar motivo y notas de productos ──
        if ($this->devuelto && isset($revRec)) {
            $fullObs = $revRec->observaciones ?? '';
            // Quitar prefijo "Devuelto a Oferta: "
            $obsBody = preg_replace('/^Devuelto a Oferta:\s*/i', '', $fullObs);
            // Separar motivo de notas de productos " | [nombre]: nota"
            $pipePos = strpos($obsBody, ' | [');
            if ($pipePos !== false) {
                $this->motivoDevolucionGuardado = trim(substr($obsBody, 0, $pipePos));
                $notasPart = substr($obsBody, $pipePos);
                // Parsear cada nota de producto
                preg_match_all('/\|\s*\[([^\]]+)\]:\s*([^|]+)/', $notasPart, $matches, PREG_SET_ORDER);
                foreach ($matches as $m) {
                    $nombreProd = trim($m[1]);
                    $nota       = trim($m[2]);
                    foreach ($this->productos as $prod) {
                        if ($prod['nombre_producto'] === $nombreProd) {
                            $this->obsProducto[$prod['idx']] = $nota;
                            break;
                        }
                    }
                }
            } else {
                $this->motivoDevolucionGuardado = trim($obsBody);
            }
        }
    }

    public function cerrarDetalle(): void
    {
        $this->flujoId          = null;
        $this->flujoData        = null;
        $this->soloVisualizacion = false;
        $this->devuelto         = false;
        $this->motivoDevolucionGuardado = '';
        $this->cotizacionId     = null;
        $this->productos        = [];
        $this->stockErrors      = [];
        $this->confirmAccion    = null;
        $this->motivoDevolucion = '';
        $this->obsProducto      = [];
        $this->productosRevisados = [];
        $this->esOfertaExpo        = false;
        $this->bodegaExpoSeleccionada = [];
        $this->filtroProducto      = '';
        $this->filtroBodega        = '';
        $this->filtroEstado        = '';
        $this->filtroRevisado      = '';
        $this->mensajeExito        = '';
        $this->mensajeError        = '';
        $this->modalReservasVisible = false;
        $this->modalReservasData    = [];
        $this->modalReservaNombre   = '';
        $this->modalSinExistenciaVisible = false;
        $this->productosSinExistenciaModal = [];
        $this->motivoEdicionSinExistencia = '';
    }

    /**
     * Limpiar filtros de la tabla de productos.
     */
    public function limpiarFiltrosTabla(): void
    {
        $this->filtroProducto = '';
        $this->filtroBodega   = '';
        $this->filtroEstado   = '';
        $this->filtroRevisado = '';
    }

    public function guardarBodegaExpo(int $idx): void
    {
        if (!$this->flujoId || !$this->cotizacionId || !$this->esOfertaExpo || $this->devuelto || $this->soloVisualizacion) {
            $this->mensajeError = 'La reasignación de bodega solo está disponible para ofertas Expo en revisión activa.';
            return;
        }

        $producto = collect($this->productos)->firstWhere('idx', $idx);
        $seleccion = trim((string) ($this->bodegaExpoSeleccionada[$idx] ?? ''));

        if (!$producto) {
            $this->mensajeError = 'Seleccione una bodega válida donde exista el producto.';
            return;
        }

        [$bodegaDestinoId, $seccionDestinoId] = array_map(
            'intval',
            array_pad(explode('|', $seleccion, 2), 2, 0)
        );
        $destino = DB::table('recibido_bodega as rb')
            ->join('seccion as s', 's.id', '=', 'rb.seccion_id')
            ->join('segmento as sg', 'sg.id', '=', 's.segmento_id')
            ->join('bodega as b', 'b.id', '=', 'sg.bodega_id')
            ->where('rb.producto_id', (int) $producto['producto_id'])
            ->where('rb.seccion_id', $seccionDestinoId)
            ->where('sg.bodega_id', $bodegaDestinoId)
            ->where('rb.cantidad_disponible', '>', 0)
            ->select('b.nombre as bodega_nombre', 's.descripcion as seccion_descripcion')
            ->selectRaw('SUM(rb.cantidad_disponible) as stock')
            ->groupBy('b.nombre', 's.descripcion')
            ->first();

        if (!$destino) {
            $this->mensajeError = 'La bodega seleccionada ya no tiene existencia disponible para este producto.';
            return;
        }

        $destinoTexto = trim($destino->bodega_nombre . ' - ' . $destino->seccion_descripcion
            . ' (Existencia: ' . (int) $destino->stock . ')');
        $linea = DB::table('cotizacion_has_producto')
            ->where('cotizacion_id', $this->cotizacionId)
            ->where('indice', (int) $producto['indice'])
            ->first();

        if (!$linea) {
            $this->mensajeError = 'No se encontró la línea de la oferta para actualizar.';
            return;
        }

        if ((int) $linea->bodega_id === $bodegaDestinoId && (int) $linea->seccion_id === $seccionDestinoId) {
            $this->mensajeError = 'La línea ya está asignada a esa bodega y sección.';
            return;
        }

        DB::beginTransaction();
        try {
            DB::table('cotizacion_has_producto')
                ->where('cotizacion_id', $this->cotizacionId)
                ->where('indice', (int) $producto['indice'])
                ->update([
                    'Bodega_id' => $bodegaDestinoId,
                    'seccion_id' => $seccionDestinoId,
                    'nombre_bodega' => $destino->bodega_nombre,
                    'updated_at' => now(),
                ]);

            DB::table('historico_cotizacion_producto_sin_existencia')->insert([
                'id_cotizacion' => $this->cotizacionId,
                'id_producto' => (int) $linea->producto_id,
                'indice_linea' => (int) $linea->indice,
                'nombre_producto' => $linea->nombre_producto,
                'id_bodega_origen' => (int) $linea->bodega_id,
                'id_seccion_origen' => (int) $linea->seccion_id,
                'id_bodega_actualizacion' => $bodegaDestinoId,
                'id_seccion_actualizacion' => $seccionDestinoId,
                'nombre_bodega_origen' => $linea->nombre_bodega,
                'nombre_bodega_destino' => $destino->bodega_nombre,
                'motivo' => 'Reasignación de bodega Expo desde Revisión de Inventario. Flujo #' . $this->flujoId,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            $mensaje = 'Bodega reasignada a ' . $destinoTexto . '. La auditoría fue registrada.';
            $flujoId = $this->flujoId;
            $this->seleccionarFlujo($flujoId);
            $this->mensajeExito = $mensaje;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->mensajeError = 'No se pudo reasignar la bodega: ' . $e->getMessage();
        }
    }

    private function obtenerDestinosBodegaExpo(array $productoIds, array $bodegaIds): array
    {
        if (empty($productoIds) || empty($bodegaIds)) {
            return [];
        }

        $rows = DB::table('recibido_bodega as rb')
            ->join('seccion as s', 's.id', '=', 'rb.seccion_id')
            ->join('segmento as sg', 'sg.id', '=', 's.segmento_id')
            ->join('bodega as b', 'b.id', '=', 'sg.bodega_id')
            ->whereIn('rb.producto_id', $productoIds)
            ->whereIn('sg.bodega_id', $bodegaIds)
            ->where('rb.cantidad_disponible', '>', 0)
            ->select(
                'rb.producto_id',
                'sg.bodega_id',
                's.id as seccion_id',
                'b.nombre as bodega_nombre',
                's.descripcion as seccion_descripcion',
                DB::raw('SUM(rb.cantidad_disponible) as stock')
            )
            ->groupBy('rb.producto_id', 'sg.bodega_id', 's.id', 'b.nombre', 's.descripcion')
            ->orderBy('b.nombre')
            ->orderBy('s.descripcion')
            ->get();

        $destinos = [];
        foreach ($rows as $row) {
            $destinos[(int) $row->producto_id][] = [
                'value' => (int) $row->bodega_id . '|' . (int) $row->seccion_id,
                'bodega_nombre' => (string) $row->bodega_nombre,
                'text' => trim($row->bodega_nombre . ' - ' . $row->seccion_descripcion . ' (Existencia: ' . (int) $row->stock . ')'),
            ];
        }

        return $destinos;
    }

    /**
     * Verifica si todos los productos visibles fueron marcados como revisados.
     */
    public function todosProductosRevisados(): bool
    {
        if (empty($this->productos)) {
            return false;
        }

        foreach ($this->productos as $prod) {
            $idx = $prod['idx'];
            if (empty($this->productosRevisados[$idx])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Indica si existe al menos una línea marcada como sin existencia.
     */
    public function tieneProductosSinExistencia(): bool
    {
        return collect($this->productos)
            ->contains(fn (array $prod) => !empty($prod['sin_existencia']));
    }

    /**
     * Productos visibles según filtros de la tabla.
     */
    public function getProductosFiltradosProperty(): array
    {
        $textoProducto = trim(mb_strtolower($this->filtroProducto));
        $textoBodega   = trim(mb_strtolower($this->filtroBodega));
        $estado        = trim($this->filtroEstado);
        $revisado      = trim($this->filtroRevisado);

        return collect($this->productos)
            ->filter(function (array $prod) use ($textoProducto, $textoBodega, $estado, $revisado) {
                $nombreProducto = mb_strtolower($prod['nombre_producto'] ?? '');
                $nombreBodega   = mb_strtolower($prod['nombre_bodega'] ?? '');
                $estaRevisado    = !empty($this->productosRevisados[$prod['idx']] ?? false);

                if ($textoProducto !== '' && mb_strpos($nombreProducto, $textoProducto) === false) {
                    return false;
                }

                if ($textoBodega !== '' && mb_strpos($nombreBodega, $textoBodega) === false) {
                    return false;
                }

                if ($estado !== '') {
                    if ($estado === 'sin_existencia' && !($prod['sin_existencia'] ?? false)) {
                        return false;
                    }

                    if ($estado === 'sin_stock' && !($prod['falta_stock'] ?? false)) {
                        return false;
                    }

                    if ($estado === 'ok' && ($prod['falta_stock'] ?? false)) {
                        return false;
                    }

                    if ($estado === 'sin_control' && (($prod['disponible'] !== null) || ($prod['sin_existencia'] ?? false))) {
                        return false;
                    }
                }

                if ($revisado !== '') {
                    if ($revisado === 'si' && !$estaRevisado) {
                        return false;
                    }

                    if ($revisado === 'no' && $estaRevisado) {
                        return false;
                    }
                }

                return true;
            })
            ->values()
            ->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────
    // ACCIONES
    // ─────────────────────────────────────────────────────────────────────

    public function confirmarAccion(string $accion): void
    {
        $this->confirmAccion    = $accion;
        $this->motivoDevolucion = '';
        $this->mensajeError     = '';
    }

    public function confirmarPrefactura(): void
    {
        $this->confirmarAccion('prefactura');
    }

    public function confirmarDevolucion(): void
    {
        $this->confirmarAccion('devolver');
    }

    public function cancelarAccion(): void
    {
        $this->confirmAccion    = null;
        $this->motivoDevolucion = '';
        $this->mensajeError     = '';
    }

    /**
     * Pasar el flujo de Revisión de Inventario → Prefactura.
     * Replica la lógica de ModalFlujoPedido::ganadoraOferta() para la creación de prefactura.
     */
    public function pasarAPrefactura(): void
    {
        if (!$this->flujoId || !$this->cotizacionId) {
            $this->mensajeError = 'No hay flujo u oferta seleccionada.';
            return;
        }

        if (!$this->todosProductosRevisados()) {
            $this->mensajeError = 'Debe marcar como revisados todos los productos antes de pasar a Prefactura.';
            return;
        }

        if (!empty($this->stockErrors)) {
            $this->mensajeError = 'Hay productos sin stock suficiente. No se puede pasar a Prefactura.';
            return;
        }

        $cotizacion = DB::table('cotizacion')->where('id', $this->cotizacionId)->first();
        if (!$cotizacion) {
            $this->mensajeError = 'Oferta ganadora no encontrada.';
            return;
        }

        $productos = DB::table('cotizacion_has_producto')
            ->where('cotizacion_id', $this->cotizacionId)
            ->get();

        if ($productos->isEmpty()) {
            $this->mensajeError = 'La oferta no tiene productos para generar prefactura.';
            return;
        }

        $lineasSinExistencia = $productos->filter(function ($prod) {
            return !((float) ($prod->resta_inventario ?? 0) > 0);
        });

        if ($lineasSinExistencia->isNotEmpty()) {
            $this->mensajeError = 'No se puede pasar a Prefactura: la oferta contiene productos marcados como sin existencia.';
            return;
        }

        $tramitePrefacturaId = (int) (DB::table('tipos_tramites')
            ->whereRaw('LOWER(nombre) = ?', ['prefactura'])
            ->value('id') ?? 0);

        if ($tramitePrefacturaId <= 0) {
            $this->mensajeError = 'No se encontró el tipo de trámite de Prefactura.';
            return;
        }

        $diasValidez = $this->diasVigenciaPrefactura((int) $this->flujoId);

        DB::beginTransaction();
        try {
            // Crear prefactura
            $prefacturaId = DB::table('prefactura')->insertGetId([
                'cotizacion_id'     => $this->cotizacionId,
                'flujo_id'          => $this->flujoId,
                'cliente_id'        => $cotizacion->cliente_id,
                'nombre_cliente'    => $cotizacion->nombre_cliente,
                'RTN'               => $cotizacion->RTN,
                'fecha_emision'     => now()->toDateString(),
                'fecha_vencimiento' => now()->addDays($diasValidez)->toDateString(),
                'sub_total'         => $cotizacion->sub_total,
                'sub_total_grabado' => $cotizacion->sub_total_grabado,
                'sub_total_excento' => $cotizacion->sub_total_excento,
                'isv'               => $cotizacion->isv,
                'total'             => $cotizacion->total,
                'porc_descuento'    => $cotizacion->porc_descuento ?? 0,
                'monto_descuento'   => $cotizacion->monto_descuento ?? 0,
                'tipo_venta_id'     => $cotizacion->tipo_venta_id,
                'vendedor'          => $cotizacion->vendedor,
                'nota'              => $cotizacion->nota,
                'arregloIdInputs'   => $cotizacion->arregloIdInputs,
                'numeroInputs'      => $cotizacion->numeroInputs,
                'estado'            => 'activo',
                'users_id'          => Auth::id(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // Insertar productos de la prefactura
            $prefProds = [];
            foreach ($productos as $prod) {
                $prefProds[] = [
                    'prefactura_id'            => $prefacturaId,
                    'producto_id'              => $prod->producto_id,
                    'indice'                   => $prod->indice,
                    'nombre_producto'          => $prod->nombre_producto,
                    'nombre_bodega'            => $prod->nombre_bodega,
                    'precio_unidad'            => $prod->precio_unidad,
                    'cantidad'                 => $prod->cantidad,
                    'sub_total'                => $prod->sub_total,
                    'isv'                      => $prod->isv,
                    'total'                    => $prod->total,
                    'isv_producto'             => $prod->isv_producto,
                    'Bodega_id'                => $prod->bodega_id,
                    'seccion_id'               => $prod->seccion_id,
                    'unidad_medida_venta_id'   => $prod->unidad_medida_venta_id,
                    'monto_descProducto'       => $prod->monto_descProducto ?? 0,
                    'idPrecioSeleccionado'      => $prod->idPrecioSeleccionado ?? null,
                    'precioSeleccionado'        => $prod->precioSeleccionado ?? null,
                    'precios_producto_carga_id' => $prod->precios_producto_carga_id ?? null,
                    // Normaliza a bandera 0/1 para evitar desbordes en tinyint.
                    'resta_inventario'          => ((float) ($prod->resta_inventario ?? 0) > 0) ? 1 : 0,
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ];
            }
            if (!empty($prefProds)) {
                DB::table('prefactura_has_producto')->insert($prefProds);
            }

            // Cerrar el paso de Revision de Inventario en historico_flujo
            DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->where('tipo_tramite_id', 9)
                ->where('estado_id', '!=', 7)
                ->update([
                    'estado_id'     => 1,
                    'observaciones' => 'Revisión aprobada. Prefactura #' . $prefacturaId . ' creada.',
                    'updated_by'    => Auth::id(),
                    'updated_at'    => now(),
                ]);

            // Registrar en historico_flujo el paso de prefactura
            DB::table('historico_flujo')->insert([
                'flujo_id'        => $this->flujoId,
                'tipo_tramite_id' => $tramitePrefacturaId,
                'tramite_id'      => $prefacturaId,
                'estado_id'       => 1,
                'observaciones'   => 'Prefactura #' . $prefacturaId . ' creada desde Revisión de Inventario.',
                'created_by'      => Auth::id(),
                'updated_by'      => Auth::id(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // Avanzar flujo a prefactura
            DB::table('flujo')->where('id', $this->flujoId)->update([
                'tipo_tramite_id' => $tramitePrefacturaId,
                'updated_by'      => Auth::id(),
                'updated_at'      => now(),
            ]);

            DB::commit();

            $flujoIdCerrado = $this->flujoId;

            // Notificar a facturadores que hay una prefactura nueva
            try {
                $flujoCtx = DB::table('flujo')
                    ->where('id', $flujoIdCerrado)
                    ->select('nombre as cliente')
                    ->first();
                event(new FlujoAvanzadoEvent(
                    $flujoIdCerrado,
                    4,
                    ['cliente' => $flujoCtx?->cliente ?? $cotizacion->nombre_cliente ?? 'N/A', 'monto' => $cotizacion->total ?? null, 'referencia' => 'Prefactura #' . $prefacturaId]
                ));
            } catch (\Throwable $notifEx) {
                \Log::error('NotificacionFlujo dispatch failed (RevicionInventario tipo=4)', [
                    'flujo_id' => $flujoIdCerrado,
                    'error'    => $notifEx->getMessage(),
                ]);
            }

            $this->cerrarDetalle();
            $this->cargar();
            $this->mensajeExito = 'Flujo #' . $flujoIdCerrado . ': Prefactura #' . $prefacturaId . ' generada. Válida por ' . $diasValidez . ' día(s).';

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->mensajeError = 'Error al crear la prefactura: ' . $e->getMessage();
        }
    }

    /**
     * Devolver el flujo desde Revisión de Inventario → Ofertas.
     * Requiere observaciones obligatorias.
     * Registra las notas de reemplazo de productos si existen.
     */
    public function devolverAOferta(): void
    {
        if (!$this->flujoId) return;

        if (!$this->todosProductosRevisados()) {
            $this->mensajeError = 'Debe marcar como revisados todos los productos antes de devolver a Oferta.';
            return;
        }

        $motivo = trim($this->motivoDevolucion);
        if ($motivo === '') {
            $this->mensajeError = 'Debe indicar el motivo para devolver a Oferta.';
            return;
        }

        // Consolidar observaciones de productos con notas
        $obsProds = '';
        foreach ($this->obsProducto as $idx => $obs) {
            $obs = trim($obs);
            if ($obs !== '' && isset($this->productos[$idx])) {
                $nombreProd = $this->productos[$idx]['nombre_producto'] ?? "Producto {$idx}";
                $obsProds .= " | [{$nombreProd}]: {$obs}";
            }
        }

        $obsCompleta = $motivo . ($obsProds !== '' ? $obsProds : '');

        DB::beginTransaction();
        try {
            // Cerrar el registro de Revision de Inventario como "devuelto"
            DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->where('tipo_tramite_id', 9)
                ->where('estado_id', '!=', 7)
                ->update([
                    'estado_id'     => 7,  // inactivado / devuelto
                    'observaciones' => 'Devuelto a Oferta: ' . $obsCompleta,
                    'updated_by'    => Auth::id(),
                    'updated_at'    => now(),
                ]);

            // Quitar la marca de ganadora de la oferta (vuelve a estado oferta normal)
            DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->where('tipo_tramite_id', 2)
                ->where('observaciones', 'ganadora')
                ->update([
                    'observaciones' => 'Devuelta desde Revisión: ' . $motivo,
                    'updated_by'    => Auth::id(),
                    'updated_at'    => now(),
                ]);

            // Auditoría en cotizacion_estado
            if ($this->cotizacionId) {
                DB::table('cotizacion_estado')->insert([
                    'cotizacion_id' => $this->cotizacionId,
                    'flujo_id'      => $this->flujoId,
                    'ganadora'      => 4,  // 4 = devuelta desde revisión
                    'comentario'    => 'Devuelta a Oferta desde Revisión de Inventario: ' . $obsCompleta,
                    'estado_id'     => 1,
                    'created_by'    => Auth::id(),
                    'updated_by'    => Auth::id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // Retroceder flujo a Ofertas (tipo_tramite_id = 2)
            DB::table('flujo')->where('id', $this->flujoId)->update([
                'tipo_tramite_id' => 2,
                'updated_by'      => Auth::id(),
                'updated_at'      => now(),
            ]);

            DB::commit();

            $this->devuelto                 = true;
            $this->motivoDevolucionGuardado  = $motivo;
            $this->confirmAccion    = null;
            $this->motivoDevolucion = '';
            $this->mensajeError     = '';
            $this->cargar();
            $this->mensajeExito = 'Flujo #' . $this->flujoId . ' devuelto a Oferta correctamente. Se registraron las observaciones.';

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->mensajeError = 'Error al devolver a Oferta: ' . $e->getMessage();
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // MODAL RESERVAS
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Abre el modal mostrando qué prefacturas/flujos tienen reservado
     * el producto indicado en la sección especificada.
     */
    public function verReservas(int $productoId, int $seccionId, string $nombreProducto): void
    {
        $this->modalReservaNombre = $nombreProducto;

        $this->modalReservasData = DB::table('prefactura_has_producto as php')
            ->join('prefactura as pf', 'pf.id', '=', 'php.prefactura_id')
            ->leftJoin('flujo as f', 'f.id', '=', 'pf.flujo_id')
            ->where('pf.estado', 'activo')
            ->whereRaw("TIMESTAMPADD(DAY, COALESCE((SELECT cp.dias_validez FROM configuracion_prefactura cp ORDER BY cp.id DESC LIMIT 1), 7), COALESCE(pf.created_at, CONCAT(COALESCE(pf.fecha_emision, CURDATE()), ' 00:00:00'))) > NOW()")
            ->where('php.producto_id', $productoId)
            ->where('php.seccion_id', $seccionId)
            ->where('php.resta_inventario', 1)
            ->select(
                'pf.id as prefactura_id',
                'pf.flujo_id',
                'pf.nombre_cliente',
                'php.cantidad',
                'pf.fecha_emision'
            )
            ->selectRaw("DATE(TIMESTAMPADD(DAY, COALESCE((SELECT cp.dias_validez FROM configuracion_prefactura cp ORDER BY cp.id DESC LIMIT 1), 7), COALESCE(pf.created_at, CONCAT(COALESCE(pf.fecha_emision, CURDATE()), ' 00:00:00')))) as fecha_vencimiento_reserva")
            ->get()
            ->filter(function ($r) {
                static $cache = [];
                return $this->prefacturaTieneReservaCompleta((int) $r->prefactura_id, $cache);
            })
            ->map(fn($r) => (array) $r)
            ->toArray();

        $this->modalReservasVisible = true;
    }

    /**
     * Abre el modal con los productos marcados como sin existencia para reasignarlos.
     */
    public function abrirEdicionProductosSinExistencia(): void
    {
        if (!$this->flujoId || !$this->cotizacionId) {
            $this->mensajeError = 'No hay un flujo u oferta seleccionada.';
            return;
        }

        $sinExistencia = collect($this->productos)
            ->filter(fn (array $prod) => (bool) ($prod['sin_existencia'] ?? false))
            ->values();

        if ($sinExistencia->isEmpty()) {
            $this->mensajeError = 'No hay productos marcados como sin existencia para editar.';
            return;
        }

        $productoIds = $sinExistencia->pluck('producto_id')->filter()->unique()->values()->all();
        $destinosPorProducto = $this->obtenerDestinosDisponiblesPorProducto($productoIds);

        $this->productosSinExistenciaModal = $sinExistencia->map(function (array $prod) use ($destinosPorProducto) {
            $destinos = $destinosPorProducto[$prod['producto_id']] ?? [];

            return [
                'idx' => $prod['idx'],
                'producto_id' => $prod['producto_id'],
                'nombre_producto' => $prod['nombre_producto'],
                'cantidad' => $prod['cantidad'],
                'bodega_actual_id' => $prod['bodega_id'] ?? null,
                'bodega_actual_nombre' => $prod['bodega_actual_nombre'] ?? ($prod['nombre_bodega'] ?? 'SIN EXISTENCIA'),
                'seccion_actual_id' => $prod['seccion_id'] ?? null,
                'seccion_actual_descripcion' => $prod['seccion_actual_descripcion'] ?? null,
                'destino_seleccionado' => '',
                'destinos' => $destinos,
            ];
        })->toArray();

        $this->motivoEdicionSinExistencia = '';
        $this->modalSinExistenciaVisible = true;
        $this->dispatchBrowserEvent('modal-sin-existencia-show');
    }

    /**
     * Guarda las reasignaciones de los productos sin existencia.
     */
    public function guardarEdicionProductosSinExistencia(): void
    {
        if (!$this->flujoId || !$this->cotizacionId) {
            $this->mensajeError = 'No hay un flujo u oferta seleccionada.';
            return;
        }

        if (empty($this->productosSinExistenciaModal)) {
            $this->mensajeError = 'No hay productos para actualizar.';
            return;
        }

        $motivo = trim($this->motivoEdicionSinExistencia);
        $actualizados = 0;

        DB::beginTransaction();
        try {
            foreach ($this->productosSinExistenciaModal as $linea) {
                $seleccion = trim((string) ($linea['destino_seleccionado'] ?? ''));
                if ($seleccion === '') {
                    continue;
                }

                [$bodegaDestinoId, $seccionDestinoId] = array_pad(explode('|', $seleccion, 2), 2, null);
                $bodegaDestinoId = (int) $bodegaDestinoId;
                $seccionDestinoId = (int) $seccionDestinoId;

                if ($bodegaDestinoId <= 0 || $seccionDestinoId <= 0) {
                    continue;
                }

                $opcionDestino = collect($linea['destinos'] ?? [])->firstWhere('value', $seleccion);
                if (!$opcionDestino) {
                    continue;
                }

                $nombreBodegaDestino = (string) ($opcionDestino['bodega_nombre'] ?? '');

                $afectados = DB::table('cotizacion_has_producto')
                    ->where('cotizacion_id', $this->cotizacionId)
                    ->where('producto_id', (int) $linea['producto_id'])
                    ->where('indice', (int) $linea['idx'])
                    ->update([
                        'Bodega_id' => $bodegaDestinoId,
                        'seccion_id' => $seccionDestinoId,
                        'nombre_bodega' => $nombreBodegaDestino,
                        'resta_inventario' => 1,
                        'updated_at' => now(),
                    ]);

                if ($afectados > 0) {
                    DB::table('historico_cotizacion_producto_sin_existencia')->insert([
                        'id_cotizacion' => $this->cotizacionId,
                        'id_producto' => (int) $linea['producto_id'],
                        'indice_linea' => (int) $linea['idx'],
                        'nombre_producto' => $linea['nombre_producto'],
                        'id_bodega_origen' => (int) ($linea['bodega_actual_id'] ?? 0) ?: null,
                        'id_seccion_origen' => (int) ($linea['seccion_actual_id'] ?? 0) ?: null,
                        'id_bodega_actualizacion' => $bodegaDestinoId,
                        'id_seccion_actualizacion' => $seccionDestinoId,
                        'nombre_bodega_origen' => $linea['bodega_actual_nombre'] ?? 'SIN EXISTENCIA',
                        'nombre_bodega_destino' => $nombreBodegaDestino,
                        'motivo' => $motivo !== '' ? $motivo : 'Reasignación de producto sin existencia',
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $actualizados++;
                }
            }

            if ($actualizados === 0) {
                DB::rollBack();
                $this->mensajeError = 'Seleccione al menos un destino válido para actualizar.';
                return;
            }

            DB::commit();

            $this->modalSinExistenciaVisible = false;
            $this->productosSinExistenciaModal = [];
            $this->motivoEdicionSinExistencia = '';
            $this->dispatchBrowserEvent('modal-sin-existencia-hide');

            $this->seleccionarFlujo($this->flujoId, $this->soloVisualizacion);
            $this->mensajeExito = 'Se actualizaron ' . $actualizados . ' producto(s) sin existencia.';
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->mensajeError = 'No se pudo actualizar la relación de productos sin existencia: ' . $e->getMessage();
        }
    }

    /**
     * Obtiene destinos con stock disponible por producto.
     *
     * @param array<int> $productoIds
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function obtenerDestinosDisponiblesPorProducto(array $productoIds): array
    {
        if (empty($productoIds)) {
            return [];
        }

        $rows = DB::table('recibido_bodega as rb')
            ->join('seccion as s', 's.id', '=', 'rb.seccion_id')
            ->join('segmento as sg', 'sg.id', '=', 's.segmento_id')
            ->join('bodega as b', 'b.id', '=', 'sg.bodega_id')
            ->whereIn('rb.producto_id', $productoIds)
            ->where('rb.cantidad_disponible', '>', 0)
            ->select(
                'rb.producto_id',
                'sg.bodega_id',
                's.id as seccion_id',
                'b.nombre as bodega_nombre',
                's.descripcion as seccion_descripcion',
                DB::raw('SUM(rb.cantidad_disponible) as stock')
            )
            ->groupBy('rb.producto_id', 'sg.bodega_id', 's.id', 'b.nombre', 's.descripcion')
            ->orderBy('b.nombre')
            ->orderBy('s.descripcion')
            ->get();

        $destinos = [];
        foreach ($rows as $row) {
            $destinos[(int) $row->producto_id][] = [
                'value' => (int) $row->bodega_id . '|' . (int) $row->seccion_id,
                'bodega_id' => (int) $row->bodega_id,
                'seccion_id' => (int) $row->seccion_id,
                'bodega_nombre' => (string) $row->bodega_nombre,
                'seccion_descripcion' => (string) $row->seccion_descripcion,
                'stock' => (float) $row->stock,
                'text' => trim((string) $row->bodega_nombre . ' - ' . (string) $row->seccion_descripcion . ' (Stock: ' . (int) $row->stock . ')'),
            ];
        }

        return $destinos;
    }

    public function cerrarModalReservas(): void
    {
        $this->modalReservasVisible = false;
        $this->modalReservasData    = [];
        $this->modalReservaNombre   = '';
    }

    private function prefacturaTieneReservaCompleta(int $prefacturaId, array &$cache): bool
    {
        if (array_key_exists($prefacturaId, $cache)) {
            return (bool) $cache[$prefacturaId];
        }

        $lineas = DB::table('prefactura_has_producto')
            ->where('prefactura_id', $prefacturaId)
            ->where('resta_inventario', 1)
            ->whereNotNull('producto_id')
            ->whereNotNull('seccion_id')
            ->get(['producto_id', 'seccion_id', 'cantidad']);

        foreach ($lineas as $linea) {
            $rawStock = (float) DB::table('recibido_bodega')
                ->where('producto_id', $linea->producto_id)
                ->where('seccion_id', $linea->seccion_id)
                ->where('cantidad_disponible', '>', 0)
                ->sum('cantidad_disponible');

            if ($rawStock + 0.0001 < (float) $linea->cantidad) {
                $cache[$prefacturaId] = false;
                return false;
            }
        }

        $cache[$prefacturaId] = true;
        return true;
    }

    // ─────────────────────────────────────────────────────────────────────
    // RENDER
    // ─────────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.flujo.revicioninventario', [
            'flujoData' => $this->flujoData,
        ]);
    }
}
