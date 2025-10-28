@push('styles')
<style>
/* Encabezado más ligero */
.page-heading, .d-flex.bg-light {
    background-color: #f8f9fa;
    border-radius: 0.35rem;
    padding: 0.5rem 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

/* Select2 moderno y compacto */
.select2-container--bootstrap4 .select2-selection--single {
    height: 36px;
    padding: 3px 10px;
    border-radius: 0.35rem;
    border: 1px solid #ced4da;
    font-size: 0.9rem;
}

.select2-container--bootstrap4 .select2-selection__rendered {
    line-height: 30px;
}

.select2-container--bootstrap4 .select2-selection__arrow {
    height: 34px;
    right: 6px;
}

.select2-container--bootstrap4 .select2-dropdown {
    max-height: 200px; /* scroll si hay muchos items */
    overflow-y: auto;
}

/* Botón más plano y limpio */
.btn-success {
    font-weight: 500;
    padding: 0.35rem 0.9rem;
}

/* Responsivo */
@media (max-width: 576px) {
    form.d-flex {
        flex-direction: column;
    }

    form.d-flex > * {
        margin-bottom: 0.5rem;
    }
}
/* Contenedor de filtros: todo en línea, con espacio dinámico */
.filtro-container {
    gap: 0.5rem; /* Espacio entre elementos */
    flex-wrap: wrap; /* Si no cabe en una línea, se mueve abajo */
}

/* Select uniforme */
.filtro-select {
    min-width: 150px;
    flex: 1 1 150px; /* Crece o se reduce dinámicamente */
    height: 38px; /* Igual altura para todos */
}

/* Botón alineado con los selects */
#btnDescargar {
    height: 38px; /* Mismo alto que los selects */
}

/* Responsivo: en pantallas pequeñas */
@media (max-width: 576px) {
    .filtro-container {
        flex-direction: column;
        gap: 0.5rem;
    }
    #btnDescargar {
        width: 100%; /* Botón ocupa todo el ancho en móvil */
    }
}
/* Select uniforme y un poco más ancho */
.filtro-select {
    min-width: 200px;      /* antes era 150px */
    flex: 1 1 220px;       /* crece hasta 220px aprox */
    height: 38px;          /* misma altura */
    font-size: 0.9rem;     /* tamaño de texto consistente */
}

/* Para pantallas medianas o grandes, deja respirar más los selects */
@media (min-width: 992px) {
    .filtro-select {
        min-width: 240px;
        flex: 1 1 240px;
    }
}
</style>
@endpush

<div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded shadow-sm flex-wrap">
    <h3 class="mb-0 text-dark"><b>Categoría de Clientes</b></h3> <h4>Creación de categorías</h4>
            <button type="submit" class="btn btn-success ml-2" id="btnDescargar">Abrir Ventana de creación</button>
        </div>
    </form>
</div>



@push('scripts')

@endpush

