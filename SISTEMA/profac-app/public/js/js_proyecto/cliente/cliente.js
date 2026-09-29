

var idCliente = null;
var modalDirecciones = [];
var modalPaises = [];
var modalMapas = {};

function modalDireccionVacia() {
    return { etiqueta: '', pais_id: '', departamento_id: '', municipio_id: '', direccion: '', latitud: '', longitud: '', principal: true };
}

function modalInicializarDirecciones() {
    modalDirecciones = [modalDireccionVacia()];
    modalCargarPaises().then(function () { modalRenderizarDirecciones(); });
}

function modalCargarPaises() {
    return axios.get('/cliente/pais').then(function (response) {
        modalPaises = response.data.listaPais || [];
        return modalPaises;
    });
}

function modalEscapar(texto) {
    return $('<div>').text(texto || '').html();
}

function modalOpcionesPais(selected) {
    var html = '<option value="">---Seleccione un país---</option>';
    modalPaises.forEach(function (pais) {
        html += '<option value="' + pais.id + '"' + (String(pais.id) === String(selected || '') ? ' selected' : '') + '>' + modalEscapar(pais.nombre) + '</option>';
    });
    return html;
}

function modalRenderizarDirecciones() {
    Object.keys(modalMapas).forEach(function (index) { modalMapas[index].remove(); });
    modalMapas = {};
    var html = '';
    modalDirecciones.forEach(function (direccion, index) {
        html += '<article class="modal-direccion-card" data-index="' + index + '"><div class="d-flex justify-content-between align-items-center mb-1"><strong style="font-size:.75rem;color:#7d3f00">Dirección ' + (index + 1) + (direccion.principal ? ' · Principal' : '') + '</strong>' +
            (modalDirecciones.length > 1 ? '<button type="button" class="btn btn-sm btn-outline-danger modal-quitar-direccion" data-index="' + index + '"><i class="fa fa-trash"></i></button>' : '') + '</div>' +
            '<div class="row"><div class="col-md-3"><label>Etiqueta <span class="text-danger">*</span></label><input class="form-control form-control-sm modal-dir-field" data-field="etiqueta" data-index="' + index + '" value="' + modalEscapar(direccion.etiqueta) + '" required></div>' +
            '<div class="col-md-3"><label>País <span class="text-danger">*</span></label><select class="form-control form-control-sm modal-dir-field" data-field="pais_id" data-index="' + index + '" required>' + modalOpcionesPais(direccion.pais_id) + '</select></div>' +
            '<div class="col-md-3"><label>Departamento <span class="text-danger">*</span></label><select class="form-control form-control-sm modal-dir-field" data-field="departamento_id" data-index="' + index + '" required><option value="">---Seleccione---</option></select></div>' +
            '<div class="col-md-3"><label>Municipio <span class="text-danger">*</span></label><select class="form-control form-control-sm modal-dir-field" data-field="municipio_id" data-index="' + index + '" required><option value="">---Seleccione---</option></select></div>' +
            '<div class="col-md-8 mt-2"><label>Dirección completa <span class="text-danger">*</span></label><textarea class="form-control form-control-sm modal-dir-field" data-field="direccion" data-index="' + index + '" rows="2" required>' + modalEscapar(direccion.direccion) + '</textarea></div>' +
            '<div class="col-md-4 mt-2"><div class="custom-control custom-radio mt-4"><input type="radio" class="custom-control-input modal-dir-principal" name="modal_direccion_principal" id="modal-dir-principal-' + index + '" data-index="' + index + '" ' + (direccion.principal ? 'checked' : '') + '><label class="custom-control-label" for="modal-dir-principal-' + index + '">Principal</label></div></div>' +
            '<div class="col-md-6 mt-2"><label>Latitud</label><input class="form-control form-control-sm modal-dir-field" data-field="latitud" data-index="' + index + '" value="' + modalEscapar(direccion.latitud) + '"></div>' +
            '<div class="col-md-6 mt-2"><label>Longitud</label><input class="form-control form-control-sm modal-dir-field" data-field="longitud" data-index="' + index + '" value="' + modalEscapar(direccion.longitud) + '"></div>' +
            '<div class="col-md-12 mt-2"><div id="modal-direccion-map-' + index + '" class="modal-direccion-map"></div><small class="modal-direccion-summary"><i class="fa fa-info-circle"></i> Haga clic para colocar las coordenadas.</small></div></div></article>';
    });
    $('#modal_direcciones_container').html(html);
    modalDirecciones.forEach(function (direccion, index) {
        modalCargarCatalogos(index).then(function () { modalUbicarDireccion(index); });
        modalInicializarMapa(index);
    });
}

