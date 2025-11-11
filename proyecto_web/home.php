<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/../app/helpers/auth_helpers.php';
require_login();

$user = current_user($pdo);
$rol = $_SESSION['rol_nombre'] ?? '';
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$counts = [];
try {
    $counts['cursos'] = $pdo->query("SELECT COUNT(*) AS c FROM cursos")->fetch(PDO::FETCH_ASSOC)['c'] ?? 0;
    $counts['usuarios'] = $pdo->query("SELECT COUNT(*) AS c FROM usuarios")->fetch(PDO::FETCH_ASSOC)['c'] ?? 0;
} catch (PDOException $e) {
    $counts['error'] = $e->getMessage();
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Inicio</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
        <h4>Inicio</h4>
        <small class="text-muted">Bienvenido, <?=h($user['nombre_completo'] ?? 'Usuario')?></small>
        </div>
        <div>
        <a href="autorizar.php?action=logout" class="btn btn-outline-danger">Cerrar sesión</a>
        </div>
    </div>
    <div class="row g-3">
        <?php if ($rol === 'admin'): ?>
        <div class="col-md-4"><a class="card text-decoration-none text-dark" href="admin.php"><div class="card-body"><h5>Panel Admin</h5><p class="small text-muted">Gestionar usuarios y cursos</p></div></a></div>
        <?php endif; ?>
        <?php if ($rol === 'profesor'): ?>
        <div class="col-md-4"><a class="card text-decoration-none text-dark" href="profesor.php"><div class="card-body"><h5>Panel Profesor</h5><p class="small text-muted">Mis cursos, tareas y solicitudes</p></div></a></div>
        <?php endif; ?>
        <?php if ($rol === 'alumno'): ?>
        <div class="col-md-4"><a class="card text-decoration-none text-dark" href="alumno.php"><div class="card-body"><h5>Panel Alumno</h5><p class="small text-muted">Mis cursos y calificaciones</p></div></a></div>
        <?php endif; ?>
        <div class="col-md-4"><a class="card text-decoration-none text-dark" href="cursos.php"><div class="card-body"><h5>Cursos</h5><p class="small text-muted">Explorar cursos disponibles</p></div></a></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><h5>Estadísticas</h5><p class="small text-muted">Cursos: <?=h($counts['cursos'])?> • Usuarios: <?=h($counts['usuarios'])?></p></div></div></div>
    </div>
</div>
</body>
</html>