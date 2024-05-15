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


//ANTI BRUTE FORCE
// Vérification si l'utilisateur a atteint le nombre maximum de tentatives de connexion autorisées
$attempts = $_SESSION['login_attempts'] ?? 0;
$last_attempt_time = $_SESSION['last_attempt_time'] ?? 0;
$locked = false;

// Durées de blocage après les tentatives échouées
$lock_times = [15, 300, 7200];  // 1 minute, 5 minutes, 2 heures


if ($attempts >= 3) {  // Commence le blocage après 3 tentatives échouées
    $index = $attempts - 3; // Ajuste l'index pour les temps de blocage
    if ($index < count($lock_times) && time() < $last_attempt_time + $lock_times[$index]) {
        $locked = true;
        $timeRemaining = $last_attempt_time + $lock_times[$index] - time();
        if ($timeRemaining > 3600) {
            $hours = floor($timeRemaining / 3600);
            $minutes = floor(($timeRemaining % 3600) / 60);
            $seconds = $timeRemaining % 60;
            $timeMsg = "{$hours} hour(s), {$minutes} minute(s), and {$seconds} second(s)";
        } else {
            $timeMsg = date("i:s", $timeRemaining) . " minutes";
        }
        $errorMsg = "Too many failed login attempts. Please try again after $timeMsg.";
    }else {
        $errorMsg = "Invalid email or password. Attempts left: 1.";
    }
}

// Inclure la logique de connexion seulement si l'utilisateur n'est pas bloqué
if (!$locked) {
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
                // Réinitialisation des tentatives après une connexion réussie
                $_SESSION['login_attempts'] = 0;
                $_SESSION['last_attempt_time'] = null;
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
                // Incrémentation du nombre de tentatives et mise à jour du temps de la dernière tentative
                $_SESSION['login_attempts'] = $attempts + 1;
                $attempts = $attempts + 1;
                $_SESSION['last_attempt_time'] = time();

                if ($attempts < 3) {  // Commence le blocage après 3 tentatives échouées
                    $remainingAttempts = 3 - ($attempts % 3);
                    $errorMsg = "Invalid email or password. Attempts left: " . ($remainingAttempts > 0 ? $remainingAttempts : 0);
                } else{
                    //MàJ de la page
                    header("Location: index.php");
                }
            }
        }
    }
}
?>