<?php

namespace App\Http\Livewire\Comisiones\Escalado;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Auth;
use App\Models\Comisiones\ModelComisionPeriodo;
use App\Models\Comisiones\ModelComisionPeriodoLog;
use App\Models\Comisiones\ModelDiasGraciaComision;

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

        $resultado = [];
        foreach ($periodos as $carbon) {
            $key    = $carbon->format('Y-m-d');
            $esFut  = ($carbon->year > $anioActual) ||
                      ($carbon->year === $anioActual && $carbon->month > $mesActual);

            $reg    = $registros->get($key);
            $live   = $totalesLive->get($key);
            $liveFac = $facturasLive->get($key);

            if ($esFut) {
                $estado = 'sin_abrir';
            } elseif ($reg && (int) $reg->estado === ModelComisionPeriodo::ESTADO_CONCILIADO) {
                $estado = 'conciliado';
            } else {
                $estado = 'abierto';
            }

            // Para conciliados usamos el snapshot; para abiertos usamos live
            $totalComision   = $estado === 'conciliado' ? (float) $reg->total_comision  : (float) ($live->total ?? 0);
            $cantEmpleados   = $estado === 'conciliado' ? (int) $reg->cantidad_empleados : (int) ($live->empleados ?? 0);
            $cantFacturas    = $estado === 'conciliado' ? (int) $reg->cantidad_facturas  : (int) ($liveFac->facturas ?? 0);

            $resultado[] = [
                'id'                      => $reg->id ?? null,
                'periodo'                 => $key,
                'periodo_label'           => $this->_mesLabel($carbon),
                'anio'                    => $carbon->year,
                'mes'                     => $carbon->month,
                'estado'                  => $estado,
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

        // No conciliar meses futuros
        if ($periodo > Carbon::now()->startOfMonth()->toDateString()) {
            return response()->json(['icon' => 'warning', 'title' => 'No permitido',
                'text' => 'No se puede conciliar un período futuro.'], 422);
        }

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

            // Upsert en comision_periodo
            if ($existente) {
                DB::table('comision_periodo')->where('id', $existente->id)->update([
                    'estado'                   => ModelComisionPeriodo::ESTADO_CONCILIADO,
                    'total_comision'            => $snapshot['total_comision'],
                    'cantidad_empleados'        => $snapshot['cantidad_empleados'],
                    'cantidad_facturas'         => $snapshot['cantidad_facturas'],
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
                    'total_comision'             => $snapshot['total_comision'],
                    'cantidad_empleados'         => $snapshot['cantidad_empleados'],
                    'cantidad_facturas'          => $snapshot['cantidad_facturas'],
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
                'snapshot_total_comision'      => $snapshot['total_comision'],
                'snapshot_cantidad_empleados'  => $snapshot['cantidad_empleados'],
                'snapshot_cantidad_facturas'   => $snapshot['cantidad_facturas'],
                'snapshot_detalle_empleados'   => $snapshot['detalle_empleados'],
                'snapshot_detalle_facturas'    => $snapshot['detalle_facturas'],
                'observacion'                  => $observacion ?: null,
                'usuario_id'                   => Auth::id(),
                'usuario_nombre'               => Auth::user()->name,
            ]);

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

        // Empleados con comisiones en este período
        $empleados = DB::select("
            SELECT
                u.id   AS user_id,
                u.name AS nombre,
                r.nombre AS rol,
                ce.comision_acumulada,
                ce.fecha_ult_modificacion,
                COUNT(DISTINCT fc.factura_id) AS facturas
            FROM comision_empleado ce
            INNER JOIN users u ON u.id  = ce.users_comision
            INNER JOIN rol   r ON r.id  = ce.rol_id
            LEFT  JOIN facturas_comision fc
                ON  fc.rol_id    = ce.rol_id
                AND fc.estado_id = 1
                AND DATE_FORMAT(fc.fecha_cierre_factura,'%Y-%m-01') = ?
            WHERE ce.mes_comision = ?
              AND ce.estado_id    = 1
              AND ce.comision_acumulada > 0
            GROUP BY ce.id, u.id, u.name, r.nombre, ce.comision_acumulada, ce.fecha_ult_modificacion
            ORDER BY ce.comision_acumulada DESC
        ", [$periodo, $periodo]);

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
            'empleados' => $empleados,
            'facturas'  => $facturas,
            'logs'      => $logs,
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
        $empleados = DB::select("
            SELECT
                u.id   AS user_id,
                u.name AS nombre,
                r.nombre AS rol,
                r.id AS rol_id,
                ce.mes_comision,
                ce.comision_acumulada,
                COUNT(DISTINCT fc.factura_id) AS cantidad_facturas
            FROM comision_empleado ce
            INNER JOIN users u ON u.id = ce.users_comision
            INNER JOIN rol   r ON r.id = ce.rol_id
            LEFT  JOIN facturas_comision fc
                ON  fc.rol_id    = ce.rol_id
                AND fc.estado_id = 1
                AND DATE_FORMAT(fc.fecha_cierre_factura,'%Y-%m-01') = ?
            WHERE ce.mes_comision = ?
              AND ce.estado_id    = 1
              AND ce.comision_acumulada > 0
            GROUP BY ce.id, u.id, u.name, r.nombre, r.id, ce.mes_comision, ce.comision_acumulada
            ORDER BY ce.comision_acumulada DESC
        ", [$periodo, $periodo]);

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

        $totalComision  = array_sum(array_column($empleados, 'comision_acumulada'));
        $cantEmpleados  = count(array_filter(array_unique(array_column($empleados, 'user_id')), function($uid) use ($empleados) {
            foreach ($empleados as $e) {
                if ($e->user_id == $uid && $e->comision_acumulada > 0) return true;
            }
            return false;
        }));
        $cantFacturas   = count(array_unique(array_column($facturas, 'factura_id')));

        return [
            'total_comision'      => round((float) $totalComision, 2),
            'cantidad_empleados'  => $cantEmpleados,
            'cantidad_facturas'   => $cantFacturas,
            'detalle_empleados'   => array_map(fn($e) => (array) $e, $empleados),
            'detalle_facturas'    => array_map(fn($f) => (array) $f, $facturas),
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

