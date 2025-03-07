function carga_libro_venta() {
    $("#tbl_libro_venta").dataTable().fnDestroy();

    var fechaInput = document.getElementById('fecha_venta').value;

    // Validar si el campo de fecha está vacío
    if (!fechaInput) {
        document.getElementById('fecha_venta_error').style.display = 'block';
        document.getElementById('fecha_venta').style.borderColor = 'red';
        return;
    }

    // Restaurar estilos si la fecha es válida
    document.getElementById('fecha_venta').style.borderColor = '';
    document.getElementById('fecha_venta_error').style.display = 'none';

    // Formato de fecha (YYYY-MM-DD)
    var fecha = new Date(fechaInput).toISOString().split('T')[0];

    $('#tbl_libro_venta').DataTable({
        order: ['0', 'desc'],
        paging: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        pageLength: 8,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp', // Mantén el DOM como estaba
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'Libro_Ventas',
                text: '<i class="fa-solid fa-file-excel"></i> Exportar a Excel',
                className: 'btn-excel'
            },
            {
                extend: 'pdfHtml5',
                title: 'Libro_ventas',
                text: '<i class="fa-solid fa-file-pdf"></i> Exportar a PDF',
                className: 'btn-pdf',
                action: function () {
                    exportarPdf();
            }
        }
        ],
        ajax: "/reporte/Libroventarep/consulta/4/" + fecha,
        columns: [
            { data: 'VENDEDOR' },
            { data: 'CLIENTE' },
            { data: 'FACTURA' },
            { data: 'EXONERADO' },
            { data: 'GRAVADO' },
            { data: 'EXCENTO' },
            { data: 'SUBTOTAL' },
            { data: 'ISV' },
            { data: 'TOTAL' },
            { data: 'FECHA COMPRA' }
        ]
    });
}

function exportarPdf() {
    var fechaInput = document.getElementById('fecha_venta').value;

    if (!fechaInput) {
        document.getElementById('fecha_venta_error').style.display = 'block';
        document.getElementById('fecha_venta').style.borderColor = 'red';
        return;
    }

    document.getElementById('fecha_venta').style.borderColor = '';
    document.getElementById('fecha_venta_error').style.display = 'none';

    // Configurar el formulario de envío POST
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/reporte/Libroventarep/exportar-pdf/4/' + fechaInput;

    // Agregar token CSRF
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    // Enviar el formulario
    document.body.appendChild(form);
    form.submit();
}
