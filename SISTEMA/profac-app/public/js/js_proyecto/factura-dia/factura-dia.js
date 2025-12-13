
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
