<div>
<style>
.cfg-flujo-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2c3e50 100%);
    border-radius: 16px; padding: 22px 28px; margin-bottom: 0;
    color: #fff; display: flex; align-items: center; gap: 16px;
    box-shadow: 0 8px 28px rgba(30,58,95,.25);
}
.cfg-flujo-hero-icon {
    width: 50px; height: 50px; border-radius: 12px;
    background: rgba(255,255,255,.13); border: 2px solid rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;
}
.cfg-flujo-hero h3 { margin: 0; font-size: 17px; font-weight: 800; }
.cfg-flujo-hero p  { margin: 3px 0 0; font-size: 12px; color: rgba(255,255,255,.65); }

.cfg-flujo-card {
    background: #fff;
    border-radius: 0 0 14px 14px;
    border: 1px solid #e2e8f0; border-top: none;
    box-shadow: 0 6px 20px rgba(0,0,0,.06);
    overflow: hidden;
}

.cfg-flujo-item {
    display: flex; align-items: center; gap: 20px;
    padding: 22px 26px; border-bottom: 1px solid #f1f5f9;
}
.cfg-flujo-item:last-child { border-bottom: none; }

.cfg-flujo-item-info { flex: 1; }
.cfg-flujo-item-info h5 {
    margin: 0 0 5px; font-size: 14px; font-weight: 800; color: #1e293b;
    display: flex; align-items: center; gap: 8px;
}
.cfg-flujo-item-info p { margin: 0; font-size: 12.5px; color: #64748b; }

.cfg-flujo-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 11px; border-radius: 20px; font-size: 11px; font-weight: 800;
}
.cfg-flujo-badge.activo   { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.cfg-flujo-badge.inactivo { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

.cfg-flujo-toggle {
    border: none !important; border-radius: 10px !important;
    font-size: 13px !important; font-weight: 700 !important;
    padding: 9px 22px !important; cursor: pointer !important;
    display: inline-flex !important; align-items: center !important; gap: 7px !important;
    min-width: 140px !important; justify-content: center !important;
    transition: opacity .15s, transform .1s !important;
    box-shadow: 0 4px 12px rgba(0,0,0,.15) !important;
    flex-shrink: 0;
}
.cfg-flujo-toggle:hover { opacity: .88 !important; transform: translateY(-1px) !important; }
.cfg-flujo-toggle.activar    { background: linear-gradient(135deg,#16a34a,#22c55e) !important; color:#fff !important; }
.cfg-flujo-toggle.desactivar { background: linear-gradient(135deg,#dc2626,#ef4444) !important; color:#fff !important; }

.cfg-flujo-flow {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12px; font-weight: 600; color: #374151;
    margin-top: 6px; padding: 4px 12px; border-radius: 20px;
    background: #f8fafc; border: 1px solid #e2e8f0;
}
.cfg-flujo-flow i { font-size: 10px; color: #94a3b8; }
</style>

{{-- PAGE HEADER --}}
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-12">
        <h2><i class="fa fa-cogs text-primary mr-2"></i>Configuración de Flujo</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
            <li class="breadcrumb-item">Flujo de Venta</li>
            <li class="breadcrumb-item active"><strong>Configuración de Flujo</strong></li>
        </ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-8 col-md-10 col-sm-12 mx-auto">

            {{-- Alertas --}}
            @if ($mensajeExito)
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="fa fa-check-circle mr-1"></i> {{ $mensajeExito }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            @endif
            @if ($mensajeError)
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="fa fa-exclamation-triangle mr-1"></i> {{ $mensajeError }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            @endif

            {{-- Hero --}}
            <div class="cfg-flujo-hero">
                <div class="cfg-flujo-hero-icon"><i class="fa fa-sitemap"></i></div>
                <div>
                    <h3>Configuración de Flujo de Venta</h3>
                    <p>Ajusta los pasos del flujo de ventas. Solo visible para administradores.</p>
                </div>
            </div>

            {{-- Card de configuraciones --}}
            <div class="cfg-flujo-card">

                {{-- ── Revisión de Inventario ── --}}
                <div class="cfg-flujo-item">
                    <div class="cfg-flujo-item-info">
                        <h5>
                            <i class="fa fa-cubes text-{{ $revisionInventarioActiva ? 'success' : 'secondary' }}"></i>
                            Revisión de Inventario
                            <span class="cfg-flujo-badge {{ $revisionInventarioActiva ? 'activo' : 'inactivo' }}">
                                {{ $revisionInventarioActiva ? 'ACTIVO' : 'INACTIVO' }}
                            </span>
                        </h5>
                        <p>Habilita un paso de verificación de stock entre la oferta ganadora y la prefactura.</p>
                        <div class="cfg-flujo-flow">
                            <span>Oferta Ganadora</span>
                            <i class="fa fa-arrow-right"></i>
                            @if ($revisionInventarioActiva)
                                <strong style="color:#16a34a;">Revisión de Inventario</strong>
                                <i class="fa fa-arrow-right"></i>
                            @endif
                            <span>Prefactura</span>
                        </div>
                    </div>
                    <button type="button"
                            wire:click="toggleRevisionInventario"
                            wire:loading.attr="disabled"
                            class="cfg-flujo-toggle {{ $revisionInventarioActiva ? 'desactivar' : 'activar' }}">
                        <span wire:loading.remove wire:target="toggleRevisionInventario">
                            <i class="fa fa-{{ $revisionInventarioActiva ? 'toggle-off' : 'toggle-on' }}"></i>
                            {{ $revisionInventarioActiva ? 'Desactivar' : 'Activar' }}
                        </span>
                        <span wire:loading wire:target="toggleRevisionInventario">
                            <i class="fa fa-spinner fa-spin"></i> Guardando...
                        </span>
                    </button>
                </div>

                {{-- Aquí se pueden agregar más configuraciones de flujo en el futuro --}}

            </div>
            {{-- /card --}}

        </div>
    </div>
</div>

</div>
