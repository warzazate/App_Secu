<?php
session_start();

// Check if the user is logged in and has the appropriate role
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] != 'staff' && $_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superstaff') {
    header("Location: index.php");
    exit;
}

// Include database configuration
include 'db.php';

$action = $_GET['action'] ?? 'list'; // Default action to list if none specified
$id = $_GET['id'] ?? 0; // For edit and delete actions

// Handle POST request for create, edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($action == 'create' || $action == 'edit')) {
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $dateOfBirth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $address = $_POST['address'] ?? '';
    $phoneNumber = $_POST['phone_number'] ?? '';
    $teacherId = $_POST['teacher_id'] ?? '';

    if ($action == 'create') {
        $stmt = $con->prepare("INSERT INTO students (first_name, last_name, email, date_of_birth, gender, address, phone_number, teacher_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$firstName, $lastName, $email, $dateOfBirth, $gender, $address, $phoneNumber, $teacherId]);
        $successMsg = "Student created successfully.";
    } elseif ($id) {
        $stmt = $con->prepare("UPDATE students SET first_name = ?, last_name = ?, email = ?, date_of_birth = ?, gender = ?, address = ?, phone_number = ?, teacher_id = ? WHERE id = ?");
        $stmt->execute([$firstName, $lastName, $email, $dateOfBirth, $gender, $address, $phoneNumber, $teacherId, $id]);
        $successMsg = "Student updated successfully.";
    }
    $action = 'list';
}

// Handle GET request for delete
if ($action == 'delete' && isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $con->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $successMsg = "Student deleted successfully.";
        $action = 'list';
    }



// Fetch all students for listing
$stmt = $con->prepare("SELECT * FROM students");
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Manage Students (for staff members)</h1>

    <?php if ($action == 'list'): ?>
        <button onclick="window.location.href='?action=create';">Add New Student</button>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Date of birth</th>
                    <th>Gender</th>
                    <th>Address</th>
                    <th>Phone number</th>
                    <th>Teacher ID (normalement le nom !)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= htmlspecialchars($student['id']) ?></td>
                    <td><?= htmlspecialchars($student['first_name']) ?></td>
                    <td><?= htmlspecialchars($student['last_name']) ?></td>
                    <td><?= htmlspecialchars($student['email']) ?></td>
                    <td><?= htmlspecialchars($student['date_of_birth']) ?></td>
                    <td><?= htmlspecialchars($student['gender']) ?></td>
                    <td><?= htmlspecialchars($student['address']) ?></td>
                    <td><?= htmlspecialchars($student['phone_number']) ?></td>
                    <td><?= htmlspecialchars($student['teacher_id']) ?></td>
                    <td>
                        <button onclick="window.location.href='?action=edit&id=<?= htmlspecialchars($student['id']) ?>';">Edit</button>
                        <button onclick="if(confirm('Are you sure you want to delete this student?')) { window.location.href='?action=delete&id=<?= htmlspecialchars($student['id']) ?>'; } return false;">Delete</button>
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
            $stmt = $con->prepare("SELECT * FROM students WHERE id = ?;");
            $stmt->execute([$id]);
            $studentChoosed = $stmt->fetch();
            
            if (!$studentChoosed) {
                die('Student not found!');
            }
        // Laisse passer si c'est pour créé un student
        } elseif ($action == 'create') {
            echo "";
        } else {
            die('Invalid request!');
        }
    ?>
        <!-- Form for creating or editing the student chosen -->
        <form method="post" action="?action=<?= htmlspecialchars($action) ?>&id=<?= htmlspecialchars($id) ?>">
            <!-- Input fields for the form here... -->
            <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?php if($action == 'edit'){echo htmlspecialchars($studentChoosed['first_name']);}?>" required><br>
            <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" value="<?php if($action == 'edit'){echo htmlspecialchars($studentChoosed['last_name']);} ?>" required><br>
            <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php if($action == 'edit'){echo htmlspecialchars($studentChoosed['email']);} ?>" required><br>
            <label for="date_of_birth">Date of Birth:</label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="<?php if($action == 'edit'){echo htmlspecialchars($studentChoosed['date_of_birth']);} ?>" required><br>
            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <option value="male" <?php if($action == 'edit'){echo ($studentChoosed['gender'] == 'male') ? 'selected' : '';} ?>>Male</option>
                <option value="female" <?php if($action == 'edit'){echo ($studentChoosed['gender'] == 'female') ? 'selected' : '';} ?>>Female</option>
                <option value="other" <?php if($action == 'edit'){echo ($studentChoosed['gender'] == 'other') ? 'selected' : '';} ?>>Other</option>
            </select><br>
            <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?php if($action == 'edit'){echo htmlspecialchars($studentChoosed['address']);} ?>" required><br>
            <label for="phone_number">Phone Number:</label>
                <input type="text" id="phone_number" name="phone_number" value="<?php if($action == 'edit'){echo htmlspecialchars($studentChoosed['phone_number']);} ?>" required><br>
            <label for="teacher_id">Teacher id:</label>
                <input type="number" id="teacher_id" name="teacher_id" value="<?php if($action == 'edit'){echo htmlspecialchars($studentChoosed['teacher_id']);} ?>" required><br>
            <button type="submit"><?= $action == 'create' ? 'Create Student' : 'Update Student' ?></button>
        </form>
    <?php endif; ?>


    <?php if (!empty($successMsg)): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($successMsg); ?>
        </div>
    <?php endif; ?>

    <button onclick="window.location.href='staffDashboard.php';">Retour sur l'écran général du Staff </button>
</body>
</html>
