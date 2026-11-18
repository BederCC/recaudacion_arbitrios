<?php
// admin/auditoria/logs.php
require_once '../includes/layout.php';
renderHeader('Bitácora de Auditoría');
?>

<div x-data="auditoriaApp()" x-init="init()" class="container mx-auto">
   
   <!-- Header -->
   <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
         <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-800">🛡️ Bitácora de Auditoría y Trazabilidad</h1>
            <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full shadow-sm" x-text="'Total: ' + (stats.total || items.length) + ' eventos'"></span>
         </div>
         <p class="text-xs text-gray-500 mt-1">Control de operaciones, seguridad y trazabilidad de eventos del sistema (Evaluación de Tesis UTEA - Oropesa 2026)</p>
      </div>

      <div class="flex items-center gap-2">
         <button 
            @click="simularLogs()" 
            :disabled="loading"
            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow transition transform hover:scale-105 text-sm flex items-center gap-1">
            <span x-show="!loading">⚡ Simular Logs de Tesis</span>
            <span x-show="loading">Procesando...</span>
         </button>
         <button 
            @click="limpiarLogs()" 
            class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 font-bold py-2 px-3 rounded text-sm hover:text-red-800">
            🗑️ Vaciar
         </button>
      </div>
   </div>

   <!-- KPI Cards -->
   <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
      <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
         <p class="text-xs text-gray-500 uppercase font-semibold">Total Eventos</p>
         <p class="text-2xl font-bold text-gray-800" x-text="stats.total || 0"></p>
         <span class="text-xs text-blue-600 font-medium">100% auditables</span>
      </div>
      <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
         <p class="text-xs text-gray-500 uppercase font-semibold">Accesos (Auth)</p>
         <p class="text-2xl font-bold text-gray-800" x-text="stats.total_auth || 0"></p>
         <span class="text-xs text-purple-600 font-medium">Control de sesiones</span>
      </div>
      <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
         <p class="text-xs text-gray-500 uppercase font-semibold">Caja y Recibos</p>
         <p class="text-2xl font-bold text-gray-800" x-text="stats.total_caja || 0"></p>
         <span class="text-xs text-green-600 font-medium">Trazabilidad de pagos</span>
      </div>
      <div class="bg-white p-4 rounded-lg shadow border-l-4 border-amber-500">
         <p class="text-xs text-gray-500 uppercase font-semibold">Catastro & Predios</p>
         <p class="text-2xl font-bold text-gray-800" x-text="stats.total_catastro || 0"></p>
         <span class="text-xs text-amber-600 font-medium">Movimientos padrón</span>
      </div>
      <div class="bg-white p-4 rounded-lg shadow border-l-4 border-rose-500">
         <p class="text-xs text-gray-500 uppercase font-semibold">Alertas Seguridad</p>
         <p class="text-2xl font-bold text-gray-800" x-text="stats.total_alertas_seguridad || 0"></p>
         <span class="text-xs text-rose-600 font-medium">Accesos bloqueados</span>
      </div>
   </div>

   <!-- Search & Filters Toolbar -->
   <div class="bg-white p-4 rounded-lg shadow mb-6">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
         <!-- Text search -->
         <div class="md:col-span-2">
            <label class="block text-xs font-bold text-gray-600 mb-1">Buscar por Descripción, Usuario o IP</label>
            <input 
               type="text" 
               x-model="search" 
               @keydown.enter="fetchLogs()"
               placeholder="Ej: Cobro, 192.168, predio, admin..." 
               class="border border-gray-300 rounded px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
         </div>

         <!-- Module filter -->
         <div>
            <label class="block text-xs font-bold text-gray-600 mb-1">Módulo</label>
            <select x-model="filtroModulo" @change="fetchLogs()" class="border border-gray-300 rounded px-3 py-2 text-sm w-full bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
               <option value="">Todos los Módulos</option>
               <template x-for="mod in modulos" :key="mod">
                  <option :value="mod" x-text="mod"></option>
               </template>
            </select>
         </div>

         <!-- Action filter -->
         <div>
            <label class="block text-xs font-bold text-gray-600 mb-1">Acción</label>
            <select x-model="filtroAccion" @change="fetchLogs()" class="border border-gray-300 rounded px-3 py-2 text-sm w-full bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
               <option value="">Todas las Acciones</option>
               <template x-for="acc in acciones" :key="acc">
                  <option :value="acc" x-text="acc"></option>
               </template>
            </select>
         </div>

         <!-- Action buttons -->
         <div class="flex items-end gap-2">
            <button @click="fetchLogs()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm w-full">
               Filtrar
            </button>
            <button @click="limpiarFiltros()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-3 rounded text-sm" title="Limpiar filtros">
               ✕
            </button>
         </div>
      </div>
   </div>

   <!-- Table -->
   <div class="bg-white shadow-md rounded overflow-x-auto">
      <table class="min-w-full table-auto">
         <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-xs leading-normal">
               <th class="py-3 px-3 text-center w-12">N°</th>
               <th class="py-3 px-4 text-left">Fecha y Hora</th>
               <th class="py-3 px-4 text-left">Usuario Responsable</th>
               <th class="py-3 px-3 text-center">Módulo</th>
               <th class="py-3 px-4 text-left">Acción</th>
               <th class="py-3 px-6 text-left">Descripción del Evento</th>
               <th class="py-3 px-3 text-center">IP Origen</th>
               <th class="py-3 px-3 text-center">Detalle</th>
            </tr>
         </thead>
         <tbody class="text-gray-600 text-sm font-light">
            <template x-for="(row, index) in items" :key="row.id">
               <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                  <td class="py-3 px-3 text-center font-bold text-gray-500 bg-gray-50/50" x-text="index + 1"></td>
                  
                  <td class="py-3 px-4 text-left whitespace-nowrap">
                     <span class="font-bold text-gray-800" x-text="formatDate(row.fecha_evento)"></span>
                     <div class="text-xs text-gray-400" x-text="formatTime(row.fecha_evento)"></div>
                  </td>

                  <td class="py-3 px-4 text-left whitespace-nowrap">
                     <div class="font-semibold text-gray-800" x-text="row.usuario_nombre || 'Sistema Automatizado'"></div>
                     <div class="text-xs text-gray-400" x-text="'ID: #' + (row.usuario_id || 'AUTO')"></div>
                  </td>

                  <td class="py-3 px-3 text-center whitespace-nowrap">
                     <span :class="getModuloBadgeClass(row.modulo)" class="px-2.5 py-1 rounded-full text-xs font-bold" x-text="row.modulo"></span>
                  </td>

                  <td class="py-3 px-4 text-left whitespace-nowrap">
                     <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-gray-100 text-gray-700" x-text="row.accion"></span>
                     <div class="text-xs text-gray-400 mt-0.5" x-show="row.tabla_afectada" x-text="'Tabla: ' + row.tabla_afectada + (row.registro_id ? ' (#' + row.registro_id + ')' : '')"></div>
                  </td>

                  <td class="py-3 px-6 text-left">
                     <p class="text-gray-800 font-medium text-xs leading-relaxed" x-text="row.descripcion"></p>
                  </td>

                  <td class="py-3 px-3 text-center font-mono text-xs text-gray-500 whitespace-nowrap" x-text="row.ip_origen"></td>

                  <td class="py-3 px-3 text-center whitespace-nowrap">
                     <button 
                        @click="verDetalle(row)" 
                        class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded text-xs font-bold transition">
                        Ver JSON
                     </button>
                  </td>
               </tr>
            </template>
            <tr x-show="items.length === 0 && !loading">
               <td colspan="8" class="py-8 text-center text-gray-500">
                  <div class="text-3xl mb-2">🔍</div>
                  <p class="font-semibold">No se encontraron eventos con los filtros seleccionados.</p>
                  <p class="text-xs text-gray-400 mt-1">Haz clic en "⚡ Simular Logs de Tesis" para generar eventos históricos de prueba.</p>
               </td>
            </tr>
            <tr x-show="loading">
               <td colspan="8" class="py-8 text-center text-gray-500">
                  <p class="font-semibold">Cargando registros de auditoría...</p>
               </td>
            </tr>
         </tbody>
      </table>

      <div class="p-4 bg-gray-50 border-t border-gray-200 text-xs text-gray-500 flex justify-between items-center">
         <span>Mostrando <strong class="text-gray-800" x-text="items.length"></strong> registros de auditoría</span>
         <span>Trazabilidad activa bajo estándar ISO/IEC 27001 & Modelo de Tesis SART</span>
      </div>
   </div>

   <!-- Modal JSON Inspection -->
   <div x-show="showModal" class="fixed inset-0 bg-gray-900/60 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" style="display: none;">
      <div class="relative bg-white rounded-lg max-w-2xl w-full shadow-2xl overflow-hidden border">
         <div class="bg-gray-800 text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-bold text-lg flex items-center gap-2">
               <span>📋 Detalle de Trazabilidad (Payload JSON)</span>
            </h3>
            <button @click="showModal = false" class="text-gray-400 hover:text-white text-xl font-bold">✕</button>
         </div>

         <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
            <div class="grid grid-cols-2 gap-3 text-xs bg-gray-50 p-3 rounded border">
               <div><strong>Evento ID:</strong> #<span x-text="selectedLog?.id"></span></div>
               <div><strong>Fecha/Hora:</strong> <span x-text="selectedLog?.fecha_evento"></span></div>
               <div><strong>Módulo:</strong> <span x-text="selectedLog?.modulo"></span></div>
               <div><strong>Acción:</strong> <span x-text="selectedLog?.accion"></span></div>
               <div><strong>Usuario:</strong> <span x-text="selectedLog?.usuario_nombre"></span></div>
               <div><strong>IP Origen:</strong> <span x-text="selectedLog?.ip_origen"></span></div>
            </div>

            <div>
               <h4 class="text-xs font-bold text-gray-700 uppercase mb-1">Descripción</h4>
               <p class="text-sm bg-blue-50 text-blue-900 p-2.5 rounded border border-blue-200 font-medium" x-text="selectedLog?.descripcion"></p>
            </div>

            <div x-show="selectedLog?.datos_previos">
               <h4 class="text-xs font-bold text-red-700 uppercase mb-1">Estado Previo (Antes de Modificación)</h4>
               <pre class="bg-red-50 text-red-900 p-3 rounded border border-red-200 text-xs font-mono overflow-x-auto" x-text="formatJson(selectedLog?.datos_previos)"></pre>
            </div>

            <div x-show="selectedLog?.datos_nuevos">
               <h4 class="text-xs font-bold text-green-700 uppercase mb-1">Datos Nuevos / Modificados (Payload)</h4>
               <pre class="bg-green-50 text-green-900 p-3 rounded border border-green-200 text-xs font-mono overflow-x-auto" x-text="formatJson(selectedLog?.datos_nuevos)"></pre>
            </div>
         </div>

         <div class="bg-gray-100 px-6 py-3 flex justify-end">
            <button @click="showModal = false" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
               Cerrar
            </button>
         </div>
      </div>
   </div>

