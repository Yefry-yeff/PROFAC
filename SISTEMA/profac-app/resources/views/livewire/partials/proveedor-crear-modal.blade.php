<div class="proveedores-view">
    <div id="modal_proveedores_crear" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="tituloCrearProveedor" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="tituloCrearProveedor" class="modal-title"><i class="fa fa-truck mr-2"></i>Registrar proveedor</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="proveedorCreacionForm" name="proveedorCreacionForm" data-parsley-validate>
                        <input type="hidden" name="_token" value="{!! csrf_token() !!}">
                        @include('livewire.partials.proveedor-form', ['editar' => false])
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" form="proveedorCreacionForm"><i class="fa fa-check mr-1"></i>Crear proveedor</button>
                </div>
            </div>
        </div>
    </div>
</div>
