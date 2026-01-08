<?php
// api/search/taxpayer.php
require_once '../../includes/db.php';

$query = $_GET['q'] ?? '';

if (strlen($query) < 3) {
   jsonResponse([]);
}

try {
   // Search by DNI/RUC or Name
   $stmt = $pdo->prepare("
        SELECT id, tipo_persona, numero_documento, nombre_razon_social, direccion_fiscal 
        FROM contribuyentes 
        WHERE numero_documento LIKE ? OR nombre_razon_social LIKE ?
        LIMIT 10
    ");
   $searchTerm = "%$query%";
   $stmt->execute([$searchTerm, $searchTerm]);
   $results = $stmt->fetchAll();

   jsonResponse($results);
} catch (Exception $e) {
   jsonResponse(['error' => $e->getMessage()], 500);
}
