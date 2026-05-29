<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AlertaRotacionConfig extends Model
{
    protected $table = 'alerta_rotacion_config';

    protected $fillable = [
        'nombre',
        'tipo',
        'parametro_dias',
        'parametro_umbral',
        'rol_id',
        'area_id',
        'icono',
        'color',
        'prioridad',
        'activo',
    ];

    protected $casts = [
        'activo'           => 'boolean',
        'parametro_dias'   => 'integer',
        'parametro_umbral' => 'float',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Etiqueta legible de la prioridad para mostrar en UI.
     */
    public function getPrioridadLabelAttribute(): string
    {
        return match ($this->prioridad) {
            'critica'     => 'Crítica',
            'alta'        => 'Alta',
            'media'       => 'Media',
            'informativa' => 'Informativa',
            default       => ucfirst($this->prioridad),
        };
    }

    /**
     * Color CSS del badge de prioridad.
     */
    public function getPrioridadColorAttribute(): string
    {
        return match ($this->prioridad) {
            'critica'     => '#ef4444',
            'alta'        => '#f97316',
            'media'       => '#f59e0b',
            'informativa' => '#6366f1',
            default       => '#6b7280',
        };
    }

    /**
     * Descripción legible del parámetro según el tipo de alerta.
     */
    public function getDescripcionParametroAttribute(): string
    {
        return match ($this->tipo) {
            'recuperacion_proxima' => "Aviso con {$this->parametro_dias} día(s) de anticipación",
            'recuperacion_vencida' => 'Alerta cuando la fecha límite ya fue superada',
            'sin_ventas'           => "Dispara si no hay ventas en {$this->parametro_dias} días",
            'baja_rotacion'        => "Dispara si ventas 60d < {$this->parametro_umbral} unidades",
            'sobreinventario'      => "Dispara si cobertura > {$this->parametro_umbral} meses",
            'incremento_demanda'   => "Dispara si demanda crece ≥ {$this->parametro_umbral}%",
            default                => '',
        };
    }

    /**
     * Resuelve los usuarios que deben recibir esta alerta.
     */
    public function resolverUsuariosDestino(): \Illuminate\Database\Eloquent\Collection
    {
        if ($this->rol_id) {
            return User::where('rol_id', $this->rol_id)
                ->where('estado_id', 1)
                ->get();
        }

        if ($this->area_id) {
            return User::whereHas('rol', fn ($q) => $q->where('area_id', $this->area_id))
                ->where('estado_id', 1)
                ->get();
        }

        return collect();
    }

    // ─── Consulta de productos afectados (compartida por Job y Reporte) ───────

    /**
     * Ejecuta la consulta SQL del tipo de esta regla y devuelve los productos
     * actualmente afectados.
     */
    public function getProductosAfectados(): \Illuminate\Support\Collection
    {
        return match ($this->tipo) {
            'recuperacion_proxima' => $this->queryRecuperacionProxima(),
            'recuperacion_vencida' => $this->queryRecuperacionVencida(),
            'sin_ventas'           => $this->querySinVentas(),
            'baja_rotacion'        => $this->queryBajaRotacion(),
            'sobreinventario'      => $this->querySobreinventario(),
            'incremento_demanda'   => $this->queryIncrementoDemanda(),
            default                => collect(),
        };
    }

    // ─── Helper: columnas comunes de producto ───────────────────────────────
    private function extraSelect(): string
    {
        return ',
                 p.codigo_barra,
                 p.precio_base,
                 p.ultimo_costo_compra,
                 p.costo_promedio,
                 COALESCE(sc.descripcion, \'Sin categoría\') AS sub_categoria';
    }

    private function withProductJoins($query)
    {
        return $query->leftJoin('sub_categoria as sc', 'sc.id', '=', 'p.sub_categoria_id');
    }

    private function queryRecuperacionProxima(): \Illuminate\Support\Collection
    {
        $dias = (int) ($this->parametro_dias ?? 15);
        $q = DB::table('producto as p')
            ->join('compra_has_producto as chp', 'chp.producto_id', '=', 'p.id')
            ->join('compra as c', 'c.id', '=', 'chp.compra_id')
            ->leftJoin('recibido_bodega as rb', 'rb.producto_id', '=', 'p.id');
        $this->withProductJoins($q);
        return $q
            ->where('p.estado_producto_id', 1)
            ->whereNotNull('p.tiempo_recuperacion_meses')
            ->where('p.tiempo_recuperacion_meses', '>', 0)
            ->selectRaw('p.id AS producto_id, p.nombre AS producto_nombre,
                 p.tiempo_recuperacion_meses,
                 MAX(c.fecha_emision) AS ultima_compra,
                 COALESCE(SUM(rb.cantidad_disponible), 0) AS stock_actual,
                 DATE_ADD(MAX(c.fecha_emision), INTERVAL p.tiempo_recuperacion_meses MONTH) AS fecha_limite'
                 . $this->extraSelect())
            ->groupBy('p.id', 'p.nombre', 'p.tiempo_recuperacion_meses',
                      'p.codigo_barra', 'p.precio_base', 'p.ultimo_costo_compra', 'p.costo_promedio', 'sc.descripcion')
            ->havingRaw('fecha_limite >= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                 AND fecha_limite < DATE_ADD(CURDATE(), INTERVAL ? DAY)
                 AND stock_actual > 0', [$dias - 1, $dias + 1])
            ->get();
    }

    private function queryRecuperacionVencida(): \Illuminate\Support\Collection
    {
        $q = DB::table('producto as p')
            ->join('compra_has_producto as chp', 'chp.producto_id', '=', 'p.id')
            ->join('compra as c', 'c.id', '=', 'chp.compra_id')
            ->leftJoin('recibido_bodega as rb', 'rb.producto_id', '=', 'p.id');
        $this->withProductJoins($q);
        return $q
            ->where('p.estado_producto_id', 1)
            ->whereNotNull('p.tiempo_recuperacion_meses')
            ->where('p.tiempo_recuperacion_meses', '>', 0)
            ->selectRaw('p.id AS producto_id, p.nombre AS producto_nombre,
                 MAX(c.fecha_emision) AS ultima_compra,
                 COALESCE(SUM(rb.cantidad_disponible), 0) AS stock_actual,
                 DATE_ADD(MAX(c.fecha_emision), INTERVAL p.tiempo_recuperacion_meses MONTH) AS fecha_limite,
                 DATEDIFF(CURDATE(), DATE_ADD(MAX(c.fecha_emision), INTERVAL p.tiempo_recuperacion_meses MONTH)) AS dias_vencido'
                 . $this->extraSelect())
            ->groupBy('p.id', 'p.nombre', 'p.tiempo_recuperacion_meses',
                      'p.codigo_barra', 'p.precio_base', 'p.ultimo_costo_compra', 'p.costo_promedio', 'sc.descripcion')
            ->havingRaw('fecha_limite < CURDATE() AND stock_actual > 0')
            ->get();
    }

    private function querySinVentas(): \Illuminate\Support\Collection
    {
        $dias = (int) ($this->parametro_dias ?? 30);
        $q = DB::table('producto as p')
            ->leftJoin('recibido_bodega as rb', 'rb.producto_id', '=', 'p.id');
        $this->withProductJoins($q);
        return $q
            ->where('p.estado_producto_id', 1)
            ->selectRaw('p.id AS producto_id, p.nombre AS producto_nombre,
                 COALESCE(SUM(rb.cantidad_disponible), 0) AS stock_actual,
                 (SELECT MAX(f.fecha_emision)
                  FROM venta_has_producto vhp
                  INNER JOIN factura f ON f.id = vhp.factura_id AND f.estado_factura_id = 1
                  WHERE vhp.producto_id = p.id) AS ultima_venta'
                  . $this->extraSelect())
            ->groupBy('p.id', 'p.nombre',
                      'p.codigo_barra', 'p.precio_base', 'p.ultimo_costo_compra', 'p.costo_promedio', 'sc.descripcion')
            ->havingRaw('(ultima_venta IS NULL OR ultima_venta < DATE_SUB(CURDATE(), INTERVAL ? DAY))
                 AND stock_actual > 0', [$dias])
            ->get();
    }

    private function queryBajaRotacion(): \Illuminate\Support\Collection
    {
        $min = (float) ($this->parametro_umbral ?? 5);
        $q = DB::table('producto as p')
            ->leftJoin('recibido_bodega as rb', 'rb.producto_id', '=', 'p.id');
        $this->withProductJoins($q);
        return $q
            ->where('p.estado_producto_id', 1)
            ->selectRaw('p.id AS producto_id, p.nombre AS producto_nombre,
                 COALESCE(SUM(rb.cantidad_disponible), 0) AS stock_actual,
                 (SELECT COALESCE(SUM(vhp.cantidad), 0)
                  FROM venta_has_producto vhp
                  INNER JOIN factura f ON f.id = vhp.factura_id AND f.estado_factura_id = 1
                      AND f.fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
                  WHERE vhp.producto_id = p.id) AS ventas_60d'
                  . $this->extraSelect())
            ->groupBy('p.id', 'p.nombre',
                      'p.codigo_barra', 'p.precio_base', 'p.ultimo_costo_compra', 'p.costo_promedio', 'sc.descripcion')
            ->havingRaw('ventas_60d < ? AND stock_actual > 0', [$min])
            ->get();
    }

    private function querySobreinventario(): \Illuminate\Support\Collection
    {
        $max = (float) ($this->parametro_umbral ?? 6);
        $q = DB::table('producto as p')
            ->leftJoin('recibido_bodega as rb', 'rb.producto_id', '=', 'p.id');
        $this->withProductJoins($q);
        return $q
            ->where('p.estado_producto_id', 1)
            ->selectRaw('p.id AS producto_id, p.nombre AS producto_nombre,
                 COALESCE(SUM(rb.cantidad_disponible), 0) AS stock_actual,
                 COALESCE((SELECT COALESCE(SUM(vhp.cantidad),0)
                      FROM venta_has_producto vhp
                      INNER JOIN factura f ON f.id = vhp.factura_id AND f.estado_factura_id = 1
                          AND f.fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                      WHERE vhp.producto_id = p.id) / 3, 0) AS prom_mensual,
                 CASE
                     WHEN (SELECT COALESCE(SUM(vhp.cantidad),0)
                           FROM venta_has_producto vhp
                           INNER JOIN factura f ON f.id = vhp.factura_id AND f.estado_factura_id = 1
                               AND f.fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                           WHERE vhp.producto_id = p.id) > 0
                     THEN COALESCE(SUM(rb.cantidad_disponible),0) /
                          ((SELECT COALESCE(SUM(vhp.cantidad),0)
                            FROM venta_has_producto vhp
                            INNER JOIN factura f ON f.id = vhp.factura_id AND f.estado_factura_id = 1
                                AND f.fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                            WHERE vhp.producto_id = p.id) / 3)
                     ELSE 9999
                 END AS cobertura_meses'
                 . $this->extraSelect())
            ->groupBy('p.id', 'p.nombre',
                      'p.codigo_barra', 'p.precio_base', 'p.ultimo_costo_compra', 'p.costo_promedio', 'sc.descripcion')
            ->havingRaw('cobertura_meses > ? AND stock_actual > 0', [$max])
            ->get();
    }

    private function queryIncrementoDemanda(): \Illuminate\Support\Collection
    {
        $min = (float) ($this->parametro_umbral ?? 20);
        $q = DB::table('producto as p');
        $this->withProductJoins($q);
        return $q
            ->where('p.estado_producto_id', 1)
            ->selectRaw('p.id AS producto_id, p.nombre AS producto_nombre,
                 (SELECT COALESCE(SUM(vhp.cantidad),0)
                  FROM venta_has_producto vhp
                  INNER JOIN factura f ON f.id = vhp.factura_id AND f.estado_factura_id = 1
                      AND f.fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                  WHERE vhp.producto_id = p.id) AS ventas_30d,
                 (SELECT COALESCE(SUM(vhp.cantidad),0)
                  FROM venta_has_producto vhp
                  INNER JOIN factura f ON f.id = vhp.factura_id AND f.estado_factura_id = 1
                      AND f.fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
                      AND f.fecha_emision < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                  WHERE vhp.producto_id = p.id) AS ventas_30d_ant,
                 CASE
                     WHEN (SELECT COALESCE(SUM(vhp.cantidad),0)
                           FROM venta_has_producto vhp
                           INNER JOIN factura f ON f.id = vhp.factura_id AND f.estado_factura_id = 1
                               AND f.fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
                               AND f.fecha_emision < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                           WHERE vhp.producto_id = p.id) > 0
                     THEN ((SELECT COALESCE(SUM(vhp.cantidad),0)
                            FROM venta_has_producto vhp
                            INNER JOIN factura f ON f.id = vhp.factura_id AND f.estado_factura_id = 1
                                AND f.fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                            WHERE vhp.producto_id = p.id)
                           - (SELECT COALESCE(SUM(vhp.cantidad),0)
                              FROM venta_has_producto vhp
                              INNER JOIN factura f ON f.id = vhp.factura_id AND f.estado_factura_id = 1
                                  AND f.fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
                                  AND f.fecha_emision < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                              WHERE vhp.producto_id = p.id))
                          / (SELECT COALESCE(SUM(vhp.cantidad),0)
                             FROM venta_has_producto vhp
                             INNER JOIN factura f ON f.id = vhp.factura_id AND f.estado_factura_id = 1
                                 AND f.fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
                                 AND f.fecha_emision < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                             WHERE vhp.producto_id = p.id) * 100
                     ELSE 0
                 END AS pct_crecimiento'
                 . $this->extraSelect())
            ->groupBy('p.id', 'p.nombre', 'p.codigo_barra', 'p.precio_base', 'p.ultimo_costo_compra', 'p.costo_promedio', 'sc.descripcion')
            ->havingRaw('pct_crecimiento >= ? AND ventas_30d_ant > 0', [$min])
            ->get();
    }
}
