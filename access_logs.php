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
          </div>
        </div>
        
        <div class="table-section">
          <h3>Access logs</h3>
          <div class="table-scroll">
            <table id="logsTable">
              <thead>
                <tr>
                  <th>Log_id</th>
                  <th>User_id</th>
                  <th>Rfid_tag</th>
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
                    SELECT access_log.*, classrooms.Room_code 
                    FROM access_log 
                    LEFT JOIN classrooms ON access_log.Room_id = classrooms.Room_id 
                    ORDER BY Access_time DESC
                  ");
                ?>
                
                <?php if ($log_id->num_rows > 0): ?>
                  <?php while($row = $log_id->fetch_assoc()): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['Log_id']); ?></td>
                      <td><?php echo htmlspecialchars($row['User_id']); ?></td>
                      <td><?php echo htmlspecialchars($row['Rfid_tag']); ?></td>
                      <td><?php echo htmlspecialchars($row['Room_id']); ?></td>
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
      </div>

<script>
// Function to filter table rows based on search and filter criteria
function filterTable() {
  const searchValue = document.getElementById('searchInput').value.toLowerCase();
  const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
  const roomFilter = document.getElementById('roomFilter').value;
  const accessTypeFilter = document.getElementById('accessTypeFilter').value.toLowerCase();
  
  const table = document.getElementById('logsTable');
  const tbody = document.getElementById('logsTableBody');
  const rows = tbody.getElementsByTagName('tr');
  
  let visibleRows = 0;
  
  for (let i = 0; i < rows.length; i++) {
    const cells = rows[i].getElementsByTagName('td');
    let showRow = true;
    
    // Skip the "no records" row if it exists
    if (cells.length === 1 && cells[0].colSpan === "7") {
      rows[i].style.display = 'none';
      continue;
    }
    
    // Search filter - check all cells for matching text
    if (searchValue) {
      let rowContainsText = false;
      for (let j = 0; j < cells.length; j++) {
        if (cells[j].textContent.toLowerCase().includes(searchValue)) {
          rowContainsText = true;
          break;
        }
      }
      if (!rowContainsText) {
        showRow = false;
      }
    }
    
    // Status filter
    if (statusFilter && showRow) {
      const statusCell = cells[6]; // Status is in the 7th column (index 6)
      if (statusCell) {
        const statusText = statusCell.querySelector('.status').textContent.toLowerCase();
        if (statusText !== statusFilter) {
          showRow = false;
        }
      }
    }
    
    // Room filter
    if (roomFilter && showRow) {
      const roomCell = cells[3]; // Room is in the 4th column (index 3)
      if (roomCell && roomCell.textContent !== roomFilter) {
        showRow = false;
      }
    }
    
    // Access type filter
    if (accessTypeFilter && showRow) {
      const accessTypeCell = cells[5]; // Access type is in the 6th column (index 5)
      if (accessTypeCell && accessTypeCell.textContent.toLowerCase() !== accessTypeFilter) {
        showRow = false;
      }
    }
    
    // Show or hide the row
    rows[i].style.display = showRow ? '' : 'none';
    if (showRow) visibleRows++;
  }
  
  // Show "no results" message if no rows are visible
  const noResultsRow = document.createElement('tr');
  noResultsRow.innerHTML = '<td colspan="7" class="no-results">No records match your search criteria</td>';
  
  // Remove existing no-results row if it exists
  const existingNoResults = tbody.querySelector('.no-results');
  if (existingNoResults) {
    existingNoResults.closest('tr').remove();
  }
  
  // Add no-results row if no visible rows
  if (visibleRows === 0 && rows.length > 0) {
    tbody.appendChild(noResultsRow);
  }
}

// Function to populate room filter options
function populateRoomFilter() {
  const table = document.getElementById('logsTable');
  const tbody = document.getElementById('logsTableBody');
  const rows = tbody.getElementsByTagName('tr');
  const roomFilter = document.getElementById('roomFilter');
  
  // Clear existing options except the first one
  while (roomFilter.children.length > 1) {
    roomFilter.removeChild(roomFilter.lastChild);
  }
  
  // Get unique room values
  const rooms = new Set();
  
  for (let i = 0; i < rows.length; i++) {
    const roomCell = rows[i].getElementsByTagName('td')[3];
    if (roomCell && roomCell.textContent && rows[i].style.display !== 'none') {
      rooms.add(roomCell.textContent);
    }
  }
  
  // Add room options to filter
  rooms.forEach(room => {
    const option = document.createElement('option');
    option.value = room;
    option.textContent = room;
    roomFilter.appendChild(option);
  });
}

// Function to load logs via AJAX
function loadLogs() {
  fetch('fetch.php')
    .then(response => response.text())
    .then(html => {
      document.getElementById('logsTableBody').innerHTML = html;
      // Re-populate room filter after loading new data
      populateRoomFilter();
      // Re-apply filters after loading new data
      filterTable();
    })
    .catch(error => {
      console.error('Error loading logs:', error);
    });
}

// Event listeners for filtering
document.getElementById('searchInput').addEventListener('input', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);
document.getElementById('roomFilter').addEventListener('change', filterTable);
document.getElementById('accessTypeFilter').addEventListener('change', filterTable);

// Clear filters button
document.getElementById('clearFilters').addEventListener('click', function() {
  document.getElementById('searchInput').value = '';
  document.getElementById('statusFilter').value = '';
  document.getElementById('roomFilter').value = '';
  document.getElementById('accessTypeFilter').value = '';
  filterTable();
});


window.onload = function() {
  populateRoomFilter();
  
};


</script>
</body>
</html>