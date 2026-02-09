<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=user_new');
    exit;
}

require_login();
if (current_user_role() !== 'ADMIN') {
    $_SESSION['flash'] = 'No tienes permisos para crear usuarios.';
    header('Location: index.php?page=dashboard');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$role = trim($_POST['role'] ?? '');
$allowedRoles = ['ADMIN', 'COBRADOR', 'SUPERVISOR', 'AUDITOR'];

if ($username === '' || $password === '' || !in_array($role, $allowedRoles, true)) {
    $_SESSION['flash'] = 'Completa los campos obligatorios.';
    header('Location: index.php?page=user_new');
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['flash'] = 'El password debe tener al menos 6 caracteres.';
    header('Location: index.php?page=user_new');
    exit;
}

$pdo = db();
$exists = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE username = :username');
$exists->execute([':username' => $username]);
if ((int) $exists->fetchColumn() > 0) {
    $_SESSION['flash'] = 'El usuario ya existe.';
    header('Location: index.php?page=user_new');
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO usuarios (username, password_hash, role) VALUES (:username, :password_hash, :role)');
$stmt->execute([
    ':username' => $username,
    ':password_hash' => $hash,
    ':role' => $role,
]);

$_SESSION['flash'] = 'Usuario creado.';
header('Location: index.php?page=user_new');
exit;
