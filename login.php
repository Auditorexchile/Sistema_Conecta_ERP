<?php
/**
 * Página de login
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

session_start();

// Si ya está autenticado, redirigir al dashboard
if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Por favor ingrese usuario y contraseña';
    } else {
        $db = Database::getInstance();

        $sql = "SELECT u.*, p.nombre_perfil
                FROM sys_usuarios u
                LEFT JOIN sys_perfiles p ON u.id_perfil = p.id_perfil
                WHERE u.username = :username AND u.activo = 1";

        $user = $db->queryOne($sql, ['username' => $username]);

        if ($user && verifyPassword($password, $user['password'])) {
            // Login exitoso
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nombre_completo'] = $user['nombre_completo'];
            $_SESSION['id_perfil'] = $user['id_perfil'];
            $_SESSION['nombre_perfil'] = $user['nombre_perfil'];

            // Actualizar último acceso
            $db->update('sys_usuarios',
                ['ultimo_acceso' => date('Y-m-d H:i:s')],
                'id_usuario = :id',
                ['id' => $user['id_usuario']]
            );

            // Registrar auditoría
            registrarAuditoria('sistema', 'login', 'sys_usuarios', $user['id_usuario'], 'Inicio de sesión exitoso');

            redirect('/index.php');
        } else {
            $error = 'Usuario o contraseña incorrectos';

            // Registrar intento fallido
            registrarAuditoria('sistema', 'login_failed', null, null, "Intento fallido de login: {$username}");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= SYSTEM_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/login.css">
</head>
<body class="login-page">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h1 class="h3 mb-3 fw-bold text-primary">
                                <i class="fas fa-chart-line"></i> <?= SYSTEM_NAME ?>
                            </h1>
                            <p class="text-muted">Sistema de Gestión Contable</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username"
                                           value="<?= htmlspecialchars($username ?? '') ?>" required autofocus>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Ingresar
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <?= SYSTEM_NAME ?> v<?= SYSTEM_VERSION ?><br>
                                &copy; <?= date('Y') ?> <?= SYSTEM_AUTHOR ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
