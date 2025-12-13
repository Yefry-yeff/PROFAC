
cargarBodegas();

function cargarBodegas(){
    $('#bodega').select2({
        ajax: {
            url: '/cardex/listar/bodega',
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


}

function obtenerIdBodega() {

    var idBodega = document.getElementById('bodega').value;
    obtenerProductos();
}

function obtenerProductos(){
    var idBodega = document.getElementById('bodega').value;
    $('#producto').select2({
        ajax: {
            url: '/cardex/listar/productos',
            data: function(params) {
                var query = {
                    search: params.term,
                    idBodega:idBodega,
                    type: 'public',
                    page: params.page || 1
                }

                // Query parameters will be ?search=[term]&type=public
                return query;
            }
        }
    });
}

function llamar(){

    var idBodega = document.getElementById('bodega');
    var idProducto = document.getElementById('producto');
    axios.get("/listado/cardex/nuevo/"+idProducto.value+"/"+idBodega.value)
        .then( response => {
            let data = response.data;
            console.log(data);


        })
        .catch( err => {
            console.log('nada');
        })
}

function cargaCardex(){

    $("#tbl_cardex").dataTable().fnDestroy();

    var idBodega = document.getElementById('bodega');
    var idProducto = document.getElementById('producto');
    //console.log(idBodega.options[idBodega.selectedIndex].text, idProducto.options[idProducto.selectedIndex].text);
    $('#tbl_cardex').DataTable({
        "paging": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        pageLength: 10,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp',
        buttons: [
            {
                extend: 'excel',
                title: 'Cardex'
            }
        ],
        "ajax": "/listado/cardex/nuevo/"+idProducto.value+"/"+idBodega.value,
        "columns": [
            {
                data: 'fecha_creacion'
            },
            {
                data: 'producto'
            },
            {
                data: 'id_producto'
            },
            {
                data: 'doc_factura'
            },
            {
                data: 'doc_ajuste'
            },

            {
                data: 'detalleCompra'
            },
            {
                data: 'comprobante_entrega'
            },/*
            {
                data: 'vale_tipo_1
            },'*/
            {
                data: 'vale_tipo_2'
            },
            {
                data: 'nota_credito'
            },
            {
                data: 'descripcion'
            },
            {
                data: 'origen'
            },
            {
                data: 'destino'
            },
            {
                data: 'cantidad'
            },
            {
                data: 'usuario'
            },
        ],initComplete: function () {
            var r = $('#tbl_cardex tfoot tr');
            r.find('th').each(function(){
              $(this).css('padding', 8);
            });
            $('#tbl_cardex thead').append(r);
            $('#search_0').css('text-align', 'center');
            this.api()
                .columns()
                .every(function () {
                    let column = this;
                    let title = column.footer().textContent;

                    // Create input element
                    let input = document.createElement('input');
                    input.placeholder = title;
                    column.footer().replaceChildren(input);

                    // Event listener for user input
                    input.addEventListener('keyup', () => {
                        if (column.search() !== this.value) {
                            column.search(input.value).draw();
                        }
                    });
                });
        }


    });
}
