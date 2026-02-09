/* =========================================
   SEED - ALTERA COLLECTIONS (20 prestamos)
   Requiere tablas:
   clientes(id, tipo, nombre, documento, telefono, email)
   prestamos(id, cliente_id, monto_original, saldo_pendiente, tasa, fecha_desembolso, proxima_fecha_pago, estado, created_at)
   pagos(id, prestamo_id, fecha_pago, monto, metodo, nota)
   gestiones_cobranza(id, prestamo_id, fecha_hora, canal, resultado, comentario)
   ========================================= */

-- Limpieza (opcional)
-- SET FOREIGN_KEY_CHECKS=0;
-- TRUNCATE TABLE gestiones_cobranza;
-- TRUNCATE TABLE pagos;
-- TRUNCATE TABLE prestamos;
-- TRUNCATE TABLE clientes;
-- SET FOREIGN_KEY_CHECKS=1;

-- 1) CLIENTES (10)
INSERT INTO clientes (id, tipo, nombre, documento, telefono, email) VALUES
(1,'PERSONA','Carlos Paredes','DNI 45871236','999111222','carlos.paredes@mail.com'),
(2,'PERSONA','María Torres','DNI 70451239','999222333','maria.torres@mail.com'),
(3,'PERSONA','Jorge Salazar','DNI 41239876','999333444','jorge.salazar@mail.com'),
(4,'PERSONA','Rosa Medina','DNI 48652109','999444555','rosa.medina@mail.com'),
(5,'PERSONA','Luis Navarro','DNI 44112098','999555666','luis.navarro@mail.com'),
(6,'PERSONA','Ana Ríos','DNI 49988776','999666777','ana.rios@mail.com'),

(7,'NEGOCIO','Taller Rápido SAC','RUC 20500111223','981111222','admin@tallerrapido.com'),
(8,'NEGOCIO','Distribuciones Andinas EIRL','RUC 20400999887','982222333','cobranzas@andinas.com'),
(9,'NEGOCIO','Agro Servicios del Sur SAC','RUC 20600333445','983333444','finanzas@agrosur.com'),
(10,'NEGOCIO','Tech Market Perú SAC','RUC 20500777111','984444555','pagos@techmarket.pe');

-- 2) PRESTAMOS (20)
-- Notas:
-- - proxima_fecha_pago determina mora vs al dia usando CURDATE().
-- - estado se puede recalcular en runtime; igual lo dejamos sembrado para filtros.
INSERT INTO prestamos (id, cliente_id, monto_original, saldo_pendiente, tasa, fecha_desembolso, proxima_fecha_pago, estado, created_at) VALUES

-- AL DIA (8)
(101, 1, 15000, 15000, 0.029, DATE_SUB(CURDATE(), INTERVAL 10 DAY), CURDATE(), 'AL_DIA', NOW()),
(102, 2,  8000,  6200, 0.031, DATE_SUB(CURDATE(), INTERVAL 25 DAY), DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'AL_DIA', NOW()),
(103, 3, 12000, 12000, 0.028, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 12 DAY), 'AL_DIA', NOW()),
(104, 4,  6000,  3500, 0.033, DATE_SUB(CURDATE(), INTERVAL 40 DAY), CURDATE(), 'AL_DIA', NOW()),
(105, 7, 45000, 45000, 0.027, DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'AL_DIA', NOW()),
(106, 8, 70000, 51000, 0.026, DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'AL_DIA', NOW()),
(107, 9, 55000, 55000, 0.028, DATE_SUB(CURDATE(), INTERVAL 12 DAY), CURDATE(), 'AL_DIA', NOW()),
(108,10, 38000, 31000, 0.029, DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'AL_DIA', NOW()),

-- MORA 1-7 (6)
(109, 5,  9000,  9000, 0.034, DATE_SUB(CURDATE(), INTERVAL 35 DAY), DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'MORA_1_7', NOW()),
(110, 6,  5000,  4100, 0.035, DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_SUB(CURDATE(), INTERVAL 6 DAY), 'MORA_1_7', NOW()),
(111, 1, 18000, 16200, 0.032, DATE_SUB(CURDATE(), INTERVAL 50 DAY), DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'MORA_1_7', NOW()),
(112, 7, 30000, 24000, 0.030, DATE_SUB(CURDATE(), INTERVAL 45 DAY), DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'MORA_1_7', NOW()),
(113, 8, 22000, 22000, 0.031, DATE_SUB(CURDATE(), INTERVAL 18 DAY), DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'MORA_1_7', NOW()),
(114,10, 14000, 10500, 0.033, DATE_SUB(CURDATE(), INTERVAL 22 DAY), DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'MORA_1_7', NOW()),

