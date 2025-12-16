<div>
<div class="row border-bottom white-bg page-heading align-items-center">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2 class="mb-0">Aplicación de Pagos</h2>
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="#">Cuentas por Cobrar</a></li>
            <li class="breadcrumb-item active">Pagos</li>
        </ol>
    </div></div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-12">
                <div class="ibox">
                    <div class="ibox-content">

                        <div class="form-row align-items-end">


                            <!-- Solicitar -->
                            <div class="form-group col-md-1">
                                <label class="invisible">Acción</label>
                                <button type="button"
                                        class="btn btn-primary btn-block"
                                        onclick="llamarTablas()">
                                    <i class="fa fa-search"></i> Solicitar
                                </button>
                            </div>
                            <!-- Cliente -->
                            <div class="form-group col-md-4">
                                </label>
                                <select id="cliente"
                                        name="cliente"
                                        class="form-control"
                                        required>
                                    <option value="" disabled selected>
                                        -- Seleccionar un Cliente --
                                    </option>
                                </select>
                            </div>

                            <div class="form-group col-md-3 ml-auto" id="btnEC">
                                <button type="button"
                                        class="btn btn-outline-primary btn-block"
                                        onclick="pdfEstadoCuenta()">
                                    <i class="fa fa-file-pdf-o"></i> Estado de Cuenta
                                </button>
                            </div>


                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--  MODAL DE RETENCION DE ISV  --}}
    <div class="modal" id="modalretencion" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h3 class="modal-title">Seleccione accion para la retención:</h3>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox ">
                            <div class="ibox-content">
                                <form class="form-control" id="formEstadoRetencion" name="formEstadoRetencion" >
                                <div class="row">
                                        <div class="row">
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Código de Registro:</b></label>
                                                <input type="text" readonly class="form-control" id="codAplicPago" name="codAplicPago" >
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Factura:</b></label>
                                                <input type="text" readonly class="form-control" id="facturaCai" name="facturaCai" >

                                                <input type="hidden" id="idFacturaRetencion" name="idFacturaRetencion" >
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Monto de retención:</b></label>
                                                <input type="text" readonly class="form-control" id="montoRetencion" name="montoRetencion" >
                                            </div>
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Nota (Obligatoria):</b></label>
                                                <textarea required class="form-control" id="comentario_retencion" name="comentario_retencion" cols="30" rows="10"></textarea>
                                            </div>


                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Seleccione estado de retención</b></label>

                                                 <select id="selectTiporetencion" name="selectTiporetencion" class="form-control form-select form-select-lg">

                                                   <option class="form-control" value="2">APLICA</option>
                                                   <option class="form-control"  value="1">NO APLICA</option>
                                                 </select>
                                            </div>
                                        </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <button id="btn_cambioRetencion" class="btn  btn-dark btn-lg btn-block float-left m-t-n-xs">
                                            <strong>
                                                Guardar gestión
                                            </strong>
                                        </button>
                                    </div>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
          </div>
        </div>
    </div>
    {{--  FIN DEL MODAL DE RETENCION ISV  --}}

    {{--  MODAL APLICAR NOTA DE CREDITO  --}}
    <div class="modal" id="modalNC" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h3 class="modal-title">Aplicación de Nota de Crédito:</h3>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox ">
                            <div class="ibox-content">
                                <form class="form-control" id="formNotaCredito" name="formNotaCredito" >
                                <div class="row">
                                        <div class="row">
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Código de Registro:</b></label>
                                                <input required type="text" readonly class="form-control" id="codAplicPagonc" name="codAplicPagonc" >
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Factura:</b></label>
                                                <input required type="text" readonly class="form-control" id="facturaCainc" name="facturaCainc" >

                                                <input type="hidden" id="idFacturaNC" name="idFacturaNC" >
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Selección de nota de crédito</b></label>

                                                 <select required onchange="datosNotaCredito()" id="selectNotaCredito" name="selectNotaCredito" class="form-control form-select form-select-lg">

                                                 </select>
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Monto de nota de crédito:</b></label>
                                                <input required type="text" readonly class="form-control" id="totalNotaCredito" name="totalNotaCredito" >
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Motivo Nota de crédito:</b></label>

                                                <textarea required readonly class="form-control"   id="motivoNotacredito" name="motivoNotacredito" cols="30" rows="5"></textarea>

                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Selección Acción para Nota de crédito</b></label>

                                                 <select required id="selectAplicado" name="selectAplicado" class="form-control form-select form-select-lg">
                                                    <option  class="form-control" selected>--------------SELECCIONE-----------------</option>
                                                    <option  class="form-control" value="1">SE APLICA</option>
                                                    <option  class="form-control" value="2">NO SE APLICA</option>
                                                 </select>
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Nota de aplicación:</b></label>

                                                <textarea required class="form-control" maxlength="500"   id="comentarioRebaja" name="comentarioRebaja" cols="30" rows="5"></textarea>

                                            </div>

                                        </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <button id="btn_notacredito" class="btn  btn-dark btn-lg btn-block float-left m-t-n-xs">
                                            <strong>
                                                Gestionar
                                            </strong>
                                        </button>
                                    </div>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
          </div>
        </div>
    </div>
    {{--  FIN DEL MODAL APLICAR NOTA DE CREDITO  --}}

    {{--  MODAL APLICAR NOTA DE DEBITO  --}}
    <div class="modal" id="modalND" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h3 class="modal-title">Aplicación de Nota de Débito:</h3>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox ">
                            <div class="ibox-content">
                                <form class="form-control" id="formNotaDebito" name="formNotaDebito" >
                                <div class="row">
                                        <div class="row">
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Código de Registro:</b></label>
                                                <input required type="text" readonly class="form-control" id="codAplicPagond" name="codAplicPagond" >
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Factura:</b></label>
                                                <input required type="text" readonly class="form-control" id="facturaCaind" name="facturaCaind" >

                                                <input type="hidden" id="idFacturaND" name="idFacturaND" >
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Selección de nota de dédito</b></label>

                                                 <select required onchange="datosNotaDebito()" id="selectNotaDebito" name="selectNotaDebito" class="form-control form-select form-select-lg">

                                                 </select>
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Monto de nota de dédito:</b></label>
                                                <input required type="text" readonly class="form-control" id="totalNotaDebito" name="totalNotaDebito" >
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Motivo Nota de dédito:</b></label>

                                                <textarea required maxlength="500" readonly class="form-control"   id="motivoNotaDebito" name="motivoNotaDebito" cols="30" rows="5"></textarea>

                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Selección Acción para Nota de dédito</b></label>

                                                 <select required id="selectAplicadond" name="selectAplicadond" class="form-control form-select form-select-lg">
                                                    <option  class="form-control" selected>--------------SELECCIONE-----------------</option>
                                                    <option  class="form-control" value="1">SE APLICA</option>
                                                    <option  class="form-control" value="2">NO SE APLICA</option>
                                                 </select>
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Nota de aplicación:</b></label>

                                                <textarea required class="form-control"   id="comentarioSuma" name="comentarioSuma" cols="30" rows="5"></textarea>

                                            </div>

                                        </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <button id="btn_notadebito" class="btn  btn-dark btn-lg btn-block float-left m-t-n-xs">
                                            <strong>
                                                Gestionar
                                            </strong>
                                        </button>
                                    </div>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
          </div>
        </div>
    </div>
    {{--  FIN DEL MODAL APLICAR NOTA DE DEBITO  --}}


    {{--  MODAL APLICAR OTROS MOVIMIENTOS  --}}
    <div class="modal fade" id="modalOtrosMovimientos" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content shadow-sm">

                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold">
                        Aplicación de otros movimientos a la factura Cobro/Rebajas
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="ibox mb-0">
                                <div class="ibox-content p-3">

                                    <form class="form-control border-0 p-0"
                                        id="formOtrosMovimientos"
                                        name="formOtrosMovimientos">

                                        <div class="row">

                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="font-weight-bold">
                                                    Código de Registro
                                                </label>
                                                <input required
                                                    type="text"
                                                    readonly
                                                    class="form-control"
                                                    id="codAplicPagoom"
                                                    name="codAplicPagoom">
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="font-weight-bold">
                                                    Factura
                                                </label>
                                                <input required
                                                    type="text"
                                                    readonly
                                                    class="form-control"
                                                    id="facturaCaiom"
                                                    name="facturaCaiom">
                                                <input type="hidden"
                                                    id="idFacturaom"
                                                    name="idFacturaom">
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="font-weight-bold">
                                                    Selección el tipo de Movimiento a realizar
                                                </label>
                                                <select required
                                                        id="selecttipoMovimiento"
                                                        name="selecttipoMovimiento"
                                                        class="form-control">
                                                    <option selected>--------SELECCIONE MOVIMIENTO----------</option>
                                                    <option value="1">CARGO EXTRA</option>
                                                    <option value="2">CARGO A DEDUCIR</option>
                                                </select>
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="font-weight-bold">
                                                    Monto por Aplicar
                                                </label>
                                                <input required
                                                    type="number"
                                                    step="any"
                                                    min="0"
                                                    class="form-control"
                                                    id="montoTM"
                                                    name="montoTM">
                                            </div>

                                            <div class="col-12 mb-3">
                                                <label class="font-weight-bold">
                                                    Comentario del movimiento
                                                </label>
                                                <textarea required
                                                        maxlength="500"
                                                        class="form-control"
                                                        id="motivoMovimiento"
                                                        name="motivoMovimiento"
                                                        rows="4"
                                                        placeholder="Ingrese el comentario del movimiento"></textarea>
                                            </div>

                                        </div>

                                        <hr class="my-3">

                                        <div class="row">
                                            <div class="col-12 text-right">
                                                <button id="btn_tipomov"
                                                        class="btn btn-dark btn-lg px-4">
                                                    <strong>Guardar Gestionar</strong>
                                                </button>
                                            </div>
                                        </div>

                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    {{--  FIN DEL MODAL OTROS MOVIMIENTOS  --}}


    {{--  MODAL APLICAR CREDITOS/ABONOS  --}}
    <div class="modal fade" id="modalAbonos" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content shadow-sm">

                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold">
                        Aplicación Créditos o Abonos al Saldo de la Factura
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="ibox mb-0">
                                <div class="ibox-content p-3">

                                    <form class="form-control border-0 p-0"
                                        id="formabonos"
                                        name="formabonos">

                                        <div class="row">

                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="font-weight-bold">
                                                    Código de Registro
                                                </label>
                                                <input required
                                                    type="text"
                                                    readonly
                                                    class="form-control"
                                                    id="codAplicPagoAbono"
                                                    name="codAplicPagoAbono">
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="font-weight-bold">
                                                    Factura
                                                </label>
                                                <input required
                                                    type="text"
                                                    readonly
                                                    class="form-control"
                                                    id="facturaCaiAbono"
                                                    name="facturaCaiAbono">
                                                <input type="hidden"
                                                    id="idFacturaAbono"
                                                    name="idFacturaAbono">
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="font-weight-bold">
                                                    Monto por Aplicar
                                                </label>
                                                <input required
                                                    type="number"
                                                    min="0"
                                                    step="any"
                                                    class="form-control"
                                                    id="montoAbono"
                                                    name="montoAbono">
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="font-weight-bold">
                                                    Selección Medio de Pago
                                                </label>
                                                <select required
                                                        onchange="metodoPago()"
                                                        id="selectMetodoPago"
                                                        name="selectMetodoPago"
                                                        class="form-control">
                                                    <option selected>--------Seleccione------</option>
                                                    <option value="1">EFECTIVO</option>
                                                    <option value="2">TRANSFERENCIA BANCARIA</option>
                                                    <option value="3">CHEQUE</option>
                                                    <option value="4">LINK DE PAGO</option>
                                                    <option value="5">POS</option>
                                                </select>
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="font-weight-bold">
                                                    Selección banco
                                                </label>
                                                <select required
                                                        id="selectBanco"
                                                        name="selectBanco"
                                                        class="form-control">
                                                </select>
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="font-weight-bold">
                                                    Fecha que se realizó el pago <span class="text-danger">*</span>
                                                </label>
                                                <input class="form-control"
                                                    required
                                                    type="date"
                                                    id="fecha_pago"
                                                    name="fecha_pago"
                                                    data-parsley-required>
                                            </div>

                                            <div class="col-12 mb-3">
                                                <label class="font-weight-bold">
                                                    Documento de Pago <span class="text-danger">*</span>
                                                </label>
                                                <input class="form-control"
                                                    id="doc_pago"
                                                    name="doc_pago"
                                                    type="file"
                                                    accept="image/png, image/jpeg, image/jpg, application/pdf">
                                            </div>

                                            <div class="col-12 mb-3">
                                                <label class="font-weight-bold">
                                                    Nota de pago
                                                </label>
                                                <textarea required
                                                        class="form-control"
                                                        id="comentarioAbono"
                                                        name="comentarioAbono"
                                                        rows="4"
                                                        placeholder="Ingrese la nota del pago realizado"></textarea>
                                            </div>

                                        </div>

                                        <hr class="my-3">

                                        <div class="row">
                                            <div class="col-12 text-right">
                                                <button id="btn_notaabono"
                                                        class="btn btn-dark btn-lg px-4">
                                                    <strong>Gestionar</strong>
                                                </button>
                                            </div>
                                        </div>

                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    {{--  FIN DEL MODAL APLICAR CREDITOS/ABONOS  --}}



    {{--  MODAL APLICAR CREDITOS/ABONOS  --}}
    <div class="modal fade" id="modalcerrarFact" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content shadow-sm">

                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold">
                        Cerrar factura
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="ibox mb-0">
                                <div class="ibox-content p-3">

                                    <form class="form-control border-0 p-0"
                                        id="formCierrefact"
                                        name="formCierrefact">

                                        <div class="row">

                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="font-weight-bold">
                                                    Código de Registro
                                                </label>
                                                <input required
                                                    type="text"
                                                    readonly
                                                    class="form-control"
                                                    id="codAplicCierre"
                                                    name="codAplicCierre">
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="font-weight-bold">
                                                    Factura
                                                </label>
                                                <input required
                                                    type="text"
                                                    readonly
                                                    class="form-control"
                                                    id="facturaCaiCierre"
                                                    name="facturaCaiCierre">
                                                <input type="hidden"
                                                    id="idFacturaCierre"
                                                    name="idFacturaCierre">
                                            </div>

                                            <div class="col-12 mb-3">
                                                <label class="font-weight-bold">
                                                    Nota de cierre
                                                </label>
                                                <textarea required
                                                        class="form-control"
                                                        id="comentarioCierre"
                                                        name="comentarioCierre"
                                                        rows="4"
                                                        placeholder="Ingrese la nota de cierre de la factura"></textarea>
                                            </div>

                                        </div>

                                        <hr class="my-3">

                                        <div class="row">
                                            <div class="col-12 text-right">
                                                <button id="btn_cierreFact"
                                                        class="btn btn-dark btn-lg px-4">
                                                    <strong>Gestionar</strong>
                                                </button>
                                            </div>
                                        </div>

                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    {{--  FIN DEL MODAL APLICAR CREDITOS/ABONOS  --}}



    {{-- ================= TABLA PRINCIPAL DE REGISTRO ================= --}}
    <div class="wrapper wrapper-content animated fadeInRight" id="tbl_principal_div">
        <div class="row">
            <div class="col-12">
                <div class="ibox">

                    <!-- Header de la tabla -->
                    <div class="ibox-title d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold">
                            Registros de saldos por factura
                        </h5>
                        <span class="badge badge-primary">
                            Cliente seleccionado
                        </span>
                    </div>

                    <div class="ibox-content">

                        <div class="table-responsive">

                            <table id="tbl_cuentas_facturas_cliente"
                                class="table table-sm table-striped table-bordered table-hover w-100">

                                <thead class="thead-light">
                                    <tr>
                                        <th>Código Pago</th>
                                        <th>Factura</th>
                                        <th>Correlativo</th>
                                        <th>Cargo</th>
                                        <th>Notas Crédito</th>
                                        <th>Notas Débito</th>
                                        <th>Créditos / Abonos</th>
                                        <th>Cargo Extra</th>
                                        <th>Deducciones</th>
                                        <th>ISV</th>
                                        <th>Retención</th>
                                        <th>Saldo</th>
                                        <th>Fecha Registro</th>
                                        <th>Última Actualización</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    {{-- DataTables --}}
                                </tbody>

                                <tfoot class="thead-light">
                                    <tr>
                                        <th>Código Pago</th>
                                        <th>Factura</th>
                                        <th>Correlativo</th>
                                        <th>Cargo</th>
                                        <th>Notas Crédito</th>
                                        <th>Notas Débito</th>
                                        <th>Créditos / Abonos</th>
                                        <th>Cargo Extra</th>
                                        <th>Deducciones</th>
                                        <th>ISV</th>
                                        <th>Retención</th>
                                        <th>Saldo</th>
                                        <th>Fecha Registro</th>
                                        <th>Última Actualización</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </tfoot>

                            </table>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- ============== FIN TABLA PRINCIPAL DE REGISTRO ============== --}}

    {{--  TABLA DE OTROS MOVIMIENTOS --}}
    <div class="wrapper wrapper-content animated fadeInRight"  id="tbl_movimientos_div">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <h4>Movimientos por facturas del cliente:</h4>
                            <table id="tbl_tipo_movimientos_cliente" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Codigo Movimiento</th>
                                        <th>Codigo Pagos</th>
                                        <th>Factura</th>
                                        <th>Monto</th>
                                        <th>Movimiento</th>
                                        <th>Comentario</th>
                                        <th>Estado</th>
                                        <th>Registrado por/th>
                                        <th>Fecha de registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Codigo Movimiento</th>
                                            <th>Codigo Pagos</th>
                                            <th>Código Factura</th>
                                            <th>Monto</th>
                                            <th>Movimiento</th>
                                            <th>Comentario</th>
                                            <th>Estado</th>
                                            <th>Registrado por/th>
                                            <th>Fecha de registro</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </tfoot>

                                </tbody>
                            </table>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- FIN TABLA DE OTROS MOVIMIENTOS  --}}

    {{--  TABLA DE CREDITOS Y ABONOS --}}
    <div class="wrapper wrapper-content animated fadeInRight"  id="tbl_creditos_abonos_div">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <h4>Creditos y abonos hechos por factura:</h4>
                            <table id="tbl_abonos_cliente" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Codigo Abono</th>
                                        <th>Codigo Pagos</th>
                                        <th>Código Factura</th>
                                        <th>Monto</th>
                                        <th>Comentario</th>
                                        <th>Estado</th>
                                        <th>Registrado por/th>
                                        <th>Fecha de registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Codigo Abono</th>
                                            <th>Codigo Pagos</th>
                                            <th>Factura</th>
                                            <th>Monto</th>
                                            <th>Comentario</th>
                                            <th>Estado</th>
                                            <th>Registrado por/th>
                                            <th>Fecha de registro</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </tfoot>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- FIN TABLA DE CREDITOS Y ABONOS --}}

</div>
@push('scripts')

<script src="{{ asset('js/js_proyecto/cuentas-por-cobrar/pagos.js') }}"></script>
@endpush
