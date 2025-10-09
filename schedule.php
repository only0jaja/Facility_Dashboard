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

    </div>
</body>

</html>