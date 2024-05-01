<?php
session_start();//Démarre une nouvelle session ou reprend une session existante (ici reprend la session existante)

// Vérifie si l'utilisateur est connecté et a un rôle adéquat. Si l'utilisateur n'est pas connecté ou n'a pas le bon rôle, il est redirigé vers la page de connexion
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] != 'teacher' && $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

require 'db.php'; // Connexion à la base de données

// ID du teacher présent dans la session
$teacher_id = $_SESSION['user_id'];

//Redirige vers un autre tableau de bord si l'utilisateur a plus de privilèges
$role = $_SESSION['role'] ?? 'none'; // Par defaut est 'none' si l'utilisateur n'a pas de rôle
//Montrer le bouton spécifique suivant le rôle
switch ($role) {
    case 'staff':
        echo '<button onclick="window.location.href=\'staffDashboard.php\';" class="redirect">Go to Staff Dashboard</button>';
        break;
    case 'superstaff':
        echo '<button onclick="window.location.href=\'superstaffDashboard.php\';" class="redirect">Go to Superstaff Dashboard</button>';
        break;
    case 'admin':
        echo '<button onclick="window.location.href=\'adminDashboard.php\';" class="redirect">Go to Admin Dashboard</button>';
        break;
    default:
        break;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord du Professeur</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Liste des Étudiants</h1>
    <!-- Tableau pour affichier la liste des étudants -->
    <table border="1">
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Date of birth</th>
            <th>Gender</th>
            <th>Address</th>
            <th>Phone number</th>
        </tr>
        <?php
        // Récupère l'ensemble des étudiants (students) pour lesquels ils ont comme prof (teacher) celui connecté actuellement
        //Prépare et exécute la requête SQL
        $stmt = $con->prepare("SELECT * FROM students WHERE teacher_id = ?");
        $stmt->execute([$teacher_id]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['date_of_birth']) . "</td>";
            echo "<td>" . htmlspecialchars($row['gender']) . "</td>";
            echo "<td>" . htmlspecialchars($row['address']) . "</td>";
            echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
            echo "</tr>";
        }
        ?>
    </table>
    <!-- affiche un message d'erreur -->
    <?php if (!empty($errorMsg)): ?>
    <div class="error-message">
        <?php echo htmlspecialchars($errorMsg); ?>
    </div>
    <?php endif; ?>
    
    <!-- bouton de déconnexion -->
    <a href="login.php?action=logout" class="logout_button">Déconnexion</a>
</body>
</html>
