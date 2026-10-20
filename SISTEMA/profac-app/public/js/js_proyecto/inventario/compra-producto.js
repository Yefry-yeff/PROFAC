(function () {
    'use strict';

    var config = window.compraProductoConfig || {};
    var state = {
        modo: config.modoInicial || 'compra',
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
        saving: false,
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

    function validDiscount(value) {
        var parsed = Number(value);
        return String(value).trim() !== '' && Number.isFinite(parsed) &&
            parsed >= 0 && parsed <= 100 && Math.abs(parsed * 100 - Math.round(parsed * 100)) < 0.000001;
    }

    function calculateItem(item) {
        item.descuento_invalido = state.modo === 'gasto' && !validDiscount(item.descuento_porcentaje);
        if (item.descuento_invalido) {
            item.subtotal_bruto = roundMoney(roundMoney(item.precio) * Math.max(0, Math.trunc(number(item.cantidad))));
            item.descuento_monto = item.subtotal = item.isv_total = item.total = 0;
            return;
        }
        var price = roundMoney(item.precio);
        var quantity = Math.max(0, Math.trunc(number(item.cantidad)));
        var units = state.modo === 'gasto' ? 1 : Math.max(1, Math.trunc(number(item.unidades_compra)));
        var subtotal = roundMoney(price * quantity * units);
        var discount = state.modo === 'gasto' ? roundMoney(subtotal * number(item.descuento_porcentaje) / 100) : 0;
        var net = roundMoney(subtotal - discount);
        var tax = roundMoney(net * number(item.isv_porcentaje) / 100);

        item.subtotal_bruto = subtotal;
        item.descuento_monto = discount;
        item.subtotal = net;
        item.isv_total = tax;
        item.total = roundMoney(net + tax);
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
        var invalidDiscount = state.modo === 'gasto' && state.order.some(function (id) { return state.items[id].descuento_invalido; });
        element('subTotalGeneral').textContent = money(state.totals.subtotal);
        element('isvGeneral').textContent = money(state.totals.isv);
        element('totalGeneral').textContent = money(state.totals.total);
        if (invalidDiscount) {
            ['subTotalGeneral', 'isvGeneral', 'totalGeneral'].forEach(function (id) {
                element(id).textContent = 'Revise descuento';
            });
        }
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
            output.textContent = item.descuento_invalido && output.dataset.compraOutput !== 'subtotal_bruto'
                ? 'Descuento inválido' : money(item[output.dataset.compraOutput]);
        });
        var input = document.querySelector('[data-compra-field="descuento_porcentaje"][data-id="' + id + '"]');
        if (input) {
            input.setCustomValidity(item.descuento_invalido ? 'Ingrese un descuento entre 0% y 100%, con hasta dos decimales.' : '');
            input.classList.toggle('is-invalid', item.descuento_invalido);
            input.setAttribute('aria-invalid', String(item.descuento_invalido));
        }
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
        if (state.modo === 'gasto') return expenseRowHtml(item);
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
            '<td class="d-none"></td><td class="d-none"></td>' +
            '<td class="cmp-money" data-compra-output="isv_total" data-id="' + item.producto_id + '">' + money(item.isv_total) + '</td>' +
            '<td class="cmp-money" data-compra-output="total" data-id="' + item.producto_id + '">' + money(item.total) + '</td>' +
            '<td><button type="button" class="cmp-remove" data-remove-id="' + item.producto_id + '" title="Quitar producto"><i class="fa fa-trash"></i></button></td>' +
        '</tr>';
    }

    function expenseRowHtml(item) {
        var id = item.producto_id;
        var tasas = config.tasasIsv.map(function (tasa) {
            return '<option value="' + tasa + '"' + (Number(item.isv_porcentaje) === tasa ? ' selected' : '') + '>' + tasa + '%</option>';
        }).join('');
        var unidades = '<option value="">Seleccione</option>' + config.unidadesMedida.map(function (unidad) {
            return '<option value="' + unidad.id + '"' + (Number(item.unidad_medida_id) === Number(unidad.id) ? ' selected' : '') + '>' +
                escapeHtml(unidad.nombre.trim()) + ' (' + escapeHtml(unidad.simbolo.trim()) + ')</option>';
        }).join('');
        return '<tr data-row-id="' + id + '">' +
            '<td><input class="form-control cmp-expense-name" aria-label="Nombre del producto" maxlength="255" required data-compra-field="nombre" data-id="' + id + '" value="' + escapeHtml(item.nombre) + '"></td>' +
            '<td><input type="number" aria-label="Precio" class="form-control cmp-number" min="0" max="999999999.99" step="0.01" required data-compra-field="precio" data-id="' + id + '" value="' + escapeHtml(item.precio) + '"></td>' +
            '<td><input type="number" aria-label="Cantidad" class="form-control cmp-number" min="1" max="1000000" step="1" required data-compra-field="cantidad" data-id="' + id + '" value="' + escapeHtml(item.cantidad) + '"></td>' +
            '<td><select aria-label="Unidad de medida" class="form-control cmp-expense-unit" required data-compra-field="unidad_medida_id" data-id="' + id + '">' + unidades + '</select></td>' +
            '<td><select aria-label="Tasa ISV" class="form-control cmp-tax-rate" data-compra-field="isv_porcentaje" data-id="' + id + '">' + tasas + '</select></td>' +
            '<td class="cmp-money" data-compra-output="subtotal_bruto" data-id="' + id + '">' + money(item.subtotal_bruto) + '</td>' +
            '<td><input type="number" aria-label="Descuento porcentual" class="form-control cmp-number" min="0" max="100" step="0.01" required data-compra-field="descuento_porcentaje" data-id="' + id + '" value="' + escapeHtml(item.descuento_porcentaje) + '">' +
            '<small class="text-muted" data-compra-output="descuento_monto" data-id="' + id + '">' + money(item.descuento_monto) + '</small></td>' +
            '<td class="cmp-money" data-compra-output="subtotal" data-id="' + id + '">' + money(item.subtotal) + '</td>' +
            '<td class="cmp-money" data-compra-output="isv_total" data-id="' + id + '">' + money(item.isv_total) + '</td>' +
            '<td class="cmp-money" data-compra-output="total" data-id="' + id + '">' + money(item.total) + '</td>' +
            '<td><button type="button" class="cmp-remove" data-remove-id="' + id + '" title="Quitar línea"><i class="fa fa-trash"></i></button></td></tr>';
    }

    function renderMode() {
        var gasto = state.modo === 'gasto';
        element('modoDocumentoCompra').value = state.modo;
        element('tituloDocumentoCompra').textContent = gasto ? 'Nuevo gasto' : 'Nueva compra';
        element('datosDocumentoCompra').textContent = gasto ? 'Datos del gasto' : 'Datos de la compra';
        element('ordenDocumentoCompra').classList.toggle('d-none', gasto);
        element('opcionReporteComprasGasto').classList.toggle('d-none', !gasto);
        element('busquedaCatalogoCompra').classList.toggle('d-none', gasto);
        element('herramientasGasto').classList.toggle('d-none', !gasto);
        element('columnaUnidadCompra').textContent = gasto ? 'Unidad de medida' : 'Unidad de compra';
        element('columnaVencimientoCompra').textContent = gasto ? 'Tasa ISV' : 'Vencimiento';
        element('columnaDescuentoGasto').classList.toggle('d-none', !gasto);
        element('columnaNetoGasto').classList.toggle('d-none', !gasto);
        element('etiquetaSubtotalCompra').textContent = gasto ? 'Subtotal neto' : 'Subtotal';
        element('guardarCompraBtn').innerHTML = '<i class="fa fa-save mr-1"></i> Guardar ' + state.modo;
    }

    function addExpense() {
        var id = state.order.length ? Math.max.apply(null, state.order) + 1 : 1;
        state.items[id] = {
            producto_id: id, nombre: '', precio: '', cantidad: 1, unidades_compra: 1,
            unidad_medida_id: '', descuento_porcentaje: 0, descuento_monto: 0, subtotal_bruto: 0,
            isv_porcentaje: 0, subtotal: 0, isv_total: 0, total: 0
        };
        state.order.push(id);
        recalculateAll();
        state.filter = '';
        element('filtrarCarritoCompra').value = '';
        state.page = Math.ceil(state.order.length / state.pageSize);
        renderCart();
        scheduleDraftSave();
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
        visible.forEach(renderRowAmounts);
        empty.classList.toggle('d-none', visible.length > 0);
        if (!visible.length) {
            empty.lastChild.nodeValue = ids.length === 0 && state.order.length > 0
                ? ' No hay coincidencias en el carrito.'
                : (state.modo === 'gasto' ? ' No hay productos en el gasto.' : ' No hay productos en la compra.');
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
        if (state.modo !== 'compra') return;
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
        if (state.modo !== 'compra') return;
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
            modo: state.modo,
            incluir_reporte_compras: element('incluirReporteComprasGasto').checked,
            numero_factura: element('numero_factura').value,
            cai: element('cai').value,
            proveedor: provider && provider.id ? { id: provider.id, text: provider.text, rtn: element('rtnProveedorCompra').value } : null,
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
                unidad_medida_id: item.unidad_medida_id,
                descuento_porcentaje: item.descuento_porcentaje,
                precio: item.precio,
                cantidad: item.cantidad,
                fecha_expiracion: item.fecha_expiracion
            };
        });
    }

    function hasDraftContent() {
        var header = headerData();
        return state.order.length > 0 || !!(header.numero_factura || header.cai || header.proveedor || header.fecha_entrega ||
            (state.modo === 'gasto' && header.incluir_reporte_compras));
    }

    function draftTitle() {
        var header = headerData();
        var invoice = (state.modo === 'gasto' ? 'Gasto · ' : '') + (header.numero_factura ? 'Factura ' + header.numero_factura : 'Compra sin número');
        return header.proveedor ? invoice + ' · ' + header.proveedor.text : invoice;
    }

    function draftPayload() {
        return {
            id: state.temporalId,
            tipo: 'compra',
            codigo_tipo: state.modo,
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
        if (!state.initialized || state.restoring || !hasDraftContent()) return Promise.resolve(true);

        setDraftStatus('Guardando...');
        state.draftQueue = state.draftQueue.catch(function () {}).then(function () {
            return axios.post('/ventas/temporales', draftPayload()).then(function (response) {
                state.temporalId = response.data.id;
                setDraftStatus('Guardado temporal');
                refreshDraftCount();
                return true;
            });
        }).catch(function () {
            setDraftStatus('No se pudo guardar');
            return false;
        });
        return state.draftQueue;
    }

    function scheduleDraftSave() {
        if (!state.initialized || state.restoring || state.saving) return;
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
            confirmButtonText: 'Continuar registro',
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
        element('rtnProveedorCompra').value = provider && provider.rtn ? provider.rtn : '';
        if (provider && provider.id && provider.rtn === undefined) {
            axios.get('/producto/lista/proveedores', { params: { id: provider.id } }).then(function (response) {
                if (String(select.val()) !== String(provider.id)) return;
                var current = response.data.results[0];
                if (!current) throw new Error('Proveedor no disponible');
                element('rtnProveedorCompra').value = current.rtn || '';
            }).catch(function () {
                Swal.fire({ icon: 'error', title: 'No se pudo consultar el RTN', text: 'Vuelva a seleccionar el proveedor.' });
            });
        }
    }

    function restoreDraftContent(content, temporalId) {
        var header = content.cabecera || {};
        state.restoring = true;
        state.modo = header.modo === 'gasto' ? 'gasto' : 'compra';
        renderMode();
        state.temporalId = Number(temporalId);
        element('numero_factura').value = header.numero_factura || '';
        element('cai').value = header.cai || '';
        element('incluirReporteComprasGasto').checked = header.incluir_reporte_compras === true || header.incluir_reporte_compras === 1;
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
                nombre: saved.nombre || (state.modo === 'gasto' ? '' : 'Producto #' + id),
                codigo_barra: saved.codigo_barra || '',
                codigo_estatal: saved.codigo_estatal || '',
                marca_nombre: saved.marca_nombre || '',
                imagen: saved.imagen || '',
                isv_porcentaje: number(saved.isv_porcentaje),
                unidad: saved.unidad || '',
                unidades_compra: Math.max(1, Math.trunc(number(saved.unidades_compra))),
                unidad_compra_id: Number(saved.unidad_compra_id),
                unidad_medida_id: saved.unidad_medida_id || '',
                descuento_porcentaje: saved.descuento_porcentaje === undefined ? 0 : saved.descuento_porcentaje,
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
        renderMode();
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
            title: '¿Iniciar un nuevo registro?',
            text: 'El registro actual quedará guardado en temporales durante 24 horas.',
            showCancelButton: true,
            confirmButtonText: 'Guardar e iniciar nueva',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (result.isConfirmed) saveDraft().then(function (saved) {
                if (saved) resetPurchase();
                else Swal.fire({ icon: 'error', title: 'No se pudo guardar el temporal', text: 'Los datos actuales se conservaron.' });
            });
        });
    }

    function payloadForPurchase() {
        var header = headerData();
        return {
            numero_factura: header.numero_factura,
            cai: header.cai,
            incluir_reporte_compras: state.modo === 'gasto' && header.incluir_reporte_compras,
            seleccionarProveedorId: header.proveedor ? header.proveedor.id : '',
            tipoPagoCompra: header.tipo_pago,
            fecha_vencimiento: header.fecha_vencimiento,
            fecha_emision: header.fecha_emision,
            fecha_entrega: header.fecha_entrega,
            productos: serializableItems().map(function (item) {
                return {
                    producto_id: item.producto_id,
                    nombre: item.nombre,
                    isv_porcentaje: item.isv_porcentaje,
                    unidad_medida_id: item.unidad_medida_id,
                    descuento_porcentaje: item.descuento_porcentaje,
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
            return item.precio === '' || number(item.precio) < 0 || number(item.cantidad) < 1 ||
                (state.modo === 'gasto' && (!item.nombre.trim() ||
                    !config.unidadesMedida.some(function (unidad) { return Number(unidad.id) === Number(item.unidad_medida_id); }) ||
                    !validDiscount(item.descuento_porcentaje)));
        });
        if (invalid) {
            state.filter = '';
            state.page = Math.ceil((state.order.indexOf(invalid) + 1) / state.pageSize);
            element('filtrarCarritoCompra').value = '';
            renderCart();
            Swal.fire({ icon: 'warning', title: 'Revise el producto', text: state.modo === 'gasto' ? 'Complete nombre, unidad, precio, cantidad y descuento entre 0% y 100%.' : 'Complete el precio y una cantidad válida.' });
            return false;
        }
        return true;
    }

    function submitPurchase() {
        if (state.saving || !validatePurchase()) return;
        state.saving = true;
        window.clearTimeout(state.draftTimer);
        var payload = payloadForPurchase();
        var endpoint = state.modo === 'gasto' ? '/gastos/guardar' : '/producto/compra/guardar';
        var printWindow = state.modo === 'gasto' ? window.open('', '_blank') : null;
        if (printWindow) printWindow.opener = null;
        element('crear_compra').inert = true;
        ['btnNuevaCompra', 'btnTemporalesCompra', 'modoDocumentoCompra'].forEach(function (id) { element(id).disabled = true; });
        var button = element('guardarCompraBtn');
        button.disabled = true;
        button.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Guardando...';

        state.draftQueue.then(function () {
            return axios.post(endpoint, payload);
        }).then(function (response) {
            var printUrl = response.data.gasto && response.data.gasto.impresion_url;
            if (printUrl && printWindow && !printWindow.closed) printWindow.location.href = printUrl;
            var deleteDraft = state.temporalId
                ? axios.delete('/ventas/temporales/' + encodeURIComponent(state.temporalId)).catch(function () {
                    Swal.fire({ icon: 'warning', title: 'Registro guardado', text: 'No se pudo eliminar su temporal. Elimínelo antes de intentar usarlo de nuevo para evitar duplicados.' });
                    return false;
                })
                : Promise.resolve();
            return deleteDraft.then(function (deleted) {
                resetPurchase();
                refreshDraftCount();
                if (deleted !== false) Swal.fire({
                    icon: 'success', title: 'Registro guardado',
                    html: printUrl
                        ? 'El gasto se guardó correctamente.<br><a class="btn btn-primary mt-3" target="_blank" rel="noopener" href="' + escapeHtml(printUrl) + '">Abrir documento de impresión</a>'
                        : 'El registro se guardó correctamente.'
                });
            });
        }).catch(function (error) {
            if (printWindow && !printWindow.closed) printWindow.close();
            Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: firstError(error) });
        }).finally(function () {
            state.saving = false;
            element('crear_compra').inert = false;
            ['btnNuevaCompra', 'btnTemporalesCompra', 'modoDocumentoCompra'].forEach(function (id) { element(id).disabled = false; });
            button.disabled = false;
            renderMode();
        });
    }

    function bindEvents() {
        $('#proveedorCompraModal').on('show.bs.modal', function () {
            document.body.classList.add('cmp-provider-open');
        }).on('hidden.bs.modal', function () {
            document.body.classList.remove('cmp-provider-open');
        });
        element('agregarLineaGasto').addEventListener('click', addExpense);
        element('incluirReporteComprasGasto').addEventListener('change', scheduleDraftSave);
        element('modoDocumentoCompra').addEventListener('change', function () {
            var next = this.value;
            element('modoDocumentoCompra').value = state.modo;
            if (state.saving) return;
            function switchMode() {
                resetPurchase();
                state.modo = next;
                renderMode();
            }
            if (!hasDraftContent()) return switchMode();
            Swal.fire({
                icon: 'question', title: '¿Cambiar tipo de registro?',
                text: 'El registro actual se guardará en temporales antes de cambiar.',
                showCancelButton: true, confirmButtonText: 'Guardar y cambiar', cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (result.isConfirmed) saveDraft().then(function (saved) {
                    if (saved) switchMode();
                    else Swal.fire({ icon: 'error', title: 'No se pudo guardar el temporal', text: 'Los datos actuales se conservaron.' });
                });
            });
        });
        element('proveedorCompraForm').addEventListener('submit', function (event) {
            event.preventDefault();
            var form = this;
            if (!form.reportValidity()) return;
            var button = element('guardarProveedorCompra');
            var error = element('proveedorCompraError');
            button.disabled = true;
            error.classList.add('d-none');
            axios.post('/proveedores/crear', new FormData(form)).then(function (response) {
                setProvider(response.data.proveedor);
                scheduleDraftSave();
                $('#proveedorCompraModal').modal('hide');
                form.reset();
            }).catch(function (failure) {
                error.textContent = firstError(failure);
                error.classList.remove('d-none');
            }).finally(function () { button.disabled = false; });
        });
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
            if (field === 'fecha_expiracion' || field === 'nombre') {
                item[field] = input.value;
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
        $('#seleccionarProveedor').on('select2:select', function (event) {
            element('rtnProveedorCompra').value = event.params.data.rtn || '';
            scheduleDraftSave();
        }).on('select2:clear', function () {
            element('rtnProveedorCompra').value = '';
            scheduleDraftSave();
        }).on('change', scheduleDraftSave);
        element('crear_compra').addEventListener('submit', function (event) {
            event.preventDefault();
            submitPurchase();
        });
        element('btnTemporalesCompra').addEventListener('click', function () { listDrafts(false); });
        element('btnNuevaCompra').addEventListener('click', startNewPurchase);
    }

    document.addEventListener('DOMContentLoaded', function () {
        initializeProvider();
        renderMode();
        bindEvents();
        renderCart();
        loadPaymentTypes().then(function () {
            state.initialized = true;
            listDrafts(true);
        });
    });
}());
