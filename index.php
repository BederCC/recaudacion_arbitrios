<!DOCTYPE html>
<html lang="es">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>SART - Portal del Vecino</title>
   <link href="./src/output.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">

   <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
      <div class="text-center mb-8">
         <h1 class="text-3xl font-bold text-blue-900">Portal del Vecino</h1>
         <p class="text-gray-500 mt-2">Consulta y paga tus tributos fácil y rápido</p>
      </div>

      <form action="public/neighbor.php" method="GET">
         <!-- In a real app, this would be POST to an auth handler, but for this demo we pass ID directly or simulate login -->
         <!-- We will simulate login by asking for DNI and finding the ID via JS or just passing DNI to neighbor.php -->

         <div class="mb-6">
            <label for="dni" class="block text-gray-700 font-bold mb-2">DNI / RUC</label>
            <input type="text" id="dni" name="dni" required
               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="Ingrese su número de documento">
         </div>

         <div class="mb-6">
            <label for="clave" class="block text-gray-700 font-bold mb-2">Clave Web</label>
            <input type="password" id="clave" name="clave"
               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="Ingrese su clave">
            <p class="text-xs text-gray-400 mt-1">* Para esta demo, cualquier clave es válida.</p>
         </div>

         <button type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300">
            INGRESAR
         </button>
      </form>

      <div class="mt-6 text-center">
         <a href="#" class="text-sm text-blue-600 hover:underline">¿Olvidaste tu clave?</a>
      </div>
   </div>

</body>

</html>