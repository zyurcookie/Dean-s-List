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
  <title>FEU Roosevelt Dean's List</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
  />
  <style>
    /* Reset and base */
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      font-size: 13px;
      color: #2e2e3a;
      background-color: #e6f0eb;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    a {
      text-decoration: none;
      color: inherit;
    }

    /* Header */
    header {
      background-color: #3f44d3;
      color: white;
      height: 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 16px;
      font-weight: 600;
      font-size: 14px;
      flex-shrink: 0;
    }
    header .user {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
    }

    /* Layout */
    .container {
      display: flex;
      flex: 1;
      min-height: calc(100vh - 40px);
    }

    /* Sidebar */
    aside {
      background-color: #b7c0f7;
      width: 208px;
      padding: 16px;
      display: flex;
      flex-direction: column;
      gap: 24px;
      flex-shrink: 0;
    }
    aside .profile {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    aside .profile .avatar {
      background-color: #3f44d3;
      color: white;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      font-weight: 600;
      font-size: 14px;
      user-select: none;
    }
    aside .profile .name {
      font-weight: 600;
      font-size: 13px;
      color: #2e2e3a;
    }
    aside .search-container {
      position: relative;
    }
    aside input[type="text"] {
      width: 100%;
      padding: 6px 28px 6px 10px;
      border-radius: 6px;
      border: none;
      font-size: 13px;
      outline: none;
      box-shadow: 0 0 0 2px transparent;
      transition: box-shadow 0.2s ease;
    }
    aside input[type="text"]:focus {
      box-shadow: 0 0 0 2px #3f44d3;
    }
    aside .search-container .fa-search {
      position: absolute;
      right: 8px;
      top: 50%;
      transform: translateY(-50%);
      color: #3f44d3;
      font-size: 13px;
      pointer-events: none;
    }

    /* Navigation */
    nav {
      display: flex;
      flex-direction: column;
      gap: 8px;
      font-weight: 600;
      font-size: 13px;
      color: #2e2e3a;
    }
    nav a, nav .settings {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 6px 12px;
      border-radius: 6px;
      cursor: pointer;
      user-select: none;
      transition: background-color 0.2s ease, color 0.2s ease;
    }
    nav a:hover, nav .settings:hover {
      color: #3f44d3;
    }
    nav a.active {
      background-color: #3f44d3;
      color: white;
    }
    nav a i, nav .settings i {
      font-size: 14px;
      flex-shrink: 0;
    }
    nav .settings {
      justify-content: space-between;
    }
    nav .settings i.fa-chevron-down {
      font-size: 12px;
      color: #2e2e3a;
    }

    /* Main content */
    main {
      flex: 1;
      padding: 24px;
      display: flex;
      gap: 24px;
      max-width: 1120px;
      margin: 0 auto;
      flex-wrap: wrap;
      justify-content: center;
    }

    /* Sections */
    section {
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgb(0 0 0 / 0.1);
      padding: 24px;
      width: 100%;
      max-width: 384px;
      display: flex;
      flex-direction: column;
    }
    section h2 {
      color: #3f44d3;
      font-weight: 600;
      font-size: 14px;
      margin: 0 0 12px 0;
      text-align: center;
    }

    /* Quick Upload */
    .quick-upload p.choose-doc {
      font-size: 12px;
      color: #3f44d3;
      font-weight: 600;
      text-align: center;
      margin: 0 0 12px 0;
    }
    .quick-upload select {
      width: 100%;
      padding: 6px 10px;
      font-size: 13px;
      border-radius: 6px;
      border: 1px solid #d1d5db;
      margin-bottom: 16px;
      outline: none;
      transition: box-shadow 0.2s ease;
    }
    .quick-upload select:focus {
      box-shadow: 0 0 0 2px #3f44d3;
      border-color: transparent;
    }
    .quick-upload button {
      background-color: #3f44d3;
      color: white;
      font-weight: 600;
      font-size: 13px;
      padding: 8px 0;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.2s ease;
    }
    .quick-upload button:hover {
      background-color: #2e33b8;
    }

    /* Eligibility list */
    .eligibility-list {
      margin-top: 24px;
      border-top: 1px solid #e5e7eb;
      padding-top: 16px;
      font-size: 12px;
      color: #2e2e3a;
      line-height: 1.4;
    }
    .eligibility-list p.title {
      font-weight: 600;
      font-size: 14px;
      margin-bottom: 8px;
    }
    .eligibility-list ol {
      margin: 0;
      padding-left: 20px;
    }
    .eligibility-list ol li {
      margin-bottom: 6px;
    }

    /* Eligibility Verification */
    .eligibility-verification ul {
      list-style: none;
      padding: 0;
      margin: 0;
      font-size: 12px;
      color: #2e2e3a;
      display: flex;
      flex-direction: column;
      gap: 8px;
      flex-grow: 1;
      overflow-y: auto;
    }
    .eligibility-verification ul li {
      display: flex;
      align-items: flex-start;
      gap: 8px;
    }
    .eligibility-verification ul li i {
      color: #3f44d3;
      margin-top: 3px;
      font-size: 12px;
      flex-shrink: 0;
    }
    .eligibility-verification ul li p {
      margin: 0;
      line-height: 1.3;
    }

    /* Waiting message */
    .waiting-message {
      color: #3f44d3;
      font-weight: 600;
      font-size: 14px;
      text-align: center;
      padding: 80px 0;
      width: 100%;
    }

    /* Responsive */
    @media (max-width: 768px) {
      aside {
        width: 160px;
        padding: 12px;
      }
      main {
        padding: 16px;
        gap: 16px;
      }
      section {
        max-width: 100%;
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <header>
    <div>FEU Roosevelt Dean's List</div>
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
        <input type="text" placeholder="Search..." />
        <i class="fas fa-search"></i>
      </div>
      <nav>
        <a href="admin_dashboard.php" class="active"><i class="fas fa-chart-bar"></i>Dashboard</a>
        <a href="documents.php"><i class="fas fa-folder"></i>Student Files</a>
        <a href="user.php"><i class="fas fa-users"></i>Users</a>
        <a href="tags.php"><i class="fas fa-tags"></i>Tags</a>
        <div class="settings">
          <div><i class="fas fa-cog"></i>Settings</div>
          <i class="fas fa-chevron-down"></i>
        </div>
      </nav>
    </aside>

    <main>
      <section class="quick-upload">
        <h2>Quick Upload</h2>
        <p class="choose-doc">Choose Document</p>
        <select>
          <option>Select document type</option>
        </select>
        <button>Upload</button>

        <div class="eligibility-list">
          <p class="title">Eligibility:</p>
          <ol>
            <li>Quality Point Average (QPA) must be at least 3.50 (B+) in the proceeding semester</li>
            <li>Has no grade lower than 3.00(b)</li>
            <li>Regular Student (no backload and advance subject taken)</li>
            <li>Has taken only the course specified in his/her curriculum in the previous semester.</li>
            <li>Has no grade if "Incomplete" upon encoding of the Faculty, nor a grade of "Dropped" or "Failed" in any subject including PATHFIT and NSTP.</li>
            <li>Has not violated any of the rules and regulation of the school</li>
            <li>Attended at least 80% of the total face to face /Online class periods.</li>
          </ol>
        </div>
      </section>

      <section class="eligibility-verification">
        <h2>Eligibility Verification</h2>
        <div class="waiting-message">Waiting for the documents upload</div>
      </section>
    </main>
  </div>
</body>
</html>