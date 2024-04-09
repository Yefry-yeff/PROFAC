<div>
    @push('styles')



        <style>
            @media (max-width: 600px) {
                .ancho-imagen {
                    max-width: 200px;
                }
                }

             @media (min-width: 601px ) and (max-width:900px){
                .ancho-imagen {
                    max-width: 300px;
                }
                }

                @media (min-width: 901px) {
                .ancho-imagen {
                    max-width: 300px;
                }
                }
          </style>






    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Clientes</h2>

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
                <a href="#" class="btn add-btn btn-success" data-toggle="modal" data-target="#modal_clientes_crear"><i
                        class="fa fa-plus"></i> Registrar Cliente</a>
            </div>
            <div style="margin-top: 1.5rem">
                <a href="/cliente/excel" class="btn-seconary"><i class="fa fa-plus"></i> Exportar Excel</a>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">

                        <div class="table-responsive">
                            <table id="tbl_ClientesLista" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Nombre</th>
                                        <th>Dirreción</th>
                                        <th>Teléfono</th>
                                        <th>Correo</th>
                                        <th>RTN</th>
                                        <th>Estado</th>
                                        <th>Registrado Por:</th>
                                        <th>Fecha </th>
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


        <!---MODAL PARA CREAR CLIENTES----->
        <div id="modal_clientes_crear" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-success">Registro de Cliente</h5>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="clientesCreacionForm" name="clientesCreacionForm" data-parsley-validate>
                            <input type="hidden" name="_token" value="{!! csrf_token() !!}">
                            <div class="row" id="row_datos">

                                <div class="col-md-12">
                                    <label class="col-form-label focus-label">Nombre del cliente<span class="text-danger">*</span></label>
                                    <input class="form-control" required type="text" id="nombre_cliente" name="nombre_cliente"
                                        data-parsley-required maxlength="60">
                                </div>
                                <div class="col-md-12">
                                    <label class="col-form-label focus-label">Dirección<span class="text-danger">*</span></label>
                                    <textarea name="direccion_cliente" placeholder="Escriba aquí..." required id="direccion_cliente" cols="30" rows="3"
                                        class="form-group form-control" data-parsley-required maxlength="142"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Credito Disponible<span class="text-danger">*</span></label>
                                    <input data-type="currency"  id="credito" name="credito" type="text"  step="any" class="form-group form-control" data-parsley-required>
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label" for="dias_credito">Dias de credito<span class="text-danger">*</span></label>
                                    <input   id="dias_credito" name="dias_credito" type="number"  min="0" max="120" class="form-group form-control" data-parsley-required>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">RTN<span class="text-danger">*</span></label>
                                    <input class="form-group form-control" required type="text" name="rtn_cliente"
                                        id="rtn_cliente" data-parsley-required pattern="[0-9]{14}">
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Correo electrónico<span class="text-danger">*</span></label>
                                    <input class="form-group form-control" type="text" name="correo_cliente" id="correo_cliente"
                                        data-parsley-required>
                                </div>

                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Teléfono del cliente<span class="text-danger">*</span></label>
                                    <input class="form-group form-control" type="text" name="telefono_cliente" id="telefono_cliente"
                                        data-parsley-required>
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Nombre de contácto 1<span class="text-danger">*</span></label>
                                    <input class="form-control" required type="text" id="contacto[]"
                                        name="contacto[]" data-parsley-required>
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Teléfono contacto 1<span class="text-danger">*</span></label>
                                    <input class="form-group form-control" required type="text" name="telefono[]"led 900900
                                        id="telefono[]" data-parsley-required pattern="[0-9]{8}"">
                                </div>

                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Nombre de contácto 2</label>
                                    <input class="form-control"  type="text" id="contacto[]"
                                        name="contacto[]" >
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Teléfono contacto 2</label>
                                    <input class="form-group form-control"  type="text" name="telefono[]"
                                        id="telefono[]" pattern="[0-9]{8}">
                                </div>


                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Longitud</label>
                                    <input class="form-group form-control"  type="text" name="longitud_cliente"
                                        id="longitud_cliente" >
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Latitud</label>
                                    <input class="form-group form-control"  type="text" name="latitud_cliente"
                                        id="latitud_clientee" >
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Pais<span class="text-danger">*</span></label>
                                    <select class="form-group form-control" name="pais_cliente" id="pais_cliente"
                                    onchange="obtenerDepartamentos()">
                                        <option selected disabled>---Seleccione un pais---</option>

                                    </select>
                                </div>



                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Departamento<span class="text-danger">*</span></label>
                                    <select class="form-group form-control" name="departamento_cliente" id="departamento_cliente"
                                        onchange="obtenerMunicipios()">
                                        <option selected disabled>---Seleccione un departamento---</option>

                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Municipio<span class="text-danger">*</span></label>
                                    <select class="form-group form-control" name="municipio_cliente" id="municipio_cliente"
                                        data-parsley-required >
                                        <option selected disabled>---Seleccione un municipio---</option>

                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Tipo de Personalidad<span class="text-danger">*</span> </label>
                                    <select class="form-group form-control" name="tipo_personalidad" id="tipo_personalidad"
                                        data-parsley-required>
                                        <option disabled selected>---Seleccione una opción---</option>


                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Tipo de cliente<span class="text-danger">*</span></label>
                                    <select class="form-group form-control" name="categoria_cliente" id="categoria_cliente"
                                        data-parsley-required>
                                        <option selected disabled>---Seleccione una opción---</option>

                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Vendedor<span class="text-danger">*</span></label>
                                    <select class="form-group form-control" name="vendedor_cliente" id="vendedor_cliente"
                                        data-parsley-required>
                                        <option selected disabled>---Seleccione una opción---</option>

                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label for="foto_cliente" class="col-form-label focus-label">Fotografía: </label>
                                    <input class="" type="file" id="foto_cliente" name="foto_cliente" accept="image/png, image/gif, image/jpeg, image/jpg" >

                                </div>
                                <div class=" col-md-7">
                                    <img id="imagenPrevisualizacion" class="ancho-imagen">

                                </div>
                            </div>
                        </form>
                        <button id="btn_crear_cliente" type="submit" class="btn btn-sm btn-primary float-left mt-4"
                            form="clientesCreacionForm"><strong>Crear
                               Cliente</strong></button>
                    </div>

                </div>
            </div>
        </div>

        <!---MODAL PARA EDITAR CLIENTES----->
        <div id="modal_clientes_editar" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-success">Editar datos del Cliente</h5>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="clientesCreacionForm_editar" name="clientesCreacionForm" data-parsley-validate>

                            <div class="row" id="row_datos">
                                <input id="idCliente" name="idCliente" type="hidden" >

                                <div class="col-md-12">
                                    <label class="col-form-label focus-label">Nombre del cliente</label>
                                    <input class="form-control" required type="text" id="nombre_cliente_editar" name="nombre_cliente_editar"
                                        data-parsley-required>
                                </div>
                                <div class="col-md-12">
                                    <label class="col-form-label focus-label">Dirección</label>
                                    <textarea name="direccion_cliente_editar" placeholder="Escriba aquí..." required id="direccion_cliente_editar" cols="30" rows="3"
                                        class="form-group form-control" data-parsley-required maxlength="142"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Credito Inicial</label>
                                    <input  id="credito_inicial_editar" name="credito_inicial_editar" type="number" step="any" class="form-group form-control" data-parsley-required>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Credito Disponible</label>
                                    <input  id="credito_editar" name="credito_editar" type="number" step="any" class="form-group form-control" data-parsley-required>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label" for="dias_credito_editar">Dias de credito<span class="text-danger">*</span></label>
                                    <input   id="dias_credito_editar" name="dias_credito_editar" type="number"  min="0" max="120" class="form-group form-control" data-parsley-required>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">RTN</label>
                                    <input class="form-group form-control" required type="text" name="rtn_cliente_editar"
                                        id="rtn_cliente_editar" data-parsley-required pattern="[0-9]{14}">
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Correo electrónico</label>
                                    <input class="form-group form-control" type="text" name="correo_cliente_editar" id="correo_cliente_editar"
                                        data-parsley-required>
                                </div>

                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Teléfono del cliente</label>
                                    <input class="form-group form-control" type="text" name="telefono_cliente_editar" id="telefono_cliente_editar"
                                        data-parsley-required>
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Nombre de contácto 1</label>
                                    <input class="form-control" required type="text" id="contacto_1_editar"
                                        name="contacto_1_editar" data-parsley-required>
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Teléfono contacto 1</label>
                                    <input class="form-group form-control" required type="text" name="telefono_1_editar"
                                        id="telefono_1_editar" data-parsley-required pattern="[0-9]{8}">
                                </div>

                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Nombre de contácto 2</label>
                                    <input class="form-control"  type="text" id="contacto_2_editar"
                                        name="contacto_2_editar" >
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Teléfono contacto 2</label>
                                    <input class="form-group form-control"  type="text" name="telefono_2_editar"
                                        id="telefono_2_editar" pattern="[0-9]{8}">
                                </div>


                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Longitud</label>
                                    <input class="form-group form-control"  type="text" name="longitud_cliente_editar"
                                        id="longitud_cliente_editar" >
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label focus-label">Latitud</label>
                                    <input class="form-group form-control"  type="text" name="latitud_cliente_editar"
                                        id="latitud_cliente_editar" >
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Pais</label>
                                    <select class="form-group form-control" name="pais_cliente_editar" id="pais_cliente_editar"
                                    onchange="obtenerDepartamentosEditar()">
                                        <option selected disabled>---Seleccione un pais---</option>

                                    </select>
                                </div>



                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Departamento</label>
                                    <select class="form-group form-control" name="departamento_cliente_editar" id="departamento_cliente_editar"
                                        onchange="obtenerMunicipiosEditar()">
                                        <option selected disabled>---Seleccione un departamento---</option>

                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Municipio</label>
                                    <select class="form-group form-control" name="municipio_cliente_editar" id="municipio_cliente_editar"
                                        data-parsley-required >
                                        <option selected disabled>---Seleccione un municipio---</option>

                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Tipo de Personalidad </label>
                                    <select class="form-group form-control" name="tipo_personalidad_editar" id="tipo_personalidad_editar"
                                        data-parsley-required>
                                        <option disabled selected>---Seleccione una opción---</option>


                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Tipo de cliente</label>
                                    <select class="form-group form-control" name="categoria_cliente_editar" id="categoria_cliente_editar"
                                        data-parsley-required>
                                        <option selected disabled>---Seleccione una opción---</option>

                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="col-form-label focus-label">Vendedor</label>
                                    <select class="form-group form-control" name="vendedor_cliente_editar" id="vendedor_cliente_editar"
                                        data-parsley-required>
                                        <option selected disabled>---Seleccione una opción---</option>
                                        @foreach ($clientes as $cliente)
                                        <option value="{{$cliente->id}}" >{{$cliente->name}}</option>
                                        @endforeach

                                    </select>
                                </div>

                            </div>
                        </form>

                        <button id="btn_crear_cliente_editar" type="submit" class="btn btn-primary  mt-4"
                            form="clientesCreacionForm_editar"><strong>Editar
                               Cliente</strong></button>
                               <button type="button" class="btn btn-default  mt-4" data-dismiss="modal">Cerrar</button>
                    </div>

                </div>
            </div>
        </div>

        <!---MODAL PARA EDITAR FOTOGRAFIA----->
        <div id="modal_fotografia_editar" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered ">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-success">Editar fotografia del cliente</h5>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="form_img_edit" name="form_img_edit" data-parsley-validate>
                        <input type="hidden" id="clienteId" name="clienteId">

                        <div class="col-md-5">
                            <label for="foto_cliente_editar" class="col-form-label focus-label">Fotografía: </label>
                            <input class="" type="file" id="foto_cliente_editar" name="foto_cliente_editar" accept="image/png, image/gif, image/jpeg, image/jpg" >

                        </div>
                        <div class=" col-md-7 mt-2">
                            <img id="imagenPrevisualizacion_editar" class="ancho-imagen">

                        </div>
                        </form>



                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default  " data-dismiss="modal">Cerrar</button>
                        <button id="btn_img_editar" type="submit" class="btn btn-primary  "
                            form="form_img_edit"><strong>
                               Cambiar Imagen</strong></button>
                    </div>



                </div>
            </div>
        </div>

