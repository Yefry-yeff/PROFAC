
var modalx ;
var formx ;
function asignacion(modalName, formName){
    //console.log(modalName, formName);
    $("#modalx").val(modalName);
    $("#formx").val(formName);
    modalx = $("#modalx").val();
    formx = $("#formx").val();

    //console.log(`'#${formx}'`);
}

$( document ).ready(function() {

    $('#tbl_historico_comisones').DataTable({
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
        "ajax": "/historico/listar",
        "columns": [
            {
                data: 'codigoComision'
            },
            {
                data: 'idFactura'
            },
            {
                data: 'numero_factura'
            },
            {
                data: 'vendedor_id'
            },
            {
                data: 'vendedor'
            },
            {
                data: 'mes'
            },
            {
                data: 'gananciaFactura'
            },
            {
                data: 'porcentaje'
            },
            {
                data: 'montoAsignado'
            },
            {
                data: 'userRegistro'
            },
            {
                data: 'fechaRegistro'
            },
            {
                data: 'acciones'
            }


        ]


    });

    $('#tbl_historico_comisionesMes').DataTable({
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
        "ajax": "/historico/listar/mes",
        "columns": [
            {
                data: 'codigoVendedor'
            },
            {
                data: 'vendedor'
            },
            {
                data: 'mes'
            },
            {
                data: 'facturasComisionadas'
            },
            {
                data: 'montotecho'
            },
            {
                data: 'gananciatotalMes'
            },
            {
                data: 'montoAsignado'
            },
            {
                data: 'estadoPago'
            },
            {
                data: 'acciones'
            }


        ]


    });

    $('#tbl_historico_comisionesPagadas').DataTable({
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
        "ajax": "/historico/listar/pagos",
        "columns": [
            {
                data: 'vendedor_id'
            },
            {
                data: 'nombre_vendedor'
            },
            {
                data: 'mes_comision'
            },
            {
                data: 'meses_id'
            },
            {
                data: 'cantidad_facturas'
            },
            {
                data: 'techo_asignado'
            },
            {
                data: 'ganancia_total'
            },
            {
                data: 'monto_asignado'
            },
            {
                data: 'users_registra_id'
            },
            {
                data: 'created_at'
            }


        ]


    });

});




/*$(document).on('submit', `'#${formx}'` , function(event) {
    event.preventDefault();
    registrarPago();
});*/




function registrarPago() {
    modalx = $("#modalx").val();
    formx = $("#formx").val();
        //console.log(modalx, formx);
       $(`#${modalx}`).modal('hide');
        $('#modalSpinnerLoading').modal('show');
        var data = new FormData($(`#${formx}`).get(0));
        //console.log(data)
        //console.log(data);
        axios.post("/historico/registrar/pago", data)
            .then(response => {



                document.getElementById(`${formx}`).reset();


               $('#modalSpinnerLoading').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Exito!',
                    text: "Pago guardado con Éxito."
                });


                window.location.href = "/comisiones/historico";

            })
            .catch(err => {
                let data = err.response.data;
                $('#modalSpinnerLoading').modal('hide');

                document.getElementById(`#${formx}`).reset();
                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text
                })
                console.error(err);

            });
}
