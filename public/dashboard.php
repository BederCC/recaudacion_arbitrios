<!DOCTYPE html>
<html lang="es">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>SART - Módulo de Caja</title>
   <link href="../src/output.css" rel="stylesheet">
   <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-gray-100 font-sans antialiased" x-data="cajaApp()">

   <!-- Navbar -->
   <nav class="bg-blue-900 text-white shadow-lg">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
         <div class="flex justify-between h-16">
            <div class="flex items-center">
               <span class="text-xl font-bold tracking-wider">🏛️ MUNICIPALIDAD DISTRITAL X</span>
            </div>
            <div class="flex items-center">
               <span class="text-sm bg-blue-800 py-1 px-3 rounded-full">Cajero: <strong>VENTANILLA 01</strong></span>
            </div>
         </div>
      </div>
   </nav>

   <!-- Main Content -->
   <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">

      <!-- Search Section -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
         <h2 class="text-lg font-semibold text-gray-700 mb-4">Buscar Contribuyente</h2>
         <div class="relative">
            <input
               type="text"
               x-model="searchQuery"
               @input.debounce.500ms="searchContribuyente"
               class="w-full border border-gray-300 rounded-lg py-3 px-4 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg"
               placeholder="Ingrese DNI, RUC o Apellidos...">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
               <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
               </svg>
            </div>

            <!-- Search Results Dropdown -->
            <div x-show="searchResults.length > 0" class="absolute z-10 w-full bg-white shadow-xl rounded-b-lg border border-t-0 border-gray-200 mt-1 max-h-60 overflow-y-auto" style="display: none;">
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

      <!-- Taxpayer Info & Debts -->
      <div x-show="contribuyente" style="display: none;">

         <!-- Info Card -->
         <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 flex justify-between items-start shadow-sm">
            <div>
               <h3 class="text-lg font-bold text-blue-900" x-text="contribuyente?.nombre_razon_social"></h3>
               <p class="text-sm text-blue-700">DOC: <span x-text="contribuyente?.numero_documento"></span></p>
               <p class="text-sm text-blue-700">DIR: <span x-text="contribuyente?.direccion_fiscal"></span></p>
            </div>
            <button @click="reset" class="text-sm text-red-600 hover:text-red-800 underline">Nueva Consulta</button>
         </div>

         <!-- Debts Table -->
         <div class="bg-white shadow rounded-lg overflow-hidden mb-24">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
               <h3 class="text-lg font-medium text-gray-900">Estado de Cuenta</h3>
               <span class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded">Mora calculada al día de hoy</span>
            </div>

            <div class="overflow-x-auto">
               <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                     <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                           <input type="checkbox" @change="toggleAll" x-model="allSelected" class="rounded text-blue-600 focus:ring-blue-500 h-4 w-4">
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Año</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concepto</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periodo</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimiento</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Insoluto</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Mora</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                     </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                     <template x-for="deuda in deudas" :key="deuda.id">
                        <tr :class="{'bg-blue-50': deuda.checked, 'hover:bg-gray-50': !deuda.checked}">
                           <td class="px-6 py-4 whitespace-nowrap">
                              <input type="checkbox" x-model="deuda.checked" class="rounded text-blue-600 focus:ring-blue-500 h-4 w-4">
                           </td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="deuda.anio"></td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                 :class="{
                                                'bg-green-100 text-green-800': deuda.concepto === 'IMPUESTO_PREDIAL',
                                                'bg-yellow-100 text-yellow-800': deuda.concepto === 'LIMPIEZA_PUBLICA',
                                                'bg-indigo-100 text-indigo-800': deuda.concepto === 'SEGURIDAD_CIUDADANA'
                                            }"
                                 x-text="deuda.concepto.replace('_', ' ')">
                              </span>
                           </td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="deuda.periodo"></td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="deuda.vencimiento"></td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono" x-text="formatMoney(deuda.monto_insoluto)"></td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-red-600" x-text="formatMoney(deuda.mora)"></td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold font-mono text-gray-900" x-text="formatMoney(deuda.total)"></td>
                        </tr>
                     </template>
                     <tr x-show="deudas.length === 0">
                        <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                           No se encontraron deudas pendientes.
                        </td>
                     </tr>
                  </tbody>
               </table>
            </div>
         </div>
      </div>

      <!-- Floating Footer -->
      <div x-show="totalSelected > 0" class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 shadow-2xl p-4 z-20" style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="transform translate-y-full"
         x-transition:enter-end="transform translate-y-0">
         <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-8">
               <div>
                  <p class="text-sm text-gray-500 uppercase tracking-wide">Items Seleccionados</p>
                  <p class="text-2xl font-bold text-gray-900" x-text="countSelected"></p>
               </div>
               <div>
                  <p class="text-sm text-gray-500 uppercase tracking-wide">Total a Pagar</p>
                  <p class="text-3xl font-bold text-blue-600" x-text="'S/ ' + formatMoney(totalSelected)"></p>
               </div>
            </div>
            <button @click="openPaymentModal" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transform transition hover:scale-105 flex items-center">
               <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
               </svg>
               PROCESAR PAGO
            </button>
         </div>
      </div>

      <!-- Payment Modal -->
      <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
         <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
               <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
               <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                  <div class="sm:flex sm:items-start">
                     <div class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                     </div>
                     <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Confirmar Pago</h3>
                        <div class="mt-4">
                           <p class="text-sm text-gray-500">Resumen de la transacción:</p>
                           <div class="bg-gray-100 p-3 rounded mt-2 flex justify-between">
                              <span class="font-medium">Total a Pagar:</span>
                              <span class="font-bold text-lg text-gray-900" x-text="'S/ ' + formatMoney(totalSelected)"></span>
                           </div>

                           <div class="mt-4">
                              <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago</label>
                              <select x-model="paymentMethod" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border">
                                 <option value="EFECTIVO">Efectivo</option>
                                 <option value="TARJETA">Tarjeta de Crédito/Débito</option>
                                 <option value="TRANSFERENCIA">Transferencia Bancaria</option>
                              </select>
                           </div>

                           <div x-show="paymentMethod !== 'EFECTIVO'" class="mt-3">
                              <label class="block text-sm font-medium text-gray-700 mb-1">Código de Operación (Voucher)</label>
                              <input type="text" x-model="operationCode" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border" placeholder="Ej: 00123456">
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                  <button @click="processPayment" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm" :disabled="processing" :class="{'opacity-50 cursor-not-allowed': processing}">
                     <span x-show="!processing">Confirmar y Emitir Recibo</span>
                     <span x-show="processing">Procesando...</span>
                  </button>
                  <button @click="showModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                     Cancelar
                  </button>
               </div>
            </div>
         </div>
      </div>

   </main>

   <script>
      function cajaApp() {
         return {
            searchQuery: '',
            searchResults: [],
            contribuyente: null,
            deudas: [],
            allSelected: false,
            showModal: false,
            paymentMethod: 'EFECTIVO',
            operationCode: '',
            processing: false,

            async searchContribuyente() {
               if (this.searchQuery.length < 3) {
                  this.searchResults = [];
                  return;
               }
               try {
                  const res = await fetch(`../api/search/taxpayer.php?q=${this.searchQuery}`);
                  this.searchResults = await res.json();
               } catch (e) {
                  console.error(e);
               }
            },

            async selectContribuyente(contribuyente) {
               this.contribuyente = contribuyente;
               this.searchResults = [];
               this.searchQuery = '';
               await this.loadDebts(contribuyente.id);
            },

            async loadDebts(id) {
               try {
                  const res = await fetch(`../api/search/debts.php?contribuyente_id=${id}`);
                  this.deudas = await res.json();
               } catch (e) {
                  console.error(e);
               }
            },

            reset() {
               this.contribuyente = null;
               this.deudas = [];
               this.allSelected = false;
            },

            toggleAll() {
               this.deudas.forEach(d => d.checked = this.allSelected);
            },

            get countSelected() {
               return this.deudas.filter(d => d.checked).length;
            },

            get totalSelected() {
               return this.deudas
                  .filter(d => d.checked)
                  .reduce((sum, d) => sum + parseFloat(d.total), 0);
            },

            formatMoney(amount) {
               return parseFloat(amount).toFixed(2);
            },

            openPaymentModal() {
               this.showModal = true;
            },

            async processPayment() {
               this.processing = true;
               const selectedDebts = this.deudas.filter(d => d.checked);

               const payload = {
                  contribuyente_id: this.contribuyente.id,
                  items: selectedDebts,
                  monto_total: this.totalSelected,
                  metodo_pago: this.paymentMethod,
                  codigo_operacion: this.operationCode
               };

               try {
                  const res = await fetch('../api/payment/process.php', {
                     method: 'POST',
                     headers: {
                        'Content-Type': 'application/json'
                     },
                     body: JSON.stringify(payload)
                  });
                  const data = await res.json();

                  if (data.success) {
                     alert(`PAGO EXITOSO\nRecibo N°: ${data.numero_serie}`);
                     this.showModal = false;
                     this.reset();
                     // In a real app, we would open the PDF here
                     // window.open(`../api/reports/receipt.php?id=${data.recibo_id}`, '_blank');
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
</body>

</html>