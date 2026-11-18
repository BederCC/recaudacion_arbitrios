<?php
// admin/exportar_requerimientos_pdf.php
require_once '../vendor/autoload.php';

// Crear nueva instancia de PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configurar información del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema SART');
$pdf->SetTitle('Requerimientos de Información');
$pdf->SetSubject('Solicitud de Datos');

// Configurar cabecera y pie de página
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Configurar fuente
$pdf->SetFont('helvetica', '', 10);

// Agregar una página
$pdf->AddPage();

// Contenido HTML
$logoPath = dirname(__DIR__) . '/src/img/logovd.png';

$html = '
<style>
    h1 { color: #1e3a8a; font-size: 18pt; font-weight: bold; text-align: right; margin: 0; }
    h2 { color: #1e3a8a; font-size: 13pt; font-weight: bold; border-bottom: 2px solid #1e3a8a; padding-bottom: 5px; margin-top: 25px; margin-bottom: 10px; }
    h3 { color: #374151; font-size: 11pt; font-weight: bold; margin-top: 15px; margin-bottom: 5px; }
    p { color: #374151; font-size: 10pt; line-height: 1.5; }
    
    table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    th { background-color: #eff6ff; color: #1e3a8a; font-weight: bold; border: 1px solid #bfdbfe; padding: 10px; text-align: left; font-size: 9pt; }
    td { border: 1px solid #e5e7eb; padding: 10px; color: #4b5563; font-size: 9pt; vertical-align: middle; }
    
    .center { text-align: center; }
    .bold { font-weight: bold; }
    .red { color: #dc2626; font-weight: bold; }
    .gray { color: #9ca3af; }
    
    ul { margin: 0; padding-left: 20px; }
    li { margin-bottom: 3px; }
</style>

<!-- Cabecera -->
<table border="0" cellpadding="5" cellspacing="0" width="100%" style="border-bottom: 2px solid #1e3a8a;">
    <tr>
        <td width="25%" style="border: none; vertical-align: middle;">
            <img src="' . $logoPath . '" height="65">
        </td>
        <td width="75%" style="text-align: right; border: none; vertical-align: middle;">
            <h1>SOLICITUD DE INFORMACIÓN BASE</h1>
            <p style="margin: 2px 0; font-size: 11pt; color: #4b5563;">Sistema de Administración de Recaudación Tributaria (SART)</p>
            <p style="margin: 2px 0; font-size: 9pt; color: #6b7280;">Fecha de Emisión: ' . date('d/m/Y') . '</p>
        </td>
    </tr>
</table>

<br>

<!-- 1. Contribuyentes -->
<h2>1. Padrón de Contribuyentes</h2>
<p>Se requiere la lista actualizada de ciudadanos y empresas sujetos a tributación, con la finalidad de establecer la base de datos de contribuyentes.</p>
<table cellpadding="5">
    <thead>
        <tr>
            <th width="30%">Campo Requerido</th>
            <th width="50%">Descripción / Formato</th>
            <th width="20%" align="center">Obligatorio</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Tipo de Persona</td>
            <td>Natural o Jurídica</td>
            <td align="center" class="red">Sí</td>
        </tr>
        <tr>
            <td>Número Documento</td>
            <td>DNI (8 dígitos) o RUC (11 dígitos)</td>
            <td align="center" class="red">Sí</td>
        </tr>
        <tr>
            <td>Nombre / Razón Social</td>
            <td>Nombre completo o nombre de la empresa</td>
            <td align="center" class="red">Sí</td>
        </tr>
        <tr>
            <td>Dirección Fiscal</td>
            <td>Domicilio para notificaciones</td>
            <td align="center" class="gray">No</td>
        </tr>
        <tr>
            <td>Correo Electrónico / Email</td>
            <td>Para notificaciones y contacto digital</td>
            <td align="center" class="gray">No</td>
        </tr>
        <tr>
            <td>Teléfono / Celular</td>
            <td>Para contacto directo</td>
            <td align="center" class="gray">No</td>
        </tr>
    </tbody>
</table>

<!-- 2. Predios -->
<h2>2. Registro de Predios (Catastro)</h2>
<p>Información física de los inmuebles necesaria para la determinación de la base imponible. <strong>Nota:</strong> El Autovalúo se calculará automáticamente en base a las fórmulas proporcionadas.</p>
<table cellpadding="5">
    <thead>
        <tr>
            <th width="30%">Campo Requerido</th>
            <th width="50%">Descripción / Formato</th>
            <th width="20%" align="center">Obligatorio</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Contribuyente (Propietario)</td>
            <td>DNI o RUC del propietario</td>
            <td align="center" class="red">Sí</td>
        </tr>
        <tr>
            <td>Código Catastral</td>
            <td>Código único del predio</td>
            <td align="center" class="red">Sí</td>
        </tr>
        <tr>
            <td>Dirección del Predio</td>
            <td>Ubicación exacta del inmueble</td>
            <td align="center" class="red">Sí</td>
        </tr>
        <tr>
            <td>Tipo de Uso</td>
            <td>Casa Habitación, Comercio, Industria, etc.</td>
            <td align="center" class="red">Sí</td>
        </tr>
        <tr>
            <td>Área Construida</td>
            <td>Total en metros cuadrados (m²)</td>
            <td align="center" class="red">Sí</td>
        </tr>
        <tr>
            <td>Longitud de Frente</td>
            <td>Metros lineales de fachada</td>
            <td align="center" class="red">Sí</td>
        </tr>
    </tbody>
</table>
<br pagebreak="true" />

<!-- 3. Tasas -->
<h2>3. Tasas de Arbitrios Municipales</h2>
<p>Estructura de costos aprobada por ordenanza municipal vigente.</p>

<h3>3.1. Configuración de Costos por Tipo de Uso</h3>
<p>Favor de completar la siguiente tabla con los valores vigentes:</p>
<table cellpadding="5">
    <thead>
        <tr>
            <th width="25%">TIPO DE USO</th>
            <th width="25%" align="center">LIMPIEZA<br><span style="font-size: 8pt; font-weight: normal;">(Costo x m²)</span></th>
            <th width="25%" align="center">LIMPIEZA<br><span style="font-size: 8pt; font-weight: normal;">(Costo Fijo)</span></th>
            <th width="25%" align="center">SEGURIDAD<br><span style="font-size: 8pt; font-weight: normal;">(Costo Fijo)</span></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>CASA HABITACIÓN</td>
            <td style="background-color: #fffbeb;"></td>
            <td style="background-color: #fffbeb;"></td>
            <td style="background-color: #fffbeb;"></td>
        </tr>
        <tr>
            <td>COMERCIO</td>
            <td style="background-color: #fffbeb;"></td>
            <td style="background-color: #fffbeb;"></td>
            <td style="background-color: #fffbeb;"></td>
        </tr>
        <tr>
            <td>INDUSTRIA</td>
            <td style="background-color: #fffbeb;"></td>
            <td style="background-color: #fffbeb;"></td>
            <td style="background-color: #fffbeb;"></td>
        </tr>
        <tr>
            <td>TERRENO SIN CONSTRUIR</td>
            <td style="background-color: #fffbeb;"></td>
            <td style="background-color: #fffbeb;"></td>
            <td style="background-color: #fffbeb;"></td>
        </tr>
        <tr>
            <td>OTROS FINES</td>
            <td style="background-color: #fffbeb;"></td>
            <td style="background-color: #fffbeb;"></td>
            <td style="background-color: #fffbeb;"></td>
        </tr>
    </tbody>
</table>

<h3>3.2. Detalles Adicionales</h3>
<table cellpadding="5">
    <thead>
        <tr>
            <th width="35%">Concepto</th>
            <th width="45%">Detalle Requerido</th>
            <th width="20%" align="center">Costo / Tasa (S/)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><b>Impuesto Predial (Autovalúo)</b></td>
            <td>
                <ul>
                    <li>Tabla de valores unitarios de edificación y terrenos.</li>
                    <li>Tramos y alícuotas aplicables.</li>
                </ul>
            </td>
            <td align="center" class="gray">Variable</td>
        </tr>
    </tbody>
</table>

<!-- 4. Formato de Entrega -->
<h2>4. Formato de Entrega (Ejemplo)</h2>
<p>Para facilitar la importación masiva de datos, se recomienda entregar la información en formato <strong>Excel (.xlsx)</strong> organizando los datos en dos hojas separadas, conforme a los siguientes ejemplos:</p>

<h3>Tabla 1: Contribuyentes</h3>
<table cellpadding="4">
    <thead>
        <tr>
            <th>TIPO_PERSONA</th>
            <th>DNI_RUC</th>
            <th>NOMBRE_RAZON_SOCIAL</th>
            <th>DIRECCION_FISCAL</th>
            <th>CORREO</th>
            <th>TELEFONO</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>NATURAL</td>
            <td>12345678</td>
            <td>JUAN PEREZ</td>
            <td>AV. LIMA 123</td>
            <td>juan@email.com</td>
            <td>999888777</td>
        </tr>
        <tr>
            <td>JURIDICA</td>
            <td>20100100101</td>
            <td>EMPRESA SAC</td>
            <td>JR. CUSCO 456</td>
            <td>contacto@empresa.com</td>
            <td>01-444-5555</td>
        </tr>
    </tbody>
</table>

<h3>Tabla 2: Predios</h3>
<table cellpadding="4">
    <thead>
        <tr>
            <th>DNI_RUC_PROPIETARIO</th>
            <th>CODIGO_CATASTRAL</th>
            <th>DIRECCION_PREDIO</th>
            <th>TIPO_USO (1-5)</th>
            <th>AREA_CONSTRUIDA_M2</th>
            <th>LONGITUD_FRENTE_M</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>12345678</td>
            <td>01-001-001</td>
            <td>AV. LIMA 123</td>
            <td>1 (CASA)</td>
            <td>120.00</td>
            <td>8.00</td>
        </tr>
        <tr>
            <td>20100100101</td>
            <td>02-050-010</td>
            <td>JR. CUSCO 456</td>
            <td>2 (COMERCIO)</td>
            <td>80.50</td>
            <td>6.00</td>
        </tr>
    </tbody>
</table>
<br pagebreak="true" />
<!-- 5. Fórmulas -->
<h2>5. Fórmulas de Cálculo</h2>
<div style="background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 15px; border-radius: 5px;">
    <p style="margin-top: 0;"><b>Impuesto Predial (Autovalúo):</b></p>
    <p>Favor de proporcionar la documentación técnica detallada (Ordenanza Municipal) que especifique la metodología de cálculo, incluyendo:</p>
    <ul>
        <li>Valores arancelarios de terrenos por zona o vía.</li>
        <li>Valores unitarios oficiales de edificación vigentes.</li>
        <li>Tablas de depreciación por antigüedad y estado de conservación.</li>
        <li>Cualquier otro factor de ajuste aplicable.</li>
    </ul>
</div>
';

// Escribir contenido HTML
$pdf->writeHTML($html, true, false, true, false, '');

// Cerrar y generar PDF
$pdf->Output('Requerimientos_Datos_SART.pdf', 'I');
