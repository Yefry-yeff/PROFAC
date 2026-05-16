<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ListarVentas extends Component
{
    // ── Filtros y sort (misma mecanica que listar-pedidos-para-ofertar) ──
    public $busquedaOfr  = '';
    public $filtroEstado = '';
    public $filtroFecha  = '';
    public $filtroNumero = '';
    public $sortColOfr   = 'created_at';
    public $sortDirOfr   = 'desc';

    // ── Control de acceso ─────────────────────────────────────────────────
    public $esAdmin = false;

    // ── Paginación y datos ────────────────────────────────────────────────
    public int $paginaOfr = 1;
    public int $perPage   = 5;
    public $registros     = [];

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
        $this->cargarRegistros();
    }

    // ── Actualización de filtros ──────────────────────────────────────────
    public function updatedBusquedaOfr() { $this->paginaOfr = 1; $this->cargarRegistros(); }
    public function updatedFiltroEstado() { $this->paginaOfr = 1; $this->cargarRegistros(); }
    public function updatedFiltroFecha() { $this->paginaOfr = 1; $this->cargarRegistros(); }
    public function updatedFiltroNumero() { $this->paginaOfr = 1; $this->cargarRegistros(); }

    public function sortByOfr(string $column): void
    {
        $allowed = [
            'flujo_id', 'documento_id', 'cliente',
            'estado_flujo', 'total_ofertas', 'created_at'
        ];

        if (!in_array($column, $allowed, true)) {
            return;
        }

        if ($this->sortColOfr === $column) {
            $this->sortDirOfr = $this->sortDirOfr === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColOfr = $column;
            $this->sortDirOfr = 'asc';
        }

        $this->paginaOfr = 1;
        $this->cargarRegistros();
    }

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

    public function ofrPrev(): void
    {
        if ($this->paginaOfr > 1) {
            $this->paginaOfr--;
        }
    }

    public function ofrNext(): void
    {
        $total = count($this->registros);
        $lastPage = max(1, (int) ceil($total / $this->perPage));
        if ($this->paginaOfr < $lastPage) {
            $this->paginaOfr++;
        }
    }

    private function cargarRegistros(): void
    {
        // Una fila por flujo — estado = tipo_tramite_id actual del flujo
        $q = DB::table('flujo as f')
            ->leftJoin('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
            ->leftJoin('pedido as p', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'p.id')
            ->leftJoin('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->leftJoin('cotizacion as co', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'co.id')
            ->select(
                'f.id as flujo_id',
                'f.created_at',
                'p.id as pedido_id',
                DB::raw("COALESCE(c.nombre, co.nombre_cliente, '—') as cliente"),
                DB::raw("COALESCE(c.rtn, co.RTN, '—') as rtn"),
                DB::raw("CASE
                    WHEN f.estado_id = 4 OR p.estado = 'cancelado' THEN 'cancelado'
                    ELSE COALESCE(tt.nombre, 'sin_flujo')
                 END as estado_flujo"),
                // Documento: factura→cai, prefactura→id, fallback→pedido id
                DB::raw("COALESCE(
                    (SELECT fa.cai
                     FROM historico_flujo hf3
                     INNER JOIN factura fa ON fa.id = hf3.tramite_id
                     WHERE hf3.flujo_id = f.id AND hf3.tipo_tramite_id = 3
                     ORDER BY hf3.id DESC LIMIT 1),
                    CAST((SELECT hf4.tramite_id
                          FROM historico_flujo hf4
                          WHERE hf4.flujo_id = f.id AND hf4.tipo_tramite_id = 4
                          ORDER BY hf4.id DESC LIMIT 1) AS CHAR),
                    CAST(p.id AS CHAR)
                ) as documento_display"),
                DB::raw('COALESCE((SELECT COUNT(*) FROM historico_flujo hf2 WHERE hf2.flujo_id = f.id AND hf2.tipo_tramite_id = 2), 0) as total_ofertas'),
                DB::raw("CASE WHEN p.id IS NULL THEN 'cotizacion' ELSE 'pedido' END as origen")
            );

        // Solo ver propios registros si no es administrador
        if (!$this->esAdmin) {
            $q->where(function ($sub) {
                $sub->where('p.users_id', Auth::id())
                    ->orWhere('co.users_id', Auth::id())
                    ->orWhere('f.created_by', Auth::id());
            });
        }

        // Filtro por número de documento (cualquier entrada del historial + CAI de factura)
        if (trim($this->filtroNumero) !== '') {
            $num = trim($this->filtroNumero);
            $q->where(function ($sub) use ($num) {
                $sub->whereExists(function ($q2) use ($num) {
                    $q2->select(DB::raw(1))
                       ->from('historico_flujo as hfx')
                       ->whereColumn('hfx.flujo_id', 'f.id')
                       ->where('hfx.tramite_id', (int) $num);
                })->orWhereExists(function ($q2) use ($num) {
                    $q2->select(DB::raw(1))
                       ->from('historico_flujo as hfx2')
                       ->join('factura as fx', 'fx.id', '=', 'hfx2.tramite_id')
                       ->whereColumn('hfx2.flujo_id', 'f.id')
                       ->where('hfx2.tipo_tramite_id', 3)
                       ->where('fx.cai', 'LIKE', '%' . $num . '%');
                });
            });
        }

        // Búsqueda principal
        $termRaw = trim($this->busquedaOfr);
        if ($termRaw !== '') {
            $term = '%' . $termRaw . '%';
            $q->where(function ($sub) use ($term, $termRaw) {
                $sub->where('c.nombre', 'LIKE', $term)
                    ->orWhere('co.nombre_cliente', 'LIKE', $term)
                    ->orWhere('c.rtn', 'LIKE', $term)
                    ->orWhere('co.RTN', 'LIKE', $term)
                    ->orWhere('f.id', 'LIKE', $term)
                    ->orWhereExists(function ($q2) use ($termRaw) {
                        $q2->select(DB::raw(1))
                           ->from('historico_flujo as hfx')
                           ->whereColumn('hfx.flujo_id', 'f.id')
                           ->where('hfx.tramite_id', 'LIKE', '%' . $termRaw . '%');
                    })
                    ->orWhereExists(function ($q2) use ($termRaw) {
                        $q2->select(DB::raw(1))
                           ->from('historico_flujo as hfx2')
                           ->join('factura as fx', 'fx.id', '=', 'hfx2.tramite_id')
                           ->whereColumn('hfx2.flujo_id', 'f.id')
                           ->where('hfx2.tipo_tramite_id', 3)
                           ->where('fx.cai', 'LIKE', '%' . $termRaw . '%');
                    });
            });
        }

        // Filtro por estado
        if ($this->filtroEstado !== '') {
            if ($this->filtroEstado === 'sin_flujo') {
                $q->whereNull('tt.id');
            } elseif ($this->filtroEstado === 'cancelado') {
                $q->where(function ($sub) {
                    $sub->where('f.estado_id', 4)
                        ->orWhere('p.estado', 'cancelado');
                });
            } else {
                $q->where('tt.nombre', $this->filtroEstado)
                  ->where(function ($sub) {
                      $sub->where('f.estado_id', '!=', 4)->orWhereNull('f.estado_id');
                  })
                  ->where(function ($sub) {
                      $sub->whereNull('p.estado')->orWhere('p.estado', '!=', 'cancelado');
                  });
            }
        }

        // Filtro por fecha
        if ($this->filtroFecha !== '') {
            $q->whereDate('f.created_at', $this->filtroFecha);
        }

        $dir = strtolower($this->sortDirOfr) === 'asc' ? 'asc' : 'desc';
        switch ($this->sortColOfr) {
            case 'flujo_id':
                $q->orderBy('f.id', $dir);
                break;
            case 'documento_id':
                $q->orderByRaw("documento_display {$dir}");
                break;
            case 'cliente':
                $q->orderByRaw("COALESCE(c.nombre, co.nombre_cliente, '') {$dir}");
                break;
            case 'estado_flujo':
                $q->orderByRaw("estado_flujo {$dir}");
                break;
            case 'total_ofertas':
                $q->orderByRaw("total_ofertas {$dir}");
                break;
            case 'created_at':
            default:
                $q->orderBy('f.created_at', $dir);
                break;
        }

        $this->registros = $q->get()->toArray();
    }

    // ── Limpiar filtros ────────────────────────────────────────────────────
    public function limpiarFiltros()
    {
        $this->busquedaOfr     = '';
        $this->filtroEstado    = '';
        $this->filtroFecha     = '';
        $this->filtroNumero    = '';
        $this->paginaOfr       = 1;
        $this->cargarRegistros();
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
                     ->where('hf.tipo_tramite_id', '=', 'pedido');
            })
            ->leftJoin('flujo as f', 'f.id', '=', 'hf.flujo_id')
            ->leftJoin('tipos_estatus as te', 'te.id', '=', 'f.estado_id')
            ->select(
                'p.id', 'p.estado', 'p.sub_estado_entrega', 'p.observaciones', 'p.created_at', 'p.updated_at',
                'c.nombre as cliente',
                'u.name as registrado_por',
                'f.id as flujo_id',
                'te.nombre as estatus_flujo',
                'hf.estado_id as estado_flujo',
                'hf.observaciones as obs_flujo'
            )
            ->where('p.id', $id)
            ->first();

        if ($pedido) {
            $ofertas = DB::table('historico_flujo as hf')
                ->join('cotizacion as co', 'co.id', '=', 'hf.tramite_id')
                ->join('flujo as f2', 'f2.id', '=', 'hf.flujo_id')
                ->where('f2.identificacion', (string) $id)
                ->where('f2.tipo_flujo_id', 1)
                ->where('hf.tipo_tramite_id', 2)
                ->select(
                    'co.id', 'co.nombre_cliente', 'co.total', 'co.created_at',
                    DB::raw("IF(hf.observaciones = 'ganadora', 'ganadora', 'activa') as estado")
                )
                ->orderBy('co.id')
                ->limit(10)
                ->get();

            $this->pedidoFlujoId   = $id;
            $this->pedidoFlujoData = array_merge((array) $pedido, [
                'ofertas' => $ofertas->toArray(),
            ]);
            $this->showModalFlujo  = true;
        }
    }

    /** Abre el componente de flujo para origen pedido (igual a ofertar) */
    public function abrirModalPedido(int $pedidoId): void
    {
        $this->emit('abrirFlujoPedido', $pedidoId);
    }

    /** Abre el componente de flujo para origen cotizacion (igual a ofertar) */
    public function abrirModalCotizacion(int $flujoId): void
    {
        $this->emit('abrirFlujoCotizacion', $flujoId);
    }

    /**
     * Abre flujo desde fila de historial.
     * Prioriza modal-flujo-pedido; si no existe pedido asociado, abre flujo de cotizacion.
     */
    public function abrirFlujoDesdeRegistro(int $flujoId, int $pedidoId = 0): void
    {
        if ($pedidoId > 0) {
            $this->emit('abrirFlujoPedido', $pedidoId);
            return;
        }

        $identificacion = DB::table('flujo')->where('id', $flujoId)->value('identificacion');
        $pedidoDetectado = (int) DB::table('pedido')->where('id', (int) $identificacion)->value('id');

        if ($pedidoDetectado > 0) {
            $this->emit('abrirFlujoPedido', $pedidoDetectado);
            return;
        }

        $this->emit('abrirFlujoCotizacion', $flujoId);
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
        return view('livewire.flujo.listar-ventas', [
            'registros' => $this->registros,
        ]);
    }
}
