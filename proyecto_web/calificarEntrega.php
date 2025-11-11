<?php
session_start();
require_once __DIR__ . '/config_database.php';
require_once __DIR__ . '/helper.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') die('Método no permitido.');

$entrega_id = intval($_POST['entrega_id'] ?? 0);
$calificacion = $_POST['calificacion'] ?? null;
$comentario = trim($_POST['comentario'] ?? '');
$usuario_id = intval($_SESSION['usuario_id']);
$rol = $_SESSION['rol_nombre'] ?? '';

if ($entrega_id <= 0) die('Entrega inválida.');

$stmt = $pdo->prepare("SELECT e.tarea_id, t.curso_id, c.creador_id FROM entregas e JOIN tareas t ON e.tarea_id = t.id JOIN cursos c ON t.curso_id = c.id WHERE e.id = ?");
$stmt->execute([$entrega_id]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$info) die('No encontrada.');

if ($rol !== 'admin' && intval($info['creador_id']) !== $usuario_id) die('No tienes permiso.');

if ($calificacion === null || $calificacion === '') {
    $cal = null;
} else {
    $cal = floatval($calificacion);
    if ($cal < 0) $cal = 0;
    if ($cal > 100) $cal = 100;
}

try {
    $upd = $pdo->prepare("UPDATE entregas SET calificacion = ?, comentario = ? WHERE id = ?");
    $upd->execute([$cal, $comentario, $entrega_id]);
    header('Location: listar_entregas.php?tarea_id=' . intval($info['tarea_id']));
    exit;
} catch (PDOException $e) {
    die('Error al guardar: ' . $e->getMessage());
}
