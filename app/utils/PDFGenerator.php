<?php
/**
 * Conecta ERP - PDF Generator
 * Generación de PDFs (facturas, reportes, etc)
 */

namespace App\Utils;

use Dompdf\Dompdf;
use Dompdf\Options;

class PDFGenerator
{
    private $dompdf;
    private $options;

    public function __construct()
    {
        $this->options = new Options();
        $this->options->set('isRemoteEnabled', true);
        $this->options->set('isHtml5ParserEnabled', true);
        $this->options->set('defaultFont', 'DejaVu Sans');
        $this->options->set('chroot', realpath(''));

        $this->dompdf = new Dompdf($this->options);
    }

    /**
     * Generar PDF desde HTML
     */
    public function generateFromHTML($html, $filename = 'document.pdf', $options = [])
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper($options['paper'] ?? 'letter', $options['orientation'] ?? 'portrait');
        $this->dompdf->render();

        if ($options['output'] ?? 'download' === 'download') {
            $this->dompdf->stream($filename, ['Attachment' => true]);
        } elseif ($options['output'] === 'save') {
            $output = $this->dompdf->output();
            file_put_contents($options['path'] ?? STORAGE_PATH . '/temp/' . $filename, $output);
            return $options['path'] ?? STORAGE_PATH . '/temp/' . $filename;
        } else {
            return $this->dompdf->output();
        }
    }

    /**
     * Generar factura en PDF
     */
    public function generateInvoice($invoiceData, $output = 'download')
    {
        $html = $this->loadTemplate('invoice', $invoiceData);
        return $this->generateFromHTML($html, "Factura_{$invoiceData['numero']}.pdf", [
            'output' => $output,
            'paper' => 'letter',
            'orientation' => 'portrait'
        ]);
    }

    /**
     * Generar reporte en PDF
     */
    public function generateReport($reportData, $template = 'report', $output = 'download')
    {
        $html = $this->loadTemplate($template, $reportData);
        return $this->generateFromHTML($html, "Reporte_{$reportData['titulo']}.pdf", [
            'output' => $output,
            'paper' => 'letter',
            'orientation' => $reportData['orientation'] ?? 'portrait'
        ]);
    }

    /**
     * Cargar plantilla HTML
     */
    private function loadTemplate($template, $data)
    {
        $templatePath = APP_PATH . "/views/pdf/{$template}.php";

        if (!file_exists($templatePath)) {
            throw new \Exception("Template not found: {$template}");
        }

        extract($data);
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    /**
     * Agregar marca de agua
     */
    public function addWatermark($text, $options = [])
    {
        // Implementar watermark
    }

    /**
     * Generar código QR en PDF
     */
    public function addQRCode($data, $position = 'bottom-right')
    {
        // Implementar QR code
    }
}
