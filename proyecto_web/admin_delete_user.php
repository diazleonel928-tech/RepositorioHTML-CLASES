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

$del_id = intval($_POST['user_id'] ?? 0);
if ($del_id <= 0) {
    header('Location: admin.php?msg=usuario_invalido');
    exit;
}

if ($del_id === intval($_SESSION['usuario_id'])) {
    header('Location: admin.php?msg=no_puedes_eliminar_tu_cuenta');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, correo, nombre_completo FROM usuarios WHERE id = ?");
    $stmt->execute([$del_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header('Location: admin.php?msg=usuario_no_encontrado');
        exit;
    }

    $pdo->prepare("DELETE FROM inscripciones WHERE estudiante_id = ?")->execute([$del_id]);
    $pdo->prepare("DELETE FROM entregas WHERE estudiante_id = ?")->execute([$del_id]);

    $stmtDel = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmtDel->execute([$del_id]);

    header('Location: admin.php?msg=Usuario+' . urlencode($usuario['nombre_completo']) . '+eliminado');
    exit;

} catch (PDOException $e) {
    header('Location: admin.php?msg=Error+al+eliminar+usuario:+'.urlencode($e->getMessage()));
    exit;
}