<div>

    @push('styles')
        <link href="{{ asset('css/plugins/iCheck/custom.css') }}" rel="stylesheet">
        <link href="{{ asset('css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css') }}"
            rel="stylesheet">
    @endpush

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <h2 class="font-weight-normal">Bodega</h2>
                </div>

                <div class="ibox-content">
                    <form id="bodegaCreacion" data-parsley-validate autocomplete="off">
                        <input type="hidden" name="_token" value="{!! csrf_token() !!}">

                        <div class="row">

                            <div class="col-sm-12 ">
                                <h4>Información general <i class="fa-solid fa-pen"></i> </h4>
                                <br>



                                <div class="form-group">
                                    <label for="bodega">Nombre de bodega<span class="text-danger">*</span></label>
                                    <input id="bodega" name="bodega" type="text" placeholder="Nombre de bodega"
                                        class="form-control" data-parsley-required required="">
                                </div>

                                <div class="form-group">
                                    <label for="direccionBodega">Dirección<span class="text-danger">*</span></label>
                                    <input id="direccionBodega" name="direccionBodega" type="text"
                                        placeholder="Direccion de bodega" class="form-control" data-parsley-required>
                                </div>

                                <div>
                                    <label for="encargadoBodega">Encargado de bodega<span class="text-danger">*</span></label>
                                    <select id="encargadoBodega" name="encargadoBodega" class="form-control m-b"
                                        name="account" data-parsley-required>
                                        <option value="0" selected disabled>Seleccione un encargado</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach

                                    </select>

                                </div>

                                <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="col-form-label focus-label">País<span class="text-danger">*</span></label>
                                    <select class="form-group form-control" name="pais_bodega" id="pais_bodega"
                                        onchange="obtenerDepartamentos()">
                                        <option selected disabled>---Seleccione un país---</option>
                                        @foreach ($paises as $pais)
                                            <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                                        @endforeach

                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="col-form-label focus-label">Departamento<span class="text-danger">*</span></label>
                                    <select class="form-group form-control" name="depto_bodega" id="depto_bodega"
                                        onchange="obtenerMunicipios()">
                                        <option selected disabled>---Seleccione un Departamento---</option>

                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="col-form-label focus-label">Municipio<span class="text-danger">*</span></label>
                                    <select class="form-group form-control" name="municipio_bodega" id="municipio_bodega"
                                        data-parsley-required>
                                        <option selected disabled>---Seleccione un municipio---</option>

                                    </select>
                                </div>
                            </div>




                            </div>



                            <div class="col-sm-12 border-top mt-2 mb-4">
                                <div class="d-flex justify-content-between   align-items-center ">

                                    <h4 class="mt-4"><i class="fa-solid fa-crop"></i> Segmentación de bodega<span class="text-danger">*</span>
                                    </h4>


                                    <button type="button" class="btn btn-sm btn-success pt-1 pb-4 mt-3"
                                        style="height:19.200px" onclick="agregarInputs()"><strong>Agregar Seccion <i
                                                class="fa-solid fa-plus"></i></strong></button>

                                </div>





                                <div id="divSecciones" class=" mt-4">

                                    <div id="0" class="row no-gutters">
                                        <div class="form-group col-7 col-sm-7 col-md-7 col-lg-7 col-xl-7 ">
                                            <div class="d-flex">
                                                <button class="btn btn-danger" type="button" style="display: inline" onclick="eliminarInput(0)" >
                                                    <i class="fa-regular fa-rectangle-xmark"></i></button>
                                                <div style="width:100%">
                                                    <label for="segmento0" class="sr-only">Letra de
                                                        Seccion</label>
                                                    <input type="text" placeholder="Letra de seccion" id="segmento0"
                                                        name="segmento0" class="form-control" pattern="[A-Z]{1}"
                                                        data-parsley-required data-parsley-pattern="[A-Z]{1}"
                                                        autocomplete="off">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group col-5 col-sm-5 col-md-5 col-lg-5 col-xl-5">
                                            <label for="seccion0" class="sr-only">Numero de secciones</label>
                                            <input type="number" placeholder="Numero de secciones" id="seccion0"
                                                name="seccion0" class="form-control" min="1" data-parsley-required
                                                autocomplete="off">
                                        </div>

                                    </div>

                                </div>



                            </div>






                        </div>
                    </form>
                    <div class="row">
                        <div class="col-lg-12">
                            <button class="btn btn-sm btn-primary float-left m-t-n-xs"
                                form="bodegaCreacion"><strong>Crear
                                    Bodega</strong></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>


    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">

                <div class="ibox-content" style="">
                    <div>
                        <img src="{{ asset('img_profac/Estante de Bodega.png') }}" alt="" style="margin: 0 auto">
                    </div>

                </div>

            </div>

        </div>
    </div>



    @push('scripts')
        <script src="{{ asset('js/plugins/iCheck/icheck.min.js') }}"></script>


        <script>
            $(document).ready(function() {
                $('.i-checks').iCheck({
                    checkboxClass: 'icheckbox_square-green',
                    radioClass: 'iradio_square-green',
                });
            });
        </script>

        <script src="{{ asset('js/js_proyecto/bodega.js') }}"></script>
    @endpush

</div>
