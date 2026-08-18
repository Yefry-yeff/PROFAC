<?php

namespace App\Http\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EvaluacionClientesExport;

class EvaluacionDeClientesPorNivelDeFacturacion extends Component
{
    public $titulo = 'Evaluación de Clientes por Nivel de Facturación';

    // ── Filtros ──────────────────────────────────────────────────────────────
    public $filtCodigo       = '';
    public $filtNombre       = '';
    public $filtEstado       = '';
    public $filtVendedor     = '';
    public $filtTeleasesor   = '';
    public $filtRequiereAt   = '';
    public $filtFechaDesde   = '';
    public $filtFechaHasta   = '';
    public $filtSinHistorial = '';   // 'sin' | 'con' | ''

    // ── Paginación ───────────────────────────────────────────────────────────
    public $paginaActual = 1;
    public $porPagina    = 5;

    // Reset page to 1 whenever any filter changes
    public function updatedFiltCodigo()       { $this->paginaActual = 1; }
    public function updatedFiltNombre()       { $this->paginaActual = 1; }
    public function updatedFiltEstado()       { $this->paginaActual = 1; }
    public function updatedFiltVendedor()     { $this->paginaActual = 1; }
    public function updatedFiltTeleasesor()   { $this->paginaActual = 1; }
    public function updatedFiltRequiereAt()   { $this->paginaActual = 1; }
    public function updatedFiltFechaDesde()   { $this->paginaActual = 1; }
    public function updatedFiltFechaHasta()   { $this->paginaActual = 1; }
    public function updatedFiltSinHistorial() { $this->paginaActual = 1; }
    public function updatedPorPagina()        { $this->paginaActual = 1; }

    public function paginaAnterior()
    {
        if ($this->paginaActual > 1) {
            $this->paginaActual--;
        }
    }

    public function paginaSiguiente($totalPaginas)
    {
        if ($this->paginaActual < $totalPaginas) {
            $this->paginaActual++;
        }
    }

    public function limpiarFiltros()
    {
        $this->filtCodigo       = '';
        $this->filtNombre       = '';
        $this->filtEstado       = '';
        $this->filtVendedor     = '';
        $this->filtTeleasesor   = '';
        $this->filtRequiereAt   = '';
        $this->filtFechaDesde   = '';
        $this->filtFechaHasta   = '';
        $this->filtSinHistorial = '';
        $this->paginaActual     = 1;
    }

