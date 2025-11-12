<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'logout') {
    session_unset();
    session_destroy();
    header('Location: login.php?msg=logout');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_GET['action'] ?? '') !== 'login') {
    header('Location: login.php');
    exit;
}

$email = trim($_POST['correo'] ?? '');
$pass = $_POST['contrasena'] ?? '';

if ($email === '' || $pass === '') {
    header('Location: login.php?msg=empty');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, correo, contrasena_hash, nombre_completo, rol_id FROM usuarios WHERE correo = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || !password_verify($pass, $user['contrasena_hash'] ?? '')) {
        header('Location: login.php?msg=invalid');
        exit;
    }

    $stm2 = $pdo->prepare("SELECT nombre FROM roles WHERE id = ?");
    $stm2->execute([intval($user['rol_id'])]);
    $rol = $stm2->fetch(PDO::FETCH_ASSOC)['nombre'] ?? '';

    $_SESSION['usuario_id'] = intval($user['id']);
    $_SESSION['usuario_nombre'] = $user['nombre_completo'];
    $_SESSION['rol_nombre'] = $rol;
    switch ($rol) {
        case 'admin': header('Location: admin.php'); break;
        case 'profesor': header('Location: profesor.php'); break;
        default: header('Location: alumno.php'); break;
    }
    exit;
} catch (PDOException $e) {
    die("Error BD: " . $e->getMessage());
}