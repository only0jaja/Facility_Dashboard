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
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #f5f7fb;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #0047AB;
            color: white;
            display: flex;
            flex-direction: column;
            height: 100vh;
            padding: 20px;
            position: fixed;
            transition: transform 0.3s ease;
        }

        .sidebar h1 {
            font-size: 24px;
            margin-bottom: 40px;
            color: #FFD700;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            margin: 10px 0;
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.3s ease;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #005ce6;
        }

        .sidebar .user {
            margin-top: auto;
            display: flex;
            align-items: center;
            padding: 15px;
            border-top: 1px solid #ffffff33;
            font-size: 14px;
        }

        .sidebar .user span {
            margin-left: 10px;
        }
        .sidebar i{
            padding: 10px;
            font-size: 22px;
        }
        /* rooms */
        .room-section{
            padding: 30px 30px 0 300px;
        }
        .room-content{
            padding-top: 50px;
        }
        header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 20px;
        }

        .search-bar {
          padding: 8px 12px;
          border-radius: 5px;
          border: 1px solid #ccc;
          width: 200px;
        }

        .filters {
          display: flex;
          justify-content: space-between;
          align-items: center;
          flex-wrap: wrap;
          margin-bottom: 20px;
          position: relative;
        }
        .filter-buttons{
          position: absolute;
            left: 0;
            top: 50px;
        }
        .filter-buttons button,
        .actions button {
          margin-right: 10px;
          padding: 10px 14px;
          border: none;
          border-radius: 6px;
          cursor: pointer;
        }

        .filter-buttons .filter {
          background: #eee;
        }

        .add-room {
          background: #007bff;
          color: white;
          transition: all 0.3s ease;
        }
        .add-room:hover {
          background: #0056b3;
          transform: scale(1.05);
        }

        .edit-rooms {
          background: #28a745;
          color: white;
          transition: all 0.3s ease;
        }
        .edit-rooms:hover {
          background: #1e7e34;
          transform: scale(1.05);
        }
        .room-grid {
          display: grid;
          grid-template-columns: repeat(auto-fill, minmax(400px, 3fr));
          gap: 20px;
            margin-top: 80px;
        }

        .room-card {
          background: white;
          border-radius: 10px;
          padding: 15px;
          box-shadow: 0 0 10px rgba(0,0,0,0.05);
          border-left: 5px solid #ccc;
        }

        .room-card.available {
          border-color: #00c896;
          transition: .3s ease;

        }
        .room-card.available:hover {
          transform: translateY(-5px);
        }
        .room-card.occupied {
          border-color: #f0ad4e;
        }

        .room-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
        }

        .status {
          font-size: 12px;
          padding: 4px 8px;
          border-radius: 12px;
          color: white;
        }

        .status.available {
          background-color: #00c896;
        }

        .status.occupied {
          background-color: #e40959;
        }

        .card-actions {
          margin-top: 10px;
        }

        .card-actions button {
          margin-right: 10px;
          padding: 6px 12px;
          border: none;
          border-radius: 5px;
          cursor: pointer;
        }

        .edit {
          background: #17a2b8;
          color: white;
        }

        .delete {
          background: #dc3545;
          color: white;
        }

        .pagination {
          margin-top: 30px;
          text-align: center;
        }

        .pagination button {
          margin: 0 5px;
          padding: 8px 12px;
          border: none;
          background: #eee;
          border-radius: 5px;
          cursor: pointer;
        }

        .pagination .active {
          background: #007bff;
          color: white;
        }

        /* Additional styles for active filter */
        .filter.active {
            background-color: #007bff;
            color: white;
        }
        
        .no-rooms-message {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
            background: white;
            border-radius: 10px;
            margin-top: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
    </style>
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
        <input type="text" id="searchInput" placeholder="Search Room" class="search-bar">
        </header>

        <div class="room-content">
            <section class="filters">
                <h3>Room Availability</h3>
                <div class="filter-buttons">
                    <button class="filter active" data-filter="all">All Rooms</button>
                    <button class="filter" data-filter="available">Available</button>
                    <button class="filter" data-filter="occupied">Occupied</button>
                </div>
                <div class="actions">
                    <button class="add-room">+ Add New Room</button>
                    <!-- Removed Edit All Rooms button -->
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
    <script>
        // Filter and Search functionality
        function initializeFilterAndSearch() {
            const searchInput = document.getElementById('searchInput');
            const filterButtons = document.querySelectorAll('.filter');
            const roomGrid = document.getElementById('roomGrid');
            
            // Store original room cards for filtering
            let originalRoomCards = Array.from(roomGrid.querySelectorAll('.room-card'));

            function applyFilters() {
                const activeFilter = document.querySelector('.filter.active').dataset.filter;
                const searchTerm = searchInput.value.toLowerCase().trim();

                // Clear the grid
                roomGrid.innerHTML = '';

                let hasVisibleRooms = false;

                // Filter rooms
                originalRoomCards.forEach(roomCard => {
                    const roomCode = roomCard.dataset.roomCode.toLowerCase();
                    const roomType = roomCard.dataset.roomType.toLowerCase();
                    const status = roomCard.dataset.status;

                    // Apply status filter
                    let statusMatch = true;
                    if (activeFilter !== 'all') {
                        statusMatch = status === activeFilter;
                    }

                    // Apply search filter
                    let searchMatch = true;
                    if (searchTerm) {
                        searchMatch = roomCode.includes(searchTerm) || roomType.includes(searchTerm);
                    }

                    // Show room if it matches both filters
                    if (statusMatch && searchMatch) {
                        roomGrid.appendChild(roomCard.cloneNode(true));
                        hasVisibleRooms = true;
                    }
                });

                // Show message if no rooms match
                if (!hasVisibleRooms) {
                    const noRoomsMessage = document.createElement('div');
                    noRoomsMessage.className = 'no-rooms-message';
                    noRoomsMessage.textContent = 'No rooms match your search criteria.';
                    roomGrid.appendChild(noRoomsMessage);
                }
            }

            // Event listeners for filter buttons
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Update active state
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Apply filters
                    applyFilters();
                });
            });

            // Event listener for search input
            searchInput.addEventListener('input', applyFilters);

            // Store original room cards for later use
            window.originalRoomCards = originalRoomCards;
        }

        // Modified loadRooms function to work with filters
        function loadRooms() {
            fetch('fetch_room.php')
                .then(response => response.text())
                .then(data => {
                    const roomGrid = document.querySelector('.room-grid');
                    
                    // Get current filter and search state
                    const activeFilter = document.querySelector('.filter.active').dataset.filter;
                    const searchTerm = document.getElementById('searchInput').value;
                    
                    // Create a temporary div to parse the HTML
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data;
                    
                    // Extract room cards and add data attributes
                    const roomCards = tempDiv.querySelectorAll('.room-card');
                    roomCards.forEach(card => {
                        const roomCode = card.querySelector('h4').textContent;
                        const statusElement = card.querySelector('.status');
                        const status = statusElement.textContent.toLowerCase();
                        const statusClass = status === 'occupied' ? 'occupied' : 'available';
                        const roomType = card.querySelector('p:nth-child(3)').textContent.replace('🏫 Type: ', '');
                        
                        // Add data attributes
                        card.setAttribute('data-room-code', roomCode);
                        card.setAttribute('data-room-type', roomType);
                        card.setAttribute('data-status', statusClass);
                    });
                    
                    // Update the roomGrid with new data
                    roomGrid.innerHTML = tempDiv.innerHTML;
                    
                    // Reinitialize filter and search functionality
                    initializeFilterAndSearch();
                    
                    // Reapply current filters
                    if (activeFilter !== 'all' || searchTerm) {
                        setTimeout(() => {
                            document.querySelector(`.filter[data-filter="${activeFilter}"]`).click();
                            document.getElementById('searchInput').value = searchTerm;
                            document.getElementById('searchInput').dispatchEvent(new Event('input'));
                        }, 100);
                    }
                    
                    // Add fade effect
                    roomGrid.classList.add('fade');
                    setTimeout(() => roomGrid.classList.remove('fade'), 400);
                })
                .catch(err => console.error('Error loading rooms:', err));
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeFilterAndSearch();
            
            // Load rooms initially
            loadRooms();
        });
    </script>
</body>
</html>