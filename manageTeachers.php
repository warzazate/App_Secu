<?php
session_start();//Démarre une nouvelle session ou reprend une session existante (ici reprend la session existante)

// Vérifie si l'utilisateur est connecté et a un rôle adéquat. Si l'utilisateur n'est pas connecté ou n'a pas le bon rôle, il est redirigé vers la page de connexion
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || ($_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superstaff')) {
    header("Location: index.php");
    exit;
}

require 'db.php'; // Connexion à la base de données

$action = $_GET['action'] ?? 'list'; // "action" de l'utilisateur mise à "list" si aucune n'est spécifiée
$user_id = $_GET['user_id'] ?? 0; // // Récupère l'identifiant des enseignants (teachers) cible pour les actions "edit" ou "delete"

// Traitement des requêtes POST pour modifier
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($action == 'edit')) {
    // Extraction et validation des données du formulaire
    // Préparation et exécution des requêtes SQL pour modifier
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $department = $_POST['department'] ?? '';
    $hireDate = $_POST['hire_date'] ?? '';

    //Prépare et exécute une requête SQL
    $stmt = $con->prepare("UPDATE teachers SET first_name = ?, last_name = ?, department = ?, hire_date = ? WHERE user_id = ?");
    $stmt->execute([$firstName, $lastName, $department, $hireDate, $user_id]);
    $successMsg = "Teacher updated successfully.";
    $action = 'list';  //remet l'action par défaut : "list"
}

//Pour créer un utilisateur dans la BDD + un teacher (lien des deux tables)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($action == 'create')) {
    // Collecter les données du formulaire
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $department = $_POST['department'] ?? '';
    $hireDate = $_POST['hire_date'] ?? '';

    try {
        $con->beginTransaction(); //utilisation de "transaction" pour effectuer plusieurs requêtes dans la BDD avec un seul formulaire
        // Insertion dans la table users
        $stmt = $con->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'teacher')");
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);
        $stmt->execute([$email, $passwordHash]);

        $user_id = $con->lastInsertId(); //récupère l'id du dernier utilisateur créé

        // Insertion dans la table teachers
        $stmt = $con->prepare("INSERT INTO teachers (user_id, first_name, last_name, department, hire_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $firstName, $lastName, $department, $hireDate]);

        $con->commit(); //lancement de la transaction pour que tout s'effectue en même temps (valide la transaction)
        $successMsg = "Teacher and user profile created successfully.";
    } catch (Exception $e) {
        $con->rollBack(); //si une étape dans la transaction n'a pas fonctionné, cela annule la transaction et affiche l'erreur
        $errorMsg = "Failed to create teacher profile: ";
    }
    $action = 'list'; //remet l'action par défaut : "list"
}


