<?php
// api/payment/process.php
require_once '../../includes/db.php';
require_once '../../includes/auditoria.php';

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
   $stmtYear = $pdo->query("SELECT anio FROM anios_fiscales WHERE estado = 'ACTIVO' LIMIT 1");
   $anioFiscal = $stmtYear->fetchColumn();

   if (!$anioFiscal) {
      throw new Exception("No hay año fiscal activo para registrar el pago.");
   }

   // Generate Serial Number
   $stmtMax = $pdo->query("SELECT MAX(id) FROM recibos");
   $maxId = $stmtMax->fetchColumn() ?: 0;
   $nextId = $maxId + 1;
   $serie = '001-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

   $stmtRecibo = $pdo->prepare("
        INSERT INTO recibos 
        (anio_fiscal, contribuyente_id, numero_serie, monto_total, origen_pago, metodo_pago, codigo_operacion_bancaria, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'VALIDO')
    ");

   $origen = $input['origen'] ?? 'WEB_ONLINE';
   $metodo = $input['metodo_pago'] ?? 'TARJETA';
   $codigoOp = $input['codigo_operacion'] ?? ('OP-' . rand(100000, 999999));

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
   $stmtDetalle = $pdo->prepare("
        INSERT INTO recibos_detalle 
        (recibo_id, deuda_id, monto_capital, monto_mora)
        VALUES (?, ?, ?, ?)
    ");

   $stmtUpdate = $pdo->prepare("
        UPDATE cta_cte_tributaria 
        SET estado = 'PAGADO', monto_pagado = monto_insoluto 
        WHERE id = ?
    ");

   foreach ($input['items'] as $item) {
      $deudaId = $item['id'];

      $stmtCheck = $pdo->prepare("SELECT * FROM cta_cte_tributaria WHERE id = ? AND estado = 'PENDIENTE'");
      $stmtCheck->execute([$deudaId]);
      $deuda = $stmtCheck->fetch();

      if (!$deuda) {
         throw new Exception("La deuda ID $deudaId no está pendiente o ya fue pagada.");
      }

      $mora = $item['mora'] ?? 0.00;

      $stmtDetalle->execute([
         $reciboId,
         $deudaId,
         $deuda['monto_insoluto'],
         $mora
      ]);

      $stmtUpdate->execute([$deudaId]);
   }

   $pdo->commit();

   // Registrar en bitácora de auditoría
   registrarLog(
      $pdo,
      'CAJA',
      'COBRO_WEB_PASARELA',
      "Pago web confirmado a través de pasarela digital: Recibo {$serie} por S/ " . number_format($input['monto_total'], 2),
      'recibos',
      (string)$reciboId,
      ['deuda_estado' => 'PENDIENTE'],
      [
         'recibo_id' => $reciboId,
         'numero_serie' => $serie,
         'monto_total' => (float)$input['monto_total'],
         'metodo_pago' => $metodo,
         'origen_pago' => $origen,
         'codigo_operacion' => $codigoOp,
         'cuotas_canceladas' => count($input['items'])
      ],
      null,
      'Portal Web Contribuyente (Auto-servicio)',
      $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
   );

   jsonResponse([
      'success' => true,
      'message' => 'Pago procesado correctamente.',
      'recibo_id' => $reciboId,
      'numero_serie' => $serie,
      'codigo_operacion' => $codigoOp,
      'monto_total' => (float)$input['monto_total'],
      'fecha_pago' => date('d/m/Y H:i:s'),
      'metodo_pago' => $metodo,
      'origen_pago' => $origen
   ]);
} catch (Exception $e) {
   if ($pdo->inTransaction()) {
      $pdo->rollBack();
   }
   jsonResponse(['error' => $e->getMessage()], 500);
}