function modalCargarCatalogos(index) {
    var direccion = modalDirecciones[index];
    if (!direccion || !direccion.pais_id) return Promise.resolve();
    return axios.post('/cliente/departamento', { id: direccion.pais_id }).then(function (response) {
        var depto = $('.modal-dir-field[data-index="' + index + '"][data-field="departamento_id"]');
        modalLlenarSelect(depto, response.data.listaDeptos || [], direccion.departamento_id);
        if (!direccion.departamento_id) return null;
        return axios.post('/cliente/municipio', { id: direccion.departamento_id });
    }).then(function (response) {
        if (!response) return;
        modalLlenarSelect($('.modal-dir-field[data-index="' + index + '"][data-field="municipio_id"]'), response.data.listaMunicipios || [], direccion.municipio_id);
    }).catch(function () {});
}

function modalLlenarSelect($select, items, selected) {
    var html = '<option value="">---Seleccione---</option>';
    items.forEach(function (item) { html += '<option value="' + item.id + '"' + (String(item.id) === String(selected || '') ? ' selected' : '') + '>' + modalEscapar(item.nombre) + '</option>'; });
    $select.html(html);
}

function modalInicializarMapa(index) {
    if (typeof L === 'undefined') return;
    var direccion = modalDirecciones[index];
    var container = document.getElementById('modal-direccion-map-' + index);
    if (!container) return;
    var lat = parseFloat(direccion.latitud), lng = parseFloat(direccion.longitud);
    var tiene = Number.isFinite(lat) && Number.isFinite(lng);
    var mapa = L.map(container).setView(tiene ? [lat, lng] : [14.0723, -87.1921], tiene ? 15 : 7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(mapa);
    var marker = tiene ? L.marker([lat, lng], { draggable: true }).addTo(mapa) : null;
    var fijar = function (latitud, longitud) {
        direccion.latitud = Number(latitud).toFixed(7); direccion.longitud = Number(longitud).toFixed(7);
        $('.modal-dir-field[data-index="' + index + '"][data-field="latitud"]').val(direccion.latitud);
        $('.modal-dir-field[data-index="' + index + '"][data-field="longitud"]').val(direccion.longitud);
        if (!marker) marker = L.marker([latitud, longitud], { draggable: true }).addTo(mapa);
        marker.setLatLng([latitud, longitud]); mapa.setView([latitud, longitud], Math.max(mapa.getZoom(), 15));
    };
    if (marker) marker.on('dragend', function (event) { var pos = event.target.getLatLng(); fijar(pos.lat, pos.lng); });
    mapa.on('click', function (event) { fijar(event.latlng.lat, event.latlng.lng); });
    mapa._fijarCoordenadas = fijar;
    modalMapas[index] = mapa;
    setTimeout(function () { mapa.invalidateSize(); }, 50);
}

function modalActualizarMapas() {
    Object.keys(modalMapas).forEach(function (index) {
        var mapa = modalMapas[index];
        if (!mapa) return;
        mapa.invalidateSize(true);
        mapa.setView(mapa.getCenter(), mapa.getZoom(), { animate: false });
    });
}

function modalUbicarDireccion(index) {
    var direccion = modalDirecciones[index];
    var card = $('.modal-direccion-card[data-index="' + index + '"]');
    if (!direccion || !card.length || !direccion.pais_id) return;
    var partes = [
        direccion.direccion,
        card.find('[data-field="municipio_id"] option:selected').text(),
        card.find('[data-field="departamento_id"] option:selected').text(),
        card.find('[data-field="pais_id"] option:selected').text()
    ].filter(function (parte) { return parte && parte.indexOf('Seleccione') === -1; });
    if (!partes.length) return;

    fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=' + encodeURIComponent(partes.join(', ')), {
        headers: { 'Accept-Language': 'es' }
    }).then(function (response) { return response.json(); }).then(function (resultados) {
        var mapa = modalMapas[index];
        if (!mapa || !mapa._fijarCoordenadas || !resultados.length) return;
        mapa._fijarCoordenadas(parseFloat(resultados[0].lat), parseFloat(resultados[0].lon));
    }).catch(function () {});
}

