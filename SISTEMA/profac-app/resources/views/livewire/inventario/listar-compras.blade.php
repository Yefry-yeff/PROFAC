<div>
    @push('styles')
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Listado de Compras</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <a>Listado</a>
                </li>

                <li class="breadcrumb-item">
                    <a>Detalle de compra</a>
                </li>
                <li class="breadcrumb-item">
                    <a>Recepción de producto</a>
                </li>
            </ol>
        </div>

        <div class="col-lg-4 col-xl-2 col-md-4 col-sm-4">
                <div style="margin-top: 1.5rem">
                    <a href="#" class="btn add-btn btn-primary" data-toggle="modal" data-target="#modal_exportar_excel"><i class="fa fa-plus "></i> Exportar Excel</a>
                </div>
        </div>

    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_listar_compras" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Codigo Interno</th>
                                        <th>N° Compra</th>
                                        <th>N° Factura</th>
                                        <th>Emisión</th>
                                        <th>Fecha de Vencimiento</th>
                                        <th>Total de compra</th>
                                        <th>Retencion</th>
                                        <th>Proveedor</th>
                                        <th>Registrado por</th>
                                        <th>Fecha de Registro</th>
                                        <th>Anular</th>
                                        <th>Opciones</th>

                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Elegir Mes a Exportar-->
    <div class="modal fade" id="modal_exportar_excel" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="exampleModalLabel">Exportar Mes a Excel</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="exportarExcelForm" name="exportarExcelForm" data-parsley-validate>
                        {{-- <input type="hidden" name="_token" value="{!! csrf_token() !!}"> --}}

                        <div class="row" id="row_datos">

                            <div class="col-md-12">
                                <label for="compras_mes" class="col-form-label focus-label">Seleccione el Mes:<span class="text-danger">*</span></label>
                                <input required type="month" id="compras_mes" name="compras_mes"  data-parsley-required onchange="insertarBotonExportar()">
                            </div>
                            <div class="col-md-12">
                                <div class="d-flex justify-content-center mt-2" id="exportar">
                                <!--///////////////////////Aqui se insertará el boton cuando se selecione un Mes///////////////////-->
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <!--<button type="button" form="exportarExcelForm" class="btn btn-primary excel_compras_mes">Exportar
                        Excel</button>-->
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/js_proyecto/inventario/listar-compras.js') }}"></script>
    @endpush
</div>
