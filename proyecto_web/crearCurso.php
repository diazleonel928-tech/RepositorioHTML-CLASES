<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();
if ($rol == 'profesor' && $rol == 'admin') {
    http_response_code(403);
    die('Acceso denegado');
}
$errors = [];
$creador_id = intval($_SESSION['usuario_id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $codigo_acceso = trim($_POST['codigo_acceso'] ?? '');
    $auto_aprobar = isset($_POST['auto_aprobar']) ? 1 : 0;

    if ($nombre === '') $errors[] = 'Nombre obligatorio.';
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO cursos (codigo, nombre, descripcion, creador_id, fecha_creacion, codigo_acceso, auto_aprobar) VALUES (?, ?, ?, ?, NOW(), ?, ?)");
            $stmt->execute([$codigo, $nombre, $descripcion, $creador_id, $codigo_acceso, $auto_aprobar]);
            header('Location: profesorCurso.php?msg=curso_creado');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Error BD: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Crear curso</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container mt-4">
    <a href="home.php" class="btn btn-secondary mb-3">← Volver</a>
    <h3>Crear curso</h3>
    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?=h($e)?></div><?php endforeach; ?>
    <form method="post">
    <div class="mb-3"><label>Código</label><input name="codigo" class="form-control" value="<?=h($_POST['codigo'] ?? '')?>"></div>
    <div class="mb-3"><label>Nombre</label><input name="nombre" class="form-control" required value="<?=h($_POST['nombre'] ?? '')?>"></div>
    <div class="mb-3"><label>Descripción</label><textarea name="descripcion" class="form-control"><?=h($_POST['descripcion'] ?? '')?></textarea></div>
    <div class="mb-3"><label>Código de acceso</label><input name="codigo_acceso" class="form-control" value="<?=h($_POST['codigo_acceso'] ?? '')?>"></div>
    <div class="form-check mb-3"><input type="checkbox" name="auto_aprobar" class="form-check-input" id="auto_aprobar" <?=isset($_POST['auto_aprobar']) ? 'checked' : ''?>><label for="auto_aprobar" class="form-check-label">Aprobar inscripciones automáticamente</label></div>
    <button class="btn btn-success">Crear</button>
    </form>
</div>
</body>
</html>