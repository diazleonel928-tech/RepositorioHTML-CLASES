<?php
session_start();

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config_database.php'; // $conn

function require_login() {
    if (empty($_SESSION['usuario_id'])) {
        header('Location: /CosasHTML-CSS/proyecto_web/public/login.php');
        exit;
    }
}

function require_role($rol) {
    if (empty($_SESSION['rol_nombre']) || $_SESSION['rol_nombre'] !== $rol) {
        header('HTTP/1.1 403 Forbidden');
        echo "Acceso denegado. (Se requiere rol: $rol)";
        exit;
    }
}

function is_profesor_creador($conn, $usuario_id, $curso_id) {
    $stmt = $conn->prepare("SELECT creador_id FROM cursos WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param('i', $curso_id);
    $stmt->execute();
    $stmt->bind_result($creador_id);
    $ok = false;
    if ($stmt->fetch()) $ok = intval($creador_id) === intval($usuario_id);
    $stmt->close();
    return $ok;
}

function alumno_aprobado_en_curso($conn, $alumno_id, $curso_id) {
    $stmt = $conn->prepare("SELECT estado FROM inscripciones WHERE estudiante_id = ? AND curso_id = ?");
    if (!$stmt) return false;
    $stmt->bind_param('ii', $alumno_id, $curso_id);
    $stmt->execute();
    $stmt->bind_result($estado);
    $ok = false;
    if ($stmt->fetch()) $ok = ($estado === 'APROBADO');
    $stmt->close();
    return $ok;
}

function log_action($conn, $actor_id, $action, $target_table = null, $target_id = null, $details = []) {
    $json = json_encode($details, JSON_UNESCAPED_UNICODE);
    if ($conn->query("SHOW TABLES LIKE 'bitacora'")->num_rows) {
        $stmt = $conn->prepare("INSERT INTO bitacora (actor_id, accion, tabla_objetivo, registro_id, detalles) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param('issis', $actor_id, $action, $target_table, $target_id, $json);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
    return false;
}
?>