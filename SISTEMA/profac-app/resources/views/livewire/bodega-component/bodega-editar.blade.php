<div>
    @push('styles')
        <link href="{{ asset('css/plugins/switchery/switchery.css') }}" rel="stylesheet">
    @endpush
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>Lista de Bodegas</h2>
            <h4>Editar bodega</h4>
        </div>

        <div class="col-lg-4 col-xl-2 col-md-4 col-sm-4">

                <div style="margin-top: 1.5rem">
                    <a href="/bodega/excel" class="btn-seconary"><i class="fa fa-plus"></i> Exportar Excel</a>
                </div>
        </div>

    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_bodegaEditar" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Codigo</th>
                                        <th>Dirreción</th>
                                        <th>Encargado</th>
                                        <th>Estado</th>
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

            <!-- Modal para editar Bodega-->
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="exampleModalLabel">Editar Bodega</h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-sm-12">
                                    <form id="editarBodega" data-parsley-validate>
                                        <input id="idBodega" name="idBodega" type="hidden" >

                                        <div class="form-group">
                                            <label for="editBodegaNombre">Nombre<span class="text-danger">*</span></label>
                                            <input id="editBodegaNombre" name="editBodegaNombre" type="text"
                                                placeholder="Nombre de bodega" class="form-control" data-parsley-required>
                                        </div>

                                        <div class="form-group">
                                            <label for="editBodegaDireccion">Direccion<span class="text-danger">*</span></label>
                                            <input id="editBodegaDireccion" name="editBodegaDireccion" type="text"
                                                placeholder="Direccion de bodega" class="form-control" data-parsley-required>
                                        </div>


                                        <div>
                                            <label for="editEncargadoBodega">Encargado de bodega<span class="text-danger">*</span></label>
                                            <select id="editEncargadoBodega" name="editEncargadoBodega"
                                                class="form-control m-b" data-parsley-required>
                                                <option value="0" selected disabled>Seleccione un encargado</option>


                                            </select>

                                        </div>

                                        <h3 class="text-center mt-4">Activar / Desactivar secciones</h3>

                                        <hr>

                                        <div id="contenedorSwich">

                                        </div>
                                        <h5 class="text-center">Nota: al desactivar una sección de bodega no podrá ingresar producto a dichas secciones de bodega</h5>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                            <button type="submit" form="editarBodega" class="btn btn-primary" >Guardar Cambios</button>
                        </div>
                    </div>
                </div>
            </div>


            <div class="modal fade" id="modalSecciones" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="exampleModalLabel">Agregar Sección</h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-sm-12">
                                    <form id="addSeccion" data-parsley-validate>
                                        <input id="idBodega" name="idBodega" type="hidden" >

                                        <div>
                                            <label for="">Seleccione el segmendo dónde quiere añadir nueva sección<span class="text-danger">*</span></label>
                                            <select id="selectSegmento" name="selectSegmento"
                                                class="form-control m-b" data-parsley-required>
                                                <option selected disabled>-------SELECCIONE SEGMENTO-----------</option>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                            <button type="submit" form="addSeccion" class="btn btn-primary" onclick="guardarSeccion()">Guardar Sección</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalSegmentos" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="exampleModalLabel">Agregar Segmento</h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-sm-12">
                                    <form id="addSegmentoForm" data-parsley-validate>
                                        <input id="idBodegaS" name="idBodegaS" type="hidden" >

                                        <div>
                                            <label for="">Lista de segmentos existentes en la bodega Actuál<span class="text-danger">*</span></label>
                                            <div class="list-group" id="ListaSegmentos">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="Letra Segmento">Indique la letra del nuevo Segmento<span class="text-danger">*</span></label>
                                            <input id="segmentoAdd" name="segmentoAdd" type="text" onkeyup="javascript:this.value=this.value.toUpperCase();"
                                                placeholder="Letra de Segmento" class="form-control" data-parsley-required>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                            <button type="submit" form="addSeccion" class="btn btn-primary" onclick="guardarSegmento()">Guardar Segmento</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        {{-- Care about people's approval and you will be their prisoner. --}}


        @push('scripts')
            <script src="{{ asset('js/plugins/switchery/switchery.js') }}"></script>
            <script>
                this.convertirSwitechs();

                function addSeccionJS(idBodega){
                    document.getElementById("idBodega").value = idBodega;


                    axios.get("/bodega/segmentos/listar/"+idBodega)
                                .then(response => {

                                    document.getElementById("selectSegmento").innerHTML = "";

                                    console.log(response.data.segmentosPorBodega);
                                    let datos = response.data.segmentosPorBodega;
                                    let array = datos;
                                    let htmlSelect = "";

                                    array.forEach(element => {

                                        htmlSelect += `<option value="${element.id}" selected>${element.descripcion}</option>`;

                                    });

                                    document.getElementById("selectSegmento").innerHTML += htmlSelect;
                                    $("#modalSecciones").modal("show");
                                })
                                .catch(error => {

                                    Swal.fire(
                                        'Error!',
                                        'Ha ocurrido un error al mostrar lista de segmentos.',
                                        'error',

                                    )

                                });
                }

                function addSegmentoJS(idBodega){
                    document.getElementById("idBodegaS").value = idBodega;

                    axios.get("/bodega/segmentos/listar/"+idBodega)
                                .then(response => {
                                    document.getElementById("ListaSegmentos").innerHTML = "";
                                    console.log(response.data.segmentosPorBodega);
                                    let datos = response.data.segmentosPorBodega;
                                    let array = datos;
                                    let htmlSelect = "";

                                    array.forEach(element => {


                                        htmlSelect += `<a href="#" class="list-group-item list-group-item-action">${element.descripcion}</a>`;

                                    });

                                    document.getElementById("ListaSegmentos").innerHTML += htmlSelect;
                                    $("#modalSegmentos").modal("show");
                                })
                                .catch(error => {

                                    Swal.fire(
                                        'Error!',
                                        'Ha ocurrido un error al mostrar lista de segmentos.',
                                        'error',

                                    )

                                });
                }

                function guardarSeccion(){
                    var data = new FormData($('#addSeccion').get(0));
                    location.reload();
                    axios.post('/guardar/seccion',data)
                    .then( response =>{
                        //console.log(response.data);
                        //location.reload();
                        $('#modalSecciones').modal('hide');


                        Swal.fire({
                        icon: 'success',
                        title: 'Exito!',
                        text: 'Guardados con éxito!',

                        })


                    })
                    .catch( err=>{

                        Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Ha ocurrido un error al Guardar!',

                        })


                    });
                }

                function guardarSegmento(){
                    var data = new FormData($('#addSegmentoForm').get(0));

                    axios.post('/guardar/segmento',data)
                    .then( response =>{
                        //console.log(response.data);
                        location.reload();
                        $('#modalSegmentos').modal('hide');


                        Swal.fire({
                        icon: 'success',
                        title: 'Exito!',
                        text: 'Guardados con éxito!',

                        })


                    })
                    .catch( err=>{

                        Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Ha ocurrido un error al Guardar!',

                        })


                    });
                }

                function convertirSwitechs() {
                    //     var elem = document.querySelector('.js-switch');
                    // var switchery = new Switchery(elem, { color: '#1AB394',size: 'small' });

                    var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));

                    elems.forEach(function(html) {
                        var switchery = new Switchery(html, {
                            color: '#1AB394',
                            size: 'small'
                        });
                    });
                }

                $(document).ready(function() {
                    $('#tbl_bodegaEditar').DataTable({
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
                        "ajax": "/bodega/listar/bodegas",
                        "columns": [{
                                data: 'numero_bodega'
                            },
                            {
                                data: 'codigo'
                            },
                            {
                                data: 'direccion'
                            },
                            {
                                data: 'encargado'
                            },
                            {
                                data: 'estado_bodega'
                            },
                            {
                                data: 'opciones'
                            }
                        ]


                    });
                })


                function desactivarBodega(id) {

                    Swal.fire({
                        title: '¿Esta seguro de desactivar esta bodega?',
                        text: "¡Si desactiva esta bodega, ya no podrá ingresar o almacenar productos esta bodega.!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: '¡Sí, Desactivar bodega!'
                    }).then((result) => {
                        if (result.isConfirmed) {


                            axios.post("/bodega/desactivar", {
                                    "id": id
                                })
                                .then(response => {


                                    Swal.fire(
                                        'Desactivado!',
                                        'La bodega a sido desactivada con exito.',
                                        'success'
                                    )

                                    $('#tbl_bodegaEditar').DataTable().ajax.reload();
                                })
                                .catch(error => {

                                    Swal.fire(
                                        'Error!',
                                        'Ha ocurrido un error al desactivar la bodega.',
                                        'error',

                                    )

                                })




                        }
                    })

                }

                function activarBodega(id) {
                    axios.post("/bodega/desactivar", {
                            "id": id
                        })
                        .then(response => {


                            Swal.fire(
                                'Activado!',
                                'La bodega a sido activada con exito.',
                                'success'
                            )

                            $('#tbl_bodegaEditar').DataTable().ajax.reload();
                        })
                        .catch(error => {

                            Swal.fire(
                                'Error!',
                                'Ha ocurrido un error al activar la bodega.',
                                'error',

                            )

                        })
                }

                function obtenerDatosBodega(id) {
                    axios.post("/bodega/datos", {
                            "id": id
                        })
                        .then(response => {
                            let datos = response.data;


                            document.getElementById("idBodega").value = datos.datosBodega.id;
                            document.getElementById("editBodegaNombre").value = datos.datosBodega.nombre;
                            document.getElementById("editBodegaDireccion").value = datos.datosBodega.direccion;

                            let array = datos.usuarios;
                            let htmlSelect = "";

                            array.forEach(element => {

                                if (element.id == datos.datosBodega.encargado_bodega) {

                                    htmlSelect +=
                                        `
                               <option value="${element.id}" selected>${element.name}</option>
                            `;
                                } else {

                                    htmlSelect +=
                                        `
                               <option value="${element.id}">${element.name}</option>
                            `;

                                }


                            });

                            document.getElementById("editEncargadoBodega").innerHTML = htmlSelect;


                            let arraySecciones = datos.secciones;
                            let htmlCheckbox = "";

                            arraySecciones.forEach(element => {
                                if (element.estado_id == 1) {
                                    htmlCheckbox +=
                                        `
                                <div class="my-2">

                                        <input id="${element.id}" name="seccion[]" value ="${element.id}"  type="checkbox" class="js-switch"  checked />
                                        <label for="" class="ml-2">${element.descripcion}</label>
                                </div>

                                `


                                } else {
                                    htmlCheckbox +=
                                        `
                                <div class="my-2">

                                        <input id="${element.id}" name="seccion[]" value ="${element.id}"  type="checkbox" class="js-switch"  />
                                        <label for="" class="ml-2">${element.descripcion}</label>
                                </div>

                                `
                                }

                            });


                            document.getElementById("contenedorSwich").innerHTML = htmlCheckbox;
                            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
                            elems.forEach(function(html) {
                                var switchery = new Switchery(html, {
                                    color: '#1AB394',
                                    size: 'small'
                                });
                            });

                            $('#exampleModal').modal('show');

                        })


                }

                // $('#editarBodega').submit(function(e){
                //     e.preventDefault();
                //     guardarCambios();
                //     //crearBodega();
                // });

                $(document).on('submit', '#editarBodega', function(event){
                    event.preventDefault();
                    guardarCambios();

                });

                function guardarCambios(){
                    //console.log("llego")
                    var data = new FormData($('#editarBodega').get(0));

                    axios.post('/bodega/editar',data)
                    .then( response =>{
                        //console.log(response.data);
                        $('#exampleModal').modal('hide');
                        document.getElementById('editarBodega').reset();


                        $('#editarBodega').parsley().reset();

                        Swal.fire({
                        icon: 'success',
                        title: 'Exito!',
                        text: 'Cambios guardados con éxito!',

                        })


                        $('#tbl_bodegaEditar').DataTable().ajax.reload();



                    })
                    .catch( err=>{
                        //console.error(err);

                        Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Ha ocurrido un error al editar la bodega!',

                        })


                    })


                }
            </script>
            <!-- Switchery -->
        @endpush

    </div>

<script>
