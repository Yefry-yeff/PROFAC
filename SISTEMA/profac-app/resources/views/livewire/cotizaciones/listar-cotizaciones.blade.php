<div>
    @push('styles')
        <style>
            .tipo-selector .btn { margin: 2px 4px; }
            .tipo-selector .btn.active { box-shadow: 0 0 0 3px rgba(0,123,255,.5); }
        </style>
    @endpush

    {{-- Loading Overlay --}}
    <div id="tbl_loading_overlay" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.78); z-index:9000; text-align:center; padding-top:18%;">
        <i class="fa fa-spinner fa-spin fa-3x" style="color:#1ab394;"></i>
        <p class="mt-3" style="color:#555; font-size:1rem;">Cargando datos...</p>
    </div>

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Listado De Cotizaciones</h2>
            <ol class="breadcrumb">

                @switch(  $idTipoVenta )
                @case(1)
                    <li class="breadcrumb-item active">
                        <a>Coorporativo</a>
                    </li>
                    @break
                @case(2)
                    <li class="breadcrumb-item active">
                        <a>Gobierno</a>
                    </li>
                    @break
                @case(3)
                    <li class="breadcrumb-item active">
                        <a>Exonerado</a>
                    </li>
                    @break
                @endswitch



                <li class="breadcrumb-item">
                    <a>Imprimir Cotización</a>
                </li>
                <li class="breadcrumb-item">
                    <a>Imprimir Factura</a>
                </li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">

        {{-- SELECTOR DE TIPO --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="ibox">
                    <div class="ibox-content py-2">
                        <div class="d-flex align-items-center flex-wrap tipo-selector">
                            <strong class="mr-3">Tipo:</strong>
                            <button type="button" class="btn btn-sm {{ $idTipoVenta == 2 ? 'btn-primary active' : 'btn-outline-secondary' }}"
                                onclick="cambiarTipoCotizacion(2, this)">Clientes A</button>
                            <button type="button" class="btn btn-sm {{ $idTipoVenta == 1 ? 'btn-primary active' : 'btn-outline-secondary' }}"
                                onclick="cambiarTipoCotizacion(1, this)">Clientes B</button>
                            <button type="button" class="btn btn-sm {{ $idTipoVenta == 3 ? 'btn-primary active' : 'btn-outline-secondary' }}"
                                onclick="cambiarTipoCotizacion(3, this)">Exoneradas</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">

                        <div class="table-responsive">
                            <table id="tbl_listar_cotizaciones" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Cliente</th>
                                        <th>RTN</th>
                                        <th>Sub Total</th>
                                        <th>ISV</th>
                                        <th>Total</th>
                                        <th>Vendedor</th>
                                        <th>Cotizador</th>
                                        <th>Fecha de registro:</th>
                                        <th>Opciones</th>


                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            var idTipoVenta = {{$idTipoVenta}};
            var nombresTipoCotiz = { 1: 'Coorporativo', 2: 'Gobierno', 3: 'Exonerado' };
            var urlHistoryCotiz = { 1: '/cotizacion/listado/corporativo', 2: '/cotizacion/listado/estatal', 3: '/cotizacion/listado/exonerado' };

            $(document).ready(function() {
                $('#tbl_listar_cotizaciones').DataTable({
                    "order": [8, 'desc'],
                    "language": {
                        "url": "/js/plugins/dataTables/i18n/Spanish.json"
                    },

                    pageLength: 5,
                    responsive: true,
                    dom: '<"html5buttons"B>lTfgitp',
                    buttons: [

                       {
                            extend: 'excel',
                            title: 'Cotizaciones',
                            className:'btn btn-success'
                        }
                    ],
                    "ajax":{
                        'url':"/cotizacion/obtener/listado",
                        'data' : function(d) { d.id = idTipoVenta; },
                        'type' : 'post',
                        'headers': {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    }


                         },
                    "columns": [

                        {
                            data: 'codigo'
                        },
                        {
                            data: 'nombre_cliente'
                        },
                        {
                            data: 'RTN'
                        },
                        {
                            data: 'sub_total'
                        },
                        {
                            data: 'isv'
                        },
                        {
                            data: 'total'
                        },
                        {
                            data: 'vendedor'
                        },
                        {
                            data: 'cotizador'
                        },
                        {
                            data: 'created_at'
                        },
                        {
                            data: 'opciones'
                        },



                    ],
                    "initComplete": function() {
                        document.getElementById('tbl_loading_overlay').style.display = 'none';
                    }


                });
                })

            function cambiarTipoCotizacion(nuevoIdTipo, btnElement) {
                if (nuevoIdTipo === idTipoVenta) return;
                document.getElementById('tbl_loading_overlay').style.display = '';
                document.querySelectorAll('.tipo-selector .btn').forEach(function(btn) {
                    btn.classList.remove('btn-primary', 'active');
                    btn.classList.add('btn-outline-secondary');
                });
                btnElement.classList.remove('btn-outline-secondary');
                btnElement.classList.add('btn-primary', 'active');
                idTipoVenta = nuevoIdTipo;
                // Actualizar breadcrumb
                var breadcrumbItems = document.querySelectorAll('.breadcrumb-item.active a');
                if (breadcrumbItems.length > 0) breadcrumbItems[0].textContent = nombresTipoCotiz[nuevoIdTipo];
                history.pushState({ tipo: nuevoIdTipo }, '', urlHistoryCotiz[nuevoIdTipo]);
                $('#tbl_listar_cotizaciones').DataTable().ajax.reload(function() {
                    document.getElementById('tbl_loading_overlay').style.display = 'none';
                });
            }

        </script>
        <script src="{{ asset('js/js_proyecto/cotizaciones/listar-cotizaciones.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function imprimirProformaConValidacion(event, idCotizacion) {
                event.preventDefault();
                axios.get('/cotizacion/validar-proforma/' + idCotizacion)
                    .then(function(response) {
                        var data = response.data;
                        if (data.valido) {
                            window.open('/proforma/imprimir/' + idCotizacion, '_blank');
                        } else {
                            Swal.fire({
                                icon: data.icon,
                                title: data.titulo,
                                text: data.mensaje,
                            });
                        }
                    })
                    .catch(function(err) {
                        var mensaje = 'Ha ocurrido un error al validar la proforma.';
                        if (err.response && err.response.data && err.response.data.mensaje) {
                            mensaje = err.response.data.mensaje;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: mensaje,
                        });
                    });
            }
        </script>
    @endpush
</div>
