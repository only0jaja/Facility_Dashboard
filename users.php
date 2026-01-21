<?php
include 'conn.php';
session_start();

// Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if user is logged in
if (!isset($_SESSION['id'])){
    header("Location: login.php");
    exit();
}

// Get current tab
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'students';

// Handle full user update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $f_name = trim($_POST['f_name']);
    $l_name = trim($_POST['l_name']);
    $rfid_tag = trim($_POST['rfid_tag']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $courseSection_id = isset($_POST['courseSection_id']) ? $_POST['courseSection_id'] : null;
    
    // For non-students, set course section to NULL
    if ($role !== 'Student') {
        $courseSection_id = null;
    }
    
    // Update the user
    if ($role === 'Student') {
        $update_sql = "UPDATE users SET Rfid_tag = ?, F_name = ?, L_name = ?, CourseSection_id = ?, Role = ?, Status = ? WHERE User_id = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "sssissi", $rfid_tag, $f_name, $l_name, $courseSection_id, $role, $status, $user_id);
    } else {
        $update_sql = "UPDATE users SET Rfid_tag = ?, F_name = ?, L_name = ?, CourseSection_id = NULL, Role = ?, Status = ? WHERE User_id = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "sssssi", $rfid_tag, $f_name, $l_name, $role, $status, $user_id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "User updated successfully!";
        header("Location: users.php?tab=$current_tab");
        exit();
    } else {
        $error_message = "Error updating user: " . mysqli_error($conn);
    }
}

// Get statistics
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
  <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
  <link rel="stylesheet" href="styles/users.css">
  <link rel="stylesheet" href="styles/sidebar.css">
