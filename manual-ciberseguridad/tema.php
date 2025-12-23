<?php
require_once 'includes/config.php';
requireLogin();

$tema_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$tema_id) {
    header('Location: dashboard.php');
    exit();
}

$db = Database::getInstance()->getConnection();

// Obtener tema
$stmt = $db->prepare("
    SELECT t.*, m.numero as modulo_numero, m.titulo as modulo_titulo, m.id as modulo_id
    FROM temas t
    JOIN modulos m ON t.modulo_id = m.id
    WHERE t.id = ? AND t.activo = 1
");
$stmt->execute([$tema_id]);
$tema = $stmt->fetch();

if (!$tema) {
    header('Location: dashboard.php');
    exit();
}

// Obtener casos reales
$stmt = $db->prepare("SELECT * FROM casos_reales WHERE tema_id = ? ORDER BY numero");
$stmt->execute([$tema_id]);
$casos = $stmt->fetchAll();

// Obtener ejercicios
$stmt = $db->prepare("SELECT * FROM ejercicios WHERE tema_id = ? ORDER BY numero");
$stmt->execute([$tema_id]);
$ejercicios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tema['titulo']; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .tema-page { background: var(--light); padding-bottom: 4rem; }
    .breadcrumb { padding: 1rem 0; color: var(--gray); font-size: 0.9375rem; }
    .breadcrumb a { color: var(--primary); }
    .breadcrumb a:hover { text-decoration: underline; }
    .tema-content-card { background: white; padding: 3rem; border-radius: 1rem; box-shadow: var(--shadow-lg); margin-bottom: 2rem; }
    .tema-header-main { border-bottom: 3px solid var(--primary); padding-bottom: 2rem; margin-bottom: 2rem; }
    .tema-badge { display: inline-block; background: linear-gradient(135deg, var(--primary-light), var(--primary)); color: white; padding: 0.5rem 1.5rem; border-radius: 2rem; font-size: 0.875rem; font-weight: 700; margin-bottom: 1rem; }
    .tema-content-text { font-size: 1.0625rem; line-height: 1.8; color: var(--dark); white-space: pre-line; }
    .section-divider { margin: 3rem 0; border-bottom: 2px solid var(--light); }
    .casos-grid, .ejercicios-grid { display: grid; gap: 1.5rem; margin-top: 2rem; }
    .caso-card, .ejercicio-card { background: white; padding: 2rem; border-radius: 0.75rem; box-shadow: var(--shadow-md); transition: var(--transition); border-left: 4px solid var(--primary); }
    .caso-card:hover, .ejercicio-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
    .caso-header, .ejercicio-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .caso-numero, .ejercicio-numero { background: var(--primary); color: white; padding: 0.375rem 1rem; border-radius: 1.5rem; font-size: 0.875rem; font-weight: 600; }
    .caso-empresa { background: var(--light); padding: 0.375rem 1rem; border-radius: 1.5rem; font-size: 0.875rem; color: var(--gray); }
    .caso-title, .ejercicio-title { font-size: 1.25rem; color: var(--primary); margin-bottom: 1rem; font-weight: 600; }
    .caso-section, .ejercicio-section { margin-bottom: 1.5rem; }
    .caso-section h4, .ejercicio-section h4 { color: var(--dark); font-size: 1rem; margin-bottom: 0.75rem; font-weight: 600; }
    .caso-section p, .ejercicio-section p, .caso-section ul { color: var(--gray); line-height: 1.7; }
    .dificultad-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.8125rem; font-weight: 600; }
    .dificultad-basico { background: #d1f2eb; color: #0a6e4e; }
    .dificultad-intermedio { background: #fff3cd; color: #856404; }
    .dificultad-avanzado { background: #f8d7da; color: #721c24; }
    .ejercicio-meta { display: flex; gap: 1rem; margin-bottom: 1rem; font-size: 0.9375rem; color: var(--gray); }
    .ejercicio-meta span { display: flex; align-items: center; gap: 0.375rem; }
    </style>
</head>
<body class="tema-page">
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

    <div class="container">
        <div class="breadcrumb">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a> /
            <a href="modulo.php?id=<?php echo $tema['modulo_id']; ?>">Módulo <?php echo $tema['modulo_numero']; ?></a> /
            <strong><?php echo $tema['titulo']; ?></strong>
        </div>

        <!-- Contenido del Tema -->
        <div class="tema-content-card">
            <div class="tema-header-main">
                <span class="tema-badge">TEMA <?php echo $tema['numero']; ?> - MÓDULO <?php echo $tema['modulo_numero']; ?></span>
                <h1 style="font-size: 2.25rem; color: var(--primary); margin-bottom: 0.5rem;">
                    <?php echo $tema['titulo']; ?>
                </h1>
            </div>
            <div class="tema-content-text">
                <?php echo $tema['contenido'] ? nl2br($tema['contenido']) : '<p style="color: var(--gray);">Contenido en desarrollo...</p>'; ?>
            </div>
        </div>

        <!-- Casos Reales -->
        <?php if (!empty($casos)): ?>
        <div class="section-divider"></div>
        <div class="tema-content-card">
            <h2 style="font-size: 2rem; color: var(--primary); margin-bottom: 1.5rem;">
                <i class="fas fa-briefcase"></i> Casos Reales
                <span style="font-size: 1rem; color: var(--gray); font-weight: normal;">(<?php echo count($casos); ?> casos documentados)</span>
            </h2>
            <div class="casos-grid">
                <?php foreach ($casos as $caso): ?>
                <div class="caso-card">
                    <div class="caso-header">
                        <span class="caso-numero">CASO #<?php echo $caso['numero']; ?></span>
                        <?php if ($caso['empresa']): ?>
                        <span class="caso-empresa">
                            <i class="fas fa-building"></i> <?php echo $caso['empresa']; ?>
                            <?php if ($caso['pais']): ?>- <?php echo $caso['pais']; ?><?php endif; ?>
                            <?php if ($caso['anio']): ?>(<?php echo $caso['anio']; ?>)<?php endif; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <h3 class="caso-title"><?php echo $caso['titulo']; ?></h3>
                    
                    <?php if ($caso['descripcion']): ?>
                    <div class="caso-section">
                        <h4><i class="fas fa-align-left"></i> Descripción</h4>
                        <p><?php echo nl2br($caso['descripcion']); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($caso['analisis']): ?>
                    <div class="caso-section">
                        <h4><i class="fas fa-search"></i> Análisis Técnico</h4>
                        <p><?php echo nl2br($caso['analisis']); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($caso['consecuencias']): ?>
                    <div class="caso-section">
                        <h4><i class="fas fa-exclamation-triangle"></i> Consecuencias</h4>
                        <p><?php echo nl2br($caso['consecuencias']); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($caso['lecciones_aprendidas']): ?>
                    <div class="caso-section">
                        <h4><i class="fas fa-lightbulb"></i> Lecciones Aprendidas</h4>
                        <p><?php echo nl2br($caso['lecciones_aprendidas']); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($caso['referencias']): ?>
                    <div class="caso-section">
                        <h4><i class="fas fa-link"></i> Referencias</h4>
                        <p><?php echo nl2br($caso['referencias']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Ejercicios Prácticos -->
        <?php if (!empty($ejercicios)): ?>
        <div class="section-divider"></div>
        <div class="tema-content-card">
            <h2 style="font-size: 2rem; color: var(--primary); margin-bottom: 1.5rem;">
                <i class="fas fa-tasks"></i> Ejercicios Prácticos
                <span style="font-size: 1rem; color: var(--gray); font-weight: normal;">(<?php echo count($ejercicios); ?> ejercicios)</span>
            </h2>
            <div class="ejercicios-grid">
                <?php foreach ($ejercicios as $ejercicio): ?>
                <div class="ejercicio-card">
                    <div class="ejercicio-header">
                        <span class="ejercicio-numero">EJERCICIO #<?php echo $ejercicio['numero']; ?></span>
                        <span class="dificultad-badge dificultad-<?php echo $ejercicio['dificultad']; ?>">
                            <?php echo strtoupper($ejercicio['dificultad']); ?>
                        </span>
                    </div>
                    <h3 class="ejercicio-title"><?php echo $ejercicio['titulo']; ?></h3>
                    
                    <div class="ejercicio-meta">
                        <span><i class="fas fa-tag"></i> <?php echo ucfirst($ejercicio['tipo']); ?></span>
                        <?php if ($ejercicio['tiempo_estimado']): ?>
                        <span><i class="fas fa-clock"></i> <?php echo $ejercicio['tiempo_estimado']; ?> min</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($ejercicio['enunciado']): ?>
                    <div class="ejercicio-section">
                        <h4><i class="fas fa-file-alt"></i> Enunciado</h4>
                        <p><?php echo nl2br($ejercicio['enunciado']); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($ejercicio['objetivos']): ?>
                    <div class="ejercicio-section">
                        <h4><i class="fas fa-bullseye"></i> Objetivos</h4>
                        <p><?php echo nl2br($ejercicio['objetivos']); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($ejercicio['pasos']): ?>
                    <div class="ejercicio-section">
                        <h4><i class="fas fa-list-ol"></i> Pasos a Seguir</h4>
                        <p><?php echo nl2br($ejercicio['pasos']); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($ejercicio['recursos_necesarios']): ?>
                    <div class="ejercicio-section">
                        <h4><i class="fas fa-toolbox"></i> Recursos Necesarios</h4>
                        <p><?php echo nl2br($ejercicio['recursos_necesarios']); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($ejercicio['solucion']): ?>
                    <details style="margin-top: 1rem;">
                        <summary style="cursor: pointer; color: var(--primary); font-weight: 600;">
                            <i class="fas fa-key"></i> Ver Solución
                        </summary>
                        <div class="ejercicio-section" style="margin-top: 1rem; padding: 1rem; background: var(--light); border-radius: 0.5rem;">
                            <p><?php echo nl2br($ejercicio['solucion']); ?></p>
                        </div>
                    </details>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 AuditorEx Chile SpA. Todos los derechos reservados.</p>
            <p><i class="fas fa-envelope"></i> gerencia@auditorexchile.cl | <i class="fas fa-phone"></i> +56 9 8575 4559</p>
        </div>
    </footer>
</body>
</html>
