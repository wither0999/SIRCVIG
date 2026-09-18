# Casos de Uso y Análisis Funcional Detallado - SIRCVIG

## 1. Introducción
El Sistema de Registro y Control de Vigilancia (SIRCVIG) es una plataforma integral diseñada para administrar operaciones de seguridad privada. Este documento detalla los actores involucrados, sus funciones específicas y las reglas de negocio que rigen sus interacciones con el sistema.

---

## 2. Actores del Sistema
Los actores representan los roles que interactúan con el software. Cada uno tiene un nivel de acceso y responsabilidades definidas.

| Actor | Descripción | Nivel de Acceso |
| :--- | :--- | :--- |
| **Administrador** | Usuario con control total del sistema. Responsable de la configuración global y gestión de otros usuarios. | **Total (Control Total)** |
| **Secretario(a)** | Encargado de la gestión administrativa y operativa diaria. Su foco es mantener la base de datos de personal y clientes actualizada. | **Medio-Alto (Operativo)** |
| **Supervisor** | Rol de consulta y verificación. Su función es validar la información en campo consultando el sistema, sin modificar datos críticos. | **Lectura (Consulta)** |

---

## 3. Detalle de Funciones por Módulo

### A. Módulo de Autenticación y Seguridad
**Caso de Uso: Iniciar Sesión**
- **Actor:** Todos.
- **Descripción:** El usuario ingresa credenciales (usuario/email y contraseña).
- **Restricciones:** 
  - Bloqueo automático tras 5 intentos fallidos consecutivas.
  - La sesión expira tras inactividad prolongada (seguridad por defecto de PHP).

**Caso de Uso: Gestión de Usuarios del Sistema**
- **Actor:** Solo Administrador.
- **Qué PUEDE hacer:**
  - Crear nuevas cuentas para secretarios o supervisores.
  - Modificar roles de usuarios existentes.
  - Eliminar usuarios o resetear contraseñas.
- **Qué NO PUEDE hacer (Secretario/Supervisor):**
  - Ningún otro rol puede acceder a este módulo.

---

### B. Módulo de Vigilantes (RRHH)
**Caso de Uso: Registrar Nuevo Vigilante**
- **Actor:** Administrador, Secretario.
- **Qué PUEDE hacer:**
  - Llenar ficha con datos personales (Nombre, Cédula, Teléfono, Dirección, Fecha Nacimiento).
  - **Subir Documentos Digitales:**
    - Foto tipo carnet (JPG/PNG).
    - Cédula de Identidad Escaneada.
    - Antecedentes Penales (PDF/Imagen).
- **Validaciones Automáticas:**
  - Edad permitida: 18 a 100 años.
  - Cédula y Teléfono únicos en la base de datos.
- **Restricción (Secretario):** Puede registrar, pero **NO** puede ver el documento de "Antecedentes Penales" una vez subido (solo el Admin tiene acceso a datos sensibles).

**Caso de Uso: Consultar y Editar Vigilante**
- **Actor:** Administrador, Secretario (Edición), Supervisor (Solo Lectura).
- **Qué PUEDE hacer (Admin/Secretario):**
  - Buscar por nombre o cédula.
  - Modificar dirección, teléfono, estatus (Activo/Inactivo) o cualquier dato personal.
  - Imprimir Ficha Técnica en PDF.
- **Qué NO PUEDE hacer (Supervisor):**
  - El Supervisor solo puede ver la ficha y disponiblidad; no puede editar ningún dato.

---

### C. Módulo Operativo (Clientes y Puestos)
**Caso de Uso: Gestión de Clientes**
- **Actor:** Administrador, Secretario.
- **Funciones:**
  - Registrar empresas o personas naturales (Clientes).
  - Datos requeridos: RIF/CI, Razón Social, Dirección Fiscal, Teléfonos de contacto.

**Caso de Uso: Gestión de Puestos de Guardia**
- **Actor:** Administrador, Secretario.
- **Descripción:** Un Cliente puede tener múltiples "Puestos" (sedes, almacenes, oficinas).
- **Funciones:**
  - Crear una ubicación física asociada a un cliente.
  - Ver historial de vigilantes que han pasado por ese puesto.
  - Imprimir reporte de detalles del puesto.
