<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SYSTEM_NAME ?> - Sistema Contable</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <!-- DatePicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/contabilidad.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
                <i class="fas fa-chart-line"></i> <?= SYSTEM_NAME ?>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- Selector de empresa -->
                    <?php
                    $db = Database::getInstance();
                    $empresas = $db->query("SELECT id_empresa, razon_social FROM emp_empresas WHERE activa = 1");
                    ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="empresaDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-building"></i>
                            <?php
                            if (isset($_SESSION['id_empresa'])) {
                                $empresa = $db->queryOne("SELECT razon_social FROM emp_empresas WHERE id_empresa = :id", ['id' => $_SESSION['id_empresa']]);
                                echo htmlspecialchars($empresa['razon_social'] ?? 'Seleccionar Empresa');
                            } else {
                                echo 'Seleccionar Empresa';
                            }
                            ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="empresaDropdown">
                            <?php foreach ($empresas as $emp): ?>
                                <li>
                                    <a class="dropdown-item" href="?cambiar_empresa=<?= $emp['id_empresa'] ?>">
                                        <?= htmlspecialchars($emp['razon_social']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>

                    <!-- Periodo contable -->
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-calendar-alt"></i>
                            <?= date('F Y') ?>
                        </span>
                    </li>

                    <!-- Usuario -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['nombre_completo']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="?module=usuarios&action=perfil"><i class="fas fa-user-edit"></i> Mi Perfil</a></li>
                            <li><a class="dropdown-item" href="?module=usuarios&action=cambiar_password"><i class="fas fa-key"></i> Cambiar Contraseña</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="wrapper">
<?php
// Procesar cambio de empresa
if (isset($_GET['cambiar_empresa'])) {
    $_SESSION['id_empresa'] = (int)$_GET['cambiar_empresa'];
    redirect('/index.php');
}
?>
