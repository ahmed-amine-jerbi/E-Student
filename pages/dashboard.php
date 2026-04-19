<?php
require_once(__DIR__ . '/../prefabs/auth.php');
require_once(__DIR__ . '/../prefabs/database_connection.php');

ensureSessionStarted();
requireAuthenticatedUser('login.php');

$currentUser = getAuthenticatedUser();
$studentName = trim(($currentUser['prenom'] ?? '') . ' ' . ($currentUser['nom'] ?? ''));
$classParts = array_filter([
    $currentUser['filiere'] ?? '',
    $currentUser['niveau'] ?? '',
    $currentUser['groupe'] ?? '',
]);
$classLabel = !empty($classParts) ? implode(' / ', $classParts) : 'Your classroom';

$filiereQuery = $database->prepare("SELECT Id FROM filieres WHERE Libelle = :filiere LIMIT 1");
$filiereQuery->execute(['filiere' => $currentUser['filiere']]);
$filiere = $filiereQuery->fetch(PDO::FETCH_ASSOC);
$filiereId = $filiere['Id'];

$channelQuery = $database->prepare("
    SELECT
        c.Id,
        c.Libelle,
        c.Description,
    FROM channels c, utilisateurs u
    WHERE c.FiliereId = :filiereId
");
$channelQuery->execute(['filiereId' => $filiereId]);
$channels = $channelQuery->fetchAll(PDO::FETCH_ASSOC);

$joinedChannelsQuery = $database->prepare(
    "SELECT jc.channelId FROM joined_channels jc WHERE jc.utilisateurId = :userId"
);
$joinedChannelsQuery->execute(['userId' => $currentUser['id']]);
$joinedChannelIds = $joinedChannelsQuery->fetchAll(PDO::FETCH_COLUMN, 0);

$grades = [
    ['module' => 'Web Development', 'coefficient' => '3.0', 'grade' => '15.5', 'status' => 'Strong'],
    ['module' => 'Database Systems', 'coefficient' => '2.0', 'grade' => '14.0', 'status' => 'On Track'],
    ['module' => 'Business Communication', 'coefficient' => '1.5', 'grade' => '16.0', 'status' => 'Strong'],
    ['module' => 'Project Management', 'coefficient' => '2.5', 'grade' => '13.0', 'status' => 'Needs Review'],
];

$timetable = [
    ['day' => 'Monday', 'items' => ['08:00 - Web Development', '10:00 - Database Systems', '13:30 - English Communication']],
    ['day' => 'Tuesday', 'items' => ['09:00 - Statistics', '11:00 - Marketing Basics', '14:00 - Study Group']],
    ['day' => 'Wednesday', 'items' => ['08:30 - Programming Lab', '11:00 - Project Workshop', '15:00 - Classroom Check-in']],
    ['day' => 'Thursday', 'items' => ['08:00 - Business Intelligence', '10:30 - Systems Analysis', '14:00 - Presentation Skills']],
];

$assignments = [
    ['title' => 'Database Modeling Exercise', 'course' => 'Database Systems', 'deadline' => 'Friday, 18:00', 'status' => 'Draft ready'],
    ['title' => 'Team Pitch Slides', 'course' => 'Project Management', 'deadline' => 'Monday, 10:00', 'status' => 'Waiting for review'],
    ['title' => 'Weekly Reflection Post', 'course' => 'Business Communication', 'deadline' => 'Sunday, 20:00', 'status' => 'Not started'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Re:Classify - Dashboard</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body id="top">
    <?php include_once('../prefabs/header.php'); ?>

    <main class="container py-5">
        <section class="hero p-4 p-md-5 mb-5">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <span class="badge text-bg-light text-primary mb-3">Session Active</span>
                    <h1 class="display-6 fw-bold">Welcome, <?php echo htmlspecialchars($studentName !== '' ? $studentName : 'Student', ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p class="lead text-white-50 mt-3 mb-4">
                        This is a static dashboard preview for your logged-in experience. You can use it to visualize classroom access, grades, timetable blocks, and assignment tracking before wiring everything to live data.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <span class="status-pill"><?php echo htmlspecialchars($classLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="status-pill"><?php echo htmlspecialchars($currentUser['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card panel-card bg-white text-dark">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-3">Quick Snapshot</h2>
                            <div class="d-grid gap-3">
                                <div class="d-flex justify-content-between">
                                    <span>Active channels</span>
                                    <strong>3</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Pending assignments</span>
                                    <strong>3</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Average grade</span>
                                    <strong>14.6 / 20</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Next class</span>
                                    <strong>08:00 Monday</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="section-title mb-1">Join Your Classrooms</h2>
                    <p class="text-secondary mb-0">Static previews of the channels and classroom spaces tied to your class.</p>
                </div>
            </div>
            <div class="row g-4">
                <?php foreach ($channels as $channel) {
                     $action = in_array($channel['Id'], $joinedChannelIds) ? 'Join Channel' : 'View';
                    ?>
               
                    <div class="col-md-6 col-xl-4">
                        <div class="card feature-card h-100">
                            <div class="card-body p-4">
                                <div class="feature-icon mb-3">CH</div>
                                <h3 class="h5"><?php echo htmlspecialchars($channel['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p class="text-secondary"><?php echo htmlspecialchars($channel['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <a href="#" class="btn btn-outline-primary"><?php echo htmlspecialchars($action, ENT_QUOTES, 'UTF-8'); ?></a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </section>

        <section class="mb-5">
            <div class="card panel-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="section-title h4 mb-1">Grades Overview</h2>
                            <p class="text-secondary mb-0">Example grade cards for the logged-in student dashboard.</p>
                        </div>
                        <span class="status-pill">Semester Preview</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Coefficient</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grades as $grade) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($grade['module'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($grade['coefficient'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><strong><?php echo htmlspecialchars($grade['grade'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($grade['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <section id="timetable" class="mb-5">
            <div class="card panel-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="section-title h4 mb-1">Timetable</h2>
                            <p class="text-secondary mb-0">A static weekly layout to preview how class planning can look.</p>
                        </div>
                        <button type="button" class="btn btn-outline-primary">Export Preview</button>
                    </div>
                    <div class="row g-3">
                        <?php foreach ($timetable as $day) { ?>
                            <div class="col-md-6 col-xl-3">
                                <div class="p-3 rounded-4 bg-light h-100">
                                    <h3 class="h6 mb-3"><?php echo htmlspecialchars($day['day'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <?php foreach ($day['items'] as $item) { ?>
                                        <p class="mb-2"><?php echo htmlspecialchars($item, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-5">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card panel-card h-100">
                        <div class="card-body p-4">
                            <h2 class="section-title h4 mb-3">Assignments</h2>
                            <div class="row g-3">
                                <?php foreach ($assignments as $assignment) { ?>
                                    <div class="col-md-6">
                                        <div class="border rounded-4 p-3 h-100">
                                            <span class="status-pill mb-3 d-inline-flex"><?php echo htmlspecialchars($assignment['course'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            <h3 class="h6"><?php echo htmlspecialchars($assignment['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                            <p class="text-secondary mb-2">Deadline: <?php echo htmlspecialchars($assignment['deadline'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="mb-3">Status: <strong><?php echo htmlspecialchars($assignment['status'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                                            <a href="#" class="btn btn-sm btn-outline-secondary">View Details</a>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card panel-card h-100">
                        <div class="card-body p-4">
                            <h2 class="section-title h4 mb-3">Next Steps</h2>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0">Connect classroom joins to real channels.</li>
                                <li class="list-group-item px-0">Replace preview grades with database results.</li>
                                <li class="list-group-item px-0">Load assignments dynamically for each class.</li>
                                <li class="list-group-item px-0">Sync timetable changes from administration.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="../js/bootstrap.js"></script>
</body>
</html>
