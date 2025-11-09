<?php
require_once __DIR__ . 'config_database.php';
require_once __DIR__ . 'helper.php';
require_login();
if ($_SESSION['rol_nombre'] !== 'profesor') { http_response_code(403); die('Acceso denegado'); }

$profesor_id = intval($_SESSION['usuario_id']);
$curso_id = intval($_GET['id'] ?? 0);
if ($curso_id <= 0) die('Curso inválido.');

$stmt = $conn->prepare("SELECT id, codigo, nombre, descripcion, codigo_acceso, auto_aprobar, creador_id FROM cursos WHERE id = ?");
$stmt->bind_param('i', $curso_id); $stmt->execute(); $stmt->bind_result($id,$codigo,$nombre,$descripcion,$codigo_acceso,$auto_aprobar,$creador_id);
if (!$stmt->fetch()) { $stmt->close(); die('Curso no encontrado.'); }
$stmt->close();

if (intval($creador_id) !== $profesor_id) die('No tienes permiso para editar este curso.');

$errors=[];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_n = trim($_POST['codigo'] ?? '');
    $nombre_n = trim($_POST['nombre'] ?? '');
    $descripcion_n = trim($_POST['descripcion'] ?? '');
    $codigo_acceso_n = trim($_POST['codigo_acceso'] ?? '');
    $auto_aprobar_n = isset($_POST['auto_aprobar']) ? 1 : 0;
    if ($nombre_n === '') $errors[] = 'El nombre es obligatorio.';
    if (empty($errors)) {
        $u = $conn->prepare("UPDATE cursos SET codigo = ?, nombre = ?, descripcion = ?, codigo_acceso = ?, auto_aprobar = ? WHERE id = ?");
        $u->bind_param('ssssii', $codigo_n, $nombre_n, $descripcion_n, $codigo_acceso_n, $auto_aprobar_n, $curso_id);
        if ($u->execute()) { $u->close(); header('Location: profesorCursos.php?msg=curso_actualizado'); exit; }
        else { $errors[] = 'Error: ' . $conn->error; $u->close(); }
    }
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Editar curso</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light">
<div class="container mt-4">
<h3>Editar curso</h3>
<?php if ($errors): foreach ($errors as $e): ?><div class="alert alert-danger"><?=htmlspecialchars($e)?></div><?php endforeach; endif; ?>
<form method="post">
    <div class="mb-3"><label class="form-label">Código (opcional)</label><input name="codigo" class="form-control" value="<?=htmlspecialchars($_POST['codigo'] ?? $codigo)?>"></div>
    <div class="mb-3"><label class="form-label">Nombre</label><input name="nombre" class="form-control" required value="<?=htmlspecialchars($_POST['nombre'] ?? $nombre)?>"></div>
    <div class="mb-3"><label class="form-label">Descripción</label><textarea name="descripcion" class="form-control"><?=htmlspecialchars($_POST['descripcion'] ?? $descripcion)?></textarea></div>
    <div class="mb-3"><label class="form-label">Código de acceso (opcional)</label><input name="codigo_acceso" class="form-control" value="<?=htmlspecialchars($_POST['codigo_acceso'] ?? $codigo_acceso)?>"></div>
    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="auto_aprobar" name="auto_aprobar" <?= (isset($_POST['auto_aprobar']) ? 'checked' : ($auto_aprobar ? 'checked' : '')) ?>><label class="form-check-label" for="auto_aprobar">Aprobar inscripciones automáticamente</label></div>
    <button class="btn btn-primary">Guardar cambios</button>
    <a class="btn btn-secondary" href="profesorCursos.php">Cancelar</a>
</form>
</div>
</body></html>