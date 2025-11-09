<?php
require_once __DIR__ . 'helper.php';
require_login();
require_once __DIR__ . 'config_database.php';

$usuario_id = intval($_SESSION['usuario_id']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') header('Location: cursos.php');

$curso_id = intval($_POST['curso_id'] ?? 0);
$codigo = trim($_POST['codigo_acceso'] ?? '');

if ($curso_id <= 0) header('Location: cursos.php?error=curso_invalido');

$chk = $conn->prepare("SELECT id, estado FROM inscripciones WHERE curso_id = ? AND estudiante_id = ?");
$chk->bind_param('ii', $curso_id, $usuario_id);
$chk->execute(); $chk->bind_result($insc_id, $insc_estado);
if ($chk->fetch()) { $chk->close(); header('Location: cursos.php?msg=Ya_inscrito'); exit; }
$chk->close();

$stmt = $conn->prepare("SELECT codigo_acceso, auto_aprobar FROM cursos WHERE id = ?");
$stmt->bind_param('i', $curso_id);
$stmt->execute(); $stmt->bind_result($codigo_db, $auto_aprobar);
if (!$stmt->fetch()) { $stmt->close(); header('Location: cursos.php?error=curso_no'); exit; }
$stmt->close();

if (!is_null($codigo_db) && $codigo_db !== '') {
    if ($codigo === '') header('Location: cursos.php?error=se_requiere_codigo&curso_id='.$curso_id);
    if (!hash_equals($codigo_db, $codigo)) header('Location: cursos.php?error=codigo_incorrecto');
}

$estado = ($auto_aprobar == 1) ? 'APROBADO' : 'PENDIENTE';
$ins = $conn->prepare("INSERT INTO inscripciones (curso_id, estudiante_id, estado, fecha_postulacion) VALUES (?, ?, ?, NOW())");
$ins->bind_param('iis', $curso_id, $usuario_id, $estado);
if ($ins->execute()) {
    $ins_id = $ins->insert_id; $ins->close();
    if ($estado === 'APROBADO') {
        $upd = $conn->prepare("UPDATE inscripciones SET fecha_inscripcion = NOW(), aceptado_por = 0 WHERE id = ?");
        $upd->bind_param('i', $ins_id); $upd->execute(); $upd->close();
    }
    log_action($conn, $usuario_id, 'solicitar_inscripcion', 'inscripciones', $ins_id, ['curso_id'=>$curso_id,'estado'=>$estado]);
    header('Location: cursos.php?msg=solicitud_enviada');
} else {
    header('Location: cursos.php?error=db');
}