<?php
require_once 'includes/config.php';

// Si ya está logueado, redirigir al dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = sanitize($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($usuario) || empty($password)) {
        $error = 'Por favor complete todos los campos';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id, usuario, nombre_completo, email, rol FROM usuarios WHERE usuario = ? AND password = MD5(?) AND activo = 1");
            $stmt->execute([$usuario, $password]);
            $user = $stmt->fetch();

            if ($user) {
                // Login exitoso
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['nombre_completo'] = $user['nombre_completo'];
                $_SESSION['rol'] = $user['rol'];

                // Actualizar último acceso
                $stmt = $db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);

                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Usuario o contraseña incorrectos';
            }
        } catch(PDOException $e) {
            $error = 'Error de sistema. Por favor intente más tarde.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="assets/img/logo.png" alt="AuditorEx Chile" class="login-logo" onerror="this.style.display='none'">
                <h1>AuditorEx Chile SpA</h1>
                <h2>Manual de Ciberseguridad</h2>
                <p>Programa de Especialización en Ethical Hacking y Defensa Digital</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="usuario">
                        <i class="fas fa-user"></i> Usuario
                    </label>
                    <input type="text" id="usuario" name="usuario" required
                           placeholder="Ingrese su usuario" autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                    <input type="password" id="password" name="password" required
                           placeholder="Ingrese su contraseña" autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </form>

            <div class="login-footer">
                <p><i class="fas fa-shield-alt"></i> Acceso seguro y encriptado</p>
                <p class="copyright">&copy; 2024 AuditorEx Chile SpA. Todos los derechos reservados.</p>
            </div>
        </div>

        <div class="login-info">
            <div class="info-card">
                <i class="fas fa-book"></i>
                <h3>6 Módulos Completos</h3>
                <p>Contenido exhaustivo desde fundamentos hasta técnicas avanzadas</p>
            </div>
            <div class="info-card">
                <i class="fas fa-flask"></i>
                <h3>Casos Reales + Ejercicios</h3>
                <p>15 casos reales y 15 ejercicios prácticos por cada tema</p>
            </div>
            <div class="info-card">
                <i class="fas fa-certificate"></i>
                <h3>Certificación Profesional</h3>
                <p>Preparación para CEH, OSCP y más</p>
            </div>
        </div>
    </div>
</body>
</html>
