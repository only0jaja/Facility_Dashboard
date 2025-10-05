<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>  
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../logs.css">
   
</head>
<body>
      <div class="sidebar" id="sidebar">
    <h1>Dashboard</h1>
    <a href="index.php" >🏠 Home</a>
    <a href="users.php">👥 Users</a>
    <a href="rooms.php">📁 Rooms</a>
    <a href="" class="active">📜 Access Logs</a>
    <a href="#">⚙️ Settings</a>
    <a href="logout.php">🚪 Log out</a>
    <div class="user">
      👤 <span>Juan<br><small>Faculty Member</small></span>
    </div>
  </div>
  <!-- access logs -->
 
    <div class="logs-content">
    <div class="table-section">
      <h3>Access logs</h3>
      <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>Log_id</th>
            <th>User_id</th>
            <th>Rfid_tag</th>
            <th>Schedule_id</th>
            <th>Access_time</th>
            <th>Access_type</th>
            <th>Status</th>
          </tr>
        </thead>
        
            <?php 
              include  'conn.php';
              $log_id = $conn->query("SELECT * FROM access_log ORDER BY Access_time DESC");
            ?>
        <tbody>
          <?php if ($log_id->num_rows > 0): ?>
            <?php while($row = $log_id->fetch_assoc()): ?>
              <tr>
                <td><?php echo $row['Log_id']; ?></td>
                <td><?php echo $row['User_id']; ?></td>
                <td><?php echo $row['Rfid_tag']; ?></td>
                <td><?php echo $row['Schedule_id']; ?></td>
                <td><?php echo $row['Access_time']; ?></td>
                <td><?php echo $row['Access_type']; ?></td>
                <td><?php echo $row['Status']; ?></td>
              </tr>
        <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" style="text-align: center;">No records found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>

   </div>
    </div>
  </div>
          </div>

<script>
function loadLogs() {
  fetch('fetch.php')
    .then(response => response.text())
    .then(html => {
      document.querySelector('tbody').innerHTML = html;
    });
}

// Load logs every 5 seconds
setInterval(loadLogs, 3000);
window.onload = loadLogs;
</script>
</body>
</html>