<?php

namespace App\Http\Livewire\LogisticaDeEntregas;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Logistica\ZoneGroup;
use App\Models\Logistica\ZoneGroupDetail;
use App\Models\Logistica\ZoneGroupAudit;
use DataTables;

class AgrupacionesDeEntregas extends Component
{
    // Propiedades del componente
    public $titulo = 'Agrupaciones de Entregas';

    // Fecha de corte usada por el resto del módulo de Logística de Entregas
    // (misma regla que DistribucionEntrega para determinar facturas "pendientes")
    private const FECHA_CORTE_FACTURAS = '2026-05-16';

    /**
     * Inicializar el componente
     */
    public function mount()
    {
        // Inicialización si es necesaria
    }

    /**
     * Renderizar la vista
     */
    public function render()
    {
        return view('livewire.logisticadeentregas.agrupacionesdeentregas');
    }

    /* =========================================================================
     |  Helpers SQL reutilizables
     * ========================================================================= */

    /**
     * Condición SQL que define una "factura pendiente de distribución".
     * Debe coincidir exactamente con la regla usada en DistribucionEntrega
     * (autocompletadoFacturas / autocompletadoClientes / obtenerFacturasPorClienteId)
     * para que el conteo de "pendientes por zona" sea consistente con el resto del módulo.
     * Requiere que la tabla factura esté aliasada como "f" en el query.
     */
    private function condicionFacturaPendiente(): string
    {
        $fecha = self::FECHA_CORTE_FACTURAS;

        return "
            f.estado_factura_id IN (1, 2)
            AND f.estado_venta_id = 1
            AND f.fecha_emision >= '{$fecha}'
            AND NOT EXISTS (
                SELECT 1 FROM distribuciones_entrega_facturas def
                WHERE def.factura_id = f.id
                AND def.estado_entrega = 'entregado'
            )
            AND NOT EXISTS (
                SELECT 1 FROM distribuciones_entrega_facturas def
                INNER JOIN distribuciones_entrega de ON def.distribucion_entrega_id = de.id
                WHERE def.factura_id = f.id
                AND de.estado_id IN (1, 2)
                AND def.estado_entrega != 'anulada'
            )
        ";
    }

    /**
     * Subconsulta que resuelve el zone_group_id de una factura aliasada como "f"
     * a partir del departamento/municipio que el GESTOR DE ENTREGA registró al
     * "tratar" la factura (tabla factura_tratamiento_entrega), y ya NO desde el
     * municipio del cliente. Prioridad: coincidencia por municipio específico
     * primero, luego coincidencia por "todo el departamento". Si la factura aún
     * no ha sido tratada, o no hay coincidencia con NINGUNA zona, devuelve NULL
     * (cae en el grupo "Sin clasificar"). Se usa ÚNICAMENTE para detectar
     * facturas sin ninguna zona asociada; para el conteo por zona individual
     * usar {@see condicionFacturaEnZona()}, ya que una factura puede pertenecer
     * a más de una zona a la vez (p. ej. una zona de municipio específico y
     * otra de "todo el departamento" que también lo cubre).
     */
    private function subconsultaZonaResuelta(): string
    {
        return "
            COALESCE(
                (SELECT zgd1.zone_group_id
                 FROM factura_tratamiento_entrega fte1
                 INNER JOIN zone_group_details zgd1 ON zgd1.status = 1 AND zgd1.municipality_id = fte1.municipality_id
                 INNER JOIN zone_groups zg1 ON zg1.id = zgd1.zone_group_id AND zg1.status = 1
                 WHERE fte1.factura_id = f.id
                 ORDER BY zgd1.id LIMIT 1),
                (SELECT zgd2.zone_group_id
                 FROM factura_tratamiento_entrega fte2
                 INNER JOIN zone_group_details zgd2 ON zgd2.status = 1 AND zgd2.municipality_id IS NULL AND zgd2.department_id = fte2.department_id
                 INNER JOIN zone_groups zg2 ON zg2.id = zgd2.zone_group_id AND zg2.status = 1
                 WHERE fte2.factura_id = f.id
                 ORDER BY zgd2.id LIMIT 1)
            )
        ";
    }

