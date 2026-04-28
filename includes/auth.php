<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) session_start();

function isLoggedIn(): bool { return isset($_SESSION['user_id']); }
function isAdmin(): bool    { return isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; }

function requireLogin(): void {
    if (!isLoggedIn()) { header('Location: /rentit/login.php'); exit; }
}
function requireAdmin(): void {
    if (!isAdmin()) { header('Location: /rentit/index.php'); exit; }
}

function getCurrentUser(): array {
    return [
        'id'   => $_SESSION['user_id'] ?? null,
        'nom'  => $_SESSION['nom']     ?? '',
        'role' => $_SESSION['role']    ?? 'user',
    ];
}

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}
