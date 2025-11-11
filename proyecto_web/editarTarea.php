<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();
if (!is_role($pdo, 'profesor')) { http_response_code(403); die('Acceso denegado'); }

$profesor = current_user($pdo);
$profesor_id = intval($profesor['id']);
$tarea_id = intval($_GET['id'] ?? $_POST['tarea_id'] ?? 0);
if ($tarea_id <= 0) die('Tarea inválida.');

$stmt = $pdo->prepare("SELECT t.*, c.creador_id, c.nombre as curso_nombre FROM tareas t JOIN cursos c ON t.curso_id = c.id WHERE t.id = ?");
$stmt->execute([$tarea_id]);
$t = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$t) die('Tarea no encontrada.');
if (intval($t['creador_id']) !== $profesor_id) die('No tienes permiso.');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { $errors[] = 'Token CSRF inválido.'; }
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha_entrega = trim($_POST['fecha_entrega'] ?? '');
    $ponderacion = floatval($_POST['ponderacion'] ?? 0);

    if ($titulo === '') $errors[] = 'Título obligatorio.';
    if ($fecha_entrega === '') $errors[] = 'Fecha de entrega obligatoria.';

    if (empty($errors)) {
        try {
            $upd = $pdo->prepare("UPDATE tareas SET titulo = ?, descripcion = ?, fecha_entrega = ?, ponderacion = ? WHERE id = ?");
            $upd->execute([$titulo, $descripcion, $fecha_entrega, $ponderacion, $tarea_id]);
            header('Location: cursoDetalles.php?id=' . intval($t['curso_id']) . '&msg=tarea_actualizada');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Error BD: ' . $e->getMessage();
        }
    }
}

$csrf = generate_csrf_token();
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Editar tarea</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container py-4">
    <a href="curso_detalle.php?id=<?=intval($t['curso_id'])?>" class="btn btn-secondary mb-3">← Volver</a>
    <h3>Editar tarea — <?=h($t['curso_nombre'])?></h3>
    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?=h($e)?></div><?php endforeach; ?>
    <form method="post" class="card p-3">
        <input type="hidden" name="tarea_id" value="<?=intval($tarea_id)?>">
        <input type="hidden" name="csrf_token" value="<?=h($csrf)?>">
        <div class="mb-3"><label class="form-label">Título</label><input name="titulo" class="form-control" required value="<?=h($_POST['titulo'] ?? $t['titulo'])?>"></div>
        <div class="mb-3"><label class="form-label">Descripción</label><textarea name="descripcion" class="form-control"><?=h($_POST['descripcion'] ?? $t['descripcion'])?></textarea></div>
        <div class="mb-3"><label class="form-label">Fecha de entrega</label><input name="fecha_entrega" class="form-control" required value="<?=h($_POST['fecha_entrega'] ?? $t['fecha_entrega'])?>"></div>
        <div class="mb-3"><label class="form-label">Ponderación</label><input name="ponderacion" class="form-control" value="<?=h($_POST['ponderacion'] ?? $t['ponderacion'])?>"></div>
        <button class="btn btn-primary">Guardar cambios</button>
    </form>
</div>
</body></html>