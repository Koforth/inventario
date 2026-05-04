# Manual Completo - Sistema de Inventario

Sistema web en Laravel para gestion comercial y operativa: catalogo, inventario, ventas, caja, compras, clientes, cotizaciones, apartados, credito, comprobantes, taller y reportes.

## Tabla de contenido

1. [Resumen general](#resumen-general)
2. [Tecnologias](#tecnologias)
3. [Instalacion](#instalacion)
4. [Ejecucion](#ejecucion)
5. [Arquitectura de seguridad y acceso](#arquitectura-de-seguridad-y-acceso)
6. [Roles y permisos implementados](#roles-y-permisos-implementados)
7. [Modulo por modulo](#modulo-por-modulo)
8. [Comprobantes y correlativos](#comprobantes-y-correlativos)
9. [Mapa tecnico de rutas y middleware](#mapa-tecnico-de-rutas-y-middleware)
10. [Estado actual de RBAC](#estado-actual-de-rbac)
11. [Dashboard](#dashboard)
12. [Base de datos](#base-de-datos)
13. [Flujos operativos recomendados](#flujos-operativos-recomendados)
14. [Comandos utiles](#comandos-utiles)
15. [Notas operativas y soporte](#notas-operativas-y-soporte)

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
- Laravel 13
- MySQL/MariaDB
- Composer
- Node.js + npm
- Vite
- Bootstrap 5
- Bootstrap Icons
- Spatie Laravel Permission (instalado)

## Instalacion

```bash
cd c:\laragon\www\inventario
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
```

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

- Ruta: `/reports`
- Incluye:
  - movimientos (cualquier tipo registrado en base de datos)
  - ventas
  - entradas
  - mermas
  - traslados
  - bajo stock
  - valor inventario
- Exportacion disponible.
- Acceso restringido por permiso `reports.manage` (operativamente asignado a `super-admin`).

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

Tablas principales:

- Seguridad: `users`, `roles`, `permissions`, `role_user`, `permission_role`
- Catalogo: `products`, `product_images`, `categories`, `brands`, `presentations`, `suppliers`, `product_supplier`
- Inventario: `movements`, `inventory_periods`
- Ventas: `sales`, `sale_items`, `sale_payments`
- Clientes: `customers`
- Apartados: `layaways`, `layaway_items`, `layaway_payments`
- Compras: `purchases`, `purchase_items`, `purchase_payments`
- Caja: `cash_registers`, `cash_movements`
- Cotizaciones: `quotes`, `quote_items`
- Taller: `technicians`, `workshop_orders`
- Comprobantes: `receipt_types`

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

## Notas operativas y soporte

- Mantener `APP_TIMEZONE=America/Managua`.
- Verificar caja abierta antes de ventas/abonos.
- Revisar tipos de comprobante activos antes de vender.
- Ajustar correlativos en `Comprobantes` al inicio de operaciones.
- Asignar roles segun perfil real del usuario, no por menu.
- La seguridad esta en backend por middleware y gates, no solo por interfaz.
