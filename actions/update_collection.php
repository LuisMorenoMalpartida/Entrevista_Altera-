<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=loans');
    exit;
}

require_login();
if (!in_array(current_user_role(), ['ADMIN', 'COBRADOR'], true)) {
    $_SESSION['flash'] = 'No tienes permisos para editar gestiones.';
    header('Location: index.php?page=loans');
    exit;
}

$collectionId = (int) ($_POST['collection_id'] ?? 0);
$loanId = (int) ($_POST['loan_id'] ?? 0);
$managedAt = $_POST['fecha_hora'] ?? '';
$channel = trim($_POST['canal'] ?? '');
$result = trim($_POST['resultado'] ?? '');
$comment = trim($_POST['comentario'] ?? '');

if ($collectionId <= 0 || $loanId <= 0 || $managedAt === '' || $channel === '' || $result === '' || $comment === '') {
    $_SESSION['flash'] = 'Completa los campos obligatorios.';
    header('Location: index.php?page=loan_detail&id=' . $loanId);
    exit;
}

$managedAt = str_replace('T', ' ', $managedAt);

$pdo = db();
$stmt = $pdo->prepare('UPDATE gestiones_cobranza SET fecha_hora = :fecha_hora, canal = :canal, resultado = :resultado, comentario = :comentario WHERE id = :id AND prestamo_id = :loan_id');
$stmt->execute([
    ':fecha_hora' => $managedAt,
    ':canal' => $channel,
    ':resultado' => $result,
    ':comentario' => $comment,
    ':id' => $collectionId,
    ':loan_id' => $loanId,
]);

$_SESSION['flash'] = 'Gestion actualizada.';
header('Location: index.php?page=loan_detail&id=' . $loanId);
exit;
