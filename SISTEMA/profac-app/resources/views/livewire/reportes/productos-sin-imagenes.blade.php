<div class="psi-wrap">
    <style>
        .psi-wrap{min-height:100vh;background:radial-gradient(circle at top left, rgba(12, 74, 110, .12), transparent 28%),radial-gradient(circle at top right, rgba(234, 88, 12, .12), transparent 26%),linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);padding:24px;}
        .psi-shell{max-width:1440px;margin:0 auto;}
        .psi-hero{background:linear-gradient(135deg, #0f172a 0%, #134e4a 54%, #0f172a 100%);color:#fff;border-radius:24px;padding:28px;box-shadow:0 20px 45px rgba(15,23,42,.22);position:relative;overflow:hidden;}
        .psi-hero:after{content:'';position:absolute;inset:auto -70px -80px auto;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle, rgba(255,255,255,.18) 0%, transparent 68%);}
        .psi-hero-top{display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;align-items:flex-start;position:relative;z-index:1;}
        .psi-kicker{display:inline-flex;align-items:center;gap:8px;padding:6px 12px;border-radius:999px;background:rgba(255,255,255,.12);font-size:12px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;}
        .psi-title{margin:14px 0 8px;font-size:clamp(24px, 3vw, 38px);font-weight:900;line-height:1.1;}
        .psi-subtitle{max-width:760px;color:rgba(255,255,255,.78);margin:0;font-size:14px;line-height:1.6;}
        .psi-counter{min-width:220px;padding:18px 20px;border-radius:18px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.16);text-align:left;}
        .psi-counter-value{font-size:34px;font-weight:900;line-height:1;}
        .psi-counter-label{margin-top:6px;font-size:12px;letter-spacing:.3px;text-transform:uppercase;color:rgba(255,255,255,.72);}
        .psi-grid{display:grid;grid-template-columns:320px 1fr;gap:18px;margin-top:18px;}
        .psi-panel{background:#fff;border:1px solid rgba(148,163,184,.24);border-radius:22px;box-shadow:0 14px 32px rgba(15,23,42,.08);overflow:hidden;}
        .psi-panel-head{padding:18px 20px 0;}
        .psi-panel-title{font-size:14px;font-weight:800;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;}
        .psi-panel-text{margin:8px 0 0;color:#64748b;font-size:13px;line-height:1.5;}
        .psi-filters{padding:18px 20px 20px;display:grid;gap:14px;}
        .psi-field label{display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:6px;}
        .psi-field .form-control,.psi-field .form-control:focus,.psi-field .custom-select:focus{border-color:#cbd5e1;box-shadow:none;}
        .psi-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:4px;}
        .psi-btn{border:none;border-radius:12px;padding:10px 16px;font-weight:700;font-size:13px;}
        .psi-btn-primary{background:linear-gradient(135deg, #0f766e 0%, #0f172a 100%);color:#fff;}
        .psi-btn-light{background:#e2e8f0;color:#0f172a;}
        .psi-table-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;padding:18px 20px;border-bottom:1px solid #e2e8f0;}
        .psi-table-head h3{margin:0;font-size:14px;font-weight:800;color:#0f172a;}
        .psi-table-head p{margin:6px 0 0;font-size:12px;color:#64748b;}
        .psi-table-wrap{padding:8px 14px 16px;}
        table.dataTable thead th{background:#f8fafc;color:#0f172a;font-size:12px;font-weight:800;border-bottom:1px solid #e2e8f0 !important;white-space:nowrap;}
        table.dataTable tbody td{vertical-align:middle;font-size:13px;}
        .psi-code{font-weight:800;color:#0f172a;}
        .psi-muted{color:#64748b;font-size:12px;}
        @media (max-width: 992px){.psi-grid{grid-template-columns:1fr;}}
    </style>

    <div class="psi-shell">
        <div class="psi-hero">
            <div class="psi-hero-top">
                <div>
                    <span class="psi-kicker"><i class="fa fa-image"></i> Reporte de inventario</span>
                    <h1 class="psi-title">Productos sin imágenes</h1>
                    <p class="psi-subtitle">Este reporte muestra los productos que no tienen ningún registro asociado en <strong>img_producto</strong>. Sirve para detectar catálogos incompletos y priorizar carga visual.</p>
                </div>
                <div class="psi-counter">
                    <div class="psi-counter-value">{{ number_format($totalSinImagenes) }}</div>
                    <div class="psi-counter-label">productos detectados</div>
                </div>
            </div>
        </div>

        <div class="psi-grid">
            <div class="psi-panel">
                <div class="psi-panel-head">
                    <h2 class="psi-panel-title"><i class="fa fa-filter"></i> Filtros</h2>
                    <p class="psi-panel-text">Ajusta marca o categoría para enfocar el listado.</p>
                </div>

                <div class="psi-filters">
                    <div class="psi-field">
                        <label for="filtroCategoria">Categoría</label>
                        <select id="filtroCategoria" class="custom-select">
                            <option value="">Todas las categorías</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria['id'] }}">{{ $categoria['descripcion'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="psi-field">
                        <label for="filtroMarca">Marca</label>
                        <select id="filtroMarca" class="custom-select">
                            <option value="">Todas las marcas</option>
                            @foreach($marcas as $marca)
                                <option value="{{ $marca['id'] }}">{{ $marca['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="psi-actions">
                        <button type="button" id="btnExportarExcelProductosSinImagenes" class="psi-btn psi-btn-light"><i class="fa fa-file-excel"></i> Excel</button>
                        <button type="button" id="btnExportarPdfProductosSinImagenes" class="psi-btn psi-btn-light"><i class="fa fa-file-pdf"></i> PDF</button>
                        <button type="button" id="btnBuscarProductosSinImagenes" class="psi-btn psi-btn-primary"><i class="fa fa-search"></i> Buscar</button>
                        <button type="button" id="btnLimpiarProductosSinImagenes" class="psi-btn psi-btn-light"><i class="fa fa-broom"></i> Limpiar</button>
                    </div>

                    <div class="psi-muted">
                        La tabla carga solo productos sin imágenes. Cada fila abre el detalle del producto para revisar o completar su ficha.
                    </div>
                </div>
            </div>

            <div class="psi-panel">
                <div class="psi-table-head">
                    <div>
                        <h3><i class="fa fa-table"></i> Listado detallado</h3>
                        <p>Se muestran nombre, clasificación, marca, estado y acceso al detalle.</p>
                    </div>
                </div>

                <div class="psi-table-wrap table-responsive">
                    <table id="tblProductosSinImagenes" class="table table-striped table-hover w-100 mb-0">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Subcategoría</th>
                                <th>Marca</th>
                                <th class="text-right">Precio base</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function () {
                const tabla = $('#tblProductosSinImagenes').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    autoWidth: false,
                    language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
                    ajax: {
                        url: "{{ url('/reporte/productos-sin-imagenes/datos') }}",
                        data: function (data) {
                            data.categoria_id = $('#filtroCategoria').val();
                            data.marca_id = $('#filtroMarca').val();
                        }
                    },
                    columns: [
                        { data: 'codigo_referencia', name: 'codigo_referencia' },
                        { data: 'producto', name: 'producto' },
                        { data: 'categoria', name: 'categoria' },
                        { data: 'sub_categoria', name: 'sub_categoria' },
                        { data: 'marca', name: 'marca' },
                        {
                            data: 'precio_base',
                            name: 'precio_base',
                            className: 'text-right',
                            render: $.fn.dataTable.render.number(',', '.', 2, 'L ')
                        },
                        { data: 'estado', name: 'estado', orderable: false, searchable: false, className: 'text-center' },
                        { data: 'acciones', name: 'acciones', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    order: [[1, 'asc']]
                });

                $('#btnBuscarProductosSinImagenes').on('click', function () {
                    tabla.ajax.reload();
                });

                function exportarProductosSinImagenes(url) {
                    const token = $('meta[name="csrf-token"]').attr('content');
                    const form = $('<form method="POST" style="display:none;"></form>');
                    form.attr('action', url);
                    form.append($('<input type="hidden" name="_token">').val(token));
                    form.append($('<input type="hidden" name="categoria_id">').val($('#filtroCategoria').val()));
                    form.append($('<input type="hidden" name="marca_id">').val($('#filtroMarca').val()));
                    $('body').append(form);
                    form.trigger('submit');
                    form.remove();
                }

                $('#btnExportarExcelProductosSinImagenes').on('click', function () {
                    exportarProductosSinImagenes("{{ url('/reporte/productos-sin-imagenes/exportar-excel') }}");
                });

                $('#btnExportarPdfProductosSinImagenes').on('click', function () {
                    exportarProductosSinImagenes("{{ url('/reporte/productos-sin-imagenes/exportar-pdf') }}");
                });

                $('#btnLimpiarProductosSinImagenes').on('click', function () {
                    $('#filtroCategoria').val('');
                    $('#filtroMarca').val('');
                    tabla.ajax.reload();
                });

                $('#filtroCategoria, #filtroMarca').on('change', function () {
                    tabla.ajax.reload();
                });
            });
        </script>
    @endpush
</div>