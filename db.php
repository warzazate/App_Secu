<?php
// Page de code permettant la connexion à la base de données 
$serverName = "localhost";  // Le nom du serveur où la base de données est hébergée (ici en local)
$username = "manager";      // Le nom d'utilisateur pour se connecter à la base de données
$password = "managermanager";  // Le mot de passe associé à l'utilisateur pour la connexion
$dbName = "student_management";  // Le nom de la base de données à laquelle se connecter

//Cette requête tente une nouvelle instance de connexion à la base de données qui nous permettra de faire toutes les requêtes sur celle-ci dans l'application WEB
try {
    $con = new PDO("mysql:host=$serverName;dbname=$dbName", $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // $successMsg = "Connexion réussie à la base de données SQL Server";
} catch (PDOException $e) {
    // $errorMsg = "Erreur de connexion à la base de données";
    // Plutôt ajouter, si on a le temps, un message de log dans un fichier dédié sécurisé !)
}
?>
