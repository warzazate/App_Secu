<?php
session_start();

// Check if the user is logged in and has the superstaff role
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] != 'superstaff' && $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Include database configuration
include 'db.php';

$action = $_GET['action'] ?? 'list'; // Default action to list if none specified
$id = $_GET['id'] ?? 0; // For edit and delete actions

// Handle POST request for create, edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($action == 'create' || $action == 'edit')) {
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    if ($action == 'create') {
        // Basic validation
        if (empty($email) || empty($password)) {
            $errorMsg = "Both fields are required.";
        } else {
            // Check if email already exists
            $stmt = $con->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                $errorMsg = "Email already used!";
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);

                // Insert new user into the database
                $stmt = $con->prepare("INSERT INTO users (email, password, role) VALUES (:email, :password, :role)");
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':role', $role);

                if ($stmt->execute()) {
                    $successMsg = "User registered successfully.";
                } else {
                    $errorMsg = "Error registering user.";
                }
            }
        }
    } elseif ($id) {
        $stmt = $con->prepare("UPDATE users SET email = ?, role = ? WHERE id = ?");
        $stmt->execute([$email, $role, $id]);
        $successMsg = "Staff member updated successfully.";
    }
    $action = 'list';
}

// Handle GET request for delete
if ($action == 'delete' && $id) {
    $stmt = $con->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $successMsg = "Staff member deleted successfully.";
    $action = 'list';
}

// Fetch all staff for listing (excluding superstaff and admin)
$stmt = $con->prepare("SELECT * FROM users WHERE role = 'staff'");
$stmt->execute();
$staffMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);


//TO REDIRECT to other dashboard if user have more privileges
$role = $_SESSION['role'] ?? 'none'; // Default to 'none' if not set
// Output the button based on the role
if($role == 'admin') {
    echo '<button onclick="window.location.href=\'adminDashboard.php\';" class="redirect">Go to Admin Dashboard</button>';
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff (Superstaff Dashboard)</title>
    <link rel="stylesheet" href="css\style.css">
</head>
<body>
    <h1>Manage Staff (for Superstaff)</h1>

    <?php if ($action == 'list'): ?>
        <button onclick="window.location.href='?action=create';">Add New Staff Member</button>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staffMembers as $member): ?>
                <tr>
                    <td><?= htmlspecialchars($member['id']) ?></td>
                    <td><?= htmlspecialchars($member['email']) ?></td>
                    <td><?= htmlspecialchars($member['role']) ?></td>
                    <td>
                        <button onclick="window.location.href='?action=edit&id=<?= htmlspecialchars($member['id']) ?>';">Edit</button>
                        <button onclick="if(confirm('Are you sure you want to delete this staff member?')) { window.location.href='?action=delete&id=<?= htmlspecialchars($member['id']) ?>'; } return false;">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($action == 'create' || $action == 'edit'): 
        // Vérification de l'ID de l'étudiant et récupération des données
        if (isset($_GET['id']) && $action == 'edit') {
            $id = $_GET['id'];
            $stmt = $con->prepare("SELECT * FROM users WHERE id = ? && role = 'staff';"); //ne prend que des STAFF !
            $stmt->execute([$id]);
            $staffMemberChoosed = $stmt->fetch();
            
            if (!$staffMemberChoosed) {
                die('Staff member not found!');
            }
        // Laisse passer si c'est pour créé un student
        } elseif ($action == 'create') {
            echo "";
        } else {
            die('Invalid request!');
        }
    ?>
        <form method="post" action="?action=<?= htmlspecialchars($action) ?>&id=<?= htmlspecialchars($id) ?>">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= $action == 'edit' ? htmlspecialchars($staffMemberChoosed['email']) : '' ?>" required><br>
            <label for="role">Role:</label>
            <select id="role" name="role">
                <option value="staff" <?= $action == 'edit' && $staffMemberChoosed['role'] == 'staff' ? 'selected' : '' ?>>Staff</option>
            </select><br>
            <?php if ($action == 'create'): ?>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            <?php endif; ?>
            <button type="submit"><?= $action == 'create' ? 'Create Staff Member' : 'Update Staff Member' ?></button>
        </form>
    <?php endif; ?>
    
    <div>
        <button onclick="window.location.href='staffDashboard.php';">Tableau de Bord Staff</button>
    </div>
    <div>
        <button onclick="window.location.href='login.php?action=logout';" class="logout_button">Déconnexion</button>
    </div>


    <?php if (!empty($successMsg)): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($successMsg); ?>
        </div>
    <?php endif; ?>
</body>
</html>
