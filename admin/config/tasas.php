<?php
// admin/config/tasas.php
require_once '../includes/layout.php';
renderHeader('Tasas de Arbitrios');
?>

<div x-data="tasasApp()" x-init="fetchData()" class="container mx-auto">
   <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Tasas de Arbitrios</h1>
      <p class="text-gray-600">Configuración de costos para el año: <strong x-text="anio"></strong></p>
   </div>

   <div class="mb-6 flex gap-4">
      <input type="number" x-model="anio" class="border rounded px-3 py-2" placeholder="Cambiar Año">
      <button @click="fetchData()" class="bg-gray-500 text-white px-4 py-2 rounded">Cargar</button>
   </div>

   <form @submit.prevent="saveData" class="bg-white shadow-md rounded overflow-hidden">
      <table class="min-w-full table-auto">
         <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
               <th class="py-3 px-6 text-left">Tipo de Uso</th>
               <th class="py-3 px-6 text-right">Limpieza (Costo x m2)</th>
               <th class="py-3 px-6 text-right">Limpieza (Costo Fijo)</th>
               <th class="py-3 px-6 text-right">Seguridad (Costo Fijo)</th>
            </tr>
         </thead>
         <tbody class="text-gray-600 text-sm font-light">
            <template x-for="tipo in tipos" :key="tipo.id">
               <tr class="border-b border-gray-200 hover:bg-gray-50">
                  <td class="py-3 px-6 text-left whitespace-nowrap font-bold">
                     <span x-text="tipo.descripcion"></span>
                     <div class="text-xs text-gray-400" x-text="tipo.codigo"></div>
                  </td>
                  <td class="py-3 px-6 text-right">
                     <input type="number" step="0.0001" x-model="getTasa(tipo.id).costo_limpieza_m2" class="border rounded px-2 py-1 w-32 text-right">
                  </td>
                  <td class="py-3 px-6 text-right">
                     <input type="number" step="0.01" x-model="getTasa(tipo.id).costo_limpieza_fijo" class="border rounded px-2 py-1 w-32 text-right">
                  </td>
                  <td class="py-3 px-6 text-right">
                     <input type="number" step="0.01" x-model="getTasa(tipo.id).costo_seguridad_fijo" class="border rounded px-2 py-1 w-32 text-right">
                  </td>
               </tr>
            </template>
         </tbody>
      </table>

      <div class="p-4 bg-gray-50 text-right">
         <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow" :disabled="loading">
            <span x-show="!loading">Guardar Cambios</span>
            <span x-show="loading">Guardando...</span>
         </button>
      </div>
   </form>
</div>

<script>
   function tasasApp() {
      return {
         anio: new Date().getFullYear(),
         tipos: [],
         tasasMap: {},
         loading: false,

         async fetchData() {
            try {
               const res = await fetch(`../../api/admin/tasas.php?anio=${this.anio}`);
               const data = await res.json();
               this.tipos = data.tipos;
               this.tasasMap = data.tasas;
               // Ensure all types have an entry in map
               this.tipos.forEach(t => {
                  if (!this.tasasMap[t.id]) {
                     this.tasasMap[t.id] = {
                        tipo_uso_id: t.id,
                        costo_limpieza_m2: 0,
                        costo_limpieza_fijo: 0,
                        costo_seguridad_fijo: 0
                     };
                  }
               });
            } catch (e) {
               alert('Error al cargar datos');
            }
         },

         getTasa(tipoId) {
            return this.tasasMap[tipoId];
         },

         async saveData() {
            this.loading = true;
            const tasasArray = Object.values(this.tasasMap);

            try {
               const res = await fetch('../../api/admin/tasas.php', {
                  method: 'POST',
                  headers: {
                     'Content-Type': 'application/json'
                  },
                  body: JSON.stringify({
                     anio: this.anio,
                     tasas: tasasArray
                  })
               });
               const data = await res.json();

               if (data.error) {
                  alert(data.error);
               } else {
                  alert(data.message);
               }
            } catch (e) {
               alert('Error al guardar');
            } finally {
               this.loading = false;
            }
         }
      }
   }
</script>

<?php renderFooter(); ?>