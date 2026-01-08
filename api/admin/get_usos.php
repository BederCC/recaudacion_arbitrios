<?php
// api/admin/get_usos.php
require_once '../../includes/db.php';
require_once '../../includes/config.php';

header('Content-Type: application/json');

try {
   $stmt = $pdo->query("SELECT * FROM tipos_uso_predio ORDER BY descripcion ASC");
   $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
   echo json_encode($data);
} catch (Exception $e) {
   http_response_code(500);
   echo json_encode(['error' => $e->getMessage()]);
}
