<?php

session_start();

if (isset($_SESSION['usuario_id'])) {
    switch ($_SESSION['rol_nombre'] ?? '') {
        case 'admin': header('Location: admin.php'); exit;
        case 'profesor': header('Location: profesor.php'); exit;
        default: header('Location: alumno.php'); exit;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Iniciar sesión</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
    <div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="card-title mb-3">Iniciar sesión</h4>

            <?php if (!empty($_GET['msg'])): ?>
            <div class="alert alert-info"><?=htmlspecialchars($_GET['msg'])?></div>
            <?php endif; ?>

            <form action="./autorizar.php?action=login" method="post">
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
