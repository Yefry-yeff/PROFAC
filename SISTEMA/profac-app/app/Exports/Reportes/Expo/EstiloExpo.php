<?php

namespace App\Exports\Reportes\Expo;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Formato común de los Excel del Reporte Expo.
 *
 * Una tabla se define con columnas:
 *   ['titulo' => 'Ofertado', 'clave' => 'ofertado', 'formato' => 'moneda',
 *    'ancho' => 16, 'total' => 'suma' | ['ratio', claveNumerador, claveDenominador | [claves sumadas]],
 *    'colores' => ['Ganadora' => '15803D']]
 * Formatos: texto, id, entero, cantidad, moneda, pct (llega en escala 0-100) y fecha.
 * Los totales usan SUBTOTAL para que respeten el autofiltro de Excel.
 */
trait EstiloExpo
{
    protected string $colorOscuro = '1A2035';
    protected string $colorMarca = '15906B';
    protected string $colorBorde = 'CBD5E1';

    protected function formatoExcel(string $formato): ?string
    {
        return [
            'moneda' => '"L "#,##0.00;[Red]-"L "#,##0.00',
            'entero' => '#,##0',
            'id' => '0',
            'cantidad' => '#,##0.00',
            'pct' => '0.00%;[Red]-0.00%',
            'fecha' => 'dd/mm/yyyy',
        ][$formato] ?? null;
    }

    protected function esNumerico(string $formato): bool
    {
        return in_array($formato, ['moneda', 'entero', 'id', 'cantidad', 'pct'], true);
    }

    protected function escribirValor(Worksheet $s, int $col, int $fila, $valor, string $formato = 'texto', bool $aplicarFormato = true): void
    {
        if ($valor === null || $valor === '') {
            return;
        }
        $celda = Coordinate::stringFromColumnIndex($col) . $fila;
        if ($formato === 'texto') {
            $s->setCellValueExplicit($celda, (string) $valor, DataType::TYPE_STRING);
        } elseif ($formato === 'fecha') {
            $marca = strtotime((string) $valor);
            $marca === false
                ? $s->setCellValueExplicit($celda, (string) $valor, DataType::TYPE_STRING)
                : $s->setCellValue($celda, Date::PHPToExcel($marca));
        } else {
            $s->setCellValue($celda, $formato === 'pct' ? (float) $valor / 100 : (float) $valor);
        }
        if ($aplicarFormato && $codigo = $this->formatoExcel($formato)) {
            $s->getStyle($celda)->getNumberFormat()->setFormatCode($codigo);
        }
    }

