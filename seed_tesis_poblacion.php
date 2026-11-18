<?php
/**
 * ==============================================================================
 * SART - SISTEMA DE ADMINISTRACIÓN DE RECAUDACIÓN TRIBUTARIA
 * SCRIPT SEEDER DE SIMULACIÓN MASIVA PARA VALIDACIÓN DE TESIS
 * ==============================================================================
 * Población Objetivo: N = 1,860 Contribuyentes | ~2,000 Predios
 * Motor de Emisión: 4 cuotas de Predial + 12 Limpieza + 12 Seguridad = 28 reg/predio/año
 * Volumen Total de Deudas Estimado: 2,000 * 28 = 56,000 registros
 * 
 * Uso: Ejecutar desde terminal: php seed_tesis_poblacion.php
 * ==============================================================================
 */

require_once __DIR__ . '/includes/db.php';

ini_set('memory_limit', '1024M');
set_time_limit(600);

echo "========================================================\n";
echo " SART: SEEDER MASIVO DE TESIS (N=1860 / Predios=2000)   \n";
echo "========================================================\n";

try {
    $pdo->beginTransaction();

    $anioActivo = 2026;
    $uitValor = 5500.00;

    // ---------------------------------------------------------
    // 1. AÑO FISCAL ACTIVO
    // ---------------------------------------------------------
    $stmtAnio = $pdo->prepare("
        INSERT INTO anios_fiscales (anio, valor_uit, interes_moratorio_diario, estado)
        VALUES (?, ?, 0.03300, 'ACTIVO')
        ON DUPLICATE KEY UPDATE valor_uit = VALUES(valor_uit), estado = 'ACTIVO'
    ");
    $stmtAnio->execute([$anioActivo, $uitValor]);
    echo "[✔] Año fiscal {$anioActivo} configurado como ACTIVO (UIT: S/ {$uitValor}).\n";

    // ---------------------------------------------------------
    // 2. INSERTAR 1,860 CONTRIBUYENTES
    // ---------------------------------------------------------
    echo "[...] Insertando 1,860 contribuyentes...\n";
    $nombres = ['Juan', 'Carlos', 'María', 'José', 'Ana', 'Luis', 'Jorge', 'Rosa', 'Pedro', 'Lucía', 'Manuel', 'Elena', 'Víctor', 'Carmen', 'David', 'Patricia'];
    $apellidos = ['Quispe', 'Flores', 'Rodríguez', 'García', 'Rojas', 'Huamán', 'Mendoza', 'Sánchez', 'Pérez', 'Torres', 'Ramírez', 'Vargas', 'Castro'];
    $rubros = ['INVERSIONES', 'CONSTRUCTORA', 'SERVICIOS GENERALES', 'COMERCIAL', 'DISTRIBUIDORA', 'CONSULTORA', 'TRANSPORTES', 'INMOBILIARIA'];

    $contribuyentesIds = [];
    $stmtContrib = $pdo->prepare("
        INSERT INTO contribuyentes 
        (tipo_persona, numero_documento, nombre_razon_social, direccion_fiscal, correo, telefono, clave_web) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $dniBase = 10000000;
    $rucBase = 20600000000;

    for ($i = 1; $i <= 1860; $i++) {
        $esJuridica = ($i % 7 === 0); // ~14.3% de personas jurídicas
        if ($esJuridica) {
            $tipoPersona = 'JURIDICA';
            $numDoc = (string)($rucBase + $i);
            $nombre = $rubros[array_rand($rubros)] . " " . $apellidos[array_rand($apellidos)] . " S.A.C.";
            $correo = "contacto" . $i . "@empresa.pe";
        } else {
            $tipoPersona = 'NATURAL';
            $numDoc = str_pad((string)($dniBase + $i), 8, '0', STR_PAD_LEFT);
            $nombre = $nombres[array_rand($nombres)] . " " . $apellidos[array_rand($apellidos)] . " " . $apellidos[array_rand($apellidos)];
            $correo = "contribuyente" . $i . "@gmail.com";
        }

        $direccion = "Jr. Progreso " . rand(100, 999) . ", Sector " . rand(1, 6);
        $telefono = "9" . rand(10000000, 99999999);
        $clave = password_hash($numDoc, PASSWORD_BCRYPT);

        $stmtContrib->execute([$tipoPersona, $numDoc, $nombre, $direccion, $correo, $telefono, $clave]);
        $contribuyentesIds[] = $pdo->lastInsertId();
    }
    echo "[✔] 1,860 contribuyentes registrados con éxito.\n";

    // ---------------------------------------------------------
    // 3. INSERTAR 2,000 PREDIOS
    // ---------------------------------------------------------
    echo "[...] Insertando 2,000 predios catastrados...\n";
    $stmtPredio = $pdo->prepare("
        INSERT INTO predios 
        (contribuyente_id, anio_declaracion, codigo_catastral, direccion_predio, tipo_uso_id, area_construida, longitud_frente, valor_autovaluo, estado) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVO')
    ");

    $totalPredios = 2000;
    $prediosCreados = [];

    for ($i = 0; $i < $totalPredios; $i++) {
        // Los primeros 1860 predios corresponden 1 a cada contribuyente; los 140 restantes a contribuyentes aleatorios (multipropiedad)
        $contribId = ($i < 1860) ? $contribuyentesIds[$i] : $contribuyentesIds[rand(0, 1859)];
        
        $codigoCat = sprintf("%02d-%03d-%03d", rand(1, 12), rand(1, 80), rand(1, 99));
        $direccionPredio = "Av. Los Próceres Nro " . rand(100, 1999) . " Urb. Central";

        // Distribución realista de usos:
        // 70% Casa Habitación (1), 15% Comercio (2), 5% Industria (3), 5% Terreno sin construir (4), 5% Otros (5)
        $r = rand(1, 100);
        if ($r <= 70) $tipoUso = 1;
        elseif ($r <= 85) $tipoUso = 2;
        elseif ($r <= 90) $tipoUso = 3;
        elseif ($r <= 95) $tipoUso = 4;
        else $tipoUso = 5;

        if ($tipoUso == 4) { // Terreno
            $areaConstruida = 0.00;
            $longitudFrente = rand(8, 25) + (rand(0, 99) / 100);
            $autovaluo = rand(30000, 80000) + (rand(0, 99) / 100);
        } else {
            $areaConstruida = rand(50, 420) + (rand(0, 99) / 100);
            $longitudFrente = rand(6, 18) + (rand(0, 99) / 100);
            // Autovalúo proporcional al área y uso
            $autovaluo = ($areaConstruida * rand(400, 850)) + rand(25000, 75000);
        }

        $stmtPredio->execute([
            $contribId,
            $anioActivo,
            $codigoCat,
            $direccionPredio,
            $tipoUso,
            round($areaConstruida, 2),
            round($longitudFrente, 2),
            round($autovaluo, 2)
        ]);
        $prediosCreados[] = $pdo->lastInsertId();
    }
    echo "[✔] 2,000 predios registrados y vinculados.\n";

    $pdo->commit();
    echo "========================================================\n";
    echo " ¡SIMULACIÓN DE BASE COMPLETADA EXITOSAMENTE!          \n";
    echo "========================================================\n";
    echo "Contribuyentes creados: " . count($contribuyentesIds) . "\n";
    echo "Predios creados:        " . count($prediosCreados) . "\n";
    echo "\nPara emitir las 56,000 cuotas correspondientes al año {$anioActivo},\n";
    echo "inicia sesión en SART y haz clic en 'PROCESAR EMISIÓN ANUAL',\n";
    echo "o ejecuta el script backend 'api/emission/generate.php'.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "[✘] ERROR DURANTE EL SEEDER: " . $e->getMessage() . "\n";
}

