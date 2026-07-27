(function () {
    function limpiarModalFoto() {
        var form = document.getElementById('foto_productoForm');
        var preview = document.getElementById('previewContainer');
        var grid = document.getElementById('previewGrid');

        if (form) form.reset();
        if (preview) preview.style.display = 'none';
        if (grid) grid.innerHTML = '';
    }

    window.abrirModalFotoProducto = function (productoId) {
        limpiarModalFoto();
        document.getElementById('id_producto_edit_foto').value = productoId;
        window.jQuery('#modal_foto_producto').modal('show');
    };

    function inicializarModalFoto() {
        if (!window.jQuery) {
            setTimeout(inicializarModalFoto, 50);
            return;
        }

        var $ = window.jQuery;

        $(document).off('change.productoFoto', '#foto_producto_edit')
        .on('change.productoFoto', '#foto_producto_edit', function () {
        var files = this.files;
        var grid = document.getElementById('previewGrid');
        var container = document.getElementById('previewContainer');
        var count = document.getElementById('previewCount');

        grid.innerHTML = '';
        if (!files || !files.length) {
            container.style.display = 'none';
            return;
        }

        if (files.length > 10) {
            Swal.fire({ icon: 'warning', title: 'Máximo 10 imágenes', text: 'Solo se subirán las primeras 10 imágenes seleccionadas.' });
        }

        var total = Math.min(files.length, 10);
        count.textContent = total;
        container.style.display = 'block';

        for (var index = 0; index < total; index++) {
            var reader = new FileReader();
            var item = document.createElement('div');
            var image = document.createElement('img');
            item.style.cssText = 'border-radius:8px;overflow:hidden;border:2px solid #e0e6ed;background:#f8fafc;min-height:90px;display:flex;align-items:center;justify-content:center;';
            image.style.cssText = 'width:100%;height:100%;object-fit:cover;';
            reader.onload = (function (previewImage) {
                return function (event) { previewImage.src = event.target.result; };
            })(image);
            reader.readAsDataURL(files[index]);
            item.appendChild(image);
            grid.appendChild(item);
        }
        });

    $(document).off('submit.productoFoto', '#foto_productoForm')
        .on('submit.productoFoto', '#foto_productoForm', function (event) {
        event.preventDefault();

        var input = document.getElementById('foto_producto_edit');
        if (!input.files.length) {
            Swal.fire({ icon: 'warning', title: 'Seleccione una imagen' });
            return;
        }

        $('#modal_foto_producto').modal('hide');
        $('#modalSpinnerLoading').modal('show');

        var data = new FormData(this);
        for (var index = 0; index < Math.min(input.files.length, 10); index++) {
            data.append('files[]', input.files[index]);
        }

        axios.post('/ruta/imagen/edit', data).then(function () {
            $('#modalSpinnerLoading').modal('hide');
            limpiarModalFoto();
            Swal.fire({ icon: 'success', title: 'Éxito!', text: 'Imagen guardada con éxito.' })
                .then(function () { location.reload(); });
        }).catch(function () {
            $('#modalSpinnerLoading').modal('hide');
            $('#modal_foto_producto').modal('show');
            Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: 'Revise las imágenes e intente nuevamente.' });
        });
        });

    }

    inicializarModalFoto();
})();