# DOCUMENTACIÓN TÉCNICA DE BASE DE DATOS Y LÓGICA DE NEGOCIO — SART
**Sistema de Administración de Recaudación Tributaria**  
*Tesis: "Implementación y evaluación de un sistema web para el control de recaudación de arbitrios en la Municipalidad Distrital de Oropesa, 2026"*  
*Autor: Bach. Jhon Puma Mamani | Asesora: Dra. Huaman Vargas Jaqueline | Universidad Tecnológica de los Andes (UTEA)*  
*Manual de Ingeniería de Datos y Simulación Experimental (Población N = 1,860 / Muestra n = 319 / Predios >= 420)*

---

## 1. Arquitectura de Datos y Consideraciones de Simulación

El sistema SART opera sobre un motor **MariaDB / MySQL (InnoDB, utf8mb4)** con integridad referencial estricta mediante claves foráneas.

### Estimación de Volumen para la Muestra de Tesis
- **Contribuyentes ($N = 1,860$):**
  - ~85% Personas Naturales ($N \approx 1,581$) con DNI de 8 dígitos.
  - ~15% Personas Jurídicas ($N \approx 279$) con RUC de 11 dígitos (prefijo 20).
- **Predios ($\approx 2,000$):**
  - ~80% de contribuyentes poseen 1 predio.
  - ~15% poseen 2 predios.
  - ~5% poseen 3 o más predios (inmobiliarias/empresas).
- **Emisión de Deuda en Cuenta Corriente (`cta_cte_tributaria`):**
  - **Por predio por año fiscal:**
    - 4 cuotas trimestrales de **Impuesto Predial**.
    - 12 cuotas mensuales de **Limpieza Pública**.
    - 12 cuotas mensuales de **Seguridad Ciudadana**.
    - **Total:** $4 + 12 + 12 = 28$ registros de deuda por predio por año.
  - **Para 2,000 predios en un año fiscal:**
    $$2,000 \times 28 = 56,000 \text{ registros en } \texttt{cta\_cte\_tributaria}$$
  > [!TIP]
  > Para sembrar 56,000 registros eficientemente en MariaDB/PHP, utiliza transacciones explícitas (`BEGIN ... COMMIT`) e inserciones en bloques (`bulk inserts` de 1,000 a 2,500 registros por consulta) para evitar el cuello de botella de I/O en disco.

---

## 2. Esquema DDL Completo (CREATE TABLE con Índices y Claves Foráneas)

```sql
-- ========================================================
-- SISTEMA DE ADMINISTRACIÓN DE RECAUDACIÓN TRIBUTARIA (SART)
-- ESQUEMA DDL COMPLETO (ORDENADO POR DEPENDENCIAS DE LLAVES)
-- ========================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `recibos_detalle`;
DROP TABLE IF EXISTS `recibos`;
DROP TABLE IF EXISTS `cta_cte_tributaria`;
DROP TABLE IF EXISTS `predios`;
DROP TABLE IF EXISTS `tasas_arbitrios_anuales`;
DROP TABLE IF EXISTS `tipos_uso_predio`;
DROP TABLE IF EXISTS `caja_sesiones`;
DROP TABLE IF EXISTS `uit_historico`;
DROP TABLE IF EXISTS `anios_fiscales`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `auditoria_logs`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- 1. USUARIOS Y CONTROL DE ACCESO
-- --------------------------------------------------------
CREATE TABLE `usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `usuario` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `rol` ENUM('ADMIN','CAJERO','RENTAS') NOT NULL DEFAULT 'RENTAS',
  `estado` ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 2. PARÁMETROS FISCALES GLOBALES
