<?php
include 'conn.php';


// Fetch schedule data
$sql = "
SELECT 
    sub.Code, 
    sub.Description, 
    sch.Day, 
    sch.Start_time, 
    sch.End_time, 
    cr.Room_code, 
    u.F_name, 
    u.L_name
FROM schedule_access AS sa
JOIN schedule AS sch ON sa.Schedule_id = sch.Schedule_id
JOIN subject AS sub ON sch.Subject_id = sub.Subject_id
JOIN users AS u ON sch.Faculty_id = u.User_id
JOIN classrooms AS cr ON sch.Room_id = cr.Room_id
WHERE sa.CourseSection_id = '1'
ORDER BY sch.Day, sch.Start_time
";

$result = $conn->query($sql);

?>

<?php
include 'conn.php';

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
    <title>Document</title>
    <link rel="stylesheet" href="styles/schedule.css">
</head>

<body>
    <div class="sidebar" id="sidebar">
        <h1>Dashboard</h1>
        <a href="#" class="active">🏠 Home</a>
        <a href="users.php">👥 Users</a>
        <a href="rooms.php">📁 Rooms</a>
        <a href="access_logs.php">📜 Access Logs</a>
        <a href="schedule.php">⚙️ Schedule</a>
        <a href="logout.php">🚪 Log out</a>
        <div class="user">
            👤 <span>Juan<br><small>Faculty Member</small></span>
        </div>
    </div>

    <div class="schedule">
        <button class="btn-primary" onclick="openModal()">+ Add Schedule</button>
        <?php
        // Fetch all course sections that have schedule entries
        $courseSections = $conn->query("SELECT DISTINCT cs.CourseSection_id, cs.CourseSection 
                                FROM course_section cs
                                JOIN schedule_access sa ON cs.CourseSection_id = sa.CourseSection_id");

        if ($courseSections->num_rows > 0) {
            while ($cs = $courseSections->fetch_assoc()) {
                echo "<h2>Schedule for {$cs['CourseSection']}</h2>";
                echo "<table>";
                echo "<thead>
                <tr>
                    <th>CODE</th>
                    <th>COURSE DESCRIPTION</th>
                    <th>DAY</th>
                    <th>START TIME</th>
                    <th>END TIME</th>
                    <th>ROOM</th>
                    <th>FACULTY</th>
                </tr>
              </thead><tbody>";

                // Fetch schedule for this course section
                $sql = "
        SELECT 
            sub.Code, 
            sub.Description, 
            sch.Day, 
            sch.Start_time, 
            sch.End_time, 
            cr.Room_code, 
            CONCAT(u.F_name, ' ', u.L_name) AS Faculty
        FROM schedule_access sa
        JOIN schedule sch ON sa.Schedule_id = sch.Schedule_id
        JOIN subject sub ON sch.Subject_id = sub.Subject_id
        JOIN users u ON sch.Faculty_id = u.User_id
        JOIN classrooms cr ON sch.Room_id = cr.Room_id
        WHERE sa.CourseSection_id = '{$cs['CourseSection_id']}'
        ORDER BY sch.Day, sch.Start_time
        ";

                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                        <td>{$row['Code']}</td>
                        <td>{$row['Description']}</td>
                        <td>{$row['Day']}</td>
                        <td>{$row['Start_time']}</td>
                        <td>{$row['End_time']}</td>
                        <td>{$row['Room_code']}</td>
                        <td>{$row['Faculty']}</td>
                      </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No schedule found</td></tr>";
                }

                echo "</tbody></table><br>";
            }
        } else {
            echo "No course sections found.";
        }
        ?>

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
                        <option value="Sun">Sunday</option>
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

            // Form submission handling
            document.getElementById('addScheduleForm').addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Schedule added successfully!');
                closeModal();
            });
        </script>
</body>

</html>