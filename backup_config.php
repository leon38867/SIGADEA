<?php
require_once __DIR__ . '/app/helpers.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    save_backup_config($_POST['day'] ?? '', (int)($_POST['hour'] ?? -1));
    flash('success', 'Configuracion de backup guardada correctamente.');
    redirect('backup_config.php');
}

if (isset($_GET['run']) && hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf'] ?? '')) {
    create_backup_zip();
    flash('success', 'Copia de seguridad creada correctamente.');
    redirect('backup_config.php');
}

$config = read_backup_config();
require __DIR__ . '/views/header.php';
?>
<div class="panel">
    <h1 class="h4">Copias de seguridad</h1>
    <p class="text-muted">Aqui se configura el backup automatico semanal del sistema.</p>
    <form method="post" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="col-md-6">
            <label class="form-label">Dia</label>
            <select class="form-select" name="day" required>
                <option value="">Selecciona</option>
                <?php foreach (backup_days() as $day): ?>
                    <option value="<?= e($day) ?>" <?= ($config['day'] ?? '') === $day ? 'selected' : '' ?>><?= e($day) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Hora</label>
            <select class="form-select" name="hour" required>
                <option value="">Selecciona</option>
                <?php foreach (backup_hours() as $hour => $label): ?>
                    <option value="<?= (int)$hour ?>" <?= (int)($config['hour'] ?? -1) === (int)$hour ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 d-flex flex-wrap gap-2">
            <button class="btn btn-primary">Aplicar</button>
            <?php if (has_backups()): ?>
                <a class="btn btn-outline-secondary" href="backup_files.php">Ver backup</a>
            <?php else: ?>
                <button class="btn btn-outline-secondary" type="button" onclick="alert('Aun no se crean copias de seguridad.')">Ver backup</button>
            <?php endif; ?>
            <a class="btn btn-outline-primary" data-confirm="Crear una copia de seguridad ahora?" href="backup_config.php?run=1&csrf=<?= e(csrf_token()) ?>">Crear ahora</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/views/footer.php'; ?>
