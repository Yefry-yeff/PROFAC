
$(document).ready(function() {
    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
    });
});

function obtenerDepartamentos() {
    document.getElementById("depto_prov").innerHTML = '<option value="">Seleccione un departamento</option>';
    document.getElementById("municipio_prov").innerHTML = '<option value="">Seleccione un municipio</option>';
    var id = document.getElementById("pais_prov").value;
    if (!id) return;
    //console.log(id)
    ///proveedores/obeter/departamento
    let datos = {
        "id": id
    };

    axios.post('/proveedores/obeter/departamentos', datos)
        .then(function(response) {

            let array = response.data.departamentos;
            if (document.getElementById("pais_prov").value !== id) return;
            let html = '<option value="">Seleccione un departamento</option>';

            array.forEach(departamento => {

                html +=
                    `
            <option value="${ departamento.id }">${departamento.nombre}</option>
           `
            });

            document.getElementById("depto_prov").innerHTML = html;


        })
        .catch(function(error) {
            // handle error
            console.log(error);

            Swal.fire({
                icon: 'error',
                title: 'Error...',
                text: "Ha ocurrido un error al obtener los departamentos"
            })
        })



}

function obtenerMunicipios() {
    document.getElementById("municipio_prov").innerHTML = '<option value="">Seleccione un municipio</option>';
    //municipio_prov
    var id = document.getElementById("depto_prov").value;
    if (!id) return;

    let datos = {
        "id": id
    }


    axios.post('/proveedores/obeter/municipios', datos)
        .then(function(response) {

            let array = response.data.departamentos;
            if (document.getElementById("depto_prov").value !== id) return;
            let html = '<option value="">Seleccione un municipio</option>';

            array.forEach(municipio => {

                html +=
                    `
            <option value="${ municipio.id }">${municipio.nombre}</option>
           `
            });

            document.getElementById("municipio_prov").innerHTML = html;


        })
        .catch(function(error) {
            // handle error
            console.log(error);

            Swal.fire({
                icon: 'error',
                title: 'Error...',
                text: "Ha ocurrido un error al obtener los municipios"
            })
        })

}

$(document).ready(function() {
    $('#tbl_proveedoresListar').DataTable({
        "order": [0, 'desc'],
        "language": {
            "url": "/js/plugins/dataTables/i18n/Spanish.json"
        },
        pageLength: 10,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp',
        buttons: [{
                extend: 'copy'
            },
            {
                extend: 'csv'
            },
            {
                extend: 'excel',
                title: 'ExampleFile'
            },
            {
                extend: 'pdf',
                title: 'ExampleFile'
            },

            {
                extend: 'print',
                customize: function(win) {
                    $(win.document.body).addClass('white-bg');
                    $(win.document.body).css('font-size', '10px');

                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('font-size', 'inherit');
                }
            }
        ],
        "ajax": "/proveedores/listar/proveedores",
        "columns": [{
                data: 'id'
            },
            {
                data: 'nombre'
            },
            {
                data: 'direccion'
            },
            {
                data: 'telefono_1'
            },
            {
                data: 'correo_1'
            },
            {
                data: 'rtn'
            },
            {
                data: 'retencion'
            },
            {
                data: 'estado'
            },
            {
                data: 'opciones'
            },
        ]


    });
})


$(document).on('submit', '#proveedorCreacionForm', function(event) {

    event.preventDefault();
    crearProveedores();

});


function crearProveedores() {
    var data = new FormData($('#proveedorCreacionForm').get(0));
    limpiarErroresProveedor('proveedorCreacionForm');
    $('button[form="proveedorCreacionForm"][type="submit"]').prop('disabled', true);

    $.ajax({
        type: "POST",
        url: "/proveedores/crear",
        data: data,
        contentType: false,
        cache: false,
        processData: false,
        dataType: "json",
        success: function(data) {


            document.getElementById("proveedorCreacionForm").reset();
            $('#modal_proveedores_crear').modal('hide');


            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Proveedor creado con exito."
            })
            $('#proveedorCreacionForm').parsley().reset();
            $('#tbl_proveedoresListar').DataTable().ajax.reload();






        },
        error: function(jqXHR, textStatus, errorThrown) {
            mostrarErroresProveedor('proveedorCreacionForm', jqXHR.responseJSON);
        },
        complete: function() {
            $('button[form="proveedorCreacionForm"][type="submit"]').prop('disabled', false);
        }
    });
}

