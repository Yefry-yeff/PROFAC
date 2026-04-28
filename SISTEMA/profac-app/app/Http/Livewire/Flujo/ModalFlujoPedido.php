<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Modal reutilizable "Flujo del Pedido".
 * Incluir con @livewire('flujo.modal-flujo-pedido') en cualquier vista.
 * Para abrirlo emitir: $this->emit('abrirFlujoPedido', $pedidoId)
 */
class ModalFlujoPedido extends Component
{
    // ── Estado del modal ──────────────────────────────────────────────────
    public $showModal       = false;
    public $pedidoData      = null;   // array del pedido + cliente + conteos
    public $pedidoDetalles  = [];     // productos del pedido
    public $flujoId         = null;   // flujo.id del pedido
    public $flujoTipos      = [];     // tipo_tramite_id[] presentes en historico_flujo

    // ── Paso activo en el stepper ─────────────────────────────────────────
    public $pasoActivo      = 'pedido'; // 'pedido'|'ofertas'|'prefactura'|'factura'|'entrega'|'cobro'

    // ── Gestión de ofertas ────────────────────────────────────────────────
    public $ofertasPedido       = [];
    public $ofertaSeleccionada  = null;
    public $confirmAccionOferta = null;  // null|'ganadora'|'anular_oferta'|'duplicar_oferta'
    public $motivoAnulOferta    = '';

    // ── Acciones sobre el pedido ──────────────────────────────────────────
    public $confirmAccion    = null;  // null|'anular'|'duplicar'
    public $motivoAnulacion  = '';

    // ── Mensajes ──────────────────────────────────────────────────────────
    public $mensajeExito = '';
    public $mensajeError = '';

    // ── Listeners ─────────────────────────────────────────────────────────
    protected $listeners = ['abrirFlujoPedido' => 'abrir'];

    // ─────────────────────────────────────────────────────────────────────
    // ABRIR / CERRAR
    // ─────────────────────────────────────────────────────────────────────

    public function abrir(int $pedidoId, string $pasoInicial = 'pedido'): void
    {
        $pedido = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.users_id')
            ->select(
                'p.id', 'p.estado', 'p.observaciones', 'p.created_at',
                'c.nombre as cliente', 'c.rtn', 'c.id as cliente_id',
                'u.name as registrado_por',
                DB::raw("(SELECT COUNT(*) FROM historico_flujo hf
                           INNER JOIN flujo f ON f.id = hf.flujo_id
                           WHERE f.identificacion = CAST(p.id AS CHAR)
                             AND f.tipo_tramite_id = 1
                             AND hf.tipo_tramite_id = 2) as total_ofertas"),
                DB::raw("(SELECT COUNT(*) FROM historico_flujo hf
                           INNER JOIN flujo f ON f.id = hf.flujo_id
                           WHERE f.identificacion = CAST(p.id AS CHAR)
                             AND f.tipo_tramite_id = 1
                             AND hf.tipo_tramite_id = 2
                             AND hf.observaciones = 'ganadora') as has_ganadora")
            )
            ->where('p.id', $pedidoId)
            ->first();

        if (!$pedido) return;

        $this->pedidoData     = (array) $pedido;

        $this->pedidoDetalles = DB::table('pedido_detalle')
            ->where('pedido_id', $pedidoId)
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();

        $this->flujoId = DB::table('flujo')
            ->where('identificacion', (string) $pedidoId)
            ->where('tipo_tramite_id', 1)
            ->value('id');

        $this->flujoTipos = $this->flujoId
            ? DB::table('historico_flujo')
                ->where('flujo_id', $this->flujoId)
                ->pluck('tipo_tramite_id')
                ->unique()
                ->values()
                ->toArray()
            : [];

        $this->cargarOfertasPedido();

        // Resetear estado
        $this->pasoActivo           = $pasoInicial;
        $this->ofertaSeleccionada   = null;
        $this->confirmAccion        = null;
        $this->confirmAccionOferta  = null;
        $this->motivoAnulacion      = '';
        $this->motivoAnulOferta     = '';
        $this->mensajeExito         = '';
        $this->mensajeError         = '';
        $this->showModal            = true;
    }

    /** Recarga los datos del pedido preservando el paso activo */
    private function recargar(): void
    {
        if ($this->pedidoData) {
            $pasoAnterior = $this->pasoActivo;
            $this->abrir((int) $this->pedidoData['id']);
            $this->pasoActivo = $pasoAnterior;
        }
    }

    public function cerrar(): void
    {
        $this->emit('pedidoActualizado');
        $this->showModal            = false;
        $this->pedidoData           = null;
        $this->pedidoDetalles       = [];
        $this->flujoId              = null;
        $this->flujoTipos           = [];
        $this->pasoActivo           = 'pedido';
        $this->ofertasPedido        = [];
        $this->ofertaSeleccionada   = null;
        $this->confirmAccion        = null;
        $this->confirmAccionOferta  = null;
        $this->motivoAnulacion      = '';
        $this->motivoAnulOferta     = '';
        $this->mensajeExito         = '';
        $this->mensajeError         = '';
    }

