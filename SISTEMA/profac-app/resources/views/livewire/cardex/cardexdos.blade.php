<div>
    <style>
        tfoot input {
            width: 100%;
            padding: 3px;
            box-sizing: border-box;
        }
    </style>
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <h2>Cardex versión 2</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.html">Lista</a>
                </li>


            </ol>
        </div>

    </div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="row">


                            <div class="col-6 col-sm-6 col-md-6 ">
                                <label for="seleccionarBodega" class="col-form-label focus-label">Seleccionar Bodega:<span class="text-danger">*</span></label>
                                <select id="bodega" name="bodega" class="form-group form-control" style=""
                                    data-parsley-required onchange="obtenerIdBodega()">
                                    <option value="" selected disabled>--Seleccionar una Bodega--</option>
                                </select>
                            </div>

                            <div class="col-6 col-sm-6 col-md-6">
                                <label for="seleccionarProducto" class="col-form-label focus-label">Seleccionar Producto:<span class="text-danger">*</span></label>
                                <select id="producto" name="producto" class="form-group form-control" style=""
                                    data-parsley-required >
                                    <option value="" selected disabled>--Seleccionar una Producto--</option>
                                </select>
                            </div>

                        </div>
                        <button class="btn btn-primary" onclick="cargaCardex()"><i class="fa-solid fa-paper-plane text-white"></i> Solicitar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_cardex" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Fecha de gestion</th>
                                        <th>Producto</th>
                                        <th>Codigo de producto</th>
                                       {{--   <th>Factura</th>
                                        <th>Ajuste</th>
                                        <th>Compra</th>
                                        <th>Comprobante de entrega</th>
                                        <th>Vale Tipo 1</th>
                                        <th>Vale Tipo 2</th>  --}}
                                        <th>Nota de credito</th>
                                        <th>Descripcion</th>
                                        <th>Origen</th>
                                        <th>Destino</th>
                                        <th>Cantidad</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Fecha de gestion</th>
                                            <th>Producto</th>
                                            <th>Codigo de producto</th>
                                           {{--   <th>Factura</th>
                                            <th>Ajuste</th>
                                            <th>Compra</th>
                                            <th>Comprobante de entrega</th>
                                            <th>Vale Tipo 1</th>
                                            <th>Vale Tipo 2</th>  --}}
                                            <th>Nota de credito</th>
                                            <th>Descripcion</th>
                                            <th>Origen</th>
                                            <th>Destino</th>
                                            <th>Cantidad</th>
                                            <th>Usuario</th>
                                        </tr>
                                    </tfoot>
                                </tbody>
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


    cargarBodegas();

    function cargarBodegas(){
        $('#bodega').select2({
            ajax: {
                url: '/cardex/listar/bodega',
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


    }

    function obtenerIdBodega() {

        var idBodega = document.getElementById('bodega').value;
        obtenerProductos();
    }

    function obtenerProductos(){
        var idBodega = document.getElementById('bodega').value;
        $('#producto').select2({
            ajax: {
                url: '/cardex/listar/productos',
                data: function(params) {
                    var query = {
                        search: params.term,
                        idBodega:idBodega,
                        type: 'public',
                        page: params.page || 1
                    }

                    // Query parameters will be ?search=[term]&type=public
                    return query;
                }
            }
        });
    }

    function llamar(){

        var idBodega = document.getElementById('bodega');
        var idProducto = document.getElementById('producto');
        axios.get("/listado/cardex/nuevo/"+idProducto.value+"/"+idBodega.value)
            .then( response => {
                let data = response.data;
                console.log(data);


            })
            .catch( err => {
                console.log('nada');
            })
    }

    function cargaCardex(){

        $("#tbl_cardex").dataTable().fnDestroy();

        var idBodega = document.getElementById('bodega');
        var idProducto = document.getElementById('producto');
        //console.log(idBodega.options[idBodega.selectedIndex].text, idProducto.options[idProducto.selectedIndex].text);
        $('#tbl_cardex').DataTable({
            "paging": false,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            pageLength: 10,
            responsive: true,
            dom: '<"html5buttons"B>lTfgitp',
            buttons: [
                {
                    extend: 'excel',
                    title: 'Cardex'
                }
            ],
            "ajax": "/listado/cardex/nuevo/"+idProducto.value+"/"+idBodega.value,
            "columns": [
                {
                    data: 'fecha_creacion'
                },
                {
                    data: 'producto'
                },
                {
                    data: 'id_producto'
                },/*
                {
                    data: 'doc_factura'
                },
                {
                    data: 'doc_ajuste'
                },
                {
                    data: 'detalleCompra'
                },
                {
                    data: 'comprobante_entrega'
                },
                {
                    data: 'vale_tipo_1'
                },
                /*{
                    data: 'vale_tipo_2'
                },*/
                {
                    data: 'nota_credito'
                },

                {
                    data: 'descripcion'
                },
                {
                    data: 'origen'
                },
                {
                    data: 'destino'
                },
                {
                    data: 'cantidad'
                },
                {
                    data: 'usuario'
                },
            ]


        });
    }
</script>

@endpush
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
