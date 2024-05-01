<?php
session_start();

// Check if the teacher is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] != 'teacher' && $_SESSION['role'] != 'admin') {
    // Not logged in or not a teacher or admin, redirect to login page
    header("Location: index.php");
    exit;
}

include 'db.php'; // Include your database connection

// Assume teacher's ID is stored in session
$teacher_id = $_SESSION['user_id'];


//TO REDIRECT to other dashboard if user have more privileges
$role = $_SESSION['role'] ?? 'none'; // Default to 'none' if not set
// Output the button based on the role
switch ($role) {
    case 'staff':
        echo '<button onclick="window.location.href=\'staffDashboard.php\';" class="redirect">Go to Staff Dashboard</button>';
        break;
    case 'superstaff':
        echo '<button onclick="window.location.href=\'superstaffDashboard.php\';" class="redirect">Go to Superstaff Dashboard</button>';
        break;
    case 'admin':
        echo '<button onclick="window.location.href=\'adminDashboard.php\';" class="redirect">Go to Admin Dashboard</button>';
        break;
    default:
        break;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord du Professeur</title>
    <link rel="stylesheet" href="css\style.css">
</head>
<body>
    <h1>Liste des Étudiants</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Date of birth</th>
            <th>Gender</th>
            <th>Address</th>
            <th>Phone</th>
            <th>Actions</th>
        </tr>
        <?php
        // Fetch students from database
        $stmt = $con->prepare("SELECT * FROM students WHERE teacher_id = ?");
        $stmt->execute([$teacher_id]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['date_of_birth']) . "</td>";
            echo "<td>" . htmlspecialchars($row['gender']) . "</td>";
            echo "<td>" . htmlspecialchars($row['address']) . "</td>";
            echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
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
    
    <a href="login.php?action=logout" class="logout_button">Déconnexion</a>
</body>
</html>
