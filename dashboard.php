<?php
require_once __DIR__ . '/app/helpers.php';
require_login();

$pdo = db();
$totalUsers = (int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
$totalStudents = (int)$pdo->query('SELECT COUNT(*) FROM status')->fetchColumn();
$where = is_admin() ? '' : ' WHERE grado = ' . $pdo->quote(current_user()['grado']);
$students = $pdo->query('SELECT * FROM status' . $where)->fetchAll();
$complete = 0;
foreach ($students as $student) {
    if (student_is_complete($student)) {
        $complete++;
    }
}
$missing = count($students) - $complete;
require __DIR__ . '/views/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Dashboard</h1>
        <p class="text-muted mb-0">Resumen general de SIGADEA</p>
    </div>
</div>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card stat-card bg-green"><div class="card-body"><div class="fs-2 fw-bold"><?= $totalStudents ?></div><div>Alumnos registrados</div></div></div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card bg-blue"><div class="card-body"><div class="fs-2 fw-bold"><?= $complete ?></div><div>Expedientes completos</div></div></div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card bg-dark-soft"><div class="card-body"><div class="fs-2 fw-bold"><?= is_admin() ? $totalUsers : $missing ?></div><div><?= is_admin() ? 'Usuarios del sistema' : 'Expedientes faltantes' ?></div></div></div>
    </div>
</div>
<?php require __DIR__ . '/views/footer.php'; ?>

