$(document).ready(function() {

var idProducto_edit = document.getElementById('id_producto_edit').value;
obtenerDatosProductoEditar(idProducto_edit);

var tablaLotes = document.getElementById('tbl_lotes_listar');
var codigoProducto = tablaLotes.dataset.productoCodigo || idProducto_edit;
var nombreProducto = tablaLotes.dataset.productoNombre || 'Producto';
var usuarioDescarga = tablaLotes.dataset.usuarioDescarga || 'Sistema';

$('#tbl_lotes_listar').DataTable({
    "language": {
        "url": "/js/plugins/dataTables/i18n/Spanish.json"
    },
    pageLength: 10,
    responsive: true,
    dom: '<"html5buttons"B>lTfgitp',
    buttons: [
        {
            extend: 'excelHtml5',
            title: '',
            filename: 'Distribuciones_Valencia_Disponibilidad_Producto_' + codigoProducto,
            text: '<i class="fa-solid fa-file-excel"></i> Exportar a Excel',
            className: 'btn btn-success btn-sm',
            exportOptions: {
                modifier: {
                    search: 'applied',
                    page: 'all'
                },
                format: {
                    body: function(data, row, column) {
                        var texto = $('<div>').html(data).text().trim();
                        if ([0, 1, 8, 9, 10, 11].indexOf(column) >= 0) {
                            var numero = parseFloat(texto.replace(/,/g, ''));
                            return isNaN(numero) ? 0 : numero;
                        }
                        return texto;
                    }
                }
            },
            customizeData: function(data) {
                var totales = [0, 0, 0];
                data.body.forEach(function(fila) {
                    totales[0] += parseFloat(fila[9]) || 0;
                    totales[1] += parseFloat(fila[10]) || 0;
                    totales[2] += parseFloat(fila[11]) || 0;
                });
                data.body.push([
                    'TOTALES', '', '', '', '', '', '', '', '',
                    totales[0], totales[1], totales[2]
                ]);
            },
            customize: function(xlsx) {
                personalizarExcelDisponibilidad(
                    xlsx,
                    'Disponibilidad de Producto - ' + codigoProducto + ' - ' + nombreProducto,
                    usuarioDescarga
                );
            }
        }
    ],
    drawCallback: function() {
        var api = $('#tbl_lotes_listar').DataTable();
        var sum = 0;
        api.column(10).data().each(function(val) {
            var text = String(val).replace(/<[^>]*>/g, '').trim();
            sum += parseInt(text) || 0;
        });
        let html = 'Cantidad Total en Bodegas: ' + sum.toLocaleString('es-HN');
        $('#total_lotes').html(html);
    }



});

$('#tbl_unidades_listar').DataTable({

    "language": {
        "url": "/js/plugins/dataTables/i18n/Spanish.json"
    },

    pageLength: 10,
    responsive: true,
    "ajax": "/detalle/producto/unidad/" + idProducto_edit,
    "columns": [{
            data: 'contador'
        },
        {
            data: 'nombre'
        },
        {
            data: 'unidad_venta'
        },
        // {
        //     data: 'eliminar'
        // },
        {
            data: 'editar'
        },
    ]


});

});

