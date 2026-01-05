<?php
namespace App\Services;

/**
 * Conecta ERP - Servicio de Copias de Seguridad
 * Gestión automática de backups de BD y archivos
 */

class BackupService {
    private $db;
    private $config;
    private $notificationService;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance();
        $this->config = $this->loadConfig();
        $this->notificationService = new NotificationService();
    }

    /**
     * Crea backup completo
     */
    public function createBackup($tipo = 'full') {
        $startTime = microtime(true);

        try {
            // Crear directorio de backup
            $backupDir = $this->createBackupDirectory();

            $backupFiles = [];

            // Backup de base de datos
            if ($tipo === 'full' || $tipo === 'database') {
                $dbBackup = $this->backupDatabase($backupDir);
                $backupFiles[] = $dbBackup;
            }

            // Backup de archivos
            if ($tipo === 'full' || $tipo === 'files') {
                $filesBackup = $this->backupFiles($backupDir);
                $backupFiles = array_merge($backupFiles, $filesBackup);
            }

            // Comprimir backup
            $zipFile = $this->compressBackup($backupDir, $backupFiles);

            // Calcular tamaño y hash
            $size = filesize($zipFile);
            $hash = hash_file('sha256', $zipFile);

            // Registrar en BD
            $idBackup = $this->registerBackup([
                'tipo' => $tipo,
                'ruta_archivo' => $zipFile,
                'tamano' => $size,
                'hash' => $hash,
                'duracion' => round(microtime(true) - $startTime, 2),
            ]);

            // Subir a almacenamiento remoto si está configurado
            if ($this->config['remote_storage']['enabled']) {
                $this->uploadToRemoteStorage($zipFile);
            }

            // Limpiar backups antiguos
            $this->cleanupOldBackups();

            // Notificar éxito
            $this->notificationService->notifyEvent('backup_completado', [
                'tipo' => $tipo,
                'size' => $this->formatBytes($size),
                'duration' => round(microtime(true) - $startTime, 2) . 's',
            ]);

            return [
                'success' => true,
                'id_backup' => $idBackup,
                'file' => $zipFile,
                'size' => $size,
                'duration' => round(microtime(true) - $startTime, 2),
            ];

        } catch (\Exception $e) {
            // Notificar error
            $this->notificationService->notifyEvent('error_integracion', [
                'integracion' => 'Backup',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Backup de base de datos
     */
    private function backupDatabase($backupDir) {
        $config = $this->config['database'];

        $filename = $backupDir . '/database_' . date('Y-m-d_H-i-s') . '.sql';

        // Usar mysqldump
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s 2>&1',
            escapeshellarg($config['user']),
            escapeshellarg($config['password']),
            escapeshellarg($config['host']),
            escapeshellarg($config['database']),
            escapeshellarg($filename)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("Error en mysqldump: " . implode("\n", $output));
        }

        if (!file_exists($filename) || filesize($filename) === 0) {
            throw new \Exception("El archivo de backup está vacío");
        }

        return $filename;
    }

    /**
     * Backup de archivos
     */
    private function backupFiles($backupDir) {
        $directories = $this->config['backup_directories'];
        $backupFiles = [];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $tarFile = $backupDir . '/' . basename($dir) . '_' . date('Y-m-d_H-i-s') . '.tar.gz';

            $command = sprintf(
                'tar -czf %s -C %s . 2>&1',
                escapeshellarg($tarFile),
                escapeshellarg($dir)
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($tarFile)) {
                $backupFiles[] = $tarFile;
            }
        }

        return $backupFiles;
    }

    /**
     * Comprime backup
     */
    private function compressBackup($backupDir, $files) {
        $zipFile = $this->config['backup_path'] . '/backup_' . date('Y-m-d_H-i-s') . '.zip';

        $zip = new \ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE) !== true) {
            throw new \Exception("No se pudo crear archivo ZIP");
        }

        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }

        $zip->close();

        // Eliminar archivos temporales
        foreach ($files as $file) {
            unlink($file);
        }

        // Eliminar directorio temporal
        rmdir($backupDir);

        return $zipFile;
    }

    /**
     * Crea directorio temporal para backup
     */
    private function createBackupDirectory() {
        $dir = $this->config['backup_path'] . '/temp_' . uniqid();

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    /**
     * Registra backup en BD
     */
    private function registerBackup($data) {
        $stmt = $this->db->prepare("
            INSERT INTO backups
            (tipo, ruta_archivo, tamano, hash, duracion, estado, created_at)
            VALUES (?, ?, ?, ?, ?, 'completado', NOW())
        ");

        $stmt->execute([
            $data['tipo'],
            $data['ruta_archivo'],
            $data['tamano'],
            $data['hash'],
            $data['duracion'],
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Sube backup a almacenamiento remoto
     */
    private function uploadToRemoteStorage($file) {
        $storage = $this->config['remote_storage']['type'];

        switch ($storage) {
            case 's3':
                return $this->uploadToS3($file);

            case 'ftp':
                return $this->uploadToFTP($file);

            case 'google_drive':
                return $this->uploadToGoogleDrive($file);

            default:
                return false;
        }
    }

    /**
     * Sube a Amazon S3
     */
    private function uploadToS3($file) {
        // Implementación con AWS SDK
        // require 'vendor/autoload.php';
        // $s3Client = new Aws\S3\S3Client([...]);
        // $result = $s3Client->putObject([...]);

        return true; // Placeholder
    }

    /**
     * Sube por FTP
     */
    private function uploadToFTP($file) {
        $config = $this->config['remote_storage']['ftp'];

        $conn = ftp_connect($config['host'], $config['port']);

        if (!$conn) {
            throw new \Exception("No se pudo conectar a FTP");
        }

        $login = ftp_login($conn, $config['user'], $config['password']);

        if (!$login) {
            ftp_close($conn);
            throw new \Exception("Error de autenticación FTP");
        }

        $remoteFile = $config['path'] . '/' . basename($file);
        $upload = ftp_put($conn, $remoteFile, $file, FTP_BINARY);

        ftp_close($conn);

        return $upload;
    }

    /**
     * Restaura backup
     */
    public function restoreBackup($idBackup) {
        $this->db->beginTransaction();

        try {
            // Obtener información del backup
            $stmt = $this->db->prepare("SELECT * FROM backups WHERE id = ?");
            $stmt->execute([$idBackup]);
            $backup = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$backup || !file_exists($backup['ruta_archivo'])) {
                throw new \Exception("Backup no encontrado");
            }

            // Verificar hash
            $hash = hash_file('sha256', $backup['ruta_archivo']);
            if ($hash !== $backup['hash']) {
                throw new \Exception("El archivo de backup está corrupto");
            }

            // Extraer ZIP
            $extractDir = $this->config['backup_path'] . '/restore_' . uniqid();
            mkdir($extractDir, 0755, true);

            $zip = new \ZipArchive();
            if ($zip->open($backup['ruta_archivo']) !== true) {
                throw new \Exception("No se pudo abrir archivo ZIP");
            }

            $zip->extractTo($extractDir);
            $zip->close();

            // Restaurar base de datos
            $sqlFile = glob($extractDir . '/database_*.sql')[0] ?? null;
            if ($sqlFile && $backup['tipo'] !== 'files') {
                $this->restoreDatabase($sqlFile);
            }

            // Restaurar archivos
            if ($backup['tipo'] !== 'database') {
                $this->restoreFiles($extractDir);
            }

            // Limpiar
            $this->deleteDirectory($extractDir);

            $this->db->commit();

            return ['success' => true];

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Restaura base de datos desde SQL
     */
    private function restoreDatabase($sqlFile) {
        $config = $this->config['database'];

        $command = sprintf(
            'mysql --user=%s --password=%s --host=%s %s < %s 2>&1',
            escapeshellarg($config['user']),
            escapeshellarg($config['password']),
            escapeshellarg($config['host']),
            escapeshellarg($config['database']),
            escapeshellarg($sqlFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("Error restaurando base de datos: " . implode("\n", $output));
        }

        return true;
    }

    /**
     * Restaura archivos
     */
    private function restoreFiles($extractDir) {
        $tarFiles = glob($extractDir . '/*.tar.gz');

        foreach ($tarFiles as $tarFile) {
            // Determinar directorio destino basado en el nombre
            $basename = basename($tarFile, '.tar.gz');
            $parts = explode('_', $basename);
            $dirName = $parts[0];

            $targetDir = null;
            foreach ($this->config['backup_directories'] as $dir) {
                if (basename($dir) === $dirName) {
                    $targetDir = $dir;
                    break;
                }
            }

            if (!$targetDir) {
                continue;
            }

            // Extraer
            $command = sprintf(
                'tar -xzf %s -C %s 2>&1',
                escapeshellarg($tarFile),
                escapeshellarg($targetDir)
            );

            exec($command, $output, $returnCode);
        }

        return true;
    }

    /**
     * Limpia backups antiguos
     */
    private function cleanupOldBackups() {
        $retention = $this->config['retention_days'];

        // Obtener backups antiguos
        $stmt = $this->db->prepare("
            SELECT *
            FROM backups
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$retention]);
        $oldBackups = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($oldBackups as $backup) {
            // Eliminar archivo
            if (file_exists($backup['ruta_archivo'])) {
                unlink($backup['ruta_archivo']);
            }

            // Eliminar registro
            $stmt = $this->db->prepare("DELETE FROM backups WHERE id = ?");
            $stmt->execute([$backup['id']]);
        }

        return count($oldBackups);
    }

    /**
     * Lista backups disponibles
     */
    public function listBackups($limit = 50) {
        $stmt = $this->db->prepare("
            SELECT *
            FROM backups
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Elimina directorio recursivamente
     */
    private function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        return rmdir($dir);
    }

    /**
     * Formatea bytes a formato legible
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Carga configuración
     */
    private function loadConfig() {
        return [
            'backup_path' => STORAGE_PATH . '/backups',
            'retention_days' => 30,
            'database' => [
                'host' => 'localhost',
                'user' => 'conectae_conectaerpuser',
                'password' => 'pt125824caraud',
                'database' => 'conectae_conectaerpbd',
            ],
            'backup_directories' => [
                STORAGE_PATH . '/uploads',
                STORAGE_PATH . '/documents',
                STORAGE_PATH . '/invoices',
            ],
            'remote_storage' => [
                'enabled' => false,
                'type' => 's3', // s3, ftp, google_drive
                'ftp' => [
                    'host' => '',
                    'port' => 21,
                    'user' => '',
                    'password' => '',
                    'path' => '/backups',
                ],
            ],
        ];
    }
}
