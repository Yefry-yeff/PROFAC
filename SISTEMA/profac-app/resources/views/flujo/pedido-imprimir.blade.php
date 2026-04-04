<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido #{{ $pedido->id }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #222;
            background: #fff;
        }

        .page {
            max-width: 720px;
            margin: 0 auto;
            padding: 32px 32px 48px;
        }

        /* ── Cabecera empresa ── */
        .print-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #1a7efb;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .print-header .company {
            font-size: 20px;
            font-weight: 700;
            color: #1a7efb;
            letter-spacing: 1px;
        }

        .print-header .doc-title {
            text-align: right;
        }

        .print-header .doc-title h1 {
            font-size: 22px;
            font-weight: 800;
            color: #1a7efb;
        }

        .print-header .doc-title .doc-num {
            font-size: 13px;
            color: #666;
            margin-top: 4px;
        }

        /* ── Badges de estado ── */
        .badge-estado {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .badge-pendiente { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
        .badge-procesado  { background: #d4edda; color: #155724; border: 1px solid #28a745; }
        .badge-anulado    { background: #f8d7da; color: #721c24; border: 1px solid #dc3545; }
        .badge-default    { background: #e9ecef; color: #495057; border: 1px solid #ced4da; }

        /* ── Sección datos cliente ── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 24px;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 20px;
        }

        .info-item label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #888;
            display: block;
            margin-bottom: 2px;
        }

        .info-item span {
            font-size: 13px;
            font-weight: 600;
            color: #333;
        }

        /* ── Observaciones ── */
        .observaciones-box {
            background: #fffbea;
            border-left: 4px solid #f0ad4e;
            border-radius: 0 6px 6px 0;
            padding: 10px 14px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #555;
        }

        /* ── Tabla de productos ── */
        table.productos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        table.productos thead tr {
            background: linear-gradient(135deg, #1a7efb, #1ab394);
            color: #fff;
        }

        table.productos thead th {
            padding: 10px 12px;
            font-size: 12px;
            font-weight: 600;
            text-align: left;
            letter-spacing: .3px;
        }

        table.productos thead th:last-child { text-align: right; }

        table.productos tbody tr:nth-child(even) { background: #f5f7fb; }

        table.productos tbody td {
            padding: 9px 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 13px;
        }

        table.productos tbody td:last-child { text-align: right; }

        .total-row td {
            font-weight: 700;
            font-size: 14px;
            border-top: 2px solid #1a7efb !important;
            background: #f0f7ff !important;
        }

        /* ── Pie de página ── */
        .print-footer {
            border-top: 1px dashed #ccc;
            padding-top: 12px;
            margin-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #999;
        }

        /* ── Botones (solo pantalla) ── */
        .no-print {
            text-align: center;
            padding: 16px 0 8px;
        }

        .btn-print {
            background: #1a7efb;
            color: #fff;
            border: none;
            padding: 10px 32px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            margin-right: 8px;
        }

        .btn-print:hover { background: #155ec7; }

        .btn-close-print {
            background: #6c757d;
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
        }

        @media print {
            .no-print { display: none; }
            body { font-size: 12px; }
            .page { padding: 16px; max-width: 100%; }
        }
    </style>
</head>
<body>

    {{-- Botones de control (no se imprimen) --}}
    <div class="no-print" style="padding-top:12px;">
        <button class="btn-print" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor"
                 viewBox="0 0 16 16" style="margin-right:6px; vertical-align:-2px;">
                <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0
                         1-1v-3a1 1 0 0 0-1-1z"/>
                <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2
                         2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
            </svg>
            Imprimir
        </button>
        <button class="btn-close-print" onclick="window.close()">Cerrar</button>
    </div>

    <div class="page">

        {{-- ── Encabezado ── --}}
        <div class="print-header">
            <div>
                <div class="company">PROFAC</div>
                <div style="font-size:12px; color:#888; margin-top:4px;">Sistema de Gestión</div>
            </div>
            <div class="doc-title">
                <h1>PEDIDO</h1>
                <div class="doc-num">#{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</div>
                <div style="margin-top:6px;">
                    @php
                        $estadoClass = match($pedido->estado ?? '') {
                            'pendiente' => 'badge-pendiente',
                            'procesado' => 'badge-procesado',
                            'anulado'   => 'badge-anulado',
                            default     => 'badge-default',
                        };
                    @endphp
                    <span class="badge-estado {{ $estadoClass }}">{{ ucfirst($pedido->estado ?? '—') }}</span>
                </div>
            </div>
        </div>

        {{-- ── Datos generales ── --}}
        <div class="info-grid">
            <div class="info-item">
                <label>Cliente</label>
                <span>{{ $pedido->cliente }}</span>
            </div>
            @if ($pedido->rtn)
            <div class="info-item">
                <label>RTN</label>
                <span>{{ $pedido->rtn }}</span>
            </div>
            @endif
            @if ($pedido->telefono_empresa)
            <div class="info-item">
                <label>Teléfono</label>
                <span>{{ $pedido->telefono_empresa }}</span>
            </div>
            @endif
            @if ($pedido->correo)
            <div class="info-item">
                <label>Correo</label>
                <span>{{ $pedido->correo }}</span>
            </div>
            @endif
            @if ($pedido->direccion)
            <div class="info-item" style="grid-column: span 2;">
                <label>Dirección</label>
                <span>{{ $pedido->direccion }}</span>
            </div>
            @endif
            <div class="info-item">
                <label>Registrado por</label>
                <span>{{ $pedido->registrado_por }}</span>
            </div>
            <div class="info-item">
                <label>Fecha</label>
                <span>{{ \Carbon\Carbon::parse($pedido->created_at)->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        {{-- ── Observaciones ── --}}
        @if ($pedido->observaciones)
        <div class="observaciones-box">
            <strong>Observaciones:</strong> {{ $pedido->observaciones }}
        </div>
        @endif

        {{-- ── Tabla de productos ── --}}
        <table class="productos">
            <thead>
                <tr>
                    <th style="width:44px;">#</th>
                    <th>Producto / Descripción</th>
                    <th style="width:90px; text-align:right;">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($detalles as $i => $det)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $det->nombre_producto }}</td>
                        <td style="text-align:right; font-weight:600;">{{ number_format($det->cantidad, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align:center; color:#999; padding:16px;">
                            Sin productos registrados.
                        </td>
                    </tr>
                @endforelse

                {{-- Fila totales --}}
                <tr class="total-row">
                    <td colspan="2" style="text-align:right;">Total de artículos:</td>
                    <td>{{ $detalles->count() }}</td>
                </tr>
            </tbody>
        </table>

        {{-- ── Pie ── --}}
        <div class="print-footer">
            <span>Generado: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</span>
            <span>PROFAC &mdash; Sistema de Gestión &mdash; Pedido #{{ $pedido->id }}</span>
        </div>

    </div>{{-- /page --}}

</body>
</html>
