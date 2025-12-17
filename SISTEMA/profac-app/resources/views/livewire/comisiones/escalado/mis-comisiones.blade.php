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

/* === MOBILE / TABLET: CARDS POR SLIDE === */
@media (max-width: 767.98px) {
    .carousel-item .row > div {
        display: none;
    }
    .carousel-item .row > div:first-child {
        display: block;
    }
}

@media (max-width: 991.98px) and (min-width: 768px) {
    .carousel-item .row > div:nth-child(n+3) {
        display: none;
    }
}

/* === MES ACTUAL === */
.mes-actual-dot {
    width: 10px;
    height: 10px;
    background-color: #28a745;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
}

/* === FLECHAS OVERLAY, SOLO HOVER === */
#carouselComisiones .carousel-control-prev,
#carouselComisiones .carousel-control-next {
    width: 60px;
    opacity: 0;
    transition: opacity .25s ease;
    pointer-events: none;
}

/* Íconos más suaves */
#carouselComisiones .carousel-control-prev-icon,
#carouselComisiones .carousel-control-next-icon {
    background-size: 60% 60%;
    opacity: 0.7;
}

/* Mostrar flechas solo en hover */
#carouselComisiones:hover .carousel-control-prev,
#carouselComisiones:hover .carousel-control-next {
    opacity: 1;
    pointer-events: auto;
}
/* === FONDO SUAVE PARA FLECHAS === */
#carouselComisiones .carousel-control-prev,
#carouselComisiones .carousel-control-next {
    background: rgba(0, 0, 0, 0.25); /* fondo oscuro suave */
    border-radius: 80%;
}

/* Íconos más visibles */
#carouselComisiones .carousel-control-prev-icon,
#carouselComisiones .carousel-control-next-icon {
    opacity: 1;                 /* totalmente visibles */
    background-size: 50% 50%;   /* un poco más grandes */
}

/* Hover más claro */
#carouselComisiones:hover .carousel-control-prev,
#carouselComisiones:hover .carousel-control-next {
    background: rgba(0, 0, 0, 0.15);
}


