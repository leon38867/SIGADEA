<?php
require_once __DIR__ . '/app/helpers.php';
require_login();

$pdo = db();
$filter = $_GET['filter'] ?? '';
$q = trim($_GET['q'] ?? '');
$params = [];
$sql = 'SELECT * FROM status';
$conditions = [];
if (!is_admin()) {
    $conditions[] = 'grado = ?';
    $params[] = current_user()['grado'];
}
if ($q !== '') {
    $conditions[] = "CONCAT(nombre,' ',apellido_p,' ',apellido_m) LIKE ?";
    $params[] = '%' . $q . '%';
}
if ($conditions) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();
if (in_array($filter, ['faltantes', 'entregados'], true)) {
    $students = array_values(array_filter($students, fn($s) => $filter === 'entregados' ? student_is_complete($s) : !student_is_complete($s)));
}
require __DIR__ . '/views/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div><h1 class="h3 mb-0">Alumnos y documentos</h1><p class="text-muted mb-0">Grado: <?= e(is_admin() ? 'Todos' : current_user()['grado']) ?></p></div>
    <a class="btn btn-primary" href="student_form.php">Registrar alumno</a>
</div>
<div class="panel">
    <form class="row g-2 mb-3">
        <div class="col-md-5"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Buscar por nombre completo"></div>
        <div class="col-md-4">
            <select class="form-select" name="filter">
                <option value="">-- Sin filtro --</option>
                <option value="faltantes" <?= $filter === 'faltantes' ? 'selected' : '' ?>>Por documentos faltantes</option>
                <option value="entregados" <?= $filter === 'entregados' ? 'selected' : '' ?>>Por documentos entregados</option>
                <option value="general" <?= $filter === 'general' ? 'selected' : '' ?>>Reporte general</option>
            </select>
        </div>
        <div class="col-md-3"><button class="btn btn-outline-secondary w-100">Aplicar</button></div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Nombre</th><th>Turno</th><th>Grado</th><th>Acta</th><th>Certificado</th><th>Comprobante</th><th>CURP</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($students as $s): ?>
                <tr>
                    <td><?= e($s['nombre'] . ' ' . $s['apellido_p'] . ' ' . $s['apellido_m']) ?></td>
                    <td><?= e($s['turno']) ?></td>
                    <td><?= e($s['grado']) ?></td>
                    <?php foreach (['acta' => 'Acta','certificado' => 'Certificado','comp_domicilio' => 'Comprobante','curp' => 'CURP'] as $field => $label): ?>
                        <td class="doc-status <?= document_uploaded($s[$field]) ? 'doc-ok' : 'doc-missing' ?>">
                            <?php if (document_uploaded($s[$field])): ?>
                                <div class="doc-cell">
                                    <span>Documento cargado</span>
                                    <button
                                        class="btn btn-sm btn-outline-success"
                                        type="button"
                                        data-document-preview
                                        data-document-url="<?= e($s[$field]) ?>"
                                        data-document-title="<?= e($label . ' - ' . $s['nombre'] . ' ' . $s['apellido_p'] . ' ' . $s['apellido_m']) ?>">
                                        Ver documento
                                    </button>
                                </div>
                            <?php else: ?>
                                Documento aun no cargado
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="student_form.php?id=<?= (int)$s['id'] ?>">Editar</a>
                        <a class="btn btn-sm btn-outline-danger" data-confirm="Seguro que deseas eliminar este alumno?" href="student_delete.php?id=<?= (int)$s['id'] ?>&csrf=<?= e(csrf_token()) ?>">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-lg-down">
        <div class="modal-content document-preview-modal">
            <div class="modal-header">
                <h2 class="modal-title h5" id="documentPreviewTitle">Vista previa</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="document-toolbar">
                    <div class="btn-group" role="group" aria-label="Controles de zoom">
                        <button class="btn btn-outline-secondary" type="button" data-document-zoom="out">-</button>
                        <button class="btn btn-outline-secondary document-zoom-label" type="button" data-document-zoom="reset">100%</button>
                        <button class="btn btn-outline-secondary" type="button" data-document-zoom="in">+</button>
                    </div>
                    <div class="document-toolbar-actions">
                        <a class="btn btn-outline-secondary" href="#" target="_blank" rel="noopener" data-document-open>Abrir</a>
                        <a class="btn btn-primary" href="#" download data-document-download>Descargar</a>
                    </div>
                </div>
                <div class="document-preview-frame">
                    <iframe title="Vista previa del documento" data-document-frame></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/views/footer.php'; ?>