function personalizarExcelDisponibilidad(xlsx, tituloReporte, usuarioDescarga) {
var sheet = xlsx.xl.worksheets['sheet1.xml'];
var $sheet = $(sheet);
var styles = xlsx.xl['styles.xml'];
var $styles = $(styles);
var sheetData = $sheet.find('sheetData');
var fecha = new Date();
var fechaDescarga = fecha.toLocaleDateString('es-HN', {
    day: '2-digit', month: '2-digit', year: 'numeric'
}) + ' ' + fecha.toLocaleTimeString('es-HN', {
    hour: '2-digit', minute: '2-digit', second: '2-digit'
});

function escaparXml(texto) {
    return String(texto || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&apos;');
}

function columnaDeReferencia(referencia) {
    return String(referencia || '').replace(/[0-9]/g, '');
}

sheetData.find('row').each(function() {
    var $row = $(this);
    var nuevaFila = parseInt($row.attr('r') || '0', 10) + 4;
    $row.attr('r', String(nuevaFila));
    $row.find('c').each(function() {
        var $cell = $(this);
        var columna = columnaDeReferencia($cell.attr('r'));
        if (columna) $cell.attr('r', columna + nuevaFila);
    });
});

var encabezado = '' +
    '<row r="1" ht="30" customHeight="1"><c r="A1" t="inlineStr"><is><t>DISTRIBUCIONES VALENCIA   |   RTN: 08011986138652</t></is></c></row>' +
    '<row r="2" ht="20" customHeight="1"><c r="A2" t="inlineStr"><is><t>' + escaparXml(tituloReporte.toUpperCase()) + '</t></is></c></row>' +
    '<row r="3"><c r="A3" t="inlineStr"><is><t>' +
        escaparXml('Generado: ' + fechaDescarga + '  |  Descargado por: ' + usuarioDescarga) +
    '</t></is></c></row>' +
    '<row r="4"><c r="A4" t="inlineStr"><is><t></t></is></c></row>';
sheetData.prepend(encabezado);

var $dimension = $sheet.find('dimension');
if ($dimension.length) {
    var referencia = $dimension.attr('ref') || 'A1:L1';
    var partes = referencia.split(':');
    var columnaFinal = partes.length === 2 ? columnaDeReferencia(partes[1]) : 'L';
    var filaFinal = partes.length === 2
        ? parseInt(String(partes[1]).replace(/[^0-9]/g, '') || '1', 10) + 4
        : 5;
    $dimension.attr('ref', 'A1:' + (columnaFinal || 'L') + filaFinal);
}

var merges = $sheet.find('mergeCells');
if (!merges.length) {
    $sheet.find('worksheet').append('<mergeCells count="0"></mergeCells>');
    merges = $sheet.find('mergeCells');
}
merges.append('<mergeCell ref="A1:L1"/><mergeCell ref="A2:L2"/><mergeCell ref="A3:L3"/>');
merges.attr('count', parseInt(merges.attr('count') || '0', 10) + 3);

var $fonts = $styles.find('fonts');
var fontEmpresa = parseInt($fonts.attr('count') || '0', 10);
$fonts.append('<font><sz val="12"/><name val="Calibri"/><b/><color rgb="FF1F3864"/></font>');
var fontTitulo = fontEmpresa + 1;
$fonts.append('<font><sz val="12"/><name val="Calibri"/><b/><color rgb="FFE07000"/></font>');
var fontMeta = fontEmpresa + 2;
$fonts.append('<font><sz val="9"/><name val="Calibri"/><i/></font>');
var fontCabecera = fontEmpresa + 3;
$fonts.append('<font><sz val="8"/><name val="Calibri"/><b/><color rgb="FFFFFFFF"/></font>');
var fontTotal = fontEmpresa + 4;
$fonts.append('<font><sz val="10"/><name val="Calibri"/><b/><color rgb="FF7D3F00"/></font>');
$fonts.attr('count', fontEmpresa + 5);

var $fills = $styles.find('fills');
var fillNaranja = parseInt($fills.attr('count') || '0', 10);
$fills.append('<fill><patternFill patternType="solid"><fgColor rgb="FFE07000"/><bgColor indexed="64"/></patternFill></fill>');
var fillTotal = fillNaranja + 1;
$fills.append('<fill><patternFill patternType="solid"><fgColor rgb="FFFFF3E0"/><bgColor indexed="64"/></patternFill></fill>');
$fills.attr('count', fillNaranja + 2);

var $borders = $styles.find('borders');
var borderDatos = parseInt($borders.attr('count') || '0', 10);
$borders.append('<border><left style="thin"><color rgb="FFE8D5BF"/></left><right style="thin"><color rgb="FFE8D5BF"/></right><top style="thin"><color rgb="FFE8D5BF"/></top><bottom style="thin"><color rgb="FFE8D5BF"/></bottom><diagonal/></border>');
var borderTotal = borderDatos + 1;
$borders.append('<border><left style="thin"><color rgb="FFE8D5BF"/></left><right style="thin"><color rgb="FFE8D5BF"/></right><top style="medium"><color rgb="FFE07000"/></top><bottom style="thin"><color rgb="FFE8D5BF"/></bottom><diagonal/></border>');
$borders.attr('count', borderDatos + 2);

var $cellXfs = $styles.find('cellXfs');
var siguienteEstilo = parseInt($cellXfs.attr('count') || '0', 10);

function agregarEstilo(fontId, fillId, borderId, numFmtId, alineacion) {
    var estilo = siguienteEstilo++;
    $cellXfs.append(
        '<xf numFmtId="' + numFmtId + '" fontId="' + fontId + '" fillId="' + fillId +
        '" borderId="' + borderId + '" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1" applyNumberFormat="1">' +
        '<alignment ' + alineacion + '/></xf>'
    );
    return estilo;
}

var estiloEmpresa = agregarEstilo(fontEmpresa, 0, 0, 0, 'horizontal="center" vertical="center"');
var estiloTitulo = agregarEstilo(fontTitulo, 0, 0, 0, 'horizontal="center" vertical="center"');
var estiloMeta = agregarEstilo(fontMeta, 0, 0, 0, 'horizontal="center" vertical="center"');
var estiloCabecera = agregarEstilo(fontCabecera, fillNaranja, borderDatos, 0, 'horizontal="center" vertical="center" wrapText="1"');
var estiloDatoCentro = agregarEstilo(0, 0, borderDatos, 0, 'horizontal="center" vertical="center"');
var estiloDatoIzquierda = agregarEstilo(0, 0, borderDatos, 0, 'horizontal="left" vertical="center"');
var estiloCantidad = agregarEstilo(0, 0, borderDatos, 3, 'horizontal="right" vertical="center"');
var estiloTotal = agregarEstilo(fontTotal, fillTotal, borderTotal, 0, 'horizontal="center" vertical="center"');
var estiloTotalCantidad = agregarEstilo(fontTotal, fillTotal, borderTotal, 3, 'horizontal="right" vertical="center"');
$cellXfs.attr('count', siguienteEstilo);

function completarCeldasFila($row, numeroFila) {
    var columnas = 'ABCDEFGHIJKL'.split('');
    var celdas = {};
    $row.find('c').each(function() {
        celdas[columnaDeReferencia($(this).attr('r'))] = this;
    });
    $row.empty();
    columnas.forEach(function(columna) {
        if (celdas[columna]) {
            $row.append(celdas[columna]);
        } else {
            $row.append('<c r="' + columna + numeroFila + '" t="inlineStr"><is><t></t></is></c>');
        }
    });
}

$sheet.find('c[r="A1"]').attr('s', estiloEmpresa);
$sheet.find('c[r="A2"]').attr('s', estiloTitulo);
$sheet.find('c[r="A3"]').attr('s', estiloMeta);
$sheet.find('row[r="5"]').attr({ ht: '28', customHeight: '1' }).find('c').attr('s', estiloCabecera);

sheetData.find('row').each(function() {
    var $row = $(this);
    var numeroFila = parseInt($row.attr('r') || '0', 10);
    if (numeroFila < 6) return;

    var etiqueta = $row.find('c[r="A' + numeroFila + '"] t').text();
    completarCeldasFila($row, numeroFila);
    if (etiqueta === 'TOTALES') {
        $row.attr({ ht: '18', customHeight: '1' });
        $row.find('c').attr('s', estiloTotal);
        ['J', 'K', 'L'].forEach(function(columna) {
            $row.find('c[r="' + columna + numeroFila + '"]').attr('s', estiloTotalCantidad);
        });
        return;
    }

    $row.attr({ ht: '18', customHeight: '1' });
    $row.find('c').attr('s', estiloDatoCentro);
    ['C', 'D', 'E', 'F', 'G', 'H'].forEach(function(columna) {
        $row.find('c[r="' + columna + numeroFila + '"]').attr('s', estiloDatoIzquierda);
    });
    ['J', 'K', 'L'].forEach(function(columna) {
        $row.find('c[r="' + columna + numeroFila + '"]').attr('s', estiloCantidad);
    });
});

var anchos = [6, 14, 32, 18, 18, 24, 26, 14, 10, 12, 16, 16];
$sheet.find('cols col').each(function(indice) {
    if (anchos[indice]) $(this).attr({ width: anchos[indice], customWidth: '1' });
});

$sheet.find('worksheet').prepend(
    '<sheetViews><sheetView workbookViewId="0"><pane ySplit="5" topLeftCell="A6" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
);
merges.before('<autoFilter ref="A5:L5"/>');
}


function obtenerDatosProductoEditar(id) {
var idProducto = document.getElementById('id_producto_edit').value;
axios.get("/producto/datos/" + idProducto)
    .then(response => {
        let datos = response.data;


        document.getElementById("nombre_producto_edit").value = datos.datosProducto.nombre;
        document.getElementById("descripcion_producto_edit").value = datos.datosProducto.descripcion;
        // isv_producto_edit: existe solo para admin (select) — se asigna con .value
        var isvEl = document.getElementById("isv_producto_edit");
        if (isvEl) isvEl.value = datos.datosProducto.isv;
        document.getElementById("cod_barra_producto_edit").value = datos.datosProducto.codigo_barra;
        document.getElementById("cod_estatal_producto_edit").value = datos.datosProducto.codigo_estatal;
        var precioBaseEl = document.getElementById("precioBase_edit");
        var costoPromedioEl = document.getElementById("costo_promedio_editar");
        var ultimoCostoEl = document.getElementById("ultimo_costo_compra_editar");
        if (precioBaseEl) precioBaseEl.value = datos.datosProducto.precio_base;
        if (costoPromedioEl) costoPromedioEl.value = datos.datosProducto.costo_promedio;
        document.getElementById("unidades_editar").value = datos.datosProducto.unidadad_compra;
        if (ultimoCostoEl) ultimoCostoEl.value = datos.datosProducto.ultimo_costo_compra;

        ['precio1', 'precio2', 'precio3', 'precio4'].forEach(function(id, index) {
            var precioEl = document.getElementById(id);
            if (precioEl) precioEl.value = datos.datosProducto['precio' + (index + 1)];
        });



        if (datos.preciosProducto.length != 0) {
            var precio2EditEl = document.getElementById("precio2_edit");
            var precio3EditEl = document.getElementById("precio3_edit");
            if (precio2EditEl && datos.preciosProducto[1]) precio2EditEl.value = datos.preciosProducto[1].precio;
            if (precio3EditEl && datos.preciosProducto[2]) precio3EditEl.value = datos.preciosProducto[2].precio;
        }





        let arrayMarcas = datos.marcas;
        let htmlMarca = "<option selected disabled>---Seleccione una marca de producto---</option>  ";

        arrayMarcas.forEach(marca => {
            if (marca.id == datos.datosProducto.marca_id) {
                htmlMarca += `<option selected value="${marca.id}">${marca.nombre}</option>`;
            } else {
                htmlMarca += `<option  value="${marca.id}">${marca.nombre}</option>`;
            }

        });

        let arrayCategorias = datos.categorias;
        let htmlCategorias = "<option selected disabled>---Seleccione una categoria---</option>"

        arrayCategorias.forEach(categoria => {
            if (categoria.id == datos.categoria.id) {
                htmlCategorias +=
                    `<option selected value="${categoria.id}">${categoria.descripcion}</option>`;
            } else {
                htmlCategorias +=
                    `<option  value="${categoria.id}">${categoria.descripcion}</option>`;
            }

        });


        let arrayUnidades = datos.unidades;
        let htmlUnidades = "<option selected disabled>---Seleccione una unidad---</option>"

        arrayUnidades.forEach(unidad => {
            if (unidad.id == datos.datosProducto.unidad_medida_compra_id) {
                htmlUnidades += `<option selected value="${unidad.id}">${unidad.nombre}</option>`;
            } else {
                htmlUnidades += `<option  value="${unidad.id}">${unidad.nombre}</option>`;
            }

        });



        let arraySubcategorias = datos.subCategorias;

        let htmlSubCategorias = "<option selected disabled>---Seleccione una sub categoria---</option>"

        arraySubcategorias.forEach(unidad => {
            if (unidad.id == datos.subCategoria.id) {
                htmlSubCategorias +=
                    `<option selected value="${unidad.id}">${unidad.descripcion}</option>`;
            } else {
                htmlSubCategorias += `<option  value="${unidad.id}">${unidad.descripcion}</option>`;
            }

        });









        document.getElementById('marca_producto_editar').innerHTML = htmlMarca;
        document.getElementById('categoria_producto_edit').innerHTML = htmlCategorias;
        document.getElementById('unidad_producto_editar').innerHTML = htmlUnidades;
        document.getElementById('sub_categoria_producto_edit').innerHTML = htmlSubCategorias;

        document.getElementById('tiempo_recuperacion_meses_edit').value = datos.datosProducto.tiempo_recuperacion_meses || '';
        document.getElementById('origen_edit').value = datos.datosProducto.origen || '';




        $('#exampleModal').modal('show');

    });


}

$(document).on('submit', '#editarProductoForm', function(event) {

event.preventDefault();
editarProducto();

});

function validacionPrecio(){


precioBase = document.getElementById('precioBase_edit').value;

document.getElementById('precio1').setAttribute("min",precioBase);
document.getElementById('precio2').setAttribute("min",precioBase);
document.getElementById('precio3').setAttribute("min",precioBase);
document.getElementById('precio4').setAttribute("min",precioBase);


precio1 = Number(precioBase) + (precioBase*0.03);
precio2 = Number(precioBase) + (precioBase*0.06);
precio3 = Number(precioBase) + (precioBase*0.10);
precio4 = Number(precioBase) + (precioBase*0.3);

document.getElementById('precio1').value = precio1.toFixed(2);
document.getElementById('precio2').value = precio2.toFixed(2);
document.getElementById('precio3').value = precio3.toFixed(2);
document.getElementById('precio4').value = precio4.toFixed(2);





/*if(precio1<precioBase || precio2<precioBase  || precio3<precioBase  || precio4<precioBase ){
    Swal.fire({
        icon: 'Info',
        title: 'Cuidado!',
        text: "PAsegurese de que los precios A, B, C y D no sean menores que el precio base del producto."
    })

}*/




}
function editarProducto() {
$('#modalSpinnerLoading').modal('show');

var data = new FormData($('#editarProductoForm').get(0));

['precio1', 'precio2', 'precio3', 'precio4'].forEach(function(id) {
    var precioEl = document.getElementById(id);
    if (precioEl) data.append(id, precioEl.value);
});
axios.post("/producto/editar", data)
    .then(response => {
        $('#modalSpinnerLoading').modal('hide');

        document.getElementById("editarProductoForm").reset();
        $('#modal_producto_editar').modal('hide');

        Swal.fire({
            icon: 'success',
            title: 'Exito!',
            text: "Producto Editado con exito."
        })

        location.reload();

    })
    .catch(err => {
        $('#modalSpinnerLoading').modal('hide');
        $('#modal_producto_editar').modal('hide');

        console.error(err);
    let data = err.response && err.response.data ? err.response.data : {};
        if (data.icon) {
            Swal.fire({
                icon: data.icon,
                title: data.title,
                text: data.text,
            })
        } else {
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: "Ha ocurrido un error.",
            })
        }

    })

}

