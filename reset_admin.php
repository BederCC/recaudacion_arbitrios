<?php
// reset_admin.php
require_once 'includes/db.php';

$user = 'admin';
$new_pass = 'admin123';
$hash = password_hash($new_pass, PASSWORD_DEFAULT);

try {
   // Check if user exists
   $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
   $stmt->execute([$user]);

   if ($stmt->fetch()) {
      // Update
      $stmtUpdate = $pdo->prepare("UPDATE usuarios SET password = ? WHERE usuario = ?");
      $stmtUpdate->execute([$hash, $user]);
      echo "Contraseña actualizada correctamente para el usuario '$user'.\n";
      echo "Nueva contraseña: $new_pass";
   } else {
      // Insert if not exists (just in case)
      $stmtInsert = $pdo->prepare("INSERT INTO usuarios (nombre, usuario, password, rol) VALUES ('Administrador', ?, ?, 'ADMIN')");
      $stmtInsert->execute([$user, $hash]);
      echo "Usuario '$user' creado con contraseña: $new_pass";
   }
} catch (PDOException $e) {
   echo "Error: " . $e->getMessage();
}
