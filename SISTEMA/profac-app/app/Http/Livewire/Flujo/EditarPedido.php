<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class EditarPedido extends Component
{
    // ── Identificador del pedido ───────────────────────────────────────────
    public $pedidoId;

    // ── Cliente (solo lectura, no se puede modificar) ──────────────────────
    public $clienteSeleccionado = null;

    

    // ── Líneas del pedido ──────────────────────────────────────────────────
    public $items = [];
    public $observaciones = '';

    // ── Mensajes ───────────────────────────────────────────────────────────
    public $mensajeExito = '';
    public $mensajeError = '';

    // ── Validación ─────────────────────────────────────────────────────────
    protected function rulesGuardar(): array
    {
        return [
            'items'                            => 'required|array|min:1',
            'items.*.nombre_producto'          => 'required|string|max:255',
            'items.*.cantidad'                 => 'required|numeric|min:0.01',
        ];
    }

    protected function messagesGuardar(): array
    {
        return [
            'items.required'                   => 'Debe agregar al menos un producto.',
            'items.min'                        => 'Debe agregar al menos un producto.',
            'items.*.nombre_producto.required' => 'El nombre del producto es obligatorio.',
            'items.*.cantidad.required'        => 'La cantidad es obligatoria.',
            'items.*.cantidad.min'             => 'La cantidad debe ser mayor a 0.',
        ];
    }

    // ── Ciclo de vida ──────────────────────────────────────────────────────
    public function mount(int $id)
    {
        $pedido = DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->select(
                'p.id', 'p.observaciones', 'p.estado',
                'p.cliente_id',
                'c.nombre as cliente_nombre',
                'c.rtn',
                'c.correo',
                'c.telefono_empresa',
                'c.direccion'
            )
            ->where('p.id', $id)
            ->first();

        if (!$pedido) {
            abort(404, 'Pedido no encontrado.');
        }

        $this->pedidoId = $id;

        $this->clienteSeleccionado = [
            'id'               => $pedido->cliente_id,
            'nombre'           => $pedido->cliente_nombre,
            'rtn'              => $pedido->rtn,
            'correo'           => $pedido->correo,
            'telefono_empresa' => $pedido->telefono_empresa,
            'direccion'        => $pedido->direccion,
        ];

        $this->observaciones = $pedido->observaciones ?? '';

        $detalles = DB::table('pedido_detalle')
            ->where('pedido_id', $id)
            ->orderBy('id')
            ->get();

        $this->items = $detalles->map(fn($d) => [
            'nombre_producto' => $d->nombre_producto,
            'cantidad'        => (int) $d->cantidad,
        ])->toArray();

        if (empty($this->items)) {
            $this->items = [['nombre_producto' => '', 'cantidad' => 1]];
        }
    }

    // ── Ítems ──────────────────────────────────────────────────────────────
    public function agregarItem()
    {
        $this->items[] = ['nombre_producto' => '', 'cantidad' => 1];
    }

    public function eliminarItem(int $index)
    {
        if (count($this->items) > 1) {
            array_splice($this->items, $index, 1);
            $this->items = array_values($this->items);
        }
    }

    // ── Guardar cambios ────────────────────────────────────────────────────
    public function guardarCambios()
    {
        $this->validate($this->rulesGuardar(), $this->messagesGuardar());

        DB::beginTransaction();
        try {
            DB::table('pedido')
                ->where('id', $this->pedidoId)
                ->update([
                    'observaciones' => $this->observaciones ?: null,
                    'updated_at'    => now(),
                ]);

            DB::table('pedido_detalle')
                ->where('pedido_id', $this->pedidoId)
                ->delete();

            foreach ($this->items as $item) {
                DB::table('pedido_detalle')->insert([
                    'pedido_id'       => $this->pedidoId,
                    'nombre_producto' => $item['nombre_producto'],
                    'cantidad'        => $item['cantidad'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            DB::commit();

            $this->mensajeExito = 'Pedido #' . $this->pedidoId . ' actualizado exitosamente.';
            $this->mensajeError = '';
            $this->dispatchBrowserEvent('scroll-top');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error al actualizar el pedido: ' . $e->getMessage();
        }
    }

    // ── Render ─────────────────────────────────────────────────────────────
    public function render()
    {
        $layout = request()->has('embed') ? 'layouts.embed' : 'layouts.app';
        return view('livewire.flujo.editar-pedido')->layout($layout);
    }
}