- **Visualización:** El Supervisor puede ver dónde quedan los puestos y quién está asignado actualmente, pero no crear nuevos ni editar los existentes.

---

### D. Módulo de Asignaciones (Cronograma y Turnos)
**Caso de Uso: Crear Asignación (Asignar Turno)**
- **Actor:** Administrador, Secretario.
- **Proceso:**
  1. Seleccionar Vigilante (de la lista de Activos).
  2. Seleccionar Puesto (Lugar de trabajo).
  3. Definir Rango de Fechas (Inicio y Fin).
  4. Seleccionar **Rol de Guardia** (Esquema de horario: 24x48, 12x12, 5x2, etc.).

**Regla de Negocio Crítica: Detección de Conflictos**
- **El Sistema NO PERMITE:**
  - Asignar a un vigilante que ya tiene un turno activo en esas fechas (Solapamiento).
  - Asignar a un vigilante que está en su periodo de descanso obligatorio (calculado según el fin de su turno anterior).
- **Acción del Sistema:** Bloquea el guardado y muestra alerta de error (ej: "Conflicto de horario detectado" o "Vigilante en descanso").

**Caso de Uso: Visualizar Cronograma**
- **Actor:** Todos (Admin, Secretario, Supervisor).
- **Funciones:**
  - Ver calendario visual indicando Vigilante -> Puesto.
  - Filtrar por Quincena o rango de fechas personalizado.
  - Filtrar por Cliente específico.
  - **Exportar PDF:** Generar reporte imprimible del cronograma actual para cartelera.

---

## 4. Matriz de Permisos Resumida

| Función / Módulo | Administrador | Secretario | Supervisor |
| :--- | :---: | :---: | :---: |
| **Login / Logout** | ✅ | ✅ | ✅ |
| **Dashboard (Ver KPIs)** | ✅ | ✅ | ✅ |
| **Crear Usuarios del Sistema** | ✅ | ❌ | ❌ |
| **Registrar/Editar Vigilantes** | ✅ | ✅ | ❌ |
| **Ver Antec. Penales (Docs)**| ✅ | ❌ | ❌ |
| **Crear Clientes/Puestos** | ✅ | ✅ | ❌ |
| **Asignar Turnos (Crear)** | ✅ | ✅ | ❌ |
| **Ver Cronograma** | ✅ | ✅ | ✅ |
| **Exportar PDFs** | ✅ | ✅ | ✅ |
| **Eliminar Registros** | ✅ | ❌ | ❌ |

---

## 5. Explicación de "Qué NO pueden hacer"

### El Supervisor NO PUEDE:
1.  **Modificar Datos:** No puede cambiar nombres, direcciones, teléfonos ni corregir errores de tipeo. Su acceso es estrictamente de "Solo Lectura" para verificación.
2.  **Asignar Personal:** No puede mover a un vigilante de un puesto a otro ni crear nuevos turnos.
3.  **Ver Datos Sensibles:** No tiene acceso a documentos privados como antecedentes o contratos detallados.

### El Secretario NO PUEDE:
1.  **Eliminar Data Sensible:** No puede borrar vigilantes ni usuarios del sistema (para evitar sabotaje o errores graves que borren historial). Solo puede cambiar estatus a "Inactivo".
2.  **Ver Antecedentes:** Por política de privacidad, no accede a los archivos de antecedentes penales de los vigilantes.
3.  **Gestionar Acceso:** No puede crear otros usuarios ni resetear contraseñas de administradores.

### El Sistema (Software) NO PUEDE:
1.  **Resolver Conflictos de Inasistencia Automáticamente:** Si un vigilante falta, el sistema no reasigna automáticamente a otro; muestra la ausencia pero requiere decisión humana del Admin/Secretario para cubrir la vacante.
2.  **Validar Identidad Biométrica:** El control de asistencia es manual (basado en la confianza del reporte de supervisión), no por hardware biométrico integrado.
