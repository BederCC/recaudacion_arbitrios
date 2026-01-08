<?php
// api/admin/anios.php
require_once '../../includes/db.php';
require_once '../../includes/config.php';

header('Content-Type: application/json');

function jsonError($message, $status = 400)
{
   jsonResponse(['error' => $message], $status);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
   try {
      $stmt = $pdo->query("SELECT * FROM anios_fiscales ORDER BY anio DESC");
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      jsonResponse($data);
   } catch (Exception $e) {
      jsonError($e->getMessage());
   }
} elseif ($method === 'POST') {
   $input = json_decode(file_get_contents('php://input'), true);

   if (!$input) {
      jsonError("Invalid JSON input");
   }

   $anio = $input['anio'] ?? null;
   $uit = $input['valor_uit'] ?? 0;
   $tim = $input['interes_moratorio_diario'] ?? 0.033;
   $estado = $input['estado'] ?? 'PLANIFICACION';

   if (!$anio || !$uit) {
      jsonError("Año y UIT son obligatorios");
   }

   try {
      $stmt = $pdo->prepare("
            INSERT INTO anios_fiscales (anio, valor_uit, interes_moratorio_diario, estado) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE valor_uit=?, interes_moratorio_diario=?, estado=?
        ");
      $stmt->execute([$anio, $uit, $tim, $estado, $uit, $tim, $estado]);
      jsonResponse(['success' => true, 'message' => 'Año fiscal guardado correctamente']);
   } catch (Exception $e) {
      jsonError($e->getMessage());
   }
} else {
   jsonError("Method not allowed", 405);
}
