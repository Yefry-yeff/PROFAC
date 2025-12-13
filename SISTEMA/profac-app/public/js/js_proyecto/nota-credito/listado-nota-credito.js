
$(document).ready(function() {
    tablas();
})


function ajustesPorfecha(){
    let inicio = document.getElementById('fechaInicio').value;
    let final = document.getElementById('fechaFinal').value;

    console.log("entro")

    fechaInicio = inicio;
    fechaFinal = final;

    $('#tbl_listar_ajustes').DataTable().clear().destroy();
   // $('#tbl_listar_ventas_dos').DataTable().clear().destroy();

    this.tablas();

    //$('#tbl_listar_ventas_uno').DataTable().ajax.reload();
    //$('#tbl_listar_ventas_dos').DataTable().ajax.reload();
}


function anularNota(idNotaCredito, idFactura){
    axios.post('/nota/credito/anular', {
                        idFactura: idFactura,
                        idNotaCredito: idNotaCredito
                    })
                    .then(response => {

                        let data = response.data;

                        Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            html: data.text,

                        })

                        location.reload()


                        return;


                    })
                    .catch(err => {


                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Ha ocurrido un error al anular nota de credito.",
                        })

                    })
}
