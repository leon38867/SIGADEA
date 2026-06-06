<?php
require_once __DIR__ . '/app/helpers.php';

if (current_user()) {
    redirect('dashboard.php');
}

$users = db()->query('SELECT usuario FROM usuarios ORDER BY usuario')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $usuario = trim($_POST['usuario'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($usuario === '' || $password === '') {
        flash('warning', 'Por favor selecciona el usuario e ingresa la contrasena.');
        redirect('index.php');
    }

    $stmt = db()->prepare('SELECT * FROM usuarios WHERE usuario = ? LIMIT 1');
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $user['id'],
            'nombre_completo' => $user['nombre_completo'],
            'usuario' => $user['usuario'],
            'rol' => $user['rol'],
            'grado' => $user['grado'],
        ];
        flash('success', strtolower($user['rol']) === 'administrador' ? 'Bienvenido Administrador.' : 'Bienvenido Usuario del grado: ' . $user['grado']);
        redirect('dashboard.php');
    }

    flash('danger', 'Usuario o contrasena incorrectos.');
    redirect('index.php');
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIGADEA - Acceso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
<div class="login-card">
    <h1 class="h3 mb-1">SIGADEA</h1>
    <p class="text-muted mb-4">(SIGADEA) Sistema de Gestion y Administracion Escolar de Documentos TELESECUNDARIA BELISARIO DOMINGUEZ MATLAPA SLP.</p>
    <?php foreach (flashes() as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>
    <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="mb-3">
            <label class="form-label">Usuario</label>
            <select name="usuario" class="form-select" required>
                <option value="">Selecciona un usuario</option>
                <?php foreach ($users as $row): ?>
                    <option value="<?= e($row['usuario']) ?>"><?= e($row['usuario']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Contrasena</label>
            <div class="input-group password-toggle">
                <input type="password" name="password" class="form-control" id="passwordInput" required>
                <button class="btn btn-outline-secondary" type="button" data-password-toggle="#passwordInput" aria-label="Ver contrasena">Ver</button>
            </div>
        </div>
        <button class="btn btn-primary w-100" type="submit">ACEPTAR</button>
    </form>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
