<?php
session_start();

if (empty($_SESSION['authenticated']) && !empty($_COOKIE['remember'])) {
    $data = base64_decode($_COOKIE['remember']);
    if ($data !== false && strpos($data, '|') !== false) {
        list($u, $color) = explode('|', $data, 2);
        $u = trim($u);
        $color = trim($color);
        if ($u !== '') {
            $_SESSION['authenticated'] = true;
            $_SESSION['username'] = $u;
            $_SESSION['color'] = $color ?: '#59f';
        }
    }
}

$logged = !empty($_SESSION['authenticated']);
$username = $logged ? htmlspecialchars($_SESSION['username']) : null;
$color = $logged && !empty($_SESSION['color']) ? htmlspecialchars($_SESSION['color']) : '#59f';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      :root { --user-color: <?= $color ?>; }
      .text-user { color: var(--user-color) !important; font-weight:600; }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-white bg-white border-bottom mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="home.php">MiApp</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <?php if ($logged): ?>
          <li class="nav-item"><a class="nav-link text-user" href="home.php"><?= $username ?></a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Cerrar sesión</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.html">Iniciar sesión</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
  <div class="card shadow-sm">
    <div class="card-body">
      <h1 class="card-title">Bienvenido a MiApp</h1>
      <?php if ($logged): ?>
        <p>Has iniciado sesión como <span class="text-user"><?= $username ?></span>.</p>
        <p class="text-muted">Prueba cerrar y reabrir el navegador: si marcaste "Recordarme", la sesión puede restaurarse mediante cookie.</p>
        <a class="btn btn-outline-secondary" href="logout.php">Cerrar sesión</a>
      <?php else: ?>
        <p class="lead">No estás autenticado. Usa el formulario de inicio de sesión.</p>
        <a class="btn btn-primary" href="login.html">Iniciar sesión</a>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
