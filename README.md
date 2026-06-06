# SIGADEA PHP

Version web independiente de SIGADEA desarrollada en PHP 8+, MySQL/MariaDB, HTML5, CSS3, JavaScript y Bootstrap 5.

## Modulos replicados

- Inicio de sesion con usuarios existentes y control por rol.
- Panel administrador.
- Control de usuarios: agregar, modificar, eliminar y buscar.
- Panel docente.
- Registro y edicion de alumnos.
- Carga de documentos PDF: acta, certificado, comprobante de domicilio y CURP.
- Estados visuales de documento cargado / documento aun no cargado.
- Busqueda y filtros de alumnos por documentos faltantes, entregados y reporte general.
- Reportes PDF.
- Configuracion semanal de copias de seguridad.
- Backups en carpetas por mes.

## Base de datos

El archivo `database.sql` crea:

- Base de datos `sigadea_php`.
- Tabla `usuarios`.
- Tabla `status` para alumnos y rutas de documentos.
- Tabla `backup_settings` como referencia relacional de configuracion.
- Usuario inicial:
  - Usuario: `admin`
  - Contrasena: `admin`

## Instalacion en XAMPP

1. Copia la carpeta `SIGADEA_PHP` dentro de:

   `C:\xampp\htdocs\`

2. Abre phpMyAdmin o MySQL Workbench.

3. Ejecuta completo el archivo:

   `database.sql`

4. Revisa credenciales de conexion en:

   `config/config.php`

   Valores por defecto:

   ```php
   DB_HOST = 127.0.0.1
   DB_NAME = sigadea_php
   DB_USER = root
   DB_PASS = ''
   ```

5. Abre en el navegador:

   `http://localhost/SIGADEA_PHP/`

## Copias de seguridad automaticas

Desde el sistema, entra como administrador a `Copias de seguridad`, selecciona dia y hora entre 8:00 AM y 2:00 PM y presiona `Aplicar`.

El sistema revisa la configuracion mientras se navega. Para una ejecucion realmente automatica aunque nadie este usando el sistema, programa una tarea de Windows que ejecute:

```bat
C:\xampp\php\php.exe C:\xampp\htdocs\SIGADEA_PHP\cron_backup.php
```

Puedes programarla para ejecutarse cada 5 minutos. El script solo crea backup cuando coincide el dia y hora configurados y evita duplicados el mismo dia.

Los backups se guardan en:

`storage/backups/Mes_Ano/`

Ejemplo:

`storage/backups/Mayo_2026/SIGADEA_Backup_Programado_20260531_130000.zip`

## Seguridad incluida

- Consultas preparadas con PDO.
- Hash de contrasenas con `password_hash`.
- Sesiones seguras con `session_regenerate_id`.
- Control de acceso por rol.
- Token CSRF en formularios y acciones sensibles.
- Validacion de datos en servidor.
- Sanitizacion de salida HTML con `htmlspecialchars`.
- Carga restringida a documentos PDF.

## Notas de compatibilidad

La funcion de escaner WIA de la app WinForms no existe de forma nativa en navegadores por seguridad. En esta version web se conserva el flujo funcional mediante carga de PDF desde el dispositivo. Para digitalizar, se escanea primero con el software del escaner y luego se carga el PDF.

