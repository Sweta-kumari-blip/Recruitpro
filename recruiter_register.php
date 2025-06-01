<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "recruitprou";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM recruiters WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $error = "Email already registered!";
    } else {
        $stmt = $conn->prepare("INSERT INTO recruiters (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);
        if ($stmt->execute()) {
            header("Location: recruiter_login.php?registered=1");
            exit();
        } else {
            $error = "Registration failed!";
        }
        $stmt->close();
    }
    $check->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Recruiter Registration</title>
   <link rel="stylesheet" href="recruiter.css">
</head>
<body>
    <div class="container2">
    <h2>Recruiter Registration</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit">Register</button>
    </form>
    <p>Already registered? <a href="recruiter_login.php">Login here</a></p>
    </div>
</body>
</html>