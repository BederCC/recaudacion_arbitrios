<?php
/**
 * ==============================================================================
 * SISTEMA DE ADMINISTRACIÓN DE RECAUDACIÓN TRIBUTARIA (SART)
 * PANEL DE SIMULACIÓN Y GENERACIÓN POR PASOS (OROPESA - CUSCO, 2026)
 * ==============================================================================
 * Título de Tesis:
 * "Implementación y evaluación de un sistema web para el control de recaudación 
 *  de arbitrios en la Municipalidad Distrital de Oropesa, 2026"
 * Autor: Bach. Jhon Puma Mamani | Asesora: Dra. Huaman Vargas Jaqueline
 * Institución: Universidad Tecnológica de los Andes (UTEA) - Cusco
 * ==============================================================================
 */

$isCli = (php_sapi_name() === 'cli');

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auditoria.php';

ini_set('memory_limit', '1024M');
set_time_limit(600);

$ANIO_SIMULACION = 2026;
$UIT_ANIO        = 5500.00;

// -----------------------------------------------------------------------------
// BANCO DE DATOS DE CUSCO Y OROPESA (AMPLIADO)
// -----------------------------------------------------------------------------
$apellidosCusco = [
    'Quispe', 'Huamán', 'Mamani', 'Condori', 'Ttito', 'Champi', 'Ccallo', 'Choque',
    'Yupanqui', 'Paucar', 'Puma', 'Flores', 'Mendoza', 'Sallo', 'Hancco', 'Ccama',
    'Huillca', 'Pfoccori', 'Kcomt', 'Chura', 'Chaccara', 'Qquellon', 'Cusihuamán',
    'Ccolqque', 'Mayhua', 'Chullo', 'Huarcaya', 'Auccapuri', 'Surco', 'Ccasani',
    'Vilca', 'Illa', 'Sayre', 'Huarhua', 'Checca', 'Loaiza', 'Cárdenas', 'Zúñiga',
    'Almirón', 'Bejar', 'Umeres', 'Farfán', 'Pezo', 'Yábar', 'Estrada', 'Valdivia',
    'Canal', 'Acurio', 'Gutiérrez', 'Navarro', 'Ramos', 'Bautista', 'Salas',
    'Ccahuana', 'Condorhuamán', 'Auccacusi', 'Huamanyauri', 'Qquenta', 'Yucra',
    'Ccoscco', 'Chinchay', 'Paccoricona', 'Pillco', 'Huallpayunca', 'Qquecho',
    'Tintaya', 'Saico', 'Huayhua', 'Cutipa', 'Checya', 'Huillcahuamán', 'Choquepuma',
    'Ticona', 'Huanca', 'Huamanñahui', 'Huamánquispe', 'Nina', 'Japura', 'Tacuri',
    'Quico', 'Coaquira', 'Chilca', 'Ccahuancama', 'Huallpa', 'Pariapaza', 'Ccala',
    'Llacma', 'Chara', 'Pumacahua', 'Auca', 'Huaccanqui', 'Silloca', 'Apaza',
    'Quispecahuana', 'Condorpuma', 'Ccompi', 'Huamantica', 'Pumayalli', 'Cconislla',
    'Ylla', 'Chaquillca', 'Alanya', 'Cumpa', 'Tupayachi', 'Halanocca', 'Phocco',
    'Suntura', 'Ccorimanya', 'Huisa', 'Ccoyllor', 'Ttupa', 'Mullisaca', 'Qquehuarucho',
    'Huayllani', 'Cconcho', 'Challco', 'Chirinos', 'Carrasco', 'Zamalloa', 'Barcena',
    'Montalvo', 'Ochoa', 'Vargas', 'Aguilar', 'Aragón', 'Castelo', 'Palomino',
    'Pacheco', 'Bellido', 'Calderón', 'Cabrera', 'Escalante', 'Galdos', 'Moscoso',
    'Pinto', 'Sarmiento', 'Tejada', 'Vivanco', 'Arriaga', 'Oblitas', 'Morales'
];

$nombresVarones = [
    'Wilber', 'Ronald', 'Edwin', 'Guido', 'Néstor', 'Braulio', 'Juvenal', 'Walter',
    'David', 'Julio', 'Washington', 'Efraín', 'Edgar', 'Rubén', 'Franklin', 'Hernán',
    'Mario', 'Percy', 'Juan Carlos', 'Marco Antonio', 'Lucio', 'Hipólito', 'Saturnino',
    'Justo', 'Apolinar', 'Zenón', 'Teófilo', 'Feliciano', 'Serapio', 'Dionicio',
    'Vicente', 'Modesto', 'Víctor Raúl', 'César', 'Héctor', 'Jorge Luis',
    'Ramiro', 'Flavio', 'Demetrio', 'Eusebio', 'Froilán', 'Gervasio', 'Honorio',
    'Inocencio', 'Leocadio', 'Macario', 'Nicanor', 'Orestes', 'Pascual', 'Quirino',
    'Sabino', 'Toribio', 'Urbano', 'Valerio', 'Wenceslao', 'Yuri', 'Zósimo',
    'Alejandro', 'Amílcar', 'Aníbal', 'Beltrán', 'Crisólogo', 'Dante', 'Emilio',
    'Fabio', 'Gonzalo', 'Heraclio', 'Isidro', 'Jaime', 'Leonidas', 'Marcelino',
    'Norberto', 'Oswaldo', 'Primitivo', 'René', 'Santos', 'Tiburcio', 'Wilfredo',
    'Yenner', 'Zacarías', 'Benjamín', 'Claudio', 'Guillermo', 'Alfonso', 'Bernardino'
];

$nombresMujeres = [
    'Marleni', 'Roxana', 'Yeny', 'Gladys', 'Vilma', 'Lourdes', 'Nancy', 'Sonia',
    'Hilda', 'Nely', 'Judith', 'Bertha', 'Maritza', 'Yanet', 'Gregoria', 'Santosa',
    'Benita', 'Justina', 'Fortunata', 'Teodora', 'Modesta', 'Dominga', 'Agripina',
    'Candelaria', 'Nicolasa', 'Asunciona', 'Martina', 'Saturnina', 'Paulina', 'Lucila',
    'Flora', 'Rosa Luz', 'Mirian', 'Elizabeth', 'Patricia', 'Delia',
    'Alejandrina', 'Beatriz', 'Casilda', 'Delfina', 'Esperanza', 'Felícitas',
    'Genoveva', 'Hermelinda', 'Inés', 'Jacinta', 'Leonor', 'Magdalena', 'Nieves',
    'Octavia', 'Primitiva', 'Quintina', 'Rufina', 'Sebastiana', 'Trinidad', 'Úrsula',
    'Valeriana', 'Zenobia', 'Aurelia', 'Balbina', 'Ceferina', 'Dionicia', 'Enriqueta',
    'Faustina', 'Gliceria', 'Honoria', 'Irene', 'Juliana', 'Leandra', 'Manuela',
    'Nazaria', 'Otilia', 'Prudencia', 'Ruperta', 'Silvestra', 'Tomasa', 'Vicenta',
    'Wanda', 'Yolanda', 'Zenaida', 'Severina', 'Basilia', 'Anacleta', 'Teófila'
];