function desactivarProveedor(id) {


    Swal.fire({
        title: '¿Esta seguro de desactivar este proveedor?',
        text: "¡Si desactiva este proveedor, ya no podrá ingresar o almacenar productos con este proveedor.!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '¡Sí, Desactivar proveedor!'
    }).then((result) => {
        if (result.isConfirmed) {


            axios.post('/proveedores/desactivar', {
                    "id": id
                })
                .then(function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Exito!',
                        text: "Proveedor desactivado con exito."
                    })

                    $('#tbl_proveedoresListar').DataTable().ajax.reload();

                })
                .catch(function(error) {
                    // handle error
                    console.log(error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: "Ha ocurrido un error al desactivar el proveedor."
                    })
                })





        }
    })


}

function activarProveedor(id) {
    axios.post('/proveedores/desactivar', {
            "id": id
        })
        .then(function(response) {

            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Proveedor Activado con exito."
            })

            $('#tbl_proveedoresListar').DataTable().ajax.reload();

        })
        .catch(function(error) {
            // handle error
            console.log(error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: "Ha ocurrido un error al Activar el proveedor."
            })
        })

}

function mostrarModalEditar(id){

    let data = {"id":id}

    axios.post('/proveedores/editar',data)
    .then( response=>{

        let proveedor =  response.data.proveedor;

        let paisProveedor = response.data.paisProveedor;
        let departamentoProveedor = response.data.departamentoProveedorId;
        let municipioProveedor = response.data.municipioProveedorId;


        let paises = response.data.paises;
        let departamentos = response.data.departamentos;
        let municipios = response.data.municipios;
        let tipoPersonalidad = response.data.tipoPersonalidad;
        let categorias = response.data.categoria;


        let htmlSelectPais ="";
        let htmlSelectDepto = "";
        let htmlSelectMunicipio = "";
        let htmlPersonalidad ="";
        let htmlCategoria ="";


        document.getElementById("idProveedor").value = proveedor.id;
        document.getElementById("editar_nombre_prov").value = proveedor.nombre;
        document.getElementById("editar_direccion_prov").value = proveedor.direccion;
        document.getElementById("editar_telefono_prov").value = proveedor.telefono_1;
        document.getElementById("editar_telefono_prov_2").value = proveedor.telefono_2;
        document.getElementById("editar_correo_prov").value = proveedor.correo_1;
        document.getElementById("editar_rtn_prov").value = proveedor.rtn;
        document.getElementById("editar_retencion_prov").value = response.data.retencionProveedor;
        const editForm = document.getElementById('proveedorEditarForm');
        editForm.querySelector('[data-aplicar-retencion]').checked = Number(response.data.retencionProveedor) !== 1;
        editForm.querySelector('[name="editar_rtn_sin_registro"]').checked = proveedor.rtn === 'N/A';
        editForm.querySelector('[name="editar_telefono_1_sin_registro"]').checked = !proveedor.telefono_1;
        editForm.querySelector('[name="editar_telefono_2_sin_registro"]').checked = !proveedor.telefono_2;


        paises.forEach( pais =>{
            if(pais.id === paisProveedor ){

                htmlSelectPais += `
                <option value="${ pais.id }" selected>${pais.nombre}</option>
                `
            }else{
                htmlSelectPais += `
                <option value="${ pais.id }">${pais.nombre}</option>
                `
            }

        });



        departamentos.filter(departamento => departamento.pais_id === undefined || departamento.pais_id == paisProveedor).forEach( departamento =>{
            if(departamento.id === departamentoProveedor ){

                htmlSelectDepto += `
                <option value="${ departamento.id }" selected>${departamento.nombre}</option>
                `
            }else{
                htmlSelectDepto += `
                <option value="${ departamento.id }">${departamento.nombre}</option>
                `
            }

        });

        municipios.filter(municipio => municipio.departamento_id === undefined || municipio.departamento_id == departamentoProveedor).forEach( municipio =>{

            if(municipio.id == municipioProveedor){

                htmlSelectMunicipio += `
                <option value="${ municipio.id }" selected>${municipio.nombre}</option>
                `

            }else{

                htmlSelectMunicipio += `
                <option value="${ municipio.id }">${municipio.nombre}</option>
                `
            }

        });

        tipoPersonalidad.forEach( personalidad => {
            if(personalidad.id == proveedor.tipo_personalidad_id){

                htmlPersonalidad += `
                <option value="${ personalidad.id }" selected>${personalidad.nombre}</option>
                `

                }else{

                    htmlPersonalidad += `
                <option value="${ personalidad.id }">${personalidad.nombre}</option>
                `
                }

        })

        categorias.forEach( categoria =>{

            if(categoria.id == proveedor.categoria_id){

                htmlCategoria += `
                <option value="${ categoria.id }" selected>${categoria.nombre}</option>
                `

                }else{

                    htmlCategoria += `
                <option value="${ categoria.id }">${categoria.nombre}</option>
                `
                }

        } );


        document.getElementById("editar_pais_prov").innerHTML = htmlSelectPais;
        document.getElementById("editar_depto_prov").innerHTML = htmlSelectDepto;
        document.getElementById("editar_municipio_prov").innerHTML = htmlSelectMunicipio;
        document.getElementById("editar_giro_neg_prov").innerHTML = htmlPersonalidad;
        document.getElementById("editar_categoria_prov").innerHTML = htmlCategoria;











        $("#modal_proveedores_editar").modal("show");
        return;
    })
    .catch(err=>{

        console.log(err)

    });


}

