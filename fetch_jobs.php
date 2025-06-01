<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "recruitprou";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch job listings
$sql = "SELECT * FROM job_listings ORDER BY id DESC";
$result = $conn->query($sql);

$jobs = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row; // Add each job to the array
    }
}

// Return the job listings as JSON
header('Content-Type: application/json');
echo json_encode($jobs);

// Close the connection
$conn->close();
?>
