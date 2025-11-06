<?php
include 'conn.php';
session_start();

// Prevent browser from caching this page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Get current tab from URL or default to students
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'students';

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
                if ($role === 'Student') {
                    if (!empty($courseSection_id)) {
                        // Verify the course section exists
                        $verify_course_sql = "SELECT CourseSection_id FROM course_section WHERE CourseSection_id = ?";
                        $verify_course_stmt = mysqli_prepare($conn, $verify_course_sql);
                        mysqli_stmt_bind_param($verify_course_stmt, "i", $courseSection_id);
                        mysqli_stmt_execute($verify_course_stmt);
                        mysqli_stmt_store_result($verify_course_stmt);
                        
                        if (mysqli_stmt_num_rows($verify_course_stmt) > 0) {
                            // Student with valid course section
                            $insert_sql = "INSERT INTO users (Rfid_tag, F_name, L_name, CourseSection_id, Role, Status) VALUES (?, ?, ?, ?, ?, ?)";
                            $insert_stmt = mysqli_prepare($conn, $insert_sql);
                            if ($insert_stmt) {
                                mysqli_stmt_bind_param($insert_stmt, "sssiss", $rfid_tag, $f_name, $l_name, $courseSection_id, $role, $status);
                            }
                        } else {
                            $error_message = "Error: Invalid course section selected!";
                            mysqli_stmt_close($verify_course_stmt);
                            mysqli_stmt_close($check_stmt);
                            $courseSection_id = null;
                        }
                        mysqli_stmt_close($verify_course_stmt);
                    } else {
                        $error_message = "Course Section is required for Students!";
                    }
                } else {
                    // Faculty/Admin - set CourseSection_id to NULL
                    $insert_sql = "INSERT INTO users (Rfid_tag, F_name, L_name, CourseSection_id, Role, Status) VALUES (?, ?, ?, NULL, ?, ?)";
                    $insert_stmt = mysqli_prepare($conn, $insert_sql);
                    if ($insert_stmt) {
                        mysqli_stmt_bind_param($insert_stmt, "sssss", $rfid_tag, $f_name, $l_name, $role, $status);
                    }
                }
                
                // Only proceed with insertion if no errors
                if (!isset($error_message)) {
                    if (isset($insert_stmt) && mysqli_stmt_execute($insert_stmt)) {
                        $success_message = "User added successfully!";
                        // Redirect to appropriate tab
                        $redirect_tab = ($role === 'Student') ? 'students' : 'faculty';
                        header("Location: users.php?tab=$redirect_tab");
                        exit();
                    } else {
                        $error_message = "Error adding user: " . mysqli_error($conn);
                    }
                    
                    if (isset($insert_stmt)) {
                        mysqli_stmt_close($insert_stmt);
                    }
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
            header("Location: users.php?tab=$current_tab");
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
    
    // First, check if the user is a faculty and their status
    $check_sql = "SELECT Role, Status FROM users WHERE User_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    
    if ($check_stmt) {
        mysqli_stmt_bind_param($check_stmt, "i", $user_id);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            mysqli_stmt_bind_result($check_stmt, $role, $status);
            mysqli_stmt_fetch($check_stmt);
            
            mysqli_stmt_free_result($check_stmt);
            mysqli_stmt_close($check_stmt);

            // Check if user is faculty and active
            if ($role === 'Faculty' && $status === 'Active') {
                $error_message = "Cannot delete faculty member with Active status. Please set status to Inactive first.";
            } else {
                // Start transaction for safe deletion
                mysqli_begin_transaction($conn);
                
                try {
                    // Handle ALL foreign key constraints
                    if ($role === 'Faculty') {
                        $get_faculty_schedules_sql = "SELECT Schedule_id FROM schedule WHERE Faculty_id = ?";
                        $get_faculty_schedules_stmt = mysqli_prepare($conn, $get_faculty_schedules_sql);
                        if ($get_faculty_schedules_stmt) {
                            mysqli_stmt_bind_param($get_faculty_schedules_stmt, "i", $user_id);
                            mysqli_stmt_execute($get_faculty_schedules_stmt);
                            mysqli_stmt_bind_result($get_faculty_schedules_stmt, $schedule_id);

                            $scheduleIds = [];
                            while (mysqli_stmt_fetch($get_faculty_schedules_stmt)) {
                                $scheduleIds[] = $schedule_id;
                            }
                            mysqli_stmt_close($get_faculty_schedules_stmt);
                        }

                        // Delete schedule_access entries
                        if (!empty($scheduleIds)) {
                            $delete_schedule_access_sql = "DELETE FROM schedule_access WHERE Schedule_id = ?";
                            $delete_schedule_access_stmt = mysqli_prepare($conn, $delete_schedule_access_sql);
                            if ($delete_schedule_access_stmt) {
                                foreach ($scheduleIds as $sid) {
                                    mysqli_stmt_bind_param($delete_schedule_access_stmt, "i", $sid);
                                    if (!mysqli_stmt_execute($delete_schedule_access_stmt)) {
                                        throw new Exception("Error deleting schedule_access: " . mysqli_error($conn));
                                    }
                                }
                                mysqli_stmt_close($delete_schedule_access_stmt);
                            }
                        }

                        // Delete schedules
                        $delete_schedule_sql = "DELETE FROM schedule WHERE Faculty_id = ?";
                        $delete_schedule_stmt = mysqli_prepare($conn, $delete_schedule_sql);
                        if ($delete_schedule_stmt) {
                            mysqli_stmt_bind_param($delete_schedule_stmt, "i", $user_id);
                            if (!mysqli_stmt_execute($delete_schedule_stmt)) {
                                throw new Exception("Error deleting faculty schedules: " . mysqli_error($conn));
                            }
                            mysqli_stmt_close($delete_schedule_stmt);
                        }
                    }
                    
                    // Handle access_log constraints
                    $update_log_sql = "UPDATE access_log SET User_id = NULL WHERE User_id = ?";
                    $update_log_stmt = mysqli_prepare($conn, $update_log_sql);
                    if ($update_log_stmt) {
                        mysqli_stmt_bind_param($update_log_stmt, "i", $user_id);
                        if (!mysqli_stmt_execute($update_log_stmt)) {
                            throw new Exception("Error updating access_log: " . mysqli_error($conn));
                        }
                        mysqli_stmt_close($update_log_stmt);
                    }
                    
                    // Delete the user
                    $delete_sql = "DELETE FROM users WHERE User_id = ?";
                    $delete_stmt = mysqli_prepare($conn, $delete_sql);
                    
                    if ($delete_stmt) {
                        mysqli_stmt_bind_param($delete_stmt, "i", $user_id);
                        if (mysqli_stmt_execute($delete_stmt)) {
                            if (mysqli_stmt_affected_rows($delete_stmt) > 0) {
                                mysqli_commit($conn);
                                mysqli_stmt_close($delete_stmt);
                                $success_message = "User deleted successfully!";
                                header("Location: users.php?tab=$current_tab");
                                exit();
                            } else {
                                throw new Exception("No user found with the specified ID.");
                            }
                        } else {
                            $error_msg = mysqli_error($conn);
                            throw new Exception("Error deleting user: " . $error_msg);
                        }
                    } else {
                        throw new Exception("Error preparing delete statement: " . mysqli_error($conn));
                    }
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $error_message = $e->getMessage();
                    
                    if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                        if ($role === 'Faculty') {
                            $error_message = "Cannot delete faculty member. They have complex schedule assignments. Please delete their schedules manually first from the Schedule page.";
                        } else {
                            $error_message = "Cannot delete user due to system constraints. Please contact administrator.";
                        }
                    }
                }
            }
        } else {
            $error_message = "User not found!";
        }
    } else {
        $error_message = "Error checking user: " . mysqli_error($conn);
    }
}

