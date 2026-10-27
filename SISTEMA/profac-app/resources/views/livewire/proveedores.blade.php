<div class="proveedores-view">

    @push('styles')
        <link href="{{ asset('css/plugins/iCheck/custom.css') }}" rel="stylesheet">
        <link href="{{ asset('css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css') }}"
            rel="stylesheet">
        <style>
            .proveedores-view {
                --proveedores-primary: #f39c12;
                --proveedores-primary-dark: #c04e00;
                --proveedores-orange: #e67e22;
                --proveedores-bg: #f4f6f9;
                --proveedores-text: #1a202c;
                --proveedores-muted: #6b7280;
                --proveedores-border: #e8d5bf;
                --proveedores-table-bg: #fdf4e7;
                --proveedores-table-text: #7d3f00;
                --proveedores-table-border: #f2d49a;
            }

            .proveedores-view .page-heading {
                padding: 18px 24px;
                margin-bottom: 0;
                background: #fff;
                border-color: var(--proveedores-border);
            }

            .proveedores-view .page-heading h2 {
                margin: 0 0 5px;
                color: var(--proveedores-text);
                font-size: 24px;
                font-weight: 600;
            }

            .proveedores-view .page-heading h2 i {
                margin-right: 8px;
                color: var(--proveedores-orange);
            }

            .proveedores-view .breadcrumb {
                padding: 0;
                margin: 0;
                background: transparent;
            }

            .proveedores-view .breadcrumb a,
            .proveedores-view .breadcrumb-item {
                color: var(--proveedores-muted);
                font-size: 13px;
            }

            .proveedores-view .breadcrumb-item.active,
            .proveedores-view .breadcrumb-item.active strong {
                color: var(--proveedores-text);
            }

            .proveedores-view .proveedores-create-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 7px;
                padding: 9px 16px;
                border: 0;
                border-radius: 7px;
                background: linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
                color: #fff;
                font-size: 13px;
                font-weight: 600;
                box-shadow: 0 3px 9px rgba(230, 126, 34, .2);
                transition: background .15s ease, transform .15s ease;
            }

            .proveedores-view .proveedores-create-btn:hover {
                background: linear-gradient(135deg, #e08e0b 0%, #c04e00 100%);
                color: #fff;
                transform: translateY(-1px);
            }

            .proveedores-view .wrapper-content {
                padding: 22px 24px;
                background: var(--proveedores-bg);
            }

            .proveedores-view .ibox {
                overflow: visible;
                border: 1px solid var(--proveedores-border);
                border-radius: 10px;
                box-shadow: 0 3px 14px rgba(45, 62, 80, .05);
            }

            .proveedores-view .ibox-content {
                padding: 22px;
            }

            .proveedores-view .dataTables_wrapper {
                color: var(--proveedores-text);
                font-size: 13px;
            }

            .proveedores-view .dataTables_length,
            .proveedores-view .dataTables_filter {
                padding: 8px 10px;
                margin-bottom: 14px;
                border: 1px solid rgba(230, 126, 34, .22);
                border-radius: 6px;
                background: linear-gradient(90deg, rgba(236, 64, 27, .05) 0%, rgba(241, 85, 51, .03) 100%);
                color: var(--proveedores-muted);
            }

            .proveedores-view .dataTables_length label,
            .proveedores-view .dataTables_filter label {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                margin: 0;
            }

            .proveedores-view .dataTables_length select,
            .proveedores-view .dataTables_filter input {
                min-height: 36px;
                padding: 6px 10px;
                border: 1px solid var(--proveedores-border);
                border-radius: 6px;
                outline: none;
                background: #fff;
            }

            .proveedores-view .dataTables_filter input:focus,
            .proveedores-view .dataTables_length select:focus {
                border-color: var(--proveedores-orange);
                box-shadow: 0 0 0 3px rgba(230, 126, 34, .2);
            }

            .proveedores-view .dt-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                margin: 0 0 14px;
            }

            .proveedores-view .dt-buttons .btn {
                padding: 6px 11px;
                border: 1px solid var(--proveedores-border);
                border-radius: 6px;
                background: #fff;
                color: #586a75;
                font-size: 12px;
                box-shadow: none;
            }

            .proveedores-view .dt-buttons .btn:hover {
                border-color: var(--proveedores-orange);
                color: var(--proveedores-table-text);
            }

            .proveedores-view #tbl_proveedoresListar {
                width: 100% !important;
                margin: 0 !important;
                border: 1px solid var(--proveedores-border);
                border-radius: 8px;
                overflow: visible;
            }

            .proveedores-view #tbl_proveedoresListar thead th {
                padding: 12px 10px;
                border-color: var(--proveedores-table-border);
                border-bottom: 2px solid var(--proveedores-table-border);
                background: var(--proveedores-table-bg);
                color: var(--proveedores-table-text);
                font-size: 12px;
                font-weight: 700;
                white-space: nowrap;
            }

            .proveedores-view #tbl_proveedoresListar tbody td {
                padding: 10px;
                border-color: var(--proveedores-border);
                color: #4b5563;
                font-size: 13px;
                vertical-align: middle;
            }

            .proveedores-view #tbl_proveedoresListar tbody tr:hover {
                background: #fffcf5;
            }

            .proveedores-view .dataTables_info {
                padding-top: 14px;
                color: var(--proveedores-muted);
            }

            .proveedores-view .dataTables_paginate {
                padding-top: 10px;
            }

            .proveedores-view .dataTables_paginate .paginate_button {
                border-radius: 5px !important;
            }

            .proveedores-view .dataTables_paginate .paginate_button.current,
            .proveedores-view .dataTables_paginate .paginate_button.current:hover {
                border-color: var(--proveedores-primary) !important;
                background: linear-gradient(135deg, #f39c12 0%, #e05a00 100%) !important;
                color: #fff !important;
            }

            .proveedores-view .modal {
                z-index: 5000 !important;
            }

            body.proveedor-modal-open .modal-backdrop {
                z-index: 4990 !important;
            }

            .proveedores-view .modal-dialog {
                width: calc(100% - 32px);
                max-width: 860px;
                margin: 24px auto;
            }

            .proveedores-view .modal-content {
                display: flex;
                flex-direction: column;
                max-height: calc(100vh - 48px);
                overflow: hidden;
                border: 0;
                border-radius: 12px;
                box-shadow: 0 18px 50px rgba(35, 50, 60, .2);
            }

            .proveedores-view .modal-header {
                padding: 16px 22px;
                border-bottom: 0;
                background: linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
            }

            .proveedores-view .modal-header .modal-title,
            .proveedores-view .modal-header .close {
                color: #fff !important;
            }

            .proveedores-view .modal-header .modal-title {
                font-weight: 600;
            }

            .proveedores-view .modal-header .close {
                opacity: .9;
                text-shadow: none;
            }

            .proveedores-view .modal-body {
                padding: 22px;
                overflow-y: auto;
                background: #fffcf8;
            }

            .proveedores-view .modal-body label {
                color: #6d4c1e;
                font-size: 12px;
                font-weight: 600;
            }

            .proveedores-view .modal-body .form-control {
                min-height: 38px;
                border: 1px solid var(--proveedores-border);
                border-radius: 6px;
                box-shadow: none;
            }

            .proveedores-view .modal-body .form-control:focus {
                border-color: var(--proveedores-orange);
                box-shadow: 0 0 0 3px rgba(230, 126, 34, .2);
            }

            .proveedores-view .proveedor-form-intro {
                margin-bottom: 20px;
                color: var(--proveedores-muted);
                font-size: 13px;
            }

            .proveedores-view .proveedor-section {
                min-width: 0;
                padding: 18px 20px 4px;
                margin: 0 0 20px;
                border: 1px solid #efdfcc;
                border-radius: 10px;
                background: #fff;
            }

            .proveedores-view .proveedor-section legend {
                display: flex;
                align-items: center;
                gap: 10px;
                width: auto;
                padding: 0 8px;
                margin: 0;
                color: #7d3f00;
                font-size: 15px;
                font-weight: 700;
            }

            .proveedores-view .proveedor-section-number {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 30px;
                height: 30px;
                border-radius: 9px;
                background: #fdf4e7;
                color: #c45a00;
                font-size: 12px;
            }

            .proveedores-view .proveedor-optional {
                margin-left: 4px;
                color: #939393;
                font-size: 10px;
                font-weight: 400;
            }

            .proveedores-view .proveedor-field-heading {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 4px 10px;
                margin-bottom: 6px;
            }

            .proveedores-view .proveedor-field-heading label {
                margin: 0;
            }

            .proveedores-view .proveedor-no-registro {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                cursor: pointer;
                white-space: nowrap;
            }

            .proveedores-view .proveedor-no-registro input {
                accent-color: var(--proveedores-orange);
            }

            .proveedores-view .proveedor-no-registro input:checked {
                background-color: var(--proveedores-orange);
                border-color: var(--proveedores-orange);
                color: var(--proveedores-orange);
            }

            .proveedores-view .proveedor-no-registro input:focus {
                --tw-ring-color: rgba(230, 126, 34, .3);
            }

            .proveedores-view .form-control:disabled {
                background: #f5f2ed;
                color: #777;
            }

            .proveedores-view .modal-footer {
                flex-shrink: 0;
                padding: 14px 22px;
                border-top: 1px solid #efdfcc;
                background: #fff;
            }

            .proveedores-view .modal-footer .btn-primary {
                border: 0;
                background: linear-gradient(135deg, #f39c12, #e05a00);
                color: #fff;
            }

            .proveedores-view .modal-footer .btn-primary:hover {
                background: linear-gradient(135deg, #e08e0b, #c04e00);
            }

            .proveedores-view .modal-header {
                flex-shrink: 0;
            }

            @media (max-width: 767px) {
                .proveedores-view .page-heading,
                .proveedores-view .wrapper-content {
                    padding-right: 15px;
                    padding-left: 15px;
                }

                .proveedores-view .page-heading > div:last-child {
                    padding-top: 12px;
                    text-align: left;
                }

                .proveedores-view .ibox-content {
                    padding: 14px;
                }

                .proveedores-view .dataTables_filter {
                    text-align: left;
                }
            }
        </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2><i class="fa fa-truck"></i>Proveedores</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active"><strong>Proveedores</strong></li>
            </ol>
        </div>

        <div class="col-lg-4 col-xl-2 col-md-4 col-sm-4">
            <a href="#" class="btn proveedores-create-btn" data-toggle="modal" data-target="#modal_proveedores_crear">
                <i class="fa fa-plus"></i> Registrar proveedor
            </a>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">

                        <div class="table-responsive">
                            <table id="tbl_proveedoresListar" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Dirección</th>
                                        <th>Teléfono</th>
                                        <th>Correo</th>
                                        <th>RTN</th>
                                        <th>Retención 1%</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>




    <!---MODAL PARA CREAR PROVEEDOR----->
    <div id="modal_proveedores_crear" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="tituloCrearProveedor" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="tituloCrearProveedor" class="modal-title"><i class="fa fa-truck mr-2"></i>Registrar proveedor</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="proveedorCreacionForm" name="proveedorCreacionForm" data-parsley-validate>
                        <input type="hidden" name="_token" value="{!! csrf_token() !!}">
                        @include('livewire.partials.proveedor-form', ['editar' => false])
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" form="proveedorCreacionForm"><i class="fa fa-check mr-1"></i>Crear proveedor</button>
                </div>
            </div>
        </div>
    </div>

    <!------MODAL PARA EDITAR PROVEEDOR--->
    <div id="modal_proveedores_editar" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="tituloEditarProveedor" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="tituloEditarProveedor" class="modal-title"><i class="fa fa-edit mr-2"></i>Editar proveedor</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="proveedorEditarForm" name="proveedorEditarForm" data-parsley-validate>
                        <input type="hidden" name="_token" value="{!! csrf_token() !!}">
                        <input type="hidden" id="idProveedor" name="idProveedor" value="0" data-parsley-required>
                        @include('livewire.partials.proveedor-form', ['editar' => true])
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" form="proveedorEditarForm"><i class="fa fa-check mr-1"></i>Guardar cambios</button>
                </div>
            </div>
        </div>
    </div>






</div>

@push('scripts')
    <script src="{{ asset('js/plugins/iCheck/icheck.min.js') }}"></script>
    <script src="{{ asset('js/js_proyecto/banco-proveedores/proveedores.js') }}?v={{ filemtime(public_path('js/js_proyecto/banco-proveedores/proveedores.js')) }}"></script>
@endpush