    /** Título, líneas de contexto (Expo, fecha, filtros) y fila en blanco. Devuelve la siguiente fila libre. */
    protected function escribirTitulo(Worksheet $s, string $titulo, array $info, int $numColumnas): int
    {
        $ultima = Coordinate::stringFromColumnIndex(max($numColumnas, 4));
        $s->setShowGridlines(false);
        $s->setCellValue('A1', $titulo);
        $s->mergeCells("A1:{$ultima}1");
        $s->getStyle("A1:{$ultima}1")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $this->colorOscuro]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);
        $s->getRowDimension(1)->setRowHeight(28);

        $fila = 2;
        foreach ($info as $linea) {
            $s->setCellValueExplicit("A{$fila}", (string) $linea, DataType::TYPE_STRING);
            $s->mergeCells("A{$fila}:{$ultima}{$fila}");
            $s->getStyle("A{$fila}")->applyFromArray([
                'font' => ['size' => 10, 'color' => ['rgb' => '475569'], 'bold' => $fila === 2],
                'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_TOP],
            ]);
            $fila++;
        }

        return $fila + 1;
    }

    /** Barra de sección (p. ej. "PRODUCTOS") a lo ancho de la tabla. */
    protected function escribirBarra(Worksheet $s, int $fila, string $texto, int $numColumnas, ?string $color = null): void
    {
        $ultima = Coordinate::stringFromColumnIndex($numColumnas);
        $s->setCellValue("A{$fila}", $texto);
        $s->mergeCells("A{$fila}:{$ultima}{$fila}");
        $s->getStyle("A{$fila}:{$ultima}{$fila}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color ?? $this->colorOscuro]],
            'alignment' => ['indent' => 1],
        ]);
        $s->getRowDimension($fila)->setRowHeight(20);
    }

    protected function escribirNota(Worksheet $s, int $fila, string $texto, int $numColumnas): void
    {
        $ultima = Coordinate::stringFromColumnIndex(max($numColumnas, 4));
        $s->setCellValueExplicit("A{$fila}", $texto, DataType::TYPE_STRING);
        $s->mergeCells("A{$fila}:{$ultima}{$fila}");
        $s->getStyle("A{$fila}")->applyFromArray([
            'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '64748B']],
            'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_TOP],
        ]);
        $s->getRowDimension($fila)->setRowHeight(max(15, 13 * (int) ceil(mb_strlen($texto) / 140)));
    }

    /**
     * Escribe una tabla desde $filaEncabezado. Devuelve la última fila usada
     * (la de totales si la hay).
     */
    protected function escribirTabla(Worksheet $s, int $filaEncabezado, array $columnas, array $filas, array $opciones = []): int
    {
        $opciones += ['totales' => true, 'autofiltro' => false, 'etiqueta_total' => 'TOTAL', 'color_encabezado' => $this->colorMarca, 'vacio' => 'Sin datos para los filtros seleccionados.'];
        $columnas = array_values($columnas);
        $ultimaLetra = Coordinate::stringFromColumnIndex(count($columnas));
        $letras = [];
        foreach ($columnas as $i => $col) {
            $letra = Coordinate::stringFromColumnIndex($i + 1);
            $letras[$col['clave'] ?? $i] = $letra;
            $s->setCellValue($letra . $filaEncabezado, $col['titulo']);
            $ancho = $col['ancho'] ?? ($this->esNumerico($col['formato'] ?? 'texto') ? 15 : 18);
            $dimension = $s->getColumnDimension($letra);
            if ($dimension->getWidth() < $ancho) {
                $dimension->setWidth($ancho);
            }
        }
        $s->getStyle("A{$filaEncabezado}:{$ultimaLetra}{$filaEncabezado}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $opciones['color_encabezado']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $s->getRowDimension($filaEncabezado)->setRowHeight(30);

        $primera = $filaEncabezado + 1;
        if (!$filas) {
            $s->setCellValue("A{$primera}", $opciones['vacio']);
            $s->mergeCells("A{$primera}:{$ultimaLetra}{$primera}");
            $s->getStyle("A{$primera}")->getFont()->setItalic(true)->getColor()->setRGB('64748B');
            $this->bordes($s, "A{$filaEncabezado}:{$ultimaLetra}{$primera}");

            return $primera;
        }

        $fila = $primera;
        foreach ($filas as $valores) {
            $valores = array_values($valores);
            foreach ($columnas as $i => $col) {
                $this->escribirValor($s, $i + 1, $fila, $valores[$i] ?? null, $col['formato'] ?? 'texto', false);
            }
            $fila++;
        }
        $ultimaDato = $fila - 1;

        foreach ($columnas as $i => $col) {
            $formato = $col['formato'] ?? 'texto';
            $letra = Coordinate::stringFromColumnIndex($i + 1);
            $rango = "{$letra}{$primera}:{$letra}{$ultimaDato}";
            if ($codigo = $this->formatoExcel($formato)) {
                $s->getStyle($rango)->getNumberFormat()->setFormatCode($codigo);
            }
            if ($this->esNumerico($formato) && $formato !== 'id') {
                $s->getStyle($rango)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            } elseif ($formato !== 'texto') {
                $s->getStyle($rango)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        // Filas alternas con formato condicional para que sigan alternando al
        // filtrar u ordenar dentro de Excel.
        $zebra = new Conditional();
        $zebra->setConditionType(Conditional::CONDITION_EXPRESSION)->addCondition('MOD(ROW(),2)=0');
        $zebra->getStyle()->getFill()->setFillType(Fill::FILL_SOLID);
        $zebra->getStyle()->getFill()->getStartColor()->setARGB('FFF1F5F9');
        $zebra->getStyle()->getFill()->getEndColor()->setARGB('FFF1F5F9');
        $s->getStyle("A{$primera}:{$ultimaLetra}{$ultimaDato}")->setConditionalStyles([$zebra]);

        // Colores por valor (tipo de oferta, estado de facturación) con formato
        // condicional: se combinan con el relleno de filas alternas.
        foreach ($columnas as $i => $col) {
            if (empty($col['colores'])) {
                continue;
            }
            $letra = Coordinate::stringFromColumnIndex($i + 1);
            $reglas = [];
            foreach ($col['colores'] as $texto => $color) {
                $regla = new Conditional();
                $regla->setConditionType(Conditional::CONDITION_CELLIS)
                    ->setOperatorType(Conditional::OPERATOR_EQUAL)
                    ->addCondition('"' . str_replace('"', '""', $texto) . '"');
                $regla->getStyle()->getFont()->setBold(true)->getColor()->setRGB($color);
                $reglas[] = $regla;
            }
            $s->getStyle("{$letra}{$primera}:{$letra}{$ultimaDato}")->setConditionalStyles($reglas);
        }

        if ($opciones['autofiltro']) {
            $s->setAutoFilter("A{$filaEncabezado}:{$ultimaLetra}{$ultimaDato}");
        }

        $ultima = $ultimaDato;
        if ($opciones['totales']) {
            $ultima = $ultimaDato + 1;
            $suma = fn (string $letra) => "SUBTOTAL(109,{$letra}{$primera}:{$letra}{$ultimaDato})";
            $s->setCellValue("A{$ultima}", $opciones['etiqueta_total']);
            foreach ($columnas as $i => $col) {
                $total = $col['total'] ?? null;
                if (!$total) {
                    continue;
                }
                $letra = Coordinate::stringFromColumnIndex($i + 1);
                $formula = $total === 'suma'
                    ? '=' . $suma($letra)
                    : '=IFERROR(' . $suma($letras[$total[1]]) . '/('
                        . implode('+', array_map(fn ($clave) => $suma($letras[$clave]), (array) $total[2])) . '),0)';
                $s->setCellValue($letra . $ultima, $formula);
                $s->getStyle($letra . $ultima)->getNumberFormat()
                    ->setFormatCode($this->formatoExcel($col['formato'] ?? 'cantidad') ?? '#,##0.00');
                $s->getStyle($letra . $ultima)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
            $s->getStyle("A{$ultima}:{$ultimaLetra}{$ultima}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            ]);
        }

        $this->bordes($s, "A{$filaEncabezado}:{$ultimaLetra}{$ultima}");
        if ($opciones['totales']) {
            $s->getStyle("A{$ultima}:{$ultimaLetra}{$ultima}")->getBorders()->getTop()
                ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB($this->colorOscuro);
        }

        return $ultima;
    }

    protected function bordes(Worksheet $s, string $rango): void
    {
        $s->getStyle($rango)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB($this->colorBorde);
    }

    protected function configurarImpresion(Worksheet $s, int $filaEncabezado): void
    {
        $s->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_LETTER)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setRowsToRepeatAtTopByStartAndEnd($filaEncabezado, $filaEncabezado);
        $s->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.3)->setRight(0.3);
        $s->getHeaderFooter()->setOddFooter('&L&8Reporte Expo&R&8Página &P de &N');
    }
}
