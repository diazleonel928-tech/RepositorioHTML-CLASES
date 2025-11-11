<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();

$usuario_id = intval($_SESSION['usuario_id']);
$rol = $_SESSION['rol_nombre'] ?? '';
$tarea_id = intval($_GET['tarea_id'] ?? 0);
$alumno_id = intval($_GET['alumno_id'] ?? 0);
if ($tarea_id <= 0) die('Tarea inválida.');
$stmt = $pdo->prepare("SELECT t.curso_id, c.creador_id FROM tareas t JOIN cursos c ON t.curso_id = c.id WHERE t.id = ?");
$stmt->execute([$tarea_id]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$info) die('Tarea no encontrada.');
if ($rol !== 'admin' && intval($info['creador_id']) !== $usuario_id) die('No tienes permiso.');

$q = "SELECT e.id, e.estudiante_id, u.nombre_completo, e.archivo, e.fecha_entregado, e.calificacion, e.comentario FROM entregas e JOIN usuarios u ON e.estudiante_id = u.id WHERE e.tarea_id = ?";
$params = [$tarea_id];
if ($alumno_id > 0) { $q .= " AND e.estudiante_id = ?"; $params[] = $alumno_id; }

$stmt = $pdo->prepare($q);
$stmt->execute($params);
$entregas = $stmt->fetchAll(PDO::FETCH_ASSOC);

function h($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="es">
    <head>
    <meta charset="utf-8">
    <title>Entregas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
    <body class="bg-light">
<div class="container mt-4">
    <a href="curso_detalle.php?id=<?=intval($info['curso_id'])?>" class="btn btn-secondary mb-3">← Volver</a>
    <h3>Entregas tarea <?=intval($tarea_id)?></h3>
    <div class="table-responsive">
        <table class="table table-sm table-hover">
        <thead><tr><th>Alumno</th><th>Archivo</th><th>Fecha</th><th>Calif</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($entregas as $r): ?>
            <tr>
            <td><?=h($r['nombre_completo'])?></td>
            <td><?= $r['archivo'] ? '<a href="'.h($r['archivo']).'" target="_blank">Ver</a>' : '—' ?></td>
            <td><?=h($r['fecha_entregado'])?></td>
            <td><?= $r['calificacion'] !== null ? h($r['calificacion']) : '—' ?></td>
            <td>
                <form method="post" action="calificar_entrega.php" class="d-inline">
                <input type="hidden" name="entrega_id" value="<?=intval($r['id'])?>">
                <input name="calificacion" type="number" step="0.1" min="0" max="100" class="form-control form-control-sm d-inline-block" style="width:90px" placeholder="0-100" value="<?=h($r['calificacion'])?>">
                <input name="comentario" class="form-control form-control-sm d-inline-block" style="width:200px" value="<?=h($r['comentario'])?>">
                <button class="btn btn-sm btn-primary mt-1">Guardar</button>
                </form>
            </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        </table>
    </div>
</div>
</body></html>