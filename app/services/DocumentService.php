<?php
namespace App\Services;

/**
 * Conecta ERP - Servicio de Gestión de Documentos
 * Manejo de archivos adjuntos, versiones y firmas digitales
 */

class DocumentService {
    private $db;
    private $fileManager;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance();
        $this->fileManager = new \App\Utils\FileManager();
    }

    /**
     * Sube documento adjunto
     */
    public function uploadDocument($file, $data) {
        $this->db->beginTransaction();

        try {
            // Validar archivo
            $this->validateFile($file);

            // Subir archivo
            $fileData = $this->fileManager->upload($file, [
                'categoria' => $data['categoria'] ?? 'documentos',
                'subcategoria' => $data['tipo_documento'] ?? 'general',
            ]);

            // Crear registro de documento
            $idDocumento = $this->createDocumentRecord([
                'id_empresa' => $data['id_empresa'],
                'tipo_entidad' => $data['tipo_entidad'] ?? null,
                'id_entidad' => $data['id_entidad'] ?? null,
                'tipo_documento' => $data['tipo_documento'] ?? 'general',
                'nombre_archivo' => $fileData['nombre'],
                'ruta_archivo' => $fileData['ruta'],
                'tamano' => $fileData['tamano'],
                'mime_type' => $fileData['mime_type'],
                'hash' => $fileData['hash'],
                'extension' => $fileData['extension'],
                'descripcion' => $data['descripcion'] ?? null,
                'id_usuario_subida' => $_SESSION['id_usuario'] ?? null,
            ]);

            // Crear versión inicial
            $this->createVersion($idDocumento, 1, 'Versión inicial', $fileData);

            $this->db->commit();

            return [
                'success' => true,
                'id_documento' => $idDocumento,
                'file_data' => $fileData,
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Crea registro de documento
     */
    private function createDocumentRecord($data) {
        $stmt = $this->db->prepare("
            INSERT INTO documentos_adjuntos
            (id_empresa, tipo_entidad, id_entidad, tipo_documento, nombre_archivo,
             ruta_archivo, tamano, mime_type, hash, extension, descripcion,
             id_usuario_subida, version_actual, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ");

        $stmt->execute([
            $data['id_empresa'],
            $data['tipo_entidad'],
            $data['id_entidad'],
            $data['tipo_documento'],
            $data['nombre_archivo'],
            $data['ruta_archivo'],
            $data['tamano'],
            $data['mime_type'],
            $data['hash'],
            $data['extension'],
            $data['descripcion'],
            $data['id_usuario_subida'],
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Crea versión de documento
     */
    private function createVersion($idDocumento, $numeroVersion, $comentario, $fileData) {
        $stmt = $this->db->prepare("
            INSERT INTO versiones_documentos
            (id_documento, numero_version, ruta_archivo, tamano, hash,
             comentario, id_usuario, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        return $stmt->execute([
            $idDocumento,
            $numeroVersion,
            $fileData['ruta'],
            $fileData['tamano'],
            $fileData['hash'],
            $comentario,
            $_SESSION['id_usuario'] ?? null,
        ]);
    }

    /**
     * Actualiza documento (nueva versión)
     */
    public function updateDocument($idDocumento, $file, $comentario = null) {
        $this->db->beginTransaction();

        try {
            // Obtener documento actual
            $documento = $this->getDocument($idDocumento);

            if (!$documento) {
                throw new \Exception("Documento no encontrado");
            }

            // Subir nueva versión
            $fileData = $this->fileManager->upload($file, [
                'categoria' => 'documentos',
                'subcategoria' => $documento['tipo_documento'],
            ]);

            // Incrementar versión
            $nuevaVersion = $documento['version_actual'] + 1;

            // Crear registro de versión
            $this->createVersion($idDocumento, $nuevaVersion, $comentario, $fileData);

            // Actualizar documento
            $stmt = $this->db->prepare("
                UPDATE documentos_adjuntos
                SET version_actual = ?,
                    ruta_archivo = ?,
                    tamano = ?,
                    hash = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");

            $stmt->execute([
                $nuevaVersion,
                $fileData['ruta'],
                $fileData['tamano'],
                $fileData['hash'],
                $idDocumento,
            ]);

            $this->db->commit();

            return [
                'success' => true,
                'id_documento' => $idDocumento,
                'version' => $nuevaVersion,
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Firma documento digitalmente
     */
    public function signDocument($idDocumento, $idUsuario, $certificado = null) {
        $this->db->beginTransaction();

        try {
            // Obtener documento
            $documento = $this->getDocument($idDocumento);

            if (!$documento) {
                throw new \Exception("Documento no encontrado");
            }

            // Generar hash del documento
            $hash = hash_file('sha256', $documento['ruta_archivo']);

            // Crear firma digital
            $stmt = $this->db->prepare("
                INSERT INTO firmas_digitales
                (id_documento, id_usuario, hash_documento, certificado,
                 fecha_firma, ip_address, user_agent)
                VALUES (?, ?, ?, ?, NOW(), ?, ?)
            ");

            $stmt->execute([
                $idDocumento,
                $idUsuario,
                $hash,
                $certificado,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            ]);

            $idFirma = $this->db->lastInsertId();

            // Marcar documento como firmado
            $stmt = $this->db->prepare("
                UPDATE documentos_adjuntos
                SET firmado = 1,
                    fecha_firma = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$idDocumento]);

            $this->db->commit();

            return [
                'success' => true,
                'id_firma' => $idFirma,
                'hash' => $hash,
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Verifica firma digital
     */
    public function verifySignature($idDocumento) {
        // Obtener firmas del documento
        $stmt = $this->db->prepare("
            SELECT fd.*, u.nombre as usuario_nombre
            FROM firmas_digitales fd
            INNER JOIN usuarios u ON fd.id_usuario = u.id
            WHERE fd.id_documento = ?
            ORDER BY fd.fecha_firma DESC
        ");
        $stmt->execute([$idDocumento]);
        $firmas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($firmas)) {
            return [
                'firmado' => false,
                'message' => 'Documento no firmado',
            ];
        }

        // Obtener documento actual
        $documento = $this->getDocument($idDocumento);
        $hashActual = hash_file('sha256', $documento['ruta_archivo']);

        $firmasValidas = [];
        $firmasInvalidas = [];

        foreach ($firmas as $firma) {
            if ($firma['hash_documento'] === $hashActual) {
                $firmasValidas[] = $firma;
            } else {
                $firmasInvalidas[] = $firma;
            }
        }

        return [
            'firmado' => true,
            'valido' => count($firmasValidas) > 0,
            'firmas_validas' => $firmasValidas,
            'firmas_invalidas' => $firmasInvalidas,
            'hash_actual' => $hashActual,
        ];
    }

    /**
     * Descarga documento
     */
    public function downloadDocument($idDocumento, $version = null) {
        if ($version) {
            // Descargar versión específica
            $stmt = $this->db->prepare("
                SELECT *
                FROM versiones_documentos
                WHERE id_documento = ? AND numero_version = ?
            ");
            $stmt->execute([$idDocumento, $version]);
            $documento = $stmt->fetch(\PDO::FETCH_ASSOC);
        } else {
            // Descargar versión actual
            $documento = $this->getDocument($idDocumento);
        }

        if (!$documento || !file_exists($documento['ruta_archivo'])) {
            throw new \Exception("Documento no encontrado");
        }

        return $this->fileManager->download($documento['ruta_archivo'], $documento['nombre_archivo']);
    }

    /**
     * Elimina documento
     */
    public function deleteDocument($idDocumento) {
        $this->db->beginTransaction();

        try {
            $documento = $this->getDocument($idDocumento);

            if (!$documento) {
                throw new \Exception("Documento no encontrado");
            }

            // Eliminar archivo físico
            $this->fileManager->delete($documento['ruta_archivo']);

            // Eliminar versiones
            $stmt = $this->db->prepare("DELETE FROM versiones_documentos WHERE id_documento = ?");
            $stmt->execute([$idDocumento]);

            // Eliminar firmas
            $stmt = $this->db->prepare("DELETE FROM firmas_digitales WHERE id_documento = ?");
            $stmt->execute([$idDocumento]);

            // Eliminar registro
            $stmt = $this->db->prepare("DELETE FROM documentos_adjuntos WHERE id = ?");
            $stmt->execute([$idDocumento]);

            $this->db->commit();

            return ['success' => true];

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene documento por ID
     */
    private function getDocument($idDocumento) {
        $stmt = $this->db->prepare("SELECT * FROM documentos_adjuntos WHERE id = ?");
        $stmt->execute([$idDocumento]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene documentos de una entidad
     */
    public function getDocumentsByEntity($tipoEntidad, $idEntidad) {
        $stmt = $this->db->prepare("
            SELECT da.*, u.nombre as usuario_nombre
            FROM documentos_adjuntos da
            LEFT JOIN usuarios u ON da.id_usuario_subida = u.id
            WHERE da.tipo_entidad = ?
            AND da.id_entidad = ?
            ORDER BY da.created_at DESC
        ");
        $stmt->execute([$tipoEntidad, $idEntidad]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene historial de versiones
     */
    public function getVersionHistory($idDocumento) {
        $stmt = $this->db->prepare("
            SELECT vd.*, u.nombre as usuario_nombre
            FROM versiones_documentos vd
            LEFT JOIN usuarios u ON vd.id_usuario = u.id
            WHERE vd.id_documento = ?
            ORDER BY vd.numero_version DESC
        ");
        $stmt->execute([$idDocumento]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Valida archivo
     */
    private function validateFile($file) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new \Exception("Archivo no válido");
        }

        $maxSize = 50 * 1024 * 1024; // 50MB
        if ($file['size'] > $maxSize) {
            throw new \Exception("El archivo excede el tamaño máximo permitido (50MB)");
        }

        return true;
    }
}
