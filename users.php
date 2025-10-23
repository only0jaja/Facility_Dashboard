<?php
include 'conn.php';

// Handle form submission for adding user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $rfid_tag = trim($_POST['rfid_tag']);
    $f_name = trim($_POST['f_name']);
    $l_name = trim($_POST['l_name']);
    $courseSection_id = $_POST['courseSection_id'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    
    // Validate required fields
    if (!empty($rfid_tag) && !empty($f_name) && !empty($l_name) && !empty($role) && !empty($status)) {
        
        // Check if RFID tag already exists
        $check_sql = "SELECT User_id FROM users WHERE Rfid_tag = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        
        if ($check_stmt) {
            mysqli_stmt_bind_param($check_stmt, "s", $rfid_tag);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);
            
            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $error_message = "Error: RFID tag '$rfid_tag' already exists!";
            } else {
                // Handle CourseSection_id based on role
                if ($role === 'Student' && !empty($courseSection_id)) {
                    // Student with course section
                    $insert_sql = "INSERT INTO users (Rfid_tag, F_name, L_name, CourseSection_id, Role, Status) VALUES (?, ?, ?, ?, ?, ?)";
                    $insert_stmt = mysqli_prepare($conn, $insert_sql);
                    if ($insert_stmt) {
                        mysqli_stmt_bind_param($insert_stmt, "sssiss", $rfid_tag, $f_name, $l_name, $courseSection_id, $role, $status);
                    }
                } else {
                    // Faculty/Admin or Student without course section (set to NULL)
                    $insert_sql = "INSERT INTO users (Rfid_tag, F_name, L_name, CourseSection_id, Role, Status) VALUES (?, ?, ?, NULL, ?, ?)";
                    $insert_stmt = mysqli_prepare($conn, $insert_sql);
                    if ($insert_stmt) {
                        mysqli_stmt_bind_param($insert_stmt, "sssss", $rfid_tag, $f_name, $l_name, $role, $status);
                    }
                }
                
                if ($insert_stmt && mysqli_stmt_execute($insert_stmt)) {
                    $success_message = "User added successfully!";
                    // Refresh the page to show the new user
                    header("Location: users.php");
                    exit();
                } else {
                    $error_message = "Error adding user: " . mysqli_error($conn);
                }
                
                if (isset($insert_stmt)) {
                    mysqli_stmt_close($insert_stmt);
                }
            }
            mysqli_stmt_close($check_stmt);
        } else {
            $error_message = "Error preparing check statement: " . mysqli_error($conn);
        }
    } else {
        $error_message = "Please fill in all required fields!";
    }
}

