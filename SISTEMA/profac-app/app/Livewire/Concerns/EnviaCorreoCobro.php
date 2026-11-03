<?php

namespace App\Livewire\Concerns;

use App\Mail\FacturaCobro;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Livewire\Attributes\Locked;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use RuntimeException;

trait EnviaCorreoCobro
{
    public string $correoFactura = '';
    public bool $correoCorrecto = false;

    #[Locked]
    public string $etapaCorreo = '';

    #[Locked]
    public string $correoOriginal = '';

    #[Locked]
    public string $correoDestino = '';

    #[Locked]
    public bool $correoEnviado = false;

    #[Locked]
    public string $mensajeCorreo = '';

    abstract protected function saldoCorreo(): ?object;
    abstract protected function pdfCorreo(): \Symfony\Component\HttpFoundation\Response;

    protected function esEstadoCuentaCorreo(): bool
    {
        return false;
    }

    protected function resetCorreoFactura(): void
    {
        $this->reset('correoFactura', 'correoCorrecto', 'etapaCorreo', 'correoOriginal', 'correoDestino', 'correoEnviado', 'mensajeCorreo');
        $this->resetValidation(['correoFactura', 'correoCorrecto', 'envioCorreo']);
    }

    public function prepararCorreoFactura(): void
    {
        abort_unless($this->facturaDetalleId !== null && !$this->correoEnviado, 422);
        $saldo = $this->saldoCorreo();
        abort_unless($saldo, 404, 'La factura ya no está disponible en esta cartera.');
        $this->resetCorreoFactura();
        $this->correoOriginal = trim((string) DB::table('cliente')->where('id', $saldo->cliente_id)->value('correo'));
        $this->correoFactura = $this->correoOriginal;
        $this->etapaCorreo = 'direccion';
    }

    public function cancelarCorreoFactura(): void
    {
        abort_if($this->correoEnviado, 422);
        $this->resetCorreoFactura();
    }

    public function solicitarEnvioFactura(): void
    {
        abort_unless($this->etapaCorreo === 'direccion' && !$this->correoEnviado, 422);
        $this->correoFactura = trim($this->correoFactura);
        $this->validate([
            'correoFactura' => 'required|email:rfc|max:254',
            'correoCorrecto' => 'accepted',
        ], [
            'correoFactura.required' => 'Indica el correo del destinatario.',
            'correoFactura.email' => 'Indica un correo válido.',
            'correoCorrecto.accepted' => 'Confirma que el correo del cliente es correcto antes de enviar.',
        ]);
        $this->correoDestino = $this->correoFactura;
        $this->etapaCorreo = $this->correoDestino !== $this->correoOriginal ? 'actualizar' : 'enviar';
        if ($this->etapaCorreo === 'enviar') {
            $this->enviarCorreoFactura(false);
        }
    }

    public function enviarCorreoFactura(bool $actualizarCliente): void
    {
        abort_unless(in_array($this->etapaCorreo, ['actualizar', 'enviar'], true) && !$this->correoEnviado && $this->facturaDetalleId !== null, 422);
        abort_if($actualizarCliente && $this->etapaCorreo !== 'actualizar', 422);
        $this->resetValidation('envioCorreo');
        $this->mensajeCorreo = '';
        $saldo = $this->saldoCorreo();
        abort_unless($saldo, 404, 'La factura ya no está disponible en esta cartera.');
        $bancos = DB::table('banco')->whereRaw('LOWER(nombre) NOT LIKE ?', ['%promerica%'])
            ->whereNotNull('cuenta')->where('cuenta', '<>', '')->orderBy('nombre')->get(['nombre', 'cuenta']);
        if ($bancos->isEmpty()) {
            $this->addError('envioCorreo', 'No hay cuentas de pago disponibles en el catálogo Bancos (sin Promerica). No se envió el correo.');
            return;
        }
        $mailer = config('mail.default');
        if (config("mail.mailers.{$mailer}.transport") !== 'smtp') {
            $this->addError('envioCorreo', 'El envío requiere un servicio SMTP configurado. No se envió el correo.');
            return;
        }
        try {
            $respuestaPdf = $this->pdfCorreo();
            $pdf = $respuestaPdf->getContent();
            if (!is_string($pdf) || !str_starts_with($pdf, '%PDF-')) {
                throw new RuntimeException('La copia generada no es un PDF válido.');
            }
            $nombre = $saldo->nombre_cliente ?: $saldo->cliente;
            Mail::to($this->correoDestino)->send(new FacturaCobro(
                $nombre, (string) ($saldo->cai ?: $saldo->numero_secuencia_cai), $saldo->centavos, $bancos, $pdf, $this->esEstadoCuentaCorreo(),
            ));
        } catch (TransportExceptionInterface | RuntimeException | QueryException $e) {
            Log::error('No se pudo enviar la factura de cobro.', ['factura_id' => $this->facturaDetalleId, 'usuario_id' => auth()->id(), 'excepcion' => get_class($e)]);
            $this->addError('envioCorreo', 'No se pudo completar el envío. El correo del cliente no se modificó. Verifica la configuración del correo antes de reintentar.');
            return;
        }
        $this->correoEnviado = true;
        $this->etapaCorreo = 'enviado';
        $this->mensajeCorreo = ($this->esEstadoCuentaCorreo() ? 'Estado de cuenta enviado a ' : 'Factura enviada a ') . $this->correoDestino . '.';
        if ($actualizarCliente) {
            try {
                $actualizados = DB::table('cliente')->where('id', $saldo->cliente_id)
                    ->where(fn ($q) => $this->correoOriginal === ''
                        ? $q->whereNull('correo')->orWhereRaw("TRIM(correo) = ''")
                        : $q->whereRaw('TRIM(correo) = ?', [$this->correoOriginal]))
                    ->update(['correo' => $this->correoDestino]);
                if ($actualizados !== 1) {
                    Log::warning('Correo del cliente cambió durante el envío de factura.', ['factura_id' => $this->facturaDetalleId, 'cliente_id' => $saldo->cliente_id]);
                    $this->addError('envioCorreo', 'La factura se envió, pero el correo del cliente cambió durante la operación y no se sobrescribió. No es necesario reenviar.');
                } else {
                    $this->mensajeCorreo .= ' El correo del cliente fue actualizado.';
                }
            } catch (QueryException $e) {
                Log::error('Factura enviada, no se pudo actualizar el correo del cliente.', ['factura_id' => $this->facturaDetalleId, 'cliente_id' => $saldo->cliente_id, 'excepcion' => get_class($e)]);
                $this->addError('envioCorreo', 'La factura se envió, pero no se pudo actualizar el correo del cliente. No es necesario reenviar.');
            }
        }
    }

}
