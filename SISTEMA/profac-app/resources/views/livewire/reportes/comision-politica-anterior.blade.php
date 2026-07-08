<div>
    <style>
        .pa-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
            margin-bottom: 16px;
        }

        .pa-card-header {
            padding: 14px 16px;
            border-bottom: 1px solid #eef2f7;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .pa-card-header h5 {
            margin: 0;
            font-size: 19px;
            font-weight: 700;
            color: #334155;
        }

        .pa-card-body {
            padding: 14px 16px 16px;
        }

        .pa-toolbar-btn {
            border-radius: 8px;
            font-weight: 600;
            height: 38px;
            padding: 0 14px;
        }

        .pa-muted {
            color: #64748b;
            font-size: 12px;
        }

        .pa-filter-label {
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 6px;
            display: block;
        }

        .pa-table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
        }

        #tblCfgProductosMiselaneos thead th,
        #tblNoMiselaneosRegistrados thead th,
        #tblPoliticaAnterior thead th {
            background: #f8fafc;
            color: #334155;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .pa-inline-stats {
            font-size: 12px;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .pa-inline-stats strong {
            color: #0f172a;
        }

        .pa-main-tabs .nav-link {
            font-weight: 700;
        }
    </style>

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <h2>Comisión por Política Anterior</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="/comisiones/general">/ Comisiones General</a>
                </li>
                <li class="breadcrumb-item active">
                    <strong>Política Anterior</strong>
                </li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="alert alert-info" id="paInfo">
                    Cargando facturas seleccionadas desde Comisiones General...
                </div>
                <ul class="nav nav-tabs pa-main-tabs" role="tablist" style="margin-bottom:12px;">
                    <li class="nav-item" role="presentation">
                        <a href="#" class="nav-link active pa-main-tab" data-target="tabFacturas">Facturas para política anterior</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="#" class="nav-link pa-main-tab" data-target="tabParametrizacion">Parametrización de productos</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="#" class="nav-link pa-main-tab" data-target="tabRegistrados">Productos no miseláneos registrados</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div id="tabParametrizacion" class="wrapper wrapper-content pt-0 pa-tab-section d-none">
        <div class="row">
            <div class="col-lg-12">
                <div class="pa-card">
                    <div class="pa-card-header">
                        <div>
                            <h5>Parametrización de Productos Activos</h5>
                            <div class="pa-muted">Use filtros y seleccione en checklist qué productos son NO miseláneos.</div>
                        </div>
                        <div class="pa-inline-stats">
                            <span>En vista: <strong id="cfgTotalEnVista">0</strong></span>
                            <span>Marcados: <strong id="cfgTotalMarcados">0</strong></span>
                        </div>
                    </div>
                    <div class="pa-card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3 mb-2">
                                <label class="pa-filter-label">Marca</label>
                                <select id="fltMarca" class="form-control">
                                    <option value="">Todas</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="pa-filter-label">Categoría</label>
                                <select id="fltCategoria" class="form-control">
                                    <option value="">Todas</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="pa-filter-label">Sub categoría</label>
                                <select id="fltSubCategoria" class="form-control">
                                    <option value="">Todas</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="pa-filter-label">Buscar producto</label>
                                <input type="text" id="fltBusquedaProducto" class="form-control" placeholder="ID, nombre o código de barra">
                            </div>
                        </div>

                        <div class="row align-items-end mt-2">
                            <div class="col-md-3 mb-2">
                                <button type="button" id="btnAplicarFiltrosChecklist" class="btn btn-default btn-block pa-toolbar-btn">
                                    Aplicar filtros
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button type="button" id="btnLimpiarFiltrosChecklist" class="btn btn-outline btn-default btn-block pa-toolbar-btn">
                                    Limpiar filtros
                                </button>
                            </div>
                            <div class="col-md-6 mb-2 text-right" style="display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap;">
                                <button type="button" id="btnSeleccionarTodosVista" class="btn btn-outline btn-default pa-toolbar-btn">
                                    Seleccionar todos
                                </button>
                                <button type="button" id="btnQuitarTodosVista" class="btn btn-outline btn-default pa-toolbar-btn">
                                    Quitar todos
                                </button>
                                <button type="button" id="btnGuardarChecklist" class="btn btn-primary pa-toolbar-btn">
                                    Guardar checklist
                                </button>
                            </div>
                        </div>

                        <div class="row mt-1">
                            <div class="col-12">
                                <div class="pa-muted">Solo se listan productos activos que aún no están registrados como NO miseláneos.</div>
                            </div>
                        </div>

                        <div class="row align-items-end mt-2">
                            <div class="col-md-5 mb-2">
                                <label class="pa-filter-label">Cargar Excel de productos NO MISELANEO</label>
                                <input type="file" id="inpExcelNoMiselaneos" class="form-control" accept=".xlsx,.xls,.csv">
                            </div>
                            <div class="col-md-3 mb-2">
                                <button type="button" id="btnImportarExcelNoMiselaneos" class="btn btn-success btn-block pa-toolbar-btn">
                                    Cargar no miseláneos
                                </button>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="pa-muted">El archivo debe contener IDs de producto. Solo se procesan productos activos.</div>
                            </div>
                        </div>

                        <hr>

                        <div class="table-responsive pa-table-wrap">
                            <table id="tblCfgProductosMiselaneos" class="table table-striped table-bordered table-hover w-100">
                                <thead>
                                    <tr>
                                        <th style="width:130px;">NO MISELANEO</th>
                                        <th>ID PRODUCTO</th>
                                        <th>PRODUCTO</th>
                                        <th>MARCA</th>
                                        <th>CATEGORÍA</th>
                                        <th>SUB CATEGORÍA</th>
                                        <th>ACTUALIZADO</th>
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

    <div id="tabRegistrados" class="wrapper wrapper-content animated fadeInRight pt-0 pa-tab-section d-none">
        <div class="row">
            <div class="col-lg-12">
                <div class="pa-card">
                    <div class="pa-card-header">
                        <div>
                            <h5 style="font-size:16px;">Productos NO Miseláneos Registrados</h5>
                            <div class="pa-muted">Histórico de registrados. Puede quitar o reactivar productos.</div>
                        </div>
                        <div style="min-width:220px;">
                            <label class="pa-filter-label" style="margin-bottom:4px;">Mostrar</label>
                            <select id="fltEstadoNoMiselaneo" class="form-control">
                                <option value="todos" selected>Todos</option>
                                <option value="activos">Activos</option>
                                <option value="inactivos">Inactivos</option>
                            </select>
                        </div>
                    </div>
                    <div class="pa-card-body">
                        <div class="table-responsive pa-table-wrap">
                            <table id="tblNoMiselaneosRegistrados" class="table table-striped table-bordered table-hover w-100">
                                <thead>
                                    <tr>
                                        <th>ID PRODUCTO</th>
                                        <th>PRODUCTO</th>
                                        <th>MARCA</th>
                                        <th>CATEGORÍA</th>
                                        <th>SUB CATEGORÍA</th>
                                        <th>ESTADO</th>
                                        <th>ACTUALIZADO</th>
                                        <th style="width:120px;">ACCIÓN</th>
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

    <div id="tabFacturas" class="wrapper wrapper-content animated fadeInRight pt-0 pa-tab-section">
        <div class="row">
            <div class="col-lg-12">
                <div class="pa-card">
                    <div class="pa-card-header">
                        <div>
                            <h5 style="font-size:16px;">Facturas Para Política Anterior</h5>
                            <div class="pa-muted">Estas son las facturas cargadas desde Comisiones General para este flujo.</div>
                        </div>
                    </div>
                    <div class="pa-card-body">
                        <div class="row align-items-center" style="margin-bottom:10px;">
                            <div class="col-md-8">
                                <div class="pa-muted">Calcule comisión sobre estas facturas usando la lista de productos no miseláneos parametrizada.</div>
                            </div>
                            <div class="col-md-4 text-right">
                                <button type="button" id="btnCalcularComisionesFacturas" class="btn btn-primary pa-toolbar-btn">
                                    Calcular comisión
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive pa-table-wrap">
                            <table id="tblPoliticaAnterior" class="table table-striped table-bordered table-hover w-100">
                                <thead>
                                    <tr>
                                        <th>FECHA PAGO</th>
                                        <th>FECHA CREACIÓN FACTURA</th>
                                        <th>FACTURA</th>
                                        <th>CLIENTE</th>
                                        <th>CAPACIDAD</th>
                                        <th>USUARIO</th>
                                        <th>RAZÓN NO COMISIONABLE</th>
                                        <th>DETALLE TÉCNICO</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div id="seccionDetalleComision" class="d-none" style="margin-top:14px;">
                            <div class="row" style="margin-bottom:10px;">
                                <div class="col-md-3"><div class="well well-sm" style="margin-bottom:0;"><strong>Líneas:</strong> <span id="resTotalLineas">0</span></div></div>
                                <div class="col-md-3"><div class="well well-sm" style="margin-bottom:0;"><strong>Comisión no miselánea:</strong> <span id="resTotalNoMiselaneo">0.00</span></div></div>
                                <div class="col-md-3"><div class="well well-sm" style="margin-bottom:0;"><strong>Comisión miselánea:</strong> <span id="resTotalMiselanea">0.00</span></div></div>
                                <div class="col-md-3"><div class="well well-sm" style="margin-bottom:0;"><strong>Total comisión:</strong> <span id="resTotalComision">0.00</span></div></div>
                            </div>

                            <div class="table-responsive pa-table-wrap">
                                <table id="tblDetalleComisionPoliticaAnterior" class="table table-striped table-bordered table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th>FACTURA</th>
                                            <th>ID FACTURA</th>
                                            <th>FECHA FACTURA</th>
                                            <th>ID PRODUCTO</th>
                                            <th>PRODUCTO</th>
                                            <th>TIPO PAGO</th>
                                            <th>SUBTOTAL LÍNEA</th>
                                            <th>CLASIFICACIÓN</th>
                                            <th>% APLICADO</th>
                                            <th>COM. TOTAL LÍNEA</th>
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
    </div>
