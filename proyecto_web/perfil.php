<?php
session_start();
require_once __DIR__ . 'config_database.php';
if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $conn->prepare("SELECT correo, nombre_completo FROM usuarios WHERE id = ?");
$stmt->bind_param('i', $_SESSION['usuario_id']);
$stmt->execute();
$stmt->bind_result($correo, $nombre);
$stmt->fetch();
$stmt->close();
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Perfil</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="card">
        <div class="card-body">
        <h4>Mi perfil</h4>
        <p><strong>Nombre:</strong> <?=htmlspecialchars($nombre)?></p>
        <p><strong>Correo:</strong> <?=htmlspecialchars($correo)?></p>

        <p>
        <a class="btn btn-outline-primary" href="cambiarPassword.php">Cambiar contraseña</a>
        <a class="btn btn-outline-danger" href="delete_account.php">Eliminar cuenta</a>
        </p>

        <p><a href="login.php">Volver al panel</a></p>
    </div>
    </div>
</div>
</body>
</html>
