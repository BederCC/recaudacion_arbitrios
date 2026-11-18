<?php
// api/admin/predios.php
require_once '../../includes/db.php';
require_once '../../includes/config.php';

header('Content-Type: application/json');

function jsonError($message, $status = 400)
{
   jsonResponse(['error' => $message], $status);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'next_code') {
   try {
      $stmtMax = $pdo->query("SELECT MAX(codigo_catastral) FROM predios");
      $maxCode = $stmtMax->fetchColumn();

      if ($maxCode) {
         $number = (int)substr($maxCode, 0, 8);
         $number++;
         $nextCode = str_pad($number, 8, '0', STR_PAD_LEFT) . 'N';
      } else {
         $nextCode = '00000001N';
      }
      jsonResponse(['next_code' => $nextCode]);
   } catch (Exception $e) {
      jsonError($e->getMessage());
   }
} elseif ($method === 'GET') {
   // List / Search
   $contribuyente_id = $_GET['contribuyente_id'] ?? null;
   $q = trim($_GET['q'] ?? '');
   $limit = $_GET['limit'] ?? 2000;

   $conditions = [];
   $params = [];

   if ($contribuyente_id) {
      $conditions[] = "p.contribuyente_id = ?";
      $params[] = $contribuyente_id;
   }

   if (!empty($q)) {
      $conditions[] = "(p.codigo_catastral LIKE ? OR p.direccion_predio LIKE ? OR c.nombre_razon_social LIKE ? OR c.numero_documento LIKE ?)";
      $searchTerm = "%$q%";
      $params[] = $searchTerm;
      $params[] = $searchTerm;
      $params[] = $searchTerm;
      $params[] = $searchTerm;
   }

   $where = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : '';

   try {
      $stmt = $pdo->prepare(
         "
            SELECT p.*, c.nombre_razon_social, t.descripcion as uso_descripcion 
            FROM predios p
            JOIN contribuyentes c ON p.contribuyente_id = c.id
            JOIN tipos_uso_predio t ON p.tipo_uso_id = t.id
            $where
            ORDER BY p.id DESC LIMIT " . (int)$limit
      );
      $stmt->execute($params);
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Also fetch Tipos de Uso for the frontend dropdown
      $stmtUsos = $pdo->query("SELECT * FROM tipos_uso_predio");
      $usos = $stmtUsos->fetchAll(PDO::FETCH_ASSOC);

      jsonResponse(['predios' => $data, 'usos' => $usos]);
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
   $contribuyente_id = $input['contribuyente_id'] ?? null;
   $anio = $input['anio_declaracion'] ?? date('Y');
   $codigo = $input['codigo_catastral'] ?? '';
   $direccion = $input['direccion_predio'] ?? '';
   $uso = $input['tipo_uso_id'] ?? 1;
   $area = $input['area_construida'] ?? 0;
   $frente = $input['longitud_frente'] ?? 0;
   $autovaluo = $input['valor_autovaluo'] ?? 0;
   $estado = $input['estado'] ?? 'ACTIVO';

   if (!$contribuyente_id || !$direccion) {
      jsonError("Contribuyente y Dirección son obligatorios");
   }

   try {
      if ($id) {
         // Update
         $stmt = $pdo->prepare("UPDATE predios SET contribuyente_id=?, anio_declaracion=?, codigo_catastral=?, direccion_predio=?, tipo_uso_id=?, area_construida=?, longitud_frente=?, valor_autovaluo=?, estado=? WHERE id=?");
         $stmt->execute([$contribuyente_id, $anio, $codigo, $direccion, $uso, $area, $frente, $autovaluo, $estado, $id]);
         jsonResponse(['success' => true, 'message' => 'Predio actualizado']);
      } else {
         // Auto-generate Codigo Catastral if empty
         if (empty($codigo)) {
            $stmtMax = $pdo->query("SELECT MAX(codigo_catastral) FROM predios");
            $maxCode = $stmtMax->fetchColumn();

            if ($maxCode) {
               $number = (int)substr($maxCode, 0, 8);
               $number++;
               $codigo = str_pad($number, 8, '0', STR_PAD_LEFT) . 'N';
            } else {
               $codigo = '00000001N';
            }
         }

         // Insert
         $stmt = $pdo->prepare("INSERT INTO predios (contribuyente_id, anio_declaracion, codigo_catastral, direccion_predio, tipo_uso_id, area_construida, longitud_frente, valor_autovaluo, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
         $stmt->execute([$contribuyente_id, $anio, $codigo, $direccion, $uso, $area, $frente, $autovaluo, $estado]);
         jsonResponse(['success' => true, 'message' => 'Predio creado', 'id' => $pdo->lastInsertId(), 'codigo' => $codigo]);
      }
   } catch (Exception $e) {
      jsonError($e->getMessage());
   }
} else {
   jsonError("Method not allowed", 405);
}
