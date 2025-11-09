<?php
require_once __DIR__ . 'config_database.php';
require_once __DIR__ . 'helper.php';
require_login();
if ($_SESSION['rol_nombre'] !== 'profesor') { http_response_code(403); die('Acceso denegado'); }

$profesor_id = intval($_SESSION['usuario_id']);
$tarea_id = intval($_GET['id'] ?? $_POST['tarea_id'] ?? 0);
if ($tarea_id <= 0) die('Tarea inválida.');

$stmt = $conn->prepare("SELECT t.id, t.titulo, t.descripcion, t.fecha_entrega, t.ponderacion, t.curso_id, c.nombre, c.creador_id FROM tareas t JOIN cursos c ON t.curso_id = c.id WHERE t.id = ?");
$stmt->bind_param('i', $tarea_id);
$stmt->execute();
$stmt->bind_result($id,$titulo,$descripcion,$fecha_entrega,$ponderacion,$curso_id,$curso_nombre,$creador_id);
if (!$stmt->fetch()) { $stmt->close(); die('Tarea no encontrada'); }
$stmt->close();

if (intval($creador_id) !== $profesor_id) die('No tienes permiso para editar esta tarea.');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo_n = trim($_POST['titulo'] ?? '');
    $descripcion_n = trim($_POST['descripcion'] ?? '');
    $fecha_entrega_n = trim($_POST['fecha_entrega'] ?? '');
    $ponderacion_n = floatval($_POST['ponderacion'] ?? 0);
    if ($titulo_n === '') $errors[] = 'El título es obligatorio.';
    if ($fecha_entrega_n === '') $errors[] = 'La fecha de entrega es obligatoria.';
    if (empty($errors)) {
        $u = $conn->prepare("UPDATE tareas SET titulo = ?, descripcion = ?, fecha_entrega = ?, ponderacion = ? WHERE id = ?");
        $u->bind_param('sssdi', $titulo_n, $descripcion_n, $fecha_entrega_n, $ponderacion_n, $tarea_id);
        if ($u->execute()) { $u->close(); header('Location: cursoDetalles.php?id=' . intval($curso_id) . '&msg=tarea_actualizada'); exit; }
        else { $errors[] = 'Error: ' . $conn->error; $u->close(); }
    }
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Editar tarea</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light">
<div class="container mt-4">
<h3>Editar tarea — Curso: <?=htmlspecialchars($curso_nombre)?></h3>
<?php if ($errors): foreach ($errors as $e): ?><div class="alert alert-danger"><?=htmlspecialchars($e)?></div><?php endforeach; endif; ?>
<form method="post">
    <input type="hidden" name="tarea_id" value="<?=intval($tarea_id)?>">
    <div class="mb-3"><label class="form-label">Título</label><input name="titulo" class="form-control" required value="<?=htmlspecialchars($_POST['titulo'] ?? $titulo)?>"></div>
    <div class="mb-3"><label class="form-label">Descripción</label><textarea name="descripcion" class="form-control"><?=htmlspecialchars($_POST['descripcion'] ?? $descripcion)?></textarea></div>
    <div class="mb-3"><label class="form-label">Fecha de entrega (YYYY-MM-DD HH:MM:SS)</label><input name="fecha_entrega" class="form-control" required value="<?=htmlspecialchars($_POST['fecha_entrega'] ?? $fecha_entrega)?>"></div>
    <div class="mb-3"><label class="form-label">Ponderación</label><input name="ponderacion" class="form-control" value="<?=htmlspecialchars($_POST['ponderacion'] ?? $ponderacion)?>"></div>
    <button class="btn btn-primary">Guardar cambios</button>
    <a class="btn btn-secondary" href="cursoDetalles.php?id=<?=intval($curso_id)?>">Cancelar</a>
</form>
</div>
</body></html>