function editarObtenerDepartamentos() {
    document.getElementById("editar_depto_prov").innerHTML = '<option value="">Seleccione un departamento</option>';
    document.getElementById("editar_municipio_prov").innerHTML = '<option value="">Seleccione un municipio</option>';
    var id = document.getElementById("editar_pais_prov").value;
    if (!id) return;


    //console.log(id)
    ///proveedores/obeter/departamento
    let datos = {
        "id": id
    };

    axios.post('/proveedores/obeter/departamentos', datos)
        .then(function(response) {

            let array = response.data.departamentos;
            if (document.getElementById("editar_pais_prov").value !== id) return;
            let html = '<option value="">Seleccione un departamento</option>';

            array.forEach(departamento => {

                html +=
                    `
            <option value="${ departamento.id }">${departamento.nombre}</option>
           `
            });

            document.getElementById("editar_depto_prov").innerHTML = html;


        })
        .catch(function(error) {
            // handle error
            console.log(error);

            Swal.fire({
                icon: 'error',
                title: 'Error...',
                text: "Ha ocurrido un error al obtener los departamentos"
            })
        })



}

function editarObtenerMunicipios() {
    document.getElementById("editar_municipio_prov").innerHTML = '<option value="">Seleccione un municipio</option>';
    //municipio_prov
    var id = document.getElementById("editar_depto_prov").value;
    if (!id) return;

    let datos = {
        "id": id
    }


    axios.post('/proveedores/obeter/municipios', datos)
        .then(function(response) {

            let array = response.data.departamentos;
            if (document.getElementById("editar_depto_prov").value !== id) return;
            let html = '<option value="">Seleccione un municipio</option>';

            array.forEach(municipio => {

                html +=
                    `
            <option value="${ municipio.id }">${municipio.nombre}</option>
           `
            });

            document.getElementById("editar_municipio_prov").innerHTML = html;


        })
        .catch(function(error) {
            // handle error
            console.log(error);

            Swal.fire({
                icon: 'error',
                title: 'Error...',
                text: "Ha ocurrido un error al obtener los municipios"
            })
        })

}

$(document).on('submit', '#proveedorEditarForm', function(event) {
event.preventDefault();
editarProveedor();
});

function editarProveedor(){
    var data = new FormData($('#proveedorEditarForm').get(0));
    limpiarErroresProveedor('proveedorEditarForm');
    $('button[form="proveedorEditarForm"][type="submit"]').prop('disabled', true);
    axios.post("/proveedores/editar/guardar", data)
    .then( response =>{
        document.getElementById("proveedorEditarForm").reset();
            $('#modal_proveedores_editar').modal('hide');


            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Proveedor editado con exito."
            })

            $('#tbl_proveedoresListar').DataTable().ajax.reload();
            $('#proveedorEditarForm').parsley().reset();


    })
    .catch(err => {
        mostrarErroresProveedor('proveedorEditarForm', err.response && err.response.data);
    })
    .finally(() => {
        $('button[form="proveedorEditarForm"][type="submit"]').prop('disabled', false);
    });




}

