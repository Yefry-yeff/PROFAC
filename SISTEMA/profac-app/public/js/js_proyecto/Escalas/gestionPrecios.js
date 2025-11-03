        $('#listaTipoFiltro, #tipoCategoria').on('change', function() {
            const tipoCategoria = $('#tipoCategoria').val();
            const lista = $('#listaTipoFiltro').val();
            $('#btnDescargar').prop('disabled', !(tipoCategoria && lista));
        });

        $(document).ready(function() {
            listarCategorias();
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

            $('#categoria_cliente_id').select2({
                    theme: 'bootstrap4',
                    placeholder: 'Seleccione una categoría...',
                    allowClear: true,
                    minimumResultsForSearch: 0, // siempre mostrar buscador
                    dropdownParent: $('#modalCategoriasPrecios') // importante para que el dropdown no se esconda
                });

        // Resetear select al cerrar modal
        $('#modalCategoriasPrecios').on('hidden.bs.modal', function () {
                    $('#CreacionCatPrecios')[0].reset();
                    $('#categoria_cliente_id').val(null).trigger('change');
                });
        });



        /* Gestiones de categoria */
            $(document).on('submit', '#CreacionCatPrecios', function(event) {
                    event.preventDefault();
                    registrarCategoriaPrecios();
            });

            function registrarCategoriaPrecios(){
                document.getElementById('btn_guardar_categoria').disabled=true;
                var data = new FormData($('#CreacionCatPrecios').get(0));
                axios.post('/guardar/categoria/precios',data)
                    .then( response => {
                        let data = response.data;
                        $('#modalCategoriasPrecios').modal('hide');
                        $('#CreacionCatPrecios').parsley().reset();
                        document.getElementById('btn_guardar_categoria').disabled=false;
                        $('#CreacionCatPrecios')[0].reset();
                        $('#tbl_listaCategoria').DataTable().ajax.reload();

                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text,
                        });

                        // Poner foco en el primer input
                        $('#nombre_cat_precio').focus();
                    })
                    .catch( err => {
                        let data = err.response.data;
                        console.log(err);
                        $('#modalCategoriasPrecios').modal('hide');
                        document.getElementById('btn_guardar_categoria').disabled=false;
                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text,
                        });
                    })

                document.getElementById('btn_guardar_categoria').disabled=false;

            }

            function listarCategorias() {
                    $('#tbl_listaCategoria').DataTable({
                        destroy: true,
                        order: [0, 'desc'],
                        language: {"url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"},
                        pageLength: 5,
                        responsive: true,
                        ajax: "/listar/categoria/precios",
                        columns: [
                        { data: 'id' },
                        { data: 'categoria' },
                        { data: 'estado' },
                        { data: 'categoriaCliente'},
                        { data: 'porc_a'},
                        { data: 'porc_b'},
                        { data: 'porc_c'},
                        { data: 'porc_d'},
                        { data: 'creacion' },
                        { data: 'registro' },
                        { data: 'opciones' }
                        ]
                    });
            }

            function desactivarCategoria(idCategoria){
                axios.get('/desactivar/categoria/precios/'+idCategoria)
                .then( response=>{
                    let data = response.data;
                    Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text,
                        })
                    $('#tbl_listaCategoria').DataTable().ajax.reload();

                })
                .catch(err=>{
                    console.log(err);
                    let data = err.response.data;
                    Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text,
                        })
                })
            }


