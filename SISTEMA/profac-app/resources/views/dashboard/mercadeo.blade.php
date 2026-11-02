{{-- ═══════════════════════════════════════════════════════════════
     DASHBOARD — GERENTE DE MARCA / MERCADEO
     Rol: 9 — Enfoque en inventario
═══════════════════════════════════════════════════════════════ --}}

@include('dashboard.inventario-kpis', ['kpisInventario' => $kpisInventario])

{{-- ── Grid: Distribución stock + Cobertura imágenes ── --}}
<div class="dash-grid-2" style="margin-bottom:20px;">

    <div class="dash-card">
        <div class="dash-card-header orange">
            <h5><i class="fa fa-bar-chart"></i> Estado del Stock — Top Críticos</h5>
        </div>
        <div class="dash-card-body">
            <div id="chart-mkt-stock" wire:ignore style="min-height:270px;"></div>
        </div>
    </div>

    <div class="dash-card">
        <div class="dash-card-header blue">
            <h5><i class="fa fa-pie-chart"></i> Cobertura de Imágenes</h5>
        </div>
        <div class="dash-card-body">
            <div id="chart-mkt-imagenes" wire:ignore style="min-height:270px;"></div>
        </div>
    </div>

</div>
