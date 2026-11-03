<?php

namespace App\Livewire\CuentasPorCobrar;

use App\Http\Controllers\MenuHelper;
use App\Livewire\Concerns\EnviaCorreoCobro;
use App\Livewire\Ventas\FacturacionCorporativa;
use App\Services\SaldoCobro;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;

class CorreoCobro extends Component
{
    use EnviaCorreoCobro;

    #[Locked]
    public ?int $facturaDetalleId = null;

    #[Locked]
    public string $tipoDocumento = '';

    #[Locked]
    public string $tituloDocumento = '';

    #[Locked]
    public string $origen = '';

    private const RUTAS = [
        'estado' => ['estado_cuenta/vendedor'],
        'factura' => ['facturas/todos', 'facturas/corporativo', 'facturas/estatal', 'exonerado/ventas/lista', 'facturas/corporativo/vendedor', 'ventas/estatal/vendedor'],
    ];

    public function mount(): void
    {
        $ruta = trim(request()->path(), '/');
        foreach (self::RUTAS as $tipo => $rutas) {
            if (in_array($ruta, $rutas, true)) {
                $this->origen = $ruta;
                $this->tipoDocumento = $tipo;
                return;
            }
        }
        abort(403);
    }

    private function autorizar(): void
    {
        abort_unless(auth()->check() && in_array($this->origen, self::RUTAS[$this->tipoDocumento] ?? [], true), 403);
        $permitido = MenuHelper::tieneAcceso($this->origen);
        if (!$permitido && $this->origen === 'facturas/todos'
            && !DB::table('sub_menu')->where('url', $this->origen)->exists()) {
            foreach (self::RUTAS['factura'] as $ruta) {
                if ($ruta !== $this->origen && MenuHelper::tieneAcceso($ruta)) {
                    $permitido = true;
                    break;
                }
            }
        }
        abort_unless($permitido, 403);
    }

    #[On('abrirCorreoCobro')]
    public function abrir(string $tipo, int $id): void
    {
        $this->autorizar();
        abort_unless($tipo === $this->tipoDocumento && $id > 0, 422);
        $this->facturaDetalleId = $id;
        $this->resetCorreoFactura();
        $saldo = $this->saldoCorreo();
        abort_unless($saldo, 422, 'No hay saldo pendiente de pago disponible.');
        $this->tituloDocumento = $tipo === 'estado' ? 'Estado de cuenta · ' . $saldo->cliente : 'Factura ' . $saldo->cai;
        $this->prepararCorreoFactura();
    }

    public function cerrar(): void
    {
        $this->facturaDetalleId = null;
        $this->resetCorreoFactura();
    }

    protected function esEstadoCuentaCorreo(): bool
    {
        return $this->tipoDocumento === 'estado';
    }

    protected function saldoCorreo(): ?object
    {
        $this->autorizar();
        $admin = in_array((int) auth()->user()->rol_id, EstadoCuentaVendedor::ADMIN_ROLES, true);
        if ($this->tipoDocumento === 'estado') {
            $cliente = DB::table('cliente')->where('id', $this->facturaDetalleId)->first(['id', 'nombre']);
            abort_unless($cliente, 404);
            if (!$admin) {
                abort_unless(DB::table('cliente_usuario')->where('cliente_id', $cliente->id)
                    ->where('usuario_id', auth()->id())->whereIn('rol_id', [2, 3])->exists(), 403);
            }
            $saldo = (float) DB::query()->fromSub(app(SaldoCobro::class)->pendientes()->where('f.cliente_id', $cliente->id), 'pendientes')->sum('saldo');
            return $saldo > 0 ? (object) [
                'cliente_id' => $cliente->id, 'cliente' => $cliente->nombre, 'nombre_cliente' => $cliente->nombre,
                'cai' => (string) $cliente->id, 'numero_secuencia_cai' => $cliente->id, 'centavos' => (int) round($saldo * 100),
            ] : null;
        }
        $factura = DB::table('factura')->where('id', $this->facturaDetalleId)->first();
        abort_unless($factura, 404);
        if (!$admin) {
            abort_unless(in_array(auth()->id(), [(int) $factura->vendedor, (int) $factura->users_id, (int) $factura->gestor_entrega], true), 403);
        }
        $saldo = app(SaldoCobro::class)->pendientes()->where('f.id', $factura->id)->first();
        if ($saldo) {
            $saldo->cliente = DB::table('cliente')->where('id', $factura->cliente_id)->value('nombre');
            $saldo->centavos = (int) round((float) $saldo->saldo * 100);
        }
        return $saldo;
    }

    protected function pdfCorreo(): Response
    {
        return $this->tipoDocumento === 'estado'
            ? app(EstadoCuentaVendedor::class)->imprimirEstadoCuenta($this->facturaDetalleId)
            : app(FacturacionCorporativa::class)->imprimirFacturaCoorporativaCopia($this->facturaDetalleId);
    }

    public function render()
    {
        if ($this->facturaDetalleId !== null) {
            $this->autorizar();
        }
        return view('livewire.cuentas-por-cobrar.correo-cobro');
    }
}
