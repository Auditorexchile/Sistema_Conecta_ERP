<?php
/**
 * Conecta ERP - Footer (NO ELIMINABLE)
 * Footer obligatorio del sistema
 */

if (!defined('IN_ERP')) {
    die('Acceso directo no permitido');
}

$currentYear = date('Y');
?>
<footer class="main-footer">
    <div class="footer-content">
        <div class="footer-left">
            <span>&copy; <?= $currentYear ?> Conecta ERP - Todos los derechos reservados</span>
        </div>
        <div class="footer-center">
            <a href="mailto:contacto@conectaerp.com"><i class="bi bi-envelope me-1"></i>contacto@conectaerp.com</a>
            <span class="mx-2">|</span>
            <a href="tel:+56985745559"><i class="bi bi-telephone me-1"></i>+56 9 8574 5559</a>
        </div>
        <div class="footer-right">
            <a href="#">Términos</a>
            <span class="mx-2">|</span>
            <a href="#">Privacidad</a>
            <span class="mx-2">|</span>
            <a href="#">Soporte</a>
        </div>
    </div>
</footer>

<style>
.main-footer {
    background: #f8f9fa;
    border-top: 1px solid #e0e0e0;
    padding: 15px 30px;
    margin-top: auto;
    position: sticky;
    bottom: 0;
    z-index: 999;
}

.footer-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13px;
    color: #666;
}

.footer-content a {
    color: #667eea;
    text-decoration: none;
    transition: color 0.3s;
}

.footer-content a:hover {
    color: #764ba2;
}

@media (max-width: 768px) {
    .footer-content {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
}
</style>
