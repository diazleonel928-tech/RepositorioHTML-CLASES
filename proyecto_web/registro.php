<?php

session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: alumno.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Registro - Alumno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
    <div class="container">
    <div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="card-title mb-3">Crear cuenta (Alumno)</h4>
            <?php if (!empty($_GET['error'])): ?>
            <div class="alert alert-danger"><?=htmlspecialchars($_GET['error'])?></div>
            <?php endif; ?>
            <form action="./autorizar.php?action=register" method="post">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre completo</label>
                <input id="nombre" name="nombre" type="text" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo</label>
                <input id="correo" name="correo" type="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input id="contrasena" name="contrasena" type="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="contrasena2" class="form-label">Repite la contraseña</label>
                <input id="contrasena2" name="contrasena2" type="password" class="form-control" required>
            </div>
            <button class="btn btn-success w-100" type="submit">Crear cuenta</button>
            </form>
            <hr>
            <p class="small mb-0">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
        </div>
        </div>
    </div>
    </div>
</div>
</body>
</html>
