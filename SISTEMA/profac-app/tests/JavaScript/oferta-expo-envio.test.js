const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const blade = fs.readFileSync(path.join(__dirname, '../../resources/views/livewire/ventas/facturacion-unificada.blade.php'), 'utf8');

function fragmento(desde, hasta) {
    const inicio = blade.indexOf(desde);
    const fin = blade.indexOf(hasta, inicio);
    assert.ok(inicio >= 0 && fin > inicio, 'No se encontró el código del flujo de guardado');
    return blade.slice(inicio, fin);
}

function crearFlujo(confirmarPrecio = true) {
    let submit;
    let modales = 0;
    const solicitudes = [];
    const boton = { disabled: false };
    const datos = new Map([
        ['zone_group_id', '7'],
        ['direccion_entrega', 'Dirección de prueba'],
    ]);
    const contexto = {
        codigoActual: 'cotizacion_clientes_a',
        expoConfig: { id: 1 },
        cambiosPrecioExpoConfirmados: false,
        ofertaEnvioConfirmado: false,
        tipoFacturaConfig: {},
        arregloIdInputs: [],
        numeroInputs: 0,
        urls: { guardar: '/cotizacion/guardar' },
        window: { location: { search: '' } },
        URLSearchParams,
        document: { getElementById: id => id === 'btn_venta_coorporativa' ? boton : null },
        Swal: { fire: () => ({ then: callback => callback({ isConfirmed: confirmarPrecio }) }) },
        obtenerCambiosPrecioExpo: () => [{ producto: 'Producto', precio_actual: 10, precio_nuevo: 12 }],
        mostrarModalEnvioOferta: () => { modales++; },
        FormData: class {
            constructor() { this.datos = new Map(datos); }
            set(clave, valor) { this.datos.set(clave, valor); }
            append(clave, valor) { this.datos.set(clave, valor); }
            get(clave) { return this.datos.get(clave); }
            forEach(callback) { this.datos.forEach(callback); }
        },
        axios: { post: (url, body) => {
            solicitudes.push({ url, body });
            return { then: () => ({ catch: () => {} }) };
        } },
    };
    contexto.$ = selector => {
        if (selector === contexto.document) {
            return { on: (evento, elemento, callback) => { submit = callback; } };
        }
        if (selector === '#crear_venta') {
            return { submit: () => submit({ preventDefault() {} }), get: () => ({}) };
        }
        if (selector === '<div>') {
            return { text: valor => ({ html: () => String(valor) }) };
        }
        return { val: () => '' };
    };
    vm.createContext(contexto);
    vm.runInContext(
        fragmento("    $(document).on('submit', '#crear_venta'", '    function obtenerCambiosPrecioExpo()')
        + fragmento('    function mostrarAdvertenciaCambiosPrecioExpo(', '    function mostrarModalSrAutorizacion()')
        + fragmento('    function guardarVenta(confirmarPrecioBajoEscala = false)', '    function mostrarResumenLiquidacionExpo('),
        contexto
    );
    return { contexto, solicitudes, boton, enviar: () => submit({ preventDefault() {} }), modales: () => modales };
}

test('confirmar el cambio de precio solicita la zona antes de guardar', () => {
    const flujo = crearFlujo();
    flujo.enviar();
    assert.equal(flujo.contexto.cambiosPrecioExpoConfirmados, true);
    assert.equal(flujo.modales(), 1);
    assert.equal(flujo.solicitudes.length, 0);

    flujo.contexto.ofertaEnvioConfirmado = true;
    flujo.enviar();
    assert.equal(flujo.solicitudes.length, 1);
    assert.equal(flujo.solicitudes[0].body.zone_group_id, '7');
    assert.equal(flujo.solicitudes[0].body.direccion_entrega, 'Dirección de prueba');
});

test('cancelar el cambio de precio no abre el modal ni guarda', () => {
    const flujo = crearFlujo(false);
    flujo.enviar();
    assert.equal(flujo.contexto.cambiosPrecioExpoConfirmados, false);
    assert.equal(flujo.modales(), 0);
    assert.equal(flujo.solicitudes.length, 0);
});

test('guardar una oferta directamente no omite la confirmación de entrega', () => {
    const flujo = crearFlujo();
    flujo.boton.disabled = true;
    flujo.contexto.guardarVenta();
    assert.equal(flujo.modales(), 1);
    assert.equal(flujo.solicitudes.length, 0);
    assert.equal(flujo.boton.disabled, false);
});

test('una oferta sin cambios de precio también solicita la entrega', () => {
    const flujo = crearFlujo();
    flujo.contexto.expoConfig = null;
    flujo.enviar();
    assert.equal(flujo.modales(), 1);
    assert.equal(flujo.solicitudes.length, 0);
});

test('la protección de entrega no modifica el guardado de otros tipos de venta', () => {
    const flujo = crearFlujo();
    flujo.contexto.codigoActual = 'corporativa';
    flujo.contexto.guardarVenta();
    assert.equal(flujo.modales(), 0);
    assert.equal(flujo.solicitudes.length, 1);
});
