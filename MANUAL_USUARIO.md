# Manual de Usuario - Sistema de Inventario

Este manual esta orientado al uso diario del sistema por personal operativo y administrativo.

## 1) Ingreso al sistema

1. Abrir el navegador.
2. Entrar a la URL del sistema.
3. Ingresar correo y clave.
4. Presionar **Iniciar sesion**.

Si no tienes acceso a un modulo, significa que tu rol no tiene permiso para esa seccion.

## 2) Pantalla principal

- Usuarios operativos ven un **dashboard** con resumen del dia.
- El dashboard incluye **3 graficos** basados en la base de datos:
  - Ventas de los ultimos 7 dias (grafico de lineas).
  - Productos por categoria (grafico de dona).
  - Top 5 productos mas vendidos (grafico de barras).
- Usuarios con rol **Invitado** ven una pantalla de bienvenida con informacion general.
- El menu lateral muestra solo modulos permitidos para tu rol.

## 3) Catalogo de productos

Ruta: **Catalogo**

Permite:

- buscar por nombre, SKU o codigo de barras,
- ver imagenes y datos del producto,
- ver precio de venta.

## 4) Clientes

Ruta: **Clientes**

Campos principales:

- Tipo: Cliente o Empresa
- Nombre
- DNI / RUC
- Telefono / Email
- Limite crediticio
- Direccion
- Estado (vigente/inactivo)

Uso recomendado:

- registrar cliente antes de ventas a credito o apartados,
- mantener actualizado limite crediticio.

## 5) Ventas

Ruta: **Ventas**

Flujo:

1. Buscar producto por nombre/SKU/codigo.
2. Agregar al carrito.
3. Definir cantidad, precio (1/2/3) y descuento.
4. Seleccionar cliente (si aplica).
5. Elegir tipo de venta: contado o credito.
6. Elegir forma de pago: efectivo, tarjeta o mixto.
7. Seleccionar tipo de comprobante.
8. Confirmar venta.

Notas:

- Para vender debe existir caja abierta.
- En contado, el monto pagado debe cubrir el total.
- En credito, el sistema valida limite del cliente.

## 6) Consultas de ventas

En el mismo modulo de ventas se puede consultar:

- ventas del dia,
- ventas por rango de fechas,
- ventas por mes.

### Imprimir factura

- Cada venta registrada (recientes o consulta) tiene un boton de **impresion** (icono de impresora).
- Al presionarlo se abre la factura en formato termico (ticket 80 mm) con:

  - datos de la tienda,
  - comprobante (numero correlativo),
  - cliente,
  - detalle de items,
  - totales y forma de pago.

- Dentro de la vista de impresion use **Imprimir** del navegador para enviar a la impresora.

## 7) Apartados

Ruta: **Apartados**

### Crear apartado

1. Buscar y agregar productos.
2. Seleccionar cliente.
3. Definir fecha/hora de retiro.
4. Ingresar abono inicial.
5. Guardar apartado.

### Gestionar apartado

- Consultar por dia, rango o mes.
- Registrar abonos.
- Finalizar cuando saldo = 0.

Al finalizar:

- se genera la venta correspondiente.

## 8) Ventas al credito (seguimiento)

En **Apartados** se muestra el bloque de ventas con saldo pendiente.

Permite:

- ver saldo por venta,
- registrar abonos hasta cancelar el credito.

## 9) Caja

Ruta: **Caja**

### Apertura

1. Ingresar monto inicial.
2. Confirmar apertura.

### Durante el dia

- registrar ingresos y egresos manuales cuando corresponda.
- revisar movimientos de caja.

### Cierre

1. Validar saldo final.
2. Registrar monto de cierre.
3. Cerrar caja.

## 10) Compras

Ruta: **Compras**

Permite:

- registrar compras de productos,
- manejar contado o credito,
- consultar compras a credito,
- registrar abonos,
- revisar historial de precios.

Nota tecnica-operativa:

- La numeracion de compras es secuencial diaria y segura frente a operaciones simultaneas.

## 11) Almacen y Kardex

### Almacen

Permite mantener:

- categorias,
- marcas,
- presentaciones,
- productos perecederos.

### Entradas

Permite aumentar stock por ingreso de mercancia.

### Kardex

Permite:

- revisar movimientos por producto y fecha,
- registrar entradas y salidas (traslado o merma),
- controlar saldos de inventario.

## 12) Comprobantes

Ruta: **Comprobantes**

Permite configurar:

- tipos de comprobante (Factura, Boleta, Ticket, etc.),
- prefijo,
- correlativo actual,
- longitud de numeracion.

Las ventas usan este correlativo automaticamente.

## 13) Taller y tecnicos

### Tecnicos

Ruta: **Tecnicos**

Permite registrar personal tecnico con:

- nombre,
- contacto,
- especialidad,
- estado.

### Taller

Ruta: **Taller**

Permite crear ordenes con:

