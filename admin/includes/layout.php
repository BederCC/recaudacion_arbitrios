<?php
// admin/includes/layout.php
session_start();
require_once __DIR__ . '/../../includes/config.php';

// Auth Check
if (!isset($_SESSION['admin_id'])) {
   header('Location: ' . url('admin/login.php'));
   exit;
}

function renderHeader($title)
{
?>
   <!DOCTYPE html>
   <html lang="es">

   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?php echo $title; ?> - Admin SART</title>
      <link href="<?php echo asset('src/output.css'); ?>" rel="stylesheet">
      <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   </head>

   <body class="bg-gray-100 font-sans antialiased flex h-screen overflow-hidden">

      <!-- Sidebar -->
      <aside class="w-64 bg-blue-900 text-white shrink-0 hidden md:flex flex-col">
         <div class="h-16 flex items-center justify-center border-b border-blue-800">
            <span class="text-xl font-bold tracking-wider">🏛️ SART ADMIN</span>
         </div>

         <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto">
            <a href="<?php echo url('admin/dashboard.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'dashboard.php') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">📊</span> Dashboard
            </a>

            <div class="px-4 pt-4 pb-1 text-xs text-gray-400 uppercase">Gestión</div>

            <a href="<?php echo url('admin/catastro/contribuyentes.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'contribuyentes.php') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">👥</span> Contribuyentes
            </a>
            <a href="<?php echo url('admin/catastro/predios.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'predios.php') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">🏠</span> Predios
            </a>

            <div class="px-4 pt-4 pb-1 text-xs text-gray-400 uppercase">Procesos</div>

            <a href="<?php echo url('admin/emision/index.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'emision') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">⚙️</span> Emisión Masiva
            </a>
            <a href="<?php echo url('admin/caja/index.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'caja') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">💰</span> Caja / POS
            </a>

            <div class="px-4 pt-4 pb-1 text-xs text-gray-400 uppercase">Configuración</div>

            <a href="<?php echo url('admin/config/anios.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'anios.php') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">📅</span> Años Fiscales
            </a>
            <a href="<?php echo url('admin/config/tasas.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'tasas.php') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">💲</span> Tasas Arbitrios
            </a>

            <div class="px-4 pt-4 pb-1 text-xs text-gray-400 uppercase">Reportes</div>

            <a href="<?php echo url('admin/reportes/ingresos.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'ingresos.php') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">📈</span> Ingresos
            </a>
            <a href="<?php echo url('admin/requerimientos_datos.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'requerimientos_datos.php') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">📋</span> Requerimientos
            </a>
         </nav>

         <div class="p-4 border-t border-blue-800">
            <div class="flex items-center">
               <div class="ml-3">
                  <p class="text-sm font-medium"><?php echo $_SESSION['admin_nombre']; ?></p>
                  <p class="text-xs text-gray-400"><?php echo $_SESSION['admin_rol']; ?></p>
               </div>
            </div>
            <a href="<?php echo url('admin/logout.php'); ?>" class="mt-4 block text-center text-sm text-red-300 hover:text-white">Cerrar Sesión</a>
         </div>
      </aside>

      <!-- Main Content Wrapper -->
      <div class="flex-1 flex flex-col overflow-hidden">
         <!-- Mobile Header -->
         <header class="flex justify-between items-center p-4 bg-white shadow md:hidden">
            <span class="text-xl font-bold text-blue-900">SART ADMIN</span>
            <button class="text-gray-600 focus:outline-none">☰</button>
         </header>

         <!-- Main Content -->
         <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
         <?php
      }

      function renderFooter()
      {
         ?>
         </main>
      </div>
   </body>

   </html>
<?php
      }
?>