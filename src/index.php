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
    <title>Re:Classify - Acceuil</title>
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
                            <div class="accordion" id="announcements">
                                <div class="accordion-item">
                                    <h3 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#announcementOne" aria-expanded="true" aria-controls="announcementOne">
                                            Exam preparation session
                                        </button>
                                    </h3>
                                    <div id="announcementOne" class="accordion-collapse collapse show" data-bs-parent="#announcements">
                                        <div class="accordion-body">
                                            Revision support will be held on Thursday afternoon in Lab 2.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h3 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#announcementTwo" aria-expanded="false" aria-controls="announcementTwo">
                                            Timetable updated
                                        </button>
                                    </h3>
                                    <div id="announcementTwo" class="accordion-collapse collapse" data-bs-parent="#announcements">
                                        <div class="accordion-body">
                                            The Wednesday programming course now starts at 09:00 instead of 08:00.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="schedule" class="mb-5">
            <div class="card panel-card">
                <div class="card-body p-4">
                    <div class="row align-items-center mb-4">
                        <div class="col-md-8">
                            <h2 class="section-title h4 mb-1">Weekly Timetable</h2>
                            <p class="text-secondary mb-0">Teachers can update this section for each class and subject.</p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <button class="btn btn-outline-primary">Edit timetable</button>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 bg-light h-100">
                                <h3 class="h6">Monday</h3>
                                <p class="mb-2">08:00 - Web Development</p>
                                <p class="mb-2">10:00 - Database Systems</p>
                                <p class="mb-0">13:00 - English</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 bg-light h-100">
                                <h3 class="h6">Tuesday</h3>
                                <p class="mb-2">08:00 - Algorithms</p>
                                <p class="mb-2">11:00 - Mathematics</p>
                                <p class="mb-0">14:00 - Networks</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 bg-light h-100">
                                <h3 class="h6">Wednesday</h3>
                                <p class="mb-2">09:00 - Programming Lab</p>
                                <p class="mb-2">11:00 - Software Design</p>
                                <p class="mb-0">15:00 - Project Workshop</p>
                            </div>
                        </div>
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
                                This interface can be your starting page before adding PHP forms, authentication, and database integration.
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