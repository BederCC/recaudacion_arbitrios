<?php
// admin/reportes/estado_cuenta.php
require_once '../../includes/db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
   die("ID de contribuyente requerido.");
}

// Fetch Contribuyente
$stmtC = $pdo->prepare("SELECT * FROM contribuyentes WHERE id = ?");
$stmtC->execute([$id]);
$contribuyente = $stmtC->fetch();

if (!$contribuyente) {
   die("Contribuyente no encontrado.");
}

// Fetch Debts
$stmtD = $pdo->prepare("
    SELECT c.*, p.direccion_predio, p.codigo_catastral
    FROM cta_cte_tributaria c
    JOIN predios p ON c.predio_id = p.id
    WHERE c.contribuyente_id = ?
    ORDER BY c.anio DESC, c.periodo ASC
");
$stmtD->execute([$id]);
$deudas = $stmtD->fetchAll();

$totalDeuda = 0;
?>
<!DOCTYPE html>
<html lang="es">

<head>
   <meta charset="UTF-8">
   <title>Estado de Cuenta - <?php echo $contribuyente['numero_documento']; ?></title>
   <link href="../../src/output.css" rel="stylesheet">
   <style>
      @media print {
         .no-print {
            display: none;
         }

         body {
            background: white;
         }
      }
   </style>
</head>

<body class="bg-gray-100 p-8">

   <div class="max-w-4xl mx-auto bg-white shadow-lg p-8">

      <!-- Header -->
      <div class="flex justify-between items-center border-b pb-4 mb-6">
         <div class="flex items-center">
            <div class="text-3xl mr-4">🏛️</div>
            <div>
               <h1 class="text-xl font-bold uppercase">Municipalidad Distrital de Oropesa</h1>
               <p class="text-sm text-gray-500">Gerencia de Administración Tributaria y Rentas</p>
            </div>
         </div>
         <div class="text-right">
            <h2 class="text-2xl font-bold text-gray-800">ESTADO DE CUENTA</h2>
            <p class="text-sm text-gray-500">Fecha: <?php echo date('d/m/Y H:i'); ?></p>
         </div>
      </div>

      <!-- Taxpayer Info -->
      <div class="bg-gray-50 p-4 rounded mb-6 border">
         <div class="grid grid-cols-2 gap-4">
            <div>
               <p class="text-xs text-gray-500 uppercase">Contribuyente</p>
               <p class="font-bold text-lg"><?php echo $contribuyente['nombre_razon_social']; ?></p>
            </div>
            <div>
               <p class="text-xs text-gray-500 uppercase">Documento</p>
               <p class="font-bold"><?php echo $contribuyente['tipo_persona']; ?> - <?php echo $contribuyente['numero_documento']; ?></p>
            </div>
            <div>
               <p class="text-xs text-gray-500 uppercase">Dirección Fiscal</p>
               <p class="font-bold"><?php echo $contribuyente['direccion_fiscal']; ?></p>
            </div>
            <div>
               <p class="text-xs text-gray-500 uppercase">Código Contribuyente</p>
               <p class="font-bold"><?php echo str_pad($contribuyente['id'], 6, '0', STR_PAD_LEFT); ?></p>
            </div>
         </div>
      </div>

      <!-- Debts Table -->
      <table class="w-full mb-6 text-sm">
         <thead>
            <tr class="border-b-2 border-gray-800">
               <th class="py-2 text-left">Año</th>
               <th class="py-2 text-left">Concepto</th>
               <th class="py-2 text-left">Predio</th>
               <th class="py-2 text-center">Periodo</th>
               <th class="py-2 text-center">Vencimiento</th>
               <th class="py-2 text-center">Estado</th>
               <th class="py-2 text-right">Monto</th>
            </tr>
         </thead>
         <tbody>
            <?php foreach ($deudas as $d):
               if ($d['estado'] == 'PENDIENTE') $totalDeuda += $d['monto_insoluto'];
            ?>
               <tr class="border-b border-gray-200">
                  <td class="py-2"><?php echo $d['anio']; ?></td>
                  <td class="py-2"><?php echo str_replace('_', ' ', $d['tributo_concepto']); ?></td>
                  <td class="py-2">
                     <div class="text-xs"><?php echo $d['direccion_predio']; ?></div>
                     <div class="text-xs text-gray-500"><?php echo $d['codigo_catastral']; ?></div>
                  </td>
                  <td class="py-2 text-center"><?php echo $d['periodo']; ?></td>
                  <td class="py-2 text-center"><?php echo $d['fecha_vencimiento']; ?></td>
                  <td class="py-2 text-center">
                     <span class="<?php echo $d['estado'] == 'PENDIENTE' ? 'text-red-600 font-bold' : 'text-green-600'; ?>">
                        <?php echo $d['estado']; ?>
                     </span>
                  </td>
                  <td class="py-2 text-right font-mono">
                     <?php echo number_format($d['monto_insoluto'], 2); ?>
                  </td>
               </tr>
            <?php endforeach; ?>
         </tbody>
         <tfoot>
            <tr class="border-t-2 border-gray-800">
               <td colspan="6" class="py-4 text-right font-bold uppercase">Total Deuda Pendiente (Sin Mora):</td>
               <td class="py-4 text-right font-bold text-xl">S/ <?php echo number_format($totalDeuda, 2); ?></td>
            </tr>
         </tfoot>
      </table>

      <div class="text-center mt-8 no-print">
         <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-8 rounded shadow">
            IMPRIMIR / DESCARGAR PDF
         </button>
      </div>

   </div>

</body>

</html>