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

// Fetch schedule data with filters
$filter_course_section = isset($_GET['course_section']) ? $_GET['course_section'] : '';
$filter_day = isset($_GET['day']) ? $_GET['day'] : '';
$filter_faculty = isset($_GET['faculty']) ? $_GET['faculty'] : '';
$filter_room = isset($_GET['room']) ? $_GET['room'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build the base query
$sql = "
SELECT 
    sub.Code, 
    sub.Description, 
    sch.Day, 
    sch.Start_time, 
    sch.End_time, 
    cr.Room_code, 
    u.F_name, 
    u.L_name,
    cs.CourseSection,
    cs.CourseSection_id,
    sch.Schedule_id,
    sub.Subject_id,
    sch.Faculty_id,
    sch.Room_id
FROM schedule_access AS sa
JOIN schedule AS sch ON sa.Schedule_id = sch.Schedule_id
JOIN subject AS sub ON sch.Subject_id = sub.Subject_id
JOIN users AS u ON sch.Faculty_id = u.User_id
JOIN classrooms AS cr ON sch.Room_id = cr.Room_id
JOIN course_section AS cs ON sa.CourseSection_id = cs.CourseSection_id
WHERE 1=1
";

// Add filters to the query
if (!empty($filter_course_section)) {
    $sql .= " AND sa.CourseSection_id = '$filter_course_section'";
}

if (!empty($filter_day)) {
    $sql .= " AND sch.Day = '$filter_day'";
}

if (!empty($filter_faculty)) {
    $sql .= " AND sch.Faculty_id = '$filter_faculty'";
}

if (!empty($filter_room)) {
    $sql .= " AND sch.Room_id = '$filter_room'";
}

if (!empty($search)) {
    $sql .= " AND (sub.Code LIKE '%$search%' OR sub.Description LIKE '%$search%' OR u.F_name LIKE '%$search%' OR u.L_name LIKE '%$search%')";
}

$sql .= " ORDER BY cs.CourseSection, sch.Day, sch.Start_time";

$result = $conn->query($sql);

// Add Schedule
if (isset($_POST['addSchedule'])) {
    // Get form data
    $subjectCode = $_POST['subjectCode'];
    $subjectDescription = $_POST['subjectDescription'];
    $facultyId = $_POST['faculty'];
    $roomId = $_POST['room'];
    $day = $_POST['day'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];
    $courseSectionId = $_POST['courseSection'];
    
    // Check for potential conflicts (same room, same time, same day)
    $conflictCheck = $conn->query("
        SELECT sch.Schedule_id 
        FROM schedule AS sch
        JOIN schedule_access AS sa ON sch.Schedule_id = sa.Schedule_id
        WHERE sch.Room_id = '$roomId' 
        AND sch.Day = '$day'
        AND sa.CourseSection_id = '$courseSectionId'
        AND (
            (sch.Start_time <= '$startTime' AND sch.End_time > '$startTime') OR
            (sch.Start_time < '$endTime' AND sch.End_time >= '$endTime') OR
            (sch.Start_time >= '$startTime' AND sch.End_time <= '$endTime')
        )
    ");
    
    if ($conflictCheck->num_rows > 0) {
        echo "<script>alert('Error: Schedule conflict detected! There is already a schedule for this room and course section at the same time.');</script>";
    } else {
        // Step 1: Insert into subject table
        $sql1 = "INSERT INTO subject (Code, Description) VALUES ('$subjectCode', '$subjectDescription')";
        
        if ($conn->query($sql1)) {
            $subjectId = $conn->insert_id;
            
            // Step 2: Insert into schedule table
            $sql2 = "INSERT INTO schedule (Subject_id, Faculty_id, Room_id, Day, Start_time, End_time) 
                    VALUES ('$subjectId', '$facultyId', '$roomId', '$day', '$startTime', '$endTime')";
            
            if ($conn->query($sql2)) {
                $scheduleId = $conn->insert_id;
                
                // Step 3: Insert into schedule_access table
                $sql3 = "INSERT INTO schedule_access (Schedule_id, CourseSection_id) 
                        VALUES ('$scheduleId', '$courseSectionId')";
                
                if ($conn->query($sql3)) {
                    echo "<script>alert('Schedule added successfully!'); window.location.href='schedule.php';</script>";
                } else {
                    echo "Error adding schedule access: " . $conn->error;
                }
            } else {
                echo "Error adding schedule: " . $conn->error;
            }
        } else {
            echo "Error adding subject: " . $conn->error;
        }
    }
}

// Update Schedule
if (isset($_POST['updateSchedule'])) {
    // Get form data
    $scheduleId = $_POST['schedule_id'];
    $subjectId = $_POST['subject_id'];
    $subjectCode = $_POST['subjectCode'];
    $subjectDescription = $_POST['subjectDescription'];
    $facultyId = $_POST['faculty'];
    $roomId = $_POST['room'];
    $day = $_POST['day'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];
    $courseSectionId = $_POST['courseSection'];
    
    // Check for conflicts (excluding current schedule)
    $conflictCheck = $conn->query("
        SELECT sch.Schedule_id 
        FROM schedule AS sch
        JOIN schedule_access AS sa ON sch.Schedule_id = sa.Schedule_id
        WHERE sch.Room_id = '$roomId' 
        AND sch.Day = '$day'
        AND sa.CourseSection_id = '$courseSectionId'
        AND sch.Schedule_id != '$scheduleId'
        AND (
            (sch.Start_time <= '$startTime' AND sch.End_time > '$startTime') OR
            (sch.Start_time < '$endTime' AND sch.End_time >= '$endTime') OR
            (sch.Start_time >= '$startTime' AND sch.End_time <= '$endTime')
        )
    ");
    
    if ($conflictCheck->num_rows > 0) {
        echo "<script>alert('Error: Schedule conflict detected! There is already a schedule for this room and course section at the same time.');</script>";
    } else {
        // Update subject table
        $sql1 = "UPDATE subject SET Code = '$subjectCode', Description = '$subjectDescription' WHERE Subject_id = '$subjectId'";
        
        if ($conn->query($sql1)) {
            // Update schedule table
            $sql2 = "UPDATE schedule SET Faculty_id = '$facultyId', Room_id = '$roomId', Day = '$day', 
                     Start_time = '$startTime', End_time = '$endTime' WHERE Schedule_id = '$scheduleId'";
            
            if ($conn->query($sql2)) {
                // Update schedule_access table
                $sql3 = "UPDATE schedule_access SET CourseSection_id = '$courseSectionId' WHERE Schedule_id = '$scheduleId'";
                
                if ($conn->query($sql3)) {
                    echo "<script>alert('Schedule updated successfully!'); window.location.href='schedule.php';</script>";
                } else {
                    echo "Error updating schedule access: " . $conn->error;
                }
            } else {
                echo "Error updating schedule: " . $conn->error;
            }
        } else {
            echo "Error updating subject: " . $conn->error;
        }
    }
}

// Delete Schedule
if (isset($_GET['delete_id'])) {
    $scheduleId = $_GET['delete_id'];
    
    // First get the subject_id to delete from subject table
    $getSubject = $conn->query("SELECT Subject_id FROM schedule WHERE Schedule_id = '$scheduleId'");
    if ($getSubject->num_rows > 0) {
        $subjectData = $getSubject->fetch_assoc();
        $subjectId = $subjectData['Subject_id'];
        
        // Delete from schedule_access first (foreign key constraint)
        $conn->query("DELETE FROM schedule_access WHERE Schedule_id = '$scheduleId'");
        
        // Delete from schedule
        if ($conn->query("DELETE FROM schedule WHERE Schedule_id = '$scheduleId'")) {
            // Delete from subject
            $conn->query("DELETE FROM subject WHERE Subject_id = '$subjectId'");
            
            echo "<script>alert('Schedule deleted successfully!'); window.location.href='schedule.php';</script>";
        } else {
            echo "Error deleting schedule: " . $conn->error;
        }
    }
}

// Get schedule data for editing
$editData = null;
if (isset($_GET['edit_id'])) {
    $editId = $_GET['edit_id'];
    $editQuery = $conn->query("
        SELECT 
            sch.Schedule_id,
            sub.Subject_id,
            sub.Code,
            sub.Description,
            sch.Faculty_id,
            sch.Room_id,
            sch.Day,
            sch.Start_time,
            sch.End_time,
            sa.CourseSection_id
        FROM schedule AS sch
        JOIN subject AS sub ON sch.Subject_id = sub.Subject_id
        JOIN schedule_access AS sa ON sch.Schedule_id = sa.Schedule_id
        WHERE sch.Schedule_id = '$editId'
    ");
    
    if ($editQuery->num_rows > 0) {
        $editData = $editQuery->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Management</title>
    <!-- Font Style -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <!-- Icons Style 1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <!-- Icons Style 2 -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <!-- Page Style -->
    <link rel="stylesheet" href="styles/schedule.css">
    <!-- Sidebar Css -->
    <link rel="stylesheet" href="styles/sidebar.css">
</head>

<body>
   <div class="sidebar" id="sidebar">
        <img src="./img/loalogo.png" alt="Lyceum of Alabang Logo" style="width:120px; height:120px; border-radius:50%; object-fit: cover;margin-left: auto; margin-right: auto;">
        <h2 style="text-align: center; font-size: 20px;margin: 15px 0">Lyceum of Alabang</h2>
        
        <div class="icons">
            <a href="index.php" ><i class='bx bxs-home'></i>Home</a>
            <a href="users.php"><i class='bx bxs-user-pin' ></i> Users</a>
            <a href="rooms.php"><i class='bx bx-folder-open'></i> Rooms</a>
            <a href="access_logs.php"><i class='bx bx-bookmark-alt-plus'></i> Access Logs</a>
            <a href="schedule.php"class="active"><i class='bx bx-calendar-week'></i> Schedule</a>
            <a href="logout.php"><i class='bx bxs-log-out'></i> Log out</a>
        </div>
        <div class="user">
            👤 <span>Juan<br><small>Faculty Member</small></span>
        </div>
    </div>

    <div class="main-content">
        <div class="controls-section">
            <h1>Schedule</h1>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search by Code, Description, Faculty...">
            </div>
        </div>
        <div class="controls">    
            <div class="filters">
                <form method="GET" class="filter-form">                   
                    <div class="filter-section">
                    <div class="filter-group">
                        <select name="course_section" onchange="this.form.submit()">
                            <option value="">All Course Sections</option>
                            <?php
                            $csQuery = $conn->query("SELECT CourseSection_id, CourseSection FROM course_section");
                            while ($cs = $csQuery->fetch_assoc()) {
                                $selected = ($filter_course_section == $cs['CourseSection_id']) ? 'selected' : '';
                                echo "<option value='{$cs['CourseSection_id']}' $selected>{$cs['CourseSection']}</option>";
                            }
                            ?>
                        </select>
                        
                        <select name="day" onchange="this.form.submit()">
                            <option value="">All Days</option>
                            <option value="Mon" <?php echo ($filter_day == 'Mon') ? 'selected' : ''; ?>>Monday</option>
                            <option value="Tue" <?php echo ($filter_day == 'Tue') ? 'selected' : ''; ?>>Tuesday</option>
                            <option value="Wed" <?php echo ($filter_day == 'Wed') ? 'selected' : ''; ?>>Wednesday</option>
                            <option value="Thu" <?php echo ($filter_day == 'Thu') ? 'selected' : ''; ?>>Thursday</option>
                            <option value="Fri" <?php echo ($filter_day == 'Fri') ? 'selected' : ''; ?>>Friday</option>
                            <option value="Sat" <?php echo ($filter_day == 'Sat') ? 'selected' : ''; ?>>Saturday</option>
                        </select>
                        
                        <select name="faculty" onchange="this.form.submit()">
                            <option value="">All Faculty</option>
                            <?php
                            $facultyQuery = $conn->query("SELECT User_id, F_name, L_name FROM users WHERE Role = 'Faculty'");
                            while ($faculty = $facultyQuery->fetch_assoc()) {
                                $selected = ($filter_faculty == $faculty['User_id']) ? 'selected' : '';
                                echo "<option value='{$faculty['User_id']}' $selected>{$faculty['F_name']} {$faculty['L_name']}</option>";
                            }
                            ?>
                        </select>
                        
                        <select name="room" onchange="this.form.submit()">
                            <option value="">All Rooms</option>
                            <?php
                            $roomQuery = $conn->query("SELECT Room_id, Room_code FROM classrooms");
                            while ($room = $roomQuery->fetch_assoc()) {
                                $selected = ($filter_room == $room['Room_id']) ? 'selected' : '';
                                echo "<option value='{$room['Room_id']}' $selected>{$room['Room_code']}</option>";
                            }
                            ?>
                        </select>
                        
                        <button type="button" class="btn-clear" onclick="clearFilters()">Clear Filters</button>
                        <div class="modal-btn">
                            <button type="button" class="btn-primary" onclick="openModal()">+ Add Schedule</button>
                        </div>
                    </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="schedule">
            <?php
            // Group results by course section
            $groupedSchedules = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $courseSectionId = $row['CourseSection_id'];
                    if (!isset($groupedSchedules[$courseSectionId])) {
                        $groupedSchedules[$courseSectionId] = [
                            'name' => $row['CourseSection'],
                            'schedules' => []
                        ];
                    }
                    $groupedSchedules[$courseSectionId]['schedules'][] = $row;
                }
                
                // Display schedules grouped by course section
                foreach ($groupedSchedules as $courseSectionId => $data) {
                    echo "<div class='course-section-group'>";
                    echo "<h2 style='padding-top: 25px'>Schedule for {$data['name']}</h2>";
                    echo "<table>";
                    echo "<thead>
                            <tr>
                                <th>CODE</th>
                                <th style='width: 250px'>COURSE DESCRIPTION</th>
                                <th>DAY</th>
                                <th>START TIME</th>
                                <th>END TIME</th>
                                <th>ROOM</th>
                                <th style='width: 150px'>FACULTY</th>
                                <th style='width: 200px'>ACTIONS</th>
                            </tr>
                        </thead><tbody>";

                    foreach ($data['schedules'] as $row) {
                        echo "<tr>
                                <td>{$row['Code']}</td>
                                <td>{$row['Description']}</td>
                                <td>{$row['Day']}</td>
                                <td style='width: 160px'>{$row['Start_time']}</td>
                                <td style='width: 160px'>{$row['End_time']}</td>
                                <td>{$row['Room_code']}</td>
                                <td>{$row['F_name']} {$row['L_name']}</td>
                                <td class='action-buttons'>
                                    <button class='btn-edit' onclick='openEditModal({$row['Schedule_id']})'>
                                        <i class='fas fa-edit'></i> Edit
                                    </button>
                                    <button class='btn-delete' onclick='confirmDelete({$row['Schedule_id']})'>
                                        <i class='fas fa-trash'></i> Delete
                                    </button>
                                </td>
                            </tr>";
                    }

                    echo "</tbody></table></div><br>";
                }
            } else {
                echo "<div class='no-results'>No schedule found matching your criteria.</div>";
            }
            ?>
        </div>
    </div>

    <!-- Add Schedule Modal -->
    <div id="addScheduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Schedule</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>

            <div class="modal-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="subjectCode">Subject Code</label>
                        <input type="text" id="subjectCode" name="subjectCode" placeholder="Enter subject code" required>
                    </div>

                    <div class="form-group">
                        <label for="subjectDescription">Subject Description</label>
                        <input type="text" id="subjectDescription" name="subjectDescription" placeholder="Enter subject description" required>
                    </div>
                       
                    <div class="form-group">
                        <label for="faculty">Faculty</label>
                        <select id="faculty" name="faculty" required>
                            <option value="">Select Faculty</option>
                            <?php
                            $facultyQuery = $conn->query("SELECT * from users WHERE Role = 'Faculty' AND Status = 'Active'");
                            while ($faculty = $facultyQuery->fetch_assoc()) {
                                echo "<option value='{$faculty['User_id']}'>{$faculty['F_name']} {$faculty['L_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="room">Room</label>
                        <select id="room" name="room" required>
                            <option value="">Select Room</option>
                            <?php
                            $roomQuery = $conn->query("SELECT Room_id, Room_code FROM classrooms");
                            while ($room = $roomQuery->fetch_assoc()) {
                                echo "<option value='{$room['Room_id']}'>{$room['Room_code']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="day">Day</label>
                        <select id="day" name="day" required>
                            <option value="">Select Day</option>
                            <option value="Mon">Monday</option>
                            <option value="Tue">Tuesday</option>
                            <option value="Wed">Wednesday</option>
                            <option value="Thu">Thursday</option>
                            <option value="Fri">Friday</option>
                            <option value="Sat">Saturday</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="startTime">Start Time</label>
                        <input type="time" id="startTime" name="startTime" required>
                    </div>

                    <div class="form-group">
                        <label for="endTime">End Time</label>
                        <input type="time" id="endTime" name="endTime" required>
                    </div>

                    <div class="form-group">
                        <label for="courseSection">Course Section</label>
                        <select id="courseSection" name="courseSection" required>
                            <option value="">Select Course Section</option>
                            <?php
                            $csQuery = $conn->query("SELECT CourseSection_id, CourseSection FROM course_section");
                            while ($cs = $csQuery->fetch_assoc()) {
                                echo "<option value='{$cs['CourseSection_id']}'>{$cs['CourseSection']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary btn-submit" name="addSchedule">Add Schedule</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Schedule Modal -->
    <div id="editScheduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Schedule</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>

            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" id="edit_schedule_id" name="schedule_id">
                    <input type="hidden" id="edit_subject_id" name="subject_id">
                    
                    <div class="form-group">
                        <label for="edit_subjectCode">Subject Code</label>
                        <input type="text" id="edit_subjectCode" name="subjectCode" placeholder="Enter subject code" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_subjectDescription">Subject Description</label>
                        <input type="text" id="edit_subjectDescription" name="subjectDescription" placeholder="Enter subject description" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_faculty">Faculty</label>
                        <select id="edit_faculty" name="faculty" required>
                            <option value="">Select Faculty</option>
                            <?php
                            $facultyQuery = $conn->query("SELECT User_id, F_name, L_name FROM users WHERE Role = 'Faculty'");
                            while ($faculty = $facultyQuery->fetch_assoc()) {
                                echo "<option value='{$faculty['User_id']}'>{$faculty['F_name']} {$faculty['L_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_room">Room</label>
                        <select id="edit_room" name="room" required>
                            <option value="">Select Room</option>
                            <?php
                            $roomQuery = $conn->query("SELECT Room_id, Room_code FROM classrooms");
                            while ($room = $roomQuery->fetch_assoc()) {
                                echo "<option value='{$room['Room_id']}'>{$room['Room_code']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_day">Day</label>
                        <select id="edit_day" name="day" required>
                            <option value="">Select Day</option>
                            <option value="Mon">Monday</option>
                            <option value="Tue">Tuesday</option>
                            <option value="Wed">Wednesday</option>
                            <option value="Thu">Thursday</option>
                            <option value="Fri">Friday</option>
                            <option value="Sat">Saturday</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_startTime">Start Time</label>
                        <input type="time" id="edit_startTime" name="startTime" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_endTime">End Time</label>
                        <input type="time" id="edit_endTime" name="endTime" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_courseSection">Course Section</label>
                        <select id="edit_courseSection" name="courseSection" required>
                            <option value="">Select Course Section</option>
                            <?php
                            $csQuery = $conn->query("SELECT CourseSection_id, CourseSection FROM course_section");
                            while ($cs = $csQuery->fetch_assoc()) {
                                echo "<option value='{$cs['CourseSection_id']}'>{$cs['CourseSection']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary btn-submit" name="updateSchedule">Update Schedule</button>
                </form>
            </div>
        </div>
    </div>

<script>
// Modal functions
function openModal() {
    const modal = document.getElementById('addScheduleModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal() {
    const modal = document.getElementById('addScheduleModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function openEditModal(scheduleId) {
    // Fetch schedule data via AJAX
    fetch(`get_schedule_data.php?schedule_id=${scheduleId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Populate the edit form with data
                document.getElementById('edit_schedule_id').value = data.schedule_id;
                document.getElementById('edit_subject_id').value = data.subject_id;
                document.getElementById('edit_subjectCode').value = data.code;
                document.getElementById('edit_subjectDescription').value = data.description;
                document.getElementById('edit_faculty').value = data.faculty_id;
                document.getElementById('edit_room').value = data.room_id;
                document.getElementById('edit_day').value = data.day;
                document.getElementById('edit_startTime').value = data.start_time;
                document.getElementById('edit_endTime').value = data.end_time;
                document.getElementById('edit_courseSection').value = data.course_section_id;
                
                // Show the edit modal
                const modal = document.getElementById('editScheduleModal');
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            } else {
                alert('Error loading schedule data: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading schedule data. Please check if get_schedule_data.php exists.');
        });
}

function closeEditModal() {
    const modal = document.getElementById('editScheduleModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function confirmDelete(scheduleId) {
    if (confirm('Are you sure you want to delete this schedule?')) {
        window.location.href = `schedule.php?delete_id=${scheduleId}`;
    }
}

// Close modals when clicking outside
window.onclick = function(event) {
    const addModal = document.getElementById('addScheduleModal');
    const editModal = document.getElementById('editScheduleModal');
    
    if (event.target === addModal) {
        closeModal();
    }
    if (event.target === editModal) {
        closeEditModal();
    }
}

function clearFilters() {
    window.location.href = 'schedule.php';
}

// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const searchValue = this.value.toLowerCase();
    const courseGroups = document.querySelectorAll('.course-section-group');
    let anyGroupVisible = false;

    courseGroups.forEach(group => {
        const table = group.querySelector('table');
        const rows = table.querySelectorAll('tbody tr');
        let hasVisibleRow = false;

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let matchFound = false;

            cells.forEach(cell => {
                if (cell.textContent.toLowerCase().includes(searchValue)) {
                    matchFound = true;
                }
            });

            row.style.display = matchFound || searchValue === '' ? '' : 'none';
            if (matchFound) hasVisibleRow = true;
        });

        // Show/hide the entire group
        if (hasVisibleRow || searchValue === '') {
            group.style.display = '';
            anyGroupVisible = true;
        } else {
            group.style.display = 'none';
        }
    });

    // Show message if no groups are visible
    let noResultsMsg = document.querySelector('.no-results-search');
    
    if (!anyGroupVisible && searchValue !== '') {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-results-search';
            noResultsMsg.textContent = 'No schedule found matching your search.';
            document.querySelector('.schedule').appendChild(noResultsMsg);
        }
    } else if (noResultsMsg) {
        noResultsMsg.remove();
    }
});

window.addEventListener("pageshow", function (event) {
  if (event.persisted) {
    window.location.reload();
  }
});
</script>

</body>
</html>