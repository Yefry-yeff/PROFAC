<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;
use App\Models\TipoFactura;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FacturacionUnificada extends Component
{
    public $tipoFacturaId;
    public $tipoFactura;
    public $tiposFactura;
    public $fromFlujo = false;

// ── Buscador de flujo ─────────────────────────────────────────────────
    public $busquedaFlujo      = '';
    public $flujoEncontrados   = [];
    public $flujoVinculado     = null;
    public $flujoVinculadoId   = null;   // flujo.id del flujo vinculado
    public $pedidoId           = null;   // pedido.id (null para flujos sin pedido)

    // ── Datos pre-cargados del pedido (solo modo oferta) ─────────────────
    public $clientePedido         = null;   // ['id','nombre','rtn','cliente_id']
    public $productosSugeridos    = [];     // [['nombre_pedido','cantidad','similares':[...]]]
    public $productosParaCarrito  = [];     // Productos del duplicado para auto-agregar al carrito

    // ── Vendedor actual ──────────────────────────────────────────────────
    public $vendedorDefault    = [];

    // ── Pedido preview (para modal de detalle) ────────────────────────────
    public $pedidoDetalle      = null;

    public function mount($codigo = null)
    {
        $this->fromFlujo = request()->get('from') === 'flujo';
        $this->tiposFactura = TipoFactura::activos()->get();

        if ($codigo) {
            $this->tipoFactura = TipoFactura::where('codigo', $codigo)->first();
        }

        if (!$this->tipoFactura) {
            $this->tipoFactura = TipoFactura::where('codigo', 'estatal')->first();
        }

        $this->tipoFacturaId = $this->tipoFactura->id ?? null;

        // Vendedor = usuario autenticado por defecto
        if (Auth::check()) {
            $this->vendedorDefault = [
                'id'   => Auth::id(),
                'name' => Auth::user()->name,
            ];
        }

        // Pre-seleccionar flujo si viene por query string (pedidoId)
        $pid = request()->get('pedidoId');
        if ($pid) {
            $flujoId = DB::table('flujo')
                ->where('identificacion', (string) $pid)
                ->where('tipo_flujo_id', 1)
                ->value('id');
            if ($flujoId) {
                $this->seleccionarFlujo((int) $flujoId);
            }
        }

        // Pre-seleccionar flujo si viene por flujoId directo (flujos sin pedido)
        $fid = request()->get('flujoId');
        if ($fid && !$pid) {
            $this->seleccionarFlujo((int) $fid);
        }

        // Cargar productos del duplicado para auto-agregar al carrito (cotizacionId)
        $cotizId = request()->get('cotizacionId');
        if ($cotizId) {
            $prods = DB::table('cotizacion_has_producto')
                ->where('cotizacion_id', (int) $cotizId)
                ->orderBy('indice')
                ->get([
                    'producto_id',
                    'nombre_producto',
                    'nombre_bodega',
                    'precio_unidad',
                    'cantidad',
                    'isv_producto',
                    'unidad_medida_venta_id',
                    'Bodega_id',
                    'seccion_id',
                    'precios_producto_carga_id',
                ])
                ->toArray();
            foreach ($prods as $p) {
                $this->productosParaCarrito[] = (array) $p;
                $this->productosSugeridos[] = [
                    'nombre_pedido' => $p->nombre_producto,
                    'cantidad'      => $p->cantidad,
                    'similares'     => $this->buscarSimilares($p->nombre_producto),
                ];
            }
        }
    }

    public function updatedBusquedaFlujo()
    {
        $term = trim($this->busquedaFlujo);
        if (strlen($term) < 2) {
            $this->flujoEncontrados = [];
            return;
        }

        $esNum = is_numeric($term);
        $num   = $esNum ? (int) $term : 0;
        $like  = '%' . $term . '%';

        $selectComun = [
            'f.id as flujo_id',
            'tt.nombre as flujo_estado',
            'f.created_at',
            'c.id as cliente_id',
            'c.nombre as cliente',
            'c.rtn',
            DB::raw('(SELECT COUNT(*) FROM historico_flujo hf WHERE hf.flujo_id = f.id AND hf.tipo_tramite_id = 2) as total_ofertas'),
            DB::raw('(SELECT COUNT(*) FROM historico_flujo hf WHERE hf.flujo_id = f.id AND hf.tipo_tramite_id = 2 AND hf.observaciones LIKE \'%ganadora%\') as has_ganadora'),
        ];

        // ── A: flujos CON pedido ─────────────────────────────────────────
        $qA = DB::table('flujo as f')
            ->join('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
            ->join('pedido as p', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'p.id')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->where('f.tipo_flujo_id', 1)
            ->whereNotIn('p.estado', ['cancelado'])
            ->whereExists(function ($s) {
                $s->from('historico_flujo as hf')
                  ->whereColumn('hf.flujo_id', 'f.id')
                  ->where('hf.tipo_tramite_id', 1);
            })
            ->select(array_merge($selectComun, [
                'p.id as pedido_id',
                DB::raw("NULL as cotizacion_id"),
                DB::raw("'pedido' as tipo_origen"),
            ]))
            ->orderByDesc('f.id')
            ->limit(8);

        // ── B: flujos SIN pedido (cotizacion directa) ────────────────────
        $qB = DB::table('flujo as f')
            ->join('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
            ->join('cotizacion as co', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'co.id')
            ->join('cliente as c', 'c.id', '=', 'co.cliente_id')
            ->where('f.tipo_flujo_id', 1)
            ->whereNotExists(function ($s) {
                $s->from('historico_flujo as hf')
                  ->whereColumn('hf.flujo_id', 'f.id')
                  ->where('hf.tipo_tramite_id', 1);
            })
            ->select(array_merge($selectComun, [
                DB::raw("NULL as pedido_id"),
                'co.id as cotizacion_id',
                DB::raw("'cotizacion' as tipo_origen"),
            ]))
            ->orderByDesc('f.id')
            ->limit(8);

        // ── Filtros ──────────────────────────────────────────────────────
        if ($esNum) {
            $qA->where(function ($s) use ($num) {
                $s->where('f.id', $num)
                  ->orWhere('p.id', $num)
                  ->orWhereExists(fn ($e) => $e->from('historico_flujo as hf')
                      ->whereColumn('hf.flujo_id', 'f.id')
                      ->where('hf.tramite_id', $num)
                      ->where('hf.tipo_tramite_id', 2));
            });
            $qB->where(function ($s) use ($num) {
                $s->where('f.id', $num)
                  ->orWhere('co.id', $num)
                  ->orWhereExists(fn ($e) => $e->from('historico_flujo as hf')
                      ->whereColumn('hf.flujo_id', 'f.id')
                      ->where('hf.tramite_id', $num)
                      ->where('hf.tipo_tramite_id', 2));
            });
        } else {
            $qA->where(fn ($s) => $s->where('c.nombre', 'LIKE', $like)->orWhere('c.rtn', 'LIKE', $like));
            $qB->where(fn ($s) => $s->where('c.nombre', 'LIKE', $like)->orWhere('c.rtn', 'LIKE', $like));
        }

        $this->flujoEncontrados = array_slice(
            array_merge($qA->get()->toArray(), $qB->get()->toArray()),
            0, 8
        );
    }

    public function seleccionarFlujo(int $flujoId)
    {
        $hasPedido = DB::table('historico_flujo')
            ->where('flujo_id', $flujoId)
            ->where('tipo_tramite_id', 1)
            ->exists();

        if ($hasPedido) {
            $f = DB::table('flujo as f')
                ->join('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
                ->join('pedido as p', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'p.id')
                ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
                ->where('f.id', $flujoId)
                ->select('f.id as flujo_id', 'tt.nombre as flujo_estado',
                         'p.id as pedido_id', DB::raw('NULL as cotizacion_id'),
                         'p.created_at', 'c.id as cliente_id', 'c.nombre as cliente', 'c.rtn',
                         DB::raw("'pedido' as tipo_origen"))
                ->first();

            if (!$f) return;

            $this->pedidoId = $f->pedido_id;

            $detalles = DB::table('pedido_detalle')
                ->where('pedido_id', $f->pedido_id)
                ->select('id', 'nombre_producto', 'cantidad')
                ->get();

            $this->productosSugeridos = [];
            foreach ($detalles as $det) {
                $this->productosSugeridos[] = [
                    'nombre_pedido' => $det->nombre_producto,
                    'cantidad'      => $det->cantidad,
                    'similares'     => $this->buscarSimilares($det->nombre_producto),
                ];
            }
        } else {
            $f = DB::table('flujo as f')
                ->join('tipos_tramites as tt', 'tt.id', '=', 'f.tipo_tramite_id')
                ->join('cotizacion as co', DB::raw('CAST(f.identificacion AS UNSIGNED)'), '=', 'co.id')
                ->join('cliente as c', 'c.id', '=', 'co.cliente_id')
                ->where('f.id', $flujoId)
                ->select('f.id as flujo_id', 'tt.nombre as flujo_estado',
                         DB::raw('NULL as pedido_id'), 'co.id as cotizacion_id',
                         'co.created_at', 'c.id as cliente_id', 'c.nombre as cliente', 'c.rtn',
                         DB::raw("'cotizacion' as tipo_origen"))
                ->first();

            if (!$f) return;

            $this->pedidoId           = null;
            $this->productosSugeridos = [];
        }

        $this->flujoVinculadoId = $flujoId;
        $this->flujoVinculado   = (array) $f;
        $this->clientePedido    = [
            'id'     => $f->cliente_id,
            'nombre' => $f->cliente,
            'rtn'    => $f->rtn,
        ];

        $this->busquedaFlujo    = '';
        $this->flujoEncontrados = [];

        $this->dispatchBrowserEvent('pedido-seleccionado', [
            'clienteId'      => $this->clientePedido['id'],
            'clienteNombre'  => $this->clientePedido['nombre'],
            'vendedorId'     => $this->vendedorDefault['id'] ?? null,
            'vendedorNombre' => $this->vendedorDefault['name'] ?? null,
        ]);
    }

    public function seleccionarFlujoDesdePedido(int $pedidoId)
    {
        $flujoId = DB::table('flujo')
            ->where('identificacion', (string) $pedidoId)
            ->where('tipo_flujo_id', 1)
            ->value('id');

        if ($flujoId) {
            $this->seleccionarFlujo((int) $flujoId);
        }
    }
    /**
     * Carga el detalle de un pedido para mostrar en modal de preview.
     */
    public function verDetallePedido(int $id)
    {
        $p = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.users_id')
            ->where('p.id', $id)
            ->select(
                'p.id', 'p.estado', 'p.created_at', 'p.observaciones',
                'c.nombre as cliente', 'c.rtn',
                'u.name as vendedor_registra'
            )
            ->first();

        if (!$p) return;

        $detalles = DB::table('pedido_detalle')
            ->where('pedido_id', $id)
            ->select('nombre_producto', 'cantidad')
            ->get()
            ->toArray();

        $this->pedidoDetalle = [
            'pedido'    => (array) $p,
            'productos' => $detalles,
        ];

        $this->dispatchBrowserEvent('mostrar-modal-detalle-pedido');
    }

    /**
     * Busca hasta $limit productos reales cuyo nombre se asemeje al nombre del pedido.
     */
    private function buscarSimilares(string $nombre, int $limit = 3): array
    {
        // Tokenizar: palabras de 3+ letras
        $palabras = array_filter(explode(' ', preg_replace('/[^a-zA-Z0-9\s]/u', ' ', $nombre)), fn($w) => strlen($w) >= 3);

        if (empty($palabras)) {
            return DB::table('producto')
                ->where('nombre', 'LIKE', '%' . $nombre . '%')
                ->whereRaw('id IN (SELECT producto_id FROM inventario WHERE cantidad > 0)')
                ->select('id', 'nombre', 'precio_base as precio', 'isv')
                ->limit($limit)->get()->toArray();
        }

        // Construir CASE para scoring por coincidencia de palabras
        $q = DB::table('producto');
        $cases  = [];
        $params = [];
        foreach ($palabras as $w) {
            $cases[]  = 'IF(nombre LIKE ?, 1, 0)';
            $params[] = '%' . $w . '%';
        }
        $score = 'IF(nombre LIKE ?, 10, 0) + ' . implode(' + ', $cases);
        $params = array_merge(['%' . $nombre . '%'], $params);

        $results = $q->selectRaw('id, nombre, precio_base as precio, isv, (' . $score . ') as score', $params)
            ->having('score', '>', 0)
            ->orderByDesc('score')
            ->limit($limit)
            ->get()->toArray();

        // Fallback si no hay resultados con scoring
        if (empty($results)) {
            $results = DB::table('producto')
                ->where('nombre', 'LIKE', '%' . array_values($palabras)[0] . '%')
                ->select('id', 'nombre', 'precio_base as precio', 'isv')
                ->limit($limit)->get()->toArray();
        }

        return $results;
    }

    public function desvincularFlujo()
    {
        $this->pedidoId            = null;
        $this->flujoVinculadoId    = null;
        $this->flujoVinculado      = null;
        $this->busquedaFlujo       = '';
        $this->flujoEncontrados    = [];
        $this->clientePedido       = null;
        $this->productosSugeridos  = [];
        $this->pedidoDetalle       = null;

        $this->dispatchBrowserEvent('pedido-desvinculado', [
            'vendedorId'     => $this->vendedorDefault['id'] ?? null,
            'vendedorNombre' => $this->vendedorDefault['name'] ?? null,
        ]);
    }

    public function render()
    {
        return view('livewire.ventas.facturacion-unificada', [
            'tiposFactura' => $this->tiposFactura,
            'config'       => $this->tipoFactura,
        ]);
    }
}
