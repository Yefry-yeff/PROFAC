/**
 * Módulo: Boleta de Compra
 * Archivo: crear-boleta-compra.js
 */

var contadorConceptos = 0;
var totalGeneral = 0;
var idBoletaGuardada = 0;
var numBoletaGuardada = '';

// ── Agregar fila de concepto ────────────────────────────────────────────────
function agregarConcepto() {
    contadorConceptos++;

    var fila = '<tr id="fila_concepto_' + contadorConceptos + '">' +
        '<td class="text-center font-weight-bold">' + contadorConceptos + '</td>' +
        '<td>' +
            '<input type="text" class="form-control form-control-sm desc_concepto" ' +
                'id="desc_' + contadorConceptos + '" ' +
                'placeholder="Descripción del concepto" maxlength="500" required>' +
        '</td>' +
        '<td>' +
            '<input type="number" class="form-control form-control-sm precio_concepto" ' +
                'id="precio_' + contadorConceptos + '" ' +
                'placeholder="0.00" step="0.01" min="0" ' +
                'oninput="calcularImporte(' + contadorConceptos + ')">' +
        '</td>' +
        '<td>' +
            '<input type="number" class="form-control form-control-sm cantidad_concepto" ' +
                'id="cantidad_' + contadorConceptos + '" ' +
                'placeholder="0" step="0.01" min="0" ' +
                'oninput="calcularImporte(' + contadorConceptos + ')">' +
        '</td>' +
        '<td>' +
            '<input type="text" class="form-control form-control-sm importe_concepto" ' +
                'id="importe_' + contadorConceptos + '" value="0.00" readonly ' +
                'style="background-color:#f8f9fa; font-weight:bold;">' +
        '</td>' +
        '<td class="text-center">' +
            '<button type="button" class="btn btn-danger btn-sm" ' +
                'onclick="eliminarConcepto(' + contadorConceptos + ')">' +
                '<i class="fa fa-trash"></i>' +
            '</button>' +
        '</td>' +
    '</tr>';

    // Ocultar fila vacía
    $('#fila_vacia').hide();

    $('#cuerpo_conceptos').append(fila);
    $('#desc_' + contadorConceptos).focus();
}

// ── Calcular importe de una fila ─────────────────────────────────────────────
function calcularImporte(indice) {
    var precio   = parseFloat($('#precio_'   + indice).val()) || 0;
    var cantidad = parseFloat($('#cantidad_' + indice).val()) || 0;
    var importe  = precio * cantidad;

    $('#importe_' + indice).val(importe.toFixed(2));

    actualizarTotal();
}

// ── Eliminar fila ────────────────────────────────────────────────────────────
function eliminarConcepto(indice) {
    $('#fila_concepto_' + indice).remove();
    actualizarTotal();

    // Mostrar fila vacía si no quedan conceptos
    if ($('#cuerpo_conceptos tr:visible').length === 0) {
        $('#fila_vacia').show();
        contadorConceptos = 0;
    }

    // Re-numerar visualmente
    renumerarFilas();
}

// ── Re-numerar visualmente la columna # ─────────────────────────────────────
function renumerarFilas() {
    var num = 1;
    $('#cuerpo_conceptos tr:not(#fila_vacia)').each(function() {
        $(this).find('td:first').text(num++);
    });
}

// ── Recalcular total general ──────────────────────────────────────────────────
function actualizarTotal() {
    totalGeneral = 0;
    $('.importe_concepto').each(function() {
        totalGeneral += parseFloat($(this).val()) || 0;
    });

    $('#bc_total_mostrar').val(totalGeneral.toFixed(2));
    $('#bc_total').val(totalGeneral.toFixed(2));
}

// ── Recolectar todos los conceptos ingresados ────────────────────────────────
function recolectarConceptos() {
    var conceptos = [];
    var linea     = 1;
    var valido    = true;

    $('#cuerpo_conceptos tr:not(#fila_vacia)').each(function() {
        var fila     = $(this);
        var idFila   = fila.attr('id').replace('fila_concepto_', '');
        var desc     = $('#desc_'     + idFila).val().trim();
        var precio   = parseFloat($('#precio_'   + idFila).val()) || 0;
        var cantidad = parseFloat($('#cantidad_' + idFila).val()) || 0;
        var importe  = parseFloat($('#importe_'  + idFila).val()) || 0;

        if (!desc) {
            valido = false;
            $('#desc_' + idFila).addClass('is-invalid').focus();
            return false; // break
        }
        if (precio <= 0 || cantidad <= 0) {
            valido = false;
            Swal.fire('Atención', 'El precio y la cantidad deben ser mayores que cero en la línea ' + linea + '.', 'warning');
            return false;
        }

        conceptos.push({
            descripcion: desc,
            precio:      precio,
            cantidad:    cantidad,
            importe:     importe
        });

        linea++;
    });

    if (!valido) return null;
    return conceptos;
}

