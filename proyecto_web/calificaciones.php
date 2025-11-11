<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();
if (($_SESSION['rol_nombre'] ?? '') !== 'alumno') { http_response_code(403); die('Acceso denegado'); }

$alumno_id = intval($_SESSION['usuario_id']);
function h($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$stmt = $pdo->prepare("SELECT c.id AS curso_id, c.nombre AS curso_nombre, c.codigo AS curso_codigo FROM inscripciones i JOIN cursos c ON i.curso_id = c.id WHERE i.estudiante_id = ? AND i.estado = 'APROBADO'");
$stmt->execute([$alumno_id]);
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_num = 0.0; $total_den = 0.0;
$results = [];

foreach ($cursos as $curso) {
    $curso_id = intval($curso['curso_id']);
    $q = "
        SELECT t.id AS tarea_id, t.titulo, COALESCE(t.ponderacion, 0) AS ponderacion,
            e.calificacion AS nota_entrega, c2.calificacion AS nota_calificacion
        FROM tareas t
        LEFT JOIN entregas e ON e.tarea_id = t.id AND e.estudiante_id = ?
        LEFT JOIN calificaciones c2 ON c2.tareas_id = t.id AND c2.estudiantes_id = ?
        WHERE t.curso_id = ?
        ORDER BY t.fecha_entrega ASC
    ";
    $st = $pdo->prepare($q);
    $st->execute([$alumno_id, $alumno_id, $curso_id]);
    $tareas = $st->fetchAll(PDO::FETCH_ASSOC);

    $num = 0.0; $den = 0.0;
    foreach ($tareas as $t) {
        $ponder = floatval($t['ponderacion']);
        $nota = null;
        if ($t['nota_entrega'] !== null && $t['nota_entrega'] !== '') $nota = floatval($t['nota_entrega']);
        elseif ($t['nota_calificacion'] !== null && $t['nota_calificacion'] !== '') $nota = floatval($t['nota_calificacion']);
        if (!is_null($nota)) { $num += $nota * $ponder; $den += $ponder; }
    }
    $promedio = null;
    if ($den > 0) {
        $promedio = $num / $den;
        $total_num += $num; $total_den += $den;
    }
    $results[] = ['curso_id'=>$curso_id,'curso_nombre'=>$curso['curso_nombre'],'curso_codigo'=>$curso['curso_codigo'],'tareas'=>$tareas,'promedio'=>$promedio];
}

$promedio_general = null;
if ($total_den > 0) $promedio_general = $total_num / $total_den;
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Mis calificaciones</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container mt-4">
    <a href="home.php" class="btn btn-secondary mb-3">← Volver</a>
    <h3>Mis calificaciones</h3>

    <?php if (empty($results)): ?>
        <div class="alert alert-info">No estás aprobado en ningún curso o no hay calificaciones aún.</div>
    <?php else: ?>
        <?php foreach ($results as $resCurso): ?>
        <div class="card mb-3">
            <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div><h5 class="mb-0"><?=h($resCurso['curso_nombre'])?> <small class="text-muted"><?=h($resCurso['curso_codigo'])?></small></h5></div>
                <div>
                <?php if (is_null($resCurso['promedio'])): ?><small class="text-muted">Aún no hay calificaciones</small>
                <?php else: ?><small class="text-muted">Promedio: <strong><?=number_format($resCurso['promedio'],2)?></strong></small><?php endif; ?>
                </div>
            </div>
            <table class="table table-sm">
                <thead><tr><th>Tarea</th><th>Ponderación</th><th>Calificación</th></tr></thead>
                <tbody>
                <?php foreach ($resCurso['tareas'] as $t): 
                    $ponder = floatval($t['ponderacion']); $nota = null;
                    if ($t['nota_entrega'] !== null && $t['nota_entrega'] !== '') $nota = $t['nota_entrega'];
                    elseif ($t['nota_calificacion'] !== null && $t['nota_calificacion'] !== '') $nota = $t['nota_calificacion'];
                ?>
                <tr>
                    <td><?=h($t['titulo'])?></td>
                    <td><?= ($ponder>0?number_format($ponder,2):'0') ?></td>
                    <td><?= is_null($nota) ? '<span class="text-muted">Sin calificar</span>' : h(number_format(floatval($nota),2)) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="card">
        <div class="card-body">
            <h5>Promedio general</h5>
            <?php if (is_null($promedio_general)): ?><p class="text-muted">Sin datos suficientes.</p>
            <?php else: ?><p class="lead mb-0"><?=number_format($promedio_general,2)?></p><?php endif; ?>
        </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>