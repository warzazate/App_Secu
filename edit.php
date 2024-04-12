<?php
include 'db.php';

// Vérification de l'ID de l'étudiant et récupération des données
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        die('Student not found!');
    }
} else {
    die('Invalid request!');
}

// Traitement du formulaire de mise à jour
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $age = $_POST['age'];

    // Mise à jour des données de l'étudiant dans la base de données
    $updateStmt = $pdo->prepare("UPDATE students SET first_name = ?, last_name = ?, email = ?, age = ? WHERE id = ?");
    $updateStmt->execute([$first_name, $last_name, $email, $age, $id]);

    // Redirection vers la page principale après mise à jour
    header("Location: index.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
</head>
<body>
    <h1>Edit Student</h1>
    <form method="post">
        First Name: <input type="text" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" required><br>
        Last Name: <input type="text" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" required><br>
        Email: <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required><br>
        Age: <input type="number" name="age" value="<?php echo htmlspecialchars($student['age']); ?>" required><br>
        <input type="submit" value="Update Student">
    </form>
</body>
</html>
