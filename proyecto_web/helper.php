<?php
session_start();

function require_login() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /CosasHTML-CSS/proyecto_web/public/login.php');
        exit;
    }
}

function require_role($rol) {
    if (!isset($_SESSION['rol_nombre']) || $_SESSION['rol_nombre'] !== $rol) {
        header('HTTP/1.1 403 Forbidden');
        echo "Acceso denegado.";
        exit;
    }
}
