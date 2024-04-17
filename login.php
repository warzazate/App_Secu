<?php
session_start();
// Secure session cookie
session_regenerate_id(true);
header("X-Frame-Options: DENY");
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

include 'db.php';  // Assure-toi que ce fichier contient la connexion à ta base de données
$message = "";  // Message pour afficher les erreurs ou confirmations


$hash = password_hash("fautlecrypter", PASSWORD_BCRYPT, ["cost" => 12]);

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (empty($email) || empty($password)) {
        echo "Both fields are required.";
    } else {
        $sql = "SELECT id, password, role FROM users WHERE email = :email";
        $stmt = $con->prepare($sql);
        
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        

        if ($user && password_verify($password, $user['password'])) {
            // Password is correct, start a session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = time(); // record login time for session timeout

            // Redirect to a new page or dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            echo "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <form method="post" action="login.php">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
