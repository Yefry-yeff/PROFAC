<?php

namespace App\Livewire\Inventario;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Gastos extends Component
{
    use WithPagination;

    public const TASAS_ISV = [0, 15, 18];

    protected $paginationTheme = 'bootstrap';

    public string $buscar = '';

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $gastos = DB::table('gastos as g')
            ->join('proveedores as p', 'p.id', '=', 'g.proveedor_id')
            ->where('g.incluir_reporte_compras', false)
            ->when(trim($this->buscar) !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('g.numero_factura', 'like', '%' . trim($this->buscar) . '%')
                        ->orWhere('p.nombre', 'like', '%' . trim($this->buscar) . '%');
                });
            })
            ->select('g.*', 'p.nombre as proveedor')
            ->orderByDesc('g.id')->paginate(20);

        return view('livewire.inventario.gastos', compact('gastos'));
    }

    public function imprimir(int $id)
    {
        $gasto = DB::table('gastos as g')
            ->join('proveedores as p', 'p.id', '=', 'g.proveedor_id')
            ->leftJoin('users as u', 'u.id', '=', 'g.usuario_id')
            ->where('g.id', $id)
            ->first(['g.*', 'p.nombre as proveedor', 'p.rtn', 'p.direccion', 'u.name as usuario']);
        abort_unless($gasto, 404);
        $lineas = DB::table('gasto_detalles as gd')
            ->leftJoin('unidad_medida as um', 'um.id', '=', 'gd.unidad_medida_id')
            ->where('gd.gasto_id', $id)->orderBy('gd.id')
            ->get(['gd.*', 'um.nombre as unidad_medida']);
        return Pdf::loadView('pdf.gasto', compact('gasto', 'lineas'))
            ->setPaper('letter')->stream('gasto_' . $id . '.pdf');
    }

    public function guardar(Request $request)
    {
        $data = $request->validate([
            'numero_factura' => 'required|string|max:255',
            'cai' => 'nullable|string|max:255',
            'incluir_reporte_compras' => 'sometimes|required|boolean',
            'seleccionarProveedorId' => ['required', 'integer', Rule::exists('proveedores', 'id')->where('estado_id', 1)],
            'tipoPagoCompra' => 'required|integer|exists:tipo_compra,id',
            'fecha_emision' => 'required|date_format:Y-m-d',
            'fecha_entrega' => 'required|date_format:Y-m-d',
            'fecha_vencimiento' => 'required|date_format:Y-m-d|after_or_equal:fecha_emision',
            'productos' => 'required|array|min:1|max:2000',
            'productos.*.nombre' => 'required|string|max:255',
            'productos.*.cantidad' => 'required|integer|min:1|max:1000000',
            'productos.*.precio' => 'required|numeric|decimal:0,2|min:0|max:999999999.99',
            'productos.*.isv_porcentaje' => ['required', 'integer', Rule::in(self::TASAS_ISV)],
            'productos.*.unidad_medida_id' => 'required|integer|exists:unidad_medida,id',
            'productos.*.descuento_porcentaje' => 'required|numeric|decimal:0,2|min:0|max:100',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'integer' => 'El campo :attribute debe ser un numero entero.',
            'numeric' => 'El campo :attribute debe ser numerico.',
            'decimal' => 'El campo :attribute admite como maximo dos decimales.',
            'productos.*.descuento_porcentaje.min' => 'El descuento debe estar entre 0% y 100%.',
            'productos.*.descuento_porcentaje.max' => 'El descuento debe estar entre 0% y 100%.',
            'exists' => 'El valor de :attribute no existe o no esta disponible.',
            'in' => 'Seleccione una tasa de ISV valida: 0%, 15% o 18%.',
            'array' => 'Los productos deben enviarse como una lista.',
            'min.numeric' => 'El campo :attribute debe ser al menos :min.',
            'max.numeric' => 'El campo :attribute no puede superar :max.',
            'min.array' => 'Agregue al menos una linea al gasto.',
            'max.array' => 'El gasto admite como maximo :max lineas.',
            'max.string' => 'El campo :attribute no puede superar :max caracteres.',
            'date_format' => 'El campo :attribute debe ser una fecha valida.',
            'after_or_equal' => 'El vencimiento no puede ser anterior a la emision.',
        ], [
            'numero_factura' => 'numero de factura', 'seleccionarProveedorId' => 'proveedor',
            'tipoPagoCompra' => 'tipo de pago', 'fecha_emision' => 'fecha de emision',
            'fecha_entrega' => 'fecha de recibido', 'fecha_vencimiento' => 'fecha de vencimiento',
            'productos.*.nombre' => 'nombre del producto', 'productos.*.cantidad' => 'cantidad',
            'productos.*.precio' => 'precio', 'productos.*.isv_porcentaje' => 'tasa de ISV',
            'productos.*.unidad_medida_id' => 'unidad de medida',
            'productos.*.descuento_porcentaje' => 'descuento porcentual',
        ]);

        $lineas = [];
        $subtotal = $impuesto = 0;
        foreach ($data['productos'] as $index => $producto) {
            $nombre = trim($producto['nombre']);
            if ($nombre === '') {
                throw ValidationException::withMessages(["productos.$index.nombre" => 'El nombre del producto es obligatorio.']);
            }
            $precio = (int) round((float) $producto['precio'] * 100);
            $base = $precio * (int) $producto['cantidad'];
            if ($base > 999999999999999) {
                throw ValidationException::withMessages(['productos' => 'El subtotal del producto excede el monto permitido.']);
            }
            $porcentajeDescuento = (int) round((float) $producto['descuento_porcentaje'] * 100);
            $descuento = (int) round($base * ($porcentajeDescuento / 10000));
            $neto = $base - $descuento;
            $isv = (int) round($neto * (int) $producto['isv_porcentaje'] / 100);
            $subtotal += $neto;
            $impuesto += $isv;
            if ($subtotal + $impuesto > 999999999999999) {
                throw ValidationException::withMessages(['productos' => 'El total excede el monto permitido.']);
            }
            $lineas[] = [
                'nombre' => $nombre, 'cantidad' => $producto['cantidad'],
                'precio' => $precio / 100, 'isv_porcentaje' => $producto['isv_porcentaje'],
                'unidad_medida_id' => $producto['unidad_medida_id'],
                'descuento_porcentaje' => $porcentajeDescuento / 100,
                'descuento_monto' => $descuento / 100,
                'subtotal' => $neto / 100, 'isv' => $isv / 100, 'total' => ($neto + $isv) / 100,
            ];
        }
        if ($subtotal + $impuesto === 0) {
            throw ValidationException::withMessages(['productos' => 'El total del gasto debe ser mayor a cero.']);
        }

        $id = DB::transaction(function () use ($data, $lineas, $subtotal, $impuesto) {
            $id = DB::table('gastos')->insertGetId([
                'proveedor_id' => $data['seleccionarProveedorId'], 'usuario_id' => Auth::id(),
                'numero_factura' => trim($data['numero_factura']), 'cai' => strtoupper(trim($data['cai'] ?? '')),
                'tipo_pago_id' => $data['tipoPagoCompra'],
                'incluir_reporte_compras' => $data['incluir_reporte_compras'] ?? false,
                'fecha_emision' => $data['fecha_emision'], 'fecha_recepcion' => $data['fecha_entrega'],
                'fecha_vencimiento' => $data['fecha_vencimiento'],
                'subtotal' => $subtotal / 100, 'isv' => $impuesto / 100,
                'total' => ($subtotal + $impuesto) / 100, 'saldo' => ($subtotal + $impuesto) / 100,
                'estado' => 'activo', 'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach (array_chunk($lineas, 200) as $grupo) {
                DB::table('gasto_detalles')->insert(array_map(fn ($linea) => $linea + ['gasto_id' => $id], $grupo));
            }
            return $id;
        });

        return response()->json([
            'message' => 'Gasto registrado correctamente.',
            'gasto' => ['id' => $id, 'impresion_url' => url('/gastos/imprimir/' . $id)],
        ], 201);
    }
}
