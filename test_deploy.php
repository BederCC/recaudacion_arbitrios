<?php
// test_deploy.php - Asistente de Verificación de Despliegue en Servidor
require_once 'includes/config.php';

$checks = [];

// 1. Versión de PHP
$phpVersion = phpversion();
$phpOk = version_compare($phpVersion, '7.4.0', '>=');
$checks['PHP Version'] = [
    'titulo' => 'Versión de PHP',
    'valor'  => $phpVersion,
    'estado' => $phpOk ? 'ok' : 'error',
    'detalle' => $phpOk ? 'Compatible (>= 7.4)' : 'Se recomienda PHP 7.4, 8.0, 8.1 o 8.2'
];

// 2. Extensiones Requeridas
$reqExts = ['pdo', 'pdo_mysql', 'session', 'json', 'mbstring'];
$missingExts = [];
foreach ($reqExts as $ext) {
    if (!extension_loaded($ext)) {
        $missingExts[] = $ext;
    }
}
$checks['Extensiones PHP'] = [
    'titulo' => 'Extensiones de PHP (PDO, MySQL, Session, etc.)',
    'valor'  => empty($missingExts) ? 'Todas instaladas' : 'Faltan: ' . implode(', ', $missingExts),
    'estado' => empty($missingExts) ? 'ok' : 'error',
    'detalle' => empty($missingExts) ? 'Módulos requeridos activos' : 'Activa estas extensiones en PHP Selector de cPanel'
];

// 3. BASE_URL Resuelta
$checks['BASE_URL'] = [
    'titulo' => 'URL Base Detectada (BASE_URL)',
    'valor'  => BASE_URL,
    'estado' => 'ok',
    'detalle' => 'Enrutamiento dinámico activo para ' . htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost')
];

// 4. Base de Datos
$dbStatus = 'error';
$dbMessage = '';
$tableCount = 0;
$adminExists = false;
$pdoInstance = null;

try {
    // Intentar cargar db sin abortar
    // 1. Credenciales de Localhost (XAMPP)
    $local_host = 'localhost';
    $local_db   = 'recaudacion_arbitrios';
    $local_user = 'root';
    $local_pass = '';
    $local_port = '3306';

    // 2. Credenciales de Hostinger
    $hostinger_host = 'localhost';
    $hostinger_db   = 'uXXXXXXXXX_arbitrios';
    $hostinger_user = 'uXXXXXXXXX_user';
    $hostinger_pass = 'TU_CONTRASEÑA_AQUI';
    $hostinger_port = '3306';

    // Leer valores de includes/db.php si ya los editó
    $dbPhpContent = file_get_contents(__DIR__ . '/includes/db.php');
    if (preg_match('/\$hostinger_host\s*=\s*[\'"](.*?)[\'"]/', $dbPhpContent, $m)) $hostinger_host = $m[1];
    if (preg_match('/\$hostinger_db\s*=\s*[\'"](.*?)[\'"]/', $dbPhpContent, $m)) $hostinger_db = $m[1];
    if (preg_match('/\$hostinger_user\s*=\s*[\'"](.*?)[\'"]/', $dbPhpContent, $m)) $hostinger_user = $m[1];
    if (preg_match('/\$hostinger_pass\s*=\s*[\'"](.*?)[\'"]/', $dbPhpContent, $m)) $hostinger_pass = $m[1];

    $httpHost = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]);
    $isLocalhost = in_array($httpHost, ['localhost', '127.0.0.1', '::1'])
                   || (php_sapi_name() === 'cli' && empty(getenv('REMOTE_ADDR')));

    if ($isLocalhost) {
        $host = $local_host;
        $db   = $local_db;
        $user = $local_user;
        $pass = $local_pass;
        $port = $local_port;
        $entorno = 'Localhost (XAMPP)';
    } else {
        $host = $hostinger_host;
        $db   = $hostinger_db;
        $user = $hostinger_user;
        $pass = $hostinger_pass;
        $port = $hostinger_port;
        $entorno = 'Hostinger (' . ($httpHost ?: 'arbitrios.beder.site') . ')';
    }

    $localConfigFile = __DIR__ . '/includes/db.local.php';
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
        $envFile = __DIR__ . '/.env';
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

    $host = getenv('DB_HOST') ?: $host;
    $db   = getenv('DB_DATABASE') ?: (getenv('DB_NAME') ?: $db);
    $user = getenv('DB_USERNAME') ?: (getenv('DB_USER') ?: $user);
    $pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : $pass);
    $port = getenv('DB_PORT') ?: $port;

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdoInstance = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $dbStatus = 'ok';
    $dbMessage = "Conexión exitosa a MySQL ($host:$port -> $db como $user)";

    // Verificar tablas
    $stmtTables = $pdoInstance->query("SHOW TABLES");
    $tables = $stmtTables->fetchAll(PDO::FETCH_COLUMN);
    $tableCount = count($tables);

    // Verificar usuario admin
    if (in_array('usuarios', $tables)) {
        $stmtAdmin = $pdoInstance->query("SELECT COUNT(*) FROM usuarios WHERE usuario = 'admin' AND estado = 'ACTIVO'");
        $adminExists = (int)$stmtAdmin->fetchColumn() > 0;
    }
} catch (Exception $e) {
    $dbStatus = 'error';
    $dbMessage = $e->getMessage();
}

