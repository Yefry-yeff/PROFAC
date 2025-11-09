

var idCliente = null;


$(document).ready(function() {
    listarClientes();
    obtenerpaiss();
    tipoPersonalidad();
    tipoCliente();
    vendedor();

})

        const $foto_cliente = document.querySelector("#foto_cliente"),
        $imagenPrevisualizacion = document.querySelector("#imagenPrevisualizacion");

        // Escuchar cuando cambie
        $foto_cliente.addEventListener("change", () => {
        // Los archivos seleccionados, pueden ser muchos o uno
        const archivos = $foto_cliente.files;
        // Si no hay archivos salimos de la función y quitamos la imagen
        if (!archivos || !archivos.length) {
            $imagenPrevisualizacion.src = "";
            return;
        }
        // Ahora tomamos el primer archivo, el cual vamos a previsualizar
        const primerArchivo = archivos[0];
        // Lo convertimos a un objeto de tipo objectURL
        const objectURL = URL.createObjectURL(primerArchivo);
        // Y a la fuente de la imagen le ponemos el objectURL
        $imagenPrevisualizacion.src = objectURL;
        });

        function listarClientes(){
            //

            $('#tbl_ClientesLista').DataTable({
                "order": [0, 'desc'],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                },
                pageLength: 10,
                responsive: true,
                "ajax": "/clientes/listar",
                "columns": [{
                        data: 'idCliente'
                    },
                    {
                        data: 'nombre'
                    },
                    {
                        data: 'direccion'
                    },
                    {
                        data: 'telefono_empresa'
                    },
                    {
                        data: 'correo'
                    },
                    {
                        data: 'rtn'
                    },
                    {
                        data: 'estado'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'opciones'
                    },

                ]


            });
        }

        function obtenerpaiss() {

            axios.get('/cliente/pais')
                .then(function(response) {

                    let array = response.data.listaPais;
                    let html = "<option selected disabled>---Seleccione un pais---</option>";

                    array.forEach(pais => {

                        html +=
                            `
                    <option value="${ pais.id }">${pais.nombre}</option>
                   `
                    });

                    document.getElementById("pais_cliente").innerHTML = html;


                })
                .catch(function(error) {
                    // handle error
                    console.log(error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error...',
                        text: "Ha ocurrido un error al obtener la lista de paises"
                    })
                })



        }

        function obtenerDepartamentos(){
            document.getElementById('departamento_cliente').innerHTML="<option selected disabled>---Seleccionar un depto---</option>";
            document.getElementById('municipio_cliente').innerHTML="<option selected disabled>---Seleccionar un depto---</option>";

            let id = document.getElementById('pais_cliente').value;
           // console.log(id)

            axios.post('/cliente/departamento',{id:id})
            .then(function(response) {

                let array = response.data.listaDeptos;
                let html = "<option selected disabled>---Seleccione un departamento---</option>";

                array.forEach(departamento => {

                    html +=
                        `
                <option value="${ departamento.id }">${departamento.nombre}</option>
                `
                });

                document.getElementById("departamento_cliente").innerHTML = html;


                })
                .catch(function(error) {
                // handle error
                console.log(error);

                Swal.fire({
                    icon: 'error',
                    title: 'Error...',
                    text: "Ha ocurrido un error al obtener los departamentos"
                })
                })


        }

        function obtenerMunicipios(){
            let id = document.getElementById('departamento_cliente').value;

            axios.post('/cliente/municipio', {id:id})
            .then(function(response) {
            let array = response.data.listaMunicipios;
            let html = "<option selected disabled>---Seleccione un municipio---</option>";

            array.forEach(municipio => {

                html +=
                    `
            <option value="${ municipio.id }">${municipio.nombre}</option>
            `
            });

            document.getElementById("municipio_cliente").innerHTML = html;


            })
            .catch(function(error) {
            // handle error
            console.log(error);

            Swal.fire({
                icon: 'error',
                title: 'Error...',
                text: "Ha ocurrido un error al obtener los municipios"
            })
            })

        }

        function tipoPersonalidad(){


            axios.get('/cliente/tipo/personalidad')
            .then(function(response) {
            let array = response.data.tipoPersonalidad;
            let html = "<option selected disabled>---Seleccione una opción---</option>";

            array.forEach(tipo => {

                html +=
                    `
            <option value="${ tipo.id }">${tipo.nombre}</option>
            `
            });

            document.getElementById("tipo_personalidad").innerHTML = html;


            })
            .catch(function(error) {
            // handle error
            console.log(error);

            Swal.fire({
                icon: 'error',
                title: 'Error...',
                text: "Ha ocurrido un error al obtener el tipo de personalidad"
            })
            })

        }


        function tipoCliente(){
           axios.get('/cliente/tipo/cliente')
           .then(function(response) {
           let array = response.data.tipoCliente;
           let html = "<option selected disabled>---Seleccione una opción---</option>";

           array.forEach(tipo => {

               html +=
                   `
           <option value="${ tipo.id }">${tipo.descripcion}</option>
           `
           });

           document.getElementById("categoria_cliente").innerHTML = html;


           })
           .catch(function(error) {
           // handle error
           console.log(error);

           Swal.fire({
               icon: 'error',
               title: 'Error...',
               text: "Ha ocurrido un error al obtener el tipo de cliente"
           })
           })

        }

       function vendedor(){


           axios.get('/cliente/lista/vendedores')
           .then(function(response) {
           let array = response.data.vendedor;
           let html = "<option selected disabled>---Seleccione una opción---</option>";

           array.forEach(vendedor => {

               html +=
                   `
           <option value="${ vendedor.id }">${vendedor.name}</option>
           `
           });

           document.getElementById("vendedor_cliente").innerHTML = html;


           })
           .catch(function(error) {
           // handle error
           console.log(error);

           Swal.fire({
               icon: 'error',
               title: 'Error...',
               text: "Ha ocurrido un error al obtener el vendedor"
           })
           })

       }

        $(document).on('submit', '#clientesCreacionForm', function(event) {
            event.preventDefault();
            registrarCliente();
        });

      function registrarCliente(){
          const catEscala = $('#cliente_categoria_escala_id_crear').val();
            if (!catEscala || catEscala === '') {
                Swal.fire({
                icon: 'warning',
                title: 'Falta la categoría/escala',
                text: 'Seleccioná una categoría de cliente antes de crear.'
                });
                return;
            }


            console.log(document.getElementById('cliente_categoria_escala_id_crear').value);
        document.getElementById('btn_crear_cliente').disabled=true;
        let contacto2 = document.getElementsByName('contacto[]');
        let telefono2 = document.getElementsByName('telefono[]');

        if( contacto2[1].value  && telefono2[1].value  ){

                var data = new FormData($('#clientesCreacionForm').get(0));



                axios.post('/cliente/registrar',data)
                .then( response => {
                    let data = response.data;


                    $('#modal_clientes_crear').modal('hide');
                    document.getElementById("clientesCreacionForm").reset();
                    $('#clientesCreacionForm').parsley().reset();
                    $('#tbl_ClientesLista').DataTable().ajax.reload();
                    $imagenPrevisualizacion.src = '';
                    document.getElementById('btn_crear_cliente').disabled=false;


                    Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })


                })
                .catch( err => {
                    let data = err.response.data;
                    console.log(err);
                    $('#modal_clientes_crear').modal('hide');
                    document.getElementById('btn_crear_cliente').disabled=false;
                    Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })
                })

        }else if( (contacto2[1].value == null || contacto2[1].value == '' ) && (telefono2[1].value == null || telefono2[1].value == '' ) ){

            var data = new FormData($('#clientesCreacionForm').get(0));

            axios.post('/cliente/registrar',data)
            .then( response => {
                let data = response.data;
                $('#modal_clientes_crear').modal('hide');

                document.getElementById("clientesCreacionForm").reset();
                $('#clientesCreacionForm').parsley().reset();
                $('#tbl_ClientesLista').DataTable().ajax.reload();
                $imagenPrevisualizacion.src='';
                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text,
                })
                document.getElementById('btn_crear_cliente').disabled=false;


            })
            .catch( err => {
                let data = err.response.data;
                $('#modal_clientes_crear').modal('hide');
                    document.getElementById('btn_crear_cliente').disabled=false;
                    Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })
            })

        }else{
            $('#modal_clientes_crear').modal('hide');

            Swal.fire({
                        icon: 'warning',
                        title: 'Advertencia!',
                        text: "Por favor completar los datos faltantes del contacto 2 del cliente. De faltar el nombre o numero de teléfono dejar en las casillas en blanco"
                    })

        }

        document.getElementById('btn_crear_cliente').disabled=false;

      }

