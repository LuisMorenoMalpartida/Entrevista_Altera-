<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=loans');
    exit;
}

require_login();
if (!in_array(current_user_role(), ['ADMIN', 'COBRADOR'], true)) {
    $_SESSION['flash'] = 'No tienes permisos para eliminar clientes.';
    header('Location: index.php?page=loans');
    exit;
}

$clientId = (int) ($_POST['client_id'] ?? 0);

if ($clientId <= 0) {
    $_SESSION['flash'] = 'Cliente no valido.';
    header('Location: index.php?page=loans');
    exit;
}

$pdo = db();
$loansCountStmt = $pdo->prepare('SELECT COUNT(*) FROM prestamos WHERE cliente_id = :id');
$loansCountStmt->execute([':id' => $clientId]);
$loansCount = (int) $loansCountStmt->fetchColumn();

if ($loansCount > 0) {
    $_SESSION['flash'] = 'No se puede eliminar un cliente con prestamos.';
    header('Location: index.php?page=loans');
    exit;
}

$stmt = $pdo->prepare('DELETE FROM clientes WHERE id = :id');
$stmt->execute([':id' => $clientId]);

$_SESSION['flash'] = 'Cliente eliminado.';
header('Location: index.php?page=loans');
exit;
