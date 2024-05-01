<?php
session_start(); //Démarre une nouvelle session ou reprend une session existante (ici reprend la session existante)

// Vérifiez si l'utilisateur est connecté et est administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord de l'Administrateur</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Tableau de Bord de l'Administrateur</h1>
    <!-- liste les différents tableaux de bord des utilisateurs avec moins de privilèges -->
    <div>
        <button onclick="window.location.href='teacherDashboard.php';">Tableau de Bord Enseignants</button>
        <button onclick="window.location.href='staffDashboard.php';">Tableau de Bord Staff</button>
        <button onclick="window.location.href='superStaffDashboard.php';">Tableau de Bord SuperStaff</button>
        <!-- <button onclick="window.location.href='manageClasses.php';">Gérer les Classes</button> 
        Ce bouton ne fonctionne pas encore-->
    </div>
    <div>
        <button onclick="window.location.href='login.php?action=logout';" class="logout_button">Déconnexion</button>
    </div>
</body>
</html>
