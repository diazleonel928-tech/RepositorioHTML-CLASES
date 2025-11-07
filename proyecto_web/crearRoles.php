<?php

require_once __DIR__ . '/config_database.php';

$roles = ['admin','profesor','alumno'];
foreach ($roles as $r) {
    $s = $conn->prepare("SELECT id FROM roles WHERE nombre = ?");
    $s->bind_param('s', $r);
    $s->execute();
    $s->store_result();
    if ($s->num_rows == 0) {
        $ins = $conn->prepare("INSERT INTO roles (nombre) VALUES (?)");
        $ins->bind_param('s', $r);
        $ins->execute();
        $ins->close();
        echo "Rol $r creado.<br>";
    } else {
        echo "Rol $r ya existe.<br>";
    }
    $s->close();
}

$admin_email = 'admin@local';
$admin_password = 'Admin123!';
$admin_name = 'Administrador';

$s = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
$s->bind_param('s', $admin_email);
$s->execute();
$s->store_result();
if ($s->num_rows == 0) {
    // obtener id rol admin
    $r = $conn->prepare("SELECT id FROM roles WHERE nombre = 'admin'");
    $r->execute();
    $r->bind_result($admin_rol_id);
    $r->fetch();
    $r->close();

    $hash = password_hash($admin_password, PASSWORD_DEFAULT);
    $ins = $conn->prepare("INSERT INTO usuarios (correo, contrasena_hash, nombre_completo, rol_id) VALUES (?, ?, ?, ?)");
    $ins->bind_param('sssi', $admin_email, $hash, $admin_name, $admin_rol_id);
    if ($ins->execute()) {
        echo "Admin creado: $admin_email / $admin_password (cámbiala).<br>";
    } else {
        echo "Error al crear admin.<br>";
    }
    $ins->close();
} else {
    echo "Admin ya existe.<br>";
}
$s->close();

echo "<hr><a href='login.php'>Ir a login</a>";
