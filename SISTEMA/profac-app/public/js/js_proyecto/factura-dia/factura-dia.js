
function cargaConsulta(){

    $("#tbl_facdia").dataTable().fnDestroy();

    var fecha_inicio = document.getElementById('fecha_inicio').value;
    var fecha_final = document.getElementById('fecha_final').value;

    $('#tbl_facdia').DataTable({
        "order": ['0', 'desc'],
        "paging": true,
        "language": {
            "decimal":        "",
            "emptyTable":     "No hay datos disponibles",
            "info":           "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty":      "Mostrando 0 a 0 de 0 registros",
            "infoFiltered":   "(filtrado de _MAX_ registros totales)",
            "lengthMenu":     "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing":     "Procesando...",
            "search":         "Buscar:",
            "zeroRecords":    "No se encontraron registros",
            "paginate": {
                "first":    "Primero",
                "last":     "Último",
                "next":     "Siguiente",
                "previous": "Anterior"
            }
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
        "ajax": "/consulta/"+fecha_inicio+"/"+fecha_final,
        "columns": [
            {
                data: 'fecha'
            },
            {
                data: 'mes'
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
                data: 'facturador'
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
                data: 'tipo'
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
                    var footer = column.footer();
                    if (!footer) return;
                    let title = footer.textContent;

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
