// Function to filter table rows 
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
            const statusCell = cells[6]; // 7th column
            if (statusCell) {
                // Try multiple ways to get the status text
                let statusText = '';
                
                // Method 1: Get from .status element
                const statusElement = statusCell.querySelector('.status');
                if (statusElement) {
                statusText = statusElement.textContent.toLowerCase().trim();
                } 
                // Method 2: Get directly from cell text
                else {
                statusText = statusCell.textContent.toLowerCase().trim();
                }
                
                console.log(`Status: "${statusText}", Filter: "${statusFilter}"`); // Debug
                
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
        fetch('ajax/fetch_logs.php')
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






// Printables Access Logs functionality
    document.getElementById('printBtn').addEventListener('click', function() {
        document.getElementById('printModal').style.display = 'flex';
    });

    document.getElementById('printCancel').addEventListener('click', function() {
        document.getElementById('printModal').style.display = 'none';
    });

    document.getElementById('printConfirm').addEventListener('click', function() {
        // Get print options
        const dateFrom = document.getElementById('printDateFrom').value;
        const dateTo = document.getElementById('printDateTo').value;
        const accessType = document.getElementById('printAccessType').value;
        const status = document.getElementById('printStatus').value;
        const course = document.getElementById('printCourse').value;
        const room = document.getElementById('printRoom').value;
        
        // Generate print view
        generatePrintView(dateFrom, dateTo, accessType, status, course, room);
        
        // Close modal
        document.getElementById('printModal').style.display = 'none';
        
        // Print the document
        window.print();
    });

    // Generate print view
    function generatePrintView(dateFrom, dateTo, accessType, status, course, room) {
        const printSection = document.getElementById('printSection');
        printSection.innerHTML = '';
        
        // Filter data based on print options
        const filteredData = filterDataForPrint(dateFrom, dateTo, accessType, status, course, room);
        
        // Create print header
        const printHeader = document.createElement('div');
        printHeader.className = 'print-header';
        printHeader.innerHTML = `
            <h2>Access Logs Report</h2>
            <p>Generated on: ${new Date().toLocaleDateString()}</p>
        `;
        printSection.appendChild(printHeader);
        
        // Create print filters info
        const printFilters = document.createElement('div');
        printFilters.className = 'print-filters';
        
        let filtersText = 'Filters: ';
        const filters = [];
        
        if (dateFrom || dateTo) {
            filters.push(`Date: ${dateFrom || 'Any'} to ${dateTo || 'Any'}`);
        }
        if (accessType !== 'all') {
            filters.push(`Access Type: ${accessType}`);
        }
        if (status !== 'all') {
            filters.push(`Status: ${status}`);
        }
        if (course !== 'all') {
            filters.push(`Course: ${course}`);
        }
        if (room !== 'all') {
            filters.push(`Room: ${room}`);
        }
        
        if (filters.length === 0) {
            filtersText += 'All records';
        } else {
            filtersText += filters.join(', ');
        }
        
        printFilters.textContent = filtersText;
        printSection.appendChild(printFilters);
        
        // Create print table
        const printTable = document.createElement('table');
        printTable.className = 'print-table';
        
        // Add table header
        const tableHeader = document.createElement('thead');
        tableHeader.innerHTML = `
            <tr>
                <th>Log_id</th>
                <th>User_id</th>
                <th>Role</th>
                <th>Room</th>
                <th>Access_time</th>
                <th>Access_type</th>
                <th>Status</th>
            </tr>
        `;
        printTable.appendChild(tableHeader);
        
        // Add table body with filtered rows
        const tableBody = document.createElement('tbody');
        
        if (filteredData.length > 0) {
            filteredData.forEach(row => {
                tableBody.appendChild(row);
            });
        } else {
            const noDataRow = document.createElement('tr');
            noDataRow.innerHTML = `<td colspan="7" style="text-align: center;">No records found matching the selected criteria</td>`;
            tableBody.appendChild(noDataRow);
        }
        
        printTable.appendChild(tableBody);
        printSection.appendChild(printTable);
        
        // Create print footer
        const printFooter = document.createElement('div');
        printFooter.className = 'print-footer';
        printFooter.innerHTML = `
            <p>Total Records: ${filteredData.length}</p>
            <p>Lyceum of San Pedro Facility Control System Access Logs</p>
        `;
        printSection.appendChild(printFooter);
    }

    // Filter data for printing
    function filterDataForPrint(dateFrom, dateTo, accessType, status, course, room) {
        const rows = document.querySelectorAll('#logsTableBody tr');
        const filteredRows = [];
        
        rows.forEach(row => {
            const rowDate = new Date(row.cells[4].textContent.split(' ')[0]);
            const rowAccessType = row.cells[5].textContent.toLowerCase();
            const statusCell = row.cells[6].querySelector('.status');
            const rowStatus = statusCell ? statusCell.textContent.toLowerCase().trim() : '';
            const rowCourse = row.cells[1].textContent;
            const rowRoom = row.cells[3].textContent;
            
            // Date filter
            let dateMatch = true;
            if (dateFrom) {
                const fromDate = new Date(dateFrom);
                if (rowDate < fromDate) dateMatch = false;
            }
            if (dateTo) {
                const toDate = new Date(dateTo);
                toDate.setDate(toDate.getDate() + 1); // Include the end date
                if (rowDate >= toDate) dateMatch = false;
            }
            
            // Access type filter
            const accessTypeMatch = (accessType === 'all' || rowAccessType === accessType);
            
            // Status filter
            const statusMatch = (status === 'all' || rowStatus === status);
            
            // Course filter
            const courseMatch = (course === 'all' || rowCourse === course);
            
            // Room filter
            const roomMatch = (room === 'all' || rowRoom === room);
            
            if (dateMatch && accessTypeMatch && statusMatch && courseMatch && roomMatch) {
                // Create a deep clone of the row for printing
                const clonedRow = row.cloneNode(true);
                filteredRows.push(clonedRow);
            }
        });
        
        return filteredRows;
    }

    // Initialize the page
    document.addEventListener('DOMContentLoaded', function() {
        populateFilters();
        
        // Set default dates for print modal
        const today = new Date();
        const oneWeekAgo = new Date();
        oneWeekAgo.setDate(today.getDate() - 7);
        
        document.getElementById('printDateFrom').valueAsDate = oneWeekAgo;
        document.getElementById('printDateTo').valueAsDate = today;
    });