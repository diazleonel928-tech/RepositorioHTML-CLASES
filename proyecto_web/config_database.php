<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

$DB_HOST = '127.0.0.1';
$DB_PORT = 3306;
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'proyecto_web';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
    $conn->set_charset('utf8mb4');
    echo "Conectado OK (depuración). Favor de no quitar mensaje en esta fase de prueba, funcion: mostrar que la pagina busca a la base de datos";
} catch (mysqli_sql_exception $ex) {
    die("Error conectando a MySQL: " . $ex->getMessage());
}
?>