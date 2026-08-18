<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\ModelCliente;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class Pedido extends Component
{
    use WithFileUploads;

    // ── Búsqueda de cliente ────────────────────────────────────────────────
    public $busqueda          = '';
    public $hasBuscado        = false;
    public $resultadosBusqueda = [];
    public $clienteSeleccionado = null;   // array con datos del cliente

    // ── Modal: crear nuevo cliente ─────────────────────────────────────────
    public $showModalCliente = false;
    public $nc_nombre        = '';
    public $nc_rtn           = '';
    public $nc_correo        = '';
    public $nc_telefono      = '';
    public $nc_direccion     = '';

    // ── Líneas de pedido ───────────────────────────────────────────────────
    public $items        = [];
    public $observaciones = '';

    // ── Importación desde Excel ────────────────────────────────────────────
    public $archivoExcel       = null;
    public $excelPreview       = [];
    public $excelSeleccionados = [];
    public $showExcelPreview   = false;
    public $mensajeExcelError  = '';
    public $excelPagina        = 1;
    public $excelPorPagina     = 10;
    public $excelImportado     = false;  // true después de importar exitosamente

    // ── Mensajes de retroalimentación ─────────────────────────────────────
    public $mensajeExito     = '';
    public $mensajeError     = '';
    public $pedidoGuardadoId = null;   // ID del pedido recién guardado (muestra panel de acciones)
    public $numeroPedido     = null;   // Número a asignar al próximo pedido

    // ── Reglas de validación para el formulario principal ─────────────────
    protected function rulesGuardar(): array
    {
        return [
            'clienteSeleccionado'              => 'required',
            'items'                            => 'required|array|min:1',
            'items.*.nombre_producto'          => 'required|string|max:255',
            'items.*.cantidad'                 => 'required|numeric|min:0.01',
        ];
    }

    protected function messagesGuardar(): array
    {
        return [
            'clienteSeleccionado.required'        => 'Debe seleccionar un cliente.',
            'items.*.nombre_producto.required'    => 'El nombre del producto es obligatorio.',
            'items.*.cantidad.required'           => 'La cantidad es obligatoria.',
            'items.*.cantidad.min'                => 'La cantidad debe ser mayor a 0.',
        ];
    }

    // ── Reglas para el modal de crear cliente ─────────────────────────────
    protected function rulesCliente(): array
    {
        return [
            'nc_nombre'    => 'required|string|max:255',
            'nc_rtn'       => 'nullable|string|max:20',
            'nc_correo'    => 'nullable|email|max:255',
            'nc_telefono'  => 'nullable|string|max:30',
            'nc_direccion' => 'nullable|string|max:500',
        ];
    }

    protected function messagesCliente(): array
    {
        return [
            'nc_nombre.required' => 'El nombre del cliente es obligatorio.',
            'nc_correo.email'    => 'Ingrese un correo electrónico válido.',
        ];
    }

    // ── Ciclo de vida ──────────────────────────────────────────────────────
    public function mount()
    {
        $this->numeroPedido = (DB::table('pedido')->max('id') ?? 0) + 1;

        // Si se llega con productos pre-cargados (ej: desde duplicar pedido)
        $productosParam = request('productos');
        if ($productosParam) {
            $decoded = json_decode(base64_decode($productosParam), true);
            if (is_array($decoded) && count($decoded) > 0) {
                $this->items = array_map(fn($p) => [
                    'nombre_producto' => $p['nombre_producto'] ?? '',
                    'cantidad'        => $p['cantidad'] ?? 1,
                ], $decoded);
                return;
            }
        }

        $this->items = [
            ['nombre_producto' => '', 'cantidad' => 1],
        ];
    }

    // ── Búsqueda ───────────────────────────────────────────────────────────

    /** Se dispara automáticamente al cambiar $busqueda (live search mientras escribe) */
    public function updatedBusqueda()
    {
        // Si ya hay un cliente seleccionado no interferimos
        if ($this->clienteSeleccionado !== null) return;

        $this->hasBuscado         = false;
        $this->mensajeExito       = '';
        $this->mensajeError       = '';

        $term = trim($this->busqueda);
        if (strlen($term) < 2) {
            $this->resultadosBusqueda = [];
            return;
        }

        $this->resultadosBusqueda = $this->ejecutarBusqueda($term);
    }

    private function ejecutarBusqueda(string $term): array
    {
        $rolId = Auth::user()->rol_id ?? 0;

        $query = DB::table('cliente')
            ->select('id', 'nombre', 'rtn', 'correo', 'telefono_empresa', 'direccion',
                     'credito', 'dias_credito')
            ->where('estado_cliente_id', 1)
            ->where(function($q) use ($term) {
                $q->where('nombre', 'LIKE', '%' . $term . '%')
                  ->orWhere('rtn', 'LIKE', '%' . $term . '%');
            });

        // Admin (1) y Tele asesor (3) ven todos; los demás solo sus asignados
        if (!in_array($rolId, [1, 3], true)) {
            $query->where('vendedor', Auth::id());
        }

        return $query->limit(5)->get()->map(fn($r) => (array) $r)->toArray();
    }

    public function buscarCliente()
    {
        $this->clienteSeleccionado  = null;
        $this->resultadosBusqueda   = [];
        $this->hasBuscado           = true;
        $this->mensajeExito         = '';
        $this->mensajeError         = '';

        $term = trim($this->busqueda);
        if (strlen($term) < 2) return;

        $this->resultadosBusqueda = $this->ejecutarBusqueda($term);
    }

    public function seleccionarCliente(int $id)
    {
        $cliente = DB::table('cliente')
            ->select('id', 'nombre', 'rtn', 'correo', 'telefono_empresa', 'direccion',
                     'credito', 'dias_credito')
            ->where('id', $id)
            ->first();

        if ($cliente) {
            $this->clienteSeleccionado  = (array) $cliente;
            $this->resultadosBusqueda   = [];
            $this->busqueda             = $cliente->nombre;
            $this->hasBuscado           = false;
        }
    }

    public function limpiarCliente()
    {
        $this->clienteSeleccionado  = null;
        $this->resultadosBusqueda   = [];
        $this->busqueda             = '';
        $this->hasBuscado           = false;
    }

    // ── Modal crear cliente ────────────────────────────────────────────────
    public function abrirModalCrearCliente()
    {
        $this->nc_nombre    = $this->busqueda;
        $this->nc_rtn       = '';
        $this->nc_correo    = '';
        $this->nc_telefono  = '';
        $this->nc_direccion = '';
        $this->resetErrorBag();
        $this->showModalCliente = true;
    }

    public function cerrarModalCrearCliente()
    {
        $this->showModalCliente = false;
        $this->resetErrorBag();
    }

    public function guardarNuevoCliente()
    {
        $this->validate($this->rulesCliente(), $this->messagesCliente());

        $cliente = new ModelCliente();
        $cliente->nombre           = $this->nc_nombre;
        $cliente->rtn              = $this->nc_rtn       ?: null;
        $cliente->correo           = $this->nc_correo    ?: null;
        $cliente->telefono_empresa = $this->nc_telefono  ?: null;
        $cliente->direccion        = $this->nc_direccion ?: null;
        $cliente->users_id         = Auth::id();
        $cliente->save();

        $this->clienteSeleccionado = [
            'id'               => $cliente->id,
            'nombre'           => $cliente->nombre,
            'rtn'              => $cliente->rtn,
            'correo'           => $cliente->correo,
            'telefono_empresa' => $cliente->telefono_empresa,
            'direccion'        => $cliente->direccion,
            'credito'          => null,
            'dias_credito'     => null,
        ];

        $this->busqueda         = $cliente->nombre;
        $this->hasBuscado       = false;
        $this->showModalCliente = false;
        $this->resultadosBusqueda = [];
    }

    // ── Ítems del pedido ───────────────────────────────────────────────────
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

    // ── Excel: procesar al seleccionar archivo ─────────────────────────────
    public function updatedArchivoExcel()
    {
        $this->mensajeExcelError = '';
        $this->excelPreview      = [];
        $this->showExcelPreview  = false;

        if (!$this->archivoExcel) return;

        $this->validate(
            ['archivoExcel' => 'required|file|mimes:xlsx,xls|max:5120'],
            [
                'archivoExcel.mimes' => 'Solo se permiten archivos .xlsx o .xls.',
                'archivoExcel.max'   => 'El archivo no debe superar 5 MB.',
            ]
        );

        try {
            $path        = $this->archivoExcel->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, false);

            $preview = [];
            foreach ($rows as $rowIndex => $row) {
                if ($rowIndex === 0) continue; // omitir cabecera
                $nombre   = trim((string)($row[0] ?? ''));
                $cantidad = trim((string)($row[1] ?? ''));
                if ($nombre === '') continue;
                $cantNum = is_numeric($cantidad) && (float)$cantidad > 0 ? (float)$cantidad : 1;
                $preview[] = ['nombre_producto' => $nombre, 'cantidad' => $cantNum];
            }

            if (empty($preview)) {
                $this->mensajeExcelError = 'El archivo no contiene filas válidas. Columna A = nombre, Columna B = cantidad (desde fila 2).';
                return;
            }

            $this->excelPreview        = $preview;
            $this->excelSeleccionados  = array_fill(0, count($preview), true);
            $this->showExcelPreview    = true;
            $this->excelPagina         = 1;
            $this->excelImportado      = false;

        } catch (\Exception $e) {
            $this->mensajeExcelError = 'No se pudo leer el archivo: ' . $e->getMessage();
        }
    }

    public function importarDesdeExcel()
    {
        if (empty($this->excelPreview)) return;

        $seleccionados = array_values(array_filter(
            $this->excelPreview,
            fn($i) => !empty($this->excelSeleccionados[$i]),
            ARRAY_FILTER_USE_KEY
        ));

        if (empty($seleccionados)) return;

        $soloVacio = count($this->items) === 1
            && trim($this->items[0]['nombre_producto'] ?? '') === '';

        if ($soloVacio) {
            $this->items = $seleccionados;
        } else {
            foreach ($seleccionados as $item) {
                $this->items[] = $item;
            }
        }
        $this->items = array_values($this->items);

        $this->excelPreview        = [];
        $this->excelSeleccionados  = [];
        $this->showExcelPreview    = false;
        $this->archivoExcel        = null;
        $this->mensajeExcelError   = '';
        $this->excelPagina         = 1;
        $this->excelImportado      = true;  // oculta la zona de carga
    }

    public function limpiarExcel()
    {
        $this->excelPreview        = [];
        $this->excelSeleccionados  = [];
        $this->showExcelPreview    = false;
        $this->archivoExcel        = null;
        $this->mensajeExcelError   = '';
        $this->excelPagina         = 1;
        $this->excelImportado      = false;
        $this->resetErrorBag('archivoExcel');
    }

    public function seleccionarTodosExcel()
    {
        $this->excelSeleccionados = array_fill(0, count($this->excelPreview), true);
    }

    public function deseleccionarTodosExcel()
    {
        $this->excelSeleccionados = array_fill(0, count($this->excelPreview), false);
    }

    public function excelPaginaAnterior()
    {
        if ($this->excelPagina > 1) $this->excelPagina--;
    }

    public function excelPaginaSiguiente()
    {
        $total  = count($this->excelPreview);
        $paginas = (int) ceil($total / $this->excelPorPagina);
        if ($this->excelPagina < $paginas) $this->excelPagina++;
    }

    public function descargarPlantilla()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Producto');
        $sheet->setCellValue('B1', 'Cantidad');
        $sheet->setCellValue('A2', 'Producto Ejemplo 1');
        $sheet->setCellValue('B2', 10);
        $sheet->setCellValue('A3', 'Producto Ejemplo 2');
        $sheet->setCellValue('B3', 5);
        $sheet->setCellValue('A4', 'Producto Ejemplo 3');
        $sheet->setCellValue('B4', 3);

        $sheet->getStyle('A1:B1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(12);

        $writer = new XlsxWriter($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'plantilla_pedido.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ── Guardar pedido ─────────────────────────────────────────────────────
    public function guardarPedido()
    {
        $this->validate($this->rulesGuardar(), $this->messagesGuardar());

        DB::beginTransaction();
        try {
            $pedidoId = DB::table('pedido')->insertGetId([
                'cliente_id'    => $this->clienteSeleccionado['id'],
                'users_id'      => Auth::id(),
                'estado'        => 'pedido',
                'observaciones' => $this->observaciones ?: null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            foreach ($this->items as $item) {
                DB::table('pedido_detalle')->insert([
                    'pedido_id'       => $pedidoId,
                    'nombre_producto' => $item['nombre_producto'],
                    'cantidad'        => $item['cantidad'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            // Registrar en el sistema de flujo (tipo_flujo_id=1 = 'venta', tipo_tramite_id=1 = 'pedido')
            $flujoId = DB::table('flujo')->insertGetId([
                'tipo_flujo_id'   => 1,
                'identificacion'  => (string) $pedidoId,
                'nombre'          => $this->clienteSeleccionado['nombre'],
                'cliente_rtn'     => $this->clienteSeleccionado['rtn'] ?? null,
                'tipo_tramite_id' => 1,
                'created_by'      => Auth::id(),
                'updated_by'      => Auth::id(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            DB::table('historico_flujo')->insert([
                'flujo_id'        => $flujoId,
                'tipo_tramite_id' => 1, // 'pedido' en tipos_tramites
                'tramite_id'      => $pedidoId,
                'estado_id'       => DB::table('estado_venta')->where('descripcion', 'pendiente')->value('id'),
                'observaciones'   => null,
                'created_by'  => Auth::id(),
                'updated_by'  => Auth::id(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::commit();

            $this->pedidoGuardadoId = $pedidoId;
            $this->mensajeExito     = 'Pedido #' . $pedidoId . ' registrado exitosamente.';
            $this->mensajeError     = '';
            $this->limpiarCliente();
            $this->items        = [['nombre_producto' => '', 'cantidad' => 1]];
            $this->observaciones = '';

            // Desplazar al principio de la página para mostrar el panel de acciones
            $this->dispatchBrowserEvent('scroll-top');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->mensajeError = 'Error al registrar el pedido: ' . $e->getMessage();
        }
    }

    // ── Reiniciar para un nuevo pedido (desde el panel post-guardado) ──────
    public function nuevoPedido()
    {
        $this->pedidoGuardadoId = null;
        $this->mensajeExito     = '';
        $this->mensajeError     = '';
        $this->limpiarExcel();
    }

    // ── Cerrar modal de éxito sin reiniciar formulario ───────────────────
    public function cerrarModalPedidoGuardado()
    {
        $this->pedidoGuardadoId = null;
        $this->mensajeExito     = '';
        $this->mensajeError     = '';
    }

    // ── Render ─────────────────────────────────────────────────────────────
    public function render()
    {
        $layout = request()->has('embed') ? 'layouts.embed' : 'layouts.app';
        return view('livewire.flujo.pedido')->layout($layout);
    }
}
