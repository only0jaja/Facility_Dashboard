<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
     <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../rooms.css">
</head>
<body>
     <div class="sidebar" id="sidebar">
    <h1>Dashboard</h1>
    <a href="index.php" >🏠 Home</a>
    <a href="users.php">👥 Users</a>
    <a href=""class="active">📁 Rooms</a>
    <a href="access_logs.php">📜 Access Logs</a>
    <a href="#">⚙️ Settings</a>
    <a href="logout.php">🚪 Log out</a>
    <div class="user">
      👤 <span>Juan<br><small>Faculty Member</small></span>
    </div>
  </div>

    
  <div class="room-section">
    <header>
    <h2>Hello Admin 👋🏻</h2>
    <input type="text" placeholder="Search" class="search-bar">
  </header>

   <div class="room-content">
    <section class="filters">
      <h3>Room Availability</h3>
      <div class="filter-buttons">
        <button class="filter">All Rooms</button>
        <button class="filter">Available</button>
        <button class="filter">Occupied</button>
      </div>
      <div class="actions">
        <button class="add-room">+ Add New Room</button>
        <button class="edit-rooms">✏️ Edit All Rooms</button>
      </div>
    </section>
    <?php 
     include "conn.php";

     $sql = 'select * from classrooms';
      $rooms = mysqli_query($conn,$sql);
    ?>
   
    <section class="room-grid">
      <!-- Sample room card -->
       <?php while($row = mysqli_fetch_assoc($rooms)): ?>
  <?php 
    $status = strtolower($row['Status']); 
    $statusClass = ($status == 'occupied') ? 'occupied' : 'available';
  ?>
  
  <div class="room-card <?php echo $statusClass; ?>">
    <div class="room-header">
      <h4><?php echo $row['Room_code']; ?></h4>
      <span class="status <?php echo $statusClass; ?>"><?php echo $row['Status']; ?></span>
    </div>
    <p>👥 Capacity: <?php echo $row['capacity']; ?></p>
    <p>🏫 Type: <?php echo $row['classroom_type']; ?></p>
   
    <div class="card-actions">
      <button class="edit">Edit</button>
    </div>
  </div>
<?php endwhile; ?>

       
      </div>
    
      
        </div>

    </section>

 </div>
  </div>

</body>
</html>