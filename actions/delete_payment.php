<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=loans');
    exit;
}

require_login();
if (!in_array(current_user_role(), ['ADMIN', 'COBRADOR'], true)) {
    $_SESSION['flash'] = 'No tienes permisos para eliminar pagos.';
    header('Location: index.php?page=loans');
    exit;
}

$paymentId = (int) ($_POST['payment_id'] ?? 0);
$loanId = (int) ($_POST['loan_id'] ?? 0);

if ($paymentId <= 0 || $loanId <= 0) {
    $_SESSION['flash'] = 'Pago no valido.';
    header('Location: index.php?page=loans');
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

$newBalance = (float) $loan['saldo_pendiente'] + (float) $payment['monto'];
$estado = loan_status([
    'saldo_pendiente' => $newBalance,
    'proxima_fecha_pago' => $loan['proxima_fecha_pago'],
]);

$deletePayment = $pdo->prepare('DELETE FROM pagos WHERE id = :id AND prestamo_id = :loan_id');
$deletePayment->execute([':id' => $paymentId, ':loan_id' => $loanId]);

$updateLoan = $pdo->prepare('UPDATE prestamos SET saldo_pendiente = :saldo, estado = :estado WHERE id = :id');
$updateLoan->execute([':saldo' => $newBalance, ':estado' => $estado, ':id' => $loanId]);

$pdo->commit();

$_SESSION['flash'] = 'Pago eliminado.';
header('Location: index.php?page=loan_detail&id=' . $loanId);
exit;
