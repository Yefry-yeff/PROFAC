function cargaCierreDiario() {
    $("#tbl_cierre_diario").dataTable().fnDestroy();

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

    var fechaInicio = new Date(fechaInicioInput).toISOString().split('T')[0]; // Convertir fecha de inicio a formato ISO (YYYY-MM-DD)
    var fechaFinal = new Date(fechaFinalInput).toISOString().split('T')[0]; // Convertir fecha final a formato ISO (YYYY-MM-DD)

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
            {
                extend: 'excelHtml5',
                title: 'Cierre_Diario',
                text: '<i class="fa-solid fa-file-excel"></i> Exportar a Excel',
                className: 'btn-excel',
                action: function(){
                    exportarExcel(fechaInicio, fechaFinal)
                }
            },
            {
                extend: 'pdfHtml5',
                title: 'Cierre_Diario',
                text: '<i class="fa-solid fa-file-pdf"></i> Exportar a PDF',
                className: 'btn-pdf',
                action: function () {
                    exportarPdf(fechaInicio, fechaFinal);
            }
        }
        ],
        "ajax":  "/reporte/Cierrediariorep/consulta/"+1+"/"+fechaInicio+"/"+fechaFinal,
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

    // Configurar el formulario de envío POST
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/reporte/Cierrediariorep/exportar-pdf/1/' + encodeURIComponent(fechaInicioFormat) + '/' + encodeURIComponent(fechaFinalFormat);

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
function exportarExcel() {
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
    form.action = '/reporte/Cierrediariorep/exportar-excel/1/' + encodeURIComponent(fechaInicioFormat) + '/' + encodeURIComponent(fechaFinalFormat);

    var csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    document.body.appendChild(form);
    form.submit();
}
