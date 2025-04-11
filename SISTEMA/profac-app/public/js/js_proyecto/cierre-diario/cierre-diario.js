

$('#divcierre').css('display','none');
$('#divcierre').hide();
$('#btn_cierreCaja').css('display','none');

$('#btn_cierreCaja').hide();

function cargaConsulta(){
    $('#baner1').css('display','none');

    $('#baner2').css('display','none');

    $('#baner3').css('display','none');

    $("#tbl_contado").dataTable().fnDestroy();
    $("#tbl_credito").dataTable().fnDestroy();
    $("#tbl_anuladas").dataTable().fnDestroy();

    var fecha = document.getElementById('fecha').value;

    /*LLENADO DE LAS DISTINTAS TABLAS*/

    $('#tbl_contado').DataTable({
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
                title: 'Facuracion_dia',
                className:'btn btn-success'
            }
        ],
        "ajax": "/contado/"+fecha,
        "columns": [
            /*{
                data: 'fecha'
            },
            {
                data: 'mes'
            },*/
            {
                data: 'codigofactura'
            },
            {
                data: 'factura'
            },
            {
                data: 'cliente'
            },
            {
                data: 'vendedor'
            },
            {
                data: 'subtotal'
            },

            {
                data: 'imp_venta'
            },
            {
                data: 'total'
            },
            {
                data: 'tipo',
                render: function (data, type, row) {


                    if(data === 'CLIENTE B'){
                        return "<span class='badge badge-primary'>"+data+"</span>";
                    }else if(data === 'CLIENTE A'){
                        return "<span class='badge badge-info'>"+data+"</span>";
                    }


                }
            },
            {
                data: 'PagoMediante',
                render: function (data, type, row) {


                    if(data === 'SIN ASIGNAR'){
                        return "<span class='badge badge-warning'>"+data+"</span>";
                    }else {
                        return "<span class='badge badge-success'>"+data+"</span>";
                    }


                }
            },
            {
                data: 'acciones'
            },

        ],initComplete: function () {
            var r = $('#tbl_contado tfoot tr');
            r.find('th').each(function(){
              $(this).css('padding', 8);
            });
            $('#tbl_contado thead').append(r);
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

    $('#tbl_credito').DataTable({
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
                title: 'Facuracion_dia',
                className:'btn btn-success'
            }
        ],
        "ajax": "/credito/"+fecha,
        "columns": [
            /*{
                data: 'fecha'
            },
            {
                data: 'mes'
            },*/
            {
                data: 'codigofactura'
            },
            {
                data: 'factura'
            },
            {
                data: 'cliente'
            },
            {
                data: 'vendedor'
            },
            {
                data: 'subtotal'
            },

            {
                data: 'imp_venta'
            },
            {
                data: 'total'
            },
            {
                data: 'tipo',
                render: function (data, type, row) {


                    if(data === 'CLIENTE B'){
                        return "<span class='badge badge-primary'>"+data+"</span>";
                    }else if(data === 'CLIENTE A'){
                        return "<span class='badge badge-info'>"+data+"</span>";
                    }


                }
            },
        ],initComplete: function () {
            var r = $('#tbl_credito tfoot tr');
            r.find('th').each(function(){
              $(this).css('padding', 8);
            });
            $('#tbl_credito thead').append(r);
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

    $('#tbl_anuladas').DataTable({
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
                title: 'Facuracion_dia',
                className:'btn btn-success'
            }
        ],
        "ajax": "/anuladas/"+fecha,
        "columns": [
            /*{
                data: 'fecha'
            },
            {
                data: 'mes'
            },*/
            {
                data: 'codigofactura'
            },
            {
                data: 'factura'
            },
            {
                data: 'cliente'
            },
            {
                data: 'vendedor'
            },
            {
                data: 'subtotal'
            },

            {
                data: 'imp_venta'
            },
            {
                data: 'total'
            },
            {
                data: 'tipo',
                render: function (data, type, row) {


                    if(data === 'CLIENTE B'){
                        return "<span class='badge badge-primary'>"+data+"</span>";
                    }else if(data === 'CLIENTE A'){
                        return "<span class='badge badge-info'>"+data+"</span>";
                    }


                }
            },
        ],initComplete: function () {
            var r = $('#tbl_anuladas tfoot tr');
            r.find('th').each(function(){
              $(this).css('padding', 8);
            });
            $('#tbl_anuladas thead').append(r);
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

    /*====================================*/



    cargarTotales();



}

function cargarTotales(){

    var fecha1 = document.getElementById('fecha').value;

    axios.get("/carga/totales/"+fecha1)
    .then(response => {

        $('#totalContado').val(response.data.totalContado);
        $('#totalCredito').val(response.data.totalCredito);
        $('#totalAnuladas').val(response.data.totalAnulado);




        $('#inputTotalContado').val(response.data.totalContado);
        $('#inputTotalCredito').val(response.data.totalCredito);
        $('#inputTotalAnulado').val(response.data.totalAnulado);

        var existencia = response.data.banderaCierre;

        if(existencia === 0){

            $('#baner2').css('display','block');
            $('#divcierre').css('display','block');
            $('#divcierre').show();
            $('#btn_cierreCaja').css('display','block');
            $('#btn_cierreCaja').show();
        }else{

            $('#baner3').css('display','block');
        }


    })
    .catch(err => {
        let data = err.response.data;
        Swal.fire({
            icon: data.icon,
            title: data.title,
            text: data.text
        })
        console.error(err);

    });
}

$(document).on('submit', '#cerrarCaja', function(event) {

    $('#divcierre').css('display','none');
    $('#divcierre').hide();
    $('#btn_cierreCaja').css('display','none');
    $('#btn_cierreCaja').hide();
    event.preventDefault();
    guardarCierre();
});

function guardarCierre(){
    var fechaFacturas = document.getElementById('fecha').value;
    var data = new FormData($('#cerrarCaja').get(0));

    axios.post("/cierre/guardar/"+fechaFacturas, data)
        .then(response => {
            $('#cerrarCaja').parsley().reset();
            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Ha realizado el cierre de caja con exito."
            });
            location. reload()

    })
    .catch(err => {
        let data = err.response.data;
        Swal.fire({
            icon: data.icon,
            title: data.title,
            text: data.text
        })
        console.error(err);

    })
}

function cargarInputFactura(codigofactura,factura, PagoMediante){
   // $('#myModalExito').modal('show'); // abrir
    //$('#myModalExito').modal('hide'); // cerrar

    var fechaFacturas1 = document.getElementById('fecha').value;
    //console.log(factura);
    $('#fechaCierreC').val(fechaFacturas1);
    $('#inputFactura').val(factura);
    $('#inputFacturaCodigo').val(codigofactura);

    obj = document.getElementById("selectTipoCierre");
    newSel = '<option class="form-control"  selected  value="'+PagoMediante+'">'+PagoMediante+' - Actuál</option><option class="form-control" value="EFECTIVO">EFECTIVO</option><option class="form-control"  value="TRANSFERENCIA BANCARIA">TRANSFERENCIA BANCARIA</option><option class="form-control" value="CHEQUE">CHEQUE</option>';
    obj.innerHTML = newSel;


    $('#modalCobro').modal('show');

}

$(document).on('submit', '#formtipoCobro', function(event) {
    event.preventDefault();
    guardarTipoCobro();
});

function guardarTipoCobro(){

   let factura = document.getElementById('inputFactura').value;

   let codigofactura = document.getElementById('inputFacturaCodigo').value;
    var data = new FormData($('#formtipoCobro').get(0));
    //console.log(data);
    //$('#formtipoCobro').parsley().reset();
    document.getElementById("fechaCierreC").value = "";
    document.getElementById("inputFactura").value = "";

    document.getElementById("inputFacturaCodigo").value = "";
    const limpiar = () => {
        for (let i = document.querySelector("#selectTipoCierre").options.length; i >= 0; i--) {
            document.querySelector("#selectTipoCierre").remove(i);
        }
      };
    $('#modalCobro').modal('hide');

    axios.post("/registro/tipoC", data)
        .then(response => {

            $('#tbl_contado').DataTable().ajax.reload();

            let data = response.data;
            /*Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Se ha registrado el tipo de cobro para la factura # "+factura
            });*/
            Swal.fire({
                icon: data.icon,
                title: data.title,
                text: data.text
            })


    })
    .catch(err => {
        let data = err.response.data;
        Swal.fire({
            icon: data.icon,
            title: data.title,
            text: data.text
        })
        console.error(err);

    });
}
