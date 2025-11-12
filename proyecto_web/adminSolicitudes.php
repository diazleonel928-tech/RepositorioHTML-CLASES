<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();
if (($_SESSION['rol_nombre'] ?? '') !== 'admin') { http_response_code(403); die('Acceso denegado'); }

$stmt = $pdo->query("SELECT s.*, u.nombre_completo, u.email FROM profesor_solicitudes s JOIN usuarios u ON s.usuario_id = u.id WHERE s.estado = 'PENDIENTE' ORDER BY s.fecha_solicitud ASC");
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
$csrf = generate_csrf_token();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Solicitudes Profesor</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container py-4">
    <a href="admin.php" class="btn btn-secondary mb-3">← Volver</a>
    <h3>Solicitudes para rol Profesor</h3>

    <?php if (empty($solicitudes)): ?>
        <div class="alert alert-info">No hay solicitudes pendientes.</div>
    <?php else: ?>
        <div class="list-group">
        <?php foreach ($solicitudes as $s): ?>
            <div class="list-group-item">
            <div class="d-flex justify-content-between">
                <div>
                <strong><?=h($s['nombre_completo'])?> (<?=h($s['email'])?>)</strong>
                <div class="small text-muted">Solicitado: <?=h($s['fecha_solicitud'])?> — Profesión: <?=h($s['profesion'])?></div>
                <?php if (!empty($s['mensaje'])): ?><div class="mt-2"><?=nl2br(h($s['mensaje']))?></div><?php endif; ?>
                <?php if (!empty($s['cv'])): ?><div class="mt-2"><a href="<?=h($s['cv'])?>" target="_blank">Ver CV</a></div><?php endif; ?>
                </div>
                <div class="text-end">
                <form method="post" action="admin_responder_profesor.php" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?=h($csrf)?>">
                    <input type="hidden" name="solicitud_id" value="<?=intval($s['id'])?>">
                    <input type="hidden" name="accion" value="aprobar">
                    <button class="btn btn-sm btn-success">Aprobar</button>
                </form>
                <form method="post" action="admin_responder_profesor.php" style="display:inline; margin-top:6px;">
                    <input type="hidden" name="csrf_token" value="<?=h($csrf)?>">
                    <input type="hidden" name="solicitud_id" value="<?=intval($s['id'])?>">
                    <input type="hidden" name="accion" value="rechazar">
                    <button class="btn btn-sm btn-danger">Rechazar</button>
                </form>
                </div>
            </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>