    // ─────────────────────────────────────────────────────────────────────
    // NAVEGACIÓN DE PASOS
    // ─────────────────────────────────────────────────────────────────────

    public function seleccionarPaso(string $paso): void
    {
        $this->pasoActivo           = $paso;
        $this->ofertaSeleccionada   = null;
        $this->confirmAccion        = null;
        $this->confirmAccionOferta  = null;
        $this->motivoAnulacion      = '';
        $this->motivoAnulOferta     = '';
        $this->mensajeExito         = '';
        $this->mensajeError         = '';

        if ($paso === 'ofertas') {
            $this->cargarOfertasPedido();
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // ACCIONES SOBRE EL PEDIDO
    // ─────────────────────────────────────────────────────────────────────

    public function confirmarAccion(string $accion): void
    {
        $this->confirmAccion    = $accion;
        $this->motivoAnulacion  = '';
        $this->mensajeError     = '';
    }

    public function cancelarConfirmacion(): void
    {
        $this->confirmAccion    = null;
        $this->motivoAnulacion  = '';
        $this->mensajeError     = '';
    }

    public function anularPedido(): void
    {
        if (!$this->pedidoData) return;

        $motivo = trim($this->motivoAnulacion);
        if ($motivo === '') {
            $this->mensajeError = 'Debe indicar el motivo de anulación.';
            return;
        }

        $pedidoId = (int) $this->pedidoData['id'];

        DB::beginTransaction();
        try {
            DB::table('pedido')
                ->where('id', $pedidoId)
                ->update(['estado' => 'cancelado', 'updated_at' => now()]);

            $canceladoId = DB::table('estado_venta')
                ->where('descripcion', 'cancelado')
                ->value('id');

            $hf = DB::table('historico_flujo')
                ->where('tipo_tramite_id', 1)
                ->where('tramite_id', $pedidoId)
                ->first();

            if ($hf) {
                DB::table('historico_flujo')
                    ->where('id', $hf->id)
                    ->update([
                        'estado_id'     => $canceladoId,
                        'observaciones' => 'Anulado: ' . $motivo,
                        'updated_by'    => Auth::id(),
                        'updated_at'    => now(),
                    ]);
                DB::table('flujo')
                    ->where('id', $hf->flujo_id)
                    ->update([
                        'estado_id'  => $canceladoId,
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();
            $this->emit('pedidoActualizado');
            $this->confirmAccion = null;
            $this->recargar();
            $this->mensajeExito = 'Pedido #' . $pedidoId . ' anulado correctamente.';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error al anular: ' . $e->getMessage();
        }
    }

    public function duplicarPedido(): void
    {
        if (!$this->pedidoData) return;
        $pedidoId = (int) $this->pedidoData['id'];

        $productos = DB::table('pedido_detalle')
            ->where('pedido_id', $pedidoId)
            ->get(['nombre_producto', 'cantidad'])
            ->map(fn($r) => [
                'nombre_producto' => $r->nombre_producto,
                'cantidad'        => $r->cantidad,
            ])
            ->toArray();

        $param = base64_encode(json_encode($productos));
        $url   = route('flujo.pedido') . '?productos=' . urlencode($param);
        $this->dispatchBrowserEvent('abrir-nueva-pestana', ['url' => $url]);
        $this->cerrar();
    }

    // ─────────────────────────────────────────────────────────────────────
    // GESTIÓN DE OFERTAS
    // ─────────────────────────────────────────────────────────────────────

    public function cargarOfertasPedido(): void
    {
        if (!$this->flujoId) {
            $this->ofertasPedido = [];
            return;
        }

        $this->ofertasPedido = DB::table('historico_flujo as hf')
            ->join('cotizacion as c', 'c.id', '=', 'hf.tramite_id')
            ->where('hf.flujo_id', $this->flujoId)
            ->where('hf.tipo_tramite_id', 2)
            ->select(
                'hf.id as hf_id',
                'hf.tramite_id as cotizacion_id',
                'hf.observaciones as hf_observaciones',
                'hf.created_at as hf_fecha',
                'c.nombre_cliente',
                'c.total',
                'c.cliente_id'
            )
            ->orderByDesc('hf.id')
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();
    }

    public function verOferta(int $cotizacionId): void
    {
        $row = DB::table('cotizacion as c')
            ->leftJoin('historico_flujo as hf', function ($j) {
                $j->on('hf.tramite_id', '=', 'c.id')
                  ->where('hf.tipo_tramite_id', 2);
            })
            ->where('c.id', $cotizacionId)
            ->select(
                'c.id', 'c.nombre_cliente', 'c.RTN', 'c.total', 'c.isv',
                'c.sub_total', 'c.porc_descuento', 'c.monto_descuento',
                'c.fecha_emision', 'c.created_at', 'c.cliente_id',
                'hf.observaciones as hf_observaciones'
            )
            ->first();

        if (!$row) return;

        $productos = DB::table('cotizacion_has_producto')
            ->where('cotizacion_id', $cotizacionId)
            ->select('nombre_producto', 'cantidad', 'precio_unidad', 'total')
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();

        $this->ofertaSeleccionada  = array_merge((array) $row, ['productos' => $productos]);
        $this->confirmAccionOferta = null;
        $this->motivoAnulOferta    = '';
        $this->mensajeExito        = '';
        $this->mensajeError        = '';
    }

    public function cerrarOferta(): void
    {
        $this->ofertaSeleccionada  = null;
        $this->confirmAccionOferta = null;
        $this->motivoAnulOferta    = '';
        $this->mensajeExito        = '';
        $this->mensajeError        = '';
    }

    public function confirmarAccionOferta(string $accion): void
    {
        $this->confirmAccionOferta = $accion;
        $this->motivoAnulOferta    = '';
        $this->mensajeError        = '';
    }

    public function cancelarConfirmOferta(): void
    {
        $this->confirmAccionOferta = null;
        $this->motivoAnulOferta    = '';
        $this->mensajeError        = '';
    }

    public function ganadoraOferta(): void
    {
        if (!$this->ofertaSeleccionada || !$this->flujoId) return;

        $cotizacionId = (int) $this->ofertaSeleccionada['id'];

        $hf = DB::table('historico_flujo')
            ->where('tramite_id', $cotizacionId)
            ->where('tipo_tramite_id', 2)
            ->where('flujo_id', $this->flujoId)
            ->first();

        if (!$hf) {
            $this->mensajeError = 'Registro de oferta no encontrado.';
            return;
        }

        // Quitar ganadora anterior si existe
        DB::table('historico_flujo')
            ->where('flujo_id', $this->flujoId)
            ->where('tipo_tramite_id', 2)
            ->where('observaciones', 'ganadora')
            ->update(['observaciones' => null, 'updated_at' => now()]);

        // Marcar la nueva ganadora
        DB::table('historico_flujo')
            ->where('id', $hf->id)
            ->update(['observaciones' => 'ganadora', 'updated_at' => now()]);

        $this->mensajeExito = 'Oferta #' . $cotizacionId . ' marcada como ganadora.';
        $this->emit('pedidoActualizado');
        $this->recargar();
        $this->mensajeExito = 'Oferta #' . $cotizacionId . ' marcada como ganadora.';
    }

    public function quitarGanadora(): void
    {
        if (!$this->ofertaSeleccionada || !$this->flujoId) return;

        $motivo = trim($this->motivoAnulOferta);
        if ($motivo === '') {
            $this->mensajeError = 'Debe indicar el motivo para quitar la ganadora.';
            return;
        }

        $cotizacionId = (int) $this->ofertaSeleccionada['id'];

        DB::table('historico_flujo')
            ->where('flujo_id', $this->flujoId)
            ->where('tipo_tramite_id', 2)
            ->where('tramite_id', $cotizacionId)
            ->update(['observaciones' => 'QuitadaGanadora: ' . $motivo, 'updated_at' => now()]);

        $this->mensajeExito = 'Oferta #' . $cotizacionId . ' ya no es ganadora.';
        $this->emit('pedidoActualizado');
        $this->recargar();
        $this->mensajeExito = 'Oferta #' . $cotizacionId . ' ya no es ganadora.';
    }

    public function anularOferta(): void
    {
        if (!$this->ofertaSeleccionada) return;

        $motivo = trim($this->motivoAnulOferta);
        if ($motivo === '') {
            $this->mensajeError = 'Debe indicar el motivo de anulación.';
            return;
        }

        $cotizacionId = (int) $this->ofertaSeleccionada['id'];

        DB::table('historico_flujo')
            ->where('tramite_id', $cotizacionId)
            ->where('tipo_tramite_id', 2)
            ->update([
                'observaciones' => 'Anulado: ' . $motivo,
                'updated_by'    => Auth::id(),
                'updated_at'    => now(),
            ]);

        $this->mensajeExito = 'Oferta #' . $cotizacionId . ' anulada correctamente.';
        $this->emit('pedidoActualizado');
        $this->recargar();
        $this->mensajeExito = 'Oferta #' . $cotizacionId . ' anulada correctamente.';
    }

    public function duplicarOferta(bool $mismoCliente): void
    {
        if (!$this->pedidoData) return;
        $pedidoId = (int) $this->pedidoData['id'];
        $url = $mismoCliente
            ? '/proforma/cotizacion/2?from=flujo&pedidoId=' . $pedidoId
            : '/proforma/cotizacion/2?from=flujo';
        $this->dispatchBrowserEvent('abrir-nueva-pestana', ['url' => $url]);
        $this->confirmAccionOferta = null;
    }

    public function nuevaOferta(): void
    {
        if (!$this->pedidoData) return;
        $pedidoId = (int) $this->pedidoData['id'];
        $this->redirect('/proforma/cotizacion/2?from=flujo&pedidoId=' . $pedidoId);
    }

    // ─────────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.flujo.modal-flujo-pedido');
    }
}
