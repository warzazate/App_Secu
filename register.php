<?php
header("X-Frame-Options: DENY");
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

include 'db.php';  // Make sure this file contains the connection to your database

$errorMsg = '';
$successMsg = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user'; // Default role is 'user'

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
                // Redirect to the login page or dashboard
                //header("Location: login.php");
                //exit;
            } else {
                $errorMsg = "Error registering user.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form method="post" action="register.php">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        <label for="role">Role:</label>
        <select id="role" name="role">
            <option value="teachers">Teachers</option>
            <!--ne pas mettre l'option ADMIN à la fin je pense-->
            <option value="staff">Staff</option>
            <option value="superStaff">Super Staff</option>
            <option value="admin">Admin</option>
        </select><br>
        <button type="submit">Register</button>
    </form>

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
</body>
</html>
