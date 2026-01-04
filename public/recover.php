<?php
/**
 * Conecta ERP - Recuperación de Contraseña
 * Sistema seguro con token de 15 minutos
 */

require_once __DIR__ . '/../app/core/bootstrap.php';

// Si ya está autenticado, redirigir al dashboard
if (isAuthenticated()) {
    redirect('/app/dashboard/dashboard.php');
}

$error = '';
$success = '';
$step = 'request'; // request, verify, reset, done

// Verificar si hay un token en la URL
if (isset($_GET['token'])) {
    $token = sanitize($_GET['token'], 'string');
    if ($security->validateRecoveryToken($token)) {
        $step = 'reset';
    } else {
        $error = 'Token inválido o expirado. Solicita uno nuevo.';
        $step = 'request';
    }
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'request_reset':
                $email = sanitize($_POST['email'] ?? '', 'email');
                $identificador = sanitize($_POST['identificador'] ?? '', 'string');

                if (empty($email) || empty($identificador)) {
                    $error = 'Email e identificador son requeridos';
                } else {
                    // Buscar usuario
                    $user = $db->queryOne(
                        "SELECT u.*, e.identificador_tributario, e.codigo_pais
                         FROM usuarios_acceso u
                         LEFT JOIN empresas e ON u.id_empresa = e.id
                         WHERE u.email = :email
                         AND u.deleted_at IS NULL
                         AND u.activo = 1",
                        ['email' => $email]
                    );

                    if (!$user) {
                        // Por seguridad, no revelamos si el email existe o no
                        $success = 'Si el email existe, recibirás un enlace de recuperación en los próximos minutos.';
                        $step = 'done';
                    } else {
                        // Validar identificador
                        $countryCode = $user['codigo_pais'] ?? 'CL';
                        $identifierClean = preg_replace('/[^0-9A-Za-z]/', '', $identificador);
                        $storedIdentifierClean = preg_replace('/[^0-9A-Za-z]/', '', $user['identificador_tributario'] ?? '');

                        if (strcasecmp($identifierClean, $storedIdentifierClean) !== 0) {
                            $error = 'Los datos no coinciden con nuestros registros';
                        } else {
                            // Generar token
                            $token = $security->generateRecoveryToken($email);

                            // En producción, enviar email aquí
                            // Por ahora, mostramos el link (solo desarrollo)
                            $recoveryLink = APP_URL . '/public/recover.php?token=' . $token;

                            if (APP_DEBUG) {
                                $success = 'Link de recuperación (solo desarrollo): <a href="' . $recoveryLink . '">' . $recoveryLink . '</a>';
                            } else {
                                $success = 'Se ha enviado un enlace de recuperación a tu email. El enlace expira en 15 minutos.';
                            }

                            logMessage("Recuperación solicitada para: $email | IP: " . getClientIP(), 'info', 'security.log');

                            $step = 'done';
                        }
                    }
                }
                break;

            case 'reset_password':
                $token = sanitize($_POST['token'] ?? '', 'string');
                $password = $_POST['password'] ?? '';
                $passwordConfirm = $_POST['password_confirm'] ?? '';

                if (empty($token) || empty($password) || empty($passwordConfirm)) {
                    $error = 'Todos los campos son requeridos';
                } elseif ($password !== $passwordConfirm) {
                    $error = 'Las contraseñas no coinciden';
                } else {
                    // Validar token
                    $reset = $db->queryOne(
                        "SELECT * FROM password_resets
                         WHERE token = :token
                         AND usado = 0
                         AND fecha_expiracion > NOW()",
                        ['token' => $token]
                    );

                    if (!$reset) {
                        $error = 'Token inválido o expirado';
                    } else {
                        // Validar fortaleza de contraseña
                        $passwordValidation = $security->validatePasswordStrength($password);

                        if (!$passwordValidation['valid']) {
                            $error = implode('. ', $passwordValidation['errors']);
                        } else {
                            // Buscar usuario
                            $user = $db->queryOne(
                                "SELECT id FROM usuarios_acceso WHERE email = :email AND deleted_at IS NULL",
                                ['email' => $reset['email']]
                            );

                            if (!$user) {
                                $error = 'Usuario no encontrado';
                            } else {
                                $db->beginTransaction();

                                try {
                                    // Actualizar contraseña
                                    $db->update('usuarios_acceso', [
                                        'password_hash' => $security->hashPassword($password),
                                        'ultimo_cambio_password' => date('Y-m-d H:i:s'),
                                        'requiere_cambio_password' => 0,
                                        'bloqueado' => 0,
                                        'bloqueado_hasta' => null,
                                        'intentos_login' => 0
                                    ], 'id = :id', ['id' => $user['id']]);

                                    // Marcar token como usado
                                    $security->markTokenAsUsed($token);

                                    // Cerrar todas las sesiones del usuario (por seguridad)
                                    $sessionHandler->destroyUserSessions($user['id']);

                                    // Log
                                    logMessage("Contraseña cambiada para: {$reset['email']} | IP: " . getClientIP(), 'info', 'security.log');

                                    $db->commit();

                                    $success = 'Contraseña actualizada correctamente. Ya puedes iniciar sesión.';
                                    $step = 'done';

                                } catch (Exception $e) {
                                    $db->rollback();
                                    $error = 'Error al actualizar la contraseña';
                                    logMessage('Error en reset password: ' . $e->getMessage(), 'error');
                                }
                            }
                        }
                    }
                }
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Conecta ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .recover-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        .recover-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .recover-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }
        .recover-body {
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
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 14px;
            font-weight: 600;
            font-size: 16px;
        }
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 8px;
            background: #e0e0e0;
        }
        .password-strength-bar {
            height: 100%;
            transition: all 0.3s;
            width: 0%;
        }
        .strength-weak { background: #dc3545; width: 25%; }
        .strength-medium { background: #ffc107; width: 50%; }
        .strength-strong { background: #28a745; width: 75%; }
        .strength-very-strong { background: #0056b3; width: 100%; }
        .icon-lg {
            font-size: 64px;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="recover-card">
        <div class="recover-header">
            <h1>
                <?php if ($step == 'done'): ?>
                    <i class="bi bi-check-circle"></i>
                <?php else: ?>
                    <i class="bi bi-key"></i>
                <?php endif; ?>
            </h1>
            <h1>Recuperar Contraseña</h1>
            <p>
                <?php
                switch ($step) {
                    case 'request':
                        echo 'Ingresa tus datos para recuperar el acceso';
                        break;
                    case 'reset':
                        echo 'Define tu nueva contraseña';
                        break;
                    case 'done':
                        echo '¡Listo!';
                        break;
                }
                ?>
            </p>
        </div>

        <div class="recover-body">
            <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= h($error) ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= $success ?>
            </div>
            <?php endif; ?>

            <?php if ($step == 'request'): ?>
            <!-- PASO 1: Solicitar recuperación -->
            <form method="POST" action="">
                <input type="hidden" name="action" value="request_reset">

                <div class="mb-3">
                    <label for="email" class="form-label">Email de Acceso</label>
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           placeholder="tu@email.com"
                           required>
                </div>

                <div class="mb-3">
                    <label for="identificador" class="form-label">RUT de la Empresa</label>
                    <input type="text"
                           class="form-control"
                           id="identificador"
                           name="identificador"
                           placeholder="12.345.678-9"
                           data-identifier="true"
                           data-country="CL"
                           required>
                    <small class="text-muted">Ingresa el RUT de tu empresa para verificar tu identidad</small>
                    <div class="invalid-feedback"></div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="bi bi-envelope me-2"></i>
                    Enviar Enlace de Recuperación
                </button>

                <div class="text-center">
                    <a href="login.php" class="text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>
                        Volver al Login
                    </a>
                </div>
            </form>

            <?php elseif ($step == 'reset'): ?>
            <!-- PASO 2: Resetear contraseña -->
            <form method="POST" action="">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="token" value="<?= h($token) ?>">

                <div class="mb-3">
                    <label for="password" class="form-label">Nueva Contraseña</label>
                    <div class="position-relative">
                        <input type="password"
                               class="form-control"
                               id="password"
                               name="password"
                               required>
                        <i class="bi bi-eye" id="togglePassword" style="position:absolute;right:16px;top:12px;cursor:pointer;"></i>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <small class="text-muted" id="strengthText">Mínimo 12 caracteres, incluye mayúsculas, minúsculas, números y símbolos</small>
                </div>

                <div class="mb-3">
                    <label for="password_confirm" class="form-label">Confirmar Contraseña</label>
                    <input type="password"
                           class="form-control"
                           id="password_confirm"
                           name="password_confirm"
                           required>
                </div>

                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary btn-sm w-100" id="generatePassword">
                        <i class="bi bi-magic me-2"></i>Generar Contraseña Segura
                    </button>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="bi bi-shield-check me-2"></i>
                    Cambiar Contraseña
                </button>
            </form>

            <?php elseif ($step == 'done'): ?>
            <!-- PASO 3: Confirmación -->
            <div class="text-center py-4">
                <i class="bi bi-check-circle icon-lg mb-3"></i>
                <p class="mb-4">Proceso completado exitosamente</p>
                <a href="login.php" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Ir al Login
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/country-config.js"></script>
    <script src="assets/js/rut-validator.js"></script>
    <script>
        // Toggle password
        const togglePassword = document.getElementById('togglePassword');
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const password = document.getElementById('password');
                const type = password.type === 'password' ? 'text' : 'password';
                password.type = type;
                this.classList.toggle('bi-eye');
                this.classList.toggle('bi-eye-slash');
            });
        }

        // Password strength
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const strength = getPasswordStrength(this.value);
                const bar = document.getElementById('strengthBar');
                const text = document.getElementById('strengthText');

                bar.className = 'password-strength-bar strength-' + strength.replace('_', '-');

                const messages = {
                    'weak': 'Contraseña débil',
                    'medium': 'Contraseña media',
                    'strong': 'Contraseña fuerte',
                    'very_strong': 'Contraseña muy fuerte'
                };

                text.textContent = messages[strength] || '';
            });
        }

        // Generate password
        const generateBtn = document.getElementById('generatePassword');
        if (generateBtn) {
            generateBtn.addEventListener('click', function() {
                const password = generateSecurePassword(16, {
                    uppercase: true,
                    lowercase: true,
                    numbers: true,
                    special: true,
                    avoid_ambiguous: true
                });

                document.getElementById('password').value = password;
                document.getElementById('password_confirm').value = password;
                document.getElementById('password').type = 'text';
                document.getElementById('password').dispatchEvent(new Event('input'));

                alert('Contraseña generada. Asegúrate de guardarla en un lugar seguro.');
            });
        }
    </script>
</body>
</html>
