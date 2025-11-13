<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();

$usuario_id = intval($_SESSION['usuario_id']);
$rol = $_SESSION['rol_nombre'] ?? 'alumno';

try {
    $stmt = $pdo->query("SELECT c.id, c.codigo, c.nombre, c.descripcion, c.creador_id, u.nombre_completo AS creador_nombre FROM cursos c LEFT JOIN usuarios u ON c.creador_id = u.id ORDER BY c.fecha_creacion DESC");
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar cursos: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Cursos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Cursos disponibles</h4>
        <a class="btn btn-secondary" href="home.php">← Volver al inicio</a>
    </div>
    <div class="row g-3">
        <?php foreach ($cursos as $c): 
        $estado = null;
        if ($rol === 'alumno') {
            $stmt = $pdo->prepare("SELECT estado FROM inscripciones WHERE estudiante_id = ? AND curso_id = ?");
            $stmt->execute([$usuario_id, $c['id']]);
            $ins = $stmt->fetch(PDO::FETCH_ASSOC);
            $estado = $ins['estado'] ?? null;
        }
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?=h($c['nombre'])?></h5>
                <p class="card-text small text-muted mb-2">Profesor: <?=h($c['creador_nombre'] ?? '—')?></p>
                <p class="card-text"><?=h(mb_strimwidth($c['descripcion'] ?? '', 0, 140, '...'))?></p>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                <?php if ($rol === 'alumno'): ?>
                    <?php if (empty($estado)): ?>
                    <a class="btn btn-sm btn-primary" href="cursoDetalles.php?id=<?=intval($c['id'])?>">Revisar curso</a>
                    <?php else: ?>
                    <span class="badge <?= $estado==='APROBADO' ? 'bg-success' : ($estado==='PENDIENTE' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                        <?=h($estado)?>
                    </span>
                    <a class="btn btn-sm btn-outline-primary" href="cursoDetalles.php?id=<?=intval($c['id'])?>">Ver</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a class="btn btn-sm btn-outline-primary" href="cursoDetalles.php?id=<?=intval($c['id'])?>">Ver</a>
                <?php endif; ?>
                </div>
            </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>