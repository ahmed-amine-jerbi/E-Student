<?php
/**
 * Teacher dashboard for class management, grading, attendance, channels, and assignments.
 */
$teacherName = trim(($currentUser['prenom'] ?? '') . ' ' . ($currentUser['nom'] ?? ''));
$currentGroupId = !empty($currentUser['groupe_id']) ? (int)$currentUser['groupe_id'] : null;
$teacherGroupIds = [];

if ($currentGroupId) {
    $teacherGroupIds[] = $currentGroupId;
}

$teacherGroupStmt = $database->prepare(
    "SELECT DISTINCT e.GroupeId
     FROM emplois e
     WHERE e.EnsId = :ensId"
);
$teacherGroupStmt->execute(['ensId' => $currentUser['id']]);
$teacherGroupIds = array_merge($teacherGroupIds, $teacherGroupStmt->fetchAll(PDO::FETCH_COLUMN, 0));
$teacherGroupIds = array_values(array_filter(array_unique(array_map('intval', $teacherGroupIds)), fn($id) => $id > 0));

$groupFilterSql = '0';
$groupFilterValues = [];
if (!empty($teacherGroupIds)) {
    $groupFilterValues = array_map('intval', array_values(array_unique($teacherGroupIds)));
    $groupFilterSql = implode(',', array_fill(0, count($groupFilterValues), '?'));
}

$students = [];
if (!empty($groupFilterValues)) {
    $studentQuery = $database->prepare(
        "SELECT u.Id, u.Prenom, u.Nom, u.Email, g.Libelle AS GroupeLibelle, n.Libelle AS NiveauLibelle, f.Libelle AS FiliereLibelle
         FROM utilisateurs u
         INNER JOIN groupes g ON g.Id = u.GroupeId
         INNER JOIN niveaux n ON n.Id = g.nivId
         INNER JOIN filieres f ON f.Id = g.FiliereId
         WHERE u.Role = 'Etudiant'
         AND u.GroupeId IN ($groupFilterSql)
         ORDER BY u.Nom, u.Prenom"
    );
    $studentQuery->execute($groupFilterValues);
    $students = $studentQuery->fetchAll(PDO::FETCH_ASSOC);
}

$subjects = [];
if (!empty($groupFilterValues)) {
    $subjectPlaceholders = implode(',', array_fill(0, count($groupFilterValues), '?'));
    $subjectQuery = $database->prepare(
        "SELECT DISTINCT m.Id, m.Libelle
         FROM emplois e
         INNER JOIN matieres m ON m.Id = e.MatiereId
         WHERE e.GroupeId IN ($subjectPlaceholders)
         ORDER BY m.Libelle"
    );
    $subjectQuery->execute($groupFilterValues);
    $subjects = $subjectQuery->fetchAll(PDO::FETCH_ASSOC);
}

if (
    empty($subjects)
    && !empty($currentUser['filiere_id'])
    && !empty($currentUser['niveau_id'])
) {
    $subjectQuery = $database->prepare(
        "SELECT DISTINCT m.Id, m.Libelle
         FROM filiere_matiere fm
         INNER JOIN matieres m ON m.Id = fm.MatiereId
         WHERE fm.FiliereId = :filiereId
         AND fm.NiveauId = :niveauId
         ORDER BY m.Libelle"
    );
    $subjectQuery->execute([
        'filiereId' => $currentUser['filiere_id'],
        'niveauId' => $currentUser['niveau_id'],
    ]);
    $subjects = $subjectQuery->fetchAll(PDO::FETCH_ASSOC);
}

$managedStudentIds = array_map('intval', array_column($students, 'Id'));
$managedStudentLookup = array_fill_keys($managedStudentIds, true);
$managedSubjectIds = array_map('intval', array_column($subjects, 'Id'));
$managedSubjectLookup = array_fill_keys($managedSubjectIds, true);

$studentIds = $managedStudentIds;
$notesByStudent = [];
if (!empty($studentIds) && !empty($managedSubjectIds)) {
    $studentNotePlaceholders = implode(',', array_fill(0, count($studentIds), '?'));
    $subjectNotePlaceholders = implode(',', array_fill(0, count($managedSubjectIds), '?'));
    $noteQuery = $database->prepare(
        "SELECT n.UserId, n.MatiereId, n.Valeur
         FROM notes n
         WHERE n.UserId IN ($studentNotePlaceholders)
         AND n.MatiereId IN ($subjectNotePlaceholders)"
    );
    $noteQuery->execute(array_merge($studentIds, $managedSubjectIds));
    foreach ($noteQuery->fetchAll(PDO::FETCH_ASSOC) as $note) {
        $notesByStudent[$note['UserId']][$note['MatiereId']] = $note['Valeur'];
    }
}

