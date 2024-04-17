<?php
include 'db.php';

// Vérification de l'ID de l'étudiant et récupération des données
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Vérifie d'abord si l'étudiant existe
    $stmt = $con->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if (!$student) {
        die('Student not found!');
    }

    // Traitement de la suppression de l'étudiant
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Suppression de l'étudiant de la base de données
        $deleteStmt = $con->prepare("DELETE FROM students WHERE id = ?");
        $deleteStmt->execute([$id]);

        // Redirection vers la page principale après la suppression
        header("Location: index.php");
        exit;
    }
} else {
    die('Invalid request!');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Student</title>
</head>
<body>
    <h1>Delete Student</h1>
    <p>Are you sure you want to delete the student <?php echo htmlspecialchars($student['first_name']) . " " . htmlspecialchars($student['last_name']); ?>?</p>
    <form method="post">
        <input type="submit" value="Delete Student">
    </form>
    <a href="index.php">Cancel</a>
</body>
</html>