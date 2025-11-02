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
                pageLength: 10,
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
