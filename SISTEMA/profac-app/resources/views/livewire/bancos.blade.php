<div>
    @push('styles')
    <style>
        @@keyframes banco-pop {
            from { opacity:0; transform:scale(.88) translateY(20px); }
            to   { opacity:1; transform:scale(1)   translateY(0); }
        }
        .banco-modal-wrap .modal-content {
            animation: banco-pop .28s cubic-bezier(.34,1.56,.64,1) both;
        }
        #tbl_bancos_listar thead th {
            background: linear-gradient(135deg,#f39c12 0%,#e67e22 100%) !important;
            color: #fff !important;
            font-size: 12px;
            font-weight: 700;
            border: none !important;
            padding: 10px 14px;
            white-space: nowrap;
        }
        #tbl_bancos_listar tbody tr:hover { background: #fff8f0 !important; }
        #tbl_bancos_listar tbody td {
            vertical-align: middle;
            font-size: 13px;
            padding: 9px 14px;
            border-color: #f0f0f0 !important;
        }
        .banco-badge-cod {
            display: inline-block;
            background: linear-gradient(135deg,#f39c12,#e67e22);
            color: #fff;
            border-radius: 20px;
            padding: 2px 10px;
            font-size: 11px;
            font-weight: 700;
        }
        .banco-btn-edit {
            background: linear-gradient(135deg,#f39c12,#e67e22);
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 5px 13px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity .15s, transform .15s;
        }
        .banco-btn-edit:hover { opacity:.85; transform:translateY(-1px); }
        .banco-modal-header {
            background: linear-gradient(135deg,#f39c12 0%,#e67e22 100%) !important;
            border: none !important;
        }
        .banco-form-label { font-size:12px; font-weight:700; color:#555; margin-bottom:4px; }
        .banco-form-label .req { color:#e74c3c; }
        .banco-form-input {
            border-radius: 8px;
            border: 1.5px solid #e0e3ee;
            padding: 8px 12px;
            font-size: 13px;
            width: 100%;
            transition: border-color .2s, box-shadow .2s;
        }
        .banco-form-input:focus {
            outline: none;
            border-color: #f39c12;
            box-shadow: 0 0 0 3px rgba(243,156,18,.15);
        }
    </style>
    @endpush

    {{-- ===== ENCABEZADO ===== --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10 col-md-9">
            <h2><i class="fa fa-university text-warning"></i> Gesti&oacute;n de Bancos</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item">Configuraci&oacute;n</li>
                <li class="breadcrumb-item active"><strong>Bancos</strong></li>
            </ol>
        </div>
        <div class="col-lg-2 col-md-3 d-flex align-items-center justify-content-end" style="padding-top:12px;">
            <button type="button" data-toggle="modal" data-target="#modal_banco_crear"
                    style="background:linear-gradient(135deg,#f39c12,#e67e22); color:#fff;
                           border:none; border-radius:8px; padding:8px 18px;
                           font-size:13px; font-weight:700; cursor:pointer;
                           display:inline-flex; align-items:center; gap:6px;
                           box-shadow:0 2px 8px rgba(243,156,18,.4);">
                <i class="fa fa-plus"></i> A&ntilde;adir Banco
            </button>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content" style="padding:24px;">
                        <div class="table-responsive">
                            <table id="tbl_bancos_listar"
                                   class="table table-hover"
                                   style="width:100%; border-collapse:separate; border-spacing:0;
                                          border-radius:10px; overflow:hidden;
                                          box-shadow:0 2px 12px rgba(0,0,0,.07); border:1px solid #f0f0f0;">
                                <thead>
                                    <tr>
                                        <th style="width:70px;">#</th>
                                        <th>Banco</th>
                                        <th>Cuenta</th>
                                        <th>Registrado por</th>
                                        <th style="width:110px; text-align:center;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL: CREAR BANCO ===== --}}
    <div class="modal fade banco-modal-wrap" id="modal_banco_crear" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:460px;">
            <div class="modal-content" style="border:none; border-radius:14px; overflow:hidden;
                        box-shadow:0 20px 60px rgba(0,0,0,.3);">
                <div class="modal-header banco-modal-header" style="padding:14px 22px;">
                    <h5 class="modal-title m-0" style="color:#fff; font-weight:700; font-size:15px;">
                        <i class="fa fa-plus-circle mr-2"></i>Registrar Banco
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"
                            style="color:#fff; opacity:1; font-size:22px;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding:24px; background:#fff;">
                    <form id="crearBancoForm" name="crearBancoForm" data-parsley-validate>
                        <div class="mb-3">
                            <label class="banco-form-label">Nombre del Banco <span class="req">*</span></label>
                            <input class="banco-form-input" type="text"
                                   id="nombre_banco" name="nombre_banco"
                                   placeholder="Ej. Banco Atl&aacute;ntida"
                                   data-parsley-required required>
                        </div>
                        <div class="mb-3">
                            <label class="banco-form-label">N&uacute;mero de Cuenta <span class="req">*</span></label>
                            <input class="banco-form-input" type="text"
                                   id="cuenta" name="cuenta"
                                   placeholder="Ej. 1234-5678-90"
                                   data-parsley-required required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border:none; padding:12px 22px 18px; background:#f8f9fc;
                             display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal"
                            style="border-radius:20px; padding:6px 20px;">
                        <i class="fa fa-times mr-1"></i>Cancelar
                    </button>
                    <button type="submit" form="crearBancoForm"
                            style="background:linear-gradient(135deg,#f39c12,#e67e22); color:#fff;
                                   border:none; border-radius:20px; padding:7px 22px;
                                   font-size:13px; font-weight:700; cursor:pointer;
                                   box-shadow:0 2px 8px rgba(243,156,18,.4);">
                        <i class="fa fa-save mr-1"></i>Guardar Banco
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL: EDITAR BANCO ===== --}}
    <div class="modal fade banco-modal-wrap" id="modal_banco_editar" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:460px;">
            <div class="modal-content" style="border:none; border-radius:14px; overflow:hidden;
                        box-shadow:0 20px 60px rgba(0,0,0,.3);">
                <div class="modal-header banco-modal-header" style="padding:14px 22px;">
                    <h5 class="modal-title m-0" style="color:#fff; font-weight:700; font-size:15px;">
                        <i class="fa fa-pencil mr-2"></i>Editar Banco
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"
                            style="color:#fff; opacity:1; font-size:22px;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding:24px; background:#fff;">
                    <form id="editarBancoForm" name="editarBancoForm" data-parsley-validate>
                        <input id="idBanco" name="idBanco" type="hidden" value="">
                        <div class="mb-3">
                            <label class="banco-form-label">Nombre del Banco <span class="req">*</span></label>
                            <input class="banco-form-input" type="text"
                                   id="nombre_banco_editar" name="nombre_banco_editar"
                                   placeholder="Nombre del banco"
                                   data-parsley-required required>
                        </div>
                        <div class="mb-3">
                            <label class="banco-form-label">N&uacute;mero de Cuenta <span class="req">*</span></label>
                            <input class="banco-form-input" type="text"
                                   id="cuenta_editar" name="cuenta_editar"
                                   placeholder="N&uacute;mero de cuenta"
                                   data-parsley-required required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border:none; padding:12px 22px 18px; background:#f8f9fc;
                             display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal"
                            style="border-radius:20px; padding:6px 20px;">
                        <i class="fa fa-times mr-1"></i>Cancelar
                    </button>
                    <button type="submit" form="editarBancoForm"
                            style="background:linear-gradient(135deg,#f39c12,#e67e22); color:#fff;
                                   border:none; border-radius:20px; padding:7px 22px;
                                   font-size:13px; font-weight:700; cursor:pointer;
                                   box-shadow:0 2px 8px rgba(243,156,18,.4);">
                        <i class="fa fa-save mr-1"></i>Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL: LOADING ===== --}}
    <div class="modal" id="modalSpinnerLoading" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:280px;">
            <div class="modal-content" style="border:none; border-radius:14px; overflow:hidden;
                        box-shadow:0 10px 40px rgba(0,0,0,.2); text-align:center; padding:32px 24px;">
                <div style="width:56px; height:56px; border-radius:50%;
                             background:linear-gradient(135deg,#f39c12,#e67e22);
                             margin:0 auto 16px; display:flex; align-items:center; justify-content:center;">
                    <i class="fa fa-spinner fa-spin fa-lg" style="color:#fff;"></i>
                </div>
                <p class="m-0" style="font-size:14px; font-weight:600; color:#555;">Procesando...</p>
                <small class="text-muted">Espere un momento</small>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/js_proyecto/banco-proveedores/bancos.js') }}"></script>
    @endpush
</div>
