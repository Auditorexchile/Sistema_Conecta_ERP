<?php
namespace App\Utils;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QRCodeGenerator {
    public function generate($data, $size = 300, $outputPath = null) {
        $qrCode = new QrCode($data);
        $qrCode->setSize($size);
        $qrCode->setMargin(10);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        if ($outputPath) {
            $result->saveToFile($outputPath);
            return $outputPath;
        }

        return $result->getDataUri();
    }

    public function generateForInvoice($invoiceNumber, $total, $rut) {
        $data = sprintf("FACTURA:%s|TOTAL:%s|RUT:%s", $invoiceNumber, $total, $rut);
        return $this->generate($data);
    }

    public function generateForProduct($productCode) {
        return $this->generate($productCode);
    }

    public function output($data, $size = 300) {
        header('Content-Type: image/png');
        $qrCode = new QrCode($data);
        $qrCode->setSize($size);
        $writer = new PngWriter();
        echo $writer->write($qrCode)->getString();
        exit;
    }
}
