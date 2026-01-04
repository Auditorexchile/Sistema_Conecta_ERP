<?php
/**
 * Conecta ERP - Login Profesional
 * Sistema de autenticación con validación RUT y multipaís
 */

require_once __DIR__ . '/../app/core/bootstrap.php';

// Si ya está autenticado, redirigir al dashboard
if (isAuthenticated()) {
    redirect('/app/dashboard/dashboard.php');
}

$error = '';
$success = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '', 'email');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $error = 'Email y contraseña son requeridos';
    } else {
        // Verificar si el usuario está bloqueado
        if ($security->isUserLocked($email)) {
            $error = 'Cuenta bloqueada temporalmente por exceso de intentos fallidos. Intente más tarde.';
            $security->logLoginAttempt($email, null, false, 'usuario_bloqueado');
        } else {
            // Buscar usuario
            $user = $db->queryOne(
                "SELECT u.*, e.id as empresa_id, e.razon_social, e.estado as empresa_estado,
                        e.codigo_pais, e.identificador_tributario,
                        ct.dias_trial_restantes, ct.trial_vencido, ct.fecha_fin_trial,
                        s.id as suscripcion_id, s.estado as suscripcion_estado,
                        p.codigo as plan_codigo, p.nombre as plan_nombre
                 FROM usuarios_acceso u
                 LEFT JOIN empresas e ON u.id_empresa = e.id
                 LEFT JOIN control_trial ct ON e.id = ct.id_empresa
                 LEFT JOIN suscripciones s ON e.id = s.id_empresa AND s.estado IN ('trial', 'activa')
                 LEFT JOIN planes p ON s.id_plan = p.id
                 WHERE u.email = :email
                 AND u.deleted_at IS NULL",
                ['email' => $email]
            );

            if (!$user) {
                $error = 'Credenciales inválidas';
                $security->logLoginAttempt($email, null, false, 'credenciales_invalidas');
                $security->checkLoginAttempts($email);
            } elseif (!$user['activo']) {
                $error = 'Cuenta inactiva. Contacte al administrador.';
                $security->logLoginAttempt($email, $user['id'], false, 'cuenta_inactiva');
            } elseif ($user['tipo_usuario'] !== 'superadmin' && $user['empresa_estado'] === 'suspendido') {
                $error = 'Cuenta suspendida por falta de pago. Contacte a soporte.';
                $security->logLoginAttempt($email, $user['id'], false, 'cuenta_suspendida');
            } elseif (!$security->verifyPassword($password, $user['password_hash'])) {
                $error = 'Credenciales inválidas';
                $security->logLoginAttempt($email, $user['id'], false, 'credenciales_invalidas');
                $security->checkLoginAttempts($email);
            } else {
                // Login exitoso
                $security->logLoginAttempt($email, $user['id'], true);

                // Crear sesión
                $sessionData = [
                    'company_id' => $user['empresa_id'],
                    'company_name' => $user['razon_social'],
                    'company_status' => $user['empresa_estado'],
                    'country_code' => $user['codigo_pais'],
                    'plan_code' => $user['plan_codigo'],
                    'plan_name' => $user['plan_nombre'],
                ];

                $sessionHandler->create($user['id'], $sessionData);

                // Establecer variables de sesión adicionales
                $_SESSION['user_type'] = $user['tipo_usuario'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['company_id'] = $user['empresa_id'];
                $_SESSION['company_name'] = $user['razon_social'];
                $_SESSION['company_status'] = $user['empresa_estado'];
                $_SESSION['country_code'] = $user['codigo_pais'];
                $_SESSION['plan_code'] = $user['plan_codigo'];

                // Trial info
                if ($user['trial_vencido']) {
                    $_SESSION['trial_expired'] = true;
                    $_SESSION['trial_days_remaining'] = 0;
                } else {
                    $_SESSION['trial_expired'] = false;
                    $_SESSION['trial_days_remaining'] = $user['dias_trial_restantes'] ?? 0;
                    $_SESSION['trial_end_date'] = $user['fecha_fin_trial'];
                }

                // Configurar preferencias de país
                $countryConfig = getCountryConfig($user['codigo_pais']);
                if ($countryConfig) {
                    $_SESSION['currency'] = $countryConfig['currency'];
                    $_SESSION['timezone'] = $countryConfig['timezone'];
                    $_SESSION['date_format'] = $countryConfig['date_format_php'];
                }

                // Redirigir al dashboard
                redirect('/app/dashboard/dashboard.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Conecta ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .login-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }
        .login-header p {
            margin: 0;
            opacity: 0.95;
        }
        .login-body {
            padding: 40px 30px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 14px;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }
        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e0e0e0;
        }
        .divider span {
            background: white;
            padding: 0 15px;
            position: relative;
            color: #999;
            font-size: 14px;
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h1>Conecta ERP</h1>
            <p>Gestiona tu empresa en un solo sistema</p>
        </div>

        <div class="login-body">
            <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= h($error) ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= h($success) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           placeholder="tu@email.com"
                           value="<?= h($_POST['email'] ?? '') ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="position-relative">
                        <input type="password"
                               class="form-control"
                               id="password"
                               name="password"
                               placeholder="••••••••"
                               required>
                        <i class="bi bi-eye password-toggle" id="togglePassword"></i>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Recordar sesión
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100 mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Ingresar
                </button>

                <div class="text-center">
                    <a href="recover.php" class="text-decoration-none">
                        <i class="bi bi-key me-1"></i>
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <div class="divider">
                    <span>¿No tienes cuenta?</span>
                </div>

                <a href="register.php" class="btn btn-outline-primary w-100">
                    <i class="bi bi-person-plus me-2"></i>
                    Crear cuenta gratis
                </a>
            </form>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    </script>
</body>
</html>
