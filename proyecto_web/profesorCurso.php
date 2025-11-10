<?php
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();
if ($_SESSION['rol_nombre'] !== 'profesor') { http_response_code(403); die('Acceso denegado'); }

$profesor_id = intval($_SESSION['usuario_id']);

$stmt = $conn->prepare("SELECT id, codigo, nombre, descripcion, fecha_creacion FROM cursos WHERE creador_id = ? ORDER BY fecha_creacion DESC");
$stmt->bind_param('i', $profesor_id);
$stmt->execute();
$res = $stmt->get_result();
$cursos = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Mis cursos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light">
<div class="container mt-4">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Mis cursos</h3>
    <div>
    <a class="btn btn-success" href="crearCurso.php">Crear curso</a>
    <a class="btn btn-secondary" href="home.php">Volver</a>
    </div>
</div>

<?php if (empty($cursos)): ?>
    <div class="alert alert-info">Aún no has creado cursos.</div>
<?php else: ?>
    <div class="list-group">
    <?php foreach ($cursos as $c): ?>
        <div class="list-group-item d-flex justify-content-between align-items-center">
        <div>
            <strong><?=htmlspecialchars($c['nombre'])?></strong><br>
            <small class="text-muted"><?=htmlspecialchars($c['codigo'] ?? '')?> — <?=htmlspecialchars($c['fecha_creacion'])?></small>
            <div class="small"><?=nl2br(htmlspecialchars($c['descripcion'] ?? ''))?></div>
            </div>
            <div class="text-end">
            <a class="btn btn-sm btn-primary" href="cursoDetalle.php?id=<?=intval($c['id'])?>">Ver</a>
            <a class="btn btn-sm btn-warning" href="editarCurso.php?id=<?=intval($c['id'])?>">Editar</a>
            <a class="btn btn-sm btn-outline-secondary" href="crearTarea.php?curso_id=<?=intval($c['id'])?>">Crear tarea</a>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>
</div>
</body>
</html>