// Code pour supprimer un professeur (teacher)
if ($action == 'delete' && isset($_GET['user_id'])) { 
    $user_id = $_GET['user_id'];
    //Prépare et exécute une requête SQL
    $stmt = $con->prepare("DELETE FROM teachers WHERE user_id = ?");
    $stmt->execute([$user_id]);

    try {
        // Commencez une transaction
        $con->beginTransaction();

        // Supprimez d'abord l'entrée dans la table teachers
        $stmt = $con->prepare("DELETE FROM teachers WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Ensuite, supprimez l'entrée correspondante dans la table users
        $stmt = $con->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        // Validez la transaction
        $con->commit();
        $successMsg = "Teacher and user profile deleted successfully.";
    } catch (Exception $e) {
        // En cas d'erreur, annulez la transaction
        $con->rollBack();
        $errorMsg = "Failed to delete teacher profile";
    }
    $action = 'list'; //remet l'action par défaut : "list"
}

// Récupère la liste de tous les enseignant (teachers) pour les lister
//Prépare et exécute une requête SQL pour récupérer la liste de tous les enseignant (teachers) pour les lister
$stmt = $con->prepare("SELECT * FROM teachers");
$stmt->execute();
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Manage Teachers</h1>
    <!-- Code pour afficher la liste des professeurs et les formulaires de création ou de modification -->
    <?php if ($action == 'list'): ?>
        <button onclick="window.location.href='?action=create';">Add New Teacher</button>
        <table border="1">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Department</th>
                    <th>Hire Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teachers as $teacher): ?>
                <tr>
                    <td><?= htmlspecialchars($teacher['user_id']) ?></td>
                    <td><?= htmlspecialchars($teacher['first_name']) ?></td>
                    <td><?= htmlspecialchars($teacher['last_name']) ?></td>
                    <td><?= htmlspecialchars($teacher['department']) ?></td>
                    <td><?= htmlspecialchars($teacher['hire_date']) ?></td>
                    <td>
                        <button onclick="window.location.href='?action=edit&user_id=<?= htmlspecialchars($teacher['user_id']) ?>';">Edit</button>
                        <button onclick="if(confirm('Are you sure you want to delete this teacher?')) { window.location.href='?action=delete&user_id=<?= htmlspecialchars($teacher['user_id']) ?>'; } return false;">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <!-- Pour lancer le script de création d'un teacher -->
    <?php if ($action == 'create'):
        ?>
        <form method="post">
            <label for="email">Email:</label>
                <input type="email" id="email" name="email" required><br>
            <label for="password">Password:</label>
                <input type="password" id="password" name="password" required><br>
            <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required><br>
            <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required><br>
            <label for="department">Department:</label>
                <input type="text" id="department" name="department" required><br>
            <label for="hire_date">Hire Date:</label>
                <input type="date" id="hire_date" name="hire_date" required><br>
        <button type="submit">Submit</button>
    </form>
    <?php endif; ?>


    <!-- Pour lancer le script de modification d'un teacher -->
    <?php if ($action == 'edit'): 
        // Vérification de l'ID du teacher et récupération des données
        if (isset($_GET['user_id']) && $action == 'edit') {
            $id = $_GET['user_id'];
            $stmt = $con->prepare("SELECT * FROM teachers WHERE user_id = ?;");
            $stmt->execute([$id]);
            $teacherChoosed = $stmt->fetch();
            if (!$teacherChoosed) {
                die('Teacher not found!');
            }
        }else {
            die('Invalid request!');
        }
        ?>
        <form method="post" action="?action=<?= htmlspecialchars($action) ?>&user_id=<?= htmlspecialchars($user_id) ?>">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" value="<?= $action == 'edit' ? htmlspecialchars($teacherChoosed['first_name']) : '' ?>" required><br>
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" value="<?= $action == 'edit' ? htmlspecialchars($teacherChoosed['last_name']) : '' ?>" required><br>
            <label for="department">Department:</label>
            <input type="text" id="department" name="department" value="<?= $action == 'edit' ? htmlspecialchars($teacherChoosed['department']) : '' ?>" required><br>
            <label for="hire_date">Hire Date:</label>
            <input type="date" id="hire_date" name="hire_date" value="<?= $action == 'edit' ? htmlspecialchars($teacherChoosed['hire_date']) : '' ?>" required><br>
            <button type="submit"><?= $action == 'create' ? 'Create Teacher' : 'Update Teacher' ?></button>
        </form>
    <?php endif; ?>

    <!-- affiche un message de validation si tout s'est bien déroulé -->
    <?php if (!empty($successMsg)): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($successMsg); ?>
        </div>
    <?php endif; ?>
    <!-- affiche un message d'erreur s'il y en a eu un précedemment -->
    <?php if (!empty($errorMsg)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($errorMsg); ?>
        </div>
    <?php endif; ?>

    <!-- bouton de redirection vers le dashboard du staff -->
    <button onclick="window.location.href='staffDashboard.php';">Retour sur l'écran général du Staff </button>
</body>
</html>
