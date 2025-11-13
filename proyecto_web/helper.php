<?php
if (session_status() === PHP_SESSION_NONE) session_start();


function require_login() {
    if (empty($_SESSION['usuario_id'])) {
        header('Location: login.php?msg=login_required');
        exit;
    }
}

if (!function_exists('h')) {
    function h($v) {
        return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/** @return array|null */
function current_user(PDO $pdo) {
    if (empty($_SESSION['usuario_id'])) return null;
    $id = intval($_SESSION['usuario_id']);
    $stmt = $pdo->prepare("SELECT id, correo, nombre_completo, rol_id FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function is_role(PDO $pdo, $roleName) {
    $user = current_user($pdo);
    if (!$user) return false;
    if (!isset($_SESSION['rol_nombre'])) {
        $stmt = $pdo->prepare("SELECT nombre FROM roles WHERE id = ?");
        $stmt->execute([intval($user['rol_id'])]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['rol_nombre'] = $r['nombre'] ?? null;
    }
    return ($_SESSION['rol_nombre'] ?? '') === $roleName;
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    return $_SESSION['csrf_token'];
}
function validate_csrf_token($token) {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}