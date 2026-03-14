<div>
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12">
            <h2>Historial de Boletas de Compra</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a>Boleta de Compra</a></li>
                <li class="breadcrumb-item active"><strong>Historial</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title"><h3>Filtrar Boletas</h3></div>
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Fecha Inicio:</label>
                                    <input type="date" id="fechaInicio" class="form-control"
                                           value="{{ $fechaInicio }}">
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Fecha Final:</label>
                                    <input type="date" id="fechaFinal" class="form-control"
                                           value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-3 d-flex align-items-end">
                                <button type="button" class="btn btn-primary mb-3" onclick="buscarBoletas()">
                                    <i class="fa fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title"><h3>Listado de Boletas de Compra</h3></div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="tbl_boletas">
                                <thead>
                                    <tr>
                                        <th>No. Boleta</th>
                                        <th>Cliente</th>
                                        <th>Dirección</th>
                                        <th>Fecha</th>
                                        <th>Total (L.)</th>
                                        <th>Registrado por</th>
                                        <th>Opciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .swal2-container { z-index: 2000 !important; }
    </style>
    <script>
        var tablaBoletas = null;

        function buscarBoletas() {
            var fechaInicio = $('#fechaInicio').val();
            var fechaFinal  = $('#fechaFinal').val();

            if (!fechaInicio || !fechaFinal) {
                Swal.fire('Atención', 'Por favor seleccione un rango de fechas.', 'warning');
                return;
            }

            if (tablaBoletas) {
                tablaBoletas.destroy();
            }

            tablaBoletas = $('#tbl_boletas').DataTable({
                language: { url: '/js/plugins/dataTables/i18n/Spanish.json' },
                pageLength: 25,
                responsive: true,
                ajax: {
                    url: '/boleta/compra/listar',
                    type: 'POST',
                    data: {
                        fechaInicio: fechaInicio,
                        fechaFinal:  fechaFinal,
                        _token: '{{ csrf_token() }}'
                    }
                },
                columns: [
                    { data: 'numero_boleta' },
                    { data: 'cliente' },
                    { data: 'direccion' },
                    { data: 'fecha' },
                    { data: 'total' },
                    { data: 'registrado_por' },
                    { data: 'opciones', orderable: false, searchable: false }
                ]
            });
        }

        function anularBoleta(id) {
            Swal.fire({
                title: '¿Anular boleta?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, anular',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed) {
                    axios.post('/boleta/compra/anular', {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    }).then(function(response) {
                        Swal.fire('Anulada', 'La boleta fue anulada correctamente.', 'success');
                        buscarBoletas();
                    }).catch(function(error) {
                        Swal.fire('Error', 'No se pudo anular la boleta.', 'error');
                    });
                }
            });
        }

        $(document).ready(function() {
            buscarBoletas();
        });
    </script>
    @endpush
</div>