/* === OCULTAR FLECHAS EN MOBILE === */
@media (max-width: 767.98px) {
    #carouselComisiones .carousel-control-prev,
    #carouselComisiones .carousel-control-next {
        display: none;
    }
}
.comision-card {
    text-align: center;
}
.comision-card h3 {
    letter-spacing: 0.5px;
}
/* Fondo con personalidad */
.comision-highlight {
    background: linear-gradient(145deg, #f8f9fa, #ffffff);
    box-shadow: 0 8px 22px rgba(0,0,0,.12);
    border-radius: 18px;
}

/* Sticker del dólar */
.icon-sticker {
    width: 52px;
    height: 52px;
    background: #e6f4ea;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    box-shadow: 0 4px 10px rgba(0,0,0,.15);
}

/* Mes actual más protagonista */
.mes-actual-dot {
    width: 10px;
    height: 10px;
    background-color: #28a745;
    border-radius: 50%;
    display: inline-block;
}

/* Hover con energía */
.comision-card:hover {
    transform: translateY(-8px) scale(1.01);
}

/* === CARD TABLA === */
.card-tabla-comisiones {
    border-radius: 14px;
    overflow: hidden;
}

/* === TABLA BASE === */
.table-comisiones {
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.88rem;
}

/* Header elegante */
.table-comisiones thead th {
    background: linear-gradient(180deg, #f8f9fa, #ffffff);
    text-transform: uppercase;
    font-size: 0.72rem;
    letter-spacing: .5px;
    color: #495057;
    border-bottom: 1px solid #dee2e6;
    padding: 12px 10px;
}

/* Celdas */
.table-comisiones tbody td {
    padding: 10px;
    vertical-align: middle;
    transition: all .2s ease;
}

/* Hover fila (movimiento suave) */
.table-comisiones tbody tr {
    transition: transform .15s ease, box-shadow .15s ease;
}

.table-comisiones tbody tr:hover {
    background-color: #f8f9fc;
    transform: scale(1.003);
    box-shadow: 0 6px 18px rgba(0,0,0,.06);
    position: relative;
    z-index: 1;
}

/* Primera columna destacada */
.table-comisiones tbody td:first-child {
    font-weight: bold;
    color: #007bff;
}

/* Columna Acciones */
.table-comisiones td:last-child {
    text-align: center;
}

/* Botones de acción más lindos */
.table-comisiones .btn {
    padding: 4px 8px;
    font-size: 0.75rem;
}

/* Estado como badge */
.badge-estado {
    padding: 5px 10px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
}

/* Scroll suave */
.table-responsive {
    scrollbar-width: thin;
}


</style>

<div class="card shadow-sm border-0 mb-3">
    <!-- HEADER -->
    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            Usuario <b>{{ $info->name }}</b>,
            Rol en sistema/comisiones: <b>{{ $info->rol }}</b>
        </h4>
    </div>
    <!-- BODY -->
    <div class="card-body">

        <div id="carouselComisiones"
            class="carousel slide"
            data-ride="carousel"
            data-interval="4500"
            data-pause="hover">

            <!-- INDICADORES -->
            <ol class="carousel-indicators">
                @foreach ($meses->chunk(4) as $index => $grupoMeses)
                    <li data-target="#carouselComisiones"
                        data-slide-to="{{ $index }}"
                        class="{{ $index == 0 ? 'active' : '' }}">
                    </li>
                @endforeach
            </ol>

            <!-- SLIDES -->
            <div class="carousel-inner">

                @foreach ($meses->chunk(4) as $index => $grupoMeses)
                    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                        <div class="row">

                            @foreach ($grupoMeses as $mes)
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-4"><div class="card comision-card h-100 border-0 comision-highlight">

                                    <div class="card-body d-flex flex-column align-items-center text-center position-relative">

                                        {{-- Sticker / Icono --}}
                                        @if($mes->comision_acumulada > 0)
                                            💰
                                        @else
                                            📉
                                        @endif

                                        <h5 class="font-weight-bold d-flex align-items-center justify-content-center mb-1">
                                            @if (\Carbon\Carbon::parse($mes->mes_comision)->isCurrentMonth())
                                                <span class="mes-actual-dot mr-1"></span>
                                            @endif
                                            {{ $mes->mes_anio }}
                                        </h5>

                                        <small class="text-uppercase text-muted mb-2">
                                            Comisión mensual
                                        </small>

                                        <div class="my-3">
                                            <h2 class="text-success font-weight-bold mb-0">
                                                L {{ number_format($mes->comision_acumulada, 2) }}
                                            </h2>
                                            <small class="text-muted">
                                                Comisión acumulada
                                            </small>
                                        </div>

                                    </div>
                                </div>

                                </div>
                            @endforeach

                        </div>
                    </div>
                @endforeach

            </div>

            <!-- CONTROLES -->
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
<!-- CARD LISTADO -->
<div class="card card-tabla-comisiones border-0 shadow-sm mb-3">

  <!-- Header -->
  <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
    <h6 class="mb-0 font-weight-bold text-primary">
      📋 Lista de comisiones por mes
    </h6>
  </div>

  <!-- Body -->
  <div class="card-body p-0">
    <div class="table-responsive">

      <table id="tbl_comisiones_empleado"
             class="table table-comisiones mb-0">

        <thead>
          <tr>
            <th>Id Empleado</th>
            <th>Empleado</th>
            <th>Año</th>
            <th>Mes</th>
            <th>Total Mes</th>
            <th>Facturas cerradas</th>
            <th>Ultima Actualización</th>
          </tr>
        </thead>

        <tbody></tbody>

      </table>

    </div>
  </div>
</div>
@push('scripts')
    <script src="{{ asset('js/js_proyecto/comisiones/Escalado/misComisiones.js') }}"></script>
@endpush
