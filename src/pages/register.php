<?php
/**
 * Registration page controller.
 *
 * Validates new signups, preserves form values on validation failure,
 * and inserts new students into the users table.
 */
require_once(__DIR__ . '/../prefabs/auth.php');

ensureSessionStarted();
redirectAuthenticatedUser('dashboard.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Student / Register</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body id="top">
    <?php include_once('../prefabs/header.php'); ?>

    <?php
    require_once('../prefabs/database_connection.php');

    $nomErreur = $prenomErreur = $emailErreur = $passErreur = $filiereErreur = $niveauErreur = $groupeErreur = '';
    $FirstName = $LastName = $Email = '';
    $Password = $ConfirmPassword = '';
    $Filiere = $Niveau = $Groupe = '';
    $selectedFiliere = $selectedNiveau = $selectedGroupe = '';

    $filieresQuery = $database->query("SELECT Id, Libelle FROM filieres ORDER BY Id");
    $filieres = $filieresQuery->fetchAll(PDO::FETCH_ASSOC);

    $classOptionsQuery = $database->query(
        "SELECT
            g.FiliereId,
            g.nivId AS NiveauId,
            n.Libelle AS NiveauLibelle,
            g.Id AS GroupeId,
            g.Libelle AS GroupeLibelle
        FROM groupes g
        INNER JOIN niveaux n ON n.Id = g.nivId
        ORDER BY g.FiliereId, g.nivId, g.Id
    ");
    $classOptionsRows = $classOptionsQuery->fetchAll(PDO::FETCH_ASSOC);

    $classOptionsByFiliere = [];
    foreach ($classOptionsRows as $row) {
        $filiereId = (string) $row['FiliereId'];
        $niveauId = (string) $row['NiveauId'];

        if (!isset($classOptionsByFiliere[$filiereId])) {
            $classOptionsByFiliere[$filiereId] = [
                'niveaux' => [],
                'groupes' => [],
            ];
        }

        if (!isset($classOptionsByFiliere[$filiereId]['niveaux'][$niveauId])) {
            $classOptionsByFiliere[$filiereId]['niveaux'][$niveauId] = [
                'id' => $row['NiveauId'],
                'libelle' => $row['NiveauLibelle'],
            ];
        }

        if (!isset($classOptionsByFiliere[$filiereId]['groupes'][$niveauId])) {
            $classOptionsByFiliere[$filiereId]['groupes'][$niveauId] = [];
        }

        $classOptionsByFiliere[$filiereId]['groupes'][$niveauId][] = [
            'id' => $row['GroupeId'],
            'libelle' => $row['GroupeLibelle'],
        ];
    }

    function sanitizeInput($data)
    {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    if (!empty($_POST)) {
        // Handle form submission and validation here
        $valid = true;

        $FirstName = sanitizeInput($_POST['first_name'] ?? '');
        $LastName = sanitizeInput($_POST['last_name'] ?? '');
        $Email = sanitizeInput($_POST['email'] ?? '');
        $Password = $_POST['password'] ?? '';
        $ConfirmPassword = $_POST['confirm_password'] ?? '';

        $Filiere = sanitizeInput($_POST['filiere'] ?? '');
        $Niveau = sanitizeInput($_POST['niveau'] ?? '');
        $Groupe = sanitizeInput($_POST['groupe'] ?? '');

        if (empty($FirstName)) {
            $nomErreur = "First name is required";
            $valid = false;
        } elseif (strlen($FirstName) < 2 || strlen($FirstName) > 50) {
            $nomErreur = "First name must be between 2 and 50 characters";
            $valid = false;
        }

        if (empty($LastName)) {
            $prenomErreur = "Last name is required";
            $valid = false;
        } elseif (strlen($LastName) < 2 || strlen($LastName) > 50) {
            $prenomErreur = "Last name must be between 2 and 50 characters";
            $valid = false;
        }

        if (empty($Email)) {
            $emailErreur = "Email is required";
            $valid = false;
        } elseif (!filter_var($Email, FILTER_VALIDATE_EMAIL)) {
            $emailErreur = "Invalid email format";
            $valid = false;
        }

        if (empty($Password)) {
            $passErreur = "Password is required";
            $valid = false;
        } elseif (strlen($Password) < 8) {
            $passErreur = "Password must be at least 8 characters long";
            $valid = false;
        } elseif ($Password !== $ConfirmPassword) {
            $passErreur = "Passwords do not match";
            $valid = false;
        }

        if (empty($Filiere)) {
            $filiereErreur = "Veuillez selectionner une Filiere";
            $valid = false;
        }elseif (empty($Niveau)) {
            $niveauErreur = "Veuillez selectionner votre Niveau";
            $valid = false;
        }elseif (empty($Groupe)) {
            $groupeErreur = "Veuillez selectionner votre Groupe";
            $valid = false;
        }

        if ($valid) {
            // Additional validation (e.g., email format, password strength) can be added here
            $validationQuery = $database->prepare("SELECT COUNT(*) FROM utilisateurs WHERE Email = :email");
            $validationQuery->execute(['email' => $Email]);
            if ($validationQuery->fetchColumn() > 0) {
                $emailErreur = "Email is already registered";
                $valid = false;
            }

            if ($valid) {
                $reponse = $database->prepare("INSERT INTO utilisateurs (Prenom, Nom, Email, Password, Role, GroupeId, dateInscription) VALUES (:first_name, :last_name, :email, :password, :role, :groupe_id, :date_inscription)");
                $reponse->execute([
                    'first_name' => $FirstName,
                    'last_name' => $LastName,
                    'email' => $Email,
                    'password' => password_hash($Password, PASSWORD_DEFAULT),
                    'role' => 'Etudiant',
                    'groupe_id' => $Groupe,
                    'date_inscription' => date('Y-m-d H:i:s'),
                ]);

                header('Location: login.php');
                // Process the registration (e.g., save to database)
                // Redirect to login page or dashboard after successful registration
                exit;
            }
        }
    };

    $selectedFiliere = $Filiere;
    $selectedNiveau = $Niveau;
    $selectedGroupe = $Groupe;
    ?>

    <main class="container py-5 min-vh-100 d-flex align-items-center">
        <section class="p-4 p-md-5 w-100">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-xl-6">
                    <div class="card panel-card bg-white text-dark">
                        <div class="card-body p-4 p-md-4">
                            <div class="text-center mb-4">
                                <h1 class="h2 fw-bold mb-2">Create Account</h1>
                                <p class="text-secondary mb-0">Fill in your information to start using the classroom
                                    platform.</p>
                            </div>

                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <div class="mb-3">
                                    <label for="FirstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg" id="FirstName"
                                        name="first_name" placeholder="Enter your first name"
                                        value="<?php echo htmlspecialchars($FirstName, ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="form-text text-danger"><?php echo htmlspecialchars($nomErreur, ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>

                                <div class="mb-3">
                                    <label for="LastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg" id="LastName"
                                        name="last_name" placeholder="Enter your last name"
                                        value="<?php echo htmlspecialchars($LastName, ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="form-text text-danger"><?php echo htmlspecialchars($prenomErreur, ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>

                                <div class="mb-3">
                                    <label for="adrEmail" class="form-label">Email address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control form-control-lg" id="adrEmail" name="email"
                                        placeholder="prenom.nom@esen.tn"
                                        value="<?php echo htmlspecialchars($Email, ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="form-text text-danger"><?php echo htmlspecialchars($emailErreur, ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="mdp" class="form-label">Password <span
                                                class="text-danger">*</span></label>
                                        <input type="password" class="form-control form-control-lg" name="password"
                                            id="mdp" placeholder="Password">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="cmdp" class="form-label">Confirm password</label>
                                        <input type="password" class="form-control form-control-lg"
                                            name="confirm_password" id="cmdp" placeholder="Confirm">
                                    </div>
                                </div>

                                <span class="text-danger"><?php echo $passErreur; ?></span>

                                <div class="mb-3">
                                    <label for="filiere" class="form-label">Filière <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-lg" id="filiere" name="filiere">
                                        <option value="">Selectionner une Filiere</option>
                                        <?php
                                        foreach ($filieres as $filiere) {
                                            $isSelected = (string) $selectedFiliere === (string) $filiere['Id'] ? ' selected' : '';
                                            echo '<option value="' . htmlspecialchars($filiere['Id'], ENT_QUOTES, 'UTF-8') . '"' . $isSelected . '>' . htmlspecialchars($filiere['Libelle'], ENT_QUOTES, 'UTF-8') . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <span class="text-danger"><?php echo $filiereErreur; ?></span>
                                </div>

                                <div class="mb-3">
                                    <label for="niveau" class="form-label">Niveau <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-lg" id="niveau" name="niveau"></select>
                                    <span class="text-danger"><?php echo $niveauErreur; ?></span>
                                </div>

                                <div class="mb-3" id="groupe-wrapper">
                                    <label for="groupe" class="form-label">Groupe <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-lg" id="groupe" name="groupe"></select>
                                    <span class="text-danger"><?php echo $groupeErreur; ?></span>
                                </div>


                                <div class="d-grid gap-3">
                                    <button type="submit" class="btn btn-primary btn-lg">Create Account</button>
                                    <a href="login.php" class="btn btn-outline-secondary btn-lg">Already have an
                                        account?</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="../js/bootstrap.js"></script>
    <script>
        const classOptionsByFiliere = <?php echo json_encode($classOptionsByFiliere, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const initialSelection = {
            filiere: <?php echo json_encode((string) $selectedFiliere); ?>,
            niveau: <?php echo json_encode((string) $selectedNiveau); ?>,
            groupe: <?php echo json_encode((string) $selectedGroupe); ?>
        };

        const filiereSelect = document.getElementById('filiere');
        const niveauSelect = document.getElementById('niveau');
        const groupeSelect = document.getElementById('groupe');
        const niveauWrapper = niveauSelect.closest('.mb-3');
        const groupeWrapper = document.getElementById('groupe-wrapper');

        function fillSelect(select, options, selectedValue) {
            select.innerHTML = '';

            const placeholders = {
                niveau: 'Selectionner votre Niveau',
                groupe: 'Selectionner votre Groupe'
            };
            const placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.textContent = placeholders[select.id] || '';
            placeholderOption.selected = !selectedValue;
            select.appendChild(placeholderOption);

            options.forEach((optionData) => {
                const option = document.createElement('option');
                option.value = optionData.id;
                option.textContent = optionData.libelle;

                if (selectedValue && String(optionData.id) === String(selectedValue)) {
                    option.selected = true;
                }

                select.appendChild(option);
            });

            select.disabled = false;
        }

        function removePlaceholder(select) {
            const placeholderOption = select.querySelector('option[value=""]');
            if (placeholderOption && select.value !== '') {
                placeholderOption.remove();
            }
        }

        function toggleDependentFields() {
            const hasFiliere = filiereSelect.value !== '';
            const hasNiveau = niveauSelect.value !== '';

            niveauWrapper.style.display = hasFiliere ? '' : 'none';
            groupeWrapper.style.display = hasFiliere && hasNiveau ? '' : 'none';

            if (!hasFiliere) {
                niveauSelect.innerHTML = '';
                groupeSelect.innerHTML = '';
            } else if (!hasNiveau) {
                groupeSelect.innerHTML = '';
            }
        }

        function updateGroupes(selectedGroupe = '') {
            toggleDependentFields();

            if (filiereSelect.value === '' || niveauSelect.value === '') {
                return;
            }

            const filiereData = classOptionsByFiliere[filiereSelect.value] || { groupes: {} };
            const groupes = filiereData.groupes?.[niveauSelect.value] || [];
            fillSelect(groupeSelect, groupes, selectedGroupe);
            removePlaceholder(groupeSelect);
            toggleDependentFields();
        }

        function updateNiveaux(selectedNiveau = '', selectedGroupe = '') {
            toggleDependentFields();

            if (filiereSelect.value === '') {
                return;
            }

            const filiereData = classOptionsByFiliere[filiereSelect.value] || { niveaux: {} };
            const niveaux = Object.values(filiereData.niveaux || {});
            fillSelect(niveauSelect, niveaux, selectedNiveau);
            removePlaceholder(niveauSelect);
            toggleDependentFields();
            updateGroupes(selectedGroupe);
        }

        filiereSelect.addEventListener('change', () => {
            updateNiveaux();
            removePlaceholder(filiereSelect);
        });

        niveauSelect.addEventListener('change', () => {
            updateGroupes();
            removePlaceholder(niveauSelect);
        });

        groupeSelect.addEventListener('change', () => {
            removePlaceholder(groupeSelect);
        });

        toggleDependentFields();
        updateNiveaux(initialSelection.niveau, initialSelection.groupe);
        removePlaceholder(filiereSelect);
    </script>
</body>

</html>
