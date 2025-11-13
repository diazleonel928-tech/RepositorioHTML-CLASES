<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';

require_login();

$usuario_id = intval($_SESSION['usuario_id']);
$rol = $_SESSION['rol_nombre'] ?? '';
if (!($rol === 'profesor' || $rol === 'admin')) {
    http_response_code(403);
    die('Acceso denegado');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cursos.php');
    exit;
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    header('Location: cursos.php?msg=' . urlencode('CSRF inválido'));
    exit;
}

$curso_id = intval($_POST['curso_id'] ?? 0);
$estudiante_id = intval($_POST['estudiante_id'] ?? 0);
if ($curso_id <= 0 || $estudiante_id <= 0) {
    header('Location: cursoDetalles.php?id=' . $curso_id . '&msg=' . urlencode('Parámetros inválidos'));
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, creador_id FROM cursos WHERE id = ? LIMIT 1");
    $stmt->execute([$curso_id]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$curso) {
        header('Location: cursos.php?msg=' . urlencode('Curso no encontrado'));
        exit;
    }

    if ($rol === 'profesor' && intval($curso['creador_id']) !== $usuario_id) {
        header('Location: cursoDetalles.php?id=' . $curso_id . '&msg=' . urlencode('No tienes permiso'));
        exit;
    }

    $pdo->beginTransaction();

    $tstmt = $pdo->prepare("SELECT id FROM tareas WHERE curso_id = ?");
    $tstmt->execute([$curso_id]);
    $tareas = $tstmt->fetchAll(PDO::FETCH_COLUMN, 0);

    if (!empty($tareas)) {
        $in = implode(',', array_fill(0, count($tareas), '?'));
        $params = $tareas;

        $qFiles = $pdo->prepare("SELECT archivo FROM entregas WHERE tarea_id IN ($in) AND estudiante_id = ?");
        $qFiles->execute(array_merge($params, [$estudiante_id]));
        $files = $qFiles->fetchAll(PDO::FETCH_COLUMN, 0);
        foreach ($files as $f) {
            if (!empty($f)) @unlink_public_file($f);
        }

        $delEnt = $pdo->prepare("DELETE FROM entregas WHERE tarea_id IN ($in) AND estudiante_id = ?");
        $delEnt->execute(array_merge($params, [$estudiante_id]));

        try {
            $delCal = $pdo->prepare("DELETE FROM calificaciones WHERE tareas_id IN ($in) AND estudiantes_id = ?");
            $delCal->execute(array_merge($params, [$estudiante_id]));
        } catch (PDOException $e1) {
            try {
                $delCal2 = $pdo->prepare("DELETE FROM calificaciones WHERE tarea_id IN ($in) AND estudiantes_id = ?");
                $delCal2->execute(array_merge($params, [$estudiante_id]));
            } catch (PDOException $e2) {
                try {
                    $delCal3 = $pdo->prepare("DELETE FROM calificaciones WHERE curso_id = ? AND estudiantes_id = ?");
                    $delCal3->execute([$curso_id, $estudiante_id]);
                } catch (PDOException $e3) {
                }
            }
        }
    } else {
        try {
            $pdo->prepare("DELETE FROM calificaciones WHERE curso_id = ? AND estudiantes_id = ?")->execute([$curso_id, $estudiante_id]);
        } catch (PDOException $e) {
        }
    }

    $pdo->prepare("DELETE FROM inscripciones WHERE curso_id = ? AND estudiante_id = ?")->execute([$curso_id, $estudiante_id]);

    $pdo->commit();

    header('Location: cursoDetalles.php?id=' . $curso_id . '&msg=' . urlencode('Alumno dado de baja correctamente'));
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header('Location: cursoDetalles.php?id=' . $curso_id . '&msg=' . urlencode('Error al dar de baja: ' . $e->getMessage()));
    exit;
}