
function _fdSetCookie(name, value, seconds) {
    var expires = '';
    if (typeof seconds === 'number') {
        var d = new Date(); d.setTime(d.getTime() + seconds * 1000);
        expires = '; expires=' + d.toUTCString();
    }
    document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/';
}
function _fdGetCookie(name) {
    var prefix = name + '=';
    var parts = document.cookie ? document.cookie.split(';') : [];
    for (var i = 0; i < parts.length; i++) {
        var c = parts[i].trim();
        if (c.indexOf(prefix) === 0) return decodeURIComponent(c.substring(prefix.length));
    }
    return '';
}

function exportarFacturaDia() {
    var fechaInicio = document.getElementById('fecha_inicio').value;
    var fechaFinal  = document.getElementById('fecha_final').value;
    if (!fechaInicio || !fechaFinal) {
        Swal.fire({ icon: 'warning', title: 'Fechas requeridas', text: 'Selecciona el rango de fechas antes de exportar.' });
        return;
    }
    var tok          = $('meta[name="csrf-token"]').attr('content');
    var downloadToken = 'fdxls_' + Date.now() + '_' + Math.floor(Math.random() * 1000000);
    var cookieName   = 'fd_excel_download_token';
    _fdSetCookie(cookieName, '', -1);

    var url  = '/reporte/factura-dia/exportar-excel/' + encodeURIComponent(fechaInicio) + '/' + encodeURIComponent(fechaFinal);
    var form = $('<form method="POST"></form>').attr('action', url);
    form.append($('<input type="hidden">').attr('name', '_token').val(tok));
    form.append($('<input type="hidden">').attr('name', 'download_token').val(downloadToken));
    $('body').append(form);

    Swal.fire({
        title: 'Generando Excel',
        html: 'Preparando reporte...<br><small>Esto puede tardar unos momentos.</small>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: function() { Swal.showLoading(); }
    });

    var startedAt = Date.now();
    var timer = setInterval(function() {
        if (_fdGetCookie(cookieName) === downloadToken) {
            clearInterval(timer);
            _fdSetCookie(cookieName, '', -1);
            Swal.close();
        } else if (Date.now() - startedAt > 5 * 60 * 1000) {
            clearInterval(timer);
            Swal.fire({ icon: 'warning', title: 'Demora en descarga', text: 'La generación sigue en proceso. Intenta nuevamente.' });
        }
    }, 400);

    form.trigger('submit');
    setTimeout(function() { form.remove(); }, 1500);
}

function cargaConsulta(){

    $("#tbl_facdia").dataTable().fnDestroy();

    var fecha_inicio = document.getElementById('fecha_inicio').value;
    var fecha_final = document.getElementById('fecha_final').value;

    $('#tbl_facdia').DataTable({
        "order": [[0, 'asc']],
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
        dom: 'lTfgitp',
        "ajax": "/consulta/"+fecha_inicio+"/"+fecha_final,
        "columns": [
            { data: 'fecha' },
            { data: 'mes' },
            { data: 'factura' },
            { data: 'cliente' },
            { data: 'vendedor' },
            { data: 'facturador' },
            { data: 'gestor_entrega' },
            { data: 'subtotal' },
            { data: 'imp_venta' },
            { data: 'total' },
            { data: 'tipo' },
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
