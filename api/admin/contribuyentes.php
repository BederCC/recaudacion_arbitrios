<?php
// api/admin/contribuyentes.php
require_once '../../includes/db.php';
require_once '../../includes/config.php';

header('Content-Type: application/json');



// Helper for Error
function jsonError($message, $status = 400)
{
   jsonResponse(['error' => $message], $status);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
   // Search / List
   $search = $_GET['q'] ?? '';
   $limit = $_GET['limit'] ?? 2000;

   $where = '';
   $params = [];

   if ($search) {
      $where = "WHERE nombre_razon_social LIKE ? OR numero_documento LIKE ?";
      $params = ["%$search%", "%$search%"];
   }

   try {
      $stmt = $pdo->prepare("SELECT * FROM contribuyentes $where ORDER BY id DESC LIMIT " . (int)$limit);
      $stmt->execute($params);
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      jsonResponse($data);
   } catch (Exception $e) {
      jsonError($e->getMessage());
   }
} elseif ($method === 'POST') {
   // Create / Update
   $input = json_decode(file_get_contents('php://input'), true);

   if (!$input) {
      jsonError("Invalid JSON input");
   }

   $id = $input['id'] ?? null;
   $tipo = $input['tipo_persona'] ?? 'NATURAL';
   $doc = $input['numero_documento'] ?? '';
   $nombre = $input['nombre_razon_social'] ?? '';
   $dir = $input['direccion_fiscal'] ?? '';
   $email = $input['correo'] ?? '';
   $tel = $input['telefono'] ?? '';

   if (empty($doc) || empty($nombre)) {
      jsonError("Documento y Nombre son obligatorios");
   }

   try {
      if ($id) {
         // Update
         $stmt = $pdo->prepare("UPDATE contribuyentes SET tipo_persona=?, numero_documento=?, nombre_razon_social=?, direccion_fiscal=?, correo=?, telefono=? WHERE id=?");
         $stmt->execute([$tipo, $doc, $nombre, $dir, $email, $tel, $id]);
         jsonResponse(['success' => true, 'message' => 'Contribuyente actualizado']);
      } else {
         // Insert
         $stmt = $pdo->prepare("INSERT INTO contribuyentes (tipo_persona, numero_documento, nombre_razon_social, direccion_fiscal, correo, telefono) VALUES (?, ?, ?, ?, ?, ?)");
         $stmt->execute([$tipo, $doc, $nombre, $dir, $email, $tel]);
         jsonResponse(['success' => true, 'message' => 'Contribuyente creado', 'id' => $pdo->lastInsertId()]);
      }
   } catch (Exception $e) {
      jsonError($e->getMessage());
   }
} else {
   jsonError("Method not allowed", 405);
}
