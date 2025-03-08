function cargafacturasanuladasrep() {
    $("#tbl_facturas_anuladas").dataTable().fnDestroy();

    var fechaInicioInput = document.getElementById('fecha_inicio').value;
    var fechaFinalInput = document.getElementById('fecha_final').value;

    // Verificamos si las fechas están vacías
    if (!fechaInicioInput || !fechaFinalInput) {
        //document.getElementById('fecha_facturas_anuladas').style.display = 'block';
        document.getElementById('fecha_inicio').style.borderColor = 'red';
        document.getElementById('fecha_final').style.borderColor = 'red';
        return; // Salir de la función si no hay fecha
    }

    document.getElementById('fecha_inicio').style.borderColor = '';
    document.getElementById('fecha_final').style.borderColor = '';
    //document.getElementById('fecha_facturas_anuladas').style.display = 'none';

    var fechaInicio = new Date(fechaInicioInput).toISOString().split('T')[0]; // Convertir fecha de inicio a formato ISO (YYYY-MM-DD)
    var fechaFinal = new Date(fechaFinalInput).toISOString().split('T')[0]; // Convertir fecha final a formato ISO (YYYY-MM-DD)

    $('#tbl_facturas_anuladas').DataTable({
        "order": ['0', 'desc'],
        "paging": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        pageLength: 8,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp',
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'Facturas_Anuladas',
                text: '<i class="fa-solid fa-file-excel"></i> Exportar a Excel',
                className: 'btn-excel'
            },
            {
                extend: 'pdfHtml5',
                title: 'Facturas_Anuladas',
                text: '<i class="fa-solid fa-file-pdf"></i> Exportar a PDF',
                className: 'btn-pdf',
                action: function () {
                    exportarPdf(fechaInicio, fechaFinal); // Llamar a la función de exportación
                }
            }
        ],
        "ajax":  "/reporte/facturasanuladasrep/consulta/2/"+fechaInicio+"/"+fechaFinal, // Ajusta la ruta de acuerdo a tu backend
        "columns": [
            { data: 'FECHA DE CREACION' },
            { data: 'NUMERO FACTURA' },
            { data: 'NOMBRE CLIENTE' },
            { data: 'SUBTOTAL' },
            { data: 'ISV' },
            { data: 'TOTAL' },
            { data: 'TIPO CLIENTE' }
        ],
        initComplete: function () {
            var r = $('#tbl_facturas_anuladas tfoot tr');
            r.find('th').each(function(){
                $(this).css('padding', 8);
            });
            $('#tbl_facturas_anuladas thead').append(r);
            $('#search_0').css('text-align', 'center');
            this.api().columns().every(function () {
                let column = this;
                let title = column.footer().textContent;

                // Crear un input para cada columna
                let input = document.createElement('input');
                input.placeholder = title;
                column.footer().replaceChildren(input);

                // Event listener para la búsqueda
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
    var fechaInicio = document.getElementById('fecha_inicio').value;
    var fechaFinal = document.getElementById('fecha_final').value;

    if (!fechaInicio || !fechaFinal) {
        document.getElementById('fecha_inicio').style.borderColor = 'red';
        document.getElementById('fecha_final').style.borderColor = 'red';
        return;
    }

    document.getElementById('fecha_inicio').style.borderColor = '';
    document.getElementById('fecha_final').style.borderColor = '';

    var fechaInicioFormat = new Date(fechaInicio).toISOString().split('T')[0];
    var fechaFinalFormat = new Date(fechaFinal).toISOString().split('T')[0];

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfMeta) {
        console.error("No se encontró el token CSRF.");
        return;
    }

    var csrfToken = csrfMeta.getAttribute('content');

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/reporte/facturasanuladasrep/exportar-pdf/2/' + encodeURIComponent(fechaInicioFormat) + '/' + encodeURIComponent(fechaFinalFormat);

    var csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    document.body.appendChild(form);
    form.submit();
}

