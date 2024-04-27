<?php
session_start();
// Regenerate session ID
session_regenerate_id(true);
header("X-Frame-Options: DENY");
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

include 'db.php';  // Assure-toi que ce fichier contient la connexion à ta base de données
$message = "";  // Message pour afficher les erreurs ou confirmations

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (empty($email) || empty($password)) {
        $errorMsg = "Both fields are required.";
    } else {
        $sql = "SELECT id, password, role FROM users WHERE email = :email";
        $stmt = $con->prepare($sql);
        
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        

        if ($user && password_verify($password, $user['password'])) {
            // Start the session and set cookie parameters securely
            session_start([
                'cookie_lifetime' => 604800, // 1 week
                'cookie_secure' => true, // Set to true if using HTTPS
                'cookie_httponly' => true // True means JavaScript can't access the cookie
            ]);
                            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
        
            // Redirect to the dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            $errorMsg = "Invalid email or password.";
        }
    }
}
?>