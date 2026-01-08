<?php
// api/admin/uit.php
require_once '../../includes/db.php';
require_once '../../includes/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
   $anio = $_GET['anio'] ?? null;

   try {
      if ($anio) {
         $stmt = $pdo->prepare("SELECT * FROM uit_historico WHERE anio = ?");
         $stmt->execute([$anio]);
         $data = $stmt->fetch(PDO::FETCH_ASSOC);
      } else {
         $stmt = $pdo->query("SELECT * FROM uit_historico ORDER BY anio DESC");
         $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      }

      echo json_encode($data ?: []);
   } catch (Exception $e) {
      http_response_code(500);
      echo json_encode(['error' => $e->getMessage()]);
   }
} else {
   http_response_code(405);
   echo json_encode(['error' => 'Method not allowed']);
}
