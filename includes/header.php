<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SYSTEM_NAME ?> - Sistema Contable</title>

    <!-- CSS Propio - SIN DEPENDENCIAS EXTERNAS -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/reset.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/components.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/contabilidad.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
                <span class="icon">&#128200;</span> <?= SYSTEM_NAME ?>
            </a>

            <button class="navbar-toggler" id="navbarToggler" aria-label="Toggle navigation">
                <span class="icon">&#9776;</span>
            </button>

            <div class="navbar-menu" id="navbarMenu">
                <ul class="navbar-nav">
                    <!-- Selector de empresa -->
                    <?php
                    $db = Database::getInstance();
                    $empresas = $db->query("SELECT id_empresa, razon_social FROM emp_empresas WHERE activa = 1");
                    ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="empresaDropdown" role="button">
                            <span class="icon">&#127970;</span>
                            <?php
                            if (isset($_SESSION['id_empresa'])) {
                                $empresa = $db->queryOne("SELECT razon_social FROM emp_empresas WHERE id_empresa = :id", ['id' => $_SESSION['id_empresa']]);
                                echo htmlspecialchars($empresa['razon_social'] ?? 'Seleccionar Empresa');
                            } else {
                                echo 'Seleccionar Empresa';
                            }
                            ?>
                        </a>
                        <ul class="dropdown-menu" id="empresaMenu">
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
                            <span class="icon">&#128197;</span>
                            <?= date('F Y') ?>
                        </span>
                    </li>

                    <!-- Usuario -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button">
                            <span class="icon">&#128100;</span> <?= htmlspecialchars($_SESSION['nombre_completo']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" id="userMenu">
                            <li><a class="dropdown-item" href="?module=usuarios&action=perfil"><span class="icon">&#9998;</span> Mi Perfil</a></li>
                            <li><a class="dropdown-item" href="?module=usuarios&action=cambiar_password"><span class="icon">&#128273;</span> Cambiar Contraseña</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/logout.php"><span class="icon">&#128682;</span> Cerrar Sesión</a></li>
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
