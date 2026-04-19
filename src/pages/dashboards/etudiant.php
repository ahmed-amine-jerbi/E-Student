<?php
$studentName = trim(($currentUser['prenom'] ?? '') . ' ' . ($currentUser['nom'] ?? ''));
$classParts = array_filter([
    $currentUser['filiere'] ?? '',
    $currentUser['niveau'] ?? '',
    $currentUser['groupe'] ?? '',
]);
$classLabel = !empty($classParts) ? implode(' / ', $classParts) : 'Your classroom';

$channels = [];
$joinedChannelIds = [];

// Fetch channels available for the student's filiere.
$channelQuery = $database->prepare(
    "SELECT c.Id, c.Libelle AS title, c.Description AS description, u.Prenom AS ensPrenom, u.Nom AS ensNom
         FROM channels c, utilisateurs u
         WHERE c.FiliereId = :filiereId
         AND u.Id = c.EnsId"
);
$channelQuery->execute(['filiereId' => $currentUser['filiere_id']]);
$channels = $channelQuery->fetchAll(PDO::FETCH_ASSOC);

$joinedChannelsQuery = $database->prepare(
    "SELECT jc.channelId FROM joined_channels jc WHERE jc.utilisateurId = :userId"
);
$joinedChannelsQuery->execute(['userId' => $currentUser['id']]);
$joinedChannelIds = $joinedChannelsQuery->fetchAll(PDO::FETCH_COLUMN, 0);

$grades = [];

$matieres = $database->prepare("SELECT Id, Libelle, Coefficient FROM matieres");
$matieres->execute();
$matieresList = $matieres->fetchAll(PDO::FETCH_ASSOC);

$notes = [];

foreach ($matieresList as $matiere) {
    $noteQuery = $database->prepare(
        "SELECT n.Valeur AS Note
         FROM notes n
         WHERE n.UserId = :userId AND n.MatiereId = :matiereId"
    );
    $noteQuery->execute(['userId' => $currentUser['id'], 'matiereId' => $matiere['Id']]);
    $noteData = $noteQuery->fetch(PDO::FETCH_ASSOC);
    if ($noteData) {
        $notes[] = [
            'module' => $matiere['Libelle'],
            'coefficient' => $matiere['Coefficient'],
            'grade' => $noteData['Note'],
        ];
    }
}

if (!empty($notes)) {
    foreach ($notes as $note) {
        $numericGrade = is_numeric($note['grade']) ? (float) $note['grade'] : null;
        $grades[] = [
            'module' => $note['module'],
            'coefficient' => $note['coefficient'],
            'valeur' => $note['grade'],
        ];
    }
}

$emploi_du_temps = $database->prepare(
    "SELECT e.Jour, h.HeureDebut, h.HeureFin, m.Libelle AS Matiere, u.Prenom AS EnsPrenom, u.Nom AS EnsNom, e.Salle, e.Type
     FROM emplois e, horaires h, matieres m, utilisateurs u
     WHERE e.MatiereId = m.Id
     AND e.HoraireId = h.Id
     AND e.FiliereId = :filiereId
     AND e.NiveauId = :niveauId
     AND e.GroupeId = :groupeId
     AND u.Id = e.EnsId"
);
$emploi_du_temps->execute([
    'filiereId' => $currentUser['filiere_id'],
    'niveauId' => $currentUser['niveau_id'],
    'groupeId' => $currentUser['groupe_id']
]);
$emploi = $emploi_du_temps->fetchAll(PDO::FETCH_ASSOC);

$scheduleDays = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
$scheduleGrid = [];
$timetableSlots = [];

$horairesQuery = $database->prepare(
    "SELECT HeureDebut, HeureFin FROM horaires ORDER BY HeureDebut ASC"
);
$horairesQuery->execute();
$horairesList = $horairesQuery->fetchAll(PDO::FETCH_ASSOC);
foreach ($horairesList as $horaire) {
    $timetableSlots[] = date('H:i', strtotime($horaire['HeureDebut'])) . ' - ' . date('H:i', strtotime($horaire['HeureFin']));
}

foreach ($emploi as $entry) {
    $slotLabel = date('H:i', strtotime($entry['HeureDebut'])) . ' - ' . date('H:i', strtotime($entry['HeureFin']));
    $scheduleGrid[$entry['Jour']][$slotLabel] = $entry;
}

foreach ($scheduleDays as $day) {
    if (!isset($scheduleGrid[$day])) {
        $scheduleGrid[$day] = [];
    }
}

