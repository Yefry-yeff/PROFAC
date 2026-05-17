<x-app-layout>
@push('styles')
<style>
    .nav-tabs .nav-link { font-weight: 600; color: #555; }
    .nav-tabs .nav-link.active { color: #1ab394; border-bottom: 3px solid #1ab394; }
    .tab-section { padding: 20px 0; }
    .form-section-title { font-size: 0.85rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .08em; color: #1ab394; border-bottom: 2px solid #e3e8ef;
        padding-bottom: 6px; margin-bottom: 16px; }
    .sticky-repo { position: sticky; top: 70px; }
    .doc-item { display: flex; align-items: center; justify-content: space-between;
        padding: 8px 12px; border: 1px solid #dee2e6; border-radius: 6px; margin-bottom: 6px;
        background: #f8f9fc; }
    .doc-item .doc-name { font-size: 0.82rem; color: #333; flex: 1; overflow: hidden;
        text-overflow: ellipsis; white-space: nowrap; }
    .doc-item .doc-actions { display: flex; gap: 4px; flex-shrink: 0; }
    .historico-item { background: #eaf7f4; border-left: 4px solid #1ab394;
        padding: 10px 14px; border-radius: 0 6px 6px 0; margin-bottom: 10px; }
    .historico-item .hi-meta { font-size: 0.75rem; color: #888; margin-top: 4px; }
    .obs-item { background: #fffbe6; border-left: 4px solid #f5a623;
        padding: 10px 14px; border-radius: 0 6px 6px 0; margin-bottom: 10px; }
    .obs-item .obs-meta { font-size: 0.75rem; color: #888; margin-top: 4px; }
    .credito-badge { font-size: 0.7rem; padding: 3px 8px; border-radius: 12px; }
    .credito-activo   { background: #d4edda; color: #155724; }
    .credito-inactivo { background: #f8d7da; color: #721c24; }
    .repo-header { background: #1ab394; color: #fff; padding: 10px 16px; border-radius: 6px 6px 0 0;
        font-weight: 700; font-size: 0.9rem; }
    .repo-body { border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 6px 6px;
        padding: 14px; background: #fff; }
    .doc-repo-card { border: 1px solid #dee2e6; border-radius: 6px; overflow: hidden; height: 100%; display:flex; flex-direction:column; }
    .doc-repo-card-header { background: #e6f5f2; color: #1ab394; font-size: 0.78rem; font-weight: 700;
        padding: 8px 12px; text-transform: uppercase; letter-spacing: .04em;
        border-bottom: 1px solid #b2ddd5; display:flex; align-items:center; gap:6px; }
    .doc-repo-card-body { padding: 10px 12px; background: #fff; flex:1; }
    .tipo-doc-label { font-size: 0.78rem; font-weight: 600; color: #555; margin-bottom: 4px;
        text-transform: uppercase; letter-spacing: .04em; }
    .doc-upload-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
    .doc-upload-row input[type=file] { flex: 1; font-size: 0.82rem; }
    #modalDocPreview .modal-header { background:#1ab394; }
    #modalDocPreview .modal-title { color:#fff; }
    #modalDocPreview .close { color:#fff; opacity:1; }
    .swal-over-modal { z-index: 99999 !important; }
    .historial-resize { resize: vertical; overflow-y: auto; height: 168px; min-height: 100px; }
    .credito-hist-wrap { resize: vertical; overflow-y: auto; height: 110px; min-height: 80px;
        border: 1px solid #dee2e6; border-radius: 4px; padding: 6px; background: #fafafa; }
</style>
@endpush

{{-- Loading overlay --}}
<div id="form_loading_overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.78); z-index:9000; text-align:center; padding-top:18%;">
    <i class="fa fa-spinner fa-spin fa-3x" style="color:#1ab394;"></i>
    <p class="mt-3" style="color:#555; font-size:1rem;">Cargando datos del cliente...</p>
</div>

<div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
    <div class="col-lg-8">
        <h2 id="page-title">{{ $id ? 'Editar Cliente' : 'Registrar Cliente' }}</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/clientes">Clientes</a></li>
            <li class="breadcrumb-item active">{{ $id ? 'Editar' : 'Registrar' }}</li>
        </ol>
    </div>
    <div class="col-lg-4 text-right">
        <a href="/clientes" class="btn btn-secondary mt-3"><i class="fa fa-arrow-left"></i> Volver</a>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <input type="hidden" id="cliente_id_form" value="{{ $id }}">
    <input type="hidden" name="_token" id="csrf_token" value="{!! csrf_token() !!}">

    <div class="row">
        {{-- ============ TABS LEFT ============ --}}
        <div class="col-lg-8 col-xl-9">
            <div class="ibox">
                <div class="ibox-content" style="padding-top:0">
                    <ul class="nav nav-tabs" id="clienteTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-datos-tab" data-toggle="tab" href="#tab-datos" role="tab">
                                <i class="fa fa-user"></i> Datos Principales
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-contacto-tab" data-toggle="tab" href="#tab-contacto" role="tab">
                                <i class="fa fa-phone"></i> Contacto
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-direccion-tab" data-toggle="tab" href="#tab-direccion" role="tab">
                                <i class="fa fa-map-marker"></i> Dirección
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-credito-tab" data-toggle="tab" href="#tab-credito" role="tab">
                                <i class="fa fa-credit-card"></i> Crédito
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-obs-tab" data-toggle="tab" href="#tab-obs" role="tab">
                                <i class="fa fa-comment"></i> Observaciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-refs-tab" data-toggle="tab" href="#tab-refs" role="tab">
                                <i class="fa fa-users"></i> Comentarios Referencias
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-og-tab" data-toggle="tab" href="#tab-og" role="tab">
                                <i class="fa fa-shield"></i> Observación Gerencia
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="clienteTabsContent">

                        {{-- ===== TAB 1: DATOS PRINCIPALES ===== --}}
                        <div class="tab-pane fade show active tab-section" id="tab-datos" role="tabpanel">
                            <p class="form-section-title">Información General</p>
                            <div class="row">
                                <div class="col-md-12 col-lg-8">
                                    <div class="form-group">
                                        <label>Nombre del Cliente <span class="text-danger">*</span></label>
                                        <input type="text" id="dp_nombre" class="form-control" maxlength="500" required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <label>RTN <span class="text-danger">*</span></label>
                                        <input type="text" id="dp_rtn" class="form-control" maxlength="14" pattern="[0-9]{14}">
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <label>Categoría de Cliente / Escala <span class="text-danger">*</span></label>
                                        <select id="dp_escala" class="form-control">
                                            <option value="" disabled selected>-- Seleccione --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <label>Tipo de Cliente <span class="text-danger">*</span></label>
                                        <select id="dp_tipo_cliente" class="form-control">
                                            <option value="" disabled selected>-- Seleccione --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <label>Tipo de Personalidad <span class="text-danger">*</span></label>
                                        <select id="dp_tipo_personalidad" class="form-control">
                                            <option value="" disabled selected>-- Seleccione --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <label>Año de Operación</label>
                                        <input type="number" id="dp_ano_operacion" class="form-control" min="1900" max="2100">
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <label>DNI Representante Legal</label>
                                        <input type="text" id="dp_dni" class="form-control" maxlength="20">
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <label>Vendedor</label>
                                        <select id="dp_vendedor" class="form-control">
                                            <option value="" disabled selected>-- Seleccione --</option>
                                            @foreach($clientes as $v)
                                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <label>Método de Pago</label>
                                        <select id="dp_metodo_pago" class="form-control">
                                            <option value="" disabled selected>-- Seleccione --</option>
                                            @foreach($metodosPago as $mp)
                                            <option value="{{ $mp->descripcion }}">{{ $mp->descripcion }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12 col-lg-4 d-flex align-items-center" style="margin-top: 10px">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="dp_estado" checked>
                                        <label class="form-check-label ml-2" for="dp_estado"><strong>Cliente Activo</strong></label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3" id="btn-guardar-datos-wrap">
                                <button class="btn btn-primary" id="btn_guardar_datos" onclick="guardarDatosPrincipales()">
                                    <i class="fa fa-save"></i> {{ $id ? 'Guardar Cambios' : 'Registrar Cliente' }}
                                </button>
                            </div>
                        </div>

                        {{-- ===== TAB 2: CONTACTO ===== --}}
                        <div class="tab-pane fade tab-section" id="tab-contacto" role="tabpanel">
                            <p class="form-section-title">Datos de Contacto</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Correo Electrónico</label>
                                        <input type="email" id="ct_correo" class="form-control" maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Teléfono Empresa</label>
                                        <input type="text" id="ct_telefono" class="form-control" maxlength="20">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nombre Contacto 1</label>
                                        <input type="text" id="ct_nombre1" class="form-control" maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Teléfono Contacto 1</label>
                                        <input type="text" id="ct_telefono1" class="form-control" maxlength="20">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nombre Contacto 2</label>
                                        <input type="text" id="ct_nombre2" class="form-control" maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Teléfono Contacto 2</label>
                                        <input type="text" id="ct_telefono2" class="form-control" maxlength="20">
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary mt-2" onclick="guardarContacto()" id="btn_guardar_contacto">
                                <i class="fa fa-save"></i> Guardar Contacto
                            </button>
                        </div>

                        {{-- ===== TAB 3: DIRECCIÓN ===== --}}
                        <div class="tab-pane fade tab-section" id="tab-direccion" role="tabpanel">
                            <p class="form-section-title">Dirección</p>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>País <span class="text-danger">*</span></label>
                                        <select id="dir_pais" class="form-control" onchange="cargarDeptosForm()">
                                            <option value="" disabled selected>-- Seleccione --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Departamento <span class="text-danger">*</span></label>
                                        <select id="dir_depto" class="form-control" onchange="cargarMunicipiosForm()">
                                            <option value="" disabled selected>-- Seleccione --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Municipio <span class="text-danger">*</span></label>
                                        <select id="dir_municipio" class="form-control">
                                            <option value="" disabled selected>-- Seleccione --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Dirección Completa <span class="text-danger">*</span></label>
                                        <textarea id="dir_direccion" class="form-control" rows="3" maxlength="500"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Latitud</label>
                                        <input type="text" id="dir_latitud" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Longitud</label>
                                        <input type="text" id="dir_longitud" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary mt-2" onclick="guardarDireccion()" id="btn_guardar_direccion">
                                <i class="fa fa-save"></i> Guardar Dirección
                            </button>
                        </div>

                        {{-- ===== TAB 4: CRÉDITO ===== --}}
                        <div class="tab-pane fade tab-section" id="tab-credito" role="tabpanel">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <p class="form-section-title mb-1">Datos de Crédito</p>
                                    <small class="text-muted">Cada modificación quedará registrada en el historial.</small>
                                </div>
                                <div class="col-md-12 d-flex align-items-center mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="cred_activo"
                                            onchange="toggleCreditoCampos()">
                                        <label class="form-check-label ml-2" for="cred_activo"><strong>Crédito Disponible</strong></label>
                                    </div>
                                </div>
                                {{-- Campos condicionales: solo visibles cuando crédito está activo --}}
                                <div id="credito_campos_condicionales" style="display:none; width:100%" class="col-md-12 row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Monto de Crédito <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text">L</span></div>
                                            <input type="text" id="cred_monto" class="form-control" data-type="currency">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Monto Disponible</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text">L</span></div>
                                            <input type="text" id="cred_monto_disponible" class="form-control" readonly style="background:#f8f9fa; cursor:default;" tabindex="-1">
                                        </div>
                                        <small class="text-muted">Calculado automáticamente por el sistema</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Días de Crédito <span class="text-danger">*</span></label>
                                        <input type="number" id="cred_dias" class="form-control" min="1" max="365">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Referencias Bancarias</label>
                                        <textarea id="cred_ref_bancarias" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Referencias Comerciales</label>
                                        <textarea id="cred_ref_comerciales" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6" style="display:none;">
                                    <div class="form-group">
                                        <label>Fecha de Vigencia del Crédito</label>
                                        <input type="date" id="cred_fecha_vigencia" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="form-check form-switch mb-1">
                                            <input class="form-check-input" type="checkbox" id="cred_letra_cambio"
                                                onchange="toggleObs('obs_letra_cambio_wrap', this.checked)">
                                            <label class="form-check-label ml-2" for="cred_letra_cambio"><strong>Letra de Cambio</strong></label>
                                        </div>
                                        <div id="obs_letra_cambio_wrap" style="display:none;">
                                            <textarea id="obs_letra_cambio" class="form-control mt-1" rows="2"
                                                maxlength="500" placeholder="Observación de letra de cambio..."></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="form-check form-switch mb-1">
                                            <input class="form-check-input" type="checkbox" id="cred_aval_solidario"
                                                onchange="toggleObs('obs_aval_solidario_wrap', this.checked)">
                                            <label class="form-check-label ml-2" for="cred_aval_solidario"><strong>Aval Solidario</strong></label>
                                        </div>
                                        <div id="obs_aval_solidario_wrap" style="display:none;">
                                            <textarea id="obs_aval_solidario" class="form-control mt-1" rows="2"
                                                maxlength="500" placeholder="Observación de aval solidario..."></textarea>
                                        </div>
                                    </div>
                                </div>
                                </div>{{-- /credito_campos_condicionales --}}
                            </div>
                            <button class="btn btn-primary mt-2" onclick="guardarCredito()" id="btn_guardar_credito">
                                <i class="fa fa-save"></i> Guardar Crédito
                            </button>

                            {{-- Historial de crédito --}}
                            <div class="mt-4">
                                <p class="form-section-title">Historial de Modificaciones</p>
                                <div class="credito-hist-wrap">
                                    <div id="historico_credito_container">
                                        <p class="text-muted text-center" id="historico_credito_empty">Sin historial.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===== TAB 5: OBSERVACIONES ===== --}}
                        <div class="tab-pane fade tab-section" id="tab-obs" role="tabpanel">
                            <p class="form-section-title">Observaciones</p>
                            <div class="form-group">
                                <label>Nueva Observación</label>
                                <textarea id="obs_texto" class="form-control" rows="3" maxlength="1000" placeholder="Escriba una observación..."></textarea>
                            </div>
                            <button class="btn btn-primary mb-4" onclick="guardarObservacion()" id="btn_guardar_obs">
                                <i class="fa fa-plus"></i> Agregar Observación
                            </button>
                            <div id="observaciones_container">
                                <p class="text-muted text-center" id="obs_empty">Sin observaciones registradas.</p>
                            </div>
                        </div>

                        {{-- ===== TAB 6: COMENTARIOS REFERENCIAS ===== --}}
                        <div class="tab-pane fade tab-section" id="tab-refs" role="tabpanel">
                            <p class="form-section-title">Comentarios y Referencias</p>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Referencias</label>
                                        <textarea id="ref_referencias" class="form-control" rows="3" maxlength="1000" placeholder="Ingrese referencias del cliente..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tiempo de Relación</label>
                                        <input type="text" id="ref_tiempo_relacion" class="form-control" maxlength="100" placeholder="Ej. 2 años">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tiempo de Crédito</label>
                                        <input type="text" id="ref_tiempo_credito" class="form-control" maxlength="100" placeholder="Ej. 1 año">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Límite de Crédito</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text">L</span></div>
                                            <input type="text" id="ref_limite_credito" class="form-control" placeholder="0.00" data-type="currency">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">&nbsp;</div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Observaciones</label>
                                        <textarea id="ref_observaciones" class="form-control" rows="3" maxlength="1000" placeholder="Observaciones adicionales..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary mt-2" onclick="guardarReferencias()" id="btn_guardar_refs">
                                <i class="fa fa-save"></i> Guardar Comentarios/Referencias
                            </button>
                        </div>

                        {{-- ===== TAB 7: OBSERVACIÓN GERENCIA ===== --}}
                        <div class="tab-pane fade tab-section" id="tab-og" role="tabpanel">
                            <p class="form-section-title">Autorización / Observación de Gerencia</p>
                            <div class="form-group">
                                <label>Autorización de Gerencia <small class="text-muted">(se guarda en el registro de crédito activo)</small></label>
                                <textarea id="og_autorizacion" class="form-control" rows="4" maxlength="1000" placeholder="Escriba la autorización o comentario de gerencia..."></textarea>
                            </div>
                            <button class="btn btn-primary mb-4" onclick="guardarAutorizacionGerencia()" id="btn_guardar_og">
                                <i class="fa fa-save"></i> Guardar Autorización Gerencia
                            </button>

                            <p class="form-section-title mt-3">Historial de Autorizaciones</p>
                            <div id="og_historial_container">
                                <p class="text-muted text-center" style="font-size:0.85rem">Sin historial disponible.</p>
                            </div>
                        </div>

                    </div>{{-- /tab-content --}}
                </div>
            </div>
        </div>

        {{-- ============ HISTORIAL DE CAMBIOS (STICKY, DERECHA) ============ --}}
        <div class="col-lg-4 col-xl-3">
            <div class="sticky-repo">
                <div class="repo-header">
                    <i class="fa fa-history"></i> Historial de Cambios
                </div>
                <div class="repo-body historial-resize" style="overflow-y:auto;">
                    <div id="historial_cambios_container">
                        <p class="text-muted text-center" style="font-size:0.8rem">Sin historial de cambios.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>{{-- /row --}}

    {{-- ============ REPOSITORIO DE DOCUMENTOS (ABAJO, ANCHO COMPLETO) ============ --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="ibox">
                <div class="ibox-title" style="background:#1ab394; border-radius:6px 6px 0 0; padding:10px 16px;">
                    <h5 style="color:#fff; margin:0;"><i class="fa fa-folder-open mr-2"></i>Repositorio de Documentos</h5>
                    <div class="ibox-tools">
                        <div id="repo_aviso" class="badge badge-warning" style="display:none; font-size:0.75rem; padding:5px 10px;">
                            <i class="fa fa-exclamation-triangle"></i> Guarde el cliente primero
                        </div>
                    </div>
                </div>
                <div class="ibox-content">
                    @php
                    $tiposDoc = [
                        'escritura_empresa'      => ['label' => 'Escritura de la Empresa',      'icon' => 'fa-building'],
                        'dni_representante'      => ['label' => 'DNI del Representante Legal',  'icon' => 'fa-id-card'],
                        'rtn'                    => ['label' => 'RTN',                          'icon' => 'fa-file-text-o'],
                        'permiso_operacion'      => ['label' => 'Permiso de Operación',         'icon' => 'fa-certificate'],
                        'croquis'                => ['label' => 'Croquis',                      'icon' => 'fa-map-o'],
                        'contrato_arrendamiento' => ['label' => 'Contrato de Arrendamiento',    'icon' => 'fa-handshake-o'],
                        'foto_establecimiento'   => ['label' => 'Fotos de Establecimiento',     'icon' => 'fa-camera'],
                    ];
                    @endphp
                    <div class="row">
                        @foreach($tiposDoc as $slug => $info)
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="doc-repo-card">
                                <div class="doc-repo-card-header">
                                    <i class="fa {{ $info['icon'] }}"></i> {{ $info['label'] }}
                                </div>
                                <div class="doc-repo-card-body">
                                    <div id="docs_list_{{ $slug }}" class="mb-2">
                                        <span class="text-muted" style="font-size:0.78rem">Sin documento cargado</span>
                                    </div>
                                    <div class="mt-1">
                                        <input type="file" id="file_{{ $slug }}" class="form-control-file mb-1"
                                            accept=".pdf,.png,.jpg,.jpeg,.gif,.doc,.docx" style="font-size:0.78rem;">
                                        <button class="btn btn-xs btn-outline-primary w-100" onclick="subirDocumento('{{ $slug }}')">
                                            <i class="fa fa-upload"></i> Subir documento
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ MODAL VISTA PREVIA ============ --}}
    <div class="modal fade" id="modalDocPreview" tabindex="-1" role="dialog" aria-labelledby="modalDocPreviewLabel">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDocPreviewLabel">Vista Previa</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-0">
                    <div id="doc_preview_area" style="min-height:300px; background:#f5f5f5; display:flex; align-items:center; justify-content:center;">
                        <span class="text-muted">Cargando vista previa...</span>
                    </div>
                </div>
                <div class="modal-footer flex-wrap" style="gap:6px;">
                    <a href="#" id="btn_modal_descargar" class="btn btn-success" target="_blank">
                        <i class="fa fa-download"></i> Descargar
                    </a>
                    <button type="button" class="btn btn-danger" id="btn_modal_eliminar">
                        <i class="fa fa-trash"></i> Eliminar
                    </button>
                    <div class="d-flex align-items-center" style="flex:1; gap:6px; min-width:260px;">
                        <input type="file" id="file_modal_reemplazar" class="form-control-file"
                            accept=".pdf,.png,.jpg,.jpeg,.gif,.doc,.docx" style="font-size:0.82rem; flex:1;">
                        <button type="button" class="btn btn-primary btn-sm" id="btn_modal_reemplazar">
                            <i class="fa fa-upload"></i> Reemplazar
                        </button>
                    </div>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window._vendedoresData = {!! json_encode($clientes) !!};
    window._metodosPagoData = {!! json_encode($metodosPago) !!};
</script>
<script src="{{ asset('js/js_proyecto/cliente/cliente-form.js') }}"></script>
@endpush
</x-app-layout>
