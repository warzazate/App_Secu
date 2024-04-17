<?php

include 'db.php'; // Assurez-vous que ce fichier inclut la connexion à votre base de données.

$target_dir = "uploads/";
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
