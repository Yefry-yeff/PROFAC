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
        /* Asegurar buen padding del texto y clear dentro del select */
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: 28px;         /* ya lo tenías */
        padding-left: 0.5rem;      /* añade espacio para el texto */
        padding-right: 2rem;       /* deja espacio para el botón clear */
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__clear {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        }

        /* =============================
        Encabezado y tarjetas
        ============================= */
        .page-heading, .d-flex.bg-light {
            background-color: #f8f9fa;
            border-radius: 0.35rem;
            padding: 0.5rem 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        /* =============================
        Select2 — todos los selects
        ============================= */
        .select2-container {
            z-index: 999 !important; /* Siempre encima de modales */
            width: 100% !important;   /* Ocupa todo el ancho del contenedor */
            font-size: 0.9rem;
        }

        .select2-dropdown {
            z-index: 3050 !important;
            max-height: 200px; /* Scroll si hay muchos items */
            overflow-y: auto;
        }

        /* Select2 estilo Bootstrap 4 */
        .select2-container--bootstrap4 .select2-selection--single {
            height: 38px;          /* Altura igual a inputs grandes */
            padding: 6px 12px;
            border-radius: 0.35rem;
            border: 1px solid #ced4da;
        }

        .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 28px;     /* Ajuste vertical del texto */
        }

        .select2-container--bootstrap4 .select2-selection__arrow {
            height: 34px;
            right: 8px;
        }

        /* Placeholder gris más suave */
        .select2-container--bootstrap4 .select2-selection__placeholder {
            color: #6c757d;
        }

        /* =============================
        Botones
        ============================= */
        .btn-success, .btn-primary, .btn-outline-secondary {
            font-weight: 500;
            padding: 0.35rem 0.9rem;
            border-radius: 0.35rem;
        }

        /* =============================
        Contenedor de filtros (selects + botón)
        ============================= */
        .filtro-container {
            display: flex;
            flex-wrap: wrap;        /* Para que en móviles se acomoden */
            gap: 0.5rem;            /* Espacio entre elementos */
            align-items: center;
        }

        .filtro-select {
            min-width: 200px;
            flex: 1 1 220px;       /* Crece hasta 220px */
            height: 38px;          /* Altura uniforme */
        }

        #btnDescargar {
            height: 38px;
            flex: 0 0 auto;        /* Botón no se encoge */
        }

        /* =============================
        Inputs y textareas
        ============================= */
        textarea.form-control, input.form-control {
            border-radius: 0.35rem;
            border: 1px solid #ced4da;
            font-size: 0.9rem;
            padding: 6px 12px;
        }

        /* =============================
        Select2 dentro de modales
        ============================= */
        .modal .select2-container {
            width: 100% !important;  /* Siempre ocupa todo el ancho de la columna */
        }

        /* =============================
        Responsivo móvil (≤576px)
        ============================= */
        @media (max-width: 576px) {
            /* Contenedor general de formularios */
            form.d-flex {
                flex-direction: column;
            }

            form.d-flex > * {
                margin-bottom: 0.5rem;
            }

            /* Filtros dentro de header */
            .filtro-container {
                flex-direction: column;
                margin-left: 0;       /* Quitar alineación a la derecha */
                gap: 0.5rem;
            }

            #btnDescargar {
                width: 100%;          /* Botón ocupa todo el ancho */
            }

            /* Card header: cada div y botón ocupa 100% */
            .card-header .d-flex > div,
            .card-header .d-flex > button {
                width: 100%;
            }

            .card-header .d-flex {
                flex-direction: column;
                gap: 0.5rem;
            }
        }

        /* =============================
        Pantallas grandes (≥992px)
        ============================= */
        @media (min-width: 992px) {
            .filtro-select {
                min-width: 240px;
                flex: 1 1 240px;
            }
        }

    </style>
@endpush
<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-light py-2 d-flex flex-wrap align-items-center justify-content-between">
        <h3 class="mb-2 mb-md-0"><b>Reporte de precios de productos</b></h3>
        <p class="mb-2 mb-md-0" >El reporte arrojará solo los productos activos y que coincidan con los filtros puestos a disposición.</p>

    </div>
    <div class="card-body p-2 text-center">

        <form id="formExportFiltrado" method="GET" action="{{ route('excel.productos.filtros') }}"
            class="d-flex flex-wrap align-items-center filtro-container">

            <!-- Tipo de filtro -->
            <div class="filtro-item">
                <select id="tipoFiltro" name="tipoFiltro" class="form-control select2bs4 filtro-select">
                    <option value="">📂 Formato</option>
                    <option value="1">🏷️ Marca</option>
                    <option value="2">📂 Categoría</option>
                </select>
            </div>

            <!-- Lista de filtro -->
            <div class="filtro-item">
                <select id="listaTipoFiltro" name="listaTipoFiltro" class="form-control select2bs4 filtro-select">
                    <option value="">Seleccione una opción</option>
                </select>
            </div>

            <!-- Categoria de precios -->
            <div class="filtro-item">
                <select id="listaTipoFiltroCatPrecios" name="listaTipoFiltroCatPrecios"
                        class="form-control select2bs4 filtro-select" required>
                    <option value="">Seleccione Categoría de precio</option>
                </select>
            </div>

            <!-- Botón alineado a la derecha SIEMPRE -->
            <div class="filtro-item ms-auto flex-grow-1 d-flex justify-content-end">
                <button type="submit" class="btn btn-success" id="btnDescargar">
                    📥 Descargar reporte
                </button>
            </div>

        </form>

    </div>
</div>

<!-- Tabla de productos -->
<div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-light py-2">
        <h5 class="mb-0"><b>📋 Listado de Productos Activos</b></h5>
    </div>
    <div class="card-body p-2">
        <div class="table-responsive">
            <table id="tbl_productos" class="table table-striped table-bordered table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th>Categoría</th>
                        <th>Escala Precio</th>
                        <th>Cat. Cliente</th>
                        <th>Precio A</th>
                        <th>Precio B</th>
                        <th>Precio C</th>
                        <th>Precio D</th>
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
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <script src="{{ asset('js/js_proyecto/Escalas/reporteEscalas.js') }}"></script>
@endpush


