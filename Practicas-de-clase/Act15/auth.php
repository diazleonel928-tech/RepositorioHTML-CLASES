<?php
session_start();

define('APP_USER', 'admin');
define('APP_PASS', 'secret');

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$remember = isset($_POST['remember']);

$errors = [];
if ($username === '') $errors[] = 'usuario vacío.';
if ($password === '') $errors[] = 'Ingrese contraseña por favor.';

if (!empty($errors)) {
    ?>
    <!doctype html>
    <html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Error - Auth</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head><body class="bg-light">
    <div class="container py-4">
      <div class="card">
        <div class="card-body">
          <h3>Errores</h3>
          <div class="alert alert-danger">
            <?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
          </div>
          <a class="btn btn-outline-secondary" href="login.html">Volver al formulario</a>
        </div>
      </div>
    </div>
    </body></html>
    <?php
    exit;
}

if ($username === APP_USER && $password === APP_PASS) {
    $_SESSION['authenticated'] = true;
    $_SESSION['username'] = $username;
    if (empty($_SESSION['color'])) $_SESSION['color'] = '#59f';

    if ($remember) {
        $value = base64_encode($username . '|' . ($_SESSION['color'] ?? '#59f'));
        setcookie('remember', $value, time() + 60*60*24*30, '/'); // 30 días
    } else {
        if (!empty($_COOKIE['remember'])) {
            setcookie('remember', '', time() - 3600, '/');
        }
    }

    header('Location: home.php');
    exit;
} else {
    ?>
    <!doctype html>
    <html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Credenciales incorrectas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head><body class="bg-light">
    <div class="container py-4">
      <div class="card">
        <div class="card-body">
          <h3>Credenciales incorrectas</h3>
          <div class="alert alert-danger">Usuario o contraseña no válidos.</div>
          <a class="btn btn-outline-secondary" href="login.html">Volver a intentar</a>
        </div>
      </div>
    </div>
    </body></html>
    <?php
    exit;
}
