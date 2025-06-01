<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "recruitprou";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM recruiters WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $name, $hashed_password);
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION['recruiter_id'] = $id;
            $_SESSION['recruiter_name'] = $name;
            header("Location: projadd.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "No recruiter found with this email!";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>

    <title>Recruiter Login</title> 
       <link rel="stylesheet" href="recruiter.css">
    <style>
        
    </style>
</head>
<body>
    <div class="container2">

    
    <h2>Recruiter Login</h2>
    <?php
    if (isset($_GET['registered'])) echo "<p style='color:green;'>Registration successful! Please login.</p>";
    if (isset($error)) echo "<p style='color:red;'>$error</p>";
    ?>
    <form method="POST" id="loginForm">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="recruiter_register.php">Register here</a></p>
    </div>
</body>
</html>