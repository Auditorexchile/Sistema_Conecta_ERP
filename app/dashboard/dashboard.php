<?php
/**
 * Conecta ERP - Dashboard Principal
 * Dashboard con banner de trial de 14 días
 */

define('IN_ERP', true);

require_once __DIR__ . '/../../app/core/bootstrap.php';

// Verificar autenticación
checkAuthentication();

// Inicializar middleware
initMiddleware();

// Obtener información de trial
$trialExpired = $_SESSION['trial_expired'] ?? false;
$trialDays = $_SESSION['trial_days_remaining'] ?? 0;
$isTrial = ($_SESSION['company_status'] ?? '') === 'trial';
$isSuperAdmin = isSuperAdmin();

// Obtener datos del dashboard
$db = Database::getInstance();

// Estadísticas básicas (placeholder - en producción vienen de BD)
$stats = [
    'ventas_mes' => 0,
    'compras_mes' => 0,
    'facturas_pendientes' => 0,
    'documentos_dia' => 0
];

if (!$isSuperAdmin && $_SESSION['company_id']) {
    // Aquí irían las consultas reales a la BD
    // Por ahora dejamos valores de ejemplo
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Conecta ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-wrapper {
            display: flex;
            flex: 1;
        }

        .content-wrapper {
            flex: 1;
            margin-left: 260px;
            transition: margin-left 0.3s;
            display: flex;
            flex-direction: column;
        }

        .main-sidebar.collapsed + .content-wrapper {
            margin-left: 70px;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            margin-top: 60px;
        }

        /* Banner de Trial */
        .trial-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .trial-banner-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .trial-icon {
            font-size: 48px;
        }

        .trial-info h4 {
            margin: 0 0 5px 0;
            font-size: 20px;
            font-weight: 600;
        }

        .trial-info p {
            margin: 0;
            opacity: 0.95;
        }

        .trial-days {
            font-size: 36px;
            font-weight: 700;
            background: rgba(255,255,255,0.2);
            padding: 15px 25px;
            border-radius: 10px;
            text-align: center;
        }

        .trial-days small {
            display: block;
            font-size: 14px;
            font-weight: 400;
        }

        .btn-upgrade {
            background: white;
            color: #667eea;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-upgrade:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            color: #764ba2;
        }

        /* Banner Expirado */
        .trial-expired-banner {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }

        .stat-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-icon.blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-icon.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .stat-icon.orange {
            background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);
            color: white;
        }

        .stat-icon.purple {
            background: linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%);
            color: white;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
            font-weight: 500;
        }

        /* Welcome Section */
        .welcome-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .welcome-section h2 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .welcome-section p {
            color: #7f8c8d;
            margin: 0;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .action-btn {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: #2c3e50;
            transition: all 0.3s;
        }

        .action-btn:hover {
            border-color: #667eea;
            background: #f0f4ff;
            transform: translateY(-3px);
            color: #667eea;
        }

        .action-btn i {
            font-size: 32px;
            margin-bottom: 10px;
            display: block;
        }

        /* Toggle Dashboard Button */
        .toggle-fullscreen {
            position: fixed;
            top: 80px;
            right: 30px;
            background: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            cursor: pointer;
            z-index: 999;
            transition: all 0.3s;
        }

        .toggle-fullscreen:hover {
            background: #667eea;
            color: white;
        }

        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
            }

            .trial-banner {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layout/header.php'; ?>

    <div class="main-wrapper">
        <?php include __DIR__ . '/../layout/sidebar.php'; ?>

        <div class="content-wrapper">
            <div class="main-content">
                <!-- Botón Toggle Fullscreen (solo dashboard) -->
                <button class="toggle-fullscreen" id="toggleFullscreen" title="Maximizar Dashboard">
                    <i class="bi bi-arrows-fullscreen"></i>
                </button>

                <!-- Banner de Trial -->
                <?php if (!$isSuperAdmin): ?>
                    <?php if ($trialExpired): ?>
                    <!-- Trial Expirado -->
                    <div class="trial-banner trial-expired-banner">
                        <div class="trial-banner-content">
                            <div class="trial-icon">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                            <div class="trial-info">
                                <h4>Tu período de prueba ha finalizado</h4>
                                <p>Para continuar usando Conecta ERP, selecciona un plan</p>
                            </div>
                        </div>
                        <div>
                            <a href="#" class="btn-upgrade">
                                <i class="bi bi-credit-card me-2"></i>Ver Planes
                            </a>
                        </div>
                    </div>
                    <?php elseif ($isTrial): ?>
                    <!-- Trial Activo -->
                    <div class="trial-banner">
                        <div class="trial-banner-content">
                            <div class="trial-icon">
                                <i class="bi bi-gift-fill"></i>
                            </div>
                            <div class="trial-info">
                                <h4>Período de Prueba Activo</h4>
                                <p>Estás probando Conecta ERP gratis por 14 días</p>
                            </div>
                        </div>
                        <div class="trial-days">
                            <?= $trialDays ?>
                            <small>días restantes</small>
                        </div>
                        <div>
                            <a href="#" class="btn-upgrade">
                                <i class="bi bi-star-fill me-2"></i>Activar Plan
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Welcome Section -->
                <div class="welcome-section">
                    <h2>Bienvenido, <?= h($_SESSION['email']) ?></h2>
                    <p>
                        <?php if ($isSuperAdmin): ?>
                            Panel de administración del sistema
                        <?php else: ?>
                            <?= h($_SESSION['company_name'] ?? 'Empresa') ?> | Plan: <?= h($_SESSION['plan_code'] ?? 'N/A') ?>
                        <?php endif; ?>
                    </p>

                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <a href="#" class="action-btn">
                            <i class="bi bi-file-earmark-plus"></i>
                            <div>Nueva Factura</div>
                        </a>
                        <a href="#" class="action-btn">
                            <i class="bi bi-person-plus"></i>
                            <div>Nuevo Cliente</div>
                        </a>
                        <a href="#" class="action-btn">
                            <i class="bi bi-box-seam"></i>
                            <div>Nuevo Producto</div>
                        </a>
                        <a href="#" class="action-btn">
                            <i class="bi bi-graph-up"></i>
                            <div>Ver Reportes</div>
                        </a>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-value"><?= formatMoney($stats['ventas_mes']) ?></div>
                                <div class="stat-label">Ventas del Mes</div>
                            </div>
                            <div class="stat-icon blue">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-value"><?= formatMoney($stats['compras_mes']) ?></div>
                                <div class="stat-label">Compras del Mes</div>
                            </div>
                            <div class="stat-icon green">
                                <i class="bi bi-cart-check"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-value"><?= $stats['facturas_pendientes'] ?></div>
                                <div class="stat-label">Facturas Pendientes</div>
                            </div>
                            <div class="stat-icon orange">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div>
                                <div class="stat-value"><?= $stats['documentos_dia'] ?></div>
                                <div class="stat-label">Documentos Hoy</div>
                            </div>
                            <div class="stat-icon purple">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Más secciones del dashboard aquí -->
            </div>

            <?php include __DIR__ . '/../layout/footer.php'; ?>
        </div>
    </div>

    <!-- Modal Trial Vencido (se muestra automáticamente) -->
    <?php if ($trialExpired && !$isSuperAdmin): ?>
    <div class="modal fade" id="trialExpiredModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Período de Prueba Finalizado
                    </h5>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-hourglass-bottom" style="font-size: 64px; color: #dc3545;"></i>
                    <h4 class="mt-3 mb-3">Tu prueba de 14 días ha terminado</h4>
                    <p class="text-muted">
                        Para continuar usando Conecta ERP y acceder a todas las funcionalidades,
                        selecciona el plan que mejor se adapte a tu empresa.
                    </p>
                    <div class="alert alert-warning">
                        <strong>Funcionalidades limitadas:</strong><br>
                        No podrás crear nuevos documentos ni realizar operaciones hasta activar un plan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Ver después
                    </button>
                    <a href="#" class="btn btn-primary">
                        <i class="bi bi-credit-card me-2"></i>Ver Planes
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle fullscreen dashboard
        document.getElementById('toggleFullscreen').addEventListener('click', function() {
            const sidebar = document.getElementById('mainSidebar');
            const content = document.querySelector('.content-wrapper');
            const footer = document.querySelector('.main-footer');
            const icon = this.querySelector('i');

            sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';

            if (sidebar.style.display === 'none') {
                content.style.marginLeft = '0';
                icon.classList.remove('bi-arrows-fullscreen');
                icon.classList.add('bi-arrows-angle-contract');
                this.title = 'Restaurar Dashboard';
            } else {
                content.style.marginLeft = '';
                icon.classList.remove('bi-arrows-angle-contract');
                icon.classList.add('bi-arrows-fullscreen');
                this.title = 'Maximizar Dashboard';
            }
        });

        // Mostrar modal de trial expirado
        <?php if ($trialExpired && !$isSuperAdmin): ?>
        const trialModal = new bootstrap.Modal(document.getElementById('trialExpiredModal'));
        setTimeout(() => {
            trialModal.show();
        }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>
