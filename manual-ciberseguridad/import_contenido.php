<?php
// Script temporal para importar contenido SQL a la base de datos

require_once 'includes/config.php';

// Obtener conexión PDO
$db = Database::getInstance();
$conn = $db->getConnection();

// Leer el archivo SQL
$sql_file = __DIR__ . '/modulo1_contenido.sql';
$sql_content = file_get_contents($sql_file);

// Dividir en statements individuales
$statements = array_filter(
    array_map('trim', explode(';', $sql_content)),
    function($stmt) {
        // Filtrar comentarios y líneas vacías
        $stmt = trim($stmt);
        return !empty($stmt) &&
               !preg_match('/^--/', $stmt) &&
               !preg_match('/^USE /', $stmt);
    }
);

$success_count = 0;
$error_count = 0;
$errors = [];

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (empty($statement)) continue;

    try {
        $conn->exec($statement);
        $success_count++;
        echo "✓ Statement ejecutado exitosamente\n";
    } catch (PDOException $e) {
        $error_count++;
        $error_msg = $e->getMessage();
        $errors[] = substr($statement, 0, 100) . "... → " . $error_msg;
        echo "✗ Error: " . $error_msg . "\n";
    }
}

echo "\n=== RESUMEN DE IMPORTACIÓN ===\n";
echo "Exitosos: $success_count\n";
echo "Errores: $error_count\n";

if (!empty($errors)) {
    echo "\n=== DETALLES DE ERRORES ===\n";
    foreach ($errors as $error) {
        echo "$error\n\n";
    }
}

// Verificar qué se insertó
echo "\n=== VERIFICACIÓN ===\n";

$result = $conn->query("SELECT COUNT(*) as total FROM temas WHERE modulo_id = 1");
$row = $result->fetch();
echo "Temas insertados para Módulo 1: " . $row['total'] . "\n";

$result = $conn->query("SELECT COUNT(*) as total FROM casos_reales WHERE tema_id = 1");
$row = $result->fetch();
echo "Casos reales insertados para Tema 1: " . $row['total'] . "\n";

echo "\n✓ Proceso completado\n";
?>
