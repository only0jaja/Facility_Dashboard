<?php
include 'conn.php';
  $log_id = $conn->query("
    SELECT 
      access_log.*,
      classrooms.Room_code
    FROM access_log
    JOIN classrooms ON access_log.Room_id = classrooms.Room_id
    ORDER BY access_log.Access_time DESC
    LIMIT 10
  ");
while($row = $log_id->fetch_assoc()): ?>
<tr>
  <td><?= $row['Log_id']; ?></td>
  <td><?= $row['User_id']; ?></td>
  <td><?= $row['Rfid_tag']; ?></td>
  <td><?= $row['Room_code']; ?></td>
  <td><?= $row['Access_time']; ?></td>
  <td><?= $row['Access_type']; ?></td>
  <td><?= $row['Status']; ?></td>
</tr>
<?php endwhile; ?>