<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();

$usuario = current_user($pdo);
$usuario_id = intval($usuario['id']);
$rol = $_SESSION['rol_nombre'] ?? '';

$tarea_id = intval($_GET['id'] ?? 0);
if ($tarea_id <= 0) die('Tarea inválida.');

$stmt = $pdo->prepare("
    SELECT t.*, c.id AS curso_id, c.nombre AS curso_nombre, c.creador_id
    FROM tareas t JOIN cursos c ON t.curso_id = c.id
    WHERE t.id = ?
");
$stmt->execute([$tarea_id]);
$tarea = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$tarea) die('Tarea no encontrada.');

$curso_id = intval($tarea['curso_id']);

$estado = null;
if ($rol === 'alumno') {
    $s = $pdo->prepare("SELECT estado FROM inscripciones WHERE curso_id = ? AND estudiante_id = ?");
    $s->execute([$curso_id, $usuario_id]);
    $r = $s->fetch(PDO::FETCH_ASSOC);
    $estado = $r['estado'] ?? null;
}

$mi_entrega = null;
if ($rol === 'alumno') {
    $se = $pdo->prepare("SELECT * FROM entregas WHERE tarea_id = ? AND estudiante_id = ? LIMIT 1");
    $se->execute([$tarea_id, $usuario_id]);
    $mi_entrega = $se->fetch(PDO::FETCH_ASSOC);
}

$entregas = [];
if ($rol === 'profesor' && intval($tarea['creador_id']) === $usuario_id || $rol === 'admin') {
    $q = $pdo->prepare("SELECT e.*, u.nombre_completo FROM entregas e JOIN usuarios u ON e.estudiante_id = u.id WHERE e.tarea_id = ? ORDER BY e.fecha_entregado DESC");
    $q->execute([$tarea_id]);
    $entregas = $q->fetchAll(PDO::FETCH_ASSOC);
}

$csrf = generate_csrf_token();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title><?=h($tarea['titulo'])?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container py-4">
    <a class="btn btn-secondary mb-3" href="cursoDetalles.php?id=<?=intval($curso_id)?>">← Volver al curso</a>
    <div class="card mb-3">
        <div class="card-body">
        <h4><?=h($tarea['titulo'])?></h4>
        <p class="small text-muted">Entrega: <?=h($tarea['fecha_entrega'])?> • Ponderación: <?=h($tarea['ponderacion'])?></p>
        <div><?=nl2br(h($tarea['descripcion']))?></div>
        </div>
    </div>

    <?php if ($rol === 'alumno'): ?>
        <?php if ($estado !== 'APROBADO'): ?>
        <div class="alert alert-warning">Tu inscripción no ha sido aprobada. Estado: <?=h($estado ?? 'Sin solicitar')?></div>
        <?php else: ?>
        <div class="card mb-3">
            <div class="card-body">
            <h5>Mi entrega</h5>
            <?php if ($mi_entrega): ?>
                <p>Archivo: <?= $mi_entrega['archivo'] ? '<a href="'.h($mi_entrega['archivo']).'" target="_blank">Ver entrega</a>' : '—' ?></p>
                <p>Fecha: <?=h($mi_entrega['fecha_entregado'])?></p>
                <p>Calificación: <?= $mi_entrega['calificacion'] !== null ? h($mi_entrega['calificacion']) : '<span class="text-muted">Sin calificar</span>' ?></p>
                <p>Comentario: <?=nl2br(h($mi_entrega['comentario']))?></p>
                <hr>
                <p class="text-muted small">Si quieres volver a subir, sube un nuevo archivo (reemplazará la entrega anterior).</p>
            <?php else: ?>
                <p class="text-muted">Aún no has entregado esta tarea.</p>
            <?php endif; ?>

            <form method="post" action="entregarTareas.php" enctype="multipart/form-data">
                <input type="hidden" name="tarea_id" value="<?=intval($tarea_id)?>">
                <input type="hidden" name="csrf_token" value="<?=h($csrf)?>">
                <div class="mb-3">
                <label class="form-label">Archivo (pdf, docx, zip, imagen)</label>
                <input type="file" name="archivo" class="form-control" required>
                </div>
                <div class="mb-3">
                <label class="form-label">Comentario (opcional)</label>
                <input name="comentario" class="form-control" value="">
                </div>
                <button class="btn btn-primary">Entregar tarea</button>
            </form>
            </div>
        </div>
        <?php endif; ?>
    <?php elseif ($rol === 'profesor' && intval($tarea['creador_id']) === $usuario_id || $rol === 'admin'): ?>
        <div class="card mb-3">
        <div class="card-body">
            <h5>Entregas</h5>
            <?php if (empty($entregas)): ?>
            <div class="alert alert-info">Aún no hay entregas.</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                <thead><tr><th>Alumno</th><th>Archivo</th><th>Fecha</th><th>Calificación</th><th>Comentario</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($entregas as $e): ?>
                    <tr>
                    <td><?=h($e['nombre_completo'])?></td>
                    <td><?= $e['archivo'] ? '<a href="'.h($e['archivo']).'" target="_blank">Ver</a>' : '—' ?></td>
                    <td><?=h($e['fecha_entregado'])?></td>
                    <td><?= $e['calificacion'] !== null ? h($e['calificacion']) : '—' ?></td>
                    <td><?=h($e['comentario'])?></td>
                    <td>
                        <a href="listaEntregas.php?tarea_id=<?=intval($tarea_id)?>" class="btn btn-sm btn-outline-primary">Abrir</a>
                    </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>