<?php
// Assurez-vous que ce fichier inclut la connexion à votre base de données.
include 'db.php'; 

if(!empty($_FILES)){
    $file_name = $_FILES['certificat']['name'];
    $file_extension = strrchr($file_name, ".");

    $file_tmp_name = $_FILES['certificat']['tmp_name'];
    $file_dest = 'uploads/'.$file_name;

    $extensions_autorisees = array('.jpg', '.jpeg', '.png');


    if(in_array($file_extension, $extensions_autorisees)){
            if(move_uploaded_file($file_tmp_name, $file_dest)){
                echo 'Nom du fichier importé :' .$file_name. '<br/>';
            } else {
                echo 'Une erreur est survenue';
            }
    }else {
        echo '<br/>'; 
        echo 'Seuls les fichiers .jpg .jpeg .png sont autorisés';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Importer un certificat de scolarité</title>
    </head>
<body>
    <!-- Formulaire pour télécharger les certificats -->
    <h2>Upload Student Certificate</h2>
    <form method="POST" enctype="multipart/form-data">
        Selectionner le certificat à importer (Seuls les fichiers .jpg .jpeg .png sont autorisés):<br/>
        <input type="file" name="certificat"><br/>
        <input type="submit" name="submit">
    </form>
</body>
</html>