@push('scripts')
<script>

    var idCliente = null;


$(document).ready(function() {
    listarClientes();
    obtenerpaiss();
    tipoPersonalidad();
    tipoCliente();
    vendedor();

})

        const $foto_cliente = document.querySelector("#foto_cliente"),
        $imagenPrevisualizacion = document.querySelector("#imagenPrevisualizacion");

        // Escuchar cuando cambie
        $foto_cliente.addEventListener("change", () => {
        // Los archivos seleccionados, pueden ser muchos o uno
        const archivos = $foto_cliente.files;
        // Si no hay archivos salimos de la función y quitamos la imagen
        if (!archivos || !archivos.length) {
            $imagenPrevisualizacion.src = "";
            return;
        }
        // Ahora tomamos el primer archivo, el cual vamos a previsualizar
        const primerArchivo = archivos[0];
        // Lo convertimos a un objeto de tipo objectURL
        const objectURL = URL.createObjectURL(primerArchivo);
        // Y a la fuente de la imagen le ponemos el objectURL
        $imagenPrevisualizacion.src = objectURL;
        });

        function listarClientes(){
            //

            $('#tbl_ClientesLista').DataTable({
                "order": [0, 'desc'],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                },
                pageLength: 10,
                responsive: true,
                "ajax": "/clientes/listar",
                "columns": [{
                        data: 'idCliente'
                    },
                    {
                        data: 'nombre'
                    },
                    {
                        data: 'direccion'
                    },
                    {
                        data: 'telefono_empresa'
                    },
                    {
                        data: 'correo'
                    },
                    {
                        data: 'rtn'
                    },
                    {
                        data: 'estado'
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

        function obtenerpaiss() {

            axios.get('/cliente/pais')
                .then(function(response) {

                    let array = response.data.listaPais;
                    let html = "<option selected disabled>---Seleccione un pais---</option>";

                    array.forEach(pais => {

                        html +=
                            `
                    <option value="${ pais.id }">${pais.nombre}</option>
                   `
                    });

                    document.getElementById("pais_cliente").innerHTML = html;


                })
                .catch(function(error) {
                    // handle error
                    console.log(error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error...',
                        text: "Ha ocurrido un error al obtener la lista de paises"
                    })
                })



        }

        function obtenerDepartamentos(){
            document.getElementById('departamento_cliente').innerHTML="<option selected disabled>---Seleccionar un depto---</option>";
            document.getElementById('municipio_cliente').innerHTML="<option selected disabled>---Seleccionar un depto---</option>";

            let id = document.getElementById('pais_cliente').value;
           // console.log(id)

            axios.post('/cliente/departamento',{id:id})
            .then(function(response) {

                let array = response.data.listaDeptos;
                let html = "<option selected disabled>---Seleccione un departamento---</option>";

                array.forEach(departamento => {

                    html +=
                        `
                <option value="${ departamento.id }">${departamento.nombre}</option>
                `
                });

                document.getElementById("departamento_cliente").innerHTML = html;


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

        function obtenerMunicipios(){
            let id = document.getElementById('departamento_cliente').value;

            axios.post('/cliente/municipio', {id:id})
            .then(function(response) {
            let array = response.data.listaMunicipios;
            let html = "<option selected disabled>---Seleccione un municipio---</option>";

            array.forEach(municipio => {

                html +=
                    `
            <option value="${ municipio.id }">${municipio.nombre}</option>
            `
            });

            document.getElementById("municipio_cliente").innerHTML = html;


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

        function tipoPersonalidad(){


            axios.get('/cliente/tipo/personalidad')
            .then(function(response) {
            let array = response.data.tipoPersonalidad;
            let html = "<option selected disabled>---Seleccione una opción---</option>";

            array.forEach(tipo => {

                html +=
                    `
            <option value="${ tipo.id }">${tipo.nombre}</option>
            `
            });

            document.getElementById("tipo_personalidad").innerHTML = html;


            })
            .catch(function(error) {
            // handle error
            console.log(error);

            Swal.fire({
                icon: 'error',
                title: 'Error...',
                text: "Ha ocurrido un error al obtener el tipo de personalidad"
            })
            })

        }

        //

        function tipoCliente(){


           axios.get('/cliente/tipo/cliente')
           .then(function(response) {
           let array = response.data.tipoCliente;
           let html = "<option selected disabled>---Seleccione una opción---</option>";

           array.forEach(tipo => {

               html +=
                   `
           <option value="${ tipo.id }">${tipo.descripcion}</option>
           `
           });

           document.getElementById("categoria_cliente").innerHTML = html;


           })
           .catch(function(error) {
           // handle error
           console.log(error);

           Swal.fire({
               icon: 'error',
               title: 'Error...',
               text: "Ha ocurrido un error al obtener el tipo de cliente"
           })
           })

       }

       function vendedor(){


           axios.get('/cliente/lista/vendedores')
           .then(function(response) {
           let array = response.data.vendedor;
           let html = "<option selected disabled>---Seleccione una opción---</option>";

           array.forEach(vendedor => {

               html +=
                   `
           <option value="${ vendedor.id }">${vendedor.name}</option>
           `
           });

           document.getElementById("vendedor_cliente").innerHTML = html;


           })
           .catch(function(error) {
           // handle error
           console.log(error);

           Swal.fire({
               icon: 'error',
               title: 'Error...',
               text: "Ha ocurrido un error al obtener el vendedor"
           })
           })

       }

      // /cliente/registrar
      $(document).on('submit', '#clientesCreacionForm', function(event) {
        event.preventDefault();
        registrarCliente();
        });

      function registrarCliente(){
        document.getElementById('btn_crear_cliente').disabled=true;
        let contacto2 = document.getElementsByName('contacto[]');
        let telefono2 = document.getElementsByName('telefono[]');

        //console.log(contacto2[1].value , telefono2[1].value)

        if( contacto2[1].value  && telefono2[1].value  ){

                var data = new FormData($('#clientesCreacionForm').get(0));



                axios.post('/cliente/registrar',data)
                .then( response => {
                    let data = response.data;


                    $('#modal_clientes_crear').modal('hide');
                    document.getElementById("clientesCreacionForm").reset();
                    $('#clientesCreacionForm').parsley().reset();
                    $('#tbl_ClientesLista').DataTable().ajax.reload();
                    $imagenPrevisualizacion.src = '';
                    document.getElementById('btn_crear_cliente').disabled=false;


                    Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })


                })
                .catch( err => {
                    let data = err.response.data;
                    console.log(err);
                    $('#modal_clientes_crear').modal('hide');
                    document.getElementById('btn_crear_cliente').disabled=false;
                    Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })
                })

        }else if( (contacto2[1].value == null || contacto2[1].value == '' ) && (telefono2[1].value == null || telefono2[1].value == '' ) ){

            var data = new FormData($('#clientesCreacionForm').get(0));

            axios.post('/cliente/registrar',data)
            .then( response => {
                let data = response.data;
                $('#modal_clientes_crear').modal('hide');

                document.getElementById("clientesCreacionForm").reset();
                $('#clientesCreacionForm').parsley().reset();
                $('#tbl_ClientesLista').DataTable().ajax.reload();
                $imagenPrevisualizacion.src='';
                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text,
                })
                document.getElementById('btn_crear_cliente').disabled=false;


            })
            .catch( err => {
                let data = err.response.data;
                $('#modal_clientes_crear').modal('hide');
                    document.getElementById('btn_crear_cliente').disabled=false;
                    Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })
            })

        }else{
            $('#modal_clientes_crear').modal('hide');

            Swal.fire({
                        icon: 'warning',
                        title: 'Advertencia!',
                        text: "Por favor completar los datos faltantes del contacto 2 del cliente. De faltar el nombre o numero de teléfono dejar en las casillas en blanco"
                    })

        }

        document.getElementById('btn_crear_cliente').disabled=false;

      }

/*---------------------------------------------------------------Editar Cliente----------------------------------------------------------------------------------------------------------------*/
/*---------------------------------------------------------------Editar Cliente----------------------------------------------------------------------------------------------------------------*/
           // /cliente/registrar

    function modalEditarCliente(id){

        // this.idCliente = id;
        // $('#modal_clientes_editar').modal('show');

        axios.post("/clientes/datos/editar", {id:id})
        .then( response => {

            document.getElementById("clientesCreacionForm_editar").reset();

            let datosCliente = response.data.datosCliente;
            let datosContacto = response.data.datosContacto;
            let datosUbicacion = response.data.datosUbicacion;
            let paises = response.data.paises;
            let deptos = response.data.deptos;
            let municipios = response.data.municipios;

            let tipoPersonalidad = response.data.tipoPersonalidad;
            let tipoCliente = response.data.tipoCliente;
            let vendedores = response.data.vendedores;

            let htmlSelectPais ="";
            let htmlSelectDepto ="";
            let htmlSelectMunicipio ="";

            let htmlSelectTipoPersonalidad ="";
            let htmlSelectTipoCliente="";
            let htmlSelectVendedor="";

            let longitudArrayContactos = datosContacto.length;

            /*------------------------------------------------------------*/
            paises.forEach(pais => {
                if(datosUbicacion.idPais == pais.id ){
                    htmlSelectPais +=
                    `
                    <option value="${ pais.id }" selected>${pais.nombre}</option>
                    `


                }else{
                    htmlSelectPais +=
                    `
                    <option value="${ pais.id }">${pais.nombre}</option>
                    `


                }
            });

            /*----------------------------------------------------------*/
            deptos.forEach(depto => {
                if(datosUbicacion.idDepto == depto.id ){
                    htmlSelectDepto +=
                    `
                    <option value="${ depto.id }" selected>${depto.nombre}</option>
                    `


                }else{
                    htmlSelectDepto +=
                    `
                    <option value="${ depto.id }">${depto.nombre}</option>
                    `


                }
            });
            /*-------------------------------------------------------------*/
            municipios.forEach(municipio => {
                if(datosUbicacion.idMunicipio == municipio.id ){
                    htmlSelectMunicipio +=
                    `
                    <option value="${ municipio.id }" selected>${municipio.nombre}</option>
                    `


                }else{
                    htmlSelectMunicipio +=
                    `
                    <option value="${ municipio.id }">${municipio.nombre}</option>
                    `


                }
            });

            /*-------------------------------------------------------------*/
            tipoPersonalidad.forEach(personalidad => {
                if(datosCliente.tipo_personalidad_id == personalidad.id ){
                    htmlSelectTipoPersonalidad +=
                    `
                    <option value="${ personalidad.id }" selected>${personalidad.nombre}</option>
                    `


                }else{
                    htmlSelectTipoPersonalidad +=
                    `
                    <option value="${ personalidad.id }">${personalidad.nombre}</option>
                    `


                }
            });

            /*-------------------------------------------------------------*/
            tipoCliente.forEach(cliente => {
                if(datosCliente.tipo_cliente_id == cliente.id ){
                    htmlSelectTipoCliente +=
                    `
                    <option value="${ cliente.id }" selected>${cliente.descripcion}</option>
                    `


                }else{
                    htmlSelectTipoCliente +=
                    `
                    <option value="${ cliente.id }">${cliente.descripcion}</option>
                    `


                }
            });

            /*-------------------------------------------------------------*/
            vendedores.forEach(vendedor => {
                if(datosCliente.vendedor == vendedor.id ){
                    htmlSelectVendedor +=
                    `
                    <option value="${ vendedor.id }" selected>${vendedor.name}</option>
                    `

                }else{
                    htmlSelectVendedor +=
                    `
                    <option value="${ vendedor.id }">${vendedor.name}</option>
                    `


                }
            });

            document.getElementById('idCliente').value = datosCliente.id;

            document.getElementById('nombre_cliente_editar').value =datosCliente.nombre;
            document.getElementById('direccion_cliente_editar').value =datosCliente.direccion;
            document.getElementById('credito_inicial_editar').value = datosCliente.credito_inicial;
           // document.getElementById('credito_inicial_editar').value = datosCliente.credito_inicial.toFixed(2);
            document.getElementById('credito_editar').value = datosCliente.credito;
           // document.getElementById('credito_editar').value = datosCliente.credito.toFixed(2);
            document.getElementById('dias_credito_editar').value = datosCliente.dias_credito;
            document.getElementById('rtn_cliente_editar').value = datosCliente.rtn;
            document.getElementById("correo_cliente_editar").value = datosCliente.correo;
            document.getElementById('telefono_cliente_editar').value = datosCliente.telefono_empresa;


            document.getElementById('contacto_1_editar').value = datosContacto[0].nombre;
            document.getElementById('telefono_1_editar').value =datosContacto[0].telefono;


            if(longitudArrayContactos>1){
                document.getElementById('contacto_2_editar').value =datosContacto[1].nombre;
                document.getElementById('telefono_2_editar').value =datosContacto[1].telefono;
            }


            document.getElementById('longitud_cliente_editar').value =datosCliente.longitud;
            document.getElementById('latitud_cliente_editar').value =datosCliente.latitud;

            document.getElementById("pais_cliente_editar").innerHTML=htmlSelectPais;
            document.getElementById("departamento_cliente_editar").innerHTML=htmlSelectDepto;
            document.getElementById("municipio_cliente_editar").innerHTML=htmlSelectMunicipio;

            document.getElementById("tipo_personalidad_editar").innerHTML=htmlSelectTipoPersonalidad;
            document.getElementById("categoria_cliente_editar").innerHTML=htmlSelectTipoCliente;
            document.getElementById("vendedor_cliente_editar").innerHTML=htmlSelectVendedor;




            $('#modal_clientes_editar').modal('show');



        })
        .catch(err=>{

            console.log(err)

        })


    }

    function obtenerDepartamentosEditar(){

        document.getElementById('departamento_cliente_editar').innerHTML="<option selected disabled>---Seleccionar un depto---</option>";
        document.getElementById('municipio_cliente_editar').innerHTML="<option selected disabled>---Seleccionar un depto---</option>";

           let id = document.getElementById('pais_cliente_editar').value;
          // console.log(id)

           axios.post('/cliente/departamento',{id:id})
           .then(function(response) {

               let array = response.data.listaDeptos;
               let html = "<option selected disabled>---Seleccione un departamento---</option>";

               array.forEach(departamento => {

                   html +=
                       `
               <option value="${ departamento.id }">${departamento.nombre}</option>
               `
               });

               document.getElementById("departamento_cliente_editar").innerHTML = html;


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

       function obtenerMunicipiosEditar(){

           let id = document.getElementById('departamento_cliente_editar').value;


           axios.post('/cliente/municipio', {id:id})
           .then(function(response) {
           let array = response.data.listaMunicipios;
           let html = "<option selected disabled>---Seleccione un municipio---</option>";

           array.forEach(municipio => {

               html +=
                   `
           <option value="${ municipio.id }">${municipio.nombre}</option>
           `
           });

           document.getElementById("municipio_cliente_editar").innerHTML = html;


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

       $(document).on('submit', '#clientesCreacionForm_editar', function(event) {
        event.preventDefault();
        editarClienteGuardar();
    });

       function editarClienteGuardar(){
        let contacto2 = document.getElementsByName('contacto_2_editar');
        let telefono2 = document.getElementsByName('telefono_2_editar');



        if( contacto2.value  && telefono2.value  ){

                var data = new FormData($('#clientesCreacionForm_editar').get(0));
                document.getElementById('btn_crear_cliente_editar').disabled=true;

                axios.post('/clientes/editar',data)
                .then( response => {
                    let data = response.data;


                    $('#modal_clientes_editar').modal('hide');
                    document.getElementById('btn_crear_cliente_editar').disabled=false;
                    document.getElementById("clientesCreacionForm_editar").reset();
                    $('#clientesCreacionForm_editar').parsley().reset();
                    $('#tbl_ClientesLista').DataTable().ajax.reload();




                    Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })


                })
                .catch( err => {
                    let data = err.response.data;
                    console.log(err);
                    $('#clientesCreacionForm_editar').modal('hide');
                    document.getElementById('btn_crear_cliente_editar').disabled=false;
                    Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })
                })

        }else if( (contacto2.value == null || contacto2.value == '' ) && (telefono2.value == null || telefono2.value == '' ) ){

            var data = new FormData($('#clientesCreacionForm_editar').get(0));

            axios.post('/clientes/editar',data)
            .then( response => {
                let data = response.data;
                $('#modal_clientes_editar').modal('hide');
                document.getElementById('btn_crear_cliente_editar').disabled=false;
                document.getElementById("clientesCreacionForm_editar").reset();
                $('#clientesCreacionForm_editar').parsley().reset();
                $('#tbl_ClientesLista').DataTable().ajax.reload();

                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text,
                })

            })
            .catch( err => {
                let data = err.response.data;
                $('#modal_clientes_editar').modal('hide');
                    document.getElementById('btn_crear_cliente_editar').disabled=false;
                    Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })
            })

        }else{
            $('#modal_clientes_editar').modal('hide');

            Swal.fire({
                        icon: 'warning',
                        title: 'Advertencia!',
                        text: "Por favor completar los datos faltantes del contacto 2 del cliente. De faltar el nombre o numero de teléfono dejar en las casillas en blanco"
                    })

        }

       }

       function modalEditarFotografia(idCliente){
           document.getElementById('clienteId').value=idCliente;

          axios.post("/clientes/imagen",{idCliente:idCliente})
          .then(response=>{

            let data = response.data.img;
            let imagenPrevisualizacion_editar = document.getElementById('imagenPrevisualizacion_editar');

            if(data){

                let url = 'img_cliente/'+data;
                imagenPrevisualizacion_editar.src = url;

            }else{
                let url = 'catalogo/noimage.png';
                imagenPrevisualizacion_editar.src = url;
            }

            $('#modal_fotografia_editar').modal('show');

            console.log("entro")
          })
          .catch(err=>{

            console.log(err)

          })
       }

       const $foto_cliente_editar = document.querySelector("#foto_cliente_editar"),
        $imagenPrevisualizacion_editar = document.querySelector("#imagenPrevisualizacion_editar");

        // Escuchar cuando cambie
        $foto_cliente_editar.addEventListener("change", () => {
        // Los archivos seleccionados, pueden ser muchos o uno
        const archivos_editar = $foto_cliente_editar.files;
        // Si no hay archivos salimos de la función y quitamos la imagen
        if (!archivos_editar || !archivos_editar.length) {
            $imagenPrevisualizacion_editar.src = "";
            return;
        }
        // Ahora tomamos el primer archivo, el cual vamos a previsualizar
        const primerArchivo_editar = archivos_editar[0];
        // Lo convertimos a un objeto de tipo objectURL
        const objectURL_editar = URL.createObjectURL(primerArchivo_editar);
        // Y a la fuente de la imagen le ponemos el objectURL
        $imagenPrevisualizacion_editar.src = objectURL_editar;
        });



        $(document).on('submit', '#form_img_edit', function(event) {
        event.preventDefault();
        imagenClienteEditarGuardar();
        })

        function imagenClienteEditarGuardar(){
            document.getElementById('btn_img_editar').disabled = true;
            var data = new FormData($('#form_img_edit').get(0));
            axios.post('/clientes/imagen/editar',data)
            .then(response=>{
                let data = response.data;
                $('#modal_fotografia_editar').modal('hide');
                document.getElementById('btn_img_editar').disabled = false;
                document.getElementById("form_img_edit").reset();
                $('#form_img_edit').parsley().reset();

                Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })
                $('#tbl_ClientesLista').DataTable().ajax.reload();

            })
            .catch(err=>{

                let data = err.response.data;
                $('#modal_fotografia_editar').modal('hide');
                document.getElementById('btn_img_editar').disabled = false;
                Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })

            })
        }

        function desactivarClienteModal(id){

            Swal.fire({
            title: '¿Esta seguro de desactivar este cliente?',
            text:'Si desactiva este cliente, no podra realizar ventas para el mismo.',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Si, Desactivar',
            cancelButtonText: 'Cancelar',
            }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                desactivar(id);
            } else if (result.isDenied) {
                Swal.fire('Changes are not saved', '', 'info')
            }
            })

        }

        function desactivar(idCliente){
            axios.post('/clientes/desactivar',{clienteId:idCliente})
            .then( response=>{
                let data = response.data;
                Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })
                $('#tbl_ClientesLista').DataTable().ajax.reload();

            })
            .catch(err=>{
                console.log(err);
                let data = err.response.data;
                Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })
            })
        }

        function activarCliente(idCliente){



            axios.post('/clientes/activar',{clienteId:idCliente})
            .then( response=>{
                let data = response.data;
                Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })
                $('#tbl_ClientesLista').DataTable().ajax.reload();
            })
            .catch(err=>{
                console.log(err);
                let data = err.response.data;
                Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })
            })
        }

        $("input[data-type='currency']").on({
    keyup: function() {
      formatCurrency($(this));
    },
    blur: function() {
      formatCurrency($(this), "blur");
    }
});


