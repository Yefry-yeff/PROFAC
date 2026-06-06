<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Autorización SR</title>
</head>
<body style="font-family:Arial,sans-serif; background:#f5f5f5; margin:0; padding:20px;">
    <div style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.12);">

        <div style="background:linear-gradient(135deg,#e65100,#f9a826); padding:20px 24px;">
            <h2 style="color:#fff; margin:0; font-size:18px;">Solicitud de Autorización SR</h2>
            <p style="color:rgba(255,255,255,.85); margin:6px 0 0; font-size:13px;">Distribuciones Valencia</p>
        </div>

        <div style="padding:24px;">

            {{-- Info de usuario / flujo / factura --}}
            <table style="width:100%; border-collapse:collapse; font-size:13px; margin-bottom:16px; background:#f9f9f9; border-radius:4px; overflow:hidden;">
                <tr>
                    <td style="padding:8px 12px; color:#555; font-weight:700; width:40%; border-bottom:1px solid #eee;">Usuario solicitante</td>
                    <td style="padding:8px 12px; color:#333; border-bottom:1px solid #eee;">{{ $usuario ?? '—' }}</td>
                </tr>
                @if(!empty($flujoId))
                <tr>
                    <td style="padding:8px 12px; color:#555; font-weight:700; border-bottom:1px solid #eee;">N° de Flujo</td>
                    <td style="padding:8px 12px; color:#333; border-bottom:1px solid #eee;">{{ $flujoId }}</td>
                </tr>
                @endif
                @if(!empty($numeroVenta))
                <tr>
                    <td style="padding:8px 12px; color:#555; font-weight:700;">N° de Factura</td>
                    <td style="padding:8px 12px; color:#333;">{{ $numeroVenta }}</td>
                </tr>
                @endif
            </table>

            <p style="font-size:14px; color:#333; margin-top:0;">
                Se ha recibido una solicitud de autorización para una factura <strong>Sin Restricción</strong>.
                A continuación se muestra el detalle de los productos.
            </p>

            @if(!empty($productos))
            <table style="width:100%; border-collapse:collapse; font-size:13px; margin-bottom:16px;">
                <thead>
                    <tr style="background:#f5f5f5;">
                        <th style="border:1px solid #ddd; padding:8px 10px; text-align:left;">Producto</th>
                        <th style="border:1px solid #ddd; padding:8px 10px; text-align:right;">Precio OPC</th>
                        <th style="border:1px solid #ddd; padding:8px 10px; text-align:right;">P.Unitario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $prod)
                    @php
                        $precioOpc  = isset($prod['precioOpc'])      ? (float)$prod['precioOpc']      : (isset($prod->precioOpc)      ? (float)$prod->precioOpc      : 0);
                        $precioUnit = isset($prod['precioUnitario']) ? (float)$prod['precioUnitario'] : (isset($prod->precioUnitario) ? (float)$prod->precioUnitario : 0);
                        $nombre     = isset($prod['nombre'])         ? $prod['nombre']                : (isset($prod->nombre)         ? $prod->nombre                : '—');
                        $esBajo     = $precioUnit < $precioOpc;
                    @endphp
                    <tr style="{{ $esBajo ? 'background:#ffebee;' : '' }}">
                        <td style="border:1px solid #ddd; padding:7px 10px;">{{ $nombre }}</td>
                        <td style="border:1px solid #ddd; padding:7px 10px; text-align:right;">{{ number_format($precioOpc, 2) }}</td>
                        <td style="border:1px solid #ddd; padding:7px 10px; text-align:right; {{ $esBajo ? 'color:#c62828; font-weight:700;' : '' }}">
                            {{ number_format($precioUnit, 2) }}
                            @if($esBajo) <span style="font-size:11px;"> &#9660; bajo OPC</span> @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            <div style="background:#fff8e1; border-left:4px solid #f9a826; padding:14px 16px; border-radius:4px; margin-bottom:20px;">
                <p style="margin:0; font-size:15px; color:#555;">Código de autorización:</p>
                <p style="margin:6px 0 0; font-size:28px; font-weight:700; color:#e65100; letter-spacing:4px;">{{ $codigo }}</p>
            </div>

            <p style="font-size:12px; color:#888; margin-bottom:0;">
                Este código es de uso único y debe ser ingresado inmediatamente por el usuario de facturación.
                Permite realizar una factura sin restricciones de precio base.
            </p>
        </div>
    </div>
</body>
</html>