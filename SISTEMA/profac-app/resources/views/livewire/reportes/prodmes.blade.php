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
            <h2>Reporte de comision</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.html">/ Comision</a>
                </li>


            </ol>
        </div>

    </div>


    <p> <b>Nota: </b> Se requiere de selección de un rango de fechas para mostrar la información.</p>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="row">


                            <div class="col-6 col-sm-6 col-md-6 ">
                                <label for="fecha_inicio" class="col-form-label focus-label">Fecha de inicio:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" type="date" id="fecha_inicio" name="fecha_inicio" value="{{date('Y-m-01')}}">
                            </div>

                            <div class="col-6 col-sm-6 col-md-6">
                                <label for="fecha_final" class="col-form-label focus-label">Fecha final:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" type="date" id="fecha_final" name="fecha_final" value="{{date('Y-m-t')}}">
                            </div>

                        </div>
                        <button class="btn btn-primary" onclick="cargaConsulta()"><i class="fa-solid fa-paper-plane text-white"></i> Solicitar</button>
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
                            <table id="tbl_facdia" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>FECHA</th>
                                        <th>FECHA VENCIMIENTO</th>
                                        <th>CRÉDITO/CONTADO</th>
                                        <th>TIPO CLIENTE (AoB)</th>
                                        <th>VENDEDOR</th>
                                        <th>FACTURA</th>
                                        <th>CLIENTE</th>
                                        <th>CÓDIGO</th>
                                        <th>PRODUCTO</th>
                                        <th>PRECIO PRODUCTO</th>
                                        <th>CANTIDAD</th>
                                        <th>SUB TOTAL PRODUCTO</th>
                                        <th>ISV</th>
                                        <th>TOTAL PRODUCTO</th>
                                        <th>SUB TOTAL FACTURA</th>
                                        <th>TOTAL FACTURA</th>
                                        <th>SUB TOTAL DIFERENCIA</th>
                                        <th>CONTADO 1.75%</th>
                                        <th>CREDITO 1.5%</th>
                                        <th>COMISION OTROS PRUEBA</th>
                                    </tr>
                                </thead>

                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>FECHA</th>
                                        <th>FECHA VENCIMIENTO</th>
                                        <th>CRÉDITO/CONTADO</th>
                                        <th>TIPO CLIENTE (AoB)</th>
                                        <th>VENDEDOR</th>
                                        <th>FACTURA</th>
                                        <th>CLIENTE</th>
                                        <th>CÓDIGO</th>
                                        <th>PRODUCTO</th>
                                        <th>PRECIO PRODUCTO</th>
                                        <th>CANTIDAD</th>
                                        <th>SUB TOTAL PRODUCTO</th>
                                        <th>ISV</th>
                                        <th>TOTAL PRODUCTO</th>
                                        <th>SUB TOTAL FACTURA</th>
                                        <th>TOTAL FACTURA</th>
                                        <th>SUB TOTAL DIFERENCIA</th>
                                        <th>CONTADO 1.75%</th>
                                        <th>CREDITO 1.5%</th>
                                        <th>COMISION OTROS PRUEBA</th>
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




    function cargaConsulta(){

        $("#tbl_facdia").dataTable().fnDestroy();

        var fecha_inicio = document.getElementById('fecha_inicio').value;
        var fecha_final = document.getElementById('fecha_final').value;

        $('#tbl_facdia').DataTable({
            "order": ['0', 'desc'],
            "paging": true,
            "language": {
                "url": "//cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css"
            },
            pageLength: 10,
            responsive: true,
            dom: '<"html5buttons"B>lTfgitp',
            buttons: [

                {
                    extend: 'excel',
                    title: 'COMISIONES',
                    className:'btn btn-success'
                }
            ],
            "ajax": "/consultaComision/"+fecha_inicio+"/"+fecha_final,
            "columns": [
                {data: 'FECHA'},
                {data: 'FECHA VENCIMIENTO'},
                {data: 'CRÉDITO/CONTADO'},
                {data: 'TIPO CLIENTE (AoB)'},
                {data: 'VENDEDOR'},
                {data: 'FACTURA'},
                {data: 'CLIENTE'},
                {data: 'CÓDIGO'},
                {data: 'PRODUCTO'},
				{data: 'PRECIO PRODUCTO'},
                {data: 'CANTIDAD'},
                {data: 'SUB TOTAL PRODUCTO'},
                {data: 'ISV'},
				{data: 'TOTAL PRODUCTO'},
                {data: 'SUB TOTAL FACTURA'},
                {data: 'TOTAL FACTURA'},
                {data: 'SUB TOTAL DIFERENCIA'},
                {data: 'CONTADO 1.75%'},
                {data: 'CREDITO 1.5%'},
                {data: 'COMISION OTROS PRUEBA'}
            ],initComplete: function () {
                var r = $('#tbl_facdia tfoot tr');
                r.find('th').each(function(){
                  $(this).css('padding', 8);
                });
                $('#tbl_facdia thead').append(r);
                $('#search_0').css('text-align', 'center');
                this.api()
                    .columns()
                    .every(function () {
                        let column = this;
                        let title = column.footer().textContent;

                        // Create input element
                        let input = document.createElement('input');
                        input.placeholder = title;
                        column.footer().replaceChildren(input);

                        // Event listener for user input
                        input.addEventListener('keyup', () => {
                            if (column.search() !== this.value) {
                                column.search(input.value).draw();
                            }
                        });
                    });




            }


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
