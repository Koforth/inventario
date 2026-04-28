# Manual del Sistema de Inventario

Sistema web desarrollado con Laravel para controlar productos del hogar, existencias, entradas, ventas, usuarios y reportes de movimientos.

## Tabla de Contenido

- [Descripcion general](#descripcion-general)
- [Tecnologias utilizadas](#tecnologias-utilizadas)
- [Requisitos](#requisitos)
- [Instalacion](#instalacion)
- [Configuracion del entorno](#configuracion-del-entorno)
- [Ejecucion del proyecto](#ejecucion-del-proyecto)
- [Usuarios y roles](#usuarios-y-roles)
- [Manual de uso](#manual-de-uso)
- [Estructura del proyecto](#estructura-del-proyecto)
- [Base de datos](#base-de-datos)
- [Comandos utiles](#comandos-utiles)
- [Mantenimiento y recomendaciones](#mantenimiento-y-recomendaciones)
- [Solucion de problemas](#solucion-de-problemas)

## Descripcion General

El proyecto es una aplicacion de inventario para registrar productos, consultar existencias, controlar entradas y ventas, administrar usuarios y revisar reportes operativos.

La aplicacion trabaja con autenticacion, roles y permisos. Cada usuario ve solamente las opciones que corresponden a su rol.

Funciones principales:

- Inicio de sesion y registro de usuarios.
- Catalogo de productos con busqueda y filtros.
- Creacion, edicion y eliminacion de productos.
- Carga de imagen JPG/JPEG por producto.
- Control de stock minimo y alerta de bajo stock.
- Registro de entradas de inventario.
- Procesamiento de ventas con descuento automatico de stock.
- Reportes de movimientos, ventas, entradas, mermas, traslados y productos mas vendidos.
- Administracion de roles de usuario.

## Tecnologias Utilizadas

- PHP 8.3 o superior.
- Laravel 13.
- MySQL, MariaDB o SQLite, segun la configuracion del archivo `.env`.
- Composer para dependencias PHP.
- Node.js y npm para assets frontend.
- Vite.
- Bootstrap 5 en las vistas principales.
- Tailwind CSS disponible en la configuracion de assets.

## Requisitos

Antes de ejecutar el proyecto, asegurese de tener instalado:

- PHP 8.3 o superior.
- Composer.
- Node.js y npm.
- Servidor de base de datos si usara MySQL o MariaDB.
- Laragon, XAMPP, Herd, Valet o un entorno equivalente.

En este equipo el proyecto esta ubicado en:

```text
c:\laragon\www\inventario
```

## Instalacion

1. Entrar a la carpeta del proyecto:

```bash
cd c:\laragon\www\inventario
```

2. Instalar dependencias PHP:

```bash
composer install
```

3. Instalar dependencias JavaScript:

```bash
npm install
```

4. Crear el archivo de entorno si no existe:

```bash
copy .env.example .env
```

5. Generar la llave de la aplicacion:

```bash
php artisan key:generate
```

6. Ejecutar migraciones:

```bash
php artisan migrate
```

7. Cargar datos iniciales:

```bash
php artisan db:seed
```

8. Crear el enlace publico para imagenes de productos:

```bash
php artisan storage:link
```

## Configuracion del Entorno

La configuracion principal se encuentra en el archivo `.env`.

Variables importantes:

```env
APP_NAME="Sistema de Inventario"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://inventario.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventario
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=local
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

Si se usa SQLite, configure:

```env
DB_CONNECTION=sqlite
```

Y cree el archivo:

```bash
type nul > database\database.sqlite
```

Despues de cambiar variables del `.env`, limpie la cache de configuracion:

```bash
php artisan config:clear
```

## Ejecucion del Proyecto

Para desarrollo, puede levantar Laravel y Vite por separado.

Servidor Laravel:

```bash
php artisan serve
```

Servidor Vite:

```bash
npm run dev
```

Tambien existe un script que ejecuta servidor, cola, logs y Vite al mismo tiempo:

```bash
composer run dev
```

Para compilar assets para produccion:

```bash
npm run build
```

## Usuarios y Roles

El sistema maneja tres roles:

| Rol | Permisos principales |
| --- | --- |
| `admin` | Administra productos, usuarios y reportes. |
| `empleado` | Registra entradas y procesa ventas. |
| `invitado` | Acceso basico al sistema despues del registro. |

El seeder crea un usuario administrador inicial:

```text
Correo: admin@example.com
Clave: password
Rol: admin
```

Por seguridad, cambie esta clave antes de usar el sistema en un entorno real.

Los usuarios registrados desde la pantalla de registro reciben inicialmente el rol `invitado`. Un administrador debe cambiar el rol desde el modulo de usuarios.

## Manual de Uso

### 1. Inicio de Sesion

Ruta:

```text
/login
```

Ingrese correo y clave. Si las credenciales son correctas, el sistema redirige al panel principal.

### 2. Registro de Usuarios

Ruta:

```text
/register
```

Campos requeridos:

- Nombre.
- Correo unico.
- Clave de al menos 8 caracteres.
- Confirmacion de clave.

El usuario queda registrado como `invitado`.

### 3. Panel Principal

Ruta:

```text
/home
```

Muestra un resumen de la sesion actual y accesos al catalogo. Las opciones visibles dependen del rol del usuario.

### 4. Catalogo de Productos

Ruta:

```text
/products
```

Disponible para usuarios autenticados.

Permite:

- Ver productos registrados.
- Buscar por SKU, codigo de barras, nombre, marca, categoria o proveedor.
- Filtrar productos con bajo stock.
- Ver estadisticas generales:
  - Productos registrados.
  - Productos con bajo stock.
  - Valor total del inventario.

Un producto se considera con bajo stock cuando:

```text
stock <= stock_minimo
```

### 5. Crear Producto

Ruta:

```text
/products/create
```

Disponible solo para `admin`.

Campos del producto:

- SKU obligatorio y unico.
- Codigo de barras opcional y unico.
- Nombre obligatorio.
- Categoria obligatoria.
- Marca obligatoria.
- Stock obligatorio.
- Stock minimo obligatorio.
- Precio obligatorio.
- Proveedor opcional.
- Descripcion opcional.
- Imagen opcional en formato JPG/JPEG, maximo 2 MB.

La imagen se guarda en el disco publico de Laravel. Para verla desde el navegador debe existir el enlace creado con:

```bash
php artisan storage:link
```

### 6. Editar Producto

Ruta:

```text
/products/{producto}/edit
```

Disponible solo para `admin`.

Permite actualizar los datos del producto. Si se sube una nueva imagen, el sistema elimina la imagen anterior del disco publico.

### 7. Eliminar Producto

Disponible solo para `admin`.

Al eliminar un producto:

- Se borra el registro del producto.
- Se elimina su imagen asociada, si existe.
- Los movimientos relacionados se eliminan por la relacion configurada en base de datos.

### 8. Ver Detalle de Producto

Ruta:

```text
/products/{producto}
```

Disponible para usuarios autenticados.

Muestra la informacion individual del producto.

### 9. Entradas de Inventario

Ruta:

```text
/inventory/entries
```

Disponible solo para `empleado`.

Permite registrar productos que ingresan al stock.

Funcionamiento:

1. Buscar el producto.
2. Indicar la cantidad.
3. Presionar `Agregar`.
4. El sistema aumenta el stock.
5. Se registra un movimiento de tipo `entrada`.

El registro se realiza dentro de una transaccion de base de datos para evitar inconsistencias.

### 10. Ventas

Ruta:

```text
/inventory/sales
```

Disponible solo para `empleado`.

Permite procesar ventas y descontar existencias.

Funcionamiento:

1. Buscar el producto disponible.
2. Indicar la cantidad vendida.
3. Presionar `Vender`.
4. El sistema valida que exista stock suficiente.
5. Se descuenta el stock.
6. Se registra un movimiento de tipo `venta`.

Si no hay stock suficiente, el sistema muestra un error y no descuenta unidades.

### 11. Reportes

Ruta:

```text
/reports
```

Disponible solo para `admin`.

Incluye:

- Total de movimientos segun filtros.
- Unidades vendidas.
- Valor de ventas.
- Entradas registradas.
- Productos con bajo stock.
- Valor total del inventario.
- Mermas.
- Traslados.
- Ranking de productos mas vendidos.

Filtros disponibles:

- Tipo de movimiento: todos, entrada, venta, merma o traslado.
- Fecha desde.
- Fecha hasta.
- Busqueda por producto, SKU, codigo o usuario.

Nota: actualmente el sistema registra desde la interfaz entradas y ventas. Los tipos `merma` y `traslado` existen en la base de datos y aparecen en reportes, pero no tienen pantalla propia de registro.

### 12. Administracion de Usuarios

Ruta:

```text
/users
```

Disponible solo para `admin`.

Permite:

- Buscar usuarios por nombre, correo o rol.
- Ver el rol actual.
- Cambiar el rol de otros usuarios.

Restriccion importante:

- Un administrador no puede cambiar su propio rol mientras tiene la sesion iniciada.

## Estructura del Proyecto

Carpetas y archivos principales:

```text
app/
  Http/
    Controllers/
      AuthController.php
      ProductController.php
      InventoryController.php
      ReportController.php
      UserManagementController.php
    Middleware/
      EnsureUserHasRole.php
  Models/
    Brand.php
    Category.php
    Movement.php
    Product.php
    User.php

database/
  migrations/
  seeders/

resources/
  views/
    auth/
    inventory/
    products/
    reports/
    users/
    layout.blade.php

routes/
  web.php

public/
storage/
```

Controladores principales:

| Controlador | Responsabilidad |
| --- | --- |
| `AuthController` | Login, registro y cierre de sesion. |
| `ProductController` | Catalogo, creacion, edicion, detalle y eliminacion de productos. |
| `InventoryController` | Entradas y ventas de inventario. |
| `ReportController` | Reportes, filtros y metricas. |
| `UserManagementController` | Listado y cambio de roles de usuarios. |

Modelos principales:

| Modelo | Descripcion |
| --- | --- |
| `User` | Usuarios autenticados y su rol. |
| `Product` | Productos, precios, stock, categoria, marca e imagen. |
| `Movement` | Movimientos de inventario. |
| `Category` | Categorias de producto. |
| `Brand` | Marcas de producto. |

## Base de Datos

Tablas principales:

### `users`

Guarda usuarios del sistema.

Campos relevantes:

- `name`
- `email`
- `password`
- `role`

### `categories`

Guarda categorias de productos.

Campos relevantes:

- `nombre`
- `descripcion`

### `brands`

Guarda marcas.

Campos relevantes:

- `nombre`

### `products`

Guarda productos del inventario.

Campos relevantes:

- `sku`
- `barcode`
- `nombre`
- `descripcion`
- `image_path`
- `precio`
- `stock`
- `stock_minimo`
- `proveedor`
- `category_id`
- `brand_id`

### `movements`

Guarda entradas, ventas y otros tipos de movimiento.

Campos relevantes:

- `product_id`
- `tipo`: `entrada`, `venta`, `merma`, `traslado`
- `cantidad`
- `user_id`

Relaciones principales:

- Un producto pertenece a una categoria.
- Un producto pertenece a una marca.
- Un producto tiene muchos movimientos.
- Un movimiento pertenece a un producto.
- Un movimiento pertenece a un usuario.

## Comandos Utiles

Ejecutar migraciones:

```bash
php artisan migrate
```

Recrear la base de datos y cargar seeders:

```bash
php artisan migrate:fresh --seed
```

Cargar seeders sin borrar tablas:

```bash
php artisan db:seed
```

Limpiar cache:

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

Ver rutas registradas:

```bash
php artisan route:list
```

Ejecutar pruebas:

```bash
php artisan test
```

Formatear codigo PHP con Pint:

```bash
vendor\bin\pint
```

Compilar frontend:

```bash
npm run build
```

## Mantenimiento y Recomendaciones

- Cambiar la clave del usuario `admin@example.com` despues de instalar.
- No subir el archivo `.env` a repositorios publicos.
- Crear respaldos periodicos de la base de datos.
- Ejecutar `php artisan storage:link` despues de desplegar para mostrar imagenes.
- Mantener actualizado Composer y npm.
- Revisar productos con bajo stock desde catalogo y reportes.
- Asignar roles correctos a usuarios nuevos antes de permitir operaciones.
- Validar que el servidor tenga permisos de escritura en `storage/` y `bootstrap/cache/`.

## Solucion de Problemas

### No cargan las imagenes de productos

Ejecute:

```bash
php artisan storage:link
```

Verifique que el archivo exista dentro de:

```text
storage/app/public/products
```

### Error de permisos en `storage` o `bootstrap/cache`

Revise que el servidor web pueda escribir en:

```text
storage/
bootstrap/cache/
```

### Cambios del `.env` no se reflejan

Ejecute:

```bash
php artisan config:clear
```

### Error de base de datos

Revise en `.env`:

```env
DB_CONNECTION
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
```

Luego ejecute:

```bash
php artisan migrate
```

### No aparece una opcion del menu

Revise el rol del usuario:

- `admin` ve productos, usuarios y reportes.
- `empleado` ve entradas y ventas.
- `invitado` tiene acceso limitado.

### No se puede vender un producto

Verifique:

- Que el producto tenga stock mayor que cero.
- Que la cantidad vendida no supere el stock actual.
- Que el usuario tenga rol `empleado`.

## Estado Actual del Proyecto

El proyecto ya cuenta con los modulos principales para operar inventario:

- Autenticacion.
- Roles.
- Productos.
- Entradas.
- Ventas.
- Reportes.
- Usuarios.
- Imagenes de productos.

Mejoras sugeridas para futuras versiones:

- Pantallas para registrar mermas y traslados.
- Exportacion de reportes a PDF o Excel.
- Historial detallado dentro del perfil de cada producto.
- Recuperacion de contrasena.
- Auditoria avanzada de cambios.
- Dashboard con graficos.
