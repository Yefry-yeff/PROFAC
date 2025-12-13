<div>
    @push('styles')
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2> Comprobantes De Entrega </h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <a>Activos</a>
                </li>
                <li class="breadcrumb-item active">
                    <a>Listado</a>
                </li>


            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_listar_comprobantes" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>

                                        <th>N° Comprobante</th>
                                        <th>Cliente</th>
                                        <th>RTN</th>
                                        <th>Fecha de Emision</th>
                                        <th>Sub Total Lps.</th>
                                        <th>ISV en Lps.</th>
                                        <th>Total en Lps.</th>
                                        <th>Estado</th>
                                        <th>Registrado Por:</th>
                                        <th>Fecha de registro</th>
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

    @push('scripts')
    <script src="{{ asset('js/js_proyecto/comprobante-entrega/listar-comprobantes.js') }}"></script>

    @endpush
</div>
