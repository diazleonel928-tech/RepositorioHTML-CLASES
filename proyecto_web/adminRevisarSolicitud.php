<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();
if (($_SESSION['rol_nombre'] ?? '') !== 'admin') { http_response_code(403); die('Acceso denegado'); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') header('Location: adminSolicitudes.php');

$solId = intval($_POST['solicitud_id'] ?? 0);
$accion = $_POST['accion'] ?? '';
if ($solId <= 0 || !in_array($accion, ['aprobar','rechazar'])) {
    header('Location: adminSolicitudes.php?error=param');
    exit;
}
if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    header('Location: adminSolicitudes.php?error=csrf');
    exit;
}

try {
    $st = $pdo->prepare("SELECT * FROM profesor_solicitudes WHERE id = ?");
    $st->execute([$solId]);
    $sol = $st->fetch(PDO::FETCH_ASSOC);
    if (!$sol) { header('Location: adminSolicitudes.php?error=no'); exit; }

    $pdo->beginTransaction();

    if ($accion === 'aprobar') {
        $r = $pdo->prepare("SELECT id FROM roles WHERE nombre = ? LIMIT 1");
        $r->execute(['profesor']);
        $rol = $r->fetch(PDO::FETCH_ASSOC);
        if (!$rol) throw new Exception('Rol "profesor" no existe en la base de datos.');
        $updUser = $pdo->prepare("UPDATE usuarios SET rol_id = ? WHERE id = ?");
        $updUser->execute([intval($rol['id']), intval($sol['usuario_id'])]);
        $updS = $pdo->prepare("UPDATE profesor_solicitudes SET estado = 'APROBADO', aprobado_por = ?, fecha_respuesta = NOW() WHERE id = ?");
        $updS->execute([intval($_SESSION['usuario_id']), $solId]);
    } else {
        $updS = $pdo->prepare("UPDATE profesor_solicitudes SET estado = 'RECHAZADO', aprobado_por = ?, fecha_respuesta = NOW() WHERE id = ?");
        $updS->execute([intval($_SESSION['usuario_id']), $solId]);
    }

    $pdo->commit();
    header('Location: admin_profesor_requests.php?msg=ok');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die('Error: ' . $e->getMessage());
}