<?php
namespace App\Utils;

class FileManager {
    private $uploadPath;
    private $maxSize;
    private $allowedExtensions;

    public function __construct() {
        $this->uploadPath = STORAGE_PATH . '/uploads/';
        $this->maxSize = 10 * 1024 * 1024; // 10MB
        $this->allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'zip'];
    }

    public function upload($file, $category = 'general') {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new \Exception('No file uploaded');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedExtensions)) {
            throw new \Exception('Invalid file type');
        }

        if ($file['size'] > $this->maxSize) {
            throw new \Exception('File too large');
        }

        $filename = uniqid() . '_' . time() . '.' . $ext;
        $path = $this->uploadPath . $category . '/' . date('Y/m/');

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $fullPath = $path . $filename;
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new \Exception('Failed to move uploaded file');
        }

        return [
            'nombre_original' => $file['name'],
            'nombre_almacenado' => $filename,
            'ruta_archivo' => $fullPath,
            'tamano_bytes' => $file['size'],
            'extension' => $ext,
            'tipo_mime' => mime_content_type($fullPath),
            'hash_archivo' => hash_file('sha256', $fullPath)
        ];
    }

    public function delete($path) {
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }

    public function download($path, $filename = null) {
        if (!file_exists($path)) {
            throw new \Exception('File not found');
        }

        $filename = $filename ?? basename($path);
        header('Content-Type: ' . mime_content_type($path));
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function getFileInfo($path) {
        if (!file_exists($path)) {
            return null;
        }

        return [
            'size' => filesize($path),
            'modified' => filemtime($path),
            'mime' => mime_content_type($path),
            'extension' => pathinfo($path, PATHINFO_EXTENSION)
        ];
    }
}
