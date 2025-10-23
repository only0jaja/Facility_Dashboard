<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Logs</title>  
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
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
        <!-- Filter Controls -->
        <div class="controls">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search by Log ID, User ID, RFID, Room...">
            </div>
            

            <div class="filter-controls">
                <select id="statusFilter">
                    <option value="">All Status</option>
                    <option value="granted">Granted</option>
                    <option value="denied">Denied</option>
                </select>
                
                <select id="roomFilter">
                    <option value="">All Rooms</option>
                </select>
                
                <select id="accessTypeFilter">
                    <option value="">All Access Type</option>
                    <option value="entry">Entry</option>
                    <option value="exit">Exits</option>
                </select>
                
                <button id="clearFilters" class="clear-btn">
                    <i class="fas fa-times"></i> Clear Filters
                </button>

                <button id="printBtn" class="print-btn">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>

        <!-- Access Logs Table -->
        <div class="table-section">
            <h3>Access logs</h3>
            <div class="table-scroll">
            <table id="logsTable">
                <thead>
                <tr>
                    <th>Log_id</th>
                    <th>User_id</th>
                    <th>Role</th>
                    <th>Room</th>
                    <th>Access_time</th> 
                    <th>Access_type</th> 
                    <th>Status</th>
                </tr>
                </thead>
                
                <tbody id="logsTableBody">
                <?php 
                    include 'conn.php';
                    $log_id = $conn->query("
                    SELECT access_log.*, classrooms.Room_code, Users.Role
                    FROM access_log 
                    LEFT JOIN classrooms ON access_log.Room_id = classrooms.Room_id 
                    LEFT JOIN Users ON access_log.User_id = Users.User_id
                    LEFT JOIN Course_section ON Course_section.CourseSection_id = Course_section.CourseSection_id
                    ORDER BY Access_time DESC
                    ");
                ?>
                
                <?php if ($log_id->num_rows > 0): ?>
                    <?php while($row = $log_id->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Log_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['User_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['Role']); ?></td>
                        <td><?php echo htmlspecialchars($row['Room_code']); ?></td>
                        <td><?= htmlspecialchars($row['Access_time']); ?></td>
                        <td><?= htmlspecialchars($row['Access_type']); ?></td>
                        <td style="text-align: center;">
                        <span class="status <?php echo strtolower($row['Status']); ?>">
                            <?php echo htmlspecialchars($row['Status']); ?>
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

        <!-- Print Modal -->
        <div class="print-modal" id="printModal">
            <div class="print-modal-content">
                <h3>Print Access Logs</h3>
                <div class="print-options">
                    <div class="print-option-group">
                        <label for="printDateFrom">Date From</label>
                        <input type="date" id="printDateFrom">
                    </div>
                    <div class="print-option-group">
                        <label for="printDateTo">Date To</label>
                        <input type="date" id="printDateTo">
                    </div>
                    <div class="print-option-group">
                        <label for="printAccessType">Access Type</label>
                        <select id="printAccessType">
                            <option value="all">All Types</option>
                            <option value="entry">Entry</option>
                            <option value="exit">Exit</option>
                        </select>
                    </div>
                    <div class="print-option-group">
                        <label for="printStatus">Status</label>
                        <select id="printStatus">
                            <option value="all">All Status</option>
                            <option value="granted">Granted</option>
                            <option value="denied">Denied</option>
                        </select>
                    </div>
                    <div class="print-option-group">
                        <label for="printCourse">Course</label>
                        <select id="printCourse">
                            <option value="all">All Courses</option>
                        </select>
                    </div>
                    <div class="print-option-group">
                        <label for="printRoom">Room</label>
                        <select id="printRoom">
                            <option value="all">All Rooms</option>
                        </select>
                    </div>
                </div>
                <div class="print-modal-buttons">
                    <button class="print-cancel" id="printCancel">Cancel</button>
                    <button class="print-confirm" id="printConfirm">Print</button>
                </div>
            </div>
        </div>

        <!-- Hidden print section (only visible during printing) -->
        <div id="printSection"></div>
    </div>
<script src="js/logs.js"></script>
</body>
</html>