-- MORA 8-30 (4)
(115, 2, 25000, 25000, 0.029, DATE_SUB(CURDATE(), INTERVAL 90 DAY), DATE_SUB(CURDATE(), INTERVAL 12 DAY), 'MORA_8_30', NOW()),
(116, 3, 11000,  8800, 0.034, DATE_SUB(CURDATE(), INTERVAL 75 DAY), DATE_SUB(CURDATE(), INTERVAL 20 DAY), 'MORA_8_30', NOW()),
(117, 9, 60000, 60000, 0.028, DATE_SUB(CURDATE(), INTERVAL 120 DAY), DATE_SUB(CURDATE(), INTERVAL 28 DAY), 'MORA_8_30', NOW()),
(118, 8, 42000, 36000, 0.029, DATE_SUB(CURDATE(), INTERVAL 85 DAY), DATE_SUB(CURDATE(), INTERVAL 15 DAY), 'MORA_8_30', NOW()),

-- MORA 31+ (2)
(119, 4, 16000, 16000, 0.036, DATE_SUB(CURDATE(), INTERVAL 160 DAY), DATE_SUB(CURDATE(), INTERVAL 45 DAY), 'MORA_31_MAS', NOW()),
(120, 7, 90000, 90000, 0.027, DATE_SUB(CURDATE(), INTERVAL 210 DAY), DATE_SUB(CURDATE(), INTERVAL 65 DAY), 'MORA_31_MAS', NOW()),

-- CANCELADO (1)
(121, 5, 10000,     0, 0.033, DATE_SUB(CURDATE(), INTERVAL 200 DAY), DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'CANCELADO', NOW());

-- 3) PAGOS (ejemplos)
INSERT INTO pagos (prestamo_id, fecha_pago, monto, metodo, nota) VALUES
(102, DATE_SUB(CURDATE(), INTERVAL 7 DAY), 1800, 'transferencia', 'Pago parcial - cuota'),
(104, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 2500, 'yape', 'Pago parcial - regularización'),
(106, DATE_SUB(CURDATE(), INTERVAL 20 DAY), 19000, 'transferencia', 'Abono a capital'),
(108, DATE_SUB(CURDATE(), INTERVAL 10 DAY),  7000, 'plin', 'Pago parcial'),
(110, DATE_SUB(CURDATE(), INTERVAL 2 DAY),   900, 'efectivo', 'Abono menor por mora'),
(116, DATE_SUB(CURDATE(), INTERVAL 30 DAY),  2200, 'transferencia', 'Pago parcial'),
(118, DATE_SUB(CURDATE(), INTERVAL 25 DAY),  6000, 'transferencia', 'Abono a capital'),
(121, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 10000, 'transferencia', 'Cancelación total');

-- 4) GESTIONES COBRANZA (ejemplos)
INSERT INTO gestiones_cobranza (prestamo_id, fecha_hora, canal, resultado, comentario) VALUES
(109, CONCAT(CURDATE(),' 10:30:00'), 'whatsapp', 'contactado', 'Cliente indica pago mañana por la tarde'),
(110, CONCAT(CURDATE(),' 11:15:00'), 'llamada', 'no_contactado', 'No contestó, reintentar 16:00'),
(111, CONCAT(CURDATE(),' 09:40:00'), 'llamada', 'promesa_de_pago', 'Promete pagar en 48h'),
(112, CONCAT(CURDATE(),' 12:05:00'), 'email', 'contactado', 'Enviada liquidación y link de pago'),
(115, CONCAT(CURDATE(),' 08:50:00'), 'visita', 'contactado', 'Se dejó notificación, acuerda abono'),
(119, CONCAT(CURDATE(),' 14:10:00'), 'llamada', 'no_contactado', 'Teléfono apagado, validar nuevo número');
