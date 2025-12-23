<?php
require_once 'includes/config.php';
requireLogin();

// Obtener estadísticas
$db = Database::getInstance()->getConnection();

$stmt = $db->query("SELECT COUNT(*) as total FROM modulos WHERE activo = 1");
$total_modulos = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM temas WHERE activo = 1");
$total_temas = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM casos_reales");
$total_casos = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM ejercicios");
$total_ejercicios = $stmt->fetch()['total'];

// Obtener módulos
$stmt = $db->query("SELECT * FROM modulos WHERE activo = 1 ORDER BY orden");
$modulos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo-section">
                    <img src="assets/img/logo.png" alt="AuditorEx Chile" onerror="this.style.display='none'">
                    <div>
                        <h1>AuditorEx Chile SpA</h1>
                        <p>Manual de Ciberseguridad</p>
                    </div>
                </div>
                <div class="user-section">
                    <span><i class="fas fa-user-circle"></i> <?php echo $_SESSION['nombre_completo']; ?></span>
                    <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Salir</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container main-content">
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_modulos; ?></h3>
                    <p>Módulos</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_temas; ?></h3>
                    <p>Temas</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_casos; ?></h3>
                    <p>Casos Reales</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_ejercicios; ?></h3>
                    <p>Ejercicios</p>
                </div>
            </div>
        </div>

        <!-- Módulos -->
        <div class="section-header">
            <h2><i class="fas fa-graduation-cap"></i> Módulos del Programa</h2>
            <p>Programa Completo de Especialización en Ciberseguridad</p>
        </div>

        <div class="modulos-grid">
            <?php foreach ($modulos as $modulo): ?>
                <div class="modulo-card">
                    <div class="modulo-header">
                        <span class="modulo-numero">Módulo <?php echo $modulo['numero']; ?></span>
                        <?php
                        $stmt = $db->prepare("SELECT COUNT(*) as total FROM temas WHERE modulo_id = ? AND activo = 1");
                        $stmt->execute([$modulo['id']]);
                        $temas_count = $stmt->fetch()['total'];
                        ?>
                        <span class="modulo-temas"><?php echo $temas_count; ?> Temas</span>
                    </div>
                    <h3><?php echo $modulo['titulo']; ?></h3>
                    <p><?php echo $modulo['descripcion']; ?></p>
                    <div class="modulo-actions">
                        <a href="modulo.php?id=<?php echo $modulo['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-play"></i> Ver Módulo
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 AuditorEx Chile SpA. Todos los derechos reservados.</p>
            <p>
                <i class="fas fa-envelope"></i> gerencia@auditorexchile.cl |
                <i class="fas fa-phone"></i> +56 9 8575 4559
            </p>
        </div>
    </footer>
</body>
</html>
