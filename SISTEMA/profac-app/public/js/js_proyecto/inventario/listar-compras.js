
$(document).ready(function() {
    var defaults = { desde: $('#comprasDesde').val(), hasta: $('#comprasHasta').val(), factura: '', proveedor: '' };
    var filtros = Object.assign({}, defaults);
    var modal = $('#modalFiltrosCompras');
    var pending = false;
    $('#comprasProveedor').select2({ dropdownParent: modal, placeholder: 'Todos los proveedores', allowClear: true, width: '100%' });

    function updateSummary() {
        var summary = 'Emisión: ' + filtros.desde + ' al ' + filtros.hasta;
        if (filtros.factura) summary += ' · Factura: ' + filtros.factura;
        if (filtros.proveedor) summary += ' · Proveedor: ' + $('#comprasProveedor option:selected').text();
        $('#resumenFiltrosCompras').text(summary);
        $('#exportarComprasExcel').attr('href', '/compras/excel_mes/' + filtros.desde.slice(0, 7) + '?' + $.param(filtros));
    }
    updateSummary();
    var table = $('#tbl_listar_compras').DataTable({
        "order": [0, 'desc'],
        "language": {
            "url": "/js/plugins/dataTables/i18n/Spanish.json"
        },

        pageLength: 10,
        responsive: true,
        dom: 'lrtip',


        "ajax": function (data, callback) {
            axios.get('/producto/compras/listar', { params: filtros }).then(function (response) {
                $('#errorReporteCompras, #errorFiltrosCompras').addClass('d-none').text('');
                $('#exportarComprasExcel').removeClass('disabled').removeAttr('aria-disabled');
                callback(response.data);
                updateSummary();
                if (pending) modal.modal('hide');
            }).catch(function (error) {
                var response = error.response && error.response.data;
                var message = response && response.errors
                    ? Object.values(response.errors).map(function (messages) { return messages.join(' '); }).join(' ')
                    : 'No se pudo cargar el reporte de compras. Intente de nuevo.';
                $('#errorReporteCompras, #errorFiltrosCompras').removeClass('d-none').text(message);
                callback({ data: [] });
                $('#exportarComprasExcel').addClass('disabled').attr('aria-disabled', 'true');
            }).finally(function () {
                pending = false;
                $('#filtrosComprasForm button').prop('disabled', false);
            });
        },
        "columns": [{
                data: 'id'
            },
            {
                data: 'numero_orden'
            },
            {
                data: 'numero_factura'
            },
            {
                data: 'fecha_emision'
            },
            {
                data: 'fecha_vencimiento'
            },
            {
                data: 'total'
            },
            {
                data: 'estado_retencion'
            },
            {
                data: 'nombre'
            },
            {
                data: 'usuario'
            },
            {
                data: 'fecha_registro'
            },
            {
                data: 'anular'
            },
            {
                data: 'opciones'
            }

        ]


    });
    $('#filtrosComprasForm').on('submit', function (event) {
        event.preventDefault();
        if (pending) return;
        if ($('#comprasHasta').val() < $('#comprasDesde').val()) {
            $('#errorFiltrosCompras').removeClass('d-none').text('La fecha hasta no puede ser anterior a la fecha desde.');
            return;
        }
        filtros = {
            desde: $('#comprasDesde').val(), hasta: $('#comprasHasta').val(),
            factura: $('#comprasFactura').val().trim(), proveedor: $('#comprasProveedor').val() || ''
        };
        pending = true;
        $('#filtrosComprasForm button').prop('disabled', true);
        table.ajax.reload();
    });
    $('#limpiarFiltrosCompras').on('click', function () {
        $('#comprasDesde').val(defaults.desde);
        $('#comprasHasta').val(defaults.hasta);
        $('#comprasFactura').val('');
        $('#comprasProveedor').val('').trigger('change');
        $('#filtrosComprasForm').trigger('submit');
    });
    })

function anularCompra(idCompra){

    Swal.fire({
    title: '¿Está seguro de anular esta compra?',
    text:'Una vez que ha sido anulada la compra no se podrá recibir el producto en bodega.',
    showDenyButton: false,
    showCancelButton: true,
    confirmButtonText: 'Si, Anular Compra',
    cancelButtonText: `Cancelar`,
    }).then((result) => {
    /* Read more about isConfirmed, isDenied below */
    if (result.isConfirmed) {

        //Swal.fire('Saved!', '', 'success')
        this.anularCompraGuardar(idCompra);

    }
    })
}

function anularCompraGuardar(idCompra){

    axios.post("/producto/compra/anular", {idCompra:idCompra})
    .then( response =>{


        let data = response.data;
        Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text,
                });
                $('#tbl_listar_compras').DataTable().ajax.reload();

    })
    .catch( err => {

        Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Ha ocurrido un error al anular la compra.',
                })

    })

}
