<?php
// admin/reportes/ingresos.php
require_once '../includes/layout.php';
renderHeader('Reporte de Ingresos');
?>

<div x-data="reportesApp()" x-init="fetchData()" class="container mx-auto">
   <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Reporte de Ingresos</h1>
      <p class="text-gray-600">Recaudación por rango de fechas</p>
   </div>

   <div class="bg-white shadow rounded-lg p-4 mb-6">
      <div class="flex flex-wrap gap-4 items-end">
         <div>
            <label class="block text-sm font-bold text-gray-700">Desde</label>
            <input type="date" x-model="desde" class="border rounded px-3 py-2">
         </div>
         <div>
            <label class="block text-sm font-bold text-gray-700">Hasta</label>
            <input type="date" x-model="hasta" class="border rounded px-3 py-2">
         </div>
         <button @click="fetchData()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
            Filtrar
         </button>
         <button @click="window.print()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded">
            Imprimir
         </button>
      </div>
   </div>

   <div class="bg-white shadow-md rounded overflow-hidden">
      <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
         <span class="font-bold text-gray-700">Resultados</span>
         <span class="text-xl font-bold text-green-600">Total: S/ <span x-text="parseFloat(total).toFixed(2)"></span></span>
      </div>
      <table class="min-w-full table-auto">
         <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
               <th class="py-3 px-6 text-left">Fecha</th>
               <th class="py-3 px-6 text-left">N° Recibo</th>
               <th class="py-3 px-6 text-left">Contribuyente</th>
               <th class="py-3 px-6 text-center">Método</th>
               <th class="py-3 px-6 text-right">Monto</th>
            </tr>
         </thead>
         <tbody class="text-gray-600 text-sm font-light">
            <template x-for="row in items" :key="row.numero_serie">
               <tr class="border-b border-gray-200 hover:bg-gray-100">
                  <td class="py-3 px-6 text-left whitespace-nowrap" x-text="row.fecha_pago"></td>
                  <td class="py-3 px-6 text-left font-mono" x-text="row.numero_serie"></td>
                  <td class="py-3 px-6 text-left" x-text="row.nombre_razon_social"></td>
                  <td class="py-3 px-6 text-center" x-text="row.metodo_pago"></td>
                  <td class="py-3 px-6 text-right font-bold" x-text="'S/ ' + parseFloat(row.monto_total).toFixed(2)"></td>
               </tr>
            </template>
            <tr x-show="items.length === 0">
               <td colspan="5" class="py-6 text-center text-gray-500">No se encontraron registros en este rango.</td>
            </tr>
         </tbody>
      </table>
   </div>
</div>

<script>
   function reportesApp() {
      return {
         desde: new Date().toISOString().slice(0, 7) + '-01',
         hasta: new Date().toISOString().slice(0, 10),
         items: [],
         total: 0,

         async fetchData() {
            try {
               const res = await fetch(`../../api/admin/reportes.php?desde=${this.desde}&hasta=${this.hasta}`);
               const data = await res.json();
               this.items = data.ingresos;
               this.total = data.total;
            } catch (e) {
               console.error(e);
               alert('Error al cargar reporte');
            }
         }
      }
   }
</script>

<?php renderFooter(); ?>