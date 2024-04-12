<?php
$serverName = "localhost\\SQLEXPRESS"; // ajuste le nom du serveur et l'instance
$database = "student_management"; // nom de ta base de données
$username = "manager"; // ton nom d'utilisateur SQL Server
$password = "managermanager"; // ton mot de passe SQL Server

try {
    $conn = new PDO("sqlsrv:server=$serverName;database=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie à la base de données SQL Server";
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}


?>
