<?php
declare(strict_types=1);

const APP_NAME = 'SIGADEA';

// Configuración para Render usando variables de entorno.
// Si no existen, usa los datos de Railway que configuraste.
define('DB_HOST', getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'zephyr.proxy.rlwy.net');
define('DB_NAME', getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'railway');
define('DB_USER', getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: getenv('MYSQLPASSWORD') ?: 'dGkSwdqkiqDQkqDLquJWZANVYVnZVIjZ');
define('DB_PORT', (int)(getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: 11902));
define('DB_CHARSET', 'utf8mb4');

define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'documents');
define('BACKUP_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups');
define('REPORT_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'reports');

date_default_timezone_set('America/Mexico_City');
