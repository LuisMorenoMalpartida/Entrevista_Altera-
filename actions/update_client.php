<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=loans');
    exit;
}

require_login();
if (!in_array(current_user_role(), ['ADMIN', 'COBRADOR'], true)) {
    $_SESSION['flash'] = 'No tienes permisos para editar clientes.';
    header('Location: index.php?page=loans');
    exit;
}

$clientId = (int) ($_POST['client_id'] ?? 0);
$tipo = trim($_POST['tipo'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$documentoTipo = trim($_POST['documento_tipo'] ?? '');
$documentoNumero = trim($_POST['documento_numero'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = trim($_POST['email'] ?? '');
$allowedTipos = ['PERSONA', 'NEGOCIO'];
$allowedDocTipos = ['DNI', 'RUC', 'PASAPORTE'];

if ($clientId <= 0 || !in_array($tipo, $allowedTipos, true) || !in_array($documentoTipo, $allowedDocTipos, true) || $nombre === '' || $documentoNumero === '' || $telefono === '' || $email === '') {
    $_SESSION['flash'] = 'Completa los datos del cliente.';
    header('Location: index.php?page=client_edit&id=' . $clientId);
    exit;
}

if ($tipo === 'NEGOCIO' && $documentoTipo !== 'RUC') {
    $_SESSION['flash'] = 'Para NEGOCIO el documento debe ser RUC.';
    header('Location: index.php?page=client_edit&id=' . $clientId);
    exit;
}

if ($tipo === 'PERSONA' && $documentoTipo === 'RUC') {
    $_SESSION['flash'] = 'Para PERSONA el documento debe ser DNI o PASAPORTE.';
    header('Location: index.php?page=client_edit&id=' . $clientId);
    exit;
}

$docLen = strlen($documentoNumero);
$lengthValid = false;

if ($documentoTipo === 'DNI') {
    $lengthValid = ($docLen === 8);
} elseif ($documentoTipo === 'RUC') {
    $lengthValid = ($docLen === 11);
} elseif ($documentoTipo === 'PASAPORTE') {
    $lengthValid = ($docLen >= 6 && $docLen <= 12);
}

if (!$lengthValid) {
    $_SESSION['flash'] = 'Longitud de documento invalida.';
    header('Location: index.php?page=client_edit&id=' . $clientId);
    exit;
}

$documento = $documentoTipo . ' ' . $documentoNumero;

$pdo = db();
$stmt = $pdo->prepare('UPDATE clientes SET tipo = :tipo, nombre = :nombre, documento = :documento, telefono = :telefono, email = :email WHERE id = :id');
$stmt->execute([
    ':tipo' => $tipo,
    ':nombre' => $nombre,
    ':documento' => $documento,
    ':telefono' => $telefono,
    ':email' => $email,
    ':id' => $clientId,
]);

$_SESSION['flash'] = 'Cliente actualizado.';
$loanId = (int) ($_POST['loan_id'] ?? 0);
if ($loanId > 0) {
    header('Location: index.php?page=loan_detail&id=' . $loanId);
    exit;
}

header('Location: index.php?page=loans');
exit;
