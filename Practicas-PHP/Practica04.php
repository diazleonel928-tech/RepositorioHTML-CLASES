<?php
    if($_POST){
    $nombre=$_POST['txtNombre'];
    $apellido=$_POST['txtApellido'];

    echo "Hola ".$nombre ." " .$apellido;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="Practica04.php" method="post">
        <label for="nombre">Nombre</label>
        <input type="text" name="txtNombre" id="">
        <br/>
        <label for="apellido">apellido</label>
        <input type="text" name="txtApellido" id="">
        <br/>
        <input type="submit" value="Enviar">
    </form>
</body>
</html>