</div>

@push('scripts')
<script>
(function(){
    var dtCfgProductos = null;
    var dtNoMiselaneos = null;
    var dtDetalleComision = null;
    var facturasPoliticaAnterior = [];

    function activarTab(tabId){
        $('.pa-tab-section').addClass('d-none');
        $('#' + tabId).removeClass('d-none');

        $('.pa-main-tab').removeClass('active');
        $('.pa-main-tab[data-target="' + tabId + '"]').addClass('active');

        setTimeout(function(){
            if($.fn.DataTable.isDataTable('#tblPoliticaAnterior')){
                $('#tblPoliticaAnterior').DataTable().columns.adjust();
            }
            if($.fn.DataTable.isDataTable('#tblDetalleComisionPoliticaAnterior')){
                $('#tblDetalleComisionPoliticaAnterior').DataTable().columns.adjust();
            }
            if($.fn.DataTable.isDataTable('#tblCfgProductosMiselaneos')){
                $('#tblCfgProductosMiselaneos').DataTable().columns.adjust();
            }
            if($.fn.DataTable.isDataTable('#tblNoMiselaneosRegistrados')){
                $('#tblNoMiselaneosRegistrados').DataTable().columns.adjust();
            }
        }, 100);
    }

    function updateGuardarChecklistState(){
        var checked = 0;
        if(dtCfgProductos){
            checked = dtCfgProductos.rows().nodes().to$().find('input.cfg-check-miselaneo:checked').length;
        }

        var $btn = $('#btnGuardarChecklist');
        $btn.prop('disabled', checked === 0);
    }

    function refreshChecklistStats(){
        if(!dtCfgProductos){
            $('#cfgTotalEnVista').text('0');
            $('#cfgTotalMarcados').text('0');
            updateGuardarChecklistState();
            return;
        }

        var total = dtCfgProductos.rows({ search: 'applied' }).count();
        var checked = 0;
        dtCfgProductos.rows({ search: 'applied' }).nodes().to$().find('input.cfg-check-miselaneo').each(function(){
            if($(this).is(':checked')) checked++;
        });

        $('#cfgTotalEnVista').text(total);
        $('#cfgTotalMarcados').text(checked);
        updateGuardarChecklistState();
    }

    function html(s){
        return String(s || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;');
    }

    function obtenerPayload(){
        try {
            var raw = sessionStorage.getItem('comisionPoliticaAnteriorPayload');
            if(!raw) return null;
            return JSON.parse(raw);
        } catch(e){
            return null;
        }
    }

    function actualizarInfo(payload, filas){
        var info = document.getElementById('paInfo');
        if(!info) return;

        if(!filas.length){
            info.className = 'alert alert-warning';
            info.innerHTML = 'No se encontraron facturas para política anterior. Regrese a Comisiones General y presione Calcular desde la tabla de facturas para política anterior.';
            return;
        }

        var usuario = payload && payload.usuario_id ? ('Usuario ID: ' + payload.usuario_id) : 'Usuario seleccionado en Comisiones General';
        var rango = ((payload && payload.fecha_inicio) ? payload.fecha_inicio : 'N/A') + ' a ' + ((payload && payload.fecha_final) ? payload.fecha_final : 'N/A');
        var facturasUnicas = new Set(filas.map(function(r){ return String(r.factura_id || r.factura || ''); })).size;

        info.className = 'alert alert-success';
        info.innerHTML = '<b>Facturas cargadas correctamente.</b> '
            + 'Rango: ' + html(rango)
            + ' | ' + html(usuario)
            + ' | Facturas únicas: <b>' + facturasUnicas + '</b>'
            + ' | Registros: <b>' + filas.length + '</b>';
    }

    function initTabla(filas){
        if ($.fn.DataTable.isDataTable('#tblPoliticaAnterior')) {
            $('#tblPoliticaAnterior').DataTable().destroy();
            $('#tblPoliticaAnterior tbody').empty();
        }

        $('#tblPoliticaAnterior').DataTable({
            data: filas,
            order: [[0, 'desc'], [2, 'asc']],
            paging: true,
            pageLength: 10,
            responsive: true,
            scrollX: true,
            dom: '<"html5buttons"B>lfgitp',
            buttons: [
                {
                    extend: 'excel',
                    title: 'FACTURAS_POLITICA_ANTERIOR',
                    className: 'btn btn-success'
                }
            ],
            columns: [
                { data: 'fecha_pago', defaultContent: '—' },
                { data: 'fecha_creacion_factura', defaultContent: '—' },
                { data: 'factura', defaultContent: '—' },
                { data: 'cliente', defaultContent: '—' },
                { data: 'capacidad', defaultContent: '—' },
                { data: 'usuario', defaultContent: '—' },
                { data: 'razon_no_comisionable', defaultContent: '—' },
                {
                    data: 'motivos',
                    render: function(d){
                        if(Array.isArray(d)) return html(d.join(' | '));
                        return d ? html(d) : '—';
                    }
                }
            ],
            language: {
                processing: 'Cargando...',
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                infoFiltered: '(filtrado de _MAX_ registros)',
                zeroRecords: 'No se encontraron resultados',
                emptyTable: 'No hay datos disponibles',
                paginate: {
                    first: 'Primero',
                    last: 'Último',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            }
        });
    }

    function money(v){
        var n = parseFloat(v || 0);
        return n.toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function getFacturaIdsParaCalculo(){
        var ids = [];
        (facturasPoliticaAnterior || []).forEach(function(row){
            var id = parseInt(row && row.factura_id ? row.factura_id : 0, 10);
            if(id > 0) ids.push(id);
        });

        return Array.from(new Set(ids));
    }

    function renderTablaDetalleComision(filas){
        if ($.fn.DataTable.isDataTable('#tblDetalleComisionPoliticaAnterior')) {
            dtDetalleComision.clear();
            dtDetalleComision.rows.add(filas).draw();
            return;
        }

        dtDetalleComision = $('#tblDetalleComisionPoliticaAnterior').DataTable({
            data: filas,
            order: [[2, 'desc'], [0, 'asc']],
            pageLength: 15,
            responsive: true,
            autoWidth: false,
            dom: '<"html5buttons"B>lfgitp',
            buttons: [
                {
                    extend: 'excel',
                    title: 'DETALLE_COMISION_POLITICA_ANTERIOR',
                    className: 'btn btn-success'
                }
            ],
            language: {
                search: 'Buscar detalle:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_',
                infoEmpty: 'Sin registros',
                zeroRecords: 'No hay líneas para mostrar',
                paginate: { first:'Primero', last:'Último', next:'Siguiente', previous:'Anterior' }
            },
            columns: [
                { data: 'factura' },
                { data: 'factura_id' },
                { data: 'fecha_factura' },
                { data: 'producto_id' },
                { data: 'producto', render: function(d){ return html(d); } },
                { data: 'tipo_pago' },
                { data: 'subtotal_linea', className: 'text-right', render: function(d){ return money(d); } },
                { data: 'clasificacion' },
                { data: 'porcentaje_aplicado', className: 'text-right', render: function(d){ return money(d) + '%'; } },
                { data: 'comision_total_linea', className: 'text-right', render: function(d){ return money(d); } }
            ]
        });
    }

    function calcularComisionesFacturas(){
        var facturaIds = getFacturaIdsParaCalculo();
        if(!facturaIds.length){
            Swal.fire({icon:'info',title:'Sin facturas',text:'No hay facturas válidas para calcular comisiones.'});
            return;
        }

        $.ajax({
            url: '/comision/politica-anterior/calcular-comisiones',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content') || '',
                factura_ids: facturaIds
            }
        }).done(function(resp){
            var filas = Array.isArray(resp && resp.detalle) ? resp.detalle : [];
            var tot = resp && resp.totales ? resp.totales : {};

            renderTablaDetalleComision(filas);

            $('#resTotalLineas').text(tot.total_lineas || 0);
            $('#resTotalNoMiselaneo').text(money(tot.total_comision_no_miselaneo || 0));
            $('#resTotalMiselanea').text(money(tot.total_comision_miselanea || 0));
            $('#resTotalComision').text(money(tot.total_comision || 0));
            $('#seccionDetalleComision').removeClass('d-none');

            Swal.fire({icon:'success',title:'Cálculo completado',text:(resp && resp.message) ? resp.message : 'Comisiones calculadas correctamente.'});
        }).fail(function(xhr){
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No se pudo calcular la comisión.';
            Swal.fire({icon:'error',title:'Error',text:msg});
        });
    }

    function cargarFiltroMarcas(){
        return $.getJSON('/comision/politica-anterior/catalogo/marcas', function(data){
            var htmlOpts = '<option value="">Todas</option>';
            (data || []).forEach(function(row){
                htmlOpts += '<option value="'+row.id+'">'+html(row.nombre)+'</option>';
            });
            $('#fltMarca').html(htmlOpts);
        });
    }

    function cargarFiltroCategorias(){
        return $.getJSON('/comision/politica-anterior/catalogo/categorias', function(data){
            var htmlOpts = '<option value="">Todas</option>';
            (data || []).forEach(function(row){
                htmlOpts += '<option value="'+row.id+'">'+html(row.nombre)+'</option>';
            });
            $('#fltCategoria').html(htmlOpts);
        });
    }

    function cargarFiltroSubCategorias(categoriaId){
        var params = categoriaId ? {categoria_id: categoriaId} : {};
        return $.getJSON('/comision/politica-anterior/catalogo/sub-categorias', params, function(data){
            var htmlOpts = '<option value="">Todas</option>';
            (data || []).forEach(function(row){
                htmlOpts += '<option value="'+row.id+'">'+html(row.nombre)+'</option>';
            });
            $('#fltSubCategoria').html(htmlOpts);
        });
    }

    function getFiltrosChecklist(){
        return {
            q: ($('#fltBusquedaProducto').val() || '').trim(),
            marca_id: $('#fltMarca').val() || '',
            categoria_id: $('#fltCategoria').val() || '',
            sub_categoria_id: $('#fltSubCategoria').val() || ''
        };
    }

    function cargarTablaParametrizacion(){
        $.getJSON('/comision/politica-anterior/parametrizacion-checklist', getFiltrosChecklist(), function(resp){
            var filas = Array.isArray(resp && resp.data) ? resp.data : [];

            if ($.fn.DataTable.isDataTable('#tblCfgProductosMiselaneos')) {
                dtCfgProductos.clear();
                dtCfgProductos.rows.add(filas).draw();
                refreshChecklistStats();
                return;
            }

            dtCfgProductos = $('#tblCfgProductosMiselaneos').DataTable({
                data: filas,
                order: [[2, 'asc']],
                pageLength: 10,
                responsive: true,
                autoWidth: false,
                language: {
                    search: 'Buscar en checklist:',
                    lengthMenu: 'Mostrar _MENU_ registros',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_',
                    infoEmpty: 'Sin registros',
                    zeroRecords: 'No hay productos con esos filtros',
                    paginate: { first:'Primero', last:'Último', next:'Siguiente', previous:'Anterior' }
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(row){
                            var checked = parseInt(row.es_no_miselaneo || 0, 10) === 1 ? 'checked' : '';
                            return '<input type="checkbox" class="cfg-check-miselaneo" data-producto-id="'+row.producto_id+'" '+checked+'>';
                        }
                    },
                    { data: 'producto_id' },
                    { data: 'producto', render: function(d){ return html(d); } },
                    { data: 'marca', render: function(d){ return html(d || 'SIN MARCA'); } },
                    { data: 'categoria', render: function(d){ return html(d || '—'); } },
                    { data: 'sub_categoria', render: function(d){ return html(d || '—'); } },
                    { data: 'updated_at', render: function(d){ return html(d || '—'); } }
                ]
            });

            $('#tblCfgProductosMiselaneos').on('change', 'input.cfg-check-miselaneo', function(){
                refreshChecklistStats();
            });

            dtCfgProductos.on('draw', refreshChecklistStats);
            refreshChecklistStats();
        }).fail(function(){
            Swal.fire({icon:'error',title:'Error',text:'No se pudo cargar la parametrización de productos.'});
        });
    }

    function cargarTablaRegistrados(){
        var estado = $('#fltEstadoNoMiselaneo').val() || 'todos';

        $.getJSON('/comision/politica-anterior/no-miselaneos-registrados', { estado: estado }, function(resp){
            var filas = Array.isArray(resp && resp.data) ? resp.data : [];

            if ($.fn.DataTable.isDataTable('#tblNoMiselaneosRegistrados')) {
                dtNoMiselaneos.clear();
                dtNoMiselaneos.rows.add(filas).draw();
                return;
            }

            dtNoMiselaneos = $('#tblNoMiselaneosRegistrados').DataTable({
                data: filas,
                order: [[6, 'desc']],
                pageLength: 10,
                responsive: true,
                autoWidth: false,
                language: {
                    search: 'Buscar registrados:',
                    lengthMenu: 'Mostrar _MENU_ registros',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_',
                    infoEmpty: 'Sin registros',
                    zeroRecords: 'No hay productos registrados',
                    paginate: { first:'Primero', last:'Último', next:'Siguiente', previous:'Anterior' }
                },
                columns: [
                    { data: 'producto_id' },
                    { data: 'producto', render: function(d){ return html(d); } },
                    { data: 'marca', render: function(d){ return html(d || 'SIN MARCA'); } },
                    { data: 'categoria', render: function(d){ return html(d || '—'); } },
                    { data: 'sub_categoria', render: function(d){ return html(d || '—'); } },
                    {
                        data: 'estado',
                        render: function(d, t, row){
                            if(parseInt(row.estado_id || 0, 10) === 1){
                                return '<span class="label label-primary">ACTIVO</span>';
                            }
                            return '<span class="label label-default">INACTIVO</span>';
                        }
                    },
                    { data: 'updated_at', render: function(d){ return html(d || '—'); } },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(row){
                            var activo = parseInt(row.estado_id || 0, 10) === 1;
                            if(activo){
                                return '<button type="button" class="btn btn-xs btn-danger btn-toggle-no-miselaneo" data-accion="quitar" data-producto-id="'+row.producto_id+'">Quitar</button>';
                            }
                            return '<button type="button" class="btn btn-xs btn-success btn-toggle-no-miselaneo" data-accion="activar" data-producto-id="'+row.producto_id+'">Reactivar</button>';
                        }
                    }
                ]
            });
        }).fail(function(){
            Swal.fire({icon:'error',title:'Error',text:'No se pudo cargar la tabla de no miseláneos registrados.'});
        });
    }

    function guardarParametrizacion(productoId, tipo){
        $.ajax({
            url: '/comision/politica-anterior/parametrizacion',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content') || '',
                producto_id: productoId,
                tipo: tipo
            }
        }).done(function(resp){
            Swal.fire({icon:'success',title:'Guardado',text:(resp && resp.message) ? resp.message : 'Parametrización guardada.'});
            cargarTablaParametrizacion();
        }).fail(function(xhr){
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No se pudo guardar la parametrización.';
            Swal.fire({icon:'warning',title:'Atención',text:msg});
        });
    }

    function guardarChecklist(){
        if(!dtCfgProductos){
            Swal.fire({icon:'info',title:'Sin datos',text:'No hay productos cargados en el checklist.'});
            return;
        }

        var items = [];
        dtCfgProductos.$('input.cfg-check-miselaneo:checked').each(function(){
            var productoId = parseInt($(this).data('productoId') || 0, 10);
            if(productoId <= 0) return;
            items.push({
                producto_id: productoId
            });
        });

        if(!items.length){
            Swal.fire({icon:'info',title:'Sin selección',text:'Debe seleccionar al menos un producto para guardar.'});
            return;
        }

        $.ajax({
            url: '/comision/politica-anterior/parametrizacion-checklist',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content') || '',
                items: items
            }
        }).done(function(resp){
            var procesados = resp && resp.procesados ? resp.procesados : items.length;
            Swal.fire({icon:'success',title:'Guardado',text:'Checklist guardado correctamente. Productos procesados: ' + procesados});
            cargarTablaParametrizacion();
            cargarTablaRegistrados();
        }).fail(function(xhr){
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No se pudo guardar el checklist.';
            Swal.fire({icon:'warning',title:'Atención',text:msg});
        });
    }

    function actualizarEstadoNoMiselaneo(productoId, accion){
        $.ajax({
            url: '/comision/politica-anterior/no-miselaneos/estado',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content') || '',
                producto_id: productoId,
                accion: accion
            }
        }).done(function(resp){
            Swal.fire({icon:'success',title:'Actualizado',text:(resp && resp.message) ? resp.message : 'Registro actualizado.'});
            cargarTablaRegistrados();
            cargarTablaParametrizacion();
        }).fail(function(xhr){
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No se pudo actualizar el producto.';
            Swal.fire({icon:'warning',title:'Atención',text:msg});
        });
    }

    function importarExcelNoMiselaneos(){
        var input = document.getElementById('inpExcelNoMiselaneos');
        var file = input && input.files && input.files.length ? input.files[0] : null;

        if(!file){
            Swal.fire({icon:'info',title:'Archivo requerido',text:'Seleccione un archivo Excel para cargar productos no miseláneos.'});
            return;
        }

        var formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content') || '');
        formData.append('archivo_excel', file);

        $.ajax({
            url: '/comision/politica-anterior/parametrizacion/importar-no-miselaneos',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
        }).done(function(resp){
            var resumen = resp && resp.resumen ? resp.resumen : {};
            var yaExistian = Array.isArray(resp && resp.productos_omitidos_ya_existian)
                ? resp.productos_omitidos_ya_existian
                : [];

            var htmlMsg = '<div style="text-align:left">'
                + '<div><b>Insertados:</b> ' + (resumen.insertados || 0) + '</div>'
                + '<div><b>Actualizados a NO MISELANEO:</b> ' + (resumen.actualizados_a_no_miselaneo || 0) + '</div>'
                + '<div><b>Omitidos por ya existir NO MISELANEO en BD:</b> ' + (resumen.omitidos_ya_no_miselaneos || 0) + '</div>'
                + '<div><b>Omitidos por inactivos/no existentes:</b> ' + (resumen.omitidos_no_activos || 0) + '</div>'
                + '<div><b>Filas inválidas:</b> ' + (resumen.filas_invalidas || 0) + '</div>'
                + '</div>';

            if(yaExistian.length){
                var preview = yaExistian.slice(0, 30).map(function(row){
                    var id = row && row.producto_id ? row.producto_id : '';
                    var nombre = row && row.producto ? (' - ' + row.producto) : '';
                    return id + nombre;
                }).join('<br>');
                if(yaExistian.length > 30){
                    preview += '<br>...';
                }
                htmlMsg += '<hr><div style="text-align:left"><b>Productos ya registrados previamente como NO MISELANEO:</b><br>' + preview + '</div>';
            }

            Swal.fire({
                icon: yaExistian.length ? 'warning' : 'success',
                title: yaExistian.length ? 'Carga completada con omisiones' : 'Carga completada',
                html: htmlMsg
            });

            if(input){
                input.value = '';
            }
            cargarTablaParametrizacion();
            cargarTablaRegistrados();
        }).fail(function(xhr){
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No se pudo procesar el archivo.';
            Swal.fire({icon:'error',title:'Error de carga',text:msg});
        });
    }

    function marcarTodosEnVista(valor){
        if(!dtCfgProductos) return;
        dtCfgProductos.rows({ search: 'applied' }).nodes().to$().find('input.cfg-check-miselaneo').prop('checked', !!valor);
        refreshChecklistStats();
    }

    $(document).ready(function(){
        var payload = obtenerPayload();
        var filas = payload && Array.isArray(payload.filas) ? payload.filas : [];
        facturasPoliticaAnterior = filas;

        actualizarInfo(payload, filas);
        initTabla(filas);

        $.when(
            cargarFiltroMarcas(),
            cargarFiltroCategorias(),
            cargarFiltroSubCategorias('')
        ).always(function(){
            cargarTablaParametrizacion();
            cargarTablaRegistrados();
            updateGuardarChecklistState();
        });

        activarTab('tabFacturas');

        $('.pa-main-tab').on('click', function(e){
            e.preventDefault();
            var tabId = String($(this).data('target') || '');
            if(!tabId) return;
            activarTab(tabId);
        });

        $('#btnCalcularComisionesFacturas').on('click', function(){
            calcularComisionesFacturas();
        });

        $('#fltCategoria').on('change', function(){
            cargarFiltroSubCategorias($(this).val() || '');
        });

        $('#btnAplicarFiltrosChecklist').on('click', function(){
            cargarTablaParametrizacion();
        });

        $('#btnLimpiarFiltrosChecklist').on('click', function(){
            $('#fltMarca').val('');
            $('#fltCategoria').val('');
            $('#fltSubCategoria').html('<option value="">Todas</option>').val('');
            $('#fltBusquedaProducto').val('');
            cargarFiltroSubCategorias('');
            cargarTablaParametrizacion();
        });

        $('#btnGuardarChecklist').on('click', function(){
            guardarChecklist();
        });

        $('#fltEstadoNoMiselaneo').on('change', function(){
            cargarTablaRegistrados();
        });

        $('#tblNoMiselaneosRegistrados').on('click', '.btn-toggle-no-miselaneo', function(){
            var productoId = parseInt($(this).data('productoId') || 0, 10);
            var accion = String($(this).data('accion') || '');
            if(productoId <= 0) return;

            if(accion !== 'quitar' && accion !== 'activar') return;

            var titulo = accion === 'quitar' ? 'Quitar producto' : 'Reactivar producto';
            var texto = accion === 'quitar'
                ? '¿Desea quitar este producto de la lista de NO miseláneos?'
                : '¿Desea reactivar este producto en la lista de NO miseláneos?';
            var confirmar = accion === 'quitar' ? 'Sí, quitar' : 'Sí, reactivar';

            Swal.fire({
                icon: 'question',
                title: titulo,
                text: texto,
                showCancelButton: true,
                confirmButtonText: confirmar,
                cancelButtonText: 'Cancelar'
            }).then(function(result){
                if(result && result.isConfirmed){
                    actualizarEstadoNoMiselaneo(productoId, accion);
                }
            });
        });

        $('#btnImportarExcelNoMiselaneos').on('click', function(){
            importarExcelNoMiselaneos();
        });

        $('#btnSeleccionarTodosVista').on('click', function(){
            marcarTodosEnVista(true);
        });

        $('#btnQuitarTodosVista').on('click', function(){
            marcarTodosEnVista(false);
        });
    });
})();
</script>
@endpush
