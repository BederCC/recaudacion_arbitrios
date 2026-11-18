<?php
// includes/auditoria.php
require_once __DIR__ . '/db.php';

/**
 * Asegura que la tabla auditoria_logs exista en la base de datos
 */
function asegurarTablaAuditoria($pdo)
{
    static $verificada = false;
    if ($verificada) return;

    $sql = "
    CREATE TABLE IF NOT EXISTS `auditoria_logs` (
      `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
      `usuario_id` INT(11) DEFAULT NULL,
      `usuario_nombre` VARCHAR(100) DEFAULT NULL,
      `modulo` VARCHAR(50) NOT NULL COMMENT 'CATASTRO, EMISION, CAJA, CONFIG, AUTH',
      `accion` VARCHAR(50) NOT NULL COMMENT 'INSERT, UPDATE, DELETE, LOGIN, EMISION_MASIVA, COBRO_RECIBO',
      `descripcion` VARCHAR(255) DEFAULT NULL,
      `tabla_afectada` VARCHAR(50) DEFAULT NULL,
      `registro_id` VARCHAR(50) DEFAULT NULL,
      `datos_previos` LONGTEXT DEFAULT NULL COMMENT 'JSON con datos anteriores',
      `datos_nuevos` LONGTEXT DEFAULT NULL COMMENT 'JSON con datos nuevos',
      `ip_origen` VARCHAR(45) DEFAULT NULL,
      `fecha_evento` DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_log_usuario` (`usuario_id`),
      KEY `idx_log_modulo` (`modulo`),
      KEY `idx_log_accion` (`accion`),
      KEY `idx_log_fecha` (`fecha_evento`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";
    $pdo->exec($sql);
    $verificada = true;
}

/**
 * Registra un evento en la bitácora de auditoría
 */
function registrarLog(
    $pdo,
    $modulo,
    $accion,
    $descripcion,
    $tabla = null,
    $registroId = null,
    $datosPrevios = null,
    $datosNuevos = null,
    $usuarioId = null,
    $usuarioNombre = null,
    $ip = null,
    $fechaEvento = null
) {
    try {
        asegurarTablaAuditoria($pdo);

        if ($usuarioId === null && isset($_SESSION['admin_id'])) {
            $usuarioId = $_SESSION['admin_id'];
        }
        if ($usuarioNombre === null && isset($_SESSION['admin_nombre'])) {
            $usuarioNombre = $_SESSION['admin_nombre'];
        }
        if (!$usuarioNombre) {
            $usuarioNombre = 'Administrador Principal';
        }
        if ($ip === null) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }

        $prevJson = is_array($datosPrevios) ? json_encode($datosPrevios, JSON_UNESCAPED_UNICODE) : $datosPrevios;
        $nuevJson = is_array($datosNuevos) ? json_encode($datosNuevos, JSON_UNESCAPED_UNICODE) : $datosNuevos;

        if ($fechaEvento !== null) {
            $stmt = $pdo->prepare("
                INSERT INTO auditoria_logs 
                (usuario_id, usuario_nombre, modulo, accion, descripcion, tabla_afectada, registro_id, datos_previos, datos_nuevos, ip_origen, fecha_evento)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $usuarioId,
                $usuarioNombre,
                $modulo,
                $accion,
                $descripcion,
                $tabla,
                $registroId,
                $prevJson,
                $nuevJson,
                $ip,
                $fechaEvento
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO auditoria_logs 
                (usuario_id, usuario_nombre, modulo, accion, descripcion, tabla_afectada, registro_id, datos_previos, datos_nuevos, ip_origen, fecha_evento)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $usuarioId,
                $usuarioNombre,
                $modulo,
                $accion,
                $descripcion,
                $tabla,
                $registroId,
                $prevJson,
                $nuevJson,
                $ip
            ]);
        }
        return true;
    } catch (Exception $e) {
        error_log("Error al registrar auditoria_logs: " . $e->getMessage());
        return false;
    }
}

/**
 * Genera simulación masiva de logs de auditoría orientados a la tesis
 */
