<div>
    @push('styles')
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Listado De Facturas Anuladas</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <a>{{$nombreTipo}}</a>
                </li>


            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_listar_compras" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Codigo Interno</th>
                                        <th>N° Factura</th>
                                        <th>CAI</th>
                                        <th>Fecha de Emision</th>
                                        <th>Cliente</th>
                                        <th>Tipo de Pago</th>
                                        <th>Fecha de Vencimiento</th>
                                        <th>Sub Total Lps.</th>
                                        <th>ISV en Lps.</th>
                                        <th>Total en Lps.</th>
                                        <th>Esto de Cobro</th>
                                        <th>Vendedor</th>
                                        <th>Facturador</th>
                                        <th>Fecha Registro</th>
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

    <!-- Modal para registro de marca-->
    <div class="modal fade" id="modal_detalle_anular" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="exampleModalLabel">Registro de Marca</h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <form id="crearProductoForm" name="crearProductoForm" data-parsley-validate>
                                {{-- <input type="hidden" name="_token" value="{!! csrf_token() !!}"> --}}
                                <div class="row" id="row_datos">
                                    <div class="col-md-12">
                                        <label for="codigo" class="col-form-label focus-label">Código
                                            de factua:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="codigo"
                                            name="codigo" data-parsley-required readonly>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="cai" class="col-form-label focus-label">CAI:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="cai"
                                            name="cai" data-parsley-required readonly>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="motivo" class="col-form-label focus-label">Motivo:<span class="text-danger">*</span></label>
                                         <textarea class="form-control" required name="motivo" id="motivo"  rows="4" readonly></textarea>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="usuario" class="col-form-label focus-label">Anulado por:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="usuario"
                                            name="usuario" data-parsley-required readonly>
                                    </div>






                                </div>
                            </form>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>


                        </div>
                    </div>
                </div>
            </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
            $('#tbl_listar_compras').DataTable({
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
                        title: 'Facuras_anuladas',
                        className:'btn btn-success'
                    }
                ],
                "ajax": "/ventas/anulado/listado",
                "ajax":{
                    'url':"/ventas/anulado/listado",
                    'data' : {'idTipo' : {{$idTipoVenta}} },
                    'type' : 'post',
                    'headers': {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }


                     },
                "columns": [
                    {
                        data: 'id'
                    },
                    {
                        data: 'numero_factura'
                    },
                    {
                        data: 'cai'
                    },
                    {
                        data: 'fecha_emision'
                    },
                    {
                        data: 'nombre'
                    },
                    {
                        data: 'descripcion'
                    },
                    {
                        data: 'fecha_vencimiento'
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
                        data: 'estado_cobro'
                    },
                    {
                        data: 'vendedor'
                    },
                    {
                        data: 'facturador'
                    },
                    {
                        data: 'fecha_registro',
                    },

                    {
                        data: 'opciones'
                    }

                ]


            });
            })

        function anularVentaConfirmar(idFactura){

            Swal.fire({
            title: '¿Está seguro de anular esta factura?',
            text:'Una vez que ha sido anulada la factura el producto registrado en la misma sera devuelto al inventario.',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Si, Anular Compra',
            cancelButtonText: `Cancelar`,
            }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {

                //Swal.fire('Saved!', '', 'success')
                anularVenta(idFactura);

            }
            })
        }

        function anularVenta(idFactura){

            axios.post("/factura/corporativo/anular", {idFactura:idFactura})
            .then( response =>{


                let data = response.data;
                Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            html: data.text,
                        });
                        $('#tbl_listar_compras').DataTable().ajax.reload();

            })
            .catch( err => {

                Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Ha ocurrido un error al anular la compra.',
                        })

            })

        }

        function detallesDeAnulacion(idFactura){
            axios.post('/ventas/anulado/detalle',{id:idFactura})
            .then(response=>{
                let data = response.data.datos;
                document.getElementById('codigo').value = data.codigo_factura
                document.getElementById('cai').value = data.cai
                document.getElementById('motivo').value = data.motivo
                document.getElementById('usuario').value = data.usuario
                $('#modal_detalle_anular').modal('show')
            })
            .catch( err=>{
                    console.log(err)
            })
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
