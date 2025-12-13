<div>
    @push('styles')
        <style>



        </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Cobros de venta</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a >Listado</a>
                </li>
                <li class="breadcrumb-item">
                    <a>Registo</a>
                </li>

            </ol>
        </div>

        <div class="col-lg-4 col-xl-2 col-md-4 col-sm-4">
            <div style="margin-top: 1.5rem">
                <a href="#" class="btn add-btn btn-primary" data-toggle="modal" data-target="#modal_registro_pagos"><i
                        class="fa fa-plus"></i> Registrar Cobro de factura</a>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="d-flex justify-content-between my-2">
                            <h3>Faltante de cobro: <span id="debitoCompra" class="text-danger"></span></h3>
                            <h3>Total de venta: <span id="totalComra" class="text-info"></span></h3>


                        </div>
                        <hr>
                        <div class="table-responsive">
                            <table id="tbl_listar_pagos" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>N° de Pago</th>

                                        <th>CAI</th>
                                        <th>Monto</th>
                                        <th>Tipo de Pago</th>
                                        <th>Banco</th>
                                        <th>Fecha de Pago</th>
                                        <th>Registrado por:</th>
                                        <th>Registrado en sistema:</th>
                                        <th>Evidencia</th>
                                        <th>Eliminar Pago</th>
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

        <!---MODAL PARA REGISTRAR PAGOS----->
        <div id="modal_registro_pagos" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered ">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title text-success">Registro de Cobros</h5>
                        </h3>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="form_registro_pago" name="form_registro_pago" data-parsley-validate>

                            <input type="hidden" id="compraId" name="compraId">

                            <div class="row" >

                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                    <label class="col-form-label focus-label">Numero de Factura:<span class="text-danger">*</span></label>
                                    <input class="form-control" required type="text"  id="numero_factura" name="numero_factura" readonly
                                      value="{{ $datosFactura->numero_factura }}"  data-parsley-required>
                                </div>
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                    <label for="cai" class="col-form-label focus-label">CAI:<span class="text-danger">*</span></label>
                                    <input class="form-control" required type="text"  id="cai" name="cai" readonly
                                        data-parsley-required value="{{$datosFactura->cai}}">
                                </div>
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                    <label for="cliente" class="col-form-label focus-label">Cliente:<span class="text-danger">*</span></label>
                                    <input class="form-control" required type="text"  id="cliente" name="cliente" readonly
                                        data-parsley-required value="{{$datosFactura->nombre_cliente}}">
                                </div>
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 mt-3">
                                    <label for="metoPago" class="col-form-label focus-label">Seleccionar Metodo de Pago:<span class="text-danger">*</span></label>
                                    <select id="metoPago" name="metoPago" class="form-group form-control" style=""
                                        data-parsley-required onchange="">
                                        <option value="" selected disabled>--Seleccionar un metodo--</option>
                                        <option value="1" >Pago con efectivo</option>
                                        <option value="2" >Transferencia bancaria</option>
                                        <option value="3" >Cheque</option>
                                    </select>
                                </div>
                                <div id="bancoCuenta" class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 mt-3 ">
                                    <label for="banco" class="col-form-label focus-label">Seleccionar Banco y Cuenta:</label>
                                    <select id="banco" name="banco" class="form-group form-control" style=""
                                         >
                                        <option value="" selected disabled>--Seleccionar un metodo--</option>
                                        @foreach ($bancos as $item)
                                        <option value="{{$item->id}}" >{{$item->nombre}}-{{$item->cuenta}}</option>
                                        @endforeach



                                    </select>
                                </div>

                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 ">
                                    <label for="monto" class="col-form-label focus-label">Monto a pagar:<span class="text-danger">*</span></label>
                                    <input class="form-control" required type="number" step="any" id="monto" name="monto" min="0"
                                        data-parsley-required>
                                </div>
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 mt-3">
                                    <label for="fecha_pago" class="col-form-label focus-label">Fecha que se realizo el pago:<span class="text-danger">*</span></label>
                                    <input class="form-control" required type="date" id="fecha_pago" name="fecha_pago"
                                        data-parsley-required>
                                </div>
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 mt-3">
                                    <label for="img_pago" class="col-form-label focus-label">Documento de Pago:<span class="text-danger">*</span></label>
                                    <input class="form-control"  id="img_pago" name="img_pago" type="file" accept="image/png, image/jpeg, image/jpg, application/pdf">
                                </div>
                            </div>
                        </form>
                        <button id="btn_registro_pago" class="btn btn-sm btn-primary float-left mt-4"
                            form="form_registro_pago" >
                            <strong>Registrar Pago
                                </strong>

                            </button>
                    </div>

                </div>
            </div>
        </div>

    @push('scripts')


    <script>
        var idFactura = {{$idFactura}};


        //window.onload=datosCompra;


        $(document).ready(
            function() {

            datosCompra();

            $('#tbl_listar_pagos').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                },
                pageLength: 10,
                responsive: true,
                "ajax": "/venta/litsado/pagos/"+idFactura,
                "columns": [
                                {
                                    data: 'contador'
                                },


                                {
                                    data: 'cai'
                                },
                                {
                                    data: 'monto'
                                },
                                {
                                    data: 'tipo_pago'
                                },
                                {
                                    data: 'banco'
                                },
                                {
                                    data: 'fecha'
                                },
                                {
                                    data: 'name'
                                },
                                {
                                    data: 'created_at'
                                },
                                {
                                    data:'documento'
                                },
                                {
                                    data: 'opciones'
                                },

                            ]


            });

        }

        );


        $(document).on('submit', '#form_registro_pago', function(event) {
        event.preventDefault();
        registrarPago();
        });



        function registrarPago(){

            document.getElementById('btn_registro_pago').disabled = true;


            document.getElementById("compraId").value=idFactura;
            var data = new FormData($('#form_registro_pago').get(0));
            data.append('idFactura',idFactura);

            axios.post('/venta/registro/cobro', data)
            .then( response =>{

                let data = response.data;

                if(data.icon == "success"){
                    document.getElementById('form_registro_pago').reset();

                   $('#form_registro_pago').parsley().reset();
                   $('#modal_registro_pagos').modal('hide')

                   Swal.fire({
                   icon: data.icon,
                   title: data.title,
                   text: data.text,
                   })

                   datosCompra();
                   $('#tbl_listar_pagos').DataTable().ajax.reload();
                }

                Swal.fire({
                   icon: data.icon,
                   title: data.title,
                   text: data.text,
                   })

                   document.getElementById('form_registro_pago').reset();

                   $('#form_registro_pago').parsley().reset();
                   $('#modal_registro_pagos').modal('hide')
                   document.getElementById('btn_registro_pago').disabled = false;
                   return;



            })
            .catch( err =>{
                $('#modal_registro_pagos').modal('hide')
                document.getElementById('btn_registro_pago').disabled = false;
                Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Ha ocurrido un error al guardar el registro de pago!',
                })

                console.log(err);

            })


        }

        function datosCompra(){
            axios.post('/venta/datos/compra',{idFactura:idFactura})
            .then( response =>{

                let data = response.data.factura;

            document.getElementById("debitoCompra").innerHTML = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'Lps' }).format(data.pendiente_cobro);
            document.getElementById("totalComra").innerHTML = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'Lps' }).format(data.total);


            })
            .catch( err=>{

                Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Ha ocurrido un error al obtener los datos de compra!',
                })

                console.log(err);


            })
        }

        function confirmarEliminarPago(idPago){

            Swal.fire({
            title: '¿Esta seguro de eliminar este registro de pago?',
            text:'Si elimina este registro de pago, el mismo será restado a los abonos de la deuda.',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Si, eliminar',

            cancelButtonText: `Cancelar`,
            }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                eliminarPago(idPago);
            }
            })

        }

        function eliminarPago(idPago){

            axios.post('/venta/cobro/eliminar', {idPago:idPago})
            .then( response =>{

                Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: 'El registro ha sido eliminado con exito!',
                })

                datosCompra();
                $('#tbl_listar_pagos').DataTable().ajax.reload();

            })
            .catch(err=>{
                Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Ha ocurrido un error a eliminar el registro!',
                });

                console.log(err);

            })

        }

        function bancosMostrarOcultar(){
            let meotodo = document.getElementById("metoPago");
            let element = document.getElementById('bancoCuenta');

            if(meotodo.value==1){

                element.classList.add("d-none");
            }else{
                element.classList.remove("d-none");
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