function formatNumber(n) {
  // format number 1000000 to 1,234,567
  return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
}


function formatCurrency(input, blur) {
  // appends $ to value, validates decimal side
  // and puts cursor back in right position.

  // get input value
  var input_val = input.val();

  // don't validate empty input
  if (input_val === "") { return; }

  // original length
  var original_len = input_val.length;

  // initial caret position
  var caret_pos = input.prop("selectionStart");

  // check for decimal
  if (input_val.indexOf(".") >= 0) {

    // get position of first decimal
    // this prevents multiple decimals from
    // being entered
    var decimal_pos = input_val.indexOf(".");

    // split number by decimal point
    var left_side = input_val.substring(0, decimal_pos);
    var right_side = input_val.substring(decimal_pos);

    // add commas to left side of number
    left_side = formatNumber(left_side);

    // validate right side
    right_side = formatNumber(right_side);

    // On blur make sure 2 numbers after decimal
    if (blur === "blur") {
      right_side += "00";
    }

    // Limit decimal to only 2 digits
    right_side = right_side.substring(0, 2);

    // join number by .
    input_val =  left_side + "." + right_side;

  } else {
    // no decimal entered
    // add commas to number
    // remove all non-digits
    input_val = formatNumber(input_val);
    input_val = input_val;

    // final formatting
    if (blur === "blur") {
      input_val += ".00";
    }
  }

  // send updated string to input
  input.val(input_val);

  // put caret back in the right position
  var updated_len = input_val.length;
  caret_pos = updated_len - original_len + caret_pos;
  input[0].setSelectionRange(caret_pos, caret_pos);
}

</script>
@endpush

</div>
<?php
    date_default_timezone_set('America/Tegucigalpa');
    $act_fecha=date("Y-m-d");
    $act_hora=date("H:i:s");
    $mes=date("m");
    $year=date("Y");
    $datetim=$act_fecha." ".$act_hora;
?>
<script>
    function mostrarHora() {
        var fecha = new Date(); // Obtener la fecha y hora actual
        var hora = fecha.getHours();
        var minutos = fecha.getMinutes();
        var segundos = fecha.getSeconds();

        // A単adir un 0 delante si los minutos o segundos son menores a 10
        minutos = minutos < 10 ? "0" + minutos : minutos;
        segundos = segundos < 10 ? "0" + segundos : segundos;

        // Mostrar la hora actual en el elemento con el id "reloj"
        document.getElementById("reloj").innerHTML = hora + ":" + minutos + ":" + segundos;
    }
    // Actualizar el reloj cada segundo
    setInterval(mostrarHora, 1000);
</script>
<div class="float-right">
    <?php echo "$act_fecha";  ?> <strong id="reloj"></strong>
</div>
<div>
    <strong>Copyright</strong> Distribuciones Valencia &copy; <?php echo "$year";  ?>
</div>
<p id="reloj"></p>
