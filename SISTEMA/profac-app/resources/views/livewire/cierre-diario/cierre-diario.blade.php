<div>
    <style>
        tfoot input {
            width: 100%;
            padding: 3px;
            box-sizing: border-box;
        }
    </style>
    <div class="modal" id="modalCobro" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Seleccione un tipo de pago:</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox ">
                            <div class="ibox-content">
                                <form class="form-control" id="formtipoCobro" name="formtipoCobro" >
                                <div class="row">
                                        <div class="row">
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Fecha de cierre:</b></label>
                                                <input type="text" readonly class="form-control" id="fechaCierreC" name="fechaCierreC" >
                                            </div>
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>No. Factura:</b></label>
                                                <input type="text" readonly class="form-control" id="inputFactura" name="inputFactura" >
                                            </div>
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>No. Factura:</b></label>
                                                <input type="text" readonly class="form-control" id="inputFacturaCodigo" name="inputFacturaCodigo" >
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <label for="exampleFormControlTextarea1"> <b>Seleccione un tipo de cobro</b></label>

                                                 <select id="selectTipoCierre" name="selectTipoCierre" class="form-control form-select form-select-lg">

                                                   <option class="form-control" value="EFECTIVO">EFECTIVO</option>
                                                   <option class="form-control"  value="TRANSFERENCIA BANCARIA">TRANSFERENCIA BANCARIA</option>
                                                   <option class="form-control" value="CHEQUE">CHEQUE</option>
                                                 </select>
                                            </div>
                                        </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <button id="btn_cobroCierre" class="btn  btn-dark btn-lg btn-block float-left m-t-n-xs">
                                            <strong>
                                                Registrar tipo de Cobro
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

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
             <nav aria-label="breadcrumb">


        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a>Módulo Contable</a></li>
          <li class="breadcrumb-item"><a>Cierre de caja</a></li>
          <li class="breadcrumb-item active" aria-current="page">Diario</li>
        </ol>
      </nav>
        </div>

    </div>






    <br>
    <div id="baner1" class="alert alert-secondary" role="alert">
        <h4 class="alert-heading">Cierre de caja.</h4>
        <h2 class="mb-0"> <b>Nota: </b> Se requiere de selección de fecha para mostrar la información.</h2>
    </div>

    <div id="baner2" style="display: none;" class="alert alert-success" role="alert">
        <h4 class="alert-heading">Cierre de caja.</h4>
        <h2 class="mb-0"> Revisión de facturas en la fecha seleccionada.</h2>
    </div>

    <div id="baner3" style="display: none;" class="alert alert-warning" role="alert">
        <h4 class="alert-heading">Cierre de caja.</h4>
        <h2 class="mb-0"> La caja para esta fecha, está cerrada.</h2>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="row">

                            <div class="col-12 col-sm-12 col-md-12">
                                <label for="fecha" class="col-form-label focus-label">Fecha a revisar:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" type="date" id="fecha" name="fecha" value="{{date('Y-m-01')}}">
                                <button class="btn btn-primary btn-lg btn-block" onclick="cargaConsulta()"><i class="fa-solid fa-paper-plane text-white"></i> Solicitar</button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="alert alert-success" role="alert">
            <h5> <b>Nota: </b> FACTURAS DE CONTADO.</h5>

        </div>
        <div class="input-group input-group-lg">
            <div class="input-group-prepend">
              <span class="input-group-text" id="inputGroup-sizing-lg">Total Lps.</span>
            </div>
            <input value="0.00"  style="font-size: 18px" type="text"  id="totalContado" name="totalContado"  disabled class="form-control" aria-label="Large" aria-describedby="inputGroup-sizing-sm">
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_contado" class="table table-striped table-bordered table-hover border border-success">
                                <thead class="">
                                    <tr>
                                       {{--   <th>FECHA</th>
                                        <th>MES</th>  --}}
                                        <th>CODIGO FACTURA</th>
                                        <th>FACTURA</th>
                                        <th>CLIENTE</th>
                                        <th>VENDENDOR</th>
                                        <th>SUBTOTAL</th>
                                        <th>IMPUESTO DE VENTA</th>
                                        <th>TOTAL</th>
                                        <th>TIPO</th>
                                        <th>DOCUMENTO DE PAGO</th>
                                        <th>ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tfoot>
                                        <tr>
                                            {{--  <th>FECHA</th>
                                            <th>MES</th>  --}}
                                            <th>CODIGO FACTURA</th>
                                            <th>FACTURA</th>
                                            <th>CLIENTE</th>
                                            <th>VENDENDOR</th>
                                            <th>SUBTOTAL</th>
                                            <th>IMPUESTO DE VENTA</th>
                                            <th>TOTAL</th>
                                            <th>TIPO</th>
                                            <th>DOCUMENTO DE PAGO</th>
                                            <th>ACCIONES</th>
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
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="alert alert-warning" role="alert">
            <h5> <b>Nota: </b> FACTURAS DE CREDITO.</h5>
        </div>
        <div class="input-group input-group-lg">
            <div class="input-group-prepend">
              <span class="input-group-text" id="inputGroup-sizing-lg">Total Lps.</span>
            </div>
            <input value="0.00"  style="font-size: 18px" type="text"  id="totalCredito" name="totalCredito"  disabled class="form-control" aria-label="Large" aria-describedby="inputGroup-sizing-sm">
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_credito" class="table table-striped table-bordered table-hover border border-warning">
                                <thead class="">
                                    <tr>
                                        {{--  <th>FECHA</th>
                                        <th>MES</th>  --}}
                                        <th>CODIGO FACTURA</th>
                                        <th>FACTURA</th>
                                        <th>CLIENTE</th>
                                        <th>VENDENDOR</th>
                                        <th>SUBTOTAL</th>
                                        <th>IMPUESTO DE VENTA</th>
                                        <th>TOTAL</th>
                                        <th>TIPO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tfoot>
                                        <tr>
                                            {{--  <th>FECHA</th>
                                            <th>MES</th>  --}}
                                            <th>CODIGO FACTURA</th>
                                            <th>FACTURA</th>
                                            <th>CLIENTE</th>
                                            <th>VENDENDOR</th>
                                            <th>SUBTOTAL</th>
                                            <th>IMPUESTO DE VENTA</th>
                                            <th>TOTAL</th>
                                            <th>TIPO</th>
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

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="alert alert-danger" role="alert">
            <h5> <b>Nota: </b> FACTURAS ANULADAS.</h5>
        </div>
        <div class="input-group input-group-lg">
            <div class="input-group-prepend">
              <span class="input-group-text" id="inputGroup-sizing-lg">Total Lps.</span>
            </div>
            <input value="0.00"  style="font-size: 18px" type="text"  id="totalAnuladas" name="totalAnuladas"  disabled class="form-control" aria-label="Large" aria-describedby="inputGroup-sizing-sm">
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_anuladas" class="table table-striped table-bordered table-hover border border-danger">
                                <thead class="">
                                    <tr>
                                        {{--  <th>FECHA</th>
                                        <th>MES</th>  --}}
                                        <th>CODIGO FACTURA</th>
                                        <th>FACTURA</th>
                                        <th>CLIENTE</th>
                                        <th>VENDENDOR</th>
                                        <th>SUBTOTAL</th>
                                        <th>IMPUESTO DE VENTA</th>
                                        <th>TOTAL</th>
                                        <th>TIPO</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                                <tfoot>
                                    <tr>
                                       {{--   <th>FECHA</th>
                                        <th>MES</th>  --}}
                                        <th>CODIGO FACTURA</th>
                                        <th>FACTURA</th>
                                        <th>CLIENTE</th>
                                        <th>VENDENDOR</th>
                                        <th>SUBTOTAL</th>
                                        <th>IMPUESTO DE VENTA</th>
                                        <th>TOTAL</th>
                                        <th>TIPO</th>
                                    </tr>
                                </tfoot>
                            </table>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="wrapper wrapper-content animated fadeInRight" id="divcierre">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3>Datos extras de cierre de caja <i class="fa-solid fa-cart-shopping"></i></h3>
                    </div>
                    <div class="ibox-content">
                        <form class="form-control" id="cerrarCaja" name="cerrarCaja" >
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                <div class="form-group">
                                    <label for="exampleFormControlTextarea1"> <b>Detalle un comentario sobre el cierre</b></label>
                                    <textarea class="form-control" id="comentario" name="comentario" rows="3"></textarea>
                                </div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                <div class="row">
                                    <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
                                        <label for="exampleFormControlTextarea1"> <b>Total Facturas de Contado</b></label>
                                        <input type="text" readonly class="form-control" id="inputTotalContado" name="inputTotalContado" >
                                    </div>

                                    <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
                                        <label for="exampleFormControlTextarea1"> <b>Total Facturas de Credito</b></label>
                                        <input type="text" readonly class="form-control" id="inputTotalCredito" name="inputTotalCredito" >
                                    </div>

                                    <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
                                        <label for="exampleFormControlTextarea1" style="font-size: 14px;"> <b>Total Facturado</b></label>
                                        <input type="text" readonly class="form-control" id="inputTotalAnulado" name="inputTotalAnulado" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                <button id="btn_cierreCaja" class="btn  btn-dark btn-lg btn-block float-left m-t-n-xs"><strong>Realizar Cierre</strong></button>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>
@push('scripts')
    <script src="{{ asset('js/js_proyecto/cierre-diario/cierre-diario.js') }}"></script>
@endpush

