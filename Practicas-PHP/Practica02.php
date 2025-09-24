<?php
    if($_POST){
    $nombre=$_POST['txtNombre'];

    echo "Hola ".$nombre;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practica 02</title>
</head>
<body>
    <h1>Practica 02</h1>

    <form action="Practica02.php" method="post">
        <label for="nombre">Nombre</label>
        <input type="text" name="txtNombre" id="">
        <br/>
        <input type="submit" value="Enviar">
    </form>

</body>
</html>