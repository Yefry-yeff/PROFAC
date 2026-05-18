
$('#btnEC').addClass('d-none');

$('#tbl_principal_div').addClass('d-none');
$('#tbl_movimientos_div').addClass('d-none');
$('#tbl_creditos_abonos_div').addClass('d-none');

// Almacena el FormData del formulario de abonos antes de que el modal
// lo limpie (hidden.bs.modal destruye selectBanco), para enviarlo al confirmar.
var _pendingAbonoData = null;

// Fix: prevent aria-hidden focus warning when Bootstrap modals close
$(document).on('hide.bs.modal', '.modal', function () {
    if (document.activeElement && $.contains(this, document.activeElement)) {
        document.activeElement.blur();
    }
});


    $('#cliente').select2({
        ajax: {
            url: '/aplicacion/pagos/clientes',
            data: function(params) {
                var query = {
                    search: params.term,
                    type: 'public',
                    page: params.page || 1
                }


                return query;
            }
        }
    });





function modalRetencion(codigoPago, retencion, estadoRetencion, caiFactura, idFactura){
    $('#codAplicPago').val(codigoPago);
    $('#montoRetencion').val(retencion);
    $('#facturaCai').val(caiFactura);
    $('#idFacturaRetencion').val(idFactura);

    $('#modalretencion').modal('show');
}


function modalNotaCredito(codigoPagoA, caiFactura, idFactura, tieneNC ){
    $('#codAplicPagonc').val(codigoPagoA);
    $('#facturaCainc').val(caiFactura);
    $('#idFacturaNC').val(idFactura);



    //llamando todas las notas de credito de la factura en cuestion

    if(tieneNC == 1){
        //Tiene notas de credito esa factura
        axios.get("/listar/nc/aplicacion/"+idFactura)
        .then(response => {

            let notas = response.data.results;
            console.log(response);
            let htmlnotas = '  <option value="" selected disabled >--Seleccione la nota a aplicar--</option>';

            notas.forEach(element => {

                htmlnotas += `
                <option value="${element.idNotaCredito}" >${element.correlativo}</option>
                `
            });

            document.getElementById('selectNotaCredito').innerHTML = htmlnotas;
            $('#modalNC').modal('show');

        })
        .catch(err => {
            let data = err.response.data;
            Swal.fire({
                icon: data.icon,
                title: data.title,
                text: data.text
            })
            console.error(err);
        });
    }else{
        //No tiene Tiene notas de credito esa factura
        Swal.fire({
            icon: 'Info',
            text: "Esta factura no cuenta con notas de crédito para aplicar."
        });

    }



}

function datosNotaCredito(){
    let idNotaCredito = document.getElementById('selectNotaCredito').value;
    axios.get("/listar/nc/aplicacion/datos/"+idNotaCredito)
    .then(response => {

        let nota = response.data.result;

        console.log(nota[0].estado_rebajado);
        /*LLENANDO EL SELECT DE LA APLICACION DEL PAGO*/
        /*if(nota[0].estado_rebajado == 1){
            document.getElementById("selectAplicado").innerHTML += '<option selected class="form-control" value="1">SE APLICA REBAJA DE NOTA DE CRÉDITO - <span class="badge badge-success">ACTUÁL</span></option>';
            document.getElementById("selectAplicado").innerHTML += '<option class="form-control" value="2">NO SE APLICA REBAJA DE NOTA DE CRÉDITO</option>';
        }else{
            document.getElementById("selectAplicado").innerHTML += '<option  class="form-control" value="1">SE APLICA REBAJA DE NOTA DE CRÉDITO</option>';
            document.getElementById("selectAplicado").innerHTML += '<option selected class="form-control" value="2">NO SE APLICA REBAJA DE NOTA DE CRÉDITO - <span class="badge badge-success">ACTUÁL</span></option>';
        }*/


        $('#totalNotaCredito').val(nota[0].total);
        $('#motivoNotacredito').val(nota[0].comentario);
    })
    .catch(err => {
        let data = err.response.data;
        Swal.fire({
            icon: data.icon,
            title: data.title,
            text: data.text
        })
        console.error(err);
    });
}

