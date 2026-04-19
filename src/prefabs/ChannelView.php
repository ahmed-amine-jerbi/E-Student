<?php
/**
 * Channel View and Messaging Page.
 *
 * Displays channel information, messages, and allows students to send messages
 */
require_once(__DIR__ . '/auth.php');
require_once(__DIR__ . '/../prefabs/database_connection.php');

ensureSessionStarted();
requireAuthenticatedUser('../pages/login.php');

$currentUser = getAuthenticatedUser();
$channelId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$channel = null;
$messages = [];
$isMember = false;
$isChannelOwner = false;
$postError = '';
$postSuccess = '';

// Fetch channel details
if ($channelId > 0) {
    $channelQuery = $database->prepare(
        "SELECT 
            c.Id, 
            c.Libelle, 
            c.Description,
            u.Id AS enseignantId,
            u.Prenom,
            u.Nom
        FROM channels c
        LEFT JOIN utilisateurs u ON u.Id = c.EnsId
        WHERE c.Id = :channelId
        LIMIT 1"
    );
    $channelQuery->execute(['channelId' => $channelId]);
    $channel = $channelQuery->fetch(PDO::FETCH_ASSOC);

    if ($channel) {
        $isChannelOwner = (int) ($channel['enseignantId'] ?? 0) === (int) ($currentUser['id'] ?? 0);

        // Check if user is a member of this channel
        $checkMemberQuery = $database->prepare(
            "SELECT COUNT(*) FROM joined_channels 
             WHERE channelId = :channelId AND utilisateurId = :userId"
        );
        $checkMemberQuery->execute(['channelId' => $channelId, 'userId' => $currentUser['id']]);
        $isMember = $isChannelOwner || $checkMemberQuery->fetchColumn() > 0;

        if (!$isMember) {
            header('Location: ../pages/dashboard.php');
            exit;
        }

        // Fetch messages for this channel
        $messagesQuery = $database->prepare(
            "SELECT 
                m.Id,
                m.Contenu,
                m.DateEnvoi,
                u.Id AS userId,
                u.Prenom,
                u.Nom,
                u.Role
            FROM messages m
            INNER JOIN utilisateurs u ON u.Id = m.UserId
            WHERE m.ChannelId = :channelId
            ORDER BY m.DateEnvoi DESC"
        );
        $messagesQuery->execute(['channelId' => $channelId]);
        $messages = $messagesQuery->fetchAll(PDO::FETCH_ASSOC);
    }
}


// Handle message posting
if (!empty($_POST) && $channelId > 0 && $isMember) {
    if ($_POST['delete_message']) {
        $messageId = $_POST['message_id'];
        $stmt = $database->prepare('DELETE FROM messages WHERE Id = :id');
        $stmt->execute(['id' => $messageId]);
        header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $channelId);
        exit;
    }

    $messageContent = trim($_POST['message'] ?? '');
    // Validate message
    if (empty($messageContent)) {
        $postError = 'Please enter a message.';
    }

    // If no error, save message to database
    if (empty($postError)) {
        try {
            $insertMessage = $database->prepare(
                "INSERT INTO messages (ChannelId, Contenu, DateEnvoi, UserId)
                     VALUES (:channelId, :contenu, NOW(), :userId)"
            );
            $insertMessage->execute([
                'channelId' => $channelId,
                'contenu' => $messageContent,
                'userId' => $currentUser['id']
            ]);
            $postSuccess = 'Message posted successfully!';

            // Refresh messages
            $messagesQuery = $database->prepare(
                "SELECT 
                        m.Id,
                        m.Contenu,
                        m.DateEnvoi,
                        u.Id AS userId,
                        u.Prenom,
                        u.Nom,
                        u.Role
                    FROM messages m
                    INNER JOIN utilisateurs u ON u.Id = m.UserId
                    WHERE m.ChannelId = :channelId
                    ORDER BY m.DateEnvoi ASC"
            );
            $messagesQuery->execute(['channelId' => $channelId]);
            $messages = $messagesQuery->fetchAll(PDO::FETCH_ASSOC);

            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } catch (Exception $e) {
            $postError = 'An error occurred while posting the message. Please try again.';
        }
    }
}

