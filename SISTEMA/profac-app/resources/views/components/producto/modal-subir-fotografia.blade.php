@if($incluirSpinner ?? false)
<div class="modal" id="modalSpinnerLoading" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:300px;">
        <div class="modal-content" style="background:transparent;border:none;box-shadow:none;">
            <div class="modal-body" style="padding:0;">
                <div class="spinner-overlay-box">
                    <div class="spinner-ring"></div>
                    <p>Procesando...</p>
                    <small>Por favor espere</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="modal fade modal-modern producto-foto-modal" id="modal_foto_producto" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-camera mr-2"></i> Subir Fotografía</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="foto_productoForm" name="foto_productoForm">
                    <input type="hidden" id="id_producto_edit_foto" name="id_producto_edit_foto" value="{{ $productoId ?? '' }}">
                    <label for="foto_producto_edit" class="foto-drop-area w-100" style="cursor:pointer;margin:0;">
                        <i class="fa fa-cloud-upload"></i>
                        <span>Haz clic para seleccionar imágenes (máx. 10)<br><small style="color:#aaa;">PNG, JPG, GIF</small></span>
                        <input type="file" id="foto_producto_edit" name="foto_producto_edit"
                            accept="image/png,image/gif,image/jpeg" multiple style="display:none;">
                    </label>
                    <div id="previewContainer" style="display:none;margin-top:16px;">
                        <p style="font-size:.78rem;color:#888;margin-bottom:10px;">
                            <i class="fa fa-check-circle" style="color:#1abc9c;"></i>&nbsp;
                            <span id="previewCount">0</span> imagen(es) seleccionada(s)
                        </p>
                        <div id="previewGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:8px;"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="gap:8px;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius:8px;">Cancelar</button>
                <button type="submit" form="foto_productoForm" class="btn btn-primary" style="border-radius:8px;font-weight:600;">
                    <i class="fa fa-save mr-1"></i> Guardar Imagen
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.producto-foto-modal .modal-content { border:none;border-radius:14px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.25); }
.producto-foto-modal .modal-header { background:linear-gradient(135deg,#f39c12 0%,#e05a00 100%);border:none;padding:18px 24px; }
.producto-foto-modal .modal-title { color:#fff;font-weight:700;font-size:1.05rem; }
.producto-foto-modal .close { color:rgba(255,255,255,.8);opacity:1;font-size:1.3rem; }
.producto-foto-modal .close:hover { color:#fff; }
.producto-foto-modal .modal-body { padding:22px;background:#f8fafc; }
.producto-foto-modal .modal-footer { background:#fff;border-top:1px solid #e8ecef;padding:12px 22px; }
.producto-foto-modal .foto-drop-area { border:2px dashed #ccd3db;border-radius:10px;padding:20px;text-align:center;transition:border-color .2s,background .2s; }
.producto-foto-modal .foto-drop-area:hover { border-color:#e05a00;background:#fdf4e7; }
.producto-foto-modal .foto-drop-area i { font-size:1.8rem;color:#aaa;display:block;margin-bottom:6px; }
.producto-foto-modal .foto-drop-area span { font-size:.8rem;color:#888; }
#modalSpinnerLoading .spinner-overlay-box { background:rgba(255,255,255,.97);border-radius:16px;padding:36px 30px;text-align:center;box-shadow:0 15px 50px rgba(0,0,0,.2); }
#modalSpinnerLoading .spinner-ring { display:inline-block;width:50px;height:50px;border:5px solid #e8d5bf;border-top-color:#e05a00;border-radius:50%;animation:foto-spin .8s linear infinite;margin-bottom:14px; }
#modalSpinnerLoading .spinner-overlay-box p { margin:0;font-size:1rem;font-weight:600;color:#7d3f00; }
#modalSpinnerLoading .spinner-overlay-box small { color:#888;font-size:.8rem; }
@keyframes foto-spin { to { transform:rotate(360deg); } }
</style>