<div>
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12">
            <h2>Editar Boleta de Compra</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/boleta/compra/historial">Boleta de Compra</a></li>
                <li class="breadcrumb-item active"><strong>Editar Boleta</strong></li>
            </ol>
        </div>
    </div>

    {{-- Datos generales --}}
    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <h3>Datos de la Boleta &mdash; <span class="text-primary">{{ $boleta->numero_boleta }}</span></h3>
                    </div>
                    <div class="ibox-content">
                        <form id="form_boleta" name="form_boleta" autocomplete="off" onkeydown="return event.key != 'Enter';">

                            <input type="hidden" id="bc_id" value="{{ $boleta->id }}">

                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-5">
                                    <div class="form-group">
                                        <label for="bc_cliente">Cliente:<span class="text-danger">*</span></label>
                                        <input type="text" id="bc_cliente" name="bc_cliente" class="form-control"
                                               placeholder="Nombre del cliente" required maxlength="255"
                                               value="{{ $boleta->cliente }}">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-12 col-md-5">
                                    <div class="form-group">
                                        <label for="bc_direccion">Dirección:</label>
                                        <input type="text" id="bc_direccion" name="bc_direccion" class="form-control"
                                               placeholder="Dirección del cliente" maxlength="500"
                                               value="{{ $boleta->direccion }}">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-12 col-md-2">
                                    <div class="form-group">
                                        <label for="bc_fecha">Fecha:<span class="text-danger">*</span></label>
                                        <input type="date" id="bc_fecha" name="bc_fecha" class="form-control" required
                                               value="{{ $boleta->fecha }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-12 col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label for="bc_rtn_dni">RTN / DNI: <small class="text-muted">(opcional)</small></label>
                                        <input type="text" id="bc_rtn_dni" name="bc_rtn_dni" class="form-control"
                                               placeholder="RTN o DNI del cliente" maxlength="50"
                                               value="{{ $boleta->rtn_dni }}">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label for="bc_telefono">Teléfono: <small class="text-muted">(opcional)</small></label>
                                        <input type="text" id="bc_telefono" name="bc_telefono" class="form-control"
                                               placeholder="Teléfono del cliente" maxlength="50"
                                               value="{{ $boleta->telefono }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="bc_comentario">Comentario general: <small class="text-muted">(opcional)</small></label>
                                        <textarea id="bc_comentario" name="bc_comentario" class="form-control" rows="3"
                                                  placeholder="Observaciones o comentarios generales de la boleta...">{{ $boleta->comentario }}</textarea>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Conceptos de Compra --}}
    <div class="wrapper wrapper-content animated fadeInRight pb-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <h3>Conceptos de Compra</h3>
                        <div class="ibox-tools">
                            <button type="button" class="btn btn-primary btn-sm" onclick="agregarConcepto()">
                                <i class="fa fa-plus"></i> Agregar Concepto
                            </button>
                        </div>
                    </div>
                    <div class="ibox-content">

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="tbl_conceptos">
                                <thead>
                                    <tr>
                                        <th style="width:50px;">#</th>
                                        <th>Descripción</th>
                                        <th style="width:150px;">Precio (L.)</th>
                                        <th style="width:130px;">Cantidad</th>
                                        <th style="width:150px;">Importe (L.)</th>
                                        <th style="width:80px;">Eliminar</th>
                                    </tr>
                                </thead>
                                <tbody id="cuerpo_conceptos">
                                    <tr id="fila_vacia" style="display:none;">
                                        <td colspan="6" class="text-center text-muted">
                                            <i class="fa fa-info-circle"></i> Haga clic en "Agregar Concepto" para comenzar.
                                        </td>
                                    </tr>
                                    @foreach($detalles as $detalle)
                                    <tr id="fila_concepto_{{ $loop->iteration }}">
                                        <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm desc_concepto"
                                                id="desc_{{ $loop->iteration }}"
                                                placeholder="Descripción del concepto" maxlength="500" required
                                                value="{{ $detalle->descripcion }}">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm precio_concepto"
                                                id="precio_{{ $loop->iteration }}"
                                                placeholder="0.00" step="0.01" min="0"
                                                oninput="calcularImporte({{ $loop->iteration }})"
                                                value="{{ $detalle->precio }}">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm cantidad_concepto"
                                                id="cantidad_{{ $loop->iteration }}"
                                                placeholder="0" step="0.01" min="0"
                                                oninput="calcularImporte({{ $loop->iteration }})"
                                                value="{{ $detalle->cantidad }}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm importe_concepto"
                                                id="importe_{{ $loop->iteration }}" value="{{ $detalle->importe }}" readonly
                                                style="background-color:#f8f9fa; font-weight:bold;">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="eliminarConcepto({{ $loop->iteration }})">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <hr>
                        <div class="row">
                            <div class="col-12 col-md-6 offset-md-6">
                                <div class="row">
                                    <div class="col-6 text-right"><strong>Total (L.):</strong></div>
                                    <div class="col-6">
                                        <input type="text" id="bc_total_mostrar" class="form-control font-weight-bold"
                                               value="{{ number_format($boleta->total, 2) }}" readonly>
                                        <input type="hidden" id="bc_total" name="bc_total" value="{{ $boleta->total }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <br>
                        <button type="button" id="btn_guardar_boleta" class="btn btn-success"
                                onclick="guardarEdicionBoleta()">
                            <i class="fa fa-save"></i> Guardar Cambios
                        </button>
                        <a href="/boleta/compra/historial" class="btn btn-secondary ml-2">
                            <i class="fa fa-arrow-left"></i> Volver al Historial
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Impresión --}}
    <div class="modal fade" id="modal_imprimir_boleta" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white border-bottom-0">
                    <div>
                        <h4 class="modal-title mb-0">
                            <i class="fa fa-check-circle"></i> Boleta de Compra Actualizada
                        </h4>
                        <p class="text-light mb-0 mt-1" style="font-size: 0.9rem;">
                            Los cambios han sido guardados exitosamente.
                        </p>
                    </div>
                    <button type="button" class="close text-white" aria-label="Close"
                            onclick="$('#modal_imprimir_boleta').modal('hide')">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="background-color: #f8f9fa;">
                    <div class="row mb-3">
                        <div class="col-12">
                            <h5 class="text-dark mb-2">
                                <i class="fa fa-print text-primary"></i>
                                <strong>Seleccione las opciones de impresión</strong>
                            </h5>
                            <p class="text-muted small">Se abrirá una nueva ventana con el documento listo para imprimir.</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-6 mb-3">
                            <button type="button" class="btn btn-primary btn-block py-3"
                                    onclick="imprimirBoletaEdit('original')">
                                <i class="fa fa-print fa-lg d-block mb-2"></i>
                                <strong>Imprimir Original</strong><br>
                                <small>Copia oficial</small>
                            </button>
                        </div>
                        <div class="col-12 col-sm-6 mb-3">
                            <button type="button" class="btn btn-info btn-block py-3"
                                    onclick="imprimirBoletaEdit('copia')">
                                <i class="fa fa-copy fa-lg d-block mb-2"></i>
                                <strong>Imprimir Copia</strong><br>
                                <small>Copia de archivo</small>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <a href="/boleta/compra/historial" class="btn btn-secondary">
                        <i class="fa fa-times"></i> Cerrar
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/js_proyecto/boleta-compra/editar-boleta-compra.js') }}"></script>
    <style>
        .modal.show { display: block !important; background-color: rgba(0,0,0,0.5); }
        .swal2-container { z-index: 2000 !important; }
    </style>
    <script>
        // Inicializar contador al número de filas ya cargadas
        contadorConceptos = {{ count($detalles) }};
        actualizarTotal();
    </script>
    @endpush
</div>
