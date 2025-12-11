<div>
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Usuario <b>{{ $info->name }}</b>, Rol en sistema/comisiones: <b>{{ $info->rol }}</b></h4>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalCategoriasClientes">
                <i class="bi bi-plus-circle mr-1"></i>+ Creación
            </button>
        </div>
        <div class="card-body p-2">
            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="ibox">
                    <div class="ibox-content">
                        <div class="table-responsive">
                        <table id="tbl_listaCategoria" class="table table-striped table-bordered table-hover">
                            <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Categoría</th>
                                <th>Descripción</th>
                                <th>Comentario</th>
                                <th>Estado</th>
                                <th>Registro</th>
                                <th>Creación</th>
                                <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
