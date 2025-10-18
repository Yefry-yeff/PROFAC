<div>
    @push('styles')
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Listado De Pedidos</h2>
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
                    @case(4)
                    <li class="breadcrumb-item active">
                        <a>Expo</a>
                    </li>
                    @break
                @endswitch

            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
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
                                        <th>Vendedor:</th>
                                        <th>Cotizador:</th>
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


              //var varToken = {{ csrf_token() }};

              $(document).ready(function() {
                $('#tbl_listar_cotizaciones').DataTable({
        "order": [0, 'desc'],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        pageLength: 10,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp',
        buttons: [
            {
                extend: 'excel',
                title: 'ExampleFile'
            },

            {
                extend: 'print',
                customize: function(win) {
                    $(win.document.body).addClass('white-bg');
                    $(win.document.body).css('font-size', '10px');

                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('font-size', 'inherit');
                }
            }
        ],
                    "ajax":{
                        'url':"/cotizacion/obtener/listado/expo",
                        'data' : {'id' : idTipoVenta },
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



                    ]


                });
                });



        </script>
    @endpush
</div>
