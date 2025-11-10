<?php
require_once __DIR__ . '/helper.php';
require_login();
require_once __DIR__ . '/config_database.php';

$usuario_id = intval($_SESSION['usuario_id']);
$rol = $_SESSION['rol_nombre'] ?? '';
$tarea_id = intval($_GET['tarea_id'] ?? 0);
$alumno_id = intval($_GET['alumno_id'] ?? 0);

if ($tarea_id <= 0) die('tarea inválida');
$st = $conn->prepare("SELECT curso_id FROM tareas WHERE id = ?");
$st->bind_param('i',$tarea_id); $st->execute(); $st->bind_result($curso_id); if (!$st->fetch()) { $st->close(); die('no'); } $st->close();

if ($rol !== 'admin' && !is_profesor_creador($conn, $usuario_id, $curso_id)) die('No tienes permiso.');

$q = "SELECT e.id, e.estudiante_id, u.nombre_completo, e.archivo, e.fecha_entregado, e.calificacion, e.comentario FROM entregas e JOIN usuarios u ON e.estudiante_id = u.id WHERE e.tarea_id = ?";
$params = ['i', $tarea_id];
if ($alumno_id>0) { $q .= " AND e.estudiante_id = ?"; $params = ['ii', $tarea_id, $alumno_id]; }

$stmt = $conn->prepare($q);
if ($alumno_id>0) $stmt->bind_param('ii', $tarea_id, $alumno_id); else $stmt->bind_param('i', $tarea_id);
$stmt->execute(); $res = $stmt->get_result();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Entregas</title></head><body>
<h3>Entregas tarea <?=intval($tarea_id)?></h3>
<table border="1"><tr><th>Alumno</th><th>Archivo</th><th>Fecha</th><th>Calif</th><th>Acciones</th></tr>
<?php while($r=$res->fetch_assoc()): ?>
<tr>
    <td><?=htmlspecialchars($r['nombre_completo'])?></td>
    <td><?php if ($r['archivo']): ?><a href="<?=htmlspecialchars('/CosasHTML-CSS/proyecto_web/' . $r['archivo'])?>" target="_blank">Ver</a><?php else: ?>—<?php endif; ?></td>
    <td><?=htmlspecialchars($r['fecha_entregado'])?></td>
    <td><?=htmlspecialchars($r['calificacion'])?></td>
    <td>
    <form method="post" action="calificarEntrega.php">
        <input type="hidden" name="entrega_id" value="<?=intval($r['id'])?>">
        <input name="calificacion" type="number" step="0.1" min="0" max="100" value="<?=htmlspecialchars($r['calificacion'])?>">
        <input name="comentario" value="<?=htmlspecialchars($r['comentario'])?>">
        <button type="submit">Guardar</button>
    </form>
    </td>
</tr>
<?php endwhile; ?>
</table>
</body></html>