    // ── Query central ────────────────────────────────────────────────────────
    private function getDatos(): array
    {
        $where  = '1=1';
        $params = [];

        if ($this->filtCodigo !== '') {
            $where   .= ' AND c.id = ?';
            $params[] = (int) $this->filtCodigo;
        }
        if ($this->filtNombre !== '') {
            $where   .= ' AND c.nombre LIKE ?';
            $params[] = '%' . $this->filtNombre . '%';
        }
        if ($this->filtEstado !== '') {
            $where   .= ' AND ec.descripcion = ?';
            $params[] = $this->filtEstado;
        }
        if ($this->filtVendedor === 'sin_asignar') {
            $where .= ' AND asesor.id IS NULL';
        } elseif ($this->filtVendedor !== '') {
            $where   .= ' AND c.vendedor = ?';
            $params[] = (int) $this->filtVendedor;
        }
        if ($this->filtTeleasesor === 'sin_asignar') {
            $where .= ' AND NOT EXISTS (
                SELECT 1
                FROM cliente_usuario cu_filtro
                WHERE cu_filtro.cliente_id = c.id
                  AND cu_filtro.rol_id = 3
            )';
        } elseif ($this->filtTeleasesor !== '') {
            $where .= ' AND EXISTS (
                SELECT 1
                FROM cliente_usuario cu_filtro
                WHERE cu_filtro.cliente_id = c.id
                  AND cu_filtro.usuario_id = ?
                  AND cu_filtro.rol_id = 3
            )';
            $params[] = (int) $this->filtTeleasesor;
        }
        if ($this->filtFechaDesde !== '') {
            $where   .= ' AND uf.fecha_emision >= ?';
            $params[] = $this->filtFechaDesde;
        }
        if ($this->filtFechaHasta !== '') {
            $where   .= ' AND uf.fecha_emision <= ?';
            $params[] = $this->filtFechaHasta;
        }
        if ($this->filtSinHistorial === 'sin') {
            $where .= ' AND uf.fecha_emision IS NULL';
        } elseif ($this->filtSinHistorial === 'con') {
            $where .= ' AND uf.fecha_emision IS NOT NULL';
        }

        $sql = "
            SELECT
                c.id                                                                             AS codigo_cliente,
                c.nombre                                                                         AS nombre_cliente,
                COALESCE(c.correo, '')                                                           AS correo,
                COALESCE(c.telefono_empresa, '')                                                  AS telefono,
                COALESCE(c.direccion, '')                                                         AS direccion,
                COALESCE(ec.descripcion, 'Sin Estado')                                           AS estado,
                COALESCE(asesor.name, 'Sin Asignar')                                             AS vendedor,
                COALESCE(tele.teleasesores, 'Sin Asignar')                                       AS teleasesores,
                uf.cai                                                                           AS numero_ultima_factura,
                uf.fecha_emision                                                                  AS fecha_ultima_factura,
                COALESCE(uf.total, 0)                                                            AS monto_ultima_factura,
                COALESCE(saldos.saldo_pendiente, 0)                                              AS saldo_pendiente,
                CASE
                    WHEN uf.fecha_emision IS NULL                              THEN 'Sí'
                    WHEN uf.fecha_emision < DATE_SUB(NOW(), INTERVAL 3 MONTH) THEN 'Sí'
                    ELSE 'No'
                END                                                                              AS requiere_atencion
            FROM cliente c
            LEFT JOIN estado_cliente ec ON ec.id = c.estado_cliente_id
            LEFT JOIN users asesor ON asesor.id = c.vendedor
            LEFT JOIN (
                SELECT
                    cu.cliente_id,
                    GROUP_CONCAT(DISTINCT u.name ORDER BY u.name SEPARATOR ', ') AS teleasesores
                FROM cliente_usuario cu
                INNER JOIN users u ON u.id = cu.usuario_id
                WHERE cu.rol_id = 3
                GROUP BY cu.cliente_id
            ) tele ON tele.cliente_id = c.id
            LEFT JOIN (
                SELECT cliente_id, SUM(saldo) AS saldo_pendiente
                FROM aplicacion_pagos
                WHERE estado = 1
                GROUP BY cliente_id
            ) saldos ON saldos.cliente_id = c.id
            LEFT JOIN factura uf
                ON uf.id = (
                    SELECT f2.id
                    FROM factura f2
                    WHERE f2.cliente_id = c.id
                    ORDER BY f2.fecha_emision DESC, f2.id DESC
                    LIMIT 1
                )
            WHERE {$where}
            ORDER BY
                CASE
                    WHEN uf.fecha_emision IS NULL                              THEN 0
                    WHEN uf.fecha_emision < DATE_SUB(NOW(), INTERVAL 3 MONTH) THEN 0
                    ELSE 1
                END ASC,
                uf.fecha_emision ASC
        ";

        $datos = DB::select($sql, $params);

        // filtro de columna calculada aplicado en PHP
        if ($this->filtRequiereAt !== '') {
            $filtro = $this->filtRequiereAt;
            $datos  = array_values(
                array_filter($datos, fn ($r) => $r->requiere_atencion === $filtro)
            );
        }

        return $datos;
    }

    // ── Exportar Excel ───────────────────────────────────────────────────────
    public function exportarExcel()
    {
        $datos   = $this->getDatos();
        $usuario = Auth::user()->name ?? 'Sistema';
        return Excel::download(
            new EvaluacionClientesExport($datos, $usuario),
            'evaluacion_clientes_' . now()->format('Y-m-d_H-i') . '.xlsx'
        );
    }

    // ── Render ───────────────────────────────────────────────────────────────
    public function render()
    {
        $datos = $this->getDatos();
        $total = count($datos);

        // Paginación
        $totalPaginas = max(1, (int) ceil($total / $this->porPagina));
        if ($this->paginaActual > $totalPaginas) {
            $this->paginaActual = $totalPaginas;
        }
        $offset      = ($this->paginaActual - 1) * $this->porPagina;
        $datosPagina = array_slice($datos, $offset, (int) $this->porPagina);

        // ── Chart 1: Requieren atención ──────────────────────────────────────
        $atSi = count(array_filter($datos, fn ($r) => $r->requiere_atencion === 'Sí'));
        $atNo = $total - $atSi;

        // ── Chart 2: Distribución por estado ─────────────────────────────────
        $estadosCounts = [];
        foreach ($datos as $r) {
            $k = $r->estado;
            $estadosCounts[$k] = ($estadosCounts[$k] ?? 0) + 1;
        }

        // ── Chart 3: Distribución por asesor comercial ───────────────────────
        $vendedoresCounts = [];
        foreach ($datos as $r) {
            $k = $r->vendedor;
            $vendedoresCounts[$k] = ($vendedoresCounts[$k] ?? 0) + 1;
        }

        // ── Chart 4: Distribución por teleasesor ─────────────────────────────
        $teleasesoresCounts = [];
        $clienteIds = array_map(fn ($r) => $r->codigo_cliente, $datos);
        if ($clienteIds) {
            $conteosTeleasesores = DB::table('cliente_usuario as cu')
                ->join('users as u', 'u.id', '=', 'cu.usuario_id')
                ->where('cu.rol_id', 3)
                ->whereIn('cu.cliente_id', $clienteIds)
                ->groupBy('u.id', 'u.name')
                ->orderBy('u.name')
                ->select('u.name', DB::raw('COUNT(DISTINCT cu.cliente_id) AS total'))
                ->get();

            foreach ($conteosTeleasesores as $conteo) {
                $teleasesoresCounts[$conteo->name] = (int) $conteo->total;
            }
        }
        $sinTeleasesor = count(array_filter($datos, fn ($r) => $r->teleasesores === 'Sin Asignar'));
        if ($sinTeleasesor > 0) {
            $teleasesoresCounts['Sin Asignar'] = $sinTeleasesor;
        }

        // ── Chart 5: Con facturación vs Sin historial ────────────────────────
        $conFact = count(array_filter($datos, fn ($r) => $r->fecha_ultima_factura !== null));
        $sinHist = $total - $conFact;

        // ── Chart 6: Top asesores con más clientes sin atención ──────────────
        $vendAtCounts = [];
        foreach ($datos as $r) {
            if ($r->requiere_atencion === 'Sí') {
                $k = $r->vendedor;
                $vendAtCounts[$k] = ($vendAtCounts[$k] ?? 0) + 1;
            }
        }
        arsort($vendAtCounts);
        $vendAtCounts = array_slice($vendAtCounts, 0, 10, true);

        $chartData = [
            'atencion'      => [
                'series' => [$atSi, $atNo],
                'labels' => ['Requieren Atención', 'No Requieren Atención'],
            ],
            'estados'       => [
                'series' => array_values($estadosCounts),
                'labels' => array_keys($estadosCounts),
            ],
            'vendedores'    => [
                'series' => array_values($vendedoresCounts),
                'labels' => array_keys($vendedoresCounts),
            ],
            'teleasesores'  => [
                'series' => array_values($teleasesoresCounts),
                'labels' => array_keys($teleasesoresCounts),
            ],
            'historial'     => [
                'series' => [$conFact, $sinHist],
                'labels' => ['Con Facturación', 'Sin Historial'],
            ],
            'topVendedoresAt' => [
                'series' => array_values($vendAtCounts),
                'labels' => array_keys($vendAtCounts),
            ],
        ];

        // Selects para filtros
        $estados = DB::select('SELECT DISTINCT descripcion FROM estado_cliente ORDER BY descripcion ASC');
        $vendedores = DB::select('
            SELECT DISTINCT u.id, u.name
            FROM cliente c
            INNER JOIN users u ON u.id = c.vendedor
            ORDER BY u.name ASC
        ');
        $teleasesores = DB::select('
            SELECT DISTINCT u.id, u.name
            FROM cliente_usuario cu
            INNER JOIN users u ON u.id = cu.usuario_id
            WHERE cu.rol_id = 3
            ORDER BY u.name ASC
        ');

        return view('livewire.reportes.evaluaciondeclientesporniveldefacturacion', [
            'datosPagina'  => $datosPagina,
            'total'        => $total,
            'totalPaginas' => $totalPaginas,
            'chartData'    => $chartData,
            'estados'      => $estados,
            'vendedores'   => $vendedores,
            'teleasesores' => $teleasesores,
        ]);
    }

    // ── Filtrar desde gráfica (llamado vía JS) ───────────────────────────────
    public function filtrarPorGrafica(string $tipo, string $valor): void
    {
        $this->paginaActual = 1;
        switch ($tipo) {
            case 'atencion':
                $mapa  = ['Requieren Atención' => 'Sí', 'No Requieren Atención' => 'No'];
                $nuevo = $mapa[$valor] ?? '';
                $this->filtRequiereAt = ($this->filtRequiereAt === $nuevo) ? '' : $nuevo;
                break;
            case 'estado':
                $this->filtEstado = ($this->filtEstado === $valor) ? '' : $valor;
                break;
            case 'vendedor':
                $id = 'sin_asignar';
                if ($valor !== 'Sin Asignar') {
                    $rows = DB::select(
                        'SELECT DISTINCT u.id
                         FROM cliente c
                         INNER JOIN users u ON u.id = c.vendedor
                         WHERE u.name = ?
                         LIMIT 1',
                        [$valor]
                    );
                    $id = $rows ? (string) $rows[0]->id : '';
                }
                $this->filtVendedor = ($this->filtVendedor === $id) ? '' : $id;
                break;
            case 'teleasesor':
                $id = 'sin_asignar';
                if ($valor !== 'Sin Asignar') {
                    $rows = DB::select(
                        'SELECT DISTINCT u.id
                         FROM cliente_usuario cu
                         INNER JOIN users u ON u.id = cu.usuario_id
                         WHERE cu.rol_id = 3 AND u.name = ?
                         LIMIT 1',
                        [$valor]
                    );
                    $id = $rows ? (string) $rows[0]->id : '';
                }
                $this->filtTeleasesor = ($this->filtTeleasesor === $id) ? '' : $id;
                break;
            case 'historial':
                $mapa  = ['Con Facturación' => 'con', 'Sin Historial' => 'sin'];
                $nuevo = $mapa[$valor] ?? '';
                $this->filtSinHistorial = ($this->filtSinHistorial === $nuevo) ? '' : $nuevo;
                break;
        }
    }

}

