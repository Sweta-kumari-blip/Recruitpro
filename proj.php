<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "recruitprou";

session_start();
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Handle job application form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['job_id'], $_POST['applicant_name'], $_POST['applicant_email'])) {
    $job_id = intval($_POST['job_id']);
    $name = trim($_POST['applicant_name']);
    $email = trim($_POST['applicant_email']);

    // Fetch job title for 'applied_for'
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $job_title = '';
    $job_title_sql = "SELECT title FROM job_listings WHERE id = $job_id";
    $job_title_result = $conn->query($job_title_sql);
    if ($job_title_result && $job_title_result->num_rows > 0) {
        $row = $job_title_result->fetch_assoc();
        $job_title = $row['title'];
    }
    // Handle resume upload
    $resume_path = NULL;
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['resume']['tmp_name'];
        $fileName = basename($_FILES['resume']['name']);
        $fileSize = $_FILES['resume']['size'];
        $fileType = $_FILES['resume']['type'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['pdf'];
        if (in_array($fileExt, $allowed) && $fileSize <= 5*1024*1024) { // 5MB max
            $uploadDir = 'resumes/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $newFileName = uniqid('resume_', true) . '.' . $fileExt;
            $destPath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $resume_path = $destPath;
            }
        }
    }
    // Insert application into the correct table
    $stmt = $conn->prepare("INSERT INTO job_applications (job_id, applied_for, applicant_name, applicant_email, resume_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $job_id, $job_title, $name, $email, $resume_path);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Application submitted successfully!';
        header('Location: proj.php');
        exit();
    } else {
        echo "<script>alert('Failed to submit application.'); window.location.href='proj.php';</script>";
        exit();
    }
    $stmt->close();
    $conn->close();
}

