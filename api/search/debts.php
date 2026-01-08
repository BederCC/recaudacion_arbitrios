<?php
// api/search/debts.php
require_once '../../includes/db.php';

$contribuyente_id = $_GET['contribuyente_id'] ?? null;

if (!$contribuyente_id) {
   jsonResponse(['error' => 'ID de contribuyente requerido'], 400);
}

try {
   // 1. Get Global Parameters for Interest
   // We need the daily interest rate from the active fiscal year OR the year of the debt?
   // Prompt says: "usando la tasa diaria (interes_moratorio_diario) de la tabla anios_fiscales."
   // Usually, the rate is from the current active year (TIM vigente), applied to all past debts.
   // Or it could be historical.
   // Let's assume we use the rate from the *current active* fiscal year for simplicity and standard practice unless specified otherwise.
   // "Verificar que el año fiscal esté 'ACTIVO' en anios_fiscales." -> This was for emission.
   // For calculation: "usando la tasa diaria... de la tabla anios_fiscales."
   // I will fetch the rate from the debt's fiscal year record if available, or the active one.
   // Actually, `anios_fiscales` has the rate. So I should join with `anios_fiscales` on `cta_cte_tributaria.anio`.

   // Fetch debts
   $stmt = $pdo->prepare("
        SELECT 
            c.*, 
            af.interes_moratorio_diario,
            p.direccion_predio
        FROM cta_cte_tributaria c
        JOIN anios_fiscales af ON c.anio = af.anio
        JOIN predios p ON c.predio_id = p.id
        WHERE c.contribuyente_id = ? 
        AND c.estado IN ('PENDIENTE', 'FRACCIONADO')
        ORDER BY c.anio ASC, c.periodo ASC
    ");
   $stmt->execute([$contribuyente_id]);
   $deudas = $stmt->fetchAll();

   $resultado = [];
   $fechaActual = new DateTime(); // Today

   foreach ($deudas as $deuda) {
      $montoOriginal = (float)$deuda['monto_insoluto'];
      $mora = 0.00;
      $diasAtraso = 0;

      $fechaVencimiento = new DateTime($deuda['fecha_vencimiento']);

      if ($fechaVencimiento < $fechaActual) {
         $interval = $fechaVencimiento->diff($fechaActual);
         $diasAtraso = $interval->days;

         // Formula: Interés = Monto_Insoluto * (Tasa_Diaria * Dias_Atraso)
         $tasaDiaria = (float)$deuda['interes_moratorio_diario']; // e.g., 0.033% -> 0.00033? 
         // Prompt says: "interes_moratorio_diario DECIMAL(10,5) DEFAULT 0.033"
         // Usually TIM is like 1.2% monthly -> 0.04% daily.
         // If the value in DB is 0.033, is it percentage or factor?
         // "DEFAULT 0.033" looks like a percentage (0.033%).
         // If it were a factor it would be 0.00033.
         // Let's assume it is a PERCENTAGE value (e.g. 0.033 means 0.033%).
         // So we divide by 100.

         $factor = ($tasaDiaria / 100) * $diasAtraso;
         $mora = $montoOriginal * $factor;
      }

      $total = $montoOriginal + $mora;

      $resultado[] = [
         'id' => $deuda['id'],
         'anio' => $deuda['anio'],
         'concepto' => $deuda['tributo_concepto'],
         'periodo' => $deuda['periodo'],
         'predio' => $deuda['direccion_predio'],
         'vencimiento' => $deuda['fecha_vencimiento'],
         'monto_insoluto' => number_format($montoOriginal, 2, '.', ''),
         'mora' => number_format($mora, 2, '.', ''),
         'dias_atraso' => $diasAtraso,
         'total' => number_format($total, 2, '.', ''),
         'checked' => false // For frontend state
      ];
   }

   jsonResponse($resultado);
} catch (Exception $e) {
   jsonResponse(['error' => $e->getMessage()], 500);
}
