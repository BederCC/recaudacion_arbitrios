<?php
// includes/db.php

// =========================================================================
// 1. CREDENCIALES DE LOCALHOST (XAMPP / Desarrollo Local)
// =========================================================================
$local_host = 'localhost';
$local_db   = 'recaudacion_arbitrios';
$local_user = 'root';
$local_pass = '';
$local_port = '3306';

// =========================================================================
// 2. CREDENCIALES DE HOSTINGER (Producción / arbitrios.beder.site)
// Pega aquí los datos de tu Base de Datos MySQL creada en el hPanel de Hostinger:
// =========================================================================
$hostinger_host = 'localhost';             // Usualmente 'localhost' en Hostinger
$hostinger_db   = 'u226387153_arbitrio';  // Nombre de Base de Datos en Hostinger
$hostinger_user = 'u226387153_arbitrio';       // Usuario de Base de Datos en Hostinger
$hostinger_pass = 'Arbitrios12/34';    // Contraseña creada en Hostinger
$hostinger_port = '3306';

// =========================================================================
// 3. DETECCIÓN AUTOMÁTICA DEL ENTORNO (LOCALHOST vs HOSTINGER)
// =========================================================================
$httpHost = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]);
$isLocalhost = in_array($httpHost, ['localhost', '127.0.0.1', '::1'])
               || (php_sapi_name() === 'cli' && empty(getenv('REMOTE_ADDR')));

if ($isLocalhost) {
    // Activar credenciales de Localhost (XAMPP)
    $host = $local_host;
    $db   = $local_db;
    $user = $local_user;
    $pass = $local_pass;
    $port = $local_port;
    $entornoNombre = 'Localhost (XAMPP)';
} else {
    // Activar credenciales de Producción (Hostinger)
    $host = $hostinger_host;
    $db   = $hostinger_db;
    $user = $hostinger_user;
    $pass = $hostinger_pass;
    $port = $hostinger_port;
    $entornoNombre = 'Producción (Hostinger: ' . ($httpHost ?: 'arbitrios.beder.site') . ')';
}

// 4. Soporte opcional de archivo local o .env (por si prefieres usar variables de entorno)
$localConfigFile = __DIR__ . '/db.local.php';
if (file_exists($localConfigFile)) {
    $localConfig = require $localConfigFile;
    if (is_array($localConfig)) {
        $host = $localConfig['host'] ?? $host;
        $db   = $localConfig['db']   ?? ($localConfig['database'] ?? $db);
        $user = $localConfig['user'] ?? ($localConfig['username'] ?? $user);
        $pass = $localConfig['pass'] ?? ($localConfig['password'] ?? $pass);
        $port = $localConfig['port'] ?? $port;
    }
} else {
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
            list($envKey, $envVal) = explode('=', $line, 2);
            $envKey = trim($envKey);
            $envVal = trim(trim($envVal), '"\'');
            if (in_array($envKey, ['DB_HOST', 'MYSQL_HOST'])) $host = $envVal;
            if (in_array($envKey, ['DB_DATABASE', 'DB_NAME', 'MYSQL_DATABASE'])) $db = $envVal;
            if (in_array($envKey, ['DB_USERNAME', 'DB_USER', 'MYSQL_USER'])) $user = $envVal;
            if (in_array($envKey, ['DB_PASSWORD', 'DB_PASS', 'MYSQL_PASSWORD'])) $pass = $envVal;
            if (in_array($envKey, ['DB_PORT', 'MYSQL_PORT'])) $port = $envVal;
        }
    }
}

// Variables de entorno de sistema como última prioridad si existen
$host = getenv('DB_HOST') ?: $host;
$db   = getenv('DB_DATABASE') ?: (getenv('DB_NAME') ?: $db);
$user = getenv('DB_USERNAME') ?: (getenv('DB_USER') ?: $user);
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : $pass);
$port = getenv('DB_PORT') ?: $port;

