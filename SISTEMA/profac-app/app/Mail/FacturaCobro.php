<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;

class FacturaCobro extends Mailable
{
    public const REMITENTE = 'cobros@distribucionesvalencia.hn';

    public function __construct(
        public string $cliente,
        public string $numeroFactura,
        public int $saldoCentavos,
        public Collection $bancos,
        private string $pdf,
    ) {}

    public function build(): self
    {
        $archivo = preg_replace('/[^A-Za-z0-9_-]/', '-', $this->numeroFactura);

        return $this->from(self::REMITENTE, 'Cobros - Distribuciones Valencia')
            ->replyTo(self::REMITENTE, 'Cobros - Distribuciones Valencia')
            ->subject('Recordatorio de pago - Factura ' . $this->numeroFactura)
            ->view('email.factura-cobro')
            ->attachData($this->pdf, 'Factura-' . $archivo . '.pdf', ['mime' => 'application/pdf']);
    }
}