function modalAgregarDireccion() {
    modalDirecciones.forEach(function (direccion) { direccion.principal = false; });
    modalDirecciones.push(modalDireccionVacia());
    modalRenderizarDirecciones();
}


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
                    "url": "/js/plugins/dataTables/i18n/Spanish.json"
                },
                pageLength: 10,
                responsive: true,
                "ajax": "/clientes/listar",
                "columns": [{
                        data: 'idCliente'
                    },{
                        data: 'categoria_escala_cliente'
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

                ],
                drawCallback: function () {
                    var api  = this.api();
                    var data = api.data();
                    var total     = data.length;
                    var activos   = 0;
                    var inactivos = 0;
                    data.each(function (row) {
                        var estado = (row.estado || '').toString();
                        if (estado.indexOf('INACTIVO') !== -1) { inactivos++; }
                        else if (estado.indexOf('ACTIVO') !== -1) { activos++; }
                    });
                    $('#cli-stat-total').text(total);
                    $('#cli-stat-activos').text(activos);
                    $('#cli-stat-inactivos').text(inactivos);
                }


            });
        }

        function obtenerpaiss() {

                    if (!document.getElementById('pais_cliente')) {
                        modalCargarPaises();
                        return;
                    }

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
           // Todo cliente nuevo se registra siempre como Estatal (A): el select
           // permanece bloqueado (disabled) y se preselecciona automáticamente.
           let html = "";

           array.forEach(tipo => {
               let esEstatal = tipo.id == 2;
               html +=
                   `
           <option value="${ tipo.id }" ${ esEstatal ? 'selected' : '' }>${tipo.descripcion}</option>
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

        // Máscara automática ####-#### para teléfono empresa (crear)
        $(document).on('input', '#telefono_cliente', function () {
            var digits = this.value.replace(/\D/g, '').substring(0, 8);
            this.value = digits.length > 4 ? digits.substring(0, 4) + '-' + digits.substring(4) : digits;
        });

        // Máscara automática ####-#### para teléfono contacto 1 (primer input name="telefono[]")
        $(document).on('input', '[name="telefono[]"]', function () {
            // Solo aplicar al primer elemento (contacto 1)
            var allTels = document.getElementsByName('telefono[]');
            if (this !== allTels[0]) return;
            var digits = this.value.replace(/\D/g, '').substring(0, 8);
            this.value = digits.length > 4 ? digits.substring(0, 4) + '-' + digits.substring(4) : digits;
        });

        // Resetear pestañas y errores al abrir el modal crear
        $('#modal_clientes_crear').on('show.bs.modal', function () {
            $('#tab-crear-datos-tab').tab('show');
            $('#clientesCreacionForm .is-invalid').removeClass('is-invalid');
            $('#badge-tab-crear-datos, #badge-tab-crear-contacto, #badge-tab-crear-ubicacion').addClass('d-none');
            modalInicializarDirecciones();
        });
        $('#tabsCrearCliente a[data-toggle="tab"]').on('shown.bs.tab', function (event) {
            if ($(event.target).attr('href') === '#tab-crear-ubicacion') {
                setTimeout(modalActualizarMapas, 100);
            }
        });

        $(document).on('input change', '.modal-dir-field', function () {
            var index = Number($(this).data('index')), field = $(this).data('field');
            if (modalDirecciones[index]) modalDirecciones[index][field] = $(this).val();
            if (field === 'pais_id' || field === 'departamento_id') {
                if (field === 'pais_id') { modalDirecciones[index].departamento_id = ''; modalDirecciones[index].municipio_id = ''; }
                if (field === 'departamento_id') modalDirecciones[index].municipio_id = '';
                modalCargarCatalogos(index).then(function () { modalUbicarDireccion(index); });
            }
            if (field === 'municipio_id') modalUbicarDireccion(index);
        });
        $(document).on('change', '.modal-dir-principal', function () {
            var index = Number($(this).data('index'));
            modalDirecciones.forEach(function (direccion, i) { direccion.principal = i === index; });
            modalRenderizarDirecciones();
        });
        $(document).on('click', '.modal-quitar-direccion', function () {
            if (modalDirecciones.length <= 1) return;
            var index = Number($(this).data('index')), eraPrincipal = modalDirecciones[index].principal;
            modalDirecciones.splice(index, 1);
            if (eraPrincipal) modalDirecciones[0].principal = true;
            modalRenderizarDirecciones();
        });

        $(document).on('submit', '#clientesCreacionForm', function(event) {
            event.preventDefault();
            registrarCliente();
        });

      /* ============================================================
         Validación por pestañas del formulario crear cliente
         ============================================================ */
      function validarCrearClienteForm() {
          var errores = { datos: [], contacto: [], ubicacion: [] };

          // Limpiar estados previos
          $('#clientesCreacionForm .is-invalid').removeClass('is-invalid');
          $('#badge-tab-crear-datos, #badge-tab-crear-contacto, #badge-tab-crear-ubicacion').addClass('d-none');

          // ── Tab Datos ──────────────────────────────────────────────
          if (!$('#cliente_categoria_escala_id_crear').val()) {
              $('#cliente_categoria_escala_id_crear').addClass('is-invalid');
              errores.datos.push('Categoría / Escala de precios');
          }
          if (!$('#nombre_cliente').val().trim()) {
              $('#nombre_cliente').addClass('is-invalid');
              errores.datos.push('Nombre del cliente');
          }
          var rtn = $('#rtn_cliente').val().trim();
          if (!rtn) {
              $('#rtn_cliente').addClass('is-invalid');
              errores.datos.push('RTN');
          }
          if (!$('#tipo_personalidad').val()) {
              $('#tipo_personalidad').addClass('is-invalid');
              errores.datos.push('Tipo de Personalidad');
          }
          if (!$('#categoria_cliente').val()) {
              $('#categoria_cliente').addClass('is-invalid');
              errores.datos.push('Tipo de Cliente');
          }
          if (!$('#vendedor_cliente').val()) {
              $('#vendedor_cliente').addClass('is-invalid');
              errores.datos.push('Vendedor');
          }

          // ── Tab Contacto ───────────────────────────────────────────
          var correoCliente = $('#correo_cliente').val().trim();
          if (!correoCliente) {
              $('#correo_cliente').addClass('is-invalid');
              errores.contacto.push('Correo electronico');
          } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correoCliente)) {
              $('#correo_cliente').addClass('is-invalid');
              errores.contacto.push('Correo electronico (formato invalido)');
          }

          var telCliente = $('#telefono_cliente').val().trim();
          if (!telCliente || !/^[0-9]{4}-[0-9]{4}$/.test(telCliente)) {
              $('#telefono_cliente').addClass('is-invalid');
              errores.contacto.push('Teléfono del cliente (formato ####-####)');
          }
          var contactos = document.getElementsByName('contacto[]');
          var telefonos = document.getElementsByName('telefono[]');
          if (!contactos[0] || !contactos[0].value.trim()) {
              if (contactos[0]) $(contactos[0]).addClass('is-invalid');
              errores.contacto.push('Nombre Contacto 1');
          }
          if (!telefonos[0] || !telefonos[0].value.trim() || !/^[0-9]{4}-[0-9]{4}$/.test(telefonos[0].value.trim())) {
              if (telefonos[0]) $(telefonos[0]).addClass('is-invalid');
              errores.contacto.push('Teléfono Contacto 1 (formato ####-####)');
          }
          // Contacto 2: ambos o ninguno
          var c2nombre = contactos[1] ? contactos[1].value.trim() : '';
          var c2tel    = telefonos[1] ? telefonos[1].value.trim() : '';
          if ((c2nombre && !c2tel) || (!c2nombre && c2tel)) {
              if (c2nombre && !c2tel && telefonos[1]) $(telefonos[1]).addClass('is-invalid');
              if (!c2nombre && c2tel && contactos[1]) $(contactos[1]).addClass('is-invalid');
              errores.contacto.push('Contacto 2: complete nombre y teléfono, o deje ambos en blanco');
          }

          // ── Tab Ubicación ──────────────────────────────────────────
          modalDirecciones.forEach(function (direccion, index) {
              if (!direccion.etiqueta.trim()) errores.ubicacion.push('Etiqueta de dirección ' + (index + 1));
              if (!direccion.pais_id) errores.ubicacion.push('País de dirección ' + (index + 1));
              if (!direccion.departamento_id) errores.ubicacion.push('Departamento de dirección ' + (index + 1));
              if (!direccion.municipio_id) errores.ubicacion.push('Municipio de dirección ' + (index + 1));
              if (!direccion.direccion.trim()) errores.ubicacion.push('Dirección de dirección ' + (index + 1));
          });

          return errores;
      }

      function registrarCliente() {
          var errores = validarCrearClienteForm();
          var totalErrores = errores.datos.length + errores.contacto.length + errores.ubicacion.length;

          if (totalErrores > 0) {
              // Mostrar badges en pestañas con error
              if (errores.datos.length)     $('#badge-tab-crear-datos').removeClass('d-none');
              if (errores.contacto.length)  $('#badge-tab-crear-contacto').removeClass('d-none');
              if (errores.ubicacion.length) $('#badge-tab-crear-ubicacion').removeClass('d-none');

              // Navegar a la primera pestaña con error
              if (errores.datos.length) {
                  $('#tab-crear-datos-tab').tab('show');
              } else if (errores.contacto.length) {
                  $('#tab-crear-contacto-tab').tab('show');
              } else {
                  $('#tab-crear-ubicacion-tab').tab('show');
              }

              var allErrors = errores.datos.concat(errores.contacto).concat(errores.ubicacion);
              Swal.fire({
                  icon: 'warning',
                  title: 'Campos obligatorios incompletos',
                  html: 'Complete los siguientes campos:<br><ul style="text-align:left;margin:8px 0 0">' +
                      allErrors.map(function(e) { return '<li>' + e + '</li>'; }).join('') + '</ul>',
              });
              return;
          }

          document.getElementById('btn_crear_cliente').disabled = true;
          var data = new FormData($('#clientesCreacionForm').get(0));
          data.append('direcciones', JSON.stringify(modalDirecciones));
          data.set('direccion_cliente', modalDirecciones[0].direccion);
          data.set('municipio_cliente', modalDirecciones[0].municipio_id);
          data.set('latitud_cliente', modalDirecciones[0].latitud || '');
          data.set('longitud_cliente', modalDirecciones[0].longitud || '');

          axios.post('/cliente/registrar', data)
              .then(function(response) {
                  var res = response.data;
                  $('#modal_clientes_crear').modal('hide');
                  document.getElementById('clientesCreacionForm').reset();
                  $('#tbl_ClientesLista').DataTable().ajax.reload();
                  $imagenPrevisualizacion.src = '';
                  document.getElementById('btn_crear_cliente').disabled = false;
                  Swal.fire({ icon: res.icon, title: res.title, text: res.text });
              })
              .catch(function(err) {
                  var res = err.response ? err.response.data : {};
                  document.getElementById('btn_crear_cliente').disabled = false;
                  if (res.type === 'rtn_duplicado') {
                      // Mantener modal abierto, resaltar campo RTN
                      $('#rtn_cliente').addClass('is-invalid');
                      $('#badge-tab-crear-datos').removeClass('d-none');
                      $('#tab-crear-datos-tab').tab('show');
                      Swal.fire({ icon: 'warning', title: res.title, text: res.text });
                      return;
                  }
                  $('#modal_clientes_crear').modal('hide');
                  Swal.fire({
                      icon: res.icon || 'error',
                      title: res.title || 'Error',
                      text: res.text || 'Error al registrar el cliente.',
                  });
              });
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
