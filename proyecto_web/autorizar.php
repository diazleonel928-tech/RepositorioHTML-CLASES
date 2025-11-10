<?php
session_start();
ini_set('display_errors',1); error_reporting(E_ALL);
require_once __DIR__ . '/config_database.php';

$action = $_GET['action'] ?? '';

function redirect_with($url, $params = []) {
    if ($params) $url .= (strpos($url,'?')===false ? '?' : '&') . http_build_query($params);
    header('Location: ' . $url); exit;
}
//token crfs para agregar mas proteccion al usuario
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf_token'];

if ($action === 'register') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $c1 = $_POST['contrasena'] ?? '';
    $c2 = $_POST['contrasena2'] ?? '';
    if ($c1 !== $c2) redirect_with('registro.php', ['error'=>'Las contraseñas no coinciden.']);
    if (strlen($c1) < 6) redirect_with('registro.php', ['error'=>'Contraseña mínimo 6 caracteres.']);

    $s = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $s->bind_param('s', $correo); $s->execute(); $s->store_result();
    if ($s->num_rows > 0) { $s->close(); redirect_with('registro.php', ['error'=>'Correo ya registrado.']); }
    $s->close();

    $r = $conn->prepare("SELECT id FROM roles WHERE nombre = ?");
    $rol = 'alumno';
    $r->bind_param('s', $rol); $r->execute(); $r->bind_result($rol_id);
    if (!$r->fetch()) { $r->close(); redirect_with('registro.php', ['error'=>'Rol alumno no encontrado.']); }
    $r->close();

    $hash = password_hash($c1, PASSWORD_DEFAULT);
    $ins = $conn->prepare("INSERT INTO usuarios (correo, contrasena_hash, nombre_completo, rol_id) VALUES (?, ?, ?, ?)");
    $ins->bind_param('sssi', $correo, $hash, $nombre, $rol_id);
    if ($ins->execute()) { $ins->close(); redirect_with('login.php', ['msg'=>'Cuenta creada, inicia sesión.']); }
    redirect_with('registro.php', ['error'=>'Error al crear cuenta.']);
}

if ($action === 'login') {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $stmt = $conn->prepare("SELECT u.id, u.contrasena_hash, u.nombre_completo, r.nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.correo = ?");
    $stmt->bind_param('s', $correo);
    $stmt->execute();
    $stmt->bind_result($id, $hash, $nombre, $rol_nombre);
    if ($stmt->fetch()) {
        $stmt->close();
        if ($hash && password_verify($contrasena, $hash)) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $id;
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['rol_nombre'] = $rol_nombre;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            redirect_with('home.php');
        } else redirect_with('login.php', ['msg'=>'Correo o contraseña incorrecta.']);
    } else {
        $stmt->close();
        redirect_with('login.php', ['msg'=>'Correo o contraseña incorrecta.']);
    }
}

if ($action === 'logout') {
    session_unset(); session_destroy();
    redirect_with('login.php', ['msg'=>'Has cerrado sesión.']);
}

// Notas para el desarrollador: esto cambia contraseñas
if ($action === 'change_password') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['usuario_id'])) redirect_with('login.php');
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) redirect_with('cambiarPassword.php', ['error'=>'Token inválido.']);
    $current = $_POST['current_password'] ?? '';
    $new1 = $_POST['new_password'] ?? '';
    $new2 = $_POST['new_password2'] ?? '';
    if ($new1 !== $new2) redirect_with('change_password.php', ['error'=>'Las nuevas contraseñas no coinciden.']);
    if (strlen($new1) < 6) redirect_with('change_password.php', ['error'=>'La contraseña debe tener al menos 6 caracteres.']);
    $stmt = $conn->prepare("SELECT contrasena_hash FROM usuarios WHERE id = ?");
    $stmt->bind_param('i', $_SESSION['usuario_id']); $stmt->execute(); $stmt->bind_result($h); 
    if ($stmt->fetch()) {
        $stmt->close();
        if (!password_verify($current, $h)) redirect_with('change_password.php', ['error'=>'Contraseña actual incorrecta.']);
        $newhash = password_hash($new1, PASSWORD_DEFAULT);
        $u = $conn->prepare("UPDATE usuarios SET contrasena_hash = ? WHERE id = ?");
        $u->bind_param('si', $newhash, $_SESSION['usuario_id']); $u->execute(); $u->close();
        redirect_with('change_password.php', ['msg'=>'Contraseña actualizada.']);
    } else { $stmt->close(); redirect_with('login.php'); }
}

// Nota para el desarrolador: esto elimina cientas cuidado con ella
if ($action === 'delete_account') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['usuario_id'])) redirect_with('login.php');
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) redirect_with('delete_account.php', ['error'=>'Token inválido.']);
    $pwd = $_POST['password'] ?? '';
    $stmt = $conn->prepare("SELECT contrasena_hash FROM usuarios WHERE id = ?");
    $stmt->bind_param('i', $_SESSION['usuario_id']); $stmt->execute(); $stmt->bind_result($h); 
    if ($stmt->fetch()) {
        $stmt->close();
        if (!password_verify($pwd, $h)) redirect_with('delete_account.php', ['error'=>'Contraseña incorrecta.']);
        $del = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $del->bind_param('i', $_SESSION['usuario_id']); $del->execute(); $del->close();
        session_unset(); session_destroy();
        redirect_with('login.php', ['msg'=>'Cuenta eliminada.']);
    } else { $stmt->close(); redirect_with('login.php'); }
}

redirect_with('login.php');
?>