<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();

$user = current_user($pdo);
$uid = intval($user['id']);
$errors = [];
$success = null;

$stmt = $pdo->prepare("SELECT * FROM profesor_solicitudes WHERE usuario_id = ? ORDER BY fecha_solicitud DESC LIMIT 1");
$stmt->execute([$uid]);
$last = $stmt->fetch(PDO::FETCH_ASSOC);
if ($last && $last['estado'] === 'PENDIENTE') {
    $errors[] = 'Ya tienes una solicitud pendiente. Espera la respuesta del administrador.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) $errors[] = 'Token CSRF inválido.';
    $profesion = trim($_POST['profesion'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');
    $cvPath = null;
    if (!empty($_FILES['cv']['name'])) {
        try {
            $allowed = ['pdf','doc','docx'];
            $ext = pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION);
            if ($ext && !in_array(strtolower($ext), $allowed)) throw new Exception('Tipo de archivo no permitido para CV.');
            $cvPath = save_uploaded_file($_FILES['cv'], 'cv_u' . $uid . '_', '');
        } catch (Exception $e) {
            $errors[] = 'Error al subir CV: ' . $e->getMessage();
        }
    }

    if (empty($errors)) {
        $ins = $pdo->prepare("INSERT INTO profesor_solicitudes (usuario_id, profesion, cv, mensaje) VALUES (?, ?, ?, ?)");
        try {
            $ins->execute([$uid, $profesion ?: null, $cvPath, $mensaje ?: null]);
            $success = 'Solicitud enviada correctamente. El administrador la revisará.';
        } catch (PDOException $e) {
            $errors[] = 'Error BD: ' . $e->getMessage();
            if ($cvPath) @unlink_public_file($cvPath);
        }
    }
}

$csrf = generate_csrf_token();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Solicitar rol Profesor</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container py-4">
    <a href="home.php" class="btn btn-secondary mb-3">← Volver</a>
    <h3>Solicitar ser Profesor</h3>

    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?=h($e)?></div><?php endforeach; ?>
    <?php if ($success): ?><div class="alert alert-success"><?=h($success)?></div><?php endif; ?>

    <?php if (!$success): ?>
    <form method="post" enctype="multipart/form-data" class="card p-3">
        <input type="hidden" name="csrf_token" value="<?=h($csrf)?>">
        <div class="mb-3"><label class="form-label">Profesión / cargo</label><input name="profesion" class="form-control" value="<?=h($_POST['profesion'] ?? '')?>"></div>
        <div class="mb-3"><label class="form-label">Mensaje (por qué quieres ser profesor)</label><textarea name="mensaje" class="form-control"><?=h($_POST['mensaje'] ?? '')?></textarea></div>
        <div class="mb-3"><label class="form-label">CV (opcional, pdf/docx)</label><input type="file" name="cv" class="form-control"></div>
        <button class="btn btn-primary">Enviar solicitud</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>