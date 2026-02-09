<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=loans');
    exit;
}

require_login();
if (!in_array(current_user_role(), ['ADMIN', 'COBRADOR'], true)) {
    $_SESSION['flash'] = 'No tienes permisos para eliminar prestamos.';
    header('Location: index.php?page=loans');
    exit;
}

$loanId = (int) ($_POST['loan_id'] ?? 0);

if ($loanId <= 0) {
    $_SESSION['flash'] = 'Prestamo no valido.';
    header('Location: index.php?page=loans');
    exit;
}

$pdo = db();

$paymentsCountStmt = $pdo->prepare('SELECT COUNT(*) FROM pagos WHERE prestamo_id = :id');
$paymentsCountStmt->execute([':id' => $loanId]);
$paymentsCount = (int) $paymentsCountStmt->fetchColumn();

$collectionsCountStmt = $pdo->prepare('SELECT COUNT(*) FROM gestiones_cobranza WHERE prestamo_id = :id');
$collectionsCountStmt->execute([':id' => $loanId]);
$collectionsCount = (int) $collectionsCountStmt->fetchColumn();

if ($paymentsCount > 0 || $collectionsCount > 0) {
    $_SESSION['flash'] = 'No se puede eliminar un prestamo con pagos o gestiones.';
    header('Location: index.php?page=loan_detail&id=' . $loanId);
    exit;
}

$stmt = $pdo->prepare('DELETE FROM prestamos WHERE id = :id');
$stmt->execute([':id' => $loanId]);

$_SESSION['flash'] = 'Prestamo eliminado.';
header('Location: index.php?page=loans');
exit;
