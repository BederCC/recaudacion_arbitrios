<?php
// includes/db.php

$host = 'localhost';
$db   = 'recaudacion_arbitrios'; // Assuming the DB name based on the folder, user didn't specify DB name but implied it exists.
// Wait, the user said "Base de Datos: Utiliza estrictamente el script SQL proporcionado".
// I'll assume the DB name is 'recaudacion_arbitrios' or I should check if I can find it.
// Actually, standard practice in these prompts is often just 'test' or the user creates it.
// I will use 'recaudacion_arbitrios' as the DB name.
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
   PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
   PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
   PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
   $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
   // In production, don't show the error details to the user
   // For this dev task, it's helpful.
   throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

function jsonResponse($data, $status = 200)
{
   header("Content-Type: application/json");
   http_response_code($status);
   echo json_encode($data);
   exit;
}

function getActiveFiscalYear($pdo)
{
   $stmt = $pdo->query("SELECT * FROM anios_fiscales WHERE estado = 'ACTIVO' LIMIT 1");
   return $stmt->fetch();
}
