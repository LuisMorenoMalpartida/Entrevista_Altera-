<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=loans');
    exit;
}

require_login();
if (!in_array(current_user_role(), ['ADMIN', 'COBRADOR'], true)) {
    $_SESSION['flash'] = 'No tienes permisos para eliminar gestiones.';
    header('Location: index.php?page=loans');
    exit;
}

$collectionId = (int) ($_POST['collection_id'] ?? 0);
$loanId = (int) ($_POST['loan_id'] ?? 0);

if ($collectionId <= 0 || $loanId <= 0) {
    $_SESSION['flash'] = 'Gestion no valida.';
    header('Location: index.php?page=loans');
    exit;
}

$pdo = db();
$stmt = $pdo->prepare('DELETE FROM gestiones_cobranza WHERE id = :id AND prestamo_id = :loan_id');
$stmt->execute([':id' => $collectionId, ':loan_id' => $loanId]);

$_SESSION['flash'] = 'Gestion eliminada.';
header('Location: index.php?page=loan_detail&id=' . $loanId);
exit;
