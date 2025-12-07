  $('#modalCategoriasClientes').on('hidden.bs.modal', function () {
    $('#formComentarioCatCliente')[0].reset();
  });



  /* Creación de categoria */
    $(document).on('submit', '#clientesCreacionForm', function(event) {
        event.preventDefault();
        registrarCategoriaCliente();
    });

      function registrarCategoriaCliente(){
        document.getElementById('btn_guardar_categoria').disabled=true;
        var data = new FormData($('#clientesCreacionForm').get(0));
        axios.post('/guardar/categoria/cliente',data)
            .then( response => {
                let data = response.data;
                $('#modalCategoriasClientes').modal('hide');
                //document.getElementById("clientesCreacionForm").reset();
                $('#clientesCreacionForm').parsley().reset();
                //$('#tbl_ClientesLista').DataTable().ajax.reload();
                document.getElementById('btn_guardar_categoria').disabled=false;

                $('#tbl_listaCategoria').DataTable().ajax.reload();
                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text,
                });


            })
            .catch( err => {
                let data = err.response.data;
                console.log(err);
                $('#modalCategoriasClientes').modal('hide');
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
                ajax: "/listar/categoria/cliente",
                columns: [
                { data: 'id' },
                { data: 'categoria' },
                { data: 'descripcion' },
                { data: 'comentario' },
                { data: 'estado' },
                { data: 'registro' },
                { data: 'creacion' },
                { data: 'opciones' }
                ]
            });
      }

       $(document).ready(function() {
            listarCategorias();
        });

        function desactivarCategoria(idCategoria){
            axios.get('/desactivar/categoria/cliente/'+idCategoria)
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

        // Resetear cuando se cambie el archivo
        $('#fileInputCategorias').on('change', function(){
            // Ocultar previews
            $('#previewActualizables').hide();
            $('#previewNoActualizables').hide();
            
            // Mostrar botón de procesar, ocultar botón de finalizar
            $('#btnProcesarArchivo').show();
            $('#btnFinalizarImport').hide();
            
            // Limpiar barra de progreso y mensajes
            $('#barImportCategorias').removeClass('bg-success bg-danger').css('width','0%');
            $('#msgImportCategorias').removeClass('text-danger').text('');
            
            // Mostrar u ocultar botón de limpiar
            if(this.files.length > 0) {
                $('#btnLimpiarArchivo').show();
            } else {
                $('#btnLimpiarArchivo').hide();
            }
        });

        // Limpiar archivo seleccionado
        $('#btnLimpiarArchivo').on('click', function(e){
            e.preventDefault();
            
            // Limpiar input
            $('#fileInputCategorias').val('');
            
            // Ocultar botón X
            $('#btnLimpiarArchivo').hide();
            
            // Ocultar previews
            $('#previewActualizables').hide();
            $('#previewNoActualizables').hide();
            
            // Mostrar botón de procesar, ocultar botón de finalizar
            $('#btnProcesarArchivo').show();
            $('#btnFinalizarImport').hide();
            
            // Limpiar barra de progreso y mensajes
            $('#barImportCategorias').removeClass('bg-success bg-danger').css('width','0%');
            $('#msgImportCategorias').removeClass('text-danger').text('');
        });

        // Procesar archivo para preview
        $('#btnProcesarArchivo').on('click', function(e){
            e.preventDefault();

            // Validar que el archivo sea .xlsx
            const fileInput = document.querySelector('#fileInputCategorias');
            if (fileInput && fileInput.files.length > 0) {
                const fileName = fileInput.files[0].name;
                const fileExt = fileName.split('.').pop().toLowerCase();
                
                if (fileExt !== 'xlsx') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Archivo inválido',
                        text: 'Solo se permiten archivos con extensión .xlsx'
                    });
                    return;
                }
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Advertencia',
                    text: 'Debe seleccionar un archivo'
                });
                return;
            }

            const form = document.getElementById('formImportCategorias');
            const fd = new FormData(form);
            const $bar = $('#barImportCategorias');
            const $msg = $('#msgImportCategorias');
            
            // Ocultar previews anteriores
            $('#previewActualizables').hide();
            $('#previewNoActualizables').hide();
            $('#btnFinalizarImport').hide();

            $bar.removeClass('bg-success bg-danger').css('width','0%');
            $msg.removeClass('text-danger').text('Procesando archivo...');

            $.ajax({
                url: '/clientes/preview-categorias',
                method: 'POST',
                data: fd,
                contentType: false,
                processData: false,
                xhr: function(){
                    const xhr = $.ajaxSettings.xhr();
                    if (xhr.upload) {
                        xhr.upload.addEventListener('progress', function(e){
                            if (e.lengthComputable) {
                                const p = Math.round((e.loaded / e.total) * 100);
                                $bar.css('width', p + '%');
                            }
                        }, false);
                    }
                    return xhr;
                },
                success: function(res){
                    $bar.addClass('bg-success').css('width','100%');
                    $msg.text('Archivo procesado exitosamente');

                    // Mostrar clientes a actualizar
                    if (res.para_actualizar && res.para_actualizar.length > 0) {
                        $('#countActualizables').text(res.para_actualizar.length);
                        let htmlActualizables = '';
                        res.para_actualizar.forEach(function(item){
                            htmlActualizables += `
                                <tr>
                                    <td>${item.id}</td>
                                    <td>${item.nombre}</td>
                                    <td>${item.rtn}</td>
                                    <td>${item.categoria_antigua_nombre} (${item.categoria_antigua_id})</td>
                                    <td class="text-success font-weight-bold">${item.categoria_nueva_nombre} (${item.categoria_nueva_id})</td>
                                </tr>
                            `;
                        });
                        $('#tablaActualizables').html(htmlActualizables);
                        $('#previewActualizables').show();
                        
                        // Mostrar botón de finalizar
                        $('#btnFinalizarImport').show();
                        $('#btnProcesarArchivo').hide();
                    }

                    // Mostrar clientes NO actualizables
                    if (res.no_actualizables && res.no_actualizables.length > 0) {
                        $('#countNoActualizables').text(res.no_actualizables.length);
                        let htmlNoActualizables = '';
                        res.no_actualizables.forEach(function(item){
                            htmlNoActualizables += `
                                <tr>
                                    <td>${item.id}</td>
                                    <td>${item.nombre}</td>
                                    <td>${item.rtn}</td>
                                    <td>${item.categoria_propuesta}</td>
                                    <td class="text-danger">${item.motivo}</td>
                                </tr>
                            `;
                        });
                        $('#tablaNoActualizables').html(htmlNoActualizables);
                        $('#previewNoActualizables').show();
                    }

                    Swal.fire({
                        icon: 'info',
                        title: 'Preview Generado',
                        html: `
                            <p><b>Clientes a actualizar:</b> ${res.para_actualizar.length}</p>
                            <p><b>Clientes NO procesados:</b> ${res.no_actualizables.length}</p>
                            <p class="text-muted mt-3">Revise los datos y haga clic en "Finalizar Actualización" para aplicar los cambios.</p>
                        `
                    });
                },
                error: function(xhr){
                    $bar.addClass('bg-danger').css('width','100%');
                    let t = 'Error al procesar el archivo.';
                    if (xhr.responseJSON && xhr.responseJSON.text) t = xhr.responseJSON.text;
                    $msg.addClass('text-danger').text(t);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: t
                    });
                },
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
            });
        });

        $('#formImportCategorias').on('submit', function(e){
            e.preventDefault();

            const $bar = $('#barImportCategorias');
            const $msg = $('#msgImportCategorias');

            $bar.removeClass('bg-success bg-danger').css('width','0%');
            $msg.removeClass('text-danger').text('Finalizando actualización...');

            $.ajax({
            url: '/clientes/importar-categorias',
            method: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function(res){
                $bar.addClass('bg-success').css('width','100%');
                $msg.text(res.text || 'Importación completada.');
                
                // Ocultar previews y botón de finalizar
                $('#previewActualizables').hide();
                $('#previewNoActualizables').hide();
                $('#btnFinalizarImport').hide();
                $('#btnProcesarArchivo').show();
                
                // Limpiar el input de archivo
                $('#fileInputCategorias').val('');
                $('#btnLimpiarArchivo').hide();
                
                $('#tbl_listaCategoria').DataTable().ajax.reload();
                
                Swal.fire({
                    icon: res.icon || 'success',
                    title: res.title || 'Éxito',
                    text: res.text || 'Importación completada.'
                });
            },
            error: function(xhr){
                $bar.addClass('bg-danger').css('width','100%');
                let t = 'Error al procesar el archivo.';
                if (xhr.responseJSON && xhr.responseJSON.text) t = xhr.responseJSON.text;
                $msg.addClass('text-danger').text(t);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: t
                });
            },
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
            });
        });
