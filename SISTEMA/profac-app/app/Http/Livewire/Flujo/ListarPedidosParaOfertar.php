<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Dos pestanas:
 *  1. Pedidos: flujos de venta con tipo_tramite_id = 1 (estado pedido)
 *  2. Ofertas: flujos de venta que tienen al menos una oferta en historico_flujo (tipo_tramite_id = 2)
 */
class ListarPedidosParaOfertar extends Component
{
    // Pestana activa
    public $tab = 'pedidos';   // 'pedidos' | 'ofertas'

    // Filtros y sort - Pestana 1 (pedidos)
    public $busquedaPed  = '';
    public $sortColPed   = 'created_at';
    public $sortDirPed   = 'desc';
    

    // Filtros y sort - Pestana 2 (ofertas)
    public $busquedaOfr  = '';
    public $sortColOfr   = 'created_at';
    public $sortDirOfr   = 'desc';

    // Paginacion
    public int $paginaPed = 1;
    public int $paginaOfr = 1;
    public int $perPage   = 5;

    // Datos
    public $pedidos = [];
    public $ofertas = [];

    // Mensajes flash
    public $mensajeExito = '';
    public $mensajeError = '';

    // Listeners
    protected $listeners = ['pedidoActualizado' => 'cargar', 'refrescarOfertas' => 'cargar'];

    // LIFECYCLE

    public function mount(): void
    {
        $this->cargar();
    }

    public function updatedBusquedaPed(): void { $this->paginaPed = 1; $this->cargarPedidos(); }
    public function updatedBusquedaOfr(): void { $this->paginaOfr = 1; $this->cargarOfertas(); }

    // PAGINACION

    public function pedPrev(): void { if ($this->paginaPed > 1) { $this->paginaPed--; } }
    public function pedNext(): void {
        $total = count($this->pedidos);
        $lastPage = max(1, (int) ceil($total / $this->perPage));
        if ($this->paginaPed < $lastPage) { $this->paginaPed++; }
    }

    public function ofrPrev(): void { if ($this->paginaOfr > 1) { $this->paginaOfr--; } }
    public function ofrNext(): void {
        $total = count($this->ofertas);
        $lastPage = max(1, (int) ceil($total / $this->perPage));
        if ($this->paginaOfr < $lastPage) { $this->paginaOfr++; }
    }

    // SORT

    public function sortByPed(string $col): void
    {
        $this->sortDirPed = ($this->sortColPed === $col && $this->sortDirPed === 'asc') ? 'desc' : 'asc';
        $this->sortColPed = $col;
        $this->cargarPedidos();
    }

    public function sortByOfr(string $col): void
    {
        $this->sortDirOfr = ($this->sortColOfr === $col && $this->sortDirOfr === 'asc') ? 'desc' : 'asc';
        $this->sortColOfr = $col;
        $this->cargarOfertas();
    }

    // CARGAR DATOS

    public function cargar(): void
    {
        $this->cargarPedidos();
        $this->cargarOfertas();
    }

