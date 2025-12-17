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
        <h3 class="mb-2 mb-md-0"><b>Comisiones por empleado</b></h3>
    </div>
    <div class="card-body p-2 text-center">

        <form id="formExportFiltrado" method="GET" action="{{ route('excel.productos.filtros') }}"
            class="d-flex flex-wrap align-items-center filtro-container">

            <div class="filtro-item">
                <select id="empleado" name="empleado" class="form-control select2bs4 filtro-select">
                </select>
            </div>

            <!-- Botón alineado a la derecha SIEMPRE -->
            <div class="filtro-item ms-auto flex-grow-1 d-flex justify-content-end">
                <button type="submit" class="btn btn-success" id="btnDescargar">
                    📥 Descargar
                </button>
            </div>

        </form>

    </div>
</div>

<hr>
<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-light py-2 d-flex flex-wrap align-items-center justify-content-between">
        <h3 class="mb-2 mb-md-0"><b>Comisiones por rol de usuarios</b></h3>
    </div>
    <div class="card-body p-2 text-center">

        <form id="formExportFiltrado" method="GET" action="{{ route('excel.productos.filtros') }}"
            class="d-flex flex-wrap align-items-center filtro-container">

            <div class="filtro-item">
                <select id="rol" name="rol" class="form-control select2bs4 filtro-select">
                </select>
            </div>

            <!-- Botón alineado a la derecha SIEMPRE -->
            <div class="filtro-item ms-auto flex-grow-1 d-flex justify-content-end">
                <button type="submit" class="btn btn-success" id="btnDescargar">
                    📥 Descargar
                </button>
            </div>

        </form>

    </div>
</div>

<hr>
<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-light py-2 d-flex flex-wrap align-items-center justify-content-between">
        <h3 class="mb-2 mb-md-0"><b>Comisiones por facturas cerradas</b></h3>
        <p>Solo aplica para facturas que están pagadas en su totalidad.</p>
    </div>
    <div class="card-body p-2 text-center">

        <form id="formExportFiltrado" method="GET" action="{{ route('excel.productos.filtros') }}"
            class="d-flex flex-wrap align-items-center filtro-container">

            <div class="filtro-item">
                <select id="rol_id" name="rol_id" class="form-control select2bs4 filtro-select">
                    <option value="">📂 Formato</option>
                    <option value="1">🏷️ Marca</option>
                    <option value="2">📂 Categoría</option>
                </select>
            </div>

            <!-- Botón alineado a la derecha SIEMPRE -->
            <div class="filtro-item ms-auto flex-grow-1 d-flex justify-content-end">
                <button type="submit" class="btn btn-success" id="btnDescargar">
                    📥 Descargar
                </button>
            </div>

        </form>

    </div>
</div>

<hr>
<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-light py-2 d-flex flex-wrap align-items-center justify-content-between">
        <h3 class="mb-2 mb-md-0"><b>Comisiones por productos</b></h3>
        <p>Solo aplica para productos que ya tienen una comisión por factura cerrada.</p>
    </div>
    <div class="card-body p-2 text-center">

        <form id="formExportFiltrado" method="GET" action="{{ route('excel.productos.filtros') }}"
            class="d-flex flex-wrap align-items-center filtro-container">

            <div class="filtro-item">
                <select id="rol_id" name="rol_id" class="form-control select2bs4 filtro-select">
                    <option value="">📂 Formato</option>
                    <option value="1">🏷️ Marca</option>
                    <option value="2">📂 Categoría</option>
                </select>
            </div>

            <!-- Botón alineado a la derecha SIEMPRE -->
            <div class="filtro-item ms-auto flex-grow-1 d-flex justify-content-end">
                <button type="submit" class="btn btn-success" id="btnDescargar">
                    📥 Descargar
                </button>
            </div>

        </form>

    </div>
</div>



@push('scripts')
    <script src="{{ asset('js/js_proyecto/comisiones/Escalado/reportesGeneral.js') }}"></script>
@endpush


