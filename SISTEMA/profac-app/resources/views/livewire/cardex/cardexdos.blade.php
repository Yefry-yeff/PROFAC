<div>
    <style>
        tfoot input {
            width: 100%;
            padding: 3px;
            box-sizing: border-box;
        }
    </style>
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <h2>Cardex versión 2</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.html">Lista</a>
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
                                <label for="seleccionarBodega" class="col-form-label focus-label">Seleccionar Bodega:<span class="text-danger">*</span></label>
                                <select id="bodega" name="bodega" class="form-group form-control" style=""
                                    data-parsley-required onchange="obtenerIdBodega()">
                                    <option value="" selected disabled>--Seleccionar una Bodega--</option>
                                </select>
                            </div>

                            <div class="col-6 col-sm-6 col-md-6">
                                <label for="seleccionarProducto" class="col-form-label focus-label">Seleccionar Producto:<span class="text-danger">*</span></label>
                                <select id="producto" name="producto" class="form-group form-control" style=""
                                    data-parsley-required >
                                    <option value="" selected disabled>--Seleccionar una Producto--</option>
                                </select>
                            </div>

                        </div>
                        <button class="btn btn-primary" onclick="cargaCardex()"><i class="fa-solid fa-paper-plane text-white"></i> Solicitar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_cardex" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Fecha de gestion</th>
                                        <th>Producto</th>
                                        <th>Codigo de producto</th>
                                       <th>Factura</th>
                                       <th>Ajuste</th>
                                       <th>Compra</th>
                                        <th>Comprobante de entrega</th>
                                        {{--  <th>Vale Tipo 1</th>  --}}
                                         <th>Vale Tipo 2</th>
                                        <th>Nota de credito</th>
                                        <th>Descripcion</th>
                                        <th>Origen</th>
                                        <th>Destino</th>
                                        <th>Cantidad</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Fecha de gestion</th>
                                            <th>Producto</th>
                                            <th>Codigo de producto</th>
                                           <th>Factura</th>
                                           <th>Ajuste</th>
                                            <th>Compra</th>
                                            <th>Comprobante de entrega</th>
                                            {{--  <th>Vale Tipo 1</th>  --}}
                                            <th>Vale Tipo 2</th>
                                            <th>Nota de credito</th>
                                            <th>Descripcion</th>
                                            <th>Origen</th>
                                            <th>Destino</th>
                                            <th>Cantidad</th>
                                            <th>Usuario</th>
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




</div>
@push('scripts')

    <script src="{{ asset('js/js_proyecto/cardex/cardexDos.js') }}"></script>

@endpush
