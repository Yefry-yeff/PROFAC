<div>

    @push('styles')
        <link href="{{ asset('css/plugins/iCheck/custom.css') }}" rel="stylesheet">
        <link href="{{ asset('css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css') }}"
            rel="stylesheet">
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Proveedores</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.html">Lista</a>
                </li>
                <li class="breadcrumb-item">
                    <a>Edicion</a>
                </li>

            </ol>
        </div>

        <div class="col-lg-4 col-xl-2 col-md-4 col-sm-4">
            <div style="margin-top: 1.5rem">
                <a href="#" class="btn add-btn btn-primary" data-toggle="modal" data-target="#modal_proveedores_crear"><i
                        class="fa fa-plus"></i> Registrar Proveedor</a>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">

                        <div class="table-responsive">
                            <table id="tbl_proveedoresListar" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Nombre</th>
                                        <th>Dirreción</th>
                                        <th>Contacto</th>
                                        <th>Correo</th>
                                        <th>RTN</th>
                                        <th>Retencion 1%</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
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





    <div id="modal_proveedores_crear" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success">Regitro de Proveedores</h5>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="proveedorCreacionForm" name="proveedorCreacionForm" data-parsley-validate>
                        <input type="hidden" name="_token" value="{!! csrf_token() !!}">
                        <div class="row" id="row_datos">
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Código:</label>
                                <input class="form-control" required type="text" id="codigo_prov" name="codigo_prov"
                                    data-parsley-required>
                            </div>
                            <div class="col-md-8">
                                <label class="col-form-label focus-label">Nombre del proveedor</label>
                                <input class="form-control" required type="text" id="nombre_prov" name="nombre_prov"
                                    data-parsley-required>
                            </div>
                            <div class="col-md-12">
                                <label class="col-form-label focus-label">Dirección</label>
                                <textarea name="direccion_prov" placeholder="Escriba aquí..." required id="direccion_prov" cols="30" rows="3"
                                    class="form-group form-control" data-parsley-required></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Contácto</label>
                                <input class="form-control" required type="text" id="contacto_prov"
                                    name="contacto_prov" data-parsley-required>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Teléfono</label>
                                <input class="form-group form-control" required type="text" name="telefono_prov"
                                    id="telefono_prov" data-parsley-required>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Teléfono 2</label>
                                <input class="form-group form-control" type="text" name="telefono_prov_2"
                                    id="telefono_prov_2">
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Correo electrónico</label>
                                <input class="form-group form-control" type="text" name="correo_prov" id="correo_prov"
                                    data-parsley-required>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Correo electrónico 2</label>
                                <input class="form-group form-control" type="text" name="correo_prov_2"
                                    id="correo_prov_2">
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">RTN</label>
                                <input class="form-group form-control" required type="text" name="rtn_prov"
                                    id="rtn_prov" data-parsley-required>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">País</label>
                                <select class="form-group form-control" name="pais_prov" id="pais_prov"
                                    onchange="obtenerDepartamentos()">
                                    <option selected disabled>---Seleccione un país---</option>
                                    @foreach ($paises as $pais)
                                        <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                                    @endforeach

                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Departamento</label>
                                <select class="form-group form-control" name="depto_prov" id="depto_prov"
                                    onchange="obtenerMunicipios()">
                                    <option selected disabled>---Seleccione un Departamento---</option>

                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Municipio</label>
                                <select class="form-group form-control" name="municipio_prov" id="municipio_prov"
                                    data-parsley-required>
                                    <option selected disabled>---Seleccione un municipio---</option>

                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Tipo de Personalidad </label>
                                <select class="form-group form-control" name="giro_neg_prov" id="giro_neg_prov"
                                    data-parsley-required>
                                    <option disabled selected>---Seleccione una opción---</option>
                                    @foreach ($tipoPersonalidad as $user)
                                        <option value="{{ $user->id }}">{{ $user->nombre }}</option>
                                    @endforeach

                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Categoría</label>
                                <select class="form-group form-control" name="categoria_prov" id="categoria_prov"
                                    data-parsley-required>
                                    <option selected disabled>---Seleccione una opción---</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="col-form-label focus-label">Retenciones </label>
                                <select class="form-group form-control" name="retencion_prov" id="retencion_prov"
                                    data-parsley-required>
                                    <option selected disabled>---Seleccione una opción---</option>
                                    @foreach ($retenciones as $retencion)
                                        <option value="{{ $retencion->id }}">{{ $retencion->nombre }}</option>
                                    @endforeach


                                </select>
                            </div>
                        </div>
                    </form>
                    <button class="btn btn-sm btn-primary float-left m-t-n-xs"
                        form="proveedorCreacionForm"><strong>Crear
                            Bodega</strong></button>
                </div>

            </div>
        </div>
    </div>




