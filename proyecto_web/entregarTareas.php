<?php
require_once __DIR__ . '/helper.php';
require_login();
require_once __DIR__ . '/config_database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') header('Location: home.php');
$usuario_id = intval($_SESSION['usuario_id']);
$rol = $_SESSION['rol_nombre'] ?? '';
if ($rol !== 'alumno') die('Solo alumnos pueden entregar.');

$tarea_id = intval($_POST['tarea_id'] ?? 0);
if ($tarea_id <= 0) die('Tarea inválida');

$st = $conn->prepare("SELECT curso_id FROM tareas WHERE id=?"); $st->bind_param('i',$tarea_id); $st->execute(); $st->bind_result($curso_id);
if (!$st->fetch()) { $st->close(); die('Tarea no encontrada'); } $st->close();

if (!alumno_aprobado_en_curso($conn, $usuario_id, $curso_id)) die('No estás aprobado en el curso.');

$uploadPath = __DIR__ . 'entregas/';
if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);

$archivo_final = null;
if (!empty($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['archivo']['tmp_name'];
    $name = basename($_FILES['archivo']['name']);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $allowed = ['pdf','doc','docx','zip','txt'];
    if (!in_array($ext, $allowed)) die('Tipo de archivo no permitido');
    if ($_FILES['archivo']['size'] > 8 * 1024 * 1024) die('Archivo muy grande');
    $nuevo = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($tmp, $uploadPath . $nuevo)) die('Error al guardar archivo.');
    $archivo_final = 'uploads/entregas/' . $nuevo;
}

$texto = trim($_POST['texto'] ?? '');

$check = $conn->prepare("SELECT id FROM entregas WHERE tarea_id = ? AND estudiante_id = ?");
$check->bind_param('ii', $tarea_id, $usuario_id); $check->execute(); $check->bind_result($existing_id);
if ($check->fetch()) {
    $check->close();
    if ($archivo_final) {
        $upd = $conn->prepare("UPDATE entregas SET archivo = ?, fecha_entregado = NOW(), comentario = ? WHERE id = ?");
        $upd->bind_param('ssi', $archivo_final, $texto, $existing_id);
    } else {
        $upd = $conn->prepare("UPDATE entregas SET fecha_entregado = NOW(), comentario = ? WHERE id = ?");
        $upd->bind_param('si', $texto, $existing_id);
    }
    $upd->execute(); $upd->close();
    log_action($conn, $usuario_id, 'actualizar_entrega', 'entregas', $existing_id, ['tarea_id'=>$tarea_id]);
    header('Location: tareaDetalles.php?id=' . $tarea_id . '&msg=actualizada');
    exit;
} else {
    $check->close();
    $ins = $conn->prepare("INSERT INTO entregas (tarea_id, estudiante_id, archivo, fecha_entregado, comentario) VALUES (?, ?, ?, NOW(), ?)");
    $ins->bind_param('iiss', $tarea_id, $usuario_id, $archivo_final, $texto);
    $ins->execute();
    $new_id = $ins->insert_id; $ins->close();
    log_action($conn, $usuario_id, 'crear_entrega', 'entregas', $new_id, ['tarea_id'=>$tarea_id]);
    header('Location: tareaDetalles.php?id=' . $tarea_id . '&msg=entregada');
    exit;
}