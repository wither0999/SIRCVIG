# 🛡️ SIRCVIG - Sistema de Gestión de Vigilancia

Sistema completo de gestión de vigilancia desarrollado en PHP y MySQL siguiendo el patrón MVC (Modelo-Vista-Controlador).

## 📋 Características Principales

### 🔐 Módulo de Autenticación
- Sistema de login seguro con encriptación SHA-256
- Bloqueo automático después de 5 intentos fallidos
- Control de sesiones
- Mensajes de error genéricos para mayor seguridad

### 👥 Gestión de Usuarios y Roles
- **Administrador:** Acceso completo al sistema
- **Secretario:** Gestión de expedientes y asignaciones
- **Supervisor:** Supervisión y consulta de información
- Creación, modificación y bloqueo de cuentas
- Asignación dinámica de roles

### 👮 Gestión de Vigilantes
- CRUD completo de vigilantes
- Registro de documentos (Fotografía, Cédula, Antecedentes)
- Control de estatus (Aspirante, Activo, Inactivo)
- Búsqueda y filtrado
- Ficha personal detallada
- Control de acceso a documentos sensibles según rol

### 📅 Asignación de Puestos
- Asignación de vigilantes a puestos
- **Detección automática de conflictos de horario**
- Cronograma de guardia semanal
- Exportación a PDF
- Control de roles de guardia (24x48, 24x24, 12x12)

### 📊 Dashboard
- Indicadores clave (KPIs)
- Total de vigilantes
- Vigilantes activos
- Puestos cubiertos
- Asignaciones activas

## 🏗️ Arquitectura

El sistema sigue el patrón **MVC (Modelo-Vista-Controlador)**:

```
sircvig/
├── config/          # Configuración del sistema
├── core/            # Clases base (Modelo, ControladorPrincipal)
├── models/          # Modelos de datos (Usuario, Vigilante, etc.)
├── controllers/     # Controladores (lógica de negocio)
├── views/           # Vistas (interfaz de usuario)
├── assets/          # Recursos estáticos (CSS, imágenes)
├── uploads/         # Archivos subidos
└── database/        # Scripts SQL
```

## 🔧 Tecnologías Utilizadas

- **Backend:** PHP 7.4+
- **Base de Datos:** MySQL 5.7+
- **Servidor Web:** Apache (XAMPP)
- **Patrón:** MVC
- **Encriptación:** SHA-256
- **Sesiones:** PHP Sessions

## 📦 Requisitos del Sistema

- XAMPP 7.4 o superior
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Navegador web moderno

## 🚀 Instalación Rápida

1. **Instalar XAMPP** desde https://www.apachefriends.org/
2. **Copiar el proyecto** a `C:\xampp\htdocs\sircvig\`
3. **Iniciar Apache y MySQL** desde el Panel de Control de XAMPP
4. **Importar la base de datos:**
   - Abrir phpMyAdmin: `http://localhost/phpmyadmin`
   - Importar el archivo `database/sircvig.sql`
5. **Acceder al sistema:** `http://localhost/sircvig`
6. **Login por defecto:**
   - Usuario: `admin`
   - Contraseña: `admin123`

📖 **Para una guía detallada, consulta [INSTALACION.md](INSTALACION.md)**

## 🔐 Credenciales por Defecto

**Usuario Administrador:**
- Usuario: `admin`
- Contraseña: `admin123`

⚠️ **IMPORTANTE:** Cambia esta contraseña después de la primera instalación.

## 📁 Estructura de Base de Datos

### Tablas Principales:
- `roles` - Roles del sistema
- `usuarios` - Usuarios del sistema
- `vigilantes` - Información de vigilantes
- `documentos` - Archivos asociados a vigilantes
- `puestos` - Puestos de servicio
- `asignaciones` - Asignaciones de vigilantes a puestos

## 🎨 Características de Diseño

- **Paleta de colores unificada:**
  - Fondo: `#e7ecef`
  - Primario: `#274c77`
  - Secundario: `#8B8C89`
- **Interfaz responsive**
- **Navegación intuitiva**
- **Mensajes de éxito/error claros**

## 🔒 Seguridad

- Encriptación de contraseñas (SHA-256)
- Control de sesiones
- Validación de datos en backend
- Protección contra acceso directo a archivos
- Control de acceso basado en roles
- Bloqueo automático de cuentas

## 📝 Funcionalidades por Rol

### Administrador
- ✅ Acceso completo al sistema
- ✅ Gestión de usuarios y roles
- ✅ Gestión de vigilantes
- ✅ Asignación de puestos
- ✅ Visualización de todos los documentos

### Secretario
- ✅ Gestión de vigilantes (crear, modificar)
- ✅ Visualización de asignaciones
- ❌ No puede ver Antecedentes Penales
- ❌ No puede gestionar usuarios

### Supervisor
- ✅ Consulta de vigilantes (solo lectura)
- ✅ Asignación de puestos
- ✅ Visualización de cronogramas
- ❌ No puede modificar datos sensibles

## 🐛 Solución de Problemas

### Error de conexión a base de datos
- Verifica que MySQL esté iniciado
- Revisa las credenciales en `config/config.php`

### Error 404
- Verifica que Apache esté iniciado
- Asegúrate de que el proyecto esté en la ruta correcta

### Error al subir archivos
- Verifica permisos de la carpeta `uploads/`
- Asegúrate de que la carpeta exista

## 📄 Licencia

Este proyecto es de uso educativo/académico.

## 👨‍💻 Desarrollo

Sistema desarrollado siguiendo las mejores prácticas de PHP y arquitectura MVC.

---

**Versión:** 3.4.0  
**Última actualización:** 2026

