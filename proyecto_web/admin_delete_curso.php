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

$curso_id = intval($_POST['curso_id'] ?? 0);
if ($curso_id <= 0) {
    header('Location: admin.php?msg=invalid'); exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, nombre FROM cursos WHERE id = ?");
    $stmt->execute([$curso_id]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$curso) {
        header('Location: admin.php?msg=not_found'); exit;
    }

    $pdo->beginTransaction();

    $q = $pdo->prepare("
        SELECT e.archivo FROM entregas e
        JOIN tareas t ON e.tarea_id = t.id
        WHERE t.curso_id = ?
    ");
    $q->execute([$curso_id]);
    $files = $q->fetchAll(PDO::FETCH_ASSOC);
    foreach ($files as $f) {
        if (!empty($f['archivo'])) @unlink_public_file($f['archivo']);
    }

    $pdo->prepare("
        DELETE e FROM entregas e
        JOIN tareas t ON e.tarea_id = t.id
        WHERE t.curso_id = ?
    ")->execute([$curso_id]);

    $pdo->prepare("
        DELETE c FROM calificaciones c
        JOIN tareas t ON c.tareas_id = t.id
        WHERE t.curso_id = ?
    ")->execute([$curso_id]);

    $pdo->prepare("DELETE FROM tareas WHERE curso_id = ?")->execute([$curso_id]);

    $pdo->prepare("DELETE FROM inscripciones WHERE curso_id = ?")->execute([$curso_id]);

    $pdo->prepare("DELETE FROM cursos WHERE id = ?")->execute([$curso_id]);

    $pdo->commit();
    header('Location: admin.php?msg=curso_eliminado');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die('Error al eliminar curso: ' . $e->getMessage());
}