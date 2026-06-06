<?php
require_once __DIR__ . '/app/helpers.php';
require_admin();
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf'] ?? '')) {
    exit('Token CSRF invalido.');
}
$id = (int)($_GET['id'] ?? 0);
if ($id === (int)current_user()['id']) {
    flash('warning', 'No puedes eliminar tu propio usuario.');
    redirect('users.php');
}
$stmt = db()->prepare('DELETE FROM usuarios WHERE id = ?');
$stmt->execute([$id]);
flash('success', 'Usuario eliminado correctamente.');
redirect('users.php');

