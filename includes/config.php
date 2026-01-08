<?php
// includes/config.php

// 1. CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
   http_response_code(200);
   exit();
}

// 2. Base URL Definition
// Detect dynamically or hardcode. For XAMPP default:
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
// Assuming the project is in the root of htdocs/recaudacion_arbitrios
// We can try to detect the folder name.
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
// If we are in /recaudacion_arbitrios/admin/config, we want /recaudacion_arbitrios/
// Simple approach: Hardcode for this specific project structure as it's the most robust for this specific user request.
define('BASE_URL', $protocol . "://" . $host . '/recaudacion_arbitrios/');

// 3. Asset Helper
function asset($path)
{
   return BASE_URL . ltrim($path, '/');
}

// 4. URL Helper
function url($path)
{
   return BASE_URL . ltrim($path, '/');
}
