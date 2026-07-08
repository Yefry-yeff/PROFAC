<?php

namespace App\Http\Livewire\Comisiones\Escalado;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Comisiones\ModelComisionPeriodo;
use App\Models\Comisiones\ModelComisionPeriodoLog;
use App\Models\Comisiones\ModelDiasGraciaComision;
use App\Exports\Comisiones\ConciliacionResumenMasivoExport;

class Conciliacion extends Component
{
    public function render()
    {
        return view('livewire.comisiones.escalado.conciliacion');
    }

    /* ══════════════════════════════════════════════════════════════════
     *  VALIDACIÓN DE REGLAS / HEALTH-CHECK
     * ════════════════════════════════════════════════════════════════ */

    /**
     * Verifica que las tablas y configuraciones necesarias para el módulo
     * de conciliación estén correctamente pobladas.
     * Devuelve una lista de checks con estado ok|warning|error.
     */
    public function validarReglas()
    {
        $checks = [];

        // 1 — Tabla comision_escala tiene configuración
        $escalaCount = DB::table('comision_escala')->count();
        $checks[] = [
            'nombre'  => 'Escalas de comisión',
            'estado'  => $escalaCount > 0 ? 'ok' : 'error',
            'mensaje' => $escalaCount > 0
                ? "{$escalaCount} escalas configuradas."
                : 'No hay escalas de comisión registradas. El cálculo de comisiones no funcionará.',
        ];

        // 2 — Tabla comision_rol_config tiene registros
        $rolConfigCount = DB::table('comision_rol_config')->count();
        $checks[] = [
            'nombre'  => 'Configuración de roles',
            'estado'  => $rolConfigCount > 0 ? 'ok' : 'warning',
            'mensaje' => $rolConfigCount > 0
                ? "{$rolConfigCount} roles configurados para comisiones."
                : 'No hay roles configurados en comision_rol_config.',
        ];

        // 3 — Días de gracia configurados para al menos un rol
        $diasGraciaCount = DB::table('dias_gracia_comision')->count();
        $checks[] = [
            'nombre'  => 'Días de gracia',
            'estado'  => $diasGraciaCount > 0 ? 'ok' : 'warning',
            'mensaje' => $diasGraciaCount > 0
                ? "{$diasGraciaCount} configuraciones de días de gracia activas."
                : 'Sin días de gracia configurados. Las comisiones se acreditarán inmediatamente.',
        ];

        // 4 — Comisiones activas en el período actual
        $periodoActual = Carbon::now()->startOfMonth()->toDateString();
        $comisionesActuales = DB::table('comision_empleado')
            ->where('mes_comision', $periodoActual)
            ->where('estado_id', 1)
            ->count();
        $checks[] = [
            'nombre'  => 'Comisiones período actual',
            'estado'  => $comisionesActuales > 0 ? 'ok' : 'warning',
            'mensaje' => $comisionesActuales > 0
                ? "{$comisionesActuales} empleados con comisiones en el período actual."
                : 'Sin comisiones registradas en el período actual (' . $this->_mesLabelFromStr($periodoActual) . ').',
        ];

        // 5 — Períodos abiertos con comisiones pero sin conciliar (deuda contable)
        $periodosAbiertosConDeuda = DB::table('comision_empleado as ce')
            ->leftJoin('comision_periodo as cp', function ($j) {
                $j->whereRaw('cp.periodo = DATE_FORMAT(ce.mes_comision, \'%Y-%m-01\')')
                  ->where('cp.estado', ModelComisionPeriodo::ESTADO_CONCILIADO);
            })
            ->whereNull('cp.id')
            ->where('ce.estado_id', 1)
            ->whereRaw('ce.mes_comision < DATE_FORMAT(NOW(), \'%Y-%m-01\')')
            ->selectRaw('DATE_FORMAT(ce.mes_comision, \'%Y-%m-01\') as periodo')
            ->distinct()
            ->pluck('periodo');

        $checks[] = [
            'nombre'  => 'Períodos sin conciliar',
            'estado'  => $periodosAbiertosConDeuda->isEmpty() ? 'ok' : 'warning',
            'mensaje' => $periodosAbiertosConDeuda->isEmpty()
                ? 'Todos los períodos anteriores con comisiones están conciliados.'
                : count($periodosAbiertosConDeuda) . ' período(s) con comisiones sin conciliar: '
                  . $periodosAbiertosConDeuda->map(fn($p) => $this->_mesLabelFromStr($p))->implode(', ') . '.',
        ];

        // 6 — Facturas comisionadas huérfanas (factura_id no existe en tabla factura)
        $huerfanas = DB::table('facturas_comision as fc')
            ->leftJoin('factura as f', 'f.id', '=', 'fc.factura_id')
            ->whereNull('f.id')
            ->where('fc.estado_id', 1)
            ->count();
        $checks[] = [
            'nombre'  => 'Integridad facturas comisionadas',
            'estado'  => $huerfanas === 0 ? 'ok' : 'error',
            'mensaje' => $huerfanas === 0
                ? 'Todas las facturas comisionadas tienen referencia válida.'
                : "{$huerfanas} registro(s) en facturas_comision sin factura padre. Revisar integridad de datos.",
        ];

        $totalErrores   = count(array_filter($checks, fn($c) => $c['estado'] === 'error'));
        $totalWarnings  = count(array_filter($checks, fn($c) => $c['estado'] === 'warning'));

        return response()->json([
            'checks'         => $checks,
            'total_errores'  => $totalErrores,
            'total_warnings' => $totalWarnings,
            'estado_global'  => $totalErrores > 0 ? 'error' : ($totalWarnings > 0 ? 'warning' : 'ok'),
        ]);
    }

    /* ══════════════════════════════════════════════════════════════════
     *  VERIFICAR PERÍODO DE UN PAGO (usado desde modal Aplicar Abono)
     * ════════════════════════════════════════════════════════════════ */

    /**
     * Recibe una fecha (YYYY-MM-DD) y determina:
     *  - Si su mes/año está conciliado en comision_periodo.
     *  - Cuál es el próximo período abierto (para informar al usuario).
     *
     * GET /comisiones/conciliacion/verificar-periodo?fecha=YYYY-MM-DD
     */
    public function verificarPeriodoPago(Request $request)
    {
        $fechaStr = trim($request->input('fecha', ''));

        if (!$fechaStr) {
            return response()->json(['error' => 'Fecha requerida.'], 422);
        }

        $periodo = Carbon::parse($fechaStr)->startOfMonth()->toDateString();

        // ¿Está ese mes conciliado?
        $registro = DB::table('comision_periodo')
            ->where('periodo', $periodo)
            ->first();

        $conciliado = $registro && (int) $registro->estado === ModelComisionPeriodo::ESTADO_CONCILIADO;

        if (!$conciliado) {
            return response()->json([
                'conciliado'    => false,
                'periodo'       => $periodo,
                'periodo_label' => $this->_mesLabelFromStr($periodo),
            ]);
        }

        // Buscar el próximo mes abierto a partir del mes siguiente al solicitado
        $cursor    = Carbon::parse($periodo)->addMonth()->startOfMonth();
        $maxIter   = 24; // seguridad: no buscar más de 2 años hacia adelante
        $proximo   = null;

        for ($i = 0; $i < $maxIter; $i++) {
            $cursorStr = $cursor->toDateString();
            $reg = DB::table('comision_periodo')
                ->where('periodo', $cursorStr)
                ->first();

            // Si no existe registro o el estado no es conciliado → es un período abierto
            if (!$reg || (int) $reg->estado !== ModelComisionPeriodo::ESTADO_CONCILIADO) {
                $proximo = $cursorStr;
                break;
            }
            $cursor->addMonth();
        }

        return response()->json([
            'conciliado'      => true,
            'periodo'         => $periodo,
            'periodo_label'   => $this->_mesLabelFromStr($periodo),
            'proximo_abierto' => $proximo,
            'proximo_label'   => $proximo ? $this->_mesLabelFromStr($proximo) : null,
        ]);
    }

