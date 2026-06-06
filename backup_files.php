<?php
require_once __DIR__ . '/app/helpers.php';
require_admin();

$files = [];
if (is_dir(BACKUP_PATH)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(BACKUP_PATH, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.zip')) {
            $files[] = $file->getPathname();
        }
    }
}
rsort($files);

if (isset($_GET['download'])) {
    $requested = realpath(BACKUP_PATH . DIRECTORY_SEPARATOR . $_GET['download']);
    if (!$requested || !str_starts_with($requested, realpath(BACKUP_PATH)) || !is_file($requested)) {
        http_response_code(404);
        exit('Backup no encontrado.');
    }
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($requested) . '"');
    readfile($requested);
    exit;
}

require __DIR__ . '/views/header.php';
?>
<div class="panel">
    <h1 class="h4 mb-3">Backups generados</h1>
    <?php if (!$files): ?>
        <div class="alert alert-info">Aun no se crean copias de seguridad.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Archivo</th><th>Mes</th><th>Fecha</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($files as $path): ?>
                    <?php $relative = str_replace('\\', '/', substr($path, strlen(BACKUP_PATH) + 1)); ?>
                    <tr>
                        <td><?= e(basename($path)) ?></td>
                        <td><?= e(basename(dirname($path))) ?></td>
                        <td><?= e(date('d/m/Y H:i', filemtime($path))) ?></td>
                        <td><a class="btn btn-sm btn-outline-primary" href="backup_files.php?download=<?= urlencode($relative) ?>">Descargar</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/views/footer.php'; ?>

