function cargaCierreDiario(){
    $("#tbl_cierre_diario").dataTable().fnDestroy();

    var fecha = document.getElementById('fecha').value;

    $('#tbl_cierre_diario').DataTable({
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
                title: 'Cierre_Diario',
                className:'btn btn-success'
            },
            {
                extend: 'pdf',
                title: 'Cierre_Diario',
                className:'btn btn-danger'
            }
        ],
        "ajax": "/reporte/cierre-diario/consulta/"+fecha,
        "columns": [
            {data: 'FECHA DE CIERRE'},
            {data: 'REGISTRADO POR'},
            {data: 'ESTADO DE CAI'},
            {data: 'FACTURA'},
            {data: 'CLIENTE'},
            {data: 'VENDEDOR'},
            {data: 'SUBTOTAL FACTURADO'},
            {data: 'ISV FACTURADO'},
            {data: 'TOTAL FACTURADO'},
            {data: 'CALIDAD DE FACTURA'},
            {data: 'TIPO DE CLIENTE'},
            {data: 'PAGO POR'},
            {data: 'BANCO'},
            {data: 'FECHA DE REGISTRO' }
        ],
        initComplete: function () {
            var r = $('#tbl_cierre_diario tfoot tr');
            r.find('th').each(function(){
              $(this).css('padding', 8);
            });
            $('#tbl_cierre_diario thead').append(r);
            $('#search_0').css('text-align', 'center');
            this.api().columns().every(function () {
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
