<?php

namespace App\Http\Controllers\Flujo;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PedidoController extends Controller
{
    private function getPedido(int $id)
    {
        return DB::table('pedido as p')
            ->join('cliente as c', 'c.id', '=', 'p.cliente_id')
            ->join('users as u', 'u.id', '=', 'p.users_id')
            ->select(
                'p.id', 'c.nombre as cliente', 'c.rtn', 'c.telefono_empresa',
                'c.direccion', 'c.correo', 'p.estado', 'p.observaciones',
                'u.name as registrado_por', 'p.created_at',
                DB::raw('(SELECT hf.flujo_id FROM historico_flujo hf WHERE hf.tramite_id = p.id AND hf.tipo_tramite_id = 1 LIMIT 1) as flujo_id')
            )
            ->where('p.id', $id)
            ->first();
    }

    // ── Vista de impresión ─────────────────────────────────────────────────
    public function imprimir(int $id)
    {
        $pedido = $this->getPedido($id);
        abort_if(!$pedido, 404);

        $detalles = DB::table('pedido_detalle')
            ->where('pedido_id', $id)
            ->orderBy('id')
            ->get();

        return view('flujo.pedido-imprimir', compact('pedido', 'detalles'));
    }

    // ── Exportar a Excel ───────────────────────────────────────────────────
    public function exportarExcel(int $id)
    {
        $pedido = $this->getPedido($id);
        abort_if(!$pedido, 404);

        $detalles = DB::table('pedido_detalle')
            ->where('pedido_id', $id)
            ->orderBy('id')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pedido #' . $id);

        // ── Título ──
        $sheet->mergeCells('A1:C1');
        $sheet->setCellValue('A1', 'PEDIDO #' . $id);
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a7efb']],
        ]);
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');

        // ── Datos cabecera ──
        $info = [
            ['Cliente',        $pedido->cliente],
            ['RTN',            $pedido->rtn ?? '—'],
            ['Estado',         ucfirst($pedido->estado ?? '')],
            ['Teléfono',       $pedido->telefono_empresa ?? '—'],
            ['Correo',         $pedido->correo ?? '—'],
            ['Registrado por', $pedido->registrado_por],
            ['Fecha',          $pedido->created_at],
        ];

        $row = 2;
        foreach ($info as [$label, $value]) {
            $sheet->setCellValue('A' . $row, $label . ':');
            $sheet->setCellValue('B' . $row, $value);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
        }

        if ($pedido->observaciones) {
            $sheet->setCellValue('A' . $row, 'Observaciones:');
            $sheet->setCellValue('B' . $row, $pedido->observaciones);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
        }

        // ── Separador ──
        $row++;

        // ── Encabezado detalle ──
        $sheet->setCellValue('A' . $row, '#');
        $sheet->setCellValue('B' . $row, 'Producto');
        $sheet->setCellValue('C' . $row, 'Cantidad');
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4F8']],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $row++;

        // ── Filas de detalle ──
        foreach ($detalles as $i => $det) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $det->nombre_producto);
            $sheet->setCellValue('C' . $row, $det->cantidad);
            $row++;
        }

        // ── Ajustar anchos ──
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(12);

        $writer   = new XlsxWriter($spreadsheet);
        $filename = 'pedido_' . $id . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
