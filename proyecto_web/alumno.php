<?php
session_start();
require_once __DIR__ . '/../config_database.php';
require_once __DIR__ . '/../app/helpers/auth_helpers.php';
require_login();
if (($_SESSION['rol_nombre'] ?? '') !== 'alumno') { http_response_code(403); die('Acceso denegado'); }

$alumno_id = intval($_SESSION['usuario_id']);
function h($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$stmt = $pdo->prepare("
    SELECT c.nombre, c.codigo, i.estado, i.fecha_inscripcion
    FROM inscripciones i
    JOIN cursos c ON i.curso_id = c.id
    WHERE i.estudiante_id = ?
");
$stmt->execute([$alumno_id]);
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Panel Alumno</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Panel del Alumno</h3>
        <a href="logout.php" class="btn btn-outline-danger">Cerrar sesión</a>
    </div>

    <a href="cursos.php" class="btn btn-primary mb-3">Ver cursos disponibles</a>

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">Mis inscripciones</div>
        <div class="card-body">
        <?php if (empty($cursos)): ?>
            <p class="text-muted">Aún no estás inscrito en ningún curso.</p>
        <?php else: ?>
            <table class="table table-striped">
            <thead><tr><th>Código</th><th>Curso</th><th>Estado</th><th>Fecha inscripción</th></tr></thead>
            <tbody>
            <?php foreach ($cursos as $c): ?>
                <tr>
                <td><?=h($c['codigo'])?></td>
                <td><?=h($c['nombre'])?></td>
                <td><?=h($c['estado'])?></td>
                <td><?=h($c['fecha_inscripcion'] ?? '---')?></td>
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