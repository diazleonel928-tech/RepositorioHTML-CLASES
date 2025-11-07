<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Información rápida del entorno
echo "<h3>PHP Info resumido</h3>";
echo "PHP SAPI: " . php_sapi_name() . "<br>";
echo "Ruta del script: " . __FILE__ . "<br>";

// Probar conexión MySQLi
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'proyecto_web'; // ajusta aquí al nombre correcto si es necesario
$port = 3306;

echo "<h3>Probando conexión MySQLi</h3>";
$conn = @mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    echo "<p style='color:red;'> Conexión fallida:</p>";
    echo "<pre>" . htmlspecialchars(mysqli_connect_error()) . "</pre>";
} else {
    echo "<p style='color:green;'> Conexión MySQLi exitosa</p>";
    $res = mysqli_query($conn, "SELECT NOW() AS ahora");
    $row = mysqli_fetch_assoc($res);
    echo "Fecha MySQL: " . htmlspecialchars($row['ahora']);
    mysqli_close($conn);
}
?>