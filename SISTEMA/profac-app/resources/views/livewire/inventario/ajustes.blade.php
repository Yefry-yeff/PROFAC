<div>
    @push('styles')
        <style>

        </style>
    @endpush
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Ajustes de Producto </h2>

        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3>Listado De Producto En Bodega</h3>
                    </div>
                    <div class="ibox-content">
                        <form id="selec_data_form" name="selec_data_form" data-parsley-validate>
                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 b-r">
                                    <div>

                                        <label for="selectBodega" class="col-form-label focus-label">Seleccionar
                                            Bodega:</label>
                                        <select id="selectBodega" class="form-group form-control" style=""
                                            data-parsley-required onchange="obtenerSecciones()">
                                            <option value="" selected disabled>--Seleccionar una Bodega--</option>
                                        </select>

                                    </div>


                                </div>

                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 b-r">
                                    <div>

                                        <label for="selectSeccion" class="col-form-label focus-label">Seleccionar
                                            Seccion:</label>
                                        <select id="selectSeccion" class="form-group form-control" style=""
                                            data-parsley-required onchange="obtenerProductosBodega()">
                                            <option value="" selected disabled>--Seleccionar una Seccion--
                                            </option>
                                        </select>

                                    </div>


                                </div>


                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">

                                    <label for="selectProducto" class="col-form-label focus-label">Seleccionar
                                        Producto:</label>
                                    <select id="selectProducto" class="form-group form-control" style=""
                                        data-parsley-required>
                                        <option value="" selected disabled>--Seleccionar un producto por codigo ó
                                            nombre--</option>
                                    </select>

                                </div>






                            </div>
                        </form>
                        <button type="submit" form="selec_data_form" class="btn btn-primary btn-lg mb-4 mt-3">Solicitar
                            Producto</button>


                        <hr>

                        <h3 class=""><i class="fa-solid fa-warehouse  m-0 p-0" style="color: #1AA689"></i> <span
                                id="total"></span></h3>
                        <div class="table-responsive">
                            <table id="tbl_translados" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Cod Producto</th>
                                        <th>Nombre</th>
                                        <th>Unidad de Medida</th>
                                        <th>Cantidad Disponible</th>
                                        <th>Bodega</th>
                                        <th>Sección</th>
                                        <th>Fecha Ingreso</th>
                                        <th>Translado</th>



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

    <!--Tabla para productos a ajustar-->
    <div id="lista_translado" class="">
        <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h3>Listado de productos</h3>
                        </div>
                        <div class="ibox-content">
                            <div class="table-responsive">
                                <form onkeydown="return event.key != 'Enter';" id="ajustar_producto_form"
                                    data-parsley-validate>
                                    <div class="form-group">
                                        <label for="tipo_ajuste_id">Motivo de ajuste<span
                                            class="text-danger">*</span></label>
                                        <select class="form-control m-b" name="tipo_ajuste_id" id="tipo_ajuste_id"
                                            required data-parsley-required>
                                            <option value="" selected disabled>---Seleccione una bodega de
                                                destino---
                                            </option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="comentario" class="col-form-label focus-label">Comentario:<span
                                                class="text-danger">*</span></label>
                                        <textarea spellcheck="true" placeholder="Escriba aquí..." required id="comentario" name="comentario" cols="30"
                                            rows="3" class="form-group form-control" data-parsley-required></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="solicitado_por">Solicitado por:<span
                                                    class="text-danger">*</span></label>
                                                <select class="form-control " name="solicitado_por" id="solicitado_por"
                                                    required data-parsley-required>
                                                    <option value="" selected disabled>---Seleccionar una
                                                        opción:---
                                                    </option>

                                                </select>
                                            </div>



                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">

                                                <label for="fecha">Fecha de solicitud:<span
                                                    class="text-danger">*</span></label>
                                                <input class="form-control" required data-parsley-required
                                                    type="date" name="fecha" id="fecha">
                                            </div>

                                        </div>
                                    </div>

                                </form>
                                <table id="tbl_translados_productos"
                                    class="table table-striped table-bordered table-hover">
                                    <thead class="">
                                        <tr>
                                            <th>Eliminar</th>
                                            <th>Cod Producto</th>
                                            <th>Nombre Producto</th>
                                            <th>Bodega</th>
                                            <th>Tipo</th>
                                            <th>Cantidad</th>
                                            <th>Uniad de medida</th>
                                        </tr>
                                    </thead>

                                    <tbody id="cuerpoListaProducto">


                                    </tbody>

                                </table>



                                <button id="btn_realizar_ajuste" type="submit" form="ajustar_producto_form"
                                    class="btn btn-primary btn-lg mb-4 mt-3">Guardar Ajuste</button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <!-- Modal para transferir producto a otra bodega-->
    <div class="modal fade" id="modal_transladar_producto" role="dialog"
        aria-labelledby="modal_transladar_productoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="modal_transladar_productoLabel"> Datos de Ajuste </h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalBody">
                    <div class="row">
                        <div class="col-sm-12">
                            <form id="datos_ajuste_form" name="datos_ajuste_form" data-parsley-validate>
                                <input type="hidden" name="idRecibido" id="idRecibido">

                                <input type="hidden" name="bodega" id="bodega" value="">
                                <input type="hidden" name="seccion" id="seccion" value="">





                                <div class="form-group">
                                    <label for="aritmetica">Método de ajuste</label>
                                    <select class="form-control m-b" name="aritmetica" id="aritmetica" required
                                        data-parsley-required>
                                        <option value="" selected disabled>---Seleccione un método de ajuste ---
                                        </option>
                                        <option value="1">Sumar Unidades</option>
                                        <option value="2">Restar Unidades</option>
                                    </select>
                                </div>


                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">

                                            <label for="idProducto">Código de producto</label>
                                            <input type="number" name="idProducto" id="idProducto"
                                                class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label for="nombre_producto">Nombre de producto:</label>
                                            <input class="form-control" required data-parsley-required type="text"
                                                name="nombre_producto" id="nombre_producto" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                        <div class="form-group">

                                            <label for="cantidad_dispo">Cantidad disponible en sección:</label>
                                            <input type="number" name="cantidad_dispo" id="cantidad_dispo"
                                                class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label for="unidad">Unidad de Medida:</label>
                                            <select onchange="calcularTotalUnidades()" class="form-control "
                                                name="unidad" id="unidad" required data-parsley-required>
                                                <option value="" data-id="" selected disabled>---Seleccionar
                                                    una unidad de medida:---</option>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                        <div class="form-group">

                                            <label for="precio_producto">Precio unitario de producto:</label>
                                            <input type="number" step="any" name="precio_producto"
                                                id="precio_producto" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label for="cantidad">Cantidad:</label>
                                            <input class="form-control" autocomplete="off" required
                                                data-parsley-required type="number" min="1" name="cantidad"
                                                id="cantidad" onchange="calcularTotalUnidades()">
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <div class="form-group">
                                            <label for="total_unidades">Total de unidades para realizar ajuste:</label>
                                            <input class="form-control" autocomplete="off" required
                                                data-parsley-required type="number" name="total_unidades"
                                                id="total_unidades" readonly>
                                        </div>
                                    </div>
                                </div>


                            </form>

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" form="datos_ajuste_form" class="btn btn-primary">Agregar producto</button>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            var contador = 1;
            var arrayInputs = [];
            var idRecibidoArray = [];
            var idProductoArray = [];
            var idRecibido = null;


            $(document).ready(function() {
                obtenerMotivos();
            });


            $('#selectBodega').select2({
                ajax: {
                    url: '/ajustes/listar/bodegas',
                    data: function(params) {
                        var query = {
                            search: params.term,
                            type: 'public',
                            page: params.page || 1
                        }

                        // Query parameters will be ?search=[term]&type=public
                        return query;
                    }

                }
            });





            $(document).on('submit', '#datos_ajuste_form', function(event) {

                event.preventDefault();
                agregarProductoLista();

            });

            $(document).on('submit', '#selec_data_form', function(event) {

                event.preventDefault();
                obtenerListaBodega();

            });

            function obtenerSecciones() {
                let bodegaId = document.getElementById('selectBodega').value;
                let selectSeccion = document.getElementById('selectSeccion');
                selectSeccion.innerHTML = '<option value="" selected disabled>--Seleccionar un Sección --</option>';

                $('#selectSeccion').select2({
                    ajax: {
                        url: '/ajuste/listar/secciones',
                        data: function(params) {

                            var query = {
                                search: params.term,
                                bodegaId: bodegaId,
                                type: 'public',
                                page: params.page || 1
                            }


                            return query;
                        }
                    }
                });
            }

            function obtenerProductosBodega() {

                let idBodega = document.getElementById("selectBodega").value;
                let idSeccion = document.getElementById("selectSeccion").value;

                let bodegaId = document.getElementById('selectBodega').value;
                let selectProducto = document.getElementById('selectProducto');
                selectProducto.innerHTML =
                    '<option value="" selected disabled>--Seleccionar un producto por codigo ó nombre--</option>';

                $('#selectProducto').select2({
                    ajax: {
                        url: '/ajustes/listar/productos',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                idBodega: idBodega,
                                idSeccion: idSeccion,
                                type: 'public',
                                page: params.page || 1
                            }

                            // Query parameters will be ?search=[term]&type=public
                            return query;
                        }
                    }
                });
            }

            function obtenerListaBodega() {
                let idBodega = document.getElementById('selectBodega').value;
                let idProducto = document.getElementById('selectProducto').value;
                //let data = {'idBodega':idBodega, 'idProducto',idProducto};

                let table = $('#tbl_translados').DataTable();
                table.destroy();

                $('#tbl_translados').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                    },
                    pageLength: 10,
                    responsive: true,
                    "ajax": {
                        'url': "/ajustes/listado/producto/bodega",
                        'data': {
                            'idBodega': idBodega,
                            idProducto: idProducto
                        },
                        'type': 'post',
                        'headers': {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }


                    },
                    "columns": [{
                            data: 'idProducto'
                        },
                        {
                            data: 'nombre'
                        },
                        {
                            data: 'simbolo'
                        },
                        {
                            data: 'cantidad_disponible'
                        },
                        {
                            data: 'bodega'
                        },
                        {
                            data: 'descripcion'
                        },
                        {
                            data: 'created_at'
                        },
                        {
                            data: 'opciones'
                        },



                    ],
                    drawCallback: function() {
                        var sum = $('#tbl_translados').DataTable().column(3).data().sum();
                        let html = 'Cantidad Total en Bodega: ' + sum
                        $('#total').html(html);


                    }

                });



            }




            $(document).on('submit', '#ajustar_producto_form', function(event) {

                event.preventDefault();
                realizarAjuste();

            });


            function obtenerMotivos() {
                axios.get("/ajustes/motivos/listar")
                    .then(response => {
                        html = '<option value="" selected disabled>---Seleccionar una opción:---</option>';
                        htmlUsers = '<option value="" selected disabled>---Seleccionar una opción:---</option>';
                        let data = response.data.results;
                        let usuarios = response.data.usuarios;

                        data.forEach(element => {
                            html += `<option value="${element.id}">${element.text}</option>`
                        });

                        usuarios.forEach(usuario => {
                            htmlUsers += `<option value="${usuario.id}">${usuario.name}</option>`
                        })

                        let select = document.getElementById('tipo_ajuste_id');
                        document.getElementById('solicitado_por').innerHTML = htmlUsers;
                        select.innerHTML = html;
                    })
                    .catch(err => {
                        console.log(err);
                    })


            }



            function datosProducto(idProducto, idRecibido, cantidadDisponible) {
                axios.post('/ajustes/datos/producto', {
                        id: idProducto,
                        idRecibido: idRecibido
                    })
                    .then(response => {

                        let data = response.data.producto;
                        let unidades = response.data.unidadesMedida;
                        let datosBodega = response.data.datosBodega;

                        document.getElementById("bodega").value = datosBodega.bodega;
                        document.getElementById("seccion").value = datosBodega.seccion;

                        let precioProducto = document.getElementById('precio_producto');
                        precioProducto.value = data.precio_base;
                        precioProducto.min = data.precio_base;


                        document.getElementById('idProducto').value = data.id;
                        document.getElementById('nombre_producto').value = data.nombre;

                        document.getElementById('idRecibido').value = idRecibido
                        document.getElementById('cantidad_dispo').value = cantidadDisponible;

                        let htmlUnidades =
                            '<option value="" data-id="" selected disabled>---Seleccionar una unidad de medida:---</option>';

                        unidades.forEach(unidad => {
                            if (unidad.unidad_venta == 1) {
                                htmlUnidades += "<option value=" + unidad.unidad_venta + " data-id=" + unidad.id +
                                    "  selected>" + unidad.nombre + "</option>";
                            } else {
                                htmlUnidades += "<option value=" + unidad.unidad_venta + " data-id=" + unidad.id +
                                    "  >" + unidad.nombre + "</option>";
                            }

                        });

                        document.getElementById('unidad').innerHTML = htmlUnidades;

                        $('#modal_transladar_producto').modal('show')
                    })
                    .catch(err => {
                        let data = err.response.data
                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text,

                        })
                        console.log(err)
                    })
            }

            function agregarProductoLista() {



                let idCuerpoLista = document.getElementById("cuerpoListaProducto");

                let bodega = document.getElementById("bodega").value;
                let seccion = document.getElementById("seccion").value;

                //---------------------Formulario-----------------------------------//

                // let tipo_ajuste_id = document.getElementById("tipo_ajuste_id").value;
                // let solicitado_por = document.getElementById("solicitado_por").value;
                // let fecha = document.getElementById("fecha").value;

                let idRecibido = document.getElementById("idRecibido").value;
                let aritmetica = document.getElementById("aritmetica").value;
                let comentario = document.getElementById("comentario").value;
                let idProducto = document.getElementById("idProducto").value;
                let nombre_producto = document.getElementById("nombre_producto").value;
                let cantidad_dispo = document.getElementById("cantidad_dispo").value;
                let unidad = document.getElementById("unidad").value;
                let precio_producto = document.getElementById("precio_producto").value
                let cantidad = document.getElementById("cantidad").value;
                let total_unidades = document.getElementById("total_unidades").value;


                let aritmeticaSelect = document.getElementById("aritmetica");
                let aritmeticaNombre = aritmeticaSelect.options[aritmeticaSelect.selectedIndex].text


                let unidadSelect = document.getElementById("unidad");
                let unidadNombre = unidadSelect.options[unidadSelect.selectedIndex].text


                let e = document.getElementById('unidad');
                let idUnidadVenta = e.options[e.selectedIndex].getAttribute("data-id");

                //------------------------------------------------------------------//

                //-----------------------------Comprobar existencia de lote en arreglo------------------------//
                let comprobarIdRecibido = idRecibidoArray.find(element => element == (idRecibido));

                if (comprobarIdRecibido) {
                    Swal.fire({
                        icon: "warning",
                        title: "Advertencia!",
                        text: "El producto,  y Lote de destino ya existen en la lista. No se puede repetir estos elementos. ",
                        confirmButtonColor: "#1AA689",
                    })
                    $('#modal_transladar_producto').modal('hide')
                    return;
                }




                idRecibidoArray.push(idRecibido);

                let html = `
                    <tr id="tr${contador}">
                        <td>
                            <button class="btn btn-danger text-center" type="button" onclick="eliminarInput(${contador},${idRecibido})"" >
                                <i class="fa-regular fa-rectangle-xmark"></i>
                            </button>
                        </dt>
                        <td>
                            ${idProducto}
                        </td>
                        <td>
                           ${nombre_producto}
                        </td>

                        <td>
                            ${bodega} - ${seccion}
                        </td>

                        <td>
                            ${aritmeticaNombre}
                        </td>

                        <td>
                            ${cantidad}
                        </td>
                        <td>
                            ${unidadNombre}
                        </td>

                                <input type="hidden" id="idRecibido${contador}" name="idRecibido${contador}" value="${idRecibido}" form="ajustar_producto_form">
                                <input type="hidden" id="aritmetica${contador}" name="aritmetica${contador}" value="${aritmetica}" form="ajustar_producto_form">
                                <input type="hidden" id="idProducto${contador}" name="idProducto${contador}" value="${idProducto}" form="ajustar_producto_form">
                                <input type="hidden" id="nombre_producto${contador}" name="nombre_producto${contador}" value="${nombre_producto}" form="ajustar_producto_form">
                                <input type="hidden" id="cantidad_dispo${contador}" name="cantidad_dispo${contador}" value="${cantidad_dispo}" form="ajustar_producto_form">

                                <input type="hidden" id="precio_producto${contador}" name="precio_producto${contador}" value="${precio_producto}" form="ajustar_producto_form">
                                <input type="hidden" id="cantidad${contador}" name="cantidad${contador}" value="${cantidad}" form="ajustar_producto_form">
                                <input type="hidden" id="total_unidades${contador}" name="total_unidades${contador}" value="${total_unidades}" form="ajustar_producto_form">
                                <input type="hidden" id="idUnidadVenta${contador}" name="idUnidadVenta${contador}" value="${idUnidadVenta}" form="ajustar_producto_form">

                    </tr>
           `;

                $('#modal_transladar_producto').modal('hide')
                idCuerpoLista.insertAdjacentHTML('beforeend', html);
                document.getElementById("ajustar_producto_form").reset();
                $('#ajustar_producto_form').parsley().reset();
                arrayInputs.push(contador);

                contador++;

                document.getElementById("datos_ajuste_form").reset();
                $('#datos_ajuste_form').parsley().reset()

                return;


            }

            function realizarAjuste() {

                document.getElementById('btn_realizar_ajuste').disabled = false;
                //document.getElementById('btn_realizar_ajuste').disabled = true;

                let dataForm = new FormData($('#ajustar_producto_form').get(0));


                let text = arrayInputs.toString();
                dataForm.append("arregloIdInputs", text);

                const formDataObj = {};

                dataForm.forEach((value, key) => (formDataObj[key] = value));


                const options = {
                    headers: {
                        "content-type": "application/json"
                    }
                }





                axios.post('/ajustes/guardar/ajuste', formDataObj, options)
                    .then(response => {

                        $('#modal_transladar_producto').modal('hide')
                        let data = response.data;
                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            html: data.text,

                        })

                        document.getElementById("ajustar_producto_form").reset();
                        $('#ajustar_producto_form').parsley().reset();

                        $('#tbl_translados').DataTable().ajax.reload();

                        window.location.href = "/listado/ajustes";
                    })
                    .catch(err => {
                        let data = err.response.data;
                        $('#modal_transladar_producto').modal('hide')
                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            html: data.text,

                        })
                        document.getElementById('btn_realizar_ajuste').disabled = false;

                    })
            }

            function calcularTotalUnidades() {
                //let precio = document.getElementById('').value;
                let unidadesMedida = document.getElementById('unidad').value;
                let cantidad = document.getElementById('cantidad').value;


                if (unidadesMedida && cantidad) {
                    let resultado = unidadesMedida * cantidad;
                    document.getElementById('total_unidades').value = resultado;
                }

                return;
            }

            function eliminarInput(id, idRecibido) {
                const element = document.getElementById("tr" + id);
                element.remove();

                let myIndex = arrayInputs.indexOf(id);
                if (myIndex !== -1) {
                    arrayInputs.splice(myIndex, 1);

                }

                let myIndex2 = idRecibidoArray.indexOf('' + idRecibido);
                console.log(myIndex2);
                if (myIndex2 !== -1) {
                    idRecibidoArray.splice(myIndex2, 1);
                }



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
