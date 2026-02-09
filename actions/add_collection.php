<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=loans');
    exit;
}

require_login();
if (!in_array(current_user_role(), ['ADMIN', 'COBRADOR'], true)) {
    $_SESSION['flash'] = 'No tienes permisos para registrar gestiones.';
    header('Location: index.php?page=loans');
    exit;
}

$loanId = (int) ($_POST['prestamo_id'] ?? 0);
$managedAt = $_POST['fecha_hora'] ?? '';
$channel = trim($_POST['canal'] ?? '');
$result = trim($_POST['resultado'] ?? '');
$comment = trim($_POST['comentario'] ?? '');

if ($loanId <= 0 || $managedAt === '' || $channel === '' || $result === '' || $comment === '') {
    $_SESSION['flash'] = 'Completa los campos obligatorios.';
    header('Location: index.php?page=loan_detail&id=' . $loanId);
    exit;
}

$managedAt = str_replace('T', ' ', $managedAt);

$pdo = db();
$stmt = $pdo->prepare('INSERT INTO gestiones_cobranza (prestamo_id, fecha_hora, canal, resultado, comentario) VALUES (:loan_id, :managed_at, :channel, :result, :comment)');
$stmt->execute([
    ':loan_id' => $loanId,
    ':managed_at' => $managedAt,
    ':channel' => $channel,
    ':result' => $result,
    ':comment' => $comment,
]);

$_SESSION['flash'] = 'Gestion registrada.';
header('Location: index.php?page=loan_detail&id=' . $loanId);
exit;
