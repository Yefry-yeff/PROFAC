@props([
    'idModal'     => 'buscadorProductoModal',
    'callback'    => 'onProductoSeleccionado',
    'bodegaIdVar' => '',
    'urlBuscar'   => '/productos/buscar',
    'urlTop'      => '/productos/buscar/top-vendidos',
    'urlFiltros'  => '/productos/buscar',
    'topLabel'    => '',
    'expoId'      => null,
])
@php
    $suf = preg_replace('/[^a-zA-Z0-9]/', '_', $idModal);
@endphp

{{-- ============================================================
     Componente reutilizable: Buscador de Productos
     Uso: <x-buscador-producto id-modal="miModalId" callback="miFuncionJS" />
     window['abrirBuscador_SUFFIX']('prefill texto');
     Callback recibe: { id, nombre, codigo_barra, codigo_estatal, isv, stock, imagen, marca_nombre }
     ============================================================ --}}

<div class="modal fade" id="{{ $idModal }}" tabindex="-1" role="dialog"
     aria-labelledby="{{ $suf }}_label" aria-hidden="true"
     style="z-index: 20050;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"
         role="document"
         style="max-width: min(96vw, 1200px);">
        {{-- Flexbox column para que el header+filtros sean fijos y el body ocupe el resto --}}
        <div class="modal-content border-0 shadow-lg"
             style="border-radius: 14px; overflow: hidden; display: flex; flex-direction: column; max-height: 92vh;">

            {{-- ══════════ HEADER ══════════ --}}
            <div class="modal-header border-0"
                 style="background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
                        flex-direction: column; align-items: stretch;
                        padding: .9rem 1.1rem .8rem; flex-shrink: 0;">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="modal-title text-white mb-0 font-weight-bold"
                        id="{{ $suf }}_label"
                        style="font-size: 1rem; letter-spacing: .3px;">
                        <i class="fa fa-search mr-2" style="opacity:.85;"></i>Buscar Producto
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"
                            style="opacity: .8; font-size: 1.4rem; margin: -4px -2px 0 0; line-height:1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                {{-- Input de búsqueda --}}
                <div class="input-group"
                     style="box-shadow: 0 2px 12px rgba(0,0,0,.25); border-radius: 9px; overflow: hidden;">
                    <input type="text"
                           id="{{ $suf }}_input"
                           class="form-control border-0"
                           placeholder="Escriba nombre, código de barras, código estatal o ID…"
                           autocomplete="off"
                           style="font-size: .95rem; padding: .6rem .9rem; background: #fff;">
                    <div class="input-group-append">
                        <button type="button"
                                id="{{ $suf }}_clearBtn"
                                class="btn btn-light border-0 d-none"
                                title="Limpiar búsqueda"
                                style="padding: .6rem .85rem; background:#fff; color:#777; font-size:.85rem;">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ══════════ BARRA DE FILTROS ══════════ --}}
            <div class="border-bottom"
                 style="background:#f4f6fb; padding: .55rem .9rem; flex-shrink: 0;">
                <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">

                    {{-- Categoría --}}
                    <div class="d-flex align-items-center" style="gap:5px;">
                        <span style="font-size:.72rem; color:#555; white-space:nowrap; font-weight:600;">
                            <i class="fa fa-tag" style="color:#1a73e8;"></i> Categoría
                        </span>
                        <select id="{{ $suf }}_filtroCategoria"
                                class="form-control form-control-sm"
                                style="font-size:.78rem; min-width:130px; max-width:190px;
                                       border-radius:6px; border-color:#ced4da;">
                            <option value="">Todas</option>
                        </select>
                    </div>

                    {{-- Marca --}}
                    <div class="d-flex align-items-center" style="gap:5px;">
                        <span style="font-size:.72rem; color:#555; white-space:nowrap; font-weight:600;">
                            <i class="fa fa-star" style="color:#1a73e8;"></i> Marca
                        </span>
                        <select id="{{ $suf }}_filtroMarca"
                                class="form-control form-control-sm"
                                style="font-size:.78rem; min-width:120px; max-width:170px;
                                       border-radius:6px; border-color:#ced4da;">
                            <option value="">Todas</option>
                        </select>
                    </div>

                    {{-- Solo con stock --}}
                    <div class="custom-control custom-switch ml-1">
                        <input type="checkbox" class="custom-control-input" checked
                               id="{{ $suf }}_conStock">
                        <label class="custom-control-label"
                               for="{{ $suf }}_conStock"
                               style="font-size:.78rem; cursor:pointer; white-space:nowrap;">
                            Solo con stock
                        </label>
                    </div>

                    {{-- Contador + spinner (derecha) --}}
                    <div class="ml-auto d-flex align-items-center" style="gap:6px;">
                        <span id="{{ $suf }}_info"
                              style="font-size:.72rem; color:#888;"></span>
                        <div class="spinner-border spinner-border-sm text-primary d-none"
                             id="{{ $suf }}_spinner"
                             role="status"
                             style="width:.85rem; height:.85rem; border-width:2px;"></div>
                    </div>

                </div>
            </div>

            {{-- ══════════ RESULTADOS (flex-grow, scroll propio) ══════════ --}}
            <div class="modal-body p-0"
                 style="flex: 1 1 auto; overflow: hidden; display: flex; flex-direction: column; min-height: 0;">

                {{-- Barra de progreso animada durante la búsqueda --}}
                <div id="{{ $suf }}_loadbar" class="d-none"
                     style="height:3px; flex-shrink:0;
                            background: linear-gradient(90deg, #1a73e8 0%, #4dabf7 40%, #1a73e8 80%);
                            background-size: 200% 100%;
                            animation: bsp_loadbar 1s linear infinite;"></div>

                <div id="{{ $suf }}_grid"
                     style="flex: 1 1 auto; overflow-y: auto; overflow-x: hidden; padding: .5rem;">
                    {{-- Label dinámico de sección (ej: Más vendidos / resultados) --}}
                    <div id="{{ $suf }}_seccion" class="d-none px-1 pb-1 pt-2">
                        <small class="font-weight-bold text-uppercase"
                               id="{{ $suf }}_seccion_txt"
                               style="font-size:.68rem; color:#1a73e8; letter-spacing:.5px;"></small>
                    </div>
                    <div class="row no-gutters" id="{{ $suf }}_results">
                        <div class="col-12 text-center py-5">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <p class="text-muted mb-0" style="font-size:.9rem;">Cargando…</p>
                        </div>
                    </div>
                </div>

                {{-- Paginación pegada al fondo --}}
                <div id="{{ $suf }}_pagination"
                     class="border-top bg-white text-center py-1"
                     style="flex-shrink: 0;"></div>

            </div>

        </div>{{-- /modal-content --}}
    </div>
</div>

@push('styles')
<style>
/* Buscador de producto: z-index por encima del sidebar (max z-index sidebar = 10000) */
#{{ $idModal }}  { z-index: 20050 !important; }
.modal-backdrop  { z-index: 20040 !important; }
/* Barra de carga animada — @@keyframes evita que Blade interprete el @ */
@@keyframes bsp_loadbar {
    0%   { background-position: 100% 0; }
    100% { background-position: -100% 0; }
}
/* Pulso de los skeleton cards */
@@keyframes bsp_pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: .45; }
}
.bsp-skeleton { animation: bsp_pulse 1.2s ease-in-out infinite; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    var MODAL_ID      = '{{ $idModal }}';
    var CB            = '{{ $callback }}';
    var IMG           = '{{ asset('catalogo') }}';
    var S             = '{{ $suf }}';
    var BVAR          = '{{ $bodegaIdVar }}';   // nombre de var JS global que contiene el bodega_id (solo traslados)
    var URL_BUSCAR    = '{{ $urlBuscar }}';
    var URL_TOP       = '{{ $urlTop }}';
    var URL_FILTROS   = '{{ $urlFiltros }}';
    var TOP_LABEL     = '{{ $topLabel }}';
    var EXPO_ID       = @json($expoId);

    var page          = 1;
    var query         = '';
    var catId         = '';
    var marcaId       = '';
    var conStock      = true;
    var timer         = null;
    var filtersLoaded        = false;
    var filtersLoadedBodega  = '';   // rastrea para qué bodega se cargaron los filtros
    var reqSeq        = 0;      // contador de peticiones; la respuesta sólo se procesa si coincide
    var isPreview     = false;  // true cuando se muestran los más vendidos

    /* ── loading helpers ────────────────────── */
    function showLoading() {
        var lb  = el(S + '_loadbar');
        var sp  = el(S + '_spinner');
        var inf = el(S + '_info');
        var pg  = el(S + '_pagination');
        if (lb)  lb.classList.remove('d-none');
        if (sp)  sp.classList.remove('d-none');
        if (inf) inf.textContent = '';
        if (pg)  pg.innerHTML = '';
        // Skeleton placeholder — respuesta visual inmediata
        var cont = el(S + '_results');
        if (cont) {
            var sk = '';
            for (var i = 0; i < 6; i++) {
                sk += '<div class="col-6 col-sm-4 col-md-3 col-lg-2 p-1">' +
                      '<div class="card border-0 h-100 bsp-skeleton" style="border-radius:10px;overflow:hidden;background:#f0f2f5;">' +
                      '<div style="height:78px;background:#dde1e8;"></div>' +
                      '<div class="p-2">' +
                      '<div style="height:11px;background:#dde1e8;border-radius:4px;margin-bottom:5px;"></div>' +
                      '<div style="height:9px;background:#e5e8ee;border-radius:4px;width:65%;"></div>' +
                      '</div></div></div>';
            }
            cont.innerHTML = sk;
        }
    }
    function hideLoading() {
        var lb = el(S + '_loadbar');
        var sp = el(S + '_spinner');
        if (lb) lb.classList.add('d-none');
        if (sp) sp.classList.add('d-none');
    }

    /* ── helpers ────────────────────────────── */
    function el(id) { return document.getElementById(id); }
    function contextParams() {
        return EXPO_ID ? { expo_id: EXPO_ID } : {};
    }
    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ── cargar filtros (una sola vez) ──────── */
    function loadFilters() {
        if (filtersLoaded) return;
        var filterParams = contextParams();
        if (BVAR) { var bvId = window[BVAR]; if (bvId) filterParams.bodega_id = bvId; }
        Promise.all([
            axios.get(URL_FILTROS + '/categorias', { params: filterParams }),
            axios.get(URL_FILTROS + '/marcas',     { params: filterParams })
        ]).then(function (rs) {
            var selCat   = el(S + '_filtroCategoria');
            var selMarca = el(S + '_filtroMarca');
            // Limpiar opciones existentes (excepto la primera "Todas")
            while (selCat.options.length > 1)   selCat.remove(1);
            while (selMarca.options.length > 1) selMarca.remove(1);
            rs[0].data.forEach(function (c) {
                var o = document.createElement('option');
                o.value = c.id; o.textContent = c.text;
                selCat.appendChild(o);
            });
            rs[1].data.forEach(function (m) {
                var o = document.createElement('option');
                o.value = m.id; o.textContent = m.text;
                selMarca.appendChild(o);
            });
            filtersLoaded = true;
            filtersLoadedBodega = BVAR ? (window[BVAR] || '') : '';
        }).catch(function () { /* silencioso */ });
    }

    /* ── búsqueda ───────────────────────────── */
    function triggerSearch() {
        clearTimeout(timer);
        page = 1;
        // Si hay query o filtros activos → búsqueda normal; si no → top vendidos
        if (query === '' && catId === '' && marcaId === '' && !conStock) {
            loadTopVendidos();
        } else {
            doSearch();
        }
    }

    function loadTopVendidos() {
        isPreview = true;
        var mySeq = ++reqSeq;
        showLoading();
        var tvParams = contextParams();
        if (BVAR) { var bvId = window[BVAR]; if (bvId) tvParams.bodega_id = bvId; }
        axios.get(URL_TOP, { params: tvParams })
            .then(function (r) {
                if (reqSeq !== mySeq) return;   // petición obsoleta, ignorar
                hideLoading();
                renderPreview(r.data);
            }).catch(function () {
                if (reqSeq !== mySeq) return;
                hideLoading();
            });
    }

    function renderPreview(items) {
        var noimg = IMG + '/noimage.png';
        var cont  = el(S + '_results');
        var sec   = el(S + '_seccion');
        var secTxt = el(S + '_seccion_txt');
        sec.classList.remove('d-none');
        secTxt.innerHTML = TOP_LABEL
            ? '<i class="fa fa-exchange mr-1" style="color:#1AA689;"></i> ' + TOP_LABEL
            : '<i class="fa fa-fire mr-1" style="color:#e53935;"></i> Más vendidos';
        el(S + '_info').textContent = '';
        el(S + '_pagination').innerHTML = '';

        if (!items.length) {
            sec.classList.add('d-none');
            cont.innerHTML = '<div class="col-12 text-center py-5"><i class="fa fa-search fa-2x mb-3 d-block" style="color:#ccc;"></i><span class="text-muted">Escriba para buscar productos</span></div>';
            return;
        }

        injectHoverStyles();
        var html = '';
        items.forEach(function (p) {
            html += buildCard(p, noimg);
        });
        cont.innerHTML = html;
        el(S + '_grid').scrollTop = 0;
    }

    function doSearch() {
        isPreview = false;
        var sec = el(S + '_seccion');
        if (query !== '' || catId !== '' || marcaId !== '' || conStock) {
            sec.classList.remove('d-none');
            el(S + '_seccion_txt').textContent = 'Resultados de búsqueda';
        } else {
            sec.classList.add('d-none');
        }
        var mySeq = ++reqSeq;   // incrementar ANTES de lanzar la petición
        showLoading();

        var srchParams = {
            q:            query,
            categoria_id: catId,
            marca_id:     marcaId,
            con_stock:    conStock ? 1 : 0,
            page:         page,
            expo_id:      EXPO_ID || undefined
        };
        if (BVAR) { var bvId2 = window[BVAR]; if (bvId2) srchParams.bodega_id = bvId2; }
        axios.get(URL_BUSCAR, { params: srchParams }).then(function (r) {
            if (reqSeq !== mySeq) return;   // el usuario ya escribió algo más, ignorar
            hideLoading();
            renderResults(r.data);
        }).catch(function () {
            if (reqSeq !== mySeq) return;
            hideLoading();
            el(S + '_info').textContent = '';
            el(S + '_results').innerHTML =
                '<div class="col-12 text-center py-5 text-danger">' +
                '<i class="fa fa-exclamation-circle fa-2x mb-2 d-block"></i>' +
                '<span>Error al realizar la búsqueda. Intente de nuevo.</span></div>';
            el(S + '_pagination').innerHTML = '';
        });
    }

    /* ── helpers de render ──────────────────── */
    function injectHoverStyles() {
        if (document.getElementById('__bsp_styles')) return;
        var st = document.createElement('style');
        st.id  = '__bsp_styles';
        st.textContent =
            '.bsp-card{cursor:pointer;transition:transform .15s,box-shadow .15s,border-color .15s!important;}' +
            '.bsp-card:hover{transform:translateY(-3px);box-shadow:0 8px 22px rgba(26,115,232,.2)!important;border-color:#1a73e8!important;}' +
            '.bsp-card:active{transform:none;box-shadow:none!important;}';
        document.head.appendChild(st);
    }

    function buildCard(p, noimg) {
        var img   = p.imagen ? IMG + '/' + p.imagen : noimg;
        var stock = parseFloat(p.stock) || 0;
        var sCls  = stock > 0 ? 'badge-success' : 'badge-secondary';
        var sTxt  = stock > 0 ? 'Stock: ' + stock : 'Sin stock';
        var mHtml = p.marca_nombre
            ? '<span class="badge badge-light border d-block mt-1"' +
              ' style="font-size:.59rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"' +
              ' title="' + esc(p.marca_nombre) + '">' + esc(p.marca_nombre) + '</span>'
            : '';
        var vendido = p.total_vendido
            ? '<span class="badge badge-warning d-block mt-1" style="font-size:.58rem;">' +
              '<i class="fa fa-fire"></i> ' + p.total_vendido + ' vendido(s)</span>'
            : '';
        var safe = esc(JSON.stringify(p));
        return '<div class="col-6 col-sm-4 col-md-3 col-lg-2 p-1">' +
                 '<div class="card h-100 border bsp-card"' +
                      ' style="border-radius:10px;overflow:hidden;"' +
                      ' data-pj="' + safe + '"' +
                      ' onclick="window[\'__bspSel_' + S + '\'](JSON.parse(this.dataset.pj))">' +
                   '<div style="height:78px;background:#f7f8fc;display:flex;align-items:center;' +
                        'justify-content:center;overflow:hidden;border-bottom:1px solid #eef0f4;">' +
                     '<img src="' + img + '" style="max-width:100%;max-height:76px;object-fit:contain;"' +
                          ' loading="lazy" onerror="this.onerror=null;this.src=\'' + noimg + '\'">' +
                   '</div>' +
                   '<div class="card-body p-2" style="font-size:.75rem;">' +
                     '<p class="mb-1 font-weight-bold text-dark text-truncate"' +
                        ' style="line-height:1.25;font-size:.78rem;" title="' + esc(p.nombre) + '">' + esc(p.nombre) + '</p>' +
                     '<p class="mb-1 text-muted" style="font-size:.64rem;line-height:1.45;">' +
                       '<b>ID:</b> ' + p.id +
                       (p.codigo_barra    ? '<br><b>C.B:</b> '  + esc(p.codigo_barra)   : '') +
                       (p.codigo_estatal  ? '<br><b>C.E:</b> '  + esc(p.codigo_estatal) : '') +
                     '</p>' +
                     '<span class="badge ' + sCls + '" style="font-size:.6rem;">' + sTxt + '</span>' +
                     mHtml + vendido +
                   '</div>' +
                 '</div>' +
               '</div>';
    }

    /* ── render resultados normales ─────────── */
    function renderResults(data) {
        el(S + '_info').textContent = data.total.toLocaleString() + ' resultado(s)';
        var cont  = el(S + '_results');
        var noimg = IMG + '/noimage.png';

        if (!data.data.length) {
            cont.innerHTML =
                '<div class="col-12 text-center py-5">' +
                '<i class="fa fa-search fa-2x mb-3 d-block" style="color:#ccc;"></i>' +
                '<span class="text-muted">' +
                (query
                    ? 'Sin resultados para <strong>"' + esc(query) + '"</strong>'
                    : 'Sin resultados') +
                '</span></div>';
            el(S + '_pagination').innerHTML = '';
            return;
        }

        injectHoverStyles();
        var html = '';
        data.data.forEach(function (p) { html += buildCard(p, noimg); });
        cont.innerHTML = html;
        renderPagination(data.current_page, data.last_page);
        el(S + '_grid').scrollTop = 0;
    }

    /* ── paginación ─────────────────────────── */
    function renderPagination(cur, last) {
        var wrap = el(S + '_pagination');
        if (last <= 1) { wrap.innerHTML = ''; return; }
        var h = '<nav><ul class="pagination pagination-sm justify-content-center flex-wrap mb-0 py-1">';
        h += pBtn(cur > 1, '‹', cur - 1);
        var s = Math.max(1, cur - 2), e = Math.min(last, cur + 2);
        if (s > 1) {
            h += pBtn(true, '1', 1);
            if (s > 2) h += '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }
        for (var i = s; i <= e; i++) {
            h += '<li class="page-item' + (i === cur ? ' active' : '') + '">' +
                 '<a class="page-link" href="#"' +
                 ' onclick="window[\'__bspPage_' + S + '\'](' + i + ');return false;">' + i + '</a></li>';
        }
        if (e < last) {
            if (e < last - 1) h += '<li class="page-item disabled"><span class="page-link">…</span></li>';
            h += pBtn(true, String(last), last);
        }
        h += pBtn(cur < last, '›', cur + 1);
        h += '</ul></nav>';
        wrap.innerHTML = h;
    }
    function pBtn(ok, txt, p) {
        return '<li class="page-item' + (ok ? '' : ' disabled') + '">' +
               '<a class="page-link" href="#"' +
               ' onclick="window[\'__bspPage_' + S + '\'](' + p + ');return false;">' + txt + '</a></li>';
    }

    /* ── API pública ────────────────────────── */
    window['__bspPage_' + S] = function (p) { page = p; doSearch(); };

    window['__bspSel_' + S] = function (producto) {
        if (typeof window[CB] === 'function') { window[CB](producto); }
        $('#' + MODAL_ID).modal('hide');
    };

    window['abrirBuscador_' + S] = function (prefill, resetFiltros) {
        if (resetFiltros) {
            el(S + '_filtroCategoria').value = '';
            el(S + '_filtroMarca').value     = '';
            catId = ''; marcaId = '';
        }
        var txt = (prefill != null) ? String(prefill).trim() : '';
        el(S + '_input').value = txt;
        query = txt;
        el(S + '_clearBtn').classList.toggle('d-none', txt === '');
        page = 1;
        $('#' + MODAL_ID).modal('show');
    };

    /* ── eventos ────────────────────────────── */

    // Escritura en tiempo real — debounce 450 ms
    el(S + '_input').addEventListener('input', function () {
        clearTimeout(timer);
        query = this.value.trim();
        page  = 1;
        el(S + '_clearBtn').classList.toggle('d-none', query === '');
        el(S + '_spinner').classList.remove('d-none');
        el(S + '_info').textContent = '…';
        if (query === '' && catId === '' && marcaId === '' && !conStock) {
            // Volver a mostrar los más vendidos si se borraron todos los filtros
            timer = setTimeout(loadTopVendidos, 300);
        } else {
            timer = setTimeout(doSearch, 300);
        }
    });

    el(S + '_clearBtn').addEventListener('click', function () {
        clearTimeout(timer);
        el(S + '_input').value = '';
        query = '';
        page  = 1;
        this.classList.add('d-none');
        el(S + '_input').focus();
        doSearch();
    });

    el(S + '_filtroCategoria').addEventListener('change', function () {
        catId = this.value;
        clearTimeout(timer);
        triggerSearch();
    });

    el(S + '_filtroMarca').addEventListener('change', function () {
        marcaId = this.value;
        clearTimeout(timer);
        triggerSearch();
    });

    el(S + '_conStock').addEventListener('change', function () {
        conStock = this.checked;
        clearTimeout(timer);
        triggerSearch();
    });

    $('#' + MODAL_ID).on('show.bs.modal', function () {
        // Forzar z-index del backdrop por encima del sidebar
        setTimeout(function() {
            var bd = document.querySelector('.modal-backdrop:last-of-type');
            if (bd) bd.style.zIndex = '20040';
        }, 10);
        // Si cambió la bodega, invalidar caché de filtros para recargarlos
        var currentBodega = BVAR ? (window[BVAR] || '') : '';
        if (filtersLoadedBodega !== currentBodega) filtersLoaded = false;
        loadFilters();
        // Si no hay query ni filtros activos → mostrar top preview
        if (query === '' && catId === '' && marcaId === '' && !conStock) {
            loadTopVendidos();
        } else {
            doSearch();
        }
        setTimeout(function () { var inp = el(S + '_input'); if (inp) inp.focus(); }, 350);
    });

    // Al cerrar: invalidar peticiones pendientes y ocultar barra de carga
    $('#' + MODAL_ID).on('hidden.bs.modal', function () {
        clearTimeout(timer);
        ++reqSeq;       // cualquier respuesta en vuelo será ignorada
        hideLoading();
    });

})();
</script>
@endpush
