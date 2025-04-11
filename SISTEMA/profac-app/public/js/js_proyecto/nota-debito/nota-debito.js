
$(document).on('submit', '#montoAddForm', function(event) {
    event.preventDefault();
    guardarMonto();
});

$(document).on('submit', '#ndAddForm', function(event) {
    event.preventDefault();
    guardarNotaDebito();
});

function guardarMonto() {
    $('#modalSpinnerLoading').modal('show');

    var data = new FormData($('#montoAddForm').get(0));

    axios.post("/debito/monto/guardar", data)
        .then(response => {


            $('#montoAddForm').parsley().reset();

            document.getElementById("montoAddForm").reset();
            $('#modal_monto_crear').modal('hide');
            $('#modalSpinnerLoading').modal('hide');
            $('#tbl_listar_monto_debito').DataTable().ajax.reload();
            $('#tbl_listar_facturas').DataTable().ajax.reload();

            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Monto Creado con exito."
            })

        })
        .catch(err => {
            let data = err.response.data;
            $('#modal_monto_crear').modal('hide');
            $('#modalSpinnerLoading').modal('hide');
            Swal.fire({
                icon: data.icon,
                title: data.title,
                text: data.text
            })
            console.error(err);

        })

}


function llenadoModalDebito(factura_id, montoND, idMonto){

    $('#factura_id').val(factura_id);
    $('#montoNotaDebito_id').val(idMonto);
    $('#monto_').val(montoND);

    $('#modal_nota_debito_crear').modal('show');
}

function guardarNotaDebito() {
    $('#modalSpinnerLoading').modal('show');

    var data = new FormData($('#ndAddForm').get(0));

    axios.post("/debito/notad/guardar", data)
        .then(response => {


            $('#ndAddForm').parsley().reset();

            document.getElementById("ndAddForm").reset();
            $('#modal_nota_debito_crear').modal('hide');
            $('#modalSpinnerLoading').modal('hide');
            $('#tbl_listar_notas_debito').DataTable().ajax.reload();
            $('#tbl_listar_facturas').DataTable().ajax.reload();



            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Noda de débito creada realizada con éxito."
            })

        })
        .catch(err => {
            let data = err.response.data;
            $('#modal_nota_debito_crear').modal('hide');
            $('#modalSpinnerLoading').modal('hide');
            Swal.fire({
                icon: data.icon,
                title: data.title,
                text: data.text
            })
            console.error(err);

        })

}

$(document).ready(function() {




    $('#tbl_listar_facturas').DataTable({
        "order": [3, 'desc'],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [3, 'desc'],
        pageLength: 10,
        responsive: true,


        "ajax": "/debito/lista/facturas",
        "columns": [
            {
                data: 'numero_factura'
            },
            {
                data: 'fecha_emision'
            },
            {
                data: 'nombre'
            },
            {
                data: 'descripcion'
            },
            {
                data: 'fecha_vencimiento'
            },
            {
                data: 'sub_total'
            },
            {
                data: 'isv'
            },
            {
                data: 'total'
            },
            {
                data: 'estado_cobro'
            },
            {
                data: 'creado_por'
            },
            {
                data: 'estado_ndebito'
            },
            {
                data: 'opciones'
            }

        ]


    });

    $('#tbl_listar_monto_debito').DataTable({
        "order": [3, 'desc'],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [3, 'asc'],
        pageLength: 10,
        responsive: true,


        "ajax": "/debito/lista/montos",
        "columns": [
            {
                data: 'id'
            },
            {
                data: 'monto'
            },
            {
                data: 'user'
            },
            {
                data: 'created_at'
            },
            {
                data: 'estado_monto'
            }

        ]


    });

    $('#tbl_listar_notas_debito').DataTable({
        "order": [3, 'desc'],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [3, 'asc'],
        pageLength: 10,
        responsive: true,


        "ajax": "/debito/lista/notas",
        "columns": [
            {
                data: 'id'
            },
            {
                data: 'factura_id'
            },
            {
                data: 'monto_asignado'
            },
            {
                data: 'fechaEmision'
            },
            {
                data: 'motivoDescripcion'
            },
            {
                data: 'cai_ndebito'
            },
            {
                data: 'numeroCai'
            },
            {
                data: 'correlativoND'
            },
            {
                data: 'user'
            },
            {
                data: 'estado'
            },
            {
                data: 'file'
            },
            {
                data: 'created_at'
            }

        ]


    });


})