function modalNotaDebito(codigoPagoA, caiFactura, idFactura, tieneND ){
    $('#codAplicPagond').val(codigoPagoA);
    $('#facturaCaind').val(caiFactura);
    $('#idFacturaND').val(idFactura);



    //llamando todas las notas de credito de la factura en cuestion

    if(tieneND == 1){
        //Tiene notas de credito esa factura
        axios.get("/listar/nd/aplicacion/"+idFactura)
        .then(response => {

            let notas = response.data.results;
            console.log(response);
            let htmlnotas = '  <option value="" selected disabled >--Seleccione la nota a aplicar--</option>';

            notas.forEach(element => {

                htmlnotas += `
                <option value="${element.idNotaDebito}" >${element.correlativo}</option>
                `
            });

            document.getElementById('selectNotaDebito').innerHTML = htmlnotas;
            $('#modalND').modal('show');

        })
        .catch(err => {
            let data = err.response.data;
            Swal.fire({
                icon: data.icon,
                title: data.title,
                text: data.text
            })
            console.error(err);
        });
    }else{
        //No tiene Tiene notas de credito esa factura
        Swal.fire({
            icon: 'Info',
            text: "Esta factura no cuenta con notas de Debito para aplicar."
        });

    }



}

function datosNotaDebito(){
    let idNotaDebito = document.getElementById('selectNotaDebito').value;

    console.log(idNotaDebito);
    axios.get("/listar/nd/aplicacion/datos/"+idNotaDebito)
    .then(response => {

        let nota = response.data.result;

        console.log(nota[0]);
        /*LLENANDO EL SELECT DE LA APLICACION DEL PAGO*/
        /*if(nota[0].estado_sumado == 1){
            document.getElementById("selectAplicadond").innerHTML += '<option selected class="form-control" value="1">SE APLICA SUMA DE NOTA DE CRÉDITO - <span class="badge badge-success">ACTUÁL</span></option>';
            document.getElementById("selectAplicadond").innerHTML += '<option class="form-control" value="2">NO SE APLICA SUMA DE NOTA DE CRÉDITO</option>';
        }else{
            document.getElementById("selectAplicadond").innerHTML += '<option  class="form-control" value="1">SE APLICA SUMA DE NOTA DE CRÉDITO</option>';
            document.getElementById("selectAplicadond").innerHTML += '<option selected class="form-control" value="2">NO SE APLICA SUMA DE NOTA DE CRÉDITO - <span class="badge badge-success">ACTUÁL</span></option>';
        }*/


        $('#totalNotaDebito').val(nota[0].total);
        $('#motivoNotaDebito').val(nota[0].comentario);
    })
    .catch(err => {
        let data = err.response.data;
        Swal.fire({
            icon: data.icon,
            title: data.title,
            text: data.text
        })
        console.error(err);
    });
}

function modalOtrosMovimientos(codigoPagoA, caiFactura, idFactura, saldo){
    $('#codAplicPagoom').val(codigoPagoA);
    $('#facturaCaiom').val(caiFactura);
    $('#idFacturaom').val(idFactura);

    var s = parseFloat(saldo) || 0;
    $('#montoTM').val(s > 0 ? s.toFixed(2) : '');
    $('#om-saldo-label').text(s > 0 ? '(Total: L. ' + s.toLocaleString('es-HN', {minimumFractionDigits:2, maximumFractionDigits:2}) + ')' : '');

    $('#modalOtrosMovimientos').modal('show');
}

function modalAbonos(codigoPagoA, caiFactura, idFactura, saldo){
    $('#codAplicPagoAbono').val(codigoPagoA);
    $('#facturaCaiAbono').val(caiFactura);
    $('#idFacturaAbono').val(idFactura);

    var s = parseFloat(saldo) || 0;
    $('#montoAbono').val(s > 0 ? s.toFixed(2) : '');
    $('#abono-saldo-label').text(s > 0 ? '(Total: L. ' + s.toLocaleString('es-HN', {minimumFractionDigits:2, maximumFractionDigits:2}) + ')' : '');

    datosBanco();
    $('#modalAbonos').modal('show');
}

function llamarTablas(){
    $('#tbl_principal_div').removeClass('d-none');
    $('#tbl_movimientos_div').removeClass('d-none');
    $('#tbl_creditos_abonos_div').removeClass('d-none');

    $("#tbl_cuentas_facturas_cliente").dataTable().fnDestroy();
    $("#tbl_tipo_movimientos_cliente").dataTable().fnDestroy();
    $("#tbl_abonos_cliente").dataTable().fnDestroy();


    this.listarCuentasPorCobrar();

    this.listarMovimientos();
    this.listarAbonos()

    $('#btnEC').removeClass('d-none');


}

