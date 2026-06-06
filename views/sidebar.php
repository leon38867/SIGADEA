<aside class="sidebar" id="sidebar">
    <div class="brand">
        <div class="brand-icon">S</div>
        <div>
            <div class="brand-title">SIGADEA</div>
            <div class="brand-subtitle">Menu principal</div>
        </div>
    </div>
    <nav class="nav flex-column gap-1">
        <a class="nav-link" href="dashboard.php">Dashboard</a>
        <?php if (is_admin()): ?>
            <a class="nav-link" href="users.php">Control de usuarios</a>
            <a class="nav-link" href="backup_config.php">Copias de seguridad</a>
        <?php else: ?>
            <a class="nav-link" href="students.php">Alumnos y documentos</a>
            <a class="nav-link" href="reports.php">Reportes</a>
        <?php endif; ?>
        <a class="nav-link text-warning" href="logout.php">Cerrar sesion</a>
    </nav>
</aside>

