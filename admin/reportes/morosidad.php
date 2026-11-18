<?php
// admin/reportes/morosidad.php
require_once '../includes/layout.php';
renderHeader('Seguimiento de Morosidad');
?>

<div x-data="morosidadApp()" x-init="init()" class="container mx-auto">

   <!-- Header -->
   <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
         <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-800">⚠️ Cartera y Seguimiento de Morosidad</h1>
            <span class="bg-red-100 text-red-800 text-xs font-bold px-3 py-1 rounded-full shadow-sm" x-text="'Tasa de Morosidad: ' + (kpis.tasa_morosidad || 0) + '%'"></span>
         </div>
         <p class="text-xs text-gray-500 mt-1">Monitoreo de obligaciones tributarias vencidas y acciones de cobranza (Objetivo Específico 4 de Tesis - Oropesa 2026)</p>
      </div>

      <div class="flex items-center gap-2">
         <select x-model="anio" @change="fetchData()" class="border border-gray-300 rounded px-3 py-2 text-sm bg-white font-bold text-gray-700">
            <option value="2026">Año Fiscal 2026</option>
            <option value="2025">Año Fiscal 2025</option>
         </select>
         <button @click="imprimirReporte()" class="bg-gray-800 hover:bg-black text-white font-bold py-2 px-4 rounded text-sm flex items-center gap-1 shadow">
            🖨️ Imprimir Padrón
         </button>
      </div>
   </div>

   <!-- KPI Cards -->
   <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
         <p class="text-xs text-gray-500 uppercase font-semibold">Tasa de Morosidad Global</p>
         <div class="flex items-baseline gap-2">
            <p class="text-2xl font-black text-red-600" x-text="(kpis.tasa_morosidad || 0) + '%'"></p>
            <span class="text-xs text-gray-400">del total emitido</span>
         </div>
         <p class="text-xs text-gray-500 mt-1" x-text="(kpis.contribuyentes_morosos || 0) + ' de ' + (kpis.total_contribuyentes || 0) + ' contribuyentes en mora'"></p>
      </div>

      <div class="bg-white p-4 rounded-lg shadow border-l-4 border-amber-500">
         <p class="text-xs text-gray-500 uppercase font-semibold">Cartera Vencida / Pendiente</p>
         <p class="text-2xl font-bold text-gray-800" x-text="'S/ ' + formatDinero(kpis.monto_pendiente)"></p>
         <span class="text-xs text-amber-600 font-medium">Por cobrar en el año</span>
      </div>

      <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
         <p class="text-xs text-gray-500 uppercase font-semibold">Recaudación Efectiva</p>
         <p class="text-2xl font-bold text-green-700" x-text="'S/ ' + formatDinero(kpis.monto_recaudado)"></p>
         <span class="text-xs text-green-600 font-medium" x-text="(kpis.tasa_cumplimiento || 0) + '% cumplimiento' "></span>
      </div>

      <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
         <p class="text-xs text-gray-500 uppercase font-semibold">Total Emisión Anual</p>
         <p class="text-2xl font-bold text-gray-800" x-text="'S/ ' + formatDinero(kpis.monto_emitido)"></p>
         <span class="text-xs text-blue-600 font-medium">Predial + Arbitrios</span>
      </div>
   </div>

   <!-- Antigüedad / Semáforo de Morosidad -->
   <div class="bg-white p-5 rounded-lg shadow mb-6 border">
      <h3 class="text-sm font-bold text-gray-800 uppercase mb-3 flex items-center gap-2">
         <span>🚦 Estratificación de la Deuda por Antigüedad (Semáforo de Riesgo)</span>
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
         <div class="p-4 rounded-lg bg-green-50 border border-green-200">
            <div class="flex justify-between items-center mb-1">
               <span class="text-xs font-bold text-green-800 uppercase">Tramo Corriente (0 - 30 días)</span>
               <span class="text-xs bg-green-200 text-green-800 px-2 py-0.5 rounded font-bold">Riesgo Bajo</span>
            </div>
            <p class="text-xl font-bold text-green-900" x-text="'S/ ' + formatDinero(semaforo.corriente)"></p>
            <p class="text-xs text-green-700 mt-1">Obligaciones vencidas recientes en periodo de aviso.</p>
         </div>

         <div class="p-4 rounded-lg bg-yellow-50 border border-yellow-200">
            <div class="flex justify-between items-center mb-1">
               <span class="text-xs font-bold text-yellow-800 uppercase">Alerta Preventiva (31 - 90 días)</span>
               <span class="text-xs bg-yellow-200 text-yellow-800 px-2 py-0.5 rounded font-bold">Riesgo Medio</span>
            </div>
            <p class="text-xl font-bold text-yellow-900" x-text="'S/ ' + formatDinero(semaforo.preventivo)"></p>
            <p class="text-xs text-yellow-700 mt-1">Sujeto a cartas de recordatorio y notificación administrativa.</p>
         </div>

         <div class="p-4 rounded-lg bg-red-50 border border-red-200">
            <div class="flex justify-between items-center mb-1">
               <span class="text-xs font-bold text-red-800 uppercase">Morosidad Crítica (> 90 días)</span>
               <span class="text-xs bg-red-200 text-red-800 px-2 py-0.5 rounded font-bold">Cobranza Coactiva</span>
            </div>
            <p class="text-xl font-bold text-red-900" x-text="'S/ ' + formatDinero(semaforo.critico)"></p>
            <p class="text-xs text-red-700 mt-1">Pasible de valores tributarios (Orden de Pago y Ejecución).</p>
         </div>
      </div>
   </div>

   <!-- Search & Filters -->
   <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
      <div class="flex gap-2 w-full md:w-auto">
         <input 
            type="text" 
            x-model="search" 
            @keydown.enter="fetchData()"
            placeholder="Buscar por Nombre, DNI/RUC o Dirección..." 
            class="border rounded px-4 py-2 w-full md:w-80 focus:outline-none focus:ring-2 focus:ring-red-500 text-sm">
         
         <select x-model="filtroRiesgo" @change="fetchData()" class="border rounded px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-red-500">
            <option value="">Todos los Riesgos</option>
            <option value="ALTO">Alto Riesgo (>90 días / >S/ 500)</option>
            <option value="MEDIO">Medio Riesgo (31-90 días)</option>
            <option value="BAJO">Bajo Riesgo (<=30 días)</option>
         </select>

         <button @click="fetchData()" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm font-semibold">
            Buscar
         </button>
      </div>

      <div class="text-xs text-gray-500">
         Mostrando <strong class="text-gray-800" x-text="deudores.length"></strong> contribuyentes con deuda
      </div>
   </div>

   <!-- Ranking Table -->
   <div class="bg-white shadow-md rounded overflow-x-auto">
      <table class="min-w-full table-auto">
         <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-xs leading-normal">
               <th class="py-3 px-3 text-center w-12">N°</th>
               <th class="py-3 px-4 text-left">Documento</th>
               <th class="py-3 px-6 text-left">Contribuyente</th>
               <th class="py-3 px-4 text-left">Dirección Fiscal (Oropesa)</th>
               <th class="py-3 px-3 text-center">Predios</th>
               <th class="py-3 px-3 text-center">Cuotas</th>
               <th class="py-3 px-4 text-right">Deuda Pendiente</th>
               <th class="py-3 px-3 text-center">Riesgo</th>
               <th class="py-3 px-4 text-center">Acciones</th>
            </tr>
         </thead>
         <tbody class="text-gray-600 text-sm font-light">
            <template x-for="(d, index) in deudores" :key="d.id">
               <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                  <td class="py-3 px-3 text-center font-bold text-gray-500 bg-gray-50/50" x-text="index + 1"></td>
                  
                  <td class="py-3 px-4 text-left whitespace-nowrap">
                     <span class="font-bold text-gray-800" x-text="d.numero_documento"></span>
                     <div class="text-xs text-gray-400" x-text="d.tipo_persona"></div>
                  </td>

                  <td class="py-3 px-6 text-left font-semibold text-gray-800" x-text="d.nombre_razon_social"></td>

                  <td class="py-3 px-4 text-left text-xs" x-text="d.direccion_fiscal"></td>

                  <td class="py-3 px-3 text-center font-bold text-gray-700" x-text="d.total_predios"></td>

                  <td class="py-3 px-3 text-center">
                     <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-semibold" x-text="d.cuotas_pendientes + ' cuotas'"></span>
                  </td>

                  <td class="py-3 px-4 text-right whitespace-nowrap">
                     <span class="font-black text-red-600 text-sm" x-text="'S/ ' + formatDinero(d.total_deuda)"></span>
                  </td>

                  <td class="py-3 px-3 text-center whitespace-nowrap">
                     <span :class="d.riesgo_clase" class="px-2.5 py-1 rounded-full text-xs font-bold border" x-text="d.riesgo"></span>
                  </td>

                  <td class="py-3 px-4 text-center whitespace-nowrap space-x-1">
                     <a :href="'estado_cuenta.php?id=' + d.id" target="_blank" class="text-blue-600 hover:text-blue-800 font-bold text-xs bg-blue-50 px-2.5 py-1 rounded border border-blue-200 hover:bg-blue-100 transition">
                        Estado Cuenta
                     </a>
                     <button @click="abrirNotificacion(d)" class="text-amber-700 hover:text-amber-900 font-bold text-xs bg-amber-50 px-2.5 py-1 rounded border border-amber-200 hover:bg-amber-100 transition">
                        Notificar
                     </button>
                  </td>
               </tr>
            </template>
            <tr x-show="deudores.length === 0 && !loading">
               <td colspan="9" class="py-8 text-center text-gray-500">
                  <div class="text-3xl mb-1">🎉</div>
                  <p class="font-bold">No se encontraron contribuyentes en mora con los criterios especificados.</p>
               </td>
            </tr>
            <tr x-show="loading">
               <td colspan="9" class="py-8 text-center text-gray-500">
                  Cargando cartera de deudores...
               </td>
            </tr>
         </tbody>
      </table>

      <div class="p-4 bg-gray-50 border-t border-gray-200 text-xs text-gray-500 flex justify-between items-center">
         <span>Total deudores listados: <strong class="text-gray-800" x-text="deudores.length"></strong></span>
         <span>Cumplimiento del Objetivo Específico 4 de la Tesis: Monitoreo sistemático de morosidad</span>
      </div>
   </div>

   <!-- Modal Notificación Preventiva de Cobranza -->
   <div x-show="showNotificacionModal" class="fixed inset-0 bg-gray-900/60 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" style="display: none;">
      <div class="relative bg-white rounded-lg max-w-2xl w-full shadow-2xl overflow-hidden border">
         <div class="bg-red-800 text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-bold text-lg">📄 Aviso Preventivo de Cobranza Tributaria</h3>
            <button @click="showNotificacionModal = false" class="text-gray-300 hover:text-white text-xl font-bold">✕</button>
         </div>

         <div id="carta-notificacion" class="p-6 text-sm text-gray-700 space-y-4 max-h-[75vh] overflow-y-auto">
            <div class="text-center border-b pb-3">
               <h4 class="font-bold uppercase text-base text-gray-900">Municipalidad Distrital de Oropesa</h4>
               <p class="text-xs text-gray-500 font-medium">Gerencia de Administración Tributaria y Rentas</p>
               <p class="text-xs text-red-600 font-bold mt-1">CARTA DE REQUERIMIENTO DE PAGO N° 2026-MDO/GAT-<span x-text="selectedDeudor?.id"></span></p>
            </div>

            <div class="bg-gray-50 p-3 rounded border text-xs">
               <p><strong>Señor(a):</strong> <span x-text="selectedDeudor?.nombre_razon_social"></span></p>
               <p><strong>Documento:</strong> <span x-text="selectedDeudor?.numero_documento"></span></p>
               <p><strong>Domicilio Fiscal:</strong> <span x-text="selectedDeudor?.direccion_fiscal"></span></p>
               <p><strong>Fecha de Emisión:</strong> <?php echo date('d/m/Y'); ?></p>
            </div>

            <p class="text-xs text-justify leading-relaxed">
               Por medio de la presente, nos dirigimos a usted para comunicarle que a la fecha registra obligaciones tributarias vencidas y pendientes de cancelación correspondientes a los <strong>Arbitrios Municipales (Limpieza Pública y Seguridad Ciudadana)</strong> e <strong>Impuesto Predial</strong> del Año Fiscal <strong>2026</strong>.
            </p>

            <div class="border rounded p-3 bg-red-50 text-xs">
               <div class="flex justify-between font-bold text-red-900 text-sm">
                  <span>TOTAL DEUDA EXIGIBLE ACUMULADA:</span>
                  <span x-text="'S/ ' + formatDinero(selectedDeudor?.total_deuda)"></span>
               </div>
               <p class="text-red-700 mt-1">Cuotas pendientes: <strong x-text="selectedDeudor?.cuotas_pendientes"></strong> | Clasificación de riesgo: <strong x-text="selectedDeudor?.riesgo"></strong></p>
            </div>

            <p class="text-xs text-justify leading-relaxed">
               A fin de evitar la aplicación de intereses moratorios diarios conforme al Art. 33° del Texto Único Ordenado del Código Tributario (D.S. N° 133-2013-EF) y el inicio del procedimiento de cobranza coactiva, se le requiere acercarse a la <strong>Ventanilla de Caja de la Municipalidad Distrital de Oropesa</strong> o efectuar su regularización por la plataforma web <strong>SART</strong> dentro de los próximos <strong>7 días hábiles</strong>.
            </p>

            <div class="text-center pt-4 border-t text-xs text-gray-500">
               <p class="font-bold text-gray-700">SUBGERENCIA DE RECAUDACIÓN Y CONTROL TRIBUTARIO</p>
               <p>MUNICIPALIDAD DISTRITAL DE OROPESA - CUSCO</p>
            </div>
         </div>

         <div class="bg-gray-100 px-6 py-3 flex justify-between items-center">
            <span class="text-xs text-gray-500">Documento probatorio para sustentación de tesis</span>
            <div class="flex gap-2">
               <button @click="showNotificacionModal = false" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded text-xs">
                  Cerrar
               </button>
               <button @click="window.print()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-xs flex items-center gap-1">
                  🖨️ Imprimir Carta
               </button>
            </div>
         </div>
      </div>
   </div>

