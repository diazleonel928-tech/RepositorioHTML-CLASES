<?php
require_once __DIR__ . 'config_database.php';
require_once __DIR__ . 'helper.php';
require_login();
$usuario_id = intval($_SESSION['usuario_id']);
$rol = $_SESSION['rol_nombre'] ?? 'alumno';

function h($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$res = $conn->query("SELECT c.id, c.codigo, c.nombre, c.descripcion, c.creador_id, u.nombre_completo AS creador_nombre FROM cursos c LEFT JOIN usuarios u ON c.creador_id = u.id ORDER BY c.fecha_creacion DESC");
$cursos = $res->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Cursos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light">
<div class="container mt-4">
<h4>Cursos disponibles</h4>
<div class="list-group">
    <?php foreach ($cursos as $c): 
    $estado = null;
    if ($rol === 'alumno') {
        $st = $conn->prepare("SELECT estado FROM inscripciones WHERE estudiante_id = ? AND curso_id = ?");
        $st->bind_param('ii', $usuario_id, $c['id']);
        $st->execute();
        $st->bind_result($estado_db);
        if ($st->fetch()) $estado = $estado_db;
        $st->close();
    }
    ?>
    <div class="list-group-item d-flex justify-content-between align-items-center">
        <div>
        <strong><?=h($c['nombre'])?></strong>
        <div class="small text-muted">Profesor: <?=h($c['creador_nombre'] ?? '—')?></div>
        </div>
        <div class="text-end">
        <?php if ($rol === 'alumno'): ?>
            <div><span class="badge bg-<?= $estado==='APROBADO' ? 'success' : ($estado==='PENDIENTE' ? 'warning' : 'secondary') ?>"><?=h($estado ?? 'Sin solicitar')?></span></div>
            <?php if (empty($estado)): ?>
            <button class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalCurso<?=intval($c['id'])?>">Solicitar (código)</button>
            <div class="modal fade" id="modalCurso<?=intval($c['id'])?>" tabindex="-1">
                <div class="modal-dialog"><div class="modal-content">
                <form method="post" action="solicitar_inscripcion.php">
                    <input type="hidden" name="curso_id" value="<?=intval($c['id'])?>">
                    <div class="modal-header"><h5 class="modal-title">Solicitar inscripción a <?=h($c['nombre'])?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label>Código de acceso (si aplica)</label><input name="codigo_acceso" class="form-control"></div>
                    <small class="text-muted">Si el curso no tiene código deja vacío.</small>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Enviar</button></div>
                </form>
                </div></div>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <a href="curso_detalle.php?id=<?=intval($c['id'])?>" class="btn btn-sm btn-outline-primary">Ver</a>
        <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>