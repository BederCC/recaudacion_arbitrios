<?php
// admin/caja/index.php
require_once '../includes/layout.php';
renderHeader('Caja y Tesorería');
?>

<div x-data="cajaApp()" x-init="checkSession()" class="container mx-auto">
   <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Módulo de Caja</h1>
      <p class="text-gray-600">Gestión de Cobranza en Ventanilla</p>
   </div>

   <!-- Loading State -->
   <div x-show="loadingSession" class="text-center py-10">
      <p class="text-gray-500">Cargando estado de caja...</p>
   </div>

   <!-- Closed State -->
   <div x-show="!loadingSession && !sesion" class="flex flex-col items-center justify-center h-64 bg-white shadow rounded-lg" style="display: none;">
      <div class="p-4 bg-yellow-100 rounded-full mb-4">
         <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
         </svg>
      </div>
      <h2 class="text-xl font-bold text-gray-700 mb-2">Caja Cerrada</h2>
      <p class="text-gray-500 mb-6">Debe abrir caja para comenzar a cobrar.</p>
      <button @click="abrirCaja" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg shadow transition transform hover:scale-105">
         ABRIR CAJA
      </button>
   </div>

   <!-- Open State -->
   <div x-show="!loadingSession && sesion" style="display: none;">
      <div class="flex justify-between items-center bg-green-50 border border-green-200 p-4 rounded-lg mb-6">
         <div>
            <p class="text-green-800 font-bold">🟢 CAJA ABIERTA</p>
            <p class="text-sm text-green-600">Sesión ID: #<span x-text="sesion?.id"></span> | Apertura: <span x-text="sesion?.fecha_apertura"></span></p>
         </div>
         <button @click="cerrarCaja" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded text-sm">
            CERRAR CAJA
         </button>
      </div>

      <!-- POS Interface -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
         <div class="relative">
            <input
               type="text"
               x-model="searchQuery"
               @input.debounce.500ms="searchContribuyente"
               class="w-full border border-gray-300 rounded-lg py-3 px-4 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg"
               placeholder="Buscar Contribuyente (DNI, RUC, Nombre)...">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
               <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
               </svg>
            </div>

            <!-- Results -->
            <div x-show="searchResults.length > 0" class="absolute z-10 w-full bg-white shadow-xl rounded-b-lg border border-t-0 border-gray-200 mt-1 max-h-60 overflow-y-auto">
               <ul>
                  <template x-for="result in searchResults" :key="result.id">
                     <li @click="selectContribuyente(result)" class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b last:border-b-0">
                        <div class="font-bold text-gray-800" x-text="result.nombre_razon_social"></div>
                        <div class="text-sm text-gray-500">
                           <span x-text="result.tipo_persona"></span> - <span x-text="result.numero_documento"></span>
                        </div>
                     </li>
                  </template>
               </ul>
            </div>
         </div>
      </div>

      <!-- Selected Taxpayer & Debts -->
      <div x-show="contribuyente" style="display: none;">
         <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 flex justify-between items-start">
            <div>
               <h3 class="text-lg font-bold text-blue-900" x-text="contribuyente?.nombre_razon_social"></h3>
               <p class="text-sm text-blue-700">DOC: <span x-text="contribuyente?.numero_documento"></span></p>
            </div>
            <button @click="reset" class="text-sm text-red-600 hover:text-red-800 underline">Limpiar</button>
         </div>

         <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">Deudas Pendientes</h2>

            <!-- Global Select All -->
            <div class="flex items-center bg-white px-4 py-2 rounded shadow cursor-pointer hover:bg-gray-50" @click="toggleAll()">
               <input type="checkbox" :checked="allSelected" class="rounded text-blue-600 focus:ring-blue-500 h-5 w-5 cursor-pointer">
               <span class="ml-2 font-bold text-gray-700 select-none">Seleccionar Todo</span>
            </div>
         </div>

         <!-- Impuesto Predial Box -->
         <div class="bg-white shadow rounded-lg overflow-hidden mb-6" x-show="groupedDebts.predial.length > 0">
            <div class="bg-blue-100 px-4 py-3 font-bold text-blue-800 border-b border-blue-200 flex items-center">
               <span class="text-xl mr-2">🏠</span> Impuesto Predial
            </div>
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seleccionar</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Concepto</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periodo</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Insoluto</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Mora</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                  </tr>
               </thead>
               <tbody class="bg-white divide-y divide-gray-200">
                  <template x-for="deuda in groupedDebts.predial" :key="deuda.id">
                     <tr :class="{'bg-blue-50': deuda.checked}">
                        <td class="px-6 py-4"><input type="checkbox" x-model="deuda.checked"></td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                           <span x-text="deuda.concepto"></span>
                           <div class="text-xs text-gray-500" x-text="deuda.predio"></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500" x-text="deuda.anio + ' - ' + deuda.periodo"></td>
                        <td class="px-6 py-4 text-sm text-right font-mono" x-text="deuda.monto_insoluto"></td>
                        <td class="px-6 py-4 text-sm text-right font-mono text-red-600" x-text="deuda.mora"></td>
                        <td class="px-6 py-4 text-sm text-right font-bold font-mono" x-text="deuda.total"></td>
                     </tr>
                  </template>
               </tbody>
            </table>
         </div>

         <!-- Limpieza Box -->
         <div class="bg-white shadow rounded-lg overflow-hidden mb-6" x-show="groupedDebts.limpieza.length > 0">
            <div class="bg-green-100 px-4 py-3 font-bold text-green-800 border-b border-green-200 flex items-center">
               <span class="text-xl mr-2">🧹</span> Limpieza Pública
            </div>
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seleccionar</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Concepto</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periodo</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Insoluto</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Mora</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                  </tr>
               </thead>
               <tbody class="bg-white divide-y divide-gray-200">
                  <template x-for="deuda in groupedDebts.limpieza" :key="deuda.id">
                     <tr :class="{'bg-blue-50': deuda.checked}">
                        <td class="px-6 py-4"><input type="checkbox" x-model="deuda.checked"></td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                           <span x-text="deuda.concepto"></span>
                           <div class="text-xs text-gray-500" x-text="deuda.predio"></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500" x-text="deuda.anio + ' - ' + deuda.periodo"></td>
                        <td class="px-6 py-4 text-sm text-right font-mono" x-text="deuda.monto_insoluto"></td>
                        <td class="px-6 py-4 text-sm text-right font-mono text-red-600" x-text="deuda.mora"></td>
                        <td class="px-6 py-4 text-sm text-right font-bold font-mono" x-text="deuda.total"></td>
                     </tr>
                  </template>
               </tbody>
            </table>
         </div>

         <!-- Seguridad Box -->
         <div class="bg-white shadow rounded-lg overflow-hidden mb-6" x-show="groupedDebts.seguridad.length > 0">
            <div class="bg-yellow-100 px-4 py-3 font-bold text-yellow-800 border-b border-yellow-200 flex items-center">
               <span class="text-xl mr-2">🛡️</span> Seguridad Ciudadana
            </div>
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seleccionar</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Concepto</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periodo</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Insoluto</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Mora</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                  </tr>
               </thead>
               <tbody class="bg-white divide-y divide-gray-200">
                  <template x-for="deuda in groupedDebts.seguridad" :key="deuda.id">
                     <tr :class="{'bg-blue-50': deuda.checked}">
                        <td class="px-6 py-4"><input type="checkbox" x-model="deuda.checked"></td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                           <span x-text="deuda.concepto"></span>
                           <div class="text-xs text-gray-500" x-text="deuda.predio"></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500" x-text="deuda.anio + ' - ' + deuda.periodo"></td>
                        <td class="px-6 py-4 text-sm text-right font-mono" x-text="deuda.monto_insoluto"></td>
                        <td class="px-6 py-4 text-sm text-right font-mono text-red-600" x-text="deuda.mora"></td>
                        <td class="px-6 py-4 text-sm text-right font-bold font-mono" x-text="deuda.total"></td>
                     </tr>
                  </template>
               </tbody>
            </table>
         </div>

         <!-- Otros Box -->
         <div class="bg-white shadow rounded-lg overflow-hidden mb-6" x-show="groupedDebts.otros.length > 0">
            <div class="bg-gray-100 px-4 py-3 font-bold text-gray-800 border-b border-gray-200 flex items-center">
               <span class="text-xl mr-2">📋</span> Otros Conceptos
            </div>
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seleccionar</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Concepto</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periodo</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Insoluto</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Mora</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                  </tr>
               </thead>
               <tbody class="bg-white divide-y divide-gray-200">
                  <template x-for="deuda in groupedDebts.otros" :key="deuda.id">
                     <tr :class="{'bg-blue-50': deuda.checked}">
                        <td class="px-6 py-4"><input type="checkbox" x-model="deuda.checked"></td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                           <span x-text="deuda.concepto"></span>
                           <div class="text-xs text-gray-500" x-text="deuda.predio"></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500" x-text="deuda.anio + ' - ' + deuda.periodo"></td>
                        <td class="px-6 py-4 text-sm text-right font-mono" x-text="deuda.monto_insoluto"></td>
                        <td class="px-6 py-4 text-sm text-right font-mono text-red-600" x-text="deuda.mora"></td>
                        <td class="px-6 py-4 text-sm text-right font-bold font-mono" x-text="deuda.total"></td>
                     </tr>
                  </template>
               </tbody>
            </table>
         </div>

         <!-- Footer Actions -->
         <div class="bg-gray-800 text-white p-4 rounded-lg shadow-lg flex justify-between items-center sticky bottom-4">
            <div>
               <p class="text-sm text-gray-400">Total a Cobrar</p>
               <p class="text-3xl font-bold" x-text="'S/ ' + formatMoney(totalSelected)"></p>
            </div>
            <button @click="processPayment" :disabled="totalSelected <= 0 || processing" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-8 rounded shadow disabled:opacity-50">
               <span x-show="!processing">COBRAR</span>
               <span x-show="processing">...</span>
            </button>
         </div>
      </div>
   </div>
