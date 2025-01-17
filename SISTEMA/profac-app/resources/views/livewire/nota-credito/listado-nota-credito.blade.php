<div>
    @push('styles')

    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    {{--  <h2>Notas de Credito Clientes <b>B</b></h2>  --}}
                    <h2>Notas de Credito Clientes <b>A</b></h2>
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
                                <label for="_venta" class="col-form-label focus-label">Fecha de inicio</label>
                                <input id="fechaInicio" type="date" value="{{ $fechaInicio }}" class="form-group form-control">
                            </div>

                            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                                <label for="_venta" class="col-form-label focus-label">Fecha Final</label>
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
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3><i class="fa-solid fa-cart-shopping"></i>Listado</h3>
                    </div>
                    <div class="ibox-content ">
                        <div class="table-responsive">
                            <table id="tbl_listar_ajustes" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>

                                        <th>Codigo</th>
                                        <th>Registro N°</th>
                                        <th>Cliente</th>
                                        <th>No. Factura</th>
                                        <th>Motivo</th>
                                        <th>Comentario</th>
                                        <th>Sub Total</th>
                                        <th>ISV</th>
                                        <th>Total</th>
                                        <th>Fecha</th>
                                        <th>Registrado por:</th>
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
    <script src="{{ asset('js/js_proyecto/nota-credito/listado-nota-credito.js') }}"></script>
    <script>
        var fechaInicio = "{{ $fechaInicio }}";
        var fechaFinal = "{{ date('Y-m-t') }}";


            function tablas()
            {

                $('#tbl_listar_ajustes').DataTable({

                    "order": [1, 'desc'],
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                    },

                    pageLength: 15,
                    responsive: true,



                    'ajax': {
                        'url': "/nota/credito/listar",
                        'data': {
                            'fechaInicio': fechaInicio,
                            'fechaFinal': fechaFinal,
                            "_token": "{{ csrf_token() }}"
                        },
                        'type': 'post'
                    },
                    "columns": [

                        {
                            data: 'codigo'
                        },
                        {
                            data: 'cai'
                        },
                        {
                            data: 'cliente'
                        },
                        {
                            data: 'factura'
                        },
                        {
                            data: 'motivo'
                        },
                        {
                            data: 'comentario'
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
                            data: 'fecha_registro'
                        },
                        {
                            data: 'registrado_por'
                        },
                        {
                            data: 'opciones'
                        },

                    ]


                });



            }

    </script>
    @endpush
</div>
