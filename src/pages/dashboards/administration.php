<?php
/**
 * Administration dashboard for user, channel, and timetable management.
 */
$adminName = trim(($currentUser['prenom'] ?? '') . ' ' . ($currentUser['nom'] ?? ''));
$actionSuccess = '';
$actionError = '';

function normalizeDateTimeInput(string $value): string
{
    $normalized = trim(str_replace('T', ' ', $value));
    if ($normalized !== '' && strlen($normalized) === 16) {
        $normalized .= ':00';
    }

    return $normalized;
}

function buildDashboardQuery(array $overrides = []): string
{
    $query = array_merge($_GET, $overrides);
    foreach ($query as $key => $value) {
        if ($value === null || $value === '') {
            unset($query[$key]);
        }
    }

    return '?' . http_build_query($query);
}

$filieres = $database->query("SELECT Id, Libelle FROM filieres ORDER BY Libelle")->fetchAll(PDO::FETCH_ASSOC);
$niveaux = $database->query("SELECT Id, Libelle FROM niveaux ORDER BY Id")->fetchAll(PDO::FETCH_ASSOC);
$groupes = $database->query(
    "SELECT g.Id, g.FiliereId, g.nivId, g.Libelle, f.Libelle AS Filiere, n.Libelle AS Niveau
     FROM groupes g
     LEFT JOIN filieres f ON f.Id = g.FiliereId
     LEFT JOIN niveaux n ON n.Id = g.nivId
     ORDER BY f.Libelle, n.Libelle, g.Libelle"
)->fetchAll(PDO::FETCH_ASSOC);
$matieres = $database->query("SELECT Id, Libelle FROM matieres ORDER BY Libelle")->fetchAll(PDO::FETCH_ASSOC);
$horaires = $database->query("SELECT Id, HeureDebut, HeureFin FROM horaires ORDER BY HeureDebut")->fetchAll(PDO::FETCH_ASSOC);
$enseignantsStmt = $database->prepare(
    "SELECT u.Id, u.Prenom, u.Nom, u.Email, u.GroupeId, g.Libelle AS Groupe, f.Libelle AS Filiere, n.Libelle AS Niveau
     FROM utilisateurs u
     LEFT JOIN groupes g ON g.Id = u.GroupeId
     LEFT JOIN filieres f ON f.Id = g.FiliereId
     LEFT JOIN niveaux n ON n.Id = g.nivId
     WHERE u.Role = 'Enseignant'
     ORDER BY u.Nom, u.Prenom"
);
$enseignantsStmt->execute();
$enseignants = $enseignantsStmt->fetchAll(PDO::FETCH_ASSOC);

$channelsStmt = $database->query(
    "SELECT c.Id, c.Libelle, c.Description, c.Cle, f.Libelle AS Filiere, n.Libelle AS Niveau, g.Libelle AS Groupe, u.Prenom AS EnsPrenom, u.Nom AS EnsNom
     FROM channels c
     LEFT JOIN filieres f ON f.Id = c.FiliereId
     LEFT JOIN niveaux n ON n.Id = c.NiveauId
     LEFT JOIN groupes g ON g.Id = c.GroupeId
     LEFT JOIN utilisateurs u ON u.Id = c.EnsId
     ORDER BY c.Id DESC"
);
$channels = $channelsStmt->fetchAll(PDO::FETCH_ASSOC);

$groupLookup = [];
foreach ($groupes as $group) {
    $groupLookup[$group['Id']] = $group;
}

