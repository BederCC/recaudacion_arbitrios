<?php
// admin/requerimientos_datos.php
require_once 'includes/layout.php';
renderHeader('Requerimientos de Información');
?>

<div class="container mx-auto max-w-4xl">
   <div class="flex justify-between items-center mb-8">
      <div>
         <h1 class="text-3xl font-bold text-gray-800">Requerimientos de Información</h1>
         <p class="text-gray-600">Documento de solicitud de datos para la configuración del sistema SART.</p>
      </div>
      <a href="exportar_requerimientos_pdf.php" target="_blank" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow flex items-center">
         <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
         </svg>
         Descargar PDF
      </a>
   </div>

   <div id="documento-requerimientos" class="bg-white shadow-lg rounded-lg p-10 print:shadow-none">

      <div class="flex justify-between items-center border-b border-gray-200 pb-6 mb-8">
         <div class="shrink-0">
            <img src="../src/img/logovd.png" alt="Logo Empresa" class="h-24">
         </div>
         <div class="text-right">
            <h2 class="text-2xl font-bold text-blue-900 uppercase">Solicitud de Información Base</h2>
            <p class="text-sm text-gray-500 mt-1">Sistema de Administración de Recaudación Tributaria (SART)</p>
            <p class="text-xs text-gray-400 mt-2">Fecha: <?php echo date('d/m/Y'); ?></p>
         </div>
      </div>

      <div class="space-y-8">
         <!-- 1. Contribuyentes -->
         <section>
            <h3 class="text-lg font-bold text-gray-800 border-b-2 border-blue-500 pb-2 mb-4 flex items-center">
               <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">1</span>
               Padrón de Contribuyentes
            </h3>
            <p class="text-sm text-gray-600 mb-4">Se requiere la lista actualizada de ciudadanos y empresas sujetos a tributación.</p>
            <table class="min-w-full border border-gray-300 text-sm">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-700">Campo Requerido</th>
                     <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-700">Descripción / Formato</th>
                     <th class="border border-gray-300 px-4 py-2 text-center font-semibold text-gray-700">Obligatorio</th>
                  </tr>
               </thead>
               <tbody>
                  <tr>
                     <td class="border border-gray-300 px-4 py-2">Tipo de Persona</td>
                     <td class="border border-gray-300 px-4 py-2">Natural o Jurídica</td>
                     <td class="border border-gray-300 px-4 py-2 text-center text-red-600 font-bold">Sí</td>
                  </tr>
                  <tr>
                     <td class="border border-gray-300 px-4 py-2">Número Documento</td>
                     <td class="border border-gray-300 px-4 py-2">DNI (8 dígitos) o RUC (11 dígitos)</td>
                     <td class="border border-gray-300 px-4 py-2 text-center text-red-600 font-bold">Sí</td>
                  </tr>
                  <tr>
                     <td class="border border-gray-300 px-4 py-2">Nombre / Razón Social</td>
                     <td class="border border-gray-300 px-4 py-2">Nombre completo o nombre de la empresa</td>
                     <td class="border border-gray-300 px-4 py-2 text-center text-red-600 font-bold">Sí</td>
                  </tr>
                  <tr>
                     <td class="border border-gray-300 px-4 py-2">Dirección Fiscal</td>
                     <td class="border border-gray-300 px-4 py-2">Domicilio para notificaciones</td>
                     <td class="border border-gray-300 px-4 py-2 text-center text-gray-500">No</td>
                  </tr>
                  <tr>
                     <td class="border border-gray-300 px-4 py-2">Correo Electrónico / Email</td>
                     <td class="border border-gray-300 px-4 py-2">Para notificaciones e ingreso al sistema</td>
                     <td class="border border-gray-300 px-4 py-2 text-center text-gray-500">No</td>
                  </tr>
                  <tr>
                     <td class="border border-gray-300 px-4 py-2">Teléfono / Celular</td>
                     <td class="border border-gray-300 px-4 py-2">Para contacto directo</td>
                     <td class="border border-gray-300 px-4 py-2 text-center text-gray-500">No</td>
                  </tr>
               </tbody>
            </table>
         </section>

         <!-- 2. Predios -->
         <section>
            <h3 class="text-lg font-bold text-gray-800 border-b-2 border-green-500 pb-2 mb-4 flex items-center">
               <span class="bg-green-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">2</span>
               Registro de Predios (Catastro)
            </h3>
            <p class="text-sm text-gray-600 mb-4">Información física de los inmuebles. El Autovalúo se calculará automáticamente.</p>
            <table class="min-w-full border border-gray-300 text-sm">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-700">Campo Requerido</th>
                     <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-700">Descripción / Formato</th>
                     <th class="border border-gray-300 px-4 py-2 text-center font-semibold text-gray-700">Obligatorio</th>
                  </tr>
               </thead>
               <tbody>
                  <tr>
                     <td class="border border-gray-300 px-4 py-2">Contribuyente (Propietario)</td>
                     <td class="border border-gray-300 px-4 py-2">DNI o RUC del propietario (Debe existir en Padrón de Contribuyentes)</td>
                     <td class="border border-gray-300 px-4 py-2 text-center text-red-600 font-bold">Sí</td>
                  </tr>
                  <tr>
                     <td class="border border-gray-300 px-4 py-2">Código Catastral</td>
                     <td class="border border-gray-300 px-4 py-2">Código único del predio</td>
                     <td class="border border-gray-300 px-4 py-2 text-center  text-red-600 font-bold">Sí</td>
                  </tr>
                  <tr>
                     <td class="border border-gray-300 px-4 py-2">Dirección del Predio</td>
                     <td class="border border-gray-300 px-4 py-2">Ubicación exacta del inmueble</td>
                     <td class="border border-gray-300 px-4 py-2 text-center text-red-600 font-bold">Sí</td>
                  </tr>
                  <tr>
                     <td class="border border-gray-300 px-4 py-2">Tipo de Uso</td>
                     <td class="border border-gray-300 px-4 py-2">Casa Habitación, Comercio, Industria, Terreno, Otros</td>
                     <td class="border border-gray-300 px-4 py-2 text-center text-red-600 font-bold">Sí</td>
                  </tr>
                  <tr>
                     <td class="border border-gray-300 px-4 py-2">Área Construida</td>
                     <td class="border border-gray-300 px-4 py-2">Total en metros cuadrados (m²)</td>
                     <td class="border border-gray-300 px-4 py-2 text-center text-red-600 font-bold">Sí</td>
                  </tr>
                  <tr>
                     <td class="border border-gray-300 px-4 py-2">Longitud de Frente</td>
                     <td class="border border-gray-300 px-4 py-2">Metros lineales de fachada</td>
                     <td class="border border-gray-300 px-4 py-2 text-center text-red-600 font-bold">Sí</td>
                  </tr>
               </tbody>
            </table>
         </section>

         <!-- 3. Tasas -->
         <section>
            <h3 class="text-lg font-bold text-gray-800 border-b-2 border-yellow-500 pb-2 mb-4 flex items-center">
               <span class="bg-yellow-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">3</span>
               Tasas de Arbitrios Municipales
            </h3>
            <p class="text-sm text-gray-600 mb-4">Estructura de costos aprobada por ordenanza municipal para el año fiscal.</p>

            <h4 class="text-md font-bold text-gray-700 mb-2 mt-6">3.1. Configuración de Costos por Tipo de Uso</h4>
            <p class="text-xs text-gray-500 mb-2">Favor de completar la siguiente tabla con los valores vigentes:</p>
            <div class="overflow-x-auto mb-6">
               <table class="min-w-full border border-gray-300 text-sm text-center">
                  <thead class="bg-gray-100 font-bold text-gray-700">
                     <tr>
                        <th class="border border-gray-300 px-4 py-2 text-left">TIPO DE USO</th>
                        <th class="border border-gray-300 px-4 py-2">LIMPIEZA<br><span class="text-xs font-normal">(Costo x m²)</span></th>
                        <th class="border border-gray-300 px-4 py-2">LIMPIEZA<br><span class="text-xs font-normal">(Costo Fijo)</span></th>
                        <th class="border border-gray-300 px-4 py-2">SEGURIDAD<br><span class="text-xs font-normal">(Costo Fijo)</span></th>
                     </tr>
                  </thead>
                  <tbody>
                     <tr>
                        <td class="border border-gray-300 px-4 py-2 text-left font-semibold">CASA HABITACIÓN</td>
                        <td class="border border-gray-300 px-4 py-2 bg-yellow-50"></td>
                        <td class="border border-gray-300 px-4 py-2 bg-yellow-50"></td>
                        <td class="border border-gray-300 px-4 py-2 bg-yellow-50"></td>
                     </tr>
                     <tr>
                        <td class="border border-gray-300 px-4 py-2 text-left font-semibold">COMERCIO</td>
                        <td class="border border-gray-300 px-4 py-2 bg-yellow-50"></td>
                        <td class="border border-gray-300 px-4 py-2 bg-yellow-50"></td>
                        <td class="border border-gray-300 px-4 py-2 bg-yellow-50"></td>
                     </tr>
                     <tr>
                        <td class="border border-gray-300 px-4 py-2 text-left font-semibold">INDUSTRIA</td>
                        <td class="border border-gray-300 px-4 py-2 bg-yellow-50"></td>
                        <td class="border border-gray-300 px-4 py-2 bg-yellow-50"></td>
                        <td class="border border-gray-300 px-4 py-2 bg-yellow-50"></td>
                     </tr>
                     <tr>
                        <td class="border border-gray-300 px-4 py-2 text-left font-semibold">TERRENO SIN CONSTRUIR</td>
                        <td class="border border-gray-300 px-4 py-2 bg-yellow-50"></td>
                        <td class="border border-gray-300 px-4 py-2 bg-yellow-50"></td>
                        <td class="border border-gray-300 px-4 py-2 bg-yellow-50"></td>
                     </tr>
                     <tr>
                        <td class="border border-gray-300 px-4 py-2 text-left font-semibold">OTROS FINES</td>
                        <td class="border border-gray-300 px-4 py-2 bg-yellow-50"></td>
                        <td class="border border-gray-300 px-4 py-2 bg-yellow-50"></td>
                        <td class="border border-gray-300 px-4 py-2 bg-yellow-50"></td>
                     </tr>
                  </tbody>
               </table>
            </div>

            <h4 class="text-md font-bold text-gray-700 mb-2">3.2. Detalles Adicionales</h4>
            <table class="min-w-full border border-gray-300 text-sm">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-700">Concepto</th>
                     <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-700">Detalle Requerido</th>
                  </tr>
               </thead>
               <tbody>
                  <tr>
                     <td class="border border-gray-300 px-4 py-2 font-bold">Impuesto Predial (Autovalúo)</td>
                     <td class="border border-gray-300 px-4 py-2">
                        <ul class="list-disc list-inside">
                           <li>Tabla de valores unitarios de edificación y terrenos.</li>
                           <li>Tramos y alícuotas (si difieren de la ley general).</li>
                        </ul>
                     </td>
                  </tr>
               </tbody>
            </table>
         </section>

         <!-- 4. Formato de Entrega -->
         <section>
            <h3 class="text-lg font-bold text-gray-800 border-b-2 border-purple-500 pb-2 mb-4 flex items-center">
               <span class="bg-purple-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">4</span>
               Formato de Entrega (Ejemplo)
            </h3>
            <p class="text-sm text-gray-600 mb-4">
               Para facilitar la importación masiva de datos, se recomienda entregar la información en formato <strong>Excel (.xlsx)</strong> en dos hojas separadas:
            </p>

            <div class="space-y-6">
               <!-- Tabla Contribuyentes -->
               <div>
                  <h4 class="text-sm font-bold text-gray-700 mb-2">Tabla 1: Contribuyentes (Personas)</h4>
                  <div class="overflow-x-auto">
                     <table class="min-w-full border border-gray-300 text-xs text-center">
                        <thead class="bg-gray-100 font-bold text-gray-700">
                           <tr>
                              <th class="border px-2 py-1">TIPO_PERSONA</th>
                              <th class="border px-2 py-1">DNI_RUC</th>
                              <th class="border px-2 py-1">NOMBRE_RAZON_SOCIAL</th>
                              <th class="border px-2 py-1">DIRECCION_FISCAL</th>
                              <th class="border px-2 py-1">CORREO</th>
                              <th class="border px-2 py-1">TELEFONO</th>
                           </tr>
                        </thead>
                        <tbody>
                           <tr>
                              <td class="border px-2 py-1">NATURAL</td>
                              <td class="border px-2 py-1">12345678</td>
                              <td class="border px-2 py-1">JUAN PEREZ</td>
                              <td class="border px-2 py-1">AV. LIMA 123</td>
                              <td class="border px-2 py-1">juan@email.com</td>
                              <td class="border px-2 py-1">999888777</td>
                           </tr>
                           <tr>
                              <td class="border px-2 py-1">JURIDICA</td>
                              <td class="border px-2 py-1">20100100101</td>
                              <td class="border px-2 py-1">EMPRESA SAC</td>
                              <td class="border px-2 py-1">JR. CUSCO 456</td>
                              <td class="border px-2 py-1">contacto@empresa.com</td>
                              <td class="border px-2 py-1">01-444-5555</td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
               </div>

               <!-- Tabla Predios -->
               <div>
                  <h4 class="text-sm font-bold text-gray-700 mb-2">Tabla 2: Predios (Inmuebles)</h4>
                  <div class="overflow-x-auto">
                     <table class="min-w-full border border-gray-300 text-xs text-center">
                        <thead class="bg-gray-100 font-bold text-gray-700">
                           <tr>
                              <th class="border px-2 py-1">DNI_RUC_PROPIETARIO</th>
                              <th class="border px-2 py-1">CODIGO_CATASTRAL</th>
                              <th class="border px-2 py-1">DIRECCION_PREDIO</th>
                              <th class="border px-2 py-1">TIPO_USO (1-5)</th>
                              <th class="border px-2 py-1">AREA_CONSTRUIDA_M2</th>
                              <th class="border px-2 py-1">LONGITUD_FRENTE_M</th>
                           </tr>
                        </thead>
                        <tbody>
                           <tr>
                              <td class="border px-2 py-1">12345678</td>
                              <td class="border px-2 py-1">01-001-001</td>
                              <td class="border px-2 py-1">AV. LIMA 123</td>
                              <td class="border px-2 py-1">1 (CASA)</td>
                              <td class="border px-2 py-1">120.00</td>
                              <td class="border px-2 py-1">8.00</td>
                           </tr>
                           <tr>
                              <td class="border px-2 py-1">20100100101</td>
                              <td class="border px-2 py-1">02-050-010</td>
                              <td class="border px-2 py-1">JR. CUSCO 456</td>
                              <td class="border px-2 py-1">2 (COMERCIO)</td>
                              <td class="border px-2 py-1">80.50</td>
                              <td class="border px-2 py-1">6.00</td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
         </section>

         <!-- 5. Fórmulas -->
         <section>
            <h3 class="text-lg font-bold text-gray-800 border-b-2 border-red-500 pb-2 mb-4 flex items-center">
               <span class="bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">5</span>
               Fórmulas de Cálculo
            </h3>
            <p class="text-sm text-gray-600 mb-4">
               Se requiere detallar la metodología para el cálculo de impuestos.
            </p>
            <div class="bg-gray-50 p-4 rounded border border-gray-200">
               <h4 class="font-bold text-gray-800 mb-2">Impuesto Predial (Autovalúo)</h4>
               <p class="text-sm text-gray-700 mb-2">
                  Favor de proporcionar la fórmula exacta o los factores utilizados para determinar el Autovalúo, incluyendo:
               </p>
               <ul class="list-disc list-inside text-sm text-gray-600 ml-4">
                  <li>Valores arancelarios de terrenos.</li>
                  <li>Valores unitarios oficiales de edificación.</li>
                  <li>Factores de depreciación por antigüedad y estado de conservación.</li>
               </ul>
            </div>
         </section>

         <div class="mt-8 p-4 bg-gray-50 border border-gray-200 rounded text-xs text-gray-500 text-center">
            <p>Este documento es generado automáticamente por el sistema SART para fines de recolección de datos.</p>
         </div>
      </div>
   </div>
</div>

<?php renderFooter(); ?>