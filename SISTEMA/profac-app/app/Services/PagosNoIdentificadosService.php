<?php

namespace App\Services;

use App\Http\Controllers\MenuHelper;
use App\Services\Comisiones\AplicadorRetencionesMora;
use App\Services\Comisiones\GeneradorFacturasComision;
use App\Services\Comisiones\ProcesadorComisiones;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PagosNoIdentificadosService
{
    public const URL = 'cuentas_por_cobrar/pagos_no_identificados';
    public const ESTADOS = [
        'pendiente' => 'Pendiente de Identificación',
        'parcial' => 'Parcialmente Aplicado',
        'aplicado' => 'Aplicado Totalmente',
    ];

    public function autorizar(): void
    {
        abort_unless(Auth::check() && MenuHelper::tieneAcceso(self::URL), 403);
    }

    public static function centavos(string $monto): int
    {
        if (!preg_match('/^\d{1,10}(?:\.\d{1,2})?$/D', $monto)) {
            throw ValidationException::withMessages(['monto' => 'Ingrese un importe válido con máximo dos decimales (hasta 9.999.999.999,99).']);
        }
        [$entero, $decimal] = array_pad(explode('.', $monto, 2), 2, '');

        return (int) $entero * 100 + (int) str_pad($decimal, 2, '0');
    }

    public static function decimal(int $centavos): string
    {
        return intdiv($centavos, 100) . '.' . str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
    }

    public function registrar(array $datos, string $clave): int
    {
        $this->autorizar();
        Validator::make($datos + ['clave' => $clave], [
            'cliente_id' => ['nullable', 'integer', 'exists:cliente,id'],
            'monto' => ['required', 'regex:/^\d{1,10}(?:\.\d{1,2})?$/D'],
            'fecha_pago' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'hora_pago' => ['nullable', 'date_format:H:i'],
            'banco_id' => ['required', 'integer', Rule::exists('banco', 'id')->whereNotIn('id', [12, 13])],
            'cuenta_bancaria' => ['required', 'string', 'max:45'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'clave' => ['required', 'uuid'],
        ])->validate();
        if (!empty($datos['hora_pago']) && Carbon::parse($datos['fecha_pago'] . ' ' . $datos['hora_pago'])->isFuture()) {
            throw ValidationException::withMessages(['hora_pago' => 'La fecha y hora del depósito no pueden ser futuras.']);
        }
        $monto = self::centavos((string) $datos['monto']);
        if ($monto === 0) {
            throw ValidationException::withMessages(['monto' => 'El depósito debe ser mayor que cero.']);
        }

        return DB::transaction(function () use ($datos, $clave, $monto) {
            DB::table('users')->where('id', Auth::id())->lockForUpdate()->first();
            $anterior = DB::table('pagos_no_identificados')->where('clave_registro', $clave)->first();
            if ($anterior) {
                abort_unless((int) $anterior->creado_por === Auth::id(), 403);
                if (self::centavos($anterior->monto_original) !== $monto
                    || (int) ($anterior->cliente_original_conocido ? $anterior->cliente_id : 0) !== (int) ($datos['cliente_id'] ?? 0)
                    || $anterior->fecha_pago !== $datos['fecha_pago']
                    || $anterior->hora_pago !== (empty($datos['hora_pago']) ? null : $datos['hora_pago'] . ':00')
                    || (int) $anterior->banco_id !== (int) $datos['banco_id']
                    || $anterior->cuenta_bancaria !== $datos['cuenta_bancaria']
                    || (string) $anterior->observaciones !== (string) ($datos['observaciones'] ?? '')) {
                    throw ValidationException::withMessages(['monto' => 'La solicitud de registro ya fue utilizada con otros datos.']);
                }
                return (int) $anterior->id;
            }
            $banco = DB::table('banco')->where('id', $datos['banco_id'])->lockForUpdate()->first();
            if (!$banco || $banco->cuenta !== $datos['cuenta_bancaria']) {
                throw ValidationException::withMessages(['cuenta_bancaria' => 'La cuenta no corresponde al banco seleccionado.']);
            }
            return DB::table('pagos_no_identificados')->insertGetId([
                'cliente_id' => ($datos['cliente_id'] ?? null) ?: null,
                'cliente_original_conocido' => !empty($datos['cliente_id']),
                'banco_id' => $banco->id,
                'cuenta_bancaria' => $banco->cuenta,
                'monto_original' => self::decimal($monto),
                'saldo_disponible' => self::decimal($monto),
                'fecha_pago' => $datos['fecha_pago'],
                'hora_pago' => empty($datos['hora_pago']) ? null : $datos['hora_pago'] . ':00',
                'observaciones' => $datos['observaciones'] ?? null,
                'creado_por' => Auth::id(),
                'clave_registro' => $clave,
                'estado' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function consulta(array $filtros): Builder
    {
        $this->autorizar();
        $query = DB::table('pagos_no_identificados as p')
            ->leftJoin('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->join('banco as b', 'b.id', '=', 'p.banco_id')
            ->join('users as u', 'u.id', '=', 'p.creado_por')
            ->select('p.*', 'c.nombre as cliente', 'b.nombre as banco', 'u.name as creador');
        $query->when($filtros['desde'] ?? '', fn ($q, $v) => $q->where('p.fecha_pago', '>=', $v))
            ->where('p.fecha_pago', '<=', ($filtros['hasta'] ?? '') ?: now()->toDateString())
            ->when($filtros['cliente'] ?? '', fn ($q, $v) => $q->where('p.cliente_id', $v))
            ->when($filtros['banco'] ?? '', fn ($q, $v) => $q->where('p.banco_id', $v))
            ->when($filtros['estado'] ?? '', fn ($q, $v) => $q->where('p.estado', $v))
            ->when($filtros['buscar'] ?? '', function ($q, $v) {
                $q->where(function ($q) use ($v) {
                    $q->where('c.nombre', 'like', "%{$v}%")->orWhere('p.id', (int) $v)
                        ->orWhere('p.observaciones', 'like', "%{$v}%");
                });
            });
        if (!empty($filtros['vendedor']) || !empty($filtros['teleasesor'])
            || !empty($filtros['gestor']) || !empty($filtros['factura'])) {
            $query->whereExists(function ($q) use ($filtros) {
                $q->selectRaw('1')->from('pni_aplicaciones as a')
                    ->join('pni_operaciones as o', 'o.id', '=', 'a.operacion_id')
                    ->join('factura as f', 'f.id', '=', 'o.factura_id')
                    ->whereColumn('a.pago_id', 'p.id');
                foreach (['vendedor' => 'vendedor', 'teleasesor' => 'users_id', 'gestor' => 'gestor_entrega'] as $key => $column) {
                    $q->when($filtros[$key] ?? '', fn ($q, $v) => $q->where("f.{$column}", $v));
                }
                $q->when($filtros['factura'] ?? '', fn ($q, $v) => $q->where(
                    fn ($q) => $q->where('f.cai', 'like', "%{$v}%")->orWhere('f.numero_secuencia_cai', 'like', "%{$v}%")
                ));
            });
        }
        return $query;
    }

    public function cuentas(int $clienteId): Builder
    {
        $this->autorizar();
        return DB::table('aplicacion_pagos as ap')
            ->join('factura as f', 'f.id', '=', 'ap.factura_id')
            ->where('f.cliente_id', $clienteId)->where('ap.cliente_id', $clienteId)
            ->where('f.estado_venta_id', 1)->where('ap.estado', 1)
            ->where(fn ($q) => $q->whereNull('ap.estado_cerrado')->orWhere('ap.estado_cerrado', '<>', 2))
            ->whereRaw($this->saldoActualSql() . ' > 0')
            ->select('ap.id', 'ap.factura_id', 'f.cai', 'f.numero_secuencia_cai', 'f.fecha_emision', 'f.fecha_vencimiento', 'f.total')
            ->selectRaw($this->saldoActualSql() . ' AS saldo');
    }

    private function saldoActualSql(): string
    {
        // Misma sincronización de cargos que el listado vigente de cuentas por cobrar.
        return 'ROUND(ap.saldo + CASE WHEN f.tipo_venta_id = 3 THEN f.sub_total ELSE f.total END'
            . ' - COALESCE(ap.total_factura_cargo, CASE WHEN f.tipo_venta_id = 3 THEN f.sub_total ELSE f.total END), 2)';
    }

    public function seleccion(array $ids, bool $bloquear = false): Collection
    {
        $this->autorizar();
        Validator::make(['pagos' => $ids], [
            'pagos' => ['required', 'array', 'min:1', 'max:100'],
            'pagos.*' => ['required', 'integer', 'min:1', 'distinct'],
        ])->validate();
        $query = DB::table('pagos_no_identificados')->whereIn('id', $ids)->orderBy('fecha_pago')->orderBy('id');
        $pagos = ($bloquear ? $query->lockForUpdate() : $query)->get();
        if ($pagos->count() !== count($ids) || $pagos->contains(fn ($p) => self::centavos($p->saldo_disponible) === 0)) {
            throw ValidationException::withMessages(['aplicacion' => 'La selección contiene pagos inexistentes o sin saldo. Actualice la consulta.']);
        }
        if ($pagos->pluck('cliente_id')->filter()->unique()->count() > 1) {
            throw ValidationException::withMessages(['aplicacion' => 'Seleccione pagos de un solo cliente.']);
        }
        return $pagos;
    }

    public function distribuir(Collection $pagos, int $monto): array
    {
        $restante = $monto;
        $detalle = [];
        foreach ($pagos as $pago) {
            $antes = self::centavos($pago->saldo_disponible);
            $consumo = min($restante, $antes);
            if ($consumo > 0) {
                $detalle[] = ['pago' => $pago, 'monto' => $consumo, 'antes' => $antes, 'despues' => $antes - $consumo];
                $restante -= $consumo;
            }
        }
        if ($monto <= 0 || $restante > 0) {
            throw ValidationException::withMessages(['aplicacion' => 'El monto excede el saldo disponible o no es mayor que cero.']);
        }
        return $detalle;
    }

    public function periodo(string $fecha): array
    {
        $original = Carbon::parse($fecha)->startOfMonth();
        $destino = $original->copy();
        while (DB::table('comision_periodo')->where('periodo', $destino->toDateString())->where('estado', 1)->exists()) {
            $destino->addMonth();
        }
        return ['original' => $original->toDateString(), 'asignado' => $destino->toDateString()];
    }

    public function aplicar(array $datos): int
    {
        $this->autorizar();
        Validator::make($datos, [
            'pagos' => ['required', 'array', 'min:1', 'max:100'],
            'pagos.*' => ['integer', 'min:1', 'distinct'],
            'cliente_id' => ['required', 'integer', 'exists:cliente,id'],
            'factura_id' => ['required', 'integer'],
            'monto' => ['required', 'regex:/^\d{1,10}(?:\.\d{1,2})?$/D'],
            'clave' => ['required', 'uuid'],
            'saldos' => ['required', 'array'],
            'saldos.*' => ['required', 'regex:/^\d{1,10}(?:\.\d{1,2})?$/D'],
            'saldo_factura' => ['required', 'regex:/^\d{1,10}(?:\.\d{1,2})?$/D'],
            'confirmar_periodo' => ['required', 'boolean'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ])->validate();
        $monto = self::centavos((string) $datos['monto']);
        $ids = array_map('intval', $datos['pagos']);
        sort($ids);
        $huella = hash('sha256', json_encode([$ids, (int) $datos['cliente_id'], (int) $datos['factura_id'], $monto]));
        $observacion = trim((string) ($datos['observaciones'] ?? ''));
        if ($observacion !== '') {
            $huella = hash('sha256', $huella . $observacion);
        }

        return DB::transaction(function () use ($datos, $monto, $ids, $huella, $observacion) {
            // Serializar reintentos del mismo usuario antes de consultar la clave idempotente.
            DB::table('users')->where('id', Auth::id())->lockForUpdate()->first();
            $anterior = DB::table('pni_operaciones')->where('clave', $datos['clave'])->first();
            if ($anterior) {
                abort_unless((int) $anterior->usuario_id === Auth::id(), 403);
                if ($anterior->huella !== $huella) {
                    throw ValidationException::withMessages(['aplicacion' => 'La clave de operación ya fue utilizada con otros datos.']);
                }
                return (int) $anterior->id;
            }
            $pagos = $this->seleccion($ids, true);
            foreach ($pagos as $pago) {
                if (($pago->cliente_id && (int) $pago->cliente_id !== (int) $datos['cliente_id'])
                    || !isset($datos['saldos'][$pago->id])
                    || self::centavos((string) $datos['saldos'][$pago->id]) !== self::centavos($pago->saldo_disponible)) {
                    throw ValidationException::withMessages(['aplicacion' => 'Los saldos o el cliente cambiaron. Cierre el modal y revise la selección.']);
                }
            }
            $cliente = DB::table('cliente')->where('id', $datos['cliente_id'])->lockForUpdate()->first();
            $factura = DB::table('factura')->where('id', $datos['factura_id'])->lockForUpdate()->first();
            $cuenta = $this->cuentas((int) $datos['cliente_id'])->where('f.id', $datos['factura_id'])->lockForUpdate()->first();
            if (!$cliente || !$factura || !$cuenta) {
                throw ValidationException::withMessages(['aplicacion' => 'La factura no pertenece al cliente o ya no admite cobros.']);
            }
            $saldoFactura = self::centavos($cuenta->saldo);
            if ($saldoFactura !== self::centavos($datos['saldo_factura']) || $monto > $saldoFactura) {
                throw ValidationException::withMessages(['aplicacion' => 'El saldo de la factura cambió o el monto supera su saldo. Revise la operación.']);
            }
            $detalle = $this->distribuir($pagos, $monto);
            $periodos = [];
            foreach ($detalle as $linea) {
                $periodo = $this->periodo($linea['pago']->fecha_pago);
                if ($periodo['original'] !== $periodo['asignado'] && !$datos['confirmar_periodo']) {
                    throw ValidationException::withMessages(['aplicacion' => 'Confirme el traslado de comisiones al siguiente período abierto.']);
                }
                // Crear/bloquear el período para serializar con su conciliación.
                DB::table('comision_periodo')->insertOrIgnore([
                    'periodo' => $periodo['asignado'], 'estado' => 0, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $fila = DB::table('comision_periodo')->where('periodo', $periodo['asignado'])->lockForUpdate()->first();
                if ((int) $fila->estado === 1) {
                    throw ValidationException::withMessages(['aplicacion' => 'El período acaba de conciliarse. Revise y confirme nuevamente.']);
                }
                $periodos[$linea['pago']->id] = $periodo;
            }
            $operacion = DB::table('pni_operaciones')->insertGetId([
                'clave' => $datos['clave'], 'huella' => $huella, 'cliente_id' => $cliente->id,
                'factura_id' => $factura->id, 'monto' => self::decimal($monto),
                'saldo_factura_antes' => self::decimal($saldoFactura),
                'saldo_factura_despues' => self::decimal($saldoFactura - $monto),
                'usuario_id' => Auth::id(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($detalle as $linea) {
                $pago = $linea['pago'];
                $periodo = $periodos[$pago->id];
                $abono = DB::table('abonos_creditos')->insertGetId([
                    'aplicacion_pagos_id' => $cuenta->id, 'factura_id' => $factura->id,
                    'banco_id' => $pago->banco_id, 'estado_abono' => 1, 'id_tipo_pago_cobro' => 2,
                    'monto_abonado' => self::decimal($linea['monto']), 'usr_registro' => Auth::id(),
                    'comentario' => "Pago no identificado #{$pago->id}, operación #{$operacion}. " . $pago->observaciones
                        . ($observacion !== '' ? "\nObservación de la aplicación: {$observacion}" : ''),
                    'fecha_pago' => $pago->fecha_pago, 'numero_recibo' => "PNI-{$pago->id}",
                    'periodo_comision_original' => $periodo['original'] !== $periodo['asignado'] ? $periodo['original'] : null,
                    'periodo_comision_asignado' => $periodo['original'] !== $periodo['asignado'] ? $periodo['asignado'] : null,
                    'desvio_confirmado_por' => $periodo['original'] !== $periodo['asignado'] ? Auth::id() : null,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                DB::table('pni_aplicaciones')->insert([
                    'operacion_id' => $operacion, 'pago_id' => $pago->id, 'abono_id' => $abono,
                    'monto' => self::decimal($linea['monto']), 'saldo_antes' => self::decimal($linea['antes']),
                    'saldo_despues' => self::decimal($linea['despues']), 'created_at' => now(), 'updated_at' => now(),
                ]);
                DB::table('pagos_no_identificados')->where('id', $pago->id)->update([
                    'cliente_id' => $cliente->id,
                    'identificado_por' => $pago->cliente_id ? $pago->identificado_por : Auth::id(),
                    'identificado_at' => $pago->cliente_id ? $pago->identificado_at : now(),
                    'saldo_disponible' => self::decimal($linea['despues']),
                    'estado' => $linea['despues'] === 0 ? 'aplicado' : 'parcial', 'updated_at' => now(),
                ]);
            }
            $nuevoSaldo = $saldoFactura - $monto;
            $cambios = [
                'saldo' => self::decimal($nuevoSaldo),
                'total_factura_cargo' => (int) $factura->tipo_venta_id === 3 ? $factura->sub_total : $factura->total,
                'retencion_isv_factura' => (int) $factura->tipo_venta_id === 3 ? 0 : $factura->isv,
                'credito_abonos' => DB::raw('COALESCE(credito_abonos, 0) + ' . self::decimal($monto)),
                'ultimo_usr_actualizo' => Auth::id(), 'updated_at' => now(),
            ];
            if ($nuevoSaldo === 0) {
                $cambios += ['estado_cerrado' => 2, 'usr_cerro' => Auth::id(), 'fecha_cierre_factura' => now()];
            }
            DB::table('aplicacion_pagos')->where('id', $cuenta->id)->update($cambios);
            if (self::centavos((string) $cliente->credito_inicial) > 0) {
                DB::table('cliente')->where('id', $cliente->id)->update([
                    'credito' => DB::raw('COALESCE(credito, 0) + ' . self::decimal($monto)),
                ]);
            }
            if ($nuevoSaldo === 0) {
                $ultima = $detalle[count($detalle) - 1]['pago'];
                $periodoCierre = $periodos[$ultima->id];
                $fechaComision = $periodoCierre['original'] !== $periodoCierre['asignado']
                    ? $periodoCierre['asignado'] : $ultima->fecha_pago;
                $comisiones = app(GeneradorFacturasComision::class)->generar((int) $factura->id, (int) $cuenta->id, $fechaComision);
                $comisiones = app(AplicadorRetencionesMora::class)->aplicar($comisiones, (int) $factura->id, $ultima->fecha_pago);
                foreach ($comisiones as $comision) {
                    app(ProcesadorComisiones::class)->procesar($comision);
                }
            }
            return $operacion;
        }, 3);
    }

    public function historial(int $pagoId): Collection
    {
        $this->autorizar();
        return DB::table('pni_aplicaciones as a')
            ->join('pni_operaciones as o', 'o.id', '=', 'a.operacion_id')
            ->join('factura as f', 'f.id', '=', 'o.factura_id')
            ->join('users as u', 'u.id', '=', 'o.usuario_id')
            ->join('abonos_creditos as ab', 'ab.id', '=', 'a.abono_id')
            ->select('a.*', 'o.factura_id', 'f.cai', 'f.numero_secuencia_cai', 'u.name as usuario',
                'o.saldo_factura_antes', 'o.saldo_factura_despues', 'ab.comentario as observaciones')
            ->where('a.pago_id', $pagoId)->orderBy('a.id')->get();
    }
}
