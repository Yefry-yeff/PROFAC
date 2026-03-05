<x-app-layout>
    <div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
        <div class="col-lg-12">
            <h2>Cambio de Contraseña Requerido</h2>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="ibox">
                    <div class="ibox-title bg-warning text-white">
                        <h5 class="mb-0">
                            <i class="fa fa-lock"></i>
                            &nbsp;Debe cambiar su contraseña antes de continuar
                        </h5>
                    </div>
                    <div class="ibox-content">
                        <p class="text-muted mb-4">
                            Por seguridad, se le ha asignado una contraseña temporal. 
                            Por favor establezca una nueva contraseña para continuar usando el sistema.
                        </p>

                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <form method="POST" action="/cambiar-contrasena/guardar" id="formCambioContrasena">
                            @csrf

                            <div class="form-group">
                                <label for="nueva_contrasena">Nueva contraseña <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('nueva_contrasena') is-invalid @enderror"
                                    id="nueva_contrasena" name="nueva_contrasena"
                                    placeholder="Mínimo 8 caracteres" minlength="8" required>
                                <small class="text-muted">Mínimo 8 caracteres</small>
                                @error('nueva_contrasena')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="confirmar_contrasena">Confirmar contraseña <span class="text-danger">*</span></label>
                                <input type="password" class="form-control"
                                    id="confirmar_contrasena" name="confirmar_contrasena"
                                    placeholder="Repita la nueva contraseña" required>
                                <small id="msg_no_coincide" class="text-danger" style="display:none;">Las contraseñas no coinciden.</small>
                            </div>

                            <div class="form-group text-right mt-4">
                                <button type="submit" class="btn btn-primary btn-lg" id="btn_guardar_contrasena">
                                    <i class="fa fa-save"></i> Guardar y Continuar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('formCambioContrasena').addEventListener('submit', function(e) {
            var pass = document.getElementById('nueva_contrasena').value;
            var confirm = document.getElementById('confirmar_contrasena').value;

            if (pass !== confirm) {
                e.preventDefault();
                document.getElementById('msg_no_coincide').style.display = 'block';
                return false;
            }

            document.getElementById('msg_no_coincide').style.display = 'none';
        });

        document.getElementById('confirmar_contrasena').addEventListener('input', function() {
            var pass = document.getElementById('nueva_contrasena').value;
            var msg = document.getElementById('msg_no_coincide');
            msg.style.display = (this.value && this.value !== pass) ? 'block' : 'none';
        });
    </script>
    @endpush
</x-app-layout>
