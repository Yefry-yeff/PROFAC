
            <!-- Modal para registro de CAI Devolución de Crédito-->
            <div class="modal fade" id="modal_cai_crear_devolucion" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="exampleModalLabel">Registro de CAI Nota Crétido</h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <form id="crearCAIDevolucionForm" name="crearCAIDevolucionForm" data-parsley-validate>
                                {{-- <input type="hidden" name="_token" value="{!! csrf_token() !!}"> --}}
                                <input id="tipo_documento_fiscal_id" name="tipo_documento_fiscal_id" type="hidden" value="5">
                                <div class="row" id="row_datos">

                                    <div class="col-md-12">
                                        <label for="cai" class="col-form-label focus-label">CAI:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="cai"
                                            name="cai" data-parsley-required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="fecha_limite" class="col-form-label focus-label">Fecha Limite:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="date" id="fecha_limite"
                                            name="fecha_limite" data-parsley-required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="cantidad_otorgada" class="col-form-label focus-label">Cantidad Otorgada:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="number" id="cantidad_otorgada"
                                            name="cantidad_otorgada" data-parsley-required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="cantidad_solicitada" class="col-form-label focus-label">Cantidad Solicitada:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="number" id="cantidad_solicitada"
                                            name="cantidad_solicitada" data-parsley-required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="numero_inicial" class="col-form-label focus-label">Numero Inicial:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="numero_inicial"
                                            name="numero_inicial" data-parsley-required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="numero_final" class="col-form-label focus-label">Numero Final:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="numero_final"
                                            name="numero_final" data-parsley-required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="punto_emision" class="col-form-label focus-label">Punto Emision:<span class="text-danger">*</span></label>
                                        <input class="form-control" required type="text" id="punto_emision"
                                            name="punto_emision" data-parsley-required>
                                    </div>

                                </div>
                            </form>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                            <button type="submit" form="crearCAIDevolucionForm" class="btn btn-primary">Guardar
                                CAI Devolución de Crédito</button>
                        </div>
                    </div>
                </div>
            </div>





    @push('scripts')
        <script>

         $(document).on('submit', '#crearCAIDevolucionForm', function(event) {
            event.preventDefault();
            guardarCaiDevolucion();
        });

        function guardarCaiDevolucion() {
            $('#modalSpinnerLoading').modal('show');

            var data = new FormData($('#crearCAIDevolucionForm').get(0));

            axios.post("/ventas/cai/guardar", data)
                .then(response => {
                    $('#modalSpinnerLoading').modal('hide');


                    $('#crearCAIDevolucionForm').parsley().reset();

                    document.getElementById("crearCAIDevolucionForm").reset();
                    $('#modal_cai_crear_devolucion').modal('hide');

                    $('#tbl_cai_listar').DataTable().ajax.reload();


                    Swal.fire({
                        icon: 'success',
                        title: 'Exito!',
                        text: "CAI creado con exito."
                    })

                })
                .catch(err => {
                    let data = err.response.data;
                    $('#modalSpinnerLoading').modal('hide');
                    $('#modal_cai_crear_devolucion').modal('hide');
                    Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text
                    })
                    console.error(err);

                })

        }


        </script>
    @endpush


