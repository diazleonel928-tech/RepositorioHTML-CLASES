<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();
if (($_SESSION['rol_nombre'] ?? '') !== 'profesor') { http_response_code(403); die('Acceso denegado'); }

$profesor_id = intval($_SESSION['usuario_id']);
function h($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$stmt = $pdo->prepare("SELECT id, codigo, nombre, fecha_creacion FROM cursos WHERE creador_id = ?");
$stmt->execute([$profesor_id]);
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Panel Profesor</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Panel del Profesor</h3>
        <a href="logout.php" class="btn btn-outline-danger">Cerrar sesión</a>
    </div>

    <a href="crear_curso.php" class="btn btn-primary mb-3">+ Crear nuevo curso</a>
    <a href="profesor_inscripciones.php" class="btn btn-warning mb-3">Ver solicitudes pendientes</a>

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">Tus cursos</div>
        <div class="card-body">
        <?php if (empty($cursos)): ?>
            <p class="text-muted">No has creado ningún curso aún.</p>
        <?php else: ?>
            <table class="table table-striped">
            <thead><tr><th>Código</th><th>Nombre</th><th>Creado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($cursos as $c): ?>
                <tr>
                <td><?=h($c['codigo'])?></td>
                <td><?=h($c['nombre'])?></td>
                <td><?=h($c['fecha_creacion'])?></td>
                <td>
                    <a href="ver_curso.php?id=<?=h($c['id'])?>" class="btn btn-sm btn-success">Ver</a>
                    <a href="modificar_curso.php?id=<?=h($c['id'])?>" class="btn btn-sm btn-primary">Editar</a>
                </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            </table>
        <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>