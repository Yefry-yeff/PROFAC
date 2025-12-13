
$( document ).ready(function() {

    $('#tbl_facturasVendedor_cerradas').DataTable({
        "order": [1, 'asc'],
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
        "ajax": "/listar/cerradas",
        "columns": [
            {
                data: 'id'
            },
            {
                data: 'mesFactura'
            },
            {
                data: 'numero_factura'
            },
            {
                data: 'fecha_emision'
            },
            {
                data: 'fecha_vencimiento'
            },
            {
                data: 'fechaGracia'
            },
            {
                data: 'nombre'
            },
            {
                data: 'total'
            },
            {
                data: 'estadoPago'
            },
            {
                data: 'comision'
            },
            {
                data: 'acciones'
            }

        ]


    });

    $('#tbl_facturasVendedor_sinCerrar').DataTable({
        "order": [1, 'asc'],
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
        "ajax": "/listar/sinCerrar",
        "columns": [
            {
                data: 'id'
            },
            {
                data: 'mesFactura'
            },
            {
                data: 'numero_factura'
            },
            {
                data: 'fecha_emision'
            },
            {
                data: 'fecha_vencimiento'
            },
            {
                data: 'fechaGracia'
            },
            {
                data: 'nombre'
            },
            {
                data: 'total'
            },
            {
                data: 'estadoPago'
            },
            {
                data: 'comision'
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
        "ajax": "/listar/pagos",
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