$charset = 'utf8mb4';
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Si la petición proviene de un endpoint API o AJAX
    $isApi = (
        (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
        (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    );

    if ($isApi) {
        if (!headers_sent()) {
            http_response_code(500);
            header("Content-Type: application/json; charset=utf-8");
        }
        echo json_encode([
            'success' => false,
            'error'   => 'Error de conexión a la Base de Datos.',
            'entorno' => $entornoNombre,
            'detalle' => $e->getMessage(),
            'host'    => $host,
            'db'      => $db
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Pantalla visual de diagnóstico amigable en caso de error
    if (!headers_sent()) {
        http_response_code(500);
    }
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error de Conexión a Base de Datos - SART</title>
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #0F172A; color: #1E293B; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
            .card { background: #FFFFFF; max-width: 680px; width: 100%; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4); overflow: hidden; border: 1px solid #334155; }
            .header { background: linear-gradient(135deg, #1E3A8A, #0F172A); color: white; padding: 24px; text-align: center; }
            .header h1 { font-size: 20px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 6px; }
            .header p { font-size: 13px; color: #94A3B8; }
            .content { padding: 28px; font-size: 14px; line-height: 1.6; }
            .badge-env { display: inline-flex; align-items: center; gap: 6px; background: #EFF6FF; color: #1E40AF; border: 1px solid #BFDBFE; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; margin-bottom: 12px; }
            .badge-error { display: block; background: #FEF2F2; color: #B91C1C; border: 1px solid #FCA5A5; padding: 8px 12px; border-radius: 8px; font-weight: 700; font-size: 13px; margin-bottom: 16px; }
            .error-box { background: #F8FAFC; border: 1px solid #E2E8F0; border-left: 4px solid #DC2626; border-radius: 8px; padding: 14px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; color: #991B1B; margin-bottom: 20px; word-break: break-word; }
            .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; background: #F1F5F9; padding: 14px; border-radius: 10px; margin-bottom: 20px; font-size: 12px; }
            .grid div strong { color: #475569; display: block; font-size: 11px; text-transform: uppercase; }
            .grid div span { font-family: monospace; color: #0F172A; font-weight: 600; font-size: 13px; }
            .steps { background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 16px; margin-bottom: 20px; font-size: 13px; color: #166534; }
            .steps h3 { font-size: 14px; font-weight: 700; margin-bottom: 6px; color: #15803D; }
            .steps ol { margin-left: 20px; }
            .steps li { margin-bottom: 4px; }
            .code-sample { background: #1E293B; color: #E2E8F0; padding: 10px 14px; border-radius: 6px; font-family: monospace; font-size: 12px; margin: 8px 0; }
            .actions { display: flex; gap: 12px; justify-content: flex-end; }
            .btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 8px; font-weight: 700; font-size: 13px; text-decoration: none; cursor: pointer; transition: all 0.2s; border: none; }
            .btn-primary { background: #2563EB; color: white; }
            .btn-primary:hover { background: #1D4ED8; }
            .btn-secondary { background: #E2E8F0; color: #334155; }
            .btn-secondary:hover { background: #CBD5E1; }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="header">
                <h1>🏛️ SART - Municipalidad Distrital de Oropesa</h1>
                <p>Sistema de Administración de Recaudación Tributaria</p>
            </div>
            <div class="content">
                <div class="badge-env">
                    🌐 Entorno Activo: <?php echo htmlspecialchars($entornoNombre); ?>
                </div>

                <div class="badge-error">
                    ⚠️ Error de Conexión MySQL con el Servidor
                </div>

                <div class="error-box">
                    <strong>Mensaje de MySQL:</strong><br>
                    <?php echo htmlspecialchars($e->getMessage()); ?>
                </div>

                <div class="grid">
                    <div><strong>Host / Servidor:</strong> <span><?php echo htmlspecialchars($host . ':' . $port); ?></span></div>
                    <div><strong>Base de Datos:</strong> <span><?php echo htmlspecialchars($db); ?></span></div>
                    <div><strong>Usuario MySQL:</strong> <span><?php echo htmlspecialchars($user); ?></span></div>
                    <div><strong>Contraseña:</strong> <span><?php echo empty($pass) ? '(Vacía / Sin clave)' : '•••••••• (Configurada)'; ?></span></div>
                </div>

                <div class="steps">
                    <h3>💡 ¿Cómo configurar tus credenciales de Hostinger?</h3>
                    <p style="margin-bottom: 8px;">Abre el archivo <strong>includes/db.php</strong> en tu editor y completa las líneas 16 a 19 con los datos de tu hPanel:</p>
                    <div class="code-sample">
$hostinger_host = 'localhost';<br>
$hostinger_db   = 'uXXXXXXXXX_arbitrios';<br>
$hostinger_user = 'uXXXXXXXXX_user';<br>
$hostinger_pass = 'Tu_Password_De_Hostinger';
                    </div>
                    <ol>
                        <li>En tu Hostinger (hPanel) ve a <strong>Bases de datos MySQL</strong>.</li>
                        <li>Copia el <strong>Nombre de la Base de Datos</strong> y el <strong>Usuario</strong>.</li>
                        <li>Pégalos en las variables <code>$hostinger_db</code>, <code>$hostinger_user</code> y <code>$hostinger_pass</code> de <strong>includes/db.php</strong>.</li>
                        <li>¡Listo! Tu XAMPP seguirá funcionando en localhost y Hostinger en producción automáticamente.</li>
                    </ol>
                </div>

                <div class="actions">
                    <a href="test_deploy.php" class="btn btn-secondary">🔍 Diagnóstico Completo</a>
                    <button onclick="window.location.reload();" class="btn btn-primary">🔄 Probar Conexión Nuevamente</button>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 5. Funciones globales de utilidad del sistema
function jsonResponse($data, $status = 200)
{
    if (!headers_sent()) {
        header("Content-Type: application/json; charset=utf-8");
        http_response_code($status);
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getActiveFiscalYear($pdo)
{
    if (!$pdo) return null;
    $stmt = $pdo->query("SELECT * FROM anios_fiscales WHERE estado = 'ACTIVO' LIMIT 1");
    return $stmt->fetch();
}