$selectedFiliereId = isset($_GET['filiere']) ? (int)$_GET['filiere'] : ($filieres[0]['Id'] ?? 0);
$selectedNiveauId = isset($_GET['niveau']) ? (int)$_GET['niveau'] : ($niveaux[0]['Id'] ?? 0);
$filteredGroups = array_values(array_filter($groupes, fn($g) => $g['FiliereId'] === $selectedFiliereId && $g['nivId'] === $selectedNiveauId));
$selectedGroupeId = isset($_GET['groupe']) ? (int)$_GET['groupe'] : ($filteredGroups[0]['Id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_teacher') {
        $prenom = trim($_POST['prenom'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $groupeId = (int)($_POST['groupe_id'] ?? 0);

        if ($prenom === '' || $nom === '' || $email === '' || $password === '' || $groupeId <= 0) {
            $actionError = 'All teacher fields are required.';
        } else {
            $existing = $database->prepare("SELECT Id FROM utilisateurs WHERE Email = :email");
            $existing->execute(['email' => $email]);
            if ($existing->fetch()) {
                $actionError = 'A user with that email already exists.';
            } else {
                $insertTeacher = $database->prepare(
                    "INSERT INTO utilisateurs (Prenom, Nom, Email, Password, Role, GroupeId, dateInscription)
                     VALUES (:prenom, :nom, :email, :password, 'Enseignant', :groupeId, :dateInscription)"
                );
                $insertTeacher->execute([
                    'prenom' => $prenom,
                    'nom' => $nom,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'groupeId' => $groupeId,
                    'dateInscription' => date('Y-m-d H:i:s'),
                ]);
                $actionSuccess = 'Teacher added successfully.';
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit;
            }
        }
    }

    if ($action === 'delete_teacher') {
        $teacherId = (int)($_POST['teacher_id'] ?? 0);
        if ($teacherId > 0) {
            $deleteTeacher = $database->prepare("DELETE FROM utilisateurs WHERE Id = :id AND Role = 'Enseignant'");
            $deleteTeacher->execute(['id' => $teacherId]);
            $actionSuccess = 'Teacher removed successfully.';
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

    if ($action === 'delete_channel') {
        $channelId = (int)($_POST['channel_id'] ?? 0);
        if ($channelId > 0) {
            $deleteChannel = $database->prepare("DELETE FROM channels WHERE Id = :id");
            $deleteChannel->execute(['id' => $channelId]);
            $actionSuccess = 'Channel deleted successfully.';
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

    if ($action === 'create_announcement') {
        $title = trim($_POST['announcement_title'] ?? '');
        $content = trim($_POST['announcement_content'] ?? '');
        $announcementDate = normalizeDateTimeInput($_POST['announcement_date'] ?? '');

        if ($title === '' || $content === '' || $announcementDate === '') {
            $actionError = 'Announcement title, content, and date are required.';
        } else {
            $insertAnnouncement = $database->prepare(
                "INSERT INTO announcements (Titre, Contenu, DatePublication)
                 VALUES (:titre, :contenu, :datePublication)"
            );
            $insertAnnouncement->execute([
                'titre' => $title,
                'contenu' => $content,
                'datePublication' => $announcementDate,
            ]);
            $actionSuccess = 'Announcement created successfully.';
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

    if ($action === 'update_announcement') {
        $announcementId = (int)($_POST['announcement_id'] ?? 0);
        $title = trim($_POST['announcement_title'] ?? '');
        $content = trim($_POST['announcement_content'] ?? '');
        $announcementDate = normalizeDateTimeInput($_POST['announcement_date'] ?? '');

        if ($announcementId <= 0 || $title === '' || $content === '' || $announcementDate === '') {
            $actionError = 'Announcement title, content, and date are required.';
        } else {
            $updateAnnouncement = $database->prepare(
                "UPDATE announcements
                 SET Titre = :titre, Contenu = :contenu, DatePublication = :datePublication
                 WHERE Id = :id"
            );
            $updateAnnouncement->execute([
                'titre' => $title,
                'contenu' => $content,
                'datePublication' => $announcementDate,
                'id' => $announcementId,
            ]);
            $actionSuccess = 'Announcement updated successfully.';
            header('Location: ' . buildDashboardQuery(['announcement_edit' => null]));
            exit;
        }
    }

    if ($action === 'delete_announcement') {
        $announcementId = (int)($_POST['announcement_id'] ?? 0);
        if ($announcementId > 0) {
            $deleteAnnouncement = $database->prepare("DELETE FROM announcements WHERE Id = :id");
            $deleteAnnouncement->execute(['id' => $announcementId]);
            $actionSuccess = 'Announcement deleted successfully.';
            header('Location: ' . buildDashboardQuery(['announcement_edit' => null]));
            exit;
        }
    }

    if ($action === 'add_timetable') {
        $filiereId = (int)($_POST['filiere_id'] ?? 0);
        $niveauId = (int)($_POST['niveau_id'] ?? 0);
        $groupeId = (int)($_POST['groupe_id'] ?? 0);
        $matiereId = (int)($_POST['matiere_id'] ?? 0);
        $jour = $_POST['jour'] ?? '';
        $horaireId = (int)($_POST['horaire_id'] ?? 0);
        $enseignantId = (int)($_POST['enseignant_id'] ?? 0);
        $salle = trim($_POST['salle'] ?? '');
        $type = $_POST['type'] ?? '';

        if ($filiereId <= 0 || $niveauId <= 0 || $groupeId <= 0 || $matiereId <= 0 || $jour === '' || $horaireId <= 0 || $enseignantId <= 0 || $salle === '' || $type === '') {
            $actionError = 'All timetable fields are required.';
        } else {
            $insertEmploi = $database->prepare(
                "INSERT INTO emplois (GroupeId, NiveauId, FiliereId, MatiereId, Jour, HoraireId, EnsId, Salle, Type)
                 VALUES (:groupeId, :niveauId, :filiereId, :matiereId, :jour, :horaireId, :ensId, :salle, :type)"
            );
            $insertEmploi->execute([
                'groupeId' => $groupeId,
                'niveauId' => $niveauId,
                'filiereId' => $filiereId,
                'matiereId' => $matiereId,
                'jour' => $jour,
                'horaireId' => $horaireId,
                'ensId' => $enseignantId,
                'salle' => $salle,
                'type' => $type,
            ]);
            $actionSuccess = 'Timetable entry added successfully.';
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

    if ($action === 'delete_timetable') {
        $groupId = (int)($_POST['group_id'] ?? 0);
        $niveauId = (int)($_POST['niveau_id'] ?? 0);
        $filiereId = (int)($_POST['filiere_id'] ?? 0);
        $matiereId = (int)($_POST['matiere_id'] ?? 0);
        $jour = $_POST['jour'] ?? '';
        $horaireId = (int)($_POST['horaire_id'] ?? 0);

        if ($groupId > 0 && $niveauId > 0 && $filiereId > 0 && $matiereId > 0 && $jour !== '' && $horaireId > 0) {
            $deleteEmploi = $database->prepare(
                "DELETE FROM emplois WHERE GroupeId = :groupId AND NiveauId = :niveauId AND FiliereId = :filiereId AND MatiereId = :matiereId AND Jour = :jour AND HoraireId = :horaireId"
            );
            $deleteEmploi->execute([
                'groupId' => $groupId,
                'niveauId' => $niveauId,
                'filiereId' => $filiereId,
                'matiereId' => $matiereId,
                'jour' => $jour,
                'horaireId' => $horaireId,
            ]);
            $actionSuccess = 'Timetable entry deleted successfully.';
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
}

$selectedGroup = $groupLookup[$selectedGroupeId] ?? null;
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

$announcementsStmt = $database->query(
    "SELECT Id, Titre, Contenu, DatePublication
     FROM announcements
     ORDER BY DatePublication DESC, Id DESC"
);
$announcements = $announcementsStmt->fetchAll(PDO::FETCH_ASSOC);

$editingAnnouncementId = isset($_GET['announcement_edit']) ? (int) $_GET['announcement_edit'] : 0;
$editingAnnouncement = null;
foreach ($announcements as $announcement) {
    if ((int) $announcement['Id'] === $editingAnnouncementId) {
        $editingAnnouncement = $announcement;
        break;
    }
}

function formatTimeSlot(array $horaire): string
{
    return date('H:i', strtotime($horaire['HeureDebut'])) . ' - ' . date('H:i', strtotime($horaire['HeureFin']));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Student / Administration Dashboard</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .status-pill { padding: 0.4rem 0.75rem; border-radius: 999px; font-size: 0.9rem; }
        .admin-hero { background: #f8f9fa; }
        .panel-card { border: none; box-shadow: 0 0.75rem 1.5rem rgba(100, 100, 120, 0.08); }
    </style>
</head>
<body id="top">
    <?php include_once(__DIR__ . '/../../prefabs/header.php'); ?>
    <main class="container py-5">
        <section class="hero p-4 p-md-5 mb-5 admin-hero rounded-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <span class="badge text-bg-light text-primary mb-3">Administration Dashboard</span>
                    <h1 class="display-6 fw-bold">Welcome, <?php echo htmlspecialchars($adminName ?: 'Administrator', ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p class="lead text-secondary mt-3 mb-4">
                        Manage teachers, channels, and timetables across every filiere, niveau, and group.
                    </p>
                     <div class="d-flex flex-wrap gap-3">
                         <span class="status-pill bg-white border">Teachers: <?php echo count($enseignants); ?></span>
                         <span class="status-pill bg-white border">Channels: <?php echo count($channels); ?></span>
                         <span class="status-pill bg-white border">Announcements: <?php echo count($announcements); ?></span>
                         <span class="status-pill bg-white border">Groups: <?php echo count($groupes); ?></span>
                     </div>
                 </div>
             </div>
         </section>

        <?php if ($actionSuccess): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($actionSuccess, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($actionError): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($actionError, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="card panel-card h-100">
                    <div class="card-body p-4">
                        <h2 class="section-title h5 mb-4">Add New Teacher</h2>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_teacher">
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="prenom" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="nom" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Assign Group</label>
                                <select class="form-select" name="groupe_id" required>
                                    <option value="">Choose group</option>
                                    <?php foreach ($groupes as $group): ?>
                                        <option value="<?php echo $group['Id']; ?>"><?php echo htmlspecialchars($group['Filiere'] . ' / ' . $group['Niveau'] . ' / ' . $group['Libelle'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Create Teacher</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card panel-card h-100">
                    <div class="card-body p-4">
                        <h2 class="section-title h5 mb-4">Teachers</h2>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Class</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enseignants as $teacher): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($teacher['Prenom'] . ' ' . $teacher['Nom'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['Email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars(($teacher['Filiere'] ?? 'N/A') . ' / ' . ($teacher['Niveau'] ?? 'N/A') . ' / ' . ($teacher['Groupe'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline-block">
                                                    <input type="hidden" name="action" value="delete_teacher">
                                                    <input type="hidden" name="teacher_id" value="<?php echo $teacher['Id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-5">
                <div class="card panel-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="section-title h5 mb-0"><?php echo $editingAnnouncement ? 'Edit Announcement' : 'Create Announcement'; ?></h2>
                            <?php if ($editingAnnouncement): ?>
                                <a href="<?php echo htmlspecialchars(buildDashboardQuery(['announcement_edit' => null]), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo $editingAnnouncement ? 'update_announcement' : 'create_announcement'; ?>">
                            <?php if ($editingAnnouncement): ?>
                                <input type="hidden" name="announcement_id" value="<?php echo (int) $editingAnnouncement['Id']; ?>">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="announcement_title" class="form-control" value="<?php echo htmlspecialchars($editingAnnouncement['Titre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Content</label>
                                <textarea name="announcement_content" class="form-control" rows="6" required><?php echo htmlspecialchars($editingAnnouncement['Contenu'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input
                                    type="datetime-local"
                                    name="announcement_date"
                                    class="form-control"
                                    value="<?php echo !empty($editingAnnouncement['DatePublication']) ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($editingAnnouncement['DatePublication'])), ENT_QUOTES, 'UTF-8') : ''; ?>"
                                    required
                                >
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><?php echo $editingAnnouncement ? 'Update Announcement' : 'Publish Announcement'; ?></button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card panel-card h-100">
                    <div class="card-body p-4">
                        <h2 class="section-title h5 mb-4">Announcements</h2>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Date</th>
                                        <th>Content</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($announcements)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-secondary">No announcements published yet.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($announcements as $announcement): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($announcement['Titre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($announcement['DatePublication'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo nl2br(htmlspecialchars($announcement['Contenu'], ENT_QUOTES, 'UTF-8')); ?></td>
                                                <td class="text-nowrap">
                                                    <a href="<?php echo htmlspecialchars(buildDashboardQuery(['announcement_edit' => $announcement['Id']]), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                    <form method="POST" class="d-inline-block">
                                                        <input type="hidden" name="action" value="delete_announcement">
                                                        <input type="hidden" name="announcement_id" value="<?php echo (int) $announcement['Id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="card panel-card">
                    <div class="card-body p-4">
                        <h2 class="section-title h5 mb-4">Timetable Management</h2>
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

                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle text-center table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Day</th>
                                        <th>Time</th>
                                        <th>Subject</th>
                                        <th>Teacher</th>
                                        <th>Room</th>
                                        <th>Type</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($emploiEntries)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-secondary">No timetable entries for this group.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($emploiEntries as $entry): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($entry['Jour'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars(date('H:i', strtotime($entry['HeureDebut'])) . ' - ' . date('H:i', strtotime($entry['HeureFin'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($entry['Matiere'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($entry['EnsPrenom'] . ' ' . $entry['EnsNom'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($entry['Salle'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($entry['Type'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <form method="POST" class="d-inline-block">
                                                        <input type="hidden" name="action" value="delete_timetable">
                                                        <input type="hidden" name="group_id" value="<?php echo $entry['GroupeId']; ?>">
                                                        <input type="hidden" name="niveau_id" value="<?php echo $entry['NiveauId']; ?>">
                                                        <input type="hidden" name="filiere_id" value="<?php echo $entry['FiliereId']; ?>">
                                                        <input type="hidden" name="matiere_id" value="<?php echo $entry['MatiereId']; ?>">
                                                        <input type="hidden" name="jour" value="<?php echo htmlspecialchars($entry['Jour'], ENT_QUOTES, 'UTF-8'); ?>">
                                                        <input type="hidden" name="horaire_id" value="<?php echo $entry['HoraireId']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="card panel-card bg-light border">
                            <div class="card-body">
                                <h3 class="h6 mb-3">Add Timetable Entry</h3>
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="action" value="add_timetable">
                                    <div class="col-md-3">
                                        <label class="form-label">Filiere</label>
                                        <select class="form-select" name="filiere_id" required>
                                            <?php foreach ($filieres as $filiere): ?>
                                                <option value="<?php echo $filiere['Id']; ?>" <?php echo $filiere['Id'] === $selectedFiliereId ? 'selected' : ''; ?>><?php echo htmlspecialchars($filiere['Libelle'], ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Niveau</label>
                                        <select class="form-select" name="niveau_id" required>
                                            <?php foreach ($niveaux as $niveau): ?>
                                                <option value="<?php echo $niveau['Id']; ?>" <?php echo $niveau['Id'] === $selectedNiveauId ? 'selected' : ''; ?>><?php echo htmlspecialchars($niveau['Libelle'], ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Group</label>
                                        <select class="form-select" name="groupe_id" required>
                                            <?php foreach ($filteredGroups as $group): ?>
                                                <option value="<?php echo $group['Id']; ?>" <?php echo $group['Id'] === $selectedGroupeId ? 'selected' : ''; ?>><?php echo htmlspecialchars($group['Libelle'], ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Subject</label>
                                        <select class="form-select" name="matiere_id" required>
                                            <?php foreach ($matieres as $matiere): ?>
                                                <option value="<?php echo $matiere['Id']; ?>"><?php echo htmlspecialchars($matiere['Libelle'], ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Day</label>
                                        <select class="form-select" name="jour" required>
                                            <?php foreach (['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $jour): ?>
                                                <option value="<?php echo $jour; ?>"><?php echo $jour; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Time Slot</label>
                                        <select class="form-select" name="horaire_id" required>
                                            <?php foreach ($horaires as $horaire): ?>
                                                <option value="<?php echo $horaire['Id']; ?>"><?php echo htmlspecialchars(date('H:i', strtotime($horaire['HeureDebut'])) . ' - ' . date('H:i', strtotime($horaire['HeureFin'])), ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Teacher</label>
                                        <select class="form-select" name="enseignant_id" required>
                                            <?php foreach ($enseignants as $teacher): ?>
                                                <option value="<?php echo $teacher['Id']; ?>"><?php echo htmlspecialchars($teacher['Prenom'] . ' ' . $teacher['Nom'], ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Type</label>
                                        <select class="form-select" name="type" required>
                                            <?php foreach (['Cours','TD','TP'] as $type): ?>
                                                <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Room</label>
                                        <input type="text" name="salle" class="form-control" required>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-primary">Save Timetable Entry</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="card panel-card">
                    <div class="card-body p-4">
                        <h2 class="section-title h5 mb-4">Channels</h2>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Class</th>
                                        <th>Teacher</th>
                                        <th>Description</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($channels as $channel): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($channel['Libelle'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars(($channel['Filiere'] ?? 'N/A') . ' / ' . ($channel['Niveau'] ?? 'N/A') . ' / ' . ($channel['Groupe'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars(trim(($channel['EnsPrenom'] ?? '') . ' ' . ($channel['EnsNom'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($channel['Description'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline-block">
                                                    <input type="hidden" name="action" value="delete_channel">
                                                    <input type="hidden" name="channel_id" value="<?php echo $channel['Id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="../js/bootstrap.js"></script>
</body>
</html>