function listarCuentasPorCobrar() {

    var idCliente = document.getElementById('cliente').value;
    $('#tbl_cuentas_facturas_cliente').DataTable({
        "paging": true,
        "language": {
            "url": "//cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css"
        },
        pageLength: 10,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp',
                buttons: [
                ],
                "ajax": "/aplicacion/pagos/listar/"+idCliente,
                "columns": [

                    {
                        data: 'codigoPago'
                    },
                    {
                        data: 'idFactura'
                    },
                    {
                        data: 'codigoFactura'
                    },
                    {
                        data: 'cargo'
                    },
                    {
                        data: 'notasCredito'
                    },
                    {
                        data: 'notasDebito'
                    },
                    {
                        data: 'abonosCargo'
                    },
                    {
                        data: 'movSuma'
                    },
                    {
                        data: 'movResta'
                    },
                    {
                        data: 'isv'
                    },
                    {
                        data: 'retencion_aplicada',
                        render: function (data, type, row) {


                            if(data != 1){
                                return "<span class='badge badge-warnig'>NO SE APLICA (+)</span>";
                            }else{
                                return "<span class='badge badge-success'>SE APLICA (-)</span>";
                            }


                        }
                    },
                    {
                        data: 'saldo'
                    },
                    {
                        data: 'fechaRegistro'
                    },
                    {
                        data: 'ultimoRegistro'
                    },
                    {
                        data: 'acciones'
                    }


                ],initComplete: function () {
                    this.api()
                        .columns()
                        .every(function () {
                            let column = this;
                            let footer = column.footer();
                            if (!footer) return;
                            let title = footer.textContent;

                            // Create input element
                            let input = document.createElement('input');
                            input.placeholder = title;
                            input.style.width = '100%';
                            footer.replaceChildren(input);

                            // Event listener for user input
                            input.addEventListener('keyup', () => {
                                if (column.search() !== input.value) {
                                    column.search(input.value).draw();
                                }
                            });
                        });
                }

            });


            $('#btnEC').css('display','block');
            $('#btnEC').show();
}

function listarMovimientos() {

    var idCliente = document.getElementById('cliente').value;
    $('#tbl_tipo_movimientos_cliente').DataTable({
        "paging": true,
        "language": {
            "url": "//cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css"
        },
        pageLength: 10,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp',
                buttons: [
                ],
                "ajax": "/aplicacion/pagos/listar/movimientos/"+idCliente,
                "columns": [
                    {
                        data: 'codigoMovimiento'
                    },
                    {
                        data: 'codigoPago'
                    },
                    {
                        data: 'correlativo'
                    },
                    {
                        data: 'monto'
                    },
                    {
                        data: 'tipo_movimiento',
                        render: function (data, type, row) {


                            if(data === 1){
                                return "<span class='badge badge-success'>CARGO</span>";
                            }else if(data === 2){
                                return "<span class='badge badge-danger'>REBAJA</span>";
                            }


                        }
                    },
                    {
                        data: 'comentario'
                    },
                    {
                        data: 'estadoMov',
                        render: function (data, type, row) {


                            if(data === 1){
                                return "<span class='badge badge-success'>ACTIVO</span>";
                            }else if(data === 2){
                                return "<span class='badge badge-danger'>INACTIVO</span>";
                            }


                        }
                    },
                    {
                        data: 'userRegistro'
                    },
                    {
                        data: 'fechaRegistro'
                    },
                    {
                        data: 'acciones'
                    }

                ],initComplete: function () {
                    this.api()
                        .columns()
                        .every(function () {
                            let column = this;
                            let footer = column.footer();
                            if (!footer) return;
                            let title = footer.textContent;

                            // Create input element
                            let input = document.createElement('input');
                            input.placeholder = title;
                            input.style.width = '100%';
                            footer.replaceChildren(input);

                            // Event listener for user input
                            input.addEventListener('keyup', () => {
                                if (column.search() !== input.value) {
                                    column.search(input.value).draw();
                                }
                            });
                        });
                }

            });
}

