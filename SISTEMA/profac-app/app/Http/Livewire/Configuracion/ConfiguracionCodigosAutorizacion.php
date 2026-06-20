<?php

namespace App\Http\Livewire\Configuracion;

use App\Models\CatalogoEstadoCodigo;
use App\Models\ConfiguracionCodigoAutorizacion;
use App\Models\ModelCodigoAutorizacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ConfiguracionCodigosAutorizacion extends Component
{
    // ── Formulario ────────────────────────────────────────────────────────
    public int  $tiempoExpiracionMinutos = 10;
    public bool $expiracionActiva        = true;

    // ── UI ────────────────────────────────────────────────────────────────
    public string $mensajeExito = '';
    public string $mensajeError = '';

    // ── Estadísticas rápidas ──────────────────────────────────────────────
    public array $estadisticas = [];

    // ── Listado de códigos recientes ──────────────────────────────────────
    public array $codigosRecientes = [];

    public function mount(): void
    {
        $this->cargarConfiguracion();
        $this->cargarEstadisticas();
        $this->cargarCodigosRecientes();
    }

    private function cargarConfiguracion(): void
    {
        $config = ConfiguracionCodigoAutorizacion::obtener();
        $this->tiempoExpiracionMinutos = $config->tiempo_expiracion_minutos;
        $this->expiracionActiva        = (bool) $config->expiracion_activa;
    }

    private function cargarEstadisticas(): void
    {
        $this->estadisticas = DB::table('codigo_autorizacion as ca')
            ->leftJoin('catalogo_estado_codigo as ce', 'ca.estado_codigo_id', '=', 'ce.id')
            ->selectRaw('ce.nombre as estado, COUNT(*) as total')
            ->groupBy('ce.nombre')
            ->pluck('total', 'estado')
            ->toArray();
    }

    private function cargarCodigosRecientes(): void
    {
        $this->codigosRecientes = DB::table('codigo_autorizacion as ca')
            ->leftJoin('users as u', 'ca.users_id', '=', 'u.id')
            ->leftJoin('catalogo_estado_codigo as ce', 'ca.estado_codigo_id', '=', 'ce.id')
            ->select(
                'ca.id',
                'ca.codigo',
                'ca.tipo_tramite',
                'ca.flujo_id',
                'ca.fecha_expiracion',
                'ca.fecha_utilizacion',
                'ca.created_at',
                'u.name as usuario',
                'ce.nombre as estado_nombre'
            )
            ->orderByDesc('ca.created_at')
            ->limit(30)
            ->get()
            ->toArray();
    }

    public function guardarConfiguracion(): void
    {
        $this->mensajeExito = '';
        $this->mensajeError = '';

        if ($this->tiempoExpiracionMinutos < 1 || $this->tiempoExpiracionMinutos > 1440) {
            $this->mensajeError = 'El tiempo de expiración debe estar entre 1 y 1440 minutos (24 h).';
            return;
        }

        $config = ConfiguracionCodigoAutorizacion::obtener();
        $config->update([
            'tiempo_expiracion_minutos' => $this->tiempoExpiracionMinutos,
            'expiracion_activa'         => $this->expiracionActiva,
            'actualizado_por'           => Auth::id(),
        ]);

        $this->mensajeExito = 'Configuración guardada correctamente.';
        $this->cargarEstadisticas();
        $this->cargarCodigosRecientes();
    }

    /**
     * Expirar manualmente todos los códigos pendientes que ya vencieron.
     */
    public function expirarCodigosPendientes(): void
    {
        $actualizados = DB::table('codigo_autorizacion')
            ->where('estado_codigo_id', CatalogoEstadoCodigo::PENDIENTE)
            ->where('estado_id', 1)
            ->whereNotNull('fecha_expiracion')
            ->where('fecha_expiracion', '<', now())
            ->update([
                'estado_id'        => 2,
                'estado_codigo_id' => CatalogoEstadoCodigo::EXPIRADO,
                'updated_at'       => now(),
            ]);

        $this->mensajeExito = "Se expiraron {$actualizados} código(s) vencido(s).";
        $this->cargarEstadisticas();
        $this->cargarCodigosRecientes();
    }

    public function render()
    {
        return view('livewire.configuracion.configuracion-codigos-autorizacion')
            ->layout('layouts.app', ['title' => 'Configuración de Códigos de Autorización']);
    }
}
