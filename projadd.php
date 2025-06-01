<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Job - RECRUITPRO</title>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Poppins:wght@600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="proj.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Open Sans', sans-serif; }
    body { background-color: #f4f4f4; color: #1a1a2e; line-height: 1.6; }

    header {
      background-color: #0f4c75;
      color: white;
      padding: 2rem 0;
      text-align: center;
    }

    header h1 {
      font-size: 9em;
      margin-bottom: 1rem;
    }

    main {
      max-width: 600px;
      margin: 2rem auto;
      padding: 2rem;
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      color: #1a1a2e;
      font-weight: bold;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
      width: 100%;
      padding: 0.8rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1rem;
    }

    .form-group textarea {
      resize: vertical;
    }

    .add-job-btn {
      background-color: #ff6f61;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
      font-size: 1rem;
      transition: background 0.3s ease;
      width: 100%;
    }

    .add-job-btn:hover {
      background: #ff8a75;
    }

    .form-error {
      color: #dc3545;
      font-size: 0.9rem;
      margin-top: 10px;
    }

    footer {
      background-color: #2c3e50;
      color: white;
      padding: 2rem 0;
      text-align: center;
    }

    footer .footer-bottom {
      margin-top: 1rem;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
<header>
  <h1>Add NEW JOBS</h1>
</header>
<main>
  <form id="addJobForm" method="POST" action="projadd.php">
    <div class="form-group">
      <label for="jobTitle">Job Title</label>
      <input type="text" id="jobTitle" name="jobTitle" required>
    </div>
    <div class="form-group">
      <label for="jobDescription">Job Description</label>
      <textarea id="jobDescription" name="jobDescription" required></textarea>
    </div>
    <div class="form-group">
      <label for="jobSkills">Skills (comma separated)</label>
      <input type="text" id="jobSkills" name="jobSkills" required>
    </div>
    <div class="form-group">
      <label for="jobExperience">Experience</label>
      <select id="jobExperience" name="jobExperience" required>
        <option value="">Select experience</option>
        <option value="fresher">Fresher</option>
        <option value="1-3 years">1-3 years</option>
        <option value="3-5 years">3-5 years</option>
        <option value="5+ years">5+ years</option>
      </select>
    </div>
    <div class="form-group">
      <label for="jobLocation">Location</label>
      <input type="text" id="jobLocation" name="jobLocation" required>
    </div>
    <button type="submit" class="add-job-btn">Add Job</button>
    <div id="addJobError" class="form-error"></div>
  </form>

  <?php
  // Database connection
  $servername = "localhost"; // Change if your server is different
  $username = "root"; // Your MySQL username
  $password = ""; // Your MySQL password
  $dbname = "recruitprou"; // Your database name

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);

  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  // Handle form submission
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $title = $conn->real_escape_string($_POST['jobTitle']);
      $description = $conn->real_escape_string($_POST['jobDescription']);
      $skills = $conn->real_escape_string($_POST['jobSkills']);
      $experience = $conn->real_escape_string($_POST['jobExperience']);
      $location = $conn->real_escape_string($_POST['jobLocation']);

      $sql = "INSERT INTO job_listings (title, description, skills, experience, location) VALUES ('$title', '$description', '$skills', '$experience', '$location')";

      if ($conn->query($sql) === TRUE) {
          echo "<script>alert('Job added successfully!');</script>";
          echo "<script>document.getElementById('addJobForm').reset();</script>";
      } else {
          echo "<div class='form-error'>Error: " . $sql . "<br>" . $conn->error . "</div>";
      }
  }

  // Close connection
  $conn->close();
  ?>
</main>
<footer>
  <div class="footer-bottom">&copy; 2025 RECRUITPRO. All rights reserved.</div>
</footer>
</body>
</html>