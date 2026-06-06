<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function backup_days(): array
{
    return ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];
}

function backup_hours(): array
{
    return [
        8 => '8:00 AM',
        9 => '9:00 AM',
        10 => '10:00 AM',
        11 => '11:00 AM',
        12 => '12:00 PM',
        13 => '1:00 PM',
        14 => '2:00 PM',
    ];
}

function backup_config_path(): string
{
    return BASE_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backup_config.json';
}

function read_backup_config(): array
{
    if (!function_exists('db')) {
        return [];
    }

    $stmt = db()->query('SELECT dia AS day, hora AS hour, ultima_ejecucion AS last_run FROM backup_settings WHERE id = 1');
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    return $data ?: [];
}

function save_backup_config(string $day, int $hour): void
{
    if (!in_array($day, backup_days(), true) || !array_key_exists($hour, backup_hours())) {
        throw new RuntimeException('Configuracion de backup invalida.');
    }
    $stmt = db()->prepare(
        'INSERT INTO backup_settings (id, dia, hora) VALUES (1, ?, ?)
         ON DUPLICATE KEY UPDATE dia = VALUES(dia), hora = VALUES(hora)'
    );
    $stmt->execute([$day, $hour]);
}

function check_scheduled_backup(): void
{
    if (!function_exists('db')) {
        return;
    }
    $config = read_backup_config();
    if (empty($config['day']) || !isset($config['hour'])) {
        return;
    }

    $days = [
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miercoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sabado',
        'Sunday' => 'Domingo',
    ];
    $today = $days[date('l')] ?? '';
    $hour = (int)date('G');
    $date = date('Y-m-d');

    if ($today !== $config['day'] || $hour !== (int)$config['hour'] || (($config['last_run'] ?? '') === $date)) {
        return;
    }

    create_backup_zip();
    $stmt = db()->prepare('UPDATE backup_settings SET ultima_ejecucion = ? WHERE id = 1');
    $stmt->execute([$date]);
}

function create_backup_zip(): string
{
    $monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $monthFolder = BACKUP_PATH . DIRECTORY_SEPARATOR . $monthNames[(int)date('n') - 1] . '_' . date('Y');
    if (!is_dir($monthFolder)) {
        mkdir($monthFolder, 0775, true);
    }

    $filename = 'SIGADEA_Backup_Programado_' . date('Ymd_His') . '.zip';
    $zipPath = $monthFolder . DIRECTORY_SEPARATOR . $filename;

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
        throw new RuntimeException('No se pudo crear el ZIP de backup.');
    }

    $zip->addFromString('BaseDatos/database_backup.sql', export_database_sql());
    add_directory_to_zip($zip, UPLOAD_PATH, 'Documentos');
    $zip->close();

    return $zipPath;
}

function export_database_sql(): string
{
    $pdo = db();
    $sql = "-- Backup SIGADEA PHP\n-- Fecha: " . date('Y-m-d H:i:s') . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        $create = $pdo->query('SHOW CREATE TABLE `' . str_replace('`', '``', $table) . '`')->fetch();
        $sql .= "DROP TABLE IF EXISTS `$table`;\n" . $create['Create Table'] . ";\n\n";

        $rows = $pdo->query('SELECT * FROM `' . str_replace('`', '``', $table) . '`')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $columns = array_map(fn($c) => '`' . str_replace('`', '``', $c) . '`', array_keys($row));
            $values = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string)$v), array_values($row));
            $sql .= 'INSERT INTO `' . $table . '` (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ");\n";
        }
        $sql .= "\n";
    }

    return $sql . "SET FOREIGN_KEY_CHECKS=1;\n";
}

function add_directory_to_zip(ZipArchive $zip, string $folder, string $prefix): void
{
    if (!is_dir($folder)) {
        return;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $relative = substr($file->getPathname(), strlen($folder) + 1);
            $zip->addFile($file->getPathname(), $prefix . '/' . str_replace('\\', '/', $relative));
        }
    }
}

function has_backups(): bool
{
    if (!is_dir(BACKUP_PATH)) {
        return false;
    }
    $files = glob(BACKUP_PATH . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'SIGADEA_Backup_*.zip');
    return !empty($files);
}
