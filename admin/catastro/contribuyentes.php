<?php
// admin/catastro/contribuyentes.php
require_once '../includes/layout.php';
renderHeader('Contribuyentes');
?>

<div x-data="contribuyentesApp()" x-init="fetchData()" class="container mx-auto">
   <div class="mb-6 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-800">Padrón de Contribuyentes</h1>
      <button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition transform hover:scale-105">
         + Nuevo Contribuyente
      </button>
   </div>

   <!-- Search -->
   <div class="mb-6">
      <div class="flex gap-2">
         <input
            type="text"
            x-model="search"
            @keydown.enter="fetchData()"
            class="border rounded px-4 py-2 w-full md:w-1/3 focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Buscar por Nombre o DNI/RUC...">
         <button @click="fetchData()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">
            Buscar
         </button>
      </div>
   </div>

   <!-- Table -->
   <div class="bg-white shadow-md rounded overflow-x-auto">
      <table class="min-w-full table-auto">
         <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
               <th class="py-3 px-6 text-left">Documento</th>
               <th class="py-3 px-6 text-left">Nombre / Razón Social</th>
               <th class="py-3 px-6 text-left">Dirección</th>
               <th class="py-3 px-6 text-center">Acciones</th>
            </tr>
         </thead>
         <tbody class="text-gray-600 text-sm font-light">
            <template x-for="row in items" :key="row.id">
               <tr class="border-b border-gray-200 hover:bg-gray-100">
                  <td class="py-3 px-6 text-left whitespace-nowrap">
                     <span class="font-bold" x-text="row.numero_documento"></span>
                     <div class="text-xs text-gray-400" x-text="row.tipo_persona"></div>
                  </td>
                  <td class="py-3 px-6 text-left" x-text="row.nombre_razon_social"></td>
                  <td class="py-3 px-6 text-left" x-text="row.direccion_fiscal"></td>
                  <td class="py-3 px-6 text-center">
                     <button @click="openModal(row)" class="text-blue-500 hover:text-blue-700 font-bold mr-2">Editar</button>
                     <a :href="'predios.php?contribuyente_id=' + row.id" class="text-green-500 hover:text-green-700 font-bold">Ver Predios</a>
                  </td>
               </tr>
            </template>
            <tr x-show="items.length === 0">
               <td colspan="4" class="py-6 text-center text-gray-500">No se encontraron registros.</td>
            </tr>
         </tbody>
      </table>
   </div>

   <!-- Modal -->
   <div x-show="showModal" class="fixed inset-0 bg-gray-600/50 overflow-y-auto h-full w-full z-50 flex items-center justify-center" style="display: none;">
      <div class="relative p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
         <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 text-center mb-4" x-text="isEdit ? 'Editar Contribuyente' : 'Nuevo Contribuyente'"></h3>

            <form @submit.prevent="saveData">
               <div class="grid grid-cols-2 gap-4 mb-4">
                  <div>
                     <label class="block text-gray-700 text-sm font-bold mb-2">Tipo Persona</label>
                     <select x-model="form.tipo_persona" class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline">
                        <option value="NATURAL">NATURAL</option>
                        <option value="JURIDICA">JURIDICA</option>
                     </select>
                  </div>
                  <div>
                     <label class="block text-gray-700 text-sm font-bold mb-2">Nro Documento</label>
                     <input type="text" x-model="form.numero_documento" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                  </div>
               </div>

               <div class="mb-4">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Nombre / Razón Social</label>
                  <input type="text" x-model="form.nombre_razon_social" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
               </div>

               <div class="mb-4">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Dirección Fiscal</label>
                  <input type="text" x-model="form.direccion_fiscal" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
               </div>

               <div class="grid grid-cols-2 gap-4 mb-4">
                  <div>
                     <label class="block text-gray-700 text-sm font-bold mb-2">Correo</label>
                     <input type="email" x-model="form.correo" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                  </div>
                  <div>
                     <label class="block text-gray-700 text-sm font-bold mb-2">Teléfono</label>
                     <input type="text" x-model="form.telefono" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                  </div>
               </div>

               <div class="flex justify-end mt-6">
                  <button type="button" @click="showModal = false" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancelar</button>
                  <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" :disabled="loading">
                     <span x-show="!loading">Guardar</span>
                     <span x-show="loading">Guardando...</span>
                  </button>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>

<script>
   function contribuyentesApp() {
      return {
         items: [],
         search: '',
         showModal: false,
         isEdit: false,
         loading: false,
         form: {
            id: null,
            tipo_persona: 'NATURAL',
            numero_documento: '',
            nombre_razon_social: '',
            direccion_fiscal: '',
            correo: '',
            telefono: ''
         },

         async fetchData() {
            try {
               const res = await fetch(`../../api/admin/contribuyentes.php?q=${this.search}`);
               this.items = await res.json();
            } catch (e) {
               console.error(e);
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
                  id: null,
                  tipo_persona: 'NATURAL',
                  numero_documento: '',
                  nombre_razon_social: '',
                  direccion_fiscal: '',
                  correo: '',
                  telefono: ''
               };
            }
            this.showModal = true;
         },

         async saveData() {
            this.loading = true;
            try {
               const res = await fetch('../../api/admin/contribuyentes.php', {
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
            } finally {
               this.loading = false;
            }
         }
      }
   }
</script>

<?php renderFooter(); ?>