<?php
session_start();

// Check if the user is logged in and has the appropriate role
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || ($_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superstaff')) {
    header("Location: index.php");
    exit;
}

// Include database configuration
include 'db.php';

$action = $_GET['action'] ?? 'list'; // Default action to list if none specified
$user_id = $_GET['user_id'] ?? 0; // For edit and delete actions using user_id

// Handle POST request for edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($action == 'edit')) {
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $department = $_POST['department'] ?? '';
    $hireDate = $_POST['hire_date'] ?? '';

    $stmt = $con->prepare("UPDATE teachers SET first_name = ?, last_name = ?, department = ?, hire_date = ? WHERE user_id = ?");
    $stmt->execute([$firstName, $lastName, $department, $hireDate, $user_id]);
    $successMsg = "Teacher updated successfully.";
    $action = 'list';
}

//TO CREATE !!! (need to create a user and a teacher !!!)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($action == 'create')) {
    // Collecter les données du formulaire
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $department = $_POST['department'] ?? '';
    $hireDate = $_POST['hire_date'] ?? '';

    try {
        $con->beginTransaction();

        // Insertion dans la table users
        $stmt = $con->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'teacher')");
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);
        $stmt->execute([$email, $passwordHash]);

        $user_id = $con->lastInsertId();

        // Insertion dans la table teachers
        $stmt = $con->prepare("INSERT INTO teachers (user_id, first_name, last_name, department, hire_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $firstName, $lastName, $department, $hireDate]);

        $con->commit();
        $successMsg = "Teacher and user profile created successfully.";
    } catch (Exception $e) {
        $con->rollBack();
        $errorMsg = "Failed to create teacher profile: ";
    }
    $action = 'list';
}


// Handle GET request for delete
if ($action == 'delete' && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $stmt = $con->prepare("DELETE FROM teachers WHERE user_id = ?");
    $stmt->execute([$user_id]);

    try {
        // Commencez une transaction
        $con->beginTransaction();

        // Supprimez d'abord l'entrée dans la table teachers
        $stmt = $con->prepare("DELETE FROM teachers WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Ensuite, supprimez l'entrée correspondante dans la table users
        $stmt = $con->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        // Validez la transaction
        $con->commit();
        $successMsg = "Teacher and user profile deleted successfully.";
    } catch (Exception $e) {
        // En cas d'erreur, annulez la transaction
        $con->rollBack();
        $errorMsg = "Failed to delete teacher profile";
    }
    $action = 'list';
}

// Fetch all teachers for listing
$stmt = $con->prepare("SELECT * FROM teachers");
$stmt->execute();
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Manage Teachers</h1>

    <?php if ($action == 'list'): ?>
        <button onclick="window.location.href='?action=create';">Add New Teacher</button>
        <table border="1">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Department</th>
                    <th>Hire Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teachers as $teacher): ?>
                <tr>
                    <td><?= htmlspecialchars($teacher['user_id']) ?></td>
                    <td><?= htmlspecialchars($teacher['first_name']) ?></td>
                    <td><?= htmlspecialchars($teacher['last_name']) ?></td>
                    <td><?= htmlspecialchars($teacher['department']) ?></td>
                    <td><?= htmlspecialchars($teacher['hire_date']) ?></td>
                    <td>
                        <button onclick="window.location.href='?action=edit&user_id=<?= htmlspecialchars($teacher['user_id']) ?>';">Edit</button>
                        <button onclick="if(confirm('Are you sure you want to delete this teacher?')) { window.location.href='?action=delete&user_id=<?= htmlspecialchars($teacher['user_id']) ?>'; } return false;">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <!-- TO CREATE Teacher !! -->
    <?php if ($action == 'create'):
        ?>
        <form method="post">
            <label for="email">Email:</label>
                <input type="email" id="email" name="email" required><br>
            <label for="password">Password:</label>
                <input type="password" id="password" name="password" required><br>
            <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required><br>
            <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required><br>
            <label for="department">Department:</label>
                <input type="text" id="department" name="department" required><br>
            <label for="hire_date">Hire Date:</label>
                <input type="date" id="hire_date" name="hire_date" required><br>
        <button type="submit">Submit</button>
    </form>
    <?php endif; ?>


    <!-- TO EDIT teacher !!!! -->
    <?php if ($action == 'edit'): 
        // Vérification de l'ID du teacher et récupération des données
        if (isset($_GET['user_id']) && $action == 'edit') {
            $id = $_GET['user_id'];
            $stmt = $con->prepare("SELECT * FROM teachers WHERE user_id = ?;");
            $stmt->execute([$id]);
            $teacherChoosed = $stmt->fetch();
            if (!$teacherChoosed) {
                die('Teacher not found!');
            }
        }else {
            die('Invalid request!');
        }
        ?>
        <form method="post" action="?action=<?= htmlspecialchars($action) ?>&user_id=<?= htmlspecialchars($user_id) ?>">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" value="<?= $action == 'edit' ? htmlspecialchars($teacherChoosed['first_name']) : '' ?>" required><br>
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" value="<?= $action == 'edit' ? htmlspecialchars($teacherChoosed['last_name']) : '' ?>" required><br>
            <label for="department">Department:</label>
            <input type="text" id="department" name="department" value="<?= $action == 'edit' ? htmlspecialchars($teacherChoosed['department']) : '' ?>" required><br>
            <label for="hire_date">Hire Date:</label>
            <input type="date" id="hire_date" name="hire_date" value="<?= $action == 'edit' ? htmlspecialchars($teacherChoosed['hire_date']) : '' ?>" required><br>
            <button type="submit"><?= $action == 'create' ? 'Create Teacher' : 'Update Teacher' ?></button>
        </form>
    <?php endif; ?>

    <?php if (!empty($successMsg)): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($successMsg); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($errorMsg)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($errorMsg); ?>
        </div>
    <?php endif; ?>

    <button onclick="window.location.href='staffDashboard.php';">Retour sur l'écran général du Staff </button>
</body>
</html>
