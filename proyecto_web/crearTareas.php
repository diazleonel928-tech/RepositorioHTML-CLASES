<?php
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();
if ($rol == 'profesor' && $rol == 'admin') {
    http_response_code(403);
    die('Acceso denegado');
}
$profesor_id = intval($_SESSION['usuario_id']);
$curso_id = intval($_GET['curso_id'] ?? $_POST['curso_id'] ?? 0);
if ($curso_id <= 0) die('Curso inválido.');

$stmt = $conn->prepare("SELECT creador_id, nombre FROM cursos WHERE id = ?");
$stmt->bind_param('i', $curso_id); $stmt->execute(); $stmt->bind_result($creador_id, $curso_nombre);
if (!$stmt->fetch()) { $stmt->close(); die('Curso no encontrado'); } $stmt->close();
if (intval($creador_id) !== $profesor_id) die('No tienes permiso.');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha_entrega = trim($_POST['fecha_entrega'] ?? '');
    $ponderacion = floatval($_POST['ponderacion'] ?? 0);
    if ($titulo === '') $errors[] = 'El título es obligatorio.';
    if ($fecha_entrega === '') $errors[] = 'La fecha de entrega es obligatoria.';
    if (empty($errors)) {
        $ins = $conn->prepare("INSERT INTO tareas (curso_id, titulo, descripcion, fecha_entrega, ponderacion, creado_por) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->bind_param('issdii', $curso_id, $titulo, $descripcion, $fecha_entrega, $ponderacion, $profesor_id);
        if ($ins->execute()) { $ins->close(); header('Location: cursoDetalles.php?id=' . $curso_id . '&msg=tarea_creada'); exit; }
        else { $errors[] = 'Error al crear tarea: ' . $conn->error; $ins->close(); }
    }
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Crear tarea</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light">
<div class="container mt-4">
<h3>Crear tarea — Curso: <?=htmlspecialchars($curso_nombre)?></h3>
<?php if ($errors): foreach ($errors as $e): ?><div class="alert alert-danger"><?=htmlspecialchars($e)?></div><?php endforeach; endif; ?>
<form method="post">
    <input type="hidden" name="curso_id" value="<?=intval($curso_id)?>">
    <div class="mb-3"><label class="form-label">Título</label><input name="titulo" class="form-control" required value="<?=htmlspecialchars($_POST['titulo'] ?? '')?>"></div>
    <div class="mb-3"><label class="form-label">Descripción</label><textarea name="descripcion" class="form-control"><?=htmlspecialchars($_POST['descripcion'] ?? '')?></textarea></div>
    <div class="mb-3"><label class="form-label">Fecha de entrega (YYYY-MM-DD HH:MM:SS)</label><input name="fecha_entrega" class="form-control" required placeholder="2025-12-31 23:59:00" value="<?=htmlspecialchars($_POST['fecha_entrega'] ?? '')?>"></div>
    <div class="mb-3"><label class="form-label">Ponderación (por ejemplo 0.3 para 30%)</label><input name="ponderacion" class="form-control" value="<?=htmlspecialchars($_POST['ponderacion'] ?? '1')?>"></div>
    <button class="btn btn-success">Crear tarea</button>
    <a class="btn btn-secondary" href="cursoDetalles.php?id=<?=intval($curso_id)?>">Cancelar</a>
</form>
</div>
</body></html>