function limpiarErroresProveedor(formId) {
    const form = document.getElementById(formId);
    $(form).find('.proveedor-form-errors').empty().addClass('d-none');
    $(form).find('[aria-invalid="true"]').removeAttr('aria-invalid').removeClass('is-invalid');
}

function mostrarErroresProveedor(formId, response) {
    const form = document.getElementById(formId);
    const container = form.querySelector('.proveedor-form-errors');
    const errors = response && response.errors;
    container.replaceChildren();
    if (errors) {
        Object.entries(errors).forEach(([name, messages]) => {
            const field = Array.from(form.elements).find(element =>
                element.name === name && !element.disabled && element.type !== 'hidden');
            if (field) {
                field.setAttribute('aria-invalid', 'true');
                field.classList.add('is-invalid');
            }
            messages.forEach(message => {
                const line = document.createElement('div');
                line.textContent = message;
                container.appendChild(line);
            });
        });
    } else {
        container.textContent = response && response.message ? response.message : 'No se pudo guardar el proveedor. Intente nuevamente.';
    }
    container.classList.remove('d-none');
    container.focus();
}

$(document).on('hidden.bs.modal', '#modal_proveedores_crear, #modal_proveedores_editar', function() {
    document.body.classList.remove('proveedor-modal-open');
    const form = this.querySelector('form');
    form.reset();
    actualizarRetencionProveedor(form.querySelector('[data-aplicar-retencion]'));
    form.querySelectorAll('[data-sin-registro]').forEach(check => {
        delete check.dataset.valorAnterior;
        actualizarSinRegistroProveedor(check);
    });
    $(form).parsley().reset();
    limpiarErroresProveedor(form.id);
    $(form).find('select[id$="depto_prov"]').html('<option value="">Seleccione un departamento</option>');
    $(form).find('select[id$="municipio_prov"]').html('<option value="">Seleccione un municipio</option>');
});

$(document).on('show.bs.modal', '#modal_proveedores_crear, #modal_proveedores_editar', function() {
    document.body.classList.add('proveedor-modal-open');
    this.querySelectorAll('[data-proveedor-telefono]').forEach(field => {
        if (/^[0-9]{8}$/.test(field.value)) {
            field.value = field.value.slice(0, 4) + '-' + field.value.slice(4);
        }
    });
    this.querySelectorAll('[data-sin-registro]').forEach(actualizarSinRegistroProveedor);
    actualizarRetencionProveedor(this.querySelector('[data-aplicar-retencion]'));
});

function actualizarRetencionProveedor(check) {
    const section = check.closest('fieldset');
    const select = section.querySelector('select');
    section.querySelector('[data-retencion-opciones]').classList.toggle('d-none', !check.checked);
    section.querySelector('[data-sin-retencion]').disabled = check.checked;
    select.disabled = !check.checked;
    select.required = check.checked;
    check.setAttribute('aria-expanded', String(check.checked));
    if (!check.checked) {
        select.classList.remove('is-invalid');
        select.removeAttribute('aria-invalid');
        $(select).parsley().reset();
    }
}

$(document).on('change', '.proveedores-view [data-aplicar-retencion]', function() {
    actualizarRetencionProveedor(this);
});

function actualizarSinRegistroProveedor(check) {
    const field = document.getElementById(check.dataset.sinRegistro);
    if (check.checked) {
        if (!field.disabled) {
            check.dataset.valorAnterior = field.value === check.dataset.sinRegistroValor ? '' : field.value;
        }
        field.value = check.dataset.sinRegistroValor;
    } else if (field.disabled) {
        field.value = check.dataset.valorAnterior || '';
    }
    field.disabled = check.checked;
    field.classList.remove('is-invalid');
    field.removeAttribute('aria-invalid');
    $(field).parsley().reset();
}

$(document).on('change', '.proveedores-view [data-sin-registro]', function() {
    actualizarSinRegistroProveedor(this);
});

$(document).on('input', '.proveedores-view [data-proveedor-telefono]', function() {
    const cursor = this.selectionStart;
    const digitsBeforeCursor = this.value.slice(0, cursor).replace(/\D/g, '').length;
    const digits = this.value.replace(/\D/g, '').slice(0, 8);
    this.value = digits.length > 4 ? digits.slice(0, 4) + '-' + digits.slice(4) : digits;
    const position = Math.min(digitsBeforeCursor + (digitsBeforeCursor > 4 ? 1 : 0), this.value.length);
    this.setSelectionRange(position, position);
});
