



function cargaCardex(){

    $("#tbl_cardex").dataTable().fnDestroy();

    var fecha_inicio = document.getElementById('fecha_inicio').value;
    var fecha_final = document.getElementById('fecha_final').value;

    $('#tbl_cardex').DataTable({
        "paging": false,
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
                title: 'Cardex'
            },
            {
                extend: 'pdf',
                title: 'Cardex'
            },

            {
                extend: 'print',
                title: '',
                customize: function(win) {
                    $(win.document.body).addClass('white-bg');
                    $(win.document.body).css('font-size', '10px');

                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('font-size', 'inherit');
                }
            }
        ],
        "ajax": "/listado/cardex/general/"+fecha_inicio+"/"+fecha_final,
        "columns": [
            {
                data: 'fechaIngreso'
            },
            {
                data: 'producto'
            },
            {
                data: 'codigoProducto'
            },
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
            {
                data: 'vale_tipo_2'
            },
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
        ],initComplete: function () {
            var r = $('#tbl_cardex tfoot tr');
            r.find('th').each(function(){
              $(this).css('padding', 8);
            });
            $('#tbl_cardex thead').append(r);
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
