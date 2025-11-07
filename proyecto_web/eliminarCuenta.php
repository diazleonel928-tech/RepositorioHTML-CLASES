<?php
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
    <title>Eliminar cuenta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
        <div class="card border-danger">
            <div class="card-body">
            <h5 class="text-danger">Eliminar tu cuenta</h5>
            <p>Esta acción <strong>no</strong> se puede deshacer. Si estás seguro, escribe tu contraseña y confirma.</p>

            <?php if (!empty($_GET['error'])): ?>
                <div class="alert alert-danger"><?=htmlspecialchars($_GET['error'])?></div>
            <?php endif; ?>

            <form action="autorizar?action=delete_account" method="post" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>">
                <div class="mb-3">
                <label for="pwd" class="form-label">Contraseña</label>
                <input id="pwd" name="password" type="password" class="form-control" required>
                </div>
                <button class="btn btn-danger w-100" type="submit">Eliminar mi cuenta</button>
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
