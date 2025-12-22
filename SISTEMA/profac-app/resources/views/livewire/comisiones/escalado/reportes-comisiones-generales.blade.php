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
            max-height: 300px;
            overflow-y: auto;
            border-radius: 0.35rem;
        }

        .select2-container--bootstrap4 .select2-selection--single {
            height: 40px !important;
            padding: 0 !important;
            border-radius: 0.35rem;
            border: 1px solid #ced4da;
            transition: all 0.2s ease;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container--bootstrap4 .select2-selection--single:focus,
        .select2-container--bootstrap4.select2-container--focus .select2-selection--single {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }

        .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 40px !important;
            padding-left: 12px !important;
            padding-right: 30px !important;
            display: block !important;
            width: 100% !important;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .select2-container--bootstrap4 .select2-selection__arrow {
            height: 40px !important;
            position: absolute !important;
            top: 0 !important;
            right: 8px !important;
            width: 20px !important;
        }

        .select2-container--bootstrap4 .select2-selection__arrow b {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .select2-container--bootstrap4 .select2-selection__placeholder {
            color: #6c757d;
            line-height: 40px !important;
        }

        .select2-container--bootstrap4 .select2-selection__clear {
            position: absolute;
            right: 30px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
        }

        /* Asegurar que el contenedor tenga la altura correcta */
        .select2-container {
            display: block !important;
        }

        .select2-container .select2-selection {
            position: relative;
        }

        .btn-success, .btn-primary, .btn-outline-secondary {
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.35rem;
            transition: all 0.2s ease;
        }

        .btn-filtrar:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0,0,0,0.15);
        }

        .filtro-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: end;
        }

        .filtro-item {
            flex: 1 1 220px;
            min-width: 220px;
        }

        .filtro-item label {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
            margin-bottom: 0.4rem;
            display: block;
        }

        .filtro-item label i {
            margin-right: 5px;
        }

        .form-control {
            border-radius: 0.35rem;
            border: 1px solid #ced4da;
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
            height: 40px;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }

        .btn-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: auto;
        }

        @media (max-width: 768px) {
            .filtro-container {
                flex-direction: column;
                gap: 0.75rem;
            }

            .filtro-item {
                min-width: 100%;
                flex: 1 1 100%;
            }

            .btn-actions {
                flex-direction: column;
                width: 100%;
            }

            .btn-filtrar {
                width: 100%;
            }
        }

        @media (min-width: 992px) {
            .filtro-item {
                min-width: 200px;
                flex: 1 1 200px;
            }
        }
    </style>
@endpush

<!-- Filtros Generales -->
<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-light py-2">
        <h5 class="mb-0"><b>🔍 FILTROS DE REPORTES DE COMISIONES</b></h5>
    </div>
    <div class="card-body p-3">
        <div class="filtro-container">
            <!-- Tipo de Reporte -->
            <div class="filtro-item">
                <label for="tipoReporte">
                    <i class="fas fa-chart-bar"></i>Tipo de Reporte
                </label>
                <select id="tipoReporte" class="form-control select2bs4">
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
                <label for="fechaInicio">
                    <i class="fas fa-calendar-alt"></i>Fecha Inicio
                </label>
                <input type="date" id="fechaInicio" class="form-control" required>
            </div>

            <!-- Fecha Fin -->
            <div class="filtro-item">
                <label for="fechaFin">
                    <i class="fas fa-calendar-check"></i>Fecha Fin
                </label>
                <input type="date" id="fechaFin" class="form-control" required>
            </div>

            <!-- Selector Específico (Empleado/Rol) -->
            <div class="filtro-item" id="containerFiltroEspecifico" style="display: none;">
                <label for="filtroEspecifico" id="labelFiltroEspecifico">
                    <i class="fas fa-user-check"></i>Seleccionar
                </label>
                <select id="filtroEspecifico" class="form-control select2bs4"></select>
            </div>

            <!-- Botones de Acción -->
            <div class="filtro-item ms-auto">
                <div class="btn-actions">
                    <button type="button" id="btnFiltrar" class="btn btn-primary btn-filtrar">
                        <i class="fas fa-search me-1"></i>Generar Reporte
                    </button>
                    <button type="button" id="btnDescargar" class="btn btn-success btn-filtrar">
                        <i class="fas fa-file-excel me-1"></i>Descargar Excel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Resultados -->
<div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-light py-2">
        <h5 class="mb-0"><b>📋 <span id="tituloTabla">Resultados del Reporte</span></b></h5>
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
