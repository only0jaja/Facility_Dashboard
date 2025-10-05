<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles/users.css">
</head>

<body>
  <div class="sidebar" id="sidebar">
    <h1>Dashboard</h1>
    <a href="index.php">🏠 Home</a>
    <a href="" class="active">👥 Users</a>
    <a href="rooms.php">📁 Rooms</a>
    <a href="access_logs.php">📜 Access Logs</a>
    <a href="#">⚙️ Settings</a>
    <a href="logout.php">🚪 Log out</a>
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
            <th>Course</th>
            <th>Course</th>
            <th>Section</th>
            <th>Year</th>
            <th>Semester</th>
            <th>Role</th>
            <th>Status</th>
          </tr>
        </thead>
        <?php 
          include 'conn.php';
          
          $sql = 'select * from users';
          $users = mysqli_query($conn,$sql);
        ?>
        <tbody>
          <?php while($row = mysqli_fetch_assoc($users)):?>
          <tr>
           <td><?php echo $row['User_id'];?></td>
           <td><?php echo $row['Rfid_tag'];?></td>
           <td><?php echo $row['F_name'];?></td>
           <td><?php echo $row['L_name'];?></td>
           <td><?php echo $row['Course'];?></td>
           <td><?php echo $row['Section'];?></td>
           <td><?php echo $row['Year'];?></td>
           <td><?php echo $row['Semester'];?></td>
           <td><?php echo $row['Role'];?></td>
           <td><?php echo $row['Status'];?></td>
          </tr>
          <?php endwhile;?>
        </tbody>
      </table>
    </div>

         
        </tbody>
      </table>
    </div>
</body>

</html>