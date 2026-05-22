<div>
    @push('styles')
    <style>
        /* =============================================
           DISEÑO DE PRODUCTO — ESTILOS MODERNOS
        ============================================= */

        /* ── Page header ── */
        .dis-page-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%);
            padding: 22px 28px 18px;
            border-bottom: 3px solid #e74c3c;
        }
        .dis-page-header .prod-name {
            color: #fff;
            font-size: 1.55rem;
            font-weight: 700;
            margin: 0 0 4px;
            letter-spacing: .3px;
        }
        .dis-page-header .prod-name small {
            font-size: .85rem;
            font-weight: 400;
            color: rgba(255,255,255,.55);
            margin-left: 8px;
        }
        .dis-page-header .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0 0 14px;
            font-size: .8rem;
        }
        .dis-page-header .breadcrumb-item a,
        .dis-page-header .breadcrumb-item.active { color: rgba(255,255,255,.6); }
        .dis-page-header .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,.35); }
        .dis-header-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn-dis-edit {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: #fff; border: none; border-radius: 8px;
            padding: 8px 18px; font-weight: 600; font-size: .85rem;
            transition: all .2s; box-shadow: 0 3px 10px rgba(243,156,18,.3);
        }
        .btn-dis-edit:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(243,156,18,.45); color: #fff; }
        .btn-dis-foto {
            background: linear-gradient(135deg, #1abc9c, #16a085);
            color: #fff; border: none; border-radius: 8px;
            padding: 8px 18px; font-weight: 600; font-size: .85rem;
            transition: all .2s; box-shadow: 0 3px 10px rgba(26,188,156,.3);
        }
        .btn-dis-foto:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(26,188,156,.45); color: #fff; }

        /* ── Body wrapper ── */
        .dis-body { padding: 20px 24px; }
        @media (max-width: 767px) { .dis-body { padding: 14px 12px; } }

        /* ── Carousel card ── */
        .dis-carousel-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 3px 16px rgba(0,0,0,.09);
            overflow: hidden;
            height: 100%;
        }
        .dis-carousel-card .card-header-bar {
            background: linear-gradient(90deg, #0f3460, #16213e);
            padding: 11px 18px;
            display: flex; align-items: center; gap: 8px;
        }
        .dis-carousel-card .card-header-bar span { color: #fff; font-weight: 600; font-size: .9rem; }
        .dis-carousel-inner { padding: 16px; }
        #carouselDisenoIndicators .carousel-item img {
            max-height: 320px;
            width: 100%;
            object-fit: contain;
            border-radius: 10px;
            background: #f8fafc;
        }
        #carouselDisenoIndicators .carousel-control-prev,
        #carouselDisenoIndicators .carousel-control-next {
            width: 42px; height: 42px;
            background: rgba(15,52,96,.85);
            border-radius: 50%;
            top: 50%; transform: translateY(-50%);
            opacity: 1;
        }
        #carouselDisenoIndicators .carousel-control-prev { left: 8px; }
        #carouselDisenoIndicators .carousel-control-next { right: 8px; }
        #carouselDisenoIndicators .carousel-control-prev i,
        #carouselDisenoIndicators .carousel-control-next i {
            font-size: 1.3rem !important;
            color: #fff !important;
        }
        #carouselDisenoIndicators .carousel-indicators li {
            background-color: #0f3460;
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
        .dis-info-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 3px 16px rgba(0,0,0,.09);
            overflow: hidden;
            margin-bottom: 18px;
        }
        .dis-info-card .card-header-bar {
            background: linear-gradient(90deg, #0f3460, #16213e);
            padding: 11px 18px;
            display: flex; align-items: center; gap: 8px;
        }
        .dis-info-card .card-header-bar span { color: #fff; font-weight: 600; font-size: .9rem; }
        .dis-info-card .card-body-inner { padding: 16px 20px; }

        /* Info rows */
        .info-row {
            display: flex; align-items: flex-start;
            padding: 8px 0;
            border-bottom: 1px solid #f0f2f5;
            font-size: .875rem;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label {
            min-width: 160px;
            color: #888;
            font-size: .78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .4px;
            flex-shrink: 0;
            padding-top: 2px;
        }
        .info-value { color: #1a1a2e; font-weight: 500; }
        .info-badge-id {
            background: #0f3460; color: #fff;
            border-radius: 6px; padding: 2px 10px;
            font-size: .82rem; font-weight: 700;
        }
        .isv-chip-exento { background:#d5f5e3; color:#1e8449; border-radius:20px; padding:3px 11px; font-size:.78rem; font-weight:700; }
        .isv-chip-15     { background:#fef9e7; color:#d35400; border-radius:20px; padding:3px 11px; font-size:.78rem; font-weight:700; }
        .isv-chip-18     { background:#fdecea; color:#c0392b; border-radius:20px; padding:3px 11px; font-size:.78rem; font-weight:700; }

        /* ── Precio A card ── */
        .precio-a-card {
            background: linear-gradient(135deg, #0f3460, #16213e);
            border-radius: 14px;
            padding: 22px 24px;
            text-align: center;
            box-shadow: 0 4px 16px rgba(15,52,96,.25);
            margin-bottom: 14px;
        }
        .precio-a-card .pa-label {
            color: rgba(255,255,255,.65);
            font-size: .78rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .6px;
            margin-bottom: 6px;
        }
        .precio-a-card .pa-badge {
            display: inline-block;
            background: #e74c3c; color: #fff;
            border-radius: 6px; padding: 3px 12px;
            font-size: .8rem; font-weight: 700;
            margin-bottom: 10px;
        }
        .precio-a-card .pa-value {
            font-size: 2rem; font-weight: 800; color: #fff;
            display: block; line-height: 1;
        }
        .precio-a-card .pa-currency {
            color: rgba(255,255,255,.55);
            font-size: .8rem; margin-top: 6px;
        }

        /* ── Table cards ── */
        .dis-table-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 3px 16px rgba(0,0,0,.09);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .dis-table-card .card-header-bar {
            background: linear-gradient(90deg, #0f3460, #16213e);
            padding: 12px 18px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .dis-table-card .card-header-bar span { color: #fff; font-weight: 600; font-size: .9rem; }
        .dis-table-card .table-wrap { padding: 14px 16px 8px; }
        .dis-table-card table thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e0e6ed;
            color: #1a1a2e;
            font-size: .75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .4px;
            padding: 9px 10px; white-space: nowrap;
        }
        .dis-table-card table tbody tr:hover { background: #f0f6ff; }
        .dis-table-card table tbody td {
            vertical-align: middle; font-size: .875rem;
            padding: 9px 10px; border-color: #f0f2f5;
        }
        .stock-total-chip {
            display: inline-flex; align-items: center; gap: 8px;
            background: linear-gradient(135deg, #1abc9c, #16a085);
            color: #fff; border-radius: 24px;
            padding: 6px 18px; font-size: .88rem; font-weight: 700;
            box-shadow: 0 3px 10px rgba(26,188,156,.3);
        }

        /* ── Modales modernos ── */
        .modal-modern .modal-content {
            border: none; border-radius: 14px; overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
        }
        .modal-modern .modal-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
            border: none; padding: 18px 24px;
        }
        .modal-modern .modal-title { color: #fff; font-weight: 700; font-size: 1.05rem; }
        .modal-modern .close { color: rgba(255,255,255,.8); opacity: 1; font-size: 1.3rem; }
        .modal-modern .close:hover { color: #fff; }
        .modal-modern .modal-body { padding: 22px; background: #f8fafc; }
        .modal-modern .modal-footer { background: #fff; border-top: 1px solid #e8ecef; padding: 12px 22px; }
        .ms-section {
            background: #fff; border-radius: 10px;
            padding: 16px 18px 10px;
            margin-bottom: 14px; border: 1px solid #e8ecef;
        }
        .ms-section-title {
            font-size: .75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .7px;
            color: #0f3460; border-bottom: 2px solid #e8ecef;
            padding-bottom: 7px; margin-bottom: 14px;
            display: flex; align-items: center; gap: 7px;
        }
        .ms-section label { font-size: .79rem; font-weight: 600; color: #555; margin-bottom: 3px; }
        .ms-section .form-control {
            border-radius: 8px; border: 1.5px solid #e0e6ed; font-size: .875rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .ms-section .form-control:focus { border-color: #0f3460; box-shadow: 0 0 0 3px rgba(15,52,96,.1); }

        /* Spinner */
        #modalSpinnerDisenoLoading .modal-content { background: transparent; border: none; box-shadow: none; }
        .spinner-overlay-box {
            background: rgba(255,255,255,.97); border-radius: 16px;
            padding: 36px 30px; text-align: center;
            box-shadow: 0 15px 50px rgba(0,0,0,.2);
        }
        .spinner-ring {
            display: inline-block; width: 50px; height: 50px;
            border: 5px solid #e8ecef; border-top-color: #0f3460;
            border-radius: 50%; animation: spin .8s linear infinite; margin-bottom: 14px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner-overlay-box p { margin: 0; font-size: 1rem; font-weight: 600; color: #1a1a2e; }
        .spinner-overlay-box small { color: #888; font-size: .8rem; }

        /* foto */
        .foto-drop-area {
            border: 2px dashed #ccd3db; border-radius: 10px;
            padding: 20px; text-align: center; cursor: pointer;
            transition: border-color .2s, background .2s;
        }
        .foto-drop-area:hover { border-color: #0f3460; background: #f0f6ff; }
        .foto-drop-area i { font-size: 1.8rem; color: #aaa; display: block; margin-bottom: 6px; }
        .foto-drop-area span { font-size: .8rem; color: #888; }
        #imagenPrevisualizacion {
            max-width: 100%; max-height: 160px; border-radius: 10px;
            object-fit: contain; border: 2px dashed #e0e6ed; padding: 6px;
        }
    </style>
    @endpush

    {{-- PAGE HEADER --}}
    <div class="dis-page-header">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home mr-1"></i> Inventario</li>
            <li class="breadcrumb-item"><a href="/producto/registro" style="color:rgba(255,255,255,.6);">Catálogo</a></li>
            <li class="breadcrumb-item active">Detalle</li>
        </ol>
        <div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:14px;">
            <div>
                <p class="prod-name">
                    <i class="fa fa-cube mr-2" style="color:#e74c3c;"></i>
                    {{ ucwords(strtolower($producto->nombre)) }}
                    <small>#{{ $producto->id }}</small>
                </p>
            </div>
            <div class="dis-header-actions">
                <button class="btn-dis-edit" data-toggle="modal" data-target="#modal_diseno_editar">
                    <i class="fa fa-pencil mr-1"></i> Editar Producto
                </button>
                <button class="btn-dis-foto" data-toggle="modal" data-target="#modal_foto_producto">
                    <i class="fa fa-camera mr-1"></i> Subir Fotografía
                </button>
            </div>
        </div>
    </div>

    {{-- CUERPO PRINCIPAL --}}
    <div class="dis-body">
        <div class="row" style="margin-bottom:18px;">

            {{-- Carousel --}}
            <div class="col-12 col-lg-6 mb-4 mb-lg-0">
                <div class="dis-carousel-card">
                    <div class="card-header-bar">
                        <i class="fa fa-picture-o" style="color:rgba(255,255,255,.7);"></i>
                        <span>Fotografías del Producto</span>
                    </div>
                    <div class="dis-carousel-inner">
                        @if(count($imagenes) > 0)
                        <div id="carouselDisenoIndicators" class="carousel slide" data-ride="carousel">
                            <ol class="carousel-indicators">
                                @for ($i = 0; $i < count($imagenes); $i++)
                                <li data-target="#carouselDisenoIndicators" data-slide-to="{{ $i }}"
                                    class="{{ $i === 0 ? 'active' : '' }}"></li>
                                @endfor
                            </ol>
                            <div class="carousel-inner">
                                @php $comillas = '"'; @endphp
                                @foreach ($imagenes as $imagen)
                                <div class="carousel-item {{ $imagen->contador == 1 ? 'active' : '' }}">
                                    <div class="text-center mb-2">
                                        <button class="btn-eliminar-img"
                                            onclick="eliminar({{ $comillas . $imagen->url_img . $comillas }})"
                                            type="button">
                                            <i class="fa fa-trash mr-1"></i> Eliminar imagen
                                        </button>
                                    </div>
                                    <img class="d-block"
                                        src="{{ asset('catalogo/' . $imagen->url_img) }}"
                                        alt="Imagen {{ $imagen->contador }}"
                                        style="max-height:300px; width:100%; object-fit:contain; border-radius:10px; background:#f8fafc;">
                                </div>
                                @endforeach
                            </div>
                            @if(count($imagenes) > 1)
                            <a class="carousel-control-prev" href="#carouselDisenoIndicators" role="button" data-slide="prev">
                                <i class="fa-solid fa-angle-left"></i>
                                <span class="sr-only">Anterior</span>
                            </a>
                            <a class="carousel-control-next" href="#carouselDisenoIndicators" role="button" data-slide="next">
                                <i class="fa-solid fa-angle-right"></i>
                                <span class="sr-only">Siguiente</span>
                            </a>
                            @endif
                        </div>
                        @else
                        <div class="no-photos-placeholder">
                            <i class="fa fa-picture-o"></i>
                            <p style="color:#aaa; font-size:.9rem; margin:0;">Sin fotografías registradas</p>
                            <button class="btn-dis-foto mt-3" data-toggle="modal" data-target="#modal_foto_producto">
                                <i class="fa fa-plus mr-1"></i> Agregar fotografía
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Info general --}}
            <div class="col-12 col-lg-6">
                <div class="dis-info-card">
                    <div class="card-header-bar">
                        <i class="fa fa-info-circle" style="color:rgba(255,255,255,.7);"></i>
                        <span>Información General</span>
                    </div>
                    <div class="card-body-inner">
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
                        <div class="info-row">
                            <span class="info-label">Cód. Estatal</span>
                            <span class="info-value">{{ $producto->codigo_estatal ?: '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Cód. de Barra</span>
                            <span class="info-value">{{ $producto->codigo_barra ?: '—' }}</span>
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

        {{-- Disponibilidad --}}
        <div class="dis-table-card">
            <div class="card-header-bar">
                <span><i class="fa fa-cubes mr-2"></i> Disponibilidad de Producto</span>
                <span class="stock-total-chip" id="total_lotes_diseno" style="font-size:.78rem; padding:4px 14px;"></span>
            </div>
            <div class="table-wrap">
                <div class="table-responsive">
                    <table id="tbl_lotes_diseno" class="table table-hover" style="width:100%">
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
                                <td><strong>{{ $lote->cantidad_disponible }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Unidades de medida --}}
        <div class="dis-table-card">
            <div class="card-header-bar">
                <span><i class="fa fa-balance-scale mr-2"></i> Unidades de Medida para Venta</span>
            </div>
            <div class="table-wrap">
                <div class="table-responsive">
                    <table id="tbl_unidades_diseno" class="table table-hover" style="width:100%">
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

    {{-- MODAL: EDITAR PRODUCTO --}}
    <div class="modal fade modal-modern" id="modal_diseno_editar" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-pencil mr-2"></i> Editar Información del Producto</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="editarProductoDisenoForm" name="editarProductoDisenoForm" data-parsley-validate>
                        <input type="hidden" id="id_producto_diseno" name="id_producto_edit" value="{{ $producto->id }}">

                        <div class="ms-section">
                            <div class="ms-section-title"><i class="fa fa-info-circle"></i> Información General</div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label>Nombre del producto <span class="text-danger">*</span></label>
                                    <input class="form-control" required type="text"
                                        id="nombre_producto_diseno" name="nombre_producto_edit"
                                        data-parsley-required placeholder="Nombre del producto">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label>Descripción <span class="text-danger">*</span></label>
                                    <textarea placeholder="Descripción detallada…" required
                                        id="descripcion_producto_diseno" name="descripcion_producto_edit"
                                        rows="3" class="form-control" data-parsley-required></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Código de barra</label>
                                    <input class="form-control" type="number"
                                        name="cod_barra_producto_edit" id="cod_barra_diseno" min="0" placeholder="Opcional">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Código estatal</label>
                                    <input class="form-control" type="number"
                                        name="cod_estatal_producto_edit" id="cod_estatal_diseno" min="0" placeholder="Opcional">
                                </div>
                            </div>
                        </div>

                        <div class="ms-section">
                            <div class="ms-section-title"><i class="fa fa-tags"></i> Categorización</div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Marca <span class="text-danger">*</span></label>
                                    <select class="form-control" name="marca_producto_editar"
                                        id="marca_diseno" data-parsley-required>
                                        <option selected disabled>— Seleccione —</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Categoría <span class="text-danger">*</span></label>
                                    <select class="form-control" name="categoria_producto_edit"
                                        id="categoria_diseno" data-parsley-required
                                        onchange="listarSubCategoriasDiseno()">
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Sub-categoría <span class="text-danger">*</span></label>
                                    <select class="form-control" name="sub_categoria_producto_edit"
                                        id="sub_categoria_diseno" data-parsley-required>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="ms-section">
                            <div class="ms-section-title"><i class="fa fa-balance-scale"></i> Unidades de Medida para Compra</div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Unidad de medida <span class="text-danger">*</span></label>
                                    <select class="form-control" name="unidad_producto_editar"
                                        id="unidad_diseno" data-parsley-required>
                                        <option selected disabled>— Seleccione —</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Cantidad de unidades <span class="text-danger">*</span></label>
                                    <input class="form-control" min="1" type="number"
                                        name="unidades_editar" id="unidades_diseno" step="any" required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" form="editarProductoDisenoForm"
                        style="background:linear-gradient(135deg,#0f3460,#16213e);color:#fff;border:none;border-radius:8px;padding:8px 22px;font-weight:600;">
                        <i class="fa fa-save mr-1"></i> Guardar producto
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: SPINNER --}}
    <div class="modal" id="modalSpinnerDisenoLoading" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="background:transparent;border:none;box-shadow:none;">
                <div class="modal-body p-0">
                    <div class="spinner-overlay-box">
                        <div class="spinner-ring"></div>
                        <p>Procesando...</p>
                        <small>Por favor espere un momento</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: SUBIR FOTOGRAFÍA --}}
    <div class="modal fade modal-modern" id="modal_foto_producto" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-camera mr-2"></i> Subir Fotografía del Producto</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="foto_productoForm" name="foto_productoForm" data-parsley-validate>
                        <input type="hidden" id="id_producto_edit_foto"
                            name="id_producto_edit_foto" value="{{ $producto->id }}">
                        <label for="foto_producto_edit" class="foto-drop-area w-100" style="cursor:pointer; margin:0;">
                            <i class="fa fa-cloud-upload"></i>
                            <span>Haz clic para seleccionar imágenes (máx. 10)<br><small style="color:#aaa;">PNG, JPG, GIF</small></span>
                            <input type="file" id="foto_producto_edit" name="foto_producto_edit"
                                accept="image/png,image/gif,image/jpeg" multiple style="display:none;">
                        </label>
                        <div id="previewContainer" style="display:none; margin-top:16px;">
                            <p style="font-size:.78rem; color:#888; margin-bottom:10px;">
                                <i class="fa fa-check-circle" style="color:#1abc9c;"></i>&nbsp;
                                <span id="previewCount">0</span> imagen(es) seleccionada(s)
                            </p>
                            <div id="previewGrid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(90px,1fr)); gap:8px;"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" form="foto_productoForm"
                        style="background:linear-gradient(135deg,#1abc9c,#16a085);color:#fff;border:none;border-radius:8px;padding:8px 22px;font-weight:600;">
                        <i class="fa fa-upload mr-1"></i> Guardar Imagen
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: EDITAR UNIDADES --}}
    <div class="modal fade modal-modern" id="modal_editar_unidades" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-balance-scale mr-2"></i> Editar Unidad de Venta</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="form_editar_unidades" name="form_editar_unidades" data-parsley-validate>
                        <input id="idUniadVenta" name="idUniadVenta" type="hidden">
                        <div class="ms-section">
                            <div class="ms-section-title"><i class="fa fa-balance-scale"></i> Datos de la Unidad</div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Unidad de medida para venta</label>
                                    <select class="form-control" name="unidad_venta_editar"
                                        id="unidad_venta_editar" data-parsley-required>
                                        <option selected disabled>— Seleccione —</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Cantidad de unidades para venta</label>
                                    <input class="form-control" min="1" type="number"
                                        name="unidades_venta_editar" id="unidades_venta_editar" step="any" required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" form="form_editar_unidades"
                        style="background:linear-gradient(135deg,#0f3460,#16213e);color:#fff;border:none;border-radius:8px;padding:8px 22px;font-weight:600;">
                        <i class="fa fa-save mr-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/js_proyecto/inventario/diseno-producto.js') }}"></script>
    @endpush
</div>
