<?php
// admin/config/anios.php
require_once '../includes/layout.php';
renderHeader('Años Fiscales');
?>

<div x-data="aniosApp()" x-init="fetchData()" class="container mx-auto">
   <div class="mb-6 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-800">Configuración de Años Fiscales</h1>
      <button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
         + Nuevo Año
      </button>
   </div>

   <div class="bg-white shadow-md rounded overflow-x-auto">
      <table class="min-w-full table-auto">
         <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
               <th class="py-3 px-6 text-left">Año</th>
               <th class="py-3 px-6 text-right">UIT (S/)</th>
               <th class="py-3 px-6 text-right">TIM Diaria (%)</th>
               <th class="py-3 px-6 text-center">Estado</th>
               <th class="py-3 px-6 text-center">Acciones</th>
            </tr>
         </thead>
         <tbody class="text-gray-600 text-sm font-light">
            <template x-for="row in items" :key="row.anio">
               <tr class="border-b border-gray-200 hover:bg-gray-100">
                  <td class="py-3 px-6 text-left whitespace-nowrap font-bold" x-text="row.anio"></td>
                  <td class="py-3 px-6 text-right" x-text="parseFloat(row.valor_uit).toFixed(2)"></td>
                  <td class="py-3 px-6 text-right" x-text="row.interes_moratorio_diario"></td>
                  <td class="py-3 px-6 text-center">
                     <span :class="row.estado === 'ACTIVO' ? 'bg-green-200 text-green-600' : 'bg-gray-200 text-gray-600'" class="py-1 px-3 rounded-full text-xs" x-text="row.estado"></span>
                  </td>
                  <td class="py-3 px-6 text-center">
                     <button @click="openModal(row)" class="text-blue-500 hover:text-blue-700 font-bold">Editar</button>
                  </td>
               </tr>
            </template>
         </tbody>
      </table>
   </div>

   <!-- Modal -->
   <div x-show="showModal" class="fixed inset-0 bg-gray-600/50 overflow-y-auto h-full w-full z-50 flex items-center justify-center" style="display: none;">
      <div class="relative p-5 border w-96 shadow-lg rounded-md bg-white">
         <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900" x-text="isEdit ? 'Editar Año Fiscal' : 'Nuevo Año Fiscal'"></h3>
            <form @submit.prevent="saveData" class="mt-2 text-left">
               <div class="mb-4">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Año</label>
                  <input type="number" x-model="form.anio" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
               </div>
               <div class="mb-4">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Valor UIT</label>
                  <input type="number" step="0.01" x-model="form.valor_uit" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
               </div>
               <div class="mb-4">
                  <label class="block text-gray-700 text-sm font-bold mb-2">TIM Diaria (%)</label>
                  <input type="number" step="0.00001" x-model="form.interes_moratorio_diario" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
               </div>
               <div class="mb-4">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Estado</label>
                  <select x-model="form.estado" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                     <option value="PLANIFICACION">PLANIFICACION</option>
                     <option value="ACTIVO">ACTIVO</option>
                     <option value="CERRADO">CERRADO</option>
                  </select>
               </div>
               <div class="flex justify-end">
                  <button type="button" @click="showModal = false" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancelar</button>
                  <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Guardar</button>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>

<script>
   function aniosApp() {
      return {
         items: [],
         showModal: false,
         isEdit: false,
         form: {
            anio: new Date().getFullYear() + 1,
            valor_uit: 0,
            interes_moratorio_diario: 0.033,
            estado: 'PLANIFICACION'
         },

         async fetchData() {
            try {
               const res = await fetch('../../api/admin/anios.php');
               this.items = await res.json();
            } catch (e) {
               alert('Error al cargar datos');
            }
         },

         openModal(data = null) {
            this.isEdit = !!data;
            if (data) {
               this.form = {
                  ...data
               };
            } else {
               this.form = {
                  anio: new Date().getFullYear() + 1,
                  valor_uit: 0,
                  interes_moratorio_diario: 0.033,
                  estado: 'PLANIFICACION'
               };
            }
            this.showModal = true;
         },

         async saveData() {
            try {
               const res = await fetch('../../api/admin/anios.php', {
                  method: 'POST',
                  headers: {
                     'Content-Type': 'application/json'
                  },
                  body: JSON.stringify(this.form)
               });
               const data = await res.json();

               if (data.error) {
                  alert(data.error);
               } else {
                  this.showModal = false;
                  this.fetchData();
               }
            } catch (e) {
               alert('Error al guardar');
            }
         }
      }
   }
</script>

<?php renderFooter(); ?>