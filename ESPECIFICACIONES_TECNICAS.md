# ESPECIFICACIONES TÉCNICAS Y TECNOLÓGICAS DEL SISTEMA

## 1. Identificación del Software

- **Nombre del Proyecto:** Sistema de Recaudación de Arbitrios (SART)
- **Dominio:** Gestión Pública Municipal / Tributación
- **Tipo de Software:** Aplicación Web (Intranet Administrativa)
- **Versión:** 1.0.0

## 2. Descripción Técnica del Sistema

El presente software es una solución tecnológica web diseñada para la automatización integral de la gestión de arbitrios municipales. Su funcionalidad abarca desde el empadronamiento de contribuyentes y catastro de predios hasta la emisión masiva de valores (deuda) y la recaudación en ventanilla. El sistema ha sido construido priorizando la integridad referencial de los datos, la escalabilidad modular y una experiencia de usuario optimizada mediante tecnologías modernas.

## 3. Arquitectura del Software

Se ha implementado una **Arquitectura Monolítica Modular** siguiendo el patrón de diseño **Modelo-Vista (con lógica de dominio)**, adaptada para despliegue en servidores HTTP estándar.

- **Enfoque de Renderizado:** Server-Side Rendering (SSR) con hidratación dinámica en el cliente.
- **Separación de Responsabilidades:**
  - **Backend:** Maneja la lógica de negocio, seguridad y acceso a datos.
  - **Frontend:** Gestiona la presentación y la interacción inmediata del usuario.
  - **Datos:** Garantiza la persistencia y consistencia de la información fiscal.

## 4. Stack Tecnológico (Corroborado)

A continuación se detallan las tecnologías empleadas en el desarrollo, verificadas mediante auditoría del código fuente:

### 4.1. Tecnologías del Lado del Servidor (Backend)

El núcleo del sistema está construido sobre tecnologías maduras y robustas:

- **Lenguaje de Programación:** **PHP 8.0.30**
  - _Justificación:_ Estándar de industria web, ofrece alto rendimiento (JIT) y amplia compatibilidad con infraestructura municipal existente.
- **Gestión de Dependencias:** **Composer**
  - _Uso:_ Administración centralizada de librerías y autoloading de clases (PSR-4).
- **Generación de Documentos:** Librería **TCPDF (^6.10)** (Paquete `tecnickcom/tcpdf`)
  - _Función:_ Generación programática de Recibos de Pago, Estados de Cuenta y Reportes en formato PDF de alta precisión.
- **Acceso a Base de Datos:** **PDO (PHP Data Objects)**
  - _Seguridad:_ Implementación nativa de _Prepared Statements_ para neutralizar ataques de Inyección SQL.

### 4.2. Tecnologías del Lado del Cliente (Frontend)

El sistema utiliza un stack moderno para garantizar una interfaz fluida y adaptable:

- **Lenguaje de Marcado:** **HTML5** (Semántico).
- **Framework de Estilos:** **Tailwind CSS (v4.1.18)**
  - _Tecnología:_ Utility-First CSS framework.
  - _Ventaja:_ Permite la construcción rápida de interfaces personalizadas y responsivas sin la deuda técnica de hojas de estilo tradicionales masivas.
- **Interactividad:** **Alpine.js (v3.0)**
  - _Función:_ Framework JavaScript ligero para manejar el estado de la UI (Modales, Menús, Validaciones) directamente en el marcado, reemplazando dependencias pesadas como jQuery.
- **Visualización de Datos:** **Chart.js**
  - _Función:_ Renderizado de gráficos estadísticos Interactivos en el Dashboard (Ingresos, Métricas).

### 4.3. Base de Datos (Persistencia)

- **Motor:** **MySQL** / **MariaDB**.
- **Codificación:** `utf8mb4` (Soporte completo Unicode/Emojis).
- **Estructura:** Base de datos Relacional normalizada.

## 5. Descripción Funcional de Módulos (Alcance Técnico)

La aplicación se estructura en los siguientes módulos lógicos:

1.  **Seguridad (Auth):** Autenticación de usuarios administradores y gestión de sesiones seguras PHP.
2.  **Dashboard (Cuadro de Mando):** Visualización de KPIs y gráficas de evolución de recaudación.
3.  **Catastro Tributario:**
    - Gestión de Contribuyentes (Personas Naturales/Jurídicas).
    - Gestión de Predios (Vinculación, Ubicación, Uso del predio).
4.  **Emisión y Cálculo:** Motor de cálculo automático de arbitrios basado en tasas configurables y generación de deuda masiva.
5.  **Caja (Punto de Venta):** Interfaz para búsqueda de deuda, procesamiento de pagos y emisión de recibos.
6.  **Configuración Fiscal:** Administración de Años Fiscales y Tasas de Arbitrios (Limpieza, Parques, Serenazgo).
7.  **Reportes:** Módulo de inteligencia de negocios para reportes de ingresos, morosidad y requerimientos.

## 6. Requisitos de Despliegue

- **Servidor Web:** Apache 2.4 o superior (Compatible con archivos `.htaccess`).
- **Intérprete:** PHP 8.0.30+ con extensiones `pdo`, `mbstring`, `gd`.
- **Base de Datos:** MySQL 5.7+ o MariaDB 10.x.