$checks['Conexión MySQL'] = [
    'titulo' => 'Conexión a Base de Datos MySQL',
    'valor'  => $dbStatus === 'ok' ? 'Conectado Exitosamente' : 'Fallo de Conexión',
    'estado' => $dbStatus,
    'detalle' => $dbMessage
];

if ($dbStatus === 'ok') {
    $checks['Tablas del Sistema'] = [
        'titulo' => 'Estructura de Tablas (db.sql)',
        'valor'  => "$tableCount tablas encontradas",
        'estado' => $tableCount >= 10 ? 'ok' : 'warning',
        'detalle' => $tableCount >= 10 ? 'Todas las tablas requeridas presentes' : 'Pocas tablas. ¿Importaste db.sql en phpMyAdmin?'
    ];

    $checks['Usuario Administrador'] = [
        'titulo' => 'Cuenta de Administrador (usuario: admin)',
        'valor'  => $adminExists ? 'Usuario Activo Encontrado' : 'No se encontró el usuario admin',
        'estado' => $adminExists ? 'ok' : 'warning',
        'detalle' => $adminExists ? 'Listo para iniciar sesión' : 'Ejecuta reset_admin.php para crear/restablecer la contraseña'
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Despliegue - SART</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; background: #0F172A; color: #1E293B; padding: 30px 15px; }
        .container { max-width: 760px; margin: 0 auto; }
        .card { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.3); border: 1px solid #334155; }
        .header { background: linear-gradient(135deg, #1E3A8A, #0F172A); color: white; padding: 26px; text-align: center; }
        .header h1 { font-size: 22px; font-weight: 800; }
        .header p { color: #94A3B8; font-size: 13px; margin-top: 4px; }
        .body { padding: 26px; }
        .item { display: flex; align-items: flex-start; justify-content: space-between; padding: 14px 16px; border-radius: 10px; margin-bottom: 12px; border: 1px solid; }
        .item-ok { background: #F0FDF4; border-color: #BBF7D0; }
        .item-warning { background: #FEFCE8; border-color: #FEF08A; }
        .item-error { background: #FEF2F2; border-color: #FECACA; }
        .icon { font-size: 20px; line-height: 1; margin-right: 12px; }
        .info { flex: 1; }
        .title { font-weight: 700; font-size: 14px; color: #0F172A; }
        .value { font-size: 13px; font-family: monospace; font-weight: 600; margin-top: 2px; }
        .desc { font-size: 12px; color: #64748B; margin-top: 2px; }
        .actions { margin-top: 24px; display: flex; gap: 12px; justify-content: space-between; align-items: center; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 22px; border-radius: 8px; font-weight: 700; font-size: 13px; text-decoration: none; transition: 0.2s; }
        .btn-blue { background: #2563EB; color: white; }
        .btn-blue:hover { background: #1D4ED8; }
        .btn-dark { background: #334155; color: white; }
        .btn-dark:hover { background: #1E293B; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>🔍 Diagnóstico de Servidor y Despliegue</h1>
                <p>Sistema SART - Servidor: <?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost'); ?></p>
            </div>
            <div class="body">
                <?php foreach ($checks as $c): ?>
                    <div class="item item-<?php echo $c['estado']; ?>">
                        <span class="icon">
                            <?php 
                            if ($c['estado'] === 'ok') echo '✅';
                            elseif ($c['estado'] === 'warning') echo '⚠️';
                            else echo '❌';
                            ?>
                        </span>
                        <div class="info">
                            <div class="title"><?php echo htmlspecialchars($c['titulo']); ?></div>
                            <div class="value"><?php echo htmlspecialchars($c['valor']); ?></div>
                            <div class="desc"><?php echo htmlspecialchars($c['detalle']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="actions">
                    <a href="<?php echo url('index.php'); ?>" class="btn btn-dark">← Ir al Portal Vecino</a>
                    <a href="<?php echo url('admin/login.php'); ?>" class="btn btn-blue">🔐 Ir al Login Admin →</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
