<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();

if (($_SESSION['rol_nombre'] ?? '') !== 'admin') {
    http_response_code(403);
    die('Acceso denegado');
}

$usuario_id = intval($_SESSION['usuario_id']);
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Administrador';

$msg = $_GET['msg'] ?? null;

$users = [];
$courses = [];

try {
    $sql = "SELECT u.id, u.correo, u.nombre_completo, r.nombre AS rol_nombre
            FROM usuarios u
            LEFT JOIN roles r ON u.rol_id = r.id
            ORDER BY u.id DESC
            LIMIT 200";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql2 = "SELECT c.id, c.codigo, c.nombre, c.descripcion, c.creador_id,
                    u.nombre_completo AS creador_nombre, c.fecha_creacion
                FROM cursos c
                LEFT JOIN usuarios u ON c.creador_id = u.id
                ORDER BY c.fecha_creacion DESC
                LIMIT 200";
    $stmt2 = $pdo->query($sql2);
    $courses = $stmt2->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('Error BD: ' . $e->getMessage());
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Panel Admin</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
        <h1 class="h4 mb-0">Panel Admin</h1>
        <small class="text-muted">Bienvenido, <?=h($usuario_nombre)?></small>
        </div>
        <div class="text-end">
        <a class="btn btn-outline-secondary btn-sm" href="home.php">Volver al sitio</a>
        <a class="btn btn-danger btn-sm" href="autorizar.php?action=logout">Cerrar sesión</a>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?=h($msg)?></div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
            <h5 class="card-title">Usuarios</h5>
            <p class="card-text text-muted">Gestiona cuentas y roles.</p>
            <a href="admin.php#usuarios" class="btn btn-primary">Ver usuarios</a>
            </div>
        </div>
        </div>
        <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
            <h5 class="card-title">Cursos</h5>
            <p class="card-text text-muted">Revisa cursos y administra contenido.</p>
            <a href="admin.php#cursos" class="btn btn-primary">Ver cursos</a>
            </div>
        </div>
        </div>
        <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
            <h5 class="card-title">Acciones rápidas</h5>
            <p class="card-text text-muted">Crear curso o revisar logs.</p>
            <a href="crearCurso.php" class="btn btn-success">Crear curso</a>
            </div>
        </div>
        </div>
    </div>
    <div id="usuarios" class="card mb-3">
        <div class="card-body">
        <h5 class="card-title">Usuarios registrados (<?=count($users)?>)</h5>
        <div class="table-responsive" style="max-height:420px; overflow:auto;">
            <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>ID</th><th>Correo</th><th>Nombre</th><th>Rol</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?=intval($u['id'])?></td>
                    <td><?=h($u['correo'])?></td>
                    <td><?=h($u['nombre_completo'])?></td>
                    <td><?=h($u['rol_nombre'])?></td>
                    <td>
                    <a class="btn btn-sm btn-outline-primary" href="admin_edit_user.php?id=<?=intval($u['id'])?>">Editar</a>
                    <?php if (intval($u['id']) !== $usuario_id): ?>
                        <a class="btn btn-sm btn-outline-danger" href="admin_delete_user.php?id=<?=intval($u['id'])?>">Eliminar</a>
                    <?php else: ?>
                        <span class="badge bg-secondary">Tu cuenta</span>
                    <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            </table>
        </div>
        </div>
    </div>
    <div id="cursos" class="card mb-3">
        <div class="card-body">
        <h5 class="card-title">Cursos (<?=count($courses)?>)</h5>
        <div class="table-responsive" style="max-height:420px; overflow:auto;">
            <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>ID</th><th>Código</th><th>Nombre</th><th>Profesor</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php foreach ($courses as $c): ?>
                <tr>
                    <td><?=intval($c['id'])?></td>
                    <td><?=h($c['codigo'])?></td>
                    <td><?=h($c['nombre'])?></td>
                    <td><?=h($c['creador_nombre'] ?? '—')?></td>
                    <td>
                    <a class="btn btn-sm btn-outline-primary" href="cursoDetalles.php?id=<?=intval($c['id'])?>">Ver</a>
                    <a class="btn btn-sm btn-warning" href="editarCurso.php?id=<?=intval($c['id'])?>">Editar</a>
                    <a class="btn btn-sm btn-outline-danger" href="admin_delete_curso.php?id=<?=intval($c['id'])?>">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            </table>
        </div>
        </div>
    </div>
    <div class="text-center mt-3">
        <a class="btn btn-outline-secondary" href="home.php">← Volver al sitio</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>