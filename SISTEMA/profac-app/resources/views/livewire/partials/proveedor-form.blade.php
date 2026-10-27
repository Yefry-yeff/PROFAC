@php($prefijo = $editar ? 'editar_' : '')
<div class="proveedor-form-errors alert alert-danger d-none" role="alert" tabindex="-1"></div>
<p class="proveedor-form-intro">Completa la información del proveedor. Los campos con <span class="text-danger">*</span> son obligatorios.</p>
<fieldset class="proveedor-section">
    <legend><span class="proveedor-section-number">01</span> Datos generales</legend>
    <div class="row">
        <div class="col-md-5 form-group">
            <div class="proveedor-field-heading">
                <label for="{{ $prefijo }}rtn_prov">RTN <span class="text-danger">*</span></label>
                <label class="proveedor-no-registro" for="{{ $prefijo }}rtn_sin_registro">
                    <input type="checkbox" id="{{ $prefijo }}rtn_sin_registro" name="{{ $prefijo }}rtn_sin_registro" value="1"
                        data-sin-registro="{{ $prefijo }}rtn_prov" data-sin-registro-valor="N/A"> Sin registro
                </label>
            </div>
            <input id="{{ $prefijo }}rtn_prov" name="{{ $prefijo }}rtn_prov" class="form-control" type="text" maxlength="45" pattern="[A-Za-z0-9]+" required aria-describedby="{{ $prefijo }}rtn_ayuda">
            <small id="{{ $prefijo }}rtn_ayuda" class="form-text text-muted">Solo letras y números. Debe ser único, excepto sin registro (N/A).</small>
        </div>
        <div class="col-md-7 form-group">
            <label for="{{ $prefijo }}nombre_prov">Nombre <span class="text-danger">*</span></label>
            <input id="{{ $prefijo }}nombre_prov" name="{{ $prefijo }}nombre_prov" class="form-control" type="text" maxlength="100" required>
        </div>
        <div class="col-md-6 form-group">
            <label for="{{ $prefijo }}categoria_prov">Categoría <span class="text-danger">*</span></label>
            <select id="{{ $prefijo }}categoria_prov" name="{{ $prefijo }}categoria_prov" class="form-control" required>
                <option value="">Seleccione una categoría</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 form-group">
            <label for="{{ $prefijo }}giro_neg_prov">Tipo de personalidad <span class="text-danger">*</span></label>
            <select id="{{ $prefijo }}giro_neg_prov" name="{{ $prefijo }}giro_neg_prov" class="form-control" required>
                <option value="">Seleccione el tipo de personalidad</option>
                @foreach ($tipoPersonalidad as $personalidad)
                    <option value="{{ $personalidad->id }}">{{ $personalidad->nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>
</fieldset>
<fieldset class="proveedor-section">
    <legend><span class="proveedor-section-number">02</span> Contacto</legend>
    <div class="row">
        <div class="col-md-6 form-group">
            <div class="proveedor-field-heading">
                <label for="{{ $prefijo }}telefono_prov">Teléfono 1 <span class="text-danger">*</span></label>
                <label class="proveedor-no-registro" for="{{ $prefijo }}telefono_1_sin_registro">
                    <input type="checkbox" id="{{ $prefijo }}telefono_1_sin_registro" name="{{ $prefijo }}telefono_1_sin_registro" value="1"
                        data-sin-registro="{{ $prefijo }}telefono_prov" data-sin-registro-valor=""> Sin registro
                </label>
            </div>
            <input id="{{ $prefijo }}telefono_prov" name="{{ $prefijo }}telefono_prov" class="form-control" type="tel" inputmode="numeric" placeholder="####-####" pattern="[0-9]{4}-[0-9]{4}" maxlength="9" data-proveedor-telefono required>
        </div>
        <div class="col-md-6 form-group">
            <div class="proveedor-field-heading">
                <label for="{{ $prefijo }}telefono_prov_2">Teléfono 2 <span class="proveedor-optional">Opcional</span></label>
                <label class="proveedor-no-registro" for="{{ $prefijo }}telefono_2_sin_registro">
                    <input type="checkbox" id="{{ $prefijo }}telefono_2_sin_registro" name="{{ $prefijo }}telefono_2_sin_registro" value="1"
                        data-sin-registro="{{ $prefijo }}telefono_prov_2" data-sin-registro-valor=""> Sin registro
                </label>
            </div>
            <input id="{{ $prefijo }}telefono_prov_2" name="{{ $prefijo }}telefono_prov_2" class="form-control" type="tel" inputmode="numeric" placeholder="####-####" pattern="[0-9]{4}-[0-9]{4}" maxlength="9" data-proveedor-telefono>
        </div>
        <div class="col-12 form-group">
            <label for="{{ $prefijo }}correo_prov">Correo electrónico <span class="proveedor-optional">Opcional</span></label>
            <input id="{{ $prefijo }}correo_prov" name="{{ $prefijo }}correo_prov" class="form-control" type="email" maxlength="45">
        </div>
    </div>
</fieldset>
<fieldset class="proveedor-section">
    <legend><span class="proveedor-section-number">03</span> Dirección</legend>
    <div class="row">
        <div class="col-md-4 form-group">
            <label for="{{ $prefijo }}pais_prov">País <span class="text-danger">*</span></label>
            <select id="{{ $prefijo }}pais_prov" name="{{ $prefijo }}pais_prov" class="form-control" required
                onchange="{{ $editar ? 'editarObtenerDepartamentos' : 'obtenerDepartamentos' }}()">
                <option value="">Seleccione un país</option>
                @foreach ($paises as $pais)
                    <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 form-group">
            <label for="{{ $prefijo }}depto_prov">Departamento <span class="text-danger">*</span></label>
            <select id="{{ $prefijo }}depto_prov" name="{{ $prefijo }}depto_prov" class="form-control" required
                onchange="{{ $editar ? 'editarObtenerMunicipios' : 'obtenerMunicipios' }}()">
                <option value="">Seleccione un departamento</option>
            </select>
        </div>
        <div class="col-md-4 form-group">
            <label for="{{ $prefijo }}municipio_prov">Municipio <span class="text-danger">*</span></label>
            <select id="{{ $prefijo }}municipio_prov" name="{{ $prefijo }}municipio_prov" class="form-control" required>
                <option value="">Seleccione un municipio</option>
            </select>
        </div>
        <div class="col-12 form-group">
            <label for="{{ $prefijo }}direccion_prov">Dirección <span class="text-danger">*</span></label>
            <textarea id="{{ $prefijo }}direccion_prov" name="{{ $prefijo }}direccion_prov" class="form-control" rows="3" placeholder="Barrio, colonia, calle y referencias del lugar" required></textarea>
        </div>
    </div>
</fieldset>
<fieldset class="proveedor-section">
    <legend><span class="proveedor-section-number">04</span> Retención</legend>
    <div class="form-group">
        <label class="proveedor-no-registro" for="{{ $prefijo }}aplicar_retencion">
            <input id="{{ $prefijo }}aplicar_retencion" type="checkbox" data-aplicar-retencion
                aria-controls="{{ $prefijo }}retencion_opciones" aria-expanded="false">
            Aplicar retención a este proveedor
        </label>
        <input type="hidden" name="{{ $prefijo }}retencion_prov" value="1" data-sin-retencion>
    </div>
    <div id="{{ $prefijo }}retencion_opciones" class="form-group d-none" data-retencion-opciones>
        <label for="{{ $prefijo }}retencion_prov">Tipo de retención <span class="text-danger">*</span></label>
        <select id="{{ $prefijo }}retencion_prov" name="{{ $prefijo }}retencion_prov" class="form-control" disabled>
            <option value="">Seleccione una retención</option>
            @foreach ($retenciones as $retencion)
                @if ($retencion->id != 1)
                    <option value="{{ $retencion->id }}">{{ $retencion->nombre }}</option>
                @endif
            @endforeach
        </select>
    </div>
</fieldset>
