<?php $user = current_user(); ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php if ($user): ?>
<div class="app-shell">
    <?php require __DIR__ . '/sidebar.php'; ?>
    <main class="app-main">
        <nav class="topbar">
            <button class="btn btn-outline-secondary d-lg-none" id="sidebarToggle">Menu</button>
            <div>
                <strong><?= e(APP_NAME) ?></strong>
                <span class="text-muted ms-2">Sistema de Gestion y Administracion Escolar de Documentos</span>
            </div>
            <div class="small text-end">
                <div><?= e($user['nombre_completo']) ?></div>
                <span class="badge text-bg-success"><?= e($user['rol']) ?></span>
            </div>
        </nav>
        <section class="content">
<?php endif; ?>
<?php foreach (flashes() as $flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endforeach; ?>

