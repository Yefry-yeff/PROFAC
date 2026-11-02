<?php

namespace App\Livewire\Reportes;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\CarbonImmutable;
use App\Http\Controllers\MenuHelper;
use App\Exports\CarteraCobrosExport;
use Maatwebsite\Excel\Facades\Excel;

class CarteraDeCobros extends Component
{
    use WithPagination;

    public string $titulo = 'Cartera de Cobros';
    public string $buscar = '';
    public string $cliente = '';
    public string $asesor = '';
    public string $teleasesor = '';
    public string $gestor = '';
    public string $tramo = '';
    public string $estado = '';
    public string $mes = '';
    public string $orden = 'saldo';
    public string $usuario = '';
    public string $inicio = '';
    public string $fin = '';
    public string $compararInicio = '';
    public string $compararFin = '';
    public string $modoPeriodo = 'mensual';
    public string $mesA = '';
    public string $mesB = '';
    protected $paginationTheme = 'bootstrap';

    public const TRAMOS = [
        'corriente' => 'Por vencer',
        '1-30' => '1 a 30 días',
        '31-60' => '31 a 60 días',
        '61-90' => '61 a 90 días',
        '91-180' => '91 a 180 días',
        '181+' => 'Más de 180 días',
        'sin-fecha' => 'Sin vencimiento',
    ];

    public function boot(): void
    {
        abort_unless(auth()->check() && MenuHelper::tieneAcceso('reportes/cartera_de_cobros'), 403);
    }

