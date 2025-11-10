<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();

$usuario_id = intval($_SESSION['usuario_id']);
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rol_nombre = $_SESSION['rol_nombre'] ?? 'alumno';

function h($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Home</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
    <a class="navbar-brand" href="home.php">Seguimiento Académico Proyecto web</a>
    <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="profile.php">Mi perfil</a></li>
        <li class="nav-item"><a class="nav-link" href="cambiarPassword.php">Cambiar contraseña</a></li>
        <?php if ($rol_nombre === 'alumno'): ?><li class="nav-item"><a class="nav-link" href="mis_calificaciones.php">Mis calificaciones</a></li><?php endif; ?>
    <li class="nav-item"><a class="nav-link text-light" href="autorizar.php?action=logout">Cerrar sesión</a></li>
    </ul>
</div>
</nav>
<div class="container mt-4">
    <h4>Bienvenido, <?=h($usuario_nombre)?> <small class="text-muted">(<?=h($rol_nombre)?>)</small></h4>
    <div class="mt-3">
    <a class="btn btn-outline-primary" href="cursos.php">Explorar cursos</a>
    <?php if ($rol_nombre === 'profesor'): ?>
        <a class="btn btn-outline-warning" href="crearCurso.php">Crear curso</a>
        <a class="btn btn-outline-info" href="profesorCursos.php">Ver promedios alumnos</a>
    <?php endif; ?>
    </div>
</div>
</body>
</html>