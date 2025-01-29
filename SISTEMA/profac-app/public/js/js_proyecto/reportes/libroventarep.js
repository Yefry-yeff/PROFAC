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
        pageLength: 15,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp', // Mantén el DOM como estaba
        buttons: [
            { extend: 'excel', title: 'Libro_Ventas ' + fecha, className: 'btn btn-success' },
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
    // Obtener la fecha seleccionada
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

    // Construir la URL para la exportación
    var url = "/reporte/Libroventarep/exportar-pdf/4/" + fechaInput;

    // Abrir la URL en una nueva pestaña para descargar el PDF
    window.open(url, '_blank');
}