    /**
     * Condición EXISTS que determina si la factura aliasada como "f" pertenece
     * a la zona indicada en $zoneGroupIdExpr (puede ser un literal correlacionado
     * como "zg.id" o un marcador "?"). A diferencia de subconsultaZonaResuelta(),
     * esta condición NO es excluyente: una factura puede cumplir esta condición
     * para varias zonas simultáneamente (p. ej. si una zona cubre el municipio
     * específico tratado y otra zona cubre "todo el departamento").
     */
    private function condicionFacturaEnZona(string $zoneGroupIdExpr): string
    {
        return "
            EXISTS (
                SELECT 1
                FROM factura_tratamiento_entrega fte
                INNER JOIN zone_group_details zgd ON zgd.status = 1
                    AND zgd.zone_group_id = {$zoneGroupIdExpr}
                    AND zgd.department_id = fte.department_id
                    AND (zgd.municipality_id = fte.municipality_id OR zgd.municipality_id IS NULL)
                INNER JOIN zone_groups zgchk ON zgchk.id = zgd.zone_group_id AND zgchk.status = 1
                WHERE fte.factura_id = f.id
            )
        ";
    }

    /**
     * Registra una entrada en la bitácora de auditoría de zonas.
     */
    private function registrarAuditoria($zoneGroupId, string $action, $oldData, $newData): void
    {
        ZoneGroupAudit::create([
            'zone_group_id' => $zoneGroupId,
            'action' => $action,
            'old_data' => $oldData,
            'new_data' => $newData,
            'user_id' => Auth::id(),
        ]);
    }

    /* =========================================================================
     |  CRUD de Zonas (zone_groups)
     * ========================================================================= */

