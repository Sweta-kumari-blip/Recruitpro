<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "recruitprou";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$skills = isset($_GET['skills']) ? $conn->real_escape_string($_GET['skills']) : '';
$experience = isset($_GET['experience']) ? $conn->real_escape_string($_GET['experience']) : '';
$location = isset($_GET['location']) ? $conn->real_escape_string($_GET['location']) : '';

$sql = "SELECT * FROM job_listings WHERE 1=1";
if ($skills !== '') {
    $sql .= " AND (title LIKE '%$skills%' OR description LIKE '%$skills%' OR skills LIKE '%$skills%')";
}
if ($experience !== '') {
    $sql .= " AND experience = '$experience'";
}
if ($location !== '') {
    $sql .= " AND location = '$location'";
}
$sql .= " ORDER BY id DESC";

$result = $conn->query($sql);

$jobs = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
}
header('Content-Type: application/json');
echo json_encode($jobs);
$conn->close();
?>