$empresasOropesa = [
    'PANIFICADORA Y HORNOS EL CHUTA DE OROPESA S.A.C.',
    'PANIFICADORA TRADICIONAL VIRGEN DEL CARMEN S.R.L.',
    'MOLINOS Y CEREALES DEL VALLE SUR S.A.C.',
    'HORNO ARTESANAL TRADICIÓN OROPESANA E.I.R.L.',
    'RESTAURANTE CAMPESTRE Y EVENTOS HUASAO S.A.C.',
    'TURISMO GASTRONÓMICO Y RECREACIONAL TIPÓN S.R.L.',
    'FERRETERÍA Y MATERIALES DE CONSTRUCCIÓN EL PROGRESO S.A.C.',
    'AGROVETERINARIA Y FORRAJES QUISPICANCHI E.I.R.L.',
    'BOTICA Y SERVICIOS DE SALUD SEÑOR DE HUANCA S.A.C.',
    'DISTRIBUIDORA DE ABARROTES Y HARINAS DEL SUR S.A.C.',
    'EMPRESA DE TRANSPORTES Y SERVICIOS VALLE SUR S.A.',
    'INMOBILIARIA Y CONSTRUCTORA CHOQUEPATA S.A.C.',
    'PANADERÍA Y PASTELERÍA SAN JERÓNIMO DE OROPESA S.R.L.',
    'ASOCIACIÓN DE PRODUCTORES DE PAN DE OROPESA - APPA',
    'SERVICIOS AGRÍCOLAS Y RIEGO PINAGUA S.A.C.',
    'GRIFO Y MULTISERVICIOS QUISPICANCHI S.R.L.',
    'RECREO TURÍSTICO LA CHUTA DE ORO S.A.C.',
    'COMERCIALIZADORA DE TRIGO Y GRANOS ANDINOS S.A.C.',
    'HORNO COLONIAL DON TATER S.R.L.',
    'PANIFICADORA ARTESANAL SAN SALVADOR E.I.R.L.',
    'MOLINERA CENTRAL HUANCARANI S.A.C.',
    'RESTAURANTE TURÍSTICO LOS BALCONES DE TIPÓN S.A.C.',
    'ASOCIACIÓN DE ARTESANOS Y PANADEROS DE QUISPICANCHI',
    'FERRETERÍA Y ACEROS VALLE DEL SOL S.R.L.',
    'BODEGA Y DISTRIBUIDORA MAYORISTA DOÑA JUSTA S.A.C.',
    'CENTRO ECOLÓGICO Y PISCIGRANJA DE HUASAO E.I.R.L.',
    'EMPRESA DE TRANSPORTES EXPRESO SANTO DOMINGO S.A.',
    'SERVICIOS GENERALES Y MAQUINARIA ANDINA S.A.C.',
    'HOSPEDAJE Y CAMPING EL MIRADOR DE OROPESA E.I.R.L.',
    'COOPERATIVA AGRARIA DE PRODUCTORES DEL SUR',
    'FARMACIA Y BOTICA FAMILIAR SANTA ANA S.C.R.L.',
    'CARPINTERÍA Y EBANISTERÍA VIRGEN ASUNCIÓN E.I.R.L.',
    'INVERSIONES Y EVENTOS SOCIALES VILLA HERMOSA S.A.C.',
    'DISTRIBUIDORA DE BEBIDAS Y ABARROTES QUISPISUR S.R.L.',
    'PANIFICADORA SANTO TOMÁS DE OROPESA S.A.C.',
    'RECREO GASTRONÓMICO EL CHICHARRÓN DE TIPÓN S.A.C.'
];

$callesOropesa = [
    'Jr. Comercio', 'Plaza de Armas de Oropesa', 'Av. Ferrocarril', 'Jr. Lima',
    'Jr. Cusco', 'Jr. San Martín', 'Jr. Bolognesi', 'Ca. Progreso', 'Ca. Santa Rosa',
    'Ca. San Juan', 'Jr. 28 de Julio', 'Ca. Arequipa', 'Prolongación Comercio',
    'Pasaje Los Hornos', 'Ca. Bolívar', 'Av. Manco Cápac', 'Jr. Sucre',
    'Pasaje Huayna Cápac', 'Ca. Puno', 'Av. Primavera', 'Ca. Tahuantinsuyo',
    'Pasaje Santa Ana', 'Jr. Independencia', 'Ca. Espinar', 'Ca. Atahualpa',
    'Pasaje Los Panificadores', 'Jr. Mariscal Cáceres', 'Ca. Garcilaso de la Vega',
    'Pasaje Los Eucaliptos', 'Av. Panamericana Sur Km 21', 'Camino Real a Tipón'
];

$sectoresOropesa = [
    'Sector Centro Urbano', 'Comunidad Campesina Huasao', 'Sector Tipón',
    'Anexo Choquepata', 'Sector Pinagua', 'Barrio Rinconada Alta',
    'Barrio Rinconada Baja', 'Urbanización Las Palmeras', 'Sector Ayaviri',
    'Barrio San José', 'Sector Saylla Límite', 'Comunidad Campesina Ccoricunca',
    'Sector San Salvador', 'Barrio Pucara', 'Zona Arqueológica Tipón',
    'Comunidad Campesina Patabamba', 'Sector Molinopata', 'Barrio Santa Bárbara',
    'Urbanización Los Molinos de Oropesa', 'Sector Muyurina'
];

// Generador de DNI realista
$dnisGenerados = [];
function generarDniRealista(&$dnisGenerados) {
    $prefijos = [23, 24, 25, 40, 41, 42, 43, 44, 45, 46, 47, 48, 70, 71, 72, 73, 74, 75, 76, 80];
    do {
        $prefijo = $prefijos[array_rand($prefijos)];
        $resto = '';
        $ultimoDigito = -1;
        $consecutivoCount = 0;

        for ($j = 0; $j < 6; $j++) {
            $digito = rand(0, 9);
            if ($digito === $ultimoDigito) {
                $consecutivoCount++;
                if ($consecutivoCount >= 2) {
                    $digito = ($digito + rand(1, 8)) % 10;
                    $consecutivoCount = 0;
                }
            } else {
                $consecutivoCount = 0;
            }
            $ultimoDigito = $digito;
            $resto .= $digito;
        }

        $dni = $prefijo . $resto;
    } while (isset($dnisGenerados[$dni]));

    $dnisGenerados[$dni] = true;
    return $dni;
}

