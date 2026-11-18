<?php
// api/admin/conciliacion.php
require_once '../../includes/db.php';
require_once '../../includes/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $sesionId = isset($_GET['sesion_id']) ? (int)$_GET['sesion_id'] : null;

        // 1. Obtener todas las sesiones de caja
        $stmtSesiones = $pdo->query("
            SELECT s.*, 
                   COUNT(r.id) as total_recibos,
                   COALESCE(SUM(r.monto_total), 0) as total_calculado
            FROM caja_sesiones s
            LEFT JOIN recibos r ON s.id = r.caja_sesion_id AND r.estado = 'VALIDO'
            GROUP BY s.id
            ORDER BY s.id DESC
        ");
        $sesiones = $stmtSesiones->fetchAll(PDO::FETCH_ASSOC);

        // Si no se especificó sesión, tomar la primera (o la abierta)
        if (!$sesionId && !empty($sesiones)) {
            $sesionId = (int)$sesiones[0]['id'];
        }

        // 2. Datos de la sesión seleccionada
        $sesionActual = null;
        foreach ($sesiones as $s) {
            if ((int)$s['id'] === $sesionId) {
                $sesionActual = $s;
                break;
            }
        }

        // 3. Desglose por método de pago para la sesión
        $stmtMetodos = $pdo->prepare("
            SELECT 
                metodo_pago,
                COUNT(*) as cantidad_recibos,
                SUM(monto_total) as subtotal
            FROM recibos
            WHERE (caja_sesion_id = ? OR (? IS NULL AND caja_sesion_id IS NULL)) AND estado = 'VALIDO'
            GROUP BY metodo_pago
        ");
        $stmtMetodos->execute([$sesionId, $sesionId]);
        $metodos = $stmtMetodos->fetchAll(PDO::FETCH_ASSOC);

        // 4. Desglose por origen de pago
        $stmtOrigenes = $pdo->prepare("
            SELECT 
                origen_pago,
                COUNT(*) as cantidad_recibos,
                SUM(monto_total) as subtotal
            FROM recibos
            WHERE (caja_sesion_id = ? OR (? IS NULL AND caja_sesion_id IS NULL)) AND estado = 'VALIDO'
            GROUP BY origen_pago
        ");
        $stmtOrigenes->execute([$sesionId, $sesionId]);
        $origenes = $stmtOrigenes->fetchAll(PDO::FETCH_ASSOC);

        // 5. Últimos recibos de la sesión
        $stmtRecibos = $pdo->prepare("
            SELECT r.*, c.nombre_razon_social, c.numero_documento
            FROM recibos r
            JOIN contribuyentes c ON r.contribuyente_id = c.id
            WHERE (r.caja_sesion_id = ? OR (? IS NULL AND r.caja_sesion_id IS NULL)) AND r.estado = 'VALIDO'
            ORDER BY r.id DESC
            LIMIT 100
        ");
        $stmtRecibos->execute([$sesionId, $sesionId]);
        $recibos = $stmtRecibos->fetchAll(PDO::FETCH_ASSOC);

        $totalSesion = 0;
        foreach ($metodos as $m) {
            $totalSesion += (float)$m['subtotal'];
        }

        jsonResponse([
            'success' => true,
            'sesion_seleccionada' => $sesionActual,
            'sesiones' => $sesiones,
            'resumen_metodos' => $metodos,
            'resumen_origenes' => $origenes,
            'total_sesion' => $totalSesion,
            'recibos' => $recibos
        ]);

    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
