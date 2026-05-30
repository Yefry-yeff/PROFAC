<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Lista cotizaciones (ofertas) desde historico_flujo + cotizacion.
 * - Activas   : hf.observaciones NOT IN ('ganadora', 'Anulado:…', 'QuitadaGanadora:…')
 * - Ganadoras : hf.observaciones = 'ganadora'
 * - Historial : hf.observaciones LIKE 'Anulado:%' OR 'QuitadaGanadora:%'
 */
class ListarOfertasParaPrefactura extends Component
{
    public $busqueda         = '';
    public $busquedaGanadora = '';
    public $busquedaHist     = '';

    public $ofertas   = [];
    public $ganadoras = [];
    public $historial = [];

    public $confirmandoId = null;
    public $mensajeExito  = null;

    protected $listeners = ['flujoCerrado' => 'cargar', 'pedidoActualizado' => 'cargar'];

    

    public function mount()
    {
        $this->cargar();
    }

    public function updatedBusqueda()        { $this->cargarActivas(); }
    public function updatedBusquedaGanadora(){ $this->cargarGanadoras(); }
    public function updatedBusquedaHist()    { $this->cargarHistorial(); }

    public function cargar()
    {
        $this->cargarActivas();
        $this->cargarGanadoras();
        $this->cargarHistorial();
    }

    // ── Base query (cotizacion + historico_flujo) ─────────────────────────

    private function queryBase()
    {
        return DB::table('historico_flujo as hf')
            ->join('cotizacion as c', 'c.id', '=', 'hf.tramite_id')
            ->join('flujo as f', 'f.id', '=', 'hf.flujo_id')
            ->leftJoin('users as u', 'u.id', '=', 'c.users_id')
            ->where('hf.tipo_tramite_id', 2)   // 2 = Ofertas
            ->select(
                'hf.id as hf_id',
                'c.id as cotizacion_id',
                'hf.flujo_id',
                'hf.observaciones as estado_oferta',
                'c.nombre_cliente',
                'c.RTN',
                DB::raw('FORMAT(c.total, 2) as total'),
                'c.fecha_emision',
                'c.created_at',
                'u.name as registrado_por',
                DB::raw('(SELECT id FROM pedido WHERE id = CAST(f.identificacion AS UNSIGNED) LIMIT 1) as pedido_id'),
                DB::raw('(SELECT COUNT(*) FROM cotizacion_has_producto chp WHERE chp.cotizacion_id = c.id) as total_productos')
            )
            ->orderByDesc('hf.id');
    }

    private function applySearch($q, string $term)
    {
        if ($term === '') return $q;
        if (is_numeric($term)) {
            return $q->where('c.id', (int) $term);
        }
        $like = '%' . $term . '%';
        return $q->where(function ($sub) use ($like) {
            $sub->where('c.nombre_cliente', 'LIKE', $like)
                ->orWhere('c.RTN',          'LIKE', $like);
        });
    }

    public function cargarActivas()
    {
        $q = $this->queryBase()
            ->where('hf.observaciones', 'NOT LIKE', 'Anulado:%')
            ->where('hf.observaciones', 'NOT LIKE', 'QuitadaGanadora:%')
            ->where(DB::raw('IFNULL(hf.observaciones,"")'), '!=', 'ganadora');

        $this->ofertas = $this->applySearch($q, trim($this->busqueda))
            ->limit(50)->get()->toArray();
    }

    public function cargarGanadoras()
    {
        $q = $this->queryBase()
            ->where('hf.observaciones', 'ganadora');

        $this->ganadoras = $this->applySearch($q, trim($this->busquedaGanadora))
            ->limit(50)->get()->toArray();
    }

    public function cargarHistorial()
    {
        // Historial = anuladas + quitadas de ganadora
        $q = $this->queryBase()
            ->where(function ($sub) {
                $sub->where('hf.observaciones', 'LIKE', 'Anulado:%')
                    ->orWhere('hf.observaciones', 'LIKE', 'QuitadaGanadora:%');
            });

        $this->historial = $this->applySearch($q, trim($this->busquedaHist))
            ->limit(100)->get()->toArray();
    }

    // ── Abrir flujo desde una cotización ─────────────────────────────────

    public function verFlujoPorCotizacion(int $cotizacionId)
    {
        // Busca el flujo al que pertenece esta cotización
        $hf = DB::table('historico_flujo')
            ->where('tramite_id', $cotizacionId)
            ->where('tipo_tramite_id', 2)
            ->first();

        if (!$hf) return;

        $flujo = DB::table('flujo')->where('id', $hf->flujo_id)->first();
        if (!$flujo) return;

        // Si identificacion es numérico y ≤ cotizacion_id range, es un pedido_id
        // Si no tiene pedido asociado, abrir desde flujo directamente
        $identificacion = $flujo->identificacion;

        // Verificar si existe un pedido con ese id
        $esPedido = is_numeric($identificacion) &&
            DB::table('pedido')->where('id', (int) $identificacion)->exists();

        if ($esPedido) {
            $this->emit('abrirFlujoPedido', (int) $identificacion, 'ofertas');
        } else {
            // Flujo de cotización sin pedido
            $this->emit('abrirFlujoCotizacion', (int) $hf->flujo_id);
        }
    }

    // ── Confirmación ganadora (marcar desde esta pantalla) ────────────────

    public function confirmarAprobar(int $hfId)
    {
        $this->confirmandoId = $hfId;
        $this->mensajeExito  = null;
    }

    public function cancelarConfirmacion()
    {
        $this->confirmandoId = null;
    }

    public function aprobarOferta(int $hfId)
    {
        $hf = DB::table('historico_flujo')->where('id', $hfId)->first();
        if (!$hf) return;

        // Quitar ganadora previa del mismo flujo
        DB::table('historico_flujo')
            ->where('flujo_id', $hf->flujo_id)
            ->where('tipo_tramite_id', 2)
            ->where('observaciones', 'ganadora')
            ->update(['observaciones' => null, 'updated_at' => now()]);

        // Marcar esta como ganadora
        DB::table('historico_flujo')
            ->where('id', $hfId)
            ->update(['observaciones' => 'ganadora', 'updated_at' => now()]);

        // Auditoría
        DB::table('cotizacion_estado')->insert([
            'cotizacion_id' => $hf->tramite_id,
            'flujo_id'      => $hf->flujo_id,
            'ganadora'      => 1,
            'comentario'    => 'Aprobada como prefactura desde pantalla de prefactura',
            'estado_id'     => 1,
            'created_by'    => Auth::id(),
            'updated_by'    => Auth::id(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $this->confirmandoId = null;
        $this->mensajeExito  = 'Cotización #' . $hf->tramite_id . ' aprobada como prefactura.';
        $this->cargar();
    }

    public function render()
    {
        return view('livewire.flujo.listar-ofertas-para-prefactura');
    }
}
