# 🚀 GUÍA DE INSTALACIÓN PASO A PASO - SISTEMA SIRCVIG

Esta guía te ayudará a instalar y configurar el Sistema SIRCVIG desde cero en tu PC con XAMPP.

---

## 📋 REQUISITOS PREVIOS

Antes de comenzar, asegúrate de tener instalado:
- **XAMPP** (versión 7.4 o superior recomendada)
- **Navegador web** (Chrome, Firefox, Edge, etc.)
- **Editor de texto** (Opcional: Visual Studio Code, Notepad++)

---

## 📦 PASO 1: INSTALAR XAMPP

### 1.1 Descargar XAMPP
1. Ve a la página oficial: https://www.apachefriends.org/
2. Descarga la versión para Windows
3. Ejecuta el instalador

### 1.2 Instalar XAMPP
1. Ejecuta el archivo `.exe` descargado
2. Durante la instalación:
   - Selecciona los componentes: **Apache** y **MySQL** (mínimo)
   - Elige una carpeta de instalación (por defecto: `C:\xampp`)
   - Completa la instalación

### 1.3 Iniciar Servicios
1. Abre el **Panel de Control de XAMPP**
2. Haz clic en **Start** para:
   - ✅ **Apache** (servidor web)
   - ✅ **MySQL** (base de datos)
3. Verifica que ambos servicios estén en color **verde**

---

## 📁 PASO 2: COPIAR EL PROYECTO

### 2.1 Ubicación del Proyecto
1. Navega a la carpeta de XAMPP: `C:\xampp\htdocs\`
2. Crea una carpeta llamada `sircvig` (o copia el proyecto completo aquí)
3. La ruta final debe ser: `C:\xampp\htdocs\sircvig\`

### 2.2 Estructura de Carpetas
Asegúrate de que tu proyecto tenga esta estructura:
```
sircvig/
├── assets/
│   └── css/
│       └── estilos.css
├── config/
│   ├── config.php
│   └── autoload.php
├── controllers/
│   ├── ControladorAsignacion.php
│   ├── ControladorDashboard.php
│   ├── ControladorExpediente.php
│   ├── ControladorLogin.php
│   └── ControladorUsuario.php
├── core/
│   ├── ControladorPrincipal.php
│   └── Modelo.php
├── database/
│   └── sircvig.sql
├── models/
│   ├── Asignacion.php
│   ├── Documento.php
│   ├── Puesto.php
│   ├── Rol.php
│   ├── Usuario.php
│   └── Vigilante.php
├── uploads/
│   └── vigilantes/
├── views/
│   ├── includes/
│   │   ├── footer.php
│   │   └── header.php
│   ├── asignar_puesto.php
│   ├── cronograma_guardia.php
│   ├── dashboard.php
│   ├── ficha_personal.php
│   ├── gestion_roles.php
│   ├── listado_vigilantes.php
│   ├── login.php
│   └── registro_vigilante.php
├── .htaccess
├── index.php
└── INSTALACION.md
```

---

## 🗄️ PASO 3: CREAR LA BASE DE DATOS

### 3.1 Acceder a phpMyAdmin
1. Abre tu navegador
2. Ve a: `http://localhost/phpmyadmin`
3. Deberías ver la interfaz de phpMyAdmin

### 3.2 Importar la Base de Datos
**Opción A: Desde phpMyAdmin (Recomendado)**
1. Haz clic en la pestaña **"Importar"**
2. Haz clic en **"Seleccionar archivo"**
3. Navega a: `C:\xampp\htdocs\sircvig\database\sircvig.sql`
4. Haz clic en **"Continuar"** o **"Ejecutar"**
5. Espera a que se complete la importación
6. Verás un mensaje de éxito ✅

**Opción B: Desde la línea de comandos**
1. Abre el **Símbolo del sistema** (CMD) como Administrador
2. Navega a la carpeta de MySQL:
   ```cmd
   cd C:\xampp\mysql\bin
   ```
3. Ejecuta:
   ```cmd
   mysql -u root -p < C:\xampp\htdocs\sircvig\database\sircvig.sql
   ```
4. Si te pide contraseña, presiona Enter (por defecto no hay contraseña)

### 3.3 Verificar la Base de Datos
1. En phpMyAdmin, haz clic en **"sircvig"** en el menú lateral
2. Deberías ver las siguientes tablas:
   - ✅ `roles`
   - ✅ `usuarios`
   - ✅ `vigilantes`
   - ✅ `documentos`
   - ✅ `puestos`
   - ✅ `asignaciones`

---

## ⚙️ PASO 4: CONFIGURAR EL PROYECTO

### 4.1 Verificar Configuración de Base de Datos
1. Abre el archivo: `C:\xampp\htdocs\sircvig\config\config.php`
2. Verifica estas líneas (deben estar así por defecto):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'sircvig');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Vacío por defecto en XAMPP
   ```
3. Si cambiaste la contraseña de MySQL, actualiza `DB_PASS`

### 4.2 Verificar Ruta Base
1. En el mismo archivo `config.php`, verifica:
   ```php
   define('BASE_URL', 'http://localhost/sircvig');
   ```
2. Si tu proyecto está en otra ubicación, ajusta la ruta

### 4.3 Crear Carpeta de Uploads
1. Asegúrate de que exista la carpeta: `C:\xampp\htdocs\sircvig\uploads\vigilantes\`
2. Si no existe, créala manualmente
3. **IMPORTANTE**: Esta carpeta debe tener permisos de escritura

---

## 🔐 PASO 5: CREAR USUARIO ADMINISTRADOR

### 5.1 Verificar Usuario por Defecto
El sistema ya incluye un usuario administrador por defecto:
- **Usuario:** `admin`
- **Contraseña:** `admin123`

### 5.2 Cambiar Contraseña (Opcional pero Recomendado)
1. Accede a phpMyAdmin: `http://localhost/phpmyadmin`
2. Selecciona la base de datos `sircvig`
3. Ve a la tabla `usuarios`
4. Haz clic en **"Editar"** en el usuario `admin`
5. En `contrasena_hash`, calcula el hash SHA-256 de tu nueva contraseña
   - Puedes usar: https://emn178.github.io/online-tools/sha256.html
