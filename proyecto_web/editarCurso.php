<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();
if (($_SESSION['rol_nombre'] ?? '') !== 'profesor') { http_response_code(403); die('Acceso denegado'); }

$curso_id = intval($_GET['id'] ?? 0);
if ($curso_id <= 0) die('Curso inválido.');
$profesor_id = intval($_SESSION['usuario_id']);
$errors = [];

$stmt = $pdo->prepare("SELECT id, codigo, nombre, descripcion, codigo_acceso, auto_aprobar, creador_id FROM cursos WHERE id = ?");
$stmt->execute([$curso_id]);
$curso = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$curso) die('No encontrado.');
if (intval($curso['creador_id']) !== $profesor_id) die('No tienes permiso.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $codigo_acceso = trim($_POST['codigo_acceso'] ?? '');
    $auto_aprobar = isset($_POST['auto_aprobar']) ? 1 : 0;
    if ($nombre === '') $errors[] = 'Nombre obligatorio.';
    if (empty($errors)) {
        $upd = $pdo->prepare("UPDATE cursos SET codigo = ?, nombre = ?, descripcion = ?, codigo_acceso = ?, auto_aprobar = ? WHERE id = ?");
        try {
            $upd->execute([$codigo, $nombre, $descripcion, $codigo_acceso, $auto_aprobar, $curso_id]);
            header('Location: profesorCursos.php?msg=curso_actualizado');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Error BD: ' . $e->getMessage();
        }
    }
}
function h($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Editar curso</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container mt-4">
    <a href="profesorCursos.php" class="btn btn-secondary mb-3">← Volver</a>
    <h3>Editar curso</h3>
    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?=h($e)?></div><?php endforeach; ?>
    <form method="post">
        <div class="mb-3"><label>Código</label><input name="codigo" class="form-control" value="<?=h($_POST['codigo'] ?? $curso['codigo'])?>"></div>
        <div class="mb-3"><label>Nombre</label><input name="nombre" class="form-control" required value="<?=h($_POST['nombre'] ?? $curso['nombre'])?>"></div>
        <div class="mb-3"><label>Descripción</label><textarea name="descripcion" class="form-control"><?=h($_POST['descripcion'] ?? $curso['descripcion'])?></textarea></div>
        <div class="mb-3"><label>Código de acceso</label><input name="codigo_acceso" class="form-control" value="<?=h($_POST['codigo_acceso'] ?? $curso['codigo_acceso'])?>"></div>
        <div class="form-check mb-3"><input type="checkbox" name="auto_aprobar" class="form-check-input" id="auto_aprobar" <?= (isset($_POST['auto_aprobar']) ? 'checked' : ($curso['auto_aprobar'] ? 'checked' : '')) ?>><label for="auto_aprobar" class="form-check-label">Aprobar inscripciones automáticamente</label></div>
        <button class="btn btn-primary">Guardar</button>
    </form>
</div>
</body></html>