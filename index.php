<?php
/**
 * ConectaERP - Sistema de Gestión Empresarial
 * Punto de entrada principal
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ '/config/database.php';

session_start();

// Verificar si el usuario está autenticado
if (!isLoggedIn()) {
    redirect('/login.php');
}

// Incluir header
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';

// Obtener módulo y acción
$module = $_GET['module'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

// Rutas de módulos
$modulePath = MODULES_PATH . '/' . $module . '/controllers/' . $action . '.php';

?>

<div class="main-content">
    <div class="container-fluid">
        <?php
        // Mostrar mensajes
        $successMsg = getSuccessMessage();
        $errorMsg = getErrorMessage();

        if ($successMsg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?= $successMsg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= $errorMsg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php
        // Cargar controlador del módulo
        if (file_exists($modulePath)) {
            include $modulePath;
        } else {
            echo '<div class="alert alert-warning">Módulo no encontrado</div>';
        }
        ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
