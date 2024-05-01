<?php require 'db.php'; //présent dans tous les fichiers, permet de d'inclure le fichier de connexion à la BDD 
require 'login.php'; //présent dans tous les fichiers, permet de d'inclure le fichier de connexion des utilisateurs à l'application WEB 
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Student Management System</title>
</head>
<body class="body-index">
    <div class="container">
        <h1>Welcome to the Student Management System</h1>
        <!-- Pour voir message d'erreur ou de bonne réalisation -->
        <?php if (!empty($errorMsg)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($errorMsg); ?>
        </div>
        <?php endif; ?>
        <?php if (!empty($successMsg)): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($successMsg); ?>
        </div>
        <?php endif; ?>
        <!-- Formulaire de connexion des utilisateurs -->
        <div class="login-form">
            <form method="post" action="index.php">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <button type="submit">Connexion</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
