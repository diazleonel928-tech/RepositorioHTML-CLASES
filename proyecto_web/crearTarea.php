<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();

$rol = $_SESSION['rol_nombre'] ?? '';
if ($rol == 'profesor' && $rol == 'admin') {
    http_response_code(403);
    die('Acceso denegado');
}

$profesor = current_user($pdo);
$profesor_id = intval($profesor['id']);

$curso_id = intval($_GET['curso_id'] ?? $_POST['curso_id'] ?? 0);
if ($curso_id <= 0) die('Curso inválido.');

$errors = [];
$success = false;

$stmt = $pdo->prepare("SELECT id, nombre, creador_id FROM cursos WHERE id = ?");
$stmt->execute([$curso_id]);
$curso = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$curso) die('Curso no encontrado.');
if (intval($curso['creador_id']) !== $profesor_id) die('No tienes permiso para crear tareas en este curso.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { $errors[] = 'Token CSRF inválido.'; }
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha_entrega = trim($_POST['fecha_entrega'] ?? '');
    $ponderacion = floatval($_POST['ponderacion'] ?? 0);

    if ($titulo === '') $errors[] = 'Título obligatorio.';
    if ($fecha_entrega === '') $errors[] = 'Fecha de entrega obligatoria (YYYY-MM-DD HH:MM:SS).';

    if (empty($errors)) {
        try {
            $ins = $pdo->prepare("INSERT INTO tareas (curso_id, titulo, descripcion, fecha_entrega, ponderacion, creado_por) VALUES (?, ?, ?, ?, ?, ?)");
            $ins->execute([$curso_id, $titulo, $descripcion, $fecha_entrega, $ponderacion, $profesor_id]);
            $success = true;
            header('Location: cursoDetalles.php?id=' . $curso_id . '&msg=tarea_creada');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Error BD: ' . $e->getMessage();
        }
    }
}

$csrf = generate_csrf_token();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Crear tarea</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container py-4">
    <a href="cursoDetalles.php?id=<?=intval($curso_id)?>" class="btn btn-secondary mb-3">← Volver</a>
    <h3>Crear tarea — <?=h($curso['nombre'])?></h3>
    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?=h($e)?></div><?php endforeach; ?>
    <form method="post" class="card p-3">
        <input type="hidden" name="curso_id" value="<?=intval($curso_id)?>">
        <input type="hidden" name="csrf_token" value="<?=h($csrf)?>">
        <div class="mb-3">
        <label class="form-label">Título</label>
        <input name="titulo" class="form-control" required value="<?=h($_POST['titulo'] ?? '')?>">
        </div>
        <div class="mb-3">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-control"><?=h($_POST['descripcion'] ?? '')?></textarea>
        </div>
        <div class="mb-3">
        <label class="form-label">Fecha de entrega (YYYY-MM-DD HH:MM:SS)</label>
        <input name="fecha_entrega" class="form-control" placeholder="2025-12-31 23:59:00" value="<?=h($_POST['fecha_entrega'] ?? '')?>" required>
        </div>
        <div class="mb-3">
        <label class="form-label">Ponderación (ej. 0.3 para 30%)</label>
        <input name="ponderacion" class="form-control" value="<?=h($_POST['ponderacion'] ?? '1')?>">
        </div>
        <button class="btn btn-success">Crear tarea</button>
    </form>
</div>
</body>
</html>