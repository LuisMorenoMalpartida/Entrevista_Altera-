<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=loan_new');
    exit;
}

require_login();
if (current_user_role() !== 'ADMIN') {
    $_SESSION['flash'] = 'No tienes permisos para crear prestamos.';
    header('Location: index.php?page=loans');
    exit;
}

$clienteId = (int) ($_POST['cliente_id'] ?? 0);
$montoOriginal = (float) ($_POST['monto_original'] ?? 0);
$saldoPendiente = (float) ($_POST['saldo_pendiente'] ?? 0);
$tasa = (float) ($_POST['tasa'] ?? 0);
$fechaDesembolso = $_POST['fecha_desembolso'] ?? '';
$proximaFechaPago = $_POST['proxima_fecha_pago'] ?? '';

if ($clienteId <= 0 || $montoOriginal <= 0 || $tasa <= 0 || $fechaDesembolso === '' || $proximaFechaPago === '') {
    $_SESSION['flash'] = 'Completa los campos obligatorios.';
    header('Location: index.php?page=loan_new');
    exit;
}

if ($saldoPendiente <= 0) {
    $saldoPendiente = $montoOriginal;
}

$estado = loan_status([
    'saldo_pendiente' => $saldoPendiente,
    'proxima_fecha_pago' => $proximaFechaPago,
]);

$pdo = db();
$stmt = $pdo->prepare('INSERT INTO prestamos (cliente_id, monto_original, saldo_pendiente, tasa, fecha_desembolso, proxima_fecha_pago, estado) VALUES (:cliente_id, :monto_original, :saldo_pendiente, :tasa, :fecha_desembolso, :proxima_fecha_pago, :estado)');
$stmt->execute([
    ':cliente_id' => $clienteId,
    ':monto_original' => $montoOriginal,
    ':saldo_pendiente' => $saldoPendiente,
    ':tasa' => $tasa,
    ':fecha_desembolso' => $fechaDesembolso,
    ':proxima_fecha_pago' => $proximaFechaPago,
    ':estado' => $estado,
]);

$_SESSION['flash'] = 'Prestamo creado.';
header('Location: index.php?page=loans');
exit;
