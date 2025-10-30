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

// Handle room deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_room'])) {
    include "conn.php";
    
    $roomId = $_POST['room_id'];
    
    // Check if room exists and get room code for message
    $check_sql = "SELECT Room_code FROM classrooms WHERE Room_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $roomId);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        $check_stmt->bind_result($roomCode);
        $check_stmt->fetch();
        
        // Check if room is used in any schedule
        $schedule_check_sql = "SELECT COUNT(*) FROM schedule WHERE Room_id = ?";
        $schedule_check_stmt = $conn->prepare($schedule_check_sql);
        $schedule_check_stmt->bind_param("i", $roomId);
        $schedule_check_stmt->execute();
        $schedule_check_stmt->bind_result($schedule_count);
        $schedule_check_stmt->fetch();
        $schedule_check_stmt->close();
        
        if ($schedule_count > 0) {
            $_SESSION['error_message'] = "Cannot delete room '$roomCode' because it is assigned to existing schedules!";
        } else {
            // Delete the room
            $delete_sql = "DELETE FROM classrooms WHERE Room_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            
            if ($delete_stmt) {
                $delete_stmt->bind_param("i", $roomId);
                
                if ($delete_stmt->execute()) {
                    $_SESSION['success_message'] = "Room '$roomCode' deleted successfully!";
                } else {
                    $_SESSION['error_message'] = "Error deleting room: " . $delete_stmt->error;
                }
                
                $delete_stmt->close();
            } else {
                $_SESSION['error_message'] = "Database error: " . $conn->error;
            }
        }
    } else {
        $_SESSION['error_message'] = "Room not found!";
    }
    
    $check_stmt->close();
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
    <!-- Rooms CSS -->
    <link rel="stylesheet" href="styles/rooms.css">
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
                <div class="alert-message alert-success" id="successMessage">
                    <?php 
                    echo $_SESSION['success_message']; 
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert-message alert-error" id="errorMessage">
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
                         data-room-id="<?php echo $row['Room_id']; ?>"
                         data-room-code="<?php echo $row['Room_code']; ?>" 
                         data-room-type="<?php echo $row['Classroom_type']; ?>" 
                         data-status="<?php echo $statusClass; ?>">
                        <div class="room-header">
                            <h4><?php echo $row['Room_code']; ?></h4>
                            <span class="status <?php echo $statusClass; ?>"><?php echo $row['Status']; ?></span>
                        </div>
                        <div class="room-details">
                            <p>👥 Capacity: <?php echo $row['Capacity']; ?></p>
                            <p>🏫 Type: <?php echo $row['Classroom_type']; ?></p>
                        </div>
                        <div class="card-actions">
                            <button class="delete" onclick="openDeleteModal(<?php echo $row['Room_id']; ?>, '<?php echo $row['Room_code']; ?>')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
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

    <!-- Delete Room Modal -->
    <div class="modal-overlay delete-modal" id="deleteRoomModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Delete Room</h2>
                <button class="close-modal">&times;</button>
            </div>
            <form id="deleteRoomForm" method="POST">
                <input type="hidden" name="delete_room" value="1">
                <input type="hidden" id="delete_room_id" name="room_id">
                
                <div class="delete-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
                
                <div style="padding: 0 25px;">
                    <p>Are you sure you want to delete room <strong id="delete_room_name"></strong>?</p>
                </div>
                
                <div class="delete-actions">
                    <button type="button" class="cancel-btn">Cancel</button>
                    <button type="submit" class="submit-btn delete-confirm">Delete Room</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="js/rooms.js"></script>
</body>
</html>