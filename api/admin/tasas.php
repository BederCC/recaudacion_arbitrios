<?php
// api/admin/tasas.php
require_once '../../includes/db.php';
require_once '../../includes/config.php';

header('Content-Type: application/json');

function jsonError($message, $status = 400)
{
   jsonResponse(['error' => $message], $status);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
   $anio = $_GET['anio'] ?? date('Y');

   try {
      // Fetch Tipos de Uso
      $stmtTipos = $pdo->query("SELECT * FROM tipos_uso_predio");
      $tiposUso = $stmtTipos->fetchAll(PDO::FETCH_ASSOC);

      // Fetch Rates for Year
      $stmtTasas = $pdo->prepare("SELECT * FROM tasas_arbitrios_anuales WHERE anio = ?");
      $stmtTasas->execute([$anio]);
      $rows = $stmtTasas->fetchAll(PDO::FETCH_ASSOC);

      $tasasMap = [];
      foreach ($rows as $r) {
         $tasasMap[$r['tipo_uso_id']] = $r;
      }

      jsonResponse(['tipos' => $tiposUso, 'tasas' => $tasasMap, 'anio' => $anio]);
   } catch (Exception $e) {
      jsonError($e->getMessage());
   }
} elseif ($method === 'POST') {
   $input = json_decode(file_get_contents('php://input'), true);

   if (!$input) {
      jsonError("Invalid JSON input");
   }

   $anio = $input['anio'];
   $tasas = $input['tasas']; // Array of tasa objects

   try {
      $pdo->beginTransaction();
      foreach ($tasas as $tasa) {
         $stmt = $pdo->prepare("
                INSERT INTO tasas_arbitrios_anuales 
                (anio, tipo_uso_id, costo_limpieza_m2, costo_limpieza_fijo, costo_seguridad_fijo)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                costo_limpieza_m2=?, costo_limpieza_fijo=?, costo_seguridad_fijo=?
            ");
         $stmt->execute([
            $anio,
            $tasa['tipo_uso_id'],
            $tasa['costo_limpieza_m2'],
            $tasa['costo_limpieza_fijo'],
            $tasa['costo_seguridad_fijo'],
            $tasa['costo_limpieza_m2'],
            $tasa['costo_limpieza_fijo'],
            $tasa['costo_seguridad_fijo']
         ]);
      }
      $pdo->commit();
      jsonResponse(['success' => true, 'message' => 'Tasas actualizadas correctamente']);
   } catch (Exception $e) {
      $pdo->rollBack();
      jsonError($e->getMessage());
   }
} else {
   jsonError("Method not allowed", 405);
}