// ── Guardar boleta de compra ──────────────────────────────────────────────────
function guardarBoletaCompra() {
    var cliente   = $('#bc_cliente').val().trim();
    var direccion = $('#bc_direccion').val().trim();
    var fecha     = $('#bc_fecha').val();
    var total     = parseFloat($('#bc_total').val()) || 0;

    // Validaciones básicas
    if (!cliente) {
        Swal.fire('Atención', 'Ingrese el nombre del cliente.', 'warning');
        $('#bc_cliente').focus();
        return;
    }

    if (!fecha) {
        Swal.fire('Atención', 'Seleccione una fecha.', 'warning');
        $('#bc_fecha').focus();
        return;
    }

    if (total <= 0) {
        Swal.fire('Atención', 'Debe agregar al menos un concepto con importe mayor a cero.', 'warning');
        return;
    }

    var conceptos = recolectarConceptos();
    if (!conceptos) return;

    if (conceptos.length === 0) {
        Swal.fire('Atención', 'Debe agregar al menos un concepto de compra.', 'warning');
        return;
    }

    // Confirmar antes de guardar
    Swal.fire({
        title: '¿Guardar boleta?',
        text: 'Se registrará la boleta de compra para: ' + cliente,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (!result.isConfirmed) return;

        $('#btn_guardar_boleta').prop('disabled', true).html(
            '<i class="fa fa-spinner fa-spin"></i> Guardando...'
        );

        axios.post('/boleta/compra/guardar', {
            cliente:    cliente,
            direccion:  direccion,
            fecha:      fecha,
            conceptos:  JSON.stringify(conceptos),
            total:      total,
            rtn_dni:    $('#bc_rtn_dni').val().trim(),
            telefono:   $('#bc_telefono').val().trim(),
            comentario: $('#bc_comentario').val().trim(),
            _token:     $('meta[name="csrf-token"]').attr('content')
        }).then(function(response) {
            var data = response.data;

            if (data.status === 'success') {
                idBoletaGuardada  = data.id;
                numBoletaGuardada = data.numero_boleta;

                $('#modal_imprimir_boleta').modal('show');
            } else {
                Swal.fire('Error', data.message || 'Error al guardar.', 'error');
            }
        }).catch(function(error) {
            var msg = 'Error al guardar la boleta de compra.';
            if (error.response && error.response.data && error.response.data.message) {
                msg = error.response.data.message;
            }
            Swal.fire('Error', msg, 'error');
        }).finally(function() {
            $('#btn_guardar_boleta').prop('disabled', false).html(
                '<i class="fa fa-save"></i> Guardar Boleta de Compra'
            );
        });
    });
}

// ── Imprimir boleta (original o copia) ───────────────────────────────────────
function imprimirBoleta(tipo) {
    if (!idBoletaGuardada) {
        Swal.fire('Error', 'No hay boleta guardada para imprimir.', 'error');
        return;
    }

    var url = tipo === 'copia'
        ? '/boleta/compra/imprimir/copia/' + idBoletaGuardada
        : '/boleta/compra/imprimir/'       + idBoletaGuardada;

    window.open(url, '_blank');
}

// ── Finalizar y limpiar formulario ───────────────────────────────────────────
function finalizarYContinuar() {
    $('#modal_imprimir_boleta').modal('hide');
    limpiarFormulario();
}

function limpiarFormulario() {
    $('#bc_cliente').val('');
    $('#bc_direccion').val('');
    $('#bc_fecha').val(new Date().toISOString().split('T')[0]);
    $('#cuerpo_conceptos').empty().append(
        '<tr id="fila_vacia">' +
            '<td colspan="6" class="text-center text-muted">' +
                '<i class="fa fa-info-circle"></i> Haga clic en "Agregar Concepto" para comenzar.' +
            '</td>' +
        '</tr>'
    );
    contadorConceptos = 0;
    totalGeneral      = 0;
    idBoletaGuardada  = 0;
    numBoletaGuardada = '';
    $('#bc_total_mostrar').val('0.00');
    $('#bc_total').val('0');
}
