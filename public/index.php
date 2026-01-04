<?php
/**
 * Conecta ERP - Portada Profesional
 * Landing page del sistema ERP
 */

require_once __DIR__ . '/../app/core/bootstrap.php';

// Si está autenticado, redirigir al dashboard
if (isAuthenticated()) {
    redirect('/app/dashboard/dashboard.php');
}

// Obtener planes desde BD
$db = Database::getInstance();
$planes = $db->query("SELECT * FROM planes WHERE activo = 1 AND visible = 1 ORDER BY orden_display");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conecta ERP - Gestiona tu empresa en un solo sistema</title>
    <meta name="description" content="Sistema ERP integral con contabilidad, ventas, inventario, producción y control total. Prueba gratis 14 días.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --dark-color: #2c3e50;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            overflow-x: hidden;
        }

        /* Header */
        .main-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: all 0.3s;
            padding: 20px 0;
        }

        .main-header.scrolled {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            font-size: 32px;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 30px;
            list-style: none;
            margin: 0;
        }

        .nav-menu a {
            color: var(--dark-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-menu a:hover {
            color: var(--primary-color);
        }

        .btn-login {
            padding: 10px 24px;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: var(--primary-color);
            color: white;
        }

        .btn-register {
            padding: 10px 24px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        /* Hero */
        .hero {
            padding: 150px 0 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 56px;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero p {
            font-size: 20px;
            margin-bottom: 40px;
            opacity: 0.95;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .btn-hero-primary {
            padding: 16px 40px;
            background: white;
            color: var(--primary-color);
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 18px;
            transition: all 0.3s;
        }

        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            color: var(--secondary-color);
        }

        .btn-hero-secondary {
            padding: 16px 40px;
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 18px;
            transition: all 0.3s;
        }

        .btn-hero-secondary:hover {
            background: white;
            color: var(--primary-color);
        }

        .hero-image {
            position: relative;
        }

        .hero-image img {
            max-width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        /* Beneficios */
        .benefits {
            padding: 100px 0;
            background: #f8f9fa;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 42px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }

        .section-title p {
            font-size: 18px;
            color: #7f8c8d;
        }

        .benefit-card {
            background: white;
            border-radius: 16px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
            height: 100%;
        }

        .benefit-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 40px rgba(102, 126, 234, 0.2);
        }

        .benefit-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
        }

        .benefit-icon.blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .benefit-icon.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .benefit-icon.orange {
            background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);
        }

        .benefit-icon.purple {
            background: linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%);
        }

        .benefit-card h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--dark-color);
        }

        .benefit-card p {
            color: #7f8c8d;
            margin: 0;
            line-height: 1.7;
        }

        /* Planes */
        .plans {
            padding: 100px 0;
        }

        .plan-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
            height: 100%;
            border: 3px solid transparent;
        }

        .plan-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 40px rgba(102, 126, 234, 0.2);
        }

        .plan-card.featured {
            border-color: var(--primary-color);
            transform: scale(1.05);
            position: relative;
        }

        .plan-badge {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 8px 24px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }

        .plan-name {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--dark-color);
        }

        .plan-price {
            font-size: 48px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .plan-period {
            color: #7f8c8d;
            margin-bottom: 30px;
        }

        .plan-features {
            list-style: none;
            padding: 0;
            margin: 0 0 30px 0;
        }

        .plan-features li {
            padding: 12px 0;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .plan-features li:last-child {
            border-bottom: none;
        }

        .plan-features i {
            color: var(--primary-color);
            font-size: 20px;
        }

        .btn-plan {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-plan:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-plan.outline {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        /* CTA */
        .cta {
            padding: 100px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
        }

        .cta h2 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .cta p {
            font-size: 20px;
            margin-bottom: 40px;
            opacity: 0.95;
        }

        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 60px 0 30px;
        }

        .footer h5 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .footer ul {
            list-style: none;
            padding: 0;
        }

        .footer ul li {
            margin-bottom: 10px;
        }

        .footer a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer a:hover {
            color: white;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 40px;
            padding-top: 30px;
            text-align: center;
            color: rgba(255,255,255,0.7);
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 36px;
            }

            .plan-card.featured {
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header" id="mainHeader">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="#" class="logo">
                    <i class="bi bi-diagram-3-fill"></i>
                    <span>Conecta ERP</span>
                </a>

                <nav class="d-none d-lg-block">
                    <ul class="nav-menu">
                        <li><a href="#inicio">Inicio</a></li>
                        <li><a href="#planes">Planes</a></li>
                        <li><a href="#beneficios">Funcionalidades</a></li>
                        <li><a href="#contacto">Contacto</a></li>
                    </ul>
                </nav>

                <div class="d-flex gap-3">
                    <a href="login.php" class="btn-login">Ingresar</a>
                    <a href="register.php" class="btn-register">Crear cuenta</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero -->
    <section class="hero" id="inicio">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1>Gestiona tu empresa en un solo sistema</h1>
                    <p>Contabilidad, ventas, inventario, producción y control total en un ERP moderno y fácil de usar.</p>
                    <div class="hero-buttons">
                        <a href="register.php" class="btn-hero-primary">
                            <i class="bi bi-rocket-takeoff me-2"></i>
                            Crear cuenta gratis
                        </a>
                        <a href="#planes" class="btn-hero-secondary">
                            <i class="bi bi-eye me-2"></i>
                            Ver planes
                        </a>
                    </div>
                    <p class="mt-4 mb-0"><small>✨ 14 días de prueba gratis. Sin tarjeta de crédito.</small></p>
                </div>
                <div class="col-lg-6 hero-image mt-5 mt-lg-0">
                    <div class="p-4">
                        <div style="background: rgba(255,255,255,0.1); padding: 30px; border-radius: 20px; backdrop-filter: blur(10px);">
                            <i class="bi bi-graph-up-arrow" style="font-size: 200px; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Beneficios -->
    <section class="benefits" id="beneficios">
        <div class="container">
            <div class="section-title">
                <h2>¿Por qué elegir Conecta ERP?</h2>
                <p>Todo lo que necesitas para gestionar tu empresa de forma eficiente</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="benefit-card">
                        <div class="benefit-icon blue">
                            <i class="bi bi-link-45deg"></i>
                        </div>
                        <h3>Todo integrado</h3>
                        <p>Un solo sistema para contabilidad, ventas, compras, inventario y producción. Sin duplicar datos.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="benefit-card">
                        <div class="benefit-icon green">
                            <i class="bi bi-file-earmark-check"></i>
                        </div>
                        <h3>Cumplimiento tributario</h3>
                        <p>Preparado para SII, Previred y normativas chilenas. Facturación electrónica incluida.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="benefit-card">
                        <div class="benefit-icon orange">
                            <i class="bi bi-arrow-up-right-circle"></i>
                        </div>
                        <h3>Escalable</h3>
                        <p>Crece con tu empresa. Desde emprendedor hasta corporativo, tenemos el plan perfecto.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="benefit-card">
                        <div class="benefit-icon purple">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3>Seguro</h3>
                        <p>Acceso protegido, auditoría completa y respaldo de datos. Tu información siempre segura.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Planes -->
    <section class="plans" id="planes">
        <div class="container">
            <div class="section-title">
                <h2>Planes para cada necesidad</h2>
                <p>Selecciona el plan que mejor se adapte a tu empresa</p>
            </div>

            <div class="row g-4">
                <?php foreach ($planes as $index => $plan): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="plan-card <?= $plan['destacado'] ? 'featured' : '' ?>">
                        <?php if ($plan['destacado']): ?>
                        <div class="plan-badge">Más Popular</div>
                        <?php endif; ?>

                        <div class="plan-name"><?= h($plan['nombre']) ?></div>

                        <?php if ($plan['precio_mensual'] > 0): ?>
                        <div class="plan-price"><?= formatMoney($plan['precio_mensual'], $plan['moneda']) ?></div>
                        <div class="plan-period">/mes</div>
                        <?php else: ?>
                        <div class="plan-price" style="font-size: 36px;">Gratis</div>
                        <div class="plan-period">Para siempre</div>
                        <?php endif; ?>

                        <ul class="plan-features">
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span><?= $plan['max_empresas'] ?> empresa<?= $plan['max_empresas'] > 1 ? 's' : '' ?></span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span><?= $plan['usuarios_ilimitados'] ? 'Usuarios ilimitados' : $plan['max_usuarios'] . ' usuario(s)' ?></span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Productos ilimitados</span>
                            </li>
                            <?php if ($plan['tiene_trial']): ?>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span><?= $plan['dias_trial'] ?> días de prueba</span>
                            </li>
                            <?php endif; ?>
                        </ul>

                        <a href="register.php" class="btn-plan <?= $plan['destacado'] ? '' : 'outline' ?>">
                            <?= $plan['codigo'] == 'CORPORATIVO' ? 'Contactar' : 'Comenzar' ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta">
        <div class="container">
            <h2>Comienza hoy y controla tu empresa desde un solo lugar</h2>
            <p>14 días de prueba gratis. Sin tarjeta de crédito. Cancela cuando quieras.</p>
            <a href="register.php" class="btn-hero-primary">
                <i class="bi bi-rocket-takeoff me-2"></i>
                Crear cuenta gratis
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>Conecta ERP</h5>
                    <p>Plataforma ERP integral para empresas modernas.</p>
                </div>

                <div class="col-md-2 mb-4">
                    <h5>Navegación</h5>
                    <ul>
                        <li><a href="#inicio">Inicio</a></li>
                        <li><a href="#planes">Planes</a></li>
                        <li><a href="#beneficios">Funcionalidades</a></li>
                        <li><a href="login.php">Login</a></li>
                    </ul>
                </div>

                <div class="col-md-3 mb-4">
                    <h5>Contacto</h5>
                    <ul>
                        <li><i class="bi bi-envelope me-2"></i>contacto@conectaerp.com</li>
                        <li><i class="bi bi-telephone me-2"></i>+56 9 8574 5559</li>
                        <li><i class="bi bi-clock me-2"></i>Lun - Vie: 9:00 - 18:00</li>
                    </ul>
                </div>

                <div class="col-md-3 mb-4">
                    <h5>Legal</h5>
                    <ul>
                        <li><a href="#">Términos y condiciones</a></li>
                        <li><a href="#">Política de privacidad</a></li>
                        <li><a href="#">Aviso legal</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                &copy; <?= date('Y') ?> Conecta ERP - Todos los derechos reservados
            </div>
        </div>
    </footer>

    <script>
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('mainHeader');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>
