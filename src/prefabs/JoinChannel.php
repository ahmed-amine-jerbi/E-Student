<?php
/**
 * Join Channel page controller.
 *
 * Handles channel password validation and membership via a dedicated page.
 * Displays channel information and processes join requests.
 */
require_once(__DIR__ . '/auth.php');
require_once(__DIR__ . '/../prefabs/database_connection.php');

ensureSessionStarted();
requireAuthenticatedUser('../pages/login.php');

$currentUser = getAuthenticatedUser();
$channelId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$joinError = '';
$joinSuccess = '';
$channel = null;
$isAlreadyJoined = false;

// Fetch channel details
if ($channelId > 0) {
    $channelQuery = $database->prepare(
        "SELECT 
            c.Id, 
            c.Libelle, 
            c.Description, 
            c.Cle AS password,
            u.Prenom,
            u.Nom,
            u.Email AS enseignantEmail
        FROM channels c
        LEFT JOIN utilisateurs u ON u.Id = c.EnsId
        WHERE c.Id = :channelId
        LIMIT 1"
    );
    $channelQuery->execute(['channelId' => $channelId]);
    $channel = $channelQuery->fetch(PDO::FETCH_ASSOC);

    if ($channel) {
        // Check if already joined
        $checkJoinedQuery = $database->prepare(
            "SELECT COUNT(*) FROM joined_channels 
             WHERE channelId = :channelId AND utilisateurId = :userId"
        );
        $checkJoinedQuery->execute(['channelId' => $channelId, 'userId' => $currentUser['id']]);
        $isAlreadyJoined = $checkJoinedQuery->fetchColumn() > 0;

        // If already joined, redirect to channel view
        if ($isAlreadyJoined) {
            header('Location: ChannelView.php?id=' . $channelId);
            exit;
        }
    }
}

// Handle join request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $channelId > 0) {
    $submittedPassword = $_POST['channel_password'] ?? '';

    if (empty($submittedPassword)) {
        $joinError = 'Please enter the channel password.';
    } elseif ($isAlreadyJoined) {
        $joinError = 'You have already joined this channel.';
    } else {
        // Validate password
        if ($channel && $channel['password'] === $submittedPassword) {
            // Insert join record
            try {
                $joinInsert = $database->prepare(
                    "INSERT INTO joined_channels (channelId, utilisateurId) 
                     VALUES (:channelId, :userId)"
                );
                $joinInsert->execute(['channelId' => $channelId, 'userId' => $currentUser['id']]);
                // Redirect to channel view after successful join
                header('Location: ChannelView.php?id=' . $channelId);
                exit;
            } catch (Exception $e) {
                $joinError = 'An error occurred while joining the channel. Please try again.';
            }
        } else {
            $joinError = 'Incorrect channel password. Please try again.';
        }
    }
}

// Handle case where channel doesn't exist
if ($channelId <= 0 || !$channel) {
    header('Location: ../pages/dashboard.php');
    exit;
}

$teacherName = (!empty($channel['Prenom']) && !empty($channel['Nom'])) 
    ? htmlspecialchars($channel['Prenom'] . ' ' . $channel['Nom'], ENT_QUOTES, 'UTF-8')
    : 'No instructor assigned';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Student / Join Channel</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body id="top">
    <?php include_once(__DIR__ . '/header.php'); ?>

    <main class="container py-5 min-vh-100 d-flex align-items-center">
        <section class="p-4 p-md-5 w-100">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-6">
                    <div class="card panel-card bg-white text-dark">
                        <div class="card-body p-4 p-md-4">
                            <div class="text-center mb-4">
                                <div class="mb-3">
                                    <span class="badge text-bg-info">Channel</span>
                                </div>
                                <h1 class="h2 fw-bold mb-2"><?php echo htmlspecialchars($channel['Libelle'], ENT_QUOTES, 'UTF-8'); ?></h1>
                                <p class="text-secondary mb-0">Enter the channel password to join this classroom.</p>
                            </div>

                            <!-- Channel Information Card -->
                            <div class="alert alert-light border mb-4" role="alert">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block mb-1"><strong>Description</strong></small>
                                        <p class="mb-0">
                                            <?php echo htmlspecialchars($channel['Description'] ?? 'No description available', ENT_QUOTES, 'UTF-8'); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block mb-1"><strong>Instructor</strong></small>
                                        <p class="mb-0"><?php echo $teacherName; ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Success Message -->
                            <?php if ($joinSuccess !== '') { ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong>Success!</strong> <?php echo htmlspecialchars($joinSuccess, ENT_QUOTES, 'UTF-8'); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php } ?>

                            <!-- Error Message -->
                            <?php if ($joinError !== '') { ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($joinError, ENT_QUOTES, 'UTF-8'); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php } ?>

                            <!-- Join Form -->
                            <?php if (!$isAlreadyJoined) { ?>
                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $channelId, ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="mb-3">
                                        <label for="channelPassword" class="form-label">Channel Password</label>
                                        <input
                                            type="password"
                                            class="form-control form-control-lg"
                                            id="channelPassword"
                                            name="channel_password"
                                            placeholder="Enter the channel password"
                                            autofocus
                                            required
                                        >
                                        <small class="form-text text-muted">Ask your instructor for the channel password.</small>
                                    </div>
                                    <div class="d-grid gap-3">
                                        <button type="submit" class="btn btn-primary btn-lg">Join Channel</button>
                                        <a href="../pages/dashboard.php" class="btn btn-outline-secondary btn-lg">Back to Dashboard</a>
                                    </div>
                                </form>
                            <?php } else { ?>
                                <!-- Already Joined Message -->
                                <div class="alert alert-success" role="alert">
                                    <strong>You are already a member of this channel.</strong>
                                </div>
                                <div class="d-grid gap-3">
                                    <a href="../pages/dashboard.php" class="btn btn-primary btn-lg">Back to Dashboard</a>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="../js/bootstrap.js"></script>
</body>
</html>
