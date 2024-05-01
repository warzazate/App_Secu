<?php
session_start(); //Démarre une nouvelle session ou reprend une session existante (ici reprend la session existante)

// Vérifie si l'utilisateur est connecté et a un rôle adéquat. Si l'utilisateur n'est pas connecté ou n'a pas le bon rôle, il est redirigé vers la page de connexion
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] != 'superstaff' && $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

require 'db.php'; // Connexion à la base de données

$action = $_GET['action'] ?? 'list'; // "action" de l'utilisateur mise à "list" si aucune n'est spécifiée
$id = $_GET['id'] ?? 0; // Récupère l'identifiant de l'étudiant cible pour les actions "edit" ou "delete"

// Traitement des requêtes POST pour créer ou modifier un membre du staff
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($action == 'create' || $action == 'edit')) {
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';

    // Spécifique pour la création
    if ($action == 'create') {
        // Validation basique
        if (empty($email) || empty($password)) {
            $errorMsg = "Both fields are required."; // Vérifie que les champs email et mot de passe ne sont pas vides
        } else {
            // Vérifie que l'adresse email n'existe pas déjà
            $stmt = $con->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                $errorMsg = "Email already used!";
            } else {
                // Hash le mot de passe avec BCRYPT avec un coût de 12
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);

                // Insert le nouvel utilisateur dans la base de données
                $stmt = $con->prepare("INSERT INTO users (email, password, role) VALUES (:email, :password, :role)"); //Prépare la requête SQL
                //Ajoute chaque paramètre du formulaire dans la requête
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':role', $role);

                if ($stmt->execute()) { //Execute la requête SQL
                    $successMsg = "User registered successfully.";
                } else {
                    $errorMsg = "Error registering user.";
                }
            }
        }
    } elseif ($id) { //cas de la modification d'un staff
        $stmt = $con->prepare("UPDATE users SET email = ?, role = ? WHERE id = ?");
        $stmt->execute([$email, $role, $id]);
        $successMsg = "Staff member updated successfully.";
    }
    $action = 'list';
}

// Code pour supprimer un membre du staff
if ($action == 'delete' && $id) {
    //Prépare et exécute une requête SQL
    $stmt = $con->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $successMsg = "Staff member deleted successfully.";
    $action = 'list'; //remet l'action par défaut : "list"
}

// Prépare et exécute une requête SQL pour récupérer toute la liste des staff pour les lister (en excluant superstaff, admin et teacher)
$stmt = $con->prepare("SELECT * FROM users WHERE role = 'staff'");
$stmt->execute();
$staffMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);


//Redirige vers un autre tableau de bord si l'utilisateur a plus de privilèges
$role = $_SESSION['role'] ?? 'none'; // Par defaut est 'none' si l'utilisateur n'a pas de rôle
//Montrer le bouton spécifique pour le rôle d'admin
if($role == 'admin') {
    echo '<button onclick="window.location.href=\'adminDashboard.php\';" class="redirect">Go to Admin Dashboard</button>';
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff (Superstaff Dashboard)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Manage Staff (for Superstaff)</h1>

    <?php if ($action == 'list'): ?>
        <button onclick="window.location.href='?action=create';">Add New Staff Member</button>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staffMembers as $member): ?>
                <tr>
                    <td><?= htmlspecialchars($member['id']) ?></td>
                    <td><?= htmlspecialchars($member['email']) ?></td>
                    <td><?= htmlspecialchars($member['role']) ?></td>
                    <td>
                        <!-- bouton qui demande la confirmation avant de lancer la suppression de membre du staff -->
                        <button onclick="window.location.href='?action=edit&id=<?= htmlspecialchars($member['id']) ?>';">Edit</button>
                        <button onclick="if(confirm('Are you sure you want to delete this staff member?')) { window.location.href='?action=delete&id=<?= htmlspecialchars($member['id']) ?>'; } return false;">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($action == 'create' || $action == 'edit'): 
        // Vérification de l'ID de l'étudiant et récupération des données
        if (isset($_GET['id']) && $action == 'edit') {
            $id = $_GET['id'];
            $stmt = $con->prepare("SELECT * FROM users WHERE id = ? && role = 'staff';"); //ne prend que des STAFF !
            $stmt->execute([$id]);
            $staffMemberChoosed = $stmt->fetch();
            
            if (!$staffMemberChoosed) {
                die('Staff member not found!');
            }
        // Laisse passer si c'est pour créé un membre du staff
        } elseif ($action == 'create') {
            echo "";
        } else {
            die('Invalid request!');
        }
    ?>
        <form method="post" action="?action=<?= htmlspecialchars($action) ?>&id=<?= htmlspecialchars($id) ?>">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= $action == 'edit' ? htmlspecialchars($staffMemberChoosed['email']) : '' ?>" required><br>
            <label for="role">Role:</label>
            <select id="role" name="role">
                <option value="staff" <?= $action == 'edit' && $staffMemberChoosed['role'] == 'staff' ? 'selected' : '' ?>>Staff</option>
            </select><br>
            <?php if ($action == 'create'): ?>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            <?php endif; ?>
            <button type="submit"><?= $action == 'create' ? 'Create Staff Member' : 'Update Staff Member' ?></button>
        </form>
    <?php endif; ?>
    <!-- redirection vers le tableau de bord du staff -->
    <div>
        <button onclick="window.location.href='staffDashboard.php';">Tableau de Bord Staff</button>
    </div>
    <!-- bouton de déconnexion -->
    <div>
        <button onclick="window.location.href='login.php?action=logout';" class="logout_button">Déconnexion</button>
    </div>

    <!-- affiche un message de validation si tout s'est bien déroulé -->
    <?php if (!empty($successMsg)): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($successMsg); ?>
        </div>
    <?php endif; ?>
</body>
</html>
