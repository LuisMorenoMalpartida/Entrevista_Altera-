# Cobranza Mini

Mini-modulo web de cobranza hecho con PHP nativo y MySQL. Permite administrar prestamos, registrar pagos, llevar bitacora de gestiones de cobranza y generar reportes de mora por cliente.

## Stack

- PHP 8.x (sin framework)
- MySQL/MariaDB
- Bootstrap (UI)

## Estructura del proyecto

```
actions/        Controladores de POST y export
assets/         Estilos y recursos
lib/            Helpers (auth, DB, calculos de mora)
pages/          Vistas renderizadas por index.php
partials/       Layout (header/footer)
sql/            Schema y seeds
config.php      Configuracion principal
index.php       Front controller y router
```

## Instalacion

1. Copia esta carpeta dentro de `c:\xampp\htdocs\entrevista`.
2. Abre XAMPP y enciende Apache + MySQL.
3. Crea la base de datos:

```sql
CREATE DATABASE entrevista CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

4. Importa el schema y los seeds:

- Importa `sql/schema.sql`.
- (Opcional) Importa `sql/seed_altera_collections.sql` para datos de prueba.

5. Ajusta credenciales en `config.php` si tu MySQL no usa `root` sin password.
6. Abre `http://localhost/entrevista/index.php` en el navegador.

### Orden recomendado de importacion

1. `sql/schema.sql`
2. `sql/seed_altera_collections.sql`

## Configuracion

Archivo `config.php`:

- `db`: host, name, user, pass, charset.
- `auth`: credenciales iniciales (solo se usan si no existe ningun usuario en la tabla `usuarios`).

## Credenciales por defecto

- Usuario: `admin`
- Password: `admin123`

En el primer login se crea el usuario ADMIN en la tabla `usuarios` si esta vacia.

## Enrutamiento

Entrada principal: `index.php`.

### Paginas (`index.php?page=...`)

- `dashboard`: resumen general.
- `loans`: listado de prestamos con filtros.
- `loan_detail`: detalle del prestamo, pagos y gestiones.
- `loan_new`: alta de prestamo (solo ADMIN).
- `loan_edit`: edicion de prestamo (ADMIN/COBRADOR).
- `client_edit`: edicion de cliente (ADMIN/COBRADOR).
- `client_report`: reporte de mora por cliente.
- `payment_edit`: edicion de pago (ADMIN/COBRADOR).
- `collection_edit`: edicion de gestion (ADMIN/COBRADOR).
- `user_new`: alta de usuarios (solo ADMIN).
- `login`: pantalla de acceso.

### Acciones (`index.php?action=...`)

- `login` / `logout`
- `add_loan`, `update_loan`, `delete_loan`
- `add_client`, `update_client`, `delete_client`
- `add_payment`, `update_payment`, `delete_payment`
- `add_collection`, `update_collection`, `delete_collection`
- `add_user`
- `export_overdue` (CSV de mora)
- `export_client_report` (CSV por cliente)

## Funcionalidades

- Login basico con sesiones.
- Listado de prestamos con filtros por tipo, estado y solo mora.
- Calculo de mora segun fecha de pago y saldo pendiente.
- Registro de pagos (actualiza saldo y si queda 0 se marca cancelado).
- Bitacora de gestiones (llamada, WhatsApp, email, visita).
- Edicion y eliminacion de pagos y gestiones (con permisos).
- Edicion y eliminacion de prestamos y clientes (solo si no tienen registros dependientes).
- Reporte de mora por cliente (vista y export CSV).
- Dashboard con resumen y top atrasados.
- Recordatorio de WhatsApp en el detalle del prestamo (texto para copiar).

## Permisos (acciones)

- ADMIN y COBRADOR: pueden editar/eliminar pagos y gestiones.
- ADMIN: puede crear usuarios y eliminar prestamos/clientes.
- SUPERVISOR y AUDITOR: solo lectura (si existen en la tabla `usuarios`).

## Regla de mora

Un prestamo esta en mora si hoy es mayor a la proxima fecha de pago y el saldo es mayor a 0.

Estados:

- `AL_DIA`
- `MORA_1_7`
- `MORA_8_30`
- `MORA_31_MAS`
- `CANCELADO`

El estado se calcula en tiempo real en `lib/loan.php` y no se persiste como estado definitivo.

## Tablas (modelo minimo)

- `clientes`: id, tipo, nombre, documento, telefono, email
- `prestamos`: id, cliente_id, monto_original, saldo_pendiente, tasa, fecha_desembolso, proxima_fecha_pago, estado
- `pagos`: id, prestamo_id, fecha_pago, monto, metodo, nota
- `gestiones_cobranza`: id, prestamo_id, fecha_hora, canal, resultado, comentario
- `usuarios`: id, username, password_hash, role

## Flujo de prueba rapido

1. Login con `admin/admin123`.
2. Dashboard: validar cards y top atrasados.
3. Prestamos: filtrar por tipo, estado y buscar por nombre.
4. Detalle: registrar un pago y verificar saldo actualizado.
5. Detalle: registrar una gestion y verificar bitacora.
6. Nuevo prestamo: crear cliente nuevo y luego el prestamo.

## Solucion de problemas

- Error "Unknown database 'entrevista'": crea la BD y reintenta.
- Warning de tasa truncada: asegurate de tener `tasa DECIMAL(6,3)` en el schema.
- No puedes crear usuarios: verifica que exista la tabla `usuarios`.
