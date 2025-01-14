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
            <h2>Reporte de facturación</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.html">/ Consulta</a>
                </li>


            </ol>
        </div>

    </div>


    <p> <b>Nota: </b> Se requiere de selección de un rango de fechas para mostrar la información.</p>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="row">


                            <div class="col-6 col-sm-6 col-md-6 ">
                                <label for="fecha_inicio" class="col-form-label focus-label">Fecha de inicio:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" type="date" id="fecha_inicio" name="fecha_inicio" value="{{date('Y-m-01')}}">
                            </div>

                            <div class="col-6 col-sm-6 col-md-6">
                                <label for="fecha_final" class="col-form-label focus-label">Fecha final:<span class="text-danger">*</span></label>
                                <input class="form-group form-control" type="date" id="fecha_final" name="fecha_final" value="{{date('Y-m-t')}}">
                            </div>

                        </div>
                        <button class="btn btn-primary" onclick="cargaConsulta()"><i class="fa-solid fa-paper-plane text-white"></i> Solicitar</button>
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
                            <table id="tbl_facdia" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>FECHA</th>
                                        <th>MES</th>
                                        <th>FACTURA</th>
                                        <th>CLIENTE</th>
                                        <th>VENDENDOR</th>
                                        <th>SUBTOTAL</th>
                                        <th>IMPUESTO DE VENTA</th>
                                        <th>TOTAL</th>
                                        <th>TIPO</th>
                                    </tr>
                                </thead>
                                <tbody>                                <tbody>
                                    <tfoot>
                                        <tr>
                                            <th>FECHA</th>
                                        <th>MES</th>
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


<script src="{{ asset('js/js_proyecto/factura-dia/factura-dia.js') }}"></script>
@endpush
