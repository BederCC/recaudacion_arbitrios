<?php
// api/admin/caja.php
require_once '../../includes/db.php';
require_once '../../includes/config.php';

header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['admin_nombre'])) {
   http_response_code(401);
   echo json_encode(['error' => 'Unauthorized']);
   exit;
}

$usuario_cajero = $_SESSION['admin_nombre'];

function jsonError($message, $status = 400)
{
   jsonResponse(['error' => $message], $status);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
   // Get Session Status
   try {
      $stmtSesion = $pdo->prepare("SELECT * FROM caja_sesiones WHERE usuario_cajero = ? AND estado = 'ABIERTA'");
      $stmtSesion->execute([$usuario_cajero]);
      $sesion = $stmtSesion->fetch(PDO::FETCH_ASSOC);

      jsonResponse(['sesion' => $sesion ?: null]);
   } catch (Exception $e) {
      jsonError($e->getMessage());
   }
} elseif ($method === 'POST') {
   $input = json_decode(file_get_contents('php://input'), true);
   $accion = $input['accion'] ?? '';

   try {
      if ($accion === 'abrir') {
         // Check if already open
         $stmtCheck = $pdo->prepare("SELECT id FROM caja_sesiones WHERE usuario_cajero = ? AND estado = 'ABIERTA'");
         $stmtCheck->execute([$usuario_cajero]);
         if ($stmtCheck->fetch()) {
            jsonError("Ya existe una caja abierta para este usuario.");
         }

         $stmt = $pdo->prepare("INSERT INTO caja_sesiones (usuario_cajero, fecha_apertura, estado) VALUES (?, NOW(), 'ABIERTA')");
         $stmt->execute([$usuario_cajero]);
         jsonResponse(['success' => true, 'message' => 'Caja abierta correctamente']);
      } elseif ($accion === 'cerrar') {
         $id = $input['sesion_id'] ?? null;
         if (!$id) jsonError("ID de sesión requerido");

         // Calculate total collected
         $stmtTotal = $pdo->prepare("SELECT SUM(monto_total) FROM recibos WHERE caja_sesion_id = ? AND estado = 'VALIDO'");
         $stmtTotal->execute([$id]);
         $total = $stmtTotal->fetchColumn() ?: 0;

         $stmt = $pdo->prepare("UPDATE caja_sesiones SET fecha_cierre = NOW(), monto_recaudado = ?, estado = 'CERRADA' WHERE id = ?");
         $stmt->execute([$total, $id]);
         jsonResponse(['success' => true, 'message' => 'Caja cerrada correctamente. Recaudado: S/ ' . number_format($total, 2)]);
      } else {
         jsonError("Acción no válida");
      }
   } catch (Exception $e) {
      jsonError($e->getMessage());
   }
} else {
   jsonError("Method not allowed", 405);
}
