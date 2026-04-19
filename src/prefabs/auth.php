<?php

/**
 * Authentication helper utilities.
 *
 * These functions manage session state and protect pages
 * across the student/teacher portal.
 */

// Ensure a session is active before accessing authentication state.
function ensureSessionStarted(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
// Checks if a user is currently authenticated based on session data.
function isUserLoggedIn(): bool
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']);
}

// Retrieves the authenticated user's information from the session, or null if not authenticated.
function getAuthenticatedUser(): ?array
{
    return isUserLoggedIn() ? $_SESSION['user'] : null;
}

// Redirects authenticated users to a specified target page, preventing access to login/register pages.
function redirectAuthenticatedUser(string $target): void
{
    if (isUserLoggedIn()) {
        header('Location: ' . $target);
        exit;
    }
}

// Protects a page by redirecting unauthenticated users to a specified target page (e.g., login page).
function requireAuthenticatedUser(string $target): void
{
    if (!isUserLoggedIn()) {
        header('Location: ' . $target);
        exit;
    }
}
