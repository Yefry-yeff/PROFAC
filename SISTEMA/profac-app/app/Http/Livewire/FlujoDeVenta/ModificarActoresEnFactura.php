<?php

namespace App\Http\Livewire\FlujoDeVenta;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ModificarActoresEnFactura extends Component
{
    public $titulo = 'Modificar actores en Factura';
    public $busqueda = '';
    public $facturas = [];
    public $usuarios = [];
    public $facturaSeleccionada = [];
    public $facturaSeleccionadaId = null;
    public $vendedorId = '';
    public $gestorEntregaId = '';
    public $teleAsesorId = '';

    protected $rules = [
        'vendedorId' => ['required', 'integer'],
        'gestorEntregaId' => ['nullable', 'integer'],
        'teleAsesorId' => ['required', 'integer'],
    ];

    public function mount()
    {
        $this->usuarios = $this->cargarUsuarios();
        $this->cargarFacturas();
    }

    public function render()
    {
        return view('livewire.flujodeventa.modificaractoresenfactura', [
            'facturas' => $this->facturas,
            'usuarios' => $this->usuarios,
            'facturaSeleccionada' => $this->facturaSeleccionada,
        ]);
    }

    public function updatedBusqueda(): void
    {
        $this->cargarFacturas();
    }

    public function cargarFacturas(): void
    {
        $busqueda = trim($this->busqueda);

        $query = DB::table('factura as f')
            ->leftJoin('users as vendedor', 'vendedor.id', '=', 'f.vendedor')
            ->leftJoin('users as gestor', 'gestor.id', '=', 'f.gestor_entrega')
            ->leftJoin('users as tele', 'tele.id', '=', 'f.users_id')
            ->select(
                'f.id',
                'f.cai',
                'f.numero_secuencia_cai',
                'f.numero_factura',
                'f.nombre_cliente',
                'f.rtn',
                'f.total',
                'f.estado_venta_id',
                'f.estado_factura_id',
                'f.fecha_emision',
                DB::raw('COALESCE(vendedor.name, "-") as vendedor_nombre'),
                DB::raw('COALESCE(gestor.name, "-") as gestor_nombre'),
                DB::raw('COALESCE(tele.name, "-") as tele_asesor_nombre')
            )
            ->orderByDesc('f.id')
            ->limit(30);

        if ($busqueda !== '') {
            $query->where(function ($subQuery) use ($busqueda) {
                $like = '%' . $busqueda . '%';
                $subQuery->where('f.id', 'LIKE', $like)
                    ->orWhere('f.numero_secuencia_cai', 'LIKE', $like)
                    ->orWhere('f.numero_factura', 'LIKE', $like)
                    ->orWhere('f.cai', 'LIKE', $like)
                    ->orWhere('f.nombre_cliente', 'LIKE', $like)
                    ->orWhere('f.rtn', 'LIKE', $like);
            });
        }

        $this->facturas = $query->get()->map(function ($factura) {
            return [
                'id' => (int) $factura->id,
                'cai' => $factura->cai,
                'numero_secuencia_cai' => $factura->numero_secuencia_cai,
                'numero_factura' => $factura->numero_factura,
                'nombre_cliente' => $factura->nombre_cliente,
                'rtn' => $factura->rtn,
                'total' => $factura->total,
                'estado_venta_id' => $factura->estado_venta_id,
                'estado_factura_id' => $factura->estado_factura_id,
                'fecha_emision' => $factura->fecha_emision,
                'vendedor_nombre' => $factura->vendedor_nombre,
                'gestor_nombre' => $factura->gestor_nombre,
                'tele_asesor_nombre' => $factura->tele_asesor_nombre,
            ];
        })->toArray();
    }

