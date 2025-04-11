
var numeroInputs = 0;
var arregloIdInputs = [];
var idProductoArray = [];
//var retencionEstado = false;  true  aplica retencion, false no aplica retencion;

window.onload = obtenerTipoPago;


function obtenerTipoPago() {

    axios.get('/producto/tipo/pagos')
        .then(response => {

            let tipoDePago = response.data.tipos;

            let htmlPagos = '  <option value="" selected disabled >--Seleccione una opcion--</option>';

            tipoDePago.forEach(element => {

                htmlPagos += `
                <option value="${element.id}" >${element.descripcion}</option>
                `
            });

            document.getElementById('tipoPagoCompra').innerHTML = htmlPagos;

        })
        .catch(err => {
            //console.log(err);
            Swal.fire({
                icon: 'error',
                title: 'Error...',
                text: "Ha ocurrido un error al obtener los tipos de pago"
            })
        })

}

$('#seleccionarProveedor').select2({
    ajax: {
        url: '/producto/lista/proveedores',
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



$('#seleccionarProdcuto').select2({
    ajax: {
        url: '/producto/listar/producto',
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

function obtenerIdProducto() {
    let id = document.getElementById('seleccionarProdcuto').value;

    this.obtenerImagenes(id)
}

function agregarProductoCarrito() {


    let id = document.getElementById('seleccionarProdcuto').value;

    let idBuscarProducto = idProductoArray.find(element => element == id);

    if(idBuscarProducto){
        Swal.fire({
                icon: 'warning',
                title: 'Advertencia!',
                text: "Este producto ya ha sido agregado al carrito de compra. No se permite duplicidad de producto."
            })

            return;
    }

    axios.post('/prodcuto/compra/datos', {
            id: id
        })
        .then(response => {
            let producto = response.data.producto;
            //console.log(response.data.producto);
            numeroInputs += 1;

            html = `
            <div id='${numeroInputs}' class="row no-gutters">
                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                    <div class="d-flex">

                                        <button class="btn btn-danger" type="button" style="display: inline" onclick="eliminarInput(${numeroInputs},${id})"><i
                                                class="fa-regular fa-rectangle-xmark"></i>
                                        </button>

                                        <input id="idProducto${numeroInputs}" name="idProducto${numeroInputs}" type="hidden" value="${producto.id}">

                                        <div style="width:100%">
                                            <label for="nombre${numeroInputs}" class="sr-only">Nombre del producto</label>
                                            <input type="text" placeholder="Nombre del producto" id="nombre${numeroInputs}"
                                                name="nombre${numeroInputs}" class="form-control"
                                                data-parsley-required "
                                                autocomplete="off"
                                                readonly
                                                value='${producto.nombre}'
                                                >
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                    <label for="precio${numeroInputs}" class="sr-only">Precio</label>
                                    <input type="number" placeholder="Precio Unidad" id="precio${numeroInputs}"
                                        name="precio${numeroInputs}" class="form-control" min="0" data-parsley-required step="any"
                                        autocomplete="off" onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},${numeroInputs}, ${producto.unidadad_compra})">
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                    <label for="cantidad${numeroInputs}" class="sr-only">cantidad</label>
                                    <input type="number" placeholder="Cantidad" id="cantidad${numeroInputs}"
                                        name="cantidad${numeroInputs}" class="form-control" min="0" data-parsley-required
                                        autocomplete="off" onchange="calcularTotales(precio${numeroInputs},cantidad${numeroInputs},${producto.isv},${numeroInputs}, ${producto.unidadad_compra})">
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                    <label for="unidad${numeroInputs}" class="sr-only">Unidad</label>
                                    <input value="${producto.unidad}" type="text" placeholder="Unidad" id="unidad${numeroInputs}"
                                        name="unidad${numeroInputs}" class="form-control" data-parsley-required
                                        autocomplete="off" readonly>
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                    <label for="vencimiento${numeroInputs}" class="sr-only">cantidad</label>
                                    <input type="date" placeholder="Fecha de vencimiento" id="vencimiento${numeroInputs}"
                                        name="vencimiento${numeroInputs}" class="form-control" min="0"
                                        autocomplete="off" >
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                    <label for="subTotal${numeroInputs}" class="sr-only">Sub Total</label>
                                    <input type="number" placeholder="Sub total producto" id="subTotal${numeroInputs}"
                                        name="subTotal${numeroInputs}" class="form-control" min="0" step="any"
                                        autocomplete="off"
                                        readonly >
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-1 col-lg-1 col-xl-1">
                                    <label for="isvProducto${numeroInputs}" class="sr-only">ISV</label>
                                    <input type="number" placeholder="ISV" id="isvProducto${numeroInputs}"
                                        name="isvProducto${numeroInputs}" class="form-control" min="0" step="any"
                                        autocomplete="off"
                                        readonly >
                                </div>

                                <div class="form-group col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                    <label for="total${numeroInputs}" class="sr-only">Total</label>
                                    <input type="number" placeholder="Total del producto" id="total${numeroInputs}"
                                        name="total${numeroInputs}" class="form-control" min="1"  step="any"
                                        autocomplete="off"
                                        readonly >
                                </div>

                                <input id="unidadesCompra${numeroInputs}" name="unidadesCompra${numeroInputs}" type="hidden" value="${producto.unidadad_compra}">
                                <input id="medidaCompraId${numeroInputs}" name="medidaCompraId${numeroInputs}" type="hidden" value="${producto.unidad_medida_compra_id}">


            </div>
            `;

            arregloIdInputs.splice(numeroInputs, 0, numeroInputs);
            idProductoArray .splice(producto.id,0,producto.id);

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

function eliminarInput(id,idProducto) {
    const element = document.getElementById(id);
    element.remove();

    console.log(idProductoArray);
    var myIndex = arregloIdInputs.indexOf(id);
    if (myIndex !== -1) {
        arregloIdInputs.splice(myIndex, 1);
        this.totalesGenerales();
    }

    var myIndex2 = idProductoArray.indexOf(idProducto);
    if (myIndex2 !== -1) {
        idProductoArray.splice(myIndex2, 1);

    }

    console.log(idProductoArray);


}

function calcularTotales(idPrecio, idCantidad, isvProducto, id, unidadad_compra) {

    let valorInputPrecio = Number(idPrecio.value).toFixed(2);
    let valirInputCantidad = idCantidad.value;


    if (valorInputPrecio && valirInputCantidad) {

        let subTotal = valorInputPrecio * (valirInputCantidad*unidadad_compra);
        let isv = subTotal * (isvProducto / 100);
        let total = subTotal + subTotal * (isvProducto / 100);

        document.getElementById('subTotal' + id).value = subTotal.toFixed(2);
        document.getElementById('total' + id).value = total.toFixed(2);
        document.getElementById('isvProducto' + id).value = isv.toFixed(2);

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
    let subTotalGeneralValor = new Number(0);


    for (let i = 0; i < arregloIdInputs.length; i++) {
        subTotalGeneralValor += new Number(document.getElementById('subTotal' + arregloIdInputs[i]).value);
        totalISV += new Number(document.getElementById('isvProducto' + arregloIdInputs[i]).value);
        totalGeneralValor += new Number(document.getElementById('total' + arregloIdInputs[i]).value);

    }

    document.getElementById('subTotalGeneral').value = subTotalGeneralValor.toFixed(2);
    document.getElementById('isvGeneral').value = totalISV.toFixed(2);
    document.getElementById('totalGeneral').value = totalGeneralValor.toFixed(2);





    return 0;


}

function validarFechaPago() {

    let tipoPago;

    tipoPago = document.getElementById('tipoPagoCompra').value;

    if (tipoPago == 1) {

        document.getElementById('fecha_vencimiento').value = "Empty";
        document.getElementById('fecha_vencimiento').readOnly = false;


    } else {
        document.getElementById('fecha_vencimiento').value = "{{ date('Y-m-d') }}";

    }

    return 0;


}

// function retencionProveedor() {
//     let idProveedor = document.getElementById('seleccionarProveedor').value;
//     axios.post('/producto/compra/retencion', {
//             idProveedor: idProveedor
//         })
//         .then(response => {
//             let data = response.data;




//             Swal.fire({
//                 title: data.title,
//                 text: data.text,
//                 showDenyButton: true,
//                 showCancelButton: true,
//                 confirmButtonText: 'Aplicar',
//                 denyButtonText: `No aplicar`,
//                 cancelButtonText: 'Cancelar',
//             }).then((result) => {
//                 /* Read more about isConfirmed, isDenied below */
//                 if (result.isConfirmed) {
//                     Swal.fire('La retencion sera aplicada a esta compra!', '', 'success')
//                     retencionEstado = true;
//                     this.totalesGenerales();
//                 } else if (result.isDenied) {
//                     Swal.fire('¡No se aplicara retencion a esta compra!', '', 'info')
//                     retencionEstado = false;
//                     this.totalesGenerales();
//                 }
//             })



//         })
//         .catch(err => {

//             Swal.fire({
//                 icon: 'error',
//                 title: 'Error!',
//                 text: "Ha ocurrido un error al seleccionar el proveedor."
//             })

//             console.error('ha ocurrido un erro', err)

//         })
// }

$(document).on('submit', '#crear_compra',
    function(event) {
        event.preventDefault();
        guardarCompra();
    });

function guardarCompra() {
    // if (!retencionEstado) {
    //     document.getElementById('retencion').value = 0;
    // }

    var data = new FormData($('#crear_compra').get(0));

    // let longitudArreglo = arregloIdInputs.length;
    // for (var i = 0; i < longitudArreglo; i++) {
    //     data.append("arregloIdInputs[]", arregloIdInputs[i]);
    // }

    data.append("numeroInputs", numeroInputs);

    let seleccionarProveedorId = document.getElementById('seleccionarProveedor').value;
    data.append("seleccionarProveedorId", seleccionarProveedorId);

    let text = arregloIdInputs.toString();
    data.append("arregloIdInputs", text);

    const formDataObj = {};

        data.forEach((value, key) => (formDataObj[key] = value));


        const options = {
            headers: {"content-type": "application/json"}
        }

    axios.post('/producto/compra/guardar', formDataObj,options)
        .then(response => {


            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Compra realizada con exito."
            })

            //console.log(arregloIdInputs);



            // for (let i = 0; i < arregloIdInputs.length; i++) {
            //     const element = document.getElementById(arregloIdInputs[i]);
            //      element.remove();

            //     console.log(i,arregloIdInputs[i])
            // }

            document.getElementById('bloqueImagenes').innerHTML = '';
            document.getElementById('divProductos').innerHTML='';

            document.getElementById("crear_compra").reset();
            $('#crear_compra').parsley().reset();



            var element2 = document.getElementById('botonAdd');
                element2.classList.add("d-none");


            arregloIdInputs = [];
            numeroInputs = 0;
            // retencionEstado=false;

        })
        .catch(err => {

            console.log(err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "Ha ocurrido un error!."
            })
        })
}
