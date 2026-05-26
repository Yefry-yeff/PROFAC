<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Autorización – Flujo</title>
</head>
<body style="font-family:Arial,sans-serif; background:#f5f5f5; margin:0; padding:20px;">
    <div style="max-width:560px; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.12);">

        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#1565c0,#1e88e5); padding:20px 24px;">
            <h2 style="color:#fff; margin:0; font-size:18px;">Solicitud de Autorización</h2>
            <p style="color:rgba(255,255,255,.85); margin:6px 0 0; font-size:13px;">Distribuciones Valencia – Gestión de Flujos</p>
        </div>

        <div style="padding:24px;">

            {{-- Acción solicitada (destacada) --}}
            <div style="background:#e3f2fd; border-left:4px solid #1e88e5; padding:12px 16px; border-radius:4px; margin-bottom:20px;">
                <p style="margin:0; font-size:12px; color:#555; text-transform:uppercase; letter-spacing:.5px;">Acción solicitada</p>
                <p style="margin:6px 0 0; font-size:17px; font-weight:700; color:#0d47a1;">{{ $accionLabel }}</p>
            </div>

            {{-- Detalles --}}
            <table style="width:100%; border-collapse:collapse; font-size:13px; margin-bottom:20px; background:#f9f9f9; border-radius:4px;">
                <tr>
                    <td style="padding:9px 12px; color:#555; font-weight:700; width:45%; border-bottom:1px solid #eee;">N° de Flujo</td>
                    <td style="padding:9px 12px; color:#222; font-weight:700; border-bottom:1px solid #eee;">{{ $flujoId ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:9px 12px; color:#555; font-weight:700;">Usuario solicitante</td>
                    <td style="padding:9px 12px; color:#222;">{{ $usuario ?? '—' }}</td>
                </tr>
            </table>

            {{-- Código --}}
            <div style="background:#fff8e1; border-left:4px solid #f9a826; padding:14px 16px; border-radius:4px; margin-bottom:20px;">
                <p style="margin:0; font-size:14px; color:#555;">Código de autorización:</p>
                <p style="margin:6px 0 0; font-size:30px; font-weight:700; color:#e65100; letter-spacing:6px;">{{ $codigo }}</p>
            </div>

            <p style="font-size:12px; color:#888; margin-bottom:0;">
                Este código es de uso único. El supervisor debe compartirlo con el usuario solicitante
                para que pueda completar la acción indicada arriba.
            </p>
        </div>
    </div>
</body>
</html>
