@push('styles')
    <style>
        .select2-dropdown { scroll-behavior: smooth; }
        .select2-hidden-accessible {
            border: 0 !important;
            clip: rect(0 0 0 0) !important;
            height: 1px !important;
            margin: -1px !important;
            overflow: hidden !important;
            padding: 0 !important;
            position: absolute !important;
            width: 1px !important;
        }
        
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: 28px;
            padding-left: 0.5rem;
            padding-right: 2rem;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__clear {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .page-heading, .d-flex.bg-light {
            background-color: #f8f9fa;
            border-radius: 0.35rem;
            padding: 0.5rem 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .select2-container {
            z-index: 999 !important;
            width: 100% !important;
            font-size: 0.9rem;
        }

        .select2-dropdown {
            z-index: 3050 !important;
            max-height: 200px;
            overflow-y: auto;
        }

        .select2-container--bootstrap4 .select2-selection--single {
            height: 38px;
            padding: 6px 12px;
            border-radius: 0.35rem;
            border: 1px solid #ced4da;
        }

        .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 28px;
        }

        .select2-container--bootstrap4 .select2-selection__arrow {
            height: 34px;
            right: 8px;
        }

        .select2-container--bootstrap4 .select2-selection__placeholder {
            color: #6c757d;
        }

        .btn-success, .btn-primary, .btn-outline-secondary {
            font-weight: 500;
            padding: 0.35rem 0.9rem;
            border-radius: 0.35rem;
        }

        .filtro-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }

        .filtro-select, .filtro-input {
            min-width: 150px;
            flex: 1 1 150px;
            height: 38px;
        }

        .btn-filtrar {
            height: 38px;
            flex: 0 0 auto;
        }

        textarea.form-control, input.form-control {
            border-radius: 0.35rem;
            border: 1px solid #ced4da;
            font-size: 0.9rem;
            padding: 6px 12px;
        }

        .modal .select2-container {
            width: 100% !important;
        }

        @media (max-width: 576px) {
            form.d-flex {
                flex-direction: column;
            }

            form.d-flex > * {
                margin-bottom: 0.5rem;
            }

            .filtro-container {
                flex-direction: column;
                margin-left: 0;
                gap: 0.5rem;
            }

            .btn-filtrar {
                width: 100%;
            }

            .card-header .d-flex > div,
            .card-header .d-flex > button {
                width: 100%;
            }

            .card-header .d-flex {
                flex-direction: column;
                gap: 0.5rem;
            }
        }

        @media (min-width: 992px) {
            .filtro-select, .filtro-input {
                min-width: 180px;
                flex: 1 1 180px;
            }
        }
    </style>
@endpush

<!-- Filtros Generales -->
<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-primary text-white py-2">
        <h5 class="mb-0"><b>📊 FILTROS GENERALES DE REPORTES</b></h5>
    </div>
    <div class="card-body p-3">
        <div class="d-flex flex-wrap align-items-center filtro-container">
            <!-- Tipo de Reporte -->
            <div class="filtro-item">
                <label class="small mb-1">Tipo de Reporte</label>
                <select id="tipoReporte" class="form-control select2bs4 filtro-select">
                    <option value="">Seleccione tipo...</option>
                    <option value="empleado">👤 Por Empleado</option>
                    <option value="rol">👥 Por Rol</option>
                    <option value="usuarios">🔐 General de Usuarios</option>
                    <option value="productos">📦 General por Producto</option>
                    <option value="facturas">🧾 General por Factura</option>
                </select>
            </div>

            <!-- Fecha Inicio -->
            <div class="filtro-item">
                <label class="small mb-1">Fecha Inicio</label>
                <input type="date" id="fechaInicio" class="form-control filtro-input">
            </div>

            <!-- Fecha Fin -->
            <div class="filtro-item">
                <label class="small mb-1">Fecha Fin</label>
                <input type="date" id="fechaFin" class="form-control filtro-input">
            </div>

            <!-- Selector Específico (Empleado/Rol) -->
            <div class="filtro-item" id="containerFiltroEspecifico" style="display: none;">
                <label class="small mb-1" id="labelFiltroEspecifico">Seleccionar</label>
                <select id="filtroEspecifico" class="form-control select2bs4 filtro-select"></select>
            </div>

            <!-- Botones -->
            <div class="filtro-item ms-auto d-flex gap-2">
                <button type="button" id="btnFiltrar" class="btn btn-primary btn-filtrar">
                    🔍 Filtrar
                </button>
                <button type="button" id="btnDescargar" class="btn btn-success btn-filtrar">
                    📥 Descargar Excel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Resultados -->
<div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-light py-2">
        <h5 class="mb-0"><b>📋 <span id="tituloTabla">Resultados</span></b></h5>
    </div>
    <div class="card-body p-2">
        <div class="table-responsive">
            <table id="tbl_comisiones" class="table table-striped table-bordered table-hover" style="width:100%">
                <thead id="theadComisiones">
                    <tr>
                        <th>ID</th>
                        <th>Información</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Se llena dinámicamente con DataTables -->
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/js_proyecto/comisiones/Escalado/reportesGeneral.js') }}"></script>
@endpush


