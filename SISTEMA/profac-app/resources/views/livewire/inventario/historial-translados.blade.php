<div>
    @push('styles')

    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Historial de Translados de Bodega</h2>
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
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3><i class="fa-solid fa-cart-shopping"></i>Ajustes</h3>
                    </div>
                    <div class="ibox-content ">
                        <div class="table-responsive">
                            <table id="tbl_listar_ajustes" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>

                                        <th>Codigo</th>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Registrado por:</th>
                                        <th>Fecha de registro</th>
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
            var fechaInicio = "{{ $fechaInicio }}";
            var fechaFinal = "{{ date('Y-m-t') }}";

            $(document).ready(function() {
                tablas();
            })

            function tablas()
            {

                $('#tbl_listar_ajustes').DataTable({

                    "order": [4, 'desc'],
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                    },
                    pageLength: 15,
                    responsive: true,
                    'ajax': {
                        'url': "/translados/obtener/listado",
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
                            data: 'nombre'
                        },
                        {
                            data: 'cantidad'
                        },
                        {
                            data: 'name'
                        },
                        {
                            data: 'created_at'
                        },
                        {
                            data: 'opciones'
                        },


                    ]


                });



            }


            function ajustesPorfecha(){
                let inicio = document.getElementById('fechaInicio').value;
                let final = document.getElementById('fechaFinal').value;


                fechaInicio = inicio;
                fechaFinal = final;

                $('#tbl_listar_ajustes').DataTable().clear().destroy();
               // $('#tbl_listar_ventas_dos').DataTable().clear().destroy();
                //$('#tbl_listar_ventas_uno').DataTable().ajax.reload();

                this.tablas();

                //$('#tbl_listar_ventas_uno').DataTable().ajax.reload();
                //$('#tbl_listar_ventas_dos').DataTable().ajax.reload();
            }




        </script>
    @endpush
</div>
