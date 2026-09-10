<div>
    @push('styles')
    <style>
        .expo-panel {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,.07);
            border: 1px solid #e8eaef;
        }
        .expo-panel .ibox-title {
            min-height: auto;
            padding: 14px 20px;
            border: 0;
            background: linear-gradient(135deg,#e65100 0%,#f9a826 100%);
        }
        .expo-panel .ibox-title h5 {
            color: #fff;
            margin: 0;
            font-size: 15px;
            font-weight: 700;
        }
        .expo-panel .ibox-title small {
            display: block;
            margin-top: 3px;
            color: rgba(255,255,255,.84);
            font-size: 11px;
        }
        .expo-panel .ibox-content { padding: 20px 22px; }
        .expo-section {
            background: #fafbfc;
            border: 1px solid #e8eaef;
            border-radius: 10px;
            padding: 16px 18px 4px;
            margin-bottom: 16px;
        }
        .expo-section-title {
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0 0 14px;
            padding-bottom: 9px;
            border-bottom: 2px solid #edf0f4;
            color: #546e7a;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .expo-section-title i { color: #e65100; font-size: 13px; }
        .expo-panel label {
            display: block;
            margin-bottom: 4px;
            color: #4a5568;
            font-size: 12px;
            font-weight: 700;
        }
        .expo-panel .form-control {
            min-height: 36px;
            border: 1.5px solid #dde2ec;
            border-radius: 8px;
            background: #fff;
            color: #2d3748;
            font-size: 13px;
            transition: border .15s, box-shadow .15s;
        }
        .expo-panel .form-control:focus {
            border-color: #e65100;
            box-shadow: 0 0 0 3px rgba(230,81,0,.11);
        }
        .expo-panel .form-control[readonly] {
            background: #eef1f5;
            color: #607d8b;
            cursor: not-allowed;
        }
        .expo-locked-hint { color: #78909c; font-size: 10px; margin-top: 4px; }
        .expo-panel select.form-control:not([multiple]) {
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 20px 20px;
            padding-right: 36px;
        }
        .expo-checklist {
            max-height: 230px;
            overflow-y: auto;
            border: 1.5px solid #dde2ec;
            border-radius: 8px;
            background: #fff;
        }
        .expo-check-all,
        .expo-check-item label {
            display: flex;
            align-items: center;
            gap: 9px;
            width: 100%;
            margin: 0;
            padding: 8px 11px;
            cursor: pointer;
            font-size: 12px;
        }
        .expo-check-all {
            position: sticky;
            top: 0;
            z-index: 2;
            border-bottom: 1px solid #f0d4bd;
            background: #fff5eb;
            color: #bf4b00;
            font-weight: 800;
        }
        .expo-check-item { border-bottom: 1px solid #f0f3f6; }
        .expo-check-item:last-child { border-bottom: 0; }
        .expo-check-item:hover { background: #fffbf7; }
        .expo-checklist input[type="checkbox"] {
            flex: 0 0 auto;
            width: 15px;
            height: 15px;
            margin: 0;
            accent-color: #e65100;
        }
        .expo-check-summary { color: #78909c; font-size: 10px; font-weight: 700; }
        .expo-hint { color: #90a4ae; font-size: 11px; margin-top: 5px; }
        .expo-user-search { position: relative; }
        .expo-user-results {
            position: absolute;
            z-index: 20;
            top: calc(100% + 3px);
            left: 0;
            right: 0;
            max-height: 210px;
            overflow-y: auto;
            border: 1px solid #d9dee7;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 8px 20px rgba(44,62,80,.16);
        }
        button.expo-user-result {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 9px 12px;
            border: 0;
            border-bottom: 1px solid #f0f3f6;
            background: #fff !important;
            color: #37474f !important;
            text-align: left;
        }
        button.expo-user-result:hover { background: #fff5eb !important; }
        .expo-user-result i { color: #e65100; }
        .expo-user-result span { min-width: 0; }
        .expo-user-result strong,
        .expo-user-result small { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .expo-user-result small { color: #90a4ae; }
        .expo-users-table { margin: 0; }
        .expo-users-table th {
            padding: 8px 10px;
            background: #f8fafc;
            color: #64748b;
            font-size: 10px;
            text-transform: uppercase;
        }
        .expo-users-table td { padding: 8px 10px; vertical-align: middle; font-size: 12px; }
        .expo-version-note {
            background: #e3f2fd;
            border: 1px solid #90caf9;
            border-left: 4px solid #1a73e8;
            border-radius: 8px;
            padding: 10px 14px;
            color: #1565c0;
            font-size: 12px;
            margin-bottom: 16px;
        }
        .expo-discount-wrap {
            border: 1px solid #e8eaef;
            border-radius: 9px;
            overflow: hidden;
            background: #fff;
        }
        .expo-discount-table { margin: 0; }
        .expo-brand-search { width: min(300px, 100%); }
        .expo-brand-manager { display:grid; grid-template-columns:minmax(250px,1fr) auto; align-items:end; gap:10px; width:100%; }
        .expo-brand-manager label { margin-bottom:4px; color:#7d3f00; font-size:10px; font-weight:800; text-transform:uppercase; }
        .expo-brand-manager select { min-height:36px!important; border-color:#e0cbb0!important; }
        .expo-brand-manager-actions { display:flex; justify-content:flex-end; gap:7px; flex-wrap:wrap; }
        .expo-brand-manager-actions .btn { min-height:34px; border-radius:7px; font-size:10px; font-weight:700; white-space:nowrap; }
        .expo-brand-table-scroll {
            max-height: 292px;
            overflow-y: auto;
            overflow-x: auto;
        }
        .expo-brand-table-scroll .expo-discount-table { min-width: 620px; }
        .expo-brand-table-scroll thead th {
            position: sticky;
            top: 0;
            z-index: 2;
        }
        .expo-brand-table-scroll tbody tr { height: 43px; }
        .expo-sort-button {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 0;
            border: 0;
            background: transparent;
            color: inherit;
            font: inherit;
            text-transform: inherit;
        }
        .expo-sort-button i { color: #a0aec0; font-size: 10px; }
        .expo-sort-button.active i { color: #e65100; }
        .expo-discount-table thead th,
        .expo-history-table thead th {
            padding: 9px 12px;
            border-bottom: 2px solid #e8edf5;
            background: #f8fafc;
            color: #64748b;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .45px;
            white-space: nowrap;
        }
        .expo-discount-table tbody td { padding: 7px 10px; vertical-align: top; border-color: #f0f3f6; }
        .expo-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding-top: 4px;
        }
        .expo-save-btn {
            border: 0 !important;
            border-radius: 8px !important;
            padding: 9px 20px !important;
            background: linear-gradient(135deg,#e65100,#f9a826) !important;
            box-shadow: 0 3px 10px rgba(230,81,0,.25);
            color: #fff !important;
            font-weight: 700;
        }
        .expo-history-table { margin-bottom: 0; }
        .expo-history-table tbody td {
            padding: 10px 12px;
            border-color: #f0f3f6;
            vertical-align: middle;
            color: #455a64;
            font-size: 12px;
        }
        .expo-history-table tbody tr:hover { background: #fffbf7; }
        .expo-history-table tbody tr.expo-clickable { cursor: pointer; }
        .expo-name { color: #2c3e50; font-size: 13px; font-weight: 800; }
        .expo-version { color: #90a4ae; font-size: 10px; }
        .expo-state {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border-radius: 20px;
            padding: 3px 9px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .expo-state-active { background: #e8f5e9; color: #2e7d32; }
        .expo-state-inactive { background: #eceff1; color: #607d8b; }
        .expo-config-counts { display: flex; flex-wrap: wrap; gap: 4px; }
        .expo-row-actions { display:flex; justify-content:center; }
        .expo-actions-toggle { display:inline-flex; align-items:center; gap:6px; padding:4px 8px; border:1px solid #d8e0e7; border-radius:5px; background:#fff; color:#455a64; font-size:10px; font-weight:700; cursor:pointer; }
        .expo-actions-popover { position:fixed; inset:auto; width:225px; margin:0; padding:5px; border:1px solid #dce3e8; border-radius:6px; background:#fff; box-shadow:0 10px 24px rgba(35,52,65,.18); }
        .expo-actions-popover::backdrop { background:transparent; }
        .expo-actions-popover .dropdown-item { display:flex; align-items:center; gap:9px; width:100%; padding:7px 9px; border:0; border-radius:4px; background:transparent; color:#40515b; font-size:11px; text-align:left; text-decoration:none; }
        .expo-actions-popover .dropdown-item:hover { background:#f3f6f8; color:#20323d; }
        .expo-actions-popover .dropdown-item i { width:14px; color:#607d8b; text-align:center; }
        .expo-actions-popover .dropdown-item.report i { color:#1976d2; }
        .expo-actions-popover .dropdown-item.close-expo i { color:#c62828; }
        .expo-actions-popover .dropdown-divider { margin:4px 0; }
        .expo-config-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border-radius: 6px;
            padding: 3px 7px;
            background: #f1f5f9;
            color: #546e7a;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }
        .expo-empty { padding: 38px 20px; text-align: center; }
        .expo-empty i { display: block; margin-bottom: 10px; color: #cfd8dc; font-size: 36px; }
        .expo-empty strong { display: block; color: #546e7a; font-size: 13px; }
        .expo-empty span { color: #90a4ae; font-size: 11px; }
        .expo-new-btn {
            border: 1px solid rgba(255,255,255,.55) !important;
            border-radius: 7px !important;
            background: rgba(255,255,255,.18) !important;
            color: #fff !important;
            font-size: 11px !important;
            font-weight: 700 !important;
        }
        .expo-detail-backdrop { position:fixed; inset:0; z-index:10050; display:flex; align-items:center; justify-content:center; padding:20px; background:rgba(27,39,51,.55); }
        .wrapper.wrapper-content.animated.fadeInRight:has(.expo-detail-backdrop) {
            transform: none !important;
            animation: none !important;
        }
        .expo-detail-modal { width:min(900px, 100%); max-height:calc(100vh - 40px); overflow-y:auto; border-radius:10px; background:#fff; box-shadow:0 20px 55px rgba(0,0,0,.28); }
        .expo-detail-head { display:flex; align-items:flex-start; justify-content:space-between; gap:14px; padding:17px 20px; background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; }
        .expo-detail-head h4 { margin:0; color:#fff; font-size:17px; font-weight:800; }
        .expo-detail-head small { color:rgba(255,255,255,.85); }
        .expo-detail-close { border:0; background:transparent!important; color:#fff!important; font-size:22px; line-height:1; }
        .expo-detail-body { padding:20px; }
        .expo-detail-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px 20px; }
        .expo-detail-field label { margin:0 0 2px; color:#90a4ae; font-size:10px; font-weight:800; text-transform:uppercase; }
        .expo-detail-field div { color:#37474f; font-size:13px; }
        .expo-detail-section { margin-top:18px; padding-top:14px; border-top:1px solid #edf0f4; }
        .expo-detail-section h6 { margin:0 0 9px; color:#546e7a; font-size:11px; font-weight:800; text-transform:uppercase; }
        .expo-detail-tags { display:flex; flex-wrap:wrap; gap:6px; }
        .expo-detail-tag { padding:5px 9px; border-radius:6px; background:#f1f5f9; color:#455a64; font-size:11px; }
        .expo-detail-users { width:100%; margin:0; }
        .expo-detail-users td { padding:6px 8px; border-top:1px solid #f0f3f6; font-size:12px; }
        .expo-close-action { color:#c62828!important; }
        .expo-close-modal { width:min(1180px,100%); max-height:calc(100vh - 32px); overflow:hidden; border-radius:8px; background:#fff; box-shadow:0 20px 55px rgba(0,0,0,.28); }
        .expo-close-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:16px 20px; background:#8e2f2f; color:#fff; }
        .expo-close-head h4 { margin:0 0 3px; color:#fff; font-size:17px; font-weight:800; }
        .expo-close-head small { color:rgba(255,255,255,.82); }
        .expo-close-body { max-height:calc(100vh - 105px); overflow-y:auto; padding:18px 20px; }
        .expo-close-warning { display:flex; gap:10px; align-items:flex-start; padding:10px 12px; border:1px solid #f2c3c3; border-radius:7px; background:#fff5f5; color:#7f1d1d; font-size:12px; }
        .expo-close-summary { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:8px; margin:12px 0; }
        .expo-close-metric { padding:9px 11px; border:1px solid #e2e8e5; border-radius:6px; background:#f8faf9; }
        .expo-close-metric span { display:block; margin-bottom:2px; color:#75827c; font-size:9px; font-weight:800; text-transform:uppercase; }
        .expo-close-metric strong { color:#263832; font-size:15px; }
        .expo-close-toolbar { display:grid; grid-template-columns:minmax(240px,1fr) auto; gap:10px; align-items:center; margin-bottom:10px; }
        .expo-close-search { position:relative; }
        .expo-close-search i { position:absolute; top:10px; left:11px; color:#9aa6a0; }
        .expo-close-search input { padding-left:33px; border-radius:6px; }
        .expo-close-table { min-width:1080px; margin:0; font-size:11px; }
        .expo-close-table th { padding:7px 8px!important; background:#f3f6f5; color:#607069; font-size:9px; text-transform:uppercase; white-space:nowrap; }
        .expo-close-table td { padding:7px 8px!important; vertical-align:middle!important; border-color:#edf1ef!important; }
        .expo-close-table tr.excluida { background:#fff8e8; }
        .expo-close-invoice-button { display:inline-flex; align-items:center; gap:6px; padding:0; border:0; background:transparent; color:#1f5f8b; font-size:11px; font-weight:800; text-align:left; }
        .expo-close-invoice-button:hover,
        .expo-close-invoice-button:focus { color:#123f60; text-decoration:underline; outline:none; }
        .expo-close-invoice-button i { font-size:9px; }
        .expo-close-detail-row td { padding:0!important; background:#f7faf9; }
        .expo-close-detail { padding:12px 14px; border-left:4px solid #2f7661; color:#344b43; }
        .expo-close-detail-title { display:flex; align-items:center; gap:7px; margin-bottom:9px; color:#244b3e; font-size:11px; font-weight:800; }
        .expo-close-detail-grid { display:grid; grid-template-columns:repeat(5,minmax(130px,1fr)); gap:7px; }
        .expo-close-detail-metric { padding:7px 8px; border:1px solid #dce8e3; border-radius:5px; background:#fff; }
        .expo-close-detail-metric span { display:block; color:#71827b; font-size:8px; font-weight:800; text-transform:uppercase; }
        .expo-close-detail-metric strong { display:block; margin-top:2px; color:#263d35; font-size:12px; }
        .expo-close-detail-formula { margin-top:8px; padding:8px 10px; border-radius:5px; background:#e9f4ef; font-size:10px; line-height:1.5; }
        .expo-close-detail-reason { margin-top:7px; color:#536a62; font-size:10px; }
        .expo-close-detail-tags { display:flex; flex-wrap:wrap; gap:5px; margin-top:7px; }
        .expo-close-detail-tag { padding:3px 6px; border-radius:4px; background:#edf1f5; color:#485b68; font-size:9px; font-weight:700; }
        .expo-close-detail-section { margin-top:10px; }
        .expo-close-detail-section h6 { margin:0 0 5px; color:#536a62; font-size:9px; font-weight:800; text-transform:uppercase; }
        .expo-close-breakdown-table { width:100%; margin:0; border:1px solid #dce6e2; background:#fff; font-size:10px; }
        .expo-close-breakdown-table th { padding:5px 7px!important; background:#edf3f0!important; color:#5d7169!important; font-size:8px!important; }
        .expo-close-breakdown-table td { padding:5px 7px!important; }
        .expo-close-final-increase { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:10px; padding:9px 11px; border:1px solid #e7c0c0; border-radius:5px; background:#fff5f5; color:#742b2b; }
        .expo-close-final-increase span { font-size:10px; font-weight:800; text-transform:uppercase; }
        .expo-close-final-increase strong { font-size:17px; }
        .expo-close-exclusions { min-height:42px; margin-top:10px; padding:9px 10px; border:1px dashed #e0ad54; border-radius:6px; background:#fffbf1; }
        .expo-close-exclusions label { display:block; margin:0 0 5px; color:#8a5a0a; font-size:9px; font-weight:800; text-transform:uppercase; }
        .expo-close-chip { display:inline-flex; align-items:center; gap:5px; margin:2px 4px 2px 0; padding:4px 7px; border-radius:5px; background:#fff0c7; color:#7a4b00; font-size:10px; font-weight:700; }
        .expo-close-footer { display:flex; justify-content:flex-end; gap:8px; margin-top:12px; padding-top:12px; border-top:1px solid #e8eeeb; }
        @media (max-width: 767px) {
            .expo-panel .ibox-content { padding: 14px; }
            .expo-section { padding: 14px 12px 2px; }
            .expo-actions { flex-direction: column-reverse; }
            .expo-actions .btn { width: 100%; }
            .expo-panel .ibox-title { padding: 12px 14px; }
            .expo-brand-manager { grid-template-columns:1fr; }
            .expo-brand-manager-actions { justify-content:flex-start; }
            .expo-brand-manager-actions .btn { flex:1 1 auto; }
            .expo-history-table { min-width: 880px; }
            .expo-close-summary { grid-template-columns:repeat(2,minmax(0,1fr)); }
            .expo-close-toolbar { grid-template-columns:1fr; }
            .expo-close-body { padding:14px 12px; }
            .expo-close-detail-grid { grid-template-columns:repeat(2,minmax(130px,1fr)); }
        }
    </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2><i class="fa fa-calendar-check-o mr-2" style="color:#e65100;"></i>Configuración de Expos</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item active">
                    <strong>Expo</strong>
                </li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content">
        
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

            @if ($mostrarFormulario)
                <div class="ibox expo-panel">
                    <div class="ibox-title">
                        <h5>
                            <i class="fa {{ $expoEditandoId ? 'fa-pencil' : ($expoDuplicandoId ? 'fa-clone' : 'fa-plus-circle') }} mr-2"></i>
                            {{ $expoEditandoId ? 'Editar Expo' : ($expoDuplicandoId ? 'Duplicar Expo' : 'Nueva Expo') }}
                        </h5>
                        <small>{{ $expoEditandoId ? 'Actualice la configuración comercial y los usuarios autorizados.' : ($expoDuplicandoId ? 'Revise los datos copiados y defina la vigencia de la nueva Expo.' : 'Defina la vigencia y las condiciones comerciales de la Expo.') }}</small>
                    </div>
                    <div class="ibox-content">
                        @if ($expoEditandoId)
                            <div class="expo-version-note">
                                <i class="fa fa-lock mr-1"></i>
                                El nombre y la fecha de inicio no pueden modificarse. Los demás campos se actualizarán en esta Expo.
                            </div>
                        @elseif ($expoDuplicandoId)
                            <div class="expo-version-note">
                                <i class="fa fa-clone mr-1"></i>
                                Se copiaron el alcance, los usuarios y descuentos. Al guardar se creará una Expo nueva.
                            </div>
                        @endif

                        <form wire:submit.prevent="guardar">
                            <div class="expo-section">
                            <div class="expo-section-title"><i class="fa fa-info-circle"></i>Información general y vigencia</div>
                            <div class="row">
                                <div class="form-group col-md-8">
                                    <label>Nombre <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.defer="nombre" class="form-control @error('nombre') is-invalid @enderror" maxlength="150" {{ $expoEditandoId ? 'readonly' : '' }}>
                                    @if ($expoEditandoId)<div class="expo-locked-hint"><i class="fa fa-lock mr-1"></i>Campo no editable.</div>@endif
                                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Estado <span class="text-danger">*</span></label>
                                    <select wire:model.defer="estado" class="form-control @error('estado') is-invalid @enderror">
                                        <option value="Inactivo">Inactiva</option>
                                        <option value="Activo">Activa</option>
                                    </select>
                                    @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-12">
                                    <label>Descripción</label>
                                    <textarea wire:model.defer="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="3"></textarea>
                                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Inicio de vigencia <span class="text-danger">*</span></label>
                                    <input type="datetime-local" wire:model.defer="fechaInicio" class="form-control @error('fechaInicio') is-invalid @enderror" {{ $expoEditandoId ? 'readonly' : '' }}>
                                    @if ($expoEditandoId)<div class="expo-locked-hint"><i class="fa fa-lock mr-1"></i>Campo no editable.</div>@endif
                                    @error('fechaInicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Finalización</label>
                                    <input type="datetime-local" wire:model.defer="fechaFin" class="form-control @error('fechaFin') is-invalid @enderror">
                                    @error('fechaFin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            </div>

                            <div class="expo-section">
                            <div class="expo-section-title"><i class="fa fa-sliders"></i>Alcance comercial</div>
                            @php
                                $idsBodegas = $bodegas->pluck('id')->map(fn ($id) => (string) $id)->all();
                                $idsEscalas = $escalas->pluck('id')->map(fn ($id) => (string) $id)->all();
                                $todasBodegas = count($idsBodegas) > 0 && empty(array_diff($idsBodegas, array_map('strval', $bodegasSeleccionadas)));
                                $todasEscalas = count($idsEscalas) > 0 && empty(array_diff($idsEscalas, array_map('strval', $escalasSeleccionadas)));
                            @endphp
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Bodegas permitidas <span class="text-danger">*</span></label>
                                    <div class="expo-checklist @error('bodegasSeleccionadas') border-danger @enderror">
                                        <label class="expo-check-all">
                                            <input type="checkbox" wire:click="alternarTodasBodegas" {{ $todasBodegas ? 'checked' : '' }}>
                                            <span>Seleccionar todas</span>
                                            <span class="expo-check-summary ml-auto">{{ count($bodegasSeleccionadas) }}/{{ count($bodegas) }}</span>
                                        </label>
                                        @foreach ($bodegas as $bodega)
                                            <div class="expo-check-item" wire:key="expo-bodega-{{ $bodega->id }}">
                                                <label for="expo-bodega-{{ $bodega->id }}">
                                                    <input id="expo-bodega-{{ $bodega->id }}" type="checkbox" value="{{ $bodega->id }}" wire:model.defer="bodegasSeleccionadas">
                                                    <span>{{ $bodega->nombre }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="expo-hint"><i class="fa fa-check-square-o mr-1"></i>Marque una, varias o todas las bodegas.</div>
                                    @error('bodegasSeleccionadas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Escalas permitidas <span class="text-danger">*</span></label>
                                    <div class="expo-checklist @error('escalasSeleccionadas') border-danger @enderror">
                                        <label class="expo-check-all">
                                            <input type="checkbox" wire:click="alternarTodasEscalas" {{ $todasEscalas ? 'checked' : '' }}>
                                            <span>Seleccionar todas</span>
                                            <span class="expo-check-summary ml-auto">{{ count($escalasSeleccionadas) }}/{{ count($escalas) }}</span>
                                        </label>
                                        @foreach ($escalas as $escala)
                                            <div class="expo-check-item" wire:key="expo-escala-{{ $escala->id }}">
                                                <label for="expo-escala-{{ $escala->id }}">
                                                    <input id="expo-escala-{{ $escala->id }}" type="checkbox" value="{{ $escala->id }}" wire:model.defer="escalasSeleccionadas">
                                                    <span>{{ $escala->nombre }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="expo-hint"><i class="fa fa-check-square-o mr-1"></i>Marque una, varias o todas las escalas permitidas.</div>
                                    @error('escalasSeleccionadas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            </div>

                            <div class="expo-section">
                                <div class="expo-section-title"><i class="fa fa-users"></i>Usuarios autorizados</div>
                                <p class="expo-hint mb-2">Solo los usuarios agregados en esta tabla podrán ver y abrir el botón <strong>Oferta de Expo</strong>.</p>
                                <div class="expo-user-search mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-search"></i></span></div>
                                        <input type="search" wire:model.debounce.300ms="busquedaUsuario" class="form-control" placeholder="Buscar usuario por nombre o correo..." autocomplete="off">
                                    </div>
                                    @if (mb_strlen(trim($busquedaUsuario)) >= 2)
                                        <div class="expo-user-results">
                                            @forelse ($usuariosEncontrados as $usuario)
                                                <button type="button" wire:click="agregarUsuario({{ $usuario->id }})" class="expo-user-result" wire:key="expo-usuario-resultado-{{ $usuario->id }}">
                                                    <i class="fa fa-user-plus"></i>
                                                    <span><strong>{{ $usuario->name }}</strong><small>{{ $usuario->email }}</small></span>
                                                </button>
                                            @empty
                                                <div class="p-3 text-center text-muted small">No se encontraron usuarios activos.</div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                                <div class="expo-discount-wrap @error('usuariosSeleccionados') border-danger @enderror">
                                    <table class="table table-sm expo-users-table">
                                        <thead><tr><th>Usuario</th><th>Correo</th><th style="width:70px;" class="text-center">Acción</th></tr></thead>
                                        <tbody>
                                            @forelse ($usuariosAgregados as $usuario)
                                                <tr wire:key="expo-usuario-agregado-{{ $usuario->id }}">
                                                    <td><i class="fa fa-user-circle-o mr-2 text-muted"></i><strong>{{ $usuario->name }}</strong></td>
                                                    <td class="text-muted">{{ $usuario->email }}</td>
                                                    <td class="text-center">
                                                        <button type="button" wire:click="eliminarUsuario({{ $usuario->id }})" class="btn btn-xs btn-white" title="Quitar usuario">
                                                            <i class="fa fa-trash text-danger"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="text-center text-muted py-3">Busque y agregue los usuarios que podrán usar la Expo.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @error('usuariosSeleccionados') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="expo-section">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:8px;">
                                    <div class="expo-section-title mb-0" style="flex:1; min-width:200px;"><i class="fa fa-list-alt"></i>Descuentos por escala de precios</div>
                                    @unless($expoEditandoId)
                                        <button type="button" wire:click="abrirModalDescuentoMarca" class="btn btn-sm btn-outline-warning" style="border-radius:7px; font-weight:700; font-size:11px; white-space:nowrap;">
                                            <i class="fa fa-plus mr-1"></i> Agregar descuento
                                        </button>
                                    @endunless
                                </div>
                                @if($expoEditandoId)
                                    <div class="expo-brand-manager mb-3">
                                        <div>
                                            <label for="expo-marca-descuento">Escala con descuento configurado</label>
                                            <select id="expo-marca-descuento" wire:model="marcaDescuentoGestionId" class="form-control">
                                                <option value="">Seleccione una escala ({{ $marcasConDescuento->count() }})</option>
                                                @foreach($marcasConDescuento as $marcaDescuento)
                                                    <option value="{{ $marcaDescuento['marca_id'] }}">{{ $marcaDescuento['marca'] }} · {{ $marcaDescuento['total_escalones'] }} escalón(es)</option>
                                                @endforeach
                                            </select>
                                            @error('marcaDescuentoGestionId') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="expo-brand-manager-actions">
                                            <button type="button" wire:click="abrirModalDescuentoMarca" class="btn btn-outline-warning"><i class="fa fa-plus mr-1"></i>Nueva escala</button>
                                            <button type="button" wire:click="editarDescuentoMarcaSeleccionado" wire:loading.attr="disabled" class="btn btn-warning" @if($descuentosMarcaSeleccionada->isEmpty()) disabled @endif><i class="fa fa-pencil mr-1"></i>Editar</button>
                                            <button type="button" wire:click="descargarDescuentosMarcaExcel" wire:loading.attr="disabled" class="btn btn-outline-success" @if($descuentosMarcaSeleccionada->isEmpty()) disabled @endif><i class="fa fa-file-excel-o mr-1"></i>Descargar escala</button>
                                            <button type="button" wire:click="descargarDescuentosMarcaExcel(true)" wire:loading.attr="disabled" class="btn btn-outline-success" @if($marcasConDescuento->isEmpty()) disabled @endif><i class="fa fa-download mr-1"></i>Descargar todas</button>
                                        </div>
                                    </div>
                                @else
                                    <div class="input-group input-group-sm expo-brand-search mb-3">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-search"></i></span></div>
                                        <input type="search" wire:model.debounce.250ms="busquedaDescuentoMarca" class="form-control" placeholder="Buscar por escala..." autocomplete="off">
                                    </div>
                                @endif
                                <div class="expo-discount-wrap expo-brand-table-scroll mb-3">
                                    <table class="table table-sm expo-discount-table">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <button type="button" wire:click="ordenarDescuentosMarca('marca')" class="expo-sort-button {{ $ordenDescuentoMarca === 'marca' ? 'active' : '' }}">
                                                        Escala de precios <i class="fa {{ $ordenDescuentoMarca === 'marca' ? ($direccionDescuentoMarca === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}"></i>
                                                    </button>
                                                </th>
                                                <th>
                                                    <button type="button" wire:click="ordenarDescuentosMarca('venta_minima')" class="expo-sort-button {{ $ordenDescuentoMarca === 'venta_minima' ? 'active' : '' }}">
                                                        Subtotal neto desde (L.) <i class="fa {{ $ordenDescuentoMarca === 'venta_minima' ? ($direccionDescuentoMarca === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}"></i>
                                                    </button>
                                                </th>
                                                <th>
                                                    <button type="button" wire:click="ordenarDescuentosMarca('porcentaje_descuento')" class="expo-sort-button {{ $ordenDescuentoMarca === 'porcentaje_descuento' ? 'active' : '' }}">
                                                        Descuento (%) <i class="fa {{ $ordenDescuentoMarca === 'porcentaje_descuento' ? ($direccionDescuentoMarca === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort' }}"></i>
                                                    </button>
                                                </th>
                                                <th>Asistencia</th>
                                                <th style="width:60px;">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($expoEditandoId ? $descuentosMarcaSeleccionada : $descuentosMarcaTabla as $regla)
                                                <tr wire:key="expo-descuento-marca-{{ $regla['indice'] }}">
                                                    <td><strong>{{ $regla['marca'] }}</strong></td>
                                                    <td>{{ number_format($regla['venta_minima'], 2) }}</td>
                                                    <td>{{ number_format($regla['porcentaje_descuento'], 2) }}%</td>
                                                    <td><span class="badge {{ $regla['requiere_asistencia'] ? 'badge-warning' : 'badge-light' }}">{{ $regla['requiere_asistencia'] ? 'Requerida' : 'No requerida' }}</span></td>
                                                    <td class="text-center">
                                                        <button type="button" wire:click="eliminarDescuentoMarca({{ $regla['indice'] }})" class="btn btn-xs btn-white" title="Eliminar regla de escala"><i class="fa fa-trash text-danger"></i></button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center text-muted py-3">{{ $expoEditandoId && $marcasConDescuento->isNotEmpty() ? 'Seleccione una escala para consultar y editar sus escalones.' : (trim($busquedaDescuentoMarca) !== '' ? 'No hay escalas que coincidan con la búsqueda.' : 'Sin escalones de descuento por escala.') }}</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="expo-section">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:8px;">
                                    <div class="expo-section-title mb-0" style="flex:1; min-width:200px;"><i class="fa fa-percent"></i>Descuentos por total</div>
                                    <button type="button" wire:click="agregarDescuento" class="btn btn-sm btn-outline-warning" style="border-radius:7px; font-weight:700; font-size:11px;">
                                        <i class="fa fa-plus mr-1"></i> Agregar regla
                                    </button>
                                </div>
                                <div class="table-responsive expo-discount-wrap mb-3">
                                    <table class="table table-sm expo-discount-table">
                                        <thead><tr><th>Subtotal neto desde (L.)</th><th>Descuento (%)</th><th style="width:60px;">Acción</th></tr></thead>
                                        <tbody>
                                            @forelse ($descuentos as $indice => $regla)
                                                <tr wire:key="expo-descuento-{{ $indice }}">
                                                    <td>
                                                        <input type="text" inputmode="decimal" wire:model.defer="descuentos.{{ $indice }}.venta_minima"
                                                               class="form-control form-control-sm expo-money-input" placeholder="0.00" autocomplete="off"
                                                               x-data="{ formatMoney() { let raw = $el.value.replace(/,/g, '').replace(/[^0-9.]/g, ''); const point = raw.indexOf('.'); if (point !== -1) raw = raw.slice(0, point + 1) + raw.slice(point + 1).replace(/\./g, '').slice(0, 2); let parts = raw.split('.'); parts[0] = (parts[0] || '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, ','); $el.value = parts[0] + (raw.includes('.') ? '.' + (parts[1] || '') : ''); } }"
                                                               x-init="$nextTick(() => { formatMoney(); const amount = Number($el.value.replace(/,/g, '')); if (Number.isFinite(amount)) $el.value = amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); })"
                                                               x-on:input="formatMoney()" x-on:blur="const amount = Number($el.value.replace(/,/g, '')); $el.value = Number.isFinite(amount) ? amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : ''">
                                                        @error('descuentos.'.$indice.'.venta_minima') <small class="text-danger">{{ $message }}</small> @enderror
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" min="0" max="100" wire:model.defer="descuentos.{{ $indice }}.porcentaje_descuento" class="form-control form-control-sm">
                                                        @error('descuentos.'.$indice.'.porcentaje_descuento') <small class="text-danger">{{ $message }}</small> @enderror
                                                    </td>
                                                    <td class="text-center"><button type="button" wire:click="eliminarDescuento({{ $indice }})" class="btn btn-xs btn-white" title="Eliminar regla"><i class="fa fa-trash text-danger"></i></button></td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="text-center text-muted">Sin descuentos por total.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>


                            <div class="expo-actions">
                                <button type="button" wire:click="cancelar" class="btn btn-default" style="border-radius:8px; font-weight:700;">
                                    <i class="fa fa-times mr-1"></i> Cancelar
                                </button>
                                <button type="submit" class="btn expo-save-btn" wire:loading.attr="disabled">
                                    <i class="fa fa-save mr-1"></i> Guardar configuración
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="ibox expo-panel">
                <div class="ibox-title">
                    <h5><i class="fa fa-list mr-2"></i>Expos configuradas</h5>
                    <small>{{ count($expos) }} configuración(es) registrada(s)</small>
                    <div class="ibox-tools">
                        @unless ($mostrarFormulario)
                            <button type="button" wire:click="nueva" class="btn btn-sm expo-new-btn">
                                <i class="fa fa-plus mr-1"></i> Nueva Expo
                            </button>
                        @endunless
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-hover expo-history-table">
                            <thead>
                                <tr>
                                    <th>Expo</th><th>Estado</th><th>Vigencia</th><th>Creación</th><th>Última modificación</th><th>Configuración</th><th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($expos as $expo)
                                    @php
                                        $totalBodegas = DB::table('expo_bodega')->where('expo_id', $expo->id)->count();
                                        $totalEscalas = DB::table('expo_escala')->where('expo_id', $expo->id)->count();
                                        $totalUsuarios = DB::table('expo_usuario')->where('expo_id', $expo->id)->count();
                                        $totalDescuentos = DB::table('expo_descuento')->where('expo_id', $expo->id)->count();
                                        $totalDescuentosMarca = DB::table('expo_descuento_escala')->where('expo_id', $expo->id)->count();
                                        $expoFinalizada = $expo->fecha_fin && strtotime($expo->fecha_fin) <= time();
                                    @endphp
                                    <tr class="expo-clickable" wire:click="verDetalle({{ $expo->id }})" title="Ver detalle e historial de cambios">
                                        <td><span class="expo-name">{{ $expo->nombre }}</span><br><span class="expo-version">Versión #{{ $expo->id }}</span></td>
                                        <td>
                                            <span class="expo-state {{ $expo->estado === 'Activo' ? 'expo-state-active' : 'expo-state-inactive' }}">
                                                <i class="fa {{ $expo->estado === 'Activo' ? 'fa-check-circle' : 'fa-pause-circle' }}"></i>{{ $expo->estado }}
                                            </span>
                                        </td>
                                        <td><strong>{{ date('d/m/Y H:i', strtotime($expo->fecha_inicio)) }}</strong><br><small class="text-muted">Hasta {{ $expo->fecha_fin ? date('d/m/Y H:i', strtotime($expo->fecha_fin)) : 'sin fecha final' }}</small></td>
                                        <td><i class="fa fa-user-circle-o mr-1 text-muted"></i>{{ $expo->creado_por }}<br><small class="text-muted">{{ date('d/m/Y H:i', strtotime($expo->created_at)) }}</small></td>
                                        <td><i class="fa fa-pencil-square-o mr-1 text-muted"></i>{{ $expo->modificado_por }}<br><small class="text-muted">{{ date('d/m/Y H:i', strtotime($expo->updated_at)) }}</small></td>
                                        <td>
                                            <div class="expo-config-counts">
                                                <span class="expo-config-chip"><i class="fa fa-archive"></i>{{ $totalBodegas }} bodega(s)</span>
                                                <span class="expo-config-chip"><i class="fa fa-tags"></i>{{ $totalEscalas }} escala(s)</span>
                                                <span class="expo-config-chip"><i class="fa fa-users"></i>{{ $totalUsuarios }} usuario(s)</span>
                                                <span class="expo-config-chip"><i class="fa fa-percent"></i>{{ $totalDescuentos }} regla(s)</span>
                                                <span class="expo-config-chip"><i class="fa fa-list-alt"></i>{{ $totalDescuentosMarca }} escala(s) con descuento</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="expo-row-actions" onclick="event.stopPropagation()">
                                                <button type="button" class="expo-actions-toggle" popovertarget="expo-actions-{{ $expo->id }}" onclick="event.stopPropagation(); const rect=this.getBoundingClientRect(); const menu=document.getElementById('expo-actions-{{ $expo->id }}'); menu.style.top=(rect.bottom+4)+'px'; menu.style.left=Math.max(8, Math.min(window.innerWidth-233, rect.right-225))+'px';">
                                                    <i class="fa fa-ellipsis-v"></i> Acciones <i class="fa fa-caret-down"></i>
                                                </button>
                                                <div id="expo-actions-{{ $expo->id }}" popover class="expo-actions-popover" onclick="event.stopPropagation()">
                                                    <button type="button" onclick="this.closest('[popover]').hidePopover()" wire:click.stop="verDetalle({{ $expo->id }})" class="dropdown-item"><i class="fa fa-eye"></i>Ver detalle de la Expo</button>
                                                    <a href="{{ url('/expo/reporte_de_expo') }}?expo_id={{ $expo->id }}" target="_blank" rel="noopener" class="dropdown-item report"><i class="fa fa-list-alt"></i>Reporte de flujos realizados</a>
                                                    <div class="dropdown-divider"></div>
                                                    @if($expo->estado === 'Activo')
                                                        <button type="button" onclick="this.closest('[popover]').hidePopover()" wire:click.stop="abrirCierreExpo({{ $expo->id }})" class="dropdown-item close-expo"><i class="fa fa-lock"></i>Finalizar facturación</button>
                                                    @endif
                                                    @unless ($expoFinalizada)
                                                        <button type="button" onclick="this.closest('[popover]').hidePopover()" wire:click.stop="editar({{ $expo->id }})" class="dropdown-item"><i class="fa fa-pencil"></i>Editar Expo</button>
                                                    @endunless
                                                    <button type="button" onclick="this.closest('[popover]').hidePopover()" wire:click.stop="duplicar({{ $expo->id }})" class="dropdown-item"><i class="fa fa-clone"></i>Duplicar Expo</button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="expo-empty"><i class="fa fa-calendar-o"></i><strong>No hay Expos configuradas</strong><span>Cree la primera configuración para habilitar ofertas de Expo.</span></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($mostrarDetalle && !empty($expoDetalle['expo']))
                @php $detalle = $expoDetalle['expo']; @endphp
                <div class="expo-detail-backdrop" wire:click.self="cerrarDetalle">
                    <div class="expo-detail-modal">
                        <div class="expo-detail-head">
                            <div><h4><i class="fa fa-calendar-check-o mr-2"></i>{{ $detalle['nombre'] }}</h4><small>Expo #{{ $detalle['id'] }}</small></div>
                            <button type="button" wire:click="cerrarDetalle" class="expo-detail-close" title="Cerrar"><i class="fa fa-times"></i></button>
                        </div>
                        <div class="expo-detail-body">
                            <div class="expo-detail-grid">
                                <div class="expo-detail-field"><label>Estado</label><div>{{ $detalle['estado'] }}</div></div>
                                <div class="expo-detail-field"><label>Vigencia</label><div>{{ date('d/m/Y H:i', strtotime($detalle['fecha_inicio'])) }} a {{ $detalle['fecha_fin'] ? date('d/m/Y H:i', strtotime($detalle['fecha_fin'])) : 'sin fecha final' }}</div></div>
                                <div class="expo-detail-field"><label>Creada por</label><div>{{ $detalle['creado_por'] }} · {{ date('d/m/Y H:i', strtotime($detalle['created_at'])) }}</div></div>
                                <div class="expo-detail-field"><label>Última modificación</label><div>{{ $detalle['modificado_por'] }} · {{ date('d/m/Y H:i', strtotime($detalle['updated_at'])) }}</div></div>
                                <div class="expo-detail-field" style="grid-column:1/-1;"><label>Descripción</label><div>{{ $detalle['descripcion'] ?: 'Sin descripción.' }}</div></div>
                            </div>
                            <div class="expo-detail-section"><h6><i class="fa fa-archive mr-1"></i>Bodegas</h6><div class="expo-detail-tags">@forelse($expoDetalle['bodegas'] as $item)<span class="expo-detail-tag">{{ $item }}</span>@empty<span class="text-muted small">Sin bodegas.</span>@endforelse</div></div>
                            <div class="expo-detail-section"><h6><i class="fa fa-tags mr-1"></i>Escalas</h6><div class="expo-detail-tags">@forelse($expoDetalle['escalas'] as $item)<span class="expo-detail-tag">{{ $item }}</span>@empty<span class="text-muted small">Sin escalas.</span>@endforelse</div></div>
                            <div class="expo-detail-section"><h6><i class="fa fa-percent mr-1"></i>Reglas de descuento</h6><div class="expo-detail-tags">@forelse($expoDetalle['descuentos'] as $regla)<span class="expo-detail-tag">Desde L {{ number_format($regla['venta_minima'], 2) }}: <strong>{{ number_format($regla['porcentaje_descuento'], 2) }}%</strong></span>@empty<span class="text-muted small">Sin descuentos.</span>@endforelse</div></div>
                            <div class="expo-detail-section"><h6><i class="fa fa-list-alt mr-1"></i>Descuentos por escala de precios</h6><div class="expo-detail-tags">@forelse($expoDetalle['descuentos_escala'] as $regla)<span class="expo-detail-tag">{{ $regla['escala'] }} con subtotal neto desde L {{ number_format($regla['venta_minima'], 2) }}: <strong>{{ number_format($regla['porcentaje_descuento'], 2) }}%</strong>{{ $regla['requiere_asistencia'] ? ' · Requiere asistencia' : '' }}</span>@empty<span class="text-muted small">Sin descuentos por escala.</span>@endforelse</div></div>
                            <div class="expo-detail-section"><h6><i class="fa fa-users mr-1"></i>Usuarios autorizados</h6><table class="expo-detail-users"><tbody>@forelse($expoDetalle['usuarios'] as $usuario)<tr><td><strong>{{ $usuario['name'] }}</strong></td><td class="text-muted">{{ $usuario['email'] }}</td></tr>@empty<tr><td class="text-muted">Sin usuarios autorizados.</td></tr>@endforelse</tbody></table></div>
                            <div class="expo-detail-section">
                                <h6><i class="fa fa-history mr-1"></i>Historial de cambios de esta Expo</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead><tr><th>Fecha</th><th>Cambio</th><th>Detalle</th><th>Usuario</th></tr></thead>
                                        <tbody>
                                            @forelse($expoDetalle['historial_cambios'] as $cambio)
                                                <tr>
                                                    <td style="white-space:nowrap;">{{ date('d/m/Y H:i:s', strtotime($cambio['created_at'])) }}</td>
                                                    <td><span class="expo-state expo-state-inactive">{{ str_replace('_', ' ', $cambio['accion']) }}</span></td>
                                                    <td>{{ $cambio['detalle'] }}</td>
                                                    <td>{{ $cambio['usuario'] }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-muted text-center">Esta Expo aún no tiene cambios registrados.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="expo-detail-section">
                                <h6><i class="fa fa-exchange mr-1"></i>Control de facturación</h6>
                                @if($detalle['estado'] === 'Activo')
                                    <p class="text-muted small mb-2">Revise las ofertas incompletas y los movimientos antes de impedir nuevas facturas.</p>
                                    <button type="button" wire:click="abrirCierreExpo({{ $detalle['id'] }})" wire:loading.attr="disabled" class="btn btn-danger btn-sm">
                                        <i class="fa fa-lock mr-1"></i>Finalizar facturación Expo
                                    </button>
                                @elseif($detalle['estado'] === 'Cerrada')
                                    <textarea wire:model.defer="motivoReapertura" class="form-control form-control-sm mb-2" maxlength="500" rows="2" placeholder="Motivo obligatorio para la reapertura"></textarea>
                                    @error('motivoReapertura')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                                    <button type="button" wire:click="reabrirExpo({{ $detalle['id'] }})" wire:loading.attr="disabled" class="btn btn-success btn-sm mb-2">
                                        <i class="fa fa-unlock mr-1"></i>Reabrir Expo completa
                                    </button>
                                    <div class="table-responsive mt-2">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead><tr><th>Flujo</th><th>Oferta</th><th>Estado</th><th class="text-right">Aumento</th><th></th></tr></thead>
                                            <tbody>
                                            @forelse($expoDetalle['flujos'] as $flujo)
                                                <tr>
                                                    <td>#{{ $flujo['flujo_id'] ?: '-' }}</td>
                                                    <td>#{{ $flujo['cotizacion_id'] }}</td>
                                                    <td>{{ $flujo['estado'] }}</td>
                                                    <td class="text-right">L {{ number_format($flujo['aumento_aplicado'] ?? 0, 2) }}</td>
                                                    <td class="text-right">
                                                        @if(in_array($flujo['estado'], ['LIQUIDADA', 'PENDIENTE_LIQUIDACION'], true))
                                                            <button type="button" wire:click="reabrirFlujo({{ $flujo['id'] }})" wire:loading.attr="disabled" class="btn btn-xs btn-outline-success" title="Reabrir solo este flujo">
                                                                <i class="fa fa-unlock"></i>
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-muted text-center">Esta Expo no tiene flujos registrados.</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                            @if(!empty($expoDetalle['exclusiones_aumento']))
                                <div class="expo-detail-section">
                                    <h6><i class="fa fa-ban mr-1"></i>Facturas exoneradas del aumento</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead><tr><th>Oferta</th><th>Factura</th><th>Cliente</th><th class="text-right">Monto exonerado</th><th>Autorizado por</th><th>Fecha</th></tr></thead>
                                            <tbody>
                                            @foreach($expoDetalle['exclusiones_aumento'] as $exclusion)
                                                <tr>
                                                    <td>#{{ $exclusion['cotizacion_id'] }}</td>
                                                    <td>{{ $exclusion['factura'] }} <small class="text-muted">(ID {{ $exclusion['factura_id'] }})</small></td>
                                                    <td>{{ $exclusion['cliente'] }}</td>
                                                    <td class="text-right">L {{ number_format($exclusion['monto_exonerado'], 2) }}</td>
                                                    <td>{{ $exclusion['excluido_por'] }}</td>
                                                    <td>{{ date('d/m/Y H:i', strtotime($exclusion['created_at'])) }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if($mostrarCierreExpo && !empty($expoCierre))
                @php
                    $filtroNormalizado = mb_strtolower(trim($filtroCierre));
                    $facturasExcluidasIds = array_map('intval', $facturasExcluidasCierre);
                    $flujosCierre = collect($cierreCandidatos)->groupBy('flujo_id')->map(function ($facturas) {
                        $primera = $facturas->first();
                        return [
                            'flujo_id' => (int) $primera['flujo_id'],
                            'cotizacion_id' => (int) $primera['cotizacion_id'],
                            'cliente' => $primera['cliente'],
                            'facturas' => $facturas->values(),
                            'descuento_otorgado' => $facturas->sum('descuento_otorgado'),
                            'monto_aumento' => $facturas->sum('monto_aumento'),
                            'detalle_aumento' => $primera['detalle_aumento'],
                        ];
                    })->values();
                    $flujosFiltrados = $flujosCierre->filter(function ($flujo) use ($filtroNormalizado) {
                        if ($filtroNormalizado === '') return true;
                        $texto = collect($flujo['facturas'])->map(fn ($factura) => implode(' ', [
                            $factura['numero'] ?? '', $factura['id'] ?? '', $factura['cliente'] ?? '',
                            $factura['asesor'] ?? '', $factura['teleasesor'] ?? '', $factura['gestor'] ?? '',
                        ]))->push($flujo['flujo_id'])->push($flujo['cotizacion_id'])->implode(' ');
                        return str_contains(mb_strtolower($texto), $filtroNormalizado);
                    });
                    $seleccionadas = collect($cierreCandidatos)->filter(fn ($factura) => in_array((int) $factura['id'], $facturasExcluidasIds, true));
                    $totalAumento = collect($cierreCandidatos)->sum('monto_aumento');
                    $totalExonerado = $seleccionadas->sum('monto_aumento');
                @endphp
                <div class="expo-detail-backdrop" wire:click.self="cerrarModalCierreExpo">
                    <div class="expo-close-modal">
                        <div class="expo-close-head">
                            <div>
                                <h4><i class="fa fa-lock mr-2"></i>Finalizar facturación de {{ $expoCierre['nombre'] }}</h4>
                                <small>Revise los aumentos antes de bloquear definitivamente nuevas facturas para estas ofertas.</small>
                            </div>
                            <button type="button" wire:click="cerrarModalCierreExpo" class="expo-detail-close" title="Cerrar"><i class="fa fa-times"></i></button>
                        </div>
                        <div class="expo-close-body">
                            <div class="expo-close-warning">
                                <i class="fa fa-exclamation-triangle mt-1"></i>
                                <span>Al confirmar, la Expo quedará cerrada y sus ofertas ya no admitirán nuevas facturas. Solo se crearán aumentos cuando el descuento otorgado supere al ganado según el subtotal neto comprado y los escalones configurados para cada marca.</span>
                            </div>

                            <div class="expo-close-summary">
                                <div class="expo-close-metric"><span>Ofertas con aumento</span><strong>{{ collect($cierreCandidatos)->pluck('cotizacion_id')->unique()->count() }}</strong></div>
                                <div class="expo-close-metric"><span>Facturas afectadas</span><strong>{{ count($cierreCandidatos) }}</strong></div>
                                <div class="expo-close-metric"><span>Aumento previsto</span><strong>L {{ number_format($totalAumento, 2) }}</strong></div>
                                <div class="expo-close-metric"><span>Monto exonerado</span><strong>L {{ number_format($totalExonerado, 2) }}</strong></div>
                            </div>

                            @if($ofertasCierreSinAumento > 0)
                                <div class="alert alert-success py-2 mb-2 small"><i class="fa fa-check-circle mr-1"></i>{{ $ofertasCierreSinAumento }} oferta(s) no generan aumento según el recálculo por monto general y por marca, o porque aún no recibieron descuento en una factura.</div>
                            @endif

                            <div class="expo-close-toolbar">
                                <div class="expo-close-search">
                                    <i class="fa fa-search"></i>
                                    <input type="search" wire:model.debounce.300ms="filtroCierre" class="form-control form-control-sm" placeholder="Buscar factura, oferta, cliente, asesor, teleasesor o gestor">
                                </div>
                                <span class="text-muted small">{{ $flujosFiltrados->count() }} de {{ $flujosCierre->count() }} flujo(s) · {{ count($cierreCandidatos) }} factura(s)</span>
                            </div>

                            <div class="table-responsive" style="border:1px solid #dfe7e3;border-radius:6px;max-height:310px;overflow:auto;">
                                <table class="table table-hover expo-close-table">
                                    <thead><tr><th>Flujo</th><th>Oferta</th><th>Cliente</th><th class="text-center">Facturas</th><th class="text-right">Descuento otorgado</th><th class="text-right">Aumento</th></tr></thead>
                                    <tbody>
                                    @forelse($flujosFiltrados as $flujo)
                                        <tr>
                                            <td>
                                                <button type="button" wire:click="alternarDetalleFlujoCierre({{ $flujo['flujo_id'] }})" class="expo-close-invoice-button" aria-expanded="{{ (int) $flujoDetalleCierreId === (int) $flujo['flujo_id'] ? 'true' : 'false' }}" title="Ver detalle consolidado del flujo">
                                                    <i class="fa fa-chevron-{{ (int) $flujoDetalleCierreId === (int) $flujo['flujo_id'] ? 'up' : 'down' }}"></i>
                                                    Flujo #{{ $flujo['flujo_id'] }}
                                                </button>
                                            </td>
                                            <td>#{{ $flujo['cotizacion_id'] }}</td>
                                            <td>{{ $flujo['cliente'] }}</td>
                                            <td class="text-center"><strong>{{ $flujo['facturas']->count() }}</strong></td>
                                            <td class="text-right">L {{ number_format($flujo['descuento_otorgado'], 2) }}</td>
                                            <td class="text-right"><strong>L {{ number_format($flujo['monto_aumento'], 2) }}</strong></td>
                                        </tr>
                                        @if((int) $flujoDetalleCierreId === (int) $flujo['flujo_id'])
                                            @php $aumento = $flujo['detalle_aumento']; @endphp
                                            <tr class="expo-close-detail-row">
                                                <td colspan="6">
                                                    <div class="expo-close-detail">
                                                        <div class="expo-close-detail-title"><i class="fa fa-calculator"></i>Detalle consolidado del flujo #{{ $flujo['flujo_id'] }}</div>
                                                        <div class="expo-close-detail-grid">
                                                            <div class="expo-close-detail-metric"><span>Compra acumulada</span><strong>L {{ number_format($aumento['total_facturado'], 2) }}</strong></div>
                                                            <div class="expo-close-detail-metric"><span>Total en facturas</span><strong>{{ $flujo['facturas']->count() }}</strong></div>
                                                            <div class="expo-close-detail-metric"><span>Descuento otorgado</span><strong>L {{ number_format($aumento['descuento_otorgado_oferta'], 2) }}</strong></div>
                                                            <div class="expo-close-detail-metric"><span>Descuento ganado</span><strong>L {{ number_format($aumento['descuento_ganado'], 2) }}</strong></div>
                                                            <div class="expo-close-detail-metric"><span>Diferencia</span><strong>L {{ number_format($aumento['aumento_oferta'], 2) }}</strong></div>
                                                        </div>

                                                        <div class="expo-close-detail-section">
                                                            <h6>Detalle por factura</h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered expo-close-breakdown-table">
                                                                    <thead><tr><th>Excluir</th><th>Factura</th><th class="text-right">Compra</th><th class="text-right">Descuento otorgado</th><th class="text-right">Participación</th><th class="text-right">Aumento</th><th>Asesor</th></tr></thead>
                                                                    <tbody>
                                                                    @foreach($flujo['facturas'] as $factura)
                                                                        <tr class="{{ in_array((int) $factura['id'], $facturasExcluidasIds, true) ? 'excluida' : '' }}">
                                                                            <td class="text-center"><input type="checkbox" wire:model="facturasExcluidasCierre" value="{{ $factura['id'] }}" aria-label="Excluir factura {{ $factura['numero'] }} del aumento"></td>
                                                                            <td><strong>{{ $factura['numero'] }}</strong><br><small class="text-muted">ID {{ $factura['id'] }}</small></td>
                                                                            <td class="text-right">L {{ number_format($factura['subtotal_bruto'], 2) }}</td>
                                                                            <td class="text-right">L {{ number_format($factura['descuento_otorgado'], 2) }}</td>
                                                                            <td class="text-right">{{ number_format($factura['detalle_aumento']['proporcion_factura'] * 100, 2) }}%</td>
                                                                            <td class="text-right"><strong>L {{ number_format($factura['monto_aumento'], 2) }}</strong></td>
                                                                            <td>{{ $factura['asesor'] }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>

                                                        <div class="expo-close-detail-section">
                                                            @php($detallePorEscala = ($aumento['tipo_descuento'] ?? 'marca') === 'escala')
                                                            <h6>Detalle sumado por {{ $detallePorEscala ? 'escala de precios' : 'marca' }}</h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered expo-close-breakdown-table">
                                                                    <thead><tr><th>{{ $detallePorEscala ? 'Escala' : 'Marca' }}</th><th class="text-right">Compra acumulada</th><th class="text-right">% {{ $detallePorEscala ? 'escala' : 'marca' }}</th><th class="text-right">Desc. {{ $detallePorEscala ? 'escala' : 'marca' }}</th><th class="text-right">Desc. general</th><th class="text-right">Total ganado</th><th class="text-right">Total otorgado</th></tr></thead>
                                                                    <tbody>
                                                                    @foreach($aumento['detalle_marcas'] as $marca)
                                                                        <tr>
                                                                            <td><strong>{{ $marca['marca'] }}</strong></td>
                                                                            <td class="text-right">L {{ number_format($marca['subtotal_bruto'], 2) }}</td>
                                                                            <td class="text-right">{{ number_format($marca['porcentaje_marca'], 2) }}%</td>
                                                                            <td class="text-right">L {{ number_format($marca['descuento_marca'], 2) }}</td>
                                                                            <td class="text-right">L {{ number_format($marca['descuento_general'], 2) }}</td>
                                                                            <td class="text-right"><strong>L {{ number_format($marca['descuento_ganado'], 2) }}</strong></td>
                                                                            <td class="text-right"><strong>L {{ number_format($marca['descuento_otorgado'], 2) }}</strong></td>
                                                                        </tr>
                                                                    @endforeach
                                                                    </tbody>
                                                                    <tfoot><tr><th colspan="5">Totales del flujo</th><th class="text-right">L {{ number_format($aumento['descuento_ganado'], 2) }}</th><th class="text-right">L {{ number_format($aumento['descuento_otorgado_oferta'], 2) }}</th></tr></tfoot>
                                                                </table>
                                                            </div>
                                                        </div>

                                                        <div class="expo-close-detail-formula">
                                                            <strong>Cálculo:</strong> L {{ number_format($aumento['descuento_otorgado_oferta'], 2) }} otorgado − L {{ number_format($aumento['descuento_ganado'], 2) }} ganado por escalones = L {{ number_format($aumento['aumento_oferta'], 2) }} a recuperar. Este monto se distribuye entre las facturas según el descuento otorgado en cada una.
                                                        </div>
                                                        <div class="expo-close-final-increase"><span>Monto final del aumento del flujo</span><strong>L {{ number_format($flujo['monto_aumento'], 2) }}</strong></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted py-4">{{ count($cierreCandidatos) ? 'No hay resultados para este filtro.' : 'Ningún flujo requiere movimiento de aumento.' }}</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="expo-close-exclusions">
                                <label>Facturas que no recibirán movimiento de aumento</label>
                                @forelse($seleccionadas as $factura)
                                    <span class="expo-close-chip"><i class="fa fa-ban"></i>{{ $factura['numero'] }} · L {{ number_format($factura['monto_aumento'], 2) }}</span>
                                @empty
                                    <span class="text-muted small">No ha seleccionado exclusiones.</span>
                                @endforelse
                                @error('facturasExcluidasCierre')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group mt-3 mb-0">
                                <label class="expo-label">Motivo del cierre <span class="text-danger">*</span></label>
                                <textarea wire:model.defer="motivoCierre" class="form-control form-control-sm" maxlength="500" rows="2" placeholder="Explique por qué finaliza la facturación de esta Expo"></textarea>
                                @error('motivoCierre')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="expo-close-footer">
                                <button type="button" wire:click="cerrarModalCierreExpo" class="btn btn-default btn-sm">Cancelar</button>
                                <button type="button" wire:click="cerrarExpo({{ $expoCierre['id'] }})" wire:loading.attr="disabled" class="btn btn-danger btn-sm">
                                    <i class="fa fa-lock mr-1"></i>Bloquear facturación y aplicar movimientos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        @include('livewire.flujodeventa.partials.modal-descuento-marca')
    </div>
