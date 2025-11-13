<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();

$usuario_id = intval($_SESSION['usuario_id']);
$rol = $_SESSION['rol_nombre'] ?? '';
$curso_id = intval($_GET['id'] ?? 0);
if ($curso_id <= 0) header('Location: cursos.php');

$msg = null;
$err = null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            $err = 'Token CSRF inválido.';
        } else {
            $action = $_POST['action'];
            if ($action === 'solicitar_inscripcion') {
                if ($rol !== 'alumno') {
                    $err = 'Solo los alumnos pueden solicitar inscripción.';
                } else {
                    $q = $pdo->prepare("SELECT id, estado FROM inscripciones WHERE curso_id = ? AND estudiante_id = ? LIMIT 1");
                    $q->execute([$curso_id, $usuario_id]);
                    $ex = $q->fetch(PDO::FETCH_ASSOC);
                    if ($ex) {
                        $err = 'Ya existe una solicitud o inscripción (estado: ' . ($ex['estado'] ?? 'desconocido') . ').';
                    } else {
                        $ins = $pdo->prepare("INSERT INTO inscripciones (curso_id, estudiante_id, estado, fecha_postulacion) VALUES (?, ?, 'PENDIENTE', NOW())");
                        $ins->execute([$curso_id, $usuario_id]);
                        header('Location: cursoDetalles.php?id=' . $curso_id . '&msg=' . urlencode('Solicitud enviada'));
                        exit;
                    }
                }
            } elseif ($action === 'cancelar_solicitud') {
                if ($rol !== 'alumno') {
                    $err = 'Solo el alumno puede cancelar la solicitud.';
                } else {
                    $q = $pdo->prepare("SELECT id, estado FROM inscripciones WHERE curso_id = ? AND estudiante_id = ? LIMIT 1");
                    $q->execute([$curso_id, $usuario_id]);
                    $ex = $q->fetch(PDO::FETCH_ASSOC);
                    if (!$ex) {
                        $err = 'No existe solicitud para cancelar.';
                    } elseif ($ex['estado'] !== 'PENDIENTE') {
                        $err = 'Solo puedes cancelar solicitudes que estén pendientes.';
                    } else {
                        $del = $pdo->prepare("DELETE FROM inscripciones WHERE id = ?");
                        $del->execute([intval($ex['id'])]);
                        header('Location: cursoDetalles.php?id=' . $curso_id . '&msg=' . urlencode('Solicitud cancelada'));
                        exit;
                    }
                }
            } elseif ($action === 'retirarse') {
                if ($rol !== 'alumno') {
                    $err = 'Solo el alumno puede retirarse.';
                } else {
                    $q = $pdo->prepare("SELECT id, estado FROM inscripciones WHERE curso_id = ? AND estudiante_id = ? LIMIT 1");
                    $q->execute([$curso_id, $usuario_id]);
                    $ex = $q->fetch(PDO::FETCH_ASSOC);
                    if (!$ex) {
                        $err = 'No estás inscrito en este curso.';
                    } elseif ($ex['estado'] !== 'APROBADO') {
                        $err = 'Solo puedes retirarte si tu inscripción está aprobada.';
                    } else {
                        $del = $pdo->prepare("DELETE FROM inscripciones WHERE id = ?");
                        $del->execute([intval($ex['id'])]);
                        header('Location: cursoDetalles.php?id=' . $curso_id . '&msg=' . urlencode('Te has retirado del curso'));
                        exit;
                    }
                }
            } else {
                $err = 'Acción desconocida.';
            }
        }
    }

    if (!empty($_GET['msg'])) $msg = $_GET['msg'];

    $stmt = $pdo->prepare("SELECT c.id, c.codigo, c.nombre, c.descripcion, c.creador_id, u.nombre_completo AS creador_nombre FROM cursos c LEFT JOIN usuarios u ON c.creador_id = u.id WHERE c.id = ?");
    $stmt->execute([$curso_id]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$curso) die('Curso no encontrado.');

    $estado = null;
    if ($rol === 'alumno') {
        $s = $pdo->prepare("SELECT estado FROM inscripciones WHERE curso_id = ? AND estudiante_id = ?");
        $s->execute([$curso_id, $usuario_id]);
        $r = $s->fetch(PDO::FETCH_ASSOC);
        $estado = $r['estado'] ?? null;
    }

    $t = $pdo->prepare("SELECT id, titulo, descripcion, fecha_entrega, ponderacion FROM tareas WHERE curso_id = ? ORDER BY fecha_entrega ASC");
    $t->execute([$curso_id]);
    $tareas = $t->fetchAll(PDO::FETCH_ASSOC);

    $alumnos_inscritos = [];
    $puede_ver_inscritos = false;
    if ($rol === 'admin' || ($rol === 'profesor' && intval($curso['creador_id']) === $usuario_id)) {
        $puede_ver_inscritos = true;
        $q = $pdo->prepare("
            SELECT u.id, u.nombre_completo, u.correo, i.fecha_inscripcion
            FROM inscripciones i
            JOIN usuarios u ON i.estudiante_id = u.id
            WHERE i.curso_id = ? AND i.estado = 'APROBADO'
            ORDER BY u.nombre_completo ASC
        ");
        $q->execute([$curso_id]);
        $alumnos_inscritos = $q->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$csrf = generate_csrf_token();
function h_local($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <title><?=h_local($curso['nombre'])?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
<body class="bg-light">
<div class="container mt-4">
    <a class="btn btn-secondary mb-3" href="cursos.php">Volver</a>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?=h_local($msg)?></div>
    <?php endif; ?>
    <?php if ($err): ?>
        <div class="alert alert-danger"><?=h_local($err)?></div>
    <?php endif; ?>

    <h3><?=h_local($curso['nombre'])?></h3>
    <p class="small text-muted">Profesor: <?=h_local($curso['creador_nombre'] ?? '')?></p>
    <p><?=nl2br(h_local($curso['descripcion']))?></p>

    <?php if ($rol === 'alumno'): ?>
        <?php if ($estado === 'APROBADO'): ?>
            <div class="alert alert-success">Tu inscripción fue aprobada. Ya puedes acceder a las tareas.</div>
            <form method="post" class="mb-3" onsubmit="return confirm('¿Estás seguro que deseas retirarte del curso?');">
                <input type="hidden" name="csrf_token" value="<?=h_local($csrf)?>">
                <input type="hidden" name="action" value="retirarse">
                <button type="submit" class="btn btn-warning">Retirarme del curso</button>
            </form>
        <?php elseif ($estado === 'PENDIENTE'): ?>
            <div class="alert alert-info">Tu solicitud de inscripción está pendiente de revisión.</div>
            <form method="post" class="mb-3" onsubmit="return confirm('¿Cancelar solicitud?');">
                <input type="hidden" name="csrf_token" value="<?=h_local($csrf)?>">
                <input type="hidden" name="action" value="cancelar_solicitud">
                <button type="submit" class="btn btn-outline-danger">Cancelar solicitud</button>
            </form>
        <?php elseif ($estado === 'RECHAZADO'): ?>
            <div class="alert alert-warning">Tu solicitud fue rechazada. Puedes volver a solicitar si lo deseas.</div>
            <form method="post" class="mb-3">
                <input type="hidden" name="csrf_token" value="<?=h_local($csrf)?>">
                <input type="hidden" name="action" value="solicitar_inscripcion">
                <button type="submit" class="btn btn-primary">Volver a solicitar inscripción</button>
            </form>
        
        <?php else: ?>
            <form method="post" class="mb-3">
                <input type="hidden" name="csrf_token" value="<?=h_local($csrf)?>">
                <input type="hidden" name="action" value="solicitar_inscripcion">
                <button type="submit" class="btn btn-primary">Solicitar inscripción a este curso</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($rol === 'alumno' && $estado !== 'APROBADO'): ?>
        <div class="alert alert-warning">No puedes acceder a tareas hasta que tu inscripción sea aprobada. Estado: <?=h_local($estado ?? 'Sin solicitar')?></div>
    <?php endif; ?>

    <?php if ($puede_ver_inscritos): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Alumnos inscritos (<?=count($alumnos_inscritos)?>)</h5>
            <?php if (empty($alumnos_inscritos)): ?>
                <p class="text-muted">No hay alumnos inscritos aún.</p>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($alumnos_inscritos as $a): ?>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?=h_local($a['nombre_completo'])?></strong><br>
                                <small class="text-muted"><?=h_local($a['correo'])?></small>
                            </div>
                            <div class="small text-muted">Inscrito: <?=h_local($a['fecha_inscripcion'])?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <h5 class="mt-4">Tareas</h5>
    <?php if (empty($tareas)): ?>
        <p class="text-muted">No hay tareas registradas.</p>
        <?php if (($rol === 'profesor' && intval($curso['creador_id']) === $usuario_id) || $rol === 'admin'): ?>
            <a class="btn btn-secondary mb-3" href="crearTarea.php?curso_id=<?=intval($curso['id'])?>">Agregar Tarea</a>
        <?php endif; ?>
    <?php else: ?>
        <div class="list-group">
        <?php foreach ($tareas as $ta): ?>
            <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <strong><?=h_local($ta['titulo'])?></strong><br><small class="text-muted"><?=h_local($ta['fecha_entrega'])?></small>
                <div class="small"><?=h_local(mb_strimwidth($ta['descripcion'] ?? '', 0, 150, '...'))?></div>
            </div>
            <div>
                <?php if ($rol === 'alumno' && $estado === 'APROBADO'): ?>
                <a class="btn btn-sm btn-primary" href="tareaDetalles.php?id=<?=intval($ta['id'])?>">Ver / Entregar</a>
                <?php else: ?>
                <a class="btn btn-sm btn-outline-secondary" href="tareaDetalles.php?id=<?=intval($ta['id'])?>">Ver</a>
                <?php endif; ?>
                <?php if ($rol === 'profesor' && intval($curso['creador_id']) === $usuario_id): ?>
                <a class="btn btn-sm btn-warning" href="editarTarea.php?id=<?=intval($ta['id'])?>">Editar</a>
                <?php endif; ?>
            </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>