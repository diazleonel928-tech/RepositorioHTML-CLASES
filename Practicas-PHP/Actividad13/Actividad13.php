<?php
    define("N_Alumnos", 5);
    $estudiantes = [
        "Ana" => rand(50, 100),
        "Luis" => rand(50, 100),
        "Maria" => rand(50, 100),
        "Carlos" => rand(50, 100),
        "Sofia" => rand(50, 100)
    ];

    function Promedio($arreglo){
        $suma = 0;
        foreach($arreglo as $calificacion) {
            $suma += $calificacion;
        }
        return $suma / count($arreglo);
    }

    echo "<h2>Lista de calificaciones: </h2>";
    foreach($estudiantes as $nombre => $calificacion){
        echo "Nombre: $nombre <br>";
        echo "Calificacion: $calificacion <br>";

    if($calificacion >= 70){
        echo "Resultado: Aprobado <br><br>";
    } else{
        echo "Resultado: Reprobado <br><br>";
    }

}

    $promedio = Promedio($estudiantes);
    echo "<h3> Promedio general: " . number_format($promedio, 2) . "</h3>";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.0/css/bulma.min.css">
    <link rel="stylesheet" type="text/css" href="Stylephp.css">
    <title>Document</title>
</head>
<body>
    <section class="section">
    <div class="container">
        <h1 class="title has-text-centered">Lista de Calificaciones</h1>
        <div class="box">
            <table class="table is-striped is-hoverable is-fullwidth">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Calificación</th>
                        <th>Resultado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($estudiantes as $nombre => $calificacion): ?>
                        <tr>
                            <td><?= $nombre ?></td>
                            <td><?= $calificacion ?></td>
                            <td>
                                <?php if ($calificacion >= 70): ?>
                                    <span class="tag is-success">Aprobado</span>
                                <?php else: ?>
                                    <span class="tag is-danger">Reprobado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
                <div class="notification is-info has-text-centered">
                    <strong>Promedio general:</strong> <?= number_format($promedio, 2) ?>
                </div>
            </div>
        </div>
    </section>
</body>
</html>