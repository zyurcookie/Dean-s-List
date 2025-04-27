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
        <div class="name">Dean</div>
      </div>
      <div class="search-container">
        <input type="search" placeholder="Search..." />
        <i class="fas fa-search"></i>
      </div>
      <nav>
        <a href="admin_dashboard.php" ><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
        <a href="documents.php" ><i class="fas fa-file-alt"></i><span>Student Files</span></a>
        <a href="tags.php" ><i class="fas fa-users"></i><span>Users</span></a>
        <a href="#" class="active"><i class="fas fa-tags"></i><span>Tags</span></a>
        <div class="settings"><i class="fas fa-cog"></i><span>Settings</span><i class="fas fa-chevron-down"></i></div>
      </nav>
    </aside>
    <main>
      <h1 class="page-title">Tags</h1>
      <section class="tags-list" aria-label="List of tags">
        <ul>
          <li>
            <div class="tag-color tag-education" title="Education"></div>
            <div class="tag-name">Education</div>
          </li>
          <li>
            <div class="tag-color tag-urgent" title="Urgent"></div>
            <div class="tag-name">Urgent</div>
          </li>
          <li>
            <div class="tag-color tag-finance" title="Finance"></div>
            <div class="tag-name">Finance</div>
          </li>
          <li>
            <div class="tag-color tag-personal" title="Personal"></div>
            <div class="tag-name">Personal</div>
          </li>
          <li>
            <div class="tag-color" style="background:#f59e0b;" title="Pending"></div>
            <div class="tag-name">Pending</div>
          </li>
          <li>
            <div class="tag-color" style="background:#16a34a;" title="Approved"></div>
            <div class="tag-name">Approved</div>
          </li>
          <li>
            <div class="tag-color" style="background:#b91c1c;" title="Declined"></div>
            <div class="tag-name">Declined</div>
          </li>
        </ul>
      </section>
    </main>
  </div>
</body>
</html>