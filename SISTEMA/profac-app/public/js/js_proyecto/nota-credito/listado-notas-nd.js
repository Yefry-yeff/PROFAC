



function ajustesPorfecha(){
    let inicio = document.getElementById('fechaInicio').value;
    let final = document.getElementById('fechaFinal').value;

    console.log("entro")

    fechaInicio = inicio;
    fechaFinal = final;

    $('#tbl_listar_ajustes').DataTable().clear().destroy();
   // $('#tbl_listar_ventas_dos').DataTable().clear().destroy();

    this.tablas();

    //$('#tbl_listar_ventas_uno').DataTable().ajax.reload();
    //$('#tbl_listar_ventas_dos').DataTable().ajax.reload();
}


function anularNota(idNotaCredito, idFactura){
    axios.post('/nota/credito/anular', {
                        idFactura: idFactura,
                        idNotaCredito: idNotaCredito
                    })
                    .then(response => {

                        let data = response.data;

                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            html: data.text,

                        })

                        location.reload()


                        return;


                    })
                    .catch(err => {
                        let data = err.response && err.response.data ? err.response.data : {};
                        Swal.fire({
                            icon: data.icon || "error",
                            title: data.title || "Error",
                            html: data.text || "Ha ocurrido un error al anular nota de crédito.",
                        })

                    })
}

function verAsientosNota(idNotaCredito) {
    axios.get('/nota/credito/asientos/' + idNotaCredito).then(function(response) {
        var asientos = response.data.asientos || [];
        if (!asientos.length) {
            Swal.fire({ icon: 'info', title: 'Sin ajuste contable', text: 'Esta nota no tiene asientos contables registrados.' });
            return;
        }

        var moneda = function(valor) {
            return Number(valor || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };
        var html = asientos.map(function(asiento) {
            var filas = asiento.detalles.map(function(detalle) {
                return '<tr><td class="text-left"><small>' + detalle.codigo + '</small><br>' + detalle.cuenta + '</td>' +
                    '<td class="text-right">L ' + moneda(detalle.debe) + '</td>' +
                    '<td class="text-right">L ' + moneda(detalle.haber) + '</td></tr>';
            }).join('');
            return '<div class="text-left mb-3"><strong>' + asiento.descripcion + '</strong>' +
                '<div class="text-muted small">' + asiento.fecha + ' · ' + (asiento.usuario || 'Sistema') + '</div></div>' +
                '<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>Cuenta</th><th>Debe</th><th>Haber</th></tr></thead><tbody>' + filas + '</tbody></table></div>';
        }).join('<hr>');

        Swal.fire({ title: 'Ajustes contables de la nota', html: html, width: 850, confirmButtonText: 'Cerrar' });
    });
}

function gestionarCreditoNota(idNotaCredito, credito, deuda) {
    axios.get('/listar/aplicacion/bancos').then(function(response) {
        var bancos = '<option value="">— Seleccione —</option>' + (response.data.result || []).map(function(b) {
            return '<option value="' + b.idBanco + '">' + b.banco + '</option>';
        }).join('');
        var moneda = function(v) { return 'L ' + Number(v || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}); };
        Swal.fire({
            title: 'Gestionar crédito disponible',
            html: '<div class="text-left"><p>Crédito: <strong>' + moneda(credito) + '</strong> · Saldos: <strong>' + moneda(deuda) + '</strong></p>' +
                '<label>Destino</label><select id="gc_destino" class="form-control mb-3"><option value="">— Seleccione —</option><option value="saldos">Aplicar a saldos</option><option value="reembolso">Reembolsar</option><option value="mixto">Mixto automático</option></select>' +
                '<div id="gc_resumen" class="alert alert-info" style="display:none"></div>' +
                '<div id="gc_reembolso" style="display:none"><label>Cuenta de salida</label><select id="gc_banco" class="form-control mb-2">' + bancos + '</select>' +
                '<label>Método</label><select id="gc_metodo" class="form-control mb-2"><option value="">— Seleccione —</option><option value="1">Efectivo</option><option value="2">Transferencia bancaria</option><option value="3">Cheque</option><option value="4">Link de pago</option><option value="5">POS</option></select>' +
                '<label>Fecha</label><input id="gc_fecha" type="date" class="form-control" value="' + new Date().toISOString().slice(0,10) + '"></div></div>',
            showCancelButton: true, confirmButtonText: 'Gestionar', cancelButtonText: 'Cancelar',
            didOpen: function() {
                $('#gc_destino').on('change', function() {
                    var destino = this.value;
                    var aplicado = destino === 'reembolso' ? 0 : Math.min(Number(credito), Number(deuda));
                    var reembolso = destino === 'reembolso' ? Number(credito) : (destino === 'mixto' ? Math.max(Number(credito) - aplicado, 0) : 0);
                    $('#gc_resumen').html('Aplicado: <strong>' + moneda(aplicado) + '</strong> · Reembolsado: <strong>' + moneda(reembolso) + '</strong>').toggle(!!destino);
                    $('#gc_reembolso').toggle(reembolso > 0.005);
                });
            },
            preConfirm: function() {
                var destino = $('#gc_destino').val();
                var aplicado = destino === 'reembolso' ? 0 : Math.min(Number(credito), Number(deuda));
                var reembolso = destino === 'reembolso' ? Number(credito) : (destino === 'mixto' ? Math.max(Number(credito) - aplicado, 0) : 0);
                if (!destino) return Swal.showValidationMessage('Seleccione el destino.');
                if (reembolso > 0.005 && (!$('#gc_banco').val() || !$('#gc_metodo').val() || !$('#gc_fecha').val())) return Swal.showValidationMessage('Seleccione cuenta, método y fecha del reembolso.');
                return { destino:destino, banco:$('#gc_banco').val(), metodo:$('#gc_metodo').val(), fecha:$('#gc_fecha').val() };
            }
        }).then(function(confirmacion) {
            if (!confirmacion.isConfirmed) return;
            var datos = new FormData();
            datos.append('selectNotaCredito', idNotaCredito); datos.append('destinoCredito', confirmacion.value.destino);
            if (confirmacion.value.banco) datos.append('bancoReembolso', confirmacion.value.banco);
            if (confirmacion.value.metodo) datos.append('metodoReembolso', confirmacion.value.metodo);
            if (confirmacion.value.fecha) datos.append('fechaReembolso', confirmacion.value.fecha);
            return axios.post('/pagos/notacredito/guardar', datos).then(function(respuesta) {
                var r = respuesta.data.resultado;
                var facturas = (r.aplicaciones || []).map(function(aplicacion) {
                    return '<li><strong>' + aplicacion.factura + '</strong>: ' + moneda(aplicacion.monto) + '</li>';
                }).join('');
                Swal.fire({
                    icon: 'success',
                    title: 'Nota gestionada',
                    html: '<div class="text-left">Aplicado: <strong>' + moneda(r.monto_aplicado) + '</strong>' +
                        (facturas ? '<p class="mb-1 mt-2"><strong>Facturas aplicadas:</strong></p><ul>' + facturas + '</ul>' : '') +
                        '<div>Reembolsado: <strong>' + moneda(r.monto_reembolsado) + '</strong></div></div>'
                });
                if (typeof ndTable !== 'undefined' && ndTable) ndTable.ajax.reload(null, false);
            });
        });
    });
}
