<div>
    @push('styles')
    <style>
        /* =============================================
           DETALLE DE PRODUCTO — ESTILOS MODERNOS
        ============================================= */

        /* ── Page header ── */
        .det-page-header {
            background: linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
            padding: 22px 28px 18px;
            border-bottom: 3px solid rgba(255,255,255,.25);
        }
        .det-page-header .prod-name {
            color: #fff;
            font-size: 1.55rem;
            font-weight: 700;
            margin: 0 0 4px;
            letter-spacing: .3px;
        }
        .det-page-header .prod-name small {
            font-size: .85rem;
            font-weight: 400;
            color: rgba(255,255,255,.55);
            margin-left: 8px;
        }
        .det-page-header .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0 0 14px;
            font-size: .8rem;
        }
        .det-page-header .breadcrumb-item a,
        .det-page-header .breadcrumb-item.active { color: rgba(255,255,255,.6); }
        .det-page-header .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,.35); }
        .det-header-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn-det-edit {
            background: rgba(255,255,255,.18);
            color: #fff; border: 1.5px solid rgba(255,255,255,.6); border-radius: 8px;
            padding: 8px 18px; font-weight: 600; font-size: .85rem;
            transition: all .2s;
        }
        .btn-det-edit:hover { background: rgba(255,255,255,.30); color: #fff; }
        .btn-det-foto {
            background: rgba(255,255,255,.10);
            color: #fff; border: 1.5px solid rgba(255,255,255,.45); border-radius: 8px;
            padding: 8px 18px; font-weight: 600; font-size: .85rem;
            transition: all .2s;
        }
        .btn-det-foto:hover { background: rgba(255,255,255,.25); color: #fff; }

        /* ── Body wrapper ── */
        .det-body { padding: 20px 24px; }
        @media (max-width: 767px) { .det-body { padding: 14px 12px; } }

        /* ── Carousel card ── */
        .det-carousel-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 3px 16px rgba(0,0,0,.09);
            overflow: hidden;
            height: 100%;
        }
        .det-carousel-card .card-header-bar {
            background: linear-gradient(135deg, #f39c12, #e05a00);
            padding: 11px 18px;
            display: flex; align-items: center; gap: 8px;
        }
        .det-carousel-card .card-header-bar span { color: #fff; font-weight: 600; font-size: .9rem; }
        .det-carousel-inner { padding: 16px; }
        #carouselExampleBigIndicators .carousel-item img {
            max-height: 320px;
            width: 100%;
            object-fit: contain;
            border-radius: 10px;
            background: #f8fafc;
        }
        #carouselExampleBigIndicators .carousel-control-prev,
        #carouselExampleBigIndicators .carousel-control-next {
            width: 42px; height: 42px;
            background: rgba(15,52,96,.85);
            border-radius: 50%;
            top: 50%; transform: translateY(-50%);
            opacity: 1;
        }
        #carouselExampleBigIndicators .carousel-control-prev { left: 8px; }
        #carouselExampleBigIndicators .carousel-control-next { right: 8px; }
        #carouselExampleBigIndicators .carousel-control-prev i,
        #carouselExampleBigIndicators .carousel-control-next i {
            font-size: 1.3rem !important;
            color: #fff !important;
        }
        #carouselExampleBigIndicators .carousel-indicators li {
            background-color: #e05a00;
            border-radius: 50%;
            width: 9px; height: 9px;
        }
        .no-photos-placeholder {
            text-align: center; padding: 50px 20px; color: #ccc;
        }
        .no-photos-placeholder i { font-size: 3rem; display: block; margin-bottom: 10px; }
        .btn-eliminar-img {
            background: #fdecea; color: #c0392b;
            border: 1px solid #f5c6cb;
            border-radius: 20px; padding: 4px 14px;
            font-size: .78rem; font-weight: 600;
            transition: all .2s; display: inline-block; margin-bottom: 10px;
        }
        .btn-eliminar-img:hover { background: #c0392b; color: #fff; }

        /* ── Info cards ── */
        .det-info-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 3px 16px rgba(0,0,0,.09);
            overflow: hidden;
            margin-bottom: 18px;
        }
        .det-info-card .card-header-bar {
            background: linear-gradient(135deg, #f39c12, #e05a00);
            padding: 9px 16px;
            display: flex; align-items: center; gap: 8px;
        }
        .det-info-card .card-header-bar span { color: #fff; font-weight: 600; font-size: .85rem; }
        .det-info-card .card-body-inner { padding: 9px 14px; }

        .info-row {
            display: flex; align-items: baseline;
            padding: 2px 0;
            border-bottom: 1px solid #f5f5f5;
            font-size: .8rem;
            gap: 8px;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label {
            min-width: 125px;
            color: #aaa;
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            flex-shrink: 0;
        }
        .info-value { color: #2d1600; font-weight: 500; font-size: .8rem; }
        .info-section-title {
            font-size: .63rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .6px; color: #e05a00;
            border-bottom: 1.5px solid #fde8cc;
            padding-bottom: 2px; margin: 6px 0 1px;
            display: flex; align-items: center; gap: 5px;
        }
        .info-section-title:first-child { margin-top: 0; }
        .info-badge-id {
            background: linear-gradient(135deg, #f39c12, #e05a00); color: #fff;
            border-radius: 6px; padding: 2px 10px;
            font-size: .82rem; font-weight: 700;
        }

        /* ISV badge en info */
        .isv-chip-exento { background:#d5f5e3; color:#1e8449; border-radius:20px; padding:3px 11px; font-size:.78rem; font-weight:700; }
        .isv-chip-15     { background:#fef9e7; color:#d35400; border-radius:20px; padding:3px 11px; font-size:.78rem; font-weight:700; }
        .isv-chip-18     { background:#fdecea; color:#c0392b; border-radius:20px; padding:3px 11px; font-size:.78rem; font-weight:700; }

        /* ── Price cards ── */
        .price-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        .price-card {
            background: #f8fafc;
            border: 1.5px solid #e8ecef;
            border-radius: 12px;
            padding: 14px 12px;
            text-align: center;
            transition: all .2s;
        }
        .price-card:hover { border-color: #e05a00; background: #fdf4e7; }
        .price-card .pc-label {
            font-size: .7rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .5px;
            color: #888; margin-bottom: 4px;
        }
        .price-card .pc-badge {
            display: inline-block;
            background: linear-gradient(135deg, #f39c12, #e05a00); color: #fff;
            border-radius: 5px; padding: 2px 8px;
            font-size: .75rem; font-weight: 700;
            margin-bottom: 8px;
        }
        .price-card .pc-value {
            font-size: 1.1rem; font-weight: 700; color: #3a1800;
            display: block;
        }
        .price-card .pc-currency { font-size: .72rem; color: #888; margin-top: 2px; }
        .price-card-base { border-color: #e08e0b; }
        .price-card-base .pc-badge { background: linear-gradient(135deg, #e08e0b, #c04e00); }
        .cost-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 9px 0; border-bottom: 1px solid #f0f2f5; font-size: .875rem;
        }
        .cost-row:last-child { border-bottom: none; }
        .cost-row .cost-label { color: #888; font-size: .78rem; font-weight: 600; text-transform: uppercase; }
        .cost-row .cost-value { font-weight: 700; color: #3a1800; }

        /* ── Table cards ── */
        .det-table-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 3px 16px rgba(0,0,0,.09);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .det-table-card .card-header-bar {
            background: linear-gradient(135deg, #f39c12, #e05a00);
            padding: 12px 18px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .det-table-card .card-header-bar span { color: #fff; font-weight: 600; font-size: .9rem; }
        .det-table-card .table-wrap { padding: 14px 16px 8px; }
        .det-table-card table thead th {
            background: #fdf4e7;
            border-bottom: 2px solid #f2d49a;
            color: #7d3f00;
            font-size: .75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .4px;
            padding: 9px 10px; white-space: nowrap;
        }
        .det-table-card table tbody tr:hover { background: #fffcf5; }
        .det-table-card table tbody td {
            vertical-align: middle; font-size: .875rem;
            padding: 9px 10px; border-color: #f0f2f5;
        }
        .stock-total-chip {
            display: inline-flex; align-items: center; gap: 8px;
            background: linear-gradient(135deg, #f39c12, #e05a00);
            color: #fff; border-radius: 24px;
            padding: 6px 18px; font-size: .88rem; font-weight: 700;
            margin-bottom: 12px;
            box-shadow: 0 3px 10px rgba(243,156,18,.3);
        }

        /* ── Tabs de modales ── */
        .prod-modal-tabs { border-bottom: 2px solid #e8d5bf; margin-bottom: 16px; }
        .prod-modal-tabs .nav-item { margin-bottom: -2px; }
        .prod-modal-tabs .nav-link {
            color: #7d3f00; font-weight: 600; font-size:.82rem; padding:8px 14px;
            border: 2px solid transparent; border-radius: 8px 8px 0 0;
            transition: all .2s;
        }
        .prod-modal-tabs .nav-link:hover { background:#fdf4e7; color:#e05a00; }
        .prod-modal-tabs .nav-link.active {
            background: linear-gradient(135deg,#f39c12,#e05a00);
            color:#fff !important; border-color: #e05a00 #e05a00 #fff;
        }
        .prod-modal-tabs .nav-link i { margin-right:5px; }

        /* ── Modales modernos ── */
        .modal-modern .modal-content {
            border: none; border-radius: 14px; overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
        }
        .modal-modern .modal-header {
            background: linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
            border: none; padding: 18px 24px;
        }
        .modal-modern .modal-title { color: #fff; font-weight: 700; font-size: 1.05rem; }
        .modal-modern .close { color: rgba(255,255,255,.8); opacity: 1; font-size: 1.3rem; }
        .modal-modern .close:hover { color: #fff; }
        .modal-modern .modal-body { padding: 22px; background: #f8fafc; }
        .modal-modern .modal-footer { background: #fff; border-top: 1px solid #e8ecef; padding: 12px 22px; }

        /* Form sections en modales */
        .ms-section {
            background: #fff; border-radius: 10px;
            padding: 16px 18px 10px;
            margin-bottom: 14px; border: 1px solid #e8ecef;
        }
        .ms-section-title {
            font-size: .75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .7px;
            color: #7d3f00; border-bottom: 2px solid #e8d5bf;
            padding-bottom: 7px; margin-bottom: 14px;
            display: flex; align-items: center; gap: 7px;
        }
        .ms-section label {
            font-size: .79rem; font-weight: 600; color: #555; margin-bottom: 3px;
        }
        .ms-section .form-control {
            border-radius: 8px; border: 1.5px solid #e0e6ed;
            font-size: .875rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .ms-section .form-control:focus {
            border-color: #e05a00;
            box-shadow: 0 0 0 3px rgba(224,90,0,.12);
        }
        .ms-section .form-control:disabled { background: #f8fafc; color: #999; }
        .ms-price-group { position: relative; }
        .ms-price-prefix {
            position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
            color: #888; font-size: .8rem; font-weight: 700; pointer-events: none; z-index: 4;
        }
        .ms-price-group .form-control { padding-left: 28px; }
        .ms-price-badge {
            display: inline-block; background: linear-gradient(135deg, #f39c12, #e05a00); color: #fff;
            border-radius: 4px; padding: 2px 7px; font-size: .7rem;
            font-weight: 700; margin-bottom: 3px;
        }

        /* Spinner moderno */
        #modalSpinnerLoading .modal-content { background: transparent; border: none; box-shadow: none; }
        .spinner-overlay-box {
            background: rgba(255,255,255,.97); border-radius: 16px;
            padding: 36px 30px; text-align: center;
            box-shadow: 0 15px 50px rgba(0,0,0,.2);
        }
        .spinner-ring {
            display: inline-block; width: 50px; height: 50px;
            border: 5px solid #e8d5bf; border-top-color: #e05a00;
            border-radius: 50%; animation: spin .8s linear infinite; margin-bottom: 14px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner-overlay-box p { margin: 0; font-size: 1rem; font-weight: 600; color: #7d3f00; }
        .spinner-overlay-box small { color: #888; font-size: .8rem; }

        /* foto upload */
        .foto-drop-area {
            border: 2px dashed #ccd3db; border-radius: 10px;
            padding: 20px; text-align: center; cursor: pointer;
            transition: border-color .2s, background .2s;
        }
        .foto-drop-area:hover { border-color: #e05a00; background: #fdf4e7; }
        .foto-drop-area i { font-size: 1.8rem; color: #aaa; display: block; margin-bottom: 6px; }
        .foto-drop-area span { font-size: .8rem; color: #888; }
        #imagenPrevisualizacion {
            max-width: 100%; max-height: 160px; border-radius: 10px;
            object-fit: contain; border: 2px dashed #e0e6ed; padding: 6px;
        }
    </style>
    @endpush

    {{-- ══ PAGE HEADER ══════════════════════════════════════════════ --}}
    <div class="det-page-header">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home mr-1"></i> Inventario</li>
            <li class="breadcrumb-item"><a href="/producto/registro" style="color:rgba(255,255,255,.6);">Catálogo</a></li>
            <li class="breadcrumb-item active">Detalle</li>
        </ol>
        <div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:14px;">
            <div>
                <p class="prod-name">
                    <i class="fa fa-cube mr-2" style="color:rgba(255,255,255,.85);"></i>
                    {{ mb_convert_case($producto->nombre, MB_CASE_TITLE, 'UTF-8') }}
                    <small>#{{ $producto->id }}</small>
                </p>
            </div>
            <div class="det-header-actions">
                <button class="btn-det-edit" data-toggle="modal" data-target="#modal_producto_editar">
                    <i class="fa fa-pencil mr-1"></i> Editar Producto
                </button>
                <button class="btn-det-foto" data-toggle="modal" data-target="#modal_foto_producto">
                    <i class="fa fa-camera mr-1"></i> Subir Fotografía
                </button>
            </div>
        </div>
    </div>

    {{-- ══ CUERPO PRINCIPAL ══════════════════════════════════════════ --}}
    <div class="det-body">
        <div class="row" style="margin-bottom:18px;">

            {{-- Carousel de fotografías --}}
            <div class="col-12 col-lg-6 mb-4 mb-lg-0">
                <div class="det-carousel-card">
                    <div class="card-header-bar">
                        <i class="fa fa-picture-o" style="color:rgba(255,255,255,.7);"></i>
                        <span>Fotografías del Producto</span>
                    </div>
                    <div class="det-carousel-inner">
                        @if(count($imagenes) > 0)
                        <div id="carouselExampleBigIndicators" class="carousel slide" data-ride="carousel">
                            <ol class="carousel-indicators">
                                @for ($i = 0; $i < count($imagenes); $i++)
                                <li data-target="#carouselExampleBigIndicators" data-slide-to="{{ $i }}"
                                    class="{{ $i === 0 ? 'active' : '' }}"></li>
                                @endfor
                            </ol>
                            <div class="carousel-inner">
                                @php $comillas = '"'; @endphp
                                @foreach ($imagenes as $imagen)
                                <div class="carousel-item {{ $imagen->contador == 1 ? 'active' : '' }}">
                                    @if (Auth::user()->rol_id == '1' || Auth::user()->rol_id == '5' || Auth::user()->rol_id == '7' || Auth::user()->rol_id == '9' || Auth::user()->rol_id == '10')
                                    <div class="text-center mb-2">
                                        <button class="btn-eliminar-img"
                                            onclick="eliminar({{ $comillas . $imagen->url_img . $comillas }})"
                                            type="button">
                                            <i class="fa fa-trash mr-1"></i> Eliminar imagen
                                        </button>
                                    </div>
                                    @endif
                                    <img class="d-block"
                                        src="{{ asset('catalogo/' . $imagen->url_img) }}"
                                        alt="Imagen {{ $imagen->contador }}"
                                        style="max-height:300px; width:100%; object-fit:contain; border-radius:10px; background:#f8fafc;">
                                </div>
                                @endforeach
                            </div>
                            @if(count($imagenes) > 1)
                            <a class="carousel-control-prev" href="#carouselExampleBigIndicators" role="button" data-slide="prev">
                                <i class="fa-solid fa-angle-left"></i>
                                <span class="sr-only">Anterior</span>
                            </a>
                            <a class="carousel-control-next" href="#carouselExampleBigIndicators" role="button" data-slide="next">
                                <i class="fa-solid fa-angle-right"></i>
                                <span class="sr-only">Siguiente</span>
                            </a>
                            @endif
                        </div>
                        @else
                        <div class="no-photos-placeholder">
                            <i class="fa fa-picture-o"></i>
                            <p style="color:#aaa; font-size:.9rem; margin:0;">Sin fotografías registradas</p>
                            @if (Auth::user()->rol_id == '1' || Auth::user()->rol_id == '10' || Auth::user()->rol_id == '9')
                            <button class="btn-det-foto mt-3" data-toggle="modal" data-target="#modal_foto_producto">
                                <i class="fa fa-plus mr-1"></i> Agregar fotografía
                            </button>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Información general --}}
            <div class="col-12 col-lg-6">
                <div class="det-info-card">
                    <div class="card-header-bar">
                        <i class="fa fa-info-circle" style="color:rgba(255,255,255,.7);"></i>
                        <span>Información General</span>
                    </div>
                    <div class="card-body-inner">

                        {{-- Identificación --}}
                        <div class="info-section-title"><i class="fa fa-barcode"></i> Identificación</div>
                        <div class="info-row">
                            <span class="info-label">ID interno</span>
                            <span class="info-value"><span class="info-badge-id">{{ $producto->id }}</span></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Nombre</span>
                            <span class="info-value">{{ $producto->nombre }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Descripción</span>
                            <span class="info-value">{{ $producto->descripcion }}</span>
                        </div>
                        @if($esAdmin)
                        <div class="info-row">
                            <span class="info-label">ISV</span>
                            <span class="info-value">
                                @if($producto->isv == 0)
                                    <span class="isv-chip-exento">Exento</span>
                                @elseif($producto->isv == 15)
                                    <span class="isv-chip-15">15%</span>
                                @else
                                    <span class="isv-chip-18">{{ $producto->isv }}%</span>
                                @endif
                            </span>
                        </div>
                        @endif
                        <div class="info-row">
                            <span class="info-label">Cód. Estatal</span>
                            <span class="info-value">{{ $producto->codigo_estatal ?: '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Cód. de Barra</span>
                            <span class="info-value">{{ $producto->codigo_barra ?: '—' }}</span>
                        </div>

                        {{-- Clasificación --}}
                        <div class="info-section-title"><i class="fa fa-tag"></i> Clasificación</div>
                        <div class="info-row">
                            <span class="info-label">Marca</span>
                            <span class="info-value">{{ $producto->marca ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Categoría</span>
                            <span class="info-value">{{ $producto->categoria }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Sub-categoría</span>
                            <span class="info-value">{{ $producto->sub_categoria }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Unidad de medida</span>
                            <span class="info-value">{{ $producto->unidad_medida }}</span>
                        </div>

                        {{-- Recuperación y Origen --}}
                        @if($producto->tiempo_recuperacion_meses || $producto->origen)
                        <div class="info-section-title"><i class="fa fa-clock-o"></i> Recuperación y Origen</div>
                        @if($producto->tiempo_recuperacion_meses)
                        <div class="info-row">
                            <span class="info-label">T. recuperación</span>
                            <span class="info-value">
                                <span style="background:#e8f5e9; color:#2e7d32; border-radius:20px; padding:2px 12px; font-size:.8rem; font-weight:700;">
                                    {{ $producto->tiempo_recuperacion_meses }} {{ $producto->tiempo_recuperacion_meses == 1 ? 'mes' : 'meses' }}
                                </span>
                            </span>
                        </div>
                        @endif
                        @if($producto->origen)
                        <div class="info-row">
                            <span class="info-label">Origen</span>
                            <span class="info-value">{{ $producto->origen }}</span>
                        </div>
                        @endif
                        @endif

                        {{-- Registro --}}
                        <div class="info-section-title"><i class="fa fa-calendar"></i> Registro</div>
                        <div class="info-row">
                            <span class="info-label">Fecha de registro</span>
                            <span class="info-value">{{ $producto->fecha_registro }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Registrado por</span>
                            <span class="info-value">{{ $producto->registrado_por }}</span>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ── Disponibilidad ────────────────────────────────────────── --}}
        <div class="det-table-card">
            <div class="card-header-bar">
                <span><i class="fa fa-cubes mr-2"></i> Disponibilidad de Producto</span>
                <span class="stock-total-chip" id="total_lotes" style="font-size:.78rem; padding:4px 14px;"></span>
            </div>
            <div class="table-wrap">
                <div class="table-responsive">
                    <table id="tbl_lotes_listar" class="table table-hover" style="width:100%"
                           data-producto-codigo="{{ $producto->id }}"
                           data-producto-nombre="{{ $producto->nombre }}"
                           data-usuario-descarga="{{ optional(Auth::user())->name ?? 'Sistema' }}">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cód. Producto</th>
                                <th>Nombre</th>
                                <th>Departamento</th>
                                <th>Municipio</th>
                                <th>Bodega</th>
                                <th>Dirección</th>
                                <th>Sección</th>
                                <th>Número</th>
                                <th style="color:#e65100;">Reserva</th>
                                <th>Cant. en Bodega</th>
                                <th>Cant. Disponible</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lotes as $lote)
                            <tr>
                                <td>{{ $lote->contador }}</td>
                                <td>{{ $lote->id }}</td>
                                <td>{{ $lote->nombre }}</td>
                                <td>{{ $lote->departamento }}</td>
                                <td>{{ $lote->municipio }}</td>
                                <td>{{ $lote->bodega }}</td>
                                <td>{{ $lote->direccion }}</td>
                                <td>{{ $lote->seccion }}</td>
                                <td>{{ $lote->numeracion }}</td>
                                {{-- Reserva (FIFO por fila) --}}
                                <td style="text-align:center;">
                                    @if ($lote->reservado_fila > 0)
                                        <button type="button"
                                                onclick="abrirModalReservas(this)"
                                                data-seccion-label="{{ $lote->bodega }} • {{ $lote->seccion }}"
                                                data-reservas='@json($lote->reservas_detalle)'
                                                style="background:#fff3e0; color:#e65100; border:1px solid #ffcc80;
                                                       border-radius:12px; padding:2px 12px; font-weight:700;
                                                       font-size:13px; cursor:pointer;">
                                            <i class="fa fa-lock" style="font-size:11px; margin-right:4px;"></i>{{ (int)$lote->reservado_fila }}
                                        </button>
                                    @else
                                        <span style="background:#f1f5f9; color:#90a4ae; border-radius:12px;
                                                     padding:2px 10px; font-size:13px;">0</span>
                                    @endif
                                </td>
                                {{-- Cant. en Bodega --}}
                                <td style="text-align:center;">
                                    <span style="background:#f3e5f5; color:#6a1b9a; border-radius:12px;
                                                 padding:2px 10px; font-weight:700; font-size:13px;">
                                        {{ (int)$lote->rawStock }}
                                    </span>
                                </td>
                                {{-- Cant. Disponible --}}
                                <td style="text-align:center;">
                                    <strong style="color:{{ $lote->disponible_fila > 0 ? '#2e7d32' : '#b71c1c' }};">
                                        {{ (int)$lote->disponible_fila }}
                                    </strong>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── Unidades de medida ────────────────────────────────────── --}}
        <div class="det-table-card">
            <div class="card-header-bar">
                <span><i class="fa fa-balance-scale mr-2"></i> Unidades de Medida para Venta</span>
            </div>
            <div class="table-wrap">
                <div class="table-responsive">
                    <table id="tbl_unidades_listar" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Unidad de medición</th>
                                <th>Cantidad de unidades</th>
                                <th>Editar</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ MODAL: EDITAR PRODUCTO ════════════════════════════════════ --}}
    <div class="modal fade modal-modern" id="modal_producto_editar" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-pencil mr-2"></i> Editar Producto</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body" style="padding:20px 24px 8px; background:#f8fafc;">
                    <form id="editarProductoForm" name="editarProductoForm" novalidate>
                        <input type="hidden" id="id_producto_edit" name="id_producto_edit" value="{{ $producto->id }}">

                        {{-- PESTAÑAS --}}
                        <ul class="nav prod-modal-tabs" id="tabsEditar" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tab-det-edit-general">
                                    <i class="fa fa-info-circle"></i> General
                                </a>
                            </li>
                            @if($esAdmin)
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab-det-edit-precios">
                                    <i class="fa fa-dollar"></i> Precios
                                </a>
                            </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab-det-edit-clasif">
                                    <i class="fa fa-tag"></i> Clasificación
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">

                            {{-- Tab 1: General --}}
                            <div class="tab-pane fade show active" id="tab-det-edit-general">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label>Nombre del producto <span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="nombre_producto_edit"
                                            name="nombre_producto_edit" placeholder="Nombre del producto">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label>Descripción <span class="text-danger">*</span></label>
                                        <textarea placeholder="Descripción detallada…" required id="descripcion_producto_edit"
                                            name="descripcion_producto_edit" rows="3" class="form-control"></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Código de barra</label>
                                        <input class="form-control" type="number" name="cod_barra_producto_edit"
                                            id="cod_barra_producto_edit" min="0" placeholder="Opcional">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Código estatal</label>
                                        <input class="form-control" type="number" name="cod_estatal_producto_edit"
                                            id="cod_estatal_producto_edit" min="0" placeholder="Opcional">
                                    </div>
                                </div>
                            </div>

                            {{-- Tab 2: Precios (solo admin) --}}
                            @if($esAdmin)
                            <div class="tab-pane fade" id="tab-det-edit-precios">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label>ISV <span class="text-danger">*</span></label>
                                        <select class="form-control" name="isv_producto_edit" id="isv_producto_edit">
                                            <option value="0">Exento de impuestos</option>
                                            <option value="15" selected>15% de ISV</option>
                                            <option value="18">18% de ISV</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Precio base <span class="text-danger">*</span></label>
                                        <div class="ms-price-group">
                                            <span class="ms-price-prefix">L.</span>
                                            <input class="form-control" step="any" min="0.000001" type="number"
                                                name="precioBase_edit" id="precioBase_edit"
                                                onchange="validacionPrecio()" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Costo promedio <span class="text-danger">*</span></label>
                                        <div class="ms-price-group">
                                            <span class="ms-price-prefix">L.</span>
                                            <input class="form-control" step="any" min="0.000001" type="number"
                                                name="costo_promedio_editar" id="costo_promedio_editar"
                                                placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Último costo de compra <span class="text-danger">*</span></label>
                                        <div class="ms-price-group">
                                            <span class="ms-price-prefix">L.</span>
                                            <input class="form-control" step="any" min="0.000001" type="number"
                                                name="ultimo_costo_compra_editar" id="ultimo_costo_compra_editar"
                                                placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label><span class="ms-price-badge">A</span> Precio A</label>
                                        <div class="ms-price-group">
                                            <span class="ms-price-prefix">L.</span>
                                            <input class="form-control" step="any" type="number" name="precio1" id="precio1" disabled placeholder="Auto">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label><span class="ms-price-badge">B</span> Precio B</label>
                                        <div class="ms-price-group">
                                            <span class="ms-price-prefix">L.</span>
                                            <input class="form-control" step="any" type="number" name="precio2" id="precio2" disabled placeholder="Auto">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label><span class="ms-price-badge">C</span> Precio C</label>
                                        <div class="ms-price-group">
                                            <span class="ms-price-prefix">L.</span>
                                            <input class="form-control" step="any" type="number" name="precio3" id="precio3" disabled placeholder="Auto">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label><span class="ms-price-badge">D</span> Precio D</label>
                                        <div class="ms-price-group">
                                            <span class="ms-price-prefix">L.</span>
                                            <input class="form-control" step="any" type="number" name="precio4" id="precio4" disabled placeholder="Auto">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Tab 3: Clasificación --}}
                            <div class="tab-pane fade" id="tab-det-edit-clasif">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label>Marca <span class="text-danger">*</span></label>
                                        <select class="form-control" name="marca_producto_editar" id="marca_producto_editar">
                                            <option selected disabled>— Seleccione una marca —</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Categoría <span class="text-danger">*</span></label>
                                        <select class="form-control" name="categoria_producto_edit" id="categoria_producto_edit"
                                            onchange="listarSubCategorias()">
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Subcategoría <span class="text-danger">*</span></label>
                                        <select class="form-control" name="sub_categoria_producto_edit"
                                            id="sub_categoria_producto_edit">
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Unidad para compra <span class="text-danger">*</span></label>
                                        <select class="form-control" name="unidad_producto_editar" id="unidad_producto_editar">
                                            <option selected disabled>— Seleccione —</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Cantidad de unidades para compra <span class="text-danger">*</span></label>
                                        <input class="form-control" min="1" type="number" name="unidades_editar"
                                            id="unidades_editar" step="any" required placeholder="Ej: 1">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Tiempo de recuperación en meses</label>
                                        <input class="form-control" type="number" min="1" max="999"
                                            name="tiempo_recuperacion_meses_edit" id="tiempo_recuperacion_meses_edit"
                                            placeholder="Ej: 3">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Origen</label>
                                        <input class="form-control" type="text" maxlength="200"
                                            name="origen_edit" id="origen_edit"
                                            placeholder="Ej: China, Honduras...">
                                    </div>
                                </div>
                            </div>

                        </div>{{-- /tab-content --}}

                    </form>
                </div>
                <div class="modal-footer" style="justify-content:space-between;">
                    <button type="button" onclick="actualizarCostos({{ $producto->id }})" class="btn btn-warning" style="border-radius:8px; font-weight:600;">
                        <i class="fa fa-calculator mr-1"></i> Calcular Costos
                    </button>
                    <div style="display:flex; gap:8px;">
                        <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius:8px;">
                            <i class="fa fa-times mr-1"></i> Cancelar
                        </button>
                        <button type="submit" form="editarProductoForm" class="btn btn-primary" style="border-radius:8px; font-weight:600;">
                            <i class="fa fa-save mr-1"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ MODAL: SPINNER ══════════════════════════════════════════ --}}
    <div class="modal" id="modalSpinnerLoading" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:300px;">
            <div class="modal-content">
                <div class="modal-body" style="padding:0;">
                    <div class="spinner-overlay-box">
                        <div class="spinner-ring"></div>
                        <p>Procesando...</p>
                        <small>Por favor espere</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ MODAL: SUBIR FOTO ════════════════════════════════════════ --}}
    @include('components.producto.modal-subir-fotografia', ['productoId' => $producto->id])

    {{-- ══ MODAL: EDITAR UNIDADES ══════════════════════════════════ --}}
    <div class="modal fade modal-modern" id="modal_editar_unidades" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-balance-scale mr-2"></i> Editar Unidad de Venta</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="form_editar_unidades" name="form_editar_unidades" >
                        <input id="idUniadVenta" name="idUniadVenta" type="hidden">
                        <div class="ms-section" style="margin-bottom:0;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Unidad de medida para venta</label>
                                    <select class="form-control" name="unidad_venta_editar" id="unidad_venta_editar">
                                        <option selected disabled>— Seleccione —</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Cantidad de unidades para venta</label>
                                    <input class="form-control" min="1" type="number" name="unidades_venta_editar"
                                        id="unidades_venta_editar" step="any" required placeholder="Ej: 1">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="gap:8px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius:8px;">Cancelar</button>
                    <button type="submit" form="form_editar_unidades" class="btn btn-primary" style="border-radius:8px; font-weight:600;">
                        <i class="fa fa-save mr-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ MODAL: RESERVAS DE PRODUCTO POR SECCIÓN ════════════════════ --}}
    <div class="modal fade modal-modern" id="modal_reservas_producto" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background:linear-gradient(135deg,#e65100,#bf360c); padding:14px 20px;">
                    <div>
                        <h5 class="modal-title" style="color:#fff; font-weight:700; font-size:14px; margin:0;">
                            <i class="fa fa-lock mr-2"></i>Reservas activas del producto
                        </h5>
                        <small id="modal_reservas_seccion" style="color:rgba(255,255,255,.85); font-size:11px;"></small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"
                            style="color:#fff; opacity:1; text-shadow:none; font-size:20px;">&times;</button>
                </div>
                <div class="modal-body" style="padding:18px 20px;">
                    <p id="modal_reservas_info" style="font-size:12px; color:#607d8b; margin-bottom:12px;"></p>
                    <div id="modal_reservas_empty" style="display:none; text-align:center; color:#aaa; padding:20px 0;">
                        <i class="fa fa-inbox d-block" style="font-size:32px; margin-bottom:8px; opacity:.35;"></i>
                        No hay prefacturas activas reservando este producto en esta sección.
                    </div>
                    <div class="table-responsive" id="modal_reservas_table_wrap">
                        <table class="table table-sm table-hover mb-0" style="font-size:13px;">
                            <thead style="background:#f8f9fc;">
                                <tr>
                                    <th style="padding:8px 12px; color:#555; font-weight:700;">Prefactura</th>
                                    <th style="padding:8px 12px; color:#555; font-weight:700;">Flujo</th>
                                    <th style="padding:8px 12px; color:#555; font-weight:700;">Cliente</th>
                                    <th style="padding:8px 12px; text-align:center; color:#e65100; font-weight:700;">Cant. Reservada</th>
                                    <th style="padding:8px 12px; color:#555; font-weight:700;">Emisión</th>
                                    <th style="padding:8px 12px; color:#555; font-weight:700;">Vence</th>
                                </tr>
                            </thead>
                            <tbody id="modal_reservas_tbody"></tbody>
                            <tfoot>
                                <tr style="background:#fff8f0;">
                                    <td colspan="3" style="padding:8px 12px; font-weight:700; color:#e65100; text-align:right;">Total reservado:</td>
                                    <td style="padding:8px 12px; text-align:center;">
                                        <span id="modal_reservas_total"
                                              style="background:#e65100; color:#fff; border-radius:10px;
                                                     padding:3px 12px; font-weight:700; font-size:13px;"></span>
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius:8px;">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="{{ asset('js/js_proyecto/inventario/modal-foto-producto.js') }}?v={{ filemtime(public_path('js/js_proyecto/inventario/modal-foto-producto.js')) }}"></script>
    <script src="{{ asset('js/js_proyecto/inventario/detalle-producto.js') }}?v={{ filemtime(public_path('js/js_proyecto/inventario/detalle-producto.js')) }}"></script>
    <script>
    function abrirModalReservas(btn) {
        var label    = btn.getAttribute('data-seccion-label') || '';
        var reservas = JSON.parse(btn.getAttribute('data-reservas') || '[]');

        document.getElementById('modal_reservas_seccion').textContent = label;

        var tbody = document.getElementById('modal_reservas_tbody');
        tbody.innerHTML = '';

        var totalReservado = 0;

        if (reservas.length === 0) {
            document.getElementById('modal_reservas_empty').style.display = 'block';
            document.getElementById('modal_reservas_table_wrap').style.display = 'none';
            document.getElementById('modal_reservas_info').textContent = '';
        } else {
            document.getElementById('modal_reservas_empty').style.display = 'none';
            document.getElementById('modal_reservas_table_wrap').style.display = 'block';
            document.getElementById('modal_reservas_info').textContent =
                reservas.length + ' prefactura(s) activa(s) con reserva en esta bodega/sección.';

            reservas.forEach(function(r) {
                var cant = parseFloat(r.cantidad) || 0;
                totalReservado += cant;

                var tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid #f0f0f0';
                tr.innerHTML =
                    '<td style="padding:8px 12px;">' +
                        '<span style="background:#e3f2fd;color:#1565c0;border-radius:8px;padding:2px 9px;font-weight:700;font-size:12px;">#' + r.prefactura_id + '</span>' +
                    '</td>' +
                    '<td style="padding:8px 12px;">' +
                        (r.flujo_id
                            ? '<span style="background:#f3e5f5;color:#6a1b9a;border-radius:8px;padding:2px 9px;font-weight:700;font-size:12px;">#' + r.flujo_id + '</span>'
                            : '<span style="color:#aaa;">—</span>') +
                    '</td>' +
                    '<td style="padding:8px 12px;color:#2c3e50;font-size:12px;">' + (r.nombre_cliente || '—') + '</td>' +
                    '<td style="padding:8px 12px;text-align:center;">' +
                        '<span style="background:#fff3e0;color:#e65100;border-radius:10px;padding:2px 10px;font-weight:700;font-size:13px;">' + Math.round(cant) + '</span>' +
                    '</td>' +
                    '<td style="padding:8px 12px;font-size:12px;color:#555;">' + (r.fecha_emision ? r.fecha_emision.substring(0,10).split('-').reverse().join('/') : '—') + '</td>' +
                    '<td style="padding:8px 12px;font-size:12px;color:#555;">' + (r.fecha_vencimiento ? r.fecha_vencimiento.substring(0,10).split('-').reverse().join('/') : '—') + '</td>';
                tbody.appendChild(tr);
            });
        }

        document.getElementById('modal_reservas_total').textContent = Math.round(totalReservado);
        $('#modal_reservas_producto').modal('show');
    }
    </script>
    @endpush
</div>
