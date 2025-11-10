<?php
require_once __DIR__ . '/helper.php';
require_login();
require_once __DIR__ . '/config_database.php';

$alumno_id = intval($_SESSION['usuario_id']);
function h($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$sql = "SELECT c.id AS curso_id, c.nombre AS curso_nombre, c.codigo AS curso_codigo
        FROM inscripciones i
        JOIN cursos c ON i.curso_id = c.id
        WHERE i.estudiante_id = ? AND (i.estado = 'APROBADO' OR i.status = 'APROBADO')";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $alumno_id);
$stmt->execute();
$res = $stmt->get_result();
$cursos = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$total_num = 0.0;
$total_denom = 0.0;

$results = [];

foreach ($cursos as $curso) {
    $curso_id = intval($curso['curso_id']);
    $q = "
        SELECT
        t.id AS tarea_id,
        COALESCE(t.titulo, '') AS titulo,
        COALESCE(t.descripcion, '') AS descripcion,
        COALESCE(t.ponderacion, t.weight, 0) AS ponderacion,
        e.calificacion AS nota_entrega,
        c.calificacion AS nota_calificacion
        FROM tareas t
        LEFT JOIN entregas e ON e.tarea_id = t.id AND e.estudiante_id = ?
        LEFT JOIN calificaciones c ON c.tareas_id = t.id AND c.estudiantes_id = ?
        WHERE t.curso_id = ?
        ORDER BY t.fecha_entrega ASC, t.id ASC
    ";
    $st = $conn->prepare($q);
    $st->bind_param('iii', $alumno_id, $alumno_id, $curso_id);
    $st->execute();
    $r = $st->get_result();
    $tareas = $r->fetch_all(MYSQLI_ASSOC);
    $st->close();
    $num = 0.0; $den = 0.0;
    foreach ($tareas as $t) {
        $ponder = floatval($t['ponderacion']);
        $nota = null;
        if (!is_null($t['nota_entrega']) && $t['nota_entrega'] !== '') $nota = floatval($t['nota_entrega']);
        elseif (!is_null($t['nota_calificacion']) && $t['nota_calificacion'] !== '') $nota = floatval($t['nota_calificacion']);
        if (!is_null($nota)) {
            $num += $nota * $ponder;
            $den += $ponder;
        }
    }
    $promedio = null;
    if ($den > 0) {
        $promedio = $num / $den;
        $total_num += $num;
        $total_denom += $den;
    }
    $results[] = [
        'curso_id' => $curso_id,
        'curso_nombre' => $curso['curso_nombre'],
        'curso_codigo' => $curso['curso_codigo'] ?? '',
        'tareas' => $tareas,
        'promedio' => $promedio,
        'num' => $num,
        'den' => $den
    ];
}
$promedio_general = null;
if ($total_denom > 0) $promedio_general = $total_num / $total_denom;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Mis calificaciones</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Mis calificaciones</h3>
    <a class="btn btn-secondary" href="home.php">Volver</a>
    </div>
    <?php if (empty($results)): ?>
    <div class="alert alert-info">No estás aprobado en ningún curso o no hay cursos con calificaciones aún.</div>
    <?php else: ?>
    <?php foreach ($results as $resCurso): ?>
    <div class="card mb-3">
        <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
            <h5 class="mb-0"><?= h($resCurso['curso_nombre']) ?> <small class="text-muted"><?= h($resCurso['curso_codigo']) ?></small></h5>
            <?php if (is_null($resCurso['promedio'])): ?>
                <small class="text-muted">Aún no hay calificaciones en este curso.</small>
            <?php else: ?>
                <small class="text-muted">Promedio del curso (ponderado): <strong><?= number_format($resCurso['promedio'],2) ?></strong></small>
            <?php endif; ?>
            </div>
            <div>
            <a class="btn btn-sm btn-outline-primary" href="curso_detalle.php?id=<?= intval($resCurso['curso_id']) ?>">Ver curso</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                <tr>
                    <th>Tarea</th>
                    <th>Fecha entrega</th>
                    <th>Ponderación</th>
                    <th>Calificación</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($resCurso['tareas'])): ?>
                <tr><td colspan="4" class="text-muted">No hay tareas registradas.</td></tr>
                <?php else: ?>
                    <?php foreach ($resCurso['tareas'] as $t): 
                    $ponder = floatval($t['ponderacion']);
                    $nota = null;
                    if (!is_null($t['nota_entrega']) && $t['nota_entrega'] !== '') $nota = floatval($t['nota_entrega']);
                    elseif (!is_null($t['nota_calificacion']) && $t['nota_calificacion'] !== '') $nota = floatval($t['nota_calificacion']);
                    ?>
                    <tr>
                        <td><?= h($t['titulo'] ?: '—') ?></td>
                        <td><?= h($t['fecha_entrega'] ?? '') ?></td>
                        <td><?= ($ponder>0 ? number_format($ponder,2) : '0') ?></td>
                        <td>
                        <?php if (is_null($nota)): ?>
                            <span class="text-muted">Sin calificar</span>
                        <?php else: ?>
                            <?= number_format($nota,2) ?>
                        <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        </div>
    </div>
    <?php endforeach; ?>

    <div class="card">
        <div class="card-body">
        <h5>Promedio general</h5>
        <?php if (is_null($promedio_general)): ?>
            <p class="text-muted">Aún no hay calificaciones para calcular un promedio general.</p>
        <?php else: ?>
            <p class="lead mb-0"><?= number_format($promedio_general,2) ?></p>
        <?php endif; ?>
        </div>
    </div>

    <?php endif; ?>
</div>
</body>
</html>