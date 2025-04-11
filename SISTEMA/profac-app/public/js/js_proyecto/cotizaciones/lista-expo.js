
              //var varToken = {{ csrf_token() }};

        function anularVentaConfirmar(idFactura){

            Swal.fire({
            title: '¿Está seguro de anular esta factura?',
            text:'Una vez que ha sido anulada la factura el producto registrado en la misma sera devuelto al inventario.',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Si, Anular Compra',
            cancelButtonText: `Cancelar`,
            }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {

                //Swal.fire('Saved!', '', 'success')
                anularVenta(idFactura);

            }
            })
        }

        function anularVenta(idFactura){

            axios.post("/factura/corporativo/anular", {idFactura:idFactura})
            .then( response =>{


                let data = response.data;
                Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            html: data.text,
                        });
                        $('#tbl_listar_compras').DataTable().ajax.reload();

            })
            .catch( err => {

                Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Ha ocurrido un error al anular la compra.',
                        })

            })

        }
