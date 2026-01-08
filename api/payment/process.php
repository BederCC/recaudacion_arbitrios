<?php
// api/payment/process.php
require_once '../../includes/db.php';

// Expect JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
   jsonResponse(['error' => 'Invalid JSON'], 400);
}

// Validate required fields
if (empty($input['contribuyente_id']) || empty($input['items']) || empty($input['monto_total'])) {
   jsonResponse(['error' => 'Datos incompletos'], 400);
}

try {
   $pdo->beginTransaction();

   // 1. Insert Receipt Header
   // Get Active Year for the receipt record (usually current year)
   $stmtYear = $pdo->query("SELECT anio FROM anios_fiscales WHERE estado = 'ACTIVO' LIMIT 1");
   $anioFiscal = $stmtYear->fetchColumn();

   if (!$anioFiscal) {
      throw new Exception("No hay año fiscal activo para registrar el pago.");
   }

   // Generate Serial Number (Simple increment for demo)
   // In real app, this needs concurrency handling or a separate sequence table.
   $stmtMax = $pdo->query("SELECT MAX(id) FROM recibos");
   $maxId = $stmtMax->fetchColumn() ?: 0;
   $nextId = $maxId + 1;
   $serie = '001-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

   $stmtRecibo = $pdo->prepare("
        INSERT INTO recibos 
        (anio_fiscal, contribuyente_id, numero_serie, monto_total, origen_pago, metodo_pago, codigo_operacion_bancaria, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'VALIDO')
    ");

   $origen = $input['origen'] ?? 'VENTANILLA_MUNICIPAL';
   $metodo = $input['metodo_pago'] ?? 'EFECTIVO';
   $codigoOp = $input['codigo_operacion'] ?? null;

   $stmtRecibo->execute([
      $anioFiscal,
      $input['contribuyente_id'],
      $serie,
      $input['monto_total'],
      $origen,
      $metodo,
      $codigoOp
   ]);

   $reciboId = $pdo->lastInsertId();

   // 2. Process Items
   foreach ($input['items'] as $item) {
      $deudaId = $item['id'];

      // Verify Debt Status (Locking row is ideal, but for now simple check)
      $stmtCheck = $pdo->prepare("SELECT * FROM cta_cte_tributaria WHERE id = ? AND estado = 'PENDIENTE'");
      $stmtCheck->execute([$deudaId]);
      $deuda = $stmtCheck->fetch();

      if (!$deuda) {
         throw new Exception("La deuda ID $deudaId no está pendiente o no existe.");
      }

      // Recalculate Mora to be sure? 
      // For this demo, we trust the frontend sent the correct split, 
      // BUT we should validate the total at least.
      // Let's use the values sent by frontend for 'monto_mora' but validate 'monto_capital' matches DB.

      if (abs((float)$deuda['monto_insoluto'] - (float)$item['monto_insoluto']) > 0.01) {
         throw new Exception("El monto insoluto no coincide para la deuda ID $deudaId.");
      }

      // Insert Detail
      $stmtDetalle = $pdo->prepare("
            INSERT INTO recibos_detalle 
            (recibo_id, deuda_id, monto_capital, monto_mora)
            VALUES (?, ?, ?, ?)
        ");

      $mora = $item['mora'] ?? 0.00;

      $stmtDetalle->execute([
         $reciboId,
         $deudaId,
         $deuda['monto_insoluto'],
         $mora
      ]);

      // Update Debt Status
      $stmtUpdate = $pdo->prepare("
            UPDATE cta_cte_tributaria 
            SET estado = 'PAGADO', monto_pagado = monto_insoluto 
            WHERE id = ?
        ");
      $stmtUpdate->execute([$deudaId]);
   }

   $pdo->commit();

   jsonResponse([
      'success' => true,
      'message' => 'Pago procesado correctamente.',
      'recibo_id' => $reciboId,
      'numero_serie' => $serie
   ]);
} catch (Exception $e) {
   if ($pdo->inTransaction()) {
      $pdo->rollBack();
   }
   jsonResponse(['error' => $e->getMessage()], 500);
}