    public function seleccionarFactura(int $facturaId): void
    {
        $factura = DB::table('factura as f')
            ->leftJoin('users as vendedor', 'vendedor.id', '=', 'f.vendedor')
            ->leftJoin('users as gestor', 'gestor.id', '=', 'f.gestor_entrega')
            ->leftJoin('users as tele', 'tele.id', '=', 'f.users_id')
            ->select(
                'f.id',
                'f.cai',
                'f.numero_secuencia_cai',
                'f.numero_factura',
                'f.nombre_cliente',
                'f.rtn',
                'f.total',
                'f.estado_venta_id',
                'f.estado_factura_id',
                'f.fecha_emision',
                'f.vendedor',
                'f.gestor_entrega',
                'f.users_id',
                DB::raw('COALESCE(vendedor.name, "-") as vendedor_nombre'),
                DB::raw('COALESCE(gestor.name, "-") as gestor_nombre'),
                DB::raw('COALESCE(tele.name, "-") as tele_asesor_nombre')
            )
            ->where('f.id', $facturaId)
            ->first();

        if (!$factura) {
            session()->flash('error', 'La factura seleccionada no existe.');
            return;
        }

        $this->facturaSeleccionadaId = (int) $factura->id;
        $this->facturaSeleccionada = [
            'id' => (int) $factura->id,
            'cai' => $factura->cai,
            'numero_secuencia_cai' => $factura->numero_secuencia_cai,
            'numero_factura' => $factura->numero_factura,
            'nombre_cliente' => $factura->nombre_cliente,
            'rtn' => $factura->rtn,
            'total' => $factura->total,
            'estado_venta_id' => $factura->estado_venta_id,
            'estado_factura_id' => $factura->estado_factura_id,
            'fecha_emision' => $factura->fecha_emision,
            'vendedor_nombre' => $factura->vendedor_nombre,
            'gestor_nombre' => $factura->gestor_nombre,
            'tele_asesor_nombre' => $factura->tele_asesor_nombre,
        ];

        $this->vendedorId = $factura->vendedor ? (string) $factura->vendedor : '';
        $this->gestorEntregaId = $factura->gestor_entrega ? (string) $factura->gestor_entrega : '';
        $this->teleAsesorId = $factura->users_id ? (string) $factura->users_id : '';
    }

    public function limpiarFormulario(): void
    {
        $this->facturaSeleccionadaId = null;
        $this->facturaSeleccionada = [];
        $this->vendedorId = '';
        $this->gestorEntregaId = '';
        $this->teleAsesorId = '';
    }

    public function guardarCambios(): void
    {
        $this->validate([
            'vendedorId' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'gestorEntregaId' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'teleAsesorId' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ], [
            'vendedorId.required' => 'Debes seleccionar el asesor comercial.',
            'gestorEntregaId.integer' => 'El gestor de entregas seleccionado no es válido.',
            'teleAsesorId.required' => 'Debes seleccionar el tele asesor.',
        ]);

        if (!$this->facturaSeleccionadaId) {
            session()->flash('error', 'Selecciona una factura antes de guardar los cambios.');
            return;
        }

        $factura = DB::table('factura')
            ->where('id', $this->facturaSeleccionadaId)
            ->first(['id', 'estado_venta_id']);

        if (!$factura) {
            session()->flash('error', 'La factura seleccionada no existe.');
            return;
        }

        if ((int) $factura->estado_venta_id === 2) {
            session()->flash('error', 'No se pueden modificar actores de una factura anulada.');
            return;
        }

        DB::table('factura')
            ->where('id', $this->facturaSeleccionadaId)
            ->update([
                'vendedor' => (int) $this->vendedorId,
                'gestor_entrega' => $this->gestorEntregaId !== '' ? (int) $this->gestorEntregaId : null,
                'users_id' => (int) $this->teleAsesorId,
                'updated_at' => now(),
            ]);

        session()->flash('success', 'Los actores de la factura se actualizaron correctamente.');

        $this->seleccionarFactura($this->facturaSeleccionadaId);
        $this->cargarFacturas();
    }

    protected function cargarUsuarios(): array
    {
        return DB::table('users as u')
            ->leftJoin('rol as r', 'r.id', '=', 'u.rol_id')
            ->orderBy('u.name')
            ->get([
                'u.id',
                'u.name',
                'u.rol_id',
                DB::raw('COALESCE(r.nombre, "Sin rol") as rol_nombre'),
            ])
            ->map(function ($usuario) {
                return [
                    'id' => (int) $usuario->id,
                    'name' => $usuario->name,
                    'rol_id' => (int) $usuario->rol_id,
                    'rol_nombre' => $usuario->rol_nombre,
                ];
            })
            ->toArray();
    }
}
