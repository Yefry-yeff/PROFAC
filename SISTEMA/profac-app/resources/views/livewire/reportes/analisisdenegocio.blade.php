<div class="an-hub" x-data="analisisNegocio(@js($modulos), @js($moduloInicial))">
    <style>
        .an-hub .an-heading { background:linear-gradient(120deg,#f39c12,#e67e22); color:white; padding:22px; border-radius:12px; margin-bottom:16px; }
        .an-hub .an-selector { background:white; padding:20px; border:1px solid #e1e7ef; border-radius:12px; margin-bottom:16px; }
        .an-hub .an-frame { width:100%; height:calc(100vh - 180px); min-height:650px; border:1px solid #e1e7ef; border-radius:12px; background:white; display:block; }
        .an-hub [x-cloak],.an-module-loading[x-cloak] { display:none !important; }
        .an-module-loading { position:fixed; inset:0; z-index:2200; display:flex; align-items:center; justify-content:center; background:#ffffffc9; }
        .an-module-loading .an-card { padding:28px; background:white; border-radius:12px; text-align:center; box-shadow:0 6px 30px #172e5026; color:#26354b; max-width:calc(100vw - 32px); }
        .an-module-loading .an-spinner { display:block; width:48px; height:48px; margin:0 auto 16px; border:5px solid #fce5c5; border-top-color:#e67e22; border-radius:50%; animation:an-spin .8s linear infinite; }
        @keyframes an-spin { to { transform:rotate(360deg); } }
    </style>
    <div class="an-heading">
        <h2 class="font-weight-bold mb-1">Análisis de negocio</h2>
        <span>Selecciona el área que deseas analizar sin salir de esta vista.</span>
    </div>
    <div class="an-selector">
        <label for="an-dashboard" class="font-weight-bold">Dashboard</label>
        <select id="an-dashboard" class="form-control" x-model="seleccion" @change="seleccionar()">
            <option value="">Selecciona un dashboard</option>
            @foreach($modulos as $modulo)
                <option value="{{ $modulo['id'] }}" @disabled(!$modulo['permitido'])>{{ $modulo['nombre'] }}{{ $modulo['permitido'] ? '' : ' (sin acceso)' }}</option>
            @endforeach
        </select>
        <small class="text-muted d-block mt-2">Cada módulo conserva sus filtros, gráficos y descargas. Al cambiar de módulo se inicia una nueva consulta. El rol Administrador tiene acceso a todos estos reportes; los demás roles conservan sus permisos asignados.</small>
        <h3 class="mt-3 mb-0" x-show="seleccion" x-text="modulo?.titulo" x-cloak></h3>
    </div>
    <div x-show="error" class="alert alert-danger" role="alert" x-cloak>
        <span x-text="error"></span>
        <button type="button" class="btn btn-sm btn-outline-danger ml-2" @click="seleccionar()">Reintentar</button>
    </div>
    <div x-show="!seleccion" class="an-selector text-center text-muted">Elige un área para visualizar su dashboard.</div>
    <div x-ref="contenedor" wire:ignore></div>
    <template x-teleport="body">
        <div class="an-module-loading" x-show="cargando" x-cloak role="status" aria-live="polite">
            <div class="an-card">
                <span class="an-spinner" aria-hidden="true"></span>
                <strong class="d-block">Cargando <span x-text="modulo?.nombre"></span>…</strong>
                <small class="d-block mt-2">Preparando el módulo y consultando su información.</small>
            </div>
        </div>
    </template>
    @push('scripts')
        <script src="{{ asset('js/js_proyecto/reportes/analisis-negocio.js') }}"></script>
    @endpush
</div>
