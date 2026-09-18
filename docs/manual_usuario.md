# Manual de Usuario y Análisis del Sistema SIRCVIIG

## Introducción
El Sistema de Registro y Control de Vigilancia (SIRCVIIG) es una plataforma web integral diseñada para la gestión eficiente de empresas de seguridad privada. Permite administrar personal, clientes, puestos de guardia y la asignación operativa de turnos (cronogramas).

---

## 1. Acceso al Sistema

### Login
*   **URL:** `/index.php`
*   El usuario debe ingresar su correo electrónico y contraseña.
*   **Roles:** El acceso y las opciones disponibles dependen del rol del usuario (Administrador, Supervisor, Secretario).

### Recuperación de Contraseña
*   En caso de olvido, dispone de una opción "¿Olvidó su contraseña?" que inicia un flujo de recuperación seguro.

---

## 2. Dashboard (Cuadro de Mando)
*   **Vista Principal:** Muestra tarjetas con estadísticas clave en tiempo real:
    *   Total de Vigilantes (Activos/Inactivos).
    *   Clientes Registrados.
    *   Puestos de Guardia Activos.
    *   Asignaciones Vigentes.
*   Accesos directos a las funciones más frecuentes.

---

## 3. Módulo de Vigilantes
Gestión del talento humano operativo.

### Funcionalidades:
*   **Listado de Vigilantes:** Tabla con búsqueda en tiempo real. Muestra foto, datos básicos y estatus.
*   **Registro de Vigilante:** Formulario completo para ingresar datos personales, contacto, y carga de documentos digitalizados.
    *   *Validaciones:* Cédula única, mayoría de edad, formatos de archivo.
*   **Ficha Personal (Perfil):** Vista detallada del vigilante.
    *   Contiene: Información personal, dirección, documentos cargados.
    *   **Acciones:** Editar datos, **Imprimir Ficha (PDF)**.
*   **Disponibilidad:** Herramienta para consultar qué vigilantes están libres en un rango de fechas.

---

## 4. Módulo de Clientes
Gestión de las empresas o personas que contratan el servicio.

### Funcionalidades:
*   **Listado de Clientes:** Directorio de clientes con filtros.
*   **Registro/Edición:** Captura de RIF/Cédula, Razón Social, Teléfonos y Dirección Fiscal.
*   **Ficha de Cliente:** Vista "Card" con todos los detalles.
    *   **PDF:** Generación de reporte imprimible del cliente.

---

## 5. Módulo de Puestos de Guardia
Administración de las ubicaciones físicas donde se presta el servicio. Un cliente puede tener múltiples puestos.

### Funcionalidades:
*   **Listado de Puestos:** Visualización de sitios vigilados.
*   **Creación de Puesto:** Asociación de un Cliente existente con una dirección física y descripción del sitio.
*   **Detalles del Puesto:** Vista profunda del sitio.
    *   Incluye historial de **Asignaciones Activas** en ese puesto.
    *   **PDF:** Reporte imprimible con los datos del puesto y quién está asignado actualmente.

---

## 6. Módulo de Asignaciones (Cronograma)
El núcleo operativo del sistema. Relaciona Vigilantes con Puestos en un rango de tiempo.

### Funcionalidades:
*   **Cronograma de Guardia:** Calendario/Tabla que muestra quién está dónde y cuándo.
    *   **Filtros Inteligentes:** Por rango de fechas (Quincena actual por defecto) y por Cliente.
    *   **Exportar PDF:** Descarga el cronograma visible para impresión y cartelera.
*   **Nueva Asignación:**
    *   Selección de Puesto y Vigilante.
    *   Definición de Fechas (Inicio/Fin).
    *   Selección de Rol (Turno: 24x48, 12x12, etc.).
    *   **Validación de Conflictos:** El sistema impide asignar a un vigilante si ya tiene guardia en esas fechas.
*   **Ficha de Asignación:** Detalle específico de un turno.
    *   Accesible desde el botón "Ver" (Ojo) en el cronograma.
    *   Muestra datos cruzados: Vigilante + Puesto + Turno.
    *   **PDF Individual:** Genera la "Boleta de Servicio" o ficha de asignación.

---

## 7. Módulo de Usuarios y Seguridad
Administración del acceso al sistema SIRCVIIG.

### Funcionalidades:
*   **Gestión de Usuarios:** (Solo Administradores) Crear, editar y eliminar cuentas de acceso al sistema.
*   **Roles y Permisos:**
    *   *Administrador:* Acceso total.
    *   *Supervisor:* Gestión operativa (Vigilantes, Puestos, Asignaciones). Sin acceso a eliminar usuarios críticos.
    *   *Secretario:* Acceso mayoritariamente de lectura y reportes.

---

## 8. Aspectos Técnicos y Diseño
*   **Interfaz:** Diseño responsivo y moderno basado en tarjetas (Cards).
*   **Colores e Iconos:** Uso consistente de colores semánticos (Verde=Activo, Rojo=Inactivo/PDF, Azul=Acciones Principales).
*   **Validaciones:** Formularios validados tanto en navegador (Javascript) como en servidor (PHP) para garantizar integridad de datos.

---

## Flujo de Trabajo Típico
1.  Registrar **Cliente**.
2.  Crear **Puestos de Guardia** para ese cliente.
3.  Registrar **Vigilantes** (con sus documentos).
4.  Ir a **Asignaciones** > **Nueva Asignación** para colocar al vigilante en el puesto.
5.  Imprimir el **PDF** de la asignación y entregarlo al vigilante.
