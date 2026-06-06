<?php
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/backup.php';

check_scheduled_backup();
echo "SIGADEA backup check: " . date('Y-m-d H:i:s') . PHP_EOL;