    /**
     * Listado (Yajra DataTables) de zonas con resumen de departamentos/municipios
     * asignados y cantidad de facturas pendientes.
     */
    public function listarZonas()
    {
        try {
            $pendiente = $this->condicionFacturaPendiente();
            $facturaEnZona = $this->condicionFacturaEnZona('zg.id');

            $zonas = DB::select("
                SELECT
                    zg.id,
                    zg.name,
                    zg.description,
                    zg.orden,
                    zg.status,
                    zg.created_at,
                    (SELECT COUNT(*) FROM zone_group_details zgd WHERE zgd.zone_group_id = zg.id AND zgd.status = 1) AS total_detalles,
                    (
                        SELECT COUNT(*)
                        FROM factura f
                        INNER JOIN cliente c ON c.id = f.cliente_id
                        WHERE {$pendiente}
                        AND {$facturaEnZona}
                    ) AS facturas_pendientes
                FROM zone_groups zg
                ORDER BY zg.orden ASC, zg.name ASC
            ");

            return Datatables::of($zonas)
                ->addColumn('detalles', function ($z) {
                    return "<span class='badge badge-info'>{$z->total_detalles} zona(s) geográfica(s)</span>";
                })
                ->addColumn('pendientes', function ($z) {
                    $color = $z->facturas_pendientes > 0 ? 'warning' : 'secondary';
                    return "<span class='badge badge-{$color}'>{$z->facturas_pendientes} factura(s)</span>";
                })
                ->addColumn('estado', function ($z) {
                    return $z->status == 1
                        ? '<span class="badge badge-success">ACTIVA</span>'
                        : '<span class="badge badge-danger">INACTIVA</span>';
                })
                ->addColumn('opciones', function ($z) {
                    if ($z->status == 1) {
                        return '
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-warning" onclick="editarZona(' . $z->id . ')" title="Editar">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarZona(' . $z->id . ')" title="Eliminar">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        ';
                    }
                    return '<span class="badge badge-secondary">Sin acciones</span>';
                })
                ->rawColumns(['detalles', 'pendientes', 'estado', 'opciones'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al listar zonas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Devuelve todos los departamentos disponibles.
     */
    public function obtenerDepartamentos()
    {
        try {
            $departamentos = DB::select("SELECT id, nombre FROM departamento ORDER BY nombre ASC");

            return response()->json([
                'success' => true,
                'departamentos' => $departamentos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener departamentos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Devuelve los municipios de un departamento.
     */
    public function obtenerMunicipios($departamentoId)
    {
        try {
            $municipios = DB::select("
                SELECT id, nombre FROM municipio WHERE departamento_id = ? ORDER BY nombre ASC
            ", [$departamentoId]);

            return response()->json([
                'success' => true,
                'municipios' => $municipios
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener municipios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Devuelve una zona con sus detalles (departamentos/municipios) para edición.
     */
    public function obtenerZona($id)
    {
        try {
            $zona = ZoneGroup::find($id);
            if (!$zona) {
                return response()->json(['success' => false, 'mensaje' => 'Zona no encontrada'], 404);
            }

            $detalles = DB::select("
                SELECT
                    zgd.id,
                    zgd.department_id,
                    zgd.municipality_id,
                    d.nombre AS departamento,
                    m.nombre AS municipio
                FROM zone_group_details zgd
                INNER JOIN departamento d ON d.id = zgd.department_id
                LEFT JOIN municipio m ON m.id = zgd.municipality_id
                WHERE zgd.zone_group_id = ? AND zgd.status = 1
                ORDER BY d.nombre ASC, m.nombre ASC
            ", [$id]);

            return response()->json([
                'success' => true,
                'zona' => $zona,
                'detalles' => $detalles
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener la zona: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crea una nueva zona geográfica junto con sus detalles (departamentos/municipios).
     */
    public function guardarZona(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:150',
                'description' => 'nullable|string',
            ], [
                'name.required' => 'El nombre de la zona es obligatorio',
            ]);

            $detalles = json_decode($request->input('detalles', '[]'), true) ?: [];

            if (empty($detalles)) {
                return response()->json([
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Debe asignar al menos un departamento a la zona',
                ], 422);
            }

            DB::beginTransaction();

            $orden = (int) (ZoneGroup::max('orden') ?? 0) + 1;

            $zona = ZoneGroup::create([
                'name' => trim($request->input('name')),
                'description' => trim((string) $request->input('description')),
                'orden' => $orden,
                'status' => 1,
                'usr_registro' => Auth::id(),
            ]);

            foreach ($detalles as $d) {
                ZoneGroupDetail::create([
                    'zone_group_id' => $zona->id,
                    'department_id' => $d['department_id'],
                    'municipality_id' => $d['municipality_id'] ?? null,
                    'status' => 1,
                    'usr_registro' => Auth::id(),
                ]);
            }

            $this->registrarAuditoria($zona->id, 'CREATE', null, [
                'name' => $zona->name,
                'description' => $zona->description,
                'detalles' => $detalles,
            ]);

            DB::commit();

            return response()->json([
                'icon' => 'success',
                'title' => '¡Éxito!',
                'text' => 'Zona geográfica creada correctamente',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'icon' => 'error',
                'title' => 'Error de validación',
                'text' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Ha ocurrido un error al crear la zona: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza una zona geográfica existente y reemplaza sus detalles.
     */
    public function actualizarZona(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:zone_groups,id',
                'name' => 'required|string|max:150',
                'description' => 'nullable|string',
            ], [
                'name.required' => 'El nombre de la zona es obligatorio',
            ]);

            $detalles = json_decode($request->input('detalles', '[]'), true) ?: [];

            if (empty($detalles)) {
                return response()->json([
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Debe asignar al menos un departamento a la zona',
                ], 422);
            }

            $zona = ZoneGroup::findOrFail($request->input('id'));

            $detallesAnteriores = DB::select("
                SELECT department_id, municipality_id
                FROM zone_group_details
                WHERE zone_group_id = ? AND status = 1
            ", [$zona->id]);

            $datosAnteriores = [
                'name' => $zona->name,
                'description' => $zona->description,
                'detalles' => $detallesAnteriores,
            ];

            DB::beginTransaction();

            $zona->name = trim($request->input('name'));
            $zona->description = trim((string) $request->input('description'));
            $zona->usr_actualizo = Auth::id();
            $zona->save();

            // Reemplazar los detalles: se eliminan los actuales y se insertan los nuevos
            ZoneGroupDetail::where('zone_group_id', $zona->id)->delete();

            foreach ($detalles as $d) {
                ZoneGroupDetail::create([
                    'zone_group_id' => $zona->id,
                    'department_id' => $d['department_id'],
                    'municipality_id' => $d['municipality_id'] ?? null,
                    'status' => 1,
                    'usr_registro' => Auth::id(),
                ]);
            }

            $this->registrarAuditoria($zona->id, 'UPDATE', $datosAnteriores, [
                'name' => $zona->name,
                'description' => $zona->description,
                'detalles' => $detalles,
            ]);

            DB::commit();

            return response()->json([
                'icon' => 'success',
                'title' => '¡Éxito!',
                'text' => 'Zona geográfica actualizada correctamente',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'icon' => 'error',
                'title' => 'Error de validación',
                'text' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Ha ocurrido un error al actualizar la zona: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Desactiva (borrado lógico) una zona geográfica.
     */
    public function eliminarZona($id)
    {
        try {
            $zona = ZoneGroup::find($id);
            if (!$zona) {
                return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Zona no encontrada'], 404);
            }

            $datosAnteriores = [
                'name' => $zona->name,
                'description' => $zona->description,
                'status' => $zona->status,
            ];

            $zona->status = 0;
            $zona->usr_actualizo = Auth::id();
            $zona->save();

            $this->registrarAuditoria($zona->id, 'DELETE', $datosAnteriores, ['status' => 0]);

            return response()->json([
                'icon' => 'success',
                'title' => '¡Éxito!',
                'text' => 'Zona geográfica eliminada correctamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Error al eliminar la zona: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reordena las zonas (drag & drop) según el arreglo de IDs recibido.
     */
    public function reordenarZonas(Request $request)
    {
        try {
            $ids = json_decode($request->input('ids', '[]'), true) ?: [];

            DB::beginTransaction();
            foreach ($ids as $orden => $id) {
                ZoneGroup::where('id', $id)->update(['orden' => $orden + 1, 'usr_actualizo' => Auth::id()]);
            }
            DB::commit();

            return response()->json(['success' => true, 'mensaje' => 'Orden actualizado'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'mensaje' => 'Error al reordenar: ' . $e->getMessage()], 500);
        }
    }

    /* =========================================================================
     |  Endpoints usados por nueva-distribucion.blade.php (pestaña "Facturas por Zona")
     * ========================================================================= */

    /**
     * Resumen de zonas activas con cantidad de facturas pendientes por zona,
     * incluyendo el grupo "Sin clasificar" (clientes sin zona asignada).
     */
    public function resumenZonas()
    {
        try {
            $pendiente = $this->condicionFacturaPendiente();
            $facturaEnZona = $this->condicionFacturaEnZona('zg.id');
            $zonaResuelta = $this->subconsultaZonaResuelta();

            $zonas = DB::select("
                SELECT
                    zg.id,
                    zg.name,
                    zg.description,
                    (
                        SELECT COUNT(*)
                        FROM factura f
                        INNER JOIN cliente c ON c.id = f.cliente_id
                        WHERE {$pendiente}
                        AND {$facturaEnZona}
                    ) AS facturas_pendientes
                FROM zone_groups zg
                WHERE zg.status = 1
                ORDER BY zg.orden ASC, zg.name ASC
            ");

            $sinClasificar = DB::selectOne("
                SELECT COUNT(*) AS total
                FROM factura f
                INNER JOIN cliente c ON c.id = f.cliente_id
                WHERE {$pendiente}
                AND {$zonaResuelta} IS NULL
            ")->total;

            $resultado = collect($zonas)->map(function ($z) {
                return [
                    'id' => $z->id,
                    'name' => $z->name,
                    'description' => $z->description,
                    'facturas_pendientes' => (int) $z->facturas_pendientes,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'zonas' => $resultado,
                'sin_clasificar' => (int) $sinClasificar,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener el resumen de zonas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Facturas pendientes de una zona específica (o "Sin clasificar" si zona_id = 0/"sin_clasificar").
     */
    public function facturasPorZona(Request $request)
    {
        try {
            $zonaId = $request->input('zona_id');
            $pendiente = $this->condicionFacturaPendiente();
            $zonaResuelta = $this->subconsultaZonaResuelta();

            $esSinClasificar = ($zonaId === 'sin_clasificar' || $zonaId === '0' || $zonaId === 0);

            $sql = "
                SELECT
                    f.id,
                    f.cai,
                    f.numero_factura,
                    f.total,
                    f.fecha_emision,
                    c.nombre AS cliente,
                    COALESCE(m.nombre, '') AS municipio,
                    COALESCE(fte.direccion_entrega, '') AS direccion_completa,
                    COALESCE(uv.name, '') AS asesor_comercial,
                    COALESCE(g.name, '') AS gestor,
                    (SELECT COUNT(*) FROM venta_has_producto vhp WHERE vhp.factura_id = f.id) AS cantidad_productos
                FROM factura f
                INNER JOIN cliente c ON c.id = f.cliente_id
                LEFT JOIN factura_tratamiento_entrega fte ON fte.factura_id = f.id
                LEFT JOIN municipio m ON m.id = fte.municipality_id
                LEFT JOIN users uv ON uv.id = f.vendedor
                LEFT JOIN users g ON g.id = f.gestor_entrega
                WHERE {$pendiente}
            ";

            if ($esSinClasificar) {
                $sql .= " AND {$zonaResuelta} IS NULL";
                $params = [];
            } else {
                $sql .= " AND {$this->condicionFacturaEnZona('?')}";
                $params = [(int) $zonaId];
            }

            $sql .= " ORDER BY f.fecha_emision DESC, f.cai DESC LIMIT 200";

            $facturas = DB::select($sql, $params);

            return response()->json([
                'success' => true,
                'facturas' => $facturas,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener facturas de la zona: ' . $e->getMessage()
            ], 500);
        }
    }
}
