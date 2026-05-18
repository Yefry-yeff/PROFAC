<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
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
        $this->cargar();
    }

    public function cargar(): void
    {
        $term = trim($this->busqueda);
        $this->bandejaRegistros  = $this->buildBandejaQuery($term, 'llegando');
        $this->bandejaDevueltos  = $this->buildBandejaQuery($term, 'devueltos');
        $this->bandejaPrefactura = $this->buildBandejaQuery($term, 'prefactura');
    }

    public function cambiarTab(string $tab): void
    {
        $this->tabActiva = in_array($tab, ['llegando', 'devueltos', 'prefactura']) ? $tab : 'llegando';
    }

    private function buildBandejaQuery(string $term, string $tipo): array
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

        return $q->orderByDesc('hf.created_at')->get()->map(fn($r) => (array) $r)->toArray();
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

        // Obtener productos (solo nombre + cantidad + stock actual)
        $prods = DB::table('cotizacion_has_producto as chp')
            ->where('chp.cotizacion_id', $this->cotizacionId)
            ->select('chp.nombre_producto', 'chp.nombre_bodega', 'chp.cantidad', 'chp.producto_id', 'chp.seccion_id', 'chp.resta_inventario')
            ->get();

        $this->productos   = [];
        $this->stockErrors = [];

        foreach ($prods as $i => $prod) {
            $disponible = null;
            $faltaStock = false;

            // Para registros ya devueltos no recalcular stock (solo mostrar datos)
            if (!$this->devuelto && $prod->resta_inventario && $prod->producto_id && $prod->seccion_id) {
                $rawStock = (float) DB::table('recibido_bodega')
                    ->where('producto_id', $prod->producto_id)
                    ->where('seccion_id',  $prod->seccion_id)
                    ->where('cantidad_disponible', '>', 0)
                    ->sum('cantidad_disponible');

                $reservado = (float) DB::table('prefactura_has_producto as php')
                    ->join('prefactura as pf', 'pf.id', '=', 'php.prefactura_id')
                    ->where('pf.estado', 'activo')
                    ->where('php.producto_id', $prod->producto_id)
                    ->where('php.seccion_id',  $prod->seccion_id)
                    ->where('php.resta_inventario', 1)
                    ->sum('php.cantidad');

                $disponible = max(0.0, $rawStock - $reservado);
                $faltaStock = $disponible < (float) $prod->cantidad;

                if ($faltaStock) {
                    $this->stockErrors[] = [
                        'idx'        => $i,
                        'producto'   => $prod->nombre_producto,
                        'solicitado' => (int) $prod->cantidad,
                        'disponible' => (int) $disponible,
                    ];
                }
            }

            $this->productos[] = [
                'idx'             => $i,
                'nombre_producto' => $prod->nombre_producto,
                'nombre_bodega'   => $prod->nombre_bodega,
                'cantidad'        => $prod->cantidad,
                'producto_id'     => $prod->producto_id,
                'seccion_id'      => $prod->seccion_id,
                'resta_inventario'=> $prod->resta_inventario,
                'disponible'      => $disponible,
                'falta_stock'     => $faltaStock,
            ];
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
        $this->mensajeExito     = '';
        $this->mensajeError     = '';
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

        $config      = DB::table('configuracion_prefactura')->first();
        $diasValidez = $config ? (int) $config->dias_validez : 7;

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
                'tipo_tramite_id' => 4,
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
                'tipo_tramite_id' => 4,
                'updated_by'      => Auth::id(),
                'updated_at'      => now(),
            ]);

            DB::commit();

            $flujoIdCerrado = $this->flujoId;
            $this->cerrarDetalle();
            $this->cargar();
            $this->mensajeExito = 'Flujo #' . $flujoIdCerrado . ': Prefactura #' . $prefacturaId . ' generada. Válida por ' . $diasValidez . ' día(s).';

        } catch (\Exception $e) {
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

        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error al devolver a Oferta: ' . $e->getMessage();
        }
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
