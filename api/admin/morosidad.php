<?php
// api/admin/morosidad.php
require_once '../../includes/db.php';
require_once '../../includes/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : 2026;
        $q = trim($_GET['q'] ?? '');
        $nivelRiesgo = $_GET['riesgo'] ?? '';
        $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 500) : 100;

        // 1. Estadísticas Globales
        $stmtTotales = $pdo->prepare("
            SELECT 
                COUNT(*) as total_cuotas,
                SUM(monto_insoluto) as monto_emitido,
                SUM(CASE WHEN estado = 'PAGADO' THEN monto_pagado ELSE 0 END) as monto_recaudado,
                SUM(CASE WHEN estado = 'PENDIENTE' THEN monto_insoluto ELSE 0 END) as monto_pendiente,
                COUNT(DISTINCT contribuyente_id) as total_contribuyentes,
                COUNT(DISTINCT CASE WHEN estado = 'PENDIENTE' THEN contribuyente_id END) as contribuyentes_morosos
            FROM cta_cte_tributaria
            WHERE anio = ?
        ");
        $stmtTotales->execute([$anio]);
        $totales = $stmtTotales->fetch(PDO::FETCH_ASSOC);

        $montoEmitido = (float)($totales['monto_emitido'] ?? 0);
        $montoPendiente = (float)($totales['monto_pendiente'] ?? 0);
        $montoRecaudado = (float)($totales['monto_recaudado'] ?? 0);
        $tasaMorosidad = $montoEmitido > 0 ? round(($montoPendiente / $montoEmitido) * 100, 2) : 0;

        // 2. Antigüedad y Semáforo de Morosidad
        $stmtSemaforo = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN DATEDIFF(CURRENT_DATE, fecha_vencimiento) <= 30 THEN monto_insoluto ELSE 0 END) as tramo_corriente,
                SUM(CASE WHEN DATEDIFF(CURRENT_DATE, fecha_vencimiento) BETWEEN 31 AND 90 THEN monto_insoluto ELSE 0 END) as tramo_preventivo,
                SUM(CASE WHEN DATEDIFF(CURRENT_DATE, fecha_vencimiento) > 90 THEN monto_insoluto ELSE 0 END) as tramo_critico
            FROM cta_cte_tributaria
            WHERE anio = ? AND estado = 'PENDIENTE'
        ");
        $stmtSemaforo->execute([$anio]);
        $semaforo = $stmtSemaforo->fetch(PDO::FETCH_ASSOC);

        // 3. Ranking de Contribuyentes Morosos
        $whereSearch = "";
        $paramsRank = [$anio];
        if (!empty($q)) {
            $whereSearch = "AND (c.nombre_razon_social LIKE ? OR c.numero_documento LIKE ? OR c.direccion_fiscal LIKE ?)";
            $paramsRank[] = "%$q%";
            $paramsRank[] = "%$q%";
            $paramsRank[] = "%$q%";
        }

        $stmtRank = $pdo->prepare("
            SELECT 
                c.id,
                c.tipo_persona,
                c.numero_documento,
                c.nombre_razon_social,
                c.direccion_fiscal,
                c.telefono,
                c.correo,
                COUNT(DISTINCT d.predio_id) as total_predios,
                COUNT(d.id) as cuotas_pendientes,
                SUM(d.monto_insoluto) as total_deuda,
                MIN(d.fecha_vencimiento) as fecha_mas_antigua,
                MAX(DATEDIFF(CURRENT_DATE, d.fecha_vencimiento)) as dias_max_mora
            FROM cta_cte_tributaria d
            JOIN contribuyentes c ON d.contribuyente_id = c.id
            WHERE d.anio = ? AND d.estado = 'PENDIENTE'
            $whereSearch
            GROUP BY c.id
            ORDER BY total_deuda DESC
            LIMIT " . (int)$limit
        );
        $stmtRank->execute($paramsRank);
        $deudores = $stmtRank->fetchAll(PDO::FETCH_ASSOC);

        // Enriquecer con Nivel de Riesgo
        foreach ($deudores as &$deudor) {
            $dias = (int)($deudor['dias_max_mora'] ?? 0);
            if ($dias > 90 || $deudor['total_deuda'] > 500) {
                $deudor['riesgo'] = 'ALTO';
                $deudor['riesgo_clase'] = 'bg-red-100 text-red-800 border-red-200';
            } elseif ($dias > 30 || $deudor['total_deuda'] > 200) {
                $deudor['riesgo'] = 'MEDIO';
                $deudor['riesgo_clase'] = 'bg-yellow-100 text-yellow-800 border-yellow-200';
            } else {
                $deudor['riesgo'] = 'BAJO';
                $deudor['riesgo_clase'] = 'bg-green-100 text-green-800 border-green-200';
            }
        }
        unset($deudor);

        // Filtrar por nivel de riesgo si fue solicitado
        if (!empty($nivelRiesgo)) {
            $deudores = array_values(array_filter($deudores, function($d) use ($nivelRiesgo) {
                return $d['riesgo'] === $nivelRiesgo;
            }));
        }

        jsonResponse([
            'success' => true,
            'kpis' => [
                'anio' => $anio,
                'monto_emitido' => $montoEmitido,
                'monto_recaudado' => $montoRecaudado,
                'monto_pendiente' => $montoPendiente,
                'tasa_morosidad' => $tasaMorosidad,
                'tasa_cumplimiento' => $montoEmitido > 0 ? round(($montoRecaudado / $montoEmitido) * 100, 2) : 0,
                'total_contribuyentes' => (int)($totales['total_contribuyentes'] ?? 0),
                'contribuyentes_morosos' => (int)($totales['contribuyentes_morosos'] ?? 0),
                'contribuyentes_puntuales' => (int)($totales['total_contribuyentes'] ?? 0) - (int)($totales['contribuyentes_morosos'] ?? 0)
            ],
            'semaforo' => [
                'corriente' => (float)($semaforo['tramo_corriente'] ?? 0),
                'preventivo' => (float)($semaforo['tramo_preventivo'] ?? 0),
                'critico' => (float)($semaforo['tramo_critico'] ?? 0)
            ],
            'total_deudores' => count($deudores),
            'deudores' => $deudores
        ]);

    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
