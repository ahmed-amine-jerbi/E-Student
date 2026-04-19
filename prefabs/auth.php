<?php

function ensureSessionStarted(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isUserLoggedIn(): bool
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']);
}

function getAuthenticatedUser(): ?array
{
    return isUserLoggedIn() ? $_SESSION['user'] : null;
}

function redirectAuthenticatedUser(string $target): void
{
    if (isUserLoggedIn()) {
        header('Location: ' . $target);
        exit;
    }
}

function requireAuthenticatedUser(string $target): void
{
    if (!isUserLoggedIn()) {
        header('Location: ' . $target);
        exit;
    }
}
