<?php


include 'db.php'; // Assurez-vous que ce fichier inclut la connexion à votre base de données.
var_dump($_FILES);

$target_dir = __DIR__ . "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Vérifier si le fichier est bien un PDF (ou tout autre format souhaité)
if(isset($_POST["submit"])) {
    if($fileType != "pdf") {
        echo "Sorry, only PDF files are allowed.";
        $uploadOk = 0;
    }
}

// Vérifier si le fichier existe déjà
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}

// Vérifier la taille du fichier
if ($_FILES["fileToUpload"]["size"] > 5000000) { // Limite de 5MB
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}

// Essayer de télécharger le fichier si tout est ok
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>

<h2>Upload Student Certificate</h2>
    <form action="upload_certificate.php" method="POST" enctype="multipart/form-data">
        Select certificate to upload (PDF only):
        <input type="file" name="fileToUpload" id="fileToUpload" accept=".pdf">
        <input type="submit" value="Importer le Certificat" name="submit">
    </form>

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importer un certificat de scolarité</title>
    </head>
<body>
    <!-- Formulaire pour télécharger les certificats -->
    <h2>Upload Student Certificate</h2>
    <form action="upload_certificate.php" method="post" enctype="multipart/form-data">
        Select certificate to upload (PDF only):
        <input type="file" name="fileToUpload" id="fileToUpload" accept=".pdf">
        <input type="submit" value="Upload Certificate" name="submit">
    </form>
</body>
</html>