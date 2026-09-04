# Sistema de Control de Inventario y Facturación de Tienda de Celulares

## 1. Análisis y Levantamiento de Requerimientos

### 1.1 Descripción General

El sistema tiene como objetivo automatizar el control de inventario y la facturación de una tienda de celulares (SmartZone). La aplicación permite gestionar productos, ventas, compras, clientes, proveedores, caja, apartados, cotizaciones, taller de reparaciones y reportes, todo bajo un esquema de roles y permisos diferenciados.

### 1.2 Usuarios del Sistema

| Rol | Descripción | Privilegios |
|-----|-------------|-------------|
| Super Administrador | Acceso total al sistema | Todos los permisos sin restricción |
| Administrador | Gestión administrativa completa | Todos los permisos excepto gestión de super-admins |
| Vendedor-Cajero | Atención al cliente y facturación | Ventas, caja, clientes, cotizaciones |
| Bodega-Inventario | Control de almacén e inventario | Catálogo, entradas, kardex, compras |
| Técnico | Taller de reparaciones | Órdenes de servicio, técnicos |
| Contabilidad-Caja | Gestión financiera | Caja, compras, ventas a crédito |
| Gerencia | Consulta y supervisión | Reportes, dashboard, consultas |
| Invitado | Acceso consultivo | Solo visualización de dashboard |

### 1.3 Requerimientos Funcionales (19)

| ID | Requerimiento | Descripción | Prioridad |
|----|---------------|-------------|-----------|
| RF-01 | Autenticación de usuarios | Inicio de sesión con rate limiting (5 intentos), regeneración de sesión y cierre de sesión seguro | Alta |
| RF-02 | Gestión de roles y permisos | Asignación, modificación y revocación de permisos por rol con cifrado de contraseñas | Alta |
| RF-03 | Dashboard principal | Panel con KPIs y al menos 3 gráficos basados en datos de la BD | Alta |
| RF-04 | Catálogo de productos | CRUD completo: SKU, código de barras, precios múltiples (1/2/3), imágenes, categoría/marca/presentación, stock mínimo, IVA, perecederos | Alta |
| RF-05 | Gestión de almacén | CRUD de categorías, marcas y presentaciones; alertas de perecederos próximos a vencer | Alta |
| RF-06 | Entradas de inventario | Registro de entradas de mercancía con incremento automático de stock y movimiento asociado | Alta |
| RF-07 | Kardex | Períodos contables mensuales, registro de movimientos (entrada, venta, merma, traslado) | Alta |
| RF-08 | Ventas con facturación | Carrito de compras, ventas al contado/crédito, pagos efectivo/tarjeta/mixto, descuentos, validación de stock, correlativos de comprobante | Alta |
| RF-09 | Apartados | Apartar productos con reserva de stock, registrar abonos y conversión a venta al finalizar | Media |
| RF-10 | Ventas a crédito | Seguimiento de saldos, registro de abonos y límite crediticio por cliente | Alta |
| RF-11 | Caja diaria | Apertura con monto inicial, movimientos (ingresos, devoluciones, préstamos, gastos), cierre con arqueo | Alta |
| RF-12 | Compras | Compra a contado/crédito, IVA 15%, actualización de costo, historial de precios, abonos | Alta |
| RF-13 | Cotizaciones | Generación de cotizaciones con numeración, subtotal, impuesto y total | Media |
| RF-14 | Clientes | CRUD con tipo persona/empresa, DNI/RUC únicos, giro de negocio, límite crediticio | Alta |
| RF-15 | Proveedores | CRUD con DNI/RUC y datos de contacto | Media |
| RF-16 | Taller de reparaciones | Órdenes de servicio con cliente, técnico, aparato, avería, costo y estados | Media |
| RF-17 | Reportes | Al menos 8 reportes: movimientos, stock bajo, top vendidos, ventas por período, compras, morosos, catálogo maestro-detalle y factura imprimible; exportables a Excel (XLS) | Alta |
| RF-18 | Respaldo y restauración | Crear copias de seguridad (backup), descargarlas, restaurarlas y eliminarlas desde la interfaz | Alta |
| RF-19 | APIs REST | Exposición de todas las funcionalidades mediante APIs REST seguras con tokens | Alta |

### 1.4 Requerimientos No Funcionales

| ID | Categoría | Requerimiento |
|----|-----------|---------------|
| RNF-01 | Seguridad | Contraseñas cifradas con bcrypt (Hash::make); protección CSRF; rate limiting en login |
| RNF-02 | Seguridad | Autorización en 3 capas: menú UI (@can), middleware (can:) y gates backend |
| RNF-03 | Rendimiento | Uso de índices, consultas optimizadas, paginación y eager loading |
| RNF-04 | Concurrencia | Bloqueos de fila (lockForUpdate) para stock, numeración correlativa y caja; Cache::lock para operaciones atómicas |
| RNF-05 | Escalabilidad | Arquitectura MVC con separación de responsabilidades; APIs desacopladas del frontend |
| RNF-06 | Compatibilidad | MySQL/MariaDB con charset UTF-8; Bootstrap 5.3 (responsive) |
| RNF-07 | Usabilidad | Interfaz responsive, breadcrumbs, feedback visual (alertas success/error) |
| RNF-08 | Disponibilidad | Sesiones en base de datos (SESSION_DRIVER=database) |
| RNF-09 | Mantenibilidad | Migraciones versionadas, seeders, modelos Eloquent y atributos tipados |
| RNF-10 | Documentación | Manual de usuario y manual técnico incluidos en el repositorio |

### 1.5 Stack Tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 13 (PHP 8.3) |
| Base de datos | MySQL / MariaDB |
| Frontend | Blade (SSR) + Bootstrap 5.3 + Chart.js 4 |
| APIs | Laravel Sanctum (tokens) |
| Exportación | HTML con Content-Type XLS (Excel) |
| Seguridad | RBAC propio + spatie/laravel-permission (instalado) |

### 1.6 Modelo de Base de Datos (29 tablas)

| Módulo | Tablas |
|--------|--------|
| Seguridad | users, roles, permissions, role_user, permission_role, sessions |
| Catálogo | products, product_images, product_prices, categories, brands, presentations, suppliers, product_supplier |
| Inventario | movements, inventory_periods |
| Clientes | customers |
| Ventas | sales, sale_items, sale_payments |
| Apartados | layaways, layaway_items, layaway_payments |
| Compras | purchases, purchase_items, purchase_payments |
| Caja | cash_registers, cash_movements |
| Cotizaciones | quotes, quote_items |
| Taller | technicians, workshop_orders |
| Comprobantes | receipt_types |