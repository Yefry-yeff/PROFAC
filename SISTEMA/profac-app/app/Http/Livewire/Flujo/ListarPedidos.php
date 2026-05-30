<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class ListarPedidos extends Component
{
    // ── Filtros ────────────────────────────────────────────────────────────
    public $busquedaCliente = '';
    public $filtroEstado    = '';
    public $filtroFecha     = '';
    public $filtroNumero    = '';   // filtro por nº de documento

    // ── Control de acceso ─────────────────────────────────────────────────
    public $esAdmin = false;

    
    // ── Paginación simple ──────────────────────────────────────────────────
    public $pagina       = 1;
    public $porPagina    = 15;
    public $totalPedidos = 0;
    public $totalPaginas = 0;

    // ── Confirmación de cancelación ──────────────────────────────────────────
    public $pedidoAnularId   = null;
    public $showModalAnular  = false;

    // ── Modal de flujo del pedido ──────────────────────────────────────────
    public $showModalFlujo  = false;
    public $pedidoFlujoId   = null;
    public $pedidoFlujoData = null;

    // ── Modal seleccionar oferta ganadora ──────────────────────────────────
    public $showModalGanadora = false;
    public $ofertaGanadoraId  = null;
    public $pedidoGanadoraId  = null;

    // ── Reasignación de flujo (solo admin) ──────────────────────────────────
    public $showModalReasignar  = false;
    public $pedidoReasignarId   = null;
    public $busquedaUsuario     = '';
    public $usuariosDisponibles = [];
    public $usuarioReasignarId  = null;

    // ── Mensaje ────────────────────────────────────────────────────────────
    public $mensajeExito = '';
    public $mensajeError = '';

    // ── Ciclo de vida ──────────────────────────────────────────────────────
    public function mount()
    {
        $this->esAdmin = Auth::user()->rol_id === 1;
    }

    // ── Actualización de filtros → volver a página 1 ──────────────────────
    public function updatedBusquedaCliente() { $this->pagina = 1; }
    public function updatedFiltroEstado()    { $this->pagina = 1; }
    public function updatedFiltroFecha()     { $this->pagina = 1; }
    public function updatedFiltroNumero()    { $this->pagina = 1; }

    public function updatedBusquedaUsuario()
    {
        $term = trim($this->busquedaUsuario);
        if (strlen($term) < 2) {
            $this->usuariosDisponibles = [];
            return;
        }
        $this->usuariosDisponibles = DB::table('users as u')
            ->join('rol as r', 'r.id', '=', 'u.rol_id')
            ->select('u.id', 'u.name', 'r.nombre as rol')
            ->where('u.name', 'LIKE', '%' . $term . '%')
            ->orderBy('u.name')
            ->limit(10)
            ->get()
            ->toArray();
    }

    // ── Listeners ─────────────────────────────────────────────────────────
    protected function getListeners()
    {
        return [
            'pedidoGuardado'    => '$refresh',
            'cerrarFlujoDesdeJS' => 'cerrarFlujo',
        ];
    }

    // ── Obtener pedidos filtrados ──────────────────────────────────────────
    private function query()
    {
        $q = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->join('users as u', 'u.id', '=', 'p.users_id')
            ->leftJoin('historico_flujo as hf', function ($join) {
                $join->on('hf.tramite_id', '=', 'p.id')
                     ->where('hf.tramite_tipo', '=', 'pedido');
            })
            ->leftJoin('flujo as f', 'f.id', '=', 'hf.flujo_id')
            ->leftJoin('tipos_estatus as te', 'te.id', '=', 'f.estatus_id')
            ->select(
                'p.id',
                'c.nombre as cliente',
                'c.rtn',
                'p.estado',
                'p.users_id',
                'u.name as registrado_por',
                'p.observaciones',
                'p.created_at',
                'p.updated_at as pedido_updated_at',
                'f.id as flujo_id',
                'te.nombre as estatus_flujo',
                'hf.estado as estado_flujo',
                DB::raw('(SELECT COUNT(*) FROM pedido_detalle pd WHERE pd.pedido_id = p.id) as total_productos'),
                DB::raw('(SELECT COUNT(*) FROM oferta o WHERE o.pedido_id = p.id) as has_ofertas'),
                DB::raw('(SELECT COUNT(*) FROM oferta o WHERE o.pedido_id = p.id AND o.estado = \'ganadora\') as has_ganadora')
            )
            ->orderByDesc('p.created_at');

        // Solo ver propios registros si no es administrador
        if (!$this->esAdmin) {
            $q->where('p.users_id', Auth::id());
        }

        // Filtro por número de documento
        if (trim($this->filtroNumero) !== '') {
            $q->where('p.id', (int) $this->filtroNumero);
        }

        // Filtro por cliente
        if (strlen(trim($this->busquedaCliente)) >= 2) {
            $term = '%' . trim($this->busquedaCliente) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('c.nombre', 'LIKE', $term)
                    ->orWhere('c.rtn', 'LIKE', $term);
            });
        }

        // Filtro por estado
        if ($this->filtroEstado !== '') {
            if ($this->filtroEstado === 'sin_flujo') {
                $q->whereNull('f.id');
            } else {
                $q->where('te.nombre', $this->filtroEstado);
            }
        }

        // Filtro por fecha
        if ($this->filtroFecha !== '') {
            $q->whereDate('p.created_at', $this->filtroFecha);
        }

        return $q;
    }

    public function getTotalPaginasProperty(): int
    {
        return (int) ceil($this->totalPedidos / $this->porPagina);
    }

    // ── Paginación ─────────────────────────────────────────────────────────
    public function paginaAnterior() { if ($this->pagina > 1) $this->pagina--; }
    public function paginaSiguiente()
    {
        if ($this->pagina < $this->totalPaginas) $this->pagina++;
    }

    public function irPagina(int $p)
    {
        if ($p >= 1 && $p <= $this->totalPaginas) {
            $this->pagina = $p;
        }
    }

    // ── Limpiar filtros ────────────────────────────────────────────────────
    public function limpiarFiltros()
    {
        $this->busquedaCliente = '';
        $this->filtroEstado    = '';
        $this->filtroFecha     = '';
        $this->filtroNumero    = '';
        $this->pagina          = 1;
    }

    // ── Anular pedido ──────────────────────────────────────────────────────
    public function confirmarAnular(int $id)
    {
        $this->pedidoAnularId  = $id;
        $this->showModalAnular = true;
    }

    public function cancelarAnular()
    {
        $this->pedidoAnularId  = null;
        $this->showModalAnular = false;
    }

    public function anularPedido()
    {
        if (!$this->pedidoAnularId) return;

        DB::beginTransaction();
        try {
            DB::table('pedido')
                ->where('id', $this->pedidoAnularId)
                ->update(['estado' => 'cancelado', 'updated_at' => now()]);

            // Actualizar el flujo si existe
            $hf = DB::table('historico_flujo')
                ->where('tramite_tipo', 'pedido')
                ->where('tramite_id', $this->pedidoAnularId)
                ->first();

            if ($hf) {
                // Estatus 'cancelado' — buscamos o usamos el estado directamente en historico
                DB::table('historico_flujo')
                    ->where('id', $hf->id)
                    ->update(['estado' => 'cancelado', 'updated_by' => Auth::id(), 'updated_at' => now()]);

                // Buscar estatus "cancelado" en tipos_estatus o marcar flujo como inactivo
                DB::table('flujo')
                    ->where('id', $hf->flujo_id)
                    ->update(['estado' => 'cancelado', 'updated_by' => Auth::id(), 'updated_at' => now()]);
            }

            DB::commit();
            $this->pedidoAnularId  = null;
            $this->showModalAnular = false;
            $this->mensajeExito    = 'Pedido cancelado correctamente.';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error al cancelar: ' . $e->getMessage();
        }
    }

    // ── Modal flujo del pedido ─────────────────────────────────────────────
    public function verFlujo(int $id)
    {
        $pedido = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->join('users as u', 'u.id', '=', 'p.users_id')
            ->leftJoin('historico_flujo as hf', function ($join) {
                $join->on('hf.tramite_id', '=', 'p.id')
                     ->where('hf.tramite_tipo', '=', 'pedido');
            })
            ->leftJoin('flujo as f', 'f.id', '=', 'hf.flujo_id')
            ->leftJoin('tipos_estatus as te', 'te.id', '=', 'f.estatus_id')
            ->select(
                'p.id', 'p.estado', 'p.sub_estado_entrega', 'p.observaciones', 'p.created_at', 'p.updated_at',
                'c.nombre as cliente',
                'u.name as registrado_por',
                'f.id as flujo_id',
                'te.nombre as estatus_flujo',
                'hf.estado as estado_flujo',
                'hf.observaciones as obs_flujo'
            )
            ->where('p.id', $id)
            ->first();

        if ($pedido) {
            $ofertas = DB::table('oferta')
                ->where('pedido_id', $id)
                ->select('id', 'nombre_cliente', 'total', 'created_at', 'estado')
                ->orderBy('id')
                ->limit(10)
                ->get();

            $this->pedidoFlujoId   = $id;
            $this->pedidoFlujoData = array_merge((array) $pedido, [
                'ofertas' => $ofertas->toArray(),
            ]);
            $this->showModalFlujo  = true;
        }
    }

    public function cerrarFlujo()
    {
        $this->showModalFlujo  = false;
        $this->pedidoFlujoId   = null;
        $this->pedidoFlujoData = null;
    }

    // ── Reasignación de flujo ──────────────────────────────────────────────
    public function abrirReasignar(int $pedidoId)
    {
        if (!$this->esAdmin) return;

        $this->pedidoReasignarId   = $pedidoId;
        $this->busquedaUsuario     = '';
        $this->usuariosDisponibles = [];
        $this->usuarioReasignarId  = null;
        $this->showModalReasignar  = true;
    }

    public function seleccionarUsuarioReasignar(int $userId)
    {
        $this->usuarioReasignarId = $userId;
    }

    public function cerrarReasignar()
    {
        $this->showModalReasignar  = false;
        $this->pedidoReasignarId   = null;
        $this->busquedaUsuario     = '';
        $this->usuariosDisponibles = [];
        $this->usuarioReasignarId  = null;
    }

    public function reasignarPedido()
    {
        if (!$this->esAdmin || !$this->pedidoReasignarId || !$this->usuarioReasignarId) return;

        DB::beginTransaction();
        try {
            // Actualizar propietario del pedido
            DB::table('pedido')
                ->where('id', $this->pedidoReasignarId)
                ->update(['users_id' => $this->usuarioReasignarId, 'updated_at' => now()]);

            // Actualizar auditoría en flujo si existe
            $hf = DB::table('historico_flujo')
                ->where('tramite_tipo', 'pedido')
                ->where('tramite_id', $this->pedidoReasignarId)
                ->first();

            if ($hf) {
                DB::table('flujo')
                    ->where('id', $hf->flujo_id)
                    ->update(['updated_by' => Auth::id(), 'updated_at' => now()]);
                DB::table('historico_flujo')
                    ->where('id', $hf->id)
                    ->update([
                        'observaciones' => 'Reasignado a usuario ID ' . $this->usuarioReasignarId . ' por administrador (ID ' . Auth::id() . ')',
                        'updated_by'    => Auth::id(),
                        'updated_at'    => now(),
                    ]);
            }

            DB::commit();

            $nuevoUsuario = DB::table('users')->where('id', $this->usuarioReasignarId)->value('name');
            $this->mensajeExito = 'Pedido #' . $this->pedidoReasignarId . ' reasignado a ' . $nuevoUsuario . ' correctamente.';
            $this->cerrarReasignar();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error al reasignar: ' . $e->getMessage();
        }
    }

    // ── Seleccionar oferta ganadora ────────────────────────────────────────
    public function confirmarGanadora(int $ofertaId, int $pedidoId)
    {
        $this->ofertaGanadoraId  = $ofertaId;
        $this->pedidoGanadoraId  = $pedidoId;
        $this->showModalGanadora = true;
    }

    public function cancelarSeleccionGanadora()
    {
        $this->ofertaGanadoraId  = null;
        $this->pedidoGanadoraId  = null;
        $this->showModalGanadora = false;
    }

    public function seleccionarGanadora()
    {
        if (!$this->ofertaGanadoraId || !$this->pedidoGanadoraId) return;

        DB::beginTransaction();
        try {
            // Cancelar todas las demás ofertas del pedido
            DB::table('oferta')
                ->where('pedido_id', $this->pedidoGanadoraId)
                ->where('id', '!=', $this->ofertaGanadoraId)
                ->update(['estado' => 'cancelada', 'updated_at' => now()]);

            // Marcar la seleccionada como ganadora
            DB::table('oferta')
                ->where('id', $this->ofertaGanadoraId)
                ->update(['estado' => 'ganadora', 'updated_at' => now()]);

            // Avanzar pedido a pre_factura
            DB::table('pedido')
                ->where('id', $this->pedidoGanadoraId)
                ->update(['estado' => 'pre_factura', 'updated_at' => now()]);

            // Avanzar flujo al estatus prefactura (id=4)
            $hf = DB::table('historico_flujo')
                ->where('tramite_tipo', 'pedido')
                ->where('tramite_id', $this->pedidoGanadoraId)
                ->first();

            if ($hf) {
                DB::table('flujo')
                    ->where('id', $hf->flujo_id)
                    ->update(['estatus_id' => 4, 'updated_by' => Auth::id(), 'updated_at' => now()]);
                DB::table('historico_flujo')
                    ->where('id', $hf->id)
                    ->update(['estado' => 'pre_factura', 'updated_by' => Auth::id(), 'updated_at' => now()]);
            }

            DB::commit();

            $this->showModalGanadora = false;
            $pedidoId = $this->pedidoGanadoraId;
            $this->ofertaGanadoraId  = null;
            $this->pedidoGanadoraId  = null;

            $this->verFlujo($pedidoId);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError      = 'Error al seleccionar la oferta ganadora.';
            $this->showModalGanadora = false;
        }
    }

    // ── Render ─────────────────────────────────────────────────────────────
    public function render()
    {
        $this->totalPedidos  = $this->query()->count();
        $this->totalPaginas  = (int) ceil($this->totalPedidos / $this->porPagina);
        $offset  = ($this->pagina - 1) * $this->porPagina;
        $pedidos = $this->query()->skip($offset)->take($this->porPagina)->get();

        return view('livewire.flujo.listar-pedidos', [
            'pedidos'      => $pedidos,
            'totalPaginas' => $this->totalPaginas,
        ]);
    }
}
