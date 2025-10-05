<?php
include 'conn.php';
$log_id = $conn->query("SELECT * FROM access_log ORDER BY Access_time DESC LIMIT 10");
while($row = $log_id->fetch_assoc()): ?>
<tr>
  <td><?= $row['Log_id']; ?></td>
  <td><?= $row['User_id']; ?></td>
  <td><?= $row['Rfid_tag']; ?></td>
  <td><?= $row['Schedule_id']; ?></td>
  <td><?= $row['Access_time']; ?></td>
  <td><?= $row['Access_type']; ?></td>
  <td><?= $row['Status']; ?></td>
</tr>
<?php endwhile; ?>