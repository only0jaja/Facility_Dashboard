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
    <!-- Room Style -->
    <link rel="stylesheet" href="styles/rooms.css">
    <!-- Sidebar Css -->
    <link rel="stylesheet" href="styles/sidebar.css">

</head>

<body>
    <div class="sidebar" id="sidebar">
        <h1>Dashboard</h1>
        <div class="icons">
            <a href="index.php" ><i class='bx bxs-home'></i>Home</a>
            <a href="users.php"><i class='bx bxs-user-pin' ></i> Users</a>
            <a href="rooms.php" class="active"><i class='bx bx-folder-open'></i> Rooms</a>
            <a href="access_logs.php"><i class='bx bx-bookmark-alt-plus'></i> Access Logs</a>
            <a href="schedule.php"><i class='bx bx-calendar-week'></i> Schedule</a>
            <a href="logout.php"><i class='bx bxs-log-out'></i> Log out</a>
        </div>
        <div class="user">
            👤 <span>Juan<br><small>Faculty Member</small></span>
        </div>
    </div>

    <div class="room-section">
        <div class="controls-section">
            <h1>Rooms</h1>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search Room">
            </div>
        </div>

        <div class="room-content">
            <section class="filters">
                <div class="filter-buttons">
                    <button class="filter active" data-filter="all">All Rooms</button>
                    <button class="filter" data-filter="available">Available</button>
                    <button class="filter" data-filter="occupied">Occupied</button>
                    <div class="actions">
                      <button class="add-room">+ Add New Room</button>
                    </div>    
                </div>
            </section>
                <?php 
                    include "conn.php";

                    $sql = 'select * from classrooms';
                    $rooms = mysqli_query($conn,$sql);
                ?>

            <section class="room-grid" id="roomGrid">
                <!-- Sample room card -->
                <?php while($row = mysqli_fetch_assoc($rooms)): ?>
                <?php 
                    $status = strtolower($row['Status']); 
                    $statusClass = ($status == 'occupied') ? 'occupied' : 'available';
                ?>
        
                <div class="room-card <?php echo $statusClass; ?>" data-room-code="<?php echo $row['Room_code']; ?>" data-room-type="<?php echo $row['Classroom_type']; ?>" data-status="<?php echo $statusClass; ?>">
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

            </section>
        </div>
    </div>
    <script src="js/rooms.js"></script>
</body>
</html>