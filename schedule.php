<?php
include 'conn.php';

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
    cs.CourseSection_id
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
</head>

<body>
    <div class="sidebar" id="sidebar">
        <h1>Dashboard</h1>
        <div class="icons">
        <a href="index.php" ><i class='bx bxs-home'></i>Home</a>
        <a href="users.php"><i class='bx bxs-user-pin' ></i> Users</a>
        <a href="rooms.php"><i class='bx bx-folder-open'></i> Rooms</a>
        <a href="access_logs.php"><i class='bx bx-bookmark-alt-plus'></i> Access Logs</a>
        <a href="schedule.php" class="active"><i class='bx bx-calendar-week'></i> Schedule</a>
        <a href="logout.php"><i class='bx bxs-log-out'></i> Log out</a>
        </div>
        <div class="user">
            👤 <span>Juan<br><small>Faculty Member</small></span>
        </div>
    </div>
    
    <div class="main-content">
          <div class="controls-section">
            <div class="modal-btn">
                <button class="btn-primary" onclick="openModal()">+ Add Schedule</button>
            </div>
             <div class="search-box">
                        <input type="text" name="search" placeholder="Search" value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit"><i class='bx bx-search'></i></button>
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
                    echo "<h2 style='padding-top: 25px'>Schedule for {$data['name']}</h2>";
                    echo "<table>";
                    echo "<thead>
                            <tr>
                                <th>CODE</th>
                                <th style='width: 300px'>COURSE DESCRIPTION</th>
                                <th>DAY</th>
                                <th>START TIME</th>
                                <th>END TIME</th>
                                <th>ROOM</th>
                                <th>FACULTY</th>
                            </tr>
                        </thead><tbody>";

                    foreach ($data['schedules'] as $row) {
                        echo "<tr>
                                <td>{$row['Code']}</td>
                                <td>{$row['Description']}</td>
                                <td>{$row['Day']}</td>
                                <td>{$row['Start_time']}</td>
                                <td>{$row['End_time']}</td>
                                <td>{$row['Room_code']}</td>
                                <td>{$row['F_name']} {$row['L_name']}</td>
                            </tr>";
                    }

                    echo "</tbody></table><br>";
                }
            } else {
                echo "<div class='no-results'>No schedule found matching your criteria.</div>";
            }
            ?>
        </div>
    </div>

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
                            // Get faculty members from users table
                                $facultyQuery = $conn->query("SELECT User_id, F_name, L_name FROM users WHERE Role = 'Faculty'");
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
                            // Get rooms from classrooms table
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
                            // Get course sections
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

    <script>
        // Modal functions
        function openModal() {
            document.getElementById('addScheduleModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('addScheduleModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addScheduleModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        
        function clearFilters() {
            window.location.href = 'schedule.php';
        }
        // Add this script if you keep separate forms
document.querySelector('.search-box button').addEventListener('click', function() {
    const searchValue = document.querySelector('input[name="search"]').value;
    const filterForm = document.querySelector('.filter-form');
    
    // Add search input to filter form
    let searchInput = filterForm.querySelector('input[name="search"]');
    if (!searchInput) {
        searchInput = document.createElement('input');
        searchInput.type = 'hidden';
        searchInput.name = 'search';
        filterForm.appendChild(searchInput);
    }
    searchInput.value = searchValue;
    
    // Submit the filter form
    filterForm.submit();
});
    </script>
</body>

</html>