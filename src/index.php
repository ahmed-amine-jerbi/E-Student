<?php
require_once(__DIR__ . '/prefabs/auth.php');

ensureSessionStarted();
redirectAuthenticatedUser('pages/dashboard.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Student / Acceuil</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
   <?php 
   include_once('prefabs/header.php');
   require_once('prefabs/database_connection.php');

   $filiereStatsQuery = $database->query("
        SELECT 
            f.Id,
            f.Libelle,
            COUNT(u.Id) AS total_registered
        FROM filieres f
        LEFT JOIN groupes g ON g.FiliereId = f.Id
        LEFT JOIN utilisateurs u ON u.GroupeId = g.Id
        GROUP BY f.Id, f.Libelle
        ORDER BY f.Libelle
   ");
   $filiereStats = $filiereStatsQuery->fetchAll(PDO::FETCH_ASSOC);
   $totalRegistered = array_sum(array_column($filiereStats, 'total_registered'));

   $announcementsQuery = $database->query(
        "SELECT Id, Titre, Contenu, DatePublication
         FROM announcements
         ORDER BY DatePublication DESC, Id DESC
         LIMIT 6"
   );
   $announcements = $announcementsQuery->fetchAll(PDO::FETCH_ASSOC);

   // Fetch default timetable for preview (first filiere, first niveau, first group)
   $defaultFiliere = $database->query("SELECT Id, Libelle FROM filieres ORDER BY Libelle LIMIT 1")->fetch(PDO::FETCH_ASSOC);
   $defaultNiveau = $database->query("SELECT Id, Libelle FROM niveaux ORDER BY Id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
   $defaultGroup = $database->query("SELECT Id, Libelle FROM groupes WHERE FiliereId = {$defaultFiliere['Id']} AND nivId = {$defaultNiveau['Id']} ORDER BY Libelle LIMIT 1")->fetch(PDO::FETCH_ASSOC);

   $emploi_du_temps = [];
   $scheduleDays = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
   $scheduleGrid = [];
   $timetableSlots = [];

   if ($defaultFiliere && $defaultNiveau && $defaultGroup) {
       $horairesQuery = $database->prepare("SELECT HeureDebut, HeureFin FROM horaires ORDER BY HeureDebut ASC");
       $horairesQuery->execute();
       $horairesList = $horairesQuery->fetchAll(PDO::FETCH_ASSOC);
       foreach ($horairesList as $horaire) {
           $timetableSlots[] = date('H:i', strtotime($horaire['HeureDebut'])) . ' - ' . date('H:i', strtotime($horaire['HeureFin']));
       }

       $emploiQuery = $database->prepare(
           "SELECT e.Jour, h.HeureDebut, h.HeureFin, m.Libelle AS Matiere, u.Prenom AS EnsPrenom, u.Nom AS EnsNom, e.Salle, e.Type
            FROM emplois e
            LEFT JOIN horaires h ON e.HoraireId = h.Id
            LEFT JOIN matieres m ON e.MatiereId = m.Id
            LEFT JOIN utilisateurs u ON e.EnsId = u.Id
            WHERE e.FiliereId = :filiereId AND e.NiveauId = :niveauId AND e.GroupeId = :groupeId
            ORDER BY FIELD(e.Jour,'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'), h.HeureDebut"
       );
       $emploiQuery->execute([
           'filiereId' => $defaultFiliere['Id'],
           'niveauId' => $defaultNiveau['Id'],
           'groupeId' => $defaultGroup['Id']
       ]);
       $emploi_du_temps = $emploiQuery->fetchAll(PDO::FETCH_ASSOC);

       foreach ($emploi_du_temps as $entry) {
           $slotLabel = date('H:i', strtotime($entry['HeureDebut'])) . ' - ' . date('H:i', strtotime($entry['HeureFin']));
           $scheduleGrid[$entry['Jour']][$slotLabel] = $entry;
       }

       foreach ($scheduleDays as $day) {
           if (!isset($scheduleGrid[$day])) {
               $scheduleGrid[$day] = [];
           }
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

   ?>

    <main class="container py-5">
        <section class="hero p-4 p-md-5 mb-5">
            <div class="row align-items-center g-4 position-relative">
                <div class="col-lg-7">
                    <span class="badge text-bg-light text-primary mb-3">Student and Teacher Workspace</span>
                    <h1 class="display-5 fw-bold">A simple classroom platform for homework, schedules, and messages.</h1>
                    <p class="lead text-white-50 mt-3">
                        Students can upload homework and follow class updates, while teachers can publish timetables and keep communication in one place.
                    </p>
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="pages/login.php" class="btn btn-warning btn-lg px-4">Open Dashboard</a>
                        <a href="#features" class="btn btn-outline-light btn-lg px-4">See Features</a>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card panel-card bg-white text-dark">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="h5 mb-0">Register Now</h2>
                                <span class="status-pill"><?php echo $totalRegistered; ?> Registered</span>
                            </div>
                            <div class="list-group list-group-flush">
                                <?php foreach ($filiereStats as $filiere): ?>
                                    <div class="list-group-item px-0 d-flex justify-content-between">
                                        <span><?php echo htmlspecialchars($filiere['Libelle']); ?></span>
                                        <strong><?php echo (int) $filiere['total_registered']; ?></strong>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="d-grid gap-3 mt-4">
                                <a href="pages/register.php" class="btn btn-primary btn-lg">Create Account</a>
                                <a href="pages/login.php" class="btn btn-outline-secondary btn-lg">Already a Member</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="mb-5">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="section-title mb-1">Core Features</h2>
                    <p class="text-secondary mb-0">A clean starting point for your school platform.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body p-4">
                            <div class="feature-icon mb-3">01</div>
                            <h3 class="h5">Communication</h3>
                            <p class="text-secondary mb-0">Communicate with your teachers and classmates.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body p-4">
                            <div class="feature-icon mb-3">02</div>
                            <h3 class="h5">Timetables</h3>
                            <p class="text-secondary mb-0">View schedules for each class and subject.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body p-4">
                            <div class="feature-icon mb-3">03</div>
                            <h3 class="h5">Assignments</h3>
                            <p class="text-secondary mb-0">Submit assignments and track their status in one place.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="dashboard" class="mb-5">
            <div class="row g-4">
                
                <div class="col-lg-12">
                        <div class="card panel-card h-100">
                            <div class="card-body p-4">
                                <h2 class="section-title h4 mb-3">Announcements</h2>
                                <?php if (empty($announcements)): ?>
                                    <p class="text-secondary mb-0">No announcements have been published yet.</p>
                                <?php else: ?>
                                    <div class="accordion" id="announcements">
                                        <?php foreach ($announcements as $index => $announcement): ?>
                                            <?php $panelId = 'announcement' . (int) $announcement['Id']; ?>
                                            <div class="accordion-item">
                                                <h3 class="accordion-header">
                                                    <button
                                                        class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>"
                                                        type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#<?php echo htmlspecialchars($panelId, ENT_QUOTES, 'UTF-8'); ?>"
                                                        aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                                                        aria-controls="<?php echo htmlspecialchars($panelId, ENT_QUOTES, 'UTF-8'); ?>"
                                                    >
                                                        <?php echo htmlspecialchars($announcement['Titre'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </button>
                                                </h3>
                                                <div
                                                    id="<?php echo htmlspecialchars($panelId, ENT_QUOTES, 'UTF-8'); ?>"
                                                    class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>"
                                                    data-bs-parent="#announcements"
                                                >
                                                    <div class="accordion-body">
                                                        <p class="small text-muted mb-2"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($announcement['DatePublication'])), ENT_QUOTES, 'UTF-8'); ?></p>
                                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($announcement['Contenu'], ENT_QUOTES, 'UTF-8')); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
            </div>
        </section>

        <section id="schedule" class="mb-5">
            <div class="card panel-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="section-title h4 mb-1">Timetable Preview</h2>
                            <p class="text-secondary mb-0">Sample timetable for <?php echo htmlspecialchars(($defaultFiliere['Libelle'] ?? 'N/A') . ' / ' . ($defaultNiveau['Libelle'] ?? 'N/A') . ' / ' . ($defaultGroup['Libelle'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?>. View all timetables below.</p>
                        </div>
                        <div class="text-md-end mt-3 mt-md-0">
                            <a href="pages/emplois_du_temps.php" class="btn btn-outline-primary">View All Timetables</a>
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

        <section id="login">
            <div class="card panel-card">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-7">
                            <h2 class="section-title h3">Ready to connect your class?</h2>
                            <p class="text-secondary mb-0">
                                Join us now to communicate with your teachers and classmates and stay up to date with all your class updates and schedules in one place.
                            </p>
                        </div>
                        <div class="col-lg-5">
                            <div class="d-grid gap-3">
                                <a href="pages/login.php" class="btn btn-primary btn-lg">Log In</a>
                                <a href="pages/register.php" class="btn btn-outline-secondary btn-lg">Create an Account</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="js/bootstrap.js"></script>
</body>
</html>
