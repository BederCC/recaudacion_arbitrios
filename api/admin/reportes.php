<?php
// api/admin/reportes.php
require_once '../../includes/db.php';
require_once '../../includes/config.php';

header('Content-Type: application/json');

function jsonError($message, $status = 400)
{
   jsonResponse(['error' => $message], $status);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
   $desde = $_GET['desde'] ?? date('Y-m-01');
   $hasta = $_GET['hasta'] ?? date('Y-m-t');

   try {
      $stmt = $pdo->prepare("
            SELECT 
                r.fecha_pago,
                r.numero_serie,
                c.nombre_razon_social,
                r.metodo_pago,
                r.monto_total
            FROM recibos r
            JOIN contribuyentes c ON r.contribuyente_id = c.id
            WHERE DATE(r.fecha_pago) BETWEEN ? AND ? 
            AND r.estado = 'VALIDO'
            ORDER BY r.fecha_pago DESC
        ");
      $stmt->execute([$desde, $hasta]);
      $ingresos = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $total = 0;
      foreach ($ingresos as $row) {
         $total += $row['monto_total'];
      }

      jsonResponse(['ingresos' => $ingresos, 'total' => $total]);
   } catch (Exception $e) {
      jsonError($e->getMessage());
   }
} else {
   jsonError("Method not allowed", 405);
}
