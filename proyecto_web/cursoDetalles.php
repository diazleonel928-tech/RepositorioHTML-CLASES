<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();

$usuario_id = intval($_SESSION['usuario_id']);
$rol = $_SESSION['rol_nombre'] ?? '';
$curso_id = intval($_GET['id'] ?? 0);
if ($curso_id <= 0) header('Location: cursos.php');

function h($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

try {
    $stmt = $pdo->prepare("SELECT c.id, c.codigo, c.nombre, c.descripcion, c.creador_id, u.nombre_completo AS creador_nombre FROM cursos c LEFT JOIN usuarios u ON c.creador_id = u.id WHERE c.id = ?");
    $stmt->execute([$curso_id]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$curso) die('Curso no encontrado.');
    $estado = null;
    if ($rol === 'alumno') {
        $s = $pdo->prepare("SELECT estado FROM inscripciones WHERE curso_id = ? AND estudiante_id = ?");
        $s->execute([$curso_id, $usuario_id]);
        $r = $s->fetch(PDO::FETCH_ASSOC);
        $estado = $r['estado'] ?? null;
    }
    $t = $pdo->prepare("SELECT id, titulo, descripcion, fecha_entrega, ponderacion FROM tareas WHERE curso_id = ? ORDER BY fecha_entrega ASC");
    $t->execute([$curso_id]);
    $tareas = $t->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <title><?=h($curso['nombre'])?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container mt-4">
    <a class="btn btn-secondary mb-3" href="cursos.php">← Volver</a>
    <h3><?=h($curso['nombre'])?></h3>
    <p class="small text-muted">Profesor: <?=h($curso['creador_nombre'] ?? '')?></p>
    <p><?=nl2br(h($curso['descripcion']))?></p>

    <?php if ($rol === 'alumno' && $estado !== 'APROBADO'): ?>
        <div class="alert alert-warning">No puedes acceder a tareas hasta que tu inscripción sea aprobada. Estado: <?=h($estado ?? 'Sin solicitar')?></div>
    <?php endif; ?>

    <h5 class="mt-4">Tareas</h5>
    <?php if (empty($tareas)): ?>
        <p class="text-muted">No hay tareas registradas.</p>
    <?php else: ?>
        <div class="list-group">
        <?php foreach ($tareas as $ta): ?>
            <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <strong><?=h($ta['titulo'])?></strong><br><small class="text-muted"><?=h($ta['fecha_entrega'])?></small>
                <div class="small"><?=h(mb_strimwidth($ta['descripcion'] ?? '', 0, 150, '...'))?></div>
            </div>
            <div>
                <?php if ($rol === 'alumno' && $estado === 'APROBADO'): ?>
                <a class="btn btn-sm btn-primary" href="tareaDetalles.php?id=<?=intval($ta['id'])?>">Ver / Entregar</a>
                <?php else: ?>
                <a class="btn btn-sm btn-outline-secondary" href="tarea_detalle.php?id=<?=intval($ta['id'])?>">Ver</a>
                <?php endif; ?>
                <?php if ($rol === 'profesor' && intval($curso['creador_id']) === $usuario_id): ?>
                <a class="btn btn-sm btn-warning" href="editarTarea.php?id=<?=intval($ta['id'])?>">Editar</a>
                <?php endif; ?>
            </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>