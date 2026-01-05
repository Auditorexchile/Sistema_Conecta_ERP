<?php
/**
 * Conecta ERP - Excel Generator
 * Generación de archivos Excel (reportes, exportaciones)
 */

namespace App\Utils;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExcelGenerator
{
    private $spreadsheet;
    private $activeSheet;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->activeSheet = $this->spreadsheet->getActiveSheet();
    }

    /**
     * Generar Excel desde array de datos
     */
    public function generateFromArray($data, $headers = [], $filename = 'export.xlsx', $options = [])
    {
        // Headers
        if (!empty($headers)) {
            $col = 'A';
            foreach ($headers as $header) {
                $this->activeSheet->setCellValue($col . '1', $header);
                $this->activeSheet->getStyle($col . '1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $col++;
            }
        }

        // Data
        $row = 2;
        foreach ($data as $rowData) {
            $col = 'A';
            foreach ($rowData as $value) {
                $this->activeSheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }

        // Auto-size columns
        foreach (range('A', $col) as $columnID) {
            $this->activeSheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        return $this->save($filename, $options);
    }

    /**
     * Exportar reporte a Excel
     */
    public function exportReport($reportData, $filename = 'reporte.xlsx')
    {
        $this->activeSheet->setTitle($reportData['titulo'] ?? 'Reporte');

        // Título
        $this->activeSheet->setCellValue('A1', $reportData['titulo'] ?? 'Reporte');
        $this->activeSheet->mergeCells('A1:' . chr(64 + count($reportData['headers'])) . '1');
        $this->activeSheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Fecha
        $this->activeSheet->setCellValue('A2', 'Fecha: ' . date('d/m/Y H:i'));

        // Datos
        return $this->generateFromArray($reportData['data'], $reportData['headers'], $filename, [
            'start_row' => 4
        ]);
    }

    /**
     * Guardar archivo
     */
    private function save($filename, $options = [])
    {
        $writer = new Xlsx($this->spreadsheet);

        if ($options['output'] ?? 'download' === 'download') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;
        } else {
            $path = $options['path'] ?? STORAGE_PATH . '/temp/' . $filename;
            $writer->save($path);
            return $path;
        }
    }

    /**
     * Agregar hoja
     */
    public function addSheet($title)
    {
        $this->activeSheet = $this->spreadsheet->createSheet();
        $this->activeSheet->setTitle($title);
    }

    /**
     * Aplicar estilos
     */
    public function applyStyles($range, $styles)
    {
        $this->activeSheet->getStyle($range)->applyFromArray($styles);
    }
}
