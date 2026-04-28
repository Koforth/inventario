# Manual del Sistema de Inventario

Sistema web en Laravel para administrar inventario, almacen, ventas, caja, compras, cotizaciones, usuarios, roles, permisos y reportes.

## Tabla de Contenido

- [Descripcion general](#descripcion-general)
- [Tecnologias](#tecnologias)
- [Instalacion](#instalacion)
- [Ejecucion](#ejecucion)
- [Usuarios, roles y permisos](#usuarios-roles-y-permisos)
- [Modulos del sistema](#modulos-del-sistema)
- [Base de datos](#base-de-datos)
- [Comandos utiles](#comandos-utiles)
- [Notas operativas](#notas-operativas)

## Descripcion General

La aplicacion permite controlar productos, existencias, entradas, ventas, compras, proveedores, caja diaria, cotizaciones y reportes operativos.

Funciones principales:

- Autenticacion de usuarios.
- Roles multiples por usuario.
- Permisos asociados a roles.
- Catalogo de productos en grilla.
- Modal carrusel para usuarios invitados/empleados.
- Edicion directa para administradores.
- Multiples imagenes JPG/JPEG por producto.
- Proveedores multiples por producto.
- Modulo Almacen.
- Modulo Compras.
- Modulo Caja.
- Modulo Cotizaciones.
- Reportes con exportacion Excel.
- Breadcrumb global e iconos por modulo.

## Tecnologias

- PHP 8.3 o superior.
- Laravel 13.
- MySQL/MariaDB.
- Composer.
- Node.js y npm.
- Vite.
- Bootstrap 5.
- Bootstrap Icons.

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

Configurar `.env` segun el entorno:

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

Despues de cambiar `.env`:

```bash
php artisan config:clear
```

## Ejecucion

Servidor Laravel:

```bash
php artisan serve
```

Servidor Vite:

```bash
npm.cmd run dev
```

En PowerShell puede ser necesario usar `npm.cmd` si la politica de ejecucion bloquea `npm.ps1`.

## Usuarios, Roles y Permisos

El sistema ya no valida por un unico campo `role`; ahora usa roles multiples y permisos.

Usuario inicial:

```text
Correo: admin@example.com
Clave: password
```

Roles iniciales:

| Rol | Permisos principales |
| --- | --- |
| Administrador | Acceso completo. |
| Empleado | Inventario, ventas, compras, caja y cotizaciones. |
| Agregar productos | Catalogo y creacion de productos. |
| Invitado | Catalogo basico. |

Permisos registrados:

- `view-catalog`
- `create-products`
- `manage-products`
- `manage-inventory`
- `manage-quotes`
- `manage-purchases`
- `manage-cash`
- `view-reports`
- `manage-users`

Notas:

- Un usuario puede tener varios roles.
- Las rutas validan permisos, no nombres de rol.
- En el topbar se muestra solo el rol de mayor privilegio.
- Si el usuario solo tiene rol Invitado, no se muestra rol en topbar ni en la tarjeta de sesion.

## Modulos del Sistema

### Home

Ruta:

```text
/home
```

Muestra la sesion activa y accesos principales segun permisos.

### Catalogo

Ruta:

```text
/products
```

Permite ver productos en grilla minimalista. Cada tarjeta muestra imagen, nombre y accion principal.

Comportamiento:

- Invitado y empleado ven detalle en modal carrusel.
- Administrador sin rol empleado va directo a editar.
- Administrador con rol empleado tambien puede ver el modal carrusel.

El modal muestra:

- Carrusel de imagenes del producto.
- Datos generales.
- Proveedores.
- Presentacion.
- Precios.
- Stock.
- Atributos: impuesto, perecedero e inventariable.

### Productos

Ruta de creacion:

```text
/products/create
```

Campos principales:

- SKU.
- Codigo de barras.
- Nombre.
- Categoria.
- Marca.
- Presentacion.
- Stock.
- Stock minimo.
- Precio de compra.
- Precio venta 1.
- Precio venta 2.
- Precio venta 3.
- Proveedores separados por coma.
- Sujeto a impuesto.
- Perecedero.
- Inventariable.
- Fecha de vencimiento.
- Descripcion.
- Multiples imagenes JPG/JPEG.

Limites de imagenes:

- Hasta 8 imagenes por carga.
- Maximo 2 MB por imagen.
- Solo JPG/JPEG.

### Almacen

Ruta:

```text
/warehouse
```

Incluye:

- Gestion de categorias.
- Gestion de marcas.
- Gestion de presentaciones.
- Acceso a productos.
- Consulta de productos perecederos.

Productos perecederos:

```text
/warehouse/perishables
```

Permite consultar productos proximos a vencer segun cantidad de dias.

### Entradas de Inventario

Ruta:

```text
/inventory/entries
```

Permite registrar entradas de productos y aumentar stock.

### Ventas

Ruta:

```text
/inventory/sales
```

Permite procesar ventas mediante modal tipo facturacion.

Incluye:

- Producto.
- Cantidad.
- Comprobante: ticket o factura.
- Metodo de pago: efectivo, tarjeta o transferencia.
- Total a pagar.
- Efectivo recibido.
- Cambio automatico.

Importante:

- Para vender es necesario tener una caja abierta.
- Al vender se descuenta stock.
- La venta registra automaticamente un ingreso en caja.

### Caja

Ruta:

```text
/cash
```

Permite administrar la caja diaria.

Funciones:

- Abrir caja con monto inicial.
- Cerrar caja.
- Registrar ingresos manuales.
- Registrar devoluciones.
- Registrar prestamos.
- Registrar gastos.
- Ver movimientos de la caja abierta.

Estadisticas:

- Monto inicial.
- Ingreso.
- Devoluciones.
- Prestamos.
- Gastos.
- Ingresos totales.
- Egresos.
- Saldo.

### Cotizaciones

Ruta:

```text
/quotes
```

Funciones:

- Generar cotizaciones con varios productos.
- Calcular subtotal, impuesto y total.
- Guardar datos del cliente.
- Ver detalle de cotizacion.
- Consultar cotizaciones entre fechas.

Rutas:

```text
/quotes
/quotes/create
/quotes/{quote}
```

### Compras

Ruta:

```text
/purchases
```

Funciones:

- Realizar compra.
- Consultar compras por fechas.
- Consultar compras por mes.
- Ver compras al credito.
- Registrar abonos.
- Ver historial de precios.

Realizar compra:

```text
/purchases/create
```

Permite:

- Seleccionar proveedor.
- Buscar/seleccionar productos por nombre, SKU o codigo.
- Indicar cantidades.
- Indicar costo unitario.
- Elegir contado o credito.
- Calcular subtotal, impuesto y total.

Al guardar una compra:

- Aumenta stock si el producto es inventariable.
- Actualiza el precio de compra.
- Guarda historial de precios.
- Si es credito, registra saldo pendiente y abonos.

Proveedores:

```text
/suppliers
```

Campos:

- Nombre.
- DNI.
- RUC.
- Contacto.
- Telefono.
- Correo.
- Direccion.

Historial de precios:

```text
/purchases/price-history
```

Compras al credito:

```text
/purchases/credits
```

### Reportes

Ruta:

```text
/reports
```

Incluye:

- Movimientos.
- Ventas.
- Entradas.
- Mermas.
- Traslados.
- Bajo stock.
- Valor de inventario.
- Productos mas vendidos.
- Exportacion Excel.

Filtros:

- Tipo de movimiento.
- Fecha desde.
- Fecha hasta.
- Busqueda.

### Usuarios

Ruta:

```text
/users
```

Permite:

- Buscar usuarios.
- Ver roles actuales.
- Asignar multiples roles.
- Validar permisos asociados a roles.

Restriccion:

- El usuario autenticado no puede modificarse a si mismo desde esta pantalla.

## Base de Datos

Tablas principales:

- `users`
- `roles`
- `permissions`
- `role_user`
- `permission_role`
- `categories`
- `brands`
- `presentations`
- `products`
- `product_images`
- `suppliers`
- `product_supplier`
- `movements`
- `quotes`
- `quote_items`
- `purchases`
- `purchase_items`
- `purchase_payments`
- `cash_registers`
- `cash_movements`

Relaciones principales:

- Un usuario tiene muchos roles.
- Un rol tiene muchos permisos.
- Un producto pertenece a categoria, marca y presentacion.
- Un producto puede tener multiples imagenes.
- Un producto puede tener multiples proveedores.
- Un producto tiene muchos movimientos.
- Una cotizacion tiene muchos items.
- Una compra tiene muchos items y puede tener muchos abonos.
- Una caja tiene muchos movimientos de caja.

## Comandos Utiles

Migraciones:

```bash
php artisan migrate
```

Recrear base y seed:

```bash
php artisan migrate:fresh --seed
```

Seeders:

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

Ver rutas:

```bash
php artisan route:list
```

Pruebas:

```bash
php artisan test
```

Compilar assets:

```bash
npm.cmd run build
```

## Notas Operativas

- Abrir caja antes de procesar ventas.
- Ejecutar `php artisan storage:link` para ver imagenes.
- Cambiar la clave del usuario administrador inicial.
- Asignar roles correctos a usuarios nuevos.
- Usar proveedores separados por coma en productos.
- Revisar productos perecederos desde Almacen.
- Consultar compras al credito para registrar abonos.
- Usar `APP_TIMEZONE=America/Managua` para que reportes y fechas usen hora local.
