<?php
/**
 * Dashboard page controller.
 *
 * Loads the authenticated user's classroom data, available channels,
 * grade previews, timetable sections, and assignment summaries.
 */
require_once(__DIR__ . '/../prefabs/auth.php'); 
require_once(__DIR__ . '/../prefabs/database_connection.php');

ensureSessionStarted();
requireAuthenticatedUser('login.php');

$currentUser = getAuthenticatedUser();

if ($currentUser['role'] === 'Etudiant') {
    require_once(__DIR__ . '/dashboards/etudiant.php');
} elseif ($currentUser['role'] === 'Enseignant') {
    require_once(__DIR__ . '/dashboards/enseignant.php');
} elseif ($currentUser['role'] === 'Administration') {
    require_once(__DIR__ . '/dashboards/administration.php');
}
?>