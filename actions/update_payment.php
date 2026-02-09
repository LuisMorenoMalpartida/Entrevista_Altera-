<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=loans');
    exit;
}

require_login();
if (!in_array(current_user_role(), ['ADMIN', 'COBRADOR'], true)) {
    $_SESSION['flash'] = 'No tienes permisos para editar pagos.';
    header('Location: index.php?page=loans');
    exit;
}

$paymentId = (int) ($_POST['payment_id'] ?? 0);
$loanId = (int) ($_POST['loan_id'] ?? 0);
$paidAt = $_POST['fecha_pago'] ?? '';
$amount = (float) ($_POST['monto'] ?? 0);
$method = trim($_POST['metodo'] ?? '');
$note = trim($_POST['nota'] ?? '');

if ($paymentId <= 0 || $loanId <= 0 || $paidAt === '' || $amount <= 0 || $method === '') {
    $_SESSION['flash'] = 'Completa los campos obligatorios.';
    header('Location: index.php?page=loan_detail&id=' . $loanId);
    exit;
}

$pdo = db();
$pdo->beginTransaction();

$paymentStmt = $pdo->prepare('SELECT monto FROM pagos WHERE id = :id AND prestamo_id = :loan_id FOR UPDATE');
$paymentStmt->execute([':id' => $paymentId, ':loan_id' => $loanId]);
$payment = $paymentStmt->fetch();

$loanStmt = $pdo->prepare('SELECT saldo_pendiente, proxima_fecha_pago FROM prestamos WHERE id = :id FOR UPDATE');
$loanStmt->execute([':id' => $loanId]);
$loan = $loanStmt->fetch();

if (!$payment || !$loan) {
    $pdo->rollBack();
    $_SESSION['flash'] = 'Pago o prestamo no encontrado.';
    header('Location: index.php?page=loan_detail&id=' . $loanId);
    exit;
}

$oldAmount = (float) $payment['monto'];
$newBalance = max(0, (float) $loan['saldo_pendiente'] + $oldAmount - $amount);
$estado = loan_status([
    'saldo_pendiente' => $newBalance,
    'proxima_fecha_pago' => $loan['proxima_fecha_pago'],
]);

$updatePayment = $pdo->prepare('UPDATE pagos SET fecha_pago = :fecha_pago, monto = :monto, metodo = :metodo, nota = :nota WHERE id = :id AND prestamo_id = :loan_id');
$updatePayment->execute([
    ':fecha_pago' => $paidAt,
    ':monto' => $amount,
    ':metodo' => $method,
    ':nota' => $note,
    ':id' => $paymentId,
    ':loan_id' => $loanId,
]);

$updateLoan = $pdo->prepare('UPDATE prestamos SET saldo_pendiente = :saldo, estado = :estado WHERE id = :id');
$updateLoan->execute([':saldo' => $newBalance, ':estado' => $estado, ':id' => $loanId]);

$pdo->commit();

$_SESSION['flash'] = 'Pago actualizado.';
header('Location: index.php?page=loan_detail&id=' . $loanId);
exit;
