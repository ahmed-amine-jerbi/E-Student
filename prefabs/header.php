<?php
require_once(__DIR__ . '/auth.php');

$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$isPagesContext = strpos($scriptPath, '/pages/') !== false;
$rootPrefix = $isPagesContext ? '../' : '';
$pagesPrefix = $isPagesContext ? '' : 'pages/';
$prefabsPrefix = $isPagesContext ? '../prefabs/' : 'prefabs/';
$isLoggedIn = isUserLoggedIn();
$currentUser = getAuthenticatedUser();
$displayName = $currentUser ? trim(($currentUser['prenom'] ?? '') . ' ' . ($currentUser['nom'] ?? '')) : '';

$homeHref = $rootPrefix . 'index.php';
$featuresHref = $homeHref . '#features';
$dashboardHref = $isLoggedIn ? $pagesPrefix . 'dashboard.php' : $pagesPrefix . 'login.php';
$scheduleHref = $isLoggedIn ? $pagesPrefix . 'dashboard.php#timetable' : $homeHref . '#schedule';
$authHref = $isLoggedIn ? $prefabsPrefix . 'logout.php' : $pagesPrefix . 'login.php';
$authLabel = $isLoggedIn ? 'Signout' : 'Login';
$authClass = $isLoggedIn ? 'btn btn-outline-danger ms-lg-2 px-4' : 'btn btn-primary ms-lg-2 px-4';
?>
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container py-2">
        <a class="navbar-brand fw-bold text-primary" href="<?php echo htmlspecialchars($homeHref, ENT_QUOTES, 'UTF-8'); ?>">Re:Classify</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($featuresHref, ENT_QUOTES, 'UTF-8'); ?>">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($dashboardHref, ENT_QUOTES, 'UTF-8'); ?>">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($scheduleHref, ENT_QUOTES, 'UTF-8'); ?>">Schedule</a></li>
                <?php if ($isLoggedIn && $displayName !== '') { ?>
                    <li class="nav-item"><span class="nav-link text-secondary">Hi, <?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></span></li>
                <?php } ?>
                <li class="nav-item"><a class="<?php echo htmlspecialchars($authClass, ENT_QUOTES, 'UTF-8'); ?>" href="<?php echo htmlspecialchars($authHref, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($authLabel, ENT_QUOTES, 'UTF-8'); ?></a></li>
            </ul>
        </div>
    </div>
</nav>