</div>

<script>
function morosidadApp() {
   return {
      anio: 2026,
      kpis: {},
      semaforo: {},
      deudores: [],
      search: '',
      filtroRiesgo: '',
      loading: false,
      showNotificacionModal: false,
      selectedDeudor: null,

      init() {
         this.fetchData();
      },

      async fetchData() {
         this.loading = true;
         try {
            let url = `../../api/admin/morosidad.php?anio=${this.anio}&limit=150`;
            if (this.search) url += `&q=${encodeURIComponent(this.search)}`;
            if (this.filtroRiesgo) url += `&riesgo=${encodeURIComponent(this.filtroRiesgo)}`;

            const res = await fetch(url);
            const data = await res.json();

            if (data.success) {
               this.kpis = data.kpis || {};
               this.semaforo = data.semaforo || {};
               this.deudores = data.deudores || [];
            }
         } catch (e) {
            console.error('Error al cargar datos de morosidad:', e);
         } finally {
            this.loading = false;
         }
      },

      abrirNotificacion(deudor) {
         this.selectedDeudor = deudor;
         this.showNotificacionModal = true;
      },

      formatDinero(val) {
         if (!val) return '0.00';
         return parseFloat(val).toLocaleString('es-PE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
      },

      imprimirReporte() {
         window.print();
      }
   }
}
</script>

<?php renderFooter(); ?>
