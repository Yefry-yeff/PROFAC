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
        
        .filtro-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
        }

        .filtro-body {
            background: white;
            border-radius: 0 0 15px 15px;
        }

        .filtro-item {
            flex: 1 1 200px;
            min-width: 200px;
        }

        .filtro-item label {
            font-weight: 600;
            color: #4a5568;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control, .select2-container--bootstrap4 .select2-selection--single {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            height: 42px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
            padding-left: 12px;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: 38px;
        }

        .btn-filtrar {
            height: 42px;
            min-width: 140px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-filtrar:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
        }

        .tabla-card {
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .tabla-card .card-header {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border-radius: 15px 15px 0 0;
            border-bottom: 3px solid #667eea;
        }

        #tbl_comisiones {
            border-radius: 10px;
            overflow: hidden;
        }

        #tbl_comisiones thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px 10px;
        }

        #tbl_comisiones tbody tr {
            transition: all 0.2s ease;
        }

        #tbl_comisiones tbody tr:hover {
            background-color: #f7fafc;
            transform: scale(1.01);
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-dropdown {
            z-index: 3050 !important;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
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
    </style>
@endpush

<!-- Filtros Generales -->
<div class="card filtro-card shadow-lg border-0 mb-4">
    <div class="card-header text-white py-3 border-0">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-filter me-2"></i>FILTROS DE REPORTES DE COMISIONES
        </h5>
    </div>
    <div class="card-body filtro-body p-4">
        <div class="row g-3">
            <!-- Tipo de Reporte -->
            <div class="col-lg-3 col-md-6">
                <div class="filtro-item">
                    <label for="tipoReporte">
                        <i class="fas fa-chart-bar text-primary me-1"></i>Tipo de Reporte
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
            </div>

            <!-- Fecha Inicio -->
            <div class="col-lg-3 col-md-6">
                <div class="filtro-item">
                    <label for="fechaInicio">
                        <i class="fas fa-calendar-alt text-success me-1"></i>Fecha Inicio
                    </label>
                    <input type="date" id="fechaInicio" class="form-control" required>
                </div>
            </div>

            <!-- Fecha Fin -->
            <div class="col-lg-3 col-md-6">
                <div class="filtro-item">
                    <label for="fechaFin">
                        <i class="fas fa-calendar-check text-danger me-1"></i>Fecha Fin
                    </label>
                    <input type="date" id="fechaFin" class="form-control" required>
                </div>
            </div>

            <!-- Selector Específico (Empleado/Rol) -->
            <div class="col-lg-3 col-md-6" id="containerFiltroEspecifico" style="display: none;">
                <div class="filtro-item">
                    <label for="filtroEspecifico" id="labelFiltroEspecifico">
                        <i class="fas fa-user-check text-info me-1"></i>Seleccionar
                    </label>
                    <select id="filtroEspecifico" class="form-control select2bs4"></select>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="btn-actions d-flex gap-3 justify-content-end">
                    <button type="button" id="btnFiltrar" class="btn btn-primary btn-filtrar">
                        <i class="fas fa-search me-2"></i>Generar Reporte
                    </button>
                    <button type="button" id="btnDescargar" class="btn btn-success btn-filtrar">
                        <i class="fas fa-file-excel me-2"></i>Exportar Excel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Resultados -->
<div class="card tabla-card shadow border-0">
    <div class="card-header py-3">
        <h5 class="mb-0 fw-bold text-dark">
            <i class="fas fa-table me-2 text-primary"></i><span id="tituloTabla">Resultados del Reporte</span>
        </h5>
    </div>
    <div class="card-body p-3">
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