function eliminar(urlImagen) {
//console.log("Esto es una URL --->     "+urlImagen)
axios.post("/producto/eliminar", {
        "urlImagen": urlImagen
    })
    .then(response => {

        Swal.fire({
            icon: 'success',
            title: 'Exito!',
            text: "Imagen eliminada con exito."
        })
        location.reload();

    })
    .catch(err => {
        console.error(err);

    });

}

function modalEditarUnidades(idVentas, unidadesVentas, idUnidadVentas) {
let id = idVentas;
let unidadesVenta = unidadesVentas;
let idUnidad = idUnidadVentas
$('#modal_editar_unidades').modal('show');

axios.get('/detalle/unidades/venta')
    .then(response => {

        let unidades = response.data.unidades;

        let htmlSelect = "<option selected disabled>---Seleccione una unidad---</option>";

        unidades.forEach(unidad => {
            if (unidad.id == idUnidad) {
                htmlSelect += `<option selected value="${unidad.id}">${unidad.nombre}</option>`;
            } else {
                htmlSelect += `<option  value="${unidad.id}">${unidad.nombre}</option>`;
            }
        });

        document.getElementById("unidad_venta_editar").innerHTML = htmlSelect;
        document.getElementById("unidades_venta_editar").value = unidadesVenta;
        document.getElementById('idUniadVenta').value = id;



    })
    .catch(err => {
        console.log(err);
    })

}

