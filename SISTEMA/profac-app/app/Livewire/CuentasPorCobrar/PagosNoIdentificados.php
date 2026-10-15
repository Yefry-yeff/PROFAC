<?php

namespace App\Livewire\CuentasPorCobrar;

use App\Livewire\Ventas\FacturacionCorporativa;
use App\Services\PagosNoIdentificadosService as PagosService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PagosNoIdentificados extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public array $filtros = [
        'desde' => '', 'hasta' => '', 'cliente' => '', 'vendedor' => '', 'teleasesor' => '',
        'gestor' => '', 'banco' => '', 'factura' => '', 'estado' => '', 'buscar' => '',
    ];
    public array $seleccionados = [];
    public array $filtrosEdicion = [];
    public string $buscarCliente = '';
    public string $buscarClienteFiltro = '';
    public string $buscarFactura = '';
    #[Locked]
    public string $modal = '';
    public array $nuevo = [
        'cliente_id' => '', 'monto' => '', 'fecha_pago' => '', 'hora_pago' => '', 'banco_id' => '',
        'cuenta_bancaria' => '', 'observaciones' => '',
    ];
    public string $clienteDestino = '';
    public string $facturaDestino = '';
    public string $montoAplicar = '';
    public string $observacionAplicacion = '';
    public bool $confirmarPeriodo = false;
    #[Locked]
    public string $claveRegistro = '';
    #[Locked]
    public string $claveAplicacion = '';
    #[Locked]
    public array $origenes = [];
    #[Locked]
    public array $saldos = [];
    #[Locked]
    public string $clienteFijo = '';
    #[Locked]
    public string $saldoFactura = '';
    #[Locked]
    public int $detalleId = 0;
    #[Locked]
    public int $operacionId = 0;
    #[Locked]
    public string $orden = 'p.fecha_pago';
    #[Locked]
    public string $direccion = 'desc';

    public function mount(): void
    {
        app(PagosService::class)->autorizar();
    }

    public function updatedFiltros(): void
    {
        $this->seleccionados = [];
        $this->resetPage();
    }

    public function ordenar(string $columna): void
    {
        if (!in_array($columna, ['p.id', 'p.fecha_pago', 'p.monto_original', 'p.saldo_disponible', 'p.created_at'], true)) {
            abort(422);
        }
        $this->direccion = $this->orden === $columna && $this->direccion === 'asc' ? 'desc' : 'asc';
        $this->orden = $columna;
        $this->resetPage();
    }

    public function seleccionarPagina(): void
    {
        $servicio = app(PagosService::class);
        $ids = $servicio->consulta($this->filtros)
            ->orderBy($this->orden, $this->direccion)->orderByDesc('p.id')
            ->forPage($this->getPage(), 25)->get()->filter(fn ($p) => (float) $p->saldo_disponible > 0)
            ->pluck('id')->map('strval')->all();
        $seleccion = array_values(array_unique(array_merge($this->seleccionados, $ids)));
        if (count($seleccion) > 100) {
            $this->addError('seleccion', 'Puede seleccionar como máximo 100 pagos.');
            return;
        }
        $this->seleccionados = $seleccion;
    }

    public function limpiarFiltros(): void
    {
        $this->filtros = array_fill_keys(array_keys($this->filtros), '');
        $this->filtrosEdicion = $this->filtros;
        $this->buscarClienteFiltro = '';
        $this->updatedFiltros();
    }

    public function abrirFiltros(): void
    {
        app(PagosService::class)->autorizar();
        $this->resetValidation();
        $this->filtrosEdicion = $this->filtros;
        $this->modal = 'filtros';
    }

    public function aplicarFiltros(): void
    {
        $reglas = [
            'filtrosEdicion.desde' => ['nullable', 'date_format:Y-m-d'],
            'filtrosEdicion.hasta' => ['nullable', 'date_format:Y-m-d'],
            'filtrosEdicion.buscar' => ['nullable', 'string', 'max:255'],
            'filtrosEdicion.factura' => ['nullable', 'string', 'max:255'],
        ];
        if (!empty($this->filtrosEdicion['desde']) && !empty($this->filtrosEdicion['hasta'])) {
            $reglas['filtrosEdicion.hasta'][] = 'after_or_equal:filtrosEdicion.desde';
        }
        $this->validate($reglas);
        $this->filtros = array_replace($this->filtros, $this->filtrosEdicion);
        $this->updatedFiltros();
        $this->cerrar();
    }

    public function quitarFiltro(string $campo): void
    {
        abort_unless(array_key_exists($campo, $this->filtros), 422);
        $this->filtros[$campo] = '';
        $this->updatedFiltros();
    }

    public function abrirNuevo(): void
    {
        app(PagosService::class)->autorizar();
        $this->resetValidation();
        $this->nuevo = [
            'cliente_id' => '', 'monto' => '', 'fecha_pago' => now()->toDateString(),
            'hora_pago' => now()->format('H:i'),
            'banco_id' => '', 'cuenta_bancaria' => '', 'observaciones' => '',
        ];
        $this->buscarCliente = '';
        $this->claveRegistro = (string) Str::uuid();
        $this->modal = 'nuevo';
    }

    public function updatedNuevoBancoId(): void
    {
        $this->nuevo['cuenta_bancaria'] = (string) (DB::table('banco')->whereNotIn('id', [12, 13])
            ->where('id', $this->nuevo['banco_id'] ?: 0)->value('cuenta') ?? '');
    }

    public function guardar(): void
    {
        $this->validate(['nuevo.hora_pago' => ['required', 'date_format:H:i']]);
        try {
            $id = app(PagosService::class)->registrar($this->nuevo, $this->claveRegistro);
            $this->cerrar();
            session()->flash('success', "Pago #{$id} registrado correctamente. Puede consultarlo por su ID.");
        } catch (QueryException $e) {
            report($e);
            $this->addError('registro', 'No se pudo registrar el depósito. Revise los datos e intente nuevamente.');
        }
    }

    public function abrirAplicacion(): void
    {
        $this->resetValidation();
        $pagos = app(PagosService::class)->seleccion($this->seleccionados);
        $this->origenes = $pagos->map(fn ($p) => (array) $p)->all();
        $this->saldos = $pagos->pluck('saldo_disponible', 'id')->all();
        $this->clienteFijo = (string) ($pagos->pluck('cliente_id')->filter()->first() ?? '');
        $this->clienteDestino = $this->clienteFijo;
        $this->facturaDestino = '';
        $this->saldoFactura = '';
        $this->montoAplicar = '';
        $this->confirmarPeriodo = false;
        $this->observacionAplicacion = '';
        $this->buscarCliente = '';
        $this->buscarFactura = '';
        $this->claveAplicacion = (string) Str::uuid();
        $this->modal = 'aplicar';
    }

    public function updatedClienteDestino(): void
    {
        $this->facturaDestino = '';
        $this->saldoFactura = '';
        $this->montoAplicar = '';
    }

    public function updatedFacturaDestino(): void
    {
        $cuenta = app(PagosService::class)->cuentas((int) $this->clienteDestino)
            ->where('f.id', (int) $this->facturaDestino)->first();
        $this->saldoFactura = $cuenta ? (string) $cuenta->saldo : '';
        $this->montoAplicar = $cuenta ? PagosService::decimal(min(
            PagosService::centavos($cuenta->saldo),
            array_sum(array_map(fn ($p) => PagosService::centavos($p['saldo_disponible']), $this->origenes))
        )) : '';
    }

    public function aplicar(): void
    {
        try {
            $id = app(PagosService::class)->aplicar([
                'pagos' => array_column($this->origenes, 'id'),
                'cliente_id' => $this->clienteDestino,
                'factura_id' => $this->facturaDestino,
                'monto' => $this->montoAplicar,
                'saldos' => $this->saldos,
                'saldo_factura' => $this->saldoFactura,
                'clave' => $this->claveAplicacion,
                'confirmar_periodo' => $this->confirmarPeriodo,
                'observaciones' => $this->observacionAplicacion,
            ]);
            $this->cerrar();
            $this->seleccionados = [];
            session()->flash('success', "Aplicación #{$id} registrada correctamente.");
        } catch (QueryException $e) {
            report($e);
            $this->addError('aplicacion', 'No se pudo completar la aplicación. La operación fue revertida. Puede reintentar.');
        }
    }

    public function verDetalle(int $id): void
    {
        app(PagosService::class)->autorizar();
        abort_unless(DB::table('pagos_no_identificados')->where('id', $id)->exists(), 404);
        $this->detalleId = $id;
        $this->operacionId = 0;
        $this->resetValidation();
        $this->modal = 'detalle';
    }

    public function verOperacion(int $id): void
    {
        app(PagosService::class)->autorizar();
        abort_unless(DB::table('pni_aplicaciones')->where('pago_id', $this->detalleId)->where('operacion_id', $id)->exists(), 404);
        $this->operacionId = $id;
    }

    public function descargarFactura(int $facturaId): StreamedResponse
    {
        app(PagosService::class)->autorizar();
        abort_unless($this->modal === 'detalle' && DB::table('pni_aplicaciones as a')
            ->join('pni_operaciones as o', 'o.id', '=', 'a.operacion_id')
            ->where('a.pago_id', $this->detalleId)->where('o.factura_id', $facturaId)->exists(), 404);

        $pdf = app(FacturacionCorporativa::class)->imprimirFacturaCoorporativa($facturaId);

        return response()->streamDownload(function () use ($pdf) {
            $pdf->sendContent();
        }, "factura-{$facturaId}.pdf", ['Content-Type' => 'application/pdf']);
    }

    public function cerrar(): void
    {
        $this->modal = '';
        $this->resetValidation();
    }

    public function render()
    {
        $servicio = app(PagosService::class);
        $servicio->autorizar();
        $reglas = [
            'filtros.desde' => ['nullable', 'date_format:Y-m-d'],
            'filtros.hasta' => ['nullable', 'date_format:Y-m-d'],
            'filtros.buscar' => ['nullable', 'string', 'max:255'],
        ];
        if ($this->filtros['desde'] && $this->filtros['hasta']) {
            $reglas['filtros.hasta'][] = 'after_or_equal:filtros.desde';
        }
        $validador = Validator::make(['filtros' => $this->filtros], $reglas);
        $this->resetValidation(['filtros.desde', 'filtros.hasta', 'filtros.buscar']);
        if ($validador->fails()) {
            foreach ($validador->errors()->messages() as $key => $messages) {
                $this->addError($key, $messages[0]);
            }
        }
        $consulta = $servicio->consulta($validador->fails() ? [] : $this->filtros);
        if ($validador->fails()) {
            $consulta->whereRaw('1 = 0');
        }
        $resumen = (clone $consulta)->reorder()->select([])->selectRaw(
            'COUNT(*) AS cantidad, COALESCE(SUM(p.monto_original), 0) AS depositos, COALESCE(SUM(p.saldo_disponible), 0) AS disponible'
        )->first();
        $pagos = $consulta->orderBy($this->orden, $this->direccion)->orderByDesc('p.id')->paginate(25);
        $clienteFiltro = $this->modal === 'filtros' ? ($this->filtrosEdicion['cliente'] ?? '') : $this->filtros['cliente'];
        $clientesFiltro = DB::table('cliente')->where(fn ($q) => $q
            ->where('nombre', 'like', '%' . $this->buscarClienteFiltro . '%')
            ->orWhere('id', (int) $this->buscarClienteFiltro)
            ->orWhere('id', (int) $clienteFiltro))
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [(int) $clienteFiltro])
            ->orderBy('nombre')->limit(50)->get(['id', 'nombre']);
        $bancos = DB::table('banco')->whereNotIn('id', [12, 13])->orderBy('nombre')->get();
        $usuarios = DB::table('users')->orderBy('name')->get(['id', 'name']);
        $clientes = in_array($this->modal, ['nuevo', 'aplicar'], true)
            ? DB::table('cliente')->orderBy('nombre')->get(['id', 'nombre'])
            : collect();
        $facturas = collect();
        if ($this->modal === 'aplicar' && $this->clienteDestino) {
            $facturas = $servicio->cuentas((int) $this->clienteDestino)
                ->orderBy('f.fecha_vencimiento')->get();
        }
        $total = array_sum(array_map(fn ($p) => PagosService::centavos($p['saldo_disponible']), $this->origenes));
        $maximoAplicable = $this->saldoFactura !== '' ? min($total, PagosService::centavos($this->saldoFactura)) : 0;
        $distribucion = [];
        $saldoDespues = null;
        $traslados = [];
        if ($this->modal === 'aplicar' && preg_match('/^\d{1,10}(?:\.\d{1,2})?$/D', $this->montoAplicar)) {
            $monto = PagosService::centavos($this->montoAplicar);
            if ($monto > 0 && $monto <= $maximoAplicable && $this->saldoFactura !== '') {
                $saldoDespues = PagosService::centavos($this->saldoFactura) - $monto;
            }
            if ($monto > 0 && $monto <= $total) {
                $distribucion = $servicio->distribuir(collect($this->origenes)->map(fn ($p) => (object) $p), $monto);
                foreach ($distribucion as $linea) {
                    $periodo = $servicio->periodo($linea['pago']->fecha_pago);
                    if ($periodo['original'] !== $periodo['asignado']) {
                        $traslados[] = ['pago' => $linea['pago']->id] + $periodo;
                    }
                }
            }
        }
        $detalle = $this->modal === 'detalle'
            ? $servicio->consulta([])->where('p.id', $this->detalleId)->first() : null;
        $historial = $detalle ? $servicio->historial($this->detalleId) : collect();
        $totalSeleccionado = DB::table('pagos_no_identificados')->whereIn('id', $this->seleccionados)->sum('saldo_disponible');
        $identificador = $detalle && $detalle->identificado_por
            ? DB::table('users')->where('id', $detalle->identificado_por)->value('name') : null;
        $operacionDetalle = $detalle && $this->operacionId ? DB::table('pni_aplicaciones as a')
            ->join('pagos_no_identificados as p', 'p.id', '=', 'a.pago_id')
            ->join('banco as b', 'b.id', '=', 'p.banco_id')
            ->where('a.operacion_id', $this->operacionId)
            ->select('a.*', 'b.nombre as banco', 'p.cuenta_bancaria', 'p.fecha_pago', 'p.hora_pago')->orderBy('a.id')->get() : collect();
        return view('livewire.cuentasporcobrar.pagosnoidentificados', compact(
            'pagos', 'bancos', 'usuarios', 'clientes', 'clientesFiltro', 'facturas', 'total', 'distribucion', 'maximoAplicable',
            'traslados', 'detalle', 'historial', 'totalSeleccionado', 'identificador', 'operacionDetalle', 'resumen', 'saldoDespues'
        ));
    }
}
