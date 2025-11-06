<?php
include '../conn.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $query = $conn->query("
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
        WHERE sch.Schedule_id = '$id'
    ");

    if ($query->num_rows > 0) {
        echo json_encode($query->fetch_assoc());
    } else {
        echo json_encode(["error" => "Schedule not found"]);
    }
}
?>