$rucsGenerados = [];
function generarRucRealista(&$rucsGenerados) {
    do {
        $num = '20' . rand(50000000, 69999999);
        $ruc = $num . rand(1, 9);
    } while (isset($rucsGenerados[$ruc]));
    $rucsGenerados[$ruc] = true;
    return $ruc;
}

// -----------------------------------------------------------------------------
// CONTROLADOR DE ACCIONES AJAX POR PASOS (EVITA BLOQUEOS Y TIMEOUTS)
// -----------------------------------------------------------------------------
if (isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['ajax_action'];

    try {
        switch ($action) {
            case 'limpiar':
                // IMPORTANTE: TRUNCATE es DDL y comitea automáticamente en MySQL.
                // NO se debe envolver en beginTransaction()/commit() para evitar "There is no active transaction".
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
                $pdo->exec("TRUNCATE TABLE `recibos_detalle`;");
                $pdo->exec("TRUNCATE TABLE `recibos`;");
                $pdo->exec("TRUNCATE TABLE `cta_cte_tributaria`;");
                $pdo->exec("TRUNCATE TABLE `predios`;");
                $pdo->exec("TRUNCATE TABLE `contribuyentes`;");
                $pdo->exec("TRUNCATE TABLE `caja_sesiones`;");
                asegurarTablaAuditoria($pdo);
                $pdo->exec("TRUNCATE TABLE `auditoria_logs`;");
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

                echo json_encode([
                    'success' => true,
                    'message' => 'Base de datos purgada con éxito. Tablas transaccionales limpiadas. Usuarios administradores y configuraciones fiscales intactos.'
                ]);
                exit;

            case 'paso_config':
                // Paso 1: Configuración de Año Fiscal y Tasas
                $stmtAnio = $pdo->prepare("
                    INSERT INTO anios_fiscales (anio, valor_uit, interes_moratorio_diario, estado)
                    VALUES (?, ?, 0.03300, 'ACTIVO')
                    ON DUPLICATE KEY UPDATE valor_uit = VALUES(valor_uit), estado = 'ACTIVO'
                ");
                $stmtAnio->execute([$ANIO_SIMULACION, $UIT_ANIO]);

                $tasas = [
                    1 => ['m2' => 0.1500, 'fijo_l' => 12.50, 'fijo_s' => 18.00],
                    2 => ['m2' => 0.3500, 'fijo_l' => 35.00, 'fijo_s' => 45.00],
                    3 => ['m2' => 0.5000, 'fijo_l' => 80.00, 'fijo_s' => 120.00],
                    4 => ['m2' => 0.0500, 'fijo_l' => 5.00,  'fijo_s' => 10.00],
                    5 => ['m2' => 0.2000, 'fijo_l' => 20.00, 'fijo_s' => 30.00],
                ];

                $stmtTasa = $pdo->prepare("
                    INSERT INTO tasas_arbitrios_anuales (anio, tipo_uso_id, costo_limpieza_m2, costo_limpieza_fijo, costo_seguridad_fijo)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        costo_limpieza_m2 = VALUES(costo_limpieza_m2),
                        costo_limpieza_fijo = VALUES(costo_limpieza_fijo),
                        costo_seguridad_fijo = VALUES(costo_seguridad_fijo)
                ");
                foreach ($tasas as $tipoId => $t) {
                    $stmtTasa->execute([$ANIO_SIMULACION, $tipoId, $t['m2'], $t['fijo_l'], $t['fijo_s']]);
                }

                // Asegurar caja de ventanilla
                $stmtCaja = $pdo->prepare("SELECT id FROM caja_sesiones WHERE usuario_cajero = 'Administrador Principal' AND estado = 'ABIERTA' LIMIT 1");
                $stmtCaja->execute();
                if (!$stmtCaja->fetchColumn()) {
                    $pdo->exec("INSERT INTO caja_sesiones (usuario_cajero, fecha_apertura, monto_recaudado, estado) VALUES ('Administrador Principal', NOW(), 0.00, 'ABIERTA')");
                }

                echo json_encode([
                    'success' => true,
                    'message' => "Año fiscal {$ANIO_SIMULACION} activado (UIT: S/ {$UIT_ANIO}) y tasas de arbitrios aprobadas."
                ]);
                exit;

            case 'paso_contribuyentes':
                // Paso 2: Crear Contribuyentes (Ultra-rápido: bcrypt precomputado)
                $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 430;
                if ($cantidad < 50) $cantidad = 430;

                // Precomputar hash una sola vez para evitar cuello de botella de 35 segundos
                $hashComun = password_hash('oropesa2026', PASSWORD_BCRYPT);

                $pdo->beginTransaction();
                $stmt = $pdo->prepare("
                    INSERT INTO contribuyentes 
                    (tipo_persona, numero_documento, nombre_razon_social, direccion_fiscal, correo, telefono, clave_web)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $dominiosEmail = ['@gmail.com', '@outlook.com', '@hotmail.com', '@yahoo.es'];
                $prefijosCel = ['984', '974', '958', '982', '993', '997', '989'];

                for ($i = 1; $i <= $cantidad; $i++) {
                    $esJuridica = ($i <= count($empresasOropesa) && ($i % 5 === 0));

                    if ($esJuridica) {
                        $tipoPersona = 'JURIDICA';
                        $numDoc = generarRucRealista($rucsGenerados);
                        $nombre = $empresasOropesa[($i - 1) % count($empresasOropesa)];
                        $calle = $callesOropesa[array_rand($callesOropesa)];
                        $direccion = "{$calle} N° " . rand(100, 500) . ", Oropesa";
                        $correo = "administracion@" . strtolower(preg_replace('/[^a-zA-Z]/', '', substr($nombre, 0, 15))) . ".pe";
                        $telefono = "084-" . rand(271000, 279999);
                    } else {
                        $tipoPersona = 'NATURAL';
                        $numDoc = generarDniRealista($dnisGenerados);

                        $esMujer = (rand(1, 100) <= 50);
                        $nombrePila = $esMujer ? $nombresMujeres[array_rand($nombresMujeres)] : $nombresVarones[array_rand($nombresVarones)];
                        $apPaterno = $apellidosCusco[array_rand($apellidosCusco)];
                        $apMaterno = $apellidosCusco[array_rand($apellidosCusco)];
                        $nombre = "{$nombrePila} {$apPaterno} {$apMaterno}";

                        $calle = $callesOropesa[array_rand($callesOropesa)];
                        $sector = $sectoresOropesa[array_rand($sectoresOropesa)];
                        $direccion = "{$calle} N° " . rand(50, 650) . " ({$sector})";

                        $dominio = $dominiosEmail[array_rand($dominiosEmail)];
                        $apL = strtolower(preg_replace('/[^a-zA-Z]/', '', $apPaterno));
                        $nomL = strtolower(preg_replace('/[^a-zA-Z]/', '', substr($nombrePila, 0, 1)));
                        $correo = "{$nomL}{$apL}" . rand(10, 99) . $dominio;

                        $telefono = $prefijosCel[array_rand($prefijosCel)] . rand(100000, 999999);
                    }

                    $stmt->execute([$tipoPersona, $numDoc, $nombre, $direccion, $correo, $telefono, $hashComun]);
                }
                $pdo->commit();

                echo json_encode([
                    'success' => true,
                    'count' => $cantidad,
                    'message' => "Se registraron {$cantidad} contribuyentes con identidad cusqueña y DNI realistas."
                ]);
                exit;

            case 'paso_predios':
                // Paso 3: Crear Predios Catastrados
                $cantidadPredios = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 465;
                if ($cantidadPredios < 50) $cantidadPredios = 465;

                $contribs = $pdo->query("SELECT id, tipo_persona FROM contribuyentes ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
                if (empty($contribs)) {
                    throw new Exception("No hay contribuyentes para asociar predios.");
                }

                $totalContribs = count($contribs);
                $pdo->beginTransaction();

                $stmtPredio = $pdo->prepare("
                    INSERT INTO predios 
                    (contribuyente_id, anio_declaracion, codigo_catastral, direccion_predio, tipo_uso_id, area_construida, longitud_frente, valor_autovaluo, estado)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVO')
                ");

                for ($p = 0; $p < $cantidadPredios; $p++) {
                    $contrib = ($p < $totalContribs) ? $contribs[$p] : $contribs[rand(0, $totalContribs - 1)];

                    $codCat = sprintf("%02d-%03d-%03dN", rand(1, 6), rand(1, 45), rand(1, 60));
                    $calle = $callesOropesa[array_rand($callesOropesa)];
                    $sector = $sectoresOropesa[array_rand($sectoresOropesa)];
                    $dirPredio = "{$calle} N° " . rand(80, 850) . ", {$sector}";

                    if ($contrib['tipo_persona'] === 'JURIDICA') {
                        $tipoUso = (rand(1, 100) <= 80) ? 2 : 3;
                    } else {
                        $uRand = rand(1, 100);
                        if ($uRand <= 65) $tipoUso = 1;      // Casa
                        elseif ($uRand <= 88) $tipoUso = 2;  // Horno / Comercio
                        elseif ($uRand <= 93) $tipoUso = 4;  // Terreno
                        elseif ($uRand <= 97) $tipoUso = 5;  // Otros
                        else $tipoUso = 3;                  // Taller
                    }

                    if ($tipoUso == 4) {
                        $area = 0.00;
                        $frente = rand(8, 25) + (rand(0, 90) / 100);
                        $autovaluo = rand(28000, 75000);
                    } elseif ($tipoUso == 2) {
                        $area = rand(120, 480);
                        $frente = rand(8, 18);
                        $autovaluo = ($area * rand(480, 850)) + rand(35000, 95000);
                    } elseif ($tipoUso == 3) {
                        $area = rand(300, 750);
                        $frente = rand(15, 30);
                        $autovaluo = ($area * rand(550, 950)) + rand(60000, 150000);
                    } else {
                        $area = rand(65, 260);
                        $frente = rand(6, 14);
                        $autovaluo = ($area * rand(380, 680)) + rand(20000, 50000);
                    }

                    $stmtPredio->execute([
                        $contrib['id'],
                        $ANIO_SIMULACION,
                        $codCat,
                        $dirPredio,
                        $tipoUso,
                        round($area, 2),
                        round($frente, 2),
                        round($autovaluo, 2)
                    ]);
                }
                $pdo->commit();

                echo json_encode([
                    'success' => true,
                    'count' => $cantidadPredios,
                    'message' => "Se catastraron y vincularon {$cantidadPredios} predios en Oropesa."
                ]);
                exit;

            case 'paso_emision':
                // Paso 4: Emisión de Deudas (28 cuotas por predio)
                $predios = $pdo->query("SELECT * FROM predios WHERE anio_declaracion = {$ANIO_SIMULACION}")->fetchAll(PDO::FETCH_ASSOC);
                if (empty($predios)) {
                    throw new Exception("No hay predios registrados para emitir deudas.");
                }

                $tasas = [
                    1 => ['m2' => 0.1500, 'fijo_l' => 12.50, 'fijo_s' => 18.00],
                    2 => ['m2' => 0.3500, 'fijo_l' => 35.00, 'fijo_s' => 45.00],
                    3 => ['m2' => 0.5000, 'fijo_l' => 80.00, 'fijo_s' => 120.00],
                    4 => ['m2' => 0.0500, 'fijo_l' => 5.00,  'fijo_s' => 10.00],
                    5 => ['m2' => 0.2000, 'fijo_l' => 20.00, 'fijo_s' => 30.00],
                ];

                $vencimientosPredial = [
                    1 => "$ANIO_SIMULACION-02-28",
                    2 => "$ANIO_SIMULACION-05-31",
                    3 => "$ANIO_SIMULACION-08-31",
                    4 => "$ANIO_SIMULACION-11-30"
                ];

                $pdo->beginTransaction();
                $stmtInsert = $pdo->prepare("
                    INSERT INTO cta_cte_tributaria 
                    (anio, contribuyente_id, predio_id, tributo_concepto, periodo, fecha_vencimiento, monto_insoluto, monto_pagado, estado)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 0.00, 'PENDIENTE')
                ");

                $totalCuotas = 0;
                $tramo1 = 15 * $UIT_ANIO;
                $tramo2 = 60 * $UIT_ANIO;

                foreach ($predios as $p) {
                    $cId = $p['contribuyente_id'];
                    $pId = $p['id'];
                    $autovaluo = (float)$p['valor_autovaluo'];
                    $area = (float)$p['area_construida'];
                    $tipoUso = (int)$p['tipo_uso_id'];

                    // Predial
                    if ($autovaluo <= $tramo1) {
                        $anual = $autovaluo * 0.002;
                    } elseif ($autovaluo <= $tramo2) {
                        $anual = ($tramo1 * 0.002) + (($autovaluo - $tramo1) * 0.006);
                    } else {
                        $anual = ($tramo1 * 0.002) + (($tramo2 - $tramo1) * 0.006) + (($autovaluo - $tramo2) * 0.010);
                    }
                    $cuotaP = round($anual / 4, 2);

                    for ($t = 1; $t <= 4; $t++) {
                        $stmtInsert->execute([$ANIO_SIMULACION, $cId, $pId, 'IMPUESTO_PREDIAL', $t, $vencimientosPredial[$t], $cuotaP]);
                        $totalCuotas++;
                    }

                    // Arbitrios
                    $t = $tasas[$tipoUso];
                    $cuotaL = round((($area * $t['m2']) + $t['fijo_l']) / 12, 2);
                    $cuotaS = round($t['fijo_s'] / 12, 2);

                    for ($mes = 1; $mes <= 12; $mes++) {
                        $vencMes = date("Y-m-t", strtotime("$ANIO_SIMULACION-$mes-01"));
                        $stmtInsert->execute([$ANIO_SIMULACION, $cId, $pId, 'LIMPIEZA_PUBLICA', $mes, $vencMes, $cuotaL]);
                        $stmtInsert->execute([$ANIO_SIMULACION, $cId, $pId, 'SEGURIDAD_CIUDADANA', $mes, $vencMes, $cuotaS]);
                        $totalCuotas += 2;
                    }
                }
                $pdo->commit();

                echo json_encode([
                    'success' => true,
                    'count' => $totalCuotas,
                    'message' => "Emisión anual completada: se generaron {$totalCuotas} cuotas tributarias en cuenta corriente."
                ]);
                exit;

            case 'paso_pagos':
                // Paso 5: Simulación de Pagos y Recibos
                $stmtCaja = $pdo->prepare("SELECT id FROM caja_sesiones WHERE usuario_cajero = 'Administrador Principal' AND estado = 'ABIERTA' ORDER BY id DESC LIMIT 1");
                $stmtCaja->execute();
                $cajaId = $stmtCaja->fetchColumn() ?: 1;

                // Agrupar deudas por contribuyente
                $deudasStmt = $pdo->query("SELECT id, contribuyente_id, monto_insoluto FROM cta_cte_tributaria WHERE anio = {$ANIO_SIMULACION} AND estado = 'PENDIENTE' ORDER BY contribuyente_id, periodo ASC");
                $deudasPorContrib = [];
                while ($row = $deudasStmt->fetch(PDO::FETCH_ASSOC)) {
                    $deudasPorContrib[$row['contribuyente_id']][] = $row;
                }

                $pdo->beginTransaction();

                $stmtRecibo = $pdo->prepare("
                    INSERT INTO recibos 
                    (anio_fiscal, contribuyente_id, caja_sesion_id, numero_serie, fecha_pago, monto_total, origen_pago, metodo_pago, codigo_operacion_bancaria, estado)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'VALIDO')
                ");

                $stmtDetalle = $pdo->prepare("
                    INSERT INTO recibos_detalle 
                    (recibo_id, deuda_id, monto_capital, monto_mora, monto_gastos)
                    VALUES (?, ?, ?, 0.00, 0.00)
                ");

                $stmtUpdate = $pdo->prepare("UPDATE cta_cte_tributaria SET estado = 'PAGADO', monto_pagado = monto_insoluto WHERE id = ?");

                $reciboNum = 1;
                $totalRecaudado = 0;
                $totalHoy = 0;
                $recibosCount = 0;
                $pagosHoyCount = 0;

                $metodos = ['EFECTIVO', 'EFECTIVO', 'TARJETA', 'TRANSFERENCIA'];
                $origenes = ['VENTANILLA_MUNICIPAL', 'VENTANILLA_MUNICIPAL', 'WEB_ONLINE', 'BANCO_CONVENIO'];

                foreach ($deudasPorContrib as $cId => $deudas) {
                    $totalD = count($deudas);
                    // Ponderación de morosidad (35% puntuales, 35% regulares, 30% morosos)
                    $r = rand(1, 100);
                    if ($r <= 35) $paga = rand(75, 100) / 100;
                    elseif ($r <= 70) $paga = rand(30, 60) / 100;
                    else $paga = rand(0, 15) / 100;

                    $cantPagar = (int)round($totalD * $paga);
                    if ($cantPagar <= 0) continue;

                    $pagarDeudas = array_slice($deudas, 0, $cantPagar);
                    $lotes = array_chunk($pagarDeudas, rand(2, 5));

                    foreach ($lotes as $lote) {
                        $montoLote = 0;
                        foreach ($lote as $d) $montoLote += (float)$d['monto_insoluto'];

                        $serie = sprintf("001-%06d", $reciboNum++);
                        $metodo = $metodos[array_rand($metodos)];
                        $origen = $origenes[array_rand($origenes)];
                        $codOp = ($metodo !== 'EFECTIVO') ? 'OP-' . rand(100000, 999999) : null;

                        // Pagos de hoy (primeros 8 recibos) para activar KPI de Recaudación del Día
                        if ($pagosHoyCount < 8) {
                            $fecha = date('Y-m-d H:i:s', strtotime("now - " . rand(5, 180) . " minutes"));
                            $pagosHoyCount++;
                            $totalHoy += $montoLote;
                        } else {
                            $diasAtras = rand(1, 120);
                            $fecha = date('Y-m-d H:i:s', strtotime("-{$diasAtras} days - " . rand(1, 10) . " hours"));
                        }

                        $stmtRecibo->execute([$ANIO_SIMULACION, $cId, $cajaId, $serie, $fecha, $montoLote, $origen, $metodo, $codOp]);
                        $reciboId = $pdo->lastInsertId();
                        $totalRecaudado += $montoLote;
                        $recibosCount++;

                        foreach ($lote as $d) {
                            $stmtDetalle->execute([$reciboId, $d['id'], $d['monto_insoluto']]);
                            $stmtUpdate->execute([$d['id']]);
                        }
                    }
                }

                $pdo->exec("UPDATE caja_sesiones SET monto_recaudado = {$totalRecaudado} WHERE id = {$cajaId}");
                $pdo->commit();

                echo json_encode([
                    'success' => true,
                    'recibos' => $recibosCount,
                    'total_recaudado' => number_format($totalRecaudado, 2),
                    'recaudado_hoy' => number_format($totalHoy, 2),
                    'message' => "Se simularon {$recibosCount} recibos de pago. Total recaudado: S/ " . number_format($totalRecaudado, 2) . ". Recaudación del día activa."
                ]);
                exit;

            case 'paso_logs':
                // Paso 7: Simulación de Bitácora de Auditoría (Trazabilidad Tesis)
                asegurarTablaAuditoria($pdo);
                $totalLogs = generarLogsSimuladosTesis($pdo);
                echo json_encode([
                    'success' => true,
                    'count' => $totalLogs,
                    'message' => "Se registraron {$totalLogs} eventos cronológicos de auditoría y trazabilidad en la bitácora."
                ]);
                exit;

            case 'get_stats':
                // Estadísticas actuales
                $cContrib = $pdo->query("SELECT COUNT(*) FROM contribuyentes")->fetchColumn() ?: 0;
                $cPredios = $pdo->query("SELECT COUNT(*) FROM predios")->fetchColumn() ?: 0;
                $cDeudas  = $pdo->query("SELECT COUNT(*) FROM cta_cte_tributaria")->fetchColumn() ?: 0;
                $cRecibos = $pdo->query("SELECT COUNT(*) FROM recibos")->fetchColumn() ?: 0;
                $cUser    = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn() ?: 0;
                asegurarTablaAuditoria($pdo);
                $cLogs    = $pdo->query("SELECT COUNT(*) FROM auditoria_logs")->fetchColumn() ?: 0;

                echo json_encode([
                    'success' => true,
                    'stats' => [
                        'contribuyentes' => (int)$cContrib,
                        'predios' => (int)$cPredios,
                        'deudas' => (int)$cDeudas,
                        'recibos' => (int)$cRecibos,
                        'usuarios' => (int)$cUser,
                        'logs' => (int)$cLogs
                    ]
                ]);
                exit;

            default:
                throw new Exception("Acción no reconocida.");
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit;
    }
}

// Estadísticas para carga inicial
$countContrib = $pdo->query("SELECT COUNT(*) FROM contribuyentes")->fetchColumn() ?: 0;
$countPredios = $pdo->query("SELECT COUNT(*) FROM predios")->fetchColumn() ?: 0;
$countDeudas  = $pdo->query("SELECT COUNT(*) FROM cta_cte_tributaria")->fetchColumn() ?: 0;
$countRecibos = $pdo->query("SELECT COUNT(*) FROM recibos")->fetchColumn() ?: 0;
$countTotalUser = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn() ?: 0;
asegurarTablaAuditoria($pdo);
$countLogs = $pdo->query("SELECT COUNT(*) FROM auditoria_logs")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SART - Generador Progresivo de Tesis (Oropesa 2026)</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen p-4 md:p-8 font-sans">
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 rounded-2xl shadow-xl overflow-hidden text-white p-6 md:p-8 border border-blue-800">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 bg-blue-500/20 text-blue-300 text-xs px-3 py-1 rounded-full font-semibold border border-blue-400/30 mb-2">
                        <span>Universidad Tecnológica de los Andes</span> &bull; <span>Cusco</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                        Generador Progresivo de Datos SART
                    </h1>
                    <p class="text-blue-200 text-sm mt-1 max-w-2xl">
                        Tesis: <em>"Implementación y evaluación de un sistema web para el control de recaudación de arbitrios en la Municipalidad Distrital de Oropesa, 2026"</em>
                    </p>
                    <p class="text-xs text-blue-300/80 mt-1">Bach. Jhon Puma Mamani &bull; Asesora: Dra. Huaman Vargas Jaqueline</p>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-xl p-4 border border-white/10 text-center min-w-[130px]">
                    <span class="text-xs text-blue-200 uppercase font-semibold">Año de Tesis</span>
                    <div class="text-3xl font-black text-amber-300"><?php echo $ANIO_SIMULACION; ?></div>
                    <span class="text-[11px] text-green-300 font-medium">Estado: ACTIVO</span>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Estado Actual en la Base de Datos -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
            <h2 class="text-base font-bold text-slate-800 mb-4 flex items-center justify-between">
                <span>Estado Actual en la Base de Datos</span>
                <button onclick="actualizarEstadisticas()" class="text-xs text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Actualizar
                </button>
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-center">
                    <span id="stat-contrib" class="text-xl font-black text-slate-800"><?php echo number_format($countContrib); ?></span>
                    <p class="text-[11px] text-slate-500 font-medium mt-1">Contribuyentes</p>
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-center">
                    <span id="stat-predios" class="text-xl font-black text-slate-800"><?php echo number_format($countPredios); ?></span>
                    <p class="text-[11px] text-slate-500 font-medium mt-1">Predios</p>
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-center">
                    <span id="stat-deudas" class="text-xl font-black text-slate-800"><?php echo number_format($countDeudas); ?></span>
                    <p class="text-[11px] text-slate-500 font-medium mt-1">Cuotas Deuda</p>
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-center">
                    <span id="stat-recibos" class="text-xl font-black text-slate-800"><?php echo number_format($countRecibos); ?></span>
                    <p class="text-[11px] text-slate-500 font-medium mt-1">Recibos Pago</p>
                </div>
                <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-3 text-center">
                    <span id="stat-logs" class="text-xl font-black text-indigo-800"><?php echo number_format($countLogs); ?></span>
                    <p class="text-[11px] text-indigo-700 font-medium mt-1">Bitácora Logs</p>
                </div>
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-center">
                    <span id="stat-users" class="text-xl font-black text-emerald-800"><?php echo $countTotalUser; ?></span>
                    <p class="text-[11px] text-emerald-700 font-semibold mt-1">Usuarios Admin</p>
                </div>
            </div>
        </div>

        <!-- PANEL DE PROGRESO EN VIVO (BARRA DE PROGRESO) -->
        <div id="progreso-container" class="hidden bg-white rounded-2xl shadow-md p-6 border-2 border-indigo-500">
            <div class="flex justify-between items-center mb-2">
                <span id="progreso-paso-texto" class="text-sm font-bold text-indigo-900 flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span>Procesando simulación paso a paso...</span>
                </span>
                <span id="progreso-porcentaje" class="text-sm font-black text-indigo-600">0%</span>
            </div>

            <!-- Barra animada -->
            <div class="w-full bg-slate-200 rounded-full h-4 overflow-hidden shadow-inner">
                <div id="progreso-barra" class="bg-gradient-to-r from-blue-600 to-indigo-600 h-full w-0 transition-all duration-300 ease-out rounded-full"></div>
            </div>

            <p id="progreso-detalle" class="text-xs text-slate-500 mt-2 italic">Preparando entorno...</p>
        </div>

        <!-- ACCIONES PRINCIPALES -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Panel 1: Botón para Limpiar Base de Datos -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-rose-200 flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">1. Limpiar Datos de Prueba</h3>
                    <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                        Vacía de inmediato las tablas de prueba de contribuyentes, predios, deudas y recibos antiguos.
                    </p>
                    <div class="mt-3 bg-rose-50 border border-rose-200 rounded-lg p-3 text-xs text-rose-800">
                        <strong>100% Seguro:</strong> La tabla <code class="font-bold">usuarios</code> (cuentas de administrador, contraseñas) y los parámetros fiscales (tasas, UIT) <strong>no se eliminan</strong>.
                    </div>
                </div>

                <div class="mt-6">
                    <button onclick="ejecutarLimpiezaDirecta()" id="btn-limpiar" class="w-full bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-bold py-3 px-4 rounded-xl shadow-sm transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Eliminar Datos de Prueba
                    </button>
                </div>
            </div>

            <!-- Panel 2: Botón para Iniciar Simulación con Barra de Progreso -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-indigo-200 flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">2. Generar Simulación de Tesis (Oropesa)</h3>
                    <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                        Genera paso a paso con barra de avance en tiempo real para no congelar la pantalla.
                    </p>

                    <div class="mt-3">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Volumen de la Población / Muestra:</label>
                        <select id="select-volumen" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500">
                            <option value="430" selected>Muestra de Tesis: 430 Contribuyentes / 465 Predios (&gt; 420)</option>
                            <option value="600">Muestra Ampliada: 600 Contribuyentes / 650 Predios</option>
                            <option value="1860">Población Censal Total: 1,860 Contribuyentes / 2,000 Predios</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row gap-2">
                    <button onclick="iniciarProcesoSimulacion(false)" id="btn-simular" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-sm transition flex items-center justify-center gap-2 text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Generar con Barra de Progreso
                    </button>
                    <button onclick="iniciarProcesoSimulacion(true)" id="btn-limpiar-simular" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-4 rounded-xl shadow-sm transition flex items-center justify-center gap-2 text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Limpiar y Regenerar de Cero
                    </button>
                </div>
                <div class="mt-2 text-center">
                    <button onclick="ejecutarSimularLogsSolo()" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold underline">
                        ⚡ O generar únicamente los eventos de Bitácora / Auditoría (Logs)
                    </button>
                </div>
            </div>
        </div>

        <!-- Terminal de Logs en Vivo -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 flex items-center justify-between">
                <span>Registro de Ejecución en Vivo (Terminal):</span>
                <span id="log-status" class="text-[11px] font-normal text-slate-400">Listo</span>
            </h3>
            <div id="terminal-logs" class="bg-slate-900 text-emerald-400 font-mono text-xs p-4 rounded-xl overflow-y-auto max-h-56 leading-relaxed border border-slate-800 space-y-1">
                <div>&gt; Sistema SART listo para ejecutar operaciones.</div>
            </div>
        </div>

        <!-- Enlaces rápidos de navegación -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
            <div>
                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block">Módulos del Sistema SART (Tesis Oropesa):</span>
                <span class="text-[11px] text-slate-400">Accede directamente a cada componente implementado</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="admin/dashboard.php" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 px-3.5 rounded-lg shadow-sm transition flex items-center gap-1.5">
                    📊 Dashboard
                </a>
                <a href="admin/catastro/contribuyentes.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold py-2 px-3 rounded-lg border border-slate-300 transition">
                    👥 Contribuyentes
                </a>
                <a href="admin/catastro/predios.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold py-2 px-3 rounded-lg border border-slate-300 transition">
                    🏠 Predios
                </a>
                <a href="admin/reportes/morosidad.php" class="bg-red-50 hover:bg-red-100 text-red-700 text-xs font-semibold py-2 px-3 rounded-lg border border-red-200 transition">
                    ⚠️ Morosidad
                </a>
                <a href="admin/caja/conciliacion.php" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold py-2 px-3 rounded-lg border border-indigo-200 transition">
                    📑 Conciliación
                </a>
                <a href="admin/auditoria/logs.php" class="bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-semibold py-2 px-3 rounded-lg border border-purple-200 transition">
                    🛡️ Bitácora Logs
                </a>
                <a href="admin/reportes/evaluacion_tesis.php" class="bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-semibold py-2 px-3 rounded-lg border border-amber-200 transition">
                    🎓 Evaluación Tesis
                </a>
            </div>
        </div>

    </div>

    <!-- SCRIPT DE CONTROL ASÍNCRONO Y BARRA DE PROGRESO -->
    <script>
        function agregarLog(msg, esError = false) {
            const terminal = document.getElementById('terminal-logs');
            const linea = document.createElement('div');
            linea.className = esError ? 'text-rose-400 font-bold' : 'text-emerald-400';
            linea.textContent = '> ' + msg;
            terminal.appendChild(linea);
            terminal.scrollTop = terminal.scrollHeight;
        }

        function setProgreso(porcentaje, pasoTexto, detalleTexto) {
            const container = document.getElementById('progreso-container');
            container.classList.remove('hidden');

            document.getElementById('progreso-porcentaje').textContent = porcentaje + '%';
            document.getElementById('progreso-barra').style.width = porcentaje + '%';
            if (pasoTexto) document.getElementById('progreso-paso-texto').querySelector('span').textContent = pasoTexto;
            if (detalleTexto) document.getElementById('progreso-detalle').textContent = detalleTexto;
        }

        async function llamarAjax(accion, extraData = {}) {
            const formData = new FormData();
            formData.append('ajax_action', accion);
            for (const key in extraData) {
                formData.append(key, extraData[key]);
            }

            const response = await fetch('simular_tesis_oropesa.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.error || data.message || 'Error en la petición');
            }
            return data;
        }

        async function actualizarEstadisticas() {
            try {
                const res = await llamarAjax('get_stats');
                if (res.stats) {
                    if (document.getElementById('stat-contrib')) document.getElementById('stat-contrib').textContent = res.stats.contribuyentes.toLocaleString();
                    if (document.getElementById('stat-predios')) document.getElementById('stat-predios').textContent = res.stats.predios.toLocaleString();
                    if (document.getElementById('stat-deudas')) document.getElementById('stat-deudas').textContent = res.stats.deudas.toLocaleString();
                    if (document.getElementById('stat-recibos')) document.getElementById('stat-recibos').textContent = res.stats.recibos.toLocaleString();
                    if (document.getElementById('stat-users')) document.getElementById('stat-users').textContent = res.stats.usuarios.toLocaleString();
                    if (document.getElementById('stat-logs')) document.getElementById('stat-logs').textContent = (res.stats.logs || 0).toLocaleString();
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function ejecutarLimpiezaDirecta() {
            if (!confirm('¿Estás seguro de que deseas purgar los datos de prueba de contribuyentes, predios, deudas, recibos y logs?\n\nLos administradores y parámetros fiscales NO serán eliminados.')) {
                return;
            }

            const btn = document.getElementById('btn-limpiar');
            btn.disabled = true;
            btn.classList.add('opacity-50');

            try {
                agregarLog('Iniciando limpieza de tablas transaccionales...');
                const res = await llamarAjax('limpiar');
                agregarLog('✔ ' + res.message);
                await actualizarEstadisticas();
                alert('¡Base de datos limpiada con éxito! Usuarios protegidos intactos.');
            } catch (err) {
                agregarLog('✘ Error: ' + err.message, true);
                alert('Error al limpiar: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.classList.remove('opacity-50');
            }
        }

        async function ejecutarSimularLogsSolo() {
            try {
                agregarLog('Generando eventos de auditoría y trazabilidad cronológica...');
                const res = await llamarAjax('paso_logs');
                agregarLog('✔ ' + res.message);
                await actualizarEstadisticas();
                alert('¡Bitácora de auditoría actualizada! Total de eventos: ' + res.count);
            } catch(e) {
                agregarLog('✘ Error: ' + e.message, true);
                alert('Error al generar logs: ' + e.message);
            }
        }

        async function iniciarProcesoSimulacion(limpiarPrimero = false) {
            if (limpiarPrimero && !confirm('¿Deseas purgar los datos anteriores y generar toda la simulación desde cero?')) {
                return;
            }

            const btnSim = document.getElementById('btn-simular');
            const btnLimSim = document.getElementById('btn-limpiar-simular');
            btnSim.disabled = true;
            btnLimSim.disabled = true;
            btnSim.classList.add('opacity-50');
            btnLimSim.classList.add('opacity-50');

            const cantidadContrib = parseInt(document.getElementById('select-volumen').value) || 430;
            const cantidadPredios = Math.round(cantidadContrib * 1.08); // +8% multipropiedad

            try {
                // PASO 1 (Opcional): Limpieza
                if (limpiarPrimero) {
                    setProgreso(8, 'Paso 1/7: Limpiando base de datos anterior...', 'Purgando tablas de prueba de forma segura...');
                    agregarLog('Purgando tablas de prueba (usuarios intactos)...');
                    const resLim = await llamarAjax('limpiar');
                    agregarLog('✔ ' + resLim.message);
                }

                // PASO 2: Configuración Fiscal
                setProgreso(20, 'Paso 2/7: Verificando Año Fiscal y Tasas...', 'Activando Año 2026 y estructura de arbitrios...');
                agregarLog('Verificando Año Fiscal 2026 y tasas de arbitrios...');
                const resConf = await llamarAjax('paso_config');
                agregarLog('✔ ' + resConf.message);

                // PASO 3: Contribuyentes
                setProgreso(38, 'Paso 3/7: Registrando Contribuyentes Cusqueños...', `Generando ${cantidadContrib} personas y empresas de Oropesa...`);
                agregarLog(`Registrando ${cantidadContrib} contribuyentes (RENIEC / SUNAT)...`);
                const resCont = await llamarAjax('paso_contribuyentes', { cantidad: cantidadContrib });
                agregarLog('✔ ' + resCont.message);
                await actualizarEstadisticas();

                // PASO 4: Predios Catastrados
                setProgreso(55, 'Paso 4/7: Catastrando Predios en Oropesa...', `Asociando ${cantidadPredios} predios a sus propietarios...`);
                agregarLog(`Catastrando ${cantidadPredios} predios en Oropesa...`);
                const resPred = await llamarAjax('paso_predios', { cantidad: cantidadPredios });
                agregarLog('✔ ' + resPred.message);
                await actualizarEstadisticas();

                // PASO 5: Emisión de Deudas
                setProgreso(72, 'Paso 5/7: Emitiendo Deudas Tributarias...', 'Liquidando Impuesto Predial y Arbitrios (28 cuotas por predio)...');
                agregarLog('Emitiendo cuotas tributarias en cuenta corriente...');
                const resEmis = await llamarAjax('paso_emision');
                agregarLog('✔ ' + resEmis.message);
                await actualizarEstadisticas();

                // PASO 6: Pagos y Recaudación
                setProgreso(86, 'Paso 6/7: Simulando Recaudación y Pagos...', 'Generando recibos de pago y activando ingresos del día de hoy...');
                agregarLog('Simulando pagos en ventanilla y caja municipal...');
                const resPagos = await llamarAjax('paso_pagos');
                agregarLog('✔ ' + resPagos.message);
                await actualizarEstadisticas();

                // PASO 7: Bitácora de Auditoría
                setProgreso(95, 'Paso 7/7: Generando Bitácora de Auditoría y Trazabilidad...', 'Registrando eventos de acceso, catastro, emisión y arqueo...');
                agregarLog('Generando eventos cronológicos de auditoría en auditoria_logs...');
                const resLogs = await llamarAjax('paso_logs');
                agregarLog('✔ ' + resLogs.message);
                await actualizarEstadisticas();

                // FINALIZACIÓN
                setProgreso(100, '✔ ¡Simulación Completada con Éxito!', 'Los datos han sido generados y tu Dashboard está 100% activo.');
                agregarLog('====================================================');
                agregarLog('¡PROCESO COMPLETADO AL 100% PARA TU TESIS!');
                agregarLog('====================================================');

                alert('¡Simulación completada con éxito!\n\nPuedes ingresar ahora al Panel de Control (Dashboard), ver la Bitácora de Logs, la Cartera de Morosidad o el Cuadro de Evaluación de Tesis.');

            } catch (err) {
                agregarLog('✘ ERROR CRÍTICO: ' + err.message, true);
                setProgreso(0, '✘ Error en el proceso', err.message);
                alert('Ocurrió un error: ' + err.message);
            } finally {
                btnSim.disabled = false;
                btnLimSim.disabled = false;
                btnSim.classList.remove('opacity-50');
                btnLimSim.classList.remove('opacity-50');
            }
        }
    </script>

            } catch (err) {
                agregarLog('✘ ERROR CRÍTICO: ' + err.message, true);
                setProgreso(0, '✘ Error en el proceso', err.message);
                alert('Ocurrió un error: ' + err.message);
            } finally {
                btnSim.disabled = false;
                btnLimSim.disabled = false;
                btnSim.classList.remove('opacity-50');
                btnLimSim.classList.remove('opacity-50');
            }
        }
    </script>
</body>
</html>
