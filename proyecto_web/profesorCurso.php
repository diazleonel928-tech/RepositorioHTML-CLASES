<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();
if ($rol == 'profesor' && $rol == 'admin') {
    http_response_code(403);
    die('Acceso denegado');
}

$profesor_id = intval($_SESSION['usuario_id']);
try {
    $stmt = $pdo->prepare("SELECT id, codigo, nombre, descripcion, fecha_creacion FROM cursos WHERE creador_id = ? ORDER BY fecha_creacion DESC");
    $stmt->execute([$profesor_id]);
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error BD: " . $e->getMessage());
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Mis cursos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container py-4">
    <a href="home.php" class="btn btn-secondary mb-3">← Volver</a>
    <h3>Mis cursos</h3>
    <a class="btn btn-success mb-3" href="crear_curso.php">Crear curso</a>
    <?php if (empty($cursos)): ?>
        <div class="alert alert-info">No tienes cursos.</div>
    <?php else: ?>
        <div class="list-group">
        <?php foreach ($cursos as $c): ?>
            <div class="list-group-item d-flex justify-content-between">
            <div>
                <strong><?=h($c['nombre'])?></strong><br><small class="text-muted"><?=h($c['codigo'])?></small>
                <div class="small"><?=h(mb_strimwidth($c['descripcion'] ?? '',0,150,'...'))?></div>
            </div>
            <div class="text-end">
                <a class="btn btn-sm btn-primary" href="cursoDetalles.php?id=<?=intval($c['id'])?>">Ver</a>
                <a class="btn btn-sm btn-warning" href="editarCurso.php?id=<?=intval($c['id'])?>">Editar</a>
            </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>