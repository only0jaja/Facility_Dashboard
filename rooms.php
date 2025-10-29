<?php
session_start();

// Prevent browser from caching this page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission to add new room
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_room'])) {
    include "conn.php";
    
    // Debug: Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $roomCode = trim($_POST['roomCode']);
    $capacity = intval($_POST['capacity']);
    $roomType = trim($_POST['roomType']);
    $status = trim($_POST['status']);
    
    // Debug output
    error_log("=== ROOM ADD ATTEMPT ===");
    error_log("Room Code: " . $roomCode);
    error_log("Capacity: " . $capacity);
    error_log("Room Type: " . $roomType);
    error_log("Status: " . $status);
    
    // Check if room already exists
    $check_sql = "SELECT Room_code FROM classrooms WHERE Room_code = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $roomCode);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        error_log("ERROR: Room code already exists");
        $_SESSION['error_message'] = "Room code '$roomCode' already exists!";
        $check_stmt->close();
        header("Location: rooms.php");
        exit();
    }
    $check_stmt->close();
    
    // Insert new room into database
    $sql = "INSERT INTO classrooms (Room_code, Capacity, Classroom_type, Status) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("siss", $roomCode, $capacity, $roomType, $status);
        
        if ($stmt->execute()) {
            error_log("SUCCESS: Room inserted into database");
            $_SESSION['success_message'] = "Room '$roomCode' added successfully!";
        } else {
            error_log("ERROR executing statement: " . $stmt->error);
            $_SESSION['error_message'] = "Error adding room: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        error_log("ERROR preparing statement: " . $conn->error);
        $_SESSION['error_message'] = "Database error: " . $conn->error;
    }
    
    $conn->close();
    
    // Redirect to prevent form resubmission
    header("Location: rooms.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms Management</title>
    <!-- Font Style -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <!-- Icons Style 1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <!-- Icons Style 2 -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <!-- Sidebar Css -->
    <link rel="stylesheet" href="styles/sidebar.css">
    
    <style>
        /* General Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #f5f7fa;
            min-height: 100vh;
        }

        .room-section{
            padding: 30px 30px 0 300px;
        }
        .room-content{
            padding-top: 25px;
        }

        /* Search Box */
        .search-box {
            position: relative;
            min-width: 300px;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            z-index: 1;
        }

        .search-box input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            background: #f8f9fa;
        }

        .search-box input:focus {
            outline: none;
            border-color: #0047AB;
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 71, 171, 0.1);
        }

        .controls-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 70px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .controls-section h1{
            width: 198px;
        }

        /* Filters Section */
        .filters {
            border-radius: 12px;
            padding: 25px;
        }

        .filter-buttons {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter {
            padding: 12px 24px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            background: white;
            color: #495057;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter:hover {
            border-color: #0047AB;
            color: #0047AB;
        }

        .filter.active {
            background: #0047AB;
            color: white;
            border-color: #0047AB;
        }

        .actions {
            display: flex;
            gap: 12px;
            margin-left: auto;
        }

        .add-room {
            background: #28a745;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .add-room:hover {
            background: #218838;
        }

        /* Room Grid */
        .room-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 3fr));
            gap: 20px;
            margin-top: 30px;
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

        /* Messages */
        .alert-message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
        }

        .modal-header h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6c757d;
        }

        .close-modal:hover {
            color: #dc3545;
        }

        .modal-content form {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            background: #f8f9fa;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #0047AB;
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 71, 171, 0.1);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .cancel-btn,
        .submit-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .cancel-btn {
            background: #6c757d;
            color: white;
        }

        .cancel-btn:hover {
            background: #5a6268;
        }

        .submit-btn {
            background: #0047AB;
            color: white;
        }

        .submit-btn:hover {
            background: #003d99;
        }
    </style>
</head>

