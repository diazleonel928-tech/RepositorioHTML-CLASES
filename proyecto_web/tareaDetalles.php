<?php
require_once __DIR__ . 'auth_helper.php';
require_login();
require_once __DIR__ . 'config_database.php';

$usuario_id = intval($_SESSION['usuario_id']);
$rol = $_SESSION['rol_nombre'] ?? '';
$tarea_id = intval($_GET['id'] ?? 0);
if ($tarea_id<=0) die('Tarea inválida');

$q = $conn->prepare("SELECT t.id, t.titulo, t.descripcion, t.fecha_entrega, t.curso_id, c.nombre FROM tareas t JOIN cursos c ON t.curso_id=c.id WHERE t.id=?");
$q->bind_param('i',$tarea_id); $q->execute(); $q->bind_result($tid,$titulo,$desc,$fecha_entrega,$curso_id,$curso_nombre);
if (!$q->fetch()) { $q->close(); die('No encontrada'); } $q->close();

if ($rol === 'alumno') {
    if (!alumno_aprobado_en_curso($conn, $usuario_id, $curso_id)) die('No tienes acceso a esta tarea.');
}
if ($rol === 'profesor' && !is_profesor_creador($conn, $usuario_id, $curso_id)) die('No tienes permiso.');

$s = $conn->prepare("SELECT id, archivo, fecha_entregado, calificacion, comentario FROM entregas WHERE tarea_id = ? AND estudiante_id = ?");
$s->bind_param('ii', $tarea_id, $usuario_id); $s->execute(); $s->bind_result($ent_id,$ent_arch,$ent_fecha,$ent_calif,$ent_com);
$has_ent = $s->fetch(); $s->close();
?>
<!doctype html><html><head><meta charset="utf-8"><title><?=htmlspecialchars($titulo)?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light">
<div class="container mt-4">
    <h3><?=htmlspecialchars($titulo)?> — <?=htmlspecialchars($curso_nombre)?></h3>
    <p><?=nl2br(htmlspecialchars($desc))?></p>
    <p>Fecha entrega: <?=htmlspecialchars($fecha_entrega)?></p>

<?php if ($rol === 'alumno'): ?>
    <h5>Mi entrega</h5>
    <?php if ($has_ent): ?>
        <p>Archivo: <?=htmlspecialchars($ent_arch)?> (<?=htmlspecialchars($ent_fecha)?>)</p>
        <?php if (!is_null($ent_calif)) echo "<p>Calificación: ".htmlspecialchars($ent_calif)." — ".htmlspecialchars($ent_com)."</p>"; ?>
    <?php else: ?>
        <p>No has entregado aún.</p>
    <?php endif; ?>
    <form action="entregar_tarea.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="tarea_id" value="<?=intval($tarea_id)?>">
        <div class="mb-3"><label>Archivo</label><input type="file" name="archivo" class="form-control"></div>
        <div class="mb-3"><label>Texto (opcional)</label><textarea name="texto" class="form-control"></textarea></div>
        <button class="btn btn-primary">Enviar entrega</button>
    </form>
    <?php else: ?>
        <a href="listar_entregas.php?tarea_id=<?=intval($tarea_id)?>" class="btn btn-outline-primary">Ver entregas</a>
    <?php endif; ?>
</div>
</body>
</html>