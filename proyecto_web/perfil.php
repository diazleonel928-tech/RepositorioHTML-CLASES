<?php
// public/profile.php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();

$uid = intval($_SESSION['usuario_id']);
$errors = []; $success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($nombre === '' || $email === '') $errors[] = 'Nombre y correo obligatorios.';
    if (empty($errors)) {
        $upd = $pdo->prepare("UPDATE usuarios SET nombre_completo = ?, email = ? WHERE id = ?");
        try {
            $upd->execute([$nombre, $email, $uid]);
            $success = 'Perfil actualizado';
            $_SESSION['usuario_nombre'] = $nombre;
        } catch (PDOException $e) { $errors[] = 'Error BD: '.$e->getMessage(); }
    }
}

$stmt = $pdo->prepare("SELECT id, correo, nombre_completo, fecha_registro FROM usuarios WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
function h($v){ return htmlspecialchars($v, ENT_QUOTES,'UTF-8'); }
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Perfil</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container py-4">
    <a href="home.php" class="btn btn-secondary mb-3">← Volver</a>
    <h3>Mi perfil</h3>
    <?php if ($success) echo '<div class="alert alert-success">'.h($success).'</div>'; ?>
    <?php foreach ($errors as $e) echo '<div class="alert alert-danger">'.h($e).'</div>'; ?>
    <form method="post" class="card p-3">
        <div class="mb-3"><label>Nombre</label><input name="nombre" class="form-control" value="<?=h($user['nombre_completo'] ?? '')?>" required></div>
        <div class="mb-3"><label>Correo</label><input name="email" type="email" class="form-control" value="<?=h($user['email'] ?? '')?>" required></div>
        <button class="btn btn-primary">Guardar</button>
    </form>
</div>
</body></html>