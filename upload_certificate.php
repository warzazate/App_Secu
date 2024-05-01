<?php
session_start(); //Démarre une nouvelle session ou reprend une session existante (ici reprend la session existante)

// Vérifie si l'utilisateur est connecté et a un rôle adéquat. Si l'utilisateur n'est pas connecté ou n'a pas le bon rôle, il est redirigé vers la page de connexion
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || ($_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superstaff')) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fileToUpload'])) {
    $file = $_FILES['fileToUpload']; //récupération des données issus du fichier uploadé
    $errorMsg = ''; // Initialiser $errorMsg
    
    // Définir le chemin de destination
    $uploadDir = "uploads/";
    $uploadFile = $uploadDir . basename($file['name']);

    // Première vérification : type de fichier (Vérifier si le fichier est un PDF par son extension)
    $fileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
    if ($fileType !== 'pdf') {
        $errorMsg = "Sorry, only PDF files are allowed.";
    }

    // // Deuxième vérification : type MIME, seulement si aucune erreur n'est encore détectée (pour s'assurer que c'est un PDF)
    if (empty($errorMsg)) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if ($mime != 'application/pdf') {
            $errorMsg = "Sorry, only PDF files are allowed.";
        }
    }

    // Troisième vérification : existence préalable du fichier
    if (empty($errorMsg) && file_exists($uploadFile)) {
        $errorMsg = "Sorry, file already exists.";
    }

    // Quatrième vérification : taille du fichier
    if (empty($errorMsg) && $file['size'] > 5000000) { // 5 Megabytes
        $errorMsg = "Sorry, your file is too large.";
    }

    // Tentative de déplacement du fichier, seulement si aucune erreur n'est encore détectée
    if (empty($errorMsg) && move_uploaded_file($file['tmp_name'], $uploadFile)) {
        $successMsg = "The file ". htmlspecialchars(basename($file['name'])) . " has been uploaded.";
    } else {
        $errorMsg = "Sorry, there was an error uploading your file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload PDF File</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Upload PDF File</h1>
    <!-- formulaire d'envoi de fichier -->
    <form action="upload_certificate.php" method="post" enctype="multipart/form-data">
        Select PDF file to upload:
        <input type="file" name="fileToUpload" id="fileToUpload">
        <input type="submit" value="Upload File" name="submit">
    </form><br>
    
    <!-- affiche un message de validation si tout s'est bien déroulé -->
    <?php if (!empty($successMsg)): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($successMsg); ?>
        </div>
    <?php endif; ?>
    <!-- affiche un message d'erreur -->
    <?php if (!empty($errorMsg)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($errorMsg); ?>
        </div>
    <?php endif; ?>

    <!-- bouton de redirection vers le dashboard du staff -->
    <button onclick="window.location.href='staffDashboard.php';">Retour sur l'écran général du Staff </button>
</body>
</html>
