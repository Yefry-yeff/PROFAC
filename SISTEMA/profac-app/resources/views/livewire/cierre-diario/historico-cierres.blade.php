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
            <h2>Cierre de caja</h2>

            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a >Histórico de cierre de caja diaria</a>
                </li>


            </ol>
        </div>
       {{--   /cajaChica/excel/general  --}}
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="alert alert-info" role="alert">
            <h5> <b>Nota: Se enlista los cierres de cajas realizados, con su respectivo reporte independiente.</h5>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">

                        <div class="table-responsive">
                            <table id="tbl_bitacoracierre" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Cod Cierre</th>
                                        <th>Dia Cerrado</th>
                                        <th>Usuario</th>
                                        <th>Comentario</th>
                                        <th>Estado de cierre</th>
                                        <th>Monto Total Contado</th>
                                        <th>Monto Total Credito</th>
                                        <th>Monto Total Acumulado</th>
                                        <th>Fecha del registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Cod Cierre</th>
                                            <th>Dia Cerrado</th>
                                            <th>Usuario</th>
                                            <th>Comentario</th>
                                            <th>Estado de cierre</th>
                                            <th>Monto Total Contado</th>
                                            <th>Monto Total Credito</th>
                                            <th>Monto Total Acumulado</th>
                                            <th>Fecha del registro</th>
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
</div>

@push('scripts')


    <script src="{{ asset('js/js_proyecto/cierre-diario/historico-cierres.js') }}"></script>



@endpush
