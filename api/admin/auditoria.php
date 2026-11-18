<?php
// api/admin/auditoria.php
require_once '../../includes/db.php';
require_once '../../includes/config.php';
require_once '../../includes/auditoria.php';

header('Content-Type: application/json');

asegurarTablaAuditoria($pdo);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $modulo = $_GET['modulo'] ?? '';
        $accion = $_GET['accion'] ?? '';
        $q = trim($_GET['q'] ?? '');
        $fechaInicio = $_GET['fecha_inicio'] ?? '';
        $fechaFin = $_GET['fecha_fin'] ?? '';
        $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 2000) : 500;

        $conditions = [];
        $params = [];

        if (!empty($modulo)) {
            $conditions[] = "modulo = ?";
            $params[] = $modulo;
        }

        if (!empty($accion)) {
            $conditions[] = "accion = ?";
            $params[] = $accion;
        }

        if (!empty($fechaInicio)) {
            $conditions[] = "DATE(fecha_evento) >= ?";
            $params[] = $fechaInicio;
        }

        if (!empty($fechaFin)) {
            $conditions[] = "DATE(fecha_evento) <= ?";
            $params[] = $fechaFin;
        }

        if (!empty($q)) {
            $conditions[] = "(usuario_nombre LIKE ? OR descripcion LIKE ? OR tabla_afectada LIKE ? OR ip_origen LIKE ? OR accion LIKE ?)";
            $searchTerm = "%$q%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $where = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : '';

        // Query logs
        $stmt = $pdo->prepare("
            SELECT * 
            FROM auditoria_logs 
            $where 
            ORDER BY fecha_evento DESC, id DESC 
            LIMIT " . (int)$limit
        );
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // General Stats
        $stmtStats = $pdo->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN modulo = 'AUTH' THEN 1 ELSE 0 END) as total_auth,
                SUM(CASE WHEN modulo = 'CATASTRO' THEN 1 ELSE 0 END) as total_catastro,
                SUM(CASE WHEN modulo = 'EMISION' THEN 1 ELSE 0 END) as total_emision,
                SUM(CASE WHEN modulo = 'CAJA' THEN 1 ELSE 0 END) as total_caja,
                SUM(CASE WHEN modulo = 'CONFIG' THEN 1 ELSE 0 END) as total_config,
                SUM(CASE WHEN accion = 'LOGIN_FALLIDO' THEN 1 ELSE 0 END) as total_alertas_seguridad
            FROM auditoria_logs
        ");
        $stats = $stmtStats->fetch(PDO::FETCH_ASSOC) ?: [
            'total' => 0,
            'total_auth' => 0,
            'total_catastro' => 0,
            'total_emision' => 0,
            'total_caja' => 0,
            'total_config' => 0,
            'total_alertas_seguridad' => 0
        ];

        // List distinct modules and actions for filter dropdowns
        $stmtModulos = $pdo->query("SELECT DISTINCT modulo FROM auditoria_logs ORDER BY modulo ASC");
        $modulos = $stmtModulos->fetchAll(PDO::FETCH_COLUMN);

        $stmtAcciones = $pdo->query("SELECT DISTINCT accion FROM auditoria_logs ORDER BY accion ASC");
        $acciones = $stmtAcciones->fetchAll(PDO::FETCH_COLUMN);

        jsonResponse([
            'success' => true,
            'stats' => $stats,
            'modulos' => $modulos,
            'acciones' => $acciones,
            'total_filtrados' => count($logs),
            'logs' => $logs
        ]);

    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['accion'] ?? '';

    try {
        if ($action === 'simular') {
            $totalGenerados = generarLogsSimuladosTesis($pdo);
            jsonResponse([
                'success' => true,
                'message' => "Se generaron eventos de auditoría y trazabilidad exitosamente. Total actual en bitácora: {$totalGenerados} registros.",
                'total' => $totalGenerados
            ]);
        } elseif ($action === 'limpiar') {
            $pdo->exec("TRUNCATE TABLE auditoria_logs");
            jsonResponse([
                'success' => true,
                'message' => 'Bitácora de auditoría reiniciada correctamente.'
            ]);
        } else {
            jsonResponse(['error' => 'Acción no válida'], 400);
        }
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
} else {
    jsonResponse(['error' => 'Método no permitido'], 405);
}
