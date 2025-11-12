<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['correo'] ?? '');
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if ($nombre === '' || $email === '' || $pass === '') $errors[] = 'Todos los campos obligatorios.';
    if ($pass !== $pass2) $errors[] = 'Las contraseñas no coinciden.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';

    if (empty($errors)) {
        $s = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $s->execute([$email]);
        if ($s->fetch()) $errors[] = 'Ya existe una cuenta con ese correo.';
    }

    if (empty($errors)) {
        $r = $pdo->prepare("SELECT id FROM roles WHERE nombre = ? LIMIT 1");
        $r->execute(['alumno']);
        $rol = $r->fetch(PDO::FETCH_ASSOC);
        $rol_id = $rol['id'] ?? null;
        if (!$rol_id) {
            $errors[] = 'Rol alumno no encontrado. Crea roles en la base de datos.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO usuarios (correo, contrasena_hash, nombre_completo, rol_id, fecha_registro) VALUES (?, ?, ?, ?, NOW())");
            try {
                $ins->execute([$email, $hash, $nombre, $rol_id]);
                $success = true;
            } catch (PDOException $e) {
                $errors[] = 'Error al crear usuario: ' . $e->getMessage();
            }
        }
    }
}

?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Registro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
            <h4 class="mb-3">Crear cuenta</h4>
            <?php if ($success): ?>
                <div class="alert alert-success">Cuenta creada. <a href="login.php">Inicia sesión</a></div>
            <?php endif; ?>
            <?php foreach ($errors as $e): ?>
                <div class="alert alert-danger"><?=h($e)?></div>
            <?php endforeach; ?>
            <?php if (!$success): ?>
            <form method="post">
                <div class="mb-3"><label class="form-label">Nombre completo</label><input name="nombre" class="form-control" required value="<?=h($_POST['nombre'] ?? '')?>"></div>
                <div class="mb-3"><label class="form-label">Correo</label><input name="correo" type="correo" class="form-control" required value="<?=h($_POST['correo'] ?? '')?>"></div>
                <div class="mb-3"><label class="form-label">Contraseña</label><input name="password" type="password" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Repetir contraseña</label><input name="password2" type="password" class="form-control" required></div>
                <button class="btn btn-success w-100">Registrarme</button>
            </form>
            <?php endif; ?>
            <hr>
            <p class="small mb-0">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
            </div>
        </div>
        </div>
    </div>
</div>
</body>
</html>