$attendanceDate = $_POST['attendance_date'] ?? $_GET['attendance_date'] ?? date('Y-m-d');
$attendanceRecords = [];
if (!empty($groupFilterValues)) {
    $attendancePlaceholders = implode(',', array_fill(0, count($groupFilterValues), '?'));
    $attendanceSql = "SELECT UserId, Status FROM attendances WHERE Date = ? AND EnsId = ? AND GroupeId IN ($attendancePlaceholders)";
    $attendanceStmt = $database->prepare($attendanceSql);
    $attendanceStmt->execute(array_merge([$attendanceDate, $currentUser['id']], $groupFilterValues));
    foreach ($attendanceStmt->fetchAll(PDO::FETCH_ASSOC) as $attendance) {
        $attendanceRecords[$attendance['UserId']] = $attendance['Status'];
    }
}

$teacherChannels = [];
$channelQuery = $database->prepare(
    "SELECT c.Id, c.Libelle, c.Description, c.Cle, f.Libelle AS Filiere, n.Libelle AS Niveau, g.Libelle AS Groupe
     FROM channels c
     LEFT JOIN filieres f ON f.Id = c.FiliereId
     LEFT JOIN niveaux n ON n.Id = c.NiveauId
     LEFT JOIN groupes g ON g.Id = c.GroupeId
     WHERE c.EnsId = :ensId
     ORDER BY c.Id DESC"
);
$channelQuery->execute(['ensId' => $currentUser['id']]);
$teacherChannels = $channelQuery->fetchAll(PDO::FETCH_ASSOC);

$teacherAssignments = [];
$assignmentQuery = $database->prepare(
    "SELECT d.Id, d.Titre, d.Deadline, f.Libelle AS Filiere, n.Libelle AS Niveau, g.Libelle AS Groupe
     FROM devoirs d
     LEFT JOIN filieres f ON f.Id = d.FiliereId
     LEFT JOIN niveaux n ON n.Id = d.NiveauId
     LEFT JOIN groupes g ON g.Id = d.GroupeId
     WHERE d.EnsId = :ensId
     ORDER BY d.Deadline DESC"
);
$assignmentQuery->execute(['ensId' => $currentUser['id']]);
$teacherAssignments = $assignmentQuery->fetchAll(PDO::FETCH_ASSOC);

