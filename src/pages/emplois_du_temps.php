<?php
require_once(__DIR__ . '/../prefabs/database_connection.php');

$filieres = $database->query("SELECT Id, Libelle FROM filieres ORDER BY Libelle")->fetchAll(PDO::FETCH_ASSOC);
$niveaux = $database->query("SELECT Id, Libelle FROM niveaux ORDER BY Id")->fetchAll(PDO::FETCH_ASSOC);
$groupes = $database->query(
    "SELECT g.Id, g.FiliereId, g.nivId, g.Libelle, f.Libelle AS Filiere, n.Libelle AS Niveau
     FROM groupes g
     LEFT JOIN filieres f ON f.Id = g.FiliereId
     LEFT JOIN niveaux n ON n.Id = g.nivId
     ORDER BY f.Libelle, n.Libelle, g.Libelle"
)->fetchAll(PDO::FETCH_ASSOC);

$selectedFiliereId = isset($_GET['filiere']) ? (int)$_GET['filiere'] : ($filieres[0]['Id'] ?? 0);
$selectedNiveauId = isset($_GET['niveau']) ? (int)$_GET['niveau'] : ($niveaux[0]['Id'] ?? 0);
$filteredGroups = array_values(array_filter($groupes, fn($g) => $g['FiliereId'] === $selectedFiliereId && $g['nivId'] === $selectedNiveauId));
$selectedGroupeId = isset($_GET['groupe']) ? (int)$_GET['groupe'] : ($filteredGroups[0]['Id'] ?? 0);

$selectedGroup = null;
foreach ($groupes as $group) {
    if ($group['Id'] === $selectedGroupeId) {
        $selectedGroup = $group;
        break;
    }
}

$selectedFiliere = null;
foreach ($filieres as $f) {
    if ($f['Id'] === $selectedFiliereId) {
        $selectedFiliere = $f;
        break;
    }
}

$selectedNiveau = null;
foreach ($niveaux as $n) {
    if ($n['Id'] === $selectedNiveauId) {
        $selectedNiveau = $n;
        break;
    }
}

$emploiEntries = [];
if ($selectedGroup && $selectedFiliere && $selectedNiveau) {
    $emploiStmt = $database->prepare(
        "SELECT e.GroupeId, e.NiveauId, e.FiliereId, e.MatiereId, e.Jour, e.HoraireId, e.EnsId, e.Salle, e.Type,
                m.Libelle AS Matiere, u.Prenom AS EnsPrenom, u.Nom AS EnsNom, h.HeureDebut, h.HeureFin
         FROM emplois e
         LEFT JOIN matieres m ON m.Id = e.MatiereId
         LEFT JOIN utilisateurs u ON u.Id = e.EnsId
         LEFT JOIN horaires h ON h.Id = e.HoraireId
         WHERE e.GroupeId = :groupId
           AND e.NiveauId = :niveauId
           AND e.FiliereId = :filiereId
         ORDER BY FIELD(e.Jour,'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'), h.HeureDebut"
    );
    $emploiStmt->execute([
        'groupId' => $selectedGroup['Id'],
        'niveauId' => $selectedNiveau['Id'],
        'filiereId' => $selectedFiliere['Id'],
    ]);
    $emploiEntries = $emploiStmt->fetchAll(PDO::FETCH_ASSOC);
}

$scheduleDays = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
$scheduleGrid = [];
$timetableSlots = [];

$horairesQuery = $database->query("SELECT HeureDebut, HeureFin FROM horaires ORDER BY HeureDebut ASC");
$horairesList = $horairesQuery->fetchAll(PDO::FETCH_ASSOC);
foreach ($horairesList as $horaire) {
    $timetableSlots[] = date('H:i', strtotime($horaire['HeureDebut'])) . ' - ' . date('H:i', strtotime($horaire['HeureFin']));
}

foreach ($emploiEntries as $entry) {
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Student / Timetables</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .panel-card { border: none; box-shadow: 0 0.75rem 1.5rem rgba(100, 100, 120, 0.08); }
    </style>
</head>
<body>
    <?php include_once(__DIR__ . '/../prefabs/header.php'); ?>
    <main class="container py-5">
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h1 class="display-6 fw-bold mb-1">Timetables</h1>
                    <p class="text-secondary mb-0">Select a filiere, niveau, and group to view the timetable.</p>
                </div>
                <a href="../index.php" class="btn btn-outline-secondary">Back to Home</a>
            </div>

            <div class="card panel-card">
                <div class="card-body p-4">
                    <form method="GET" class="row g-3 align-items-end mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Filiere</label>
                            <select class="form-select" name="filiere" onchange="this.form.submit()">
                                <?php foreach ($filieres as $filiere): ?>
                                    <option value="<?php echo $filiere['Id']; ?>" <?php echo $filiere['Id'] === $selectedFiliereId ? 'selected' : ''; ?>><?php echo htmlspecialchars($filiere['Libelle'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Niveau</label>
                            <select class="form-select" name="niveau" onchange="this.form.submit()">
                                <?php foreach ($niveaux as $niveau): ?>
                                    <option value="<?php echo $niveau['Id']; ?>" <?php echo $niveau['Id'] === $selectedNiveauId ? 'selected' : ''; ?>><?php echo htmlspecialchars($niveau['Libelle'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Group</label>
                            <select class="form-select" name="groupe" onchange="this.form.submit()">
                                <?php foreach ($filteredGroups as $group): ?>
                                    <option value="<?php echo $group['Id']; ?>" <?php echo $group['Id'] === $selectedGroupeId ? 'selected' : ''; ?>><?php echo htmlspecialchars($group['Libelle'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <?php if ($selectedGroup && $selectedFiliere && $selectedNiveau): ?>
                        <h2 class="h5 mb-3">Timetable for <?php echo htmlspecialchars($selectedFiliere['Libelle'] . ' / ' . $selectedNiveau['Libelle'] . ' / ' . $selectedGroup['Libelle'], ENT_QUOTES, 'UTF-8'); ?></h2>
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
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
    <script src="../js/bootstrap.js"></script>
</body>
</html>