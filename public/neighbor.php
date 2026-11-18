<?php
// public/neighbor.php
// Simple simulation of session handling
$dni = $_GET['dni'] ?? '';
if (!$dni) {
   header('Location: ../index.php');
   exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Mi Estado de Cuenta - SART</title>
   <link href="../src/output.css" rel="stylesheet">
   <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-gray-50 font-sans antialiased" x-data="vecinoApp('<?php echo $dni; ?>')">

   <!-- Navbar -->
   <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
         <div class="flex justify-between h-16">
            <div class="flex items-center">
               <span class="text-xl font-bold text-blue-900">🏡 Portal del Vecino</span>
            </div>
            <div class="flex items-center space-x-4">
               <span class="text-sm text-gray-600" x-text="contribuyente?.nombre_razon_social || 'Cargando...'"></span>
               <a href="../index.php" class="text-sm text-red-600 hover:text-red-800">Salir</a>
            </div>
         </div>
      </div>
   </nav>

   <!-- Main Content -->
   <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

      <div class="flex flex-col lg:flex-row gap-8">

         <!-- Left Column: Debts -->
         <div class="lg:w-2/3">
            <div class="flex justify-between items-center mb-6">
               <h2 class="text-2xl font-bold text-gray-800">Mis Deudas Pendientes</h2>

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
               <template x-for="deuda in groupedDebts.predial" :key="deuda.id">
                  <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition flex justify-between items-center">
                     <div class="flex items-start space-x-4">
                        <input type="checkbox" x-model="deuda.checked" class="mt-1 rounded text-blue-600 focus:ring-blue-500 h-5 w-5">
                        <div>
                           <div class="flex items-center space-x-2">
                              <span class="font-bold text-gray-800" x-text="deuda.concepto.replace('_', ' ')"></span>
                              <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full" x-text="deuda.anio"></span>
                           </div>
                           <p class="text-sm text-gray-500">Periodo: <span x-text="deuda.periodo"></span> • Vence: <span x-text="deuda.vencimiento"></span></p>
                           <p class="text-xs text-gray-400" x-text="deuda.predio"></p>
                        </div>
                     </div>
                     <div class="text-right">
                        <p class="text-lg font-bold text-gray-900" x-text="'S/ ' + formatMoney(deuda.total)"></p>
                        <p class="text-xs text-red-500" x-show="deuda.mora > 0" x-text="'Incluye mora: S/ ' + formatMoney(deuda.mora)"></p>
                     </div>
                  </div>
               </template>
            </div>

            <!-- Limpieza Box -->
            <div class="bg-white shadow rounded-lg overflow-hidden mb-6" x-show="groupedDebts.limpieza.length > 0">
               <div class="bg-green-100 px-4 py-3 font-bold text-green-800 border-b border-green-200 flex items-center">
                  <span class="text-xl mr-2">🧹</span> Limpieza Pública
               </div>
               <template x-for="deuda in groupedDebts.limpieza" :key="deuda.id">
                  <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition flex justify-between items-center">
                     <div class="flex items-start space-x-4">
                        <input type="checkbox" x-model="deuda.checked" class="mt-1 rounded text-green-600 focus:ring-green-500 h-5 w-5">
                        <div>
                           <div class="flex items-center space-x-2">
                              <span class="font-bold text-gray-800" x-text="deuda.concepto.replace('_', ' ')"></span>
                              <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full" x-text="deuda.anio"></span>
                           </div>
                           <p class="text-sm text-gray-500">Periodo: <span x-text="deuda.periodo"></span> • Vence: <span x-text="deuda.vencimiento"></span></p>
                           <p class="text-xs text-gray-400" x-text="deuda.predio"></p>
                        </div>
                     </div>
                     <div class="text-right">
                        <p class="text-lg font-bold text-gray-900" x-text="'S/ ' + formatMoney(deuda.total)"></p>
                        <p class="text-xs text-red-500" x-show="deuda.mora > 0" x-text="'Incluye mora: S/ ' + formatMoney(deuda.mora)"></p>
                     </div>
                  </div>
               </template>
            </div>

            <!-- Seguridad Box -->
            <div class="bg-white shadow rounded-lg overflow-hidden mb-6" x-show="groupedDebts.seguridad.length > 0">
               <div class="bg-yellow-100 px-4 py-3 font-bold text-yellow-800 border-b border-yellow-200 flex items-center">
                  <span class="text-xl mr-2">🛡️</span> Seguridad Ciudadana
               </div>
               <template x-for="deuda in groupedDebts.seguridad" :key="deuda.id">
                  <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition flex justify-between items-center">
                     <div class="flex items-start space-x-4">
                        <input type="checkbox" x-model="deuda.checked" class="mt-1 rounded text-yellow-600 focus:ring-yellow-500 h-5 w-5">
                        <div>
                           <div class="flex items-center space-x-2">
                              <span class="font-bold text-gray-800" x-text="deuda.concepto.replace('_', ' ')"></span>
                              <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full" x-text="deuda.anio"></span>
                           </div>
                           <p class="text-sm text-gray-500">Periodo: <span x-text="deuda.periodo"></span> • Vence: <span x-text="deuda.vencimiento"></span></p>
                           <p class="text-xs text-gray-400" x-text="deuda.predio"></p>
                        </div>
                     </div>
                     <div class="text-right">
                        <p class="text-lg font-bold text-gray-900" x-text="'S/ ' + formatMoney(deuda.total)"></p>
                        <p class="text-xs text-red-500" x-show="deuda.mora > 0" x-text="'Incluye mora: S/ ' + formatMoney(deuda.mora)"></p>
                     </div>
                  </div>
               </template>
            </div>

            <!-- Otros Box -->
            <div class="bg-white shadow rounded-lg overflow-hidden mb-6" x-show="groupedDebts.otros.length > 0">
               <div class="bg-gray-100 px-4 py-3 font-bold text-gray-800 border-b border-gray-200 flex items-center">
                  <span class="text-xl mr-2">📋</span> Otros Conceptos
               </div>
               <template x-for="deuda in groupedDebts.otros" :key="deuda.id">
                  <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition flex justify-between items-center">
                     <div class="flex items-start space-x-4">
                        <input type="checkbox" x-model="deuda.checked" class="mt-1 rounded text-gray-600 focus:ring-gray-500 h-5 w-5">
                        <div>
                           <div class="flex items-center space-x-2">
                              <span class="font-bold text-gray-800" x-text="deuda.concepto.replace('_', ' ')"></span>
                              <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full" x-text="deuda.anio"></span>
                           </div>
                           <p class="text-sm text-gray-500">Periodo: <span x-text="deuda.periodo"></span> • Vence: <span x-text="deuda.vencimiento"></span></p>
                           <p class="text-xs text-gray-400" x-text="deuda.predio"></p>
                        </div>
                     </div>
                     <div class="text-right">
                        <p class="text-lg font-bold text-gray-900" x-text="'S/ ' + formatMoney(deuda.total)"></p>
                        <p class="text-xs text-red-500" x-show="deuda.mora > 0" x-text="'Incluye mora: S/ ' + formatMoney(deuda.mora)"></p>
                     </div>
                  </div>
               </template>
            </div>

            <div x-show="deudas.length === 0" class="bg-white shadow rounded-lg p-8 text-center text-gray-500">
               ¡Felicidades! No tienes deudas pendientes.
            </div>
         </div>

         <!-- Right Column: Cart Summary -->
         <div class="lg:w-1/3">
            <div class="bg-white shadow-lg rounded-lg p-6 sticky top-6">
               <h3 class="text-lg font-bold text-gray-900 mb-4">Resumen de Pago</h3>

               <div class="space-y-3 mb-6">
                  <div class="flex justify-between text-gray-600">
                     <span>Items seleccionados</span>
                     <span x-text="countSelected"></span>
                  </div>
                  <div class="flex justify-between text-xl font-bold text-blue-900 border-t pt-3">
                     <span>Total a Pagar</span>
                     <span x-text="'S/ ' + formatMoney(totalSelected)"></span>
                  </div>
               </div>

               <button @click="payOnline"
                  class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow transition transform hover:-translate-y-1"
                  :disabled="countSelected === 0 || processing"
                  :class="{'opacity-50 cursor-not-allowed': countSelected === 0 || processing}">
                  <span x-show="!processing">PAGAR EN LÍNEA</span>
                  <span x-show="processing">Conectando con Niubiz...</span>
               </button>

               <div class="mt-4 flex justify-center space-x-2">
                  <img src="https://thumb.wikimedia.org/wikipedia/commons/thumb/5/5c/Visa_Inc._logo_%282021%E2%80%93present%29.svg/3840px-Visa_Inc._logo_%282021%E2%80%93present%29.svg.png?utm_source=es.wikipedia.org&utm_campaign=index&utm_content=thumbnail" class="h-6 opacity-50">
                  <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/1280px-Mastercard-logo.svg.png" class="h-6 opacity-50">
               </div>
            </div>
         </div>

      </div>
   </main>

   <script>
      function vecinoApp(dni) {
         return {
            contribuyente: null,
            deudas: [],
            processing: false,

            async init() {
               // 1. Find User ID by DNI
               try {
                  const resSearch = await fetch(`../api/search/taxpayer.php?q=${dni}`);
                  const users = await resSearch.json();
                  if (users.length > 0) {
                     this.contribuyente = users[0];
                     // 2. Load Debts
                     await this.loadDebts(this.contribuyente.id);
                  } else {
                     alert('Usuario no encontrado');
                     window.location.href = '../index.php';
                  }
               } catch (e) {
                  console.error(e);
               }
            },

            async loadDebts(id) {
               try {
                  const res = await fetch(`../api/search/debts.php?contribuyente_id=${id}`);
                  let data = await res.json();
                  // Initial sort by year/period
                  data.sort((a, b) => {
                     if (b.anio !== a.anio) return b.anio - a.anio;
                     return a.periodo - b.periodo;
                  });
                  this.deudas = data;
               } catch (e) {
                  console.error(e);
               }
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

            async payOnline() {
               this.processing = true;
               // Simulate Niubiz/Izipay delay
               setTimeout(async () => {
                  const selectedDebts = this.deudas.filter(d => d.checked);
                  const payload = {
                     contribuyente_id: this.contribuyente.id,
                     items: selectedDebts,
                     monto_total: this.totalSelected,
                     metodo_pago: 'TARJETA',
                     origen: 'WEB_ONLINE',
                     codigo_operacion: 'WEB-' + Math.floor(Math.random() * 1000000)
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
                        alert(`¡PAGO EXITOSO!\nSe ha enviado el recibo N° ${data.numero_serie} a su correo.`);
                        window.location.reload();
                     } else {
                        alert('Error: ' + data.error);
                     }
                  } catch (e) {
                     alert('Error de conexión');
                  } finally {
                     this.processing = false;
                  }
               }, 2000);
            }
         }
      }
   </script>
</body>

</html>