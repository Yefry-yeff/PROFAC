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

        $('#formImportCategorias').on('submit', function(e){
            e.preventDefault();

            const fd = new FormData(this);
            const $bar = $('#barImportCategorias');
            const $msg = $('#msgImportCategorias');
            const $errBox = $('#erroresImportCategorias');
            const $errList = $('#erroresLista');

            $errBox.addClass('d-none'); $errList.empty();
            $bar.removeClass('bg-success bg-danger').css('width','0%');
            $msg.removeClass('text-danger').text('Subiendo archivo…');

            $.ajax({
            url: '/clientes/importar-categorias',
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
                $msg.text(res.text || 'Importación completada.');
                if (res.errores && res.errores.length){
                $errBox.removeClass('d-none');
                res.errores.forEach(e => $errList.append('<li>'+e+'</li>'));
                }
                // Si tenés tabla/listado en pantalla:
                // $('#tbl_listaCategoria').DataTable().ajax?.reload();
            },
            error: function(xhr){
                $bar.addClass('bg-danger').css('width','100%');
                let t = 'Error al procesar el archivo.';
                if (xhr.responseJSON && xhr.responseJSON.text) t = xhr.responseJSON.text;
                $msg.addClass('text-danger').text(t);
            },
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
            });
        });
