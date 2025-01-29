function cargaCierreDiario() {
    $("#tbl_cierre_diario").dataTable().fnDestroy();

    var fechaInput = document.getElementById('fecha_cierre').value;
     // Verificamos si la fecha está vacía
     if (!fechaInput) {
        // Mostrar mensaje de error si la fecha no está seleccionada
        document.getElementById('fecha_cierre_error').style.display = 'block';
        document.getElementById('fecha_cierre').style.borderColor = 'red';
        return; // Salir de la función si no hay fecha
    }

    document.getElementById('fecha_cierre').style.borderColor = '';
    document.getElementById('fecha_cierre_error').style.display = 'none';
    var fecha = new Date(fechaInput).toISOString().split('T')[0]; // Convertimos a texto en formato ISO (YYYY-MM-DD)

    $('#tbl_cierre_diario').DataTable({
        "order": ['0', 'desc'],
        "paging": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        pageLength: 8,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp',
        buttons: [
            { extend: 'excel', title: 'Cierre_Diario', className: 'btn btn-success' },
        ],
        "ajax":  "/reporte/Cierrediariorep/consulta/"+1+"/"+fecha,
        "columns": [
            { data: 'FECHA DE CIERRE' },
            { data: 'REGISTRADO POR' },
            { data: 'ESTADO DE CAJA' },
            { data: 'FACTURA' },
            { data: 'CLIENTE' },
            { data: 'VENDEDOR' },
            { data: 'SUBTOTAL FACTURADO' },
            { data: 'ISV FACTURADO' },
            { data: 'TOTAL FACTURADO' },
            { data: 'CALIDAD DE FACTURA' },
            { data: 'TIPO DE CLIENTE' },
            { data: 'PAGO POR' },
            { data: 'BANCO' },
            { data: 'ABONO' },
            { data: 'FECHA DE PAGO' }
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


function exportarPdf() {
    // Obtener la fecha seleccionada
    var fechaInput = document.getElementById('fecha_cierre').value;

    // Validar si el campo de fecha está vacío
    if (!fechaInput) {
        document.getElementById('fecha_cierre_error').style.display = 'block';
        document.getElementById('fecha_cierre').style.borderColor = 'red';
        return;
    }

    // Restaurar estilos si la fecha es válida
    document.getElementById('fecha_cierre').style.borderColor = '';
    document.getElementById('fecha_cierre_error').style.display = 'none';

    // Construir la URL para la exportación
    var url = "/reporte/Cierrediariorep/exportar-pdf/1/" + fechaInput;

    // Abrir la URL en una nueva pestaña para descargar el PDF
    window.open(url, '_blank');
}
