<?php
namespace App\Utils;

use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorHTML;

class BarcodeGenerator {
    private $generator;

    public function __construct($type = 'png') {
        $this->generator = $type === 'png' ? new BarcodeGeneratorPNG() : new BarcodeGeneratorHTML();
    }

    public function generate($code, $type = 'EAN13', $widthFactor = 2, $height = 50) {
        return $this->generator->getBarcode($code, constant("Picqer\\Barcode\\BarcodeGenerator::TYPE_{$type}"), $widthFactor, $height);
    }

    public function generateEAN13($code) {
        return $this->generate($code, 'EAN13');
    }

    public function generateCode128($code) {
        return $this->generate($code, 'CODE_128');
    }

    public function output($code, $type = 'EAN13') {
        header('Content-Type: image/png');
        echo $this->generate($code, $type);
        exit;
    }

    public function saveToFile($code, $path, $type = 'EAN13') {
        $barcode = $this->generate($code, $type);
        return file_put_contents($path, $barcode);
    }
}
