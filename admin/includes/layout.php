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
      <style>
         /* Scrollbar elegante, sutil y minimalista para el Sidebar azul */
         .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(147, 197, 253, 0.3) transparent;
         }
         .sidebar-scroll::-webkit-scrollbar {
            width: 5px;
         }
         .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
         }
         .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(147, 197, 253, 0.25);
            border-radius: 9999px;
            transition: background-color 0.2s ease;
         }
         .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(147, 197, 253, 0.6);
         }

         /* Scrollbar sutil para el área de contenido principal */
         .main-scroll {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
         }
         .main-scroll::-webkit-scrollbar {
            width: 7px;
            height: 7px;
         }
         .main-scroll::-webkit-scrollbar-track {
            background: #f8fafc;
         }
         .main-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
         }
         .main-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
         }

         /* Reglas explícitas e infalibles para el Drawer del Sidebar Móvil */
         @media (max-width: 767px) {
            #admin-sidebar {
               position: fixed !important;
               top: 0 !important;
               bottom: 0 !important;
               left: 0 !important;
               width: 16rem !important; /* 256px */
               height: 100vh !important;
               z-index: 50 !important;
               transform: translateX(-100%) !important;
               transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1) !important;
               box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6) !important;
            }
            #admin-sidebar.sidebar-open {
               transform: translateX(0) !important;
            }
            #sidebar-backdrop {
               position: fixed !important;
               inset: 0 !important;
               background: rgba(15, 23, 42, 0.65) !important;
               backdrop-filter: blur(2px) !important;
               z-index: 40 !important;
               opacity: 0;
               pointer-events: none;
               transition: opacity 0.28s ease !important;
               display: block !important;
            }
            #sidebar-backdrop.active {
               opacity: 1 !important;
               pointer-events: auto !important;
            }
         }
         @media (min-width: 768px) {
            #admin-sidebar {
               position: static !important;
               transform: none !important;
               display: flex !important;
            }
            #sidebar-backdrop {
               display: none !important;
            }
         }
      </style>

      <script>
         // Función global infalible para abrir y cerrar el menú móvil
         function toggleSidebar(forceState = null) {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (!sidebar) return;

            const isOpen = sidebar.classList.contains('sidebar-open');
            const shouldOpen = forceState !== null ? forceState : !isOpen;

            if (shouldOpen) {
               sidebar.classList.add('sidebar-open');
               if (backdrop) backdrop.classList.add('active');
            } else {
               sidebar.classList.remove('sidebar-open');
               if (backdrop) backdrop.classList.remove('active');
            }
         }

         // Cerrar con tecla Escape o al hacer clic en un enlace en móviles
         document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && window.innerWidth < 768) {
               toggleSidebar(false);
            }
         });

         document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('#admin-sidebar nav a');
            links.forEach(function(link) {
               link.addEventListener('click', function() {
                  if (window.innerWidth < 768) {
                     toggleSidebar(false);
                  }
               });
            });
         });
      </script>
   </head>

   <body class="bg-gray-100 font-sans antialiased flex h-screen overflow-hidden">

      <!-- Mobile Backdrop Overlay -->
      <div 
         id="sidebar-backdrop"
         onclick="toggleSidebar(false)">
      </div>

      <!-- Sidebar -->
      <aside 
         id="admin-sidebar"
         class="w-64 bg-blue-900 text-white shrink-0 flex flex-col">
         
         <div class="h-16 flex items-center justify-between md:justify-center px-4 border-b border-blue-800">
            <span class="text-xl font-bold tracking-wider">SART ADMIN</span>
            <!-- Botón Cerrar en Móvil -->
            <button onclick="toggleSidebar(false)" type="button" aria-label="Cerrar Menú" class="md:hidden text-blue-200 hover:text-white p-1.5 rounded-lg hover:bg-blue-800 transition focus:outline-none">
               <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
         </div>

         <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto sidebar-scroll">
            <a href="<?php echo url('admin/dashboard.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'dashboard.php') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">📊</span> Dashboard
            </a>

            <div class="px-4 pt-4 pb-1 text-xs text-gray-400 uppercase font-bold tracking-wider">Gestión Catastral</div>

            <a href="<?php echo url('admin/catastro/contribuyentes.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'contribuyentes.php') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">👥</span> Contribuyentes
            </a>
            <a href="<?php echo url('admin/catastro/predios.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'predios.php') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">🏠</span> Predios
            </a>

            <div class="px-4 pt-4 pb-1 text-xs text-gray-400 uppercase font-bold tracking-wider">Tributos y Cobranza</div>

            <a href="<?php echo url('admin/emision/index.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'emision') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">⚙️</span> Emisión Masiva
            </a>
            <a href="<?php echo url('admin/caja/index.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo (strpos($_SERVER['PHP_SELF'], 'caja/index.php') || (strpos($_SERVER['PHP_SELF'], 'caja/') && !strpos($_SERVER['PHP_SELF'], 'conciliacion.php'))) ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">💰</span> Caja / POS
            </a>
            <a href="<?php echo url('admin/caja/conciliacion.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'conciliacion.php') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">📑</span> Conciliación Caja
            </a>

            <div class="px-4 pt-4 pb-1 text-xs text-gray-400 uppercase font-bold tracking-wider">Seguimiento y Control</div>

            <a href="<?php echo url('admin/reportes/morosidad.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'morosidad.php') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">⚠️</span> Morosidad y Cartera
            </a>
            <a href="<?php echo url('admin/reportes/ingresos.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'ingresos.php') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">📈</span> Ingresos
            </a>
            <a href="<?php echo url('admin/auditoria/logs.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'auditoria/logs.php') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">🛡️</span> Bitácora de Logs
            </a>

            <div class="px-4 pt-4 pb-1 text-xs text-gray-400 uppercase font-bold tracking-wider">Configuración y Tesis</div>

            <a href="<?php echo url('admin/config/anios.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'anios.php') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">📅</span> Años Fiscales
            </a>
            <a href="<?php echo url('admin/config/tasas.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'tasas.php') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">💲</span> Tasas Arbitrios
            </a>
            <a href="<?php echo url('admin/reportes/evaluacion_tesis.php'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'evaluacion_tesis.php') ? 'bg-blue-800' : ''; ?>">
               <span class="mr-3">🎓</span> Evaluación de Tesis
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
         <header class="flex justify-between items-center p-4 bg-white border-b border-slate-200 shadow-sm md:hidden z-30">
            <span class="text-xl font-bold text-blue-900 tracking-wide">SART ADMIN</span>
            <button 
               onclick="toggleSidebar()" 
               type="button" 
               aria-label="Abrir Menú"
               class="text-slate-700 hover:text-blue-900 hover:bg-slate-100 active:bg-slate-200 p-2 rounded-xl border border-slate-200 focus:outline-none transition flex items-center justify-center cursor-pointer">
               <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
               </svg>
            </button>
         </header>

         <!-- Main Content -->
         <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6 main-scroll">
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