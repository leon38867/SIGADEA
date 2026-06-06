<?php
require_once __DIR__ . '/app/helpers.php';
require_admin();

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $stmt = db()->prepare('SELECT * FROM usuarios WHERE nombre_completo LIKE ? ORDER BY id DESC');
    $stmt->execute(['%' . $q . '%']);
    $users = $stmt->fetchAll();
} else {
    $users = db()->query('SELECT * FROM usuarios ORDER BY id DESC')->fetchAll();
}
require __DIR__ . '/views/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div><h1 class="h3 mb-0">Control de usuarios</h1><p class="text-muted mb-0">Administradores y docentes</p></div>
    <a class="btn btn-primary" href="user_form.php">Agregar usuario</a>
</div>
<div class="panel">
    <form class="row g-2 mb-3">
        <div class="col-md-8"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Buscar por nombre completo"></div>
        <div class="col-md-4"><button class="btn btn-outline-secondary w-100">Buscar</button></div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Rol</th><th>Grado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= (int)$user['id'] ?></td>
                    <td><?= e($user['nombre_completo']) ?></td>
                    <td><?= e($user['usuario']) ?></td>
                    <td><?= e($user['rol']) ?></td>
                    <td><?= e($user['grado']) ?></td>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="user_form.php?id=<?= (int)$user['id'] ?>">Modificar</a>
                        <a class="btn btn-sm btn-outline-danger" data-confirm="Seguro que deseas eliminar este usuario?" href="user_delete.php?id=<?= (int)$user['id'] ?>&csrf=<?= e(csrf_token()) ?>">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/views/footer.php'; ?>

