<?php
require_once __DIR__ . 'config_database.php';
require_once __DIR__ . 'helper.php';
require_login();
if ($_SESSION['rol_nombre'] !== 'profesor') { http_response_code(403); die('Acceso denegado'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $codigo_acceso = trim($_POST['codigo_acceso'] ?? '');
    $auto_aprobar = isset($_POST['auto_aprobar']) ? 1 : 0;
    $creador_id = intval($_SESSION['usuario_id']);

    if ($nombre === '') $errors[] = 'El nombre es obligatorio.';
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO cursos (codigo, nombre, descripcion, creador_id, fecha_creacion, codigo_acceso, auto_aprobar) VALUES (?, ?, ?, ?, NOW(), ?, ?)");
        $stmt->bind_param('sssisi', $codigo, $nombre, $descripcion, $creador_id, $codigo_acceso, $auto_aprobar);
        if ($stmt->execute()) {
            $new_id = $stmt->insert_id;
            $stmt->close();
            header('Location: profesorCurso.php?msg=curso_creado');
            exit;
        } else {
            $errors[] = 'Error al crear curso: ' . $conn->error;
            $stmt->close();
        }
    }
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Crear curso</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light">
<div class="container mt-4">
<h3>Crear curso</h3>
<?php if ($errors): foreach ($errors as $e): ?><div class="alert alert-danger"><?=htmlspecialchars($e)?></div><?php endforeach; endif; ?>
<form method="post">
    <div class="mb-3"><label class="form-label">Código (opcional)</label><input name="codigo" class="form-control" value="<?=htmlspecialchars($_POST['codigo'] ?? '')?>"></div>
    <div class="mb-3"><label class="form-label">Nombre</label><input name="nombre" class="form-control" required value="<?=htmlspecialchars($_POST['nombre'] ?? '')?>"></div>
    <div class="mb-3"><label class="form-label">Descripción</label><textarea name="descripcion" class="form-control"><?=htmlspecialchars($_POST['descripcion'] ?? '')?></textarea></div>
    <div class="mb-3"><label class="form-label">Código de acceso (opcional)</label><input name="codigo_acceso" class="form-control" value="<?=htmlspecialchars($_POST['codigo_acceso'] ?? '')?>"></div>
    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="auto_aprobar" name="auto_aprobar" <?=isset($_POST['auto_aprobar']) ? 'checked' : ''?>><label class="form-check-label" for="auto_aprobar">Aprobar inscripciones automáticamente</label></div>
    <button class="btn btn-success">Crear</button>
    <a class="btn btn-secondary" href="profesorCurso.php">Cancelar</a>
</form>
</div>
</body>
</html>