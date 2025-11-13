<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();

if (!function_exists('h_local')) {
    function h_local($v) {
        return h($v);
    }
}

$rol = $_SESSION['rol_nombre'] ?? '';
if ($rol == 'profesor' && $rol == 'admin') {
    http_response_code(403);
    die('Acceso denegado');
}

$usuario_id = intval($_SESSION['usuario_id']);
$rol = $_SESSION['rol_nombre'] ?? '';

$msg = null;
$err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $err = 'Token CSRF inválido.';
    } else {
        $accion = $_POST['accion'] ?? '';
        $insc_id = intval($_POST['inscripcion_id'] ?? 0);
        if ($insc_id <= 0) {
            $err = 'Solicitud inválida.';
        } else {
            try {
                $s = $pdo->prepare("
                    SELECT i.*, u.nombre_completo AS estudiante_nombre, u.correo AS estudiante_correo, c.nombre AS curso_nombre, c.creador_id
                    FROM inscripciones i
                    JOIN usuarios u ON i.estudiante_id = u.id
                    JOIN cursos c ON i.curso_id = c.id
                    WHERE i.id = ?
                    LIMIT 1
                ");
                $s->execute([$insc_id]);
                $ins = $s->fetch(PDO::FETCH_ASSOC);
                if (!$ins) throw new Exception('Solicitud no encontrada.');

                if ($rol === 'profesor' && intval($ins['creador_id']) !== $usuario_id) {
                    throw new Exception('No tienes permiso para gestionar esta solicitud.');
                }

                $pdo->beginTransaction();

                if ($accion === 'aprobar') {
                    $upd = $pdo->prepare("UPDATE inscripciones SET estado = 'APROBADO', fecha_inscripcion = NOW(), aceptado_por = ?, razon = NULL WHERE id = ?");
                    $upd->execute([$usuario_id, $insc_id]);
                    $msg = 'Solicitud aprobada.';
                } elseif ($accion === 'rechazar') {
                    $razon = trim($_POST['razon'] ?? '');
                    $upd = $pdo->prepare("UPDATE inscripciones SET estado = 'RECHAZADO', fecha_inscripcion = NULL, aceptado_por = ?, razon = ? WHERE id = ?");
                    $upd->execute([$usuario_id, $razon ?: null, $insc_id]);
                    $msg = 'Solicitud rechazada.';
                } else {
                    throw new Exception('Acción desconocida.');
                }

                $pdo->commit();
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $err = 'Error al procesar la solicitud: ' . $e->getMessage();
            }
        }
    }
}

try {
    if ($rol === 'profesor') {
        $q = $pdo->prepare("
            SELECT i.id, i.curso_id, i.estudiante_id, i.estado, i.fecha_postulacion, i.fecha_inscripcion, i.aceptado_por, i.razon,
                u.nombre_completo AS estudiante_nombre, u.correo AS estudiante_correo,
                c.nombre AS curso_nombre, c.creador_id
            FROM inscripciones i
            JOIN usuarios u ON i.estudiante_id = u.id
            JOIN cursos c ON i.curso_id = c.id
            WHERE i.estado = 'PENDIENTE' AND c.creador_id = ?
            ORDER BY i.fecha_postulacion ASC
        ");
        $q->execute([$usuario_id]);
    } else {
        $q = $pdo->prepare("
            SELECT i.id, i.curso_id, i.estudiante_id, i.estado, i.fecha_postulacion, i.fecha_inscripcion, i.aceptado_por, i.razon,
                u.nombre_completo AS estudiante_nombre, u.correo AS estudiante_correo,
                c.nombre AS curso_nombre, c.creador_id
            FROM inscripciones i
            JOIN usuarios u ON i.estudiante_id = u.id
            JOIN cursos c ON i.curso_id = c.id
            WHERE i.estado = 'PENDIENTE'
            ORDER BY i.fecha_postulacion ASC
        ");
        $q->execute();
    }
    $solicitudes = $q->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $err = 'Error al cargar solicitudes: ' . $e->getMessage();
    $solicitudes = [];
}

$csrf = generate_csrf_token();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Solicitudes de Inscripción — Profesor</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
        <h3 class="mb-0">Solicitudes de Inscripción</h3>
        <small class="text-muted">Revise y administre las solicitudes a sus cursos.</small>
        </div>
        <div>
        <a class="btn btn-outline-secondary" href="profesor.php">Volver</a>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?=h_local($msg)?></div>
    <?php endif; ?>
    <?php if ($err): ?>
        <div class="alert alert-danger"><?=h_local($err)?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
        <?php if (empty($solicitudes)): ?>
            <div class="alert alert-info">No hay solicitudes pendientes.</div>
        <?php else: ?>
            <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Alumno</th>
                    <th>Correo</th>
                    <th>Curso</th>
                    <th>Fecha de solicitud</th>
                    <th class="text-end">Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($solicitudes as $s): ?>
                    <tr>
                    <td><?=h_local($s['id'])?></td>
                    <td><?=h_local($s['estudiante_nombre'])?></td>
                    <td><?=h_local($s['estudiante_correo'])?></td>
                    <td><?=h_local($s['curso_nombre'])?></td>
                    <td><?=h_local($s['fecha_postulacion'])?></td>
                    <td class="text-end">
                        <form method="post" style="display:inline-block" class="me-1" onsubmit="return confirm('¿Aprobar esta solicitud?');">
                        <input type="hidden" name="csrf_token" value="<?=h_local($csrf)?>">
                        <input type="hidden" name="inscripcion_id" value="<?=intval($s['id'])?>">
                        <input type="hidden" name="accion" value="aprobar">
                        <button type="submit" class="btn btn-sm btn-success">Aprobar</button>
                        </form>

                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rechazarModal<?=$s['id']?>">Rechazar</button>

                        <div class="modal fade" id="rechazarModal<?=$s['id']?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                            <form method="post" onsubmit="return confirm('¿Rechazar esta solicitud?');">
                                <div class="modal-header">
                                <h5 class="modal-title">Rechazar solicitud — <?=h_local($s['estudiante_nombre'])?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                <input type="hidden" name="csrf_token" value="<?=h_local($csrf)?>">
                                <input type="hidden" name="inscripcion_id" value="<?=intval($s['id'])?>">
                                <input type="hidden" name="accion" value="rechazar">
                                <div class="mb-3">
                                    <label class="form-label">Motivo (opcional)</label>
                                    <textarea name="razon" class="form-control" rows="3" placeholder="Explica por qué rechazas la solicitud (opcional)"></textarea>
                                </div>
                                </div>
                                <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-danger">Rechazar solicitud</button>
                                </div>
                            </form>
                            </div>
                        </div>
                        </div>
                    </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
        </div>
    </div>
    </div>
</body>
</html>