<?php
include 'conn.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if (isset($_GET['schedule_id'])) {
    $scheduleId = $_GET['schedule_id'];
    
    // Sanitize input
    $scheduleId = $conn->real_escape_string($scheduleId);
    
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
        WHERE sch.Schedule_id = '$scheduleId'
    ");
    
    if ($query && $query->num_rows > 0) {
        $data = $query->fetch_assoc();
        echo json_encode([
            'success' => true,
            'schedule_id' => $data['Schedule_id'],
            'subject_id' => $data['Subject_id'],
            'code' => $data['Code'],
            'description' => $data['Description'],
            'faculty_id' => $data['Faculty_id'],
            'room_id' => $data['Room_id'],
            'day' => $data['Day'],
            'start_time' => $data['Start_time'],
            'end_time' => $data['End_time'],
            'course_section_id' => $data['CourseSection_id']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Schedule not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No schedule ID provided']);
}
?>