<?php
include 'db.php';  // Assure-toi que ce fichier contient la connexion à ta base de données

$message = "";  // Message pour afficher les erreurs ou confirmations


// Récupérer la liste des tables
try {
    $stmt = $con->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_NAME='students' OR TABLE_NAME='admin' OR TABLE_NAME='teachers'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des tables : " . $e->getMessage());
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $con->prepare("SELECT id, password_hash FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");  // Rediriger vers la page d'accueil ou le tableau de bord
            exit;
        } else {
            $message = "Invalid username or password.";
        }
    } else {
        $message = "Username and password are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <!-- Formulaire de login -->
    <form action="authenticate.php" method="post">
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>

        <!-- Menu déroulant des tables -->
        <label for="tables">Choose a table:</label>
        <select name="tables" id="tables">
            <?php foreach ($tables as $table): ?>
                <option value="<?= htmlspecialchars($table) ?>"><?= htmlspecialchars($table) ?></option>
            <?php endforeach; ?>
        </select>

        <input type="submit" value="Login">
    </form>
</body>
</html>