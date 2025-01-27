function carga_libro_cobros() {
    $("#tbl_libro_cobros").dataTable().fnDestroy();

    var fechaInput = document.getElementById('fecha_cobro').value;
    var fecha = new Date(fechaInput).toISOString().split('T')[0]; // Convertimos a texto en formato ISO (YYYY-MM-DD)

    $('#tbl_libro_cobros').DataTable({
        "order": ['0', 'desc'],
        "paging": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        pageLength: 15,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp',
        buttons: [
            { extend: 'copy' },
            { extend: 'csv' },
            { extend: 'excel', title: 'Libro_Cobros '+fecha, className: 'btn btn-success' },
            { extend: 'pdf', title: 'Libro_Cobros '+fecha, className: 'btn btn-danger' }
        ],
        "ajax":  "/reporte/Librocobrosrep/consulta/"+3+"/"+fecha,
        "columns": [
            { data: 'VENDEDOR' },
            { data: 'CLIENTE' },
            { data: 'FACTURA' },
            { data: 'EXONERADO' },
            { data: 'GRAVADO' },
            { data: 'EXCENTO' },
            { data: 'ABONO' },
            { data: 'SUBTOTAL' },
            { data: 'ISV' },
            { data: 'TOTAL' },
            { data: 'RETENCION' },
            { data: 'TOTAL PAGADO' },
            { data: 'FECHA DE COMPRA' },
            { data: 'FECHA DE VENCIMIENTO' },
            { data: 'FECHA DE PAGO' },
            { data: 'BANCO' },
            { data: 'OBSERVACIONES' }
        ],
        initComplete: function () {
            var r = $('#tbl_libro_cobros tfoot tr');
            r.find('th').each(function(){
              $(this).css('padding', 8);
            });
            $('#tbl_libro_cobros thead').append(r);
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