6. Guarda los cambios

---

## 🌐 PASO 6: ACCEDER AL SISTEMA

### 6.1 Abrir el Sistema
1. Abre tu navegador
2. Ve a: `http://localhost/sircvig`
3. Deberías ver la página de **Login**

### 6.2 Iniciar Sesión
1. Ingresa las credenciales:
   - **Usuario:** `admin`
   - **Contraseña:** `admin123`
2. Haz clic en **"Iniciar Sesión"**
3. Serás redirigido al **Dashboard**

---

## ✅ PASO 7: VERIFICAR FUNCIONALIDAD

### 7.1 Verificar Dashboard
- Deberías ver los indicadores (KPIs) del sistema
- Verifica que los números se muestren correctamente

### 7.2 Probar Módulos
1. **Gestión de Usuarios:**
   - Ve a "Gestión de Usuarios" en el menú lateral
   - Intenta crear un nuevo usuario

2. **Vigilantes:**
   - Ve a "Vigilantes" → "Nuevo Vigilante"
   - Completa el formulario y registra un vigilante de prueba

3. **Asignaciones:**
   - Ve a "Asignaciones" → "Nueva Asignación"
   - Crea una asignación de prueba

---

## 🛠️ SOLUCIÓN DE PROBLEMAS COMUNES

### ❌ Error: "No se puede conectar a la base de datos"
**Solución:**
1. Verifica que MySQL esté iniciado en XAMPP
2. Revisa las credenciales en `config/config.php`
3. Asegúrate de que la base de datos `sircvig` exista

### ❌ Error: "404 Not Found"
**Solución:**
1. Verifica que Apache esté iniciado
2. Asegúrate de que el proyecto esté en `C:\xampp\htdocs\sircvig\`
3. Verifica la URL: `http://localhost/sircvig`

### ❌ Error: "Permission denied" al subir archivos
**Solución:**
1. Verifica que la carpeta `uploads/vigilantes/` exista
2. En Windows, haz clic derecho → Propiedades → Seguridad
3. Asegúrate de que el usuario tenga permisos de escritura

### ❌ Error: "Class not found"
**Solución:**
1. Verifica que todos los archivos estén en sus carpetas correctas
2. Asegúrate de que `config/autoload.php` esté siendo incluido
3. Revisa que no haya errores de sintaxis en los archivos PHP

### ❌ Error: "Session already started"
**Solución:**
1. Verifica que no haya espacios antes de `<?php` en los archivos
2. Asegúrate de que `session_start()` solo se llame una vez

---

## 📚 PRÓXIMOS PASOS

### Crear Datos de Prueba
1. **Crear Puestos:**
   - Ve a phpMyAdmin
   - Inserta registros en la tabla `puestos`:
   ```sql
   INSERT INTO puestos (nombre_cliente, direccion, estatus) 
   VALUES ('Cliente Ejemplo', 'Dirección Ejemplo', 'Activo');
   ```

2. **Crear Vigilantes:**
   - Usa el formulario del sistema para registrar vigilantes
   - Sube documentos de prueba (fotos, cédulas, etc.)

3. **Crear Asignaciones:**
   - Asigna puestos a vigilantes
   - Prueba la detección de conflictos de horario

---

## 🔒 SEGURIDAD

### Recomendaciones Importantes:
1. **Cambiar contraseña por defecto** del usuario `admin`
2. **Configurar contraseña para MySQL** en producción
3. **No exponer** la carpeta `uploads` directamente
4. **Mantener XAMPP actualizado**
5. **Hacer respaldos** regulares de la base de datos

---

## 📞 SOPORTE

Si encuentras problemas:
1. Revisa los **logs de error** de Apache: `C:\xampp\apache\logs\error.log`
2. Revisa los **logs de MySQL**: `C:\xampp\mysql\data\mysql_error.log`
3. Verifica que todos los servicios de XAMPP estén funcionando

---

## ✅ CHECKLIST DE INSTALACIÓN

- [ ] XAMPP instalado y funcionando
- [ ] Apache iniciado (verde en el panel)
- [ ] MySQL iniciado (verde en el panel)
- [ ] Proyecto copiado en `C:\xampp\htdocs\sircvig\`
- [ ] Base de datos `sircvig` creada e importada
- [ ] Tablas creadas correctamente
- [ ] Configuración en `config.php` verificada
- [ ] Carpeta `uploads/vigilantes/` creada
- [ ] Acceso al sistema: `http://localhost/sircvig`
- [ ] Login exitoso con usuario `admin`
- [ ] Dashboard visible y funcional

---

**¡Felicidades! 🎉 Tu sistema SIRCVIG está listo para usar.**

