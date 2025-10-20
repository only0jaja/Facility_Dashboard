<?php
  include 'conn.php';
?>
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
    <link rel="stylesheet" href="styles/users.css">
</head>

<body>
  <div class="sidebar" id="sidebar">
      <h1>Dashboard</h1>
      <div class="icons">
          <a href="index.php"><i class='bx bxs-home'></i>Home</a>
          <a href="users.php" class="active"><i class='bx bxs-user-pin' ></i> Users</a>
          <a href="rooms.php"><i class='bx bx-folder-open'></i> Rooms</a>
          <a href="access_logs.php"><i class='bx bx-bookmark-alt-plus'></i> Access Logs</a>
          <a href="schedule.php"><i class='bx bx-calendar-week'></i> Schedule</a>
          <a href="logout.php"><i class='bx bxs-log-out'></i> Log out</a>
      </div>
      <div class="user">
          👤 <span>Juan<br><small>Faculty Member</small></span>
      </div>
  </div>
  <!-- user header -->
  <div class="header">
    <h1>Users</h1>
    <input type="search" placeholder="Search">
  </div>

  <!-- user table -->
  <div class="user-table">
    <div class="table-section">     
      <table>
        <thead>
          <tr>
            <th>User_id</th>
            <th>Rfid_tag</th>
            <th>f_name</th>
            <th>l_name</th>
            <th>CourseSection</th>
            <th>Role</th>
            <th>Status</th>
          </tr>
        </thead>
        <?php 
          $sql = 'select *, course_section.CourseSection from users JOIN course_section ON users.courseSection_id = course_section.courseSection_id';
          $users = mysqli_query($conn,$sql);
        ?>
        <tbody>
          <?php while($row = mysqli_fetch_assoc($users)):?>
            <tr>
              <td><?php echo $row['User_id'];?></td>
              <td><?php echo $row['Rfid_tag'];?></td>
              <td><?php echo $row['F_name'];?></td>
              <td><?php echo $row['L_name'];?></td>
              <td><?php echo $row['CourseSection'];?></td>
              <td><?php echo $row['Role'];?></td>
              <td><?php echo $row['Status'];?></td>
            </tr>
          <?php endwhile;?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>