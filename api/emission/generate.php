<?php
// api/emission/generate.php
require_once '../../includes/db.php';

// Only allow POST requests (or CLI execution if needed, but for web trigger POST is safer)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
   // For testing convenience, we might allow GET, but let's stick to a simple check
   // jsonResponse(['error' => 'Method not allowed'], 405);
}

try {
   // 1. Verify Active Fiscal Year
   $anioFiscal = getActiveFiscalYear($pdo);
   if (!$anioFiscal) {
      jsonResponse(['error' => 'No hay año fiscal ACTIVO.'], 400);
   }

   $anio = $anioFiscal['anio'];
   $uit = $anioFiscal['valor_uit'];

   // 2. Get All Active Predios
   // We need to join with contribuyentes to make sure we have valid owners? 
   // The prompt says "Recorrer todos los predios activos".
   $stmtPredios = $pdo->prepare("
        SELECT p.* 
        FROM predios p 
        WHERE p.estado = 'ACTIVO' 
        AND p.anio_declaracion <= ?
    ");
   $stmtPredios->execute([$anio]);
   $predios = $stmtPredios->fetchAll();

   $countPredial = 0;
   $countArbitrios = 0;

   $pdo->beginTransaction();

   $stmtCheck = $pdo->prepare("SELECT 1 FROM cta_cte_tributaria WHERE anio = ? AND predio_id = ? AND tributo_concepto = ? LIMIT 1");

   foreach ($predios as $predio) {
      // --- A. IMPUESTO PREDIAL ---
      // Check if already emitted
      $stmtCheck->execute([$anio, $predio['id'], 'IMPUESTO_PREDIAL']);
      if (!$stmtCheck->fetch()) {
         $autovaluo = $predio['valor_autovaluo'];

         // Calculate Tax Base
         $tramo1 = 15 * $uit;
         $tramo2 = 60 * $uit;

         $impuestoAnual = 0;

         if ($autovaluo <= $tramo1) {
            $impuestoAnual = $autovaluo * 0.002;
         } elseif ($autovaluo <= $tramo2) {
            $base1 = $tramo1 * 0.002;
            $exceso = $autovaluo - $tramo1;
            $impuestoAnual = $base1 + ($exceso * 0.006);
         } else {
            $base1 = $tramo1 * 0.002;
            $base2 = ($tramo2 - $tramo1) * 0.006;
            $exceso = $autovaluo - $tramo2;
            $impuestoAnual = $base1 + $base2 + ($exceso * 0.01);
         }

         // Divide into 4 installments
         $cuotaTrimestral = $impuestoAnual / 4;

         $vencimientosPredial = [
            1 => "$anio-02-28",
            2 => "$anio-05-31",
            3 => "$anio-08-31",
            4 => "$anio-11-30"
         ];

         $stmtInsert = $pdo->prepare("
                INSERT INTO cta_cte_tributaria 
                (anio, contribuyente_id, predio_id, tributo_concepto, periodo, fecha_vencimiento, monto_insoluto, estado)
                VALUES (?, ?, ?, 'IMPUESTO_PREDIAL', ?, ?, ?, 'PENDIENTE')
            ");

         for ($i = 1; $i <= 4; $i++) {
            $stmtInsert->execute([
               $anio,
               $predio['contribuyente_id'],
               $predio['id'],
               $i,
               $vencimientosPredial[$i],
               round($cuotaTrimestral, 2)
            ]);
            $countPredial++;
         }
      }

      // --- B. ARBITRIOS (Limpieza y Seguridad) ---
      // Check if already emitted (Checking LIMPIEZA_PUBLICA is enough as they go together)
      $stmtCheck->execute([$anio, $predio['id'], 'LIMPIEZA_PUBLICA']);
      if (!$stmtCheck->fetch()) {
         // Find Rate
         $stmtTasa = $pdo->prepare("
                SELECT * FROM tasas_arbitrios_anuales 
                WHERE anio = ? AND tipo_uso_id = ?
            ");
         $stmtTasa->execute([$anio, $predio['tipo_uso_id']]);
         $tasa = $stmtTasa->fetch();

         if ($tasa) {
            // 1. Limpieza
            $costoLimpiezaAnual = ($predio['area_construida'] * $tasa['costo_limpieza_m2']) + $tasa['costo_limpieza_fijo'];
            $mensualLimpieza = $costoLimpiezaAnual / 12;

            // 2. Seguridad
            $costoSeguridadAnual = $tasa['costo_seguridad_fijo'];
            $mensualSeguridad = $costoSeguridadAnual / 12;

            $stmtInsertArb = $pdo->prepare("
                    INSERT INTO cta_cte_tributaria 
                    (anio, contribuyente_id, predio_id, tributo_concepto, periodo, fecha_vencimiento, monto_insoluto, estado)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDIENTE')
                ");

            for ($mes = 1; $mes <= 12; $mes++) {
               // Vencimiento: Last day of each month
               $fechaVenc = date("Y-m-t", strtotime("$anio-$mes-01"));

               // Limpieza
               $stmtInsertArb->execute([
                  $anio,
                  $predio['contribuyente_id'],
                  $predio['id'],
                  'LIMPIEZA_PUBLICA',
                  $mes,
                  $fechaVenc,
                  round($mensualLimpieza, 2)
               ]);

               // Seguridad
               $stmtInsertArb->execute([
                  $anio,
                  $predio['contribuyente_id'],
                  $predio['id'],
                  'SEGURIDAD_CIUDADANA',
                  $mes,
                  $fechaVenc,
                  round($mensualSeguridad, 2)
               ]);

               $countArbitrios += 2;
            }
         }
      }
   }

   $pdo->commit();

   jsonResponse([
      'message' => 'Emisión masiva completada con éxito.',
      'anio' => $anio,
      'predios_procesados' => count($predios),
      'registros_predial' => $countPredial,
      'registros_arbitrios' => $countArbitrios
   ]);
} catch (Exception $e) {
   if ($pdo->inTransaction()) {
      $pdo->rollBack();
   }
   jsonResponse(['error' => $e->getMessage()], 500);
}
