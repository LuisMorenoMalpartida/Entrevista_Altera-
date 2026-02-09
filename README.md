# Cobranza Mini

Mini-modulo web de cobranza con PHP nativo y MySQL.

## Requisitos

- XAMPP con PHP 8.x
- MySQL/MariaDB
- MySQL Workbench (para importar SQL)

## Instalacion

1. Copia esta carpeta dentro de `c:\xampp\htdocs\entrevista`.
2. Abre XAMPP y enciende Apache + MySQL.
2. Crea la base de datos:

```sql
CREATE DATABASE entrevista CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

3. Importa el dataset entregado (si no tienes dataset, usa el schema local):

- En MySQL Workbench: Server > Data Import.
- Selecciona el archivo SQL entregado y ejecuta la importacion.
- Alternativa: importa `sql/schema.sql` para crear tablas vacias.
- Dataset de prueba: importa `sql/seed_altera_collections.sql`.

4. Ajusta credenciales en `config.php` si tu MySQL no usa `root` sin password.
5. Abre `http://localhost/entrevista/index.php` en el navegador.

### Orden recomendado de importacion

1. `sql/schema.sql`
2. `sql/seed_altera_collections.sql`

### Primer login

- Usuario: `admin`
- Password: `admin123`
- En el primer login se crea el usuario ADMIN en la tabla `usuarios` si esta vacia.

## Credenciales

- Usuario: `admin`
- Password: `admin123`

## Funcionalidades

- Login basico.
- Listado de prestamos con filtros por tipo, estado y solo mora.
- Calculo de mora segun fecha de pago y saldo pendiente.
- Registro de pagos (actualiza saldo y si queda 0 se marca cancelado).
- Bitacora de gestiones (llamada, WhatsApp, email, visita).
- Dashboard con resumen y top atrasados.

## Bonus

- Exportar CSV de prestamos en mora desde el listado.
- Recordatorio WhatsApp en el detalle de prestamo (texto para copiar).

## Regla de mora

Un prestamo esta en mora si hoy es mayor a la proxima fecha de pago y el saldo es mayor a 0.

Estados:

- `AL_DIA`
- `MORA_1_7`
- `MORA_8_30`
- `MORA_31_MAS`
- `CANCELADO`

## Notas

- El estado se calcula en tiempo real (no se guarda en BD).
- El saldo se actualiza cada vez que se registra un pago.

## Tablas (modelo minimo)

- clientes: id, tipo, nombre, documento, telefono, email
- prestamos: id, cliente_id, monto_original, saldo_pendiente, tasa, fecha_desembolso, proxima_fecha_pago, estado
- pagos: id, prestamo_id, fecha_pago, monto, metodo, nota
- gestiones_cobranza: id, prestamo_id, fecha_hora, canal, resultado, comentario

## Usuarios y roles

Roles sugeridos:

- ADMIN: acceso total y creacion de usuarios.
- COBRADOR: registra pagos y gestiones.
- SUPERVISOR: solo lectura.
- AUDITOR: solo lectura.

## Flujo de prueba rapido

1. Login con `admin/admin123`.
2. Dashboard: validar cards y top atrasados.
3. Prestamos: filtrar por tipo, estado y buscar por nombre.
4. Detalle: registrar un pago y verificar saldo actualizado.
5. Detalle: registrar una gestion y verificar bitacora.
6. Nuevo prestamo: crear cliente nuevo y luego el prestamo.

## Solucion de problemas

- Error "Unknown database 'entrevista'": crea la BD y reintenta.
- Warning de tasa truncada: asegurate de tener `tasa DECIMAL(6,3)`.
- Si no puedes crear usuarios: verifica que exista la tabla `usuarios`.
