<?php
// admin/emision/index.php
require_once '../includes/layout.php';
renderHeader('Emisión Masiva');
?>

<div x-data="emisionApp()" x-init="checkStatus()" class="container mx-auto">
   <div class="mb-8 text-center">
      <h1 class="text-3xl font-bold text-gray-800">Emisión Masiva de Tributos</h1>
      <p class="text-gray-600 mt-2">Generación de Impuesto Predial y Arbitrios Municipales</p>
   </div>

   <div class="max-w-3xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
      <div class="p-8">
         <!-- Loading -->
         <div x-show="loading" class="text-center py-4">
            <p class="text-gray-500">Cargando información...</p>
         </div>

         <!-- Active Year Found -->
         <div x-show="!loading && anio" style="display: none;">
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
               <div class="flex">
                  <div class="shrink-0">
                     <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                     </svg>
                  </div>
                  <div class="ml-3">
                     <p class="text-sm text-blue-700">
                        Año Fiscal Activo: <strong class="text-lg" x-text="anio?.anio"></strong>
                     </p>
                     <p class="text-xs text-blue-600 mt-1">
                        UIT: S/ <span x-text="parseFloat(anio?.valor_uit).toFixed(2)"></span>
                     </p>
                  </div>
               </div>
            </div>

            <div class="text-center">
               <!-- Progress -->
               <div x-show="processing" class="mb-6">
                  <div class="w-full bg-gray-200 rounded-full h-4 mb-2">
                     <div class="bg-blue-600 h-4 rounded-full transition-all duration-500" :style="'width: ' + progress + '%'"></div>
                  </div>
                  <p class="text-sm text-gray-600">Procesando...</p>
               </div>

               <!-- Result -->
               <div x-show="result" class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative text-left" style="display: none;">
                  <strong class="font-bold">¡Emisión Completada!</strong>
                  <ul class="list-disc list-inside mt-2 text-sm">
                     <li>Año: <span x-text="result?.anio"></span></li>
                     <li>Predios Procesados: <span x-text="result?.predios_procesados"></span></li>
                     <li>Deudas Predial Generadas: <span x-text="result?.registros_predial"></span></li>
                     <li>Deudas Arbitrios Generadas: <span x-text="result?.registros_arbitrios"></span></li>
                  </ul>
               </div>

               <button @click="startEmission" :disabled="processing || result" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-8 rounded-full shadow-lg transform transition hover:scale-105 flex items-center justify-center mx-auto disabled:opacity-50 disabled:cursor-not-allowed">
                  <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                  </svg>
                  PROCESAR EMISIÓN ANUAL
               </button>
            </div>
         </div>

         <!-- No Active Year -->
         <div x-show="!loading && !anio" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-center" style="display: none;">
            <strong class="font-bold">Error:</strong>
            <span class="block sm:inline">No hay un Año Fiscal ACTIVO configurado.</span>
            <a href="../config/anios.php" class="underline font-bold mt-2 block">Ir a Configuración</a>
         </div>
      </div>
   </div>
</div>

<script>
   function emisionApp() {
      return {
         loading: true,
         anio: null,
         processing: false,
         progress: 0,
         result: null,

         async checkStatus() {
            try {
               const res = await fetch('../../api/admin/emision.php');
               const data = await res.json();
               this.anio = data.anio;
            } catch (e) {
               console.error(e);
            } finally {
               this.loading = false;
            }
         },

         async startEmission() {
            if (!confirm('¿Está seguro de iniciar la emisión masiva? Esto generará deuda para todos los contribuyentes activos.')) {
               return;
            }

            this.processing = true;
            this.result = null;
            this.progress = 0;

            // Simulate progress
            const interval = setInterval(() => {
               if (this.progress >= 90) return;
               this.progress += 5;
            }, 200);

            try {
               const response = await fetch('../../api/emission/generate.php', {
                  method: 'POST'
               });
               const data = await response.json();

               clearInterval(interval);
               this.progress = 100;

               if (data.error) {
                  alert('Error: ' + data.error);
                  this.processing = false;
               } else {
                  this.result = data;
                  // Keep processing true to disable button, but show result
               }
            } catch (e) {
               alert('Error de conexión con el servidor.');
               this.processing = false;
            }
         }
      }
   }
</script>

<?php renderFooter(); ?>