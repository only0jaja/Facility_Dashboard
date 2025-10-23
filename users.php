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
    <!-- User Style -->
    <link rel="stylesheet" href="styles/users.css">
    <!-- Sidebar Css -->
    <link rel="stylesheet" href="styles/sidebar.css">

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
        <div class="controls-section">
            <h1>Rooms</h1>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search Name, ID,Course">
            </div>
        </div>
        <div class="search-container">
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
                Clear Filters
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

    <script src="js/users.js"></script>

</body>
</html>