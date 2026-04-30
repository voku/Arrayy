<?php

\error_reporting(\E_ALL);
\ini_set('display_errors', '1');

$buildLogsDir = __DIR__ . '/../build/logs';
if (!\is_dir($buildLogsDir)) {
    \mkdir($buildLogsDir, 0777, true);
}

require_once __DIR__ . '/../vendor/autoload.php';
