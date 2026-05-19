<div>
    @push('styles')
    <style>
        :root {
            --pf-grad:   linear-gradient(135deg, #f39c12 0%, #e05a00 100%);
            --pf-orange: #e67e22;
            --pf-radius: 8px;
            --pf-shadow: 0 2px 8px rgba(0,0,0,.10);
        }
        .cdx-card {
            border: 1px solid #e8d5bf;
            border-radius: var(--pf-radius);
            box-shadow: var(--pf-shadow);
            background: #fff;
            overflow: visible;
        }
        .cdx-card-header {
            background: var(--pf-grad);
            padding: 10px 18px;
            border-radius: var(--pf-radius) var(--pf-radius) 0 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .cdx-card-header h5 {
            margin: 0; color: #fff;
            font-size: .85rem; font-weight: 700;
            letter-spacing: .05em; text-transform: uppercase;
            display: flex; align-items: center; gap: 8px;
        }
        .cdx-card-body { padding: 14px 18px; }
        #tbl_boletas thead th {
            background: #fdf4e7; color: #7d3f00;
            font-size: .70rem; font-weight: 700;
            letter-spacing: .04em; text-transform: uppercase;
            border-bottom: 2px solid #f2d49a;
            white-space: nowrap; padding: 7px 8px; vertical-align: middle;
        }
        #tbl_boletas tbody td {
            font-size: .80rem; vertical-align: middle; padding: 6px 8px;
        }
        #tbl_boletas tbody tr:hover { background: #fffcf5; }
        .swal2-container { z-index: 2000 !important; }
    </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12">
            <h2><i class="fa fa-shopping-cart mr-2" style="color:#e67e22"></i>Historial de Boletas de Compra</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a>Boleta de Compra</a></li>
                <li class="breadcrumb-item active"><strong>Historial</strong></li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="cdx-card">
                    <div class="cdx-card-header">
                        <h5><i class="fa fa-filter"></i> Filtrar Boletas</h5>
                    </div>
                    <div class="cdx-card-body">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold small">Fecha Inicio:</label>
                                    <input type="date" id="fechaInicio" class="form-control"
                                           value="{{ $fechaInicio }}">
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold small">Fecha Final:</label>
                                    <input type="date" id="fechaFinal" class="form-control"
                                           value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-3 d-flex align-items-end">
                                <button type="button" class="btn btn-primary mb-3" onclick="buscarBoletas()">
                                    <i class="fa fa-search mr-1"></i> Buscar
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
                <div class="cdx-card">
                    <div class="cdx-card-header">
                        <h5><i class="fa fa-list"></i> Listado de Boletas de Compra</h5>
                    </div>
                    <div class="cdx-card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tbl_boletas">
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
                pageLength: 5,
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
