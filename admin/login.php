<?php
// admin/login.php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $user = $_POST['usuario'];
   $pass = $_POST['password'];

   $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? AND estado = 'ACTIVO'");
   $stmt->execute([$user]);
   $usuario = $stmt->fetch();

   if ($usuario && password_verify($pass, $usuario['password'])) {
      $_SESSION['admin_id'] = $usuario['id'];
      $_SESSION['admin_nombre'] = $usuario['nombre'];
      $_SESSION['admin_rol'] = $usuario['rol'];
      header('Location: ' . url('admin/dashboard.php'));
      exit;
   } else {
      $error = "Credenciales incorrectas";
   }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login Admin - SART</title>
   <link href="<?php echo asset('src/output.css'); ?>" rel="stylesheet">
</head>

<body class="bg-gray-900 flex items-center justify-center h-screen">

   <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-sm">
      <div class="text-center mb-8">
         <h1 class="text-2xl font-bold text-gray-800">Acceso Administrativo</h1>
         <p class="text-gray-500 text-sm mt-1">Sistema de Recaudación Tributaria</p>
      </div>

      <?php if (isset($error)): ?>
         <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo $error; ?></span>
         </div>
      <?php endif; ?>

      <form method="POST">
         <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Usuario</label>
            <input type="text" name="usuario" required
               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
               placeholder="admin">
         </div>

         <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">Contraseña</label>
            <input type="password" name="password" required
               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
               placeholder="••••••••">
         </div>

         <button type="submit"
            class="w-full bg-blue-900 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            INGRESAR
         </button>
      </form>

      <div class="mt-4 text-center">
         <a href="<?php echo url('index.php'); ?>" class="text-xs text-gray-500 hover:underline">← Volver al Portal Vecino</a>
      </div>
   </div>

</body>

</html>