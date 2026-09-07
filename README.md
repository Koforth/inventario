# Manual Completo - Sistema de Inventario de una tienda de telefonos

Sistema web en Laravel para gestion comercial y operativa: catalogo, inventario, ventas, caja, compras, clientes, cotizaciones, apartados, creditos, comprobantes, taller y reportes.

## Tabla de contenido

1. [Resumen general](#resumen-general)
2. [Tecnologias](#tecnologias)
3. [Estructura del proyecto](#estructura-del-proyecto)
4. [Instalacion](#instalacion)
5. [Ejecucion](#ejecucion)
6. [Arquitectura de seguridad y acceso](#arquitectura-de-seguridad-y-acceso)
7. [Roles y permisos implementados](#roles-y-permisos-implementados)
8. [Modulo por modulo](#modulo-por-modulo)
9. [Comprobantes y correlativos](#comprobantes-y-correlativos)
10. [Mapa tecnico de rutas y middleware](#mapa-tecnico-de-rutas-y-middleware)
11. [Estado actual de RBAC](#estado-actual-de-rbac)
12. [Dashboard](#dashboard)
13. [Base de datos](#base-de-datos)
14. [Flujos operativos recomendados](#flujos-operativos-recomendados)
15. [Comandos utiles](#comandos-utiles)
16. [Notas operativas y soporte](#notas-operativas-y-soporte)

## Resumen general

El sistema permite:

- Gestionar productos con multiples imagenes, precio de compra y 3 precios de venta.
- Controlar inventario por entradas, salidas, kardex y movimientos historicos.
- Registrar ventas al contado y credito.
- Gestionar apartados con abonos y finalizacion de venta.
- Manejar caja diaria con apertura, cierre y movimientos.
- Realizar compras y seguimiento de compras al credito.
- Administrar clientes y limites crediticios.
- Configurar tipos de comprobante y correlativos (tiraje).
- Gestionar ordenes de taller y tecnicos.
- Consultar reportes operativos y exportacion.
- Administrar usuarios con roles y permisos.

## Tecnologias

- PHP 8.3+
- Laravel 13 (framework 13.6)
- MySQL/MariaDB
- Composer
- Node.js + npm
- Vite 8 + Tailwind CSS 4
- Bootstrap 5.3.3 + Bootstrap Icons 1.11.3 (CDN)
- Font Awesome 6.5.2 (CDN)
- Chart.js 4.4.4 (graficos del dashboard)
- Laravel Sanctum 4.3 (tokens de API)
- Spatie Laravel Permission 7.4 (instalado, no usado)

## Estructura del proyecto

Arbol de directorios principal:

```text
inventario/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Controller.php              # controlador base
│   │       ├── AuthController.php          # login/logout web
│   │       ├── HomeController.php          # dashboard (KPIs + graficos Chart.js)
│   │       ├── ProductController.php       # catalogo y CRUD de productos
│   │       ├── CategoryController.php      # categorias del almacen
│   │       ├── BrandController.php         # marcas
│   │       ├── PresentationController.php  # presentaciones
│   │       ├── WarehouseController.php     # almacen y perecederos
│   │       ├── InventoryController.php     # entradas y ventas
│   │       ├── KardexController.php        # kardex y movimientos
│   │       ├── SaleController.php          # impresion de venta (termica 80mm)
│   │       ├── LayawayController.php       # apartados y creditos
│   │       ├── CustomerController.php      # clientes
│   │       ├── QuoteController.php         # cotizaciones
│   │       ├── PurchaseController.php      # compras, creditos, historial de precios
│   │       ├── SupplierController.php      # proveedores
│   │       ├── CashRegisterController.php  # caja (apertura/cierre/movimientos)
│   │       ├── ReceiptTypeController.php   # comprobantes y correlativos
│   │       ├── TechnicianController.php    # tecnicos
│   │       ├── WorkshopController.php      # taller (ordenes de servicio)
│   │       ├── ReportController.php        # reportes y exportacion XLS
│   │       ├── UserManagementController.php
│   │       ├── RoleManagementController.php
│   │       ├── BackupController.php        # backup/restauracion de BD
│   │       └── Api/                        # API REST v1 (12 controladores)
│   │           ├── AuthController.php      # login/logout/me
│   │           ├── DashboardController.php
│   │           ├── ProductController.php
│   │           ├── CatalogController.php   # categorias, marcas, presentaciones, clientes, proveedores
│   │           ├── SaleController.php
│   │           ├── InventoryController.php # entries, kardex, stock
│   │           ├── PurchaseController.php
│   │           ├── LayawayController.php
│   │           ├── CashController.php
│   │           ├── ReportController.php    # 7 reportes
│   │           ├── UserController.php
│   │           └── BackupController.php
│   ├── Models/                             # 31 modelos Eloquent
│   │   ├── User.php                        # roles, sales; helpers hasPermission/hasRole
│   │   ├── Role.php / Permission.php       # RBAC propio
│   │   ├── Product.php                     # precio, imagenes, movimientos, proveedores
│   │   └── resto: catalogo, inventario, ventas, apartados, compras, caja, cotizaciones, taller
│   └── Providers/
│       └── AppServiceProvider.php          # Gates RBAC + bypass super-admin
├── bootstrap/
│   ├── app.php                             # configuracion de la app, statefulApi (Sanctum SPA)
│   └── providers.php
├── config/                                 # 10 archivos de configuracion estandar
├── database/
│   ├── factories/UserFactory.php
│   ├── migrations/                         # 26 migraciones (~30 tablas)
│   └── seeders/DatabaseSeeder.php          # 20 permisos, 8 roles, admin, datos base
├── docs/
│   └── REQUERIMIENTOS.md                   # analisis RF/RNF completo
├── public/
│   ├── index.php
│   ├── .htaccess
│   ├── favicon.svg
│   └── robots.txt
├── resources/
│   ├── css/app.css                         # Tailwind CSS 4 (@import)
│   ├── js/app.js                           # utilidades frontend (confirmar delete, SKU)
│   └── views/                              # 58 vistas Blade por modulo
│       ├── layout.blade.php                # layout principal (Bootstrap 5 + sidebar RBAC)
│       ├── layouts/app.blade.php           # layout secundario (Tailwind 4)
│       ├── auth/, products/, warehouse/, inventory/, sales/, customers/
│       ├── purchases/, quotes/, cash/, reports/, users/, roles/, backup/
│       └── home.blade.php, home-invite.blade.php, welcome.blade.php
├── routes/
│   ├── web.php                             # rutas web (grupos auth + can:<permiso>)
│   ├── api.php                             # API v1 (Sanctum)
│   └── console.php
├── storage/app/backups/                    # backups de BD generados
└── tests/
    ├── TestCase.php
    ├── Feature/                            # ApiFeatureTest, BackupAndDashboardTest,
    │                                       # RoleManagementTest, ExampleTest
    └── Unit/ExampleTest.php
```

Notas de arquitectura:

- El RBAC es **propio** (modelos `Role`/`Permission` + gates definidos en `AppServiceProvider`). El paquete `spatie/laravel-permission` esta instalado en `composer.json` pero **no se usa** en controladores ni modelos.
- No existen FormRequests ni middleware propios: toda la validacion es inline con `$request->validate()` y la autorizacion se hace con middleware `can:<permiso>` + `Gate::before` (bypass para `super-admin`).
- Doble frontend: el layout principal usa Bootstrap 5 via CDN; Vite/Tailwind 4 se usa en `resources/css/app.css` y el layout secundario.
- El `.htaccess` raiz redirige `/inventario` a `/inventario/public/` (despliegue WAMP en subcarpeta).

## Instalacion

```bash
cd c:\wamp64\www\inventario
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
```

Instalacion rapida con el script de Composer:

```bash
cd c:\wamp64\www\inventario
composer setup
```

El script `composer setup` instala dependencias, crea `.env` desde `.env.example` si no existe, genera la `APP_KEY`, ejecuta las migraciones, instala npm y compila los assets.

Configurar `.env`:

```env
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=America/Managua

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sisinventario
DB_USERNAME=root
DB_PASSWORD=
```

Aplicar cambios de configuracion:

```bash
php artisan config:clear
```

## Ejecucion

Backend:

```bash
php artisan serve
```

Frontend (Vite):

```bash
npm.cmd run dev
```

En PowerShell puede requerirse `npm.cmd` para evitar bloqueo de `npm.ps1`.

Script combinado `composer dev` (servidor + cola + logs + Vite):

```bash
composer dev
```

Despliegue con WAMP (Laravel en subcarpeta): el acceso es via `http://localhost/inventario/public/`; el `.htaccess` raiz ya redirige `/inventario` a `/inventario/public/`.

## Arquitectura de seguridad y acceso

El control de acceso esta implementado en capas:

1. **Menu / UI**: muestra modulos segun permisos.
2. **Middleware de rutas**: cada modulo usa `can:<permiso>`.
3. **Gate definitions**: permisos definidos en `AppServiceProvider`.
4. **Bypass super-admin**: `Gate::before` autoriza todo para `super-admin`.
5. **Controladores/rutas**: no depende solo del frontend.

Esto protege web/API interna frente a acceso directo por URL.

Adicionalmente:

- Los permisos se evalúan por `Gate::define` para cada modulo.
- `super-admin` tiene bypass total con `Gate::before`.
- El menu lateral usa `@can`, pero la proteccion real esta en rutas.

## Roles y permisos implementados

### Roles activos

- `super-admin`
- `administrador`
- `vendedor-cajero`
- `bodega-inventario`
- `tecnico`
- `contabilidad-caja`
- `gerencia`
- `invitado`

### Matriz resumida

#### 1) Super Admin

- Acceso total.
- Puede: usuarios, roles/permisos, configuracion, borrado, reportes completos.

#### 2) Administrador

- Home, Catalogo, Productos, Almacen, Clientes, Ventas, Caja, Compras, Cotizaciones, Tecnicos, Taller, Apartados, Kardex, Comprobantes.
- No: seguridad critica avanzada reservada a super-admin.

#### 3) Vendedor / Cajero

- Home, Clientes, Ventas, Caja, Cotizaciones, Apartados, Comprobantes.
- No: Usuarios, Compras, Kardex, Almacen, reportes de control interno.

#### 4) Bodega / Inventario

- Catalogo, Nuevo producto, Almacen, Entradas, Kardex, Compras.
- No: Caja, Usuarios, reportes financieros.

#### 5) Tecnico

- Tecnicos, Taller, Clientes, Comprobantes.
- Opcional organizacional: Apartados.
- No: Caja, Compras, Usuarios.

#### 6) Contabilidad / Caja

- Caja, Ventas, Compras, Comprobantes.
- No: Usuarios, Taller, configuracion sensible.

#### 7) Gerencia

- Ventas, Compras, Caja, Kardex, Clientes.
- Uso comun: lectura/supervision.

#### Invitado

- Se mantiene con acceso restringido y vista de bienvenida (sin dashboard operativo).

### Permisos tecnicos principales

- `catalog.view`
- `products.create`
- `products.manage`
- `warehouse.manage`
- `inventory.entries.manage`
- `kardex.manage`
- `customers.manage`
- `sales.manage`
- `cash.manage`
- `quotes.manage`
- `layaways.manage`
- `receipts.manage`
- `purchases.manage`
- `reports.manage`
- `technicians.manage`
- `workshop.manage`
- `users.manage`
- `roles.manage`
- `settings.manage`
- `records.delete`

## Mapa tecnico de rutas y middleware

Todas las rutas de negocio estan bajo `auth` y ademas en grupos `can:<permiso>`.

### Catalogo y productos

- `catalog.view`: `/products`, `/products/{product}`
- `products.create`: `/products/create`, `POST /products`
- `products.manage`: edicion/eliminacion de producto

### Inventario y almacen

- `warehouse.manage`: `/warehouse`, categorias, marcas, presentaciones, perecederos
- `inventory.entries.manage`: `/inventory/entries` (GET/POST)
- `kardex.manage`: `/inventory/kardex` y `POST /inventory/kardex/move`

### Ventas y clientes

- `customers.manage`: `/customers` (GET/POST/PUT)
- `sales.manage`: `/inventory/sales` y `POST /inventory/credit-sales/{sale}/payments`
- `layaways.manage`: `/inventory/layaways` + abonos/finalizacion
- `receipts.manage`: `/inventory/receipts` (tipos y correlativos)

### Caja, compras, cotizaciones, reportes

- `cash.manage`: `/cash` + apertura/cierre/movimientos
- `purchases.manage`: `/purchases`, `/suppliers`, creditos y abonos
- `quotes.manage`: `/quotes`
- `reports.manage`: `/reports`, `/reports/export`

### Taller y tecnicos

- `technicians.manage`: `/inventory/technicians`
- `workshop.manage`: `/inventory/workshop`

### Usuarios

- `users.manage`: `/users`, edicion de roles

## Estado actual de RBAC

### Implementado actualmente

- Modelo local de roles/permisos con tablas:
  - `roles`
  - `permissions`
  - `role_user`
  - `permission_role`
- `User::hasPermission()` consulta permisos via roles.
- Gates definidos en `AppServiceProvider`.
- Roles cargados/actualizados en `DatabaseSeeder`.

### Spatie Laravel Permission

- Paquete instalado en dependencias (`spatie/laravel-permission`).
- En esta version, la autorizacion activa del sistema sigue usando el modelo local existente.
- Si se desea migrar totalmente a Spatie (traits, tablas propias, middleware Spatie), se recomienda un plan de migracion controlado para no romper asignaciones actuales.

## Modulo por modulo

### Home / Bienvenida

- Ruta: `/home`
- Para la mayoria de roles muestra dashboard.
- Para `invitado` muestra pagina de bienvenida simple.

### Dashboard

Incluye:

- Ventas del dia (cantidad y monto).
- Estado de caja y flujo diario.
- Bajo stock y valor de inventario.
- Apartados pendientes y credito pendiente.
- Ventas recientes.
- Movimientos recientes.
- Proximos retiros de apartados.
- Resumen de taller y compras del mes.

### Catalogo

- Ruta: `/products`
- Muestra tarjetas con imagen, nombre y precio.
- Para roles de consulta usa modal carrusel.
- Para roles de gestion redirige a edicion.

### Productos

- Crear: `/products/create`
- Editar: `/products/{id}/edit`
- Campos:
  - SKU, barcode, nombre, descripcion
  - categoria, marca, presentacion
  - stock, stock minimo
  - precio compra
  - precio venta 1/2/3
  - impuestos, perecedero, inventariable
  - proveedores
  - fecha vencimiento
  - imagenes JPG/JPEG (hasta 8, max 2MB c/u)

### Almacen

- Ruta: `/warehouse`
- Gestion de:
  - categorias
  - marcas
  - presentaciones
  - productos perecederos `/warehouse/perishables`

### Entradas de inventario

- Ruta: `/inventory/entries`
- Registra entradas y aumenta stock.

### Kardex

- Ruta: `/inventory/kardex`
- Apertura de inventario automatica por periodo mensual.
- Consulta saldos/movimientos por producto y fechas.
- Registro de entrada/salida de almacen.
- Salida clasificada como `traslado` o `merma`.

### Clientes

- Ruta: `/customers`
- Tipo de cliente: `cliente` o `empresa`.
- Campos:
  - nombre unico (cliente o empresa)
  - dni, ruc
  - telefono, email
  - giro de negocio
  - limite crediticio
  - direccion
  - estado vigente/inactivo

### Ventas

- Ruta: `/inventory/sales`
- Carrito de venta rapida por nombre/SKU/barcode.
- Seleccion por producto:
  - cantidad
  - precio 1/2/3
  - descuento
- Cobro:
  - contado o credito
  - efectivo, tarjeta o mixto
  - cliente asociado
  - comprobante configurable
- Consulta de ventas:
  - del dia
  - por rango de fechas
  - por mes

### Apartados

- Ruta: `/inventory/layaways`
- Crear apartado con:
  - productos
  - cliente
  - fecha/hora de retiro
  - abono inicial
- Descuenta stock al apartar (reserva).
- Consultas:
  - apartados del dia
  - por rango
  - por mes
- Permite:
  - abonar
  - finalizar apartado
  - generar venta al completar saldo

### Ventas al credito

- Integrado en modulo de apartados.
- Lista ventas con saldo pendiente.
- Registra abonos y actualiza `credit_balance`.

### Caja

- Ruta: `/cash`
- Abrir caja (monto inicial).
- Cerrar caja (arqueo final).
- Movimientos manuales:
  - ingresos
  - devoluciones
  - prestamos
  - gastos
- Registra ingresos de ventas y abonos.

### Cotizaciones

- Ruta: `/quotes`
- Crear, listar, ver detalle.
- Calcula subtotal, impuesto y total.

### Compras

- Ruta: `/purchases`
- Crear compra contado/credito.
- Registra items y costos.
- Aumenta stock de productos inventariables.
- Historial de precios.
- Compras a credito y abonos.
- Proveedores: `/suppliers`

### Comprobantes

- Ruta: `/inventory/receipts`
- Configura tipos: FACTURA, BOLETA, TICKET, etc.
- Configura tiraje:
  - prefijo
  - correlativo actual
  - longitud (padding)
- En venta se usa `receipt_type_id` y se genera `receipt_number` correlativo.

### Tecnicos

- Ruta: `/inventory/technicians`
- Lista y registro de tecnicos:
  - nombre
  - telefono
  - correo
  - especialidad
  - estado

### Taller (ordenes de servicio)

- Ruta: `/inventory/workshop`
- Registro de orden considerando:
  - cliente
  - tecnico asignado
  - aparato
  - marca
  - modelo
  - serie
  - averia
  - observaciones
  - costo del servicio

### Reportes

- Ruta: `/reports`, todas bajo permiso `reports.manage`.
- Incluye (5 reportes web + exportacion XLS):
  - **Movimientos** (entradas, mermas, traslados, bajo stock y valor de inventario) - `/reports`
  - **Ventas por periodo** (maestro-detalle) - `/reports/sales`
  - **Compras por periodo** (maestro-detalle) - `/reports/purchases`
  - **Clientes morosos** (saldo al credito) - `/reports/debtors`
  - **Catalogo maestro-detalle** - `/reports/catalog`
- Exportacion a Excel disponible en todos los reportes: `/reports/export`, `/reports/sales/export`, `/reports/purchases/export`, `/reports/debtors/export`, `/reports/catalog/export`.
- Via API se agregan `reports/low-stock` y `reports/top-selling`.
- Acceso restringido por permiso `reports.manage` (operativamente asignado a `super-admin`).

### Factura imprimible

- Cada venta registrada tiene un boton de impresion (<i class="bi bi-printer"></i>) en las tablas de ventas recientes y consulta detallada.
- La factura/boleta/ticket se genera en formato termico 80mm (`/sales/{sale}/print`).
- Incluye datos de la tienda, comprobante, cliente, items, totales y formas de pago.

### Backup y restauracion

- Ruta: `/backup` (permiso `settings.manage`, rol `administrador`/`super-admin`).
- Genera copias de seguridad `.sql` completas de la base de datos.
- Permite descargar, restaurar (archivo subido o backup del servidor) y eliminar backups.
- Almacenamiento en `storage/app/backups`.
- Fallback con dump via PHP si `mysqldump` no esta disponible.

### API REST

- Prefijo base: `/api/v1`, autenticacion con Laravel Sanctum (SPA stateful + tokens `Bearer`).
- Implementacion: 12 controladores en `app/Http/Controllers/Api/`.
- Autenticacion:
  - `POST /api/v1/login` - obtener token (publico)
  - `POST /api/v1/logout` - invalidar sesion/token
  - `GET /api/v1/me` - datos del usuario autenticado
- Dashboard: `GET /api/v1/dashboard` - estadisticas y graficos.
- Productos: `GET/POST /api/v1/products`, `GET/PUT/DELETE /api/v1/products/{product}` (crear exige `products.create`, editar `products.manage`, eliminar `records.delete`).
- Catalogos:
  - Categorias, marcas y presentaciones: `GET/POST/PUT/DELETE` (escritura con `warehouse.manage`, borrado con `records.delete`).
  - Clientes: `GET/POST/PUT /api/v1/customers`.
  - Proveedores: `GET/POST/PUT/DELETE /api/v1/suppliers`.
- Ventas: `GET/POST /api/v1/sales`, `GET /api/v1/sales/{sale}`, `POST /api/v1/sales/{sale}/payments` (abonos al credito).
- Inventario: `GET/POST /api/v1/inventory/entries`, `GET /api/v1/inventory/kardex`, `GET /api/v1/inventory/stock`.
- Compras: `GET/POST /api/v1/purchases`, `GET /api/v1/purchases/{purchase}`.
- Apartados: `GET/POST /api/v1/layaways`, `POST /api/v1/layaways/{layaway}/payments`, `POST /api/v1/layaways/{layaway}/complete`.
- Caja: `GET /api/v1/cash`, `POST /api/v1/cash/open`, `PUT /api/v1/cash/{cashRegister}/close`, `POST /api/v1/cash/{cashRegister}/movements`.
- Reportes (7): `GET /api/v1/reports/movements|sales|purchases|debtors|catalog|low-stock|top-selling`.
- Usuarios: `GET/POST /api/v1/users`, `PUT /api/v1/users/{user}`.
- Backup: `POST /api/v1/backup/create`, `GET /api/v1/backup/status`, `GET /api/v1/backup/{file}/download`, `POST /api/v1/backup/{file}/restore`, `DELETE /api/v1/backup/{file}`.
- Todo endpoint protegido requiere header `Authorization: Bearer <token>` excepto login.
- Las rutas respetan los permisos RBAC del usuario mediante middleware `can:<permiso>`.

### Graficos del dashboard

- El dashboard (`/home`) ahora muestra 3 graficos con Chart.js:
  - Ventas de los ultimos 7 dias (lineas)
  - Productos por categoria (dona)
  - Top 5 mas vendidos (barras)
- Los datos se cargan desde la base de datos en `HomeController` y via API en `Api\DashboardController`.

### Pruebas automatizadas

- `tests/Feature/ApiFeatureTest.php`: login API, tokens/credenciales, CRUD de categorias, dashboard, reportes y backup por API.
- `tests/Feature/BackupAndDashboardTest.php`: renderizado de dashboard, backup y reportes web.
- `tests/Feature/RoleManagementTest.php` (8 tests): CRUD de roles, asignacion de permisos, protecciones (`super-admin` ineliminable, roles con usuarios) y acceso por permiso.
- `tests/Feature/ExampleTest.php` y `tests/Unit/ExampleTest.php`: pruebas base.
- Configuracion: SQLite en memoria (ver `phpunit.xml`).
- Ejecutar con `php artisan test` o `composer test`.

### Requerimientos

- Documento de analisis completo en `docs/REQUERIMIENTOS.md` con 19 funcionalidades y 10 requerimientos no funcionales.

### Usuarios

- Ruta: `/users`
- Gestion de usuarios y asignacion de roles.
- Restriccion: usuario autenticado no se autoedita.
- La seleccion de roles en crear/editar usuario esta organizada en cards colapsables:
  - Roles por modulo
  - Roles especiales
  - Roles con mas de un permiso
  - Roles con un solo permiso
- Las cards usan icono colapsable tipo `plus/minus`.

### Roles

- Ruta: `/roles` (permiso `roles.manage`, solo `super-admin` por defecto).
- Gestion directa de roles: crear, editar y eliminar.
- Al crear/editar un rol se asignan, modifican o revocan sus permisos mediante checkboxes agrupados:
  - Permisos por modulo (una card por gestion de cada modulo)
  - Permisos especiales (ver catalogo, crear productos, roles sensibles, settings, eliminar registros)
- Validaciones: identificador `name` unico en minusculas/numeros/guiones, label requerido, permisos existentes en BD.
- Protecciones: el rol `super-admin` no puede eliminarse, y no se eliminan roles con usuarios asignados.
- Solo un usuario con permiso `roles.manage` puede modificar los permisos del rol protegido `super-admin`.

## Comprobantes y correlativos

### Tablas clave

- `receipt_types`
  - `name`
  - `code`
  - `prefix`
  - `current_number`
  - `padding`
  - `is_active`

- `sales`
  - `receipt_type_id`
  - `receipt_number`

### Regla de correlativo

1. Se bloquea el tipo de comprobante para concurrencia segura.
2. Se calcula siguiente numero (`current_number + 1`).
3. Se arma `prefijo + numero con padding`.
4. Se guarda en la venta.
5. Se incrementa `current_number`.

### Numeracion de compras y cotizaciones (concurrencia)

- `purchases.number` y `quotes.number` tienen restriccion unica en base de datos.
- La generacion usa estrategia atomica:
  - transaccion DB
  - lectura del ultimo correlativo del dia con `lockForUpdate()`
  - incremento secuencial seguro
- Se aplica reintento ante colision unica para tolerar concurrencia alta.

## Base de datos

Tablas principales (~30, via 26 migraciones en `database/migrations`):

- Seguridad: `users`, `roles`, `permissions`, `role_user`, `permission_role`
- Catalogo: `products`, `product_images`, `product_prices`, `categories`, `brands`, `presentations`, `suppliers`, `product_supplier`
- Inventario: `movements`, `inventory_periods`
- Ventas: `sales`, `sale_items`, `sale_payments`
- Clientes: `customers`
- Apartados: `layaways`, `layaway_items`, `layaway_payments`
- Compras: `purchases`, `purchase_items`, `purchase_payments`
- Caja: `cash_registers`, `cash_movements`
- Cotizaciones: `quotes`, `quote_items`
- Taller: `technicians`, `workshop_orders`
- Comprobantes: `receipt_types`
- Infraestructura: `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `personal_access_tokens` (Sanctum)

Seeder (`database/seeders/DatabaseSeeder.php`):

- 20 permisos, 8 roles y usuario admin (`ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD` desde `.env`).
- Datos base: 5 categorias, 4 marcas y 3 tipos de comprobante (FACTURA/F001, BOLETA/B001, TICKET/T001).

## Flujos operativos recomendados

### Flujo 1 - Venta contado

1. Abrir caja.
2. Ir a ventas.
3. Agregar productos.
4. Seleccionar comprobante.
5. Cobrar y guardar.

### Flujo 2 - Venta credito

1. Registrar cliente con limite.
2. Procesar venta en modo credito.
3. Registrar abonos en modulo de apartados/credito.

### Flujo 3 - Apartado

1. Crear apartado con abono inicial.
2. Consultar por fecha de retiro.
3. Completar abonos.
4. Finalizar apartado para convertir a venta.

### Flujo 4 - Inventario

1. Registrar entradas.
2. Revisar kardex y salidas.
3. Auditar bajo stock en dashboard/reportes.

## Comandos utiles

Migraciones:

```bash
php artisan migrate
```

Migrar limpio + seed:

```bash
php artisan migrate:fresh --seed
```

Seeders:

```bash
php artisan db:seed
```

Cache:

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

Rutas:

```bash
php artisan route:list
```

Pruebas:

```bash
php artisan test
```

Build frontend:

```bash
npm.cmd run build
```

Scripts de Composer:

- `composer setup`: instalacion completa (deps, .env, key, migraciones, npm y build).
- `composer dev`: servidor + cola + logs + Vite en paralelo.
- `composer test`: limpia config y ejecuta la suite de pruebas.

## Notas operativas y soporte

- Mantener `APP_TIMEZONE=America/Managua`.
- Despliegue WAMP en subcarpeta: acceder via `http://localhost/inventario/public/` (el `.htaccess` raiz redirige automaticamente).
- `SESSION_DRIVER`, `QUEUE_CONNECTION` y `CACHE_STORE` usan la base de datos, por lo que las tablas correspondientes deben estar migradas.
- Verificar caja abierta antes de ventas/abonos.
- Revisar tipos de comprobante activos antes de vender.
- Ajustar correlativos en `Comprobantes` al inicio de operaciones.
- Asignar roles segun perfil real del usuario, no por menu.
- La seguridad esta en backend por middleware y gates, no solo por interfaz.
