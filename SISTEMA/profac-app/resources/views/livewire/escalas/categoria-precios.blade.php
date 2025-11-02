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
  <h3 class="mb-0 text-dark"><b>Categoría de precios</b></h3>
  <h4>Creación de categorías</h4>
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCategoriasPrecios">
    Abrir Ventana de creación
  </button>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded shadow-sm flex-wrap">
    <h4 class="mb-0 text-dark"><b>Plantilla de productos para precios</b></h4>

    <form id="formExport" method="GET" action="{{ route('excel.plantilla') }}">
        <div class="d-flex align-items-center flex-wrap filtro-container">

            <!-- Tipo de inserción -->
            <select id="tipoCategoria" name="tipoCategoria" class="form-control select2bs4 filtro-select">
                <option value="">🧾 Tipo de categoría</option>
                <option value="escalable">📈 Escalable</option>
                <option value="manual">✍️ Manual</option>
            </select>

            <!-- Tipo de filtro -->
            <select id="tipoFiltro" name="tipoFiltro" class="form-control select2bs4 filtro-select">
                <option value="">📂 Formato</option>
                <option value="1">🏷️ Marca</option>
                <option value="2">📂 Categoría</option>
            </select>

            <!-- Filtro por valor -->
            <select id="listaTipoFiltro" name="listaTipoFiltro" class="form-control select2bs4 filtro-select">
                <option value="">Seleccione filtro</option>
            </select>

            <button type="submit" class="btn btn-success ml-2" id="btnDescargar" disabled>
                📥 Descargar plantilla
            </button>
        </div>
    </form>
</div>



@push('scripts')

    <script>
        $('#listaTipoFiltro, #tipoCategoria').on('change', function() {
            const tipoCategoria = $('#tipoCategoria').val();
            const lista = $('#listaTipoFiltro').val();
            $('#btnDescargar').prop('disabled', !(tipoCategoria && lista));
        });
        $(document).ready(function() {
            // Inicialmente deshabilitamos el botón
            const $btnDescargar = $('#btnDescargar');
            $btnDescargar.prop('disabled', true);

            // Detectar cambios en el select dinámico
            $('#listaTipoFiltro').on('change', function() {
                if ($(this).val()) {
                    $btnDescargar.prop('disabled', false); // habilitar
                } else {
                    $btnDescargar.prop('disabled', true); // deshabilitar
                }
            });
        });


        $(document).ready(function() {
                // Tipo de categoría
                $('#tipoCategoria').select2({
                    theme: 'bootstrap4',
                    placeholder: "🧾 Tipo de categoría",
                    width: 'resolve'
                });

                // Tipo de filtro
                $('#tipoFiltro').select2({
                    theme: 'bootstrap4',
                    placeholder: "📂 Tipo de filtro",
                    width: 'resolve'
                });

                // Lista de valores según el filtro
                $('#listaTipoFiltro').select2({
                    theme: 'bootstrap4',
                    placeholder: "Seleccione una opción",
                    width: 'resolve'
                });

            $('#tipoFiltro').on('change', function() {
                let tipo = $(this).val();
                let $listaTipo = $('#listaTipoFiltro');

                $listaTipo.val(null).trigger('change');
                $listaTipo.empty();

                if (!tipo) return;

                let url = tipo == '1' ? '/filtros/marca' : '/filtros/categoria';

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $listaTipo.append(new Option('Seleccione', '', false, false));
                        data.forEach(function(item) {
                            $listaTipo.append(new Option(item.nombre, item.id, false, false));
                        });
                        $listaTipo.trigger('change');
                    }
                });
            });
        });
    </script>



@endpush

