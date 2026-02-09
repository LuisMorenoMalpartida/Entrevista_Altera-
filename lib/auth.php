<?php

declare(strict_types=1);

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function current_user_role(): string
{
    return $_SESSION['role'] ?? 'GUEST';
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: index.php?page=login');
        exit;
    }
}

function login_user(string $username, string $role, int $userId): void
{
    $_SESSION['user'] = $username;
    $_SESSION['role'] = $role;
    $_SESSION['user_id'] = $userId;
}

function logout_user(): void
{
    unset($_SESSION['user']);
    unset($_SESSION['role']);
    unset($_SESSION['user_id']);
}