// Get statistics for dashboard
$student_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE Role = 'Student' AND Status = 'Active'"))['count'];
$faculty_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE Role = 'Faculty' AND Status = 'Active'"))['count'];
$inactive_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE Status = 'Inactive'"))['count'];
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
        <img src="./img/loalogo.png" alt="Lyceum of Alabang Logo" style="width:120px; height:120px; border-radius:50%; object-fit: cover;margin-left: auto; margin-right: auto;">
        <h2 style="text-align: center; font-size: 20px;margin: 15px 0">Lyceum of Alabang</h2>
        
        <div class="icons">
            <a href="index.php" class=""><i class='bx bxs-home'></i>Home</a>
            <a href="users.php" class="active"><i class='bx bxs-user-pin' ></i> Users</a>
            <a href="rooms.php"><i class='bx bx-folder-open'></i> Rooms</a>
            <a href="access_logs.php"><i class='bx bx-bookmark-alt-plus'></i> Access Logs</a>
            <a href="schedule.php"><i class='bx bx-calendar-week'></i> Schedule</a>
            <a href="logout.php"><i class='bx bxs-log-out'></i> Log out</a>
        </div>
        <div class="user">
            👤 <span>Juan<br><small>Faculty Member</small></span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Statistics Dashboard -->
        <div class="dashboard-stats">
            <div class="stat-card student-stat">
                <div class="stat-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $student_count; ?></h3>
                    <p>Active Students</p>
                </div>
            </div>
            <div class="stat-card faculty-stat">
                <div class="stat-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $faculty_count; ?></h3>
                    <p>Active Faculty</p>
                </div>
            </div>
            <div class="stat-card inactive-stat">
                <div class="stat-icon">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $inactive_count; ?></h3>
                    <p>Inactive Users</p>
                </div>
            </div>
        </div>

        <!-- Header Section -->
        <div class="header">
            <div class="controls-section">
                <h1>User Management</h1>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search Name, ID, Course...">
                </div>
            </div>
            
            <!-- Tab Navigation -->
            <div class="tab-navigation">
                <button class="tab-btn <?php echo $current_tab === 'students' ? 'active' : ''; ?>" data-tab="students">
                    <i class="fas fa-user-graduate"></i>
                    Students
                    <span class="tab-badge"><?php echo $student_count; ?></span>
                </button>
                <button class="tab-btn <?php echo $current_tab === 'faculty' ? 'active' : ''; ?>" data-tab="faculty">
                    <i class="fas fa-chalkboard-teacher"></i>
                    Faculty
                    <span class="tab-badge"><?php echo $faculty_count; ?></span>
                </button>
                <button class="tab-btn <?php echo $current_tab === 'all' ? 'active' : ''; ?>" data-tab="all">
                    <i class="fas fa-users"></i>
                    All Users
                    <span class="tab-badge"><?php echo $student_count + $faculty_count + $inactive_count; ?></span>
                </button>
            </div>

            <div class="search-container">
                <div class="filter-controls">
                    <?php if ($current_tab === 'students' || $current_tab === 'all'): ?>
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
                    <?php endif; ?>
                    
                    <select id="statusFilter">
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
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

        <!-- User Tables -->
        <div class="user-table">
            <div class="table-section">
                <div class="table-scroll">
                    <!-- Students Table -->
                    <div class="tab-content <?php echo $current_tab === 'students' ? 'active' : ''; ?>" id="students-tab">
                        <table id="studentsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>RFID Tag</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Course Section</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentsTableBody">
                                <?php 
                                $student_sql = "SELECT users.*, course_section.CourseSection 
                                                FROM users 
                                                LEFT JOIN course_section ON users.courseSection_id = course_section.courseSection_id
                                                WHERE users.Role = 'Student'
                                                ORDER BY users.User_id";
                                $students = mysqli_query($conn, $student_sql);
                                ?>
                                <?php if(mysqli_num_rows($students) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($students)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['User_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Rfid_tag']); ?></td>
                                        <td><?php echo htmlspecialchars($row['F_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['L_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['CourseSection'] ?? 'N/A'); ?></td>
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
                                                <button class="btn-delete" onclick="openDeleteModal(<?php echo $row['User_id']; ?>, '<?php echo $row['F_name']; ?>', '<?php echo $row['L_name']; ?>', 'Student', '<?php echo $row['Status']; ?>')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="no-results">No students found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Faculty Table -->
                    <div class="tab-content <?php echo $current_tab === 'faculty' ? 'active' : ''; ?>" id="faculty-tab">
                        <table id="facultyTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>RFID Tag</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="facultyTableBody">
                                <?php 
                                $faculty_sql = "SELECT * FROM users WHERE Role = 'Faculty' ORDER BY User_id";
                                $faculty = mysqli_query($conn, $faculty_sql);
                                ?>
                                <?php if(mysqli_num_rows($faculty) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($faculty)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['User_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Rfid_tag']); ?></td>
                                        <td><?php echo htmlspecialchars($row['F_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['L_name']); ?></td>
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
                                                <button class="btn-delete" onclick="openDeleteModal(<?php echo $row['User_id']; ?>, '<?php echo $row['F_name']; ?>', '<?php echo $row['L_name']; ?>', 'Faculty', '<?php echo $row['Status']; ?>')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="no-results">No faculty members found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- All Users Table -->
                    <div class="tab-content <?php echo $current_tab === 'all' ? 'active' : ''; ?>" id="all-tab">
                        <table id="allUsersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>RFID Tag</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Course Section</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="allUsersTableBody">
                                <?php 
                                $all_sql = "SELECT users.*, course_section.CourseSection 
                                            FROM users 
                                            LEFT JOIN course_section ON users.courseSection_id = course_section.courseSection_id
                                            ORDER BY users.Role, users.User_id";
                                $all_users = mysqli_query($conn, $all_sql);
                                ?>
                                <?php if(mysqli_num_rows($all_users) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($all_users)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['User_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Rfid_tag']); ?></td>
                                        <td><?php echo htmlspecialchars($row['F_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['L_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['CourseSection'] ?? 'N/A'); ?></td>
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
                                                <button class="btn-delete" onclick="openDeleteModal(<?php echo $row['User_id']; ?>, '<?php echo $row['F_name']; ?>', '<?php echo $row['L_name']; ?>', '<?php echo $row['Role']; ?>', '<?php echo $row['Status']; ?>')">
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
                    <p><strong>Role:</strong> <span id="delete_user_role"></span></p>
                    <p><strong>Status:</strong> <span id="delete_user_status"></span></p>
                    <div id="facultyWarning" class="alert alert-warning" style="display: none;">
                        <strong>Warning:</strong> All schedules and related data assigned to this faculty member will be permanently deleted.
                    </div>
                    <div id="foreignKeyWarning" class="alert alert-warning" style="display: none;">
                        <strong>Note:</strong> This user's access logs will be preserved but disassociated from their account.
                    </div>
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
    <script>
    // Enhanced delete modal function with faculty restriction
    function openDeleteModal(userId, firstName, lastName, role, status) {
        // Check if it's a faculty member with active status
        if (role === 'Faculty' && status === 'Active') {
            alert('Cannot delete faculty member with Active status. Please set status to Inactive first.');
            return;
        }
        
        // If not faculty or faculty is inactive, proceed with deletion modal
        document.getElementById('delete_user_id').value = userId;
        document.getElementById('delete_user_name').textContent = firstName + ' ' + lastName;
        document.getElementById('delete_user_role').textContent = role;
        document.getElementById('delete_user_status').textContent = status;
        
        // Show appropriate warnings
        const facultyWarning = document.getElementById('facultyWarning');
        const foreignKeyWarning = document.getElementById('foreignKeyWarning');
        
        if (role === 'Faculty') {
            facultyWarning.style.display = 'block';
            foreignKeyWarning.style.display = 'block';
        } else {
            facultyWarning.style.display = 'none';
            foreignKeyWarning.style.display = 'block';
        }
        
        document.getElementById('deleteUserModal').style.display = 'block';
    }

    // Toggle course section based on role selection
    function toggleCourseSection() {
        const role = document.getElementById('role').value;
        const courseSectionGroup = document.getElementById('courseSectionGroup');
        
        if (role === 'Student') {
            courseSectionGroup.style.display = 'block';
        } else {
            courseSectionGroup.style.display = 'none';
        }
    }

    // Tab functionality
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-btn');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');
                window.location.href = `users.php?tab=${tabName}`;
            });
        });
        
        toggleCourseSection();
    });

    window.addEventListener("pageshow", function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
    </script>

</body>
</html>