<?php
require_once 'includes/config.php';
requireLogin();

$modulo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$modulo_id) {
    header('Location: dashboard.php');
    exit();
}

$db = Database::getInstance()->getConnection();

// Obtener información del módulo
$stmt = $db->prepare("SELECT * FROM modulos WHERE id = ? AND activo = 1");
$stmt->execute([$modulo_id]);
$modulo = $stmt->fetch();

if (!$modulo) {
    header('Location: dashboard.php');
    exit();
}

// Obtener temas del módulo
$stmt = $db->prepare("SELECT * FROM temas WHERE modulo_id = ? AND activo = 1 ORDER BY orden");
$stmt->execute([$modulo_id]);
$temas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo <?php echo $modulo['numero']; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .modulo-detail {
        background: white;
        padding: 3rem;
        border-radius: 1rem;
        box-shadow: var(--shadow-lg);
        margin-bottom: 2rem;
    }
    .modulo-detail-header {
        border-bottom: 3px solid var(--primary);
        padding-bottom: 2rem;
        margin-bottom: 2rem;
    }
    .modulo-numero-big {
        display: inline-block;
        background: linear-gradient(135deg, var(--primary-light), var(--primary));
        color: white;
        padding: 0.5rem 2rem;
        border-radius: 3rem;
        font-size: 0.9375rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }
    .temas-list {
        display: grid;
        gap: 1.5rem;
    }
    .tema-item {
        background: var(--light);
        padding: 1.5rem;
        border-radius: 0.75rem;
        border-left: 4px solid var(--primary);
        transition: var(--transition);
    }
    .tema-item:hover {
        transform: translateX(5px);
        box-shadow: var(--shadow-md);
    }
    .tema-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    .tema-numero {
        background: var(--primary);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 1.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
    }
    .tema-stats {
        display: flex;
        gap: 1rem;
        font-size: 0.875rem;
        color: var(--gray);
    }
    .tema-stats span {
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }
    .tema-item h3 {
        font-size: 1.125rem;
        color: var(--dark);
        margin-bottom: 0.75rem;
    }
    .tema-content {
        color: var(--gray);
        line-height: 1.6;
        margin-bottom: 1rem;
    }
    .btn-tema {
        padding: 0.625rem 1.5rem;
        font-size: 0.9375rem;
    }
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 2rem;
    }
    .back-link:hover {
        color: var(--primary-dark);
    }
    </style>
</head>
<body>
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

    <div class="container main-content">
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>

        <div class="modulo-detail">
            <div class="modulo-detail-header">
                <span class="modulo-numero-big">MÓDULO <?php echo $modulo['numero']; ?></span>
                <h1 style="font-size: 2rem; color: var(--primary); margin-bottom: 1rem;">
                    <?php echo $modulo['titulo']; ?>
                </h1>
                <p style="font-size: 1.125rem; color: var(--gray);">
                    <?php echo $modulo['descripcion']; ?>
                </p>
            </div>

            <h2 style="margin-bottom: 2rem; color: var(--dark);">
                <i class="fas fa-list"></i> Temas del Módulo
            </h2>

            <?php if (empty($temas)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Este módulo aún no tiene temas disponibles. Estamos trabajando en el contenido.
                </div>
            <?php else: ?>
                <div class="temas-list">
                    <?php foreach ($temas as $tema):
                        // Contar casos y ejercicios
                        $stmt = $db->prepare("SELECT COUNT(*) as total FROM casos_reales WHERE tema_id = ?");
                        $stmt->execute([$tema['id']]);
                        $casos_count = $stmt->fetch()['total'];

                        $stmt = $db->prepare("SELECT COUNT(*) as total FROM ejercicios WHERE tema_id = ?");
                        $stmt->execute([$tema['id']]);
                        $ejercicios_count = $stmt->fetch()['total'];
                    ?>
                        <div class="tema-item">
                            <div class="tema-header">
                                <span class="tema-numero">Tema <?php echo $tema['numero']; ?></span>
                                <div class="tema-stats">
                                    <span><i class="fas fa-briefcase"></i> <?php echo $casos_count; ?> Casos</span>
                                    <span><i class="fas fa-tasks"></i> <?php echo $ejercicios_count; ?> Ejercicios</span>
                                </div>
                            </div>
                            <h3><?php echo $tema['titulo']; ?></h3>
                            <div class="tema-content">
                                <?php echo nl2br(substr($tema['contenido'], 0, 250)); ?>...
                            </div>
                            <a href="tema.php?id=<?php echo $tema['id']; ?>" class="btn btn-primary btn-tema">
                                <i class="fas fa-book-open"></i> Ver Contenido Completo
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

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