$(document).on('submit', '#form_editar_unidades', function(event) {

event.preventDefault();
editarUnidadesVenta();

});

function editarUnidadesVenta() {
var data = new FormData($('#form_editar_unidades').get(0));

axios.post("/detalle/unidades/editar", data)
    .then(response => {
        $("#modal_editar_unidades").modal("hide");
        Swal.fire({
            icon: 'success',
            title: 'Exito!',
            text: "Producto Editado con exito."
        })

        location.reload();
    })
    .catch(err => {
        console.log(err);
        $("#modal_editar_unidades").modal("hide");
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: "Ha ocurrido un error."
        })

    })
}


function actualizarCostos(idProducto) {

axios.post('/producto/actualizar/costos', {
        idProducto: idProducto
    })
    .then(response => {
        let data = response.data;

        if (data.ultimoCosto != 0 && data.costoPromedio != 0) {
            document.getElementById('ultimo_costo_compra_editar').value = data.ultimoCosto;
            document.getElementById('costo_promedio_editar').value = data.costoPromedio;
        }




    }).catch(err => {
        console.error(err);

    })

}

function listarSubCategorias() {

var categoria_produ = document.getElementById('categoria_producto_edit').value;
axios.get("/producto/sub_categoria/listar/" + categoria_produ)
    .then(response => {
        let data = response.data.sub_categorias;

        let htmlSelect = '<option disabled selected>--Seleccione una Subcategoria--</option>'

        data.forEach(element => {
            htmlSelect += `<option value="${element.id}">${element.descripcion}</option>`
        });

        document.getElementById('sub_categoria_producto_edit').innerHTML = htmlSelect;
    })
    .catch(err => {
        console.log(err.response.data)
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Ha ocurrido un error',
        })
    })
}

///////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////