function listarAbonos() {

    var idCliente = document.getElementById('cliente').value;
    $('#tbl_abonos_cliente').DataTable({
        "paging": true,
        "language": {
            "url": "//cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css"
        },
        pageLength: 10,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp',
                buttons: [
                ],
                "ajax": "/aplicacion/pagos/listar/abonos/"+idCliente,
                "columns": [

                    {
                        data: 'codigoAbono'
                    },
                    {
                        data: 'codigoPago'
                    },
                    {
                        data: 'correlativo'
                    },
                    {
                        data: 'monto'
                    },
                    {
                        data: 'comentarioabono'
                    },
                    {
                        data: 'estadoAbono',
                        render: function (data, type, row) {


                            if(data === 1){
                                return "<span class='badge badge-success'>ACTIVO</span>";
                            }else if(data === 2){
                                return "<span class='badge badge-danger'>INACTIVO</span>";
                            }


                        }
                    },
                    {
                        data: 'userRegistro'
                    },
                    {
                        data: 'fechaRegistro'
                    },
                    {
                        data: 'acciones'
                    }


                ],initComplete: function () {
                    this.api()
                        .columns()
                        .every(function () {
                            let column = this;
                            let footer = column.footer();
                            if (!footer) return;
                            let title = footer.textContent;

                            // Create input element
                            let input = document.createElement('input');
                            input.placeholder = title;
                            input.style.width = '100%';
                            footer.replaceChildren(input);

                            // Event listener for user input
                            input.addEventListener('keyup', () => {
                                if (column.search() !== input.value) {
                                    column.search(input.value).draw();
                                }
                            });
                        });
                }

            });
}
/////////////////////////////FUNCIONALIDADES DE LAS GESTIONES

$(document).on('submit', '#formEstadoRetencion', function(event) {

    $('#btn_cambioRetencion').css('display','none');
    $('#btn_cambioRetencion').hide();


    $('#modalretencion').modal('hide');

    event.preventDefault();
    guardarRetencions();
});

function guardarRetencions(){
    var data = new FormData($('#formEstadoRetencion').get(0));

    axios.post("/pagos/retencion/guardar", data)
        .then(response => {

            //$('#formEstadoRetencion').parsley().reset();
            $('#tbl_cuentas_facturas_cliente').DataTable().ajax.reload();

            var formulario = document.getElementById("formEstadoRetencion");

            // Resetear el formulario, lo que también reseteará el valor del TextArea
            formulario.reset();

            $('#btn_cambioRetencion').css('display','block');
            $('#btn_cambioRetencion').show();

            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Ha realizado gestiona la retención."
            });

    })
    .catch(err => {
        let data = err.response.data;
        Swal.fire({
            icon: data.icon,
            title: data.title,
            text: data.text
        })
        console.error(err);

    })
}

$(document).on('submit', '#formNotaCredito', function(event) {

    $('#btn_notacredito').css('display','none');
    $('#btn_notacredito').hide();


    $('#modalNC').modal('hide');

    event.preventDefault();
    guardargNC();
});

function guardargNC(){
    var data = new FormData($('#formNotaCredito').get(0));

    axios.post("/pagos/notacredito/guardar", data)
        .then(response => {

            //$('#formEstadoRetencion').parsley().reset();
            $('#tbl_cuentas_facturas_cliente').DataTable().ajax.reload();

            var formulario = document.getElementById("formNotaCredito");

            // Resetear el formulario, lo que también reseteará el valor del TextArea
            formulario.reset();

            $('#btn_notacredito').css('display','block');
            $('#btn_notacredito').show();

            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Ha realizado la gestion."
            });

    })
    .catch(err => {
        let data = err.response.data;
        Swal.fire({
            icon: data.icon,
            title: data.title,
            text: data.text
        })
        console.error(err);

    })
}


$(document).on('submit', '#formNotaDebito', function(event) {

    $('#btn_notadebito').css('display','none');
    $('#btn_notadebito').hide();


    $('#modalND').modal('hide');

    event.preventDefault();
    guardargND();
});

function guardargND(){
    var data = new FormData($('#formNotaDebito').get(0));

    axios.post("/pagos/notadebito/guardar", data)
        .then(response => {

            //$('#formEstadoRetencion').parsley().reset();
            $('#tbl_cuentas_facturas_cliente').DataTable().ajax.reload();

            var formulario = document.getElementById("formNotaDebito");

            // Resetear el formulario, lo que también reseteará el valor del TextArea
            formulario.reset();

            $('#btn_notadebito').css('display','block');
            $('#btn_notadebito').show();

            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Ha realizado la gestion."
            });

    })
    .catch(err => {
        let data = err.response.data;
        Swal.fire({
            icon: data.icon,
            title: data.title,
            text: data.text
        })
        console.error(err);

    })
}

$(document).on('submit', '#formOtrosMovimientos', function(event) {

    $('#btn_tipomov').css('display','none');
    $('#btn_tipomov').hide();


    $('#modalOtrosMovimientos').modal('hide');

    event.preventDefault();
    guardarOtroMov();
});

