(function() {
    function getSelectedText(selector) {
        var element = $(selector);
        if (!element.length) {
            return '';
        }

        return (element.find('option:selected').text() || '').trim();
    }

    function renderBar() {
        var bar = document.getElementById('cdxFiltrosBar');
        if (!bar) {
            return;
        }

        var chips = [];
        var bodegaText = getSelectedText('#bodega');
        var productoText = getSelectedText('#producto');

        if (bodegaText && bodegaText.indexOf('Seleccionar') === -1) {
            chips.push('<span class="filtro-badge">Bodega: <strong>' + bodegaText + '</strong></span>');
        }

        if (productoText && productoText.indexOf('Seleccionar') === -1) {
            chips.push('<span class="filtro-badge">Producto: <strong>' + productoText + '</strong></span>');
        }

        if (!chips.length) {
            bar.innerHTML = '';
            bar.style.display = 'none';
            return;
        }

        bar.innerHTML = chips.join('');
        bar.style.display = 'flex';
    }

    function clearFilters() {
        if ($('#bodega').length) {
            $('#bodega').val(null).trigger('change');
        }

        if ($('#producto').length) {
            $('#producto').val(null).trigger('change');
        }

        renderBar();
    }

    window.cardexCommonRefreshBar = renderBar;
    window.cardexCommonClearFilters = clearFilters;

    $(function() {
        renderBar();

        $(document).on('change', '#bodega, #producto', renderBar);
        $(document).on('shown.bs.modal', '#modalFiltrosCardex', renderBar);
    });
})();