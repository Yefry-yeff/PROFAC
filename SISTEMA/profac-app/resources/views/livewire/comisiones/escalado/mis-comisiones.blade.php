<style>
/* === CARD COMISIÓN === */
.comision-card {
    border: none;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,.08);
    transition: all .25s ease;
    background-color: #fff;
}

.comision-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 28px rgba(0,0,0,.15);
}

/* === PROGRESS === */
.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-grow {
    animation: growProgress 1s ease-out;
}

@keyframes growProgress {
    from { width: 0; }
}

/* === CAROUSEL CONTROLS (más discretos) === */
.carousel-control-prev-icon,
.carousel-control-next-icon {
    filter: invert(1);
}
</style>

<div class="card shadow-sm border-0 mb-3">

    <!-- HEADER -->
    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            Usuario <b>{{ $info->name }}</b>,
            Rol en sistema/comisiones: <b>{{ $info->rol }}</b>
        </h4>
        <button type="button"
                class="btn btn-primary btn-sm"
                data-toggle="modal"
                data-target="#modalCategoriasClientes">
            + Creación
        </button>
    </div>

    <!-- BODY -->
    <div class="card-body">

        <div id="carouselComisiones"
             class="carousel slide"
             data-ride="carousel"
             data-interval="4500"
             data-pause="hover">

            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="row">
                        <!-- CARD -->
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                            <div class="card comision-card h-100">
                                <div class="card-body d-flex flex-column">

                                    <small class="text-muted text-uppercase">
                                        Comisión mensual
                                    </small>
                                    <h5 class="font-weight-bold">
                                        Marzo · 2025
                                    </h5>

                                    <div class="my-3">
                                        <h3 class="text-primary font-weight-bold mb-0">
                                            L 12,450.75
                                        </h3>
                                        <small class="text-muted">
                                            Comisión acumulada
                                        </small>
                                    </div>

                                    <div class="mb-3">
                                        <div class="progress" style="height:6px;">
                                            <div class="progress-bar bg-success progress-grow"
                                                 role="progressbar"
                                                 style="width:65%">
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            65% del objetivo
                                        </small>
                                    </div>

                                    <a href="#"
                                       class="btn btn-outline-primary btn-sm mt-auto">
                                        Ver facturas comisionadas
                                    </a>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <a class="carousel-control-prev"
               href="#carouselComisiones"
               role="button"
               data-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </a>

            <a class="carousel-control-next"
               href="#carouselComisiones"
               role="button"
               data-slide="next">
                <span class="carousel-control-next-icon"></span>
            </a>

        </div>

    </div>
</div>