function guardarOtroMov(){
    var data = new FormData($('#formOtrosMovimientos').get(0));

    axios.post("/pagos/otrosmov/guardar", data)
        .then(response => {

            //$('#formEstadoRetencion').parsley().reset();
            $('#tbl_cuentas_facturas_cliente').DataTable().ajax.reload();
            $('#tbl_tipo_movimientos_cliente').DataTable().ajax.reload();

            var formulario = document.getElementById("formOtrosMovimientos");

            // Resetear el formulario, lo que también reseteará el valor del TextArea
            formulario.reset();

            $('#btn_tipomov').css('display','block');
            $('#btn_tipomov').show();

            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Ha realizado la gestion."
            });

    })
    .catch(err => {
        let data = err.response.data;
        Swal.fire({
            icon: data.icon,
            title: data.title,
            text: data.text
        })
        console.error(err);

    })
}

$(document).on('submit', '#formabonos', function(event) {

    $('#btn_notaabono').css('display','none');
    $('#btn_notaabono').hide();

    event.preventDefault();

    var facturaId        = $('#idFacturaAbono').val();
    var montoAbono       = $('#montoAbono').val();
    var aplicacionPagoId = $('#codAplicPagoAbono').val();

    // Consultar si este pago cerrará la factura y qué roles recibirán comisión
    axios.get('/pagos/preview-comisiones', {
        params: {
            factura_id:          facturaId,
            monto_abono:         montoAbono,
            aplicacion_pagos_id: aplicacionPagoId
        }
    }).then(function(response) {
        var preview = response.data;

        if (preview.cerrara && preview.targets && preview.targets.length > 0) {
            // Capturar FormData ANTES de ocultar el modal (el evento
            // hidden.bs.modal limpia selectBanco, lo que causa banco_id=null)
            _pendingAbonoData = new FormData($('#formabonos').get(0));

            // Preparar el contenido ANTES de hacer cualquier cambio de modal
            renderPreviewComisiones(preview.targets);

            // Esperar a que #modalAbonos esté completamente oculto para abrir
            // el preview (evita que el backdrop de Bootstrap bloquee los clics)
            $('#modalAbonos').one('hidden.bs.modal', function() {
                // Limpiar cualquier backdrop residual
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
                $('#modalPreviewComisiones').modal('show');
            });
            $('#modalAbonos').modal('hide');
        } else {
            // No cerrará o ya fue comisionada — proceder directamente
            $('#modalAbonos').modal('hide');
            guardarCreditos();
        }
    }).catch(function() {
        // En caso de error en el preview, proceder igual
        $('#modalAbonos').modal('hide');
        guardarCreditos();
    });
});

/* Renderiza la tabla de roles a comisionar en el modal de preview */
function renderPreviewComisiones(targets) {
    var tipoConfig = {
        1: { label: 'FACTURADOR', color: '#f59e0b' },
        2: { label: 'ROL REAL',   color: '#3b82f6' },
        3: { label: 'VENDEDOR',   color: '#10b981' }
    };

    var html = '<div class="table-responsive">'
        + '<table class="table table-bordered table-sm mb-0" style="font-size:.9rem;">'
        + '<thead style="background:#1e40af;color:#fff;">'
        + '<tr><th style="width:120px;">Capacidad</th><th>Empleado</th><th>Rol de Comisión</th><th class="text-center" style="width:110px;">Escala Activa</th></tr>'
        + '</thead><tbody>';

    targets.forEach(function(t) {
        var cfg   = tipoConfig[t.tipo] || { label: 'N/D', color: '#6b7280' };
        var badge = '<span style="background:' + cfg.color + ';color:#fff;padding:2px 8px;border-radius:8px;font-size:10px;font-weight:700;">'
            + cfg.label + '</span>';
        var escala = t.tiene_escala
            ? '<span class="badge badge-success" style="font-size:10px;">&#10003; Configurada</span>'
            : '<span class="badge badge-secondary" style="font-size:10px;">&#8212; Sin escala</span>';

        html += '<tr>'
            + '<td class="text-center">' + badge + '</td>'
            + '<td><i class="fa fa-user mr-1 text-muted"></i><strong>' + t.empleado + '</strong></td>'
            + '<td>' + t.rol_nombre + '</td>'
            + '<td class="text-center">' + escala + '</td>'
            + '</tr>';
    });

    html += '</tbody></table></div>';
    $('#preview-comisiones-lista').html(html);
}

/* Confirmar: cerrar preview y ejecutar el guardado */
$(document).on('click', '#btn-confirmar-y-guardar-pago', function() {
    $('#modalPreviewComisiones').modal('hide');
    guardarCreditos();
});

