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
            <h1>Reporte de productos por Bodega</h1>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.html">/ Consulta</a>
                </li>


            </ol>
        </div>

    </div>


    <p> <b>Nota: </b> Se requiere de selección de la bodega que desea consultar.</p>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="row">


                            <div class="col-6 col-sm-6 col-md-6 ">
                                <div>

                                    <label for="selectBodega" class="col-form-label focus-label">Seleccionar
                                        Bodega:</label>
                                    <select id="selectBodega" class="form-group form-control" style=""
                                        data-parsley-required>
                                        <option value="" selected disabled>--Seleccionar una Bodega--</option>
                                    </select>

                                </div>
                            </div>

                            <div class="col-6 col-sm-6 col-md-6 ">
                                <div class="form-group">

                        <button class="btn btn-primary" onclick="cargaConsulta()"><i class="fa-solid fa-paper-plane text-white"></i> Solicitar</button>

                                </div>
                            </div>

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
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_facdia" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th>Descripción</th>
                                        <th>ISV</th>
                                        <th>Categoría</th>
                                        <th>Bodega</th>
                                        <th>Existencia por Compra</th>
                                        <th>Existencia por Ajuste</th>
                                        <th>Existencia Total</th>
                                    </tr>
                                </thead>
                                <tbody>                                <tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Código</th>
                                            <th>Producto</th>
                                            <th>Descripción</th>
                                            <th>ISV</th>
                                            <th>Categoría</th>
                                            <th>Bodega</th>
                                            <th>Existencia por Compra</th>
                                            <th>Existencia por Ajuste</th>
                                            <th>Existencia Total</th>
                                        </tr>
                                    </tfoot>

                                </tbody>

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


    /*===========================LO QUE SI ME SERVIRÁ=====================================*/
    $('#selectBodega').select2({
        ajax: {
            url: '/translado/lista/bodegas',

        }
    });

   {{--   $(document).on('submit', '#selec_data_form', function(event) {

        event.preventDefault();
        cargaConsulta();

    });  --}}
    /*====================================================================================*/


    function cargaConsulta(){

        $("#tbl_facdia").dataTable().fnDestroy();

        var selectBodega = document.getElementById('selectBodega').value;

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
                    title: 'Productos_Bodega',
                    className:'btn btn-success'
                }
            ],
            "ajax": "/consulta/prod/bodega/"+selectBodega,
            "columns": [
                {
                    data: 'codigo'
                },
                {
                    data: 'producto'
                },
                {
                    data: 'descripcion'
                },
                {
                    data: 'ISV'
                },
                {
                    data: 'categoria'
                },
                {
                    data: 'bodega'
                },

                {
                    data: 'existenciaCompra'
                },
                {
                    data: 'existenciaAjuste'
                },
                {
                    data: 'existencia'
                },
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
