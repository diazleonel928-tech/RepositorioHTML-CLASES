<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';

$msg = $_GET['msg'] ?? '';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Iniciar sesión</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body">
            <h4 class="card-title mb-3">Iniciar sesión</h4>

            <?php if ($msg === 'invalid'): ?>
                <div class="alert alert-danger">Correo o contraseña inválidos.</div>
            <?php elseif ($msg === 'logout'): ?>
                <div class="alert alert-success">Has cerrado sesión.</div>
            <?php elseif ($msg === 'login_required'): ?>
                <div class="alert alert-warning">Debes iniciar sesión para ver esa página.</div>
            <?php endif; ?>

            <form action="autorizar.php?action=login" method="post">
                <div class="mb-3">
                <label for="correo" class="form-label">Correo</label>
                <input id="correo" name="correo" type="email" class="form-control" required>
                </div>
                <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input id="contrasena" name="contrasena" type="password" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100" type="submit">Entrar</button>
            </form>

            <hr>
            <p class="small mb-0">¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
            </div>
        </div>
        </div>
    </div>
</div>
</body>
</html>