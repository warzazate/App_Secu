<?php
session_start();

// Vérifier si l'utilisateur est connecté et s'il est un membre du personnel
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superstaff') {
    // Si l'utilisateur n'est pas staff, admin ou superstaff, rediriger vers la page de login
    header("Location: login.php");
    exit;
}

// Inclusion du fichier de connexion à la base de données
include 'db.php';


//TO REDIRECT to other dashboard if user have more privileges
$role = $_SESSION['role'] ?? 'none'; // Default to 'none' if not set
// Output the button based on the role
switch ($role) {
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
    <title>Tableau de Bord du Staff</title>
    <link rel="stylesheet" href="css\style.css">
</head>
<body>
    <h1>Tableau de Bord du Staff</h1>
    <div>
        <h2>Gestion du personnel</h2>
        <button onclick="window.location.href='manageStudents.php';">Gérer les Étudiants</button>
        <button onclick="window.location.href='manageTeachers.php';">Gérer les professeurs</button>
    </div>
    <div>
        <h2>Communication et Logistique</h2>
        <!-- <button onclick="window.location.href='manageClasses.php';">Gérer les Classes</button> 
    Ce bouton ne fonctionne pas encore-->
        <button onclick="window.location.href='upload_certificate.php';">Ajouter certificat de scolarité</button>
    </div>
    <div>
        <button onclick="window.location.href='login.php?action=logout';" class="logout_button">Déconnexion</button>
    </div>
</body>
</html>
