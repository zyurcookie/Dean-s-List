<?php
session_start();
include('db.php');

// Restrict access to Admins only
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
    header('Location: landing.php');
    exit();
}

// Initialize variables for alerts
$successMessage = "";
$errorMessage = "";

// Handle account creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_account'])) {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Check if email already exists
    $sql = "SELECT * FROM user WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $errorMessage = "Email already exists.";
    } else {
        // Insert new user into the database
        $sql = "INSERT INTO user (email, password, role) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $email, $password, $role);

        if ($stmt->execute()) {
            $successMessage = "User created successfully!";
        } else {
            $errorMessage = "Error creating user.";
        }
    }
}

// Handle approve/decline actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['doc_id'])) {
    $action = $_POST['action'];
    $doc_id = $_POST['doc_id'];
    $comments = $_POST['comments'] ?? '';

    if ($action === 'approve' || $action === 'decline') {
        $status = $action === 'approve' ? 'Approved' : 'Declined';
        $sql = "UPDATE document SET status=?, comments=? WHERE doc_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $status, $comments, $doc_id);
        if ($stmt->execute()) {
            $successMessage = "Document $status successfully.";
        } else {
            $errorMessage = "Error updating document status.";
        }
    }
}
?>


<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Docu Dashboard</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
  />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap"
    rel="stylesheet"
  />
 <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <header>
    <div>Digi Docu</div>
    <div class="user">
      <i class="fas fa-user-circle"></i>
      <span>Dean</span>
    </div>
  </header>

  <div class="container">
    <aside>
      <div class="profile">
        <div class="avatar">DN</div>
        <span>Dean</span>
      </div>
      <div class="search-wrapper">
        <input type="text" placeholder="Search..." />
        <i class="fas fa-search"></i>
      </div>
      <nav>
        <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="#" class="active"><i class="fas fa-file-alt"></i> Student Files</a>
        <a href="tags.php"><i class="fas fa-users"></i> Users</a>
        <a href="#"><i class="fas fa-tags"></i> Tags</a>
        <div class="settings">
          <div><i class="fas fa-cog"></i> Settings</div>
          <i class="fas fa-chevron-down"></i>
        </div>
      </nav>
    </aside>

    <main>
      <section class="stats-grid">
        <div class="stat-card">
          <p>Total Student Files</p>
          <p>320</p>
        </div>
        <div class="stat-card">
          <p>Pending Verifications</p>
          <p class="stat-yellow">24</p>
        </div>
        <div class="stat-card">
          <p>Approved Files</p>
          <p class="stat-green">280</p>
        </div>
        <div class="stat-card">
          <p>Declined Files</p>
          <p class="stat-red">16</p>
        </div>
      </section>

      <section class="table-container">
        <h2>Student Files</h2>
        <table>
          <thead>
            <tr>
              <th>File ID</th>
              <th>Student Name</th>
              <th>File Type</th>
              <th>Date Submitted</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <tr onclick="window.location.href='document-detail.php?doc_id=001'">
              <td>001</td>
              <td>John Doe</td>
              <td>Student ID</td>
              <td>2024-04-25</td>
              <td class="status-pending">Pending</td>
              <td><button class="action-button" onclick="event.stopPropagation(); window.location.href='document-detail.php?doc_id=001'">Review</button></td>
            </tr>
            
            <tr onclick="alert('Reviewing Mary Johnson Transcript')">
              <td>002</td>
              <td>Mary Johnson</td>
              <td>Transcript</td>
              <td>2024-04-23</td>
              <td class="status-inreview">In Review</td>
              <td><button class="action-button" onclick="event.stopPropagation(); alert('Review Mary Johnson Transcript')">Review</button></td>
            </tr>
            <tr onclick="alert('Reviewing Alex Lee Enrollment Cert.')">
              <td>003</td>
              <td>Alex Lee</td>
              <td>Enrollment Cert.</td>
              <td>2024-04-27 06:00</td>
              <td class="status-pending">Pending</td>
              <td><button class="action-button" onclick="event.stopPropagation(); alert('Review Alex Lee Enrollment Cert.')">Review</button></td>
            </tr>
            <tr onclick="alert('Reviewing Emma Wilson Declined Statement')">
              <td>004</td>
              <td>Emma Wilson</td>
              <td>Declined Statement</td>
              <td>2024-04-19</td>
              <td class="status-declined">Declined</td>
              <td><button class="action-button" onclick="event.stopPropagation(); alert('Review Emma Wilson Declined Statement')">Review</button></td>
            </tr>
            <tr onclick="alert('Reviewing David Kim Personal Statement')">
              <td>005</td>
              <td>David Kim</td>
              <td>Personal Statement</td>
              <td>2024-03-31</td>
              <td class="status-approved">Approved</td>
              <td><button class="action-button" onclick="event.stopPropagation(); alert('Review David Kim Personal Statement')">Review</button></td>
            </tr>
          </tbody>
        </table>
      </section>
    </main>
  </div>
</body>
</html>