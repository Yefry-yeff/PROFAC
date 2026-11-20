<?php

namespace App\Services\Auditoria;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\AutoFilter;
use OpenSpout\Writer\Common\Entity\Sheet;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use RuntimeException;
use Throwable;

class ExportadorMovimientosXlsx
{
    private const FECHA_INICIO = '2022-11-01';
    private const FECHA_FIN = '2025-12-31';

    private const ENCABEZADOS_KARDEX = [
        'FECHA DE REGISTRO',
        'NÚMERO DE FACTURA COMERCIAL',
        'CÓDIGO',
        'DESCRIPCIÓN DE MERCANCÍA',
        'UNIDAD DE MEDIDA',
        'CANTIDAD INICIAL',
        'COMPRA',
        'VENTA',
        'SALDO / EXISTENCIA',
        'COSTOS',
        'NÚMERO DE DECLARACIÓN',
    ];

    private const ENCABEZADOS_COMPLETO = [
        'Secuencia', 'Movimiento', 'Fecha movimiento', 'Fecha ingreso compra',
        'Fecha creación factura', 'Fecha emisión factura', 'Factura ID', 'Compra ID',
        'Comprobante ID', 'Ajuste ID', 'Documento', 'CAI', 'Clasificación', 'Estado',
        'Motivo', 'Comentario', 'Producto ID', 'Producto', 'Lote', 'Bodega', 'Sección',
        'Unidad compra', 'Factor compra', 'Unidad venta', 'Factor venta',
        'Cantidad documento', 'Cantidad base', 'Movimiento base', 'Afecta inventario',
        'Cliente', 'Proveedor', 'Usuario',
    ];

    public function __construct(private readonly ConsultaComprasVentas $consulta)
    {
    }

    public function generar(): string
    {
        $ruta = tempnam(sys_get_temp_dir(), 'auditoria_movimientos_');
        if ($ruta === false) {
            throw new RuntimeException('No fue posible crear el archivo temporal de auditoría.');
        }

        $rutaXlsx = $ruta . '.xlsx';
        @unlink($ruta);

        $options = new Options();
        $options->SHOULD_USE_INLINE_STRINGS = true;
        $writer = new Writer($options);

        try {
            $writer->openToFile($rutaXlsx);

            [$productos, $movimientos] = $this->datosKardex();

            $hojaA = $writer->getCurrentSheet();
            $hojaA->setName('Facturas A');
            $this->configurarHojaKardex($hojaA);
            $this->escribirKardex(
                $writer,
                $options,
                $hojaA,
                0,
                'A',
                $productos,
                $movimientos
            );

            $hojaB = $writer->addNewSheetAndMakeItCurrent();
            $hojaB->setName('Facturas B');
            $this->configurarHojaKardex($hojaB);
            $this->escribirKardex(
                $writer,
                $options,
                $hojaB,
                1,
                'B',
                $productos,
                $movimientos
            );

            unset($movimientos);

            $hojaCompleta = $writer->addNewSheetAndMakeItCurrent();
            $hojaCompleta->setName('Reporte completo');
            $this->configurarHojaCompleta($hojaCompleta);
            $this->escribirReporteCompleto($writer, $hojaCompleta);

            $writer->close();
        } catch (Throwable $error) {
            @unlink($rutaXlsx);
            throw $error;
        }

        return $rutaXlsx;
    }

    public function generarPorProducto(string $clasificacion): string
    {
        $clasificacion = strtoupper($clasificacion);
        if (! in_array($clasificacion, ['A', 'B'], true)) {
            throw new RuntimeException('La clasificación debe ser A o B.');
        }

        $ruta = tempnam(sys_get_temp_dir(), "auditoria_productos_{$clasificacion}_");
        if ($ruta === false) {
            throw new RuntimeException('No fue posible crear el archivo temporal de auditoría.');
        }

        $rutaXlsx = $ruta . '.xlsx';
        @unlink($ruta);

        $options = new Options();
        $options->SHOULD_USE_INLINE_STRINGS = true;
        $writer = new Writer($options);

        try {
            $writer->openToFile($rutaXlsx);

            [$productos, $movimientos] = $this->datosKardex();

            foreach ($productos->values() as $indice => $producto) {
                $hoja = $indice === 0
                    ? $writer->getCurrentSheet()
                    : $writer->addNewSheetAndMakeItCurrent();

                $hoja->setName((string) $producto->id);
                $this->configurarHojaKardex($hoja);
                $this->escribirKardexProducto(
                    $writer,
                    $options,
                    $hoja,
                    $indice,
                    $clasificacion,
                    $producto,
                    $movimientos->get($producto->id, collect())
                );
            }

            $writer->close();
        } catch (Throwable $error) {
            @unlink($rutaXlsx);
            throw $error;
        }

        return $rutaXlsx;
    }

