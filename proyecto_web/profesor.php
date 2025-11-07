<?php
require_once __DIR__ . 'helper.php';
require_login();
require_role('profesor');
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Profesor - Dashboard</title></head>
<body>
    <h1>Panel Profesor</h1>
    <p>Bienvenido, <?=htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Profesor')?></p>
    <a href="perfil.php">Mi perfil</a> |
    <a href="cambiarPassword.php">Cambiar contraseña</a>
    <a href="eliminarCuenta.php">Eliminar cuenta</a>
    <a href="autorizar.php?action=logout">Cerrar sesión</a>
</body>
</html>
