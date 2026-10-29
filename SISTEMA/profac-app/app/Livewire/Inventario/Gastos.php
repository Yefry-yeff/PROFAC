<?php

namespace App\Livewire\Inventario;

use App\Exports\GastosExport;
use App\Models\Gasto;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Gastos extends Component
{
    use WithPagination;

    public const TASAS_ISV = [0, 15, 18];

    protected $paginationTheme = 'bootstrap';

    public array $filtros = [];
    #[Locked]
    public array $filtrosAplicados = [];
    public array $seleccionados = [];
    public bool $seleccionarTodos = false;
    public string $motivoAnulacion = '';
    #[Locked]
    public string $columnaOrden = 'id';
    #[Locked]
    public string $direccionOrden = 'desc';

    public function mount(): void
    {
        $this->filtros = $this->filtrosAplicados = $this->filtrosDelMes();
    }

    private function filtrosDelMes(): array
    {
        return [
            'desde' => now()->startOfMonth()->toDateString(),
            'hasta' => now()->endOfMonth()->toDateString(),
            'factura' => '', 'proveedor' => '', 'estado' => '',
        ];
    }

    public function aplicarFiltros(): void
    {
        $this->validate([
            'filtros.desde' => 'required|date_format:Y-m-d',
            'filtros.hasta' => 'required|date_format:Y-m-d|after_or_equal:filtros.desde',
            'filtros.factura' => 'nullable|string|max:255',
            'filtros.proveedor' => 'nullable|integer|exists:proveedores,id',
            'filtros.estado' => ['nullable', Rule::in(array_keys(Gasto::ESTADOS_REPORTE))],
        ], [
            'filtros.desde.required' => 'Indique la fecha de emision desde.',
            'filtros.hasta.required' => 'Indique la fecha de emision hasta.',
            'date_format' => 'Ingrese una fecha valida.',
            'after_or_equal' => 'La fecha hasta no puede ser anterior a la fecha desde.',
            'exists' => 'Seleccione un proveedor valido.',
            'integer' => 'Seleccione un proveedor valido.',
            'max' => 'El numero de factura no puede superar 255 caracteres.',
            'filtros.estado.in' => 'Seleccione un estado valido.',
        ]);
        $this->filtrosAplicados = $this->filtros;
        $this->resetPage();
        $this->limpiarSeleccion();
        $this->dispatch('gastos-filtros-aplicados');
    }

    public function limpiarFiltros(): void
    {
        $this->filtros = $this->filtrosDelMes();
        $this->aplicarFiltros();
        $this->dispatch('gastos-proveedor-limpiado');
    }

    public function ordenarPor(string $columna): void
    {
        validator(['columna' => $columna], [
            'columna' => ['required', Rule::in(array_keys(Gasto::COLUMNAS_ORDEN))],
        ])->validate();

        $this->direccionOrden = $this->columnaOrden === $columna && $this->direccionOrden === 'asc'
            ? 'desc' : 'asc';
        $this->columnaOrden = $columna;
        $this->resetPage();
    }

    public function updatedSeleccionarTodos(bool $value): void
    {
        $this->seleccionados = $value
            ? Gasto::reporte()->filtrar($this->filtrosAplicados)->where('gastos.estado', 'activo')
                ->orderBy('gastos.id')->pluck('gastos.id')->map(fn ($id) => (string) $id)->all()
            : [];
        $this->seleccionarTodos = $value && count($this->seleccionados) > 0;
        $this->resetValidation();
    }

    public function updatedSeleccionados(): void
    {
        $this->seleccionarTodos = count($this->seleccionados) > 0
            && !Gasto::reporte()->filtrar($this->filtrosAplicados)->where('gastos.estado', 'activo')
                ->whereNotIn('gastos.id', $this->seleccionados)->exists();
        $this->resetValidation();
    }

    public function limpiarSeleccion(): void
    {
        $this->seleccionados = [];
        $this->seleccionarTodos = false;
        $this->motivoAnulacion = '';
        $this->resetValidation();
    }

    public function anularSeleccionados(): void
    {
        $this->aplicarAccion('anular');
    }

    public function pasarACompras(): void
    {
        $this->aplicarAccion('incluir_compras');
    }

    public function quitarDeCompras(): void
    {
        $this->aplicarAccion('quitar_compras');
    }

    private function aplicarAccion(string $accion): void
    {
        abort_unless(Auth::check(), 403);
        $anular = $accion === 'anular';
        if ($anular) {
            $this->motivoAnulacion = trim($this->motivoAnulacion);
        }
        $this->validate([
            'seleccionados' => 'required|array|min:1',
            'seleccionados.*' => 'required|integer|min:1|distinct',
            'motivoAnulacion' => $anular ? 'required|string|max:1000' : 'nullable|string|max:1000',
        ], [
            'seleccionados.required' => 'Seleccione al menos un gasto.',
            'seleccionados.min' => 'Seleccione al menos un gasto.',
            'seleccionados.*.integer' => 'La seleccion de gastos no es valida.',
            'seleccionados.*.distinct' => 'La seleccion contiene gastos repetidos.',
            'motivoAnulacion.required' => 'El motivo de anulacion es obligatorio.',
            'motivoAnulacion.max' => 'El motivo no puede superar 1000 caracteres.',
        ]);

        try {
            $cantidad = DB::transaction(function () use ($anular, $accion) {
                $gastos = Gasto::whereIn('id', $this->seleccionados)->orderBy('id')->lockForUpdate()->get();
                if ($gastos->count() !== count($this->seleccionados)
                    || $gastos->contains(fn ($gasto) => $gasto->estado !== 'activo')) {
                    throw ValidationException::withMessages([
                        'seleccionados' => 'Algun gasto ya fue anulado o no existe. Actualice la seleccion.',
                    ]);
                }

                if ($anular && $gastos->contains(fn ($gasto) => $gasto->incluir_reporte_compras)) {
                    throw ValidationException::withMessages([
                        'seleccionados' => 'No se pueden anular gastos incluidos en compras. Quitelos primero del reporte de compras.',
                    ]);
                }

                if ($anular) {
                    $cambios = [
                        'estado' => 'anulado', 'anulado_por' => Auth::id(), 'anulado_at' => now(),
                        'motivo_anulacion' => $this->motivoAnulacion,
                        'updated_at' => now(),
                    ];
                    $afectados = $gastos;
                } else {
                    $incluir = $accion === 'incluir_compras';
                    $afectados = $gastos->filter(fn ($gasto) => $gasto->incluir_reporte_compras !== $incluir);
                    if ($afectados->isEmpty()) {
                        throw ValidationException::withMessages([
                            'seleccionados' => $incluir
                                ? 'Los gastos seleccionados ya estan en el reporte de compras.'
                                : 'Los gastos seleccionados no estan en el reporte de compras.',
                        ]);
                    }
                    $cambios = ['incluir_reporte_compras' => $incluir, 'updated_at' => now()];
                }

                foreach ($afectados as $gasto) {
                    $antes = [
                        'estado' => $gasto->estado,
                        'incluir_reporte_compras' => $gasto->incluir_reporte_compras,
                    ];
                    $despues = [
                        'estado' => $cambios['estado'] ?? $gasto->estado,
                        'incluir_reporte_compras' => $cambios['incluir_reporte_compras'] ?? $gasto->incluir_reporte_compras,
                    ];
                    $this->registrarAuditoria($gasto->id, $accion, $antes, $despues,
                        $anular ? $this->motivoAnulacion : null);
                }

                return Gasto::whereIn('id', $afectados->pluck('id'))->update($cambios);
            });
        } catch (QueryException $exception) {
            report($exception);
            $this->addError('seleccionados', 'No se pudo actualizar los gastos. Intente de nuevo.');
            return;
        }

        $this->limpiarSeleccion();
        session()->flash('gastos_mensaje', $anular
            ? "Se anularon {$cantidad} gastos. Se registro el usuario y la fecha y hora."
            : ($accion === 'incluir_compras'
                ? "Se pasaron {$cantidad} gastos al reporte de compras. Permanecen en este listado."
                : "Se quitaron {$cantidad} gastos del reporte de compras. Permanecen en Gastos."));
        $this->dispatch('gastos-accion-completada');
    }

    private function registrarAuditoria(int $id, string $accion, ?array $antes, array $despues, ?string $motivo = null): void
    {
        DB::table('gasto_auditoria')->insert([
            'gasto_id' => $id, 'usuario_id' => Auth::id(), 'accion' => $accion,
            'antes' => $antes === null ? null : json_encode($antes, JSON_THROW_ON_ERROR),
            'despues' => json_encode($despues, JSON_THROW_ON_ERROR),
            'motivo' => $motivo, 'created_at' => now(),
        ]);
    }

    public function exportarExcel()
    {
        abort_unless(Auth::check(), 403);

        return Excel::download(new GastosExport($this->filtrosAplicados, $this->columnaOrden, $this->direccionOrden), 'Gastos_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function render()
    {
        $gastos = Gasto::reporte()->filtrar($this->filtrosAplicados)
            ->ordenar($this->columnaOrden, $this->direccionOrden)->paginate(20);
        $proveedores = DB::table('proveedores')->orderBy('nombre')->get(['id', 'nombre']);
        $auditoria = DB::table('gasto_auditoria as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.usuario_id')
            ->whereIn('a.gasto_id', $gastos->pluck('id'))
            ->select('a.*', 'u.name as usuario')->orderByDesc('a.id')->get()->groupBy('gasto_id');
        $seleccion = Gasto::whereIn('id', $this->seleccionados)->get();
        $seleccionActiva = $seleccion->isNotEmpty() && $seleccion->count() === count($this->seleccionados)
            && $seleccion->every(fn ($gasto) => $gasto->estado === 'activo');
        $puedeAnular = $seleccionActiva && $seleccion->every(fn ($gasto) => !$gasto->incluir_reporte_compras);
        $puedeIncluir = $seleccionActiva && $seleccion->contains(fn ($gasto) => !$gasto->incluir_reporte_compras);
        $puedeQuitar = $seleccionActiva && $seleccion->contains(fn ($gasto) => $gasto->incluir_reporte_compras);

        return view('livewire.inventario.gastos', compact(
            'gastos', 'proveedores', 'auditoria', 'puedeAnular', 'puedeIncluir', 'puedeQuitar'
        ));
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
            $this->registrarAuditoria($id, 'registrar', null, [
                'estado' => 'activo', 'incluir_reporte_compras' => (bool) ($data['incluir_reporte_compras'] ?? false),
            ]);
            return $id;
        });

        return response()->json([
            'message' => 'Gasto registrado correctamente.',
            'gasto' => ['id' => $id],
        ], 201);
    }
}
