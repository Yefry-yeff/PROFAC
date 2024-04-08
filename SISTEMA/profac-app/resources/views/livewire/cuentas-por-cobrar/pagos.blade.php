<div>
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <h2>Aplicación de Pagos</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.html">/ Cuentas Por Cobrar</a>
                </li>


            </ol>
        </div>

    </div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="row">


                            <div class="col-6 col-sm-6 col-md-6 ">
                                <label for="cliente" class="col-form-label focus-label">Seleccionar Cliente:<span class="text-danger">*</span></label>
                                <select id="cliente" name="cliente" class="form-group form-control" style=""
                                    data-parsley-required >
                                    <option value="" selected disabled>--Seleccionar un Cliente--</option>
                                </select>
                            </div>

                            <div class="col-6 col-sm-6 col-md-6 " id="btnEC" name="btnEC" style="display: none">
                                <label for="cliente" class="alert alert-warning"> <b>Estado de cuenta</b> <span class="text-danger"></span></label>
                                <button class="btn btn-primary btn-block" onclick="pdfEstadoCuenta()"><i class="fa-solid fa-paper-plane text-white"></i> Visualizar </button>
                            </div>



                        </div>
                        <button class="btn btn-primary mt-2" onclick=" llamarTablas()"><i class="fa-solid fa-paper-plane text-white"></i> Solicitar</button>

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
    <div class="modal" id="modalOtrosMovimientos" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <h3 class="modal-title">Aplicación de otros movimientos a la factura Cobro/Rebajas:</h3>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox ">
                            <div class="ibox-content">
                                <form class="form-control" id="formOtrosMovimientos" name="formOtrosMovimientos" >
                                <div class="row">
                                        <div class="row">
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Código de Registro:</b></label>
                                                <input required type="text" readonly class="form-control" id="codAplicPagoom" name="codAplicPagoom" >
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Factura:</b></label>
                                                <input required type="text" readonly class="form-control" id="facturaCaiom" name="facturaCaiom" >

                                                <input type="hidden" id="idFacturaom" name="idFacturaom" >
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Selección el tipo de Movimiento a realizar</b></label>

                                                <select required id="selecttipoMovimiento" name="selecttipoMovimiento" class="form-control form-select form-select-lg">
                                                    <option class="form-control" selected>--------SELECCIONE MOVIMIENTO----------</option>
                                                    <option class="form-control" value="1">CARGO EXTRA</option>
                                                    <option class="form-control"  value="2">CARGO A DEDUCIR</option>
                                                </select>
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Monto por Aplicar:</b></label>
                                                <input required type="number" step="any" min="0" class="form-control" id="montoTM" name="montoTM" >
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Comentario del movimiento:</b></label>

                                                <textarea required maxlength="500" class="form-control"   id="motivoMovimiento" name="motivoMovimiento" cols="30" rows="5"></textarea>

                                            </div>

                                        </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <button id="btn_tipomov" class="btn  btn-dark btn-lg btn-block float-left m-t-n-xs">
                                            <strong>
                                                Guardar Gestionar
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
    {{--  FIN DEL MODAL OTROS MOVIMIENTOS  --}}


    {{--  MODAL APLICAR CREDITOS/ABONOS  --}}
    <div class="modal" id="modalAbonos" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <h3 class="modal-title">Aplicación Creditos o Abonos al Saldo de la factura:</h3>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox ">
                            <div class="ibox-content">
                                <form class="form-control" id="formabonos" name="formabonos" >
                                <div class="row">
                                        <div class="row">
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Código de Registro:</b></label>
                                                <input required type="text" readonly class="form-control" id="codAplicPagoAbono" name="codAplicPagoAbono" >
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Factura:</b></label>
                                                <input required type="text" readonly class="form-control" id="facturaCaiAbono" name="facturaCaiAbono" >

                                                <input type="hidden" id="idFacturaAbono" name="idFacturaAbono" >
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Monto por Aplicar:</b></label>
                                                <input required type="number" min="0" step="any" class="form-control" id="montoAbono" name="montoAbono" >
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Selección Medio de Pago:</b></label>

                                                 <select required onchange="metodoPago()" id="selectMetodoPago" name="selectMetodoPago" class="form-control form-select form-select-lg">

                                                    <option class="form-control" selected >--------Seleccione------</option>
                                                    <option class="form-control" value="1">EFECTIVO</option>
                                                    <option class="form-control"  value="2">TRANSFERENCIA BANCARIA</option>
                                                    <option class="form-control"  value="2">CHEQUE</option>
                                                 </select>
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Selección banco:</b></label>

                                                 <select required id="selectBanco" name="selectBanco" class="form-control form-select form-select-lg">

                                                 </select>
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 mt-3">
                                                <label for="doc_pago" class="col-form-label focus-label">Documento de Pago:<span class="text-danger">*</span></label>
                                                <input class="form-control"  id="doc_pago" name="doc_pago" type="file" accept="image/png, image/jpeg, image/jpg, application/pdf">
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 mt-3">
                                                <label for="fecha_pago" class="col-form-label focus-label">Fecha que se realizo el pago:<span class="text-danger">*</span></label>
                                                <input class="form-control" required type="date" id="fecha_pago" name="fecha_pago"
                                                    data-parsley-required>
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Nota de pago:</b></label>

                                                <textarea required class="form-control"   id="comentarioAbono" name="comentarioAbono" cols="30" rows="5"></textarea>

                                            </div>

                                        </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <button id="btn_notaabono" class="btn  btn-dark btn-lg btn-block float-left m-t-n-xs">
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
    {{--  FIN DEL MODAL APLICAR CREDITOS/ABONOS  --}}



    {{--  MODAL APLICAR CREDITOS/ABONOS  --}}
    <div class="modal" id="modalcerrarFact" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <h3 class="modal-title">Cerrar factura:</h3>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox ">
                            <div class="ibox-content">
                                <form class="form-control" id="formCierrefact" name="formCierrefact" >
                                <div class="row">
                                        <div class="row">
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Código de Registro:</b></label>
                                                <input required type="text" readonly class="form-control" id="codAplicCierre" name="codAplicCierre" >
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Factura:</b></label>
                                                <input required type="text" readonly class="form-control" id="facturaCaiCierre" name="facturaCaiCierre" >

                                                <input type="hidden" id="idFacturaCierre" name="idFacturaCierre" >
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Nota de cierre:</b></label>

                                                <textarea required class="form-control"   id="comentarioCierre" name="comentarioCierre" cols="30" rows="5"></textarea>

                                            </div>

                                        </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <button id="btn_cierreFact" class="btn  btn-dark btn-lg btn-block float-left m-t-n-xs">
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
    {{--  FIN DEL MODAL APLICAR CREDITOS/ABONOS  --}}






    {{--  TABLA PRINCIPAL DE REGISTRO  --}}
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <h4>Registros de saldos por factura del cliente:</h4>
                            <table id="tbl_cuentas_facturas_cliente" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Codigo Pagos</th>
                                        <th>Cod. Factura</th>
                                        <th>Correlativo</th>
                                        <th>Cargo</th>
                                        <th>Notas Credito</th>
                                        <th>Notas Débito</th>
                                        <th>Créditos/Abonos</th>
                                        <th>Cargo Extra</th>
                                        <th>Cargo A Deducir</th>
                                        <th>ISV</th>
                                        <th>Retencion</th>
                                        <th>Saldo</th>
                                        <th>Fecha registro</th>
                                        <th>Ultima actualizacion</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Codigo Pagos</th>
                                            <th>Cod. Factura</th>
                                            <th>Correlativo</th>
                                            <th>Cargo</th>
                                            <th>Notas Credito</th>
                                            <th>Notas Débito</th>
                                            <th>Créditos/Abonos</th>
                                            <th>Cargo Extra</th>
                                            <th>Cargo Debito</th>
                                            <th>ISV</th>
                                            <th>Retencion</th>
                                            <th>Saldo</th>
                                            <th>Fecha registro</th>
                                            <th>Ultima actualizacion</th>
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
    {{-- FIN TABLA PRINCIPAL DE REGISTRO  --}}

    {{--  TABLA DE OTROS MOVIMIENTOS --}}
    <div class="wrapper wrapper-content animated fadeInRight">
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
    <div class="wrapper wrapper-content animated fadeInRight">
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
<script>

    $('#btnEC').css('display','none');
    $('#btnEC').hide();
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

    function modalOtrosMovimientos(codigoPagoA, caiFactura, idFactura ){
        $('#codAplicPagoom').val(codigoPagoA);
        $('#facturaCaiom').val(caiFactura);
        $('#idFacturaom').val(idFactura);

        $('#modalOtrosMovimientos').modal('show');

    }

    function modalAbonos(codigoPagoA, caiFactura, idFactura){
        $('#codAplicPagoAbono').val(codigoPagoA);
        $('#facturaCaiAbono').val(caiFactura);
        $('#idFacturaAbono').val(idFactura);

        datosBanco();
        $('#modalAbonos').modal('show');
    }

    function llamarTablas(){

        $("#tbl_cuentas_facturas_cliente").dataTable().fnDestroy();
        $("#tbl_tipo_movimientos_cliente").dataTable().fnDestroy();
        $("#tbl_abonos_cliente").dataTable().fnDestroy();


        this.listarCuentasPorCobrar();

        this.listarMovimientos();
        this.listarAbonos()

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
                        var r = $('#tbl_cuentas_facturas_cliente tfoot tr');
                        r.find('th').each(function(){
                          $(this).css('padding', 8);
                        });
                        $('#tbl_cuentas_facturas_cliente thead').append(r);
                        $('#search_0').css('text-align', 'center');
                        this.api()
                            .columns()
                            .every(function () {
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
                        var r = $('#tbl_tipo_movimientos_cliente tfoot tr');
                        r.find('th').each(function(){
                          $(this).css('padding', 8);
                        });
                        $('#tbl_tipo_movimientos_cliente thead').append(r);
                        $('#search_0').css('text-align', 'center');
                        this.api()
                            .columns()
                            .every(function () {
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
                        var r = $('#tbl_abonos_cliente tfoot tr');
                        r.find('th').each(function(){
                          $(this).css('padding', 8);
                        });
                        $('#tbl_abonos_cliente thead').append(r);
                        $('#search_0').css('text-align', 'center');
                        this.api()
                            .columns()
                            .every(function () {
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


        $('#modalAbonos').modal('hide');

        event.preventDefault();
        guardarCreditos();
    });

    function guardarCreditos(){
        var data = new FormData($('#formabonos').get(0));

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

</script>

@endpush
<?php
    date_default_timezone_set('America/Tegucigalpa');
    $act_fecha=date("Y-m-d");
    $act_hora=date("H:i:s");
    $mes=date("m");
    $year=date("Y");
    $datetim=$act_fecha." ".$act_hora;
?>
<script>
    function mostrarHora() {
        var fecha = new Date(); // Obtener la fecha y hora actual
        var hora = fecha.getHours();
        var minutos = fecha.getMinutes();
        var segundos = fecha.getSeconds();

        // A単adir un 0 delante si los minutos o segundos son menores a 10
        minutos = minutos < 10 ? "0" + minutos : minutos;
        segundos = segundos < 10 ? "0" + segundos : segundos;

        // Mostrar la hora actual en el elemento con el id "reloj"
        document.getElementById("reloj").innerHTML = hora + ":" + minutos + ":" + segundos;
    }
    // Actualizar el reloj cada segundo
    setInterval(mostrarHora, 1000);
</script>
<div class="float-right">
    <?php echo "$act_fecha";  ?> <strong id="reloj"></strong>
</div>
<div>
    <strong>Copyright</strong> Distribuciones Valencia &copy; <?php echo "$year";  ?>
</div>
<p id="reloj"></p>
