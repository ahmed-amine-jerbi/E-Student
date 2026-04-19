<?php
/**
 * Login page controller.
 *
 * Handles authentication requests and renders the login form.
 */
require_once(__DIR__ . '/../prefabs/auth.php');
require_once(__DIR__ . '/../prefabs/database_connection.php');

ensureSessionStarted();
redirectAuthenticatedUser('dashboard.php');

$email = '';
$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect submitted credentials and validate the inputs.
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $loginError = 'Please enter your email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $loginError = 'Please enter a valid email address.';
    } else {
        $userQuery = $database->prepare("
            SELECT
                u.Id,
                u.Prenom,
                u.Nom,
                u.Email,
                u.Password,
                u.Role,
                u.GroupeId,
                g.Libelle AS GroupeLibelle,
                n.Id AS NiveauId,
                n.Libelle AS NiveauLibelle,
                f.Id AS FiliereId,
                f.Libelle AS FiliereLibelle
            FROM utilisateurs u
            LEFT JOIN groupes g ON g.Id = u.GroupeId
            LEFT JOIN niveaux n ON n.Id = g.nivId
            LEFT JOIN filieres f ON f.Id = g.FiliereId
            WHERE u.Email = :email
            LIMIT 1
        ");
        $userQuery->execute(['email' => $email]);
        $user = $userQuery->fetch(PDO::FETCH_ASSOC);

        $storedPassword = $user['Password'] ?? '';
        $passwordMatches = false;

        // Authenticate the user by comparing the submitted password to the stored hash.
        if ($user && password_verify($password, $storedPassword)) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => $user['Id'],
                'prenom' => $user['Prenom'],
                'nom' => $user['Nom'],
                'email' => $user['Email'],
                'role' => $user['Role'],
                'groupe_id' => $user['GroupeId'],
                'groupe' => $user['GroupeLibelle'] ?? '',
                'niveau_id' => $user['NiveauId'],
                'niveau' => $user['NiveauLibelle'] ?? '',
                'filiere_id' => $user['FiliereId'],
                'filiere' => $user['FiliereLibelle'] ?? '',
            ];

            header('Location: dashboard.php');
            exit;
        }

        $loginError = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Student / Login</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body id="top">
    <?php include_once('../prefabs/header.php'); ?>

    <main class="container py-5 min-vh-100 d-flex align-items-center">
        <section class="p-4 p-md-5 w-100">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-xl-6">
                    <div class="card panel-card bg-white text-dark">
                        <div class="card-body p-4 p-md-4">
                            <div class="text-center mb-4">
                                <h1 class="h2 fw-bold mb-2">Welcome Back</h1>
                                <p class="text-secondary mb-0">Use your academic email and password to access your workspace.</p>
                            </div>

                            <?php if ($loginError !== '') { ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php } ?>

                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="mb-3">
                                    <label for="loginEmail" class="form-label">Email address</label>
                                    <input
                                        type="email"
                                        class="form-control form-control-lg"
                                        id="loginEmail"
                                        name="email"
                                        value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                                        placeholder="name@reclassify.tn"
                                    >
                                </div>
                                <div class="mb-3">
                                    <label for="loginPassword" class="form-label">Password</label>
                                    <input
                                        type="password"
                                        class="form-control form-control-lg"
                                        id="loginPassword"
                                        name="password"
                                        placeholder="Enter your password"
                                    >
                                </div>
                                <div class="d-grid gap-3">
                                    <button type="submit" class="btn btn-primary btn-lg">Log In</button>
                                    <a href="register.php" class="btn btn-outline-secondary btn-lg">Create an Account</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="../js/bootstrap.js"></script>
</body>
</html>
