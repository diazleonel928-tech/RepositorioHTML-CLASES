<?php
require_once __DIR__ . '/helper.php';
require_login();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"><title>Alumno - Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-3">
        <a href="<?= htmlspecialchars($homeDestino) ?>" class="btn btn-secondary mb-3">
            ← Volver al inicio
        </a>
    </div>
    <h1>Panel Alumno</h1>
    <p>Bienvenido, <?=htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Alumno')?></p>
    <a href="perfil.php">Mi perfil</a>
    <a href="cambiarPassword.php">Cambiar contraseña</a>
    <a href="eliminarCuenta.php">Eliminar cuenta</a>
    <a href="autorizar.php?action=logout">Cerrar sesión</a>
</body>
</html>
