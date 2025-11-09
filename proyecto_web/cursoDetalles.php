<?php
require_once __DIR__ . 'helper.php';
require_login();
require_once __DIR__ . 'config_database.php';

$usuario_id = intval($_SESSION['usuario_id']);
$rol = $_SESSION['rol_nombre'] ?? 'alumno';
$curso_id = intval($_GET['id'] ?? 0);
if ($curso_id<=0) header('Location: cursos.php');

$st = $conn->prepare("SELECT id, codigo, nombre, descripcion, creador_id FROM cursos WHERE id = ?");
$st->bind_param('i', $curso_id); $st->execute(); $st->bind_result($id,$codigo,$nombre,$descripcion,$creador_id);
if (!$st->fetch()) { $st->close(); die('Curso no encontrado'); } $st->close();

$prof_name='';
$rp = $conn->prepare("SELECT nombre_completo FROM usuarios WHERE id = ?");
$rp->bind_param('i', $creador_id); $rp->execute(); $rp->bind_result($prof_name); $rp->fetch(); $rp->close();

$estado = null;
if ($rol === 'alumno') {
    $s = $conn->prepare("SELECT estado FROM inscripciones WHERE curso_id = ? AND estudiante_id = ?");
    $s->bind_param('ii', $curso_id, $usuario_id); $s->execute(); $s->bind_result($estado_db);
    if ($s->fetch()) $estado = $estado_db; $s->close();
}

$tareas = [];
$qt = $conn->prepare("SELECT id, titulo, descripcion, fecha_entrega, ponderacion FROM tareas WHERE curso_id = ? ORDER BY fecha_entrega ASC");
$qt->bind_param('i', $curso_id); $qt->execute(); $res=$qt->get_result();
while ($r = $res->fetch_assoc()) $tareas[] = $r;
$qt->close();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title><?=htmlspecialchars($nombre)?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light">
<div class="container mt-4">
    <h3><?=htmlspecialchars($nombre)?></h3>
    <p>Profesor: <?=htmlspecialchars($prof_name)?></p>
    <p><?=nl2br(htmlspecialchars($descripcion))?></p>

<?php if ($rol==='alumno'): ?>
    <h5>Estado: <?=htmlspecialchars($estado ?? 'Sin solicitar')?></h5>
    <?php if ($estado === 'RECHAZADO'): ?><div class="alert alert-danger">Inscripción rechazada</div><?php endif; ?>
    <?php if ($estado !== 'APROBADO'): ?><div class="alert alert-warning">No puedes acceder a tareas hasta estar aprobado.</div><?php endif; ?>
<?php endif; ?>

    <h4>Tareas</h4>
    <?php if (empty($tareas)) echo "<p>No hay tareas</p>"; else ?>
    <ul class="list-group">
        <?php foreach ($tareas as $t): ?>
        <li class="list-group-item d-flex justify-content-between">
            <div>
            <strong><?=htmlspecialchars($t['titulo'])?></strong><br><small class="text-muted"><?=htmlspecialchars($t['fecha_entrega'])?></small>
            </div>
            <div>
            <?php if ($rol==='alumno' && $estado === 'APROBADO'): ?>
                <a class="btn btn-sm btn-primary" href="tareaDetalles.php?id=<?=intval($t['id'])?>">Ver / Entregar</a>
            <?php else: ?>
                <a class="btn btn-sm btn-secondary" href="tareaDetalles.php?id=<?=intval($t['id'])?>">Ver</a>
            <?php endif; ?>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
<?php end; ?>
</div>
</body>
</html>