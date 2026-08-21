<div>
    @push('styles')
        <style>
            :root { --tr-orange:#e65100; --tr-amber:#f9a826; --tr-green:#087f5b; --tr-border:#e4e9ef; }
            .tr-card { overflow:hidden; border:1px solid var(--tr-border); border-radius:10px; background:#fff; box-shadow:0 5px 18px rgba(41,55,66,.08); }
            .tr-card-head { padding:14px 18px; background:linear-gradient(135deg,var(--tr-orange),var(--tr-amber)); color:#fff; }
            .tr-card-head h3 { margin:0; color:#fff; font-size:16px; font-weight:800; }
            .tr-card-head small { color:rgba(255,255,255,.86); }
            .tr-card-body { padding:18px; }
            .tr-product-picker { display:flex; align-items:end; gap:10px; width:100%; }
            .tr-product-picker > div { flex:1 1 auto; min-width:0; }
            .tr-product-field { min-height:40px; border:1.5px solid #dbe2e8; border-radius:7px; background:#f8fafb; font-weight:700; }
            button.tr-btn-search { display:inline-flex; flex:0 0 auto; align-items:center; justify-content:center; min-height:40px; padding:0 18px; white-space:nowrap; border:1px solid var(--tr-green)!important; border-radius:7px; background:var(--tr-green)!important; color:#fff!important; font-weight:800; }
            button.tr-btn-search:hover, button.tr-btn-search:focus, button.tr-btn-search:active { border-color:#066b4d!important; background:#066b4d!important; color:#fff!important; }
            .tr-location-note { margin:14px 0 10px; padding:9px 12px; border-left:4px solid var(--tr-green); border-radius:5px; background:#e8f5ef; color:#285c4b; font-size:12px; }
            .tr-table thead th { border-color:#dfe7e3; background:#edf7f2; color:#17664d; font-size:10px; text-transform:uppercase; letter-spacing:.3px; }
            .tr-table td { vertical-align:middle; color:#455a64; font-size:12px; }
            .tr-modal .modal-content { overflow:hidden; border:0; border-radius:10px; box-shadow:0 18px 45px rgba(31,44,54,.25); }
            .tr-modal .modal-header { border:0; background:linear-gradient(135deg,var(--tr-orange),var(--tr-amber)); color:#fff; }
            .tr-modal .modal-title, .tr-modal .close { color:#fff; }
            @media(max-width:767px){ .tr-product-picker{flex-direction:column;align-items:stretch}.tr-btn-search{width:100%} }
        </style>
    @endpush
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Translado en Bodega</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a>Translado de Producto</a>
                </li>
                {{-- <li class="breadcrumb-item">
                    <a data-toggle="modal" data-target="#modal_producto_crear">Registrar</a>
                </li> --}}

            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="tr-card">
                    <div class="tr-card-head">
                        <h3><i class="fa fa-search mr-2"></i>Seleccionar producto</h3>
                        <small>Busque el producto y seleccione la ubicación de origen del traslado.</small>
                    </div>
                    <div class="tr-card-body">
                        <form id="selec_data_form" name="selec_data_form" data-parsley-validate>
                            <label for="productoTraslado_nombre">Producto <span class="text-danger">*</span></label>
                            <div class="tr-product-picker">
                                <div>
                                        <input type="text" id="productoTraslado_nombre" class="form-control tr-product-field"
                                               placeholder="Escriba nombre, código o ID" autocomplete="off" data-parsley-required
                                               oninput="prepararNuevaBusquedaProductoTraslado(this.value)"
                                               onkeydown="if(event.key==='Enter'){event.preventDefault();buscarPorCodigoTraslado(this.value);return false;}">
                                        <input type="hidden" id="selectProducto" value="">
                                </div>
                                <button type="button" class="tr-btn-search" onclick="abrirBuscadorProductoTraslado()"><i class="fa fa-search mr-1"></i>Buscar producto</button>
                                </div>
                        </form>

                        <div id="ubicaciones_traslado_panel" style="display:none;">
                            <div class="tr-location-note"><i class="fa fa-warehouse mr-1"></i><span id="producto_ubicaciones_traslado_texto"></span> Seleccione la ubicación de origen.</div>
                            <h4 class="mb-2" style="color:#087f5b;font-size:14px;"><span id="total"></span></h4>
                            <div class="table-responsive">
                            <table id="tbl_translados" class="table table-bordered table-hover tr-table">
                                <thead class="">
                                    <tr>
                                        <th>Cod Producto</th>
                                        <th>Nombre</th>
                                        <th>Unidad de Medida</th>
                                        <th>Cantidad Disponible</th>
                                        <th>Bodega</th>
                                        <th>Sección</th>
                                        <th>Fecha Ingreso</th>
                                        <th>Acción</th>



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
    </div>




    <!-- Modal para transferir producto a otra bodega-->
    <div class="modal fade tr-modal" id="modal_transladar_producto" tabindex="-1" role="dialog"
        aria-labelledby="modal_transladar_productoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="modal_transladar_productoLabel"> Transladar a otra bodega </h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <form id="recibirProducto" data-parsley-validate>


                                <div class="form-group">
                                    <label for="bodega">Bodega</label>
                                    <select class="form-control m-b" name="bodega" id="bodega"
                                        onchange="listarSegmentos()" required data-parsley-required>
                                        <option value="" selected disabled>---Seleccione una bodega de destino---
                                        </option>

                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="segmento">Segmento</label>
                                    <select class="form-control m-b" name="segmento" id="segmento" required
                                        data-parsley-required onchange="listarSecciones()">
                                        <option value="" selected disabled>---Seleccione un segmento de destino---
                                        </option>

                                    </select>
                                </div>



                                <div class="form-group">
                                    <label for="seccion">Seccion</label>
                                    <select class="form-control m-b" name="seccion" id="seccion" required
                                        data-parsley-required="">
                                        <option value="" selected disabled>---Seleccione una sección de destino---
                                        </option>

                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="comentario">Cantidad a transladar</label>


                                    <input id="cantidad" name="cantidad" type="number" min="1"
                                        class="form-control" required data-parsley-required>

                                </div>

                                <div class="form-group">
                                    <label for="seccion">Unidad de medida</label>
                                    <select class="form-control m-b" name="Umedida" id="Umedida" required
                                        data-parsley-required="">
                                        <option value="" selected disabled>---Seleccione una Unidad de medida---
                                        </option>

                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="comentario_item">Comentario (opcional)</label>
                                    <textarea id="comentario_item" name="comentario_item" class="form-control"
                                        rows="2" placeholder="Ingrese un comentario (opcional)"></textarea>
                                </div>

                                <input id="idProducto" type="hidden" value="">

                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button id="btn_recibir_bodega" type="submit" form="recibirProducto"
                        class="btn btn-primary">Agregar a lista</button>
                </div>
            </div>
        </div>
    </div>


    <!--Tabla para productos a transladar-->
    <div id="lista_translado" class="">
        <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="ibox ">
                            <div class="ibox-title">
                                <h3>Listado de productos a transaladar</h3>
                            </div>
                             <div class="ibox-content">
                                <div class="table-responsive">
                                    <form onkeydown="return event.key != 'Enter';" autocomplete="off" id="guardar_translados"   name="guardar_translados" data-parsley-validate>

                                    </form>
                                    <table id="tbl_translados_productos"
                                        class="table table-striped table-bordered table-hover">
                                        <thead class="">
                                            <tr>
                                                <th>Eliminar</th>
                                                <th>Cod Producto</th>
                                                <th>Bodega</th>
                                                <th>Segmento</th>
                                                <th>Seccion</th>
                                                <th>Cantidad</th>
                                                <th>Uniad de medida</th>
                                                <th>Comentario</th>
                                            </tr>
                                        </thead>

                                        <tbody id="cuerpoListaProducto">



                                        </tbody>

                                    </table>

                                    <button id="btn_guardar_translado" type="button" onclick="abrirModalMotivo()"   class="btn btn-primary btn-lg mb-4 mt-3" >Guardar Translado</button>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="destino" class="d-none">
        <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h3>Listado De Producto En Bodega De Destino</h3>
                        </div>
                        <div class="ibox-content">
                            <div class="table-responsive">
                                <table id="tbl_translados_destino"
                                    class="table table-striped table-bordered table-hover">
                                    <thead class="">
                                        <tr>
                                            <th>Cod Producto</th>
                                            <th>Nombre</th>
                                            <th>Unidad de Medida</th>
                                            <th>Cantidad Disponible</th>
                                            <th>Bodega</th>
                                            <th>Sección</th>
                                            <th>Fecha Ingreso</th>




                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                                <button onclick="limpiar()" type="button" class="btn btn-warning btn-lg mb-4 mt-3" >Limpiar</button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Buscador de productos con existencia --}}
    <x-buscador-producto id-modal="buscadorProductoTraslados"
                         callback="alSeleccionarProductoTraslado"
                         url-buscar="/productos/buscar"
                         url-top="/productos/buscar/top-vendidos"
                         url-filtros="/productos/buscar"
                         top-label="Productos con existencia" />

    <!-- Modal para motivo del traslado (obligatorio al guardar) -->
    <div class="modal fade" id="modal_motivo_traslado" tabindex="-1" role="dialog"
        aria-labelledby="modal_motivo_trasladoLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="modal_motivo_trasladoLabel">Motivo del Traslado</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="motivo_traslado">
                            Motivo del traslado <span class="text-danger">*</span>
                        </label>
                        <textarea id="motivo_traslado" class="form-control" rows="3"
                            placeholder="Ingrese el motivo del traslado..."></textarea>
                        <small id="motivo_error" class="text-danger d-none">El motivo del traslado es obligatorio.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button id="btn_confirmar_traslado" type="button" class="btn btn-primary">Confirmar Traslado</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Impresión de Traslado -->
    <div class="modal fade" id="modal_imprimir_traslado" tabindex="-1" role="dialog"
        aria-labelledby="modal_imprimir_traslado" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white border-bottom-0">
                    <div>
                        <h4 class="modal-title mb-0">
                            <i class="fa fa-check-circle"></i> Traslado Registrado
                        </h4>
                        <p class="modal-text-small text-light mb-0 mt-1" style="font-size: 0.9rem;">
                            El traslado ha sido guardado exitosamente en el sistema
                        </p>
                    </div>
                    <button type="button" class="close text-white" aria-label="Close" onclick="cerrarModalImpresionTraslado()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="background-color: #f8f9fa;">
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-dark mb-3">
                                <i class="fa fa-print text-primary"></i>
                                <strong>Opciones de impresión</strong>
                            </h5>
                            <p class="text-muted small">
                                Se abrirá una nueva ventana con el documento listo para imprimir.
                            </p>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-12 col-sm-6 mb-3">
                            <button type="button" class="btn btn-primary btn-block py-3" onclick="imprimirTrasladoPDF()">
                                <div>
                                    <i class="fa fa-print fa-lg"></i>
                                </div>
                                <div style="margin-top: 8px;">
                                    <strong>Imprimir Traslado</strong>
                                    <br>
                                    <small class="text-muted">Abrir PDF en nueva pestaña</small>
                                </div>
                            </button>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-info border-left-4" style="border-left: 4px solid #17a2b8;">
                                <i class="fa fa-info-circle"></i>
                                <strong>Información:</strong>
                                Se abrirá el PDF en una nueva ventana del navegador.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalImpresionTraslado()">
                        <i class="fa fa-times"></i> Cerrar
                    </button>
                    <button type="button" class="btn btn-success" onclick="cerrarModalImpresionTraslado()">
                        <i class="fa fa-check"></i> Finalizar
                    </button>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script src="{{ asset('js/js_proyecto/inventario/traslados.js') }}"></script>
@endpush

</div>
