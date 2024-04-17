<?php
include 'db.php'; // Assure-toi que ce fichier inclut la connexion à ta base de données

// Traitement du formulaire après soumission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $age = $_POST['age'];

    // Préparer la requête SQL pour insérer les données
    $insertStmt = $con->prepare("INSERT INTO students (first_name, last_name, email, age) VALUES (?, ?, ?, ?)");
    $insertStmt->bindValue(1, $first_name);
    $insertStmt->bindValue(2, $last_name);
    $insertStmt->bindValue(3, $email);
    $insertStmt->bindValue(4, $age);

    // Exécuter la requête
    try {
        $insertStmt->execute();
        // Redirection vers la page principale après l'insertion
        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        die("Erreur lors de l'ajout de l'étudiant : " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Student</title>
</head>
<body>
    <h1>Add New Student</h1>
    <form method="post">
        First Name: <input type="text" name="first_name" required><br>
        Last Name: <input type="text" name="last_name" required><br>
        Email: <input type="email" name="email" required><br>
        Age: <input type="number" name="age" required><br>
        <input type="submit" value="Add Student">
    </form>
</body>
</html>