/*---------------------------------------------------------------Editar Cliente----------------------------------------------------------------------------------------------------------------*/
/*---------------------------------------------------------------Editar Cliente----------------------------------------------------------------------------------------------------------------*/
    // cache para no pedir las categorías cada vez


    function fillCategoriaEscalaSelect(currentId = null, currentText = null) {
        const $sel = $('#categoria_cliente_escala_editar');
        $sel.empty();

        // Trae todas las categorías
        $.getJSON("/clientes/categorias-escala", function(res){
        const list = res.categorias || [];

        // Si NO hay categoría actual, ponemos placeholder
        if (currentId === null || currentId === '' || typeof currentId === 'undefined') {
            $sel.append(new Option('Seleccione…', '', true, true));
            list.forEach(c => $sel.append(new Option(c.nombre_categoria, c.id, false, false)));
            return;
        }

        // 1) Opción seleccionada con la categoría actual (visible arriba)
        $sel.append(new Option(currentText ?? ('ID ' + currentId), currentId, true, true));

        // 2) Agregar el resto EXCLUYENDO la actual
        list.forEach(c => {
            if (String(c.id) !== String(currentId)) {
            $sel.append(new Option(c.nombre_categoria, c.id, false, false));
            }
        });
        });
    }
loadCategoriasEscalaCreate();
    function loadCategoriasEscalaCreate() {
    const $sel = $('#cliente_categoria_escala_id_crear');
    const url  = $sel.data('url');

    // placeholder limpio
    $sel.empty().append(new Option('--- Seleccione una categoría ---', '', true, true));

    $.getJSON(url, function(res){
        (res.categorias || []).forEach(c => {
        $sel.append(new Option(c.nombre_categoria, c.id, false, false));
        });
    });
    }

    function modalEditarCliente(id){

        axios.post("/clientes/datos/editar", {id:id})
        .then( response => {

            document.getElementById("clientesCreacionForm_editar").reset();

            let datosCliente = response.data.datosCliente;
            let datosContacto = response.data.datosContacto;
            let datosUbicacion = response.data.datosUbicacion;
            let paises = response.data.paises;
            let deptos = response.data.deptos;
            let municipios = response.data.municipios;

            let tipoPersonalidad = response.data.tipoPersonalidad;
            let tipoCliente = response.data.tipoCliente;
            let vendedores = response.data.vendedores;

            let htmlSelectPais ="";
            let htmlSelectDepto ="";
            let htmlSelectMunicipio ="";

            let htmlSelectTipoPersonalidad ="";
            let htmlSelectTipoCliente="";
            let htmlSelectVendedor="";

            let longitudArrayContactos = datosContacto.length;

            /*------------------------------------------------------------*/
            paises.forEach(pais => {
                if(datosUbicacion.idPais == pais.id ){
                    htmlSelectPais +=
                    `
                    <option value="${ pais.id }" selected>${pais.nombre}</option>
                    `


                }else{
                    htmlSelectPais +=
                    `
                    <option value="${ pais.id }">${pais.nombre}</option>
                    `


                }
            });

            /*----------------------------------------------------------*/
            deptos.forEach(depto => {
                if(datosUbicacion.idDepto == depto.id ){
                    htmlSelectDepto +=
                    `
                    <option value="${ depto.id }" selected>${depto.nombre}</option>
                    `


                }else{
                    htmlSelectDepto +=
                    `
                    <option value="${ depto.id }">${depto.nombre}</option>
                    `


                }
            });
            /*-------------------------------------------------------------*/
            municipios.forEach(municipio => {
                if(datosUbicacion.idMunicipio == municipio.id ){
                    htmlSelectMunicipio +=
                    `
                    <option value="${ municipio.id }" selected>${municipio.nombre}</option>
                    `


                }else{
                    htmlSelectMunicipio +=
                    `
                    <option value="${ municipio.id }">${municipio.nombre}</option>
                    `


                }
            });

            /*-------------------------------------------------------------*/
            tipoPersonalidad.forEach(personalidad => {
                if(datosCliente.tipo_personalidad_id == personalidad.id ){
                    htmlSelectTipoPersonalidad +=
                    `
                    <option value="${ personalidad.id }" selected>${personalidad.nombre}</option>
                    `


                }else{
                    htmlSelectTipoPersonalidad +=
                    `
                    <option value="${ personalidad.id }">${personalidad.nombre}</option>
                    `


                }
            });

            /*-------------------------------------------------------------*/
            tipoCliente.forEach(cliente => {
                if(datosCliente.tipo_cliente_id == cliente.id ){
                    htmlSelectTipoCliente +=
                    `
                    <option value="${ cliente.id }" selected>${cliente.descripcion}</option>
                    `


                }else{
                    htmlSelectTipoCliente +=
                    `
                    <option value="${ cliente.id }">${cliente.descripcion}</option>
                    `


                }
            });

            /*-------------------------------------------------------------*/
            vendedores.forEach(vendedor => {
                if(datosCliente.vendedor == vendedor.id ){
                    htmlSelectVendedor +=
                    `
                    <option value="${ vendedor.id }" selected>${vendedor.name}</option>
                    `

                }else{
                    htmlSelectVendedor +=
                    `
                    <option value="${ vendedor.id }">${vendedor.name}</option>
                    `


                }
            });

            document.getElementById('idCliente').value = datosCliente.id;

            document.getElementById('nombre_cliente_editar').value =datosCliente.nombre;
            document.getElementById('direccion_cliente_editar').value =datosCliente.direccion;
            document.getElementById('credito_inicial_editar').value = datosCliente.credito_inicial;
           // document.getElementById('credito_inicial_editar').value = datosCliente.credito_inicial.toFixed(2);
            document.getElementById('credito_editar').value = datosCliente.credito;
           // document.getElementById('credito_editar').value = datosCliente.credito.toFixed(2);
            document.getElementById('dias_credito_editar').value = datosCliente.dias_credito;
            document.getElementById('rtn_cliente_editar').value = datosCliente.rtn;
            document.getElementById("correo_cliente_editar").value = datosCliente.correo;
            document.getElementById('telefono_cliente_editar').value = datosCliente.telefono_empresa;


            document.getElementById('contacto_1_editar').value = datosContacto[0].nombre;
            document.getElementById('telefono_1_editar').value =datosContacto[0].telefono;


            if(longitudArrayContactos>1){
                document.getElementById('contacto_2_editar').value =datosContacto[1].nombre;
                document.getElementById('telefono_2_editar').value =datosContacto[1].telefono;
            }


            document.getElementById('longitud_cliente_editar').value =datosCliente.longitud;
            document.getElementById('latitud_cliente_editar').value =datosCliente.latitud;

            document.getElementById("pais_cliente_editar").innerHTML=htmlSelectPais;
            document.getElementById("departamento_cliente_editar").innerHTML=htmlSelectDepto;
            document.getElementById("municipio_cliente_editar").innerHTML=htmlSelectMunicipio;

            document.getElementById("tipo_personalidad_editar").innerHTML=htmlSelectTipoPersonalidad;
            document.getElementById("categoria_cliente_editar").innerHTML=htmlSelectTipoCliente;
            document.getElementById("vendedor_cliente_editar").innerHTML=htmlSelectVendedor;

            const actualId   = datosCliente.cliente_categoria_escala_id;
            const actualText = datosCliente.nombre_cat_escala;

             fillCategoriaEscalaSelect(actualId, actualText);
            $('#modal_clientes_editar').modal('show');



        })
        .catch(err=>{

            console.log(err)

        })


    }

    function obtenerDepartamentosEditar(){

        document.getElementById('departamento_cliente_editar').innerHTML="<option selected disabled>---Seleccionar un depto---</option>";
        document.getElementById('municipio_cliente_editar').innerHTML="<option selected disabled>---Seleccionar un depto---</option>";

           let id = document.getElementById('pais_cliente_editar').value;
          // console.log(id)

           axios.post('/cliente/departamento',{id:id})
           .then(function(response) {

               let array = response.data.listaDeptos;
               let html = "<option selected disabled>---Seleccione un departamento---</option>";

               array.forEach(departamento => {

                   html +=
                       `
               <option value="${ departamento.id }">${departamento.nombre}</option>
               `
               });

               document.getElementById("departamento_cliente_editar").innerHTML = html;


               })
               .catch(function(error) {
               // handle error
               console.log(error);

               Swal.fire({
                   icon: 'error',
                   title: 'Error...',
                   text: "Ha ocurrido un error al obtener los departamentos"
               })
               })


       }

       function obtenerMunicipiosEditar(){

           let id = document.getElementById('departamento_cliente_editar').value;


           axios.post('/cliente/municipio', {id:id})
           .then(function(response) {
           let array = response.data.listaMunicipios;
           let html = "<option selected disabled>---Seleccione un municipio---</option>";

           array.forEach(municipio => {

               html +=
                   `
           <option value="${ municipio.id }">${municipio.nombre}</option>
           `
           });

           document.getElementById("municipio_cliente_editar").innerHTML = html;


           })
           .catch(function(error) {
           // handle error
           console.log(error);

           Swal.fire({
               icon: 'error',
               title: 'Error...',
               text: "Ha ocurrido un error al obtener los municipios"
           })
           })

       }

    $(document).on('submit', '#clientesCreacionForm_editar', function(event) {
        event.preventDefault();
        editarClienteGuardar();
    });

       function editarClienteGuardar(){
        let contacto2 = document.getElementsByName('contacto_2_editar');
        let telefono2 = document.getElementsByName('telefono_2_editar');



        if( contacto2.value  && telefono2.value  ){

                var data = new FormData($('#clientesCreacionForm_editar').get(0));
                document.getElementById('btn_crear_cliente_editar').disabled=true;

                axios.post('/clientes/editar',data)
                .then( response => {
                    let data = response.data;


                    $('#modal_clientes_editar').modal('hide');
                    document.getElementById('btn_crear_cliente_editar').disabled=false;
                    document.getElementById("clientesCreacionForm_editar").reset();
                    $('#clientesCreacionForm_editar').parsley().reset();
                    $('#tbl_ClientesLista').DataTable().ajax.reload();




                    Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })


                })
                .catch( err => {
                    let data = err.response.data;
                    console.log(err);
                    $('#clientesCreacionForm_editar').modal('hide');
                    document.getElementById('btn_crear_cliente_editar').disabled=false;
                    Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })
                })

        }else if( (contacto2.value == null || contacto2.value == '' ) && (telefono2.value == null || telefono2.value == '' ) ){

            var data = new FormData($('#clientesCreacionForm_editar').get(0));

            axios.post('/clientes/editar',data)
            .then( response => {
                let data = response.data;
                $('#modal_clientes_editar').modal('hide');
                document.getElementById('btn_crear_cliente_editar').disabled=false;
                document.getElementById("clientesCreacionForm_editar").reset();
                $('#clientesCreacionForm_editar').parsley().reset();
                $('#tbl_ClientesLista').DataTable().ajax.reload();

                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text,
                })

            })
            .catch( err => {
                let data = err.response.data;
                $('#modal_clientes_editar').modal('hide');
                    document.getElementById('btn_crear_cliente_editar').disabled=false;
                    Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })
            })

        }else{
            $('#modal_clientes_editar').modal('hide');

            Swal.fire({
                        icon: 'warning',
                        title: 'Advertencia!',
                        text: "Por favor completar los datos faltantes del contacto 2 del cliente. De faltar el nombre o numero de teléfono dejar en las casillas en blanco"
                    })

        }

       }

       function modalEditarFotografia(idCliente){
           document.getElementById('clienteId').value=idCliente;

          axios.post("/clientes/imagen",{idCliente:idCliente})
          .then(response=>{

            let data = response.data.img;
            let imagenPrevisualizacion_editar = document.getElementById('imagenPrevisualizacion_editar');

            if(data){

                let url = 'img_cliente/'+data;
                imagenPrevisualizacion_editar.src = url;

            }else{
                let url = 'catalogo/noimage.png';
                imagenPrevisualizacion_editar.src = url;
            }

            $('#modal_fotografia_editar').modal('show');

            console.log("entro")
          })
          .catch(err=>{

            console.log(err)

          })
       }

       const $foto_cliente_editar = document.querySelector("#foto_cliente_editar"),
        $imagenPrevisualizacion_editar = document.querySelector("#imagenPrevisualizacion_editar");

        // Escuchar cuando cambie
        $foto_cliente_editar.addEventListener("change", () => {
        // Los archivos seleccionados, pueden ser muchos o uno
        const archivos_editar = $foto_cliente_editar.files;
        // Si no hay archivos salimos de la función y quitamos la imagen
        if (!archivos_editar || !archivos_editar.length) {
            $imagenPrevisualizacion_editar.src = "";
            return;
        }
        // Ahora tomamos el primer archivo, el cual vamos a previsualizar
        const primerArchivo_editar = archivos_editar[0];
        // Lo convertimos a un objeto de tipo objectURL
        const objectURL_editar = URL.createObjectURL(primerArchivo_editar);
        // Y a la fuente de la imagen le ponemos el objectURL
        $imagenPrevisualizacion_editar.src = objectURL_editar;
        });



        $(document).on('submit', '#form_img_edit', function(event) {
        event.preventDefault();
        imagenClienteEditarGuardar();
        })

        function imagenClienteEditarGuardar(){
            document.getElementById('btn_img_editar').disabled = true;
            var data = new FormData($('#form_img_edit').get(0));
            axios.post('/clientes/imagen/editar',data)
            .then(response=>{
                let data = response.data;
                $('#modal_fotografia_editar').modal('hide');
                document.getElementById('btn_img_editar').disabled = false;
                document.getElementById("form_img_edit").reset();
                $('#form_img_edit').parsley().reset();

                Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })
                $('#tbl_ClientesLista').DataTable().ajax.reload();

            })
            .catch(err=>{

                let data = err.response.data;
                $('#modal_fotografia_editar').modal('hide');
                document.getElementById('btn_img_editar').disabled = false;
                Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })

            })
        }

        function desactivarClienteModal(id){

            Swal.fire({
            title: '¿Esta seguro de desactivar este cliente?',
            text:'Si desactiva este cliente, no podra realizar ventas para el mismo.',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Si, Desactivar',
            cancelButtonText: 'Cancelar',
            }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                desactivar(id);
            } else if (result.isDenied) {
                Swal.fire('Changes are not saved', '', 'info')
            }
            })

        }

        function desactivar(idCliente){
            axios.post('/clientes/desactivar',{clienteId:idCliente})
            .then( response=>{
                let data = response.data;
                Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })
                $('#tbl_ClientesLista').DataTable().ajax.reload();

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

        function activarCliente(idCliente){



            axios.post('/clientes/activar',{clienteId:idCliente})
            .then( response=>{
                let data = response.data;
                Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text,
                    })
                $('#tbl_ClientesLista').DataTable().ajax.reload();
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

        $("input[data-type='currency']").on({
             keyup: function() {
            formatCurrency($(this));
             },
             blur: function() {
                formatCurrency($(this), "blur");
            }
        });


        function formatNumber(n) {
        // format number 1000000 to 1,234,567
        return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
        }


        function formatCurrency(input, blur) {
        // appends $ to value, validates decimal side
        // and puts cursor back in right position.

        // get input value
        var input_val = input.val();

        // don't validate empty input
        if (input_val === "") { return; }

        // original length
        var original_len = input_val.length;

        // initial caret position
        var caret_pos = input.prop("selectionStart");

        // check for decimal
        if (input_val.indexOf(".") >= 0) {

            // get position of first decimal
            // this prevents multiple decimals from
            // being entered
            var decimal_pos = input_val.indexOf(".");

            // split number by decimal point
            var left_side = input_val.substring(0, decimal_pos);
            var right_side = input_val.substring(decimal_pos);

            // add commas to left side of number
            left_side = formatNumber(left_side);

            // validate right side
            right_side = formatNumber(right_side);

            // On blur make sure 2 numbers after decimal
            if (blur === "blur") {
            right_side += "00";
            }

            // Limit decimal to only 2 digits
            right_side = right_side.substring(0, 2);

            // join number by .
            input_val =  left_side + "." + right_side;

        } else {
            // no decimal entered
            // add commas to number
            // remove all non-digits
            input_val = formatNumber(input_val);
            input_val = input_val;

            // final formatting
            if (blur === "blur") {
            input_val += ".00";
            }
        }

        // send updated string to input
        input.val(input_val);

        // put caret back in the right position
        var updated_len = input_val.length;
        caret_pos = updated_len - original_len + caret_pos;
        input[0].setSelectionRange(caret_pos, caret_pos);
        }