// Handle message editing
if (isset($_POST['edit_message']) && $channelId > 0 && $isMember) {
    $messageId = (int) $_POST['message_id'];
    $newContent = trim($_POST['edit_content']);
    if (!empty($newContent)) {
        // Check if user can edit this message
        $checkOwnership = $database->prepare("SELECT UserId FROM messages WHERE Id = :id AND ChannelId = :channelId");
        $checkOwnership->execute(['id' => $messageId, 'channelId' => $channelId]);
        $owner = $checkOwnership->fetch();
        if ($owner && ((int) $owner['UserId'] === (int) $currentUser['id'] || $isChannelOwner)) {
            $updateQuery = $database->prepare("UPDATE messages SET Contenu = :contenu WHERE Id = :id");
            $updateQuery->execute(['contenu' => $newContent, 'id' => $messageId]);
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
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
    : 'No Enseignant assigned';
$teacherId = $channel['enseignantId'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Student / Channel - <?php echo htmlspecialchars($channel['Libelle'], ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .message-container {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 1rem;
            background-color: #f9f9f9;
        }

        .message {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 8px;
            background-color: #fff;
            border-left: 4px solid #0d6efd;
        }

        .message.Enseignant {
            background-color: #fef3cd;
            border-left-color: #ffc107;
        }

        .message-header {
            display: flex;
            justify-content-between;
            align-items-center;
            margin-bottom: 0.5rem;
        }

        .message-author {
            font-weight: 600;
            color: #333;
        }

        .message-time {
            font-size: 0.875rem;
            color: #999;
        }

        .message-content {
            color: #555;
            line-height: 1.5;
            word-wrap: break-word;
        }
    </style>
</head>

<body id="top">

    <main class="container py-5">
        <!-- Channel Header -->
        <div class="mb-4">
            <a href="../pages/dashboard.php" class="btn btn-outline-secondary btn-sm mb-3">← Back to Dashboard</a>
            <div class="card panel-card bg-white text-dark">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col">
                            <h1 class="h3 mb-2">
                                <?php echo htmlspecialchars($channel['Libelle'], ENT_QUOTES, 'UTF-8'); ?>
                            </h1>
                            <p class="text-secondary mb-2">
                                <?php echo htmlspecialchars($channel['Description'] ?? 'No description', ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                            <small class="text-muted"><strong>Enseignant:</strong> <?php echo $teacherName; ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Messages Section -->
            <div class="col-lg-8">
                <div class="card panel-card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-4">Channel Messages</h2>

                        <!-- Success Message -->
                        <?php if (!empty($postSuccess)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($postSuccess, ENT_QUOTES, 'UTF-8'); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Error Message -->
                        <?php if (!empty($postError)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($postError, ENT_QUOTES, 'UTF-8'); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Messages Display -->
                        <div class="message-container mb-4">
                            <?php if (empty($messages)): ?>
                                <p class="text-center text-secondary py-5">No messages yet. Start the conversation!</p>
                            <?php else: ?>
                                <?php foreach ($messages as $message):
                                    $isEnseignant = ($teacherId !== null && $message['userId'] == $teacherId);
                                    $authorName = htmlspecialchars($message['Prenom'] . ' ' . $message['Nom'], ENT_QUOTES, 'UTF-8');
                                    $messageTime = date('M d, Y \a\t H:i', strtotime($message['DateEnvoi']));
                                    $canManageMessage = ((int) $message['userId'] === (int) $currentUser['id']) || $isChannelOwner;
                                    ?>
                                    <div class="message <?php echo $isEnseignant ? 'Enseignant' : ''; ?>">
                                        <div class="message-header">
                                            <div>
                                                <span class="message-author"><?php echo $authorName; ?></span>
                                                <?php if ($isEnseignant): ?>
                                                    <span class="badge text-bg-warning ms-2">Enseignant</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 ms-auto">
                                                <span class="message-time"><?php echo $messageTime; ?></span>
                                                <?php if ($canManageMessage): ?>
                                                    <button class="btn btn-sm btn-outline-primary"
                                                        onclick="editMessage(<?php echo $message['Id']; ?>)">Edit</button>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                                        <input type="hidden" name="message_id"
                                                            value="<?php echo $message['Id']; ?>">
                                                        <button type="submit" name="delete_message" value="true"
                                                            class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="message-content" id="content-<?php echo $message['Id']; ?>">
                                            <?php echo nl2br(htmlspecialchars($message['Contenu'], ENT_QUOTES, 'UTF-8')); ?>
                                        </div>
                                        <div id="edit-form-<?php echo $message['Id']; ?>" style="display:none;">
                                            <form method="POST">
                                                <input type="hidden" name="message_id" value="<?php echo $message['Id']; ?>">
                                                <textarea name="edit_content" class="form-control mb-2"
                                                    rows="3"><?php echo htmlspecialchars($message['Contenu'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                <button type="submit" name="edit_message"
                                                    class="btn btn-sm btn-primary me-1">Save</button>
                                                <button type="button" class="btn btn-sm btn-secondary"
                                                    onclick="cancelEdit(<?php echo $message['Id']; ?>)">Cancel</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Composer Section -->
            <div class="col-lg-4">
                <div class="card panel-card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-4">Send Message</h2>

                        <form method="POST"
                            action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $channelId, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="mb-2">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5"
                                    placeholder="Type your message or assignment submission here..."></textarea>
                                <small class="form-text text-muted">You can include assignment details or
                                    questions.</small>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Post Message</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Channel Info Sidebar -->
                <div class="card panel-card mt-2">
                    <div class="card-body p-4">
                        <h5>Channel Info</h5>
                        <div>
                            <small class="text-muted"><strong>Enseignant</strong></small>
                            <p class="small mb-0"><?php echo $teacherName; ?></p>
                        </div>
                        <div>
                            <small class="text-muted"><strong>Total Messages</strong></small>
                            <p class="small mb-0"><?php echo count($messages); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../js/bootstrap.js"></script>
    <script>
        function editMessage(id) {
            document.getElementById('content-' + id).style.display = 'none';
            document.getElementById('edit-form-' + id).style.display = 'block';
        }
        function cancelEdit(id) {
            document.getElementById('content-' + id).style.display = 'block';
            document.getElementById('edit-form-' + id).style.display = 'none';
        }
    </script>
</body>

</html>
