<?php
// public/neighbor.php
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
   <title>Pasarela de Pagos en Línea - SART Oropesa</title>
   <link href="../src/output.css" rel="stylesheet">
   <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
   <style>
      @media print {
         body * {
            visibility: hidden;
         }
         #comprobante-imprimible, #comprobante-imprimible * {
            visibility: visible;
         }
         #comprobante-imprimible {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 20px;
            background: white !important;
            box-shadow: none !important;
         }
         .no-print {
            display: none !important;
         }
      }
   </style>
</head>

<body class="bg-slate-100 font-sans antialiased text-slate-800" x-data="pasarelaApp('<?php echo htmlspecialchars($dni); ?>')">

   <!-- Top Security Banner -->
   <div class="bg-slate-900 text-slate-300 text-xs py-2 px-4 border-b border-slate-800 no-print">
      <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-2">
         <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1 text-emerald-400 font-bold">
               <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
               Conexión Segura SSL 256-Bit
            </span>
            <span class="text-slate-500">•</span>
            <span>Certificación de Pagos Seguros PCI-DSS</span>
            <span class="text-slate-500">•</span>
            <span class="text-slate-400">Oropesa - Cusco, 2026</span>
         </div>
         <div class="flex items-center gap-1.5 overflow-x-auto">
            <span class="text-[11px] text-slate-400 mr-1 hidden md:inline">Canales autorizados:</span>
            <img src="../src/img/visa.svg" alt="Visa" class="h-5 rounded shadow-sm">
            <img src="../src/img/mastercard.svg" alt="Mastercard" class="h-5 rounded shadow-sm">
            <img src="../src/img/yape.svg" alt="Yape" class="h-5 rounded shadow-sm">
            <img src="../src/img/plin.svg" alt="Plin" class="h-5 rounded shadow-sm">
            <img src="../src/img/pagoefectivo.svg" alt="PagoEfectivo" class="h-5 rounded shadow-sm">
            <img src="../src/img/niubiz.svg" alt="Niubiz" class="h-5 rounded shadow-sm">
         </div>
      </div>
   </div>

   <!-- Institutional Header -->
   <header class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-40 no-print">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
         <div class="flex justify-between items-center h-20">
            <div class="flex items-center gap-3">
               <img src="../src/img/logovd.png" alt="Escudo de Oropesa" class="h-14 w-auto object-contain">
               <div>
                  <h1 class="text-lg font-extrabold text-blue-950 tracking-tight leading-tight uppercase">
                     Municipalidad Distrital de Oropesa
                  </h1>
                  <p class="text-xs text-blue-800 font-semibold flex items-center gap-1.5">
                     <span>Gerencia de Administración Tributaria</span>
                     <span class="text-slate-300">•</span>
                     <span class="text-indigo-600 font-bold">SART Pago Seguro en Línea</span>
                  </p>
               </div>
            </div>

            <!-- User Info & Logout -->
            <div class="flex items-center gap-4">
               <div class="text-right hidden sm:block">
                  <p class="text-xs text-slate-400 font-medium">Contribuyente Identificado:</p>
                  <p class="text-sm font-bold text-slate-800" x-text="contribuyente?.nombre_razon_social || 'Cargando...'"></p>
                  <p class="text-xs text-slate-500 font-mono" x-text="'DNI/RUC: ' + (contribuyente?.numero_documento || '...')"></p>
               </div>
               <a href="../index.php" class="bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 font-semibold text-xs py-2 px-3.5 rounded-lg border border-slate-200 transition flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                  <span>Cerrar</span>
               </a>
            </div>
         </div>
      </div>
   </header>

   <!-- Step Wizard Tracker -->
   <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-blue-950 text-white py-5 px-4 shadow-inner no-print">
      <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
         <div>
            <span class="text-xs text-blue-300 font-bold uppercase tracking-wider">Pasarela Oficial de Recaudación Web</span>
            <h2 class="text-xl font-black">Portal Tributario del Vecino Oropesano</h2>
         </div>

         <!-- Steps -->
         <div class="flex items-center gap-3 text-xs">
            <div class="flex items-center gap-1.5 font-bold text-emerald-400">
               <span class="w-6 h-6 rounded-full bg-emerald-500 text-slate-900 flex items-center justify-center text-xs font-black">1</span>
               <span>Selección de Arbitrios</span>
            </div>
            <span class="text-slate-500">───</span>
            <div class="flex items-center gap-1.5 font-semibold text-blue-200">
               <span class="w-6 h-6 rounded-full bg-blue-800 text-white flex items-center justify-center text-xs">2</span>
               <span>Pasarela de Pago</span>
            </div>
            <span class="text-slate-500">───</span>
            <div class="flex items-center gap-1.5 font-semibold text-blue-200">
               <span class="w-6 h-6 rounded-full bg-blue-800 text-white flex items-center justify-center text-xs">3</span>
               <span>Recibo Oficial</span>
            </div>
         </div>
      </div>
   </div>

   <!-- Taxpayer Profile Summary Card -->
   <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-4 no-print">
      <div class="bg-white rounded-2xl shadow-md border border-slate-200 p-5">
         <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
            <div class="md:col-span-2 border-b md:border-b-0 md:border-r border-slate-100 pb-3 md:pb-0 md:pr-4">
               <span class="text-xs text-indigo-600 font-bold uppercase tracking-wider block mb-0.5">Sujeto Pasivo / Titular</span>
               <h3 class="text-base font-black text-slate-900 leading-snug" x-text="contribuyente?.nombre_razon_social || 'Cargando datos...'"></h3>
               <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500 mt-1">
                  <span><strong>Doc:</strong> <span x-text="contribuyente?.numero_documento"></span></span>
                  <span><strong>Tipo:</strong> <span x-text="contribuyente?.tipo_persona"></span></span>
                  <span x-show="contribuyente?.direccion_fiscal"><strong>Dirección:</strong> <span x-text="contribuyente?.direccion_fiscal"></span></span>
               </div>
            </div>

            <div class="text-center md:text-left border-b md:border-b-0 md:border-r border-slate-100 pb-3 md:pb-0 md:pr-4">
               <span class="text-xs text-slate-400 font-semibold block">Deuda Total Registrada:</span>
               <span class="text-xl font-black text-slate-800" x-text="'S/ ' + formatMoney(deudaTotalAcumulada)"></span>
               <span class="text-[11px] text-slate-500 block" x-text="deudas.length + ' cuotas pendientes' "></span>
            </div>

            <div class="text-center md:text-left bg-blue-50/80 p-3 rounded-xl border border-blue-100">
               <span class="text-xs text-blue-700 font-bold block">Seleccionado para Pagar:</span>
               <span class="text-2xl font-black text-blue-900" x-text="'S/ ' + formatMoney(totalSelected)"></span>
               <span class="text-[11px] text-blue-700 font-semibold block" x-text="countSelected + ' obligaciones marcadas'"></span>
            </div>
         </div>
      </div>
   </section>

   <!-- Main Content Grid -->
   <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 no-print">
      <div class="flex flex-col lg:flex-row gap-8 items-start">

         <!-- Left Column: Debts & Filters -->
         <div class="w-full lg:w-2/3 space-y-6">

            <!-- Selection Toolbar -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
               <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-3">
                  <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                     <span>📋 Obligaciones Tributarias del Contribuyente</span>
                  </h3>
                  <span class="text-xs text-slate-500" x-text="'Mostrando ' + deudas.length + ' cuotas'"></span>
               </div>

               <!-- Quick Selection Filters -->
               <div class="flex flex-wrap gap-2 text-xs">
                  <button @click="seleccionarTodo()" class="bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold py-1.5 px-3 rounded-lg border border-slate-300 transition">
                     ✓ Marcar Todo
                  </button>
                  <button @click="seleccionarSoloVencidas()" class="bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold py-1.5 px-3 rounded-lg border border-amber-300 transition">
                     ⚠️ Solo Vencidas
                  </button>
                  <button @click="seleccionarPorConcepto('PREDIAL')" class="bg-blue-50 hover:bg-blue-100 text-blue-800 font-bold py-1.5 px-3 rounded-lg border border-blue-300 transition">
                     🏠 Solo Predial
                  </button>
                  <button @click="seleccionarPorConcepto('ARBITRIOS')" class="bg-green-50 hover:bg-green-100 text-green-800 font-bold py-1.5 px-3 rounded-lg border border-green-300 transition">
                     🧹 Solo Arbitrios
                  </button>
                  <button @click="desmarcarTodo()" class="bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 font-semibold py-1.5 px-3 rounded-lg border border-slate-200 transition">
                     ✕ Desmarcar
                  </button>
               </div>
            </div>

            <!-- Group 1: Impuesto Predial -->
            <div class="bg-white shadow-sm rounded-2xl border border-slate-200 overflow-hidden" x-show="groupedDebts.predial.length > 0">
               <div class="bg-gradient-to-r from-blue-700 to-indigo-800 px-5 py-3.5 text-white flex justify-between items-center">
                  <div class="flex items-center gap-2.5">
                     <span class="text-xl">🏠</span>
                     <div>
                        <h4 class="font-bold text-sm tracking-wide">Impuesto Predial Anual</h4>
                        <p class="text-[11px] text-blue-200">Gravamen a la propiedad inmueble urbana y rústica</p>
                     </div>
                  </div>
                  <div class="text-right">
                     <span class="text-xs bg-white/20 text-white font-bold px-2.5 py-1 rounded-full" x-text="groupedDebts.predial.length + ' cuotas'"></span>
                  </div>
               </div>

               <div class="divide-y divide-slate-100">
                  <template x-for="deuda in groupedDebts.predial" :key="deuda.id">
                     <label class="p-4 flex items-center justify-between hover:bg-blue-50/40 transition cursor-pointer select-none">
                        <div class="flex items-start gap-3.5">
                           <input type="checkbox" x-model="deuda.checked" class="mt-1 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                           <div>
                              <div class="flex items-center gap-2">
                                 <span class="font-bold text-sm text-slate-800">Impuesto Predial</span>
                                 <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700" x-text="'Trimestre ' + deuda.periodo"></span>
                                 <span class="text-xs font-bold text-slate-500" x-text="deuda.anio"></span>
                              </div>
                              <p class="text-xs text-slate-500 mt-0.5" x-text="'Predio: ' + (deuda.direccion_predio || deuda.predio || 'Oropesa')"></p>
                              
                              <div class="mt-1 flex items-center gap-2 text-[11px]">
                                 <span :class="esVencida(deuda.fecha_vencimiento || deuda.vencimiento) ? 'text-red-600 font-bold' : 'text-slate-500'">
                                    Vence: <span x-text="deuda.fecha_vencimiento || deuda.vencimiento"></span>
                                 </span>
                                 <template x-if="esVencida(deuda.fecha_vencimiento || deuda.vencimiento)">
                                    <span class="bg-red-100 text-red-700 font-bold px-1.5 py-0.2 rounded text-[10px]">Vencido</span>
                                 </template>
                              </div>
                           </div>
                        </div>

                        <div class="text-right">
                           <div class="text-base font-black text-slate-900" x-text="'S/ ' + formatMoney(deuda.total || deuda.monto_insoluto)"></div>
                           <template x-if="deuda.mora > 0">
                              <span class="text-[11px] text-amber-700 font-semibold block" x-text="'Incluye TIM: S/ ' + formatMoney(deuda.mora)"></span>
                           </template>
                        </div>
                     </label>
                  </template>
               </div>
            </div>

            <!-- Group 2: Limpieza Pública -->
            <div class="bg-white shadow-sm rounded-2xl border border-slate-200 overflow-hidden" x-show="groupedDebts.limpieza.length > 0">
               <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-5 py-3.5 text-white flex justify-between items-center">
                  <div class="flex items-center gap-2.5">
                     <span class="text-xl">🧹</span>
                     <div>
                        <h4 class="font-bold text-sm tracking-wide">Arbitrios de Limpieza Pública</h4>
                        <p class="text-[11px] text-emerald-100">Recolección de residuos sólidos, barrido de calles y disposición final</p>
                     </div>
                  </div>
                  <div class="text-right">
                     <span class="text-xs bg-white/20 text-white font-bold px-2.5 py-1 rounded-full" x-text="groupedDebts.limpieza.length + ' cuotas'"></span>
                  </div>
               </div>

               <div class="divide-y divide-slate-100">
                  <template x-for="deuda in groupedDebts.limpieza" :key="deuda.id">
                     <label class="p-4 flex items-center justify-between hover:bg-emerald-50/40 transition cursor-pointer select-none">
                        <div class="flex items-start gap-3.5">
                           <input type="checkbox" x-model="deuda.checked" class="mt-1 h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                           <div>
                              <div class="flex items-center gap-2">
                                 <span class="font-bold text-sm text-slate-800">Limpieza Pública</span>
                                 <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700" x-text="getNombreMes(deuda.periodo) + ' ' + deuda.anio"></span>
                              </div>
                              <p class="text-xs text-slate-500 mt-0.5" x-text="'Predio: ' + (deuda.direccion_predio || deuda.predio || 'Oropesa')"></p>
                              
                              <div class="mt-1 flex items-center gap-2 text-[11px]">
                                 <span :class="esVencida(deuda.fecha_vencimiento || deuda.vencimiento) ? 'text-red-600 font-bold' : 'text-slate-500'">
                                    Vence: <span x-text="deuda.fecha_vencimiento || deuda.vencimiento"></span>
                                 </span>
                                 <template x-if="esVencida(deuda.fecha_vencimiento || deuda.vencimiento)">
                                    <span class="bg-red-100 text-red-700 font-bold px-1.5 py-0.2 rounded text-[10px]">Vencido</span>
                                 </template>
                              </div>
                           </div>
                        </div>

                        <div class="text-right">
                           <div class="text-base font-black text-slate-900" x-text="'S/ ' + formatMoney(deuda.total || deuda.monto_insoluto)"></div>
                           <template x-if="deuda.mora > 0">
                              <span class="text-[11px] text-amber-700 font-semibold block" x-text="'Incluye TIM: S/ ' + formatMoney(deuda.mora)"></span>
                           </template>
                        </div>
                     </label>
                  </template>
               </div>
            </div>

            <!-- Group 3: Seguridad Ciudadana -->
            <div class="bg-white shadow-sm rounded-2xl border border-slate-200 overflow-hidden" x-show="groupedDebts.seguridad.length > 0">
               <div class="bg-gradient-to-r from-amber-600 to-orange-700 px-5 py-3.5 text-white flex justify-between items-center">
                  <div class="flex items-center gap-2.5">
                     <span class="text-xl">🛡️</span>
                     <div>
                        <h4 class="font-bold text-sm tracking-wide">Arbitrios de Seguridad Ciudadana (Serenazgo)</h4>
                        <p class="text-[11px] text-amber-100">Patrullaje integrado, vigilancia comunitaria y prevención en Oropesa</p>
                     </div>
                  </div>
                  <div class="text-right">
                     <span class="text-xs bg-white/20 text-white font-bold px-2.5 py-1 rounded-full" x-text="groupedDebts.seguridad.length + ' cuotas'"></span>
                  </div>
               </div>

               <div class="divide-y divide-slate-100">
                  <template x-for="deuda in groupedDebts.seguridad" :key="deuda.id">
                     <label class="p-4 flex items-center justify-between hover:bg-amber-50/40 transition cursor-pointer select-none">
                        <div class="flex items-start gap-3.5">
                           <input type="checkbox" x-model="deuda.checked" class="mt-1 h-5 w-5 rounded border-slate-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                           <div>
                              <div class="flex items-center gap-2">
                                 <span class="font-bold text-sm text-slate-800">Seguridad Ciudadana</span>
                                 <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700" x-text="getNombreMes(deuda.periodo) + ' ' + deuda.anio"></span>
                              </div>
                              <p class="text-xs text-slate-500 mt-0.5" x-text="'Predio: ' + (deuda.direccion_predio || deuda.predio || 'Oropesa')"></p>
                              
                              <div class="mt-1 flex items-center gap-2 text-[11px]">
                                 <span :class="esVencida(deuda.fecha_vencimiento || deuda.vencimiento) ? 'text-red-600 font-bold' : 'text-slate-500'">
                                    Vence: <span x-text="deuda.fecha_vencimiento || deuda.vencimiento"></span>
                                 </span>
                                 <template x-if="esVencida(deuda.fecha_vencimiento || deuda.vencimiento)">
                                    <span class="bg-red-100 text-red-700 font-bold px-1.5 py-0.2 rounded text-[10px]">Vencido</span>
                                 </template>
                              </div>
                           </div>
                        </div>

                        <div class="text-right">
                           <div class="text-base font-black text-slate-900" x-text="'S/ ' + formatMoney(deuda.total || deuda.monto_insoluto)"></div>
                           <template x-if="deuda.mora > 0">
                              <span class="text-[11px] text-amber-700 font-semibold block" x-text="'Incluye TIM: S/ ' + formatMoney(deuda.mora)"></span>
                           </template>
                        </div>
                     </label>
                  </template>
               </div>
            </div>

            <!-- Empty State -->
            <div x-show="deudas.length === 0 && !loading" class="bg-white shadow-sm rounded-2xl p-10 text-center border border-slate-200">
               <div class="text-5xl mb-3">🎉</div>
               <h3 class="text-lg font-bold text-slate-800">¡Felicitaciones! Te encuentras al día.</h3>
               <p class="text-xs text-slate-500 mt-1">No registras cuotas pendientes ni obligaciones tributarias por vencer.</p>
            </div>

         </div>

         <!-- Right Column: Sticky Checkout Panel -->
         <div class="w-full lg:w-1/3 sticky top-24 space-y-4">

            <!-- Summary Box -->
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 overflow-hidden">
               <div class="border-b border-slate-100 pb-4 mb-4">
                  <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Resumen de Liquidación</span>
                  <h3 class="text-lg font-black text-slate-900">Total a Cancelar</h3>
               </div>

               <!-- Calculations -->
               <div class="space-y-3 text-xs mb-6">
                  <div class="flex justify-between text-slate-600">
                     <span>Cuotas tributarias marcadas:</span>
                     <span class="font-bold text-slate-900" x-text="countSelected + ' concepto(s)'"></span>
                  </div>
                  <div class="flex justify-between text-slate-600">
                     <span>Subtotal Tributos (Insoluto):</span>
                     <span class="font-bold text-slate-900" x-text="'S/ ' + formatMoney(subtotalInsolutoSelected)"></span>
                  </div>
                  <div class="flex justify-between text-slate-600">
                     <span>Intereses Moratorios Acumulados:</span>
                     <span class="font-bold text-amber-700" x-text="'S/ ' + formatMoney(totalMoraSelected)"></span>
                  </div>
                  <div class="flex justify-between text-slate-600 border-t border-slate-100 pt-2">
                     <span>Comisión por Pago Digital:</span>
                     <span class="font-bold text-emerald-600">S/ 0.00 (Bonificado)</span>
                  </div>

                  <!-- Big Total -->
                  <div class="bg-blue-50/70 p-4 rounded-xl border border-blue-200/60 mt-4 flex justify-between items-center">
                     <div>
                        <span class="text-xs font-bold text-blue-900 block">TOTAL A PAGAR:</span>
                        <span class="text-[10px] text-blue-600">Moneda nacional (PEN)</span>
                     </div>
                     <span class="text-2xl font-black text-blue-900" x-text="'S/ ' + formatMoney(totalSelected)"></span>
                  </div>
               </div>

               <!-- Payment Methods Selector Preview -->
               <div class="mb-6">
                  <label class="block text-xs font-bold text-slate-700 mb-2">Medio de Pago Seleccionado:</label>
                  <div class="grid grid-cols-3 gap-2">
                     <button 
                        type="button" 
                        @click="metodoPago = 'TARJETA'"
                        :class="metodoPago === 'TARJETA' ? 'bg-blue-50 border-blue-600 ring-2 ring-blue-500 shadow-sm' : 'bg-slate-50 border-slate-200 hover:bg-slate-100'"
                        class="p-2 rounded-xl border text-center transition flex flex-col items-center justify-center">
                        <div class="flex items-center gap-1.5 mb-1.5">
                           <img src="../src/img/visa.svg" alt="Visa" class="h-5 w-auto rounded shadow-xs">
                           <img src="../src/img/mastercard.svg" alt="Mastercard" class="h-5 w-auto rounded shadow-xs">
                        </div>
                        <span class="text-[10px] font-bold text-slate-800">Tarjeta</span>
                     </button>

                     <button 
                        type="button" 
                        @click="metodoPago = 'YAPE'"
                        :class="metodoPago === 'YAPE' ? 'bg-purple-50 border-purple-600 ring-2 ring-purple-500 shadow-sm' : 'bg-slate-50 border-slate-200 hover:bg-slate-100'"
                        class="p-2 rounded-xl border text-center transition flex flex-col items-center justify-center">
                        <div class="flex items-center gap-1.5 mb-1.5">
                           <img src="../src/img/yape.svg" alt="Yape" class="h-5 w-auto rounded shadow-xs">
                           <img src="../src/img/plin.svg" alt="Plin" class="h-5 w-auto rounded shadow-xs">
                        </div>
                        <span class="text-[10px] font-bold text-slate-800">Yape / Plin</span>
                     </button>

                     <button 
                        type="button" 
                        @click="metodoPago = 'TRANSFERENCIA'"
                        :class="metodoPago === 'TRANSFERENCIA' ? 'bg-amber-50 border-amber-600 ring-2 ring-amber-500 shadow-sm' : 'bg-slate-50 border-slate-200 hover:bg-slate-100'"
                        class="p-2 rounded-xl border text-center transition flex flex-col items-center justify-center">
                        <div class="flex items-center gap-1.5 mb-1.5">
                           <img src="../src/img/pagoefectivo.svg" alt="PagoEfectivo" class="h-5 w-auto rounded shadow-xs">
                        </div>
                        <span class="text-[10px] font-bold text-slate-800">Banca / CIP</span>
                     </button>
                  </div>
               </div>

               <!-- Primary Pay Button -->
               <button 
                  @click="abrirPasarelaModal()"
                  :disabled="countSelected === 0"
                  class="w-full bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-800 text-white font-extrabold py-3.5 px-4 rounded-xl shadow-lg transition transform hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-2 text-sm">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                  <span>PAGAR S/ <span x-text="formatMoney(totalSelected)"></span> SEGURO</span>
               </button>

               <!-- Trust Badges with Real Logos -->
               <div class="mt-4 pt-4 border-t border-slate-100 text-center">
                  <p class="text-[11px] text-slate-400 flex items-center justify-center gap-1">
                     <span>🔒 Transacción encriptada con tecnología bancaria</span>
                  </p>
                  
                  <!-- Real Logos Grid -->
                  <div class="flex flex-wrap items-center justify-center gap-2 mt-2.5">
                     <img src="../src/img/visa.svg" alt="Visa" class="h-6 w-auto shadow-sm rounded">
                     <img src="../src/img/mastercard.svg" alt="Mastercard" class="h-6 w-auto shadow-sm rounded">
                     <img src="../src/img/yape.svg" alt="Yape" class="h-6 w-auto shadow-sm rounded">
                     <img src="../src/img/plin.svg" alt="Plin" class="h-6 w-auto shadow-sm rounded">
                     <img src="../src/img/pagoefectivo.svg" alt="PagoEfectivo" class="h-6 w-auto shadow-sm rounded">
                     <img src="../src/img/niubiz.svg" alt="Niubiz" class="h-6 w-auto shadow-sm rounded">
                  </div>

                  <p class="text-[10px] text-slate-400 mt-2">Emisión de Recibo Digital instantáneo con validez legal ante la SUNAT y MDO.</p>
               </div>
            </div>

            <!-- Help / Municipal Notice -->
            <div class="bg-indigo-50/60 rounded-xl p-4 border border-indigo-100 text-xs text-indigo-900">
               <p class="font-bold mb-1">ℹ️ Atención al Contribuyente:</p>
               <p class="leading-relaxed text-indigo-800">
                  Para consultas sobre el estado de su predio o deducción para adulto mayor/pensionista, acérquese a la Subgerencia de Recaudación en la Plaza de Armas de Oropesa.
               </p>
            </div>

         </div>

      </div>
   </main>

   <!-- MODAL: PASARELA DE PAGO INTERACTIVA (CHECKOUT) -->
   <div x-show="showCheckoutModal" class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4 no-print" style="display: none;">
      <div class="relative bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden border border-slate-200">
         
         <!-- Modal Header -->
         <div class="bg-slate-900 text-white p-5 flex justify-between items-center">
            <div class="flex items-center gap-2.5">
               <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center font-bold text-sm shadow">
                  🔒
               </div>
               <div>
                  <h3 class="font-bold text-sm flex items-center gap-2">
                     Pasarela de Pago Seguro
                     <span class="text-[9px] bg-emerald-500/20 text-emerald-300 font-mono px-1.5 py-0.5 rounded border border-emerald-500/30">SSL 256-BIT</span>
                  </h3>
                  <p class="text-[11px] text-slate-400">Municipalidad Distrital de Oropesa & Niubiz</p>
               </div>
            </div>
            <div class="flex items-center gap-3">
               <img src="../src/img/niubiz.svg" alt="Niubiz" class="h-6 w-auto shadow-sm">
               <button @click="showCheckoutModal = false" :disabled="processing" class="text-slate-400 hover:text-white text-lg font-bold">✕</button>
            </div>
         </div>

         <!-- Modal Body -->
         <div class="p-6">

            <!-- Amount badge -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 mb-5 flex justify-between items-center">
               <span class="text-xs text-slate-500 font-semibold">Total a debitar:</span>
               <span class="text-xl font-black text-blue-900" x-text="'S/ ' + formatMoney(totalSelected)"></span>
            </div>

            <!-- TAB 1: TARJETA -->
            <div x-show="metodoPago === 'TARJETA'" class="space-y-4">
               <!-- Animated Visual Card -->
               <div class="bg-gradient-to-tr from-slate-900 via-indigo-950 to-blue-900 text-white rounded-xl p-5 shadow-lg relative overflow-hidden border border-indigo-700/40">
                  <div class="flex justify-between items-center mb-5">
                     <div class="flex items-center gap-2">
                        <img src="../src/img/chip.svg" alt="Chip EMV" class="h-7 w-auto shadow-inner rounded">
                        <svg class="h-5 w-5 text-indigo-300/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                     </div>
                     <div class="flex items-center">
                        <img src="../src/img/visa.svg" alt="Visa" class="h-7 w-auto rounded shadow" x-show="!cardForm.number || cardForm.number.startsWith('4')">
                        <img src="../src/img/mastercard.svg" alt="Mastercard" class="h-7 w-auto rounded shadow" x-show="cardForm.number && !cardForm.number.startsWith('4')">
                     </div>
                  </div>
                  <div class="font-mono text-lg tracking-widest mb-4 drop-shadow" x-text="cardForm.number || '•••• •••• •••• ••••'"></div>
                  <div class="flex justify-between items-end text-xs font-mono">
                     <div>
                        <span class="text-[9px] text-slate-400 uppercase block tracking-wider">Titular</span>
                        <span class="font-bold uppercase tracking-wider text-slate-100" x-text="cardForm.name || (contribuyente?.nombre_razon_social || 'NOMBRE DEL TITULAR')"></span>
                     </div>
                     <div class="text-right">
                        <span class="text-[9px] text-slate-400 uppercase block tracking-wider">Expira</span>
                        <span class="font-bold text-slate-100" x-text="cardForm.expiry || 'MM/AA'"></span>
                     </div>
                  </div>
               </div>

               <!-- Form Fields -->
               <div class="space-y-3 text-xs">
                  <div>
                     <label class="block font-bold text-slate-700 mb-1">Número de Tarjeta</label>
                     <input type="text" x-model="cardForm.number" maxlength="19" placeholder="4557 •••• •••• ••••" class="w-full border rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500">
                  </div>
                  <div class="grid grid-cols-2 gap-3">
                     <div>
                        <label class="block font-bold text-slate-700 mb-1">Vencimiento (MM/AA)</label>
                        <input type="text" x-model="cardForm.expiry" maxlength="5" placeholder="12/28" class="w-full border rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500">
                     </div>
                     <div>
                        <label class="block font-bold text-slate-700 mb-1">Código CVV</label>
                        <input type="password" x-model="cardForm.cvv" maxlength="4" placeholder="•••" class="w-full border rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500">
                     </div>
                  </div>
                  <div>
                     <label class="block font-bold text-slate-700 mb-1">Nombre Impreso en la Tarjeta</label>
                     <input type="text" x-model="cardForm.name" placeholder="Como figura en el plástico" class="w-full border rounded-lg px-3 py-2 text-sm uppercase focus:ring-2 focus:ring-blue-500">
                  </div>
               </div>
            </div>

            <!-- TAB 2: YAPE / PLIN -->
            <div x-show="metodoPago === 'YAPE'" class="space-y-4 text-center">
               <div class="bg-purple-50 p-4 rounded-xl border border-purple-200 inline-block mx-auto">
                  <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=MUNICIPALIDAD_OROPESA_SART_PAGO_TRIBUTOS" alt="QR Yape" class="w-36 h-36 mx-auto rounded-lg shadow-sm">
                  <p class="text-xs text-purple-900 font-bold mt-2">YAPE Municipal: 984-271-880</p>
               </div>
               <p class="text-xs text-slate-600">Escanea el código QR desde tu app Yape o Plin y digita el código de aprobación:</p>
               <input type="text" x-model="yapeCode" maxlength="6" placeholder="Código de aprobación (6 dígitos)" class="w-full text-center tracking-widest text-base font-bold border rounded-lg px-4 py-2 font-mono focus:ring-2 focus:ring-purple-500">
            </div>

            <!-- TAB 3: TRANSFERENCIA -->
            <div x-show="metodoPago === 'TRANSFERENCIA'" class="space-y-3 text-xs">
               <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-200">
                  <p class="font-bold text-indigo-900 mb-1">Banco de la Nación (Cuenta Corriente):</p>
                  <p class="font-mono text-slate-800">Cta: 00-181-049182 | CCI: 018-181-000181049182-44</p>
                  <p class="text-[11px] text-indigo-700 mt-2">Titular: Municipalidad Distrital de Oropesa - Rentas</p>
               </div>
               <div>
                  <label class="block font-bold text-slate-700 mb-1">Número de Operación Bancaria:</label>
                  <input type="text" x-model="codigoTransferencia" placeholder="Ej: OP-849201" class="w-full border rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-indigo-500">
               </div>
            </div>

            <!-- Progress / Processing Banner -->
            <div x-show="processing" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-xl text-center">
               <div class="flex items-center justify-center gap-2 text-blue-900 font-bold text-xs">
                  <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                  <span x-text="processingMessage"></span>
               </div>
            </div>

            <!-- Action buttons -->
            <div class="mt-6 flex gap-2">
               <button 
                  type="button" 
                  @click="showCheckoutModal = false" 
                  :disabled="processing"
                  class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 px-4 rounded-xl text-xs transition">
                  Cancelar
               </button>
               <button 
                  type="button" 
                  @click="procesarPagoDefinitivo()" 
                  :disabled="processing"
                  class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition shadow flex items-center justify-center gap-1.5 disabled:opacity-50">
                  <span x-show="!processing">Confirmar y Pagar</span>
                  <span x-show="processing">Validando...</span>
               </button>
            </div>

         </div>

      </div>
   </div>

   <!-- MODAL / VOUCHER DE PAGO EXITOSO (RECIBO OFICIAL IMPRIMIBLE) -->
   <div x-show="showSuccessModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" style="display: none;">
      <div id="comprobante-imprimible" class="relative bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden border border-slate-200">
         
         <!-- Header -->
         <div class="bg-gradient-to-r from-emerald-600 to-teal-700 text-white p-6 text-center">
            <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-2 text-2xl font-black">
               ✓
            </div>
            <h3 class="text-xl font-black tracking-tight">¡PAGO PROCESADO CON ÉXITO!</h3>
            <p class="text-xs text-emerald-100 mt-1">Transacción tributaria confirmada y conciliada</p>
         </div>

         <!-- Voucher Content -->
         <div class="p-6 text-xs text-slate-700 space-y-4">
            
            <div class="text-center border-b pb-3">
               <img src="../src/img/logovd.png" alt="Escudo de Oropesa" class="h-14 w-auto mx-auto mb-2 object-contain drop-shadow-sm">
               <h4 class="font-extrabold uppercase text-slate-900 text-sm tracking-wide">Municipalidad Distrital de Oropesa</h4>
               <p class="text-slate-500 font-medium text-[11px]">Subgerencia de Recaudación y Administración Tributaria</p>
               <div class="mt-2 inline-block bg-slate-100 border border-slate-300 px-3 py-1 rounded-lg">
                  <span class="text-slate-500 font-semibold">RECIBO DE PAGO DIGITAL N°:</span>
                  <strong class="text-slate-900 font-mono text-sm ml-1" x-text="ultimoRecibo?.numero_serie"></strong>
               </div>
            </div>

            <div class="grid grid-cols-2 gap-2 bg-slate-50 p-3 rounded-xl border text-[11px]">
               <div><strong>Fecha y Hora:</strong> <span x-text="ultimoRecibo?.fecha_pago"></span></div>
               <div><strong>Canal:</strong> <span x-text="ultimoRecibo?.origen_pago"></span></div>
               <div><strong>Operación:</strong> <span class="font-mono" x-text="ultimoRecibo?.codigo_operacion"></span></div>
               <div><strong>Medio:</strong> <span x-text="ultimoRecibo?.metodo_pago"></span></div>
               <div class="col-span-2"><strong>Contribuyente:</strong> <span x-text="contribuyente?.nombre_razon_social"></span></div>
               <div class="col-span-2"><strong>DNI / RUC:</strong> <span x-text="contribuyente?.numero_documento"></span></div>
            </div>

            <!-- Total Paid Box -->
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 flex justify-between items-center text-sm font-black text-emerald-900">
               <span>TOTAL CANCELADO:</span>
               <span class="text-lg" x-text="'S/ ' + formatMoney(ultimoRecibo?.monto_total)"></span>
            </div>

            <!-- Sello Digital de Autenticidad -->
            <div class="pt-2 border-t flex items-center justify-between">
               <div class="flex items-center gap-2">
                  <img src="../src/img/niubiz.svg" alt="Niubiz" class="h-5 w-auto">
                  <span class="text-[10px] text-slate-400 font-mono">TOKEN: <span x-text="'SEC-' + (ultimoRecibo?.codigo_operacion || 'VALID')"></span></span>
               </div>
               <div class="flex items-center gap-1.5 opacity-80">
                  <img src="../src/img/visa.svg" alt="Visa" class="h-4 w-auto">
                  <img src="../src/img/mastercard.svg" alt="Mastercard" class="h-4 w-auto">
                  <img src="../src/img/yape.svg" alt="Yape" class="h-4 w-auto">
               </div>
            </div>

            <p class="text-[10px] text-slate-400 text-center leading-relaxed">
               Este documento electrónico constituye constancia válida de no adeudo sobre los periodos cancelados conforme al Código Tributario Peruano.
            </p>
         </div>

         <!-- Buttons -->
         <div class="bg-slate-100 p-4 flex gap-2 justify-end no-print">
            <button @click="imprimirComprobante()" class="bg-slate-800 hover:bg-black text-white font-bold py-2 px-4 rounded-xl text-xs flex items-center gap-1.5 shadow">
               🖨️ Imprimir / Guardar PDF
            </button>
            <button @click="cerrarComprobanteYRecargar()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-xl text-xs shadow">
               Finalizar y Volver
            </button>
         </div>

      </div>
   </div>

   <script>
      function pasarelaApp(dni) {
         return {
            contribuyente: null,
            deudas: [],
            metodoPago: 'TARJETA',
            loading: true,
            processing: false,
            processingMessage: '',
            showCheckoutModal: false,
            showSuccessModal: false,
            ultimoRecibo: null,
            yapeCode: '',
            codigoTransferencia: '',

            cardForm: {
               number: '',
               expiry: '',
               cvv: '',
               name: ''
            },

            async init() {
               this.loading = true;
               try {
                  const resSearch = await fetch(`../api/search/taxpayer.php?q=${encodeURIComponent(dni)}`);
                  const users = await resSearch.json();
                  if (users && users.length > 0) {
                     this.contribuyente = users[0];
                     this.cardForm.name = this.contribuyente.nombre_razon_social;
                     await this.loadDebts(this.contribuyente.id);
                  } else {
                     alert('Contribuyente no encontrado');
                     window.location.href = '../index.php';
                  }
               } catch (e) {
                  console.error(e);
               } finally {
                  this.loading = false;
               }
            },

            async loadDebts(id) {
               try {
                  const res = await fetch(`../api/search/debts.php?contribuyente_id=${id}`);
                  let data = await res.json();
                  if (Array.isArray(data)) {
                     data.forEach(d => d.checked = false);
                     this.deudas = data;
                  }
               } catch (e) {
                  console.error(e);
               }
            },

            get groupedDebts() {
               return {
                  predial: this.deudas.filter(d => (d.concepto || d.tributo_concepto || '').includes('PREDIAL')),
                  limpieza: this.deudas.filter(d => (d.concepto || d.tributo_concepto || '').includes('LIMPIEZA')),
                  seguridad: this.deudas.filter(d => (d.concepto || d.tributo_concepto || '').includes('SEGURIDAD'))
               };
            },

            get deudaTotalAcumulada() {
               return this.deudas.reduce((acc, d) => acc + parseFloat(d.total || d.monto_insoluto || 0), 0);
            },

            get countSelected() {
               return this.deudas.filter(d => d.checked).length;
            },

            get subtotalInsolutoSelected() {
               return this.deudas
                  .filter(d => d.checked)
                  .reduce((sum, d) => sum + parseFloat(d.monto_insoluto || 0), 0);
            },

            get totalMoraSelected() {
               return this.deudas
                  .filter(d => d.checked)
                  .reduce((sum, d) => sum + parseFloat(d.mora || 0), 0);
            },

            get totalSelected() {
               return this.deudas
                  .filter(d => d.checked)
                  .reduce((sum, d) => sum + parseFloat(d.total || d.monto_insoluto || 0), 0);
            },

            seleccionarTodo() {
               this.deudas.forEach(d => d.checked = true);
            },

            desmarcarTodo() {
               this.deudas.forEach(d => d.checked = false);
            },

            seleccionarSoloVencidas() {
               this.deudas.forEach(d => {
                  d.checked = this.esVencida(d.fecha_vencimiento || d.vencimiento);
               });
            },

            seleccionarPorConcepto(concepto) {
               this.deudas.forEach(d => {
                  const c = (d.concepto || d.tributo_concepto || '').toUpperCase();
                  if (concepto === 'PREDIAL') {
                     d.checked = c.includes('PREDIAL');
                  } else if (concepto === 'ARBITRIOS') {
                     d.checked = c.includes('LIMPIEZA') || c.includes('SEGURIDAD');
                  }
               });
            },

            esVencida(fechaStr) {
               if (!fechaStr) return false;
               const hoy = new Date().toISOString().split('T')[0];
               return fechaStr < hoy;
            },

            getNombreMes(num) {
               const meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'];
               return meses[parseInt(num)] || ('Periodo ' + num);
            },

            formatMoney(val) {
               if (!val) return '0.00';
               return parseFloat(val).toLocaleString('es-PE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            },

            abrirPasarelaModal() {
               if (this.countSelected === 0) {
                  alert('Por favor selecciona al menos una cuota tributaria a pagar.');
                  return;
               }
               this.showCheckoutModal = true;
            },

            async procesarPagoDefinitivo() {
               this.processing = true;
               this.processingMessage = 'Conectando con pasarela segura Niubiz...';

               const selectedDebts = this.deudas.filter(d => d.checked);
               const codOp = 'WEB-' + Math.floor(Math.random() * 899999 + 100000);

               setTimeout(async () => {
                  this.processingMessage = 'Autorizando transacción bancaria...';

                  setTimeout(async () => {
                     this.processingMessage = 'Generando recibo tributario oficial...';

                     const payload = {
                        contribuyente_id: this.contribuyente.id,
                        items: selectedDebts,
                        monto_total: this.totalSelected,
                        metodo_pago: this.metodoPago,
                        origen: 'WEB_ONLINE',
                        codigo_operacion: codOp
                     };

                     try {
                        const res = await fetch('../api/payment/process.php', {
                           method: 'POST',
                           headers: { 'Content-Type': 'application/json' },
                           body: JSON.stringify(payload)
                        });
                        const data = await res.json();

                        if (data.success) {
                           this.ultimoRecibo = {
                              numero_serie: data.numero_serie,
                              codigo_operacion: data.codigo_operacion || codOp,
                              monto_total: data.monto_total || this.totalSelected,
                              fecha_pago: data.fecha_pago || new Date().toLocaleString(),
                              metodo_pago: this.metodoPago,
                              origen_pago: 'Portal Web (Auto-servicio)'
                           };
                           this.showCheckoutModal = false;
                           this.showSuccessModal = true;
                        } else {
                           alert('Error: ' + (data.error || 'No se pudo procesar el pago.'));
                        }
                     } catch(e) {
                        alert('Error de conexión con el servidor.');
                     } finally {
                        this.processing = false;
                     }
                  }, 800);
               }, 800);
            },

            imprimirComprobante() {
               window.print();
            },

            cerrarComprobanteYRecargar() {
               this.showSuccessModal = false;
               window.location.reload();
            }
         }
      }
   </script>
</body>

</html>
