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

    var cantidad = function (valor) {
        return Number(valor || 0).toLocaleString('es-HN', { maximumFractionDigits: 2 });
    };

    var mensajeBloqueos = function (previa) {
        var faltantes = (previa.inventario || []).filter(function (lote) {
            return lote.bloqueo;
        });
        var bloqueosInventario = faltantes.map(function (lote) { return lote.bloqueo; });
        var otrosBloqueos = previa.bloqueos.filter(function (bloqueo) {
            return bloqueosInventario.indexOf(bloqueo) === -1;
        });
        var html = '';

        if (faltantes.length) {
            html += '<p class="text-left mb-2">No hay inventario suficiente en la bodega seleccionada para estas devoluciones:</p>' +
                '<div class="table-responsive"><table class="table table-sm table-bordered text-left mb-2">' +
                '<thead class="thead-light"><tr><th>Código</th><th>Producto</th><th>Bodega</th><th>Requiere</th><th>Disponible</th><th>Estado</th></tr></thead><tbody>';
            html += faltantes.map(function (lote) {
                return '<tr><td>' + esc(lote.codigo || lote.producto_id || '') + '</td>' +
                    '<td>' + esc(lote.producto || 'Producto no disponible') + '</td>' +
                    '<td>' + esc(lote.bodega || 'No disponible') + '</td>' +
                    '<td class="text-right">' + cantidad(lote.cantidad) + '</td>' +
                    '<td class="text-right">' + cantidad(lote.disponible) + '</td>' +
                    '<td>Inventario insuficiente</td></tr>';
            }).join('');
            html += '</tbody></table></div>';
        }

        if (otrosBloqueos.length) {
            html += lista(otrosBloqueos.map(esc));
        }

        return html;
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
        if (previa.inventario.length) {
            html += '<p class="mb-1 mt-3"><strong>Inventario por rebajar</strong></p>' +
                '<p class="text-muted small">Seleccione la bodega de donde se descontará cada producto.</p>' +
                '<div class="table-responsive"><table class="table table-sm table-bordered text-left mb-2">' +
                '<thead class="thead-light"><tr><th>Código</th><th>Producto</th><th>Bodega de origen</th><th>Requiere</th><th>Bodega para rebajar</th></tr></thead><tbody>';
            html += previa.inventario.map(function (item) {
                var bodegas = item.bodegas || [];
                var disponible = bodegas.filter(function (bodega) {
                    return Number(bodega.disponible) + 0.0001 >= Number(item.cantidad);
                });
                var seleccion = item.bodega_id && disponible.some(function (bodega) {
                    return Number(bodega.id) === Number(item.bodega_id);
                }) ? Number(item.bodega_id) : (disponible.length ? Number(disponible[0].id) : '');
                var opciones = '<option value="">— Seleccione bodega —</option>' + bodegas.map(function (bodega) {
                    var suficiente = Number(bodega.disponible) + 0.0001 >= Number(item.cantidad);
                    return '<option value="' + Number(bodega.id) + '"' +
                        (Number(bodega.id) === seleccion ? ' selected' : '') +
                        (!suficiente ? ' disabled' : '') + '>' +
                        esc(bodega.nombre) + ' · Disponible: ' + cantidad(bodega.disponible) +
                        (suficiente ? '' : ' · Insuficiente') + '</option>';
                }).join('');
                return '<tr><td>' + esc(item.codigo || item.producto_id || '') + '</td>' +
                    '<td>' + esc(item.producto || 'Producto no disponible') + '</td>' +
                    '<td>' + esc(item.bodega || 'No disponible') + '</td>' +
                    '<td class="text-right">' + cantidad(item.cantidad) + '</td>' +
                    '<td><select class="form-control form-control-sm nc-bodega-inventario" data-producto="' +
                    Number(item.producto_id) + '">' + opciones + '</select></td></tr>';
            }).join('');
            html += '</tbody></table></div>';
        }
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
            width: opciones.width || 720,
            showCancelButton: true,
            confirmButtonText: opciones.confirmar,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33',
            showLoaderOnConfirm: true,
            allowOutsideClick: function () { return !Swal.isLoading(); },
            preConfirm: function (motivo) {
                var datos = typeof opciones.datos === 'function' ? opciones.datos() : opciones.datos;
                if (opciones.validarDatos) {
                    var error = opciones.validarDatos(datos);
                    if (error) {
                        Swal.showValidationMessage(error);
                        return false;
                    }
                }
                return axios.post(opciones.url, Object.assign({}, datos, { motivo: motivo.trim() }))
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
            var inventarioPuedeElegirse = (previa.inventario || []).every(function (item) {
                return (item.bodegas || []).some(function (bodega) {
                    return Number(bodega.disponible) + 0.0001 >= Number(item.cantidad);
                });
            });
            var bloqueosInventario = (previa.inventario || []).map(function (item) { return item.bloqueo; });
            var otrosBloqueos = (previa.bloqueos || []).filter(function (bloqueo) {
                return bloqueosInventario.indexOf(bloqueo) === -1;
            });
            if (otrosBloqueos.length || !inventarioPuedeElegirse) {
                Swal.fire({ icon: 'warning', title: 'No se puede anular', html: mensajeBloqueos(previa), width: 850 });
                return;
            }

            pedirMotivo({
                titulo: 'Anular nota de crédito',
                html: resumenNota(previa),
                width: previa.inventario.length ? 960 : 720,
                confirmar: 'Sí, anular nota',
                url: '/nota/credito/anular',
                datos: function () {
                    var bodegas = {};
                    $('.nc-bodega-inventario').each(function () {
                        bodegas[$(this).data('producto')] = $(this).val();
                    });
                    return { idNotaCredito: idNotaCredito, bodegas: bodegas };
                },
                validarDatos: function (datos) {
                    var incompleto = Object.keys(datos.bodegas).some(function (productoId) {
                        return !datos.bodegas[productoId];
                    });
                    return incompleto ? 'Seleccione una bodega con inventario suficiente para cada producto.' : null;
                }
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