// Handle user status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $user_id = $_POST['user_id'];
    $status = $_POST['status'];
    
    $update_sql = "UPDATE users SET Status = ? WHERE User_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    
    if ($update_stmt) {
        mysqli_stmt_bind_param($update_stmt, "si", $status, $user_id);
        if (mysqli_stmt_execute($update_stmt)) {
            $success_message = "User status updated successfully!";
            header("Location: users.php");
            exit();
        } else {
            $error_message = "Error updating user status: " . mysqli_error($conn);
        }
        mysqli_stmt_close($update_stmt);
    }
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    
    $delete_sql = "DELETE FROM users WHERE User_id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_sql);
    
    if ($delete_stmt) {
        mysqli_stmt_bind_param($delete_stmt, "i", $user_id);
        if (mysqli_stmt_execute($delete_stmt)) {
            $success_message = "User deleted successfully!";
            header("Location: users.php");
            exit();
        } else {
            $error_message = "Error deleting user: " . mysqli_error($conn);
        }
        mysqli_stmt_close($delete_stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Management</title>
    <!-- Font Style -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <!-- Icons Style 1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <!-- Icons Style 2 -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
        }

      .sidebar {
      width: 250px;
      background-color: #0047AB;
      color: white;
      display: flex;
      flex-direction: column;
      height: 100vh;
      padding: 20px;
      position: fixed;
      transition: transform 0.3s ease;
    }

    .sidebar h1 {
      font-size: 24px;
      margin-bottom: 40px;
      color: #FFD700;
    }

    .sidebar a {
      color: white;
      text-decoration: none;
      margin: 10px 0;
      display: flex;
      align-items: center;
      padding: 12px;
      border-radius: 8px;
      font-weight: 500;
      transition: background 0.3s ease;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background-color: #005ce6;
    }

    .sidebar .user {
      margin-top: auto;
      display: flex;
      align-items: center;
      padding: 15px;
      border-top: 1px solid #ffffff33;
      font-size: 14px;
    }

    .sidebar .user span {
      margin-left: 10px;
    }
    .sidebar i{
        padding: 10px;
        font-size: 22px;
    }

        .icons a {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .icons a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .icons a.active {
            background: rgba(255, 255, 255, 0.2);
            font-weight: 600;
        }

        .icons i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .user {
            position: absolute;
            bottom: 20px;
            display: flex;
            align-items: center;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            width: calc(100% - 40px);
        }

        .user span {
            margin-left: 10px;
        }

        .user small {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .header {
            margin-left: 250px;
            padding: 30px;
            width: calc(100% - 250px);
        }

        .header h1 {
            color: #2a5298;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .search-container {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-container input[type="search"] {
            padding: 12px 15px;
            border: 1px solid #e0e6ed;
            border-radius: 8px;
            width: 300px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .search-container input[type="search"]:focus {
            outline: none;
            border-color: #2a5298;
            box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.1);
        }

        .filter-controls {
            display: flex;
            gap: 15px;
            
        }

        .filter-controls select {
            padding: 10px 15px;
            border: 1px solid #e0e6ed;
            border-radius: 8px;
            background: white;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-controls select:focus {
            outline: none;
            border-color: #2a5298;
        }

        .clear-filters {
            padding: 10px 15px;
            background: #f0f4f9;
            border: 1px solid #e0e6ed;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            color: #5a6c7d;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .clear-filters:hover {
            background: #e0e6ed;
        }

        .user-table {
            margin: 20px 100px 0 280px;
            width: calc(94% - 225px);
          
        }

        .table-section {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8fafc;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #2a5298;
            border-bottom: 2px solid #e0e6ed;
            font-size: 0.9rem;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f0f4f9;
            font-size: 0.9rem;
        }

        tbody tr {
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        .status-active {
            color: #2ecc71;
            font-weight: 600;
        }

        .status-inactive {
            color: #e74c3c;
            font-weight: 600;
        }

        .role-admin {
            background: #e8f4fd;
            color: #2a5298;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .role-student {
            background: #e8f8ef;
            color: #2ecc71;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .role-faculty {
            background: #fef5e7;
            color: #f39c12;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            color: #7e8a9a;
            font-size: 1rem;
        }

        .table-scroll {
            overflow-x: auto;
        }

        /* Add User Button Styles */
        .add-user-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .add-user-btn:hover {
            background: #1e7e34;
            transform: scale(1.05);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-edit {
            background: #ffc107;
            color: #212529;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .btn-edit:hover {
            background: #e0a800;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 500px;
            max-width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #0047AB;
            background: white;
            z-index: 1;
        }

        .modal-header h2 {
            color: #0047AB;
            margin: 0;
            font-weight: 600;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            position: sticky;
            top: 0;
        }

        .close:hover {
            color: #000;
        }

        /* Custom scrollbar styling for modal */
        .modal-content::-webkit-scrollbar {
            width: 8px;
        }

        .modal-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .modal-content::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .modal-content::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* For Firefox */
        .modal-content {
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e0e6ed;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2a5298;
            box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.1);
        }

        .required {
            color: #e74c3c;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 25px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #0047AB;
            color: white;
        }

        .btn-primary:hover {
            background-color: #003580;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #545b62;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .user-info p {
            margin: 5px 0;
            font-size: 0.9rem;
        }

        @media (max-width: 1024px) {
            .sidebar {
                width: 220px;
            }
            
            .header, .user-table {
                margin-left: 220px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 15px 10px;
            }
            
            .sidebar h1, .icons a span, .user span {
                display: none;
            }
            
            .icons a {
                justify-content: center;
                padding: 15px 0;
            }
            
            .icons i {
                margin-right: 0;
                font-size: 1.4rem;
            }
            
            .header, .user-table {
                margin-left: 70px;
            }
            
            .header {
                padding: 20px;
            }
            
            .search-container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-container input[type="search"] {
                width: 100%;
            }
            
            .filter-controls {
                flex-wrap: wrap;
            }
            
            .table-scroll {
                overflow-x: auto;
            }
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
 <div class="sidebar" id="sidebar">
      <h1>Dashboard</h1>
      <div class="icons">
          <a href="index.php" ><i class='bx bxs-home'></i>Home</a>
          <a href="users.php"class="active"><i class='bx bxs-user-pin' ></i> Users</a>
          <a href="rooms.php"><i class='bx bx-folder-open'></i> Rooms</a>
          <a href="access_logs.php"><i class='bx bx-bookmark-alt-plus'></i> Access Logs</a>
          <a href="schedule.php"><i class='bx bx-calendar-week'></i> Schedule</a>
          <a href="logout.php"><i class='bx bxs-log-out'></i> Log out</a>
      </div>
      <div class="user">
          👤 <span>Juan<br><small>Faculty Member</small></span>
      </div>
  </div>

  <!-- user header -->
  <div class="header">
    <h1>Users</h1>
    <div class="search-container">
      <input type="search" id="searchInput" placeholder="Search by name, ID, RFID, or course...">
      <div class="filter-controls">
        <select id="roleFilter">
          <option value="">All Roles</option>
          <option value="Student">Student</option>
          <option value="Faculty">Faculty</option>
          <option value="Admin">Admin</option>
        </select>
        <select id="statusFilter">
          <option value="">All Status</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
        <select id="courseFilter">
          <option value="">All Courses</option>
          <?php
            $courseSql = "SELECT DISTINCT CourseSection FROM course_section ORDER BY CourseSection";
            $courseResult = mysqli_query($conn, $courseSql);
            while($course = mysqli_fetch_assoc($courseResult)) {
                echo '<option value="' . htmlspecialchars($course['CourseSection']) . '">' . htmlspecialchars($course['CourseSection']) . '</option>';
            }
          ?>
        </select>
        <button class="clear-filters" id="clearFilters">
          <i class="fas fa-times"></i> Clear Filters
        </button>
        <button class="add-user-btn" id="addUserBtn">
          <i class="fas fa-plus"></i> Add User
        </button>
      </div>
    </div>
  </div>

  <!-- user table -->
  <div class="user-table">
    <div class="table-section">
      <div class="table-scroll">
        <table id="usersTable">
          <thead>
            <tr>
              <th>User_id</th>
              <th>Rfid_tag</th>
              <th>f_name</th>
              <th>l_name</th>
              <th>CourseSection</th>
              <th>Role</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="usersTableBody">
            <?php 
              $sql = 'SELECT users.*, course_section.CourseSection 
                      FROM users 
                      JOIN course_section ON users.courseSection_id = course_section.courseSection_id
                      ORDER BY users.User_id';
              $users = mysqli_query($conn,$sql);
            ?>
            <?php if(mysqli_num_rows($users) > 0): ?>
              <?php while($row = mysqli_fetch_assoc($users)): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['User_id']); ?></td>
                  <td><?php echo htmlspecialchars($row['Rfid_tag']); ?></td>
                  <td><?php echo htmlspecialchars($row['F_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['L_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['CourseSection']); ?></td>
                  <td>
                    <span class="role-<?php echo strtolower($row['Role']); ?>">
                      <?php echo htmlspecialchars($row['Role']); ?>
                    </span>
                  </td>
                  <td>
                    <span class="status-<?php echo strtolower($row['Status']); ?>">
                      <?php echo htmlspecialchars($row['Status']); ?>
                    </span>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <button class="btn-edit" onclick="openEditModal(<?php echo $row['User_id']; ?>, '<?php echo $row['F_name']; ?>', '<?php echo $row['L_name']; ?>', '<?php echo $row['Status']; ?>')">
                        <i class="fas fa-edit"></i> Edit
                      </button>
                      <button class="btn-delete" onclick="openDeleteModal(<?php echo $row['User_id']; ?>, '<?php echo $row['F_name']; ?>', '<?php echo $row['L_name']; ?>')">
                        <i class="fas fa-trash"></i> Delete
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="no-results">No users found</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Add User Modal -->
  <div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New User</h2>
            <span class="close">&times;</span>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="rfid_tag">RFID Tag <span class="required">*</span></label>
                <input type="text" id="rfid_tag" name="rfid_tag" required 
                       placeholder="Enter RFID tag (e.g., 82 04 10 01)">
            </div>
            
            <div class="form-group">
                <label for="f_name">First Name <span class="required">*</span></label>
                <input type="text" id="f_name" name="f_name" required 
                       placeholder="Enter first name">
            </div>
            
            <div class="form-group">
                <label for="l_name">Last Name <span class="required">*</span></label>
                <input type="text" id="l_name" name="l_name" required 
                       placeholder="Enter last name">
            </div>
            
            <div class="form-group">
                <label for="role">Role <span class="required">*</span></label>
                <select id="role" name="role" required onchange="toggleCourseSection()">
                    <option value="">Select Role</option>
                    <option value="Student">Student</option>
                    <option value="Faculty">Faculty</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>
            
            <div class="form-group" id="courseSectionGroup">
                <label for="courseSection_id">Course Section</label>
                <select id="courseSection_id" name="courseSection_id">
                    <option value="">Select Course Section</option>
                    <?php
                        $courseSql = "SELECT * FROM course_section ORDER BY CourseSection";
                        $courseResult = mysqli_query($conn, $courseSql);
                        while($course = mysqli_fetch_assoc($courseResult)) {
                            echo '<option value="' . $course['CourseSection_id'] . '">' . htmlspecialchars($course['CourseSection']) . '</option>';
                        }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="status">Status <span class="required">*</span></label>
                <select id="status" name="status" required>
                    <option value="">Select Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                <button type="submit" class="btn btn-primary" name="add_user">Add User</button>
            </div>
        </form>
    </div>
  </div>

  <!-- Edit Status Modal -->
  <div id="editStatusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit User Status</h2>
            <span class="close">&times;</span>
        </div>
        
        <form method="POST" action="">
            <input type="hidden" id="edit_user_id" name="user_id">
            
            <div class="user-info">
                <p><strong>User:</strong> <span id="edit_user_name"></span></p>
                <p><strong>Current Status:</strong> <span id="edit_current_status"></span></p>
            </div>
            
            <div class="form-group">
                <label for="edit_status">New Status <span class="required">*</span></label>
                <select id="edit_status" name="status" required>
                    <option value="">Select Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="cancelEditBtn">Cancel</button>
                <button type="submit" class="btn btn-primary" name="update_status">Update Status</button>
            </div>
        </form>
    </div>
  </div>

  <!-- Delete User Modal -->
  <div id="deleteUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Delete User</h2>
            <span class="close">&times;</span>
        </div>
        
        <form method="POST" action="">
            <input type="hidden" id="delete_user_id" name="user_id">
            
            <div class="user-info">
                <p><strong>User:</strong> <span id="delete_user_name"></span></p>
                <p class="alert alert-error">Are you sure you want to delete this user? This action cannot be undone.</p>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="cancelDeleteBtn">Cancel</button>
                <button type="submit" class="btn btn-danger" name="delete_user">Delete User</button>
            </div>
        </form>
    </div>
  </div>

  <script>
    // Modal functionality
    const addUserModal = document.getElementById('addUserModal');
    const editStatusModal = document.getElementById('editStatusModal');
    const deleteUserModal = document.getElementById('deleteUserModal');
    const addUserBtn = document.getElementById('addUserBtn');
    const closeBtns = document.querySelectorAll('.close');
    const cancelBtn = document.getElementById('cancelBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

    // Open add user modal
    addUserBtn.addEventListener('click', function() {
        addUserModal.style.display = 'block';
    });

    // Open edit status modal
    function openEditModal(userId, firstName, lastName, currentStatus) {
        document.getElementById('edit_user_id').value = userId;
        document.getElementById('edit_user_name').textContent = firstName + ' ' + lastName;
        document.getElementById('edit_current_status').textContent = currentStatus;
        document.getElementById('edit_status').value = currentStatus;
        editStatusModal.style.display = 'block';
    }

    // Open delete user modal
    function openDeleteModal(userId, firstName, lastName) {
        document.getElementById('delete_user_id').value = userId;
        document.getElementById('delete_user_name').textContent = firstName + ' ' + lastName;
        deleteUserModal.style.display = 'block';
    }

    // Close modals
    function closeAllModals() {
        addUserModal.style.display = 'none';
        editStatusModal.style.display = 'none';
        deleteUserModal.style.display = 'none';
    }

    closeBtns.forEach(btn => {
        btn.addEventListener('click', closeAllModals);
    });

    cancelBtn.addEventListener('click', closeAllModals);
    cancelEditBtn.addEventListener('click', closeAllModals);
    cancelDeleteBtn.addEventListener('click', closeAllModals);

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === addUserModal || event.target === editStatusModal || event.target === deleteUserModal) {
            closeAllModals();
        }
    });

    // Toggle course section based on role selection
    function toggleCourseSection() {
        const role = document.getElementById('role').value;
        const courseSectionGroup = document.getElementById('courseSectionGroup');
        
        if (role === 'Student') {
            courseSectionGroup.style.display = 'block';
        } else {
            courseSectionGroup.style.display = 'none';
            document.getElementById('courseSection_id').value = '';
        }
    }

    // Initialize course section visibility
    document.addEventListener('DOMContentLoaded', function() {
        toggleCourseSection();
    });

    // Function to filter table rows based on search and filter criteria
    function filterUsers() {
      const searchValue = document.getElementById('searchInput').value.toLowerCase();
      const roleFilter = document.getElementById('roleFilter').value;
      const statusFilter = document.getElementById('statusFilter').value;
      const courseFilter = document.getElementById('courseFilter').value;
      
      const tableBody = document.getElementById('usersTableBody');
      const rows = tableBody.getElementsByTagName('tr');
      
      let visibleRows = 0;
      
      for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let showRow = true;
        
        // Skip the "no users" row if it exists
        if (cells.length === 1 && cells[0].className === 'no-results') {
          rows[i].style.display = 'none';
          continue;
        }
        
        // Search filter - check all cells for matching text
        if (searchValue) {
          let rowContainsText = false;
          for (let j = 0; j < cells.length; j++) {
            if (cells[j].textContent.toLowerCase().includes(searchValue)) {
              rowContainsText = true;
              break;
            }
          }
          if (!rowContainsText) {
            showRow = false;
          }
        }
        
        // Role filter
        if (roleFilter && showRow) {
          const roleCell = cells[5]; // Role is in the 6th column (index 5)
          if (roleCell && roleCell.textContent.trim() !== roleFilter) {
            showRow = false;
          }
        }
        
        // Status filter
        if (statusFilter && showRow) {
          const statusCell = cells[6]; // Status is in the 7th column (index 6)
          if (statusCell && statusCell.textContent.trim() !== statusFilter) {
            showRow = false;
          }
        }
        
        // Course filter
        if (courseFilter && showRow) {
          const courseCell = cells[4]; // Course is in the 5th column (index 4)
          if (courseCell && courseCell.textContent.trim() !== courseFilter) {
            showRow = false;
          }
        }
        
        // Show or hide the row
        rows[i].style.display = showRow ? '' : 'none';
        if (showRow) visibleRows++;
      }
      
      // Show "no results" message if no rows are visible
      const existingNoResults = tableBody.querySelector('.no-results');
      if (existingNoResults) {
        existingNoResults.parentElement.remove();
      }
      
      if (visibleRows === 0) {
        const noResultsRow = document.createElement('tr');
        noResultsRow.innerHTML = '<td colspan="8" class="no-results">No users match your search criteria</td>';
        tableBody.appendChild(noResultsRow);
      }
    }

    // Event listeners for filtering
    document.getElementById('searchInput').addEventListener('input', filterUsers);
    document.getElementById('roleFilter').addEventListener('change', filterUsers);
    document.getElementById('statusFilter').addEventListener('change', filterUsers);
    document.getElementById('courseFilter').addEventListener('change', filterUsers);

    // Clear filters button
    document.getElementById('clearFilters').addEventListener('click', function() {
      document.getElementById('searchInput').value = '';
      document.getElementById('roleFilter').value = '';
      document.getElementById('statusFilter').value = '';
      document.getElementById('courseFilter').value = '';
      filterUsers();
    });
  </script>
</body>
</html>x    