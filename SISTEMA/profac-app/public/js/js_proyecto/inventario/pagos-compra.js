

$(document).on('submit', '#form_registro_pago', function(event) {
    event.preventDefault();
    retencionComprobar();
    });



    function registrarPago(){

        document.getElementById('btn_registro_pago').disabled = true;


        document.getElementById("compraId").value=idCompra;
        var data = new FormData($('#form_registro_pago').get(0));
        data.append('retencionEstado',retencionEstado);

        axios.post('/producto/compra/pagos/registro', data)
        .then( response =>{

            let data = response.data;

            if(data.icon == "success"){
                document.getElementById('form_registro_pago').reset();

               $('#form_registro_pago').parsley().reset();
               $('#modal_registro_pagos').modal('hide')

               Swal.fire({
               icon: data.icon,
               title: data.title,
               text: data.text,
               })

               datosCompra();
               $('#tbl_listar_pagos').DataTable().ajax.reload();
               document.getElementById('btn_registro_pago').disabled = false;
            }

            Swal.fire({
               icon: data.icon,
               title: data.title,
               text: data.text,
               })

               document.getElementById('form_registro_pago').reset();

               $('#form_registro_pago').parsley().reset();
               $('#modal_registro_pagos').modal('hide')
               document.getElementById('btn_registro_pago').disabled = false;
               return;



        })
        .catch( err =>{
            document.getElementById('btn_registro_pago').disabled = false;
            Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Ha ocurrido un error al guardar el registro de pago!',
            })

            console.log(err);

        })


    }

    function datosCompra(){
        axios.post('/producto/compra/pagos/datos',{idCompra:idCompra})
        .then( response =>{

            let data = response.data.compra;

        document.getElementById("debitoCompra").innerHTML = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'Lps' }).format(data.debito);
        document.getElementById("totalComra").innerHTML = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'Lps' }).format(data.total);
        document.getElementById("retencion").innerHTML = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'Lps' }).format(data.monto_retencion);

        if(data.numero_secuencia_retencion){
           let btn= document.getElementById('btn_print_retencion');
           btn.classList.remove("d-none");
        }

        })
        .catch( err=>{

            Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Ha ocurrido un error al obtener los datos de compra!',
            })

            console.log(err);


        })
    }

    function confirmarEliminarPago(idPago){

        Swal.fire({
        title: '¿Esta seguro de eliminar este registro de pago?',
        text:'Si elimina este registro de pago, el mismo será restado a los abonos de la deuda.',
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: 'Si, eliminar',

        cancelButtonText: `Cancelar`,
        }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            eliminarPago(idPago);
        }
        })

    }

    function eliminarPago(idPago){

        axios.post('/producto/compra/pagos/eliminar', {idPago:idPago})
        .then( response =>{

            Swal.fire({
            icon: 'success',
            title: 'Exito!',
            text: 'El registro ha sido eliminado con exito!',
            })

            datosCompra();
               $('#tbl_listar_pagos').DataTable().ajax.reload();

        })
        .catch(err=>{
            Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Ha ocurrido un error a eliminar el registro!',
            });

            console.log(err);

        })

    }

    function retencionComprobar(){
        $('#modal_registro_pagos').modal('hide');

        axios.post("/producto/compra/pagos/comprobar",{idCompra:idCompra})
        .then( response =>{

            let data = response.data.numero_pagos;


            if(data.numero_pagos == 0){
                Swal.fire({
                        title: "Retencion del 1%",
                        text: "¿Desea aplicar retención del 1% a esta compra?",
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: 'Aplicar',
                        denyButtonText: `No aplicar`,
                        cancelButtonText: 'Cancelar',
                    }).then((result) => {
                        /* Read more about isConfirmed, isDenied below */
                        if (result.isConfirmed) {
                            Swal.fire('La retencion sera aplicada a esta compra!', '', 'success')
                            retencionEstado = 1;
                            this.registrarPago();

                        } else if (result.isDenied) {
                            Swal.fire('¡No se aplicara retencion a esta compra!', '', 'info')
                            retencionEstado = 0;
                            this.registrarPago();
                        }
                    })

            }else{
                this.registrarPago();
            }

        })
        .catch( err =>{

            console.log(err);

        })


    }
