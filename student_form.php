<?php
require_once __DIR__ . '/app/helpers.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$editing = $id > 0;
$student = ['nombre' => '', 'apellido_p' => '', 'apellido_m' => '', 'turno' => 'VESPERTINO', 'grado' => current_user()['grado'], 'acta' => '', 'certificado' => '', 'comp_domicilio' => '', 'curp' => ''];
if ($editing) {
    $stmt = db()->prepare('SELECT * FROM status WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found || (!is_admin() && $found['grado'] !== current_user()['grado'])) {
        flash('danger', 'Alumno no encontrado.');
        redirect('students.php');
    }
    $student = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $nombre = sanitize_name($_POST['nombre'] ?? '');
    $ap = sanitize_name($_POST['apellido_p'] ?? '');
    $am = sanitize_name($_POST['apellido_m'] ?? '');
    $turno = 'VESPERTINO';
    $grado = is_admin() ? trim($_POST['grado'] ?? '') : current_user()['grado'];
    if ($nombre === '' || $ap === '' || $am === '' || $grado === '') {
        flash('warning', 'Por favor completa todos los datos personales.');
        redirect($_SERVER['REQUEST_URI']);
    }
    $fullName = $nombre . ' ' . $ap . ' ' . $am;
    try {
        $acta = upload_pdf('acta', current_user()['nombre_completo'], $grado, $fullName, 'ACTA', $student['acta'] ?? '');
        $certificado = upload_pdf('certificado', current_user()['nombre_completo'], $grado, $fullName, 'CERTIFICADO', $student['certificado'] ?? '');
        $comp = upload_pdf('comp_domicilio', current_user()['nombre_completo'], $grado, $fullName, 'COMPROBANTE', $student['comp_domicilio'] ?? '');
        $curp = upload_pdf('curp', current_user()['nombre_completo'], $grado, $fullName, 'CURP', $student['curp'] ?? '');
        if ($editing) {
            $stmt = db()->prepare('UPDATE status SET nombre=?, apellido_p=?, apellido_m=?, turno=?, grado=?, acta=?, certificado=?, comp_domicilio=?, curp=? WHERE id=?');
            $stmt->execute([$nombre, $ap, $am, $turno, $grado, $acta, $certificado, $comp, $curp, $id]);
            flash('success', 'Registro actualizado correctamente.');
        } else {
            $stmt = db()->prepare('INSERT INTO status (docente_id, nombre, apellido_p, apellido_m, turno, grado, acta, certificado, comp_domicilio, curp) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([current_user()['id'], $nombre, $ap, $am, $turno, $grado, $acta, $certificado, $comp, $curp]);
            flash('success', 'Alumno registrado con documentos.');
        }
        redirect('students.php');
    } catch (Throwable $e) {
        flash('danger', $e->getMessage());
        redirect($_SERVER['REQUEST_URI']);
    }
}
require __DIR__ . '/views/header.php';
?>
<div class="panel">
    <h1 class="h4 mb-3"><?= $editing ? 'Editar alumno' : 'Registrar alumno' ?></h1>
    <form method="post" enctype="multipart/form-data" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="col-md-4"><label class="form-label">Nombre</label><input class="form-control" name="nombre" required value="<?= e($student['nombre']) ?>"></div>
        <div class="col-md-4"><label class="form-label">Apellido paterno</label><input class="form-control" name="apellido_p" required value="<?= e($student['apellido_p']) ?>"></div>
        <div class="col-md-4"><label class="form-label">Apellido materno</label><input class="form-control" name="apellido_m" required value="<?= e($student['apellido_m']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Turno</label><input class="form-control" value="VESPERTINO" readonly></div>
        <div class="col-md-6"><label class="form-label">Grado</label><input class="form-control" name="grado" value="<?= e($student['grado']) ?>" <?= is_admin() ? '' : 'readonly' ?>></div>
        <?php foreach (['acta' => 'Acta', 'certificado' => 'Certificado', 'comp_domicilio' => 'Comprobante domicilio', 'curp' => 'CURP'] as $field => $label): ?>
            <div class="col-md-6">
                <label class="form-label"><?= e($label) ?> (PDF)</label>
                <input type="file" class="form-control" name="<?= e($field) ?>" accept="application/pdf">
                <div class="form-text <?= document_uploaded($student[$field] ?? '') ? 'text-success' : 'text-muted' ?>">
                    <?= document_uploaded($student[$field] ?? '') ? 'Documento cargado' : 'Documento aun no cargado' ?>
                    <?php if (document_uploaded($student[$field] ?? '')): ?>
                        · <a href="<?= e($student[$field]) ?>" target="_blank">Ver</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="col-12 d-flex gap-2"><button class="btn btn-primary">Guardar</button><a class="btn btn-secondary" href="students.php">Regresar</a></div>
    </form>
</div>
<?php require __DIR__ . '/views/footer.php'; ?>
