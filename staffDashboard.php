<?php include 'db.php'; 
include 'login.php';
    // Check if the user is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        // User is not logged in, redirect them to the login page
        header("Location: index.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Student Management System</title>
</head>
<body>
    <h1>List of Students</h1>
    <a href="createSTUDENT.php">Add New Student</a>
    <a href="login.php">Login Here</a>
    <!-- Register Here Button -->
    <a href="register.php" style="margin-left: 10px;">Register Here</a>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Age</th>
            <th>Actions</th>
        </tr>
        <?php
        // il fallait mettre $con et pas $pdo pour lancer une requête à la base de données !!!!!
        $stmt = $con->query('SELECT * FROM students');
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['age']) . "</td>";
            echo "<td><a href='editSTUDENT.php?id=" . $row['id'] . "'>Edit</a> <a href='deleteSTUDENT.php?id=" . $row['id'] . "'>Delete</a></td>";
            echo "</tr>";
        }
        ?>
    </table>
    <?php if (!empty($errorMsg)): ?>
    <div class="error-message">
        <?php echo htmlspecialchars($errorMsg); ?>
    </div>
    <?php endif; ?>


    <a href="logout.php" class="logout_button">Déconnexion</a>
</body>
</html>