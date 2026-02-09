<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=loan_new');
    exit;
}

require_login();
if (current_user_role() !== 'ADMIN') {
    $_SESSION['flash'] = 'No tienes permisos para crear clientes.';
    header('Location: index.php?page=loan_new');
    exit;
}

$tipo = trim($_POST['tipo'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$documentoTipo = trim($_POST['documento_tipo'] ?? '');
$documentoNumero = trim($_POST['documento_numero'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = trim($_POST['email'] ?? '');
$allowedTipos = ['PERSONA', 'NEGOCIO'];
$allowedDocTipos = ['DNI', 'RUC', 'PASAPORTE'];

if (!in_array($tipo, $allowedTipos, true) || !in_array($documentoTipo, $allowedDocTipos, true) || $nombre === '' || $documentoNumero === '' || $telefono === '' || $email === '') {
    $_SESSION['flash'] = 'Completa los datos del cliente.';
    header('Location: index.php?page=loan_new');
    exit;
}

if ($tipo === 'NEGOCIO' && $documentoTipo !== 'RUC') {
    $_SESSION['flash'] = 'Para NEGOCIO el documento debe ser RUC.';
    header('Location: index.php?page=loan_new');
    exit;
}

if ($tipo === 'PERSONA' && $documentoTipo === 'RUC') {
    $_SESSION['flash'] = 'Para PERSONA el documento debe ser DNI o PASAPORTE.';
    header('Location: index.php?page=loan_new');
    exit;
}

$documentoNumero = trim($documentoNumero);
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
    header('Location: index.php?page=loan_new');
    exit;
}

$documento = $documentoTipo . ' ' . $documentoNumero;

$pdo = db();
$stmt = $pdo->prepare('INSERT INTO clientes (tipo, nombre, documento, telefono, email) VALUES (:tipo, :nombre, :documento, :telefono, :email)');
$stmt->execute([
    ':tipo' => $tipo,
    ':nombre' => $nombre,
    ':documento' => $documento,
    ':telefono' => $telefono,
    ':email' => $email,
]);

$newClientId = (int) $pdo->lastInsertId();
$_SESSION['flash'] = 'Cliente creado.';
header('Location: index.php?page=loan_new&cliente_id=' . $newClientId);
exit;
