<?php

$config = require __DIR__ . '/../config.php';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($config['app_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/styles.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Cobranza Mini</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navBar" aria-controls="navBar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navBar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php?page=dashboard">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=loans">Prestamos</a></li>
            </ul>
            <?php if (isset($_SESSION['user'])): ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>
                    <a class="btn btn-outline-light btn-sm me-2" href="index.php?page=user_new">Usuarios</a>
                <?php endif; ?>
                <span class="navbar-text me-3">Hola, <?= htmlspecialchars($_SESSION['user']) ?> (<?= htmlspecialchars($_SESSION['role'] ?? '') ?>)</span>
                <a class="btn btn-outline-light btn-sm" href="index.php?action=logout">Salir</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div class="container mb-5">
