(function () {
    'use strict';

    var estado = { token: null, filas: [], catalogos: null, revalidacion: null, revision: 0 };

    function escapeHtml(valor) {
        return String(valor == null ? '' : valor).replace(/[&<>'"]/g, function (caracter) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[caracter];
        });
    }

    function escapeAttr(valor) {
        return escapeHtml(valor).replace(/`/g, '&#96;');
    }

    function filaPorUid(uid) {
        return estado.filas.find(function (fila) { return fila.uid === uid; });
    }

    function opciones(items, campoTexto, seleccionado, original, filtro) {
        var disponibles = filtro ? items.filter(filtro) : items;
        var html = '<option value="">Seleccione</option>';
        if (!seleccionado && original) {
            html += '<option value="" selected>' + escapeHtml(original) + ' (no existe)</option>';
        }
        disponibles.forEach(function (item) {
            var texto = item[campoTexto] + (item.simbolo ? ' - ' + item.simbolo : '');
            html += '<option value="' + item.id + '"' + (Number(seleccionado) === Number(item.id) ? ' selected' : '') + '>' + escapeHtml(texto) + '</option>';
        });
        return html;
    }

    function input(fila, campo, tipo, clase, atributos) {
        var deshabilitado = fila.estado === 'creado' ? ' disabled' : '';
        return '<input class="form-control ' + (clase || '') + '" type="' + (tipo || 'text') + '" data-field="' + campo + '" value="' +
            escapeAttr(fila[campo] == null ? '' : fila[campo]) + '" ' + (atributos || '') + deshabilitado + '>';
    }

    function selector(fila, campo, htmlOpciones) {
        return '<select class="form-control" data-field="' + campo + '"' + (fila.estado === 'creado' ? ' disabled' : '') + '>' + htmlOpciones + '</select>';
    }

    function estadoFila(fila) {
        var etiqueta = { listo: 'Listo para crear', error: 'Con errores', advertencia: 'Advertencia', creado: 'Creado' }[fila.estado] || fila.estado;
        var mensajes = (fila.errores || []).concat(fila.advertencias || []);
        return '<div class="pcm-status"><span class="pcm-badge pcm-badge-' + fila.estado + '">' + escapeHtml(etiqueta) + '</span>' +
            (mensajes.length ? '<ul>' + mensajes.map(function (mensaje) { return '<li>' + escapeHtml(mensaje) + '</li>'; }).join('') + '</ul>' : '') + '</div>';
    }

    function renderFilas() {
        var cuerpo = document.querySelector('#tabla_carga_masiva tbody');
        if (!cuerpo || !estado.catalogos) return;

        cuerpo.innerHTML = estado.filas.map(function (fila) {
            var bloqueada = fila.estado === 'error' || fila.estado === 'creado';
            var subcategorias = function (item) { return Number(item.categoria_producto_id) === Number(fila.categoria_id); };
            return '<tr data-uid="' + fila.uid + '" class="pcm-' + fila.estado + '">' +
                '<td class="text-center"><input type="checkbox" data-select ' + (fila.seleccionado ? 'checked ' : '') + (bloqueada ? 'disabled' : '') + '></td>' +
                '<td>' + fila.fila_excel + '</td>' +
                '<td>' + input(fila, 'codigo_barra', 'text', '', 'maxlength="100"') + '</td>' +
                '<td>' + input(fila, 'codigo_estatal', 'text', '', 'maxlength="45"') + '</td>' +
                '<td>' + input(fila, 'nombre', 'text', 'pcm-wide', 'maxlength="1000"') + '</td>' +
                '<td>' + input(fila, 'descripcion', 'text', 'pcm-description', 'maxlength="2000"') + '</td>' +
                '<td>' + selector(fila, 'isv', '<option value="0"' + (Number(fila.isv) === 0 ? ' selected' : '') + '>0%</option><option value="15"' + (Number(fila.isv) === 15 ? ' selected' : '') + '>15%</option><option value="18"' + (Number(fila.isv) === 18 ? ' selected' : '') + '>18%</option>') + '</td>' +
                '<td>' + selector(fila, 'marca_id', opciones(estado.catalogos.marcas, 'nombre', fila.marca_id, fila.marca_original)) + '</td>' +
                '<td>' + selector(fila, 'categoria_id', opciones(estado.catalogos.categorias, 'descripcion', fila.categoria_id, fila.categoria_original)) + '</td>' +
                '<td>' + selector(fila, 'subcategoria_id', opciones(estado.catalogos.subcategorias, 'descripcion', fila.subcategoria_id, fila.subcategoria_original, subcategorias)) + '</td>' +
                '<td>' + selector(fila, 'unidad_compra_id', opciones(estado.catalogos.unidades, 'nombre', fila.unidad_compra_id, fila.unidad_compra_original)) + '</td>' +
                '<td>' + input(fila, 'cantidad_compra', 'number', '', 'min="0.01" step="0.01"') + '</td>' +
                '<td>' + selector(fila, 'unidad_venta_id', opciones(estado.catalogos.unidades, 'nombre', fila.unidad_venta_id, fila.unidad_venta_original)) + '</td>' +
                '<td>' + input(fila, 'cantidad_venta', 'number', '', 'min="0.01" step="0.01"') + '</td>' +
                '<td>' + input(fila, 'precio_base', 'number', '', 'min="0" step="0.01"') + '</td>' +
                '<td>' + input(fila, 'costo_promedio', 'number', '', 'min="0" step="0.01"') + '</td>' +
                '<td>' + input(fila, 'ultimo_costo_compra', 'number', '', 'min="0" step="0.01"') + '</td>' +
                '<td>' + input(fila, 'tiempo_recuperacion_meses', 'number', '', 'min="1" max="65535" step="1"') + '</td>' +
                '<td>' + input(fila, 'origen', 'text', '', 'maxlength="200"') + '</td>' +
                '<td>' + estadoFila(fila) + '</td></tr>';
        }).join('');
        actualizarContadores();
    }

    function actualizarContadores() {
        var contar = function (tipo) { return estado.filas.filter(function (fila) { return fila.estado === tipo; }).length; };
        var seleccionados = estado.filas.filter(function (fila) { return fila.seleccionado && fila.estado !== 'error' && fila.estado !== 'creado'; }).length;
        document.getElementById('pcm_total').textContent = estado.filas.length;
        document.getElementById('pcm_listos').textContent = contar('listo');
        document.getElementById('pcm_errores').textContent = contar('error');
        document.getElementById('pcm_advertencias').textContent = contar('advertencia');
        document.getElementById('pcm_seleccionados').textContent = seleccionados;
        document.getElementById('pcm_guardar').disabled = seleccionados === 0;
    }

    function procesarArchivo() {
        var archivo = document.getElementById('pcm_archivo').files[0];
        if (!archivo) {
            Swal.fire({ icon: 'warning', title: 'Seleccione un archivo', text: 'Debe elegir una plantilla XLSX o XLS.' });
            return;
        }
        var datos = new FormData();
        datos.append('archivo', archivo);
        document.getElementById('pcm_procesar').disabled = true;
        document.getElementById('pcm_mensaje').textContent = 'Leyendo y validando archivo...';

        axios.post('/producto/carga-masiva/previsualizar', datos).then(function (respuesta) {
            estado.token = respuesta.data.token;
            estado.filas = respuesta.data.filas;
            estado.catalogos = respuesta.data.catalogos;
            document.getElementById('pcm_previsualizacion').style.display = '';
            document.getElementById('pcm_mensaje').textContent = 'Previsualización generada. No se ha creado ningún producto.';
            renderFilas();
        }).catch(function (error) {
            document.getElementById('pcm_mensaje').textContent = '';
            Swal.fire({ icon: 'error', title: 'No se pudo procesar', text: error.response && error.response.data.message ? error.response.data.message : 'Revise el archivo seleccionado.' });
        }).finally(function () {
            document.getElementById('pcm_procesar').disabled = false;
        });
    }

    function programarRevalidacion() {
        clearTimeout(estado.revalidacion);
        estado.revision += 1;
        var revision = estado.revision;
        estado.revalidacion = setTimeout(function () { revalidar(revision); }, 450);
    }

    function revalidar(revision) {
        axios.post('/producto/carga-masiva/revalidar', { token: estado.token, filas: estado.filas }).then(function (respuesta) {
            if (revision !== estado.revision) return;
            var seleccionActual = {};
            estado.filas.forEach(function (fila) { seleccionActual[fila.uid] = fila.seleccionado; });
            estado.filas = respuesta.data.filas.map(function (fila) {
                fila.seleccionado = fila.estado !== 'error' && fila.estado !== 'creado' && Boolean(seleccionActual[fila.uid]);
                return fila;
            });
            renderFilas();
        }).catch(function (error) {
            if (error.response && error.response.status === 419) {
                Swal.fire({ icon: 'warning', title: 'Previsualización expirada', text: error.response.data.message });
                return;
            }
            Swal.fire({ icon: 'error', title: 'No se pudo revalidar', text: error.response && error.response.data.message ? error.response.data.message : 'Revise los cambios realizados.' });
        });
    }

    function guardarProductos() {
        var cantidad = estado.filas.filter(function (fila) { return fila.seleccionado && fila.estado !== 'error' && fila.estado !== 'creado'; }).length;
        Swal.fire({
            icon: 'question', title: 'Confirmar creación masiva',
            html: 'Está a punto de crear <strong>' + cantidad + ' productos</strong>.<br>Los registros con errores no serán incluidos.<br><br>¿Desea continuar?',
            showCancelButton: true, confirmButtonText: 'Confirmar y guardar', cancelButtonText: 'Cancelar', confirmButtonColor: '#08783e'
        }).then(function (resultado) {
            if (!resultado.isConfirmed) return;
            document.getElementById('pcm_guardar').disabled = true;
            axios.post('/producto/carga-masiva/guardar', { token: estado.token, filas: estado.filas }).then(function (respuesta) {
                var actualizadas = {};
                respuesta.data.creados.concat(respuesta.data.rechazados).forEach(function (fila) { actualizadas[fila.uid] = fila; });
                estado.filas = estado.filas.map(function (fila) { return actualizadas[fila.uid] || fila; });
                estado.token = null;
                renderFilas();
                $('#tbl_productosListar').DataTable().ajax.reload();
                Swal.fire({
                    icon: respuesta.data.cantidad_rechazada ? 'warning' : 'success', title: 'Proceso finalizado',
                    html: '<strong>' + respuesta.data.cantidad_creada + '</strong> productos creados.<br><strong>' + respuesta.data.cantidad_rechazada + '</strong> productos rechazados.<br><small>Importación: ' + escapeHtml(respuesta.data.identificador) + '</small>'
                });
            }).catch(function (error) {
                Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: error.response && error.response.data.message ? error.response.data.message : 'Ocurrió un error durante la confirmación.' });
                actualizarContadores();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('pcm_procesar').addEventListener('click', procesarArchivo);
        document.getElementById('pcm_guardar').addEventListener('click', guardarProductos);
        document.getElementById('pcm_seleccionar_validos').addEventListener('click', function () {
            estado.filas.forEach(function (fila) { fila.seleccionado = fila.estado === 'listo' || fila.estado === 'advertencia'; });
            renderFilas();
        });
        document.getElementById('pcm_deseleccionar').addEventListener('click', function () {
            estado.filas.forEach(function (fila) { fila.seleccionado = false; });
            renderFilas();
        });

        document.querySelector('#tabla_carga_masiva tbody').addEventListener('change', function (evento) {
            var filaElemento = evento.target.closest('tr[data-uid]');
            if (!filaElemento) return;
            var fila = filaPorUid(filaElemento.dataset.uid);
            if (evento.target.hasAttribute('data-select')) {
                fila.seleccionado = evento.target.checked;
                actualizarContadores();
                return;
            }
            var campo = evento.target.dataset.field;
            if (!campo) return;
            fila[campo] = evento.target.type === 'number' || evento.target.tagName === 'SELECT' ? evento.target.value : evento.target.value.trim();
            if (campo === 'categoria_id') {
                fila.subcategoria_id = 0;
                fila.subcategoria_original = '';
            }
            programarRevalidacion();
        });
        document.querySelector('#tabla_carga_masiva tbody').addEventListener('input', function (evento) {
            if (evento.target.tagName === 'SELECT' || !evento.target.dataset.field) return;
            var filaElemento = evento.target.closest('tr[data-uid]');
            var fila = filaElemento ? filaPorUid(filaElemento.dataset.uid) : null;
            if (!fila) return;
            fila[evento.target.dataset.field] = evento.target.value;
            programarRevalidacion();
        });
    });
})();