$typeColors = [
    'Cours' => 'bg-primary bg-opacity-10 border-primary',
    'TD' => 'bg-success bg-opacity-10 border-success',
    'TP' => 'bg-warning bg-opacity-10 border-warning',
];

$typeBadges = [
    'Cours' => 'badge text-bg-primary',
    'TD' => 'badge text-bg-success',
    'TP' => 'badge text-bg-warning',
];

$devoirs = $database->prepare(
    "SELECT d.Titre AS title, d.Deadline AS deadline
     FROM devoirs d
     WHERE d.FiliereId = :FiliereId
     AND d.NiveauId = :NiveauId
     AND d.GroupeId = :GroupeId"
);
$devoirs->execute([
    'FiliereId' => $currentUser['filiere_id'],
    'NiveauId' => $currentUser['niveau_id'],
    'GroupeId' => $currentUser['groupe_id']
]);
$devoirsList = $devoirs->fetchAll(PDO::FETCH_ASSOC);

$announcementsQuery = $database->query(
    "SELECT Id, Titre, Contenu, DatePublication
     FROM announcements
     ORDER BY DatePublication DESC, Id DESC
     LIMIT 8"
);
$announcements = $announcementsQuery->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Student / Dashboard</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .assignment-expired { background-color: #ffdddd; }
        .assignment-active { background-color: #ddffdd; }
    </style>
</head>

<body id="top">
    <?php include_once('../prefabs/header.php'); ?>

    <main class="container py-5">
        <section class="hero p-4 p-md-5 mb-5">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <span class="badge text-bg-light text-primary mb-3">Session Active</span>
                    <h1 class="display-6 fw-bold">Welcome,
                        <?php echo htmlspecialchars($studentName !== '' ? $studentName : 'Student', ENT_QUOTES, 'UTF-8'); ?>
                    </h1>
                    <p class="lead text-white-50 mt-3 mb-4">
                        This is a static dashboard preview for your logged-in experience. You can use it to visualize
                        classroom access, grades, timetable blocks, and assignment tracking before wiring everything to
                        live data.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <span
                            class="status-pill text-bg-success"><?php echo htmlspecialchars($classLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span
                            class="status-pill text-bg-success"><?php echo htmlspecialchars($currentUser['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </div>
                
        </section>

        <section class="mb-5">
            <div class="card panel-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="section-title h4 mb-1">Announcements</h2>
                            <p class="text-secondary mb-0">Follow the latest academic and classroom updates published by administration.</p>
                        </div>
                    </div>
                    <?php if (empty($announcements)): ?>
                        <p class="text-secondary mb-0">No announcements are available right now.</p>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($announcements as $announcement): ?>
                                <div class="col-12">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                            <h3 class="h5 mb-0"><?php echo htmlspecialchars($announcement['Titre'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                            <span class="badge text-bg-light"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($announcement['DatePublication'])), ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                        <p class="text-secondary mb-0"><?php echo nl2br(htmlspecialchars($announcement['Contenu'], ENT_QUOTES, 'UTF-8')); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="section-title mb-1">Available Channels</h2>
                    <p class="text-secondary mb-0">Browse and join classroom channels taught by your instructors.</p>
                </div>
            </div>
            <div class="row g-4">
                
                <?php foreach ($channels as $channel) {
                    $isJoined = in_array($channel['Id'], $joinedChannelIds);
                    $teacherName = htmlspecialchars($channel['ensPrenom'] . ' ' . $channel['ensNom'], ENT_QUOTES, 'UTF-8');
                    ?>

                    <div class="col-md-6 col-xl-4">
                        <div class="card feature-card h-100">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h3 class="h5 mb-0"><?php echo htmlspecialchars($channel['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    </div>
                                    <?php if ($isJoined): ?>
                                        <span class="badge text-bg-success">Joined</span>
                                    <?php endif; ?>
                                </div>
                                <p class="small text-muted mb-2"><strong>Enseignant:</strong></p>
                                <p class="small mb-3"><?php echo $teacherName; ?></p>
                                <p class="text-secondary small mb-3">
                                    <?php echo htmlspecialchars($channel['description'] ?? 'No description', ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                                <a href="<?php echo $isJoined ? '../prefabs/ChannelView.php?id=' . $channel['Id'] : '../prefabs/JoinChannel.php?id=' . $channel['Id']; ?>"
                                    class="btn btn-outline-primary w-100">
                                    <?php echo $isJoined ? 'View Channel' : 'Join Channel'; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </section>

        <section id="notes" class="mb-5">
            <div class="card panel-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="section-title h4 mb-1">Grades Overview</h2>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <?php if (!empty($grades)){ ?>
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Coefficient</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <?php } ?>
                            <tbody>
                                <?php 
                                if (empty($grades)) { ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary">No grades available yet.</td>
                                    </tr>
                                <?php } else { 
                                foreach ($grades as $grade) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($grade['module'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($grade['coefficient'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><strong><?php echo htmlspecialchars($grade['valeur'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        </td>
                                        <td><?php
                                        $Status = 'Trés Bien';
                                        
                                        if ($grade['valeur'] >= 16) {
                                            $Status = 'Trés Bien';
                                        } elseif ($grade['valeur'] >= 14) {
                                            $Status = 'Bien';
                                        } elseif ($grade['valeur'] >= 12) {
                                            $Status = 'Assez Bien';
                                        } elseif ($grade['valeur'] >= 10) {
                                            $Status = 'Passable';
                                        } else {
                                            $Status = 'Insuffisant';
                                        }

                                        echo htmlspecialchars($Status, ENT_QUOTES, 'UTF-8');
                                        ?></td>
                                    </tr>
                                <?php }} ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <section id="schedule" class="mb-5">
            <div class="card panel-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="section-title h4 mb-1">Timetable</h2>
                            <p class="text-secondary mb-0">Your real weekly class schedule, with lesson type and room details.</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Jour</th>
                                    <?php foreach ($timetableSlots as $slot) { ?>
                                        <th scope="col"><?php echo htmlspecialchars($slot, ENT_QUOTES, 'UTF-8'); ?></th>
                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($scheduleDays as $day) { ?>
                                    <tr>
                                        <th class="text-start"><?php echo htmlspecialchars($day, ENT_QUOTES, 'UTF-8'); ?></th>
                                        <?php foreach ($timetableSlots as $slot) {
                                            $entry = $scheduleGrid[$day][$slot] ?? null;
                                            if ($entry) {
                                                $entryType = $entry['Type'] ?? 'Cours';
                                                $entryClass = $typeColors[$entryType] ?? 'bg-secondary bg-opacity-10 border-secondary';
                                                $badgeClass = $typeBadges[$entryType] ?? 'badge text-bg-secondary';
                                                ?>
                                                <td class="p-2">
                                                    <div class="border rounded-3 p-2 <?php echo $entryClass; ?>">
                                                        <div class="mb-1"><strong><?php echo htmlspecialchars($entry['Matiere'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
                                                        <div class="small text-muted mb-1"><?php echo htmlspecialchars($entry['EnsPrenom'] . ' ' . $entry['EnsNom'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <div class="small text-muted mb-2">Salle: <?php echo htmlspecialchars($entry['Salle'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <span class="<?php echo $badgeClass; ?>"><?php echo htmlspecialchars($entryType, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    </div>
                                                </td>
                                            <?php } else { ?>
                                                <td class="bg-white"></td>
                                            <?php } ?>
                                        <?php } ?>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <section id="assignments" class="mb-5">
            <div class="row g-4">
                    <div class="card panel-card h-100">
                        <div class="card-body p-4">
                            <h2 class="section-title h4 mb-3">Assignments</h2>
                            <div class="row g-3">
                                <?php 
                                if (empty($devoirsList)) { ?>
                                    <div class="col-12">
                                        <div class="border rounded-4 p-3 h-100">
                                            <p class="text-center text-secondary mb-0">No assignments found for your class.</p>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <?php foreach ($devoirsList as $assignment) { 
                                        $isExpired = strtotime($assignment['deadline']) < time();
                                    ?>
                                        <div class="col-md-6">
                                            <div class="<?php echo $isExpired ? 'assignment-expired' : 'assignment-active'; ?> border rounded-4 p-3 h-100">
                                                
                                            <h3 class="h6">
                                                <?php echo htmlspecialchars($assignment['title'], ENT_QUOTES, 'UTF-8'); ?>
                                            </h3>
                                            <p class="text-secondary mb-1"><strong>Deadline:</strong>
                                                <?php echo htmlspecialchars($assignment['deadline'], ENT_QUOTES, 'UTF-8'); ?>
                                            </p>
                                            <p class="mb-2"><strong>Status:
                                                <?php $status = $assignment['deadline'] > date('Y-m-d') ? 'En Cours' : 'Expirée'; echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></strong>
                                            </p>
                                        </div>
                                    </div>
                                <?php }} ?>
                            </div>
                        </div>
                    </div>
                
            </div>
        </section>
    </main>

    <script src="../js/bootstrap.js"></script>
</body>

</html>
