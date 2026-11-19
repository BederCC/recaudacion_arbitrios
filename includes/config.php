<?php
// includes/config.php

// 1. Error Reporting / Debug helper
$appDebug = false;
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    if (preg_match('/APP_DEBUG\s*=\s*(true|1)/i', $envContent)) {
        $appDebug = true;
    }
}
if ($appDebug || (isset($_GET['debug']) && $_GET['debug'] === 'sart2026')) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

// 2. CORS Headers
if (!headers_sent()) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
}

// Handle preflight requests
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 3. Dynamic Base URL Definition
if (!defined('BASE_URL')) {
    $customBaseUrl = null;
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, '#') === 0) continue;
            if (strpos($line, 'BASE_URL=') === 0) {
                $customBaseUrl = trim(substr($line, strlen('BASE_URL=')));
                $customBaseUrl = trim($customBaseUrl, '"\'');
                break;
            }
        }
    }

    if ($customBaseUrl) {
        define('BASE_URL', rtrim($customBaseUrl, '/') . '/');
    } else {
        $isHttps = (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
            (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
        );
        $protocol = $isHttps ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..'));
        $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));

        $subpath = '';
        if ($docRoot && strpos($projectRoot, $docRoot) === 0) {
            $relative = trim(substr($projectRoot, strlen($docRoot)), '/');
            $subpath = $relative ? '/' . $relative : '';
        } else {
            $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
            if (strpos($script, '/recaudacion_arbitrios/') !== false) {
                $subpath = '/recaudacion_arbitrios';
            }
        }

        define('BASE_URL', $protocol . "://" . $host . $subpath . '/');
    }
}

// 4. Asset Helper
function asset($path)
{
    return BASE_URL . ltrim($path, '/');
}

// 5. URL Helper
function url($path)
{
    return BASE_URL . ltrim($path, '/');
}
