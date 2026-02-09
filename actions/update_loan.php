<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=loans');
    exit;
}

require_login();
if (!in_array(current_user_role(), ['ADMIN', 'COBRADOR'], true)) {
    $_SESSION['flash'] = 'No tienes permisos para editar prestamos.';
    header('Location: index.php?page=loans');
    exit;
}

$loanId = (int) ($_POST['loan_id'] ?? 0);
$montoOriginal = (float) ($_POST['monto_original'] ?? 0);
$saldoPendiente = (float) ($_POST['saldo_pendiente'] ?? -1);
$tasa = (float) ($_POST['tasa'] ?? 0);
$fechaDesembolso = $_POST['fecha_desembolso'] ?? '';
$proximaFechaPago = $_POST['proxima_fecha_pago'] ?? '';

if ($loanId <= 0 || $montoOriginal <= 0 || $saldoPendiente < 0 || $tasa <= 0 || $fechaDesembolso === '' || $proximaFechaPago === '') {
    $_SESSION['flash'] = 'Completa los campos obligatorios.';
    header('Location: index.php?page=loan_edit&id=' . $loanId);
    exit;
}

$estado = loan_status([
    'saldo_pendiente' => $saldoPendiente,
    'proxima_fecha_pago' => $proximaFechaPago,
]);

$pdo = db();
$stmt = $pdo->prepare('UPDATE prestamos SET monto_original = :monto_original, saldo_pendiente = :saldo_pendiente, tasa = :tasa, fecha_desembolso = :fecha_desembolso, proxima_fecha_pago = :proxima_fecha_pago, estado = :estado WHERE id = :id');
$stmt->execute([
    ':monto_original' => $montoOriginal,
    ':saldo_pendiente' => $saldoPendiente,
    ':tasa' => $tasa,
    ':fecha_desembolso' => $fechaDesembolso,
    ':proxima_fecha_pago' => $proximaFechaPago,
    ':estado' => $estado,
    ':id' => $loanId,
]);

$_SESSION['flash'] = 'Prestamo actualizado.';
header('Location: index.php?page=loan_detail&id=' . $loanId);
exit;
