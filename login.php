<?php
session_start();
// Regenerate session ID
session_regenerate_id(true);
header("X-Frame-Options: DENY");
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

include 'db.php';  // Assure-toi que ce fichier contient la connexion à ta base de données

// if (!isset($_SERVER['HTTP_REFERER'])) {
//     // HTTP_REFERER n'est pas défini si la page est accédée directement
//     header("Location: index.php");
//     exit;
// }

// Vérification de la demande de logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    // Détruire toutes les variables de session
    $_SESSION = array();
    
    // Supprimer le cookie de session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Détruire la session
    session_destroy();
    
    // Rediriger vers la page de login
    header("Location: index.php");
    exit;
}



// Check if the LOGIN form is submitted
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
 
            //Redirect to the role's page
            switch ($_SESSION['role']) {
                case 'teacher':
                    // Redirection vers le tableau de bord des enseignants
                    header("Location: teacherDashboard.php");
                    exit;
                case 'staff':
                    // Redirection vers le tableau de bord du personnel
                    header("Location: staffDashboard.php");
                    exit;
                case 'superstaff':
                    // Redirection vers le tableau de bord du super personnel
                    header("Location: superStaffDashboard.php");
                    exit;
                case 'admin':
                    // Redirection vers le tableau de bord de l'administrateur
                    header("Location: adminDashboard.php");
                    exit;
                default:
                    // Redirection pour les rôles non reconnus ou autres cas
                    header("Location: index.php");
                    exit;
            }
        } else {
            $errorMsg = "Invalid email or password.";
        }
    }
}
?>