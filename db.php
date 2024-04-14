<?php
$serverName = "localhost";
$username = "manager";
$password = "managermanager";
$dbName = "student_management";

try {
    $con = new PDO("mysql:host=$serverName;dbname=$dbName", $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie à la base de données SQL Server";
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}

?>