- cliente,
- tecnico,
- aparato (marca/modelo/serie),
- averia,
- observaciones,
- costo del servicio.

## 14) Reportes

Ruta: **Reportes**

El modulo tiene pestanas de navegacion con los siguientes reportes:

1. **Movimientos** (pestana principal):
   - movimientos de inventario (cualquier tipo de movimiento registrado),
   - ventas,
   - entradas,
   - mermas y traslados,
   - bajo stock,
   - valor de inventario,
   - filtros por fecha y tipo.

2. **Ventas** (por periodo): ventas con su detalle (maestro-detalle) por rango de fechas.

3. **Compras** (por periodo): compras con su detalle (maestro-detalle) por rango de fechas.

4. **Morosos**: clientes con saldo pendiente al credito.

5. **Catalogo**: listado maestro-detalle de productos con precios.

### Exportar a Excel

- Todos los reportes tienen un boton **Exportar a Excel**.
- Se descarga un archivo `.xls` con los mismos datos del reporte filtrado.

Acceso:

- Este modulo esta restringido para perfiles autorizados por administracion (actualmente super-admin y roles con permiso `reports.manage`).

## 15) Roles de uso (resumen)

- **Super Admin**: acceso total.
- **Administrador**: gestion integral del negocio (sin acceso a reportes sensibles).
- **Vendedor/Cajero**: ventas, caja, clientes, cotizaciones, apartados, comprobantes.
- **Bodega/Inventario**: catalogo, productos, almacen, entradas, kardex, compras.
- **Tecnico**: tecnicos, taller, clientes, comprobantes.
- **Contabilidad/Caja**: caja, ventas, compras, comprobantes.
- **Gerencia**: supervision (ventas, compras, caja, kardex, clientes).
- **Invitado**: acceso restringido de consulta.

## 16) Buenas practicas operativas

- Abrir caja antes de ventas y abonos.
- Revisar bajo stock diariamente.
- Verificar cliente y limite antes de credito.
- Mantener correlativos de comprobante actualizados.
- Cerrar caja todos los dias al finalizar turno.
- Usar busquedas por SKU/codigo para operar mas rapido.

## 17) Errores comunes y solucion rapida

- **No deja vender**: revisar que caja este abierta.
- **No deja vender a credito**: revisar cliente vigente y limite.
- **No aparece modulo**: validar rol/permisos con administrador.
- **No coincide numeracion de comprobante**: revisar configuracion en Comprobantes.
- **No hay stock suficiente**: registrar entrada o ajustar cantidad.

## 18) Modulo de usuarios (cards colapsables)

En crear/editar usuario, los roles se muestran en bloques colapsables para facilitar asignacion:

- Roles por modulo
- Roles especiales
- Roles con mas de un permiso
- Roles con un solo permiso
- Cada card se puede expandir/contraer con icono `plus/minus`.

## 19) Modulo de roles

Ruta: **Roles** (en el menu Administracion).

Permite al administrador crear, editar y eliminar roles, y **asignar o revocar permisos** directamente.

### Crear o editar un rol

1. Presionar **Crear rol** o **Editar permisos** en un rol existente.
2. Ingresar el nombre del rol y el identificador (solo minusculas, numeros y guiones).
3. Marcar los permisos deseados en las cards colapsables:
   - **Permisos por modulo** (gestion de productos, almacen, ventas, caja, etc.).
   - **Permisos especiales** (ver catalogo, crear productos, gestionar roles/settings, eliminar registros).
4. Guardar. Los permisos del rol se aplican de inmediato a los usuarios que lo tengan asignado.

### Eliminar rol

- Un rol **no se puede eliminar** si tiene usuarios asignados.
- El rol **Super Admin** no se puede eliminar, y sus permisos solo los puede modificar un usuario con permiso `roles.manage`.

## 20) Respaldo y restauracion de la base de datos

Ruta: **Backup y Restauracion** (en el menu Administracion).

Permite respaldar y recuperar la base de datos desde el sistema.

### Crear respaldo

1. Ir a **Backup y Restauracion**.
2. Presionar **Generar backup**.
3. El sistema crea un archivo `.sql` con toda la base de datos.

### Descargar / eliminar respaldo

- En la lista de backups, usar los botones:
  - **Descargar** para guardar la copia en la computadora.
  - **Eliminar** para borrar el archivo del servidor.

### Restaurar

Hay dos formas:

1. **Subir archivo**: seleccionar un archivo `.sql` de la computadora y presionar restaurar.
2. **Desde el servidor**: presionar **Restaurar** junto a un backup existente.

Advertencia:

- **Restaurar reemplaza todos los datos actuales** con los datos del backup. Se recomienda generar un backup antes de restaurar.

## 21) Requerimientos y documentacion tecnica

- El analisis completo (requerimientos funcionales y no funcionales) esta en el documento `docs/REQUERIMIENTOS.md`.
- El sistema expone toda su funcionalidad mediante **APIs REST** protegidas con tokens (para integraciones de terceros o aplicaciones moviles).