    /**
     * Pestana 1: Flujos de venta en estado pedido (tipo_tramite_id = 1)
     */
    public function cargarPedidos(): void
    {
        $term = trim($this->busquedaPed);
        $allowed = ['id', 'cliente', 'total_productos', 'total_ofertas', 'created_at'];
        $col = in_array($this->sortColPed, $allowed) ? $this->sortColPed : 'created_at';
        $dir = $this->sortDirPed === 'asc' ? 'asc' : 'desc';

        $q = DB::table('flujo as f')
            ->join('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
            ->join('pedido as p', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'p.id')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.users_id')
            ->where('f.tipo_flujo_id', 1)
            ->where('f.tipo_tramite_id', 1)
            ->whereNotIn('p.estado', ['cancelado'])
            ->whereRaw('NOT EXISTS (SELECT 1 FROM historico_flujo hf WHERE hf.flujo_id = f.id AND hf.tipo_tramite_id = 2)')
            ->select(
                'f.id as flujo_id',
                'p.id',
                'p.observaciones',
                'p.created_at',
                'c.nombre as cliente',
                'c.rtn',
                'u.name as registrado_por',
                DB::raw('(SELECT COUNT(*) FROM pedido_detalle pd WHERE pd.pedido_id = p.id) as total_productos'),
                DB::raw('(SELECT COUNT(*) FROM historico_flujo hf WHERE hf.flujo_id = f.id AND hf.tipo_tramite_id = 2) as total_ofertas'),
                DB::raw("(SELECT COUNT(*) FROM historico_flujo hf WHERE hf.flujo_id = f.id AND hf.tipo_tramite_id = 2 AND hf.observaciones = 'ganadora') as ofertas_ganadoras")
            );

        if ($term !== '') {
            if (is_numeric($term)) {
                $q->where('p.id', (int) $term);
            } else {
                $like = '%' . $term . '%';
                $q->where(function ($sub) use ($like) {
                    $sub->where('c.nombre', 'LIKE', $like)
                        ->orWhere('c.rtn', 'LIKE', $like);
                });
            }
        }

        $colMap = [
            'id'              => 'p.id',
            'cliente'         => 'c.nombre',
            'total_productos' => 'total_productos',
            'total_ofertas'   => 'total_ofertas',
            'created_at'      => 'p.created_at',
        ];
        $q->orderBy(DB::raw($colMap[$col] ?? 'p.created_at'), $dir);

        $this->pedidos = $q->get()->toArray();
    }

    /**
     * Pestana 2: Flujos de venta con al menos una oferta registrada en historico_flujo.
     * Incluye flujos con y sin pedido previo (UNION).
     */
    public function cargarOfertas(): void
    {
        $term = trim($this->busquedaOfr);
        $allowed = ['flujo_id', 'cliente', 'total_ofertas', 'estado_flujo', 'created_at'];
        $col = in_array($this->sortColOfr, $allowed) ? $this->sortColOfr : 'created_at';
        $dir = $this->sortDirOfr === 'asc' ? 'asc' : 'desc';

        // Caso A: flujo partio de un pedido
        $qA = DB::table('flujo as f')
            ->join('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
            ->join('pedido as p', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'p.id')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->where('f.tipo_flujo_id', 1)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('historico_flujo as hf')
                  ->whereColumn('hf.flujo_id', 'f.id')
                  ->where('hf.tipo_tramite_id', 2);
            })
            ->select(
                'f.id as flujo_id',
                'p.id as tramite_id',
                'c.nombre as cliente',
                'c.rtn',
                'p.created_at',
                'tt.nombre as estado_flujo',
                DB::raw('(SELECT COUNT(*) FROM historico_flujo hf WHERE hf.flujo_id = f.id AND hf.tipo_tramite_id = 2) as total_ofertas'),
                DB::raw("(SELECT COUNT(*) FROM historico_flujo hf WHERE hf.flujo_id = f.id AND hf.tipo_tramite_id = 2 AND hf.observaciones = 'ganadora') as tiene_ganadora"),
                DB::raw("'pedido' as origen")
            );

        // Caso B: flujo partio directamente de cotizacion
        $qB = DB::table('flujo as f')
            ->join('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
            ->join('cotizacion as o', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'o.id')
            ->where('f.tipo_flujo_id', 1)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('historico_flujo as hf')
                  ->whereColumn('hf.flujo_id', 'f.id')
                  ->where('hf.tipo_tramite_id', 1);
            })
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('historico_flujo as hf')
                  ->whereColumn('hf.flujo_id', 'f.id')
                  ->where('hf.tipo_tramite_id', 2);
            })
            ->select(
                'f.id as flujo_id',
                'o.id as tramite_id',
                'o.nombre_cliente as cliente',
                'o.RTN as rtn',
                'o.created_at',
                'tt.nombre as estado_flujo',
                DB::raw('(SELECT COUNT(*) FROM historico_flujo hf WHERE hf.flujo_id = f.id AND hf.tipo_tramite_id = 2) as total_ofertas'),
                DB::raw("(SELECT COUNT(*) FROM historico_flujo hf WHERE hf.flujo_id = f.id AND hf.tipo_tramite_id = 2 AND hf.observaciones = 'ganadora') as tiene_ganadora"),
                DB::raw("'cotizacion' as origen")
            );

        // Aplicar busqueda
        if ($term !== '') {
            $like = '%' . $term . '%';
            if (is_numeric($term)) {
                $qA->where('p.id', (int) $term);
                $qB->where('o.id', (int) $term);
            } else {
                $qA->where(function ($s) use ($like) {
                    $s->where('c.nombre', 'LIKE', $like)->orWhere('c.rtn', 'LIKE', $like);
                });
                $qB->where(function ($s) use ($like) {
                    $s->where('o.nombre_cliente', 'LIKE', $like)->orWhere('o.RTN', 'LIKE', $like);
                });
            }
        }

        $colMap = [
            'flujo_id'      => 'flujo_id',
            'cliente'       => 'cliente',
            'total_ofertas' => 'total_ofertas',
            'estado_flujo'  => 'estado_flujo',
            'created_at'    => 'created_at',
        ];
        $orderCol = $colMap[$col] ?? 'created_at';

        $this->ofertas = DB::table(DB::raw("({$qA->toSql()} UNION ALL {$qB->toSql()}) as combined"))
            ->mergeBindings($qA)
            ->mergeBindings($qB)
            ->orderBy($orderCol, $dir)
            ->get()
            ->toArray();
    }

    // ACCIONES

    public function marcarGanadora(int $flujoId, int $ofertaId): void
    {
        DB::beginTransaction();
        try {
            // Verificar si la revisión de inventario está activa
            $configRevision = DB::table('configuracion_revision_inventario')->first();
            $revisionActiva = $configRevision && (bool) $configRevision->activo;

            $nuevoTipo = $revisionActiva ? 9 : 4;

            DB::table('flujo')->where('id', $flujoId)
                ->update(['tipo_tramite_id' => $nuevoTipo, 'updated_by' => Auth::id(), 'updated_at' => now()]);

            DB::table('historico_flujo')
                ->where('flujo_id', $flujoId)
                ->where('tipo_tramite_id', 2)
                ->where('tramite_id', $ofertaId)
                ->update(['observaciones' => 'ganadora', 'updated_by' => Auth::id(), 'updated_at' => now()]);

            if ($revisionActiva) {
                DB::table('historico_flujo')->insert([
                    'flujo_id'        => $flujoId,
                    'tipo_tramite_id' => 9,
                    'tramite_id'      => $ofertaId,
                    'estado_id'       => 5,
                    'observaciones'   => 'En Revisión de Inventario. Oferta #' . $ofertaId,
                    'created_by'      => Auth::id(),
                    'updated_by'      => Auth::id(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            DB::commit();
            $this->mensajeExito = 'Oferta #' . $ofertaId . ($revisionActiva ? ' enviada a Revisión de Inventario.' : ' marcada como ganadora.');
            $this->mensajeError = '';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error: ' . $e->getMessage();
            $this->mensajeExito = '';
        }
        $this->cargar();
    }

    public function anularGanadora(int $flujoId, int $ofertaId): void
    {
        DB::beginTransaction();
        try {
            DB::table('flujo')->where('id', $flujoId)
                ->update(['tipo_tramite_id' => 2, 'updated_by' => Auth::id(), 'updated_at' => now()]);

            DB::table('historico_flujo')
                ->where('flujo_id', $flujoId)
                ->where('tipo_tramite_id', 2)
                ->where('tramite_id', $ofertaId)
                ->update(['observaciones' => null, 'updated_by' => Auth::id(), 'updated_at' => now()]);

            DB::commit();
            $this->mensajeExito = 'Ganadora anulada. Flujo revertido a Ofertas.';
            $this->mensajeError = '';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error: ' . $e->getMessage();
            $this->mensajeExito = '';
        }
        $this->cargar();
    }

    /** Abre el modal para un flujo originado desde un pedido (pasa el pedido_id) */
    public function abrirModalPedido(int $pedidoId): void
    {
        $this->emit('abrirFlujoPedido', $pedidoId);
    }

    /** Abre el modal para un flujo originado desde una cotización sin pedido (pasa el flujo_id) */
    public function abrirModalCotizacion(int $flujoId): void
    {
        $this->emit('abrirFlujoCotizacion', $flujoId);
    }

    public function nuevaOfertaSinPedido(): void
    {
        $this->redirect('/proforma/cotizacion/2?from=flujo');
    }

    // RENDER

    public function render()
    {
        return view('livewire.flujo.listar-pedidos-para-ofertar');
    }
}
