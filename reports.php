<?php
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/pdf.php';
require_login();

$filter = $_GET['filter'] ?? 'general';
$allowed = ['general', 'faltantes', 'entregados'];
if (!in_array($filter, $allowed, true)) {
    $filter = 'general';
}

$params = [];
$sql = 'SELECT * FROM status';
if (!is_admin()) {
    $sql .= ' WHERE grado = ?';
    $params[] = current_user()['grado'];
}
$stmt = db()->prepare($sql . ' ORDER BY apellido_p, apellido_m, nombre');
$stmt->execute($params);
$students = $stmt->fetchAll();
$filtered = array_values(array_filter($students, function ($student) use ($filter) {
    return match ($filter) {
        'faltantes' => !student_is_complete($student),
        'entregados' => student_is_complete($student),
        default => true,
    };
}));
$complete = count(array_filter($students, fn($s) => student_is_complete($s)));
$missing = count($students) - $complete;

if (isset($_GET['pdf'])) {
    if (!$filtered) {
        flash('warning', 'No hay alumnos que coincidan con el tipo de reporte seleccionado.');
        redirect('reports.php?filter=' . urlencode($filter));
    }
    if (!is_dir(REPORT_PATH)) {
        mkdir(REPORT_PATH, 0775, true);
    }
    $path = REPORT_PATH . DIRECTORY_SEPARATOR . 'Reporte_' . $filter . '_' . date('Ymd_His') . '.pdf';
    $pdf = new SimplePdf();
    $pdf->addTitle('SIGADEA - Reporte de documentos');
    $pdf->addSubtitle('Sistema de Gestion y Administracion Escolar de Documentos');
    $pdf->addLine('Docente: ' . current_user()['nombre_completo']);
    $pdf->addLine('Grado / Grupo: ' . (is_admin() ? 'Todos' : current_user()['grado']));
    $pdf->addLine('Tipo de reporte: ' . report_filter_label($filter));
    $pdf->addLine('Fecha: ' . date('d/m/Y H:i'));
    $pdf->addLine('Total en reporte: ' . count($filtered) . '    Expedientes completos: ' . $complete . '    Expedientes faltantes: ' . $missing);
    $pdf->addLine('');

    $rows = [];
    $i = 1;
    foreach ($filtered as $student) {
        $rows[] = [
            (string)$i++,
            $student['nombre'] . ' ' . $student['apellido_p'] . ' ' . $student['apellido_m'],
            $student['turno'],
            document_uploaded($student['acta']) ? 'Entregado' : 'Faltante',
            document_uploaded($student['certificado']) ? 'Entregado' : 'Faltante',
            document_uploaded($student['comp_domicilio']) ? 'Entregado' : 'Faltante',
            document_uploaded($student['curp']) ? 'Entregado' : 'Faltante',
        ];
    }
    $pdf->addTable(['#', 'Nombre', 'Turno', 'Acta', 'Certificado', 'Comprobante', 'CURP'], $rows);
    $pdf->output($path);
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($path) . '"');
    readfile($path);
    exit;
}

require __DIR__ . '/views/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div><h1 class="h3 mb-0">Reportes</h1><p class="text-muted mb-0">Vista previa y generacion PDF</p></div>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-6"><div class="card stat-card bg-green"><div class="card-body"><div class="fs-2 fw-bold"><?= $complete ?></div><div>Documentos entregados</div></div></div></div>
    <div class="col-md-6"><div class="card stat-card bg-dark-soft"><div class="card-body"><div class="fs-2 fw-bold"><?= $missing ?></div><div>Documentos faltantes</div></div></div></div>
</div>
<div class="panel">
    <form class="row g-2 mb-3">
        <div class="col-md-8">
            <select class="form-select" name="filter">
                <option value="general" <?= $filter === 'general' ? 'selected' : '' ?>>Reporte general</option>
                <option value="faltantes" <?= $filter === 'faltantes' ? 'selected' : '' ?>>Por documentos faltantes</option>
                <option value="entregados" <?= $filter === 'entregados' ? 'selected' : '' ?>>Por documentos entregados</option>
            </select>
        </div>
        <div class="col-md-4"><button class="btn btn-outline-secondary w-100">Generar por</button></div>
    </form>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0"><?= e(report_filter_label($filter)) ?></h2>
        <a class="btn btn-primary" target="_blank" href="reports.php?filter=<?= e($filter) ?>&pdf=1">Generar reporte PDF</a>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead><tr><th>#</th><th>Nombre</th><th>Acta</th><th>Certificado</th><th>Comprobante</th><th>CURP</th></tr></thead>
            <tbody>
            <?php foreach ($filtered as $i => $s): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($s['nombre'] . ' ' . $s['apellido_p'] . ' ' . $s['apellido_m']) ?></td>
                    <?php foreach (['acta','certificado','comp_domicilio','curp'] as $field): ?>
                        <td class="<?= document_uploaded($s[$field]) ? 'doc-ok' : 'doc-missing' ?>"><?= document_uploaded($s[$field]) ? 'Entregado' : 'Faltante' ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/views/footer.php'; ?>