    /* ══════════════════════════════════════════════════════════════════
     *  LISTADO DE PERÍODOS
     * ════════════════════════════════════════════════════════════════ */

    /**
     * Construye la tabla de períodos para la pantalla principal.
     * Incluye todos los meses del año en curso + histórico con comisiones.
     * Los meses futuros aparecen como estado "sin_abrir".
     */
    public function listarPeriodos()
    {
        $hoy        = Carbon::now();
        $anioActual = (int) $hoy->year;
        $mesActual  = (int) $hoy->month;

        // Registros existentes en comision_periodo
        $registros = DB::table('comision_periodo as cp')
            ->leftJoin('users as u', 'u.id', '=', 'cp.usuario_concilio')
            ->select(
                'cp.id',
                'cp.periodo',
                'cp.estado',
                'cp.total_comision',
                'cp.total_comision_escala',
                'cp.total_comision_politica_anterior',
                'cp.total_comision_global',
                'cp.cantidad_empleados',
                'cp.cantidad_facturas',
                'cp.observacion_conciliacion',
                'cp.fecha_conciliacion',
                'u.name as nombre_concilio'
            )
            ->orderByDesc('cp.periodo')
            ->get()
            ->keyBy(fn($r) => $r->periodo); // keyed by "YYYY-MM-DD"

        // Meses desde enero del año actual hasta diciembre
        $periodos = [];
        for ($m = 1; $m <= 12; $m++) {
            $periodos[] = Carbon::create($anioActual, $m, 1)->startOfMonth();
        }

        // Agregar meses históricos con comisiones de años anteriores
        $historicos = DB::table('comision_empleado')
            ->whereYear('mes_comision', '<', $anioActual)
            ->where('estado_id', 1)
            ->selectRaw('DATE_FORMAT(mes_comision, "%Y-%m-01") as p')
            ->distinct()
            ->pluck('p');

        foreach ($historicos as $h) {
            $c = Carbon::parse($h)->startOfMonth();
            // Evitar duplicados con el año actual
            if (!in_array($c->format('Y-m-d'), array_map(fn($x) => $x->format('Y-m-d'), $periodos))) {
                $periodos[] = $c;
            }
        }

        // Ordenar descendente
        usort($periodos, fn($a, $b) => $b->timestamp - $a->timestamp);

        // Totales live desde comision_empleado (solo para periodos abiertos o sin registro)
        $totalesLive = DB::table('comision_empleado')
            ->where('estado_id', 1)
            ->where('comision_acumulada', '>', 0)
            ->selectRaw('DATE_FORMAT(mes_comision, "%Y-%m-01") as periodo,
                         SUM(comision_acumulada) as total,
                         COUNT(DISTINCT users_comision) as empleados')
            ->groupBy('periodo')
            ->get()
            ->keyBy('periodo');

        // Facturas comisionadas vivas por período
        $facturasLive = DB::table('facturas_comision')
            ->where('estado_id', 1)
            ->selectRaw('DATE_FORMAT(fecha_cierre_factura, "%Y-%m-01") as periodo,
                         COUNT(DISTINCT factura_id) as facturas')
            ->groupBy('periodo')
            ->get()
            ->keyBy('periodo');

        // Retenciones en la fuente activas por período (afectan total abierto en vivo)
        $retencionesLive = DB::table('comision_retencion_fuente')
            ->where('estado', 1)
            ->selectRaw('periodo, SUM(monto_retencion) as total_retencion')
            ->groupBy('periodo')
            ->get()
            ->keyBy('periodo');

        $resultado = [];
        foreach ($periodos as $carbon) {
            $key    = $carbon->format('Y-m-d');
            $esFut  = ($carbon->year > $anioActual) ||
                      ($carbon->year === $anioActual && $carbon->month > $mesActual);

            $reg    = $registros->get($key);
            $live   = $totalesLive->get($key);
            $liveFac = $facturasLive->get($key);

            $totalLivePeriodo = (float) ($live->total ?? 0);
            $facturasLivePeriodo = (int) ($liveFac->facturas ?? 0);
            $tieneComisionesParaConciliar = ($totalLivePeriodo > 0) && ($facturasLivePeriodo > 0);

            if ($reg && (int) $reg->estado === ModelComisionPeriodo::ESTADO_CONCILIADO) {
                $estado = 'conciliado';
            } elseif ($tieneComisionesParaConciliar) {
                // Regla de negocio: si el mes ya tiene comisiones, debe poder conciliarse
                // aunque no sea el mes actual.
                $estado = 'abierto';
            } elseif ($esFut) {
                $estado = 'sin_abrir';
            } else {
                $estado = 'abierto';
            }

            // Para conciliados usamos el snapshot; para abiertos usamos live
            $retencionPeriodo = (float) ($retencionesLive->get($key)->total_retencion ?? 0);
            $totalLiveAbierto = max(0.0, (float) ($live->total ?? 0) - $retencionPeriodo);

            $totalEscala = $estado === 'conciliado'
                ? (float) ($reg->total_comision_escala ?? $reg->total_comision ?? 0)
                : $totalLiveAbierto;
            $totalPoliticaAnterior = (float) ($reg->total_comision_politica_anterior ?? 0);
            $totalGlobal = $estado === 'conciliado'
                ? (float) ($reg->total_comision_global ?? ($totalEscala + $totalPoliticaAnterior))
                : ($totalEscala + $totalPoliticaAnterior);

            $totalComision = round((float) $totalGlobal, 2);
            $cantEmpleados   = $estado === 'conciliado' ? (int) $reg->cantidad_empleados : (int) ($live->empleados ?? 0);
            $cantFacturas    = $estado === 'conciliado' ? (int) $reg->cantidad_facturas  : (int) ($liveFac->facturas ?? 0);

            $resultado[] = [
                'id'                      => $reg->id ?? null,
                'periodo'                 => $key,
                'periodo_label'           => $this->_mesLabel($carbon),
                'anio'                    => $carbon->year,
                'mes'                     => $carbon->month,
                'estado'                  => $estado,
                'total_comision_escala'   => round((float) $totalEscala, 2),
                'total_comision_politica_anterior' => round((float) $totalPoliticaAnterior, 2),
                'total_comision_global'   => round((float) $totalGlobal, 2),
                'total_comision'          => $totalComision,
                'total_comision_fmt'      => 'L ' . number_format($totalComision, 2),
                'cantidad_empleados'      => $cantEmpleados,
                'cantidad_facturas'       => $cantFacturas,
                'fecha_conciliacion'      => $reg->fecha_conciliacion ?? null,
                'usuario_concilio'        => $reg->nombre_concilio    ?? null,
                'observacion'             => $reg->observacion_conciliacion ?? null,
                'es_mes_actual'           => ($carbon->year === $anioActual && $carbon->month === $mesActual),
            ];
        }

        // KPIs resumen
        $kpis = [
            'total_abiertos'     => count(array_filter($resultado, fn($r) => $r['estado'] === 'abierto')),
            'total_conciliados'  => count(array_filter($resultado, fn($r) => $r['estado'] === 'conciliado')),
            'total_sin_abrir'    => count(array_filter($resultado, fn($r) => $r['estado'] === 'sin_abrir')),
            'monto_abierto'      => array_sum(array_map(fn($r) => $r['estado'] === 'abierto' ? $r['total_comision'] : 0, $resultado)),
            'monto_conciliado'   => array_sum(array_map(fn($r) => $r['estado'] === 'conciliado' ? $r['total_comision'] : 0, $resultado)),
        ];

        return response()->json([
            'periodos' => $resultado,
            'kpis'     => $kpis,
        ]);
    }

    /* ══════════════════════════════════════════════════════════════════
     *  CONCILIAR UN PERÍODO
     * ════════════════════════════════════════════════════════════════ */

    public function conciliarPeriodo(Request $request)
    {
        $periodoStr  = trim($request->input('periodo', ''));
        $observacion = trim($request->input('observacion', ''));

        if (!$periodoStr) {
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Período requerido.'], 422);
        }

        $periodo = Carbon::parse($periodoStr)->startOfMonth()->toDateString();

        // Verificar que no esté ya conciliado
        $existente = DB::table('comision_periodo')->where('periodo', $periodo)->first();
        if ($existente && (int) $existente->estado === ModelComisionPeriodo::ESTADO_CONCILIADO) {
            return response()->json(['icon' => 'warning', 'title' => 'Ya conciliado',
                'text' => 'Este período ya fue conciliado previamente.'], 422);
        }

        DB::beginTransaction();
        try {
            // Calcular snapshot en tiempo real
            $snapshot = $this->_calcularSnapshot($periodo);

            $totalEscala = round((float) ($snapshot['total_comision'] ?? 0), 2);
            $totalPolitica = round((float) ($existente ? ($existente->total_comision_politica_anterior ?? 0) : 0), 2);
            $totalGlobal = round($totalEscala + $totalPolitica, 2);
            $cantidadFacturasGlobal = max(
                (int) ($snapshot['cantidad_facturas'] ?? 0),
                (int) ($existente ? ($existente->cantidad_facturas ?? 0) : 0)
            );

            if ($cantidadFacturasGlobal <= 0 || $totalGlobal <= 0) {
                return response()->json([
                    'icon'  => 'warning',
                    'title' => 'Sin comisiones',
                    'text'  => 'Solo se puede conciliar un período que tenga al menos una factura comisionada.',
                ], 422);
            }

            // Upsert en comision_periodo
            if ($existente) {
                DB::table('comision_periodo')->where('id', $existente->id)->update([
                    'estado'                   => ModelComisionPeriodo::ESTADO_CONCILIADO,
                    'total_comision_escala'     => $totalEscala,
                    'total_comision_politica_anterior' => $totalPolitica,
                    'total_comision_global'     => $totalGlobal,
                    'total_comision'            => $totalGlobal,
                    'cantidad_empleados'        => $snapshot['cantidad_empleados'],
                    'cantidad_facturas'         => $cantidadFacturasGlobal,
                    'observacion_conciliacion'  => $observacion ?: null,
                    'usuario_concilio'          => Auth::id(),
                    'fecha_conciliacion'        => now(),
                    'updated_at'               => now(),
                ]);
                $periodoId = $existente->id;
            } else {
                $periodoId = DB::table('comision_periodo')->insertGetId([
                    'periodo'                   => $periodo,
                    'estado'                    => ModelComisionPeriodo::ESTADO_CONCILIADO,
                    'total_comision_escala'      => $totalEscala,
                    'total_comision_politica_anterior' => $totalPolitica,
                    'total_comision_global'      => $totalGlobal,
                    'total_comision'             => $totalGlobal,
                    'cantidad_empleados'         => $snapshot['cantidad_empleados'],
                    'cantidad_facturas'          => $cantidadFacturasGlobal,
                    'observacion_conciliacion'   => $observacion ?: null,
                    'usuario_concilio'           => Auth::id(),
                    'fecha_conciliacion'         => now(),
                    'created_at'                => now(),
                    'updated_at'                => now(),
                ]);
            }

            // Registrar en el log de auditoría
            ModelComisionPeriodoLog::create([
                'periodo'                     => $periodo,
                'comision_periodo_id'          => $periodoId,
                'accion'                       => ModelComisionPeriodoLog::ACCION_CONCILIACION,
                'estado_anterior'              => ModelComisionPeriodo::ESTADO_ABIERTO,
                'estado_nuevo'                 => ModelComisionPeriodo::ESTADO_CONCILIADO,
                'snapshot_total_comision'      => $totalGlobal,
                'snapshot_cantidad_empleados'  => $snapshot['cantidad_empleados'],
                'snapshot_cantidad_facturas'   => $cantidadFacturasGlobal,
                'snapshot_detalle_empleados'   => $snapshot['detalle_empleados'],
                'snapshot_detalle_facturas'    => $snapshot['detalle_facturas'],
                'observacion'                  => $observacion ?: null,
                'usuario_id'                   => Auth::id(),
                'usuario_nombre'               => Auth::user()->name,
            ]);

            if (DB::getSchemaBuilder()->hasTable('comision_politica_anterior_factura')) {
                DB::table('comision_politica_anterior_factura')
                    ->where('periodo', $periodo)
                    ->update([
                        'estado' => 1,
                        'comision_periodo_id' => $periodoId,
                        'usuario_concilio_id' => Auth::id(),
                        'fecha_conciliacion' => now(),
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();

            return response()->json([
                'icon'  => 'success',
                'title' => '¡Período Conciliado!',
                'text'  => "El período {$this->_mesLabelFromStr($periodo)} fue conciliado correctamente. No se acreditarán nuevas comisiones para este mes.",
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['icon' => 'error', 'title' => 'Error',
                'text' => 'Error al conciliar: ' . $e->getMessage()], 500);
        }
    }

    /* ══════════════════════════════════════════════════════════════════
     *  REABRIR UN PERÍODO CONCILIADO
     * ════════════════════════════════════════════════════════════════ */

    public function reabrirPeriodo(Request $request)
    {
        $periodoStr  = trim($request->input('periodo', ''));
        $observacion = trim($request->input('observacion', ''));

        if (!$periodoStr) {
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Período requerido.'], 422);
        }

        if ($observacion === '') {
            return response()->json(['icon' => 'warning', 'title' => 'Observación requerida',
                'text' => 'Debe ingresar una observación para reabrir el período.'], 422);
        }

        $periodo   = Carbon::parse($periodoStr)->startOfMonth()->toDateString();
        $existente = DB::table('comision_periodo')->where('periodo', $periodo)->first();

        if (!$existente || (int) $existente->estado !== ModelComisionPeriodo::ESTADO_CONCILIADO) {
            return response()->json(['icon' => 'warning', 'title' => 'No conciliado',
                'text' => 'Este período no está conciliado.'], 422);
        }

        DB::beginTransaction();
        try {
            // Snapshot del estado ANTES de reabrir (para el log)
            $snapshot = $this->_calcularSnapshot($periodo);

            // Reabrir el período
            DB::table('comision_periodo')->where('id', $existente->id)->update([
                'estado'      => ModelComisionPeriodo::ESTADO_ABIERTO,
                'updated_at'  => now(),
                // Limpiamos la fecha y usuario de conciliación para que quede limpio
                // (el historial está en el log)
                'usuario_concilio'   => null,
                'fecha_conciliacion' => null,
                'observacion_conciliacion' => null,
            ]);

            // Log de reapertura con snapshot completo del estado al momento de reabrir
            ModelComisionPeriodoLog::create([
                'periodo'                     => $periodo,
                'comision_periodo_id'          => $existente->id,
                'accion'                       => ModelComisionPeriodoLog::ACCION_REAPERTURA,
                'estado_anterior'              => ModelComisionPeriodo::ESTADO_CONCILIADO,
                'estado_nuevo'                 => ModelComisionPeriodo::ESTADO_ABIERTO,
                'snapshot_total_comision'      => $snapshot['total_comision'],
                'snapshot_cantidad_empleados'  => $snapshot['cantidad_empleados'],
                'snapshot_cantidad_facturas'   => $snapshot['cantidad_facturas'],
                'snapshot_detalle_empleados'   => $snapshot['detalle_empleados'],
                'snapshot_detalle_facturas'    => $snapshot['detalle_facturas'],
                'observacion'                  => $observacion,
                'usuario_id'                   => Auth::id(),
                'usuario_nombre'               => Auth::user()->name,
            ]);

            if (DB::getSchemaBuilder()->hasTable('comision_politica_anterior_factura')) {
                DB::table('comision_politica_anterior_factura')
                    ->where('periodo', $periodo)
                    ->update([
                        'estado' => 0,
                        'usuario_concilio_id' => null,
                        'fecha_conciliacion' => null,
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();

            return response()->json([
                'icon'  => 'success',
                'title' => 'Período Reabierto',
                'text'  => "El período {$this->_mesLabelFromStr($periodo)} fue reabierto. Se volverán a acreditar comisiones para este mes.",
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['icon' => 'error', 'title' => 'Error',
                'text' => 'Error al reabrir: ' . $e->getMessage()], 500);
        }
    }

    /* ══════════════════════════════════════════════════════════════════
     *  DETALLE DE UN PERÍODO (modal)
     * ════════════════════════════════════════════════════════════════ */

    public function detallePeriodo(Request $request)
    {
        $periodoStr = trim($request->input('periodo', ''));
        if (!$periodoStr) {
            return response()->json(['error' => 'Período requerido.'], 422);
        }

        $periodo = Carbon::parse($periodoStr)->startOfMonth()->toDateString();
        $snapshot = $this->_calcularSnapshot($periodo);

        $metaConciliacion = DB::table('comision_periodo as cp')
            ->leftJoin('users as uc', 'uc.id', '=', 'cp.usuario_concilio')
            ->where('cp.periodo', $periodo)
            ->select('cp.fecha_conciliacion', 'uc.name as conciliado_por')
            ->first();

        $retencionesActivas = DB::table('comision_retencion_fuente')
            ->where('periodo', $periodo)
            ->where('estado', 1)
            ->selectRaw('users_comision, SUM(monto_retencion) as total_retencion')
            ->groupBy('users_comision')
            ->pluck('total_retencion', 'users_comision');

        // Consolidado por empleado con facturas reales comisionadas del período.
        $empleadosBase = DB::select("
            SELECT
                map.user_id,
                u.name AS nombre,
                GROUP_CONCAT(DISTINCT r.nombre ORDER BY r.nombre SEPARATOR ', ') AS roles_asignados,
                COUNT(DISTINCT map.factura_id) AS facturas_reales,
                ROUND(SUM(map.monto_rol), 2) AS comision_bruta,
                MAX(map.fecha_cierre_factura) AS fecha_ult_modificacion
            FROM (
                SELECT
                    CASE fc.tipo_comision
                        WHEN 1 THEN f.users_id
                        WHEN 2 THEN f.users_id
                        WHEN 3 THEN f.vendedor
                        WHEN 4 THEN f.gestor_entrega
                        ELSE NULL
                    END AS user_id,
                    fc.factura_id,
                    fc.monto_rol,
                    fc.rol_id,
                    fc.fecha_cierre_factura
                FROM facturas_comision fc
                INNER JOIN factura f ON f.id = fc.factura_id
                WHERE fc.estado_id = 1
                  AND DATE_FORMAT(fc.fecha_cierre_factura, '%Y-%m-01') = ?
            ) map
            INNER JOIN users u ON u.id = map.user_id
            LEFT JOIN rol r ON r.id = map.rol_id
            WHERE map.user_id IS NOT NULL
            GROUP BY map.user_id, u.name
            ORDER BY comision_bruta DESC
        ", [$periodo]);

        $fechaConciliacion = null;
        if (!empty($metaConciliacion->fecha_conciliacion)) {
            $fechaConciliacion = Carbon::parse($metaConciliacion->fecha_conciliacion)->format('Y-m-d H:i:s');
        }

        $empleados = array_map(function ($row) use ($retencionesActivas, $fechaConciliacion, $metaConciliacion) {
            $bruto = (float) ($row->comision_bruta ?? 0);
            $ret = (float) ($retencionesActivas[$row->user_id] ?? 0);
            $retAjustada = min($ret, $bruto);
            $neto = max(0.0, $bruto - $retAjustada);

            return [
                'user_id'               => (int) $row->user_id,
                'nombre'                => (string) $row->nombre,
                'rol'                   => (string) ($row->roles_asignados ?? '—'),
                'roles_asignados'       => (string) ($row->roles_asignados ?? '—'),
                'facturas'              => (int) ($row->facturas_reales ?? 0),
                'facturas_reales'       => (int) ($row->facturas_reales ?? 0),
                'comision_acumulada'    => round($neto, 2),
                'comision_conciliada'   => round($neto, 2),
                'fecha_ult_modificacion'=> $row->fecha_ult_modificacion,
                'fecha_conciliacion'    => $fechaConciliacion,
                'conciliado_por'        => $metaConciliacion->conciliado_por ?? null,
            ];
        }, $empleadosBase);

        // Facturas comisionadas en este período
        $facturas = DB::select("
            SELECT
                fc.factura_id,
                f.cai AS correlativo,
                cl.nombre AS cliente,
                fc.monto_rol,
                r.nombre AS rol,
                fc.tipo_comision,
                fc.fecha_cierre_factura,
                u.name AS empleado
            FROM facturas_comision fc
            INNER JOIN factura f  ON f.id  = fc.factura_id
            INNER JOIN cliente cl ON cl.id = f.cliente_id
            INNER JOIN rol r      ON r.id  = fc.rol_id
            INNER JOIN users u ON (
                CASE fc.tipo_comision
                    WHEN 1 THEN u.id = f.users_id
                    WHEN 2 THEN u.id = f.users_id
                    WHEN 3 THEN u.id = f.vendedor
                    WHEN 4 THEN u.id = f.gestor_entrega
                    ELSE 1=0
                END
            )
            WHERE fc.estado_id = 1
              AND DATE_FORMAT(fc.fecha_cierre_factura, '%Y-%m-01') = ?
            ORDER BY fc.fecha_cierre_factura DESC
        ", [$periodo]);

        // Historial de logs de este período
        $logs = DB::select("
            SELECT
                cpl.accion,
                cpl.estado_anterior,
                cpl.estado_nuevo,
                cpl.snapshot_total_comision,
                cpl.snapshot_cantidad_empleados,
                cpl.snapshot_cantidad_facturas,
                cpl.observacion,
                cpl.usuario_nombre,
                cpl.created_at
            FROM comision_periodo_log cpl
            WHERE cpl.periodo = ?
            ORDER BY cpl.id DESC
        ", [$periodo]);

        return response()->json([
            'periodo'   => $periodo,
            'label'     => $this->_mesLabelFromStr($periodo),
            'resumen'   => [
                'total_bruto'           => $snapshot['total_bruto'],
                'total_retencion'       => $snapshot['total_retencion'],
                'total_neto'            => $snapshot['total_comision'],
                'cantidad_empleados'    => $snapshot['cantidad_empleados'],
                'cantidad_facturas'     => $snapshot['cantidad_facturas'],
            ],
            'empleados' => $empleados,
            'facturas'  => $facturas,
            'logs'      => $logs,
        ]);
    }

    public function exportarResumenEmpleado(Request $request)
    {
        $periodoStr = trim((string) $request->input('periodo', ''));
        $userId = (int) $request->input('user_id', 0);

        if ($periodoStr === '' || $userId <= 0) {
            return response()->json(['error' => 'Período y empleado son requeridos.'], 422);
        }

        $periodo = Carbon::parse($periodoStr)->startOfMonth()->toDateString();
        $data = $this->_construirDataResumenConciliadoEmpleado($periodo, $userId);

        if (empty($data)) {
            return response()->json(['error' => 'No se encontraron datos para el empleado en el período.'], 404);
        }

        $slugEmpleado = preg_replace('/[^A-Za-z0-9]+/', '_', (string) ($data['empleado'] ?? ('empleado_' . $userId)));
        $slugEmpleado = trim((string) $slugEmpleado, '_');
        $slugEmpleado = $slugEmpleado !== '' ? strtolower($slugEmpleado) : ('empleado_' . $userId);

        $fileName = 'resumen_conciliacion_' . $periodo . '_' . $slugEmpleado . '.xlsx';

        return Excel::download(
            new ConciliacionResumenMasivoExport([$data], $periodo, $this->_mesLabelFromStr($periodo)),
            $fileName
        );
    }

    public function exportarResumenMasivo(Request $request)
    {
        $periodoStr = trim((string) $request->input('periodo', ''));

        if ($periodoStr === '') {
            return response()->json(['error' => 'Período requerido.'], 422);
        }

        $periodo = Carbon::parse($periodoStr)->startOfMonth()->toDateString();

        $usuariosPeriodo = DB::table('facturas_comision as fc')
            ->join('factura as f', 'f.id', '=', 'fc.factura_id')
            ->where('fc.estado_id', 1)
            ->whereRaw("DATE_FORMAT(fc.fecha_cierre_factura, '%Y-%m-01') = ?", [$periodo])
            ->selectRaw(
                "DISTINCT CASE fc.tipo_comision
                    WHEN 1 THEN f.users_id
                    WHEN 2 THEN f.users_id
                    WHEN 3 THEN f.vendedor
                    WHEN 4 THEN f.gestor_entrega
                    ELSE NULL
                 END as user_id"
            )
            ->pluck('user_id')
            ->filter(fn($id) => !is_null($id) && (int) $id > 0)
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        if ($usuariosPeriodo->isEmpty()) {
            return response()->json(['error' => 'No hay empleados conciliados con facturas para ese período.'], 404);
        }

        $data = [];
        foreach ($usuariosPeriodo as $userId) {
            $row = $this->_construirDataResumenConciliadoEmpleado($periodo, $userId);
            if (!empty($row)) {
                $data[] = $row;
            }
        }

        if (empty($data)) {
            return response()->json(['error' => 'No se encontraron datos para exportar.'], 404);
        }

        usort($data, function ($a, $b) {
            return strcasecmp((string) ($a['empleado'] ?? ''), (string) ($b['empleado'] ?? ''));
        });

        $fileName = 'resumen_conciliacion_masivo_' . $periodo . '.xlsx';

        return Excel::download(
            new ConciliacionResumenMasivoExport($data, $periodo, $this->_mesLabelFromStr($periodo)),
            $fileName
        );
    }

    /* ══════════════════════════════════════════════════════════════════
     *  RETENCIÓN EN LA FUENTE — GESTIÓN POR EMPLEADO
     * ════════════════════════════════════════════════════════════════ */

    public function resumenRetencionFuente(Request $request)
    {
        $periodoStr = trim($request->input('periodo', ''));
        if (!$periodoStr) {
            return response()->json(['error' => 'Período requerido.'], 422);
        }

        $periodo = Carbon::parse($periodoStr)->startOfMonth()->toDateString();
        $resumen = $this->_construirResumenRetencionFuente($periodo);

        $historial = DB::table('comision_retencion_fuente as rf')
            ->leftJoin('users as ua', 'ua.id', '=', 'rf.usuario_aplico')
            ->leftJoin('users as ur', 'ur.id', '=', 'rf.usuario_revirtio')
            ->leftJoin('users as ue', 'ue.id', '=', 'rf.users_comision')
            ->where('rf.periodo', $periodo)
            ->orderByDesc('rf.id')
            ->get([
                'rf.id',
                'rf.periodo',
                'rf.users_comision as user_id',
                'ue.name as empleado_nombre',
                'rf.monto_retencion',
                'rf.comentario',
                'rf.estado',
                'rf.created_at as fecha_aplicacion',
                'rf.fecha_reversion',
                'rf.comentario_reversion',
                'ua.name as usuario_aplico_nombre',
                'ur.name as usuario_revirtio_nombre',
            ]);

        return response()->json([
            'periodo'   => $periodo,
            'label'     => $this->_mesLabelFromStr($periodo),
            'resumen'   => $resumen,
            'historial' => $historial,
        ]);
    }

    public function aplicarRetencionFuente(Request $request)
    {
        $request->validate([
            'periodo'   => 'required|date',
            'user_id'   => 'required|integer',
            'monto'     => 'required|numeric|min:0.01',
            'comentario'=> 'required|string|max:500',
        ], [
            'comentario.required' => 'Debe ingresar un comentario de la retención en la fuente.',
        ]);

        $periodo = Carbon::parse($request->periodo)->startOfMonth()->toDateString();
        $userId  = (int) $request->user_id;
        $monto   = round((float) $request->monto, 2);

        DB::beginTransaction();
        try {
            $comisionBruta = (float) DB::table('comision_empleado')
                ->where('mes_comision', $periodo)
                ->where('users_comision', $userId)
                ->where('estado_id', 1)
                ->sum('comision_acumulada');

            if ($comisionBruta <= 0) {
                return response()->json([
                    'icon'  => 'warning',
                    'title' => 'Sin comisión',
                    'text'  => 'El empleado no tiene comisión para este período.',
                ], 422);
            }

            $activa = DB::table('comision_retencion_fuente')
                ->where('periodo', $periodo)
                ->where('users_comision', $userId)
                ->where('estado', 1)
                ->first();

            if ($activa) {
                return response()->json([
                    'icon'  => 'warning',
                    'title' => 'Retención activa',
                    'text'  => 'Ya existe una retención activa para este empleado en el período. Reviértala antes de aplicar una nueva.',
                ], 422);
            }

            if ($monto > $comisionBruta) {
                return response()->json([
                    'icon'  => 'warning',
                    'title' => 'Monto inválido',
                    'text'  => 'La retención no puede ser mayor a la comisión bruta del empleado en este período.',
                ], 422);
            }

            DB::table('comision_retencion_fuente')->insert([
                'periodo'              => $periodo,
                'users_comision'       => $userId,
                'monto_retencion'      => $monto,
                'comentario'           => trim((string) $request->comentario),
                'estado'               => 1,
                'usuario_aplico'       => Auth::id(),
                'usuario_nombre_aplico'=> Auth::user()->name,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            DB::commit();

            return response()->json([
                'icon'  => 'success',
                'title' => 'Retención aplicada',
                'text'  => 'La retención fue aplicada y se deducirá del total a comisionar del empleado.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'No se pudo aplicar la retención: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function revertirRetencionFuente(Request $request)
    {
        $request->validate([
            'retencion_id' => 'required|integer',
            'comentario'   => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $ret = DB::table('comision_retencion_fuente')
                ->where('id', (int) $request->retencion_id)
                ->first();

            if (!$ret || (int) $ret->estado !== 1) {
                return response()->json([
                    'icon'  => 'warning',
                    'title' => 'No disponible',
                    'text'  => 'La retención no existe o ya fue revertida.',
                ], 422);
            }

            DB::table('comision_retencion_fuente')
                ->where('id', (int) $request->retencion_id)
                ->update([
                    'estado'                 => 0,
                    'usuario_revirtio'       => Auth::id(),
                    'usuario_nombre_revirtio'=> Auth::user()->name,
                    'fecha_reversion'        => now(),
                    'comentario_reversion'   => trim((string) ($request->comentario ?? '')) ?: null,
                    'updated_at'             => now(),
                ]);

            DB::commit();

            return response()->json([
                'icon'  => 'success',
                'title' => 'Retención revertida',
                'text'  => 'La retención fue revertida correctamente.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'No se pudo revertir la retención: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function historialRetencionFuente(Request $request)
    {
        $anio = (int) $request->input('anio', 0);

        $query = DB::table('comision_retencion_fuente as rf')
            ->leftJoin('users as u', 'u.id', '=', 'rf.users_comision')
            ->orderByDesc('rf.created_at');

        if ($anio > 0) {
            $query->whereYear('rf.periodo', $anio);
        }

        $rows = $query->get([
            'rf.id',
            'rf.periodo',
            'rf.users_comision as user_id',
            'u.name as empleado_nombre',
            'rf.monto_retencion',
            'rf.comentario',
            'rf.estado',
            'rf.usuario_nombre_aplico',
            'rf.created_at as fecha_aplicacion',
            'rf.usuario_nombre_revirtio',
            'rf.fecha_reversion',
            'rf.comentario_reversion',
        ]);

        $aniosDisponibles = DB::table('comision_retencion_fuente')
            ->selectRaw('YEAR(periodo) as anio')
            ->distinct()
            ->orderByDesc('anio')
            ->pluck('anio');

        return response()->json([
            'rows'  => $rows,
            'anios' => $aniosDisponibles,
            'total' => $rows->count(),
        ]);
    }

    /* ══════════════════════════════════════════════════════════════════
     *  HELPERS PRIVADOS
     * ════════════════════════════════════════════════════════════════ */

    /**
     * Construye el snapshot completo de un período desde las tablas live.
     */
    private function _calcularSnapshot(string $periodo): array
    {
        $empleadosBase = DB::select(" 
            SELECT
                ce.users_comision AS user_id,
                u.name            AS nombre,
                SUM(ce.comision_acumulada) AS comision_bruta,
                MAX(ce.fecha_ult_modificacion) AS fecha_ult_modificacion
            FROM comision_empleado ce
            INNER JOIN users u ON u.id = ce.users_comision
            WHERE ce.mes_comision = ?
              AND ce.estado_id    = 1
              AND ce.comision_acumulada > 0
            GROUP BY ce.users_comision, u.name
            ORDER BY SUM(ce.comision_acumulada) DESC
        ", [$periodo]);

        $retencionesActivas = DB::table('comision_retencion_fuente')
            ->where('periodo', $periodo)
            ->where('estado', 1)
            ->selectRaw('users_comision, SUM(monto_retencion) as total_retencion')
            ->groupBy('users_comision')
            ->pluck('total_retencion', 'users_comision');

        $empleados = [];
        $totalBruto = 0.0;
        $totalRetencion = 0.0;
        $totalNeto = 0.0;

        foreach ($empleadosBase as $e) {
            $bruto = (float) ($e->comision_bruta ?? 0);
            $ret = (float) ($retencionesActivas[$e->user_id] ?? 0);
            $retAjustada = min($ret, $bruto);
            $neto = max(0.0, $bruto - $retAjustada);

            $totalBruto += $bruto;
            $totalRetencion += $retAjustada;
            $totalNeto += $neto;

            $empleados[] = [
                'user_id'              => (int) $e->user_id,
                'nombre'               => (string) $e->nombre,
                'comision_bruta'       => round($bruto, 2),
                'retencion_fuente'     => round($retAjustada, 2),
                'comision_neta'        => round($neto, 2),
                'fecha_ult_modificacion' => $e->fecha_ult_modificacion,
            ];
        }

        $facturas = DB::select("
            SELECT
                fc.factura_id,
                fc.monto_rol,
                fc.rol_id,
                fc.tipo_comision,
                fc.fecha_cierre_factura
            FROM facturas_comision fc
            WHERE fc.estado_id = 1
              AND DATE_FORMAT(fc.fecha_cierre_factura,'%Y-%m-01') = ?
        ", [$periodo]);

        $cantEmpleados  = count($empleados);
        $cantFacturas   = count(array_unique(array_column($facturas, 'factura_id')));

        return [
            'total_bruto'         => round((float) $totalBruto, 2),
            'total_retencion'     => round((float) $totalRetencion, 2),
            'total_comision'      => round((float) $totalNeto, 2),
            'cantidad_empleados'  => $cantEmpleados,
            'cantidad_facturas'   => $cantFacturas,
            'detalle_empleados'   => $empleados,
            'detalle_facturas'    => array_map(fn($f) => (array) $f, $facturas),
        ];
    }

    /**
     * Construye un resumen por empleado para retención en la fuente.
     */
    private function _construirResumenRetencionFuente(string $periodo): array
    {
        $empleadosBase = DB::select(" 
            SELECT
                ce.users_comision AS user_id,
                u.name            AS nombre,
                SUM(ce.comision_acumulada) AS comision_bruta
            FROM comision_empleado ce
            INNER JOIN users u ON u.id = ce.users_comision
            WHERE ce.mes_comision = ?
              AND ce.estado_id    = 1
              AND ce.comision_acumulada > 0
            GROUP BY ce.users_comision, u.name
            ORDER BY SUM(ce.comision_acumulada) DESC
        ", [$periodo]);

        $retencionesActivas = DB::table('comision_retencion_fuente as rf')
            ->leftJoin('users as ua', 'ua.id', '=', 'rf.usuario_aplico')
            ->where('rf.periodo', $periodo)
            ->where('rf.estado', 1)
            ->get([
                'rf.id',
                'rf.users_comision',
                'rf.monto_retencion',
                'rf.comentario',
                'rf.created_at',
                'ua.name as usuario_aplico_nombre',
            ])
            ->keyBy('users_comision');

        $empleados = [];
        $totalBruto = 0.0;
        $totalRet = 0.0;
        $totalNeto = 0.0;

        foreach ($empleadosBase as $e) {
            $bruto = (float) ($e->comision_bruta ?? 0);
            $retActivo = $retencionesActivas->get($e->user_id);
            $ret = (float) ($retActivo->monto_retencion ?? 0);
            $retAjustada = min($ret, $bruto);
            $neto = max(0.0, $bruto - $retAjustada);

            $totalBruto += $bruto;
            $totalRet += $retAjustada;
            $totalNeto += $neto;

            $empleados[] = [
                'user_id'             => (int) $e->user_id,
                'nombre'              => (string) $e->nombre,
                'comision_bruta'      => round($bruto, 2),
                'retencion_fuente'    => round($retAjustada, 2),
                'comision_neta'       => round($neto, 2),
                'retencion_activa_id' => $retActivo ? (int) $retActivo->id : null,
                'comentario_retencion'=> $retActivo->comentario ?? null,
                'fecha_retencion'     => $retActivo->created_at ?? null,
                'usuario_aplico'      => $retActivo->usuario_aplico_nombre ?? null,
            ];
        }

        return [
            'total_bruto'        => round($totalBruto, 2),
            'total_retencion'    => round($totalRet, 2),
            'total_neto'         => round($totalNeto, 2),
            'cantidad_empleados' => count($empleados),
            'empleados'          => $empleados,
        ];
    }

    private function _construirDataResumenConciliadoEmpleado(string $periodo, int $userId): array
    {
        $empleadoNombre = (string) (DB::table('users')->where('id', $userId)->value('name') ?? 'Empleado #' . $userId);

        $metaConciliacion = DB::table('comision_periodo as cp')
            ->leftJoin('users as uc', 'uc.id', '=', 'cp.usuario_concilio')
            ->where('cp.periodo', $periodo)
            ->select('cp.fecha_conciliacion', 'uc.name as conciliado_por')
            ->first();

        $facturasEmpleado = DB::table('facturas_comision as fc')
            ->join('factura as f', 'f.id', '=', 'fc.factura_id')
            ->leftJoin('rol as r', 'r.id', '=', 'fc.rol_id')
            ->where('fc.estado_id', 1)
            ->whereRaw("DATE_FORMAT(fc.fecha_cierre_factura, '%Y-%m-01') = ?", [$periodo])
            ->whereRaw(
                "CASE fc.tipo_comision
                    WHEN 1 THEN f.users_id
                    WHEN 2 THEN f.users_id
                    WHEN 3 THEN f.vendedor
                    WHEN 4 THEN f.gestor_entrega
                    ELSE NULL
                 END = ?",
                [$userId]
            )
            ->groupBy('fc.factura_id')
            ->selectRaw(
                "fc.factura_id,
                 MAX(f.fecha_emision) as fecha_emision,
                 MAX(fc.fecha_cierre_factura) as fecha_cierre,
                 MAX(f.total) as total_factura,
                 SUM(fc.monto_rol) as comision_bruta_factura,
                 GROUP_CONCAT(DISTINCT r.nombre ORDER BY r.nombre SEPARATOR ', ') as roles_factura"
            )
            ->get();

        if ($facturasEmpleado->isEmpty()) {
            return [];
        }

        $facturaIds = $facturasEmpleado->pluck('factura_id')->map(fn($id) => (int) $id)->values()->all();

        $abonosPorFactura = DB::table('abonos_creditos as ac')
            ->whereIn('ac.factura_id', $facturaIds)
            ->where('ac.estado_abono', 1)
            ->groupBy('ac.factura_id')
            ->selectRaw('ac.factura_id, SUM(ac.monto_abonado) as total_abonos')
            ->pluck('total_abonos', 'factura_id');

        $roles = DB::table('facturas_comision as fc')
            ->join('factura as f', 'f.id', '=', 'fc.factura_id')
            ->leftJoin('rol as r', 'r.id', '=', 'fc.rol_id')
            ->where('fc.estado_id', 1)
            ->whereRaw("DATE_FORMAT(fc.fecha_cierre_factura, '%Y-%m-01') = ?", [$periodo])
            ->whereRaw(
                "CASE fc.tipo_comision
                    WHEN 1 THEN f.users_id
                    WHEN 2 THEN f.users_id
                    WHEN 3 THEN f.vendedor
                    WHEN 4 THEN f.gestor_entrega
                    ELSE NULL
                 END = ?",
                [$userId]
            )
            ->whereNotNull('r.nombre')
            ->distinct()
            ->orderBy('r.nombre')
            ->pluck('r.nombre')
            ->values()
            ->all();

        $totalCobrado = 0.0;
        $totalComisionBruta = 0.0;
        $meses = [];

        foreach ($facturasEmpleado as $factura) {
            $facturaId = (int) $factura->factura_id;
            $abonosFactura = (float) ($abonosPorFactura[$facturaId] ?? 0);

            $totalCobrado += $abonosFactura;
            $totalComisionBruta += (float) ($factura->comision_bruta_factura ?? 0);

            $fechaEmision = $factura->fecha_emision ? Carbon::parse($factura->fecha_emision) : null;
            if (!$fechaEmision) {
                continue;
            }

            $mesClave = $fechaEmision->format('Y-m');
            if (!isset($meses[$mesClave])) {
                $meses[$mesClave] = [
                    'mes_clave' => $mesClave,
                    'mes_label' => $this->_mesLabel(Carbon::create($fechaEmision->year, $fechaEmision->month, 1)),
                    'cantidad_facturas' => 0,
                    'total_cobrado' => 0.0,
                ];
            }

            $meses[$mesClave]['cantidad_facturas']++;
            $meses[$mesClave]['total_cobrado'] += $abonosFactura;
        }

        ksort($meses);
        $meses = array_values(array_map(function ($row) {
            $row['cantidad_facturas'] = (int) $row['cantidad_facturas'];
            $row['total_cobrado'] = round((float) $row['total_cobrado'], 2);
            return $row;
        }, $meses));

        $retencionFuente = (float) DB::table('comision_retencion_fuente')
            ->where('periodo', $periodo)
            ->where('users_comision', $userId)
            ->where('estado', 1)
            ->sum('monto_retencion');

        $retencionAjustada = min($retencionFuente, $totalComisionBruta);
        $comisionNeta = max(0.0, $totalComisionBruta - $retencionAjustada);

        return [
            'periodo' => $periodo,
            'periodo_label' => $this->_mesLabelFromStr($periodo),
            'empleado_id' => $userId,
            'empleado' => $empleadoNombre,
            'roles' => !empty($roles) ? implode(', ', $roles) : 'Sin rol definido',
            'fecha_conciliacion' => $metaConciliacion->fecha_conciliacion ?? null,
            'conciliado_por' => $metaConciliacion->conciliado_por ?? 'No registrado',
            'total_facturas' => count($facturaIds),
            'total_cobrado' => round($totalCobrado, 2),
            'comision_bruta' => round($totalComisionBruta, 2),
            'retencion_fuente' => round($retencionAjustada, 2),
            'comision_neta' => round($comisionNeta, 2),
            'meses_cobrados' => $meses,
        ];
    }

    private function _mesLabel(Carbon $c): string
    {
        $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                  'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        return $meses[$c->month - 1] . ' ' . $c->year;
    }

    private function _mesLabelFromStr(string $periodo): string
    {
        return $this->_mesLabel(Carbon::parse($periodo));
    }

    /* ══════════════════════════════════════════════════════════════════
     *  DÍAS DE GRACIA — CATÁLOGO
     * ════════════════════════════════════════════════════════════════ */

    /**
     * Retorna todos los roles con su configuración de días de gracia.
     * Incluye todos los roles del sistema, con o sin configuración.
     */
    public function listarDiasGracia()
    {
        $roles = DB::table('rol')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        // Configuración existente indexada por rol_id + tipo
        $config = DB::table('dias_gracia_comision')
            ->get()
            ->groupBy('rol_id');

        $resultado = $roles->map(function ($rol) use ($config) {
            $cfg = $config->get($rol->id, collect());

            $contado = $cfg->firstWhere('tipo_factura', 'contado');
            $credito  = $cfg->firstWhere('tipo_factura', 'credito');

            return [
                'rol_id'               => $rol->id,
                'rol_nombre'           => $rol->nombre,
                'contado_dias'         => $contado ? (int) $contado->dias_gracia        : null,
                'contado_retencion'    => $contado ? (float) $contado->porcentaje_retencion : null,
                'contado_descripcion'  => $contado ? $contado->descripcion               : null,
                'contado_id'           => $contado ? (int) $contado->id                  : null,
                'credito_dias'         => $credito  ? (int) $credito->dias_gracia        : null,
                'credito_retencion'    => $credito  ? (float) $credito->porcentaje_retencion : null,
                'credito_descripcion'  => $credito  ? $credito->descripcion              : null,
                'credito_id'           => $credito  ? (int) $credito->id                 : null,
            ];
        });

        return response()->json(['roles' => $resultado]);
    }

    /**
     * Guarda o actualiza los días de gracia para un rol + tipo.
     */
    public function guardarDiasGracia(Request $request)
    {
        $request->validate([
            'rol_id'               => 'required|integer',
            'tipo'                 => 'required|in:contado,credito',
            'dias'                 => 'required|integer|min:0|max:9999',
            'porcentaje_retencion' => 'nullable|numeric|min:0|max:100',
            'descripcion'          => 'nullable|string|max:200',
        ]);

        $userId = Auth::id();

        DB::table('dias_gracia_comision')->updateOrInsert(
            ['rol_id' => $request->rol_id, 'tipo_factura' => $request->tipo],
            [
                'dias_gracia'           => $request->dias,
                'porcentaje_retencion'  => $request->porcentaje_retencion ?? 0,
                'descripcion'           => $request->descripcion ?? null,
                'updated_by'            => $userId,
                'updated_at'            => now(),
                'created_at'            => now(),
            ]
        );

        $rolNombre = DB::table('rol')->where('id', $request->rol_id)->value('nombre') ?? 'Rol';
        $tipoLabel = $request->tipo === 'contado' ? 'Contado' : 'Crédito';

        return response()->json([
            'icon'  => 'success',
            'title' => 'Guardado',
            'text'  => "Días de gracia para {$rolNombre} ({$tipoLabel}): {$request->dias} días",
        ]);
    }

    /* ══════════════════════════════════════════════════════════════════
     *  HISTORIAL DE AUDITORÍA — snapshots de conciliación y reaperturas
     * ════════════════════════════════════════════════════════════════ */

    public function listarAuditoriaLogs(Request $request)
    {
        $anio = (int) $request->input('anio', 0);

        $query = DB::table('comision_periodo_log')
            ->orderByDesc('created_at');

        if ($anio > 0) {
            $query->whereYear('periodo', $anio);
        }

        $logs = $query->get();

        $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                  'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

        $result = $logs->map(function ($row) use ($meses) {
            $c = Carbon::parse($row->periodo);
            return [
                'id'                          => $row->id,
                'periodo'                     => $row->periodo,
                'periodo_label'               => $meses[$c->month - 1] . ' ' . $c->year,
                'accion'                      => $row->accion,
                'estado_anterior'             => (int) $row->estado_anterior,
                'estado_nuevo'                => (int) $row->estado_nuevo,
                'snapshot_total_comision'     => (float) $row->snapshot_total_comision,
                'snapshot_total_fmt'          => 'L ' . number_format((float) $row->snapshot_total_comision, 2),
                'snapshot_cantidad_empleados' => (int) $row->snapshot_cantidad_empleados,
                'snapshot_cantidad_facturas'  => (int) $row->snapshot_cantidad_facturas,
                'snapshot_detalle_empleados'  => $row->snapshot_detalle_empleados
                                                    ? json_decode($row->snapshot_detalle_empleados, true)
                                                    : [],
                'snapshot_detalle_facturas'   => $row->snapshot_detalle_facturas
                                                    ? json_decode($row->snapshot_detalle_facturas, true)
                                                    : [],
                'observacion'                 => $row->observacion,
                'usuario_nombre'              => $row->usuario_nombre,
                'fecha'                       => Carbon::parse($row->created_at)->format('d/m/Y H:i'),
                'fecha_iso'                   => $row->created_at,
            ];
        });

        // Años disponibles para filtro
        $aniosDisponibles = DB::table('comision_periodo_log')
            ->selectRaw('YEAR(periodo) as anio')
            ->distinct()
            ->orderByDesc('anio')
            ->pluck('anio');

        return response()->json([
            'logs'  => $result,
            'anios' => $aniosDisponibles,
            'total' => $result->count(),
        ]);
    }
}

