<?php
require_once __DIR__ . '/app/helpers.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$editing = $id > 0;
$user = ['nombre_completo' => '', 'usuario' => '', 'rol' => 'USUARIO', 'grado_num' => '', 'grupo' => ''];
if ($editing) {
    $stmt = db()->prepare('SELECT * FROM usuarios WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash('danger', 'Usuario no encontrado.');
        redirect('users.php');
    }
    $user = $found;
    if (str_contains((string)$found['grado'], '-')) {
        [$user['grado_num'], $user['grupo']] = array_map('trim', explode('-', $found['grado'], 2));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = sanitize_name($_POST['nombre_completo'] ?? '');
    $username = trim($_POST['usuario'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $role = $_POST['rol'] ?? '';
    $grade = '';

    if ($role !== 'administrador') {
        $gradeNum = $_POST['grado_num'] ?? '';
        $group = strtoupper(preg_replace('/[^A-Za-z]/', '', $_POST['grupo'] ?? ''));
        $grade = $gradeNum . ' - ' . $group;
        if (!in_array($gradeNum, ['1', '2', '3'], true) || strlen($group) !== 1) {
            flash('warning', 'El grado y grupo son obligatorios para usuarios docentes.');
            redirect($_SERVER['REQUEST_URI']);
        }
    }

    if ($name === '' || $username === '' || $role === '' || (!$editing && $password === '')) {
        flash('warning', 'Por favor completa todos los campos antes de guardar.');
        redirect($_SERVER['REQUEST_URI']);
    }

    if ($editing) {
        if ($password !== '') {
            $stmt = db()->prepare('UPDATE usuarios SET nombre_completo=?, usuario=?, password_hash=?, rol=?, grado=? WHERE id=?');
            $stmt->execute([$name, $username, password_hash($password, PASSWORD_DEFAULT), $role, $grade, $id]);
        } else {
            $stmt = db()->prepare('UPDATE usuarios SET nombre_completo=?, usuario=?, rol=?, grado=? WHERE id=?');
            $stmt->execute([$name, $username, $role, $grade, $id]);
        }
        flash('success', 'Usuario actualizado correctamente.');
    } else {
        $stmt = db()->prepare('INSERT INTO usuarios (nombre_completo, usuario, password_hash, rol, grado) VALUES (?,?,?,?,?)');
        $stmt->execute([$name, $username, password_hash($password, PASSWORD_DEFAULT), $role, $grade]);
        flash('success', 'Usuario agregado correctamente.');
    }
    redirect('users.php');
}
require __DIR__ . '/views/header.php';
?>
<div class="panel">
    <h1 class="h4 mb-3"><?= $editing ? 'Modificar usuario' : 'Agregar usuario' ?></h1>
    <form method="post" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="col-md-6"><label class="form-label">Nombre completo</label><input name="nombre_completo" class="form-control" required value="<?= e($user['nombre_completo']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Usuario</label><input name="usuario" class="form-control" required value="<?= e($user['usuario']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Contrasena <?= $editing ? '(dejar vacia para conservar)' : '' ?></label><input type="password" name="password" class="form-control" <?= $editing ? '' : 'required' ?>></div>
        <div class="col-md-6"><label class="form-label">Rol</label><select name="rol" class="form-select" data-role-select="#gradeFields"><option value="administrador" <?= ($user['rol'] ?? '') === 'administrador' ? 'selected' : '' ?>>administrador</option><option value="usuario" <?= ($user['rol'] ?? '') === 'usuario' || ($user['rol'] ?? '') === 'USUARIO' ? 'selected' : '' ?>>usuario</option></select></div>
        <div class="col-12" id="gradeFields">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Grado</label><select name="grado_num" class="form-select"><option value="">Selecciona</option><?php foreach (['1','2','3'] as $g): ?><option <?= ($user['grado_num'] ?? '') === $g ? 'selected' : '' ?>><?= $g ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label class="form-label">Grupo</label><input name="grupo" maxlength="1" class="form-control text-uppercase" value="<?= e($user['grupo'] ?? '') ?>"></div>
            </div>
        </div>
        <div class="col-12 d-flex gap-2"><button class="btn btn-primary">Guardar</button><a class="btn btn-secondary" href="users.php">Cerrar</a></div>
    </form>
</div>
<?php require __DIR__ . '/views/footer.php'; ?>