<body>
    <div class="sidebar" id="sidebar">
        <img src="./img/loalogo.png" alt="Lyceum of Alabang Logo" style="width:120px; height:120px; border-radius:50%; object-fit: cover;margin-left: auto; margin-right: auto;">
        <h2 style="text-align: center; font-size: 20px;margin: 15px 0">
            Lyceum of Alabang
        </h2>
        
        <div class="icons">
            <a href="index.php"><i class='bx bxs-home'></i>Home</a>
            <a href="users.php"><i class='bx bxs-user-pin'></i> Users</a>
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
            <!-- Display Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert-message alert-success">
                    <?php 
                    echo $_SESSION['success_message']; 
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert-message alert-error">
                    <?php 
                    echo $_SESSION['error_message']; 
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <section class="filters">
                <div class="filter-buttons">
                    <button class="filter active" data-filter="all">All Rooms</button>
                    <button class="filter" data-filter="available">Unoccupied</button>
                    <button class="filter" data-filter="occupied">Occupied</button>
                    <div class="actions">
                        <button class="add-room" id="addRoomBtn">+ Add New Room</button>
                    </div>    
                </div>
            </section>
            
            <?php 
                include "conn.php";
                $sql = 'SELECT * FROM classrooms ORDER BY Room_code';
                $rooms = mysqli_query($conn, $sql);
                
                // Debug: Check if we have rooms
                if (!$rooms) {
                    error_log("Database query failed: " . mysqli_error($conn));
                }
            ?>

            <section class="room-grid" id="roomGrid">
                <?php 
                if ($rooms && mysqli_num_rows($rooms) > 0): 
                    while($row = mysqli_fetch_assoc($rooms)): 
                        $status = strtolower($row['Status']); 
                        $statusClass = ($status == 'occupied') ? 'occupied' : 'available';
                ?>
                    <div class="room-card <?php echo $statusClass; ?>" 
                         data-room-code="<?php echo $row['Room_code']; ?>" 
                         data-room-type="<?php echo $row['Classroom_type']; ?>" 
                         data-status="<?php echo $statusClass; ?>">
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
                <?php 
                    endwhile;
                else: 
                ?>
                    <div class="no-rooms-message">
                        No rooms found in the database.
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <!-- Add Room Modal -->
    <div class="modal-overlay" id="addRoomModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Room</h2>
                <button class="close-modal">&times;</button>
            </div>
            <form id="addRoomForm" method="POST">
                <input type="hidden" name="add_room" value="1">
                
                <div class="form-group">
                    <label for="roomCode">Room Code *</label>
                    <input type="text" id="roomCode" name="roomCode" required placeholder="e.g., Room 101">
                </div>
                
                <div class="form-group">
                    <label for="capacity">Capacity *</label>
                    <input type="number" id="capacity" name="capacity" required min="1" placeholder="e.g., 30">
                </div>
                
                <div class="form-group">
                    <label for="roomType">Room Type *</label>
                    <select id="roomType" name="roomType" required>
                        <option value="">Select Room Type</option>
                        <option value="Lecture Room">Lecture Room</option>
                        <option value="Laboratory">Laboratory</option>
                        <option value="Computer Lab">Computer Lab</option>
                        <option value="Conference Room">Conference Room</option>
                        <option value="Seminar Room">Seminar Room</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="Unoccupied">Unoccupied</option>
                        <option value="Occupied">Occupied</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="cancel-btn">Cancel</button>
                    <button type="submit" class="submit-btn">Add Room</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Wait for the page to load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded - initializing scripts');
            
            // Modal functionality
            const addRoomBtn = document.getElementById('addRoomBtn');
            const addRoomModal = document.getElementById('addRoomModal');
            const closeModalBtn = document.querySelector('.close-modal');
            const cancelBtn = document.querySelector('.cancel-btn');
            const addRoomForm = document.getElementById('addRoomForm');
            
            // Open modal
            if (addRoomBtn) {
                addRoomBtn.addEventListener('click', function() {
                    console.log('Add Room button clicked');
                    addRoomModal.style.display = 'flex';
                });
            }
            
            // Close modal functions
            function closeModal() {
                addRoomModal.style.display = 'none';
            }
            
            if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
            if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
            
            // Close modal when clicking outside
            addRoomModal.addEventListener('click', function(event) {
                if (event.target === addRoomModal) {
                    closeModal();
                }
            });
            
            // Form validation (optional - form will submit to PHP regardless)
            if (addRoomForm) {
                addRoomForm.addEventListener('submit', function(event) {
                    const roomCode = document.getElementById('roomCode').value;
                    const capacity = document.getElementById('capacity').value;
                    const roomType = document.getElementById('roomType').value;
                    
                    if (!roomCode || !capacity || !roomType) {
                        alert('Please fill in all required fields');
                        event.preventDefault();
                        return;
                    }
                    
                    console.log('Form submitted with values:', {
                        roomCode: roomCode,
                        capacity: capacity,
                        roomType: roomType,
                        status: document.getElementById('status').value
                    });
                });
            }

            // Initialize filter functionality
            initializeFilters();
        });

        // Filter functionality
        function initializeFilters() {
            const searchInput = document.getElementById('searchInput');
            const filterButtons = document.querySelectorAll('.filter');
            const roomGrid = document.getElementById('roomGrid');
            
            if (!searchInput || !roomGrid) return;
            
            const originalRoomCards = Array.from(roomGrid.querySelectorAll('.room-card'));
            
            function filterRooms() {
                const activeFilter = document.querySelector('.filter.active').getAttribute('data-filter');
                const searchTerm = searchInput.value.toLowerCase().trim();
                
                roomGrid.innerHTML = '';
                
                let hasVisibleRooms = false;
                
                originalRoomCards.forEach(card => {
                    const roomCode = card.getAttribute('data-room-code').toLowerCase();
                    const roomType = card.getAttribute('data-room-type').toLowerCase();
                    const status = card.getAttribute('data-status');
                    
                    let matchesFilter = true;
                    if (activeFilter !== 'all') {
                        matchesFilter = status === activeFilter;
                    }
                    
                    const matchesSearch = !searchTerm || 
                                         roomCode.includes(searchTerm) || 
                                         roomType.includes(searchTerm);
                    
                    if (matchesFilter && matchesSearch) {
                        roomGrid.appendChild(card.cloneNode(true));
                        hasVisibleRooms = true;
                    }
                });
                
                if (!hasVisibleRooms) {
                    const message = document.createElement('div');
                    message.className = 'no-rooms-message';
                    message.textContent = 'No rooms match your search criteria.';
                    roomGrid.appendChild(message);
                }
            }
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    filterRooms();
                });
            });
            
            searchInput.addEventListener('input', filterRooms);
        }

        // Page refresh for back/forward cache
        window.addEventListener("pageshow", function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>