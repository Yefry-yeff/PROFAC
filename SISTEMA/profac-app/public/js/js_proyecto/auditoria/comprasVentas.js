(function () {
    function filtros() {
        return {
            desde: $('#acvDesde').val(),
            hasta: $('#acvHasta').val(),
            tipo: $('#acvTipo').val(),
            clasificacion: $('#acvClasificacion').val(),
            estado: $('#acvEstado').val(),
            producto: $('#acvProducto').val().trim(),
            documento: $('#acvDocumento').val().trim(),
            lote: $('#acvLote').val().trim()
        };
    }

    function cargarResumen() {
        $.get('/auditoria/compras-ventas/resumen', filtros()).done(function (data) {
            $('#acvKpiCompras').text(Number(data.compras || 0).toLocaleString());
            $('#acvKpiVentas').text(Number(data.ventas || 0).toLocaleString());
            $('#acvKpiComprobantes').text(Number(data.comprobantes || 0).toLocaleString());
            $('#acvKpiAjustes').text(Number(data.ajustes || 0).toLocaleString());
            $('#acvKpiFilas').text(Number(data.filas || 0).toLocaleString());
            $('#acvKpiEntradas').text(Number(data.entradas || 0).toLocaleString());
            $('#acvKpiSalidas').text(Number(data.salidas_activas || 0).toLocaleString());
            $('#acvKpiAnulados').text(Number(data.anulados || 0).toLocaleString());
        });
    }

    $(function () {
        var tabla = $('#tbl_auditoria_cv').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            ordering: false,
            scrollX: true,
            language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
            ajax: { url: '/auditoria/compras-ventas/listar', data: function (data) { $.extend(data, filtros()); } },
            columns: [
                { data: 'DT_RowIndex', searchable: false },
                { data: 'tipo_movimiento' }, { data: 'fecha_movimiento' }, { data: 'fecha_ingreso_compra' },
                { data: 'fecha_creacion_factura' }, { data: 'fecha_emision_factura' }, { data: 'factura_id' },
                { data: 'compra_id' }, { data: 'comprobante_id' }, { data: 'ajuste_id' },
                { data: 'numero_documento' }, { data: 'cai' },
                { data: 'clasificacion_factura' }, { data: 'estado_documento' },
                { data: 'motivo_documento' }, { data: 'comentario_documento' }, { data: 'producto_id' },
                { data: 'producto_nombre' }, { data: 'lote_id' }, { data: 'bodega_nombre' },
                { data: 'seccion_nombre' }, { data: 'unidad_compra_nombre' }, { data: 'factor_unidad_compra' },
                { data: 'unidad_venta_nombre' }, { data: 'factor_unidad_venta' }, { data: 'cantidad_documento' },
                { data: 'cantidad_base' }, { data: 'cantidad_movimiento_base' },
                { data: 'afecta_inventario', render: function (data) { return Number(data) === 1 ? 'SI' : 'NO'; } },
                { data: 'cliente_nombre' },
                { data: 'proveedor_nombre' }, { data: 'usuario_nombre' }
            ]
        });

        cargarResumen();

        $('#btnAcvBuscar').on('click', function () {
            tabla.ajax.reload();
            cargarResumen();
        });

        $('#btnAcvExportar').on('click', function () {
            window.location = '/auditoria/compras-ventas/exportar?' + $.param(filtros());
        });

        $('#btnAcvExportarExcel').on('click', function () {
            window.location = '/auditoria/compras-ventas/exportar-excel';
        });

        $('#btnAcvExportarExcelProductosA').on('click', function () {
            window.location = '/auditoria/compras-ventas/exportar-excel-productos/A';
        });

        $('#btnAcvExportarExcelProductosB').on('click', function () {
            window.location = '/auditoria/compras-ventas/exportar-excel-productos/B';
        });

        $('#btnAcvSincronizar').on('click', function () {
            var boton = $(this).prop('disabled', true);
            $.post('/auditoria/compras-ventas/sincronizar', { _token: document.querySelector('meta[name="csrf-token"]').content })
                .done(function (response) {
                    toastr.success(response.message);
                    tabla.ajax.reload();
                    cargarResumen();
                })
                .fail(function (xhr) {
                    toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'No se pudo sincronizar la auditoría.');
                })
                .always(function () { boton.prop('disabled', false); });
        });
    });
})();
