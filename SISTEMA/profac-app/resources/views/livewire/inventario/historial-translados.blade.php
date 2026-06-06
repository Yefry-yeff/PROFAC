<div>
    @push('styles')

    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Historial de Traslados de Bodega</h2>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a>Listado </a>
                        </li>


                    </ol>
                </div>


            </div>


        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3>Seleccionar Rango de Fechas</h3>
                    </div>
                    <div class="ibox-content ">

                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                                <label for="fechaInicio" class="col-form-label focus-label">Fecha de inicio</label>
                                <input id="fechaInicio" type="date" value="{{ $fechaInicio }}" class="form-group form-control">
                            </div>

                            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                                <label for="fechaFinal" class="col-form-label focus-label">Fecha Final</label>
                                <input id="fechaFinal" type="date" value="{{ date('Y-m-t') }}" class="form-group form-control">
                            </div>


                        </div>
                        <div>
                            <button onclick="ajustesPorfecha()" class="btn btn-primary mt-3"><i
                                    class="fa-solid fa-arrow-rotate-right"></i> Solicitar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title" style="padding:0; border-bottom:none;">
                        <ul class="nav nav-tabs" id="historialTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-traslado-link"
                                   data-toggle="tab" href="#pane-traslado" role="tab">
                                    <i class="fa-solid fa-boxes-stacked mr-1"></i> Por Traslado
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-producto-link"
                                   data-toggle="tab" href="#pane-producto" role="tab">
                                    <i class="fa-solid fa-box mr-1"></i> Por Producto
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="ibox-content">
                        <div class="tab-content mt-2">

                            {{-- Tab 1: Por Traslado --}}
                            <div class="tab-pane fade show active" id="pane-traslado" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="tbl_por_traslado"
                                           class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>N° Traslado</th>
                                                <th>Comentario / Motivo</th>
                                                <th>Usuario</th>
                                                <th>Fecha</th>
                                                <th>Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Tab 2: Por Producto --}}
                            <div class="tab-pane fade" id="pane-producto" role="tabpanel">
                                <div class="row mb-3 align-items-end">
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <label class="col-form-label">Buscar Producto</label>
                                        <input type="text" id="filtro_q" class="form-control"
                                               placeholder="Ej: bolsa concept (palabras separadas)">
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-2">
                                        <label class="col-form-label">N° Traslado</label>
                                        <input type="text" id="filtro_num_traslado" class="form-control"
                                               placeholder="Ej: 2026-5">
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-2">
                                        <label class="col-form-label">Bodega Origen</label>
                                        <select id="filtro_bodega_origen" class="form-control">
                                            <option value="">-- Todas --</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-2">
                                        <label class="col-form-label">Bodega Destino</label>
                                        <select id="filtro_bodega_destino" class="form-control">
                                            <option value="">-- Todas --</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3" style="padding-top:28px; display:flex; gap:6px;">
                                        <button onclick="aplicarFiltrosProducto()" class="btn btn-info btn-sm">
                                            <i class="fa fa-filter"></i> Filtrar
                                        </button>
                                        <button onclick="limpiarFiltrosProducto()" class="btn btn-default btn-sm">
                                            <i class="fa fa-times"></i> Limpiar
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="tbl_por_producto"
                                           class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>N° Traslado</th>
                                                <th>ID Producto</th>
                                                <th>Producto</th>
                                                <th>Cantidad</th>
                                                <th>Origen (Bodega / Segmento / Sección)</th>
                                                <th>Destino (Bodega / Segmento / Sección)</th>
                                                <th>Usuario</th>
                                                <th>Fecha</th>
                                                <th>Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                        </div>{{-- /tab-content --}}
                    </div>{{-- /ibox-content --}}
                </div>{{-- /ibox --}}
            </div>
        </div>
    </div>





    @push('scripts')
    <script>
        var fechaInicio         = "{{ $fechaInicio }}";
        var fechaFinal          = "{{ date('Y-m-t') }}";
        var tblTraslado         = null;
        var tblProducto         = null;
        var filtroQ             = '';
        var filtroNumTraslado   = '';
        var filtroBodegaOrigen  = '';
        var filtroBodegaDestino = '';

        $(document).ready(function () {
            cargarBodegas();
            tablaTraslado();
        });

        $('#tab-producto-link').on('shown.bs.tab', function () {
            if (!tblProducto) tablaProducto();
        });

        function cargarBodegas() {
            axios.get('/translados/bodegas').then(function (r) {
                var opts = '<option value="">-- Todas --<\/option>';
                r.data.forEach(function (b) {
                    opts += '<option value="' + b.id + '">' + b.nombre + '<\/option>';
                });
                document.getElementById('filtro_bodega_origen').innerHTML  = opts;
                document.getElementById('filtro_bodega_destino').innerHTML = opts;
            }).catch(function () {});
        }

        function tablaTraslado() {
            if ($.fn.DataTable.isDataTable('#tbl_por_traslado')) {
                $('#tbl_por_traslado').DataTable().clear().destroy();
            }
            tblTraslado = $('#tbl_por_traslado').DataTable({
                order: [[3, 'desc']],
                language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
                pageLength: 15,
                responsive: true,
                ajax: {
                    url: '/translados/obtener/por-traslado',
                    type: 'POST',
                    data: function (d) {
                        d.fechaInicio = fechaInicio;
                        d.fechaFinal  = fechaFinal;
                        d._token      = "{{ csrf_token() }}";
                    },
                    error: function (xhr) {
                        console.error('Error tblTraslado:', xhr.status, xhr.responseText);
                    }
                },
                columns: [
                    { data: 'codigo' },
                    { data: 'comentario' },
                    { data: 'name' },
                    { data: 'fecha' },
                    { data: 'opciones', orderable: false, searchable: false },
                ]
            });
        }

        function tablaProducto() {
            if ($.fn.DataTable.isDataTable('#tbl_por_producto')) {
                $('#tbl_por_producto').DataTable().clear().destroy();
            }
            tblProducto = $('#tbl_por_producto').DataTable({
                order: [[7, 'desc']],
                language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
                pageLength: 5,
                dom: 'rtip',
                responsive: true,
                ajax: {
                    url: '/translados/obtener/listado',
                    type: 'POST',
                    data: function (d) {
                        d.fechaInicio       = fechaInicio;
                        d.fechaFinal        = fechaFinal;
                        d.q                 = filtroQ;
                        d.num_traslado      = filtroNumTraslado;
                        d.bodega_origen_id  = filtroBodegaOrigen;
                        d.bodega_destino_id = filtroBodegaDestino;
                        d._token            = "{{ csrf_token() }}";
                    },
                    error: function (xhr) {
                        console.error('Error tblProducto:', xhr.status, xhr.responseText);
                    }
                },
                columns: [
                    { data: 'num_traslado' },
                    { data: 'id_producto' },
                    { data: 'nombre' },
                    { data: 'cantidad' },
                    { data: 'origen_completo' },
                    { data: 'destino_completo' },
                    { data: 'name' },
                    { data: 'created_at' },
                    { data: 'opciones', orderable: false, searchable: false },
                ]
            });
        }

        function ajustesPorfecha() {
            var inicio = document.getElementById('fechaInicio').value;
            var fin    = document.getElementById('fechaFinal').value;
            if (!inicio || !fin) { alert('Seleccione un rango de fechas válido.'); return; }
            fechaInicio = inicio;
            fechaFinal  = fin;
            tablaTraslado();
            if (tblProducto) { tblProducto = null; tablaProducto(); }
        }

        function aplicarFiltrosProducto() {
            filtroQ             = document.getElementById('filtro_q').value.trim();
            filtroNumTraslado   = document.getElementById('filtro_num_traslado').value.trim();
            filtroBodegaOrigen  = document.getElementById('filtro_bodega_origen').value;
            filtroBodegaDestino = document.getElementById('filtro_bodega_destino').value;
            tblProducto = null;
            tablaProducto();
        }

        function limpiarFiltrosProducto() {
            document.getElementById('filtro_q').value              = '';
            document.getElementById('filtro_num_traslado').value   = '';
            document.getElementById('filtro_bodega_origen').value  = '';
            document.getElementById('filtro_bodega_destino').value = '';
            filtroQ = ''; filtroNumTraslado = ''; filtroBodegaOrigen = ''; filtroBodegaDestino = '';
            tblProducto = null;
            tablaProducto();
        }
    </script>
    @endpush
</div>