-- --------------------------------------------------------
CREATE TABLE `anios_fiscales` (
  `anio` INT(11) NOT NULL,
  `valor_uit` DECIMAL(10,2) NOT NULL,
  `interes_moratorio_diario` DECIMAL(10,5) DEFAULT 0.03300 COMMENT 'Porcentaje diario (TIM/30)',
  `estado` ENUM('PLANIFICACION','ACTIVO','CERRADO') NOT NULL DEFAULT 'PLANIFICACION',
  PRIMARY KEY (`anio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `uit_historico` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `anio` INT(11) NOT NULL,
  `valor` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_uit_anio` (`anio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 3. PADRÓN DE CONTRIBUYENTES
-- --------------------------------------------------------
CREATE TABLE `contribuyentes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tipo_persona` ENUM('NATURAL','JURIDICA') NOT NULL,
  `numero_documento` VARCHAR(15) NOT NULL,
  `nombre_razon_social` VARCHAR(200) NOT NULL,
  `direccion_fiscal` VARCHAR(255) DEFAULT NULL,
  `correo` VARCHAR(100) DEFAULT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `clave_web` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_contribuyente_documento` (`numero_documento`),
  KEY `idx_contribuyente_nombre` (`nombre_razon_social`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 4. CATASTRO Y USOS DE PREDIO
-- --------------------------------------------------------
CREATE TABLE `tipos_uso_predio` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(10) DEFAULT NULL,
  `descripcion` VARCHAR(100) NOT NULL,
  `factor_limpieza` DECIMAL(5,2) DEFAULT 1.00,
  `factor_seguridad` DECIMAL(5,2) DEFAULT 1.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tipo_uso_codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `predios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `contribuyente_id` INT(11) NOT NULL,
  `anio_declaracion` INT(11) NOT NULL,
  `codigo_catastral` VARCHAR(20) DEFAULT NULL,
  `direccion_predio` VARCHAR(255) NOT NULL,
  `tipo_uso_id` INT(11) NOT NULL,
  `area_construida` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `longitud_frente` DECIMAL(10,2) DEFAULT 0.00,
  `valor_autovaluo` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `estado` ENUM('ACTIVO','INACTIVO','FISCALIZACION') NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id`),
  KEY `idx_predio_contribuyente` (`contribuyente_id`),
  KEY `idx_predio_tipo_uso` (`tipo_uso_id`),
  KEY `idx_predio_anio_decl` (`anio_declaracion`),
  CONSTRAINT `fk_predios_contribuyente` FOREIGN KEY (`contribuyente_id`) REFERENCES `contribuyentes` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_predios_tipo_uso` FOREIGN KEY (`tipo_uso_id`) REFERENCES `tipos_uso_predio` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_predios_anio` FOREIGN KEY (`anio_declaracion`) REFERENCES `anios_fiscales` (`anio`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 5. TASAS DE ARBITRIOS ANUALES (ESTRUCTURA DE COSTOS)
-- --------------------------------------------------------
CREATE TABLE `tasas_arbitrios_anuales` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `anio` INT(11) NOT NULL,
  `tipo_uso_id` INT(11) NOT NULL,
  `costo_limpieza_m2` DECIMAL(10,4) DEFAULT 0.0000,
  `costo_limpieza_fijo` DECIMAL(10,2) DEFAULT 0.00,
  `costo_seguridad_fijo` DECIMAL(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_anio_tipo_uso` (`anio`, `tipo_uso_id`),
  KEY `idx_tasas_tipo_uso` (`tipo_uso_id`),
  CONSTRAINT `fk_tasas_anio` FOREIGN KEY (`anio`) REFERENCES `anios_fiscales` (`anio`) ON UPDATE CASCADE,
  CONSTRAINT `fk_tasas_tipo_uso` FOREIGN KEY (`tipo_uso_id`) REFERENCES `tipos_uso_predio` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 6. CUENTA CORRIENTE TRIBUTARIA (DEUDAS / EMISIÓN)
-- --------------------------------------------------------
CREATE TABLE `cta_cte_tributaria` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `anio` INT(11) NOT NULL,
  `contribuyente_id` INT(11) NOT NULL,
  `predio_id` INT(11) NOT NULL,
  `tributo_concepto` ENUM('IMPUESTO_PREDIAL','LIMPIEZA_PUBLICA','SEGURIDAD_CIUDADANA') NOT NULL,
  `periodo` INT(11) NOT NULL COMMENT '1-4 para Predial; 1-12 para Arbitrios',
  `fecha_vencimiento` DATE NOT NULL,
  `monto_insoluto` DECIMAL(10,2) NOT NULL,
  `monto_pagado` DECIMAL(10,2) DEFAULT 0.00,
  `estado` ENUM('PENDIENTE','PAGADO','FRACCIONADO','ANULADO') NOT NULL DEFAULT 'PENDIENTE',
  PRIMARY KEY (`id`),
  KEY `idx_cta_anio` (`anio`),
  KEY `idx_cta_predio` (`predio_id`),
  KEY `idx_busqueda_pagos` (`contribuyente_id`,`anio`,`estado`),
  CONSTRAINT `fk_cta_anio` FOREIGN KEY (`anio`) REFERENCES `anios_fiscales` (`anio`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cta_contribuyente` FOREIGN KEY (`contribuyente_id`) REFERENCES `contribuyentes` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cta_predio` FOREIGN KEY (`predio_id`) REFERENCES `predios` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 7. CAJA, SESIONES Y RECAUDACIÓN (PAGOS)
-- --------------------------------------------------------
CREATE TABLE `caja_sesiones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `usuario_cajero` VARCHAR(50) NOT NULL,
  `fecha_apertura` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `fecha_cierre` DATETIME DEFAULT NULL,
  `monto_recaudado` DECIMAL(10,2) DEFAULT 0.00,
  `estado` ENUM('ABIERTA','CERRADA') NOT NULL DEFAULT 'ABIERTA',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `recibos` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `anio_fiscal` INT(11) NOT NULL,
  `contribuyente_id` INT(11) NOT NULL,
  `caja_sesion_id` INT(11) DEFAULT NULL,
  `numero_serie` VARCHAR(10) NOT NULL,
  `fecha_pago` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `monto_total` DECIMAL(10,2) NOT NULL,
  `origen_pago` ENUM('VENTANILLA_MUNICIPAL','WEB_ONLINE','APP_MOVIL','BANCO_CONVENIO') NOT NULL DEFAULT 'VENTANILLA_MUNICIPAL',
  `metodo_pago` ENUM('EFECTIVO','TARJETA','TRANSFERENCIA') NOT NULL DEFAULT 'EFECTIVO',
  `codigo_operacion_bancaria` VARCHAR(50) DEFAULT NULL,
  `estado` ENUM('VALIDO','ANULADO') NOT NULL DEFAULT 'VALIDO',
  PRIMARY KEY (`id`),
  KEY `idx_recibo_contribuyente` (`contribuyente_id`),
  KEY `idx_recibo_caja` (`caja_sesion_id`),
  CONSTRAINT `fk_recibos_contribuyente` FOREIGN KEY (`contribuyente_id`) REFERENCES `contribuyentes` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_recibos_caja` FOREIGN KEY (`caja_sesion_id`) REFERENCES `caja_sesiones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `recibos_detalle` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `recibo_id` BIGINT(20) NOT NULL,
  `deuda_id` BIGINT(20) NOT NULL,
  `monto_capital` DECIMAL(10,2) NOT NULL,
  `monto_mora` DECIMAL(10,2) DEFAULT 0.00,
  `monto_gastos` DECIMAL(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_detalle_recibo` (`recibo_id`),
  KEY `idx_detalle_deuda` (`deuda_id`),
  CONSTRAINT `fk_detalle_recibo` FOREIGN KEY (`recibo_id`) REFERENCES `recibos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detalle_deuda` FOREIGN KEY (`deuda_id`) REFERENCES `cta_cte_tributaria` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 8. BITÁCORA / LOGS DE AUDITORÍA (MÓDULO DE TRAZABILIDAD)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `auditoria_logs` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `usuario_id` INT(11) DEFAULT NULL,
  `modulo` VARCHAR(50) NOT NULL COMMENT 'CATASTRO, EMISION, CAJA, CONFIG',
  `accion` VARCHAR(50) NOT NULL COMMENT 'INSERT, UPDATE, DELETE, LOGIN, EMISION_MASIVA',
  `tabla_afectada` VARCHAR(50) DEFAULT NULL,
  `registro_id` VARCHAR(50) DEFAULT NULL,
  `datos_previos` LONGTEXT DEFAULT NULL COMMENT 'JSON con datos anteriores',
  `datos_nuevos` LONGTEXT DEFAULT NULL COMMENT 'JSON con datos modificados',
  `ip_origen` VARCHAR(45) DEFAULT NULL,
  `fecha_evento` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_usuario` (`usuario_id`),
  KEY `idx_audit_fecha` (`fecha_evento`),
  CONSTRAINT `fk_audit_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

---

## 3. Diccionario Rápido de Datos

### 3.1. `contribuyentes` (Padrón de Contribuyentes)
| Campo | Tipo | Nulo | Clave | Valores Aceptados / Descripción |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `INT(11)` | No | PK | Auto-incremental. |
| `tipo_persona` | `ENUM` | No | - | `'NATURAL'`, `'JURIDICA'`. |
| `numero_documento` | `VARCHAR(15)` | No | UNIQUE | DNI (8 dígitos) o RUC (11 dígitos, ej: 20...). |
| `nombre_razon_social`| `VARCHAR(200)`| No | - | Nombres y Apellidos completos o Razón Social. |
| `direccion_fiscal` | `VARCHAR(255)`| Sí | - | Domicilio fiscal del contribuyente. |
| `correo` | `VARCHAR(100)`| Sí | - | Correo electrónico para notificaciones / clave web. |
| `telefono` | `VARCHAR(20)` | Sí | - | Teléfono fijo o móvil (9 dígitos). |
| `clave_web` | `VARCHAR(255)`| Sí | - | Hash bcrypt para portal del vecino (`public/neighbor.php`). |

### 3.2. `predios` (Registro Catastral)
| Campo | Tipo | Nulo | Clave | Valores Aceptados / Descripción |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `INT(11)` | No | PK | Auto-incremental. |
| `contribuyente_id` | `INT(11)` | No | FK | Referencia a `contribuyentes.id`. |
| `anio_declaracion` | `INT(11)` | No | FK | Referencia a `anios_fiscales.anio` (ej: 2025, 2026). |
| `codigo_catastral` | `VARCHAR(20)` | Sí | - | Código único catastral (ej: `'01-001-001'`). |
| `direccion_predio` | `VARCHAR(255)`| No | - | Ubicación física del predio. |
| `tipo_uso_id` | `INT(11)` | No | FK | Referencia a `tipos_uso_predio.id` (1 a 5). |
| `area_construida` | `DECIMAL(10,2)`| No | - | Área techada en $m^2$ (Default: 0.00). |
| `longitud_frente` | `DECIMAL(10,2)`| Sí | - | Metros lineales de frontis (Default: 0.00). |
| `valor_autovaluo` | `DECIMAL(12,2)`| No | - | Base imponible calculada en S/ para Impuesto Predial. |
| `estado` | `ENUM` | Sí | - | `'ACTIVO'`, `'INACTIVO'`, `'FISCALIZACION'` (Default: `'ACTIVO'`). |

### 3.3. `tipos_uso_predio` (Clasificación de Usos)
| `id` | `codigo` | `descripcion` | `factor_limpieza` | `factor_seguridad` |
| :---: | :---: | :--- | :---: | :---: |
| 1 | `'01'` | CASA HABITACION | 1.00 | 1.00 |
| 2 | `'02'` | COMERCIO | 2.50 | 1.50 |
| 3 | `'03'` | INDUSTRIA | 3.00 | 2.00 |
| 4 | `'04'` | TERRENO SIN CONSTRUIR | 0.50 | 1.00 |
| 5 | `'05'` | OTROS FINES | 1.50 | 1.00 |

### 3.4. `anios_fiscales` y `tasas_arbitrios_anuales`
- **`anios_fiscales`**:
  - `anio` (PK, `INT(11)`)
  - `valor_uit` (`DECIMAL(10,2)`): UIT oficial (ej: 2025 = 5,350.00, 2026 = 5,500.00).
  - `interes_moratorio_diario` (`DECIMAL(10,5)`): Default `0.03300` (equivalente a 0.033% diario).
  - `estado`: `'PLANIFICACION'`, `'ACTIVO'`, `'CERRADO'`.
- **`tasas_arbitrios_anuales`**:
  - `anio` + `tipo_uso_id` (Unique Composite Key).
  - `costo_limpieza_m2`: Tasa unitaria anual por metro cuadrado de área construida.
  - `costo_limpieza_fijo`: Importe anual fijo de recolección de residuos.
  - `costo_seguridad_fijo`: Importe anual fijo de serenazgo / seguridad ciudadana.

### 3.5. `cta_cte_tributaria` (Deuda Individualizada por Cuota)
| Campo | Tipo | Nulo | Clave | Valores Aceptados / Descripción |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT(20)` | No | PK | Auto-incremental. |
| `anio` | `INT(11)` | No | FK | Año del tributo (`anios_fiscales.anio`). |
| `contribuyente_id` | `INT(11)` | No | FK | `contribuyentes.id`. |
| `predio_id` | `INT(11)` | No | FK | `predios.id`. |
| `tributo_concepto` | `ENUM` | No | - | `'IMPUESTO_PREDIAL'`, `'LIMPIEZA_PUBLICA'`, `'SEGURIDAD_CIUDADANA'`. |
| `periodo` | `INT(11)` | No | - | Trimestres (1-4) para Predial; Meses (1-12) para Arbitrios. |
| `fecha_vencimiento`| `DATE` | No | - | Fecha límite de pago puntual. |
| `monto_insoluto` | `DECIMAL(10,2)`| No | - | Cuota nominal determinada sin recargos. |
| `monto_pagado` | `DECIMAL(10,2)`| Sí | - | Monto amortizado / cancelado (Default: 0.00). |
| `estado` | `ENUM` | Sí | - | `'PENDIENTE'`, `'PAGADO'`, `'FRACCIONADO'`, `'ANULADO'`. |

### 3.6. `recibos` y `recibos_detalle` (Caja / Recaudación)
- `recibos.origen_pago`: `'VENTANILLA_MUNICIPAL'`, `'WEB_ONLINE'`, `'APP_MOVIL'`, `'BANCO_CONVENIO'`.
- `recibos.metodo_pago`: `'EFECTIVO'`, `'TARJETA'`, `'TRANSFERENCIA'`.
- `recibos.estado`: `'VALIDO'`, `'ANULADO'`.
- `recibos_detalle`: Vincula `recibo_id` con `deuda_id` (`cta_cte_tributaria.id`), almacenando el desglose `monto_capital`, `monto_mora` y `monto_gastos`.

---

## 4. Lógica y Fórmulas Exactas de Liquidación (Motor de Emisión Masiva)

El proceso de emisión automática se ejecuta en `api/emission/generate.php` y aplica las siguientes reglas de negocio:

### A. Impuesto Predial (Escala Progresiva Acumulativa - D. Leg. 776)
La base imponible es el valor del autovalúo del predio ($V_A$) evaluado contra los tramos de la Unidad Impositiva Tributaria ($UIT$):

1. **Definición de Tramos:**
   - Tramo 1: Hasta $15 \times UIT$
   - Tramo 2: De más de $15 \times UIT$ hasta $60 \times UIT$
   - Tramo 3: Más de $60 \times UIT$

2. **Cálculo del Impuesto Anual ($I_{anual}$):**
   - **Si $V_A \le 15 \times UIT$:**
     $$I_{anual} = V_A \times 0.002 \quad (0.2\%)$$
   - **Si $15 \times UIT < V_A \le 60 \times UIT$:**
     $$I_{anual} = (15 \times UIT \times 0.002) + \Big((V_A - 15 \times UIT) \times 0.006\Big)$$
   - **Si $V_A > 60 \times UIT$:**
     $$I_{anual} = (15 \times UIT \times 0.002) + (45 \times UIT \times 0.006) + \Big((V_A - 60 \times UIT) \times 0.010\Big)$$

3. **Fraccionamiento y Vencimientos:**
   - El impuesto se divide en **4 cuotas trimestrales iguales**:
     $$\text{Cuota Trimestral} = \text{round}\left(\frac{I_{anual}}{4}, 2\right)$$
   - Vencimientos normativos:
     - Periodo 1: Último día de Febrero (`YYYY-02-28`)
     - Periodo 2: Último día de Mayo (`YYYY-05-31`)
     - Periodo 3: Último día de Agosto (`YYYY-08-31`)
     - Periodo 4: Último día de Noviembre (`YYYY-11-30`)

---

### B. Arbitrios Municipales (Limpieza Pública y Seguridad Ciudadana)
Se obtienen las tasas aprobadas para el año y tipo de uso desde `tasas_arbitrios_anuales`:

1. **Limpieza Pública (Recolección de Residuos + Barrido):**
   - **Costo Anual:**
     $$C_{LP\_anual} = (\text{Área Construida } m^2 \times \text{costo\_limpieza\_m2}) + \text{costo\_limpieza\_fijo}$$
   - **Cuota Mensual (Periodos 1 al 12):**
     $$\text{Mensual Limpieza} = \text{round}\left(\frac{C_{LP\_anual}}{12}, 2\right)$$

2. **Seguridad Ciudadana (Serenazgo):**
   - **Costo Anual:**
     $$C_{SC\_anual} = \text{costo\_seguridad\_fijo}$$
   - **Cuota Mensual (Periodos 1 al 12):**
     $$\text{Mensual Seguridad} = \text{round}\left(\frac{C_{SC\_anual}}{12}, 2\right)$$

3. **Vencimientos de Arbitrios:**
   - Se generan **12 cuotas mensuales** (del mes 1 al 12).
   - Fecha de vencimiento: **Último día natural del mes correspondiente** (`date('Y-m-t', strtotime("$anio-$mes-01"))`).

---

### C. Liquidación de Mora e Interés Moratorio (TIM) en Caja
Cuando una cuota vence y no ha sido pagada (`fecha_vencimiento < fecha_actual`), el sistema liquida el interés en `api/search/debts.php`:

$$\text{Días de Atraso} = \text{Fecha Actual} - \text{Fecha Vencimiento}$$
$$\text{Factor de Mora} = \left(\frac{\text{interes\_moratorio\_diario}}{100}\right) \times \text{Días de Atraso}$$
$$\text{Mora (S/)} = \text{round}(\text{monto\_insoluto} \times \text{Factor de Mora}, 2)$$
$$\text{Monto Total a Pagar} = \text{monto\_insoluto} + \text{Mora}$$

---

## 5. Script Seeder de Simulación en PHP (`seed_simulacion_tesis.php`)

```php
<?php
/**
 * SEEDER DE SIMULACIÓN MASIVA - PROYECTO SART
 * Población: N = 1860 Contribuyentes | ~2000 Predios | Emisión Masiva
 * Ejecutable vía CLI: php seed_simulacion_tesis.php
 */

require_once __DIR__ . '/includes/db.php';

// Aumentar límites para proceso masivo
ini_set('memory_limit', '1024M');
set_time_limit(600);

echo "========================================================\n";
echo " INICIANDO SEEDER DE SIMULACIÓN SART (TESIS)\n";
echo "========================================================\n";

try {
    $pdo->beginTransaction();

    $anioSimulacion = 2026;
    $uitSimulacion = 5500.00;

    // 1. Asegurar Año Fiscal
    $stmtAnio = $pdo->prepare("INSERT INTO anios_fiscales (anio, valor_uit, interes_moratorio_diario, estado)
                               VALUES (?, ?, 0.03300, 'ACTIVO')
                               ON DUPLICATE KEY UPDATE valor_uit = VALUES(valor_uit), estado = 'ACTIVO'");
    $stmtAnio->execute([$anioSimulacion, $uitSimulacion]);

    // 2. Generar 1,860 Contribuyentes
    echo "[1/4] Creando 1,860 Contribuyentes...\n";
    $nombres = ['Carlos', 'María', 'José', 'Ana', 'Luis', 'Jorge', 'Rosa', 'Pedro', 'Lucía', 'Manuel', 'Elena', 'Víctor'];
    $apellidos = ['Quispe', 'Flores', 'Rodríguez', 'García', 'Rojas', 'Huamán', 'Mendoza', 'Sánchez', 'Pérez', 'Torres'];
    $empresas = ['INVERSIONES', 'CONSTRUCTORA', 'SERVICIOS GENERALES', 'DISTRIBUIDORA', 'CONSULTORA', 'COMERCIAL'];

    $contribuyentesIds = [];
    $stmtContrib = $pdo->prepare("INSERT INTO contribuyentes 
        (tipo_persona, numero_documento, nombre_razon_social, direccion_fiscal, correo, telefono, clave_web) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");

    $dniBase = 10000001;
    $rucBase = 20500000001;

    for ($i = 1; $i <= 1860; $i++) {
        $esJuridica = ($i % 7 === 0); // ~15% personas jurídicas
        if ($esJuridica) {
            $tipoPersona = 'JURIDICA';
            $numDoc = (string)($rucBase + $i);
            $nombre = $empresas[array_rand($empresas)] . " " . $apellidos[array_rand($apellidos)] . " S.A.C.";
            $correo = "contacto" . $i . "@empresa.com";
        } else {
            $tipoPersona = 'NATURAL';
            $numDoc = str_pad((string)($dniBase + $i), 8, '0', STR_PAD_LEFT);
            $nombre = $nombres[array_rand($nombres)] . " " . $apellidos[array_rand($apellidos)] . " " . $apellidos[array_rand($apellidos)];
            $correo = "ciudadano" . $i . "@gmail.com";
        }
        $direccion = "Calle Los Álamos " . rand(100, 999) . " - Sector " . rand(1, 8);
        $telefono = "9" . rand(10000000, 99999999);
        $clave = password_hash($numDoc, PASSWORD_BCRYPT);

        $stmtContrib->execute([$tipoPersona, $numDoc, $nombre, $direccion, $correo, $telefono, $clave]);
        $contribuyentesIds[] = $pdo->lastInsertId();
    }

    // 3. Generar ~2,000 Predios
    echo "[2/4] Creando 2,000 Predios vinculados...\n";
    $stmtPredio = $pdo->prepare("INSERT INTO predios 
        (contribuyente_id, anio_declaracion, codigo_catastral, direccion_predio, tipo_uso_id, area_construida, longitud_frente, valor_autovaluo, estado) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVO')");

    $totalPredios = 2000;
    $prediosCreados = 0;

    // Asignación de predios (1 por contribuyente para los primeros 1860, y extras para llegar a 2000)
    for ($i = 0; $i < $totalPredios; $i++) {
        $contribId = ($i < 1860) ? $contribuyentesIds[$i] : $contribuyentesIds[rand(0, 1859)];
        $codigoCat = sprintf("%02d-%03d-%03d", rand(1, 10), rand(1, 90), rand(1, 90));
        $direccionPredio = "Av. Principal Nro " . rand(100, 1500) . " Mz " . chr(rand(65, 75)) . " Lte " . rand(1, 30);
        
        // Ponderación de tipos de uso: 70% Casa habitación, 15% Comercio, 5% Industria, 5% Terreno, 5% Otros
        $randUso = rand(1, 100);
        if ($randUso <= 70) $tipoUso = 1;
        elseif ($randUso <= 85) $tipoUso = 2;
        elseif ($randUso <= 90) $tipoUso = 3;
        elseif ($randUso <= 95) $tipoUso = 4;
        else $tipoUso = 5;

        $areaConstruida = ($tipoUso == 4) ? 0.00 : rand(60, 350) + (rand(0, 99) / 100);
        $longitudFrente = rand(6, 20) + (rand(0, 99) / 100);
        $autovaluo = ($tipoUso == 4) ? rand(25000, 70000) : ($areaConstruida * rand(350, 750) + rand(20000, 60000));

        $stmtPredio->execute([
            $contribId,
            $anioSimulacion,
            $codigoCat,
            $direccionPredio,
            $tipoUso,
            round($areaConstruida, 2),
            round($longitudFrente, 2),
            round($autovaluo, 2)
        ]);
        $prediosCreados++;
    }

    $pdo->commit();
    echo "¡Simulación base completada con éxito!\n";
    echo "- Contribuyentes insertados: 1,860\n";
    echo "- Predios insertados: 2,000\n";
    echo "Siguiente paso: Ejecutar Emisión Masiva para generar las ~56,000 cuotas tributarias.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "ERROR EN LA SIMULACIÓN: " . $e->getMessage() . "\n";
}
```
