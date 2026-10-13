(function () {
    'use strict';

    var config = window.compraProductoConfig || {};
    var state = {
        items: Object.create(null),
        order: [],
        totals: { subtotal: 0, isv: 0, total: 0 },
        page: 1,
        pageSize: 50,
        filter: '',
        temporalId: null,
        draftTimer: null,
        draftQueue: Promise.resolve(),
        restoring: false,
        initialized: false
    };

    function element(id) {
        return document.getElementById(id);
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function number(value) {
        var parsed = Number(value);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function roundMoney(value) {
        return Math.round((number(value) + Number.EPSILON) * 100) / 100;
    }

    function money(value) {
        return 'L. ' + roundMoney(value).toLocaleString('es-HN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function calculateItem(item) {
        var price = roundMoney(item.precio);
        var quantity = Math.max(0, Math.trunc(number(item.cantidad)));
        var units = Math.max(1, Math.trunc(number(item.unidades_compra)));
        var subtotal = roundMoney(price * quantity * units);
        var tax = roundMoney(subtotal * number(item.isv_porcentaje) / 100);

        item.subtotal = subtotal;
        item.isv_total = tax;
        item.total = roundMoney(subtotal + tax);
    }

    function recalculateAll() {
        state.totals = { subtotal: 0, isv: 0, total: 0 };
        state.order.forEach(function (id) {
            var item = state.items[id];
            calculateItem(item);
            state.totals.subtotal += item.subtotal;
            state.totals.isv += item.isv_total;
            state.totals.total += item.total;
        });
        normalizeTotals();
        renderTotals();
    }

    function updateItemTotals(item, oldValues) {
        calculateItem(item);
        state.totals.subtotal += item.subtotal - oldValues.subtotal;
        state.totals.isv += item.isv_total - oldValues.isv;
        state.totals.total += item.total - oldValues.total;
        normalizeTotals();
        renderTotals();
        renderRowAmounts(item.producto_id);
    }

    function normalizeTotals() {
        state.totals.subtotal = roundMoney(state.totals.subtotal);
        state.totals.isv = roundMoney(state.totals.isv);
        state.totals.total = roundMoney(state.totals.total);
    }

    function renderTotals() {
        element('subTotalGeneral').textContent = money(state.totals.subtotal);
        element('isvGeneral').textContent = money(state.totals.isv);
        element('totalGeneral').textContent = money(state.totals.total);
        element('contadorProductosCompra').textContent = state.order.length + (state.order.length === 1 ? ' producto' : ' productos');
        element('resumenLineasCompra').textContent = state.order.length
            ? state.order.length + (state.order.length === 1 ? ' línea agregada' : ' líneas agregadas')
            : 'Sin líneas pendientes';
    }

    function renderRowAmounts(id) {
        var item = state.items[id];
        if (!item) return;
        var outputs = document.querySelectorAll('[data-compra-output][data-id="' + id + '"]');
        outputs.forEach(function (output) {
            output.textContent = money(item[output.dataset.compraOutput]);
        });
    }

    function filteredIds() {
        if (!state.filter) return state.order.slice();
        var term = state.filter.toLowerCase();
        return state.order.filter(function (id) {
            var item = state.items[id];
            return [item.producto_id, item.nombre, item.codigo_barra, item.codigo_estatal]
                .some(function (value) { return String(value || '').toLowerCase().indexOf(term) !== -1; });
        });
    }

    function imageUrl(item) {
        if (!item.imagen) return config.noImagenUrl;
        if (/^https?:\/\//i.test(item.imagen) || item.imagen.charAt(0) === '/') return item.imagen;
        return String(config.catalogoUrl || '').replace(/\/$/, '') + '/' + String(item.imagen).replace(/^\//, '');
    }

    function rowHtml(item) {
        var metadata = 'ID ' + item.producto_id;
        if (item.codigo_barra) metadata += ' · C.B. ' + item.codigo_barra;
        if (item.marca_nombre) metadata += ' · ' + item.marca_nombre;

        return '<tr data-row-id="' + item.producto_id + '">' +
            '<td><div class="cmp-product">' +
                '<img src="' + escapeHtml(imageUrl(item)) + '" alt="" loading="lazy" onerror="this.onerror=null;this.src=\'' + escapeHtml(config.noImagenUrl) + '\'">' +
                '<div><div class="cmp-product-name">' + escapeHtml(item.nombre) + '</div><div class="cmp-product-meta">' + escapeHtml(metadata) + '</div></div>' +
            '</div></td>' +
            '<td><input type="number" class="form-control form-control-sm cmp-number" min="0" step="0.01" data-compra-field="precio" data-id="' + item.producto_id + '" value="' + escapeHtml(item.precio) + '" required></td>' +
            '<td><input type="number" class="form-control form-control-sm cmp-number" min="1" step="1" data-compra-field="cantidad" data-id="' + item.producto_id + '" value="' + escapeHtml(item.cantidad) + '" required></td>' +
            '<td><span class="font-weight-bold">' + escapeHtml(item.unidad) + '</span></td>' +
            '<td><input type="date" class="form-control form-control-sm cmp-date" data-compra-field="fecha_expiracion" data-id="' + item.producto_id + '" value="' + escapeHtml(item.fecha_expiracion) + '"></td>' +
            '<td class="cmp-money" data-compra-output="subtotal" data-id="' + item.producto_id + '">' + money(item.subtotal) + '</td>' +
            '<td class="cmp-money" data-compra-output="isv_total" data-id="' + item.producto_id + '">' + money(item.isv_total) + '</td>' +
            '<td class="cmp-money" data-compra-output="total" data-id="' + item.producto_id + '">' + money(item.total) + '</td>' +
            '<td><button type="button" class="cmp-remove" data-remove-id="' + item.producto_id + '" title="Quitar producto"><i class="fa fa-trash"></i></button></td>' +
        '</tr>';
    }

    function renderCart() {
        var ids = filteredIds();
        var pages = Math.max(1, Math.ceil(ids.length / state.pageSize));
        state.page = Math.min(Math.max(1, state.page), pages);
        var start = (state.page - 1) * state.pageSize;
        var visible = ids.slice(start, start + state.pageSize);
        var body = element('productosCompraBody');
        var empty = element('carritoCompraVacio');
        var pagination = element('paginacionCompra');

        body.innerHTML = visible.map(function (id) { return rowHtml(state.items[id]); }).join('');
        empty.classList.toggle('d-none', visible.length > 0);
        if (!visible.length) {
            empty.lastChild.nodeValue = ids.length === 0 && state.order.length > 0
                ? ' No hay coincidencias en el carrito.'
                : ' No hay productos en la compra.';
        }

        pagination.classList.toggle('d-none', ids.length <= state.pageSize);
        element('paginacionInfoCompra').textContent = ids.length
            ? 'Mostrando ' + (start + 1) + '-' + Math.min(start + state.pageSize, ids.length) + ' de ' + ids.length
            : '';
        element('paginaAnteriorCompra').disabled = state.page <= 1;
        element('paginaSiguienteCompra').disabled = state.page >= pages;
        renderTotals();
    }

    function addProduct(searchProduct, detail) {
        var id = Number(detail.id);
        if (state.items[id]) {
            state.filter = '';
            element('filtrarCarritoCompra').value = '';
            state.page = Math.ceil((state.order.indexOf(id) + 1) / state.pageSize);
            renderCart();
            Swal.fire({ icon: 'warning', title: 'Producto ya agregado', text: 'La compra no permite productos duplicados.' });
            return;
        }

        var item = {
            producto_id: id,
            nombre: searchProduct.nombre || detail.nombre,
            codigo_barra: searchProduct.codigo_barra || '',
            codigo_estatal: searchProduct.codigo_estatal || '',
            marca_nombre: searchProduct.marca_nombre || '',
            imagen: searchProduct.imagen || '',
            isv_porcentaje: number(detail.isv),
            unidad: detail.unidad || '',
            unidades_compra: Math.max(1, Math.trunc(number(detail.unidadad_compra))),
            unidad_compra_id: Number(detail.unidad_medida_compra_id),
            precio: '',
            cantidad: 1,
            fecha_expiracion: '',
            subtotal: 0,
            isv_total: 0,
            total: 0
        };
        calculateItem(item);
        state.items[id] = item;
        state.order.push(id);
        state.filter = '';
        state.page = Math.ceil(state.order.length / state.pageSize);
        element('filtrarCarritoCompra').value = '';
        element('buscarProductoCompra').value = '';
        renderCart();
        scheduleDraftSave();

        window.setTimeout(function () {
            var priceInput = document.querySelector('[data-compra-field="precio"][data-id="' + id + '"]');
            if (priceInput) priceInput.focus();
        }, 0);
    }

    window.compraSeleccionarProducto = function (product) {
        if (product.unidadad_compra && product.unidad_medida_compra_id) {
            addProduct(product, {
                id: product.id,
                isv: product.isv,
                unidadad_compra: product.unidadad_compra,
                unidad_medida_compra_id: product.unidad_medida_compra_id,
                unidad: (product.unidad_medida_compra_nombre || 'Unidad') + ' - ' + product.unidadad_compra
            });
            return;
        }

        axios.post('/prodcuto/compra/datos', { id: product.id })
            .then(function (response) {
                addProduct(product, response.data.producto);
            })
            .catch(function () {
                Swal.fire({ icon: 'error', title: 'No se pudo agregar', text: 'No fue posible obtener la unidad de compra del producto.' });
            });
    };

    function removeProduct(id) {
        var item = state.items[id];
        if (!item) return;
        state.totals.subtotal -= item.subtotal;
        state.totals.isv -= item.isv_total;
        state.totals.total -= item.total;
        normalizeTotals();
        delete state.items[id];
        state.order = state.order.filter(function (itemId) { return itemId !== id; });
        renderCart();
        scheduleDraftSave();
    }

    function openProductFinder() {
        var opener = window.abrirBuscador_buscadorProductoCompra;
        if (typeof opener === 'function') opener(element('buscarProductoCompra').value, false);
    }

    function updateDueDate(preserveValue) {
        var paymentType = element('tipoPagoCompra').value;
        var dueDate = element('fecha_vencimiento');
        if (String(paymentType) === '1') {
            dueDate.readOnly = false;
            if (!preserveValue && dueDate.value === config.fechaHoy) dueDate.value = '';
        } else if (String(paymentType) === '2') {
            dueDate.value = config.fechaHoy;
            dueDate.readOnly = true;
        }
    }

    window.validarFechaPago = updateDueDate;

    function loadPaymentTypes() {
        return axios.get('/producto/tipo/pagos').then(function (response) {
            var options = '<option value="">Seleccione un tipo</option>';
            (response.data.tipos || []).forEach(function (type) {
                options += '<option value="' + escapeHtml(type.id) + '">' + escapeHtml(type.descripcion) + '</option>';
            });
            element('tipoPagoCompra').innerHTML = options;
        }).catch(function () {
            element('tipoPagoCompra').innerHTML = '<option value="">No disponible</option>';
            Swal.fire({ icon: 'error', title: 'Tipos de pago no disponibles', text: 'Actualice la página para intentarlo de nuevo.' });
        });
    }

    function initializeProvider() {
        $('#seleccionarProveedor').select2({
            width: '100%',
            placeholder: 'Buscar proveedor',
            allowClear: true,
            ajax: {
                url: '/producto/lista/proveedores',
                delay: 300,
                data: function (params) {
                    return { search: params.term || '', page: params.page || 1 };
                }
            }
        });
    }

    function headerData() {
        var provider = $('#seleccionarProveedor').select2('data')[0];
        return {
            numero_factura: element('numero_factura').value,
            cai: element('cai').value,
            proveedor: provider && provider.id ? { id: provider.id, text: provider.text } : null,
            tipo_pago: element('tipoPagoCompra').value,
            fecha_vencimiento: element('fecha_vencimiento').value,
            fecha_emision: element('fecha_emision').value,
            fecha_entrega: element('fecha_entrega').value
        };
    }

    function serializableItems() {
        return state.order.map(function (id) {
            var item = state.items[id];
            return {
                producto_id: item.producto_id,
                nombre: item.nombre,
                codigo_barra: item.codigo_barra,
                codigo_estatal: item.codigo_estatal,
                marca_nombre: item.marca_nombre,
                imagen: item.imagen,
                isv_porcentaje: item.isv_porcentaje,
                unidad: item.unidad,
                unidades_compra: item.unidades_compra,
                unidad_compra_id: item.unidad_compra_id,
                precio: item.precio,
                cantidad: item.cantidad,
                fecha_expiracion: item.fecha_expiracion
            };
        });
    }

    function hasDraftContent() {
        var header = headerData();
        return state.order.length > 0 || !!(header.numero_factura || header.cai || header.proveedor || header.fecha_entrega);
    }

    function draftTitle() {
        var header = headerData();
        var invoice = header.numero_factura ? 'Factura ' + header.numero_factura : 'Compra sin número';
        return header.proveedor ? invoice + ' · ' + header.proveedor.text : invoice;
    }

    function draftPayload() {
        return {
            id: state.temporalId,
            tipo: 'compra',
            codigo_tipo: 'compra',
            titulo: draftTitle(),
            url_reanudacion: '/producto/compra',
            contenido: { cabecera: headerData(), productos: serializableItems() }
        };
    }

    function setDraftStatus(text) {
        element('estadoTemporalCompra').textContent = text || '';
    }

    function saveDraft() {
        window.clearTimeout(state.draftTimer);
        if (!state.initialized || state.restoring || !hasDraftContent()) return Promise.resolve();

        setDraftStatus('Guardando...');
        state.draftQueue = state.draftQueue.catch(function () {}).then(function () {
            return axios.post('/ventas/temporales', draftPayload()).then(function (response) {
                state.temporalId = response.data.id;
                setDraftStatus('Guardado temporal');
                refreshDraftCount();
            });
        }).catch(function () {
            setDraftStatus('No se pudo guardar');
        });
        return state.draftQueue;
    }

    function scheduleDraftSave() {
        if (!state.initialized || state.restoring) return;
        window.clearTimeout(state.draftTimer);
        setDraftStatus(hasDraftContent() ? 'Cambios pendientes' : '');
        state.draftTimer = window.setTimeout(saveDraft, 1200);
    }

    function refreshDraftCount() {
        return axios.get('/ventas/temporales', { params: { tipo: 'compra' } }).then(function (response) {
            var drafts = response.data.data || [];
            element('cantidadTemporalesCompra').textContent = drafts.length;
            return drafts;
        });
    }

    function formatDateTime(value) {
        if (!value) return '';
        var date = new Date(String(value).replace(' ', 'T'));
        return Number.isNaN(date.getTime()) ? value : date.toLocaleString('es-HN');
    }

    function showDraftList(drafts, startup) {
        if (!drafts.length) {
            if (!startup) Swal.fire({ icon: 'info', title: 'Sin registros temporales', text: 'No hay compras pendientes guardadas.' });
            return;
        }

        var selected = state.temporalId || drafts[0].id;
        var html = '<div class="cmp-temp-list">' + drafts.map(function (draft) {
            return '<label class="cmp-temp-item">' +
                '<input type="radio" name="temporalCompraSeleccionado" value="' + draft.id + '" ' + (String(selected) === String(draft.id) ? 'checked' : '') + '>' +
                '<span class="cmp-temp-copy"><strong>' + escapeHtml(draft.titulo || 'Compra temporal') + '</strong><small>Actualizado ' + escapeHtml(formatDateTime(draft.updated_at)) + '</small></span>' +
                '<button type="button" class="cmp-temp-delete" title="Eliminar" onclick="event.preventDefault();event.stopPropagation();eliminarTemporalCompra(' + draft.id + ')"><i class="fa fa-trash"></i></button>' +
            '</label>';
        }).join('') + '</div>';

        Swal.fire({
            title: 'Registros temporales',
            html: html,
            showCancelButton: true,
            confirmButtonText: 'Continuar compra',
            cancelButtonText: startup ? 'Iniciar nueva' : 'Cerrar',
            allowOutsideClick: !startup,
            preConfirm: function () {
                var chosen = document.querySelector('input[name="temporalCompraSeleccionado"]:checked');
                if (!chosen) {
                    Swal.showValidationMessage('Seleccione un registro temporal.');
                    return false;
                }
                return chosen.value;
            }
        }).then(function (result) {
            if (result.isConfirmed) loadDraft(result.value);
            else if (startup) resetPurchase();
        });
    }

    function listDrafts(startup) {
        refreshDraftCount().then(function (drafts) {
            showDraftList(drafts, !!startup);
        }).catch(function () {
            if (!startup) Swal.fire({ icon: 'error', title: 'No se pudieron cargar', text: 'Intente nuevamente.' });
        });
    }

    function setProvider(provider) {
        var select = $('#seleccionarProveedor');
        select.empty().append(new Option('Seleccione un proveedor', '', false, false));
        if (provider && provider.id) {
            select.append(new Option(provider.text, provider.id, true, true));
        }
        select.trigger('change.select2');
    }

    function restoreDraftContent(content, temporalId) {
        var header = content.cabecera || {};
        state.restoring = true;
        state.temporalId = Number(temporalId);
        element('numero_factura').value = header.numero_factura || '';
        element('cai').value = header.cai || '';
        setProvider(header.proveedor || null);
        element('tipoPagoCompra').value = header.tipo_pago || '';
        element('fecha_vencimiento').value = header.fecha_vencimiento || config.fechaHoy;
        element('fecha_emision').value = header.fecha_emision || config.fechaHoy;
        element('fecha_entrega').value = header.fecha_entrega || '';
        updateDueDate(true);

        state.items = Object.create(null);
        state.order = [];
        (content.productos || []).forEach(function (saved) {
            var id = Number(saved.producto_id);
            if (!id || state.items[id]) return;
            var item = {
                producto_id: id,
                nombre: saved.nombre || ('Producto #' + id),
                codigo_barra: saved.codigo_barra || '',
                codigo_estatal: saved.codigo_estatal || '',
                marca_nombre: saved.marca_nombre || '',
                imagen: saved.imagen || '',
                isv_porcentaje: number(saved.isv_porcentaje),
                unidad: saved.unidad || '',
                unidades_compra: Math.max(1, Math.trunc(number(saved.unidades_compra))),
                unidad_compra_id: Number(saved.unidad_compra_id),
                precio: saved.precio === null || saved.precio === undefined ? '' : saved.precio,
                cantidad: Math.max(1, Math.trunc(number(saved.cantidad))),
                fecha_expiracion: saved.fecha_expiracion || '',
                subtotal: 0,
                isv_total: 0,
                total: 0
            };
            state.items[id] = item;
            state.order.push(id);
        });
        state.page = 1;
        state.filter = '';
        element('filtrarCarritoCompra').value = '';
        recalculateAll();
        renderCart();
        state.restoring = false;
        setDraftStatus('Temporal restaurado');
    }

    function loadDraft(id) {
        axios.get('/ventas/temporales/' + encodeURIComponent(id)).then(function (response) {
            restoreDraftContent(response.data.data.contenido || {}, id);
        }).catch(function () {
            Swal.fire({ icon: 'error', title: 'Registro no disponible', text: 'El temporal venció o ya fue eliminado.' });
            listDrafts(false);
        });
    }

    window.eliminarTemporalCompra = function (id) {
        axios.delete('/ventas/temporales/' + encodeURIComponent(id)).then(function () {
            if (String(state.temporalId) === String(id)) state.temporalId = null;
            Swal.close();
            listDrafts(false);
        }).catch(function () {
            Swal.fire({ icon: 'error', title: 'No se pudo eliminar', text: 'Intente nuevamente.' });
        });
    };

    function resetPurchase() {
        state.restoring = true;
        window.clearTimeout(state.draftTimer);
        state.items = Object.create(null);
        state.order = [];
        state.totals = { subtotal: 0, isv: 0, total: 0 };
        state.page = 1;
        state.filter = '';
        state.temporalId = null;
        element('crear_compra').reset();
        setProvider(null);
        element('fecha_emision').value = config.fechaHoy;
        element('fecha_vencimiento').value = config.fechaHoy;
        element('filtrarCarritoCompra').value = '';
        element('buscarProductoCompra').value = '';
        renderCart();
        state.restoring = false;
        setDraftStatus('');
    }

    function startNewPurchase() {
        if (!hasDraftContent()) {
            resetPurchase();
            return;
        }
        Swal.fire({
            icon: 'question',
            title: '¿Iniciar una nueva compra?',
            text: 'La compra actual quedará guardada en temporales durante 24 horas.',
            showCancelButton: true,
            confirmButtonText: 'Guardar e iniciar nueva',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (result.isConfirmed) saveDraft().then(resetPurchase);
        });
    }

    function payloadForPurchase() {
        var header = headerData();
        return {
            numero_factura: header.numero_factura,
            cai: header.cai,
            seleccionarProveedorId: header.proveedor ? header.proveedor.id : '',
            tipoPagoCompra: header.tipo_pago,
            fecha_vencimiento: header.fecha_vencimiento,
            fecha_emision: header.fecha_emision,
            fecha_entrega: header.fecha_entrega,
            productos: serializableItems().map(function (item) {
                return {
                    producto_id: item.producto_id,
                    precio: item.precio,
                    cantidad: item.cantidad,
                    fecha_expiracion: item.fecha_expiracion || null
                };
            })
        };
    }

    function firstError(error) {
        var response = error.response && error.response.data;
        if (response && response.errors) {
            var keys = Object.keys(response.errors);
            if (keys.length) return response.errors[keys[0]][0];
        }
        return response && (response.message || response.mensaje)
            ? (response.message || response.mensaje)
            : 'No fue posible guardar la compra.';
    }

    function validatePurchase() {
        var form = element('crear_compra');
        if (!form.checkValidity()) {
            form.reportValidity();
            return false;
        }
        if (!element('seleccionarProveedor').value) {
            Swal.fire({ icon: 'warning', title: 'Seleccione un proveedor' });
            return false;
        }
        if (!state.order.length) {
            Swal.fire({ icon: 'warning', title: 'Agregue productos', text: 'La compra debe contener al menos una línea.' });
            return false;
        }
        var invalid = state.order.find(function (id) {
            var item = state.items[id];
            return item.precio === '' || number(item.precio) < 0 || number(item.cantidad) < 1;
        });
        if (invalid) {
            state.filter = '';
            state.page = Math.ceil((state.order.indexOf(invalid) + 1) / state.pageSize);
            element('filtrarCarritoCompra').value = '';
            renderCart();
            Swal.fire({ icon: 'warning', title: 'Revise el producto', text: 'Complete el precio y una cantidad válida.' });
            return false;
        }
        return true;
    }

    function submitPurchase() {
        if (!validatePurchase()) return;
        var button = element('guardarCompraBtn');
        button.disabled = true;
        button.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Guardando...';

        axios.post('/producto/compra/guardar', payloadForPurchase()).then(function () {
            var deleteDraft = state.temporalId
                ? axios.delete('/ventas/temporales/' + encodeURIComponent(state.temporalId)).catch(function () {})
                : Promise.resolve();
            return deleteDraft.then(function () {
                resetPurchase();
                refreshDraftCount();
                Swal.fire({ icon: 'success', title: 'Compra guardada', text: 'La compra fue registrada correctamente.' });
            });
        }).catch(function (error) {
            Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: firstError(error) });
        }).finally(function () {
            button.disabled = false;
            button.innerHTML = '<i class="fa fa-save mr-1"></i> Guardar compra';
        });
    }

    function bindEvents() {
        element('abrirBuscadorCompra').addEventListener('click', openProductFinder);
        element('buscarProductoCompra').addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                openProductFinder();
            }
        });
        element('filtrarCarritoCompra').addEventListener('input', function () {
            state.filter = this.value.trim();
            state.page = 1;
            renderCart();
        });
        element('paginaAnteriorCompra').addEventListener('click', function () {
            state.page -= 1;
            renderCart();
        });
        element('paginaSiguienteCompra').addEventListener('click', function () {
            state.page += 1;
            renderCart();
        });
        element('productosCompraBody').addEventListener('click', function (event) {
            var removeButton = event.target.closest('[data-remove-id]');
            if (removeButton) removeProduct(Number(removeButton.dataset.removeId));
        });
        element('productosCompraBody').addEventListener('input', function (event) {
            var input = event.target.closest('[data-compra-field]');
            if (!input) return;
            var item = state.items[Number(input.dataset.id)];
            if (!item) return;
            var field = input.dataset.compraField;
            if (field === 'fecha_expiracion') {
                item.fecha_expiracion = input.value;
            } else {
                var oldValues = { subtotal: item.subtotal, isv: item.isv_total, total: item.total };
                item[field] = input.value;
                updateItemTotals(item, oldValues);
            }
            scheduleDraftSave();
        });
        element('productosCompraBody').addEventListener('change', function (event) {
            var input = event.target.closest('[data-compra-field="precio"]');
            if (!input || input.value === '') return;
            input.value = roundMoney(input.value).toFixed(2);
            var item = state.items[Number(input.dataset.id)];
            if (item) item.precio = input.value;
        });
        element('tipoPagoCompra').addEventListener('change', function () {
            updateDueDate();
            scheduleDraftSave();
        });
        element('crear_compra').addEventListener('input', function (event) {
            if (!event.target.matches('[data-compra-field]')) scheduleDraftSave();
        });
        $('#seleccionarProveedor').on('change', scheduleDraftSave);
        element('crear_compra').addEventListener('submit', function (event) {
            event.preventDefault();
            submitPurchase();
        });
        element('btnTemporalesCompra').addEventListener('click', function () { listDrafts(false); });
        element('btnNuevaCompra').addEventListener('click', startNewPurchase);
    }

    document.addEventListener('DOMContentLoaded', function () {
        initializeProvider();
        bindEvents();
        renderCart();
        loadPaymentTypes().then(function () {
            state.initialized = true;
            listDrafts(true);
        });
    });
}());
