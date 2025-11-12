<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();
if (!es_admin()) { http_response_code(403); die('Acceso denegado'); }

$uid = intval($_GET['id'] ?? $_POST['id'] ?? 0);
if ($uid <= 0) { header('Location: admin.php?msg=invalid'); exit; }

$errors = [];
$success = null;

try {
    $rolesStmt = $pdo->query("SELECT id, nombre FROM roles ORDER BY id ASC");
    $roles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT id, correo, nombre_completo, rol_id FROM usuarios WHERE id = ?");
    $stmt->execute([$uid]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) { header('Location: admin.php?msg=no_user'); exit; }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            $errors[] = 'Token CSRF inválido.';
        } else {
            $nombre = trim($_POST['nombre'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $rol_id = intval($_POST['rol_id'] ?? 0);

            if ($nombre === '' || $correo === '') $errors[] = 'Nombre y correo obligatorios.';
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo inválido.';

            $s = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ? AND id != ? LIMIT 1");
            $s->execute([$correo, $uid]);
            if ($s->fetch()) $errors[] = 'El correo ya está en uso por otro usuario.';

            $r = $pdo->prepare("SELECT id FROM roles WHERE id = ? LIMIT 1");
            $r->execute([$rol_id]);
            if (!$r->fetch()) $errors[] = 'Rol seleccionado inválido.';

            if (empty($errors)) {
                $upd = $pdo->prepare("UPDATE usuarios SET correo = ?, nombre_completo = ?, rol_id = ? WHERE id = ?");
                $upd->execute([$correo, $nombre, $rol_id, $uid]);
                $success = 'Usuario actualizado correctamente.';
                $stmt->execute([$uid]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
    }
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}

$csrf = generate_csrf_token();
?>

<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Editar usuario</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container py-4">
    <a href="admin.php" class="btn btn-secondary mb-3">← Volver</a>
    <h3>Editar usuario — <?=h_local($user['nombre_completo'])?></h3>

    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?=h_local($e)?></div><?php endforeach; ?>
    <?php if ($success): ?><div class="alert alert-success"><?=h_local($success)?></div><?php endif; ?>

    <form method="post" class="card p-3">
        <input type="hidden" name="csrf_token" value="<?=h_local($csrf)?>">
        <input type="hidden" name="id" value="<?=h_local($user['id'])?>">
        <div class="mb-3"><label class="form-label">Nombre</label><input name="nombre" class="form-control" required value="<?=h_local($_POST['nombre'] ?? $user['nombre_completo'])?>"></div>
        <div class="mb-3"><label class="form-label">Correo</label><input name="correo" type="email" class="form-control" required value="<?=h_local($_POST['correo'] ?? $user['correo'])?>"></div>
        <div class="mb-3"><label class="form-label">Rol</label>
        <select name="rol_id" class="form-select">
            <?php foreach ($roles as $r): ?>
            <option value="<?=h_local($r['id'])?>" <?= ( ($r['id']==($_POST['rol_id'] ?? $user['rol_id'])) ? 'selected' : '' ) ?>><?=h_local($r['nombre'])?></option>
            <?php endforeach; ?>
        </select>
        </div>
        <div class="d-flex gap-2">
        <button class="btn btn-primary">Guardar cambios</button>
        <a href="admin.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
</body>
</html>