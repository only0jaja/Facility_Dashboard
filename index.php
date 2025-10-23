<?php
include 'conn.php';
session_start();

$user = $_SESSION['id'];
if (!isset($user)) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Room Dashboard</title>
    <!-- Font Style -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <!-- Icons Style 1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <!-- Icons Style 2 -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <!-- Page Style -->
    <link rel="stylesheet" href="styles/index.css">
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
      <h1>Dashboard</h1>
      <div class="icons">
          <a href="index.php" class="active"><i class='bx bxs-home'></i>Home</a>
          <a href="users.php"><i class='bx bxs-user-pin' ></i> Users</a>
          <a href="rooms.php"><i class='bx bx-folder-open'></i> Rooms</a>
          <a href="access_logs.php"><i class='bx bx-bookmark-alt-plus'></i> Access Logs</a>
          <a href="schedule.php"><i class='bx bx-calendar-week'></i> Schedule</a>
          <a href="logout.php"><i class='bx bxs-log-out'></i> Log out</a>
      </div>
      <div class="user">
          👤 <span>Juan<br><small>Faculty Member</small></span>
      </div>
  </div>
  
  <!-- Main Content -->
  <div class="main">
    <div class="header">
      <h2>Hello Admin!!</h2>
      <input type="search" placeholder="Search..." />
    </div>
     <?php    
      $totalroom = $conn->query("SELECT COUNT(*) AS total FROM classrooms")->fetch_assoc();
      $totaloccupied = $conn->query("SELECT COUNT(*) AS total FROM classrooms WHERE status = 'occupied'")->fetch_assoc();
      $totalUnoccupied = $conn->query("SELECT COUNT(*) AS total FROM classrooms WHERE status = 'Unoccupied'")->fetch_assoc();
      $totalusers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc();    
     ?>
    <div class="cards">
      <div class="card">
        <h3 id="totalroom"><?php echo $totalroom['total']; ?></h3>
        <p>Total Rooms</p>
      </div>
      <div class="card">
        <h3 id="totalusers"><?php echo $totalusers['total']; ?></h3>
        <p>Total Users</p>
      </div>
      <div class="card">
        <h3 id="totaloccupied"><?php echo $totaloccupied['total']; ?></h3>
        <p>Occupied Rooms</p>
      </div>
      <div class="card">
        <h3 id="totalUnoccupied"><?php echo $totalUnoccupied['total']; ?></h3>
        <p>Unoccupied Rooms</p>
      </div>
    </div>

    <div class="table-section">
      <h3>Recent Access</h3>
      <table>
        <thead>
          <tr>
            <th>Log_id</th>
            <th>User_id</th>
            <th>Rfid_tag</th>
            <th>Room_Code</th>
            <th>Access_time</th>
            <th>Access_type</th>
            <th>Status</th>
          </tr>
        </thead>
        
            <?php 
                $log_id = $conn->query("
                  SELECT 
                    access_log.*,
                    classrooms.Room_code
                  FROM access_log
                  JOIN classrooms ON access_log.Room_id = classrooms.Room_id
                  ORDER BY access_log.Access_time DESC
                  LIMIT 10
                ");
            ?>
        <tbody>
          <?php if ($log_id->num_rows > 0): ?>
            <?php while($row = $log_id->fetch_assoc()): ?>
              <tr>
                <td><?php echo $row['Log_id']; ?></td>
                <td><?php echo $row['User_id']; ?></td>
                <td><?php echo $row['Rfid_tag']; ?></td>
                <td><?php echo $row['Room_code']; ?></td>
                <td><?php echo $row['Access_time']; ?></td>
                <td><?php echo $row['Access_type']; ?></td>
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

  <script>
function loadLogs() {
  fetch('fetch.php')
    .then(response => response.text())
    .then(html => {
      document.querySelector('tbody').innerHTML = html;
    });
}
function refreshDashboard() {
  fetch('fetch_dashboard.php') // or 'fetch_dashboard.php' depending on filename
    .then(response => response.json())
    .then(data => {
      document.getElementById('totalroom').textContent = data.totalroom;
      document.getElementById('totalusers').textContent = data.totalusers;
      document.getElementById('totaloccupied').textContent = data.totaloccupied;
      document.getElementById('totalUnoccupied').textContent = data.totalUnoccupied;
    })
    .catch(error => console.error('Error refreshing dashboard:', error));
}

setInterval(loadLogs, 3000);
window.onload = loadLogs;
setInterval(refreshDashboard, 3000);
refreshDashboard();
</script>
</body>
</html>
