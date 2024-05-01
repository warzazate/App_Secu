<?php
session_start(); //Démarre une nouvelle session ou reprend une session existante (ici démarre la session)
// Regenerate session ID
session_regenerate_id(true); //Régénère l'ID de la session pour protéger contre les attaques XSS
//Header de sécurité
header("X-Frame-Options: DENY"); //empêche l'incorporation de la page dans des iframes, protégeant contre les attaques de clickjacking.
header('X-Content-Type-Options: nosniff'); // bloque les navigateurs de tenter de sniff le MIME type, ce qui peut prévenir certains types d'attaques.
header('X-XSS-Protection: 1; mode=block'); //active les filtres de protection XSS des navigateurs et configure pour bloquer la page entière si une attaque est détectée.

require 'db.php';  // Connexion à la base de données

// Gestion de la déconnexion : 
if (isset($_GET['action']) && $_GET['action'] == 'logout') { //vérifie si l'utilisateur a demandé à se déconnection (action = logout)
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


// Traitement de la connexion des utilisateurs
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validation basique
    if (empty($email) || empty($password)) {
        $errorMsg = "Both fields are required."; // Vérifie que les champs email et mot de passe ne sont pas vides
    } else { 
        $sql = "SELECT id, password, role FROM users WHERE email = :email"; //Prépare et exécute une requête SQL pour rechercher l'utilisateur par email.
        $stmt = $con->prepare($sql);
        
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        
        //Si utilisateur trouvé, il compare le mot de passe saisi avec le hash enregistré dans la base de données.
        if ($user && password_verify($password, $user['password'])) {
            // Redémarre la session avec des paramètres de cookie sécurisés
            session_start([
                'cookie_lifetime' => 604800, // Vie du cookie : 1 semaine
                'cookie_secure' => true, // Met en "true" si l'utilisateur utilise HTTPS
                'cookie_httponly' => true // True signifie que JavaScript ne peut pas accéder au cookie
            ]);
                            
            // Enregistre les informations de l'utilisateur dans les variables de session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
 
            //Redirige l'utilisateur vers une page spécifique basée sur son rôle. Si aucun rôle n'est reconnu, redirige vers la page de login.
            switch ($_SESSION['role']) {
                case 'teacher':
                    // Redirection vers le tableau de bord des enseignants (teachers)
                    header("Location: teacherDashboard.php");
                    exit;
                case 'staff':
                    // Redirection vers le tableau de bord du personnel (staff)
                    header("Location: staffDashboard.php");
                    exit;
                case 'superstaff':
                    // Redirection vers le tableau de bord du super personnel (superstaff)
                    header("Location: superStaffDashboard.php");
                    exit;
                case 'admin':
                    // Redirection vers le tableau de bord de l'administrateur (admin)
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