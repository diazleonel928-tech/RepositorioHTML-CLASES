<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

echo "<h3>Test de inclusión de config_database.php</h3>";

$cfgPath = __DIR__ . '/config_database.php';
if (!file_exists($cfgPath)) {
    echo "<p style='color:red;'>No se encontró config_database.php en: $cfgPath</p>";
    exit;
}

require_once $cfgPath;

if (!isset($conn)) {
    echo "<p style='color:red;'>Se incluyó config_database.php pero la variable \$conn no existe.</p>";
    exit;
}

if ($conn->connect_error) {
    echo "<p style='color:red;'>Conexión fallida: " . htmlspecialchars($conn->connect_error) . "</p>";
} else {
    echo "<p style='color:green;'>Conexión exitosa a MySQL — host: " . htmlspecialchars($DB_HOST ?? '') . " bd: " . htmlspecialchars($DB_NAME ?? '') . " (port: " . htmlspecialchars($DB_PORT ?? '') . ")</p>";
    echo "<p>Servidor MySQL: " . htmlspecialchars(mysqli_get_server_info($conn)) . "</p>";
}