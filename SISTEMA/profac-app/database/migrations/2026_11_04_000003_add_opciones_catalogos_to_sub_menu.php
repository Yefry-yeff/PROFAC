<?php

use App\Models\SubMenu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Segmenta los catálogos en opciones asignables por rol o usuario.
 * Las asignaciones replican lo que cada rol podía hacer antes (algunas
 * acciones estaban fijas por rol en código), para que nadie pierda acceso.
 */
return new class extends Migration
{
    /** url => [nombre, icono, roles a los que estaba limitada (null = todos los del catálogo)] */
    private function opcionesProductos(): array
    {
        return [
            SubMenu::OPCION_PRODUCTOS_VER_DETALLE => ['Ver detalle', 'fa-solid fa-eye', null],
            SubMenu::OPCION_PRODUCTOS_EDITAR => ['Editar producto', 'fa-solid fa-pencil', null],
            SubMenu::OPCION_PRODUCTOS_SUBIR_FOTO => ['Subir fotografía', 'fa-solid fa-camera', null],
            SubMenu::OPCION_PRODUCTOS_CREAR => ['Crear nuevo producto', 'fa-solid fa-plus', [1]],
            SubMenu::OPCION_PRODUCTOS_CARGA_MASIVA => ['Carga masiva', 'fa-solid fa-upload', [1]],
            SubMenu::OPCION_PRODUCTOS_EXPORTAR => ['Exportar Excel', 'fa-solid fa-file-excel', [1, 7]],
            SubMenu::OPCION_PRODUCTOS_INACTIVAR => ['Inactivar / activar', 'fa-solid fa-ban', null],
            SubMenu::OPCION_PRODUCTOS_UNIDADES_VENTA => ['Editar unidades de medida para la venta', 'fa-solid fa-scale-balanced', [1, 5]],
        ];
    }

    private function opcionesApoyo(): array
    {
        return [
            // Nueva capacidad: no se asigna a nadie; se otorga desde Roles/Usuarios.
            SubMenu::OPCION_APOYO_SUBIR_FOTO => ['Subir fotografía', 'fa-solid fa-camera', []],
        ];
    }

    public function up(): void
    {
        $this->crearOpciones(SubMenu::URL_CATALOGO_PRODUCTOS, $this->opcionesProductos());
        $this->crearOpciones(SubMenu::URL_CATALOGO_APOYO, $this->opcionesApoyo());
    }

    public function down(): void
    {
        $urls = array_merge(array_keys($this->opcionesProductos()), array_keys($this->opcionesApoyo()));
        $ids = DB::table('sub_menu')->whereIn('url', $urls)->pluck('id');
        DB::table('usuario_submenu')->whereIn('sub_menu_id', $ids)->delete();
        DB::table('rol_submenu')->whereIn('sub_menu_id', $ids)->delete();
        DB::table('sub_menu')->whereIn('id', $ids)->delete();
    }

    private function crearOpciones(string $urlPadre, array $opciones): void
    {
        $padre = DB::table('sub_menu')->where('url', $urlPadre)->first();
        if (!$padre) {
            return;
        }

        $ahora = now();
        $rolesPadre = DB::table('rol_submenu')->where('sub_menu_id', $padre->id)->pluck('rol_id')->map('intval')->all();
        $directos = DB::table('usuario_submenu as us')
            ->join('users as u', 'u.id', '=', 'us.usuario_id')
            ->where('us.sub_menu_id', $padre->id)
            ->get(['us.usuario_id', 'us.permitido', 'u.rol_id']);

        $orden = 1;
        foreach ($opciones as $url => [$nombre, $icono, $rolesLimitados]) {
            if (DB::table('sub_menu')->where('url', $url)->exists()) {
                $orden++;
                continue;
            }

            $opcionId = DB::table('sub_menu')->insertGetId([
                'url' => $url,
                'nombre' => $nombre,
                'menu_id' => $padre->menu_id,
                'padre_id' => $padre->id,
                'orden' => $orden++,
                'estado_id' => 1,
                'icono' => $icono,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);

            $roles = $rolesLimitados === null ? $rolesPadre : array_intersect($rolesPadre, $rolesLimitados);
            DB::table('rol_submenu')->insertOrIgnore(array_map(fn ($rolId) => [
                'rol_id' => $rolId,
                'sub_menu_id' => $opcionId,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ], array_values($roles)));

            // Las excepciones por usuario del catálogo se heredan en sus opciones.
            $filas = [];
            foreach ($directos as $directo) {
                $permitido = (bool) $directo->permitido
                    && ($rolesLimitados === null || in_array((int) $directo->rol_id, $rolesLimitados, true));
                if (!$directo->permitido || $permitido) {
                    $filas[] = [
                        'usuario_id' => $directo->usuario_id,
                        'sub_menu_id' => $opcionId,
                        'permitido' => $permitido ? 1 : 0,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ];
                }
            }
            DB::table('usuario_submenu')->insertOrIgnore($filas);
        }
    }
};
