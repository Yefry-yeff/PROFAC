<?php

namespace App\Services\Expo;

use App\Models\CreditoRevision;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SeccionadorOfertaExpo
{
    public function iniciarSeccionado(
        int $flujoId,
        int $cotizacionId,
        int $usuarioId
    ): void {
        DB::transaction(function () use ($flujoId, $cotizacionId, $usuarioId) {
            $esExpo = DB::table('expo_cotizacion')->where('cotizacion_id', $cotizacionId)->exists();
            $oferta = DB::table('historico_flujo')
                ->where('flujo_id', $flujoId)
                ->where('tipo_tramite_id', 2)
                ->where('tramite_id', $cotizacionId)
                ->lockForUpdate()
                ->first();

            if (!$esExpo || !$oferta) {
                throw ValidationException::withMessages([
                    'oferta' => 'La oferta indicada no es una oferta Expo válida de este flujo.',
                ]);
            }

            DB::table('historico_flujo')
                ->where('flujo_id', $flujoId)
                ->where('tipo_tramite_id', 2)
                ->where('observaciones', 'ganadora')
                ->update(['observaciones' => null, 'updated_by' => $usuarioId, 'updated_at' => now()]);

            DB::table('historico_flujo')->where('id', $oferta->id)->update([
                'observaciones' => 'ganadora',
                'updated_by' => $usuarioId,
                'updated_at' => now(),
            ]);

            DB::table('cotizacion_estado')->insert([
                'cotizacion_id' => $cotizacionId,
                'flujo_id' => $flujoId,
                'ganadora' => 1,
                'comentario' => 'Oferta Expo ganadora. Enviada a Secciones de Ofertas.',
                'estado_id' => 1,
                'created_by' => $usuarioId,
                'updated_by' => $usuarioId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('historico_flujo')
                ->where('flujo_id', $flujoId)
                ->where('tipo_tramite_id', 11)
                ->where('estado_id', 5)
                ->update([
                    'estado_id' => 7,
                    'observaciones' => 'Ciclo anterior reemplazado al seleccionar la oferta ganadora.',
                    'updated_by' => $usuarioId,
                    'updated_at' => now(),
                ]);

            DB::table('historico_flujo')->insert([
                'flujo_id' => $flujoId,
                'tipo_tramite_id' => 11,
                'tramite_id' => $cotizacionId,
                'estado_id' => 5,
                'observaciones' => 'Oferta Expo ganadora lista para crear secciones.',
                'created_by' => $usuarioId,
                'updated_by' => $usuarioId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('flujo')->where('id', $flujoId)->update([
                'tipo_tramite_id' => 11,
                'updated_by' => $usuarioId,
                'updated_at' => now(),
            ]);
        });
    }

    public function crear(
        int $flujoId,
        int $cotizacionOrigenId,
        array $cantidades,
        int $tipoPagoId,
        string $fechaEmision,
        string $fechaPago,
        bool $finalizaSeccionado,
        int $usuarioId,
        ?string $comentarioCredito = null
    ): int {
        return DB::transaction(function () use (
            $flujoId,
            $cotizacionOrigenId,
            $cantidades,
            $tipoPagoId,
            $fechaEmision,
            $fechaPago,
            $finalizaSeccionado,
            $usuarioId,
            $comentarioCredito
        ) {
            $flujo = DB::table('flujo')->where('id', $flujoId)->lockForUpdate()->first();
            $origen = DB::table('cotizacion')->where('id', $cotizacionOrigenId)->lockForUpdate()->first();
            $expoOrigen = DB::table('expo_cotizacion')
                ->where('cotizacion_id', $cotizacionOrigenId)
                ->lockForUpdate()
                ->first();

            if (!$flujo || !$origen || !$expoOrigen) {
                throw ValidationException::withMessages([
                    'oferta' => 'Solo una oferta Expo válida puede dividirse en secciones.',
                ]);
            }

            if (!in_array($tipoPagoId, [1, 2], true)) {
                throw ValidationException::withMessages([
                    'tipo_pago' => 'Seleccione una condición de pago válida para la sección.',
                ]);
            }

            $emision = Carbon::parse($fechaEmision)->startOfDay();
            $vencimiento = Carbon::parse($fechaPago)->startOfDay();
            if ($vencimiento->lt($emision)) {
                throw ValidationException::withMessages([
                    'fecha_pago' => 'La fecha de pago no puede ser anterior a la fecha de emisión.',
                ]);
            }
            if ($tipoPagoId === 1) {
                $vencimiento = $emision->copy();
            }

            $esGanadora = DB::table('historico_flujo')
                ->where('flujo_id', $flujoId)
                ->where('tipo_tramite_id', 2)
                ->where('tramite_id', $cotizacionOrigenId)
                ->where('observaciones', 'ganadora')
                ->exists();

            if (!$esGanadora) {
                throw ValidationException::withMessages([
                    'oferta' => 'La oferta Expo debe estar marcada como ganadora antes de seccionarla.',
                ]);
            }

            $lineasOrigen = DB::table('cotizacion_has_producto')
                ->where('cotizacion_id', $cotizacionOrigenId)
                ->orderBy('indice')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $asignadas = $this->cantidadesAsignadas($cotizacionOrigenId);
            $seleccionadas = collect($cantidades)
                ->mapWithKeys(fn ($cantidad, $lineaId) => [(int) $lineaId => (float) $cantidad])
                ->filter(fn ($cantidad) => $cantidad > 0);

            if ($seleccionadas->isEmpty()) {
                throw ValidationException::withMessages([
                    'productos' => 'Seleccione al menos un producto para crear la sección.',
                ]);
            }

            foreach ($seleccionadas as $lineaId => $cantidad) {
                $linea = $lineasOrigen->get($lineaId);
                $disponible = $linea
                    ? max(0, (float) $linea->cantidad - (float) ($asignadas[$lineaId] ?? 0))
                    : 0;

                if (!$linea || floor($cantidad) !== $cantidad || $cantidad > $disponible) {
                    throw ValidationException::withMessages([
                        'productos' => 'Una cantidad seleccionada supera el saldo disponible de la oferta ganadora.',
                    ]);
                }
            }

            $numero = (int) DB::table('expo_oferta_seccion')
                ->where('cotizacion_origen_id', $cotizacionOrigenId)
                ->max('numero') + 1;

            $lineasHija = $this->prepararLineas($lineasOrigen, $seleccionadas);
            $totales = $this->calcularTotales($lineasHija);
            $cotizacionHijaId = $this->crearCotizacionHija(
                $origen,
                $totales,
                $lineasHija,
                $tipoPagoId,
                $emision->toDateString(),
                $vencimiento->toDateString(),
                $usuarioId
            );

            foreach ($lineasHija as &$lineaHija) {
                $lineaHija['cotizacion_id'] = $cotizacionHijaId;
            }
            unset($lineaHija);
            DB::table('cotizacion_has_producto')->insert($lineasHija);

            $expoHija = (array) $expoOrigen;
            unset($expoHija['id']);
            $expoHija['cotizacion_id'] = $cotizacionHijaId;
            $expoHija['created_by'] = $usuarioId;
            $expoHija['created_at'] = now();
            if (array_key_exists('flujo_id', $expoHija)) {
                $expoHija['flujo_id'] = $flujoId;
            }
            foreach ([
                'cierre_manual' => 0,
                'reapertura_autorizada' => 0,
                'motivo_reapertura' => null,
                'reabierto_por' => null,
                'reabierto_at' => null,
                'motivo_cierre' => null,
                'cerrado_por' => null,
                'cerrado_at' => null,
                'total_facturado' => 0,
                'porcentaje_descuento_final' => 0,
                'aumento_calculado' => 0,
                'aumento_aplicado' => 0,
                'descuento_calculado' => 0,
                'saldo_aplicable' => 0,
                'diferencia_contabilidad' => 0,
                'nota_credito_id' => null,
                'liquidado_por' => null,
                'liquidado_at' => null,
            ] as $campo => $valor) {
                if (array_key_exists($campo, $expoHija)) {
                    $expoHija[$campo] = $valor;
                }
            }
            $expoHija['estado'] = 'PENDIENTE_FACTURACION';
            DB::table('expo_cotizacion')->insert($expoHija);
            $this->actualizarSnapshotLineas($cotizacionHijaId);

            DB::table('expo_oferta_seccion')->insert([
                'flujo_id' => $flujoId,
                'cotizacion_origen_id' => $cotizacionOrigenId,
                'cotizacion_id' => $cotizacionHijaId,
                'numero' => $numero,
                'nombre' => 'Seccion ' . $numero,
                'estado' => 'EN_REVISION_CREDITO',
                'finaliza_seccionado' => $finalizaSeccionado,
                'created_by' => $usuarioId,
                'updated_by' => $usuarioId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('historico_flujo')
                ->where('flujo_id', $flujoId)
                ->where('tipo_tramite_id', 11)
                ->where('tramite_id', $cotizacionOrigenId)
                ->where('estado_id', 5)
                ->update([
                    'estado_id' => 1,
                    'observaciones' => 'Sección #' . $numero . ' creada como oferta #' . $cotizacionHijaId . '.',
                    'updated_by' => $usuarioId,
                    'updated_at' => now(),
                ]);

            $this->enviarACredito(
                $flujoId,
                $cotizacionHijaId,
                $tipoPagoId,
                $emision->toDateString(),
                $vencimiento->toDateString(),
                $totales['total'],
                $usuarioId,
                $comentarioCredito
            );

            $saldoPendiente = $this->pendientes($cotizacionOrigenId)->sum('cantidad_pendiente');
            $continuaSeccionado = !$finalizaSeccionado && $saldoPendiente > 0;
            if ($continuaSeccionado) {
                DB::table('historico_flujo')->insert([
                    'flujo_id' => $flujoId,
                    'tipo_tramite_id' => 11,
                    'tramite_id' => $cotizacionOrigenId,
                    'estado_id' => 5,
                    'observaciones' => 'La oferta Expo conserva productos pendientes y puede crear más secciones.',
                    'created_by' => $usuarioId,
                    'updated_by' => $usuarioId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('flujo')->where('id', $flujoId)->update([
                'tipo_tramite_id' => $continuaSeccionado ? 11 : 10,
                'updated_by' => $usuarioId,
                'updated_at' => now(),
            ]);

            return $cotizacionHijaId;
        });
    }

    public function pendientes(int $cotizacionOrigenId): Collection
    {
        $asignadas = $this->cantidadesAsignadas($cotizacionOrigenId);

        return DB::table('cotizacion_has_producto')
            ->where('cotizacion_id', $cotizacionOrigenId)
            ->orderBy('indice')
            ->get()
            ->map(function ($linea) use ($asignadas) {
                $linea->cantidad_asignada = (float) ($asignadas[$linea->id] ?? 0);
                $linea->cantidad_pendiente = max(0, (float) $linea->cantidad - $linea->cantidad_asignada);
                return $linea;
            });
    }

    public function actualizarRechazadaCredito(
        int $seccionId,
        array $cantidades,
        int $tipoPagoId,
        string $fechaEmision,
        string $fechaPago,
        bool $finalizaSeccionado,
        int $usuarioId,
        ?string $comentarioCredito = null
    ): int {
        return DB::transaction(function () use (
            $seccionId,
            $cantidades,
            $tipoPagoId,
            $fechaEmision,
            $fechaPago,
            $finalizaSeccionado,
            $usuarioId,
            $comentarioCredito
        ) {
            $seccion = DB::table('expo_oferta_seccion')->where('id', $seccionId)->lockForUpdate()->first();
            if (!$seccion || !in_array($seccion->estado, ['RECHAZADA_CREDITO', 'DEVUELTA_INVENTARIO', 'DEVUELTA_SECCION'], true)) {
                throw ValidationException::withMessages([
                    'seccion' => 'Sólo puede editar una sección devuelta desde Crédito, Inventario o Prefactura.',
                ]);
            }

            $devueltaInventario = $seccion->estado === 'DEVUELTA_INVENTARIO';
            if ($devueltaInventario) {
                $cotizacionActual = DB::table('cotizacion')
                    ->where('id', $seccion->cotizacion_id)
                    ->lockForUpdate()
                    ->first(['tipo_pago_id', 'fecha_emision', 'fecha_vencimiento']);
                $tipoPagoId = (int) $cotizacionActual->tipo_pago_id;
                $fechaEmision = $cotizacionActual->fecha_emision;
                $fechaPago = $cotizacionActual->fecha_vencimiento ?: $cotizacionActual->fecha_emision;
                $finalizaSeccionado = (bool) $seccion->finaliza_seccionado;
            }

            if (!in_array($tipoPagoId, [1, 2], true)) {
                throw ValidationException::withMessages(['tipo_pago' => 'Seleccione una condición de pago válida.']);
            }

            $emision = Carbon::parse($fechaEmision)->startOfDay();
            $vencimiento = Carbon::parse($fechaPago)->startOfDay();
            if ($vencimiento->lt($emision)) {
                throw ValidationException::withMessages(['fecha_pago' => 'La fecha de pago no puede ser anterior a la fecha de emisión.']);
            }
            if ($tipoPagoId === 1) {
                $vencimiento = $emision->copy();
            }

            $lineasOrigen = DB::table('cotizacion_has_producto')
                ->where('cotizacion_id', $seccion->cotizacion_origen_id)
                ->orderBy('indice')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $asignadas = $this->cantidadesAsignadas((int) $seccion->cotizacion_origen_id);
            $seleccionadas = collect($cantidades)
                ->mapWithKeys(fn ($cantidad, $lineaId) => [(int) $lineaId => (float) $cantidad])
                ->filter(fn ($cantidad) => $cantidad > 0);

            if ($seleccionadas->isEmpty()) {
                throw ValidationException::withMessages(['productos' => 'Seleccione al menos un producto para reenviar la sección.']);
            }

            foreach ($seleccionadas as $lineaId => $cantidad) {
                $linea = $lineasOrigen->get($lineaId);
                $saldo = $linea ? max(0, (float) $linea->cantidad - (float) ($asignadas[$lineaId] ?? 0)) : 0;
                if (!$linea || floor($cantidad) !== $cantidad || $cantidad > $saldo) {
                    throw ValidationException::withMessages(['productos' => 'Una cantidad supera el saldo disponible de la oferta ganadora.']);
                }
            }

            $lineasHija = $this->prepararLineas($lineasOrigen, $seleccionadas);
            $totales = $this->calcularTotales($lineasHija);
            $indices = collect($lineasHija)->pluck('indice')->map(fn ($indice) => (int) $indice)->values()->all();

            DB::table('cotizacion')->where('id', $seccion->cotizacion_id)->update(array_merge($totales, [
                'tipo_pago_id' => $tipoPagoId,
                'fecha_emision' => $emision->toDateString(),
                'fecha_vencimiento' => $vencimiento->toDateString(),
                'arregloIdInputs' => json_encode($indices),
                'numeroInputs' => count($indices),
                'updated_by' => $usuarioId,
                'updated_at' => now(),
            ]));

            DB::table('cotizacion_has_producto')->where('cotizacion_id', $seccion->cotizacion_id)->delete();
            foreach ($lineasHija as &$lineaHija) {
                $lineaHija['cotizacion_id'] = $seccion->cotizacion_id;
            }
            unset($lineaHija);
            DB::table('cotizacion_has_producto')->insert($lineasHija);
            $this->actualizarSnapshotLineas((int) $seccion->cotizacion_id);

            DB::table('expo_oferta_seccion')->where('id', $seccionId)->update([
                'estado' => $devueltaInventario ? 'EN_REVISION_INVENTARIO' : 'EN_REVISION_CREDITO',
                'finaliza_seccionado' => $finalizaSeccionado,
                'updated_by' => $usuarioId,
                'updated_at' => now(),
            ]);

            if ($devueltaInventario) {
                DB::table('historico_flujo')
                    ->where('flujo_id', $seccion->flujo_id)
                    ->where('tipo_tramite_id', 9)
                    ->where('tramite_id', $seccion->cotizacion_id)
                    ->where('estado_id', 7)
                    ->update([
                        'estado_id' => 5,
                        'observaciones' => 'Sección Expo corregida y reenviada directamente a Revisión de Inventario.',
                        'updated_by' => $usuarioId,
                        'updated_at' => now(),
                    ]);

                DB::table('flujo')->where('id', $seccion->flujo_id)->update([
                    'tipo_tramite_id' => 11,
                    'updated_by' => $usuarioId,
                    'updated_at' => now(),
                ]);

                return (int) $seccion->cotizacion_id;
            }

            $revision = CreditoRevision::paraSeccion((int) $seccion->flujo_id, (int) $seccion->cotizacion_id);
            if ($revision) {
                $estadoAnterior = $revision->estado;
                $revision->update([
                    'estado' => CreditoRevision::PENDIENTE,
                    'fecha_aprobacion' => null,
                    'fecha_emision_solicitada' => $emision->toDateString(),
                    'fecha_vencimiento_solicitada' => $vencimiento->toDateString(),
                    'dias_credito_solicitados' => $tipoPagoId === 2 ? $emision->diffInDays($vencimiento) : 0,
                    'fecha_vencimiento_credito' => null,
                    'dias_credito_aprobados' => null,
                    'motivo_rechazo' => null,
                    'observaciones' => 'Sección Expo corregida y reenviada a Crédito.',
                    'usuario_revision' => $usuarioId,
                    'ip_revision' => request()->ip(),
                ]);
            } else {
                $estadoAnterior = null;
                $revision = CreditoRevision::create([
                    'flujo_id' => $seccion->flujo_id,
                    'cotizacion_id' => $seccion->cotizacion_id,
                    'estado' => CreditoRevision::PENDIENTE,
                    'fecha_emision_solicitada' => $emision->toDateString(),
                    'fecha_vencimiento_solicitada' => $vencimiento->toDateString(),
                    'dias_credito_solicitados' => $tipoPagoId === 2 ? $emision->diffInDays($vencimiento) : 0,
                    'observaciones' => 'Sección Expo corregida y reenviada a Crédito.',
                    'usuario_revision' => $usuarioId,
                    'ip_revision' => request()->ip(),
                ]);
            }
            $revision->registrarHistorial(
                'reenviado',
                $estadoAnterior,
                CreditoRevision::PENDIENTE,
                'Sección corregida y reenviada a Revisión de Crédito.',
                request()->ip()
            );

            DB::table('flujo_oferta_credito_comentarios')->insert([
                'flujo_id' => $seccion->flujo_id,
                'tramite_id' => $seccion->cotizacion_id,
                'observacion' => trim((string) $comentarioCredito) ?: null,
                'created_by' => $usuarioId,
                'updated_by' => $usuarioId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('historico_flujo')->insert([
                'flujo_id' => $seccion->flujo_id,
                'tipo_tramite_id' => 10,
                'tramite_id' => $seccion->cotizacion_id,
                'estado_id' => 5,
                'observaciones' => 'Sección Expo corregida y reenviada a Revisión de Crédito.',
                'created_by' => $usuarioId,
                'updated_by' => $usuarioId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $saldoPendiente = $this->pendientes((int) $seccion->cotizacion_origen_id)->sum('cantidad_pendiente');
            $continuaSeccionado = !$finalizaSeccionado && $saldoPendiente > 0;
            if (!$continuaSeccionado) {
                DB::table('historico_flujo')
                    ->where('flujo_id', $seccion->flujo_id)
                    ->where('tipo_tramite_id', 11)
                    ->where('tramite_id', $seccion->cotizacion_origen_id)
                    ->where('estado_id', 5)
                    ->update([
                        'estado_id' => 1,
                        'observaciones' => 'Sección corregida y reenviada a Revisión de Crédito.',
                        'updated_by' => $usuarioId,
                        'updated_at' => now(),
                    ]);
            }

            DB::table('flujo')->where('id', $seccion->flujo_id)->update([
                'tipo_tramite_id' => $continuaSeccionado ? 11 : 10,
                'updated_by' => $usuarioId,
                'updated_at' => now(),
            ]);

            return (int) $seccion->cotizacion_id;
        });
    }

    private function cantidadesAsignadas(int $cotizacionOrigenId): array
    {
        return DB::table('expo_oferta_seccion as eos')
            ->join('cotizacion_has_producto as hija', 'hija.cotizacion_id', '=', 'eos.cotizacion_id')
            ->join('cotizacion_has_producto as origen', function ($join) use ($cotizacionOrigenId) {
                $join->on('origen.indice', '=', 'hija.indice')
                    ->where('origen.cotizacion_id', '=', $cotizacionOrigenId);
            })
            ->where('eos.cotizacion_origen_id', $cotizacionOrigenId)
            ->whereNotIn('eos.estado', ['ANULADA', 'RECHAZADA_CREDITO', 'DEVUELTA_INVENTARIO', 'DEVUELTA_SECCION'])
            ->groupBy('origen.id')
            ->selectRaw('origen.id, SUM(hija.cantidad) as cantidad')
            ->pluck('cantidad', 'id')
            ->map(fn ($cantidad) => (float) $cantidad)
            ->all();
    }

    private function prepararLineas(Collection $lineasOrigen, Collection $seleccionadas): array
    {
        $lineas = [];
        foreach ($seleccionadas as $lineaId => $cantidad) {
            $origen = $lineasOrigen->get($lineaId);
            $factor = $cantidad / (float) $origen->cantidad;
            $linea = (array) $origen;
            unset($linea['id']);
            $linea['cantidad'] = $cantidad;
            foreach (['sub_total', 'isv', 'total', 'monto_descProducto'] as $campo) {
                $linea[$campo] = round((float) ($linea[$campo] ?? 0) * $factor, 2);
            }
            $linea['created_at'] = now();
            $linea['updated_at'] = now();
            $lineas[] = $linea;
        }

        return $lineas;
    }

    private function calcularTotales(array $lineas): array
    {
        $coleccion = collect($lineas);

        return [
            'sub_total' => round((float) $coleccion->sum('sub_total'), 2),
            'sub_total_grabado' => round((float) $coleccion->where('isv_producto', '>', 0)->sum('sub_total'), 2),
            'sub_total_excento' => round((float) $coleccion->where('isv_producto', '<=', 0)->sum('sub_total'), 2),
            'isv' => round((float) $coleccion->sum('isv'), 2),
            'total' => round((float) $coleccion->sum('total'), 2),
            'monto_descuento' => round((float) $coleccion->sum('monto_descProducto'), 2),
        ];
    }

    private function crearCotizacionHija(
        object $origen,
        array $totales,
        array $lineas,
        int $tipoPagoId,
        string $fechaEmision,
        string $fechaPago,
        int $usuarioId
    ): int
    {
        $hija = (array) $origen;
        unset($hija['id']);
        foreach ($totales as $campo => $valor) {
            $hija[$campo] = $valor;
        }
        $hija['tipo_pago_id'] = $tipoPagoId;
        $hija['fecha_emision'] = $fechaEmision;
        $hija['fecha_vencimiento'] = $fechaPago;
        $indices = collect($lineas)->pluck('indice')->map(fn ($indice) => (int) $indice)->values()->all();
        $hija['arregloIdInputs'] = json_encode($indices);
        $hija['numeroInputs'] = count($indices);
        $hija['users_id'] = $usuarioId;
        $hija['created_by'] = $usuarioId;
        $hija['updated_by'] = $usuarioId;
        $hija['created_at'] = now();
        $hija['updated_at'] = now();

        return (int) DB::table('cotizacion')->insertGetId($hija);
    }

    private function actualizarSnapshotLineas(int $cotizacionId): void
    {
        $expo = DB::table('expo_cotizacion')->where('cotizacion_id', $cotizacionId)->first();
        if (!$expo || empty($expo->reglas_descuento_snapshot)) {
            return;
        }

        $snapshot = json_decode($expo->reglas_descuento_snapshot, true) ?: [];
        $snapshot['lineas'] = DB::table('cotizacion_has_producto as chp')
            ->leftJoin('precios_producto_carga as ppc', 'ppc.id', '=', 'chp.precios_producto_carga_id')
            ->leftJoin('categoria_precios as cp', 'cp.id', '=', 'ppc.categoria_precios_id')
            ->where('chp.cotizacion_id', $cotizacionId)
            ->orderBy('chp.indice')
            ->get(['chp.id as linea_id', 'chp.producto_id', 'ppc.categoria_precios_id', 'cp.nombre as escala'])
            ->map(fn ($linea) => [
                'linea_id' => (int) $linea->linea_id,
                'producto_id' => (int) $linea->producto_id,
                'escala_id' => (int) ($linea->categoria_precios_id ?? 0),
                'escala' => $linea->escala,
            ])->all();

        DB::table('expo_cotizacion')->where('cotizacion_id', $cotizacionId)->update([
            'reglas_descuento_snapshot' => json_encode($snapshot),
        ]);
    }

    private function enviarACredito(
        int $flujoId,
        int $cotizacionId,
        int $tipoPagoId,
        string $fechaEmision,
        string $fechaPago,
        float $total,
        int $usuarioId,
        ?string $comentarioCredito = null
    ): void {
        DB::table('historico_flujo')->insert([
            'flujo_id' => $flujoId,
            'tipo_tramite_id' => 10,
            'tramite_id' => $cotizacionId,
            'estado_id' => 5,
            'observaciones' => 'En Revisión de Crédito. Sección de Oferta Expo #' . $cotizacionId,
            'created_by' => $usuarioId,
            'updated_by' => $usuarioId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('credito_revision')->insert([
            'flujo_id' => $flujoId,
            'cotizacion_id' => $cotizacionId,
            'estado' => 'pendiente',
            'fecha_emision_solicitada' => $fechaEmision,
            'fecha_vencimiento_solicitada' => $fechaPago,
            'dias_credito_solicitados' => $tipoPagoId === 2
                ? max(0, Carbon::parse($fechaEmision)->diffInDays(Carbon::parse($fechaPago), false))
                : 0,
            'observaciones' => 'Sección de Oferta Expo. Condición: '
                . ($tipoPagoId === 2 ? 'Crédito' : 'Contado')
                . '. Total: L ' . number_format($total, 2, '.', ','),
            'usuario_revision' => $usuarioId,
            'ip_revision' => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('flujo_oferta_credito_comentarios')->insert([
            'flujo_id' => $flujoId,
            'tramite_id' => $cotizacionId,
            'observacion' => trim((string) $comentarioCredito) ?: null,
            'created_by' => $usuarioId,
            'updated_by' => $usuarioId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

            DB::table('flujo')->where('id', $flujoId)->update([
                'tipo_tramite_id' => 11,
            'updated_by' => $usuarioId,
            'updated_at' => now(),
        ]);
    }
}