<?php
header('Content-Type: application/json');
include 'db_config.php';

try {
    // Prepare SQL statement with proper ORDER BY clause
    $sql = "SELECT * FROM jobs ORDER BY id DESC";
    $result = $conn->query($sql);

    if ($result === false) {
        throw new Exception($conn->error);
    }

    $jobs = [];
    while ($row = $result->fetch_assoc()) {
        $jobs[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'company' => $row['company'],
            'location' => $row['location'],
            'description' => $row['description'],
            'requirements' => $row['requirements'],
            'salary_range' => $row['salary_range'],
            'job_type' => $row['job_type'],
            'experience_level' => $row['experience_level']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $jobs
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching jobs.',
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?> 