</div>

<script>
   function cajaApp() {
      return {
         loadingSession: true,
         sesion: null,

         searchQuery: '',
         searchResults: [],
         contribuyente: null,
         deudas: [],
         allSelected: false,
         processing: false,

         async checkSession() {
            try {
               const res = await fetch('../../api/admin/caja.php');
               const data = await res.json();
               this.sesion = data.sesion;
            } catch (e) {
               console.error(e);
            } finally {
               this.loadingSession = false;
            }
         },

         async abrirCaja() {
            try {
               const res = await fetch('../../api/admin/caja.php', {
                  method: 'POST',
                  headers: {
                     'Content-Type': 'application/json'
                  },
                  body: JSON.stringify({
                     accion: 'abrir'
                  })
               });
               const data = await res.json();
               if (data.success) {
                  this.checkSession();
               } else {
                  alert(data.error);
               }
            } catch (e) {
               alert('Error al abrir caja');
            }
         },

         async cerrarCaja() {
            if (!confirm('¿Seguro que desea cerrar caja?')) return;
            try {
               const res = await fetch('../../api/admin/caja.php', {
                  method: 'POST',
                  headers: {
                     'Content-Type': 'application/json'
                  },
                  body: JSON.stringify({
                     accion: 'cerrar',
                     sesion_id: this.sesion.id
                  })
               });
               const data = await res.json();
               if (data.success) {
                  alert(data.message);
                  this.sesion = null;
                  this.reset();
               } else {
                  alert(data.error);
               }
            } catch (e) {
               alert('Error al cerrar caja');
            }
         },

         async searchContribuyente() {
            if (this.searchQuery.length < 3) {
               this.searchResults = [];
               return;
            }
            const res = await fetch(`../../api/admin/contribuyentes.php?q=${this.searchQuery}`);
            this.searchResults = await res.json();
         },

         async selectContribuyente(c) {
            this.contribuyente = c;
            this.searchResults = [];
            this.searchQuery = '';
            const res = await fetch(`../../api/search/debts.php?contribuyente_id=${c.id}`);
            let data = await res.json();

            // Sort: Impuesto Predial first, then by Year Descending, then Period Ascending
            data.sort((a, b) => {
               if (b.anio !== a.anio) return b.anio - a.anio;
               return a.periodo - b.periodo;
            });

            this.deudas = data;
         },

         reset() {
            this.contribuyente = null;
            this.deudas = [];
            this.allSelected = false;
         },

         get groupedDebts() {
            return {
               predial: this.deudas.filter(d => d.concepto.includes('PREDIAL')),
               limpieza: this.deudas.filter(d => d.concepto.includes('LIMPIEZA')),
               seguridad: this.deudas.filter(d => d.concepto.includes('SEGURIDAD')),
               otros: this.deudas.filter(d => !d.concepto.includes('PREDIAL') && !d.concepto.includes('LIMPIEZA') && !d.concepto.includes('SEGURIDAD'))
            };
         },

         toggleAll() {
            const allChecked = this.allSelected;
            this.deudas.forEach(d => d.checked = !allChecked);
         },

         get allSelected() {
            return this.deudas.length > 0 && this.deudas.every(d => d.checked);
         },

         get totalSelected() {
            return this.deudas.filter(d => d.checked).reduce((sum, d) => sum + parseFloat(d.total), 0);
         },

         formatMoney(amount) {
            return parseFloat(amount).toFixed(2);
         },

         async processPayment() {
            if (!confirm('¿Confirmar cobro de S/ ' + this.formatMoney(this.totalSelected) + '?')) return;

            this.processing = true;
            const selectedDebts = this.deudas.filter(d => d.checked);

            const payload = {
               contribuyente_id: this.contribuyente.id,
               items: selectedDebts,
               monto_total: this.totalSelected,
               metodo_pago: 'EFECTIVO',
               origen: 'VENTANILLA_MUNICIPAL',
               caja_sesion_id: this.sesion.id
            };

            try {
               const res = await fetch('../../api/payment/process.php', {
                  method: 'POST',
                  headers: {
                     'Content-Type': 'application/json'
                  },
                  body: JSON.stringify(payload)
               });
               const data = await res.json();

               if (data.success) {
                  alert(`COBRO EXITOSO\nRecibo N°: ${data.numero_serie}`);
                  this.reset();
               } else {
                  alert('Error: ' + data.error);
               }
            } catch (e) {
               alert('Error de conexión');
            } finally {
               this.processing = false;
            }
         }
      }
   }
</script>

<?php renderFooter(); ?>