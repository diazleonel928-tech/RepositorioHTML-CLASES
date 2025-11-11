<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();

$usuario = current_user($pdo);
if (!$usuario) { header('Location: login.php'); exit; }
$uid = intval($usuario['id']);

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token CSRF inválido.';
    } else {
        $actual = $_POST['current_password'] ?? '';
        $nuevo = $_POST['new_password'] ?? '';
        $nuevo2 = $_POST['new_password2'] ?? '';

        if ($actual === '' || $nuevo === '' || $nuevo2 === '') {
            $errors[] = 'Todos los campos son obligatorios.';
        } elseif ($nuevo !== $nuevo2) {
            $errors[] = 'Las nuevas contraseñas no coinciden.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT contrasena_hash FROM usuarios WHERE id = ?");
                $stmt->execute([$uid]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $hash_actual = $row['contrasena_hash'] ?? '';

                if (!password_verify($actual, $hash_actual)) {
                    $errors[] = 'Contraseña actual incorrecta.';
                } else {
                    if (strlen($nuevo) < 6) {
                        $errors[] = 'La nueva contraseña debe tener al menos 6 caracteres.';
                    } else {
                        $nuevo_hash = password_hash($nuevo, PASSWORD_DEFAULT);
                        $upd = $pdo->prepare("UPDATE usuarios SET contrasena_hash = ? WHERE id = ?");
                        $upd->execute([$nuevo_hash, $uid]);
                        $success = 'Contraseña actualizada correctamente.';
                    }
                }
            } catch (PDOException $e) {
                $errors[] = 'Error de base de datos: ' . $e->getMessage();
            }
        }
    }
}

$csrf = generate_csrf_token();
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Cambiar contraseña</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container py-4">
    <a href="profile.php" class="btn btn-secondary mb-3">← Volver</a>
    <h3>Cambiar contraseña</h3>

    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?=h($e)?></div><?php endforeach; ?>
    <?php if ($success): ?><div class="alert alert-success"><?=h($success)?></div><?php endif; ?>

    <form method="post" class="card p-3" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?=h($csrf)?>">
        <div class="mb-3">
        <label class="form-label">Contraseña actual</label>
        <input name="current_password" type="password" class="form-control" required>
        </div>
        <div class="mb-3">
        <label class="form-label">Nueva contraseña</label>
        <input name="new_password" type="password" class="form-control" required>
        </div>
        <div class="mb-3">
        <label class="form-label">Repetir nueva contraseña</label>
        <input name="new_password2" type="password" class="form-control" required>
        </div>
        <button class="btn btn-primary">Cambiar contraseña</button>
    </form>
</div>
</body></html>