/* Cancelar: cerrar preview y reabrir el modal de abonos */
$(document).on('click', '#btn-cancel-preview-comision', function() {
    $('#modalPreviewComisiones').one('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
        $('#btn_notaabono').css('display','block').show();
        $('#modalAbonos').modal('show');
    });
    $('#modalPreviewComisiones').modal('hide');
});

function guardarCreditos(){
    // Usar los datos capturados antes del cierre del modal (para no perder
    // selectBanco que es limpiado por el evento hidden.bs.modal)
    var data = _pendingAbonoData ? _pendingAbonoData : new FormData($('#formabonos').get(0));
    _pendingAbonoData = null;

    axios.post("/pagos/creditos/guardar", data)
        .then(response => {

            //$('#formEstadoRetencion').parsley().reset();
            $('#tbl_cuentas_facturas_cliente').DataTable().ajax.reload();
            $('#tbl_abonos_cliente').DataTable().ajax.reload();

            var formulario = document.getElementById("formabonos");

            // Resetear el formulario, lo que también reseteará el valor del TextArea
            formulario.reset();

            $('#btn_notaabono').css('display','block');
            $('#btn_notaabono').show();

            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Ha realizado la gestion."
            });

    })
    .catch(err => {
        let data = err.response.data;
        Swal.fire({
            icon: data.icon,
            title: data.title,
            text: data.text
        })
        console.error(err);

    })
}


function AnularOtroMov(idOtroMov){

    axios.get("/pagos/anular/movimiento/"+idOtroMov)
        .then(response => {

            $('#tbl_cuentas_facturas_cliente').DataTable().ajax.reload();
            $('#tbl_tipo_movimientos_cliente').DataTable().ajax.reload();
            $('#tbl_abonos_cliente').DataTable().ajax.reload();


            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Anulado con exito."
            })

    })
    .catch(err => {
        console.error(err);
        Swal.fire({
                icon: 'error',
                text: "Hubo un error al anular nota de débito."
            })

    })

}

function metodoPago() {
    var metodo = document.getElementById('selectMetodoPago').value;
    var selectBanco = document.getElementById('selectBanco');
    // EFECTIVO (1) does not require a bank; all other methods do
    if (metodo == '1') {
        selectBanco.removeAttribute('required');
    } else {
        selectBanco.setAttribute('required', 'required');
    }
}

function datosBanco(){
    document.getElementById("selectBanco").innerHTML  ='';
    axios.get("/listar/aplicacion/bancos")
    .then(response => {

        let datos = response.data.result;
        datos.forEach((element) => document.getElementById("selectBanco").innerHTML += '<option  class="form-control" value="'+element.idBanco+'">'+element.banco+'</option>');
    })
    .catch(err => {
        let data = err.response.data;
        Swal.fire({
            icon: data.icon,
            title: data.title,
            text: data.text
        })
        console.error(err);
    });
}

function modalcerrarFactura(codigoPagoA, caiFactura, idFactura){
    $('#codAplicCierre').val(codigoPagoA);
    $('#facturaCaiCierre').val(caiFactura);
    $('#idFacturaCierre').val(idFactura);

    $('#modalcerrarFact').modal('show');
}


$(document).on('submit', '#formCierrefact', function(event) {

    $('#btn_cierreFact').css('display','none');
    $('#btn_cierreFact').hide();


    $('#modalcerrarFact').modal('hide');

    event.preventDefault();
    cerrarFactura();
});

function cerrarFactura(){
    var data = new FormData($('#formCierrefact').get(0));

    axios.post("/pagos/cerrar/factura", data)
        .then(response => {

            //$('#formEstadoRetencion').parsley().reset();
            $('#tbl_cuentas_facturas_cliente').DataTable().ajax.reload();

            var formulario = document.getElementById("formCierrefact");

            // Resetear el formulario, lo que también reseteará el valor del TextArea
            formulario.reset();

            $('#btn_cierreFact').css('display','block');
            $('#btn_cierreFact').show();

            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Ha realizado la gestion."
            });

    })
    .catch(err => {
        let data = err.response.data;
        Swal.fire({
            icon: data.icon,
            title: data.title,
            text: data.text
        })
        console.error(err);

    })
}

function pdfEstadoCuenta(){

    var idClientepdf = document.getElementById('cliente').value;
    window.open('/estadoCuenta/imprimir/aplicpagos/'+idClientepdf, '_blank');
}
