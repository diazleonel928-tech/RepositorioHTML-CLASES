<?php
session_start();
if (!empty($_SESSION['usuario_id'])) header('Location: home.php');
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Registro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card"><div class="card-body">
        <h4>Registrarse (Alumno)</h4>
        <?php if (!empty($_GET['error'])): ?><div class="alert alert-danger"><?=htmlspecialchars($_GET['error'])?></div><?php endif;?>
        <form action="autorizar.php?action=register" method="post" autocomplete="off">
            <div class="mb-3"><label>Nombre completo</label><input name="nombre" class="form-control" required></div>
            <div class="mb-3"><label>Correo</label><input name="correo" type="email" class="form-control" required></div>
            <div class="mb-3"><label>Contraseña</label><input name="contrasena" type="password" class="form-control" required></div>
            <div class="mb-3"><label>Repite contraseña</label><input name="contrasena2" type="password" class="form-control" required></div>
            <button class="btn btn-success w-100">Crear cuenta</button>
        </form>
        </div></div>
    </div>
    </div>
</div>
</body>
</html>
