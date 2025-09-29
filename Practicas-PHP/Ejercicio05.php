<?php
    if ($_POST) {
            $Nombre=$_POST['txtNombre'];
            $Altura=(float)$_POST['Altura'];
            $Programador=$_POST['Programador'];
        echo "Hola " .$Nombre ." " .$Altura ." " .$Programador; 
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Style.css">
    <title>Document</title>
</head>
<body>
    <form action="Ejercicio05.php" method="post">
        <label for="nombre">Nombre</label>
        <input type="text" name="txtNombre" id="">
        <br/>
        <label for="Altura">Altura</label>
        <input type="text" name="Altura" id="">
        <br/>
        <label for="Programador">Es programador</label>
        <select name="Programador" id="Programador">
                <option value="Es Programador">Es Programador</option>
                <option value="No es Programador">No es Programador</option>
        </select>
        <br/>

        <input type="submit" value="Enviar">
    </form>
</body>
</html>