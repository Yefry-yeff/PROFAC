<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\SubMenu;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds - Sistema completo de menús dinámicos
     * Migración completa de todos los menús del sistema original
     *
     * @return void
     */
    public function run()
    {
        // Limpiar tablas
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('rol_submenu')->truncate();
        DB::table('sub_menu')->truncate();
        DB::table('menu')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Array completo de menús con sus submenús
        // NOTA: Dashboard no está incluido aquí porque es un botón estático visible para todos los roles
        $menusData = [
            // MENÚ 1: Usuarios (Solo Admin)
            [
                'nombre' => 'Usuarios',
                'icono' => 'fa-solid fa-user',
                'orden' => 1,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Lista de Usuarios', 'url' => 'usuarios', 'icono' => 'fa fa-list', 'orden' => 1, 'roles' => [1]],
                    ['nombre' => 'Gestión de Menús', 'url' => 'menu/gestion', 'icono' => 'fa fa-bars', 'orden' => 2, 'roles' => [1]],
                ]
            ],

            // MENÚ 2: Escalas de Precios (Solo Admin)
            [
                'nombre' => 'Escalas de Precios',
                'icono' => 'fa-solid fa-tags',
                'orden' => 2,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Escalas de Precio', 'url' => 'escalas', 'icono' => 'fa fa-list', 'orden' => 1, 'roles' => [1]],
                    ['nombre' => 'Categorias de Precios', 'url' => 'categorias/escalas', 'icono' => 'fa fa-list', 'orden' => 2, 'roles' => [1]],
                    ['nombre' => 'Listado de Escalas', 'url' => 'escalas/listado', 'icono' => 'fa fa-list', 'orden' => 3, 'roles' => [1]],
                ]
            ],

            // MENÚ 3: Escalas de Comisiones (Solo Admin)
            [
                'nombre' => 'Escalas de Comisiones',
                'icono' => 'fa-solid fa-magnifying-glass-dollar',
                'orden' => 3,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Escalas de Comisión', 'url' => 'comisiones/escala/index', 'icono' => 'fa fa-list', 'orden' => 1, 'roles' => [1]],
                ]
            ],

            // MENÚ 4: Ventas Clientes A (Vendedores: 2, Facturadores: 3, Aux Admin: 5, 7, RRHH: 8, Admin: 1)
            [
                'nombre' => 'Ventas Clientes A',
                'icono' => 'fa-solid fa-file-invoice',
                'orden' => 4,
                'estado_id' => 1,
                'submenus' => [
                    // Vendedores (rol 2)
                    ['nombre' => 'Cotización Clientes A', 'url' => 'proforma/cotizacion/2', 'icono' => 'fa fa-file', 'orden' => 1, 'roles' => [1, 2, 3]],
                    ['nombre' => 'Listado Cotizaciones Clientes A', 'url' => 'cotizacion/listado/estatal', 'icono' => 'fa fa-list', 'orden' => 2, 'roles' => [1, 2, 3]],
                    
                    // Facturadores (rol 3)
                    ['nombre' => 'Facturación Clientes A', 'url' => 'ventas/estatal', 'icono' => 'fa fa-file-invoice', 'orden' => 3, 'roles' => [1, 3]],
                    ['nombre' => 'Facturación SR/Clientes A', 'url' => 'ventas/sin/restriccion/gobierno', 'icono' => 'fa fa-file-invoice', 'orden' => 4, 'roles' => [1, 3]],
                    ['nombre' => 'Listado Facturas Clientes A', 'url' => 'facturas/estatal', 'icono' => 'fa fa-list', 'orden' => 5, 'roles' => [1, 3, 5, 7, 8]],
                    ['nombre' => 'Facturas Anuladas Clientes A', 'url' => 'ventas/anulado/estatal', 'icono' => 'fa fa-ban', 'orden' => 6, 'roles' => [1, 3, 5, 7, 8]],
                    ['nombre' => 'Orden de Compra Clientes A', 'url' => 'estatal/ordenes', 'icono' => 'fa fa-shopping-cart', 'orden' => 7, 'roles' => [1, 3]],
                ]
            ],

            // MENÚ 5: Ventas Clientes B (Vendedores: 2, Facturadores: 3, Aux Admin: 5, 7, RRHH: 8, Admin: 1)
            [
                'nombre' => 'Ventas Clientes B',
                'icono' => 'fa-solid fa-file-invoice',
                'orden' => 5,
                'estado_id' => 1,
                'submenus' => [
                    // Vendedores (rol 2)
                    ['nombre' => 'Cotización Clientes B', 'url' => 'proforma/cotizacion/1', 'icono' => 'fa fa-file', 'orden' => 1, 'roles' => [1, 2, 3]],
                    ['nombre' => 'Listado Cotizaciones Clientes B', 'url' => 'cotizacion/listado/corporativo', 'icono' => 'fa fa-list', 'orden' => 2, 'roles' => [1, 2, 3]],
                    
                    // Facturadores (rol 3)
                    ['nombre' => 'Facturación Clientes B', 'url' => 'ventas/coporativo', 'icono' => 'fa fa-file-invoice', 'orden' => 3, 'roles' => [1, 3]],
                    ['nombre' => 'Facturación SR/P Clientes B', 'url' => 'ventas/sin/restriccion/precio', 'icono' => 'fa fa-file-invoice', 'orden' => 4, 'roles' => [1, 3]],
                    ['nombre' => 'Listado Facturas Clientes B', 'url' => 'facturas/corporativo/lista', 'icono' => 'fa fa-list', 'orden' => 5, 'roles' => [1, 3, 5, 7, 8]],
                    ['nombre' => 'Facturas Anuladas Clientes B', 'url' => 'ventas/anulado/corporativo', 'icono' => 'fa fa-ban', 'orden' => 6, 'roles' => [1, 3, 5, 7, 8]],
                    ['nombre' => 'Orden de Compra Clientes B', 'url' => 'ventas/coorporativo/orden/compra', 'icono' => 'fa fa-shopping-cart', 'orden' => 7, 'roles' => [1, 3]],
                ]
            ],

            // MENÚ 6: Ventas Exoneradas (Solo Facturadores: 3 y Admin: 1)
            [
                'nombre' => 'Ventas Exoneradas',
                'icono' => 'fa-solid fa-file-invoice',
                'orden' => 6,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Facturación Exonerada', 'url' => 'ventas/exonerado/factura', 'icono' => 'fa fa-file-invoice', 'orden' => 1, 'roles' => [1, 3]],
                    ['nombre' => 'Listado Facturas Exoneradas', 'url' => 'exonerado/ventas/lista', 'icono' => 'fa fa-list', 'orden' => 2, 'roles' => [1, 3]],
                    ['nombre' => 'Facturas Anuladas Exoneradas', 'url' => 'ventas/anulado/exonerado', 'icono' => 'fa fa-ban', 'orden' => 3, 'roles' => [1, 3]],
                    ['nombre' => 'Registro Exonerado', 'url' => 'estatal/exonerado', 'icono' => 'fa fa-plus', 'orden' => 4, 'roles' => [1, 3]],
                ]
            ],

            // MENÚ 7: Comisiones (Vendedores: 2, RRHH: 8, Admin: 1)
            [
                'nombre' => 'Comisiones',
                'icono' => 'fa-solid fa-magnifying-glass-dollar',
                'orden' => 7,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Comisiones Colaborador', 'url' => 'comisiones/vendedor', 'icono' => 'fa fa-dollar', 'orden' => 1, 'roles' => [1, 2, 8]],
                    ['nombre' => 'Gestión Inicial Comisiones', 'url' => 'comisiones/gestion', 'icono' => 'fa fa-cogs', 'orden' => 2, 'roles' => [1, 8]],
                    ['nombre' => 'Gestión de Comisiones', 'url' => 'comisiones', 'icono' => 'fa fa-cogs', 'orden' => 3, 'roles' => [1, 8]],
                    ['nombre' => 'Histórico de Comisiones', 'url' => 'comisiones/historico', 'icono' => 'fa fa-history', 'orden' => 4, 'roles' => [1, 8]],
                ]
            ],

            // MENÚ 8: Clientes (Contabilidad: 4, Admin: 1)
            [
                'nombre' => 'Clientes',
                'icono' => 'fa-solid fa-users',
                'orden' => 8,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Registrar Cliente', 'url' => 'clientes', 'icono' => 'fa fa-user-plus', 'orden' => 1, 'roles' => [1, 4]],
                ]
            ],

            // MENÚ 9: Bancos (Contabilidad: 4, Admin: 1)
            [
                'nombre' => 'Bancos',
                'icono' => 'fa-solid fa-building-columns',
                'orden' => 9,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Gestionar Bancos', 'url' => 'banco/bancos', 'icono' => 'fa fa-university', 'orden' => 1, 'roles' => [1, 4]],
                ]
            ],

            // MENÚ 10: Boleta de Compra (Contabilidad: 4, Aux Admin: 5, RRHH: 8, Admin: 1)
            [
                'nombre' => 'Boleta de Compra',
                'icono' => 'fa-solid fa-list-check',
                'orden' => 10,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Gestión de Boleta', 'url' => 'https://cadss.hn/boleta/blta_listar_boletas.php', 'icono' => 'fa fa-list', 'orden' => 1, 'roles' => [1, 4, 5, 8]],
                ]
            ],

            // MENÚ 11: Notas de Crédito (Contabilidad: 4, Admin: 1)
            [
                'nombre' => 'Notas de Crédito',
                'icono' => 'fa-solid fa-arrow-right-arrow-left',
                'orden' => 11,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Crear Devolución', 'url' => 'nota/credito', 'icono' => 'fa fa-plus', 'orden' => 1, 'roles' => [1, 4]],
                    ['nombre' => 'Notas Crédito Clientes A', 'url' => 'nota/credito/listado', 'icono' => 'fa fa-list', 'orden' => 2, 'roles' => [1, 4]],
                    ['nombre' => 'Notas Crédito Clientes B', 'url' => 'nota/credito/gobierno', 'icono' => 'fa fa-list', 'orden' => 3, 'roles' => [1, 4]],
                    ['nombre' => 'Motivo Nota de Crédito', 'url' => 'ventas/motivo_credito', 'icono' => 'fa fa-list-alt', 'orden' => 4, 'roles' => [1, 4]],
                ]
            ],

            // MENÚ 12: Notas de Débito (Contabilidad: 4, RRHH: 8, Admin: 1)
            [
                'nombre' => 'Notas de Débito',
                'icono' => 'fa-solid fa-file-invoice',
                'orden' => 12,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Gestión Notas Débito', 'url' => 'debito', 'icono' => 'fa fa-cogs', 'orden' => 1, 'roles' => [1, 4, 8]],
                    ['nombre' => 'Notas Débito Clientes A', 'url' => 'nota/debito/lista/gobierno', 'icono' => 'fa fa-list', 'orden' => 2, 'roles' => [1, 4, 8]],
                    ['nombre' => 'Notas Débito Clientes B', 'url' => 'nota/debito/lista', 'icono' => 'fa fa-list', 'orden' => 3, 'roles' => [1, 4, 8]],
                ]
            ],

            // MENÚ 13: Cuentas por Cobrar (Contabilidad: 4, Admin: 1)
            [
                'nombre' => 'Cuentas por Cobrar',
                'icono' => 'fa-solid fa-magnifying-glass-dollar',
                'orden' => 13,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Listado de Facturas CxC', 'url' => 'cuentas/por/cobrar/listado', 'icono' => 'fa fa-list', 'orden' => 1, 'roles' => [1, 4]],
                    ['nombre' => 'Cuentas Por Cobrar', 'url' => 'ventas/cuentas_por_cobrar', 'icono' => 'fa fa-dollar', 'orden' => 2, 'roles' => [1, 4]],
                    ['nombre' => 'Aplicación de Pagos', 'url' => 'cuentas_por_cobrar/pagos', 'icono' => 'fa fa-money', 'orden' => 3, 'roles' => [1, 4]],
                ]
            ],

            // MENÚ 14: Cierre Diario (Contabilidad: 4, RRHH: 8, Admin: 1)
            [
                'nombre' => 'Cierre Diario',
                'icono' => 'fa-solid fa-calendar-check',
                'orden' => 14,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Detalle de Cierre', 'url' => 'cierre/caja', 'icono' => 'fa fa-calculator', 'orden' => 1, 'roles' => [1, 4, 8]],
                    ['nombre' => 'Histórico de Cierre', 'url' => 'cierre/historico', 'icono' => 'fa fa-history', 'orden' => 2, 'roles' => [1, 4, 8]],
                ]
            ],

            // MENÚ 15: Bodega (Aux Admin: 5, Auditoria: 7, Admin: 1)
            [
                'nombre' => 'Bodega',
                'icono' => 'fa-solid fa-warehouse',
                'orden' => 15,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Crear Bodega', 'url' => 'bodega', 'icono' => 'fa fa-plus', 'orden' => 1, 'roles' => [1, 5, 7]],
                    ['nombre' => 'Editar Bodega', 'url' => 'bodega/editar/screen', 'icono' => 'fa fa-edit', 'orden' => 2, 'roles' => [1, 5, 7]],
                ]
            ],

            // MENÚ 16: Proveedores (Aux Admin: 5, Auditoria: 7, Admin: 1)
            [
                'nombre' => 'Proveedores',
                'icono' => 'fa-solid fa-dolly',
                'orden' => 16,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Registrar Proveedor', 'url' => 'proveedores', 'icono' => 'fa fa-user-plus', 'orden' => 1, 'roles' => [1, 5, 7]],
                    ['nombre' => 'Crear Retenciones', 'url' => 'inventario/retenciones', 'icono' => 'fa fa-percent', 'orden' => 2, 'roles' => [1, 5, 7]],
                ]
            ],

            // MENÚ 17: CAI - Configuración (Aux Admin: 5, Admin: 1)
            [
                'nombre' => 'CAI - Configuración',
                'icono' => 'fa-solid fa-cog',
                'orden' => 17,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Gestionar CAI', 'url' => 'ventas/cai', 'icono' => 'fa fa-cog', 'orden' => 1, 'roles' => [1, 5]],
                ]
            ],

            // MENÚ 18: Inventario (Vendedores: 2, Facturadores: 3, Aux Admin: 5, Auditoria: 7, Admin: 1)
            [
                'nombre' => 'Inventario',
                'icono' => 'fa-solid fa-cubes',
                'orden' => 18,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Catálogo de Productos', 'url' => 'producto/registro', 'icono' => 'fa fa-book', 'orden' => 1, 'roles' => [1, 2, 3, 5, 7]],
                    ['nombre' => 'Marcas de Productos', 'url' => 'marca/producto', 'icono' => 'fa fa-tag', 'orden' => 2, 'roles' => [1, 5, 7]],
                    ['nombre' => 'Unidades de Medida', 'url' => 'inventario/unidades/medida', 'icono' => 'fa fa-balance-scale', 'orden' => 3, 'roles' => [1, 5, 7]],
                    ['nombre' => 'Comprar Producto', 'url' => 'producto/compra', 'icono' => 'fa fa-shopping-cart', 'orden' => 4, 'roles' => [1, 5, 7]],
                    ['nombre' => 'Listar Compras', 'url' => 'producto/listar/compras', 'icono' => 'fa fa-list', 'orden' => 5, 'roles' => [1, 5, 7]],
                    ['nombre' => 'Categorías', 'url' => 'categoria/categorias', 'icono' => 'fa fa-folder', 'orden' => 6, 'roles' => [1, 5, 7]],
                    ['nombre' => 'Sub-Categorías', 'url' => 'sub_categoria/sub_categorias', 'icono' => 'fa fa-folder-open', 'orden' => 7, 'roles' => [1, 5, 7]],
                ]
            ],

            // MENÚ 19: Traslado de Productos (Aux Admin: 5, Auditoria: 7, Admin: 1)
            [
                'nombre' => 'Traslado de Productos',
                'icono' => 'fa-solid fa-exchange-alt',
                'orden' => 19,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Traslado de Producto', 'url' => 'inventario/translado', 'icono' => 'fa fa-exchange', 'orden' => 1, 'roles' => [1, 5, 7]],
                    ['nombre' => 'Historial de Traslados', 'url' => 'translados/historial', 'icono' => 'fa fa-history', 'orden' => 2, 'roles' => [1, 5, 7]],
                ]
            ],

            // MENÚ 20: Ajustes de Inventario (Aux Admin: 5, Auditoria: 7, Admin: 1)
            [
                'nombre' => 'Ajustes de Inventario',
                'icono' => 'fa-solid fa-box-open',
                'orden' => 20,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Realizar Ajustes', 'url' => 'inventario/ajustes', 'icono' => 'fa fa-wrench', 'orden' => 1, 'roles' => [1, 5, 7]],
                    ['nombre' => 'Ingresar Producto', 'url' => 'inventario/ajuste/ingreso', 'icono' => 'fa fa-plus', 'orden' => 2, 'roles' => [1, 5, 7]],
                    ['nombre' => 'Historial de Ajustes', 'url' => 'listado/ajustes', 'icono' => 'fa fa-history', 'orden' => 3, 'roles' => [1, 5, 7]],
                    ['nombre' => 'Motivos de Ajuste', 'url' => 'inventario/tipoajuste', 'icono' => 'fa fa-list-alt', 'orden' => 4, 'roles' => [1, 5, 7]],
                ]
            ],

            // MENÚ 21: Compras Locales (Aux Admin: 5, Auditoria: 7, RRHH: 8, Admin: 1)
            [
                'nombre' => 'Compras Locales',
                'icono' => 'fa-solid fa-shopping-bag',
                'orden' => 21,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Orden de Compra Local', 'url' => 'https://cadss.hn/orden/ordn_listar_ordenes.php', 'icono' => 'fa fa-file', 'orden' => 1, 'roles' => [1, 5, 7, 8]],
                ]
            ],

            // MENÚ 22: Declaraciones (Aux Admin: 5, Admin: 1)
            [
                'nombre' => 'Declaraciones',
                'icono' => 'fa-solid fa-clipboard-check',
                'orden' => 22,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Configuración Declaraciones', 'url' => 'ventas/Configuracion', 'icono' => 'fa fa-cog', 'orden' => 1, 'roles' => [1, 5]],
                    ['nombre' => 'Listado de Declaraciones', 'url' => 'ventas/listado/comparacion', 'icono' => 'fa fa-list', 'orden' => 2, 'roles' => [1, 5]],
                    ['nombre' => 'Seleccionar Declaraciones', 'url' => 'ventas/seleccionar', 'icono' => 'fa fa-check-square', 'orden' => 3, 'roles' => [1, 5]],
                ]
            ],

            // MENÚ 23: Cardex (Aux Admin: 5, Auditoria: 7, Admin: 1)
            [
                'nombre' => 'Cardex',
                'icono' => 'fa-solid fa-truck-fast',
                'orden' => 23,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Gestionar Cardex', 'url' => 'cardex', 'icono' => 'fa fa-clipboard', 'orden' => 1, 'roles' => [1, 5, 7]],
                    ['nombre' => 'Cardex General', 'url' => 'cardex/general', 'icono' => 'fa fa-clipboard-list', 'orden' => 2, 'roles' => [1, 5, 7]],
                ]
            ],

            // MENÚ 24: Comprobantes de Entrega (Facturadores: 3, Aux Admin: 5, Auditoria: 7, Admin: 1)
            [
                'nombre' => 'Comprobantes de Entrega',
                'icono' => 'fa-solid fa-check-to-slot',
                'orden' => 24,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Crear Comprobante', 'url' => 'comprobante/entrega', 'icono' => 'fa fa-plus', 'orden' => 1, 'roles' => [1, 5, 7]],
                    ['nombre' => 'Listado de Comprobantes', 'url' => 'comprovante/entrega/listado', 'icono' => 'fa fa-list', 'orden' => 2, 'roles' => [1, 3, 5, 7]],
                    ['nombre' => 'Comprobantes Anulados', 'url' => 'comprovante/entrega/anulados', 'icono' => 'fa fa-ban', 'orden' => 3, 'roles' => [1, 3, 5, 7]],
                ]
            ],

            // MENÚ 25: Entregas Agendadas (Aux Admin: 5, Auditoria: 7, Admin: 1)
            [
                'nombre' => 'Entregas Agendadas',
                'icono' => 'fa-solid fa-truck-medical',
                'orden' => 25,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Listado de Entregas', 'url' => 'listar/vale/entrega', 'icono' => 'fa fa-list', 'orden' => 1, 'roles' => [1, 5, 7]],
                ]
            ],

            // MENÚ 26: Vale (Aux Admin: 5, Auditoria: 7, Admin: 1)
            [
                'nombre' => 'Vale',
                'icono' => 'fa-solid fa-ticket',
                'orden' => 26,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Lista de Vales', 'url' => 'vale/restar/inventario', 'icono' => 'fa fa-list', 'orden' => 1, 'roles' => [1, 5, 7]],
                ]
            ],

            // MENÚ 27: Precios (Facturadores: 3, Aux Admin: 5, RRHH: 8, Admin: 1)
            [
                'nombre' => 'Precios',
                'icono' => 'fa-solid fa-dollar-sign',
                'orden' => 27,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Histórico de Precios', 'url' => 'ventas/historico_precios_cliente', 'icono' => 'fa fa-history', 'orden' => 1, 'roles' => [1, 3, 5, 8]],
                ]
            ],

            // MENÚ 28: Reportes (Contabilidad: 4, Aux Admin: 5, RRHH: 8, Admin: 1)
            [
                'nombre' => 'Reportes',
                'icono' => 'fa-solid fa-chart-bar',
                'orden' => 28,
                'estado_id' => 1,
                'submenus' => [
                    ['nombre' => 'Reporte de Ventas', 'url' => 'facturaDia', 'icono' => 'fa fa-chart-line', 'orden' => 1, 'roles' => [1, 4, 5, 8]],
                    ['nombre' => 'Reportes Varios', 'url' => 'reporte/reporteria', 'icono' => 'fa fa-file-alt', 'orden' => 2, 'roles' => [1, 4]],
                    ['nombre' => 'Reporte Comisiones', 'url' => 'reporte/comision', 'icono' => 'fa fa-dollar', 'orden' => 3, 'roles' => [1, 8]],
                ]
            ],
        ];

        // Insertar menús y submenús
        $totalSubmenus = 0;
        foreach ($menusData as $menuData) {
            $menu = Menu::create([
                'nombre_menu' => $menuData['nombre'],
                'icon' => $menuData['icono'],
                'orden' => $menuData['orden'],
                'estado_id' => $menuData['estado_id'],
            ]);

            foreach ($menuData['submenus'] as $submenuData) {
                $submenu = SubMenu::create([
                    'menu_id' => $menu->id,
                    'nombre' => $submenuData['nombre'],
                    'url' => $submenuData['url'],
                    'icono' => $submenuData['icono'],
                    'orden' => $submenuData['orden'],
                    'estado_id' => 1,
                ]);

                // Asignar roles al submenu
                $submenu->roles()->attach($submenuData['roles']);
                $totalSubmenus++;
            }
        }

        $this->command->info('✓ Sistema de menús completo creado exitosamente');
        $this->command->info('✓ Total de menús: ' . count($menusData));
        $this->command->info('✓ Total de submenús: ' . $totalSubmenus);
        $this->command->info('');
        $this->command->info('NOTA: El Dashboard es un botón estático visible para todos los roles');
        $this->command->info('');
        $this->command->info('Roles configurados:');
        $this->command->info('  1 = Administrador');
        $this->command->info('  2 = Vendedores');
        $this->command->info('  3 = Facturadores');
        $this->command->info('  4 = Contabilidad');
        $this->command->info('  5 = Auxiliar Administrativo');
        $this->command->info('  7 = Auditoría y Logística');
        $this->command->info('  8 = RRHH');
    }
}
