        $('#listaTipoFiltro, #tipoCategoria').on('change', function() {
            const tipoCategoria = $('#tipoCategoria').val();
            const lista = $('#listaTipoFiltro').val();
            $('#btnDescargar').prop('disabled', !(tipoCategoria && lista));
        });

        $(document).ready(function() {
            // Inicialmente deshabilitamos el botón
            const $btnDescargar = $('#btnDescargar');
            $btnDescargar.prop('disabled', true);

            // Detectar cambios en el select dinámico
            $('#listaTipoFiltro').on('change', function() {
                if ($(this).val()) {
                    $btnDescargar.prop('disabled', false); // habilitar
                } else {
                    $btnDescargar.prop('disabled', true); // deshabilitar
                }
            });
        });

        $(document).ready(function() {
                // Tipo de categoría
                $('#tipoCategoria').select2({
                    theme: 'bootstrap4',
                    placeholder: "🧾 Tipo de categoría",
                    width: 'resolve'
                });

                // Tipo de filtro
                $('#tipoFiltro').select2({
                    theme: 'bootstrap4',
                    placeholder: "📂 Tipo de filtro",
                    width: 'resolve'
                });

                // Lista de valores según el filtro
                $('#listaTipoFiltro').select2({
                    theme: 'bootstrap4',
                    placeholder: "Seleccione una opción",
                    width: 'resolve'
                });

            $('#tipoFiltro').on('change', function() {
                let tipo = $(this).val();
                let $listaTipo = $('#listaTipoFiltro');

                $listaTipo.val(null).trigger('change');
                $listaTipo.empty();

                if (!tipo) return;

                let url = tipo == '1' ? '/filtros/marca' : '/filtros/categoria';

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $listaTipo.append(new Option('Seleccione', '', false, false));
                        data.forEach(function(item) {
                            $listaTipo.append(new Option(item.nombre, item.id, false, false));
                        });
                        $listaTipo.trigger('change');
                    }
                });
            });
        });
