<?php
/**
 * Conecta ERP - Instalador de Base de Datos
 * Este script ejecuta todos los schemas SQL en el orden correcto
 *
 * IMPORTANTE: Ejecutar solo una vez al instalar el sistema
 *
 * Uso: php install.php
 */

// Configuración de base de datos
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'conectae_conectaerpbd',
    'user' => 'conectae_conectaerpuser',
    'pass' => 'pt125824caraud',
    'charset' => 'utf8mb4'
];

// Archivos SQL en orden de ejecución
$sqlFiles = [
    '01_core_seguridad.sql',
    '02_empresas_configuracion.sql',
    '03_planes_suscripciones.sql',
    '04_productos_servicios.sql',
    '05_inventario.sql',
    '06_compras.sql',
    '07_ventas_facturacion.sql',
    '08_contabilidad.sql',
    '09_tesoreria.sql',
    '10_rrhh_previred.sql',
    '11_produccion.sql',
    '12_proyectos.sql',
    '13_configuracion.sql',
    '14_api_reportes.sql',
    '15_ecommerce.sql',
    '16_proyectos_calidad_mantenimiento.sql',
    '17_bi_avanzado.sql',
    '18_reloj_control.sql',
    '19_password_recovery.sql',
    '20_control_acceso_planes.sql',
    '21_ia_auditoria.sql',
    '22_crm_avanzado.sql',
    '23_fidelizacion.sql',
    '24_scm.sql',
    '25_integraciones_externas.sql',
    '26_auditoria_notificaciones_workflow.sql'
];

// Colores para consola
class ConsoleColor {
    public static $RESET = "\033[0m";
    public static $RED = "\033[31m";
    public static $GREEN = "\033[32m";
    public static $YELLOW = "\033[33m";
    public static $BLUE = "\033[34m";
    public static $MAGENTA = "\033[35m";
    public static $CYAN = "\033[36m";
}

function printMessage($message, $color = null) {
    $color = $color ?? ConsoleColor::$RESET;
    echo $color . $message . ConsoleColor::$RESET . PHP_EOL;
}

function printHeader($message) {
    echo PHP_EOL;
    echo str_repeat('=', 70) . PHP_EOL;
    printMessage($message, ConsoleColor::$CYAN);
    echo str_repeat('=', 70) . PHP_EOL;
}

function printSuccess($message) {
    printMessage("✓ $message", ConsoleColor::$GREEN);
}

function printError($message) {
    printMessage("✗ $message", ConsoleColor::$RED);
}

function printWarning($message) {
    printMessage("⚠ $message", ConsoleColor::$YELLOW);
}

function printInfo($message) {
    printMessage("ℹ $message", ConsoleColor::$BLUE);
}

// Inicio del proceso
printHeader("CONECTA ERP - INSTALADOR DE BASE DE DATOS");
printInfo("Iniciando instalación de base de datos...");
printInfo("Base de datos: " . $dbConfig['dbname']);
printInfo("Usuario: " . $dbConfig['user']);
echo PHP_EOL;

// Conexión a MySQL
try {
    printInfo("Conectando a MySQL...");
    $dsn = "mysql:host={$dbConfig['host']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    printSuccess("Conexión exitosa a MySQL");
} catch (PDOException $e) {
    printError("Error de conexión: " . $e->getMessage());
    exit(1);
}

// Verificar si la base de datos existe
try {
    printInfo("Verificando base de datos...");
    $stmt = $pdo->query("SHOW DATABASES LIKE '{$dbConfig['dbname']}'");
    $exists = $stmt->fetch();

    if ($exists) {
        printWarning("La base de datos '{$dbConfig['dbname']}' ya existe.");
        echo "¿Desea eliminarla y recrearla? (s/N): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);

        if (trim(strtolower($line)) === 's') {
            printInfo("Eliminando base de datos existente...");
            $pdo->exec("DROP DATABASE `{$dbConfig['dbname']}`");
            printSuccess("Base de datos eliminada");
        } else {
            printError("Instalación cancelada por el usuario");
            exit(0);
        }
    }

    // Crear base de datos
    printInfo("Creando base de datos...");
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbConfig['dbname']}`
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    printSuccess("Base de datos creada: {$dbConfig['dbname']}");

    // Seleccionar base de datos
    $pdo->exec("USE `{$dbConfig['dbname']}`");

} catch (PDOException $e) {
    printError("Error al crear base de datos: " . $e->getMessage());
    exit(1);
}

// Ejecutar archivos SQL
printHeader("EJECUTANDO SCHEMAS SQL");
$totalFiles = count($sqlFiles);
$successCount = 0;
$errorCount = 0;

foreach ($sqlFiles as $index => $file) {
    $fileNum = $index + 1;
    $filePath = __DIR__ . '/' . $file;

    printInfo("[$fileNum/$totalFiles] Procesando: $file");

    if (!file_exists($filePath)) {
        printError("Archivo no encontrado: $filePath");
        $errorCount++;
        continue;
    }

    try {
        // Leer archivo SQL
        $sql = file_get_contents($filePath);

        // Eliminar comentarios de línea
        $sql = preg_replace('/^--.*$/m', '', $sql);

        // Dividir por sentencias (separadas por ;)
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                return !empty($stmt) && $stmt !== '';
            }
        );

        // Ejecutar cada sentencia
        $stmtCount = 0;
        foreach ($statements as $statement) {
            if (trim($statement)) {
                $pdo->exec($statement);
                $stmtCount++;
            }
        }

        printSuccess("Ejecutado correctamente ($stmtCount sentencias)");
        $successCount++;

    } catch (PDOException $e) {
        printError("Error en $file: " . $e->getMessage());
        $errorCount++;

        // Preguntar si continuar
        echo "¿Desea continuar con los demás archivos? (S/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);

        if (trim(strtolower($line)) === 'n') {
            printError("Instalación cancelada");
            exit(1);
        }
    }
}

// Resumen final
printHeader("RESUMEN DE INSTALACIÓN");
printInfo("Total archivos: $totalFiles");
printSuccess("Ejecutados correctamente: $successCount");

if ($errorCount > 0) {
    printError("Con errores: $errorCount");
    echo PHP_EOL;
    printWarning("La instalación se completó con errores. Revise los mensajes anteriores.");
} else {
    echo PHP_EOL;
    printSuccess("¡INSTALACIÓN COMPLETADA EXITOSAMENTE!");
    echo PHP_EOL;
    printInfo("La base de datos está lista para usar.");
    printInfo("Puede acceder al sistema en: http://localhost/public/");
    echo PHP_EOL;
}

// Verificar tablas creadas
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $tableCount = count($tables);

    printInfo("Tablas creadas: $tableCount");

    // Opcional: mostrar listado
    if ($tableCount > 0 && $tableCount < 50) {
        echo PHP_EOL;
        printInfo("Listado de tablas:");
        foreach ($tables as $table) {
            echo "  • $table" . PHP_EOL;
        }
    }

} catch (PDOException $e) {
    printWarning("No se pudo obtener el listado de tablas");
}

echo PHP_EOL;
printHeader("FIN DE LA INSTALACIÓN");
echo PHP_EOL;

// Cerrar conexión
$pdo = null;
exit($errorCount > 0 ? 1 : 0);
