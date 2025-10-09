<?php
include "conn.php";

$sql = "SELECT * FROM classrooms";
$rooms = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($rooms)):
  $status = strtolower($row['Status']);
  $statusClass = ($status == 'occupied') ? 'occupied' : 'available';
?>
  <div class="room-card <?php echo $statusClass; ?>">
    <div class="room-header">
      <h4><?php echo $row['Room_code']; ?></h4>
      <span class="status <?php echo $statusClass; ?>"><?php echo $row['Status']; ?></span>
    </div>
    <p>👥 Capacity: <?php echo $row['Capacity']; ?></p>
    <p>🏫 Type: <?php echo $row['Classroom_type']; ?></p>
    <div class="card-actions">
      <button class="edit">Edit</button>
    </div>
  </div>
<?php endwhile; ?>
