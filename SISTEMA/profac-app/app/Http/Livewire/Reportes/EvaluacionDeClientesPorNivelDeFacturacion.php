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
        if ($this->filtVendedor !== '') {
            $where   .= ' AND c.vendedor = ?';
            $params[] = (int) $this->filtVendedor;
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
                COALESCE(ec.descripcion, 'Sin Estado')                                           AS estado,
                COALESCE((SELECT name FROM users WHERE id = c.vendedor LIMIT 1), 'Sin Vendedor') AS vendedor,
                uf.cai                                                                           AS numero_ultima_factura,
                uf.fecha_emision                                                                  AS fecha_ultima_factura,
                COALESCE(uf.total, 0)                                                            AS monto_ultima_factura,
                COALESCE(
                    (SELECT SUM(ap2.saldo)
                     FROM aplicacion_pagos ap2
                     WHERE ap2.cliente_id = c.id
                       AND ap2.estado = 1),
                    0
                )                                                                                AS saldo_pendiente,
                CASE
                    WHEN uf.fecha_emision IS NULL                              THEN 'Sí'
                    WHEN uf.fecha_emision < DATE_SUB(NOW(), INTERVAL 3 MONTH) THEN 'Sí'
                    ELSE 'No'
                END                                                                              AS requiere_atencion
            FROM cliente c
            LEFT JOIN estado_cliente ec ON ec.id = c.estado_cliente_id
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

        // ── Chart 3: Distribución por vendedor ────────────────────────────────
        $vendedoresCounts = [];
        foreach ($datos as $r) {
            $k = $r->vendedor;
            $vendedoresCounts[$k] = ($vendedoresCounts[$k] ?? 0) + 1;
        }

        // ── Chart 4: Con facturación vs Sin historial ─────────────────────────
        $conFact = count(array_filter($datos, fn ($r) => $r->fecha_ultima_factura !== null));
        $sinHist = $total - $conFact;

        // ── Chart 5: Top vendedores con más clientes sin atención ─────────────
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
        $estados    = DB::select('SELECT DISTINCT descripcion FROM estado_cliente ORDER BY descripcion ASC');
        $vendedores = DB::select('SELECT id, name FROM users WHERE rol_id = 2 ORDER BY name ASC');

        return view('livewire.reportes.evaluaciondeclientesporniveldefacturacion', [
            'datosPagina'  => $datosPagina,
            'total'        => $total,
            'totalPaginas' => $totalPaginas,
            'chartData'    => $chartData,
            'estados'      => $estados,
            'vendedores'   => $vendedores,
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
                $rows = DB::select(
                    'SELECT id FROM users WHERE name = ? AND rol_id = 2 LIMIT 1',
                    [$valor]
                );
                $id = $rows ? (string) $rows[0]->id : '';
                $this->filtVendedor = ($this->filtVendedor === $id) ? '' : $id;
                break;
            case 'historial':
                $mapa  = ['Con Facturación' => 'con', 'Sin Historial' => 'sin'];
                $nuevo = $mapa[$valor] ?? '';
                $this->filtSinHistorial = ($this->filtSinHistorial === $nuevo) ? '' : $nuevo;
                break;
        }
    }

}

