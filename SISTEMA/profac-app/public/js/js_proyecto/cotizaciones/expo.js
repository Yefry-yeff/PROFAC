
            // ============ FUNCIONES DE UTILIDAD PARA CÓDIGOS DE BARRAS ============

            // Función para normalizar códigos de barras eliminando ceros a la izquierda
            function normalizarCodigoBarras(codigo) {
                if (!codigo || typeof codigo !== 'string') {
                    return '';
                }

                // Eliminar ceros a la izquierda, pero conservar al menos un dígito
               // const normalizado = codigo.replace(/^0+/, '') || '0';

                console.log('🔧 Código normalizado:', {
                    original: codigo,
                    normalizado: normalizado,
                    cambioRealizado: codigo !== normalizado
                });

                return normalizado;
            }

            /*****************************************/
                // Función a ejecutar cuando el interruptor está activado
                function checkActivo() {

                    // Variable para almacenar el código de barras actual
                    let currentBarcode = '';
                    // Arreglo para almacenar todos los códigos de barras leídos
                    let barcodes = [];
                    // Tiempo máximo para considerar una secuencia completa
                    const MAX_TIME = 100; // en milisegundos
                    let timer;

                    // Función para manejar la entrada del teclado
                    function handleKeyDown(event) {
                        // Ignorar teclas especiales
                        if (event.key.length > 1) return;

                        // Append key to currentBarcode
                        currentBarcode += event.key;

                        // Reset timer
                        clearTimeout(timer);

                        // Set timeout to clear currentBarcode and process the barcodes array
                        timer = setTimeout(() => {
                            if (currentBarcode) {
                                // Usar la función de normalización
                                const processedBarcode = normalizarCodigoBarras(currentBarcode);
                                barcodes.push(processedBarcode);
                                currentBarcode = ''; // Clear the currentBarcode after adding to the array
                            }
                            if (barcodes.length > 0) {
                                // Process barcodes
                                barcodes.forEach((barcode, index) => {
                                    console.log('📋 Procesando código (teclado):', barcode);
                                    agregarProductoCarritoBarra(barcode);
                                });
                                // Clear the array after processing
                                barcodes = [];
                            }
                        }, MAX_TIME);
                    }

                    // Escuchar el evento keydown en el documento
                    document.addEventListener('keydown', handleKeyDown);
                }

                // Función a ejecutar cuando el interruptor está desactivado
                function checkInactivo() {
                        console.log('Interruptor está desactivado.');
                }

                // Función para manejar el cambio de estado del interruptor
                function handleSwitchChange(event) {
                    if (event.target.checked) {
                        checkActivo();
                    } else {
                        checkInactivo();
                    }
                }

                // Obtener el elemento del interruptor y agregar el evento de cambio
                document.getElementById('mySwitch').addEventListener('change', handleSwitchChange);

                // Ejecutar la función inicial basada en el estado actual del interruptor
                handleSwitchChange({ target: document.getElementById('mySwitch') });
            /*****************************************/

            var numeroInputs = 0;
            var arregloIdInputs = [];
            var retencionEstado = false; // true  aplica retencion, false no aplica retencion;

            window.onload = obtenerTipoPago;
            var public_path = "{{ asset('catalogo/') }}";
            var diasCredito = 0;

            //validando que no escriban un numero que no este entre 0 y 25
            function validarDescuento(){
                const numeroInput = document.getElementById('porDescuento');
                const mensajeError = document.getElementById('mensajeError');
                const numero = parseFloat(numeroInput.value);

                if (isNaN(numero) || numero < 0 || numero > 100) {
                    mensajeError.textContent = 'Este campo solo admite un valor entre 0 a 100';
                    numeroInput.value = '';
                } else {
                    mensajeError.textContent = '';
                }
            }


            $('#seleccionarProducto').select2({
                ajax: {
                    url: '/productos/listar/',
                    data: function(params) {
                        var query = {
                            search: params.term,
                            type: 'public',
                            page: params.page || 1
                        }

                        // Query parameters will be ?search=[term]&type=public

                        return query;
                    }
                }
            });

            function prueba() {

                var element = document.getElementById('botonAdd');
                element.classList.remove("d-none");

            }

            function obtenerBodegas(id) {

                document.getElementById('bodega').innerHTML = "<option  selected disabled>--Seleccione una bodega--</option>";
                let idProducto = id;
                $('#bodega').select2({
                    ajax: {
                        url: '/cotizacion/listar/bodegas/' + idProducto,
                        data: function(params) {
                            var query = {
                                search: params.term,
                                type: 'public',
                                page: params.page || 1,
                                idProducto: idProducto
                            }

                            // Query parameters will be ?search=[term]&type=public
                            return query;
                        }
                    }
                });
            }

            function obtenerTipoPago() {

                axios.get('/ventas/tipo/pago')
                    .then(response => {

                        let tipoDePago = response.data.tipos;
                        let numeroVenta = response.data.numeroVenta.numero;

                        let htmlPagos = '  <option value="" selected disabled >--Seleccione una opcion--</option>';

                        tipoDePago.forEach(element => {

                            htmlPagos += `
                            <option value="${element.id}" >${element.descripcion}</option>
                            `
                        });

                        document.getElementById('tipoPagoVenta').innerHTML = htmlPagos;



                    })
                    .catch(err => {
                        console.log(err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error...',
                            text: "Ha ocurrido un error al obtener los tipos de pago"
                        })
                    })

            }

            function obtenerImagenes() {
                let id = document.getElementById('seleccionarProducto').value;

                document.getElementById("bodega").disabled = false;
                let htmlImagenes = '';
                axios.post('/producto/listar/imagenes', {
                        id: id,

                    })
                    .then(response => {

                        let imagenes = response.data.imagenes;

                        if (imagenes.length == 0) {


                            htmlImagenes += `
                            <div class="carousel-item active " >
                                <img  class="d-block  img-size" src="${public_path+'/'+'noimage.png'}" alt="noimage.png"  >
                            </div>`

                            document.getElementById('bloqueImagenes').innerHTML = htmlImagenes;

                            var element = document.getElementById('botonAdd');
                            element.classList.remove("d-none");

                        } else {
                            imagenes.forEach(element => {

                                if (element.contador == 1) {
                                    htmlImagenes += `
                            <div class="carousel-item active " >
                                <img class="d-block  img-size" src="${public_path+'/'+element.url_img}" alt="imagen ${element.contador}"  >
                            </div>`
                                } else {

                                    htmlImagenes += `
                            <div class="carousel-item  " >
                                <img class="d-block  img-size" src="${public_path+'/'+element.url_img}" alt="imagen ${element.contador}"  >
                            </div>`

                                }

                            });

                            document.getElementById('bloqueImagenes').innerHTML = htmlImagenes;


                        }

                        /*var element = document.getElementById('botonAdd');
                        element.classList.add("d-none");*/
                        let a = document.getElementById("detalleProducto");
                        let url = "/producto/detalle/" + id;
                        a.href = url;
                        a.classList.remove("d-none");

                        return;



                    })
                    .catch(err => {

                        console.log(err);

                    })

                obtenerBodegas(id);
                prueba();
                infoProducto(id);
            }

            function infoProducto(id){

                axios.get('/info/producto/expo/'+id)
                    .then(response => {
                        console.log(response);
                       /* let tipoDePago = response.data.tipos;
                        let numeroVenta = response.data.numeroVenta.numero;

                        let htmlPagos = '  <option value="" selected disabled >--Seleccione una opcion--</option>';

                        tipoDePago.forEach(element => {

                            htmlPagos += `
                            <option value="${element.id}" >${element.descripcion}</option>
                            `
                        });

                        document.getElementById('tipoPagoVenta').innerHTML = htmlPagos;*/

                        let html = '<li class="list-group-item"> <b>Categoría </b>: '+response.data.categoria+'</li><li class="list-group-item">  <b>Sub Categoría </b>: '+response.data.sub_categoria+'</li><li class="list-group-item">  <b>Código de Barra </b>: '+response.data.codigo_barra+'</li><li class="list-group-item">  <b>Marca </b>: '+response.data.marca+'</li><li class="list-group-item">  <b>Nombre </b>: '+response.data.nombre+'</li><li class="list-group-item">  <b>Descripción </b>: '+response.data.descripcion+'</li>';


                        document.getElementById('descripcionProducto').innerHTML = html;
                    })
                    .catch(err => {
                        console.log(err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error...',
                            text: "Ha ocurrido un error al obtener los tipos de pago"
                        })
                    })
            }


            function agregarProductoCarrito() {
                let idProducto = document.getElementById('seleccionarProducto').value;

                let idCliente = document.getElementById('seleccionarCliente').value;

               // let data = $("#bodega").select2('data')[0];
                let bodega = 'SALA DE VENTAS';
                let idBodega = 16;
                let idSeccion = 156;


                axios.post('/ventas/datos/producto', {
                        idProducto: idProducto,

                        idCliente: idCliente

                    })
                    .then(response => {

                        let flag = false;
                        arregloIdInputs.forEach(idInpunt => {
                            let idProductoFila = document.getElementById("idProducto" + idInpunt).value;
                            let idSeccionFila = document.getElementById("idSeccion" + idInpunt).value;

                            if (idProducto == idProductoFila && idSeccion == idSeccionFila && !flag) {
                                flag = true;
                            }

                        })

                        if (flag) {
                            Swal.fire({

                                icon: 'warning',
                                title: 'Advertencia!',
                                html: `
                            <p class="text-left">
                                La sección de bodega y producto ha sido agregada anteriormente.<br><br>
                                Por favor verificar la sección de bodega y producto sea distinto a los ya existentes en la lista de venta.<br><br>
                                De ser necesario aumentar la cantidad de producto en la lista de productos seleccionados para la venta.
                            </p>`
                            })

                            return;
                        }

                        let producto = response.data.producto;

                        let arrayUnidades = response.data.unidades;


                        numeroInputs += 1;

                        //     let arraySecciones  = response.data.secciones;
                        // htmlSelectSeccion ="<option selected disabled>--seccion--</option>";

                        // arraySecciones.forEach(seccion => {
                        //     htmlSelectSeccion += `<option values="${seccion.id}" >${seccion.descripcion}</option>`
                        // });

                        htmlSelectUnidades = "";

                        /*<option  value="${producto.precio_base}" data-id="pb">${producto.precio_base} - Base</option>*/
                        htmlprecios = `
                        <option  value="${producto.precio1}" data-id="p1" selected>${producto.precio1} - A</option>
                        <option  value="${producto.precio2}" data-id="p2">${producto.precio2} - B</option>
                        <option  value="${producto.precio3}" data-id="p3">${producto.precio3} - C</option>
                        <option  value="${producto.precio4}" data-id="p4">${producto.precio4} - D</option>
                        `;



                        arrayUnidades.forEach(unidad => {
                            if (unidad.valor_defecto == 1) {
                                htmlSelectUnidades +=
                                    `<option selected value="${unidad.id}" data-id="${unidad.idUnidadVenta}">${unidad.nombre}</option>`;
                            } else {
                                htmlSelectUnidades +=
                                    `<option  value="${unidad.id}" data-id="${unidad.idUnidadVenta}">${unidad.nombre}</option>`;
                            }

                        });


                        html = `
                        <div id='${numeroInputs}' class="row no-gutters">
                                            <div class="form-group col-3">
                                                <div class="d-flex">

                                                    <button class="btn btn-danger" type="button" style="display: inline" onclick="eliminarInput(${numeroInputs})"><i
                                                            class="fa-regular fa-rectangle-xmark"></i>
                                                    </button>

                                                    <input id="idProducto${numeroInputs}" name="idProducto${numeroInputs}" type="hidden" value="${producto.id}">

                                                    <div style="width:100%">
                                                        <label for="nombre${numeroInputs}" class="sr-only">Producto</label>
                                                        <input type="text" placeholder="Producto" id="nombre${numeroInputs}"
                                                            name="nombre${numeroInputs}" class="form-control"
                                                            data-parsley-required "
                                                            autocomplete="off"
                                                            readonly
                                                            value='${producto.nombre}'

                                                            >
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group col-1">
                                                <label for="" class="sr-only">cantidad</label>
                                                <input type="text" value="${bodega}" placeholder="Bodega" id="bodega${numeroInputs}"
                                                    name="bodega${numeroInputs}" class="form-control"
                                                    autocomplete="off"  readonly  >
                                            </div>
                                            <div class="form-group col-2">
                                                <label for="" class="sr-only">precios</label>
                                                <select class="form-control" name="precios${numeroInputs}" id="precios${numeroInputs}"
                                                    data-parsley-required style="height:35.7px;"
                                                    onchange="validacionPrecio(precios${numeroInputs}, precio${numeroInputs})"
                                                    >
                                                            ${htmlprecios}
                                                </select>


                                            </div>

                                            <div class="form-group col-1">
                                                <label for="precio${numeroInputs}" class="sr-only">Precio</label>
                                                <input type="number" placeholder="Precio Unidad" id="precio${numeroInputs}"
                                                    name="cantidad${numeroInputs}" class="form-control" min="${producto.precio1}" data-parsley-required
                                                    autocomplete="off" onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                                            </div>


                                            <div class="form-group col-1">
                                                <label for="cantidad${numeroInputs}" class="sr-only">cantidad</label>
                                                <input type="number" placeholder="Cantidad" id="cantidad${numeroInputs}"
                                                    name="cantidad${numeroInputs}" class="form-control" min="1" data-parsley-required
                                                    autocomplete="off" onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                                            </div>

                                            <div class="form-group col-1">
                                                <label for="" class="sr-only">unidad</label>
                                                <select class="form-control" name="unidad${numeroInputs}" id="unidad${numeroInputs}"
                                                    data-parsley-required style="height:35.7px;"
                                                    onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                                                            ${htmlSelectUnidades}
                                                </select>


                                            </div>




                                            <div class="form-group col-1">
                                                <label for="subTotalMostrar${numeroInputs}" class="sr-only">Sub Total</label>
                                                <input type="text" placeholder="Sub total" id="subTotalMostrar${numeroInputs}"
                                                    name="subTotalMostrar${numeroInputs}" class="form-control"
                                                    autocomplete="off"
                                                    readonly >

                                                <input id="subTotal${numeroInputs}" name="subTotal${numeroInputs}" type="hidden" value="" required>
                                                <input type="hidden" id="acumuladoDescuento${numeroInputs}" name="acumuladoDescuento${numeroInputs}" >
                                            </div>

                                            <div class="form-group col-1">
                                                <label for="isvProductoMostrar${numeroInputs}" class="sr-only">ISV</label>
                                                <input type="text" placeholder="ISV" id="isvProductoMostrar${numeroInputs}"
                                                    name="isvProductoMostrar${numeroInputs}" class="form-control"
                                                    autocomplete="off"
                                                    readonly >

                                                    <input id="isvProducto${numeroInputs}" name="isvProducto${numeroInputs}" type="hidden" value="" required>
                                            </div>

                                            <div class="form-group col-1">
                                                <label for="totalMostrar${numeroInputs}" class="sr-only">Total</label>
                                                <input type="text" placeholder="Total" id="totalMostrar${numeroInputs}"
                                                    name="totalMostrar${numeroInputs}" class="form-control"
                                                    autocomplete="off"
                                                    readonly >

                                                    <input id="total${numeroInputs}" name="total${numeroInputs}" type="hidden" value="" required>


                                            </div>

                                            <input id="idBodega${numeroInputs}" name="idBodega${numeroInputs}" type="hidden" value="${idBodega}">
                                            <input id="idSeccion${numeroInputs}" name="idSeccion${numeroInputs}" type="hidden" value="${idSeccion}">
                                            <input id="restaInventario${numeroInputs}" name="restaInventario${numeroInputs}" type="hidden" value="">
                                            <input id="isv${numeroInputs}" name="isv${numeroInputs}" type="hidden" value="${producto.isv}">



                        </div>
                        `;

                        arregloIdInputs.splice(numeroInputs, 0, numeroInputs);
                        document.getElementById('divProductos').insertAdjacentHTML('beforeend', html);


                        return;

                    })
                    .catch(err => {

                        console.error(err);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: "Ha ocurrido un error al agregar el producto a la compra."
                        })
                    })
            }

            function validacionPrecio(idPrecios, idprecio){

                var idPrecioSeleccionado = idPrecios.options[idPrecios.selectedIndex].getAttribute("data-id");
                var precioSeleccionado = idPrecios.value;
                var idprecioIngresado = idprecio.id;
                var precioIngresado = idprecio.value;

                // Obtener el número del input para actualizar campos hidden
                const numeroInput = idprecioIngresado.replace('precio', '');

                // Actualizar campo hidden para el idPrecioSeleccionado
                const idPrecioSeleccionadoField = document.getElementById(`idPrecioSeleccionado${numeroInput}`);

                if (idPrecioSeleccionadoField) {
                    idPrecioSeleccionadoField.value = idPrecioSeleccionado;
                }

                    document.getElementById(idprecioIngresado).value = precioSeleccionado;
                    document.getElementById(idprecioIngresado).setAttribute("min",precioSeleccionado);


             }

            function eliminarInput(id) {
                const element = document.getElementById(id);
                element.remove();


                var myIndex = arregloIdInputs.indexOf(id);
                if (myIndex !== -1) {
                    arregloIdInputs.splice(myIndex, 1);
                    this.totalesGenerales();
                }

            }
            function myRound(num, dec) {
                var exp = Math.pow(10, dec || 2); // 2 decimales por defecto
                return parseInt(num * exp, 10) / exp;
            }

            function calcularTotalesInicioPagina() {

                let arrayInputs = this.arregloIdInputs;


                let valorInputPrecio = 0;
                let valorInputCantidad = 0;
                let valorSelectUnidad =0;
                let isvProducto = 0;

                let subTotal = 0;
                let isv =0;
                let total = 0;
                let descuento = 0;
                let descuentoCalculado = 0

                arrayInputs.forEach(id => {
                    // calcularTotales(idPrecio, idCantidad, isvProducto, idUnidad, id)
                        valorInputPrecio = document.getElementById('precio' + id).value;
                        valorInputCantidad = document.getElementById('cantidad' + id).value;
                        valorSelectUnidad = document.getElementById('unidad' + id).value;
                        isvProducto = document.getElementById("isv"+id).value;

                            if (valorInputPrecio && valorInputCantidad) {

                                descuento = document.getElementById("porDescuento").value;

                               /* if (descuento > 0){
                                    subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                                    descuentoCalculado = subTotal * (descuento/100);
                                    subTotal = subTotal - descuentoCalculado;
                                    isv = subTotal * (isvProducto / 100);
                                    total = subTotal + (subTotal * (isvProducto / 100));
                                }else{
                                    descuentoCalculado = 0;
                                    subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                                    isv = subTotal * (isvProducto / 100);
                                    total = subTotal + subTotal * (isvProducto / 100);
                                }*/

                                if (descuento > 0) {
                                    subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                                    descuentoCalculado = subTotal * (descuento / 100);
                                    subTotal = subTotal - descuentoCalculado;
                                    let isv1 = subTotal * (isvProducto / 100);
                                    let isvSinRedondeo1 = parseFloat(isv1.toFixed(2));
                                    isv = isvSinRedondeo1 ;
                                    total = subTotal + (subTotal * (isvProducto / 100));
                                } else {
                                    descuentoCalculado = 0
                                    subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                                    let isv2 = subTotal * (isvProducto / 100);
                                    let isvSinRedondeo2 = parseFloat(isv2.toFixed(2));
                                    isv = isvSinRedondeo2;
                                    total = subTotal + subTotal * (isvProducto / 100);
                                }

                                document.getElementById("acumuladoDescuento"+id).value = descuentoCalculado.toFixed(2);

                                document.getElementById('total' + id).value = total.toFixed(2);
                                document.getElementById('totalMostrar' + id).value = new Intl.NumberFormat('es-HN', {
                                    style: 'currency',
                                    currency: 'HNL',
                                    minimumFractionDigits: 2,
                                }).format(total)

                                document.getElementById('subTotal' + id).value = subTotal.toFixed(2);
                                document.getElementById('subTotalMostrar' + id).value = new Intl.NumberFormat('es-HN', {
                                    style: 'currency',
                                    currency: 'HNL',
                                    minimumFractionDigits: 2,
                                }).format(subTotal)


                                document.getElementById('isvProducto' + id).value = isv.toFixed(2);
                                document.getElementById('isvProductoMostrar' + id).value = new Intl.NumberFormat(
                                    'es-HN', {
                                        style: 'currency',
                                        currency: 'HNL',
                                        minimumFractionDigits: 2,
                                    }).format(isv)

                            }

                        });



                    this.totalesGenerales();
                    return 0;


                }

            function calcularTotales(idPrecio, idCantidad, isvProducto, idUnidad, id, idRestaInventario) {


                    let valorInputPrecio = Number(idPrecio.value).toFixed(2);
                    let valorInputCantidad = idCantidad.value;
                    let valorSelectUnidad = idUnidad.value;

                    let subTotal = 0;
                    let isv = 0;
                    let total =0;

                    var descuentoCalculado = 0;

                    if (valorInputPrecio && valorInputCantidad) {
                        var descuento = $('#porDescuento').val();


                       /* if (descuento > 0){
                             subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                            descuentoCalculado = subTotal * (descuento/100);

                            $('#acumuladoDescuento'+id).val(descuentoCalculado);


                             subTotal = subTotal - descuentoCalculado;

                             isv = subTotal * (isvProducto / 100);
                             total = subTotal + (subTotal * (isvProducto / 100));


                        }else{
                            $('#descuentoGeneral').val(0);
                             subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                             isv = subTotal * (isvProducto / 100);
                             total = subTotal + subTotal * (isvProducto / 100);

                        }*/
                        if (descuento > 0) {
                            subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                            descuentoCalculado = subTotal * (descuento / 100);
                            $('#acumuladoDescuento'+id).val(descuentoCalculado);
                            subTotal = subTotal - descuentoCalculado;
                            let isv1 = subTotal * (isvProducto / 100);
                            let isvSinRedondeo1 = parseFloat(isv1.toFixed(2));
                            isv = isvSinRedondeo1 ;
                            total = subTotal + (subTotal * (isvProducto / 100));
                        } else {
                            $('#descuentoGeneral').val(0);
                            subTotal = valorInputPrecio * (valorInputCantidad * valorSelectUnidad);
                            let isv2 = subTotal * (isvProducto / 100);
                            let isvSinRedondeo2 = parseFloat(isv2.toFixed(2));
                            isv = isvSinRedondeo2;
                            total = subTotal + subTotal * (isvProducto / 100);
                        }


                        document.getElementById('total' + id).value = total.toFixed(2);
                        document.getElementById('totalMostrar' + id).value = new Intl.NumberFormat('es-HN', {
                            style: 'currency',
                            currency: 'HNL',
                            minimumFractionDigits: 2,
                        }).format(total)

                        document.getElementById('subTotal' + id).value = subTotal.toFixed(2);
                        document.getElementById('subTotalMostrar' + id).value = new Intl.NumberFormat('es-HN', {
                            style: 'currency',
                            currency: 'HNL',
                            minimumFractionDigits: 2,
                        }).format(subTotal)


                        document.getElementById('isvProducto' + id).value = isv.toFixed(2);
                        document.getElementById('isvProductoMostrar' + id).value = new Intl.NumberFormat('es-HN', {
                            style: 'currency',
                            currency: 'HNL',
                            minimumFractionDigits: 2,
                        }).format(isv)


                        idRestaInventario.value = valorInputCantidad * valorSelectUnidad;

                        // Actualizar el campo idUnidadVenta si existe
                        const idUnidadVentaField = document.getElementById(`idUnidadVenta${id}`);
                        if (idUnidadVentaField && idUnidad.selectedOptions.length > 0) {
                            const selectedOption = idUnidad.selectedOptions[0];
                            const idUnidadVenta = selectedOption.getAttribute('data-id');
                            idUnidadVentaField.value = idUnidadVenta;
                            console.log(`✅ Campo idUnidadVenta${id} actualizado:`, idUnidadVenta);
                        }

                        this.totalesGenerales();





                    }

                idPrecio.value = valorInputPrecio;
                return 0;


            }

            function totalesGenerales() {

                //console.log(arregloIdInputs);

                if (numeroInputs == 0) {
                    return;
                }



                let totalGeneralValor = new Number(0);
                let totalISV = new Number(0);
                let subTotalGeneralGrabadoValor = new Number(0);
                let subTotalGeneralExcentoValor = new Number(0);
                let subTotalGeneral = new Number(0);
                let subTotalFila = 0;
                let isvFila = 0;
                let acumularDescuento = new Number(0);


                for (let i = 0; i < arregloIdInputs.length; i++) {

                    subTotalFila = new Number(document.getElementById('subTotal' + arregloIdInputs[i]).value);
                    isvFila = new Number(document.getElementById('isvProducto' + arregloIdInputs[i]).value);

                    ;

                    if (isvFila == 0) {
                        subTotalGeneralExcentoValor += new Number(document.getElementById('subTotal' + arregloIdInputs[i])
                            .value);
                    } else if (subTotalFila > 0) {
                        subTotalGeneralGrabadoValor += new Number(document.getElementById('subTotal' + arregloIdInputs[i])
                            .value);
                    }

                    subTotalGeneral += new Number(document.getElementById('subTotal' + arregloIdInputs[i]).value);


                    totalISV += new Number(document.getElementById('isvProducto' + arregloIdInputs[i]).value);
                    totalGeneralValor += new Number(document.getElementById('total' + arregloIdInputs[i]).value);


                    acumularDescuento += new Number($('#acumuladoDescuento'+arregloIdInputs[i]).val());

                }




                document.getElementById('descuentoGeneral').value = acumularDescuento.toFixed(2);

                document.getElementById('descuentoMostrar').value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(acumularDescuento)

                document.getElementById('subTotalGeneral').value = subTotalGeneral.toFixed(2);
                document.getElementById('subTotalGeneralMostrar').value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(subTotalGeneral)

                document.getElementById('subTotalGeneralGrabado').value = subTotalGeneralGrabadoValor.toFixed(2);
                document.getElementById('subTotalGeneralGrabadoMostrar').value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(subTotalGeneralGrabadoValor)

                document.getElementById('subTotalGeneralExcento').value = subTotalGeneralExcentoValor.toFixed(2);
                document.getElementById('subTotalGeneralExcentoMostrar').value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(subTotalGeneralExcentoValor)

                document.getElementById('isvGeneral').value = totalISV.toFixed(2);
                document.getElementById('isvGeneralMostrar').value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(totalISV)

                document.getElementById('totalGeneral').value = totalGeneralValor.toFixed(2);
                document.getElementById('totalGeneralMostrar').value = new Intl.NumberFormat('es-HN', {
                    style: 'currency',
                    currency: 'HNL',
                    minimumFractionDigits: 2,
                }).format(totalGeneralValor)





                return 0;


            }

            function validarFechaPago() {

                let tipoPago;

                tipoPago = document.getElementById('tipoPagoVenta').value;

                if (tipoPago == 2) {

                    // document.getElementById('fecha_vencimiento').value = "empty";
                    document.getElementById('fecha_vencimiento').readOnly = false;
                    this.sumarDiasCredito();

                } else {
                    document.getElementById('fecha_vencimiento').value = "{{ date('Y-m-d') }}";

                    document.getElementById('fecha_vencimiento').readOnly = true;

                }

                return 0;


            }

            function obtenerDatosCliente() {
                let idCliente = document.getElementById("seleccionarCliente").value;
                axios.post("/ventas/datos/cliente", {
                        id: idCliente
                    })
                    .then(
                        response => {

                            let data = response.data.datos;
                           // console.log(data);
                            let html = '<option value="'+data.idVendedor+'" selected disable>'+data.vendedor+'</option>';
                            document.getElementById('vendedor').innerHTML = html;

                            if (data.id == 1) {
                                document.getElementById("nombre_cliente_ventas").readOnly = false;
                                document.getElementById("nombre_cliente_ventas").value = '';

                                document.getElementById("rtn_ventas").readOnly = false;
                                document.getElementById("rtn_ventas").value = '';
                                let selectBox = document.getElementById("tipoPagoVenta");
                                selectBox.remove(2);

                            } else {
                                document.getElementById("nombre_cliente_ventas").readOnly = true;
                                document.getElementById("rtn_ventas").readOnly = true;

                                document.getElementById("nombre_cliente_ventas").value = data.nombre;
                                document.getElementById("rtn_ventas").value = data.rtn;
                                //obtenerTipoPago();
                                diasCredito = data.dias_credito;
                            }



                        }
                    )
                    .catch(err => {

                        console.log(err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error...',
                            text: "Ha ocurrido un error al obtener los datos del cliente"
                        })


                    })

            }

            $(document).on('submit', '#crear_venta',
                function(event) {
                    event.preventDefault();
                    guardarVenta();
            });

            function guardarVenta() {

                document.getElementById("guardar_cotizacion_btn").style.display = "none";
                /* document.getElementById("guardar_cotizacion_btn").disabled = true; */

                console.log('=== INICIANDO GUARDADO DE COTIZACIÓN ===');
                console.log('Array de IDs:', arregloIdInputs);
                console.log('Número de inputs:', numeroInputs);

                // Verificar campos antes de enviar
                if (typeof window.verificarCamposGuardado === 'function') {
                    window.verificarCamposGuardado();
                }

                // Dar tiempo para que los cálculos se completen
                setTimeout(() => {
                    console.log('Iniciando captura de datos después del timeout...');
                    capturarYEnviarDatos();
                }, 100); // Reducido a 100ms
            }

            function capturarYEnviarDatos() {
                //console.log('DESCUENTO DE MIERDA ABAJO MOSTRAR');

                //console.log(document.getElementById('descuentoMostrar').value);

                //console.log('DESCUENTO CULERO ARRIBA GENERAL');
                //console.log(document.getElementById('descuentoGeneral').value);
                //console.log('Recopilando datos del formulario manualmente...');

                // Recopilar datos básicos del formulario
                const formData = {};
                const formElements = document.getElementById('crear_venta').elements;

                // Agregar todos los campos del formulario base
                for (let element of formElements) {
                    if (element.name && element.type !== 'button') {
                        formData[element.name] = element.value;
                       console.log(`Campo base: ${element.name} = ${element.value}`);
                    }
                }

                // También recopilar TODOS los campos dentro del divProductos
                const divProductos = document.getElementById('divProductos');
                console.log('divProductos encontrado:', divProductos);

                if (divProductos) {
                    const allInputsInDiv = divProductos.querySelectorAll('input, select');
                    console.log(`Encontrados ${allInputsInDiv.length} elementos en divProductos`);

                    allInputsInDiv.forEach((element, index) => {
                        console.log(`Elemento ${index}:`, {
                            tag: element.tagName,
                            type: element.type,
                            id: element.id,
                            name: element.name,
                            value: element.value
                        });

                        if (element.name) {
                            formData[element.name] = element.value;
                            console.log(`Campo producto: ${element.name} = ${element.value}`);
                        } else {
                            console.log(`⚠️ Elemento sin name:`, element);
                        }
                    });
                } else {
                    console.error('❌ divProductos NO encontrado!');
                }

                // También capturar campos específicos del producto por ID
                console.log('Capturando campos específicos por ID...');
                arregloIdInputs.forEach(id => {
                    console.log(`--- Capturando campos para producto ID: ${id} ---`);

                    // Lista de campos específicos que necesitamos
                    const camposRequeridos = [
                        'idProducto', 'nombre', 'bodega', 'precio', 'cantidad',
                        'subTotal', 'isvProducto', 'total', 'idBodega',
                        'idSeccion', 'restaInventario', 'isv', 'precios', 'unidad'
                    ];

                    camposRequeridos.forEach(campo => {
                        const nombreCampo = campo + id;
                        const elemento = document.getElementById(nombreCampo);

                        if (elemento) {
                            const valor = elemento.value || elemento.textContent || '';
                            formData[nombreCampo] = valor;
                            console.log(`✅ ${nombreCampo}: "${valor}"`);
                        } else {
                            console.log(`❌ ${nombreCampo}: NO ENCONTRADO`);
                        }
                    });
                });

                console.log('Datos básicos del formulario:', formData);

                let longitudArreglo = arregloIdInputs.length;
                console.log('Longitud del arreglo:', longitudArreglo);

                for (var i = 0; i < longitudArreglo; i++) {

                    console.log(`=== PROCESANDO PRODUCTO ${i + 1} ===`);
                    console.log('ID actual:', arregloIdInputs[i]);

                    let name = "unidad" + arregloIdInputs[i];
                    let nameForm = "idUnidadVenta" + arregloIdInputs[i];

                    let e = document.getElementById(name);
                    console.log('Elemento unidad encontrado:', e);

                    let idUnidadVenta = e.options[e.selectedIndex].getAttribute("data-id");
                    console.log('ID Unidad Venta:', idUnidadVenta);

                    formData[nameForm] = idUnidadVenta;

                    /**************************************************************/

                    let name2 = "precios" + arregloIdInputs[i];
                    let nameForm2 = "idPrecioSeleccionado" + arregloIdInputs[i];

                    let a = document.getElementById(name2);
                    console.log('Elemento precio encontrado:', a);

                    let idPrecioSeleccionado = a.options[a.selectedIndex].getAttribute("data-id");
                    console.log('ID Precio Seleccionado:', idPrecioSeleccionado);

                    formData[nameForm2] = idPrecioSeleccionado;



                    /**************************************************************/

                }
                formData["numeroInputs"] = numeroInputs;

                let text = arregloIdInputs.toString();
                formData["arregloIdInputs"] = text;

                // ✅ FIX: Agregar valor dummy para seleccionarProducto si tiene productos escaneados
                if (arregloIdInputs.length > 0 && !formData["seleccionarProducto"]) {
                    formData["seleccionarProducto"] = "dummy"; // Valor dummy para pasar validación
                    console.log('✅ Agregado valor dummy para seleccionarProducto');
                }

                console.log('=== DATOS FINALES A ENVIAR ===');
                console.log('FormData Object:', formData);
                console.log('Número de campos:', Object.keys(formData).length);

                const options = {
                    headers: {
                        "content-type": "application/json"
                    }
                }

                console.log('Enviando petición POST a /expo/cotizacion...');


                axios.post('/expo/cotizacion', formData, options)
                    .then(response => {
                        console.log('=== RESPUESTA DEL SERVIDOR ===');
                        console.log('Response status:', response.status);
                        console.log('Response data:', response.data);

                        let data = response.data;

                        //console.log(response.data);

                       /*  if (data.idFactura == 0) {


                            Swal.fire({
                                icon: data.icon,
                                title: data.title,
                                html: data.text,
                            })
                            document.getElementById("guardar_cotizacion_btn").disabled = false;
                            return;

                        }

                        Swal.fire({
                            confirmButtonText: 'Cerrar',
                            confirmButtonColor: '#5A6268',
                            icon: data.icon,
                            title: data.title,
                            html: data.text
                        }) */


                        /* document.getElementById('bloqueImagenes').innerHTML = '';
                        document.getElementById('divProductos').innerHTML = '';

                        document.getElementById("crear_venta").reset();
                        $('#crear_venta').parsley().reset();

                        var element = document.getElementById('detalleProducto');
                        element.classList.add("d-none");
                        element.href = "";

                        document.getElementById("seleccionarCliente").innerHTML =
                            '<option value="" selected disabled>--Seleccionar un cliente--</option>';

                        document.getElementById('seleccionarProducto').innerHTML =
                            '<option value="" selected disabled>--Seleccione un producto--</option>';
                        document.getElementById('bodega').innerHTML =
                            '<option value="" selected disabled>--Seleccione un producto--</option>';
                        document.getElementById("bodega").disabled = true;



                        let element2 = document.getElementById('detalleProducto');
                        element2.classList.add("d-none");


                        arregloIdInputs = [];
                        numeroInputs = 0;
                        retencionEstado = false;


                        document.getElementById("guardar_cotizacion_btn").disabled = false; */

                            /* location.reload(); */
                        const input = document.getElementById("pedido_id");
                        input.value = data.pedido_id;

                        const alerta = document.createElement("div");
                        alerta.className = "alert alert-success alert-dismissible fade show";
                        alerta.role = "alert";
                        alerta.innerHTML = `
                            ✅ Se ha guardado el pedido correctamente.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        `;

                        const contenedor = document.getElementById("alert-fixed");
                        contenedor.appendChild(alerta);

                        // 📌 Scroll automático hasta la alerta
                        alerta.scrollIntoView({ behavior: "smooth", block: "center" });

                        // Desaparece a los 5 segundos
                        setTimeout(() => {
                            alerta.classList.remove("show");
                            alerta.classList.add("hide");
                            setTimeout(() => alerta.remove(), 500);
                        }, 3000);


                        document.getElementById("guardar_cotizacion_btn").style.display = "inline-block";

                    })
                    .catch(err => {
                        console.log('=== ERROR EN EL GUARDADO ===');
                        console.error('Error completo:', err);
                        console.error('Error response:', err.response);

                        if (err.response) {
                            console.error('Status:', err.response.status);
                            console.error('Data:', err.response.data);
                            console.error('Headers:', err.response.headers);
                        }

                        document.getElementById("guardar_cotizacion_btn").disabled = false;
                        let data = err.response.data;
                        console.log(err);
                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            text: data.text
                        })
                    })
            }

            function sumarDiasCredito() {
                tipoPago = document.getElementById('tipoPagoVenta').value;

                if (tipoPago == 2) {

                    let fechaEmision = document.getElementById("fecha_emision").value;
                    let date = new Date(fechaEmision);
                    date.setDate(date.getDate() + diasCredito);
                    let suma = date.toISOString().split('T')[0];
                    //console.log( diasCredito);

                    document.getElementById("fecha_vencimiento").value = suma;

                }
            }

            // Función para agregar producto al carrito mediante código de barras
            function agregarProductoCarritoBarra(codigoBarra) {
                console.log('� Agregando producto al carrito con código:', codigoBarra);

                // Función para reproducir sonido como le gusta a yeff
                function playSound(type) {
                    try {
                        let frequency = type === 'success' ? 800 : 300;
                        let duration = type === 'success' ? 200 : 500;

                        // Crear contexto de audio
                        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                        const oscillator = audioContext.createOscillator();
                        const gainNode = audioContext.createGain();

                        oscillator.connect(gainNode);
                        gainNode.connect(audioContext.destination);

                        oscillator.frequency.value = frequency;
                        oscillator.type = 'sine';

                        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + duration / 1000);

                        oscillator.start(audioContext.currentTime);
                        oscillator.stop(audioContext.currentTime + duration / 1000);
                    } catch (e) {
                        console.log('No se pudo reproducir el sonido:', e);
                    }
                }

                // Usar el método existente obtenerDatosProductoExpo
                axios.post('/ventas/datos/producto/expo', {
                    barraProd: codigoBarra
                })
                .then(response => {
                    console.log('=== RESPUESTA DATOS PRODUCTO ===');
                    console.log('Response data:', response.data);

                    // Verificar si la respuesta indica éxito
                    if (!response.data.success) {
                        playSound('error');
                        Swal.fire({
                            icon: 'error',
                            title: '¡Producto No Encontrado!',
                            html: `
                                <div style="text-align: left;">
                                    <p><strong>Código escaneado:</strong> ${codigoBarra}</p>
                                    <p><strong>Estado:</strong> No existe en la base de datos</p>
                                    <hr>
                                    <p style="color: #666; font-size: 0.9em;">
                                        • Verifique que el código esté completo<br>
                                        • Asegúrese de que el producto esté registrado<br>
                                        • Contacte al administrador si persiste el problema
                                    </p>
                                </div>
                            `,
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#d33'
                        });
                        return;
                    }

                    let producto = response.data.producto;
                    let arrayUnidades = response.data.unidades;

                    console.log('Producto encontrado:', producto);
                    console.log('Unidades disponibles:', arrayUnidades);

                    if (!producto || !producto.id) {
                        playSound('error');
                        Swal.fire({
                            icon: 'warning',
                            title: 'Producto no encontrado!',
                            text: `No se encontró ningún producto con el código de barras: ${codigoBarra}`,
                            confirmButtonColor: '#f39c12'
                        });
                        return;
                    }

                    // Reproducir sonido de éxito
                    playSound('success');

                    // Usar la misma lógica que agregarProductoCarrito
                    let bodega = 'SALA DE VENTAS';
                    let idBodega = 16;
                    let idSeccion = 156;
                    let idProducto = producto.id;

                    // Verificar si el producto ya existe en el carrito
                    let flag = false;
                    arregloIdInputs.forEach(idInpunt => {
                        let idProductoFila = document.getElementById("idProducto" + idInpunt).value;
                        let idSeccionFila = document.getElementById("idSeccion" + idInpunt).value;

                        if (idProducto == idProductoFila && idSeccion == idSeccionFila && !flag) {
                            flag = true;
                        }
                    });

                    if (flag) {
                        playSound('error');
                        Swal.fire({
                            icon: 'info',
                            title: '¡Producto ya agregado!',
                            text: 'Este producto ya se encuentra en el carrito. Modifique la cantidad si es necesario.',
                            confirmButtonColor: '#17a2b8'
                        });
                        return;
                    }

                    numeroInputs += 1;

                    let htmlSelectUnidades = "";
                    let htmlprecios = `
                    <option data-id="0" selected>--Seleccione precio--</option>
                    <option  value="${producto.precio_base}" data-id="pb">${producto.precio_base} - Base</option>
                    <option  value="${producto.precio1}" data-id="p1">${producto.precio1} - A</option>
                    <option  value="${producto.precio2}" data-id="p2">${producto.precio2} - B</option>
                    <option  value="${producto.precio3}" data-id="p3">${producto.precio3} - C</option>
                    <option  value="${producto.precio4}" data-id="p4">${producto.precio4} - D</option>
                    `;

                    arrayUnidades.forEach(unidad => {
                        if (unidad.valor_defecto == 1) {
                            htmlSelectUnidades +=
                                `<option selected value="${unidad.id}" data-id="${unidad.idUnidadVenta}">${unidad.nombre}</option>`;
                        } else {
                            htmlSelectUnidades +=
                                `<option  value="${unidad.id}" data-id="${unidad.idUnidadVenta}">${unidad.nombre}</option>`;
                        }
                    });

                    let html = `
                    <div id='${numeroInputs}' class="row no-gutters">
                        <div class="form-group col-3">
                            <div class="d-flex">
                                <button class="btn btn-danger" type="button" style="display: inline" onclick="eliminarInput(${numeroInputs})"><i
                                        class="fa-regular fa-rectangle-xmark"></i>
                                </button>
                                <input id="idProducto${numeroInputs}" name="idProducto${numeroInputs}" type="hidden" value="${producto.id}">
                                <div style="width:100%">
                                    <label for="nombre${numeroInputs}" class="sr-only">Producto</label>
                                    <input type="text" placeholder="Producto" id="nombre${numeroInputs}"
                                        name="nombre${numeroInputs}" class="form-control"
                                        data-parsley-required
                                        autocomplete="off"
                                        readonly
                                        value='${producto.nombre} [ESCANEADO]'
                                        style="background-color: #e8f5e8; border-color: #28a745; font-weight: bold;">
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-1">
                            <label for="" class="sr-only">cantidad</label>
                            <input type="text" value="${bodega}" placeholder="Bodega" id="bodega${numeroInputs}"
                                name="bodega${numeroInputs}" class="form-control"
                                autocomplete="off"  readonly  >
                        </div>
                        <div class="form-group col-2">
                            <label for="" class="sr-only">precios</label>
                            <select class="form-control" name="precios${numeroInputs}" id="precios${numeroInputs}"
                                data-parsley-required style="height:35.7px;"
                                onchange="validacionPrecio(precios${numeroInputs}, precio${numeroInputs})"
                                >
                                    ${htmlprecios}
                            </select>
                        </div>
                        <div class="form-group col-1">
                            <label for="precio${numeroInputs}" class="sr-only">Precio</label>
                            <input type="number" placeholder="Precio Unidad" id="precio${numeroInputs}"
                                name="precio${numeroInputs}" class="form-control"  data-parsley-required step="any"
                                autocomplete="off" onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                        </div>
                        <div class="form-group col-1">
                            <label for="cantidad${numeroInputs}" class="sr-only">cantidad</label>
                            <input type="number" placeholder="Cantidad" id="cantidad${numeroInputs}"
                                name="cantidad${numeroInputs}" class="form-control" min="1" data-parsley-required
                                autocomplete="off" onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                        </div>
                        <div class="form-group col-1">
                            <label for="" class="sr-only">unidad</label>
                            <select class="form-control" name="unidad${numeroInputs}" id="unidad${numeroInputs}"
                                data-parsley-required style="height:35.7px;"
                                onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},unidad${numeroInputs},${numeroInputs},restaInventario${numeroInputs})">
                                    ${htmlSelectUnidades}
                            </select>
                        </div>
                        <div class="form-group col-1">
                            <label for="subTotalMostrar${numeroInputs}" class="sr-only">Sub Total</label>
                            <input type="text" placeholder="Sub total" id="subTotalMostrar${numeroInputs}"
                                name="subTotalMostrar${numeroInputs}" class="form-control"
                                autocomplete="off"
                                readonly >
                            <input id="subTotal${numeroInputs}" name="subTotal${numeroInputs}" type="hidden" value="" required>
                            <input type="hidden" id="acumuladoDescuento${numeroInputs}" name="acumuladoDescuento${numeroInputs}" >
                        </div>
                        <div class="form-group col-1">
                            <label for="isvProductoMostrar${numeroInputs}" class="sr-only">ISV</label>
                            <input type="text" placeholder="ISV" id="isvProductoMostrar${numeroInputs}"
                                name="isvProductoMostrar${numeroInputs}" class="form-control"
                                autocomplete="off"
                                readonly >
                            <input id="isvProducto${numeroInputs}" name="isvProducto${numeroInputs}" type="hidden" value="" required>
                        </div>
                        <div class="form-group col-1">
                            <label for="totalMostrar${numeroInputs}" class="sr-only">Total</label>
                            <input type="text" placeholder="Total" id="totalMostrar${numeroInputs}"
                                name="totalMostrar${numeroInputs}" class="form-control"
                                autocomplete="off"
                                readonly >
                            <input id="total${numeroInputs}" name="total${numeroInputs}" type="hidden" value="" required>
                        </div>
                        <input id="idBodega${numeroInputs}" name="idBodega${numeroInputs}" type="hidden" value="${idBodega}">
                        <input id="idSeccion${numeroInputs}" name="idSeccion${numeroInputs}" type="hidden" value="${idSeccion}">
                        <input id="restaInventario${numeroInputs}" name="restaInventario${numeroInputs}" type="hidden" value="">
                        <input id="isv${numeroInputs}" name="isv${numeroInputs}" type="hidden" value="${producto.isv}">
                        <input id="idPrecioSeleccionado${numeroInputs}" name="idPrecioSeleccionado${numeroInputs}" type="hidden" value="pb">
                        <input id="idUnidadVenta${numeroInputs}" name="idUnidadVenta${numeroInputs}" type="hidden" value="">
                    </div>
                    `;

                    arregloIdInputs.splice(numeroInputs, 0, numeroInputs);
                    document.getElementById('divProductos').insertAdjacentHTML('beforeend', html);

                    console.log('=== CONFIGURACIÓN AUTOMÁTICA DEL PRODUCTO ===');
                    console.log('Número de input:', numeroInputs);
                    console.log('ID agregado al array:', arregloIdInputs);

                    // Configurar precio base automáticamente para el producto escaneado
                    setTimeout(() => {
                        try {
                            console.log('Iniciando configuración automática...');

                            // Seleccionar precio base automáticamente
                            const selectPrecios = document.getElementById(`precios${numeroInputs}`);
                            if (selectPrecios) {
                                selectPrecios.value = producto.precio_base;
                                console.log('Precio base configurado:', producto.precio_base);
                                // Disparar el evento onchange para actualizar el precio
                                selectPrecios.dispatchEvent(new Event('change'));
                            }

                            // Configurar la unidad seleccionada y su idUnidadVenta
                            const selectUnidad = document.getElementById(`unidad${numeroInputs}`);
                            const idUnidadVentaField = document.getElementById(`idUnidadVenta${numeroInputs}`);

                            console.log('Select unidad encontrado:', selectUnidad);
                            console.log('Campo idUnidadVenta encontrado:', idUnidadVentaField);

                            if (selectUnidad && idUnidadVentaField) {
                                const selectedOption = selectUnidad.options[selectUnidad.selectedIndex];
                                if (selectedOption) {
                                    const idUnidadVenta = selectedOption.getAttribute('data-id');
                                    idUnidadVentaField.value = idUnidadVenta;
                                    console.log('✅ Unidad configurada - idUnidadVenta:', idUnidadVenta);
                                    console.log('Valor del campo idUnidadVenta:', idUnidadVentaField.value);
                                }
                            }

                            // Verificar campo idPrecioSeleccionado
                            const idPrecioSeleccionadoField = document.getElementById(`idPrecioSeleccionado${numeroInputs}`);
                            console.log('Campo idPrecioSeleccionado encontrado:', idPrecioSeleccionadoField);
                            console.log('Valor inicial idPrecioSeleccionado:', idPrecioSeleccionadoField?.value);

                            // Calcular totales automáticamente
                            const precioInput = document.getElementById(`precio${numeroInputs}`);
                            const cantidadInput = document.getElementById(`cantidad${numeroInputs}`);
                            const restaInventarioInput = document.getElementById(`restaInventario${numeroInputs}`);

                            console.log('Elementos para calcular totales:');
                            console.log('- precioInput:', precioInput);
                            console.log('- cantidadInput:', cantidadInput);
                            console.log('- restaInventarioInput:', restaInventarioInput);

                            // No calcular totales automáticamente - el usuario debe ingresar cantidad primero
                            // if (precioInput && cantidadInput && selectUnidad && restaInventarioInput) {
                            //     calcularTotales(precioInput, cantidadInput, producto.isv, selectUnidad, numeroInputs, restaInventarioInput);
                            // }

                            // Enfocar el campo cantidad para que el usuario pueda escribir inmediatamente
                            if (cantidadInput) {
                                cantidadInput.focus();
                                cantidadInput.select(); // Seleccionar todo el texto si hubiera alguno
                            }

                            console.log('✅ Producto configurado - esperando cantidad del usuario');
                        } catch (error) {
                            console.error('❌ Error configurando producto automáticamente:', error);
                        }
                    }, 100);

                    // Mostrar mensaje de éxito
                    Swal.fire({
                        icon: 'success',
                        title: '¡Producto Escaneado!',
                        html: `
                            <div style="text-align: left;">
                                <p><strong>Código:</strong> ${codigoBarra}</p>
                                <p><strong>Producto:</strong> ${producto.nombre}</p>
                                <p style="color: #28a745;">✓ Agregado al carrito</p>
                                <p style="color: #ff9800; font-weight: bold;">⚠️ Por favor, ingrese la cantidad deseada</p>
                            </div>
                        `,
                        timer: 3000,
                        showConfirmButton: false
                    });
                    infoProducto(producto.id);

                    return;
                })
                .catch(err => {
                    console.error('Error al buscar producto por código de barras:', err);
                    playSound('error');

                    // Manejar diferentes tipos de errores
                    let errorMessage = 'No se pudo encontrar el producto con ese código de barras.';
                    let errorTitle = 'Error al escanear';

                    if (err.response) {
                        if (err.response.status === 404) {
                            errorTitle = '¡Producto No Encontrado!';
                            errorMessage = err.response.data.message || 'El producto no existe en la base de datos';
                        } else if (err.response.data && err.response.data.message) {
                            errorMessage = err.response.data.message;
                        }
                    }

                    Swal.fire({
                        icon: 'error',
                        title: errorTitle,
                        html: `
                            <div style="text-align: left;">
                                <p><strong>Código escaneado:</strong> ${codigoBarra}</p>
                                <p><strong>Error:</strong> ${errorMessage}</p>
                                <hr>
                                <p style="color: #666; font-size: 0.9em;">
                                    Intente escanear el código nuevamente o verifique que el producto esté registrado en el sistema.
                                </p>
                            </div>
                        `,
                        confirmButtonColor: '#d33'
                    });
                });
            }

            // ========================================
            // SCANNER DE CÓDIGOS DE BARRAS SIMPLIFICADO
            // ========================================

            let isScanning = false;
            let videoStream = null;

            // Función para inicializar el scanner (sin validaciones)
            function initBarcodeScanner() {
                if (isScanning) {
                    console.log('⚠️ Scanner ya está activo');
                    return;
                }

                console.log('🎥 Iniciando scanner...');

                // Mostrar elementos
                document.getElementById('cameraContainer').style.display = 'block';
                document.getElementById('scannerStatus').style.display = 'block';
                document.getElementById('btnStartCamera').style.display = 'none';
                document.getElementById('btnStopCamera').style.display = 'inline-block';

                // Actualizar estado inicial
                if (typeof window.actualizarEstadoScanner === 'function') {
                    window.actualizarEstadoScanner('Escaneando...');
                }

                // Asegurar que el overlay esté presente
                let overlay = document.querySelector('.scanner-overlay');
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.className = 'scanner-overlay';
                    document.getElementById('cameraContainer').appendChild(overlay);
                }

                // Marcar como iniciando para evitar bucles
                isScanning = true;

                // Inicializar Quagga directamente con configuración mejorada
                console.log('🔧 Configurando Quagga...');
                Quagga.init({
                    inputStream: {
                        name: "Live",
                        type: "LiveStream",
                        target: document.querySelector('#cameraContainer'), // Cambiar target al contenedor
                        constraints: {
                            width: { min: 320, ideal: 640, max: 1280 },
                            height: { min: 240, ideal: 480, max: 720 },
                            facingMode: "environment"
                        },
                        area: { // Área de escaneo definida
                            top: "20%",
                            right: "20%",
                            left: "20%",
                            bottom: "20%"
                        }
                    },
                    decoder: {
                        readers: [
                            "code_128_reader",
                            "ean_reader",
                            "ean_8_reader",
                            "code_39_reader",
                            "upc_reader",
                            "upc_e_reader",
                            "i2of5_reader"
                        ],
                        debug: {
                            drawBoundingBox: true,
                            showFrequency: true,
                            drawScanline: true,
                            showPattern: true
                        }
                    },
                    locate: true,
                    locator: {
                        patchSize: "medium",
                        halfSample: false // Mejor calidad
                    },
                    frequency: 10 // Frecuencia de escaneo
                }, function(err) {
                    if (err) {
                        console.error('❌ Error Quagga:', err);
                        alert('Error al inicializar el scanner: ' + err.message);
                        stopBarcodeScanner();
                        return;
                    }

                    console.log('✅ Quagga inicializado, iniciando...');
                    Quagga.start();
                    console.log('✅ Scanner activo - buscando códigos...');

                    // Verificar que el video esté funcionando
                    setTimeout(() => {
                        const video = document.querySelector('#cameraContainer video');
                        const canvas = document.querySelector('#cameraContainer canvas');
                        console.log('🔍 Estado después de 2 segundos:', {
                            isScanning: isScanning,
                            videoElement: video ? 'presente' : 'ausente',
                            videoSrc: video ? video.srcObject : 'sin source',
                            videoPlaying: video ? !video.paused : false,
                            videoWidth: video ? video.videoWidth : 0,
                            videoHeight: video ? video.videoHeight : 0,
                            canvasElement: canvas ? 'presente' : 'ausente',
                            containerVisible: document.getElementById('cameraContainer').style.display
                        });

                        // Si el video no está reproduciéndose, intentar forzar play
                        if (video && video.paused) {
                            console.log('🔧 Intentando forzar reproducción del video...');
                            video.play().catch(e => console.log('❌ Error al reproducir:', e));
                        }
                    }, 2000);
                });

                // Configurar detección de códigos con mejor manejo
                Quagga.onDetected(function(result) {
                    console.log('🔍 Evento onDetected disparado');

                    if (!isScanning) {
                        console.log('❌ Scanner no activo, ignorando detección');
                        return;
                    }

                    const rawCode = result.codeResult.code;
                    console.log('📷 Código crudo detectado:', rawCode, 'Longitud:', rawCode ? rawCode.length : 0);

                    // Usar la función de normalización para eliminar ceros a la izquierda
                    const code = normalizarCodigoBarras(rawCode);
                    console.log('✅ Código procesado (scanner visual):', code);

                    // Validación más flexible
                    if (code && code.length >= 1) { // Reducir longitud mínima
                        console.log('✅ Código válido detectado:', code);

                        // Mostrar código dinámicamente debajo de la cámara
                        if (typeof window.mostrarCodigoDetectado === 'function') {
                            window.mostrarCodigoDetectado(code);
                        }

                        // Sonido de éxito
                        playSound('success');

                        // Agregar al carrito directamente con código procesado
                        agregarProductoCarritoBarra(code);

                        // Pausar temporalmente para evitar múltiples detecciones
                        isScanning = false;
                        Quagga.pause();

                        setTimeout(() => {
                            if (document.getElementById('cameraContainer').style.display !== 'none') {
                                isScanning = true;
                                Quagga.start();
                                console.log('🔄 Scanner reactivado');
                            }
                        }, 2000);

                    } else {
                        console.log('❌ Código no válido o muy corto:', code);
                    }
                });

                // Agregar eventos de debug adicionales
                Quagga.onProcessed(function(result) {
                    if (result && result.codeResult && result.codeResult.code) {
                        console.log('🔍 Procesando código:', result.codeResult.code);
                    }
                });

                        // Ocultar resultado después de 3 segundos
            }

            // Función para detener el scanner
            function stopBarcodeScanner() {
                console.log('🛑 Deteniendo scanner...');

                // Detener Quagga completamente
                try {
                    if (isScanning) {
                        Quagga.stop();
                        Quagga.offDetected();
                        console.log('✅ Quagga detenido');
                    }
                } catch (err) {
                    console.error('Error deteniendo Quagga:', err);
                }

                // Marcar como no activo INMEDIATAMENTE
                isScanning = false;

                // Limpiar el contenedor completamente
                const cameraContainer = document.getElementById('cameraContainer');
                if (cameraContainer) {
                    // Remover todos los elementos de video y canvas creados por Quagga
                    const videos = cameraContainer.querySelectorAll('video');
                    const canvases = cameraContainer.querySelectorAll('canvas');

                    videos.forEach(video => {
                        if (video.srcObject) {
                            video.srcObject.getTracks().forEach(track => track.stop());
                            video.srcObject = null;
                        }
                        video.remove();
                    });

                    canvases.forEach(canvas => canvas.remove());

                    // Ocultar contenedor
                    cameraContainer.style.display = 'none';
                }

                console.log('✅ Scanner completamente detenido');
                isScanning = false;

                // Detener todos los streams de video
                try {
                    const video = document.getElementById('scanner-video');
                    if (video && video.srcObject) {
                        const tracks = video.srcObject.getTracks();
                        tracks.forEach(track => {
                            track.stop();
                            console.log('🔴 Track detenido:', track.kind);
                        });
                        video.srcObject = null;
                    }

                    if (videoStream) {
                        videoStream.getTracks().forEach(track => track.stop());
                        videoStream = null;
                    }
                } catch (err) {
                    console.error('Error deteniendo video:', err);
                }

                // Ocultar elementos de UI - CON TIMEOUT PARA ASEGURAR ACTUALIZACIÓN
                setTimeout(() => {
                    try {
                        const cameraContainer = document.getElementById('cameraContainer');
                        const scannerStatus = document.getElementById('scannerStatus');
                        const btnStartCamera = document.getElementById('btnStartCamera');
                        const btnStopCamera = document.getElementById('btnStopCamera');

                        console.log('🔧 Elementos encontrados:', {
                            cameraContainer: !!cameraContainer,
                            scannerStatus: !!scannerStatus,
                            btnStartCamera: !!btnStartCamera,
                            btnStopCamera: !!btnStopCamera
                        });

                        if (cameraContainer) cameraContainer.style.display = 'none';
                        if (scannerStatus) scannerStatus.style.display = 'none';
                        if (btnStartCamera) {
                            btnStartCamera.style.display = 'inline-block';
                            console.log('✅ Botón START mostrado');
                        }
                        if (btnStopCamera) {
                            btnStopCamera.style.display = 'none';
                            console.log('✅ Botón STOP ocultado');
                        }

                        // Actualizar estado del texto
                        if (typeof window.actualizarEstadoScanner === 'function') {
                            window.actualizarEstadoScanner('Scanner detenido');
                        }
                    } catch (err) {
                        console.error('Error ocultando elementos:', err);
                    }
                }, 100);

                console.log('✅ Scanner completamente detenido');
            }

            // Función para reproducir sonidos
            function playSound(type) {
                try {
                    let frequency = type === 'success' ? 800 : 300;
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    oscillator.frequency.value = frequency;
                    oscillator.type = 'sine';
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.2);
                } catch (e) {
                    console.log('No se pudo reproducir el sonido');
                }
            }

            // Hacer las funciones globales para que estén disponibles
            window.initBarcodeScanner = initBarcodeScanner;
            window.stopBarcodeScanner = stopBarcodeScanner;

            // Inicialización cuando el DOM esté listo
            document.addEventListener('DOMContentLoaded', function() {
                console.log('🔧 Inicializando controles del scanner...');

                const btnStart = document.getElementById('btnStartCamera');
                const btnStop = document.getElementById('btnStopCamera');

                if (btnStart) {
                    // Limpiar eventos previos
                    btnStart.removeEventListener('click', initBarcodeScanner);
                    btnStart.addEventListener('click', function(e) {
                        e.preventDefault();
                        console.log('▶️ Botón activar presionado');
                        initBarcodeScanner();
                    });
                }

                if (btnStop) {
                    // Limpiar eventos previos
                    btnStop.removeEventListener('click', stopBarcodeScanner);
                    btnStop.addEventListener('click', function(e) {
                        e.preventDefault();
                        console.log('⏹️ Botón detener presionado');
                        stopBarcodeScanner();
                    });
                }

                console.log('✅ Controles del scanner listos');
            });

            // Función de prueba para debugging
            window.testBarcodeAPI = function(codigo) {
                codigo = codigo || '849607055569';
                console.log('🧪 Probando API con código:', codigo);
                agregarProductoCarritoBarra(codigo);
            };

            // Función de emergencia para detener todo
            window.emergencyStop = function() {
                console.log('🚨 PARADA DE EMERGENCIA');
                isScanning = false;

                try {
                    Quagga.stop();
                    Quagga.offDetected();
                } catch (err) {
                    console.log('Quagga ya detenido');
                }

                // Detener todos los streams de medios
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(stream => {
                        stream.getTracks().forEach(track => track.stop());
                    })
                    .catch(() => {});

                // Limpiar video elements
                const videos = document.querySelectorAll('video');
                videos.forEach(video => {
                    if (video.srcObject) {
                        video.srcObject.getTracks().forEach(track => track.stop());
                        video.srcObject = null;
                    }
                });

                // Resetear UI
                try {
                    const cameraContainer = document.getElementById('cameraContainer');
                    const scannerStatus = document.getElementById('scannerStatus');
                    const btnStartCamera = document.getElementById('btnStartCamera');
                    const btnStopCamera = document.getElementById('btnStopCamera');

                    if (cameraContainer) cameraContainer.style.display = 'none';
                    if (scannerStatus) scannerStatus.style.display = 'none';
                    if (btnStartCamera) btnStartCamera.style.display = 'inline-block';
                    if (btnStopCamera) btnStopCamera.style.display = 'none';

                    // Actualizar estado del texto
                    if (typeof window.actualizarEstadoScanner === 'function') {
                        window.actualizarEstadoScanner('Scanner detenido');
                    }
                } catch (err) {}

                console.log('✅ Parada de emergencia completada');
            };

            // Función de debug para probar el scanner
            window.testScanner = function() {
                console.log('🧪 Iniciando prueba del scanner...');
                console.log('📊 Estado actual:', {
                    isScanning: isScanning,
                    cameraContainer: document.getElementById('cameraContainer'),
                    quaggaExists: typeof Quagga !== 'undefined'
                });

                const container = document.getElementById('cameraContainer');
                if (container) {
                    const video = container.querySelector('video');
                    const canvas = container.querySelector('canvas');

                    console.log('🎥 Elementos de video:', {
                        video: video ? 'presente' : 'ausente',
                        videoSrc: video ? video.srcObject : 'sin source',
                        canvas: canvas ? 'presente' : 'ausente',
                        containerDisplay: container.style.display
                    });
                }

                if (typeof Quagga !== 'undefined') {
                    console.log('✅ QuaggaJS disponible');
                    // Simular detección
                    if (typeof window.mostrarCodigoDetectado === 'function') {
                        window.mostrarCodigoDetectado('123456789012');
                        console.log('✅ Función mostrarCodigoDetectado llamada');
                    }
                } else {
                    console.log('❌ QuaggaJS no disponible');
                }
            };

            // Función para verificar estado del video en tiempo real
            window.checkVideoStatus = function() {
                const container = document.getElementById('cameraContainer');
                if (!container) {
                    console.log('❌ Contenedor no encontrado');
                    return;
                }

                const video = container.querySelector('video');
                if (!video) {
                    console.log('❌ Video no encontrado');
                    return;
                }

                console.log('📹 Estado del video:', {
                    paused: video.paused,
                    muted: video.muted,
                    autoplay: video.autoplay,
                    srcObject: video.srcObject,
                    videoWidth: video.videoWidth,
                    videoHeight: video.videoHeight,
                    currentTime: video.currentTime,
                    readyState: video.readyState
                });
            };

            // Función para probar la normalización de códigos
            window.testNormalizacion = function() {
                console.log('🧪 === TEST DE NORMALIZACIÓN DE CÓDIGOS ===');
                const testCodes = ['000123456', '00012', '0001', '0', '000', '123456', ''];

                testCodes.forEach(code => {
                    const normalizado = normalizarCodigoBarras(code);
                    console.log(`📋 "${code}" → "${normalizado}"`);
                });

                console.log('🧪 === FIN TEST NORMALIZACIÓN ===');
            };

            // Función para verificar campos antes del guardado
            window.verificarCamposGuardado = function() {
                console.log('🔍 === VERIFICACIÓN DE CAMPOS PARA GUARDADO ===');

                arregloIdInputs.forEach(id => {
                    console.log(`📦 Producto ${id}:`);

                    const campos = [
                        'idProducto', 'nombre', 'bodega', 'precios', 'precio', 'cantidad',
                        'unidad', 'subTotal', 'isvProducto', 'total', 'idBodega',
                        'idSeccion', 'restaInventario', 'isv', 'idPrecioSeleccionado', 'idUnidadVenta'
                    ];

                    campos.forEach(campo => {
                        const elemento = document.getElementById(`${campo}${id}`);
                        if (elemento) {
                            console.log(`  ✅ ${campo}${id}: "${elemento.value}"`);
                        } else {
                            console.log(`  ❌ ${campo}${id}: NO ENCONTRADO`);
                        }
                    });

                    console.log('---');
                });

                console.log('🔍 === FIN VERIFICACIÓN ===');
            };

            console.log('📱 Scanner de códigos de barras cargado - usa testScanner() para probar');
