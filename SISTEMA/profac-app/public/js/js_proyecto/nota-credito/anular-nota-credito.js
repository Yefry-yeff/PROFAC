(function (window, $) {
    'use strict';

    var moneda = function (valor) {
        return 'L ' + Number(valor || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    var esc = function (texto) {
        return $('<div>').text(texto === null || texto === undefined ? '' : String(texto)).html();
    };

    var lista = function (items) {
        return '<ul class="text-left mb-2 pl-4">' + items.map(function (item) {
            return '<li>' + item + '</li>';
        }).join('') + '</ul>';
    };

    var errorDe = function (err, porDefecto) {
        var datos = err && err.response && err.response.data ? err.response.data : {};
        return datos.text || porDefecto;
    };

    var recargar = function () {
        window.location.reload();
    };

    var describirAplicacion = function (aplicacion) {
        var partes = ['<strong>' + esc(aplicacion.factura || 'Factura') + '</strong>: ' + moneda(aplicacion.monto)];
        if (!aplicacion.vigente) {
            partes.push('<span class="text-muted">' + esc(aplicacion.motivo_omision) + '</span>');
            return partes.join(' · ');
        }

        partes.push('saldo de la factura quedará en ' + moneda(aplicacion.saldo_posterior));
        if (aplicacion.reabre_factura) {
            partes.push('<span class="text-danger">la factura cerrada se reabre</span>');
        }
        if (aplicacion.comisiones > 0) {
            partes.push('se revierten ' + aplicacion.comisiones + ' comisión(es) por ' + moneda(aplicacion.comisiones_monto));
        }

        return partes.join(' · ');
    };

    var bloque = function (titulo, items) {
        return items.length ? '<p class="mb-1 mt-3"><strong>' + titulo + '</strong></p>' + lista(items) : '';
    };

    var resumenNota = function (previa) {
        var html = '<p>Se anulará la nota <strong>' + esc(previa.nota.cai) + '</strong> por <strong>' + moneda(previa.nota.total) + '</strong> y se revertirá lo siguiente:</p>';
        html += bloque('Aplicaciones a facturas', previa.aplicaciones.map(describirAplicacion));
        html += bloque('Reembolsos (reverso contable)', previa.reembolsos.map(function (reembolso) {
            return moneda(reembolso.monto) + (reembolso.cuenta ? ' · cuenta ' + esc(reembolso.cuenta) : '');
        }));
        html += bloque('Comisiones', previa.ajustes_comision.cantidad > 0
            ? ['Se devuelven ' + moneda(previa.ajustes_comision.monto) + ' rebajados a empleados (' + previa.ajustes_comision.cantidad + ' ajuste(s))']
            : []);
        html += bloque('Inventario', previa.inventario.map(function (lote) {
            return esc(lote.producto || 'Lote ' + lote.lote) + ': se descuentan ' + lote.cantidad + ' unidad(es) del inventario devuelto';
        }));
        html += '<p class="mt-3 mb-1">Se generan los asientos de reverso de <strong>' + previa.asientos + '</strong> ajuste(s) contable(s).</p>';

        if (previa.advertencias.length) {
            html += '<div class="alert alert-warning mt-3 mb-0">' + lista(previa.advertencias.map(esc)) + '</div>';
        }

        return html;
    };

    var pedirMotivo = function (opciones) {
        return Swal.fire({
            title: opciones.titulo,
            html: '<div class="text-left">' + opciones.html + '</div>',
            input: 'textarea',
            inputLabel: 'Motivo de la anulación',
            inputAttributes: { maxlength: 1000 },
            inputValidator: function (valor) {
                if (!valor || valor.trim().length < 5) {
                    return 'Ingrese un motivo de al menos 5 caracteres.';
                }
            },
            width: 720,
            showCancelButton: true,
            confirmButtonText: opciones.confirmar,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33',
            showLoaderOnConfirm: true,
            allowOutsideClick: function () { return !Swal.isLoading(); },
            preConfirm: function (motivo) {
                return axios.post(opciones.url, Object.assign({}, opciones.datos, { motivo: motivo.trim() }))
                    .then(function (respuesta) { return respuesta.data; })
                    .catch(function (err) {
                        Swal.showValidationMessage(errorDe(err, 'No se pudo completar la anulación.'));
                    });
            }
        });
    };

    window.anularNota = function (idNotaCredito) {
        axios.get('/nota/credito/anular/previsualizar/' + idNotaCredito).then(function (respuesta) {
            var previa = respuesta.data;
            if (!previa.puede) {
                Swal.fire({ icon: 'warning', title: 'No se puede anular', html: lista(previa.bloqueos.map(esc)) });
                return;
            }

            pedirMotivo({
                titulo: 'Anular nota de crédito',
                html: resumenNota(previa),
                confirmar: 'Sí, anular nota',
                url: '/nota/credito/anular',
                datos: { idNotaCredito: idNotaCredito }
            }).then(function (resultado) {
                if (resultado.isConfirmed && resultado.value) {
                    Swal.fire({ icon: 'success', title: resultado.value.title, html: resultado.value.text }).then(recargar);
                }
            });
        }).catch(function (err) {
            Swal.fire({ icon: 'error', title: 'Error', html: errorDe(err, 'No se pudo consultar el efecto de la anulación.') });
        });
    };

    window.revertirAplicacionNota = function (idMovimiento, idNotaCredito) {
        axios.get('/nota/credito/aplicacion/' + idMovimiento + '/previsualizar-reversion').then(function (respuesta) {
            var previa = respuesta.data;
            if (!previa.puede) {
                Swal.fire({ icon: 'warning', title: 'No se puede anular', html: lista(previa.bloqueos.map(esc)) });
                return;
            }

            var html = '<p>Se anulará esta aplicación de la nota <strong>' + esc(previa.nota.cai) + '</strong>:</p>' +
                lista(previa.aplicaciones.map(describirAplicacion)) +
                '<p class="mb-0">La nota seguirá vigente y su crédito disponible quedará en <strong>' + moneda(previa.saldo_resultante) + '</strong>.</p>';

            pedirMotivo({
                titulo: 'Anular aplicación de nota de crédito',
                html: html,
                confirmar: 'Sí, anular aplicación',
                url: '/nota/credito/aplicacion/revertir',
                datos: { idMovimiento: idMovimiento }
            }).then(function (resultado) {
                if (resultado.isConfirmed && resultado.value) {
                    Swal.fire({ icon: 'success', title: resultado.value.title, html: esc(resultado.value.text) }).then(recargar);
                } else {
                    window.verAsientosNota(idNotaCredito);
                }
            });
        }).catch(function (err) {
            Swal.fire({ icon: 'error', title: 'Error', html: errorDe(err, 'No se pudo consultar el efecto de la anulación.') });
        });
    };

    window.verAsientosNota = function (idNotaCredito) {
        axios.get('/nota/credito/asientos/' + idNotaCredito).then(function (respuesta) {
            var asientos = respuesta.data.asientos || [];
            if (!asientos.length) {
                Swal.fire({ icon: 'info', title: 'Sin ajuste contable', text: 'Esta nota no tiene asientos contables registrados.' });
                return;
            }

            var html = asientos.map(function (asiento) {
                var etiqueta = '';
                if (String(asiento.tipo).indexOf('anulacion') === 0) {
                    etiqueta = ' <span class="badge badge-danger">Anulación</span>';
                } else if (asiento.anulado) {
                    etiqueta = ' <span class="badge badge-secondary">Anulado</span>';
                }

                var boton = asiento.anulable
                    ? '<button type="button" class="btn btn-sm btn-outline-danger float-right" onclick="revertirAplicacionNota(' +
                        Number(asiento.movimiento_id) + ',' + Number(idNotaCredito) + ')"><i class="fa fa-undo"></i> Anular aplicación</button>'
                    : '';

                var filas = asiento.detalles.map(function (detalle) {
                    return '<tr><td class="text-left"><small>' + esc(detalle.codigo) + '</small><br>' + esc(detalle.cuenta) + '</td>' +
                        '<td class="text-right">' + moneda(detalle.debe) + '</td>' +
                        '<td class="text-right">' + moneda(detalle.haber) + '</td></tr>';
                }).join('');

                return '<div class="text-left mb-3">' + boton + '<strong>' + esc(asiento.descripcion) + '</strong>' + etiqueta +
                    '<div class="text-muted small">' + esc(asiento.fecha) + ' · ' + esc(asiento.usuario || 'Sistema') + '</div></div>' +
                    '<div class="table-responsive"><table class="table table-sm table-bordered' + (asiento.anulado ? ' text-muted' : '') + '"><thead><tr><th>Cuenta</th><th>Debe</th><th>Haber</th></tr></thead><tbody>' + filas + '</tbody></table></div>';
            }).join('<hr>');

            Swal.fire({ title: 'Ajustes contables de la nota', html: html, width: 850, confirmButtonText: 'Cerrar' });
        });
    };
})(window, window.jQuery);
