<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();

if (($_SESSION['rol_nombre'] ?? '') !== 'admin') {
    http_response_code(403);
    die('Acceso denegado');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php?msg=Metodo+no+permitido');
    exit;
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    header('Location: admin.php?msg=csrf_invalido');
    exit;
}

$curso_id = intval($_POST['curso_id'] ?? 0);
if ($curso_id <= 0) {
    header('Location: admin.php?msg=curso_invalido');
    exit;
}

try {
    $s = $pdo->prepare("SELECT id, nombre FROM cursos WHERE id = ?");
    $s->execute([$curso_id]);
    $curso = $s->fetch(PDO::FETCH_ASSOC);
    if (!$curso) {
        header('Location: admin.php?msg=curso_no_encontrado');
        exit;
    }

    $pdo->beginTransaction();

    $tstmt = $pdo->prepare("SELECT id FROM tareas WHERE curso_id = ?");
    $tstmt->execute([$curso_id]);
    $tareas = $tstmt->fetchAll(PDO::FETCH_COLUMN, 0);
    if (!empty($tareas)) {
        $in = implode(',', array_fill(0, count($tareas), '?'));
        $qFiles = $pdo->prepare("SELECT archivo FROM entregas WHERE tarea_id IN ($in)");
        $qFiles->execute($tareas);
        $files = $qFiles->fetchAll(PDO::FETCH_COLUMN, 0);
        foreach ($files as $f) {
            if (!empty($f)) @unlink_public_file($f);
        }

        // 3) Eliminar entregas
        $delEnt = $pdo->prepare("DELETE FROM entregas WHERE tarea_id IN ($in)");
        $delEnt->execute($tareas);

        try {
            $delCal = $pdo->prepare("DELETE FROM calificaciones WHERE tareas_id IN ($in)");
            $delCal->execute($tareas);
        } catch (PDOException $e) {
            try {
                $delCal2 = $pdo->prepare("DELETE FROM calificaciones WHERE tarea_id IN ($in)");
                $delCal2->execute($tareas);
            } catch (PDOException $e2) {
            }
        }

        $delT = $pdo->prepare("DELETE FROM tareas WHERE id IN ($in)");
        $delT->execute($tareas);
    }

    $pdo->prepare("DELETE FROM inscripciones WHERE curso_id = ?")->execute([$curso_id]);

    $pdo->prepare("DELETE FROM cursos WHERE id = ?")->execute([$curso_id]);

    $pdo->commit();

    header('Location: admin.php?msg=curso_eliminado');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header('Location: admin.php?msg=' . urlencode('Error al eliminar curso: ' . $e->getMessage()));
    exit;
}