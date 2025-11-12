<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();
if (!es_admin()) { http_response_code(403); die('Acceso denegado'); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php'); exit;
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    header('Location: admin.php?msg=csrf'); exit;
}

$del_id = intval($_POST['user_id'] ?? 0);
if ($del_id <= 0) { header('Location: admin.php?msg=invalid'); exit; }

$current = intval($_SESSION['usuario_id']);
if ($del_id === $current) {
    header('Location: admin.php?msg=cannot_delete_self'); exit;
}

try {
    $s = $pdo->prepare("SELECT r.nombre AS rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.id = ?");
    $s->execute([$del_id]);
    $row = $s->fetch(PDO::FETCH_ASSOC);
    $rol_nombre = $row['rol_nombre'] ?? '';

    if ($rol_nombre === 'admin') {
        $c = $pdo->query("SELECT COUNT(*) AS c FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE r.nombre = 'admin'");
        $numAdmins = intval($c->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
        if ($numAdmins <= 1) {
            header('Location: admin.php?msg=cannot_delete_last_admin'); exit;
        }
    }

    $pdo->beginTransaction();

    $q = $pdo->prepare("SELECT archivo FROM entregas WHERE estudiante_id = ?");
    $q->execute([$del_id]);
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $f) {
        if (!empty($f['archivo'])) @unlink_public_file($f['archivo']);
    }

    $q2 = $pdo->prepare("SELECT cv FROM profesor_solicitudes WHERE usuario_id = ?");
    $q2->execute([$del_id]);
    foreach ($q2->fetchAll(PDO::FETCH_ASSOC) as $f) {
        if (!empty($f['cv'])) @unlink_public_file($f['cv']);
    }

    $pdo->prepare("DELETE FROM entregas WHERE estudiante_id = ?")->execute([$del_id]);
    $pdo->prepare("DELETE FROM inscripciones WHERE estudiante_id = ?")->execute([$del_id]);
    $pdo->prepare("DELETE FROM calificaciones WHERE estudiantes_id = ?")->execute([$del_id]);
    $pdo->prepare("DELETE FROM profesor_solicitudes WHERE usuario_id = ?")->execute([$del_id]);

    $q3 = $pdo->prepare("SELECT id FROM cursos WHERE creador_id = ?");
    $q3->execute([$del_id]);
    $cursos = $q3->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cursos as $c) {
        $cid = intval($c['id']);
        $qf = $pdo->prepare("SELECT e.archivo FROM entregas e JOIN tareas t ON e.tarea_id = t.id WHERE t.curso_id = ?");
        $qf->execute([$cid]);
        foreach ($qf->fetchAll(PDO::FETCH_ASSOC) as $ff) {
            if (!empty($ff['archivo'])) @unlink_public_file($ff['archivo']);
        }
        $pdo->prepare("DELETE e FROM entregas e JOIN tareas t ON e.tarea_id = t.id WHERE t.curso_id = ?")->execute([$cid]);
        $pdo->prepare("DELETE FROM calificaciones WHERE tareas_id IN (SELECT id FROM tareas WHERE curso_id = ?)")->execute([$cid]);
        $pdo->prepare("DELETE FROM tareas WHERE curso_id = ?")->execute([$cid]);
        $pdo->prepare("DELETE FROM inscripciones WHERE curso_id = ?")->execute([$cid]);
        $pdo->prepare("DELETE FROM cursos WHERE id = ?")->execute([$cid]);
    }

    $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$del_id]);

    $pdo->commit();
    header('Location: admin.php?msg=user_deleted');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die('Error al eliminar usuario: ' . $e->getMessage());
}