// Fetch jobs and their applicants
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$jobs = [];
$job_sql = "SELECT * FROM job_listings ORDER BY id DESC";
$job_result = $conn->query($job_sql);
if ($job_result && $job_result->num_rows > 0) {
    while ($job = $job_result->fetch_assoc()) {
        $job_id = $job['id'];
        $applicants = [];
        $app_sql = "SELECT applicant_name, applicant_email, resume_path FROM job_applications WHERE job_id = $job_id";
        $app_result = $conn->query($app_sql);
        if ($app_result && $app_result->num_rows > 0) {
            while ($app = $app_result->fetch_assoc()) {
                $applicants[] = $app;
            }
        }
        $job['applicants'] = $applicants;
        $jobs[] = $job;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RECRUITPRO - Recruitment Platform</title>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Poppins:wght@600&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="proj.css">
 <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Open Sans', sans-serif; }
    body { background-color: #f4f4f4; color: #1a1a2e; line-height: 1.6; }
    
    .job-section {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); /* Smaller cards */
      gap: 16px;
      padding: 1.2rem;
    }

    .job-card {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(15,76,117,0.08);
      padding: 1.1rem 1rem 1rem 1rem;
      margin: 0;
      min-width: 0;
      max-width: 270px;
      width: 100%;
      min-height: 320px;
      display: flex;
      flex-direction: column;
      align-items: stretch;
      justify-content: flex-start;
    }

    .job-title {
      font-size: 1.1rem;
      font-weight: bold;
      color: #0f4c75;
      margin-bottom: 7px;
      max-width: 100%;
      min-height: 32px;
      max-height: 32px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .job-description {
      font-size: 0.97rem;
      color: #333;
      margin-bottom: 7px;
      min-height: 38px;
      max-height: 38px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .job-skills, .job-experience, .job-location {
      font-size: 0.93rem;
      color: #333;
      margin-bottom: 7px;
      min-height: 24px;
      max-height: 24px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .apply-btn {
      background: #0f4c75;
      color: #fff;
      border: none;
      padding: 0.7rem 0;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
      font-size: 1rem;
      margin-top: 0.5rem;
      box-shadow: 0 2px 8px rgba(15,76,117,0.08);
      transition: background 0.2s;
      width: 100%;
    }

    .apply-btn:hover {
      background: #3282b8;
    }

    @media (max-width: 500px) {
      .modal-content {
        max-width: 98vw;
        padding: 1rem 0.5rem;
        font-size: 1rem;
      }
    }

    .modal {
      overflow-y: auto;
    }

    .logo-area {
      position: absolute;
      top: 20px;
      left: 30px;
      z-index: 20;
      display: flex;
      align-items: center;
    }
    .logo-area img {
      height: 48px;
      width: 48px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(15,76,117,0.08);
      margin-right: 10px;
    }
    .success-message {
      background: #e6f9ed;
      color: #1a7f37;
      border: 1px solid #b6e2c6;
      padding: 1rem 2rem;
      border-radius: 6px;
      margin: 1.5rem auto 0 auto;
      max-width: 600px;
      text-align: center;
      font-weight: bold;
      font-size: 1.1rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .apply-form {
      background: #fff;
      border-radius: 10px;
      box-shadow: none;
      padding: 1rem 0 0 0;
      margin-top: 0.5rem;
      margin-bottom: 0.5rem;
      display: flex;
      flex-direction: column;
      gap: 0.7rem;
      max-width: 100%;
      width: 100%;
      align-items: stretch;
    }
    .apply-form label {
      font-weight: 600;
      color: #0f4c75;
      margin-bottom: 0.2rem;
      font-size: 0.97rem;
    }
    .apply-form input[type="text"],
    .apply-form input[type="email"] {
      padding: 0.6rem 0.7rem;
      border: 1.2px solid #b6e2c6;
      border-radius: 5px;
      font-size: 0.97rem;
      background: #f7fafc;
      transition: border 0.2s;
      outline: none;
    }
    .apply-form input[type="text"]:focus,
    .apply-form input[type="email"]:focus {
      border: 1.2px solid #0f4c75;
      background: #fff;
    }
    .custom-file-label {
      background: #fff;
      color: #0f4c75;
      border: 1.2px solid #0f4c75;
      padding: 0.6rem 1rem;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 600;
      font-size: 0.97rem;
      margin-bottom: 0.1rem;
      display: inline-block;
      transition: background 0.2s, color 0.2s, border 0.2s;
    }
    .custom-file-label:hover {
      background: #0f4c75;
      color: #fff;
    }
    .file-selected {
      color: #1a1a2e;
      font-size: 0.93rem;
      margin-left: 0.5rem;
      font-style: italic;
    }
  </style>
  
</head>
<body>
<div class="logo-area">
  <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="RecruitPro Logo">
  <span style="font-family: 'Poppins', sans-serif; font-size: 1.5rem; color: #0f4c75; font-weight: bold;">RECRUITPRO</span>
</div>
<?php if (!empty($success_message)): ?>
  <div class="success-message" id="successMsg"><?= htmlspecialchars($success_message) ?></div>
  <script>
    setTimeout(function() {
      var msg = document.getElementById('successMsg');
      if (msg) msg.style.display = 'none';
    }, 10000);
  </script>
<?php endif; ?>
<header>
  <div class="bg-slider">
    <div class="slide slide1"></div>
    <div class="slide slide2"></div>
    <div class="slide slide3"></div>
    <div class="slide slide4"></div>
  </div>
  <div class="top-buttons" id="topButtons">
    <a href="#" id="loginBtn" onclick="toggleLoginModal()">LOG IN</a>
    <a href="#" id="registerBtn" onclick="toggleRegisterModal()">REGISTER</a>
  </div>
  <div>
    <h1>WELCOME TO RECRUITPRO</h1>
    <p>Connecting talented professionals with the right opportunities</p>
    <a href="#jobs" class="explore-btn">Explore Jobs</a>
  </div>
  <a href="#jobs" class="dream-job-btn">Find your dream job now</a>
  <div class="search-section">
    <input type="text" id="skills" placeholder="Enter skills / designations / companies" />
    <select id="experience">
        <option value="">Select experience</option>
        <option value="fresher">Fresher</option>
        <option value="1-3 years">1-3 years</option>
        <option value="3-5 years">3-5 years</option>
        <option value="5+ years">5+ years</option>
    </select>
    <select id="location">
        <option value="">Select location</option>
        <option value="Andhra Pradesh">Andhra Pradesh</option>
<option value="Arunachal Pradesh">Arunachal Pradesh</option>
<option value="Assam">Assam</option>
<option value="Bihar">Bihar</option>
<option value="Chhattisgarh">Chhattisgarh</option>
<option value="Goa">Goa</option>
<option value="Gujarat">Gujarat</option>
<option value="Haryana">Haryana</option>
<option value="Himachal Pradesh">Himachal Pradesh</option>
<option value="Jharkhand">Jharkhand</option>
<option value="Karnataka">Karnataka</option>
<option value="Kerala">Kerala</option>
<option value="Madhya Pradesh">Madhya Pradesh</option>
<option value="Maharashtra">Maharashtra</option>
<option value="Manipur">Manipur</option>
<option value="Meghalaya">Meghalaya</option>
<option value="Mizoram">Mizoram</option>
<option value="Nagaland">Nagaland</option>
<option value="Odisha">Odisha</option>
<option value="Punjab">Punjab</option>
<option value="Rajasthan">Rajasthan</option>
<option value="Sikkim">Sikkim</option>
<option value="Tamil Nadu">Tamil Nadu</option>
<option value="Telangana">Telangana</option>
<option value="Tripura">Tripura</option>
<option value="Uttar Pradesh">Uttar Pradesh</option>
<option value="Uttarakhand">Uttarakhand</option>
<option value="West Bengal">West Bengal</option>

    </select>
    <button type="button" onclick="performSearch()">Search</button>
  </div>
</header>
<main>
  <div class="job-section">
    <?php foreach ($jobs as $job): ?>
      <div class="job-card">
        <h2 class='job-title'><?= htmlspecialchars($job['title']) ?></h2>
        <p><strong>Description:</strong> <?= htmlspecialchars($job['description']) ?></p>
        <p><strong>Skills:</strong> <?= htmlspecialchars($job['skills']) ?></p>
        <p><strong>Experience:</strong> <?= htmlspecialchars($job['experience']) ?></p>
        <p><strong>Location:</strong> <?= htmlspecialchars($job['location']) ?></p>
        <?php
          // Check if the current user (by email) has already applied for this job
          $current_user_email = null;
          if (isset($_COOKIE['current_user_email'])) {
            $current_user_email = strtolower(trim($_COOKIE['current_user_email']));
          }
          $already_applied = false;
          if ($current_user_email && !empty($job['applicants'])) {
            foreach ($job['applicants'] as $app) {
              if (strtolower(trim($app['applicant_email'])) === $current_user_email) {
                $already_applied = true;
                break;
              }
            }
          }
        ?>
        <?php if ($already_applied): ?>
          <div class="applied-badge" style="background:#28a745;color:white;padding:8px 16px;border-radius:5px;font-weight:bold;margin-top:10px;">Applied</div>
        <?php else: ?>
          <form action='proj.php' method='POST' class='apply-form' enctype='multipart/form-data' onsubmit="document.cookie='current_user_email='+encodeURIComponent(this.applicant_email.value.toLowerCase())+';path=/';">
              <label for='applicant_name_<?= $job['id'] ?>'>Full Name</label>
              <input type='hidden' name='job_id' value='<?= $job['id'] ?>' />
              <input type='text' id='applicant_name_<?= $job['id'] ?>' name='applicant_name' class='your' placeholder='Your Name' required class='input-field' />
              <label for='applicant_email_<?= $job['id'] ?>'>Email Address</label>
              <input type='email' id='applicant_email_<?= $job['id'] ?>' name='applicant_email' placeholder='Your Email' required class='input-field' />
              <label for='resume_<?= $job['id'] ?>'>Upload Resume (PDF):</label>
              <input type='file' id='resume_<?= $job['id'] ?>' name='resume' accept='application/pdf' required class='input-field' style='display:none;'>
              <label for='resume_<?= $job['id'] ?>' class='custom-file-label'>Upload Resume</label>
              <span class='file-selected' id='file-selected-<?= $job['id'] ?>'></span>
              <button type='submit' class='apply-btn'>Apply</button>
          </form>
        <?php endif; ?>
        <?php if (!empty($job['applicants'])): ?>
          <div class="applied-by">
            <strong>Applied by:</strong>
            <ul>
              <?php foreach ($job['applicants'] as $app): ?>
                <li>
                  <?= htmlspecialchars($app['applicant_name']) ?> (<?= htmlspecialchars($app['applicant_email']) ?>)
                  <?php if (!empty($app['resume_path'])): ?>
                    - <a href="<?= htmlspecialchars($app['resume_path']) ?>" target="_blank">View Resume</a>
                  <?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</main>
<section class="section" id="jobs">
 
  <div class="slider" id="jobResults"></div>
</section>
<footer>
  <div class="footer-content">
    <div>
      <h3>RECRUITPRO</h3>
      <p>Your trusted recruitment partner for career growth and hiring solutions.</p>
    </div>
    <div>
      <h4>Job Seekers</h4>
      <ul>
        <li><a href="proj.php">Search Jobs</a></li>
      </ul>
    </div>
    <div>
      <h4>Employers</h4>
      <ul>
        <li><a href="recruiter_register.php">Post a Job</a></li>
      </ul>
    </div>
    <div>
      <h4>About</h4>
      <ul>
        <li><a href="about.php">About US</a></li>
        <li><a href="about.php">Privacy Policy</a></li>
        <li><a href="about.php">Terms & Conditions</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">&copy; 2025 RECRUITPRO. All rights reserved.</div>
</footer>

<!-- Login Modal -->
<div id="loginModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="toggleLoginModal();resetForgotPasswordForm();">&times;</span>
    <h2 style="text-align:center; margin-bottom: 1.5rem;">Login to RECRUITPRO</h2>
    <form id="loginForm" onsubmit="handleLogin(event)">
      <div class="form-group">
        <label for="loginEmail">Email</label>
        <input type="email" id="loginEmail" required>
      </div>
      <div class="form-group">
        <label for="loginPassword">Password</label>
        <input type="password" id="loginPassword" required>
      </div>
      <button type="submit" class="login-btn">Login</button>
    </form>
    <div style="text-align:center; margin-top:1rem;">
      <span>Don't have an account? <a href="#" onclick="toggleLoginModal();toggleRegisterModal();return false;">Register</a></span>
    </div>
  </div>
</div>

<!-- Register Modal -->
<div id="registerModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="toggleRegisterModal()">&times;</span>
    <h2 style="text-align:center; margin-bottom: 1.5rem;">Register for RECRUITPRO</h2>
    <form id="registerForm" onsubmit="handleRegister(event)">
      <div class="form-group">
        <label for="registerName">Full Name</label>
        <input type="text" id="registerName" required>
      </div>
      <div class="form-group">
        <label for="registerEmail">Email</label>
        <input type="email" id="registerEmail" required>
      </div>
      <div class="form-group">
        <label for="registerPassword">Password</label>
        <input type="password" id="registerPassword" required>
      </div>
      <div class="form-group">
        <label for="registerConfirmPassword">Confirm Password</label>
        <input type="password" id="registerConfirmPassword" required>
      </div>
      <div id="registerError" class="form-error"></div>
      <button type="submit" class="register-btn">Register</button>
    </form>
    <div style="text-align:center; margin-top:1rem;">
      <span>Already have an account? <a href="#" onclick="toggleRegisterModal();toggleLoginModal();return false;">Login</a></span>
    </div>
  </div>
</div>

<script>
function performSearch() {
    const skills = document.getElementById('skills').value;
    const experience = document.getElementById('experience').value;
    const location = document.getElementById('location').value;

    // Create a query string for the GET request
    const queryString = `skills=${encodeURIComponent(skills)}&experience=${encodeURIComponent(experience)}&location=${encodeURIComponent(location)}`;

    // Make an AJAX request to the PHP script
    fetch(`search_jobs.php?${queryString}`)
        .then(response => response.json())
        .then(jobs => displayResults(jobs))
        .catch(error => console.error('Error fetching jobs:', error));
}

function getAppliedJobs() {
    // Returns an array of job IDs the user has applied for, keyed by their email
    let applied = JSON.parse(localStorage.getItem('appliedJobs') || '{}');
    return applied;
}

function setAppliedJob(jobId, email) {
    let applied = JSON.parse(localStorage.getItem('appliedJobs') || '{}');
    if (!applied[email]) applied[email] = [];
    if (!applied[email].includes(jobId)) applied[email].push(jobId);
    localStorage.setItem('appliedJobs', JSON.stringify(applied));
}

function displayResults(jobs) {
    const jobSection = document.querySelector('.job-section');
    jobSection.innerHTML = '';
    if (jobs.length === 0) {
        jobSection.innerHTML = '<p>No jobs found matching your criteria.</p>';
        return;
    }
    let currentUser = JSON.parse(localStorage.getItem('currentUser') || 'null');
    let userEmail = currentUser ? currentUser.email : null;
    let applied = getAppliedJobs();
    jobs.forEach(job => {
        const jobDiv = document.createElement('div');
        jobDiv.className = 'job-card';
        jobDiv.innerHTML = `
            <h2 class='job-title'>${job.title}</h2>
            <p><strong>Description:</strong> ${job.description}</p>
            <p><strong>Skills:</strong> ${job.skills}</p>
            <p><strong>Experience:</strong> ${job.experience}</p>
            <p><strong>Location:</strong> ${job.location}</p>
        `;
        // Check if user has applied for this job
        let alreadyApplied = false;
        if (userEmail && applied[userEmail] && applied[userEmail].includes(String(job.id))) {
            alreadyApplied = true;
        }
        if (alreadyApplied) {
            const appliedBadge = document.createElement('div');
            appliedBadge.className = 'applied-badge';
            appliedBadge.textContent = 'Applied';
            appliedBadge.style.background = '#28a745';
            appliedBadge.style.color = 'white';
            appliedBadge.style.padding = '8px 16px';
            appliedBadge.style.borderRadius = '5px';
            appliedBadge.style.fontWeight = 'bold';
            appliedBadge.style.marginTop = '10px';
            jobDiv.appendChild(appliedBadge);
        } else {
            const form = document.createElement('form');
            form.action = 'proj.php';
            form.method = 'POST';
            form.className = 'apply-form';
            form.enctype = 'multipart/form-data';
            form.innerHTML = `
                <label for='applicant_name_${job.id}'>Full Name</label>
                <input type='hidden' name='job_id' value='${job.id}' />
                <input type='text' id='applicant_name_${job.id}' name='applicant_name' class='your' placeholder='Your Name' required class='input-field' />
                <label for='applicant_email_${job.id}'>Email Address</label>
                <input type='email' id='applicant_email_${job.id}' name='applicant_email' placeholder='Your Email' required class='input-field' />
                <label for='resume_${job.id}'>Upload Resume (PDF):</label>
                <input type='file' id='resume_${job.id}' name='resume' accept='application/pdf' required class='input-field' style='display:none;'>
                <label for='resume_${job.id}' class='custom-file-label'>Upload Resume</label>
                <span class='file-selected' id='file-selected-${job.id}'></span>
                <button type='submit' class='apply-btn'>Apply</button>
        `;
            form.onsubmit = function(e) {
                // On submit, store the applied job for this user
                const emailInput = form.querySelector("input[name='applicant_email']");
                const jobId = String(job.id);
                if (emailInput && emailInput.value) {
                    setAppliedJob(jobId, emailInput.value.trim().toLowerCase());
                }
            setTimeout(() => performSearch(), 500); // Refresh jobs after applying
        };
            jobDiv.appendChild(form);
        }
        jobSection.appendChild(jobDiv);
    });
}

window.onload = function() {
    performSearch(); // Show all jobs on page load
    if (typeof updateUIForLoggedInUser === 'function') updateUIForLoggedInUser();
};

function toggleLoginModal() {
  const modal = document.getElementById('loginModal');
  modal.style.display = modal.style.display === 'block' ? 'none' : 'block';
  const form = document.getElementById('loginForm');
  if (form) form.reset();
  document.getElementById('loginEmail').focus();
}
function toggleRegisterModal() {
  const modal = document.getElementById('registerModal');
  modal.style.display = modal.style.display === 'block' ? 'none' : 'block';
  const form = document.getElementById('registerForm');
  if (form) form.reset();
  document.getElementById('registerError').textContent = '';
  document.getElementById('registerName').focus();
}

function handleRegister(event) {
  event.preventDefault();
  const name = document.getElementById('registerName').value.trim();
  const email = document.getElementById('registerEmail').value.trim().toLowerCase();
  const password = document.getElementById('registerPassword').value;
  const confirmPassword = document.getElementById('registerConfirmPassword').value;
  const errorDiv = document.getElementById('registerError');
  errorDiv.textContent = '';

  if (!name || !email || !password || !confirmPassword) {
    errorDiv.textContent = 'All fields are required.';
    return;
  }
  if (password.length < 6) {
    errorDiv.textContent = 'Password must be at least 6 characters.';
    return;
  }
  if (password !== confirmPassword) {
    errorDiv.textContent = 'Passwords do not match.';
    return;
  }
  let users = JSON.parse(localStorage.getItem('users') || '[]');
  if (users.find(u => u.email === email)) {
    errorDiv.textContent = 'Email is already registered.';
    return;
  }
  users.push({ name, email, password });
  localStorage.setItem('users', JSON.stringify(users));
  localStorage.setItem('currentUser', JSON.stringify({ name, email }));
  toggleRegisterModal();
  updateUIForLoggedInUser();
  alert('Registration successful! You are now logged in.');
}

function handleLogin(event) {
  event.preventDefault();
  const email = document.getElementById('loginEmail').value.trim().toLowerCase();
  const password = document.getElementById('loginPassword').value;
  let users = JSON.parse(localStorage.getItem('users') || '[]');
  const user = users.find(u => u.email === email && u.password === password);
  if (user) {
    localStorage.setItem('currentUser', JSON.stringify({ name: user.name, email: user.email }));
    toggleLoginModal();
    updateUIForLoggedInUser();
    alert('Login successful!');
  } else {
    alert('Invalid email or password!');
  }
}

function updateUIForLoggedInUser() {
  const topButtons = document.getElementById('topButtons');
  const currentUser = JSON.parse(localStorage.getItem('currentUser') || 'null');
  if (currentUser) {
    topButtons.innerHTML = `
      <span style="background:none;color:#fff;font-weight:bold;">Welcome, ${currentUser.name}</span>
      <button onclick="handleLogout()">LOGOUT</button>
    `;
  } else {
    topButtons.innerHTML = `
      <a href="#" id="loginBtn" onclick="toggleLoginModal()">LOG IN</a>
      <a href="#" id="registerBtn" onclick="toggleRegisterModal()">REGISTER</a>
    `;
  }
}
function handleLogout() {
  localStorage.removeItem('currentUser');
  updateUIForLoggedInUser();
}

document.querySelectorAll("input[type='file'][name='resume']").forEach(function(input) {
  input.addEventListener('change', function() {
    var id = this.id.replace('resume_', '');
    var fileName = this.files[0] ? this.files[0].name : '';
    var span = document.getElementById('file-selected-' + id);
    if (span) span.textContent = fileName;
  });
});
</script>

</body>
</html>

