<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: cursos.php'); exit; }

$usuario = current_user($pdo);
$usuario_id = intval($usuario['id']);
$rol = $_SESSION['rol_nombre'] ?? '';

$tarea_id = intval($_POST['tarea_id'] ?? 0);
if ($tarea_id <= 0) die('Tarea inválida.');

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('Token CSRF inválido.');

try {
    $stmt = $pdo->prepare("SELECT t.id, t.curso_id, c.creador_id FROM tareas t JOIN cursos c ON t.curso_id = c.id WHERE t.id = ?");
    $stmt->execute([$tarea_id]);
    $tarea = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tarea) die('Tarea no encontrada.');

    if ($rol !== 'alumno' && $rol !== 'admin') die('Solo alumnos pueden entregar tareas.');

    $s = $pdo->prepare("SELECT estado FROM inscripciones WHERE curso_id = ? AND estudiante_id = ?");
    $s->execute([$tarea['curso_id'], $usuario_id]);
    $ins = $s->fetch(PDO::FETCH_ASSOC);
    if (!$ins || $ins['estado'] !== 'APROBADO') die('No estás inscrito/ aprobado en este curso.');

    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        die('Error al subir archivo.');
    }

    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

    $file = $_FILES['archivo'];
    $originalName = basename($file['name']);
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    $allowed = ['pdf','doc','docx','zip','jpg','jpeg','png','txt','rar'];
    if ($ext !== '' && !in_array(strtolower($ext), $allowed)) {
        die('Tipo de archivo no permitido.');
    }

    $newName = sprintf('tarea_%d_user_%d_%d.%s', $tarea_id, $usuario_id, time(), $ext ?: 'bin');
    $targetPath = $uploadDir . '/' . $newName;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        die('No se pudo mover el archivo subido.');
    }

    $q = $pdo->prepare("SELECT id FROM entregas WHERE tarea_id = ? AND estudiante_id = ? LIMIT 1");
    $q->execute([$tarea_id, $usuario_id]);
    $existing = $q->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $upd = $pdo->prepare("UPDATE entregas SET archivo = ?, fecha_entregado = NOW(), comentario = ?, calificacion = NULL WHERE id = ?");
        $upd->execute([$publicPath, trim($_POST['comentario'] ?? ''), intval($existing['id'])]);
    } else {
        $ins = $pdo->prepare("INSERT INTO entregas (tarea_id, estudiante_id, archivo, fecha_entregado, comentario) VALUES (?, ?, ?, NOW(), ?)");
        $ins->execute([$tarea_id, $usuario_id, $publicPath, trim($_POST['comentario'] ?? '')]);
    }

    header('Location: tareaDetalles.php?id=' . $tarea_id . '&msg=entregado');
    exit;

} catch (PDOException $e) {
    die('Error BD: ' . $e->getMessage());
}