$scheduleDays = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
$timetableSlots = [];
$horairesQuery = $database->prepare(
    "SELECT HeureDebut, HeureFin FROM horaires ORDER BY HeureDebut ASC"
);
$horairesQuery->execute();
$horairesList = $horairesQuery->fetchAll(PDO::FETCH_ASSOC);
foreach ($horairesList as $horaire) {
    $timetableSlots[] = date('H:i', strtotime($horaire['HeureDebut'])) . ' - ' . date('H:i', strtotime($horaire['HeureFin']));
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

$teacherEmploi = $database->prepare(
    "SELECT e.Jour, h.HeureDebut, h.HeureFin, m.Libelle AS Matiere, g.Libelle AS Groupe, e.Salle, e.Type
     FROM emplois e, horaires h, matieres m, groupes g
     WHERE e.MatiereId = m.Id
     AND e.HoraireId = h.Id
     AND e.GroupeId = g.Id
     AND e.EnsId = :ensId"
);
$teacherEmploi->execute(['ensId' => $currentUser['id']]);
$teacherEmploiData = $teacherEmploi->fetchAll(PDO::FETCH_ASSOC);

$teacherScheduleGrid = [];
foreach ($teacherEmploiData as $entry) {
    $slotLabel = date('H:i', strtotime($entry['HeureDebut'])) . ' - ' . date('H:i', strtotime($entry['HeureFin']));
    $teacherScheduleGrid[$entry['Jour']][$slotLabel] = $entry;
}

foreach ($scheduleDays as $day) {
    if (!isset($teacherScheduleGrid[$day])) {
        $teacherScheduleGrid[$day] = [];
    }
}

$actionSuccess = '';
$actionError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_channel') {
        $channelTitle = trim($_POST['channel_title'] ?? '');
        $channelDescription = trim($_POST['channel_description'] ?? '');
        $channelPassword = trim($_POST['channel_password'] ?? '');
        if ($channelTitle === '' || $channelPassword === '') {
            $actionError = 'Channel name and password are required.';
        } elseif (!$currentUser['filiere_id'] || !$currentUser['niveau_id'] || !$currentUser['groupe_id']) {
            $actionError = 'A class and group assignment is required to create a channel.';
        } else {
            $insertChannel = $database->prepare(
                "INSERT INTO channels (EnsId, FiliereId, NiveauId, GroupeId, Description, Cle, Libelle)
                 VALUES (:ensId, :filiereId, :niveauId, :groupeId, :description, :cle, :libelle)"
            );
            $insertChannel->execute([
                'ensId' => $currentUser['id'],
                'filiereId' => $currentUser['filiere_id'],
                'niveauId' => $currentUser['niveau_id'],
                'groupeId' => $currentUser['groupe_id'],
                'description' => $channelDescription,
                'cle' => $channelPassword,
                'libelle' => $channelTitle,
            ]);
            $actionSuccess = 'Channel created successfully.';
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

    if ($action === 'create_assignment') {
        $assignmentTitle = trim($_POST['assignment_title'] ?? '');
        $assignmentDeadline = trim($_POST['assignment_deadline'] ?? '');
        if ($assignmentTitle === '' || $assignmentDeadline === '') {
            $actionError = 'Assignment title and deadline are required.';
        } elseif (!$currentUser['filiere_id'] || !$currentUser['niveau_id'] || !$currentUser['groupe_id']) {
            $actionError = 'A class and group assignment is required to create an assignment.';
        } else {
            $assignmentDeadline = str_replace('T', ' ', $assignmentDeadline);
            $insertAssignment = $database->prepare(
                "INSERT INTO devoirs (EnsId, FiliereId, NiveauId, GroupeId, Titre, Deadline)
                 VALUES (:ensId, :filiereId, :niveauId, :groupeId, :titre, :deadline)"
            );
            $insertAssignment->execute([
                'ensId' => $currentUser['id'],
                'filiereId' => $currentUser['filiere_id'],
                'niveauId' => $currentUser['niveau_id'],
                'groupeId' => $currentUser['groupe_id'],
                'titre' => $assignmentTitle,
                'deadline' => $assignmentDeadline,
            ]);
            $actionSuccess = 'Assignment created successfully.';
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

    if ($action === 'grade_student') {
        $selectedStudent = (int)($_POST['student_id'] ?? 0);
        $selectedSubject = (int)($_POST['subject_id'] ?? 0);
        $gradeValue = trim($_POST['grade_value'] ?? '');
        if ($selectedStudent <= 0 || $selectedSubject <= 0 || $gradeValue === '') {
            $actionError = 'Student, subject, and grade are required.';
        } elseif (!isset($managedStudentLookup[$selectedStudent])) {
            $actionError = 'The selected student is not part of your managed class list.';
        } elseif (!isset($managedSubjectLookup[$selectedSubject])) {
            $actionError = 'The selected subject is not available for your class.';
        } elseif (!is_numeric($gradeValue)) {
            $actionError = 'Grade must be a numeric value.';
        } elseif ((float) $gradeValue < 0 || (float) $gradeValue > 20) {
            $actionError = 'Grade must be between 0 and 20.';
        } else {
            $existingNote = $database->prepare(
                "SELECT 1 FROM notes WHERE UserId = :userId AND MatiereId = :matiereId"
            );
            $existingNote->execute(['userId' => $selectedStudent, 'matiereId' => $selectedSubject]);
            if ($existingNote->fetch()) {
                $updateNote = $database->prepare(
                    "UPDATE notes SET Valeur = :valeur WHERE UserId = :userId AND MatiereId = :matiereId"
                );
                $updateNote->execute([
                    'valeur' => $gradeValue,
                    'userId' => $selectedStudent,
                    'matiereId' => $selectedSubject,
                ]);
                $actionSuccess = 'Grade updated successfully.';
            } else {
                $insertNote = $database->prepare(
                    "INSERT INTO notes (UserId, MatiereId, Valeur) VALUES (:userId, :matiereId, :valeur)"
                );
                $insertNote->execute([
                    'userId' => $selectedStudent,
                    'matiereId' => $selectedSubject,
                    'valeur' => $gradeValue,
                ]);
                $actionSuccess = 'Grade assigned successfully.';
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

    if ($action === 'mark_attendance') {
        $attendanceDate = trim($_POST['attendance_date'] ?? $attendanceDate);
        $attendanceStatus = $_POST['attendance_status'] ?? [];
        if (!empty($attendanceStatus) && !empty($groupFilterValues)) {
            $attendanceSelect = $database->prepare(
                "SELECT Id FROM attendances WHERE UserId = :userId AND Date = :date"
            );
            $attendanceUpdate = $database->prepare(
                "UPDATE attendances SET Status = :status WHERE Id = :id"
            );
            $attendanceInsert = $database->prepare(
                "INSERT INTO attendances (UserId, EnsId, FiliereId, NiveauId, GroupeId, Date, Status)
                 VALUES (:userId, :ensId, :filiereId, :niveauId, :groupeId, :date, :status)"
            );
            foreach ($attendanceStatus as $studentId => $status) {
                if (!in_array($status, ['Present', 'Absent'], true)) {
                    continue;
                }
                $attendanceSelect->execute(['userId' => $studentId, 'date' => $attendanceDate]);
                $existing = $attendanceSelect->fetch(PDO::FETCH_ASSOC);
                if ($existing) {
                    $attendanceUpdate->execute(['status' => $status, 'id' => $existing['Id']]);
                } else {
                    $attendanceInsert->execute([
                        'userId' => $studentId,
                        'ensId' => $currentUser['id'],
                        'filiereId' => $currentUser['filiere_id'] ?? 0,
                        'niveauId' => $currentUser['niveau_id'] ?? 0,
                        'groupeId' => $currentUser['groupe_id'] ?? 0,
                        'date' => $attendanceDate,
                        'status' => $status,
                    ]);
                }
            }
            $actionSuccess = 'Attendance recorded successfully.';
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

    if ($action === 'update_grades') {
        $grades = $_POST['grades'] ?? [];
        foreach ($grades as $studentId => $subjectGrades) {
            $studentId = (int) $studentId;
            if (!isset($managedStudentLookup[$studentId]) || !is_array($subjectGrades)) {
                continue;
            }
            foreach ($subjectGrades as $subjectId => $grade) {
                $subjectId = (int) $subjectId;
                if (!isset($managedSubjectLookup[$subjectId])) {
                    continue;
                }
                $grade = trim($grade);
                if ($grade !== '' && is_numeric($grade)) {
                    $existingNote = $database->prepare(
                        "SELECT 1 FROM notes WHERE UserId = :userId AND MatiereId = :matiereId"
                    );
                    $existingNote->execute(['userId' => $studentId, 'matiereId' => $subjectId]);
                    if ($existingNote->fetch()) {
                        $updateNote = $database->prepare(
                            "UPDATE notes SET Valeur = :valeur WHERE UserId = :userId AND MatiereId = :matiereId"
                        );
                        $updateNote->execute([
                            'valeur' => $grade,
                            'userId' => $studentId,
                            'matiereId' => $subjectId,
                        ]);
                    } else {
                        $insertNote = $database->prepare(
                            "INSERT INTO notes (UserId, MatiereId, Valeur) VALUES (:userId, :matiereId, :valeur)"
                        );
                        $insertNote->execute([
                            'userId' => $studentId,
                            'matiereId' => $subjectId,
                            'valeur' => $grade,
                        ]);
                    }
                }
            }
        }
        $actionSuccess = 'Grades updated successfully.';
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

$groupLabel = 'Unassigned';
if ($currentUser['groupe'] ?? '') {
    $groupLabel = htmlspecialchars($currentUser['groupe'], ENT_QUOTES, 'UTF-8');
}
$teacherClassLabel = trim(($currentUser['filiere'] ?? '') . ' / ' . ($currentUser['niveau'] ?? '') . ' / ' . $groupLabel);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Student / Enseignant Dashboard</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .status-pill { padding: 0.4rem 0.75rem; border-radius: 999px; font-size: 0.9rem; }
        .attendance-select .form-check { margin-bottom: 0.5rem; }
        .assignment-expired { background-color: #ffdddd; }
        .assignment-active { background-color: #ddffdd; }
    </style>
</head>
<body id="top">
    <?php include_once(__DIR__ . '/../../prefabs/header.php'); ?>
    <main class="container py-5">
        <section class="hero p-4 p-md-5 mb-5 bg-light rounded-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <span class="badge text-bg-light text-primary mb-3">Teacher Dashboard</span>
                    <h1 class="display-6 fw-bold">Welcome, <?php echo htmlspecialchars($teacherName ?: 'Enseignant', ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p class="lead text-secondary mt-3 mb-4">
                        Manage your students, assign grades, create secure channels, publish assignments, and track attendance in one place.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <span class="status-pill bg-white border">Class: <?php echo $teacherClassLabel; ?></span>
                        <span class="status-pill bg-white border">Students: <?php echo count($students); ?></span>
                        <span class="status-pill bg-white border">Subjects: <?php echo count($subjects); ?></span>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card panel-card h-100">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-3">Quick Actions</h2>
                            <div class="d-grid gap-3">
                                <a class="btn btn-outline-primary" href="#students">View Students</a>
                                <a class="btn btn-outline-primary" href="#attendance">Mark Attendance</a>
                                <a class="btn btn-outline-primary" href="#assignments">Create Assignment</a>
                                <a class="btn btn-outline-primary" href="#channels">Create Channel</a>
                            </div>
                        </div>
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

        <div class="row g-4 mb-5" id="students">
            <div class="col-lg-8">
                <div class="card panel-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h2 class="section-title h5 mb-1">Students in Your Class</h2>
                                <p class="text-secondary mb-0">All students assigned to your current group and section.</p>
                            </div>
                        </div>
                        <?php if (empty($students)): ?>
                            <p class="text-center text-secondary">No students found for your group.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Attendance</th>
                                            <th>Last Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student):
                                            $studentId = $student['Id'];
                                            $attendanceStatus = $attendanceRecords[$studentId] ?? 'Not marked';
                                            $latestGrade = '';
                                            if (!empty($notesByStudent[$studentId])) {
                                                $latestValues = array_values($notesByStudent[$studentId]);
                                                $latestGrade = end($latestValues);
                                            }
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['Prenom'] . ' ' . $student['Nom'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($student['Email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($attendanceStatus, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo $latestGrade !== '' ? htmlspecialchars($latestGrade, ENT_QUOTES, 'UTF-8') : '—'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card panel-card mb-4">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-4">Grade a Student</h2>
                        <form method="POST">
                            <input type="hidden" name="action" value="grade_student">
                            <div class="mb-3">
                                <label class="form-label">Student</label>
                                <select class="form-select" name="student_id" required>
                                    <option value="">Choose student</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?php echo $student['Id']; ?>"><?php echo htmlspecialchars($student['Prenom'] . ' ' . $student['Nom'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subject</label>
                                <select class="form-select" name="subject_id" <?php echo empty($subjects) ? 'disabled' : ''; ?> required>
                                    <option value="">Choose subject</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?php echo $subject['Id']; ?>"><?php echo htmlspecialchars($subject['Libelle'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($subjects)): ?>
                                    <div class="form-text text-danger">No subjects are linked to your managed class yet.</div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Grade</label>
                                <input type="number" step="0.01" min="0" max="20" class="form-control" name="grade_value" placeholder="Enter grade" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" <?php echo empty($subjects) ? 'disabled' : ''; ?>>Save Grade</button>
                        </form>
                    </div>
                </div>
                <div class="card panel-card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-4" id="attendance">Attendance</h2>
                        <form method="POST">
                            <input type="hidden" name="action" value="mark_attendance">
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" name="attendance_date" value="<?php echo htmlspecialchars($attendanceDate, ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['Prenom'] . ' ' . $student['Nom'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="attendance_status[<?php echo $student['Id']; ?>]" value="Present" <?php echo ($attendanceRecords[$student['Id']] ?? '') === 'Present' ? 'checked' : ''; ?>>
                                                            <label class="form-check-label">P</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="attendance_status[<?php echo $student['Id']; ?>]" value="Absent" <?php echo ($attendanceRecords[$student['Id']] ?? '') === 'Absent' ? 'checked' : ''; ?>>
                                                            <label class="form-check-label">A</label>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Save Attendance</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="card panel-card">
                    <div class="card-body p-4">
                        <h2 class="section-title h4 mb-4">Grades Management</h2>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_grades">
                            <?php if (empty($subjects)): ?>
                                <p class="text-secondary mb-0">Subjects will appear here once they are linked to your managed class or timetable.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <?php foreach ($subjects as $subject): ?>
                                                    <th><?php echo htmlspecialchars($subject['Libelle'], ENT_QUOTES, 'UTF-8'); ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($students as $student): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($student['Prenom'] . ' ' . $student['Nom'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <?php foreach ($subjects as $subject): 
                                                        $grade = $notesByStudent[$student['Id']][$subject['Id']] ?? '';
                                                    ?>
                                                        <td><input type="number" step="0.01" min="0" max="20" class="form-control form-control-sm" name="grades[<?php echo $student['Id']; ?>][<?php echo $subject['Id']; ?>]" value="<?php echo htmlspecialchars($grade, ENT_QUOTES, 'UTF-8'); ?>"></td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="submit" class="btn btn-primary">Save All Grades</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="card panel-card h-100" id="channels">
                    <div class="card-body p-4">
                        <h2 class="section-title h5 mb-4">Create Channel</h2>
                        <form method="POST">
                            <input type="hidden" name="action" value="create_channel">
                            <div class="mb-3">
                                <label class="form-label">Channel Name</label>
                                <input type="text" name="channel_title" class="form-control" placeholder="Channel title" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="channel_description" class="form-control" rows="3" placeholder="Channel description"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="text" name="channel_password" class="form-control" placeholder="Channel password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Create Channel</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card panel-card h-100" id="assignments">
                    <div class="card-body p-4">
                        <h2 class="section-title h5 mb-4">Create Assignment</h2>
                        <form method="POST">
                            <input type="hidden" name="action" value="create_assignment">
                            <div class="mb-3">
                                <label class="form-label">Assignment Title</label>
                                <input type="text" name="assignment_title" class="form-control" placeholder="Assignment title" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deadline</label>
                                <input type="datetime-local" name="assignment_deadline" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Publish Assignment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card panel-card h-100">
                    <div class="card-body p-4">
                        <h2 class="section-title h5 mb-4">Channels You Created</h2>
                        <?php if (empty($teacherChannels)): ?>
                            <p class="text-center text-secondary">No channels created yet.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($teacherChannels as $channel): ?>
                                    <div class="list-group-item rounded-4 mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <h3 class="h6 mb-1"><?php echo htmlspecialchars($channel['Libelle'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                                <p class="mb-0 text-secondary"><?php echo htmlspecialchars($channel['Description'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            </div>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($channel['Filiere'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center gap-3">
                                            <small class="text-muted">Group: <?php echo htmlspecialchars($channel['Groupe'] ?? 'All', ENT_QUOTES, 'UTF-8'); ?> • Level: <?php echo htmlspecialchars($channel['Niveau'] ?? 'All', ENT_QUOTES, 'UTF-8'); ?></small>
                                            <a href="../prefabs/ChannelView.php?id=<?php echo (int) $channel['Id']; ?>" class="btn btn-sm btn-outline-primary">View Channel</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card panel-card h-100">
                    <div class="card-body p-4">
                        <h2 class="section-title h5 mb-4">Assignments You Published</h2>
                        <?php if (empty($teacherAssignments)): ?>
                            <p class="text-center text-secondary">No assignments published yet.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($teacherAssignments as $assignment): 
                                    $isExpired = strtotime($assignment['Deadline']) < time();
                                ?>
                                    <div class="list-group-item rounded-4 mb-2 <?php echo $isExpired ? 'assignment-expired' : 'assignment-active'; ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <h3 class="h6 mb-1"><?php echo htmlspecialchars($assignment['Titre'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                            </div>
                                            <span class="badge bg-secondary"><?php echo date('Y-m-d H:i', strtotime($assignment['Deadline'])); ?></span>
                                        </div>
                                        <small class="text-muted">Group: <?php echo htmlspecialchars($assignment['Groupe'], ENT_QUOTES, 'UTF-8'); ?> • Level: <?php echo htmlspecialchars($assignment['Niveau'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5 mt-5">
            <div class="col-12">
                <div class="card panel-card">
                    <div class="card-body p-4">
                        <h2 class="section-title h4 mb-4">Your Timetable</h2>
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
                                                $entry = $teacherScheduleGrid[$day][$slot] ?? null;
                                                if ($entry) {
                                                    $entryType = $entry['Type'] ?? 'Cours';
                                                    $entryClass = $typeColors[$entryType] ?? 'bg-secondary bg-opacity-10 border-secondary';
                                                    $badgeClass = $typeBadges[$entryType] ?? 'badge text-bg-secondary';
                                                    ?>
                                                    <td class="p-2">
                                                        <div class="border rounded-3 p-2 <?php echo $entryClass; ?>">
                                                            <div class="mb-1"><strong><?php echo htmlspecialchars($entry['Matiere'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
                                                            <div class="small text-muted mb-1"><?php echo htmlspecialchars($entry['Groupe'], ENT_QUOTES, 'UTF-8'); ?></div>
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
            </div>
        </div>
    </main>
    <script src="../js/bootstrap.js"></script>
</body>
</html>
