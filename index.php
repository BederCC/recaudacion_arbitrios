<!DOCTYPE html>
<html lang="es">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Portal del Vecino - Municipalidad Distrital de Oropesa</title>
   <link href="./src/output.css" rel="stylesheet">
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen p-4 font-sans text-slate-800">

   <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-slate-200">
      
      <!-- Logo & Title -->
      <div class="text-center mb-6">
         <div class="w-20 h-20 mx-auto mb-3 flex items-center justify-center p-1 bg-white rounded-2xl shadow-sm border border-slate-100">
            <img src="src/img/logovd.png" alt="Escudo de Oropesa" class="h-full w-auto object-contain">
         </div>
         <h2 class="text-xs font-bold text-blue-800 uppercase tracking-wider">Municipalidad Distrital de Oropesa</h2>
         <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-0.5">Portal del Vecino SART</h1>
         <p class="text-xs text-slate-500 mt-1">Consulta y paga tus arbitrios municipales e impuesto predial en línea con total seguridad.</p>
      </div>

      <!-- Trust Badges & Real Payment Logos -->
      <div class="mb-6 bg-slate-50 p-3 rounded-xl border border-slate-200/80">
         <div class="flex items-center justify-center gap-1.5 text-xs font-bold text-emerald-700 mb-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            <span>Canal Oficial de Pagos en Línea 24/7</span>
         </div>
         <div class="flex items-center justify-center flex-wrap gap-2.5 pt-1.5 border-t border-slate-200/60">
            <img src="src/img/visa.svg" alt="Visa" class="h-6 w-auto shadow-sm rounded">
            <img src="src/img/mastercard.svg" alt="Mastercard" class="h-6 w-auto shadow-sm rounded">
            <img src="src/img/yape.svg" alt="Yape" class="h-6 w-auto shadow-sm rounded">
            <img src="src/img/plin.svg" alt="Plin" class="h-6 w-auto shadow-sm rounded">
            <img src="src/img/pagoefectivo.svg" alt="PagoEfectivo" class="h-6 w-auto shadow-sm rounded">
            <img src="src/img/niubiz.svg" alt="Niubiz" class="h-6 w-auto shadow-sm rounded">
         </div>
      </div>

      <form action="public/neighbor.php" method="GET" class="space-y-4">
         <div>
            <label for="dni" class="block text-xs font-bold text-slate-700 mb-1">DNI o RUC del Contribuyente</label>
            <input type="text" id="dni" name="dni" required
               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm font-semibold text-slate-900"
               placeholder="Ingrese su número de documento">
         </div>

         <div>
            <label for="clave" class="block text-xs font-bold text-slate-700 mb-1">Clave Web / Contraseña</label>
            <input type="password" id="clave" name="clave"
               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm font-semibold text-slate-900"
               placeholder="Ingrese su clave">
            <p class="text-[11px] text-slate-400 mt-1">* Para la sustentación de tesis, puedes ingresar cualquier clave o tu DNI.</p>
         </div>

         <button type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition transform hover:-translate-y-0.5 active:translate-y-0 text-sm flex items-center justify-center gap-2">
            <span>CONSULTAR Y PAGAR EN LÍNEA</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
         </button>
      </form>

      <!-- Admin Access Link -->
      <div class="mt-6 pt-4 border-t border-slate-100 flex justify-between items-center text-xs text-slate-500">
         <a href="admin/login.php" class="text-blue-600 hover:text-blue-800 font-semibold hover:underline">
            Acceso Funcionarios (Admin)
         </a>
         <!-- <a href="simular_tesis_oropesa.php" class="text-indigo-600 hover:text-indigo-800 font-semibold hover:underline">
            Simulador de Tesis
         </a> -->
      </div>

   </div>

</body>

</html>
