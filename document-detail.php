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
      <div class="doc-header">
        <h1>Document <span>John Doe Student File</span></h1>
        <div class="buttons">
          <button type="button"><i class="fas fa-download"></i> Download Zip</button>
          <button type="button"><i class="fas fa-edit"></i> Edit</button>
          <button type="button" class="delete"><i class="fas fa-trash-alt"></i> Delete</button>
        </div>
      </div>
      <div class="content">
        <section class="info-panel" aria-label="Document information">
          <div><span>Document Name:</span><span>John Doe Student File</span></div>
          <div><span>Tags:</span><span><span style="background:#7b2cbf;color:#fff;padding:2px 6px;border-radius:4px;font-size:9px;">Education</span></span></div>
          <div><span>Description:</span><span>Student academic and personal documents</span></div>
          <div><span>Status:</span><span class="status">Pending</span></div>
          <div><span>Created By:</span><span>Super Admin</span></div>
          <div><span>Created At:</span><span>04/20/2024 09:30 AM <small style="color:#9ca3af;">(2 days ago)</small></span></div>
          <div><span>Last Updated:</span><span>04/22/2024 02:15 PM <small style="color:#9ca3af;">(Today)</small></span></div>
        </section>
        <section class="files-panel" aria-label="Files, Verification, Activity, Permission tabs and content">
          <nav class="tabs" role="tablist">
            <button aria-current="page" role="tab" aria-selected="true" tabindex="0">Files</button>
            <button role="tab" aria-selected="false" tabindex="-1">Verification</button>
            <button role="tab" aria-selected="false" tabindex="-1">Activity</button>
            <button role="tab" aria-selected="false" tabindex="-1">Permission</button>
          </nav>
          <div class="files-grid" role="tabpanel">
            <article class="file-card" aria-label="Student ID card file">
              <div class="image-container">
                <img src="https://storage.googleapis.com/a1aa/image/cb304ae1-31e8-4989-8ebc-0785ab586bdb.jpg" alt="Student ID card with photo of young man and student details" />
              </div>
              <div class="info">
                <span class="uppercase">Student ID</span>
                <span>ID Card</span>
                <span style="font-size:10px;">2 days ago by Super Admin</span>
                <i class="fas fa-info-circle" title="More info"></i>
              </div>
            </article>
            </article>
            <article class="file-card" aria-label="Personal statement file">
              <div class="image-container personal-statement">
                <img src="https://storage.googleapis.com/a1aa/image/93c42d1e-1914-4711-bf4c-7ca7bae80064.jpg" alt="PDF icon white text on blue background representing personal statement document" />
              </div>
              <div class="info">
                <span class="uppercase">Personal Statement</span>
                <span>Statement</span>
                <span style="font-size:10px;">4 days ago by Super Admin</span>
                <i class="fas fa-info-circle" title="More info"></i>
              </div>
            </article>
          </div>
          <button class="add-files-btn" type="button">+ Add Files</button>
        </section>
      </div>
    </main>
  </div>
</body>
</html>