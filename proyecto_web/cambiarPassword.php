<?php
// public/change_password.php
session_start();
if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cambiar contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card">
        <div class="card-body">
            <h5>Cambiar contraseña</h5>

            <?php if (!empty($_GET['error'])): ?>
            <div class="alert alert-danger"><?=htmlspecialchars($_GET['error'])?></div>
            <?php endif; ?>
            <?php if (!empty($_GET['msg'])): ?>
            <div class="alert alert-success"><?=htmlspecialchars($_GET['msg'])?></div>
            <?php endif; ?>

            <form action="auth.php?action=change_password" method="post" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>">
            <div class="mb-3">
                <label for="current" class="form-label">Contraseña actual</label>
                <input id="current" name="current_password" type="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="new1" class="form-label">Nueva contraseña</label>
                <input id="new1" name="new_password" type="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="new2" class="form-label">Repite nueva contraseña</label>
                <input id="new2" name="new_password2" type="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">Actualizar contraseña</button>
            </form>

            <hr>
            <a href="login.php">Volver al panel</a>
            </div>
        </div>
        </div>
    </div>
</div>
</body>
</html>
