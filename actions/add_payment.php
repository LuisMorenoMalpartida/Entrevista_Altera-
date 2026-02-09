<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=loans');
    exit;
}

require_login();
if (!in_array(current_user_role(), ['ADMIN', 'COBRADOR'], true)) {
    $_SESSION['flash'] = 'No tienes permisos para registrar pagos.';
    header('Location: index.php?page=loans');
    exit;
}

$loanId = (int) ($_POST['prestamo_id'] ?? 0);
$paidAt = $_POST['fecha_pago'] ?? '';
$amount = (float) ($_POST['monto'] ?? 0);
$method = trim($_POST['metodo'] ?? '');
$note = trim($_POST['nota'] ?? '');

if ($loanId <= 0 || $paidAt === '' || $amount <= 0 || $method === '') {
    $_SESSION['flash'] = 'Completa los campos obligatorios.';
    header('Location: index.php?page=loan_detail&id=' . $loanId);
    exit;
}

$pdo = db();
$pdo->beginTransaction();

$loanStmt = $pdo->prepare('SELECT saldo_pendiente FROM prestamos WHERE id = :id FOR UPDATE');
$loanStmt->execute([':id' => $loanId]);
$loan = $loanStmt->fetch();

if (!$loan) {
    $pdo->rollBack();
    $_SESSION['flash'] = 'Prestamo no encontrado.';
    header('Location: index.php?page=loans');
    exit;
}

$newBalance = max(0, (float) $loan['saldo_pendiente'] - $amount);
$newStatus = $newBalance <= 0 ? 'CANCELADO' : null;

$insertPayment = $pdo->prepare('INSERT INTO pagos (prestamo_id, fecha_pago, monto, metodo, nota) VALUES (:loan_id, :paid_at, :amount, :method, :note)');
$insertPayment->execute([
    ':loan_id' => $loanId,
    ':paid_at' => $paidAt,
    ':amount' => $amount,
    ':method' => $method,
    ':note' => $note,
]);

$updateLoan = $pdo->prepare('UPDATE prestamos SET saldo_pendiente = :balance, estado = :estado WHERE id = :id');
$updateLoan->execute([':balance' => $newBalance, ':estado' => $newStatus, ':id' => $loanId]);

$pdo->commit();

$_SESSION['flash'] = 'Pago registrado. Nuevo saldo: ' . number_format($newBalance, 2);
header('Location: index.php?page=loan_detail&id=' . $loanId);
exit;
