<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
//require_once __DIR__ . '/../app/helpers/file_helpers.php'; Esto en caso de ser necesario
require_login();

$usuario = current_user($pdo);
if (!$usuario) { header('Location: login.php'); exit; }
$uid = intval($usuario['id']);

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token CSRF inválido.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT r.nombre AS rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.id = ?");
            $stmt->execute([$uid]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $rol_nombre = $row['rol_nombre'] ?? '';

            if ($rol_nombre === 'admin') {
                $c = $pdo->query("SELECT COUNT(*) AS c FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE r.nombre = 'admin'")->fetch(PDO::FETCH_ASSOC);
                $numAdmins = intval($c['c'] ?? 0);
                if ($numAdmins <= 1) {
                    $errors[] = 'No puedes eliminar tu cuenta: eres el único administrador registrado. Crea otro administrador antes de eliminar la cuenta.';
                }
            }

            if (empty($errors)) {
                $pdo->beginTransaction();
            
                $q = $pdo->prepare("SELECT archivo FROM entregas WHERE estudiante_id = ?");
                $q->execute([$uid]);
                $files = $q->fetchAll(PDO::FETCH_ASSOC);
                foreach ($files as $f) {
                    if (!empty($f['archivo'])) {
                        @unlink_public_file($f['archivo']);
                    }
                }

                $pdo->prepare("DELETE FROM entregas WHERE estudiante_id = ?")->execute([$uid]);

                $pdo->prepare("DELETE FROM inscripciones WHERE estudiante_id = ?")->execute([$uid]);

                $pdo->prepare("DELETE FROM calificaciones WHERE estudiantes_id = ?")->execute([$uid]);

                $pdo->prepare("DELETE FROM cursos WHERE creador_id = ?")->execute([$uid]);

                $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$uid]);

                $pdo->commit();

                session_unset();
                session_destroy();
                header('Location: login.php?msg=account_deleted');
                exit;
            }

        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Error al eliminar cuenta: ' . $e->getMessage();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }
}

$csrf = generate_csrf_token();
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Eliminar cuenta</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container py-4">
    <a href="profile.php" class="btn btn-secondary mb-3">← Volver</a>
    <h3>Eliminar mi cuenta</h3>

    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?=h($e)?></div><?php endforeach; ?>
    <?php if ($success): ?><div class="alert alert-success"><?=h($success)?></div><?php endif; ?>

    <div class="card p-3">
        <p class="text-muted">Eliminar tu cuenta elimina permanentemente tus datos: inscripciones, entregas, cursos que hayas creado (si eres profesor), y otros registros asociados. Esta acción es irreversible.</p>
        <form method="post" onsubmit="return confirm('¿Seguro que deseas eliminar tu cuenta? Esta acción no se puede deshacer.');">
        <input type="hidden" name="csrf_token" value="<?=h($csrf)?>">
        <div class="mb-3">
            <label class="form-label">Para confirmar, escribe tu correo</label>
            <input name="confirm_email" type="email" class="form-control" placeholder="Tu correo" required>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-danger">Eliminar mi cuenta</button>
            <a class="btn btn-secondary" href="profile.php">Cancelar</a>
        </div>
        </form>
    </div>
</div>
</body></html>