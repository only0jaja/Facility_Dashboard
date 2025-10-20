<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>  
    <!-- Font Style -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <!-- Icons Style 1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <!-- Icons Style 2 -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <!-- Page Style -->
    <link rel="stylesheet" href="styles/logs.css">
   
</head>
<body>
      <div class="sidebar" id="sidebar">
          <h1>Dashboard</h1>
          <div class="icons">
              <a href="index.php"><i class='bx bxs-home'></i>Home</a>
              <a href="users.php"><i class='bx bxs-user-pin' ></i> Users</a>
              <a href="rooms.php"><i class='bx bx-folder-open'></i> Rooms</a>
              <a href="access_logs.php" class="active"><i class='bx bx-bookmark-alt-plus'></i> Access Logs</a>
              <a href="schedule.php"><i class='bx bx-calendar-week'></i> Schedule</a>
              <a href="logout.php"><i class='bx bxs-log-out'></i> Log out</a>
          </div>
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
                  <th>Room</th>
                  <th>Access_time</th> 
                  <th>Access_type</th> 
                  <th>Status</th>
                </tr>
              </thead>
              
                  <?php 
                    include  'conn.php';
                    $log_id = $conn->query("
                      SELECT access_log.*, classrooms.Room_code 
                      FROM access_log 
                      LEFT JOIN classrooms ON access_log.Room_id = classrooms.Room_id 
                      ORDER BY Access_time DESC
                    ");
                  ?>
              <tbody>
                <?php if ($log_id->num_rows > 0): ?>
                  <?php while($row = $log_id->fetch_assoc()): ?>
                    <tr>
                      <td><?php echo $row['Log_id']; ?></td>
                      <td><?php echo $row['User_id']; ?></td>
                      <td><?php echo $row['Rfid_tag']; ?></td>
                      <td><?php echo $row['Room_id']; ?></td>
                      <td><?= $row['Access_time']; ?></td>
                      <td><?= $row['Access_type']; ?></td>
                      <td style="text-align: center;">
                        <span class="status <?php echo strtolower($row['Status']); ?>">
                          <?php echo $row['Status']; ?>
                        </span>
                      </td>
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