function generarLogsSimuladosTesis($pdo, $cantidadLote = 180)
{
    asegurarTablaAuditoria($pdo);

    $usuariosSimulados = [
        ['id' => 1, 'nombre' => 'Administrador Principal (Jhon Puma)', 'ip' => '192.168.1.10'],
        ['id' => 2, 'nombre' => 'Cajero Ventanilla 1 (Ronald Quispe)', 'ip' => '192.168.1.15'],
        ['id' => 3, 'nombre' => 'Fiscalizador Catastral (Edwin Condori)', 'ip' => '192.168.1.22'],
        ['id' => 4, 'nombre' => 'Jefe de Rentas (Marleni Huamán)', 'ip' => '192.168.1.12']
    ];

    $pdo->beginTransaction();

    // 1. Logs de Configuración Inicial (Enero 2026)
    $fechaInicio = '2026-01-05 08:30:00';
    $uAdmin = $usuariosSimulados[0];

    registrarLog(
        $pdo,
        'AUTH',
        'LOGIN_EXITOSO',
        'Inicio de sesión exitoso con credenciales administrativas',
        'usuarios',
        '1',
        null,
        ['usuario' => 'admin', 'metodo' => 'PASSWORD_HASH'],
        $uAdmin['id'],
        $uAdmin['nombre'],
        $uAdmin['ip'],
        '2026-01-05 08:15:20'
    );

    registrarLog(
        $pdo,
        'CONFIG',
        'APROBACION_ANIO',
        'Activación de Año Fiscal 2026 con UIT S/ 5,500.00 según D.S. N° 309-2025-EF',
        'anios_fiscales',
        '2026',
        ['anio' => 2026, 'estado' => 'PLANIFICACION'],
        ['anio' => 2026, 'valor_uit' => 5500.00, 'estado' => 'ACTIVO'],
        $uAdmin['id'],
        $uAdmin['nombre'],
        $uAdmin['ip'],
        '2026-01-05 09:00:15'
    );

    registrarLog(
        $pdo,
        'CONFIG',
        'ORDENANZA_TASAS',
        'Aprobación de tasas y costos de arbitrios 2026 según Ordenanza Municipal N° 004-2026-MDO (Oropesa)',
        'tasas_arbitrios_anuales',
        '2026',
        null,
        ['limpieza_m2' => 0.15, 'fijo_limpieza' => 12.50, 'fijo_seguridad' => 18.00],
        $uAdmin['id'],
        $uAdmin['nombre'],
        $uAdmin['ip'],
        '2026-01-05 09:45:00'
    );

    // 2. Logs de Catastro (Registros y fiscalizaciones)
    $stmtContribs = $pdo->query("SELECT id, numero_documento, nombre_razon_social, direccion_fiscal FROM contribuyentes ORDER BY id ASC LIMIT 50");
    $contribs = $stmtContribs->fetchAll(PDO::FETCH_ASSOC);

    $diasAcumulados = 5;
    foreach ($contribs as $idx => $c) {
        $uOp = $usuariosSimulados[$idx % count($usuariosSimulados)];
        $diasAcumulados += ($idx % 3 === 0) ? 1 : 0;
        $hora = rand(8, 16);
        $min = rand(10, 55);
        $fechaLog = date('Y-m-d H:i:s', strtotime("2026-01-05 +{$diasAcumulados} days +{$hora} hours +{$min} minutes"));

        // Log de registro de contribuyente
        registrarLog(
            $pdo,
            'CATASTRO',
            'REGISTRO_CONTRIBUYENTE',
            "Inscripción de contribuyente: {$c['nombre_razon_social']} ({$c['numero_documento']})",
            'contribuyentes',
            (string)$c['id'],
            null,
            ['documento' => $c['numero_documento'], 'nombre' => $c['nombre_razon_social'], 'direccion' => $c['direccion_fiscal']],
            $uOp['id'],
            $uOp['nombre'],
            $uOp['ip'],
            $fechaLog
        );

        // Cada 4 registros, un log de predio
        if ($idx % 2 === 0) {
            $codCat = sprintf("%02d-%03d-%03dN", rand(1, 5), rand(1, 30), rand(1, 40));
            $fechaPredio = date('Y-m-d H:i:s', strtotime("{$fechaLog} + 15 minutes"));
            registrarLog(
                $pdo,
                'CATASTRO',
                'REGISTRO_PREDIO',
                "Declaración jurada y catastración de predio {$codCat} en {$c['direccion_fiscal']}",
                'predios',
                (string)($idx + 1),
                null,
                ['codigo_catastral' => $codCat, 'propietario' => $c['nombre_razon_social'], 'estado' => 'ACTIVO'],
                $uOp['id'],
                $uOp['nombre'],
                $uOp['ip'],
                $fechaPredio
            );
        }

        // Cada 10 registros, una fiscalización / actualización
        if ($idx % 10 === 0) {
            $fechaMod = date('Y-m-d H:i:s', strtotime("{$fechaLog} + 2 hours"));
            registrarLog(
                $pdo,
                'CATASTRO',
                'ACTUALIZACION_PREDIO',
                "Ajuste técnico por fiscalización catastral en campo de Oropesa (Área construida verificada)",
                'predios',
                (string)($idx + 1),
                ['area_anterior' => 120.00, 'estado' => 'ACTIVO'],
                ['area_nueva' => 145.50, 'estado' => 'ACTIVO', 'motivo' => 'Ampliación 2do piso no declarada'],
                $usuariosSimulados[2]['id'],
                $usuariosSimulados[2]['nombre'],
                $usuariosSimulados[2]['ip'],
                $fechaMod
            );
        }
    }

    // 3. Log de Emisión Masiva Anual
    registrarLog(
        $pdo,
        'EMISION',
        'EMISION_MASIVA',
        'Generación masiva del padrón de deuda 2026 (Impuesto Predial y Arbitrios Municipales de Oropesa)',
        'cta_cte_tributaria',
        '2026',
        null,
        ['anio' => 2026, 'total_predios_procesados' => 465, 'conceptos' => ['PREDIAL', 'LIMPIEZA', 'SEGURIDAD'], 'cuotas_emitidas' => 13020],
        $uAdmin['id'],
        $uAdmin['nombre'],
        $uAdmin['ip'],
        '2026-01-20 11:30:45'
    );

    // 4. Logs de Caja y Cobranza (Recibos emitidos)
    $stmtRecibos = $pdo->query("SELECT r.*, c.nombre_razon_social FROM recibos r JOIN contribuyentes c ON r.contribuyente_id = c.id ORDER BY r.id ASC LIMIT 75");
    $recibos = $stmtRecibos->fetchAll(PDO::FETCH_ASSOC);

    $uCajero = $usuariosSimulados[1];

    // Apertura de caja previa
    registrarLog(
        $pdo,
        'CAJA',
        'APERTURA_CAJA',
        'Apertura oficial de caja ventanilla principal para cobro de arbitrios 2026',
        'caja_sesiones',
        '1',
        null,
        ['cajero' => $uCajero['nombre'], 'saldo_inicial' => 0.00, 'estado' => 'ABIERTA'],
        $uCajero['id'],
        $uCajero['nombre'],
        $uCajero['ip'],
        '2026-01-22 08:00:00'
    );

    foreach ($recibos as $rec) {
        $fechaPago = $rec['fecha_pago'] ?? date('Y-m-d H:i:s');
        registrarLog(
            $pdo,
            'CAJA',
            'COBRO_RECIBO',
            "Cobro de arbitrios en ventanilla: Recibo N° {$rec['numero_serie']} por S/ " . number_format($rec['monto_total'], 2) . " a {$rec['nombre_razon_social']}",
            'recibos',
            (string)$rec['id'],
            ['deuda_estado' => 'PENDIENTE'],
            [
                'serie' => $rec['numero_serie'],
                'monto' => (float)$rec['monto_total'],
                'metodo' => $rec['metodo_pago'],
                'origen' => $rec['origen_pago'],
                'deuda_estado' => 'PAGADO'
            ],
            $uCajero['id'],
            $uCajero['nombre'],
            $uCajero['ip'],
            $fechaPago
        );
    }

    // 5. Logs de Seguridad y Auditoría de Acceso (Trazabilidad y controles)
    $intentosFallidos = [
        ['user' => 'admin_test', 'ip' => '190.236.45.12', 'fecha' => '2026-01-28 22:15:10'],
        ['user' => 'operador_demo', 'ip' => '179.6.14.88', 'fecha' => '2026-02-04 23:40:02'],
        ['user' => 'root', 'ip' => '190.237.110.5', 'fecha' => '2026-02-10 03:12:18']
    ];

    foreach ($intentosFallidos as $intento) {
        registrarLog(
            $pdo,
            'AUTH',
            'LOGIN_FALLIDO',
            "Intento de acceso denegado: Usuario no reconocido o credenciales incorrectas ({$intento['user']})",
            'usuarios',
            null,
            null,
            ['usuario_ingresado' => $intento['user'], 'resultado' => 'BLOQUEADO', 'politica' => 'Filtro de seguridad SART'],
            null,
            'Desconocido (Intrusión Bloqueada)',
            $intento['ip'],
            $intento['fecha']
        );
    }

    // 6. Conciliación y Arqueo de Caja
    registrarLog(
        $pdo,
        'CAJA',
        'CONCILIACION_DIARIA',
        'Arqueo y conciliación de caja ventanilla con tesorería municipal sin descuadres (Diferencia: S/ 0.00)',
        'caja_sesiones',
        '1',
        ['diferencia_detectada' => 0.00],
        ['total_arqueado' => 4580.00, 'total_sistema' => 4580.00, 'estado_conciliacion' => 'CONFORME_100%'],
        $uAdmin['id'],
        $uAdmin['nombre'],
        $uAdmin['ip'],
        date('Y-m-d 17:30:00')
    );

    $pdo->commit();

    $stmtCount = $pdo->query("SELECT COUNT(*) FROM auditoria_logs");
    return (int)$stmtCount->fetchColumn();
}
