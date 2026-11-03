<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Recordatorio de pago</title></head>
<body style="font-family:Arial,sans-serif;color:#26354b;background:#f4f6f9;padding:20px;">
    <div style="max-width:760px;margin:auto;background:white;border:1px solid #e1e7ef;border-radius:10px;padding:24px;">
        <h2 style="color:#a65a0e;">Recordatorio de pago</h2>
        <p>Estimado(a) {{ $cliente }}:</p>
        <p>Reciba un cordial saludo de Distribuciones Valencia. Como parte del seguimiento de su cuenta,
            se adjunta en PDF la factura número <strong>{{ $numeroFactura }}</strong>,
            con un saldo pendiente de <strong>L {{ number_format($saldoCentavos / 100, 2) }}</strong>
            al {{ now()->format('d/m/Y') }}.</p>
        <p>Agradeceremos su apoyo para programar el pago de este saldo. Si ya realizó el pago,
            por favor responda a este correo adjuntando el comprobante para verificar y actualizar su cuenta.</p>
        <h3>Información para realizar sus pagos</h3>
        <p>Puede realizar depósitos o transferencias a las siguientes cuentas de nuestro catálogo:</p>
        <table style="width:100%;border-collapse:collapse;">
            <thead><tr><th style="text-align:left;padding:8px;background:#fff3e0;">Banco</th><th style="text-align:left;padding:8px;background:#fff3e0;">Cuenta</th></tr></thead>
            <tbody>
                @foreach($bancos as $banco)
                    <tr><td style="padding:8px;border-bottom:1px solid #e1e7ef;">{{ $banco->nombre }}</td><td style="padding:8px;border-bottom:1px solid #e1e7ef;">{{ $banco->cuenta }}</td></tr>
                @endforeach
            </tbody>
        </table>
        <p>Incluya el número de factura como referencia y envíe su comprobante a
            <a href="mailto:{{ \App\Mail\FacturaCobro::REMITENTE }}">{{ \App\Mail\FacturaCobro::REMITENTE }}</a>.</p>
        <p>También puede consultar con nuestro equipo de Cobros para coordinar el pago.</p>
        <p>Gracias por su atención y por su confianza.</p>
        <hr style="border:0;border-top:1px solid #e1e7ef;margin:24px 0;">
        <img src="{{ $message->embed(public_path('img/LOGO_VALENCIA.jpg')) }}" alt="Distribuciones Valencia" width="180" style="display:block;width:180px;max-width:100%;height:auto;">
        <p><strong>Departamento de Cobros · Distribuciones Valencia</strong><br>
            Teléfonos: +504 2234-9877 / +504 2234-9914<br>
            Col. Godoy, frente a Fuerza Aérea, calle hacia el FHIS, Lote No. 5.<br>
            <a href="https://www.distribucionesvalencia.hn">www.distribucionesvalencia.hn</a><br>
            {{ \App\Mail\FacturaCobro::REMITENTE }}</p>
    </div>
</body>
</html>
