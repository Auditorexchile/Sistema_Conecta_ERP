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
    <!-- CSS Propio - SIN DEPENDENCIAS -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/reset.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/login.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1 class="login-title">
                    <span class="icon">&#128200;</span> <?= SYSTEM_NAME ?>
                </h1>
                <p class="login-subtitle">Sistema de Gestión Contable</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <span class="icon">⚠</span> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="username" class="form-label">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text"><span class="icon">&#128100;</span></span>
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?= htmlspecialchars($username ?? '') ?>" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><span class="icon">&#128273;</span></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <span class="icon">&#128682;</span> Ingresar
                    </button>
                </div>
            </form>

            <div class="login-footer">
                <small>
                    <?= SYSTEM_NAME ?> v<?= SYSTEM_VERSION ?><br>
                    &copy; <?= date('Y') ?> <?= SYSTEM_AUTHOR ?>
                </small>
            </div>
        </div>
    </div>
</body>
</html>
