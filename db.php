<?php
$serverName = "localhost";
$username = "manager";
$password = "managermanager";
$dbName = "student_management";

try {
    $con = new PDO("mysql:host=$serverName;dbname=$dbName", $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $successMsg = "Connexion réussie à la base de données SQL Server";
} catch (PDOException $e) {
    $errorMsg = "Erreur de connexion à la base de données";
}
?>
