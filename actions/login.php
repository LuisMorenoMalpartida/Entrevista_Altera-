<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=login');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

$pdo = db();
$tableExists = false;
$usersCount = 0;

try {
    $tableExists = (bool) $pdo->query("SHOW TABLES LIKE 'usuarios'")->fetch();
} catch (Throwable $e) {
    $tableExists = false;
}

if ($tableExists) {
    $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM usuarios WHERE username = :username');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();
    $usersCount = (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();

    if ($user && password_verify($password, $user['password_hash'])) {
        login_user($user['username'], $user['role'], (int) $user['id']);
        header('Location: index.php?page=dashboard');
        exit;
    }
}

if (!$tableExists || $usersCount === 0) {
    if ($username === $config['auth']['username'] && $password === $config['auth']['password']) {
        if ($tableExists) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare('INSERT INTO usuarios (username, password_hash, role) VALUES (:username, :password_hash, :role)');
            $insert->execute([
                ':username' => $username,
                ':password_hash' => $hash,
                ':role' => 'ADMIN',
            ]);
        }

        login_user($username, 'ADMIN', 0);
        header('Location: index.php?page=dashboard');
        exit;
    }
}

$error = 'Credenciales invalidas.';
include __DIR__ . '/../partials/header.php';
include __DIR__ . '/../pages/login.php';
include __DIR__ . '/../partials/footer.php';
exit;
