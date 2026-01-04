<?php
/**
 * Conecta ERP - Header
 * Header global del sistema ERP
 */

if (!defined('IN_ERP')) {
    die('Acceso directo no permitido');
}

$userName = $_SESSION['user']['nombre'] ?? $_SESSION['email'] ?? 'Usuario';
$companyName = $_SESSION['company_name'] ?? 'Empresa';
?>
<header class="main-header">
    <div class="header-left">
        <button class="btn-toggle-sidebar" id="toggleSidebar">
            <i class="bi bi-list"></i>
        </button>
        <div class="company-info">
            <span class="company-name"><?= h($companyName) ?></span>
        </div>
    </div>

    <div class="header-center">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Buscar..." class="form-control form-control-sm">
        </div>
    </div>

    <div class="header-right">
        <!-- Selector de idioma -->
        <div class="dropdown">
            <button class="btn btn-sm btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-translate"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" data-lang="es"><span class="fi fi-es"></span> Español</a></li>
                <li><a class="dropdown-item" href="#" data-lang="en"><span class="fi fi-us"></span> English</a></li>
                <li><a class="dropdown-item" href="#" data-lang="pt"><span class="fi fi-br"></span> Português</a></li>
            </ul>
        </div>

        <!-- Notificaciones -->
        <div class="dropdown">
            <button class="btn btn-sm btn-link position-relative" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">3</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><h6 class="dropdown-header">Notificaciones</h6></li>
                <li><a class="dropdown-item" href="#"><small>Nueva funcionalidad disponible</small></a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-center" href="#">Ver todas</a></li>
            </ul>
        </div>

        <!-- Usuario -->
        <div class="dropdown">
            <button class="btn btn-sm btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i>
                <span><?= h($userName) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Mi Perfil</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Configuración</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/public/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
            </ul>
        </div>
    </div>
</header>

<style>
.main-header {
    height: 60px;
    background: white;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.btn-toggle-sidebar {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #333;
    padding: 8px;
    border-radius: 4px;
    transition: background 0.3s;
}

.btn-toggle-sidebar:hover {
    background: #f0f0f0;
}

.company-name {
    font-weight: 600;
    color: #333;
}

.header-center {
    flex: 1;
    max-width: 500px;
    margin: 0 20px;
}

.search-box {
    position: relative;
}

.search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
}

.search-box input {
    padding-left: 40px;
    border-radius: 20px;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 10px;
}
</style>
