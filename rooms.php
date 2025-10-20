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
    <link rel="stylesheet" href="styles/rooms.css">
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
    <script>
        function loadRooms() {
        fetch('fetch_room.php')
            .then(response => response.text())
            .then(data => {
            const roomGrid = document.querySelector('.room-grid');
            roomGrid.innerHTML = data;
            roomGrid.classList.add('fade');
            setTimeout(() => roomGrid.classList.remove('fade'), 400);
            })
            .catch(err => console.error('Error loading rooms:', err));
        }

        // Load every 3 seconds (adjust if needed)
        setInterval(loadRooms, 1000);
        window.onload = loadRooms;
    </script>
</body>
</html>