    private function datosKardex(): array
    {
        $productos = DB::table('producto as p')
            ->leftJoin('unidad_medida as um', 'um.id', '=', 'p.unidad_medida_compra_id')
            ->whereIn('p.id', ConsultaComprasVentas::PRODUCTOS_AUDITADOS)
            ->orderBy('p.id')
            ->get([
                'p.id',
                'p.nombre',
                DB::raw("COALESCE(um.nombre, 'UNIDAD BASE') AS unidad_base"),
            ]);

        $movimientos = $this->consulta
            ->construir($this->consulta->filtros([
                'desde' => self::FECHA_INICIO,
                'hasta' => self::FECHA_FIN,
            ]))
            ->where('a.estado_documento', 'ACTIVO')
            ->where(function ($query) {
                $query->whereIn('a.tipo_movimiento', ['COMPRA', 'COMPROBANTE', 'AJUSTE'])
                    ->orWhere(function ($ventas) {
                        $ventas->where('a.tipo_movimiento', 'VENTA')
                            ->whereIn('a.clasificacion_factura', ['A', 'B']);
                    });
            })
            ->select('a.*')
            ->orderBy('a.producto_id')
            ->orderBy('a.fecha_movimiento')
            ->orderByRaw("
                CASE a.tipo_movimiento
                    WHEN 'COMPRA' THEN 0
                    WHEN 'COMPROBANTE' THEN 1
                    WHEN 'VENTA' THEN 2
                    WHEN 'AJUSTE' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('a.numero_documento')
            ->orderBy('a.id')
            ->get()
            ->groupBy('producto_id');

        return [$productos, $movimientos];
    }

    private function configurarHojaKardex(Sheet $sheet): void
    {
        $anchos = [17, 31, 11, 56, 22, 18, 16, 16, 20, 15, 24];
        foreach ($anchos as $indice => $ancho) {
            $sheet->setColumnWidth($ancho, $indice + 1);
        }

        $sheet->setSheetView((new SheetView())->setFreezeRow(5));
    }

    private function escribirKardex(
        Writer $writer,
        Options $options,
        Sheet $sheet,
        int $sheetIndex,
        string $clasificacion,
        Collection $productos,
        Collection $movimientos
    ): void {
        $tituloStyle = (new Style())
            ->setFontBold()
            ->setFontSize(13)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor('E66C00');
        $subtituloStyle = (new Style())
            ->setFontItalic()
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor('1F4E78');
        $encabezadoStyle = (new Style())
            ->setFontBold()
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor('E66C00')
            ->setCellAlignment('center')
            ->setCellVerticalAlignment('center')
            ->setShouldWrapText();
        $saldoInicialStyle = (new Style())->setBackgroundColor('D9EAF7');
        $numeroStyle = (new Style())->setFormat('#,##0.0000');

        $fila = 1;
        $writer->addRow(
            Row::fromValues(
                ['CONTROL DE KARDEX DE INVENTARIO — ENTRADAS, SALIDAS Y EXISTENCIAS ACUMULADAS'],
                $tituloStyle
            )->setHeight(25)
        );
        $options->mergeCells(0, $fila, 10, $fila, $sheetIndex);
        $fila++;

        $writer->addRow(
            Row::fromValues([
                'Período: 01/11/2022 al 31/12/2025   |   '
                . $productos->count() . ' productos ordenados por código y cronológicamente   |   '
                . 'Cantidades y saldos expresados en unidad base',
            ], $subtituloStyle)->setHeight(22)
        );
        $options->mergeCells(0, $fila, 10, $fila, $sheetIndex);
        $fila++;

        $writer->addRow(Row::fromValues(array_fill(0, 11, null)));
        $fila++;
        $writer->addRow(Row::fromValues(self::ENCABEZADOS_KARDEX, $encabezadoStyle)->setHeight(32));
        $fila++;

        foreach ($productos as $producto) {
            $saldo = 0.0;

            $writer->addRow(Row::fromValuesWithStyles([
                null,
                'SALDO INICIAL',
                $producto->id,
                $this->seguro($producto->nombre),
                $this->seguro($producto->unidad_base),
                0,
                null,
                null,
                $saldo,
                null,
                null,
            ], $saldoInicialStyle, [
                5 => $numeroStyle,
                8 => $numeroStyle,
            ]));
            $fila++;

            $movimientosProducto = $this->movimientosDesdePrimeraCompra(
                $movimientos->get($producto->id, collect()),
                $clasificacion
            );

            foreach ($movimientosProducto as $movimiento) {
                $impacto = (float) $movimiento->cantidad_movimiento_base;
                $saldo += $impacto;

                $writer->addRow(Row::fromValuesWithStyles([
                    $this->fecha($movimiento->fecha_movimiento),
                    $this->seguro($this->numeroComercial($movimiento)),
                    $producto->id,
                    $this->seguro($producto->nombre),
                    $this->seguro($producto->unidad_base),
                    0,
                    $impacto > 0 ? $impacto : null,
                    $impacto < 0 ? abs($impacto) : null,
                    $saldo,
                    null,
                    null,
                ], null, [
                    5 => $numeroStyle,
                    6 => $numeroStyle,
                    7 => $numeroStyle,
                    8 => $numeroStyle,
                ]));
                $fila++;
            }
        }

        $sheet->setAutoFilter(new AutoFilter(0, 4, 10, $fila - 1));
    }

    private function escribirKardexProducto(
        Writer $writer,
        Options $options,
        Sheet $sheet,
        int $sheetIndex,
        string $clasificacion,
        object $producto,
        Collection $movimientos
    ): void {
        $tituloStyle = (new Style())
            ->setFontBold()
            ->setFontSize(13)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor('E66C00');
        $subtituloStyle = (new Style())
            ->setFontItalic()
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor('1F4E78');
        $encabezadoStyle = (new Style())
            ->setFontBold()
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor('E66C00')
            ->setCellAlignment('center')
            ->setCellVerticalAlignment('center')
            ->setShouldWrapText();
        $saldoInicialStyle = (new Style())->setBackgroundColor('D9EAF7');
        $totalesStyle = (new Style())
            ->setFontBold()
            ->setBackgroundColor('E2F0D9');
        $numeroStyle = (new Style())->setFormat('#,##0.0000');

        $fila = 1;
        $writer->addRow(
            Row::fromValues(
                ['CONTROL DE KARDEX DE INVENTARIO — ENTRADAS, SALIDAS Y EXISTENCIAS ACUMULADAS'],
                $tituloStyle
            )->setHeight(25)
        );
        $options->mergeCells(0, $fila, 10, $fila, $sheetIndex);
        $fila++;

        $writer->addRow(
            Row::fromValues([
                "Producto {$producto->id}: "
                . $this->seguro($producto->nombre)
                . '   |   Período: 01/11/2022 al 31/12/2025   |   '
                . 'Cantidades y saldos expresados en unidad base',
            ], $subtituloStyle)->setHeight(22)
        );
        $options->mergeCells(0, $fila, 10, $fila, $sheetIndex);
        $fila++;

        $writer->addRow(Row::fromValues(array_fill(0, 11, null)));
        $fila++;
        $writer->addRow(Row::fromValues(self::ENCABEZADOS_KARDEX, $encabezadoStyle)->setHeight(32));
        $fila++;

        $writer->addRow(Row::fromValuesWithStyles([
            null,
            'SALDO INICIAL',
            $producto->id,
            $this->seguro($producto->nombre),
            $this->seguro($producto->unidad_base),
            0,
            null,
            null,
            0,
            null,
            null,
        ], $saldoInicialStyle, [
            5 => $numeroStyle,
            8 => $numeroStyle,
        ]));
        $fila++;

        $saldo = 0.0;
        $totalEntradas = 0.0;
        $totalSalidas = 0.0;
        $ultimaFilaMovimiento = $fila - 1;

        foreach ($this->movimientosDesdePrimeraCompra($movimientos, $clasificacion) as $movimiento) {
            $impacto = (float) $movimiento->cantidad_movimiento_base;
            $saldo += $impacto;
            $totalEntradas += max($impacto, 0);
            $totalSalidas += abs(min($impacto, 0));

            $writer->addRow(Row::fromValuesWithStyles([
                $this->fecha($movimiento->fecha_movimiento),
                $this->seguro($this->numeroComercial($movimiento)),
                $producto->id,
                $this->seguro($producto->nombre),
                $this->seguro($producto->unidad_base),
                0,
                $impacto > 0 ? $impacto : null,
                $impacto < 0 ? abs($impacto) : null,
                $saldo,
                null,
                null,
            ], null, [
                5 => $numeroStyle,
                6 => $numeroStyle,
                7 => $numeroStyle,
                8 => $numeroStyle,
            ]));
            $fila++;
            $ultimaFilaMovimiento = $fila - 1;
        }

        $sheet->setAutoFilter(new AutoFilter(0, 4, 10, $ultimaFilaMovimiento));

        $writer->addRow(Row::fromValues(array_fill(0, 11, null)));
        $writer->addRow(Row::fromValuesWithStyles([
            null,
            'TOTALES CONSOLIDADOS',
            $producto->id,
            $this->seguro($producto->nombre),
            $this->seguro($producto->unidad_base),
            0,
            $totalEntradas,
            $totalSalidas,
            $saldo,
            null,
            null,
        ], $totalesStyle, [
            5 => $numeroStyle,
            6 => $numeroStyle,
            7 => $numeroStyle,
            8 => $numeroStyle,
        ]));
    }

    private function movimientosDesdePrimeraCompra(
        Collection $movimientos,
        string $clasificacion
    ): Collection {
        return $movimientos
            ->filter(fn ($movimiento) => $movimiento->tipo_movimiento !== 'VENTA'
                || $movimiento->clasificacion_factura === $clasificacion)
            ->values()
            ->skipUntil(fn ($movimiento) => $movimiento->tipo_movimiento === 'COMPRA')
            ->values();
    }

    private function configurarHojaCompleta(Sheet $sheet): void
    {
        $anchos = [
            12, 22, 21, 20, 23, 21, 13, 13, 17, 12, 24, 24, 14, 13, 28, 48,
            13, 55, 11, 23, 23, 19, 16, 19, 16, 20, 18, 19, 19, 42, 42, 28,
        ];
        foreach ($anchos as $indice => $ancho) {
            $sheet->setColumnWidth($ancho, $indice + 1);
        }

        $sheet->setSheetView((new SheetView())->setFreezeRow(2));
    }

    private function escribirReporteCompleto(Writer $writer, Sheet $sheet): void
    {
        $encabezadoStyle = (new Style())
            ->setFontBold()
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor('E66C00')
            ->setCellAlignment('center')
            ->setCellVerticalAlignment('center')
            ->setShouldWrapText();
        $numeroStyle = (new Style())->setFormat('#,##0.0000');

        $writer->addRow(Row::fromValues(self::ENCABEZADOS_COMPLETO, $encabezadoStyle)->setHeight(32));

        $secuencia = 0;
        $query = $this->consulta->ordenarCronologicamente(
            $this->consulta->construir($this->consulta->filtros([]))->select('a.*')
        );

        foreach ($query->cursor() as $fila) {
            $writer->addRow(Row::fromValuesWithStyles([
                ++$secuencia,
                $fila->tipo_movimiento,
                $fila->fecha_movimiento,
                $fila->fecha_ingreso_compra,
                $fila->fecha_creacion_factura,
                $fila->fecha_emision_factura,
                $fila->factura_id,
                $fila->compra_id,
                $fila->comprobante_id,
                $fila->ajuste_id,
                $this->seguro($fila->numero_documento),
                $this->seguro($fila->cai),
                $fila->clasificacion_factura,
                $fila->estado_documento,
                $this->seguro($fila->motivo_documento),
                $this->seguro($fila->comentario_documento),
                $fila->producto_id,
                $this->seguro($fila->producto_nombre),
                $fila->lote_id,
                $this->seguro($fila->bodega_nombre),
                $this->seguro($fila->seccion_nombre),
                $this->seguro($fila->unidad_compra_nombre),
                $fila->factor_unidad_compra,
                $this->seguro($fila->unidad_venta_nombre),
                $fila->factor_unidad_venta,
                (float) $fila->cantidad_documento,
                (float) $fila->cantidad_base,
                (float) $fila->cantidad_movimiento_base,
                $fila->afecta_inventario ? 'SI' : 'NO',
                $this->seguro($fila->cliente_nombre),
                $this->seguro($fila->proveedor_nombre),
                $this->seguro($fila->usuario_nombre),
            ], null, [25 => $numeroStyle, 26 => $numeroStyle, 27 => $numeroStyle]));
        }

        $sheet->setAutoFilter(new AutoFilter(0, 1, 31, $secuencia + 1));
    }

    private function numeroComercial(object $movimiento): ?string
    {
        return match ($movimiento->tipo_movimiento) {
            'VENTA' => $movimiento->cai ?: $movimiento->numero_documento,
            'COMPROBANTE' => 'COMPROBANTE ' . $movimiento->numero_documento,
            'AJUSTE' => 'AJUSTE ' . $movimiento->numero_documento,
            default => $movimiento->numero_documento,
        };
    }

    private function fecha(?string $fecha): ?string
    {
        return $fecha ? date('d/m/Y', strtotime($fecha)) : null;
    }

    private function seguro(?string $valor): ?string
    {
        if ($valor !== null && preg_match('/^[=+\-@]/', $valor)) {
            return "'" . $valor;
        }

        return $valor;
    }
}
