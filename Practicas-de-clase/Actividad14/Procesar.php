<?php
    $num1 = $_POST['num1'];
    $num2 = $_POST['num2'];
    $op = $_POST['operacion'];
    $resultado = 0;


    switch($op){
        case "suma":
        $resultado = $num1 + $num2;
        break;
        case "resta":
        $resultado = $num1 - $num2;
        break;
        case "mul":
        $resultado = $num1 * $num2;
        break;
        case "div":
        $resultado = $num1 / $num2;
        break;
        default:
        echo "no disponible";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.0/css/bulma.min.css">
    <title>Document</title>
</head>
<body>
    <div class="resultado-box">
        <h1 class="title">Resultado de la Operación</h1>
        <p><strong>Número 1:</strong> <?php echo $num1; ?></p>
        <p><strong>Número 2:</strong> <?php echo $num2; ?></p>
        <hr>
        <p class="resultado">Resultado: 
            <?php echo is_numeric($resultado) ? number_format($resultado, 2) : $resultado; ?>
        </p>
        <a href="../index.html" class="button is-primary">Volver</a>
    </div>
</body>
</html>