    public function mount(): void
    {
        $hoy = CarbonImmutable::today();
        $this->inicio = $hoy->startOfMonth()->toDateString();
        $this->fin = $hoy->toDateString();
        $this->compararInicio = $hoy->subMonthNoOverflow()->startOfMonth()->toDateString();
        $this->compararFin = $hoy->subMonthNoOverflow()->endOfMonth()->toDateString();
        $this->mesA = $hoy->format('Y-m');
        $this->mesB = $hoy->subMonthNoOverflow()->format('Y-m');
    }

    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, ['modoPeriodo', 'mesA', 'mesB'], true)) {
            $this->fechasValidas();
        }
        $this->resetPage();
    }

    public function seleccionar(string $dimension, string $valor): void
    {
        $validos = ['cliente', 'asesor', 'teleasesor', 'gestor', 'tramo', 'estado', 'mes', 'usuario'];
        abort_unless(in_array($dimension, $validos, true), 422);
        abort_if($dimension === 'tramo' && !array_key_exists($valor, self::TRAMOS) && $valor !== '', 422);
        abort_if($dimension === 'estado' && !in_array($valor, ['', 'vencida', 'corriente', 'critica', 'sin-fecha'], true), 422);
        $this->$dimension = $this->$dimension === $valor ? '' : $valor;
        $this->resetPage();
    }

    public function limpiar(): void
    {
        $this->reset('buscar', 'cliente', 'asesor', 'teleasesor', 'gestor', 'tramo', 'estado', 'mes', 'orden', 'usuario');
        $this->resetPage();
    }

    private function cartera(bool $soloPendiente = true): Collection
    {
        $hoy = CarbonImmutable::today();
        $cuentas = DB::table('aplicacion_pagos')->where('estado', 1)
            ->selectRaw('MAX(id) AS id, factura_id')->groupBy('factura_id');
        $cargo = 'CASE WHEN f.tipo_venta_id = 3 THEN f.sub_total ELSE f.total END';
        $saldo = "ROUND(CASE WHEN ap.id IS NULL THEN $cargo
            ELSE ap.saldo + $cargo - COALESCE(ap.total_factura_cargo, $cargo) END, 2)";

        $query = DB::table('factura as f')
            ->leftJoinSub($cuentas, 'ultima', fn ($join) => $join->on('ultima.factura_id', '=', 'f.id'))
            ->leftJoin('aplicacion_pagos as ap', 'ap.id', '=', 'ultima.id')
            ->leftJoin('cliente as c', 'c.id', '=', 'f.cliente_id')
            ->leftJoin('users as a', 'a.id', '=', 'f.vendedor')
            ->leftJoin('users as t', 't.id', '=', 'f.users_id')
            ->leftJoin('users as g', 'g.id', '=', 'f.gestor_entrega')
            ->where('f.estado_venta_id', 1);
        if ($soloPendiente) {
            $query->where(fn ($q) => $q->whereNull('ap.estado_cerrado')->orWhere('ap.estado_cerrado', '<>', 2))
                ->whereRaw("$saldo > 0");
        }

        return $query
            ->select('f.id', 'f.cliente_id', 'f.cai', 'f.numero_secuencia_cai', 'f.fecha_emision',
                'f.fecha_vencimiento', 'f.vendedor as asesor_id', 'f.users_id as teleasesor_id',
                'f.gestor_entrega as gestor_id', 'c.nombre as cliente', 'a.name as asesor',
                't.name as teleasesor', 'g.name as gestor')
            ->selectRaw("$saldo AS saldo")
            ->get()->map(function ($fila) use ($hoy) {
                $vencimiento = $fila->fecha_vencimiento && $fila->fecha_vencimiento !== '0000-00-00'
                    ? CarbonImmutable::parse($fila->fecha_vencimiento)->startOfDay() : null;
                $emision = $fila->fecha_emision && $fila->fecha_emision !== '0000-00-00'
                    ? CarbonImmutable::parse($fila->fecha_emision)->startOfDay() : null;
                $fila->centavos = (int) round((float) $fila->saldo * 100);
                $fila->dias = $vencimiento ? (int) $vencimiento->diffInDays($hoy, false) : null;
                $fila->antiguedad = $emision ? max(0, (int) $emision->diffInDays($hoy, false)) : null;
                $fila->mes = $vencimiento ? $vencimiento->format('Y-m') : 'sin-fecha';
                $fila->tramo = match (true) {
                    $fila->dias === null => 'sin-fecha',
                    $fila->dias <= 0 => 'corriente',
                    $fila->dias <= 30 => '1-30',
                    $fila->dias <= 60 => '31-60',
                    $fila->dias <= 90 => '61-90',
                    $fila->dias <= 180 => '91-180',
                    default => '181+',
                };
                $fila->cliente = $fila->cliente ?: 'Cliente #' . $fila->cliente_id;
                foreach (['asesor', 'teleasesor', 'gestor'] as $rol) {
                    $id = $rol . '_id';
                    $fila->$id = $fila->$id ?: 0;
                    $fila->$rol = $fila->$rol ?: 'Sin asignar';
                }

                return $fila;
            });
    }

    private function filtrar(Collection $filas): Collection
    {
        $cohorteUsuario = null;
        if ($this->usuario !== '') {
            if (!$this->fechasValidas()) return collect();
            $cohorteUsuario = DB::table('abonos_creditos')->where('estado_abono', 1)
                ->where('monto_abonado', '>', 0)
                ->where(fn ($q) => $this->usuario === '0'
                    ? $q->whereNull('usr_registro')->orWhere('usr_registro', 0)
                    : $q->where('usr_registro', $this->usuario))
                ->where(fn ($q) => $this->limitarPeriodos($q, 'fecha_pago'))
                ->pluck('factura_id')->flip();
        }
        return $filas->filter(function ($f) use ($cohorteUsuario) {
            if ($cohorteUsuario !== null && !$cohorteUsuario->has($f->id)) return false;
            foreach (['cliente', 'asesor', 'teleasesor', 'gestor'] as $campo) {
                if ($this->$campo !== '' && (string) $f->{$campo . '_id'} !== $this->$campo) {
                    return false;
                }
            }
            if ($this->tramo !== '' && $f->tramo !== $this->tramo) return false;
            if ($this->mes !== '' && $f->mes !== $this->mes) return false;
            if ($this->estado === 'vencida' && ($f->dias === null || $f->dias <= 0)) return false;
            if ($this->estado === 'corriente' && ($f->dias === null || $f->dias > 0)) return false;
            if ($this->estado === 'critica' && ($f->dias === null || $f->dias <= 90)) return false;
            if ($this->estado === 'sin-fecha' && $f->dias !== null) return false;
            $busqueda = trim($this->buscar);

            return $busqueda === '' || mb_stripos(implode(' ', [
                $f->cliente_id, $f->cliente, $f->cai, $f->numero_secuencia_cai,
            ]), $busqueda) !== false;
        })->values();
    }

    private function ranking(Collection $filas, string $campo): Collection
    {
        return $filas->groupBy($campo . '_id')->map(function ($grupo, $id) use ($campo) {
            return [
                'id' => (string) $id, 'nombre' => $grupo->first()->$campo,
                'saldo' => $grupo->sum('centavos'), 'facturas' => $grupo->count(),
                'vencido' => $grupo->filter(fn ($f) => $f->dias !== null && $f->dias > 0)->sum('centavos'),
                'dias' => $grupo->max('dias'), 'antiguedad' => $grupo->max('antiguedad'),
            ];
        })->sortByDesc('saldo')->values();
    }

    public function render()
    {
        $base = $this->cartera();
        $filas = $this->filtrar($base);
        $vencidas = $filas->filter(fn ($f) => $f->dias !== null && $f->dias > 0);
        $total = $filas->sum('centavos');
        $clientes = $this->ranking($filas, 'cliente');
        $metricas = [
            'total' => $total,
            'vencido' => $vencidas->sum('centavos'),
            'corriente' => $filas->where('tramo', 'corriente')->sum('centavos'),
            'critica' => $filas->filter(fn ($f) => $f->dias !== null && $f->dias > 90)->sum('centavos'),
            'sinFecha' => $filas->where('tramo', 'sin-fecha')->sum('centavos'),
            'clientes' => $clientes->count(), 'facturas' => $filas->count(),
            'mora' => $vencidas->sum('centavos') > 0
                ? round($vencidas->sum(fn ($f) => $f->centavos * $f->dias) / $vencidas->sum('centavos'), 1) : 0,
        ];
        $antigua = $filas->whereNotNull('antiguedad')->sortByDesc('antiguedad')->first();
        $tramos = collect(self::TRAMOS)->map(fn ($nombre, $id) => [
            'id' => $id, 'nombre' => $nombre, 'saldo' => $filas->where('tramo', $id)->sum('centavos'),
            'facturas' => $filas->where('tramo', $id)->count(),
        ]);
        $responsables = collect(['asesor' => 'Asesor comercial', 'teleasesor' => 'Teleasesor', 'gestor' => 'Gestor de entrega'])
            ->map(fn ($nombre, $campo) => ['nombre' => $nombre, 'filas' => $this->ranking($filas, $campo)]);
        $meses = $filas->groupBy('mes')->sortKeys()->map(fn ($grupo, $mes) => [
            'id' => $mes, 'saldo' => $grupo->sum('centavos'), 'facturas' => $grupo->count(),
        ]);
        $catalogos = [];
        $universo = $this->cartera(false);
        foreach (['cliente', 'asesor', 'teleasesor', 'gestor'] as $campo) {
            $catalogos[$campo] = $universo->unique($campo . '_id')->sortBy($campo)
                ->mapWithKeys(fn ($f) => [(string) $f->{$campo . '_id'} => $f->$campo]);
        }
        $ordenadas = match ($this->orden) {
            'mora' => $filas->sortByDesc(fn ($f) => $f->dias ?? -PHP_INT_MAX),
            'antiguedad' => $filas->sortByDesc(fn ($f) => $f->antiguedad ?? -1),
            default => $filas->sortByDesc('centavos'),
        };
        $detalle = new LengthAwarePaginator(
            $ordenadas->values()->forPage($this->getPage(), 20)->values(),
            $filas->count(), 20, $this->getPage(), ['path' => request()->url()]
        );

        $recuperacion = $this->recuperacion($universo, $filas);
        return view('livewire.reportes.carteradecobros', compact(
            'metricas', 'antigua', 'tramos', 'responsables', 'clientes', 'meses', 'catalogos', 'detalle', 'recuperacion'
        ))->layout('layouts.app', ['title' => $this->titulo]);
    }

    private function fechasValidas(): bool
    {
        $modo = validator(['modoPeriodo' => $this->modoPeriodo], ['modoPeriodo' => 'required|in:mensual,detalle']);
        if ($modo->fails()) {
            $this->setErrorBag($modo->errors());
            return false;
        }
        if ($this->modoPeriodo === 'mensual') {
            $meses = validator(['mesA' => $this->mesA, 'mesB' => $this->mesB], [
                'mesA' => 'required|date_format:Y-m',
                'mesB' => 'required|date_format:Y-m',
            ]);
            $meses->fails();
            foreach (['mesA', 'mesB'] as $campo) {
                if ($this->$campo > CarbonImmutable::today()->format('Y-m')) {
                    $meses->errors()->add($campo, 'Selecciona un mes que no sea posterior al actual.');
                }
            }
            if ($meses->errors()->isNotEmpty()) {
                $this->setErrorBag($meses->errors());
                return false;
            }
            foreach (['mesA' => ['inicio', 'fin'], 'mesB' => ['compararInicio', 'compararFin']] as $campo => [$desde, $hasta]) {
                $mes = CarbonImmutable::createFromFormat('!Y-m', $this->$campo);
                $this->$desde = $mes->startOfMonth()->toDateString();
                $this->$hasta = $mes->endOfMonth()->min(CarbonImmutable::today())->toDateString();
            }
        }
        $validador = validator([
            'inicio' => $this->inicio, 'fin' => $this->fin,
            'compararInicio' => $this->compararInicio, 'compararFin' => $this->compararFin,
        ], [
            'inicio' => 'required|date_format:Y-m-d',
            'fin' => 'required|date_format:Y-m-d|after_or_equal:inicio|before_or_equal:today',
            'compararInicio' => 'required|date_format:Y-m-d',
            'compararFin' => 'required|date_format:Y-m-d|after_or_equal:compararInicio|before_or_equal:today',
        ]);
        if ($validador->fails()) {
            $this->setErrorBag($validador->errors());
            return false;
        }
        $this->resetErrorBag();
        return true;
    }

    private function recuperacion(Collection $universo, Collection $pendientes): array
    {
        $valida = $this->fechasValidas();
        $facturas = $this->filtrar($universo)->keyBy('id');
        $pagos = collect();
        if ($valida && $facturas->isNotEmpty()) {
            $pagos = DB::table('abonos_creditos as ac')
                ->leftJoin('users as u', 'u.id', '=', 'ac.usr_registro')
                ->where('ac.estado_abono', 1)
                ->where('ac.monto_abonado', '>', 0)
                ->where(fn ($q) => $this->limitarPeriodos($q, 'ac.fecha_pago'))
                ->select('ac.id', 'ac.factura_id', 'ac.fecha_pago', 'ac.monto_abonado', 'ac.usr_registro', 'u.name as usuario')
                ->orderBy('ac.fecha_pago')->orderBy('ac.id')->get()
                ->filter(fn ($p) => $facturas->has($p->factura_id))
                ->map(function ($p) use ($facturas) {
                    $p->factura = $facturas[$p->factura_id];
                    $p->centavos = (int) round((float) $p->monto_abonado * 100);
                    $p->usuario_id = (string) ($p->usr_registro ?: 0);
                    $p->usuario = $p->usuario ?: 'Sin usuario';
                    $p->fecha = substr($p->fecha_pago, 0, 10);
                    return $p;
                })->values();
        }
        $usuarios = $pagos->unique('usuario_id')->sortBy('usuario')->pluck('usuario', 'usuario_id');
        // El usuario registrador filtra la cohorte de facturas, no solo el numerador.
        if ($this->usuario !== '') {
            $ids = $pagos->where('usuario_id', $this->usuario)->pluck('factura_id')->unique();
            $pendientes = $pendientes->whereIn('id', $ids);
            $pagos = $pagos->where('usuario_id', $this->usuario)->values();
        }
        $actual = $pagos->filter(fn ($p) => $p->fecha >= $this->inicio && $p->fecha <= $this->fin)->values();
        $anterior = $pagos->filter(fn ($p) => $p->fecha >= $this->compararInicio && $p->fecha <= $this->compararFin)->values();
        $saldo = $pendientes->sum('centavos');
        $tasa = static fn ($cobrado, $saldo) => $cobrado + $saldo > 0 ? round($cobrado * 100 / ($cobrado + $saldo), 2) : null;
        $comparacion = collect([
            ['periodo' => 'A', 'inicio' => $this->inicio, 'fin' => $this->fin, 'cobrado' => $actual->sum('centavos'), 'abonos' => $actual->count()],
            ['periodo' => 'B', 'inicio' => $this->compararInicio, 'fin' => $this->compararFin, 'cobrado' => $anterior->sum('centavos'), 'abonos' => $anterior->count()],
        ])->map(fn ($p) => $p + ['saldo' => $saldo, 'porcentaje' => $valida ? $tasa($p['cobrado'], $saldo) : null]);
        $grupos = [];
        foreach (['cliente', 'asesor', 'teleasesor', 'gestor', 'usuario'] as $campo) {
            $ids = $pagos->map(fn ($p) => $campo === 'usuario' ? $p->usuario_id : (string) $p->factura->{$campo . '_id'})
                ->merge($campo === 'usuario' ? [] : $pendientes->pluck($campo . '_id')->map('strval'))->unique();
            $grupos[$campo] = $ids->map(function ($id) use ($campo, $actual, $anterior, $pagos, $pendientes, $tasa, $valida) {
                $pertenece = fn ($p) => (string) ($campo === 'usuario' ? $p->usuario_id : $p->factura->{$campo . '_id'}) === (string) $id;
                $pago = $pagos->first($pertenece);
                $pendienteGrupo = $campo === 'usuario'
                    ? $pendientes->whereIn('id', $pagos->filter($pertenece)->pluck('factura_id')->unique())
                    : $pendientes->where($campo . '_id', $id);
                $nombre = $campo === 'usuario' ? $pago?->usuario : ($pago?->factura->$campo ?? $pendienteGrupo->first()?->$campo);
                $a = $actual->filter($pertenece)->sum('centavos');
                $b = $anterior->filter($pertenece)->sum('centavos');
                $s = $pendienteGrupo->sum('centavos');
                return ['id' => (string) $id, 'nombre' => $nombre, 'actual' => $a, 'anterior' => $b,
                    'saldo' => $s, 'porcentaje' => $valida ? $tasa($a, $s) : null,
                    'porcentajeB' => $valida ? $tasa($b, $s) : null, 'diferencia' => $a - $b];
            })->sortByDesc('actual')->values();
        }
        $evolucion = $actual->groupBy(fn ($p) => substr($p->fecha, 0, 7))->sortKeys()
            ->map(fn ($grupo, $mes) => ['mes' => $mes, 'cobrado' => $grupo->sum('centavos'), 'abonos' => $grupo->count()])->values();
        return compact('valida', 'comparacion', 'grupos', 'actual', 'anterior', 'evolucion', 'usuarios');
    }

    private function limitarPeriodos(Builder $query, string $campo): void
    {
        $query->where(fn ($q) => $q->where($campo, '>=', $this->inicio)
            ->where($campo, '<', CarbonImmutable::parse($this->fin)->addDay()->toDateString()))
            ->orWhere(fn ($q) => $q->where($campo, '>=', $this->compararInicio)
                ->where($campo, '<', CarbonImmutable::parse($this->compararFin)->addDay()->toDateString()));
    }

    public function descargar(string $tabla = 'todas')
    {
        $permitidas = ['todas', 'detalle', 'clientes', 'antiguedad', 'asesor', 'teleasesor', 'gestor', 'meses',
            'comparacion', 'recuperacion-cliente', 'recuperacion-asesor', 'recuperacion-teleasesor',
            'recuperacion-gestor', 'recuperacion-usuario', 'abonos', 'evolucion'];
        abort_unless(in_array($tabla, $permitidas, true), 422);
        if (!$this->fechasValidas()) return;
        $filas = $this->filtrar($this->cartera());
        $recuperacion = $this->recuperacion($this->cartera(false), $filas);
        $tablas = [];
        $tablas['detalle'] = ['Detalle cartera', ['ID factura', 'Cliente', 'Factura', 'Emision', 'Vencimiento', 'Saldo', 'Dias mora', 'Asesor', 'Teleasesor', 'Gestor'],
            $filas->map(fn ($f) => [$f->id, $f->cliente, $f->cai ?: $f->numero_secuencia_cai, $f->fecha_emision, $f->fecha_vencimiento, $f->centavos / 100, $f->dias, $f->asesor, $f->teleasesor, $f->gestor])->all()];
        foreach (['cliente' => 'clientes', 'asesor' => 'asesor', 'teleasesor' => 'teleasesor', 'gestor' => 'gestor'] as $campo => $clave) {
            $tablas[$clave] = [ucfirst($clave), ['Nombre', 'Saldo', 'Vencido', 'Facturas', 'Mora maxima', 'Antiguedad maxima'],
                $this->ranking($filas, $campo)->map(fn ($g) => [$g['nombre'], $g['saldo'] / 100, $g['vencido'] / 100, $g['facturas'], $g['dias'], $g['antiguedad']])->all()];
        }
        $tablas['antiguedad'] = ['Antiguedad', ['Tramo', 'Saldo', 'Facturas'], collect(self::TRAMOS)->map(fn ($n, $id) => [$n, $filas->where('tramo', $id)->sum('centavos') / 100, $filas->where('tramo', $id)->count()])->values()->all()];
        $tablas['meses'] = ['Vencimientos', ['Mes', 'Saldo', 'Facturas'], $filas->groupBy('mes')->sortKeys()->map(fn ($g, $m) => [$m, $g->sum('centavos') / 100, $g->count()])->values()->all()];
        $tablas['comparacion'] = ['Comparacion', ['Periodo', 'Inicio', 'Fin', 'Cobrado', 'Saldo actual', 'Recuperacion %', 'Abonos'],
            $recuperacion['comparacion']->map(fn ($p) => [$p['periodo'], $p['inicio'], $p['fin'], $p['cobrado'] / 100, $p['saldo'] / 100, $p['porcentaje'], $p['abonos']])->all()];
        foreach ($recuperacion['grupos'] as $campo => $grupos) {
            $tablas['recuperacion-' . $campo] = ['Recuperacion ' . $campo, ['Nombre', 'Cobrado A', 'Cobrado B', 'Diferencia', 'Saldo actual', 'Recuperacion A %', 'Recuperacion B %'],
                $grupos->map(fn ($g) => [$g['nombre'], $g['actual'] / 100, $g['anterior'] / 100, $g['diferencia'] / 100, $g['saldo'] / 100, $g['porcentaje'], $g['porcentajeB']])->all()];
        }
        $tablas['evolucion'] = ['Evolucion', ['Mes', 'Cobrado A', 'Abonos'], $recuperacion['evolucion']->map(fn ($g) => [$g['mes'], $g['cobrado'] / 100, $g['abonos']])->all()];
        $abonos = collect();
        foreach (['A' => 'actual', 'B' => 'anterior'] as $periodo => $clave) {
            foreach ($recuperacion[$clave] as $p) {
                $abonos->push([$periodo, $p->id, $p->fecha, $p->factura->cliente, $p->factura->cai, $p->centavos / 100,
                    $p->factura->asesor, $p->factura->teleasesor, $p->factura->gestor, $p->usuario]);
            }
        }
        $tablas['abonos'] = ['Abonos recuperados', ['Periodo', 'ID abono', 'Fecha', 'Cliente', 'Factura', 'Aplicado a deuda', 'Asesor', 'Teleasesor', 'Gestor', 'Usuario registrador'], $abonos->all()];
        $contexto = [
            ['Generado', CarbonImmutable::now()->toDateTimeString()],
            ['Modo de comparacion', $this->modoPeriodo === 'mensual' ? 'Mensual (mes actual hasta hoy)' : 'Rangos de fechas detallados'],
            ['Periodo A', $this->inicio . ' a ' . $this->fin],
            ['Periodo B', $this->compararInicio . ' a ' . $this->compararFin],
            ['Recuperacion %', 'Cobrado del periodo / (cobrado del periodo + saldo pendiente actual) x 100. Sin base si ambos son cero.'],
            ['Atribucion', 'Responsables actuales de la factura; usuario registrador del abono. Los saldos por usuario pueden solaparse y no deben sumarse.'],
            ['Periodos superpuestos', 'Un mismo abono puede aparecer en A y B. No sumar ambos periodos como cobros unicos.'],
            ['Fechas de cartera', 'Mora y vencimiento evaluados hoy, no al inicio de cada periodo.'],
        ];
        foreach (['buscar', 'cliente', 'asesor', 'teleasesor', 'gestor', 'tramo', 'estado', 'mes', 'usuario'] as $campo) {
            $contexto[] = ['Filtro ' . $campo, $this->$campo === '' ? 'Todos' : $this->$campo];
        }
        $seleccion = $tabla === 'todas' ? array_values($tablas) : [$tablas[$tabla]];
        $seleccion[] = ['Criterios', ['Concepto', 'Valor'], $contexto];
        return Excel::download(new CarteraCobrosExport($seleccion),
            'Cartera-' . $tabla . '-' . CarbonImmutable::today()->toDateString() . '.xlsx');
    }
}
