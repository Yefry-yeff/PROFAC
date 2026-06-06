<table>
    <thead>
        <tr>
            <th colspan="5" style="text-align: center; font-size: 16px; font-weight: bold;">
                BITÁCORA DE LOGIN - SISTEMA PROFAC
            </th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: center; font-size: 12px;">
                Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
            </th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: center; font-size: 11px;">
                Fecha de generación: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
            </th>
        </tr>
        <tr>
            <th style="background-color: #D3D3D3; font-weight: bold; text-align: center;">ID</th>
            <th style="background-color: #D3D3D3; font-weight: bold; text-align: center;">Nombre de Usuario</th>
            <th style="background-color: #D3D3D3; font-weight: bold; text-align: center;">Dirección IP</th>
            <th style="background-color: #D3D3D3; font-weight: bold; text-align: center;">Terminal</th>
            <th style="background-color: #D3D3D3; font-weight: bold; text-align: center;">Fecha y Hora de Ingreso</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $login)
            <tr>
                <td style="text-align: center;">{{ $login->id }}</td>
                <td>{{ $login->nombre }}</td>
                <td style="text-align: center;">{{ $login->ip_address }}</td>
                <td>{{ $login->terminal ?? 'N/A' }}</td>
                <td style="text-align: center;">{{ \Carbon\Carbon::parse($login->fecha_ingreso)->format('d/m/Y H:i:s') }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" style="text-align: center; font-weight: bold; padding-top: 10px;">
                Total de registros: {{ count($data) }}
            </td>
        </tr>
    </tfoot>
</table>