</div>

<script>
function auditoriaApp() {
   return {
      items: [],
      stats: {},
      modulos: [],
      acciones: [],
      filtroModulo: '',
      filtroAccion: '',
      search: '',
      loading: false,
      showModal: false,
      selectedLog: null,

      init() {
         this.fetchLogs();
      },

      async fetchLogs() {
         this.loading = true;
         try {
            let url = '../../api/admin/auditoria.php?limit=1000';
            if (this.filtroModulo) url += `&modulo=${encodeURIComponent(this.filtroModulo)}`;
            if (this.filtroAccion) url += `&accion=${encodeURIComponent(this.filtroAccion)}`;
            if (this.search) url += `&q=${encodeURIComponent(this.search)}`;

            const res = await fetch(url);
            const data = await res.json();

            if (data.success) {
               this.items = data.logs || [];
               this.stats = data.stats || {};
               this.modulos = data.modulos || [];
               this.acciones = data.acciones || [];
            }
         } catch (e) {
            console.error('Error al cargar logs:', e);
         } finally {
            this.loading = false;
         }
      },

      limpiarFiltros() {
         this.filtroModulo = '';
         this.filtroAccion = '';
         this.search = '';
         this.fetchLogs();
      },

      async simularLogs() {
         if (!confirm('¿Deseas generar la simulación masiva de eventos de auditoría para la tesis?')) return;
         this.loading = true;
         try {
            const res = await fetch('../../api/admin/auditoria.php', {
               method: 'POST',
               headers: {'Content-Type': 'application/json'},
               body: JSON.stringify({accion: 'simular'})
            });
            const data = await res.json();
            if (data.success) {
               alert(data.message);
               this.fetchLogs();
            } else {
               alert(data.error || 'Error al simular logs');
            }
         } catch (e) {
            alert('Error en la petición de simulación');
         } finally {
            this.loading = false;
         }
      },

      async limpiarLogs() {
         if (!confirm('¿Seguro que deseas vaciar toda la bitácora de auditoría?')) return;
         this.loading = true;
         try {
            const res = await fetch('../../api/admin/auditoria.php', {
               method: 'POST',
               headers: {'Content-Type': 'application/json'},
               body: JSON.stringify({accion: 'limpiar'})
            });
            const data = await res.json();
            if (data.success) {
               this.fetchLogs();
            }
         } catch (e) {
            alert('Error al vaciar logs');
         } finally {
            this.loading = false;
         }
      },

      verDetalle(log) {
         this.selectedLog = log;
         this.showModal = true;
      },

      formatDate(dt) {
         if (!dt) return '';
         const d = new Date(dt);
         return d.toLocaleDateString('es-PE', {day: '2-digit', month: '2-digit', year: 'numeric'});
      },

      formatTime(dt) {
         if (!dt) return '';
         const d = new Date(dt);
         return d.toLocaleTimeString('es-PE', {hour: '2-digit', minute: '2-digit', second: '2-digit'});
      },

      formatJson(val) {
         if (!val) return 'Sin datos adicionales';
         try {
            const parsed = typeof val === 'string' ? JSON.parse(val) : val;
            return JSON.stringify(parsed, null, 2);
         } catch(e) {
            return val;
         }
      },

      getModuloBadgeClass(modulo) {
         switch (modulo) {
            case 'AUTH': return 'bg-purple-100 text-purple-700';
            case 'CATASTRO': return 'bg-amber-100 text-amber-700';
            case 'EMISION': return 'bg-indigo-100 text-indigo-700';
            case 'CAJA': return 'bg-green-100 text-green-700';
            case 'CONFIG': return 'bg-blue-100 text-blue-700';
            default: return 'bg-gray-100 text-gray-700';
         }
      }
   }
}
</script>

<?php renderFooter(); ?>