</head>
<body>
   <div class="sidebar" id="sidebar">
        <img src="./img/loalogo.png" alt="Lyceum of Alabang Logo" style="width:120px; height:120px; border-radius:50%; object-fit: cover;margin-left: auto; margin-right: auto;">
        <h2 style="text-align: center; font-size: 20px;margin: 15px 0">Lyceum of Alabang</h2>
        
        <div class="icons">
            <a href="index.php"><i class='bx bxs-home'></i>Home</a>
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
                                    <th>Role</th>
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
                                        <td><?php echo $row['User_id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['Rfid_tag']); ?></td>
                                        <td><?php echo htmlspecialchars($row['F_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['L_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['CourseSection'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="role-student"><?php echo $row['Role']; ?></span>
                                        </td>
                                        <td>
                                            <span class="status-<?php echo strtolower($row['Status']); ?>">
                                                <?php echo $row['Status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-edit" onclick="editUser(
                                                    <?php echo $row['User_id']; ?>,
                                                    '<?php echo $row['Rfid_tag']; ?>',
                                                    '<?php echo $row['F_name']; ?>',
                                                    '<?php echo $row['L_name']; ?>',
                                                    '<?php echo $row['Role']; ?>',
                                                    '<?php echo $row['Status']; ?>',
                                                    '<?php echo $row['CourseSection_id'] ?? ''; ?>'
                                                )">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn-delete" onclick="deleteUser(<?php echo $row['User_id']; ?>, '<?php echo $row['F_name']; ?>', '<?php echo $row['L_name']; ?>')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="no-results">No students found</td>
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
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="facultyTableBody">
                                <?php 
                                $faculty_sql = "SELECT * FROM users WHERE Role IN ('Faculty', 'Admin') ORDER BY User_id";
                                $faculty = mysqli_query($conn, $faculty_sql);
                                ?>
                                <?php if(mysqli_num_rows($faculty) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($faculty)): ?>
                                    <tr>
                                        <td><?php echo $row['User_id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['Rfid_tag']); ?></td>
                                        <td><?php echo htmlspecialchars($row['F_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['L_name']); ?></td>
                                        <td>
                                            <span class="role-<?php echo strtolower($row['Role']); ?>">
                                                <?php echo $row['Role']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-<?php echo strtolower($row['Status']); ?>">
                                                <?php echo $row['Status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-edit" onclick="editUser(
                                                    <?php echo $row['User_id']; ?>,
                                                    '<?php echo $row['Rfid_tag']; ?>',
                                                    '<?php echo $row['F_name']; ?>',
                                                    '<?php echo $row['L_name']; ?>',
                                                    '<?php echo $row['Role']; ?>',
                                                    '<?php echo $row['Status']; ?>',
                                                    ''
                                                )">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn-delete" onclick="deleteUser(<?php echo $row['User_id']; ?>, '<?php echo $row['F_name']; ?>', '<?php echo $row['L_name']; ?>')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="no-results">No faculty members found</td>
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
                                        <td><?php echo $row['User_id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['Rfid_tag']); ?></td>
                                        <td><?php echo htmlspecialchars($row['F_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['L_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['CourseSection'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="role-<?php echo strtolower($row['Role']); ?>">
                                                <?php echo $row['Role']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-<?php echo strtolower($row['Status']); ?>">
                                                <?php echo $row['Status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-edit" onclick="editUser(
                                                    <?php echo $row['User_id']; ?>,
                                                    '<?php echo $row['Rfid_tag']; ?>',
                                                    '<?php echo $row['F_name']; ?>',
                                                    '<?php echo $row['L_name']; ?>',
                                                    '<?php echo $row['Role']; ?>',
                                                    '<?php echo $row['Status']; ?>',
                                                    '<?php echo $row['CourseSection_id'] ?? ''; ?>'
                                                )">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn-delete" onclick="deleteUser(<?php echo $row['User_id']; ?>, '<?php echo $row['F_name']; ?>', '<?php echo $row['L_name']; ?>')">
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

    <!-- Edit User Modal -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit User</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            
            <form method="POST" action="" id="editForm">
                <input type="hidden" id="edit_user_id" name="user_id">
                
                <div class="form-group">
                    <label for="edit_rfid_tag">RFID Tag <span class="required">*</span></label>
                    <input type="text" id="edit_rfid_tag" name="rfid_tag" required placeholder="Enter RFID tag">
                </div>
                
                <div class="form-group">
                    <label for="edit_f_name">First Name <span class="required">*</span></label>
                    <input type="text" id="edit_f_name" name="f_name" required placeholder="Enter first name">
                </div>
                
                <div class="form-group">
                    <label for="edit_l_name">Last Name <span class="required">*</span></label>
                    <input type="text" id="edit_l_name" name="l_name" required placeholder="Enter last name">
                </div>
                
                <div class="form-group">
                    <label for="edit_role">Role <span class="required">*</span></label>
                    <select id="edit_role" name="role" required onchange="toggleCourseSection()">
                        <option value="">Select Role</option>
                        <option value="Student">Student</option>
                        <option value="Faculty">Faculty</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                    
                <div class="form-group" id="courseSectionGroup" style="display: none;">
                    <label for="edit_courseSection_id">Course Section <span class="required">*</span></label>
                    <select id="edit_courseSection_id" name="courseSection_id">
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
                    <label for="edit_status">Status <span class="required">*</span></label>
                    <select id="edit_status" name="status" required>
                        <option value="">Select Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="update_user">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Simple edit function
        function editUser(userId, rfid, firstName, lastName, role, status, courseSectionId) {
            console.log("Editing user:", userId, rfid, firstName, lastName, role, status, courseSectionId);
            
            // Set form values
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_rfid_tag').value = rfid;
            document.getElementById('edit_f_name').value = firstName;
            document.getElementById('edit_l_name').value = lastName;
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_status').value = status;
            
            // Set course section
            if (courseSectionId && courseSectionId !== '') {
                document.getElementById('edit_courseSection_id').value = courseSectionId;
            }
            
            // Show/hide course section based on role
            toggleCourseSection();
            
            // Show modal
            document.getElementById('editModal').style.display = 'block';
        }
        
        function toggleCourseSection() {
            const role = document.getElementById('edit_role').value;
            const courseSectionGroup = document.getElementById('courseSectionGroup');
            
            if (role === 'Student') {
                courseSectionGroup.style.display = 'block';
            } else {
                courseSectionGroup.style.display = 'none';
            }
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        function deleteUser(userId, firstName, lastName) {
            if (confirm('Are you sure you want to delete ' + firstName + ' ' + lastName + '?')) {
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const userIdInput = document.createElement('input');
                userIdInput.type = 'hidden';
                userIdInput.name = 'user_id';
                userIdInput.value = userId;
                
                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'delete_user';
                deleteInput.value = '1';
                
                form.appendChild(userIdInput);
                form.appendChild(deleteInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
        
        // Simple search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchValue = this.value.toLowerCase();
            const activeTab = document.querySelector('.tab-content.active');
            const rows = activeTab.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });
    </script>
</body>
</html>