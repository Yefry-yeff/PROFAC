function carga_libro_cobros() {
    $("#tbl_libro_cobros").dataTable().fnDestroy();

    var fechaInput = document.getElementById('fecha_cobro').value;

    // Validar si el campo de fecha está vacío
    if (!fechaInput) {
        document.getElementById('fecha_cobro_error').style.display = 'block';
        document.getElementById('fecha_cobro').style.borderColor = 'red';
        return;
    }

    // Restaurar estilos si la fecha es válida
    document.getElementById('fecha_cobro').style.borderColor = '';
    document.getElementById('fecha_cobro_error').style.display = 'none';

    // Formato de fecha (YYYY-MM-DD)
    var fecha = new Date(fechaInput).toISOString().split('T')[0];

    $('#tbl_libro_cobros').DataTable({
        order: ['0', 'desc'],
        paging: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        pageLength: 8,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp', // Mantén el DOM como estaba
        buttons: [
            { extend: 'excel', title: 'Libro_Cobros ' + fecha, className: 'btn btn-success' },
        ],
        ajax: "/reporte/Librocobrosrep/consulta/3/" + fecha,
        columns: [
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
        ]
    });
}

function exportarPdf() {
    // Obtener la fecha seleccionada
    var fechaInput = document.getElementById('fecha_cobro').value;

    // Validar si el campo de fecha está vacío
    if (!fechaInput) {
        document.getElementById('fecha_cobro_error').style.display = 'block';
        document.getElementById('fecha_cobro').style.borderColor = 'red';
        return;
    }

    // Restaurar estilos si la fecha es válida
    document.getElementById('fecha_cobro').style.borderColor = '';
    document.getElementById('fecha_cobro_error').style.display = 'none';

    // Construir la URL para la exportación
    var url = "/reporte/Librocobrosrep/exportar-pdf/3/" + fechaInput;

    // Abrir la URL en una nueva pestaña para descargar el PDF
    window.open(url, '_blank');
}
