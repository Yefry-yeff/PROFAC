<div>
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <h2>Desglose de productos y ganancias. </h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.html">Gestiones de desglose de productos </a>
                </li>


            </ol>
        </div>


    </div>
    <br>
    <div class="jumbotron jumbotron-fluid">
        <div class="container">
          <h1 class="display-4">Factura con código: {{ $idFactura }}</h1>
          <p class="lead">Total de ganancia del vendedor para comisionar: Lps. <b>{{ $gananciaTotal->gananciaTotal }}</b> </p>
            <div style="margin-top: 1.5rem">
                @if($centComisionado->estado == 0)

                    <a href="#" class="btn add-btn btn-primary" data-toggle="modal"
                    data-target="#modal_comision_crear"><i class="fa fa-plus"></i>Asignar comisión</a>
                @else
                    <div class="alert alert-success" role="alert">
                        <h4 class="alert-heading">Esta factura ya fue comisionada</h4>
                        <hr>
                    </div>
                @endif
            </div>
        </div>
      </div>

      <div class="modal" id="modalSpinnerLoading" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalSpinnerLoadingTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">

                    <div class="modal-body">
                        <h2 class="text-center">Espere un momento...</h2>
                        <div class="loader">Loading...</div>

                    </div>

                </div>
            </div>
        </div>

    <div class="modal fade" id="modal_comision_crear" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="exampleModalLabel">Registro de Techos de Comisiones</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="comisionForm" name="comisionForm" data-parsley-validate>
                        <Label>Nota: El porcentaje aplicado será guardado unicamente a ésta factura, de no ser lo que necesita, regresar a la pantalla anterior a comisionar masivamente.</Label>
                        <div class="row" id="row_datos">
                                <div class="col-md-12">
                                    <label  class="col-form-label focus-label">Código de Factura No: <span class="text-danger">*</span></label>
                                    <input readonly class="form-control" required type="number"  id="factura" name="factura" value="{{ $idFactura }}" data-parsley-required>
                                    <input readonly class="form-control" required type="hidden" id="mesFactura" name="mesFactura" value="{{ $mesFactura->mes }}">
                                </div>
                                <div class="col-md-12">
                                    <label  class="col-form-label focus-label">Código de vendedor: <span class="text-danger">*</span></label>
                                    <input readonly class="form-control" required type="number"  id="idVendedor" value="{{ $idVendedor->id }}" name="idVendedor" data-parsley-required >
                                </div>
                                <div class="col-md-12">
                                    <label  class="col-form-label focus-label">Total por comisionar: <span class="text-danger">*</span></label>
                                    <input readonly class="form-control" required type="text"  id="gananciaTotal" name="gananciaTotal" value="{{ $gananciaTotal->gananciaTotal }}" data-parsley-required >
                                </div>
                                <div class="col-md-12">
                                    <label  class="col-form-label focus-label">Porcentaje de 0 a 100 (Ejemplo: 50 - equivaliendo al 50%): <span class="text-danger">*</span></label>
                                    <input  class="form-control" required type="number" min="0" id="porcentaje" name="porcentaje"  data-parsley-required >
                                </div>
                        </div>
                    </form>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" form="comisionForm" class="btn btn-primary">Guardar Comisión</button>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <label for="" class="col-form-label focus-label"><b> Lista de productos:</b></label>
        <input type="hidden" name="idFactura" id="idFactura" value="{{ $idFactura }}">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_productos_factura" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Código Factura</th>

                                        <th>Nº Factura</th>

                                        <th>Código producto</th>

                                        <th>Producto</th>

                                        <th>Precio Base</th>

                                        <th>Último costo de compra</th>

                                        <th>Unidad</th>

                                        <th>Cantidad</th>

                                        <th>Precio de Venta</th>

                                        <th>Ganancia x Unidad</th>


                                        <th>Ganancia total vendedor</th>
                                        <th>Total Facturado</th>
                                        <th>Sub Total</th>
                                        <th>ISV</th>
                                        <th>Código de sección</th>
                                        <th>Sección</th>
                                        <th>Bodega</th>

                                        <th>Comisionado</th>
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
         var idFactura = document.getElementById('idFactura').value;
         $( document ).ready(function() {

                $('#tbl_productos_factura').DataTable({
                    "order": [0, 'desc'],
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
                    "ajax": "/desglose/productos/"+idFactura,
                    "columns": [
                        {
                            data: 'idFactura'
                        },
                        {
                            data: 'numero_factura'
                        },
                        {
                            data: 'idProducto'
                        },
                        {
                            data: 'producto'
                        },
                        {
                            data: 'precio_base'
                        },
                        {
                            data: 'ultimo_costo_compra'
                        },
                        {
                            data: 'unidad_venta'
                        },
                        {
                            data: 'cantidad'
                        },
                        {
                            data: 'precio_unidad'
                        },
                        {
                            data: 'gananciaUnidad'
                        },
                        {
                            data: 'gananciatotal'
                        },
                        {
                            data: 'total'
                        },
                        {
                            data: 'sub_total'
                        },
                        {
                            data: 'isv'
                        },
                        {
                            data: 'seccion_id'
                        },
                        {
                            data: 'seccion'
                        },
                        {
                            data: 'bodega'
                        },
                        {
                            data: 'estadoComisionado'
                        }

                    ]


                });
         });





            $(document).on('submit', '#comisionForm', function(event) {
                event.preventDefault();
                guardarComision();
            });

            function guardarComision() {

                    $('#modal_comision_crear').modal('hide');
                    $('#modalSpinnerLoading').modal('show');

                    var data = new FormData($('#comisionForm').get(0));
                    console.log(data);
                    axios.post("/comision/guardar", data)
                        .then(response => {



                            document.getElementById("comisionForm").reset();

                           // $('#tbl_techos_guardados').DataTable().ajax.reload();

                           $('#modalSpinnerLoading').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Exito!',
                                text: "Asignado y guardado con Éxito."
                            });


                            window.location.href = "/comisiones/historico";

                        })
                        .catch(err => {
                            let data = err.response.data;
                            $('#modalSpinnerLoading').modal('hide');

                            document.getElementById("comisionForm").reset();
                            Swal.fire({
                                icon: data.icon,
                                title: data.title,
                                text: data.text
                            })
                            console.error(err);

                        });
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
