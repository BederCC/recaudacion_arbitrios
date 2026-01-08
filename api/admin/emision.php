<?php
// api/admin/emision.php
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
      // Get Active Year
      $stmt = $pdo->query("SELECT * FROM anios_fiscales WHERE estado = 'ACTIVO' LIMIT 1");
      $anio = $stmt->fetch(PDO::FETCH_ASSOC);

      jsonResponse(['anio' => $anio ?: null]);
   } catch (Exception $e) {
      jsonError($e->getMessage());
   }
} else {
   jsonError("Method not allowed", 405);
}
