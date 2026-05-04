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

Incluye:

- movimientos de inventario,
- ventas,
- entradas,
- mermas y traslados,
- valor de inventario,
- filtros por fecha y tipo.

Acceso:

- Este modulo esta restringido para perfiles autorizados por administracion (actualmente super-admin).

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
