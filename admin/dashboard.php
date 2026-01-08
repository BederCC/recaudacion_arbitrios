<?php
// admin/dashboard.php
require_once 'includes/layout.php';
require_once '../includes/db.php';

renderHeader('Dashboard');

// 1. Recaudación del Día (Pagos en recibos hoy)
$stmtHoy = $pdo->query("
    SELECT COALESCE(SUM(monto_total), 0) 
    FROM recibos 
    WHERE DATE(fecha_pago) = CURDATE() AND estado = 'VALIDO'
");
$recaudacionHoy = $stmtHoy->fetchColumn();

// 2. Deuda Total del Año (Pendiente)
// Get active year first
$stmtAnio = $pdo->query("SELECT anio FROM anios_fiscales WHERE estado = 'ACTIVO' LIMIT 1");
$anioActivo = $stmtAnio->fetchColumn();

$deudaTotal = 0;
if ($anioActivo) {
   $stmtDeuda = $pdo->prepare("
        SELECT COALESCE(SUM(monto_insoluto), 0) 
        FROM cta_cte_tributaria 
        WHERE anio = ? AND estado = 'PENDIENTE'
    ");
   $stmtDeuda->execute([$anioActivo]);
   $deudaTotal = $stmtDeuda->fetchColumn();
}

// 3. Morosidad (Pagado vs Pendiente)
$pagado = 0;
$pendiente = 0;
if ($anioActivo) {
   // Let's get amounts for the chart
   $stmtPieMonto = $pdo->prepare("
        SELECT estado, SUM(monto_insoluto) as monto
        FROM cta_cte_tributaria 
        WHERE anio = ?
        GROUP BY estado
    ");
   $stmtPieMonto->execute([$anioActivo]);
   $montos = $stmtPieMonto->fetchAll(PDO::FETCH_KEY_PAIR);

   $pagado = $montos['PAGADO'] ?? 0;
   $pendiente = $montos['PENDIENTE'] ?? 0;
}

// 4. Top Morosos
$topMorosos = [];
if ($anioActivo) {
   $stmtTop = $pdo->prepare("
        SELECT c.nombre_razon_social, SUM(ct.monto_insoluto) as total_deuda
        FROM cta_cte_tributaria ct
        JOIN contribuyentes c ON ct.contribuyente_id = c.id
        WHERE ct.anio = ? AND ct.estado = 'PENDIENTE'
        GROUP BY c.id
        ORDER BY total_deuda DESC
        LIMIT 5
    ");
   $stmtTop->execute([$anioActivo]);
   $topMorosos = $stmtTop->fetchAll();
}
?>

<div class="mb-8">
   <h1 class="text-3xl font-bold text-gray-800">Panel de Control</h1>
   <p class="text-gray-500">Resumen de la recaudación tributaria</p>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
   <!-- Card 1 -->
   <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
      <div class="flex items-center">
         <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
         </div>
         <div>
            <p class="text-sm text-gray-500 font-medium">Recaudación del Día</p>
            <p class="text-2xl font-bold text-gray-800">S/ <?php echo number_format($recaudacionHoy, 2); ?></p>
         </div>
      </div>
   </div>

   <!-- Card 2 -->
   <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
      <div class="flex items-center">
         <div class="p-3 rounded-full bg-red-100 text-red-500 mr-4">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
            </svg>
         </div>
         <div>
            <p class="text-sm text-gray-500 font-medium">Deuda Total (<?php echo $anioActivo; ?>)</p>
            <p class="text-2xl font-bold text-gray-800">S/ <?php echo number_format($deudaTotal, 2); ?></p>
         </div>
      </div>
   </div>

   <!-- Card 3 -->
   <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
      <div class="flex items-center">
         <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
         </div>
         <div>
            <p class="text-sm text-gray-500 font-medium">Año Fiscal Activo</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $anioActivo ?: 'NINGUNO'; ?></p>
         </div>
      </div>
   </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
   <!-- Chart -->
   <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center justify-center">
      <h3 class="text-lg font-bold text-gray-700 mb-4 self-start">Estado de la Cartera (Soles)</h3>
      <div class="w-64 h-64">
         <canvas id="morosidadChart"></canvas>
      </div>
   </div>

   <!-- Top Debtors -->
   <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-bold text-gray-700 mb-4">Top 5 Deudores</h3>
      <div class="overflow-x-auto">
         <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
               <tr>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Contribuyente</th>
                  <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Deuda Total</th>
               </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
               <?php foreach ($topMorosos as $moroso): ?>
                  <tr>
                     <td class="px-4 py-3 text-sm text-gray-900"><?php echo $moroso['nombre_razon_social']; ?></td>
                     <td class="px-4 py-3 text-sm text-right font-bold text-red-600">S/ <?php echo number_format($moroso['total_deuda'], 2); ?></td>
                  </tr>
               <?php endforeach; ?>
               <?php if (empty($topMorosos)): ?>
                  <tr>
                     <td colspan="2" class="px-4 py-3 text-center text-sm text-gray-500">No hay datos</td>
                  </tr>
               <?php endif; ?>
            </tbody>
         </table>
      </div>
   </div>
</div>

<script>
   const ctx = document.getElementById('morosidadChart').getContext('2d');
   const myChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
         labels: ['Pagado', 'Pendiente'],
         datasets: [{
            data: [<?php echo $pagado; ?>, <?php echo $pendiente; ?>],
            backgroundColor: [
               'rgba(34, 197, 94, 0.8)', // Green
               'rgba(239, 68, 68, 0.8)' // Red
            ],
            borderColor: [
               'rgba(34, 197, 94, 1)',
               'rgba(239, 68, 68, 1)'
            ],
            borderWidth: 1
         }]
      },
      options: {
         responsive: true,
         maintainAspectRatio: false,
         plugins: {
            legend: {
               position: 'bottom',
            }
         }
      }
   });
</script>

<?php renderFooter(); ?>