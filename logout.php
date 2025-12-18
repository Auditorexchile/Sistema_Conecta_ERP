<?php
/**
 * Cerrar sesión
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

session_start();

if (isLoggedIn()) {
    // Registrar auditoría
    registrarAuditoria('sistema', 'logout', 'sys_usuarios', $_SESSION['id_usuario'], 'Cierre de sesión');
}

// Destruir sesión
session_destroy();

// Redirigir al login
redirect('/login.php');
