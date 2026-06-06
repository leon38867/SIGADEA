<?php
require_once __DIR__ . '/app/helpers.php';
require_login();
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf'] ?? '')) {
    exit('Token CSRF invalido.');
}
$id = (int)($_GET['id'] ?? 0);
if (is_admin()) {
    $stmt = db()->prepare('DELETE FROM status WHERE id = ?');
    $stmt->execute([$id]);
} else {
    $stmt = db()->prepare('DELETE FROM status WHERE id = ? AND grado = ?');
    $stmt->execute([$id, current_user()['grado']]);
}
flash('success', 'Registro eliminado correctamente.');
redirect('students.php');

