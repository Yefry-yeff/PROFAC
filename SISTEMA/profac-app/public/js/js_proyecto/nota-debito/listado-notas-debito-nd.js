
listarNotasDebito();
function listarNotasDebito(){

    let fechaInicio = document.getElementById("fechaInicio").value;
    let fechaFinal = document.getElementById("fechaFinal").value;

    $('#tbl_listar_notas_debito').DataTable().clear().destroy();


    $('#tbl_listar_notas_debito').DataTable({
    "order": [3, 'desc'],
    "language": {
        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
    },
    "order": [0, 'desc'],
    pageLength: 10,
    responsive: true,


    "ajax": "/listado/nota/debito/gobierno/"+fechaInicio+"/"+fechaFinal,
    "columns": [
        {
            data: 'id'
        },
        {
            data: 'correlativoND'
        },
        {
            data: 'monto_asignado'
        },
        {
            data: 'cai'
        },
        {
            data: 'cliente'
        },
        {
            data: 'fechaEmision'
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
        },
        {
            data: 'acciones'
        }

    ]


});
}

function anularnd(idNota){

    axios.get("/debito/anular/"+idNota)
        .then(response => {


            $('#tbl_listar_notas_debito').DataTable().ajax.reload();


            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Anulado con exito."
            })

    })
    .catch(err => {
        console.error(err);
        Swal.fire({
                icon: 'error',
                text: "Hubo un error al anular nota de débito."
            })

    })

}