</div>

@push('scripts')
    <script src="{{ asset('js/plugins/iCheck/icheck.min.js') }}"></script>


    <script>
        $(document).ready(function() {
            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
            });
        });

        function obtenerDepartamentos() {
            var id = document.getElementById("pais_prov").value;
            //console.log(id)
            ///proveedores/obeter/departamento
            let datos = {
                "id": id
            };

            axios.post('/proveedores/obeter/departamentos', datos)
                .then(function(response) {

                    let array = response.data.departamentos;
                    let html = "";

                    array.forEach(departamento => {

                        html +=
                            `
                    <option value="${ departamento.id }">${departamento.nombre}</option>   
                   `
                    });

                    document.getElementById("depto_prov").innerHTML = html;


                })
                .catch(function(error) {
                    // handle error
                    console.log(error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error...',
                        text: "Ha ocurrido un error al obtener los departamentos"
                    })
                })



        }

        function obtenerMunicipios() {
            //municipio_prov
            var id = document.getElementById("depto_prov").value;

            let datos = {
                "id": id
            }


            axios.post('/proveedores/obeter/municipios', datos)
                .then(function(response) {

                    let array = response.data.departamentos;
                    let html = "";

                    array.forEach(municipio => {

                        html +=
                            `
                    <option value="${ municipio.id }">${municipio.nombre}</option>   
                   `
                    });

                    document.getElementById("municipio_prov").innerHTML = html;


                })
                .catch(function(error) {
                    // handle error
                    console.log(error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error...',
                        text: "Ha ocurrido un error al obtener los municipios"
                    })
                })

        }

        $(document).ready(function() {
            $('#tbl_proveedoresListar').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                },
                pageLength: 10,
                responsive: true,
                dom: '<"html5buttons"B>lTfgitp',
                buttons: [{
                        extend: 'copy'
                    },
                    {
                        extend: 'csv'
                    },
                    {
                        extend: 'excel',
                        title: 'ExampleFile'
                    },
                    {
                        extend: 'pdf',
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
                "ajax": "/proveedores/listar/proveedores",
                "columns": [{
                        data: 'id'
                    },
                    {
                        data: 'nombre'
                    },
                    {
                        data: 'direccion'
                    },
                    {
                        data: 'contacto'
                    },
                    {
                        data: 'correo_1'
                    },
                    {
                        data: 'rtn'
                    },
                    {
                        data: 'retencion'
                    },
                    {
                        data: 'estado'
                    },
                    {
                        data: 'opciones'
                    },
                ]


            });
        })


        $(document).on('submit', '#bodegaCreacion', function(event) {
            event.preventDefault();

        });

        $(document).on('submit', '#proveedorCreacionForm', function(event) {

            event.preventDefault();
            crearProveedores();

        });


        function crearProveedores() {
            var data = new FormData($('#proveedorCreacionForm').get(0));

            $.ajax({
                type: "POST",
                url: "/proveedores/crear",
                data: data,
                contentType: false,
                cache: false,
                processData: false,
                dataType: "json",
                success: function(data) {


                    document.getElementById("proveedorCreacionForm").reset();
                    $('#modal_proveedores_crear').modal('hide');


                    Swal.fire({
                        icon: 'success',
                        title: 'Exito!',
                        text: "Proveedor creado con exito."
                    })

                    $('#tbl_proveedoresListar').DataTable().ajax.reload();



                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR.responseJSON.message);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: jqXHR.responseJSON.message
                    })
                }
            });
        }

        function desactivarProveedor(id) {


            Swal.fire({
                title: '¿Esta seguro de desactivar este proveedor?',
                text: "¡Si desactiva este proveedor, ya no podrá ingresar o almacenar productos con este proveedor.!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Sí, Desactivar proveedor!'
            }).then((result) => {
                if (result.isConfirmed) {


                    axios.post('/proveedores/desactivar', {
                            "id": id
                        })
                        .then(function(response) {

                            Swal.fire({
                                icon: 'success',
                                title: 'Exito!',
                                text: "Proveedor desactivado con exito."
                            })

                            $('#tbl_proveedoresListar').DataTable().ajax.reload();

                        })
                        .catch(function(error) {
                            // handle error
                            console.log(error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: "Ha ocurrido un error al desactivar el proveedor."
                            })
                        })





                }
            })


        }

        function activarProveedor(id) {
            axios.post('/proveedores/desactivar', {
                    "id": id
                })
                .then(function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Exito!',
                        text: "Proveedor Activado con exito."
                    })

                    $('#tbl_proveedoresListar').DataTable().ajax.reload();

                })
                .catch(function(error) {
                    // handle error
                    console.log(error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: "Ha ocurrido un error al Activar el proveedor."
                    })
                })

        }
    </script>
@endpush

</div>
