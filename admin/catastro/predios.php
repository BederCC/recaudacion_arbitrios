<?php
// admin/catastro/predios.php
require_once '../includes/layout.php';
renderHeader('Predios');
?>

<div x-data="prediosApp()" x-init="init()" class="container mx-auto">
   <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div class="flex items-center gap-3">
         <h1 class="text-2xl font-bold text-gray-800">Padrón de Predios</h1>
         <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full shadow-sm" x-text="'Total: ' + items.length + ' predios'"></span>
         <template x-if="urlContribuyenteId">
            <span class="bg-amber-100 text-amber-800 text-xs px-2.5 py-1 rounded-full flex items-center gap-1">
               Filtrado por contribuyente
               <button @click="clearFilter()" class="underline font-bold text-amber-900 ml-1 hover:text-black">✕ Quitar filtro</button>
            </span>
         </template>
      </div>
      <button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition transform hover:scale-105">
         + Nuevo Predio
      </button>
   </div>

   <!-- Search & Filter -->
   <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
      <div class="flex gap-2 w-full md:w-auto">
         <input
            type="text"
            x-model="search"
            @keydown.enter="fetchData()"
            class="border rounded px-4 py-2 w-full md:w-80 focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
            placeholder="Buscar por Dirección, Código o Propietario...">
         <button @click="fetchData()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow text-sm font-semibold">
            Buscar
         </button>
         <button x-show="search" @click="search = ''; fetchData()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded text-sm">
            Limpiar
         </button>
      </div>
      <div class="text-xs text-gray-500">
         Mostrando <strong class="text-gray-800" x-text="items.length"></strong> predios
      </div>
   </div>

   <!-- Table -->
   <div class="bg-white shadow-md rounded overflow-x-auto">
      <table class="min-w-full table-auto">
         <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-xs leading-normal">
               <th class="py-3 px-4 text-center w-16">N°</th>
               <th class="py-3 px-6 text-left">Código / Dirección</th>
               <th class="py-3 px-6 text-left">Propietario</th>
               <th class="py-3 px-6 text-left">Uso</th>
               <th class="py-3 px-6 text-right">Autovaluo</th>
               <th class="py-3 px-6 text-center">Estado</th>
               <th class="py-3 px-6 text-center">Acciones</th>
            </tr>
         </thead>
         <tbody class="text-gray-600 text-sm font-light">
            <template x-for="(row, index) in items" :key="row.id">
               <tr class="border-b border-gray-200 hover:bg-gray-100">
                  <td class="py-3 px-4 text-center font-bold text-gray-500 bg-gray-50/50" x-text="index + 1"></td>
                  <td class="py-3 px-6 text-left">
                     <div class="font-bold text-gray-800" x-text="row.codigo_catastral"></div>
                     <div class="text-xs text-gray-500" x-text="row.direccion_predio"></div>
                  </td>
                  <td class="py-3 px-6 text-left font-medium" x-text="row.nombre_razon_social"></td>
                  <td class="py-3 px-6 text-left">
                     <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs" x-text="row.uso_descripcion"></span>
                  </td>
                  <td class="py-3 px-6 text-right font-medium text-gray-800" x-text="'S/ ' + parseFloat(row.valor_autovaluo).toFixed(2)"></td>
                  <td class="py-3 px-6 text-center">
                     <span :class="row.estado === 'ACTIVO' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="py-1 px-3 rounded-full text-xs font-semibold" x-text="row.estado"></span>
                  </td>
                  <td class="py-3 px-6 text-center whitespace-nowrap">
                     <button @click="openModal(row)" class="text-blue-500 hover:text-blue-700 font-bold">Editar</button>
                  </td>
               </tr>
            </template>
            <tr x-show="items.length === 0">
               <td colspan="7" class="py-6 text-center text-gray-500">No se encontraron predios registrados.</td>
            </tr>
         </tbody>
      </table>
      <div class="p-4 bg-gray-50 border-t border-gray-200 text-xs text-gray-500 flex justify-between items-center">
         <span>Total de predios en lista: <strong class="text-gray-800" x-text="items.length"></strong></span>
      </div>
   </div>

   <!-- Modal -->
   <div x-show="showModal" class="fixed inset-0 bg-gray-600/50 overflow-y-auto h-full w-full z-50 flex items-center justify-center" style="display: none;">
      <div class="relative p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
         <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 text-center mb-4" x-text="isEdit ? 'Editar Predio' : 'Nuevo Predio'"></h3>

            <form @submit.prevent="saveData">
               <!-- Taxpayer Search -->
               <div class="mb-4 relative">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Propietario</label>
                  <input type="hidden" x-model="form.contribuyente_id">

                  <div class="flex">
                     <input
                        type="text"
                        x-model="searchContribuyenteQuery"
                        @input.debounce.300ms="searchContribuyente"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Buscar por Nombre o DNI..."
                        autocomplete="off">
                     <span x-show="form.contribuyente_id" class="ml-2 flex items-center text-green-600 font-bold">✓</span>
                  </div>

                  <!-- Search Results Dropdown -->
                  <div x-show="contribuyenteResults.length > 0" class="absolute z-10 w-full bg-white shadow-xl rounded-b-lg border border-t-0 border-gray-200 mt-1 max-h-40 overflow-y-auto">
                     <ul>
                        <template x-for="c in contribuyenteResults" :key="c.id">
                           <li @click="selectContribuyente(c)" class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b last:border-b-0">
                              <div class="font-bold text-sm" x-text="c.nombre_razon_social"></div>
                              <div class="text-xs text-gray-500" x-text="c.numero_documento"></div>
                           </li>
                        </template>
                     </ul>
                  </div>
               </div>

               <div class="grid grid-cols-2 gap-4 mb-4">
                  <div>
                     <label class="block text-gray-700 text-sm font-bold mb-2">Año Declaración</label>
                     <input type="number" x-model="form.anio_declaracion" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                  </div>
                  <div>
                     <label class="block text-gray-700 text-sm font-bold mb-2">Código Catastral</label>
                     <input type="text" x-model="form.codigo_catastral" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-100" placeholder="Generado Automáticamente" readonly>
                  </div>
               </div>

               <div class="mb-4">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Dirección del Predio</label>
                  <input type="text" x-model="form.direccion_predio" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
               </div>

               <div class="grid grid-cols-2 gap-4 mb-4">
                  <div>
                     <label class="block text-gray-700 text-sm font-bold mb-2">Tipo de Uso</label>
                     <select x-model="form.tipo_uso_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline">
                        <option value="">Seleccione...</option>
                        <template x-for="uso in usos" :key="uso.id">
                           <option :value="uso.id" x-text="uso.descripcion"></option>
                        </template>
                     </select>
                  </div>
                  <div>
                     <label class="block text-gray-700 text-sm font-bold mb-2">Estado</label>
                     <select x-model="form.estado" class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline">
                        <option value="ACTIVO">ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
                        <option value="FISCALIZACION">FISCALIZACION</option>
                     </select>
                  </div>
               </div>

               <div class="grid grid-cols-3 gap-4 mb-4 bg-gray-50 p-4 rounded">
                  <div>
                     <label class="block text-gray-700 text-sm font-bold mb-2">Área Const. (m2)</label>
                     <input type="number" step="0.01" x-model="form.area_construida" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                  </div>
                  <div>
                     <label class="block text-gray-700 text-sm font-bold mb-2">Frente (ml)</label>
                     <input type="number" step="0.01" x-model="form.longitud_frente" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                  </div>
                  <div>
                     <label class="block text-gray-700 text-sm font-bold mb-2">Autovaluo (S/)</label>
                     <input type="number" step="0.01" x-model="form.valor_autovaluo" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-100" readonly>
                  </div>
               </div>

               <div class="mb-4 bg-blue-50 p-4 rounded border border-blue-200">
                  <div class="flex justify-between items-center">
                     <label class="block text-blue-800 text-sm font-bold">Impuesto Predial Estimado (Anual):</label>
                     <span class="text-xl font-bold text-blue-900" x-text="'S/ ' + impuestoCalculado"></span>
                  </div>
                  <div class="text-xs text-blue-600 mt-1">
                     Calculado en base a la UIT del año <span x-text="form.anio_declaracion"></span> (S/ <span x-text="uit"></span>)
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
   function prediosApp() {
      return {
         items: [],
         usos: [],
         search: '',
         urlContribuyenteId: null,
         showModal: false,
         isEdit: false,
         loading: false,
         uit: 0,
         impuestoCalculado: '0.00',

         // Search Contribuyente
         searchContribuyenteQuery: '',
         contribuyenteResults: [],

         form: {
            id: null,
            contribuyente_id: null,
            anio_declaracion: new Date().getFullYear(),
            codigo_catastral: '',
            direccion_predio: '',
            tipo_uso_id: '',
            area_construida: 0,
            longitud_frente: 0,
            valor_autovaluo: 0,
            estado: 'ACTIVO'
         },

         init() {
            const urlParams = new URLSearchParams(window.location.search);
            this.urlContribuyenteId = urlParams.get('contribuyente_id');
            this.fetchData();
            this.$watch('form.area_construida', () => this.calculateAutovaluo());
            this.$watch('form.longitud_frente', () => this.calculateAutovaluo());
            this.$watch('form.tipo_uso_id', () => this.calculateAutovaluo());
            this.$watch('form.anio_declaracion', () => this.fetchUITAndCalculate());
            this.fetchUITAndCalculate(); // Initial fetch
         },

         clearFilter() {
            this.urlContribuyenteId = null;
            window.history.replaceState({}, document.title, window.location.pathname);
            this.fetchData();
         },

         async fetchUITAndCalculate() {
            const anio = this.form.anio_declaracion;
            if (!anio) return;

            try {
               const res = await fetch(`../../api/admin/uit.php?anio=${anio}`);
               const data = await res.json();
               if (data && data.valor) {
                  this.uit = parseFloat(data.valor);
               } else {
                  this.uit = 0;
               }
               this.calculateImpuesto();
            } catch (e) {
               console.error('Error fetching UIT:', e);
               this.uit = 0;
            }
         },

         calculateAutovaluo() {
            // Formula: (Area * 300 * Factor) + (Frente * 50)
            const area = parseFloat(this.form.area_construida) || 0;
            const frente = parseFloat(this.form.longitud_frente) || 0;
            const usoId = this.form.tipo_uso_id;

            let factor = 1;
            const usoSelected = this.usos.find(u => u.id == usoId);
            if (usoSelected) {
               factor = parseFloat(usoSelected.factor_limpieza) || 1; // Using factor_limpieza as a proxy for value factor
            }

            const autovaluo = (area * 300 * factor) + (frente * 50);
            this.form.valor_autovaluo = autovaluo.toFixed(2);

            this.calculateImpuesto();
         },

         calculateImpuesto() {
            if (!this.uit || this.uit === 0) {
               this.impuestoCalculado = '0.00';
               return;
            }

            const autovaluo = parseFloat(this.form.valor_autovaluo) || 0;
            let impuesto = 0;

            // Tramo 1: Hasta 15 UIT (0.2%)
            const tramo1 = 15 * this.uit;
            if (autovaluo > 0) {
               const base1 = Math.min(autovaluo, tramo1);
               impuesto += base1 * 0.002;
            }

            // Tramo 2: Más de 15 UIT hasta 60 UIT (0.6%)
            const tramo2 = 60 * this.uit;
            if (autovaluo > tramo1) {
               const base2 = Math.min(autovaluo - tramo1, tramo2 - tramo1);
               impuesto += base2 * 0.006;
            }

            // Tramo 3: Más de 60 UIT (1.0%)
            if (autovaluo > tramo2) {
               const base3 = autovaluo - tramo2;
               impuesto += base3 * 0.01;
            }

            this.impuestoCalculado = impuesto.toFixed(2);
         },

         async fetchData() {
            try {
               let url = '../../api/admin/predios.php?limit=2000';
               if (this.urlContribuyenteId) {
                  url += `&contribuyente_id=${encodeURIComponent(this.urlContribuyenteId)}`;
               }
               if (this.search) {
                  url += `&q=${encodeURIComponent(this.search)}`;
               }

               // Fetch Predios
               const resPredios = await fetch(url);
               const dataPredios = await resPredios.json();
               this.items = dataPredios.predios || [];

               // Fetch Usos separately
               const resUsos = await fetch('../../api/admin/get_usos.php');
               const dataUsos = await resUsos.json();

               if (Array.isArray(dataUsos)) {
                  this.usos = dataUsos;
               } else {
                  console.error('Usos data is not an array:', dataUsos);
                  this.usos = [];
               }

            } catch (e) {
               console.error(e);
            }
         },

         async searchContribuyente() {
            if (this.searchContribuyenteQuery.length < 3) {
               this.contribuyenteResults = [];
               return;
            }
            const res = await fetch(`../../api/admin/contribuyentes.php?q=${this.searchContribuyenteQuery}`);
            this.contribuyenteResults = await res.json();
         },

         selectContribuyente(c) {
            this.form.contribuyente_id = c.id;
            this.searchContribuyenteQuery = c.nombre_razon_social;
            this.contribuyenteResults = [];
         },

         openModal(data = null) {
            this.isEdit = !!data;
            this.contribuyenteResults = [];

            if (data) {
               this.form = {
                  ...data
               };
               this.searchContribuyenteQuery = data.nombre_razon_social; // Pre-fill name
               this.fetchUITAndCalculate(); // Recalculate for edit
            } else {
               this.form = {
                  id: null,
                  contribuyente_id: null,
                  anio_declaracion: new Date().getFullYear(),
                  codigo_catastral: '',
                  direccion_predio: '',
                  tipo_uso_id: '',
                  area_construida: 0,
                  longitud_frente: 0,
                  valor_autovaluo: 0,
                  estado: 'ACTIVO'
               };
               this.searchContribuyenteQuery = '';
               this.fetchNextCode();
               this.fetchUITAndCalculate(); // Recalculate for new
            }
            this.showModal = true;
         },

         async fetchNextCode() {
            try {
               const res = await fetch('../../api/admin/predios.php?action=next_code');
               const data = await res.json();
               if (data.next_code) {
                  this.form.codigo_catastral = data.next_code;
               }
            } catch (e) {
               console.error('Error fetching next code');
            }
         },

         async saveData() {
            if (!this.form.contribuyente_id) {
               alert('Debe seleccionar un propietario');
               return;
            }

            this.loading = true;
            try {
               const res = await fetch('../../api/admin/predios.php', {
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