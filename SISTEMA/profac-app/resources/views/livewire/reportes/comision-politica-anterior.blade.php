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

        #seccionParametrizacionProductos.show {
            opacity: 1 !important;
            transform: none !important;
            visibility: visible !important;
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
                <div style="margin-bottom: 12px;">
                    <button type="button"
                            id="btnToggleParametrizacion"
                            class="btn btn-outline btn-primary pa-toolbar-btn"
                            data-toggle="collapse"
                            data-target="#seccionParametrizacionProductos"
                            aria-expanded="false"
                            aria-controls="seccionParametrizacionProductos">
                        Mostrar parametrización de productos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="seccionParametrizacionProductos" class="collapse wrapper wrapper-content pt-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="pa-card">
                    <div class="pa-card-header">
                        <div>
                            <h5>Parametrización de Productos Activos</h5>
                            <div class="pa-muted">Use filtros y seleccione en checklist qué productos son miseláneos.</div>
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

                        <hr>

                        <div class="table-responsive pa-table-wrap">
                            <table id="tblCfgProductosMiselaneos" class="table table-striped table-bordered table-hover w-100">
                                <thead>
                                    <tr>
                                        <th style="width:100px;">MISELANEO</th>
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

    <div class="wrapper wrapper-content animated fadeInRight pt-0">
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

    function refreshChecklistStats(){
        if(!dtCfgProductos){
            $('#cfgTotalEnVista').text('0');
            $('#cfgTotalMarcados').text('0');
            return;
        }

        var total = dtCfgProductos.rows({ search: 'applied' }).count();
        var checked = 0;
        dtCfgProductos.rows({ search: 'applied' }).nodes().to$().find('input.cfg-check-miselaneo').each(function(){
            if($(this).is(':checked')) checked++;
        });

        $('#cfgTotalEnVista').text(total);
        $('#cfgTotalMarcados').text(checked);
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
                            var checked = parseInt(row.es_miselaneo || 0, 10) === 1 ? 'checked' : '';
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
        dtCfgProductos.$('input.cfg-check-miselaneo').each(function(){
            var productoId = parseInt($(this).data('productoId') || 0, 10);
            if(productoId <= 0) return;
            items.push({
                producto_id: productoId,
                es_miselaneo: $(this).is(':checked') ? 1 : 0
            });
        });

        if(!items.length){
            Swal.fire({icon:'info',title:'Sin cambios',text:'No hay productos para guardar.'});
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
        }).fail(function(xhr){
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No se pudo guardar el checklist.';
            Swal.fire({icon:'warning',title:'Atención',text:msg});
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

        actualizarInfo(payload, filas);
        initTabla(filas);

        $.when(
            cargarFiltroMarcas(),
            cargarFiltroCategorias(),
            cargarFiltroSubCategorias('')
        ).always(function(){
            cargarTablaParametrizacion();
        });

        var $btnToggle = $('#btnToggleParametrizacion');
        var $seccion = $('#seccionParametrizacionProductos');

        function syncTextoToggle() {
            if ($seccion.hasClass('show')) {
                $btnToggle.text('Ocultar parametrización de productos');
            } else {
                $btnToggle.text('Mostrar parametrización de productos');
            }
        }

        syncTextoToggle();
        $seccion.on('shown.bs.collapse hidden.bs.collapse', syncTextoToggle);

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
